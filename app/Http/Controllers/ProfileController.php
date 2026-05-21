<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FirebaseHelper;
use Illuminate\Support\Facades\Http;

class ProfileController extends Controller
{
    public function show($uid = null)
    {
        $currentUid = session('user.uid');
        if (!$uid) {
            $uid = $currentUid;
        }

        if (!$uid) {
            return redirect('/login');
        }

        $profileUser = FirebaseHelper::baca("users/{$uid}");
        if (!$profileUser) {
            abort(404, 'User tidak ditemukan.');
        }
        $profileUser['uid'] = $uid;

        $currentUser = null;
        if ($currentUid) {
            $currentUserData = FirebaseHelper::baca("users/{$currentUid}") ?? [];
            $currentUser = [
                'uid' => $currentUid,
                'role' => $currentUserData['role'] ?? 'user',
            ];
        }

        $activeOffers = [];
        $cards = FirebaseHelper::baca('cards') ?? [];

        foreach ($cards as $cardId => $card) {
            if (empty($card['offers']) || !is_array($card['offers'])) {
                continue;
            }

            foreach ($card['offers'] as $offerId => $offer) {
                if (($offer['uid'] ?? null) !== $uid) {
                    continue;
                }

                $activeOffers[] = [
                    'cardId' => $cardId,
                    'offerId' => $offerId,
                    'price' => $offer['price'] ?? null,
                    'condition' => $offer['condition'] ?? null,
                    'contact' => $offer['contact'] ?? null,
                    'message' => $offer['desc'] ?? ($offer['message'] ?? null),
                    'createdAt' => $offer['createdAt'] ?? 0,
                    'uid' => $offer['uid'] ?? null,
                ];
            }
        }

        // Ambil data kartu dari Pokemon TCG API
        $uniqueCardIds = array_unique(array_column($activeOffers, 'cardId'));
        $cardDataMap   = [];
        foreach ($uniqueCardIds as $cid) {
            try {
                $resp = Http::timeout(5)->get("https://api.pokemontcg.io/v2/cards/{$cid}");
                if ($resp->successful()) {
                    $raw = $resp->json()['data'] ?? [];
                    $cardDataMap[$cid] = [
                        'name'  => $raw['name']                            ?? $cid,
                        'image' => $raw['images']['small']                 ?? null,
                    ];
                }
            } catch (\Exception $e) {
                // Biarkan fallback yang menangani
            }
        }

        // Enrich activeOffers dengan nama dan gambar kartu
        foreach ($activeOffers as &$item) {
            $cdata = $cardDataMap[$item['cardId']] ?? [];
            $item['cardName']  = $cdata['name']  ?? $this->cardNameFromId($item['cardId']);
            $item['cardImage'] = $cdata['image'] ?? $this->cardImageFromId($item['cardId']);
        }
        unset($item);

        usort($activeOffers, function ($a, $b) {
            return ($b['createdAt'] ?? 0) <=> ($a['createdAt'] ?? 0);
        });

        return view('profile', [
            'profileUser' => $profileUser,
            'activeOffers' => $activeOffers,
            'currentUser' => $currentUser,
        ]);
    }

    private function cardNameFromId(string $cardId): string
    {
        return strtoupper(str_replace('-', ' #', $cardId));
    }

    private function cardImageFromId(string $cardId): ?string
    {
        $parts = explode('-', $cardId, 2);
        if (count($parts) === 2) {
            [$setId, $number] = $parts;
            return "https://images.pokemontcg.io/{$setId}/{$number}.png";
        }
        return null;
    }

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