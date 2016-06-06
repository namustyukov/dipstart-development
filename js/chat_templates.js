var TYPE_CUSTOMER = 1, TYPE_AUTHOR = 2, TYPE_CORRECTOR = 3;

$('.select_recipient').on('change', function () {
	$('.msg_answer').html('');
    var value = parseInt($(this).val());
    var submitButton = $('.chtpl0-submit1');
    var templateButton = $('.attach_template');
    $('.chat_templates_wrapper').addClass('hidden');
    if (TYPE_CUSTOMER !== value && TYPE_AUTHOR !== value && TYPE_CORRECTOR !== value) {
        templateButton.addClass('hidden');
        submitButton.addClass('disabled');
        return;
    }

    templateButton.removeClass('hidden');
    submitButton.removeClass('disabled');

    if (TYPE_CORRECTOR === value)
        templateButton.addClass('hidden');

    $('.template_type_' + value).removeClass('hidden');
    var recipient = TYPE_CUSTOMER === value ? 'Customer' : (TYPE_CORRECTOR === value ? 'Corrector' : 'Author');
    submitButton.data('recipient', recipient);
});

$('.chat_template_names').on('change', function () {
    var self = $(this);
    var templateIndex = self.val();
    var type = self.data('type');

    $('.chat_templates_' + type + ':not(.hidden)').addClass('hidden');
    $('#template_type_' + type + '_' + templateIndex).removeClass('hidden')
});

$('.template-submit').on('click', function () {
    var message = $('.chat_templates_wrapper:not(.hidden)').find('.chat_templates:not(.hidden)').val();
    tinymce.get('chat_message').execCommand('mceInsertContent', false, message);
});