//
// check for palm size device
//
export function is_palm() {
    // (getComputedStyle not working in iOS)
    var pc = $('#js-is_palm');
    return ('none' === pc.css('display'));
}
