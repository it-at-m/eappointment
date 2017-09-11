import BaseView from '../../../lib/baseview'
import $ from 'jquery'

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.includeUrl = options.includeUrl || "";
        this.exclude = "";
        this.processId = options.calledProcess || 0;
        this.refreshCounter = null;
        this.refreshCurrentTime = null;
        this.onNextProcess = options.onNextProcess || (() => {});
        this.bindPublicMethods('bindEvents','loadClientNext','setTimeSinceCall', 'loadCalled', 'loadProcessing');
        $.ajaxSetup({ cache: false });
        this.bindEvents();
        console.log('Component: Client', this, options);
        if (this.processId)
            this.loadCall();
        else
            this.load();
    }

    load() {
        console.log('init load')
        this.cleanInstance();
        const url = `${this.includeUrl}/workstation/process/callbutton/`
        return this.loadContent(url).then(this.setTimeSinceCall).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    loadClientNext() {
        this.cleanInstance();
        const url = `${this.includeUrl}/workstation/process/next/?exclude=` + this.exclude
        return this.loadContent(url).then(this.setTimeSinceCall).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    loadCall() {
        console.log('load call')
        this.cleanInstance();
        const url = `${this.includeUrl}/workstation/call/${this.processId}/?direct=1`
        return this.loadContent(url).then(this.setTimeSinceCall).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    loadCalled() {
        this.cleanInstance();
        const url = `${this.includeUrl}/workstation/process/${this.processId}/called/`
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
        const url = `${this.includeUrl}/workstation/process/cancel/next/`
        return this.loadContent(url).then(this.setTimeSinceCall).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    loadProcessing() {
        this.cleanInstance();
        const url = `${this.includeUrl}/workstation/process/processing/`
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
            this.processId = $(ev.target).data('processid');
            this.loadCalled();
        }).on('click', '.client-precall_button-skip', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.exclude = $(ev.target).data('exclude');
            this.loadClientNext();
            this.onNextProcess();
        }).on('click', '.client-called_button-skip', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.exclude = $(ev.target).data('exclude');
            this.loadCancelClientNext();
            this.onNextProcess();
        }).on('click', '.client-called_button-success', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.loadProcessing();
        }).on('click', '.client-called_button-abort, .client-precall_button-abort, .button-cancel', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.exclude = '';
            this.loadCancel().then(() => this.onNextProcess());
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

    setTimeSinceCall(lastsecond, lastminute) {
        let localTime = new Date() / 1000;
        let diffServer = Math.floor(new Date($("#clock").data('now')) - $("#clock").data('calltime'));
        let localCallTime = localTime - diffServer;
        this.setNextSinceCallTick(localCallTime);
    }

    setNextSinceCallTick (localCallTime) {
        let diff = Math.floor((new Date() / 1000) - localCallTime);
        let minute = (diff >= 60) ? Math.floor(diff/60) : 0;
        let second = diff % 60;
        let temp = '';
        second++;
        if (second == 60) {
            second = 0;
            minute++;
        }
        temp+=((minute < 10)? "0" : "")+minute + ":" + ((second < 10)? "0" : "")+second;

        $("#clock").text(temp);
        clearTimeout(this.refreshCounter);
        this.refreshCounter = setTimeout(() => {
            this.setNextSinceCallTick(localCallTime)
        }, 1000);
    }

    cleanInstance() {
        clearTimeout(this.refreshCounter);
        clearTimeout(this.refreshCurrentTime);
        this.setCurrentTime();
        this.calledProcess = 0;
    }

    setCurrentTime () {
        var time=new Date();
        var hour=time.getHours();
        var minute=time.getMinutes();
        var second=time.getSeconds();
        var temp=hour;
        if (second%2) temp+=((minute<10)? ":0" : ":")+minute;
        else temp+=((minute<10)? " 0" : " ")+minute;
        $('.currentTime').text(temp);
        this.refreshCurrentTime = setTimeout(() => {
            this.setCurrentTime()
        }, 1000);
    }
}

export default View
