{{-- resources/views/components/navbar.blade.php --}}

<style>
  .navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
    height: 72px;
    background: linear-gradient(135deg,
        #1b1535 0%, #1e1a40 30%, #2a1e5c 55%, #1a1535 80%, #110e28 100%);
    border-bottom: 1px solid rgba(130, 80, 200, 0.15);
    box-shadow: 0 4px 32px rgba(0, 0, 0, 0.5);
    position: sticky;
    top: 0;
    z-index: 100;
    overflow: hidden;
  }

  .navbar::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 60% 120% at 50% 50%,
        rgba(90, 50, 160, 0.18) 0%, transparent 70%);
    pointer-events: none;
    animation: navbar-pulse 3s ease-in-out infinite;
  }

  .navbar::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 60%;
    height: 100%;
    background: linear-gradient(120deg, transparent, rgba(167, 139, 250, 0.08), transparent);
    animation: navbar-scan 4s ease-in-out infinite;
    pointer-events: none;
  }

  .navbar-border {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 1px;
    background: linear-gradient(90deg,
        transparent,
        rgba(167, 139, 250, 0.8),
        rgba(244, 114, 182, 0.8),
        rgba(167, 139, 250, 0.8),
        transparent);
    background-size: 200% 100%;
    animation: border-shimmer 3s linear infinite;
  }

  .navbar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    position: relative;
    z-index: 1;
  }

  .brand-icon img {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(167, 139, 250, 0.6);
    animation: icon-glow 2.5s ease-in-out infinite;
    transition: transform 0.3s;
  }

  .brand-icon img:hover {
    transform: scale(1.1) rotate(5deg);
  }

  .brand-name {
    font-family: 'Freckle Face', cursive;
    font-size: 2rem;
    letter-spacing: 0.04em;
    color: #fff;
    user-select: none;
    animation: text-glow 2.5s ease-in-out infinite;
    text-shadow:
      -2px -2px 0 black, 2px -2px 0 black,
      -2px 2px 0 black, 2px 2px 0 black,
      -2px 0 0 black, 2px 0 0 black,
      0 -2px 0 black, 0 2px 0 black;
  }

  .navbar-right {
    display: flex;
    align-items: center;
    gap: 16px;
    position: relative;
    z-index: 1;
  }

  .nav-icon-chat img,
  .nav-icon-profile img {
    width: 32px;
    height: 32px;
    object-fit: contain;
    transition: transform 0.2s, opacity 0.2s;
    opacity: 0.85;
  }

  .nav-icon-chat img:hover,
  .nav-icon-profile img:hover {
    transform: scale(1.1);
    opacity: 1;
  }

  .profile-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(167, 139, 250, 0.3);
    border: 2px solid rgba(167, 139, 250, 0.5);
    transition: border-color 0.2s;
  }

  .profile-avatar:hover {
    border-color: rgba(244, 114, 182, 0.8);
  }

  .btn-login {
    font-family: 'Freckle Face', cursive;
    font-size: 0.95rem;
    letter-spacing: 1.5px;
    color: #fff;
    background: transparent;
    border: 2px solid rgba(167, 139, 250, 0.6);
    border-radius: 8px;
    padding: 7px 20px;
    text-decoration: none;
    cursor: pointer;
    position: relative;
    z-index: 1;
    overflow: hidden;
    transition: color 0.25s, border-color 0.25s, transform 0.15s;
    white-space: nowrap;
  }

  /* Scanline retro background */
  .btn-login::before {
    content: '';
    position: absolute;
    inset: 0;
    background: repeating-linear-gradient(0deg,
        transparent,
        transparent 2px,
        rgba(167, 139, 250, 0.04) 2px,
        rgba(167, 139, 250, 0.04) 4px);
    pointer-events: none;
    z-index: -1;
  }

  /* Fill sweep on hover */
  .btn-login::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg,
        rgba(167, 139, 250, 0.25) 0%,
        rgba(244, 114, 182, 0.2) 100%);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.25s ease;
    z-index: -1;
  }

  .btn-login:hover::after {
    transform: scaleX(1);
  }

  .btn-login:hover {
    border-color: rgba(244, 114, 182, 0.8);
    transform: translateY(-2px);
  }

  .btn-login:active {
    transform: translateY(0) scale(0.97);
  }

  /* Animasi kedip border saat idle */
  @keyframes btn-blink {

    0%,
    100% {
      border-color: rgba(167, 139, 250, 0.6);
    }

    50% {
      border-color: rgba(244, 114, 182, 0.9);
    }
  }

  .btn-login {
    animation: btn-blink 2.5s ease-in-out infinite;
  }

  .btn-login:hover {
    animation: none;
  }



  @keyframes navbar-pulse {

    0%,
    100% {
      opacity: 0.6;
    }

    50% {
      opacity: 1;
    }
  }

  @keyframes navbar-scan {
    0% {
      left: -100%;
    }

    100% {
      left: 200%;
    }
  }

  @keyframes border-shimmer {
    0% {
      background-position: 200% 0;
    }

    100% {
      background-position: -200% 0;
    }
  }

  @keyframes icon-glow {

    0%,
    100% {
      box-shadow: 0 0 8px rgba(167, 139, 250, 0.5), 0 0 16px rgba(167, 139, 250, 0.2);
      border-color: rgba(167, 139, 250, 0.6);
    }

    50% {
      box-shadow: 0 0 16px rgba(244, 114, 182, 0.8), 0 0 32px rgba(244, 114, 182, 0.3);
      border-color: rgba(244, 114, 182, 0.9);
    }
  }

  @keyframes text-glow {

    0%,
    100% {
      text-shadow:
        -2px -2px 0 black, 2px -2px 0 black,
        -2px 2px 0 black, 2px 2px 0 black,
        -2px 0 0 black, 2px 0 0 black,
        0 -2px 0 black, 0 2px 0 black,
        0 0 12px rgba(167, 139, 250, 0);
    }

    50% {
      text-shadow:
        -2px -2px 0 black, 2px -2px 0 black,
        -2px 2px 0 black, 2px 2px 0 black,
        -2px 0 0 black, 2px 0 0 black,
        0 -2px 0 black, 0 2px 0 black,
        0 0 16px rgba(244, 114, 182, 0.8),
        0 0 32px rgba(244, 114, 182, 0.4);
    }




  }

  .btn-logout {
    font-family: 'Freckle Face', cursive;
    font-size: 0.78rem;
    letter-spacing: 1px;
    color: var(--muted);
    background: transparent;
    border: 1px solid rgba(155, 110, 224, 0.25);
    border-radius: 8px;
    padding: 6px 14px;
    cursor: pointer;
    transition: all 0.2s;
  }

  .btn-logout:hover {
    color: #ff5e7e;
    border-color: #ff5e7e;
    background: rgba(255, 94, 126, 0.1);
  }
