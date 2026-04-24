import $ from 'jquery';
import { lightbox, noOp } from './utils';

function escapeHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(String(str)));
    return div.innerHTML;
}

/**
 * Delegated clicks on .js-readmore-open: show full text in centered lightbox.
 */
export default function initReadMoreLightbox() {
    if (initReadMoreLightbox.bound) {
        return;
    }
    initReadMoreLightbox.bound = true;

    document.body.addEventListener('click', (ev) => {
        const btn = ev.target.closest('.js-readmore-open');
        if (!btn) {
            return;
        }
        const row = btn.closest('tr');
        if (
            row &&
            (row.classList.contains('reserved') ||
                row.classList.contains('preconfirmed') ||
                row.classList.contains('deleted'))
        ) {
            return;
        }
        ev.preventDefault();
        let payload;
        try {
            payload = JSON.parse(btn.getAttribute('data-readmore-json') || '{}');
        } catch (e) {
            return;
        }
        const text = payload.text == null ? '' : String(payload.text);
        const metaRaw = payload.meta == null ? '' : String(payload.meta);
        const meta = metaRaw.trim();
        const rawTitle = btn.getAttribute('data-readmore-title');
        const title = rawTitle != null ? String(rawTitle).trim() : '';
        const rawCloseLabel = btn.getAttribute('data-readmore-close-label');
        const closeLabel =
            rawCloseLabel != null && String(rawCloseLabel).trim() !== ''
                ? String(rawCloseLabel).trim()
                : 'Schließen';
        const { lightboxContentElement, destroyLightbox } = lightbox($('body'), noOp);
        lightboxContentElement.closest('.lightbox').addClass('lightbox--readmore');
        const safeTitle = escapeHtml(title);
        const safeBody = escapeHtml(text);
        const safeMeta = meta
            ? '<div class="readmore-lightbox__meta">' + escapeHtml(meta).replace(/\n/g, '<br>') + '</div>'
            : '';
        const headerBlock = title
            ? '<div class="header board__header"><h2 class="board__heading">' + safeTitle + '</h2></div>'
            : '';
        lightboxContentElement.html(
            '<section tabindex="0" class="board dialog readmore-lightbox" role="document">' +
            headerBlock +
            '<div class="body board__body readmore-lightbox__body">' +
            '<pre class="readmore-lightbox__pre">' + safeBody + '</pre>' +
            safeMeta +
            '<footer class="readmore-lightbox__footer">' +
            '<button type="button" class="button button--default readmore-lightbox__close">' +
                escapeHtml(closeLabel) +
            '</button>' +
            '</footer></div></section>'
        );
        lightboxContentElement.find('.readmore-lightbox__close').on('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            destroyLightbox();
        });
    });
}

initReadMoreLightbox.bound = false;
