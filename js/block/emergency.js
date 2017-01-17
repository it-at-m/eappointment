import BaseView from '../lib/baseview'
import $ from 'jquery'
import { deepGet, tryJson, noOp } from '../lib/utils'
import { playSound } from '../lib/audio'

const DEFAULT_REFRESH_INTERVAL = 5

class View extends BaseView {

    constructor (element, options) {
        super(element)
        this.includeUrl = options.includeurl
        this.workstationName = ""+options.workstationname
        this.scope = options.scope
        this.data = Object.assign({}, deepGet(this, ['scope', 'status', 'emergency']))
        this.minimized = false
        this.refreshTimer = null
        this.refreshId = 0

        this.bindPublicMethods('triggerEmergency',
                               'endEmergency',
                               'comeForHelp',
                               'refresh',
                               'minimize',
                               'show')
        this.$.find('.emergency__button-trigger').on('click', this.triggerEmergency)
        this.$.find('.emergency__button-end').on('click', this.endEmergency)
        this.$.find('.emergency__button-help').on('click', this.comeForHelp)
        this.$.find('.emergency__button-hide').on('click', this.minimize)
        this.$.find('.emergency__button-show').on('click', this.show)

        this.render()
        this.refresh()
        console.log('Component: Emergency', this)
    }

    invalidateRefreshId() {
        this.refreshId = this.refreshId + 1
    }

    refresh() {
        const refreshInterval = deepGet(this, ['scope',
                                               'preferences',
                                               'workstation',
                                               'emergencyRefreshInterval']) || DEFAULT_REFRESH_INTERVAL
        this.invalidateRefreshId()
        const refreshId = this.refreshId
        clearTimeout(this.refreshTimer)

        this.loadData()
            .then(data => {
                // if there is a more recent refresh request going on, ignore this one.
                // this is to prevent race condition when updating the UI after a user input
                if (refreshId === this.refreshId) {
                    return data
                } else {
                    throw { message: 'outdated refresh request' }
                }
            })
            .then(data => {
                this.playSound(data)
                this.update(data)
            }, noOp).then(() => {
                this.refreshTimer = setTimeout(this.refresh, refreshInterval * 1000)
            })
    }

    loadData () {
        const url = `${this.includeUrl}/workstation/status/`

        return new Promise((resolve, reject) => {
            $.ajax(url, {
                method: 'GET'
            }).done(data => {
                const emergencyData = deepGet(tryJson(data), ['workstation', 'scope', 'status', 'emergency'])
                resolve(emergencyData)
            }).fail(err => {
                console.log('XHR error', url, err)
                reject(err)
            })
        })
    }

    sendEmergencyCall() {
        this.invalidateRefreshId()
        const url = `${this.includeUrl}/scope/${this.scope.id}/emergency/`

        return new Promise((resolve, reject) => {
            $.ajax(url, {
                method: 'POST'
            }).done(() => {
                resolve()
            }).fail(err => {
                console.log('XHR error', url, err)
                reject(err)
            })
        })
    }

    sendEmergencyResponse() {
        this.invalidateRefreshId()
        const url = `${this.includeUrl}/scope/${this.scope.id}/emergency/respond/`

        return new Promise((resolve, reject) => {
            $.ajax(url, {
                method: 'POST'
            }).done(() => {
                resolve()
            }).fail(err => {
                console.log('XHR error', url, err)
                reject(err)
            })
        })
    }

    sendEmergencyCancel() {
        this.invalidateRefreshId()
        const url = `${this.includeUrl}/scope/${this.scope.id}/emergency/`

        return new Promise((resolve, reject) => {
            $.ajax(url, {
                method: 'DELETE'
            }).done(() => {
                resolve()
            }).fail(err => {
                console.log('XHR error', url, err)
                reject(err)
            })
        })
    }

    update (newData) {
        this.data = Object.assign({}, this.data, newData)
        this.render()
    }

    playSound(data) {
        console.log('playSound', data.activated, this.data.activated, data.calledByWorkstation, this.workstationName)
        let play = false

        if (data.activated === "1") {
            if (this.data.activated === "0") {
                //emergency changed from 'off' to 'on', play the sound!
                play = true
            } else {
                if (data.calledByWorkstation !== this.data.calledByWorkstation) {
                    //the source of the emergency changed, it must be a new one. play sound!
                    play = true
                }
            }

            if ( data.calledByWorkstation === this.workstationName) {
                //never play the sound when the source is the same workstation
                play = false
            }
        }

        if (play) {
            playSound(`${this.includeUrl}/_audio/emergency.ogg`)
        }
    }

    render () {
        const data = this.data

        const activated = parseInt(data.activated, 10)
        const acceptedByWorkstation = parseInt(data.acceptedByWorkstation, 10)
        const source = data.calledByWorkstation === this.workstationName ? 'self' : 'other'

        let state = 'clear'
        if (activated > 0) {
            state = 'triggered'

            if (acceptedByWorkstation > -1) {
                state = 'help-coming'
            }
        }

        if (this.minimized) {
            this.$.attr('data-minimized', true)
        } else {
            this.$.removeAttr('data-minimized')
        }



        this.$.attr('data-source', source)
        this.$.attr('data-state', state)
        this.$.find('.emergency__source').text(data.calledByWorkstation)
        this.$.find('.emergency__help-from').text(data.acceptedByWorkstation)
    }

    minimize() {
        this.minimized = true
        this.render()
    }

    show() {
        this.minimized = false
        this.render()
    }

    triggerEmergency () {
        this.update({activated: "1", calledByWorkstation: this.workstationName })
        this.sendEmergencyCall().then(this.refresh)
    }

    comeForHelp () {
        this.update({acceptedByWorkstation: this.workstationName})
        this.sendEmergencyResponse().then(this.refresh)
    }

    endEmergency() {
        this.update({activated: "0", calledByWorkstation: "-1", acceptedByWorkstation: "-1"})
        this.sendEmergencyCancel().then(this.refresh)
    }
}

export default View;
