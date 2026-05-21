<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Kreait\Laravel\Firebase\Facades\Firebase;

class CardController extends Controller
{
    /**
     * Tampilkan halaman detail kartu.
     * URL: /card/{id}  →  contoh: /card/swsh1-1
     *
     * TCGdex API endpoint:
     *   https://api.tcgdex.net/v2/en/cards/{id}
     */
  public function show(string $id)
{
    // Coba Pokemon TCG API dulu (untuk hasil search)
    $response = Http::timeout(8)->get("https://api.pokemontcg.io/v2/cards/{$id}");

    if ($response->successful()) {
        $raw = $response->json()['data'] ?? [];

        $card = [
            'id'          => $raw['id']               ?? $id,
            'name'        => $raw['name']              ?? 'Unknown',
            'image'       => $raw['images']['large']   ?? $raw['images']['small'] ?? null,
            'rarity'      => $raw['rarity']            ?? null,
            'type'        => isset($raw['types'])
                                ? implode(' / ', (array) $raw['types'])
                                : null,
            'stage'       => $raw['subtypes'][0]       ?? null,
            'description' => $raw['flavorText']        ?? null,
            'set'         => $raw['set']['name']       ?? null,
            'hp'          => $raw['hp']                ?? null,
            'logo'        => $raw['set']['images']['logo'] ?? null,
        ];
        $currentUid = session('user.uid');
        $isAdmin = false;
        if ($currentUid) {
            $user = \App\Models\FirebaseHelper::baca("users/{$currentUid}");
            $isAdmin = ($user['role'] ?? 'user') === 'admin';
        }

        // Server-side lookup untuk offers
        $fbCard = \App\Models\FirebaseHelper::baca("cards/{$id}") ?? [];
        $rawOffers = $fbCard['offers'] ?? [];
        $offers = [];

        foreach ($rawOffers as $offerId => $offer) {
            $uid = $offer['uid'] ?? null;
            $seller = [];
            if ($uid) {
                $seller = \App\Models\FirebaseHelper::baca("users/{$uid}") ?? [];
            }

            $name = $seller['name'] ?? $seller['username'] ?? 'User';
            $sellerHandle = '@' . strtolower(str_replace(' ', '', $name));

            $offers[] = [
                'offerId'   => $offerId,
                'cardId'    => $id,
                'uid'       => $uid,
                'price'     => $offer['price'] ?? null,
                'condition' => $offer['condition'] ?? null,
                'contact'   => $offer['contact'] ?? null,
                'message'   => $offer['desc'] ?? $offer['message'] ?? null, // handle both desc and message
                'createdAt' => $offer['createdAt'] ?? 0,
                'replies'   => [],
                'seller'    => [
                    'username' => $name,
                    'handle'   => $sellerHandle,
                    'pfp'      => $seller['pfp'] ?? 'default',
                    'role'     => $seller['role'] ?? 'user',
                ],
            ];
        }

        usort($offers, function ($a, $b) {
            return ($b['createdAt'] ?? 0) <=> ($a['createdAt'] ?? 0);
        });

        // Server-side lookup untuk comments
        $rawComments = $fbCard['comments'] ?? [];
        $comments = [];
        foreach ($rawComments as $commentId => $comment) {
            $uid = $comment['uid'] ?? null;
            $commenter = [];
            if ($uid) {
                $commenter = \App\Models\FirebaseHelper::baca("users/{$uid}") ?? [];
            }
            $cName = $commenter['name'] ?? $commenter['username'] ?? 'User';
            $commenterHandle = '@' . strtolower(str_replace(' ', '', $cName));

            $comments[] = [
                'id'        => $commentId,
                'uid'       => $uid,
                'text'      => $comment['text'] ?? null,
                'createdAt' => $comment['createdAt'] ?? 0,
                'seller'    => [
                    'username' => $cName,
                    'handle'   => $commenterHandle,
                    'pfp'      => $commenter['pfp'] ?? 'default',
                    'role'     => $commenter['role'] ?? 'user',
                ],
            ];
        }

        usort($comments, function ($a, $b) {
            return ($b['createdAt'] ?? 0) <=> ($a['createdAt'] ?? 0);
        });

        $firebaseToken = null;
        if ($currentUid) {
            try {
                $firebaseToken = Firebase::auth()->createCustomToken($currentUid)->toString();
            } catch (\Exception $e) {
                // Ignore
            }
        }

        return view('card-detail', compact('card', 'isAdmin', 'offers', 'comments', 'firebaseToken'));
    }

    // Fallback ke TCGdex (untuk kartu dari halaman explore)
    // $response = Http::timeout(8)->get("https://api.tcgdex.net/v2/en/cards/{$id}");

    // if ($response->failed()) {
    //     abort(404, 'Kartu tidak ditemukan.');
    // }

    // $raw = $response->json();

    // $card = [
    //     'id'          => $raw['id']          ?? $id,
    //     'name'        => $raw['name']         ?? 'Unknown',
    //     'image'       => isset($raw['image'])
    //                         ? $raw['image'] . '/low.webp'
    //                         : null,
    //     'rarity'      => $raw['rarity']       ?? null,
    //     'type'        => isset($raw['types'])
    //                         ? implode(' / ', (array) $raw['types'])
    //                         : null,
    //     'stage'       => $raw['stage']        ?? null,
    //     'description' => $raw['description']  ?? '-',
    //     'set'         => $raw['set']['name']  ?? null,
    //     'hp'          => $raw['hp']           ?? null,
    //     'logo'        => $raw['set']['logo'].'.png' ?? null,
    // ];

    // return view('card-detail', compact('card'));
}

public function search(Request $request)
{
    try {
        $page = $request->query('page', 1);
        $type = $request->query('type');
        $rarity = $request->query('rarity');

        // Gunakan API Pokemon TCG untuk search
        $query = [];

        if ($type) {
            $query[] = "types:$type";
        }

        if ($rarity) {
            $query[] = "rarity:$rarity";
        }

        $q = implode(' ', $query);

        $response = Http::timeout(8)->get('https://api.pokemontcg.io/v2/cards', [
            'q' => $q,
            'page' => $page,
            'pageSize' => 20,
        ]);

        if ($response->failed()) {
            return response()->json([
                'error' => 'Gagal ambil data dari API'
            ], 500);
        }

        return response()->json($response->json());

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}

private function currentUid()
{
    return session('user.uid');
}

private function isAdmin($uid)
{
    if (!$uid) return false;
    $user = \App\Models\FirebaseHelper::baca("users/{$uid}");
    return ($user['role'] ?? 'user') === 'admin';
}

public function destroyComment($cardId, $commentId)
{
    $uid = $this->currentUid();
    if (!$uid) {
        return request()->ajax() ? response()->json(['error' => 'Unauthorized'], 401) : redirect()->back()->with('error', 'Login dulu untuk menghapus komentar.');
    }

    $comment = \App\Models\FirebaseHelper::baca("cards/{$cardId}/comments/{$commentId}");
    if (!$comment) {
        return request()->ajax() ? response()->json(['error' => 'Not found'], 404) : abort(404, 'Komentar tidak ditemukan.');
    }

    $isOwner = ($comment['uid'] ?? null) === $uid;
    $isAdmin = $this->isAdmin($uid);

    if (!$isOwner && !$isAdmin) {
        return request()->ajax() ? response()->json(['error' => 'Forbidden'], 403) : abort(403, 'Kamu tidak punya akses untuk menghapus komentar ini.');
    }

    \App\Models\FirebaseHelper::hapus("cards/{$cardId}/comments/{$commentId}");

    if (request()->wantsJson() || request()->ajax()) {
        return response()->json(['success' => true]);
    }
    return redirect()->back()->with('success', 'Komentar berhasil dihapus.');
}

public function destroyOffer($cardId, $offerId)
{
    $uid = $this->currentUid();
    if (!$uid) {
        return request()->ajax() ? response()->json(['error' => 'Unauthorized'], 401) : redirect()->back()->with('error', 'Login dulu untuk menghapus penawaran.');
    }

    $offer = \App\Models\FirebaseHelper::baca("cards/{$cardId}/offers/{$offerId}");
    if (!$offer) {
        return request()->ajax() ? response()->json(['error' => 'Not found'], 404) : abort(404, 'Penawaran tidak ditemukan.');
    }

    $isOwner = ($offer['uid'] ?? null) === $uid;
    $isAdmin = $this->isAdmin($uid);

    if (!$isOwner && !$isAdmin) {
        return request()->ajax() ? response()->json(['error' => 'Forbidden'], 403) : abort(403, 'Kamu tidak punya akses untuk menghapus penawaran ini.');
    }

    \App\Models\FirebaseHelper::hapus("cards/{$cardId}/offers/{$offerId}");

    if (request()->wantsJson() || request()->ajax()) {
        return response()->json(['success' => true]);
    }
    return redirect()->back()->with('success', 'Penawaran berhasil dihapus.');
}
}