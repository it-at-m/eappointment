import BaseView from '../../../lib/baseview'
import $ from 'jquery'

window.refreshCounter = null;

class View extends BaseView {

    constructor(element, options) {
        super(element, options);
        this.element = element;
        this.options = options;
        this.includeUrl = options.includeUrl || "";
        this.showLoader = options.showLoader || false;
        this.exclude = "";
        this.processId = options.calledProcess || 0;
        this.refreshCurrentTime = null;
        this.onNextProcess = options.onNextProcess || (() => { });
        this.onCallNextProcess = options.onCallNextProcess || (() => { });
        this.onCancelForm = options.onCancelNextProcess || (() => { });
        this.onCalledProcess = options.onCalledProcess || (() => { });
        this.bindPublicMethods('cleanInstance', 'bindEvents', 'loadClientNext', 'setTimeSinceCall', 'loadCalled', 'loadProcessing');
        $.ajaxSetup({ cache: false });
        this.bindEvents();
        $(() => {
            this.setCurrentTime();
        });
        //console.log('Component: Client', this, options);
        if (this.processId)
            this.loadCall();
        else
            this.load();
    }

    load() {
        this.cleanInstance();
        const url = `${this.includeUrl}/workstation/process/callbutton/`
        return this.loadInto(url).then(this.setTimeSinceCall);
    }

    loadClientNext() {
        this.cleanInstance();
        const url = `${this.includeUrl}/workstation/process/next/?exclude=` + this.exclude
        return this.loadInto(url).then(() => this.onNextProcess()).then(this.setTimeSinceCall);
    }

    loadCall() {
        this.cleanInstance();
        const url = `${this.includeUrl}/workstation/call/${this.processId}/?direct=1`
        return this.loadInto(url).then(() => this.onNextProcess()).then(this.setTimeSinceCall);
    }

    loadCalled() {
        this.cleanInstance();
        const url = `${this.includeUrl}/workstation/process/${this.processId}/called/?exclude=` + this.exclude
        return this.loadInto(url).then(() => this.onNextProcess()).then(this.setTimeSinceCall);
    }

    loadCancel() {
        this.cleanInstance();
        const url = `${this.includeUrl}/workstation/process/cancel/`
        return this.loadInto(url).then(() => {
            this.onNextProcess();
            this.updateURL();
        });
    }

    loadParked() {
        this.cleanInstance();
        const url = `${this.includeUrl}/workstation/process/parked/`
        return this.loadInto(url).then(() => {
            this.onNextProcess();
            this.updateURL();
        });
    }

    // if process is called and button "nein, nächster Kunde bitte" is clicked, delete process from workstation and call next
    loadCancelClientNext() {
        this.cleanInstance();
        const url = `${this.includeUrl}/workstation/process/cancel/next/?exclude=` + this.exclude
        return this.loadInto(url).then(() => this.onNextProcess()).then(this.setTimeSinceCall);
    }

    loadProcessing() {
        this.cleanInstance();
        const url = `${this.includeUrl}/workstation/process/processing/`
        return this.loadInto(url).then(() => this.onNextProcess());
    }

    loadInto(url) {
        return this.loadContent(url, 'GET', null, null, this.showLoader).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    updateURL() {
        const url = new URL(window.location.href);
        url.searchParams.delete('calledprocess');
        window.history.replaceState({}, '', url);
    }

    bindEvents() {
        this.$main.on('click', '.button-callnextclient .button', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.exclude = $(ev.target).data('exclude') || '';
            this.loadClientNext();
        }).on('click', '.client-precall_button-success', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.processId = $(ev.target).data('processid');
            this.exclude = $(ev.target).data('exclude');
            this.loadCalled();
        }).on('click', '.client-precall_button-skip', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.exclude = $(ev.target).data('exclude');
            this.loadClientNext();
        }).on('click', '.client-called_button-skip', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.exclude = $(ev.target).data('exclude');
            this.loadCancelClientNext();
        }).on('click', '.client-called_button-success', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.loadProcessing();
        }).on('click', '.client-called_button-abort, .client-precall_button-abort, .button-cancel', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.exclude = '';
            this.loadCancel();
        }).on('click', '.client-called_button-parked, .client-precall_button-parked, .button-parked', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.exclude = '';
            this.loadParked();
        })
    }

    loadErrorCallback(source, url) {
        if (source == 'button') {
            return this.loadContent(url)
        } else if (source == 'lightbox') {
            const defaultUrl = `${this.includeUrl}/workstation/process/cancel/`
            return this.loadContent(defaultUrl)
        } else {
            const defaultUrl = `${this.includeUrl}/workstation/process/cancel/`
            return this.loadContent(defaultUrl)
        }
    }

    setTimeSinceCall() {
        let localTime = new Date() / 1000;
        let diffServer = Math.floor(new Date($("#clock").data('now')) - $("#clock").data('calltime'));
        let localCallTime = localTime - diffServer;
        this.setNextSinceCallTick(localCallTime);
    }

    setNextSinceCallTick(localCallTime) {
        let diff = Math.floor((new Date() / 1000) - localCallTime);
        let minute = (diff >= 60) ? Math.floor(diff / 60) : 0;
        let second = diff % 60;
        let temp = '';
        second++;
        if (second == 60) {
            second = 0;
            minute++;
        }
        temp += ((minute < 10) ? "0" : "") + minute + ":" + ((second < 10) ? "0" : "") + second;

        $("#clock").text(temp);
        clearTimeout(window.refreshCounter);
        window.refreshCounter = setTimeout(() => {
            this.setNextSinceCallTick(localCallTime)
        }, 1000);
    }

    cleanInstance() {
        clearTimeout(window.refreshCounter);
        clearTimeout(this.refreshCurrentTime);
        this.calledProcess = 0;
    }

    setCurrentTime() {
        var time = new Date();
        var hour = time.getHours();
        var minute = time.getMinutes();
        var temp = hour + ((minute < 10) ? ":0" : ":") + minute;
        $('.currentTime').text(temp + " Uhr");
        this.refreshCurrentTime = setTimeout(() => {
            this.setCurrentTime()
        }, 1000);
    }
}

export default View
