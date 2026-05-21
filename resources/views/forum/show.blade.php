@extends('layout.app')
@section('navbar')
    @include('layout.navbar')
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/forum-discord.css?v=' . time()) }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.typing-indicator {
    font-size: 0.8rem;
    color: #a0aec0;
    padding: 0 16px 8px;
    font-style: italic;
    animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .5; }
}
/* Animasi pesan baru */
.message-new {
    animation: slideIn 0.3s ease-out forwards;
}
@keyframes slideIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.message-item {
    position: relative;
    cursor: default;
}
body.selection-mode .forum-message {
    cursor: pointer;
}
.forum-message.selected {
    background: rgba(168, 85, 247, 0.22);
    outline: 1px solid rgba(168, 85, 247, 0.7);
}
.selection-check {
    display: none;
    width: 22px;
    height: 22px;
    border-radius: 999px;
    align-items: center;
    justify-content: center;
    background: #a855f7;
    color: white;
    font-size: 12px;
    position: absolute;
    left: -10px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
}
.forum-message.selected .selection-check {
    display: flex;
}
.selection-toolbar {
    position: sticky;
    bottom: 80px;
    z-index: 20;
    background: #2b1740;
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 12px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: white;
    margin: 10px 20px;
}
.selection-toolbar.hidden {
    display: none !important;
}

/* ── Forum Image Upload Styles ── */
.forum-image-preview {
    position: relative;
    width: 72px;
    height: 72px;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid rgba(168, 85, 247, 0.35);
    background: rgba(255, 255, 255, 0.04);
    margin: 8px 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.forum-image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
#removeForumImageBtn {
    position: absolute;
    top: 4px;
    right: 4px;
    border: none;
    width: 20px;
    height: 20px;
    border-radius: 999px;
    background: rgba(239, 68, 68, 0.9);
    color: white;
    cursor: pointer;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}
.forum-image-btn {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--dc-border);
    border-radius: 10px;
    color: var(--dc-muted);
    width: 42px;
    height: 42px;
    flex-shrink: 0;
    cursor: pointer;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s, transform 0.15s;
}
.forum-image-btn:hover {
    background: rgba(124, 77, 255, 0.2);
    color: #fff;
    transform: scale(1.05);
}
.forum-message-image-btn {
    margin-top: 8px;
    border: none;
    padding: 0;
    background: transparent;
    cursor: pointer;
    display: block;
    max-width: 340px;
    text-align: left;
}
.forum-message-image {
    display: block;
    max-width: 340px;
    max-height: 280px;
    border-radius: 12px;
    object-fit: cover;
    border: 1px solid rgba(124, 77, 255, 0.25);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    transition: transform 0.2s;
}
.forum-message-image:hover {
    transform: scale(1.01);
}
.forum-image-modal {
    position: fixed;
    inset: 0;
    z-index: 99999;
    background: rgba(8, 4, 18, 0.88);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    backdrop-filter: blur(4px);
}
.forum-image-modal img {
    max-width: min(92vw, 900px);
    max-height: 86vh;
    border-radius: 14px;
    object-fit: contain;
    box-shadow: 0 24px 70px rgba(0, 0, 0, 0.5);
}
.forum-image-modal-close {
    position: fixed;
    top: 24px;
    right: 24px;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 999px;
    background: var(--dc-pink);
    color: #ffffff;
    font-size: 22px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.forum-upload-progress {
    display: none;
    height: 4px;
    background: rgba(255, 255, 255, 0.08);
    position: relative;
    width: 100%;
}
.forum-upload-progress.active {
    display: block;
}
.forum-upload-progress-fill {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, var(--dc-pink), var(--dc-purple));
    transition: width 0.1s ease;
}
.forum-upload-progress-text {
    position: absolute;
    right: 16px;
    top: -20px;
    font-size: 0.72rem;
    color: var(--dc-muted);
}
.forum-image-modal.hidden,
.hidden {
    display: none !important;
}
@media (max-width: 768px) {
    .forum-message-image,
    .forum-message-image-btn {
        max-width: 240px;
    }
}
</style>
@endpush

@php
    use App\Models\FirebaseHelper;
    $currentUid = session('user.uid');
    $currentUsername = session('user.name') ?? session('user.username') ?? 'User';
    $currentPfp = 'default';
    if ($currentUid) {
        $fbUser = FirebaseHelper::baca("users/{$currentUid}");
        $currentPfp = $fbUser['pfp'] ?? 'default';
    }
@endphp

