<?php

namespace App\Console\Commands;

use App\Models\pokecardFirebase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncPokeCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-poke-cards';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    // app/Console/Commands/SyncPokeCards.php

    public function handle()
    {
        $page = 1;
        $pageSize = 250;
        $send = [];

        do {
            $cards = $this->fetchPage($page, $pageSize);

            if ($cards === null) {
                $this->error("Failed to fetch page $page after retries. Stopping.");
                break;
            }

            foreach ($cards as $card) {
                $send[$card['id']] = [
                    'name' => $card['name'],
                    'rarity' => $card['rarity'] ?? 'Unknown',
                    'imageLow' => $card['images']['small'] ?? null,
                    'imageLarge' => $card['images']['large'] ?? null,
                    'setId' => $card['set']['id'],
                ];
            }

            $this->comment("Page $page fetched: " . count($cards) . " cards (total: " . count($send) . ")");
            $page++;

            sleep(1); // be polite to the API

        } while (count($cards) === $pageSize);

        $this->comment("Syncing " . count($send) . " cards to Firebase...");
        pokecardFirebase::set($send);
        $this->info("Done!");
    }

    private function fetchPage(int $page, int $pageSize, int $maxRetries = 3): ?array
    {
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Http::timeout(60)->get('https://api.pokemontcg.io/v2/cards', [
                    'page' => $page,
                    'pageSize' => $pageSize,
                ]);

                return $response->json()['data'] ?? [];

            } catch (\Exception $e) {
                $this->warn("Page $page attempt $attempt failed: {$e->getMessage()}");
                if ($attempt < $maxRetries) {
                    $this->info("Retrying in 5 seconds...");
                    sleep(5);
                }
            }
        }

        return null;
    }

}
