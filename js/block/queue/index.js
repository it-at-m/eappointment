import BaseView from "../../lib/baseview"
import $ from "jquery"
import { lightbox } from '../../lib/utils'
import ButtonActionHandler from "../appointment/action"
import ProcessNext from "../process/next"
import MessageHandler from '../../lib/messageHandler';
import DialogHandler from '../../lib/dialogHandler';

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.ButtonAction = new ButtonActionHandler(element, options);
        this.source = options.source;
        this.selectedDate = options.selectedDate;
        this.includeUrl = options.includeUrl || "";
        this.showLoader = options.showLoader || false;
        this.onDatePick = options.onDatePick || (() => {});
        this.onDateToday = options.onDateToday || (() => {});
        this.onDeleteProcess = options.onDeleteProcess || (() => {});
        this.onEditProcess = options.onEditProcess || (() => {});
        this.onNextProcess = options.onNextProcess || (() => {});
        this.bindPublicMethods('load');
        $.ajaxSetup({ cache: false });
        this.bindEvents();
        console.log('Component: Queue', this, options);
        this.load();
    }

    load() {
        const url = `${this.includeUrl}/queueTable/?selecteddate=${this.selectedDate}`
        return this.loadContent(url, 'GET', null, null, this.showLoader).catch(err => this.loadErrorCallback(err));
    }

    cleanReload () {
        this.load().then(() => {
            this.bindEvents();
        });
    }

    loadErrorCallback(err) {
        if (err.message) {
            let exceptionType = $(err.message).find('.exception').data('exception');
            if (exceptionType === 'process-not-found')
                this.cleanReload()
            else {
                this.load();
                console.log('EXCEPTION thrown: ' + exceptionType);
            }
        }
        else
            console.log('Ajax error', err);
    }

    loadMessage (response, callback) {
        if (response) {
            const { lightboxContentElement, destroyLightbox } = lightbox(this.$main, () => {callback()})
            new MessageHandler(lightboxContentElement, {
                message: response,
                callback: (buttonAction, buttonUrl) => {
                    if (buttonAction)
                        this.ButtonAction[buttonAction]()
                    else if (buttonUrl)
                        this.loadByCallbackUrl(buttonUrl)
                    destroyLightbox()
                    this.cleanReload();
                }})
        }
    }

    loadDialog (response, callback) {
        const { lightboxContentElement, destroyLightbox } = lightbox(this.$main, () => {callback()})
        new DialogHandler(lightboxContentElement, {
            response: response,
            callback: (message) => {
                if (message) {
                    if ($(message).find('.dialog form').length > 0) {
                        this.loadDialog(message, callback);
                    }
                    else {
                        this.loadMessage(message, callback);
                    }
                }
                destroyLightbox();
            }
        })
    }

    loadByCallbackUrl(url) {
        this.loadPromise = this.loadContent(url).catch(err => this.loadErrorCallback(err));
        return this.loadPromise;
    }

    bindEvents() {
        this.$main.off('click').on('click', '.queue-table .reload', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.load();
        }).on('change', '.queue-table .switchcluster select', (ev) => {
            let loc = window.location;
            let pathName = loc.pathname.substring(0, loc.pathname.lastIndexOf('/')).split('/').pop();
            $('.sourceSwitchCluster').val(pathName);
            $(ev.target).closest('form').submit();
        }).on('change', '.queue-table .appointmentsOnly input', (ev) => {
            let loc = window.location;
            let pathName = loc.pathname.substring(0, loc.pathname.lastIndexOf('/')).split('/').pop();
            $('.sourceAppointmentsOnly').val(pathName);
            $(ev.target).closest('form').submit();
        }).on('click', 'a.process-edit', (ev) => {
            this.onEditProcess($(ev.target).data('id'))
        }).on('click', 'a.process-reset', (ev) => {
            this.ButtonAction.reset(ev).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadMessage(response, this.onDeleteProcess);
            });
        }).on('click', 'a.process-delete', (ev) => {
            this.ButtonAction.delete(ev).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadMessage(response, this.onDeleteProcess);
            });
        }).on('click', '.queue-table .calendar-navigation .pagedaylink', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            const selectedDate = $(ev.target).attr('data-date');
            console.log('next or prev day selected', selectedDate)
            this.onDatePick(selectedDate, this);
        }).on('click', '.queue-table .calendar-navigation .today', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            const selectedDate = $(ev.target).attr('data-date');
            console.log('today selected', selectedDate)
            this.onDateToday(selectedDate, this)
        }).on('click', '.queue-table .process-notification-send', (ev) => {
            this.ButtonAction.sendNotificationReminder(ev).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadMessage(response, this.load);
            });
        }).on('click', '.process-custom-mail-send', (ev) => {
            const url = `${this.includeUrl}/mail/`;
            this.ButtonAction.sendMail(ev, url).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadDialog(response, this.load);
            });
        }).on('click', '.process-custom-notification-send', (ev) => {
            const url = `${this.includeUrl}/notification/`;
            this.ButtonAction.sendNotification(ev, url).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadDialog(response, this.load);
            });
        })
    }
}

export default View;
