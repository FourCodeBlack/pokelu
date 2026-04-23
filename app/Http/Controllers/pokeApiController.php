<?php

namespace App\Http\Controllers;

use App\Models\pokecardFirebase;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use function PHPUnit\Framework\returnArgument;

class pokeApiController extends Controller
{
    public function getData(Request $request)
    {
        // Pagination langsung di Firebase
        $page = $request->input('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $db = pokecardFirebase::db();

        // Ambil total dulu (hanya keys, ringan)
        $total = count($db
            ->shallow()
            ->getSnapshot()
            ->getValue() ?? []);

        // Ambil data sesuai halaman
        $cards = array_values(
            $db->orderByKey()
                ->limitToFirst($offset + $perPage)
                ->getSnapshot()
                ->getValue() ?? []
        );

        // Slice manual karena Firebase tidak support offset
        $paginated = array_slice($cards, $offset, $perPage);

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
    public function sendData() //ADMIN ONLY
    {
        if (!pokecardFirebase::exists()) {
            set_time_limit(300);
            $response = file_get_contents(storage_path('app/cards.json'));
            $data = json_decode($response, true);

            $cards = array_values(array_filter(
                $data,
                fn($card) => !empty($card['image']) && !empty($card['name'])
            ));

            // Ubah dari array biasa ke associative array dengan key = id kartu
            $cards = array_column(
                array_map(fn($card) => [
                    'id' => $card['id'],
                    'name' => $card['name'],
                    'image' => $card['image'].'/low.png',
                ], $cards),
                null,
                'id'
            );

            // Sanitize key — hapus karakter yang dilarang Firebase
            $sanitized = [];
            foreach ($cards as $key => $value) {
                $cleanKey = str_replace(['.', '#', '$', '[', ']', '/'], '-', $key);
                $sanitized[$cleanKey] = $value;
            }

            pokecardFirebase::set($sanitized);
        }
        return view('index');

    }
}
