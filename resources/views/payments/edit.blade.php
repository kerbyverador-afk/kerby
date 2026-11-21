<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-extrabold text-[#173f35]">Edit Payment Status</h2>
    </x-slot>

    <div class="px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-xl">
            <form action="{{ route('payments.update', $payment) }}" method="POST" class="rounded-3xl border border-[#d3ded4] bg-white p-6 shadow-sm space-y-4">
                @csrf
                @method('PUT')

                <!-- Updated Tenant Field (Gamit ang $tenants para mag-match sa Controller) -->
                <div>
                    <label class="block text-sm font-medium">Tenant</label>
                    <select name="user_id" class="w-full mt-1 rounded-md border-gray-300" required>
                        @foreach($tenants as $tenant)
                            <option value="{{ $tenant->id }}" {{ $payment->user_id == $tenant->id ? 'selected' : '' }}>
                                {{ $tenant->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium">Month & Year</label>
                    <input type="text" name="month_year" value="{{ $payment->month_year }}" class="w-full mt-1 rounded-md border-gray-300" required>
                </div>

                <div>
                    <label class="block text-sm font-medium">Total Amount Due (₱)</label>
                    <input type="number" min="0.01" step="0.01" name="amount" value="{{ $payment->amount }}" class="w-full mt-1 rounded-md border-gray-300" required>
                    @if((float) $payment->carried_in_amount > 0)
                        <p class="mt-1 text-xs text-gray-500">This total includes ₱{{ number_format((float) $payment->carried_in_amount, 2) }} carried from earlier billing periods.</p>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium">Amount Paid (₱)</label>
                    <input type="number" min="0" step="0.01" name="amount_paid" value="{{ $payment->amount_paid }}" class="w-full mt-1 rounded-md border-gray-300" required>
                </div>

                <div>
                    <label class="block text-sm font-medium">Status</label>
                    <select name="status" class="w-full mt-1 rounded-md border-gray-300" required>
                        <option value="Unpaid" {{ $payment->status === 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="Paid" {{ $payment->status === 'Paid' ? 'selected' : '' }}>Paid in full</option>
                        <option value="Partial" {{ $payment->status === 'Partial' ? 'selected' : '' }}>Partial</option>
                        <option value="Due" {{ $payment->status === 'Due' ? 'selected' : '' }}>Due</option>
                    </select>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium">Due Date</label>
                        <input type="date" name="due_date" value="{{ $payment->due_date?->format('Y-m-d') }}" class="w-full mt-1 rounded-md border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Payment Method</label>
                        <select name="payment_method" class="w-full mt-1 rounded-md border-gray-300">
                            <option value="">Not specified</option>
                            <option value="Paid in person" {{ $payment->payment_method === 'Paid in person' ? 'selected' : '' }}>Paid in person</option>
                            <option value="Online payment" {{ $payment->payment_method === 'Online payment' ? 'selected' : '' }}>Online payment</option>
                        </select>
                    </div>
                </div>

                @if($payment->receipt_path)
                    <a href="{{ route('payments.uploaded-receipt', $payment) }}" class="block rounded-xl border border-[#d7e0d8] bg-[#f7f9f4] px-4 py-3 text-sm font-bold text-[#31584b]">
                        Download uploaded receipt
                    </a>
                @endif

                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded">Update Payment</button>
            </form>
        </div>
    </div>
</x-app-layout>