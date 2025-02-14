import Workstation from '../workstation'
import settings from '../../settings'

class View extends Workstation {

    constructor(element, options) {
        super(element, options);
        this.bindPublicMethods();
        this.page = 'counter';
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
    }

    loadAllPartials() {
        return Promise.all([
            this.loadCalendar(),
            this.loadQueueTable(),
            this.loadAppointmentForm(),
            this.loadQueueInfo(),
            this.loadAppointmentTimes(),
            this.loadHeaderScope()
        ])
    }
}

export default View;
