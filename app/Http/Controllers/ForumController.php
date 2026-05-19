<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FirebaseHelper;
use App\Services\CloudinaryService;
use Illuminate\Support\Str;

class ForumController extends Controller
{
    public function index()
    {
        $threadsData = FirebaseHelper::baca('forums/threads') ?? [];
        $threads = [];

        foreach ($threadsData as $id => $data) {
            $data['id'] = $id;
            
            // Hitung likes dan dislikes
            $likes = isset($data['likes']) ? count($data['likes']) : 0;
            $dislikes = isset($data['dislikes']) ? count($data['dislikes']) : 0;
            $data['likes_count'] = $likes;
            $data['dislikes_count'] = $dislikes;

            $threads[] = $data;
        }

        // Urutkan berdasarkan createdAt terbaru
        usort($threads, function ($a, $b) {
            $aTime = $a['createdAt'] ?? 0;
            $bTime = $b['createdAt'] ?? 0;
            return $bTime <=> $aTime;
        });

        return view('forum.index', compact('threads'));
    }

    public function show($threadId)
    {
        $thread = FirebaseHelper::baca("forums/threads/{$threadId}");
        if (!$thread) {
            abort(404);
        }

        $thread['id'] = $threadId;

        $likes    = is_array($thread['likes']    ?? null) ? $thread['likes']    : [];
        $dislikes = is_array($thread['dislikes'] ?? null) ? $thread['dislikes'] : [];
        $rawComments = is_array($thread['comments'] ?? null) ? $thread['comments'] : [];

        $likeCount    = count($likes);
        $dislikeCount = count($dislikes);
        $commentCount = count($rawComments);

        // Check user status
        $uid        = session('user.uid');
        $userLiked    = $uid && isset($likes[$uid]);
        $userDisliked = $uid && isset($dislikes[$uid]);

        // Build messages with avatar + formatted time
        $messages = [];
        foreach ($rawComments as $cid => $cdata) {
            if (empty($cdata)) continue;

            $parsedReactions = [];
            if (!empty($cdata['reactions']) && is_array($cdata['reactions'])) {
                foreach ($cdata['reactions'] as $emoji => $users) {
                    $count = is_array($users) ? count($users) : 0;
                    if ($count > 0) {
                        $parsedReactions[] = [
                            'emoji' => $emoji,
                            'count' => $count,
                            'reacted' => $uid && isset($users[$uid])
                        ];
                    }
                }
            }

            $pfp = $cdata['photoURL'] ?? 'pfp6';
            $avatar = str_starts_with($pfp, 'http') ? $pfp : '/images/avatar/' . $pfp . '.png';
            $messages[] = [
                'id'               => $cid,
                'uid'              => $cdata['uid'] ?? '',
                'displayName'      => $cdata['displayName'] ?? 'User',
                'username'         => $cdata['username'] ?? 'user',
                'avatar'           => $avatar,
                'text'             => $cdata['text'] ?? '',
                'createdAt'        => $cdata['createdAt'] ?? 0,
                'createdAtFormatted' => isset($cdata['createdAt'])
                    ? date('d M Y, H:i', $cdata['createdAt'] / 1000) : '',
                'replyTo'          => $cdata['replyTo'] ?? '',
                'reactions'        => $parsedReactions,
            ];
        }
        usort($messages, fn($a, $b) => $a['createdAt'] <=> $b['createdAt']);

        // Resolve thread author avatar
        $pfp = $thread['photoURL'] ?? 'pfp6';
        $thread['authorAvatar'] = str_starts_with($pfp, 'http') ? $pfp : '/images/avatar/' . $pfp . '.png';
        $thread['thumbnailSrc'] = !empty($thread['thumbnailUrl'])
            ? $thread['thumbnailUrl'] : '/images/pfp.png';

        // Popular threads for sidebar (top 5 by likes)
        $allThreadsRaw = FirebaseHelper::baca('forums/threads') ?? [];
        $popularThreads = [];
        foreach ($allThreadsRaw as $tid => $tdata) {
            if (empty($tdata['title'])) continue;
            $popularThreads[] = [
                'id'         => $tid,
                'title'      => $tdata['title'],
                'like_count' => is_array($tdata['likes'] ?? null) ? count($tdata['likes']) : 0,
            ];
        }
        usort($popularThreads, fn($a, $b) => $b['like_count'] <=> $a['like_count']);
        $popularThreads = array_slice($popularThreads, 0, 5);

        // Fetch current user role
        $currentUser = null;
        if ($uid) {
            $currentUser = FirebaseHelper::baca("users/{$uid}");
        }
        $isAdmin = ($currentUser['role'] ?? 'user') === 'admin';

        return view('forum.show', compact(
            'thread', 'threadId', 'messages',
            'popularThreads', 'likeCount', 'dislikeCount',
            'commentCount', 'userLiked', 'userDisliked', 'uid', 'isAdmin'
        ));
    }

