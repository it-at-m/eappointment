/* global window */
import BaseView from "../../lib/baseview"

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.includeUrl = options.includeurl
        this.today = this.options.today
        this.scopeId = null
        this.bindEvents();
        console.log('Component: LoginForm', this, options);
        this.update()
    }

    bindEvents() {
        this.$main.off().on('click', '[data-button-print]', (ev) => {
            ev.preventDefault()
            ev.stopPropagation()

            this.printAppointments()
        }).on('click', '[data-button-download]', (ev) => {
            ev.preventDefault()
            ev.stopPropagation()

            this.downloadAppointments()
        }).on('change', 'select[name=scope]', (ev)=> {
            this.scopeId = ev.target.value
            console.log(this.scopeId)
            this.update()
        })
    }

    printAppointments() {
        if (this.scopeId && this.today) {
            window.open(`${this.includeUrl}/scope/${this.scopeId}/process/${this.today}/?print=1`)
        }
    }

    downloadAppointments() {
        if (this.scopeId && this.today)  {
            window.open(`${this.includeUrl}/scope/${this.scopeId}/process/${this.today}/xlsx/`)
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
