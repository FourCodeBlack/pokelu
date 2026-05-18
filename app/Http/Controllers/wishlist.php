<?php

namespace App\Http\Controllers;

use App\Models\FirebaseHelper;
use App\Models\pokecardFirebase;
use Illuminate\Http\Request;

class wishlist extends Controller
{
    public function addWishlist(Request $request)
    {
        $pokeID = $request->id;
        $uid = session('user.uid');

        $path = "users/$uid/wishlist/$pokeID";

        if (FirebaseHelper::adakah($path)) {

            FirebaseHelper::hapus($path);

            return response()->json([
                'success' => true,
                'wished' => false
            ]);
        }

        $data = pokecardFirebase::searchById($pokeID);

        FirebaseHelper::buatParent($path, [
            "name" => $data['name'],
            "image" => $data['imageLow']
        ]);

        return response()->json([
            'success' => true,
            'wished' => true
        ]);
    }
}
