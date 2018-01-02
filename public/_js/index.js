(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _baseview = require('../../lib/baseview');

var _baseview2 = _interopRequireDefault(_baseview);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var View = function (_BaseView) {
    _inherits(View, _BaseView);

    function View(element, options) {
        _classCallCheck(this, View);

        var _this = _possibleConstructorReturn(this, (View.__proto__ || Object.getPrototypeOf(View)).call(this, element, options));

        _this.bindEvents();
        console.log('Component: Periodlist', _this, options);
        return _this;
    }

    _createClass(View, [{
        key: 'bindEvents',
        value: function bindEvents() {
            var _this2 = this;

            this.$main.off('click').on('click', '.report-period--show-all', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                _this2.$main.find('.report-period--show-all').hide();
                _this2.$main.find(".report-period--table tr").removeClass('hide');
            });
        }
    }]);

    return View;
}(_baseview2.default);

exports.default = View;

},{"../../lib/baseview":3}],2:[function(require,module,exports){
(function (global){
"use strict";

require("babel-polyfill");

var _window = (typeof window !== "undefined" ? window['window'] : typeof global !== "undefined" ? global['window'] : null);

var _window2 = _interopRequireDefault(_window);

var _jquery = require("jquery");

var _jquery2 = _interopRequireDefault(_jquery);

var _moment = require("moment");

var _moment2 = _interopRequireDefault(_moment);

require("moment/locale/de");

var _utils = require("./lib/utils");

var _periodlist = require("./block/periodlist");

var _periodlist2 = _interopRequireDefault(_periodlist);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

// Bind jQuery on $ for testing

//import bindReact from './lib/bindReact.js'


// Import base libs
_window2.default.$ = _jquery2.default; // --------------------------------------------------------
// ZMS Statistic behavior
// --------------------------------------------------------

_moment2.default.locale('de');

// Say hello
console.log("Welcome to the ZMS statistic interface...");

(0, _jquery2.default)('.report-period').each(function () {
    new _periodlist2.default(this, (0, _utils.getDataAttributes)(this));
});

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})

},{"./block/periodlist":1,"./lib/utils":5,"babel-polyfill":"babel-polyfill","jquery":"jquery","moment":"moment","moment/locale/de":6}],3:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _jquery = require("jquery");

var _jquery2 = _interopRequireDefault(_jquery);

var _bindHandler = require("./bindHandler");

var _bindHandler2 = _interopRequireDefault(_bindHandler);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var BaseView = function (_BindHandler) {
    _inherits(BaseView, _BindHandler);

    function BaseView(element) {
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

        _classCallCheck(this, BaseView);

        var _this = _possibleConstructorReturn(this, (BaseView.__proto__ || Object.getPrototypeOf(BaseView)).call(this));

        _this.$main = (0, _jquery2.default)(element);
        _this.$main.off();
        _this.options = options;
        return _this;
    }

    _createClass(BaseView, [{
        key: "$",
        get: function get() {
            return this.$main;
        }
    }]);

    return BaseView;
}(_bindHandler2.default);

exports.default = BaseView;

},{"./bindHandler":4,"jquery":"jquery"}],4:[function(require,module,exports){
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

},{}],5:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.getUrlParameters = exports.noOp = exports.lightbox = exports.tryJson = exports.getDataAttributes = exports.deepGet = exports.range = exports.timestampToFloat = exports.timeToFloat = undefined;

var _slicedToArray = function () { function sliceIterator(arr, i) { var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"]) _i["return"](); } finally { if (_d) throw _e; } } return _arr; } return function (arr, i) { if (Array.isArray(arr)) { return arr; } else if (Symbol.iterator in Object(arr)) { return sliceIterator(arr, i); } else { throw new TypeError("Invalid attempt to destructure non-iterable instance"); } }; }();

var _jquery = require('jquery');

var _jquery2 = _interopRequireDefault(_jquery);

var _moment = require('moment');

var _moment2 = _interopRequireDefault(_moment);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var timeToFloat = exports.timeToFloat = function timeToFloat(time) {
    var momentTime = (0, _moment2.default)(time, 'HH:mm:ss');

    return momentTime.hours() + momentTime.minutes() / 60;
};

var timestampToFloat = exports.timestampToFloat = function timestampToFloat(timestamp) {
    var momentTime = (0, _moment2.default)(timestamp, 'X');

    return momentTime.hours() + momentTime.minutes() / 60;
};

var range = exports.range = function range(start, end) {
    var step = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 1;

    var result = [];
    for (var i = start; i <= end; i += step) {
        result.push(i);
    }

    return result;
};

var deepGet = exports.deepGet = function deepGet(obj) {
    var path = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
    return path.reduce(function (carry, current) {
        return carry ? carry[current] : undefined;
    }, obj);
};

var attributesToArray = function attributesToArray(attributes) {
    return Array.prototype.slice.call(attributes, 0);
};

var getDataAttributes = exports.getDataAttributes = function getDataAttributes(element) {
    var attributes = attributesToArray(element.attributes);
    var dataRegex = /^data-/i;

    return attributes.filter(function (attribute) {
        return dataRegex.test(attribute.nodeName);
    }).map(function (attribute) {
        return [attribute.name.replace(dataRegex, ''), attribute.value];
    }).reduce(function (carry, _ref) {
        var _ref2 = _slicedToArray(_ref, 2),
            key = _ref2[0],
            value = _ref2[1];

        carry[key] = tryJson(value);
        return carry;
    }, {});
};

var tryJson = exports.tryJson = function tryJson(input) {
    try {
        return JSON.parse(input);
    } catch (e) {
        return input;
    }
};

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
        console.log('background click', ev);
        ev.stopPropagation();
        ev.preventDefault();
        destroyLightbox();
        onBackgroundClick();
    }).on('click', '.lightbox__content', function (ev) {
        ev.stopPropagation();
    });

    (0, _jquery2.default)(parentElement).append(lightboxElement);

    return {
        lightboxContentElement: lightboxContentElement,
        destroyLightbox: destroyLightbox
    };
};

var noOp = exports.noOp = function noOp() {};

var getUrlParameters = exports.getUrlParameters = function getUrlParameters() {
    return document.location.search.replace(/^\?/, "").split("&").reduce(function (carry, current) {
        var _current$split = current.split('='),
            _current$split2 = _slicedToArray(_current$split, 2),
            key = _current$split2[0],
            value = _current$split2[1];

        if (key) {
            return Object.assign({}, carry, _defineProperty({}, key, value));
        } else {
            return carry;
        }
    }, {});
};

},{"jquery":"jquery","moment":"moment"}],6:[function(require,module,exports){
//! moment.js locale configuration
//! locale : German [de]
//! author : lluchs : https://github.com/lluchs
//! author: Menelion Elensúle: https://github.com/Oire
//! author : Mikolaj Dadela : https://github.com/mik01aj

;(function (global, factory) {
   typeof exports === 'object' && typeof module !== 'undefined'
       && typeof require === 'function' ? factory(require('../moment')) :
   typeof define === 'function' && define.amd ? define(['../moment'], factory) :
   factory(global.moment)
}(this, (function (moment) { 'use strict';


function processRelativeTime(number, withoutSuffix, key, isFuture) {
    var format = {
        'm': ['eine Minute', 'einer Minute'],
        'h': ['eine Stunde', 'einer Stunde'],
        'd': ['ein Tag', 'einem Tag'],
        'dd': [number + ' Tage', number + ' Tagen'],
        'M': ['ein Monat', 'einem Monat'],
        'MM': [number + ' Monate', number + ' Monaten'],
        'y': ['ein Jahr', 'einem Jahr'],
        'yy': [number + ' Jahre', number + ' Jahren']
    };
    return withoutSuffix ? format[key][0] : format[key][1];
}

var de = moment.defineLocale('de', {
    months : 'Januar_Februar_März_April_Mai_Juni_Juli_August_September_Oktober_November_Dezember'.split('_'),
    monthsShort : 'Jan._Feb._März_Apr._Mai_Juni_Juli_Aug._Sep._Okt._Nov._Dez.'.split('_'),
    monthsParseExact : true,
    weekdays : 'Sonntag_Montag_Dienstag_Mittwoch_Donnerstag_Freitag_Samstag'.split('_'),
    weekdaysShort : 'So._Mo._Di._Mi._Do._Fr._Sa.'.split('_'),
    weekdaysMin : 'So_Mo_Di_Mi_Do_Fr_Sa'.split('_'),
    weekdaysParseExact : true,
    longDateFormat : {
        LT: 'HH:mm',
        LTS: 'HH:mm:ss',
        L : 'DD.MM.YYYY',
        LL : 'D. MMMM YYYY',
        LLL : 'D. MMMM YYYY HH:mm',
        LLLL : 'dddd, D. MMMM YYYY HH:mm'
    },
    calendar : {
        sameDay: '[heute um] LT [Uhr]',
        sameElse: 'L',
        nextDay: '[morgen um] LT [Uhr]',
        nextWeek: 'dddd [um] LT [Uhr]',
        lastDay: '[gestern um] LT [Uhr]',
        lastWeek: '[letzten] dddd [um] LT [Uhr]'
    },
    relativeTime : {
        future : 'in %s',
        past : 'vor %s',
        s : 'ein paar Sekunden',
        m : processRelativeTime,
        mm : '%d Minuten',
        h : processRelativeTime,
        hh : '%d Stunden',
        d : processRelativeTime,
        dd : processRelativeTime,
        M : processRelativeTime,
        MM : processRelativeTime,
        y : processRelativeTime,
        yy : processRelativeTime
    },
    dayOfMonthOrdinalParse: /\d{1,2}\./,
    ordinal : '%d.',
    week : {
        dow : 1, // Monday is the first day of the week.
        doy : 4  // The week that contains Jan 4th is the first week of the year.
    }
});

return de;

})));

},{"../moment":7}],7:[function(require,module,exports){
//! moment.js
//! version : 2.19.4
//! authors : Tim Wood, Iskren Chernev, Moment.js contributors
//! license : MIT
//! momentjs.com

;(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
    typeof define === 'function' && define.amd ? define(factory) :
    global.moment = factory()
}(this, (function () { 'use strict';

var hookCallback;

function hooks () {
    return hookCallback.apply(null, arguments);
}

// This is done to register the method called with moment()
// without creating circular dependencies.
function setHookCallback (callback) {
    hookCallback = callback;
}

function isArray(input) {
    return input instanceof Array || Object.prototype.toString.call(input) === '[object Array]';
}

function isObject(input) {
    // IE8 will treat undefined and null as object if it wasn't for
    // input != null
    return input != null && Object.prototype.toString.call(input) === '[object Object]';
}

function isObjectEmpty(obj) {
    if (Object.getOwnPropertyNames) {
        return (Object.getOwnPropertyNames(obj).length === 0);
    } else {
        var k;
        for (k in obj) {
            if (obj.hasOwnProperty(k)) {
                return false;
            }
        }
        return true;
    }
}

function isUndefined(input) {
    return input === void 0;
}

function isNumber(input) {
    return typeof input === 'number' || Object.prototype.toString.call(input) === '[object Number]';
}

function isDate(input) {
    return input instanceof Date || Object.prototype.toString.call(input) === '[object Date]';
}

function map(arr, fn) {
    var res = [], i;
    for (i = 0; i < arr.length; ++i) {
        res.push(fn(arr[i], i));
    }
    return res;
}

function hasOwnProp(a, b) {
    return Object.prototype.hasOwnProperty.call(a, b);
}

function extend(a, b) {
    for (var i in b) {
        if (hasOwnProp(b, i)) {
            a[i] = b[i];
        }
    }

    if (hasOwnProp(b, 'toString')) {
        a.toString = b.toString;
    }

    if (hasOwnProp(b, 'valueOf')) {
        a.valueOf = b.valueOf;
    }

    return a;
}

function createUTC (input, format, locale, strict) {
    return createLocalOrUTC(input, format, locale, strict, true).utc();
}

function defaultParsingFlags() {
    // We need to deep clone this object.
    return {
        empty           : false,
        unusedTokens    : [],
        unusedInput     : [],
        overflow        : -2,
        charsLeftOver   : 0,
        nullInput       : false,
        invalidMonth    : null,
        invalidFormat   : false,
        userInvalidated : false,
        iso             : false,
        parsedDateParts : [],
        meridiem        : null,
        rfc2822         : false,
        weekdayMismatch : false
    };
}

function getParsingFlags(m) {
    if (m._pf == null) {
        m._pf = defaultParsingFlags();
    }
    return m._pf;
}

var some;
if (Array.prototype.some) {
    some = Array.prototype.some;
} else {
    some = function (fun) {
        var t = Object(this);
        var len = t.length >>> 0;

        for (var i = 0; i < len; i++) {
            if (i in t && fun.call(this, t[i], i, t)) {
                return true;
            }
        }

        return false;
    };
}

function isValid(m) {
    if (m._isValid == null) {
        var flags = getParsingFlags(m);
        var parsedParts = some.call(flags.parsedDateParts, function (i) {
            return i != null;
        });
        var isNowValid = !isNaN(m._d.getTime()) &&
            flags.overflow < 0 &&
            !flags.empty &&
            !flags.invalidMonth &&
            !flags.invalidWeekday &&
            !flags.weekdayMismatch &&
            !flags.nullInput &&
            !flags.invalidFormat &&
            !flags.userInvalidated &&
            (!flags.meridiem || (flags.meridiem && parsedParts));

        if (m._strict) {
            isNowValid = isNowValid &&
                flags.charsLeftOver === 0 &&
                flags.unusedTokens.length === 0 &&
                flags.bigHour === undefined;
        }

        if (Object.isFrozen == null || !Object.isFrozen(m)) {
            m._isValid = isNowValid;
        }
        else {
            return isNowValid;
        }
    }
    return m._isValid;
}

function createInvalid (flags) {
    var m = createUTC(NaN);
    if (flags != null) {
        extend(getParsingFlags(m), flags);
    }
    else {
        getParsingFlags(m).userInvalidated = true;
    }

    return m;
}

// Plugins that add properties should also add the key here (null value),
// so we can properly clone ourselves.
var momentProperties = hooks.momentProperties = [];

function copyConfig(to, from) {
    var i, prop, val;

    if (!isUndefined(from._isAMomentObject)) {
        to._isAMomentObject = from._isAMomentObject;
    }
    if (!isUndefined(from._i)) {
        to._i = from._i;
    }
    if (!isUndefined(from._f)) {
        to._f = from._f;
    }
    if (!isUndefined(from._l)) {
        to._l = from._l;
    }
    if (!isUndefined(from._strict)) {
        to._strict = from._strict;
    }
    if (!isUndefined(from._tzm)) {
        to._tzm = from._tzm;
    }
    if (!isUndefined(from._isUTC)) {
        to._isUTC = from._isUTC;
    }
    if (!isUndefined(from._offset)) {
        to._offset = from._offset;
    }
    if (!isUndefined(from._pf)) {
        to._pf = getParsingFlags(from);
    }
    if (!isUndefined(from._locale)) {
        to._locale = from._locale;
    }

    if (momentProperties.length > 0) {
        for (i = 0; i < momentProperties.length; i++) {
            prop = momentProperties[i];
            val = from[prop];
            if (!isUndefined(val)) {
                to[prop] = val;
            }
        }
    }

    return to;
}

var updateInProgress = false;

// Moment prototype object
function Moment(config) {
    copyConfig(this, config);
    this._d = new Date(config._d != null ? config._d.getTime() : NaN);
    if (!this.isValid()) {
        this._d = new Date(NaN);
    }
    // Prevent infinite loop in case updateOffset creates new moment
    // objects.
    if (updateInProgress === false) {
        updateInProgress = true;
        hooks.updateOffset(this);
        updateInProgress = false;
    }
}

function isMoment (obj) {
    return obj instanceof Moment || (obj != null && obj._isAMomentObject != null);
}

function absFloor (number) {
    if (number < 0) {
        // -0 -> 0
        return Math.ceil(number) || 0;
    } else {
        return Math.floor(number);
    }
}

function toInt(argumentForCoercion) {
    var coercedNumber = +argumentForCoercion,
        value = 0;

    if (coercedNumber !== 0 && isFinite(coercedNumber)) {
        value = absFloor(coercedNumber);
    }

    return value;
}

// compare two arrays, return the number of differences
function compareArrays(array1, array2, dontConvert) {
    var len = Math.min(array1.length, array2.length),
        lengthDiff = Math.abs(array1.length - array2.length),
        diffs = 0,
        i;
    for (i = 0; i < len; i++) {
        if ((dontConvert && array1[i] !== array2[i]) ||
            (!dontConvert && toInt(array1[i]) !== toInt(array2[i]))) {
            diffs++;
        }
    }
    return diffs + lengthDiff;
}

function warn(msg) {
    if (hooks.suppressDeprecationWarnings === false &&
            (typeof console !==  'undefined') && console.warn) {
        console.warn('Deprecation warning: ' + msg);
    }
}

function deprecate(msg, fn) {
    var firstTime = true;

    return extend(function () {
        if (hooks.deprecationHandler != null) {
            hooks.deprecationHandler(null, msg);
        }
        if (firstTime) {
            var args = [];
            var arg;
            for (var i = 0; i < arguments.length; i++) {
                arg = '';
                if (typeof arguments[i] === 'object') {
                    arg += '\n[' + i + '] ';
                    for (var key in arguments[0]) {
                        arg += key + ': ' + arguments[0][key] + ', ';
                    }
                    arg = arg.slice(0, -2); // Remove trailing comma and space
                } else {
                    arg = arguments[i];
                }
                args.push(arg);
            }
            warn(msg + '\nArguments: ' + Array.prototype.slice.call(args).join('') + '\n' + (new Error()).stack);
            firstTime = false;
        }
        return fn.apply(this, arguments);
    }, fn);
}

var deprecations = {};

function deprecateSimple(name, msg) {
    if (hooks.deprecationHandler != null) {
        hooks.deprecationHandler(name, msg);
    }
    if (!deprecations[name]) {
        warn(msg);
        deprecations[name] = true;
    }
}

hooks.suppressDeprecationWarnings = false;
hooks.deprecationHandler = null;

function isFunction(input) {
    return input instanceof Function || Object.prototype.toString.call(input) === '[object Function]';
}

function set (config) {
    var prop, i;
    for (i in config) {
        prop = config[i];
        if (isFunction(prop)) {
            this[i] = prop;
        } else {
            this['_' + i] = prop;
        }
    }
    this._config = config;
    // Lenient ordinal parsing accepts just a number in addition to
    // number + (possibly) stuff coming from _dayOfMonthOrdinalParse.
    // TODO: Remove "ordinalParse" fallback in next major release.
    this._dayOfMonthOrdinalParseLenient = new RegExp(
        (this._dayOfMonthOrdinalParse.source || this._ordinalParse.source) +
            '|' + (/\d{1,2}/).source);
}

function mergeConfigs(parentConfig, childConfig) {
    var res = extend({}, parentConfig), prop;
    for (prop in childConfig) {
        if (hasOwnProp(childConfig, prop)) {
            if (isObject(parentConfig[prop]) && isObject(childConfig[prop])) {
                res[prop] = {};
                extend(res[prop], parentConfig[prop]);
                extend(res[prop], childConfig[prop]);
            } else if (childConfig[prop] != null) {
                res[prop] = childConfig[prop];
            } else {
                delete res[prop];
            }
        }
    }
    for (prop in parentConfig) {
        if (hasOwnProp(parentConfig, prop) &&
                !hasOwnProp(childConfig, prop) &&
                isObject(parentConfig[prop])) {
            // make sure changes to properties don't modify parent config
            res[prop] = extend({}, res[prop]);
        }
    }
    return res;
}

function Locale(config) {
    if (config != null) {
        this.set(config);
    }
}

var keys;

if (Object.keys) {
    keys = Object.keys;
} else {
    keys = function (obj) {
        var i, res = [];
        for (i in obj) {
            if (hasOwnProp(obj, i)) {
                res.push(i);
            }
        }
        return res;
    };
}

var defaultCalendar = {
    sameDay : '[Today at] LT',
    nextDay : '[Tomorrow at] LT',
    nextWeek : 'dddd [at] LT',
    lastDay : '[Yesterday at] LT',
    lastWeek : '[Last] dddd [at] LT',
    sameElse : 'L'
};

function calendar (key, mom, now) {
    var output = this._calendar[key] || this._calendar['sameElse'];
    return isFunction(output) ? output.call(mom, now) : output;
}

var defaultLongDateFormat = {
    LTS  : 'h:mm:ss A',
    LT   : 'h:mm A',
    L    : 'MM/DD/YYYY',
    LL   : 'MMMM D, YYYY',
    LLL  : 'MMMM D, YYYY h:mm A',
    LLLL : 'dddd, MMMM D, YYYY h:mm A'
};

function longDateFormat (key) {
    var format = this._longDateFormat[key],
        formatUpper = this._longDateFormat[key.toUpperCase()];

    if (format || !formatUpper) {
        return format;
    }

    this._longDateFormat[key] = formatUpper.replace(/MMMM|MM|DD|dddd/g, function (val) {
        return val.slice(1);
    });

    return this._longDateFormat[key];
}

var defaultInvalidDate = 'Invalid date';

function invalidDate () {
    return this._invalidDate;
}

var defaultOrdinal = '%d';
var defaultDayOfMonthOrdinalParse = /\d{1,2}/;

function ordinal (number) {
    return this._ordinal.replace('%d', number);
}

var defaultRelativeTime = {
    future : 'in %s',
    past   : '%s ago',
    s  : 'a few seconds',
    ss : '%d seconds',
    m  : 'a minute',
    mm : '%d minutes',
    h  : 'an hour',
    hh : '%d hours',
    d  : 'a day',
    dd : '%d days',
    M  : 'a month',
    MM : '%d months',
    y  : 'a year',
    yy : '%d years'
};

function relativeTime (number, withoutSuffix, string, isFuture) {
    var output = this._relativeTime[string];
    return (isFunction(output)) ?
        output(number, withoutSuffix, string, isFuture) :
        output.replace(/%d/i, number);
}

function pastFuture (diff, output) {
    var format = this._relativeTime[diff > 0 ? 'future' : 'past'];
    return isFunction(format) ? format(output) : format.replace(/%s/i, output);
}

var aliases = {};

function addUnitAlias (unit, shorthand) {
    var lowerCase = unit.toLowerCase();
    aliases[lowerCase] = aliases[lowerCase + 's'] = aliases[shorthand] = unit;
}

function normalizeUnits(units) {
    return typeof units === 'string' ? aliases[units] || aliases[units.toLowerCase()] : undefined;
}

function normalizeObjectUnits(inputObject) {
    var normalizedInput = {},
        normalizedProp,
        prop;

    for (prop in inputObject) {
        if (hasOwnProp(inputObject, prop)) {
            normalizedProp = normalizeUnits(prop);
            if (normalizedProp) {
                normalizedInput[normalizedProp] = inputObject[prop];
            }
        }
    }

    return normalizedInput;
}

var priorities = {};

function addUnitPriority(unit, priority) {
    priorities[unit] = priority;
}

function getPrioritizedUnits(unitsObj) {
    var units = [];
    for (var u in unitsObj) {
        units.push({unit: u, priority: priorities[u]});
    }
    units.sort(function (a, b) {
        return a.priority - b.priority;
    });
    return units;
}

function zeroFill(number, targetLength, forceSign) {
    var absNumber = '' + Math.abs(number),
        zerosToFill = targetLength - absNumber.length,
        sign = number >= 0;
    return (sign ? (forceSign ? '+' : '') : '-') +
        Math.pow(10, Math.max(0, zerosToFill)).toString().substr(1) + absNumber;
}

var formattingTokens = /(\[[^\[]*\])|(\\)?([Hh]mm(ss)?|Mo|MM?M?M?|Do|DDDo|DD?D?D?|ddd?d?|do?|w[o|w]?|W[o|W]?|Qo?|YYYYYY|YYYYY|YYYY|YY|gg(ggg?)?|GG(GGG?)?|e|E|a|A|hh?|HH?|kk?|mm?|ss?|S{1,9}|x|X|zz?|ZZ?|.)/g;

var localFormattingTokens = /(\[[^\[]*\])|(\\)?(LTS|LT|LL?L?L?|l{1,4})/g;

var formatFunctions = {};

var formatTokenFunctions = {};

// token:    'M'
// padded:   ['MM', 2]
// ordinal:  'Mo'
// callback: function () { this.month() + 1 }
function addFormatToken (token, padded, ordinal, callback) {
    var func = callback;
    if (typeof callback === 'string') {
        func = function () {
            return this[callback]();
        };
    }
    if (token) {
        formatTokenFunctions[token] = func;
    }
    if (padded) {
        formatTokenFunctions[padded[0]] = function () {
            return zeroFill(func.apply(this, arguments), padded[1], padded[2]);
        };
    }
    if (ordinal) {
        formatTokenFunctions[ordinal] = function () {
            return this.localeData().ordinal(func.apply(this, arguments), token);
        };
    }
}

function removeFormattingTokens(input) {
    if (input.match(/\[[\s\S]/)) {
        return input.replace(/^\[|\]$/g, '');
    }
    return input.replace(/\\/g, '');
}

function makeFormatFunction(format) {
    var array = format.match(formattingTokens), i, length;

    for (i = 0, length = array.length; i < length; i++) {
        if (formatTokenFunctions[array[i]]) {
            array[i] = formatTokenFunctions[array[i]];
        } else {
            array[i] = removeFormattingTokens(array[i]);
        }
    }

    return function (mom) {
        var output = '', i;
        for (i = 0; i < length; i++) {
            output += isFunction(array[i]) ? array[i].call(mom, format) : array[i];
        }
        return output;
    };
}

// format date using native date object
function formatMoment(m, format) {
    if (!m.isValid()) {
        return m.localeData().invalidDate();
    }

    format = expandFormat(format, m.localeData());
    formatFunctions[format] = formatFunctions[format] || makeFormatFunction(format);

    return formatFunctions[format](m);
}

function expandFormat(format, locale) {
    var i = 5;

    function replaceLongDateFormatTokens(input) {
        return locale.longDateFormat(input) || input;
    }

    localFormattingTokens.lastIndex = 0;
    while (i >= 0 && localFormattingTokens.test(format)) {
        format = format.replace(localFormattingTokens, replaceLongDateFormatTokens);
        localFormattingTokens.lastIndex = 0;
        i -= 1;
    }

    return format;
}

var match1         = /\d/;            //       0 - 9
var match2         = /\d\d/;          //      00 - 99
var match3         = /\d{3}/;         //     000 - 999
var match4         = /\d{4}/;         //    0000 - 9999
var match6         = /[+-]?\d{6}/;    // -999999 - 999999
var match1to2      = /\d\d?/;         //       0 - 99
var match3to4      = /\d\d\d\d?/;     //     999 - 9999
var match5to6      = /\d\d\d\d\d\d?/; //   99999 - 999999
var match1to3      = /\d{1,3}/;       //       0 - 999
var match1to4      = /\d{1,4}/;       //       0 - 9999
var match1to6      = /[+-]?\d{1,6}/;  // -999999 - 999999

var matchUnsigned  = /\d+/;           //       0 - inf
var matchSigned    = /[+-]?\d+/;      //    -inf - inf

var matchOffset    = /Z|[+-]\d\d:?\d\d/gi; // +00:00 -00:00 +0000 -0000 or Z
var matchShortOffset = /Z|[+-]\d\d(?::?\d\d)?/gi; // +00 -00 +00:00 -00:00 +0000 -0000 or Z

var matchTimestamp = /[+-]?\d+(\.\d{1,3})?/; // 123456789 123456789.123

// any word (or two) characters or numbers including two/three word month in arabic.
// includes scottish gaelic two word and hyphenated months
var matchWord = /[0-9]{0,256}['a-z\u00A0-\u05FF\u0700-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]{1,256}|[\u0600-\u06FF\/]{1,256}(\s*?[\u0600-\u06FF]{1,256}){1,2}/i;


var regexes = {};

function addRegexToken (token, regex, strictRegex) {
    regexes[token] = isFunction(regex) ? regex : function (isStrict, localeData) {
        return (isStrict && strictRegex) ? strictRegex : regex;
    };
}

function getParseRegexForToken (token, config) {
    if (!hasOwnProp(regexes, token)) {
        return new RegExp(unescapeFormat(token));
    }

    return regexes[token](config._strict, config._locale);
}

// Code from http://stackoverflow.com/questions/3561493/is-there-a-regexp-escape-function-in-javascript
function unescapeFormat(s) {
    return regexEscape(s.replace('\\', '').replace(/\\(\[)|\\(\])|\[([^\]\[]*)\]|\\(.)/g, function (matched, p1, p2, p3, p4) {
        return p1 || p2 || p3 || p4;
    }));
}

function regexEscape(s) {
    return s.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
}

var tokens = {};

function addParseToken (token, callback) {
    var i, func = callback;
    if (typeof token === 'string') {
        token = [token];
    }
    if (isNumber(callback)) {
        func = function (input, array) {
            array[callback] = toInt(input);
        };
    }
    for (i = 0; i < token.length; i++) {
        tokens[token[i]] = func;
    }
}

function addWeekParseToken (token, callback) {
    addParseToken(token, function (input, array, config, token) {
        config._w = config._w || {};
        callback(input, config._w, config, token);
    });
}

function addTimeToArrayFromToken(token, input, config) {
    if (input != null && hasOwnProp(tokens, token)) {
        tokens[token](input, config._a, config, token);
    }
}

var YEAR = 0;
var MONTH = 1;
var DATE = 2;
var HOUR = 3;
var MINUTE = 4;
var SECOND = 5;
var MILLISECOND = 6;
var WEEK = 7;
var WEEKDAY = 8;

// FORMATTING

addFormatToken('Y', 0, 0, function () {
    var y = this.year();
    return y <= 9999 ? '' + y : '+' + y;
});

addFormatToken(0, ['YY', 2], 0, function () {
    return this.year() % 100;
});

addFormatToken(0, ['YYYY',   4],       0, 'year');
addFormatToken(0, ['YYYYY',  5],       0, 'year');
addFormatToken(0, ['YYYYYY', 6, true], 0, 'year');

// ALIASES

addUnitAlias('year', 'y');

// PRIORITIES

addUnitPriority('year', 1);

// PARSING

addRegexToken('Y',      matchSigned);
addRegexToken('YY',     match1to2, match2);
addRegexToken('YYYY',   match1to4, match4);
addRegexToken('YYYYY',  match1to6, match6);
addRegexToken('YYYYYY', match1to6, match6);

addParseToken(['YYYYY', 'YYYYYY'], YEAR);
addParseToken('YYYY', function (input, array) {
    array[YEAR] = input.length === 2 ? hooks.parseTwoDigitYear(input) : toInt(input);
});
addParseToken('YY', function (input, array) {
    array[YEAR] = hooks.parseTwoDigitYear(input);
});
addParseToken('Y', function (input, array) {
    array[YEAR] = parseInt(input, 10);
});

// HELPERS

function daysInYear(year) {
    return isLeapYear(year) ? 366 : 365;
}

function isLeapYear(year) {
    return (year % 4 === 0 && year % 100 !== 0) || year % 400 === 0;
}

// HOOKS

hooks.parseTwoDigitYear = function (input) {
    return toInt(input) + (toInt(input) > 68 ? 1900 : 2000);
};

// MOMENTS

var getSetYear = makeGetSet('FullYear', true);

function getIsLeapYear () {
    return isLeapYear(this.year());
}

function makeGetSet (unit, keepTime) {
    return function (value) {
        if (value != null) {
            set$1(this, unit, value);
            hooks.updateOffset(this, keepTime);
            return this;
        } else {
            return get(this, unit);
        }
    };
}

function get (mom, unit) {
    return mom.isValid() ?
        mom._d['get' + (mom._isUTC ? 'UTC' : '') + unit]() : NaN;
}

function set$1 (mom, unit, value) {
    if (mom.isValid() && !isNaN(value)) {
        if (unit === 'FullYear' && isLeapYear(mom.year()) && mom.month() === 1 && mom.date() === 29) {
            mom._d['set' + (mom._isUTC ? 'UTC' : '') + unit](value, mom.month(), daysInMonth(value, mom.month()));
        }
        else {
            mom._d['set' + (mom._isUTC ? 'UTC' : '') + unit](value);
        }
    }
}

// MOMENTS

function stringGet (units) {
    units = normalizeUnits(units);
    if (isFunction(this[units])) {
        return this[units]();
    }
    return this;
}


function stringSet (units, value) {
    if (typeof units === 'object') {
        units = normalizeObjectUnits(units);
        var prioritized = getPrioritizedUnits(units);
        for (var i = 0; i < prioritized.length; i++) {
            this[prioritized[i].unit](units[prioritized[i].unit]);
        }
    } else {
        units = normalizeUnits(units);
        if (isFunction(this[units])) {
            return this[units](value);
        }
    }
    return this;
}

function mod(n, x) {
    return ((n % x) + x) % x;
}

var indexOf;

if (Array.prototype.indexOf) {
    indexOf = Array.prototype.indexOf;
} else {
    indexOf = function (o) {
        // I know
        var i;
        for (i = 0; i < this.length; ++i) {
            if (this[i] === o) {
                return i;
            }
        }
        return -1;
    };
}

function daysInMonth(year, month) {
    if (isNaN(year) || isNaN(month)) {
        return NaN;
    }
    var modMonth = mod(month, 12);
    year += (month - modMonth) / 12;
    return modMonth === 1 ? (isLeapYear(year) ? 29 : 28) : (31 - modMonth % 7 % 2);
}

// FORMATTING

addFormatToken('M', ['MM', 2], 'Mo', function () {
    return this.month() + 1;
});

addFormatToken('MMM', 0, 0, function (format) {
    return this.localeData().monthsShort(this, format);
});

addFormatToken('MMMM', 0, 0, function (format) {
    return this.localeData().months(this, format);
});

// ALIASES

addUnitAlias('month', 'M');

// PRIORITY

addUnitPriority('month', 8);

// PARSING

addRegexToken('M',    match1to2);
addRegexToken('MM',   match1to2, match2);
addRegexToken('MMM',  function (isStrict, locale) {
    return locale.monthsShortRegex(isStrict);
});
addRegexToken('MMMM', function (isStrict, locale) {
    return locale.monthsRegex(isStrict);
});

addParseToken(['M', 'MM'], function (input, array) {
    array[MONTH] = toInt(input) - 1;
});

addParseToken(['MMM', 'MMMM'], function (input, array, config, token) {
    var month = config._locale.monthsParse(input, token, config._strict);
    // if we didn't find a month name, mark the date as invalid.
    if (month != null) {
        array[MONTH] = month;
    } else {
        getParsingFlags(config).invalidMonth = input;
    }
});

// LOCALES

var MONTHS_IN_FORMAT = /D[oD]?(\[[^\[\]]*\]|\s)+MMMM?/;
var defaultLocaleMonths = 'January_February_March_April_May_June_July_August_September_October_November_December'.split('_');
function localeMonths (m, format) {
    if (!m) {
        return isArray(this._months) ? this._months :
            this._months['standalone'];
    }
    return isArray(this._months) ? this._months[m.month()] :
        this._months[(this._months.isFormat || MONTHS_IN_FORMAT).test(format) ? 'format' : 'standalone'][m.month()];
}

var defaultLocaleMonthsShort = 'Jan_Feb_Mar_Apr_May_Jun_Jul_Aug_Sep_Oct_Nov_Dec'.split('_');
function localeMonthsShort (m, format) {
    if (!m) {
        return isArray(this._monthsShort) ? this._monthsShort :
            this._monthsShort['standalone'];
    }
    return isArray(this._monthsShort) ? this._monthsShort[m.month()] :
        this._monthsShort[MONTHS_IN_FORMAT.test(format) ? 'format' : 'standalone'][m.month()];
}

function handleStrictParse(monthName, format, strict) {
    var i, ii, mom, llc = monthName.toLocaleLowerCase();
    if (!this._monthsParse) {
        // this is not used
        this._monthsParse = [];
        this._longMonthsParse = [];
        this._shortMonthsParse = [];
        for (i = 0; i < 12; ++i) {
            mom = createUTC([2000, i]);
            this._shortMonthsParse[i] = this.monthsShort(mom, '').toLocaleLowerCase();
            this._longMonthsParse[i] = this.months(mom, '').toLocaleLowerCase();
        }
    }

    if (strict) {
        if (format === 'MMM') {
            ii = indexOf.call(this._shortMonthsParse, llc);
            return ii !== -1 ? ii : null;
        } else {
            ii = indexOf.call(this._longMonthsParse, llc);
            return ii !== -1 ? ii : null;
        }
    } else {
        if (format === 'MMM') {
            ii = indexOf.call(this._shortMonthsParse, llc);
            if (ii !== -1) {
                return ii;
            }
            ii = indexOf.call(this._longMonthsParse, llc);
            return ii !== -1 ? ii : null;
        } else {
            ii = indexOf.call(this._longMonthsParse, llc);
            if (ii !== -1) {
                return ii;
            }
            ii = indexOf.call(this._shortMonthsParse, llc);
            return ii !== -1 ? ii : null;
        }
    }
}

function localeMonthsParse (monthName, format, strict) {
    var i, mom, regex;

    if (this._monthsParseExact) {
        return handleStrictParse.call(this, monthName, format, strict);
    }

    if (!this._monthsParse) {
        this._monthsParse = [];
        this._longMonthsParse = [];
        this._shortMonthsParse = [];
    }

    // TODO: add sorting
    // Sorting makes sure if one month (or abbr) is a prefix of another
    // see sorting in computeMonthsParse
    for (i = 0; i < 12; i++) {
        // make the regex if we don't have it already
        mom = createUTC([2000, i]);
        if (strict && !this._longMonthsParse[i]) {
            this._longMonthsParse[i] = new RegExp('^' + this.months(mom, '').replace('.', '') + '$', 'i');
            this._shortMonthsParse[i] = new RegExp('^' + this.monthsShort(mom, '').replace('.', '') + '$', 'i');
        }
        if (!strict && !this._monthsParse[i]) {
            regex = '^' + this.months(mom, '') + '|^' + this.monthsShort(mom, '');
            this._monthsParse[i] = new RegExp(regex.replace('.', ''), 'i');
        }
        // test the regex
        if (strict && format === 'MMMM' && this._longMonthsParse[i].test(monthName)) {
            return i;
        } else if (strict && format === 'MMM' && this._shortMonthsParse[i].test(monthName)) {
            return i;
        } else if (!strict && this._monthsParse[i].test(monthName)) {
            return i;
        }
    }
}

// MOMENTS

function setMonth (mom, value) {
    var dayOfMonth;

    if (!mom.isValid()) {
        // No op
        return mom;
    }

    if (typeof value === 'string') {
        if (/^\d+$/.test(value)) {
            value = toInt(value);
        } else {
            value = mom.localeData().monthsParse(value);
            // TODO: Another silent failure?
            if (!isNumber(value)) {
                return mom;
            }
        }
    }

    dayOfMonth = Math.min(mom.date(), daysInMonth(mom.year(), value));
    mom._d['set' + (mom._isUTC ? 'UTC' : '') + 'Month'](value, dayOfMonth);
    return mom;
}

function getSetMonth (value) {
    if (value != null) {
        setMonth(this, value);
        hooks.updateOffset(this, true);
        return this;
    } else {
        return get(this, 'Month');
    }
}

function getDaysInMonth () {
    return daysInMonth(this.year(), this.month());
}

var defaultMonthsShortRegex = matchWord;
function monthsShortRegex (isStrict) {
    if (this._monthsParseExact) {
        if (!hasOwnProp(this, '_monthsRegex')) {
            computeMonthsParse.call(this);
        }
        if (isStrict) {
            return this._monthsShortStrictRegex;
        } else {
            return this._monthsShortRegex;
        }
    } else {
        if (!hasOwnProp(this, '_monthsShortRegex')) {
            this._monthsShortRegex = defaultMonthsShortRegex;
        }
        return this._monthsShortStrictRegex && isStrict ?
            this._monthsShortStrictRegex : this._monthsShortRegex;
    }
}

var defaultMonthsRegex = matchWord;
function monthsRegex (isStrict) {
    if (this._monthsParseExact) {
        if (!hasOwnProp(this, '_monthsRegex')) {
            computeMonthsParse.call(this);
        }
        if (isStrict) {
            return this._monthsStrictRegex;
        } else {
            return this._monthsRegex;
        }
    } else {
        if (!hasOwnProp(this, '_monthsRegex')) {
            this._monthsRegex = defaultMonthsRegex;
        }
        return this._monthsStrictRegex && isStrict ?
            this._monthsStrictRegex : this._monthsRegex;
    }
}

function computeMonthsParse () {
    function cmpLenRev(a, b) {
        return b.length - a.length;
    }

    var shortPieces = [], longPieces = [], mixedPieces = [],
        i, mom;
    for (i = 0; i < 12; i++) {
        // make the regex if we don't have it already
        mom = createUTC([2000, i]);
        shortPieces.push(this.monthsShort(mom, ''));
        longPieces.push(this.months(mom, ''));
        mixedPieces.push(this.months(mom, ''));
        mixedPieces.push(this.monthsShort(mom, ''));
    }
    // Sorting makes sure if one month (or abbr) is a prefix of another it
    // will match the longer piece.
    shortPieces.sort(cmpLenRev);
    longPieces.sort(cmpLenRev);
    mixedPieces.sort(cmpLenRev);
    for (i = 0; i < 12; i++) {
        shortPieces[i] = regexEscape(shortPieces[i]);
        longPieces[i] = regexEscape(longPieces[i]);
    }
    for (i = 0; i < 24; i++) {
        mixedPieces[i] = regexEscape(mixedPieces[i]);
    }

    this._monthsRegex = new RegExp('^(' + mixedPieces.join('|') + ')', 'i');
    this._monthsShortRegex = this._monthsRegex;
    this._monthsStrictRegex = new RegExp('^(' + longPieces.join('|') + ')', 'i');
    this._monthsShortStrictRegex = new RegExp('^(' + shortPieces.join('|') + ')', 'i');
}

function createDate (y, m, d, h, M, s, ms) {
    // can't just apply() to create a date:
    // https://stackoverflow.com/q/181348
    var date = new Date(y, m, d, h, M, s, ms);

    // the date constructor remaps years 0-99 to 1900-1999
    if (y < 100 && y >= 0 && isFinite(date.getFullYear())) {
        date.setFullYear(y);
    }
    return date;
}

function createUTCDate (y) {
    var date = new Date(Date.UTC.apply(null, arguments));

    // the Date.UTC function remaps years 0-99 to 1900-1999
    if (y < 100 && y >= 0 && isFinite(date.getUTCFullYear())) {
        date.setUTCFullYear(y);
    }
    return date;
}

// start-of-first-week - start-of-year
function firstWeekOffset(year, dow, doy) {
    var // first-week day -- which january is always in the first week (4 for iso, 1 for other)
        fwd = 7 + dow - doy,
        // first-week day local weekday -- which local weekday is fwd
        fwdlw = (7 + createUTCDate(year, 0, fwd).getUTCDay() - dow) % 7;

    return -fwdlw + fwd - 1;
}

// https://en.wikipedia.org/wiki/ISO_week_date#Calculating_a_date_given_the_year.2C_week_number_and_weekday
function dayOfYearFromWeeks(year, week, weekday, dow, doy) {
    var localWeekday = (7 + weekday - dow) % 7,
        weekOffset = firstWeekOffset(year, dow, doy),
        dayOfYear = 1 + 7 * (week - 1) + localWeekday + weekOffset,
        resYear, resDayOfYear;

    if (dayOfYear <= 0) {
        resYear = year - 1;
        resDayOfYear = daysInYear(resYear) + dayOfYear;
    } else if (dayOfYear > daysInYear(year)) {
        resYear = year + 1;
        resDayOfYear = dayOfYear - daysInYear(year);
    } else {
        resYear = year;
        resDayOfYear = dayOfYear;
    }

    return {
        year: resYear,
        dayOfYear: resDayOfYear
    };
}

function weekOfYear(mom, dow, doy) {
    var weekOffset = firstWeekOffset(mom.year(), dow, doy),
        week = Math.floor((mom.dayOfYear() - weekOffset - 1) / 7) + 1,
        resWeek, resYear;

    if (week < 1) {
        resYear = mom.year() - 1;
        resWeek = week + weeksInYear(resYear, dow, doy);
    } else if (week > weeksInYear(mom.year(), dow, doy)) {
        resWeek = week - weeksInYear(mom.year(), dow, doy);
        resYear = mom.year() + 1;
    } else {
        resYear = mom.year();
        resWeek = week;
    }

    return {
        week: resWeek,
        year: resYear
    };
}

function weeksInYear(year, dow, doy) {
    var weekOffset = firstWeekOffset(year, dow, doy),
        weekOffsetNext = firstWeekOffset(year + 1, dow, doy);
    return (daysInYear(year) - weekOffset + weekOffsetNext) / 7;
}

// FORMATTING

addFormatToken('w', ['ww', 2], 'wo', 'week');
addFormatToken('W', ['WW', 2], 'Wo', 'isoWeek');

// ALIASES

addUnitAlias('week', 'w');
addUnitAlias('isoWeek', 'W');

// PRIORITIES

addUnitPriority('week', 5);
addUnitPriority('isoWeek', 5);

// PARSING

addRegexToken('w',  match1to2);
addRegexToken('ww', match1to2, match2);
addRegexToken('W',  match1to2);
addRegexToken('WW', match1to2, match2);

addWeekParseToken(['w', 'ww', 'W', 'WW'], function (input, week, config, token) {
    week[token.substr(0, 1)] = toInt(input);
});

// HELPERS

// LOCALES

function localeWeek (mom) {
    return weekOfYear(mom, this._week.dow, this._week.doy).week;
}

var defaultLocaleWeek = {
    dow : 0, // Sunday is the first day of the week.
    doy : 6  // The week that contains Jan 1st is the first week of the year.
};

function localeFirstDayOfWeek () {
    return this._week.dow;
}

function localeFirstDayOfYear () {
    return this._week.doy;
}

// MOMENTS

function getSetWeek (input) {
    var week = this.localeData().week(this);
    return input == null ? week : this.add((input - week) * 7, 'd');
}

function getSetISOWeek (input) {
    var week = weekOfYear(this, 1, 4).week;
    return input == null ? week : this.add((input - week) * 7, 'd');
}

// FORMATTING

addFormatToken('d', 0, 'do', 'day');

addFormatToken('dd', 0, 0, function (format) {
    return this.localeData().weekdaysMin(this, format);
});

addFormatToken('ddd', 0, 0, function (format) {
    return this.localeData().weekdaysShort(this, format);
});

addFormatToken('dddd', 0, 0, function (format) {
    return this.localeData().weekdays(this, format);
});

addFormatToken('e', 0, 0, 'weekday');
addFormatToken('E', 0, 0, 'isoWeekday');

// ALIASES

addUnitAlias('day', 'd');
addUnitAlias('weekday', 'e');
addUnitAlias('isoWeekday', 'E');

// PRIORITY
addUnitPriority('day', 11);
addUnitPriority('weekday', 11);
addUnitPriority('isoWeekday', 11);

// PARSING

addRegexToken('d',    match1to2);
addRegexToken('e',    match1to2);
addRegexToken('E',    match1to2);
addRegexToken('dd',   function (isStrict, locale) {
    return locale.weekdaysMinRegex(isStrict);
});
addRegexToken('ddd',   function (isStrict, locale) {
    return locale.weekdaysShortRegex(isStrict);
});
addRegexToken('dddd',   function (isStrict, locale) {
    return locale.weekdaysRegex(isStrict);
});

addWeekParseToken(['dd', 'ddd', 'dddd'], function (input, week, config, token) {
    var weekday = config._locale.weekdaysParse(input, token, config._strict);
    // if we didn't get a weekday name, mark the date as invalid
    if (weekday != null) {
        week.d = weekday;
    } else {
        getParsingFlags(config).invalidWeekday = input;
    }
});

addWeekParseToken(['d', 'e', 'E'], function (input, week, config, token) {
    week[token] = toInt(input);
});

// HELPERS

function parseWeekday(input, locale) {
    if (typeof input !== 'string') {
        return input;
    }

    if (!isNaN(input)) {
        return parseInt(input, 10);
    }

    input = locale.weekdaysParse(input);
    if (typeof input === 'number') {
        return input;
    }

    return null;
}

function parseIsoWeekday(input, locale) {
    if (typeof input === 'string') {
        return locale.weekdaysParse(input) % 7 || 7;
    }
    return isNaN(input) ? null : input;
}

// LOCALES

var defaultLocaleWeekdays = 'Sunday_Monday_Tuesday_Wednesday_Thursday_Friday_Saturday'.split('_');
function localeWeekdays (m, format) {
    if (!m) {
        return isArray(this._weekdays) ? this._weekdays :
            this._weekdays['standalone'];
    }
    return isArray(this._weekdays) ? this._weekdays[m.day()] :
        this._weekdays[this._weekdays.isFormat.test(format) ? 'format' : 'standalone'][m.day()];
}

var defaultLocaleWeekdaysShort = 'Sun_Mon_Tue_Wed_Thu_Fri_Sat'.split('_');
function localeWeekdaysShort (m) {
    return (m) ? this._weekdaysShort[m.day()] : this._weekdaysShort;
}

var defaultLocaleWeekdaysMin = 'Su_Mo_Tu_We_Th_Fr_Sa'.split('_');
function localeWeekdaysMin (m) {
    return (m) ? this._weekdaysMin[m.day()] : this._weekdaysMin;
}

function handleStrictParse$1(weekdayName, format, strict) {
    var i, ii, mom, llc = weekdayName.toLocaleLowerCase();
    if (!this._weekdaysParse) {
        this._weekdaysParse = [];
        this._shortWeekdaysParse = [];
        this._minWeekdaysParse = [];

        for (i = 0; i < 7; ++i) {
            mom = createUTC([2000, 1]).day(i);
            this._minWeekdaysParse[i] = this.weekdaysMin(mom, '').toLocaleLowerCase();
            this._shortWeekdaysParse[i] = this.weekdaysShort(mom, '').toLocaleLowerCase();
            this._weekdaysParse[i] = this.weekdays(mom, '').toLocaleLowerCase();
        }
    }

    if (strict) {
        if (format === 'dddd') {
            ii = indexOf.call(this._weekdaysParse, llc);
            return ii !== -1 ? ii : null;
        } else if (format === 'ddd') {
            ii = indexOf.call(this._shortWeekdaysParse, llc);
            return ii !== -1 ? ii : null;
        } else {
            ii = indexOf.call(this._minWeekdaysParse, llc);
            return ii !== -1 ? ii : null;
        }
    } else {
        if (format === 'dddd') {
            ii = indexOf.call(this._weekdaysParse, llc);
            if (ii !== -1) {
                return ii;
            }
            ii = indexOf.call(this._shortWeekdaysParse, llc);
            if (ii !== -1) {
                return ii;
            }
            ii = indexOf.call(this._minWeekdaysParse, llc);
            return ii !== -1 ? ii : null;
        } else if (format === 'ddd') {
            ii = indexOf.call(this._shortWeekdaysParse, llc);
            if (ii !== -1) {
                return ii;
            }
            ii = indexOf.call(this._weekdaysParse, llc);
            if (ii !== -1) {
                return ii;
            }
            ii = indexOf.call(this._minWeekdaysParse, llc);
            return ii !== -1 ? ii : null;
        } else {
            ii = indexOf.call(this._minWeekdaysParse, llc);
            if (ii !== -1) {
                return ii;
            }
            ii = indexOf.call(this._weekdaysParse, llc);
            if (ii !== -1) {
                return ii;
            }
            ii = indexOf.call(this._shortWeekdaysParse, llc);
            return ii !== -1 ? ii : null;
        }
    }
}

function localeWeekdaysParse (weekdayName, format, strict) {
    var i, mom, regex;

    if (this._weekdaysParseExact) {
        return handleStrictParse$1.call(this, weekdayName, format, strict);
    }

    if (!this._weekdaysParse) {
        this._weekdaysParse = [];
        this._minWeekdaysParse = [];
        this._shortWeekdaysParse = [];
        this._fullWeekdaysParse = [];
    }

    for (i = 0; i < 7; i++) {
        // make the regex if we don't have it already

        mom = createUTC([2000, 1]).day(i);
        if (strict && !this._fullWeekdaysParse[i]) {
            this._fullWeekdaysParse[i] = new RegExp('^' + this.weekdays(mom, '').replace('.', '\.?') + '$', 'i');
            this._shortWeekdaysParse[i] = new RegExp('^' + this.weekdaysShort(mom, '').replace('.', '\.?') + '$', 'i');
            this._minWeekdaysParse[i] = new RegExp('^' + this.weekdaysMin(mom, '').replace('.', '\.?') + '$', 'i');
        }
        if (!this._weekdaysParse[i]) {
            regex = '^' + this.weekdays(mom, '') + '|^' + this.weekdaysShort(mom, '') + '|^' + this.weekdaysMin(mom, '');
            this._weekdaysParse[i] = new RegExp(regex.replace('.', ''), 'i');
        }
        // test the regex
        if (strict && format === 'dddd' && this._fullWeekdaysParse[i].test(weekdayName)) {
            return i;
        } else if (strict && format === 'ddd' && this._shortWeekdaysParse[i].test(weekdayName)) {
            return i;
        } else if (strict && format === 'dd' && this._minWeekdaysParse[i].test(weekdayName)) {
            return i;
        } else if (!strict && this._weekdaysParse[i].test(weekdayName)) {
            return i;
        }
    }
}

// MOMENTS

function getSetDayOfWeek (input) {
    if (!this.isValid()) {
        return input != null ? this : NaN;
    }
    var day = this._isUTC ? this._d.getUTCDay() : this._d.getDay();
    if (input != null) {
        input = parseWeekday(input, this.localeData());
        return this.add(input - day, 'd');
    } else {
        return day;
    }
}

function getSetLocaleDayOfWeek (input) {
    if (!this.isValid()) {
        return input != null ? this : NaN;
    }
    var weekday = (this.day() + 7 - this.localeData()._week.dow) % 7;
    return input == null ? weekday : this.add(input - weekday, 'd');
}

function getSetISODayOfWeek (input) {
    if (!this.isValid()) {
        return input != null ? this : NaN;
    }

    // behaves the same as moment#day except
    // as a getter, returns 7 instead of 0 (1-7 range instead of 0-6)
    // as a setter, sunday should belong to the previous week.

    if (input != null) {
        var weekday = parseIsoWeekday(input, this.localeData());
        return this.day(this.day() % 7 ? weekday : weekday - 7);
    } else {
        return this.day() || 7;
    }
}

var defaultWeekdaysRegex = matchWord;
function weekdaysRegex (isStrict) {
    if (this._weekdaysParseExact) {
        if (!hasOwnProp(this, '_weekdaysRegex')) {
            computeWeekdaysParse.call(this);
        }
        if (isStrict) {
            return this._weekdaysStrictRegex;
        } else {
            return this._weekdaysRegex;
        }
    } else {
        if (!hasOwnProp(this, '_weekdaysRegex')) {
            this._weekdaysRegex = defaultWeekdaysRegex;
        }
        return this._weekdaysStrictRegex && isStrict ?
            this._weekdaysStrictRegex : this._weekdaysRegex;
    }
}

var defaultWeekdaysShortRegex = matchWord;
function weekdaysShortRegex (isStrict) {
    if (this._weekdaysParseExact) {
        if (!hasOwnProp(this, '_weekdaysRegex')) {
            computeWeekdaysParse.call(this);
        }
        if (isStrict) {
            return this._weekdaysShortStrictRegex;
        } else {
            return this._weekdaysShortRegex;
        }
    } else {
        if (!hasOwnProp(this, '_weekdaysShortRegex')) {
            this._weekdaysShortRegex = defaultWeekdaysShortRegex;
        }
        return this._weekdaysShortStrictRegex && isStrict ?
            this._weekdaysShortStrictRegex : this._weekdaysShortRegex;
    }
}

var defaultWeekdaysMinRegex = matchWord;
function weekdaysMinRegex (isStrict) {
    if (this._weekdaysParseExact) {
        if (!hasOwnProp(this, '_weekdaysRegex')) {
            computeWeekdaysParse.call(this);
        }
        if (isStrict) {
            return this._weekdaysMinStrictRegex;
        } else {
            return this._weekdaysMinRegex;
        }
    } else {
        if (!hasOwnProp(this, '_weekdaysMinRegex')) {
            this._weekdaysMinRegex = defaultWeekdaysMinRegex;
        }
        return this._weekdaysMinStrictRegex && isStrict ?
            this._weekdaysMinStrictRegex : this._weekdaysMinRegex;
    }
}


function computeWeekdaysParse () {
    function cmpLenRev(a, b) {
        return b.length - a.length;
    }

    var minPieces = [], shortPieces = [], longPieces = [], mixedPieces = [],
        i, mom, minp, shortp, longp;
    for (i = 0; i < 7; i++) {
        // make the regex if we don't have it already
        mom = createUTC([2000, 1]).day(i);
        minp = this.weekdaysMin(mom, '');
        shortp = this.weekdaysShort(mom, '');
        longp = this.weekdays(mom, '');
        minPieces.push(minp);
        shortPieces.push(shortp);
        longPieces.push(longp);
        mixedPieces.push(minp);
        mixedPieces.push(shortp);
        mixedPieces.push(longp);
    }
    // Sorting makes sure if one weekday (or abbr) is a prefix of another it
    // will match the longer piece.
    minPieces.sort(cmpLenRev);
    shortPieces.sort(cmpLenRev);
    longPieces.sort(cmpLenRev);
    mixedPieces.sort(cmpLenRev);
    for (i = 0; i < 7; i++) {
        shortPieces[i] = regexEscape(shortPieces[i]);
        longPieces[i] = regexEscape(longPieces[i]);
        mixedPieces[i] = regexEscape(mixedPieces[i]);
    }

    this._weekdaysRegex = new RegExp('^(' + mixedPieces.join('|') + ')', 'i');
    this._weekdaysShortRegex = this._weekdaysRegex;
    this._weekdaysMinRegex = this._weekdaysRegex;

    this._weekdaysStrictRegex = new RegExp('^(' + longPieces.join('|') + ')', 'i');
    this._weekdaysShortStrictRegex = new RegExp('^(' + shortPieces.join('|') + ')', 'i');
    this._weekdaysMinStrictRegex = new RegExp('^(' + minPieces.join('|') + ')', 'i');
}

// FORMATTING

function hFormat() {
    return this.hours() % 12 || 12;
}

function kFormat() {
    return this.hours() || 24;
}

addFormatToken('H', ['HH', 2], 0, 'hour');
addFormatToken('h', ['hh', 2], 0, hFormat);
addFormatToken('k', ['kk', 2], 0, kFormat);

addFormatToken('hmm', 0, 0, function () {
    return '' + hFormat.apply(this) + zeroFill(this.minutes(), 2);
});

addFormatToken('hmmss', 0, 0, function () {
    return '' + hFormat.apply(this) + zeroFill(this.minutes(), 2) +
        zeroFill(this.seconds(), 2);
});

addFormatToken('Hmm', 0, 0, function () {
    return '' + this.hours() + zeroFill(this.minutes(), 2);
});

addFormatToken('Hmmss', 0, 0, function () {
    return '' + this.hours() + zeroFill(this.minutes(), 2) +
        zeroFill(this.seconds(), 2);
});

function meridiem (token, lowercase) {
    addFormatToken(token, 0, 0, function () {
        return this.localeData().meridiem(this.hours(), this.minutes(), lowercase);
    });
}

meridiem('a', true);
meridiem('A', false);

// ALIASES

addUnitAlias('hour', 'h');

// PRIORITY
addUnitPriority('hour', 13);

// PARSING

function matchMeridiem (isStrict, locale) {
    return locale._meridiemParse;
}

addRegexToken('a',  matchMeridiem);
addRegexToken('A',  matchMeridiem);
addRegexToken('H',  match1to2);
addRegexToken('h',  match1to2);
addRegexToken('k',  match1to2);
addRegexToken('HH', match1to2, match2);
addRegexToken('hh', match1to2, match2);
addRegexToken('kk', match1to2, match2);

addRegexToken('hmm', match3to4);
addRegexToken('hmmss', match5to6);
addRegexToken('Hmm', match3to4);
addRegexToken('Hmmss', match5to6);

addParseToken(['H', 'HH'], HOUR);
addParseToken(['k', 'kk'], function (input, array, config) {
    var kInput = toInt(input);
    array[HOUR] = kInput === 24 ? 0 : kInput;
});
addParseToken(['a', 'A'], function (input, array, config) {
    config._isPm = config._locale.isPM(input);
    config._meridiem = input;
});
addParseToken(['h', 'hh'], function (input, array, config) {
    array[HOUR] = toInt(input);
    getParsingFlags(config).bigHour = true;
});
addParseToken('hmm', function (input, array, config) {
    var pos = input.length - 2;
    array[HOUR] = toInt(input.substr(0, pos));
    array[MINUTE] = toInt(input.substr(pos));
    getParsingFlags(config).bigHour = true;
});
addParseToken('hmmss', function (input, array, config) {
    var pos1 = input.length - 4;
    var pos2 = input.length - 2;
    array[HOUR] = toInt(input.substr(0, pos1));
    array[MINUTE] = toInt(input.substr(pos1, 2));
    array[SECOND] = toInt(input.substr(pos2));
    getParsingFlags(config).bigHour = true;
});
addParseToken('Hmm', function (input, array, config) {
    var pos = input.length - 2;
    array[HOUR] = toInt(input.substr(0, pos));
    array[MINUTE] = toInt(input.substr(pos));
});
addParseToken('Hmmss', function (input, array, config) {
    var pos1 = input.length - 4;
    var pos2 = input.length - 2;
    array[HOUR] = toInt(input.substr(0, pos1));
    array[MINUTE] = toInt(input.substr(pos1, 2));
    array[SECOND] = toInt(input.substr(pos2));
});

// LOCALES

function localeIsPM (input) {
    // IE8 Quirks Mode & IE7 Standards Mode do not allow accessing strings like arrays
    // Using charAt should be more compatible.
    return ((input + '').toLowerCase().charAt(0) === 'p');
}

var defaultLocaleMeridiemParse = /[ap]\.?m?\.?/i;
function localeMeridiem (hours, minutes, isLower) {
    if (hours > 11) {
        return isLower ? 'pm' : 'PM';
    } else {
        return isLower ? 'am' : 'AM';
    }
}


// MOMENTS

// Setting the hour should keep the time, because the user explicitly
// specified which hour he wants. So trying to maintain the same hour (in
// a new timezone) makes sense. Adding/subtracting hours does not follow
// this rule.
var getSetHour = makeGetSet('Hours', true);

// months
// week
// weekdays
// meridiem
var baseConfig = {
    calendar: defaultCalendar,
    longDateFormat: defaultLongDateFormat,
    invalidDate: defaultInvalidDate,
    ordinal: defaultOrdinal,
    dayOfMonthOrdinalParse: defaultDayOfMonthOrdinalParse,
    relativeTime: defaultRelativeTime,

    months: defaultLocaleMonths,
    monthsShort: defaultLocaleMonthsShort,

    week: defaultLocaleWeek,

    weekdays: defaultLocaleWeekdays,
    weekdaysMin: defaultLocaleWeekdaysMin,
    weekdaysShort: defaultLocaleWeekdaysShort,

    meridiemParse: defaultLocaleMeridiemParse
};

// internal storage for locale config files
var locales = {};
var localeFamilies = {};
var globalLocale;

function normalizeLocale(key) {
    return key ? key.toLowerCase().replace('_', '-') : key;
}

// pick the locale from the array
// try ['en-au', 'en-gb'] as 'en-au', 'en-gb', 'en', as in move through the list trying each
// substring from most specific to least, but move to the next array item if it's a more specific variant than the current root
function chooseLocale(names) {
    var i = 0, j, next, locale, split;

    while (i < names.length) {
        split = normalizeLocale(names[i]).split('-');
        j = split.length;
        next = normalizeLocale(names[i + 1]);
        next = next ? next.split('-') : null;
        while (j > 0) {
            locale = loadLocale(split.slice(0, j).join('-'));
            if (locale) {
                return locale;
            }
            if (next && next.length >= j && compareArrays(split, next, true) >= j - 1) {
                //the next array item is better than a shallower substring of this one
                break;
            }
            j--;
        }
        i++;
    }
    return null;
}

function loadLocale(name) {
    var oldLocale = null;
    // TODO: Find a better way to register and load all the locales in Node
    if (!locales[name] && (typeof module !== 'undefined') &&
            module && module.exports) {
        try {
            oldLocale = globalLocale._abbr;
            var aliasedRequire = require;
            aliasedRequire('./locale/' + name);
            getSetGlobalLocale(oldLocale);
        } catch (e) {}
    }
    return locales[name];
}

// This function will load locale and then set the global locale.  If
// no arguments are passed in, it will simply return the current global
// locale key.
function getSetGlobalLocale (key, values) {
    var data;
    if (key) {
        if (isUndefined(values)) {
            data = getLocale(key);
        }
        else {
            data = defineLocale(key, values);
        }

        if (data) {
            // moment.duration._locale = moment._locale = data;
            globalLocale = data;
        }
    }

    return globalLocale._abbr;
}

function defineLocale (name, config) {
    if (config !== null) {
        var parentConfig = baseConfig;
        config.abbr = name;
        if (locales[name] != null) {
            deprecateSimple('defineLocaleOverride',
                    'use moment.updateLocale(localeName, config) to change ' +
                    'an existing locale. moment.defineLocale(localeName, ' +
                    'config) should only be used for creating a new locale ' +
                    'See http://momentjs.com/guides/#/warnings/define-locale/ for more info.');
            parentConfig = locales[name]._config;
        } else if (config.parentLocale != null) {
            if (locales[config.parentLocale] != null) {
                parentConfig = locales[config.parentLocale]._config;
            } else {
                if (!localeFamilies[config.parentLocale]) {
                    localeFamilies[config.parentLocale] = [];
                }
                localeFamilies[config.parentLocale].push({
                    name: name,
                    config: config
                });
                return null;
            }
        }
        locales[name] = new Locale(mergeConfigs(parentConfig, config));

        if (localeFamilies[name]) {
            localeFamilies[name].forEach(function (x) {
                defineLocale(x.name, x.config);
            });
        }

        // backwards compat for now: also set the locale
        // make sure we set the locale AFTER all child locales have been
        // created, so we won't end up with the child locale set.
        getSetGlobalLocale(name);


        return locales[name];
    } else {
        // useful for testing
        delete locales[name];
        return null;
    }
}

function updateLocale(name, config) {
    if (config != null) {
        var locale, tmpLocale, parentConfig = baseConfig;
        // MERGE
        tmpLocale = loadLocale(name);
        if (tmpLocale != null) {
            parentConfig = tmpLocale._config;
        }
        config = mergeConfigs(parentConfig, config);
        locale = new Locale(config);
        locale.parentLocale = locales[name];
        locales[name] = locale;

        // backwards compat for now: also set the locale
        getSetGlobalLocale(name);
    } else {
        // pass null for config to unupdate, useful for tests
        if (locales[name] != null) {
            if (locales[name].parentLocale != null) {
                locales[name] = locales[name].parentLocale;
            } else if (locales[name] != null) {
                delete locales[name];
            }
        }
    }
    return locales[name];
}

// returns locale data
function getLocale (key) {
    var locale;

    if (key && key._locale && key._locale._abbr) {
        key = key._locale._abbr;
    }

    if (!key) {
        return globalLocale;
    }

    if (!isArray(key)) {
        //short-circuit everything else
        locale = loadLocale(key);
        if (locale) {
            return locale;
        }
        key = [key];
    }

    return chooseLocale(key);
}

function listLocales() {
    return keys(locales);
}

function checkOverflow (m) {
    var overflow;
    var a = m._a;

    if (a && getParsingFlags(m).overflow === -2) {
        overflow =
            a[MONTH]       < 0 || a[MONTH]       > 11  ? MONTH :
            a[DATE]        < 1 || a[DATE]        > daysInMonth(a[YEAR], a[MONTH]) ? DATE :
            a[HOUR]        < 0 || a[HOUR]        > 24 || (a[HOUR] === 24 && (a[MINUTE] !== 0 || a[SECOND] !== 0 || a[MILLISECOND] !== 0)) ? HOUR :
            a[MINUTE]      < 0 || a[MINUTE]      > 59  ? MINUTE :
            a[SECOND]      < 0 || a[SECOND]      > 59  ? SECOND :
            a[MILLISECOND] < 0 || a[MILLISECOND] > 999 ? MILLISECOND :
            -1;

        if (getParsingFlags(m)._overflowDayOfYear && (overflow < YEAR || overflow > DATE)) {
            overflow = DATE;
        }
        if (getParsingFlags(m)._overflowWeeks && overflow === -1) {
            overflow = WEEK;
        }
        if (getParsingFlags(m)._overflowWeekday && overflow === -1) {
            overflow = WEEKDAY;
        }

        getParsingFlags(m).overflow = overflow;
    }

    return m;
}

// Pick the first defined of two or three arguments.
function defaults(a, b, c) {
    if (a != null) {
        return a;
    }
    if (b != null) {
        return b;
    }
    return c;
}

function currentDateArray(config) {
    // hooks is actually the exported moment object
    var nowValue = new Date(hooks.now());
    if (config._useUTC) {
        return [nowValue.getUTCFullYear(), nowValue.getUTCMonth(), nowValue.getUTCDate()];
    }
    return [nowValue.getFullYear(), nowValue.getMonth(), nowValue.getDate()];
}

// convert an array to a date.
// the array should mirror the parameters below
// note: all values past the year are optional and will default to the lowest possible value.
// [year, month, day , hour, minute, second, millisecond]
function configFromArray (config) {
    var i, date, input = [], currentDate, expectedWeekday, yearToUse;

    if (config._d) {
        return;
    }

    currentDate = currentDateArray(config);

    //compute day of the year from weeks and weekdays
    if (config._w && config._a[DATE] == null && config._a[MONTH] == null) {
        dayOfYearFromWeekInfo(config);
    }

    //if the day of the year is set, figure out what it is
    if (config._dayOfYear != null) {
        yearToUse = defaults(config._a[YEAR], currentDate[YEAR]);

        if (config._dayOfYear > daysInYear(yearToUse) || config._dayOfYear === 0) {
            getParsingFlags(config)._overflowDayOfYear = true;
        }

        date = createUTCDate(yearToUse, 0, config._dayOfYear);
        config._a[MONTH] = date.getUTCMonth();
        config._a[DATE] = date.getUTCDate();
    }

    // Default to current date.
    // * if no year, month, day of month are given, default to today
    // * if day of month is given, default month and year
    // * if month is given, default only year
    // * if year is given, don't default anything
    for (i = 0; i < 3 && config._a[i] == null; ++i) {
        config._a[i] = input[i] = currentDate[i];
    }

    // Zero out whatever was not defaulted, including time
    for (; i < 7; i++) {
        config._a[i] = input[i] = (config._a[i] == null) ? (i === 2 ? 1 : 0) : config._a[i];
    }

    // Check for 24:00:00.000
    if (config._a[HOUR] === 24 &&
            config._a[MINUTE] === 0 &&
            config._a[SECOND] === 0 &&
            config._a[MILLISECOND] === 0) {
        config._nextDay = true;
        config._a[HOUR] = 0;
    }

    config._d = (config._useUTC ? createUTCDate : createDate).apply(null, input);
    expectedWeekday = config._useUTC ? config._d.getUTCDay() : config._d.getDay();

    // Apply timezone offset from input. The actual utcOffset can be changed
    // with parseZone.
    if (config._tzm != null) {
        config._d.setUTCMinutes(config._d.getUTCMinutes() - config._tzm);
    }

    if (config._nextDay) {
        config._a[HOUR] = 24;
    }

    // check for mismatching day of week
    if (config._w && typeof config._w.d !== 'undefined' && config._w.d !== expectedWeekday) {
        getParsingFlags(config).weekdayMismatch = true;
    }
}

function dayOfYearFromWeekInfo(config) {
    var w, weekYear, week, weekday, dow, doy, temp, weekdayOverflow;

    w = config._w;
    if (w.GG != null || w.W != null || w.E != null) {
        dow = 1;
        doy = 4;

        // TODO: We need to take the current isoWeekYear, but that depends on
        // how we interpret now (local, utc, fixed offset). So create
        // a now version of current config (take local/utc/offset flags, and
        // create now).
        weekYear = defaults(w.GG, config._a[YEAR], weekOfYear(createLocal(), 1, 4).year);
        week = defaults(w.W, 1);
        weekday = defaults(w.E, 1);
        if (weekday < 1 || weekday > 7) {
            weekdayOverflow = true;
        }
    } else {
        dow = config._locale._week.dow;
        doy = config._locale._week.doy;

        var curWeek = weekOfYear(createLocal(), dow, doy);

        weekYear = defaults(w.gg, config._a[YEAR], curWeek.year);

        // Default to current week.
        week = defaults(w.w, curWeek.week);

        if (w.d != null) {
            // weekday -- low day numbers are considered next week
            weekday = w.d;
            if (weekday < 0 || weekday > 6) {
                weekdayOverflow = true;
            }
        } else if (w.e != null) {
            // local weekday -- counting starts from begining of week
            weekday = w.e + dow;
            if (w.e < 0 || w.e > 6) {
                weekdayOverflow = true;
            }
        } else {
            // default to begining of week
            weekday = dow;
        }
    }
    if (week < 1 || week > weeksInYear(weekYear, dow, doy)) {
        getParsingFlags(config)._overflowWeeks = true;
    } else if (weekdayOverflow != null) {
        getParsingFlags(config)._overflowWeekday = true;
    } else {
        temp = dayOfYearFromWeeks(weekYear, week, weekday, dow, doy);
        config._a[YEAR] = temp.year;
        config._dayOfYear = temp.dayOfYear;
    }
}

// iso 8601 regex
// 0000-00-00 0000-W00 or 0000-W00-0 + T + 00 or 00:00 or 00:00:00 or 00:00:00.000 + +00:00 or +0000 or +00)
var extendedIsoRegex = /^\s*((?:[+-]\d{6}|\d{4})-(?:\d\d-\d\d|W\d\d-\d|W\d\d|\d\d\d|\d\d))(?:(T| )(\d\d(?::\d\d(?::\d\d(?:[.,]\d+)?)?)?)([\+\-]\d\d(?::?\d\d)?|\s*Z)?)?$/;
var basicIsoRegex = /^\s*((?:[+-]\d{6}|\d{4})(?:\d\d\d\d|W\d\d\d|W\d\d|\d\d\d|\d\d))(?:(T| )(\d\d(?:\d\d(?:\d\d(?:[.,]\d+)?)?)?)([\+\-]\d\d(?::?\d\d)?|\s*Z)?)?$/;

var tzRegex = /Z|[+-]\d\d(?::?\d\d)?/;

var isoDates = [
    ['YYYYYY-MM-DD', /[+-]\d{6}-\d\d-\d\d/],
    ['YYYY-MM-DD', /\d{4}-\d\d-\d\d/],
    ['GGGG-[W]WW-E', /\d{4}-W\d\d-\d/],
    ['GGGG-[W]WW', /\d{4}-W\d\d/, false],
    ['YYYY-DDD', /\d{4}-\d{3}/],
    ['YYYY-MM', /\d{4}-\d\d/, false],
    ['YYYYYYMMDD', /[+-]\d{10}/],
    ['YYYYMMDD', /\d{8}/],
    // YYYYMM is NOT allowed by the standard
    ['GGGG[W]WWE', /\d{4}W\d{3}/],
    ['GGGG[W]WW', /\d{4}W\d{2}/, false],
    ['YYYYDDD', /\d{7}/]
];

// iso time formats and regexes
var isoTimes = [
    ['HH:mm:ss.SSSS', /\d\d:\d\d:\d\d\.\d+/],
    ['HH:mm:ss,SSSS', /\d\d:\d\d:\d\d,\d+/],
    ['HH:mm:ss', /\d\d:\d\d:\d\d/],
    ['HH:mm', /\d\d:\d\d/],
    ['HHmmss.SSSS', /\d\d\d\d\d\d\.\d+/],
    ['HHmmss,SSSS', /\d\d\d\d\d\d,\d+/],
    ['HHmmss', /\d\d\d\d\d\d/],
    ['HHmm', /\d\d\d\d/],
    ['HH', /\d\d/]
];

var aspNetJsonRegex = /^\/?Date\((\-?\d+)/i;

// date from iso format
function configFromISO(config) {
    var i, l,
        string = config._i,
        match = extendedIsoRegex.exec(string) || basicIsoRegex.exec(string),
        allowTime, dateFormat, timeFormat, tzFormat;

    if (match) {
        getParsingFlags(config).iso = true;

        for (i = 0, l = isoDates.length; i < l; i++) {
            if (isoDates[i][1].exec(match[1])) {
                dateFormat = isoDates[i][0];
                allowTime = isoDates[i][2] !== false;
                break;
            }
        }
        if (dateFormat == null) {
            config._isValid = false;
            return;
        }
        if (match[3]) {
            for (i = 0, l = isoTimes.length; i < l; i++) {
                if (isoTimes[i][1].exec(match[3])) {
                    // match[2] should be 'T' or space
                    timeFormat = (match[2] || ' ') + isoTimes[i][0];
                    break;
                }
            }
            if (timeFormat == null) {
                config._isValid = false;
                return;
            }
        }
        if (!allowTime && timeFormat != null) {
            config._isValid = false;
            return;
        }
        if (match[4]) {
            if (tzRegex.exec(match[4])) {
                tzFormat = 'Z';
            } else {
                config._isValid = false;
                return;
            }
        }
        config._f = dateFormat + (timeFormat || '') + (tzFormat || '');
        configFromStringAndFormat(config);
    } else {
        config._isValid = false;
    }
}

// RFC 2822 regex: For details see https://tools.ietf.org/html/rfc2822#section-3.3
var rfc2822 = /^(?:(Mon|Tue|Wed|Thu|Fri|Sat|Sun),?\s)?(\d{1,2})\s(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s(\d{2,4})\s(\d\d):(\d\d)(?::(\d\d))?\s(?:(UT|GMT|[ECMP][SD]T)|([Zz])|([+-]\d{4}))$/;

function extractFromRFC2822Strings(yearStr, monthStr, dayStr, hourStr, minuteStr, secondStr) {
    var result = [
        untruncateYear(yearStr),
        defaultLocaleMonthsShort.indexOf(monthStr),
        parseInt(dayStr, 10),
        parseInt(hourStr, 10),
        parseInt(minuteStr, 10)
    ];

    if (secondStr) {
        result.push(parseInt(secondStr, 10));
    }

    return result;
}

function untruncateYear(yearStr) {
    var year = parseInt(yearStr, 10);
    if (year <= 49) {
        return 2000 + year;
    } else if (year <= 999) {
        return 1900 + year;
    }
    return year;
}

function preprocessRFC2822(s) {
    // Remove comments and folding whitespace and replace multiple-spaces with a single space
    return s.replace(/\([^)]*\)|[\n\t]/g, ' ').replace(/(\s\s+)/g, ' ').trim();
}

function checkWeekday(weekdayStr, parsedInput, config) {
    if (weekdayStr) {
        // TODO: Replace the vanilla JS Date object with an indepentent day-of-week check.
        var weekdayProvided = defaultLocaleWeekdaysShort.indexOf(weekdayStr),
            weekdayActual = new Date(parsedInput[0], parsedInput[1], parsedInput[2]).getDay();
        if (weekdayProvided !== weekdayActual) {
            getParsingFlags(config).weekdayMismatch = true;
            config._isValid = false;
            return false;
        }
    }
    return true;
}

var obsOffsets = {
    UT: 0,
    GMT: 0,
    EDT: -4 * 60,
    EST: -5 * 60,
    CDT: -5 * 60,
    CST: -6 * 60,
    MDT: -6 * 60,
    MST: -7 * 60,
    PDT: -7 * 60,
    PST: -8 * 60
};

function calculateOffset(obsOffset, militaryOffset, numOffset) {
    if (obsOffset) {
        return obsOffsets[obsOffset];
    } else if (militaryOffset) {
        // the only allowed military tz is Z
        return 0;
    } else {
        var hm = parseInt(numOffset, 10);
        var m = hm % 100, h = (hm - m) / 100;
        return h * 60 + m;
    }
}

// date and time from ref 2822 format
function configFromRFC2822(config) {
    var match = rfc2822.exec(preprocessRFC2822(config._i));
    if (match) {
        var parsedArray = extractFromRFC2822Strings(match[4], match[3], match[2], match[5], match[6], match[7]);
        if (!checkWeekday(match[1], parsedArray, config)) {
            return;
        }

        config._a = parsedArray;
        config._tzm = calculateOffset(match[8], match[9], match[10]);

        config._d = createUTCDate.apply(null, config._a);
        config._d.setUTCMinutes(config._d.getUTCMinutes() - config._tzm);

        getParsingFlags(config).rfc2822 = true;
    } else {
        config._isValid = false;
    }
}

// date from iso format or fallback
function configFromString(config) {
    var matched = aspNetJsonRegex.exec(config._i);

    if (matched !== null) {
        config._d = new Date(+matched[1]);
        return;
    }

    configFromISO(config);
    if (config._isValid === false) {
        delete config._isValid;
    } else {
        return;
    }

    configFromRFC2822(config);
    if (config._isValid === false) {
        delete config._isValid;
    } else {
        return;
    }

    // Final attempt, use Input Fallback
    hooks.createFromInputFallback(config);
}

hooks.createFromInputFallback = deprecate(
    'value provided is not in a recognized RFC2822 or ISO format. moment construction falls back to js Date(), ' +
    'which is not reliable across all browsers and versions. Non RFC2822/ISO date formats are ' +
    'discouraged and will be removed in an upcoming major release. Please refer to ' +
    'http://momentjs.com/guides/#/warnings/js-date/ for more info.',
    function (config) {
        config._d = new Date(config._i + (config._useUTC ? ' UTC' : ''));
    }
);

// constant that refers to the ISO standard
hooks.ISO_8601 = function () {};

// constant that refers to the RFC 2822 form
hooks.RFC_2822 = function () {};

// date from string and format string
function configFromStringAndFormat(config) {
    // TODO: Move this to another part of the creation flow to prevent circular deps
    if (config._f === hooks.ISO_8601) {
        configFromISO(config);
        return;
    }
    if (config._f === hooks.RFC_2822) {
        configFromRFC2822(config);
        return;
    }
    config._a = [];
    getParsingFlags(config).empty = true;

    // This array is used to make a Date, either with `new Date` or `Date.UTC`
    var string = '' + config._i,
        i, parsedInput, tokens, token, skipped,
        stringLength = string.length,
        totalParsedInputLength = 0;

    tokens = expandFormat(config._f, config._locale).match(formattingTokens) || [];

    for (i = 0; i < tokens.length; i++) {
        token = tokens[i];
        parsedInput = (string.match(getParseRegexForToken(token, config)) || [])[0];
        // console.log('token', token, 'parsedInput', parsedInput,
        //         'regex', getParseRegexForToken(token, config));
        if (parsedInput) {
            skipped = string.substr(0, string.indexOf(parsedInput));
            if (skipped.length > 0) {
                getParsingFlags(config).unusedInput.push(skipped);
            }
            string = string.slice(string.indexOf(parsedInput) + parsedInput.length);
            totalParsedInputLength += parsedInput.length;
        }
        // don't parse if it's not a known token
        if (formatTokenFunctions[token]) {
            if (parsedInput) {
                getParsingFlags(config).empty = false;
            }
            else {
                getParsingFlags(config).unusedTokens.push(token);
            }
            addTimeToArrayFromToken(token, parsedInput, config);
        }
        else if (config._strict && !parsedInput) {
            getParsingFlags(config).unusedTokens.push(token);
        }
    }

    // add remaining unparsed input length to the string
    getParsingFlags(config).charsLeftOver = stringLength - totalParsedInputLength;
    if (string.length > 0) {
        getParsingFlags(config).unusedInput.push(string);
    }

    // clear _12h flag if hour is <= 12
    if (config._a[HOUR] <= 12 &&
        getParsingFlags(config).bigHour === true &&
        config._a[HOUR] > 0) {
        getParsingFlags(config).bigHour = undefined;
    }

    getParsingFlags(config).parsedDateParts = config._a.slice(0);
    getParsingFlags(config).meridiem = config._meridiem;
    // handle meridiem
    config._a[HOUR] = meridiemFixWrap(config._locale, config._a[HOUR], config._meridiem);

    configFromArray(config);
    checkOverflow(config);
}


function meridiemFixWrap (locale, hour, meridiem) {
    var isPm;

    if (meridiem == null) {
        // nothing to do
        return hour;
    }
    if (locale.meridiemHour != null) {
        return locale.meridiemHour(hour, meridiem);
    } else if (locale.isPM != null) {
        // Fallback
        isPm = locale.isPM(meridiem);
        if (isPm && hour < 12) {
            hour += 12;
        }
        if (!isPm && hour === 12) {
            hour = 0;
        }
        return hour;
    } else {
        // this is not supposed to happen
        return hour;
    }
}

// date from string and array of format strings
function configFromStringAndArray(config) {
    var tempConfig,
        bestMoment,

        scoreToBeat,
        i,
        currentScore;

    if (config._f.length === 0) {
        getParsingFlags(config).invalidFormat = true;
        config._d = new Date(NaN);
        return;
    }

    for (i = 0; i < config._f.length; i++) {
        currentScore = 0;
        tempConfig = copyConfig({}, config);
        if (config._useUTC != null) {
            tempConfig._useUTC = config._useUTC;
        }
        tempConfig._f = config._f[i];
        configFromStringAndFormat(tempConfig);

        if (!isValid(tempConfig)) {
            continue;
        }

        // if there is any input that was not parsed add a penalty for that format
        currentScore += getParsingFlags(tempConfig).charsLeftOver;

        //or tokens
        currentScore += getParsingFlags(tempConfig).unusedTokens.length * 10;

        getParsingFlags(tempConfig).score = currentScore;

        if (scoreToBeat == null || currentScore < scoreToBeat) {
            scoreToBeat = currentScore;
            bestMoment = tempConfig;
        }
    }

    extend(config, bestMoment || tempConfig);
}

function configFromObject(config) {
    if (config._d) {
        return;
    }

    var i = normalizeObjectUnits(config._i);
    config._a = map([i.year, i.month, i.day || i.date, i.hour, i.minute, i.second, i.millisecond], function (obj) {
        return obj && parseInt(obj, 10);
    });

    configFromArray(config);
}

function createFromConfig (config) {
    var res = new Moment(checkOverflow(prepareConfig(config)));
    if (res._nextDay) {
        // Adding is smart enough around DST
        res.add(1, 'd');
        res._nextDay = undefined;
    }

    return res;
}

function prepareConfig (config) {
    var input = config._i,
        format = config._f;

    config._locale = config._locale || getLocale(config._l);

    if (input === null || (format === undefined && input === '')) {
        return createInvalid({nullInput: true});
    }

    if (typeof input === 'string') {
        config._i = input = config._locale.preparse(input);
    }

    if (isMoment(input)) {
        return new Moment(checkOverflow(input));
    } else if (isDate(input)) {
        config._d = input;
    } else if (isArray(format)) {
        configFromStringAndArray(config);
    } else if (format) {
        configFromStringAndFormat(config);
    }  else {
        configFromInput(config);
    }

    if (!isValid(config)) {
        config._d = null;
    }

    return config;
}

function configFromInput(config) {
    var input = config._i;
    if (isUndefined(input)) {
        config._d = new Date(hooks.now());
    } else if (isDate(input)) {
        config._d = new Date(input.valueOf());
    } else if (typeof input === 'string') {
        configFromString(config);
    } else if (isArray(input)) {
        config._a = map(input.slice(0), function (obj) {
            return parseInt(obj, 10);
        });
        configFromArray(config);
    } else if (isObject(input)) {
        configFromObject(config);
    } else if (isNumber(input)) {
        // from milliseconds
        config._d = new Date(input);
    } else {
        hooks.createFromInputFallback(config);
    }
}

function createLocalOrUTC (input, format, locale, strict, isUTC) {
    var c = {};

    if (locale === true || locale === false) {
        strict = locale;
        locale = undefined;
    }

    if ((isObject(input) && isObjectEmpty(input)) ||
            (isArray(input) && input.length === 0)) {
        input = undefined;
    }
    // object construction must be done this way.
    // https://github.com/moment/moment/issues/1423
    c._isAMomentObject = true;
    c._useUTC = c._isUTC = isUTC;
    c._l = locale;
    c._i = input;
    c._f = format;
    c._strict = strict;

    return createFromConfig(c);
}

function createLocal (input, format, locale, strict) {
    return createLocalOrUTC(input, format, locale, strict, false);
}

var prototypeMin = deprecate(
    'moment().min is deprecated, use moment.max instead. http://momentjs.com/guides/#/warnings/min-max/',
    function () {
        var other = createLocal.apply(null, arguments);
        if (this.isValid() && other.isValid()) {
            return other < this ? this : other;
        } else {
            return createInvalid();
        }
    }
);

var prototypeMax = deprecate(
    'moment().max is deprecated, use moment.min instead. http://momentjs.com/guides/#/warnings/min-max/',
    function () {
        var other = createLocal.apply(null, arguments);
        if (this.isValid() && other.isValid()) {
            return other > this ? this : other;
        } else {
            return createInvalid();
        }
    }
);

// Pick a moment m from moments so that m[fn](other) is true for all
// other. This relies on the function fn to be transitive.
//
// moments should either be an array of moment objects or an array, whose
// first element is an array of moment objects.
function pickBy(fn, moments) {
    var res, i;
    if (moments.length === 1 && isArray(moments[0])) {
        moments = moments[0];
    }
    if (!moments.length) {
        return createLocal();
    }
    res = moments[0];
    for (i = 1; i < moments.length; ++i) {
        if (!moments[i].isValid() || moments[i][fn](res)) {
            res = moments[i];
        }
    }
    return res;
}

// TODO: Use [].sort instead?
function min () {
    var args = [].slice.call(arguments, 0);

    return pickBy('isBefore', args);
}

function max () {
    var args = [].slice.call(arguments, 0);

    return pickBy('isAfter', args);
}

var now = function () {
    return Date.now ? Date.now() : +(new Date());
};

var ordering = ['year', 'quarter', 'month', 'week', 'day', 'hour', 'minute', 'second', 'millisecond'];

function isDurationValid(m) {
    for (var key in m) {
        if (!(indexOf.call(ordering, key) !== -1 && (m[key] == null || !isNaN(m[key])))) {
            return false;
        }
    }

    var unitHasDecimal = false;
    for (var i = 0; i < ordering.length; ++i) {
        if (m[ordering[i]]) {
            if (unitHasDecimal) {
                return false; // only allow non-integers for smallest unit
            }
            if (parseFloat(m[ordering[i]]) !== toInt(m[ordering[i]])) {
                unitHasDecimal = true;
            }
        }
    }

    return true;
}

function isValid$1() {
    return this._isValid;
}

function createInvalid$1() {
    return createDuration(NaN);
}

function Duration (duration) {
    var normalizedInput = normalizeObjectUnits(duration),
        years = normalizedInput.year || 0,
        quarters = normalizedInput.quarter || 0,
        months = normalizedInput.month || 0,
        weeks = normalizedInput.week || 0,
        days = normalizedInput.day || 0,
        hours = normalizedInput.hour || 0,
        minutes = normalizedInput.minute || 0,
        seconds = normalizedInput.second || 0,
        milliseconds = normalizedInput.millisecond || 0;

    this._isValid = isDurationValid(normalizedInput);

    // representation for dateAddRemove
    this._milliseconds = +milliseconds +
        seconds * 1e3 + // 1000
        minutes * 6e4 + // 1000 * 60
        hours * 1000 * 60 * 60; //using 1000 * 60 * 60 instead of 36e5 to avoid floating point rounding errors https://github.com/moment/moment/issues/2978
    // Because of dateAddRemove treats 24 hours as different from a
    // day when working around DST, we need to store them separately
    this._days = +days +
        weeks * 7;
    // It is impossible to translate months into days without knowing
    // which months you are are talking about, so we have to store
    // it separately.
    this._months = +months +
        quarters * 3 +
        years * 12;

    this._data = {};

    this._locale = getLocale();

    this._bubble();
}

function isDuration (obj) {
    return obj instanceof Duration;
}

function absRound (number) {
    if (number < 0) {
        return Math.round(-1 * number) * -1;
    } else {
        return Math.round(number);
    }
}

// FORMATTING

function offset (token, separator) {
    addFormatToken(token, 0, 0, function () {
        var offset = this.utcOffset();
        var sign = '+';
        if (offset < 0) {
            offset = -offset;
            sign = '-';
        }
        return sign + zeroFill(~~(offset / 60), 2) + separator + zeroFill(~~(offset) % 60, 2);
    });
}

offset('Z', ':');
offset('ZZ', '');

// PARSING

addRegexToken('Z',  matchShortOffset);
addRegexToken('ZZ', matchShortOffset);
addParseToken(['Z', 'ZZ'], function (input, array, config) {
    config._useUTC = true;
    config._tzm = offsetFromString(matchShortOffset, input);
});

// HELPERS

// timezone chunker
// '+10:00' > ['10',  '00']
// '-1530'  > ['-15', '30']
var chunkOffset = /([\+\-]|\d\d)/gi;

function offsetFromString(matcher, string) {
    var matches = (string || '').match(matcher);

    if (matches === null) {
        return null;
    }

    var chunk   = matches[matches.length - 1] || [];
    var parts   = (chunk + '').match(chunkOffset) || ['-', 0, 0];
    var minutes = +(parts[1] * 60) + toInt(parts[2]);

    return minutes === 0 ?
      0 :
      parts[0] === '+' ? minutes : -minutes;
}

// Return a moment from input, that is local/utc/zone equivalent to model.
function cloneWithOffset(input, model) {
    var res, diff;
    if (model._isUTC) {
        res = model.clone();
        diff = (isMoment(input) || isDate(input) ? input.valueOf() : createLocal(input).valueOf()) - res.valueOf();
        // Use low-level api, because this fn is low-level api.
        res._d.setTime(res._d.valueOf() + diff);
        hooks.updateOffset(res, false);
        return res;
    } else {
        return createLocal(input).local();
    }
}

function getDateOffset (m) {
    // On Firefox.24 Date#getTimezoneOffset returns a floating point.
    // https://github.com/moment/moment/pull/1871
    return -Math.round(m._d.getTimezoneOffset() / 15) * 15;
}

// HOOKS

// This function will be called whenever a moment is mutated.
// It is intended to keep the offset in sync with the timezone.
hooks.updateOffset = function () {};

// MOMENTS

// keepLocalTime = true means only change the timezone, without
// affecting the local hour. So 5:31:26 +0300 --[utcOffset(2, true)]-->
// 5:31:26 +0200 It is possible that 5:31:26 doesn't exist with offset
// +0200, so we adjust the time as needed, to be valid.
//
// Keeping the time actually adds/subtracts (one hour)
// from the actual represented time. That is why we call updateOffset
// a second time. In case it wants us to change the offset again
// _changeInProgress == true case, then we have to adjust, because
// there is no such time in the given timezone.
function getSetOffset (input, keepLocalTime, keepMinutes) {
    var offset = this._offset || 0,
        localAdjust;
    if (!this.isValid()) {
        return input != null ? this : NaN;
    }
    if (input != null) {
        if (typeof input === 'string') {
            input = offsetFromString(matchShortOffset, input);
            if (input === null) {
                return this;
            }
        } else if (Math.abs(input) < 16 && !keepMinutes) {
            input = input * 60;
        }
        if (!this._isUTC && keepLocalTime) {
            localAdjust = getDateOffset(this);
        }
        this._offset = input;
        this._isUTC = true;
        if (localAdjust != null) {
            this.add(localAdjust, 'm');
        }
        if (offset !== input) {
            if (!keepLocalTime || this._changeInProgress) {
                addSubtract(this, createDuration(input - offset, 'm'), 1, false);
            } else if (!this._changeInProgress) {
                this._changeInProgress = true;
                hooks.updateOffset(this, true);
                this._changeInProgress = null;
            }
        }
        return this;
    } else {
        return this._isUTC ? offset : getDateOffset(this);
    }
}

function getSetZone (input, keepLocalTime) {
    if (input != null) {
        if (typeof input !== 'string') {
            input = -input;
        }

        this.utcOffset(input, keepLocalTime);

        return this;
    } else {
        return -this.utcOffset();
    }
}

function setOffsetToUTC (keepLocalTime) {
    return this.utcOffset(0, keepLocalTime);
}

function setOffsetToLocal (keepLocalTime) {
    if (this._isUTC) {
        this.utcOffset(0, keepLocalTime);
        this._isUTC = false;

        if (keepLocalTime) {
            this.subtract(getDateOffset(this), 'm');
        }
    }
    return this;
}

function setOffsetToParsedOffset () {
    if (this._tzm != null) {
        this.utcOffset(this._tzm, false, true);
    } else if (typeof this._i === 'string') {
        var tZone = offsetFromString(matchOffset, this._i);
        if (tZone != null) {
            this.utcOffset(tZone);
        }
        else {
            this.utcOffset(0, true);
        }
    }
    return this;
}

function hasAlignedHourOffset (input) {
    if (!this.isValid()) {
        return false;
    }
    input = input ? createLocal(input).utcOffset() : 0;

    return (this.utcOffset() - input) % 60 === 0;
}

function isDaylightSavingTime () {
    return (
        this.utcOffset() > this.clone().month(0).utcOffset() ||
        this.utcOffset() > this.clone().month(5).utcOffset()
    );
}

function isDaylightSavingTimeShifted () {
    if (!isUndefined(this._isDSTShifted)) {
        return this._isDSTShifted;
    }

    var c = {};

    copyConfig(c, this);
    c = prepareConfig(c);

    if (c._a) {
        var other = c._isUTC ? createUTC(c._a) : createLocal(c._a);
        this._isDSTShifted = this.isValid() &&
            compareArrays(c._a, other.toArray()) > 0;
    } else {
        this._isDSTShifted = false;
    }

    return this._isDSTShifted;
}

function isLocal () {
    return this.isValid() ? !this._isUTC : false;
}

function isUtcOffset () {
    return this.isValid() ? this._isUTC : false;
}

function isUtc () {
    return this.isValid() ? this._isUTC && this._offset === 0 : false;
}

// ASP.NET json date format regex
var aspNetRegex = /^(\-|\+)?(?:(\d*)[. ])?(\d+)\:(\d+)(?:\:(\d+)(\.\d*)?)?$/;

// from http://docs.closure-library.googlecode.com/git/closure_goog_date_date.js.source.html
// somewhat more in line with 4.4.3.2 2004 spec, but allows decimal anywhere
// and further modified to allow for strings containing both week and day
var isoRegex = /^(-|\+)?P(?:([-+]?[0-9,.]*)Y)?(?:([-+]?[0-9,.]*)M)?(?:([-+]?[0-9,.]*)W)?(?:([-+]?[0-9,.]*)D)?(?:T(?:([-+]?[0-9,.]*)H)?(?:([-+]?[0-9,.]*)M)?(?:([-+]?[0-9,.]*)S)?)?$/;

function createDuration (input, key) {
    var duration = input,
        // matching against regexp is expensive, do it on demand
        match = null,
        sign,
        ret,
        diffRes;

    if (isDuration(input)) {
        duration = {
            ms : input._milliseconds,
            d  : input._days,
            M  : input._months
        };
    } else if (isNumber(input)) {
        duration = {};
        if (key) {
            duration[key] = input;
        } else {
            duration.milliseconds = input;
        }
    } else if (!!(match = aspNetRegex.exec(input))) {
        sign = (match[1] === '-') ? -1 : 1;
        duration = {
            y  : 0,
            d  : toInt(match[DATE])                         * sign,
            h  : toInt(match[HOUR])                         * sign,
            m  : toInt(match[MINUTE])                       * sign,
            s  : toInt(match[SECOND])                       * sign,
            ms : toInt(absRound(match[MILLISECOND] * 1000)) * sign // the millisecond decimal point is included in the match
        };
    } else if (!!(match = isoRegex.exec(input))) {
        sign = (match[1] === '-') ? -1 : (match[1] === '+') ? 1 : 1;
        duration = {
            y : parseIso(match[2], sign),
            M : parseIso(match[3], sign),
            w : parseIso(match[4], sign),
            d : parseIso(match[5], sign),
            h : parseIso(match[6], sign),
            m : parseIso(match[7], sign),
            s : parseIso(match[8], sign)
        };
    } else if (duration == null) {// checks for null or undefined
        duration = {};
    } else if (typeof duration === 'object' && ('from' in duration || 'to' in duration)) {
        diffRes = momentsDifference(createLocal(duration.from), createLocal(duration.to));

        duration = {};
        duration.ms = diffRes.milliseconds;
        duration.M = diffRes.months;
    }

    ret = new Duration(duration);

    if (isDuration(input) && hasOwnProp(input, '_locale')) {
        ret._locale = input._locale;
    }

    return ret;
}

createDuration.fn = Duration.prototype;
createDuration.invalid = createInvalid$1;

function parseIso (inp, sign) {
    // We'd normally use ~~inp for this, but unfortunately it also
    // converts floats to ints.
    // inp may be undefined, so careful calling replace on it.
    var res = inp && parseFloat(inp.replace(',', '.'));
    // apply sign while we're at it
    return (isNaN(res) ? 0 : res) * sign;
}

function positiveMomentsDifference(base, other) {
    var res = {milliseconds: 0, months: 0};

    res.months = other.month() - base.month() +
        (other.year() - base.year()) * 12;
    if (base.clone().add(res.months, 'M').isAfter(other)) {
        --res.months;
    }

    res.milliseconds = +other - +(base.clone().add(res.months, 'M'));

    return res;
}

function momentsDifference(base, other) {
    var res;
    if (!(base.isValid() && other.isValid())) {
        return {milliseconds: 0, months: 0};
    }

    other = cloneWithOffset(other, base);
    if (base.isBefore(other)) {
        res = positiveMomentsDifference(base, other);
    } else {
        res = positiveMomentsDifference(other, base);
        res.milliseconds = -res.milliseconds;
        res.months = -res.months;
    }

    return res;
}

// TODO: remove 'name' arg after deprecation is removed
function createAdder(direction, name) {
    return function (val, period) {
        var dur, tmp;
        //invert the arguments, but complain about it
        if (period !== null && !isNaN(+period)) {
            deprecateSimple(name, 'moment().' + name  + '(period, number) is deprecated. Please use moment().' + name + '(number, period). ' +
            'See http://momentjs.com/guides/#/warnings/add-inverted-param/ for more info.');
            tmp = val; val = period; period = tmp;
        }

        val = typeof val === 'string' ? +val : val;
        dur = createDuration(val, period);
        addSubtract(this, dur, direction);
        return this;
    };
}

function addSubtract (mom, duration, isAdding, updateOffset) {
    var milliseconds = duration._milliseconds,
        days = absRound(duration._days),
        months = absRound(duration._months);

    if (!mom.isValid()) {
        // No op
        return;
    }

    updateOffset = updateOffset == null ? true : updateOffset;

    if (months) {
        setMonth(mom, get(mom, 'Month') + months * isAdding);
    }
    if (days) {
        set$1(mom, 'Date', get(mom, 'Date') + days * isAdding);
    }
    if (milliseconds) {
        mom._d.setTime(mom._d.valueOf() + milliseconds * isAdding);
    }
    if (updateOffset) {
        hooks.updateOffset(mom, days || months);
    }
}

var add      = createAdder(1, 'add');
var subtract = createAdder(-1, 'subtract');

function getCalendarFormat(myMoment, now) {
    var diff = myMoment.diff(now, 'days', true);
    return diff < -6 ? 'sameElse' :
            diff < -1 ? 'lastWeek' :
            diff < 0 ? 'lastDay' :
            diff < 1 ? 'sameDay' :
            diff < 2 ? 'nextDay' :
            diff < 7 ? 'nextWeek' : 'sameElse';
}

function calendar$1 (time, formats) {
    // We want to compare the start of today, vs this.
    // Getting start-of-today depends on whether we're local/utc/offset or not.
    var now = time || createLocal(),
        sod = cloneWithOffset(now, this).startOf('day'),
        format = hooks.calendarFormat(this, sod) || 'sameElse';

    var output = formats && (isFunction(formats[format]) ? formats[format].call(this, now) : formats[format]);

    return this.format(output || this.localeData().calendar(format, this, createLocal(now)));
}

function clone () {
    return new Moment(this);
}

function isAfter (input, units) {
    var localInput = isMoment(input) ? input : createLocal(input);
    if (!(this.isValid() && localInput.isValid())) {
        return false;
    }
    units = normalizeUnits(!isUndefined(units) ? units : 'millisecond');
    if (units === 'millisecond') {
        return this.valueOf() > localInput.valueOf();
    } else {
        return localInput.valueOf() < this.clone().startOf(units).valueOf();
    }
}

function isBefore (input, units) {
    var localInput = isMoment(input) ? input : createLocal(input);
    if (!(this.isValid() && localInput.isValid())) {
        return false;
    }
    units = normalizeUnits(!isUndefined(units) ? units : 'millisecond');
    if (units === 'millisecond') {
        return this.valueOf() < localInput.valueOf();
    } else {
        return this.clone().endOf(units).valueOf() < localInput.valueOf();
    }
}

function isBetween (from, to, units, inclusivity) {
    inclusivity = inclusivity || '()';
    return (inclusivity[0] === '(' ? this.isAfter(from, units) : !this.isBefore(from, units)) &&
        (inclusivity[1] === ')' ? this.isBefore(to, units) : !this.isAfter(to, units));
}

function isSame (input, units) {
    var localInput = isMoment(input) ? input : createLocal(input),
        inputMs;
    if (!(this.isValid() && localInput.isValid())) {
        return false;
    }
    units = normalizeUnits(units || 'millisecond');
    if (units === 'millisecond') {
        return this.valueOf() === localInput.valueOf();
    } else {
        inputMs = localInput.valueOf();
        return this.clone().startOf(units).valueOf() <= inputMs && inputMs <= this.clone().endOf(units).valueOf();
    }
}

function isSameOrAfter (input, units) {
    return this.isSame(input, units) || this.isAfter(input,units);
}

function isSameOrBefore (input, units) {
    return this.isSame(input, units) || this.isBefore(input,units);
}

function diff (input, units, asFloat) {
    var that,
        zoneDelta,
        delta, output;

    if (!this.isValid()) {
        return NaN;
    }

    that = cloneWithOffset(input, this);

    if (!that.isValid()) {
        return NaN;
    }

    zoneDelta = (that.utcOffset() - this.utcOffset()) * 6e4;

    units = normalizeUnits(units);

    switch (units) {
        case 'year': output = monthDiff(this, that) / 12; break;
        case 'month': output = monthDiff(this, that); break;
        case 'quarter': output = monthDiff(this, that) / 3; break;
        case 'second': output = (this - that) / 1e3; break; // 1000
        case 'minute': output = (this - that) / 6e4; break; // 1000 * 60
        case 'hour': output = (this - that) / 36e5; break; // 1000 * 60 * 60
        case 'day': output = (this - that - zoneDelta) / 864e5; break; // 1000 * 60 * 60 * 24, negate dst
        case 'week': output = (this - that - zoneDelta) / 6048e5; break; // 1000 * 60 * 60 * 24 * 7, negate dst
        default: output = this - that;
    }

    return asFloat ? output : absFloor(output);
}

function monthDiff (a, b) {
    // difference in months
    var wholeMonthDiff = ((b.year() - a.year()) * 12) + (b.month() - a.month()),
        // b is in (anchor - 1 month, anchor + 1 month)
        anchor = a.clone().add(wholeMonthDiff, 'months'),
        anchor2, adjust;

    if (b - anchor < 0) {
        anchor2 = a.clone().add(wholeMonthDiff - 1, 'months');
        // linear across the month
        adjust = (b - anchor) / (anchor - anchor2);
    } else {
        anchor2 = a.clone().add(wholeMonthDiff + 1, 'months');
        // linear across the month
        adjust = (b - anchor) / (anchor2 - anchor);
    }

    //check for negative zero, return zero if negative zero
    return -(wholeMonthDiff + adjust) || 0;
}

hooks.defaultFormat = 'YYYY-MM-DDTHH:mm:ssZ';
hooks.defaultFormatUtc = 'YYYY-MM-DDTHH:mm:ss[Z]';

function toString () {
    return this.clone().locale('en').format('ddd MMM DD YYYY HH:mm:ss [GMT]ZZ');
}

function toISOString() {
    if (!this.isValid()) {
        return null;
    }
    var m = this.clone().utc();
    if (m.year() < 0 || m.year() > 9999) {
        return formatMoment(m, 'YYYYYY-MM-DD[T]HH:mm:ss.SSS[Z]');
    }
    if (isFunction(Date.prototype.toISOString)) {
        // native implementation is ~50x faster, use it when we can
        return this.toDate().toISOString();
    }
    return formatMoment(m, 'YYYY-MM-DD[T]HH:mm:ss.SSS[Z]');
}

/**
 * Return a human readable representation of a moment that can
 * also be evaluated to get a new moment which is the same
 *
 * @link https://nodejs.org/dist/latest/docs/api/util.html#util_custom_inspect_function_on_objects
 */
function inspect () {
    if (!this.isValid()) {
        return 'moment.invalid(/* ' + this._i + ' */)';
    }
    var func = 'moment';
    var zone = '';
    if (!this.isLocal()) {
        func = this.utcOffset() === 0 ? 'moment.utc' : 'moment.parseZone';
        zone = 'Z';
    }
    var prefix = '[' + func + '("]';
    var year = (0 <= this.year() && this.year() <= 9999) ? 'YYYY' : 'YYYYYY';
    var datetime = '-MM-DD[T]HH:mm:ss.SSS';
    var suffix = zone + '[")]';

    return this.format(prefix + year + datetime + suffix);
}

function format (inputString) {
    if (!inputString) {
        inputString = this.isUtc() ? hooks.defaultFormatUtc : hooks.defaultFormat;
    }
    var output = formatMoment(this, inputString);
    return this.localeData().postformat(output);
}

function from (time, withoutSuffix) {
    if (this.isValid() &&
            ((isMoment(time) && time.isValid()) ||
             createLocal(time).isValid())) {
        return createDuration({to: this, from: time}).locale(this.locale()).humanize(!withoutSuffix);
    } else {
        return this.localeData().invalidDate();
    }
}

function fromNow (withoutSuffix) {
    return this.from(createLocal(), withoutSuffix);
}

function to (time, withoutSuffix) {
    if (this.isValid() &&
            ((isMoment(time) && time.isValid()) ||
             createLocal(time).isValid())) {
        return createDuration({from: this, to: time}).locale(this.locale()).humanize(!withoutSuffix);
    } else {
        return this.localeData().invalidDate();
    }
}

function toNow (withoutSuffix) {
    return this.to(createLocal(), withoutSuffix);
}

// If passed a locale key, it will set the locale for this
// instance.  Otherwise, it will return the locale configuration
// variables for this instance.
function locale (key) {
    var newLocaleData;

    if (key === undefined) {
        return this._locale._abbr;
    } else {
        newLocaleData = getLocale(key);
        if (newLocaleData != null) {
            this._locale = newLocaleData;
        }
        return this;
    }
}

var lang = deprecate(
    'moment().lang() is deprecated. Instead, use moment().localeData() to get the language configuration. Use moment().locale() to change languages.',
    function (key) {
        if (key === undefined) {
            return this.localeData();
        } else {
            return this.locale(key);
        }
    }
);

function localeData () {
    return this._locale;
}

function startOf (units) {
    units = normalizeUnits(units);
    // the following switch intentionally omits break keywords
    // to utilize falling through the cases.
    switch (units) {
        case 'year':
            this.month(0);
            /* falls through */
        case 'quarter':
        case 'month':
            this.date(1);
            /* falls through */
        case 'week':
        case 'isoWeek':
        case 'day':
        case 'date':
            this.hours(0);
            /* falls through */
        case 'hour':
            this.minutes(0);
            /* falls through */
        case 'minute':
            this.seconds(0);
            /* falls through */
        case 'second':
            this.milliseconds(0);
    }

    // weeks are a special case
    if (units === 'week') {
        this.weekday(0);
    }
    if (units === 'isoWeek') {
        this.isoWeekday(1);
    }

    // quarters are also special
    if (units === 'quarter') {
        this.month(Math.floor(this.month() / 3) * 3);
    }

    return this;
}

function endOf (units) {
    units = normalizeUnits(units);
    if (units === undefined || units === 'millisecond') {
        return this;
    }

    // 'date' is an alias for 'day', so it should be considered as such.
    if (units === 'date') {
        units = 'day';
    }

    return this.startOf(units).add(1, (units === 'isoWeek' ? 'week' : units)).subtract(1, 'ms');
}

function valueOf () {
    return this._d.valueOf() - ((this._offset || 0) * 60000);
}

function unix () {
    return Math.floor(this.valueOf() / 1000);
}

function toDate () {
    return new Date(this.valueOf());
}

function toArray () {
    var m = this;
    return [m.year(), m.month(), m.date(), m.hour(), m.minute(), m.second(), m.millisecond()];
}

function toObject () {
    var m = this;
    return {
        years: m.year(),
        months: m.month(),
        date: m.date(),
        hours: m.hours(),
        minutes: m.minutes(),
        seconds: m.seconds(),
        milliseconds: m.milliseconds()
    };
}

function toJSON () {
    // new Date(NaN).toJSON() === null
    return this.isValid() ? this.toISOString() : null;
}

function isValid$2 () {
    return isValid(this);
}

function parsingFlags () {
    return extend({}, getParsingFlags(this));
}

function invalidAt () {
    return getParsingFlags(this).overflow;
}

function creationData() {
    return {
        input: this._i,
        format: this._f,
        locale: this._locale,
        isUTC: this._isUTC,
        strict: this._strict
    };
}

// FORMATTING

addFormatToken(0, ['gg', 2], 0, function () {
    return this.weekYear() % 100;
});

addFormatToken(0, ['GG', 2], 0, function () {
    return this.isoWeekYear() % 100;
});

function addWeekYearFormatToken (token, getter) {
    addFormatToken(0, [token, token.length], 0, getter);
}

addWeekYearFormatToken('gggg',     'weekYear');
addWeekYearFormatToken('ggggg',    'weekYear');
addWeekYearFormatToken('GGGG',  'isoWeekYear');
addWeekYearFormatToken('GGGGG', 'isoWeekYear');

// ALIASES

addUnitAlias('weekYear', 'gg');
addUnitAlias('isoWeekYear', 'GG');

// PRIORITY

addUnitPriority('weekYear', 1);
addUnitPriority('isoWeekYear', 1);


// PARSING

addRegexToken('G',      matchSigned);
addRegexToken('g',      matchSigned);
addRegexToken('GG',     match1to2, match2);
addRegexToken('gg',     match1to2, match2);
addRegexToken('GGGG',   match1to4, match4);
addRegexToken('gggg',   match1to4, match4);
addRegexToken('GGGGG',  match1to6, match6);
addRegexToken('ggggg',  match1to6, match6);

addWeekParseToken(['gggg', 'ggggg', 'GGGG', 'GGGGG'], function (input, week, config, token) {
    week[token.substr(0, 2)] = toInt(input);
});

addWeekParseToken(['gg', 'GG'], function (input, week, config, token) {
    week[token] = hooks.parseTwoDigitYear(input);
});

// MOMENTS

function getSetWeekYear (input) {
    return getSetWeekYearHelper.call(this,
            input,
            this.week(),
            this.weekday(),
            this.localeData()._week.dow,
            this.localeData()._week.doy);
}

function getSetISOWeekYear (input) {
    return getSetWeekYearHelper.call(this,
            input, this.isoWeek(), this.isoWeekday(), 1, 4);
}

function getISOWeeksInYear () {
    return weeksInYear(this.year(), 1, 4);
}

function getWeeksInYear () {
    var weekInfo = this.localeData()._week;
    return weeksInYear(this.year(), weekInfo.dow, weekInfo.doy);
}

function getSetWeekYearHelper(input, week, weekday, dow, doy) {
    var weeksTarget;
    if (input == null) {
        return weekOfYear(this, dow, doy).year;
    } else {
        weeksTarget = weeksInYear(input, dow, doy);
        if (week > weeksTarget) {
            week = weeksTarget;
        }
        return setWeekAll.call(this, input, week, weekday, dow, doy);
    }
}

function setWeekAll(weekYear, week, weekday, dow, doy) {
    var dayOfYearData = dayOfYearFromWeeks(weekYear, week, weekday, dow, doy),
        date = createUTCDate(dayOfYearData.year, 0, dayOfYearData.dayOfYear);

    this.year(date.getUTCFullYear());
    this.month(date.getUTCMonth());
    this.date(date.getUTCDate());
    return this;
}

// FORMATTING

addFormatToken('Q', 0, 'Qo', 'quarter');

// ALIASES

addUnitAlias('quarter', 'Q');

// PRIORITY

addUnitPriority('quarter', 7);

// PARSING

addRegexToken('Q', match1);
addParseToken('Q', function (input, array) {
    array[MONTH] = (toInt(input) - 1) * 3;
});

// MOMENTS

function getSetQuarter (input) {
    return input == null ? Math.ceil((this.month() + 1) / 3) : this.month((input - 1) * 3 + this.month() % 3);
}

// FORMATTING

addFormatToken('D', ['DD', 2], 'Do', 'date');

// ALIASES

addUnitAlias('date', 'D');

// PRIOROITY
addUnitPriority('date', 9);

// PARSING

addRegexToken('D',  match1to2);
addRegexToken('DD', match1to2, match2);
addRegexToken('Do', function (isStrict, locale) {
    // TODO: Remove "ordinalParse" fallback in next major release.
    return isStrict ?
      (locale._dayOfMonthOrdinalParse || locale._ordinalParse) :
      locale._dayOfMonthOrdinalParseLenient;
});

addParseToken(['D', 'DD'], DATE);
addParseToken('Do', function (input, array) {
    array[DATE] = toInt(input.match(match1to2)[0]);
});

// MOMENTS

var getSetDayOfMonth = makeGetSet('Date', true);

// FORMATTING

addFormatToken('DDD', ['DDDD', 3], 'DDDo', 'dayOfYear');

// ALIASES

addUnitAlias('dayOfYear', 'DDD');

// PRIORITY
addUnitPriority('dayOfYear', 4);

// PARSING

addRegexToken('DDD',  match1to3);
addRegexToken('DDDD', match3);
addParseToken(['DDD', 'DDDD'], function (input, array, config) {
    config._dayOfYear = toInt(input);
});

// HELPERS

// MOMENTS

function getSetDayOfYear (input) {
    var dayOfYear = Math.round((this.clone().startOf('day') - this.clone().startOf('year')) / 864e5) + 1;
    return input == null ? dayOfYear : this.add((input - dayOfYear), 'd');
}

// FORMATTING

addFormatToken('m', ['mm', 2], 0, 'minute');

// ALIASES

addUnitAlias('minute', 'm');

// PRIORITY

addUnitPriority('minute', 14);

// PARSING

addRegexToken('m',  match1to2);
addRegexToken('mm', match1to2, match2);
addParseToken(['m', 'mm'], MINUTE);

// MOMENTS

var getSetMinute = makeGetSet('Minutes', false);

// FORMATTING

addFormatToken('s', ['ss', 2], 0, 'second');

// ALIASES

addUnitAlias('second', 's');

// PRIORITY

addUnitPriority('second', 15);

// PARSING

addRegexToken('s',  match1to2);
addRegexToken('ss', match1to2, match2);
addParseToken(['s', 'ss'], SECOND);

// MOMENTS

var getSetSecond = makeGetSet('Seconds', false);

// FORMATTING

addFormatToken('S', 0, 0, function () {
    return ~~(this.millisecond() / 100);
});

addFormatToken(0, ['SS', 2], 0, function () {
    return ~~(this.millisecond() / 10);
});

addFormatToken(0, ['SSS', 3], 0, 'millisecond');
addFormatToken(0, ['SSSS', 4], 0, function () {
    return this.millisecond() * 10;
});
addFormatToken(0, ['SSSSS', 5], 0, function () {
    return this.millisecond() * 100;
});
addFormatToken(0, ['SSSSSS', 6], 0, function () {
    return this.millisecond() * 1000;
});
addFormatToken(0, ['SSSSSSS', 7], 0, function () {
    return this.millisecond() * 10000;
});
addFormatToken(0, ['SSSSSSSS', 8], 0, function () {
    return this.millisecond() * 100000;
});
addFormatToken(0, ['SSSSSSSSS', 9], 0, function () {
    return this.millisecond() * 1000000;
});


// ALIASES

addUnitAlias('millisecond', 'ms');

// PRIORITY

addUnitPriority('millisecond', 16);

// PARSING

addRegexToken('S',    match1to3, match1);
addRegexToken('SS',   match1to3, match2);
addRegexToken('SSS',  match1to3, match3);

var token;
for (token = 'SSSS'; token.length <= 9; token += 'S') {
    addRegexToken(token, matchUnsigned);
}

function parseMs(input, array) {
    array[MILLISECOND] = toInt(('0.' + input) * 1000);
}

for (token = 'S'; token.length <= 9; token += 'S') {
    addParseToken(token, parseMs);
}
// MOMENTS

var getSetMillisecond = makeGetSet('Milliseconds', false);

// FORMATTING

addFormatToken('z',  0, 0, 'zoneAbbr');
addFormatToken('zz', 0, 0, 'zoneName');

// MOMENTS

function getZoneAbbr () {
    return this._isUTC ? 'UTC' : '';
}

function getZoneName () {
    return this._isUTC ? 'Coordinated Universal Time' : '';
}

var proto = Moment.prototype;

proto.add               = add;
proto.calendar          = calendar$1;
proto.clone             = clone;
proto.diff              = diff;
proto.endOf             = endOf;
proto.format            = format;
proto.from              = from;
proto.fromNow           = fromNow;
proto.to                = to;
proto.toNow             = toNow;
proto.get               = stringGet;
proto.invalidAt         = invalidAt;
proto.isAfter           = isAfter;
proto.isBefore          = isBefore;
proto.isBetween         = isBetween;
proto.isSame            = isSame;
proto.isSameOrAfter     = isSameOrAfter;
proto.isSameOrBefore    = isSameOrBefore;
proto.isValid           = isValid$2;
proto.lang              = lang;
proto.locale            = locale;
proto.localeData        = localeData;
proto.max               = prototypeMax;
proto.min               = prototypeMin;
proto.parsingFlags      = parsingFlags;
proto.set               = stringSet;
proto.startOf           = startOf;
proto.subtract          = subtract;
proto.toArray           = toArray;
proto.toObject          = toObject;
proto.toDate            = toDate;
proto.toISOString       = toISOString;
proto.inspect           = inspect;
proto.toJSON            = toJSON;
proto.toString          = toString;
proto.unix              = unix;
proto.valueOf           = valueOf;
proto.creationData      = creationData;

// Year
proto.year       = getSetYear;
proto.isLeapYear = getIsLeapYear;

// Week Year
proto.weekYear    = getSetWeekYear;
proto.isoWeekYear = getSetISOWeekYear;

// Quarter
proto.quarter = proto.quarters = getSetQuarter;

// Month
proto.month       = getSetMonth;
proto.daysInMonth = getDaysInMonth;

// Week
proto.week           = proto.weeks        = getSetWeek;
proto.isoWeek        = proto.isoWeeks     = getSetISOWeek;
proto.weeksInYear    = getWeeksInYear;
proto.isoWeeksInYear = getISOWeeksInYear;

// Day
proto.date       = getSetDayOfMonth;
proto.day        = proto.days             = getSetDayOfWeek;
proto.weekday    = getSetLocaleDayOfWeek;
proto.isoWeekday = getSetISODayOfWeek;
proto.dayOfYear  = getSetDayOfYear;

// Hour
proto.hour = proto.hours = getSetHour;

// Minute
proto.minute = proto.minutes = getSetMinute;

// Second
proto.second = proto.seconds = getSetSecond;

// Millisecond
proto.millisecond = proto.milliseconds = getSetMillisecond;

// Offset
proto.utcOffset            = getSetOffset;
proto.utc                  = setOffsetToUTC;
proto.local                = setOffsetToLocal;
proto.parseZone            = setOffsetToParsedOffset;
proto.hasAlignedHourOffset = hasAlignedHourOffset;
proto.isDST                = isDaylightSavingTime;
proto.isLocal              = isLocal;
proto.isUtcOffset          = isUtcOffset;
proto.isUtc                = isUtc;
proto.isUTC                = isUtc;

// Timezone
proto.zoneAbbr = getZoneAbbr;
proto.zoneName = getZoneName;

// Deprecations
proto.dates  = deprecate('dates accessor is deprecated. Use date instead.', getSetDayOfMonth);
proto.months = deprecate('months accessor is deprecated. Use month instead', getSetMonth);
proto.years  = deprecate('years accessor is deprecated. Use year instead', getSetYear);
proto.zone   = deprecate('moment().zone is deprecated, use moment().utcOffset instead. http://momentjs.com/guides/#/warnings/zone/', getSetZone);
proto.isDSTShifted = deprecate('isDSTShifted is deprecated. See http://momentjs.com/guides/#/warnings/dst-shifted/ for more information', isDaylightSavingTimeShifted);

function createUnix (input) {
    return createLocal(input * 1000);
}

function createInZone () {
    return createLocal.apply(null, arguments).parseZone();
}

function preParsePostFormat (string) {
    return string;
}

var proto$1 = Locale.prototype;

proto$1.calendar        = calendar;
proto$1.longDateFormat  = longDateFormat;
proto$1.invalidDate     = invalidDate;
proto$1.ordinal         = ordinal;
proto$1.preparse        = preParsePostFormat;
proto$1.postformat      = preParsePostFormat;
proto$1.relativeTime    = relativeTime;
proto$1.pastFuture      = pastFuture;
proto$1.set             = set;

// Month
proto$1.months            =        localeMonths;
proto$1.monthsShort       =        localeMonthsShort;
proto$1.monthsParse       =        localeMonthsParse;
proto$1.monthsRegex       = monthsRegex;
proto$1.monthsShortRegex  = monthsShortRegex;

// Week
proto$1.week = localeWeek;
proto$1.firstDayOfYear = localeFirstDayOfYear;
proto$1.firstDayOfWeek = localeFirstDayOfWeek;

// Day of Week
proto$1.weekdays       =        localeWeekdays;
proto$1.weekdaysMin    =        localeWeekdaysMin;
proto$1.weekdaysShort  =        localeWeekdaysShort;
proto$1.weekdaysParse  =        localeWeekdaysParse;

proto$1.weekdaysRegex       =        weekdaysRegex;
proto$1.weekdaysShortRegex  =        weekdaysShortRegex;
proto$1.weekdaysMinRegex    =        weekdaysMinRegex;

// Hours
proto$1.isPM = localeIsPM;
proto$1.meridiem = localeMeridiem;

function get$1 (format, index, field, setter) {
    var locale = getLocale();
    var utc = createUTC().set(setter, index);
    return locale[field](utc, format);
}

function listMonthsImpl (format, index, field) {
    if (isNumber(format)) {
        index = format;
        format = undefined;
    }

    format = format || '';

    if (index != null) {
        return get$1(format, index, field, 'month');
    }

    var i;
    var out = [];
    for (i = 0; i < 12; i++) {
        out[i] = get$1(format, i, field, 'month');
    }
    return out;
}

// ()
// (5)
// (fmt, 5)
// (fmt)
// (true)
// (true, 5)
// (true, fmt, 5)
// (true, fmt)
function listWeekdaysImpl (localeSorted, format, index, field) {
    if (typeof localeSorted === 'boolean') {
        if (isNumber(format)) {
            index = format;
            format = undefined;
        }

        format = format || '';
    } else {
        format = localeSorted;
        index = format;
        localeSorted = false;

        if (isNumber(format)) {
            index = format;
            format = undefined;
        }

        format = format || '';
    }

    var locale = getLocale(),
        shift = localeSorted ? locale._week.dow : 0;

    if (index != null) {
        return get$1(format, (index + shift) % 7, field, 'day');
    }

    var i;
    var out = [];
    for (i = 0; i < 7; i++) {
        out[i] = get$1(format, (i + shift) % 7, field, 'day');
    }
    return out;
}

function listMonths (format, index) {
    return listMonthsImpl(format, index, 'months');
}

function listMonthsShort (format, index) {
    return listMonthsImpl(format, index, 'monthsShort');
}

function listWeekdays (localeSorted, format, index) {
    return listWeekdaysImpl(localeSorted, format, index, 'weekdays');
}

function listWeekdaysShort (localeSorted, format, index) {
    return listWeekdaysImpl(localeSorted, format, index, 'weekdaysShort');
}

function listWeekdaysMin (localeSorted, format, index) {
    return listWeekdaysImpl(localeSorted, format, index, 'weekdaysMin');
}

getSetGlobalLocale('en', {
    dayOfMonthOrdinalParse: /\d{1,2}(th|st|nd|rd)/,
    ordinal : function (number) {
        var b = number % 10,
            output = (toInt(number % 100 / 10) === 1) ? 'th' :
            (b === 1) ? 'st' :
            (b === 2) ? 'nd' :
            (b === 3) ? 'rd' : 'th';
        return number + output;
    }
});

// Side effect imports
hooks.lang = deprecate('moment.lang is deprecated. Use moment.locale instead.', getSetGlobalLocale);
hooks.langData = deprecate('moment.langData is deprecated. Use moment.localeData instead.', getLocale);

var mathAbs = Math.abs;

function abs () {
    var data           = this._data;

    this._milliseconds = mathAbs(this._milliseconds);
    this._days         = mathAbs(this._days);
    this._months       = mathAbs(this._months);

    data.milliseconds  = mathAbs(data.milliseconds);
    data.seconds       = mathAbs(data.seconds);
    data.minutes       = mathAbs(data.minutes);
    data.hours         = mathAbs(data.hours);
    data.months        = mathAbs(data.months);
    data.years         = mathAbs(data.years);

    return this;
}

function addSubtract$1 (duration, input, value, direction) {
    var other = createDuration(input, value);

    duration._milliseconds += direction * other._milliseconds;
    duration._days         += direction * other._days;
    duration._months       += direction * other._months;

    return duration._bubble();
}

// supports only 2.0-style add(1, 's') or add(duration)
function add$1 (input, value) {
    return addSubtract$1(this, input, value, 1);
}

// supports only 2.0-style subtract(1, 's') or subtract(duration)
function subtract$1 (input, value) {
    return addSubtract$1(this, input, value, -1);
}

function absCeil (number) {
    if (number < 0) {
        return Math.floor(number);
    } else {
        return Math.ceil(number);
    }
}

function bubble () {
    var milliseconds = this._milliseconds;
    var days         = this._days;
    var months       = this._months;
    var data         = this._data;
    var seconds, minutes, hours, years, monthsFromDays;

    // if we have a mix of positive and negative values, bubble down first
    // check: https://github.com/moment/moment/issues/2166
    if (!((milliseconds >= 0 && days >= 0 && months >= 0) ||
            (milliseconds <= 0 && days <= 0 && months <= 0))) {
        milliseconds += absCeil(monthsToDays(months) + days) * 864e5;
        days = 0;
        months = 0;
    }

    // The following code bubbles up values, see the tests for
    // examples of what that means.
    data.milliseconds = milliseconds % 1000;

    seconds           = absFloor(milliseconds / 1000);
    data.seconds      = seconds % 60;

    minutes           = absFloor(seconds / 60);
    data.minutes      = minutes % 60;

    hours             = absFloor(minutes / 60);
    data.hours        = hours % 24;

    days += absFloor(hours / 24);

    // convert days to months
    monthsFromDays = absFloor(daysToMonths(days));
    months += monthsFromDays;
    days -= absCeil(monthsToDays(monthsFromDays));

    // 12 months -> 1 year
    years = absFloor(months / 12);
    months %= 12;

    data.days   = days;
    data.months = months;
    data.years  = years;

    return this;
}

function daysToMonths (days) {
    // 400 years have 146097 days (taking into account leap year rules)
    // 400 years have 12 months === 4800
    return days * 4800 / 146097;
}

function monthsToDays (months) {
    // the reverse of daysToMonths
    return months * 146097 / 4800;
}

function as (units) {
    if (!this.isValid()) {
        return NaN;
    }
    var days;
    var months;
    var milliseconds = this._milliseconds;

    units = normalizeUnits(units);

    if (units === 'month' || units === 'year') {
        days   = this._days   + milliseconds / 864e5;
        months = this._months + daysToMonths(days);
        return units === 'month' ? months : months / 12;
    } else {
        // handle milliseconds separately because of floating point math errors (issue #1867)
        days = this._days + Math.round(monthsToDays(this._months));
        switch (units) {
            case 'week'   : return days / 7     + milliseconds / 6048e5;
            case 'day'    : return days         + milliseconds / 864e5;
            case 'hour'   : return days * 24    + milliseconds / 36e5;
            case 'minute' : return days * 1440  + milliseconds / 6e4;
            case 'second' : return days * 86400 + milliseconds / 1000;
            // Math.floor prevents floating point math errors here
            case 'millisecond': return Math.floor(days * 864e5) + milliseconds;
            default: throw new Error('Unknown unit ' + units);
        }
    }
}

// TODO: Use this.as('ms')?
function valueOf$1 () {
    if (!this.isValid()) {
        return NaN;
    }
    return (
        this._milliseconds +
        this._days * 864e5 +
        (this._months % 12) * 2592e6 +
        toInt(this._months / 12) * 31536e6
    );
}

function makeAs (alias) {
    return function () {
        return this.as(alias);
    };
}

var asMilliseconds = makeAs('ms');
var asSeconds      = makeAs('s');
var asMinutes      = makeAs('m');
var asHours        = makeAs('h');
var asDays         = makeAs('d');
var asWeeks        = makeAs('w');
var asMonths       = makeAs('M');
var asYears        = makeAs('y');

function clone$1 () {
    return createDuration(this);
}

function get$2 (units) {
    units = normalizeUnits(units);
    return this.isValid() ? this[units + 's']() : NaN;
}

function makeGetter(name) {
    return function () {
        return this.isValid() ? this._data[name] : NaN;
    };
}

var milliseconds = makeGetter('milliseconds');
var seconds      = makeGetter('seconds');
var minutes      = makeGetter('minutes');
var hours        = makeGetter('hours');
var days         = makeGetter('days');
var months       = makeGetter('months');
var years        = makeGetter('years');

function weeks () {
    return absFloor(this.days() / 7);
}

var round = Math.round;
var thresholds = {
    ss: 44,         // a few seconds to seconds
    s : 45,         // seconds to minute
    m : 45,         // minutes to hour
    h : 22,         // hours to day
    d : 26,         // days to month
    M : 11          // months to year
};

// helper function for moment.fn.from, moment.fn.fromNow, and moment.duration.fn.humanize
function substituteTimeAgo(string, number, withoutSuffix, isFuture, locale) {
    return locale.relativeTime(number || 1, !!withoutSuffix, string, isFuture);
}

function relativeTime$1 (posNegDuration, withoutSuffix, locale) {
    var duration = createDuration(posNegDuration).abs();
    var seconds  = round(duration.as('s'));
    var minutes  = round(duration.as('m'));
    var hours    = round(duration.as('h'));
    var days     = round(duration.as('d'));
    var months   = round(duration.as('M'));
    var years    = round(duration.as('y'));

    var a = seconds <= thresholds.ss && ['s', seconds]  ||
            seconds < thresholds.s   && ['ss', seconds] ||
            minutes <= 1             && ['m']           ||
            minutes < thresholds.m   && ['mm', minutes] ||
            hours   <= 1             && ['h']           ||
            hours   < thresholds.h   && ['hh', hours]   ||
            days    <= 1             && ['d']           ||
            days    < thresholds.d   && ['dd', days]    ||
            months  <= 1             && ['M']           ||
            months  < thresholds.M   && ['MM', months]  ||
            years   <= 1             && ['y']           || ['yy', years];

    a[2] = withoutSuffix;
    a[3] = +posNegDuration > 0;
    a[4] = locale;
    return substituteTimeAgo.apply(null, a);
}

// This function allows you to set the rounding function for relative time strings
function getSetRelativeTimeRounding (roundingFunction) {
    if (roundingFunction === undefined) {
        return round;
    }
    if (typeof(roundingFunction) === 'function') {
        round = roundingFunction;
        return true;
    }
    return false;
}

// This function allows you to set a threshold for relative time strings
function getSetRelativeTimeThreshold (threshold, limit) {
    if (thresholds[threshold] === undefined) {
        return false;
    }
    if (limit === undefined) {
        return thresholds[threshold];
    }
    thresholds[threshold] = limit;
    if (threshold === 's') {
        thresholds.ss = limit - 1;
    }
    return true;
}

function humanize (withSuffix) {
    if (!this.isValid()) {
        return this.localeData().invalidDate();
    }

    var locale = this.localeData();
    var output = relativeTime$1(this, !withSuffix, locale);

    if (withSuffix) {
        output = locale.pastFuture(+this, output);
    }

    return locale.postformat(output);
}

var abs$1 = Math.abs;

function sign(x) {
    return ((x > 0) - (x < 0)) || +x;
}

function toISOString$1() {
    // for ISO strings we do not use the normal bubbling rules:
    //  * milliseconds bubble up until they become hours
    //  * days do not bubble at all
    //  * months bubble up until they become years
    // This is because there is no context-free conversion between hours and days
    // (think of clock changes)
    // and also not between days and months (28-31 days per month)
    if (!this.isValid()) {
        return this.localeData().invalidDate();
    }

    var seconds = abs$1(this._milliseconds) / 1000;
    var days         = abs$1(this._days);
    var months       = abs$1(this._months);
    var minutes, hours, years;

    // 3600 seconds -> 60 minutes -> 1 hour
    minutes           = absFloor(seconds / 60);
    hours             = absFloor(minutes / 60);
    seconds %= 60;
    minutes %= 60;

    // 12 months -> 1 year
    years  = absFloor(months / 12);
    months %= 12;


    // inspired by https://github.com/dordille/moment-isoduration/blob/master/moment.isoduration.js
    var Y = years;
    var M = months;
    var D = days;
    var h = hours;
    var m = minutes;
    var s = seconds ? seconds.toFixed(3).replace(/\.?0+$/, '') : '';
    var total = this.asSeconds();

    if (!total) {
        // this is the same as C#'s (Noda) and python (isodate)...
        // but not other JS (goog.date)
        return 'P0D';
    }

    var totalSign = total < 0 ? '-' : '';
    var ymSign = sign(this._months) !== sign(total) ? '-' : '';
    var daysSign = sign(this._days) !== sign(total) ? '-' : '';
    var hmsSign = sign(this._milliseconds) !== sign(total) ? '-' : '';

    return totalSign + 'P' +
        (Y ? ymSign + Y + 'Y' : '') +
        (M ? ymSign + M + 'M' : '') +
        (D ? daysSign + D + 'D' : '') +
        ((h || m || s) ? 'T' : '') +
        (h ? hmsSign + h + 'H' : '') +
        (m ? hmsSign + m + 'M' : '') +
        (s ? hmsSign + s + 'S' : '');
}

var proto$2 = Duration.prototype;

proto$2.isValid        = isValid$1;
proto$2.abs            = abs;
proto$2.add            = add$1;
proto$2.subtract       = subtract$1;
proto$2.as             = as;
proto$2.asMilliseconds = asMilliseconds;
proto$2.asSeconds      = asSeconds;
proto$2.asMinutes      = asMinutes;
proto$2.asHours        = asHours;
proto$2.asDays         = asDays;
proto$2.asWeeks        = asWeeks;
proto$2.asMonths       = asMonths;
proto$2.asYears        = asYears;
proto$2.valueOf        = valueOf$1;
proto$2._bubble        = bubble;
proto$2.clone          = clone$1;
proto$2.get            = get$2;
proto$2.milliseconds   = milliseconds;
proto$2.seconds        = seconds;
proto$2.minutes        = minutes;
proto$2.hours          = hours;
proto$2.days           = days;
proto$2.weeks          = weeks;
proto$2.months         = months;
proto$2.years          = years;
proto$2.humanize       = humanize;
proto$2.toISOString    = toISOString$1;
proto$2.toString       = toISOString$1;
proto$2.toJSON         = toISOString$1;
proto$2.locale         = locale;
proto$2.localeData     = localeData;

// Deprecations
proto$2.toIsoString = deprecate('toIsoString() is deprecated. Please use toISOString() instead (notice the capitals)', toISOString$1);
proto$2.lang = lang;

// Side effect imports

// FORMATTING

addFormatToken('X', 0, 0, 'unix');
addFormatToken('x', 0, 0, 'valueOf');

// PARSING

addRegexToken('x', matchSigned);
addRegexToken('X', matchTimestamp);
addParseToken('X', function (input, array, config) {
    config._d = new Date(parseFloat(input, 10) * 1000);
});
addParseToken('x', function (input, array, config) {
    config._d = new Date(toInt(input));
});

// Side effect imports


hooks.version = '2.19.4';

setHookCallback(createLocal);

hooks.fn                    = proto;
hooks.min                   = min;
hooks.max                   = max;
hooks.now                   = now;
hooks.utc                   = createUTC;
hooks.unix                  = createUnix;
hooks.months                = listMonths;
hooks.isDate                = isDate;
hooks.locale                = getSetGlobalLocale;
hooks.invalid               = createInvalid;
hooks.duration              = createDuration;
hooks.isMoment              = isMoment;
hooks.weekdays              = listWeekdays;
hooks.parseZone             = createInZone;
hooks.localeData            = getLocale;
hooks.isDuration            = isDuration;
hooks.monthsShort           = listMonthsShort;
hooks.weekdaysMin           = listWeekdaysMin;
hooks.defineLocale          = defineLocale;
hooks.updateLocale          = updateLocale;
hooks.locales               = listLocales;
hooks.weekdaysShort         = listWeekdaysShort;
hooks.normalizeUnits        = normalizeUnits;
hooks.relativeTimeRounding  = getSetRelativeTimeRounding;
hooks.relativeTimeThreshold = getSetRelativeTimeThreshold;
hooks.calendarFormat        = getCalendarFormat;
hooks.prototype             = proto;

return hooks;

})));

},{}]},{},[2])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJqcy9ibG9jay9wZXJpb2RsaXN0L2luZGV4LmpzIiwianMvaW5kZXguanMiLCJqcy9saWIvYmFzZXZpZXcuanMiLCJqcy9saWIvYmluZEhhbmRsZXIuanMiLCJqcy9saWIvdXRpbHMuanMiLCJub2RlX21vZHVsZXMvbW9tZW50L2xvY2FsZS9kZS5qcyIsIm5vZGVfbW9kdWxlcy9tb21lbnQvbW9tZW50LmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7Ozs7QUNBQTs7Ozs7Ozs7Ozs7O0lBRU0sSTs7O0FBRUYsa0JBQWEsT0FBYixFQUFzQixPQUF0QixFQUErQjtBQUFBOztBQUFBLGdIQUNyQixPQURxQixFQUNaLE9BRFk7O0FBRTNCLGNBQUssVUFBTDtBQUNBLGdCQUFRLEdBQVIsQ0FBWSx1QkFBWixTQUEyQyxPQUEzQztBQUgyQjtBQUk5Qjs7OztxQ0FFWTtBQUFBOztBQUNULGlCQUFLLEtBQUwsQ0FBVyxHQUFYLENBQWUsT0FBZixFQUF3QixFQUF4QixDQUEyQixPQUEzQixFQUFvQywwQkFBcEMsRUFBZ0UsVUFBQyxFQUFELEVBQVE7QUFDcEUsbUJBQUcsY0FBSDtBQUNBLG1CQUFHLGVBQUg7QUFDQSx1QkFBSyxLQUFMLENBQVcsSUFBWCxDQUFnQiwwQkFBaEIsRUFBNEMsSUFBNUM7QUFDQSx1QkFBSyxLQUFMLENBQVcsSUFBWCxDQUFnQiwwQkFBaEIsRUFBNEMsV0FBNUMsQ0FBd0QsTUFBeEQ7QUFDSCxhQUxEO0FBTUg7Ozs7OztrQkFHVSxJOzs7Ozs7QUNoQmY7O0FBR0E7Ozs7QUFDQTs7OztBQUNBOzs7O0FBQ0E7O0FBRUE7O0FBRUE7Ozs7OztBQUVBOztBQUxBOzs7QUFMQTtBQVdBLGlCQUFPLENBQVAsb0IsQ0FqQkE7QUFDQTtBQUNBOztBQWlCQSxpQkFBTyxNQUFQLENBQWMsSUFBZDs7QUFFQTtBQUNBLFFBQVEsR0FBUixDQUFZLDJDQUFaOztBQUVBLHNCQUFFLGdCQUFGLEVBQW9CLElBQXBCLENBQXlCLFlBQVc7QUFDaEMsNkJBQW1CLElBQW5CLEVBQXlCLDhCQUFrQixJQUFsQixDQUF6QjtBQUNILENBRkQ7Ozs7Ozs7Ozs7Ozs7QUN4QkE7Ozs7QUFDQTs7Ozs7Ozs7Ozs7O0lBRU0sUTs7O0FBRUYsc0JBQVksT0FBWixFQUFtQztBQUFBLFlBQWQsT0FBYyx1RUFBSixFQUFJOztBQUFBOztBQUFBOztBQUUvQixjQUFLLEtBQUwsR0FBYSxzQkFBRSxPQUFGLENBQWI7QUFDQSxjQUFLLEtBQUwsQ0FBVyxHQUFYO0FBQ0EsY0FBSyxPQUFMLEdBQWUsT0FBZjtBQUorQjtBQUtsQzs7Ozs0QkFFUTtBQUNMLG1CQUFPLEtBQUssS0FBWjtBQUNIOzs7Ozs7a0JBSVUsUTs7Ozs7Ozs7Ozs7OztJQ2hCVCxXOzs7Ozs7OzRDQUU2QjtBQUMzQixnQkFBSSxTQUFTLElBQWI7O0FBRDJCLDhDQUFULE9BQVM7QUFBVCx1QkFBUztBQUFBOztBQUUzQixvQkFBUSxPQUFSLENBQWlCLFVBQVUsTUFBVixFQUFrQjtBQUMvQixvQkFBSSxPQUFPLE9BQU8sTUFBUCxDQUFQLEtBQTBCLFVBQTlCLEVBQTBDO0FBQ3RDLDBCQUFNLHVCQUF1QixNQUE3QjtBQUNIO0FBQ0QsdUJBQU8sTUFBUCxJQUFpQixPQUFPLE1BQVAsRUFBZSxJQUFmLENBQW9CLE1BQXBCLENBQWpCO0FBQ0gsYUFMRDtBQU1IOzs7Ozs7a0JBR1UsVzs7Ozs7Ozs7Ozs7O0FDZmY7Ozs7QUFDQTs7Ozs7Ozs7QUFFTyxJQUFNLG9DQUFjLFNBQWQsV0FBYyxDQUFDLElBQUQsRUFBVTtBQUNqQyxRQUFNLGFBQWEsc0JBQU8sSUFBUCxFQUFhLFVBQWIsQ0FBbkI7O0FBRUEsV0FBTyxXQUFXLEtBQVgsS0FBc0IsV0FBVyxPQUFYLEtBQXVCLEVBQXBEO0FBQ0gsQ0FKTTs7QUFNQSxJQUFNLDhDQUFtQixTQUFuQixnQkFBbUIsWUFBYTtBQUN6QyxRQUFNLGFBQWEsc0JBQU8sU0FBUCxFQUFrQixHQUFsQixDQUFuQjs7QUFFQSxXQUFPLFdBQVcsS0FBWCxLQUFzQixXQUFXLE9BQVgsS0FBdUIsRUFBcEQ7QUFDSCxDQUpNOztBQU1BLElBQU0sd0JBQVEsU0FBUixLQUFRLENBQUMsS0FBRCxFQUFRLEdBQVIsRUFBMEI7QUFBQSxRQUFiLElBQWEsdUVBQU4sQ0FBTTs7QUFDM0MsUUFBTSxTQUFTLEVBQWY7QUFDQSxTQUFLLElBQUksSUFBSSxLQUFiLEVBQW9CLEtBQUssR0FBekIsRUFBOEIsS0FBSyxJQUFuQyxFQUF5QztBQUNyQyxlQUFPLElBQVAsQ0FBWSxDQUFaO0FBQ0g7O0FBRUQsV0FBTyxNQUFQO0FBQ0gsQ0FQTTs7QUFTQSxJQUFNLDRCQUFVLFNBQVYsT0FBVSxDQUFDLEdBQUQ7QUFBQSxRQUFNLElBQU4sdUVBQWEsRUFBYjtBQUFBLFdBQW9CLEtBQUssTUFBTCxDQUFZLFVBQUMsS0FBRCxFQUFRLE9BQVI7QUFBQSxlQUFvQixRQUFRLE1BQU0sT0FBTixDQUFSLEdBQXlCLFNBQTdDO0FBQUEsS0FBWixFQUFvRSxHQUFwRSxDQUFwQjtBQUFBLENBQWhCOztBQUVQLElBQU0sb0JBQW9CLFNBQXBCLGlCQUFvQjtBQUFBLFdBQWMsTUFBTSxTQUFOLENBQWdCLEtBQWhCLENBQXNCLElBQXRCLENBQTJCLFVBQTNCLEVBQXVDLENBQXZDLENBQWQ7QUFBQSxDQUExQjs7QUFFTyxJQUFNLGdEQUFvQixTQUFwQixpQkFBb0IsQ0FBQyxPQUFELEVBQWE7QUFDMUMsUUFBTSxhQUFhLGtCQUFrQixRQUFRLFVBQTFCLENBQW5CO0FBQ0EsUUFBTSxZQUFZLFNBQWxCOztBQUVBLFdBQU8sV0FDRixNQURFLENBQ0s7QUFBQSxlQUFjLFVBQVUsSUFBVixDQUFlLFVBQVUsUUFBekIsQ0FBZDtBQUFBLEtBREwsRUFFRixHQUZFLENBRUU7QUFBQSxlQUFhLENBQ2QsVUFBVSxJQUFWLENBQWUsT0FBZixDQUF1QixTQUF2QixFQUFrQyxFQUFsQyxDQURjLEVBRWQsVUFBVSxLQUZJLENBQWI7QUFBQSxLQUZGLEVBTUYsTUFORSxDQU1LLFVBQUMsS0FBRCxRQUF5QjtBQUFBO0FBQUEsWUFBaEIsR0FBZ0I7QUFBQSxZQUFYLEtBQVc7O0FBQzdCLGNBQU0sR0FBTixJQUFhLFFBQVEsS0FBUixDQUFiO0FBQ0EsZUFBTyxLQUFQO0FBQ0gsS0FURSxFQVNBLEVBVEEsQ0FBUDtBQVVILENBZE07O0FBZ0JBLElBQU0sNEJBQVUsU0FBVixPQUFVLENBQUMsS0FBRCxFQUFXO0FBQzlCLFFBQUk7QUFDQSxlQUFPLEtBQUssS0FBTCxDQUFXLEtBQVgsQ0FBUDtBQUNILEtBRkQsQ0FFRSxPQUFPLENBQVAsRUFBVTtBQUNSLGVBQU8sS0FBUDtBQUNIO0FBQ0osQ0FOTTs7QUFRUCxJQUFNLGVBQWUsbUVBQXJCOztBQUVPLElBQU0sOEJBQVcsU0FBWCxRQUFXLENBQUMsYUFBRCxFQUFnQixpQkFBaEIsRUFBc0M7QUFDMUQsUUFBTSxrQkFBa0Isc0JBQUUsWUFBRixDQUF4Qjs7QUFFQSxRQUFJLENBQUMsYUFBTCxFQUFvQjtBQUNoQix3QkFBZ0Isc0JBQUUsTUFBRixDQUFoQjtBQUNBLHdCQUFnQixRQUFoQixDQUF5QixPQUF6QjtBQUNIOztBQUVELFFBQU0sa0JBQWtCLFNBQWxCLGVBQWtCLEdBQU07QUFDMUIsd0JBQWdCLEdBQWhCO0FBQ0Esd0JBQWdCLE1BQWhCO0FBQ0gsS0FIRDs7QUFLQSxRQUFNLHlCQUF5QixnQkFBZ0IsSUFBaEIsQ0FBcUIsb0JBQXJCLENBQS9COztBQUVBLG9CQUFnQixFQUFoQixDQUFtQixPQUFuQixFQUE0QixVQUFDLEVBQUQsRUFBUTtBQUNoQyxnQkFBUSxHQUFSLENBQVksa0JBQVosRUFBZ0MsRUFBaEM7QUFDQSxXQUFHLGVBQUg7QUFDQSxXQUFHLGNBQUg7QUFDQTtBQUNBO0FBQ0gsS0FORCxFQU1HLEVBTkgsQ0FNTSxPQU5OLEVBTWUsb0JBTmYsRUFNcUMsVUFBQyxFQUFELEVBQVE7QUFDekMsV0FBRyxlQUFIO0FBQ0gsS0FSRDs7QUFVQSwwQkFBRSxhQUFGLEVBQWlCLE1BQWpCLENBQXdCLGVBQXhCOztBQUVBLFdBQU87QUFDSCxzREFERztBQUVIO0FBRkcsS0FBUDtBQUlILENBL0JNOztBQWlDQSxJQUFNLHNCQUFPLFNBQVAsSUFBTyxHQUFNLENBQUUsQ0FBckI7O0FBRUEsSUFBTSw4Q0FBbUIsU0FBbkIsZ0JBQW1CLEdBQU07QUFDbEMsV0FBTyxTQUFTLFFBQVQsQ0FBa0IsTUFBbEIsQ0FBeUIsT0FBekIsQ0FBaUMsS0FBakMsRUFBd0MsRUFBeEMsRUFDUyxLQURULENBQ2UsR0FEZixFQUVTLE1BRlQsQ0FFZ0IsVUFBQyxLQUFELEVBQVEsT0FBUixFQUFvQjtBQUFBLDZCQUNILFFBQVEsS0FBUixDQUFjLEdBQWQsQ0FERztBQUFBO0FBQUEsWUFDakIsR0FEaUI7QUFBQSxZQUNaLEtBRFk7O0FBR3hCLFlBQUksR0FBSixFQUFTO0FBQ0wsbUJBQU8sT0FBTyxNQUFQLENBQWMsRUFBZCxFQUFrQixLQUFsQixzQkFBMkIsR0FBM0IsRUFBaUMsS0FBakMsRUFBUDtBQUNILFNBRkQsTUFFTztBQUNILG1CQUFPLEtBQVA7QUFDSDtBQUNKLEtBVlQsRUFVVyxFQVZYLENBQVA7QUFXSCxDQVpNOzs7QUN6RlA7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDOUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uIGUodCxuLHIpe2Z1bmN0aW9uIHMobyx1KXtpZighbltvXSl7aWYoIXRbb10pe3ZhciBhPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7aWYoIXUmJmEpcmV0dXJuIGEobywhMCk7aWYoaSlyZXR1cm4gaShvLCEwKTt2YXIgZj1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK28rXCInXCIpO3Rocm93IGYuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixmfXZhciBsPW5bb109e2V4cG9ydHM6e319O3Rbb11bMF0uY2FsbChsLmV4cG9ydHMsZnVuY3Rpb24oZSl7dmFyIG49dFtvXVsxXVtlXTtyZXR1cm4gcyhuP246ZSl9LGwsbC5leHBvcnRzLGUsdCxuLHIpfXJldHVybiBuW29dLmV4cG9ydHN9dmFyIGk9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtmb3IodmFyIG89MDtvPHIubGVuZ3RoO28rKylzKHJbb10pO3JldHVybiBzfSkiLCJpbXBvcnQgQmFzZVZpZXcgZnJvbSBcIi4uLy4uL2xpYi9iYXNldmlld1wiXG5cbmNsYXNzIFZpZXcgZXh0ZW5kcyBCYXNlVmlldyB7XG5cbiAgICBjb25zdHJ1Y3RvciAoZWxlbWVudCwgb3B0aW9ucykge1xuICAgICAgICBzdXBlcihlbGVtZW50LCBvcHRpb25zKTtcbiAgICAgICAgdGhpcy5iaW5kRXZlbnRzKCk7XG4gICAgICAgIGNvbnNvbGUubG9nKCdDb21wb25lbnQ6IFBlcmlvZGxpc3QnLCB0aGlzLCBvcHRpb25zKTtcbiAgICB9XG5cbiAgICBiaW5kRXZlbnRzKCkge1xuICAgICAgICB0aGlzLiRtYWluLm9mZignY2xpY2snKS5vbignY2xpY2snLCAnLnJlcG9ydC1wZXJpb2QtLXNob3ctYWxsJywgKGV2KSA9PiB7XG4gICAgICAgICAgICBldi5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICAgICAgZXYuc3RvcFByb3BhZ2F0aW9uKCk7XG4gICAgICAgICAgICB0aGlzLiRtYWluLmZpbmQoJy5yZXBvcnQtcGVyaW9kLS1zaG93LWFsbCcpLmhpZGUoKTtcbiAgICAgICAgICAgIHRoaXMuJG1haW4uZmluZChcIi5yZXBvcnQtcGVyaW9kLS10YWJsZSB0clwiKS5yZW1vdmVDbGFzcygnaGlkZScpO1xuICAgICAgICB9KVxuICAgIH1cbn1cblxuZXhwb3J0IGRlZmF1bHQgVmlldztcbiIsIi8vIC0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tXG4vLyBaTVMgU3RhdGlzdGljIGJlaGF2aW9yXG4vLyAtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLVxuXG5pbXBvcnQgJ2JhYmVsLXBvbHlmaWxsJztcblxuLy8gSW1wb3J0IGJhc2UgbGlic1xuaW1wb3J0IHdpbmRvdyBmcm9tIFwid2luZG93XCI7XG5pbXBvcnQgJCBmcm9tIFwianF1ZXJ5XCI7XG5pbXBvcnQgbW9tZW50IGZyb20gJ21vbWVudCdcbmltcG9ydCAnbW9tZW50L2xvY2FsZS9kZSc7XG4vL2ltcG9ydCBiaW5kUmVhY3QgZnJvbSAnLi9saWIvYmluZFJlYWN0LmpzJ1xuaW1wb3J0IHsgZ2V0RGF0YUF0dHJpYnV0ZXMgfSBmcm9tICcuL2xpYi91dGlscydcblxuaW1wb3J0IFBlcmlvZExpc3RWaWV3IGZyb20gJy4vYmxvY2svcGVyaW9kbGlzdCdcblxuLy8gQmluZCBqUXVlcnkgb24gJCBmb3IgdGVzdGluZ1xud2luZG93LiQgPSAkO1xuXG5tb21lbnQubG9jYWxlKCdkZScpXG5cbi8vIFNheSBoZWxsb1xuY29uc29sZS5sb2coXCJXZWxjb21lIHRvIHRoZSBaTVMgc3RhdGlzdGljIGludGVyZmFjZS4uLlwiKTtcblxuJCgnLnJlcG9ydC1wZXJpb2QnKS5lYWNoKGZ1bmN0aW9uKCkge1xuICAgIG5ldyBQZXJpb2RMaXN0Vmlldyh0aGlzLCBnZXREYXRhQXR0cmlidXRlcyh0aGlzKSk7XG59KVxuIiwiaW1wb3J0ICQgZnJvbSBcImpxdWVyeVwiO1xuaW1wb3J0IEJpbmRIYW5kbGVyIGZyb20gXCIuL2JpbmRIYW5kbGVyXCI7XG5cbmNsYXNzIEJhc2VWaWV3IGV4dGVuZHMgQmluZEhhbmRsZXIge1xuXG4gICAgY29uc3RydWN0b3IoZWxlbWVudCwgb3B0aW9ucyA9IHt9KSB7XG4gICAgICAgIHN1cGVyKCk7XG4gICAgICAgIHRoaXMuJG1haW4gPSAkKGVsZW1lbnQpO1xuICAgICAgICB0aGlzLiRtYWluLm9mZigpO1xuICAgICAgICB0aGlzLm9wdGlvbnMgPSBvcHRpb25zO1xuICAgIH1cblxuICAgIGdldCAkICgpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuJG1haW47XG4gICAgfVxuXG59XG5cbmV4cG9ydCBkZWZhdWx0IEJhc2VWaWV3O1xuIiwiXG5cbmNsYXNzIEJpbmRIYW5kbGVyIHtcblxuICAgIGJpbmRQdWJsaWNNZXRob2RzICguLi5tZXRob2RzKSB7XG4gICAgICAgIGxldCBvYmplY3QgPSB0aGlzO1xuICAgICAgICBtZXRob2RzLmZvckVhY2goIGZ1bmN0aW9uIChtZXRob2QpIHtcbiAgICAgICAgICAgIGlmICh0eXBlb2Ygb2JqZWN0W21ldGhvZF0gIT09ICdmdW5jdGlvbicpIHtcbiAgICAgICAgICAgICAgICB0aHJvdyBcIk1ldGhvZCBub3QgZm91bmQ6IFwiICsgbWV0aG9kO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgb2JqZWN0W21ldGhvZF0gPSBvYmplY3RbbWV0aG9kXS5iaW5kKG9iamVjdCk7XG4gICAgICAgIH0pO1xuICAgIH1cbn1cblxuZXhwb3J0IGRlZmF1bHQgQmluZEhhbmRsZXI7XG4iLCJpbXBvcnQgJCBmcm9tICdqcXVlcnknXG5pbXBvcnQgbW9tZW50IGZyb20gJ21vbWVudCdcblxuZXhwb3J0IGNvbnN0IHRpbWVUb0Zsb2F0ID0gKHRpbWUpID0+IHtcbiAgICBjb25zdCBtb21lbnRUaW1lID0gbW9tZW50KHRpbWUsICdISDptbTpzcycpXG5cbiAgICByZXR1cm4gbW9tZW50VGltZS5ob3VycygpICsgKG1vbWVudFRpbWUubWludXRlcygpIC8gNjApXG59XG5cbmV4cG9ydCBjb25zdCB0aW1lc3RhbXBUb0Zsb2F0ID0gdGltZXN0YW1wID0+IHtcbiAgICBjb25zdCBtb21lbnRUaW1lID0gbW9tZW50KHRpbWVzdGFtcCwgJ1gnKVxuXG4gICAgcmV0dXJuIG1vbWVudFRpbWUuaG91cnMoKSArIChtb21lbnRUaW1lLm1pbnV0ZXMoKSAvIDYwKVxufVxuXG5leHBvcnQgY29uc3QgcmFuZ2UgPSAoc3RhcnQsIGVuZCwgc3RlcCA9IDEpID0+IHtcbiAgICBjb25zdCByZXN1bHQgPSBbXVxuICAgIGZvciAobGV0IGkgPSBzdGFydDsgaSA8PSBlbmQ7IGkgKz0gc3RlcCkge1xuICAgICAgICByZXN1bHQucHVzaChpKVxuICAgIH1cblxuICAgIHJldHVybiByZXN1bHRcbn1cblxuZXhwb3J0IGNvbnN0IGRlZXBHZXQgPSAob2JqLCBwYXRoID0gW10pID0+IHBhdGgucmVkdWNlKChjYXJyeSwgY3VycmVudCkgPT4gY2FycnkgPyBjYXJyeVtjdXJyZW50XSA6IHVuZGVmaW5lZCwgb2JqKVxuXG5jb25zdCBhdHRyaWJ1dGVzVG9BcnJheSA9IGF0dHJpYnV0ZXMgPT4gQXJyYXkucHJvdG90eXBlLnNsaWNlLmNhbGwoYXR0cmlidXRlcywgMClcblxuZXhwb3J0IGNvbnN0IGdldERhdGFBdHRyaWJ1dGVzID0gKGVsZW1lbnQpID0+IHtcbiAgICBjb25zdCBhdHRyaWJ1dGVzID0gYXR0cmlidXRlc1RvQXJyYXkoZWxlbWVudC5hdHRyaWJ1dGVzKVxuICAgIGNvbnN0IGRhdGFSZWdleCA9IC9eZGF0YS0vaVxuXG4gICAgcmV0dXJuIGF0dHJpYnV0ZXNcbiAgICAgICAgLmZpbHRlcihhdHRyaWJ1dGUgPT4gKGRhdGFSZWdleC50ZXN0KGF0dHJpYnV0ZS5ub2RlTmFtZSkpKVxuICAgICAgICAubWFwKGF0dHJpYnV0ZSA9PiBbXG4gICAgICAgICAgICBhdHRyaWJ1dGUubmFtZS5yZXBsYWNlKGRhdGFSZWdleCwgJycpLFxuICAgICAgICAgICAgYXR0cmlidXRlLnZhbHVlXVxuICAgICAgICApXG4gICAgICAgIC5yZWR1Y2UoKGNhcnJ5LCBba2V5LCB2YWx1ZV0pID0+IHtcbiAgICAgICAgICAgIGNhcnJ5W2tleV0gPSB0cnlKc29uKHZhbHVlKVxuICAgICAgICAgICAgcmV0dXJuIGNhcnJ5XG4gICAgICAgIH0sIHt9KVxufVxuXG5leHBvcnQgY29uc3QgdHJ5SnNvbiA9IChpbnB1dCkgPT4ge1xuICAgIHRyeSB7XG4gICAgICAgIHJldHVybiBKU09OLnBhcnNlKGlucHV0KVxuICAgIH0gY2F0Y2ggKGUpIHtcbiAgICAgICAgcmV0dXJuIGlucHV0XG4gICAgfVxufVxuXG5jb25zdCBsaWdodGJveEh0bWwgPSAnPGRpdiBjbGFzcz1cImxpZ2h0Ym94XCI+PGRpdiBjbGFzcz1cImxpZ2h0Ym94X19jb250ZW50XCI+PC9kaXY+PC9kaXY+J1xuXG5leHBvcnQgY29uc3QgbGlnaHRib3ggPSAocGFyZW50RWxlbWVudCwgb25CYWNrZ3JvdW5kQ2xpY2spID0+IHtcbiAgICBjb25zdCBsaWdodGJveEVsZW1lbnQgPSAkKGxpZ2h0Ym94SHRtbClcblxuICAgIGlmICghcGFyZW50RWxlbWVudCkge1xuICAgICAgICBwYXJlbnRFbGVtZW50ID0gJCgnYm9keScpXG4gICAgICAgIGxpZ2h0Ym94RWxlbWVudC5hZGRDbGFzcygnZml4ZWQnKVxuICAgIH1cblxuICAgIGNvbnN0IGRlc3Ryb3lMaWdodGJveCA9ICgpID0+IHtcbiAgICAgICAgbGlnaHRib3hFbGVtZW50Lm9mZigpXG4gICAgICAgIGxpZ2h0Ym94RWxlbWVudC5yZW1vdmUoKVxuICAgIH1cblxuICAgIGNvbnN0IGxpZ2h0Ym94Q29udGVudEVsZW1lbnQgPSBsaWdodGJveEVsZW1lbnQuZmluZCgnLmxpZ2h0Ym94X19jb250ZW50Jyk7XG5cbiAgICBsaWdodGJveEVsZW1lbnQub24oJ2NsaWNrJywgKGV2KSA9PiB7XG4gICAgICAgIGNvbnNvbGUubG9nKCdiYWNrZ3JvdW5kIGNsaWNrJywgZXYpO1xuICAgICAgICBldi5zdG9wUHJvcGFnYXRpb24oKVxuICAgICAgICBldi5wcmV2ZW50RGVmYXVsdCgpXG4gICAgICAgIGRlc3Ryb3lMaWdodGJveCgpXG4gICAgICAgIG9uQmFja2dyb3VuZENsaWNrKClcbiAgICB9KS5vbignY2xpY2snLCAnLmxpZ2h0Ym94X19jb250ZW50JywgKGV2KSA9PiB7XG4gICAgICAgIGV2LnN0b3BQcm9wYWdhdGlvbigpO1xuICAgIH0pXG5cbiAgICAkKHBhcmVudEVsZW1lbnQpLmFwcGVuZChsaWdodGJveEVsZW1lbnQpXG5cbiAgICByZXR1cm4ge1xuICAgICAgICBsaWdodGJveENvbnRlbnRFbGVtZW50LFxuICAgICAgICBkZXN0cm95TGlnaHRib3hcbiAgICB9XG59XG5cbmV4cG9ydCBjb25zdCBub09wID0gKCkgPT4ge31cblxuZXhwb3J0IGNvbnN0IGdldFVybFBhcmFtZXRlcnMgPSAoKSA9PiB7XG4gICAgcmV0dXJuIGRvY3VtZW50LmxvY2F0aW9uLnNlYXJjaC5yZXBsYWNlKC9eXFw/LywgXCJcIilcbiAgICAgICAgICAgICAgICAgICAuc3BsaXQoXCImXCIpXG4gICAgICAgICAgICAgICAgICAgLnJlZHVjZSgoY2FycnksIGN1cnJlbnQpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgICAgY29uc3QgW2tleSwgdmFsdWVdID0gY3VycmVudC5zcGxpdCgnPScpXG5cbiAgICAgICAgICAgICAgICAgICAgICAgaWYgKGtleSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgcmV0dXJuIE9iamVjdC5hc3NpZ24oe30sIGNhcnJ5LCB7W2tleV06IHZhbHVlfSlcbiAgICAgICAgICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgIHJldHVybiBjYXJyeVxuICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgfSwge30pXG59XG4iLCIvLyEgbW9tZW50LmpzIGxvY2FsZSBjb25maWd1cmF0aW9uXG4vLyEgbG9jYWxlIDogR2VybWFuIFtkZV1cbi8vISBhdXRob3IgOiBsbHVjaHMgOiBodHRwczovL2dpdGh1Yi5jb20vbGx1Y2hzXG4vLyEgYXV0aG9yOiBNZW5lbGlvbiBFbGVuc8O6bGU6IGh0dHBzOi8vZ2l0aHViLmNvbS9PaXJlXG4vLyEgYXV0aG9yIDogTWlrb2xhaiBEYWRlbGEgOiBodHRwczovL2dpdGh1Yi5jb20vbWlrMDFhalxuXG47KGZ1bmN0aW9uIChnbG9iYWwsIGZhY3RvcnkpIHtcbiAgIHR5cGVvZiBleHBvcnRzID09PSAnb2JqZWN0JyAmJiB0eXBlb2YgbW9kdWxlICE9PSAndW5kZWZpbmVkJ1xuICAgICAgICYmIHR5cGVvZiByZXF1aXJlID09PSAnZnVuY3Rpb24nID8gZmFjdG9yeShyZXF1aXJlKCcuLi9tb21lbnQnKSkgOlxuICAgdHlwZW9mIGRlZmluZSA9PT0gJ2Z1bmN0aW9uJyAmJiBkZWZpbmUuYW1kID8gZGVmaW5lKFsnLi4vbW9tZW50J10sIGZhY3RvcnkpIDpcbiAgIGZhY3RvcnkoZ2xvYmFsLm1vbWVudClcbn0odGhpcywgKGZ1bmN0aW9uIChtb21lbnQpIHsgJ3VzZSBzdHJpY3QnO1xuXG5cbmZ1bmN0aW9uIHByb2Nlc3NSZWxhdGl2ZVRpbWUobnVtYmVyLCB3aXRob3V0U3VmZml4LCBrZXksIGlzRnV0dXJlKSB7XG4gICAgdmFyIGZvcm1hdCA9IHtcbiAgICAgICAgJ20nOiBbJ2VpbmUgTWludXRlJywgJ2VpbmVyIE1pbnV0ZSddLFxuICAgICAgICAnaCc6IFsnZWluZSBTdHVuZGUnLCAnZWluZXIgU3R1bmRlJ10sXG4gICAgICAgICdkJzogWydlaW4gVGFnJywgJ2VpbmVtIFRhZyddLFxuICAgICAgICAnZGQnOiBbbnVtYmVyICsgJyBUYWdlJywgbnVtYmVyICsgJyBUYWdlbiddLFxuICAgICAgICAnTSc6IFsnZWluIE1vbmF0JywgJ2VpbmVtIE1vbmF0J10sXG4gICAgICAgICdNTSc6IFtudW1iZXIgKyAnIE1vbmF0ZScsIG51bWJlciArICcgTW9uYXRlbiddLFxuICAgICAgICAneSc6IFsnZWluIEphaHInLCAnZWluZW0gSmFociddLFxuICAgICAgICAneXknOiBbbnVtYmVyICsgJyBKYWhyZScsIG51bWJlciArICcgSmFocmVuJ11cbiAgICB9O1xuICAgIHJldHVybiB3aXRob3V0U3VmZml4ID8gZm9ybWF0W2tleV1bMF0gOiBmb3JtYXRba2V5XVsxXTtcbn1cblxudmFyIGRlID0gbW9tZW50LmRlZmluZUxvY2FsZSgnZGUnLCB7XG4gICAgbW9udGhzIDogJ0phbnVhcl9GZWJydWFyX03DpHJ6X0FwcmlsX01haV9KdW5pX0p1bGlfQXVndXN0X1NlcHRlbWJlcl9Pa3RvYmVyX05vdmVtYmVyX0RlemVtYmVyJy5zcGxpdCgnXycpLFxuICAgIG1vbnRoc1Nob3J0IDogJ0phbi5fRmViLl9Nw6Ryel9BcHIuX01haV9KdW5pX0p1bGlfQXVnLl9TZXAuX09rdC5fTm92Ll9EZXouJy5zcGxpdCgnXycpLFxuICAgIG1vbnRoc1BhcnNlRXhhY3QgOiB0cnVlLFxuICAgIHdlZWtkYXlzIDogJ1Nvbm50YWdfTW9udGFnX0RpZW5zdGFnX01pdHR3b2NoX0Rvbm5lcnN0YWdfRnJlaXRhZ19TYW1zdGFnJy5zcGxpdCgnXycpLFxuICAgIHdlZWtkYXlzU2hvcnQgOiAnU28uX01vLl9EaS5fTWkuX0RvLl9Gci5fU2EuJy5zcGxpdCgnXycpLFxuICAgIHdlZWtkYXlzTWluIDogJ1NvX01vX0RpX01pX0RvX0ZyX1NhJy5zcGxpdCgnXycpLFxuICAgIHdlZWtkYXlzUGFyc2VFeGFjdCA6IHRydWUsXG4gICAgbG9uZ0RhdGVGb3JtYXQgOiB7XG4gICAgICAgIExUOiAnSEg6bW0nLFxuICAgICAgICBMVFM6ICdISDptbTpzcycsXG4gICAgICAgIEwgOiAnREQuTU0uWVlZWScsXG4gICAgICAgIExMIDogJ0QuIE1NTU0gWVlZWScsXG4gICAgICAgIExMTCA6ICdELiBNTU1NIFlZWVkgSEg6bW0nLFxuICAgICAgICBMTExMIDogJ2RkZGQsIEQuIE1NTU0gWVlZWSBISDptbSdcbiAgICB9LFxuICAgIGNhbGVuZGFyIDoge1xuICAgICAgICBzYW1lRGF5OiAnW2hldXRlIHVtXSBMVCBbVWhyXScsXG4gICAgICAgIHNhbWVFbHNlOiAnTCcsXG4gICAgICAgIG5leHREYXk6ICdbbW9yZ2VuIHVtXSBMVCBbVWhyXScsXG4gICAgICAgIG5leHRXZWVrOiAnZGRkZCBbdW1dIExUIFtVaHJdJyxcbiAgICAgICAgbGFzdERheTogJ1tnZXN0ZXJuIHVtXSBMVCBbVWhyXScsXG4gICAgICAgIGxhc3RXZWVrOiAnW2xldHp0ZW5dIGRkZGQgW3VtXSBMVCBbVWhyXSdcbiAgICB9LFxuICAgIHJlbGF0aXZlVGltZSA6IHtcbiAgICAgICAgZnV0dXJlIDogJ2luICVzJyxcbiAgICAgICAgcGFzdCA6ICd2b3IgJXMnLFxuICAgICAgICBzIDogJ2VpbiBwYWFyIFNla3VuZGVuJyxcbiAgICAgICAgbSA6IHByb2Nlc3NSZWxhdGl2ZVRpbWUsXG4gICAgICAgIG1tIDogJyVkIE1pbnV0ZW4nLFxuICAgICAgICBoIDogcHJvY2Vzc1JlbGF0aXZlVGltZSxcbiAgICAgICAgaGggOiAnJWQgU3R1bmRlbicsXG4gICAgICAgIGQgOiBwcm9jZXNzUmVsYXRpdmVUaW1lLFxuICAgICAgICBkZCA6IHByb2Nlc3NSZWxhdGl2ZVRpbWUsXG4gICAgICAgIE0gOiBwcm9jZXNzUmVsYXRpdmVUaW1lLFxuICAgICAgICBNTSA6IHByb2Nlc3NSZWxhdGl2ZVRpbWUsXG4gICAgICAgIHkgOiBwcm9jZXNzUmVsYXRpdmVUaW1lLFxuICAgICAgICB5eSA6IHByb2Nlc3NSZWxhdGl2ZVRpbWVcbiAgICB9LFxuICAgIGRheU9mTW9udGhPcmRpbmFsUGFyc2U6IC9cXGR7MSwyfVxcLi8sXG4gICAgb3JkaW5hbCA6ICclZC4nLFxuICAgIHdlZWsgOiB7XG4gICAgICAgIGRvdyA6IDEsIC8vIE1vbmRheSBpcyB0aGUgZmlyc3QgZGF5IG9mIHRoZSB3ZWVrLlxuICAgICAgICBkb3kgOiA0ICAvLyBUaGUgd2VlayB0aGF0IGNvbnRhaW5zIEphbiA0dGggaXMgdGhlIGZpcnN0IHdlZWsgb2YgdGhlIHllYXIuXG4gICAgfVxufSk7XG5cbnJldHVybiBkZTtcblxufSkpKTtcbiIsIi8vISBtb21lbnQuanNcbi8vISB2ZXJzaW9uIDogMi4xOS40XG4vLyEgYXV0aG9ycyA6IFRpbSBXb29kLCBJc2tyZW4gQ2hlcm5ldiwgTW9tZW50LmpzIGNvbnRyaWJ1dG9yc1xuLy8hIGxpY2Vuc2UgOiBNSVRcbi8vISBtb21lbnRqcy5jb21cblxuOyhmdW5jdGlvbiAoZ2xvYmFsLCBmYWN0b3J5KSB7XG4gICAgdHlwZW9mIGV4cG9ydHMgPT09ICdvYmplY3QnICYmIHR5cGVvZiBtb2R1bGUgIT09ICd1bmRlZmluZWQnID8gbW9kdWxlLmV4cG9ydHMgPSBmYWN0b3J5KCkgOlxuICAgIHR5cGVvZiBkZWZpbmUgPT09ICdmdW5jdGlvbicgJiYgZGVmaW5lLmFtZCA/IGRlZmluZShmYWN0b3J5KSA6XG4gICAgZ2xvYmFsLm1vbWVudCA9IGZhY3RvcnkoKVxufSh0aGlzLCAoZnVuY3Rpb24gKCkgeyAndXNlIHN0cmljdCc7XG5cbnZhciBob29rQ2FsbGJhY2s7XG5cbmZ1bmN0aW9uIGhvb2tzICgpIHtcbiAgICByZXR1cm4gaG9va0NhbGxiYWNrLmFwcGx5KG51bGwsIGFyZ3VtZW50cyk7XG59XG5cbi8vIFRoaXMgaXMgZG9uZSB0byByZWdpc3RlciB0aGUgbWV0aG9kIGNhbGxlZCB3aXRoIG1vbWVudCgpXG4vLyB3aXRob3V0IGNyZWF0aW5nIGNpcmN1bGFyIGRlcGVuZGVuY2llcy5cbmZ1bmN0aW9uIHNldEhvb2tDYWxsYmFjayAoY2FsbGJhY2spIHtcbiAgICBob29rQ2FsbGJhY2sgPSBjYWxsYmFjaztcbn1cblxuZnVuY3Rpb24gaXNBcnJheShpbnB1dCkge1xuICAgIHJldHVybiBpbnB1dCBpbnN0YW5jZW9mIEFycmF5IHx8IE9iamVjdC5wcm90b3R5cGUudG9TdHJpbmcuY2FsbChpbnB1dCkgPT09ICdbb2JqZWN0IEFycmF5XSc7XG59XG5cbmZ1bmN0aW9uIGlzT2JqZWN0KGlucHV0KSB7XG4gICAgLy8gSUU4IHdpbGwgdHJlYXQgdW5kZWZpbmVkIGFuZCBudWxsIGFzIG9iamVjdCBpZiBpdCB3YXNuJ3QgZm9yXG4gICAgLy8gaW5wdXQgIT0gbnVsbFxuICAgIHJldHVybiBpbnB1dCAhPSBudWxsICYmIE9iamVjdC5wcm90b3R5cGUudG9TdHJpbmcuY2FsbChpbnB1dCkgPT09ICdbb2JqZWN0IE9iamVjdF0nO1xufVxuXG5mdW5jdGlvbiBpc09iamVjdEVtcHR5KG9iaikge1xuICAgIGlmIChPYmplY3QuZ2V0T3duUHJvcGVydHlOYW1lcykge1xuICAgICAgICByZXR1cm4gKE9iamVjdC5nZXRPd25Qcm9wZXJ0eU5hbWVzKG9iaikubGVuZ3RoID09PSAwKTtcbiAgICB9IGVsc2Uge1xuICAgICAgICB2YXIgaztcbiAgICAgICAgZm9yIChrIGluIG9iaikge1xuICAgICAgICAgICAgaWYgKG9iai5oYXNPd25Qcm9wZXJ0eShrKSkge1xuICAgICAgICAgICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgICByZXR1cm4gdHJ1ZTtcbiAgICB9XG59XG5cbmZ1bmN0aW9uIGlzVW5kZWZpbmVkKGlucHV0KSB7XG4gICAgcmV0dXJuIGlucHV0ID09PSB2b2lkIDA7XG59XG5cbmZ1bmN0aW9uIGlzTnVtYmVyKGlucHV0KSB7XG4gICAgcmV0dXJuIHR5cGVvZiBpbnB1dCA9PT0gJ251bWJlcicgfHwgT2JqZWN0LnByb3RvdHlwZS50b1N0cmluZy5jYWxsKGlucHV0KSA9PT0gJ1tvYmplY3QgTnVtYmVyXSc7XG59XG5cbmZ1bmN0aW9uIGlzRGF0ZShpbnB1dCkge1xuICAgIHJldHVybiBpbnB1dCBpbnN0YW5jZW9mIERhdGUgfHwgT2JqZWN0LnByb3RvdHlwZS50b1N0cmluZy5jYWxsKGlucHV0KSA9PT0gJ1tvYmplY3QgRGF0ZV0nO1xufVxuXG5mdW5jdGlvbiBtYXAoYXJyLCBmbikge1xuICAgIHZhciByZXMgPSBbXSwgaTtcbiAgICBmb3IgKGkgPSAwOyBpIDwgYXJyLmxlbmd0aDsgKytpKSB7XG4gICAgICAgIHJlcy5wdXNoKGZuKGFycltpXSwgaSkpO1xuICAgIH1cbiAgICByZXR1cm4gcmVzO1xufVxuXG5mdW5jdGlvbiBoYXNPd25Qcm9wKGEsIGIpIHtcbiAgICByZXR1cm4gT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsKGEsIGIpO1xufVxuXG5mdW5jdGlvbiBleHRlbmQoYSwgYikge1xuICAgIGZvciAodmFyIGkgaW4gYikge1xuICAgICAgICBpZiAoaGFzT3duUHJvcChiLCBpKSkge1xuICAgICAgICAgICAgYVtpXSA9IGJbaV07XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBpZiAoaGFzT3duUHJvcChiLCAndG9TdHJpbmcnKSkge1xuICAgICAgICBhLnRvU3RyaW5nID0gYi50b1N0cmluZztcbiAgICB9XG5cbiAgICBpZiAoaGFzT3duUHJvcChiLCAndmFsdWVPZicpKSB7XG4gICAgICAgIGEudmFsdWVPZiA9IGIudmFsdWVPZjtcbiAgICB9XG5cbiAgICByZXR1cm4gYTtcbn1cblxuZnVuY3Rpb24gY3JlYXRlVVRDIChpbnB1dCwgZm9ybWF0LCBsb2NhbGUsIHN0cmljdCkge1xuICAgIHJldHVybiBjcmVhdGVMb2NhbE9yVVRDKGlucHV0LCBmb3JtYXQsIGxvY2FsZSwgc3RyaWN0LCB0cnVlKS51dGMoKTtcbn1cblxuZnVuY3Rpb24gZGVmYXVsdFBhcnNpbmdGbGFncygpIHtcbiAgICAvLyBXZSBuZWVkIHRvIGRlZXAgY2xvbmUgdGhpcyBvYmplY3QuXG4gICAgcmV0dXJuIHtcbiAgICAgICAgZW1wdHkgICAgICAgICAgIDogZmFsc2UsXG4gICAgICAgIHVudXNlZFRva2VucyAgICA6IFtdLFxuICAgICAgICB1bnVzZWRJbnB1dCAgICAgOiBbXSxcbiAgICAgICAgb3ZlcmZsb3cgICAgICAgIDogLTIsXG4gICAgICAgIGNoYXJzTGVmdE92ZXIgICA6IDAsXG4gICAgICAgIG51bGxJbnB1dCAgICAgICA6IGZhbHNlLFxuICAgICAgICBpbnZhbGlkTW9udGggICAgOiBudWxsLFxuICAgICAgICBpbnZhbGlkRm9ybWF0ICAgOiBmYWxzZSxcbiAgICAgICAgdXNlckludmFsaWRhdGVkIDogZmFsc2UsXG4gICAgICAgIGlzbyAgICAgICAgICAgICA6IGZhbHNlLFxuICAgICAgICBwYXJzZWREYXRlUGFydHMgOiBbXSxcbiAgICAgICAgbWVyaWRpZW0gICAgICAgIDogbnVsbCxcbiAgICAgICAgcmZjMjgyMiAgICAgICAgIDogZmFsc2UsXG4gICAgICAgIHdlZWtkYXlNaXNtYXRjaCA6IGZhbHNlXG4gICAgfTtcbn1cblxuZnVuY3Rpb24gZ2V0UGFyc2luZ0ZsYWdzKG0pIHtcbiAgICBpZiAobS5fcGYgPT0gbnVsbCkge1xuICAgICAgICBtLl9wZiA9IGRlZmF1bHRQYXJzaW5nRmxhZ3MoKTtcbiAgICB9XG4gICAgcmV0dXJuIG0uX3BmO1xufVxuXG52YXIgc29tZTtcbmlmIChBcnJheS5wcm90b3R5cGUuc29tZSkge1xuICAgIHNvbWUgPSBBcnJheS5wcm90b3R5cGUuc29tZTtcbn0gZWxzZSB7XG4gICAgc29tZSA9IGZ1bmN0aW9uIChmdW4pIHtcbiAgICAgICAgdmFyIHQgPSBPYmplY3QodGhpcyk7XG4gICAgICAgIHZhciBsZW4gPSB0Lmxlbmd0aCA+Pj4gMDtcblxuICAgICAgICBmb3IgKHZhciBpID0gMDsgaSA8IGxlbjsgaSsrKSB7XG4gICAgICAgICAgICBpZiAoaSBpbiB0ICYmIGZ1bi5jYWxsKHRoaXMsIHRbaV0sIGksIHQpKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuIHRydWU7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgfTtcbn1cblxuZnVuY3Rpb24gaXNWYWxpZChtKSB7XG4gICAgaWYgKG0uX2lzVmFsaWQgPT0gbnVsbCkge1xuICAgICAgICB2YXIgZmxhZ3MgPSBnZXRQYXJzaW5nRmxhZ3MobSk7XG4gICAgICAgIHZhciBwYXJzZWRQYXJ0cyA9IHNvbWUuY2FsbChmbGFncy5wYXJzZWREYXRlUGFydHMsIGZ1bmN0aW9uIChpKSB7XG4gICAgICAgICAgICByZXR1cm4gaSAhPSBudWxsO1xuICAgICAgICB9KTtcbiAgICAgICAgdmFyIGlzTm93VmFsaWQgPSAhaXNOYU4obS5fZC5nZXRUaW1lKCkpICYmXG4gICAgICAgICAgICBmbGFncy5vdmVyZmxvdyA8IDAgJiZcbiAgICAgICAgICAgICFmbGFncy5lbXB0eSAmJlxuICAgICAgICAgICAgIWZsYWdzLmludmFsaWRNb250aCAmJlxuICAgICAgICAgICAgIWZsYWdzLmludmFsaWRXZWVrZGF5ICYmXG4gICAgICAgICAgICAhZmxhZ3Mud2Vla2RheU1pc21hdGNoICYmXG4gICAgICAgICAgICAhZmxhZ3MubnVsbElucHV0ICYmXG4gICAgICAgICAgICAhZmxhZ3MuaW52YWxpZEZvcm1hdCAmJlxuICAgICAgICAgICAgIWZsYWdzLnVzZXJJbnZhbGlkYXRlZCAmJlxuICAgICAgICAgICAgKCFmbGFncy5tZXJpZGllbSB8fCAoZmxhZ3MubWVyaWRpZW0gJiYgcGFyc2VkUGFydHMpKTtcblxuICAgICAgICBpZiAobS5fc3RyaWN0KSB7XG4gICAgICAgICAgICBpc05vd1ZhbGlkID0gaXNOb3dWYWxpZCAmJlxuICAgICAgICAgICAgICAgIGZsYWdzLmNoYXJzTGVmdE92ZXIgPT09IDAgJiZcbiAgICAgICAgICAgICAgICBmbGFncy51bnVzZWRUb2tlbnMubGVuZ3RoID09PSAwICYmXG4gICAgICAgICAgICAgICAgZmxhZ3MuYmlnSG91ciA9PT0gdW5kZWZpbmVkO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKE9iamVjdC5pc0Zyb3plbiA9PSBudWxsIHx8ICFPYmplY3QuaXNGcm96ZW4obSkpIHtcbiAgICAgICAgICAgIG0uX2lzVmFsaWQgPSBpc05vd1ZhbGlkO1xuICAgICAgICB9XG4gICAgICAgIGVsc2Uge1xuICAgICAgICAgICAgcmV0dXJuIGlzTm93VmFsaWQ7XG4gICAgICAgIH1cbiAgICB9XG4gICAgcmV0dXJuIG0uX2lzVmFsaWQ7XG59XG5cbmZ1bmN0aW9uIGNyZWF0ZUludmFsaWQgKGZsYWdzKSB7XG4gICAgdmFyIG0gPSBjcmVhdGVVVEMoTmFOKTtcbiAgICBpZiAoZmxhZ3MgIT0gbnVsbCkge1xuICAgICAgICBleHRlbmQoZ2V0UGFyc2luZ0ZsYWdzKG0pLCBmbGFncyk7XG4gICAgfVxuICAgIGVsc2Uge1xuICAgICAgICBnZXRQYXJzaW5nRmxhZ3MobSkudXNlckludmFsaWRhdGVkID0gdHJ1ZTtcbiAgICB9XG5cbiAgICByZXR1cm4gbTtcbn1cblxuLy8gUGx1Z2lucyB0aGF0IGFkZCBwcm9wZXJ0aWVzIHNob3VsZCBhbHNvIGFkZCB0aGUga2V5IGhlcmUgKG51bGwgdmFsdWUpLFxuLy8gc28gd2UgY2FuIHByb3Blcmx5IGNsb25lIG91cnNlbHZlcy5cbnZhciBtb21lbnRQcm9wZXJ0aWVzID0gaG9va3MubW9tZW50UHJvcGVydGllcyA9IFtdO1xuXG5mdW5jdGlvbiBjb3B5Q29uZmlnKHRvLCBmcm9tKSB7XG4gICAgdmFyIGksIHByb3AsIHZhbDtcblxuICAgIGlmICghaXNVbmRlZmluZWQoZnJvbS5faXNBTW9tZW50T2JqZWN0KSkge1xuICAgICAgICB0by5faXNBTW9tZW50T2JqZWN0ID0gZnJvbS5faXNBTW9tZW50T2JqZWN0O1xuICAgIH1cbiAgICBpZiAoIWlzVW5kZWZpbmVkKGZyb20uX2kpKSB7XG4gICAgICAgIHRvLl9pID0gZnJvbS5faTtcbiAgICB9XG4gICAgaWYgKCFpc1VuZGVmaW5lZChmcm9tLl9mKSkge1xuICAgICAgICB0by5fZiA9IGZyb20uX2Y7XG4gICAgfVxuICAgIGlmICghaXNVbmRlZmluZWQoZnJvbS5fbCkpIHtcbiAgICAgICAgdG8uX2wgPSBmcm9tLl9sO1xuICAgIH1cbiAgICBpZiAoIWlzVW5kZWZpbmVkKGZyb20uX3N0cmljdCkpIHtcbiAgICAgICAgdG8uX3N0cmljdCA9IGZyb20uX3N0cmljdDtcbiAgICB9XG4gICAgaWYgKCFpc1VuZGVmaW5lZChmcm9tLl90em0pKSB7XG4gICAgICAgIHRvLl90em0gPSBmcm9tLl90em07XG4gICAgfVxuICAgIGlmICghaXNVbmRlZmluZWQoZnJvbS5faXNVVEMpKSB7XG4gICAgICAgIHRvLl9pc1VUQyA9IGZyb20uX2lzVVRDO1xuICAgIH1cbiAgICBpZiAoIWlzVW5kZWZpbmVkKGZyb20uX29mZnNldCkpIHtcbiAgICAgICAgdG8uX29mZnNldCA9IGZyb20uX29mZnNldDtcbiAgICB9XG4gICAgaWYgKCFpc1VuZGVmaW5lZChmcm9tLl9wZikpIHtcbiAgICAgICAgdG8uX3BmID0gZ2V0UGFyc2luZ0ZsYWdzKGZyb20pO1xuICAgIH1cbiAgICBpZiAoIWlzVW5kZWZpbmVkKGZyb20uX2xvY2FsZSkpIHtcbiAgICAgICAgdG8uX2xvY2FsZSA9IGZyb20uX2xvY2FsZTtcbiAgICB9XG5cbiAgICBpZiAobW9tZW50UHJvcGVydGllcy5sZW5ndGggPiAwKSB7XG4gICAgICAgIGZvciAoaSA9IDA7IGkgPCBtb21lbnRQcm9wZXJ0aWVzLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgICAgICBwcm9wID0gbW9tZW50UHJvcGVydGllc1tpXTtcbiAgICAgICAgICAgIHZhbCA9IGZyb21bcHJvcF07XG4gICAgICAgICAgICBpZiAoIWlzVW5kZWZpbmVkKHZhbCkpIHtcbiAgICAgICAgICAgICAgICB0b1twcm9wXSA9IHZhbDtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgIH1cblxuICAgIHJldHVybiB0bztcbn1cblxudmFyIHVwZGF0ZUluUHJvZ3Jlc3MgPSBmYWxzZTtcblxuLy8gTW9tZW50IHByb3RvdHlwZSBvYmplY3RcbmZ1bmN0aW9uIE1vbWVudChjb25maWcpIHtcbiAgICBjb3B5Q29uZmlnKHRoaXMsIGNvbmZpZyk7XG4gICAgdGhpcy5fZCA9IG5ldyBEYXRlKGNvbmZpZy5fZCAhPSBudWxsID8gY29uZmlnLl9kLmdldFRpbWUoKSA6IE5hTik7XG4gICAgaWYgKCF0aGlzLmlzVmFsaWQoKSkge1xuICAgICAgICB0aGlzLl9kID0gbmV3IERhdGUoTmFOKTtcbiAgICB9XG4gICAgLy8gUHJldmVudCBpbmZpbml0ZSBsb29wIGluIGNhc2UgdXBkYXRlT2Zmc2V0IGNyZWF0ZXMgbmV3IG1vbWVudFxuICAgIC8vIG9iamVjdHMuXG4gICAgaWYgKHVwZGF0ZUluUHJvZ3Jlc3MgPT09IGZhbHNlKSB7XG4gICAgICAgIHVwZGF0ZUluUHJvZ3Jlc3MgPSB0cnVlO1xuICAgICAgICBob29rcy51cGRhdGVPZmZzZXQodGhpcyk7XG4gICAgICAgIHVwZGF0ZUluUHJvZ3Jlc3MgPSBmYWxzZTtcbiAgICB9XG59XG5cbmZ1bmN0aW9uIGlzTW9tZW50IChvYmopIHtcbiAgICByZXR1cm4gb2JqIGluc3RhbmNlb2YgTW9tZW50IHx8IChvYmogIT0gbnVsbCAmJiBvYmouX2lzQU1vbWVudE9iamVjdCAhPSBudWxsKTtcbn1cblxuZnVuY3Rpb24gYWJzRmxvb3IgKG51bWJlcikge1xuICAgIGlmIChudW1iZXIgPCAwKSB7XG4gICAgICAgIC8vIC0wIC0+IDBcbiAgICAgICAgcmV0dXJuIE1hdGguY2VpbChudW1iZXIpIHx8IDA7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgcmV0dXJuIE1hdGguZmxvb3IobnVtYmVyKTtcbiAgICB9XG59XG5cbmZ1bmN0aW9uIHRvSW50KGFyZ3VtZW50Rm9yQ29lcmNpb24pIHtcbiAgICB2YXIgY29lcmNlZE51bWJlciA9ICthcmd1bWVudEZvckNvZXJjaW9uLFxuICAgICAgICB2YWx1ZSA9IDA7XG5cbiAgICBpZiAoY29lcmNlZE51bWJlciAhPT0gMCAmJiBpc0Zpbml0ZShjb2VyY2VkTnVtYmVyKSkge1xuICAgICAgICB2YWx1ZSA9IGFic0Zsb29yKGNvZXJjZWROdW1iZXIpO1xuICAgIH1cblxuICAgIHJldHVybiB2YWx1ZTtcbn1cblxuLy8gY29tcGFyZSB0d28gYXJyYXlzLCByZXR1cm4gdGhlIG51bWJlciBvZiBkaWZmZXJlbmNlc1xuZnVuY3Rpb24gY29tcGFyZUFycmF5cyhhcnJheTEsIGFycmF5MiwgZG9udENvbnZlcnQpIHtcbiAgICB2YXIgbGVuID0gTWF0aC5taW4oYXJyYXkxLmxlbmd0aCwgYXJyYXkyLmxlbmd0aCksXG4gICAgICAgIGxlbmd0aERpZmYgPSBNYXRoLmFicyhhcnJheTEubGVuZ3RoIC0gYXJyYXkyLmxlbmd0aCksXG4gICAgICAgIGRpZmZzID0gMCxcbiAgICAgICAgaTtcbiAgICBmb3IgKGkgPSAwOyBpIDwgbGVuOyBpKyspIHtcbiAgICAgICAgaWYgKChkb250Q29udmVydCAmJiBhcnJheTFbaV0gIT09IGFycmF5MltpXSkgfHxcbiAgICAgICAgICAgICghZG9udENvbnZlcnQgJiYgdG9JbnQoYXJyYXkxW2ldKSAhPT0gdG9JbnQoYXJyYXkyW2ldKSkpIHtcbiAgICAgICAgICAgIGRpZmZzKys7XG4gICAgICAgIH1cbiAgICB9XG4gICAgcmV0dXJuIGRpZmZzICsgbGVuZ3RoRGlmZjtcbn1cblxuZnVuY3Rpb24gd2Fybihtc2cpIHtcbiAgICBpZiAoaG9va3Muc3VwcHJlc3NEZXByZWNhdGlvbldhcm5pbmdzID09PSBmYWxzZSAmJlxuICAgICAgICAgICAgKHR5cGVvZiBjb25zb2xlICE9PSAgJ3VuZGVmaW5lZCcpICYmIGNvbnNvbGUud2Fybikge1xuICAgICAgICBjb25zb2xlLndhcm4oJ0RlcHJlY2F0aW9uIHdhcm5pbmc6ICcgKyBtc2cpO1xuICAgIH1cbn1cblxuZnVuY3Rpb24gZGVwcmVjYXRlKG1zZywgZm4pIHtcbiAgICB2YXIgZmlyc3RUaW1lID0gdHJ1ZTtcblxuICAgIHJldHVybiBleHRlbmQoZnVuY3Rpb24gKCkge1xuICAgICAgICBpZiAoaG9va3MuZGVwcmVjYXRpb25IYW5kbGVyICE9IG51bGwpIHtcbiAgICAgICAgICAgIGhvb2tzLmRlcHJlY2F0aW9uSGFuZGxlcihudWxsLCBtc2cpO1xuICAgICAgICB9XG4gICAgICAgIGlmIChmaXJzdFRpbWUpIHtcbiAgICAgICAgICAgIHZhciBhcmdzID0gW107XG4gICAgICAgICAgICB2YXIgYXJnO1xuICAgICAgICAgICAgZm9yICh2YXIgaSA9IDA7IGkgPCBhcmd1bWVudHMubGVuZ3RoOyBpKyspIHtcbiAgICAgICAgICAgICAgICBhcmcgPSAnJztcbiAgICAgICAgICAgICAgICBpZiAodHlwZW9mIGFyZ3VtZW50c1tpXSA9PT0gJ29iamVjdCcpIHtcbiAgICAgICAgICAgICAgICAgICAgYXJnICs9ICdcXG5bJyArIGkgKyAnXSAnO1xuICAgICAgICAgICAgICAgICAgICBmb3IgKHZhciBrZXkgaW4gYXJndW1lbnRzWzBdKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBhcmcgKz0ga2V5ICsgJzogJyArIGFyZ3VtZW50c1swXVtrZXldICsgJywgJztcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICBhcmcgPSBhcmcuc2xpY2UoMCwgLTIpOyAvLyBSZW1vdmUgdHJhaWxpbmcgY29tbWEgYW5kIHNwYWNlXG4gICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgYXJnID0gYXJndW1lbnRzW2ldO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICBhcmdzLnB1c2goYXJnKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIHdhcm4obXNnICsgJ1xcbkFyZ3VtZW50czogJyArIEFycmF5LnByb3RvdHlwZS5zbGljZS5jYWxsKGFyZ3MpLmpvaW4oJycpICsgJ1xcbicgKyAobmV3IEVycm9yKCkpLnN0YWNrKTtcbiAgICAgICAgICAgIGZpcnN0VGltZSA9IGZhbHNlO1xuICAgICAgICB9XG4gICAgICAgIHJldHVybiBmbi5hcHBseSh0aGlzLCBhcmd1bWVudHMpO1xuICAgIH0sIGZuKTtcbn1cblxudmFyIGRlcHJlY2F0aW9ucyA9IHt9O1xuXG5mdW5jdGlvbiBkZXByZWNhdGVTaW1wbGUobmFtZSwgbXNnKSB7XG4gICAgaWYgKGhvb2tzLmRlcHJlY2F0aW9uSGFuZGxlciAhPSBudWxsKSB7XG4gICAgICAgIGhvb2tzLmRlcHJlY2F0aW9uSGFuZGxlcihuYW1lLCBtc2cpO1xuICAgIH1cbiAgICBpZiAoIWRlcHJlY2F0aW9uc1tuYW1lXSkge1xuICAgICAgICB3YXJuKG1zZyk7XG4gICAgICAgIGRlcHJlY2F0aW9uc1tuYW1lXSA9IHRydWU7XG4gICAgfVxufVxuXG5ob29rcy5zdXBwcmVzc0RlcHJlY2F0aW9uV2FybmluZ3MgPSBmYWxzZTtcbmhvb2tzLmRlcHJlY2F0aW9uSGFuZGxlciA9IG51bGw7XG5cbmZ1bmN0aW9uIGlzRnVuY3Rpb24oaW5wdXQpIHtcbiAgICByZXR1cm4gaW5wdXQgaW5zdGFuY2VvZiBGdW5jdGlvbiB8fCBPYmplY3QucHJvdG90eXBlLnRvU3RyaW5nLmNhbGwoaW5wdXQpID09PSAnW29iamVjdCBGdW5jdGlvbl0nO1xufVxuXG5mdW5jdGlvbiBzZXQgKGNvbmZpZykge1xuICAgIHZhciBwcm9wLCBpO1xuICAgIGZvciAoaSBpbiBjb25maWcpIHtcbiAgICAgICAgcHJvcCA9IGNvbmZpZ1tpXTtcbiAgICAgICAgaWYgKGlzRnVuY3Rpb24ocHJvcCkpIHtcbiAgICAgICAgICAgIHRoaXNbaV0gPSBwcm9wO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgdGhpc1snXycgKyBpXSA9IHByb3A7XG4gICAgICAgIH1cbiAgICB9XG4gICAgdGhpcy5fY29uZmlnID0gY29uZmlnO1xuICAgIC8vIExlbmllbnQgb3JkaW5hbCBwYXJzaW5nIGFjY2VwdHMganVzdCBhIG51bWJlciBpbiBhZGRpdGlvbiB0b1xuICAgIC8vIG51bWJlciArIChwb3NzaWJseSkgc3R1ZmYgY29taW5nIGZyb20gX2RheU9mTW9udGhPcmRpbmFsUGFyc2UuXG4gICAgLy8gVE9ETzogUmVtb3ZlIFwib3JkaW5hbFBhcnNlXCIgZmFsbGJhY2sgaW4gbmV4dCBtYWpvciByZWxlYXNlLlxuICAgIHRoaXMuX2RheU9mTW9udGhPcmRpbmFsUGFyc2VMZW5pZW50ID0gbmV3IFJlZ0V4cChcbiAgICAgICAgKHRoaXMuX2RheU9mTW9udGhPcmRpbmFsUGFyc2Uuc291cmNlIHx8IHRoaXMuX29yZGluYWxQYXJzZS5zb3VyY2UpICtcbiAgICAgICAgICAgICd8JyArICgvXFxkezEsMn0vKS5zb3VyY2UpO1xufVxuXG5mdW5jdGlvbiBtZXJnZUNvbmZpZ3MocGFyZW50Q29uZmlnLCBjaGlsZENvbmZpZykge1xuICAgIHZhciByZXMgPSBleHRlbmQoe30sIHBhcmVudENvbmZpZyksIHByb3A7XG4gICAgZm9yIChwcm9wIGluIGNoaWxkQ29uZmlnKSB7XG4gICAgICAgIGlmIChoYXNPd25Qcm9wKGNoaWxkQ29uZmlnLCBwcm9wKSkge1xuICAgICAgICAgICAgaWYgKGlzT2JqZWN0KHBhcmVudENvbmZpZ1twcm9wXSkgJiYgaXNPYmplY3QoY2hpbGRDb25maWdbcHJvcF0pKSB7XG4gICAgICAgICAgICAgICAgcmVzW3Byb3BdID0ge307XG4gICAgICAgICAgICAgICAgZXh0ZW5kKHJlc1twcm9wXSwgcGFyZW50Q29uZmlnW3Byb3BdKTtcbiAgICAgICAgICAgICAgICBleHRlbmQocmVzW3Byb3BdLCBjaGlsZENvbmZpZ1twcm9wXSk7XG4gICAgICAgICAgICB9IGVsc2UgaWYgKGNoaWxkQ29uZmlnW3Byb3BdICE9IG51bGwpIHtcbiAgICAgICAgICAgICAgICByZXNbcHJvcF0gPSBjaGlsZENvbmZpZ1twcm9wXTtcbiAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgZGVsZXRlIHJlc1twcm9wXTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgIH1cbiAgICBmb3IgKHByb3AgaW4gcGFyZW50Q29uZmlnKSB7XG4gICAgICAgIGlmIChoYXNPd25Qcm9wKHBhcmVudENvbmZpZywgcHJvcCkgJiZcbiAgICAgICAgICAgICAgICAhaGFzT3duUHJvcChjaGlsZENvbmZpZywgcHJvcCkgJiZcbiAgICAgICAgICAgICAgICBpc09iamVjdChwYXJlbnRDb25maWdbcHJvcF0pKSB7XG4gICAgICAgICAgICAvLyBtYWtlIHN1cmUgY2hhbmdlcyB0byBwcm9wZXJ0aWVzIGRvbid0IG1vZGlmeSBwYXJlbnQgY29uZmlnXG4gICAgICAgICAgICByZXNbcHJvcF0gPSBleHRlbmQoe30sIHJlc1twcm9wXSk7XG4gICAgICAgIH1cbiAgICB9XG4gICAgcmV0dXJuIHJlcztcbn1cblxuZnVuY3Rpb24gTG9jYWxlKGNvbmZpZykge1xuICAgIGlmIChjb25maWcgIT0gbnVsbCkge1xuICAgICAgICB0aGlzLnNldChjb25maWcpO1xuICAgIH1cbn1cblxudmFyIGtleXM7XG5cbmlmIChPYmplY3Qua2V5cykge1xuICAgIGtleXMgPSBPYmplY3Qua2V5cztcbn0gZWxzZSB7XG4gICAga2V5cyA9IGZ1bmN0aW9uIChvYmopIHtcbiAgICAgICAgdmFyIGksIHJlcyA9IFtdO1xuICAgICAgICBmb3IgKGkgaW4gb2JqKSB7XG4gICAgICAgICAgICBpZiAoaGFzT3duUHJvcChvYmosIGkpKSB7XG4gICAgICAgICAgICAgICAgcmVzLnB1c2goaSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICAgICAgcmV0dXJuIHJlcztcbiAgICB9O1xufVxuXG52YXIgZGVmYXVsdENhbGVuZGFyID0ge1xuICAgIHNhbWVEYXkgOiAnW1RvZGF5IGF0XSBMVCcsXG4gICAgbmV4dERheSA6ICdbVG9tb3Jyb3cgYXRdIExUJyxcbiAgICBuZXh0V2VlayA6ICdkZGRkIFthdF0gTFQnLFxuICAgIGxhc3REYXkgOiAnW1llc3RlcmRheSBhdF0gTFQnLFxuICAgIGxhc3RXZWVrIDogJ1tMYXN0XSBkZGRkIFthdF0gTFQnLFxuICAgIHNhbWVFbHNlIDogJ0wnXG59O1xuXG5mdW5jdGlvbiBjYWxlbmRhciAoa2V5LCBtb20sIG5vdykge1xuICAgIHZhciBvdXRwdXQgPSB0aGlzLl9jYWxlbmRhcltrZXldIHx8IHRoaXMuX2NhbGVuZGFyWydzYW1lRWxzZSddO1xuICAgIHJldHVybiBpc0Z1bmN0aW9uKG91dHB1dCkgPyBvdXRwdXQuY2FsbChtb20sIG5vdykgOiBvdXRwdXQ7XG59XG5cbnZhciBkZWZhdWx0TG9uZ0RhdGVGb3JtYXQgPSB7XG4gICAgTFRTICA6ICdoOm1tOnNzIEEnLFxuICAgIExUICAgOiAnaDptbSBBJyxcbiAgICBMICAgIDogJ01NL0REL1lZWVknLFxuICAgIExMICAgOiAnTU1NTSBELCBZWVlZJyxcbiAgICBMTEwgIDogJ01NTU0gRCwgWVlZWSBoOm1tIEEnLFxuICAgIExMTEwgOiAnZGRkZCwgTU1NTSBELCBZWVlZIGg6bW0gQSdcbn07XG5cbmZ1bmN0aW9uIGxvbmdEYXRlRm9ybWF0IChrZXkpIHtcbiAgICB2YXIgZm9ybWF0ID0gdGhpcy5fbG9uZ0RhdGVGb3JtYXRba2V5XSxcbiAgICAgICAgZm9ybWF0VXBwZXIgPSB0aGlzLl9sb25nRGF0ZUZvcm1hdFtrZXkudG9VcHBlckNhc2UoKV07XG5cbiAgICBpZiAoZm9ybWF0IHx8ICFmb3JtYXRVcHBlcikge1xuICAgICAgICByZXR1cm4gZm9ybWF0O1xuICAgIH1cblxuICAgIHRoaXMuX2xvbmdEYXRlRm9ybWF0W2tleV0gPSBmb3JtYXRVcHBlci5yZXBsYWNlKC9NTU1NfE1NfEREfGRkZGQvZywgZnVuY3Rpb24gKHZhbCkge1xuICAgICAgICByZXR1cm4gdmFsLnNsaWNlKDEpO1xuICAgIH0pO1xuXG4gICAgcmV0dXJuIHRoaXMuX2xvbmdEYXRlRm9ybWF0W2tleV07XG59XG5cbnZhciBkZWZhdWx0SW52YWxpZERhdGUgPSAnSW52YWxpZCBkYXRlJztcblxuZnVuY3Rpb24gaW52YWxpZERhdGUgKCkge1xuICAgIHJldHVybiB0aGlzLl9pbnZhbGlkRGF0ZTtcbn1cblxudmFyIGRlZmF1bHRPcmRpbmFsID0gJyVkJztcbnZhciBkZWZhdWx0RGF5T2ZNb250aE9yZGluYWxQYXJzZSA9IC9cXGR7MSwyfS87XG5cbmZ1bmN0aW9uIG9yZGluYWwgKG51bWJlcikge1xuICAgIHJldHVybiB0aGlzLl9vcmRpbmFsLnJlcGxhY2UoJyVkJywgbnVtYmVyKTtcbn1cblxudmFyIGRlZmF1bHRSZWxhdGl2ZVRpbWUgPSB7XG4gICAgZnV0dXJlIDogJ2luICVzJyxcbiAgICBwYXN0ICAgOiAnJXMgYWdvJyxcbiAgICBzICA6ICdhIGZldyBzZWNvbmRzJyxcbiAgICBzcyA6ICclZCBzZWNvbmRzJyxcbiAgICBtICA6ICdhIG1pbnV0ZScsXG4gICAgbW0gOiAnJWQgbWludXRlcycsXG4gICAgaCAgOiAnYW4gaG91cicsXG4gICAgaGggOiAnJWQgaG91cnMnLFxuICAgIGQgIDogJ2EgZGF5JyxcbiAgICBkZCA6ICclZCBkYXlzJyxcbiAgICBNICA6ICdhIG1vbnRoJyxcbiAgICBNTSA6ICclZCBtb250aHMnLFxuICAgIHkgIDogJ2EgeWVhcicsXG4gICAgeXkgOiAnJWQgeWVhcnMnXG59O1xuXG5mdW5jdGlvbiByZWxhdGl2ZVRpbWUgKG51bWJlciwgd2l0aG91dFN1ZmZpeCwgc3RyaW5nLCBpc0Z1dHVyZSkge1xuICAgIHZhciBvdXRwdXQgPSB0aGlzLl9yZWxhdGl2ZVRpbWVbc3RyaW5nXTtcbiAgICByZXR1cm4gKGlzRnVuY3Rpb24ob3V0cHV0KSkgP1xuICAgICAgICBvdXRwdXQobnVtYmVyLCB3aXRob3V0U3VmZml4LCBzdHJpbmcsIGlzRnV0dXJlKSA6XG4gICAgICAgIG91dHB1dC5yZXBsYWNlKC8lZC9pLCBudW1iZXIpO1xufVxuXG5mdW5jdGlvbiBwYXN0RnV0dXJlIChkaWZmLCBvdXRwdXQpIHtcbiAgICB2YXIgZm9ybWF0ID0gdGhpcy5fcmVsYXRpdmVUaW1lW2RpZmYgPiAwID8gJ2Z1dHVyZScgOiAncGFzdCddO1xuICAgIHJldHVybiBpc0Z1bmN0aW9uKGZvcm1hdCkgPyBmb3JtYXQob3V0cHV0KSA6IGZvcm1hdC5yZXBsYWNlKC8lcy9pLCBvdXRwdXQpO1xufVxuXG52YXIgYWxpYXNlcyA9IHt9O1xuXG5mdW5jdGlvbiBhZGRVbml0QWxpYXMgKHVuaXQsIHNob3J0aGFuZCkge1xuICAgIHZhciBsb3dlckNhc2UgPSB1bml0LnRvTG93ZXJDYXNlKCk7XG4gICAgYWxpYXNlc1tsb3dlckNhc2VdID0gYWxpYXNlc1tsb3dlckNhc2UgKyAncyddID0gYWxpYXNlc1tzaG9ydGhhbmRdID0gdW5pdDtcbn1cblxuZnVuY3Rpb24gbm9ybWFsaXplVW5pdHModW5pdHMpIHtcbiAgICByZXR1cm4gdHlwZW9mIHVuaXRzID09PSAnc3RyaW5nJyA/IGFsaWFzZXNbdW5pdHNdIHx8IGFsaWFzZXNbdW5pdHMudG9Mb3dlckNhc2UoKV0gOiB1bmRlZmluZWQ7XG59XG5cbmZ1bmN0aW9uIG5vcm1hbGl6ZU9iamVjdFVuaXRzKGlucHV0T2JqZWN0KSB7XG4gICAgdmFyIG5vcm1hbGl6ZWRJbnB1dCA9IHt9LFxuICAgICAgICBub3JtYWxpemVkUHJvcCxcbiAgICAgICAgcHJvcDtcblxuICAgIGZvciAocHJvcCBpbiBpbnB1dE9iamVjdCkge1xuICAgICAgICBpZiAoaGFzT3duUHJvcChpbnB1dE9iamVjdCwgcHJvcCkpIHtcbiAgICAgICAgICAgIG5vcm1hbGl6ZWRQcm9wID0gbm9ybWFsaXplVW5pdHMocHJvcCk7XG4gICAgICAgICAgICBpZiAobm9ybWFsaXplZFByb3ApIHtcbiAgICAgICAgICAgICAgICBub3JtYWxpemVkSW5wdXRbbm9ybWFsaXplZFByb3BdID0gaW5wdXRPYmplY3RbcHJvcF07XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICByZXR1cm4gbm9ybWFsaXplZElucHV0O1xufVxuXG52YXIgcHJpb3JpdGllcyA9IHt9O1xuXG5mdW5jdGlvbiBhZGRVbml0UHJpb3JpdHkodW5pdCwgcHJpb3JpdHkpIHtcbiAgICBwcmlvcml0aWVzW3VuaXRdID0gcHJpb3JpdHk7XG59XG5cbmZ1bmN0aW9uIGdldFByaW9yaXRpemVkVW5pdHModW5pdHNPYmopIHtcbiAgICB2YXIgdW5pdHMgPSBbXTtcbiAgICBmb3IgKHZhciB1IGluIHVuaXRzT2JqKSB7XG4gICAgICAgIHVuaXRzLnB1c2goe3VuaXQ6IHUsIHByaW9yaXR5OiBwcmlvcml0aWVzW3VdfSk7XG4gICAgfVxuICAgIHVuaXRzLnNvcnQoZnVuY3Rpb24gKGEsIGIpIHtcbiAgICAgICAgcmV0dXJuIGEucHJpb3JpdHkgLSBiLnByaW9yaXR5O1xuICAgIH0pO1xuICAgIHJldHVybiB1bml0cztcbn1cblxuZnVuY3Rpb24gemVyb0ZpbGwobnVtYmVyLCB0YXJnZXRMZW5ndGgsIGZvcmNlU2lnbikge1xuICAgIHZhciBhYnNOdW1iZXIgPSAnJyArIE1hdGguYWJzKG51bWJlciksXG4gICAgICAgIHplcm9zVG9GaWxsID0gdGFyZ2V0TGVuZ3RoIC0gYWJzTnVtYmVyLmxlbmd0aCxcbiAgICAgICAgc2lnbiA9IG51bWJlciA+PSAwO1xuICAgIHJldHVybiAoc2lnbiA/IChmb3JjZVNpZ24gPyAnKycgOiAnJykgOiAnLScpICtcbiAgICAgICAgTWF0aC5wb3coMTAsIE1hdGgubWF4KDAsIHplcm9zVG9GaWxsKSkudG9TdHJpbmcoKS5zdWJzdHIoMSkgKyBhYnNOdW1iZXI7XG59XG5cbnZhciBmb3JtYXR0aW5nVG9rZW5zID0gLyhcXFtbXlxcW10qXFxdKXwoXFxcXCk/KFtIaF1tbShzcyk/fE1vfE1NP00/TT98RG98REREb3xERD9EP0Q/fGRkZD9kP3xkbz98d1tvfHddP3xXW298V10/fFFvP3xZWVlZWVl8WVlZWVl8WVlZWXxZWXxnZyhnZ2c/KT98R0coR0dHPyk/fGV8RXxhfEF8aGg/fEhIP3xraz98bW0/fHNzP3xTezEsOX18eHxYfHp6P3xaWj98LikvZztcblxudmFyIGxvY2FsRm9ybWF0dGluZ1Rva2VucyA9IC8oXFxbW15cXFtdKlxcXSl8KFxcXFwpPyhMVFN8TFR8TEw/TD9MP3xsezEsNH0pL2c7XG5cbnZhciBmb3JtYXRGdW5jdGlvbnMgPSB7fTtcblxudmFyIGZvcm1hdFRva2VuRnVuY3Rpb25zID0ge307XG5cbi8vIHRva2VuOiAgICAnTSdcbi8vIHBhZGRlZDogICBbJ01NJywgMl1cbi8vIG9yZGluYWw6ICAnTW8nXG4vLyBjYWxsYmFjazogZnVuY3Rpb24gKCkgeyB0aGlzLm1vbnRoKCkgKyAxIH1cbmZ1bmN0aW9uIGFkZEZvcm1hdFRva2VuICh0b2tlbiwgcGFkZGVkLCBvcmRpbmFsLCBjYWxsYmFjaykge1xuICAgIHZhciBmdW5jID0gY2FsbGJhY2s7XG4gICAgaWYgKHR5cGVvZiBjYWxsYmFjayA9PT0gJ3N0cmluZycpIHtcbiAgICAgICAgZnVuYyA9IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgIHJldHVybiB0aGlzW2NhbGxiYWNrXSgpO1xuICAgICAgICB9O1xuICAgIH1cbiAgICBpZiAodG9rZW4pIHtcbiAgICAgICAgZm9ybWF0VG9rZW5GdW5jdGlvbnNbdG9rZW5dID0gZnVuYztcbiAgICB9XG4gICAgaWYgKHBhZGRlZCkge1xuICAgICAgICBmb3JtYXRUb2tlbkZ1bmN0aW9uc1twYWRkZWRbMF1dID0gZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgcmV0dXJuIHplcm9GaWxsKGZ1bmMuYXBwbHkodGhpcywgYXJndW1lbnRzKSwgcGFkZGVkWzFdLCBwYWRkZWRbMl0pO1xuICAgICAgICB9O1xuICAgIH1cbiAgICBpZiAob3JkaW5hbCkge1xuICAgICAgICBmb3JtYXRUb2tlbkZ1bmN0aW9uc1tvcmRpbmFsXSA9IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgIHJldHVybiB0aGlzLmxvY2FsZURhdGEoKS5vcmRpbmFsKGZ1bmMuYXBwbHkodGhpcywgYXJndW1lbnRzKSwgdG9rZW4pO1xuICAgICAgICB9O1xuICAgIH1cbn1cblxuZnVuY3Rpb24gcmVtb3ZlRm9ybWF0dGluZ1Rva2VucyhpbnB1dCkge1xuICAgIGlmIChpbnB1dC5tYXRjaCgvXFxbW1xcc1xcU10vKSkge1xuICAgICAgICByZXR1cm4gaW5wdXQucmVwbGFjZSgvXlxcW3xcXF0kL2csICcnKTtcbiAgICB9XG4gICAgcmV0dXJuIGlucHV0LnJlcGxhY2UoL1xcXFwvZywgJycpO1xufVxuXG5mdW5jdGlvbiBtYWtlRm9ybWF0RnVuY3Rpb24oZm9ybWF0KSB7XG4gICAgdmFyIGFycmF5ID0gZm9ybWF0Lm1hdGNoKGZvcm1hdHRpbmdUb2tlbnMpLCBpLCBsZW5ndGg7XG5cbiAgICBmb3IgKGkgPSAwLCBsZW5ndGggPSBhcnJheS5sZW5ndGg7IGkgPCBsZW5ndGg7IGkrKykge1xuICAgICAgICBpZiAoZm9ybWF0VG9rZW5GdW5jdGlvbnNbYXJyYXlbaV1dKSB7XG4gICAgICAgICAgICBhcnJheVtpXSA9IGZvcm1hdFRva2VuRnVuY3Rpb25zW2FycmF5W2ldXTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIGFycmF5W2ldID0gcmVtb3ZlRm9ybWF0dGluZ1Rva2VucyhhcnJheVtpXSk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICByZXR1cm4gZnVuY3Rpb24gKG1vbSkge1xuICAgICAgICB2YXIgb3V0cHV0ID0gJycsIGk7XG4gICAgICAgIGZvciAoaSA9IDA7IGkgPCBsZW5ndGg7IGkrKykge1xuICAgICAgICAgICAgb3V0cHV0ICs9IGlzRnVuY3Rpb24oYXJyYXlbaV0pID8gYXJyYXlbaV0uY2FsbChtb20sIGZvcm1hdCkgOiBhcnJheVtpXTtcbiAgICAgICAgfVxuICAgICAgICByZXR1cm4gb3V0cHV0O1xuICAgIH07XG59XG5cbi8vIGZvcm1hdCBkYXRlIHVzaW5nIG5hdGl2ZSBkYXRlIG9iamVjdFxuZnVuY3Rpb24gZm9ybWF0TW9tZW50KG0sIGZvcm1hdCkge1xuICAgIGlmICghbS5pc1ZhbGlkKCkpIHtcbiAgICAgICAgcmV0dXJuIG0ubG9jYWxlRGF0YSgpLmludmFsaWREYXRlKCk7XG4gICAgfVxuXG4gICAgZm9ybWF0ID0gZXhwYW5kRm9ybWF0KGZvcm1hdCwgbS5sb2NhbGVEYXRhKCkpO1xuICAgIGZvcm1hdEZ1bmN0aW9uc1tmb3JtYXRdID0gZm9ybWF0RnVuY3Rpb25zW2Zvcm1hdF0gfHwgbWFrZUZvcm1hdEZ1bmN0aW9uKGZvcm1hdCk7XG5cbiAgICByZXR1cm4gZm9ybWF0RnVuY3Rpb25zW2Zvcm1hdF0obSk7XG59XG5cbmZ1bmN0aW9uIGV4cGFuZEZvcm1hdChmb3JtYXQsIGxvY2FsZSkge1xuICAgIHZhciBpID0gNTtcblxuICAgIGZ1bmN0aW9uIHJlcGxhY2VMb25nRGF0ZUZvcm1hdFRva2VucyhpbnB1dCkge1xuICAgICAgICByZXR1cm4gbG9jYWxlLmxvbmdEYXRlRm9ybWF0KGlucHV0KSB8fCBpbnB1dDtcbiAgICB9XG5cbiAgICBsb2NhbEZvcm1hdHRpbmdUb2tlbnMubGFzdEluZGV4ID0gMDtcbiAgICB3aGlsZSAoaSA+PSAwICYmIGxvY2FsRm9ybWF0dGluZ1Rva2Vucy50ZXN0KGZvcm1hdCkpIHtcbiAgICAgICAgZm9ybWF0ID0gZm9ybWF0LnJlcGxhY2UobG9jYWxGb3JtYXR0aW5nVG9rZW5zLCByZXBsYWNlTG9uZ0RhdGVGb3JtYXRUb2tlbnMpO1xuICAgICAgICBsb2NhbEZvcm1hdHRpbmdUb2tlbnMubGFzdEluZGV4ID0gMDtcbiAgICAgICAgaSAtPSAxO1xuICAgIH1cblxuICAgIHJldHVybiBmb3JtYXQ7XG59XG5cbnZhciBtYXRjaDEgICAgICAgICA9IC9cXGQvOyAgICAgICAgICAgIC8vICAgICAgIDAgLSA5XG52YXIgbWF0Y2gyICAgICAgICAgPSAvXFxkXFxkLzsgICAgICAgICAgLy8gICAgICAwMCAtIDk5XG52YXIgbWF0Y2gzICAgICAgICAgPSAvXFxkezN9LzsgICAgICAgICAvLyAgICAgMDAwIC0gOTk5XG52YXIgbWF0Y2g0ICAgICAgICAgPSAvXFxkezR9LzsgICAgICAgICAvLyAgICAwMDAwIC0gOTk5OVxudmFyIG1hdGNoNiAgICAgICAgID0gL1srLV0/XFxkezZ9LzsgICAgLy8gLTk5OTk5OSAtIDk5OTk5OVxudmFyIG1hdGNoMXRvMiAgICAgID0gL1xcZFxcZD8vOyAgICAgICAgIC8vICAgICAgIDAgLSA5OVxudmFyIG1hdGNoM3RvNCAgICAgID0gL1xcZFxcZFxcZFxcZD8vOyAgICAgLy8gICAgIDk5OSAtIDk5OTlcbnZhciBtYXRjaDV0bzYgICAgICA9IC9cXGRcXGRcXGRcXGRcXGRcXGQ/LzsgLy8gICA5OTk5OSAtIDk5OTk5OVxudmFyIG1hdGNoMXRvMyAgICAgID0gL1xcZHsxLDN9LzsgICAgICAgLy8gICAgICAgMCAtIDk5OVxudmFyIG1hdGNoMXRvNCAgICAgID0gL1xcZHsxLDR9LzsgICAgICAgLy8gICAgICAgMCAtIDk5OTlcbnZhciBtYXRjaDF0bzYgICAgICA9IC9bKy1dP1xcZHsxLDZ9LzsgIC8vIC05OTk5OTkgLSA5OTk5OTlcblxudmFyIG1hdGNoVW5zaWduZWQgID0gL1xcZCsvOyAgICAgICAgICAgLy8gICAgICAgMCAtIGluZlxudmFyIG1hdGNoU2lnbmVkICAgID0gL1srLV0/XFxkKy87ICAgICAgLy8gICAgLWluZiAtIGluZlxuXG52YXIgbWF0Y2hPZmZzZXQgICAgPSAvWnxbKy1dXFxkXFxkOj9cXGRcXGQvZ2k7IC8vICswMDowMCAtMDA6MDAgKzAwMDAgLTAwMDAgb3IgWlxudmFyIG1hdGNoU2hvcnRPZmZzZXQgPSAvWnxbKy1dXFxkXFxkKD86Oj9cXGRcXGQpPy9naTsgLy8gKzAwIC0wMCArMDA6MDAgLTAwOjAwICswMDAwIC0wMDAwIG9yIFpcblxudmFyIG1hdGNoVGltZXN0YW1wID0gL1srLV0/XFxkKyhcXC5cXGR7MSwzfSk/LzsgLy8gMTIzNDU2Nzg5IDEyMzQ1Njc4OS4xMjNcblxuLy8gYW55IHdvcmQgKG9yIHR3bykgY2hhcmFjdGVycyBvciBudW1iZXJzIGluY2x1ZGluZyB0d28vdGhyZWUgd29yZCBtb250aCBpbiBhcmFiaWMuXG4vLyBpbmNsdWRlcyBzY290dGlzaCBnYWVsaWMgdHdvIHdvcmQgYW5kIGh5cGhlbmF0ZWQgbW9udGhzXG52YXIgbWF0Y2hXb3JkID0gL1swLTldezAsMjU2fVsnYS16XFx1MDBBMC1cXHUwNUZGXFx1MDcwMC1cXHVEN0ZGXFx1RjkwMC1cXHVGRENGXFx1RkRGMC1cXHVGRkVGXXsxLDI1Nn18W1xcdTA2MDAtXFx1MDZGRlxcL117MSwyNTZ9KFxccyo/W1xcdTA2MDAtXFx1MDZGRl17MSwyNTZ9KXsxLDJ9L2k7XG5cblxudmFyIHJlZ2V4ZXMgPSB7fTtcblxuZnVuY3Rpb24gYWRkUmVnZXhUb2tlbiAodG9rZW4sIHJlZ2V4LCBzdHJpY3RSZWdleCkge1xuICAgIHJlZ2V4ZXNbdG9rZW5dID0gaXNGdW5jdGlvbihyZWdleCkgPyByZWdleCA6IGZ1bmN0aW9uIChpc1N0cmljdCwgbG9jYWxlRGF0YSkge1xuICAgICAgICByZXR1cm4gKGlzU3RyaWN0ICYmIHN0cmljdFJlZ2V4KSA/IHN0cmljdFJlZ2V4IDogcmVnZXg7XG4gICAgfTtcbn1cblxuZnVuY3Rpb24gZ2V0UGFyc2VSZWdleEZvclRva2VuICh0b2tlbiwgY29uZmlnKSB7XG4gICAgaWYgKCFoYXNPd25Qcm9wKHJlZ2V4ZXMsIHRva2VuKSkge1xuICAgICAgICByZXR1cm4gbmV3IFJlZ0V4cCh1bmVzY2FwZUZvcm1hdCh0b2tlbikpO1xuICAgIH1cblxuICAgIHJldHVybiByZWdleGVzW3Rva2VuXShjb25maWcuX3N0cmljdCwgY29uZmlnLl9sb2NhbGUpO1xufVxuXG4vLyBDb2RlIGZyb20gaHR0cDovL3N0YWNrb3ZlcmZsb3cuY29tL3F1ZXN0aW9ucy8zNTYxNDkzL2lzLXRoZXJlLWEtcmVnZXhwLWVzY2FwZS1mdW5jdGlvbi1pbi1qYXZhc2NyaXB0XG5mdW5jdGlvbiB1bmVzY2FwZUZvcm1hdChzKSB7XG4gICAgcmV0dXJuIHJlZ2V4RXNjYXBlKHMucmVwbGFjZSgnXFxcXCcsICcnKS5yZXBsYWNlKC9cXFxcKFxcWyl8XFxcXChcXF0pfFxcWyhbXlxcXVxcW10qKVxcXXxcXFxcKC4pL2csIGZ1bmN0aW9uIChtYXRjaGVkLCBwMSwgcDIsIHAzLCBwNCkge1xuICAgICAgICByZXR1cm4gcDEgfHwgcDIgfHwgcDMgfHwgcDQ7XG4gICAgfSkpO1xufVxuXG5mdW5jdGlvbiByZWdleEVzY2FwZShzKSB7XG4gICAgcmV0dXJuIHMucmVwbGFjZSgvWy1cXC9cXFxcXiQqKz8uKCl8W1xcXXt9XS9nLCAnXFxcXCQmJyk7XG59XG5cbnZhciB0b2tlbnMgPSB7fTtcblxuZnVuY3Rpb24gYWRkUGFyc2VUb2tlbiAodG9rZW4sIGNhbGxiYWNrKSB7XG4gICAgdmFyIGksIGZ1bmMgPSBjYWxsYmFjaztcbiAgICBpZiAodHlwZW9mIHRva2VuID09PSAnc3RyaW5nJykge1xuICAgICAgICB0b2tlbiA9IFt0b2tlbl07XG4gICAgfVxuICAgIGlmIChpc051bWJlcihjYWxsYmFjaykpIHtcbiAgICAgICAgZnVuYyA9IGZ1bmN0aW9uIChpbnB1dCwgYXJyYXkpIHtcbiAgICAgICAgICAgIGFycmF5W2NhbGxiYWNrXSA9IHRvSW50KGlucHV0KTtcbiAgICAgICAgfTtcbiAgICB9XG4gICAgZm9yIChpID0gMDsgaSA8IHRva2VuLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgIHRva2Vuc1t0b2tlbltpXV0gPSBmdW5jO1xuICAgIH1cbn1cblxuZnVuY3Rpb24gYWRkV2Vla1BhcnNlVG9rZW4gKHRva2VuLCBjYWxsYmFjaykge1xuICAgIGFkZFBhcnNlVG9rZW4odG9rZW4sIGZ1bmN0aW9uIChpbnB1dCwgYXJyYXksIGNvbmZpZywgdG9rZW4pIHtcbiAgICAgICAgY29uZmlnLl93ID0gY29uZmlnLl93IHx8IHt9O1xuICAgICAgICBjYWxsYmFjayhpbnB1dCwgY29uZmlnLl93LCBjb25maWcsIHRva2VuKTtcbiAgICB9KTtcbn1cblxuZnVuY3Rpb24gYWRkVGltZVRvQXJyYXlGcm9tVG9rZW4odG9rZW4sIGlucHV0LCBjb25maWcpIHtcbiAgICBpZiAoaW5wdXQgIT0gbnVsbCAmJiBoYXNPd25Qcm9wKHRva2VucywgdG9rZW4pKSB7XG4gICAgICAgIHRva2Vuc1t0b2tlbl0oaW5wdXQsIGNvbmZpZy5fYSwgY29uZmlnLCB0b2tlbik7XG4gICAgfVxufVxuXG52YXIgWUVBUiA9IDA7XG52YXIgTU9OVEggPSAxO1xudmFyIERBVEUgPSAyO1xudmFyIEhPVVIgPSAzO1xudmFyIE1JTlVURSA9IDQ7XG52YXIgU0VDT05EID0gNTtcbnZhciBNSUxMSVNFQ09ORCA9IDY7XG52YXIgV0VFSyA9IDc7XG52YXIgV0VFS0RBWSA9IDg7XG5cbi8vIEZPUk1BVFRJTkdcblxuYWRkRm9ybWF0VG9rZW4oJ1knLCAwLCAwLCBmdW5jdGlvbiAoKSB7XG4gICAgdmFyIHkgPSB0aGlzLnllYXIoKTtcbiAgICByZXR1cm4geSA8PSA5OTk5ID8gJycgKyB5IDogJysnICsgeTtcbn0pO1xuXG5hZGRGb3JtYXRUb2tlbigwLCBbJ1lZJywgMl0sIDAsIGZ1bmN0aW9uICgpIHtcbiAgICByZXR1cm4gdGhpcy55ZWFyKCkgJSAxMDA7XG59KTtcblxuYWRkRm9ybWF0VG9rZW4oMCwgWydZWVlZJywgICA0XSwgICAgICAgMCwgJ3llYXInKTtcbmFkZEZvcm1hdFRva2VuKDAsIFsnWVlZWVknLCAgNV0sICAgICAgIDAsICd5ZWFyJyk7XG5hZGRGb3JtYXRUb2tlbigwLCBbJ1lZWVlZWScsIDYsIHRydWVdLCAwLCAneWVhcicpO1xuXG4vLyBBTElBU0VTXG5cbmFkZFVuaXRBbGlhcygneWVhcicsICd5Jyk7XG5cbi8vIFBSSU9SSVRJRVNcblxuYWRkVW5pdFByaW9yaXR5KCd5ZWFyJywgMSk7XG5cbi8vIFBBUlNJTkdcblxuYWRkUmVnZXhUb2tlbignWScsICAgICAgbWF0Y2hTaWduZWQpO1xuYWRkUmVnZXhUb2tlbignWVknLCAgICAgbWF0Y2gxdG8yLCBtYXRjaDIpO1xuYWRkUmVnZXhUb2tlbignWVlZWScsICAgbWF0Y2gxdG80LCBtYXRjaDQpO1xuYWRkUmVnZXhUb2tlbignWVlZWVknLCAgbWF0Y2gxdG82LCBtYXRjaDYpO1xuYWRkUmVnZXhUb2tlbignWVlZWVlZJywgbWF0Y2gxdG82LCBtYXRjaDYpO1xuXG5hZGRQYXJzZVRva2VuKFsnWVlZWVknLCAnWVlZWVlZJ10sIFlFQVIpO1xuYWRkUGFyc2VUb2tlbignWVlZWScsIGZ1bmN0aW9uIChpbnB1dCwgYXJyYXkpIHtcbiAgICBhcnJheVtZRUFSXSA9IGlucHV0Lmxlbmd0aCA9PT0gMiA/IGhvb2tzLnBhcnNlVHdvRGlnaXRZZWFyKGlucHV0KSA6IHRvSW50KGlucHV0KTtcbn0pO1xuYWRkUGFyc2VUb2tlbignWVknLCBmdW5jdGlvbiAoaW5wdXQsIGFycmF5KSB7XG4gICAgYXJyYXlbWUVBUl0gPSBob29rcy5wYXJzZVR3b0RpZ2l0WWVhcihpbnB1dCk7XG59KTtcbmFkZFBhcnNlVG9rZW4oJ1knLCBmdW5jdGlvbiAoaW5wdXQsIGFycmF5KSB7XG4gICAgYXJyYXlbWUVBUl0gPSBwYXJzZUludChpbnB1dCwgMTApO1xufSk7XG5cbi8vIEhFTFBFUlNcblxuZnVuY3Rpb24gZGF5c0luWWVhcih5ZWFyKSB7XG4gICAgcmV0dXJuIGlzTGVhcFllYXIoeWVhcikgPyAzNjYgOiAzNjU7XG59XG5cbmZ1bmN0aW9uIGlzTGVhcFllYXIoeWVhcikge1xuICAgIHJldHVybiAoeWVhciAlIDQgPT09IDAgJiYgeWVhciAlIDEwMCAhPT0gMCkgfHwgeWVhciAlIDQwMCA9PT0gMDtcbn1cblxuLy8gSE9PS1NcblxuaG9va3MucGFyc2VUd29EaWdpdFllYXIgPSBmdW5jdGlvbiAoaW5wdXQpIHtcbiAgICByZXR1cm4gdG9JbnQoaW5wdXQpICsgKHRvSW50KGlucHV0KSA+IDY4ID8gMTkwMCA6IDIwMDApO1xufTtcblxuLy8gTU9NRU5UU1xuXG52YXIgZ2V0U2V0WWVhciA9IG1ha2VHZXRTZXQoJ0Z1bGxZZWFyJywgdHJ1ZSk7XG5cbmZ1bmN0aW9uIGdldElzTGVhcFllYXIgKCkge1xuICAgIHJldHVybiBpc0xlYXBZZWFyKHRoaXMueWVhcigpKTtcbn1cblxuZnVuY3Rpb24gbWFrZUdldFNldCAodW5pdCwga2VlcFRpbWUpIHtcbiAgICByZXR1cm4gZnVuY3Rpb24gKHZhbHVlKSB7XG4gICAgICAgIGlmICh2YWx1ZSAhPSBudWxsKSB7XG4gICAgICAgICAgICBzZXQkMSh0aGlzLCB1bml0LCB2YWx1ZSk7XG4gICAgICAgICAgICBob29rcy51cGRhdGVPZmZzZXQodGhpcywga2VlcFRpbWUpO1xuICAgICAgICAgICAgcmV0dXJuIHRoaXM7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICByZXR1cm4gZ2V0KHRoaXMsIHVuaXQpO1xuICAgICAgICB9XG4gICAgfTtcbn1cblxuZnVuY3Rpb24gZ2V0IChtb20sIHVuaXQpIHtcbiAgICByZXR1cm4gbW9tLmlzVmFsaWQoKSA/XG4gICAgICAgIG1vbS5fZFsnZ2V0JyArIChtb20uX2lzVVRDID8gJ1VUQycgOiAnJykgKyB1bml0XSgpIDogTmFOO1xufVxuXG5mdW5jdGlvbiBzZXQkMSAobW9tLCB1bml0LCB2YWx1ZSkge1xuICAgIGlmIChtb20uaXNWYWxpZCgpICYmICFpc05hTih2YWx1ZSkpIHtcbiAgICAgICAgaWYgKHVuaXQgPT09ICdGdWxsWWVhcicgJiYgaXNMZWFwWWVhcihtb20ueWVhcigpKSAmJiBtb20ubW9udGgoKSA9PT0gMSAmJiBtb20uZGF0ZSgpID09PSAyOSkge1xuICAgICAgICAgICAgbW9tLl9kWydzZXQnICsgKG1vbS5faXNVVEMgPyAnVVRDJyA6ICcnKSArIHVuaXRdKHZhbHVlLCBtb20ubW9udGgoKSwgZGF5c0luTW9udGgodmFsdWUsIG1vbS5tb250aCgpKSk7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgICBtb20uX2RbJ3NldCcgKyAobW9tLl9pc1VUQyA/ICdVVEMnIDogJycpICsgdW5pdF0odmFsdWUpO1xuICAgICAgICB9XG4gICAgfVxufVxuXG4vLyBNT01FTlRTXG5cbmZ1bmN0aW9uIHN0cmluZ0dldCAodW5pdHMpIHtcbiAgICB1bml0cyA9IG5vcm1hbGl6ZVVuaXRzKHVuaXRzKTtcbiAgICBpZiAoaXNGdW5jdGlvbih0aGlzW3VuaXRzXSkpIHtcbiAgICAgICAgcmV0dXJuIHRoaXNbdW5pdHNdKCk7XG4gICAgfVxuICAgIHJldHVybiB0aGlzO1xufVxuXG5cbmZ1bmN0aW9uIHN0cmluZ1NldCAodW5pdHMsIHZhbHVlKSB7XG4gICAgaWYgKHR5cGVvZiB1bml0cyA9PT0gJ29iamVjdCcpIHtcbiAgICAgICAgdW5pdHMgPSBub3JtYWxpemVPYmplY3RVbml0cyh1bml0cyk7XG4gICAgICAgIHZhciBwcmlvcml0aXplZCA9IGdldFByaW9yaXRpemVkVW5pdHModW5pdHMpO1xuICAgICAgICBmb3IgKHZhciBpID0gMDsgaSA8IHByaW9yaXRpemVkLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgICAgICB0aGlzW3ByaW9yaXRpemVkW2ldLnVuaXRdKHVuaXRzW3ByaW9yaXRpemVkW2ldLnVuaXRdKTtcbiAgICAgICAgfVxuICAgIH0gZWxzZSB7XG4gICAgICAgIHVuaXRzID0gbm9ybWFsaXplVW5pdHModW5pdHMpO1xuICAgICAgICBpZiAoaXNGdW5jdGlvbih0aGlzW3VuaXRzXSkpIHtcbiAgICAgICAgICAgIHJldHVybiB0aGlzW3VuaXRzXSh2YWx1ZSk7XG4gICAgICAgIH1cbiAgICB9XG4gICAgcmV0dXJuIHRoaXM7XG59XG5cbmZ1bmN0aW9uIG1vZChuLCB4KSB7XG4gICAgcmV0dXJuICgobiAlIHgpICsgeCkgJSB4O1xufVxuXG52YXIgaW5kZXhPZjtcblxuaWYgKEFycmF5LnByb3RvdHlwZS5pbmRleE9mKSB7XG4gICAgaW5kZXhPZiA9IEFycmF5LnByb3RvdHlwZS5pbmRleE9mO1xufSBlbHNlIHtcbiAgICBpbmRleE9mID0gZnVuY3Rpb24gKG8pIHtcbiAgICAgICAgLy8gSSBrbm93XG4gICAgICAgIHZhciBpO1xuICAgICAgICBmb3IgKGkgPSAwOyBpIDwgdGhpcy5sZW5ndGg7ICsraSkge1xuICAgICAgICAgICAgaWYgKHRoaXNbaV0gPT09IG8pIHtcbiAgICAgICAgICAgICAgICByZXR1cm4gaTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgICByZXR1cm4gLTE7XG4gICAgfTtcbn1cblxuZnVuY3Rpb24gZGF5c0luTW9udGgoeWVhciwgbW9udGgpIHtcbiAgICBpZiAoaXNOYU4oeWVhcikgfHwgaXNOYU4obW9udGgpKSB7XG4gICAgICAgIHJldHVybiBOYU47XG4gICAgfVxuICAgIHZhciBtb2RNb250aCA9IG1vZChtb250aCwgMTIpO1xuICAgIHllYXIgKz0gKG1vbnRoIC0gbW9kTW9udGgpIC8gMTI7XG4gICAgcmV0dXJuIG1vZE1vbnRoID09PSAxID8gKGlzTGVhcFllYXIoeWVhcikgPyAyOSA6IDI4KSA6ICgzMSAtIG1vZE1vbnRoICUgNyAlIDIpO1xufVxuXG4vLyBGT1JNQVRUSU5HXG5cbmFkZEZvcm1hdFRva2VuKCdNJywgWydNTScsIDJdLCAnTW8nLCBmdW5jdGlvbiAoKSB7XG4gICAgcmV0dXJuIHRoaXMubW9udGgoKSArIDE7XG59KTtcblxuYWRkRm9ybWF0VG9rZW4oJ01NTScsIDAsIDAsIGZ1bmN0aW9uIChmb3JtYXQpIHtcbiAgICByZXR1cm4gdGhpcy5sb2NhbGVEYXRhKCkubW9udGhzU2hvcnQodGhpcywgZm9ybWF0KTtcbn0pO1xuXG5hZGRGb3JtYXRUb2tlbignTU1NTScsIDAsIDAsIGZ1bmN0aW9uIChmb3JtYXQpIHtcbiAgICByZXR1cm4gdGhpcy5sb2NhbGVEYXRhKCkubW9udGhzKHRoaXMsIGZvcm1hdCk7XG59KTtcblxuLy8gQUxJQVNFU1xuXG5hZGRVbml0QWxpYXMoJ21vbnRoJywgJ00nKTtcblxuLy8gUFJJT1JJVFlcblxuYWRkVW5pdFByaW9yaXR5KCdtb250aCcsIDgpO1xuXG4vLyBQQVJTSU5HXG5cbmFkZFJlZ2V4VG9rZW4oJ00nLCAgICBtYXRjaDF0bzIpO1xuYWRkUmVnZXhUb2tlbignTU0nLCAgIG1hdGNoMXRvMiwgbWF0Y2gyKTtcbmFkZFJlZ2V4VG9rZW4oJ01NTScsICBmdW5jdGlvbiAoaXNTdHJpY3QsIGxvY2FsZSkge1xuICAgIHJldHVybiBsb2NhbGUubW9udGhzU2hvcnRSZWdleChpc1N0cmljdCk7XG59KTtcbmFkZFJlZ2V4VG9rZW4oJ01NTU0nLCBmdW5jdGlvbiAoaXNTdHJpY3QsIGxvY2FsZSkge1xuICAgIHJldHVybiBsb2NhbGUubW9udGhzUmVnZXgoaXNTdHJpY3QpO1xufSk7XG5cbmFkZFBhcnNlVG9rZW4oWydNJywgJ01NJ10sIGZ1bmN0aW9uIChpbnB1dCwgYXJyYXkpIHtcbiAgICBhcnJheVtNT05USF0gPSB0b0ludChpbnB1dCkgLSAxO1xufSk7XG5cbmFkZFBhcnNlVG9rZW4oWydNTU0nLCAnTU1NTSddLCBmdW5jdGlvbiAoaW5wdXQsIGFycmF5LCBjb25maWcsIHRva2VuKSB7XG4gICAgdmFyIG1vbnRoID0gY29uZmlnLl9sb2NhbGUubW9udGhzUGFyc2UoaW5wdXQsIHRva2VuLCBjb25maWcuX3N0cmljdCk7XG4gICAgLy8gaWYgd2UgZGlkbid0IGZpbmQgYSBtb250aCBuYW1lLCBtYXJrIHRoZSBkYXRlIGFzIGludmFsaWQuXG4gICAgaWYgKG1vbnRoICE9IG51bGwpIHtcbiAgICAgICAgYXJyYXlbTU9OVEhdID0gbW9udGg7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgZ2V0UGFyc2luZ0ZsYWdzKGNvbmZpZykuaW52YWxpZE1vbnRoID0gaW5wdXQ7XG4gICAgfVxufSk7XG5cbi8vIExPQ0FMRVNcblxudmFyIE1PTlRIU19JTl9GT1JNQVQgPSAvRFtvRF0/KFxcW1teXFxbXFxdXSpcXF18XFxzKStNTU1NPy87XG52YXIgZGVmYXVsdExvY2FsZU1vbnRocyA9ICdKYW51YXJ5X0ZlYnJ1YXJ5X01hcmNoX0FwcmlsX01heV9KdW5lX0p1bHlfQXVndXN0X1NlcHRlbWJlcl9PY3RvYmVyX05vdmVtYmVyX0RlY2VtYmVyJy5zcGxpdCgnXycpO1xuZnVuY3Rpb24gbG9jYWxlTW9udGhzIChtLCBmb3JtYXQpIHtcbiAgICBpZiAoIW0pIHtcbiAgICAgICAgcmV0dXJuIGlzQXJyYXkodGhpcy5fbW9udGhzKSA/IHRoaXMuX21vbnRocyA6XG4gICAgICAgICAgICB0aGlzLl9tb250aHNbJ3N0YW5kYWxvbmUnXTtcbiAgICB9XG4gICAgcmV0dXJuIGlzQXJyYXkodGhpcy5fbW9udGhzKSA/IHRoaXMuX21vbnRoc1ttLm1vbnRoKCldIDpcbiAgICAgICAgdGhpcy5fbW9udGhzWyh0aGlzLl9tb250aHMuaXNGb3JtYXQgfHwgTU9OVEhTX0lOX0ZPUk1BVCkudGVzdChmb3JtYXQpID8gJ2Zvcm1hdCcgOiAnc3RhbmRhbG9uZSddW20ubW9udGgoKV07XG59XG5cbnZhciBkZWZhdWx0TG9jYWxlTW9udGhzU2hvcnQgPSAnSmFuX0ZlYl9NYXJfQXByX01heV9KdW5fSnVsX0F1Z19TZXBfT2N0X05vdl9EZWMnLnNwbGl0KCdfJyk7XG5mdW5jdGlvbiBsb2NhbGVNb250aHNTaG9ydCAobSwgZm9ybWF0KSB7XG4gICAgaWYgKCFtKSB7XG4gICAgICAgIHJldHVybiBpc0FycmF5KHRoaXMuX21vbnRoc1Nob3J0KSA/IHRoaXMuX21vbnRoc1Nob3J0IDpcbiAgICAgICAgICAgIHRoaXMuX21vbnRoc1Nob3J0WydzdGFuZGFsb25lJ107XG4gICAgfVxuICAgIHJldHVybiBpc0FycmF5KHRoaXMuX21vbnRoc1Nob3J0KSA/IHRoaXMuX21vbnRoc1Nob3J0W20ubW9udGgoKV0gOlxuICAgICAgICB0aGlzLl9tb250aHNTaG9ydFtNT05USFNfSU5fRk9STUFULnRlc3QoZm9ybWF0KSA/ICdmb3JtYXQnIDogJ3N0YW5kYWxvbmUnXVttLm1vbnRoKCldO1xufVxuXG5mdW5jdGlvbiBoYW5kbGVTdHJpY3RQYXJzZShtb250aE5hbWUsIGZvcm1hdCwgc3RyaWN0KSB7XG4gICAgdmFyIGksIGlpLCBtb20sIGxsYyA9IG1vbnRoTmFtZS50b0xvY2FsZUxvd2VyQ2FzZSgpO1xuICAgIGlmICghdGhpcy5fbW9udGhzUGFyc2UpIHtcbiAgICAgICAgLy8gdGhpcyBpcyBub3QgdXNlZFxuICAgICAgICB0aGlzLl9tb250aHNQYXJzZSA9IFtdO1xuICAgICAgICB0aGlzLl9sb25nTW9udGhzUGFyc2UgPSBbXTtcbiAgICAgICAgdGhpcy5fc2hvcnRNb250aHNQYXJzZSA9IFtdO1xuICAgICAgICBmb3IgKGkgPSAwOyBpIDwgMTI7ICsraSkge1xuICAgICAgICAgICAgbW9tID0gY3JlYXRlVVRDKFsyMDAwLCBpXSk7XG4gICAgICAgICAgICB0aGlzLl9zaG9ydE1vbnRoc1BhcnNlW2ldID0gdGhpcy5tb250aHNTaG9ydChtb20sICcnKS50b0xvY2FsZUxvd2VyQ2FzZSgpO1xuICAgICAgICAgICAgdGhpcy5fbG9uZ01vbnRoc1BhcnNlW2ldID0gdGhpcy5tb250aHMobW9tLCAnJykudG9Mb2NhbGVMb3dlckNhc2UoKTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIGlmIChzdHJpY3QpIHtcbiAgICAgICAgaWYgKGZvcm1hdCA9PT0gJ01NTScpIHtcbiAgICAgICAgICAgIGlpID0gaW5kZXhPZi5jYWxsKHRoaXMuX3Nob3J0TW9udGhzUGFyc2UsIGxsYyk7XG4gICAgICAgICAgICByZXR1cm4gaWkgIT09IC0xID8gaWkgOiBudWxsO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgaWkgPSBpbmRleE9mLmNhbGwodGhpcy5fbG9uZ01vbnRoc1BhcnNlLCBsbGMpO1xuICAgICAgICAgICAgcmV0dXJuIGlpICE9PSAtMSA/IGlpIDogbnVsbDtcbiAgICAgICAgfVxuICAgIH0gZWxzZSB7XG4gICAgICAgIGlmIChmb3JtYXQgPT09ICdNTU0nKSB7XG4gICAgICAgICAgICBpaSA9IGluZGV4T2YuY2FsbCh0aGlzLl9zaG9ydE1vbnRoc1BhcnNlLCBsbGMpO1xuICAgICAgICAgICAgaWYgKGlpICE9PSAtMSkge1xuICAgICAgICAgICAgICAgIHJldHVybiBpaTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGlpID0gaW5kZXhPZi5jYWxsKHRoaXMuX2xvbmdNb250aHNQYXJzZSwgbGxjKTtcbiAgICAgICAgICAgIHJldHVybiBpaSAhPT0gLTEgPyBpaSA6IG51bGw7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICBpaSA9IGluZGV4T2YuY2FsbCh0aGlzLl9sb25nTW9udGhzUGFyc2UsIGxsYyk7XG4gICAgICAgICAgICBpZiAoaWkgIT09IC0xKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuIGlpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgaWkgPSBpbmRleE9mLmNhbGwodGhpcy5fc2hvcnRNb250aHNQYXJzZSwgbGxjKTtcbiAgICAgICAgICAgIHJldHVybiBpaSAhPT0gLTEgPyBpaSA6IG51bGw7XG4gICAgICAgIH1cbiAgICB9XG59XG5cbmZ1bmN0aW9uIGxvY2FsZU1vbnRoc1BhcnNlIChtb250aE5hbWUsIGZvcm1hdCwgc3RyaWN0KSB7XG4gICAgdmFyIGksIG1vbSwgcmVnZXg7XG5cbiAgICBpZiAodGhpcy5fbW9udGhzUGFyc2VFeGFjdCkge1xuICAgICAgICByZXR1cm4gaGFuZGxlU3RyaWN0UGFyc2UuY2FsbCh0aGlzLCBtb250aE5hbWUsIGZvcm1hdCwgc3RyaWN0KTtcbiAgICB9XG5cbiAgICBpZiAoIXRoaXMuX21vbnRoc1BhcnNlKSB7XG4gICAgICAgIHRoaXMuX21vbnRoc1BhcnNlID0gW107XG4gICAgICAgIHRoaXMuX2xvbmdNb250aHNQYXJzZSA9IFtdO1xuICAgICAgICB0aGlzLl9zaG9ydE1vbnRoc1BhcnNlID0gW107XG4gICAgfVxuXG4gICAgLy8gVE9ETzogYWRkIHNvcnRpbmdcbiAgICAvLyBTb3J0aW5nIG1ha2VzIHN1cmUgaWYgb25lIG1vbnRoIChvciBhYmJyKSBpcyBhIHByZWZpeCBvZiBhbm90aGVyXG4gICAgLy8gc2VlIHNvcnRpbmcgaW4gY29tcHV0ZU1vbnRoc1BhcnNlXG4gICAgZm9yIChpID0gMDsgaSA8IDEyOyBpKyspIHtcbiAgICAgICAgLy8gbWFrZSB0aGUgcmVnZXggaWYgd2UgZG9uJ3QgaGF2ZSBpdCBhbHJlYWR5XG4gICAgICAgIG1vbSA9IGNyZWF0ZVVUQyhbMjAwMCwgaV0pO1xuICAgICAgICBpZiAoc3RyaWN0ICYmICF0aGlzLl9sb25nTW9udGhzUGFyc2VbaV0pIHtcbiAgICAgICAgICAgIHRoaXMuX2xvbmdNb250aHNQYXJzZVtpXSA9IG5ldyBSZWdFeHAoJ14nICsgdGhpcy5tb250aHMobW9tLCAnJykucmVwbGFjZSgnLicsICcnKSArICckJywgJ2knKTtcbiAgICAgICAgICAgIHRoaXMuX3Nob3J0TW9udGhzUGFyc2VbaV0gPSBuZXcgUmVnRXhwKCdeJyArIHRoaXMubW9udGhzU2hvcnQobW9tLCAnJykucmVwbGFjZSgnLicsICcnKSArICckJywgJ2knKTtcbiAgICAgICAgfVxuICAgICAgICBpZiAoIXN0cmljdCAmJiAhdGhpcy5fbW9udGhzUGFyc2VbaV0pIHtcbiAgICAgICAgICAgIHJlZ2V4ID0gJ14nICsgdGhpcy5tb250aHMobW9tLCAnJykgKyAnfF4nICsgdGhpcy5tb250aHNTaG9ydChtb20sICcnKTtcbiAgICAgICAgICAgIHRoaXMuX21vbnRoc1BhcnNlW2ldID0gbmV3IFJlZ0V4cChyZWdleC5yZXBsYWNlKCcuJywgJycpLCAnaScpO1xuICAgICAgICB9XG4gICAgICAgIC8vIHRlc3QgdGhlIHJlZ2V4XG4gICAgICAgIGlmIChzdHJpY3QgJiYgZm9ybWF0ID09PSAnTU1NTScgJiYgdGhpcy5fbG9uZ01vbnRoc1BhcnNlW2ldLnRlc3QobW9udGhOYW1lKSkge1xuICAgICAgICAgICAgcmV0dXJuIGk7XG4gICAgICAgIH0gZWxzZSBpZiAoc3RyaWN0ICYmIGZvcm1hdCA9PT0gJ01NTScgJiYgdGhpcy5fc2hvcnRNb250aHNQYXJzZVtpXS50ZXN0KG1vbnRoTmFtZSkpIHtcbiAgICAgICAgICAgIHJldHVybiBpO1xuICAgICAgICB9IGVsc2UgaWYgKCFzdHJpY3QgJiYgdGhpcy5fbW9udGhzUGFyc2VbaV0udGVzdChtb250aE5hbWUpKSB7XG4gICAgICAgICAgICByZXR1cm4gaTtcbiAgICAgICAgfVxuICAgIH1cbn1cblxuLy8gTU9NRU5UU1xuXG5mdW5jdGlvbiBzZXRNb250aCAobW9tLCB2YWx1ZSkge1xuICAgIHZhciBkYXlPZk1vbnRoO1xuXG4gICAgaWYgKCFtb20uaXNWYWxpZCgpKSB7XG4gICAgICAgIC8vIE5vIG9wXG4gICAgICAgIHJldHVybiBtb207XG4gICAgfVxuXG4gICAgaWYgKHR5cGVvZiB2YWx1ZSA9PT0gJ3N0cmluZycpIHtcbiAgICAgICAgaWYgKC9eXFxkKyQvLnRlc3QodmFsdWUpKSB7XG4gICAgICAgICAgICB2YWx1ZSA9IHRvSW50KHZhbHVlKTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIHZhbHVlID0gbW9tLmxvY2FsZURhdGEoKS5tb250aHNQYXJzZSh2YWx1ZSk7XG4gICAgICAgICAgICAvLyBUT0RPOiBBbm90aGVyIHNpbGVudCBmYWlsdXJlP1xuICAgICAgICAgICAgaWYgKCFpc051bWJlcih2YWx1ZSkpIHtcbiAgICAgICAgICAgICAgICByZXR1cm4gbW9tO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgfVxuXG4gICAgZGF5T2ZNb250aCA9IE1hdGgubWluKG1vbS5kYXRlKCksIGRheXNJbk1vbnRoKG1vbS55ZWFyKCksIHZhbHVlKSk7XG4gICAgbW9tLl9kWydzZXQnICsgKG1vbS5faXNVVEMgPyAnVVRDJyA6ICcnKSArICdNb250aCddKHZhbHVlLCBkYXlPZk1vbnRoKTtcbiAgICByZXR1cm4gbW9tO1xufVxuXG5mdW5jdGlvbiBnZXRTZXRNb250aCAodmFsdWUpIHtcbiAgICBpZiAodmFsdWUgIT0gbnVsbCkge1xuICAgICAgICBzZXRNb250aCh0aGlzLCB2YWx1ZSk7XG4gICAgICAgIGhvb2tzLnVwZGF0ZU9mZnNldCh0aGlzLCB0cnVlKTtcbiAgICAgICAgcmV0dXJuIHRoaXM7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgcmV0dXJuIGdldCh0aGlzLCAnTW9udGgnKTtcbiAgICB9XG59XG5cbmZ1bmN0aW9uIGdldERheXNJbk1vbnRoICgpIHtcbiAgICByZXR1cm4gZGF5c0luTW9udGgodGhpcy55ZWFyKCksIHRoaXMubW9udGgoKSk7XG59XG5cbnZhciBkZWZhdWx0TW9udGhzU2hvcnRSZWdleCA9IG1hdGNoV29yZDtcbmZ1bmN0aW9uIG1vbnRoc1Nob3J0UmVnZXggKGlzU3RyaWN0KSB7XG4gICAgaWYgKHRoaXMuX21vbnRoc1BhcnNlRXhhY3QpIHtcbiAgICAgICAgaWYgKCFoYXNPd25Qcm9wKHRoaXMsICdfbW9udGhzUmVnZXgnKSkge1xuICAgICAgICAgICAgY29tcHV0ZU1vbnRoc1BhcnNlLmNhbGwodGhpcyk7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKGlzU3RyaWN0KSB7XG4gICAgICAgICAgICByZXR1cm4gdGhpcy5fbW9udGhzU2hvcnRTdHJpY3RSZWdleDtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIHJldHVybiB0aGlzLl9tb250aHNTaG9ydFJlZ2V4O1xuICAgICAgICB9XG4gICAgfSBlbHNlIHtcbiAgICAgICAgaWYgKCFoYXNPd25Qcm9wKHRoaXMsICdfbW9udGhzU2hvcnRSZWdleCcpKSB7XG4gICAgICAgICAgICB0aGlzLl9tb250aHNTaG9ydFJlZ2V4ID0gZGVmYXVsdE1vbnRoc1Nob3J0UmVnZXg7XG4gICAgICAgIH1cbiAgICAgICAgcmV0dXJuIHRoaXMuX21vbnRoc1Nob3J0U3RyaWN0UmVnZXggJiYgaXNTdHJpY3QgP1xuICAgICAgICAgICAgdGhpcy5fbW9udGhzU2hvcnRTdHJpY3RSZWdleCA6IHRoaXMuX21vbnRoc1Nob3J0UmVnZXg7XG4gICAgfVxufVxuXG52YXIgZGVmYXVsdE1vbnRoc1JlZ2V4ID0gbWF0Y2hXb3JkO1xuZnVuY3Rpb24gbW9udGhzUmVnZXggKGlzU3RyaWN0KSB7XG4gICAgaWYgKHRoaXMuX21vbnRoc1BhcnNlRXhhY3QpIHtcbiAgICAgICAgaWYgKCFoYXNPd25Qcm9wKHRoaXMsICdfbW9udGhzUmVnZXgnKSkge1xuICAgICAgICAgICAgY29tcHV0ZU1vbnRoc1BhcnNlLmNhbGwodGhpcyk7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKGlzU3RyaWN0KSB7XG4gICAgICAgICAgICByZXR1cm4gdGhpcy5fbW9udGhzU3RyaWN0UmVnZXg7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICByZXR1cm4gdGhpcy5fbW9udGhzUmVnZXg7XG4gICAgICAgIH1cbiAgICB9IGVsc2Uge1xuICAgICAgICBpZiAoIWhhc093blByb3AodGhpcywgJ19tb250aHNSZWdleCcpKSB7XG4gICAgICAgICAgICB0aGlzLl9tb250aHNSZWdleCA9IGRlZmF1bHRNb250aHNSZWdleDtcbiAgICAgICAgfVxuICAgICAgICByZXR1cm4gdGhpcy5fbW9udGhzU3RyaWN0UmVnZXggJiYgaXNTdHJpY3QgP1xuICAgICAgICAgICAgdGhpcy5fbW9udGhzU3RyaWN0UmVnZXggOiB0aGlzLl9tb250aHNSZWdleDtcbiAgICB9XG59XG5cbmZ1bmN0aW9uIGNvbXB1dGVNb250aHNQYXJzZSAoKSB7XG4gICAgZnVuY3Rpb24gY21wTGVuUmV2KGEsIGIpIHtcbiAgICAgICAgcmV0dXJuIGIubGVuZ3RoIC0gYS5sZW5ndGg7XG4gICAgfVxuXG4gICAgdmFyIHNob3J0UGllY2VzID0gW10sIGxvbmdQaWVjZXMgPSBbXSwgbWl4ZWRQaWVjZXMgPSBbXSxcbiAgICAgICAgaSwgbW9tO1xuICAgIGZvciAoaSA9IDA7IGkgPCAxMjsgaSsrKSB7XG4gICAgICAgIC8vIG1ha2UgdGhlIHJlZ2V4IGlmIHdlIGRvbid0IGhhdmUgaXQgYWxyZWFkeVxuICAgICAgICBtb20gPSBjcmVhdGVVVEMoWzIwMDAsIGldKTtcbiAgICAgICAgc2hvcnRQaWVjZXMucHVzaCh0aGlzLm1vbnRoc1Nob3J0KG1vbSwgJycpKTtcbiAgICAgICAgbG9uZ1BpZWNlcy5wdXNoKHRoaXMubW9udGhzKG1vbSwgJycpKTtcbiAgICAgICAgbWl4ZWRQaWVjZXMucHVzaCh0aGlzLm1vbnRocyhtb20sICcnKSk7XG4gICAgICAgIG1peGVkUGllY2VzLnB1c2godGhpcy5tb250aHNTaG9ydChtb20sICcnKSk7XG4gICAgfVxuICAgIC8vIFNvcnRpbmcgbWFrZXMgc3VyZSBpZiBvbmUgbW9udGggKG9yIGFiYnIpIGlzIGEgcHJlZml4IG9mIGFub3RoZXIgaXRcbiAgICAvLyB3aWxsIG1hdGNoIHRoZSBsb25nZXIgcGllY2UuXG4gICAgc2hvcnRQaWVjZXMuc29ydChjbXBMZW5SZXYpO1xuICAgIGxvbmdQaWVjZXMuc29ydChjbXBMZW5SZXYpO1xuICAgIG1peGVkUGllY2VzLnNvcnQoY21wTGVuUmV2KTtcbiAgICBmb3IgKGkgPSAwOyBpIDwgMTI7IGkrKykge1xuICAgICAgICBzaG9ydFBpZWNlc1tpXSA9IHJlZ2V4RXNjYXBlKHNob3J0UGllY2VzW2ldKTtcbiAgICAgICAgbG9uZ1BpZWNlc1tpXSA9IHJlZ2V4RXNjYXBlKGxvbmdQaWVjZXNbaV0pO1xuICAgIH1cbiAgICBmb3IgKGkgPSAwOyBpIDwgMjQ7IGkrKykge1xuICAgICAgICBtaXhlZFBpZWNlc1tpXSA9IHJlZ2V4RXNjYXBlKG1peGVkUGllY2VzW2ldKTtcbiAgICB9XG5cbiAgICB0aGlzLl9tb250aHNSZWdleCA9IG5ldyBSZWdFeHAoJ14oJyArIG1peGVkUGllY2VzLmpvaW4oJ3wnKSArICcpJywgJ2knKTtcbiAgICB0aGlzLl9tb250aHNTaG9ydFJlZ2V4ID0gdGhpcy5fbW9udGhzUmVnZXg7XG4gICAgdGhpcy5fbW9udGhzU3RyaWN0UmVnZXggPSBuZXcgUmVnRXhwKCdeKCcgKyBsb25nUGllY2VzLmpvaW4oJ3wnKSArICcpJywgJ2knKTtcbiAgICB0aGlzLl9tb250aHNTaG9ydFN0cmljdFJlZ2V4ID0gbmV3IFJlZ0V4cCgnXignICsgc2hvcnRQaWVjZXMuam9pbignfCcpICsgJyknLCAnaScpO1xufVxuXG5mdW5jdGlvbiBjcmVhdGVEYXRlICh5LCBtLCBkLCBoLCBNLCBzLCBtcykge1xuICAgIC8vIGNhbid0IGp1c3QgYXBwbHkoKSB0byBjcmVhdGUgYSBkYXRlOlxuICAgIC8vIGh0dHBzOi8vc3RhY2tvdmVyZmxvdy5jb20vcS8xODEzNDhcbiAgICB2YXIgZGF0ZSA9IG5ldyBEYXRlKHksIG0sIGQsIGgsIE0sIHMsIG1zKTtcblxuICAgIC8vIHRoZSBkYXRlIGNvbnN0cnVjdG9yIHJlbWFwcyB5ZWFycyAwLTk5IHRvIDE5MDAtMTk5OVxuICAgIGlmICh5IDwgMTAwICYmIHkgPj0gMCAmJiBpc0Zpbml0ZShkYXRlLmdldEZ1bGxZZWFyKCkpKSB7XG4gICAgICAgIGRhdGUuc2V0RnVsbFllYXIoeSk7XG4gICAgfVxuICAgIHJldHVybiBkYXRlO1xufVxuXG5mdW5jdGlvbiBjcmVhdGVVVENEYXRlICh5KSB7XG4gICAgdmFyIGRhdGUgPSBuZXcgRGF0ZShEYXRlLlVUQy5hcHBseShudWxsLCBhcmd1bWVudHMpKTtcblxuICAgIC8vIHRoZSBEYXRlLlVUQyBmdW5jdGlvbiByZW1hcHMgeWVhcnMgMC05OSB0byAxOTAwLTE5OTlcbiAgICBpZiAoeSA8IDEwMCAmJiB5ID49IDAgJiYgaXNGaW5pdGUoZGF0ZS5nZXRVVENGdWxsWWVhcigpKSkge1xuICAgICAgICBkYXRlLnNldFVUQ0Z1bGxZZWFyKHkpO1xuICAgIH1cbiAgICByZXR1cm4gZGF0ZTtcbn1cblxuLy8gc3RhcnQtb2YtZmlyc3Qtd2VlayAtIHN0YXJ0LW9mLXllYXJcbmZ1bmN0aW9uIGZpcnN0V2Vla09mZnNldCh5ZWFyLCBkb3csIGRveSkge1xuICAgIHZhciAvLyBmaXJzdC13ZWVrIGRheSAtLSB3aGljaCBqYW51YXJ5IGlzIGFsd2F5cyBpbiB0aGUgZmlyc3Qgd2VlayAoNCBmb3IgaXNvLCAxIGZvciBvdGhlcilcbiAgICAgICAgZndkID0gNyArIGRvdyAtIGRveSxcbiAgICAgICAgLy8gZmlyc3Qtd2VlayBkYXkgbG9jYWwgd2Vla2RheSAtLSB3aGljaCBsb2NhbCB3ZWVrZGF5IGlzIGZ3ZFxuICAgICAgICBmd2RsdyA9ICg3ICsgY3JlYXRlVVRDRGF0ZSh5ZWFyLCAwLCBmd2QpLmdldFVUQ0RheSgpIC0gZG93KSAlIDc7XG5cbiAgICByZXR1cm4gLWZ3ZGx3ICsgZndkIC0gMTtcbn1cblxuLy8gaHR0cHM6Ly9lbi53aWtpcGVkaWEub3JnL3dpa2kvSVNPX3dlZWtfZGF0ZSNDYWxjdWxhdGluZ19hX2RhdGVfZ2l2ZW5fdGhlX3llYXIuMkNfd2Vla19udW1iZXJfYW5kX3dlZWtkYXlcbmZ1bmN0aW9uIGRheU9mWWVhckZyb21XZWVrcyh5ZWFyLCB3ZWVrLCB3ZWVrZGF5LCBkb3csIGRveSkge1xuICAgIHZhciBsb2NhbFdlZWtkYXkgPSAoNyArIHdlZWtkYXkgLSBkb3cpICUgNyxcbiAgICAgICAgd2Vla09mZnNldCA9IGZpcnN0V2Vla09mZnNldCh5ZWFyLCBkb3csIGRveSksXG4gICAgICAgIGRheU9mWWVhciA9IDEgKyA3ICogKHdlZWsgLSAxKSArIGxvY2FsV2Vla2RheSArIHdlZWtPZmZzZXQsXG4gICAgICAgIHJlc1llYXIsIHJlc0RheU9mWWVhcjtcblxuICAgIGlmIChkYXlPZlllYXIgPD0gMCkge1xuICAgICAgICByZXNZZWFyID0geWVhciAtIDE7XG4gICAgICAgIHJlc0RheU9mWWVhciA9IGRheXNJblllYXIocmVzWWVhcikgKyBkYXlPZlllYXI7XG4gICAgfSBlbHNlIGlmIChkYXlPZlllYXIgPiBkYXlzSW5ZZWFyKHllYXIpKSB7XG4gICAgICAgIHJlc1llYXIgPSB5ZWFyICsgMTtcbiAgICAgICAgcmVzRGF5T2ZZZWFyID0gZGF5T2ZZZWFyIC0gZGF5c0luWWVhcih5ZWFyKTtcbiAgICB9IGVsc2Uge1xuICAgICAgICByZXNZZWFyID0geWVhcjtcbiAgICAgICAgcmVzRGF5T2ZZZWFyID0gZGF5T2ZZZWFyO1xuICAgIH1cblxuICAgIHJldHVybiB7XG4gICAgICAgIHllYXI6IHJlc1llYXIsXG4gICAgICAgIGRheU9mWWVhcjogcmVzRGF5T2ZZZWFyXG4gICAgfTtcbn1cblxuZnVuY3Rpb24gd2Vla09mWWVhcihtb20sIGRvdywgZG95KSB7XG4gICAgdmFyIHdlZWtPZmZzZXQgPSBmaXJzdFdlZWtPZmZzZXQobW9tLnllYXIoKSwgZG93LCBkb3kpLFxuICAgICAgICB3ZWVrID0gTWF0aC5mbG9vcigobW9tLmRheU9mWWVhcigpIC0gd2Vla09mZnNldCAtIDEpIC8gNykgKyAxLFxuICAgICAgICByZXNXZWVrLCByZXNZZWFyO1xuXG4gICAgaWYgKHdlZWsgPCAxKSB7XG4gICAgICAgIHJlc1llYXIgPSBtb20ueWVhcigpIC0gMTtcbiAgICAgICAgcmVzV2VlayA9IHdlZWsgKyB3ZWVrc0luWWVhcihyZXNZZWFyLCBkb3csIGRveSk7XG4gICAgfSBlbHNlIGlmICh3ZWVrID4gd2Vla3NJblllYXIobW9tLnllYXIoKSwgZG93LCBkb3kpKSB7XG4gICAgICAgIHJlc1dlZWsgPSB3ZWVrIC0gd2Vla3NJblllYXIobW9tLnllYXIoKSwgZG93LCBkb3kpO1xuICAgICAgICByZXNZZWFyID0gbW9tLnllYXIoKSArIDE7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgcmVzWWVhciA9IG1vbS55ZWFyKCk7XG4gICAgICAgIHJlc1dlZWsgPSB3ZWVrO1xuICAgIH1cblxuICAgIHJldHVybiB7XG4gICAgICAgIHdlZWs6IHJlc1dlZWssXG4gICAgICAgIHllYXI6IHJlc1llYXJcbiAgICB9O1xufVxuXG5mdW5jdGlvbiB3ZWVrc0luWWVhcih5ZWFyLCBkb3csIGRveSkge1xuICAgIHZhciB3ZWVrT2Zmc2V0ID0gZmlyc3RXZWVrT2Zmc2V0KHllYXIsIGRvdywgZG95KSxcbiAgICAgICAgd2Vla09mZnNldE5leHQgPSBmaXJzdFdlZWtPZmZzZXQoeWVhciArIDEsIGRvdywgZG95KTtcbiAgICByZXR1cm4gKGRheXNJblllYXIoeWVhcikgLSB3ZWVrT2Zmc2V0ICsgd2Vla09mZnNldE5leHQpIC8gNztcbn1cblxuLy8gRk9STUFUVElOR1xuXG5hZGRGb3JtYXRUb2tlbigndycsIFsnd3cnLCAyXSwgJ3dvJywgJ3dlZWsnKTtcbmFkZEZvcm1hdFRva2VuKCdXJywgWydXVycsIDJdLCAnV28nLCAnaXNvV2VlaycpO1xuXG4vLyBBTElBU0VTXG5cbmFkZFVuaXRBbGlhcygnd2VlaycsICd3Jyk7XG5hZGRVbml0QWxpYXMoJ2lzb1dlZWsnLCAnVycpO1xuXG4vLyBQUklPUklUSUVTXG5cbmFkZFVuaXRQcmlvcml0eSgnd2VlaycsIDUpO1xuYWRkVW5pdFByaW9yaXR5KCdpc29XZWVrJywgNSk7XG5cbi8vIFBBUlNJTkdcblxuYWRkUmVnZXhUb2tlbigndycsICBtYXRjaDF0bzIpO1xuYWRkUmVnZXhUb2tlbignd3cnLCBtYXRjaDF0bzIsIG1hdGNoMik7XG5hZGRSZWdleFRva2VuKCdXJywgIG1hdGNoMXRvMik7XG5hZGRSZWdleFRva2VuKCdXVycsIG1hdGNoMXRvMiwgbWF0Y2gyKTtcblxuYWRkV2Vla1BhcnNlVG9rZW4oWyd3JywgJ3d3JywgJ1cnLCAnV1cnXSwgZnVuY3Rpb24gKGlucHV0LCB3ZWVrLCBjb25maWcsIHRva2VuKSB7XG4gICAgd2Vla1t0b2tlbi5zdWJzdHIoMCwgMSldID0gdG9JbnQoaW5wdXQpO1xufSk7XG5cbi8vIEhFTFBFUlNcblxuLy8gTE9DQUxFU1xuXG5mdW5jdGlvbiBsb2NhbGVXZWVrIChtb20pIHtcbiAgICByZXR1cm4gd2Vla09mWWVhcihtb20sIHRoaXMuX3dlZWsuZG93LCB0aGlzLl93ZWVrLmRveSkud2Vlaztcbn1cblxudmFyIGRlZmF1bHRMb2NhbGVXZWVrID0ge1xuICAgIGRvdyA6IDAsIC8vIFN1bmRheSBpcyB0aGUgZmlyc3QgZGF5IG9mIHRoZSB3ZWVrLlxuICAgIGRveSA6IDYgIC8vIFRoZSB3ZWVrIHRoYXQgY29udGFpbnMgSmFuIDFzdCBpcyB0aGUgZmlyc3Qgd2VlayBvZiB0aGUgeWVhci5cbn07XG5cbmZ1bmN0aW9uIGxvY2FsZUZpcnN0RGF5T2ZXZWVrICgpIHtcbiAgICByZXR1cm4gdGhpcy5fd2Vlay5kb3c7XG59XG5cbmZ1bmN0aW9uIGxvY2FsZUZpcnN0RGF5T2ZZZWFyICgpIHtcbiAgICByZXR1cm4gdGhpcy5fd2Vlay5kb3k7XG59XG5cbi8vIE1PTUVOVFNcblxuZnVuY3Rpb24gZ2V0U2V0V2VlayAoaW5wdXQpIHtcbiAgICB2YXIgd2VlayA9IHRoaXMubG9jYWxlRGF0YSgpLndlZWsodGhpcyk7XG4gICAgcmV0dXJuIGlucHV0ID09IG51bGwgPyB3ZWVrIDogdGhpcy5hZGQoKGlucHV0IC0gd2VlaykgKiA3LCAnZCcpO1xufVxuXG5mdW5jdGlvbiBnZXRTZXRJU09XZWVrIChpbnB1dCkge1xuICAgIHZhciB3ZWVrID0gd2Vla09mWWVhcih0aGlzLCAxLCA0KS53ZWVrO1xuICAgIHJldHVybiBpbnB1dCA9PSBudWxsID8gd2VlayA6IHRoaXMuYWRkKChpbnB1dCAtIHdlZWspICogNywgJ2QnKTtcbn1cblxuLy8gRk9STUFUVElOR1xuXG5hZGRGb3JtYXRUb2tlbignZCcsIDAsICdkbycsICdkYXknKTtcblxuYWRkRm9ybWF0VG9rZW4oJ2RkJywgMCwgMCwgZnVuY3Rpb24gKGZvcm1hdCkge1xuICAgIHJldHVybiB0aGlzLmxvY2FsZURhdGEoKS53ZWVrZGF5c01pbih0aGlzLCBmb3JtYXQpO1xufSk7XG5cbmFkZEZvcm1hdFRva2VuKCdkZGQnLCAwLCAwLCBmdW5jdGlvbiAoZm9ybWF0KSB7XG4gICAgcmV0dXJuIHRoaXMubG9jYWxlRGF0YSgpLndlZWtkYXlzU2hvcnQodGhpcywgZm9ybWF0KTtcbn0pO1xuXG5hZGRGb3JtYXRUb2tlbignZGRkZCcsIDAsIDAsIGZ1bmN0aW9uIChmb3JtYXQpIHtcbiAgICByZXR1cm4gdGhpcy5sb2NhbGVEYXRhKCkud2Vla2RheXModGhpcywgZm9ybWF0KTtcbn0pO1xuXG5hZGRGb3JtYXRUb2tlbignZScsIDAsIDAsICd3ZWVrZGF5Jyk7XG5hZGRGb3JtYXRUb2tlbignRScsIDAsIDAsICdpc29XZWVrZGF5Jyk7XG5cbi8vIEFMSUFTRVNcblxuYWRkVW5pdEFsaWFzKCdkYXknLCAnZCcpO1xuYWRkVW5pdEFsaWFzKCd3ZWVrZGF5JywgJ2UnKTtcbmFkZFVuaXRBbGlhcygnaXNvV2Vla2RheScsICdFJyk7XG5cbi8vIFBSSU9SSVRZXG5hZGRVbml0UHJpb3JpdHkoJ2RheScsIDExKTtcbmFkZFVuaXRQcmlvcml0eSgnd2Vla2RheScsIDExKTtcbmFkZFVuaXRQcmlvcml0eSgnaXNvV2Vla2RheScsIDExKTtcblxuLy8gUEFSU0lOR1xuXG5hZGRSZWdleFRva2VuKCdkJywgICAgbWF0Y2gxdG8yKTtcbmFkZFJlZ2V4VG9rZW4oJ2UnLCAgICBtYXRjaDF0bzIpO1xuYWRkUmVnZXhUb2tlbignRScsICAgIG1hdGNoMXRvMik7XG5hZGRSZWdleFRva2VuKCdkZCcsICAgZnVuY3Rpb24gKGlzU3RyaWN0LCBsb2NhbGUpIHtcbiAgICByZXR1cm4gbG9jYWxlLndlZWtkYXlzTWluUmVnZXgoaXNTdHJpY3QpO1xufSk7XG5hZGRSZWdleFRva2VuKCdkZGQnLCAgIGZ1bmN0aW9uIChpc1N0cmljdCwgbG9jYWxlKSB7XG4gICAgcmV0dXJuIGxvY2FsZS53ZWVrZGF5c1Nob3J0UmVnZXgoaXNTdHJpY3QpO1xufSk7XG5hZGRSZWdleFRva2VuKCdkZGRkJywgICBmdW5jdGlvbiAoaXNTdHJpY3QsIGxvY2FsZSkge1xuICAgIHJldHVybiBsb2NhbGUud2Vla2RheXNSZWdleChpc1N0cmljdCk7XG59KTtcblxuYWRkV2Vla1BhcnNlVG9rZW4oWydkZCcsICdkZGQnLCAnZGRkZCddLCBmdW5jdGlvbiAoaW5wdXQsIHdlZWssIGNvbmZpZywgdG9rZW4pIHtcbiAgICB2YXIgd2Vla2RheSA9IGNvbmZpZy5fbG9jYWxlLndlZWtkYXlzUGFyc2UoaW5wdXQsIHRva2VuLCBjb25maWcuX3N0cmljdCk7XG4gICAgLy8gaWYgd2UgZGlkbid0IGdldCBhIHdlZWtkYXkgbmFtZSwgbWFyayB0aGUgZGF0ZSBhcyBpbnZhbGlkXG4gICAgaWYgKHdlZWtkYXkgIT0gbnVsbCkge1xuICAgICAgICB3ZWVrLmQgPSB3ZWVrZGF5O1xuICAgIH0gZWxzZSB7XG4gICAgICAgIGdldFBhcnNpbmdGbGFncyhjb25maWcpLmludmFsaWRXZWVrZGF5ID0gaW5wdXQ7XG4gICAgfVxufSk7XG5cbmFkZFdlZWtQYXJzZVRva2VuKFsnZCcsICdlJywgJ0UnXSwgZnVuY3Rpb24gKGlucHV0LCB3ZWVrLCBjb25maWcsIHRva2VuKSB7XG4gICAgd2Vla1t0b2tlbl0gPSB0b0ludChpbnB1dCk7XG59KTtcblxuLy8gSEVMUEVSU1xuXG5mdW5jdGlvbiBwYXJzZVdlZWtkYXkoaW5wdXQsIGxvY2FsZSkge1xuICAgIGlmICh0eXBlb2YgaW5wdXQgIT09ICdzdHJpbmcnKSB7XG4gICAgICAgIHJldHVybiBpbnB1dDtcbiAgICB9XG5cbiAgICBpZiAoIWlzTmFOKGlucHV0KSkge1xuICAgICAgICByZXR1cm4gcGFyc2VJbnQoaW5wdXQsIDEwKTtcbiAgICB9XG5cbiAgICBpbnB1dCA9IGxvY2FsZS53ZWVrZGF5c1BhcnNlKGlucHV0KTtcbiAgICBpZiAodHlwZW9mIGlucHV0ID09PSAnbnVtYmVyJykge1xuICAgICAgICByZXR1cm4gaW5wdXQ7XG4gICAgfVxuXG4gICAgcmV0dXJuIG51bGw7XG59XG5cbmZ1bmN0aW9uIHBhcnNlSXNvV2Vla2RheShpbnB1dCwgbG9jYWxlKSB7XG4gICAgaWYgKHR5cGVvZiBpbnB1dCA9PT0gJ3N0cmluZycpIHtcbiAgICAgICAgcmV0dXJuIGxvY2FsZS53ZWVrZGF5c1BhcnNlKGlucHV0KSAlIDcgfHwgNztcbiAgICB9XG4gICAgcmV0dXJuIGlzTmFOKGlucHV0KSA/IG51bGwgOiBpbnB1dDtcbn1cblxuLy8gTE9DQUxFU1xuXG52YXIgZGVmYXVsdExvY2FsZVdlZWtkYXlzID0gJ1N1bmRheV9Nb25kYXlfVHVlc2RheV9XZWRuZXNkYXlfVGh1cnNkYXlfRnJpZGF5X1NhdHVyZGF5Jy5zcGxpdCgnXycpO1xuZnVuY3Rpb24gbG9jYWxlV2Vla2RheXMgKG0sIGZvcm1hdCkge1xuICAgIGlmICghbSkge1xuICAgICAgICByZXR1cm4gaXNBcnJheSh0aGlzLl93ZWVrZGF5cykgPyB0aGlzLl93ZWVrZGF5cyA6XG4gICAgICAgICAgICB0aGlzLl93ZWVrZGF5c1snc3RhbmRhbG9uZSddO1xuICAgIH1cbiAgICByZXR1cm4gaXNBcnJheSh0aGlzLl93ZWVrZGF5cykgPyB0aGlzLl93ZWVrZGF5c1ttLmRheSgpXSA6XG4gICAgICAgIHRoaXMuX3dlZWtkYXlzW3RoaXMuX3dlZWtkYXlzLmlzRm9ybWF0LnRlc3QoZm9ybWF0KSA/ICdmb3JtYXQnIDogJ3N0YW5kYWxvbmUnXVttLmRheSgpXTtcbn1cblxudmFyIGRlZmF1bHRMb2NhbGVXZWVrZGF5c1Nob3J0ID0gJ1N1bl9Nb25fVHVlX1dlZF9UaHVfRnJpX1NhdCcuc3BsaXQoJ18nKTtcbmZ1bmN0aW9uIGxvY2FsZVdlZWtkYXlzU2hvcnQgKG0pIHtcbiAgICByZXR1cm4gKG0pID8gdGhpcy5fd2Vla2RheXNTaG9ydFttLmRheSgpXSA6IHRoaXMuX3dlZWtkYXlzU2hvcnQ7XG59XG5cbnZhciBkZWZhdWx0TG9jYWxlV2Vla2RheXNNaW4gPSAnU3VfTW9fVHVfV2VfVGhfRnJfU2EnLnNwbGl0KCdfJyk7XG5mdW5jdGlvbiBsb2NhbGVXZWVrZGF5c01pbiAobSkge1xuICAgIHJldHVybiAobSkgPyB0aGlzLl93ZWVrZGF5c01pblttLmRheSgpXSA6IHRoaXMuX3dlZWtkYXlzTWluO1xufVxuXG5mdW5jdGlvbiBoYW5kbGVTdHJpY3RQYXJzZSQxKHdlZWtkYXlOYW1lLCBmb3JtYXQsIHN0cmljdCkge1xuICAgIHZhciBpLCBpaSwgbW9tLCBsbGMgPSB3ZWVrZGF5TmFtZS50b0xvY2FsZUxvd2VyQ2FzZSgpO1xuICAgIGlmICghdGhpcy5fd2Vla2RheXNQYXJzZSkge1xuICAgICAgICB0aGlzLl93ZWVrZGF5c1BhcnNlID0gW107XG4gICAgICAgIHRoaXMuX3Nob3J0V2Vla2RheXNQYXJzZSA9IFtdO1xuICAgICAgICB0aGlzLl9taW5XZWVrZGF5c1BhcnNlID0gW107XG5cbiAgICAgICAgZm9yIChpID0gMDsgaSA8IDc7ICsraSkge1xuICAgICAgICAgICAgbW9tID0gY3JlYXRlVVRDKFsyMDAwLCAxXSkuZGF5KGkpO1xuICAgICAgICAgICAgdGhpcy5fbWluV2Vla2RheXNQYXJzZVtpXSA9IHRoaXMud2Vla2RheXNNaW4obW9tLCAnJykudG9Mb2NhbGVMb3dlckNhc2UoKTtcbiAgICAgICAgICAgIHRoaXMuX3Nob3J0V2Vla2RheXNQYXJzZVtpXSA9IHRoaXMud2Vla2RheXNTaG9ydChtb20sICcnKS50b0xvY2FsZUxvd2VyQ2FzZSgpO1xuICAgICAgICAgICAgdGhpcy5fd2Vla2RheXNQYXJzZVtpXSA9IHRoaXMud2Vla2RheXMobW9tLCAnJykudG9Mb2NhbGVMb3dlckNhc2UoKTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIGlmIChzdHJpY3QpIHtcbiAgICAgICAgaWYgKGZvcm1hdCA9PT0gJ2RkZGQnKSB7XG4gICAgICAgICAgICBpaSA9IGluZGV4T2YuY2FsbCh0aGlzLl93ZWVrZGF5c1BhcnNlLCBsbGMpO1xuICAgICAgICAgICAgcmV0dXJuIGlpICE9PSAtMSA/IGlpIDogbnVsbDtcbiAgICAgICAgfSBlbHNlIGlmIChmb3JtYXQgPT09ICdkZGQnKSB7XG4gICAgICAgICAgICBpaSA9IGluZGV4T2YuY2FsbCh0aGlzLl9zaG9ydFdlZWtkYXlzUGFyc2UsIGxsYyk7XG4gICAgICAgICAgICByZXR1cm4gaWkgIT09IC0xID8gaWkgOiBudWxsO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgaWkgPSBpbmRleE9mLmNhbGwodGhpcy5fbWluV2Vla2RheXNQYXJzZSwgbGxjKTtcbiAgICAgICAgICAgIHJldHVybiBpaSAhPT0gLTEgPyBpaSA6IG51bGw7XG4gICAgICAgIH1cbiAgICB9IGVsc2Uge1xuICAgICAgICBpZiAoZm9ybWF0ID09PSAnZGRkZCcpIHtcbiAgICAgICAgICAgIGlpID0gaW5kZXhPZi5jYWxsKHRoaXMuX3dlZWtkYXlzUGFyc2UsIGxsYyk7XG4gICAgICAgICAgICBpZiAoaWkgIT09IC0xKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuIGlpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgaWkgPSBpbmRleE9mLmNhbGwodGhpcy5fc2hvcnRXZWVrZGF5c1BhcnNlLCBsbGMpO1xuICAgICAgICAgICAgaWYgKGlpICE9PSAtMSkge1xuICAgICAgICAgICAgICAgIHJldHVybiBpaTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGlpID0gaW5kZXhPZi5jYWxsKHRoaXMuX21pbldlZWtkYXlzUGFyc2UsIGxsYyk7XG4gICAgICAgICAgICByZXR1cm4gaWkgIT09IC0xID8gaWkgOiBudWxsO1xuICAgICAgICB9IGVsc2UgaWYgKGZvcm1hdCA9PT0gJ2RkZCcpIHtcbiAgICAgICAgICAgIGlpID0gaW5kZXhPZi5jYWxsKHRoaXMuX3Nob3J0V2Vla2RheXNQYXJzZSwgbGxjKTtcbiAgICAgICAgICAgIGlmIChpaSAhPT0gLTEpIHtcbiAgICAgICAgICAgICAgICByZXR1cm4gaWk7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBpaSA9IGluZGV4T2YuY2FsbCh0aGlzLl93ZWVrZGF5c1BhcnNlLCBsbGMpO1xuICAgICAgICAgICAgaWYgKGlpICE9PSAtMSkge1xuICAgICAgICAgICAgICAgIHJldHVybiBpaTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGlpID0gaW5kZXhPZi5jYWxsKHRoaXMuX21pbldlZWtkYXlzUGFyc2UsIGxsYyk7XG4gICAgICAgICAgICByZXR1cm4gaWkgIT09IC0xID8gaWkgOiBudWxsO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgaWkgPSBpbmRleE9mLmNhbGwodGhpcy5fbWluV2Vla2RheXNQYXJzZSwgbGxjKTtcbiAgICAgICAgICAgIGlmIChpaSAhPT0gLTEpIHtcbiAgICAgICAgICAgICAgICByZXR1cm4gaWk7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBpaSA9IGluZGV4T2YuY2FsbCh0aGlzLl93ZWVrZGF5c1BhcnNlLCBsbGMpO1xuICAgICAgICAgICAgaWYgKGlpICE9PSAtMSkge1xuICAgICAgICAgICAgICAgIHJldHVybiBpaTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGlpID0gaW5kZXhPZi5jYWxsKHRoaXMuX3Nob3J0V2Vla2RheXNQYXJzZSwgbGxjKTtcbiAgICAgICAgICAgIHJldHVybiBpaSAhPT0gLTEgPyBpaSA6IG51bGw7XG4gICAgICAgIH1cbiAgICB9XG59XG5cbmZ1bmN0aW9uIGxvY2FsZVdlZWtkYXlzUGFyc2UgKHdlZWtkYXlOYW1lLCBmb3JtYXQsIHN0cmljdCkge1xuICAgIHZhciBpLCBtb20sIHJlZ2V4O1xuXG4gICAgaWYgKHRoaXMuX3dlZWtkYXlzUGFyc2VFeGFjdCkge1xuICAgICAgICByZXR1cm4gaGFuZGxlU3RyaWN0UGFyc2UkMS5jYWxsKHRoaXMsIHdlZWtkYXlOYW1lLCBmb3JtYXQsIHN0cmljdCk7XG4gICAgfVxuXG4gICAgaWYgKCF0aGlzLl93ZWVrZGF5c1BhcnNlKSB7XG4gICAgICAgIHRoaXMuX3dlZWtkYXlzUGFyc2UgPSBbXTtcbiAgICAgICAgdGhpcy5fbWluV2Vla2RheXNQYXJzZSA9IFtdO1xuICAgICAgICB0aGlzLl9zaG9ydFdlZWtkYXlzUGFyc2UgPSBbXTtcbiAgICAgICAgdGhpcy5fZnVsbFdlZWtkYXlzUGFyc2UgPSBbXTtcbiAgICB9XG5cbiAgICBmb3IgKGkgPSAwOyBpIDwgNzsgaSsrKSB7XG4gICAgICAgIC8vIG1ha2UgdGhlIHJlZ2V4IGlmIHdlIGRvbid0IGhhdmUgaXQgYWxyZWFkeVxuXG4gICAgICAgIG1vbSA9IGNyZWF0ZVVUQyhbMjAwMCwgMV0pLmRheShpKTtcbiAgICAgICAgaWYgKHN0cmljdCAmJiAhdGhpcy5fZnVsbFdlZWtkYXlzUGFyc2VbaV0pIHtcbiAgICAgICAgICAgIHRoaXMuX2Z1bGxXZWVrZGF5c1BhcnNlW2ldID0gbmV3IFJlZ0V4cCgnXicgKyB0aGlzLndlZWtkYXlzKG1vbSwgJycpLnJlcGxhY2UoJy4nLCAnXFwuPycpICsgJyQnLCAnaScpO1xuICAgICAgICAgICAgdGhpcy5fc2hvcnRXZWVrZGF5c1BhcnNlW2ldID0gbmV3IFJlZ0V4cCgnXicgKyB0aGlzLndlZWtkYXlzU2hvcnQobW9tLCAnJykucmVwbGFjZSgnLicsICdcXC4/JykgKyAnJCcsICdpJyk7XG4gICAgICAgICAgICB0aGlzLl9taW5XZWVrZGF5c1BhcnNlW2ldID0gbmV3IFJlZ0V4cCgnXicgKyB0aGlzLndlZWtkYXlzTWluKG1vbSwgJycpLnJlcGxhY2UoJy4nLCAnXFwuPycpICsgJyQnLCAnaScpO1xuICAgICAgICB9XG4gICAgICAgIGlmICghdGhpcy5fd2Vla2RheXNQYXJzZVtpXSkge1xuICAgICAgICAgICAgcmVnZXggPSAnXicgKyB0aGlzLndlZWtkYXlzKG1vbSwgJycpICsgJ3xeJyArIHRoaXMud2Vla2RheXNTaG9ydChtb20sICcnKSArICd8XicgKyB0aGlzLndlZWtkYXlzTWluKG1vbSwgJycpO1xuICAgICAgICAgICAgdGhpcy5fd2Vla2RheXNQYXJzZVtpXSA9IG5ldyBSZWdFeHAocmVnZXgucmVwbGFjZSgnLicsICcnKSwgJ2knKTtcbiAgICAgICAgfVxuICAgICAgICAvLyB0ZXN0IHRoZSByZWdleFxuICAgICAgICBpZiAoc3RyaWN0ICYmIGZvcm1hdCA9PT0gJ2RkZGQnICYmIHRoaXMuX2Z1bGxXZWVrZGF5c1BhcnNlW2ldLnRlc3Qod2Vla2RheU5hbWUpKSB7XG4gICAgICAgICAgICByZXR1cm4gaTtcbiAgICAgICAgfSBlbHNlIGlmIChzdHJpY3QgJiYgZm9ybWF0ID09PSAnZGRkJyAmJiB0aGlzLl9zaG9ydFdlZWtkYXlzUGFyc2VbaV0udGVzdCh3ZWVrZGF5TmFtZSkpIHtcbiAgICAgICAgICAgIHJldHVybiBpO1xuICAgICAgICB9IGVsc2UgaWYgKHN0cmljdCAmJiBmb3JtYXQgPT09ICdkZCcgJiYgdGhpcy5fbWluV2Vla2RheXNQYXJzZVtpXS50ZXN0KHdlZWtkYXlOYW1lKSkge1xuICAgICAgICAgICAgcmV0dXJuIGk7XG4gICAgICAgIH0gZWxzZSBpZiAoIXN0cmljdCAmJiB0aGlzLl93ZWVrZGF5c1BhcnNlW2ldLnRlc3Qod2Vla2RheU5hbWUpKSB7XG4gICAgICAgICAgICByZXR1cm4gaTtcbiAgICAgICAgfVxuICAgIH1cbn1cblxuLy8gTU9NRU5UU1xuXG5mdW5jdGlvbiBnZXRTZXREYXlPZldlZWsgKGlucHV0KSB7XG4gICAgaWYgKCF0aGlzLmlzVmFsaWQoKSkge1xuICAgICAgICByZXR1cm4gaW5wdXQgIT0gbnVsbCA/IHRoaXMgOiBOYU47XG4gICAgfVxuICAgIHZhciBkYXkgPSB0aGlzLl9pc1VUQyA/IHRoaXMuX2QuZ2V0VVRDRGF5KCkgOiB0aGlzLl9kLmdldERheSgpO1xuICAgIGlmIChpbnB1dCAhPSBudWxsKSB7XG4gICAgICAgIGlucHV0ID0gcGFyc2VXZWVrZGF5KGlucHV0LCB0aGlzLmxvY2FsZURhdGEoKSk7XG4gICAgICAgIHJldHVybiB0aGlzLmFkZChpbnB1dCAtIGRheSwgJ2QnKTtcbiAgICB9IGVsc2Uge1xuICAgICAgICByZXR1cm4gZGF5O1xuICAgIH1cbn1cblxuZnVuY3Rpb24gZ2V0U2V0TG9jYWxlRGF5T2ZXZWVrIChpbnB1dCkge1xuICAgIGlmICghdGhpcy5pc1ZhbGlkKCkpIHtcbiAgICAgICAgcmV0dXJuIGlucHV0ICE9IG51bGwgPyB0aGlzIDogTmFOO1xuICAgIH1cbiAgICB2YXIgd2Vla2RheSA9ICh0aGlzLmRheSgpICsgNyAtIHRoaXMubG9jYWxlRGF0YSgpLl93ZWVrLmRvdykgJSA3O1xuICAgIHJldHVybiBpbnB1dCA9PSBudWxsID8gd2Vla2RheSA6IHRoaXMuYWRkKGlucHV0IC0gd2Vla2RheSwgJ2QnKTtcbn1cblxuZnVuY3Rpb24gZ2V0U2V0SVNPRGF5T2ZXZWVrIChpbnB1dCkge1xuICAgIGlmICghdGhpcy5pc1ZhbGlkKCkpIHtcbiAgICAgICAgcmV0dXJuIGlucHV0ICE9IG51bGwgPyB0aGlzIDogTmFOO1xuICAgIH1cblxuICAgIC8vIGJlaGF2ZXMgdGhlIHNhbWUgYXMgbW9tZW50I2RheSBleGNlcHRcbiAgICAvLyBhcyBhIGdldHRlciwgcmV0dXJucyA3IGluc3RlYWQgb2YgMCAoMS03IHJhbmdlIGluc3RlYWQgb2YgMC02KVxuICAgIC8vIGFzIGEgc2V0dGVyLCBzdW5kYXkgc2hvdWxkIGJlbG9uZyB0byB0aGUgcHJldmlvdXMgd2Vlay5cblxuICAgIGlmIChpbnB1dCAhPSBudWxsKSB7XG4gICAgICAgIHZhciB3ZWVrZGF5ID0gcGFyc2VJc29XZWVrZGF5KGlucHV0LCB0aGlzLmxvY2FsZURhdGEoKSk7XG4gICAgICAgIHJldHVybiB0aGlzLmRheSh0aGlzLmRheSgpICUgNyA/IHdlZWtkYXkgOiB3ZWVrZGF5IC0gNyk7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuZGF5KCkgfHwgNztcbiAgICB9XG59XG5cbnZhciBkZWZhdWx0V2Vla2RheXNSZWdleCA9IG1hdGNoV29yZDtcbmZ1bmN0aW9uIHdlZWtkYXlzUmVnZXggKGlzU3RyaWN0KSB7XG4gICAgaWYgKHRoaXMuX3dlZWtkYXlzUGFyc2VFeGFjdCkge1xuICAgICAgICBpZiAoIWhhc093blByb3AodGhpcywgJ193ZWVrZGF5c1JlZ2V4JykpIHtcbiAgICAgICAgICAgIGNvbXB1dGVXZWVrZGF5c1BhcnNlLmNhbGwodGhpcyk7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKGlzU3RyaWN0KSB7XG4gICAgICAgICAgICByZXR1cm4gdGhpcy5fd2Vla2RheXNTdHJpY3RSZWdleDtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIHJldHVybiB0aGlzLl93ZWVrZGF5c1JlZ2V4O1xuICAgICAgICB9XG4gICAgfSBlbHNlIHtcbiAgICAgICAgaWYgKCFoYXNPd25Qcm9wKHRoaXMsICdfd2Vla2RheXNSZWdleCcpKSB7XG4gICAgICAgICAgICB0aGlzLl93ZWVrZGF5c1JlZ2V4ID0gZGVmYXVsdFdlZWtkYXlzUmVnZXg7XG4gICAgICAgIH1cbiAgICAgICAgcmV0dXJuIHRoaXMuX3dlZWtkYXlzU3RyaWN0UmVnZXggJiYgaXNTdHJpY3QgP1xuICAgICAgICAgICAgdGhpcy5fd2Vla2RheXNTdHJpY3RSZWdleCA6IHRoaXMuX3dlZWtkYXlzUmVnZXg7XG4gICAgfVxufVxuXG52YXIgZGVmYXVsdFdlZWtkYXlzU2hvcnRSZWdleCA9IG1hdGNoV29yZDtcbmZ1bmN0aW9uIHdlZWtkYXlzU2hvcnRSZWdleCAoaXNTdHJpY3QpIHtcbiAgICBpZiAodGhpcy5fd2Vla2RheXNQYXJzZUV4YWN0KSB7XG4gICAgICAgIGlmICghaGFzT3duUHJvcCh0aGlzLCAnX3dlZWtkYXlzUmVnZXgnKSkge1xuICAgICAgICAgICAgY29tcHV0ZVdlZWtkYXlzUGFyc2UuY2FsbCh0aGlzKTtcbiAgICAgICAgfVxuICAgICAgICBpZiAoaXNTdHJpY3QpIHtcbiAgICAgICAgICAgIHJldHVybiB0aGlzLl93ZWVrZGF5c1Nob3J0U3RyaWN0UmVnZXg7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICByZXR1cm4gdGhpcy5fd2Vla2RheXNTaG9ydFJlZ2V4O1xuICAgICAgICB9XG4gICAgfSBlbHNlIHtcbiAgICAgICAgaWYgKCFoYXNPd25Qcm9wKHRoaXMsICdfd2Vla2RheXNTaG9ydFJlZ2V4JykpIHtcbiAgICAgICAgICAgIHRoaXMuX3dlZWtkYXlzU2hvcnRSZWdleCA9IGRlZmF1bHRXZWVrZGF5c1Nob3J0UmVnZXg7XG4gICAgICAgIH1cbiAgICAgICAgcmV0dXJuIHRoaXMuX3dlZWtkYXlzU2hvcnRTdHJpY3RSZWdleCAmJiBpc1N0cmljdCA/XG4gICAgICAgICAgICB0aGlzLl93ZWVrZGF5c1Nob3J0U3RyaWN0UmVnZXggOiB0aGlzLl93ZWVrZGF5c1Nob3J0UmVnZXg7XG4gICAgfVxufVxuXG52YXIgZGVmYXVsdFdlZWtkYXlzTWluUmVnZXggPSBtYXRjaFdvcmQ7XG5mdW5jdGlvbiB3ZWVrZGF5c01pblJlZ2V4IChpc1N0cmljdCkge1xuICAgIGlmICh0aGlzLl93ZWVrZGF5c1BhcnNlRXhhY3QpIHtcbiAgICAgICAgaWYgKCFoYXNPd25Qcm9wKHRoaXMsICdfd2Vla2RheXNSZWdleCcpKSB7XG4gICAgICAgICAgICBjb21wdXRlV2Vla2RheXNQYXJzZS5jYWxsKHRoaXMpO1xuICAgICAgICB9XG4gICAgICAgIGlmIChpc1N0cmljdCkge1xuICAgICAgICAgICAgcmV0dXJuIHRoaXMuX3dlZWtkYXlzTWluU3RyaWN0UmVnZXg7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICByZXR1cm4gdGhpcy5fd2Vla2RheXNNaW5SZWdleDtcbiAgICAgICAgfVxuICAgIH0gZWxzZSB7XG4gICAgICAgIGlmICghaGFzT3duUHJvcCh0aGlzLCAnX3dlZWtkYXlzTWluUmVnZXgnKSkge1xuICAgICAgICAgICAgdGhpcy5fd2Vla2RheXNNaW5SZWdleCA9IGRlZmF1bHRXZWVrZGF5c01pblJlZ2V4O1xuICAgICAgICB9XG4gICAgICAgIHJldHVybiB0aGlzLl93ZWVrZGF5c01pblN0cmljdFJlZ2V4ICYmIGlzU3RyaWN0ID9cbiAgICAgICAgICAgIHRoaXMuX3dlZWtkYXlzTWluU3RyaWN0UmVnZXggOiB0aGlzLl93ZWVrZGF5c01pblJlZ2V4O1xuICAgIH1cbn1cblxuXG5mdW5jdGlvbiBjb21wdXRlV2Vla2RheXNQYXJzZSAoKSB7XG4gICAgZnVuY3Rpb24gY21wTGVuUmV2KGEsIGIpIHtcbiAgICAgICAgcmV0dXJuIGIubGVuZ3RoIC0gYS5sZW5ndGg7XG4gICAgfVxuXG4gICAgdmFyIG1pblBpZWNlcyA9IFtdLCBzaG9ydFBpZWNlcyA9IFtdLCBsb25nUGllY2VzID0gW10sIG1peGVkUGllY2VzID0gW10sXG4gICAgICAgIGksIG1vbSwgbWlucCwgc2hvcnRwLCBsb25ncDtcbiAgICBmb3IgKGkgPSAwOyBpIDwgNzsgaSsrKSB7XG4gICAgICAgIC8vIG1ha2UgdGhlIHJlZ2V4IGlmIHdlIGRvbid0IGhhdmUgaXQgYWxyZWFkeVxuICAgICAgICBtb20gPSBjcmVhdGVVVEMoWzIwMDAsIDFdKS5kYXkoaSk7XG4gICAgICAgIG1pbnAgPSB0aGlzLndlZWtkYXlzTWluKG1vbSwgJycpO1xuICAgICAgICBzaG9ydHAgPSB0aGlzLndlZWtkYXlzU2hvcnQobW9tLCAnJyk7XG4gICAgICAgIGxvbmdwID0gdGhpcy53ZWVrZGF5cyhtb20sICcnKTtcbiAgICAgICAgbWluUGllY2VzLnB1c2gobWlucCk7XG4gICAgICAgIHNob3J0UGllY2VzLnB1c2goc2hvcnRwKTtcbiAgICAgICAgbG9uZ1BpZWNlcy5wdXNoKGxvbmdwKTtcbiAgICAgICAgbWl4ZWRQaWVjZXMucHVzaChtaW5wKTtcbiAgICAgICAgbWl4ZWRQaWVjZXMucHVzaChzaG9ydHApO1xuICAgICAgICBtaXhlZFBpZWNlcy5wdXNoKGxvbmdwKTtcbiAgICB9XG4gICAgLy8gU29ydGluZyBtYWtlcyBzdXJlIGlmIG9uZSB3ZWVrZGF5IChvciBhYmJyKSBpcyBhIHByZWZpeCBvZiBhbm90aGVyIGl0XG4gICAgLy8gd2lsbCBtYXRjaCB0aGUgbG9uZ2VyIHBpZWNlLlxuICAgIG1pblBpZWNlcy5zb3J0KGNtcExlblJldik7XG4gICAgc2hvcnRQaWVjZXMuc29ydChjbXBMZW5SZXYpO1xuICAgIGxvbmdQaWVjZXMuc29ydChjbXBMZW5SZXYpO1xuICAgIG1peGVkUGllY2VzLnNvcnQoY21wTGVuUmV2KTtcbiAgICBmb3IgKGkgPSAwOyBpIDwgNzsgaSsrKSB7XG4gICAgICAgIHNob3J0UGllY2VzW2ldID0gcmVnZXhFc2NhcGUoc2hvcnRQaWVjZXNbaV0pO1xuICAgICAgICBsb25nUGllY2VzW2ldID0gcmVnZXhFc2NhcGUobG9uZ1BpZWNlc1tpXSk7XG4gICAgICAgIG1peGVkUGllY2VzW2ldID0gcmVnZXhFc2NhcGUobWl4ZWRQaWVjZXNbaV0pO1xuICAgIH1cblxuICAgIHRoaXMuX3dlZWtkYXlzUmVnZXggPSBuZXcgUmVnRXhwKCdeKCcgKyBtaXhlZFBpZWNlcy5qb2luKCd8JykgKyAnKScsICdpJyk7XG4gICAgdGhpcy5fd2Vla2RheXNTaG9ydFJlZ2V4ID0gdGhpcy5fd2Vla2RheXNSZWdleDtcbiAgICB0aGlzLl93ZWVrZGF5c01pblJlZ2V4ID0gdGhpcy5fd2Vla2RheXNSZWdleDtcblxuICAgIHRoaXMuX3dlZWtkYXlzU3RyaWN0UmVnZXggPSBuZXcgUmVnRXhwKCdeKCcgKyBsb25nUGllY2VzLmpvaW4oJ3wnKSArICcpJywgJ2knKTtcbiAgICB0aGlzLl93ZWVrZGF5c1Nob3J0U3RyaWN0UmVnZXggPSBuZXcgUmVnRXhwKCdeKCcgKyBzaG9ydFBpZWNlcy5qb2luKCd8JykgKyAnKScsICdpJyk7XG4gICAgdGhpcy5fd2Vla2RheXNNaW5TdHJpY3RSZWdleCA9IG5ldyBSZWdFeHAoJ14oJyArIG1pblBpZWNlcy5qb2luKCd8JykgKyAnKScsICdpJyk7XG59XG5cbi8vIEZPUk1BVFRJTkdcblxuZnVuY3Rpb24gaEZvcm1hdCgpIHtcbiAgICByZXR1cm4gdGhpcy5ob3VycygpICUgMTIgfHwgMTI7XG59XG5cbmZ1bmN0aW9uIGtGb3JtYXQoKSB7XG4gICAgcmV0dXJuIHRoaXMuaG91cnMoKSB8fCAyNDtcbn1cblxuYWRkRm9ybWF0VG9rZW4oJ0gnLCBbJ0hIJywgMl0sIDAsICdob3VyJyk7XG5hZGRGb3JtYXRUb2tlbignaCcsIFsnaGgnLCAyXSwgMCwgaEZvcm1hdCk7XG5hZGRGb3JtYXRUb2tlbignaycsIFsna2snLCAyXSwgMCwga0Zvcm1hdCk7XG5cbmFkZEZvcm1hdFRva2VuKCdobW0nLCAwLCAwLCBmdW5jdGlvbiAoKSB7XG4gICAgcmV0dXJuICcnICsgaEZvcm1hdC5hcHBseSh0aGlzKSArIHplcm9GaWxsKHRoaXMubWludXRlcygpLCAyKTtcbn0pO1xuXG5hZGRGb3JtYXRUb2tlbignaG1tc3MnLCAwLCAwLCBmdW5jdGlvbiAoKSB7XG4gICAgcmV0dXJuICcnICsgaEZvcm1hdC5hcHBseSh0aGlzKSArIHplcm9GaWxsKHRoaXMubWludXRlcygpLCAyKSArXG4gICAgICAgIHplcm9GaWxsKHRoaXMuc2Vjb25kcygpLCAyKTtcbn0pO1xuXG5hZGRGb3JtYXRUb2tlbignSG1tJywgMCwgMCwgZnVuY3Rpb24gKCkge1xuICAgIHJldHVybiAnJyArIHRoaXMuaG91cnMoKSArIHplcm9GaWxsKHRoaXMubWludXRlcygpLCAyKTtcbn0pO1xuXG5hZGRGb3JtYXRUb2tlbignSG1tc3MnLCAwLCAwLCBmdW5jdGlvbiAoKSB7XG4gICAgcmV0dXJuICcnICsgdGhpcy5ob3VycygpICsgemVyb0ZpbGwodGhpcy5taW51dGVzKCksIDIpICtcbiAgICAgICAgemVyb0ZpbGwodGhpcy5zZWNvbmRzKCksIDIpO1xufSk7XG5cbmZ1bmN0aW9uIG1lcmlkaWVtICh0b2tlbiwgbG93ZXJjYXNlKSB7XG4gICAgYWRkRm9ybWF0VG9rZW4odG9rZW4sIDAsIDAsIGZ1bmN0aW9uICgpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMubG9jYWxlRGF0YSgpLm1lcmlkaWVtKHRoaXMuaG91cnMoKSwgdGhpcy5taW51dGVzKCksIGxvd2VyY2FzZSk7XG4gICAgfSk7XG59XG5cbm1lcmlkaWVtKCdhJywgdHJ1ZSk7XG5tZXJpZGllbSgnQScsIGZhbHNlKTtcblxuLy8gQUxJQVNFU1xuXG5hZGRVbml0QWxpYXMoJ2hvdXInLCAnaCcpO1xuXG4vLyBQUklPUklUWVxuYWRkVW5pdFByaW9yaXR5KCdob3VyJywgMTMpO1xuXG4vLyBQQVJTSU5HXG5cbmZ1bmN0aW9uIG1hdGNoTWVyaWRpZW0gKGlzU3RyaWN0LCBsb2NhbGUpIHtcbiAgICByZXR1cm4gbG9jYWxlLl9tZXJpZGllbVBhcnNlO1xufVxuXG5hZGRSZWdleFRva2VuKCdhJywgIG1hdGNoTWVyaWRpZW0pO1xuYWRkUmVnZXhUb2tlbignQScsICBtYXRjaE1lcmlkaWVtKTtcbmFkZFJlZ2V4VG9rZW4oJ0gnLCAgbWF0Y2gxdG8yKTtcbmFkZFJlZ2V4VG9rZW4oJ2gnLCAgbWF0Y2gxdG8yKTtcbmFkZFJlZ2V4VG9rZW4oJ2snLCAgbWF0Y2gxdG8yKTtcbmFkZFJlZ2V4VG9rZW4oJ0hIJywgbWF0Y2gxdG8yLCBtYXRjaDIpO1xuYWRkUmVnZXhUb2tlbignaGgnLCBtYXRjaDF0bzIsIG1hdGNoMik7XG5hZGRSZWdleFRva2VuKCdraycsIG1hdGNoMXRvMiwgbWF0Y2gyKTtcblxuYWRkUmVnZXhUb2tlbignaG1tJywgbWF0Y2gzdG80KTtcbmFkZFJlZ2V4VG9rZW4oJ2htbXNzJywgbWF0Y2g1dG82KTtcbmFkZFJlZ2V4VG9rZW4oJ0htbScsIG1hdGNoM3RvNCk7XG5hZGRSZWdleFRva2VuKCdIbW1zcycsIG1hdGNoNXRvNik7XG5cbmFkZFBhcnNlVG9rZW4oWydIJywgJ0hIJ10sIEhPVVIpO1xuYWRkUGFyc2VUb2tlbihbJ2snLCAna2snXSwgZnVuY3Rpb24gKGlucHV0LCBhcnJheSwgY29uZmlnKSB7XG4gICAgdmFyIGtJbnB1dCA9IHRvSW50KGlucHV0KTtcbiAgICBhcnJheVtIT1VSXSA9IGtJbnB1dCA9PT0gMjQgPyAwIDoga0lucHV0O1xufSk7XG5hZGRQYXJzZVRva2VuKFsnYScsICdBJ10sIGZ1bmN0aW9uIChpbnB1dCwgYXJyYXksIGNvbmZpZykge1xuICAgIGNvbmZpZy5faXNQbSA9IGNvbmZpZy5fbG9jYWxlLmlzUE0oaW5wdXQpO1xuICAgIGNvbmZpZy5fbWVyaWRpZW0gPSBpbnB1dDtcbn0pO1xuYWRkUGFyc2VUb2tlbihbJ2gnLCAnaGgnXSwgZnVuY3Rpb24gKGlucHV0LCBhcnJheSwgY29uZmlnKSB7XG4gICAgYXJyYXlbSE9VUl0gPSB0b0ludChpbnB1dCk7XG4gICAgZ2V0UGFyc2luZ0ZsYWdzKGNvbmZpZykuYmlnSG91ciA9IHRydWU7XG59KTtcbmFkZFBhcnNlVG9rZW4oJ2htbScsIGZ1bmN0aW9uIChpbnB1dCwgYXJyYXksIGNvbmZpZykge1xuICAgIHZhciBwb3MgPSBpbnB1dC5sZW5ndGggLSAyO1xuICAgIGFycmF5W0hPVVJdID0gdG9JbnQoaW5wdXQuc3Vic3RyKDAsIHBvcykpO1xuICAgIGFycmF5W01JTlVURV0gPSB0b0ludChpbnB1dC5zdWJzdHIocG9zKSk7XG4gICAgZ2V0UGFyc2luZ0ZsYWdzKGNvbmZpZykuYmlnSG91ciA9IHRydWU7XG59KTtcbmFkZFBhcnNlVG9rZW4oJ2htbXNzJywgZnVuY3Rpb24gKGlucHV0LCBhcnJheSwgY29uZmlnKSB7XG4gICAgdmFyIHBvczEgPSBpbnB1dC5sZW5ndGggLSA0O1xuICAgIHZhciBwb3MyID0gaW5wdXQubGVuZ3RoIC0gMjtcbiAgICBhcnJheVtIT1VSXSA9IHRvSW50KGlucHV0LnN1YnN0cigwLCBwb3MxKSk7XG4gICAgYXJyYXlbTUlOVVRFXSA9IHRvSW50KGlucHV0LnN1YnN0cihwb3MxLCAyKSk7XG4gICAgYXJyYXlbU0VDT05EXSA9IHRvSW50KGlucHV0LnN1YnN0cihwb3MyKSk7XG4gICAgZ2V0UGFyc2luZ0ZsYWdzKGNvbmZpZykuYmlnSG91ciA9IHRydWU7XG59KTtcbmFkZFBhcnNlVG9rZW4oJ0htbScsIGZ1bmN0aW9uIChpbnB1dCwgYXJyYXksIGNvbmZpZykge1xuICAgIHZhciBwb3MgPSBpbnB1dC5sZW5ndGggLSAyO1xuICAgIGFycmF5W0hPVVJdID0gdG9JbnQoaW5wdXQuc3Vic3RyKDAsIHBvcykpO1xuICAgIGFycmF5W01JTlVURV0gPSB0b0ludChpbnB1dC5zdWJzdHIocG9zKSk7XG59KTtcbmFkZFBhcnNlVG9rZW4oJ0htbXNzJywgZnVuY3Rpb24gKGlucHV0LCBhcnJheSwgY29uZmlnKSB7XG4gICAgdmFyIHBvczEgPSBpbnB1dC5sZW5ndGggLSA0O1xuICAgIHZhciBwb3MyID0gaW5wdXQubGVuZ3RoIC0gMjtcbiAgICBhcnJheVtIT1VSXSA9IHRvSW50KGlucHV0LnN1YnN0cigwLCBwb3MxKSk7XG4gICAgYXJyYXlbTUlOVVRFXSA9IHRvSW50KGlucHV0LnN1YnN0cihwb3MxLCAyKSk7XG4gICAgYXJyYXlbU0VDT05EXSA9IHRvSW50KGlucHV0LnN1YnN0cihwb3MyKSk7XG59KTtcblxuLy8gTE9DQUxFU1xuXG5mdW5jdGlvbiBsb2NhbGVJc1BNIChpbnB1dCkge1xuICAgIC8vIElFOCBRdWlya3MgTW9kZSAmIElFNyBTdGFuZGFyZHMgTW9kZSBkbyBub3QgYWxsb3cgYWNjZXNzaW5nIHN0cmluZ3MgbGlrZSBhcnJheXNcbiAgICAvLyBVc2luZyBjaGFyQXQgc2hvdWxkIGJlIG1vcmUgY29tcGF0aWJsZS5cbiAgICByZXR1cm4gKChpbnB1dCArICcnKS50b0xvd2VyQ2FzZSgpLmNoYXJBdCgwKSA9PT0gJ3AnKTtcbn1cblxudmFyIGRlZmF1bHRMb2NhbGVNZXJpZGllbVBhcnNlID0gL1thcF1cXC4/bT9cXC4/L2k7XG5mdW5jdGlvbiBsb2NhbGVNZXJpZGllbSAoaG91cnMsIG1pbnV0ZXMsIGlzTG93ZXIpIHtcbiAgICBpZiAoaG91cnMgPiAxMSkge1xuICAgICAgICByZXR1cm4gaXNMb3dlciA/ICdwbScgOiAnUE0nO1xuICAgIH0gZWxzZSB7XG4gICAgICAgIHJldHVybiBpc0xvd2VyID8gJ2FtJyA6ICdBTSc7XG4gICAgfVxufVxuXG5cbi8vIE1PTUVOVFNcblxuLy8gU2V0dGluZyB0aGUgaG91ciBzaG91bGQga2VlcCB0aGUgdGltZSwgYmVjYXVzZSB0aGUgdXNlciBleHBsaWNpdGx5XG4vLyBzcGVjaWZpZWQgd2hpY2ggaG91ciBoZSB3YW50cy4gU28gdHJ5aW5nIHRvIG1haW50YWluIHRoZSBzYW1lIGhvdXIgKGluXG4vLyBhIG5ldyB0aW1lem9uZSkgbWFrZXMgc2Vuc2UuIEFkZGluZy9zdWJ0cmFjdGluZyBob3VycyBkb2VzIG5vdCBmb2xsb3dcbi8vIHRoaXMgcnVsZS5cbnZhciBnZXRTZXRIb3VyID0gbWFrZUdldFNldCgnSG91cnMnLCB0cnVlKTtcblxuLy8gbW9udGhzXG4vLyB3ZWVrXG4vLyB3ZWVrZGF5c1xuLy8gbWVyaWRpZW1cbnZhciBiYXNlQ29uZmlnID0ge1xuICAgIGNhbGVuZGFyOiBkZWZhdWx0Q2FsZW5kYXIsXG4gICAgbG9uZ0RhdGVGb3JtYXQ6IGRlZmF1bHRMb25nRGF0ZUZvcm1hdCxcbiAgICBpbnZhbGlkRGF0ZTogZGVmYXVsdEludmFsaWREYXRlLFxuICAgIG9yZGluYWw6IGRlZmF1bHRPcmRpbmFsLFxuICAgIGRheU9mTW9udGhPcmRpbmFsUGFyc2U6IGRlZmF1bHREYXlPZk1vbnRoT3JkaW5hbFBhcnNlLFxuICAgIHJlbGF0aXZlVGltZTogZGVmYXVsdFJlbGF0aXZlVGltZSxcblxuICAgIG1vbnRoczogZGVmYXVsdExvY2FsZU1vbnRocyxcbiAgICBtb250aHNTaG9ydDogZGVmYXVsdExvY2FsZU1vbnRoc1Nob3J0LFxuXG4gICAgd2VlazogZGVmYXVsdExvY2FsZVdlZWssXG5cbiAgICB3ZWVrZGF5czogZGVmYXVsdExvY2FsZVdlZWtkYXlzLFxuICAgIHdlZWtkYXlzTWluOiBkZWZhdWx0TG9jYWxlV2Vla2RheXNNaW4sXG4gICAgd2Vla2RheXNTaG9ydDogZGVmYXVsdExvY2FsZVdlZWtkYXlzU2hvcnQsXG5cbiAgICBtZXJpZGllbVBhcnNlOiBkZWZhdWx0TG9jYWxlTWVyaWRpZW1QYXJzZVxufTtcblxuLy8gaW50ZXJuYWwgc3RvcmFnZSBmb3IgbG9jYWxlIGNvbmZpZyBmaWxlc1xudmFyIGxvY2FsZXMgPSB7fTtcbnZhciBsb2NhbGVGYW1pbGllcyA9IHt9O1xudmFyIGdsb2JhbExvY2FsZTtcblxuZnVuY3Rpb24gbm9ybWFsaXplTG9jYWxlKGtleSkge1xuICAgIHJldHVybiBrZXkgPyBrZXkudG9Mb3dlckNhc2UoKS5yZXBsYWNlKCdfJywgJy0nKSA6IGtleTtcbn1cblxuLy8gcGljayB0aGUgbG9jYWxlIGZyb20gdGhlIGFycmF5XG4vLyB0cnkgWydlbi1hdScsICdlbi1nYiddIGFzICdlbi1hdScsICdlbi1nYicsICdlbicsIGFzIGluIG1vdmUgdGhyb3VnaCB0aGUgbGlzdCB0cnlpbmcgZWFjaFxuLy8gc3Vic3RyaW5nIGZyb20gbW9zdCBzcGVjaWZpYyB0byBsZWFzdCwgYnV0IG1vdmUgdG8gdGhlIG5leHQgYXJyYXkgaXRlbSBpZiBpdCdzIGEgbW9yZSBzcGVjaWZpYyB2YXJpYW50IHRoYW4gdGhlIGN1cnJlbnQgcm9vdFxuZnVuY3Rpb24gY2hvb3NlTG9jYWxlKG5hbWVzKSB7XG4gICAgdmFyIGkgPSAwLCBqLCBuZXh0LCBsb2NhbGUsIHNwbGl0O1xuXG4gICAgd2hpbGUgKGkgPCBuYW1lcy5sZW5ndGgpIHtcbiAgICAgICAgc3BsaXQgPSBub3JtYWxpemVMb2NhbGUobmFtZXNbaV0pLnNwbGl0KCctJyk7XG4gICAgICAgIGogPSBzcGxpdC5sZW5ndGg7XG4gICAgICAgIG5leHQgPSBub3JtYWxpemVMb2NhbGUobmFtZXNbaSArIDFdKTtcbiAgICAgICAgbmV4dCA9IG5leHQgPyBuZXh0LnNwbGl0KCctJykgOiBudWxsO1xuICAgICAgICB3aGlsZSAoaiA+IDApIHtcbiAgICAgICAgICAgIGxvY2FsZSA9IGxvYWRMb2NhbGUoc3BsaXQuc2xpY2UoMCwgaikuam9pbignLScpKTtcbiAgICAgICAgICAgIGlmIChsb2NhbGUpIHtcbiAgICAgICAgICAgICAgICByZXR1cm4gbG9jYWxlO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgaWYgKG5leHQgJiYgbmV4dC5sZW5ndGggPj0gaiAmJiBjb21wYXJlQXJyYXlzKHNwbGl0LCBuZXh0LCB0cnVlKSA+PSBqIC0gMSkge1xuICAgICAgICAgICAgICAgIC8vdGhlIG5leHQgYXJyYXkgaXRlbSBpcyBiZXR0ZXIgdGhhbiBhIHNoYWxsb3dlciBzdWJzdHJpbmcgb2YgdGhpcyBvbmVcbiAgICAgICAgICAgICAgICBicmVhaztcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGotLTtcbiAgICAgICAgfVxuICAgICAgICBpKys7XG4gICAgfVxuICAgIHJldHVybiBudWxsO1xufVxuXG5mdW5jdGlvbiBsb2FkTG9jYWxlKG5hbWUpIHtcbiAgICB2YXIgb2xkTG9jYWxlID0gbnVsbDtcbiAgICAvLyBUT0RPOiBGaW5kIGEgYmV0dGVyIHdheSB0byByZWdpc3RlciBhbmQgbG9hZCBhbGwgdGhlIGxvY2FsZXMgaW4gTm9kZVxuICAgIGlmICghbG9jYWxlc1tuYW1lXSAmJiAodHlwZW9mIG1vZHVsZSAhPT0gJ3VuZGVmaW5lZCcpICYmXG4gICAgICAgICAgICBtb2R1bGUgJiYgbW9kdWxlLmV4cG9ydHMpIHtcbiAgICAgICAgdHJ5IHtcbiAgICAgICAgICAgIG9sZExvY2FsZSA9IGdsb2JhbExvY2FsZS5fYWJicjtcbiAgICAgICAgICAgIHZhciBhbGlhc2VkUmVxdWlyZSA9IHJlcXVpcmU7XG4gICAgICAgICAgICBhbGlhc2VkUmVxdWlyZSgnLi9sb2NhbGUvJyArIG5hbWUpO1xuICAgICAgICAgICAgZ2V0U2V0R2xvYmFsTG9jYWxlKG9sZExvY2FsZSk7XG4gICAgICAgIH0gY2F0Y2ggKGUpIHt9XG4gICAgfVxuICAgIHJldHVybiBsb2NhbGVzW25hbWVdO1xufVxuXG4vLyBUaGlzIGZ1bmN0aW9uIHdpbGwgbG9hZCBsb2NhbGUgYW5kIHRoZW4gc2V0IHRoZSBnbG9iYWwgbG9jYWxlLiAgSWZcbi8vIG5vIGFyZ3VtZW50cyBhcmUgcGFzc2VkIGluLCBpdCB3aWxsIHNpbXBseSByZXR1cm4gdGhlIGN1cnJlbnQgZ2xvYmFsXG4vLyBsb2NhbGUga2V5LlxuZnVuY3Rpb24gZ2V0U2V0R2xvYmFsTG9jYWxlIChrZXksIHZhbHVlcykge1xuICAgIHZhciBkYXRhO1xuICAgIGlmIChrZXkpIHtcbiAgICAgICAgaWYgKGlzVW5kZWZpbmVkKHZhbHVlcykpIHtcbiAgICAgICAgICAgIGRhdGEgPSBnZXRMb2NhbGUoa2V5KTtcbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgIGRhdGEgPSBkZWZpbmVMb2NhbGUoa2V5LCB2YWx1ZXMpO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKGRhdGEpIHtcbiAgICAgICAgICAgIC8vIG1vbWVudC5kdXJhdGlvbi5fbG9jYWxlID0gbW9tZW50Ll9sb2NhbGUgPSBkYXRhO1xuICAgICAgICAgICAgZ2xvYmFsTG9jYWxlID0gZGF0YTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIHJldHVybiBnbG9iYWxMb2NhbGUuX2FiYnI7XG59XG5cbmZ1bmN0aW9uIGRlZmluZUxvY2FsZSAobmFtZSwgY29uZmlnKSB7XG4gICAgaWYgKGNvbmZpZyAhPT0gbnVsbCkge1xuICAgICAgICB2YXIgcGFyZW50Q29uZmlnID0gYmFzZUNvbmZpZztcbiAgICAgICAgY29uZmlnLmFiYnIgPSBuYW1lO1xuICAgICAgICBpZiAobG9jYWxlc1tuYW1lXSAhPSBudWxsKSB7XG4gICAgICAgICAgICBkZXByZWNhdGVTaW1wbGUoJ2RlZmluZUxvY2FsZU92ZXJyaWRlJyxcbiAgICAgICAgICAgICAgICAgICAgJ3VzZSBtb21lbnQudXBkYXRlTG9jYWxlKGxvY2FsZU5hbWUsIGNvbmZpZykgdG8gY2hhbmdlICcgK1xuICAgICAgICAgICAgICAgICAgICAnYW4gZXhpc3RpbmcgbG9jYWxlLiBtb21lbnQuZGVmaW5lTG9jYWxlKGxvY2FsZU5hbWUsICcgK1xuICAgICAgICAgICAgICAgICAgICAnY29uZmlnKSBzaG91bGQgb25seSBiZSB1c2VkIGZvciBjcmVhdGluZyBhIG5ldyBsb2NhbGUgJyArXG4gICAgICAgICAgICAgICAgICAgICdTZWUgaHR0cDovL21vbWVudGpzLmNvbS9ndWlkZXMvIy93YXJuaW5ncy9kZWZpbmUtbG9jYWxlLyBmb3IgbW9yZSBpbmZvLicpO1xuICAgICAgICAgICAgcGFyZW50Q29uZmlnID0gbG9jYWxlc1tuYW1lXS5fY29uZmlnO1xuICAgICAgICB9IGVsc2UgaWYgKGNvbmZpZy5wYXJlbnRMb2NhbGUgIT0gbnVsbCkge1xuICAgICAgICAgICAgaWYgKGxvY2FsZXNbY29uZmlnLnBhcmVudExvY2FsZV0gIT0gbnVsbCkge1xuICAgICAgICAgICAgICAgIHBhcmVudENvbmZpZyA9IGxvY2FsZXNbY29uZmlnLnBhcmVudExvY2FsZV0uX2NvbmZpZztcbiAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgaWYgKCFsb2NhbGVGYW1pbGllc1tjb25maWcucGFyZW50TG9jYWxlXSkge1xuICAgICAgICAgICAgICAgICAgICBsb2NhbGVGYW1pbGllc1tjb25maWcucGFyZW50TG9jYWxlXSA9IFtdO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICBsb2NhbGVGYW1pbGllc1tjb25maWcucGFyZW50TG9jYWxlXS5wdXNoKHtcbiAgICAgICAgICAgICAgICAgICAgbmFtZTogbmFtZSxcbiAgICAgICAgICAgICAgICAgICAgY29uZmlnOiBjb25maWdcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICByZXR1cm4gbnVsbDtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgICBsb2NhbGVzW25hbWVdID0gbmV3IExvY2FsZShtZXJnZUNvbmZpZ3MocGFyZW50Q29uZmlnLCBjb25maWcpKTtcblxuICAgICAgICBpZiAobG9jYWxlRmFtaWxpZXNbbmFtZV0pIHtcbiAgICAgICAgICAgIGxvY2FsZUZhbWlsaWVzW25hbWVdLmZvckVhY2goZnVuY3Rpb24gKHgpIHtcbiAgICAgICAgICAgICAgICBkZWZpbmVMb2NhbGUoeC5uYW1lLCB4LmNvbmZpZyk7XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIGJhY2t3YXJkcyBjb21wYXQgZm9yIG5vdzogYWxzbyBzZXQgdGhlIGxvY2FsZVxuICAgICAgICAvLyBtYWtlIHN1cmUgd2Ugc2V0IHRoZSBsb2NhbGUgQUZURVIgYWxsIGNoaWxkIGxvY2FsZXMgaGF2ZSBiZWVuXG4gICAgICAgIC8vIGNyZWF0ZWQsIHNvIHdlIHdvbid0IGVuZCB1cCB3aXRoIHRoZSBjaGlsZCBsb2NhbGUgc2V0LlxuICAgICAgICBnZXRTZXRHbG9iYWxMb2NhbGUobmFtZSk7XG5cblxuICAgICAgICByZXR1cm4gbG9jYWxlc1tuYW1lXTtcbiAgICB9IGVsc2Uge1xuICAgICAgICAvLyB1c2VmdWwgZm9yIHRlc3RpbmdcbiAgICAgICAgZGVsZXRlIGxvY2FsZXNbbmFtZV07XG4gICAgICAgIHJldHVybiBudWxsO1xuICAgIH1cbn1cblxuZnVuY3Rpb24gdXBkYXRlTG9jYWxlKG5hbWUsIGNvbmZpZykge1xuICAgIGlmIChjb25maWcgIT0gbnVsbCkge1xuICAgICAgICB2YXIgbG9jYWxlLCB0bXBMb2NhbGUsIHBhcmVudENvbmZpZyA9IGJhc2VDb25maWc7XG4gICAgICAgIC8vIE1FUkdFXG4gICAgICAgIHRtcExvY2FsZSA9IGxvYWRMb2NhbGUobmFtZSk7XG4gICAgICAgIGlmICh0bXBMb2NhbGUgIT0gbnVsbCkge1xuICAgICAgICAgICAgcGFyZW50Q29uZmlnID0gdG1wTG9jYWxlLl9jb25maWc7XG4gICAgICAgIH1cbiAgICAgICAgY29uZmlnID0gbWVyZ2VDb25maWdzKHBhcmVudENvbmZpZywgY29uZmlnKTtcbiAgICAgICAgbG9jYWxlID0gbmV3IExvY2FsZShjb25maWcpO1xuICAgICAgICBsb2NhbGUucGFyZW50TG9jYWxlID0gbG9jYWxlc1tuYW1lXTtcbiAgICAgICAgbG9jYWxlc1tuYW1lXSA9IGxvY2FsZTtcblxuICAgICAgICAvLyBiYWNrd2FyZHMgY29tcGF0IGZvciBub3c6IGFsc28gc2V0IHRoZSBsb2NhbGVcbiAgICAgICAgZ2V0U2V0R2xvYmFsTG9jYWxlKG5hbWUpO1xuICAgIH0gZWxzZSB7XG4gICAgICAgIC8vIHBhc3MgbnVsbCBmb3IgY29uZmlnIHRvIHVudXBkYXRlLCB1c2VmdWwgZm9yIHRlc3RzXG4gICAgICAgIGlmIChsb2NhbGVzW25hbWVdICE9IG51bGwpIHtcbiAgICAgICAgICAgIGlmIChsb2NhbGVzW25hbWVdLnBhcmVudExvY2FsZSAhPSBudWxsKSB7XG4gICAgICAgICAgICAgICAgbG9jYWxlc1tuYW1lXSA9IGxvY2FsZXNbbmFtZV0ucGFyZW50TG9jYWxlO1xuICAgICAgICAgICAgfSBlbHNlIGlmIChsb2NhbGVzW25hbWVdICE9IG51bGwpIHtcbiAgICAgICAgICAgICAgICBkZWxldGUgbG9jYWxlc1tuYW1lXTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgIH1cbiAgICByZXR1cm4gbG9jYWxlc1tuYW1lXTtcbn1cblxuLy8gcmV0dXJucyBsb2NhbGUgZGF0YVxuZnVuY3Rpb24gZ2V0TG9jYWxlIChrZXkpIHtcbiAgICB2YXIgbG9jYWxlO1xuXG4gICAgaWYgKGtleSAmJiBrZXkuX2xvY2FsZSAmJiBrZXkuX2xvY2FsZS5fYWJicikge1xuICAgICAgICBrZXkgPSBrZXkuX2xvY2FsZS5fYWJicjtcbiAgICB9XG5cbiAgICBpZiAoIWtleSkge1xuICAgICAgICByZXR1cm4gZ2xvYmFsTG9jYWxlO1xuICAgIH1cblxuICAgIGlmICghaXNBcnJheShrZXkpKSB7XG4gICAgICAgIC8vc2hvcnQtY2lyY3VpdCBldmVyeXRoaW5nIGVsc2VcbiAgICAgICAgbG9jYWxlID0gbG9hZExvY2FsZShrZXkpO1xuICAgICAgICBpZiAobG9jYWxlKSB7XG4gICAgICAgICAgICByZXR1cm4gbG9jYWxlO1xuICAgICAgICB9XG4gICAgICAgIGtleSA9IFtrZXldO1xuICAgIH1cblxuICAgIHJldHVybiBjaG9vc2VMb2NhbGUoa2V5KTtcbn1cblxuZnVuY3Rpb24gbGlzdExvY2FsZXMoKSB7XG4gICAgcmV0dXJuIGtleXMobG9jYWxlcyk7XG59XG5cbmZ1bmN0aW9uIGNoZWNrT3ZlcmZsb3cgKG0pIHtcbiAgICB2YXIgb3ZlcmZsb3c7XG4gICAgdmFyIGEgPSBtLl9hO1xuXG4gICAgaWYgKGEgJiYgZ2V0UGFyc2luZ0ZsYWdzKG0pLm92ZXJmbG93ID09PSAtMikge1xuICAgICAgICBvdmVyZmxvdyA9XG4gICAgICAgICAgICBhW01PTlRIXSAgICAgICA8IDAgfHwgYVtNT05USF0gICAgICAgPiAxMSAgPyBNT05USCA6XG4gICAgICAgICAgICBhW0RBVEVdICAgICAgICA8IDEgfHwgYVtEQVRFXSAgICAgICAgPiBkYXlzSW5Nb250aChhW1lFQVJdLCBhW01PTlRIXSkgPyBEQVRFIDpcbiAgICAgICAgICAgIGFbSE9VUl0gICAgICAgIDwgMCB8fCBhW0hPVVJdICAgICAgICA+IDI0IHx8IChhW0hPVVJdID09PSAyNCAmJiAoYVtNSU5VVEVdICE9PSAwIHx8IGFbU0VDT05EXSAhPT0gMCB8fCBhW01JTExJU0VDT05EXSAhPT0gMCkpID8gSE9VUiA6XG4gICAgICAgICAgICBhW01JTlVURV0gICAgICA8IDAgfHwgYVtNSU5VVEVdICAgICAgPiA1OSAgPyBNSU5VVEUgOlxuICAgICAgICAgICAgYVtTRUNPTkRdICAgICAgPCAwIHx8IGFbU0VDT05EXSAgICAgID4gNTkgID8gU0VDT05EIDpcbiAgICAgICAgICAgIGFbTUlMTElTRUNPTkRdIDwgMCB8fCBhW01JTExJU0VDT05EXSA+IDk5OSA/IE1JTExJU0VDT05EIDpcbiAgICAgICAgICAgIC0xO1xuXG4gICAgICAgIGlmIChnZXRQYXJzaW5nRmxhZ3MobSkuX292ZXJmbG93RGF5T2ZZZWFyICYmIChvdmVyZmxvdyA8IFlFQVIgfHwgb3ZlcmZsb3cgPiBEQVRFKSkge1xuICAgICAgICAgICAgb3ZlcmZsb3cgPSBEQVRFO1xuICAgICAgICB9XG4gICAgICAgIGlmIChnZXRQYXJzaW5nRmxhZ3MobSkuX292ZXJmbG93V2Vla3MgJiYgb3ZlcmZsb3cgPT09IC0xKSB7XG4gICAgICAgICAgICBvdmVyZmxvdyA9IFdFRUs7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKGdldFBhcnNpbmdGbGFncyhtKS5fb3ZlcmZsb3dXZWVrZGF5ICYmIG92ZXJmbG93ID09PSAtMSkge1xuICAgICAgICAgICAgb3ZlcmZsb3cgPSBXRUVLREFZO1xuICAgICAgICB9XG5cbiAgICAgICAgZ2V0UGFyc2luZ0ZsYWdzKG0pLm92ZXJmbG93ID0gb3ZlcmZsb3c7XG4gICAgfVxuXG4gICAgcmV0dXJuIG07XG59XG5cbi8vIFBpY2sgdGhlIGZpcnN0IGRlZmluZWQgb2YgdHdvIG9yIHRocmVlIGFyZ3VtZW50cy5cbmZ1bmN0aW9uIGRlZmF1bHRzKGEsIGIsIGMpIHtcbiAgICBpZiAoYSAhPSBudWxsKSB7XG4gICAgICAgIHJldHVybiBhO1xuICAgIH1cbiAgICBpZiAoYiAhPSBudWxsKSB7XG4gICAgICAgIHJldHVybiBiO1xuICAgIH1cbiAgICByZXR1cm4gYztcbn1cblxuZnVuY3Rpb24gY3VycmVudERhdGVBcnJheShjb25maWcpIHtcbiAgICAvLyBob29rcyBpcyBhY3R1YWxseSB0aGUgZXhwb3J0ZWQgbW9tZW50IG9iamVjdFxuICAgIHZhciBub3dWYWx1ZSA9IG5ldyBEYXRlKGhvb2tzLm5vdygpKTtcbiAgICBpZiAoY29uZmlnLl91c2VVVEMpIHtcbiAgICAgICAgcmV0dXJuIFtub3dWYWx1ZS5nZXRVVENGdWxsWWVhcigpLCBub3dWYWx1ZS5nZXRVVENNb250aCgpLCBub3dWYWx1ZS5nZXRVVENEYXRlKCldO1xuICAgIH1cbiAgICByZXR1cm4gW25vd1ZhbHVlLmdldEZ1bGxZZWFyKCksIG5vd1ZhbHVlLmdldE1vbnRoKCksIG5vd1ZhbHVlLmdldERhdGUoKV07XG59XG5cbi8vIGNvbnZlcnQgYW4gYXJyYXkgdG8gYSBkYXRlLlxuLy8gdGhlIGFycmF5IHNob3VsZCBtaXJyb3IgdGhlIHBhcmFtZXRlcnMgYmVsb3dcbi8vIG5vdGU6IGFsbCB2YWx1ZXMgcGFzdCB0aGUgeWVhciBhcmUgb3B0aW9uYWwgYW5kIHdpbGwgZGVmYXVsdCB0byB0aGUgbG93ZXN0IHBvc3NpYmxlIHZhbHVlLlxuLy8gW3llYXIsIG1vbnRoLCBkYXkgLCBob3VyLCBtaW51dGUsIHNlY29uZCwgbWlsbGlzZWNvbmRdXG5mdW5jdGlvbiBjb25maWdGcm9tQXJyYXkgKGNvbmZpZykge1xuICAgIHZhciBpLCBkYXRlLCBpbnB1dCA9IFtdLCBjdXJyZW50RGF0ZSwgZXhwZWN0ZWRXZWVrZGF5LCB5ZWFyVG9Vc2U7XG5cbiAgICBpZiAoY29uZmlnLl9kKSB7XG4gICAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICBjdXJyZW50RGF0ZSA9IGN1cnJlbnREYXRlQXJyYXkoY29uZmlnKTtcblxuICAgIC8vY29tcHV0ZSBkYXkgb2YgdGhlIHllYXIgZnJvbSB3ZWVrcyBhbmQgd2Vla2RheXNcbiAgICBpZiAoY29uZmlnLl93ICYmIGNvbmZpZy5fYVtEQVRFXSA9PSBudWxsICYmIGNvbmZpZy5fYVtNT05USF0gPT0gbnVsbCkge1xuICAgICAgICBkYXlPZlllYXJGcm9tV2Vla0luZm8oY29uZmlnKTtcbiAgICB9XG5cbiAgICAvL2lmIHRoZSBkYXkgb2YgdGhlIHllYXIgaXMgc2V0LCBmaWd1cmUgb3V0IHdoYXQgaXQgaXNcbiAgICBpZiAoY29uZmlnLl9kYXlPZlllYXIgIT0gbnVsbCkge1xuICAgICAgICB5ZWFyVG9Vc2UgPSBkZWZhdWx0cyhjb25maWcuX2FbWUVBUl0sIGN1cnJlbnREYXRlW1lFQVJdKTtcblxuICAgICAgICBpZiAoY29uZmlnLl9kYXlPZlllYXIgPiBkYXlzSW5ZZWFyKHllYXJUb1VzZSkgfHwgY29uZmlnLl9kYXlPZlllYXIgPT09IDApIHtcbiAgICAgICAgICAgIGdldFBhcnNpbmdGbGFncyhjb25maWcpLl9vdmVyZmxvd0RheU9mWWVhciA9IHRydWU7XG4gICAgICAgIH1cblxuICAgICAgICBkYXRlID0gY3JlYXRlVVRDRGF0ZSh5ZWFyVG9Vc2UsIDAsIGNvbmZpZy5fZGF5T2ZZZWFyKTtcbiAgICAgICAgY29uZmlnLl9hW01PTlRIXSA9IGRhdGUuZ2V0VVRDTW9udGgoKTtcbiAgICAgICAgY29uZmlnLl9hW0RBVEVdID0gZGF0ZS5nZXRVVENEYXRlKCk7XG4gICAgfVxuXG4gICAgLy8gRGVmYXVsdCB0byBjdXJyZW50IGRhdGUuXG4gICAgLy8gKiBpZiBubyB5ZWFyLCBtb250aCwgZGF5IG9mIG1vbnRoIGFyZSBnaXZlbiwgZGVmYXVsdCB0byB0b2RheVxuICAgIC8vICogaWYgZGF5IG9mIG1vbnRoIGlzIGdpdmVuLCBkZWZhdWx0IG1vbnRoIGFuZCB5ZWFyXG4gICAgLy8gKiBpZiBtb250aCBpcyBnaXZlbiwgZGVmYXVsdCBvbmx5IHllYXJcbiAgICAvLyAqIGlmIHllYXIgaXMgZ2l2ZW4sIGRvbid0IGRlZmF1bHQgYW55dGhpbmdcbiAgICBmb3IgKGkgPSAwOyBpIDwgMyAmJiBjb25maWcuX2FbaV0gPT0gbnVsbDsgKytpKSB7XG4gICAgICAgIGNvbmZpZy5fYVtpXSA9IGlucHV0W2ldID0gY3VycmVudERhdGVbaV07XG4gICAgfVxuXG4gICAgLy8gWmVybyBvdXQgd2hhdGV2ZXIgd2FzIG5vdCBkZWZhdWx0ZWQsIGluY2x1ZGluZyB0aW1lXG4gICAgZm9yICg7IGkgPCA3OyBpKyspIHtcbiAgICAgICAgY29uZmlnLl9hW2ldID0gaW5wdXRbaV0gPSAoY29uZmlnLl9hW2ldID09IG51bGwpID8gKGkgPT09IDIgPyAxIDogMCkgOiBjb25maWcuX2FbaV07XG4gICAgfVxuXG4gICAgLy8gQ2hlY2sgZm9yIDI0OjAwOjAwLjAwMFxuICAgIGlmIChjb25maWcuX2FbSE9VUl0gPT09IDI0ICYmXG4gICAgICAgICAgICBjb25maWcuX2FbTUlOVVRFXSA9PT0gMCAmJlxuICAgICAgICAgICAgY29uZmlnLl9hW1NFQ09ORF0gPT09IDAgJiZcbiAgICAgICAgICAgIGNvbmZpZy5fYVtNSUxMSVNFQ09ORF0gPT09IDApIHtcbiAgICAgICAgY29uZmlnLl9uZXh0RGF5ID0gdHJ1ZTtcbiAgICAgICAgY29uZmlnLl9hW0hPVVJdID0gMDtcbiAgICB9XG5cbiAgICBjb25maWcuX2QgPSAoY29uZmlnLl91c2VVVEMgPyBjcmVhdGVVVENEYXRlIDogY3JlYXRlRGF0ZSkuYXBwbHkobnVsbCwgaW5wdXQpO1xuICAgIGV4cGVjdGVkV2Vla2RheSA9IGNvbmZpZy5fdXNlVVRDID8gY29uZmlnLl9kLmdldFVUQ0RheSgpIDogY29uZmlnLl9kLmdldERheSgpO1xuXG4gICAgLy8gQXBwbHkgdGltZXpvbmUgb2Zmc2V0IGZyb20gaW5wdXQuIFRoZSBhY3R1YWwgdXRjT2Zmc2V0IGNhbiBiZSBjaGFuZ2VkXG4gICAgLy8gd2l0aCBwYXJzZVpvbmUuXG4gICAgaWYgKGNvbmZpZy5fdHptICE9IG51bGwpIHtcbiAgICAgICAgY29uZmlnLl9kLnNldFVUQ01pbnV0ZXMoY29uZmlnLl9kLmdldFVUQ01pbnV0ZXMoKSAtIGNvbmZpZy5fdHptKTtcbiAgICB9XG5cbiAgICBpZiAoY29uZmlnLl9uZXh0RGF5KSB7XG4gICAgICAgIGNvbmZpZy5fYVtIT1VSXSA9IDI0O1xuICAgIH1cblxuICAgIC8vIGNoZWNrIGZvciBtaXNtYXRjaGluZyBkYXkgb2Ygd2Vla1xuICAgIGlmIChjb25maWcuX3cgJiYgdHlwZW9mIGNvbmZpZy5fdy5kICE9PSAndW5kZWZpbmVkJyAmJiBjb25maWcuX3cuZCAhPT0gZXhwZWN0ZWRXZWVrZGF5KSB7XG4gICAgICAgIGdldFBhcnNpbmdGbGFncyhjb25maWcpLndlZWtkYXlNaXNtYXRjaCA9IHRydWU7XG4gICAgfVxufVxuXG5mdW5jdGlvbiBkYXlPZlllYXJGcm9tV2Vla0luZm8oY29uZmlnKSB7XG4gICAgdmFyIHcsIHdlZWtZZWFyLCB3ZWVrLCB3ZWVrZGF5LCBkb3csIGRveSwgdGVtcCwgd2Vla2RheU92ZXJmbG93O1xuXG4gICAgdyA9IGNvbmZpZy5fdztcbiAgICBpZiAody5HRyAhPSBudWxsIHx8IHcuVyAhPSBudWxsIHx8IHcuRSAhPSBudWxsKSB7XG4gICAgICAgIGRvdyA9IDE7XG4gICAgICAgIGRveSA9IDQ7XG5cbiAgICAgICAgLy8gVE9ETzogV2UgbmVlZCB0byB0YWtlIHRoZSBjdXJyZW50IGlzb1dlZWtZZWFyLCBidXQgdGhhdCBkZXBlbmRzIG9uXG4gICAgICAgIC8vIGhvdyB3ZSBpbnRlcnByZXQgbm93IChsb2NhbCwgdXRjLCBmaXhlZCBvZmZzZXQpLiBTbyBjcmVhdGVcbiAgICAgICAgLy8gYSBub3cgdmVyc2lvbiBvZiBjdXJyZW50IGNvbmZpZyAodGFrZSBsb2NhbC91dGMvb2Zmc2V0IGZsYWdzLCBhbmRcbiAgICAgICAgLy8gY3JlYXRlIG5vdykuXG4gICAgICAgIHdlZWtZZWFyID0gZGVmYXVsdHMody5HRywgY29uZmlnLl9hW1lFQVJdLCB3ZWVrT2ZZZWFyKGNyZWF0ZUxvY2FsKCksIDEsIDQpLnllYXIpO1xuICAgICAgICB3ZWVrID0gZGVmYXVsdHMody5XLCAxKTtcbiAgICAgICAgd2Vla2RheSA9IGRlZmF1bHRzKHcuRSwgMSk7XG4gICAgICAgIGlmICh3ZWVrZGF5IDwgMSB8fCB3ZWVrZGF5ID4gNykge1xuICAgICAgICAgICAgd2Vla2RheU92ZXJmbG93ID0gdHJ1ZTtcbiAgICAgICAgfVxuICAgIH0gZWxzZSB7XG4gICAgICAgIGRvdyA9IGNvbmZpZy5fbG9jYWxlLl93ZWVrLmRvdztcbiAgICAgICAgZG95ID0gY29uZmlnLl9sb2NhbGUuX3dlZWsuZG95O1xuXG4gICAgICAgIHZhciBjdXJXZWVrID0gd2Vla09mWWVhcihjcmVhdGVMb2NhbCgpLCBkb3csIGRveSk7XG5cbiAgICAgICAgd2Vla1llYXIgPSBkZWZhdWx0cyh3LmdnLCBjb25maWcuX2FbWUVBUl0sIGN1cldlZWsueWVhcik7XG5cbiAgICAgICAgLy8gRGVmYXVsdCB0byBjdXJyZW50IHdlZWsuXG4gICAgICAgIHdlZWsgPSBkZWZhdWx0cyh3LncsIGN1cldlZWsud2Vlayk7XG5cbiAgICAgICAgaWYgKHcuZCAhPSBudWxsKSB7XG4gICAgICAgICAgICAvLyB3ZWVrZGF5IC0tIGxvdyBkYXkgbnVtYmVycyBhcmUgY29uc2lkZXJlZCBuZXh0IHdlZWtcbiAgICAgICAgICAgIHdlZWtkYXkgPSB3LmQ7XG4gICAgICAgICAgICBpZiAod2Vla2RheSA8IDAgfHwgd2Vla2RheSA+IDYpIHtcbiAgICAgICAgICAgICAgICB3ZWVrZGF5T3ZlcmZsb3cgPSB0cnVlO1xuICAgICAgICAgICAgfVxuICAgICAgICB9IGVsc2UgaWYgKHcuZSAhPSBudWxsKSB7XG4gICAgICAgICAgICAvLyBsb2NhbCB3ZWVrZGF5IC0tIGNvdW50aW5nIHN0YXJ0cyBmcm9tIGJlZ2luaW5nIG9mIHdlZWtcbiAgICAgICAgICAgIHdlZWtkYXkgPSB3LmUgKyBkb3c7XG4gICAgICAgICAgICBpZiAody5lIDwgMCB8fCB3LmUgPiA2KSB7XG4gICAgICAgICAgICAgICAgd2Vla2RheU92ZXJmbG93ID0gdHJ1ZTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIC8vIGRlZmF1bHQgdG8gYmVnaW5pbmcgb2Ygd2Vla1xuICAgICAgICAgICAgd2Vla2RheSA9IGRvdztcbiAgICAgICAgfVxuICAgIH1cbiAgICBpZiAod2VlayA8IDEgfHwgd2VlayA+IHdlZWtzSW5ZZWFyKHdlZWtZZWFyLCBkb3csIGRveSkpIHtcbiAgICAgICAgZ2V0UGFyc2luZ0ZsYWdzKGNvbmZpZykuX292ZXJmbG93V2Vla3MgPSB0cnVlO1xuICAgIH0gZWxzZSBpZiAod2Vla2RheU92ZXJmbG93ICE9IG51bGwpIHtcbiAgICAgICAgZ2V0UGFyc2luZ0ZsYWdzKGNvbmZpZykuX292ZXJmbG93V2Vla2RheSA9IHRydWU7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgdGVtcCA9IGRheU9mWWVhckZyb21XZWVrcyh3ZWVrWWVhciwgd2Vlaywgd2Vla2RheSwgZG93LCBkb3kpO1xuICAgICAgICBjb25maWcuX2FbWUVBUl0gPSB0ZW1wLnllYXI7XG4gICAgICAgIGNvbmZpZy5fZGF5T2ZZZWFyID0gdGVtcC5kYXlPZlllYXI7XG4gICAgfVxufVxuXG4vLyBpc28gODYwMSByZWdleFxuLy8gMDAwMC0wMC0wMCAwMDAwLVcwMCBvciAwMDAwLVcwMC0wICsgVCArIDAwIG9yIDAwOjAwIG9yIDAwOjAwOjAwIG9yIDAwOjAwOjAwLjAwMCArICswMDowMCBvciArMDAwMCBvciArMDApXG52YXIgZXh0ZW5kZWRJc29SZWdleCA9IC9eXFxzKigoPzpbKy1dXFxkezZ9fFxcZHs0fSktKD86XFxkXFxkLVxcZFxcZHxXXFxkXFxkLVxcZHxXXFxkXFxkfFxcZFxcZFxcZHxcXGRcXGQpKSg/OihUfCApKFxcZFxcZCg/OjpcXGRcXGQoPzo6XFxkXFxkKD86Wy4sXVxcZCspPyk/KT8pKFtcXCtcXC1dXFxkXFxkKD86Oj9cXGRcXGQpP3xcXHMqWik/KT8kLztcbnZhciBiYXNpY0lzb1JlZ2V4ID0gL15cXHMqKCg/OlsrLV1cXGR7Nn18XFxkezR9KSg/OlxcZFxcZFxcZFxcZHxXXFxkXFxkXFxkfFdcXGRcXGR8XFxkXFxkXFxkfFxcZFxcZCkpKD86KFR8ICkoXFxkXFxkKD86XFxkXFxkKD86XFxkXFxkKD86Wy4sXVxcZCspPyk/KT8pKFtcXCtcXC1dXFxkXFxkKD86Oj9cXGRcXGQpP3xcXHMqWik/KT8kLztcblxudmFyIHR6UmVnZXggPSAvWnxbKy1dXFxkXFxkKD86Oj9cXGRcXGQpPy87XG5cbnZhciBpc29EYXRlcyA9IFtcbiAgICBbJ1lZWVlZWS1NTS1ERCcsIC9bKy1dXFxkezZ9LVxcZFxcZC1cXGRcXGQvXSxcbiAgICBbJ1lZWVktTU0tREQnLCAvXFxkezR9LVxcZFxcZC1cXGRcXGQvXSxcbiAgICBbJ0dHR0ctW1ddV1ctRScsIC9cXGR7NH0tV1xcZFxcZC1cXGQvXSxcbiAgICBbJ0dHR0ctW1ddV1cnLCAvXFxkezR9LVdcXGRcXGQvLCBmYWxzZV0sXG4gICAgWydZWVlZLURERCcsIC9cXGR7NH0tXFxkezN9L10sXG4gICAgWydZWVlZLU1NJywgL1xcZHs0fS1cXGRcXGQvLCBmYWxzZV0sXG4gICAgWydZWVlZWVlNTUREJywgL1srLV1cXGR7MTB9L10sXG4gICAgWydZWVlZTU1ERCcsIC9cXGR7OH0vXSxcbiAgICAvLyBZWVlZTU0gaXMgTk9UIGFsbG93ZWQgYnkgdGhlIHN0YW5kYXJkXG4gICAgWydHR0dHW1ddV1dFJywgL1xcZHs0fVdcXGR7M30vXSxcbiAgICBbJ0dHR0dbV11XVycsIC9cXGR7NH1XXFxkezJ9LywgZmFsc2VdLFxuICAgIFsnWVlZWURERCcsIC9cXGR7N30vXVxuXTtcblxuLy8gaXNvIHRpbWUgZm9ybWF0cyBhbmQgcmVnZXhlc1xudmFyIGlzb1RpbWVzID0gW1xuICAgIFsnSEg6bW06c3MuU1NTUycsIC9cXGRcXGQ6XFxkXFxkOlxcZFxcZFxcLlxcZCsvXSxcbiAgICBbJ0hIOm1tOnNzLFNTU1MnLCAvXFxkXFxkOlxcZFxcZDpcXGRcXGQsXFxkKy9dLFxuICAgIFsnSEg6bW06c3MnLCAvXFxkXFxkOlxcZFxcZDpcXGRcXGQvXSxcbiAgICBbJ0hIOm1tJywgL1xcZFxcZDpcXGRcXGQvXSxcbiAgICBbJ0hIbW1zcy5TU1NTJywgL1xcZFxcZFxcZFxcZFxcZFxcZFxcLlxcZCsvXSxcbiAgICBbJ0hIbW1zcyxTU1NTJywgL1xcZFxcZFxcZFxcZFxcZFxcZCxcXGQrL10sXG4gICAgWydISG1tc3MnLCAvXFxkXFxkXFxkXFxkXFxkXFxkL10sXG4gICAgWydISG1tJywgL1xcZFxcZFxcZFxcZC9dLFxuICAgIFsnSEgnLCAvXFxkXFxkL11cbl07XG5cbnZhciBhc3BOZXRKc29uUmVnZXggPSAvXlxcLz9EYXRlXFwoKFxcLT9cXGQrKS9pO1xuXG4vLyBkYXRlIGZyb20gaXNvIGZvcm1hdFxuZnVuY3Rpb24gY29uZmlnRnJvbUlTTyhjb25maWcpIHtcbiAgICB2YXIgaSwgbCxcbiAgICAgICAgc3RyaW5nID0gY29uZmlnLl9pLFxuICAgICAgICBtYXRjaCA9IGV4dGVuZGVkSXNvUmVnZXguZXhlYyhzdHJpbmcpIHx8IGJhc2ljSXNvUmVnZXguZXhlYyhzdHJpbmcpLFxuICAgICAgICBhbGxvd1RpbWUsIGRhdGVGb3JtYXQsIHRpbWVGb3JtYXQsIHR6Rm9ybWF0O1xuXG4gICAgaWYgKG1hdGNoKSB7XG4gICAgICAgIGdldFBhcnNpbmdGbGFncyhjb25maWcpLmlzbyA9IHRydWU7XG5cbiAgICAgICAgZm9yIChpID0gMCwgbCA9IGlzb0RhdGVzLmxlbmd0aDsgaSA8IGw7IGkrKykge1xuICAgICAgICAgICAgaWYgKGlzb0RhdGVzW2ldWzFdLmV4ZWMobWF0Y2hbMV0pKSB7XG4gICAgICAgICAgICAgICAgZGF0ZUZvcm1hdCA9IGlzb0RhdGVzW2ldWzBdO1xuICAgICAgICAgICAgICAgIGFsbG93VGltZSA9IGlzb0RhdGVzW2ldWzJdICE9PSBmYWxzZTtcbiAgICAgICAgICAgICAgICBicmVhaztcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgICBpZiAoZGF0ZUZvcm1hdCA9PSBudWxsKSB7XG4gICAgICAgICAgICBjb25maWcuX2lzVmFsaWQgPSBmYWxzZTtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuICAgICAgICBpZiAobWF0Y2hbM10pIHtcbiAgICAgICAgICAgIGZvciAoaSA9IDAsIGwgPSBpc29UaW1lcy5sZW5ndGg7IGkgPCBsOyBpKyspIHtcbiAgICAgICAgICAgICAgICBpZiAoaXNvVGltZXNbaV1bMV0uZXhlYyhtYXRjaFszXSkpIHtcbiAgICAgICAgICAgICAgICAgICAgLy8gbWF0Y2hbMl0gc2hvdWxkIGJlICdUJyBvciBzcGFjZVxuICAgICAgICAgICAgICAgICAgICB0aW1lRm9ybWF0ID0gKG1hdGNoWzJdIHx8ICcgJykgKyBpc29UaW1lc1tpXVswXTtcbiAgICAgICAgICAgICAgICAgICAgYnJlYWs7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICAgICAgaWYgKHRpbWVGb3JtYXQgPT0gbnVsbCkge1xuICAgICAgICAgICAgICAgIGNvbmZpZy5faXNWYWxpZCA9IGZhbHNlO1xuICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgICBpZiAoIWFsbG93VGltZSAmJiB0aW1lRm9ybWF0ICE9IG51bGwpIHtcbiAgICAgICAgICAgIGNvbmZpZy5faXNWYWxpZCA9IGZhbHNlO1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG4gICAgICAgIGlmIChtYXRjaFs0XSkge1xuICAgICAgICAgICAgaWYgKHR6UmVnZXguZXhlYyhtYXRjaFs0XSkpIHtcbiAgICAgICAgICAgICAgICB0ekZvcm1hdCA9ICdaJztcbiAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgY29uZmlnLl9pc1ZhbGlkID0gZmFsc2U7XG4gICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgICAgIGNvbmZpZy5fZiA9IGRhdGVGb3JtYXQgKyAodGltZUZvcm1hdCB8fCAnJykgKyAodHpGb3JtYXQgfHwgJycpO1xuICAgICAgICBjb25maWdGcm9tU3RyaW5nQW5kRm9ybWF0KGNvbmZpZyk7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgY29uZmlnLl9pc1ZhbGlkID0gZmFsc2U7XG4gICAgfVxufVxuXG4vLyBSRkMgMjgyMiByZWdleDogRm9yIGRldGFpbHMgc2VlIGh0dHBzOi8vdG9vbHMuaWV0Zi5vcmcvaHRtbC9yZmMyODIyI3NlY3Rpb24tMy4zXG52YXIgcmZjMjgyMiA9IC9eKD86KE1vbnxUdWV8V2VkfFRodXxGcml8U2F0fFN1biksP1xccyk/KFxcZHsxLDJ9KVxccyhKYW58RmVifE1hcnxBcHJ8TWF5fEp1bnxKdWx8QXVnfFNlcHxPY3R8Tm92fERlYylcXHMoXFxkezIsNH0pXFxzKFxcZFxcZCk6KFxcZFxcZCkoPzo6KFxcZFxcZCkpP1xccyg/OihVVHxHTVR8W0VDTVBdW1NEXVQpfChbWnpdKXwoWystXVxcZHs0fSkpJC87XG5cbmZ1bmN0aW9uIGV4dHJhY3RGcm9tUkZDMjgyMlN0cmluZ3MoeWVhclN0ciwgbW9udGhTdHIsIGRheVN0ciwgaG91clN0ciwgbWludXRlU3RyLCBzZWNvbmRTdHIpIHtcbiAgICB2YXIgcmVzdWx0ID0gW1xuICAgICAgICB1bnRydW5jYXRlWWVhcih5ZWFyU3RyKSxcbiAgICAgICAgZGVmYXVsdExvY2FsZU1vbnRoc1Nob3J0LmluZGV4T2YobW9udGhTdHIpLFxuICAgICAgICBwYXJzZUludChkYXlTdHIsIDEwKSxcbiAgICAgICAgcGFyc2VJbnQoaG91clN0ciwgMTApLFxuICAgICAgICBwYXJzZUludChtaW51dGVTdHIsIDEwKVxuICAgIF07XG5cbiAgICBpZiAoc2Vjb25kU3RyKSB7XG4gICAgICAgIHJlc3VsdC5wdXNoKHBhcnNlSW50KHNlY29uZFN0ciwgMTApKTtcbiAgICB9XG5cbiAgICByZXR1cm4gcmVzdWx0O1xufVxuXG5mdW5jdGlvbiB1bnRydW5jYXRlWWVhcih5ZWFyU3RyKSB7XG4gICAgdmFyIHllYXIgPSBwYXJzZUludCh5ZWFyU3RyLCAxMCk7XG4gICAgaWYgKHllYXIgPD0gNDkpIHtcbiAgICAgICAgcmV0dXJuIDIwMDAgKyB5ZWFyO1xuICAgIH0gZWxzZSBpZiAoeWVhciA8PSA5OTkpIHtcbiAgICAgICAgcmV0dXJuIDE5MDAgKyB5ZWFyO1xuICAgIH1cbiAgICByZXR1cm4geWVhcjtcbn1cblxuZnVuY3Rpb24gcHJlcHJvY2Vzc1JGQzI4MjIocykge1xuICAgIC8vIFJlbW92ZSBjb21tZW50cyBhbmQgZm9sZGluZyB3aGl0ZXNwYWNlIGFuZCByZXBsYWNlIG11bHRpcGxlLXNwYWNlcyB3aXRoIGEgc2luZ2xlIHNwYWNlXG4gICAgcmV0dXJuIHMucmVwbGFjZSgvXFwoW14pXSpcXCl8W1xcblxcdF0vZywgJyAnKS5yZXBsYWNlKC8oXFxzXFxzKykvZywgJyAnKS50cmltKCk7XG59XG5cbmZ1bmN0aW9uIGNoZWNrV2Vla2RheSh3ZWVrZGF5U3RyLCBwYXJzZWRJbnB1dCwgY29uZmlnKSB7XG4gICAgaWYgKHdlZWtkYXlTdHIpIHtcbiAgICAgICAgLy8gVE9ETzogUmVwbGFjZSB0aGUgdmFuaWxsYSBKUyBEYXRlIG9iamVjdCB3aXRoIGFuIGluZGVwZW50ZW50IGRheS1vZi13ZWVrIGNoZWNrLlxuICAgICAgICB2YXIgd2Vla2RheVByb3ZpZGVkID0gZGVmYXVsdExvY2FsZVdlZWtkYXlzU2hvcnQuaW5kZXhPZih3ZWVrZGF5U3RyKSxcbiAgICAgICAgICAgIHdlZWtkYXlBY3R1YWwgPSBuZXcgRGF0ZShwYXJzZWRJbnB1dFswXSwgcGFyc2VkSW5wdXRbMV0sIHBhcnNlZElucHV0WzJdKS5nZXREYXkoKTtcbiAgICAgICAgaWYgKHdlZWtkYXlQcm92aWRlZCAhPT0gd2Vla2RheUFjdHVhbCkge1xuICAgICAgICAgICAgZ2V0UGFyc2luZ0ZsYWdzKGNvbmZpZykud2Vla2RheU1pc21hdGNoID0gdHJ1ZTtcbiAgICAgICAgICAgIGNvbmZpZy5faXNWYWxpZCA9IGZhbHNlO1xuICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICB9XG4gICAgfVxuICAgIHJldHVybiB0cnVlO1xufVxuXG52YXIgb2JzT2Zmc2V0cyA9IHtcbiAgICBVVDogMCxcbiAgICBHTVQ6IDAsXG4gICAgRURUOiAtNCAqIDYwLFxuICAgIEVTVDogLTUgKiA2MCxcbiAgICBDRFQ6IC01ICogNjAsXG4gICAgQ1NUOiAtNiAqIDYwLFxuICAgIE1EVDogLTYgKiA2MCxcbiAgICBNU1Q6IC03ICogNjAsXG4gICAgUERUOiAtNyAqIDYwLFxuICAgIFBTVDogLTggKiA2MFxufTtcblxuZnVuY3Rpb24gY2FsY3VsYXRlT2Zmc2V0KG9ic09mZnNldCwgbWlsaXRhcnlPZmZzZXQsIG51bU9mZnNldCkge1xuICAgIGlmIChvYnNPZmZzZXQpIHtcbiAgICAgICAgcmV0dXJuIG9ic09mZnNldHNbb2JzT2Zmc2V0XTtcbiAgICB9IGVsc2UgaWYgKG1pbGl0YXJ5T2Zmc2V0KSB7XG4gICAgICAgIC8vIHRoZSBvbmx5IGFsbG93ZWQgbWlsaXRhcnkgdHogaXMgWlxuICAgICAgICByZXR1cm4gMDtcbiAgICB9IGVsc2Uge1xuICAgICAgICB2YXIgaG0gPSBwYXJzZUludChudW1PZmZzZXQsIDEwKTtcbiAgICAgICAgdmFyIG0gPSBobSAlIDEwMCwgaCA9IChobSAtIG0pIC8gMTAwO1xuICAgICAgICByZXR1cm4gaCAqIDYwICsgbTtcbiAgICB9XG59XG5cbi8vIGRhdGUgYW5kIHRpbWUgZnJvbSByZWYgMjgyMiBmb3JtYXRcbmZ1bmN0aW9uIGNvbmZpZ0Zyb21SRkMyODIyKGNvbmZpZykge1xuICAgIHZhciBtYXRjaCA9IHJmYzI4MjIuZXhlYyhwcmVwcm9jZXNzUkZDMjgyMihjb25maWcuX2kpKTtcbiAgICBpZiAobWF0Y2gpIHtcbiAgICAgICAgdmFyIHBhcnNlZEFycmF5ID0gZXh0cmFjdEZyb21SRkMyODIyU3RyaW5ncyhtYXRjaFs0XSwgbWF0Y2hbM10sIG1hdGNoWzJdLCBtYXRjaFs1XSwgbWF0Y2hbNl0sIG1hdGNoWzddKTtcbiAgICAgICAgaWYgKCFjaGVja1dlZWtkYXkobWF0Y2hbMV0sIHBhcnNlZEFycmF5LCBjb25maWcpKSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBjb25maWcuX2EgPSBwYXJzZWRBcnJheTtcbiAgICAgICAgY29uZmlnLl90em0gPSBjYWxjdWxhdGVPZmZzZXQobWF0Y2hbOF0sIG1hdGNoWzldLCBtYXRjaFsxMF0pO1xuXG4gICAgICAgIGNvbmZpZy5fZCA9IGNyZWF0ZVVUQ0RhdGUuYXBwbHkobnVsbCwgY29uZmlnLl9hKTtcbiAgICAgICAgY29uZmlnLl9kLnNldFVUQ01pbnV0ZXMoY29uZmlnLl9kLmdldFVUQ01pbnV0ZXMoKSAtIGNvbmZpZy5fdHptKTtcblxuICAgICAgICBnZXRQYXJzaW5nRmxhZ3MoY29uZmlnKS5yZmMyODIyID0gdHJ1ZTtcbiAgICB9IGVsc2Uge1xuICAgICAgICBjb25maWcuX2lzVmFsaWQgPSBmYWxzZTtcbiAgICB9XG59XG5cbi8vIGRhdGUgZnJvbSBpc28gZm9ybWF0IG9yIGZhbGxiYWNrXG5mdW5jdGlvbiBjb25maWdGcm9tU3RyaW5nKGNvbmZpZykge1xuICAgIHZhciBtYXRjaGVkID0gYXNwTmV0SnNvblJlZ2V4LmV4ZWMoY29uZmlnLl9pKTtcblxuICAgIGlmIChtYXRjaGVkICE9PSBudWxsKSB7XG4gICAgICAgIGNvbmZpZy5fZCA9IG5ldyBEYXRlKCttYXRjaGVkWzFdKTtcbiAgICAgICAgcmV0dXJuO1xuICAgIH1cblxuICAgIGNvbmZpZ0Zyb21JU08oY29uZmlnKTtcbiAgICBpZiAoY29uZmlnLl9pc1ZhbGlkID09PSBmYWxzZSkge1xuICAgICAgICBkZWxldGUgY29uZmlnLl9pc1ZhbGlkO1xuICAgIH0gZWxzZSB7XG4gICAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICBjb25maWdGcm9tUkZDMjgyMihjb25maWcpO1xuICAgIGlmIChjb25maWcuX2lzVmFsaWQgPT09IGZhbHNlKSB7XG4gICAgICAgIGRlbGV0ZSBjb25maWcuX2lzVmFsaWQ7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgcmV0dXJuO1xuICAgIH1cblxuICAgIC8vIEZpbmFsIGF0dGVtcHQsIHVzZSBJbnB1dCBGYWxsYmFja1xuICAgIGhvb2tzLmNyZWF0ZUZyb21JbnB1dEZhbGxiYWNrKGNvbmZpZyk7XG59XG5cbmhvb2tzLmNyZWF0ZUZyb21JbnB1dEZhbGxiYWNrID0gZGVwcmVjYXRlKFxuICAgICd2YWx1ZSBwcm92aWRlZCBpcyBub3QgaW4gYSByZWNvZ25pemVkIFJGQzI4MjIgb3IgSVNPIGZvcm1hdC4gbW9tZW50IGNvbnN0cnVjdGlvbiBmYWxscyBiYWNrIHRvIGpzIERhdGUoKSwgJyArXG4gICAgJ3doaWNoIGlzIG5vdCByZWxpYWJsZSBhY3Jvc3MgYWxsIGJyb3dzZXJzIGFuZCB2ZXJzaW9ucy4gTm9uIFJGQzI4MjIvSVNPIGRhdGUgZm9ybWF0cyBhcmUgJyArXG4gICAgJ2Rpc2NvdXJhZ2VkIGFuZCB3aWxsIGJlIHJlbW92ZWQgaW4gYW4gdXBjb21pbmcgbWFqb3IgcmVsZWFzZS4gUGxlYXNlIHJlZmVyIHRvICcgK1xuICAgICdodHRwOi8vbW9tZW50anMuY29tL2d1aWRlcy8jL3dhcm5pbmdzL2pzLWRhdGUvIGZvciBtb3JlIGluZm8uJyxcbiAgICBmdW5jdGlvbiAoY29uZmlnKSB7XG4gICAgICAgIGNvbmZpZy5fZCA9IG5ldyBEYXRlKGNvbmZpZy5faSArIChjb25maWcuX3VzZVVUQyA/ICcgVVRDJyA6ICcnKSk7XG4gICAgfVxuKTtcblxuLy8gY29uc3RhbnQgdGhhdCByZWZlcnMgdG8gdGhlIElTTyBzdGFuZGFyZFxuaG9va3MuSVNPXzg2MDEgPSBmdW5jdGlvbiAoKSB7fTtcblxuLy8gY29uc3RhbnQgdGhhdCByZWZlcnMgdG8gdGhlIFJGQyAyODIyIGZvcm1cbmhvb2tzLlJGQ18yODIyID0gZnVuY3Rpb24gKCkge307XG5cbi8vIGRhdGUgZnJvbSBzdHJpbmcgYW5kIGZvcm1hdCBzdHJpbmdcbmZ1bmN0aW9uIGNvbmZpZ0Zyb21TdHJpbmdBbmRGb3JtYXQoY29uZmlnKSB7XG4gICAgLy8gVE9ETzogTW92ZSB0aGlzIHRvIGFub3RoZXIgcGFydCBvZiB0aGUgY3JlYXRpb24gZmxvdyB0byBwcmV2ZW50IGNpcmN1bGFyIGRlcHNcbiAgICBpZiAoY29uZmlnLl9mID09PSBob29rcy5JU09fODYwMSkge1xuICAgICAgICBjb25maWdGcm9tSVNPKGNvbmZpZyk7XG4gICAgICAgIHJldHVybjtcbiAgICB9XG4gICAgaWYgKGNvbmZpZy5fZiA9PT0gaG9va3MuUkZDXzI4MjIpIHtcbiAgICAgICAgY29uZmlnRnJvbVJGQzI4MjIoY29uZmlnKTtcbiAgICAgICAgcmV0dXJuO1xuICAgIH1cbiAgICBjb25maWcuX2EgPSBbXTtcbiAgICBnZXRQYXJzaW5nRmxhZ3MoY29uZmlnKS5lbXB0eSA9IHRydWU7XG5cbiAgICAvLyBUaGlzIGFycmF5IGlzIHVzZWQgdG8gbWFrZSBhIERhdGUsIGVpdGhlciB3aXRoIGBuZXcgRGF0ZWAgb3IgYERhdGUuVVRDYFxuICAgIHZhciBzdHJpbmcgPSAnJyArIGNvbmZpZy5faSxcbiAgICAgICAgaSwgcGFyc2VkSW5wdXQsIHRva2VucywgdG9rZW4sIHNraXBwZWQsXG4gICAgICAgIHN0cmluZ0xlbmd0aCA9IHN0cmluZy5sZW5ndGgsXG4gICAgICAgIHRvdGFsUGFyc2VkSW5wdXRMZW5ndGggPSAwO1xuXG4gICAgdG9rZW5zID0gZXhwYW5kRm9ybWF0KGNvbmZpZy5fZiwgY29uZmlnLl9sb2NhbGUpLm1hdGNoKGZvcm1hdHRpbmdUb2tlbnMpIHx8IFtdO1xuXG4gICAgZm9yIChpID0gMDsgaSA8IHRva2Vucy5sZW5ndGg7IGkrKykge1xuICAgICAgICB0b2tlbiA9IHRva2Vuc1tpXTtcbiAgICAgICAgcGFyc2VkSW5wdXQgPSAoc3RyaW5nLm1hdGNoKGdldFBhcnNlUmVnZXhGb3JUb2tlbih0b2tlbiwgY29uZmlnKSkgfHwgW10pWzBdO1xuICAgICAgICAvLyBjb25zb2xlLmxvZygndG9rZW4nLCB0b2tlbiwgJ3BhcnNlZElucHV0JywgcGFyc2VkSW5wdXQsXG4gICAgICAgIC8vICAgICAgICAgJ3JlZ2V4JywgZ2V0UGFyc2VSZWdleEZvclRva2VuKHRva2VuLCBjb25maWcpKTtcbiAgICAgICAgaWYgKHBhcnNlZElucHV0KSB7XG4gICAgICAgICAgICBza2lwcGVkID0gc3RyaW5nLnN1YnN0cigwLCBzdHJpbmcuaW5kZXhPZihwYXJzZWRJbnB1dCkpO1xuICAgICAgICAgICAgaWYgKHNraXBwZWQubGVuZ3RoID4gMCkge1xuICAgICAgICAgICAgICAgIGdldFBhcnNpbmdGbGFncyhjb25maWcpLnVudXNlZElucHV0LnB1c2goc2tpcHBlZCk7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBzdHJpbmcgPSBzdHJpbmcuc2xpY2Uoc3RyaW5nLmluZGV4T2YocGFyc2VkSW5wdXQpICsgcGFyc2VkSW5wdXQubGVuZ3RoKTtcbiAgICAgICAgICAgIHRvdGFsUGFyc2VkSW5wdXRMZW5ndGggKz0gcGFyc2VkSW5wdXQubGVuZ3RoO1xuICAgICAgICB9XG4gICAgICAgIC8vIGRvbid0IHBhcnNlIGlmIGl0J3Mgbm90IGEga25vd24gdG9rZW5cbiAgICAgICAgaWYgKGZvcm1hdFRva2VuRnVuY3Rpb25zW3Rva2VuXSkge1xuICAgICAgICAgICAgaWYgKHBhcnNlZElucHV0KSB7XG4gICAgICAgICAgICAgICAgZ2V0UGFyc2luZ0ZsYWdzKGNvbmZpZykuZW1wdHkgPSBmYWxzZTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGVsc2Uge1xuICAgICAgICAgICAgICAgIGdldFBhcnNpbmdGbGFncyhjb25maWcpLnVudXNlZFRva2Vucy5wdXNoKHRva2VuKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGFkZFRpbWVUb0FycmF5RnJvbVRva2VuKHRva2VuLCBwYXJzZWRJbnB1dCwgY29uZmlnKTtcbiAgICAgICAgfVxuICAgICAgICBlbHNlIGlmIChjb25maWcuX3N0cmljdCAmJiAhcGFyc2VkSW5wdXQpIHtcbiAgICAgICAgICAgIGdldFBhcnNpbmdGbGFncyhjb25maWcpLnVudXNlZFRva2Vucy5wdXNoKHRva2VuKTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8vIGFkZCByZW1haW5pbmcgdW5wYXJzZWQgaW5wdXQgbGVuZ3RoIHRvIHRoZSBzdHJpbmdcbiAgICBnZXRQYXJzaW5nRmxhZ3MoY29uZmlnKS5jaGFyc0xlZnRPdmVyID0gc3RyaW5nTGVuZ3RoIC0gdG90YWxQYXJzZWRJbnB1dExlbmd0aDtcbiAgICBpZiAoc3RyaW5nLmxlbmd0aCA+IDApIHtcbiAgICAgICAgZ2V0UGFyc2luZ0ZsYWdzKGNvbmZpZykudW51c2VkSW5wdXQucHVzaChzdHJpbmcpO1xuICAgIH1cblxuICAgIC8vIGNsZWFyIF8xMmggZmxhZyBpZiBob3VyIGlzIDw9IDEyXG4gICAgaWYgKGNvbmZpZy5fYVtIT1VSXSA8PSAxMiAmJlxuICAgICAgICBnZXRQYXJzaW5nRmxhZ3MoY29uZmlnKS5iaWdIb3VyID09PSB0cnVlICYmXG4gICAgICAgIGNvbmZpZy5fYVtIT1VSXSA+IDApIHtcbiAgICAgICAgZ2V0UGFyc2luZ0ZsYWdzKGNvbmZpZykuYmlnSG91ciA9IHVuZGVmaW5lZDtcbiAgICB9XG5cbiAgICBnZXRQYXJzaW5nRmxhZ3MoY29uZmlnKS5wYXJzZWREYXRlUGFydHMgPSBjb25maWcuX2Euc2xpY2UoMCk7XG4gICAgZ2V0UGFyc2luZ0ZsYWdzKGNvbmZpZykubWVyaWRpZW0gPSBjb25maWcuX21lcmlkaWVtO1xuICAgIC8vIGhhbmRsZSBtZXJpZGllbVxuICAgIGNvbmZpZy5fYVtIT1VSXSA9IG1lcmlkaWVtRml4V3JhcChjb25maWcuX2xvY2FsZSwgY29uZmlnLl9hW0hPVVJdLCBjb25maWcuX21lcmlkaWVtKTtcblxuICAgIGNvbmZpZ0Zyb21BcnJheShjb25maWcpO1xuICAgIGNoZWNrT3ZlcmZsb3coY29uZmlnKTtcbn1cblxuXG5mdW5jdGlvbiBtZXJpZGllbUZpeFdyYXAgKGxvY2FsZSwgaG91ciwgbWVyaWRpZW0pIHtcbiAgICB2YXIgaXNQbTtcblxuICAgIGlmIChtZXJpZGllbSA9PSBudWxsKSB7XG4gICAgICAgIC8vIG5vdGhpbmcgdG8gZG9cbiAgICAgICAgcmV0dXJuIGhvdXI7XG4gICAgfVxuICAgIGlmIChsb2NhbGUubWVyaWRpZW1Ib3VyICE9IG51bGwpIHtcbiAgICAgICAgcmV0dXJuIGxvY2FsZS5tZXJpZGllbUhvdXIoaG91ciwgbWVyaWRpZW0pO1xuICAgIH0gZWxzZSBpZiAobG9jYWxlLmlzUE0gIT0gbnVsbCkge1xuICAgICAgICAvLyBGYWxsYmFja1xuICAgICAgICBpc1BtID0gbG9jYWxlLmlzUE0obWVyaWRpZW0pO1xuICAgICAgICBpZiAoaXNQbSAmJiBob3VyIDwgMTIpIHtcbiAgICAgICAgICAgIGhvdXIgKz0gMTI7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKCFpc1BtICYmIGhvdXIgPT09IDEyKSB7XG4gICAgICAgICAgICBob3VyID0gMDtcbiAgICAgICAgfVxuICAgICAgICByZXR1cm4gaG91cjtcbiAgICB9IGVsc2Uge1xuICAgICAgICAvLyB0aGlzIGlzIG5vdCBzdXBwb3NlZCB0byBoYXBwZW5cbiAgICAgICAgcmV0dXJuIGhvdXI7XG4gICAgfVxufVxuXG4vLyBkYXRlIGZyb20gc3RyaW5nIGFuZCBhcnJheSBvZiBmb3JtYXQgc3RyaW5nc1xuZnVuY3Rpb24gY29uZmlnRnJvbVN0cmluZ0FuZEFycmF5KGNvbmZpZykge1xuICAgIHZhciB0ZW1wQ29uZmlnLFxuICAgICAgICBiZXN0TW9tZW50LFxuXG4gICAgICAgIHNjb3JlVG9CZWF0LFxuICAgICAgICBpLFxuICAgICAgICBjdXJyZW50U2NvcmU7XG5cbiAgICBpZiAoY29uZmlnLl9mLmxlbmd0aCA9PT0gMCkge1xuICAgICAgICBnZXRQYXJzaW5nRmxhZ3MoY29uZmlnKS5pbnZhbGlkRm9ybWF0ID0gdHJ1ZTtcbiAgICAgICAgY29uZmlnLl9kID0gbmV3IERhdGUoTmFOKTtcbiAgICAgICAgcmV0dXJuO1xuICAgIH1cblxuICAgIGZvciAoaSA9IDA7IGkgPCBjb25maWcuX2YubGVuZ3RoOyBpKyspIHtcbiAgICAgICAgY3VycmVudFNjb3JlID0gMDtcbiAgICAgICAgdGVtcENvbmZpZyA9IGNvcHlDb25maWcoe30sIGNvbmZpZyk7XG4gICAgICAgIGlmIChjb25maWcuX3VzZVVUQyAhPSBudWxsKSB7XG4gICAgICAgICAgICB0ZW1wQ29uZmlnLl91c2VVVEMgPSBjb25maWcuX3VzZVVUQztcbiAgICAgICAgfVxuICAgICAgICB0ZW1wQ29uZmlnLl9mID0gY29uZmlnLl9mW2ldO1xuICAgICAgICBjb25maWdGcm9tU3RyaW5nQW5kRm9ybWF0KHRlbXBDb25maWcpO1xuXG4gICAgICAgIGlmICghaXNWYWxpZCh0ZW1wQ29uZmlnKSkge1xuICAgICAgICAgICAgY29udGludWU7XG4gICAgICAgIH1cblxuICAgICAgICAvLyBpZiB0aGVyZSBpcyBhbnkgaW5wdXQgdGhhdCB3YXMgbm90IHBhcnNlZCBhZGQgYSBwZW5hbHR5IGZvciB0aGF0IGZvcm1hdFxuICAgICAgICBjdXJyZW50U2NvcmUgKz0gZ2V0UGFyc2luZ0ZsYWdzKHRlbXBDb25maWcpLmNoYXJzTGVmdE92ZXI7XG5cbiAgICAgICAgLy9vciB0b2tlbnNcbiAgICAgICAgY3VycmVudFNjb3JlICs9IGdldFBhcnNpbmdGbGFncyh0ZW1wQ29uZmlnKS51bnVzZWRUb2tlbnMubGVuZ3RoICogMTA7XG5cbiAgICAgICAgZ2V0UGFyc2luZ0ZsYWdzKHRlbXBDb25maWcpLnNjb3JlID0gY3VycmVudFNjb3JlO1xuXG4gICAgICAgIGlmIChzY29yZVRvQmVhdCA9PSBudWxsIHx8IGN1cnJlbnRTY29yZSA8IHNjb3JlVG9CZWF0KSB7XG4gICAgICAgICAgICBzY29yZVRvQmVhdCA9IGN1cnJlbnRTY29yZTtcbiAgICAgICAgICAgIGJlc3RNb21lbnQgPSB0ZW1wQ29uZmlnO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgZXh0ZW5kKGNvbmZpZywgYmVzdE1vbWVudCB8fCB0ZW1wQ29uZmlnKTtcbn1cblxuZnVuY3Rpb24gY29uZmlnRnJvbU9iamVjdChjb25maWcpIHtcbiAgICBpZiAoY29uZmlnLl9kKSB7XG4gICAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICB2YXIgaSA9IG5vcm1hbGl6ZU9iamVjdFVuaXRzKGNvbmZpZy5faSk7XG4gICAgY29uZmlnLl9hID0gbWFwKFtpLnllYXIsIGkubW9udGgsIGkuZGF5IHx8IGkuZGF0ZSwgaS5ob3VyLCBpLm1pbnV0ZSwgaS5zZWNvbmQsIGkubWlsbGlzZWNvbmRdLCBmdW5jdGlvbiAob2JqKSB7XG4gICAgICAgIHJldHVybiBvYmogJiYgcGFyc2VJbnQob2JqLCAxMCk7XG4gICAgfSk7XG5cbiAgICBjb25maWdGcm9tQXJyYXkoY29uZmlnKTtcbn1cblxuZnVuY3Rpb24gY3JlYXRlRnJvbUNvbmZpZyAoY29uZmlnKSB7XG4gICAgdmFyIHJlcyA9IG5ldyBNb21lbnQoY2hlY2tPdmVyZmxvdyhwcmVwYXJlQ29uZmlnKGNvbmZpZykpKTtcbiAgICBpZiAocmVzLl9uZXh0RGF5KSB7XG4gICAgICAgIC8vIEFkZGluZyBpcyBzbWFydCBlbm91Z2ggYXJvdW5kIERTVFxuICAgICAgICByZXMuYWRkKDEsICdkJyk7XG4gICAgICAgIHJlcy5fbmV4dERheSA9IHVuZGVmaW5lZDtcbiAgICB9XG5cbiAgICByZXR1cm4gcmVzO1xufVxuXG5mdW5jdGlvbiBwcmVwYXJlQ29uZmlnIChjb25maWcpIHtcbiAgICB2YXIgaW5wdXQgPSBjb25maWcuX2ksXG4gICAgICAgIGZvcm1hdCA9IGNvbmZpZy5fZjtcblxuICAgIGNvbmZpZy5fbG9jYWxlID0gY29uZmlnLl9sb2NhbGUgfHwgZ2V0TG9jYWxlKGNvbmZpZy5fbCk7XG5cbiAgICBpZiAoaW5wdXQgPT09IG51bGwgfHwgKGZvcm1hdCA9PT0gdW5kZWZpbmVkICYmIGlucHV0ID09PSAnJykpIHtcbiAgICAgICAgcmV0dXJuIGNyZWF0ZUludmFsaWQoe251bGxJbnB1dDogdHJ1ZX0pO1xuICAgIH1cblxuICAgIGlmICh0eXBlb2YgaW5wdXQgPT09ICdzdHJpbmcnKSB7XG4gICAgICAgIGNvbmZpZy5faSA9IGlucHV0ID0gY29uZmlnLl9sb2NhbGUucHJlcGFyc2UoaW5wdXQpO1xuICAgIH1cblxuICAgIGlmIChpc01vbWVudChpbnB1dCkpIHtcbiAgICAgICAgcmV0dXJuIG5ldyBNb21lbnQoY2hlY2tPdmVyZmxvdyhpbnB1dCkpO1xuICAgIH0gZWxzZSBpZiAoaXNEYXRlKGlucHV0KSkge1xuICAgICAgICBjb25maWcuX2QgPSBpbnB1dDtcbiAgICB9IGVsc2UgaWYgKGlzQXJyYXkoZm9ybWF0KSkge1xuICAgICAgICBjb25maWdGcm9tU3RyaW5nQW5kQXJyYXkoY29uZmlnKTtcbiAgICB9IGVsc2UgaWYgKGZvcm1hdCkge1xuICAgICAgICBjb25maWdGcm9tU3RyaW5nQW5kRm9ybWF0KGNvbmZpZyk7XG4gICAgfSAgZWxzZSB7XG4gICAgICAgIGNvbmZpZ0Zyb21JbnB1dChjb25maWcpO1xuICAgIH1cblxuICAgIGlmICghaXNWYWxpZChjb25maWcpKSB7XG4gICAgICAgIGNvbmZpZy5fZCA9IG51bGw7XG4gICAgfVxuXG4gICAgcmV0dXJuIGNvbmZpZztcbn1cblxuZnVuY3Rpb24gY29uZmlnRnJvbUlucHV0KGNvbmZpZykge1xuICAgIHZhciBpbnB1dCA9IGNvbmZpZy5faTtcbiAgICBpZiAoaXNVbmRlZmluZWQoaW5wdXQpKSB7XG4gICAgICAgIGNvbmZpZy5fZCA9IG5ldyBEYXRlKGhvb2tzLm5vdygpKTtcbiAgICB9IGVsc2UgaWYgKGlzRGF0ZShpbnB1dCkpIHtcbiAgICAgICAgY29uZmlnLl9kID0gbmV3IERhdGUoaW5wdXQudmFsdWVPZigpKTtcbiAgICB9IGVsc2UgaWYgKHR5cGVvZiBpbnB1dCA9PT0gJ3N0cmluZycpIHtcbiAgICAgICAgY29uZmlnRnJvbVN0cmluZyhjb25maWcpO1xuICAgIH0gZWxzZSBpZiAoaXNBcnJheShpbnB1dCkpIHtcbiAgICAgICAgY29uZmlnLl9hID0gbWFwKGlucHV0LnNsaWNlKDApLCBmdW5jdGlvbiAob2JqKSB7XG4gICAgICAgICAgICByZXR1cm4gcGFyc2VJbnQob2JqLCAxMCk7XG4gICAgICAgIH0pO1xuICAgICAgICBjb25maWdGcm9tQXJyYXkoY29uZmlnKTtcbiAgICB9IGVsc2UgaWYgKGlzT2JqZWN0KGlucHV0KSkge1xuICAgICAgICBjb25maWdGcm9tT2JqZWN0KGNvbmZpZyk7XG4gICAgfSBlbHNlIGlmIChpc051bWJlcihpbnB1dCkpIHtcbiAgICAgICAgLy8gZnJvbSBtaWxsaXNlY29uZHNcbiAgICAgICAgY29uZmlnLl9kID0gbmV3IERhdGUoaW5wdXQpO1xuICAgIH0gZWxzZSB7XG4gICAgICAgIGhvb2tzLmNyZWF0ZUZyb21JbnB1dEZhbGxiYWNrKGNvbmZpZyk7XG4gICAgfVxufVxuXG5mdW5jdGlvbiBjcmVhdGVMb2NhbE9yVVRDIChpbnB1dCwgZm9ybWF0LCBsb2NhbGUsIHN0cmljdCwgaXNVVEMpIHtcbiAgICB2YXIgYyA9IHt9O1xuXG4gICAgaWYgKGxvY2FsZSA9PT0gdHJ1ZSB8fCBsb2NhbGUgPT09IGZhbHNlKSB7XG4gICAgICAgIHN0cmljdCA9IGxvY2FsZTtcbiAgICAgICAgbG9jYWxlID0gdW5kZWZpbmVkO1xuICAgIH1cblxuICAgIGlmICgoaXNPYmplY3QoaW5wdXQpICYmIGlzT2JqZWN0RW1wdHkoaW5wdXQpKSB8fFxuICAgICAgICAgICAgKGlzQXJyYXkoaW5wdXQpICYmIGlucHV0Lmxlbmd0aCA9PT0gMCkpIHtcbiAgICAgICAgaW5wdXQgPSB1bmRlZmluZWQ7XG4gICAgfVxuICAgIC8vIG9iamVjdCBjb25zdHJ1Y3Rpb24gbXVzdCBiZSBkb25lIHRoaXMgd2F5LlxuICAgIC8vIGh0dHBzOi8vZ2l0aHViLmNvbS9tb21lbnQvbW9tZW50L2lzc3Vlcy8xNDIzXG4gICAgYy5faXNBTW9tZW50T2JqZWN0ID0gdHJ1ZTtcbiAgICBjLl91c2VVVEMgPSBjLl9pc1VUQyA9IGlzVVRDO1xuICAgIGMuX2wgPSBsb2NhbGU7XG4gICAgYy5faSA9IGlucHV0O1xuICAgIGMuX2YgPSBmb3JtYXQ7XG4gICAgYy5fc3RyaWN0ID0gc3RyaWN0O1xuXG4gICAgcmV0dXJuIGNyZWF0ZUZyb21Db25maWcoYyk7XG59XG5cbmZ1bmN0aW9uIGNyZWF0ZUxvY2FsIChpbnB1dCwgZm9ybWF0LCBsb2NhbGUsIHN0cmljdCkge1xuICAgIHJldHVybiBjcmVhdGVMb2NhbE9yVVRDKGlucHV0LCBmb3JtYXQsIGxvY2FsZSwgc3RyaWN0LCBmYWxzZSk7XG59XG5cbnZhciBwcm90b3R5cGVNaW4gPSBkZXByZWNhdGUoXG4gICAgJ21vbWVudCgpLm1pbiBpcyBkZXByZWNhdGVkLCB1c2UgbW9tZW50Lm1heCBpbnN0ZWFkLiBodHRwOi8vbW9tZW50anMuY29tL2d1aWRlcy8jL3dhcm5pbmdzL21pbi1tYXgvJyxcbiAgICBmdW5jdGlvbiAoKSB7XG4gICAgICAgIHZhciBvdGhlciA9IGNyZWF0ZUxvY2FsLmFwcGx5KG51bGwsIGFyZ3VtZW50cyk7XG4gICAgICAgIGlmICh0aGlzLmlzVmFsaWQoKSAmJiBvdGhlci5pc1ZhbGlkKCkpIHtcbiAgICAgICAgICAgIHJldHVybiBvdGhlciA8IHRoaXMgPyB0aGlzIDogb3RoZXI7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICByZXR1cm4gY3JlYXRlSW52YWxpZCgpO1xuICAgICAgICB9XG4gICAgfVxuKTtcblxudmFyIHByb3RvdHlwZU1heCA9IGRlcHJlY2F0ZShcbiAgICAnbW9tZW50KCkubWF4IGlzIGRlcHJlY2F0ZWQsIHVzZSBtb21lbnQubWluIGluc3RlYWQuIGh0dHA6Ly9tb21lbnRqcy5jb20vZ3VpZGVzLyMvd2FybmluZ3MvbWluLW1heC8nLFxuICAgIGZ1bmN0aW9uICgpIHtcbiAgICAgICAgdmFyIG90aGVyID0gY3JlYXRlTG9jYWwuYXBwbHkobnVsbCwgYXJndW1lbnRzKTtcbiAgICAgICAgaWYgKHRoaXMuaXNWYWxpZCgpICYmIG90aGVyLmlzVmFsaWQoKSkge1xuICAgICAgICAgICAgcmV0dXJuIG90aGVyID4gdGhpcyA/IHRoaXMgOiBvdGhlcjtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIHJldHVybiBjcmVhdGVJbnZhbGlkKCk7XG4gICAgICAgIH1cbiAgICB9XG4pO1xuXG4vLyBQaWNrIGEgbW9tZW50IG0gZnJvbSBtb21lbnRzIHNvIHRoYXQgbVtmbl0ob3RoZXIpIGlzIHRydWUgZm9yIGFsbFxuLy8gb3RoZXIuIFRoaXMgcmVsaWVzIG9uIHRoZSBmdW5jdGlvbiBmbiB0byBiZSB0cmFuc2l0aXZlLlxuLy9cbi8vIG1vbWVudHMgc2hvdWxkIGVpdGhlciBiZSBhbiBhcnJheSBvZiBtb21lbnQgb2JqZWN0cyBvciBhbiBhcnJheSwgd2hvc2Vcbi8vIGZpcnN0IGVsZW1lbnQgaXMgYW4gYXJyYXkgb2YgbW9tZW50IG9iamVjdHMuXG5mdW5jdGlvbiBwaWNrQnkoZm4sIG1vbWVudHMpIHtcbiAgICB2YXIgcmVzLCBpO1xuICAgIGlmIChtb21lbnRzLmxlbmd0aCA9PT0gMSAmJiBpc0FycmF5KG1vbWVudHNbMF0pKSB7XG4gICAgICAgIG1vbWVudHMgPSBtb21lbnRzWzBdO1xuICAgIH1cbiAgICBpZiAoIW1vbWVudHMubGVuZ3RoKSB7XG4gICAgICAgIHJldHVybiBjcmVhdGVMb2NhbCgpO1xuICAgIH1cbiAgICByZXMgPSBtb21lbnRzWzBdO1xuICAgIGZvciAoaSA9IDE7IGkgPCBtb21lbnRzLmxlbmd0aDsgKytpKSB7XG4gICAgICAgIGlmICghbW9tZW50c1tpXS5pc1ZhbGlkKCkgfHwgbW9tZW50c1tpXVtmbl0ocmVzKSkge1xuICAgICAgICAgICAgcmVzID0gbW9tZW50c1tpXTtcbiAgICAgICAgfVxuICAgIH1cbiAgICByZXR1cm4gcmVzO1xufVxuXG4vLyBUT0RPOiBVc2UgW10uc29ydCBpbnN0ZWFkP1xuZnVuY3Rpb24gbWluICgpIHtcbiAgICB2YXIgYXJncyA9IFtdLnNsaWNlLmNhbGwoYXJndW1lbnRzLCAwKTtcblxuICAgIHJldHVybiBwaWNrQnkoJ2lzQmVmb3JlJywgYXJncyk7XG59XG5cbmZ1bmN0aW9uIG1heCAoKSB7XG4gICAgdmFyIGFyZ3MgPSBbXS5zbGljZS5jYWxsKGFyZ3VtZW50cywgMCk7XG5cbiAgICByZXR1cm4gcGlja0J5KCdpc0FmdGVyJywgYXJncyk7XG59XG5cbnZhciBub3cgPSBmdW5jdGlvbiAoKSB7XG4gICAgcmV0dXJuIERhdGUubm93ID8gRGF0ZS5ub3coKSA6ICsobmV3IERhdGUoKSk7XG59O1xuXG52YXIgb3JkZXJpbmcgPSBbJ3llYXInLCAncXVhcnRlcicsICdtb250aCcsICd3ZWVrJywgJ2RheScsICdob3VyJywgJ21pbnV0ZScsICdzZWNvbmQnLCAnbWlsbGlzZWNvbmQnXTtcblxuZnVuY3Rpb24gaXNEdXJhdGlvblZhbGlkKG0pIHtcbiAgICBmb3IgKHZhciBrZXkgaW4gbSkge1xuICAgICAgICBpZiAoIShpbmRleE9mLmNhbGwob3JkZXJpbmcsIGtleSkgIT09IC0xICYmIChtW2tleV0gPT0gbnVsbCB8fCAhaXNOYU4obVtrZXldKSkpKSB7XG4gICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICB2YXIgdW5pdEhhc0RlY2ltYWwgPSBmYWxzZTtcbiAgICBmb3IgKHZhciBpID0gMDsgaSA8IG9yZGVyaW5nLmxlbmd0aDsgKytpKSB7XG4gICAgICAgIGlmIChtW29yZGVyaW5nW2ldXSkge1xuICAgICAgICAgICAgaWYgKHVuaXRIYXNEZWNpbWFsKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuIGZhbHNlOyAvLyBvbmx5IGFsbG93IG5vbi1pbnRlZ2VycyBmb3Igc21hbGxlc3QgdW5pdFxuICAgICAgICAgICAgfVxuICAgICAgICAgICAgaWYgKHBhcnNlRmxvYXQobVtvcmRlcmluZ1tpXV0pICE9PSB0b0ludChtW29yZGVyaW5nW2ldXSkpIHtcbiAgICAgICAgICAgICAgICB1bml0SGFzRGVjaW1hbCA9IHRydWU7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICByZXR1cm4gdHJ1ZTtcbn1cblxuZnVuY3Rpb24gaXNWYWxpZCQxKCkge1xuICAgIHJldHVybiB0aGlzLl9pc1ZhbGlkO1xufVxuXG5mdW5jdGlvbiBjcmVhdGVJbnZhbGlkJDEoKSB7XG4gICAgcmV0dXJuIGNyZWF0ZUR1cmF0aW9uKE5hTik7XG59XG5cbmZ1bmN0aW9uIER1cmF0aW9uIChkdXJhdGlvbikge1xuICAgIHZhciBub3JtYWxpemVkSW5wdXQgPSBub3JtYWxpemVPYmplY3RVbml0cyhkdXJhdGlvbiksXG4gICAgICAgIHllYXJzID0gbm9ybWFsaXplZElucHV0LnllYXIgfHwgMCxcbiAgICAgICAgcXVhcnRlcnMgPSBub3JtYWxpemVkSW5wdXQucXVhcnRlciB8fCAwLFxuICAgICAgICBtb250aHMgPSBub3JtYWxpemVkSW5wdXQubW9udGggfHwgMCxcbiAgICAgICAgd2Vla3MgPSBub3JtYWxpemVkSW5wdXQud2VlayB8fCAwLFxuICAgICAgICBkYXlzID0gbm9ybWFsaXplZElucHV0LmRheSB8fCAwLFxuICAgICAgICBob3VycyA9IG5vcm1hbGl6ZWRJbnB1dC5ob3VyIHx8IDAsXG4gICAgICAgIG1pbnV0ZXMgPSBub3JtYWxpemVkSW5wdXQubWludXRlIHx8IDAsXG4gICAgICAgIHNlY29uZHMgPSBub3JtYWxpemVkSW5wdXQuc2Vjb25kIHx8IDAsXG4gICAgICAgIG1pbGxpc2Vjb25kcyA9IG5vcm1hbGl6ZWRJbnB1dC5taWxsaXNlY29uZCB8fCAwO1xuXG4gICAgdGhpcy5faXNWYWxpZCA9IGlzRHVyYXRpb25WYWxpZChub3JtYWxpemVkSW5wdXQpO1xuXG4gICAgLy8gcmVwcmVzZW50YXRpb24gZm9yIGRhdGVBZGRSZW1vdmVcbiAgICB0aGlzLl9taWxsaXNlY29uZHMgPSArbWlsbGlzZWNvbmRzICtcbiAgICAgICAgc2Vjb25kcyAqIDFlMyArIC8vIDEwMDBcbiAgICAgICAgbWludXRlcyAqIDZlNCArIC8vIDEwMDAgKiA2MFxuICAgICAgICBob3VycyAqIDEwMDAgKiA2MCAqIDYwOyAvL3VzaW5nIDEwMDAgKiA2MCAqIDYwIGluc3RlYWQgb2YgMzZlNSB0byBhdm9pZCBmbG9hdGluZyBwb2ludCByb3VuZGluZyBlcnJvcnMgaHR0cHM6Ly9naXRodWIuY29tL21vbWVudC9tb21lbnQvaXNzdWVzLzI5NzhcbiAgICAvLyBCZWNhdXNlIG9mIGRhdGVBZGRSZW1vdmUgdHJlYXRzIDI0IGhvdXJzIGFzIGRpZmZlcmVudCBmcm9tIGFcbiAgICAvLyBkYXkgd2hlbiB3b3JraW5nIGFyb3VuZCBEU1QsIHdlIG5lZWQgdG8gc3RvcmUgdGhlbSBzZXBhcmF0ZWx5XG4gICAgdGhpcy5fZGF5cyA9ICtkYXlzICtcbiAgICAgICAgd2Vla3MgKiA3O1xuICAgIC8vIEl0IGlzIGltcG9zc2libGUgdG8gdHJhbnNsYXRlIG1vbnRocyBpbnRvIGRheXMgd2l0aG91dCBrbm93aW5nXG4gICAgLy8gd2hpY2ggbW9udGhzIHlvdSBhcmUgYXJlIHRhbGtpbmcgYWJvdXQsIHNvIHdlIGhhdmUgdG8gc3RvcmVcbiAgICAvLyBpdCBzZXBhcmF0ZWx5LlxuICAgIHRoaXMuX21vbnRocyA9ICttb250aHMgK1xuICAgICAgICBxdWFydGVycyAqIDMgK1xuICAgICAgICB5ZWFycyAqIDEyO1xuXG4gICAgdGhpcy5fZGF0YSA9IHt9O1xuXG4gICAgdGhpcy5fbG9jYWxlID0gZ2V0TG9jYWxlKCk7XG5cbiAgICB0aGlzLl9idWJibGUoKTtcbn1cblxuZnVuY3Rpb24gaXNEdXJhdGlvbiAob2JqKSB7XG4gICAgcmV0dXJuIG9iaiBpbnN0YW5jZW9mIER1cmF0aW9uO1xufVxuXG5mdW5jdGlvbiBhYnNSb3VuZCAobnVtYmVyKSB7XG4gICAgaWYgKG51bWJlciA8IDApIHtcbiAgICAgICAgcmV0dXJuIE1hdGgucm91bmQoLTEgKiBudW1iZXIpICogLTE7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgcmV0dXJuIE1hdGgucm91bmQobnVtYmVyKTtcbiAgICB9XG59XG5cbi8vIEZPUk1BVFRJTkdcblxuZnVuY3Rpb24gb2Zmc2V0ICh0b2tlbiwgc2VwYXJhdG9yKSB7XG4gICAgYWRkRm9ybWF0VG9rZW4odG9rZW4sIDAsIDAsIGZ1bmN0aW9uICgpIHtcbiAgICAgICAgdmFyIG9mZnNldCA9IHRoaXMudXRjT2Zmc2V0KCk7XG4gICAgICAgIHZhciBzaWduID0gJysnO1xuICAgICAgICBpZiAob2Zmc2V0IDwgMCkge1xuICAgICAgICAgICAgb2Zmc2V0ID0gLW9mZnNldDtcbiAgICAgICAgICAgIHNpZ24gPSAnLSc7XG4gICAgICAgIH1cbiAgICAgICAgcmV0dXJuIHNpZ24gKyB6ZXJvRmlsbCh+fihvZmZzZXQgLyA2MCksIDIpICsgc2VwYXJhdG9yICsgemVyb0ZpbGwofn4ob2Zmc2V0KSAlIDYwLCAyKTtcbiAgICB9KTtcbn1cblxub2Zmc2V0KCdaJywgJzonKTtcbm9mZnNldCgnWlonLCAnJyk7XG5cbi8vIFBBUlNJTkdcblxuYWRkUmVnZXhUb2tlbignWicsICBtYXRjaFNob3J0T2Zmc2V0KTtcbmFkZFJlZ2V4VG9rZW4oJ1paJywgbWF0Y2hTaG9ydE9mZnNldCk7XG5hZGRQYXJzZVRva2VuKFsnWicsICdaWiddLCBmdW5jdGlvbiAoaW5wdXQsIGFycmF5LCBjb25maWcpIHtcbiAgICBjb25maWcuX3VzZVVUQyA9IHRydWU7XG4gICAgY29uZmlnLl90em0gPSBvZmZzZXRGcm9tU3RyaW5nKG1hdGNoU2hvcnRPZmZzZXQsIGlucHV0KTtcbn0pO1xuXG4vLyBIRUxQRVJTXG5cbi8vIHRpbWV6b25lIGNodW5rZXJcbi8vICcrMTA6MDAnID4gWycxMCcsICAnMDAnXVxuLy8gJy0xNTMwJyAgPiBbJy0xNScsICczMCddXG52YXIgY2h1bmtPZmZzZXQgPSAvKFtcXCtcXC1dfFxcZFxcZCkvZ2k7XG5cbmZ1bmN0aW9uIG9mZnNldEZyb21TdHJpbmcobWF0Y2hlciwgc3RyaW5nKSB7XG4gICAgdmFyIG1hdGNoZXMgPSAoc3RyaW5nIHx8ICcnKS5tYXRjaChtYXRjaGVyKTtcblxuICAgIGlmIChtYXRjaGVzID09PSBudWxsKSB7XG4gICAgICAgIHJldHVybiBudWxsO1xuICAgIH1cblxuICAgIHZhciBjaHVuayAgID0gbWF0Y2hlc1ttYXRjaGVzLmxlbmd0aCAtIDFdIHx8IFtdO1xuICAgIHZhciBwYXJ0cyAgID0gKGNodW5rICsgJycpLm1hdGNoKGNodW5rT2Zmc2V0KSB8fCBbJy0nLCAwLCAwXTtcbiAgICB2YXIgbWludXRlcyA9ICsocGFydHNbMV0gKiA2MCkgKyB0b0ludChwYXJ0c1syXSk7XG5cbiAgICByZXR1cm4gbWludXRlcyA9PT0gMCA/XG4gICAgICAwIDpcbiAgICAgIHBhcnRzWzBdID09PSAnKycgPyBtaW51dGVzIDogLW1pbnV0ZXM7XG59XG5cbi8vIFJldHVybiBhIG1vbWVudCBmcm9tIGlucHV0LCB0aGF0IGlzIGxvY2FsL3V0Yy96b25lIGVxdWl2YWxlbnQgdG8gbW9kZWwuXG5mdW5jdGlvbiBjbG9uZVdpdGhPZmZzZXQoaW5wdXQsIG1vZGVsKSB7XG4gICAgdmFyIHJlcywgZGlmZjtcbiAgICBpZiAobW9kZWwuX2lzVVRDKSB7XG4gICAgICAgIHJlcyA9IG1vZGVsLmNsb25lKCk7XG4gICAgICAgIGRpZmYgPSAoaXNNb21lbnQoaW5wdXQpIHx8IGlzRGF0ZShpbnB1dCkgPyBpbnB1dC52YWx1ZU9mKCkgOiBjcmVhdGVMb2NhbChpbnB1dCkudmFsdWVPZigpKSAtIHJlcy52YWx1ZU9mKCk7XG4gICAgICAgIC8vIFVzZSBsb3ctbGV2ZWwgYXBpLCBiZWNhdXNlIHRoaXMgZm4gaXMgbG93LWxldmVsIGFwaS5cbiAgICAgICAgcmVzLl9kLnNldFRpbWUocmVzLl9kLnZhbHVlT2YoKSArIGRpZmYpO1xuICAgICAgICBob29rcy51cGRhdGVPZmZzZXQocmVzLCBmYWxzZSk7XG4gICAgICAgIHJldHVybiByZXM7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgcmV0dXJuIGNyZWF0ZUxvY2FsKGlucHV0KS5sb2NhbCgpO1xuICAgIH1cbn1cblxuZnVuY3Rpb24gZ2V0RGF0ZU9mZnNldCAobSkge1xuICAgIC8vIE9uIEZpcmVmb3guMjQgRGF0ZSNnZXRUaW1lem9uZU9mZnNldCByZXR1cm5zIGEgZmxvYXRpbmcgcG9pbnQuXG4gICAgLy8gaHR0cHM6Ly9naXRodWIuY29tL21vbWVudC9tb21lbnQvcHVsbC8xODcxXG4gICAgcmV0dXJuIC1NYXRoLnJvdW5kKG0uX2QuZ2V0VGltZXpvbmVPZmZzZXQoKSAvIDE1KSAqIDE1O1xufVxuXG4vLyBIT09LU1xuXG4vLyBUaGlzIGZ1bmN0aW9uIHdpbGwgYmUgY2FsbGVkIHdoZW5ldmVyIGEgbW9tZW50IGlzIG11dGF0ZWQuXG4vLyBJdCBpcyBpbnRlbmRlZCB0byBrZWVwIHRoZSBvZmZzZXQgaW4gc3luYyB3aXRoIHRoZSB0aW1lem9uZS5cbmhvb2tzLnVwZGF0ZU9mZnNldCA9IGZ1bmN0aW9uICgpIHt9O1xuXG4vLyBNT01FTlRTXG5cbi8vIGtlZXBMb2NhbFRpbWUgPSB0cnVlIG1lYW5zIG9ubHkgY2hhbmdlIHRoZSB0aW1lem9uZSwgd2l0aG91dFxuLy8gYWZmZWN0aW5nIHRoZSBsb2NhbCBob3VyLiBTbyA1OjMxOjI2ICswMzAwIC0tW3V0Y09mZnNldCgyLCB0cnVlKV0tLT5cbi8vIDU6MzE6MjYgKzAyMDAgSXQgaXMgcG9zc2libGUgdGhhdCA1OjMxOjI2IGRvZXNuJ3QgZXhpc3Qgd2l0aCBvZmZzZXRcbi8vICswMjAwLCBzbyB3ZSBhZGp1c3QgdGhlIHRpbWUgYXMgbmVlZGVkLCB0byBiZSB2YWxpZC5cbi8vXG4vLyBLZWVwaW5nIHRoZSB0aW1lIGFjdHVhbGx5IGFkZHMvc3VidHJhY3RzIChvbmUgaG91cilcbi8vIGZyb20gdGhlIGFjdHVhbCByZXByZXNlbnRlZCB0aW1lLiBUaGF0IGlzIHdoeSB3ZSBjYWxsIHVwZGF0ZU9mZnNldFxuLy8gYSBzZWNvbmQgdGltZS4gSW4gY2FzZSBpdCB3YW50cyB1cyB0byBjaGFuZ2UgdGhlIG9mZnNldCBhZ2FpblxuLy8gX2NoYW5nZUluUHJvZ3Jlc3MgPT0gdHJ1ZSBjYXNlLCB0aGVuIHdlIGhhdmUgdG8gYWRqdXN0LCBiZWNhdXNlXG4vLyB0aGVyZSBpcyBubyBzdWNoIHRpbWUgaW4gdGhlIGdpdmVuIHRpbWV6b25lLlxuZnVuY3Rpb24gZ2V0U2V0T2Zmc2V0IChpbnB1dCwga2VlcExvY2FsVGltZSwga2VlcE1pbnV0ZXMpIHtcbiAgICB2YXIgb2Zmc2V0ID0gdGhpcy5fb2Zmc2V0IHx8IDAsXG4gICAgICAgIGxvY2FsQWRqdXN0O1xuICAgIGlmICghdGhpcy5pc1ZhbGlkKCkpIHtcbiAgICAgICAgcmV0dXJuIGlucHV0ICE9IG51bGwgPyB0aGlzIDogTmFOO1xuICAgIH1cbiAgICBpZiAoaW5wdXQgIT0gbnVsbCkge1xuICAgICAgICBpZiAodHlwZW9mIGlucHV0ID09PSAnc3RyaW5nJykge1xuICAgICAgICAgICAgaW5wdXQgPSBvZmZzZXRGcm9tU3RyaW5nKG1hdGNoU2hvcnRPZmZzZXQsIGlucHV0KTtcbiAgICAgICAgICAgIGlmIChpbnB1dCA9PT0gbnVsbCkge1xuICAgICAgICAgICAgICAgIHJldHVybiB0aGlzO1xuICAgICAgICAgICAgfVxuICAgICAgICB9IGVsc2UgaWYgKE1hdGguYWJzKGlucHV0KSA8IDE2ICYmICFrZWVwTWludXRlcykge1xuICAgICAgICAgICAgaW5wdXQgPSBpbnB1dCAqIDYwO1xuICAgICAgICB9XG4gICAgICAgIGlmICghdGhpcy5faXNVVEMgJiYga2VlcExvY2FsVGltZSkge1xuICAgICAgICAgICAgbG9jYWxBZGp1c3QgPSBnZXREYXRlT2Zmc2V0KHRoaXMpO1xuICAgICAgICB9XG4gICAgICAgIHRoaXMuX29mZnNldCA9IGlucHV0O1xuICAgICAgICB0aGlzLl9pc1VUQyA9IHRydWU7XG4gICAgICAgIGlmIChsb2NhbEFkanVzdCAhPSBudWxsKSB7XG4gICAgICAgICAgICB0aGlzLmFkZChsb2NhbEFkanVzdCwgJ20nKTtcbiAgICAgICAgfVxuICAgICAgICBpZiAob2Zmc2V0ICE9PSBpbnB1dCkge1xuICAgICAgICAgICAgaWYgKCFrZWVwTG9jYWxUaW1lIHx8IHRoaXMuX2NoYW5nZUluUHJvZ3Jlc3MpIHtcbiAgICAgICAgICAgICAgICBhZGRTdWJ0cmFjdCh0aGlzLCBjcmVhdGVEdXJhdGlvbihpbnB1dCAtIG9mZnNldCwgJ20nKSwgMSwgZmFsc2UpO1xuICAgICAgICAgICAgfSBlbHNlIGlmICghdGhpcy5fY2hhbmdlSW5Qcm9ncmVzcykge1xuICAgICAgICAgICAgICAgIHRoaXMuX2NoYW5nZUluUHJvZ3Jlc3MgPSB0cnVlO1xuICAgICAgICAgICAgICAgIGhvb2tzLnVwZGF0ZU9mZnNldCh0aGlzLCB0cnVlKTtcbiAgICAgICAgICAgICAgICB0aGlzLl9jaGFuZ2VJblByb2dyZXNzID0gbnVsbDtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9IGVsc2Uge1xuICAgICAgICByZXR1cm4gdGhpcy5faXNVVEMgPyBvZmZzZXQgOiBnZXREYXRlT2Zmc2V0KHRoaXMpO1xuICAgIH1cbn1cblxuZnVuY3Rpb24gZ2V0U2V0Wm9uZSAoaW5wdXQsIGtlZXBMb2NhbFRpbWUpIHtcbiAgICBpZiAoaW5wdXQgIT0gbnVsbCkge1xuICAgICAgICBpZiAodHlwZW9mIGlucHV0ICE9PSAnc3RyaW5nJykge1xuICAgICAgICAgICAgaW5wdXQgPSAtaW5wdXQ7XG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLnV0Y09mZnNldChpbnB1dCwga2VlcExvY2FsVGltZSk7XG5cbiAgICAgICAgcmV0dXJuIHRoaXM7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgcmV0dXJuIC10aGlzLnV0Y09mZnNldCgpO1xuICAgIH1cbn1cblxuZnVuY3Rpb24gc2V0T2Zmc2V0VG9VVEMgKGtlZXBMb2NhbFRpbWUpIHtcbiAgICByZXR1cm4gdGhpcy51dGNPZmZzZXQoMCwga2VlcExvY2FsVGltZSk7XG59XG5cbmZ1bmN0aW9uIHNldE9mZnNldFRvTG9jYWwgKGtlZXBMb2NhbFRpbWUpIHtcbiAgICBpZiAodGhpcy5faXNVVEMpIHtcbiAgICAgICAgdGhpcy51dGNPZmZzZXQoMCwga2VlcExvY2FsVGltZSk7XG4gICAgICAgIHRoaXMuX2lzVVRDID0gZmFsc2U7XG5cbiAgICAgICAgaWYgKGtlZXBMb2NhbFRpbWUpIHtcbiAgICAgICAgICAgIHRoaXMuc3VidHJhY3QoZ2V0RGF0ZU9mZnNldCh0aGlzKSwgJ20nKTtcbiAgICAgICAgfVxuICAgIH1cbiAgICByZXR1cm4gdGhpcztcbn1cblxuZnVuY3Rpb24gc2V0T2Zmc2V0VG9QYXJzZWRPZmZzZXQgKCkge1xuICAgIGlmICh0aGlzLl90em0gIT0gbnVsbCkge1xuICAgICAgICB0aGlzLnV0Y09mZnNldCh0aGlzLl90em0sIGZhbHNlLCB0cnVlKTtcbiAgICB9IGVsc2UgaWYgKHR5cGVvZiB0aGlzLl9pID09PSAnc3RyaW5nJykge1xuICAgICAgICB2YXIgdFpvbmUgPSBvZmZzZXRGcm9tU3RyaW5nKG1hdGNoT2Zmc2V0LCB0aGlzLl9pKTtcbiAgICAgICAgaWYgKHRab25lICE9IG51bGwpIHtcbiAgICAgICAgICAgIHRoaXMudXRjT2Zmc2V0KHRab25lKTtcbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgIHRoaXMudXRjT2Zmc2V0KDAsIHRydWUpO1xuICAgICAgICB9XG4gICAgfVxuICAgIHJldHVybiB0aGlzO1xufVxuXG5mdW5jdGlvbiBoYXNBbGlnbmVkSG91ck9mZnNldCAoaW5wdXQpIHtcbiAgICBpZiAoIXRoaXMuaXNWYWxpZCgpKSB7XG4gICAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9XG4gICAgaW5wdXQgPSBpbnB1dCA/IGNyZWF0ZUxvY2FsKGlucHV0KS51dGNPZmZzZXQoKSA6IDA7XG5cbiAgICByZXR1cm4gKHRoaXMudXRjT2Zmc2V0KCkgLSBpbnB1dCkgJSA2MCA9PT0gMDtcbn1cblxuZnVuY3Rpb24gaXNEYXlsaWdodFNhdmluZ1RpbWUgKCkge1xuICAgIHJldHVybiAoXG4gICAgICAgIHRoaXMudXRjT2Zmc2V0KCkgPiB0aGlzLmNsb25lKCkubW9udGgoMCkudXRjT2Zmc2V0KCkgfHxcbiAgICAgICAgdGhpcy51dGNPZmZzZXQoKSA+IHRoaXMuY2xvbmUoKS5tb250aCg1KS51dGNPZmZzZXQoKVxuICAgICk7XG59XG5cbmZ1bmN0aW9uIGlzRGF5bGlnaHRTYXZpbmdUaW1lU2hpZnRlZCAoKSB7XG4gICAgaWYgKCFpc1VuZGVmaW5lZCh0aGlzLl9pc0RTVFNoaWZ0ZWQpKSB7XG4gICAgICAgIHJldHVybiB0aGlzLl9pc0RTVFNoaWZ0ZWQ7XG4gICAgfVxuXG4gICAgdmFyIGMgPSB7fTtcblxuICAgIGNvcHlDb25maWcoYywgdGhpcyk7XG4gICAgYyA9IHByZXBhcmVDb25maWcoYyk7XG5cbiAgICBpZiAoYy5fYSkge1xuICAgICAgICB2YXIgb3RoZXIgPSBjLl9pc1VUQyA/IGNyZWF0ZVVUQyhjLl9hKSA6IGNyZWF0ZUxvY2FsKGMuX2EpO1xuICAgICAgICB0aGlzLl9pc0RTVFNoaWZ0ZWQgPSB0aGlzLmlzVmFsaWQoKSAmJlxuICAgICAgICAgICAgY29tcGFyZUFycmF5cyhjLl9hLCBvdGhlci50b0FycmF5KCkpID4gMDtcbiAgICB9IGVsc2Uge1xuICAgICAgICB0aGlzLl9pc0RTVFNoaWZ0ZWQgPSBmYWxzZTtcbiAgICB9XG5cbiAgICByZXR1cm4gdGhpcy5faXNEU1RTaGlmdGVkO1xufVxuXG5mdW5jdGlvbiBpc0xvY2FsICgpIHtcbiAgICByZXR1cm4gdGhpcy5pc1ZhbGlkKCkgPyAhdGhpcy5faXNVVEMgOiBmYWxzZTtcbn1cblxuZnVuY3Rpb24gaXNVdGNPZmZzZXQgKCkge1xuICAgIHJldHVybiB0aGlzLmlzVmFsaWQoKSA/IHRoaXMuX2lzVVRDIDogZmFsc2U7XG59XG5cbmZ1bmN0aW9uIGlzVXRjICgpIHtcbiAgICByZXR1cm4gdGhpcy5pc1ZhbGlkKCkgPyB0aGlzLl9pc1VUQyAmJiB0aGlzLl9vZmZzZXQgPT09IDAgOiBmYWxzZTtcbn1cblxuLy8gQVNQLk5FVCBqc29uIGRhdGUgZm9ybWF0IHJlZ2V4XG52YXIgYXNwTmV0UmVnZXggPSAvXihcXC18XFwrKT8oPzooXFxkKilbLiBdKT8oXFxkKylcXDooXFxkKykoPzpcXDooXFxkKykoXFwuXFxkKik/KT8kLztcblxuLy8gZnJvbSBodHRwOi8vZG9jcy5jbG9zdXJlLWxpYnJhcnkuZ29vZ2xlY29kZS5jb20vZ2l0L2Nsb3N1cmVfZ29vZ19kYXRlX2RhdGUuanMuc291cmNlLmh0bWxcbi8vIHNvbWV3aGF0IG1vcmUgaW4gbGluZSB3aXRoIDQuNC4zLjIgMjAwNCBzcGVjLCBidXQgYWxsb3dzIGRlY2ltYWwgYW55d2hlcmVcbi8vIGFuZCBmdXJ0aGVyIG1vZGlmaWVkIHRvIGFsbG93IGZvciBzdHJpbmdzIGNvbnRhaW5pbmcgYm90aCB3ZWVrIGFuZCBkYXlcbnZhciBpc29SZWdleCA9IC9eKC18XFwrKT9QKD86KFstK10/WzAtOSwuXSopWSk/KD86KFstK10/WzAtOSwuXSopTSk/KD86KFstK10/WzAtOSwuXSopVyk/KD86KFstK10/WzAtOSwuXSopRCk/KD86VCg/OihbLStdP1swLTksLl0qKUgpPyg/OihbLStdP1swLTksLl0qKU0pPyg/OihbLStdP1swLTksLl0qKVMpPyk/JC87XG5cbmZ1bmN0aW9uIGNyZWF0ZUR1cmF0aW9uIChpbnB1dCwga2V5KSB7XG4gICAgdmFyIGR1cmF0aW9uID0gaW5wdXQsXG4gICAgICAgIC8vIG1hdGNoaW5nIGFnYWluc3QgcmVnZXhwIGlzIGV4cGVuc2l2ZSwgZG8gaXQgb24gZGVtYW5kXG4gICAgICAgIG1hdGNoID0gbnVsbCxcbiAgICAgICAgc2lnbixcbiAgICAgICAgcmV0LFxuICAgICAgICBkaWZmUmVzO1xuXG4gICAgaWYgKGlzRHVyYXRpb24oaW5wdXQpKSB7XG4gICAgICAgIGR1cmF0aW9uID0ge1xuICAgICAgICAgICAgbXMgOiBpbnB1dC5fbWlsbGlzZWNvbmRzLFxuICAgICAgICAgICAgZCAgOiBpbnB1dC5fZGF5cyxcbiAgICAgICAgICAgIE0gIDogaW5wdXQuX21vbnRoc1xuICAgICAgICB9O1xuICAgIH0gZWxzZSBpZiAoaXNOdW1iZXIoaW5wdXQpKSB7XG4gICAgICAgIGR1cmF0aW9uID0ge307XG4gICAgICAgIGlmIChrZXkpIHtcbiAgICAgICAgICAgIGR1cmF0aW9uW2tleV0gPSBpbnB1dDtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIGR1cmF0aW9uLm1pbGxpc2Vjb25kcyA9IGlucHV0O1xuICAgICAgICB9XG4gICAgfSBlbHNlIGlmICghIShtYXRjaCA9IGFzcE5ldFJlZ2V4LmV4ZWMoaW5wdXQpKSkge1xuICAgICAgICBzaWduID0gKG1hdGNoWzFdID09PSAnLScpID8gLTEgOiAxO1xuICAgICAgICBkdXJhdGlvbiA9IHtcbiAgICAgICAgICAgIHkgIDogMCxcbiAgICAgICAgICAgIGQgIDogdG9JbnQobWF0Y2hbREFURV0pICAgICAgICAgICAgICAgICAgICAgICAgICogc2lnbixcbiAgICAgICAgICAgIGggIDogdG9JbnQobWF0Y2hbSE9VUl0pICAgICAgICAgICAgICAgICAgICAgICAgICogc2lnbixcbiAgICAgICAgICAgIG0gIDogdG9JbnQobWF0Y2hbTUlOVVRFXSkgICAgICAgICAgICAgICAgICAgICAgICogc2lnbixcbiAgICAgICAgICAgIHMgIDogdG9JbnQobWF0Y2hbU0VDT05EXSkgICAgICAgICAgICAgICAgICAgICAgICogc2lnbixcbiAgICAgICAgICAgIG1zIDogdG9JbnQoYWJzUm91bmQobWF0Y2hbTUlMTElTRUNPTkRdICogMTAwMCkpICogc2lnbiAvLyB0aGUgbWlsbGlzZWNvbmQgZGVjaW1hbCBwb2ludCBpcyBpbmNsdWRlZCBpbiB0aGUgbWF0Y2hcbiAgICAgICAgfTtcbiAgICB9IGVsc2UgaWYgKCEhKG1hdGNoID0gaXNvUmVnZXguZXhlYyhpbnB1dCkpKSB7XG4gICAgICAgIHNpZ24gPSAobWF0Y2hbMV0gPT09ICctJykgPyAtMSA6IChtYXRjaFsxXSA9PT0gJysnKSA/IDEgOiAxO1xuICAgICAgICBkdXJhdGlvbiA9IHtcbiAgICAgICAgICAgIHkgOiBwYXJzZUlzbyhtYXRjaFsyXSwgc2lnbiksXG4gICAgICAgICAgICBNIDogcGFyc2VJc28obWF0Y2hbM10sIHNpZ24pLFxuICAgICAgICAgICAgdyA6IHBhcnNlSXNvKG1hdGNoWzRdLCBzaWduKSxcbiAgICAgICAgICAgIGQgOiBwYXJzZUlzbyhtYXRjaFs1XSwgc2lnbiksXG4gICAgICAgICAgICBoIDogcGFyc2VJc28obWF0Y2hbNl0sIHNpZ24pLFxuICAgICAgICAgICAgbSA6IHBhcnNlSXNvKG1hdGNoWzddLCBzaWduKSxcbiAgICAgICAgICAgIHMgOiBwYXJzZUlzbyhtYXRjaFs4XSwgc2lnbilcbiAgICAgICAgfTtcbiAgICB9IGVsc2UgaWYgKGR1cmF0aW9uID09IG51bGwpIHsvLyBjaGVja3MgZm9yIG51bGwgb3IgdW5kZWZpbmVkXG4gICAgICAgIGR1cmF0aW9uID0ge307XG4gICAgfSBlbHNlIGlmICh0eXBlb2YgZHVyYXRpb24gPT09ICdvYmplY3QnICYmICgnZnJvbScgaW4gZHVyYXRpb24gfHwgJ3RvJyBpbiBkdXJhdGlvbikpIHtcbiAgICAgICAgZGlmZlJlcyA9IG1vbWVudHNEaWZmZXJlbmNlKGNyZWF0ZUxvY2FsKGR1cmF0aW9uLmZyb20pLCBjcmVhdGVMb2NhbChkdXJhdGlvbi50bykpO1xuXG4gICAgICAgIGR1cmF0aW9uID0ge307XG4gICAgICAgIGR1cmF0aW9uLm1zID0gZGlmZlJlcy5taWxsaXNlY29uZHM7XG4gICAgICAgIGR1cmF0aW9uLk0gPSBkaWZmUmVzLm1vbnRocztcbiAgICB9XG5cbiAgICByZXQgPSBuZXcgRHVyYXRpb24oZHVyYXRpb24pO1xuXG4gICAgaWYgKGlzRHVyYXRpb24oaW5wdXQpICYmIGhhc093blByb3AoaW5wdXQsICdfbG9jYWxlJykpIHtcbiAgICAgICAgcmV0Ll9sb2NhbGUgPSBpbnB1dC5fbG9jYWxlO1xuICAgIH1cblxuICAgIHJldHVybiByZXQ7XG59XG5cbmNyZWF0ZUR1cmF0aW9uLmZuID0gRHVyYXRpb24ucHJvdG90eXBlO1xuY3JlYXRlRHVyYXRpb24uaW52YWxpZCA9IGNyZWF0ZUludmFsaWQkMTtcblxuZnVuY3Rpb24gcGFyc2VJc28gKGlucCwgc2lnbikge1xuICAgIC8vIFdlJ2Qgbm9ybWFsbHkgdXNlIH5+aW5wIGZvciB0aGlzLCBidXQgdW5mb3J0dW5hdGVseSBpdCBhbHNvXG4gICAgLy8gY29udmVydHMgZmxvYXRzIHRvIGludHMuXG4gICAgLy8gaW5wIG1heSBiZSB1bmRlZmluZWQsIHNvIGNhcmVmdWwgY2FsbGluZyByZXBsYWNlIG9uIGl0LlxuICAgIHZhciByZXMgPSBpbnAgJiYgcGFyc2VGbG9hdChpbnAucmVwbGFjZSgnLCcsICcuJykpO1xuICAgIC8vIGFwcGx5IHNpZ24gd2hpbGUgd2UncmUgYXQgaXRcbiAgICByZXR1cm4gKGlzTmFOKHJlcykgPyAwIDogcmVzKSAqIHNpZ247XG59XG5cbmZ1bmN0aW9uIHBvc2l0aXZlTW9tZW50c0RpZmZlcmVuY2UoYmFzZSwgb3RoZXIpIHtcbiAgICB2YXIgcmVzID0ge21pbGxpc2Vjb25kczogMCwgbW9udGhzOiAwfTtcblxuICAgIHJlcy5tb250aHMgPSBvdGhlci5tb250aCgpIC0gYmFzZS5tb250aCgpICtcbiAgICAgICAgKG90aGVyLnllYXIoKSAtIGJhc2UueWVhcigpKSAqIDEyO1xuICAgIGlmIChiYXNlLmNsb25lKCkuYWRkKHJlcy5tb250aHMsICdNJykuaXNBZnRlcihvdGhlcikpIHtcbiAgICAgICAgLS1yZXMubW9udGhzO1xuICAgIH1cblxuICAgIHJlcy5taWxsaXNlY29uZHMgPSArb3RoZXIgLSArKGJhc2UuY2xvbmUoKS5hZGQocmVzLm1vbnRocywgJ00nKSk7XG5cbiAgICByZXR1cm4gcmVzO1xufVxuXG5mdW5jdGlvbiBtb21lbnRzRGlmZmVyZW5jZShiYXNlLCBvdGhlcikge1xuICAgIHZhciByZXM7XG4gICAgaWYgKCEoYmFzZS5pc1ZhbGlkKCkgJiYgb3RoZXIuaXNWYWxpZCgpKSkge1xuICAgICAgICByZXR1cm4ge21pbGxpc2Vjb25kczogMCwgbW9udGhzOiAwfTtcbiAgICB9XG5cbiAgICBvdGhlciA9IGNsb25lV2l0aE9mZnNldChvdGhlciwgYmFzZSk7XG4gICAgaWYgKGJhc2UuaXNCZWZvcmUob3RoZXIpKSB7XG4gICAgICAgIHJlcyA9IHBvc2l0aXZlTW9tZW50c0RpZmZlcmVuY2UoYmFzZSwgb3RoZXIpO1xuICAgIH0gZWxzZSB7XG4gICAgICAgIHJlcyA9IHBvc2l0aXZlTW9tZW50c0RpZmZlcmVuY2Uob3RoZXIsIGJhc2UpO1xuICAgICAgICByZXMubWlsbGlzZWNvbmRzID0gLXJlcy5taWxsaXNlY29uZHM7XG4gICAgICAgIHJlcy5tb250aHMgPSAtcmVzLm1vbnRocztcbiAgICB9XG5cbiAgICByZXR1cm4gcmVzO1xufVxuXG4vLyBUT0RPOiByZW1vdmUgJ25hbWUnIGFyZyBhZnRlciBkZXByZWNhdGlvbiBpcyByZW1vdmVkXG5mdW5jdGlvbiBjcmVhdGVBZGRlcihkaXJlY3Rpb24sIG5hbWUpIHtcbiAgICByZXR1cm4gZnVuY3Rpb24gKHZhbCwgcGVyaW9kKSB7XG4gICAgICAgIHZhciBkdXIsIHRtcDtcbiAgICAgICAgLy9pbnZlcnQgdGhlIGFyZ3VtZW50cywgYnV0IGNvbXBsYWluIGFib3V0IGl0XG4gICAgICAgIGlmIChwZXJpb2QgIT09IG51bGwgJiYgIWlzTmFOKCtwZXJpb2QpKSB7XG4gICAgICAgICAgICBkZXByZWNhdGVTaW1wbGUobmFtZSwgJ21vbWVudCgpLicgKyBuYW1lICArICcocGVyaW9kLCBudW1iZXIpIGlzIGRlcHJlY2F0ZWQuIFBsZWFzZSB1c2UgbW9tZW50KCkuJyArIG5hbWUgKyAnKG51bWJlciwgcGVyaW9kKS4gJyArXG4gICAgICAgICAgICAnU2VlIGh0dHA6Ly9tb21lbnRqcy5jb20vZ3VpZGVzLyMvd2FybmluZ3MvYWRkLWludmVydGVkLXBhcmFtLyBmb3IgbW9yZSBpbmZvLicpO1xuICAgICAgICAgICAgdG1wID0gdmFsOyB2YWwgPSBwZXJpb2Q7IHBlcmlvZCA9IHRtcDtcbiAgICAgICAgfVxuXG4gICAgICAgIHZhbCA9IHR5cGVvZiB2YWwgPT09ICdzdHJpbmcnID8gK3ZhbCA6IHZhbDtcbiAgICAgICAgZHVyID0gY3JlYXRlRHVyYXRpb24odmFsLCBwZXJpb2QpO1xuICAgICAgICBhZGRTdWJ0cmFjdCh0aGlzLCBkdXIsIGRpcmVjdGlvbik7XG4gICAgICAgIHJldHVybiB0aGlzO1xuICAgIH07XG59XG5cbmZ1bmN0aW9uIGFkZFN1YnRyYWN0IChtb20sIGR1cmF0aW9uLCBpc0FkZGluZywgdXBkYXRlT2Zmc2V0KSB7XG4gICAgdmFyIG1pbGxpc2Vjb25kcyA9IGR1cmF0aW9uLl9taWxsaXNlY29uZHMsXG4gICAgICAgIGRheXMgPSBhYnNSb3VuZChkdXJhdGlvbi5fZGF5cyksXG4gICAgICAgIG1vbnRocyA9IGFic1JvdW5kKGR1cmF0aW9uLl9tb250aHMpO1xuXG4gICAgaWYgKCFtb20uaXNWYWxpZCgpKSB7XG4gICAgICAgIC8vIE5vIG9wXG4gICAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICB1cGRhdGVPZmZzZXQgPSB1cGRhdGVPZmZzZXQgPT0gbnVsbCA/IHRydWUgOiB1cGRhdGVPZmZzZXQ7XG5cbiAgICBpZiAobW9udGhzKSB7XG4gICAgICAgIHNldE1vbnRoKG1vbSwgZ2V0KG1vbSwgJ01vbnRoJykgKyBtb250aHMgKiBpc0FkZGluZyk7XG4gICAgfVxuICAgIGlmIChkYXlzKSB7XG4gICAgICAgIHNldCQxKG1vbSwgJ0RhdGUnLCBnZXQobW9tLCAnRGF0ZScpICsgZGF5cyAqIGlzQWRkaW5nKTtcbiAgICB9XG4gICAgaWYgKG1pbGxpc2Vjb25kcykge1xuICAgICAgICBtb20uX2Quc2V0VGltZShtb20uX2QudmFsdWVPZigpICsgbWlsbGlzZWNvbmRzICogaXNBZGRpbmcpO1xuICAgIH1cbiAgICBpZiAodXBkYXRlT2Zmc2V0KSB7XG4gICAgICAgIGhvb2tzLnVwZGF0ZU9mZnNldChtb20sIGRheXMgfHwgbW9udGhzKTtcbiAgICB9XG59XG5cbnZhciBhZGQgICAgICA9IGNyZWF0ZUFkZGVyKDEsICdhZGQnKTtcbnZhciBzdWJ0cmFjdCA9IGNyZWF0ZUFkZGVyKC0xLCAnc3VidHJhY3QnKTtcblxuZnVuY3Rpb24gZ2V0Q2FsZW5kYXJGb3JtYXQobXlNb21lbnQsIG5vdykge1xuICAgIHZhciBkaWZmID0gbXlNb21lbnQuZGlmZihub3csICdkYXlzJywgdHJ1ZSk7XG4gICAgcmV0dXJuIGRpZmYgPCAtNiA/ICdzYW1lRWxzZScgOlxuICAgICAgICAgICAgZGlmZiA8IC0xID8gJ2xhc3RXZWVrJyA6XG4gICAgICAgICAgICBkaWZmIDwgMCA/ICdsYXN0RGF5JyA6XG4gICAgICAgICAgICBkaWZmIDwgMSA/ICdzYW1lRGF5JyA6XG4gICAgICAgICAgICBkaWZmIDwgMiA/ICduZXh0RGF5JyA6XG4gICAgICAgICAgICBkaWZmIDwgNyA/ICduZXh0V2VlaycgOiAnc2FtZUVsc2UnO1xufVxuXG5mdW5jdGlvbiBjYWxlbmRhciQxICh0aW1lLCBmb3JtYXRzKSB7XG4gICAgLy8gV2Ugd2FudCB0byBjb21wYXJlIHRoZSBzdGFydCBvZiB0b2RheSwgdnMgdGhpcy5cbiAgICAvLyBHZXR0aW5nIHN0YXJ0LW9mLXRvZGF5IGRlcGVuZHMgb24gd2hldGhlciB3ZSdyZSBsb2NhbC91dGMvb2Zmc2V0IG9yIG5vdC5cbiAgICB2YXIgbm93ID0gdGltZSB8fCBjcmVhdGVMb2NhbCgpLFxuICAgICAgICBzb2QgPSBjbG9uZVdpdGhPZmZzZXQobm93LCB0aGlzKS5zdGFydE9mKCdkYXknKSxcbiAgICAgICAgZm9ybWF0ID0gaG9va3MuY2FsZW5kYXJGb3JtYXQodGhpcywgc29kKSB8fCAnc2FtZUVsc2UnO1xuXG4gICAgdmFyIG91dHB1dCA9IGZvcm1hdHMgJiYgKGlzRnVuY3Rpb24oZm9ybWF0c1tmb3JtYXRdKSA/IGZvcm1hdHNbZm9ybWF0XS5jYWxsKHRoaXMsIG5vdykgOiBmb3JtYXRzW2Zvcm1hdF0pO1xuXG4gICAgcmV0dXJuIHRoaXMuZm9ybWF0KG91dHB1dCB8fCB0aGlzLmxvY2FsZURhdGEoKS5jYWxlbmRhcihmb3JtYXQsIHRoaXMsIGNyZWF0ZUxvY2FsKG5vdykpKTtcbn1cblxuZnVuY3Rpb24gY2xvbmUgKCkge1xuICAgIHJldHVybiBuZXcgTW9tZW50KHRoaXMpO1xufVxuXG5mdW5jdGlvbiBpc0FmdGVyIChpbnB1dCwgdW5pdHMpIHtcbiAgICB2YXIgbG9jYWxJbnB1dCA9IGlzTW9tZW50KGlucHV0KSA/IGlucHV0IDogY3JlYXRlTG9jYWwoaW5wdXQpO1xuICAgIGlmICghKHRoaXMuaXNWYWxpZCgpICYmIGxvY2FsSW5wdXQuaXNWYWxpZCgpKSkge1xuICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgfVxuICAgIHVuaXRzID0gbm9ybWFsaXplVW5pdHMoIWlzVW5kZWZpbmVkKHVuaXRzKSA/IHVuaXRzIDogJ21pbGxpc2Vjb25kJyk7XG4gICAgaWYgKHVuaXRzID09PSAnbWlsbGlzZWNvbmQnKSB7XG4gICAgICAgIHJldHVybiB0aGlzLnZhbHVlT2YoKSA+IGxvY2FsSW5wdXQudmFsdWVPZigpO1xuICAgIH0gZWxzZSB7XG4gICAgICAgIHJldHVybiBsb2NhbElucHV0LnZhbHVlT2YoKSA8IHRoaXMuY2xvbmUoKS5zdGFydE9mKHVuaXRzKS52YWx1ZU9mKCk7XG4gICAgfVxufVxuXG5mdW5jdGlvbiBpc0JlZm9yZSAoaW5wdXQsIHVuaXRzKSB7XG4gICAgdmFyIGxvY2FsSW5wdXQgPSBpc01vbWVudChpbnB1dCkgPyBpbnB1dCA6IGNyZWF0ZUxvY2FsKGlucHV0KTtcbiAgICBpZiAoISh0aGlzLmlzVmFsaWQoKSAmJiBsb2NhbElucHV0LmlzVmFsaWQoKSkpIHtcbiAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgIH1cbiAgICB1bml0cyA9IG5vcm1hbGl6ZVVuaXRzKCFpc1VuZGVmaW5lZCh1bml0cykgPyB1bml0cyA6ICdtaWxsaXNlY29uZCcpO1xuICAgIGlmICh1bml0cyA9PT0gJ21pbGxpc2Vjb25kJykge1xuICAgICAgICByZXR1cm4gdGhpcy52YWx1ZU9mKCkgPCBsb2NhbElucHV0LnZhbHVlT2YoKTtcbiAgICB9IGVsc2Uge1xuICAgICAgICByZXR1cm4gdGhpcy5jbG9uZSgpLmVuZE9mKHVuaXRzKS52YWx1ZU9mKCkgPCBsb2NhbElucHV0LnZhbHVlT2YoKTtcbiAgICB9XG59XG5cbmZ1bmN0aW9uIGlzQmV0d2VlbiAoZnJvbSwgdG8sIHVuaXRzLCBpbmNsdXNpdml0eSkge1xuICAgIGluY2x1c2l2aXR5ID0gaW5jbHVzaXZpdHkgfHwgJygpJztcbiAgICByZXR1cm4gKGluY2x1c2l2aXR5WzBdID09PSAnKCcgPyB0aGlzLmlzQWZ0ZXIoZnJvbSwgdW5pdHMpIDogIXRoaXMuaXNCZWZvcmUoZnJvbSwgdW5pdHMpKSAmJlxuICAgICAgICAoaW5jbHVzaXZpdHlbMV0gPT09ICcpJyA/IHRoaXMuaXNCZWZvcmUodG8sIHVuaXRzKSA6ICF0aGlzLmlzQWZ0ZXIodG8sIHVuaXRzKSk7XG59XG5cbmZ1bmN0aW9uIGlzU2FtZSAoaW5wdXQsIHVuaXRzKSB7XG4gICAgdmFyIGxvY2FsSW5wdXQgPSBpc01vbWVudChpbnB1dCkgPyBpbnB1dCA6IGNyZWF0ZUxvY2FsKGlucHV0KSxcbiAgICAgICAgaW5wdXRNcztcbiAgICBpZiAoISh0aGlzLmlzVmFsaWQoKSAmJiBsb2NhbElucHV0LmlzVmFsaWQoKSkpIHtcbiAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgIH1cbiAgICB1bml0cyA9IG5vcm1hbGl6ZVVuaXRzKHVuaXRzIHx8ICdtaWxsaXNlY29uZCcpO1xuICAgIGlmICh1bml0cyA9PT0gJ21pbGxpc2Vjb25kJykge1xuICAgICAgICByZXR1cm4gdGhpcy52YWx1ZU9mKCkgPT09IGxvY2FsSW5wdXQudmFsdWVPZigpO1xuICAgIH0gZWxzZSB7XG4gICAgICAgIGlucHV0TXMgPSBsb2NhbElucHV0LnZhbHVlT2YoKTtcbiAgICAgICAgcmV0dXJuIHRoaXMuY2xvbmUoKS5zdGFydE9mKHVuaXRzKS52YWx1ZU9mKCkgPD0gaW5wdXRNcyAmJiBpbnB1dE1zIDw9IHRoaXMuY2xvbmUoKS5lbmRPZih1bml0cykudmFsdWVPZigpO1xuICAgIH1cbn1cblxuZnVuY3Rpb24gaXNTYW1lT3JBZnRlciAoaW5wdXQsIHVuaXRzKSB7XG4gICAgcmV0dXJuIHRoaXMuaXNTYW1lKGlucHV0LCB1bml0cykgfHwgdGhpcy5pc0FmdGVyKGlucHV0LHVuaXRzKTtcbn1cblxuZnVuY3Rpb24gaXNTYW1lT3JCZWZvcmUgKGlucHV0LCB1bml0cykge1xuICAgIHJldHVybiB0aGlzLmlzU2FtZShpbnB1dCwgdW5pdHMpIHx8IHRoaXMuaXNCZWZvcmUoaW5wdXQsdW5pdHMpO1xufVxuXG5mdW5jdGlvbiBkaWZmIChpbnB1dCwgdW5pdHMsIGFzRmxvYXQpIHtcbiAgICB2YXIgdGhhdCxcbiAgICAgICAgem9uZURlbHRhLFxuICAgICAgICBkZWx0YSwgb3V0cHV0O1xuXG4gICAgaWYgKCF0aGlzLmlzVmFsaWQoKSkge1xuICAgICAgICByZXR1cm4gTmFOO1xuICAgIH1cblxuICAgIHRoYXQgPSBjbG9uZVdpdGhPZmZzZXQoaW5wdXQsIHRoaXMpO1xuXG4gICAgaWYgKCF0aGF0LmlzVmFsaWQoKSkge1xuICAgICAgICByZXR1cm4gTmFOO1xuICAgIH1cblxuICAgIHpvbmVEZWx0YSA9ICh0aGF0LnV0Y09mZnNldCgpIC0gdGhpcy51dGNPZmZzZXQoKSkgKiA2ZTQ7XG5cbiAgICB1bml0cyA9IG5vcm1hbGl6ZVVuaXRzKHVuaXRzKTtcblxuICAgIHN3aXRjaCAodW5pdHMpIHtcbiAgICAgICAgY2FzZSAneWVhcic6IG91dHB1dCA9IG1vbnRoRGlmZih0aGlzLCB0aGF0KSAvIDEyOyBicmVhaztcbiAgICAgICAgY2FzZSAnbW9udGgnOiBvdXRwdXQgPSBtb250aERpZmYodGhpcywgdGhhdCk7IGJyZWFrO1xuICAgICAgICBjYXNlICdxdWFydGVyJzogb3V0cHV0ID0gbW9udGhEaWZmKHRoaXMsIHRoYXQpIC8gMzsgYnJlYWs7XG4gICAgICAgIGNhc2UgJ3NlY29uZCc6IG91dHB1dCA9ICh0aGlzIC0gdGhhdCkgLyAxZTM7IGJyZWFrOyAvLyAxMDAwXG4gICAgICAgIGNhc2UgJ21pbnV0ZSc6IG91dHB1dCA9ICh0aGlzIC0gdGhhdCkgLyA2ZTQ7IGJyZWFrOyAvLyAxMDAwICogNjBcbiAgICAgICAgY2FzZSAnaG91cic6IG91dHB1dCA9ICh0aGlzIC0gdGhhdCkgLyAzNmU1OyBicmVhazsgLy8gMTAwMCAqIDYwICogNjBcbiAgICAgICAgY2FzZSAnZGF5Jzogb3V0cHV0ID0gKHRoaXMgLSB0aGF0IC0gem9uZURlbHRhKSAvIDg2NGU1OyBicmVhazsgLy8gMTAwMCAqIDYwICogNjAgKiAyNCwgbmVnYXRlIGRzdFxuICAgICAgICBjYXNlICd3ZWVrJzogb3V0cHV0ID0gKHRoaXMgLSB0aGF0IC0gem9uZURlbHRhKSAvIDYwNDhlNTsgYnJlYWs7IC8vIDEwMDAgKiA2MCAqIDYwICogMjQgKiA3LCBuZWdhdGUgZHN0XG4gICAgICAgIGRlZmF1bHQ6IG91dHB1dCA9IHRoaXMgLSB0aGF0O1xuICAgIH1cblxuICAgIHJldHVybiBhc0Zsb2F0ID8gb3V0cHV0IDogYWJzRmxvb3Iob3V0cHV0KTtcbn1cblxuZnVuY3Rpb24gbW9udGhEaWZmIChhLCBiKSB7XG4gICAgLy8gZGlmZmVyZW5jZSBpbiBtb250aHNcbiAgICB2YXIgd2hvbGVNb250aERpZmYgPSAoKGIueWVhcigpIC0gYS55ZWFyKCkpICogMTIpICsgKGIubW9udGgoKSAtIGEubW9udGgoKSksXG4gICAgICAgIC8vIGIgaXMgaW4gKGFuY2hvciAtIDEgbW9udGgsIGFuY2hvciArIDEgbW9udGgpXG4gICAgICAgIGFuY2hvciA9IGEuY2xvbmUoKS5hZGQod2hvbGVNb250aERpZmYsICdtb250aHMnKSxcbiAgICAgICAgYW5jaG9yMiwgYWRqdXN0O1xuXG4gICAgaWYgKGIgLSBhbmNob3IgPCAwKSB7XG4gICAgICAgIGFuY2hvcjIgPSBhLmNsb25lKCkuYWRkKHdob2xlTW9udGhEaWZmIC0gMSwgJ21vbnRocycpO1xuICAgICAgICAvLyBsaW5lYXIgYWNyb3NzIHRoZSBtb250aFxuICAgICAgICBhZGp1c3QgPSAoYiAtIGFuY2hvcikgLyAoYW5jaG9yIC0gYW5jaG9yMik7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgYW5jaG9yMiA9IGEuY2xvbmUoKS5hZGQod2hvbGVNb250aERpZmYgKyAxLCAnbW9udGhzJyk7XG4gICAgICAgIC8vIGxpbmVhciBhY3Jvc3MgdGhlIG1vbnRoXG4gICAgICAgIGFkanVzdCA9IChiIC0gYW5jaG9yKSAvIChhbmNob3IyIC0gYW5jaG9yKTtcbiAgICB9XG5cbiAgICAvL2NoZWNrIGZvciBuZWdhdGl2ZSB6ZXJvLCByZXR1cm4gemVybyBpZiBuZWdhdGl2ZSB6ZXJvXG4gICAgcmV0dXJuIC0od2hvbGVNb250aERpZmYgKyBhZGp1c3QpIHx8IDA7XG59XG5cbmhvb2tzLmRlZmF1bHRGb3JtYXQgPSAnWVlZWS1NTS1ERFRISDptbTpzc1onO1xuaG9va3MuZGVmYXVsdEZvcm1hdFV0YyA9ICdZWVlZLU1NLUREVEhIOm1tOnNzW1pdJztcblxuZnVuY3Rpb24gdG9TdHJpbmcgKCkge1xuICAgIHJldHVybiB0aGlzLmNsb25lKCkubG9jYWxlKCdlbicpLmZvcm1hdCgnZGRkIE1NTSBERCBZWVlZIEhIOm1tOnNzIFtHTVRdWlonKTtcbn1cblxuZnVuY3Rpb24gdG9JU09TdHJpbmcoKSB7XG4gICAgaWYgKCF0aGlzLmlzVmFsaWQoKSkge1xuICAgICAgICByZXR1cm4gbnVsbDtcbiAgICB9XG4gICAgdmFyIG0gPSB0aGlzLmNsb25lKCkudXRjKCk7XG4gICAgaWYgKG0ueWVhcigpIDwgMCB8fCBtLnllYXIoKSA+IDk5OTkpIHtcbiAgICAgICAgcmV0dXJuIGZvcm1hdE1vbWVudChtLCAnWVlZWVlZLU1NLUREW1RdSEg6bW06c3MuU1NTW1pdJyk7XG4gICAgfVxuICAgIGlmIChpc0Z1bmN0aW9uKERhdGUucHJvdG90eXBlLnRvSVNPU3RyaW5nKSkge1xuICAgICAgICAvLyBuYXRpdmUgaW1wbGVtZW50YXRpb24gaXMgfjUweCBmYXN0ZXIsIHVzZSBpdCB3aGVuIHdlIGNhblxuICAgICAgICByZXR1cm4gdGhpcy50b0RhdGUoKS50b0lTT1N0cmluZygpO1xuICAgIH1cbiAgICByZXR1cm4gZm9ybWF0TW9tZW50KG0sICdZWVlZLU1NLUREW1RdSEg6bW06c3MuU1NTW1pdJyk7XG59XG5cbi8qKlxuICogUmV0dXJuIGEgaHVtYW4gcmVhZGFibGUgcmVwcmVzZW50YXRpb24gb2YgYSBtb21lbnQgdGhhdCBjYW5cbiAqIGFsc28gYmUgZXZhbHVhdGVkIHRvIGdldCBhIG5ldyBtb21lbnQgd2hpY2ggaXMgdGhlIHNhbWVcbiAqXG4gKiBAbGluayBodHRwczovL25vZGVqcy5vcmcvZGlzdC9sYXRlc3QvZG9jcy9hcGkvdXRpbC5odG1sI3V0aWxfY3VzdG9tX2luc3BlY3RfZnVuY3Rpb25fb25fb2JqZWN0c1xuICovXG5mdW5jdGlvbiBpbnNwZWN0ICgpIHtcbiAgICBpZiAoIXRoaXMuaXNWYWxpZCgpKSB7XG4gICAgICAgIHJldHVybiAnbW9tZW50LmludmFsaWQoLyogJyArIHRoaXMuX2kgKyAnICovKSc7XG4gICAgfVxuICAgIHZhciBmdW5jID0gJ21vbWVudCc7XG4gICAgdmFyIHpvbmUgPSAnJztcbiAgICBpZiAoIXRoaXMuaXNMb2NhbCgpKSB7XG4gICAgICAgIGZ1bmMgPSB0aGlzLnV0Y09mZnNldCgpID09PSAwID8gJ21vbWVudC51dGMnIDogJ21vbWVudC5wYXJzZVpvbmUnO1xuICAgICAgICB6b25lID0gJ1onO1xuICAgIH1cbiAgICB2YXIgcHJlZml4ID0gJ1snICsgZnVuYyArICcoXCJdJztcbiAgICB2YXIgeWVhciA9ICgwIDw9IHRoaXMueWVhcigpICYmIHRoaXMueWVhcigpIDw9IDk5OTkpID8gJ1lZWVknIDogJ1lZWVlZWSc7XG4gICAgdmFyIGRhdGV0aW1lID0gJy1NTS1ERFtUXUhIOm1tOnNzLlNTUyc7XG4gICAgdmFyIHN1ZmZpeCA9IHpvbmUgKyAnW1wiKV0nO1xuXG4gICAgcmV0dXJuIHRoaXMuZm9ybWF0KHByZWZpeCArIHllYXIgKyBkYXRldGltZSArIHN1ZmZpeCk7XG59XG5cbmZ1bmN0aW9uIGZvcm1hdCAoaW5wdXRTdHJpbmcpIHtcbiAgICBpZiAoIWlucHV0U3RyaW5nKSB7XG4gICAgICAgIGlucHV0U3RyaW5nID0gdGhpcy5pc1V0YygpID8gaG9va3MuZGVmYXVsdEZvcm1hdFV0YyA6IGhvb2tzLmRlZmF1bHRGb3JtYXQ7XG4gICAgfVxuICAgIHZhciBvdXRwdXQgPSBmb3JtYXRNb21lbnQodGhpcywgaW5wdXRTdHJpbmcpO1xuICAgIHJldHVybiB0aGlzLmxvY2FsZURhdGEoKS5wb3N0Zm9ybWF0KG91dHB1dCk7XG59XG5cbmZ1bmN0aW9uIGZyb20gKHRpbWUsIHdpdGhvdXRTdWZmaXgpIHtcbiAgICBpZiAodGhpcy5pc1ZhbGlkKCkgJiZcbiAgICAgICAgICAgICgoaXNNb21lbnQodGltZSkgJiYgdGltZS5pc1ZhbGlkKCkpIHx8XG4gICAgICAgICAgICAgY3JlYXRlTG9jYWwodGltZSkuaXNWYWxpZCgpKSkge1xuICAgICAgICByZXR1cm4gY3JlYXRlRHVyYXRpb24oe3RvOiB0aGlzLCBmcm9tOiB0aW1lfSkubG9jYWxlKHRoaXMubG9jYWxlKCkpLmh1bWFuaXplKCF3aXRob3V0U3VmZml4KTtcbiAgICB9IGVsc2Uge1xuICAgICAgICByZXR1cm4gdGhpcy5sb2NhbGVEYXRhKCkuaW52YWxpZERhdGUoKTtcbiAgICB9XG59XG5cbmZ1bmN0aW9uIGZyb21Ob3cgKHdpdGhvdXRTdWZmaXgpIHtcbiAgICByZXR1cm4gdGhpcy5mcm9tKGNyZWF0ZUxvY2FsKCksIHdpdGhvdXRTdWZmaXgpO1xufVxuXG5mdW5jdGlvbiB0byAodGltZSwgd2l0aG91dFN1ZmZpeCkge1xuICAgIGlmICh0aGlzLmlzVmFsaWQoKSAmJlxuICAgICAgICAgICAgKChpc01vbWVudCh0aW1lKSAmJiB0aW1lLmlzVmFsaWQoKSkgfHxcbiAgICAgICAgICAgICBjcmVhdGVMb2NhbCh0aW1lKS5pc1ZhbGlkKCkpKSB7XG4gICAgICAgIHJldHVybiBjcmVhdGVEdXJhdGlvbih7ZnJvbTogdGhpcywgdG86IHRpbWV9KS5sb2NhbGUodGhpcy5sb2NhbGUoKSkuaHVtYW5pemUoIXdpdGhvdXRTdWZmaXgpO1xuICAgIH0gZWxzZSB7XG4gICAgICAgIHJldHVybiB0aGlzLmxvY2FsZURhdGEoKS5pbnZhbGlkRGF0ZSgpO1xuICAgIH1cbn1cblxuZnVuY3Rpb24gdG9Ob3cgKHdpdGhvdXRTdWZmaXgpIHtcbiAgICByZXR1cm4gdGhpcy50byhjcmVhdGVMb2NhbCgpLCB3aXRob3V0U3VmZml4KTtcbn1cblxuLy8gSWYgcGFzc2VkIGEgbG9jYWxlIGtleSwgaXQgd2lsbCBzZXQgdGhlIGxvY2FsZSBmb3IgdGhpc1xuLy8gaW5zdGFuY2UuICBPdGhlcndpc2UsIGl0IHdpbGwgcmV0dXJuIHRoZSBsb2NhbGUgY29uZmlndXJhdGlvblxuLy8gdmFyaWFibGVzIGZvciB0aGlzIGluc3RhbmNlLlxuZnVuY3Rpb24gbG9jYWxlIChrZXkpIHtcbiAgICB2YXIgbmV3TG9jYWxlRGF0YTtcblxuICAgIGlmIChrZXkgPT09IHVuZGVmaW5lZCkge1xuICAgICAgICByZXR1cm4gdGhpcy5fbG9jYWxlLl9hYmJyO1xuICAgIH0gZWxzZSB7XG4gICAgICAgIG5ld0xvY2FsZURhdGEgPSBnZXRMb2NhbGUoa2V5KTtcbiAgICAgICAgaWYgKG5ld0xvY2FsZURhdGEgIT0gbnVsbCkge1xuICAgICAgICAgICAgdGhpcy5fbG9jYWxlID0gbmV3TG9jYWxlRGF0YTtcbiAgICAgICAgfVxuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9XG59XG5cbnZhciBsYW5nID0gZGVwcmVjYXRlKFxuICAgICdtb21lbnQoKS5sYW5nKCkgaXMgZGVwcmVjYXRlZC4gSW5zdGVhZCwgdXNlIG1vbWVudCgpLmxvY2FsZURhdGEoKSB0byBnZXQgdGhlIGxhbmd1YWdlIGNvbmZpZ3VyYXRpb24uIFVzZSBtb21lbnQoKS5sb2NhbGUoKSB0byBjaGFuZ2UgbGFuZ3VhZ2VzLicsXG4gICAgZnVuY3Rpb24gKGtleSkge1xuICAgICAgICBpZiAoa2V5ID09PSB1bmRlZmluZWQpIHtcbiAgICAgICAgICAgIHJldHVybiB0aGlzLmxvY2FsZURhdGEoKTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIHJldHVybiB0aGlzLmxvY2FsZShrZXkpO1xuICAgICAgICB9XG4gICAgfVxuKTtcblxuZnVuY3Rpb24gbG9jYWxlRGF0YSAoKSB7XG4gICAgcmV0dXJuIHRoaXMuX2xvY2FsZTtcbn1cblxuZnVuY3Rpb24gc3RhcnRPZiAodW5pdHMpIHtcbiAgICB1bml0cyA9IG5vcm1hbGl6ZVVuaXRzKHVuaXRzKTtcbiAgICAvLyB0aGUgZm9sbG93aW5nIHN3aXRjaCBpbnRlbnRpb25hbGx5IG9taXRzIGJyZWFrIGtleXdvcmRzXG4gICAgLy8gdG8gdXRpbGl6ZSBmYWxsaW5nIHRocm91Z2ggdGhlIGNhc2VzLlxuICAgIHN3aXRjaCAodW5pdHMpIHtcbiAgICAgICAgY2FzZSAneWVhcic6XG4gICAgICAgICAgICB0aGlzLm1vbnRoKDApO1xuICAgICAgICAgICAgLyogZmFsbHMgdGhyb3VnaCAqL1xuICAgICAgICBjYXNlICdxdWFydGVyJzpcbiAgICAgICAgY2FzZSAnbW9udGgnOlxuICAgICAgICAgICAgdGhpcy5kYXRlKDEpO1xuICAgICAgICAgICAgLyogZmFsbHMgdGhyb3VnaCAqL1xuICAgICAgICBjYXNlICd3ZWVrJzpcbiAgICAgICAgY2FzZSAnaXNvV2Vlayc6XG4gICAgICAgIGNhc2UgJ2RheSc6XG4gICAgICAgIGNhc2UgJ2RhdGUnOlxuICAgICAgICAgICAgdGhpcy5ob3VycygwKTtcbiAgICAgICAgICAgIC8qIGZhbGxzIHRocm91Z2ggKi9cbiAgICAgICAgY2FzZSAnaG91cic6XG4gICAgICAgICAgICB0aGlzLm1pbnV0ZXMoMCk7XG4gICAgICAgICAgICAvKiBmYWxscyB0aHJvdWdoICovXG4gICAgICAgIGNhc2UgJ21pbnV0ZSc6XG4gICAgICAgICAgICB0aGlzLnNlY29uZHMoMCk7XG4gICAgICAgICAgICAvKiBmYWxscyB0aHJvdWdoICovXG4gICAgICAgIGNhc2UgJ3NlY29uZCc6XG4gICAgICAgICAgICB0aGlzLm1pbGxpc2Vjb25kcygwKTtcbiAgICB9XG5cbiAgICAvLyB3ZWVrcyBhcmUgYSBzcGVjaWFsIGNhc2VcbiAgICBpZiAodW5pdHMgPT09ICd3ZWVrJykge1xuICAgICAgICB0aGlzLndlZWtkYXkoMCk7XG4gICAgfVxuICAgIGlmICh1bml0cyA9PT0gJ2lzb1dlZWsnKSB7XG4gICAgICAgIHRoaXMuaXNvV2Vla2RheSgxKTtcbiAgICB9XG5cbiAgICAvLyBxdWFydGVycyBhcmUgYWxzbyBzcGVjaWFsXG4gICAgaWYgKHVuaXRzID09PSAncXVhcnRlcicpIHtcbiAgICAgICAgdGhpcy5tb250aChNYXRoLmZsb29yKHRoaXMubW9udGgoKSAvIDMpICogMyk7XG4gICAgfVxuXG4gICAgcmV0dXJuIHRoaXM7XG59XG5cbmZ1bmN0aW9uIGVuZE9mICh1bml0cykge1xuICAgIHVuaXRzID0gbm9ybWFsaXplVW5pdHModW5pdHMpO1xuICAgIGlmICh1bml0cyA9PT0gdW5kZWZpbmVkIHx8IHVuaXRzID09PSAnbWlsbGlzZWNvbmQnKSB7XG4gICAgICAgIHJldHVybiB0aGlzO1xuICAgIH1cblxuICAgIC8vICdkYXRlJyBpcyBhbiBhbGlhcyBmb3IgJ2RheScsIHNvIGl0IHNob3VsZCBiZSBjb25zaWRlcmVkIGFzIHN1Y2guXG4gICAgaWYgKHVuaXRzID09PSAnZGF0ZScpIHtcbiAgICAgICAgdW5pdHMgPSAnZGF5JztcbiAgICB9XG5cbiAgICByZXR1cm4gdGhpcy5zdGFydE9mKHVuaXRzKS5hZGQoMSwgKHVuaXRzID09PSAnaXNvV2VlaycgPyAnd2VlaycgOiB1bml0cykpLnN1YnRyYWN0KDEsICdtcycpO1xufVxuXG5mdW5jdGlvbiB2YWx1ZU9mICgpIHtcbiAgICByZXR1cm4gdGhpcy5fZC52YWx1ZU9mKCkgLSAoKHRoaXMuX29mZnNldCB8fCAwKSAqIDYwMDAwKTtcbn1cblxuZnVuY3Rpb24gdW5peCAoKSB7XG4gICAgcmV0dXJuIE1hdGguZmxvb3IodGhpcy52YWx1ZU9mKCkgLyAxMDAwKTtcbn1cblxuZnVuY3Rpb24gdG9EYXRlICgpIHtcbiAgICByZXR1cm4gbmV3IERhdGUodGhpcy52YWx1ZU9mKCkpO1xufVxuXG5mdW5jdGlvbiB0b0FycmF5ICgpIHtcbiAgICB2YXIgbSA9IHRoaXM7XG4gICAgcmV0dXJuIFttLnllYXIoKSwgbS5tb250aCgpLCBtLmRhdGUoKSwgbS5ob3VyKCksIG0ubWludXRlKCksIG0uc2Vjb25kKCksIG0ubWlsbGlzZWNvbmQoKV07XG59XG5cbmZ1bmN0aW9uIHRvT2JqZWN0ICgpIHtcbiAgICB2YXIgbSA9IHRoaXM7XG4gICAgcmV0dXJuIHtcbiAgICAgICAgeWVhcnM6IG0ueWVhcigpLFxuICAgICAgICBtb250aHM6IG0ubW9udGgoKSxcbiAgICAgICAgZGF0ZTogbS5kYXRlKCksXG4gICAgICAgIGhvdXJzOiBtLmhvdXJzKCksXG4gICAgICAgIG1pbnV0ZXM6IG0ubWludXRlcygpLFxuICAgICAgICBzZWNvbmRzOiBtLnNlY29uZHMoKSxcbiAgICAgICAgbWlsbGlzZWNvbmRzOiBtLm1pbGxpc2Vjb25kcygpXG4gICAgfTtcbn1cblxuZnVuY3Rpb24gdG9KU09OICgpIHtcbiAgICAvLyBuZXcgRGF0ZShOYU4pLnRvSlNPTigpID09PSBudWxsXG4gICAgcmV0dXJuIHRoaXMuaXNWYWxpZCgpID8gdGhpcy50b0lTT1N0cmluZygpIDogbnVsbDtcbn1cblxuZnVuY3Rpb24gaXNWYWxpZCQyICgpIHtcbiAgICByZXR1cm4gaXNWYWxpZCh0aGlzKTtcbn1cblxuZnVuY3Rpb24gcGFyc2luZ0ZsYWdzICgpIHtcbiAgICByZXR1cm4gZXh0ZW5kKHt9LCBnZXRQYXJzaW5nRmxhZ3ModGhpcykpO1xufVxuXG5mdW5jdGlvbiBpbnZhbGlkQXQgKCkge1xuICAgIHJldHVybiBnZXRQYXJzaW5nRmxhZ3ModGhpcykub3ZlcmZsb3c7XG59XG5cbmZ1bmN0aW9uIGNyZWF0aW9uRGF0YSgpIHtcbiAgICByZXR1cm4ge1xuICAgICAgICBpbnB1dDogdGhpcy5faSxcbiAgICAgICAgZm9ybWF0OiB0aGlzLl9mLFxuICAgICAgICBsb2NhbGU6IHRoaXMuX2xvY2FsZSxcbiAgICAgICAgaXNVVEM6IHRoaXMuX2lzVVRDLFxuICAgICAgICBzdHJpY3Q6IHRoaXMuX3N0cmljdFxuICAgIH07XG59XG5cbi8vIEZPUk1BVFRJTkdcblxuYWRkRm9ybWF0VG9rZW4oMCwgWydnZycsIDJdLCAwLCBmdW5jdGlvbiAoKSB7XG4gICAgcmV0dXJuIHRoaXMud2Vla1llYXIoKSAlIDEwMDtcbn0pO1xuXG5hZGRGb3JtYXRUb2tlbigwLCBbJ0dHJywgMl0sIDAsIGZ1bmN0aW9uICgpIHtcbiAgICByZXR1cm4gdGhpcy5pc29XZWVrWWVhcigpICUgMTAwO1xufSk7XG5cbmZ1bmN0aW9uIGFkZFdlZWtZZWFyRm9ybWF0VG9rZW4gKHRva2VuLCBnZXR0ZXIpIHtcbiAgICBhZGRGb3JtYXRUb2tlbigwLCBbdG9rZW4sIHRva2VuLmxlbmd0aF0sIDAsIGdldHRlcik7XG59XG5cbmFkZFdlZWtZZWFyRm9ybWF0VG9rZW4oJ2dnZ2cnLCAgICAgJ3dlZWtZZWFyJyk7XG5hZGRXZWVrWWVhckZvcm1hdFRva2VuKCdnZ2dnZycsICAgICd3ZWVrWWVhcicpO1xuYWRkV2Vla1llYXJGb3JtYXRUb2tlbignR0dHRycsICAnaXNvV2Vla1llYXInKTtcbmFkZFdlZWtZZWFyRm9ybWF0VG9rZW4oJ0dHR0dHJywgJ2lzb1dlZWtZZWFyJyk7XG5cbi8vIEFMSUFTRVNcblxuYWRkVW5pdEFsaWFzKCd3ZWVrWWVhcicsICdnZycpO1xuYWRkVW5pdEFsaWFzKCdpc29XZWVrWWVhcicsICdHRycpO1xuXG4vLyBQUklPUklUWVxuXG5hZGRVbml0UHJpb3JpdHkoJ3dlZWtZZWFyJywgMSk7XG5hZGRVbml0UHJpb3JpdHkoJ2lzb1dlZWtZZWFyJywgMSk7XG5cblxuLy8gUEFSU0lOR1xuXG5hZGRSZWdleFRva2VuKCdHJywgICAgICBtYXRjaFNpZ25lZCk7XG5hZGRSZWdleFRva2VuKCdnJywgICAgICBtYXRjaFNpZ25lZCk7XG5hZGRSZWdleFRva2VuKCdHRycsICAgICBtYXRjaDF0bzIsIG1hdGNoMik7XG5hZGRSZWdleFRva2VuKCdnZycsICAgICBtYXRjaDF0bzIsIG1hdGNoMik7XG5hZGRSZWdleFRva2VuKCdHR0dHJywgICBtYXRjaDF0bzQsIG1hdGNoNCk7XG5hZGRSZWdleFRva2VuKCdnZ2dnJywgICBtYXRjaDF0bzQsIG1hdGNoNCk7XG5hZGRSZWdleFRva2VuKCdHR0dHRycsICBtYXRjaDF0bzYsIG1hdGNoNik7XG5hZGRSZWdleFRva2VuKCdnZ2dnZycsICBtYXRjaDF0bzYsIG1hdGNoNik7XG5cbmFkZFdlZWtQYXJzZVRva2VuKFsnZ2dnZycsICdnZ2dnZycsICdHR0dHJywgJ0dHR0dHJ10sIGZ1bmN0aW9uIChpbnB1dCwgd2VlaywgY29uZmlnLCB0b2tlbikge1xuICAgIHdlZWtbdG9rZW4uc3Vic3RyKDAsIDIpXSA9IHRvSW50KGlucHV0KTtcbn0pO1xuXG5hZGRXZWVrUGFyc2VUb2tlbihbJ2dnJywgJ0dHJ10sIGZ1bmN0aW9uIChpbnB1dCwgd2VlaywgY29uZmlnLCB0b2tlbikge1xuICAgIHdlZWtbdG9rZW5dID0gaG9va3MucGFyc2VUd29EaWdpdFllYXIoaW5wdXQpO1xufSk7XG5cbi8vIE1PTUVOVFNcblxuZnVuY3Rpb24gZ2V0U2V0V2Vla1llYXIgKGlucHV0KSB7XG4gICAgcmV0dXJuIGdldFNldFdlZWtZZWFySGVscGVyLmNhbGwodGhpcyxcbiAgICAgICAgICAgIGlucHV0LFxuICAgICAgICAgICAgdGhpcy53ZWVrKCksXG4gICAgICAgICAgICB0aGlzLndlZWtkYXkoKSxcbiAgICAgICAgICAgIHRoaXMubG9jYWxlRGF0YSgpLl93ZWVrLmRvdyxcbiAgICAgICAgICAgIHRoaXMubG9jYWxlRGF0YSgpLl93ZWVrLmRveSk7XG59XG5cbmZ1bmN0aW9uIGdldFNldElTT1dlZWtZZWFyIChpbnB1dCkge1xuICAgIHJldHVybiBnZXRTZXRXZWVrWWVhckhlbHBlci5jYWxsKHRoaXMsXG4gICAgICAgICAgICBpbnB1dCwgdGhpcy5pc29XZWVrKCksIHRoaXMuaXNvV2Vla2RheSgpLCAxLCA0KTtcbn1cblxuZnVuY3Rpb24gZ2V0SVNPV2Vla3NJblllYXIgKCkge1xuICAgIHJldHVybiB3ZWVrc0luWWVhcih0aGlzLnllYXIoKSwgMSwgNCk7XG59XG5cbmZ1bmN0aW9uIGdldFdlZWtzSW5ZZWFyICgpIHtcbiAgICB2YXIgd2Vla0luZm8gPSB0aGlzLmxvY2FsZURhdGEoKS5fd2VlaztcbiAgICByZXR1cm4gd2Vla3NJblllYXIodGhpcy55ZWFyKCksIHdlZWtJbmZvLmRvdywgd2Vla0luZm8uZG95KTtcbn1cblxuZnVuY3Rpb24gZ2V0U2V0V2Vla1llYXJIZWxwZXIoaW5wdXQsIHdlZWssIHdlZWtkYXksIGRvdywgZG95KSB7XG4gICAgdmFyIHdlZWtzVGFyZ2V0O1xuICAgIGlmIChpbnB1dCA9PSBudWxsKSB7XG4gICAgICAgIHJldHVybiB3ZWVrT2ZZZWFyKHRoaXMsIGRvdywgZG95KS55ZWFyO1xuICAgIH0gZWxzZSB7XG4gICAgICAgIHdlZWtzVGFyZ2V0ID0gd2Vla3NJblllYXIoaW5wdXQsIGRvdywgZG95KTtcbiAgICAgICAgaWYgKHdlZWsgPiB3ZWVrc1RhcmdldCkge1xuICAgICAgICAgICAgd2VlayA9IHdlZWtzVGFyZ2V0O1xuICAgICAgICB9XG4gICAgICAgIHJldHVybiBzZXRXZWVrQWxsLmNhbGwodGhpcywgaW5wdXQsIHdlZWssIHdlZWtkYXksIGRvdywgZG95KTtcbiAgICB9XG59XG5cbmZ1bmN0aW9uIHNldFdlZWtBbGwod2Vla1llYXIsIHdlZWssIHdlZWtkYXksIGRvdywgZG95KSB7XG4gICAgdmFyIGRheU9mWWVhckRhdGEgPSBkYXlPZlllYXJGcm9tV2Vla3Mod2Vla1llYXIsIHdlZWssIHdlZWtkYXksIGRvdywgZG95KSxcbiAgICAgICAgZGF0ZSA9IGNyZWF0ZVVUQ0RhdGUoZGF5T2ZZZWFyRGF0YS55ZWFyLCAwLCBkYXlPZlllYXJEYXRhLmRheU9mWWVhcik7XG5cbiAgICB0aGlzLnllYXIoZGF0ZS5nZXRVVENGdWxsWWVhcigpKTtcbiAgICB0aGlzLm1vbnRoKGRhdGUuZ2V0VVRDTW9udGgoKSk7XG4gICAgdGhpcy5kYXRlKGRhdGUuZ2V0VVRDRGF0ZSgpKTtcbiAgICByZXR1cm4gdGhpcztcbn1cblxuLy8gRk9STUFUVElOR1xuXG5hZGRGb3JtYXRUb2tlbignUScsIDAsICdRbycsICdxdWFydGVyJyk7XG5cbi8vIEFMSUFTRVNcblxuYWRkVW5pdEFsaWFzKCdxdWFydGVyJywgJ1EnKTtcblxuLy8gUFJJT1JJVFlcblxuYWRkVW5pdFByaW9yaXR5KCdxdWFydGVyJywgNyk7XG5cbi8vIFBBUlNJTkdcblxuYWRkUmVnZXhUb2tlbignUScsIG1hdGNoMSk7XG5hZGRQYXJzZVRva2VuKCdRJywgZnVuY3Rpb24gKGlucHV0LCBhcnJheSkge1xuICAgIGFycmF5W01PTlRIXSA9ICh0b0ludChpbnB1dCkgLSAxKSAqIDM7XG59KTtcblxuLy8gTU9NRU5UU1xuXG5mdW5jdGlvbiBnZXRTZXRRdWFydGVyIChpbnB1dCkge1xuICAgIHJldHVybiBpbnB1dCA9PSBudWxsID8gTWF0aC5jZWlsKCh0aGlzLm1vbnRoKCkgKyAxKSAvIDMpIDogdGhpcy5tb250aCgoaW5wdXQgLSAxKSAqIDMgKyB0aGlzLm1vbnRoKCkgJSAzKTtcbn1cblxuLy8gRk9STUFUVElOR1xuXG5hZGRGb3JtYXRUb2tlbignRCcsIFsnREQnLCAyXSwgJ0RvJywgJ2RhdGUnKTtcblxuLy8gQUxJQVNFU1xuXG5hZGRVbml0QWxpYXMoJ2RhdGUnLCAnRCcpO1xuXG4vLyBQUklPUk9JVFlcbmFkZFVuaXRQcmlvcml0eSgnZGF0ZScsIDkpO1xuXG4vLyBQQVJTSU5HXG5cbmFkZFJlZ2V4VG9rZW4oJ0QnLCAgbWF0Y2gxdG8yKTtcbmFkZFJlZ2V4VG9rZW4oJ0REJywgbWF0Y2gxdG8yLCBtYXRjaDIpO1xuYWRkUmVnZXhUb2tlbignRG8nLCBmdW5jdGlvbiAoaXNTdHJpY3QsIGxvY2FsZSkge1xuICAgIC8vIFRPRE86IFJlbW92ZSBcIm9yZGluYWxQYXJzZVwiIGZhbGxiYWNrIGluIG5leHQgbWFqb3IgcmVsZWFzZS5cbiAgICByZXR1cm4gaXNTdHJpY3QgP1xuICAgICAgKGxvY2FsZS5fZGF5T2ZNb250aE9yZGluYWxQYXJzZSB8fCBsb2NhbGUuX29yZGluYWxQYXJzZSkgOlxuICAgICAgbG9jYWxlLl9kYXlPZk1vbnRoT3JkaW5hbFBhcnNlTGVuaWVudDtcbn0pO1xuXG5hZGRQYXJzZVRva2VuKFsnRCcsICdERCddLCBEQVRFKTtcbmFkZFBhcnNlVG9rZW4oJ0RvJywgZnVuY3Rpb24gKGlucHV0LCBhcnJheSkge1xuICAgIGFycmF5W0RBVEVdID0gdG9JbnQoaW5wdXQubWF0Y2gobWF0Y2gxdG8yKVswXSk7XG59KTtcblxuLy8gTU9NRU5UU1xuXG52YXIgZ2V0U2V0RGF5T2ZNb250aCA9IG1ha2VHZXRTZXQoJ0RhdGUnLCB0cnVlKTtcblxuLy8gRk9STUFUVElOR1xuXG5hZGRGb3JtYXRUb2tlbignREREJywgWydEREREJywgM10sICdERERvJywgJ2RheU9mWWVhcicpO1xuXG4vLyBBTElBU0VTXG5cbmFkZFVuaXRBbGlhcygnZGF5T2ZZZWFyJywgJ0RERCcpO1xuXG4vLyBQUklPUklUWVxuYWRkVW5pdFByaW9yaXR5KCdkYXlPZlllYXInLCA0KTtcblxuLy8gUEFSU0lOR1xuXG5hZGRSZWdleFRva2VuKCdEREQnLCAgbWF0Y2gxdG8zKTtcbmFkZFJlZ2V4VG9rZW4oJ0REREQnLCBtYXRjaDMpO1xuYWRkUGFyc2VUb2tlbihbJ0RERCcsICdEREREJ10sIGZ1bmN0aW9uIChpbnB1dCwgYXJyYXksIGNvbmZpZykge1xuICAgIGNvbmZpZy5fZGF5T2ZZZWFyID0gdG9JbnQoaW5wdXQpO1xufSk7XG5cbi8vIEhFTFBFUlNcblxuLy8gTU9NRU5UU1xuXG5mdW5jdGlvbiBnZXRTZXREYXlPZlllYXIgKGlucHV0KSB7XG4gICAgdmFyIGRheU9mWWVhciA9IE1hdGgucm91bmQoKHRoaXMuY2xvbmUoKS5zdGFydE9mKCdkYXknKSAtIHRoaXMuY2xvbmUoKS5zdGFydE9mKCd5ZWFyJykpIC8gODY0ZTUpICsgMTtcbiAgICByZXR1cm4gaW5wdXQgPT0gbnVsbCA/IGRheU9mWWVhciA6IHRoaXMuYWRkKChpbnB1dCAtIGRheU9mWWVhciksICdkJyk7XG59XG5cbi8vIEZPUk1BVFRJTkdcblxuYWRkRm9ybWF0VG9rZW4oJ20nLCBbJ21tJywgMl0sIDAsICdtaW51dGUnKTtcblxuLy8gQUxJQVNFU1xuXG5hZGRVbml0QWxpYXMoJ21pbnV0ZScsICdtJyk7XG5cbi8vIFBSSU9SSVRZXG5cbmFkZFVuaXRQcmlvcml0eSgnbWludXRlJywgMTQpO1xuXG4vLyBQQVJTSU5HXG5cbmFkZFJlZ2V4VG9rZW4oJ20nLCAgbWF0Y2gxdG8yKTtcbmFkZFJlZ2V4VG9rZW4oJ21tJywgbWF0Y2gxdG8yLCBtYXRjaDIpO1xuYWRkUGFyc2VUb2tlbihbJ20nLCAnbW0nXSwgTUlOVVRFKTtcblxuLy8gTU9NRU5UU1xuXG52YXIgZ2V0U2V0TWludXRlID0gbWFrZUdldFNldCgnTWludXRlcycsIGZhbHNlKTtcblxuLy8gRk9STUFUVElOR1xuXG5hZGRGb3JtYXRUb2tlbigncycsIFsnc3MnLCAyXSwgMCwgJ3NlY29uZCcpO1xuXG4vLyBBTElBU0VTXG5cbmFkZFVuaXRBbGlhcygnc2Vjb25kJywgJ3MnKTtcblxuLy8gUFJJT1JJVFlcblxuYWRkVW5pdFByaW9yaXR5KCdzZWNvbmQnLCAxNSk7XG5cbi8vIFBBUlNJTkdcblxuYWRkUmVnZXhUb2tlbigncycsICBtYXRjaDF0bzIpO1xuYWRkUmVnZXhUb2tlbignc3MnLCBtYXRjaDF0bzIsIG1hdGNoMik7XG5hZGRQYXJzZVRva2VuKFsncycsICdzcyddLCBTRUNPTkQpO1xuXG4vLyBNT01FTlRTXG5cbnZhciBnZXRTZXRTZWNvbmQgPSBtYWtlR2V0U2V0KCdTZWNvbmRzJywgZmFsc2UpO1xuXG4vLyBGT1JNQVRUSU5HXG5cbmFkZEZvcm1hdFRva2VuKCdTJywgMCwgMCwgZnVuY3Rpb24gKCkge1xuICAgIHJldHVybiB+fih0aGlzLm1pbGxpc2Vjb25kKCkgLyAxMDApO1xufSk7XG5cbmFkZEZvcm1hdFRva2VuKDAsIFsnU1MnLCAyXSwgMCwgZnVuY3Rpb24gKCkge1xuICAgIHJldHVybiB+fih0aGlzLm1pbGxpc2Vjb25kKCkgLyAxMCk7XG59KTtcblxuYWRkRm9ybWF0VG9rZW4oMCwgWydTU1MnLCAzXSwgMCwgJ21pbGxpc2Vjb25kJyk7XG5hZGRGb3JtYXRUb2tlbigwLCBbJ1NTU1MnLCA0XSwgMCwgZnVuY3Rpb24gKCkge1xuICAgIHJldHVybiB0aGlzLm1pbGxpc2Vjb25kKCkgKiAxMDtcbn0pO1xuYWRkRm9ybWF0VG9rZW4oMCwgWydTU1NTUycsIDVdLCAwLCBmdW5jdGlvbiAoKSB7XG4gICAgcmV0dXJuIHRoaXMubWlsbGlzZWNvbmQoKSAqIDEwMDtcbn0pO1xuYWRkRm9ybWF0VG9rZW4oMCwgWydTU1NTU1MnLCA2XSwgMCwgZnVuY3Rpb24gKCkge1xuICAgIHJldHVybiB0aGlzLm1pbGxpc2Vjb25kKCkgKiAxMDAwO1xufSk7XG5hZGRGb3JtYXRUb2tlbigwLCBbJ1NTU1NTU1MnLCA3XSwgMCwgZnVuY3Rpb24gKCkge1xuICAgIHJldHVybiB0aGlzLm1pbGxpc2Vjb25kKCkgKiAxMDAwMDtcbn0pO1xuYWRkRm9ybWF0VG9rZW4oMCwgWydTU1NTU1NTUycsIDhdLCAwLCBmdW5jdGlvbiAoKSB7XG4gICAgcmV0dXJuIHRoaXMubWlsbGlzZWNvbmQoKSAqIDEwMDAwMDtcbn0pO1xuYWRkRm9ybWF0VG9rZW4oMCwgWydTU1NTU1NTU1MnLCA5XSwgMCwgZnVuY3Rpb24gKCkge1xuICAgIHJldHVybiB0aGlzLm1pbGxpc2Vjb25kKCkgKiAxMDAwMDAwO1xufSk7XG5cblxuLy8gQUxJQVNFU1xuXG5hZGRVbml0QWxpYXMoJ21pbGxpc2Vjb25kJywgJ21zJyk7XG5cbi8vIFBSSU9SSVRZXG5cbmFkZFVuaXRQcmlvcml0eSgnbWlsbGlzZWNvbmQnLCAxNik7XG5cbi8vIFBBUlNJTkdcblxuYWRkUmVnZXhUb2tlbignUycsICAgIG1hdGNoMXRvMywgbWF0Y2gxKTtcbmFkZFJlZ2V4VG9rZW4oJ1NTJywgICBtYXRjaDF0bzMsIG1hdGNoMik7XG5hZGRSZWdleFRva2VuKCdTU1MnLCAgbWF0Y2gxdG8zLCBtYXRjaDMpO1xuXG52YXIgdG9rZW47XG5mb3IgKHRva2VuID0gJ1NTU1MnOyB0b2tlbi5sZW5ndGggPD0gOTsgdG9rZW4gKz0gJ1MnKSB7XG4gICAgYWRkUmVnZXhUb2tlbih0b2tlbiwgbWF0Y2hVbnNpZ25lZCk7XG59XG5cbmZ1bmN0aW9uIHBhcnNlTXMoaW5wdXQsIGFycmF5KSB7XG4gICAgYXJyYXlbTUlMTElTRUNPTkRdID0gdG9JbnQoKCcwLicgKyBpbnB1dCkgKiAxMDAwKTtcbn1cblxuZm9yICh0b2tlbiA9ICdTJzsgdG9rZW4ubGVuZ3RoIDw9IDk7IHRva2VuICs9ICdTJykge1xuICAgIGFkZFBhcnNlVG9rZW4odG9rZW4sIHBhcnNlTXMpO1xufVxuLy8gTU9NRU5UU1xuXG52YXIgZ2V0U2V0TWlsbGlzZWNvbmQgPSBtYWtlR2V0U2V0KCdNaWxsaXNlY29uZHMnLCBmYWxzZSk7XG5cbi8vIEZPUk1BVFRJTkdcblxuYWRkRm9ybWF0VG9rZW4oJ3onLCAgMCwgMCwgJ3pvbmVBYmJyJyk7XG5hZGRGb3JtYXRUb2tlbignenonLCAwLCAwLCAnem9uZU5hbWUnKTtcblxuLy8gTU9NRU5UU1xuXG5mdW5jdGlvbiBnZXRab25lQWJiciAoKSB7XG4gICAgcmV0dXJuIHRoaXMuX2lzVVRDID8gJ1VUQycgOiAnJztcbn1cblxuZnVuY3Rpb24gZ2V0Wm9uZU5hbWUgKCkge1xuICAgIHJldHVybiB0aGlzLl9pc1VUQyA/ICdDb29yZGluYXRlZCBVbml2ZXJzYWwgVGltZScgOiAnJztcbn1cblxudmFyIHByb3RvID0gTW9tZW50LnByb3RvdHlwZTtcblxucHJvdG8uYWRkICAgICAgICAgICAgICAgPSBhZGQ7XG5wcm90by5jYWxlbmRhciAgICAgICAgICA9IGNhbGVuZGFyJDE7XG5wcm90by5jbG9uZSAgICAgICAgICAgICA9IGNsb25lO1xucHJvdG8uZGlmZiAgICAgICAgICAgICAgPSBkaWZmO1xucHJvdG8uZW5kT2YgICAgICAgICAgICAgPSBlbmRPZjtcbnByb3RvLmZvcm1hdCAgICAgICAgICAgID0gZm9ybWF0O1xucHJvdG8uZnJvbSAgICAgICAgICAgICAgPSBmcm9tO1xucHJvdG8uZnJvbU5vdyAgICAgICAgICAgPSBmcm9tTm93O1xucHJvdG8udG8gICAgICAgICAgICAgICAgPSB0bztcbnByb3RvLnRvTm93ICAgICAgICAgICAgID0gdG9Ob3c7XG5wcm90by5nZXQgICAgICAgICAgICAgICA9IHN0cmluZ0dldDtcbnByb3RvLmludmFsaWRBdCAgICAgICAgID0gaW52YWxpZEF0O1xucHJvdG8uaXNBZnRlciAgICAgICAgICAgPSBpc0FmdGVyO1xucHJvdG8uaXNCZWZvcmUgICAgICAgICAgPSBpc0JlZm9yZTtcbnByb3RvLmlzQmV0d2VlbiAgICAgICAgID0gaXNCZXR3ZWVuO1xucHJvdG8uaXNTYW1lICAgICAgICAgICAgPSBpc1NhbWU7XG5wcm90by5pc1NhbWVPckFmdGVyICAgICA9IGlzU2FtZU9yQWZ0ZXI7XG5wcm90by5pc1NhbWVPckJlZm9yZSAgICA9IGlzU2FtZU9yQmVmb3JlO1xucHJvdG8uaXNWYWxpZCAgICAgICAgICAgPSBpc1ZhbGlkJDI7XG5wcm90by5sYW5nICAgICAgICAgICAgICA9IGxhbmc7XG5wcm90by5sb2NhbGUgICAgICAgICAgICA9IGxvY2FsZTtcbnByb3RvLmxvY2FsZURhdGEgICAgICAgID0gbG9jYWxlRGF0YTtcbnByb3RvLm1heCAgICAgICAgICAgICAgID0gcHJvdG90eXBlTWF4O1xucHJvdG8ubWluICAgICAgICAgICAgICAgPSBwcm90b3R5cGVNaW47XG5wcm90by5wYXJzaW5nRmxhZ3MgICAgICA9IHBhcnNpbmdGbGFncztcbnByb3RvLnNldCAgICAgICAgICAgICAgID0gc3RyaW5nU2V0O1xucHJvdG8uc3RhcnRPZiAgICAgICAgICAgPSBzdGFydE9mO1xucHJvdG8uc3VidHJhY3QgICAgICAgICAgPSBzdWJ0cmFjdDtcbnByb3RvLnRvQXJyYXkgICAgICAgICAgID0gdG9BcnJheTtcbnByb3RvLnRvT2JqZWN0ICAgICAgICAgID0gdG9PYmplY3Q7XG5wcm90by50b0RhdGUgICAgICAgICAgICA9IHRvRGF0ZTtcbnByb3RvLnRvSVNPU3RyaW5nICAgICAgID0gdG9JU09TdHJpbmc7XG5wcm90by5pbnNwZWN0ICAgICAgICAgICA9IGluc3BlY3Q7XG5wcm90by50b0pTT04gICAgICAgICAgICA9IHRvSlNPTjtcbnByb3RvLnRvU3RyaW5nICAgICAgICAgID0gdG9TdHJpbmc7XG5wcm90by51bml4ICAgICAgICAgICAgICA9IHVuaXg7XG5wcm90by52YWx1ZU9mICAgICAgICAgICA9IHZhbHVlT2Y7XG5wcm90by5jcmVhdGlvbkRhdGEgICAgICA9IGNyZWF0aW9uRGF0YTtcblxuLy8gWWVhclxucHJvdG8ueWVhciAgICAgICA9IGdldFNldFllYXI7XG5wcm90by5pc0xlYXBZZWFyID0gZ2V0SXNMZWFwWWVhcjtcblxuLy8gV2VlayBZZWFyXG5wcm90by53ZWVrWWVhciAgICA9IGdldFNldFdlZWtZZWFyO1xucHJvdG8uaXNvV2Vla1llYXIgPSBnZXRTZXRJU09XZWVrWWVhcjtcblxuLy8gUXVhcnRlclxucHJvdG8ucXVhcnRlciA9IHByb3RvLnF1YXJ0ZXJzID0gZ2V0U2V0UXVhcnRlcjtcblxuLy8gTW9udGhcbnByb3RvLm1vbnRoICAgICAgID0gZ2V0U2V0TW9udGg7XG5wcm90by5kYXlzSW5Nb250aCA9IGdldERheXNJbk1vbnRoO1xuXG4vLyBXZWVrXG5wcm90by53ZWVrICAgICAgICAgICA9IHByb3RvLndlZWtzICAgICAgICA9IGdldFNldFdlZWs7XG5wcm90by5pc29XZWVrICAgICAgICA9IHByb3RvLmlzb1dlZWtzICAgICA9IGdldFNldElTT1dlZWs7XG5wcm90by53ZWVrc0luWWVhciAgICA9IGdldFdlZWtzSW5ZZWFyO1xucHJvdG8uaXNvV2Vla3NJblllYXIgPSBnZXRJU09XZWVrc0luWWVhcjtcblxuLy8gRGF5XG5wcm90by5kYXRlICAgICAgID0gZ2V0U2V0RGF5T2ZNb250aDtcbnByb3RvLmRheSAgICAgICAgPSBwcm90by5kYXlzICAgICAgICAgICAgID0gZ2V0U2V0RGF5T2ZXZWVrO1xucHJvdG8ud2Vla2RheSAgICA9IGdldFNldExvY2FsZURheU9mV2VlaztcbnByb3RvLmlzb1dlZWtkYXkgPSBnZXRTZXRJU09EYXlPZldlZWs7XG5wcm90by5kYXlPZlllYXIgID0gZ2V0U2V0RGF5T2ZZZWFyO1xuXG4vLyBIb3VyXG5wcm90by5ob3VyID0gcHJvdG8uaG91cnMgPSBnZXRTZXRIb3VyO1xuXG4vLyBNaW51dGVcbnByb3RvLm1pbnV0ZSA9IHByb3RvLm1pbnV0ZXMgPSBnZXRTZXRNaW51dGU7XG5cbi8vIFNlY29uZFxucHJvdG8uc2Vjb25kID0gcHJvdG8uc2Vjb25kcyA9IGdldFNldFNlY29uZDtcblxuLy8gTWlsbGlzZWNvbmRcbnByb3RvLm1pbGxpc2Vjb25kID0gcHJvdG8ubWlsbGlzZWNvbmRzID0gZ2V0U2V0TWlsbGlzZWNvbmQ7XG5cbi8vIE9mZnNldFxucHJvdG8udXRjT2Zmc2V0ICAgICAgICAgICAgPSBnZXRTZXRPZmZzZXQ7XG5wcm90by51dGMgICAgICAgICAgICAgICAgICA9IHNldE9mZnNldFRvVVRDO1xucHJvdG8ubG9jYWwgICAgICAgICAgICAgICAgPSBzZXRPZmZzZXRUb0xvY2FsO1xucHJvdG8ucGFyc2Vab25lICAgICAgICAgICAgPSBzZXRPZmZzZXRUb1BhcnNlZE9mZnNldDtcbnByb3RvLmhhc0FsaWduZWRIb3VyT2Zmc2V0ID0gaGFzQWxpZ25lZEhvdXJPZmZzZXQ7XG5wcm90by5pc0RTVCAgICAgICAgICAgICAgICA9IGlzRGF5bGlnaHRTYXZpbmdUaW1lO1xucHJvdG8uaXNMb2NhbCAgICAgICAgICAgICAgPSBpc0xvY2FsO1xucHJvdG8uaXNVdGNPZmZzZXQgICAgICAgICAgPSBpc1V0Y09mZnNldDtcbnByb3RvLmlzVXRjICAgICAgICAgICAgICAgID0gaXNVdGM7XG5wcm90by5pc1VUQyAgICAgICAgICAgICAgICA9IGlzVXRjO1xuXG4vLyBUaW1lem9uZVxucHJvdG8uem9uZUFiYnIgPSBnZXRab25lQWJicjtcbnByb3RvLnpvbmVOYW1lID0gZ2V0Wm9uZU5hbWU7XG5cbi8vIERlcHJlY2F0aW9uc1xucHJvdG8uZGF0ZXMgID0gZGVwcmVjYXRlKCdkYXRlcyBhY2Nlc3NvciBpcyBkZXByZWNhdGVkLiBVc2UgZGF0ZSBpbnN0ZWFkLicsIGdldFNldERheU9mTW9udGgpO1xucHJvdG8ubW9udGhzID0gZGVwcmVjYXRlKCdtb250aHMgYWNjZXNzb3IgaXMgZGVwcmVjYXRlZC4gVXNlIG1vbnRoIGluc3RlYWQnLCBnZXRTZXRNb250aCk7XG5wcm90by55ZWFycyAgPSBkZXByZWNhdGUoJ3llYXJzIGFjY2Vzc29yIGlzIGRlcHJlY2F0ZWQuIFVzZSB5ZWFyIGluc3RlYWQnLCBnZXRTZXRZZWFyKTtcbnByb3RvLnpvbmUgICA9IGRlcHJlY2F0ZSgnbW9tZW50KCkuem9uZSBpcyBkZXByZWNhdGVkLCB1c2UgbW9tZW50KCkudXRjT2Zmc2V0IGluc3RlYWQuIGh0dHA6Ly9tb21lbnRqcy5jb20vZ3VpZGVzLyMvd2FybmluZ3Mvem9uZS8nLCBnZXRTZXRab25lKTtcbnByb3RvLmlzRFNUU2hpZnRlZCA9IGRlcHJlY2F0ZSgnaXNEU1RTaGlmdGVkIGlzIGRlcHJlY2F0ZWQuIFNlZSBodHRwOi8vbW9tZW50anMuY29tL2d1aWRlcy8jL3dhcm5pbmdzL2RzdC1zaGlmdGVkLyBmb3IgbW9yZSBpbmZvcm1hdGlvbicsIGlzRGF5bGlnaHRTYXZpbmdUaW1lU2hpZnRlZCk7XG5cbmZ1bmN0aW9uIGNyZWF0ZVVuaXggKGlucHV0KSB7XG4gICAgcmV0dXJuIGNyZWF0ZUxvY2FsKGlucHV0ICogMTAwMCk7XG59XG5cbmZ1bmN0aW9uIGNyZWF0ZUluWm9uZSAoKSB7XG4gICAgcmV0dXJuIGNyZWF0ZUxvY2FsLmFwcGx5KG51bGwsIGFyZ3VtZW50cykucGFyc2Vab25lKCk7XG59XG5cbmZ1bmN0aW9uIHByZVBhcnNlUG9zdEZvcm1hdCAoc3RyaW5nKSB7XG4gICAgcmV0dXJuIHN0cmluZztcbn1cblxudmFyIHByb3RvJDEgPSBMb2NhbGUucHJvdG90eXBlO1xuXG5wcm90byQxLmNhbGVuZGFyICAgICAgICA9IGNhbGVuZGFyO1xucHJvdG8kMS5sb25nRGF0ZUZvcm1hdCAgPSBsb25nRGF0ZUZvcm1hdDtcbnByb3RvJDEuaW52YWxpZERhdGUgICAgID0gaW52YWxpZERhdGU7XG5wcm90byQxLm9yZGluYWwgICAgICAgICA9IG9yZGluYWw7XG5wcm90byQxLnByZXBhcnNlICAgICAgICA9IHByZVBhcnNlUG9zdEZvcm1hdDtcbnByb3RvJDEucG9zdGZvcm1hdCAgICAgID0gcHJlUGFyc2VQb3N0Rm9ybWF0O1xucHJvdG8kMS5yZWxhdGl2ZVRpbWUgICAgPSByZWxhdGl2ZVRpbWU7XG5wcm90byQxLnBhc3RGdXR1cmUgICAgICA9IHBhc3RGdXR1cmU7XG5wcm90byQxLnNldCAgICAgICAgICAgICA9IHNldDtcblxuLy8gTW9udGhcbnByb3RvJDEubW9udGhzICAgICAgICAgICAgPSAgICAgICAgbG9jYWxlTW9udGhzO1xucHJvdG8kMS5tb250aHNTaG9ydCAgICAgICA9ICAgICAgICBsb2NhbGVNb250aHNTaG9ydDtcbnByb3RvJDEubW9udGhzUGFyc2UgICAgICAgPSAgICAgICAgbG9jYWxlTW9udGhzUGFyc2U7XG5wcm90byQxLm1vbnRoc1JlZ2V4ICAgICAgID0gbW9udGhzUmVnZXg7XG5wcm90byQxLm1vbnRoc1Nob3J0UmVnZXggID0gbW9udGhzU2hvcnRSZWdleDtcblxuLy8gV2Vla1xucHJvdG8kMS53ZWVrID0gbG9jYWxlV2VlaztcbnByb3RvJDEuZmlyc3REYXlPZlllYXIgPSBsb2NhbGVGaXJzdERheU9mWWVhcjtcbnByb3RvJDEuZmlyc3REYXlPZldlZWsgPSBsb2NhbGVGaXJzdERheU9mV2VlaztcblxuLy8gRGF5IG9mIFdlZWtcbnByb3RvJDEud2Vla2RheXMgICAgICAgPSAgICAgICAgbG9jYWxlV2Vla2RheXM7XG5wcm90byQxLndlZWtkYXlzTWluICAgID0gICAgICAgIGxvY2FsZVdlZWtkYXlzTWluO1xucHJvdG8kMS53ZWVrZGF5c1Nob3J0ICA9ICAgICAgICBsb2NhbGVXZWVrZGF5c1Nob3J0O1xucHJvdG8kMS53ZWVrZGF5c1BhcnNlICA9ICAgICAgICBsb2NhbGVXZWVrZGF5c1BhcnNlO1xuXG5wcm90byQxLndlZWtkYXlzUmVnZXggICAgICAgPSAgICAgICAgd2Vla2RheXNSZWdleDtcbnByb3RvJDEud2Vla2RheXNTaG9ydFJlZ2V4ICA9ICAgICAgICB3ZWVrZGF5c1Nob3J0UmVnZXg7XG5wcm90byQxLndlZWtkYXlzTWluUmVnZXggICAgPSAgICAgICAgd2Vla2RheXNNaW5SZWdleDtcblxuLy8gSG91cnNcbnByb3RvJDEuaXNQTSA9IGxvY2FsZUlzUE07XG5wcm90byQxLm1lcmlkaWVtID0gbG9jYWxlTWVyaWRpZW07XG5cbmZ1bmN0aW9uIGdldCQxIChmb3JtYXQsIGluZGV4LCBmaWVsZCwgc2V0dGVyKSB7XG4gICAgdmFyIGxvY2FsZSA9IGdldExvY2FsZSgpO1xuICAgIHZhciB1dGMgPSBjcmVhdGVVVEMoKS5zZXQoc2V0dGVyLCBpbmRleCk7XG4gICAgcmV0dXJuIGxvY2FsZVtmaWVsZF0odXRjLCBmb3JtYXQpO1xufVxuXG5mdW5jdGlvbiBsaXN0TW9udGhzSW1wbCAoZm9ybWF0LCBpbmRleCwgZmllbGQpIHtcbiAgICBpZiAoaXNOdW1iZXIoZm9ybWF0KSkge1xuICAgICAgICBpbmRleCA9IGZvcm1hdDtcbiAgICAgICAgZm9ybWF0ID0gdW5kZWZpbmVkO1xuICAgIH1cblxuICAgIGZvcm1hdCA9IGZvcm1hdCB8fCAnJztcblxuICAgIGlmIChpbmRleCAhPSBudWxsKSB7XG4gICAgICAgIHJldHVybiBnZXQkMShmb3JtYXQsIGluZGV4LCBmaWVsZCwgJ21vbnRoJyk7XG4gICAgfVxuXG4gICAgdmFyIGk7XG4gICAgdmFyIG91dCA9IFtdO1xuICAgIGZvciAoaSA9IDA7IGkgPCAxMjsgaSsrKSB7XG4gICAgICAgIG91dFtpXSA9IGdldCQxKGZvcm1hdCwgaSwgZmllbGQsICdtb250aCcpO1xuICAgIH1cbiAgICByZXR1cm4gb3V0O1xufVxuXG4vLyAoKVxuLy8gKDUpXG4vLyAoZm10LCA1KVxuLy8gKGZtdClcbi8vICh0cnVlKVxuLy8gKHRydWUsIDUpXG4vLyAodHJ1ZSwgZm10LCA1KVxuLy8gKHRydWUsIGZtdClcbmZ1bmN0aW9uIGxpc3RXZWVrZGF5c0ltcGwgKGxvY2FsZVNvcnRlZCwgZm9ybWF0LCBpbmRleCwgZmllbGQpIHtcbiAgICBpZiAodHlwZW9mIGxvY2FsZVNvcnRlZCA9PT0gJ2Jvb2xlYW4nKSB7XG4gICAgICAgIGlmIChpc051bWJlcihmb3JtYXQpKSB7XG4gICAgICAgICAgICBpbmRleCA9IGZvcm1hdDtcbiAgICAgICAgICAgIGZvcm1hdCA9IHVuZGVmaW5lZDtcbiAgICAgICAgfVxuXG4gICAgICAgIGZvcm1hdCA9IGZvcm1hdCB8fCAnJztcbiAgICB9IGVsc2Uge1xuICAgICAgICBmb3JtYXQgPSBsb2NhbGVTb3J0ZWQ7XG4gICAgICAgIGluZGV4ID0gZm9ybWF0O1xuICAgICAgICBsb2NhbGVTb3J0ZWQgPSBmYWxzZTtcblxuICAgICAgICBpZiAoaXNOdW1iZXIoZm9ybWF0KSkge1xuICAgICAgICAgICAgaW5kZXggPSBmb3JtYXQ7XG4gICAgICAgICAgICBmb3JtYXQgPSB1bmRlZmluZWQ7XG4gICAgICAgIH1cblxuICAgICAgICBmb3JtYXQgPSBmb3JtYXQgfHwgJyc7XG4gICAgfVxuXG4gICAgdmFyIGxvY2FsZSA9IGdldExvY2FsZSgpLFxuICAgICAgICBzaGlmdCA9IGxvY2FsZVNvcnRlZCA/IGxvY2FsZS5fd2Vlay5kb3cgOiAwO1xuXG4gICAgaWYgKGluZGV4ICE9IG51bGwpIHtcbiAgICAgICAgcmV0dXJuIGdldCQxKGZvcm1hdCwgKGluZGV4ICsgc2hpZnQpICUgNywgZmllbGQsICdkYXknKTtcbiAgICB9XG5cbiAgICB2YXIgaTtcbiAgICB2YXIgb3V0ID0gW107XG4gICAgZm9yIChpID0gMDsgaSA8IDc7IGkrKykge1xuICAgICAgICBvdXRbaV0gPSBnZXQkMShmb3JtYXQsIChpICsgc2hpZnQpICUgNywgZmllbGQsICdkYXknKTtcbiAgICB9XG4gICAgcmV0dXJuIG91dDtcbn1cblxuZnVuY3Rpb24gbGlzdE1vbnRocyAoZm9ybWF0LCBpbmRleCkge1xuICAgIHJldHVybiBsaXN0TW9udGhzSW1wbChmb3JtYXQsIGluZGV4LCAnbW9udGhzJyk7XG59XG5cbmZ1bmN0aW9uIGxpc3RNb250aHNTaG9ydCAoZm9ybWF0LCBpbmRleCkge1xuICAgIHJldHVybiBsaXN0TW9udGhzSW1wbChmb3JtYXQsIGluZGV4LCAnbW9udGhzU2hvcnQnKTtcbn1cblxuZnVuY3Rpb24gbGlzdFdlZWtkYXlzIChsb2NhbGVTb3J0ZWQsIGZvcm1hdCwgaW5kZXgpIHtcbiAgICByZXR1cm4gbGlzdFdlZWtkYXlzSW1wbChsb2NhbGVTb3J0ZWQsIGZvcm1hdCwgaW5kZXgsICd3ZWVrZGF5cycpO1xufVxuXG5mdW5jdGlvbiBsaXN0V2Vla2RheXNTaG9ydCAobG9jYWxlU29ydGVkLCBmb3JtYXQsIGluZGV4KSB7XG4gICAgcmV0dXJuIGxpc3RXZWVrZGF5c0ltcGwobG9jYWxlU29ydGVkLCBmb3JtYXQsIGluZGV4LCAnd2Vla2RheXNTaG9ydCcpO1xufVxuXG5mdW5jdGlvbiBsaXN0V2Vla2RheXNNaW4gKGxvY2FsZVNvcnRlZCwgZm9ybWF0LCBpbmRleCkge1xuICAgIHJldHVybiBsaXN0V2Vla2RheXNJbXBsKGxvY2FsZVNvcnRlZCwgZm9ybWF0LCBpbmRleCwgJ3dlZWtkYXlzTWluJyk7XG59XG5cbmdldFNldEdsb2JhbExvY2FsZSgnZW4nLCB7XG4gICAgZGF5T2ZNb250aE9yZGluYWxQYXJzZTogL1xcZHsxLDJ9KHRofHN0fG5kfHJkKS8sXG4gICAgb3JkaW5hbCA6IGZ1bmN0aW9uIChudW1iZXIpIHtcbiAgICAgICAgdmFyIGIgPSBudW1iZXIgJSAxMCxcbiAgICAgICAgICAgIG91dHB1dCA9ICh0b0ludChudW1iZXIgJSAxMDAgLyAxMCkgPT09IDEpID8gJ3RoJyA6XG4gICAgICAgICAgICAoYiA9PT0gMSkgPyAnc3QnIDpcbiAgICAgICAgICAgIChiID09PSAyKSA/ICduZCcgOlxuICAgICAgICAgICAgKGIgPT09IDMpID8gJ3JkJyA6ICd0aCc7XG4gICAgICAgIHJldHVybiBudW1iZXIgKyBvdXRwdXQ7XG4gICAgfVxufSk7XG5cbi8vIFNpZGUgZWZmZWN0IGltcG9ydHNcbmhvb2tzLmxhbmcgPSBkZXByZWNhdGUoJ21vbWVudC5sYW5nIGlzIGRlcHJlY2F0ZWQuIFVzZSBtb21lbnQubG9jYWxlIGluc3RlYWQuJywgZ2V0U2V0R2xvYmFsTG9jYWxlKTtcbmhvb2tzLmxhbmdEYXRhID0gZGVwcmVjYXRlKCdtb21lbnQubGFuZ0RhdGEgaXMgZGVwcmVjYXRlZC4gVXNlIG1vbWVudC5sb2NhbGVEYXRhIGluc3RlYWQuJywgZ2V0TG9jYWxlKTtcblxudmFyIG1hdGhBYnMgPSBNYXRoLmFicztcblxuZnVuY3Rpb24gYWJzICgpIHtcbiAgICB2YXIgZGF0YSAgICAgICAgICAgPSB0aGlzLl9kYXRhO1xuXG4gICAgdGhpcy5fbWlsbGlzZWNvbmRzID0gbWF0aEFicyh0aGlzLl9taWxsaXNlY29uZHMpO1xuICAgIHRoaXMuX2RheXMgICAgICAgICA9IG1hdGhBYnModGhpcy5fZGF5cyk7XG4gICAgdGhpcy5fbW9udGhzICAgICAgID0gbWF0aEFicyh0aGlzLl9tb250aHMpO1xuXG4gICAgZGF0YS5taWxsaXNlY29uZHMgID0gbWF0aEFicyhkYXRhLm1pbGxpc2Vjb25kcyk7XG4gICAgZGF0YS5zZWNvbmRzICAgICAgID0gbWF0aEFicyhkYXRhLnNlY29uZHMpO1xuICAgIGRhdGEubWludXRlcyAgICAgICA9IG1hdGhBYnMoZGF0YS5taW51dGVzKTtcbiAgICBkYXRhLmhvdXJzICAgICAgICAgPSBtYXRoQWJzKGRhdGEuaG91cnMpO1xuICAgIGRhdGEubW9udGhzICAgICAgICA9IG1hdGhBYnMoZGF0YS5tb250aHMpO1xuICAgIGRhdGEueWVhcnMgICAgICAgICA9IG1hdGhBYnMoZGF0YS55ZWFycyk7XG5cbiAgICByZXR1cm4gdGhpcztcbn1cblxuZnVuY3Rpb24gYWRkU3VidHJhY3QkMSAoZHVyYXRpb24sIGlucHV0LCB2YWx1ZSwgZGlyZWN0aW9uKSB7XG4gICAgdmFyIG90aGVyID0gY3JlYXRlRHVyYXRpb24oaW5wdXQsIHZhbHVlKTtcblxuICAgIGR1cmF0aW9uLl9taWxsaXNlY29uZHMgKz0gZGlyZWN0aW9uICogb3RoZXIuX21pbGxpc2Vjb25kcztcbiAgICBkdXJhdGlvbi5fZGF5cyAgICAgICAgICs9IGRpcmVjdGlvbiAqIG90aGVyLl9kYXlzO1xuICAgIGR1cmF0aW9uLl9tb250aHMgICAgICAgKz0gZGlyZWN0aW9uICogb3RoZXIuX21vbnRocztcblxuICAgIHJldHVybiBkdXJhdGlvbi5fYnViYmxlKCk7XG59XG5cbi8vIHN1cHBvcnRzIG9ubHkgMi4wLXN0eWxlIGFkZCgxLCAncycpIG9yIGFkZChkdXJhdGlvbilcbmZ1bmN0aW9uIGFkZCQxIChpbnB1dCwgdmFsdWUpIHtcbiAgICByZXR1cm4gYWRkU3VidHJhY3QkMSh0aGlzLCBpbnB1dCwgdmFsdWUsIDEpO1xufVxuXG4vLyBzdXBwb3J0cyBvbmx5IDIuMC1zdHlsZSBzdWJ0cmFjdCgxLCAncycpIG9yIHN1YnRyYWN0KGR1cmF0aW9uKVxuZnVuY3Rpb24gc3VidHJhY3QkMSAoaW5wdXQsIHZhbHVlKSB7XG4gICAgcmV0dXJuIGFkZFN1YnRyYWN0JDEodGhpcywgaW5wdXQsIHZhbHVlLCAtMSk7XG59XG5cbmZ1bmN0aW9uIGFic0NlaWwgKG51bWJlcikge1xuICAgIGlmIChudW1iZXIgPCAwKSB7XG4gICAgICAgIHJldHVybiBNYXRoLmZsb29yKG51bWJlcik7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgcmV0dXJuIE1hdGguY2VpbChudW1iZXIpO1xuICAgIH1cbn1cblxuZnVuY3Rpb24gYnViYmxlICgpIHtcbiAgICB2YXIgbWlsbGlzZWNvbmRzID0gdGhpcy5fbWlsbGlzZWNvbmRzO1xuICAgIHZhciBkYXlzICAgICAgICAgPSB0aGlzLl9kYXlzO1xuICAgIHZhciBtb250aHMgICAgICAgPSB0aGlzLl9tb250aHM7XG4gICAgdmFyIGRhdGEgICAgICAgICA9IHRoaXMuX2RhdGE7XG4gICAgdmFyIHNlY29uZHMsIG1pbnV0ZXMsIGhvdXJzLCB5ZWFycywgbW9udGhzRnJvbURheXM7XG5cbiAgICAvLyBpZiB3ZSBoYXZlIGEgbWl4IG9mIHBvc2l0aXZlIGFuZCBuZWdhdGl2ZSB2YWx1ZXMsIGJ1YmJsZSBkb3duIGZpcnN0XG4gICAgLy8gY2hlY2s6IGh0dHBzOi8vZ2l0aHViLmNvbS9tb21lbnQvbW9tZW50L2lzc3Vlcy8yMTY2XG4gICAgaWYgKCEoKG1pbGxpc2Vjb25kcyA+PSAwICYmIGRheXMgPj0gMCAmJiBtb250aHMgPj0gMCkgfHxcbiAgICAgICAgICAgIChtaWxsaXNlY29uZHMgPD0gMCAmJiBkYXlzIDw9IDAgJiYgbW9udGhzIDw9IDApKSkge1xuICAgICAgICBtaWxsaXNlY29uZHMgKz0gYWJzQ2VpbChtb250aHNUb0RheXMobW9udGhzKSArIGRheXMpICogODY0ZTU7XG4gICAgICAgIGRheXMgPSAwO1xuICAgICAgICBtb250aHMgPSAwO1xuICAgIH1cblxuICAgIC8vIFRoZSBmb2xsb3dpbmcgY29kZSBidWJibGVzIHVwIHZhbHVlcywgc2VlIHRoZSB0ZXN0cyBmb3JcbiAgICAvLyBleGFtcGxlcyBvZiB3aGF0IHRoYXQgbWVhbnMuXG4gICAgZGF0YS5taWxsaXNlY29uZHMgPSBtaWxsaXNlY29uZHMgJSAxMDAwO1xuXG4gICAgc2Vjb25kcyAgICAgICAgICAgPSBhYnNGbG9vcihtaWxsaXNlY29uZHMgLyAxMDAwKTtcbiAgICBkYXRhLnNlY29uZHMgICAgICA9IHNlY29uZHMgJSA2MDtcblxuICAgIG1pbnV0ZXMgICAgICAgICAgID0gYWJzRmxvb3Ioc2Vjb25kcyAvIDYwKTtcbiAgICBkYXRhLm1pbnV0ZXMgICAgICA9IG1pbnV0ZXMgJSA2MDtcblxuICAgIGhvdXJzICAgICAgICAgICAgID0gYWJzRmxvb3IobWludXRlcyAvIDYwKTtcbiAgICBkYXRhLmhvdXJzICAgICAgICA9IGhvdXJzICUgMjQ7XG5cbiAgICBkYXlzICs9IGFic0Zsb29yKGhvdXJzIC8gMjQpO1xuXG4gICAgLy8gY29udmVydCBkYXlzIHRvIG1vbnRoc1xuICAgIG1vbnRoc0Zyb21EYXlzID0gYWJzRmxvb3IoZGF5c1RvTW9udGhzKGRheXMpKTtcbiAgICBtb250aHMgKz0gbW9udGhzRnJvbURheXM7XG4gICAgZGF5cyAtPSBhYnNDZWlsKG1vbnRoc1RvRGF5cyhtb250aHNGcm9tRGF5cykpO1xuXG4gICAgLy8gMTIgbW9udGhzIC0+IDEgeWVhclxuICAgIHllYXJzID0gYWJzRmxvb3IobW9udGhzIC8gMTIpO1xuICAgIG1vbnRocyAlPSAxMjtcblxuICAgIGRhdGEuZGF5cyAgID0gZGF5cztcbiAgICBkYXRhLm1vbnRocyA9IG1vbnRocztcbiAgICBkYXRhLnllYXJzICA9IHllYXJzO1xuXG4gICAgcmV0dXJuIHRoaXM7XG59XG5cbmZ1bmN0aW9uIGRheXNUb01vbnRocyAoZGF5cykge1xuICAgIC8vIDQwMCB5ZWFycyBoYXZlIDE0NjA5NyBkYXlzICh0YWtpbmcgaW50byBhY2NvdW50IGxlYXAgeWVhciBydWxlcylcbiAgICAvLyA0MDAgeWVhcnMgaGF2ZSAxMiBtb250aHMgPT09IDQ4MDBcbiAgICByZXR1cm4gZGF5cyAqIDQ4MDAgLyAxNDYwOTc7XG59XG5cbmZ1bmN0aW9uIG1vbnRoc1RvRGF5cyAobW9udGhzKSB7XG4gICAgLy8gdGhlIHJldmVyc2Ugb2YgZGF5c1RvTW9udGhzXG4gICAgcmV0dXJuIG1vbnRocyAqIDE0NjA5NyAvIDQ4MDA7XG59XG5cbmZ1bmN0aW9uIGFzICh1bml0cykge1xuICAgIGlmICghdGhpcy5pc1ZhbGlkKCkpIHtcbiAgICAgICAgcmV0dXJuIE5hTjtcbiAgICB9XG4gICAgdmFyIGRheXM7XG4gICAgdmFyIG1vbnRocztcbiAgICB2YXIgbWlsbGlzZWNvbmRzID0gdGhpcy5fbWlsbGlzZWNvbmRzO1xuXG4gICAgdW5pdHMgPSBub3JtYWxpemVVbml0cyh1bml0cyk7XG5cbiAgICBpZiAodW5pdHMgPT09ICdtb250aCcgfHwgdW5pdHMgPT09ICd5ZWFyJykge1xuICAgICAgICBkYXlzICAgPSB0aGlzLl9kYXlzICAgKyBtaWxsaXNlY29uZHMgLyA4NjRlNTtcbiAgICAgICAgbW9udGhzID0gdGhpcy5fbW9udGhzICsgZGF5c1RvTW9udGhzKGRheXMpO1xuICAgICAgICByZXR1cm4gdW5pdHMgPT09ICdtb250aCcgPyBtb250aHMgOiBtb250aHMgLyAxMjtcbiAgICB9IGVsc2Uge1xuICAgICAgICAvLyBoYW5kbGUgbWlsbGlzZWNvbmRzIHNlcGFyYXRlbHkgYmVjYXVzZSBvZiBmbG9hdGluZyBwb2ludCBtYXRoIGVycm9ycyAoaXNzdWUgIzE4NjcpXG4gICAgICAgIGRheXMgPSB0aGlzLl9kYXlzICsgTWF0aC5yb3VuZChtb250aHNUb0RheXModGhpcy5fbW9udGhzKSk7XG4gICAgICAgIHN3aXRjaCAodW5pdHMpIHtcbiAgICAgICAgICAgIGNhc2UgJ3dlZWsnICAgOiByZXR1cm4gZGF5cyAvIDcgICAgICsgbWlsbGlzZWNvbmRzIC8gNjA0OGU1O1xuICAgICAgICAgICAgY2FzZSAnZGF5JyAgICA6IHJldHVybiBkYXlzICAgICAgICAgKyBtaWxsaXNlY29uZHMgLyA4NjRlNTtcbiAgICAgICAgICAgIGNhc2UgJ2hvdXInICAgOiByZXR1cm4gZGF5cyAqIDI0ICAgICsgbWlsbGlzZWNvbmRzIC8gMzZlNTtcbiAgICAgICAgICAgIGNhc2UgJ21pbnV0ZScgOiByZXR1cm4gZGF5cyAqIDE0NDAgICsgbWlsbGlzZWNvbmRzIC8gNmU0O1xuICAgICAgICAgICAgY2FzZSAnc2Vjb25kJyA6IHJldHVybiBkYXlzICogODY0MDAgKyBtaWxsaXNlY29uZHMgLyAxMDAwO1xuICAgICAgICAgICAgLy8gTWF0aC5mbG9vciBwcmV2ZW50cyBmbG9hdGluZyBwb2ludCBtYXRoIGVycm9ycyBoZXJlXG4gICAgICAgICAgICBjYXNlICdtaWxsaXNlY29uZCc6IHJldHVybiBNYXRoLmZsb29yKGRheXMgKiA4NjRlNSkgKyBtaWxsaXNlY29uZHM7XG4gICAgICAgICAgICBkZWZhdWx0OiB0aHJvdyBuZXcgRXJyb3IoJ1Vua25vd24gdW5pdCAnICsgdW5pdHMpO1xuICAgICAgICB9XG4gICAgfVxufVxuXG4vLyBUT0RPOiBVc2UgdGhpcy5hcygnbXMnKT9cbmZ1bmN0aW9uIHZhbHVlT2YkMSAoKSB7XG4gICAgaWYgKCF0aGlzLmlzVmFsaWQoKSkge1xuICAgICAgICByZXR1cm4gTmFOO1xuICAgIH1cbiAgICByZXR1cm4gKFxuICAgICAgICB0aGlzLl9taWxsaXNlY29uZHMgK1xuICAgICAgICB0aGlzLl9kYXlzICogODY0ZTUgK1xuICAgICAgICAodGhpcy5fbW9udGhzICUgMTIpICogMjU5MmU2ICtcbiAgICAgICAgdG9JbnQodGhpcy5fbW9udGhzIC8gMTIpICogMzE1MzZlNlxuICAgICk7XG59XG5cbmZ1bmN0aW9uIG1ha2VBcyAoYWxpYXMpIHtcbiAgICByZXR1cm4gZnVuY3Rpb24gKCkge1xuICAgICAgICByZXR1cm4gdGhpcy5hcyhhbGlhcyk7XG4gICAgfTtcbn1cblxudmFyIGFzTWlsbGlzZWNvbmRzID0gbWFrZUFzKCdtcycpO1xudmFyIGFzU2Vjb25kcyAgICAgID0gbWFrZUFzKCdzJyk7XG52YXIgYXNNaW51dGVzICAgICAgPSBtYWtlQXMoJ20nKTtcbnZhciBhc0hvdXJzICAgICAgICA9IG1ha2VBcygnaCcpO1xudmFyIGFzRGF5cyAgICAgICAgID0gbWFrZUFzKCdkJyk7XG52YXIgYXNXZWVrcyAgICAgICAgPSBtYWtlQXMoJ3cnKTtcbnZhciBhc01vbnRocyAgICAgICA9IG1ha2VBcygnTScpO1xudmFyIGFzWWVhcnMgICAgICAgID0gbWFrZUFzKCd5Jyk7XG5cbmZ1bmN0aW9uIGNsb25lJDEgKCkge1xuICAgIHJldHVybiBjcmVhdGVEdXJhdGlvbih0aGlzKTtcbn1cblxuZnVuY3Rpb24gZ2V0JDIgKHVuaXRzKSB7XG4gICAgdW5pdHMgPSBub3JtYWxpemVVbml0cyh1bml0cyk7XG4gICAgcmV0dXJuIHRoaXMuaXNWYWxpZCgpID8gdGhpc1t1bml0cyArICdzJ10oKSA6IE5hTjtcbn1cblxuZnVuY3Rpb24gbWFrZUdldHRlcihuYW1lKSB7XG4gICAgcmV0dXJuIGZ1bmN0aW9uICgpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuaXNWYWxpZCgpID8gdGhpcy5fZGF0YVtuYW1lXSA6IE5hTjtcbiAgICB9O1xufVxuXG52YXIgbWlsbGlzZWNvbmRzID0gbWFrZUdldHRlcignbWlsbGlzZWNvbmRzJyk7XG52YXIgc2Vjb25kcyAgICAgID0gbWFrZUdldHRlcignc2Vjb25kcycpO1xudmFyIG1pbnV0ZXMgICAgICA9IG1ha2VHZXR0ZXIoJ21pbnV0ZXMnKTtcbnZhciBob3VycyAgICAgICAgPSBtYWtlR2V0dGVyKCdob3VycycpO1xudmFyIGRheXMgICAgICAgICA9IG1ha2VHZXR0ZXIoJ2RheXMnKTtcbnZhciBtb250aHMgICAgICAgPSBtYWtlR2V0dGVyKCdtb250aHMnKTtcbnZhciB5ZWFycyAgICAgICAgPSBtYWtlR2V0dGVyKCd5ZWFycycpO1xuXG5mdW5jdGlvbiB3ZWVrcyAoKSB7XG4gICAgcmV0dXJuIGFic0Zsb29yKHRoaXMuZGF5cygpIC8gNyk7XG59XG5cbnZhciByb3VuZCA9IE1hdGgucm91bmQ7XG52YXIgdGhyZXNob2xkcyA9IHtcbiAgICBzczogNDQsICAgICAgICAgLy8gYSBmZXcgc2Vjb25kcyB0byBzZWNvbmRzXG4gICAgcyA6IDQ1LCAgICAgICAgIC8vIHNlY29uZHMgdG8gbWludXRlXG4gICAgbSA6IDQ1LCAgICAgICAgIC8vIG1pbnV0ZXMgdG8gaG91clxuICAgIGggOiAyMiwgICAgICAgICAvLyBob3VycyB0byBkYXlcbiAgICBkIDogMjYsICAgICAgICAgLy8gZGF5cyB0byBtb250aFxuICAgIE0gOiAxMSAgICAgICAgICAvLyBtb250aHMgdG8geWVhclxufTtcblxuLy8gaGVscGVyIGZ1bmN0aW9uIGZvciBtb21lbnQuZm4uZnJvbSwgbW9tZW50LmZuLmZyb21Ob3csIGFuZCBtb21lbnQuZHVyYXRpb24uZm4uaHVtYW5pemVcbmZ1bmN0aW9uIHN1YnN0aXR1dGVUaW1lQWdvKHN0cmluZywgbnVtYmVyLCB3aXRob3V0U3VmZml4LCBpc0Z1dHVyZSwgbG9jYWxlKSB7XG4gICAgcmV0dXJuIGxvY2FsZS5yZWxhdGl2ZVRpbWUobnVtYmVyIHx8IDEsICEhd2l0aG91dFN1ZmZpeCwgc3RyaW5nLCBpc0Z1dHVyZSk7XG59XG5cbmZ1bmN0aW9uIHJlbGF0aXZlVGltZSQxIChwb3NOZWdEdXJhdGlvbiwgd2l0aG91dFN1ZmZpeCwgbG9jYWxlKSB7XG4gICAgdmFyIGR1cmF0aW9uID0gY3JlYXRlRHVyYXRpb24ocG9zTmVnRHVyYXRpb24pLmFicygpO1xuICAgIHZhciBzZWNvbmRzICA9IHJvdW5kKGR1cmF0aW9uLmFzKCdzJykpO1xuICAgIHZhciBtaW51dGVzICA9IHJvdW5kKGR1cmF0aW9uLmFzKCdtJykpO1xuICAgIHZhciBob3VycyAgICA9IHJvdW5kKGR1cmF0aW9uLmFzKCdoJykpO1xuICAgIHZhciBkYXlzICAgICA9IHJvdW5kKGR1cmF0aW9uLmFzKCdkJykpO1xuICAgIHZhciBtb250aHMgICA9IHJvdW5kKGR1cmF0aW9uLmFzKCdNJykpO1xuICAgIHZhciB5ZWFycyAgICA9IHJvdW5kKGR1cmF0aW9uLmFzKCd5JykpO1xuXG4gICAgdmFyIGEgPSBzZWNvbmRzIDw9IHRocmVzaG9sZHMuc3MgJiYgWydzJywgc2Vjb25kc10gIHx8XG4gICAgICAgICAgICBzZWNvbmRzIDwgdGhyZXNob2xkcy5zICAgJiYgWydzcycsIHNlY29uZHNdIHx8XG4gICAgICAgICAgICBtaW51dGVzIDw9IDEgICAgICAgICAgICAgJiYgWydtJ10gICAgICAgICAgIHx8XG4gICAgICAgICAgICBtaW51dGVzIDwgdGhyZXNob2xkcy5tICAgJiYgWydtbScsIG1pbnV0ZXNdIHx8XG4gICAgICAgICAgICBob3VycyAgIDw9IDEgICAgICAgICAgICAgJiYgWydoJ10gICAgICAgICAgIHx8XG4gICAgICAgICAgICBob3VycyAgIDwgdGhyZXNob2xkcy5oICAgJiYgWydoaCcsIGhvdXJzXSAgIHx8XG4gICAgICAgICAgICBkYXlzICAgIDw9IDEgICAgICAgICAgICAgJiYgWydkJ10gICAgICAgICAgIHx8XG4gICAgICAgICAgICBkYXlzICAgIDwgdGhyZXNob2xkcy5kICAgJiYgWydkZCcsIGRheXNdICAgIHx8XG4gICAgICAgICAgICBtb250aHMgIDw9IDEgICAgICAgICAgICAgJiYgWydNJ10gICAgICAgICAgIHx8XG4gICAgICAgICAgICBtb250aHMgIDwgdGhyZXNob2xkcy5NICAgJiYgWydNTScsIG1vbnRoc10gIHx8XG4gICAgICAgICAgICB5ZWFycyAgIDw9IDEgICAgICAgICAgICAgJiYgWyd5J10gICAgICAgICAgIHx8IFsneXknLCB5ZWFyc107XG5cbiAgICBhWzJdID0gd2l0aG91dFN1ZmZpeDtcbiAgICBhWzNdID0gK3Bvc05lZ0R1cmF0aW9uID4gMDtcbiAgICBhWzRdID0gbG9jYWxlO1xuICAgIHJldHVybiBzdWJzdGl0dXRlVGltZUFnby5hcHBseShudWxsLCBhKTtcbn1cblxuLy8gVGhpcyBmdW5jdGlvbiBhbGxvd3MgeW91IHRvIHNldCB0aGUgcm91bmRpbmcgZnVuY3Rpb24gZm9yIHJlbGF0aXZlIHRpbWUgc3RyaW5nc1xuZnVuY3Rpb24gZ2V0U2V0UmVsYXRpdmVUaW1lUm91bmRpbmcgKHJvdW5kaW5nRnVuY3Rpb24pIHtcbiAgICBpZiAocm91bmRpbmdGdW5jdGlvbiA9PT0gdW5kZWZpbmVkKSB7XG4gICAgICAgIHJldHVybiByb3VuZDtcbiAgICB9XG4gICAgaWYgKHR5cGVvZihyb3VuZGluZ0Z1bmN0aW9uKSA9PT0gJ2Z1bmN0aW9uJykge1xuICAgICAgICByb3VuZCA9IHJvdW5kaW5nRnVuY3Rpb247XG4gICAgICAgIHJldHVybiB0cnVlO1xuICAgIH1cbiAgICByZXR1cm4gZmFsc2U7XG59XG5cbi8vIFRoaXMgZnVuY3Rpb24gYWxsb3dzIHlvdSB0byBzZXQgYSB0aHJlc2hvbGQgZm9yIHJlbGF0aXZlIHRpbWUgc3RyaW5nc1xuZnVuY3Rpb24gZ2V0U2V0UmVsYXRpdmVUaW1lVGhyZXNob2xkICh0aHJlc2hvbGQsIGxpbWl0KSB7XG4gICAgaWYgKHRocmVzaG9sZHNbdGhyZXNob2xkXSA9PT0gdW5kZWZpbmVkKSB7XG4gICAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9XG4gICAgaWYgKGxpbWl0ID09PSB1bmRlZmluZWQpIHtcbiAgICAgICAgcmV0dXJuIHRocmVzaG9sZHNbdGhyZXNob2xkXTtcbiAgICB9XG4gICAgdGhyZXNob2xkc1t0aHJlc2hvbGRdID0gbGltaXQ7XG4gICAgaWYgKHRocmVzaG9sZCA9PT0gJ3MnKSB7XG4gICAgICAgIHRocmVzaG9sZHMuc3MgPSBsaW1pdCAtIDE7XG4gICAgfVxuICAgIHJldHVybiB0cnVlO1xufVxuXG5mdW5jdGlvbiBodW1hbml6ZSAod2l0aFN1ZmZpeCkge1xuICAgIGlmICghdGhpcy5pc1ZhbGlkKCkpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMubG9jYWxlRGF0YSgpLmludmFsaWREYXRlKCk7XG4gICAgfVxuXG4gICAgdmFyIGxvY2FsZSA9IHRoaXMubG9jYWxlRGF0YSgpO1xuICAgIHZhciBvdXRwdXQgPSByZWxhdGl2ZVRpbWUkMSh0aGlzLCAhd2l0aFN1ZmZpeCwgbG9jYWxlKTtcblxuICAgIGlmICh3aXRoU3VmZml4KSB7XG4gICAgICAgIG91dHB1dCA9IGxvY2FsZS5wYXN0RnV0dXJlKCt0aGlzLCBvdXRwdXQpO1xuICAgIH1cblxuICAgIHJldHVybiBsb2NhbGUucG9zdGZvcm1hdChvdXRwdXQpO1xufVxuXG52YXIgYWJzJDEgPSBNYXRoLmFicztcblxuZnVuY3Rpb24gc2lnbih4KSB7XG4gICAgcmV0dXJuICgoeCA+IDApIC0gKHggPCAwKSkgfHwgK3g7XG59XG5cbmZ1bmN0aW9uIHRvSVNPU3RyaW5nJDEoKSB7XG4gICAgLy8gZm9yIElTTyBzdHJpbmdzIHdlIGRvIG5vdCB1c2UgdGhlIG5vcm1hbCBidWJibGluZyBydWxlczpcbiAgICAvLyAgKiBtaWxsaXNlY29uZHMgYnViYmxlIHVwIHVudGlsIHRoZXkgYmVjb21lIGhvdXJzXG4gICAgLy8gICogZGF5cyBkbyBub3QgYnViYmxlIGF0IGFsbFxuICAgIC8vICAqIG1vbnRocyBidWJibGUgdXAgdW50aWwgdGhleSBiZWNvbWUgeWVhcnNcbiAgICAvLyBUaGlzIGlzIGJlY2F1c2UgdGhlcmUgaXMgbm8gY29udGV4dC1mcmVlIGNvbnZlcnNpb24gYmV0d2VlbiBob3VycyBhbmQgZGF5c1xuICAgIC8vICh0aGluayBvZiBjbG9jayBjaGFuZ2VzKVxuICAgIC8vIGFuZCBhbHNvIG5vdCBiZXR3ZWVuIGRheXMgYW5kIG1vbnRocyAoMjgtMzEgZGF5cyBwZXIgbW9udGgpXG4gICAgaWYgKCF0aGlzLmlzVmFsaWQoKSkge1xuICAgICAgICByZXR1cm4gdGhpcy5sb2NhbGVEYXRhKCkuaW52YWxpZERhdGUoKTtcbiAgICB9XG5cbiAgICB2YXIgc2Vjb25kcyA9IGFicyQxKHRoaXMuX21pbGxpc2Vjb25kcykgLyAxMDAwO1xuICAgIHZhciBkYXlzICAgICAgICAgPSBhYnMkMSh0aGlzLl9kYXlzKTtcbiAgICB2YXIgbW9udGhzICAgICAgID0gYWJzJDEodGhpcy5fbW9udGhzKTtcbiAgICB2YXIgbWludXRlcywgaG91cnMsIHllYXJzO1xuXG4gICAgLy8gMzYwMCBzZWNvbmRzIC0+IDYwIG1pbnV0ZXMgLT4gMSBob3VyXG4gICAgbWludXRlcyAgICAgICAgICAgPSBhYnNGbG9vcihzZWNvbmRzIC8gNjApO1xuICAgIGhvdXJzICAgICAgICAgICAgID0gYWJzRmxvb3IobWludXRlcyAvIDYwKTtcbiAgICBzZWNvbmRzICU9IDYwO1xuICAgIG1pbnV0ZXMgJT0gNjA7XG5cbiAgICAvLyAxMiBtb250aHMgLT4gMSB5ZWFyXG4gICAgeWVhcnMgID0gYWJzRmxvb3IobW9udGhzIC8gMTIpO1xuICAgIG1vbnRocyAlPSAxMjtcblxuXG4gICAgLy8gaW5zcGlyZWQgYnkgaHR0cHM6Ly9naXRodWIuY29tL2RvcmRpbGxlL21vbWVudC1pc29kdXJhdGlvbi9ibG9iL21hc3Rlci9tb21lbnQuaXNvZHVyYXRpb24uanNcbiAgICB2YXIgWSA9IHllYXJzO1xuICAgIHZhciBNID0gbW9udGhzO1xuICAgIHZhciBEID0gZGF5cztcbiAgICB2YXIgaCA9IGhvdXJzO1xuICAgIHZhciBtID0gbWludXRlcztcbiAgICB2YXIgcyA9IHNlY29uZHMgPyBzZWNvbmRzLnRvRml4ZWQoMykucmVwbGFjZSgvXFwuPzArJC8sICcnKSA6ICcnO1xuICAgIHZhciB0b3RhbCA9IHRoaXMuYXNTZWNvbmRzKCk7XG5cbiAgICBpZiAoIXRvdGFsKSB7XG4gICAgICAgIC8vIHRoaXMgaXMgdGhlIHNhbWUgYXMgQyMncyAoTm9kYSkgYW5kIHB5dGhvbiAoaXNvZGF0ZSkuLi5cbiAgICAgICAgLy8gYnV0IG5vdCBvdGhlciBKUyAoZ29vZy5kYXRlKVxuICAgICAgICByZXR1cm4gJ1AwRCc7XG4gICAgfVxuXG4gICAgdmFyIHRvdGFsU2lnbiA9IHRvdGFsIDwgMCA/ICctJyA6ICcnO1xuICAgIHZhciB5bVNpZ24gPSBzaWduKHRoaXMuX21vbnRocykgIT09IHNpZ24odG90YWwpID8gJy0nIDogJyc7XG4gICAgdmFyIGRheXNTaWduID0gc2lnbih0aGlzLl9kYXlzKSAhPT0gc2lnbih0b3RhbCkgPyAnLScgOiAnJztcbiAgICB2YXIgaG1zU2lnbiA9IHNpZ24odGhpcy5fbWlsbGlzZWNvbmRzKSAhPT0gc2lnbih0b3RhbCkgPyAnLScgOiAnJztcblxuICAgIHJldHVybiB0b3RhbFNpZ24gKyAnUCcgK1xuICAgICAgICAoWSA/IHltU2lnbiArIFkgKyAnWScgOiAnJykgK1xuICAgICAgICAoTSA/IHltU2lnbiArIE0gKyAnTScgOiAnJykgK1xuICAgICAgICAoRCA/IGRheXNTaWduICsgRCArICdEJyA6ICcnKSArXG4gICAgICAgICgoaCB8fCBtIHx8IHMpID8gJ1QnIDogJycpICtcbiAgICAgICAgKGggPyBobXNTaWduICsgaCArICdIJyA6ICcnKSArXG4gICAgICAgIChtID8gaG1zU2lnbiArIG0gKyAnTScgOiAnJykgK1xuICAgICAgICAocyA/IGhtc1NpZ24gKyBzICsgJ1MnIDogJycpO1xufVxuXG52YXIgcHJvdG8kMiA9IER1cmF0aW9uLnByb3RvdHlwZTtcblxucHJvdG8kMi5pc1ZhbGlkICAgICAgICA9IGlzVmFsaWQkMTtcbnByb3RvJDIuYWJzICAgICAgICAgICAgPSBhYnM7XG5wcm90byQyLmFkZCAgICAgICAgICAgID0gYWRkJDE7XG5wcm90byQyLnN1YnRyYWN0ICAgICAgID0gc3VidHJhY3QkMTtcbnByb3RvJDIuYXMgICAgICAgICAgICAgPSBhcztcbnByb3RvJDIuYXNNaWxsaXNlY29uZHMgPSBhc01pbGxpc2Vjb25kcztcbnByb3RvJDIuYXNTZWNvbmRzICAgICAgPSBhc1NlY29uZHM7XG5wcm90byQyLmFzTWludXRlcyAgICAgID0gYXNNaW51dGVzO1xucHJvdG8kMi5hc0hvdXJzICAgICAgICA9IGFzSG91cnM7XG5wcm90byQyLmFzRGF5cyAgICAgICAgID0gYXNEYXlzO1xucHJvdG8kMi5hc1dlZWtzICAgICAgICA9IGFzV2Vla3M7XG5wcm90byQyLmFzTW9udGhzICAgICAgID0gYXNNb250aHM7XG5wcm90byQyLmFzWWVhcnMgICAgICAgID0gYXNZZWFycztcbnByb3RvJDIudmFsdWVPZiAgICAgICAgPSB2YWx1ZU9mJDE7XG5wcm90byQyLl9idWJibGUgICAgICAgID0gYnViYmxlO1xucHJvdG8kMi5jbG9uZSAgICAgICAgICA9IGNsb25lJDE7XG5wcm90byQyLmdldCAgICAgICAgICAgID0gZ2V0JDI7XG5wcm90byQyLm1pbGxpc2Vjb25kcyAgID0gbWlsbGlzZWNvbmRzO1xucHJvdG8kMi5zZWNvbmRzICAgICAgICA9IHNlY29uZHM7XG5wcm90byQyLm1pbnV0ZXMgICAgICAgID0gbWludXRlcztcbnByb3RvJDIuaG91cnMgICAgICAgICAgPSBob3VycztcbnByb3RvJDIuZGF5cyAgICAgICAgICAgPSBkYXlzO1xucHJvdG8kMi53ZWVrcyAgICAgICAgICA9IHdlZWtzO1xucHJvdG8kMi5tb250aHMgICAgICAgICA9IG1vbnRocztcbnByb3RvJDIueWVhcnMgICAgICAgICAgPSB5ZWFycztcbnByb3RvJDIuaHVtYW5pemUgICAgICAgPSBodW1hbml6ZTtcbnByb3RvJDIudG9JU09TdHJpbmcgICAgPSB0b0lTT1N0cmluZyQxO1xucHJvdG8kMi50b1N0cmluZyAgICAgICA9IHRvSVNPU3RyaW5nJDE7XG5wcm90byQyLnRvSlNPTiAgICAgICAgID0gdG9JU09TdHJpbmckMTtcbnByb3RvJDIubG9jYWxlICAgICAgICAgPSBsb2NhbGU7XG5wcm90byQyLmxvY2FsZURhdGEgICAgID0gbG9jYWxlRGF0YTtcblxuLy8gRGVwcmVjYXRpb25zXG5wcm90byQyLnRvSXNvU3RyaW5nID0gZGVwcmVjYXRlKCd0b0lzb1N0cmluZygpIGlzIGRlcHJlY2F0ZWQuIFBsZWFzZSB1c2UgdG9JU09TdHJpbmcoKSBpbnN0ZWFkIChub3RpY2UgdGhlIGNhcGl0YWxzKScsIHRvSVNPU3RyaW5nJDEpO1xucHJvdG8kMi5sYW5nID0gbGFuZztcblxuLy8gU2lkZSBlZmZlY3QgaW1wb3J0c1xuXG4vLyBGT1JNQVRUSU5HXG5cbmFkZEZvcm1hdFRva2VuKCdYJywgMCwgMCwgJ3VuaXgnKTtcbmFkZEZvcm1hdFRva2VuKCd4JywgMCwgMCwgJ3ZhbHVlT2YnKTtcblxuLy8gUEFSU0lOR1xuXG5hZGRSZWdleFRva2VuKCd4JywgbWF0Y2hTaWduZWQpO1xuYWRkUmVnZXhUb2tlbignWCcsIG1hdGNoVGltZXN0YW1wKTtcbmFkZFBhcnNlVG9rZW4oJ1gnLCBmdW5jdGlvbiAoaW5wdXQsIGFycmF5LCBjb25maWcpIHtcbiAgICBjb25maWcuX2QgPSBuZXcgRGF0ZShwYXJzZUZsb2F0KGlucHV0LCAxMCkgKiAxMDAwKTtcbn0pO1xuYWRkUGFyc2VUb2tlbigneCcsIGZ1bmN0aW9uIChpbnB1dCwgYXJyYXksIGNvbmZpZykge1xuICAgIGNvbmZpZy5fZCA9IG5ldyBEYXRlKHRvSW50KGlucHV0KSk7XG59KTtcblxuLy8gU2lkZSBlZmZlY3QgaW1wb3J0c1xuXG5cbmhvb2tzLnZlcnNpb24gPSAnMi4xOS40Jztcblxuc2V0SG9va0NhbGxiYWNrKGNyZWF0ZUxvY2FsKTtcblxuaG9va3MuZm4gICAgICAgICAgICAgICAgICAgID0gcHJvdG87XG5ob29rcy5taW4gICAgICAgICAgICAgICAgICAgPSBtaW47XG5ob29rcy5tYXggICAgICAgICAgICAgICAgICAgPSBtYXg7XG5ob29rcy5ub3cgICAgICAgICAgICAgICAgICAgPSBub3c7XG5ob29rcy51dGMgICAgICAgICAgICAgICAgICAgPSBjcmVhdGVVVEM7XG5ob29rcy51bml4ICAgICAgICAgICAgICAgICAgPSBjcmVhdGVVbml4O1xuaG9va3MubW9udGhzICAgICAgICAgICAgICAgID0gbGlzdE1vbnRocztcbmhvb2tzLmlzRGF0ZSAgICAgICAgICAgICAgICA9IGlzRGF0ZTtcbmhvb2tzLmxvY2FsZSAgICAgICAgICAgICAgICA9IGdldFNldEdsb2JhbExvY2FsZTtcbmhvb2tzLmludmFsaWQgICAgICAgICAgICAgICA9IGNyZWF0ZUludmFsaWQ7XG5ob29rcy5kdXJhdGlvbiAgICAgICAgICAgICAgPSBjcmVhdGVEdXJhdGlvbjtcbmhvb2tzLmlzTW9tZW50ICAgICAgICAgICAgICA9IGlzTW9tZW50O1xuaG9va3Mud2Vla2RheXMgICAgICAgICAgICAgID0gbGlzdFdlZWtkYXlzO1xuaG9va3MucGFyc2Vab25lICAgICAgICAgICAgID0gY3JlYXRlSW5ab25lO1xuaG9va3MubG9jYWxlRGF0YSAgICAgICAgICAgID0gZ2V0TG9jYWxlO1xuaG9va3MuaXNEdXJhdGlvbiAgICAgICAgICAgID0gaXNEdXJhdGlvbjtcbmhvb2tzLm1vbnRoc1Nob3J0ICAgICAgICAgICA9IGxpc3RNb250aHNTaG9ydDtcbmhvb2tzLndlZWtkYXlzTWluICAgICAgICAgICA9IGxpc3RXZWVrZGF5c01pbjtcbmhvb2tzLmRlZmluZUxvY2FsZSAgICAgICAgICA9IGRlZmluZUxvY2FsZTtcbmhvb2tzLnVwZGF0ZUxvY2FsZSAgICAgICAgICA9IHVwZGF0ZUxvY2FsZTtcbmhvb2tzLmxvY2FsZXMgICAgICAgICAgICAgICA9IGxpc3RMb2NhbGVzO1xuaG9va3Mud2Vla2RheXNTaG9ydCAgICAgICAgID0gbGlzdFdlZWtkYXlzU2hvcnQ7XG5ob29rcy5ub3JtYWxpemVVbml0cyAgICAgICAgPSBub3JtYWxpemVVbml0cztcbmhvb2tzLnJlbGF0aXZlVGltZVJvdW5kaW5nICA9IGdldFNldFJlbGF0aXZlVGltZVJvdW5kaW5nO1xuaG9va3MucmVsYXRpdmVUaW1lVGhyZXNob2xkID0gZ2V0U2V0UmVsYXRpdmVUaW1lVGhyZXNob2xkO1xuaG9va3MuY2FsZW5kYXJGb3JtYXQgICAgICAgID0gZ2V0Q2FsZW5kYXJGb3JtYXQ7XG5ob29rcy5wcm90b3R5cGUgICAgICAgICAgICAgPSBwcm90bztcblxucmV0dXJuIGhvb2tzO1xuXG59KSkpO1xuIl19
//# sourceMappingURL=index.js.map?build=27b1311fd237a5c84b052a98226e5c0048a94bc9
