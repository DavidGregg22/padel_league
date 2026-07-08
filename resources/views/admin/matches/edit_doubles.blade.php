<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-lg text-blue-100">Edit Doubles Match — {{ $season->name }}</h2></x-slot>
    <div class="py-6 max-w-lg mx-auto px-4">
        <div class="bg-blue-900 shadow rounded-lg p-6">
            <form method="POST" action="{{ route('admin.matches.doubles.update', [$club, $season, $match]) }}" class="space-y-4"
                  x-data="{ pair1: '{{ $match->pair1_id }}', pair2: '{{ $match->pair2_id }}' }">
                @csrf @method('PATCH')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-blue-200">Pair 1</label>
                        <select name="pair1_id" required x-model="pair1"
                                class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md">
                            @foreach($pairs as $pair)
                                <option value="{{ $pair->id }}" :disabled="pair2 == '{{ $pair->id }}'">{{ $pair->displayName() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-blue-200">Pair 2</label>
                        <select name="pair2_id" required x-model="pair2"
                                class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md">
                            @foreach($pairs as $pair)
                                <option value="{{ $pair->id }}" :disabled="pair1 == '{{ $pair->id }}'">{{ $pair->displayName() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div><label class="block text-sm font-medium text-blue-200">Sets</label><input type="text" name="sets_input" value="{{ old('sets_input', $match->setsDisplay()) }}" placeholder="6-4, 3-6, 7-5" class="mt-1 block w-full bg-blue-800 border-blue-700 text-white placeholder-blue-500 rounded-md">@error('sets_input')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="block text-sm font-medium text-blue-200">Date</label><input type="date" name="played_at" value="{{ old('played_at', $match->played_at?->format('Y-m-d')) }}" class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md"></div>
                <div class="flex flex-wrap justify-between items-center gap-3 pt-2">
                    <form method="POST" action="{{ route('admin.matches.doubles.destroy', [$club, $season, $match]) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button type="submit" class="text-red-400 text-sm hover:text-red-300 font-medium">Delete</button></form>
                    <div class="flex gap-3"><a href="{{ route('admin.seasons.show', [$club, $season]) }}" class="text-blue-400 text-sm">Cancel</a><button type="submit" class="bg-amber-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-amber-600">Save</button></div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
