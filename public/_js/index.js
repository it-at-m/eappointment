(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _baseview = require('../lib/baseview');

var _baseview2 = _interopRequireDefault(_baseview);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; } /* global setInterval */


var View = function (_BaseView) {
    _inherits(View, _BaseView);

    function View(element) {
        _classCallCheck(this, View);

        var _this = _possibleConstructorReturn(this, (View.__proto__ || Object.getPrototypeOf(View)).call(this, element));

        _this.bindPublicMethods('initClock', 'setInterval');
        console.log("Found digital clock");
        _this.$.ready(_this.setInterval);
        return _this;
    }

    _createClass(View, [{
        key: 'initClock',
        value: function initClock() {
            var time = new Date();
            var hour = time.getHours();
            var minute = time.getMinutes();
            var second = time.getSeconds();
            var temp = hour;
            if (second % 2) temp += (minute < 10 ? ":0" : ":") + minute;else temp += (minute < 10 ? ":0" : " ") + minute;
            this.$.text(temp);
        }
    }, {
        key: 'setInterval',
        value: function (_setInterval) {
            function setInterval() {
                return _setInterval.apply(this, arguments);
            }

            setInterval.toString = function () {
                return _setInterval.toString();
            };

            return setInterval;
        }(function () {
            setInterval(this.initClock, 1000);
        })
    }]);

    return View;
}(_baseview2.default);

exports.default = View;

},{"../lib/baseview":5}],2:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _baseview = require('../lib/baseview');

var _baseview2 = _interopRequireDefault(_baseview);

var _jquery = require('jquery');

var _jquery2 = _interopRequireDefault(_jquery);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var View = function (_BaseView) {
    _inherits(View, _BaseView);

    function View(element) {
        _classCallCheck(this, View);

        var _this = _possibleConstructorReturn(this, (View.__proto__ || Object.getPrototypeOf(View)).call(this, element));

        _this.source = _this.$.attr('id');
        if (_this.source == "waitingNumberPad") {
            _this.minNumberLength = 1;
        } else {
            _this.minNumberLength = 10;
        }

        _this.bindPublicMethods('appendNumber', 'deleteNumber', 'clearNumbers', 'checkNumber');
        console.log("Found keyboard-handheld");
        _this.$.find('button.ziffer').on('click', _this.appendNumber);
        _this.$.find('button#removelastdigit').on('click', _this.deleteNumber);
        _this.$.find('button#removealldigitsphone').on('click', _this.clearNumbers);
        _this.$numberInput = _this.$.find('.nummerneingabe');
        return _this;
    }

    _createClass(View, [{
        key: 'appendNumber',
        value: function appendNumber(event) {
            var $content = (0, _jquery2.default)(event.target).closest('button');
            var number = $content.text();
            this.$numberInput.val(this.$numberInput.val() + '' + number);
            this.checkNumber();
            return false;
        }
    }, {
        key: 'deleteNumber',
        value: function deleteNumber() {
            this.$numberInput.val(this.$numberInput.val().replace(/.$/, ''));
            this.checkNumber();
            return false;
        }
    }, {
        key: 'clearNumbers',
        value: function clearNumbers() {
            this.$numberInput.val('');
            this.checkNumber();
            return false;
        }
    }, {
        key: 'checkNumber',
        value: function checkNumber() {
            //console.log(this.$numberInput.val());
            var number = this.$numberInput.val();
            if (this.source == 'waitingNumberPad') {
                number = number.replace(/^0+/, '');
            }
            number = number.replace(/[^\d]/g, '');
            var $button = this.$.find('.nachtrag');
            if (number.length >= this.minNumberLength) {
                $button.removeClass('disabled').attr('disabled', false);
            } else {
                if (!$button.hasClass('disabled')) {
                    $button.addClass('disabled').attr('disabled', true);
                }
            }
            this.$numberInput.val(number);
        }
    }]);

    return View;
}(_baseview2.default);

exports.default = View;

},{"../lib/baseview":5,"jquery":"jquery"}],3:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});

var _jquery = require('jquery');

var _jquery2 = _interopRequireDefault(_jquery);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var preventFormResubmit = function preventFormResubmit(element) {
  var $form = (0, _jquery2.default)(element);

  $form.on('submit', function (ev) {
    if ($form.data('submitted') === true) {
      // Previously submitted - don't submit again
      ev.stopPropagation();
      ev.preventDefault();
    } else {
      // Mark it so that the next submit can be ignored
      $form.data('submitted', true);
    }
  });
};

exports.default = preventFormResubmit;

},{"jquery":"jquery"}],4:[function(require,module,exports){
(function (global){
"use strict";

require("babel-polyfill");

var _window = (typeof window !== "undefined" ? window['window'] : typeof global !== "undefined" ? global['window'] : null);

var _window2 = _interopRequireDefault(_window);

var _jquery = require("jquery");

var _jquery2 = _interopRequireDefault(_jquery);

var _settings = require("./settings");

var _settings2 = _interopRequireDefault(_settings);

var _utils = require("./lib/utils");

var _main = require("./page/main");

var _main2 = _interopRequireDefault(_main);

var _newhash = require("./page/newhash");

var _newhash2 = _interopRequireDefault(_newhash);

var _process = require("./page/process");

var _process2 = _interopRequireDefault(_process);

var _digitalClock = require("./block/digital-clock");

var _digitalClock2 = _interopRequireDefault(_digitalClock);

var _notificationKeyboardHandheld = require("./block/notification-keyboard-handheld");

var _notificationKeyboardHandheld2 = _interopRequireDefault(_notificationKeyboardHandheld);

var _preventFormResubmit = require("./element/form/preventFormResubmit");

var _preventFormResubmit2 = _interopRequireDefault(_preventFormResubmit);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

// Bind jQuery on $ for testing


// Import Views


// Import base libs
_window2.default.$ = _jquery2.default; // --------------------------------------------------------
// ZMS Ticketprinter behavior
// --------------------------------------------------------

_window2.default.bo = {
    "zmsticketprinter": _settings2.default
};

// Init Views
(0, _jquery2.default)('#newhash').each(function () {
    new _newhash2.default(this);
});
(0, _jquery2.default)('#index, #message, #exception').each(function () {
    new _main2.default(this);
});
(0, _jquery2.default)('#process').each(function () {
    new _process2.default(this);
});
(0, _jquery2.default)('.digitaluhr').each(function () {
    new _digitalClock2.default(this);
});
(0, _jquery2.default)('.smsbox').each(function () {
    new _notificationKeyboardHandheld2.default(this);
});

// prevent resubmits
(0, _jquery2.default)('form').each(function () {
    (0, _preventFormResubmit2.default)(this);
});

// Say hello
console.log("Welcome to the ZMS Ticketprinter interface...");

// Force https protocol
(0, _utils.forceHttps)();

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})

},{"./block/digital-clock":1,"./block/notification-keyboard-handheld":2,"./element/form/preventFormResubmit":3,"./lib/utils":9,"./page/main":10,"./page/newhash":11,"./page/process":12,"./settings":13,"babel-polyfill":"babel-polyfill","jquery":"jquery"}],5:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _jquery = require('jquery');

var _jquery2 = _interopRequireDefault(_jquery);

var _errorHandler = require('./errorHandler');

var _errorHandler2 = _interopRequireDefault(_errorHandler);

var _dialogHandler = require('./dialogHandler');

var _dialogHandler2 = _interopRequireDefault(_dialogHandler);

var _utils = require('./utils');

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var BaseView = function (_ErrorHandler) {
    _inherits(BaseView, _ErrorHandler);

    function BaseView(element) {
        _classCallCheck(this, BaseView);

        var _this = _possibleConstructorReturn(this, (BaseView.__proto__ || Object.getPrototypeOf(BaseView)).call(this, element));

        _this.$main = (0, _jquery2.default)(element);
        return _this;
    }

    _createClass(BaseView, [{
        key: '$',
        get: function get() {
            return this.$main;
        }
    }], [{
        key: 'loadCallStatic',
        value: function loadCallStatic(url) {
            var method = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'GET';
            var data = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;

            var ajaxSettings = {
                method: method
            };
            if (method === 'POST' || method === 'PUT') {
                ajaxSettings.data = data;
            }
            return new Promise(function (resolve, reject) {
                _jquery2.default.ajax(url, ajaxSettings).done(function (responseData) {
                    resolve(responseData);
                }).fail(function (err) {
                    console.log('XHR load error', url, err);
                    reject(err);
                });
            });
        }
    }, {
        key: 'loadDialogStatic',
        value: function loadDialogStatic(response, _callback) {
            var _lightbox = (0, _utils.lightbox)(this.$main, function () {
                destroyLightbox(), _callback();
            }),
                lightboxContentElement = _lightbox.lightboxContentElement,
                destroyLightbox = _lightbox.destroyLightbox;

            new _dialogHandler2.default(lightboxContentElement, {
                response: response,
                callback: function callback() {
                    _callback();
                    destroyLightbox();
                }
            });
        }
    }]);

    return BaseView;
}(_errorHandler2.default);

exports.default = BaseView;

},{"./dialogHandler":7,"./errorHandler":8,"./utils":9,"jquery":"jquery"}],6:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var BindHandler = function () {
    function BindHandler() {
        _classCallCheck(this, BindHandler);
    }

    _createClass(BindHandler, [{
        key: "bindPublicMethods",
        value: function bindPublicMethods() {
            var object = this;

            for (var _len = arguments.length, methods = Array(_len), _key = 0; _key < _len; _key++) {
                methods[_key] = arguments[_key];
            }

            methods.forEach(function (method) {
                if (typeof object[method] !== 'function') {
                    throw "Method not found: " + method;
                }
                object[method] = object[method].bind(object);
            });
        }
    }]);

    return BindHandler;
}();

exports.default = BindHandler;

},{}],7:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _jquery = require('jquery');

