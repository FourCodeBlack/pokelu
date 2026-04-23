@extends('layout.app')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Freckle+Face&family=Fragment+Mono:ital@0;1&display=swap" rel="stylesheet"/>
<style>
/* ── RESET & ROOT ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --pink:        #F533CB;
  --pink-glow:   rgba(245, 51, 203, 0.5);
  --dark-msg:    #0D0820;
  --sidebar-bg:  rgba(18, 10, 40, 0.92);
  --sidebar-w:   280px;
  --input-bg:    rgba(30, 14, 60, 0.85);
  --border:      rgba(245, 51, 203, 0.25);
  --text:        #f0e6ff;
  --muted:       rgba(200, 170, 240, 0.55);
  --radius:      14px;
}

body {
  font-family: 'Fragment Mono', monospace;
  background: var(--dark-msg);
  color: var(--text);
  height: 100vh;
  overflow: hidden;
  display: flex;
  align-items: stretch;
}

body::after {
  content: '';
  position: fixed; inset: 0;
  background: repeating-linear-gradient(
    to bottom,
    transparent 0px, transparent 3px,
    rgba(0,0,0,0.06) 3px, rgba(0,0,0,0.06) 4px
  );
  pointer-events: none;
  z-index: 999;
  animation: scanMove 10s linear infinite;
}
@keyframes scanMove {
  from { background-position: 0 0; }
  to   { background-position: 0 80px; }
}

/* ── SIDEBAR ── */
.sidebar {
  width: var(--sidebar-w);
  flex-shrink: 0;
  background: var(--sidebar-bg);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  position: relative;
  z-index: 2;
  backdrop-filter: blur(16px);
  animation: slideInLeft .45s cubic-bezier(0.22,1,0.36,1) both;
}
@keyframes slideInLeft {
  from { transform: translateX(-30px); opacity: 0; }
  to   { transform: translateX(0);     opacity: 1; }
}
.sidebar::after {
  content: '';
  position: absolute;
  top: 0; right: -1px;
  width: 1px; height: 100%;
  background: linear-gradient(180deg, transparent, var(--pink) 30%, rgba(245,51,203,0.3) 70%, transparent);
  animation: sideGlow 3s ease-in-out infinite;
}
@keyframes sideGlow {
  0%,100% { opacity: 0.4; }
  50%      { opacity: 1; }
}

.sidebar-header {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 18px 20px;
  border-bottom: 1px solid var(--border);
}

.btn-back {
  width: 36px; height: 36px;
  border: 1.5px solid var(--border);
  border-radius: 8px;
  background: rgba(245,51,203,0.08);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  text-decoration: none;
  transition: border-color .2s, box-shadow .2s, transform .15s;
  flex-shrink: 0;
}
.btn-back img {
  width: 18px; height: 18px;
  object-fit: contain;
  filter: brightness(0) invert(1) sepia(1) saturate(4) hue-rotate(270deg);
  transition: transform .2s;
}
.btn-back:hover { border-color: var(--pink); box-shadow: 0 0 12px var(--pink-glow); transform: translateY(-1px); }
.btn-back:hover img { transform: translateX(-2px); }

.sidebar-title {
  font-family: 'Freckle Face', cursive;
  font-size: 1.3rem;
  color: #fff;
  letter-spacing: .06em;
  text-shadow: 0 0 10px var(--pink-glow);
}

/* Contact list */
.contact-list {
  flex: 1;
  overflow-y: auto;
  padding: 8px 0;
  scrollbar-width: thin;
  scrollbar-color: var(--pink) transparent;
}
.contact-list::-webkit-scrollbar { width: 3px; }
.contact-list::-webkit-scrollbar-thumb { background: var(--pink); border-radius: 2px; }

.contact-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 20px;
  cursor: pointer;
  position: relative;
  transition: background .18s;
  animation: fadeInUp .35s ease both;
}
.contact-item::before {
  content: '';
  position: absolute;
  left: 0; top: 0; bottom: 0;
  width: 3px;
  background: var(--pink);
  border-radius: 0 2px 2px 0;
  opacity: 0;
  transition: opacity .2s;
}
.contact-item:hover, .contact-item.active { background: rgba(245, 51, 203, 0.1); }
.contact-item:hover::before, .contact-item.active::before { opacity: 1; }

