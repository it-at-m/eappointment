import $ from "jquery";

const maxChars = (element) => {
    const $element = $(element)
    if ($element.is(':visible')) {
        initChars($element);
    }
    $element.on('input keyup', (ev) => {
        initChars($(ev.target));
    });
}

function initChars($target) {
    var limit = parseInt($target.attr('maxlength'));
    var text = $target.val();
    var chars = text.length;
    $target.closest('div').find('.maxcharsleft span').text(chars);
    if (chars > limit) {
        var new_text = text.substr(0, limit);
        $target.val(new_text);
    }
}

export default maxChars
