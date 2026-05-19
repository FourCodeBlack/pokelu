(function(){function g(){const t=document.querySelector(".content");if(!t)return;const e=document.createElement("div");e.className="search-wrapper",e.innerHTML=`
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
    `;const a=t.querySelector(".section")||t,n=a.querySelector(".section-title"),r=a.querySelector(".items-grid");n&&r?a.insertBefore(e,r):a.prepend(e)}function h(t){const e=t.trim();if(/\bVSTAR\b/i.test(e))return"Ultra Rare";if(/\bVMAX\b/i.test(e))return"Rare Holo VMAX";if(/\bV\b/i.test(e))return"Rare Holo V";if(/\bGX\b/i.test(e))return"Rare Holo GX";if(/\bEX\b/i.test(e))return"Rare Holo EX";if(/\bTag Team\b/i.test(e))return"Ultra Rare";if(/\bRadiant\b/i.test(e))return"Rare Shining";if(/\bPrism Star\b|\b♦\b/i.test(e))return"Rare Prism Star";if(/\bBreak\b/i.test(e))return"Rare BREAK";if(/\bLv\.?\s*X\b/i.test(e))return"Rare LV.X";if(/\bDelta Species\b/i.test(e))return"Rare";const a=["mewtwo","mew","lugia","ho-oh","celebi","kyogre","groudon","rayquaza","deoxys","dialga","palkia","giratina","arceus","reshiram","zekrom","kyurem","xerneas","yveltal","zygarde","solgaleo","lunala","necrozma","zacian","zamazenta","eternatus","koraidon","miraidon"],n=e.toLowerCase();return a.some(r=>n.includes(r))?"Rare Holo":""}function y(t){const e=t.toLowerCase(),a=[{type:"Fire",words:["charizard","flareon","arcanine","blaziken","infernape","heatran","reshiram","volcarona","talonflame","cinderace","incineroar","typhlosion","magmar","rapidash","ninetales","moltres","ho-oh","entei","torkoal","houndoom","victini","darmanitan"]},{type:"Water",words:["blastoise","vaporeon","gyarados","lapras","starmie","kyogre","empoleon","swampert","feraligatr","suicune","lugia","milotic","walrein","gastrodon","samurott","greninja","primarina","inteleon","palafin","pelipper","tentacruel","dewgong","cloyster","slowbro"]},{type:"Grass",words:["venusaur","leafeon","celebi","sceptile","torterra","serperior","chesnaught","decidueye","rillaboom","kartana","shaymin","tangrowth","exeggutor","breloom","roserade","vileplume","victreebel"]},{type:"Lightning",words:["pikachu","raichu","jolteon","ampharos","raikou","manectric","luxray","electivire","zebstrika","thundurus","zekrom","vikavolt","boltund","miraidon","pachirisu","plusle","minun","lanturn","magnezone","electrode","zapdos"]},{type:"Psychic",words:["mewtwo","mew","alakazam","espeon","jynx","exeggutor","gardevoir","deoxys","latios","latias","jirachi","victini","gothitelle","reuniclus","musharna","elgyem","beheeyem","gallade","xatu","lunatone","solrock","slowking","starmie","hypno","mr. mime","wobbuffet","unown"]},{type:"Fighting",words:["machamp","hitmonlee","hitmonchan","lucario","conkeldurr","mienshao","terrakion","cobalion","virizion","heracross","medicham","hariyama","poliwrath","primeape","breloom","gallade","toxicroak","hawlucha","buzzwole","urshifu","zamazenta","annihilape"]},{type:"Darkness",words:["umbreon","tyranitar","weavile","darkrai","honchkrow","spiritomb","zoroark","hydreigon","yveltal","pangoro","guzzlord","grimmsnarl","cacturne","sharpedo","crawdaunt","sableye","absol","incineroar","malamar","obstagoon"]},{type:"Metal",words:["steelix","scizor","metagross","dialga","lucario","bronzong","magnezone","empoleon","registeel","jirachi","cobalion","klinklang","aegislash","excadrill","ferrothorn","genesect","zacian","zamazenta","melmetal","copperajah","duraludon"]},{type:"Dragon",words:["dragonite","dragonair","dratini","charizard","salamence","flygon","altaria","garchomp","latias","latios","kingdra","rayquaza","palkia","giratina","haxorus","axew","deino","zweilous","hydreigon","goodra","noivern","zygarde","turtonator","drampa","kommo-o","naganadel","eternatus","duraludon","miraidon","koraidon","baxcalibur"]},{type:"Fairy",words:["clefairy","clefable","togepi","togekiss","sylveon","gardevoir","xerneas","snubbull","granbull","mr. mime","jigglypuff","wigglytuff","marill","azumarill","mawile","carbink","diancie","ribombee","comfey","primarina","mimikyu","tapu lele","tapu fini","tapu koko","tapu bulu","zacian"]},{type:"Colorless",words:["pidgeot","fearow","dodrio","snorlax","kangaskhan","tauros","ditto","eevee","porygon","meowth","persian","aipom","ambipom","staraptor","lopunny","blissey","chansey","lickilicky","regigigas","togekiss","arceus","bewear","stufful","drampa","oranguru","silvally","komala","pyukumuku","type: null"]}];for(const{type:n,words:r}of a)if(r.some(o=>e.includes(o)))return n;return""}function b(){const t=document.querySelectorAll(".section .items-grid:not(.search-results-grid) .item-card");return Array.from(t).map(e=>{const a=e.querySelector(".item-name"),n=e.querySelector("img"),r=a?a.textContent.trim():"",o=e.dataset.type||y(r),i=e.dataset.rarity||h(r);return!e.dataset.type&&o&&(e.dataset.type=o),!e.dataset.rarity&&i&&(e.dataset.rarity=i),{el:e,name:r,image:n?n.src:"",type:o,rarity:i}})}async function c(){const t=document.getElementById("search-input").value.trim(),e=document.getElementById("filter-type").value,a=document.getElementById("filter-rarity").value;if(!t&&!e&&!a)return;const n=[t||null,e?`Tipe: ${e}`:null,a?`Rarity: ${a}`:null].filter(Boolean).join(", ")||"semua";v(n);try{const r=new URLSearchParams({page:1});t&&r.set("name",t),e&&r.set("type",e),a&&r.set("rarity",a);const o=await fetch(`${window.location.origin}/cards/search?${r}`,{headers:{"X-Requested-With":"XMLHttpRequest"}});if(!o.ok)throw new Error("Request gagal: "+o.status);const i=await o.json();f(i,n)}catch(r){console.error(r),w()}}function f({data:t=[],total:e=0,page:a=1,perPage:n=20},r){const o=document.getElementById("search-results-section"),i=document.getElementById("search-results-grid"),u=document.getElementById("search-empty"),m=document.getElementById("search-count"),R=document.getElementById("search-query-label"),z=document.getElementById("btn-clear"),B=document.getElementById("grid-divider");if(o.style.display="block",B.style.display="flex",z.style.display="inline-flex",R.textContent=`"${r}"`,t.length===0){i.style.display="none",u.style.display="flex",m.textContent="0 kartu ditemukan";return}u.style.display="none",i.style.display="grid",m.textContent=`${e} kartu ditemukan (menampilkan ${t.length})`,i.innerHTML=t.map((s,I)=>{const l=x(s.name),S=(I*.03).toFixed(2);return`
      <a class="item-card" href="/cards/${s.id}"
         style="animation-delay:${S}s">
        <div class="item-thumb tier-${l}" data-tier="${l}">
          <div class="glow-spot"></div>
          <div class="border-glow"></div>
          ${["secret","ultra"].includes(l)?`<div class="holo-layer"></div>
               <div class="shimmer-layer"></div>
               <div class="sparkle-layer"></div>
               ${l==="secret"?'<div class="gold-layer"></div>':""}`:l==="holo"?'<div class="shimmer-layer"></div>':""}
          ${s.image?`<img src="${s.image}" alt="${d(s.name)}" loading="lazy">`:'<span class="no-img">?</span>'}
        </div>
        <span class="item-name">${d(s.name)}</span>
      </a>`}).join(""),i.querySelectorAll(".item-thumb").forEach(s=>typeof attachHoverEffect=="function"&&attachHoverEffect(s))}function d(t){return t.replace(/[&<>"']/g,e=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"})[e])}function x(t){return/VMAX|VSTAR|\bEX\b/i.test(t)?"secret":/\bV\b|\bGX\b/i.test(t)?"ultra":/holo|prism|radiant/i.test(t)?"holo":"normal"}function v(t){const e=document.getElementById("search-results-section"),a=document.getElementById("search-results-grid"),n=document.getElementById("search-count");document.getElementById("search-query-label").textContent=`"${t}"`,e.style.display="block",document.getElementById("grid-divider").style.display="flex",document.getElementById("btn-clear").style.display="inline-flex",document.getElementById("search-empty").style.display="none",a.style.display="grid",n.textContent="Mencari…",a.innerHTML=Array(5).fill(`
    <div class="item-card">
      <div class="item-thumb tier-normal" style="opacity:.35;animation:cardIn .6s ease both"></div>
      <span class="item-name" style="opacity:.3">——————</span>
    </div>`).join("")}function w(t){document.getElementById("search-count").textContent="Gagal mengambil data",document.getElementById("search-results-grid").innerHTML="",document.getElementById("search-empty").style.display="flex",document.getElementById("search-empty").querySelector("p").textContent="Terjadi kesalahan. Coba lagi beberapa saat."}function k(){document.getElementById("search-input").value="",document.getElementById("filter-type").value="",document.getElementById("filter-rarity").value="",document.getElementById("search-results-section").style.display="none",document.getElementById("grid-divider").style.display="none",document.getElementById("btn-clear").style.display="none",document.getElementById("search-results-grid").innerHTML=""}function E(){const t=document.createElement("style");t.textContent=`
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
    `,document.head.appendChild(t)}function p(){E(),g(),b();const t=document.getElementById("btn-search"),e=document.getElementById("btn-clear"),a=document.getElementById("search-input");t.addEventListener("click",()=>c()),a.addEventListener("keydown",n=>{n.key==="Enter"&&c()}),e.addEventListener("click",k)}document.readyState==="loading"?document.addEventListener("DOMContentLoaded",p):p()})();
