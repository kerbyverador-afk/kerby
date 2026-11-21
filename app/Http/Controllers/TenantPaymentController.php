<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TenantPaymentController extends Controller
{
    public function dashboard(): View
    {
        $tenant = Auth::user();
        $payments = $tenant->payments()
            ->latest()
            ->get();

        $latestPayment = $payments->first();
        $currentStatus = $latestPayment?->payment_state ?? 'Unpaid';
        $reminderPayments = $payments->filter(
            fn (Payment $payment): bool => $payment->balance > 0
                && $payment->days_until_due !== null
                && $payment->days_until_due <= 5
        );
        $notifications = $tenant->notifications()->latest()->limit(5)->get();
        $unreadNotificationCount = $tenant->unreadNotifications()->count();

        return view('tenant.dashboard', compact(
            'payments',
            'latestPayment',
            'currentStatus',
            'reminderPayments',
            'notifications',
            'unreadNotificationCount'
        ));
    }

    public function markNotificationAsRead(string $notification): RedirectResponse
    {
        $tenantNotification = Auth::user()->notifications()->whereKey($notification)->firstOrFail();
        $tenantNotification->markAsRead();

        return redirect()
            ->route('tenant.dashboard')
            ->with('success', 'Notification marked as read.');
    }

    public function create(): View
    {
        $openPayments = Auth::user()
            ->payments()
            ->where('is_billing_record', true)
            ->whereNull('carried_to_payment_id')
            ->whereIn('status', ['Unpaid', 'Due', 'Partial'])
            ->latest('id')
            ->get(['month_year', 'amount', 'amount_paid', 'approval_status'])
            ->filter(fn (Payment $payment): bool => (float) $payment->balance > 0)
            ->unique('month_year');
        $dueAmountsByMonth = $openPayments
            ->mapWithKeys(fn (Payment $payment): array => [$payment->month_year => $payment->balance])
            ->all();
        $pendingMonthsByMonth = $openPayments
            ->filter(fn (Payment $payment): bool => $payment->approval_status === 'Pending')
            ->mapWithKeys(fn (Payment $payment): array => [$payment->month_year => true])
            ->all();

        return view('tenant.payments.create', compact('dueAmountsByMonth', 'pendingMonthsByMonth'));
    }

    public function history(): View
    {
        $payments = Auth::user()
            ->payments()
            ->with('carriedTo')
            ->latest()
            ->get();

        return view('tenant.payments.history', compact('payments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'payment_coverage' => ['required', 'in:full,partial'],
            'month_year' => ['required', 'string', 'max:40'],
            'due_date' => ['nullable', 'date'],
            'payment_method' => ['required', 'in:Paid in person,Online payment'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $duePayment = Auth::user()
                ->payments()
                ->where('month_year', $validated['month_year'])
                ->where('is_billing_record', true)
                ->whereNull('carried_to_payment_id')
                ->whereIn('status', ['Unpaid', 'Due', 'Partial'])
                ->lockForUpdate()
                ->latest('id')
                ->get(['id', 'month_year', 'amount', 'amount_paid', 'due_date', 'receipt_path', 'notes', 'approval_status'])
                ->first(fn (Payment $payment): bool => (float) $payment->balance > 0);

            if (! $duePayment) {
                throw ValidationException::withMessages([
                    'amount' => 'No outstanding payment was set by the admin for this month. Please contact the admin.',
                ]);
            }

            if ($duePayment->approval_status === 'Pending') {
                throw ValidationException::withMessages([
                    'amount_paid' => 'Your previous payment for this month is awaiting admin confirmation.',
                ]);
            }

            $balanceCents = (int) round((float) $duePayment->balance * 100);
            $paidNowCents = (int) round((float) ($validated['amount_paid'] ?? 0) * 100);

            if ($validated['payment_coverage'] === 'full') {
                $paidNowCents = $balanceCents;
            } elseif ($paidNowCents <= 0 || $paidNowCents >= $balanceCents) {
                throw ValidationException::withMessages([
                    'amount_paid' => 'For a partial payment, the amount paid must be greater than zero and less than the total due.',
                ]);
            }

            $receiptPath = $duePayment->receipt_path;
            if ($request->hasFile('receipt')) {
                $receiptPath = $request->file('receipt')->store('payment-receipts', 'local');
            }

            $duePayment->update([
                'pending_amount' => $paidNowCents / 100,
                'approval_status' => 'Pending',
                'due_date' => $validated['due_date'] ?? $duePayment->due_date,
                'payment_method' => $validated['payment_method'],
                'receipt_path' => $receiptPath,
                'submitted_at' => now(),
                'notes' => $validated['notes'] ?? $duePayment->notes ?? 'Awaiting admin confirmation.',
            ]);
        });

        return redirect()
            ->route('tenant.dashboard')
            ->with('success', 'Payment submitted successfully. It will be recorded after admin confirmation.');
    }
}
