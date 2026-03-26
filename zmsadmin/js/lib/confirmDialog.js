/**
 * Shared helper for confirm dialogs (lightbox with title, message, Abbruch, OK).
 * Use with BaseView.loadDialogStatic so the same UI is used instead of native confirm().
 */

function escapeHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(String(str)));
    return div.innerHTML;
}

/**
 * @param {string} title - Dialog heading (escaped)
 * @param {string} message - Body text; may contain trusted HTML (e.g. <br>)
 * @param {string} [okButtonText='Bestätigen'] - Label for the confirm button (escaped)
 * @returns {string} HTML for the lightbox dialog (section.board.dialog with .button-ok, .button-abort)
 */
export function buildConfirmDialogHtml(title, message, okButtonText = 'Bestätigen') {
    const safeTitle = escapeHtml(title);
    const safeOk = escapeHtml(okButtonText);
    return '<div class="lightbox__content" role="dialog" aria-modal="true"><section tabindex="0" class="board dialog" data-reload="">' +
        '<div class="header board__header">' +
        '<h2 tabindex="0" class="board__heading">' +
        '<i aria-hidden="true" title="' + safeTitle + '" class="fas fa-info-circle"></i> ' + safeTitle + '</h2>' +
        '</div>' +
        '<div tabindex="0" class="body board__body">' +
        '<p>' + message + '</p>' +
        '<div class="form-actions">' +
        '<a data-action-abort="" class="button button--diamond button-abort" href="#">Abbruch</a>' +
        '<a data-action-ok="" class="button button--destructive button-ok" href="#">' + safeOk + '</a>' +
        '</div>' +
        '</div>' +
        '</section></div>';
}
