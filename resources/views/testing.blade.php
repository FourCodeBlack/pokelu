<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pokemon Card Tilt Effect</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    min-height: 100vh;
    background: #0d0d1a;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 60px;
    font-family: 'Segoe UI', sans-serif;
    color: white;
  }

  h2 {
    font-size: 13px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #666;
  }

  .cards-row {
    display: flex;
    gap: 40px;
    align-items: center;
  }

  /* ─── CARD WRAPPER ─── */
  .card-wrap {
    perspective: 600px;
    cursor: pointer;
  }

  .card {
    width: 180px;
    height: 252px;
    border-radius: 12px;
    position: relative;
    transform-style: preserve-3d;
    transition: transform 0.1s ease, box-shadow 0.1s ease;
    will-change: transform;
    overflow: hidden;
  }

  .card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px;
    display: block;
    pointer-events: none;
    user-select: none;
  }

  /* glare overlay */
  .card .glare {
    position: absolute;
    inset: 0;
    border-radius: 12px;
    background: radial-gradient(
      circle at 50% 50%,
      rgba(255,255,255,0.35) 0%,
      rgba(255,255,255,0.05) 40%,
      transparent 70%
    );
    opacity: 0;
    transition: opacity 0.2s;
    pointer-events: none;
    mix-blend-mode: screen;
  }

  .card-wrap:hover .glare {
    opacity: 1;
  }

  /* holo shimmer */
  .card .holo {
    position: absolute;
    inset: 0;
    border-radius: 12px;
    background: linear-gradient(
      115deg,
      transparent 20%,
      rgba(255,50,150,0.15) 30%,
      rgba(50,150,255,0.15) 40%,
      rgba(255,220,50,0.1) 50%,
      rgba(50,255,150,0.15) 60%,
      transparent 70%
    );
    background-size: 200% 200%;
    opacity: 0;
    transition: opacity 0.3s;
    pointer-events: none;
    mix-blend-mode: color-dodge;
  }

  .card-wrap:hover .holo {
    opacity: 1;
  }

  /* shadow */
  .card-wrap:hover .card {
    box-shadow:
      0 20px 60px rgba(0,0,0,0.6),
      0 0 30px rgba(150,100,255,0.2);
  }

  /* ─── PLACEHOLDER CARD (no image) ─── */
  .card.placeholder {
    background: linear-gradient(135deg, #1a1a3e 0%, #2d1b4e 50%, #1a1a3e 100%);
    border: 1px solid rgba(150,100,255,0.3);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  .card.placeholder .poke-icon {
    font-size: 64px;
    filter: drop-shadow(0 0 12px rgba(150,100,255,0.8));
  }

  .card.placeholder .poke-name {
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 1px;
    color: rgba(255,255,255,0.7);
  }

  .card.placeholder .poke-hp {
    font-size: 11px;
    color: rgba(255,100,100,0.8);
    letter-spacing: 1px;
  }

  /* ─── CODE SNIPPET ─── */
  .code-box {
    background: #111;
    border: 1px solid #222;
    border-radius: 10px;
    padding: 20px 24px;
    max-width: 520px;
    width: 100%;
  }

  .code-box pre {
    font-family: 'Courier New', monospace;
    font-size: 12px;
    color: #aaa;
    line-height: 1.7;
    overflow-x: auto;
  }

  .code-box pre .kw  { color: #c678dd; }
  .code-box pre .fn  { color: #61afef; }
  .code-box pre .str { color: #98c379; }
  .code-box pre .cm  { color: #5c6370; font-style: italic; }
  .code-box pre .num { color: #d19a66; }
</style>
</head>
<body>

<h2>Pokemon Card — Tilt Hover Effect</h2>

<div class="cards-row">
  <!-- Card 1: placeholder styled -->
  <div class="card-wrap" id="card1">
    <div class="card placeholder">
      <div class="poke-icon">👻</div>
      <div class="poke-name">Gengar</div>
      <div class="poke-hp">HP 120</div>
      <div class="glare"></div>
      <div class="holo"></div>
    </div>
  </div>

  <!-- Card 2 -->
  <div class="card-wrap" id="card2">
    <div class="card placeholder" style="background: linear-gradient(135deg, #1a2e1a 0%, #1b4e2d 50%, #1a2e1a 100%); border-color: rgba(100,255,150,0.3);">
      <div class="poke-icon">🌿</div>
      <div class="poke-name">Bulbasaur</div>
      <div class="poke-hp">HP 90</div>
      <div class="glare"></div>
      <div class="holo"></div>
    </div>
  </div>

  <!-- Card 3 -->
  <div class="card-wrap" id="card3">
    <div class="card placeholder" style="background: linear-gradient(135deg, #2e1a1a 0%, #4e1b1b 50%, #2e1a1a 100%); border-color: rgba(255,100,100,0.3);">
      <div class="poke-icon">🔥</div>
      <div class="poke-name">Charizard</div>
      <div class="poke-hp">HP 150</div>
      <div class="glare"></div>
      <div class="holo"></div>
    </div>
  </div>
</div>

<!-- Code snippet -->
<div class="code-box">
  <pre><span class="cm">// Pakai ini di blade untuk tiap kartu</span>
<span class="kw">const</span> wrap = document.<span class="fn">querySelector</span>(<span class="str">'.card-wrap'</span>);

wrap.<span class="fn">addEventListener</span>(<span class="str">'mousemove'</span>, (e) => {
  <span class="kw">const</span> rect = wrap.<span class="fn">getBoundingClientRect</span>();
  <span class="kw">const</span> x = (e.clientX - rect.left) / rect.width;
  <span class="kw">const</span> y = (e.clientY - rect.top) / rect.height;

  <span class="kw">const</span> rotateX = (<span class="num">0.5</span> - y) * <span class="num">20</span>; <span class="cm">// -10 to 10</span>
  <span class="kw">const</span> rotateY = (x - <span class="num">0.5</span>) * <span class="num">20</span>; <span class="cm">// -10 to 10</span>

  card.style.transform =
    <span class="str">`rotateX(<span class="num">${rotateX}</span>deg) rotateY(<span class="num">${rotateY}</span>deg)`</span>;
});</pre>
</div>

<script>
  document.querySelectorAll('.card-wrap').forEach(wrap => {
    const card = wrap.querySelector('.card');
    const glare = wrap.querySelector('.glare');
    const holo = wrap.querySelector('.holo');

    wrap.addEventListener('mousemove', (e) => {
      const rect = wrap.getBoundingClientRect();
      const x = (e.clientX - rect.left) / rect.width;
      const y = (e.clientY - rect.top) / rect.height;

      const rotateX = (0.5 - y) * 22;
      const rotateY = (x - 0.5) * 22;

      card.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.05)`;

      // geser glare sesuai posisi mouse
      glare.style.background = `radial-gradient(
        circle at ${x * 100}% ${y * 100}%,
        rgba(255,255,255,0.4) 0%,
        rgba(255,255,255,0.05) 40%,
        transparent 70%
      )`;

      // geser holo
      holo.style.backgroundPosition = `${x * 100}% ${y * 100}%`;
    });

    wrap.addEventListener('mouseleave', () => {
      card.style.transform = 'rotateX(0deg) rotateY(0deg) scale(1)';
      card.style.transition = 'transform 0.4s ease, box-shadow 0.4s ease';
      setTimeout(() => {
        card.style.transition = 'transform 0.1s ease, box-shadow 0.1s ease';
      }, 400);
    });

    wrap.addEventListener('mouseenter', () => {
      card.style.transition = 'transform 0.1s ease, box-shadow 0.1s ease';
    });
  });
</script>
</body>
</html>