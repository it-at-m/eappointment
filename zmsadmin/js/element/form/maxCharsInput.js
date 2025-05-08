import $ from "jquery";

const maxCharsInput = (element) => {
    const $input = $(element)
    if ($input.is(':visible')) {
        initChars($input);
    }
    $input.on('input', (ev) => {
        initChars($(ev.target));
    });
}

function initChars($target) {
    var limit = parseInt($target.attr('maxlength'));
    var text = $target.val();
    var chars = text.length;
    $target.nextAll('.maxcharsleft').first().find('span').text(chars);
    if (chars > limit) {
        var new_text = text.substr(0, limit);
        $target.val(new_text);
    }
}

export default maxCharsInput
