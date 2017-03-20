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
        const url = `${this.includeUrl}/workstation/process/callbutton/`
        return this.loadContent(url)
    }

    loadClientNext() {
        const url = `${this.includeUrl}/workstation/process/next/?exclude=` + this.exclude
        return this.loadContent(url).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    loadPreCall() {
        const url = `${this.includeUrl}/workstation/process/${this.processId}/precall/`
        return this.loadContent(url).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    loadCalled() {
        const url = `${this.includeUrl}/workstation/process/${this.processId}}/called/`
        return this.loadContent(url).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    loadCancel() {
        const url = `${this.includeUrl}/workstation/process/cancel/`
        return this.loadContent(url).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    loadProcessing() {
        const url = `${this.includeUrl}/workstation/process/${this.processId}/processing/`
        return this.loadContent(url).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    loadProcessed() {
        const url = `${this.includeUrl}/workstation/process/${this.processId}/finished/`
        return this.loadContent(url).catch(err => this.loadErrorCallback(err.source, err.url));
    }

    bindEvents() {
        this.$main.on('click', '.button-callnextclient a', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.loadClientNext();
            this.setTimeSinceCall()
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
            this.loadCancel();
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

    setTimeSinceCall() {
        clearInterval(counter);
        let second = 0;
        let minute = 0;
        var counter = setInterval(() => {
            let temp = '';
            second++;
            if (second == 60) {
                second = 0;
                minute++;
            }
            temp+=((minute < 10)? "0" : "")+minute + ":" + ((second < 10)? "0" : "")+second;
            $("#clock").text(temp);
        }, 1000);
    }

    setExcludeIds(ids) {
        this.exclude = ids;
    }
}

export default View
