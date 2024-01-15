import $ from 'jquery'
import Baseview from './baseview';
import settings from '../settings';

const lightboxHtml = '<div class="lightbox"><div class="lightbox__content"></div></div>'

export const lightbox = (parentElement, onBackgroundClick) => {
    const lightboxElement = $(lightboxHtml)

    if (!parentElement) {
        parentElement = $('body')
        lightboxElement.addClass('fixed')
    }

    const destroyLightbox = () => {
        lightboxElement.off()
        lightboxElement.remove()
    }

    const lightboxContentElement = lightboxElement.find('.lightbox__content');

    lightboxElement.on('click', (ev) => {
        //console.log('background click', ev);
        ev.stopPropagation()
        ev.preventDefault()
        destroyLightbox()
        onBackgroundClick()
    }).on('click', '.lightbox__content', (ev) => {
        ev.stopPropagation();
    })


    if ($(parentElement).find('.lightbox').length) {
        $(parentElement).find('.lightbox').remove();
    }
    $(parentElement).append(lightboxElement)

    return {
        lightboxContentElement,
        destroyLightbox
    }
}

export const forceHttps = () => {
    if (document.location.protocol !== "https:") {
        Baseview.loadCallStatic(`${settings.includeUrl}/dialog/?template=force_https`).then((response) => {
            Baseview.loadDialogStatic(response, () => {
                document.location.href = "https://" + document.location.href.substring(document.location.protocol.length, document.location.href.length);
            });
        });
    }

}
