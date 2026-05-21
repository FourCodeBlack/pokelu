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

    {{-- Transaction Offer Picker --}}
    <section id="offerPicker" class="offer-picker hidden">
        <div class="offer-picker-head">
            <div>
                <h3>Penawaran Dipilih</h3>
                <p>Kirim penawaran ini ke chat.</p>
            </div>

            <button type="button" id="closeOfferPickerBtn" class="close-offer-picker">
                ×
            </button>
        </div>

        <div id="offerPickerList" class="offer-picker-list">
            <div class="offer-picker-loading">Memuat penawaran...</div>
        </div>
    </section>

    {{-- Seller Review Box --}}
    <div id="sellerReviewBox" class="seller-review-box hidden" style="display:none; margin: 10px 20px 16px 20px; padding: 18px;">
        <h3>Beri Reputasi Seller</h3>
        <p>Nilai pengalaman transaksi kamu.</p>

        <select id="sellerRatingInput" style="margin-top: 10px;">
            <option value="5">5 - Sangat baik</option>
            <option value="4">4 - Baik</option>
            <option value="3">3 - Cukup</option>
            <option value="2">2 - Buruk</option>
            <option value="1">1 - Sangat buruk</option>
        </select>

        <textarea id="sellerReviewComment" placeholder="Komentar opsional" style="margin-top: 10px;"></textarea>

        <button type="button" id="submitSellerReviewBtn" class="send-offer-btn" style="margin-top: 14px; background: linear-gradient(135deg, #22c55e, #16a34a);">
            Kirim Review
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

          <button type="button" id="openOfferPickerBtn" class="open-offer-btn" title="Kirim Penawaran">
            Kartu
          </button>

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
    const FIREBASE_TOKEN = '{{ $firebaseToken ?? '' }}';
    let isAuthenticatingWithToken = !!FIREBASE_TOKEN;

    if (FIREBASE_TOKEN) {
      auth.signInWithCustomToken(FIREBASE_TOKEN)
        .then(() => {
          console.log('Firebase authenticated successfully via custom token');
          isAuthenticatingWithToken = false;
        })
        .catch(err => {
          console.error('Firebase custom token authentication failed:', err);
          isAuthenticatingWithToken = false;
          if (!auth.currentUser) {
            window.location.href = '{{ route("login") }}?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
          }
        });
    }

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
    const params          = new URLSearchParams(window.location.search);
    const initRoom        = params.get('room');
    const initSeller      = params.get('sellerId');
    const initName        = decodeURIComponent(params.get('sellerName') || '');
    const initCard        = params.get('cardId');
    const selectedOfferId = params.get('offerId');

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
        if (isAuthenticatingWithToken) {
          // Sedang login dengan custom token, tunggu
          return;
        }
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
        if (initSeller === user.uid) {
          console.warn('[CHAT] Self-to-self room detected — skipping. Check sellerId in URL.');
        } else {
          // Fetch seller name from Firebase — initName from URL is often empty
          // because card-detail does not pass sellerName param.
          const sellerName = await fetchDisplayName(initSeller);
          console.log('CHAT INIT', {
            roomId:     initRoom,
            currentUid: user.uid,
            sellerId:   initSeller,
            sellerName,
            cardId:     initCard,
            offerId:    selectedOfferId,
            databaseURL: firebase.app().options.databaseURL
          });
          openRoom(initRoom, sellerName || 'Penjual', initSeller);
        }
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

    // ── fetchCardName: get Pokémon TCG card name with cache ──
    const cardNameCache = {};
    async function fetchCardName(cardId) {
      if (!cardId) return cardId || '—';
      if (cardNameCache[cardId] !== undefined) return cardNameCache[cardId];
      try {
        const res = await fetch(`https://api.pokemontcg.io/v2/cards/${encodeURIComponent(cardId)}`, {
          headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        cardNameCache[cardId] = data?.data?.name || cardId;
      } catch (e) {
        cardNameCache[cardId] = cardId; // fallback to cardId code
      }
      return cardNameCache[cardId];
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

      // Guard: never open a room with yourself
      if (currentUser.uid === partnerId) {
        console.warn('[createOrOpenRoom] Self-to-self chat detected. Aborting.', { uid: currentUser.uid, partnerId });
        return;
      }

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

    // Global active room info variables
    let activeCardId = null;
    let activeBuyerId = null;
    let activeSellerId = null;
    let activeRoomStatus = null;
    let activeRoomMetaOff = null;

    // ── Message listener de-dupe flag ──
    let messagesListenerAttached = false;

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
      if (activeRoomMetaOff) { activeRoomMetaOff(); activeRoomMetaOff = null; }
      
      // Reset typing state
      clearMyTyping();

      // Fetch or initialize room metadata!
      const roomRef = db.ref(`chats/${roomId}`);
      const metaHandler = roomRef.on('value', async snap => {
        let roomData = snap.val();
        
        if (!roomData) {
          // Initialize room metadata!
          const buyerId = currentUser.uid;
          const sellerId = partnerId;
          const cardId = initCard || '';
          
          roomData = {
              cardId: cardId,
              buyerId: buyerId,
              sellerId: sellerId,
              participants: {
                  [buyerId]: true,
                  [sellerId]: true
              },
              status: 'active',
              createdAt: firebase.database.ServerValue.TIMESTAMP,
              updatedAt: firebase.database.ServerValue.TIMESTAMP
          };
          await roomRef.set(roomData);
        } else {
          // Check if updates are needed to ensure complete fields
          const updates = {};
          let needsUpdate = false;
          
          if (!roomData.buyerId) {
              roomData.buyerId = currentUser.uid;
              updates.buyerId = currentUser.uid;
              needsUpdate = true;
          }
          if (!roomData.sellerId) {
              roomData.sellerId = partnerId;
              updates.sellerId = partnerId;
              needsUpdate = true;
          }
          if (!roomData.cardId && initCard) {
              roomData.cardId = initCard;
              updates.cardId = initCard;
              needsUpdate = true;
          }
          if (!roomData.participants) {
              roomData.participants = {
                  [currentUser.uid]: true,
                  [partnerId]: true
              };
              updates.participants = roomData.participants;
              needsUpdate = true;
          }
          if (!roomData.status) {
              roomData.status = 'active';
              updates.status = 'active';
              needsUpdate = true;
          }
          
          if (needsUpdate) {
              await roomRef.update(updates);
          }
        }

        activeCardId = roomData.cardId || null;
        activeBuyerId = roomData.buyerId || null;
        activeSellerId = roomData.sellerId || null;
        activeRoomStatus = roomData.status || null;
        
        // Show/hide components based on room metadata and status
        updateRoomUI();
      });

      activeRoomMetaOff = () => roomRef.off('value', metaHandler);

      const wrap = document.getElementById('chatMessages');
      wrap.innerHTML = '';
      messagesListenerAttached = false;

      const msgsRef = db.ref(`chats/${roomId}/messages`).orderByChild('createdAt').limitToLast(100);
      console.log('LISTENING PATH:', `chats/${roomId}/messages`);

      // ── child_added: render each message once ──
      const addedHandler = msgsRef.on('child_added', async snapshot => {
        const msgId = snapshot.key;
        const msgData = snapshot.val();

        console.log('[child_added] MSG:', msgId, 'type:', msgData?.type, msgData);

        // Skip if already rendered (safety guard)
        if (document.getElementById(`msg-${msgId}`)) {
          console.warn('[child_added] Skipping duplicate:', msgId);
          return;
        }

        const msg = { id: msgId, ...msgData };
        if (msg.deletedFor && currentUser && msg.deletedFor[currentUser.uid]) return;

        // Pre-fetch pfp and name for this sender
        const uid = msg.senderId || msg.uid;
        if (uid) {
          await Promise.all([fetchPfp(uid), fetchDisplayName(uid)]);
        }

        console.log('[child_added] Rendering type:', msg.type, 'id:', msgId);
        appendMessage(wrap, msg, partnerId);
        wrap.scrollTop = wrap.scrollHeight;
      });

      // ── child_changed: replace existing card in-place ──
      const changeHandler = msgsRef.on('child_changed', async snapshot => {
        const msgId = snapshot.key;
        const msg = { id: msgId, ...snapshot.val() };

        // Pre-fetch pfp/name in case they're missing
        const uid = msg.senderId || msg.uid;
        if (uid) {
          await Promise.all([fetchPfp(uid), fetchDisplayName(uid)]);
        }

        const oldEl = document.getElementById(`msg-${msgId}`);
        if (oldEl) {
          const tempDiv = document.createElement('div');
          appendMessage(tempDiv, msg, partnerId);
          if (tempDiv.firstElementChild) {
            oldEl.replaceWith(tempDiv.firstElementChild);
          }
        }
      });

      // ── child_removed: remove from DOM ──
      const removedHandler = msgsRef.on('child_removed', snapshot => {
        const el = document.getElementById(`msg-${snapshot.key}`);
        if (el) el.remove();
      });

      messagesListenerAttached = true;

      activeMsgOff = () => {
        msgsRef.off('child_added', addedHandler);
        msgsRef.off('child_changed', changeHandler);
        msgsRef.off('child_removed', removedHandler);
        messagesListenerAttached = false;
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
      if (msg.type === 'system') {
        const el = document.createElement('div');
        el.className = 'msg-row system';
        el.style.display = 'flex';
        el.style.justifyContent = 'center';
        el.style.width = '100%';
        el.style.margin = '10px 0';
        el.innerHTML = `
          <div style="background: rgba(168, 85, 247, 0.12); border: 1px solid rgba(168, 85, 247, 0.28); border-radius: 999px; padding: 6px 18px; font-size: 0.8rem; font-weight: 800; color: #c4b5fd; text-shadow: 0 0 8px rgba(168,85,247,0.3);">
            ${escHtml(msg.text)}
          </div>
        `;
        container.appendChild(el);
        return;
      }

      const senderId = msg.senderId || msg.uid;
      const isSelf    = senderId === currentUser?.uid;
      const pfp       = pfpCache[senderId] || 'default';
      const avatarUrl = `/images/avatar/${pfp}.png`;
      const displayName = nameCache[senderId] || 'Anonymous';
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
            <div style="color:var(--pink);margin-bottom:2px;">@${escHtml(nameCache[msg.replyTo.uid] || 'Anonymous')}</div>
            <div style="color:var(--muted);opacity:0.8;">${escHtml(replyText)}</div>
          </div>
        `;
      }

      if (msg.deletedForAll) {
        contentHtml = `<div class="msg-bubble deleted" style="opacity:0.5;font-style:italic;font-size:0.75rem;">🚫 Pesan telah dihapus</div>`;
      } else if (msg.type === 'offer' || (msg.offer && msg.type !== 'nego')) {
        console.log('[appendMessage] Rendering OFFER card', msg.id, msg.offer);
        const offer  = msg.offer || {};
        const cardId = offer.cardId || msg.cardId || activeCardId || initCard || '';
        const status = offer.status || 'pending';
        const isSeller = currentUser?.uid === activeSellerId;
        const isBuyer  = currentUser?.uid === activeBuyerId;

        const canSellerAction = isSeller && status === 'pending';
        const canNego    = status !== 'done' && status !== 'rejected' && status !== 'cancelled';
        const canComplete = (isBuyer || isSeller) && (status === 'accepted' || status === 'negotiating');

        // Build card image URL from cardId (e.g. sv2-218 → https://images.pokemontcg.io/sv2/218.png)
        const cardImgUrl = (function(cid) {
          if (!cid) return '';
          const parts = cid.split('-');
          if (parts.length >= 2) {
            const setId  = parts[0];
            const number = parts.slice(1).join('-');
            return `https://images.pokemontcg.io/${setId}/${number}.png`;
          }
          return '';
        })(cardId);

        let actionButtonsHtml = '';
        if (status === 'done') {
          actionButtonsHtml = `<div class="transaction-done-state" style="color:#22c55e;font-weight:800;font-size:0.85rem;padding:4px 0;">✓ Transaksi Selesai</div>`;
        } else if (status === 'rejected') {
          actionButtonsHtml = `<div class="transaction-rejected-state" style="color:#ef4444;font-weight:800;font-size:0.85rem;padding:4px 0;">✗ Transaksi Ditolak</div>`;
        } else {
          if (canSellerAction) {
            actionButtonsHtml += `<button type="button" class="offer-accept-btn" data-message-id="${msg.id}">Terima</button>`;
            actionButtonsHtml += `<button type="button" class="offer-reject-btn" data-message-id="${msg.id}">Tolak</button>`;
          }
          if (canNego) {
            actionButtonsHtml += `<button type="button" class="offer-nego-btn" data-message-id="${msg.id}">Nego</button>`;
          }
          if (canComplete || status === 'accepted') {
            actionButtonsHtml += `<button type="button" class="offer-done-btn" data-message-id="${msg.id}">Selesai</button>`;
          }
        }

        const cardNameElId = `card-name-${msg.id}`;

        contentHtml += `
          <div class="transaction-offer-card" data-message-id="${msg.id}">

              <div class="offer-card-preview">
                  ${cardImgUrl ? `<img class="offer-card-img" src="${escHtml(cardImgUrl)}" alt="Card" onerror="this.style.display='none'">` : ''}
                  <div class="offer-card-info">
                      <span class="offer-card-code">${escHtml(cardId || '—')}</span>
                      <span class="offer-card-name" id="${cardNameElId}">Memuat...</span>
                  </div>
              </div>

              <div class="transaction-offer-top">
                  <div>
                      <span class="transaction-label">Offer Kartu</span>
                      <h4>Penawaran Baru</h4>
                  </div>
                  <strong class="transaction-price">$${escHtml(String(offer.price || 0))}</strong>
              </div>

              <div class="transaction-meta">
                  <span>Kondisi: ${escHtml(offer.condition || '-')}</span>
                  <span class="status-${escHtml(status)}">${escHtml(status.toUpperCase())}</span>
              </div>

              ${(offer.note || offer.desc || offer.message) ? `<p class="transaction-note">${escHtml(offer.note || offer.desc || offer.message)}</p>` : ''}

              <div class="transaction-actions">${actionButtonsHtml}</div>
          </div>
        `;

        // Async-fetch card name and update DOM
        if (cardId) {
          fetchCardName(cardId).then(name => {
            const el = document.getElementById(cardNameElId);
            if (el) el.textContent = name;
          });
        }
      } else if (msg.type === 'nego' || msg.nego) {
        const nego = msg.nego || {};
        const isMineNego = msg.senderId === currentUser?.uid || msg.uid === currentUser?.uid;
        const status = nego.status || 'pending';

        let negoActions = '';
        if (status === 'deal') {
            negoActions = `<div class="transaction-done-state" style="color:#22c55e;font-weight:800;font-size:0.85rem;padding:4px 0;">✓ Deal Disetujui</div>`;
        } else if (!isMineNego && status === 'pending') {
            negoActions = `<button type="button" class="nego-deal-btn" data-message-id="${msg.id}">Deal</button>`;
        }

        contentHtml += `
          <div class="transaction-nego-card" data-message-id="${msg.id}">
              <div class="transaction-offer-top">
                  <div>
                      <span class="transaction-label">Counter Offer</span>
                      <h4>Nego Baru</h4>
                  </div>
                  <strong class="transaction-price">
                      $${escHtml(String(nego.price || 0))}
                  </strong>
              </div>
              <p class="transaction-note">${isMineNego ? 'Anda mengajukan harga counter.' : 'Lawan bicara mengajukan harga counter.'}</p>
              <div class="transaction-actions">${negoActions}</div>
          </div>
        `;
      } else if (msg.type === 'system') {
        contentHtml += `
          <div class="msg-bubble system" style="background:transparent;text-align:center;font-size:0.75rem;color:var(--muted);width:100%;margin:10px 0;">
             ${escHtml(msg.text || '')}
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

    // ── sendMessage (text / file only — offers go via sendOfferMessage) ──
    async function sendMessage() {
      if (!currentUser || !activeRoomId) return;
      const input     = document.getElementById('chatInput');
      const text      = input.value.trim();
      const fileInput = document.getElementById('fileInput');

      // Guard: refuse to send with no room or no user
      if (!text && !fileInput.files.length) return;
      console.log('SEND MESSAGE TO PATH:', `chats/${activeRoomId}/messages`);

      input.value = '';
      autoResizeTextarea(input);
      document.getElementById('btnSend').classList.remove('active');

      const receiverId = activePartnerId || initSeller;

      let messageData = removeUndefined({
        uid:        currentUser.uid,
        senderId:   currentUser.uid,
        receiverId: receiverId,
        text:       text || '',
        type:       'text',
        createdAt:  firebase.database.ServerValue.TIMESTAMP,
      });

      if (replyTo) {
        messageData.replyTo = removeUndefined({
          id:       replyTo.id,
          uid:      replyTo.uid,
          text:     replyTo.text || '',
          imageUrl: replyTo.imageUrl || null,
        });
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

        await updateRoomLastMsg(preview);
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
        await updateRoomLastMsg('Pesan telah dihapus');
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
        // Push a nego-type message (no displayName/photoURL)
        const receiverId = currentUser.uid === activeSellerId
          ? (activeBuyerId || activePartnerId)
          : (activeSellerId || activePartnerId);

        await db.ref(`chats/${activeRoomId}/messages`).push(removeUndefined({
          type:       'nego',
          uid:        currentUser.uid,
          senderId:   currentUser.uid,
          receiverId: receiverId,
          cardId:     activeCardId || initCard,
          createdAt:  firebase.database.ServerValue.TIMESTAMP,
          nego: {
            relatedOfferId: currentNegoOffer.id,
            price:          counterPrice,
            note:           counterMessage || null,
            status:         'pending'
          }
        }));

        // Update the original offer status to 'negotiating'
        await db.ref(`chats/${activeRoomId}/messages/${currentNegoOffer.id}/offer`)
          .update({ status: 'negotiating' });

        await updateRoomLastMsg(`Nego: $${counterPrice}`);
        closeNegoModal();
      } catch (e) {
        console.error('submitNego error:', e);
        alert('Gagal mengirim counter offer.');
      }
    }

    // ── ensureRoomParticipants ──
    // Must be called before any user_rooms write.
    // The new Firebase rule requires both auth.uid AND $uid to be
    // present in chats/{roomId}/participants for the write to succeed.
    async function ensureRoomParticipants() {
      if (!activeRoomId || !currentUser) return;
      const partnerId = activePartnerId || initSeller || '';
      if (!partnerId || partnerId === currentUser.uid) return;
      try {
        await db.ref(`chats/${activeRoomId}/participants`).update({
          [currentUser.uid]: true,
          [partnerId]:       true,
        });
      } catch (e) {
        console.warn('[ensureRoomParticipants] failed:', e.message);
      }
    }

    async function updateRoomLastMsg(previewText) {
      if (!activeRoomId || !currentUser) return;
      const ts        = Date.now();
      const partnerId = activePartnerId || initSeller || '';

      console.log('[updateRoomLastMsg] debug:', {
        roomId:     activeRoomId,
        myUid:      currentUser.uid,
        partnerUid: partnerId,
      });

      if (!partnerId || partnerId === currentUser.uid) return;

      // Step 1: ensure participants exist so the rule passes
      await ensureRoomParticipants();

      // Step 2: resolve display info (never store Google photoURL)
      const myName       = nameCache[currentUser.uid]
                           || (await fetchDisplayName(currentUser.uid));
      const myAvatar     = pfpCache[currentUser.uid]
                           || (await fetchPfp(currentUser.uid));
      const partnerName  = (knownRooms[activeRoomId] && knownRooms[activeRoomId].name)
                           || (await fetchDisplayName(partnerId))
                           || 'User';
      const partnerAvatar = (knownRooms[activeRoomId] && knownRooms[activeRoomId].avatar)
                           || (await fetchPfp(partnerId))
                           || 'default';

      // Step 3: atomic multi-path update — both sides in one request
      const updates = {
        [`user_rooms/${currentUser.uid}/${activeRoomId}`]: {
          name:      partnerName,
          avatar:    partnerAvatar,
          lastMsg:   previewText,
          lastTs:    ts,
          partnerId: partnerId,
        },
        [`user_rooms/${partnerId}/${activeRoomId}`]: {
          name:      myName,
          avatar:    myAvatar || 'default',
          lastMsg:   previewText,
          lastTs:    ts,
          partnerId: currentUser.uid,
        },
      };

      try {
        await db.ref().update(updates);
        console.log('[updateRoomLastMsg] multi-path update success');
      } catch (e) {
        console.error('[updateRoomLastMsg] multi-path failed:', e.message);
        // Fallback: at least update sender's own side
        try {
          await db.ref(`user_rooms/${currentUser.uid}/${activeRoomId}`).update({
            lastMsg: previewText,
            lastTs:  ts,
          });
        } catch (e2) {
          console.warn('[updateRoomLastMsg] fallback also failed:', e2.message);
        }
      }
    }

    function updateRoomUI() {
      const openOfferBtn = document.getElementById('openOfferComposerBtn');
      if (openOfferBtn) {
        const isBuyer = (activeBuyerId === currentUser?.uid);
        const isActive = (activeRoomStatus === 'active');
        if (activeCardId && isActive && isBuyer) {
          openOfferBtn.style.display = 'block';
        } else {
          openOfferBtn.style.display = 'none';
        }
      }
      
      checkShowReviewBox();
    }

    async function checkShowReviewBox() {
      const box = document.getElementById('sellerReviewBox');
      if (!box) return;
      
      if (!currentUser || !activeRoomId) {
        box.style.display = 'none';
        return;
      }
      
      if (activeBuyerId !== currentUser.uid || activeRoomStatus !== 'done') {
        box.style.display = 'none';
        return;
      }
      
      try {
        const transSnap = await db.ref('transactions').orderByChild('roomId').equalTo(activeRoomId).once('value');
        let transactionId = null;
        
        transSnap.forEach(child => {
          const trans = child.val();
          if (trans.status === 'done' && trans.buyerId === currentUser.uid) {
            transactionId = child.key;
          }
        });
        
        if (transactionId) {
          const reviewSnap = await db.ref('reviews').orderByChild('transactionId').equalTo(transactionId).once('value');
          if (reviewSnap.exists()) {
            box.style.display = 'none';
          } else {
            box.style.display = 'block';
            box.classList.remove('hidden');
          }
        } else {
          box.style.display = 'none';
        }
      } catch (e) {
        console.error('Error checking review box status:', e);
        box.style.display = 'none';
      }
    }

    async function submitSellerReview() {
      if (!currentUser || !activeRoomId) return;
      
      try {
        const transSnap = await db.ref('transactions').orderByChild('roomId').equalTo(activeRoomId).once('value');
        let transactionId = null;
        let sellerId = null;
        
        transSnap.forEach(child => {
          const trans = child.val();
          if (trans.status === 'done' && trans.buyerId === currentUser.uid) {
            transactionId = child.key;
            sellerId = trans.sellerId;
          }
        });
        
        if (!transactionId || !sellerId) {
          alert('Transaksi selesai tidak ditemukan.');
          return;
        }
        
        if (sellerId === currentUser.uid) {
          alert('Anda tidak bisa memberikan review pada diri sendiri.');
          return;
        }

        const ratingInput = document.getElementById('sellerRatingInput');
        const commentInput = document.getElementById('sellerReviewComment');
        const rating = Number(ratingInput?.value || 5);
        const comment = commentInput?.value?.trim() || '';

        const reviewRef = db.ref('reviews').push();
        await reviewRef.set(removeUndefined({
            transactionId: transactionId,
            reviewerId: currentUser.uid,
            sellerId: sellerId,
            rating: rating,
            comment: comment,
            createdAt: firebase.database.ServerValue.TIMESTAMP
        }));

        alert('Terima kasih atas review Anda!');
        
        const box = document.getElementById('sellerReviewBox');
        if (box) {
          box.style.display = 'none';
          box.classList.add('hidden');
        }

        await recalculateSellerReputation(sellerId);

      } catch (error) {
        console.error('submitSellerReview error:', error);
        alert('Gagal mengirim review.');
      }
    }

    async function recalculateSellerReputation(sellerId) {
      if (!sellerId) return;
      
      try {
        const reviewsSnap = await db.ref('reviews').orderByChild('sellerId').equalTo(sellerId).once('value');
        let totalReviews = 0;
        let sumRating = 0;
        
        reviewsSnap.forEach(child => {
          const rev = child.val();
          totalReviews++;
          sumRating += Number(rev.rating || 0);
        });
        
        const averageRating = totalReviews > 0 ? (sumRating / totalReviews) : 0;
        
        const transSnap = await db.ref('transactions').orderByChild('sellerId').equalTo(sellerId).once('value');
        let totalTransactions = 0;
        
        transSnap.forEach(child => {
          const trans = child.val();
          if (trans.status === 'done') {
            totalTransactions++;
          }
        });
        
        let badge = 'New Seller';
        if (totalTransactions >= 20 && averageRating >= 4.7) {
          badge = 'Trusted Seller';
        } else if (totalTransactions >= 5 && averageRating >= 4.3) {
          badge = 'Good Seller';
        } else if (totalTransactions >= 1) {
          badge = 'Active Seller';
        }
        
        await db.ref(`users/${sellerId}`).update({
          rating: averageRating,
          totalReviews: totalReviews,
          totalTransactions: totalTransactions,
          sellerReputation: badge,
          updatedAt: firebase.database.ServerValue.TIMESTAMP
        });
        
        console.log(`Reputation updated for ${sellerId}: Rating = ${averageRating}, Reviews = ${totalReviews}, Transactions = ${totalTransactions}, Badge = ${badge}`);
      } catch (error) {
        console.error('Error recalculating seller reputation:', error);
      }
    }

    async function acceptOffer(messageId) {
      if (!confirm('Terima offer ini?')) return;
      try {
        await db.ref(`chats/${activeRoomId}/messages/${messageId}/offer`).update({
          status: 'accepted',
          acceptedAt: firebase.database.ServerValue.TIMESTAMP,
          acceptedBy: currentUser.uid
        });

        await db.ref(`chats/${activeRoomId}/currentOffer`).update({
          status: 'accepted',
          updatedAt: firebase.database.ServerValue.TIMESTAMP
        });

        await db.ref(`chats/${activeRoomId}/messages`).push(removeUndefined({
          type:      'system',
          senderId:  currentUser.uid,
          text:      'Offer diterima.',
          createdAt: firebase.database.ServerValue.TIMESTAMP
        }));

        await updateRoomLastMsg('Offer diterima');
      } catch (e) {
        console.error('acceptOffer error:', e);
        alert('Gagal menerima offer.');
      }
    }

    async function rejectOffer(messageId) {
      if (!confirm('Tolak offer ini?')) return;
      try {
        await db.ref(`chats/${activeRoomId}/messages/${messageId}/offer`).update({
          status: 'rejected',
          rejectedAt: firebase.database.ServerValue.TIMESTAMP,
          rejectedBy: currentUser.uid
        });

        await db.ref(`chats/${activeRoomId}/messages`).push(removeUndefined({
          type:      'system',
          senderId:  currentUser.uid,
          text:      'Offer ditolak.',
          createdAt: firebase.database.ServerValue.TIMESTAMP
        }));

        await updateRoomLastMsg('Offer ditolak');
      } catch (e) {
        console.error('rejectOffer error:', e);
        alert('Gagal menolak offer.');
      }
    }

    function escapeHTML(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function escapeAttr(value) {
        return escapeHTML(value);
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

    async function getUserProfile(uid) {
        if (!uid) {
            return { username: 'User', handle: '@user', pfp: 'default', role: 'user' };
        }
        if (window.userCache && window.userCache[uid]) {
            return window.userCache[uid];
        }
        window.userCache = window.userCache || {};
        const snapshot = await db.ref(`users/${uid}`).once('value');
        const user = snapshot.val() || {};
        const nameVal = user.name || 'User';
        const cleanHandle = nameVal.toLowerCase().replace(/\s+/g, '');
        const profile = {
            username: nameVal,
            handle: user.handle || `@${cleanHandle}`,
            pfp: user.pfp || 'default',
            role: user.role || 'user'
        };
        window.userCache[uid] = profile;
        return profile;
    }

    function formatOfferDate(timestamp) {
        if (!timestamp) return 'Tanggal tidak diketahui';
        const date = new Date(Number(timestamp));
        return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    // Helper to build Card Image URL
    function getCardImageUrl(cid) {
        if (!cid) return '';
        const parts = cid.split('-');
        if (parts.length >= 2) {
            const setId  = parts[0];
            const number = parts.slice(1).join('-');
            return `https://images.pokemontcg.io/${setId}/${number}.png`;
        }
        return '';
    }

    // ── Load selected offer untuk picker ──
    async function loadSelectedOfferForPicker() {
        const offerPickerList = document.getElementById('offerPickerList');
        const cardId = activeCardId || initCard;
        if (!cardId || !selectedOfferId) {
            offerPickerList.innerHTML = `
                <div class="offer-picker-empty">
                    Data penawaran tidak lengkap.
                </div>
            `;
            return;
        }

        offerPickerList.innerHTML = `
            <div class="offer-picker-loading">
                Memuat penawaran...
            </div>
        `;

        try {
            const offerSnap = await db.ref(`cards/${cardId}/offers/${selectedOfferId}`).once('value');
            const offer = offerSnap.val();

            if (!offer) {
                offerPickerList.innerHTML = `
                    <div class="offer-picker-empty">
                        Penawaran tidak ditemukan atau sudah dihapus.
                    </div>
                `;
                return;
            }

            const sellerId = activeSellerId || activePartnerId || initSeller;
            if (offer.uid !== sellerId) {
                console.error('Offer seller mismatch:', {
                    offerUid: offer.uid,
                    sellerId: sellerId
                });

                offerPickerList.innerHTML = `
                    <div class="offer-picker-empty">
                        Penawaran ini tidak cocok dengan seller.
                    </div>
                `;
                return;
            }

            const seller = await getUserProfile(offer.uid);
            renderSelectedOfferItem(selectedOfferId, offer, seller);

        } catch (error) {
            console.error('loadSelectedOfferForPicker error:', error);
            offerPickerList.innerHTML = `
                <div class="offer-picker-empty">
                    Gagal memuat penawaran.
                </div>
            `;
        }
    }

    function renderSelectedOfferItem(offerId, offer, seller) {
        const createdDate = formatOfferDate(offer.createdAt);
        const cardId = activeCardId || initCard;
        const cardImgUrl = getCardImageUrl(cardId);

        const offerPickerList = document.getElementById('offerPickerList');
        offerPickerList.innerHTML = '';

        const el = document.createElement('div');
        el.className = 'offer-picker-item single-offer';
        el.dataset.offerId = offerId;
        el.dataset.sellerUid = offer.uid || '';

        el.innerHTML = `
            <div class="offer-picker-card-left">
                ${cardImgUrl ? `<img class="offer-picker-card-img" src="${escapeAttr(cardImgUrl)}" alt="Card" onerror="this.style.display='none'">` : ''}
            </div>
            <div class="offer-picker-content-right">
                <div class="offer-picker-seller">
                    <img class="offer-picker-avatar"
                         src="/images/avatar/${escapeAttr(seller.pfp || 'default')}.png"
                         onerror="this.src='/images/avatar/default.png'"
                         alt="${escapeAttr(seller.username || 'User')}">

                    <div class="offer-picker-seller-info">
                        <strong>${escapeHTML(seller.username || 'User')}</strong>
                        <span>${escapeHTML(seller.handle || '@user')}</span>
                    </div>

                    <time class="offer-picker-date">
                        ${escapeHTML(createdDate)}
                    </time>
                </div>

                <div class="offer-picker-main">
                    <div class="offer-picker-price">
                        $${escapeHTML(String(offer.price || 0))}
                    </div>

                    <div class="offer-picker-detail">
                        <strong>${escapeHTML(offer.condition || 'Kondisi tidak diisi')}</strong>
                        <p>${escapeHTML(offer.desc || offer.message || 'Tidak ada deskripsi')}</p>
                    </div>
                </div>

                <div class="offer-picker-footer">
                    <button type="button" class="offer-picker-send-btn">
                        Kirim Penawaran Ini
                    </button>
                </div>
            </div>
        `;

        el.querySelector('.offer-picker-send-btn').addEventListener('click', function () {
            sendSelectedOfferToChat(offerId, offer);
        });

        offerPickerList.appendChild(el);
    }

    async function sendSelectedOfferToChat(offerId, offer) {
        try {
            if (!currentUser?.uid) {
                alert('Kamu harus login.');
                return;
            }

            const roomId   = activeRoomId || initRoom;
            const sellerId = activeSellerId || activePartnerId || initSeller;
            const buyerId  = activeBuyerId  || currentUser.uid;
            const cardId   = activeCardId || initCard;

            if (!roomId || !sellerId || !cardId || !selectedOfferId) {
                alert('Data chat tidak lengkap.');
                console.error({
                    roomId,
                    sellerId,
                    cardId,
                    selectedOfferId
                });
                return;
            }

            if (offerId !== selectedOfferId) {
                alert('Offer tidak valid.');
                return;
            }

            if (offer.uid !== sellerId) {
                alert('Offer ini bukan milik seller yang sedang kamu hubungi.');
                return;
            }

            const payload = removeUndefined({
                type: 'offer',
                senderId: currentUser.uid,
                receiverId: sellerId,
                cardId: cardId,
                createdAt: firebase.database.ServerValue.TIMESTAMP,
                offer: {
                    cardId: cardId,
                    offerId: selectedOfferId,
                    sellerUid: offer.uid,
                    price: Number(offer.price || 0),
                    condition: offer.condition || '',
                    desc: offer.desc || offer.message || '',
                    status: 'pending'
                }
            });

            console.log('SEND SELECTED OFFER:', payload);

            await db.ref(`chats/${roomId}/messages`).push(payload);

            await db.ref(`chats/${roomId}`).update(removeUndefined({
                cardId: cardId,
                buyerId: buyerId,
                sellerId: sellerId,
                status: 'active',
                currentOffer: {
                    cardId: cardId,
                    offerId: selectedOfferId,
                    sellerUid: offer.uid,
                    price: Number(offer.price || 0),
                    condition: offer.condition || '',
                    desc: offer.desc || offer.message || '',
                    status: 'pending'
                },
                lastMessage: 'Penawaran dikirim',
                lastMessageAt: firebase.database.ServerValue.TIMESTAMP,
                updatedAt: firebase.database.ServerValue.TIMESTAMP,
                [`participants/${currentUser.uid}`]: true,
                [`participants/${sellerId}`]: true
            }));

            // Sync local vars
            if (!activeBuyerId)  activeBuyerId  = buyerId;
            if (!activeSellerId) activeSellerId = sellerId;
            if (!activeCardId)   activeCardId   = cardId;

            const offerPicker = document.getElementById('offerPicker');
            if (offerPicker) {
                offerPicker.style.display = 'none';
                offerPicker.classList.add('hidden');
            }

            await updateRoomLastMsg('Penawaran baru diajukan');

        } catch (error) {
            console.error('sendSelectedOfferToChat error:', error);
            alert('Gagal mengirim penawaran.');
        }
    }

    // ── Fungsi nego ──
    async function negotiateOffer(messageId) {
        const newPrice = prompt('Masukkan harga nego:');

        if (!newPrice) return;

        const price = Number(newPrice);

        if (!price || price <= 0) {
            alert('Harga nego tidak valid.');
            return;
        }
        
        const sellerId = activeSellerId || activePartnerId || initSeller;
        const buyerId  = activeBuyerId  || currentUser.uid;

        const payload = removeUndefined({
            type: 'nego',
            senderId: currentUser.uid,
            receiverId: currentUser.uid === sellerId ? buyerId : sellerId,
            cardId: activeCardId || initCard,
            createdAt: firebase.database.ServerValue.TIMESTAMP,
            nego: {
                relatedOfferMessageId: messageId,
                price: price,
                status: 'pending'
            }
        });

        await db.ref(`chats/${activeRoomId}/messages`).push(payload);

        await db.ref(`chats/${activeRoomId}/messages/${messageId}/offer/status`).set('negotiating');
        await db.ref(`chats/${activeRoomId}/currentOffer/status`).set('negotiating');
        await updateRoomLastMsg(`Nego: $${price}`);
    }

    // ── Deal Nego ──
    async function dealNego(messageId) {
        if (!confirm('Apakah kamu yakin menerima harga counter (deal)?')) return;
        
        const negoSnap = await db.ref(`chats/${activeRoomId}/messages/${messageId}/nego`).once('value');
        const nego = negoSnap.val();

        if (!nego) {
            alert('Data nego tidak ditemukan.');
            return;
        }

        const price = Number(nego.price || 0);

        if (!price || price <= 0) {
            alert('Harga nego tidak valid.');
            return;
        }

        await db.ref(`chats/${activeRoomId}/messages/${messageId}/nego`).update({
            status: 'deal',
            dealAt: firebase.database.ServerValue.TIMESTAMP,
            dealBy: currentUser.uid
        });

        await db.ref(`chats/${activeRoomId}/currentOffer`).update({
            price: price,
            status: 'accepted',
            updatedAt: firebase.database.ServerValue.TIMESTAMP
        });

        if (nego.relatedOfferMessageId) {
            await db.ref(`chats/${activeRoomId}/messages/${nego.relatedOfferMessageId}/offer`).update({
                price: price,
                status: 'accepted',
                updatedAt: firebase.database.ServerValue.TIMESTAMP
            });
        }

        await db.ref(`chats/${activeRoomId}/messages`).push(removeUndefined({
            type: 'system',
            senderId: currentUser.uid,
            text: `Deal di harga $${price}`,
            createdAt: firebase.database.ServerValue.TIMESTAMP
        }));
        
        await updateRoomLastMsg(`Deal: $${price}`);
    }

    // ── Selesai 2 Arah ──
    async function requestCompleteTransaction(messageId) {
        const isBuyer = currentUser.uid === activeBuyerId;
        const isSeller = currentUser.uid === activeSellerId;

        if (!isBuyer && !isSeller) {
            alert('Kamu bukan peserta transaksi.');
            return;
        }

        const update = {
            requested: true,
            requestedAt: firebase.database.ServerValue.TIMESTAMP,
            requestedBy: currentUser.uid
        };

        if (isBuyer) {
            update.buyerConfirmed = true;
        }

        if (isSeller) {
            update.sellerConfirmed = true;
        }

        await db.ref(`chats/${activeRoomId}/completion`).update(update);

        await db.ref(`chats/${activeRoomId}/messages`).push(removeUndefined({
            type: 'system',
            senderId: currentUser.uid,
            text: 'Salah satu pihak meminta transaksi diselesaikan. Menunggu konfirmasi pihak lain.',
            createdAt: firebase.database.ServerValue.TIMESTAMP
        }));

        await checkTransactionCompletion(messageId);
    }

    async function checkTransactionCompletion(messageId) {
        const completionSnap = await db.ref(`chats/${activeRoomId}/completion`).once('value');
        const completion = completionSnap.val() || {};

        if (!completion.buyerConfirmed || !completion.sellerConfirmed) {
            return; // Belum komplit
        }

        const currentOfferSnap = await db.ref(`chats/${activeRoomId}/currentOffer`).once('value');
        const currentOffer = currentOfferSnap.val() || {};

        const transactionRef = db.ref('transactions').push();

        await transactionRef.set(removeUndefined({
            roomId: activeRoomId,
            cardId: activeCardId || initCard,
            buyerId: activeBuyerId,
            sellerId: activeSellerId,
            finalPrice: Number(currentOffer.price || 0),
            condition: currentOffer.condition || '',
            status: 'done',
            createdAt: firebase.database.ServerValue.TIMESTAMP,
            completedAt: firebase.database.ServerValue.TIMESTAMP
        }));

        await db.ref(`chats/${activeRoomId}`).update({
            status: 'done',
            updatedAt: firebase.database.ServerValue.TIMESTAMP
        });

        await db.ref(`chats/${activeRoomId}/completion`).update({
            completedAt: firebase.database.ServerValue.TIMESTAMP,
            transactionId: transactionRef.key
        });
        
        if (messageId) {
            await db.ref(`chats/${activeRoomId}/messages/${messageId}/offer`).update({
                status: 'done',
                updatedAt: firebase.database.ServerValue.TIMESTAMP
            });
        }

        await db.ref(`chats/${activeRoomId}/messages`).push(removeUndefined({
            type: 'system',
            senderId: currentUser.uid,
            text: 'Transaksi selesai. Kedua pihak telah mengonfirmasi.',
            createdAt: firebase.database.ServerValue.TIMESTAMP
        }));
        
        await updateRoomLastMsg('Transaksi selesai');
    }

    // ── Event binding and delegated listeners ──
    (function bindOfferAndReviewButtons() {
        const offerPicker = document.getElementById('offerPicker');
        const openOfferPickerBtn = document.getElementById('openOfferPickerBtn');
        const closeOfferPickerBtn = document.getElementById('closeOfferPickerBtn');

        if (openOfferPickerBtn) {
            openOfferPickerBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                if (offerPicker) {
                    offerPicker.style.display = 'block';
                    offerPicker.classList.remove('hidden');
                    await loadSelectedOfferForPicker();
                }
            });
        }

        if (closeOfferPickerBtn) {
            closeOfferPickerBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (offerPicker) {
                    offerPicker.style.display = 'none';
                    offerPicker.classList.add('hidden');
                }
            });
        }

        const reviewBtn = document.getElementById('submitSellerReviewBtn');
        if (reviewBtn) {
            reviewBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                submitSellerReview();
            });
        }
    })();

    document.addEventListener('click', function(e) {
        const negoBtn = e.target.closest('.offer-nego-btn');
        const doneBtn = e.target.closest('.offer-done-btn');
        const dealBtn = e.target.closest('.nego-deal-btn');

        if (negoBtn) { e.preventDefault(); negotiateOffer(negoBtn.dataset.messageId); }
        if (doneBtn) { e.preventDefault(); requestCompleteTransaction(doneBtn.dataset.messageId); }
        if (dealBtn) { e.preventDefault(); dealNego(dealBtn.dataset.messageId); }
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