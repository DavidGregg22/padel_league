<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-bold text-lg text-blue-100">Edit Club — {{ $club->name }}</h2>
            <a href="{{ route('super.clubs.index') }}" class="text-sm text-teal-400 hover:text-teal-300 font-medium">← All Clubs</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-lg mx-auto px-4">
        <div class="bg-blue-900 shadow rounded-lg p-6">
            <form method="POST" action="{{ route('super.clubs.update', $club) }}" class="space-y-4">
                @csrf @method('PATCH')
                <div>
                    <label class="block text-sm font-medium text-blue-200">Club Name</label>
                    <input type="text" name="name" value="{{ old('name', $club->name) }}" required
                        class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md placeholder-blue-500">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-blue-200">Playtomic Club ID <span class="text-blue-500 font-normal">(optional)</span></label>
                    <input type="text" name="playtomic_tenant_id" value="{{ old('playtomic_tenant_id', $club->playtomic_tenant_id) }}"
                        class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md placeholder-blue-500"
                        placeholder="e.g. 2ab75436-9bb0-4e9c-9a6f-b12931a9ca4a">
                    <p class="text-blue-500 text-xs mt-1">Found in your Playtomic club URL. Enables court booking integration.</p>
                    @error('playtomic_tenant_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('super.clubs.index') }}" class="text-blue-400 hover:text-blue-200 text-sm">Cancel</a>
                    <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-teal-700 transition-colors">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
