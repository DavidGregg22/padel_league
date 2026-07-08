<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InvitationController extends Controller
{
    /** Show the accept page — register or log in. */
    public function show(string $token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if (! $invitation->isPending()) {
            return redirect()->route('home')->with('error', 'This invitation has already been used.');
        }

        return view('invite.accept', compact('invitation'));
    }

    /** Accept: create account (if new) or attach existing user. */
    public function accept(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if (! $invitation->isPending()) {
            return redirect()->route('home')->with('error', 'This invitation has already been used.');
        }

        $existing = User::where('email', $invitation->email)->first();

        if ($existing) {
            // Just attach to club
            if (! $existing->belongsToClub($invitation->club)) {
                $invitation->club->users()->attach($existing->id, ['role' => 'member']);
            }
            $invitation->update(['accepted_at' => now()]);
            Auth::login($existing);
        } else {
            // New user — need name + password
            $data = $request->validate([
                'name' => 'required|string|max:100',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $invitation->email,
                'password' => Hash::make($data['password']),
            ]);

            $invitation->club->users()->attach($user->id, ['role' => 'member']);
            $invitation->update(['accepted_at' => now()]);
            Auth::login($user);
        }

        return redirect()->route('club.league', $invitation->club)->with('success', "Welcome to {$invitation->club->name}!");
    }
}
