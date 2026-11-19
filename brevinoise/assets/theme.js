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
})();
