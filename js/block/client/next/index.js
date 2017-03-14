import BaseView from '../../../lib/baseview'
import $ from 'jquery'

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.includeUrl = options.includeUrl || "";
        this.bindPublicMethods('load');
        $.ajaxSetup({ cache: false });
        this.bindEvents();
        console.log('Component: ClientNext', this, options);
        this.load();
    }

    load() {
        const url = `${this.includeUrl}/clientNext/`
        this.loadPromise = this.loadContent(url)
        return this.loadPromise;
    }

    bindEvents() {
        this.$main.on('click', '.body a', (ev) => {
            console.log(ev)
        })
    }
}

export default View
