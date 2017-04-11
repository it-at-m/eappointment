/* global window */
import $ from 'jquery'
import { getUrlParameters } from '../../lib/utils'

export default () => {
    if (getUrlParameters().print === "1") {
        window.print()
    }
}
