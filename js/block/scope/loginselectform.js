import BaseView from "../../lib/baseview"
import $ from 'jquery'

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.includeUrl = options.includeurl
        this.today = this.options.today
        $(() => {
            this.scopeId = this.$main.find('select[name=scope]').val() || null;
            this.bindEvents();
            this.update();
        });
        //console.log('Component: LoginForm', this, options);

    }

    bindEvents() {
        this.$main.on('click', '[data-button-print]', (ev) => {
            ev.preventDefault()
            ev.stopPropagation()

            this.printAppointments()
        }).on('click', '[data-button-download]', (ev) => {
            ev.preventDefault()
            ev.stopPropagation()

            this.downloadAppointments()
        }).on('change', 'select[name=scope]', (ev)=> {
            this.scopeId = ev.target.value
            this.update()
        })
    }

    printAppointments() {
        if (this.scopeId && this.today) {
            window.open(`${this.includeUrl}/scope/${this.scopeId}/process/${this.today}/?print=1`)
        } else {
            if (!this.scopeId) {
                alert('Kein Standort ausgewählt');
            }
        }
    }

    downloadAppointments() {
        if (this.scopeId && this.today)  {
            window.open(`${this.includeUrl}/scope/${this.scopeId}/process/${this.today}/spreadsheet/`)
        } else {
            if (!this.scopeId) {
                alert('Kein Standort ausgewählt');
            }
        }
    }

    update() {
        const $scopeButtons = this.$main.find('[data-button-print],[data-button-download]')

        if (this.scopeId) {
            $scopeButtons.removeAttr('disabled')
        } else {
            $scopeButtons.attr('disabled', 'disabled')
        }
    }
}

export default View
