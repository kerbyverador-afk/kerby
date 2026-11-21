<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#73917c]">Tenant Portal</p>
                <h2 class="mt-1 text-xl font-extrabold text-[#173f35]">Welcome, {{ Auth::user()->name }}!</h2>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full border border-[#cbd8cd] bg-[#f4f7ef] px-3 py-1.5 text-xs font-bold text-[#31584b]">
                <span class="h-2 w-2 rounded-full bg-[#73917c]"></span>
                Account active
            </span>
        </div>
    </x-slot>

    <div class="min-h-[calc(100vh-7rem)] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            @if(session('success'))
                <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('success') }}
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

            <div class="grid gap-6 lg:grid-cols-[1.35fr_0.65fr]">
                <section class="relative overflow-hidden rounded-3xl bg-[#173f35] p-6 text-white sm:p-8">
                    <div class="relative z-10 max-w-xl">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#c9dac1]">My payment status</p>
                        <h1 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">
                            {{ $currentStatus === 'Paid' ? 'Payment complete' : ($currentStatus === 'Pending approval' ? 'Payment awaiting confirmation' : ($currentStatus === 'Rejected' ? 'Payment needs attention' : ($currentStatus === 'Due' ? 'Payment is due' : ($currentStatus === 'Partial' ? 'Partial payment' : 'Payment pending')))) }}
                        </h1>
                        <p class="mt-3 max-w-md text-sm leading-6 text-[#d8e3d6]">
                            {{ $currentStatus === 'Paid' ? 'Your latest payment is paid in full.' : ($currentStatus === 'Pending approval' ? 'Your submitted amount will be applied to the balance after admin confirmation.' : ($currentStatus === 'Rejected' ? 'Your submission was rejected. The outstanding balance remains due; please contact the admin or submit again.' : ($currentStatus === 'Partial' ? 'A partial payment is recorded. Check your remaining balance.' : 'View reminders and submit your boarding-house payment here.'))) }}
                        </p>
                    </div>

                    <div class="relative z-10 mt-8 flex flex-wrap items-end justify-between gap-5">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#c9dac1]">Current status</p>
                            <span class="mt-2 inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-extrabold {{ $statusStyles[$currentStatus] ?? $statusStyles['Unpaid'] }}">
                                <span class="h-2.5 w-2.5 rounded-full bg-current"></span>
                                {{ $currentStatus === 'Paid' ? 'Paid in full' : $currentStatus }}
                            </span>
                        </div>
                        @if($latestPayment)
                            <div class="rounded-2xl border border-white/15 bg-white/10 px-5 py-4 text-right backdrop-blur-sm">
                                <p class="text-xs font-bold uppercase tracking-wider text-[#c9dac1]">{{ $latestPayment->month_year }}</p>
                                <p class="mt-1 text-sm text-[#d8e3d6]">Remaining balance</p>
                                <p class="text-2xl font-extrabold">₱{{ number_format($latestPayment->balance, 2) }}</p>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="rounded-3xl border border-[#d3ded4] bg-white p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#73917c]">My account</p>
                    <h2 class="mt-1 text-xl font-extrabold text-[#173f35]">{{ Auth::user()->name }}</h2>
                    <dl class="mt-5 divide-y divide-[#edf1eb]">
                        <div class="flex items-center justify-between gap-4 py-4">
                            <dt class="text-sm text-[#718278]">Email</dt>
                            <dd class="max-w-[180px] truncate text-right text-sm font-bold text-[#31584b]">{{ Auth::user()->email }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-4">
                            <dt class="text-sm text-[#718278]">Payment records</dt>
                            <dd class="text-sm font-extrabold text-[#31584b]">{{ $payments->count() }}</dd>
                        </div>
                        @if($latestPayment)
                            <div class="flex items-center justify-between gap-4 py-4">
                                <dt class="text-sm text-[#718278]">Latest amount paid</dt>
                                <dd class="text-sm font-extrabold text-[#32795b]">₱{{ number_format((float) $latestPayment->amount_paid, 2) }}</dd>
                            </div>
                            @if((float) $latestPayment->pending_amount > 0)
                                <div class="flex items-center justify-between gap-4 py-4">
                                    <dt class="text-sm text-[#718278]">Awaiting confirmation</dt>
                                    <dd class="text-sm font-extrabold text-[#9a6418]">₱{{ number_format((float) $latestPayment->pending_amount, 2) }}</dd>
                                </div>
                            @endif
                        @endif
                    </dl>
                </section>
            </div>

            @if($notifications->isNotEmpty())
                <section class="mt-6 overflow-hidden rounded-3xl border border-[#d3ded4] bg-white shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#edf1eb] px-5 py-4 sm:px-6">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#73917c]">In-app updates</p>
                            <h2 class="mt-1 text-lg font-extrabold text-[#173f35]">Notifications</h2>
                        </div>
                        <span class="rounded-full bg-[#edf3e9] px-3 py-1.5 text-xs font-bold text-[#31584b]">{{ $unreadNotificationCount }} unread</span>
                    </div>
                    <div class="divide-y divide-[#edf1eb]">
                        @foreach($notifications as $notification)
                            <article class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 sm:px-6">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-sm font-extrabold text-[#31584b]">{{ $notification->data['title'] ?? 'Boarding-house update' }}</h3>
                                        @if(!$notification->read_at)
                                            <span class="rounded-full bg-[#fff1c9] px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-[#8b651e]">New</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm leading-6 text-[#718278]">{{ $notification->data['message'] ?? '' }}</p>
                                    <p class="mt-1 text-xs text-[#91a097]">{{ $notification->created_at->format('M d, Y g:i A') }}</p>
                                </div>
                                @if(!$notification->read_at)
                                    <form action="{{ route('tenant.notifications.read', $notification->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="rounded-lg border border-[#cbd8cd] px-3 py-2 text-xs font-bold text-[#31584b] transition hover:bg-[#edf3e9]">Mark as read</button>
                                    </form>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="mt-6 overflow-hidden rounded-3xl border border-[#d3ded4] bg-white shadow-sm">
                <div class="flex flex-col justify-between gap-4 border-b border-[#edf1eb] p-5 sm:flex-row sm:items-center sm:px-6">
                    <div class="flex items-start gap-3">
                        <span class="grid h-11 w-11 flex-none place-items-center rounded-xl bg-[#fff1c9] text-[#a97924]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#a97924]">Reminders</p>
                            <h2 class="mt-1 text-xl font-extrabold text-[#173f35]">Upcoming payments</h2>
                            <p class="mt-1 text-sm text-[#718278]">Countdown reminders start 5 days before the due date.</p>
                        </div>
                    </div>
                    <a href="{{ route('tenant.payments.create') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#173f35] px-4 text-sm font-extrabold text-white shadow-lg shadow-[#173f35]/10 transition hover:-translate-y-0.5">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Submit a payment
                    </a>
                </div>

                @if($reminderPayments->isNotEmpty())
                    <div class="divide-y divide-[#edf1eb]">
                        @foreach($reminderPayments as $payment)
                            <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 sm:px-6">
                                <div>
                                    <p class="font-extrabold text-[#31584b]">{{ $payment->month_year }}</p>
                                    <p class="mt-1 text-sm text-[#718278]">
                                        Balance ₱{{ number_format($payment->balance, 2) }}
                                        <span class="px-1 text-[#c2cdc4]">·</span>
                                        Due {{ $payment->due_date->format('M d, Y') }}
                                    </p>
                                    @if((float) $payment->carried_in_amount > 0)
                                        <p class="mt-1 text-xs text-[#718278]">Includes ₱{{ number_format((float) $payment->carried_in_amount, 2) }} carried from a previous balance.</p>
                                    @endif
                                </div>
                                <p data-due-countdown data-due-date="{{ $payment->due_date->format('Y-m-d') }}" class="due-countdown rounded-full px-3 py-1.5 text-xs font-extrabold {{ $payment->days_until_due < 0 ? 'is-overdue' : 'is-reminder' }}" aria-live="polite">
                                    @if($payment->days_until_due < 0)
                                        Overdue by {{ abs($payment->days_until_due) }} {{ \Illuminate\Support\Str::plural('day', abs($payment->days_until_due)) }}
                                    @elseif($payment->days_until_due === 0)
                                        Due today
                                    @else
                                        Due in {{ $payment->days_until_due }} {{ \Illuminate\Support\Str::plural('day', $payment->days_until_due) }}
                                    @endif
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-6 text-sm text-[#718278] sm:px-6">
                        No outstanding payment is due within the next 5 days.
                    </div>
                @endif
            </section>

            <a href="{{ route('tenant.payments.history') }}" class="mt-6 flex items-center justify-between gap-4 rounded-3xl border border-[#d3ded4] bg-white p-5 shadow-sm transition hover:border-[#9db6a2] hover:shadow-md sm:px-6">
                <span>
                    <span class="block text-xs font-bold uppercase tracking-[0.14em] text-[#73917c]">Payment history</span>
                    <span class="mt-1 block text-lg font-extrabold text-[#173f35]">View payment history</span>
                    <span class="mt-1 block text-sm text-[#718278]">See all your payment records, balances, and receipts.</span>
                </span>
                <svg class="h-6 w-6 flex-none text-[#31584b]" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>
    </div>
</x-app-layout>
