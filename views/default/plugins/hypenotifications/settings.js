define(function(require) {
    var $ = require('jquery');

    $(document).on('change', '.notifications-transport-selector', function() {

    	var val = $(this).val();

        $('.notifications-settings-tabs').find('[rel="' + val + '"]').trigger('click');
    });
});
