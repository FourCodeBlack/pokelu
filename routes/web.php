<?php

use App\Http\Controllers\pokeApiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

Route::get('/chat', function () {
    return view('chat');
})->name('chat');

Route::get('/profile', function () {
    return view('profile');
})->name('profile');

Route::get('/api/pokeTcg/data', [pokeApiController::class, 'getData']);

//EXPLORE HANDLERRRR
Route::get('/explore', [PokeApiController::class, 'getData'])->name('jelajah');
Route::get('/reset/iya/konfirmasi', [pokeApiController::class, 'reset']);



//ADMIN ONLY
// Route::get('/data', [PokeApiController::class, 'sendData']);