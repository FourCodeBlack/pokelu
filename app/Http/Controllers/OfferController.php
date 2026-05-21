<?php

namespace App\Http\Controllers;

use App\Models\FirebaseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        // ── 1. Baca semua card dari Firebase ──
        $allCards = FirebaseHelper::baca('cards') ?? [];

        // ── 2. Flatten: kumpulkan semua offer dari semua card ──
        $offers = [];
        foreach ($allCards as $cardId => $cardData) {
            if (empty($cardData['offers']) || !is_array($cardData['offers'])) {
                continue;
            }
            foreach ($cardData['offers'] as $offerId => $offer) {
                if (empty($offer) || !is_array($offer)) continue;

                $offers[] = [
                    'cardId'      => $cardId,
                    'offerId'     => $offerId,
                    'price'       => $offer['price']       ?? null,
                    'condition'   => $offer['condition']   ?? 'MULUS',
                    'desc'        => $offer['desc']        ?? '',
                    'uid'         => $offer['uid']         ?? null,
                    'displayName' => $offer['displayName'] ?? ($offer['username'] ?? 'User'),
                    'username'    => $offer['username']    ?? null,
                    'handle'      => $offer['handle']      ?? null,
                    'pfp'         => $offer['pfp']         ?? null,   // new field
                    'photoURL'    => $offer['photoURL']    ?? null,   // legacy field
                    'contact'     => $offer['contact']     ?? null,
                    'createdAt'   => $offer['createdAt']   ?? 0,
                ];
            }
        }

        // ── 3. Urutkan dari terbaru ──
        usort($offers, fn($a, $b) => ($b['createdAt'] ?? 0) <=> ($a['createdAt'] ?? 0));

        // ── 4. Paginasi manual ──
        $page    = max(1, (int) $request->get('page', 1));
        $perPage = 4;
        $total   = count($offers);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page    = min($page, $lastPage); // jangan melebihi halaman terakhir
        $items   = array_slice($offers, ($page - 1) * $perPage, $perPage);

        // ── 5. Ambil data kartu dari Pokemon TCG API (hanya kartu di halaman ini) ──
        $uniqueCardIds = array_unique(array_column($items, 'cardId'));
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

        // ── 6. Enrich items: tambahkan cardName, cardImage, user info final ──
        foreach ($items as &$item) {
            $cdata = $cardDataMap[$item['cardId']] ?? [];

            $item['cardName']  = $cdata['name']  ?? $this->cardNameFromId($item['cardId']);
            $item['cardImage'] = $cdata['image'] ?? $this->cardImageFromId($item['cardId']);

            $fbUser = [];
            if ($item['uid']) {
                $fbUser = FirebaseHelper::baca("users/{$item['uid']}") ?? [];
            }

            // Resolve displayName
            $item['displayName'] = $fbUser['name'] ?? $fbUser['username'] ?? $item['displayName'] ?? 'User';

            // Resolve handle
            $item['handle'] = $fbUser['handle'] ?? null;
            if (!$item['handle']) {
                $name = $item['displayName'];
                $item['handle'] = '@' . strtolower(str_replace(' ', '', $name));
            }

            // Resolve pfp
            if (!$item['pfp']) {
                $photoURL = $item['photoURL'] ?? null;
                if ($photoURL && !str_starts_with($photoURL, 'http')) {
                    $item['pfp'] = $photoURL;
                } else {
                    $item['pfp'] = $fbUser['pfp'] ?? 'default';
                }
            }
        }
        unset($item);

        $uid = session('user.uid');
        $currentUser = null;
        if ($uid) {
            $userData = FirebaseHelper::baca("users/{$uid}") ?? [];
            $currentUser = [
                'uid' => $uid,
                'role' => $userData['role'] ?? 'user',
            ];
        }

        return view('offers.index', [
            'offers'      => $items,
            'page'        => $page,
            'perPage'     => $perPage,
            'total'       => $total,
            'lastPage'    => $lastPage,
            'currentUser' => $currentUser,
        ]);
    }

    // ── Fallback nama kartu dari ID (misal: "swsh1-25" → "swsh1-25") ──
    private function cardNameFromId(string $cardId): string
    {
        return strtoupper(str_replace('-', ' #', $cardId));
    }

    // ── Konstruksi URL gambar dari cardId tanpa API ──
    // Format: {setId}-{number} → https://images.pokemontcg.io/{setId}/{number}.png
    private function cardImageFromId(string $cardId): ?string
    {
        $parts = explode('-', $cardId, 2);
        if (count($parts) === 2) {
            [$setId, $number] = $parts;
            return "https://images.pokemontcg.io/{$setId}/{$number}.png";
        }
        return null;
    }

    public function destroy($cardId, $offerId)
    {
        $currentUid = session('user.uid') ?? session('uid');

        if (!$currentUid) {
            abort(401, 'Kamu harus login.');
        }

        $path = "cards/{$cardId}/offers/{$offerId}";

        logger()->info('[OfferController::destroy] Request', [
            'cardId'     => $cardId,
            'offerId'    => $offerId,
            'currentUid' => $currentUid,
            'path'       => $path,
        ]);

        // Check admin role
        $currentUserData = FirebaseHelper::baca("users/{$currentUid}") ?? [];
        $isAdmin         = ($currentUserData['role'] ?? null) === 'admin';

        // Read the offer
        $offer = FirebaseHelper::baca($path);

        logger()->info('[OfferController::destroy] Offer data', [
            'offer'   => $offer,
            'isAdmin' => $isAdmin,
            'path'    => $path,
        ]);

        if (!$offer) {
            return back()->with('error', "Penawaran tidak ditemukan. (path: {$path})");
        }

        $isOwner = ($offer['uid'] ?? null) === $currentUid;

        if (!$isAdmin && !$isOwner) {
            abort(403, 'Kamu tidak punya izin menghapus penawaran ini.');
        }

        FirebaseHelper::hapus($path);

        return back()->with('success', 'Penawaran berhasil dihapus.');
    }
}

