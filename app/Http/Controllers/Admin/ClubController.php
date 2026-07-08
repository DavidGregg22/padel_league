<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClubController extends Controller
{
    // ── Club management (super-admin only) ───────────────────

    public function index()
    {
        $clubs = Club::withCount('users')->orderBy('name')->get();

        return view('admin.clubs.index', compact('clubs'));
    }

    public function create()
    {
        return view('admin.clubs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'admin_name' => 'required|string|max:100',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        $club = Club::create(['name' => $data['name']]);

        $admin = User::create([
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'password' => Hash::make($data['admin_password']),
        ]);

        $club->users()->attach($admin->id, ['role' => 'admin']);

        return redirect()->route('super.clubs.index')->with('success', "Club \"{$club->name}\" created.");
    }

    public function destroy(Club $club)
    {
        $club->delete();

        return redirect()->route('super.clubs.index')->with('success', 'Club deleted.');
    }

    // ── Invitation management (club-admin) ───────────────────

    public function inviteIndex(Club $club)
    {
        $invitations = $club->invitations()->latest()->get();
        $members = $club->users()->orderBy('name')->get();

        return view('admin.clubs.members', compact('club', 'invitations', 'members'));
    }

    public function inviteStore(Request $request, Club $club)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        // If already a member, skip
        if ($club->users()->where('email', $data['email'])->exists()) {
            return back()->with('error', 'That person is already a member of this club.');
        }

        $invitation = Invitation::firstOrCreate(
            ['club_id' => $club->id, 'email' => $data['email']],
            ['token' => Str::random(48)]
        );

        // Reset token if re-inviting
        if (! $invitation->wasRecentlyCreated) {
            $invitation->update(['token' => Str::random(48), 'accepted_at' => null]);
        }

        $inviteUrl = route('invite.accept', $invitation->token);

        return back()->with('success', "Invite link for {$data['email']}: {$inviteUrl}");
    }

    public function inviteDestroy(Club $club, Invitation $invitation)
    {
        $invitation->delete();

        return back()->with('success', 'Invitation removed.');
    }

    public function removeMember(Club $club, User $user)
    {
        $club->users()->detach($user->id);

        return back()->with('success', "{$user->name} removed from club.");
    }

    public function promoteMember(Club $club, User $user)
    {
        $club->users()->updateExistingPivot($user->id, ['role' => 'admin']);

        return back()->with('success', "{$user->name} is now a club admin.");
    }

    public function demoteMember(Club $club, User $user)
    {
        $club->users()->updateExistingPivot($user->id, ['role' => 'member']);

        return back()->with('success', "{$user->name} is now a regular member.");
    }
}
