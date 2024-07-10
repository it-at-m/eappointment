import $ from "jquery";
import BaseView from '../lib/baseview';

$('.confirm-before').on('click', (ev) => {
    BaseView.loadDialogStatic('<div class="lightbox__content" role="dialog" aria-modal="true"><section tabindex="0" class="board dialog" data-reload="">\n' +
        '\n' +
        '        <div class="header board__header">\n' +
        '        <h2 tabindex="0" class="board__heading">\n' +
        '            <i aria-hidden="true" title="Eintrag löschen" class="fas fa-info-circle"></i> Eintrag löschen        </h2>\n' +
        '    </div>\n' +
        '    \n' +
        '    <div tabindex="0" class="body board__body ">\n' +
        '                \n' +
        '\t\t\t\t        \n' +
        '                                        <p>' + ev.target.dataset.confirmBeforeDescription + '</p>\n' +
        '        <div class="form-actions">\n' +
        '            <a data-action-abort="" class="button button--diamond button-abort" href="#">Abbruch</a>\n' +
        '            <a data-action-ok="" data-id="1" data-name="Wartenummer 1" class="button button--destructive button-ok" href="#">' + ev.target.dataset.confirmBeforeYesButton + '</a>\n' +
        '        </div>\n' +
        '                </div>\n' +
        '\n' +
        '    \n' +
        '</section></div>', () => { window.location.href = ev.target.href; }, () => { return false }, false);

    return false
})