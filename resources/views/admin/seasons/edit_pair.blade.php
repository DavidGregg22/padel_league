<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-bold text-lg text-blue-100">Edit Pair — {{ $season->name }}</h2>
            <a href="{{ route('admin.seasons.show', $season) }}" class="text-sm text-teal-400 hover:text-teal-300 font-medium">← Back</a>
        </div>
    </x-slot>

    <div class="py-6 max-w-md mx-auto px-4">
        <div class="bg-blue-900 shadow rounded-lg p-6">
            <p class="text-blue-400 text-sm mb-4">Change either player in this pair.</p>
            <form method="POST" action="{{ route('admin.seasons.pairs.update', [$season, $pair]) }}" class="space-y-4">
                @csrf @method('PATCH')
                <div>
                    <label class="block text-sm font-medium text-blue-200">Player 1</label>
                    <select name="player1_id" required
                            class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500">
                        @foreach($players as $p)
                            <option value="{{ $p->id }}" {{ $pair->player1_id == $p->id ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('player1_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-blue-200">Player 2</label>
                    <select name="player2_id" required
                            class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500">
                        @foreach($players as $p)
                            <option value="{{ $p->id }}" {{ $pair->player2_id == $p->id ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('player2_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.seasons.show', $season) }}" class="text-blue-400 hover:text-blue-200 text-sm">Cancel</a>
                    <button type="submit"
                            class="bg-teal-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-teal-700 transition-colors">
                        Save Pair
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
