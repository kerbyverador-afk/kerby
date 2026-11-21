<div>
    <!-- He who is contented is rich. - Laozi -->
</div>
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#73917c]">Reminders / Payments</p>
                <h2 class="mt-1 text-xl font-extrabold text-[#173f35]">Submit a payment</h2>
            </div>
            <a href="{{ route('tenant.dashboard') }}" class="rounded-xl border border-[#cbd8cd] bg-white px-4 py-2.5 text-sm font-bold text-[#31584b]">Back to dashboard</a>
        </div>
    </x-slot>

    <div class="min-h-[calc(100vh-7rem)] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl">
            <section class="rounded-3xl border border-[#d3ded4] bg-white p-6 shadow-sm sm:p-8">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#73917c]">Payment details</p>
                <h1 class="mt-1 text-2xl font-extrabold text-[#173f35]">Make a payment submission</h1>
                <p class="mt-2 text-sm leading-6 text-[#718278]">Choose full or partial payment and attach your receipt. The total due is loaded from your admin-assigned outstanding payment for the selected month.</p>

                <form method="POST" action="{{ route('tenant.payments.store') }}" enctype="multipart/form-data" class="mt-6 grid gap-5" data-payment-coverage data-due-amounts="{{ json_encode($dueAmountsByMonth) }}" data-pending-months="{{ json_encode($pendingMonthsByMonth) }}">
                    @csrf

                    <div>
                        <label for="month_year" class="text-sm font-bold text-[#31584b]">Month and year</label>
                        <input id="month_year" name="month_year" type="text" value="{{ old('month_year', now()->format('F Y')) }}" required class="mt-2 w-full rounded-xl border-[#cbd8cd] text-sm">
                        @error('month_year')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="amount" class="text-sm font-bold text-[#31584b]">Total amount due (₱)</label>
                            <input id="amount" name="amount" type="number" min="0.01" step="0.01" value="" required readonly data-amount-due class="payment-amount-field mt-2 w-full rounded-xl border-[#cbd8cd] text-sm">
                            <p data-amount-due-help class="mt-1 text-xs text-[#91a097]" aria-live="polite">Checking the admin-assigned amount for this month.</p>
                            @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="amount_paid" class="text-sm font-bold text-[#31584b]">Amount paid now (₱)</label>
                            <input id="amount_paid" name="amount_paid" type="number" min="0.01" step="0.01" value="{{ old('amount_paid', '') }}" required data-amount-paid class="payment-amount-field mt-2 w-full rounded-xl border-[#cbd8cd] text-sm">
                            <p data-amount-paid-help class="mt-1 text-xs text-[#91a097]">Choose a payment option to enter an amount.</p>
                            @error('amount_paid')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="due_date" class="text-sm font-bold text-[#31584b]">Due date</label>
                            <input id="due_date" name="due_date" type="date" value="{{ old('due_date') }}" class="mt-2 w-full rounded-xl border-[#cbd8cd] text-sm">
                            @error('due_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <fieldset>
                        <legend class="text-sm font-bold text-[#31584b]">Payment coverage</legend>
                        <div class="mt-2 grid gap-3 sm:grid-cols-2">
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-[#cbd8cd] p-4 text-sm font-semibold text-[#31584b]">
                                <input type="radio" name="payment_coverage" value="full" {{ old('payment_coverage', 'full') === 'full' ? 'checked' : '' }} class="text-[#173f35] focus:ring-[#73917c]">
                                Paid in full
                            </label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-[#cbd8cd] p-4 text-sm font-semibold text-[#31584b]">
                                <input type="radio" name="payment_coverage" value="partial" {{ old('payment_coverage') === 'partial' ? 'checked' : '' }} class="text-[#173f35] focus:ring-[#73917c]">
                                Partial payment
                            </label>
                        </div>
                        @error('payment_coverage')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </fieldset>

                    <fieldset>
                        <legend class="text-sm font-bold text-[#31584b]">Payment method</legend>
                        <div class="mt-2 grid gap-3 sm:grid-cols-2">
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-[#cbd8cd] p-4 text-sm font-semibold text-[#31584b]">
                                <input type="radio" name="payment_method" value="Paid in person" {{ old('payment_method', 'Paid in person') === 'Paid in person' ? 'checked' : '' }} class="text-[#173f35] focus:ring-[#73917c]">
                                Paid in person
                            </label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-[#cbd8cd] p-4 text-sm font-semibold text-[#31584b]">
                                <input type="radio" name="payment_method" value="Online payment" {{ old('payment_method') === 'Online payment' ? 'checked' : '' }} class="text-[#173f35] focus:ring-[#73917c]">
                                Online payment
                            </label>
                        </div>
                        @error('payment_method')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </fieldset>

                    <div>
                        <label for="receipt" class="text-sm font-bold text-[#31584b]">Upload receipt (optional)</label>
                        <input id="receipt" name="receipt" type="file" accept=".jpg,.jpeg,.png,.pdf" class="mt-2 block w-full text-sm text-[#718278] file:mr-3 file:rounded-lg file:border-0 file:bg-[#edf3e9] file:px-3 file:py-2 file:text-sm file:font-bold file:text-[#31584b]">
                        <p class="mt-1 text-xs text-[#91a097]">Accepted formats: JPG, PNG, or PDF. Maximum 5 MB.</p>
                        @error('receipt')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="notes" class="text-sm font-bold text-[#31584b]">Message for admin (optional)</label>
                        <textarea id="notes" name="notes" rows="3" maxlength="1000" class="mt-2 w-full rounded-xl border-[#cbd8cd] text-sm" placeholder="Add a note or payment message for admin review">{{ old('notes') }}</textarea>
                        @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <details class="terms-details p-4" open>
                        <summary>Terms &amp; Conditions for Payment Submissions</summary>
                        <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm leading-6">
                            <li>Choose the correct billing period, payment method, and amount. A full payment uses the outstanding balance shown; a partial payment must be less than that balance.</li>
                            <li>Payment submissions remain pending until the admin reviews and approves them. Pending amounts do not count as confirmed payments, and receipts become available after approval.</li>
                            <li>Any unpaid balance after a partial payment remains due and may be carried forward to the next billing period.</li>
                            <li>Uploaded receipts and messages you provide are shared with the boarding-house admin for payment verification. Do not include passwords, PINs, one-time codes, or other sensitive account information in your message.</li>
                            <li>Ensure your payment details are accurate. Contact the admin if a submitted payment or balance needs correction.</li>
                        </ol>
                    </details>

                    <button type="submit" data-payment-submit class="h-12 rounded-xl bg-[#173f35] text-sm font-extrabold text-white shadow-lg shadow-[#173f35]/10 transition hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-60">Submit payment</button>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
