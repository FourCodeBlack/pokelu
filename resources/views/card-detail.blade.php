@section('navbar')
    @include('layout.navbar')
@endsection
@extends('layout.app')

@push('styles')
  @vite('resources/css/card-detail.css')
@endpush

@section('content')
  <main class="detail-page" x-data="cardDetail('{{ $card['id'] }}')" x-init="init()">

    {{-- ░░ HERO SECTION ░░ --}}
    <section class="card-hero">
      <div class="hero-bg" style="--img:url('{{ asset('images/Pokelubg.webp') }}')"></div>

      <div class="hero-inner">
        <div class="card-visual-wrap" id="cardWrap">
          <div class="card-visual" id="cardEl">
            <img src="{{ $card['image'] }}" alt="{{ $card['name'] }}" class="card-img">
            <div class="card-shine" id="cardShine"></div>
            <div class="card-glare" id="cardGlare"></div>
          </div>
        </div>

        <div class="card-info">
          <div class="card-meta-top">
            <span class="card-set-badge">{{ $card['set'] ?? 'Unknown Set' }}</span>
            <span class="card-id-badge">#{{ $card['id'] }}</span>
          </div>
          <div class="container-fluit container">
            <div class="row">
              <div class="col">
                <h1 class="card-name">{{ $card['name'] }}</h1>
              </div>
              <div class="col">
                <img class="" src="{{ $card['logo'] }}" style="height: 50px; width: auto;">
              </div>
            </div>
          </div>

          <div class="card-tags">
            @if(!empty($card['rarity']))
              <span class="tag tag-rarity">{{ $card['rarity'] }}</span>
            @endif
            @if(!empty($card['type']))
              <span class="tag tag-type">{{ $card['type'] }}</span>
            @endif
            @if(!empty($card['stage']))
              <span class="tag tag-stage">{{ $card['stage'] }}</span>
            @endif
          </div>

          @if(!empty($card['description']))
            <p class="card-desc">{{ $card['description'] }}</p>
          @endif

          <div class="price-summary" x-show="priceStats.count > 0" x-cloak>
            <div class="price-label">MARKET PRICE</div>
            <div class="price-row">
              <span>Trend: <strong x-text="'$' + priceStats.avg"></strong></span>
              <span>Lowest: <strong x-text="'$' + priceStats.min"></strong></span>
              <span>Highest: <strong x-text="'$' + priceStats.max"></strong></span>
            </div>
            <div class="price-offers" x-text="priceStats.count + ' offers available'"></div>
          </div>

          <button class="btn-wish" @click="toggleWish()" :class="{ active: wished }">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <path
                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
            </svg>
            <span x-text="wished ? 'Wishlisted' : 'Add to Wishlist'"></span>
          </button>
        </div>
      </div>
    </section>

    {{-- ░░ MARKETPLACE SECTION ░░ --}}
    <section class="market-section">
      <div class="market-container">

        {{-- ── AUTH BAR (belum login) ── --}}
        <div class="auth-bar" x-show="!currentUser" x-cloak>
          <p class="auth-prompt">Masuk untuk menawar atau berkomentar</p>
          <div class="auth-buttons">
            <button class="btn-auth btn-google" @click="loginGoogle()">
              <svg width="18" height="18" viewBox="0 0 24 24">
                <path fill="#EA4335"
                  d="M5.27 9.76A7.08 7.08 0 0 1 12 4.9c1.69 0 3.22.6 4.41 1.59L19.9 3A11.84 11.84 0 0 0 12 0C8.07 0 4.63 2.1 2.71 5.26l2.56 4.5z" />
                <path fill="#34A853"
                  d="M16.04 18.01A7.07 7.07 0 0 1 12 19.1c-2.93 0-5.44-1.77-6.62-4.34l-2.54 4.46A11.85 11.85 0 0 0 12 24c2.93 0 5.73-1.05 7.83-2.97l-3.79-3.02z" />
                <path fill="#FBBC05"
                  d="M19.1 12c0-.66-.06-1.3-.17-1.91H12v3.62h3.98a3.4 3.4 0 0 1-1.48 2.24l3.79 3.02C20.24 17.28 21 14.8 21 12H19.1z" />
                <path fill="#4285F4"
                  d="M2.71 5.26A11.82 11.82 0 0 0 0 12c0 2.36.65 4.56 1.78 6.44l2.54-4.46A7.08 7.08 0 0 1 4.9 12c0-1.02.22-1.98.61-2.84L2.71 5.26z" />
                <path fill="#4285F4"
                  d="M12 4.9c1.69 0 3.22.6 4.41 1.59l3.49-3.49A11.84 11.84 0 0 0 12 0C8.07 0 4.63 2.1 2.71 5.26l2.56 4.5A7.08 7.08 0 0 1 12 4.9z" />
              </svg>
              Login dengan Google
            </button>
            <button class="btn-auth btn-email" @click="showEmailLogin = !showEmailLogin">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="4" width="20" height="16" rx="2" />
                <polyline points="2,4 12,13 22,4" />
              </svg>
              Login Email
            </button>
          </div>

          <div class="email-login-form" x-show="showEmailLogin" x-transition>
            <input type="email" x-model="emailInput" placeholder="Email" class="form-input">
            <input type="password" x-model="passInput" placeholder="Password" class="form-input">
            <div class="email-login-actions">
              <button class="btn-submit" @click="loginEmail()">Masuk</button>
              <button class="btn-submit btn-register" @click="registerEmail()">Daftar</button>
            </div>
            <p class="auth-error" x-text="authError" x-show="authError" x-cloak></p>
          </div>
        </div>

        {{-- ── USER BAR (sudah login) ── --}}
        <div class="user-bar" x-show="currentUser" x-cloak>
          <div class="user-info">
            <img :src="currentUser?.photoURL || 'https://api.dicebear.com/7.x/bottts/svg?seed=' + currentUser?.uid"
              class="user-avatar">
            <span x-text="currentUser?.displayName || currentUser?.email || 'Anonymous'"></span>
          </div>
          <button class="btn-logout" @click="logout()">Keluar</button>
        </div>

        {{-- ── MARKET HEADER ── --}}
        <div class="market-header">
          <h2 class="market-title">
            <span class="market-title-icon">🏪</span>
            POKELU MARKET
          </h2>
          <span class="market-count" x-text="offers.length + ' offers'"></span>
        </div>

        {{-- ── POST OFFER FORM ── --}}
        <div class="post-offer-form" x-show="currentUser" x-cloak x-transition>
          <h3 class="form-title">+ Pasang Penawaran</h3>
          <div class="offer-form-grid">
            <input type="number" x-model="newOffer.price" placeholder="Harga ($)" class="form-input" min="0" step="0.01">
            <input type="text" x-model="newOffer.condition" placeholder="Kondisi (e.g. Near Mint)" class="form-input">
          </div>
          <textarea x-model="newOffer.desc" placeholder="Deskripsi penawaran kamu…" class="form-textarea"
            rows="2"></textarea>
          <button class="btn-post-offer" @click="postOffer()" :disabled="!newOffer.price || !newOffer.desc">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="12" y1="5" x2="12" y2="19" />
              <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            Pasang Sekarang
          </button>
        </div>

        {{-- ── OFFER LIST ── --}}
        <div class="offer-list">
          <template x-if="offers.length === 0">
            <div class="empty-state">
              <span>📭</span>
              <p>Belum ada penawaran. Jadilah yang pertama!</p>
            </div>
          </template>

          <template x-for="offer in offers" :key="offer.id">
            <div class="offer-card">
              <div class="offer-top">
                <div class="offer-seller">
                  <img :src="offer.photoURL || 'https://api.dicebear.com/7.x/bottts/svg?seed=' + offer.uid"
                    class="offer-avatar">
                  <div>
                    <span class="offer-seller-name" x-text="offer.displayName || 'Anonymous'"></span>
                    <span class="offer-seller-sub"
                      x-text="offer.soldCount ? offer.soldCount + ' cards sold' : 'New seller'"></span>
                  </div>
                </div>
                <div class="offer-price" x-text="'$' + parseFloat(offer.price).toFixed(2)"></div>
              </div>

              <div class="offer-body">
                <div class="offer-thumb-wrap">
                  <img src="{{ $card['image'] }}" class="offer-thumb" alt="">
                </div>
                <div class="offer-detail">
                  <div class="offer-condition" x-text="offer.condition || 'Kondisi tidak disebutkan'"></div>
                  <p class="offer-desc" x-text="offer.desc"></p>
                  <button class="btn-contact" @click="startChat(offer)">CONTACT</button>
                </div>
              </div>

              <div class="offer-thread">
                <template x-for="reply in (offer.replies || [])" :key="reply.id">
                  <div class="thread-item" :class="{ 'thread-mine': reply.uid === currentUser?.uid }">
                    <img :src="reply.photoURL || 'https://api.dicebear.com/7.x/bottts/svg?seed=' + reply.uid"
                      class="thread-avatar">
                    <div class="thread-bubble">
                      <span class="thread-name" x-text="reply.displayName || 'Anonymous'"></span>
                      <p class="thread-text" x-text="reply.text"></p>
                    </div>
                  </div>
                </template>

                <div class="thread-reply-form" x-show="currentUser">
                  <input type="text" :placeholder="'Balas ke ' + (offer.displayName || 'penjual') + '…'"
                    class="thread-input" @keydown.enter="replyOffer(offer, $event)" x-model="offer._replyDraft">
                  <button class="thread-send" @click="replyOffer(offer, null, offer._replyDraft)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <line x1="22" y1="2" x2="11" y2="13" />
                      <polygon points="22 2 15 22 11 13 2 9 22 2" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </template>
        </div>

        {{-- ── GENERAL COMMENTS ── --}}
        {{-- ── GENERAL COMMENTS ── --}}
        <div class="comments-section" id="diskusi">
          <h3 class="comments-title">💬 Diskusi Kartu</h3>

          {{-- Form komentar: tampil jika sudah login via Firebase Auth --}}
          <div class="comment-form" x-show="currentUser" x-cloak>
            <img :src="currentUser?.photoURL || 'https://api.dicebear.com/7.x/bottts/svg?seed=' + currentUser?.uid"
              class="comment-avatar">
            <div class="comment-input-wrap">
              <textarea x-model="newComment" placeholder="Tulis komentar…" class="form-textarea" rows="2"></textarea>
              <button class="btn-comment" @click="postComment()" :disabled="!newComment.trim()">Kirim</button>
            </div>
          </div>

          {{--
          ✅ BARU: Login prompt yang lebih jelas + tombol yang redirect balik ke kartu ini.
          Menggunakan route Laravel /login?redirect=/card/{id}#diskusi
          sehingga setelah login, user langsung balik ke halaman kartu ini.
          --}}
          <div class="comment-login-prompt" x-show="!currentUser" x-cloak>
            <div class="comment-login-inner">

              {{-- Icon chat --}}
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                stroke-linecap="round" stroke-linejoin="round" style="color:var(--muted); opacity:.6">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
              </svg>

              <p class="comment-login-text">
                Masuk untuk ikut diskusi dan meninggalkan komentar.
              </p>

              {{--
              ✅ KUNCI: href ke /login dengan ?redirect= berisi URL kartu ini + #diskusi
              Blade {{ request()->path() }} = "card/swsh1-1" (tanpa slash depan)
              Jadi kita buat /card/{id}#diskusi sebagai redirect target
              --}}
              <a href="{{ route('login') }}?redirect={{ urlencode('/' . request()->path() . '#diskusi') }}"
                class="btn-login-cta">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                  <polyline points="10 17 15 12 10 7" />
                  <line x1="15" y1="12" x2="3" y2="12" />
                </svg>
                Login untuk Berkomentar
              </a>

              {{-- Sub-text: info akun yang tersedia --}}
              <span class="comment-login-sub">
                Belum punya akun? Gunakan akun Google atau daftar dengan email.
              </span>
            </div>
          </div>

          {{-- List komentar --}}
          <div class="comments-list">
            <template x-for="c in comments" :key="c.id">
              <div class="comment-item">
                <img :src="c.photoURL || 'https://api.dicebear.com/7.x/bottts/svg?seed=' + c.uid" class="comment-avatar">
                <div class="comment-body">
                  <div class="comment-header">
                    <span class="comment-name" x-text="c.displayName || 'Anonymous'"></span>
                    <span class="comment-time" x-text="timeAgo(c.createdAt)"></span>
                  </div>
                  <p class="comment-text" x-text="c.text"></p>
                </div>
              </div>
            </template>
            <div class="empty-state" x-show="comments.length === 0">
              <span>🌱</span>
              <p>Belum ada komentar. Mulai diskusi!</p>
            </div>
          </div>
        </div>

      </div>
    </section>

  </main>

  {{-- ░░ CHAT MODAL ░░ --}}
  <div class="chat-modal" x-show="chatOpen" x-cloak x-transition.opacity>
    <div class="chat-box">
      <div class="chat-header">
        <span x-text="'Chat dengan ' + (chatTarget?.displayName || 'Penjual')"></span>
        <button @click="chatOpen = false">✕</button>
      </div>
      <div class="chat-messages" x-ref="chatMessages">
        <template x-for="m in chatMessages" :key="m.id">
          <div class="chat-msg" :class="{ 'chat-msg-mine': m.uid === currentUser?.uid }">
            <span class="chat-msg-name" x-text="m.displayName || 'Anonymous'"></span>
            <p class="chat-msg-text" x-text="m.text"></p>
          </div>
        </template>
      </div>
      <div class="chat-input-row">
        <input type="text" x-model="chatDraft" placeholder="Ketik pesan…" class="form-input" @keydown.enter="sendChat()">
        <button class="btn-submit" @click="sendChat()">Kirim</button>
      </div>
    </div>
  </div>

