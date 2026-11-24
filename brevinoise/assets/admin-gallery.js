/* Galerie d'images des fiches : choix dans la médiathèque, ordre, retrait. */
jQuery(function ($) {
	var btns = '<span class="brev-gal-btns"><button type="button" class="brev-g-left" aria-label="Avancer">◀</button><button type="button" class="brev-g-right" aria-label="Reculer">▶</button><button type="button" class="brev-g-del" aria-label="Retirer">✕</button></span>';
	function sync(box) {
		var ids = [];
		box.find('.brev-gal-list li').each(function () { ids.push(parseInt($(this).attr('data-id'), 10)); });
		box.find('input[type=hidden]').val(ids.join(','));
	}
	$(document).on('click', '.brev-gal-add', function (e) {
		e.preventDefault();
		var box = $(this).closest('.brev-gallery');
		var frame = wp.media({ title: 'Choisir des images', button: { text: 'Ajouter à la galerie' }, library: { type: 'image' }, multiple: 'add' });
		frame.on('select', function () {
			frame.state().get('selection').each(function (m) {
				var a = m.toJSON();
				if (box.find('li[data-id="' + a.id + '"]').length) { return; }
				var url = (a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url;
				var li = $('<li>').attr('data-id', a.id).append($('<img>').attr({ src: url, alt: '' })).append(btns);
				box.find('.brev-gal-list').append(li);
			});
			sync(box);
		});
		frame.open();
	});
	$(document).on('click', '.brev-g-del', function (e) {
		e.preventDefault();
		var box = $(this).closest('.brev-gallery');
		$(this).closest('li').remove();
		sync(box);
	});
	$(document).on('click', '.brev-g-left', function (e) {
		e.preventDefault();
		var li = $(this).closest('li'), box = li.closest('.brev-gallery');
		li.prev('li').before(li);
		sync(box);
	});
	$(document).on('click', '.brev-g-right', function (e) {
		e.preventDefault();
		var li = $(this).closest('li'), box = li.closest('.brev-gallery');
		li.next('li').after(li);
		sync(box);
	});
});
