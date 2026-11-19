/* Choix d'images dans la médiathèque (page Gare au Chacal). */
jQuery(function ($) {
	$(document).on('click', '.brev-pick', function (e) {
		e.preventDefault();
		var box = $(this).closest('.brev-media');
		var frame = wp.media({ title: 'Choisir une image', button: { text: 'Utiliser cette image' }, library: { type: 'image' }, multiple: false });
		frame.on('select', function () {
			var a = frame.state().get('selection').first().toJSON();
			var url = (a.sizes && a.sizes.medium) ? a.sizes.medium.url : a.url;
			box.find('input[type=hidden]').val(a.id);
			box.find('.brev-prev').html('<img src="' + url + '" alt="">');
			box.find('.brev-clear').prop('hidden', false);
		});
		frame.open();
	});
	$(document).on('click', '.brev-clear', function (e) {
		e.preventDefault();
		var box = $(this).closest('.brev-media');
		box.find('input[type=hidden]').val('');
		box.find('.brev-prev').html('<span>' + box.data('emoji') + '</span>');
		$(this).prop('hidden', true);
	});
});
