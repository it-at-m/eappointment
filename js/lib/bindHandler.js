

class BindHandler {

    bindPublicMethods (...methods) {
        let object = this;
        methods.forEach( function (method) {
            if (typeof object[method] !== 'function') {
                throw "Method not found: " + method;
            }
            object[method] = object[method].bind(object);
        });
    }
}

export default BindHandler;