@section('content')
<div class="discord-forum-page">

    {{-- ── SIDEBAR KIRI ── --}}
    <aside class="forum-sidebar">
        <div class="forum-sidebar-title">
            <i class="fa-solid fa-comments"></i> POKELU FORUM
        </div>

        <a href="{{ route('forum.index') }}" class="forum-back-link">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Forum
        </a>

        {{-- Forum Populer --}}
        <div class="sidebar-section">
            <div class="sidebar-section-title">Forum Populer</div>

            @forelse($popularThreads as $popular)
                <a href="{{ route('forum.show', $popular['id']) }}"
                   class="sidebar-thread {{ $popular['id'] === $threadId ? 'active' : '' }}">
                    <span class="sidebar-hash">#</span>
                    <span class="sidebar-thread-title">{{ Str::limit($popular['title'], 28) }}</span>
                    @if($popular['like_count'] > 0)
                        <span class="sidebar-likes">{{ $popular['like_count'] }}</span>
                    @endif
                </a>
            @empty
                <span class="sidebar-empty">Belum ada thread.</span>
            @endforelse
        </div>
    </aside>

    {{-- ── MAIN CHAT AREA ── --}}
    <main class="forum-chat-main">

        {{-- Header Channel --}}
        <header class="chat-header">
            <div class="chat-header-info">
                <h1 class="chat-header-title">
                    <span class="chat-hash">#</span>{{ Str::slug($thread['title']) }}
                </h1>
                <p class="chat-header-sub">
                    {{ $commentCount }} pesan &nbsp;·&nbsp; {{ $thread['category'] ?? 'general' }}
                </p>
            </div>

            <div class="chat-header-actions">
                <form method="POST" action="{{ route('forum.like', $threadId) }}">
                    @csrf
                    <button type="submit" class="btn-reaction {{ $userLiked ? 'active' : '' }}" title="Like">
                        <i class="fa-solid fa-thumbs-up"></i> {{ $likeCount }}
                    </button>
                </form>
                <form method="POST" action="{{ route('forum.dislike', $threadId) }}">
                    @csrf
                    <button type="submit" class="btn-reaction {{ $userDisliked ? 'active liked-down' : '' }}" title="Dislike">
                        <i class="fa-solid fa-thumbs-down"></i> {{ $dislikeCount }}
                    </button>
                </form>
            </div>
        </header>

        {{-- Scrollable Chat Area --}}
        <section class="chat-scroll-area" id="chatScrollArea">

            @if(session('success'))
                <div class="flash flash-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="flash flash-error">{{ session('error') }}</div>
            @endif

            {{-- ── PINNED THREAD CARD ── --}}
            <article class="pinned-thread-card">
                <div class="pinned-badge">
                    <i class="fa-solid fa-thumbtack"></i> Pinned Discussion
                </div>
                <div class="pinned-inner">
                    <img src="{{ $thread['thumbnailSrc'] }}"
                         alt="Thumbnail"
                         class="pinned-thumbnail"
                         onerror="this.src='/images/pfp.png'">
                    <div class="pinned-content">
                        <h2 class="pinned-title">{{ $thread['title'] }}</h2>
                        <p class="pinned-body">{{ $thread['body'] }}</p>
                        <div class="pinned-author">
                            <img src="{{ $thread['authorAvatar'] }}"
                                 alt="Avatar"
                                 class="pinned-author-avatar"
                                 onerror="this.src='/images/avatar/pfp6.png'">
                            <div>
                                <strong>{{ $thread['displayName'] ?? 'User' }}</strong>
                                <span>{{ '@' . ($thread['username'] ?? 'user') }}</span>
                            </div>
                            @if(!empty($thread['createdAt']))
                                <time class="pinned-time">{{ date('d M Y', $thread['createdAt'] / 1000) }}</time>
                            @endif
                        </div>
                    </div>
                </div>

                @php
                    $isOwner = $currentUid && (($thread['uid'] ?? null) === $currentUid);
                    $canDelete = false;
                    $deleteRemainingMinutes = null;

                    try {
                        $createdAt = $thread['createdAt'] ?? null;
                        if ($createdAt) {
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
                            $minutesPassed = $createdTime->diffInMinutes(now());
                            $canDelete = $minutesPassed >= 10;
                            $deleteRemainingMinutes = max(0, 10 - $minutesPassed);
                        }
                    } catch (\Exception $e) {
                        $canDelete = false;
                    }
                @endphp

                @if($isOwner)
                    <div class="thread-owner-actions">
                        @if($canDelete)
                            <form method="POST"
                                  action="{{ route('forum.destroy', $threadId) }}"
                                  onsubmit="return confirm('Yakin ingin menghapus forum ini? Semua komentar, like, dan dislike juga akan terhapus.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-thread-btn">
                                    <i class="fa-solid fa-trash"></i> Hapus Forum
                                </button>
                            </form>
                        @else
                            <button type="button" class="delete-thread-btn disabled" disabled>
                                Hapus tersedia dalam {{ ceil($deleteRemainingMinutes) }} menit
                            </button>
                        @endif
                    </div>
                @endif
                
                @if(!$isOwner && ($isAdmin ?? false))
                    <div class="thread-owner-actions">
                        <form method="POST"
                              action="{{ route('admin.forum.destroy', $threadId) }}"
                              onsubmit="return confirm('Yakin ingin menghapus thread ini sebagai Admin?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-delete-btn">
                                <i class="fa-solid fa-shield"></i> Hapus Thread (Admin)
                            </button>
                        </form>
                    </div>
                @endif
            </article>

            <div class="msg-divider">
                <span>Diskusi dimulai · {{ $commentCount }} pesan</span>
            </div>

            {{-- ── MESSAGE LIST ── --}}
            <div class="message-list" id="messageList">
                @forelse($messages as $message)
                    @php
                        $isOwn = $currentUid && $message['uid'] === $currentUid;
                    @endphp
                    <div class="forum-message" id="msg-{{ $message['id'] }}"
                         data-msg-id="{{ $message['id'] }}"
                         data-msg-uid="{{ $message['uid'] }}"
                         data-user-uid="{{ $message['uid'] }}"
                         data-msg-text="{{ htmlspecialchars($message['text'], ENT_QUOTES) }}"
                         data-is-own="{{ $isOwn ? '1' : '0' }}"
                         oncontextmenu="showCtxMenu(event, '{{ $message['id'] }}', {{ $isOwn ? 'true' : 'false' }}, {{ ($isAdmin ?? false) ? 'true' : 'false' }})">

                        <div class="selection-check">✓</div>

                        <img src="{{ $message['avatar'] }}"
                             alt="{{ $message['displayName'] }}"
                             class="message-avatar chat-avatar"
                             onerror="this.src='/images/avatar/pfp6.png'">

                        <div class="message-body">
                            <div class="message-meta">
                                <strong class="message-name chat-username">{{ $message['displayName'] }}</strong>
                                <span class="message-username chat-handle">{{ '@' . $message['username'] }}</span>
                                <time class="message-time">{{ $message['createdAtFormatted'] }}</time>
                            </div>
                            {{-- Reply preview --}}
                            @if(!empty($message['replyTo']))
                                <div class="reply-preview">
                                    <i class="fa-solid fa-reply fa-flip-horizontal"></i>
                                    {{ $message['replyTo'] }}
                                </div>
                            @endif
                            @if(!empty($message['text']))
                                <p class="message-text" id="text-{{ $message['id'] }}">{{ $message['text'] }}</p>
                            @endif
                            @if(!empty($message['imageUrl']))
                                <button type="button" class="forum-message-image-btn">
                                    <img src="{{ $message['imageUrl'] }}"
                                         class="forum-message-image"
                                         alt="Forum image">
                                </button>
                            @endif

                            {{-- Reaction bar --}}
                            <div class="reaction-bar" id="reactions-{{ $message['id'] }}">
                                @if(!empty($message['reactions']))
                                    @foreach($message['reactions'] as $react)
                                        <button class="react-chip {{ $react['reacted'] ? 'reacted' : '' }}" 
                                                data-emoji="{{ $react['emoji'] }}"
                                                onclick="activeMsg = { id: '{{ $message['id'] }}', isOwn: {{ $isOwn ? 'true' : 'false' }} }; sendReact('{{ $react['emoji'] }}')">
                                            {{ $react['emoji'] }} <span>{{ $react['count'] }}</span>
                                        </button>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        {{-- 3-dot button (visible on hover) --}}
                        <button class="msg-dots-btn"
                                title="Opsi"
                                onclick="showCtxMenu(event, '{{ $message['id'] }}', {{ $isOwn ? 'true' : 'false' }}, {{ ($isAdmin ?? false) ? 'true' : 'false' }}); event.stopPropagation();">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>
                    </div>
                @empty
                    <div class="empty-message">
                        <i class="fa-regular fa-comment-dots"></i>
                        <p>Belum ada pesan. Jadilah yang pertama memulai diskusi!</p>
                    </div>
                @endforelse
            </div>

        </section>

        {{-- ── SELECTION TOOLBAR ── --}}
        <div id="selectionToolbar" class="selection-toolbar hidden">
            <span id="selectedCount">0 pesan dipilih</span>
            <div>
                <button type="button" id="cancelSelectionBtn" class="chat-send-btn" style="background:transparent; border:1px solid rgba(255,255,255,0.2); margin-right:10px;">Batal</button>
                <button type="button" id="deleteSelectedBtn" class="chat-send-btn" style="background:#ef4444;">Hapus</button>
            </div>
        </div>

        {{-- ── REPLY BAR (hidden by default) ── --}}
        <div class="reply-bar" id="replyBar" style="display:none;">
            <div class="reply-bar-inner">
                <i class="fa-solid fa-reply fa-flip-horizontal"></i>
                <span id="replyBarText">Membalas <strong id="replyBarName"></strong></span>
            </div>
            <button onclick="cancelReply()" class="reply-bar-close"><i class="fa-solid fa-xmark"></i></button>
        </div>

        {{-- ── TYPING INDICATOR ── --}}
        <div id="typingIndicator" class="typing-indicator" style="display:none;"></div>

        {{-- ── INPUT BAR ── --}}
        @if(session()->has('user'))
            <!-- Progress Bar Upload -->
            <div class="forum-upload-progress" id="forumUploadProgress">
                <div class="forum-upload-progress-fill" id="forumUploadProgressFill"></div>
                <div class="forum-upload-progress-text" id="forumUploadProgressText">0%</div>
            </div>

            <form class="chat-input-bar"
                  id="commentForm"
                  style="flex-wrap: wrap;">
                @csrf
                <input type="hidden" name="replyTo" id="replyToInput" value="">
                
                <div id="forumImagePreview" class="forum-image-preview hidden">
                    <img id="forumPreviewImg" src="" alt="Preview">
                    <button type="button" id="removeForumImageBtn">×</button>
                </div>

                <div style="display: flex; width: 100%; align-items: flex-end; gap: 10px;">
                    <button type="button" id="forumImageBtn" class="forum-image-btn" title="Upload Gambar">
                        <i class="fa-solid fa-paperclip"></i>
                    </button>
                    <input type="file"
                           id="forumImageInput"
                           accept="image/png,image/jpeg,image/jpg,image/webp"
                           hidden>

                    <textarea name="text"
                              class="chat-textarea"
                              id="chatTextarea"
                              placeholder="Kirim pesan ke #{{ Str::slug($thread['title']) }}"
                              rows="1"
                              maxlength="1000"></textarea>
                    <button type="submit" class="chat-send-btn" id="sendBtn" title="Kirim">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        @else
            <div class="chat-login-prompt">
                Silakan <a href="{{ route('login') }}">login</a> untuk bergabung dalam diskusi.
            </div>
        @endif

    </main>
