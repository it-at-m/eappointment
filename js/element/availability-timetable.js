
import BaseView from '../lib/baseview';
import $ from "jquery";
import settings from '../settings';

class View extends BaseView {

    constructor (element) {
        super(element);
        this.bindPublicMethods('itemClick');
        console.log("Found timetable");
        this.$.find('.item-bar').on('click', this.itemClick);
        this.clickAnimationRunning = false;
    }

    itemClick (event) {
        let $link = $(event.target).closest('.item-bar');
        let animationName = 'spaceOutLeft';
        let $form = $('.availability-form');
        let $clonedForm = $('.availability-form').clone();

        if (!$link.hasClass('selected') && !this.clickAnimationRunning) {
            this.$.find('.item-bar').removeClass('selected');
            $link.addClass('selected');
            $clonedForm.css({
                'position': 'absolute',
                'z-index': 2,
            });
            $form.before($clonedForm);
            // TODO Change form data here
            try {
                this.changeformData(JSON.parse($link.attr('data-availability')));
            } catch (exception) {
                console.log("Fehlerhafte Daten: %o", $link.attr('data-availability'));
                $clonedForm.remove();
            }
            $clonedForm.addClass('magictime ' + animationName);
            this.clickAnimationRunning = true;
            //$('body').stop().animate({scrollTop: $form.offset().top}, '1500', 'swing', function() { 
            //    console.log("Finished scrolling to %o", $form.offset().top);
            //});
            $clonedForm.one(settings.animationEnd, () => {
                this.clickAnimationRunning = false;
                $clonedForm.remove();
            });
        }
        return false;
    }

    changeformData(data) {
        console.log("Change form %o", data);
        let $form = $('.availability-form');
        $form.find('input[name=description]').val(data.description);
        $form.find('input[name=slotTimeInMinutes]').val(data.slotTimeInMinutes);
        $form.find('input[name=time_start_hour]').val(data.startTime.replace(/:\d+$/, ''));
        $form.find('input[name=time_start_min]').val(data.startTime.replace(/^\d+:/, ''));
        $form.find('input[name=time_end_hour]').val(data.endTime.replace(/:\d+$/, ''));
        $form.find('input[name=time_end_min]').val(data.endTime.replace(/^\d+:/, ''));
        $form.find('select[name=type]').val(data.type);
        $form.find('select[name=repeat]').val('-1');
    }
}

export default View;
