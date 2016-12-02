
import BaseView from '../lib/baseview';
import $ from "jquery";
import settings from '../settings';

class View extends BaseView {

    constructor (element) {
        super(element);
        this.bindPublicMethods('itemClick');
        console.log("Found timetable");
        this.$.find('a.item-bar').on('click', this.itemClick);
        this.clickAnimationRunning = false;
    }

    itemClick (event) {
        let $link = $(event.target).closest('a.item-bar');
        let animationName = 'spaceInUp';
        let $form = $('.availability-form');
        let $clonedForm = $('.availability-form').clone();

        if (!$link.hasClass('selected') && !this.clickAnimationRunning) {
            this.$.find('a.item-bar').removeClass('selected');
            $link.addClass('selected');
            $clonedForm.css({
                'position': 'absolute',
                'z-index': 2,
            });
            $form.before($clonedForm);
            $clonedForm.show();
            $clonedForm.addClass('magictime ' + animationName);
            this.clickAnimationRunning = true;
            //$('body').stop().animate({scrollTop: $form.offset().top}, '1500', 'swing', function() { 
            //    console.log("Finished scrolling to %o", $form.offset().top);
            //});
            $clonedForm.one(settings.animationEnd, () => {
                this.clickAnimationRunning = false;
                try {
                    this.changeformData(JSON.parse($link.attr('data-availability')));
                    $form.show();
                } catch (exception) {
                    console.log("Fehlerhafte Daten: %o", $link.attr('data-availability'));
                    $clonedForm.remove();
                }
                $clonedForm.remove();
            });
        }
        return false;
    }

    changeformData(data) {
        let $form = $('.availability-form');
        $form.find('input[name=description]').val(data.description);
        $form.find('input[name=slotTimeInMinutes]').val(data.slotTimeInMinutes);
        $form.find('input[name=time_start_hour]').val(data.startTime.replace(/:\d+$/, ''));
        $form.find('input[name=time_start_min]').val(data.startTime.replace(/^\d+:/, ''));
        $form.find('input[name=time_end_hour]').val(data.endTime.replace(/:\d+$/, ''));
        $form.find('input[name=time_end_min]').val(data.endTime.replace(/^\d+:/, ''));
        $form.find('select[name=type]').val(data.type);
        $form.find('select[name=repeat]').val('-1');
        $form.find('input[name=workstationCount_intern]').val(data.workstationCount.intern);
        $form.find('select[name=workstationCount_callcenter]').val(data.workstationCount.callcenter);
        $form.find('select[name=workstationCount_public]').val(data.workstationCount.public);
    }
}

export default View;
