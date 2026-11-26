/* La Brévinoise — interactions du site : menu mobile, recherche, filtres, fenêtres d'infos. */
(function () {
	'use strict';
	var $ = function (id) { return document.getElementById(id); };

	/* Menu mobile */
	var burger = $('burger'), menu = $('menu');
	if (burger && menu) {
		burger.addEventListener('click', function () {
			var o = menu.classList.toggle('open');
			burger.setAttribute('aria-expanded', String(o));
		});
	}

	/* Recherche et filtres des pages Jeux, BD, Escape Box */
	var q = $('q'), kinds = $('kinds'), list = $('list'), kind = '';
	function filter() {
		if (!list) return;
		var t = q ? q.value.toLowerCase().trim() : '', shown = 0;
		list.querySelectorAll('.card').forEach(function (c) {
			var ok = (!t || (c.dataset.search || '').indexOf(t) > -1) && (!kind || c.dataset.kind === kind);
			c.hidden = !ok; if (ok) shown++;
		});
		var nr = $('noresult'); if (nr) nr.hidden = shown > 0;
	}
	if (q) q.addEventListener('input', filter);
	if (kinds) kinds.addEventListener('click', function (e) {
		var b = e.target.closest('.chip'); if (!b) return;
		kind = b.dataset.k;
		kinds.querySelectorAll('.chip').forEach(function (c) { c.classList.toggle('on', c === b); });
		filter();
	});

	/* Fenêtre d'infos : survol = petite fenêtre à côté ; clic ou toucher = grande fenêtre au centre */
	var pop = document.createElement('div'), bg = document.createElement('div');
	pop.className = 'pop'; pop.hidden = true; pop.setAttribute('role', 'dialog'); pop.id = 'pop';
	bg.className = 'popbg'; bg.hidden = true;
	document.body.appendChild(bg); document.body.appendChild(pop);
	var timer = 0, owner = null, canHover = window.matchMedia('(hover: hover) and (pointer: fine)');
	function content(el) { var tp = $('pop-' + el.dataset.pop); return tp ? tp.innerHTML : ''; }
	function hide() {
		clearTimeout(timer); pop.hidden = true; bg.hidden = true; pop.className = 'pop';
		if (owner && pop.contains(document.activeElement)) owner.focus();
		owner = null;
	}
	function showFloat(el) {
		var html = content(el); if (!html) return;
		owner = el; pop.className = 'pop float'; pop.innerHTML = html; pop.hidden = false; bg.hidden = true;
		var r = el.getBoundingClientRect(), pw = pop.offsetWidth, ph = pop.offsetHeight, m = 12, W = window.innerWidth, H = window.innerHeight, left;
		if (r.right + m + pw <= W - 8) left = r.right + m;
		else if (r.left - m - pw >= 8) left = r.left - m - pw;
		else left = Math.max(8, Math.min(W - pw - 8, r.left + (r.width - pw) / 2));
		pop.style.left = left + 'px';
		pop.style.top = Math.max(8, Math.min(H - ph - 8, r.top + (r.height - ph) / 2)) + 'px';
	}
	function showModal(el) {
		var html = content(el); if (!html) return;
		clearTimeout(timer); owner = el; pop.className = 'pop modal'; pop.style.left = pop.style.top = '';
		pop.innerHTML = '<button class="pclose" aria-label="Fermer">✕</button>' + html;
		pop.hidden = false; bg.hidden = false; pop.querySelector('.pclose').focus();
	}
	document.addEventListener('mouseover', function (e) {
		if (!canHover.matches || pop.classList.contains('modal')) return;
		var t = e.target.closest('[data-pop]');
		if (t) { clearTimeout(timer); if (owner !== t) timer = setTimeout(function () { showFloat(t); }, 120); }
		else if (e.target.closest('#pop')) clearTimeout(timer);
		else if (owner) { clearTimeout(timer); timer = setTimeout(hide, 220); }
	});
	document.addEventListener('click', function (e) {
		if (e.target.closest('.pclose') || e.target === bg) return hide();
		if (e.target.closest('#pop')) return;
		var t = e.target.closest('[data-pop]');
		if (t) showModal(t); else if (!pop.hidden) hide();
	});
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && !pop.hidden) return hide();
		var t = e.target.closest && e.target.closest('[data-pop]');
		if (t && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); showModal(t); }
	});
	window.addEventListener('scroll', function () { if (pop.classList.contains('float')) hide(); }, { passive: true });
	/* Page de présentation : onglets Images / Vidéo, carrousel, vidéo chargée à l'ouverture de l'onglet */
	document.querySelectorAll('[data-carousel]').forEach(function (c) {
		var track = c.querySelector('.car-track'), prev = c.querySelector('.prev'), next = c.querySelector('.next');
		var count = c.parentNode.querySelector('.car-count'), slides = track.children;
		var calm = window.matchMedia('(prefers-reduced-motion: reduce)');
		function idx() { var w = track.clientWidth; return w ? Math.round(track.scrollLeft / w) : 0; }
		function update() {
			var n = slides.length, i = Math.min(n - 1, Math.max(0, idx()));
			if (count) count.textContent = (i + 1) + ' / ' + n;
			if (prev) prev.disabled = i <= 0;
			if (next) next.disabled = i >= n - 1;
			c.classList.toggle('single', n < 2);
			if (count) count.hidden = n < 2;
		}
		function go(d) { track.scrollTo({ left: (idx() + d) * track.clientWidth, behavior: calm.matches ? 'auto' : 'smooth' }); }
		if (prev) prev.addEventListener('click', function () { go(-1); });
		if (next) next.addEventListener('click', function () { go(1); });
		track.addEventListener('scroll', function () { window.requestAnimationFrame(update); }, { passive: true });
		track.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowRight') { e.preventDefault(); go(1); }
			else if (e.key === 'ArrowLeft') { e.preventDefault(); go(-1); }
		});
		window.addEventListener('resize', update);
		c.update = update; update();
	});
	var medias = document.querySelector('.medias');
	if (medias) {
		var tabs = [].slice.call(medias.querySelectorAll('[role=tab]'));
		var panels = [].slice.call(medias.querySelectorAll('[role=tabpanel]'));
		var openTab = function (name, focus) {
			tabs.forEach(function (t) {
				var on = t.id === 'tab-' + name;
				t.classList.toggle('on', on); t.setAttribute('aria-selected', String(on)); t.tabIndex = on ? 0 : -1;
				if (on && focus) t.focus();
			});
			panels.forEach(function (p) { p.hidden = p.id !== name; });
			var f = medias.querySelector('#' + name + ' iframe[data-src]');
			if (f) { f.src = f.getAttribute('data-src'); f.removeAttribute('data-src'); }
			var c = medias.querySelector('#' + name + ' [data-carousel]');
			if (c && c.update) c.update();
		};
		tabs.forEach(function (t, i) {
			t.addEventListener('click', function () { openTab(t.id.replace('tab-', '')); });
			t.addEventListener('keydown', function (e) {
				var d = e.key === 'ArrowRight' ? 1 : (e.key === 'ArrowLeft' ? -1 : 0);
				if (!d) return;
				e.preventDefault();
				openTab(tabs[(i + d + tabs.length) % tabs.length].id.replace('tab-', ''), true);
			});
		});
		var fromHash = function () {
			var h = location.hash.replace('#', '');
			if (h && /^[a-z]+$/.test(h) && medias.querySelector('#' + h + '[role=tabpanel]')) { openTab(h); medias.scrollIntoView(); return true; }
			return false;
		};
		if (!fromHash()) openTab(tabs[0].id.replace('tab-', ''));
		window.addEventListener('hashchange', fromHash);
	}
})();
