<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('User Management') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Manage all registered accounts and role assignments.</p>
            </div>
            <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add New User
            </a>
        </div>
    </x-slot>

    <div class="py-10 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Alert Messages --}}
            @if(session('success'))
                <div class="flex items-center p-4 mb-6 text-sm text-green-800 border border-green-200 rounded-xl bg-green-50 shadow-sm">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="flex items-center p-4 mb-6 text-sm text-red-800 border border-red-200 rounded-xl bg-red-50 shadow-sm">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Main Table Container --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-100/70 border-b border-slate-200 text-xs uppercase font-bold text-slate-500 tracking-wider">
                                <th class="py-4 px-6">User Details</th>
                                <th class="py-4 px-6">Email Address</th>
                                <th class="py-4 px-6">System Role</th>
                                <th class="py-4 px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($users as $user)
                            <tr class="hover:bg-slate-50/80 transition-colors duration-150">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-slate-200 font-bold text-slate-600 flex items-center justify-center text-sm">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-800 text-sm">{{ $user->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-sm text-slate-600 font-medium">
                                    {{ $user->email }}
                                </td>
                                <td class="py-4 px-6">
                                    @if($user->role === 'admin')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 border border-purple-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span>
                                            Admin (Owner)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                            Tenant
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Edit Button --}}
                                        <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold py-1.5 px-3.5 rounded-lg shadow-sm hover:shadow transition-all duration-150">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </a>

                                        {{-- Delete Button --}}
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete {{ $user->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold py-1.5 px-3.5 rounded-lg shadow-sm hover:shadow transition-all duration-150">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>