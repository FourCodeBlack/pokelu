<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\pokeApiController;
use App\Http\Controllers\searchPokeName;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

// ── HALAMAN UTAMA ──
Route::get('/', function () {
    return view('index');
});

// ── CHAT ──
Route::get('/chat', function () {
    return view('chat');
})->name('chat');

// ── PROFILE ──
Route::get('/profile', function () {
    return view('profile');
})->name('profile');

// ── LOGIN ──
// Tidak ada lagi session/auth Laravel — semua ditangani Firebase Auth di frontend
Route::get('/login', function () {
    return view('layout.login');
})->name('login');

// ── EXPLORE ──
Route::get('/explore', [pokeApiController::class, 'getData'])->name('jelajah');

// ── CARD DETAIL ──
Route::get('/card/{id}', [CardController::class, 'show'])->name('card.detail');

// ── SEARCH ──
Route::get('/cards/search', [searchPokeName::class, 'search'])->name('cards.search');
Route::get('/cards/{id}',   [CardController::class, 'show']);

// ── API INTERNAL ──
Route::get('/api/pokeTcg/data', [pokeApiController::class, 'getData']);

Route::get('/chat', [ChatController::class, 'index'])->name('chat');

// ── ADMIN ONLY ──
Route::get('/data',              [pokeApiController::class, 'sendData']);
Route::get('/reset/all',         [pokeApiController::class, 'reset']);
Route::get('/reset/iya/konfirmasi', [pokeApiController::class, 'reset']);



Route::post('/auth/firebase', [AuthController::class, 'firebaseLogin'])->name('auth.firebase');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');