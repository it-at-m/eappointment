import $ from "jquery";

const maxChars = (element) => {
    const $textarea = $(element)

    $textarea.on('keyup', (ev) => {
        var limit = parseInt($(ev.target).attr('maxlength'));
        var text = $(ev.target).val();
        var chars = text.length;
        $(ev.target).closest('div').find('.maxcharsleft span').text(chars);
        if(chars > limit){
            var new_text = text.substr(0, limit);
            $(ev.target).val(new_text);
        }
    });
}

export default maxChars
