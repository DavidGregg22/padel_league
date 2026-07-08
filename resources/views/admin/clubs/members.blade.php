<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-bold text-lg text-blue-100">Members — {{ $club->name }}</h2>
            <a href="{{ route('club.league', $club) }}" class="text-sm text-teal-400 hover:text-teal-300 font-medium">← Back to league</a>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4 space-y-6">
        @if(session('success'))
            <div class="bg-emerald-900/50 text-emerald-300 border border-emerald-700 px-4 py-3 rounded-md text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-900/50 text-red-300 border border-red-700 px-4 py-3 rounded-md text-sm">{{ session('error') }}</div>
        @endif

        {{-- Invite --}}
        <div class="bg-blue-900 rounded-lg shadow p-5">
            <h3 class="font-bold text-blue-100 mb-3">Invite a New Member</h3>
            <form method="POST" action="{{ route('admin.members.invite', $club) }}" class="flex flex-wrap gap-3 items-end">
                @csrf
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs text-blue-400 mb-1">Email address</label>
                    <input type="email" name="email" required placeholder="player@example.com"
                        class="w-full bg-blue-800 border-blue-700 text-white text-sm rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500 placeholder-blue-500">
                    @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-teal-700 transition-colors whitespace-nowrap">
                    Send Invite
                </button>
            </form>
            <p class="text-blue-500 text-xs mt-2">They'll get a link to join. If they don't have an account, one will be created.</p>
        </div>

        {{-- Pending Invitations --}}
        @if($invitations->where('accepted_at', null)->count())
        <div class="bg-blue-900 rounded-lg shadow p-5">
            <h3 class="font-bold text-blue-100 mb-3">Pending Invitations</h3>
            <div class="space-y-2">
                @foreach($invitations->whereNull('accepted_at') as $inv)
                    <div class="flex justify-between items-center bg-blue-800/50 rounded-md px-4 py-2">
                        <span class="text-blue-200 text-sm">{{ $inv->email }}</span>
                        <form method="POST" action="{{ route('admin.members.invite.destroy', [$club, $inv]) }}" onsubmit="return confirm('Cancel this invite?')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 text-xs hover:text-red-300">Cancel</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Current Members --}}
        <div class="bg-blue-900 rounded-lg shadow overflow-hidden">
            <div class="bg-blue-800 px-5 py-3.5">
                <h3 class="font-bold text-blue-100">Current Members</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[400px]">
                    <thead class="bg-blue-800/50 text-blue-300 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Email</th>
                            <th class="px-4 py-3 text-center">Plays</th>
                            <th class="px-4 py-3 text-center">Role</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blue-800">
                        @foreach($members as $member)
                        <tr class="hover:bg-blue-800/40">
                            <td class="px-4 py-3 text-white font-medium">{{ $member->name }}</td>
                            <td class="px-4 py-3 text-blue-300 hidden sm:table-cell">{{ $member->email }}</td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="{{ route('admin.members.plays', [$club, $member]) }}">
                                    @csrf @method('PATCH')
                                    <select name="plays" onchange="this.form.submit()"
                                            class="text-xs bg-blue-800 border-blue-700 text-blue-200 rounded px-2 py-1 focus:ring-teal-500 focus:border-teal-500">
                                        <option value="both" {{ $member->pivot->plays === 'both' ? 'selected' : '' }}>Both</option>
                                        <option value="singles" {{ $member->pivot->plays === 'singles' ? 'selected' : '' }}>Singles</option>
                                        <option value="doubles" {{ $member->pivot->plays === 'doubles' ? 'selected' : '' }}>Doubles</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($member->pivot->role === 'admin')
                                    <span class="text-xs text-teal-400 bg-teal-900/50 px-2 py-0.5 rounded font-medium">Admin</span>
                                @else
                                    <span class="text-xs text-blue-400">Member</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                @if($member->id !== auth()->id())
                                    @if($member->pivot->role === 'member')
                                        <form method="POST" action="{{ route('admin.members.promote', [$club, $member]) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button class="text-teal-400 text-xs hover:text-teal-300 font-medium">Promote</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.members.demote', [$club, $member]) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button class="text-amber-400 text-xs hover:text-amber-300 font-medium">Demote</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.members.remove', [$club, $member]) }}" class="inline"
                                          onsubmit="return confirm('Remove {{ $member->name }}?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-400 text-xs hover:text-red-300 font-medium">Remove</button>
                                    </form>
                                @else
                                    <span class="text-xs text-blue-500">You</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