</style>
@php
  use App\Models\userLogin;
@endphp

<nav class="navbar sticky top-0 z-3">
  <div class="navbar-border"></div>

  <a class="navbar-brand" href="{{ url('/') }}">
    <div class="brand-icon">
      <img src="{{ asset('images/pfp.png') }}" alt="Pokelu Logo">
    </div>
    <span class="brand-name">POKELU</span>
  </a>

  <div class="navbar-right">
    <a class="nav-icon-chat" href="{{ route('chat') }}" title="Chat">
      <img src="{{ asset('images/icon_chat.png') }}" alt="Chat">
    </a>
    {{-- Tambah di atas card, setelah .back-btn --}}
    @if (userLogin::isLogin())
      <div style="display:flex;align-items:center;gap:12px;position:relative;z-index:1;">
        <a class="nav-icon-profile" href="{{ route('profile') }}" title="Profil">
          <div class="profile-avatar"></div>
        </a>
        <form method="POST" action="{{ route('logout') }}" id="logoutForm">
          @csrf
          <button type="submit" class="btn-logout" onclick="handleLogout(event)">Keluar</button>
        </form>
      </div>
    @else
      <a class="btn-login" href="{{ route('login') }}">► LOGIN</a>
    @endif
    <a href="">{{ userLogin::isLogin() }}</a>
  </div>
</nav>