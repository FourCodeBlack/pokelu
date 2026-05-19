<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\pokeApiController;
use App\Http\Controllers\searchPokeName;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\userProfile;
use App\Http\Controllers\user;
use App\Http\Controllers\wishlist;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// ── HALAMAN UTAMA ──
Route::get('/', function () {
    return view('index');
});

// ── HOME (POPULER TCG) ──
Route::get('/home', [HomeController::class, 'index'])->name('home');

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

// ── FORUM ──
Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
Route::get('/forum/create', [ForumController::class, 'create'])->name('forum.create');
Route::post('/forum', [ForumController::class, 'store'])->name('forum.store');
Route::get('/forum/{threadId}', [ForumController::class, 'show'])->name('forum.show');
Route::post('/forum/{threadId}/like', [ForumController::class, 'like'])->name('forum.like');
Route::post('/forum/{threadId}/dislike', [ForumController::class, 'dislike'])->name('forum.dislike');
Route::post('/forum/{threadId}/comments', [ForumController::class, 'comment'])->name('forum.comment');
Route::delete('/forum/{threadId}/comments/{commentId}', [ForumController::class, 'deleteComment'])->name('forum.comment.delete');
Route::patch('/forum/{threadId}/comments/{commentId}', [ForumController::class, 'editComment'])->name('forum.comment.edit');
Route::post('/forum/{threadId}/comments/{commentId}/react', [ForumController::class, 'reactComment'])->name('forum.comment.react');
Route::delete('/forum/{threadId}', [ForumController::class, 'destroy'])->name('forum.destroy');

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


//POST
Route::post('/wishlist/add', [wishlist::class, 'addWishlist'])->name('wishlist.add');
use App\Http\Controllers\ProfileController;

Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');


Route::post('/auth/firebase', [AuthController::class, 'firebaseLogin'])->name('auth.firebase');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── ADMIN ONLY ──
Route::middleware(['admin'])->group(function () {
    Route::delete('/admin/forum/{threadId}', [AdminController::class, 'deleteForumThread'])->name('admin.forum.destroy');
    Route::delete('/admin/forum/{threadId}/comments/{commentId}', [AdminController::class, 'deleteForumComment'])->name('admin.forum.comment.destroy');

    Route::delete('/admin/cards/{cardId}/comments/{commentId}', [AdminController::class, 'deleteCardComment'])->name('admin.card.comment.destroy');
    Route::delete('/admin/cards/{cardId}/offers/{offerId}', [AdminController::class, 'deleteCardOffer'])->name('admin.card.offer.destroy');
    Route::delete('/admin/cards/{cardId}/offers/{offerId}/replies/{replyId}', [AdminController::class, 'deleteOfferReply'])->name('admin.card.offer.reply.destroy');
});

// ── DETAIL CARD DELETES (OWNER ATAU ADMIN) ──
Route::delete('/cards/{cardId}/comments/{commentId}', [\App\Http\Controllers\CardController::class, 'destroyComment'])->name('cards.comments.destroy');
Route::delete('/cards/{cardId}/offers/{offerId}', [\App\Http\Controllers\CardController::class, 'destroyOffer'])->name('cards.offers.destroy');
