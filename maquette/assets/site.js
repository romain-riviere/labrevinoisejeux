/* La Brévinoise — affichage du site public.
   Brev.state contient tous les contenus ; Brev.render() redessine la page. */
{
const KEY = 'brevinoise-site';
const clone = o => JSON.parse(JSON.stringify(o));
const isObj = o => o && typeof o === 'object' && !Array.isArray(o);

// Fusion « mise à jour » : garde vos réglages, ajoute les nouveaux réglages du thème
function mergeSettings(def, saved) {
  if (!isObj(saved)) return clone(def);
  const out = clone(def);
  for (const k of Object.keys(saved)) {
    if (k === 'menu' && Array.isArray(saved.menu)) {
      const ids = saved.menu.map(m => m.id);
      out.menu = saved.menu.concat(def.menu.filter(m => !ids.includes(m.id)));
    } else if (isObj(def[k]) && isObj(saved[k])) out[k] = mergeSettings(def[k], saved[k]);
    else out[k] = saved[k];
  }
  return out;
}
function upgrade(saved) {
  const D = window.BREV_DEFAULTS, s = { version: window.BREV_THEME_VERSION };
  s.settings = mergeSettings(D.settings, saved && saved.settings);
  for (const k of ['festivals', 'jeux', 'escape', 'bd', 'adherents'])
    s[k] = saved && Array.isArray(saved[k]) ? saved[k] : clone(D[k]);
  // Jeux en ligne : vos réglages + les nouveaux jeux ajoutés par une mise à jour du thème
  const sg = saved && Array.isArray(saved.navGames) ? saved.navGames : [];
  s.navGames = D.navGames.map(g => { const o = sg.find(x => x.id === g.id); return o ? mergeSettings(g, o) : clone(g); })
    .concat(sg.filter(x => !D.navGames.some(g => g.id === x.id)));
  // Migration 1.2.0 : relier la fiche « Gare au Chacal » au jeu en ligne
  if (saved && (saved.version || '0') < '1.2.0') s.jeux.forEach(j => { if (/gare au chacal/i.test(j.title) && !j.play) j.play = 'gac'; });
  return s;
}
function load() {
  let saved = null;
  try { saved = JSON.parse(localStorage.getItem(KEY) || 'null'); } catch (e) {}
  return upgrade(saved);
}

const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const $ = id => document.getElementById(id);
const Brev = window.Brev = {
  KEY, esc, upgrade, mergeSettings,
  state: load(),
  saved: true,
  save() {
    this.state.version = window.BREV_THEME_VERSION;
    try { localStorage.setItem(KEY, JSON.stringify(this.state)); this.saved = true; return true; }
    catch (e) { this.saved = false; return false; }
  },
  reset() { try { localStorage.removeItem(KEY); } catch (e) {} this.state = upgrade(null); },
  allItems() { const s = this.state; return [...s.jeux, ...s.escape, ...s.bd]; },
  nameOf(id) { const it = this.allItems().find(x => x.id === id); return it ? it.title : null; },
  routes: ['accueil', 'festivals', 'jeux', 'escape', 'bd', 'adherents', 'contact']
};

const tagsOf = it => String(it.tags || '').split(',').map(t => t.trim()).filter(Boolean);
const ext = l => l && !l.startsWith('#');
const initials = n => String(n).split(/\s+/).filter(w => /^[\p{L}]/u.test(w)).slice(0, 2).map(w => w[0].toUpperCase()).join('') || '?';

function visual(it, isNew) {
  const badge = (isNew ? '<span class="new">Nouveau</span>' : '') + (it.kind ? `<span class="kind">${esc(it.kind)}</span>` : '');
  if (!it.img) return `<div class="cv"><span class="soon">${esc(it.title)}<br><small>Visuel à venir</small></span>${badge}</div>`;
  const flat = /escape|photo/.test(it.img) || it.flat;
  return `<div class="cv${flat ? ' flat' : ''}"><img src="${esc(it.img)}" alt="${esc(it.title)}" loading="lazy">${badge}</div>`;
}
function playUrl(it) {
  if (it.playUrl) return it.playUrl;
  const g = it.play && (Brev.state.navGames || []).find(x => x.id === it.play);
  return g ? g.path : '';
}
function card(it, isNew) {
  const S = Brev.state.settings, play = playUrl(it);
  const tgt = l => ext(l) ? ' target="_blank" rel="noopener"' : '';
  const pic = it.link ? `<a href="${esc(it.link)}"${tgt(it.link)} tabindex="-1" aria-hidden="true">${visual(it, isNew)}</a>` : visual(it, isNew);
  const btns = (it.link ? `<a class="btn sm" href="${esc(it.link)}"${tgt(it.link)}>${esc(S.sections.itemBtn)} →</a>` : '')
    + (play ? `<a class="btn sm play" href="${esc(play)}"${/^https?:/.test(play) ? ' target="_blank" rel="noopener"' : ''}>▶ ${esc(S.sections.playBtn || 'Tester le jeu')}</a>` : '');
  return `<article class="card">${pic}<div class="body"><h3>${esc(it.title)}</h3><p>${esc(it.desc)}</p><div class="tags">${tagsOf(it).map(t => `<span class="tag">${esc(t)}</span>`).join('')}</div>${btns ? `<div class="cbtns">${btns}</div>` : ''}</div></article>`;
}
function festCard(f) {
  return `<div><article class="fest" tabindex="0" data-pop="festivals:${esc(f.id)}" aria-haspopup="dialog" aria-label="${esc(f.title)} : afficher les détails">${f.img ? `<img src="${esc(f.img)}" alt="Affiche : ${esc(f.title)}" loading="lazy">` : `<div class="avatar" style="aspect-ratio:1100/700;font-size:28px">${esc(f.title)}</div>`}<span class="peek">Infos</span></article>
  <div class="fcap"><span>${esc(f.title)}</span><span>${esc(String(f.date || '').split(',')[0])}</span></div></div>`;
}
function memberCard(m) {
  return `<div><article class="member" tabindex="0" data-pop="adherents:${esc(m.id)}" aria-haspopup="dialog" aria-label="${esc(m.title)} : afficher la fiche">${m.img ? `<img src="${esc(m.img)}" alt="${esc(m.title)}" loading="lazy">` : `<div class="avatar">${esc(initials(m.title))}</div>`}</article>
  <div class="mcap"><b>${esc(m.title)}</b><span>${esc(m.role)}</span></div></div>`;
}
function popHTML(kind, x, big) {
  const g = (x.games || []).map(id => Brev.nameOf(id)).filter(Boolean);
  const list = g.length ? `<p class="plbl">${kind === 'festivals' ? 'Jeux associés' : 'Jeux et projets'}</p><ul class="pgames">${g.map(n => `<li>${esc(n)}</li>`).join('')}</ul>` : '';
  if (kind === 'festivals') return `${big && x.img ? `<img class="pimg" src="${esc(x.img)}" alt="">` : ''}<h3>${esc(x.title)}</h3>
    <p class="pmeta"><b>${esc(x.date)}</b><br>${esc(x.place)}</p><p>${esc(x.desc)}</p>${list}${x.link ? `<a class="btn sm" href="${esc(x.link)}" target="_blank" rel="noopener">En savoir plus →</a>` : ''}`;
  return `<div class="phead">${x.img ? `<img src="${esc(x.img)}" alt="">` : `<span class="avatar">${esc(initials(x.title))}</span>`}<div><h3>${esc(x.title)}</h3><p class="pmeta"><b>${esc(x.role)}</b></p></div></div><p>${esc(x.desc)}</p>${list}`;
}
const pagehead = (t, p) => `<div class="band pagehead"><div class="wrap"><h1>${esc(t)}</h1><p>${esc(p)}</p></div></div>`;
const menuLabel = id => (Brev.state.settings.menu.find(m => m.id === id) || {}).label || id;

function view(r) {
  const S = Brev.state.settings, st = Brev.state, H = S.hero, X = S.sections;
  if (r === 'accueil') return `
  <div class="band hero"><div class="wrap">
    <div class="art">${H.image ? `<img src="${esc(H.image)}" alt="">` : ''}</div>
    <div><h1>${esc(H.title)}</h1><p>${esc(H.text1)}</p><p>${esc(H.text2)}</p>
    ${H.btn1Label ? `<a class="btn" href="${esc(H.btn1Link)}">${esc(H.btn1Label)}</a>` : ''}${H.btn2Label ? `<a class="btn alt" href="${esc(H.btn2Link)}">${esc(H.btn2Label)}</a>` : ''}</div>
  </div></div>
  <div class="band"><div class="wrap"><div class="head"><div><h2>${esc(X.festTitle)}</h2><p>${esc(X.festText)}</p></div><a class="btn" href="#festivals">${esc(X.festBtn)}</a></div>
    <div class="fgrid">${st.festivals.slice(0, 3).map(festCard).join('') || '<p class="empty">Aucun festival pour le moment.</p>'}</div></div></div>
  <div class="band alt-bg"><div class="wrap"><div class="head"><div><h2>${esc(X.jeuxTitle)}</h2><p>${esc(X.jeuxText)}</p></div><a class="btn" href="#jeux">${esc(X.jeuxBtn)}</a></div>
    <div class="grid">${st.jeux.slice(0, 3).map(j => card(j, true)).join('') || '<p class="empty">Aucun jeu pour le moment.</p>'}</div></div></div>
  <div class="band"><div class="wrap"><div class="head"><div><h2>${esc(X.bdTitle)}</h2><p>${esc(X.bdText)}</p></div><a class="btn" href="#bd">${esc(X.bdBtn)}</a></div>
    <div class="grid">${st.bd.slice(0, 3).map((b, i) => card(b, i === 0)).join('') || '<p class="empty">Aucune BD pour le moment.</p>'}</div></div></div>`;
  if (r === 'festivals') return pagehead(menuLabel('festivals'), S.pages.festivals) + `<div class="band"><div class="wrap"><div class="fgrid">${st.festivals.map(festCard).join('') || '<p class="empty">Aucun festival pour le moment.</p>'}</div></div></div>`;
  if (r === 'jeux' || r === 'bd') return pagehead(menuLabel(r), S.pages[r]) + `<div class="band"><div class="wrap"><div class="tools"><input id="q" type="search" placeholder="Rechercher…" aria-label="Rechercher"></div><div class="grid" id="list"></div></div></div>`;
  if (r === 'escape') return pagehead(menuLabel('escape'), S.pages.escape) + `<div class="band"><div class="wrap"><div class="tools" id="kinds"></div><div class="grid" id="list"></div></div></div>`;
  if (r === 'adherents') return pagehead(menuLabel('adherents'), S.pages.adherents) + `<div class="band"><div class="wrap"><div class="mgrid">${st.adherents.map(memberCard).join('') || '<p class="empty">Aucun adhérent pour le moment.</p>'}</div></div></div>`;
  if (r === 'contact') return pagehead(menuLabel('contact'), S.pages.contact) + `<div class="band"><div class="wrap contact">
    ${S.contact.map(c => `<div class="box"><h3>${esc(c.title)}</h3><p class="pre">${esc(c.text)}</p></div>`).join('')}
    <div class="box" style="grid-column:1/-1"><h3>Nous écrire</h3><form class="sform" id="form"><label for="n">Nom</label><input id="n" required><label for="e">E-mail</label><input id="e" type="email" required><label for="m">Message</label><textarea id="m" rows="5" required></textarea>
    <p style="margin-top:16px"><button class="btn" type="submit">Envoyer</button></p><p class="ok" id="ok" hidden>Maquette : le formulaire n'est pas encore relié à une messagerie, rien n'a été envoyé.</p></form></div></div></div>`;
  return '';
}

function wireList(r) {
  const st = Brev.state, list = $('list'); if (!list) return;
  let kind = 'Tout';
  const draw = () => {
    const q = ($('q') ? $('q').value : '').toLowerCase().trim();
    const src = st[r];
    const L = src.map((x, i) => [x, i]).filter(([x]) => (kind === 'Tout' || x.kind === kind) && (x.title + ' ' + x.desc + ' ' + x.tags).toLowerCase().includes(q));
    list.innerHTML = L.length ? L.map(([x, i]) => card(x, i < (r === 'bd' ? 1 : 3))).join('') : '<p class="empty">Rien à afficher pour le moment.</p>';
  };
  if ($('q')) $('q').addEventListener('input', draw);
  if ($('kinds')) {
    const kinds = ['Tout', ...new Set(st.escape.map(x => x.kind).filter(Boolean))];
    $('kinds').innerHTML = kinds.map(k => `<button class="chip${k === 'Tout' ? ' on' : ''}" data-k="${esc(k)}">${esc(k)}</button>`).join('');
    $('kinds').addEventListener('click', e => { const b = e.target.closest('.chip'); if (!b) return; kind = b.dataset.k; $('kinds').querySelectorAll('.chip').forEach(c => c.classList.toggle('on', c === b)); draw(); });
  }
  draw();
}

function applyTheme() {
  const S = Brev.state.settings, R = document.documentElement.style;
  R.setProperty('--primary', S.colors.primary); R.setProperty('--dark', S.colors.dark);
  R.setProperty('--accent', S.colors.accent); R.setProperty('--soft', S.colors.soft);
  R.setProperty('--logo', S.logoSize + 'px');
  $('logo').src = S.logo || ''; $('logo').hidden = !S.logo;
  $('sitename').textContent = S.siteName; $('sitename').hidden = !S.showSiteName;
  document.title = S.siteName;
}
function renderMenu(r) {
  const S = Brev.state.settings;
  $('menu').innerHTML = S.menu.filter(m => m.visible).map(m => m.id === 'boutique'
    ? `<a href="${esc(S.shopUrl)}" class="shop" target="_blank" rel="noopener">${esc(m.label)} ↗</a>`
    : `<a href="#${m.id}" class="${m.id === r ? 'on' : ''}">${esc(m.label)}</a>`).join('') + '<a href="#admin" class="adm" title="Bouton temporaire, remplacé par la barre WordPress">Administration</a>';
}
function renderFooter() {
  const S = Brev.state.settings, F = S.footer;
  $('foot').innerHTML = `<div class="wrap"><div><h3>${esc(S.siteName)}</h3><p>${esc(F.about)}</p></div>
  <div><h3>Coordonnées</h3><p class="pre">${esc(F.address)}</p></div>
  <div><h3>Navigation</h3><ul>${S.menu.filter(m => m.visible).map(m => `<li><a href="${m.id === 'boutique' ? esc(S.shopUrl) + '" target="_blank" rel="noopener' : '#' + m.id}">${esc(m.label)}</a></li>`).join('')}</ul></div>
  <div><h3>Distribution</h3><p class="pre">${esc(F.extra)}</p></div></div><div class="wrap legal">${esc(F.legal)}</div>`;
}
let last = null;
Brev.render = function () {
  let r = location.hash.slice(1);
  if (r === 'admin') { if (window.BrevAdmin) window.BrevAdmin.open(); r = last || 'accueil'; }
  else if (window.BrevAdmin) window.BrevAdmin.close();
  if (!Brev.routes.includes(r)) r = 'accueil';
  applyTheme(); renderMenu(r); renderFooter();
  $('app').innerHTML = view(r);
  if (['jeux', 'bd', 'escape'].includes(r)) wireList(r);
  const f = $('form'); if (f) f.addEventListener('submit', e => { e.preventDefault(); $('ok').hidden = false; });
  $('menu').classList.remove('open'); $('burger').setAttribute('aria-expanded', 'false');
  if (r !== last) window.scrollTo(0, 0);
  last = r;
};
/* Fenêtre d'infos : survol = petite fenêtre à côté de l'image ; clic ou toucher = fenêtre au centre */
const pop = document.createElement('div'); pop.id = 'pop'; pop.className = 'pop'; pop.hidden = true; pop.setAttribute('role', 'dialog');
const popbg = document.createElement('div'); popbg.className = 'popbg'; popbg.hidden = true;
document.body.append(popbg, pop);
let popT = 0, popFor = null;
const canHover = matchMedia('(hover: hover) and (pointer: fine)');
function popData(el) { const [k, id] = el.dataset.pop.split(':'); return [k, (Brev.state[k] || []).find(x => x.id === id)]; }
function hidePop() { clearTimeout(popT); pop.hidden = true; popbg.hidden = true; pop.className = 'pop'; if (popFor && pop.contains(document.activeElement)) popFor.focus(); popFor = null; }
function showFloat(el) {
  const [k, x] = popData(el); if (!x) return;
  popFor = el; pop.className = 'pop float'; pop.innerHTML = popHTML(k, x, false); pop.hidden = false; popbg.hidden = true;
  const r = el.getBoundingClientRect(), pw = pop.offsetWidth, ph = pop.offsetHeight, m = 12;
  let left = r.right + m <= innerWidth - pw - 8 ? r.right + m : r.left - m - pw >= 8 ? r.left - m - pw : Math.max(8, Math.min(innerWidth - pw - 8, r.left + (r.width - pw) / 2));
  let top = Math.max(8, Math.min(innerHeight - ph - 8, r.top + (r.height - ph) / 2));
  pop.style.left = left + 'px'; pop.style.top = top + 'px';
}
function showModal(el) {
  const [k, x] = popData(el); if (!x) return;
  clearTimeout(popT); popFor = el; pop.className = 'pop modal'; pop.style.left = pop.style.top = '';
  pop.innerHTML = `<button class="pclose" aria-label="Fermer">✕</button>` + popHTML(k, x, true);
  pop.hidden = false; popbg.hidden = false; pop.querySelector('.pclose').focus();
}
document.addEventListener('mouseover', e => {
  if (!canHover.matches || pop.classList.contains('modal')) return;
  const t = e.target.closest('[data-pop]');
  if (t) { clearTimeout(popT); if (popFor !== t) popT = setTimeout(() => showFloat(t), 120); }
  else if (e.target.closest('#pop')) clearTimeout(popT);
  else if (popFor) { clearTimeout(popT); popT = setTimeout(hidePop, 220); }
});
document.addEventListener('click', e => {
  if (e.target.closest('.pclose') || e.target === popbg) return hidePop();
  if (e.target.closest('#pop')) return;
  const t = e.target.closest('[data-pop]');
  if (t) showModal(t); else if (!pop.hidden) hidePop();
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape' && !pop.hidden) return hidePop();
  const t = e.target.closest && e.target.closest('[data-pop]');
  if (t && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); showModal(t); }
});
addEventListener('scroll', () => { if (pop.classList.contains('float')) hidePop(); }, { passive: true });
window.addEventListener('hashchange', hidePop);
$('burger').addEventListener('click', () => { const o = $('menu').classList.toggle('open'); $('burger').setAttribute('aria-expanded', String(o)); });
window.addEventListener('hashchange', () => Brev.render());
document.addEventListener('DOMContentLoaded', () => Brev.render());
}
