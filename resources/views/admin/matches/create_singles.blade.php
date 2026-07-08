<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-lg text-blue-100">Add Singles Match — {{ $season->name }}</h2>
    </x-slot>

    <div class="py-6 max-w-lg mx-auto px-4">
        <div class="bg-blue-900 shadow rounded-lg p-6">
            <form method="POST" action="{{ route('admin.matches.singles.store', $season) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-blue-200">Player 1</label>
                        <select name="player1_id" required class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500">
                            <option value="">Select player</option>
                            @foreach($players as $p)
                                <option value="{{ $p->id }}" {{ old('player1_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                        @error('player1_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-blue-200">Player 2</label>
                        <select name="player2_id" required class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500">
                            <option value="">Select player</option>
                            @foreach($players as $p)
                                <option value="{{ $p->id }}" {{ old('player2_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                        @error('player2_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
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
                    <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-teal-700 transition-colors">Save Match</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
