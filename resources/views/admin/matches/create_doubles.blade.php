<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-lg text-blue-100">Add Doubles Match — {{ $season->name }}</h2>
    </x-slot>

    <div class="py-6 max-w-lg mx-auto px-4">
        <div class="bg-blue-900 shadow rounded-lg p-6">
            <form method="POST" action="{{ route('admin.matches.doubles.store', $season) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-blue-200">Pair 1</label>
                        <select name="pair1_id" required class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500">
                            <option value="">Select pair</option>
                            @foreach($pairs as $pair)
                                <option value="{{ $pair->id }}" {{ old('pair1_id') == $pair->id ? 'selected' : '' }}>{{ $pair->displayName() }}</option>
                            @endforeach
                        </select>
                        @error('pair1_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-blue-200">Pair 2</label>
                        <select name="pair2_id" required class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500">
                            <option value="">Select pair</option>
                            @foreach($pairs as $pair)
                                <option value="{{ $pair->id }}" {{ old('pair2_id') == $pair->id ? 'selected' : '' }}>{{ $pair->displayName() }}</option>
                            @endforeach
                        </select>
                        @error('pair2_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-blue-200">Sets (games per set)</label>
                    <input type="text" name="sets_input" value="{{ old('sets_input') }}"
                        placeholder="e.g. 6-4, 3-6, 7-5"
                        class="mt-1 block w-full bg-blue-800 border-blue-700 text-white placeholder-blue-500 rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500">
                    <p class="text-blue-500 text-xs mt-1">Enter each set as P1 games - P2 games, separated by commas. Sets are auto-calculated.</p>
                    @error('sets_input')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-blue-200">Date Played</label>
                    <input type="date" name="played_at" value="{{ old('played_at', date('Y-m-d')) }}"
                        class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.seasons.show', $season) }}" class="text-blue-400 hover:text-blue-200 text-sm">Cancel</a>
                    <button type="submit" class="bg-amber-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-amber-600 transition-colors">Save Match</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