    public function create()
    {
        if (!session()->has('user')) {
            return redirect('/login')->with('error', 'Anda harus login untuk membuat diskusi.');
        }

        return view('forum.create');
    }

    public function store(Request $request, CloudinaryService $cloudinaryService)
    {
        if (!session()->has('user')) {
            return redirect('/login');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'thumbnail' => 'nullable|image|max:10240',
        ]);

        $uid = session('user.uid');
        $user = FirebaseHelper::baca("users/{$uid}");

        $thumbnailUrl = null;
        $cloudinaryPublicId = null;

        if ($request->hasFile('thumbnail')) {
            try {
                $uploadResult = $cloudinaryService->uploadImage($request->file('thumbnail'));
                $thumbnailUrl = $uploadResult['secure_url'];
                $cloudinaryPublicId = $uploadResult['public_id'];
            } catch (\Exception $e) {
                return back()->withInput()->withErrors(['thumbnail' => 'Gagal upload gambar: ' . $e->getMessage()]);
            }
        }

        $excerpt = Str::limit(strip_tags($request->body), 100);

        $threadData = [
            'title' => $request->title,
            'body' => $request->body,
            'excerpt' => $excerpt,
            'thumbnailUrl' => $thumbnailUrl,
            'cloudinaryPublicId' => $cloudinaryPublicId,
            'category' => $request->category ?? 'General',
            'createdAt' => time() * 1000,
            'updatedAt' => time() * 1000,
            'uid' => $uid,
            'displayName' => $user['name'] ?? 'User',
            'username' => strtolower(str_replace(' ', '', $user['name'] ?? 'user')),
            'photoURL' => $user['pfp'] ?? 'pfp6',
        ];

        // Random ID for thread
        $threadId = Str::random(20);
        FirebaseHelper::buatParent("forums/threads/{$threadId}", $threadData);

