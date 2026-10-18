import $ from 'jquery'

/**
 * ZMS-3503: unchecking Notruffunktion on the Standort form ends an active emergency
 * via DELETE /scope/{id}/emergency/ (proxied as GET from this page).
 */
class EndEmergencyView {

    constructor(element, options) {
        this.includeUrl = options.includeurl
        this.scope = options.scope
        this.workstationName = "" + options.workstationname
        this.data = {}
        this.$ = $(element)

        this.$.find('.emergency__button-end').on('change', (ev) => {
            if (!ev.currentTarget.checked) {
                this.endEmergency()
            }
        })
    }

    endEmergency() {
        this.update({ activated: "0", calledByWorkstation: "-1", acceptedByWorkstation: "-1" })
        this.sendEmergencyCancel()
    }

    update(newData) {
        this.data = Object.assign({}, this.data, newData)
    }

    sendEmergencyCancel() {
        const url = `${this.includeUrl}/scope/${this.scope.id}/emergency/`

        return new Promise((resolve, reject) => {
            $.ajax(url, {
                method: 'GET'
            }).done(() => {
                resolve()
            }).fail(err => {
                console.log('XHR error', url, err)
                reject(err)
            })
        })
    }
}

export default EndEmergencyView;