</div>

{{-- ── CONTEXT MENU ── --}}
<div id="ctxMenu" class="ctx-menu" style="display:none;" onclick="event.stopPropagation()">
    <button class="ctx-item" onclick="doReply()">
        <i class="fa-solid fa-reply fa-flip-horizontal"></i> Balas
    </button>
    <button class="ctx-item" onclick="openEmojiPicker()">
        <i class="fa-solid fa-face-smile"></i> React
    </button>
    
    <div class="ctx-divider can-delete-only"></div>
    <button class="ctx-item can-delete-only" onclick="doSelectMessage()">
        <i class="fa-regular fa-square-check"></i> Pilih Pesan
    </button>

    <div class="ctx-divider own-only"></div>
    <button class="ctx-item own-only" onclick="doEdit()">
        <i class="fa-solid fa-pen"></i> Edit
    </button>
    <button class="ctx-item ctx-danger own-only" onclick="doDelete()">
        <i class="fa-solid fa-trash"></i> Hapus
    </button>

    <div class="ctx-divider admin-only"></div>
    <button class="ctx-item ctx-danger admin-only" onclick="doAdminDelete()">
        <i class="fa-solid fa-shield"></i> Hapus (Admin)
    </button>
</div>

{{-- ── EMOJI PICKER ── --}}
<div id="emojiPicker" class="emoji-picker" style="display:none;">
    @foreach(['👍','❤️','😂','😮','😢','🔥','🎉','👏'] as $e)
        <button class="emoji-btn" onclick="sendReact('{{ $e }}')">{{ $e }}</button>
    @endforeach
