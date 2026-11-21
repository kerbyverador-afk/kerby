<div>
    <!-- You must be the change you wish to see in the world. - Mahatma Gandhi -->
</div>
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#73917c]">Tenant Portal</p>
                <h2 class="mt-1 text-xl font-extrabold text-[#173f35]">Payment history</h2>
            </div>
            <a href="{{ route('tenant.dashboard') }}" class="rounded-xl border border-[#cbd8cd] bg-white px-4 py-2.5 text-sm font-bold text-[#31584b]">Back to dashboard</a>
        </div>
    </x-slot>

    <div class="min-h-[calc(100vh-7rem)] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <section class="overflow-hidden rounded-3xl border border-[#d3ded4] bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#edf1eb] px-5 py-5 sm:px-6">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#73917c]">Your records</p>
                        <h1 class="mt-1 text-xl font-extrabold text-[#173f35]">Payment history</h1>
                    </div>
                    <span class="rounded-full bg-[#edf3e9] px-3 py-1.5 text-xs font-bold text-[#31584b]">{{ $payments->count() }} total</span>
                </div>

                @php
                    $statusStyles = [
                        'Paid' => 'bg-[#dceee3] text-[#32795b]',
                        'Partial' => 'bg-[#f8e4bd] text-[#9a6418]',
                        'Due' => 'bg-[#f8e4bd] text-[#9a6418]',
                        'Unpaid' => 'bg-[#fee4d7] text-[#b84d32]',
                        'Carried forward' => 'bg-[#e9eef5] text-[#4c5f78]',
                        'Pending approval' => 'bg-[#fff1c9] text-[#8b651e]',
                        'Rejected' => 'bg-[#fee4d7] text-[#b84d32]',
                    ];
                @endphp

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left">
                        <thead>
                            <tr class="text-xs uppercase tracking-wider text-[#718278]">
                                <th class="px-5 py-4">Period / Due date</th>
                                <th class="px-5 py-4">Total due</th>
                                <th class="px-5 py-4">Amount paid</th>
                                <th class="px-5 py-4">Balance</th>
                                <th class="px-5 py-4">Method</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Receipt</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#edf1eb]">
                            @forelse($payments as $payment)
                                @php($displayStatus = $payment->payment_state)
                                <tr>
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-bold text-[#31584b]">{{ $payment->month_year }}</p>
                                        @if($payment->due_date)
                                            <p class="mt-1 text-xs text-[#91a097]">Due {{ $payment->due_date->format('M d, Y') }}</p>
                                            @if($payment->balance > 0)
                                                <p data-due-countdown data-due-date="{{ $payment->due_date->format('Y-m-d') }}" class="due-countdown mt-1 inline-flex rounded-full px-2 py-1 text-[11px] font-extrabold {{ $payment->days_until_due < 0 ? 'is-overdue' : 'is-reminder' }}" @if($payment->days_until_due > 5) hidden @endif aria-live="polite">
                                                    @if($payment->days_until_due < 0)
                                                        Overdue by {{ abs($payment->days_until_due) }} {{ \Illuminate\Support\Str::plural('day', abs($payment->days_until_due)) }}
                                                    @elseif($payment->days_until_due === 0)
                                                        Due today
                                                    @else
                                                        Due in {{ $payment->days_until_due }} {{ \Illuminate\Support\Str::plural('day', $payment->days_until_due) }}
                                                    @endif
                                                </p>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-sm font-bold text-[#31584b]">
                                        ₱{{ number_format((float) $payment->amount, 2) }}
                                        @if((float) $payment->carried_in_amount > 0)
                                            <p class="mt-1 text-xs font-medium text-[#718278]">Includes ₱{{ number_format((float) $payment->carried_in_amount, 2) }} carried from a previous balance</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-sm font-extrabold text-[#32795b]">
                                        ₱{{ number_format((float) $payment->amount_paid, 2) }}
                                        @if((float) $payment->pending_amount > 0)
                                            <p class="mt-1 text-xs font-semibold text-[#9a6418]">₱{{ number_format((float) $payment->pending_amount, 2) }} awaiting admin confirmation</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-sm font-extrabold {{ $payment->balance > 0 ? 'text-[#9a6418]' : 'text-[#32795b]' }}">
                                        ₱{{ number_format($payment->balance, 2) }}
                                        @if($payment->carried_to_payment_id)
                                            <p class="mt-1 text-xs font-medium text-[#718278]">₱{{ number_format((float) $payment->carried_balance, 2) }} carried to {{ $payment->carriedTo?->month_year ?? 'the next billing period' }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-sm text-[#718278]">{{ $payment->payment_method ?: 'Not specified' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold {{ $statusStyles[$displayStatus] ?? $statusStyles['Unpaid'] }}">{{ $displayStatus === 'Paid' ? 'Paid in full' : $displayStatus }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($payment->approval_status === 'Pending')
                                            <span class="text-xs font-semibold text-[#9a6418]">Receipt visible after admin confirmation</span>
                                        @elseif($payment->approval_status === 'Rejected')
                                            <span class="text-xs font-semibold text-[#b84d32]">Submission rejected; balance remains due</span>
                                        @else
                                            <div class="flex flex-col items-start gap-2">
                                                @if((float) $payment->amount_paid > 0)
                                                    <a href="{{ route('payments.receipt', $payment) }}" class="inline-flex rounded-lg bg-[#edf3e9] px-3 py-2 text-xs font-extrabold text-[#31584b] hover:bg-[#dceee3]">Download PDF</a>
                                                @endif
                                                @if($payment->receipt_path)
                                                    <a href="{{ route('payments.uploaded-receipt.view', $payment) }}" class="text-xs font-bold text-[#31584b] underline underline-offset-4">View upload</a>
                                                @endif
                                                @if((float) $payment->amount_paid <= 0 && !$payment->receipt_path)
                                                    <span class="text-xs text-[#91a097]">No receipt</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-[#718278]">You don't have any payment history yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
