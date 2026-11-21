<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-extrabold text-[#173f35]">Add Payment Record</h2>
    </x-slot>

    <div class="px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-xl">
            <form action="{{ route('payments.store') }}" method="POST" class="rounded-3xl border border-[#d3ded4] bg-white p-6 shadow-sm space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium">Select Tenant</label>
                    <select name="user_id" class="w-full mt-1 rounded-md border-gray-300" required>
                        @foreach($tenants as $tenant)
                            <option value="{{ $tenant->id }}">{{ $tenant->name }} ({{ $tenant->email }})</option>
                        @endforeach
                    </select>
                    @error('user_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium">Month & Year</label>
                    <input type="text" name="month_year" placeholder="e.g. October 2026" class="w-full mt-1 rounded-md border-gray-300" required>
                </div>

                <div>
                    <label class="block text-sm font-medium">Total Amount Due (₱)</label>
                    <input type="number" min="0.01" step="0.01" name="amount" class="w-full mt-1 rounded-md border-gray-300" required>
                    <p class="mt-1 text-xs text-gray-500">Any unpaid balance from earlier billing periods is added automatically.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium">Amount Paid (₱)</label>
                    <input type="number" min="0" step="0.01" name="amount_paid" value="0.00" class="w-full mt-1 rounded-md border-gray-300" required>
                </div>

                <div>
                    <label class="block text-sm font-medium">Status</label>
                    <select name="status" class="w-full mt-1 rounded-md border-gray-300" required>
                        <option value="Unpaid">Unpaid</option>
                        <option value="Paid">Paid in full</option>
                        <option value="Partial">Partial</option>
                        <option value="Due">Due</option>
                    </select>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium">Due Date</label>
                        <input type="date" name="due_date" class="w-full mt-1 rounded-md border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Payment Method</label>
                        <select name="payment_method" class="w-full mt-1 rounded-md border-gray-300">
                            <option value="">Not specified</option>
                            <option value="Paid in person">Paid in person</option>
                            <option value="Online payment">Online payment</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded">Save Payment</button>
            </form>
        </div>
    </div>
</x-app-layout>