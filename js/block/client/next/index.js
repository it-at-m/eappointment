import BaseView from '../../../lib/baseview'
import $ from 'jquery'

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.includeUrl = options.includeUrl || "";
        this.selectedDate = options.selectedDate;
        this.bindPublicMethods('loadClientNext');
        $.ajaxSetup({ cache: false });
        this.bindEvents();
        console.log('Component: Client', this, options);
        this.loadClientNext();
    }

    loadClientNext() {
        const url = `${this.includeUrl}/clientNext/?selecteddate=${this.options.selectedDate}`
        return this.loadContent(url)
    }

    loadPreCall() {
        const url = `${this.includeUrl}/workstation/process/0/precall/`
        return this.loadContent(url)
    }

    loadCalled() {
        const url = `${this.includeUrl}/workstation/process/0/called/`
        return this.loadContent(url)
    }

    loadProcessing() {
        const url = `${this.includeUrl}/workstation/process/0/processing/`
        return this.loadContent(url)
    }

    loadProcessed() {
        const url = `${this.includeUrl}/workstation/process/0/finished/`
        return this.loadContent(url)
    }

    bindEvents() {
        this.$main.on('click', '.button-callnextclient a', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.loadPreCall();
        }).on('click', '.client-precall_button-success', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.loadCalled();
        }).on('click', '.client-precall_button-skip, .client-called_button-skip', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.loadClientNext();
        }).on('click', '.client-called_button-success', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.loadProcessing();
        }).on('click', '.client-called_button-abort, .client-precall_button-abort, .button-cancel', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.loadClientNext();
        })
    }
}

export default View
