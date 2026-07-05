<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Edit Singles Match — {{ $season->name }}</h2>
    </x-slot>

    <div class="py-8 max-w-lg mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('admin.matches.singles.update', [$season, $match]) }}" class="space-y-4">
                @csrf @method('PATCH')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Player 1</label>
                        <select name="player1_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach($players as $p)
                                <option value="{{ $p->id }}" {{ $match->player1_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Player 2</label>
                        <select name="player2_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach($players as $p)
                                <option value="{{ $p->id }}" {{ $match->player2_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sets won by Player 1</label>
                        <input type="number" name="score1" min="0" max="9" value="{{ old('score1', $match->score1) }}" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('score1')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sets won by Player 2</label>
                        <input type="number" name="score2" min="0" max="9" value="{{ old('score2', $match->score2) }}" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('score2')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date Played</label>
                    <input type="date" name="played_at" value="{{ old('played_at', $match->played_at?->format('Y-m-d')) }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="flex justify-between items-center">
                    <form method="POST" action="{{ route('admin.matches.singles.destroy', [$season, $match]) }}"
                          onsubmit="return confirm('Delete this match?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 text-sm hover:underline">Delete match</button>
                    </form>
                    <div class="flex gap-3">
                        <a href="{{ route('admin.seasons.show', $season) }}" class="text-gray-600 hover:underline text-sm">Cancel</a>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
