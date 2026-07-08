@php
    $currentClub = request()->route('club');
@endphp
<nav x-data="{ open: false }" class="bg-slate-800 border-b border-slate-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14">
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-white font-bold text-lg tracking-tight">
                    🎾 Padel
                </a>

                <!-- Desktop Nav Links -->
                <div class="hidden sm:flex sm:items-center sm:ms-8 sm:gap-1">
                    @auth
                        @if($currentClub)
                            <a href="{{ route('club.league', $currentClub) }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-700 transition-colors
                                      {{ request()->routeIs('club.league') || request()->routeIs('club.season') ? 'bg-slate-700 text-white' : '' }}">
                                {{ $currentClub->name }}
                            </a>
                            @if(auth()->user()->isClubAdmin($currentClub))
                                <a href="{{ route('admin.seasons.index', $currentClub) }}"
                                   class="px-3 py-2 rounded-md text-sm font-medium transition-colors
                                          {{ request()->routeIs('admin.seasons*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-700' }}">
                                    Seasons
                                </a>
                                <a href="{{ route('admin.members', $currentClub) }}"
                                   class="px-3 py-2 rounded-md text-sm font-medium transition-colors
                                          {{ request()->routeIs('admin.members*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-700' }}">
                                    Members
                                </a>
                            @endif
                        @endif
                        @if(auth()->user()->clubs()->count() > 1)
                            <a href="{{ route('home') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-700 transition-colors">
                                My Clubs
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Right side -->
            <div class="hidden sm:flex sm:items-center sm:gap-3">
                @auth
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-1.5 px-3 py-2 rounded-md text-sm text-slate-300 hover:text-white hover:bg-slate-700 transition-colors">
                            {{ Auth::user()->name }}
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                        @if(auth()->user()->isAdmin())
                            <x-dropdown-link :href="route('super.clubs.index')">Super Admin</x-dropdown-link>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Log Out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-slate-300 hover:text-white transition-colors">Log in</a>
                @endauth
            </div>

            <!-- Mobile hamburger -->
            <div class="flex items-center sm:hidden">
                <button @click="open = !open"
                        class="p-2 rounded-md text-slate-400 hover:text-white hover:bg-slate-700 transition-colors focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden border-t border-slate-700">
        <div class="px-2 pt-2 pb-3 space-y-1">
            @auth
                @if($currentClub)
                    <a href="{{ route('club.league', $currentClub) }}" class="block px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-700">
                        🎾 {{ $currentClub->name }}
                    </a>
                    @if(auth()->user()->isClubAdmin($currentClub))
                        <a href="{{ route('admin.seasons.index', $currentClub) }}" class="block px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-700">⚙️ Seasons</a>
                        <a href="{{ route('admin.members', $currentClub) }}" class="block px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-700">👥 Members</a>
                    @endif
                @endif
                @if(auth()->user()->clubs()->count() > 1)
                    <a href="{{ route('home') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-700">🏠 My Clubs</a>
                @endif
            @endauth
        </div>
        <div class="border-t border-slate-700 px-2 py-3 space-y-1">
            @auth
                <div class="px-3 py-1">
                    <div class="text-sm font-medium text-white">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-slate-400">{{ Auth::user()->email }}</div>
                </div>
                <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-md text-sm text-slate-300 hover:text-white hover:bg-slate-700">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button onclick="event.preventDefault(); this.closest('form').submit();"
                            class="block w-full text-left px-3 py-2 rounded-md text-sm text-slate-300 hover:text-white hover:bg-slate-700">
                        Log Out
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-sm text-slate-300 hover:text-white">Log in</a>
            @endauth
        </div>
    </div>
</nav>
