import BaseView from "../../lib/baseview"
import $ from "jquery"
import FreeProcessList from './free-process-list'
import FormButtons from './form-buttons'
import { lightbox } from '../../lib/utils'
import CalendarView from '../calendar'
import FormValidationView from '../form-validation'
import ExceptionHandler from '../../lib/exceptionHandler'
import MessageHandler from '../../lib/messageHandler'
import ActionHandler from "./action"
import RequestList from "./requests"
import maxChars from '../../element/form/maxChars'

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.$main = $(element);
        this.selectedDate = options.selectedDate;
        this.selectedTime = options.selectedTime;
        this.includeUrl = options.includeUrl || "";
        this.selectedProcess = options.selectedProcess;
        this.onDeleteProcess = options.onDeleteProcess || (() => {});
        this.onSaveProcess = options.onSaveProcess || (() => {});
        this.onEditProcess = options.onEditProcess || (() => {});
        this.onQueueProcess = options.onQueueProcess || (() => {});
        this.onDatePick = options.onDatePick || (() => {});
        this.FreeProcessList = new FreeProcessList(this.$main.find('[data-free-process-list]'), options);
        this.FormButtons = new FormButtons(this.$main.find('[data-form-buttons]'), options);
        this.ActionHandler = new ActionHandler(element, options);
        this.RequestList = new RequestList(element, options);
        $.ajaxSetup({ cache: false });
        this.load().then(() => {
            this.bindEvents();
            $('textarea.maxchars').each(function() {
                maxChars(this);
            });
        });
    }

    load() {
        const url = `${this.includeUrl}/appointmentForm/?selecteddate=${this.selectedDate}&selectedprocess=${this.selectedProcess}`
        this.loadPromise = this.loadContent(url).then(() => {
            this.RequestList.loadList();
            this.FormButtons.load();
        }).catch(err => this.loadErrorCallback(err));
        return this.loadPromise;
    }

    loadByCallbackUrl(url) {
        this.loadPromise = this.loadContent(url).catch(err => this.loadErrorCallback(err));
        return this.loadPromise;
    }

    loadNew () {
        const url = `${this.includeUrl}/appointmentForm/?selectedprocess=${this.selectedProcess}&new=1&selecteddate=${this.selectedDate}`
        this.loadPromise = this.loadContent(url).then(() => {
            this.RequestList.loadList();
            this.FreeProcessList.loadList();
            this.FormButtons.load();
            this.bindEvents();
        }).catch(err => this.loadErrorCallback(err));
        return this.loadPromise;
    }

    cleanReload () {
        this.selectedProcess = null;
        this.load().then(() => {
            this.bindEvents();
        });
    }

    bindEvents() {
        this.$main.off().on('change', '.checkboxselect input:checkbox', (event) => {
            this.RequestList.addServiceToList($(event.target), this.RequestList.serviceListSelected);
            this.RequestList.removeServiceFromList($(event.target), this.RequestList.serviceList);
            this.RequestList.updateLists();
            this.FreeProcessList.loadList();
        }).on('change', '.checkboxdeselect input:checkbox', (event) => {
            this.RequestList.removeServiceFromList($(event.target), this.RequestList.serviceListSelected);
            this.RequestList.addServiceToList($(event.target), this.RequestList.serviceList);
            this.RequestList.updateLists();
            this.FreeProcessList.loadList();
        }).on('click', '.clear-list', () => {
            this.RequestList.cleanLists();
            this.RequestList.updateLists();
            this.FreeProcessList.loadList();
        }).on('click', '.add-date-picker', () => {
            this.ActionHandler.selectDateWithOverlay();
        }).on('change', 'select#appointmentForm_slotCount', (ev) => {
            console.log('slots changed manualy');
            this.RequestList.slotCount = this.$main.find('select#appointmentForm_slotCount').val();
            this.FreeProcessList.loadList();
        }).on('click', '.form-actions button.process-reserve', (ev) => {
            event.preventDefault();
            event.stopPropagation();
            this.ActionHandler.reserve(ev).then((response) => {
                let selectedProcess = $(response).filter('[data-process]').data('process');
                this.loadMessage(response, () => {
                    this.onSaveProcess(selectedProcess)
                });
            }).catch(err => this.loadErrorCallback(err));
        }).on('click', '.form-actions button.process-queue', (ev) => {
            event.preventDefault();
            event.stopPropagation();
            this.ActionHandler.queue(ev).then((response) => {
                this.loadMessage(response, this.onQueueProcess);
            }).catch(err => this.loadErrorCallback(err));
        }).on('click', '.form-actions button.process-new', (ev) => {
            event.preventDefault();
            event.stopPropagation();
            this.loadNew();
        }).on('click', '.form-actions button.process-edit', (ev) => {
            this.onEditProcess($(ev.target).data('id'))
        }).on('click', '.form-actions button.process-delete', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.ActionHandler.delete(ev).then((response) => {
                this.loadMessage(response, this.onDeleteProcess);
            }).catch(err => this.loadErrorCallback(err));
        }).on('click', '.form-actions button.process-abort', (ev) => {
            this.ActionHandler.abort(ev);
            this.cleanReload();
        }).on('click', '.form-actions button.process-save', (ev) => {
            event.preventDefault();
            event.stopPropagation();
            this.ActionHandler.save(ev).then((response) => {
                this.loadMessage(response, this.onSaveProcess);
            }).catch(err => this.loadErrorCallback(err));
        }).on('click', '[data-button-print]', (ev) => {
            ev.preventDefault()
            ev.stopPropagation()
            this.ActionHandler.printWaitingNumber();
        })
    }

    loadMessage (response, callback) {
        this.$main.find('.form-actions').hide();
        const { lightboxContentElement, destroyLightbox } = lightbox(this.$main, () => {callback()})
        new MessageHandler(lightboxContentElement, {
            message: response,
            callback: (ActionHandler, buttonUrl) => {
                if (ActionHandler) {
                    this.ActionHandler[ActionHandler]()
                } else if (buttonUrl) {
                    this.loadByCallbackUrl(buttonUrl);
                }
                callback();
                destroyLightbox();
                this.cleanReload();
            }})
    }

    loadErrorCallback(err) {
        if (err.status == 428)
            new FormValidationView(this.$main.find('.appointment-form form'), {
                responseJson: err.responseJSON
            });
        else if (err.message.toLowerCase().includes('exception')) {
            let exceptionType = $(err.message).filter('.exception').data('exception');
            if (exceptionType === 'reservation-failed') {
                this.FreeProcessList.loadList();
                this.FormButtons.load();
            }
            else if (exceptionType === 'process-not-found')
                this.cleanReload()
            else {
                this.load();
                console.log('EXCEPTION thrown: ' + exceptionType);
            }
        }
        else
            console.log('Ajax error', err);
    }
}

export default View;
