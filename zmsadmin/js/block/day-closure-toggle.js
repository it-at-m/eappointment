import $ from 'jquery';
import BaseView from '../lib/baseview';
import { buildConfirmDialogHtml } from '../lib/confirmDialog';

/**
 * ZMSKVR-42: day lock/unlock on Öffnungszeiten Tagesübersicht uses the same
 * lightbox confirm dialog as other opening-hours actions (not native confirm()).
 *
 * Banner visibility must use the HTML `hidden` attribute — admin.css only
 * styles `[hidden]`, not a `.hidden` class, so class toggling never updated
 * the UI until a full page refresh.
 */
function applyDayClosureState(existing) {
    const locked = !!existing;
    const $close = $('#closure-close');
    const $open = $('#closure-open');
    $close.prop('hidden', !locked);
    $open.prop('hidden', locked);
    $close.find('.js-day-closure-toggle').data('locked', locked);
    $open.find('.js-day-closure-toggle').data('locked', locked);
}

function bindDayClosureToggle() {
    $(document).on('click', '.js-day-closure-toggle', (ev) => {
        ev.preventDefault();
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
                $.ajax(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    data: JSON.stringify({})
                }).done((data) => {
                    const fromResponse = data && data.closure && data.closure.existing;
                    // Prefer API flag; fall back to the inverse of the pre-click state.
                    const existing = typeof fromResponse === 'boolean' ? fromResponse : !locked;
                    applyDayClosureState(existing);
                }).fail((err) => {
                    console.log('day closure toggle error', err);
                });
            },
            () => {},
            { $main: $('body') }
        );
    });
}

bindDayClosureToggle();
