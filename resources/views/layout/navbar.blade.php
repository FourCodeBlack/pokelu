<?php
// navbar.php - Pokelu Navbar
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pokelu Navbar</title>
  <link href="https://fonts.googleapis.com/css2?family=Freckle+Face&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background-color: #1a1a2e;
      font-family: 'Freckle Face', cursive;
      color: #fff;
    }

    /* ── Navbar ── */
    .navbar {
      display: flex;
      align-items: center;
      padding: 0 24px;
      height: 72px;
      background: linear-gradient(
        135deg,
        #1b1535 0%,
        #1e1a40 30%,
        #2a1e5c 55%,
        #1a1535 80%,
        #110e28 100%
      );
      border-bottom: 1px solid rgba(130, 80, 200, 0.15);
      box-shadow: 0 4px 32px rgba(0, 0, 0, 0.5);
      position: relative;
      overflow: hidden;
    }

    /* Glow overlay berdenyut */
    .navbar::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(
        ellipse 60% 120% at 50% 50%,
        rgba(90, 50, 160, 0.18) 0%,
        transparent 70%
      );
      pointer-events: none;
      animation: navbar-pulse 3s ease-in-out infinite;
    }

    /* Garis scan bergerak kiri ke kanan */
    .navbar::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 60%;
      height: 100%;
      background: linear-gradient(
        120deg,
        transparent,
        rgba(167, 139, 250, 0.08),
        transparent
      );
      animation: navbar-scan 4s ease-in-out infinite;
      pointer-events: none;
    }

    @keyframes navbar-pulse {
      0%, 100% { opacity: 0.6; }
      50%       { opacity: 1;   }
    }

    @keyframes navbar-scan {
      0%   { left: -100%; }
      100% { left: 200%;  }
    }

    /* Garis bawah berkilau */
    .navbar-border {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 1px;
      background: linear-gradient(
        90deg,
        transparent,
        rgba(167, 139, 250, 0.8),
        rgba(244, 114, 182, 0.8),
        rgba(167, 139, 250, 0.8),
        transparent
      );
      background-size: 200% 100%;
      animation: border-shimmer 3s linear infinite;
    }

    @keyframes border-shimmer {
      0%   { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }

    /* ── Logo area ── */
    .navbar-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      position: relative;
      z-index: 1;
    }

    /* ── Profile icon ── */
    .brand-icon {
      width: 46px;
      height: 46px;
      position: relative;
      flex-shrink: 0;
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

    @keyframes icon-glow {
      0%, 100% {
        box-shadow:
          0 0 8px rgba(167, 139, 250, 0.5),
          0 0 16px rgba(167, 139, 250, 0.2);
        border-color: rgba(167, 139, 250, 0.6);
      }
      50% {
        box-shadow:
          0 0 16px rgba(244, 114, 182, 0.8),
          0 0 32px rgba(244, 114, 182, 0.3);
        border-color: rgba(244, 114, 182, 0.9);
      }
    }

    /* ── Brand name ── */
    .brand-name {
      font-family: 'Freckle Face', cursive;
      font-size: 2rem;
      letter-spacing: 0.04em;
      color: #ffffff;
      user-select: none;
      position: relative;
      animation: text-glow 2.5s ease-in-out infinite;
      text-shadow:
        -2px -2px 0 black,
         2px -2px 0 black,
        -2px  2px 0 black,
         2px  2px 0 black,
        -2px  0   0 black,
         2px  0   0 black,
         0   -2px 0 black,
         0    2px 0 black;
    }

    @keyframes text-glow {
      0%, 100% {
        text-shadow:
          -2px -2px 0 black,
           2px -2px 0 black,
          -2px  2px 0 black,
           2px  2px 0 black,
          -2px  0   0 black,
           2px  0   0 black,
           0   -2px 0 black,
           0    2px 0 black,
           0 0 12px rgba(167, 139, 250, 0);
      }
      50% {
        text-shadow:
          -2px -2px 0 black,
           2px -2px 0 black,
          -2px  2px 0 black,
           2px  2px 0 black,
          -2px  0   0 black,
           2px  0   0 black,
           0   -2px 0 black,
           0    2px 0 black,
           0 0 16px rgba(244, 114, 182, 0.8),
           0 0 32px rgba(244, 114, 182, 0.4);
      }
    }
  </style>
</head>
<body>

<?php
// ── PHP renders the navbar ───────────────────────────────
$brand_name = "POKELU";
?>

<nav class="navbar position-sticky top-0 z-3">

  <!-- Garis bawah berkilau -->
  <div class="navbar-border"></div>

  <!-- Brand / Logo -->
  <a class="navbar-brand" href="#">

    <!-- Profile picture -->
    <div class="brand-icon">
      <img src="{{ asset('images/pfp.png') }}" alt="Logo">
    </div>

    <!-- Brand text dari PHP variable -->
    <span class="brand-name"><?= htmlspecialchars($brand_name) ?></span>

  </a>

</nav>

    <nav class="navbar position-sticky top-0">
        <div class="navbar-border"></div>

        <a class="navbar-brand" href="{{ url('/') }}">
            <div class="brand-icon">
                <img src="{{ asset('images/pfp.png') }}" alt="Logo">
            </div>
            <span class="brand-name">POKELU</span>
        </a>

        <div class="navbar-right">
            <a class="nav-icon-chat" href="{{ route('chat') }}" title="Chat">
                <img src="{{ asset('images/icon_chat.png') }}" alt="Chat">
            </a>
            <a class="nav-icon-profile" href="{{ route('profile') }}" title="Profil">
                <div class="profile-avatar"></div>
            </a>
        </div>
    </nav>


</body>
</html>