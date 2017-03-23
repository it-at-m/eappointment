import BaseView from '../../../lib/baseview'
import $ from 'jquery'

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.includeUrl = options.includeUrl || "";
        this.exclude = "";
        this.processId = 0;
        this.refreshCounter = null;
        this.refreshCurrentTime = null;
        this.onNextProcess = options.onNextProcess || (() => {});
        this.bindPublicMethods('bindEvents','loadClientNext','setTimeSinceCall', 'loadCalled', 'loadProcessing');
        $.ajaxSetup({ cache: false });
        this.bindEvents();
        console.log('Component: Client', this, options);
        this.load();
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
        this.setTimeSinceCall();
        const url = `${this.includeUrl}/workstation/process/cancel/next/`
        return this.loadContent(url).catch(err => this.loadErrorCallback(err.source, err.url));
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
            this.loadCancel();
            this.onNextProcess();
        })
    }

    loadErrorCallback(source, url) {
        if (source == 'button') {
            return this.loadContent(url)
        } else if (source == 'lightbox') {
            console.log('lightbox closed without action call');
        } else {
            const defaultUrl = `${this.includeUrl}/workstation/process/cancel/`
            return this.loadContent(defaultUrl)
        }
    }

    setTimeSinceCall(lastsecond, lastminute) {
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
        clearTimeout(this.refreshCurrentTime);
        this.setCurrentTime();
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
