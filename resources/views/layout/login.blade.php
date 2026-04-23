<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login – Pokelu</title>
  <link href="https://fonts.googleapis.com/css2?family=Freckle+Face&family=Francois+One&display=swap"
    rel="stylesheet" />
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      --pink: #ff2d78;
      --pink-light: #ffb3d1;
      --pink-glow: rgba(255, 45, 120, 0.6);
      --dark: #0d0a14;
      --card-bg: rgba(13, 8, 22, 0.88);
      --border: rgba(255, 45, 120, 0.35);
      --input-bg: rgba(255, 180, 210, 0.18);
    }

    html,
    body {
      width: 100%;
      height: 100%;
      overflow: hidden;
    }

    body {
      font-family: 'Freckle Face', cursive;
      background: var(--dark);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    .bg {
      position: fixed;
      inset: 0;
      z-index: 0;
      background: url('{{ asset("images/background_login.webp") }}') center / cover no-repeat;
    }

    .bg::after {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(ellipse at 50% 60%, rgba(80, 0, 60, 0.45) 0%, rgba(13, 8, 22, 0.72) 100%);
    }

    .scanlines {
      position: fixed;
      inset: 0;
      z-index: 1;
      background: repeating-linear-gradient(to bottom, transparent 0px, transparent 3px, rgba(0, 0, 0, 0.08) 3px, rgba(0, 0, 0, 0.08) 4px);
      pointer-events: none;
      animation: scanMove 8s linear infinite;
    }

    @keyframes scanMove {
      from {
        background-position: 0 0;
      }

      to {
        background-position: 0 100px;
      }
    }

    .particles {
      position: fixed;
      inset: 0;
      z-index: 2;
      pointer-events: none;
      overflow: hidden;
    }

    .particle {
      position: absolute;
      border-radius: 50%;
      background: var(--pink);
      opacity: 0;
      animation: floatUp var(--dur, 6s) ease-in var(--delay, 0s) infinite;
    }

    @keyframes floatUp {
      0% {
        transform: translateY(110vh) scale(0);
        opacity: 0;
      }

      10% {
        opacity: 0.7;
      }

      90% {
        opacity: 0.3;
      }

      100% {
        transform: translateY(-10vh) scale(1.5);
        opacity: 0;
      }
    }

    .corner {
      position: fixed;
      z-index: 3;
      width: 60px;
      height: 60px;
      pointer-events: none;
      animation: cornerPulse 3s ease-in-out infinite;
    }

    .corner--tl {
      top: 18px;
      left: 18px;
      border-top: 2px solid var(--pink);
      border-left: 2px solid var(--pink);
    }

    .corner--tr {
      top: 18px;
      right: 18px;
      border-top: 2px solid var(--pink);
      border-right: 2px solid var(--pink);
    }

    .corner--bl {
      bottom: 18px;
      left: 18px;
      border-bottom: 2px solid var(--pink);
      border-left: 2px solid var(--pink);
    }

    .corner--br {
      bottom: 18px;
      right: 18px;
      border-bottom: 2px solid var(--pink);
      border-right: 2px solid var(--pink);
    }

    @keyframes cornerPulse {

      0%,
      100% {
        opacity: 0.5;
        box-shadow: 0 0 6px var(--pink-glow);
      }

      50% {
        opacity: 1;
        box-shadow: 0 0 18px var(--pink-glow);
      }
    }

    .back-btn {
      position: fixed;
      top: 16px;
      left: 16px;
      z-index: 10;
      width: 42px;
      height: 42px;
      border: 2px solid var(--border);
      border-radius: 10px;
      background: rgba(13, 8, 22, 0.72);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: border-color .25s, box-shadow .25s, transform .15s;
      backdrop-filter: blur(8px);
      text-decoration: none;
    }

    .back-btn img {
      width: 22px;
      height: 22px;
      object-fit: contain;
      filter: brightness(0) invert(1) sepia(1) saturate(3) hue-rotate(290deg);
      transition: filter .25s, transform .2s;
    }

    .back-btn:hover {
      border-color: var(--pink);
      box-shadow: 0 0 16px var(--pink-glow);
      transform: translateY(-1px);
    }

    .back-btn:hover img {
      filter: brightness(0) invert(1);
      transform: translateX(-2px);
    }

    .card {
      position: relative;
      z-index: 5;
      width: min(390px, 92vw);
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 36px 34px 30px;
      backdrop-filter: blur(18px);
      box-shadow: 0 0 0 1px rgba(255, 45, 120, 0.12), 0 8px 48px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.07);
      animation: cardIn .7s cubic-bezier(0.22, 1, 0.36, 1) both;
    }

    @keyframes cardIn {
      from {
        opacity: 0;
        transform: translateY(40px) scale(0.95);
      }

      to {
        opacity: 1;
        transform: none;
      }
    }

    .card::before {
      content: '';
      position: absolute;
      inset: -1px;
      border-radius: 21px;
      background: linear-gradient(135deg, var(--pink), transparent 40%, transparent 60%, var(--pink));
      z-index: -1;
      animation: borderPulse 4s linear infinite;
    }

    @keyframes borderPulse {

      0%,
      100% {
        opacity: 0.35;
      }

      50% {
        opacity: 0.75;
      }
    }

    .hud-dot {
      position: absolute;
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: var(--pink);
      box-shadow: 0 0 8px var(--pink-glow);
      animation: hudBlink 2s ease-in-out infinite;
    }

    .hud-dot:nth-child(1) {
      top: 14px;
      left: 14px;
    }

    .hud-dot:nth-child(2) {
      top: 14px;
      right: 14px;
      animation-delay: .6s;
    }

    @keyframes hudBlink {

      0%,
      100% {
        opacity: 1;
        transform: scale(1);
      }

      50% {
        opacity: .3;
        transform: scale(.6);
      }
    }

    .title {
      font-family: 'Freckle Face', cursive;
      font-size: 1.9rem;
      color: #fff;
      text-align: center;
      letter-spacing: .12em;
      margin-bottom: 20px;
      text-shadow: 0 0 12px var(--pink-glow), 0 0 28px var(--pink-glow);
      animation: glitch 6s infinite;
    }

    @keyframes glitch {

      0%,
      94%,
      100% {
        text-shadow: 0 0 12px var(--pink-glow), 0 0 28px var(--pink-glow);
        transform: none;
      }

      95% {
        text-shadow: -3px 0 #0ff, 3px 0 var(--pink);
        transform: skewX(-4deg);
      }

      97% {
        text-shadow: 3px 0 #0ff, -3px 0 var(--pink);
        transform: skewX(3deg);
      }
    }

    /* ── Tabs ── */
    .tabs {
      display: flex;
      margin-bottom: 22px;
      border-radius: 10px;
      overflow: hidden;
      border: 1px solid var(--border);
    }

    .tab-btn {
      flex: 1;
      padding: 10px;
      background: transparent;
      border: none;
      cursor: pointer;
      font-family: 'Freckle Face', cursive;
      font-size: .95rem;
      color: rgba(255, 179, 209, .45);
      letter-spacing: .06em;
      transition: background .2s, color .2s;
    }

    .tab-btn.active {
      background: rgba(255, 45, 120, .18);
      color: #fff;
      text-shadow: 0 0 8px var(--pink-glow);
    }

    /* ── Redirect notice ── */
    .redirect-notice {
      background: rgba(255, 45, 120, .08);
      border: 1px solid rgba(255, 45, 120, .25);
      border-radius: 8px;
      color: var(--pink-light);
      font-family: 'Francois One', sans-serif;
      font-size: .8rem;
      padding: 8px 12px;
      margin-bottom: 14px;
      text-align: center;
      display: none;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }

    .label {
      font-family: 'Freckle Face', cursive;
      font-size: .9rem;
      color: var(--pink-light);
      margin-bottom: 6px;
      letter-spacing: .05em;
      display: block;
    }

    .field {
      margin-bottom: 14px;
    }

    .input-wrap input {
      width: 100%;
      padding: 11px 16px;
      background: var(--input-bg);
      border: 1.5px solid rgba(255, 45, 120, .3);
      border-radius: 10px;
      color: #fff;
      font-family: 'Freckle Face', cursive;
      font-size: .95rem;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
    }

    .input-wrap input::placeholder {
      color: rgba(255, 179, 209, .45);
    }

    .input-wrap input:focus {
      border-color: var(--pink);
      box-shadow: 0 0 0 3px rgba(255, 45, 120, .18);
    }

    /* ── Alerts ── */
    .alert {
      border-radius: 8px;
      font-family: 'Francois One', sans-serif;
      font-size: .82rem;
      padding: 9px 14px;
      margin-bottom: 12px;
      text-align: center;
      display: none;
      animation: fadeIn .3s ease;
    }

    .alert.error {
      background: rgba(255, 45, 120, .15);
      border: 1px solid rgba(255, 45, 120, .4);
      color: var(--pink-light);
      display: block;
    }

    .alert.success {
      background: rgba(0, 200, 100, .12);
      border: 1px solid rgba(0, 200, 100, .3);
      color: #6fffc0;
      display: block;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-6px);
      }

      to {
        opacity: 1;
      }
    }

    .btn-confirm {
      width: 100%;
      padding: 13px;
      margin-top: 4px;
      background: linear-gradient(90deg, #e0176b, var(--pink), #ff6fa8);
      background-size: 200% 100%;
      border: none;
      border-radius: 10px;
      color: #fff;
      font-family: 'Freckle Face', cursive;
      font-size: 1.05rem;
      letter-spacing: .08em;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      transition: transform .15s, box-shadow .15s, background-position .4s;
      box-shadow: 0 4px 22px rgba(255, 45, 120, .45);
    }

    .btn-confirm:hover:not(:disabled) {
      background-position: 100% 0;
      transform: translateY(-2px);
      box-shadow: 0 8px 30px rgba(255, 45, 120, .65);
    }

    .btn-confirm:disabled {
      opacity: .55;
      cursor: not-allowed;
    }

    .btn-confirm::before {
      content: '';
      position: absolute;
      top: 0;
      left: -75%;
      width: 50%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .22), transparent);
      transform: skewX(-20deg);
      animation: btnShine 3s ease-in-out infinite;
    }

    @keyframes btnShine {

      0%,
      60%,
      100% {
        left: -75%;
        opacity: 0;
      }

      30% {
        left: 130%;
        opacity: 1;
      }
    }

    .divider {
      display: flex;
      align-items: center;
      gap: 12px;
      margin: 18px 0;
    }

    .divider-line {
      flex: 1;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(255, 45, 120, .4), transparent);
    }

    .divider-text {
      font-family: 'Francois One', sans-serif;
      font-size: .85rem;
      color: rgba(255, 179, 209, .7);
      letter-spacing: .1em;
    }

    .btn-google {
      width: 100%;
      padding: 12px;
      background: rgba(255, 255, 255, .05);
      border: 1.5px solid rgba(255, 45, 120, .3);
      border-radius: 10px;
      color: #fff;
      font-family: 'Francois One', sans-serif;
      font-size: .95rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      transition: all .25s;
    }

    .btn-google:hover {
      border-color: var(--pink);
      background: rgba(255, 45, 120, .1);
      box-shadow: 0 0 18px rgba(255, 45, 120, .25);
      transform: translateY(-1px);
    }

    .btn-google svg {
      width: 20px;
      height: 20px;
      flex-shrink: 0;
    }

    .spinner {
      display: inline-block;
      width: 14px;
      height: 14px;
      border: 2px solid rgba(255, 255, 255, .3);
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin .6s linear infinite;
      vertical-align: middle;
      margin-right: 6px;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }
  </style>
