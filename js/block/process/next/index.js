import BaseView from '../../../lib/baseview'
import $ from 'jquery'

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.includeUrl = options.includeUrl || "";
        this.exclude = "";
        this.processId = options.processId;
        this.refreshCounter = null;
        this.onNextProcess = options.onNextProcess || (() => {});
        this.bindPublicMethods('loadClientNext','setTimeSinceCall');
        $.ajaxSetup({ cache: false });
        this.bindEvents();
        console.log('Component: Client', this, options);
        this.load();
        var me = this;
    }

    load() {
        this.cleanInstance();
        const url = `${this.includeUrl}/workstation/process/callbutton/`
        return this.loadContent(url)
    }

    loadClientNext() {
        this.cleanInstance();
        this.setTimeSinceCall();
        const url = `${this.includeUrl}/workstation/process/next/?exclude=` + this.exclude
        return this.loadContent(url).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    loadPreCall() {
        this.cleanInstance();
        const url = `${this.includeUrl}/workstation/process/${this.processId}/precall/`
        return this.loadContent(url).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    loadCalled() {
        this.cleanInstance();
        const url = `${this.includeUrl}/workstation/process/${this.processId}}/called/`
        return this.loadContent(url).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    loadCancel() {
        this.cleanInstance();
        const url = `${this.includeUrl}/workstation/process/cancel/`
        return this.loadContent(url).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    // if process is called and button "nein, nächster Kunde bitte" is clicked, delete process from workstation and call next
    loadCancelClientNext() {
        this.cleanInstance();
        this.setTimeSinceCall();
        const url = `${this.includeUrl}/workstation/process/cancel/next/`
        return this.loadContent(url).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    loadProcessing() {
        this.cleanInstance();
        const url = `${this.includeUrl}/workstation/process/${this.processId}/processing/`
        return this.loadContent(url).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    loadProcessed() {
        this.cleanInstance();
        const url = `${this.includeUrl}/workstation/process/${this.processId}/finished/`
        return this.loadContent(url).catch(err => this.loadErrorCallback(err.source, err.url));
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
        }).on('click', '.client-precall_button-skip', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.setExcludeIds($(ev.target).data('exclude'));
            this.loadClientNext();
            this.onNextProcess();
        }).on('click', '.client-called_button-skip', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.setExcludeIds($(ev.target).data('exclude'));
            this.loadCancelClientNext();
            this.onNextProcess();
        }).on('click', '.client-called_button-success', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.loadProcessing();
        }).on('click', '.client-called_button-abort, .client-precall_button-abort, .button-cancel', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.setExcludeIds('');
            this.loadCancel();
            this.onNextProcess();
        })
    }

    loadErrorCallback(source, url) {
        if (source == 'button') {
            return this.loadContent(url)
        } else {
            const defaultUrl = `${this.includeUrl}/workstation/process/cancel/`
            return this.loadContent(defaultUrl)
        }
    }

    setTimeSinceCall(lastsecond, lastminute) {
        this.cleanInstance();
        let second = (lastsecond) ? lastsecond : 0;
        let minute = (lastminute) ? lastminute : 0;
        let temp = '';
        second++;
        if (second == 60) {
            second = 0;
            minute++;
        }
        temp+=((minute < 10)? "0" : "")+minute + ":" + ((second < 10)? "0" : "")+second;
        $("#clock").text(temp);
        this.refreshCounter = setTimeout(() => {
            this.setTimeSinceCall(second, minute)
        }, 1000);
    }

    cleanInstance() {
        clearTimeout(this.refreshCounter);
    }

    setExcludeIds(ids) {
        this.exclude = ids;
    }
}

export default View
