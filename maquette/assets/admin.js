/* La Brévinoise — Administration temporaire (aperçu de l'éditeur WordPress).
   Chaque « type de contenu » ici deviendra un Custom Post Type dans le thème WordPress,
   et chaque page de réglages une section du Customizer / des options du thème. */
{
const B = window.Brev, esc = B.esc, root = document.getElementById('admin');
const TYPES = {
  festivals: { label: 'Festivals (affiches)', one: 'une affiche', fields: [
    ['title', 'Titre du festival', 'text'], ['date', 'Date et horaires', 'text', 'Ex. : Sam. 17 et dim. 18 octobre 2026, 10 h – 18 h'],
    ['place', 'Lieu', 'text'], ['desc', 'Présentation (affichée au survol)', 'textarea'],
    ['link', 'Lien de redirection', 'url', 'Page du festival, billetterie… Laisser vide pour aucun bouton.'], ['games', 'Jeux associés', 'checks']] },
  jeux: { label: 'Jeux de société', one: 'un jeu', fields: [
    ['title', 'Nom du jeu', 'text'], ['desc', 'Description', 'textarea'], ['tags', 'Étiquettes', 'text', 'Séparées par des virgules : 2–5 joueurs, 8 ans +, 30 min'],
    ['link', 'Lien vers la boutique', 'url', 'Quand on clique sur le jeu, on arrive sur cette adresse.'], ['play', 'Jeu en ligne associé', 'playsel', 'Ajoute le bouton « Tester le jeu » sur la fiche. Les jeux se règlent dans Jeux en ligne.'], ['playUrl', 'Ou adresse d\'un autre jeu en ligne', 'url', 'Facultatif : remplace le choix ci-dessus.']] },
  escape: { label: 'Escape Box & accessoires', one: 'une escape box ou un accessoire', fields: [
    ['title', 'Nom', 'text'], ['kind', 'Catégorie', 'select', '', ['Escape Box', 'Accessoire']], ['desc', 'Description', 'textarea'],
    ['tags', 'Étiquettes', 'text', 'Séparées par des virgules'], ['link', 'Lien vers la boutique', 'url', 'Quand on clique, on arrive sur cette adresse.'], ['play', 'Jeu en ligne associé', 'playsel', 'Ajoute le bouton « Tester le jeu » sur la fiche. Les jeux se règlent dans Jeux en ligne.'], ['playUrl', 'Ou adresse d\'un autre jeu en ligne', 'url', 'Facultatif : remplace le choix ci-dessus.']] },
  bd: { label: 'Bandes dessinées', one: 'une BD', fields: [
    ['title', "Titre de l'album", 'text'], ['desc', 'Résumé', 'textarea'], ['tags', 'Étiquettes', 'text', 'Auteurs, nombre de pages, date…'],
    ['link', 'Lien vers la boutique', 'url', "Quand on clique sur l'album, on arrive sur cette adresse."]] },
  adherents: { label: 'Adhérents', one: 'un adhérent', fields: [
    ['title', 'Nom', 'text'], ['role', "Fonction dans l'association", 'text'], ['desc', 'Quelques mots (affichés au survol)', 'textarea'], ['games', 'Jeux et projets associés', 'checks']] }
};
const IMG_LABEL = { festivals: "Image de l'affiche", jeux: 'Visuel de la boîte', escape: 'Visuel', bd: 'Couverture', adherents: 'Photo' };
const PAGES = [['dash', 'Tableau de bord'], '-', ...Object.keys(TYPES).map(k => [k, TYPES[k].label]), '-',
  ['identite', 'Apparence › Logo & identité'], ['couleurs', 'Apparence › Couleurs'], ['menu', 'Apparence › Menu & liens'], ['textes', 'Textes de l\'accueil & des pages'], ['contact', 'Contact & pied de page'], '-', ['maj', 'Mises à jour']];
PAGES.splice(PAGES.indexOf('-', 2), 0, ['enligne', 'Jeux en ligne › Gare au Chacal']);
let page = 'dash', edit = null, notice = '', draft = null;
const S = () => B.state.settings;
const uid = p => p + Date.now().toString(36) + Math.random().toString(36).slice(2, 5);
const get = (o, p) => p.split('.').reduce((a, k) => a == null ? a : a[k], o);
const set = (o, p, v) => { const ks = p.split('.'); const last = ks.pop(); ks.reduce((a, k) => a[k], o)[last] = v; };

function resizeImage(file, max = 900) {
  return new Promise((ok, ko) => {
    const r = new FileReader();
    r.onerror = ko;
    r.onload = () => { const im = new Image(); im.onerror = ko; im.onload = () => {
      const s = Math.min(1, max / Math.max(im.width, im.height)); const c = document.createElement('canvas');
      c.width = Math.round(im.width * s); c.height = Math.round(im.height * s); c.getContext('2d').drawImage(im, 0, 0, c.width, c.height);
      let d = c.toDataURL('image/webp', .85); if (!d.startsWith('data:image/webp')) d = c.toDataURL('image/png'); ok(d); }; im.src = r.result; };
    r.readAsDataURL(file);
  });
}
function persist(msg) {
  const ok = B.save();
  notice = ok ? msg : 'warn:La sauvegarde a échoué : l\'espace du navigateur est plein (trop d\'images lourdes ?). Les changements restent visibles jusqu\'à la fermeture de la page.';
  B.render(); draw();
}
const fld = (label, inner, help) => `<div class="fld"><label>${label}</label>${inner}${help ? `<div class="help">${help}</div>` : ''}</div>`;
const sIn = (path, label, help) => fld(label, `<input type="text" data-path="${path}" value="${esc(get(S(), path))}">`, help);
const sTa = (path, label, help, rows = 3) => fld(label, `<textarea rows="${rows}" data-path="${path}">${esc(get(S(), path))}</textarea>`, help);
const saveBtn = '<p><button class="wpbtn" data-act="save-settings">Enregistrer les modifications</button></p>';

function listView(t) {
  const T = TYPES[t], arr = B.state[t];
  const rows = arr.map((x, i) => `<tr><td>${x.img ? `<img src="${esc(x.img)}" alt="">` : '<span class="ph">aucune</span>'}</td>
    <td><b>${esc(x.title)}</b>${x.kind ? ` — ${esc(x.kind)}` : ''}${x.role ? `<br><span style="color:#646970">${esc(x.role)}</span>` : ''}${x.date ? `<br><span style="color:#646970">${esc(x.date)}</span>` : ''}
    <div class="rowa"><button data-act="edit" data-id="${x.id}">Modifier</button><button data-act="up" data-id="${x.id}" ${i ? '' : 'disabled'}>Monter</button><button data-act="down" data-id="${x.id}" ${i < arr.length - 1 ? '' : 'disabled'}>Descendre</button><button class="d" data-act="del" data-id="${x.id}">Supprimer</button></div></td>
    <td>${x.link ? `<a href="${esc(x.link)}" target="_blank" rel="noopener">${esc(x.link.replace(/^https?:\/\//, '').slice(0, 40))}</a>` : '<span style="color:#8c8f94">—</span>'}</td></tr>`).join('');
  return `<h1>${T.label} <button class="wpbtn sec" data-act="new">Ajouter ${T.one}</button></h1>
  <p style="color:#646970">L'ordre de la liste est l'ordre d'affichage sur le site. ${t === 'adherents' ? '' : 'Les 3 premiers sont mis en avant sur la page d\'accueil.'}</p>
  <table class="wptable"><thead><tr><th style="width:70px">Image</th><th>Titre</th><th>Lien</th></tr></thead><tbody>${rows || '<tr><td colspan="3">Aucun élément. Cliquez sur « Ajouter ».</td></tr>'}</tbody></table>`;
}
function editView(t) {
  const T = TYPES[t], x = draft;
  const choices = [['Jeux', B.state.jeux], ['Escape Box & accessoires', B.state.escape], ['BD', B.state.bd]];
  const main = T.fields.map(([k, label, type, help, opts]) => {
    if (k === 'title') return fld(label, `<input type="text" class="titlein" data-f="title" value="${esc(x.title)}" placeholder="Saisissez le titre">`);
    if (type === 'textarea') return fld(label, `<textarea rows="5" data-f="${k}">${esc(x[k])}</textarea>`, help);
    if (type === 'playsel') return fld(label, `<select data-f="${k}"><option value="">Aucun</option>${(B.state.navGames || []).map(g => `<option value="${esc(g.id)}"${x[k] === g.id ? ' selected' : ''}>${esc(g.title)} (version navigateur)</option>`).join('')}</select>`, help);
    if (type === 'select') return fld(label, `<select data-f="${k}">${opts.map(o => `<option${x[k] === o ? ' selected' : ''}>${o}</option>`).join('')}</select>`, help);
    if (type === 'checks') return fld(label, `<div class="checks">${choices.map(([g, arr]) => arr.length ? `<b>${g}</b>` + arr.map(it => `<label><input type="checkbox" data-g value="${it.id}"${(x.games || []).includes(it.id) ? ' checked' : ''}> ${esc(it.title)}</label>`).join('') : '').join('')}</div>`, help);
    return fld(label, `<input type="${type === 'url' ? 'text' : 'text'}" data-f="${k}" value="${esc(x[k])}"${type === 'url' ? ' placeholder="https://"' : ''}>`, help);
  }).join('');
  return `<h1>${edit.id ? 'Modifier' : 'Ajouter'} ${T.one}</h1><div class="wpgrid"><div class="wpbox">${main}</div>
  <div><div class="wpbox"><h2 style="margin-top:0">Publier</h2><p><button class="wpbtn" data-act="publish">${edit.id ? 'Mettre à jour' : 'Publier'}</button> <button class="wpbtn sec" data-act="cancel">Annuler</button></p></div>
  <div class="wpbox"><h2 style="margin-top:0">${IMG_LABEL[t]}</h2><div class="imgprev">${x.img ? `<img src="${esc(x.img)}" alt="">` : 'Aucune image'}</div>
  <input type="file" accept="image/*" data-act="img"> ${x.img ? '<p><button class="wpbtn del" data-act="noimg">Retirer l\'image</button></p>' : ''}<p class="help" style="color:#646970">Image redimensionnée automatiquement.</p></div></div></div>`;
}
const GCARDS = [['b0', 'Cheval', '🐴'], ['b1', 'Vache', '🐄'], ['b2', 'Mouton', '🐑'], ['b3', 'Chèvre', '🐐'], ['b4', 'Cochon', '🐖'], ['b5', 'Lapin', '🐇'], ['b6', 'Canard', '🦆'], ['b7', 'Poule', '🐔'], ['b8', 'Poussin', '🐤'], ['c', 'Chacal', '🐺'], ['jd', 'Chien de garde', '🐕'], ['jf', 'Renard', '🦊'], ['back', 'Dos des cartes', '🐾']];
const GEMO = { skinner: '🐔', nico: '🐄', tan: '🦊', tipim: '🐺' };
const gIn = (k, label, help) => fld(label, `<input type="text" data-gp="${k}" value="${esc(navG()[k])}">`, help);
function navG() { return B.state.navGames.find(g => g.id === 'gac') || B.state.navGames[0]; }
function gamesView() {
  const g = navG(); if (!g) return '<h1>Jeux en ligne</h1><p>Aucun jeu en ligne.</p>';
  const linked = B.allItems().filter(x => x.play === g.id).map(x => esc(x.title)).join(', ') || 'aucune fiche pour le moment';
  return `<h1>Jeux en ligne : ${esc(g.title)} <a class="wpbtn sec" style="display:inline-flex;align-items:center;text-decoration:none" href="${esc(g.path)}">Ouvrir le jeu ↗</a></h1>
  <div class="notice info">Bouton « Tester le jeu » affiché sur : <b>${linked}</b>. Pour le relier à une autre fiche, modifiez la fiche du jeu (champ « Jeu en ligne associé »).</div>
  <div class="wpbox"><h2 style="margin-top:0">Fin de partie et boutique</h2>
    ${gIn('shopUrl', 'Lien de la boutique (bouton de fin de partie)', 'Gagnant ou perdant, le joueur est invité à acheter le jeu à cette adresse.')}
    ${gIn('winMsg', 'Message quand le joueur gagne')}${gIn('winBtn', 'Bouton quand le joueur gagne')}
    ${gIn('loseMsg', 'Message quand le joueur perd')}${gIn('loseBtn', 'Bouton quand le joueur perd')}
    ${fld('Table de jeu par défaut', `<select data-gp="table">${[['miel', 'Bois miel'], ['sombre', 'Bois sombre'], ['vert', 'Tapis vert'], ['nappe', 'Nappe à carreaux']].map(([v, l]) => `<option value="${v}"${g.table === v ? ' selected' : ''}>${l}</option>`).join('')}</select>`)}
    <p><button class="wpbtn" data-act="gsave">Enregistrer</button></p></div>
  <div class="wpbox"><h2 style="margin-top:0">Adversaires (bots)</h2>
    <table class="wptable"><thead><tr><th style="width:70px">Avatar</th><th>Nom, niveau et présentation</th></tr></thead><tbody>
    ${g.bots.map((b, i) => `<tr><td>${g.av[b.id] ? `<img src="${esc(g.av[b.id])}" alt="" style="border-radius:50%;object-fit:cover">` : `<span class="ph" style="font-size:28px">${GEMO[b.id] || '🙂'}</span>`}</td>
      <td><div class="fld"><input type="text" data-gb="${i}:name" value="${esc(b.name)}" aria-label="Nom"></div><div class="fld"><input type="text" data-gb="${i}:tag" value="${esc(b.tag)}" aria-label="Niveau"></div><div class="fld"><input type="text" data-gb="${i}:desc" value="${esc(b.desc)}" aria-label="Présentation"></div>
      <div class="rowa"><label class="wpbtn sec" style="display:inline-flex;align-items:center">Changer l'avatar<input type="file" accept="image/*" data-gup="av:${esc(b.id)}" hidden></label>${g.av[b.id] ? `<button data-act="greset" data-k="av:${esc(b.id)}">Avatar d'origine</button>` : ''}</div></td></tr>`).join('')}
    </tbody></table><p><button class="wpbtn" data-act="gsave">Enregistrer les adversaires</button></p></div>
  <div class="wpbox" style="max-width:none"><h2 style="margin-top:0">Design des cartes</h2><p style="color:#646970">Une image par type de carte (cadrée automatiquement au format carte). Sans image, la carte d'origine est utilisée.</p>
    <div class="gcards">${GCARDS.map(([k, l, e]) => `<div class="gcard"><div class="gimg">${g.cards[k] ? `<img src="${esc(g.cards[k])}" alt="">` : `<span>${e}</span>`}</div><b>${l}</b>
      <label class="wpbtn sec" style="display:inline-flex;align-items:center">Image<input type="file" accept="image/*" data-gup="card:${k}" hidden></label>${g.cards[k] ? `<button class="wpbtn del" data-act="greset" data-k="card:${k}">Retirer</button>` : ''}</div>`).join('')}</div></div>`;
}
function cropTo(file, W, H) {
  return new Promise((ok, ko) => { const im = new Image(); im.onerror = ko; im.onload = () => {
    const c = document.createElement('canvas'); c.width = W; c.height = H; const x = c.getContext('2d'), r = Math.max(W / im.naturalWidth, H / im.naturalHeight), w = im.naturalWidth * r, h = im.naturalHeight * r;
    x.fillStyle = '#fff'; x.fillRect(0, 0, W, H); x.drawImage(im, (W - w) / 2, (H - h) / 2, w, h); URL.revokeObjectURL(im.src); ok(c.toDataURL('image/jpeg', .82)); };
    im.src = URL.createObjectURL(file); });
}
function readGame() {
  const g = navG();
  root.querySelectorAll('[data-gp]').forEach(el => g[el.dataset.gp] = el.value);
  root.querySelectorAll('[data-gb]').forEach(el => { const [i, k] = el.dataset.gb.split(':'); g.bots[+i][k] = el.value; });
}
function settingsView(p) {
  if (p === 'enligne') return gamesView();
  const s = S();
  if (p === 'identite') return `<h1>Logo & identité</h1><div class="wpbox">
    ${sIn('siteName', 'Nom du site')}<div class="fld"><label><input type="checkbox" data-path="showSiteName"${s.showSiteName ? ' checked' : ''}> Afficher le nom à côté du logo</label></div>
    <div class="fld"><label>Logo</label><div class="imgprev" style="max-width:220px">${s.logo ? `<img src="${esc(s.logo)}" alt="">` : 'Aucun logo'}</div><input type="file" accept="image/*" data-act="logo"></div>
    <div class="fld"><label>Taille du logo : <span id="lsv">${s.logoSize}</span> px</label>
    <div class="seg">${[['Petit', 72], ['Moyen', 96], ['Grand', 140], ['Très grand', 180]].map(([n, v]) => `<button data-act="lsize" data-v="${v}" class="${+s.logoSize === v ? 'on' : ''}">${n}</button>`).join('')}</div>
    <input type="range" min="48" max="220" step="2" value="${s.logoSize}" data-act="lrange" style="width:100%;max-width:400px"><div class="help">L'aperçu se met à jour en direct sur le site (fermez l'administration pour voir).</div></div>${saveBtn}</div>`;
  if (p === 'couleurs') return `<h1>Couleurs</h1><div class="wpbox"><div class="colors">
    ${[['primary', 'Couleur principale (menu, titres)'], ['dark', 'Couleur foncée (pied de page)'], ['accent', 'Couleur d\'accent (boutique, badges)'], ['soft', 'Fond des sections claires']].map(([k, l]) => `<label><input type="color" data-path="colors.${k}" value="${esc(s.colors[k])}">${l}</label>`).join('')}
    </div><p style="margin-top:16px"><button class="wpbtn" data-act="save-settings">Enregistrer les modifications</button> <button class="wpbtn sec" data-act="colors-reset">Revenir aux couleurs d'origine</button></p></div>`;
  if (p === 'menu') return `<h1>Menu & liens</h1><div class="wpbox"><h2 style="margin-top:0">Onglets du menu</h2><p class="help" style="color:#646970">Renommez, masquez ou réordonnez les onglets.</p>
    <div class="menurows">${s.menu.map((m, i) => `<div class="menurow"><input type="checkbox" data-mv="${i}"${m.visible ? ' checked' : ''} title="Visible"><input type="text" data-ml="${i}" value="${esc(m.label)}"><button data-act="mup" data-i="${i}" title="Monter">↑</button><button data-act="mdown" data-i="${i}" title="Descendre">↓</button></div>`).join('')}</div>
    <h2>Boutique</h2>${sIn('shopUrl', 'Adresse de la boutique en ligne', 'Utilisée par l\'onglet Boutique du menu et du pied de page.')}
    <h2>Boutons</h2>${sIn('sections.itemBtn', 'Texte du lien sur les jeux, BD et accessoires')}
    ${sIn('hero.btn1Label', 'Accueil : texte du 1er bouton')}${sIn('hero.btn1Link', 'Accueil : lien du 1er bouton', 'Une page du site (#jeux, #contact…) ou une adresse complète.')}
    ${sIn('hero.btn2Label', 'Accueil : texte du 2e bouton')}${sIn('hero.btn2Link', 'Accueil : lien du 2e bouton')}
    ${sIn('sections.festBtn', 'Bouton « Tous les festivals »')}${sIn('sections.jeuxBtn', 'Bouton « Voir tous les jeux »')}${sIn('sections.bdBtn', 'Bouton « Voir toutes les BD »')}
    <p class="help" style="color:#646970">Les liens de chaque jeu, BD, accessoire et affiche se règlent dans leur fiche.</p>${saveBtn}</div>`;
  if (p === 'textes') return `<h1>Textes de l'accueil & des pages</h1><div class="wpbox"><h2 style="margin-top:0">Présentation (accueil)</h2>
    <div class="fld"><label>Image de gauche</label><div class="imgprev" style="max-width:260px">${s.hero.image ? `<img src="${esc(s.hero.image)}" alt="">` : 'Aucune image'}</div><input type="file" accept="image/*" data-act="heroimg"></div>
    ${sIn('hero.title', 'Titre')}${sTa('hero.text1', 'Paragraphe 1')}${sTa('hero.text2', 'Paragraphe 2')}
    <h2>Sections de l'accueil</h2>${sIn('sections.festTitle', 'Titre festivals')}${sIn('sections.festText', 'Texte festivals')}${sIn('sections.jeuxTitle', 'Titre jeux')}${sIn('sections.jeuxText', 'Texte jeux')}${sIn('sections.bdTitle', 'Titre BD')}${sIn('sections.bdText', 'Texte BD')}
    <h2>Introductions des pages</h2>${Object.keys(s.pages).map(k => sTa('pages.' + k, 'Page ' + ((s.menu.find(m => m.id === k) || {}).label || k), '', 2)).join('')}${saveBtn}</div>`;
  if (p === 'contact') return `<h1>Contact & pied de page</h1><div class="wpbox"><h2 style="margin-top:0">Blocs de la page Contact</h2>
    ${s.contact.map((c, i) => `<div class="wpbox" style="background:#f6f7f7">${sIn(`contact.${i}.title`, 'Titre')}${sTa(`contact.${i}.text`, 'Texte', 'Une information par ligne.', 4)}<button class="wpbtn del" data-act="cdel" data-i="${i}">Supprimer ce bloc</button></div>`).join('')}
    <p><button class="wpbtn sec" data-act="cadd">Ajouter un bloc</button></p>
    <h2>Pied de page</h2>${sTa('footer.about', 'Présentation')}${sTa('footer.address', 'Coordonnées', 'Une information par ligne.', 4)}${sTa('footer.extra', 'Colonne Distribution', 'Une information par ligne.')}${sIn('footer.legal', 'Mention en bas de page')}${saveBtn}</div>`;
  if (p === 'maj') return `<h1>Mises à jour</h1>
    <div class="notice info">Version du thème installée : <b>${esc(window.BREV_THEME_VERSION)}</b>. Vos contenus sont enregistrés à part du thème.</div>
    <div class="wpbox"><h2 style="margin-top:0">Comment se passent les mises à jour</h2>
    <p>Le thème (mise en page, styles, fonctions) et vos contenus (affiches, jeux, BD, adhérents, couleurs, liens, textes) sont séparés. Quand une nouvelle version du thème arrive, il suffit de remplacer le dossier du thème : vos contenus sont repris automatiquement et les nouveaux réglages apparaissent avec leur valeur par défaut, sans rien refaire.</p>
    <p>Dans WordPress, ce sera la même chose : le thème se met à jour depuis <i>Apparence › Thèmes</i>, et les contenus restent dans la base de données du site.</p></div>
    <div class="wpbox"><h2 style="margin-top:0">Sauvegarder et transférer vos contenus</h2>
    <p>Exportez vos contenus avant une mise à jour, ou pour les transférer sur un autre ordinateur ou navigateur.</p>
    <p><button class="wpbtn" data-act="export">Exporter mes contenus (.json)</button> <label class="wpbtn sec" style="display:inline-flex;align-items:center">Importer un fichier<input type="file" accept=".json,application/json" data-act="import" hidden></label></p>
    <div id="jsonout"></div></div>
    <div class="wpbox"><h2 style="margin-top:0">Réinitialiser</h2><p>Revenir aux contenus de démonstration d'origine. Vos modifications seront effacées de ce navigateur.</p><p><button class="wpbtn del" data-act="reset">Tout réinitialiser</button></p></div>`;
  const c = k => B.state[k].length;
  return `<h1>Tableau de bord</h1><div class="notice info">Ceci est un aperçu de l'administration du futur thème WordPress. Les modifications sont enregistrées dans ce navigateur.</div>
  <div class="dash">${Object.keys(TYPES).map(k => `<button data-go="${k}"><b>${c(k)}</b>${TYPES[k].label}</button>`).join('')}</div>
  <div class="wpbox" style="margin-top:18px"><h2 style="margin-top:0">Raccourcis</h2><p><button class="wpbtn sec" data-go="festivals" data-new="1">Nouvelle affiche</button> <button class="wpbtn sec" data-go="jeux" data-new="1">Nouveau jeu</button> <button class="wpbtn sec" data-go="bd" data-new="1">Nouvelle BD</button> <button class="wpbtn sec" data-go="adherents" data-new="1">Nouvel adhérent</button> <button class="wpbtn sec" data-go="couleurs">Changer les couleurs</button> <button class="wpbtn sec" data-go="menu">Modifier les liens</button></p></div>`;
}
function draw() {
  let body;
  if (TYPES[page]) body = edit ? editView(page) : listView(page); else body = settingsView(page);
  const n = notice ? `<div class="notice${notice.startsWith('warn:') ? ' warn' : ''}">${esc(notice.replace(/^warn:/, ''))}</div>` : '';
  root.innerHTML = `<div class="wpbar"><b>Ⓦ ${esc(S().siteName)}</b><span style="color:#a7aaad">Administration (aperçu)</span><a class="sp" href="#accueil">Voir le site ↗</a></div>
  <nav class="wpside">${PAGES.map(p => p === '-' ? '<hr>' : `<button data-go="${p[0]}" class="${page === p[0] ? 'on' : ''}">${p[1]}</button>`).join('')}</nav>
  <div class="wpmain">${n}${body}</div>`;
  notice = '';
}
function readSettings() {
  const s = S();
  root.querySelectorAll('[data-path]').forEach(el => set(s, el.dataset.path, el.type === 'checkbox' ? el.checked : el.value));
  root.querySelectorAll('[data-ml]').forEach(el => s.menu[+el.dataset.ml].label = el.value);
  root.querySelectorAll('[data-mv]').forEach(el => s.menu[+el.dataset.mv].visible = el.checked);
}
function readDraft() {
  root.querySelectorAll('[data-f]').forEach(el => draft[el.dataset.f] = el.value.trim());
  if (root.querySelector('[data-g]')) draft.games = [...root.querySelectorAll('[data-g]:checked')].map(el => el.value);
}
function blank(t) { const o = { id: uid(t[0]), title: '', desc: '', img: '', link: '' }; if (t === 'festivals') Object.assign(o, { date: '', place: '', games: [] }); if (t === 'adherents') Object.assign(o, { role: '', games: [] }); if (t === 'escape') o.kind = 'Escape Box'; if (t !== 'festivals' && t !== 'adherents') o.tags = ''; return o; }

root.addEventListener('click', async e => {
  const g = e.target.closest('[data-go]');
  if (g) { page = g.dataset.go; edit = null; if (g.dataset.new) { edit = { id: null }; draft = blank(page); } draw(); root.querySelector('.wpmain').scrollTop = 0; return; }
  const b = e.target.closest('[data-act]'); if (!b || b.tagName === 'INPUT') return;
  const a = b.dataset.act, arr = B.state[page], id = b.dataset.id, i = arr ? arr.findIndex(x => x.id === id) : -1;
  if (a === 'new') { edit = { id: null }; draft = blank(page); draw(); }
  else if (a === 'edit') { edit = { id }; draft = JSON.parse(JSON.stringify(arr[i])); draw(); }
  else if (a === 'up' && i > 0) { [arr[i - 1], arr[i]] = [arr[i], arr[i - 1]]; persist('Ordre mis à jour.'); }
  else if (a === 'down' && i < arr.length - 1) { [arr[i + 1], arr[i]] = [arr[i], arr[i + 1]]; persist('Ordre mis à jour.'); }
  else if (a === 'del') { if (b.dataset.sure) { arr.splice(i, 1); persist('Élément supprimé.'); } else { b.dataset.sure = 1; b.textContent = 'Confirmer la suppression ?'; } }
  else if (a === 'cancel') { edit = null; draw(); }
  else if (a === 'noimg') { readDraft(); draft.img = ''; draw(); }
  else if (a === 'publish') {
    readDraft(); if (!draft.title) { notice = 'warn:Le titre est obligatoire.'; draw(); return; }
    if (edit.id) arr[arr.findIndex(x => x.id === edit.id)] = draft; else arr.unshift(draft);
    const msg = edit.id ? 'Élément mis à jour.' : 'Élément publié : il apparaît en premier sur le site.'; edit = null; persist(msg);
  }
  else if (a === 'gsave') { readGame(); persist('Réglages du jeu enregistrés : ils s\'appliquent à la prochaine ouverture du jeu.'); }
  else if (a === 'greset') { readGame(); const [kind, k] = b.dataset.k.split(':'); delete navG()[kind === 'av' ? 'av' : 'cards'][k]; persist('Image d\'origine rétablie.'); }
  else if (a === 'save-settings') { readSettings(); persist('Réglages enregistrés.'); }
  else if (a === 'lsize') { S().logoSize = +b.dataset.v; readSettingsKeepSize(); persist('Taille du logo enregistrée.'); }
  else if (a === 'colors-reset') { S().colors = JSON.parse(JSON.stringify(window.BREV_DEFAULTS.settings.colors)); persist('Couleurs d\'origine rétablies.'); }
  else if (a === 'mup' || a === 'mdown') { readSettings(); const m = S().menu, k = +b.dataset.i, j = a === 'mup' ? k - 1 : k + 1; if (j >= 0 && j < m.length) { [m[k], m[j]] = [m[j], m[k]]; } persist('Menu mis à jour.'); }
  else if (a === 'cadd') { readSettings(); S().contact.push({ title: 'Nouveau bloc', text: '' }); persist('Bloc ajouté.'); }
  else if (a === 'cdel') { readSettings(); S().contact.splice(+b.dataset.i, 1); persist('Bloc supprimé.'); }
  else if (a === 'export') {
    const json = JSON.stringify(B.state, null, 1), name = 'brevinoise-contenus-' + new Date().toISOString().slice(0, 10) + '.json';
    try { const u = URL.createObjectURL(new Blob([json], { type: 'application/json' })); const l = document.createElement('a'); l.href = u; l.download = name; document.body.appendChild(l); l.click(); l.remove(); setTimeout(() => URL.revokeObjectURL(u), 2000); } catch (err) {}
    document.getElementById('jsonout').innerHTML = `<p class="help" style="color:#646970">Fichier « ${name} » proposé au téléchargement. S'il n'apparaît pas, copiez le texte ci-dessous :</p><textarea class="json" readonly>${esc(json.slice(0, 200000))}</textarea>`;
  }
  else if (a === 'reset') { if (b.dataset.sure) { B.reset(); B.render(); notice = 'Contenus de démonstration rétablis.'; draw(); } else { b.dataset.sure = 1; b.textContent = 'Confirmer : tout effacer ?'; } }
});
function readSettingsKeepSize() { const v = S().logoSize; readSettings(); S().logoSize = v; }
root.addEventListener('input', e => {
  if (e.target.dataset.act === 'lrange') { S().logoSize = +e.target.value; document.getElementById('lsv').textContent = e.target.value; document.documentElement.style.setProperty('--logo', e.target.value + 'px'); }
  if (e.target.type === 'color') document.documentElement.style.setProperty('--' + e.target.dataset.path.split('.')[1], e.target.value);
});
root.addEventListener('change', async e => {
  const a = e.target.dataset.act, f = e.target.files && e.target.files[0];
  if (e.target.dataset.gup && f) { try { readGame(); const [kind, k] = e.target.dataset.gup.split(':'); const g = navG();
    if (kind === 'av') g.av[k] = await cropTo(f, 220, 220); else g.cards[k] = await cropTo(f, 300, 420); persist('Image enregistrée.'); } catch (err) { notice = 'warn:Impossible de lire cette image.'; draw(); } return; }
  if (a === 'lrange') { readSettingsKeepSize(); persist('Taille du logo enregistrée.'); return; }
  if (!f) return;
  try {
    if (a === 'img') { readDraft(); draft.img = await resizeImage(f, page === 'adherents' ? 600 : 900); draw(); }
    else if (a === 'logo') { readSettings(); S().logo = await resizeImage(f, 400); persist('Logo mis à jour.'); }
    else if (a === 'heroimg') { readSettings(); S().hero.image = await resizeImage(f, 1000); persist('Image mise à jour.'); }
    else if (a === 'import') { const data = JSON.parse(await f.text()); B.state = B.upgrade(data); persist('Contenus importés.'); }
  } catch (err) { notice = 'warn:Impossible de lire ce fichier.'; draw(); }
});
window.BrevAdmin = {
  open() { if (root.hidden) { root.hidden = false; document.body.style.overflow = 'hidden'; draw(); } },
  close() { root.hidden = true; document.body.style.overflow = ''; }
};
}
