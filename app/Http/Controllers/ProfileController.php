<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FirebaseHelper;

class ProfileController extends Controller
{
    public function edit()
    {
        $uid = session('user.uid');

        if (!$uid) {
            return redirect('/login');
        }

        $data = FirebaseHelper::baca("users/$uid");

        return view('profile.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $uid = session('user.uid');

        if (!$uid) {
            return redirect('/login');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'pfp' => 'required|string',
        ]);

        FirebaseHelper::perbarui("users/$uid", [
            'name' => $request->name,
            'pfp' => $request->pfp,
        ]);

        session([
            'user.name' => $request->name,
            'user.pfp' => $request->pfp,
        ]);

        return redirect()->route('profile')->with('success', 'Profile updated');
    }
}