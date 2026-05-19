@extends('layout.app')
@section('navbar')
    @include('layout.navbar')
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/forum-discord.css?v=' . time()) }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

@php
    $currentUid = session('user.uid');
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
                         data-msg-text="{{ htmlspecialchars($message['text'], ENT_QUOTES) }}"
                         data-is-own="{{ $isOwn ? '1' : '0' }}"
                         oncontextmenu="showCtxMenu(event, '{{ $message['id'] }}', {{ $isOwn ? 'true' : 'false' }}, {{ ($isAdmin ?? false) ? 'true' : 'false' }})">

                        <img src="{{ $message['avatar'] }}"
                             alt="{{ $message['displayName'] }}"
                             class="message-avatar"
                             onerror="this.src='/images/avatar/pfp6.png'">

                        <div class="message-body">
                            <div class="message-meta">
                                <strong class="message-name">{{ $message['displayName'] }}</strong>
                                <span class="message-username">{{ '@' . $message['username'] }}</span>
                                <time class="message-time">{{ $message['createdAtFormatted'] }}</time>
                            </div>
                            {{-- Reply preview --}}
                            @if(!empty($message['replyTo']))
                                <div class="reply-preview">
                                    <i class="fa-solid fa-reply fa-flip-horizontal"></i>
                                    {{ $message['replyTo'] }}
                                </div>
                            @endif
                            <p class="message-text" id="text-{{ $message['id'] }}">{{ $message['text'] }}</p>

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

        {{-- ── REPLY BAR (hidden by default) ── --}}
        <div class="reply-bar" id="replyBar" style="display:none;">
            <div class="reply-bar-inner">
                <i class="fa-solid fa-reply fa-flip-horizontal"></i>
                <span id="replyBarText">Membalas <strong id="replyBarName"></strong></span>
            </div>
            <button onclick="cancelReply()" class="reply-bar-close"><i class="fa-solid fa-xmark"></i></button>
        </div>

        {{-- ── INPUT BAR ── --}}
        @if(session()->has('user'))
            <form method="POST"
                  action="{{ route('forum.comment', $threadId) }}"
                  class="chat-input-bar"
                  id="commentForm"
                  onsubmit="submitCommentAjax(event)">
                @csrf
                <input type="hidden" name="replyTo" id="replyToInput" value="">
                <textarea name="text"
                          class="chat-textarea"
                          id="chatTextarea"
                          placeholder="Kirim pesan ke #{{ Str::slug($thread['title']) }}"
                          rows="1"
                          maxlength="1000"
                          required
                          onkeydown="if(event.key==='Enter'&&!event.shiftKey){document.getElementById('commentForm').dispatchEvent(new Event('submit', {cancelable:true, bubbles:true}));event.preventDefault();}"></textarea>
                <button type="submit" class="chat-send-btn" id="sendBtn" title="Kirim">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
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

@push('scripts')
<script>
const CSRF   = document.querySelector('meta[name="csrf-token"]').content;
const THREAD = '{{ $threadId }}';
const IS_ADMIN = {{ ($isAdmin ?? false) ? 'true' : 'false' }};

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
        const res = await fetch(`/forum/${THREAD}/comments/${activeMsg.id}/react`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ emoji })
        });
        const data = await res.json();
        updateReactionBar(activeMsg.id, emoji, data.count, data.reacted);
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
            activeMsg = { id: msgId, isOwn: document.getElementById('msg-'+msgId)?.dataset.isOwn === '1' };
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
        const res = await fetch(`/forum/${THREAD}/comments/${activeMsg.id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ text })
        });
        const data = await res.json();
        if (data.success) {
            const p = document.getElementById('text-' + activeMsg.id);
            if (p) p.innerText = data.text;
            closeEditModal();
        } else { alert('Gagal edit.'); }
    } catch(err) { console.error(err); }
}

// ── Delete ──
async function doDelete() {
    if (!activeMsg) return;
    ctxMenu.style.display = 'none';
    if (!confirm('Hapus pesan ini?')) return;
    try {
        const res = await fetch(`/forum/${THREAD}/comments/${activeMsg.id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('msg-' + activeMsg.id)?.remove();
        } else { alert('Gagal menghapus.'); }
    } catch(err) { console.error(err); }
}

// ── Delete (Admin) ──
async function doAdminDelete() {
    if (!activeMsg) return;
    ctxMenu.style.display = 'none';
    if (!confirm('Hapus pesan ini sebagai admin?')) return;
    try {
        const res = await fetch(`/admin/forum/${THREAD}/comments/${activeMsg.id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('msg-' + activeMsg.id)?.remove();
        } else { alert(data.error || 'Gagal menghapus.'); }
    } catch(err) { console.error(err); }
}

// ── AJAX Submit Comment ──
async function submitCommentAjax(e) {
    e.preventDefault();
    const form = e.target;
    const textInput = document.getElementById('chatTextarea');
    const replyInput = document.getElementById('replyToInput');
    const btn = document.getElementById('sendBtn');
    
    if (!textInput.value.trim()) return;

    btn.disabled = true;
    btn.style.opacity = '0.5';

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                text: textInput.value,
                replyTo: replyInput.value
            })
        });

        const data = await res.json();
        
        if (data.success) {
            textInput.value = '';
            textInput.style.height = '42px'; // Reset height
            cancelReply();
            
            // Render new message dynamically
            appendMessage(data.message);
            
            // Scroll to bottom
            if (area) area.scrollTop = area.scrollHeight;
        } else {
            alert(data.error || 'Gagal mengirim pesan');
        }
    } catch(err) {
        console.error(err);
        alert('Terjadi kesalahan jaringan.');
    } finally {
        btn.disabled = false;
        btn.style.opacity = '1';
        textInput.focus();
    }
}

function appendMessage(msg) {
    const list = document.getElementById('messageList');
    const emptyMsg = list.querySelector('.empty-message');
    if (emptyMsg) emptyMsg.remove();

    const isOwn = true;
    
    let replyHtml = '';
    if (msg.replyTo) {
        replyHtml = `
        <div class="reply-preview">
            <i class="fa-solid fa-reply fa-flip-horizontal"></i>
            ${escapeHtml(msg.replyTo)}
        </div>`;
    }

    const html = `
    <div class="forum-message" id="msg-${msg.id}"
         data-msg-id="${msg.id}"
         data-msg-uid="${msg.uid}"
         data-msg-text="${escapeHtml(msg.text)}"
         data-is-own="1"
         oncontextmenu="showCtxMenu(event, '${msg.id}', true, IS_ADMIN)">

        <img src="${msg.avatar}"
             alt="${escapeHtml(msg.displayName)}"
             class="message-avatar"
             onerror="this.src='/images/avatar/pfp6.png'">

        <div class="message-body">
            <div class="message-meta">
                <strong class="message-name">${escapeHtml(msg.displayName)}</strong>
                <span class="message-username">@${escapeHtml(msg.username)}</span>
                <time class="message-time">${msg.createdAtFormatted}</time>
            </div>
            ${replyHtml}
            <p class="message-text" id="text-${msg.id}">${escapeHtml(msg.text)}</p>

            <div class="reaction-bar" id="reactions-${msg.id}"></div>
        </div>

        <button class="msg-dots-btn"
                title="Opsi"
                onclick="showCtxMenu(event, '${msg.id}', true, IS_ADMIN); event.stopPropagation();">
            <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>
    </div>
    `;

    list.insertAdjacentHTML('beforeend', html);
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
</script>
@endpush
@endsection
