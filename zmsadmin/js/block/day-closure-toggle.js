import $ from 'jquery';
import BaseView from '../lib/baseview';
import { buildConfirmDialogHtml } from '../lib/confirmDialog';

let dayClosureToggleInFlight = false;

/**
 * ZMSKVR-42: day lock/unlock on Öffnungszeiten Tagesübersicht uses the same
 * lightbox confirm dialog as other opening-hours actions (not native confirm()).
 *
 * Visibility must use inline display — .message--* sets display:block, which
 * overrides both [hidden] and a non-existent .hidden class, so both banners
 * (Sperren + Entsperren) were shown at once.
 */
function applyDayClosureState(existing) {
    const locked = !!existing;
    const $close = $('#closure-close');
    const $open = $('#closure-open');
    if (locked) {
        $close.show();
        $open.hide();
    } else {
        $close.hide();
        $open.show();
    }
    $close.find('.js-day-closure-toggle').data('locked', locked);
    $open.find('.js-day-closure-toggle').data('locked', locked);
}

function showDayClosureError(err) {
    const status = err && err.status ? ' (HTTP ' + err.status + ')' : '';
    $('#day-closure-error').remove();
    const $msg = $('<div id="day-closure-error" class="message message--error" role="alert">')
        .text('Die Sperrung/Entsperrung konnte nicht gespeichert werden' + status + '.');
    $('#closure-close, #closure-open').first().before($msg);
}

function bindDayClosureToggle() {
    $(document).on('click', '.js-day-closure-toggle', (ev) => {
        ev.preventDefault();
        if (dayClosureToggleInFlight) {
            return;
        }
        const $btn = $(ev.currentTarget);
        const locked = $btn.data('locked') === true || $btn.data('locked') === 'true' || $btn.data('locked') === 1;
        const url = $btn.data('toggleUrl');
        if (!url) {
            return;
        }

        const title = locked ? 'Terminbuchung entsperren' : 'Terminbuchung sperren';
        const message = locked
            ? 'Möchten Sie wirklich die Terminbuchung für diesen Tag entsperren?'
            : 'Möchten Sie wirklich die Terminbuchung für diesen Tag sperren?';
        const okLabel = locked ? 'Entsperren' : 'Sperren';

        const dialogHtml = buildConfirmDialogHtml(title, message, okLabel);
        BaseView.loadDialogStatic(
            dialogHtml,
            () => {
                if (dayClosureToggleInFlight) {
                    return;
                }
                dayClosureToggleInFlight = true;
                $('#day-closure-error').remove();
                $.ajax(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    data: JSON.stringify({})
                }).done((data) => {
                    const fromResponse = data && data.closure && data.closure.existing;
                    const existing = typeof fromResponse === 'boolean' ? fromResponse : !locked;
                    applyDayClosureState(existing);
                }).fail((err) => {
                    console.error('day closure toggle error', err);
                    showDayClosureError(err);
                }).always(() => {
                    dayClosureToggleInFlight = false;
                });
            },
            () => {},
            { $main: $('body') }
        );
    });
}

bindDayClosureToggle();
