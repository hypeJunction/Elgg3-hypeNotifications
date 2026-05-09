import $ from 'jquery';
import 'jquery-ui/progressbar';
import Ajax from 'elgg/Ajax';

const ajax = new Ajax();

const next = function ($form) {
	var data = ajax.objectify($form);
	ajax.action($form.attr('action'), {
		data: data
	}).done(function () {

		var $limit = $form.find('[name="limit"]');
		var $offset = $form.find('[name="offset"]');
		var $count = $form.find('[name="count"]');

		var done = parseInt($offset.val()) + parseInt($limit.val());
		var count = parseInt($count.val());

		$offset.val(done);

		$('.elgg-progressbar').progressbar({
			value: done,
			max: count
		});

		if (done >= count) {
			return;
		}

		next($form);
	});
};

$(document).on('submit', '.elgg-form-admin-notifications-methods', function (e) {
	e.preventDefault();
	var $form = $(this);

	$form.find('[type="submit"]').prop('disabled', true);

	next($form);
});
