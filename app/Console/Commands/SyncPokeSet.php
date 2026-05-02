<?php

namespace App\Console\Commands;

use App\Models\FirebaseHelper;
use App\Models\pokecardFirebase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;


class SyncPokeSet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-poke-set';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!FirebaseHelper::adakah('sets')) {
            self::data();
        } else {
            if (!$this->confirm('Data set sudah ada, re-write?')) {
                $this->error('Dibatalkan.');
                return;
            } else {
                self::data();
            }
        }

    }
    public function data()
    {
        $response = Http::get('https://api.pokemontcg.io/v2/sets');
        $send = [];
        foreach ($response['data'] as $data) {
            echo "\rAdding {$data['name']} (ID: {$data['id']})";

            $send[$data['id']] = [
                'name' => $data['name'],
                'series' => $data['series'],
                'images' => [
                    'imagesLogo' => $data['images']['logo'],
                    'imagesSymbol' => $data['images']['symbol'],
                ],
                'releaseDate' => $data['releaseDate'],
                'total' => $data['total'],
            ];
        }
        FirebaseHelper::buatParent('sets', $send);
        $this->info('FINISH');
    }
}
