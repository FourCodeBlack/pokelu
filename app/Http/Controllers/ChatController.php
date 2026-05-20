<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * Halaman chat utama
     */
    public function index()
    {
        return view('chat');
    }

    /**
     * API: Ambil semua users dari Firebase REST API
     * Route: GET /chat/users
     */
    public function getUsers(Request $request)
    {

        // 1. Ambil konfigurasi dari config/services.php
        $dbUrl = rtrim(config('services.firebase.database_url'), '/');
        $secret = config('services.firebase.database_secret');

        // Debugging (Aktifkan jika data tidak muncul)

        if (empty($dbUrl)) {
            Log::error('ChatController::getUsers — firebase.database_url kosong di config/services.php');
            return response()->json(['error' => 'Konfigurasi Firebase tidak ditemukan'], 500);
        }

        try {
            // 2. Susun URL REST API Firebase
            $url = "{$dbUrl}/users.json";
            
            // Tambahkan auth jika menggunakan database secret
            if (!empty($secret)) {
                $url .= "?auth={$secret}";
            }

            // 3. Request ke Firebase
            $response = Http::timeout(10)->get($url);

            if ($response->failed()) {
                Log::error('ChatController::getUsers — Firebase REST gagal', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return response()->json(['error' => 'Gagal mengambil data dari Firebase'], 500);
            }

            $users = $response->json();

            // Jika node 'users' di Firebase masih kosong
            if (!is_array($users)) {
                return response()->json([]);
            }

            // 4. Filter data untuk Frontend
            $filtered = [];
            foreach ($users as $uid => $user) {
                if (!is_array($user)) continue;

                $filtered[$uid] = [
                    'uid'    => $uid,
                    'name'   => $user['name']   ?? 'Anonymous',
                    'email'  => $user['email']  ?? '',
                    'pfp'    => $user['pfp']    ?? 'default',
                    'status' => $user['status'] ?? 'offline',
                ];
            }

            return response()->json($filtered);

        } catch (\Exception $e) {
            Log::error('ChatController::getUsers — Exception: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan sistem'], 500);
        }
    }

    /**
     * API: Sinkronisasi user Firebase ke session Laravel
     * Route: POST /chat/sync-user
     */
    public function syncUser(Request $request)
    {
        $validated = $request->validate([
            'uid'         => 'required|string',
            'displayName' => 'nullable|string',
            'email'       => 'nullable|string|email',
            'pfp'         => 'nullable|string',
        ]);

        // Simpan info ke session
        session([
            'firebase_uid'   => $validated['uid'],
            'firebase_name'  => $validated['displayName'],
            'firebase_email' => $validated['email'],
            'firebase_pfp'   => $validated['pfp'] ?? 'default',
        ]);

        return response()->json(['ok' => true]);
    }
}