</div>

{{-- ── EDIT MODAL ── --}}
<div id="editModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeEditModal()">
    <div class="modal-box">
        <div class="modal-header">
            <span><i class="fa-solid fa-pen"></i> Edit Pesan</span>
            <button onclick="closeEditModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <textarea id="editTextarea" class="modal-textarea" maxlength="1000" rows="4"></textarea>
        <div class="modal-footer">
            <button class="modal-cancel" onclick="closeEditModal()">Batal</button>
            <button class="modal-save" onclick="submitEdit()">Simpan</button>
        </div>
    </div>
</div>

{{-- ── IMAGE PREVIEW MODAL ── --}}
<div id="forumImageModal" class="forum-image-modal hidden" onclick="if(event.target===this) { this.classList.add('hidden'); document.getElementById('forumImageModalImg').src = ''; }">
    <button type="button" id="closeForumImageModal" class="forum-image-modal-close">×</button>
    <img id="forumImageModalImg" src="" alt="Preview">
</div>

@push('scripts')
<script>
const CSRF   = document.querySelector('meta[name="csrf-token"]').content;
const THREAD = '{{ $threadId }}';
const CURRENT_UID      = '{{ $currentUid ?? '' }}';
const CURRENT_USERNAME = '{{ $currentUsername ?? '' }}';
const CURRENT_PFP      = '{{ $currentPfp ?? 'default' }}';
const IS_ADMIN = {{ ($isAdmin ?? false) ? 'true' : 'false' }};
const FIREBASE_TOKEN = '{{ $firebaseToken ?? '' }}';

// ── Firebase Client-Side Authentication via Custom Token ──
if (FIREBASE_TOKEN) {
    firebase.auth().onAuthStateChanged(async (user) => {
        if (!user || user.uid !== CURRENT_UID) {
            try {
                await firebase.auth().signInWithCustomToken(FIREBASE_TOKEN);
                console.log('Firebase authenticated successfully via custom token');
            } catch (err) {
                console.error('Firebase custom token authentication failed:', err);
            }
        }
    });
}

let activeMsg   = null; // { id, isOwn, isAdmin, el }
let ctxMenu     = document.getElementById('ctxMenu');
let emojiPicker = document.getElementById('emojiPicker');
let editModal   = document.getElementById('editModal');

// ── Auto-scroll to bottom ──
const area = document.getElementById('chatScrollArea');
if (area) area.scrollTop = area.scrollHeight;

// ── Auto-resize textarea ──
const ta = document.querySelector('.chat-textarea');
if (ta) ta.addEventListener('input', () => {
    ta.style.height = 'auto';
    ta.style.height = Math.min(ta.scrollHeight, 120) + 'px';
});

// ── Close menus on outside click ──
document.addEventListener('click', () => {
    ctxMenu.style.display = 'none';
    emojiPicker.style.display = 'none';
});

// ── Show context menu ──
function showCtxMenu(e, msgId, isOwn, isAdmin = false) {
    e.preventDefault();
    const el = document.getElementById('msg-' + msgId);
    activeMsg = { id: msgId, isOwn, isAdmin, el };

    // Show/hide own-only items
    document.querySelectorAll('.own-only').forEach(item => {
        item.style.display = isOwn ? '' : 'none';
    });
    
    // Show/hide admin-only items
    document.querySelectorAll('.admin-only').forEach(item => {
        item.style.display = (!isOwn && isAdmin) ? '' : 'none';
    });

    // Position
    const x = Math.min(e.clientX, window.innerWidth - 180);
    const y = Math.min(e.clientY, window.innerHeight - 200);
    ctxMenu.style.left = x + 'px';
    ctxMenu.style.top  = y + 'px';
    ctxMenu.style.display = 'block';
    emojiPicker.style.display = 'none';
}

// ── Reply ──
function doReply() {
    if (!activeMsg) return;
    ctxMenu.style.display = 'none';
    const name  = activeMsg.el.querySelector('.message-name')?.innerText || 'User';
    const text  = activeMsg.el.querySelector('.message-text')?.innerText || '';
    const short = text.length > 60 ? text.substring(0, 60) + '…' : text;

    document.getElementById('replyBarName').innerText = name + ': ' + short;
    document.getElementById('replyToInput').value = name + ': ' + short;
    document.getElementById('replyBar').style.display = 'flex';
    document.getElementById('chatTextarea')?.focus();
}

function cancelReply() {
    document.getElementById('replyBar').style.display = 'none';
    document.getElementById('replyToInput').value = '';
}

// ── Emoji Picker ──
function openEmojiPicker() {
    ctxMenu.style.display = 'none';
    const rect = ctxMenu.getBoundingClientRect();
    emojiPicker.style.left = (parseInt(ctxMenu.style.left)) + 'px';
    emojiPicker.style.top  = (parseInt(ctxMenu.style.top) - 60) + 'px';
    emojiPicker.style.display = 'flex';
}

async function sendReact(emoji) {
    if (!activeMsg) return;
    emojiPicker.style.display = 'none';
    try {
        await fetch(`/forum/${THREAD}/comments/${activeMsg.id}/react`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ emoji })
        });
    } catch(err) { console.error(err); }
}

function updateReactionBar(msgId, emoji, count, reacted) {
    const bar = document.getElementById('reactions-' + msgId);
    if (!bar) return;
    let btn = bar.querySelector(`[data-emoji="${emoji}"]`);
    if (!btn) {
        btn = document.createElement('button');
        btn.dataset.emoji = emoji;
        btn.className = 'react-chip';
        btn.onclick = () => {
            const isOwn = document.getElementById('msg-'+msgId)?.dataset.isOwn === '1';
            activeMsg = { id: msgId, isOwn: isOwn };
            sendReact(emoji);
        };
        bar.appendChild(btn);
    }
    if (count <= 0) { btn.remove(); return; }
    btn.innerHTML = `${emoji} <span>${count}</span>`;
    btn.classList.toggle('reacted', reacted);
}