.contact-avatar {
  width: 46px; height: 46px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3a1a5c, #5a2a80);
  border: 2px solid rgba(245,51,203,0.3);
  flex-shrink: 0;
  overflow: hidden;
  transition: border-color .2s, box-shadow .2s;
  display: flex; align-items: center; justify-content: center;
}
.contact-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
.contact-item:hover .contact-avatar,
.contact-item.active .contact-avatar { border-color: var(--pink); box-shadow: 0 0 10px var(--pink-glow); }

.contact-info { flex: 1; min-width: 0; }
.contact-name {
  font-family: 'Freckle Face', cursive;
  font-size: .88rem;
  color: var(--text);
  letter-spacing: .04em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.contact-last-msg {
  font-size: .72rem;
  color: var(--muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-top: 2px;
}
.contact-item:hover .contact-name, .contact-item.active .contact-name { color: #fff; text-shadow: 0 0 8px var(--pink-glow); }

/* Empty sidebar state */
.sidebar-empty {
  padding: 32px 20px;
  text-align: center;
  color: var(--muted);
  font-size: .8rem;
  line-height: 1.6;
}

/* ── CHAT AREA ── */
.chat-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
}

.chat-bg {
  position: absolute; inset: 0; z-index: 0;
  background: url('{{ asset("images/background_chat.webp") }}') center / cover no-repeat;
}
.chat-bg::after {
  content: '';
  position: absolute; inset: 0;
  background: rgba(13, 8, 32, 0.55);
}

/* Chat header */
.chat-header {
  position: relative; z-index: 2;
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 24px;
  background: rgba(13, 8, 32, 0.75);
  border-bottom: 1px solid var(--border);
  backdrop-filter: blur(12px);
}
.chat-header-avatar {
  width: 42px; height: 42px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3a1a5c, #5a2a80);
  border: 2px solid rgba(245,51,203,0.4);
  flex-shrink: 0;
  overflow: hidden;
  display: flex; align-items: center; justify-content: center;
  animation: avatarPulse 3s ease-in-out infinite;
}
.chat-header-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
@keyframes avatarPulse {
  0%,100% { box-shadow: 0 0 6px rgba(245,51,203,0.4); }
  50%      { box-shadow: 0 0 16px rgba(245,51,203,0.8); }
}

.chat-header-name {
  font-family: 'Freckle Face', cursive;
  font-size: 1rem;
  color: #fff;
  letter-spacing: .05em;
  text-shadow: 0 0 10px var(--pink-glow);
}
.chat-header-status {
  font-size: .72rem;
  color: var(--muted);
  letter-spacing: .08em;
  margin-top: 2px;
  display: flex; align-items: center; gap: 5px;
}
.status-dot {
  width: 6px; height: 6px; border-radius: 50%;
  background: #4ade80;
  box-shadow: 0 0 6px rgba(74,222,128,0.7);
  animation: blink 2s ease-in-out infinite;
}
@keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: .3; } }

/* ── MESSAGES AREA ── */
.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  position: relative; z-index: 1;
  scrollbar-width: thin;
  scrollbar-color: var(--pink) transparent;
}
.chat-messages::-webkit-scrollbar { width: 3px; }
.chat-messages::-webkit-scrollbar-thumb { background: var(--pink); border-radius: 2px; }

/* Date separator */
.date-sep {
  display: flex; align-items: center; gap: 10px;
  margin: 8px 0;
}
.date-sep-line {
  flex: 1; height: 1px;
  background: linear-gradient(90deg, transparent, var(--border), transparent);
}
.date-sep-text {
  font-size: .68rem;
  color: var(--muted);
  letter-spacing: .1em;
  white-space: nowrap;
}

