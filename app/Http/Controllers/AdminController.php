<?php

namespace App\Http\Controllers;

use App\Models\FirebaseHelper;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // ── Hapus Thread Forum ──
    public function deleteForumThread($threadId)
    {
        $thread = FirebaseHelper::baca("forums/threads/{$threadId}");
        if (!$thread) {
            return back()->with('error', 'Thread tidak ditemukan.');
        }

        FirebaseHelper::hapus("forums/threads/{$threadId}");
        return redirect()->route('forum.index')->with('success', 'Thread berhasil dihapus oleh Admin.');
    }

    // ── Hapus Komentar Forum ──
    public function deleteForumComment($threadId, $commentId)
    {
        $comment = FirebaseHelper::baca("forums/{$threadId}/messages/{$commentId}");
        $isNewPath = true;
        if (!$comment) {
            $comment = FirebaseHelper::baca("forums/threads/{$threadId}/comments/{$commentId}");
            $isNewPath = false;
        }

        if (!$comment) {
            return response()->json(['error' => 'Komentar tidak ditemukan.'], 404);
        }

        if ($isNewPath) {
            FirebaseHelper::hapus("forums/{$threadId}/messages/{$commentId}");
        } else {
            FirebaseHelper::hapus("forums/threads/{$threadId}/comments/{$commentId}");
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Komentar berhasil dihapus oleh Admin.');
    }

    // ── Hapus Komentar Kartu ──
    public function deleteCardComment($cardId, $commentId)
    {
        $comment = FirebaseHelper::baca("cards/{$cardId}/comments/{$commentId}");
        if (!$comment) {
            return response()->json(['error' => 'Komentar tidak ditemukan.'], 404);
        }

        FirebaseHelper::hapus("cards/{$cardId}/comments/{$commentId}");

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Komentar kartu berhasil dihapus oleh Admin.');
    }

    // ── Hapus Penawaran (Offer) Kartu ──
    public function deleteCardOffer($cardId, $offerId)
    {
        $offer = FirebaseHelper::baca("cards/{$cardId}/offers/{$offerId}");
        if (!$offer) {
            return response()->json(['error' => 'Offer tidak ditemukan.'], 404);
        }

        FirebaseHelper::hapus("cards/{$cardId}/offers/{$offerId}");

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Offer berhasil dihapus oleh Admin.');
    }

    // ── Hapus Balasan (Reply) Penawaran ──
    public function deleteOfferReply($cardId, $offerId, $replyId)
    {
        $reply = FirebaseHelper::baca("cards/{$cardId}/offers/{$offerId}/replies/{$replyId}");
        if (!$reply) {
            return response()->json(['error' => 'Reply tidak ditemukan.'], 404);
        }

        FirebaseHelper::hapus("cards/{$cardId}/offers/{$offerId}/replies/{$replyId}");

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Reply berhasil dihapus oleh Admin.');
    }
}
