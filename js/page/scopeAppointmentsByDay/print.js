import $ from 'jquery'
import { getUrlParameters } from '../../lib/utils'

export default () => {
    const scopeAppointmentsElement = $('.scope-appointments-by-day')

    if (scopeAppointmentsElement.length > 0) {
        if (getUrlParameters().print === "1") {
            window.print()
        }
    }
}
