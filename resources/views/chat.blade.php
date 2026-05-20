@extends('layout.app')

@push('styles')
  <link href="https://fonts.googleapis.com/css2?family=Freckle+Face&family=Fragment+Mono:ital@0;1&display=swap"
    rel="stylesheet" />
    @vite('resources/css/chat.css')
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
        <img src="{{ asset('images/icon_back.png') }}" alt="Back" />
      </a>
      <span class="sidebar-title">CHAT</span>
    </div>

    {{-- Search --}}
    <div class="sidebar-search">
      <input type="text" id="userSearchInput" class="search-input" placeholder="Cari user..." autocomplete="off" />
      <div class="search-result-count" id="searchResultCount">Memuat...</div>
      <div class="search-hint">Ketik nama atau UID</div>
    </div>

    {{-- Contact / Search / Recommendations List --}}
    <div class="contact-list" id="contactList">
      {{-- FIX: Loading state awal supaya user tahu sedang memuat --}}
      <div class="sidebar-empty" id="sidebarEmpty" style="color:var(--muted);font-size:0.75rem;text-align:center;padding:20px;">
        Memuat...
      </div>
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
          <span class="status-dot" id="headerStatusDot"></span>
          <span id="headerStatusText">ONLINE</span>
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

    {{-- Typing Indicator --}}
    <div id="typingIndicator" class="typing-indicator" style="display:none;"></div>

    {{-- Offer Compose Card --}}
    <div class="offer-compose-card" id="offerComposeCard" style="display:none;">
      <div class="offer-compose-header">
        <h3>Kirim Offer</h3>
        <span>Ajukan penawaran kartu</span>
      </div>

      <div class="offer-compose-grid">
        <input id="offerPriceInput" type="number" min="0" placeholder="Harga offer">
        <input id="offerConditionInput" type="text" placeholder="Kondisi kartu">
      </div>

      <textarea id="offerNoteInput" placeholder="Catatan opsional"></textarea>

      <button id="sendOfferBtn" type="button">
        Kirim Offer
      </button>
    </div>

    {{-- Input --}}
    <div class="chat-input-wrap" id="chatInputWrap" style="display:none">
      <!-- Upload Progress -->
      <div class="upload-progress" id="uploadProgress">
        <div class="upload-progress-bar">
          <div class="upload-progress-fill" id="uploadProgressFill"></div>
        </div>
        <div class="upload-progress-text" id="uploadProgressText">Mengunggah…</div>
      </div>

      <!-- Offer Selector Popup -->
      <div class="offer-selector" id="offerSelector">
        <div class="offer-selector-header">
          <span class="offer-selector-title">PILIH OFFER</span>
          <button type="button" class="offer-selector-close" onclick="toggleOfferSelector()">TUTUP</button>
        </div>
        <div class="offer-selector-content" id="offerSelectorContent">
          <div class="offer-selector-empty">Memuat penawaran...</div>
        </div>
      </div>

      <!-- Discord-style Input Container -->
      <div class="chat-input-container">
        <!-- Reply Preview -->
        <div class="reply-preview-wrapper" id="replyPreview">
          <div class="reply-preview-content">
            <div class="reply-preview-text">
              <div class="reply-preview-label">REPLYING TO</div>
              <div class="reply-preview-message" id="replyPreviewText"></div>
            </div>
            <button type="button" class="reply-preview-close" onclick="clearReplyPreview()">×</button>
          </div>
        </div>

        <!-- Offer Attachment Preview -->
        <div class="offer-preview-wrapper" id="offerAttachmentPreview">
          <div class="offer-preview-content">
            <div class="offer-preview-text">
              <div class="offer-preview-label">ATTACHED OFFER</div>
              <div class="offer-preview-message" id="attachedOfferSummary"></div>
            </div>
            <button type="button" class="offer-preview-close" onclick="clearAttachedOffer()">×</button>
          </div>
        </div>

        <!-- File Preview -->
        <div class="file-preview-wrapper" id="filePreview">
          <div class="file-preview-content">
            <div class="file-preview-image" id="filePreviewImageWrap" style="display:none;">
              <img id="filePreviewImage" src="" alt="preview">
            </div>
            <div class="file-preview-info">
              <div class="file-preview-name" id="filePreviewName"></div>
              <div class="file-preview-size" id="filePreviewSize"></div>
            </div>
            <button type="button" class="file-preview-close" onclick="clearFilePreview()">×</button>
          </div>
        </div>

        <!-- Chat Controls -->
        <div class="chat-controls">
          <input type="file" id="fileInput" accept="image/*,application/pdf" />

          <button class="btn-attach" onclick="document.getElementById('fileInput').click()" title="Upload file">
            <img src="{{ asset('images/icon_upload.png') }}" alt="Upload" />
          </button>

          <button class="btn-attach-offer" id="attachOfferBtn" onclick="toggleOfferSelector()"
            title="Attach offer"></button>

          <textarea class="chat-input" id="chatInput" placeholder="Ketik pesan…" rows="1"></textarea>

          <button class="btn-send" id="btnSend" onclick="sendMessage()" title="Kirim">
            <img src="{{ asset('images/icon_send.png') }}" alt="Send" />
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- ══ NEGO MODAL ══ --}}
  <div class="nego-modal-overlay" id="negoModal" onclick="if(event.target===this)closeNegoModal()">
    <div class="nego-modal">
      <div class="nego-modal-header">
        <span class="nego-modal-title">💬 COUNTER OFFER</span>
        <button class="nego-modal-close" onclick="closeNegoModal()">×</button>
      </div>

      <div class="nego-modal-body">
        <div class="nego-section-label">PENAWARAN ASLI</div>
        <div id="negoOriginalOffer"></div>

        <div id="negoHistorySection" style="display:none; margin-top:12px;">
          <div class="nego-section-label">RIWAYAT NEGOSIASI</div>
          <div id="negoHistory"></div>
        </div>

        <div style="margin-top:16px;">
          <div class="nego-section-label">HARGA COUNTER</div>
          <div class="nego-quick-btns">
            <button class="btn-nego-quick" onclick="setQuickNego(-10)">-$10</button>
            <button class="btn-nego-quick" onclick="setQuickNego(-5)">-$5</button>
            <button class="btn-nego-quick" onclick="setQuickNego(5)">+$5</button>
            <button class="btn-nego-quick" onclick="setQuickNego(10)">+$10</button>
          </div>
          <input type="number" id="negoPrice" class="nego-input" placeholder="Masukkan harga..." min="0" step="0.01" />
          <textarea id="negoMessage" class="nego-input" placeholder="Pesan tambahan (opsional)..." rows="2" style="margin-top:8px;"></textarea>
        </div>
      </div>

      <div class="nego-modal-footer">
        <button class="btn-nego-cancel" onclick="closeNegoModal()">Batal</button>
        <button class="btn-nego-submit" onclick="submitNego()">Kirim Counter</button>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
  <script>
    // ════════════════════════════════════════════════════════════════
    //  CHAT.JS — FIXED VERSION
    //  Bug fixes:
    //  1. Route /chat/users tidak terdaftar → tambah di web.php
    //  2. syncUser() dipanggil saat onAuthStateChanged sukses
    //  3. fetchUsersFromServer() sekarang retry + verbose error
    //  4. renderSidebar() dipanggil hanya setelah allUsers terisi
    //  5. Search bekerja langsung tanpa menunggu Firebase WebSocket
    //  6. Rekomendasi teman bekerja karena allUsers sudah tersedia
    //  7. escAttr() dipakai konsisten di semua onclick JSON
    // ════════════════════════════════════════════════════════════════

    const auth = firebase.auth();
    const db   = firebase.database();
    const FB_TS = firebase.database.ServerValue.TIMESTAMP;

    // ── State ──
    let currentUser    = null;
    let activeRoomId   = null;
    let activePartnerId = null;
    let activeMsgOff   = null;
    let activeTypingOff = null;
    let typingTimeout   = null;
    let userRoomsRef   = null;
    let unloadHandler  = null;

    let knownRooms   = {};
    let allUsers     = {};
    let searchResults = [];
    let isSearching   = false;
    let searchTimer   = null;
    const SEARCH_DEBOUNCE_MS = 200;

    let pfpCache = {};

    let replyTo       = null;
    let attachedOffer = null;
    let myOffers      = [];
    let partnerOffers = [];
    let currentNegoOffer = null;

    // FIX: Flag untuk tahu apakah allUsers sudah pernah berhasil dimuat
    let usersLoaded       = false;
    let usersRefreshTimer = null;

    // ── URL params ──
    const params     = new URLSearchParams(window.location.search);
    const initRoom   = params.get('room');
    const initSeller = params.get('sellerId');
    const initName   = decodeURIComponent(params.get('sellerName') || '');
    const initCard   = params.get('cardId');

    // ── CSRF token helper ──
    function getCsrfToken() {
      const meta = document.querySelector('meta[name="csrf-token"]');
      if (!meta) {
        console.error('CSRF meta tag tidak ditemukan. Pastikan layout.app punya <meta name="csrf-token" content="{{ csrf_token() }}">');
        return '';
      }
      return meta.content;
    }

    // ════════════════════════════════════════════════════════════════
    //  AUTH — FIX UTAMA: sinkronisasi ke session Laravel setelah login
    // ════════════════════════════════════════════════════════════════
    auth.onAuthStateChanged(async user => {
      currentUser = user;

      if (!user) {
        // Belum login → redirect ke halaman login
        window.location.href = '{{ route("login") }}?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
        return;
      }

      // FIX: Sinkronisasi user Firebase ke session Laravel
      // Ini penting agar middleware Laravel (misal 'auth') bisa mengenali user
      await syncUserToLaravel(user);

      // Mulai muat data
      await loadAllUsers();  // Tunggu load pertama selesai sebelum render
      loadMyRooms();
      setupUserStatusListener();

      if (initCard) loadOffersForCard(initCard);
      if (initRoom && initSeller) {
        setTimeout(() => openRoom(initRoom, initName, initSeller), 800);
      }
    });

    // ── Sinkronisasi user ke session Laravel ──
    async function syncUserToLaravel(user) {
      try {
        await fetch('{{ route("chat.sync-user") }}', {
          method: 'POST',
          headers: {
            'Content-Type':  'application/json',
            'X-CSRF-TOKEN':  getCsrfToken(),
            'Accept':        'application/json',
          },
          body: JSON.stringify({
            uid:         user.uid,
            displayName: user.displayName || user.email || 'Anonymous',
            email:       user.email || '',
            pfp:         pfpCache[user.uid] || 'default',
          }),
        });
      } catch (e) {
        // Bukan fatal — chat masih bisa jalan tanpa session Laravel
        console.warn('syncUserToLaravel gagal (non-fatal):', e.message);
      }
    }

    // ════════════════════════════════════════════════════════════════
    //  LOAD ALL USERS — FIX: await load pertama, retry jika gagal
    // ════════════════════════════════════════════════════════════════
    async function loadAllUsers() {
      await fetchUsersFromServer();   // Tunggu selesai sebelum render awal
      clearInterval(usersRefreshTimer);
      usersRefreshTimer = setInterval(fetchUsersFromServer, 15000);
    }

    async function fetchUsersFromServer() {
      const csrfToken = getCsrfToken();
      if (!csrfToken) return;  // Jangan fetch kalau CSRF tidak ada

      try {
        const res = await fetch('{{ route("chat.users") }}', {
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept':       'application/json',
          },
        });

        // FIX: Cek status HTTP dulu
        if (!res.ok) {
          const text = await res.text();
          console.error(`fetchUsersFromServer: HTTP ${res.status}`, text.slice(0, 200));
          return;
        }

        const data = await res.json();

        // FIX: Kalau data bukan object (misalnya array error), skip
        if (!data || typeof data !== 'object' || Array.isArray(data)) {
          console.warn('fetchUsersFromServer: data tidak valid', data);
          return;
        }

        // FIX: Kalau response adalah error dari controller
        if (data.error) {
          console.error('fetchUsersFromServer: server error:', data.error);
          return;
        }

        allUsers = {};
        Object.entries(data).forEach(([uid, userData]) => {
          // Skip user sendiri
          if (currentUser && uid === currentUser.uid) return;
          allUsers[uid] = {
            uid,
            name:   userData.name   || 'Anonymous',
            email:  userData.email  || '',
            pfp:    userData.pfp    || 'default',
            status: userData.status || 'offline',
          };
          if (userData.pfp) pfpCache[uid] = userData.pfp;
        });

// Bersihkan knownRooms dari partner yang tidak ada di allUsers
Object.keys(knownRooms).forEach(rId => {
  const pid = knownRooms[rId]?.partnerId;
  if (pid && pid !== currentUser.uid && !allUsers[pid]) {
    delete knownRooms[rId];
  }
});
          usersLoaded = true;

    

        // Re-render apapun yang sedang aktif
        if (isSearching && document.getElementById('userSearchInput')?.value.trim()) {
          doSearch(document.getElementById('userSearchInput').value.trim().toLowerCase());
        } else {
          renderSidebar();
        }

      } catch (e) {
        console.error('fetchUsersFromServer error:', e);
      }
    }

    // ── fetchPfp ──
    async function fetchPfp(uid) {
      if (!uid) return 'default';
      if (pfpCache[uid] !== undefined) return pfpCache[uid];
      try {
        const snap = await db.ref(`users/${uid}/pfp`).once('value');
        pfpCache[uid] = snap.val() || 'default';
      } catch (e) {
        pfpCache[uid] = 'default';
      }
      return pfpCache[uid];
    }

    const nameCache = {};
    async function fetchDisplayName(uid) {
      if (!uid) return 'Anonymous';
      if (nameCache[uid] !== undefined) return nameCache[uid];
      try {
        const snap = await db.ref(`users/${uid}/name`).once('value');
        nameCache[uid] = snap.val() || 'Anonymous';
      } catch (e) {
        nameCache[uid] = 'Anonymous';
      }
      return nameCache[uid];
    }

    // ── Load offers ──
    async function loadOffersForCard(cardId) {
      if (!currentUser) return;

      db.ref(`cards/${cardId}/offers`).orderByChild('uid').equalTo(currentUser.uid)
        .on('value', snapshot => {
          const arr = [];
          snapshot.forEach(child => arr.push({ id: child.key, ...child.val() }));
          myOffers = arr.reverse();
          renderOfferSelector();
        });

      db.ref(`cards/${cardId}/offers`).on('value', snapshot => {
        const arr = [];
        snapshot.forEach(child => {
          const offer = { id: child.key, ...child.val() };
          if (offer.uid !== currentUser.uid) arr.push(offer);
        });
        partnerOffers = arr.reverse();
      });
    }

    // ── User status ──
    function setupUserStatusListener() {
      if (!currentUser) return;
      if (unloadHandler) window.removeEventListener('beforeunload', unloadHandler);

      const userRef = db.ref(`users/${currentUser.uid}`);
      userRef.onDisconnect().update({ status: 'offline', lastSeen: FB_TS });
      userRef.update({ status: 'online', lastSeen: FB_TS });

      unloadHandler = () => userRef.update({ status: 'offline', lastSeen: Date.now() });
      window.addEventListener('beforeunload', unloadHandler);
    }

    // ════════════════════════════════════════════════════════════════
    //  SEARCH — FIX: debounce + doSearch terpisah + render benar
    // ════════════════════════════════════════════════════════════════
    const userSearchInput = document.getElementById('userSearchInput');

    userSearchInput.addEventListener('input', function () {
      const query = this.value.trim().toLowerCase();
      clearTimeout(searchTimer);

      if (!query) {
        isSearching = false;
        searchResults = [];
        document.getElementById('searchResultCount').textContent = 'Chat history';
        renderSidebar();
        return;
      }

      isSearching = true;
      document.getElementById('searchResultCount').textContent = 'Mencari...';
      searchTimer = setTimeout(() => doSearch(query), SEARCH_DEBOUNCE_MS);
    });

    function doSearch(query) {
      // FIX: Kalau allUsers masih kosong, tunjukkan pesan yang jelas
      if (!usersLoaded) {
        const count = document.getElementById('searchResultCount');
        if (count) count.textContent = 'Masih memuat data user...';
        return;
      }

      searchResults = [];
      Object.entries(allUsers).forEach(([uid, user]) => {
        const name  = (user.name  || '').toLowerCase();
        const email = (user.email || '').toLowerCase();
        if (
          name.includes(query) ||
          email.includes(query) ||
          uid.toLowerCase().includes(query)
        ) {
          searchResults.push({ uid, ...user });
        }
      });
      renderSearchResults();
    }

    function renderSearchResults() {
      const list  = document.getElementById('contactList');
      const empty = document.getElementById('sidebarEmpty');
      const count = document.getElementById('searchResultCount');
      if (!list) return;

      list.innerHTML = '';

      if (searchResults.length === 0) {
        // empty.style.display  = 'block';
        // empty.textContent    = 'Tidak ada user ditemukan';
        count.textContent    = '0 user ditemukan';
        list.appendChild(empty);
        return;
      }

      // empty.style.display = 'none';
      count.textContent   = `${searchResults.length} user ditemukan`;

      searchResults.forEach(user => {
        const el       = document.createElement('div');
        el.className   = 'contact-item';
        el.dataset.uid = user.uid;
        el.onclick     = () => createOrOpenRoom(user.uid, user.name, user.pfp);

        const avatarUrl   = `/images/avatar/${user.pfp || 'default'}.png`;
        const isOnline    = user.status === 'online';
        const statusColor = isOnline ? '#4ade80' : '#888';
        const statusLabel = isOnline ? '● ONLINE' : '● OFFLINE';

        el.innerHTML = `
          <div class="contact-avatar">
            <img src="${avatarUrl}" alt="${escHtml(user.name)}" onerror="this.src='/images/avatar/pfp1.png'">
          </div>
          <div class="contact-info">
            <div class="contact-name">${escHtml(user.name.toUpperCase())}</div>
            <div class="contact-last-msg">${escHtml(user.email || user.uid)}</div>
          </div>
          <div style="font-size:0.7rem;color:${statusColor};flex-shrink:0;">${statusLabel}</div>
        `;
        list.appendChild(el);
      });
    }

    // ════════════════════════════════════════════════════════════════
    //  REKOMENDASI TEMAN — FIX: dipanggil setelah usersLoaded = true
    // ════════════════════════════════════════════════════════════════
    function renderRecommendations() {
      const list  = document.getElementById('contactList');
      const empty = document.getElementById('sidebarEmpty');
      const count = document.getElementById('searchResultCount');
      if (!list || !count) return;

      if (!usersLoaded) {
        // empty.style.display = 'block';
        // empty.textContent   = 'Memuat user...';
        count.textContent   = '';
        return;
      }

      const recs = Object.entries(allUsers)
        .filter(([uid]) => {
          // Filter user yang belum pernah chat
          const roomId = [currentUser.uid, uid].sort().join('_');
          return !knownRooms[roomId];
        })
        .map(([uid, user]) => ({ uid, ...user }))
        .sort((a, b) => {
          if (a.status === 'online' && b.status !== 'online') return -1;
          if (b.status === 'online' && a.status !== 'online') return 1;
          return (a.name || '').localeCompare(b.name || '');
        })
        .slice(0, 8);

      if (recs.length === 0) {
        // empty.style.display = 'block';
        // empty.textContent   = 'Belum ada user lain. Ketik nama untuk cari.';
        count.textContent   = '';
        return;
      }

      // empty.style.display = 'none';
      count.textContent   = ' Rekomendasi Teman';
      list.innerHTML      = `<div class="rec-section-label">PENGGUNA AKTIF</div>`;

      recs.forEach(user => {
        const el       = document.createElement('div');
        el.className   = 'contact-item rec-item';
        el.dataset.uid = user.uid;
        el.onclick     = () => createOrOpenRoom(user.uid, user.name, user.pfp);

        const avatarUrl   = `/images/avatar/${user.pfp || 'default'}.png`;
        const isOnline    = user.status === 'online';
        const statusColor = isOnline ? '#4ade80' : '#888';

        el.innerHTML = `
          <div class="contact-avatar" style="position:relative;">
            <img src="${avatarUrl}" alt="${escHtml(user.name)}" onerror="this.src='/images/avatar/pfp1.png'">
            <span style="position:absolute;bottom:0;right:0;width:9px;height:9px;border-radius:50%;background:${statusColor};border:2px solid var(--sidebar-bg,#0f172a);"></span>
          </div>
          <div class="contact-info">
            <div class="contact-name">${escHtml(user.name.toUpperCase())}</div>
            <div class="contact-last-msg" style="color:${statusColor};font-size:0.65rem;">${isOnline ? 'ONLINE SEKARANG' : 'OFFLINE'}</div>
          </div>
          <div class="rec-add-btn" title="Mulai chat">💬</div>
        `;
        list.appendChild(el);
      });
    }

    // ── createOrOpenRoom ──
    async function createOrOpenRoom(partnerId, partnerName, partnerPfp) {
      if (!currentUser) return;

      const uids   = [currentUser.uid, partnerId].sort();
      const roomId = `${uids[0]}_${uids[1]}`;

      userSearchInput.value = '';
      isSearching           = false;

      const roomSnap = await db.ref(`chats/${roomId}`).once('value');
      if (!roomSnap.exists()) {
        await db.ref(`chats/${roomId}`).set({
          createdAt:    FB_TS,
          participants: { [currentUser.uid]: true, [partnerId]: true },
          messages:     {}
        });

        await db.ref(`user_rooms/${currentUser.uid}/${roomId}`).set({
          name:      partnerName,
          avatar:    partnerPfp || 'default',
          lastMsg:   '',
          lastTs:    Date.now(),
          partnerId: partnerId,
        });

        knownRooms[roomId] = {
          name: partnerName, avatar: partnerPfp || 'default',
          lastMsg: '', lastTs: Date.now(), partnerId: partnerId,
        };
      }

      renderSidebar();
      openRoom(roomId, partnerName, partnerId);
    }

    // ── loadMyRooms ──
    async function loadMyRooms() {
      if (userRoomsRef) userRoomsRef.off('value');

      userRoomsRef = db.ref(`user_rooms/${currentUser.uid}`);
      userRoomsRef.on('value', async snapshot => {
        const data  = snapshot.val() || {};
        knownRooms  = data;

        const uids = Object.values(data).map(r => r.partnerId).filter(Boolean);
        if (initSeller) uids.push(initSeller);
        await Promise.all(uids.map(uid => fetchPfp(uid)));

        if (!isSearching) renderSidebar();

        if (initRoom && initSeller && !data[initRoom]) {
          db.ref(`user_rooms/${currentUser.uid}/${initRoom}`).set({
            name: initName, avatar: null, lastMsg: '',
            lastTs: Date.now(), partnerId: initSeller,
          });
        }
      });
    }

    // ── renderSidebar ──
 function renderSidebar() {
  if (isSearching) return;

  const list  = document.getElementById('contactList');
  const count = document.getElementById('searchResultCount');
  if (!list || !count) return;

  const rooms = Object.entries(knownRooms)
    .filter(([rId, info]) => {
      const pid = info.partnerId;
      if (!pid || pid === currentUser.uid) return false;
      const alreadyChatted = Object.values(knownRooms).some(r => r.partnerId === pid);
      return alreadyChatted;
    })
    .sort((a, b) => (b[1].lastTs || 0) - (a[1].lastTs || 0));

  list.innerHTML = '';

  if (rooms.length > 0) {
    const historyLabel = document.createElement('div');
    historyLabel.className = 'rec-section-label';
    historyLabel.textContent = '💬 RIWAYAT CHAT';
    list.appendChild(historyLabel);

    rooms.forEach(([rId, info]) => {
      renderContactItem(list, rId, info.name || 'Anonymous', info.partnerId, info.lastMsg || '');
    });

    count.textContent = 'Chat history';
  } else {
    count.textContent = '✨ Rekomendasi Teman';
  }

  renderRecommendationsAppended(list);
}

   function renderRecommendationsAppended(list) {
  if (!usersLoaded) return;

  const chattedPartnerIds = new Set(
    Object.values(knownRooms).map(r => r.partnerId).filter(Boolean)
  );

  const recs = Object.entries(allUsers)
    .filter(([uid]) => !chattedPartnerIds.has(uid))
    .map(([uid, user]) => ({ uid, ...user }))
    .sort((a, b) => {
      if (a.status === 'online' && b.status !== 'online') return -1;
      if (b.status === 'online' && a.status !== 'online') return 1;
      return (a.name || '').localeCompare(b.name || '');
    })
    .slice(0, 8);

  if (recs.length === 0) return;

  const label = document.createElement('div');
  label.className = 'rec-section-label';
  label.textContent = ' REKOMENDASI TEMAN';
  label.style.marginTop = '16px';
  list.appendChild(label);

  recs.forEach(user => {
    const el = document.createElement('div');
    el.className = 'contact-item rec-item';
    el.dataset.uid = user.uid;
    el.onclick = () => createOrOpenRoom(user.uid, user.name, user.pfp);

    const avatarUrl = `/images/avatar/${user.pfp || 'default'}.png`;
    const isOnline = user.status === 'online';
    const statusColor = isOnline ? '#4ade80' : '#888';

    el.innerHTML = `
      <div class="contact-avatar" style="position:relative;">
        <img src="${avatarUrl}" alt="${escHtml(user.name)}" onerror="this.style.display='none'">
        <span style="position:absolute;bottom:0;right:0;width:9px;height:9px;border-radius:50%;background:${statusColor};border:2px solid var(--sidebar-bg,#0f172a);"></span>
      </div>
      <div class="contact-info">
        <div class="contact-name">${escHtml(user.name.toUpperCase())}</div>
        <div class="contact-last-msg" style="color:${statusColor};font-size:0.65rem;">${isOnline ? 'ONLINE SEKARANG' : 'OFFLINE'}</div>
      </div>
      <div class="rec-add-btn" title="Mulai chat">💬</div>
    `;
    list.appendChild(el);
  });
}

    function renderContactItem(container, roomId, name, partnerId, lastMsg) {
      const pfp       = pfpCache[partnerId] || 'default';
      const avatarUrl = `/images/avatar/${pfp}.png`;
      const partnerData = allUsers[partnerId];
      const isOnline  = partnerData && partnerData.status === 'online';
      const statusColor = isOnline ? '#4ade80' : '#888';

      const el        = document.createElement('div');
      el.className    = 'contact-item' + (roomId === activeRoomId ? ' active' : '');
      el.dataset.room = roomId;
      el.onclick      = () => openRoom(roomId, name, partnerId);

      el.innerHTML = `
        <div class="contact-avatar" style="position:relative;">
          <img src="${avatarUrl}" alt="${escHtml(name)}" onerror="this.src='/images/avatar/pfp1.png'">
          <span style="position:absolute;bottom:0;right:0;width:9px;height:9px;border-radius:50%;background:${statusColor};border:2px solid var(--sidebar-bg,#0f172a);"></span>
        </div>
        <div class="contact-info">
          <div class="contact-name">${escHtml(name.toUpperCase())}</div>
          <div class="contact-last-msg">${escHtml(lastMsg)}</div>
        </div>
      `;
      container.appendChild(el);
    }

    // ── openRoom ──
    async function openRoom(roomId, name, partnerId) {
      clearReplyPreview();
      clearAttachedOffer();

      document.querySelectorAll('.contact-item').forEach(i => i.classList.remove('active'));
      const contactEl = document.querySelector(`[data-room="${roomId}"]`);
      if (contactEl) contactEl.classList.add('active');

      activeRoomId = roomId;
      activePartnerId = partnerId;

      document.getElementById('chatEmptyState').style.display  = 'none';
      document.getElementById('chatHeader').style.display      = 'flex';
      document.getElementById('chatMessages').style.display    = 'flex';
      document.getElementById('chatInputWrap').style.display   = 'flex';

      const pfp       = await fetchPfp(partnerId);
      const avatarUrl = `/images/avatar/${pfp}.png`;
      document.getElementById('headerName').textContent = name.toUpperCase();
      document.getElementById('headerAvatar').innerHTML =
        `<img src="${avatarUrl}" alt="${escHtml(name)}" style="width:100%;height:100%;object-fit:cover;border-radius:50%" onerror="this.src='/images/avatar/pfp1.png'">`;

      const partnerData = allUsers[partnerId];
      const isOnline    = partnerData && partnerData.status === 'online';
      const statusDot   = document.getElementById('headerStatusDot');
      const statusText  = document.getElementById('headerStatusText');
      if (statusDot)  statusDot.style.background = isOnline ? '#4ade80' : '#888';
      if (statusText) statusText.textContent      = isOnline ? 'ONLINE' : 'OFFLINE';

      db.ref(`users/${partnerId}/status`).on('value', snap => {
        const online = snap.val() === 'online';
        if (statusDot)  statusDot.style.background = online ? '#4ade80' : '#888';
        if (statusText) statusText.textContent      = online ? 'ONLINE' : 'OFFLINE';
      });

      document.getElementById('chatInput').focus();

      if (activeMsgOff) { activeMsgOff(); activeMsgOff = null; }
      if (activeTypingOff) { activeTypingOff(); activeTypingOff = null; }
      
      // Reset typing state
      clearMyTyping();

      const offerComposeCard = document.getElementById('offerComposeCard');
      if (offerComposeCard) {
        offerComposeCard.style.display = (initCard && roomId === initRoom) ? 'block' : 'none';
      }

      const wrap = document.getElementById('chatMessages');
      wrap.innerHTML = '';

      const msgsRef = db.ref(`chats/${roomId}/messages`).orderByChild('createdAt').limitToLast(100);
      const handler = msgsRef.on('value', async snapshot => {
        wrap.innerHTML = '';
        let lastDate   = null;
        const messages = [];

        snapshot.forEach(child => {
          const msg = { id: child.key, ...child.val() };
          if (msg.deletedFor && currentUser && msg.deletedFor[currentUser.uid]) return;
          messages.push(msg);
        });

        const uids = [...new Set(messages.map(m => m.uid).filter(Boolean))];
        await Promise.all([
          Promise.all(uids.map(uid => fetchPfp(uid))),
          Promise.all(uids.map(uid => fetchDisplayName(uid)))
        ]);

        for (const m of messages) {
          const msgDate = m.createdAt
            ? new Date(m.createdAt).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
            : null;

          if (msgDate && msgDate !== lastDate) {
            appendDateSep(wrap, msgDate);
            lastDate = msgDate;
          }
          appendMessage(wrap, m, partnerId);
        }
        wrap.scrollTop = wrap.scrollHeight;
      });
      const changeHandler = msgsRef.on('child_changed', snapshot => {
        const msgId = snapshot.key;
        const msg = snapshot.val();
        const oldCard = document.querySelector(`.chat-offer-card[data-message-id="${msgId}"]`);
        if (oldCard && msg.offer) {
          const offer = msg.offer;
          const isMine = msg.senderId === currentUser?.uid;
          const status = offer.status || 'pending';
          
          // Update price
          const priceEl = oldCard.querySelector('.chat-offer-price');
          if (priceEl) priceEl.textContent = `$${offer.price || 0}`;
          
          // Update status meta
          const metaSpans = oldCard.querySelectorAll('.chat-offer-meta span');
          if (metaSpans.length >= 2) {
            metaSpans[1].textContent = `Status: ${status.toUpperCase()}`;
          }

          // Update actions buttons
          const actionsDiv = oldCard.querySelector('.chat-offer-actions');
          if (actionsDiv) {
            if (status !== 'done') {
              const negoBtn = !isMine ? `<button type="button" class="chat-offer-nego-btn" data-message-id="${msgId}">Nego</button>` : '';
              const doneBtn = `<button type="button" class="chat-offer-done-btn" data-message-id="${msgId}">Selesai</button>`;
              actionsDiv.innerHTML = `${negoBtn}${doneBtn}`;
            } else {
              actionsDiv.className = 'chat-offer-actions done-state';
              actionsDiv.style.color = '#22c55e';
              actionsDiv.style.fontWeight = '800';
              actionsDiv.style.fontSize = '0.85rem';
              actionsDiv.style.padding = '4px 0';
              actionsDiv.textContent = '✓ Transaksi Selesai';
            }
          }
        }
      });

      activeMsgOff = () => {
        msgsRef.off('value', handler);
        msgsRef.off('child_changed', changeHandler);
      };

      // Typing Indicator Listener
      const typingRef = db.ref(`chats/${roomId}/typing`);
      const typingHandler = typingRef.on('value', snapshot => {
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
          if (uid === currentUser.uid) continue;
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
        } else {
          indicator.style.display = 'block';
          indicator.innerText = `${typers[0]} dan ${typers.length - 1} lainnya sedang mengetik...`;
        }
      });
      activeTypingOff = () => typingRef.off('value', typingHandler);

      // Clean typing status on disconnect
      db.ref(`chats/${roomId}/typing/${currentUser.uid}`).onDisconnect().remove();
    }

    // ── appendMessage ──
    function appendMessage(container, msg, partnerId) {
      const isSelf    = msg.uid === currentUser?.uid;
      const pfp       = pfpCache[msg.uid] || 'default';
      const avatarUrl = `/images/avatar/${pfp}.png`;
      const displayName = nameCache[msg.uid] || 'Anonymous';
      const timeStr   = msg.createdAt
        ? new Date(msg.createdAt).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
        : '';

      const row     = document.createElement('div');
      row.id        = `msg-${msg.id}`;
      row.className = `msg-row ${isSelf ? 'self' : 'other'}`;

      let contentHtml = '';
      let actionHtml  = '';

      if (msg.replyTo) {
        const replyText = msg.replyTo.deletedForAll
          ? '🚫 Pesan dihapus'
          : (msg.replyTo.text || (msg.replyTo.imageUrl ? '📷 Foto' : '📎 File'));
        contentHtml += `
          <div class="msg-reply-preview" style="padding:6px 10px;background:rgba(255,255,255,0.05);border-left:2px solid var(--pink);border-radius:4px;margin-bottom:6px;font-size:0.7rem;">
            <div style="color:var(--pink);margin-bottom:2px;">@${escHtml(msg.replyTo.displayName)}</div>
            <div style="color:var(--muted);opacity:0.8;">${escHtml(replyText)}</div>
          </div>
        `;
      }

      if (msg.deletedForAll) {
        contentHtml = `<div class="msg-bubble deleted" style="opacity:0.5;font-style:italic;font-size:0.75rem;">🚫 Pesan telah dihapus</div>`;
      } else if (msg.type === 'offer') {
        const offer = msg.offer || {};
        const isMine = msg.senderId === currentUser?.uid;
        const status = offer.status || 'pending';
        let actionButtonsHtml = '';
        
        if (status !== 'done') {
          const negoBtn = !isMine ? `<button type="button" class="chat-offer-nego-btn" data-message-id="${msg.id}">Nego</button>` : '';
          const doneBtn = `<button type="button" class="chat-offer-done-btn" data-message-id="${msg.id}">Selesai</button>`;
          actionButtonsHtml = `
            <div class="chat-offer-actions">
              ${negoBtn}
              ${doneBtn}
            </div>
          `;
        } else {
          actionButtonsHtml = `
            <div class="chat-offer-actions done-state" style="color: #22c55e; font-weight: 800; font-size: 0.85rem; padding: 4px 0;">
              ✓ Transaksi Selesai
            </div>
          `;
        }

        contentHtml += `
          <div class="chat-offer-card" data-message-id="${msg.id}">
              <div class="chat-offer-top">
                  <div>
                      <span class="chat-offer-label">Offer Kartu</span>
                      <h4 class="chat-offer-title">Penawaran Baru</h4>
                  </div>
                  <strong class="chat-offer-price">
                      $${escHtml(String(offer.price || 0))}
                  </strong>
              </div>

              <div class="chat-offer-meta">
                  <span>Kondisi: ${escHtml(offer.condition || '-')}</span>
                  <span>Status: ${escHtml(status.toUpperCase())}</span>
              </div>

              ${offer.message ? `<p class="chat-offer-note">${escHtml(offer.message)}</p>` : ''}
              
              ${actionButtonsHtml}
          </div>
        `;
      } else if (msg.type === 'nego') {
        const nego = msg.nego || {};
        const isMine = msg.senderId === currentUser?.uid;
        contentHtml += `
          <div class="chat-nego-card" style="border: 1px dashed rgba(139, 92, 246, 0.4); border-radius: 12px; padding: 10px 14px; background: rgba(139, 92, 246, 0.15); color: #fff; max-width: 320px; text-align: left;">
            <div style="font-size: 0.75rem; color: #a78bfa; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">Counter Offer / Nego</div>
            <div style="font-size: 1.15rem; font-weight: 900; color: #4ade80; margin: 4px 0;">$${escHtml(String(nego.price || 0))}</div>
            <div style="font-size: 0.7rem; color: rgba(255,255,255,0.7);">${isMine ? 'Anda mengajukan harga counter' : 'Lawan bicara mengajukan harga counter'}</div>
          </div>
        `;
      } else if (msg.imageUrl) {
        contentHtml += `
          <div class="msg-bubble msg-bubble--image">
            <a href="${escHtml(msg.imageUrl)}" target="_blank" rel="noopener">
              <img src="${escHtml(msg.imageUrl)}"
                   style="max-width:220px;max-height:280px;border-radius:10px;display:block;cursor:pointer;"
                   onerror="this.style.display='none'"
                   loading="lazy">
            </a>
          </div>
        `;
      } else if (msg.fileUrl) {
        contentHtml += `
          <div class="msg-bubble" style="display:flex;align-items:center;gap:8px;">
            <span style="font-size:1.5rem;">📎</span>
            <a href="${escHtml(msg.fileUrl)}" target="_blank" rel="noopener"
               style="color:inherit;text-decoration:underline;">${escHtml(msg.fileName || 'File')}</a>
          </div>
        `;
      } else {
        contentHtml += `<div class="msg-bubble">${escHtml(msg.text).replace(/\n/g, '<br>')}</div>`;
      }

      if (!msg.deletedForAll && msg.type !== 'offer' && msg.type !== 'nego') {
        const msgJson = escAttr(JSON.stringify(msg));
        actionHtml = `
          <div class="msg-actions">
            <button class="btn-msg-action" onclick='prepareReply(${msgJson})'>Reply</button>
            <button class="btn-msg-action" onclick='deleteMessageForMe("${escHtml(msg.id)}")'>Hide</button>
            ${isSelf ? `<button class="btn-msg-action danger" onclick='deleteMessageForAll("${escHtml(msg.id)}")'>Delete</button>` : ''}
          </div>
        `;
      }

      row.innerHTML = `
        <div class="msg-avatar">
          <img src="${avatarUrl}" alt="" onerror="this.src='/images/avatar/pfp1.png'">
        </div>
        <div class="msg-content">
          ${!isSelf ? `<div class="msg-name">${escHtml(displayName)}</div>` : ''}
          ${contentHtml}
          <div class="msg-time">${timeStr}</div>
          ${actionHtml}
        </div>
      `;
      container.appendChild(row);
    }

    function appendDateSep(container, dateStr) {
      const el     = document.createElement('div');
      el.className = 'date-sep';
      el.innerHTML = `
        <div class="date-sep-line"></div>
        <div class="date-sep-text">${escHtml(dateStr)}</div>
        <div class="date-sep-line"></div>
      `;
      container.appendChild(el);
    }

    // ── sendMessage ──
    async function sendMessage() {
      if (!currentUser || !activeRoomId) return;
      const input     = document.getElementById('chatInput');
      const text      = input.value.trim();
      const fileInput = document.getElementById('fileInput');

      if (!text && !fileInput.files.length && !attachedOffer) return;

      input.value = '';
      autoResizeTextarea(input);
      document.getElementById('btnSend').classList.remove('active');

      const myPfp = await fetchPfp(currentUser.uid);
      let messageData = {
        uid:         currentUser.uid,
        displayName: currentUser.displayName || currentUser.email || 'Anonymous',
        photoURL:    myPfp,
        text:        text || '',
        type:        'text',
        createdAt:   Date.now(),
      };

      if (replyTo) {
        messageData.replyTo = {
          id:          replyTo.id,
          uid:         replyTo.uid,
          displayName: replyTo.displayName,
          text:        replyTo.text || '',
          imageUrl:    replyTo.imageUrl || null,
        };
      }

      if (attachedOffer) {
        messageData.offer = {
          offerId:      attachedOffer.id,
          cardId:       initCard,
          uid:          attachedOffer.uid,
          displayName:  attachedOffer.displayName,
          price:        attachedOffer.price,
          condition:    attachedOffer.condition,
          desc:         attachedOffer.desc,
          status:       'pending',
          negotiations: []
        };
        if (!messageData.text) {
          messageData.text = `Menambahkan offer: ${attachedOffer.desc || attachedOffer.price || 'Penawaran'}`;
        }
      }

      if (fileInput.files.length > 0) {
        const file    = fileInput.files[0];
        const isImage = file.type.startsWith('image/');

        if (file.size > 10 * 1024 * 1024) {
          alert('File terlalu besar. Max 10MB.');
          return;
        }

        try {
          showUploadProgress(true);
          const uploadUrl = await uploadToCloudinary(file);

          if (isImage) {
            messageData.imageUrl = uploadUrl;
            messageData.type     = 'image';
          } else {
            messageData.fileUrl  = uploadUrl;
            messageData.fileName = file.name;
            messageData.type     = 'file';
          }
        } catch (e) {
          alert('Gagal upload file: ' + e.message);
          showUploadProgress(false);
          return;
        }

        fileInput.value = '';
        clearFilePreview();
        showUploadProgress(false);
      }

      try {
        await db.ref(`chats/${activeRoomId}/messages`).push(removeUndefined(messageData));
        clearMyTyping();
        clearReplyPreview();
        clearAttachedOffer();

        const preview = messageData.imageUrl
          ? '📸 Gambar'
          : (messageData.fileUrl ? `📎 ${messageData.fileName}` : text);

        await db.ref(`user_rooms/${currentUser.uid}/${activeRoomId}`)
          .update({ lastMsg: preview, lastTs: Date.now() });
      } catch (e) {
        console.error('sendMessage error:', e);
        alert('Gagal mengirim pesan.');
      }
    }

    // ── Delete ──
    async function deleteMessageForMe(msgId) {
      if (!currentUser || !activeRoomId || !msgId) return;
      if (replyTo && String(replyTo.id) === String(msgId)) clearReplyPreview();
      try {
        await db.ref(`chats/${activeRoomId}/messages/${msgId}/deletedFor/${currentUser.uid}`).set(true);
      } catch (e) {
        console.error('deleteMessageForMe error:', e);
        alert('Gagal menyembunyikan pesan.');
      }
    }

    async function deleteMessageForAll(msgId) {
      if (!confirm('Hapus pesan ini untuk semua orang?')) return;
      if (!currentUser || !activeRoomId || !msgId) return;
      try {
        const msgRef = db.ref(`chats/${activeRoomId}/messages/${msgId}`);
        const snap   = await msgRef.once('value');
        const msg    = snap.val();
        if (!msg) return;
        if (msg.uid !== currentUser.uid) { alert('Hanya bisa menghapus pesan sendiri.'); return; }

        await msgRef.update({
          text: 'Pesan telah dihapus',
          imageUrl: null, fileUrl: null, fileName: null,
          type: 'text', deletedForAll: true,
        });
        if (replyTo && String(replyTo.id) === String(msgId)) clearReplyPreview();
        db.ref(`user_rooms/${currentUser.uid}/${activeRoomId}`).update({ lastMsg: 'Pesan telah dihapus' });
      } catch (e) {
        console.error('deleteMessageForAll error:', e);
      }
    }

    // ── Reply ──
    function prepareReply(msg) {
      if (!msg || !msg.id) return;
      replyTo = {
        id: msg.id, uid: msg.uid || null,
        displayName: msg.displayName || 'Anonymous',
        text: msg.text || '', imageUrl: msg.imageUrl || null,
        fileUrl: msg.fileUrl || null, fileName: msg.fileName || null,
        deletedForAll: !!msg.deletedForAll
      };

      const preview = document.getElementById('replyPreview');
      const text    = document.getElementById('replyPreviewText');
      if (!preview || !text) return;

      preview.classList.add('active');
      text.textContent = replyTo.deletedForAll
        ? '🚫 Pesan dihapus'
        : (replyTo.text || (replyTo.imageUrl ? '📷 Foto' : (replyTo.fileUrl ? `📎 ${replyTo.fileName}` : 'Pesan')));

      document.getElementById('chatInput').focus();
    }

    function clearReplyPreview() {
      replyTo = null;
      const preview = document.getElementById('replyPreview');
      const text    = document.getElementById('replyPreviewText');
      if (preview) preview.classList.remove('active');
      if (text)    text.textContent = '';
      document.getElementById('chatInput')?.focus();
    }

    // ── Offer selector ──
    function toggleOfferSelector() {
      const panel = document.getElementById('offerSelector');
      if (!panel) return;
      const isActive = panel.classList.contains('active');
      panel.classList.toggle('active', !isActive);
      if (!isActive) renderOfferSelector();
    }

    function renderOfferSelector() {
      const content = document.getElementById('offerSelectorContent');
      if (!content) return;
      const allOffers = [...myOffers, ...partnerOffers];
      if (!allOffers.length) {
        content.innerHTML = '<div class="offer-selector-empty">Tidak ada penawaran di card ini.</div>';
        return;
      }
      content.innerHTML = allOffers.map(offer => {
        const isMine     = offer.uid === currentUser.uid;
        const ownerLabel = isMine ? '(Penawaran Anda)' : `(dari ${escHtml(offer.displayName || 'User')})`;
        return `
          <div class="offer-item" onclick='selectAttachedOffer(${escAttr(JSON.stringify(offer))})'>
            <div class="offer-item-header">
              <div class="offer-item-desc">${escHtml(offer.desc || 'Penawaran')}</div>
              <div class="offer-item-price">$${escHtml(String(offer.price || '0'))}</div>
            </div>
            <div class="offer-item-meta">${escHtml(offer.condition || 'NM')} • ${ownerLabel}</div>
          </div>
        `;
      }).join('');
    }

    function selectAttachedOffer(offer) {
      attachedOffer = offer;
      document.getElementById('offerSelector').classList.remove('active');
      renderAttachedOffer();
    }

    function renderAttachedOffer() {
      const preview = document.getElementById('offerAttachmentPreview');
      const summary = document.getElementById('attachedOfferSummary');
      if (!preview || !summary) return;
      if (!attachedOffer) { preview.classList.remove('active'); summary.innerHTML = ''; return; }

      preview.classList.add('active');
      const ownerLabel = attachedOffer.uid === currentUser.uid
        ? 'Penawaran Anda'
        : `Penawaran dari ${escHtml(attachedOffer.displayName || 'User')}`;

      summary.innerHTML = `
        <div style="font-size:0.8rem;color:var(--text);">${escHtml(attachedOffer.desc)} - <strong>$${escHtml(String(attachedOffer.price))}</strong></div>
        <div style="font-size:0.7rem;color:var(--muted);margin-top:2px;">${ownerLabel}</div>
      `;
    }

    function clearAttachedOffer() {
      attachedOffer = null;
      document.getElementById('offerAttachmentPreview')?.classList.remove('active');
      document.getElementById('offerSelector')?.classList.remove('active');
    }

    // ── Nego ──
    function openNegoModal(msg) {
      if (!msg.offer) return;
      currentNegoOffer = msg;

      document.getElementById('negoOriginalOffer').innerHTML = `
        <div class="nego-offer-row"><span class="nego-offer-label">Deskripsi</span><span class="nego-offer-value">${escHtml(msg.offer.desc)}</span></div>
        <div class="nego-offer-row"><span class="nego-offer-label">Harga</span><span class="nego-offer-value price">$${escHtml(String(msg.offer.price))}</span></div>
        <div class="nego-offer-row"><span class="nego-offer-label">Kondisi</span><span class="nego-offer-value">${escHtml(msg.offer.condition)}</span></div>
      `;

      const negotiations   = msg.offer.negotiations || [];
      const historySection = document.getElementById('negoHistorySection');
      const historyDiv     = document.getElementById('negoHistory');

      if (negotiations.length > 0) {
        historySection.style.display = 'block';
        historyDiv.innerHTML = negotiations.map(nego => {
          const time = new Date(nego.timestamp).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
          return `
            <div class="nego-history-item">
              <div class="nego-history-header">
                <span class="nego-history-user">${escHtml(nego.displayName)}</span>
                <span class="nego-history-time">${time}</span>
              </div>
              <div class="nego-history-price">$${escHtml(String(nego.price))}</div>
              ${nego.message ? `<div class="nego-history-message">${escHtml(nego.message)}</div>` : ''}
            </div>
          `;
        }).join('');
      } else {
        historySection.style.display = 'none';
      }

      document.getElementById('negoPrice').value   = '';
      document.getElementById('negoMessage').value = '';
      document.getElementById('negoModal').classList.add('active');
    }

    function closeNegoModal() {
      document.getElementById('negoModal').classList.remove('active');
      currentNegoOffer = null;
    }

    function setQuickNego(amount) {
      if (!currentNegoOffer?.offer) return;
      const newPrice = Math.max(0, (parseFloat(currentNegoOffer.offer.price) || 0) + amount);
      document.getElementById('negoPrice').value = newPrice.toFixed(2);
    }

    async function submitNego() {
      if (!currentNegoOffer || !activeRoomId) return;
      const counterPrice   = parseFloat(document.getElementById('negoPrice').value);
      const counterMessage = document.getElementById('negoMessage').value.trim();

      if (!counterPrice || counterPrice <= 0) { alert('Masukkan harga counter offer yang valid.'); return; }

      try {
        const negoEntry = {
          uid:         currentUser.uid,
          displayName: currentUser.displayName || currentUser.email || 'Anonymous',
          price:       counterPrice,
          message:     counterMessage,
          timestamp:   Date.now()
        };

        const msgRef     = db.ref(`chats/${activeRoomId}/messages/${currentNegoOffer.id}`);
        const snapshot   = await msgRef.once('value');
        const currentMsg = snapshot.val();
        if (!currentMsg?.offer) { alert('Offer tidak ditemukan.'); return; }

        const negotiations = [...(currentMsg.offer.negotiations || []), negoEntry];
        await msgRef.child('offer').update({ status: 'negotiating', negotiations, lastNego: negoEntry });

        const myPfp = await fetchPfp(currentUser.uid);
        await db.ref(`chats/${activeRoomId}/messages`).push({
          uid:         currentUser.uid,
          displayName: currentUser.displayName || currentUser.email || 'Anonymous',
          photoURL:    myPfp,
          text:        `💬 Counter offer: $${counterPrice}${counterMessage ? ' - ' + counterMessage : ''}`,
          type:        'text',
          createdAt:   Date.now(),
        });

        closeNegoModal();
      } catch (e) {
        console.error('submitNego error:', e);
        alert('Gagal mengirim counter offer.');
      }
    }

    async function acceptOffer(msg) {
      if (!confirm('Setuju dengan penawaran ini?')) return;
      if (!msg.offer || !activeRoomId) return;
      try {
        await db.ref(`chats/${activeRoomId}/messages/${msg.id}/offer`)
          .update({ status: 'accepted', acceptedBy: currentUser.uid, acceptedAt: Date.now() });

        const myPfp = await fetchPfp(currentUser.uid);
        await db.ref(`chats/${activeRoomId}/messages`).push({
          uid:         currentUser.uid,
          displayName: currentUser.displayName || currentUser.email || 'Anonymous',
          photoURL:    myPfp,
          text:        `✅ Menyetujui penawaran $${msg.offer.price}`,
          type:        'text',
          createdAt:   Date.now(),
        });
      } catch (e) {
        console.error('acceptOffer error:', e);
        alert('Gagal menerima penawaran.');
      }
    }

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

    async function sendOfferMessage() {
      try {
        if (!currentUser || !currentUser.uid) {
          alert('Kamu harus login.');
          return;
        }

        const sellerId = activePartnerId || initSeller;
        if (!activeRoomId || !sellerId || !initCard) {
          alert('Data chat tidak lengkap.');
          return;
        }

        const priceInput     = document.getElementById('offerPriceInput');
        const conditionInput = document.getElementById('offerConditionInput');
        const noteInput      = document.getElementById('offerNoteInput');

        const price     = Number(priceInput?.value || 0);
        const condition = conditionInput?.value?.trim() || '';
        const note      = noteInput?.value?.trim() || '';

        if (!price || price <= 0) {
          alert('Masukkan harga offer yang valid.');
          return;
        }

        const payload = removeUndefined({
          type:       'offer',
          uid:        currentUser.uid,
          senderId:   currentUser.uid,
          receiverId: sellerId,
          cardId:     initCard,
          createdAt:  firebase.database.ServerValue.TIMESTAMP,
          offer: {
            cardId:    initCard,
            price:     price,
            condition: condition,
            message:   note,
            status:    'pending'
          }
        });

        await db.ref(`chats/${activeRoomId}/messages`).push(payload);

        priceInput.value     = '';
        conditionInput.value = '';
        noteInput.value      = '';

        const preview = `Ajukan offer: $${price}`;
        await db.ref(`user_rooms/${currentUser.uid}/${activeRoomId}`)
          .update({ lastMsg: preview, lastTs: Date.now() });

      } catch (error) {
        console.error('sendOfferMessage error:', error);
        alert('Gagal mengirim offer.');
      }
    }

    async function negotiateOffer(messageId) {
      const newPrice = prompt('Masukkan harga nego:');
      if (!newPrice) return;

      const price = Number(newPrice);
      if (!price || price <= 0) {
        alert('Harga tidak valid.');
        return;
      }

      try {
        const partnerId = activePartnerId || initSeller || 'partner';
        const payload = removeUndefined({
          type:       'nego',
          uid:        currentUser.uid,
          senderId:   currentUser.uid,
          receiverId: partnerId,
          cardId:     initCard || 'card',
          createdAt:  firebase.database.ServerValue.TIMESTAMP,
          nego: {
            relatedOfferId: messageId,
            price:          price,
            status:         'pending'
          }
        });

        await db.ref(`chats/${activeRoomId}/messages`).push(payload);
        await db.ref(`chats/${activeRoomId}/messages/${messageId}/offer/status`).set('negotiating');

        const preview = `Nego harga baru: $${price}`;
        await db.ref(`user_rooms/${currentUser.uid}/${activeRoomId}`)
          .update({ lastMsg: preview, lastTs: Date.now() });

      } catch (error) {
        console.error('negotiateOffer error:', error);
        alert('Gagal mengirim nego.');
      }
    }

    async function completeOffer(messageId) {
      if (!confirm('Tandai offer ini sebagai selesai?')) return;

      try {
        await db.ref(`chats/${activeRoomId}/messages/${messageId}/offer/status`).set('done');
        await db.ref(`chats/${activeRoomId}/messages/${messageId}/offer/completedAt`).set(firebase.database.ServerValue.TIMESTAMP);
        await db.ref(`chats/${activeRoomId}/messages/${messageId}/offer/completedBy`).set(currentUser.uid);

        const preview = `Transaksi selesai`;
        await db.ref(`user_rooms/${currentUser.uid}/${activeRoomId}`)
          .update({ lastMsg: preview, lastTs: Date.now() });
      } catch (error) {
        console.error('completeOffer error:', error);
        alert('Gagal menyelesaikan offer.');
      }
    }

    // Bind event send offer & click handler
    document.addEventListener('DOMContentLoaded', () => {
      const btn = document.getElementById('sendOfferBtn');
      if (btn) {
        btn.addEventListener('click', sendOfferMessage);
      }
    });

    document.addEventListener('click', function(e) {
      const negoBtn = e.target.closest('.chat-offer-nego-btn');
      const doneBtn = e.target.closest('.chat-offer-done-btn');

      if (negoBtn) {
        negotiateOffer(negoBtn.dataset.messageId);
      }
      if (doneBtn) {
        completeOffer(doneBtn.dataset.messageId);
      }
    });

    // ── Upload Cloudinary ──
    async function uploadToCloudinary(file) {
      const formData = new FormData();
      formData.append('file', file);
      formData.append('upload_preset', 'pokelu_storage');

      const endpoint = file.type.startsWith('image/')
        ? 'https://api.cloudinary.com/v1_1/dsz8bojjy/image/upload'
        : 'https://api.cloudinary.com/v1_1/dsz8bojjy/auto/upload';

      return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.upload.addEventListener('progress', e => {
          if (e.lengthComputable) {
            const pct = (e.loaded / e.total) * 100;
            document.getElementById('uploadProgressFill').style.width = pct + '%';
            document.getElementById('uploadProgressText').textContent  = Math.round(pct) + '%';
          }
        });
        xhr.addEventListener('load', () => {
          if (xhr.status === 200) {
            const res = JSON.parse(xhr.responseText);
            res?.secure_url ? resolve(res.secure_url) : reject(new Error('Upload tidak menghasilkan URL'));
          } else {
            reject(new Error(`Upload failed: ${xhr.status} ${xhr.statusText}`));
          }
        });
        xhr.addEventListener('error', () => reject(new Error('Network error')));
        xhr.open('POST', endpoint);
        xhr.send(formData);
      });
    }

    function showUploadProgress(show) {
      const progress = document.getElementById('uploadProgress');
      if (show) {
        progress.classList.add('active');
        document.getElementById('uploadProgressFill').style.width = '0%';
        document.getElementById('uploadProgressText').textContent  = '0%';
      } else {
        progress.classList.remove('active');
      }
    }

    // ── File preview ──
    document.getElementById('fileInput').addEventListener('change', function () {
      if (!this.files.length) return;
      const file      = this.files[0];
      const wrapper   = document.getElementById('filePreview');
      const imageWrap = document.getElementById('filePreviewImageWrap');
      const image     = document.getElementById('filePreviewImage');

      document.getElementById('filePreviewName').textContent =
        file.name.length > 30 ? file.name.slice(0, 27) + '...' : file.name;
      document.getElementById('filePreviewSize').textContent =
        (file.size / 1024 / 1024).toFixed(2) + ' MB';

      wrapper.classList.add('active');

      if (file.type.startsWith('image/')) {
        imageWrap.style.display = 'block';
        const reader = new FileReader();
        reader.onload = e => { image.src = e.target.result; };
        reader.readAsDataURL(file);
      } else {
        imageWrap.style.display = 'none';
      }
    });

    function clearFilePreview() {
      document.getElementById('fileInput').value = '';
      document.getElementById('filePreview').classList.remove('active');
    }

    // ── Textarea auto-resize & Typing indicator state ──
    function autoResizeTextarea(el) {
      el.style.height = 'auto';
      el.style.height = Math.min(el.scrollHeight, 120) + 'px';
    }

    function clearMyTyping() {
      if (currentUser && activeRoomId) {
        db.ref(`chats/${activeRoomId}/typing/${currentUser.uid}`).remove();
      }
    }

    const chatInput = document.getElementById('chatInput');
    chatInput.addEventListener('input', () => {
      autoResizeTextarea(chatInput);
      document.getElementById('btnSend').classList.toggle('active', !!chatInput.value.trim());

      if (!currentUser || !activeRoomId) return;

      if (!chatInput.value.trim()) {
        clearMyTyping();
        return;
      }

      db.ref(`chats/${activeRoomId}/typing/${currentUser.uid}`).set({
        username: currentUser.displayName || currentUser.email || 'User',
        isTyping: true,
        updatedAt: firebase.database.ServerValue.TIMESTAMP
      });

      clearTimeout(typingTimeout);
      typingTimeout = setTimeout(clearMyTyping, 2000);
    });

    chatInput.addEventListener('keydown', e => {
      if (e.key === 'Enter' && !e.shiftKey) { 
        sendMessage(); 
        e.preventDefault(); 
      }
    });

    // ── Helpers ──
    function escHtml(str) {
      if (str === null || str === undefined) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    function escAttr(str) {
      if (str === null || str === undefined) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/'/g, '&#39;')
        .replace(/"/g, '&quot;');
    }
  </script>
@endpush