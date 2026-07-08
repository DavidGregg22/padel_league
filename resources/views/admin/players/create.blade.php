<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-lg text-blue-100">Add Player</h2>
    </x-slot>

    <div class="py-6 max-w-lg mx-auto px-4">
        <div class="bg-blue-900 shadow rounded-lg p-6">
            <form method="POST" action="{{ route('admin.players.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-blue-200">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-blue-200">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500">
                    @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-blue-200">Password</label>
                    <input type="password" name="password" required
                        class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500">
                    @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-blue-200">Confirm Password</label>
                    <input type="password" name="password_confirmation" required
                        class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.players.index') }}" class="text-blue-400 hover:text-blue-200 text-sm">Cancel</a>
                    <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-teal-700 transition-colors">Add Player</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
