<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#73917c]">Admin / Billing</p>
                <h2 class="mt-1 text-xl font-extrabold text-[#173f35]">Payment Records</h2>
            </div>
            <div class="flex flex-col items-start gap-2">
                <div class="flex flex-wrap items-end gap-3">
                    <form action="{{ route('payments.reminders.send') }}" method="POST" class="flex flex-wrap items-end gap-2" onsubmit="return confirm('Send payment reminders to tenants with an outstanding balance due on the selected date?')">
                        @csrf
                        <div>
                            <label for="reminder_date" class="block text-xs font-bold text-[#31584b]">Reminder date (month, day, year)</label>
                            <input id="reminder_date" name="reminder_date" type="date" value="{{ old('reminder_date', now()->toDateString()) }}" required class="mt-1 h-11 w-44 rounded-xl border-[#cbd8cd] text-sm">
                        </div>
                        <button type="submit" class="inline-flex h-11 items-center gap-2 whitespace-nowrap !bg-[#d9a746] px-4 text-sm font-extrabold text-[#173f35] shadow-lg shadow-[#173f35]/10 transition hover:-translate-y-0.5 hover:!bg-[#c99534]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span>Send payment reminders</span>
                        </button>
                    </form>
                    <a href="{{ route('payments.create') }}" class="inline-flex h-11 items-center gap-2 rounded-xl bg-[#173f35] px-4 text-sm font-extrabold text-white shadow-lg shadow-[#173f35]/10">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Add payment
                    </a>
                </div>
                <p class="text-xs text-[#718278]">Only tenants with an outstanding balance due on the selected date will be notified.</p>
                @error('reminder_date')<p class="text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </x-slot>

    <div class="min-h-[calc(100vh-7rem)] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            @if(session('success'))
                <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif
            @if($dueSoonCount > 0)
                <div class="mb-5 flex items-center gap-3 rounded-2xl border border-[#efd79e] bg-[#fff8e7] px-4 py-3 text-sm text-[#805c1e]" role="status">
                    <span class="grid h-10 w-10 flex-none place-items-center rounded-xl bg-[#f7edcf] text-[#a97924]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span><strong>Payment reminder:</strong> {{ $dueSoonCount }} outstanding {{ \Illuminate\Support\Str::plural('payment', $dueSoonCount) }} due within 5 days.</span>
                </div>
            @endif

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

            <section class="rounded-2xl border border-[#d3ded4] bg-white p-4 shadow-sm">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('payments.index') }}" class="rounded-lg px-3 py-2 text-sm font-bold {{ !request('status') ? 'bg-[#173f35] text-white' : 'bg-[#edf3e9] text-[#31584b]' }}">All history</a>
                        @foreach(['Paid' => 'Paid in full', 'Partial' => 'Partial', 'Unpaid' => 'Unpaid', 'Due' => 'Due'] as $status => $label)
                            <a href="{{ route('payments.index', ['status' => $status]) }}" class="rounded-lg px-3 py-2 text-sm font-bold {{ request('status') === $status ? 'bg-[#173f35] text-white' : 'bg-[#edf3e9] text-[#31584b]' }}">{{ $label }}</a>
                        @endforeach
                    </div>
                    <form action="{{ route('payments.index') }}" method="GET" class="flex gap-2">
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tenant..." class="w-full rounded-xl border-[#cbd8cd] text-sm lg:w-64">
                        <button type="submit" class="rounded-xl bg-[#31584b] px-4 text-sm font-bold text-white">Search</button>
                    </form>
                </div>
            </section>

            <section class="mt-5 overflow-hidden rounded-3xl border border-[#d3ded4] bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1180px] text-left">
                        <thead>
                            <tr class="text-xs uppercase tracking-wider text-[#718278]">
                                <th class="px-5 py-4">Tenant</th>
                                <th class="px-5 py-4">Period</th>
                                <th class="px-5 py-4">Total Due</th>
                                <th class="px-5 py-4">Amount Paid</th>
                                <th class="px-5 py-4">Balance</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Method / Due</th>
                                <th class="px-5 py-4">Tenant message</th>
                                <th class="px-5 py-4">Receipt</th>
                                <th class="px-5 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#edf1eb]">
                            @forelse($payments as $payment)
                                @php
                                    $displayStatus = $payment->payment_state;
                                @endphp
                                <tr class="hover:bg-[#fbfcf9]">
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-extrabold text-[#31584b]">{{ $payment->user->name ?? 'N/A' }}</p>
                                        <p class="mt-1 text-xs text-[#91a097]">{{ $payment->user->email ?? '' }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-semibold text-[#31584b]">{{ $payment->month_year }}</td>
                                    <td class="px-5 py-4 text-sm font-bold text-[#31584b]">
                                        ₱{{ number_format((float) $payment->amount, 2) }}
                                        @if((float) $payment->carried_in_amount > 0)
                                            <p class="mt-1 text-xs font-medium text-[#718278]">Includes ₱{{ number_format((float) $payment->carried_in_amount, 2) }} carried from a previous balance</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-sm font-extrabold text-[#173f35]">
                                        ₱{{ number_format((float) $payment->amount_paid, 2) }}
                                        @if((float) $payment->pending_amount > 0)
                                            <p class="mt-1 text-xs font-semibold text-[#9a6418]">₱{{ number_format((float) $payment->pending_amount, 2) }} awaiting confirmation</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-sm font-extrabold {{ $payment->balance > 0 ? 'text-[#9a6418]' : 'text-[#32795b]' }}">
                                        ₱{{ number_format($payment->balance, 2) }}
                                        @if($payment->carried_to_payment_id)
                                            <p class="mt-1 text-xs font-medium text-[#718278]">₱{{ number_format((float) $payment->carried_balance, 2) }} carried to {{ $payment->carriedTo?->month_year ?? 'the next billing period' }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold {{ $statusStyles[$displayStatus] ?? $statusStyles['Unpaid'] }}">{{ $displayStatus === 'Paid' ? 'Paid in full' : $displayStatus }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="text-sm text-[#31584b]">{{ $payment->payment_method ?: 'Not specified' }}</p>
                                        @if($payment->due_date)
                                            <p class="mt-1 text-xs text-[#91a097]">Due {{ $payment->due_date->format('M d, Y') }}</p>
                                            @if($payment->balance > 0)
                                                <p data-due-countdown data-due-date="{{ $payment->due_date->format('Y-m-d') }}" class="due-countdown mt-1 inline-flex rounded-full px-2 py-1 text-[11px] font-extrabold {{ $payment->days_until_due < 0 ? 'is-overdue' : 'is-reminder' }}" @if($payment->days_until_due > 5) hidden @endif>
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
                                    <td class="px-5 py-4">
                                        @if(trim((string) $payment->notes) !== '')
                                            <p class="max-w-[18rem] whitespace-pre-wrap break-words text-sm leading-6 text-[#31584b]">{{ $payment->notes }}</p>
                                        @else
                                            <span class="text-xs text-[#91a097]">No message</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            @if($payment->receipt_path)
                                                <div class="flex flex-col items-start gap-1">
                                                    <a href="{{ route('payments.uploaded-receipt.view', $payment) }}" class="text-xs font-bold text-[#31584b] underline underline-offset-4">View upload</a>
                                                    <a href="{{ route('payments.uploaded-receipt', $payment) }}" class="text-xs text-[#718278] underline underline-offset-4">Download</a>
                                                </div>
                                            @endif
                                            @if($payment->approval_status === 'Pending')
                                                <span class="text-xs font-semibold text-[#9a6418]">Admin review only</span>
                                            @elseif($payment->approval_status === 'Approved' && (float) $payment->amount_paid > 0)
                                                <a href="{{ route('payments.receipt', $payment) }}" class="rounded-lg bg-[#edf3e9] px-2.5 py-1.5 text-xs font-extrabold text-[#31584b]">PDF receipt</a>
                                            @endif
                                            @if(!$payment->receipt_path && (float) $payment->amount_paid <= 0)
                                                <span class="text-xs text-[#91a097]">No upload</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex justify-center gap-2">
                                            @if($payment->approval_status === 'Pending' && !$payment->carried_to_payment_id)
                                                <div class="flex flex-col gap-2">
                                                    <form action="{{ route('payments.approve', $payment) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="rounded-lg bg-[#32795b] px-3 py-2 text-xs font-extrabold text-white">Confirm payment</button>
                                                    </form>
                                                    <form action="{{ route('payments.reject', $payment) }}" method="POST" onsubmit="return confirm('Reject this payment submission? The balance will remain due.')">
                                                        @csrf
                                                        <button type="submit" class="rounded-lg bg-[#b84d32] px-3 py-2 text-xs font-extrabold text-white">Reject</button>
                                                    </form>
                                                </div>
                                            @elseif(!$payment->carried_to_payment_id)
                                                <a href="{{ route('payments.edit', $payment) }}" class="rounded-lg bg-[#d9a746] px-3 py-2 text-xs font-extrabold text-[#173f35]">Edit</a>
                                            @else
                                                <span class="px-2 py-2 text-xs font-bold text-[#718278]">Carried forward</span>
                                            @endif
                                            <form action="{{ route('payments.destroy', $payment) }}" method="POST" onsubmit="return confirm('Delete this payment record?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg bg-[#b84d32] px-3 py-2 text-xs font-extrabold text-white">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="px-5 py-12 text-center text-sm text-[#718278]">No payment records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