/* Message row — HORIZONTAL layout */
.msg-row {
  display: flex;
  align-items: flex-end;
  gap: 8px;
  animation: msgIn .22s ease both;
  max-width: 100%;
}
@keyframes msgIn {
  from { opacity: 0; transform: translateY(6px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* Self = kanan, Other = kiri */
.msg-row.self  { flex-direction: row-reverse; }
.msg-row.other { flex-direction: row; }

.msg-avatar {
  width: 32px; height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3a1a5c, #5a2a80);
  border: 1.5px solid rgba(245,51,203,0.3);
  flex-shrink: 0;
  overflow: hidden;
  display: flex; align-items: center; justify-content: center;
  align-self: flex-end; /* avatar align ke bawah bubble */
}
.msg-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }

/* Wrapper bubble + waktu */
.msg-content {
  display: flex;
  flex-direction: column;
  max-width: 62%;
  gap: 3px;
}
.msg-row.self  .msg-content { align-items: flex-end; }
.msg-row.other .msg-content { align-items: flex-start; }

.msg-bubble {
  padding: 9px 14px;
  border-radius: var(--radius);
  font-family: 'Fragment Mono', monospace;
  font-size: .82rem;
  line-height: 1.55;
  letter-spacing: .02em;
  word-break: break-word;
  white-space: pre-wrap;
}

/* Bubble SELF (kanan) */
.msg-row.self .msg-bubble {
  background: var(--pink);
  color: #fff;
  border-bottom-right-radius: 4px;
  box-shadow: 0 3px 14px rgba(245,51,203,0.35);
}

/* Bubble OTHER (kiri) */
.msg-row.other .msg-bubble {
  background: rgba(13, 8, 32, 0.88);
  color: var(--text);
  border-bottom-left-radius: 4px;
  border: 1px solid rgba(245,51,203,0.18);
  box-shadow: 0 3px 12px rgba(0,0,0,0.4);
}

.msg-name {
  font-size: .68rem;
  color: var(--muted);
  letter-spacing: .05em;
  padding: 0 2px;
}

.msg-time {
  font-size: .65rem;
  color: var(--muted);
  letter-spacing: .05em;
  padding: 0 2px;
}

/* ── INPUT AREA ── */
.chat-input-wrap {
  position: relative; z-index: 2;
  display: flex;
  align-items: flex-end;   /* align ke bawah agar textarea naik ke atas */
  gap: 10px;
  padding: 14px 20px;
  background: rgba(13, 8, 32, 0.8);
  border-top: 1px solid var(--border);
  backdrop-filter: blur(12px);
}
.chat-input-wrap::before {
  content: '';
  position: absolute;
  top: -1px; left: 0; right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--pink) 30%, rgba(245,51,203,0.3) 70%, transparent);
  background-size: 200% 100%;
  animation: inputBorder 3s linear infinite;
}
@keyframes inputBorder {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.btn-upload {
  width: 42px; height: 42px;
  border: 1.5px solid var(--border);
  border-radius: 10px;
  background: rgba(245,51,203,0.08);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
  transition: border-color .2s, box-shadow .2s, transform .15s;
}
.btn-upload img {
  width: 20px; height: 20px;
  object-fit: contain;
  filter: brightness(0) invert(1) sepia(1) saturate(4) hue-rotate(270deg);
  opacity: .8;
  transition: opacity .2s, transform .2s;
}
.btn-upload:hover { border-color: var(--pink); box-shadow: 0 0 12px var(--pink-glow); transform: translateY(-1px); }
.btn-upload:hover img { opacity: 1; transform: scale(1.1); }

#fileInput { display: none; }

/* TEXTAREA yang bisa expand — BUKAN input text biasa */
.chat-input {
  flex: 1;
  background: var(--input-bg);
  border: 1.5px solid var(--border);
  border-radius: 10px;
  padding: 11px 16px;
  color: var(--text);
  font-family: 'Fragment Mono', monospace;
  font-size: .85rem;
  outline: none;
  transition: border-color .2s, box-shadow .2s;
  backdrop-filter: blur(4px);
  resize: none;          /* hilangkan handle resize bawaan */
  min-height: 44px;
  max-height: 160px;     /* max ~6 baris */
  overflow-y: auto;
  line-height: 1.5;
}
.chat-input::placeholder { color: var(--muted); }
.chat-input:focus {
  border-color: var(--pink);
  box-shadow: 0 0 0 3px rgba(245,51,203,0.15), 0 0 14px rgba(245,51,203,0.2);
}

.btn-send {
  width: 42px; height: 42px;
  border: none;
  border-radius: 10px;
  background: var(--pink);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
  transition: transform .15s, box-shadow .2s;
  box-shadow: 0 4px 14px rgba(245,51,203,0.45);
  position: relative;
  overflow: hidden;
}
.btn-send img {
  width: 20px; height: 20px;
  object-fit: contain;
  filter: brightness(0) invert(1);
  position: relative; z-index: 1;
  transition: transform .2s;
}
.btn-send::before {
  content: '';
  position: absolute; inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.2), transparent);
  opacity: 0;
  transition: opacity .2s;
}
.btn-send:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(245,51,203,0.65); }
.btn-send:hover::before { opacity: 1; }
.btn-send:hover img { transform: translateX(2px) translateY(-1px); }
.btn-send:active { transform: translateY(0); }

