<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PlayerController extends Controller
{
    public function index()
    {
        $players = User::where('is_admin', false)->orderBy('name')->get();
        return view('admin.players.index', compact('players'));
    }

    public function create()
    {
        return view('admin.players.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => false,
        ]);

        return redirect()->route('admin.players.index')->with('success', 'Player added.');
    }

    public function edit(User $player)
    {
        return view('admin.players.edit', compact('player'));
    }

    public function update(Request $request, User $player)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email,' . $player->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $player->update([
            'name'  => $data['name'],
            'email' => $data['email'],
            ...($data['password'] ? ['password' => Hash::make($data['password'])] : []),
        ]);

        return redirect()->route('admin.players.index')->with('success', 'Player updated.');
    }

    public function destroy(User $player)
    {
        $player->delete();
        return redirect()->route('admin.players.index')->with('success', 'Player removed.');
    }
}
