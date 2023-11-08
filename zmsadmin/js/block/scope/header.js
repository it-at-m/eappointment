import BaseView from '../../lib/baseview'
import $ from 'jquery'
import { deepGet, tryJson } from '../../lib/utils'

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.$main = $(element);
        this.includeUrl = options.includeUrl;
        this.data = this.$main.data('header-scope');
        this.bindPublicMethods('refresh');
        this.render();
        this.refresh();
    }

    loadData() {
        const url = `${this.includeUrl}/workstation/status/`
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

    refresh() {
        this.loadData()
            .then(data => {
                this.data = Object.assign({}, deepGet(tryJson(data), ['workstation', 'scope', 'contact']));
                this.render()
            })
    }
    
    render() {
        const data = this.data;
        let length = 30;  // set to the number of characters you want to keep
        let trimmedName = (data.name.length > length) ? data.name.substr(0, length-1) + '...' : data.name;
        this.$main.find('.header-scope-name').text(trimmedName)
        this.$main.find('.header-scope-title').attr('title', data.name)
    }
}

export default View
