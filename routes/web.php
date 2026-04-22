<?php

use App\Http\Controllers\pokeApiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

// Route::get('/explore', function () {
//     return view('jelajah');
// })->name('jelajah');

Route::get('/chat', function () {
    return view('chat');
})->name('chat');

Route::get('/profile', function () {
    return view('profile');
})->name('profile');

Route::get('/api/pokeTcg/data', [pokeApiController::class, 'getData']);

Route::get('/explore', [PokeApiController::class, 'getData'])->name('jelajah');
Route::get('/reset', [PokeApiController::class, 'reset']);