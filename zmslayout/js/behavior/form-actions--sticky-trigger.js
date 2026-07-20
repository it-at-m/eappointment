export default function() { 

    $('.form-actions--sticky').each(function() {
        var self = $(this);
        var openText = $(this).data('opentext') || 'Öffnen';
        var closeText = $(this).data('closetext') || 'Schließen';
        $('.js-form-actions--sticky-trigger', this).click(function(e) {
            e.preventDefault();
            self.toggleClass('open');
            if (self.hasClass('open')) {
                $('.js-form-actions--sticky-trigger', self).attr('title',closeText);
            } else {
                $('.js-form-actions--sticky-trigger', self).attr('title',openText);
            }
        });
    });

}
