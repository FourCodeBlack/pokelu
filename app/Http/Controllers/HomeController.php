<?php

namespace App\Http\Controllers;

use App\Models\FirebaseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    /**
     * Tampilkan halaman /home dengan kartu TCG terpopuler
     * berdasarkan jumlah offer terbanyak di Firebase.
     */
    public function index()
    {
        // ── 1. Ambil semua data cards dari Firebase ──
        $cardsRaw = FirebaseHelper::baca('cards') ?? [];

        // ── 2. Hitung jumlah offer tiap kartu ──
        $scored = [];
        foreach ($cardsRaw as $cardId => $cardData) {
            $offers     = $cardData['offers'] ?? [];
            $offerCount = is_array($offers) ? count($offers) : 0;

            if ($offerCount > 0) {
                $scored[] = [
                    'id'          => $cardId,
                    'offer_count' => $offerCount,
                ];
            }
        }

        // ── 3. Urutkan descending & ambil top 12 ──
        usort($scored, fn ($a, $b) => $b['offer_count'] - $a['offer_count']);
        $scored = array_slice($scored, 0, 12);

        // ── 4. Enrich dengan detail kartu dari Pokemon TCG API ──
        $popularCards = [];
        foreach ($scored as $entry) {
            $id     = $entry['id'];
            $detail = Cache::remember("pokelu_card_detail_{$id}", 3600, function () use ($id) {
                try {
                    $res = Http::timeout(6)->get("https://api.pokemontcg.io/v2/cards/{$id}");
                    if ($res->successful()) {
                        $raw = $res->json()['data'] ?? [];
                        return [
                            'name'  => $raw['name']                                         ?? $id,
                            'image' => $raw['images']['small'] ?? $raw['images']['large']   ?? null,
                        ];
                    }
                } catch (\Throwable $e) {
                    // Fallback jika API gagal
                }
                return ['name' => $id, 'image' => null];
            });

            $popularCards[] = [
                'id'          => $id,
                'name'        => $detail['name'],
                'image'       => $detail['image'],
                'offer_count' => $entry['offer_count'],
            ];
        }

        // ── 5. Ambil forum populer berdasarkan like terbanyak ──
        $threadsRaw  = FirebaseHelper::baca('forums/threads') ?? [];
        $forumScored = [];

        foreach ($threadsRaw as $threadId => $thread) {
            if (empty($thread) || empty($thread['title'])) continue;

            $likeCount    = is_array($thread['likes']    ?? null) ? count($thread['likes'])    : 0;
            $dislikeCount = is_array($thread['dislikes'] ?? null) ? count($thread['dislikes']) : 0;

            $pfp       = $thread['photoURL'] ?? 'pfp6';
            $avatarSrc = str_starts_with($pfp, 'http')
                ? $pfp
                : '/images/avatar/' . $pfp . '.png';

            $forumScored[] = [
                'id'            => $threadId,
                'title'         => $thread['title'],
                'excerpt'       => $thread['excerpt'] ?? \Illuminate\Support\Str::limit(strip_tags($thread['body'] ?? ''), 180),
                'thumbnailUrl'  => $thread['thumbnailUrl'] ?? null,
                'displayName'   => $thread['displayName'] ?? 'Unknown',
                'username'      => $thread['username']    ?? ($thread['displayName'] ?? 'unknown'),
                'avatarSrc'     => $avatarSrc,
                'like_count'    => $likeCount,
                'dislike_count' => $dislikeCount,
                'createdAt'     => $thread['createdAt'] ?? 0,
            ];
        }

        // Sort: like terbanyak, jika sama urutkan berdasarkan createdAt terbaru
        usort($forumScored, function ($a, $b) {
            if ($b['like_count'] !== $a['like_count']) {
                return $b['like_count'] - $a['like_count'];
            }
            return $b['createdAt'] - $a['createdAt'];
        });

        // Ambil 1 paling populer
        $popularForum = $forumScored[0] ?? null;

        return view('home.index', compact('popularCards', 'popularForum'));
    }
}
