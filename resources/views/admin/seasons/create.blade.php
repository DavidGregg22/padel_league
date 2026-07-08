<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-lg text-blue-100">Create Season — {{ $club->name }}</h2>
    </x-slot>
    <div class="py-6 max-w-lg mx-auto px-4">
        <div class="bg-blue-900 shadow rounded-lg p-6">
            <form method="POST" action="{{ route('admin.seasons.store', $club) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-blue-200">Season Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Spring 2026" class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500 placeholder-blue-500">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-blue-200">Year</label>
                    <input type="number" name="year" value="{{ old('year', date('Y')) }}" required class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500">
                    @error('year')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.seasons.index', $club) }}" class="text-blue-400 hover:text-blue-200 text-sm">Cancel</a>
                    <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-teal-700 transition-colors">Create Season</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