/* HUD corners */
.hud-corner {
  position: fixed; z-index: 998;
  width: 40px; height: 40px;
  pointer-events: none;
  opacity: .5;
  animation: hudPulse 3s ease-in-out infinite;
}
.hud-corner--tl { top: 12px; left: 12px; border-top: 1.5px solid var(--pink); border-left: 1.5px solid var(--pink); }
.hud-corner--tr { top: 12px; right: 12px; border-top: 1.5px solid var(--pink); border-right: 1.5px solid var(--pink); }
.hud-corner--bl { bottom: 12px; left: 12px; border-bottom: 1.5px solid var(--pink); border-left: 1.5px solid var(--pink); }
.hud-corner--br { bottom: 12px; right: 12px; border-bottom: 1.5px solid var(--pink); border-right: 1.5px solid var(--pink); }
@keyframes hudPulse { 0%,100% { opacity: .35; } 50% { opacity: .75; } }

/* Empty chat state */
.chat-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  color: var(--muted);
  position: relative; z-index: 1;
}
.chat-empty-icon { font-size: 2.5rem; opacity: .4; }
.chat-empty-text { font-size: .85rem; letter-spacing: .06em; }
</style>
@endpush

@section('content')

<div class="hud-corner hud-corner--tl"></div>
<div class="hud-corner hud-corner--tr"></div>
<div class="hud-corner hud-corner--bl"></div>
<div class="hud-corner hud-corner--br"></div>

{{-- ══ SIDEBAR ══ --}}
<aside class="sidebar">
  <div class="sidebar-header">
    <a href="{{ route('jelajah') }}" class="btn-back" title="Kembali">
      <img src="{{ asset('images/icon_back.png') }}" alt="Back"/>
    </a>
    <span class="sidebar-title">CHAT</span>
  </div>
  <div class="contact-list" id="contactList">
    <div class="sidebar-empty" id="sidebarEmpty">Belum ada percakapan</div>
  </div>
</aside>

{{-- ══ CHAT AREA ══ --}}
<div class="chat-area">
  <div class="chat-bg"></div>

  {{-- Header --}}
  <div class="chat-header" id="chatHeader" style="display:none">
    <div class="chat-header-avatar" id="headerAvatar"></div>
    <div>
      <div class="chat-header-name" id="headerName">—</div>
      <div class="chat-header-status">
        <span class="status-dot"></span>
        <span>ONLINE</span>
      </div>
    </div>
  </div>

  {{-- Empty state saat belum pilih kontak --}}
  <div class="chat-empty" id="chatEmptyState">
    <div class="chat-empty-icon">💬</div>
    <div class="chat-empty-text">Pilih percakapan atau hubungi penjual dari halaman kartu</div>
  </div>

  {{-- Messages --}}
  <div class="chat-messages" id="chatMessages" style="display:none"></div>

  {{-- Input --}}
  <div class="chat-input-wrap" id="chatInputWrap" style="display:none">
    <input type="file" id="fileInput" accept="image/*,application/pdf"/>
    <button class="btn-upload" onclick="document.getElementById('fileInput').click()" title="Upload file">
      <img src="{{ asset('images/icon_upload.png') }}" alt="Upload"/>
    </button>

    {{-- TEXTAREA bukan input text — bisa Enter baru baris, Shift+Enter kirim --}}
    <textarea
      class="chat-input"
      id="chatInput"
      placeholder="Ketik pesan… (Enter = baris baru, Shift+Enter = kirim)"
      rows="1"
    ></textarea>

    <button class="btn-send" onclick="sendMessage()" title="Kirim">
      <img src="{{ asset('images/icon_send.png') }}" alt="Send"/>
    </button>
  </div>
</div>

{{-- Firebase SDK --}}
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-database-compat.js"></script>

<script>
// ── Firebase init ──
if (!firebase.apps.length) {
  firebase.initializeApp({
    apiKey: "AIzaSyDr7r6PrzHWUX6i_5bMnL9I7eZMcx2AS_s",
    authDomain: "pokelu-project.firebaseapp.com",
    databaseURL: "https://pokelu-project-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId: "pokelu-project",
    storageBucket: "pokelu-project.firebasestorage.app",
    messagingSenderId: "210207641471",
    appId: "1:210207641471:web:aff0df7b7b3acb0ce2d44a"
  });
}

const auth   = firebase.auth();
const db     = firebase.database();
const FB_TS  = firebase.database.ServerValue.TIMESTAMP;

// ── State ──
let currentUser   = null;
let activeRoomId  = null;
let activeMsgOff  = null;   // Firebase listener off-function
let knownRooms    = {};     // roomId → {name, avatar, lastMsg, lastTs}