</head>

<body>

  <div class="bg"></div>
  <div class="scanlines"></div>
  <div class="particles" id="particles"></div>
  <div class="corner corner--tl"></div>
  <div class="corner corner--tr"></div>
  <div class="corner corner--bl"></div>
  <div class="corner corner--br"></div>

  <a href="{{ route('jelajah') }}" class="back-btn" title="Kembali ke Jelajah">
    <img src="{{ asset('images/icon_back.png') }}" alt="Kembali" />
  </a>

  <div class="card">
    <div class="hud-dot"></div>
    <div class="hud-dot"></div>

    <h1 class="title">POKELU</h1>

    <div class="redirect-notice" id="redirectNotice">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10" />
        <line x1="12" y1="8" x2="12" y2="12" />
        <line x1="12" y1="16" x2="12.01" y2="16" />
      </svg>
      Login dulu untuk ikut diskusi kartu ini
    </div>

    {{-- Tab: LOGIN | REGISTER --}}
    <div class="tabs">
      <button class="tab-btn active" id="tabLogin" onclick="switchTab('login')">LOGIN</button>
      <button class="tab-btn" id="tabRegister" onclick="switchTab('register')">REGISTER</button>
    </div>

    {{-- Alert --}}
    <div id="alertBox" class="alert"></div>

    {{-- Form Login --}}
    <div id="formLogin">
      <div class="field">
        <label class="label">Email</label>
        <div class="input-wrap">
          <input type="email" id="loginEmail" placeholder="your@email.com" autocomplete="email" />
        </div>
      </div>
      <div class="field">
        <label class="label">Password</label>
        <div class="input-wrap">
          <input type="password" id="loginPassword" placeholder="••••••••" autocomplete="current-password" />
        </div>
      </div>
      <button class="btn-confirm" id="btnLogin" onclick="doLogin()">Masuk</button>
    </div>

    {{-- Form Register --}}
    <div id="formRegister" style="display:none">
      <div class="field">
        <label class="label">Nama</label>
        <div class="input-wrap">
          <input type="text" id="regName" placeholder="Nama kamu" autocomplete="name" />
        </div>
      </div>
      <div class="field">
        <label class="label">Email</label>
        <div class="input-wrap">
          <input type="email" id="regEmail" placeholder="your@email.com" autocomplete="email" />
        </div>
      </div>
      <div class="field">
        <label class="label">Password</label>
        <div class="input-wrap">
          <input type="password" id="regPassword" placeholder="Min. 6 karakter" autocomplete="new-password" />
        </div>
      </div>
      <button class="btn-confirm" id="btnRegister" onclick="doRegister()">Daftar</button>
    </div>

    <div class="divider">
      <div class="divider-line"></div>
      <span class="divider-text">Or</span>
      <div class="divider-line"></div>
    </div>

    <button class="btn-google" onclick="doGoogle()">
      <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
        <path fill="#EA4335"
          d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
        <path fill="#4285F4"
          d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
        <path fill="#FBBC05"
          d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z" />
        <path fill="#34A853"
          d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.36-8.16 2.36-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
      </svg>
      Continue with Google
    </button>
  </div>

  <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js"></script>
  <script>
    firebase.initializeApp({
      apiKey: "AIzaSyDr7r6PrzHWUX6i_5bMnL9I7eZMcx2AS_s",
      authDomain: "pokelu-project.firebaseapp.com",
      databaseURL: "https://pokelu-project-default-rtdb.asia-southeast1.firebasedatabase.app",
      projectId: "pokelu-project",
      storageBucket: "pokelu-project.firebasestorage.app",
      messagingSenderId: "210207641471",
      appId: "1:210207641471:web:aff0df7b7b3acb0ce2d44a",
    });
    const auth = firebase.auth();

    // Ambil redirect target dari URL query
    const redirectTo = new URLSearchParams(window.location.search).get('redirect') || '/explore';

    // Tampilkan notice kalau dari halaman kartu
    if (redirectTo.startsWith('/card/')) {
      const n = document.getElementById('redirectNotice');
      n.style.display = 'flex';
    }

    // Kalau sudah login, langsung redirect
    auth.onAuthStateChanged(async user => {
      if (user) {
        await syncToLaravel(user);
        window.location.href = redirectTo;
      }
    });

    // ── Tab switch ──
    function switchTab(tab) {
      const isLogin = tab === 'login';
      document.getElementById('formLogin').style.display = isLogin ? 'block' : 'none';
      document.getElementById('formRegister').style.display = isLogin ? 'none' : 'block';
      document.getElementById('tabLogin').classList.toggle('active', isLogin);
      document.getElementById('tabRegister').classList.toggle('active', !isLogin);
      clearAlert();
    }

    // ── Alert helpers ──
    function showAlert(msg, type = 'error') {
      const el = document.getElementById('alertBox');
      el.textContent = msg;
      el.className = 'alert ' + type;
    }
    function clearAlert() {
      const el = document.getElementById('alertBox');
      el.className = 'alert';
      el.textContent = '';
    }

    // ── Loading state ──
    function setLoading(id, on) {
      const btn = document.getElementById(id);
      btn.disabled = on;
      btn.innerHTML = on
        ? '<span class="spinner"></span> Mohon tunggu…'
        : { btnLogin: 'Masuk', btnRegister: 'Daftar' }[id];
    }

    // ── Login email ──
    async function doLogin() {
      clearAlert();
      const email = document.getElementById('loginEmail').value.trim();
      const password = document.getElementById('loginPassword').value;
      if (!email || !password) { showAlert('Email dan password wajib diisi.'); return; }

      setLoading('btnLogin', true);
      try {
        await auth.signInWithEmailAndPassword(email, password);
        const user = auth.currentUser;
        await syncToLaravel(user);
        showAlert('Login berhasil! Mengalihkan…', 'success');
        setTimeout(() => window.location.href = redirectTo, 800);
      } catch (e) {
        showAlert(errMsg(e.code));
      } finally {
        setLoading('btnLogin', false);
      }
    }

    // ── Register email ──
    async function doRegister() {
      clearAlert();
      const name = document.getElementById('regName').value.trim();
      const email = document.getElementById('regEmail').value.trim();
      const password = document.getElementById('regPassword').value;
      if (!name) { showAlert('Nama wajib diisi.'); return; }
      if (!email) { showAlert('Email wajib diisi.'); return; }
      if (password.length < 6) { showAlert('Password minimal 6 karakter.'); return; }

      setLoading('btnRegister', true);
      try {
        const cred = await auth.createUserWithEmailAndPassword(email, password);
        await syncToLaravel(cred.user);
        await cred.user.updateProfile({ displayName: name });
        showAlert('Akun berhasil dibuat! Mengalihkan…', 'success');
        setTimeout(() => window.location.href = redirectTo, 800);
      } catch (e) {
        showAlert(errMsg(e.code));
      } finally {
        setLoading('btnRegister', false);
      }
    }

    // ── Login Google ──
    async function doGoogle() {
      clearAlert();
      try {
        await auth.signInWithPopup(new firebase.auth.GoogleAuthProvider());
        const result = await auth.signInWithPopup(new firebase.auth.GoogleAuthProvider());
        await syncToLaravel(result.user);
        window.location.href = redirectTo;
        window.location.href = redirectTo;
      } catch (e) {
        if (e.code !== 'auth/popup-closed-by-user') showAlert(errMsg(e.code));
      }
    }

    // ── Error messages Indonesia ──
    function errMsg(code) {
      return ({
        'auth/user-not-found': 'Email tidak terdaftar. Silakan daftar dulu.',
        'auth/wrong-password': 'Password salah.',
        'auth/invalid-credential': 'Email atau password salah.',
        'auth/email-already-in-use': 'Email sudah dipakai, coba login.',
        'auth/weak-password': 'Password terlalu lemah, minimal 6 karakter.',
        'auth/invalid-email': 'Format email tidak valid.',
        'auth/too-many-requests': 'Terlalu banyak percobaan. Coba lagi nanti.',
        'auth/network-request-failed': 'Gagal terhubung. Periksa koneksi internet.',
      })[code] || 'Terjadi kesalahan, silakan coba lagi.';
    }

    // ── Enter key ──
    document.getElementById('loginPassword').addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });
    document.getElementById('regPassword').addEventListener('keydown', e => { if (e.key === 'Enter') doRegister(); });

    // ── Particles ──
    const pc = document.getElementById('particles');
    for (let i = 0; i < 28; i++) {
      const p = document.createElement('div');
      p.className = 'particle';
      const s = Math.random() * 3 + 1.5;
      p.style.cssText = `left:${Math.random() * 100}%;width:${s}px;height:${s}px;--dur:${(Math.random() * 8 + 5).toFixed(1)}s;--delay:${(Math.random() * 8).toFixed(1)}s;`;
      pc.appendChild(p);
    }

    async function syncToLaravel(user) {
      await fetch('{{ route("auth.firebase") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({
          token: await user.getIdToken(),
          uid: user.uid,
          name: user.displayName,
          email: user.email,
          avatar: user.photoURL,
        }),
      });
    }
  </script>
</body>

</html>