// ── Edit ──
function doEdit() {
    if (!activeMsg) return;
    ctxMenu.style.display = 'none';
    const current = document.getElementById('text-' + activeMsg.id)?.innerText || '';
    document.getElementById('editTextarea').value = current;
    editModal.style.display = 'flex';
}

function closeEditModal() {
    editModal.style.display = 'none';
}

async function submitEdit() {
    if (!activeMsg) return;
    const text = document.getElementById('editTextarea').value.trim();
    if (!text) return;
    try {
        await firebase.database().ref(`forums/${THREAD}/messages/${activeMsg.id}`).update({ text: text });
        
        fetch(`/forum/${THREAD}/comments/${activeMsg.id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ text })
        }).catch(err => console.error("Sync error:", err));

        closeEditModal();
    } catch(err) { console.error(err); }
}

// ── Delete ──
async function doDelete() {
    if (!activeMsg) return;
    ctxMenu.style.display = 'none';
    if (!confirm('Hapus pesan ini?')) return;
    try {
        await firebase.database().ref(`forums/${THREAD}/messages/${activeMsg.id}`).remove();
        
        fetch(`/forum/${THREAD}/comments/${activeMsg.id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        }).catch(err => console.error("Sync error:", err));
    } catch(err) { console.error(err); }
}

