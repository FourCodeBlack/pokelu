<?php

namespace App\Http\Controllers;

use App\Models\FirebaseHelper;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    // ── Akun dummy sementara (ganti dengan DB nanti) ──
    private array $accounts = [
        [
            'email' => 'admin@pokelu.com',
            'password' => 'pokelu123',
            'name' => 'Admin Pokelu',
            'role' => 'admin',
        ],
        [
            'email' => 'trainer@pokelu.com',
            'password' => '123456',
            'name' => 'Ash Ketchum',
            'role' => 'user',
        ],
    ];

    // ── POST /login ──
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:4'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 4 karakter.',
        ]);

        $email = strtolower(trim($request->email));
        $password = $request->password;

        $matched = collect($this->accounts)->first(
            fn($acc) => $acc['email'] === $email && $acc['password'] === $password
        );

        if (!$matched) {
            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->withInput($request->only('email'))
                ->with('redirect', $request->input('redirect', '/'));
        }

        session([
            'user' => [
                'email' => $matched['email'],
                'name' => $matched['name'],
                'role' => $matched['role'],
            ],
        ]);

        if (!$matched) {
            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->withInput($request->only('email'));
        }

        // 1. regenerate DULU
        $request->session()->regenerate();

        // 2. baru simpan session
        $request->session()->put('user', [
            'uid' => $matched['uid'],
            'email' => $matched['email'],
            'name' => $matched['name'],
            'role' => $matched['role'],
        ]);
        $uid = $matched['uid'];

        if (!FirebaseHelper::adakah("users/$uid")) {
            FirebaseHelper::buatParent("users/$uid", [
                'email' => $matched['email'],
                'name' => $matched['name'],
                'role' => $matched['role'],
                'pfp' => 'pfp6',
            ]);
        }

        $redirect = $request->input('redirect', '/');


        return redirect($redirect)->with('success', 'Selamat datang, ' . $matched['name'] . '!');
    }

    // ── POST /logout ──
    public function logout(Request $request)
    {
        $request->session()->flush(); // hapus semua session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
    public function firebaseLogin(Request $request)
    {
        $request->validate(['token' => 'required']);

        // Simpan data user dari Firebase ke session Laravel
        $request->session()->regenerate();
        $request->session()->put('user', [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'avatar' => $request->input('avatar'),
            'uid' => $request->input('uid'),
        ]);

        $uid = $request->input('uid');

        // Create/update user di Firebase RTDB
        if (!FirebaseHelper::adakah("users/{$uid}")) {
            FirebaseHelper::buatParent("users/{$uid}", [
                'uid' => $uid,
                'email' => $request->input('email'),
                'name' => $request->input('name'),
                'role' => 'user',
                'pfp' => 'pfp6', // default avatar
            ]);
        } else {
            // Update existing user
            FirebaseHelper::perbarui("users/{$uid}", [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
            ]);
        }

        return response()->json(['success' => true]);
    }
}