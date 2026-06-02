/*
 *  Collapse any element with the ".js-collapse-me" class:
 *  Use data-opentext and data-closetext attibutes to set button texts.
 */

export default function() { 

    $('.js-collapse-me').each(function() {
        var self = $(this);
        var openText = $(this).data('opentext') || 'Öffnen';
        var closeText = $(this).data('closetext') || 'Schließen';
        self.wrapInner('<span class="js-collapse-me-inner"></span> ');
        self.prepend('<span class="js-collapse-me-toggler" title="">'+openText+'</span>');
        self.show();
        $('.js-collapse-me-toggler', this).click(function() {
            self.toggleClass('opened');
            if (self.hasClass('opened')) {
                $('.js-collapse-me-toggler', self).html(closeText);
            } else {
                $('.js-collapse-me-toggler', self).html(openText);
            }
        });
    });

}
