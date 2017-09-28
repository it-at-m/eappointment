/* global window */
import BaseView from "../../lib/baseview"
import $ from "jquery"
import { lightbox } from '../../lib/utils'
import ActionHandler from "../appointment/action"
import MessageHandler from '../../lib/messageHandler';
import DialogHandler from '../../lib/dialogHandler';

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.ActionHandler = new ActionHandler(element, options);
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
            const { lightboxContentElement, destroyLightbox } = lightbox(this.$main, () => {callback()});
            new MessageHandler(lightboxContentElement, {
                message: response,
                callback: (ActionHandler, buttonUrl, ev) => {
                    if (ActionHandler) {
                        let promise = this.ActionHandler[ActionHandler](ev);
                        if (promise instanceof Promise) {
                            promise
                                .then((response) => {this.loadMessage(response, callback)})
                                .catch(err => this.loadErrorCallback(err))
                        } else {
                            callback();
                        }
                    } else if (buttonUrl) {
                        this.loadByCallbackUrl(buttonUrl);
                        callback();
                    }
                    destroyLightbox();
                }
            })
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
            this.ActionHandler.reset(ev).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadMessage(response, this.onDeleteProcess);
            });
        }).on('click', 'a.process-delete', (ev) => {
            const id  = $(ev.target).data('id')
            const name  = $(ev.target).data('name')
            var confirmDelete = this.loadCall(`${this.includeUrl}/dialog/?template=confirm_delete&parameter[id]=${id}&parameter[name]=${name}`);
            confirmDelete.catch(err => this.loadErrorCallback(err)).then((response) => {
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
            const id  = $(ev.target).data('process')
            var confirmNotificationReminder = this.loadCall(`${this.includeUrl}/dialog/?template=confirm_notification_reminder&parameter[id]=${id}`);
            confirmNotificationReminder.catch(err => this.loadErrorCallback(err)).then((response) => {
                this.showSpinner();
                this.loadMessage(response, this.load);
            });
        }).on('click', '.process-custom-mail-send', (ev) => {
            const url = `${this.includeUrl}/mail/`;
            this.ActionHandler.sendMail(ev, url).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadDialog(response, this.load);
            });
        }).on('click', '.process-custom-notification-send', (ev) => {
            const url = `${this.includeUrl}/notification/`;
            this.ActionHandler.sendNotification(ev, url).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadDialog(response, this.load);
            });
        })
    }
}

export default View;