// ── Delete (Admin) ──
async function doAdminDelete() {
    if (!activeMsg) return;
    ctxMenu.style.display = 'none';
    if (!confirm('Hapus pesan ini sebagai admin?')) return;
    try {
        await firebase.database().ref(`forums/${THREAD}/messages/${activeMsg.id}`).remove();
        
        fetch(`/admin/forum/${THREAD}/comments/${activeMsg.id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        }).catch(err => console.error("Sync error:", err));
    } catch(err) { console.error(err); }
}

let selectedForumImage = null;

function removeUndefined(value) {
    if (Array.isArray(value)) {
        return value.map(removeUndefined);
    }
    if (value && typeof value === 'object') {
        return Object.fromEntries(
            Object.entries(value)
                .filter(([_, v]) => v !== undefined)
                .map(([k, v]) => [k, removeUndefined(v)])
        );
    }
    return value;
}

function showForumUploadProgress(show) {
    const progress = document.getElementById('forumUploadProgress');
    if (!progress) return;
    if (show) {
        progress.classList.add('active');
        document.getElementById('forumUploadProgressFill').style.width = '0%';
        document.getElementById('forumUploadProgressText').textContent  = '0%';
    } else {
        progress.classList.remove('active');
    }
}

async function uploadForumImage(file) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('upload_preset', 'pokelu_storage');

    const endpoint = 'https://api.cloudinary.com/v1_1/dsz8bojjy/image/upload';

    showForumUploadProgress(true);

    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.upload.addEventListener('progress', e => {
            if (e.lengthComputable) {
                const pct = (e.loaded / e.total) * 100;
                const fill = document.getElementById('forumUploadProgressFill');
                const text = document.getElementById('forumUploadProgressText');
                if (fill) fill.style.width = pct + '%';
                if (text) text.textContent = Math.round(pct) + '%';
            }
        });
        xhr.addEventListener('load', () => {
            showForumUploadProgress(false);
            if (xhr.status === 200) {
                const res = JSON.parse(xhr.responseText);
                if (res?.secure_url) {
                    resolve({
                        imageUrl: res.secure_url,
                        publicId: res.public_id
                    });
                } else {
                    reject(new Error('Upload tidak menghasilkan URL'));
                }
            } else {
                reject(new Error(`Upload failed: ${xhr.status} ${xhr.statusText}`));
            }
        });
        xhr.addEventListener('error', () => {
            showForumUploadProgress(false);
            reject(new Error('Network error'));
        });
        xhr.open('POST', endpoint);
        xhr.send(formData);
    });
}

// ── Realtime Submit Comment directly to Firebase ──
async function submitComment(e) {
    if (e) e.preventDefault();
    const textInput = document.getElementById('chatTextarea');
    const replyInput = document.getElementById('replyToInput');
    const btn = document.getElementById('sendBtn');
    
    const text = textInput.value.trim();
    if (!text && !selectedForumImage) return;

    if (!CURRENT_UID) {
        alert('Harap login terlebih dahulu.');
        return;
    }

    btn.disabled = true;
    btn.style.opacity = '0.5';

    try {
        let imageData = null;
        if (selectedForumImage) {
            imageData = await uploadForumImage(selectedForumImage);
        }

        let type = 'text';
        if (text && imageData?.imageUrl) {
            type = 'mixed';
        } else if (imageData?.imageUrl) {
            type = 'image';
        }

        const db = firebase.database();
        const messagesRef = db.ref(`forums/${THREAD}/messages`);
        const newMsgRef = messagesRef.push();
        
        const payload = removeUndefined({
            uid: CURRENT_UID,
            type: type,
            text: text || '',
            imageUrl: imageData?.imageUrl || null,
            imagePublicId: imageData?.publicId || null,
            replyTo: replyInput.value || null,
            createdAt: firebase.database.ServerValue.TIMESTAMP
        });

        await newMsgRef.set(payload);

        textInput.value = '';
        textInput.style.height = '42px'; // Reset height
        
        // Reset image selection
        selectedForumImage = null;
        const fileInput = document.getElementById('forumImageInput');
        if (fileInput) fileInput.value = '';
        const previewWrap = document.getElementById('forumImagePreview');
        if (previewWrap) previewWrap.classList.add('hidden');
        const previewImg = document.getElementById('forumPreviewImg');
        if (previewImg) previewImg.src = '';

        cancelReply();
        clearTyping();
    } catch(err) {
        console.error(err);
        alert('Gagal mengirim pesan.');
    } finally {
        btn.disabled = false;
        btn.style.opacity = '1';
        textInput.focus();
    }
}

function getAvatarUrl(pfp) {
    const code = (pfp && !pfp.startsWith('http')) ? pfp : 'default';
    return `/images/avatar/${code}.png`;
}

function formatTime(timestamp) {
    if (!timestamp) return '';
    const date = new Date(timestamp);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) + ', ' + 
           date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

const userCache = {};
const watchedUsers = {};

async function getUserProfile(uid) {
    if (!uid) {
        return { username: 'User', handle: '@user', pfp: 'default' };
    }

    if (userCache[uid]) return userCache[uid];

    const snapshot = await firebase.database().ref(`users/${uid}`).once('value');
    const user = snapshot.val() || {};

    console.log('Lookup user:', uid, user);

    let handle = user?.handle || '';
    if (!handle) {
        const name = user?.name || user?.username || 'User';
        handle = name.toLowerCase().replace(/\s+/g, '');
    }

    const profile = {
        username: user?.name || user?.username || 'User',
        handle: handle,
        pfp: user?.pfp || 'default'
    };

    userCache[uid] = profile;
    watchUserProfile(uid);

    return profile;
}

function watchUserProfile(uid) {
    if (!uid || watchedUsers[uid]) return;
    watchedUsers[uid] = true;

    firebase.database().ref(`users/${uid}`).on('value', function(snapshot) {
        const user = snapshot.val();
        
        console.log('watch user profile:', uid, user);
        
        if (!user) {
            console.warn('User profile not found:', uid);
            return;
        }
        
        let handle = user.handle || '';
        if (!handle) {
            const name = user.name || user.username || 'User';
            handle = name.toLowerCase().replace(/\s+/g, '');
        }

        const profile = {
            username: user.name || user.username || 'User',
            handle: handle,
            pfp: user.pfp || 'default'
        };
        
        userCache[uid] = profile;

        document.querySelectorAll(`[data-user-uid="${uid}"]`).forEach(function(el) {
            applyUserProfileToElement(el, profile);
        });
    });
}

function applyUserProfileToElement(el, profile) {
    const usernameEl = el.querySelector('.chat-username');
    const handleEl = el.querySelector('.chat-handle');
    const avatarEl = el.querySelector('.chat-avatar');

    if (usernameEl) usernameEl.textContent = profile.username || 'User';
    if (handleEl) {
        // Only prefix with @ if handle doesn't already have one
        const h = profile.handle || 'user';
        handleEl.textContent = h.startsWith('@') ? h : '@' + h;
    }

    if (avatarEl) {
        avatarEl.src = `/images/avatar/${profile.pfp || 'default'}.png`;
        avatarEl.onerror = function() {
            this.src = '/images/avatar/default.png';
        };
    }
}

async function appendMessage(msg) {
    const list = document.getElementById('messageList');
    const emptyMsg = list.querySelector('.empty-message');
    if (emptyMsg) emptyMsg.remove();

    const isOwn = CURRENT_UID && msg.uid === CURRENT_UID;
    
    let replyHtml = '';
    if (msg.replyTo) {
        replyHtml = `
        <div class="reply-preview">
            <i class="fa-solid fa-reply fa-flip-horizontal"></i>
            ${escapeHtml(msg.replyTo)}
        </div>`;
    }

    let reactionsHtml = '';
    if (msg.reactions) {
        Object.entries(msg.reactions).forEach(([emoji, users]) => {
            const count = Object.keys(users).length;
            const reacted = CURRENT_UID && users[CURRENT_UID];
            reactionsHtml += `
                <button class="react-chip ${reacted ? 'reacted' : ''}" 
                        data-emoji="${emoji}"
                        onclick="activeMsg = { id: '${msg.id}', isOwn: ${isOwn ? 'true' : 'false'} }; sendReact('${emoji}')">
                    ${emoji} <span>${count}</span>
                </button>
            `;
        });
    }

    const timeFormatted = msg.createdAtFormatted || formatTime(msg.createdAt);
    const hasText = !!msg.text;
    const hasImage = !!msg.imageUrl;

    const html = `
    <div class="forum-message" id="msg-${msg.id}"
          data-msg-id="${msg.id}"
          data-msg-uid="${msg.uid}"
          data-user-uid="${msg.uid}"
          data-msg-text="${escapeHtml(msg.text)}"
          data-is-own="${isOwn ? '1' : '0'}"
          oncontextmenu="showCtxMenu(event, '${msg.id}', ${isOwn ? 'true' : 'false'}, IS_ADMIN)">

        <div class="selection-check">✓</div>

        <img src="/images/avatar/default.png"
             alt="Avatar"
             class="chat-avatar message-avatar"
             onerror="this.src='/images/avatar/default.png'">

        <div class="message-body">
            <div class="message-meta">
                <strong class="chat-username message-name">Loading...</strong>
                <span class="chat-handle message-username">@...</span>
                <time class="message-time">${timeFormatted}</time>
            </div>
            ${replyHtml}
            ${hasText ? `<p class="message-text" id="text-${msg.id}">${escapeHtml(msg.text)}</p>` : ''}
            ${hasImage ? `
                <button type="button" class="forum-message-image-btn">
                    <img src="${escapeHtml(msg.imageUrl)}"
                         class="forum-message-image"
                         alt="Forum image">
                </button>
            ` : ''}

            <div class="reaction-bar" id="reactions-${msg.id}">
                ${reactionsHtml}
            </div>
        </div>

        <button class="msg-dots-btn"
                title="Opsi"
                onclick="showCtxMenu(event, '${msg.id}', ${isOwn ? 'true' : 'false'}, IS_ADMIN); event.stopPropagation();">
            <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>
    </div>
    `;

    list.insertAdjacentHTML('beforeend', html);
    
    const newEl = document.getElementById(`msg-${msg.id}`);
    if (newEl && msg.uid) {
        watchUserProfile(msg.uid);
        if (userCache[msg.uid]) {
            applyUserProfileToElement(newEl, userCache[msg.uid]);
        }
        attachMessageSelectionEvents(newEl, msg.id, msg.uid);
    }
}

function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}

// ── REALTIME FIREBASE LISTENERS ──
const db = firebase.database();
const messagesRef = db.ref(`forums/${THREAD}/messages`);
const typingRef = db.ref(`forums/${THREAD}/typing`);
let typingTimeout = null;

// 1. Listen for new messages
messagesRef.limitToLast(50).on('child_added', async snapshot => {
    const msg = snapshot.val();
    if (!msg) return;
    const msgId = snapshot.key;

    // Jangan render ulang jika sudah ada (dirender oleh Blade)
    if (document.getElementById(`msg-${msgId}`)) return;

    // pfp is now resolved inside appendMessage via getUserProfile
    await appendMessage({
        id: msgId,
        uid: msg.uid,
        text: msg.text,
        imageUrl: msg.imageUrl,
        imagePublicId: msg.imagePublicId,
        replyTo: msg.replyTo,
        createdAt: msg.createdAt,
        reactions: msg.reactions,
        // fallbacks for old messages:
        displayName: msg.displayName,
        username: msg.username,
        pfp: msg.pfp
    });

    // Tambahkan class animasi agar munculnya smooth
    const newMsgEl = document.getElementById(`msg-${msgId}`);
    if (newMsgEl) newMsgEl.classList.add('message-new');

    // Scroll ke bawah jika user dekat bagian bawah atau ini adalah pesan dari user sendiri
    if (area) {
        const isNearBottom = area.scrollHeight - area.scrollTop <= area.clientHeight + 150;
        if (isNearBottom || msg.uid === CURRENT_UID) {
            area.scrollTop = area.scrollHeight;
        }
    }
});

// 2. Listen for deleted messages
messagesRef.on('child_removed', snapshot => {
    const el = document.getElementById(`msg-${snapshot.key}`);
    if (el) el.remove();
    
    if (typeof selectedMessages !== 'undefined' && selectedMessages.has(snapshot.key)) {
        selectedMessages.delete(snapshot.key);
        updateSelectionToolbar();
    }
});

// 3. Listen for edited messages
messagesRef.on('child_changed', snapshot => {
    const msg = snapshot.val();
    if (!msg) return;
    const msgId = snapshot.key;
    
    const textEl = document.getElementById(`text-${msgId}`);
    if (textEl) textEl.innerText = msg.text;

    // Update reaction bar
    const bar = document.getElementById('reactions-' + msgId);
    if (bar) {
        bar.innerHTML = '';
        if (msg.reactions) {
            Object.entries(msg.reactions).forEach(([emoji, users]) => {
                const count = Object.keys(users).length;
                const reacted = CURRENT_UID && users[CURRENT_UID];
                const isOwn = CURRENT_UID && msg.uid === CURRENT_UID;
                const btn = document.createElement('button');
                btn.className = `react-chip ${reacted ? 'reacted' : ''}`;
                btn.dataset.emoji = emoji;
                btn.onclick = () => {
                    activeMsg = { id: msgId, isOwn: isOwn };
                    sendReact(emoji);
                };
                btn.innerHTML = `${emoji} <span>${count}</span>`;
                bar.appendChild(btn);
            });
        }
    }
});

// 4. Typing Indicator Logic
function clearTyping() {
    if (CURRENT_UID) typingRef.child(CURRENT_UID).remove();
}

const chatTextarea = document.getElementById('chatTextarea');
if (chatTextarea && CURRENT_UID) {
    chatTextarea.addEventListener('input', () => {
        if (!chatTextarea.value.trim()) {
            clearTyping();
            return;
        }
        
        typingRef.child(CURRENT_UID).set({
            username: CURRENT_USERNAME,
            isTyping: true,
            updatedAt: firebase.database.ServerValue.TIMESTAMP
        });
        
        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(clearTyping, 2000);
    });
    
    // Hapus status typing saat user keluar
    typingRef.child(CURRENT_UID).onDisconnect().remove();
}

// Tampilkan indikator mengetik
typingRef.on('value', snapshot => {
    const val = snapshot.val();
    const indicator = document.getElementById('typingIndicator');
    if (!indicator) return;
    
    if (!val) {
        indicator.style.display = 'none';
        indicator.innerText = '';
        return;
    }
    
    const typers = [];
    for (let uid in val) {
        if (uid === CURRENT_UID) continue; // Jangan tampilkan diri sendiri
        if (val[uid].isTyping) {
            typers.push(val[uid].username);
        }
    }
    
    if (typers.length === 0) {
        indicator.style.display = 'none';
        indicator.innerText = '';
    } else if (typers.length === 1) {
        indicator.style.display = 'block';
        indicator.innerText = `${typers[0]} sedang mengetik...`;
    } else if (typers.length === 2) {
        indicator.style.display = 'block';
        indicator.innerText = `${typers[0]} dan ${typers[1]} sedang mengetik...`;
    } else {
        indicator.style.display = 'block';
        indicator.innerText = `${typers[0]} dan ${typers.length - 1} lainnya sedang mengetik...`;
    }
});

// ── Bind Submit Listener and Keydown Listener cleanly ──
const commentForm = document.getElementById('commentForm');
if (commentForm) {
    commentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitComment(e);
    });
}

if (chatTextarea) {
    chatTextarea.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            submitComment(e);
        }
    });
}

// ── MULTI-SELECT MESSAGES ──
let selectionMode = false;
const selectedMessages = new Map();

function canDeleteMessageByUid(uid) {
    return IS_ADMIN || uid === CURRENT_UID;
}

function enterSelectionMode() {
    selectionMode = true;
    document.body.classList.add('selection-mode');
    updateSelectionToolbar();
}

function exitSelectionMode() {
    selectionMode = false;
    selectedMessages.clear();

    document.querySelectorAll('.forum-message.selected').forEach(el => {
        el.classList.remove('selected');
    });

    document.body.classList.remove('selection-mode');
    updateSelectionToolbar();
}

function toggleSelectMessage(messageId, uid, el) {
    if (!canDeleteMessageByUid(uid)) {
        return; // Tidak boleh pilih/hapus pesan orang lain jika bukan admin
    }

    if (!selectionMode) {
        enterSelectionMode();
    }

    if (selectedMessages.has(messageId)) {
        selectedMessages.delete(messageId);
        el.classList.remove('selected');
    } else {
        selectedMessages.set(messageId, { messageId, uid });
        el.classList.add('selected');
    }

    updateSelectionToolbar();
}

function updateSelectionToolbar() {
    const toolbar = document.getElementById('selectionToolbar');
    const countEl = document.getElementById('selectedCount');
    const count = selectedMessages.size;

    if (!toolbar || !countEl) return;

    if (selectionMode && count > 0) {
        toolbar.classList.remove('hidden');
        countEl.textContent = `${count} pesan dipilih`;
    } else {
        toolbar.classList.add('hidden');
        countEl.textContent = '0 pesan dipilih';
    }
}

function attachMessageSelectionEvents(el, messageId, uid) {
    let pressTimer = null;

    const startTimer = () => {
        pressTimer = setTimeout(function() {
            toggleSelectMessage(messageId, uid, el);
        }, 600);
    };
    const clearTimer = () => clearTimeout(pressTimer);

    el.addEventListener('mousedown', function(e) {
        if (e.button !== 0) return;
        startTimer();
    });
    el.addEventListener('mouseup', clearTimer);
    el.addEventListener('mouseleave', clearTimer);
    
    el.addEventListener('touchstart', startTimer, { passive: true });
    el.addEventListener('touchend', clearTimer);

    el.addEventListener('click', function(e) {
        // Prevent click events inside the message from toggling selection if we click on a button or link
        if (e.target.closest('button') || e.target.closest('a')) return;
        
        if (selectionMode) {
            e.preventDefault();
            toggleSelectMessage(messageId, uid, el);
        }
    });
}

function doSelectMessage() {
    if (!activeMsg) return;
    ctxMenu.style.display = 'none';
    toggleSelectMessage(activeMsg.id, activeMsg.el.dataset.msgUid, activeMsg.el);
}

async function deleteSelectedMessages() {
    const count = selectedMessages.size;
    if (count === 0) return;

    if (!confirm(`Hapus ${count} pesan yang dipilih?`)) {
        return;
    }

    const updates = {};
    selectedMessages.forEach(function(item) {
        if (canDeleteMessageByUid(item.uid)) {
            updates[`forums/${THREAD}/messages/${item.messageId}`] = null;
        }
    });

    try {
        await firebase.database().ref().update(updates);
    } catch(err) {
        console.error("Gagal menghapus pesan multi", err);
    }

    exitSelectionMode();
}

document.getElementById('deleteSelectedBtn')?.addEventListener('click', deleteSelectedMessages);
document.getElementById('cancelSelectionBtn')?.addEventListener('click', exitSelectionMode);


// ── Initialize Watchers for Existing Messages ──
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.forum-message').forEach(el => {
        const uid = el.getAttribute('data-user-uid');
        const messageId = el.getAttribute('data-msg-id');
        if (uid) {
            watchUserProfile(uid);
            attachMessageSelectionEvents(el, messageId, uid);
        }
    });

    // ── Bind Image Upload and Preview events ──
    const forumImageBtn = document.getElementById('forumImageBtn');
    const forumImageInput = document.getElementById('forumImageInput');
    const forumImagePreview = document.getElementById('forumImagePreview');
    const forumPreviewImg = document.getElementById('forumPreviewImg');
    const removeForumImageBtn = document.getElementById('removeForumImageBtn');

    if (forumImageBtn && forumImageInput) {
        forumImageBtn.addEventListener('click', function () {
            forumImageInput.click();
        });
    }

    if (forumImageInput) {
        forumImageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            const maxSize = 3 * 1024 * 1024; // Let's enforce 3 MB or 5 MB. 3 MB as default for speed

            if (!allowedTypes.includes(file.type)) {
                alert('Format gambar tidak didukung. Harap pilih JPG, JPEG, PNG, atau WEBP.');
                this.value = '';
                return;
            }

            if (file.size > maxSize) {
                alert('Ukuran gambar maksimal 3 MB.');
                this.value = '';
                return;
            }

            selectedForumImage = file;

            const previewUrl = URL.createObjectURL(file);
            if (forumPreviewImg) forumPreviewImg.src = previewUrl;
            if (forumImagePreview) forumImagePreview.classList.remove('hidden');
        });
    }

    if (removeForumImageBtn) {
        removeForumImageBtn.addEventListener('click', function () {
            selectedForumImage = null;
            if (forumImageInput) forumImageInput.value = '';
            if (forumPreviewImg) forumPreviewImg.src = '';
            if (forumImagePreview) forumImagePreview.classList.add('hidden');
        });
    }

    // Modal click handler for message images
    document.addEventListener('click', function (e) {
        const imageBtn = e.target.closest('.forum-message-image-btn');
        if (imageBtn) {
            const img = imageBtn.querySelector('img');
            if (!img) return;

            const modalImg = document.getElementById('forumImageModalImg');
            const modal = document.getElementById('forumImageModal');
            if (modalImg) modalImg.src = img.src;
            if (modal) modal.classList.remove('hidden');
        }
    });

    const closeBtn = document.getElementById('closeForumImageModal');
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            const modal = document.getElementById('forumImageModal');
            const modalImg = document.getElementById('forumImageModalImg');
            if (modal) modal.classList.add('hidden');
            if (modalImg) modalImg.src = '';
        });
    }
});
</script>
@endpush
@endsection
