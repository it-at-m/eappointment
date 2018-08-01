/*
import BaseView from "../../lib/baseview"

class View extends BaseView {

    constructor (element, options) {
        super(element, options);
        this.$.ready(() => {
            this.bindKeyEvents();
        });
      //  console.log('Component: LoginForm', this, options);
    }

    bindKeyEvents() {
        $(document).off().on('keypress', (keyEvent) => {
            var input = $('input');
            if (!input.is(":focus")) {
                switch (keyEvent.key) {
                    case "n":
                        this.$main.find('input[name=loginName]').focus()
                        break;
                    case "p":
                        this.$main.find('input[name=password]').focus()
                        break;
                }}
        })
    }
}

export default View
*/
