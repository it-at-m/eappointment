import BaseView from "../../lib/baseview"
import $ from "jquery"

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.selectedDate = options.selectedDate;
        this.includeUrl = options.includeUrl || "";
        this.showLoader = options.showLoader || false;
        this.ghostWorkstationCount = "-1";
        this.onGhostWorkstationChange = options.onGhostWorkstationChange || (() => {});
        this.bindPublicMethods('load');
        $.ajaxSetup({ cache: false });
        this.bindEvents();
        console.log('Component: Queue Info', this, options);
        this.load();
    }

    load() {
        const url = `${this.includeUrl}/counter/queueInfo/?selecteddate=${this.selectedDate}`
        return this.loadContent(url, 'GET', null, null, this.showLoader).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    loadErrorCallback(source, url) {
        if (source == 'button') {
            return this.loadContent(url)
        } else if (source == 'lightbox') {
            console.log('lightbox closed without action call');
        } else {
            const defaultUrl = `${this.includeUrl}/counter/`
            return this.loadContent(defaultUrl)
        }
    }

    bindEvents() {
        this.$main.off('click').on('change', 'select[name=count]', (ev)=> {
            this.ghostWorkstationCount = ev.target.value
            this.updateGhostWorkstationsCount()
        })
    }

    updateGhostWorkstationsCount () {
        const url = `${this.includeUrl}/counter/queueInfo/?ghostworkstationcount=${this.ghostWorkstationCount}`;
        $.ajax(url, {
            method: 'GET'
        }).success(() => {
            this.onGhostWorkstationChange()
        }).done(() => {
            console.log('changed ghostworkstationcount')
        }).fail(err => {
            console.log('ajax error', err);
        })
    }
}

export default View;
