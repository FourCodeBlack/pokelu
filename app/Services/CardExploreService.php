<?php

namespace App\Services;

use App\Models\pokecardFirebase;
use App\Models\FirebaseHelper;
use Illuminate\Support\Facades\Cache;

class CardExploreService
{
    /**
     * Ambil data kartu untuk halaman Explore / Discovery Harian.
     * Menggunakan cache random harian yang sama agar data sinkron.
     */
    public function getPage(int $page = 1, int $perPage = 20): array
    {
        $today = now()->format('Y-m-d');
        $cacheKey = 'daily_random_cards_' . $today;

        // Ambil data kartu ter-random harian dari cache,
        // jika belum ada, buat cache baru dengan logic pengacakan yang sama dengan Explore.
        $randomizedCards = Cache::remember($cacheKey, now()->endOfDay(), function () use ($today) {
            $db = pokecardFirebase::db();
            $cards = $db->getSnapshot()->getValue() ?? [];
            $keys = array_keys($cards);

            mt_srand(crc32($today));
            shuffle($keys);
            mt_srand();

            $result = [];
            foreach ($keys as $key) {
                $result[$key] = $cards[$key];
            }
            return $result;
        });

        $offset = ($page - 1) * $perPage;
        $paginated = array_slice($randomizedCards, $offset, $perPage, true);

        // Ambil semua offers dari Firebase untuk menghitung penawaran secara efisien
        $offersRaw = FirebaseHelper::baca('cards') ?? [];

        $result = [];
        foreach ($paginated as $cardId => $card) {
            $offers = $offersRaw[$cardId]['offers'] ?? [];
            $offerCount = is_array($offers) ? count($offers) : 0;

            $result[] = [
                'id'          => $cardId,
                'name'        => $card['name'] ?? 'Unknown Card',
                'image'       => $card['imageLow'] ?? $card['image'] ?? null,
                'offer_count' => $offerCount,
                'raw'         => $card
            ];
        }

        return $result;
    }
}
