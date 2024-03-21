
import BindHandler from "./bindHandler";

class ErrorHandler extends BindHandler {
    constructor() {
        super();
        this._errorHandler = function() {};
    }

    get errorHandler () {
        return this._errorHandler;
    }
    set errorHandler (callback) {
        this._errorHandler = callback;
    }
}

export default ErrorHandler;
