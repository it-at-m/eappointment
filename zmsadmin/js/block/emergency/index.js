import BaseView from '../../lib/baseview'
import $ from 'jquery'
import { deepGet, tryJson, noOp } from '../../lib/utils'
import { playSound } from '../../lib/audio'

const DEFAULT_REFRESH_INTERVAL = 5

class View extends BaseView {

    constructor (element, options) {
        super(element)
        this.includeUrl = options.includeurl
        this.returnTarget = options.returnTarget
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
        this.$.on('keydown', function exitKeyEventListener (ev){
            var key = ev.key;
            switch(key) {
                case 'Escape': // ESC
                console.log('ESC'); 
                this.minimize; // ToDo: Don't work yet
                break;
            }
        })
        

        this.render()
        this.refresh()
        //console.log('Component: Emergency', this)
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
                method: 'GET'
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
        //console.log('playSound', data.activated, this.data.activated, data.calledByWorkstation, this.workstationName)
        let play = false

        if (data.activated === "1") {
            play = true

            if ( data.calledByWorkstation === this.workstationName) {
                //never play the sound when the source is the same workstation
                play = false
            }
        }

        if (this.minimized) {
            play = false
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
        // Barrierefreiheit
        if (state == 'clear') {
            this.$.find('.emergency__overlay').attr('hidden', 'hidden')
        } else {
            this.$.find('.emergency__overlay').removeAttr('hidden')
        }

        this.$.attr('data-source', source)
        this.$.attr('data-state', state)
        this.$.find('.emergency__source').text((data.calledByWorkstation === '0') ? 'Tresen' : `Platz ${data.calledByWorkstation}`)
        this.$.find('.emergency__help-from').text((data.acceptedByWorkstation === '0' ? 'Tresen' : `Platz ${data.acceptedByWorkstation}`))
    }

    minimize() {
        this.minimized = true
        this.render()
        //console.log('minimize emergency');
        this.removeFocusTrap(this.$.find('.emergency__overlay-layout'));
    }

    show() {
        this.minimized = false
        this.render()
        //console.log('maximize emergency');
        this.addFocusTrap(this.$.find('.emergency__overlay-layout'));
    }

    triggerEmergency () {
        this.update({activated: "1", calledByWorkstation: this.workstationName })
        this.sendEmergencyCall().then(this.refresh)
        //console.log('start emergency');
        this.addFocusTrap(this.$.find('.emergency__overlay-layout'));
    }

    comeForHelp () {
        this.update({acceptedByWorkstation: this.workstationName})
        this.sendEmergencyResponse().then(this.refresh)
        //console.log('comeforhelp emergency');
    }

    endEmergency() {
        this.update({activated: "0", calledByWorkstation: "-1", acceptedByWorkstation: "-1"})
        this.sendEmergencyCancel().then(this.refresh)
        //console.log('end emergency');
        this.removeFocusTrap(this.$.find('.emergency__overlay-layout'));
    }

    removeFocusTrap(elem) {
        var tabbable = elem.find('select, input, textarea, button, a, *[role="button"]');
        tabbable.unbind('keydown');
    }

    addFocusTrap(elem) {
        // Get all focusable elements inside our trap container
        var tabbable = elem.find('select, input, textarea, button, a, *[role="button"]');
        // Focus the first element
        if (tabbable.length ) {
            tabbable.filter(':visible').first().focus();
            //console.log(tabbable.filter(':visible').first());
        }
        tabbable.bind('keydown', function (e) {
            if (e.keyCode === 9) { // TAB pressed
                // we need to update the visible last and first focusable elements everytime tab is pressed,
                // because elements can change their visibility
                var firstVisible = tabbable.filter(':visible').first();
                var lastVisible = tabbable.filter(':visible').last();
                if (firstVisible && lastVisible) {
                    if (e.shiftKey && ( $(firstVisible)[0] === $(this)[0] ) ) {
                        // TAB + SHIFT pressed on first visible element
                        e.preventDefault();
                        lastVisible.focus();
                    } 
                    else if (!e.shiftKey && ( $(lastVisible)[0] === $(this)[0] ) ) {
                        // TAB pressed pressed on last visible element
                        e.preventDefault();
                        firstVisible.focus();
                    }
                }
            }
        });
    }

}

export default View;
