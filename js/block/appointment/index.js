import BaseView from "../../lib/baseview"
import $ from "jquery"
import freeProcessList from './free-process-list'
import { lightbox } from '../../lib/utils'
import CalendarView from '../calendar'
import moment from 'moment'

class View extends BaseView {

    constructor (element, options) {
        super(element);
        this.selectedDate = options.selectedDate;
        this.selectedTime = options.selectedTime;
        this.includeUrl = options.includeUrl || "";
        this.selectedProcess = options.selectedProcess;
        this.slotCount = 0;
        this.slotType = 'intern';
        this.serviceList = [];

        this.load().then(() => {
            this.cleanUpLists();
            this.loadFreeProcessList();
            this.bindEvents();
        });
    }

    load() {
        const url = `${this.includeUrl}/appointmentForm/?selecteddate=${this.selectedDate}&selectedprocess=${this.selectedProcess}`
        this.loadPromise = this.loadContent(url)
        return this.loadPromise;
    }

    bindEvents() {
        this.$main.on('change', '.checkboxselect input:checkbox', (event) => {
            this.addService($(event.target), this.serviceListSelected);
            this.removeService($(event.target), this.serviceList);
            this.updateList();
        }).on('change', '.checkboxdeselect input:checkbox', (event) => {
            this.removeService($(event.target), this.serviceListSelected);
            this.addService($(event.target), this.serviceList);
            this.updateList();
        }).on('click', '.clear-list', () => {
            this.cleanUpLists();
            this.updateList();
        }).on('click', 'input[name=date]', () => {
            console.log('date click')
            this.selectDateWithOverlay()
                   .then(date => {
                       this.selectedDate = date;
                       this.setSelectedDate();
                   })
                   .then(() => {
                       this.calculateSlotCount();
                       this.loadFreeProcessList();
                   })
                   .catch(() => console.log('no date selected'));
        }).on('change', 'select#appointmentForm_slotCount', (event) => {
            console.log('slots changed manualy');
            this.slotCount = this.$main.find('select#appointmentForm_slotCount').val();
            this.loadFreeProcessList();
        })
    }

    /**
     * update events after replacing list
     */
    updateList () {
        this.$main.find('.checkboxdeselect input:checkbox').each((index, element) => {
            $(element).prop("checked", false);
            $(element).closest('label').hide();
            if ($.inArray($(element).val(), this.serviceListSelected) !== -1) {
                $(element).prop("checked", true);
                $(element).closest('label').show();
            }
        });

        this.$main.find('.checkboxselect input:checkbox').each((index, element) => {
            $(element).prop("checked", false);
            $(element).closest('label').hide();
            if ($.inArray($(element).val(), this.serviceList) !== -1) {
                $(element).closest('label').show();
            }
        });
        this.calculateSlotCount();
        this.loadFreeProcessList();
    }

    addService (element, list) {
        return list.push(element.val());
    }

    removeService (element, list) {
        for (var i = 0; i < list.length; i++)
            if (list[i] === element.val()) {
                return list.splice(i,1);
            }
    }

    cleanUpLists ()
    {
        this.serviceList = this.$main.find('.checkboxselect input:checkbox').map(function() {
            return $(this).val();
        }).toArray();
        this.serviceListSelected = [];
    }

    calculateSlotCount () {
        var slotCount = 0;
        var selectedSlots = this.$main.find('.checkboxdeselect label:visible input:checkbox').map(function() {
            return $(this).data('slots');
        }).toArray();
        for (var i = 1; i < selectedSlots.length; i++)
            if (selectedSlots[i] > 0) {
                slotCount += selectedSlots[i];
            }
        this.slotCount = slotCount;
        this.$main.find('#appointmentForm_slotCount option:eq(' + this.slotCount +')').prop('selected', true)
    }

    loadFreeProcessList () {
        return new freeProcessList(this.$main.find('[data-free-process-list]'), {
            selectedDate: this.selectedDate,
            selectedTime: this.selectedTime,
            includeUrl: this.includeUrl,
            slotsRequired: this.slotCount,
            slotType: this.slotType
        })
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
