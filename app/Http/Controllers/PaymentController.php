<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentReminder;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with('user');
        if (! $request->expectsJson()) {
            $query->with('carriedTo');
        }

        // History Filter (Paid, Unpaid, Due)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query->whereHas('user', function (Builder $userQuery) use ($search): void {
                    $userQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                })->orWhere('tenant_name', 'like', '%'.$search.'%');
            });
        }

        $payments = $query->latest()->get();
        $dueSoonCount = $payments->filter(
            fn (Payment $payment): bool => $payment->balance > 0
                && $payment->days_until_due !== null
                && $payment->days_until_due >= 0
                && $payment->days_until_due <= 5
        )->count();

        // Kung API request, magbabalik ng JSON
        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Payments retrieved successfully.',
                'data' => $payments,
            ]);
        }

        return view('payments.index', compact('payments', 'dueSoonCount'));
    }

    public function sendPaymentReminders(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reminder_date' => ['required', 'date_format:Y-m-d'],
        ]);
        $reminderDate = DateTimeImmutable::createFromFormat('!Y-m-d', $validated['reminder_date']);

        if ($reminderDate === false || $reminderDate->format('Y-m-d') !== $validated['reminder_date']) {
            throw ValidationException::withMessages([
                'reminder_date' => 'Choose a valid reminder date.',
            ]);
        }

        $monthYear = $reminderDate->format('F Y');
        $dueDate = $reminderDate->format('Y-m-d');
        $displayDueDate = $reminderDate->format('F j, Y');
        $billingPayments = function (Builder|HasMany $query) use ($monthYear, $dueDate): void {
            $query->where('is_billing_record', true)
                ->where('month_year', $monthYear)
                ->whereDate('due_date', $dueDate)
                ->whereNull('carried_to_payment_id')
                ->where('approval_status', '!=', 'Pending')
                ->whereColumn('amount', '>', 'amount_paid')
                ->latest('id');
        };
        $tenants = User::query()
            ->where('role', 'tenant')
            ->whereHas('payments', $billingPayments)
            ->with(['payments' => $billingPayments])
            ->get();

        if ($tenants->isEmpty()) {
            return redirect()
                ->route('payments.index')
                ->with('success', 'No tenants have an outstanding balance due on '.$displayDueDate.'.');
        }

        $tenantCount = $tenants->count();

        foreach ($tenants as $tenant) {
            $payment = $tenant->payments->first();

            if (! $payment instanceof Payment) {
                throw new \LogicException('A tenant selected for a reminder must have a matching outstanding billing record.');
            }

            Notification::send($tenant, new PaymentReminder($payment));
        }

        return redirect()
            ->route('payments.index')
            ->with('success', 'Payment reminders sent to '.$tenantCount.' '.($tenantCount === 1 ? 'tenant' : 'tenants').' due on '.$displayDueDate.' by email and in-app notification.');
    }

    public function create()
    {
        $tenants = User::where('role', 'tenant')->get();

        return view('payments.create', compact('tenants'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id' => 'nullable|integer|unique:payments,id',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'amount_paid' => 'nullable|numeric|min:0',
            'month_year' => 'nullable|string',     // Ginawa nang opsyonal
            'payment_date' => 'nullable|string',   // Ginawa ring opsyonal
            'status' => 'required|in:Paid,Partial,Unpaid,Due',
            'due_date' => 'nullable|date',
            'payment_method' => 'nullable|in:Paid in person,Online payment',
            'notes' => 'nullable|string',
        ]);

        // Sasalo nito alinman sa month_year o payment_date ang ipasa mo sa Postman
        $dateValue = $request->month_year ?? $request->payment_date ?? now()->format('F Y');
        $tenant = User::findOrFail($request->user_id);
        $baseAmountCents = (int) round((float) $request->input('amount') * 100);
        $nextBillingPeriod = $this->billingPeriodStart($dateValue);

        $payment = DB::transaction(function () use ($request, $tenant, $dateValue, $baseAmountCents, $nextBillingPeriod): Payment {
            $previousPayments = $tenant->payments()
                ->where('is_billing_record', true)
                ->whereNull('carried_to_payment_id')
                ->whereIn('status', ['Unpaid', 'Due', 'Partial'])
                ->lockForUpdate()
                ->get(['id', 'amount', 'amount_paid', 'month_year', 'approval_status']);
            $balancesToCarry = [];
            $carriedInCents = 0;

            foreach ($previousPayments as $previousPayment) {
                if (! $this->isEarlierBillingPeriod($previousPayment, $dateValue, $nextBillingPeriod)) {
                    continue;
                }

                if ($previousPayment->approval_status === 'Pending') {
                    throw ValidationException::withMessages([
                        'user_id' => "Confirm or reject the tenant's pending payment before creating the next billing period.",
                    ]);
                }

                $balanceCents = (int) round((float) $previousPayment->balance * 100);

                if ($balanceCents <= 0) {
                    continue;
                }

                $balancesToCarry[] = [$previousPayment, $balanceCents];
                $carriedInCents += $balanceCents;
            }

            $amountDueCents = $baseAmountCents + $carriedInCents;
            $amountDue = $amountDueCents / 100;
            $amountPaid = $this->amountPaidForStatus(
                $request,
                $amountDue,
                (float) ($request->input('amount_paid') ?? 0)
            );

            $dataToCreate = [
                'user_id' => $tenant->id,
                'is_billing_record' => true,
                'amount' => $amountDue,
                'amount_paid' => $amountPaid,
                'carried_in_amount' => $carriedInCents / 100,
                'month_year' => $dateValue,
                'status' => $request->status,
                'due_date' => $request->due_date,
                'payment_method' => $request->payment_method,
                'paid_at' => $amountPaid > 0 ? now() : null,
                'payment_date' => $amountPaid > 0 ? now()->toDateString() : null,
                'notes' => $request->notes ?? null,
            ];

            if ($request->filled('id')) {
                $dataToCreate['id'] = $request->id;
            }

            $payment = Payment::create($dataToCreate);

            foreach ($balancesToCarry as [$previousPayment, $balanceCents]) {
                $previousPayment->update([
                    'carried_balance' => $balanceCents / 100,
                    'carried_to_payment_id' => $payment->id,
                ]);
            }

            return $payment;
        });

        // Kung galing Postman / API, magbabalik ng JSON response
        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Payment record created successfully.',
                'data' => $payment,
            ], 201);
        }

        return redirect()->route('payments.index')->with('success', 'Payment record created successfully.');
    }

    public function approve(Payment $payment): RedirectResponse
    {
        DB::transaction(function () use ($payment): void {
            $payment = Payment::whereKey($payment->getKey())->lockForUpdate()->firstOrFail();

            if (
                ! $payment->is_billing_record
                || $payment->carried_to_payment_id !== null
                || $payment->approval_status !== 'Pending'
                || (float) $payment->pending_amount <= 0
            ) {
                throw ValidationException::withMessages([
                    'approval_status' => 'This payment does not have a pending submission to confirm.',
                ]);
            }

            $paidCents = (int) round((float) $payment->amount_paid * 100);
            $pendingCents = (int) round((float) $payment->pending_amount * 100);
            $amountCents = (int) round((float) $payment->amount * 100);

            if ($paidCents + $pendingCents > $amountCents) {
                throw ValidationException::withMessages([
                    'approval_status' => 'The submitted amount exceeds the remaining balance. Edit the billing record before confirming.',
                ]);
            }

            $totalPaidCents = $paidCents + $pendingCents;
            $payment->update([
                'amount_paid' => $totalPaidCents / 100,
                'pending_amount' => 0,
                'approval_status' => 'Approved',
                'status' => $totalPaidCents === $amountCents ? 'Paid' : 'Partial',
                'paid_at' => now(),
                'payment_date' => now()->toDateString(),
            ]);
        });

        return redirect()
            ->route('payments.index')
            ->with('success', 'Payment confirmed. The receipt is now available to the tenant.');
    }

    public function reject(Payment $payment): RedirectResponse
    {
        DB::transaction(function () use ($payment): void {
            $payment = Payment::whereKey($payment->getKey())->lockForUpdate()->firstOrFail();

            if (
                ! $payment->is_billing_record
                || $payment->carried_to_payment_id !== null
                || $payment->approval_status !== 'Pending'
                || (float) $payment->pending_amount <= 0
            ) {
                throw ValidationException::withMessages([
                    'approval_status' => 'This payment does not have a pending submission to reject.',
                ]);
            }

            $payment->update([
                'pending_amount' => 0,
                'approval_status' => 'Rejected',
            ]);
        });

        return redirect()
            ->route('payments.index')
            ->with('success', 'Payment rejected. The outstanding balance remains due.');
    }

    public function edit(Payment $payment)
    {
        $tenants = User::where('role', 'tenant')->get();

        return view('payments.edit', compact('payment', 'tenants'));
    }

    public function update(Request $request, Payment $payment)
    {
        if ($payment->carried_to_payment_id !== null) {
            throw ValidationException::withMessages([
                'amount' => 'This balance has been carried forward. Edit the current billing record instead.',
            ]);
        }

        if ($payment->approval_status === 'Pending') {
            throw ValidationException::withMessages([
                'status' => 'Confirm or reject the pending tenant payment before editing this billing record.',
            ]);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id', // Idinagdag para ma-validate ang mapipiling tenant
            'amount' => 'required|numeric|min:0.01',
            'amount_paid' => 'nullable|numeric|min:0',
            'month_year' => 'nullable|string',
            'payment_date' => 'nullable|string',
            'status' => 'required|in:Paid,Partial,Unpaid,Due',
            'due_date' => 'nullable|date',
            'payment_method' => 'nullable|in:Paid in person,Online payment',
            'notes' => 'nullable|string',
        ]);

        $amountPaid = $this->amountPaidForStatus(
            $request,
            (float) $request->input('amount'),
            (float) ($request->input('amount_paid') ?? 0)
        );

        $dateValue = $request->month_year ?? $request->payment_date ?? $payment->month_year;

        $payment->update([
            'user_id' => $request->user_id, // Idinagdag para ma-save ang bagong napiling tenant
            'amount' => $request->amount,
            'amount_paid' => $amountPaid,
            'month_year' => $dateValue,
            'status' => $request->status,
            'due_date' => $request->due_date,
            'payment_method' => $request->payment_method,
            'paid_at' => $amountPaid > 0 ? ($payment->paid_at ?? now()) : null,
            'payment_date' => $amountPaid > 0 ? ($payment->payment_date ?? now()->toDateString()) : null,
            'notes' => $request->notes ?? $payment->notes,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Payment record updated successfully.',
                'data' => $payment,
            ]);
        }

        return redirect()->route('payments.index')->with('success', 'Payment record updated successfully.');
    }

    public function destroy(Request $request, Payment $payment)
    {
        if ($payment->receipt_path) {
            foreach (['local', 'public'] as $disk) {
                Storage::disk($disk)->delete($payment->receipt_path);
            }
        }

        $payment->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Payment record deleted successfully.',
            ]);
        }

        return redirect()->route('payments.index')->with('success', 'Payment record deleted successfully.');
    }

    public function receipt(Payment $payment)
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && $payment->user_id !== $user->id) {
            abort(404);
        }

        if ($payment->approval_status !== 'Approved') {
            abort(403, 'The PDF receipt is available after admin approval.');
        }

        if ((float) $payment->amount_paid <= 0) {
            abort(404, 'A PDF receipt is available after a payment has been recorded.');
        }

        $tenantName = $payment->user?->name ?? 'Tenant';
        $receiptNumber = 'BH-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT);
        $pdf = $this->createPaymentReceiptPdf($payment, $tenantName, $receiptNumber);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$receiptNumber.'.pdf"',
        ]);
    }

    public function uploadedReceipt(Payment $payment)
    {
        $disk = $this->receiptStorageDisk($payment);

        return Storage::disk($disk)->download(
            $payment->receipt_path,
            'payment-receipt-'.$payment->id.'.'.pathinfo($payment->receipt_path, PATHINFO_EXTENSION)
        );
    }

    public function viewUploadedReceipt(Payment $payment)
    {
        $diskName = $this->receiptStorageDisk($payment);
        $storage = Storage::disk($diskName);
        $mimeType = $storage->mimeType($payment->receipt_path);
        $extensions = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];

        if (! isset($extensions[$mimeType])) {
            abort(415, 'This uploaded receipt format cannot be previewed.');
        }

        return response()->file($storage->path($payment->receipt_path), [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="payment-receipt-'.$payment->id.'.'.$extensions[$mimeType].'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function receiptStorageDisk(Payment $payment): string
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && $payment->user_id !== $user->id) {
            abort(404);
        }

        if ($user->role !== 'admin' && $payment->approval_status !== 'Approved') {
            abort(403, 'Uploaded receipts are available after admin approval.');
        }

        if (! $payment->receipt_path) {
            abort(404, 'Uploaded receipt not found.');
        }

        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($payment->receipt_path)) {
                return $disk;
            }
        }

        abort(404, 'Uploaded receipt not found.');
    }

    private function amountPaidForStatus(Request $request, float $amount, float $amountPaid): float
    {
        $status = $request->input('status');

        if ($status === 'Paid') {
            return $amount;
        }

        if ($status === 'Partial' && ($amountPaid <= 0 || $amountPaid >= $amount)) {
            throw ValidationException::withMessages([
                'amount_paid' => 'For a partial payment, enter an amount greater than zero and less than the total due.',
            ]);
        }

        if (in_array($status, ['Unpaid', 'Due'], true)) {
            return 0;
        }

        if ($amountPaid >= $amount) {
            throw ValidationException::withMessages([
                'status' => 'Select Paid in full when the amount paid equals the total due.',
            ]);
        }

        return round($amountPaid, 2);
    }

    private function isEarlierBillingPeriod(Payment $payment, string $nextMonthYear, ?DateTimeImmutable $nextPeriod): bool
    {
        if (trim($payment->month_year) === trim($nextMonthYear)) {
            return false;
        }

        $paymentPeriod = $this->billingPeriodStart($payment->month_year);
        if ($paymentPeriod !== null && $nextPeriod !== null) {
            return $paymentPeriod < $nextPeriod;
        }

        return true;
    }

    private function billingPeriodStart(string $monthYear): ?DateTimeImmutable
    {
        foreach (['!F Y', '!M Y', '!Y-m-d', '!Y-m'] as $format) {
            $period = DateTimeImmutable::createFromFormat($format, trim($monthYear));
            $errors = DateTimeImmutable::getLastErrors();

            if ($period !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $period->modify('first day of this month')->setTime(0, 0);
            }
        }

        return null;
    }

    private function createPaymentReceiptPdf(Payment $payment, string $tenantName, string $receiptNumber): string
    {
        $escape = static function (string $value): string {
            $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            $safe = $ascii === false ? preg_replace('/[^\x20-\x7E]/', '?', $value) : $ascii;

            return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $safe);
        };
        $text = static function (string $font, int $size, int $x, int $y, string $value) use ($escape): string {
            $escapedValue = $escape($value);

            return "BT /{$font} {$size} Tf 1 1 1 rg {$x} {$y} Td ({$escapedValue}) Tj ET\n";
        };
        $darkText = static function (string $font, int $size, int $x, int $y, string $value) use ($escape): string {
            $escapedValue = $escape($value);

            return "BT /{$font} {$size} Tf 0.09 0.25 0.21 rg {$x} {$y} Td ({$escapedValue}) Tj ET\n";
        };

        $total = number_format((float) $payment->amount, 2);
        $paid = number_format((float) $payment->amount_paid, 2);
        $balance = number_format($payment->balance, 2);
        $state = $payment->payment_state;
        $receiptState = $state === 'Paid' ? 'PAID IN FULL' : strtoupper($state);

        $stream = "q\n";
        $stream .= "0.09 0.25 0.21 rg 0 620 612 172 re f\n";
        $stream .= "0.91 0.67 0.28 rg 48 724 44 44 re f\n";
        $stream .= "1 1 1 rg 57 732 26 24 re f\n";
        $stream .= "0.09 0.25 0.21 rg 62 732 16 15 re f\n";
        $stream .= $text('F2', 23, 108, 746, 'BHouse');
        $stream .= $text('F1', 10, 109, 728, 'BOARDING HOUSE PAYMENT');
        $stream .= $text('F2', 19, 48, 672, 'PAYMENT RECEIPT');
        $stream .= $text('F1', 10, 48, 651, 'Official record of your boarding-house payment');
        $stream .= "0.91 0.67 0.28 rg 48 596 516 1 re f\n";

        $stream .= $darkText('F2', 9, 48, 560, 'RECEIPT NUMBER');
        $stream .= $darkText('F2', 13, 48, 540, $receiptNumber);
        $stream .= $darkText('F2', 9, 324, 560, 'PAYMENT STATUS');
        $stream .= $darkText('F2', 13, 324, 540, $receiptState);

        $stream .= "0.85 0.90 0.86 RG 48 516 m 564 516 l S\n";
        $stream .= $darkText('F2', 9, 48, 493, 'TENANT');
        $stream .= $darkText('F1', 13, 48, 472, $tenantName);
        $stream .= $darkText('F2', 9, 324, 493, 'BILLING PERIOD');
        $stream .= $darkText('F1', 13, 324, 472, $payment->month_year);

        $stream .= "0.94 0.97 0.93 rg 48 324 516 112 re f\n";
        $stream .= $darkText('F2', 9, 66, 412, 'TOTAL AMOUNT DUE');
        $stream .= $darkText('F2', 17, 66, 385, 'PHP '.$total);
        $stream .= $darkText('F2', 9, 232, 412, 'AMOUNT PAID');
        $stream .= $darkText('F2', 17, 232, 385, 'PHP '.$paid);
        $stream .= $darkText('F2', 9, 408, 412, 'BALANCE');
        $stream .= $darkText('F2', 17, 408, 385, 'PHP '.$balance);

        $stream .= $darkText('F2', 9, 48, 290, 'PAYMENT METHOD');
        $stream .= $darkText('F1', 12, 48, 270, $payment->payment_method ?: 'Paid in person');
        $stream .= $darkText('F2', 9, 324, 290, 'DATE RECORDED');
        $stream .= $darkText('F1', 12, 324, 270, $payment->paid_at?->format('F d, Y') ?? now()->format('F d, Y'));

        $stream .= "0.85 0.90 0.86 RG 48 238 m 564 238 l S\n";
        $stream .= $darkText('F1', 10, 48, 210, 'Thank you. Please keep this receipt for your payment records.');
        $stream .= $darkText('F1', 9, 48, 190, 'For questions about this transaction, please contact the boarding-house administrator.');
        $stream .= 'Q';

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            '<< /Length '.strlen($stream)." >>\nstream\n".$stream."\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
        ];
        $objects[2] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> /Contents 4 0 R >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n".$object."\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n".$xref."\n%%EOF";

        return $pdf;
    }
}
