@section('navbar')
  @include('layout.navbar')
@endsection
@extends('layout.app')

@push('styles')
  @vite('resources/css/card-detail.css')
  <style>
    .offer-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 12px;
    }

    .offer-delete-btn {
        border: 1px solid rgba(255, 92, 122, 0.45);
        border-radius: 14px;
        padding: 10px 16px;
        background: linear-gradient(135deg, rgba(255, 75, 110, 0.95), rgba(190, 24, 93, 0.95));
        color: #ffffff;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        box-shadow: 0 10px 24px rgba(255, 75, 110, 0.18);
        transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
    }

    .offer-delete-btn:hover {
        transform: translateY(-1px);
        opacity: 0.95;
        box-shadow: 0 14px 30px rgba(255, 75, 110, 0.25);
    }

    .offer-delete-btn:active {
        transform: translateY(0);
    }

    .admin-delete-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 999px;
        background: rgba(255, 75, 110, 0.14);
        color: #ff9eb0;
        font-size: 12px;
        font-weight: 700;
    }
  </style>
@endpush

@section('content')
  <main class="detail-page" x-data="cardDetail('{{ $card['id'] }}')" x-init="init()">

    {{-- ░░ HERO SECTION ░░ --}}
    <section class="card-hero">
      <a href="{{ url()->previous() }}" class="back-floating">
        ← Back
      </a>
      
      @if(session('success'))
          <div class="alert-success">
              {{ session('success') }}
          </div>
      @endif

      @if(session('error'))
          <div class="alert-error">
              {{ session('error') }}
          </div>
      @endif

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
          @php
            use App\Models\FirebaseHelper;
            $uid = session('user.uid');

            $isWishlisted = $uid
              ? FirebaseHelper::adakah("users/$uid/wishlist/" . $card['id'])
              : false;
            $data = FirebaseHelper::baca("users/$uid");
            $isAdmin = ($data['role'] ?? 'user') === 'admin';
          @endphp
          <form action="{{ route('wishlist.add') }}" method="post" x-data="{
                                                              wished: {{ $isWishlisted ? 'true' : 'false' }},

                                                              async toggleWish() {

                                                                  const response = await fetch('/wishlist/add', {
                                                                      method: 'POST',
                                                                      headers: {
                                                                          'Content-Type': 'application/json',
                                                                          'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                                      },
                                                                      body: JSON.stringify({
                                                                          id: '{{ $card['id'] }}'
                                                                      })
                                                                  });

                                                                  const data = await response.json();

                                                                  if(data.success){
                                                                      this.wished = data.wished;
                                                                  }
                                                              }
                                                          }">
            @csrf
            <input type="hidden" name="id" value="{{ $card['id'] }}">
            <button class="btn-wish" @click="toggleWish" :class="{ active: wished }" type="button">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path
                  d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
              </svg>
              <span x-text="wished ? 'Wishlisted' : 'Add to Wishlist'"></span>
            </button>
          </form>

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
            <img :src="'/images/avatar/' + (currentUserPfp || 'default') + '.png'" class="user-avatar">
            <span x-text="(userCache[currentUser?.uid]?.name || userCache[currentUser?.uid]?.username) || currentUser?.displayName || currentUser?.email || 'Anonymous'" class="text-light font"></span>
          </div>
          <button class="btn-logout" @click="logout()">Keluar</button>
        </div>

        {{-- Debug sementara --}}
        {{-- UID: {{ $offers[0]['uid'] ?? 'NULL' }} --}}
        {{-- Seller: {{ json_encode($offers[0]['seller'] ?? []) }} --}}

        {{-- ── MARKET HEADER ── --}}
        <div class="market-header">
          <h2 class="market-title text-light">
            <span class="market-title-icon text-light">🏪</span>
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
              <p class="text-light">Belum ada penawaran. Jadilah yang pertama!</p>
            </div>
          </template>

          <template x-for="offer in offers" :key="offer.id">
            <div class="offer-card">
              <div class="offer-top">
                <div class="offer-seller">
                  <img :src="'/images/avatar/' + (offer.resolvedPfp || 'default') + '.png'"
                    class="offer-avatar">
                  <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                      <span class="offer-seller-name text-light" x-text="offer.resolvedName"></span>
                      <template x-if="offer.resolvedRole === 'admin'">
                        <span class="admin-delete-badge" style="position: static; font-size: 10px; padding: 2px 6px;">Admin</span>
                      </template>
                    </div>
                    <span class="offer-seller-sub"
                      x-text="offer.resolvedHandle || '@user'"></span>
                  </div>
                </div>
                <div class="offer-price">
                  <span x-text="'$' + parseFloat(offer.price).toFixed(2)"></span>
                  <template x-if="canDeleteOffer(offer)">
                    <button @click="deleteOffer(offer.id, offer)" class="offer-delete-btn" style="padding: 6px 12px; font-size: 12px; border-radius: 8px; margin-left: 8px;">
                      <span x-text="isAdmin && offer.uid !== currentUser?.uid ? 'Hapus sebagai Admin' : 'Hapus Penawaran'"></span>
                    </button>
                  </template>
                </div>
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
                    <img :src="'/images/avatar/' + (reply.resolvedPfp || 'default') + '.png'"
                      class="thread-avatar">
                    <div class="thread-bubble" style="flex:1;">
                      <span class="thread-name" x-text="reply.resolvedName"></span>
                      <p class="thread-text text-light" x-text="reply.text"></p>
                    </div>
                    @if($isAdmin ?? false)
                      <button class="admin-delete-btn" @click="adminDeleteReply(offer.id, reply.id)" title="Hapus Reply (Admin)" style="padding:4px 8px; font-size:12px;">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    @endif
                  </div>
                </template>

                <div class="thread-reply-form" x-show="currentUser">
                  <input type="text" :placeholder="'Balas ke ' + (offer.resolvedName || 'penjual') + '…'"
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
          <h3 class="comments-title text-light">💬 Diskusi Kartu</h3>

          {{-- Form komentar: tampil jika sudah login via Firebase Auth --}}
          <div class="comment-form" x-show="currentUser" x-cloak>
            <img :src="'/images/avatar/' + (currentUserPfp || 'default') + '.png'" class="comment-avatar">
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
                <img :src="'/images/avatar/' + (c.resolvedPfp || 'default') + '.png'"
                  class="comment-avatar">
                {{-- BREAKPOINT --}}
                <div class="comment-body">
                  <div class="comment-header" style="display:flex; justify-content:space-between; width:100%;">
                    <div>
                      <span class="comment-name" x-text="c.resolvedName"></span>
                      <span class="comment-time" x-text="timeAgo(c.createdAt)"></span>
                    </div>
                    <template x-if="isAdmin || c.uid === currentUser?.uid">
                      <form method="POST" :action="`/cards/${cardId}/comments/${c.id}`" onsubmit="return confirm('Yakin ingin menghapus komentar ini?');" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="delete-mini-btn">
                            Hapus
                        </button>
                      </form>
                    </template>
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

