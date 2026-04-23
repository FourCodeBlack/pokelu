<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
    // $response = Http::timeout(8)->get("https://api.pokemontcg.io/v2/cards/{$id}");

    // if ($response->successful()) {
    //     $raw = $response->json()['data'] ?? [];

    //     $card = [
    //         'id'          => $raw['id']               ?? $id,
    //         'name'        => $raw['name']              ?? 'Unknown',
    //         'image'       => $raw['images']['large']   ?? $raw['images']['small'] ?? null,
    //         'rarity'      => $raw['rarity']            ?? null,
    //         'type'        => isset($raw['types'])
    //                             ? implode(' / ', (array) $raw['types'])
    //                             : null,
    //         'stage'       => $raw['subtypes'][0]       ?? null,
    //         'description' => $raw['flavorText']        ?? null,
    //         'set'         => $raw['set']['name']       ?? null,
    //         'hp'          => $raw['hp']                ?? null,
    //         'logo'        => $raw['set']['images']['logo'] ?? null,
    //     ];

    //     return view('card-detail', compact('card'));
    // }

    // Fallback ke TCGdex (untuk kartu dari halaman explore)
    $response = Http::timeout(8)->get("https://api.tcgdex.net/v2/en/cards/{$id}");

    if ($response->failed()) {
        abort(404, 'Kartu tidak ditemukan.');
    }

    $raw = $response->json();

    $card = [
        'id'          => $raw['id']          ?? $id,
        'name'        => $raw['name']         ?? 'Unknown',
        'image'       => isset($raw['image'])
                            ? $raw['image'] . '/low.webp'
                            : null,
        'rarity'      => $raw['rarity']       ?? null,
        'type'        => isset($raw['types'])
                            ? implode(' / ', (array) $raw['types'])
                            : null,
        'stage'       => $raw['stage']        ?? null,
        'description' => $raw['description']  ?? null,
        'set'         => $raw['set']['name']  ?? null,
        'hp'          => $raw['hp']           ?? null,
        'logo'        => $raw['set']['logo'].'.png' ?? null,
    ];

    return view('card-detail', compact('card'));
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
}