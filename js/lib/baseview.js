import $ from "jquery";
import BindHandler from "./bindHandler";

class BaseView extends BindHandler {

    constructor(element, options = {}) {
        super();
        this.$main = $(element);
        this.$main.off();
        this.options = options;
    }

    get $ () {
        return this.$main;
    }

}

export default BaseView;
