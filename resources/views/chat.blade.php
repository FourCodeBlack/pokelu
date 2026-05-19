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
    <div class="sidebar-search">
      <input type="text" id="userSearchInput" class="search-input" placeholder="Cari user..." autocomplete="off" />
      <div class="search-result-count" id="searchResultCount">Tampilkan semua user</div>
      <div class="search-hint">Ketik nama atau UID</div>
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

    {{-- Discord-style Input --}}
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

  {{-- Firebase SDK --}}
  <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-database-compat.js"></script>

  <script>
    // ── Firebase init ──
    if (!firebase.apps.length) {
      firebase.initializeApp({
        apiKey: 'AIzaSyDr7r6PrzHWUX6i_5bMnL9I7eZMcx2AS_s',
        authDomain: "pokelu-project.firebaseapp.com",
        databaseURL: "https://pokelu-project-default-rtdb.asia-southeast1.firebasedatabase.app",
        projectId: "pokelu-project",
        storageBucket: "pokelu-project.firebasestorage.app",
        messagingSenderId: "210207641471",
        appId: "1:210207641471:web:aff0df7b7b3acb0ce2d44a"
      });
    }

    const auth = firebase.auth();
    const db = firebase.database();
    const FB_TS = firebase.database.ServerValue.TIMESTAMP;

    // ── State ──
    let currentUser = null;
    let activeRoomId = null;
    let activeMsgOff = null;
    let allUsersRef = null;
    let userRoomsRef = null;
    let unloadHandler = null;

    let knownRooms = {};
    let allUsers = {};
    let searchResults = [];
    let isSearching = false;
    let searchTimer = null;
    const SEARCH_DEBOUNCE_MS = 200;

    let pfpCache = {};
    let userStatusOff = null;

    let replyTo = null;
    let attachedOffer = null;
    let myOffers = [];
    let partnerOffers = [];
    let currentNegoOffer = null;

    // ── URL params (dari btn-contact) ──
    const params = new URLSearchParams(window.location.search);
    const initRoom = params.get('room');
    const initSeller = params.get('sellerId');
    const initName = decodeURIComponent(params.get('sellerName') || '');
    const initCard = params.get('cardId');

    // ── Auth listener ──
    auth.onAuthStateChanged(user => {
      currentUser = user;
      if (!user) {
        window.location.href = '{{ route("login") }}?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
        return;
      }

      loadAllUsers();
      loadMyRooms();
      setupUserStatusListener();
      if (initCard) {
        loadOffersForCard(initCard);
      }

      if (initRoom && initSeller) {
        setTimeout(() => openRoom(initRoom, initName, initSeller), 800);
      }
    });

    async function fetchPfp(uid) {
      if (!uid) return 'default';
      if (pfpCache[uid]) return pfpCache[uid];

      try {
        const snap = await db.ref(`users/${uid}/pfp`).once('value');
        pfpCache[uid] = snap.val() || 'default';
      } catch (e) {
        console.error('fetchPfp error:', e);
        pfpCache[uid] = 'default';
      }

      return pfpCache[uid];
    }

    async function loadAllUsers() {
      if (allUsersRef) allUsersRef.off('value');

      allUsersRef = db.ref('users');
      allUsersRef.on('value', async snapshot => {
        allUsers = {};
        const data = snapshot.val() || {};

        Object.entries(data).forEach(([uid, userData]) => {
          if (!currentUser || uid === currentUser.uid) return;

          allUsers[uid] = {
            name: userData.name || 'Anonymous',
            email: userData.email || '',
            pfp: userData.pfp || 'default',
            status: userData.status || 'offline',
            ...userData
          };
        });

        await Promise.all(Object.keys(allUsers).map(uid => fetchPfp(uid)));

        if (isSearching) {
          renderSearchResults();
        } else {
          renderSidebar();
        }
      });
    }

    // Load offers dari card tertentu (baik milik user maupun partner)
    async function loadOffersForCard(cardId) {
      if (!currentUser) return;

      // Load offers milik current user
      const myOffersRef = db.ref(`cards/${cardId}/offers`).orderByChild('uid').equalTo(currentUser.uid);
      myOffersRef.on('value', snapshot => {
        const arr = [];
        snapshot.forEach(child => {
          arr.push({ id: child.key, ...child.val() });
        });
        myOffers = arr.reverse();
        renderOfferSelector();
      });

      // Load semua offers di card ini untuk deteksi partner offers
      const allOffersRef = db.ref(`cards/${cardId}/offers`);
      allOffersRef.on('value', snapshot => {
        const arr = [];
        snapshot.forEach(child => {
          const offer = { id: child.key, ...child.val() };
          if (offer.uid !== currentUser.uid) {
            arr.push(offer);
          }
        });
        partnerOffers = arr.reverse();
      });
    }

    function setupUserStatusListener() {
      if (!currentUser || !db) return;

      if (unloadHandler) {
        window.removeEventListener('beforeunload', unloadHandler);
        unloadHandler = null;
      }

      const userRef = db.ref(`users/${currentUser.uid}`);

      userRef.onDisconnect().update({
        status: 'offline',
        lastSeen: FB_TS
      });

      userRef.update({
        status: 'online',
        lastSeen: FB_TS
      });

      unloadHandler = () => {
        userRef.update({
          status: 'offline',
          lastSeen: Date.now()
        });
      };

      window.addEventListener('beforeunload', unloadHandler);
    }

    // ── Search user realtime ──
    const userSearchInput = document.getElementById('userSearchInput');
    userSearchInput.addEventListener('input', function () {
      const query = this.value.trim().toLowerCase();

      clearTimeout(searchTimer);
      searchTimer = setTimeout(() => {
        if (!query) {
          isSearching = false;
          renderSidebar();
          return;
        }

        isSearching = true;
        searchResults = [];

        Object.entries(allUsers).forEach(([uid, user]) => {
          const name = user.name.toLowerCase();
          const email = String(user.email || '').toLowerCase();

          if (
            name.includes(query) ||
            email.includes(query) ||
            uid.toLowerCase().includes(query)
          ) {
            searchResults.push({ uid, ...user });
          }
        });

        renderSearchResults();
      }, SEARCH_DEBOUNCE_MS);
    });

    function renderSearchResults() {
      const list = document.getElementById('contactList');
      const empty = document.getElementById('sidebarEmpty');
      const count = document.getElementById('searchResultCount');

      if (searchResults.length === 0) {
        empty.style.display = 'block';
        empty.textContent = 'Tidak ada user ditemukan';
        count.textContent = '';
        list.innerHTML = '';
        return;
      }

      empty.style.display = 'none';
      count.textContent = `${searchResults.length} user ditemukan`;
      list.innerHTML = '';

      searchResults.forEach(user => {
        const el = document.createElement('div');
        el.className = 'contact-item';
        el.dataset.uid = user.uid;
        el.onclick = () => createOrOpenRoom(user.uid, user.name, user.pfp);

        const avatarUrl = `/images/avatar/${user.pfp}.png`;
        const metaText = [user.email || user.uid].filter(Boolean).join(' • ');

        el.innerHTML = `
                    <div class="contact-avatar">
                      <img src="${avatarUrl}" alt="${escHtml(user.name)}" onerror="this.src='/images/avatar/default.png'">
                    </div>
                    <div class="contact-info">
                      <div class="contact-name">${escHtml(user.name.toUpperCase())}</div>
                      <div class="contact-last-msg">${escHtml(metaText)}</div>
                    </div>
                    <div style="font-size: 0.7rem; color: ${user.status === 'online' ? '#4ade80' : '#888'};">
                      ${user.status === 'online' ? '● ONLINE' : '● OFFLINE'}
                    </div>
                  `;
        list.appendChild(el);
      });
    }

    async function createOrOpenRoom(partnerId, partnerName, partnerPfp) {
      if (!currentUser) return;

      const uids = [currentUser.uid, partnerId].sort();
      const roomId = `${uids[0]}_${uids[1]}`;

      const roomExists = await db.ref(`chats/${roomId}`).once('value');

      if (!roomExists.exists()) {
        await db.ref(`chats/${roomId}`).set({
          createdAt: FB_TS,
          participants: {
            [currentUser.uid]: true,
            [partnerId]: true,
          },
          messages: {}
        });

        const myName = currentUser.displayName || currentUser.email || 'Anonymous';
        const myPfp = await fetchPfp(currentUser.uid);

        await db.ref(`user_rooms/${currentUser.uid}/${roomId}`).set({
          name: partnerName,
          avatar: partnerPfp,
          lastMsg: '',
          lastTs: Date.now(),
          partnerId: partnerId,
        });

        knownRooms[roomId] = {
          name: partnerName,
          avatar: partnerPfp,
          lastMsg: '',
          lastTs: Date.now(),
          partnerId: partnerId,
        };
      }

      document.getElementById('userSearchInput').value = '';
      isSearching = false;
      renderSidebar();

      openRoom(roomId, partnerName, partnerId);
    }

    async function loadMyRooms() {
      if (userRoomsRef) userRoomsRef.off('value');

      userRoomsRef = db.ref(`user_rooms/${currentUser.uid}`);
      userRoomsRef.on('value', async snapshot => {
        const data = snapshot.val() || {};
        knownRooms = data;

        const uids = Object.values(data).map(r => r.partnerId).filter(Boolean);
        if (initSeller) uids.push(initSeller);
        await Promise.all(uids.map(uid => fetchPfp(uid)));

        if (!isSearching) {
          renderSidebar();
        }

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

    function toggleOfferSelector() {
      const panel = document.getElementById('offerSelector');
      if (!panel) return;
      const isActive = panel.classList.contains('active');
      panel.classList.toggle('active', !isActive);
      if (!isActive) {
        renderOfferSelector();
      }
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
        const isMine = offer.uid === currentUser.uid;
        const ownerLabel = isMine ? '(Penawaran Anda)' : `(dari ${escHtml(offer.displayName || 'User')})`;

        return `
                    <div class="offer-item" onclick='selectAttachedOffer(${JSON.stringify(offer)})'>
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

      if (!attachedOffer) {
        preview.classList.remove('active');
        summary.innerHTML = '';
        return;
      }

      preview.classList.add('active');
      const isMine = attachedOffer.uid === currentUser.uid;
      const ownerLabel = isMine ? 'Penawaran Anda' : `Penawaran dari ${escHtml(attachedOffer.displayName || 'User')}`;

      summary.innerHTML = `
                  <div style="font-size: 0.8rem; color: var(--text);">
                    ${escHtml(attachedOffer.desc)} - <strong>$${escHtml(String(attachedOffer.price))}</strong>
                  </div>
                  <div style="font-size: 0.7rem; color: var(--muted); margin-top: 2px;">
                    ${ownerLabel}
                  </div>
                `;
    }

    function clearAttachedOffer() {
      attachedOffer = null;
      const preview = document.getElementById('offerAttachmentPreview');
      if (preview) preview.classList.remove('active');
      const panel = document.getElementById('offerSelector');
      if (panel) panel.classList.remove('active');
    }

    function prepareReply(msg) {
      if (!msg || !msg.id) return;

      replyTo = {
        id: msg.id,
        uid: msg.uid || null,
        displayName: msg.displayName || 'Anonymous',
        text: msg.text || '',
        imageUrl: msg.imageUrl || null,
        fileUrl: msg.fileUrl || null,
        fileName: msg.fileName || null,
        deletedForAll: !!msg.deletedForAll
      };

      const preview = document.getElementById('replyPreview');
      const text = document.getElementById('replyPreviewText');
      const input = document.getElementById('chatInput');

      if (!preview || !text) return;

      preview.classList.add('active');

      if (replyTo.text) {
        text.textContent = replyTo.text;
      } else if (replyTo.imageUrl) {
        text.textContent = '📷 Foto';
      } else if (replyTo.fileUrl) {
        text.textContent = `📎 ${replyTo.fileName || 'File'}`;
      } else {
        text.textContent = 'Pesan';
      }

      if (input) input.focus();
    }

    function clearReplyPreview() {
      replyTo = null;

      const preview = document.getElementById('replyPreview');
      const text = document.getElementById('replyPreviewText');

      if (preview) {
        preview.classList.remove('active');
      }

      if (text) {
        text.textContent = '';
      }

      const input = document.getElementById('chatInput');
      if (input) input.focus();
    }

    async function deleteMessageForMe(msgId) {
      if (!currentUser || !activeRoomId || !msgId) return;

      try {
        // Kalau pesan yang sedang direply di-hide, tutup reply preview
        if (replyTo && String(replyTo.id) === String(msgId)) {
          clearReplyPreview();
        }

        await db
          .ref(`chats/${activeRoomId}/messages/${msgId}/deletedFor/${currentUser.uid}`)
          .set(true);

      } catch (e) {
        console.error('Failed delete message for me:', e);
        alert('Gagal menyembunyikan pesan.');
      }
    }

    async function deleteMessageForAll(msgId) {
      if (!confirm('Hapus pesan ini untuk semua orang?')) return;
      if (!currentUser || !activeRoomId || !msgId) return;
      try {
        const msgRef = db.ref(`chats/${activeRoomId}/messages/${msgId}`);
        const snap = await msgRef.once('value');
        const msg = snap.val();
        if (!msg) return;

        if (msg.uid !== currentUser.uid) {
          alert('Hanya bisa menghapus pesan sendiri.');
          return;
        }

        await msgRef.update({
          text: 'Pesan telah dihapus',
          imageUrl: null,
          fileUrl: null,
          fileName: null,
          type: 'text',
          deletedForAll: true,
        });

        if (replyTo && String(replyTo.id) === String(msgId)) {
          clearReplyPreview();
        }

        const roomUpdate = { lastMsg: 'Pesan telah dihapus' };
        db.ref(`user_rooms/${currentUser.uid}/${activeRoomId}`).update(roomUpdate);

      } catch (e) {
        console.error('Failed delete message for all:', e);
      }
    }

    function renderSidebar() {
      const list = document.getElementById('contactList');
      const empty = document.getElementById('sidebarEmpty');
      const count = document.getElementById('searchResultCount');

      if (isSearching) return;

      const rooms = Object.entries(knownRooms).sort((a, b) => (b[1].lastTs || 0) - (a[1].lastTs || 0));

      if (rooms.length === 0) {
        list.innerHTML = '';
        empty.style.display = 'block';
        empty.textContent = 'Belum ada percakapan. Ketik nama user untuk mulai chat.';
        count.textContent = 'Tampilkan semua user';
        return;
      }

      empty.style.display = 'none';
      count.textContent = 'Chat history';
      list.innerHTML = '';

      rooms.forEach(([rId, info]) => {
        renderContactItem(list, rId, info.name || 'Anonymous', info.partnerId, info.lastMsg || '');
      });
    }

    function renderContactItem(container, roomId, name, partnerId, lastMsg) {
      const existing = container.querySelector(`[data-room="${roomId}"]`);
      if (existing) {
        existing.querySelector('.contact-last-msg').textContent = lastMsg;
        return;
      }

      const pfp = pfpCache[partnerId] || 'default';
      const avatarUrl = `/images/avatar/${pfp}.png`;

      const el = document.createElement('div');
      el.className = 'contact-item' + (roomId === activeRoomId ? ' active' : '');
      el.dataset.room = roomId;
      el.onclick = () => openRoom(roomId, name, partnerId);

      el.innerHTML = `
                  <div class="contact-avatar">
                    <img src="${avatarUrl}" alt="${escHtml(name)}" onerror="this.src='/images/avatar/default.png'">
                  </div>
                  <div class="contact-info">
                    <div class="contact-name">${escHtml(name.toUpperCase())}</div>
                    <div class="contact-last-msg">${escHtml(lastMsg)}</div>
                  </div>
                `;
      container.appendChild(el);
    }

    async function openRoom(roomId, name, partnerId) {
      clearReplyPreview();
      clearAttachedOffer();

      document.querySelectorAll('.contact-item').forEach(i => i.classList.remove('active'));
      const contactEl = document.querySelector(`[data-room="${roomId}"]`);
      if (contactEl) contactEl.classList.add('active');

      activeRoomId = roomId;

      document.getElementById('chatEmptyState').style.display = 'none';
      document.getElementById('chatHeader').style.display = 'flex';
      document.getElementById('chatMessages').style.display = 'flex';
      document.getElementById('chatInputWrap').style.display = 'flex';

      const pfp = await fetchPfp(partnerId);
      const avatarUrl = `/images/avatar/${pfp}.png`;
      document.getElementById('headerName').textContent = name.toUpperCase();
      document.getElementById('headerAvatar').innerHTML =
        `<img src="${avatarUrl}" alt="${escHtml(name)}" style="width:100%;height:100%;object-fit:cover;border-radius:50%" onerror="this.src='/images/avatar/default.png'">`;

      document.getElementById('chatInput').focus();

      if (activeMsgOff) { activeMsgOff(); activeMsgOff = null; }

      const wrap = document.getElementById('chatMessages');
      wrap.innerHTML = '';

      const msgsRef = db.ref(`chats/${roomId}/messages`).orderByChild('createdAt').limitToLast(100);
      const handler = msgsRef.on('value', async snapshot => {
        wrap.innerHTML = '';
        let lastDate = null;
        const messages = [];

        snapshot.forEach(child => {
          const msg = { id: child.key, ...child.val() };
          if (msg.deletedFor && currentUser && msg.deletedFor[currentUser.uid]) return;
          messages.push(msg);
        });

        const uids = [...new Set(messages.map(m => m.uid).filter(Boolean))];
        await Promise.all(uids.map(uid => fetchPfp(uid)));

        for (const m of messages) {
          const msgDate = m.createdAt
            ? new Date(m.createdAt).toLocaleDateString('id-ID', {
              day: 'numeric',
              month: 'long',
              year: 'numeric'
            })
            : null;

          if (msgDate && msgDate !== lastDate) {
            appendDateSep(wrap, msgDate);
            lastDate = msgDate;
          }

          appendMessage(wrap, m, partnerId);
        }
        wrap.scrollTop = wrap.scrollHeight;
      });
      activeMsgOff = () => msgsRef.off('value', handler);
    }

    async function sendMessage() {
      if (!currentUser || !activeRoomId) return;
      const input = document.getElementById('chatInput');
      const text = input.value.trim();
      const fileInput = document.getElementById('fileInput');

      if (!text && !fileInput.files.length) return;

      input.value = '';
      autoResizeTextarea(input);

      const myPfp = await fetchPfp(currentUser.uid);
      let messageData = {
        uid: currentUser.uid,
        displayName: currentUser.displayName || currentUser.email || 'Anonymous',
        photoURL: myPfp,
        text: text || '',
        type: 'text',
        createdAt: Date.now(),
      };

      if (replyTo) {
        messageData.replyTo = {
          id: replyTo.id,
          uid: replyTo.uid,
          displayName: replyTo.displayName,
          text: replyTo.text || '',
          imageUrl: replyTo.imageUrl || null,
        };
      }

      if (attachedOffer) {
        messageData.offer = {
          offerId: attachedOffer.id,
          cardId: initCard,
          uid: attachedOffer.uid,
          displayName: attachedOffer.displayName,
          photoURL: attachedOffer.photoURL,
          price: attachedOffer.price,
          condition: attachedOffer.condition,
          desc: attachedOffer.desc,
          createdAt: attachedOffer.createdAt,
          status: 'pending',
          negotiations: []
        };

        if (!messageData.text) {
          messageData.text = `Menambahkan offer: ${attachedOffer.desc || attachedOffer.price || 'Penawaran'}`;
        }
      }

      if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        const isImage = file.type.startsWith('image/');

        const maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
          alert('File terlalu besar. Max 10MB.');
          return;
        }

        try {
          showUploadProgress(true);
          const uploadUrl = await uploadToCloudinary(file);

          if (isImage) {
            messageData.imageUrl = uploadUrl;
            messageData.type = 'image';
          } else {
            messageData.fileUrl = uploadUrl;
            messageData.fileName = file.name;
          }
        } catch (e) {
          console.error('Upload error:', e);
          alert('Gagal upload file. ' + e.message);
          showUploadProgress(false);
          return;
        }

        fileInput.value = '';
        showUploadProgress(false);
      }

      try {
        await db.ref(`chats/${activeRoomId}/messages`).push(messageData);
        clearReplyPreview();
        clearAttachedOffer();

        const preview = messageData.imageUrl ? '📸 Image' : (messageData.fileUrl ? '📎 ' + messageData.fileName : text);
        const updatePayload = { lastMsg: preview, lastTs: Date.now() };
        try {
          await db.ref(`user_rooms/${currentUser.uid}/${activeRoomId}`).update(updatePayload);
        } catch (e) {
          console.error('Failed updating own user_rooms preview:', e);
        }
      } catch (e) { console.error(e); }
    }

    function appendMessage(container, msg, partnerId) {
      const isSelf = msg.uid === currentUser?.uid;
      const pfp = pfpCache[msg.uid] || 'default';
      const avatarUrl = `/images/avatar/${pfp}.png`;
      const timeStr = msg.createdAt
        ? new Date(msg.createdAt).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
        : '';

      const row = document.createElement('div');
      row.id = `msg-${msg.id}`;
      row.className = `msg-row ${isSelf ? 'self' : 'other'}`;

      let contentHtml = '';
      let actionHtml = '';

      if (msg.replyTo) {
        contentHtml += `
                    <div class="msg-reply-preview" style="padding:6px 10px; background:rgba(255,255,255,0.05); border-left:2px solid var(--pink); border-radius:4px; margin-bottom:6px; font-size:0.7rem;">
                      <div style="color:var(--pink); margin-bottom:2px;">@${escHtml(msg.replyTo.displayName)}</div>
                      <div style="color:var(--muted); opacity:0.8;">${escHtml(msg.replyTo.text || (msg.replyTo.imageUrl ? '📷 Foto' : ''))}</div>
                    </div>
                  `;
      }

      if (msg.offer) {
        const offer = msg.offer;
        const isOfferOwner = offer.uid === currentUser.uid;
        const canInteract = !isSelf && !isOfferOwner;

        let statusBadge = '';
        const status = offer.status || 'pending';
        if (status === 'pending') statusBadge = '<span class="msg-offer-status pending">PENDING</span>';
        else if (status === 'accepted') statusBadge = '<span class="msg-offer-status accepted">ACCEPTED</span>';
        else if (status === 'rejected') statusBadge = '<span class="msg-offer-status rejected">REJECTED</span>';
        else if (status === 'negotiating') statusBadge = '<span class="msg-offer-status negotiating">NEGOTIATING</span>';

        let actionButtons = '';
        if (canInteract && (status === 'pending' || status === 'negotiating')) {
          actionButtons = `
                      <div class="msg-offer-actions">
                        <button class="btn-offer-action nego" onclick='openNegoModal(${JSON.stringify(msg)})'>💬 Nego</button>
                        <button class="btn-offer-action accept" onclick='acceptOffer(${JSON.stringify(msg)})'>✓ Gas</button>
                      </div>
                    `;
        }

        contentHtml += `
                    <div class="msg-offer-card">
                      <div class="msg-offer-header">
                        <span class="msg-offer-badge">OFFER</span>
                        ${statusBadge}
                      </div>
                      <div class="msg-offer-desc">${escHtml(offer.desc)}</div>
                      <div class="msg-offer-price-row">
                        <div class="msg-offer-price">$${escHtml(String(offer.price))}</div>
                        <div class="msg-offer-condition">${escHtml(offer.condition)}</div>
                      </div>
                      ${actionButtons}
                    </div>
                  `;
      }

      if (msg.imageUrl) {
        contentHtml += `
                    <div class="msg-bubble">
                      <img src="${msg.imageUrl}" alt="Image" style="max-width: 230px; border-radius: 8px; margin-bottom: 6px;" loading="lazy" onerror="this.src='/images/avatar/default.png'">
                      ${msg.text ? `<div>${escHtml(msg.text)}</div>` : ''}
                    </div>
                  `;
      } else if (msg.fileUrl) {
        contentHtml += `
                    <div class="msg-bubble" style="display: flex; align-items: center; gap: 8px;">
                      <span style="font-size: 1.5rem;">📎</span>
                      <a href="${msg.fileUrl}" target="_blank" style="color: inherit; text-decoration: underline;">${escHtml(msg.fileName || 'File')}</a>
                    </div>
                  `;
      } else if (!msg.offer) {
        contentHtml += `<div class="msg-bubble">${escHtml(msg.text)}</div>`;
      }

      if (msg.deletedForAll) {
        contentHtml = `<div class="msg-bubble deleted" style="opacity:0.5; font-style:italic; font-size:0.75rem;">🚫 Pesan telah dihapus</div>`;
      } else {
        actionHtml = `
                    <div class="msg-actions">
                      <button class="btn-msg-action" onclick='prepareReply(${JSON.stringify(msg)})'>Reply</button>
                      <button class="btn-msg-action" onclick='deleteMessageForMe("${msg.id}")'>Hide</button>
                      ${isSelf ? `<button class="btn-msg-action danger" onclick='deleteMessageForAll("${msg.id}")'>Delete</button>` : ''}
                    </div>
                  `;
      }

      row.innerHTML = `
                  <div class="msg-avatar">
                    <img src="${avatarUrl}" alt="" onerror="this.src='/images/avatar/default.png'">
                  </div>
                  <div class="msg-content">
                    ${!isSelf ? `<div class="msg-name">${escHtml(msg.displayName || 'Anonymous')}</div>` : ''}
                    ${contentHtml}
                    <div class="msg-time">${timeStr}</div>
                    ${actionHtml}
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

    // ── Nego System ──
    function openNegoModal(msg) {
      if (!msg.offer) return;

      currentNegoOffer = msg;

      // Populate original offer
      const originalOfferDiv = document.getElementById('negoOriginalOffer');
      originalOfferDiv.innerHTML = `
                  <div class="nego-offer-row">
                    <span class="nego-offer-label">Deskripsi</span>
                    <span class="nego-offer-value">${escHtml(msg.offer.desc)}</span>
                  </div>
                  <div class="nego-offer-row">
                    <span class="nego-offer-label">Harga</span>
                    <span class="nego-offer-value price">$${escHtml(String(msg.offer.price))}</span>
                  </div>
                  <div class="nego-offer-row">
                    <span class="nego-offer-label">Kondisi</span>
                    <span class="nego-offer-value">${escHtml(msg.offer.condition)}</span>
                  </div>
                `;

      // Show negotiation history if exists
      const negotiations = msg.offer.negotiations || [];
      if (negotiations.length > 0) {
        const historySection = document.getElementById('negoHistorySection');
        const historyDiv = document.getElementById('negoHistory');
        historySection.style.display = 'block';

        historyDiv.innerHTML = negotiations.map(nego => {
          const time = new Date(nego.timestamp).toLocaleString('id-ID', {
            day: 'numeric',
            month: 'short',
            hour: '2-digit',
            minute: '2-digit'
          });
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
        document.getElementById('negoHistorySection').style.display = 'none';
      }

      // Clear inputs
      document.getElementById('negoPrice').value = '';
      document.getElementById('negoMessage').value = '';

      // Show modal
      document.getElementById('negoModal').classList.add('active');
    }

    function closeNegoModal() {
      document.getElementById('negoModal').classList.remove('active');
      currentNegoOffer = null;
    }

    function setQuickNego(amount) {
      if (!currentNegoOffer || !currentNegoOffer.offer) return;

      const currentPrice = parseFloat(currentNegoOffer.offer.price) || 0;
      const newPrice = Math.max(0, currentPrice + amount);

      document.getElementById('negoPrice').value = newPrice.toFixed(2);
    }

    async function submitNego() {
      if (!currentNegoOffer || !activeRoomId) return;

      const priceInput = document.getElementById('negoPrice');
      const messageInput = document.getElementById('negoMessage');

      const counterPrice = parseFloat(priceInput.value);
      const counterMessage = messageInput.value.trim();

      if (!counterPrice || counterPrice <= 0) {
        alert('Masukkan harga counter offer yang valid.');
        return;
      }

      try {
        // Create negotiation entry
        const negoEntry = {
          uid: currentUser.uid,
          displayName: currentUser.displayName || currentUser.email || 'Anonymous',
          price: counterPrice,
          message: counterMessage,
          timestamp: Date.now()
        };

        // Get current negotiations array
        const msgRef = db.ref(`chats/${activeRoomId}/messages/${currentNegoOffer.id}`);
        const snapshot = await msgRef.once('value');
        const currentMsg = snapshot.val();

        if (!currentMsg || !currentMsg.offer) {
          alert('Offer tidak ditemukan.');
          return;
        }

        const negotiations = currentMsg.offer.negotiations || [];
        negotiations.push(negoEntry);

        // Update message with new negotiation
        await msgRef.child('offer').update({
          status: 'negotiating',
          negotiations: negotiations,
          lastNego: negoEntry
        });

        // Send notification message
        const myPfp = await fetchPfp(currentUser.uid);
        await db.ref(`chats/${activeRoomId}/messages`).push({
          uid: currentUser.uid,
          displayName: currentUser.displayName || currentUser.email || 'Anonymous',
          photoURL: myPfp,
          text: `💬 Counter offer: $${counterPrice}${counterMessage ? ' - ' + counterMessage : ''}`,
          type: 'text',
          createdAt: Date.now(),
        });

        closeNegoModal();
      } catch (e) {
        console.error('Failed to submit negotiation:', e);
        alert('Gagal mengirim counter offer.');
      }
    }

    async function acceptOffer(msg) {
      if (!confirm('Setuju dengan penawaran ini?')) return;
      if (!msg.offer || !activeRoomId) return;

      try {
        const msgRef = db.ref(`chats/${activeRoomId}/messages/${msg.id}`);
        await msgRef.child('offer').update({
          status: 'accepted',
          acceptedBy: currentUser.uid,
          acceptedAt: Date.now()
        });

        // Send notification message
        const myPfp = await fetchPfp(currentUser.uid);
        await db.ref(`chats/${activeRoomId}/messages`).push({
          uid: currentUser.uid,
          displayName: currentUser.displayName || currentUser.email || 'Anonymous',
          photoURL: myPfp,
          text: `✅ Menyetujui penawaran $${msg.offer.price}`,
          type: 'text',
          createdAt: Date.now(),
        });

      } catch (e) {
        console.error('Failed to accept offer:', e);
        alert('Gagal menerima penawaran.');
      }
    }

    async function uploadToCloudinary(file) {
      const formData = new FormData();
      formData.append('file', file);
      formData.append('upload_preset', 'pokelu_storage');

      const endpoint = file.type.startsWith('image/')
        ? 'https://api.cloudinary.com/v1_1/dsz8bojjy/image/upload'
        : 'https://api.cloudinary.com/v1_1/dsz8bojjy/auto/upload';

      const xhr = new XMLHttpRequest();

      return new Promise((resolve, reject) => {
        xhr.upload.addEventListener('progress', (e) => {
          if (e.lengthComputable) {
            const percent = (e.loaded / e.total) * 100;
            document.getElementById('uploadProgressFill').style.width = percent + '%';
            document.getElementById('uploadProgressText').textContent = Math.round(percent) + '%';
          }
        });

        xhr.addEventListener('load', () => {
          if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response && response.secure_url) {
              resolve(response.secure_url);
            } else {
              reject(new Error('Upload tidak menghasilkan URL'));
            }
          } else {
            reject(new Error('Upload failed: ' + xhr.status + ' ' + xhr.statusText));
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
        document.getElementById('uploadProgressText').textContent = '0%';
      } else {
        progress.classList.remove('active');
      }
    }

    function autoResizeTextarea(el) {
      el.style.height = 'auto';
      el.style.height = Math.min(el.scrollHeight, 120) + 'px';
    }

    const chatInput = document.getElementById('chatInput');

    chatInput.addEventListener('input', () => {
      autoResizeTextarea(chatInput);

      // Update send button state
      const btnSend = document.getElementById('btnSend');
      if (chatInput.value.trim()) {
        btnSend.classList.add('active');
      } else {
        btnSend.classList.remove('active');
      }
    });

    chatInput.addEventListener('keydown', e => {
      if (e.key === 'Enter' && !e.shiftKey) {
        sendMessage();
        e.preventDefault();
      }
    });

    document.getElementById('fileInput').addEventListener('change', function () {
      if (!this.files.length) return;
      const fileName = this.files[0].name;
      console.log('Selected file:', fileName);
    });

    function escHtml(str) {
      if (!str) return '';
      return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
  </script>

@endsection