        return redirect()->route('forum.index')->with('success', 'Diskusi berhasil dibuat!');
    }

    public function like($threadId)
    {
        if (!session()->has('user')) {
            return back()->with('error', 'Harap login terlebih dahulu.');
        }

        $uid = session('user.uid');
        
        $hasLiked = FirebaseHelper::adakah("forums/threads/{$threadId}/likes/{$uid}");
        
        if ($hasLiked) {
            FirebaseHelper::hapus("forums/threads/{$threadId}/likes/{$uid}");
        } else {
            // Remove dislike if any
            FirebaseHelper::hapus("forums/threads/{$threadId}/dislikes/{$uid}");
            // Add like
            FirebaseHelper::buatParent("forums/threads/{$threadId}/likes/{$uid}", [
                'uid' => $uid,
                'createdAt' => time() * 1000
            ]);
        }

        return back();
    }

    public function dislike($threadId)
    {
        if (!session()->has('user')) {
            return back()->with('error', 'Harap login terlebih dahulu.');
        }

        $uid = session('user.uid');
        
        $hasDisliked = FirebaseHelper::adakah("forums/threads/{$threadId}/dislikes/{$uid}");
        
        if ($hasDisliked) {
            FirebaseHelper::hapus("forums/threads/{$threadId}/dislikes/{$uid}");
        } else {
            // Remove like if any
            FirebaseHelper::hapus("forums/threads/{$threadId}/likes/{$uid}");
            // Add dislike
            FirebaseHelper::buatParent("forums/threads/{$threadId}/dislikes/{$uid}", [
                'uid' => $uid,
                'createdAt' => time() * 1000
            ]);
        }

        return back();
    }

    public function comment(Request $request, $threadId)
    {
        if (!session()->has('user')) {
            return back()->with('error', 'Harap login terlebih dahulu.');
        }

        $request->validate([
            'text' => 'required|string'
        ]);

        $uid = session('user.uid');
        $user = FirebaseHelper::baca("users/{$uid}");

        $commentId = Str::random(20);
        $commentData = [
            'uid' => $uid,
            'displayName' => $user['name'] ?? 'User',
            'username' => strtolower(str_replace(' ', '', $user['name'] ?? 'user')),
            'photoURL' => $user['pfp'] ?? 'pfp6',
            'text' => $request->text,
            'replyTo' => $request->replyTo ?? '',
            'createdAt' => time() * 1000
        ];

        FirebaseHelper::buatParent("forums/threads/{$threadId}/comments/{$commentId}", $commentData);

        if ($request->wantsJson() || $request->ajax()) {
            $pfp = $commentData['photoURL'];
            $commentData['avatar'] = str_starts_with($pfp, 'http') ? $pfp : '/images/avatar/' . $pfp . '.png';
            $commentData['id'] = $commentId;
            $commentData['createdAtFormatted'] = date('d M Y, H:i', $commentData['createdAt'] / 1000);
            return response()->json(['success' => true, 'message' => $commentData]);
        }

        return back()->with('success', 'Komentar berhasil ditambahkan!');
    }

    // ── Hapus komentar ──
    public function deleteComment(Request $request, $threadId, $commentId)
    {
        if (!session()->has('user')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $uid = session('user.uid');
        $comment = FirebaseHelper::baca("forums/threads/{$threadId}/comments/{$commentId}");

        if (!$comment || ($comment['uid'] ?? '') !== $uid) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        FirebaseHelper::hapus("forums/threads/{$threadId}/comments/{$commentId}");

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Komentar dihapus.');
    }

    // ── Edit komentar ──
    public function editComment(Request $request, $threadId, $commentId)
    {
        if (!session()->has('user')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate(['text' => 'required|string|max:1000']);

        $uid = session('user.uid');
        $comment = FirebaseHelper::baca("forums/threads/{$threadId}/comments/{$commentId}");

        if (!$comment || ($comment['uid'] ?? '') !== $uid) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        FirebaseHelper::buatParent("forums/threads/{$threadId}/comments/{$commentId}", array_merge(
            (array) $comment,
            ['text' => $request->text, 'updatedAt' => time() * 1000]
        ));

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'text' => $request->text]);
        }
        return back()->with('success', 'Komentar diperbarui.');
    }

    // ── React komentar (toggle emoji) ──
    public function reactComment(Request $request, $threadId, $commentId)
    {
        if (!session()->has('user')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate(['emoji' => 'required|string|max:10']);

        $uid   = session('user.uid');
        $emoji = $request->emoji;
        $path  = "forums/threads/{$threadId}/comments/{$commentId}/reactions/{$emoji}/{$uid}";

        $exists = FirebaseHelper::adakah($path);
        if ($exists) {
            FirebaseHelper::hapus($path);
            $reacted = false;
        } else {
            FirebaseHelper::buatParent($path, ['uid' => $uid, 'createdAt' => time() * 1000]);
            $reacted = true;
        }

        // Return updated count
        $reactions = FirebaseHelper::baca("forums/threads/{$threadId}/comments/{$commentId}/reactions/{$emoji}") ?? [];
        $count = is_array($reactions) ? count($reactions) : 0;

        return response()->json(['success' => true, 'reacted' => $reacted, 'count' => $count]);
    }

    // ── Hapus thread ──
    public function destroy($threadId)
    {
        $uid = session('user.uid');

        if (!$uid) {
            return redirect()->route('login')->with('error', 'Login dulu untuk menghapus forum.');
        }

        $thread = FirebaseHelper::baca("forums/threads/{$threadId}");

        if (!$thread) {
            abort(404);
        }

        if (($thread['uid'] ?? null) !== $uid) {
            abort(403, 'Kamu tidak punya akses untuk menghapus forum ini.');
        }

        $createdAt = $thread['createdAt'] ?? null;

        if (!$createdAt) {
            return back()->with('error', 'Waktu pembuatan forum tidak valid.');
        }

        try {
            if (is_numeric($createdAt)) {
                $createdAtString = (string) $createdAt;
                if (strlen($createdAtString) >= 13) {
                    $createdTime = \Carbon\Carbon::createFromTimestampMs((int) $createdAt);
                } else {
                    $createdTime = \Carbon\Carbon::createFromTimestamp((int) $createdAt);
                }
            } else {
                $createdTime = \Carbon\Carbon::parse($createdAt);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Format waktu forum tidak valid.');
        }

        $minutesPassed = $createdTime->diffInMinutes(now());

        if ($minutesPassed < 10) {
            $remaining = 10 - $minutesPassed;
            return back()->with('error', 'Forum baru bisa dihapus setelah 10 menit. Tunggu sekitar ' . ceil($remaining) . ' menit lagi.');
        }

        if (!empty($thread['cloudinaryPublicId'])) {
            try {
                // If CloudinaryService exists, delete it.
                // app(CloudinaryService::class)->delete($thread['cloudinaryPublicId']);
            } catch (\Exception $e) {
                // Optional log only
            }
        }

        FirebaseHelper::hapus("forums/threads/{$threadId}");

        return redirect()->route('forum.index')->with('success', 'Forum berhasil dihapus.');
    }
}