var _jquery2 = _interopRequireDefault(_jquery);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var DialogHandler = function () {
    function DialogHandler(element, options) {
        _classCallCheck(this, DialogHandler);

        this.$main = (0, _jquery2.default)(element);
        this.response = options.response;
        this.callback = options.callback || function () {};
        this.parent = options.parent;
        this.handleLightbox = options.handleLightbox || function () {};
        this.bindEvents();
        this.render();
    }

    _createClass(DialogHandler, [{
        key: 'render',
        value: function render() {
            var content = (0, _jquery2.default)(this.response).filter('div.dialog');
            if (content.length == 0) {
                var message = (0, _jquery2.default)(this.response).find('div.dialog');
                if (message.length > 0) {
                    content = message.get(0).outerHTML;
                }
            }
            this.$main.html(content);
        }
    }, {
        key: 'bindEvents',
        value: function bindEvents() {
            var _this = this;

            this.$main.off().on('click', '.button-ok', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                _this.callback(ev);
            }).on('click', '.button-abort', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                _this.handleLightbox();
            }).on('click', '.button-callback', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                var callback = (0, _jquery2.default)(ev.target).data('callback');
                _this.callback = _this.parent[callback];
                _this.callback(ev);
            });
        }
    }]);

    return DialogHandler;
}();

exports.default = DialogHandler;

},{"jquery":"jquery"}],8:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _bindHandler = require("./bindHandler");

var _bindHandler2 = _interopRequireDefault(_bindHandler);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var ErrorHandler = function (_BindHandler) {
    _inherits(ErrorHandler, _BindHandler);

    function ErrorHandler() {
        _classCallCheck(this, ErrorHandler);

        var _this = _possibleConstructorReturn(this, (ErrorHandler.__proto__ || Object.getPrototypeOf(ErrorHandler)).call(this));

        _this._errorHandler = function () {};
        return _this;
    }

    _createClass(ErrorHandler, [{
        key: "errorHandler",
        get: function get() {
            return this._errorHandler;
        },
        set: function set(callback) {
            this._errorHandler = callback;
        }
    }]);

    return ErrorHandler;
}(_bindHandler2.default);

exports.default = ErrorHandler;

},{"./bindHandler":6}],9:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.forceHttps = exports.lightbox = undefined;

var _jquery = require('jquery');

var _jquery2 = _interopRequireDefault(_jquery);

var _baseview = require('./baseview');

var _baseview2 = _interopRequireDefault(_baseview);

var _settings = require('../settings');

var _settings2 = _interopRequireDefault(_settings);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var lightboxHtml = '<div class="lightbox"><div class="lightbox__content"></div></div>';

var lightbox = exports.lightbox = function lightbox(parentElement, onBackgroundClick) {
    var lightboxElement = (0, _jquery2.default)(lightboxHtml);

    if (!parentElement) {
        parentElement = (0, _jquery2.default)('body');
        lightboxElement.addClass('fixed');
    }

    var destroyLightbox = function destroyLightbox() {
        lightboxElement.off();
        lightboxElement.remove();
    };

    var lightboxContentElement = lightboxElement.find('.lightbox__content');

    lightboxElement.on('click', function (ev) {
        //console.log('background click', ev);
        ev.stopPropagation();
        ev.preventDefault();
        destroyLightbox();
        onBackgroundClick();
    }).on('click', '.lightbox__content', function (ev) {
        ev.stopPropagation();
    });

    if ((0, _jquery2.default)(parentElement).find('.lightbox').length) {
        (0, _jquery2.default)(parentElement).find('.lightbox').remove();
    }
    (0, _jquery2.default)(parentElement).append(lightboxElement);

    return {
        lightboxContentElement: lightboxContentElement,
        destroyLightbox: destroyLightbox
    };
};

