<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                {{ __('Edit User') }}
            </h2>
            <a href="{{ route('users.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition duration-150">
                ← Back to Users
            </a>
        </div>
    </x-slot>

    <div class="py-10 bg-slate-50 min-h-screen">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('users.update', $user) }}" method="POST" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('name')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('email')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">New Password <span class="text-xs text-gray-400">(leave blank to keep current)</span></label>
                    <input type="password" name="password" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('password')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Assign System Role</label>
                    <select name="role" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="tenant" {{ old('role', $user->role) === 'tenant' ? 'selected' : '' }}>Tenant</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin (Owner)</option>
                    </select>
                    @error('role')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center space-x-3 pt-3">
                    <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 px-4 rounded-lg shadow-sm transition duration-150">
                        Update User
                    </button>
                    <a href="{{ route('users.index') }}" class="w-full text-center bg-gray-500 hover:bg-gray-600 text-white font-bold py-2.5 px-4 rounded-lg shadow-sm transition duration-150">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>