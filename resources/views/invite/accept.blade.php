<x-guest-layout>
    <h2 class="text-lg font-bold text-white mb-2">You've been invited to {{ $invitation->club->name }}</h2>
    <p class="text-blue-300 text-sm mb-4">Invitation for <span class="font-medium text-white">{{ $invitation->email }}</span></p>

    @php $existing = \App\Models\User::where('email', $invitation->email)->first(); @endphp

    @if($existing)
        <p class="text-blue-300 text-sm mb-4">You already have an account. Click below to join the club.</p>
        <form method="POST" action="{{ route('invite.accept', $invitation->token) }}">
            @csrf
            <button type="submit" class="w-full bg-teal-600 text-white py-2 rounded-md font-medium hover:bg-teal-700 transition-colors">
                Join {{ $invitation->club->name }}
            </button>
        </form>
    @else
        <p class="text-blue-300 text-sm mb-4">Create your account to join the club.</p>
        <form method="POST" action="{{ route('invite.accept', $invitation->token) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-blue-200">Your Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500">
                @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-blue-200">Password</label>
                <input type="password" name="password" required
                    class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500">
                @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-blue-200">Confirm Password</label>
                <input type="password" name="password_confirmation" required
                    class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
            <button type="submit" class="w-full bg-teal-600 text-white py-2 rounded-md font-medium hover:bg-teal-700 transition-colors">
                Create Account & Join
            </button>
        </form>
    @endif
</x-guest-layout>
