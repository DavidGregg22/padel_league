<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-lg text-blue-100">🎾 My Clubs</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4">
            @if($clubs->isEmpty())
                <div class="bg-blue-900 rounded-lg shadow p-8 text-center text-blue-300">
                    You haven't been invited to any clubs yet. Ask a club admin to send you an invite.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($clubs as $club)
                        <a href="{{ route('club.league', $club) }}"
                           class="bg-blue-900 rounded-lg shadow p-6 hover:bg-blue-800/80 transition-colors block">
                            <h3 class="text-white font-bold text-lg">{{ $club->name }}</h3>
                            <p class="text-blue-400 text-sm mt-1">{{ $club->users_count ?? $club->users()->count() }} members</p>
                            @if(auth()->user()->isClubAdmin($club))
                                <span class="inline-block mt-2 text-xs text-teal-400 font-medium bg-teal-900/50 px-2 py-0.5 rounded">Admin</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