var forceHttps = exports.forceHttps = function forceHttps() {
    if (document.location.protocol !== "https:") {
        _baseview2.default.loadCallStatic(_settings2.default.includeUrl + '/dialog/?template=force_https').then(function (response) {
            _baseview2.default.loadDialogStatic(response, function () {
                document.location.href = "https://" + document.location.href.substring(document.location.protocol.length, document.location.href.length);
            });
        });
    }
};

},{"../settings":13,"./baseview":5,"jquery":"jquery"}],10:[function(require,module,exports){
(function (global){
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _baseview = require('../lib/baseview');

var _baseview2 = _interopRequireDefault(_baseview);

var _window = (typeof window !== "undefined" ? window['window'] : typeof global !== "undefined" ? global['window'] : null);

var _window2 = _interopRequireDefault(_window);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; } /* global setInterval */


var View = function (_BaseView) {
    _inherits(View, _BaseView);

    function View(element) {
        _classCallCheck(this, View);

        var _this = _possibleConstructorReturn(this, (View.__proto__ || Object.getPrototypeOf(View)).call(this, element));

        _this.bindPublicMethods('setInterval', 'reloadPage');
        console.log('Redirect to home url every 30 seconds');
        _this.$.ready(_this.setInterval);
        return _this;
    }

    _createClass(View, [{
        key: 'reloadPage',
        value: function reloadPage() {
            _window2.default.location.href = this.getUrl('/home/');
        }
    }, {
        key: 'setInterval',
        value: function (_setInterval) {
            function setInterval() {
                return _setInterval.apply(this, arguments);
            }

            setInterval.toString = function () {
                return _setInterval.toString();
            };

            return setInterval;
        }(function () {
            var reloadTime = _window2.default.bo.zmsticketprinter.reloadInterval;
            setInterval(this.reloadPage, reloadTime * 1000);
        })
    }, {
        key: 'getUrl',
        value: function getUrl(relativePath) {
            var includepath = _window2.default.bo.zmsticketprinter.includepath;
            return includepath + relativePath;
        }
    }]);

    return View;
}(_baseview2.default);

exports.default = View;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})

},{"../lib/baseview":5}],11:[function(require,module,exports){
(function (global){
"use strict";

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _baseview = require("../lib/baseview");

var _baseview2 = _interopRequireDefault(_baseview);

var _window = (typeof window !== "undefined" ? window['window'] : typeof global !== "undefined" ? global['window'] : null);

var _window2 = _interopRequireDefault(_window);

var _jsCookie = require("js-cookie");

var _jsCookie2 = _interopRequireDefault(_jsCookie);

var _jquery = require("jquery");

var _jquery2 = _interopRequireDefault(_jquery);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; } /* global setTimeout */


var View = function (_BaseView) {
    _inherits(View, _BaseView);

    function View(element) {
        _classCallCheck(this, View);

        var _this = _possibleConstructorReturn(this, (View.__proto__ || Object.getPrototypeOf(View)).call(this, element));

        _this.bindPublicMethods('load', 'setTimeout', 'getUrl', 'reloadPage');
        (0, _jquery2.default)(_window2.default).on('load', _this.load);
        return _this;
    }

    _createClass(View, [{
        key: "load",
        value: function load() {
            _jsCookie2.default.remove("Ticketprinter", { secure: true });
            this.setTimeout();
        }
    }, {
        key: "reloadPage",
        value: function reloadPage() {
            _window2.default.location.href = this.getUrl('/home/');
        }
    }, {
        key: "setTimeout",
        value: function (_setTimeout) {
            function setTimeout() {
                return _setTimeout.apply(this, arguments);
            }

            setTimeout.toString = function () {
                return _setTimeout.toString();
            };

            return setTimeout;
        }(function () {
            setTimeout(this.reloadPage, 5000);
        })
    }, {
        key: "getUrl",
        value: function getUrl(relativePath) {
            var includepath = _window2.default.bo.zmsticketprinter.includepath;
            return includepath + relativePath;
        }
    }]);

    return View;
}(_baseview2.default);

exports.default = View;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})

},{"../lib/baseview":5,"jquery":"jquery","js-cookie":14}],12:[function(require,module,exports){
(function (global){
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _baseview = require('../lib/baseview');

var _baseview2 = _interopRequireDefault(_baseview);

var _window = (typeof window !== "undefined" ? window['window'] : typeof global !== "undefined" ? global['window'] : null);

var _window2 = _interopRequireDefault(_window);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; } /* global setTimeout */


var View = function (_BaseView) {
    _inherits(View, _BaseView);

    function View(element) {
        _classCallCheck(this, View);

        var _this = _possibleConstructorReturn(this, (View.__proto__ || Object.getPrototypeOf(View)).call(this, element));

        _this.bindPublicMethods('printDialog', 'reload');
        console.log('Print data and redirect to home url after presetted time');
        _this.$.ready(_this.printDialog);
        return _this;
    }

    _createClass(View, [{
        key: 'reload',
        value: function reload() {
            _window2.default.location.href = this.getUrl('/home/');
        }
    }, {
        key: 'getUrl',
        value: function getUrl(relativePath) {
            var includepath = _window2.default.bo.zmsticketprinter.includepath;
            return includepath + relativePath;
        }
    }, {
        key: 'printDialog',
        value: function printDialog() {
            var _this2 = this;

            document.title = "Anmeldung an Warteschlange";
            _window2.default.print();

            var beforePrint = function beforePrint() {
                console.log('start printing');
            };
            var afterPrint = function afterPrint() {
                var reloadTime = _window2.default.bo.zmsticketprinter.reloadInterval;
                setTimeout(function () {
                    _this2.reload();
                }, reloadTime * 1000); // default is 30
            };

            if (_window2.default.matchMedia) {
                var mediaQueryList = _window2.default.matchMedia('print');
                mediaQueryList.addListener(function (mql) {
                    if (mql.matches) {
                        beforePrint();
                    } else {
                        afterPrint();
                    }
                });
            }

            _window2.default.onbeforeprint = beforePrint;
            _window2.default.onafterprint = afterPrint;
        }
    }]);

    return View;
}(_baseview2.default);

exports.default = View;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})

},{"../lib/baseview":5}],13:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.default = {
    'reloadInterval': 30,
    'animationEnd': 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend',
    'includeUrl': '/terminvereinbarung/ticketprinter'
};

},{}],14:[function(require,module,exports){
/*!
 * JavaScript Cookie v2.2.0
 * https://github.com/js-cookie/js-cookie
 *
 * Copyright 2006, 2015 Klaus Hartl & Fagner Brack
 * Released under the MIT license
 */
;(function (factory) {
	var registeredInModuleLoader = false;
	if (typeof define === 'function' && define.amd) {
		define(factory);
		registeredInModuleLoader = true;
	}
	if (typeof exports === 'object') {
		module.exports = factory();
		registeredInModuleLoader = true;
	}
	if (!registeredInModuleLoader) {
		var OldCookies = window.Cookies;
		var api = window.Cookies = factory();
		api.noConflict = function () {
			window.Cookies = OldCookies;
			return api;
		};
	}
}(function () {
	function extend () {
		var i = 0;
		var result = {};
		for (; i < arguments.length; i++) {
			var attributes = arguments[ i ];
			for (var key in attributes) {
				result[key] = attributes[key];
			}
		}
		return result;
	}

	function init (converter) {
		function api (key, value, attributes) {
			var result;
			if (typeof document === 'undefined') {
				return;
			}

			// Write

			if (arguments.length > 1) {
				attributes = extend({
					path: '/'
				}, api.defaults, attributes);

				if (typeof attributes.expires === 'number') {
					var expires = new Date();
					expires.setMilliseconds(expires.getMilliseconds() + attributes.expires * 864e+5);
					attributes.expires = expires;
				}

				// We're using "expires" because "max-age" is not supported by IE
				attributes.expires = attributes.expires ? attributes.expires.toUTCString() : '';

				try {
					result = JSON.stringify(value);
					if (/^[\{\[]/.test(result)) {
						value = result;
					}
				} catch (e) {}

				if (!converter.write) {
					value = encodeURIComponent(String(value))
						.replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g, decodeURIComponent);
				} else {
					value = converter.write(value, key);
				}

				key = encodeURIComponent(String(key));
				key = key.replace(/%(23|24|26|2B|5E|60|7C)/g, decodeURIComponent);
				key = key.replace(/[\(\)]/g, escape);

				var stringifiedAttributes = '';

				for (var attributeName in attributes) {
					if (!attributes[attributeName]) {
						continue;
					}
					stringifiedAttributes += '; ' + attributeName;
					if (attributes[attributeName] === true) {
						continue;
					}
					stringifiedAttributes += '=' + attributes[attributeName];
				}
				return (document.cookie = key + '=' + value + stringifiedAttributes);
			}

			// Read

			if (!key) {
				result = {};
			}

			// To prevent the for loop in the first place assign an empty array
			// in case there are no cookies at all. Also prevents odd result when
			// calling "get()"
			var cookies = document.cookie ? document.cookie.split('; ') : [];
			var rdecode = /(%[0-9A-Z]{2})+/g;
			var i = 0;

			for (; i < cookies.length; i++) {
				var parts = cookies[i].split('=');
				var cookie = parts.slice(1).join('=');

				if (!this.json && cookie.charAt(0) === '"') {
					cookie = cookie.slice(1, -1);
				}

				try {
					var name = parts[0].replace(rdecode, decodeURIComponent);
					cookie = converter.read ?
						converter.read(cookie, name) : converter(cookie, name) ||
						cookie.replace(rdecode, decodeURIComponent);

					if (this.json) {
						try {
							cookie = JSON.parse(cookie);
						} catch (e) {}
					}

					if (key === name) {
						result = cookie;
						break;
					}

					if (!key) {
						result[name] = cookie;
					}
				} catch (e) {}
			}

			return result;
		}

		api.set = api;
		api.get = function (key) {
			return api.call(api, key);
		};
		api.getJSON = function () {
			return api.apply({
				json: true
			}, [].slice.call(arguments));
		};
		api.defaults = {};

		api.remove = function (key, attributes) {
			api(key, '', extend(attributes, {
				expires: -1
			}));
		};

		api.withConverter = init;

		return api;
	}

	return init(function () {});
}));

},{}]},{},[4])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJqcy9ibG9jay9kaWdpdGFsLWNsb2NrLmpzIiwianMvYmxvY2svbm90aWZpY2F0aW9uLWtleWJvYXJkLWhhbmRoZWxkLmpzIiwianMvZWxlbWVudC9mb3JtL3ByZXZlbnRGb3JtUmVzdWJtaXQuanMiLCJqcy9pbmRleC5qcyIsImpzL2xpYi9iYXNldmlldy5qcyIsImpzL2xpYi9iaW5kSGFuZGxlci5qcyIsImpzL2xpYi9kaWFsb2dIYW5kbGVyLmpzIiwianMvbGliL2Vycm9ySGFuZGxlci5qcyIsImpzL2xpYi91dGlscy5qcyIsImpzL3BhZ2UvbWFpbi5qcyIsImpzL3BhZ2UvbmV3aGFzaC5qcyIsImpzL3BhZ2UvcHJvY2Vzcy5qcyIsImpzL3NldHRpbmdzLmpzIiwibm9kZV9tb2R1bGVzL2pzLWNvb2tpZS9zcmMvanMuY29va2llLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7Ozs7QUNDQTs7Ozs7Ozs7OzsrZUFEQTs7O0lBR00sSTs7O0FBRUYsa0JBQWEsT0FBYixFQUFzQjtBQUFBOztBQUFBLGdIQUNaLE9BRFk7O0FBRWxCLGNBQUssaUJBQUwsQ0FBdUIsV0FBdkIsRUFBb0MsYUFBcEM7QUFDQSxnQkFBUSxHQUFSLENBQVkscUJBQVo7QUFDQSxjQUFLLENBQUwsQ0FBTyxLQUFQLENBQWEsTUFBSyxXQUFsQjtBQUprQjtBQUtyQjs7OztvQ0FFWTtBQUNULGdCQUFJLE9BQUssSUFBSSxJQUFKLEVBQVQ7QUFDQSxnQkFBSSxPQUFLLEtBQUssUUFBTCxFQUFUO0FBQ0EsZ0JBQUksU0FBTyxLQUFLLFVBQUwsRUFBWDtBQUNBLGdCQUFJLFNBQU8sS0FBSyxVQUFMLEVBQVg7QUFDQSxnQkFBSSxPQUFLLElBQVQ7QUFDQSxnQkFBSSxTQUFPLENBQVgsRUFBYyxRQUFNLENBQUUsU0FBTyxFQUFSLEdBQWEsSUFBYixHQUFvQixHQUFyQixJQUEwQixNQUFoQyxDQUFkLEtB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QTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24gZSh0LG4scil7ZnVuY3Rpb24gcyhvLHUpe2lmKCFuW29dKXtpZighdFtvXSl7dmFyIGE9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtpZighdSYmYSlyZXR1cm4gYShvLCEwKTtpZihpKXJldHVybiBpKG8sITApO3ZhciBmPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIrbytcIidcIik7dGhyb3cgZi5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGZ9dmFyIGw9bltvXT17ZXhwb3J0czp7fX07dFtvXVswXS5jYWxsKGwuZXhwb3J0cyxmdW5jdGlvbihlKXt2YXIgbj10W29dWzFdW2VdO3JldHVybiBzKG4/bjplKX0sbCxsLmV4cG9ydHMsZSx0LG4scil9cmV0dXJuIG5bb10uZXhwb3J0c312YXIgaT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2Zvcih2YXIgbz0wO288ci5sZW5ndGg7bysrKXMocltvXSk7cmV0dXJuIHN9KSIsIi8qIGdsb2JhbCBzZXRJbnRlcnZhbCAqL1xuaW1wb3J0IEJhc2VWaWV3IGZyb20gJy4uL2xpYi9iYXNldmlldyc7XG5cbmNsYXNzIFZpZXcgZXh0ZW5kcyBCYXNlVmlldyB7XG5cbiAgICBjb25zdHJ1Y3RvciAoZWxlbWVudCkge1xuICAgICAgICBzdXBlcihlbGVtZW50KTtcbiAgICAgICAgdGhpcy5iaW5kUHVibGljTWV0aG9kcygnaW5pdENsb2NrJywgJ3NldEludGVydmFsJyk7XG4gICAgICAgIGNvbnNvbGUubG9nKFwiRm91bmQgZGlnaXRhbCBjbG9ja1wiKTtcbiAgICAgICAgdGhpcy4kLnJlYWR5KHRoaXMuc2V0SW50ZXJ2YWwpO1xuICAgIH1cblxuICAgIGluaXRDbG9jayAoKSB7XG4gICAgICAgIHZhciB0aW1lPW5ldyBEYXRlKCk7XG4gICAgICAgIHZhciBob3VyPXRpbWUuZ2V0SG91cnMoKTtcbiAgICAgICAgdmFyIG1pbnV0ZT10aW1lLmdldE1pbnV0ZXMoKTtcbiAgICAgICAgdmFyIHNlY29uZD10aW1lLmdldFNlY29uZHMoKTtcbiAgICAgICAgdmFyIHRlbXA9aG91cjtcbiAgICAgICAgaWYgKHNlY29uZCUyKSB0ZW1wKz0oKG1pbnV0ZTwxMCk/IFwiOjBcIiA6IFwiOlwiKSttaW51dGU7XG4gICAgICAgIGVsc2UgdGVtcCs9KChtaW51dGU8MTApPyBcIjowXCIgOiBcIiBcIikrbWludXRlO1xuICAgICAgICB0aGlzLiQudGV4dCh0ZW1wKTtcbiAgICB9XG5cbiAgICBzZXRJbnRlcnZhbCAoKSB7XG5cdHNldEludGVydmFsKHRoaXMuaW5pdENsb2NrLCAxMDAwKTtcbiAgICB9XG59XG5cbmV4cG9ydCBkZWZhdWx0IFZpZXc7XG4iLCJcbmltcG9ydCBCYXNlVmlldyBmcm9tICcuLi9saWIvYmFzZXZpZXcnO1xuaW1wb3J0ICQgZnJvbSBcImpxdWVyeVwiO1xuXG5jbGFzcyBWaWV3IGV4dGVuZHMgQmFzZVZpZXcge1xuXG5cbiAgICBjb25zdHJ1Y3RvciAoZWxlbWVudCkge1xuICAgICAgICBzdXBlcihlbGVtZW50KTsgICAgICAgIFxuICAgICAgICB0aGlzLnNvdXJjZSA9IHRoaXMuJC5hdHRyKCdpZCcpOyAgICAgICAgXG4gICAgICAgIGlmICh0aGlzLnNvdXJjZSA9PSBcIndhaXRpbmdOdW1iZXJQYWRcIikge1xuICAgICAgICAgICAgdGhpcy5taW5OdW1iZXJMZW5ndGggPSAxO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgdGhpcy5taW5OdW1iZXJMZW5ndGggPSAxMDtcbiAgICAgICAgfVxuICAgICAgICBcbiAgICAgICAgdGhpcy5iaW5kUHVibGljTWV0aG9kcygnYXBwZW5kTnVtYmVyJywgJ2RlbGV0ZU51bWJlcicsICdjbGVhck51bWJlcnMnLCAnY2hlY2tOdW1iZXInKTtcbiAgICAgICAgY29uc29sZS5sb2coXCJGb3VuZCBrZXlib2FyZC1oYW5kaGVsZFwiKTtcbiAgICAgICAgdGhpcy4kLmZpbmQoJ2J1dHRvbi56aWZmZXInKS5vbignY2xpY2snLCB0aGlzLmFwcGVuZE51bWJlcik7XG4gICAgICAgIHRoaXMuJC5maW5kKCdidXR0b24jcmVtb3ZlbGFzdGRpZ2l0Jykub24oJ2NsaWNrJywgdGhpcy5kZWxldGVOdW1iZXIpO1xuICAgICAgICB0aGlzLiQuZmluZCgnYnV0dG9uI3JlbW92ZWFsbGRpZ2l0c3Bob25lJykub24oJ2NsaWNrJywgdGhpcy5jbGVhck51bWJlcnMpO1xuICAgICAgICB0aGlzLiRudW1iZXJJbnB1dCA9IHRoaXMuJC5maW5kKCcubnVtbWVybmVpbmdhYmUnKTtcbiAgICB9XG5cbiAgICBhcHBlbmROdW1iZXIgKGV2ZW50KSB7XG5cdGxldCAkY29udGVudCA9ICQoZXZlbnQudGFyZ2V0KS5jbG9zZXN0KCdidXR0b24nKTtcbiAgICAgICAgbGV0IG51bWJlciA9ICRjb250ZW50LnRleHQoKTtcbiAgICAgICAgdGhpcy4kbnVtYmVySW5wdXQudmFsKHRoaXMuJG51bWJlcklucHV0LnZhbCgpICsgJycgKyBudW1iZXIpO1xuICAgICAgICB0aGlzLmNoZWNrTnVtYmVyKCk7XG4gICAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9XG5cbiAgICBkZWxldGVOdW1iZXIgKCkge1xuICAgICAgICB0aGlzLiRudW1iZXJJbnB1dC52YWwodGhpcy4kbnVtYmVySW5wdXQudmFsKCkucmVwbGFjZSgvLiQvLCAnJykpO1xuICAgICAgICB0aGlzLmNoZWNrTnVtYmVyKCk7XG4gICAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9XG5cbiAgICBjbGVhck51bWJlcnMgKCkge1xuICAgICAgICB0aGlzLiRudW1iZXJJbnB1dC52YWwoJycpO1xuICAgICAgICB0aGlzLmNoZWNrTnVtYmVyKCk7XG4gICAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9XG5cbiAgICBjaGVja051bWJlciAoKSB7XG4gICAgICAgIC8vY29uc29sZS5sb2codGhpcy4kbnVtYmVySW5wdXQudmFsKCkpO1xuICAgICAgICB2YXIgbnVtYmVyID0gdGhpcy4kbnVtYmVySW5wdXQudmFsKCk7XG4gICAgICAgIGlmICh0aGlzLnNvdXJjZSA9PSAnd2FpdGluZ051bWJlclBhZCcpIHtcbiAgICAgICAgICAgIG51bWJlciA9IG51bWJlci5yZXBsYWNlKC9eMCsvLCAnJyk7XG4gICAgICAgIH0gICAgICAgIFxuICAgICAgICBudW1iZXIgPSBudW1iZXIucmVwbGFjZSgvW15cXGRdL2csICcnKTtcbiAgICAgICAgdmFyICRidXR0b24gPSB0aGlzLiQuZmluZCgnLm5hY2h0cmFnJyk7XG4gICAgICAgIGlmIChudW1iZXIubGVuZ3RoID49IHRoaXMubWluTnVtYmVyTGVuZ3RoKSB7XG4gICAgICAgICAgICAkYnV0dG9uLnJlbW92ZUNsYXNzKCdkaXNhYmxlZCcpLmF0dHIoJ2Rpc2FibGVkJywgZmFsc2UpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgaWYgKCEkYnV0dG9uLmhhc0NsYXNzKCdkaXNhYmxlZCcpKSB7XG4gICAgICAgICAgICAgICAgJGJ1dHRvbi5hZGRDbGFzcygnZGlzYWJsZWQnKS5hdHRyKCdkaXNhYmxlZCcsIHRydWUpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgICAgIHRoaXMuJG51bWJlcklucHV0LnZhbChudW1iZXIpO1xuICAgIH1cblxufVxuXG5leHBvcnQgZGVmYXVsdCBWaWV3O1xuIiwiaW1wb3J0ICQgZnJvbSBcImpxdWVyeVwiO1xuXG5jb25zdCBwcmV2ZW50Rm9ybVJlc3VibWl0ID0gKGVsZW1lbnQpID0+IHtcbiAgICBjb25zdCAkZm9ybSA9ICQoZWxlbWVudClcblxuICAgICRmb3JtLm9uKCdzdWJtaXQnLCAoZXYpID0+IHtcbiAgICAgICAgaWYgKCRmb3JtLmRhdGEoJ3N1Ym1pdHRlZCcpID09PSB0cnVlKSB7XG4gICAgICAgICAgLy8gUHJldmlvdXNseSBzdWJtaXR0ZWQgLSBkb24ndCBzdWJtaXQgYWdhaW5cbiAgICAgICAgICBldi5zdG9wUHJvcGFnYXRpb24oKTtcbiAgICAgICAgICBldi5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgIC8vIE1hcmsgaXQgc28gdGhhdCB0aGUgbmV4dCBzdWJtaXQgY2FuIGJlIGlnbm9yZWRcbiAgICAgICAgICAkZm9ybS5kYXRhKCdzdWJtaXR0ZWQnLCB0cnVlKTtcbiAgICAgICAgfVxuXG4gICAgfSlcbn1cblxuZXhwb3J0IGRlZmF1bHQgcHJldmVudEZvcm1SZXN1Ym1pdFxuIiwiLy8gLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS1cbi8vIFpNUyBUaWNrZXRwcmludGVyIGJlaGF2aW9yXG4vLyAtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLVxuXG5pbXBvcnQgJ2JhYmVsLXBvbHlmaWxsJztcblxuLy8gSW1wb3J0IGJhc2UgbGlic1xuaW1wb3J0IHdpbmRvdyBmcm9tIFwid2luZG93XCI7XG5pbXBvcnQgJCBmcm9tIFwianF1ZXJ5XCI7XG5pbXBvcnQgc2V0dGluZ3MgZnJvbSAnLi9zZXR0aW5ncyc7XG5pbXBvcnQgeyBmb3JjZUh0dHBzIH0gZnJvbSAnLi9saWIvdXRpbHMnXG5cbi8vIEltcG9ydCBWaWV3c1xuaW1wb3J0IFJlbG9hZCBmcm9tIFwiLi9wYWdlL21haW5cIjtcbmltcG9ydCBHZXRIYXNoIGZyb20gXCIuL3BhZ2UvbmV3aGFzaFwiO1xuaW1wb3J0IFByaW50RGlhbG9nIGZyb20gXCIuL3BhZ2UvcHJvY2Vzc1wiO1xuaW1wb3J0IERpZ2l0YWxUaW1lIGZyb20gXCIuL2Jsb2NrL2RpZ2l0YWwtY2xvY2tcIjtcbmltcG9ydCBOb3RpZmljYXRpb25LZXlib2FyZEhhbmRoZWxkVmlldyBmcm9tIFwiLi9ibG9jay9ub3RpZmljYXRpb24ta2V5Ym9hcmQtaGFuZGhlbGRcIjtcbmltcG9ydCBwcmV2ZW50Rm9ybVJlc3VibWl0IGZyb20gJy4vZWxlbWVudC9mb3JtL3ByZXZlbnRGb3JtUmVzdWJtaXQnXG5cbi8vIEJpbmQgalF1ZXJ5IG9uICQgZm9yIHRlc3RpbmdcbndpbmRvdy4kID0gJDtcbndpbmRvdy5ibyA9IHtcbiAgICBcInptc3RpY2tldHByaW50ZXJcIjogc2V0dGluZ3Ncbn07XG5cbi8vIEluaXQgVmlld3NcbiQoJyNuZXdoYXNoJykuZWFjaChmdW5jdGlvbigpIHsgbmV3IEdldEhhc2godGhpcyk7fSk7XG4kKCcjaW5kZXgsICNtZXNzYWdlLCAjZXhjZXB0aW9uJykuZWFjaChmdW5jdGlvbigpIHsgbmV3IFJlbG9hZCh0aGlzKTt9KTtcbiQoJyNwcm9jZXNzJykuZWFjaChmdW5jdGlvbigpIHsgbmV3IFByaW50RGlhbG9nKHRoaXMpO30pO1xuJCgnLmRpZ2l0YWx1aHInKS5lYWNoKGZ1bmN0aW9uKCkgeyBuZXcgRGlnaXRhbFRpbWUodGhpcyk7fSk7XG4kKCcuc21zYm94JykuZWFjaChmdW5jdGlvbigpIHsgbmV3IE5vdGlmaWNhdGlvbktleWJvYXJkSGFuZGhlbGRWaWV3KHRoaXMpO30pO1xuXG4vLyBwcmV2ZW50IHJlc3VibWl0c1xuJCgnZm9ybScpLmVhY2goZnVuY3Rpb24oKSB7XG4gICAgcHJldmVudEZvcm1SZXN1Ym1pdCh0aGlzKTtcbn0pXG5cbi8vIFNheSBoZWxsb1xuY29uc29sZS5sb2coXCJXZWxjb21lIHRvIHRoZSBaTVMgVGlja2V0cHJpbnRlciBpbnRlcmZhY2UuLi5cIik7XG5cbi8vIEZvcmNlIGh0dHBzIHByb3RvY29sXG5mb3JjZUh0dHBzKCk7XG4iLCJpbXBvcnQgJCBmcm9tIFwianF1ZXJ5XCI7XG5pbXBvcnQgRXJyb3JIYW5kbGVyIGZyb20gJy4vZXJyb3JIYW5kbGVyJztcbmltcG9ydCBEaWFsb2dIYW5kbGVyIGZyb20gJy4vZGlhbG9nSGFuZGxlcic7XG5pbXBvcnQgeyBsaWdodGJveCB9IGZyb20gJy4vdXRpbHMnO1xuXG5jbGFzcyBCYXNlVmlldyBleHRlbmRzIEVycm9ySGFuZGxlciB7XG5cbiAgICBjb25zdHJ1Y3RvcihlbGVtZW50KSB7XG4gICAgICAgIHN1cGVyKGVsZW1lbnQpO1xuICAgICAgICB0aGlzLiRtYWluID0gJChlbGVtZW50KTtcbiAgICB9XG5cbiAgICBnZXQgJCAoKSB7XG4gICAgICAgIHJldHVybiB0aGlzLiRtYWluO1xuICAgIH1cblxuICAgIHN0YXRpYyBsb2FkQ2FsbFN0YXRpYyh1cmwsIG1ldGhvZCA9ICdHRVQnLCBkYXRhID0gbnVsbCkge1xuICAgICAgICBjb25zdCBhamF4U2V0dGluZ3MgPSB7XG4gICAgICAgICAgICBtZXRob2RcbiAgICAgICAgfTtcbiAgICAgICAgaWYgKG1ldGhvZCA9PT0gJ1BPU1QnIHx8IG1ldGhvZCA9PT0gJ1BVVCcpIHtcbiAgICAgICAgICAgIGFqYXhTZXR0aW5ncy5kYXRhID0gZGF0YTtcbiAgICAgICAgfVxuICAgICAgICByZXR1cm4gbmV3IFByb21pc2UoKHJlc29sdmUsIHJlamVjdCkgPT4ge1xuICAgICAgICAgICAgJC5hamF4KHVybCwgYWpheFNldHRpbmdzKS5kb25lKHJlc3BvbnNlRGF0YSA9PiB7XG4gICAgICAgICAgICAgICAgcmVzb2x2ZShyZXNwb25zZURhdGEpO1xuICAgICAgICAgICAgfSkuZmFpbChlcnIgPT4ge1xuICAgICAgICAgICAgICAgIGNvbnNvbGUubG9nKCdYSFIgbG9hZCBlcnJvcicsIHVybCwgZXJyKTtcbiAgICAgICAgICAgICAgICByZWplY3QoZXJyKTtcbiAgICAgICAgICAgIH0pXG4gICAgICAgIH0pO1xuICAgIH1cblxuICAgIHN0YXRpYyBsb2FkRGlhbG9nU3RhdGljIChyZXNwb25zZSwgY2FsbGJhY2spIHtcbiAgICAgICAgY29uc3QgeyBsaWdodGJveENvbnRlbnRFbGVtZW50LCBkZXN0cm95TGlnaHRib3ggfSA9IGxpZ2h0Ym94KHRoaXMuJG1haW4sICgpID0+IHtcbiAgICAgICAgICAgIGRlc3Ryb3lMaWdodGJveCgpLFxuICAgICAgICAgICAgY2FsbGJhY2soKVxuICAgICAgICB9KTtcbiAgICAgICAgbmV3IERpYWxvZ0hhbmRsZXIobGlnaHRib3hDb250ZW50RWxlbWVudCwge1xuICAgICAgICAgICAgcmVzcG9uc2U6IHJlc3BvbnNlLFxuICAgICAgICAgICAgY2FsbGJhY2s6ICgpID0+IHtcbiAgICAgICAgICAgICAgICBjYWxsYmFjaygpO1xuICAgICAgICAgICAgICAgIGRlc3Ryb3lMaWdodGJveCgpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KVxuICAgIH1cblxufVxuXG5leHBvcnQgZGVmYXVsdCBCYXNlVmlldztcbiIsIlxuXG5jbGFzcyBCaW5kSGFuZGxlciB7XG5cbiAgICBiaW5kUHVibGljTWV0aG9kcyAoLi4ubWV0aG9kcykge1xuICAgICAgICBsZXQgb2JqZWN0ID0gdGhpcztcbiAgICAgICAgbWV0aG9kcy5mb3JFYWNoKCBmdW5jdGlvbiAobWV0aG9kKSB7XG4gICAgICAgICAgICBpZiAodHlwZW9mIG9iamVjdFttZXRob2RdICE9PSAnZnVuY3Rpb24nKSB7XG4gICAgICAgICAgICAgICAgdGhyb3cgXCJNZXRob2Qgbm90IGZvdW5kOiBcIiArIG1ldGhvZDtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIG9iamVjdFttZXRob2RdID0gb2JqZWN0W21ldGhvZF0uYmluZChvYmplY3QpO1xuICAgICAgICB9KTtcbiAgICB9XG59XG5cbmV4cG9ydCBkZWZhdWx0IEJpbmRIYW5kbGVyO1xuIiwiaW1wb3J0ICQgZnJvbSAnanF1ZXJ5JztcblxuY2xhc3MgRGlhbG9nSGFuZGxlciB7XG5cbiAgICBjb25zdHJ1Y3RvciAoZWxlbWVudCwgb3B0aW9ucykge1xuICAgICAgICB0aGlzLiRtYWluID0gJChlbGVtZW50KTtcbiAgICAgICAgdGhpcy5yZXNwb25zZSA9IG9wdGlvbnMucmVzcG9uc2U7XG4gICAgICAgIHRoaXMuY2FsbGJhY2sgPSBvcHRpb25zLmNhbGxiYWNrIHx8ICgoKSA9PiB7fSk7XG4gICAgICAgIHRoaXMucGFyZW50ID0gb3B0aW9ucy5wYXJlbnQ7XG4gICAgICAgIHRoaXMuaGFuZGxlTGlnaHRib3ggPSBvcHRpb25zLmhhbmRsZUxpZ2h0Ym94IHx8ICgoKSA9PiB7fSk7XG4gICAgICAgIHRoaXMuYmluZEV2ZW50cygpO1xuICAgICAgICB0aGlzLnJlbmRlcigpO1xuICAgIH1cblxuICAgIHJlbmRlcigpIHtcbiAgICAgICAgdmFyIGNvbnRlbnQgPSAkKHRoaXMucmVzcG9uc2UpLmZpbHRlcignZGl2LmRpYWxvZycpO1xuICAgICAgICBpZiAoY29udGVudC5sZW5ndGggPT0gMCkge1xuICAgICAgICAgICAgdmFyIG1lc3NhZ2UgPSAkKHRoaXMucmVzcG9uc2UpLmZpbmQoJ2Rpdi5kaWFsb2cnKTtcbiAgICAgICAgICAgIGlmIChtZXNzYWdlLmxlbmd0aCA+IDApIHtcbiAgICAgICAgICAgICAgICBjb250ZW50ID0gbWVzc2FnZS5nZXQoMCkub3V0ZXJIVE1MO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgICAgIHRoaXMuJG1haW4uaHRtbChjb250ZW50KTtcbiAgICB9XG5cbiAgICBiaW5kRXZlbnRzKCkge1xuICAgICAgICB0aGlzLiRtYWluLm9mZigpLm9uKCdjbGljaycsICcuYnV0dG9uLW9rJywgKGV2KSA9PiB7XG4gICAgICAgICAgICBldi5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICAgICAgZXYuc3RvcFByb3BhZ2F0aW9uKCk7XG4gICAgICAgICAgICB0aGlzLmNhbGxiYWNrKGV2KTtcbiAgICAgICAgfSkub24oJ2NsaWNrJywgJy5idXR0b24tYWJvcnQnLCAoZXYpID0+IHtcbiAgICAgICAgICAgIGV2LnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgICAgICBldi5zdG9wUHJvcGFnYXRpb24oKTtcbiAgICAgICAgICAgIHRoaXMuaGFuZGxlTGlnaHRib3goKTtcbiAgICAgICAgfSkub24oJ2NsaWNrJywgJy5idXR0b24tY2FsbGJhY2snLCAoZXYpID0+IHtcbiAgICAgICAgICAgIGV2LnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgICAgICBldi5zdG9wUHJvcGFnYXRpb24oKTtcbiAgICAgICAgICAgIHZhciBjYWxsYmFjayA9ICQoZXYudGFyZ2V0KS5kYXRhKCdjYWxsYmFjaycpO1xuICAgICAgICAgICAgdGhpcy5jYWxsYmFjayA9IHRoaXMucGFyZW50W2NhbGxiYWNrXTtcbiAgICAgICAgICAgIHRoaXMuY2FsbGJhY2soZXYpO1xuICAgICAgICB9KTtcbiAgICB9XG59XG5cbmV4cG9ydCBkZWZhdWx0IERpYWxvZ0hhbmRsZXJcbiIsIlxuaW1wb3J0IEJpbmRIYW5kbGVyIGZyb20gXCIuL2JpbmRIYW5kbGVyXCI7XG5cbmNsYXNzIEVycm9ySGFuZGxlciBleHRlbmRzIEJpbmRIYW5kbGVyIHtcbiAgICBjb25zdHJ1Y3RvcigpIHtcbiAgICAgICAgc3VwZXIoKTtcbiAgICAgICAgdGhpcy5fZXJyb3JIYW5kbGVyID0gZnVuY3Rpb24oKSB7fTtcbiAgICB9XG5cbiAgICBnZXQgZXJyb3JIYW5kbGVyICgpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuX2Vycm9ySGFuZGxlcjtcbiAgICB9XG4gICAgc2V0IGVycm9ySGFuZGxlciAoY2FsbGJhY2spIHtcbiAgICAgICAgdGhpcy5fZXJyb3JIYW5kbGVyID0gY2FsbGJhY2s7XG4gICAgfVxufVxuXG5leHBvcnQgZGVmYXVsdCBFcnJvckhhbmRsZXI7XG4iLCJpbXBvcnQgJCBmcm9tICdqcXVlcnknXG5pbXBvcnQgQmFzZXZpZXcgZnJvbSAnLi9iYXNldmlldyc7XG5pbXBvcnQgc2V0dGluZ3MgZnJvbSAnLi4vc2V0dGluZ3MnO1xuXG5jb25zdCBsaWdodGJveEh0bWwgPSAnPGRpdiBjbGFzcz1cImxpZ2h0Ym94XCI+PGRpdiBjbGFzcz1cImxpZ2h0Ym94X19jb250ZW50XCI+PC9kaXY+PC9kaXY+J1xuXG5leHBvcnQgY29uc3QgbGlnaHRib3ggPSAocGFyZW50RWxlbWVudCwgb25CYWNrZ3JvdW5kQ2xpY2spID0+IHtcbiAgICBjb25zdCBsaWdodGJveEVsZW1lbnQgPSAkKGxpZ2h0Ym94SHRtbClcblxuICAgIGlmICghcGFyZW50RWxlbWVudCkge1xuICAgICAgICBwYXJlbnRFbGVtZW50ID0gJCgnYm9keScpXG4gICAgICAgIGxpZ2h0Ym94RWxlbWVudC5hZGRDbGFzcygnZml4ZWQnKVxuICAgIH1cblxuICAgIGNvbnN0IGRlc3Ryb3lMaWdodGJveCA9ICgpID0+IHtcbiAgICAgICAgbGlnaHRib3hFbGVtZW50Lm9mZigpXG4gICAgICAgIGxpZ2h0Ym94RWxlbWVudC5yZW1vdmUoKVxuICAgIH1cblxuICAgIGNvbnN0IGxpZ2h0Ym94Q29udGVudEVsZW1lbnQgPSBsaWdodGJveEVsZW1lbnQuZmluZCgnLmxpZ2h0Ym94X19jb250ZW50Jyk7XG5cbiAgICBsaWdodGJveEVsZW1lbnQub24oJ2NsaWNrJywgKGV2KSA9PiB7XG4gICAgICAgIC8vY29uc29sZS5sb2coJ2JhY2tncm91bmQgY2xpY2snLCBldik7XG4gICAgICAgIGV2LnN0b3BQcm9wYWdhdGlvbigpXG4gICAgICAgIGV2LnByZXZlbnREZWZhdWx0KClcbiAgICAgICAgZGVzdHJveUxpZ2h0Ym94KClcbiAgICAgICAgb25CYWNrZ3JvdW5kQ2xpY2soKVxuICAgIH0pLm9uKCdjbGljaycsICcubGlnaHRib3hfX2NvbnRlbnQnLCAoZXYpID0+IHtcbiAgICAgICAgZXYuc3RvcFByb3BhZ2F0aW9uKCk7XG4gICAgfSlcblxuXG4gICAgaWYgKCQocGFyZW50RWxlbWVudCkuZmluZCgnLmxpZ2h0Ym94JykubGVuZ3RoKSB7XG4gICAgICAgICQocGFyZW50RWxlbWVudCkuZmluZCgnLmxpZ2h0Ym94JykucmVtb3ZlKCk7XG4gICAgfVxuICAgICQocGFyZW50RWxlbWVudCkuYXBwZW5kKGxpZ2h0Ym94RWxlbWVudClcblxuICAgIHJldHVybiB7XG4gICAgICAgIGxpZ2h0Ym94Q29udGVudEVsZW1lbnQsXG4gICAgICAgIGRlc3Ryb3lMaWdodGJveFxuICAgIH1cbn1cblxuZXhwb3J0IGNvbnN0IGZvcmNlSHR0cHMgPSAoKSA9PiB7XG4gICAgaWYgKGRvY3VtZW50LmxvY2F0aW9uLnByb3RvY29sICE9PSBcImh0dHBzOlwiKSB7XG4gICAgICAgIEJhc2V2aWV3LmxvYWRDYWxsU3RhdGljKGAke3NldHRpbmdzLmluY2x1ZGVVcmx9L2RpYWxvZy8/dGVtcGxhdGU9Zm9yY2VfaHR0cHNgKS50aGVuKChyZXNwb25zZSkgPT4ge1xuICAgICAgICAgICAgQmFzZXZpZXcubG9hZERpYWxvZ1N0YXRpYyhyZXNwb25zZSwgKCkgPT4ge1xuICAgICAgICAgICAgICAgIGRvY3VtZW50LmxvY2F0aW9uLmhyZWYgPSBcImh0dHBzOi8vXCIgKyBkb2N1bWVudC5sb2NhdGlvbi5ocmVmLnN1YnN0cmluZyhkb2N1bWVudC5sb2NhdGlvbi5wcm90b2NvbC5sZW5ndGgsIGRvY3VtZW50LmxvY2F0aW9uLmhyZWYubGVuZ3RoKTtcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9KTtcbiAgICB9XG5cbn1cbiIsIi8qIGdsb2JhbCBzZXRJbnRlcnZhbCAqL1xuaW1wb3J0IEJhc2VWaWV3IGZyb20gJy4uL2xpYi9iYXNldmlldyc7XG5pbXBvcnQgd2luZG93IGZyb20gXCJ3aW5kb3dcIjtcblxuY2xhc3MgVmlldyBleHRlbmRzIEJhc2VWaWV3IHtcblxuICAgIGNvbnN0cnVjdG9yIChlbGVtZW50KSB7XG4gICAgICAgIHN1cGVyKGVsZW1lbnQpO1xuICAgICAgICB0aGlzLmJpbmRQdWJsaWNNZXRob2RzKCdzZXRJbnRlcnZhbCcsICdyZWxvYWRQYWdlJyk7XG4gICAgICAgIGNvbnNvbGUubG9nKCdSZWRpcmVjdCB0byBob21lIHVybCBldmVyeSAzMCBzZWNvbmRzJyk7XG4gICAgICAgIHRoaXMuJC5yZWFkeSh0aGlzLnNldEludGVydmFsKTtcbiAgICB9XG5cbiAgICByZWxvYWRQYWdlICgpIHtcbiAgICAgICAgd2luZG93LmxvY2F0aW9uLmhyZWYgPSB0aGlzLmdldFVybCgnL2hvbWUvJyk7XG4gICAgfVxuXG4gICAgc2V0SW50ZXJ2YWwgKCkge1xuICAgICAgICB2YXIgcmVsb2FkVGltZSA9IHdpbmRvdy5iby56bXN0aWNrZXRwcmludGVyLnJlbG9hZEludGVydmFsO1xuICAgICAgICBzZXRJbnRlcnZhbCh0aGlzLnJlbG9hZFBhZ2UsIHJlbG9hZFRpbWUgKiAxMDAwKTtcbiAgICB9XG5cbiAgICBnZXRVcmwgKHJlbGF0aXZlUGF0aCkge1xuICAgICAgICBsZXQgaW5jbHVkZXBhdGggPSB3aW5kb3cuYm8uem1zdGlja2V0cHJpbnRlci5pbmNsdWRlcGF0aDtcbiAgICAgICAgcmV0dXJuIGluY2x1ZGVwYXRoICsgcmVsYXRpdmVQYXRoO1xuICAgIH1cbn1cblxuZXhwb3J0IGRlZmF1bHQgVmlldztcbiIsIi8qIGdsb2JhbCBzZXRUaW1lb3V0ICovXG5pbXBvcnQgQmFzZVZpZXcgZnJvbSAnLi4vbGliL2Jhc2V2aWV3JztcbmltcG9ydCB3aW5kb3cgZnJvbSBcIndpbmRvd1wiO1xuaW1wb3J0IGNvb2tpZSBmcm9tIFwianMtY29va2llXCI7XG5pbXBvcnQgJCBmcm9tIFwianF1ZXJ5XCI7XG5cbmNsYXNzIFZpZXcgZXh0ZW5kcyBCYXNlVmlldyB7XG5cbiAgICBjb25zdHJ1Y3RvciAoZWxlbWVudCkge1xuICAgICAgICBzdXBlcihlbGVtZW50KTtcbiAgICAgICAgdGhpcy5iaW5kUHVibGljTWV0aG9kcygnbG9hZCcsJ3NldFRpbWVvdXQnLCdnZXRVcmwnLCdyZWxvYWRQYWdlJyk7XG4gICAgICAgICQod2luZG93KS5vbignbG9hZCcsIHRoaXMubG9hZCk7XG4gICAgfVxuXG4gICAgbG9hZCAoKSB7XG4gICAgICAgIGNvb2tpZS5yZW1vdmUoXCJUaWNrZXRwcmludGVyXCIsIHsgc2VjdXJlOiB0cnVlIH0pO1xuICAgICAgICB0aGlzLnNldFRpbWVvdXQoKTtcbiAgICB9XG5cbiAgICByZWxvYWRQYWdlICgpIHtcbiAgICAgICAgd2luZG93LmxvY2F0aW9uLmhyZWYgPSB0aGlzLmdldFVybCgnL2hvbWUvJyk7XG4gICAgfVxuXG4gICAgc2V0VGltZW91dCAoKSB7XG4gICAgICAgIHNldFRpbWVvdXQodGhpcy5yZWxvYWRQYWdlLCA1MDAwKTtcbiAgICB9XG5cbiAgICBnZXRVcmwgKHJlbGF0aXZlUGF0aCkge1xuICAgICAgICBsZXQgaW5jbHVkZXBhdGggPSB3aW5kb3cuYm8uem1zdGlja2V0cHJpbnRlci5pbmNsdWRlcGF0aDtcbiAgICAgICAgcmV0dXJuIGluY2x1ZGVwYXRoICsgcmVsYXRpdmVQYXRoO1xuICAgIH1cbn1cblxuZXhwb3J0IGRlZmF1bHQgVmlldztcbiIsIi8qIGdsb2JhbCBzZXRUaW1lb3V0ICovXG5pbXBvcnQgQmFzZVZpZXcgZnJvbSAnLi4vbGliL2Jhc2V2aWV3JztcbmltcG9ydCB3aW5kb3cgZnJvbSBcIndpbmRvd1wiO1xuXG5jbGFzcyBWaWV3IGV4dGVuZHMgQmFzZVZpZXcge1xuXG4gICAgY29uc3RydWN0b3IgKGVsZW1lbnQpIHtcbiAgICAgICAgc3VwZXIoZWxlbWVudCk7XG4gICAgICAgIHRoaXMuYmluZFB1YmxpY01ldGhvZHMoJ3ByaW50RGlhbG9nJywgJ3JlbG9hZCcpO1xuICAgICAgICBjb25zb2xlLmxvZygnUHJpbnQgZGF0YSBhbmQgcmVkaXJlY3QgdG8gaG9tZSB1cmwgYWZ0ZXIgcHJlc2V0dGVkIHRpbWUnKTtcbiAgICAgICAgdGhpcy4kLnJlYWR5KHRoaXMucHJpbnREaWFsb2cpO1xuICAgIH1cblxuICAgIHJlbG9hZCAoKSB7XG4gICAgICAgIHdpbmRvdy5sb2NhdGlvbi5ocmVmID0gdGhpcy5nZXRVcmwoJy9ob21lLycpO1xuICAgIH1cblxuICAgIGdldFVybCAocmVsYXRpdmVQYXRoKSB7XG4gICAgICAgIGxldCBpbmNsdWRlcGF0aCA9IHdpbmRvdy5iby56bXN0aWNrZXRwcmludGVyLmluY2x1ZGVwYXRoO1xuICAgICAgICByZXR1cm4gaW5jbHVkZXBhdGggKyByZWxhdGl2ZVBhdGg7XG4gICAgfVxuXG4gICAgcHJpbnREaWFsb2cgKCkge1xuICAgICAgICBkb2N1bWVudC50aXRsZSA9IFwiQW5tZWxkdW5nIGFuIFdhcnRlc2NobGFuZ2VcIjtcbiAgICAgICAgd2luZG93LnByaW50KCk7XG5cbiAgICAgICAgdmFyIGJlZm9yZVByaW50ID0gKCkgPT4ge1xuICAgICAgICAgICAgY29uc29sZS5sb2coJ3N0YXJ0IHByaW50aW5nJyk7XG4gICAgICAgIH07XG4gICAgICAgIHZhciBhZnRlclByaW50ID0gKCkgPT4ge1xuICAgICAgICAgICAgbGV0IHJlbG9hZFRpbWUgPSB3aW5kb3cuYm8uem1zdGlja2V0cHJpbnRlci5yZWxvYWRJbnRlcnZhbDtcbiAgICAgICAgICAgIHNldFRpbWVvdXQoKCkgPT4ge1xuICAgICAgICAgICAgICAgIHRoaXMucmVsb2FkKCk7XG4gICAgICAgICAgICB9LCByZWxvYWRUaW1lICogMTAwMCk7IC8vIGRlZmF1bHQgaXMgMzBcbiAgICAgICAgfTtcblxuICAgICAgICBpZiAod2luZG93Lm1hdGNoTWVkaWEpIHtcbiAgICAgICAgICAgIHZhciBtZWRpYVF1ZXJ5TGlzdCA9IHdpbmRvdy5tYXRjaE1lZGlhKCdwcmludCcpO1xuICAgICAgICAgICAgbWVkaWFRdWVyeUxpc3QuYWRkTGlzdGVuZXIoZnVuY3Rpb24obXFsKSB7XG4gICAgICAgICAgICAgICAgaWYgKG1xbC5tYXRjaGVzKSB7XG4gICAgICAgICAgICAgICAgICAgIGJlZm9yZVByaW50KCk7XG4gICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgYWZ0ZXJQcmludCgpO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG5cbiAgICAgICAgd2luZG93Lm9uYmVmb3JlcHJpbnQgPSBiZWZvcmVQcmludDtcbiAgICAgICAgd2luZG93Lm9uYWZ0ZXJwcmludCA9IGFmdGVyUHJpbnQ7XG4gICAgfVxufVxuXG5leHBvcnQgZGVmYXVsdCBWaWV3O1xuIiwiZXhwb3J0IGRlZmF1bHQge1xuICAgICdyZWxvYWRJbnRlcnZhbCc6IDMwLFxuICAgICdhbmltYXRpb25FbmQnOiAnd2Via2l0QW5pbWF0aW9uRW5kIG1vekFuaW1hdGlvbkVuZCBNU0FuaW1hdGlvbkVuZCBvYW5pbWF0aW9uZW5kIGFuaW1hdGlvbmVuZCcsXG4gICAgJ2luY2x1ZGVVcmwnOiAnL3Rlcm1pbnZlcmVpbmJhcnVuZy90aWNrZXRwcmludGVyJ1xufTtcbiIsIi8qIVxuICogSmF2YVNjcmlwdCBDb29raWUgdjIuMi4wXG4gKiBodHRwczovL2dpdGh1Yi5jb20vanMtY29va2llL2pzLWNvb2tpZVxuICpcbiAqIENvcHlyaWdodCAyMDA2LCAyMDE1IEtsYXVzIEhhcnRsICYgRmFnbmVyIEJyYWNrXG4gKiBSZWxlYXNlZCB1bmRlciB0aGUgTUlUIGxpY2Vuc2VcbiAqL1xuOyhmdW5jdGlvbiAoZmFjdG9yeSkge1xuXHR2YXIgcmVnaXN0ZXJlZEluTW9kdWxlTG9hZGVyID0gZmFsc2U7XG5cdGlmICh0eXBlb2YgZGVmaW5lID09PSAnZnVuY3Rpb24nICYmIGRlZmluZS5hbWQpIHtcblx0XHRkZWZpbmUoZmFjdG9yeSk7XG5cdFx0cmVnaXN0ZXJlZEluTW9kdWxlTG9hZGVyID0gdHJ1ZTtcblx0fVxuXHRpZiAodHlwZW9mIGV4cG9ydHMgPT09ICdvYmplY3QnKSB7XG5cdFx0bW9kdWxlLmV4cG9ydHMgPSBmYWN0b3J5KCk7XG5cdFx0cmVnaXN0ZXJlZEluTW9kdWxlTG9hZGVyID0gdHJ1ZTtcblx0fVxuXHRpZiAoIXJlZ2lzdGVyZWRJbk1vZHVsZUxvYWRlcikge1xuXHRcdHZhciBPbGRDb29raWVzID0gd2luZG93LkNvb2tpZXM7XG5cdFx0dmFyIGFwaSA9IHdpbmRvdy5Db29raWVzID0gZmFjdG9yeSgpO1xuXHRcdGFwaS5ub0NvbmZsaWN0ID0gZnVuY3Rpb24gKCkge1xuXHRcdFx0d2luZG93LkNvb2tpZXMgPSBPbGRDb29raWVzO1xuXHRcdFx0cmV0dXJuIGFwaTtcblx0XHR9O1xuXHR9XG59KGZ1bmN0aW9uICgpIHtcblx0ZnVuY3Rpb24gZXh0ZW5kICgpIHtcblx0XHR2YXIgaSA9IDA7XG5cdFx0dmFyIHJlc3VsdCA9IHt9O1xuXHRcdGZvciAoOyBpIDwgYXJndW1lbnRzLmxlbmd0aDsgaSsrKSB7XG5cdFx0XHR2YXIgYXR0cmlidXRlcyA9IGFyZ3VtZW50c1sgaSBdO1xuXHRcdFx0Zm9yICh2YXIga2V5IGluIGF0dHJpYnV0ZXMpIHtcblx0XHRcdFx0cmVzdWx0W2tleV0gPSBhdHRyaWJ1dGVzW2tleV07XG5cdFx0XHR9XG5cdFx0fVxuXHRcdHJldHVybiByZXN1bHQ7XG5cdH1cblxuXHRmdW5jdGlvbiBpbml0IChjb252ZXJ0ZXIpIHtcblx0XHRmdW5jdGlvbiBhcGkgKGtleSwgdmFsdWUsIGF0dHJpYnV0ZXMpIHtcblx0XHRcdHZhciByZXN1bHQ7XG5cdFx0XHRpZiAodHlwZW9mIGRvY3VtZW50ID09PSAndW5kZWZpbmVkJykge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdC8vIFdyaXRlXG5cblx0XHRcdGlmIChhcmd1bWVudHMubGVuZ3RoID4gMSkge1xuXHRcdFx0XHRhdHRyaWJ1dGVzID0gZXh0ZW5kKHtcblx0XHRcdFx0XHRwYXRoOiAnLydcblx0XHRcdFx0fSwgYXBpLmRlZmF1bHRzLCBhdHRyaWJ1dGVzKTtcblxuXHRcdFx0XHRpZiAodHlwZW9mIGF0dHJpYnV0ZXMuZXhwaXJlcyA9PT0gJ251bWJlcicpIHtcblx0XHRcdFx0XHR2YXIgZXhwaXJlcyA9IG5ldyBEYXRlKCk7XG5cdFx0XHRcdFx0ZXhwaXJlcy5zZXRNaWxsaXNlY29uZHMoZXhwaXJlcy5nZXRNaWxsaXNlY29uZHMoKSArIGF0dHJpYnV0ZXMuZXhwaXJlcyAqIDg2NGUrNSk7XG5cdFx0XHRcdFx0YXR0cmlidXRlcy5leHBpcmVzID0gZXhwaXJlcztcblx0XHRcdFx0fVxuXG5cdFx0XHRcdC8vIFdlJ3JlIHVzaW5nIFwiZXhwaXJlc1wiIGJlY2F1c2UgXCJtYXgtYWdlXCIgaXMgbm90IHN1cHBvcnRlZCBieSBJRVxuXHRcdFx0XHRhdHRyaWJ1dGVzLmV4cGlyZXMgPSBhdHRyaWJ1dGVzLmV4cGlyZXMgPyBhdHRyaWJ1dGVzLmV4cGlyZXMudG9VVENTdHJpbmcoKSA6ICcnO1xuXG5cdFx0XHRcdHRyeSB7XG5cdFx0XHRcdFx0cmVzdWx0ID0gSlNPTi5zdHJpbmdpZnkodmFsdWUpO1xuXHRcdFx0XHRcdGlmICgvXltcXHtcXFtdLy50ZXN0KHJlc3VsdCkpIHtcblx0XHRcdFx0XHRcdHZhbHVlID0gcmVzdWx0O1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0fSBjYXRjaCAoZSkge31cblxuXHRcdFx0XHRpZiAoIWNvbnZlcnRlci53cml0ZSkge1xuXHRcdFx0XHRcdHZhbHVlID0gZW5jb2RlVVJJQ29tcG9uZW50KFN0cmluZyh2YWx1ZSkpXG5cdFx0XHRcdFx0XHQucmVwbGFjZSgvJSgyM3wyNHwyNnwyQnwzQXwzQ3wzRXwzRHwyRnwzRnw0MHw1Qnw1RHw1RXw2MHw3Qnw3RHw3QykvZywgZGVjb2RlVVJJQ29tcG9uZW50KTtcblx0XHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0XHR2YWx1ZSA9IGNvbnZlcnRlci53cml0ZSh2YWx1ZSwga2V5KTtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdGtleSA9IGVuY29kZVVSSUNvbXBvbmVudChTdHJpbmcoa2V5KSk7XG5cdFx0XHRcdGtleSA9IGtleS5yZXBsYWNlKC8lKDIzfDI0fDI2fDJCfDVFfDYwfDdDKS9nLCBkZWNvZGVVUklDb21wb25lbnQpO1xuXHRcdFx0XHRrZXkgPSBrZXkucmVwbGFjZSgvW1xcKFxcKV0vZywgZXNjYXBlKTtcblxuXHRcdFx0XHR2YXIgc3RyaW5naWZpZWRBdHRyaWJ1dGVzID0gJyc7XG5cblx0XHRcdFx0Zm9yICh2YXIgYXR0cmlidXRlTmFtZSBpbiBhdHRyaWJ1dGVzKSB7XG5cdFx0XHRcdFx0aWYgKCFhdHRyaWJ1dGVzW2F0dHJpYnV0ZU5hbWVdKSB7XG5cdFx0XHRcdFx0XHRjb250aW51ZTtcblx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0c3RyaW5naWZpZWRBdHRyaWJ1dGVzICs9ICc7ICcgKyBhdHRyaWJ1dGVOYW1lO1xuXHRcdFx0XHRcdGlmIChhdHRyaWJ1dGVzW2F0dHJpYnV0ZU5hbWVdID09PSB0cnVlKSB7XG5cdFx0XHRcdFx0XHRjb250aW51ZTtcblx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0c3RyaW5naWZpZWRBdHRyaWJ1dGVzICs9ICc9JyArIGF0dHJpYnV0ZXNbYXR0cmlidXRlTmFtZV07XG5cdFx0XHRcdH1cblx0XHRcdFx0cmV0dXJuIChkb2N1bWVudC5jb29raWUgPSBrZXkgKyAnPScgKyB2YWx1ZSArIHN0cmluZ2lmaWVkQXR0cmlidXRlcyk7XG5cdFx0XHR9XG5cblx0XHRcdC8vIFJlYWRcblxuXHRcdFx0aWYgKCFrZXkpIHtcblx0XHRcdFx0cmVzdWx0ID0ge307XG5cdFx0XHR9XG5cblx0XHRcdC8vIFRvIHByZXZlbnQgdGhlIGZvciBsb29wIGluIHRoZSBmaXJzdCBwbGFjZSBhc3NpZ24gYW4gZW1wdHkgYXJyYXlcblx0XHRcdC8vIGluIGNhc2UgdGhlcmUgYXJlIG5vIGNvb2tpZXMgYXQgYWxsLiBBbHNvIHByZXZlbnRzIG9kZCByZXN1bHQgd2hlblxuXHRcdFx0Ly8gY2FsbGluZyBcImdldCgpXCJcblx0XHRcdHZhciBjb29raWVzID0gZG9jdW1lbnQuY29va2llID8gZG9jdW1lbnQuY29va2llLnNwbGl0KCc7ICcpIDogW107XG5cdFx0XHR2YXIgcmRlY29kZSA9IC8oJVswLTlBLVpdezJ9KSsvZztcblx0XHRcdHZhciBpID0gMDtcblxuXHRcdFx0Zm9yICg7IGkgPCBjb29raWVzLmxlbmd0aDsgaSsrKSB7XG5cdFx0XHRcdHZhciBwYXJ0cyA9IGNvb2tpZXNbaV0uc3BsaXQoJz0nKTtcblx0XHRcdFx0dmFyIGNvb2tpZSA9IHBhcnRzLnNsaWNlKDEpLmpvaW4oJz0nKTtcblxuXHRcdFx0XHRpZiAoIXRoaXMuanNvbiAmJiBjb29raWUuY2hhckF0KDApID09PSAnXCInKSB7XG5cdFx0XHRcdFx0Y29va2llID0gY29va2llLnNsaWNlKDEsIC0xKTtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdHRyeSB7XG5cdFx0XHRcdFx0dmFyIG5hbWUgPSBwYXJ0c1swXS5yZXBsYWNlKHJkZWNvZGUsIGRlY29kZVVSSUNvbXBvbmVudCk7XG5cdFx0XHRcdFx0Y29va2llID0gY29udmVydGVyLnJlYWQgP1xuXHRcdFx0XHRcdFx0Y29udmVydGVyLnJlYWQoY29va2llLCBuYW1lKSA6IGNvbnZlcnRlcihjb29raWUsIG5hbWUpIHx8XG5cdFx0XHRcdFx0XHRjb29raWUucmVwbGFjZShyZGVjb2RlLCBkZWNvZGVVUklDb21wb25lbnQpO1xuXG5cdFx0XHRcdFx0aWYgKHRoaXMuanNvbikge1xuXHRcdFx0XHRcdFx0dHJ5IHtcblx0XHRcdFx0XHRcdFx0Y29va2llID0gSlNPTi5wYXJzZShjb29raWUpO1xuXHRcdFx0XHRcdFx0fSBjYXRjaCAoZSkge31cblx0XHRcdFx0XHR9XG5cblx0XHRcdFx0XHRpZiAoa2V5ID09PSBuYW1lKSB7XG5cdFx0XHRcdFx0XHRyZXN1bHQgPSBjb29raWU7XG5cdFx0XHRcdFx0XHRicmVhaztcblx0XHRcdFx0XHR9XG5cblx0XHRcdFx0XHRpZiAoIWtleSkge1xuXHRcdFx0XHRcdFx0cmVzdWx0W25hbWVdID0gY29va2llO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0fSBjYXRjaCAoZSkge31cblx0XHRcdH1cblxuXHRcdFx0cmV0dXJuIHJlc3VsdDtcblx0XHR9XG5cblx0XHRhcGkuc2V0ID0gYXBpO1xuXHRcdGFwaS5nZXQgPSBmdW5jdGlvbiAoa2V5KSB7XG5cdFx0XHRyZXR1cm4gYXBpLmNhbGwoYXBpLCBrZXkpO1xuXHRcdH07XG5cdFx0YXBpLmdldEpTT04gPSBmdW5jdGlvbiAoKSB7XG5cdFx0XHRyZXR1cm4gYXBpLmFwcGx5KHtcblx0XHRcdFx0anNvbjogdHJ1ZVxuXHRcdFx0fSwgW10uc2xpY2UuY2FsbChhcmd1bWVudHMpKTtcblx0XHR9O1xuXHRcdGFwaS5kZWZhdWx0cyA9IHt9O1xuXG5cdFx0YXBpLnJlbW92ZSA9IGZ1bmN0aW9uIChrZXksIGF0dHJpYnV0ZXMpIHtcblx0XHRcdGFwaShrZXksICcnLCBleHRlbmQoYXR0cmlidXRlcywge1xuXHRcdFx0XHRleHBpcmVzOiAtMVxuXHRcdFx0fSkpO1xuXHRcdH07XG5cblx0XHRhcGkud2l0aENvbnZlcnRlciA9IGluaXQ7XG5cblx0XHRyZXR1cm4gYXBpO1xuXHR9XG5cblx0cmV0dXJuIGluaXQoZnVuY3Rpb24gKCkge30pO1xufSkpO1xuIl19
//# sourceMappingURL=index.js.map?build=eb1dae139fd75ecc3a16ff18d370b3a4e0e0b5e2
