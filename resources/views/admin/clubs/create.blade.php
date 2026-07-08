<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-lg text-blue-100">Create Club</h2></x-slot>
    <div class="py-6 max-w-lg mx-auto px-4">
        <div class="bg-blue-900 shadow rounded-lg p-6">
            <form method="POST" action="{{ route('super.clubs.store') }}" class="space-y-4">@csrf
                <div><label class="block text-sm font-medium text-blue-200">Club Name</label><input type="text" name="name" value="{{ old('name') }}" required class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md placeholder-blue-500" placeholder="e.g. City Padel Club">@error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror</div>
                <hr class="border-blue-700">
                <p class="text-blue-300 text-sm">Create the first admin for this club:</p>
                <div><label class="block text-sm font-medium text-blue-200">Admin Name</label><input type="text" name="admin_name" value="{{ old('admin_name') }}" required class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md">@error('admin_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="block text-sm font-medium text-blue-200">Admin Email</label><input type="email" name="admin_email" value="{{ old('admin_email') }}" required class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md">@error('admin_email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="block text-sm font-medium text-blue-200">Admin Password</label><input type="password" name="admin_password" required class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md">@error('admin_password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="block text-sm font-medium text-blue-200">Confirm Password</label><input type="password" name="admin_password_confirmation" required class="mt-1 block w-full bg-blue-800 border-blue-700 text-white rounded-md"></div>
                <div class="flex justify-end gap-3 pt-2"><a href="{{ route('super.clubs.index') }}" class="text-blue-400 text-sm">Cancel</a><button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-teal-700">Create Club</button></div>
            </form>
        </div>
    </div>
</x-app-layout>