// ── URL params (dari btn-contact) ──
const params     = new URLSearchParams(window.location.search);
const initRoom   = params.get('room');
const initSeller = params.get('sellerId');
const initName   = decodeURIComponent(params.get('sellerName') || '');
const initCard   = params.get('cardId');

// ── Auth listener ──
auth.onAuthStateChanged(user => {
  currentUser = user;
  if (!user) {
    window.location.href = '{{ route("login") }}?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
    return;
  }

  loadMyRooms();

  // Jika datang dari btn-contact, langsung buka room tersebut
  if (initRoom && initSeller) {
    setTimeout(() => openRoom(initRoom, initName, null), 800);
  }
});

// ── Muat semua room yang pernah diikuti user ──
function loadMyRooms() {
  db.ref(`user_rooms/${currentUser.uid}`).on('value', snapshot => {
    const data = snapshot.val() || {};
    knownRooms = data;
    renderSidebar();

    // Jika ada initRoom dari URL, pastikan sudah terdaftar
    if (initRoom && initSeller && !data[initRoom]) {
      db.ref(`user_rooms/${currentUser.uid}/${initRoom}`).set({
        name: initName,
        avatar: null,
        lastMsg: '',
        lastTs: Date.now(),
        partnerId: initSeller,
      });
    }
  });
}

// ── Render daftar kontak sidebar ──
function renderSidebar() {
  const list = document.getElementById('contactList');
  const empty = document.getElementById('sidebarEmpty');

  const rooms = Object.entries(knownRooms).sort((a,b) => (b[1].lastTs||0) - (a[1].lastTs||0));

  if (rooms.length === 0) {
    empty.style.display = 'block';
    // Hanya tampilkan entry inisiasi dari URL jika ada
    if (initRoom && initSeller) {
      empty.style.display = 'none';
      renderContactItem(list, initRoom, initName, null, '');
    }
    return;
  }
  empty.style.display = 'none';
  list.innerHTML = '';

// SESUDAH (nama berbeda)
rooms.forEach(([rId, info]) => {
    renderContactItem(list, rId, info.name || 'Anonymous', info.avatar, info.lastMsg || '');
});
}

function renderContactItem(container, roomId, name, avatar, lastMsg) {
  const existing = container.querySelector(`[data-room="${roomId}"]`);
  if (existing) {
    existing.querySelector('.contact-last-msg').textContent = lastMsg;
    return;
  }

  const el = document.createElement('div');
  el.className = 'contact-item' + (roomId === activeRoomId ? ' active' : '');
  el.dataset.room = roomId;
  el.onclick = () => openRoom(roomId, name, avatar);

  const avatarUrl = avatar || `https://api.dicebear.com/7.x/bottts/svg?seed=${roomId}`;
  el.innerHTML = `
    <div class="contact-avatar">
      <img src="${avatarUrl}" alt="${escHtml(name)}" onerror="this.src='https://api.dicebear.com/7.x/bottts/svg?seed=${roomId}'">
    </div>
    <div class="contact-info">
      <div class="contact-name">${escHtml(name.toUpperCase())}</div>
      <div class="contact-last-msg">${escHtml(lastMsg)}</div>
    </div>
  `;
  container.appendChild(el);
}

// ── Buka room chat ──
function openRoom(roomId, name, avatar) {
  // Update active state sidebar
  document.querySelectorAll('.contact-item').forEach(i => i.classList.remove('active'));
  const contactEl = document.querySelector(`[data-room="${roomId}"]`);
  if (contactEl) contactEl.classList.add('active');

  activeRoomId = roomId;

  // Tampilkan UI chat
  document.getElementById('chatEmptyState').style.display = 'none';
  document.getElementById('chatHeader').style.display     = 'flex';
  document.getElementById('chatMessages').style.display   = 'flex';
  document.getElementById('chatInputWrap').style.display  = 'flex';

  // Update header
  const avatarUrl = avatar || `https://api.dicebear.com/7.x/bottts/svg?seed=${roomId}`;
  document.getElementById('headerName').textContent = name.toUpperCase();
  document.getElementById('headerAvatar').innerHTML =
    `<img src="${avatarUrl}" alt="${escHtml(name)}" style="width:100%;height:100%;object-fit:cover;border-radius:50%">`;

  // Focus textarea
  document.getElementById('chatInput').focus();

  // Unsubscribe listener lama
  if (activeMsgOff) { activeMsgOff(); activeMsgOff = null; }

  // Clear messages
  const wrap = document.getElementById('chatMessages');
  wrap.innerHTML = '';

  // Subscribe pesan baru
  const msgsRef = db.ref(`chats/${roomId}/messages`).orderByChild('createdAt').limitToLast(100);
  const handler = msgsRef.on('value', snapshot => {
    wrap.innerHTML = '';
    let lastDate = null;
    snapshot.forEach(child => {
      const m = { id: child.key, ...child.val() };
      const msgDate = m.createdAt ? new Date(m.createdAt).toLocaleDateString('id-ID', {day:'numeric',month:'long',year:'numeric'}) : null;
      if (msgDate && msgDate !== lastDate) {
        appendDateSep(wrap, msgDate);
        lastDate = msgDate;
      }
      appendMessage(wrap, m);
    });
    wrap.scrollTop = wrap.scrollHeight;
  });
  activeMsgOff = () => msgsRef.off('value', handler);
}