@endsection

{{--
═══════════════════════════════════════════════════════
GANTI bagian @push('scripts') di card-detail.blade.php
dengan seluruh kode di bawah ini
═══════════════════════════════════════════════════════
--}}
@push('scripts')
  {{-- 1. Firebase SDK DULU --}}
  <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-database-compat.js"></script>

  {{-- 2. Definisi cardDetail SEBELUM Alpine load --}}
  <script>
    // Cek Firebase sudah ada atau belum (cegah double init)
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

    const auth = firebase.auth();
    const db   = firebase.database();
    const FB_TIMESTAMP = firebase.database.ServerValue.TIMESTAMP;

    function snapshotToArray(snapshot) {
      const result = [];
      snapshot.forEach(child => {
        result.push({ id: child.key, ...child.val() });
      });
      return result;
    }

    // Daftarkan ke Alpine SEBELUM alpine load lewat alpine:init
    document.addEventListener('alpine:init', () => {
      Alpine.data('cardDetail', (cardId) => ({
        cardId,
        currentUser: null,
        showEmailLogin: false,
        emailInput: '',
        passInput: '',
        authError: '',
        wished: false,
        offers: [],
        newOffer: { price: '', condition: '', desc: '' },
        priceStats: { count: 0, min: 0, max: 0, avg: 0 },
        comments: [],
        newComment: '',
        chatOpen: false,
        chatTarget: null,
        chatMessages: [],
        chatDraft: '',
        _chatOff: null,

        init() {
          auth.onAuthStateChanged(user => {
            this.currentUser = user;
            if (user) {
              this.wished = JSON.parse(localStorage.getItem('wish_' + this.cardId) || 'false');
              if (window.location.hash === '#diskusi') {
                this.$nextTick(() => {
                  const section = document.getElementById('diskusi');
                  if (section) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                  history.replaceState(null, '', window.location.pathname);
                });
              }
            }
          });

          db.ref(`cards/${this.cardId}/offers`).orderByChild('createdAt').on('value', snapshot => {
            const arr = snapshotToArray(snapshot).reverse();
            this.offers = arr.map(o => ({ ...o, _replyDraft: '' }));
            this.calcPriceStats();
            this.offers.forEach(o => this.loadReplies(o));
          });

          db.ref(`cards/${this.cardId}/comments`).orderByChild('createdAt').limitToLast(50).on('value', snapshot => {
            this.comments = snapshotToArray(snapshot).reverse();
          });
        },

        async loginGoogle() {
          const provider = new firebase.auth.GoogleAuthProvider();
          try { await auth.signInWithPopup(provider); } catch (e) { this.authError = e.message; }
        },
        async loginEmail() {
          try {
            await auth.signInWithEmailAndPassword(this.emailInput, this.passInput);
            this.showEmailLogin = false;
          } catch (e) { this.authError = e.message; }
        },
        async logout() { await auth.signOut(); },

        async postOffer() {
          if (!this.currentUser) return;
          const u = this.currentUser;
          try {
            await db.ref(`cards/${this.cardId}/offers`).push({
              uid: u.uid,
              displayName: u.displayName || u.email,
              photoURL: u.photoURL || null,
              price: parseFloat(this.newOffer.price),
              condition: this.newOffer.condition || '',
              desc: this.newOffer.desc,
              createdAt: FB_TIMESTAMP
            });
            this.newOffer = { price: '', condition: '', desc: '' };
          } catch (e) { console.error(e); }
        },

        loadReplies(offer) {
          db.ref(`cards/${this.cardId}/offers/${offer.id}/replies`).orderByChild('createdAt').on('value', snapshot => {
            const idx = this.offers.findIndex(o => o.id === offer.id);
            if (idx > -1) this.offers[idx].replies = snapshotToArray(snapshot);
          });
        },

        async replyOffer(offer) {
          if (!this.currentUser || !offer._replyDraft?.trim()) return;
          const u = this.currentUser;
          try {
            await db.ref(`cards/${this.cardId}/offers/${offer.id}/replies`).push({
              uid: u.uid,
              displayName: u.displayName || u.email,
              text: offer._replyDraft.trim(),
              createdAt: FB_TIMESTAMP
            });
            offer._replyDraft = '';
          } catch (e) { console.error(e); }
        },

        calcPriceStats() {
          if (!this.offers.length) { this.priceStats = { count: 0 }; return; }
          const prices = this.offers.map(o => parseFloat(o.price)).filter(p => !isNaN(p));
          this.priceStats = {
            count: prices.length,
            min: Math.min(...prices).toFixed(2),
            max: Math.max(...prices).toFixed(2),
            avg: (prices.reduce((a, b) => a + b, 0) / prices.length).toFixed(2),
          };
        },

        async postComment() {
          if (!this.currentUser || !this.newComment.trim()) return;
          const u = this.currentUser;
          try {
            await db.ref(`cards/${this.cardId}/comments`).push({
              uid: u.uid,
              displayName: u.displayName || u.email,
              photoURL: u.photoURL || null,
              text: this.newComment.trim(),
              createdAt: FB_TIMESTAMP
            });
            this.newComment = '';
          } catch (e) { console.error(e); }
        },

        // Redirect ke halaman chat
        startChat(offer) {
          if (!this.currentUser) return;
          const roomId = [this.currentUser.uid, offer.uid].sort().join('_') + '_' + this.cardId;
          // Simpan info room dulu ke Firebase agar sidebar chat keisi
          db.ref(`user_rooms/${this.currentUser.uid}/${roomId}`).once('value').then(snap => {
            if (!snap.exists()) {
              db.ref(`user_rooms/${this.currentUser.uid}/${roomId}`).set({
                name: offer.displayName || 'Penjual',
                avatar: offer.photoURL || null,
                lastMsg: '',
                lastTs: Date.now(),
                partnerId: offer.uid,
              });
              db.ref(`user_rooms/${offer.uid}/${roomId}`).set({
                name: this.currentUser.displayName || this.currentUser.email,
                avatar: this.currentUser.photoURL || null,
                lastMsg: '',
                lastTs: Date.now(),
                partnerId: this.currentUser.uid,
              });
            }
            window.location.href = `/chat?room=${roomId}&sellerId=${offer.uid}&sellerName=${encodeURIComponent(offer.displayName || 'Penjual')}&cardId=${this.cardId}`;
          });
        },

        toggleWish() {
          this.wished = !this.wished;
          localStorage.setItem('wish_' + this.cardId, JSON.stringify(this.wished));
        },

        timeAgo(ts) {
          if (!ts) return '';
          const s = Math.floor((Date.now() - ts) / 1000);
          if (s < 60) return 'Baru saja';
          if (s < 3600) return Math.floor(s / 60) + 'm lalu';
          if (s < 86400) return Math.floor(s / 3600) + 'j lalu';
          return Math.floor(s / 86400) + ' hari lalu';
        }
      }));
    });
  </script>

  {{-- 3. Alpine TERAKHIR — tanpa defer agar alpine:init event masih bisa ditangkap --}}
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

  {{-- 4. Card visual effect (tidak berubah) --}}
  <script>
    const wrap = document.getElementById('cardWrap');
    const card = document.getElementById('cardEl');
    if (wrap && card) {
      const shine = document.getElementById('cardShine');
      const glare = document.getElementById('cardGlare');
      wrap.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const cx = rect.left + rect.width / 2;
        const cy = rect.top + rect.height / 2;
        const dx = e.clientX - cx;
        const dy = e.clientY - cy;
        const rotX = (-dy / (rect.height / 2)) * 18;
        const rotY = (dx / (rect.width / 2)) * 18;
        const px = ((e.clientX - rect.left) / rect.width) * 100;
        const py = ((e.clientY - rect.top) / rect.height) * 100;
        card.style.transform = `rotateX(${rotX}deg) rotateY(${rotY}deg)`;
        shine.style.setProperty('--mx', px + '%');
        shine.style.setProperty('--my', py + '%');
      });
      wrap.addEventListener('mouseleave', () => {
        card.style.transform = 'rotateX(0deg) rotateY(0deg)';
      });
    }
  </script>
@endpush