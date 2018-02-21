/* global window */
import Workstation from '../workstation'
import settings from '../../settings'
import AppointmentTimesView from '../../block/appointment/times'
import QueueInfoView from '../../block/queue/info'

class View extends Workstation {

    constructor (element, options) {
        super(element, options);
        this.bindPublicMethods(
            'onGhostWorkstationChange'
        );
        //console.log('Component: Counter', this, options);
    }

    bindEvents() {
        window.onfocus = () => {
            //console.log("on Focus");
            if (this.lastReload > settings.reloadInterval) {
                this.loadReloadPartials();
                this.lastReload = 0;
            }
            this.setReloadTimer();
        }
        window.onblur = () => {
            //console.log("lost Focus");
            clearTimeout(this.reloadTimer);
        }
        this.$main.find('[data-queue-table], [data-queue-info]').mouseenter(() => {
            //console.log("stop Reload on mouse enter");
            clearTimeout(this.reloadTimer);
        });
        this.$main.find('[data-queue-table], [data-queue-info]').mouseleave(() => {
            //console.log("start reload on mouse leave");
            this.setReloadTimer();
        });
    }

    onGhostWorkstationChange($container, event) {
        let ghostWorkstationCount = "-1";
        if (event.target.value > -1)
            ghostWorkstationCount = event.target.value;
        this.loadContent(`${this.includeUrl}/counter/queueInfo/?ghostworkstationcount=${ghostWorkstationCount}`, null, null, $container).then(() => {
            this.loadAllPartials();
        });
    }

    loadAllPartials() {
        return Promise.all([
            this.loadCalendar(),
            this.loadQueueTable(),
            this.loadAppointmentForm(),
            this.loadQueueInfo(),
            this.loadAppointmentTimes()
        ])
    }

    loadAppointmentTimes (showLoader = true) {
        return new AppointmentTimesView(this.$main.find('[data-appointment-times]'), {
            selectedDate: this.selectedDate,
            includeUrl: this.includeUrl,
            showLoader: showLoader
        })
    }

    loadQueueInfo (showLoader = true) {
        return new QueueInfoView(this.$main.find('[data-queue-info]'), {
            selectedDate: this.selectedDate,
            includeUrl: this.includeUrl,
            onGhostWorkstationChange: this.onGhostWorkstationChange,
            showLoader: showLoader
        })
    }

}

export default View;
