<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FirebaseHelper;

class DeleteFirebaseUserData extends Command
{
    protected $signature = 'firebase:delete-user-data 
                            {uid : Firebase UID user}
                            {--force : Benar-benar hapus data}';

    protected $description = 'Menghapus semua data Firebase Realtime Database yang berhubungan dengan UID tertentu';

    public function handle()
    {
        $uid = $this->argument('uid');
        $force = $this->option('force');

        $updates = [];

        /*
        |--------------------------------------------------------------------------
        | 1. Data yang path-nya langsung pakai UID
        |--------------------------------------------------------------------------
        */
        $directPaths = [
            "users/{$uid}",
            "status/{$uid}",
            "wishlists/{$uid}",
            "wishlist/{$uid}",
            "favorites/{$uid}",
            "notifications/{$uid}",
        ];

        foreach ($directPaths as $path) {
            if (FirebaseHelper::adakah($path)) {
                $updates[$path] = null;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Hapus data user di cards
        |--------------------------------------------------------------------------
        | Dari struktur database kamu:
        | cards/{cardId}/comments/{commentId}/uid
        | cards/{cardId}/offers/{offerId}/uid
        | cards/{cardId}/offers/{offerId}/replies/{replyId}/uid
        */
        $cards = FirebaseHelper::baca('cards') ?? [];

        foreach ($cards as $cardId => $card) {
            // comments
            foreach (($card['comments'] ?? []) as $commentId => $comment) {
                if (($comment['uid'] ?? null) === $uid) {
                    $updates["cards/{$cardId}/comments/{$commentId}"] = null;
                }
            }

            // offers
            foreach (($card['offers'] ?? []) as $offerId => $offer) {
                if (($offer['uid'] ?? null) === $uid) {
                    // Kalau offer milik user, hapus seluruh offer
                    $updates["cards/{$cardId}/offers/{$offerId}"] = null;
                    continue;
                }

                // Kalau hanya reply milik user, hapus reply-nya saja
                foreach (($offer['replies'] ?? []) as $replyId => $reply) {
                    if (($reply['uid'] ?? null) === $uid) {
                        $updates["cards/{$cardId}/offers/{$offerId}/replies/{$replyId}"] = null;
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Hapus chats yang berhubungan dengan UID
        |--------------------------------------------------------------------------
        | Struktur chat kamu:
        | chats/{uid1}_{uid2}_{cardId}
        */
        $chats = FirebaseHelper::baca('chats') ?? [];

        foreach ($chats as $chatId => $chat) {
            if (str_contains($chatId, $uid)) {
                // Kalau UID ada di key chat, hapus seluruh room chat
                $updates["chats/{$chatId}"] = null;
                continue;
            }

            // Fallback kalau suatu saat chat key tidak mengandung UID
            foreach (($chat['messages'] ?? []) as $messageId => $message) {
                if (($message['uid'] ?? null) === $uid) {
                    $updates["chats/{$chatId}/messages/{$messageId}"] = null;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Preview
        |--------------------------------------------------------------------------
        */
        $this->info("UID: {$uid}");
        $this->info("Total path yang akan dihapus: " . count($updates));

        foreach (array_keys($updates) as $path) {
            $this->line("- {$path}");
        }

        if (!$force) {
            $this->newLine();
            $this->warn('Mode preview. Belum ada data yang dihapus.');
            $this->line("Jalankan ini untuk benar-benar hapus:");
            $this->line("php artisan firebase:delete-user-data {$uid} --force");

            return Command::SUCCESS;
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Eksekusi delete
        |--------------------------------------------------------------------------
        */
        if (empty($updates)) {
            $this->info('Tidak ada data yang cocok untuk UID tersebut.');
            return Command::SUCCESS;
        }

        FirebaseHelper::db()->update($updates);

        $this->info('Data berhasil dihapus.');

        return Command::SUCCESS;
    }
}