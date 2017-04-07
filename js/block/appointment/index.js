import BaseView from "../../lib/baseview"
import $ from "jquery"
import freeProcessList from './free-process-list'
import { lightbox } from '../../lib/utils'
import CalendarView from '../calendar'
import FormValidationView from '../form-validation'
import ExceptionHandler from '../../lib/exceptionHandler'
import MessageHandler from '../../lib/messageHandler';
import ProcessActionHandler from "../process/action"
import RequestListAction from "./requests"

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.ProcessAction = new ProcessActionHandler(element, options);
        this.RequestListAction = new RequestListAction(element, options);
        this.$main = $(element);
        this.selectedDate = options.selectedDate;
        this.selectedTime = options.selectedTime;
        this.includeUrl = options.includeUrl || "";
        this.selectedProcess = options.selectedProcess;
        this.onDeleteProcess = options.onDeleteProcess || (() => {});
        this.onSaveProcess = options.onSaveProcess || (() => {});
        this.onEditProcess = options.onEditProcess || (() => {});
        this.slotType = 'intern';

        $.ajaxSetup({ cache: false });
        this.load().then(() => {
            if (this.selectedProcess)
                this.RequestListAction.readList()
            else
                this.RequestListAction.cleanLists();
            this.loadFreeProcessList();
            this.bindEvents();
        });
    }

    load() {
        const url = `${this.includeUrl}/appointmentForm/?selecteddate=${this.selectedDate}&selectedprocess=${this.selectedProcess}`
        this.loadPromise = this.loadContent(url).catch(err => this.loadErrorCallback(err));
        return this.loadPromise;
    }

    loadFreeProcessList () {
        return new freeProcessList(this.$main.find('[data-free-process-list]'), {
            selectedDate: this.selectedDate,
            selectedTime: this.selectedTime,
            includeUrl: this.includeUrl,
            slotsRequired: this.RequestListAction.slotCount,
            slotType: this.slotType
        })
    }

    cleanReload () {
        this.selectedProcess = null;
        this.load().then(() => {
            this.RequestListAction.cleanLists();
            this.loadFreeProcessList();
            this.bindEvents();
        });
    }

    bindEvents() {
        this.$main.off().on('change', '.checkboxselect input:checkbox', (event) => {
            this.RequestListAction.addServiceToList($(event.target), this.RequestListAction.serviceListSelected);
            this.RequestListAction.removeServiceFromList($(event.target), this.RequestListAction.serviceList);
            this.RequestListAction.updateLists();
            this.loadFreeProcessList();
        }).on('change', '.checkboxdeselect input:checkbox', (event) => {
            this.RequestListAction.removeServiceFromList($(event.target), this.RequestListAction.serviceListSelected);
            this.RequestListAction.addServiceToList($(event.target), this.RequestListAction.serviceList);
            this.RequestListAction.updateLists();
            this.loadFreeProcessList();
        }).on('click', '.clear-list', () => {
            this.RequestListAction.cleanLists();
            this.RequestListAction.updateLists();
            this.loadFreeProcessList();
        }).on('click', 'input[name=date]', () => {
            this.selectDateWithOverlay()
                   .then(date => {
                       this.selectedDate = date;
                       this.setSelectedDate();
                   })
                   .then(() => {
                       this.RequestListAction.calculateSlotCount();
                       this.loadFreeProcessList();
                   })
                   .catch(() => console.log('no date selected'));
        }).on('change', 'select#appointmentForm_slotCount', (ev) => {
            console.log('slots changed manualy');
            this.RequestListAction.slotCount = this.$main.find('select#appointmentForm_slotCount').val();
            this.loadFreeProcessList();
        }).on('click', '.form-actions button.process-reserve', (ev) => {
            event.preventDefault();
            event.stopPropagation();
            this.ProcessAction.reserve(ev).catch(err => this.loadErrorCallback(err)).then((processData) => {
                console.log('RESERVE successfully', processData);
                this.onSaveProcess(processData.id)
            });
        }).on('click', '.form-actions button.process-queue', (ev) => {
            event.preventDefault();
            event.stopPropagation();
            this.ProcessAction.queue(ev);
        }).on('click', '.form-actions button.process-edit', (ev) => {
            this.onEditProcess($(ev.target).data('id'))
        }).on('click', '.form-actions button.process-delete', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            this.ProcessAction.delete(ev).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadMessage(response, this.onDeleteProcess);
            });
        }).on('click', '.form-actions button.process-abort', (ev) => {
            this.selectedProcess = null;
            this.load();
        }).on('click', '.form-actions button.process-save', (ev) => {
            event.preventDefault();
            event.stopPropagation();
            this.ProcessAction.save(ev).catch(err => this.loadErrorCallback(err)).then((response) => {
                this.loadMessage(response, this.onSaveProcess);
            });
        })
    }

    loadMessage (response, callback) {
        const { lightboxContentElement, destroyLightbox } = lightbox(this.$main, () => {callback()})
        new MessageHandler(lightboxContentElement, {message: response})
    }

    loadErrorCallback(err) {
        if (err.status == 428)
            new FormValidationView(this.$main.find('.appointment-form form'), {
                responseJson: err.responseJSON
            });
        else if (err.message.toLowerCase().includes('exception')) {
            let exceptionType = $(err.message).filter('.exception').data('exception');
            if (exceptionType === 'reservation-failed')
                this.loadFreeProcessList();
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

    selectDateWithOverlay () {
        return new Promise((resolve, reject) => {
            const destroyCalendar = () => {
                tempCalendar.destroy()
            }

            const { lightboxContentElement, destroyLightbox } = lightbox(this.$main, () => {
                destroyCalendar()
                reject()
            })

            const tempCalendar = new CalendarView(lightboxContentElement, {
                includeUrl: this.includeUrl,
                selectedDate: this.selectedDate,
                onDatePick: (date) => {
                    destroyCalendar()
                    destroyLightbox()
                    resolve(date);
                },
                onDateToday: (date) => {
                    destroyCalendar()
                    destroyLightbox()
                    resolve(date);
                }
            })
        });
    }

    setSelectedDate () {
        this.$main.find('.add-date-picker input[name="date"]').val(moment(this.selectedDate, 'YYYY-MM-DD').format('L'))
    }
}

export default View;
