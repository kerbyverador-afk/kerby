<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create User</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('users.store') }}" method="POST" class="bg-white p-6 rounded-lg shadow-sm space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium">Name</label>
                    <input type="text" name="name" class="w-full mt-1 rounded-md border-gray-300" required>
                </div>

                <div>
                    <label class="block text-sm font-medium">Email</label>
                    <input type="email" name="email" class="w-full mt-1 rounded-md border-gray-300" required>
                </div>

                <div>
                    <label class="block text-sm font-medium">Password</label>
                    <input type="password" name="password" class="w-full mt-1 rounded-md border-gray-300" required>
                </div>

                <div>
                    <label class="block text-sm font-medium">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="w-full mt-1 rounded-md border-gray-300" required>
                </div>

                <div>
                    <label class="block text-sm font-medium">Assign Role</label>
                    <select name="role" class="w-full mt-1 rounded-md border-gray-300" required>
                        <option value="tenant">Tenant</option>
                        <option value="admin">Admin (Owner)</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded">Create User</button>
            </form>
        </div>
    </div>
</x-app-layout>