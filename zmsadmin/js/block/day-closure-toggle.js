import $ from 'jquery';
import BaseView from '../lib/baseview';
import { buildConfirmDialogHtml } from '../lib/confirmDialog';

/**
 * ZMSKVR-42: day lock/unlock on Öffnungszeiten Tagesübersicht uses the same
 * lightbox confirm dialog as other opening-hours actions (not native confirm()).
 */
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
                    const existing = data && data.closure && data.closure.existing;
                    const $close = $('#closure-close');
                    const $open = $('#closure-open');
                    if (existing) {
                        $close.removeClass('hidden');
                        $open.addClass('hidden');
                    } else {
                        $close.addClass('hidden');
                        $open.removeClass('hidden');
                    }
                    $close.find('.js-day-closure-toggle').data('locked', !!existing);
                    $open.find('.js-day-closure-toggle').data('locked', !!existing);
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
