<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-lg text-blue-100">Edit Singles Match — {{ $season->name }}</h2></x-slot>
    <div class="py-6 max-w-lg mx-auto px-4">
        <div class="bg-blue-900 shadow rounded-lg p-6">
            <form method="POST" action="{{ route('admin.matches.singles.update', [$club, $season, $match]) }}" class="space-y-4">@csrf @method('PATCH')
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-blue-200">Player 1</label><select name="player1_id" required class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md">@foreach($players as $p)<option value="{{ $p->id }}" {{ $match->player1_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach</select></div>
                    <div><label class="block text-sm font-medium text-blue-200">Player 2</label><select name="player2_id" required class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md">@foreach($players as $p)<option value="{{ $p->id }}" {{ $match->player2_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach</select></div>
                </div>
                <div><label class="block text-sm font-medium text-blue-200">Sets</label><input type="text" name="sets_input" value="{{ old('sets_input', $match->setsDisplay()) }}" placeholder="6-4, 3-6, 7-5" class="mt-1 block w-full bg-blue-800 border-blue-700 text-white placeholder-blue-500 rounded-md">@error('sets_input')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="block text-sm font-medium text-blue-200">Date</label><input type="date" name="played_at" value="{{ old('played_at', $match->played_at?->format('Y-m-d')) }}" class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md"></div>
                <div class="flex flex-wrap justify-between items-center gap-3 pt-2">
                    <form method="POST" action="{{ route('admin.matches.singles.destroy', [$club, $season, $match]) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button type="submit" class="text-red-400 text-sm hover:text-red-300 font-medium">Delete</button></form>
                    <div class="flex gap-3"><a href="{{ route('admin.seasons.show', [$club, $season]) }}" class="text-blue-400 text-sm">Cancel</a><button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-teal-700">Save</button></div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
