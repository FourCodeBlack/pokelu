<?php

namespace App\Http\Controllers;

use App\Models\FirebaseHelper;
use App\Models\pokecardFirebase;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use function PHPUnit\Framework\returnArgument;
use Illuminate\Support\Facades\Cache;


class pokeApiController extends Controller
{

    public function getData(Request $request)
    {
        $page = (int) $request->input('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $today = now()->format('Y-m-d');
        $cacheKey = 'daily_random_cards_' . $today;

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

        $total = count($randomizedCards);

        $paginated = array_slice(
            $randomizedCards,
            $offset,
            $perPage,
            true
        );

        return view('jelajah', [
            'data' => $paginated,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total
        ]);
    }
    public function reset()
    {
        session()->flush();
        pokecardFirebase::dropAll();
        return redirect('/');
    }
    // public function sendData() //ADMIN ONLY
    // {
    //     if (!pokecardFirebase::exists()) {
    //         set_time_limit(300);
    //         $response = file_get_contents(storage_path('app/cards.json'));
    //         $data = json_decode($response, true);

    //         $cards = array_values(array_filter(
    //             $data,
    //             fn($card) => !empty($card['image']) && !empty($card['name'])
    //         ));

    //         // Ubah dari array biasa ke associative array dengan key = id kartu
    //         $cards = array_column(
    //             array_map(fn($card) => [
    //                 'id' => $card['id'],
    //                 'name' => $card['name'],
    //                 'image' => $card['image'] . '/',
    //             ], $cards),
    //             null,
    //             'id'
    //         );

    //         // Sanitize key — hapus karakter yang dilarang Firebase
    //         $sanitized = [];
    //         foreach ($cards as $key => $value) {
    //             $cleanKey = str_replace(['.', '#', '$', '[', ']', '/'], '-', $key);
    //             $sanitized[$cleanKey] = $value;
    //         }

    //         pokecardFirebase::set($sanitized);
    //     }
    //     return view('index');

    // }
    public function sendData()
    {
        $page = 1;
        $pageSize = 250;
        $send = [];

        do {
            $response = Http::get('https://api.pokemontcg.io/v2/cards', [
                'page' => $page,
                'pageSize' => $pageSize
            ]);

            $data = $response->json();

            foreach ($data['data'] as $card) {
                $send[] = [
                    'id' => $card['id'],
                    'name' => $card['name'],
                    'imageLow' => $card['images']['small'] ?? null,
                    'imageLarge' => $card['images']['large'] ?? null,
                    'rarity' => $card['rarity'] ?? 'Unknown',
                ];
            }

            $page++;
        } while (count($data['data']) > 0);

        pokecardFirebase::set($send);

    }
}
