import BaseView from '../../../lib/baseview'
import $ from 'jquery'

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.includeUrl = options.includeUrl || "";
        this.exclude = "";
        this.processId = options.processId;
        this.bindPublicMethods('loadClientNext');
        $.ajaxSetup({ cache: false });
        this.bindEvents();
        console.log('Component: Client', this, options);
        this.load();
    }

    load() {
        const url = `${this.includeUrl}/workstation/process/cancel/`
        return this.loadContent(url)
    }

    loadErrorCallback(source, url) {
        if (source == 'button') {
            return this.loadContent(url)
        } else {
            const defaultUrl = `${this.includeUrl}/workstation/process/cancel/`
            return this.loadContent(defaultUrl)
        }
    }

    loadClientNext() {
        const url = `${this.includeUrl}/workstation/process/next/?exclude=` + this.exclude
        return this.loadContent(url).catch(err => this.loadErrorCallback(err.soure, err.url));
    }

    loadPreCall() {
        const url = `${this.includeUrl}/workstation/process/${this.processId}/precall/`
        return this.loadContent(url)
    }

    loadCalled() {
        const url = `${this.includeUrl}/workstation/process/${this.processId}}/called/`
        return this.loadContent(url)
    }

    loadProcessing() {
        const url = `${this.includeUrl}/workstation/process/${this.processId}/processing/`
        return this.loadContent(url)
    }

    loadProcessed() {
        const url = `${this.includeUrl}/workstation/process/${this.processId}/finished/`
        return this.loadContent(url)
    }

    bindEvents() {
        this.$main.on('click', '.button-callnextclient a', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.loadClientNext();
        }).on('click', '.client-precall_button-success', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.loadCalled();
        }).on('click', '.client-precall_button-skip, .client-called_button-skip', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.setExcludeIds($(ev.target).data('exclude'));
            this.loadClientNext();
        }).on('click', '.client-called_button-success', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.loadProcessing();
        }).on('click', '.client-called_button-abort, .client-precall_button-abort, .button-cancel', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.setExcludeIds('');
            this.load();
        })
    }

    setExcludeIds(ids) {
        this.exclude = ids;
    }
}

export default View
