import BaseView from '../../lib/baseview'
import $ from 'jquery'

class View extends BaseView {

    constructor (element, options) {
        super(element)
        this.includeUrl = options.includeurl
        this.render()
    }

    render () {
        const url = `${this.includeUrl}/metalinks/`
        return this.loadContent(url, 'GET');
    }
}

export default View;