// ── Kirim pesan ──
async function sendMessage() {
  if (!currentUser || !activeRoomId) return;
  const input = document.getElementById('chatInput');
  const text  = input.value.trim();
  if (!text) return;

  input.value = '';
  autoResizeTextarea(input);

  try {
    await db.ref(`chats/${activeRoomId}/messages`).push({
      uid:         currentUser.uid,
      displayName: currentUser.displayName || currentUser.email || 'Anonymous',
      photoURL:    currentUser.photoURL || null,
      text:        text,
      createdAt:   FB_TS,
    });

    // Update lastMsg di user_rooms untuk kedua user
    const roomInfo = knownRooms[activeRoomId] || {};
    const partnerId = roomInfo.partnerId;

    const updatePayload = { lastMsg: text, lastTs: Date.now() };
    db.ref(`user_rooms/${currentUser.uid}/${activeRoomId}`).update(updatePayload);
    if (partnerId) {
      db.ref(`user_rooms/${partnerId}/${activeRoomId}`).update({
        ...updatePayload,
        name: currentUser.displayName || currentUser.email,
        avatar: currentUser.photoURL || null,
        partnerId: currentUser.uid,
      });
    }
  } catch(e) { console.error(e); }
}

// ── Render bubble (horizontal) ──
function appendMessage(container, msg) {
  const isSelf = msg.uid === currentUser?.uid;
  const avatarUrl = msg.photoURL || `https://api.dicebear.com/7.x/bottts/svg?seed=${msg.uid}`;
  const timeStr = msg.createdAt
    ? new Date(msg.createdAt).toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'})
    : '';

  const row = document.createElement('div');
  row.className = `msg-row ${isSelf ? 'self' : 'other'}`;
  row.innerHTML = `
    <div class="msg-avatar">
      <img src="${avatarUrl}" alt="" onerror="this.src='https://api.dicebear.com/7.x/bottts/svg?seed=${msg.uid}'">
    </div>
    <div class="msg-content">
      ${!isSelf ? `<div class="msg-name">${escHtml(msg.displayName || 'Anonymous')}</div>` : ''}
      <div class="msg-bubble">${escHtml(msg.text)}</div>
      <div class="msg-time">${timeStr}</div>
    </div>
  `;
  container.appendChild(row);
}

function appendDateSep(container, dateStr) {
  const el = document.createElement('div');
  el.className = 'date-sep';
  el.innerHTML = `
    <div class="date-sep-line"></div>
    <div class="date-sep-text">${escHtml(dateStr)}</div>
    <div class="date-sep-line"></div>
  `;
  container.appendChild(el);
}

// ── Textarea auto-resize ──
function autoResizeTextarea(el) {
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 160) + 'px';
}

const chatInput = document.getElementById('chatInput');

chatInput.addEventListener('input', () => autoResizeTextarea(chatInput));

// Enter = kirim, Shift+Enter = baris baru
chatInput.addEventListener('keydown', e => {
  if (e.key === 'Enter' && e.shiftKey) {
    sendMessage();
    e.preventDefault();
  }
  // Enter biasa = baris baru (default textarea behavior, tidak perlu handle)
});

// File upload
document.getElementById('fileInput').addEventListener('change', function() {
  if (!this.files.length) return;
  document.getElementById('chatInput').value += (document.getElementById('chatInput').value ? '\n' : '') + `📎 ${this.files[0].name}`;
  autoResizeTextarea(document.getElementById('chatInput'));
  this.value = '';
});

function escHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>

@endsection