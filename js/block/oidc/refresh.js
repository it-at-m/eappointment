import BaseView from '../../lib/baseview'
import $ from 'jquery'

const DEFAULT_REFRESH_INTERVAL = 5 //in minutes

class OidcRefresh extends BaseView {

    constructor (element, options) {
        super(element)
        this.includeUrl = options.includeurl
        this.refreshTimer = null
        this.refreshId = 0
        this.bindPublicMethods('refresh', 'invalidateRefreshId', 'loadData');
        this.refresh()
    }

    invalidateRefreshId () {
        this.refreshId = this.refreshId + 1
    }

    refresh () {
        const refreshInterval = DEFAULT_REFRESH_INTERVAL

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
            .then(() => {
                this.refreshTimer = setTimeout(this.refresh, refreshInterval * 60 * 1000)
            })
    }

    loadData () {
        const url = `${this.includeUrl}/oidc/refresh/`
        return new Promise((resolve, reject) => {
            $.ajax(url, {
                method: 'GET'
            }).done(data => {
                resolve(data)
            }).fail(err => {
                console.log('XHR error', url, err)
                reject(err)
            })
        })
    }

}

export default OidcRefresh;
