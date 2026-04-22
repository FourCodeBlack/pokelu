<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use function PHPUnit\Framework\returnArgument;

class pokeApiController extends Controller
{
    public function getData(Request $request)
    {
        // Cek langsung ke cards_shuffled yang sudah bersih
        if (!session()->has('cards_shuffled')) {

            // Ambil raw data (cache session)
            if (session()->has('pokemon_cards')) {
                $data = session('pokemon_cards');
            } else {
                $response = Http::get('https://api.tcgdex.net/v2/en/cards');
                $data = $response->json();
                session(['pokemon_cards' => $data]);
            }

            // Filter + map SEKALI SAJA, langsung simpan ke session
            $cards = array_values(array_filter($data, function ($card) {
                return !empty($card['image']) && !empty($card['name']);
            }));

            $cards = array_map(function ($card) {
                $card['image'] = $card['image'] . '/low.webp';
                return $card;
            }, $cards);

            $cards = collect($cards)->shuffle()->values()->all();
            session(['cards_shuffled' => $cards]);

        } else {
            $cards = session('cards_shuffled');
        }

        // Pagination
        $page = $request->input('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $paginated = array_slice($cards, $offset, $perPage);
        $total = count($cards);

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

        return redirect('/');
    }
}
