/**
 * explore-search.js
 * Client-side search + filter untuk halaman Jelajah (Pokelu TCG)
 *
 * Cara kerja:
 *  - Membaca semua .item-card yang ada di .items-grid (kartu yang sudah di-render Blade)
 *  - Saat user klik Search → tampilkan section hasil di atas grid
 *  - Grid asli + pagination TETAP tampil di bawah
 *  - Filter: nama kartu (text) + tipe (auto-detect) + rarity (auto-detect dari suffix nama)
 */

(function () {
  "use strict";

  /* ─────────────────────────────────────────
     1. INJECT: Search Bar HTML ke DOM
  ───────────────────────────────────────── */
  function buildSearchUI() {
    const content = document.querySelector(".content");
    if (!content) return;

    const wrapper = document.createElement("div");
    wrapper.className = "search-wrapper";
    wrapper.innerHTML = `
      <div class="search-bar">
        <div class="search-input-group">
          <span class="search-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
          </span>
          <input
            type="text"
            id="search-input"
            class="search-text"
            placeholder="Cari nama kartu…"
            autocomplete="off"
          />
        </div>

        <div class="search-filters">
          <select id="filter-type" class="search-select">
            <option value="">Semua Tipe</option>
            <option value="Colorless">Colorless</option>
            <option value="Darkness">Darkness</option>
            <option value="Dragon">Dragon</option>
            <option value="Fairy">Fairy</option>
            <option value="Fighting">Fighting</option>
            <option value="Fire">Fire</option>
            <option value="Grass">Grass</option>
            <option value="Lightning">Lightning</option>
            <option value="Metal">Metal</option>
            <option value="Psychic">Psychic</option>
            <option value="Water">Water</option>
          </select>

          <select id="filter-rarity" class="search-select">
            <option value="">Semua Rarity</option>
            <option value="Common">Common</option>
            <option value="Uncommon">Uncommon</option>
            <option value="Rare">Rare</option>
            <option value="Rare Holo">Rare Holo</option>
            <option value="Rare Holo V">Rare Holo V</option>
            <option value="Rare Holo GX">Rare Holo GX</option>
            <option value="Rare Holo EX">Rare Holo EX</option>
            <option value="Ultra Rare">Ultra Rare</option>
            <option value="Secret Rare">Secret Rare</option>
          </select>
        </div>

        <div class="search-actions">
          <button id="btn-search" class="btn-search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            Search
          </button>
          <button id="btn-clear" class="btn-clear" style="display:none">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
            Reset
          </button>
        </div>
      </div>

      <!-- Hasil Search (muncul di atas grid asli) -->
      <div id="search-results-section" class="search-results-section" style="display:none">
        <div class="search-results-header">
          <h3 class="search-results-title">
            <span class="search-results-icon">🔍</span>
            Hasil Pencarian: <span id="search-query-label" class="search-query-text"></span>
          </h3>
          <span id="search-count" class="search-count"></span>
        </div>
        <div id="search-results-grid" class="items-grid search-results-grid"></div>
        <div id="search-empty" class="search-empty" style="display:none">
          <span class="search-empty-icon">😶</span>
          <p>Tidak ada kartu yang cocok dengan pencarian kamu.</p>
        </div>
      </div>

      <!-- Divider sebelum grid asli -->
      <div id="grid-divider" class="grid-divider" style="display:none">
        <span>Semua Kartu</span>
      </div>
    `;

    // Insert search wrapper tepat setelah h2.section-title, sebelum .items-grid
    const section = content.querySelector(".section") || content;
    const title = section.querySelector(".section-title");
    const grid  = section.querySelector(".items-grid");

    if (title && grid) {
      // Masukkan search wrapper di antara title dan grid
      section.insertBefore(wrapper, grid);
    } else {
      // Fallback: taruh di awal section
      section.prepend(wrapper);
    }
  }

  /* ─────────────────────────────────────────
     2A. DETEKSI RARITY dari nama kartu
         Urutan pengecekan: spesifik → umum
  ───────────────────────────────────────── */
  function detectRarity(name) {
    // Trim trailing whitespace & normalize spaces
    const n = name.trim();

    // Suffix-based detection (urutan: paling spesifik dulu)
    if (/\bVSTAR\b/i.test(n))          return "Ultra Rare";      // Arceus VSTAR
    if (/\bVMAX\b/i.test(n))           return "Rare Holo VMAX";  // Charizard VMAX
    if (/\bV\b/i.test(n))              return "Rare Holo V";     // Pikachu V
    if (/\bGX\b/i.test(n))             return "Rare Holo GX";    // Mewtwo GX
    if (/\bEX\b/i.test(n))             return "Rare Holo EX";    // Blastoise EX
    if (/\bTag Team\b/i.test(n))       return "Ultra Rare";      // Pikachu & Zekrom-GX
    if (/\bRadiant\b/i.test(n))        return "Rare Shining";    // Radiant Charizard
    if (/\bPrism Star\b|\b♦\b/i.test(n)) return "Rare Prism Star";
    if (/\bBreak\b/i.test(n))          return "Rare BREAK";      // Greninja BREAK
    if (/\bLv\.?\s*X\b/i.test(n))     return "Rare LV.X";       // Lucario LV.X
    if (/\bDelta Species\b/i.test(n))  return "Rare";

    // Nama-nama legendaris / mythical yang biasanya Rare Holo
    const holoLegendaries = [
      "mewtwo","mew","lugia","ho-oh","celebi","kyogre","groudon","rayquaza",
      "deoxys","dialga","palkia","giratina","arceus","reshiram","zekrom","kyurem",
      "xerneas","yveltal","zygarde","solgaleo","lunala","necrozma","zacian",
      "zamazenta","eternatus","koraidon","miraidon"
    ];
    const nameLower = n.toLowerCase();
    if (holoLegendaries.some(leg => nameLower.includes(leg))) return "Rare Holo";

    // Default: tidak bisa ditentukan
    return "";
  }

  /* ─────────────────────────────────────────
     2B. DETEKSI TIPE dari nama kartu
         Berdasarkan kata kunci Pokémon terkenal
         per tipe. Tidak 100% akurat tapi useful.
  ───────────────────────────────────────── */
  function detectType(name) {
    const n = name.toLowerCase();

    // Map: keyword → tipe
    // Dibuat berdasarkan Pokémon ikonik tiap tipe
    const typeMap = [
      { type: "Fire",      words: ["charizard","flareon","arcanine","blaziken","infernape","heatran","reshiram","volcarona","talonflame","cinderace","incineroar","typhlosion","magmar","rapidash","ninetales","moltres","ho-oh","entei","torkoal","houndoom","victini","darmanitan"] },
      { type: "Water",     words: ["blastoise","vaporeon","gyarados","lapras","starmie","kyogre","empoleon","swampert","feraligatr","suicune","lugia","milotic","walrein","gastrodon","samurott","greninja","primarina","inteleon","palafin","pelipper","tentacruel","dewgong","cloyster","slowbro"] },
      { type: "Grass",     words: ["venusaur","leafeon","celebi","sceptile","torterra","serperior","chesnaught","decidueye","rillaboom","kartana","shaymin","tangrowth","exeggutor","breloom","roserade","vileplume","victreebel"] },
      { type: "Lightning", words: ["pikachu","raichu","jolteon","ampharos","raikou","manectric","luxray","electivire","zebstrika","thundurus","zekrom","vikavolt","boltund","miraidon","pachirisu","plusle","minun","lanturn","magnezone","electrode","zapdos"] },
      { type: "Psychic",   words: ["mewtwo","mew","alakazam","espeon","jynx","exeggutor","gardevoir","deoxys","latios","latias","jirachi","victini","gothitelle","reuniclus","musharna","elgyem","beheeyem","gallade","xatu","lunatone","solrock","slowking","starmie","hypno","mr. mime","wobbuffet","unown"] },
      { type: "Fighting",  words: ["machamp","hitmonlee","hitmonchan","lucario","conkeldurr","mienshao","terrakion","cobalion","virizion","heracross","medicham","hariyama","poliwrath","primeape","breloom","gallade","toxicroak","hawlucha","buzzwole","urshifu","zamazenta","annihilape"] },
      { type: "Darkness",  words: ["umbreon","tyranitar","weavile","darkrai","honchkrow","spiritomb","zoroark","hydreigon","yveltal","pangoro","guzzlord","grimmsnarl","cacturne","sharpedo","crawdaunt","sableye","absol","incineroar","malamar","obstagoon"] },
      { type: "Metal",     words: ["steelix","scizor","metagross","dialga","lucario","bronzong","magnezone","empoleon","registeel","jirachi","cobalion","klinklang","aegislash","excadrill","ferrothorn","genesect","zacian","zamazenta","melmetal","copperajah","duraludon"] },
      { type: "Dragon",    words: ["dragonite","dragonair","dratini","charizard","salamence","flygon","altaria","garchomp","latias","latios","kingdra","rayquaza","palkia","giratina","haxorus","axew","deino","zweilous","hydreigon","goodra","noivern","zygarde","turtonator","drampa","kommo-o","naganadel","eternatus","duraludon","miraidon","koraidon","baxcalibur"] },
      { type: "Fairy",     words: ["clefairy","clefable","togepi","togekiss","sylveon","gardevoir","xerneas","snubbull","granbull","mr. mime","jigglypuff","wigglytuff","marill","azumarill","mawile","carbink","diancie","ribombee","comfey","primarina","mimikyu","tapu lele","tapu fini","tapu koko","tapu bulu","zacian"] },
      { type: "Colorless", words: ["pidgeot","fearow","dodrio","snorlax","kangaskhan","tauros","ditto","eevee","porygon","meowth","persian","aipom","ambipom","staraptor","lopunny","blissey","chansey","lickilicky","regigigas","togekiss","arceus","bewear","stufful","drampa","oranguru","silvally","komala","pyukumuku","type: null"] },
    ];

    for (const { type, words } of typeMap) {
      if (words.some(w => n.includes(w))) return type;
    }

    return ""; // tidak terdeteksi
  }

  /* ─────────────────────────────────────────
     2C. KUMPULKAN DATA KARTU dari DOM
  ───────────────────────────────────────── */
  function collectCards() {
    // Hanya ambil kartu dari grid utama (bukan hasil search)
    const cards = document.querySelectorAll(
      ".section .items-grid:not(.search-results-grid) .item-card"
    );
    return Array.from(cards).map((card) => {
      const nameEl = card.querySelector(".item-name");
      const imgEl  = card.querySelector("img");
      const name   = nameEl ? nameEl.textContent.trim() : "";

      // Prioritas: data-attribute dari Blade → fallback deteksi dari nama
      const type   = card.dataset.type   || detectType(name);
      const rarity = card.dataset.rarity || detectRarity(name);

      // Tulis balik ke data-attribute agar bisa di-inspect
      if (!card.dataset.type   && type)   card.dataset.type   = type;
      if (!card.dataset.rarity && rarity) card.dataset.rarity = rarity;

      return {
        el: card,
        name,
        image: imgEl ? imgEl.src : "",
        type,
        rarity,
      };
    });
  }

  /* ─────────────────────────────────────────
     3. RENDER HASIL ke search-results-grid
  ───────────────────────────────────────── */
  function renderResults(matches, query) {
    const section = document.getElementById("search-results-section");
    const grid = document.getElementById("search-results-grid");
    const emptyEl = document.getElementById("search-empty");
    const countEl = document.getElementById("search-count");
    const labelEl = document.getElementById("search-query-label");
    const clearBtn = document.getElementById("btn-clear");
    const divider = document.getElementById("grid-divider");

    section.style.display = "block";
    divider.style.display = "flex";
    clearBtn.style.display = "inline-flex";

    // Label query
    labelEl.textContent = `"${query}"`;

    if (matches.length === 0) {
      grid.style.display = "none";
      emptyEl.style.display = "flex";
      countEl.textContent = "0 kartu ditemukan";
      return;
    }

    emptyEl.style.display = "none";
    grid.style.display = "grid";
    countEl.textContent = `${matches.length} kartu ditemukan`;

    // Rebuild grid dengan clone kartu
    grid.innerHTML = "";
    matches.forEach((card, i) => {
      const clone = card.el.cloneNode(true);
      clone.style.animationDelay = `${i * 0.03}s`;
      grid.appendChild(clone);
    });
  }

  /* ─────────────────────────────────────────
     4. FILTER LOGIC
  ───────────────────────────────────────── */
  async function doSearch() {
  const query      = document.getElementById("search-input").value.trim();
  const typeFilter = document.getElementById("filter-type").value;
  const rarityFilter = document.getElementById("filter-rarity").value;

  // Minimal: harus ada input
  if (!query && !typeFilter && !rarityFilter) return;

  const label = [
    query || null,
    typeFilter   ? `Tipe: ${typeFilter}`   : null,
    rarityFilter ? `Rarity: ${rarityFilter}` : null,
  ].filter(Boolean).join(", ") || "semua";

  // Tampilkan loading state
  showLoading(label);

  try {
    // Bangun query string
    const params = new URLSearchParams({ page: 1 });
    if (query)       params.set("name", query);
    if (typeFilter)  params.set("type", typeFilter);
    if (rarityFilter) params.set("rarity", rarityFilter);

    const res  = await fetch(`${window.location.origin}/cards/search?${params}`, {
      headers: { "X-Requested-With": "XMLHttpRequest" }
    });

    if (!res.ok) throw new Error("Request gagal: " + res.status);

    const json = await res.json();
    renderApiResults(json, label);

  } catch (err) {
    console.error(err);
    renderError(label);
  }
}

/* ── Render hasil dari API ── */
function renderApiResults({ data = [], total = 0, page = 1, perPage = 20 }, label) {
  const section  = document.getElementById("search-results-section");
  const grid     = document.getElementById("search-results-grid");
  const emptyEl  = document.getElementById("search-empty");
  const countEl  = document.getElementById("search-count");
  const labelEl  = document.getElementById("search-query-label");
  const clearBtn = document.getElementById("btn-clear");
  const divider  = document.getElementById("grid-divider");

  section.style.display = "block";
  divider.style.display = "flex";
  clearBtn.style.display = "inline-flex";
  labelEl.textContent   = `"${label}"`;

  if (data.length === 0) {
    grid.style.display   = "none";
    emptyEl.style.display = "flex";
    countEl.textContent  = "0 kartu ditemukan";
    return;
  }

  emptyEl.style.display = "none";
  grid.style.display    = "grid";
  countEl.textContent   = `${total} kartu ditemukan (menampilkan ${data.length})`;

  // Bangun card HTML dari data API
  grid.innerHTML = data.map((card, i) => {
    const tier  = detectTierFromName(card.name);
    const delay = (i * 0.03).toFixed(2);
    return `
      <a class="item-card" href="/cards/${card.id}"
         style="animation-delay:${delay}s">
        <div class="item-thumb tier-${tier}" data-tier="${tier}">
          <div class="glow-spot"></div>
          <div class="border-glow"></div>
          ${["secret","ultra"].includes(tier)
            ? `<div class="holo-layer"></div>
               <div class="shimmer-layer"></div>
               <div class="sparkle-layer"></div>
               ${tier === "secret" ? '<div class="gold-layer"></div>' : ""}`
            : tier === "holo" ? '<div class="shimmer-layer"></div>' : ""}
          ${card.image
            ? `<img src="${card.image}" alt="${escHtml(card.name)}" loading="lazy">`
            : `<span class="no-img">?</span>`}
        </div>
        <span class="item-name">${escHtml(card.name)}</span>
      </a>`;
  }).join("");

  // Re-attach efek hover pada kartu baru
  grid.querySelectorAll(".item-thumb").forEach(
    thumb => typeof attachHoverEffect === "function" && attachHoverEffect(thumb)
  );
}

/* ── Helper ── */
function escHtml(str) {
  return str.replace(/[&<>"']/g, c =>
    ({ "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;" }[c])
  );
}

function detectTierFromName(name) {
  if (/VMAX|VSTAR|\bEX\b/i.test(name))    return "secret";
  if (/\bV\b|\bGX\b/i.test(name))         return "ultra";
  if (/holo|prism|radiant/i.test(name))   return "holo";
  return "normal";
}

function showLoading(label) {
  const section = document.getElementById("search-results-section");
  const grid    = document.getElementById("search-results-grid");
  const countEl = document.getElementById("search-count");
  document.getElementById("search-query-label").textContent = `"${label}"`;
  section.style.display = "block";
  document.getElementById("grid-divider").style.display = "flex";
  document.getElementById("btn-clear").style.display = "inline-flex";
  document.getElementById("search-empty").style.display = "none";
  grid.style.display = "grid";
  countEl.textContent = "Mencari…";
  grid.innerHTML = Array(5).fill(`
    <div class="item-card">
      <div class="item-thumb tier-normal" style="opacity:.35;animation:cardIn .6s ease both"></div>
      <span class="item-name" style="opacity:.3">——————</span>
    </div>`).join("");
}

function renderError(label) {
  document.getElementById("search-count").textContent = "Gagal mengambil data";
  document.getElementById("search-results-grid").innerHTML = "";
  document.getElementById("search-empty").style.display = "flex";
  document.getElementById("search-empty").querySelector("p").textContent =
    "Terjadi kesalahan. Coba lagi beberapa saat.";
}
  /* ─────────────────────────────────────────
     5. RESET
  ───────────────────────────────────────── */
  function resetSearch() {
    document.getElementById("search-input").value = "";
    document.getElementById("filter-type").value = "";
    document.getElementById("filter-rarity").value = "";
    document.getElementById("search-results-section").style.display = "none";
    document.getElementById("grid-divider").style.display = "none";
    document.getElementById("btn-clear").style.display = "none";
    document.getElementById("search-results-grid").innerHTML = "";
  }

  /* ─────────────────────────────────────────
     6. INJECT CSS
  ───────────────────────────────────────── */
  function injectStyles() {
    const style = document.createElement("style");
    style.textContent = `
      /* ── SEARCH WRAPPER ── */
      .search-wrapper {
        margin-bottom: 32px;
      }

      /* ── SEARCH BAR ── */
      .search-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        background: linear-gradient(135deg, #2e1f56 0%, #241848 100%);
        border: 1px solid rgba(155, 110, 224, 0.25);
        border-radius: 14px;
        padding: 16px 20px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.35),
                    inset 0 1px 0 rgba(255,255,255,0.05);
      }

      /* Input group */
      .search-input-group {
        flex: 1 1 240px;
        position: relative;
        display: flex;
        align-items: center;
      }

      .search-icon {
        position: absolute;
        left: 14px;
        color: #9b6ee0;
        display: flex;
        align-items: center;
        pointer-events: none;
      }

      .search-text {
        width: 100%;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(155,110,224,0.3);
        border-radius: 9px;
        padding: 10px 14px 10px 42px;
        color: #f0eaf8;
        font-family: inherit;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
      }

      .search-text::placeholder { color: rgba(155,143,194,0.55); }

      .search-text:focus {
        border-color: #9b6ee0;
        box-shadow: 0 0 0 3px rgba(155,110,224,0.18);
      }

      /* Filters */
      .search-filters {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
      }

      .search-select {
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(155,110,224,0.3);
        border-radius: 9px;
        padding: 10px 14px;
        color: #f0eaf8;
        font-family: inherit;
        font-size: 0.85rem;
        outline: none;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
        min-width: 140px;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239b6ee0' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 32px;
      }

      .search-select option {
        background: #2a1d4a;
        color: #f0eaf8;
      }

      .search-select:focus {
        border-color: #9b6ee0;
        box-shadow: 0 0 0 3px rgba(155,110,224,0.18);
      }

      /* Action buttons */
      .search-actions {
        display: flex;
        gap: 8px;
      }

      .btn-search,
      .btn-clear {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-family: 'Freckle Face', cursive;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
        border: none;
        border-radius: 9px;
        padding: 10px 22px;
        cursor: pointer;
        transition: transform 0.15s, box-shadow 0.15s, background 0.18s;
        white-space: nowrap;
      }

      .btn-search {
        background: linear-gradient(135deg, #9b6ee0 0%, #7c45c8 100%);
        color: #fff;
        box-shadow: 0 4px 14px rgba(155,110,224,0.4);
      }

      .btn-search:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(155,110,224,0.55);
      }

      .btn-search:active { transform: translateY(0); }

      .btn-clear {
        background: rgba(255,255,255,0.07);
        color: #c4b0e8;
        border: 1px solid rgba(155,110,224,0.25);
      }

      .btn-clear:hover {
        background: rgba(255,80,80,0.18);
        border-color: rgba(255,80,80,0.4);
        color: #ff8a8a;
        transform: translateY(-2px);
      }

      /* ── HASIL SECTION ── */
      .search-results-section {
        margin-bottom: 12px;
        animation: fadeSlideDown 0.3s ease both;
      }

      @keyframes fadeSlideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to   { opacity: 1; transform: translateY(0); }
      }

      .search-results-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 18px;
      }

      .search-results-title {
        font-family: 'Freckle Face', cursive;
        font-size: 1.25rem;
        color: #f0eaf8;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        text-shadow: 0 2px 10px rgba(155,110,224,0.35);
      }

      .search-results-icon { font-size: 1.1rem; }

      .search-query-text {
        color: #c89ff8;
        font-style: italic;
      }

      .search-count {
        font-size: 0.8rem;
        font-weight: 700;
        color: #9b8fc2;
        background: rgba(155,110,224,0.12);
        border: 1px solid rgba(155,110,224,0.25);
        border-radius: 20px;
        padding: 4px 14px;
        letter-spacing: 0.3px;
      }

      /* Hasil grid — override semua sumber min-height */
      .search-results-grid {
        padding-bottom: 8px;
        min-height: 0 !important;
        height: auto !important;
      }

      /* Override min-height di search results section juga */
      .search-results-section,
      .search-results-section .items-grid,
      #search-results-grid {
        min-height: 0 !important;
        height: auto !important;
      }

      /* Empty state */
      .search-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 40px 20px;
        color: #9b8fc2;
        font-size: 0.9rem;
        text-align: center;
      }

      .search-empty-icon { font-size: 2.5rem; }

      /* ── DIVIDER ── */
      .grid-divider {
        display: flex;
        align-items: center;
        gap: 16px;
        margin: 28px 0 22px;
        color: #6a5a9a;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
      }

      .grid-divider::before,
      .grid-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(to right, transparent, rgba(155,110,224,0.25), transparent);
      }

      /* ── RESPONSIVE ── */
      @media (max-width: 800px) {
        .search-bar { padding: 14px 16px; gap: 10px; }
        .search-filters { width: 100%; }
        .search-select { flex: 1; min-width: 120px; }
        .search-actions { width: 100%; }
        .btn-search, .btn-clear { flex: 1; justify-content: center; }
      }

      @media (max-width: 580px) {
        .search-input-group { flex: 1 1 100%; }
        .search-actions { flex-wrap: wrap; }
      }
    `;
    document.head.appendChild(style);
  }

  /* ─────────────────────────────────────────
     7. INIT
  ───────────────────────────────────────── */
  function init() {
    injectStyles();
    buildSearchUI();

    const cards = collectCards();

    const searchBtn = document.getElementById("btn-search");
    const clearBtn = document.getElementById("btn-clear");
    const searchInput = document.getElementById("search-input");

    // Klik tombol Search
    searchBtn.addEventListener("click", () => doSearch());

    // Enter di input juga trigger search
    searchInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter") doSearch();
    });

    // Reset
    clearBtn.addEventListener("click", resetSearch);
  }

  // Jalankan setelah DOM siap
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();