@push('scripts')
  <script>
    const auth = firebase.auth();
    const db = firebase.database();
    const FB_TIMESTAMP = firebase.database.ServerValue.TIMESTAMP;
    const FIREBASE_TOKEN = '{{ $firebaseToken ?? '' }}';

    if (FIREBASE_TOKEN) {
      auth.onAuthStateChanged(async (user) => {
        const currentUid = '{{ session("user.uid") }}';
        if (!user || user.uid !== currentUid) {
          try {
            await auth.signInWithCustomToken(FIREBASE_TOKEN);
            console.log('Firebase authenticated successfully via custom token');
          } catch (err) {
            console.error('Firebase custom token authentication failed:', err);
          }
        }
      });
    }

    function snapshotToArray(snapshot) {
      const result = [];
      snapshot.forEach(child => {
        result.push({ id: child.key, ...child.val() });
      });
      return result;
    }

    document.addEventListener('alpine:init', () => {
      Alpine.data('cardDetail', (cardId) => ({
        cardId,
        currentUser: null,
        currentUserPfp: 'default',
        isAdmin: {{ ($isAdmin ?? false) ? 'true' : 'false' }},
        pfpCache: {},
        showEmailLogin: false,
        emailInput: '',
        passInput: '',
        authError: '',
        wished: false,
        offers: @json($offers ?? []),
        newOffer: { price: '', condition: '', desc: '' },
        priceStats: { count: 0, min: 0, max: 0, avg: 0 },
        comments: @json($comments ?? []),
        newComment: '',
        chatOpen: false,
        chatTarget: null,
        chatMessages: [],
        chatDraft: '',

        canDeleteOffer(offer) {
          if (!this.currentUser) return false;
          return this.isAdmin || offer.uid === this.currentUser.uid;
        },

        async deleteOffer(offerId, offer) {
          if (!this.canDeleteOffer(offer)) {
            alert('Kamu tidak punya izin menghapus penawaran ini.');
            return;
          }

          if (!confirm('Yakin ingin menghapus penawaran ini?')) {
            return;
          }

          try {
            await db.ref(`cards/${this.cardId}/offers/${offerId}`).remove();
          } catch(e) {
            console.error(e);
            alert('Gagal menghapus penawaran.');
          }
        },

        async init() {
          auth.onAuthStateChanged(async user => {
            this.currentUser = user;
            if (user) {
              this.fetchUser(user.uid);
              if (window.location.hash === '#diskusi') {
                this.$nextTick(() => {
                  const section = document.getElementById('diskusi');
                  if (section) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                  history.replaceState(null, '', window.location.pathname);
                });
              }
            } else {
              this.isAdmin = false;
              this.currentUserPfp = 'default';
            }
          });

          db.ref(`cards/${this.cardId}/offers`).orderByChild('createdAt').on('value', snapshot => {
            const arr = snapshotToArray(snapshot).reverse();

            arr.forEach(o => {
              if (o.uid) this.fetchUser(o.uid);
            });

            this.offers = arr.map(o => {
              console.log('Render offer:', o.id, o);
              console.log('Offer uid:', o.uid);
              const u = this.userCache[o.uid] || {};
              return {
                ...o,
                _replyDraft: '',
                resolvedPfp: u.pfp || 'default',
                resolvedName: u.name || u.username || 'User',
                resolvedHandle: this.getUserHandle(u),
                resolvedRole: u.role || 'user'
              };
            });

            this.calcPriceStats();
            this.offers.forEach(o => this.loadReplies(o));
          });

          db.ref(`cards/${this.cardId}/comments`).orderByChild('createdAt').limitToLast(50).on('value', snapshot => {
            const arr = snapshotToArray(snapshot).reverse();
            
            arr.forEach(c => {
              if (c.uid) this.fetchUser(c.uid);
            });

            this.comments = arr.map(c => {
              const u = this.userCache[c.uid] || {};
              return {
                ...c,
                resolvedPfp: u.pfp || 'default',
                resolvedName: u.name || u.username || 'User',
                resolvedHandle: this.getUserHandle(u),
                resolvedRole: u.role || 'user'
              };
            });
          });
        },

        getUserHandle(u) {
          if (!u) return '@user';
          if (u.handle) return u.handle.startsWith('@') ? u.handle : '@' + u.handle;
          const name = u.name || u.username || 'user';
          return '@' + name.toLowerCase().replace(/\s+/g, '');
        },

        userCache: {},
        watchedUids: new Set(),

        fetchUser(uid) {
          if (!uid) return;
          if (this.watchedUids.has(uid)) return;
          this.watchedUids.add(uid);

          db.ref(`users/${uid}`).on('value', snapshot => {
            const userData = snapshot.val() || {};
            console.log('Offer UID:', uid);
            console.log('User profile:', uid, snapshot.val());

            this.userCache[uid] = userData;
            
            if (this.currentUser && uid === this.currentUser.uid) {
              this.currentUserPfp = userData.pfp || 'default';
              this.isAdmin = userData.role === 'admin';
            }

            this.refreshListProfiles();
          });
        },

        refreshListProfiles() {
          this.offers = this.offers.map(o => {
            const u = this.userCache[o.uid] || {};
            const mappedReplies = (o.replies || []).map(r => {
              const ru = this.userCache[r.uid] || {};
              return {
                ...r,
                resolvedPfp: ru.pfp || 'default',
                resolvedName: ru.name || ru.username || 'User',
                resolvedHandle: this.getUserHandle(ru),
                resolvedRole: ru.role || 'user'
              };
            });
            return {
              ...o,
              resolvedPfp: u.pfp || 'default',
              resolvedName: u.name || u.username || 'User',
              resolvedHandle: this.getUserHandle(u),
              resolvedRole: u.role || 'user',
              replies: mappedReplies
            };
          });

          this.comments = this.comments.map(c => {
            const u = this.userCache[c.uid] || {};
            return {
              ...c,
              resolvedPfp: u.pfp || 'default',
              resolvedName: u.name || u.username || 'User',
              resolvedHandle: this.getUserHandle(u),
              resolvedRole: u.role || 'user'
            };
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

        async registerEmail() {
          try {
            await auth.createUserWithEmailAndPassword(this.emailInput, this.passInput);
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
            const replies = snapshotToArray(snapshot);

            replies.forEach(r => {
              if (r.uid) this.fetchUser(r.uid);
            });

            const idx = this.offers.findIndex(o => o.id === offer.id);
            if (idx > -1) {
              this.offers[idx].replies = replies.map(r => {
                const u = this.userCache[r.uid] || {};
                return {
                  ...r,
                  resolvedPfp: u.pfp || 'default',
                  resolvedName: u.name || u.username || 'User',
                  resolvedHandle: this.getUserHandle(u),
                  resolvedRole: u.role || 'user'
                };
              });
            }
          });
        },

        async replyOffer(offer) {
          if (!this.currentUser || !offer._replyDraft?.trim()) return;
          const u = this.currentUser;
          try {
            await db.ref(`cards/${this.cardId}/offers/${offer.id}/replies`).push({
              uid: u.uid,
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
              text: this.newComment.trim(),
              createdAt: FB_TIMESTAMP
            });
            this.newComment = '';
          } catch (e) { console.error(e); }
        },

        startChat(offer) {
          if (!this.currentUser) return;
          const roomId = [this.currentUser.uid, offer.uid].sort().join('_') + '_' + this.cardId;
          db.ref(`user_rooms/${this.currentUser.uid}/${roomId}`).once('value').then(snap => {
            if (!snap.exists()) {
              db.ref(`user_rooms/${this.currentUser.uid}/${roomId}`).set({
                name: offer.resolvedName || 'Penjual',
                avatar: offer.resolvedPfp || 'default',
                lastMsg: '',
                lastTs: Date.now(),
                partnerId: offer.uid,
              });
              // Do NOT write partner's user_rooms from client; partner should create/update their own entry.
            }
            window.location.href = `/chat?room=${roomId}&sellerId=${offer.uid}&cardId=${this.cardId}&offerId=${offer.id}`;
          });
        },

        toggleWish() { },

        timeAgo(ts) {
          if (!ts) return '';
          const s = Math.floor((Date.now() - ts) / 1000);
          if (s < 60) return 'Baru saja';
          if (s < 3600) return Math.floor(s / 60) + 'm lalu';
          if (s < 86400) return Math.floor(s / 3600) + 'j lalu';
          return Math.floor(s / 86400) + ' hari lalu';
        },

        async adminDeleteOffer(offerId) {
          if (!confirm('Hapus offer ini sebagai admin?')) return;
          try {
            const res = await fetch(`/admin/cards/${this.cardId}/offers/${offerId}`, {
              method: 'DELETE',
              headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            });
            const data = await res.json();
          } catch(e) { console.error(e); }
        },

        async adminDeleteReply(offerId, replyId) {
          if (!confirm('Hapus reply ini sebagai admin?')) return;
          try {
            const res = await fetch(`/admin/cards/${this.cardId}/offers/${offerId}/replies/${replyId}`, {
              method: 'DELETE',
              headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            });
            const data = await res.json();
          } catch(e) { console.error(e); }
        },

        async adminDeleteComment(commentId) {
          if (!confirm('Hapus komentar ini sebagai admin?')) return;
          try {
            const res = await fetch(`/admin/cards/${this.cardId}/comments/${commentId}`, {
              method: 'DELETE',
              headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            });
            const data = await res.json();
          } catch(e) { console.error(e); }
        }
      }));
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

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