<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
 
class ExploreController extends Controller
{
    public function index(Request $request)
    {
        $page    = (int) $request->get('page', 1);
        $perPage = 25; // 5 kolom × 5 baris
 
        $response = Http::get('https://api.tcgdex.net/v2/en/cards', [
            'page'          => $page,
            'itemsPerPage'  => $perPage,
        ]);
 
        $data  = $response->json() ?? [];
        $total = (int) ($response->header('X-Total-Count') ?? count($data));
 
        return view('explore', compact('data', 'page', 'perPage', 'total'));
    }
}