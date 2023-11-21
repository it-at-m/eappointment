(()=>{function e(e){return e&&e.__esModule?e.default:e}// --------------------------------------------------------
// ZMS Admin behavior
// --------------------------------------------------------
// Import base libs
var t,n,r,i={};t="undefined"!=typeof window?window:i,n=function(e,t){var n,r=[],i=Object.getPrototypeOf,o=r.slice,a=r.flat?function(e){return r.flat.call(e)}:function(e){return r.concat.apply([],e)},s=r.push,u=r.indexOf,l={},c=l.toString,f=l.hasOwnProperty,d=f.toString,p=d.call(Object),h={},g=function(e){// Support: Chrome <=57, Firefox <=52
// In some browsers, typeof returns "function" for HTML <object> elements
// (i.e., `typeof document.createElement( "object" ) === "function"`).
// We don't want to classify *any* DOM node as a function.
// Support: QtWeb <=3.8.5, WebKit <=534.34, wkhtmltopdf tool <=0.12.5
// Plus for old WebKit, typeof returns "function" for HTML collections
// (e.g., `typeof document.getElementsByTagName("div") === "function"`). (gh-4756)
return"function"==typeof e&&"number"!=typeof e.nodeType&&"function"!=typeof e.item},v=function(e){return null!=e&&e===e.window},y=e.document,m={type:!0,src:!0,nonce:!0,noModule:!0};function x(e,t,n){var r,i,o=(n=n||y).createElement("script");if(o.text=e,t)for(r in m)// Support: Firefox 64+, Edge 18+
// Some browsers don't support the "nonce" property on scripts.
// On the other hand, just using `getAttribute` is not enough as
// the `nonce` attribute is reset to an empty string whenever it
// becomes browsing-context connected.
// See https://github.com/whatwg/html/issues/2369
// See https://html.spec.whatwg.org/#nonce-attributes
// The `node.getAttribute` check was added for the sake of
// `jQuery.globalEval` so that it can fake a nonce-containing node
// via an object.
(i=t[r]||t.getAttribute&&t.getAttribute(r))&&o.setAttribute(r,i);n.head.appendChild(o).parentNode.removeChild(o)}function b(e){return null==e?e+"":"object"==typeof e||"function"==typeof e?l[c.call(e)]||"object":typeof e}/* global Symbol */// Defining this global in .eslintrc.json would create a danger of using the global
// unguarded in another place, it seems safer to define global only for this module
var w="3.7.1",T=/HTML$/i,C=function(e,t){// The jQuery object is actually just the init constructor 'enhanced'
// Need init if jQuery is called (just allow error to be thrown if not included)
return new C.fn.init(e,t)};function k(e){// Support: real iOS 8.2 only (not reproducible in simulator)
// `in` check used to prevent JIT error (gh-2145)
// hasOwn isn't used here due to false negatives
// regarding Nodelist length in IE
var t=!!e&&"length"in e&&e.length,n=b(e);return!(g(e)||v(e))&&("array"===n||0===t||"number"==typeof t&&t>0&&t-1 in e)}function A(e,t){return e.nodeName&&e.nodeName.toLowerCase()===t.toLowerCase()}C.fn=C.prototype={// The current version of jQuery being used
jquery:w,constructor:C,// The default length of a jQuery object is 0
length:0,toArray:function(){return o.call(this)},// Get the Nth element in the matched element set OR
// Get the whole matched element set as a clean array
get:function(e){return(// Return all the elements in a clean array
null==e?o.call(this):e<0?this[e+this.length]:this[e])},// Take an array of elements and push it onto the stack
// (returning the new matched element set)
pushStack:function(e){// Build a new jQuery matched element set
var t=C.merge(this.constructor(),e);// Return the newly-formed element set
return(// Add the old object onto the stack (as a reference)
t.prevObject=this,t)},// Execute a callback for every element in the matched set.
each:function(e){return C.each(this,e)},map:function(e){return this.pushStack(C.map(this,function(t,n){return e.call(t,n,t)}))},slice:function(){return this.pushStack(o.apply(this,arguments))},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},even:function(){return this.pushStack(C.grep(this,function(e,t){return(t+1)%2}))},odd:function(){return this.pushStack(C.grep(this,function(e,t){return t%2}))},eq:function(e){var t=this.length,n=+e+(e<0?t:0);return this.pushStack(n>=0&&n<t?[this[n]]:[])},end:function(){return this.prevObject||this.constructor()},// For internal use only.
// Behaves like an Array's method, not like a jQuery method.
push:s,sort:r.sort,splice:r.splice},C.extend=C.fn.extend=function(){var e,t,n,r,i,o,a=arguments[0]||{},s=1,u=arguments.length,l=!1;for("boolean"==typeof a&&(l=a,// Skip the boolean and the target
a=arguments[s]||{},s++),"object"==typeof a||g(a)||(a={}),s===u&&(a=this,s--);s<u;s++)// Only deal with non-null/undefined values
if(null!=(e=arguments[s]))for(t in e)r=e[t],"__proto__"!==t&&a!==r&&(l&&r&&(C.isPlainObject(r)||(i=Array.isArray(r)))?(n=a[t],o=i&&!Array.isArray(n)?[]:i||C.isPlainObject(n)?n:{},i=!1,// Never move original objects, clone them
a[t]=C.extend(l,o,r)):void 0!==r&&(a[t]=r));// Return the modified object
return a},C.extend({// Unique for each copy of jQuery on the page
expando:"jQuery"+(w+Math.random()).replace(/\D/g,""),// Assume jQuery is ready without the ready module
isReady:!0,error:function(e){throw Error(e)},noop:function(){},isPlainObject:function(e){var t,n;return(// Detect obvious negatives
// Use toString instead of jQuery.type to catch host objects
!!e&&"[object Object]"===c.call(e)&&(!(t=i(e))||"function"==typeof// Objects with prototype are plain iff they were constructed by a global Object function
(n=f.call(t,"constructor")&&t.constructor)&&d.call(n)===p))},isEmptyObject:function(e){var t;for(t in e)return!1;return!0},// Evaluates a script in a provided context; falls back to the global one
// if not specified.
globalEval:function(e,t,n){x(e,{nonce:t&&t.nonce},n)},each:function(e,t){var n,r=0;if(k(e))for(n=e.length;r<n&&!1!==t.call(e[r],r,e[r]);r++);else for(r in e)if(!1===t.call(e[r],r,e[r]))break;return e},// Retrieve the text value of an array of DOM nodes
text:function(e){var t,n="",r=0,i=e.nodeType;if(!i)for(;t=e[r++];)n+=C.text(t);return 1===i||11===i?e.textContent:9===i?e.documentElement.textContent:3===i||4===i?e.nodeValue:n},// results is for internal usage only
makeArray:function(e,t){var n=t||[];return null!=e&&(k(Object(e))?C.merge(n,"string"==typeof e?[e]:e):s.call(n,e)),n},inArray:function(e,t,n){return null==t?-1:u.call(t,e,n)},isXMLDoc:function(e){var t=e&&e.namespaceURI,n=e&&(e.ownerDocument||e).documentElement;// Assume HTML when documentElement doesn't yet exist, such as inside
// document fragments.
return!T.test(t||n&&n.nodeName||"HTML")},// Support: Android <=4.0 only, PhantomJS 1 only
// push.apply(_, arraylike) throws on ancient WebKit
merge:function(e,t){for(var n=+t.length,r=0,i=e.length;r<n;r++)e[i++]=t[r];return e.length=i,e},grep:function(e,t,n){// Go through the array, only saving the items
// that pass the validator function
for(var r=[],i=0,o=e.length,a=!n;i<o;i++)!t(e[i],i)!==a&&r.push(e[i]);return r},// arg is for internal usage only
map:function(e,t,n){var r,i,o=0,s=[];// Go through the array, translating each of the items to their new values
if(k(e))for(r=e.length;o<r;o++)null!=(i=t(e[o],o,n))&&s.push(i);else for(o in e)null!=(i=t(e[o],o,n))&&s.push(i);// Flatten any nested arrays
return a(s)},// A global GUID counter for objects
guid:1,// jQuery.support is not used in Core but other projects attach their
// properties to it so it needs to exist.
support:h}),"function"==typeof Symbol&&(C.fn[Symbol.iterator]=r[Symbol.iterator]),// Populate the class2type map
C.each("Boolean Number String Function Array Date RegExp Object Error Symbol".split(" "),function(e,t){l["[object "+t+"]"]=t.toLowerCase()});var E=r.pop,S=r.sort,D=r.splice,N="[\\x20\\t\\r\\n\\f]",M=RegExp("^"+N+"+|((?:^|[^\\\\])(?:\\\\.)*)"+N+"+$","g");// Note: an element does not contain itself
C.contains=function(e,t){var n=t&&t.parentNode;return e===n||!!(n&&1===n.nodeType&&// Support: IE 9 - 11+
// IE doesn't have `contains` on SVG.
(e.contains?e.contains(n):e.compareDocumentPosition&&16&e.compareDocumentPosition(n)))};// CSS string/identifier serialization
// https://drafts.csswg.org/cssom/#common-serializing-idioms
var j=/([\0-\x1f\x7f]|^-?\d)|^-$|[^\x80-\uFFFF\w-]/g;function L(e,t){return t?// U+0000 NULL becomes U+FFFD REPLACEMENT CHARACTER
"\x00"===e?"�":e.slice(0,-1)+"\\"+e.charCodeAt(e.length-1).toString(16)+" ":"\\"+e}C.escapeSelector=function(e){return(e+"").replace(j,L)},function(){var t,n,i,a,l,c,d,p,g,v,m=s,x=C.expando,b=0,w=0,T=ee(),k=ee(),j=ee(),L=ee(),q=function(e,t){return e===t&&(l=!0),0},B="checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",// https://www.w3.org/TR/css-syntax-3/#ident-token-diagram
O="(?:\\\\[\\da-fA-F]{1,6}"+N+"?|\\\\[^\\r\\n\\f]|[\\w-]|[^\x00-\\x7f])+",P="\\["+N+"*("+O+")(?:"+N+// Operator (capture 2)
"*([*^$|!~]?=)"+N+// "Attribute values must be CSS identifiers [capture 5] or strings [capture 3 or capture 4]"
"*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|("+O+"))|)"+N+"*\\]",I=":("+O+")(?:\\((('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|((?:\\\\.|[^\\\\()[\\]]|"+P+")*)|.*)\\)|)",H=RegExp(N+"+","g"),R=RegExp("^"+N+"*,"+N+"*"),_=RegExp("^"+N+"*([>+~]|"+N+")"+N+"*"),F=RegExp(N+"|>"),W=new RegExp(I),$=RegExp("^"+O+"$"),z={ID:RegExp("^#("+O+")"),CLASS:RegExp("^\\.("+O+")"),TAG:RegExp("^("+O+"|[*])"),ATTR:RegExp("^"+P),PSEUDO:RegExp("^"+I),CHILD:RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\("+N+"*(even|odd|(([+-]|)(\\d*)n|)"+N+"*(?:([+-]|)"+N+"*(\\d+)|))"+N+"*\\)|)","i"),bool:RegExp("^(?:"+B+")$","i"),// For use in libraries implementing .is()
// We use this for POS matching in `select`
needsContext:RegExp("^"+N+"*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\("+N+"*((?:-\\d)?\\d*)"+N+"*\\)|)(?=[^-]|$)","i")},U=/^(?:input|select|textarea|button)$/i,X=/^h\d$/i,Q=/^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,Y=/[+~]/,// https://www.w3.org/TR/CSS21/syndata.html#escaped-characters
V=RegExp("\\\\[\\da-fA-F]{1,6}"+N+"?|\\\\([^\\r\\n\\f])","g"),J=function(e,t){var n="0x"+e.slice(1)-65536;return t||(n<0?String.fromCharCode(n+65536):String.fromCharCode(n>>10|55296,1023&n|56320))},// Support: IE 9 - 11+, Edge 12 - 18+
// Removing the function wrapper causes a "Permission Denied"
// error in IE/Edge.
G=function(){ea()},K=ec(function(e){return!0===e.disabled&&A(e,"fieldset")},{dir:"parentNode",next:"legend"});// Optimize for push.apply( _, NodeList )
try{m.apply(r=o.call(y.childNodes),y.childNodes),// Support: Android <=4.0
// Detect silently failing push.apply
// eslint-disable-next-line no-unused-expressions
r[y.childNodes.length].nodeType}catch(e){m={apply:function(e,t){s.apply(e,o.call(t))},call:function(e){s.apply(e,o.call(arguments,1))}}}function Z(e,t,n,r){var i,o,a,s,u,l,f,d=t&&t.ownerDocument,v=t?t.nodeType:9;// Return early from calls with invalid selector or context
if(n=n||[],"string"!=typeof e||!e||1!==v&&9!==v&&11!==v)return n;// Try to shortcut find operations (as opposed to filters) in HTML documents
if(!r&&(ea(t),t=t||c,p)){// If the selector is sufficiently simple, try using a "get*By*" DOM method
// (excepting DocumentFragment context, where the methods don't exist)
if(11!==v&&(u=Q.exec(e))){// ID selector
if(i=u[1]){// Document context
if(9===v){if(!(a=t.getElementById(i)))return n;if(a.id===i)return m.call(n,a),n;// Element context
}else // getElementById can match elements by name instead of ID
if(d&&(a=d.getElementById(i))&&Z.contains(t,a)&&a.id===i)return m.call(n,a),n}else if(u[2])return m.apply(n,t.getElementsByTagName(e)),n;else if((i=u[3])&&t.getElementsByClassName)return m.apply(n,t.getElementsByClassName(i)),n}// Take advantage of querySelectorAll
if(!L[e+" "]&&(!g||!g.test(e))){// qSA considers elements outside a scoping root when evaluating child or
// descendant combinators, which is not what we want.
// In such cases, we work around the behavior by prefixing every selector in the
// list with an ID selector referencing the scope context.
// The technique has to be used as well when a leading combinator is used
// as such selectors are not recognized by querySelectorAll.
// Thanks to Andrew Dupont for this technique.
if(f=e,d=t,1===v&&(F.test(e)||_.test(e))){for(// Expand context for sibling selectors
(d=Y.test(e)&&eo(t.parentNode)||t)==t&&h.scope||((s=t.getAttribute("id"))?s=C.escapeSelector(s):t.setAttribute("id",s=x)),o=// Prefix every selector in the list
(l=eu(e)).length;o--;)l[o]=(s?"#"+s:":scope")+" "+el(l[o]);f=l.join(",")}try{return m.apply(n,d.querySelectorAll(f)),n}catch(t){L(e,!0)}finally{s===x&&t.removeAttribute("id")}}}// All others
return eh(e.replace(M,"$1"),t,n,r)}/**
 * Create key-value caches of limited size
 * @returns {function(string, object)} Returns the Object data after storing it on itself with
 *	property name the (space-suffixed) string and (if the cache is larger than Expr.cacheLength)
 *	deleting the oldest entry
 */function ee(){var e=[];return function t(r,i){return e.push(r+" ")>n.cacheLength&&delete t[e.shift()],t[r+" "]=i}}/**
 * Mark a function for special use by jQuery selector module
 * @param {Function} fn The function to mark
 */function et(e){return e[x]=!0,e}/**
 * Support testing using an element
 * @param {Function} fn Passed the created element and returns a boolean result
 */function en(e){var t=c.createElement("fieldset");try{return!!e(t)}catch(e){return!1}finally{t.parentNode&&t.parentNode.removeChild(t),// release memory in IE
t=null}}/**
 * Returns a function to use in pseudos for :enabled/:disabled
 * @param {Boolean} disabled true for :disabled; false for :enabled
 */function er(e){// Known :disabled false positives: fieldset[disabled] > legend:nth-of-type(n+2) :can-disable
return function(t){// Only certain elements can match :enabled or :disabled
// https://html.spec.whatwg.org/multipage/scripting.html#selector-enabled
// https://html.spec.whatwg.org/multipage/scripting.html#selector-disabled
if("form"in t)return(// Check for inherited disabledness on relevant non-disabled elements:
// * listed form-associated elements in a disabled fieldset
//   https://html.spec.whatwg.org/multipage/forms.html#category-listed
//   https://html.spec.whatwg.org/multipage/forms.html#concept-fe-disabled
// * option elements in a disabled optgroup
//   https://html.spec.whatwg.org/multipage/forms.html#concept-option-disabled
// All such elements have a "form" property.
t.parentNode&&!1===t.disabled?// Option elements defer to a parent optgroup if present
"label"in t?"label"in t.parentNode?t.parentNode.disabled===e:t.disabled===e:t.isDisabled===e||// Where there is no isDisabled, check manually
!e!==t.isDisabled&&K(t)===e:t.disabled===e);return"label"in t&&t.disabled===e}}/**
 * Returns a function to use in pseudos for positionals
 * @param {Function} fn
 */function ei(e){return et(function(t){return t=+t,et(function(n,r){// Match elements found at the specified indexes
for(var i,o=e([],n.length,t),a=o.length;a--;)n[i=o[a]]&&(n[i]=!(r[i]=n[i]))})})}/**
 * Checks a node for validity as a jQuery selector context
 * @param {Element|Object=} context
 * @returns {Element|Object|Boolean} The input node if acceptable, otherwise a falsy value
 */function eo(e){return e&&void 0!==e.getElementsByTagName&&e}/**
 * Sets document-related variables once based on the current document
 * @param {Element|Object} [node] An element or document object to use to set the document
 * @returns {Object} Returns the current document
 */function ea(e){var t,r=e?e.ownerDocument||e:y;return r!=c&&9===r.nodeType&&r.documentElement&&(d=// Update global variables
(c=r).documentElement,p=!C.isXMLDoc(c),// Support: iOS 7 only, IE 9 - 11+
// Older browsers didn't support unprefixed `matches`.
v=d.matches||d.webkitMatchesSelector||d.msMatchesSelector,d.msMatchesSelector&&// Support: IE 11+, Edge 17 - 18+
// IE/Edge sometimes throw a "Permission denied" error when strict-comparing
// two documents; shallow comparisons work.
// eslint-disable-next-line eqeqeq
y!=c&&(t=c.defaultView)&&t.top!==t&&t.addEventListener("unload",G),// Support: IE <10
// Check if getElementById returns elements by name
// The broken getElementById methods don't pick up programmatically-set names,
// so use a roundabout getElementsByName test
h.getById=en(function(e){return d.appendChild(e).id=C.expando,!c.getElementsByName||!c.getElementsByName(C.expando).length}),// Support: IE 9 only
// Check to see if it's possible to do matchesSelector
// on a disconnected node.
h.disconnectedMatch=en(function(e){return v.call(e,"*")}),// Support: IE 9 - 11+, Edge 12 - 18+
// IE/Edge don't support the :scope pseudo-class.
h.scope=en(function(){return c.querySelectorAll(":scope")}),// Support: Chrome 105 - 111 only, Safari 15.4 - 16.3 only
// Make sure the `:has()` argument is parsed unforgivingly.
// We include `*` in the test to detect buggy implementations that are
// _selectively_ forgiving (specifically when the list includes at least
// one valid selector).
// Note that we treat complete lack of support for `:has()` as if it were
// spec-compliant support, which is fine because use of `:has()` in such
// environments will fail in the qSA path and fall back to jQuery traversal
// anyway.
h.cssHas=en(function(){try{return c.querySelector(":has(*,:jqfake)"),!1}catch(e){return!0}}),h.getById?(n.filter.ID=function(e){var t=e.replace(V,J);return function(e){return e.getAttribute("id")===t}},n.find.ID=function(e,t){if(void 0!==t.getElementById&&p){var n=t.getElementById(e);return n?[n]:[]}}):(n.filter.ID=function(e){var t=e.replace(V,J);return function(e){var n=void 0!==e.getAttributeNode&&e.getAttributeNode("id");return n&&n.value===t}},// Support: IE 6 - 7 only
// getElementById is not reliable as a find shortcut
n.find.ID=function(e,t){if(void 0!==t.getElementById&&p){var n,r,i,o=t.getElementById(e);if(o){if(// Verify the id attribute
(n=o.getAttributeNode("id"))&&n.value===e)return[o];for(// Fall back on getElementsByName
i=t.getElementsByName(e),r=0;o=i[r++];)if((n=o.getAttributeNode("id"))&&n.value===e)return[o]}return[]}}),// Tag
n.find.TAG=function(e,t){return void 0!==t.getElementsByTagName?t.getElementsByTagName(e):t.querySelectorAll(e)},// Class
n.find.CLASS=function(e,t){if(void 0!==t.getElementsByClassName&&p)return t.getElementsByClassName(e)},/* QSA/matchesSelector
	---------------------------------------------------------------------- */// QSA and matchesSelector support
g=[],// Build QSA regex
// Regex strategy adopted from Diego Perini
en(function(e){var t;d.appendChild(e).innerHTML="<a id='"+x+"' href='' disabled='disabled'></a><select id='"+x+"-\r\\' disabled='disabled'><option selected=''></option></select>",e.querySelectorAll("[selected]").length||g.push("\\["+N+"*(?:value|"+B+")"),e.querySelectorAll("[id~="+x+"-]").length||g.push("~="),e.querySelectorAll("a#"+x+"+*").length||g.push(".#.+[+~]"),e.querySelectorAll(":checked").length||g.push(":checked"),// Support: Windows 8 Native Apps
// The type and name attributes are restricted during .innerHTML assignment
(t=c.createElement("input")).setAttribute("type","hidden"),e.appendChild(t).setAttribute("name","D"),// Support: IE 9 - 11+
// IE's :disabled selector does not pick up the children of disabled fieldsets
// Support: Chrome <=105+, Firefox <=104+, Safari <=15.4+
// In some of the document kinds, these selectors wouldn't work natively.
// This is probably OK but for backwards compatibility we want to maintain
// handling them through jQuery traversal in jQuery 3.x.
d.appendChild(e).disabled=!0,2!==e.querySelectorAll(":disabled").length&&g.push(":enabled",":disabled"),// Support: IE 11+, Edge 15 - 18+
// IE 11/Edge don't find elements on a `[name='']` query in some cases.
// Adding a temporary attribute to the document before the selection works
// around the issue.
// Interestingly, IE 10 & older don't seem to have the issue.
(t=c.createElement("input")).setAttribute("name",""),e.appendChild(t),e.querySelectorAll("[name='']").length||g.push("\\["+N+"*name"+N+"*="+N+"*(?:''|\"\")")}),h.cssHas||// Our regular `try-catch` mechanism fails to detect natively-unsupported
// pseudo-classes inside `:has()` (such as `:has(:contains("Foo"))`)
// in browsers that parse the `:has()` argument as a forgiving selector list.
// https://drafts.csswg.org/selectors/#relational now requires the argument
// to be parsed unforgivingly, but browsers have not yet fully adjusted.
g.push(":has"),g=g.length&&new RegExp(g.join("|")),/* Sorting
	---------------------------------------------------------------------- */// Document order sorting
q=function(e,t){// Flag for duplicate removal
if(e===t)return l=!0,0;// Sort on method existence if only one input has compareDocumentPosition
var n=!e.compareDocumentPosition-!t.compareDocumentPosition;return n||(1&// Calculate position if both inputs belong to the same document
// Support: IE 11+, Edge 17 - 18+
// IE/Edge sometimes throw a "Permission denied" error when strict-comparing
// two documents; shallow comparisons work.
// eslint-disable-next-line eqeqeq
(n=(e.ownerDocument||e)==(t.ownerDocument||t)?e.compareDocumentPosition(t):1)||!h.sortDetached&&t.compareDocumentPosition(e)===n?// Choose the first element that is related to our preferred document
// Support: IE 11+, Edge 17 - 18+
// IE/Edge sometimes throw a "Permission denied" error when strict-comparing
// two documents; shallow comparisons work.
// eslint-disable-next-line eqeqeq
e===c||e.ownerDocument==y&&Z.contains(y,e)?-1:t===c||t.ownerDocument==y&&Z.contains(y,t)?1:a?u.call(a,e)-u.call(a,t):0:4&n?-1:1)}),c}// Add button/input type pseudos
for(t in Z.matches=function(e,t){return Z(e,null,null,t)},Z.matchesSelector=function(e,t){if(ea(e),p&&!L[t+" "]&&(!g||!g.test(t)))try{var n=v.call(e,t);// IE 9's matchesSelector returns false on disconnected nodes
if(n||h.disconnectedMatch||// As well, disconnected nodes are said to be in a document
// fragment in IE 9
e.document&&11!==e.document.nodeType)return n}catch(e){L(t,!0)}return Z(t,c,null,[e]).length>0},Z.contains=function(e,t){return(e.ownerDocument||e)!=c&&ea(e),C.contains(e,t)},Z.attr=function(e,t){// Set document vars if needed
// Support: IE 11+, Edge 17 - 18+
// IE/Edge sometimes throw a "Permission denied" error when strict-comparing
// two documents; shallow comparisons work.
// eslint-disable-next-line eqeqeq
(e.ownerDocument||e)!=c&&ea(e);var r=n.attrHandle[t.toLowerCase()],i=r&&f.call(n.attrHandle,t.toLowerCase())?r(e,t,!p):void 0;return void 0!==i?i:e.getAttribute(t)},Z.error=function(e){throw Error("Syntax error, unrecognized expression: "+e)},/**
 * Document sorting and removing duplicates
 * @param {ArrayLike} results
 */C.uniqueSort=function(e){var t,n=[],r=0,i=0;if(// Unless we *know* we can detect duplicates, assume their presence
//
// Support: Android <=4.0+
// Testing for detecting duplicates is unpredictable so instead assume we can't
// depend on duplicate detection in all browsers without a stable sort.
l=!h.sortStable,a=!h.sortStable&&o.call(e,0),S.call(e,q),l){for(;t=e[i++];)t===e[i]&&(r=n.push(i));for(;r--;)D.call(e,n[r],1)}return(// Clear input after sorting to release objects
// See https://github.com/jquery/sizzle/pull/225
a=null,e)},C.fn.uniqueSort=function(){return this.pushStack(C.uniqueSort(o.apply(this)))},(n=C.expr={// Can be adjusted by the user
cacheLength:50,createPseudo:et,match:z,attrHandle:{},find:{},relative:{">":{dir:"parentNode",first:!0}," ":{dir:"parentNode"},"+":{dir:"previousSibling",first:!0},"~":{dir:"previousSibling"}},preFilter:{ATTR:function(e){return e[1]=e[1].replace(V,J),// Move the given value to match[3] whether quoted or unquoted
e[3]=(e[3]||e[4]||e[5]||"").replace(V,J),"~="===e[2]&&(e[3]=" "+e[3]+" "),e.slice(0,4)},CHILD:function(e){return(/* matches from matchExpr["CHILD"]
				1 type (only|nth|...)
				2 what (child|of-type)
				3 argument (even|odd|\d*|\d*n([+-]\d+)?|...)
				4 xn-component of xn+y argument ([+-]?\d*n|)
				5 sign of xn-component
				6 x of xn-component
				7 sign of y-component
				8 y of y-component
			*/e[1]=e[1].toLowerCase(),"nth"===e[1].slice(0,3)?(e[3]||Z.error(e[0]),// numeric x and y parameters for Expr.filter.CHILD
// remember that false/true cast respectively to 0/1
e[4]=+(e[4]?e[5]+(e[6]||1):2*("even"===e[3]||"odd"===e[3])),e[5]=+(e[7]+e[8]||"odd"===e[3])):e[3]&&Z.error(e[0]),e)},PSEUDO:function(e){var t,n=!e[6]&&e[2];return z.CHILD.test(e[0])?null:(e[3]?e[2]=e[4]||e[5]||"":n&&W.test(n)&&// Get excess from tokenize (recursively)
(t=eu(n,!0))&&// advance to the next closing parenthesis
(t=n.indexOf(")",n.length-t)-n.length)&&(// excess is a negative index
e[0]=e[0].slice(0,t),e[2]=n.slice(0,t)),e.slice(0,3))}},filter:{TAG:function(e){var t=e.replace(V,J).toLowerCase();return"*"===e?function(){return!0}:function(e){return A(e,t)}},CLASS:function(e){var t=T[e+" "];return t||(t=RegExp("(^|"+N+")"+e+"("+N+"|$)"),T(e,function(e){return t.test("string"==typeof e.className&&e.className||void 0!==e.getAttribute&&e.getAttribute("class")||"")}))},ATTR:function(e,t,n){return function(r){var i=Z.attr(r,e);return null==i?"!="===t:!t||((i+="","="===t)?i===n:"!="===t?i!==n:"^="===t?n&&0===i.indexOf(n):"*="===t?n&&i.indexOf(n)>-1:"$="===t?n&&i.slice(-n.length)===n:"~="===t?(" "+i.replace(H," ")+" ").indexOf(n)>-1:"|="===t&&(i===n||i.slice(0,n.length+1)===n+"-"))}},CHILD:function(e,t,n,r,i){var o="nth"!==e.slice(0,3),a="last"!==e.slice(-4),s="of-type"===t;return 1===r&&0===i?function(e){return!!e.parentNode}:function(t,n,u){var l,c,f,d,p,h=o!==a?"nextSibling":"previousSibling",g=t.parentNode,v=s&&t.nodeName.toLowerCase(),y=!u&&!s,m=!1;if(g){// :(first|last|only)-(child|of-type)
if(o){for(;h;){for(f=t;f=f[h];)if(s?A(f,v):1===f.nodeType)return!1;// Reverse direction for :only-* (if we haven't yet done so)
p=h="only"===e&&!p&&"nextSibling"}return!0}// non-xml :nth-child(...) stores cache data on `parent`
if(p=[a?g.firstChild:g.lastChild],a&&y){for(m=(d=(l=// Seek `elem` from a previously-cached index
(c=g[x]||(g[x]={}))[e]||[])[0]===b&&l[1])&&l[2],f=d&&g.childNodes[d];f=++d&&f&&f[h]||// Fallback to seeking `elem` from the start
(m=d=0)||p.pop();)if(1===f.nodeType&&++m&&f===t){c[e]=[b,d,m];break}}else // xml :nth-child(...)
// or :nth-last-child(...) or :nth(-last)?-of-type(...)
if(y&&(m=d=(l=(c=t[x]||(t[x]={}))[e]||[])[0]===b&&l[1]),!1===m)// Use the same loop as above to seek `elem` from the start
for(;(f=++d&&f&&f[h]||(m=d=0)||p.pop())&&(!((s?A(f,v):1===f.nodeType)&&++m)||(y&&((c=f[x]||(f[x]={}))[e]=[b,m]),f!==t)););return(// Incorporate the offset, then check against cycle size
(m-=i)===r||m%r==0&&m/r>=0)}}},PSEUDO:function(e,t){// pseudo-class names are case-insensitive
// https://www.w3.org/TR/selectors/#pseudo-classes
// Prioritize by case sensitivity in case custom pseudos are added with uppercase letters
// Remember that setFilters inherits from pseudos
var r,i=n.pseudos[e]||n.setFilters[e.toLowerCase()]||Z.error("unsupported pseudo: "+e);return(// The user may use createPseudo to indicate that
// arguments are needed to create the filter function
// just as jQuery does
i[x]?i(t):i.length>1?(r=[e,e,"",t],n.setFilters.hasOwnProperty(e.toLowerCase())?et(function(e,n){for(var r,o=i(e,t),a=o.length;a--;)r=u.call(e,o[a]),e[r]=!(n[r]=o[a])}):function(e){return i(e,0,r)}):i)}},pseudos:{// Potentially complex pseudos
not:et(function(e){// Trim the selector passed to compile
// to avoid treating leading and trailing
// spaces as combinators
var t=[],n=[],r=ep(e.replace(M,"$1"));return r[x]?et(function(e,t,n,i){// Match elements unmatched by `matcher`
for(var o,a=r(e,null,i,[]),s=e.length;s--;)(o=a[s])&&(e[s]=!(t[s]=o))}):function(e,i,o){return t[0]=e,r(t,null,o,n),// Don't keep the element
// (see https://github.com/jquery/sizzle/issues/299)
t[0]=null,!n.pop()}}),has:et(function(e){return function(t){return Z(e,t).length>0}}),contains:et(function(e){return e=e.replace(V,J),function(t){return(t.textContent||C.text(t)).indexOf(e)>-1}}),// "Whether an element is represented by a :lang() selector
// is based solely on the element's language value
// being equal to the identifier C,
// or beginning with the identifier C immediately followed by "-".
// The matching of C against the element's language value is performed case-insensitively.
// The identifier C does not have to be a valid language name."
// https://www.w3.org/TR/selectors/#lang-pseudo
lang:et(function(e){return $.test(e||"")||Z.error("unsupported lang: "+e),e=e.replace(V,J).toLowerCase(),function(t){var n;do if(n=p?t.lang:t.getAttribute("xml:lang")||t.getAttribute("lang"))return(n=n.toLowerCase())===e||0===n.indexOf(e+"-");while((t=t.parentNode)&&1===t.nodeType)return!1}}),// Miscellaneous
target:function(t){var n=e.location&&e.location.hash;return n&&n.slice(1)===t.id},root:function(e){return e===d},focus:function(e){return e===// Support: IE <=9 only
// Accessing document.activeElement can throw unexpectedly
// https://bugs.jquery.com/ticket/13393
function(){try{return c.activeElement}catch(e){}}()&&c.hasFocus()&&!!(e.type||e.href||~e.tabIndex)},// Boolean properties
enabled:er(!1),disabled:er(!0),checked:function(e){// In CSS3, :checked should return both checked and selected elements
// https://www.w3.org/TR/2011/REC-css3-selectors-20110929/#checked
return A(e,"input")&&!!e.checked||A(e,"option")&&!!e.selected},selected:function(e){return e.parentNode&&e.parentNode.selectedIndex,!0===e.selected},// Contents
empty:function(e){// https://www.w3.org/TR/selectors/#empty-pseudo
// :empty is negated by element (1) or content nodes (text: 3; cdata: 4; entity ref: 5),
//   but not by others (comment: 8; processing instruction: 7; etc.)
// nodeType < 6 works because attributes (2) do not appear as children
for(e=e.firstChild;e;e=e.nextSibling)if(e.nodeType<6)return!1;return!0},parent:function(e){return!n.pseudos.empty(e)},// Element/input types
header:function(e){return X.test(e.nodeName)},input:function(e){return U.test(e.nodeName)},button:function(e){return A(e,"input")&&"button"===e.type||A(e,"button")},text:function(e){var t;return A(e,"input")&&"text"===e.type&&// Support: IE <10 only
// New HTML5 attribute values (e.g., "search") appear
// with elem.type === "text"
(null==(t=e.getAttribute("type"))||"text"===t.toLowerCase())},// Position-in-collection
first:ei(function(){return[0]}),last:ei(function(e,t){return[t-1]}),eq:ei(function(e,t,n){return[n<0?n+t:n]}),even:ei(function(e,t){for(var n=0;n<t;n+=2)e.push(n);return e}),odd:ei(function(e,t){for(var n=1;n<t;n+=2)e.push(n);return e}),lt:ei(function(e,t,n){var r;for(r=n<0?n+t:n>t?t:n;--r>=0;)e.push(r);return e}),gt:ei(function(e,t,n){for(var r=n<0?n+t:n;++r<t;)e.push(r);return e})}}).pseudos.nth=n.pseudos.eq,{radio:!0,checkbox:!0,file:!0,password:!0,image:!0})n.pseudos[t]=/**
 * Returns a function to use in pseudos for input types
 * @param {String} type
 */function(e){return function(t){return A(t,"input")&&t.type===e}}(t);for(t in{submit:!0,reset:!0})n.pseudos[t]=/**
 * Returns a function to use in pseudos for buttons
 * @param {String} type
 */function(e){return function(t){return(A(t,"input")||A(t,"button"))&&t.type===e}}(t);// Easy API for creating new setFilters
function es(){}function eu(e,t){var r,i,o,a,s,u,l,c=k[e+" "];if(c)return t?0:c.slice(0);for(s=e,u=[],l=n.preFilter;s;){// Filters
for(a in(!r||(i=R.exec(s)))&&(i&&(s=s.slice(i[0].length)||s),u.push(o=[])),r=!1,(i=_.exec(s))&&(r=i.shift(),o.push({value:r,// Cast descendant combinators to space
type:i[0].replace(M," ")}),s=s.slice(r.length)),n.filter)(i=z[a].exec(s))&&(!l[a]||(i=l[a](i)))&&(r=i.shift(),o.push({value:r,type:a,matches:i}),s=s.slice(r.length));if(!r)break}return(// Return the length of the invalid excess
// if we're just parsing
// Otherwise, throw an error or return tokens
t?s.length:s?Z.error(e):k(e,u).slice(0))}function el(e){for(var t=0,n=e.length,r="";t<n;t++)r+=e[t].value;return r}function ec(e,t,n){var r=t.dir,i=t.next,o=i||r,a=n&&"parentNode"===o,s=w++;return t.first?function(t,n,i){for(;t=t[r];)if(1===t.nodeType||a)return e(t,n,i);return!1}:function(t,n,u){var l,c,f=[b,s];// We can't set arbitrary data on XML nodes, so they don't benefit from combinator caching
if(u){for(;t=t[r];)if((1===t.nodeType||a)&&e(t,n,u))return!0}else for(;t=t[r];)if(1===t.nodeType||a){if(c=t[x]||(t[x]={}),i&&A(t,i))t=t[r]||t;else{if((l=c[o])&&l[0]===b&&l[1]===s)return f[2]=l[2];// A match means we're done; a fail means we have to keep checking
if(// Reuse newcache so results back-propagate to previous elements
c[o]=f,f[2]=e(t,n,u))return!0}}return!1}}function ef(e){return e.length>1?function(t,n,r){for(var i=e.length;i--;)if(!e[i](t,n,r))return!1;return!0}:e[0]}function ed(e,t,n,r,i){for(var o,a=[],s=0,u=e.length,l=null!=t;s<u;s++)(o=e[s])&&(!n||n(o,r,i))&&(a.push(o),l&&t.push(s));return a}function ep(e,t/* Internal Use Only */){var r,o,a,s,l=[],f=[],d=j[e+" "];if(!d){for(t||(t=eu(e)),s=t.length;s--;)(d=function e(t){for(var r,o,a,s=t.length,l=n.relative[t[0].type],c=l||n.relative[" "],f=l?1:0,d=ec(function(e){return e===r},c,!0),p=ec(function(e){return u.call(r,e)>-1},c,!0),h=[function(e,t,n){// Support: IE 11+, Edge 17 - 18+
// IE/Edge sometimes throw a "Permission denied" error when strict-comparing
// two documents; shallow comparisons work.
// eslint-disable-next-line eqeqeq
var o=!l&&(n||t!=i)||((r=t).nodeType?d(e,t,n):p(e,t,n));return(// Avoid hanging onto element
// (see https://github.com/jquery/sizzle/issues/299)
r=null,o)}];f<s;f++)if(o=n.relative[t[f].type])h=[ec(ef(h),o)];else{// Return special upon seeing a positional matcher
if((o=n.filter[t[f].type].apply(null,t[f].matches))[x]){for(// Find the next relative operator (if any) for proper handling
a=++f;a<s&&!n.relative[t[a].type];a++);return function e(t,n,r,i,o,a){return i&&!i[x]&&(i=e(i)),o&&!o[x]&&(o=e(o,a)),et(function(e,a,s,l){var c,f,d,p,h=[],g=[],v=a.length,y=e||function(e,t,n){for(var r=0,i=t.length;r<i;r++)Z(e,t[r],n);return n}(n||"*",s.nodeType?[s]:s,[]),x=t&&(e||!n)?ed(y,h,t,s,l):y;// Apply postFilter
if(r?// Find primary matches
r(x,// If we have a postFinder, or filtered seed, or non-seed postFilter
// or preexisting results,
p=o||(e?t:v||i)?[]:a,s,l):p=x,i)for(c=ed(p,g),i(c,[],s,l),// Un-match failing elements by moving them back to matcherIn
f=c.length;f--;)(d=c[f])&&(p[g[f]]=!(x[g[f]]=d));if(e){if(o||t){if(o){for(// Get the final matcherOut by condensing this intermediate into postFinder contexts
c=[],f=p.length;f--;)(d=p[f])&&c.push(x[f]=d);o(null,p=[],c,l)}for(// Move matched elements from seed to results to keep them synchronized
f=p.length;f--;)(d=p[f])&&(c=o?u.call(e,d):h[f])>-1&&(e[c]=!(a[c]=d))}}else p=ed(p===a?p.splice(v,p.length):p),o?o(null,a,p,l):m.apply(a,p)})}(f>1&&ef(h),f>1&&el(t.slice(0,f-1).concat({value:" "===t[f-2].type?"*":""})).replace(M,"$1"),o,f<a&&e(t.slice(f,a)),a<s&&e(t=t.slice(a)),a<s&&el(t))}h.push(o)}return ef(h)}(t[s]))[x]?l.push(d):f.push(d);// Save selector and tokenization
// Cache the compiled function
(d=j(e,(r=l.length>0,o=f.length>0,a=function(e,t,a,s,u){var d,h,g,v=0,y="0",x=e&&[],w=[],T=i,k=e||o&&n.find.TAG("*",u),A=b+=null==T?1:Math.random()||.1,S=k.length;// Add elements passing elementMatchers directly to results
// Support: iOS <=7 - 9 only
// Tolerate NodeList properties (IE: "length"; Safari: <number>) matching
// elements by id. (see trac-14142)
for(u&&// IE/Edge sometimes throw a "Permission denied" error when strict-comparing
// two documents; shallow comparisons work.
// eslint-disable-next-line eqeqeq
(i=t==c||t||u);y!==S&&null!=(d=k[y]);y++){if(o&&d){for(h=0,t||d.ownerDocument==c||(ea(d),a=!p);g=f[h++];)if(g(d,t||c,a)){m.call(s,d);break}u&&(b=A)}// Track unmatched elements for set filters
r&&((d=!g&&d)&&v--,e&&x.push(d))}// Apply set filters to unmatched elements
// NOTE: This can be skipped if there are no unmatched elements (i.e., `matchedCount`
// equals `i`), unless we didn't visit _any_ elements in the above loop because we have
// no element matchers and no seed.
// Incrementing an initially-string "0" `i` allows `i` to remain a string only in that
// case, which will result in a "00" `matchedCount` that differs from `i` but is also
// numerically zero.
if(// `i` is now the count of elements visited above, and adding it to `matchedCount`
// makes the latter nonnegative.
v+=y,r&&y!==v){for(h=0;g=l[h++];)g(x,w,t,a);if(e){// Reintegrate element matches to eliminate the need for sorting
if(v>0)for(;y--;)x[y]||w[y]||(w[y]=E.call(s));// Discard index placeholder values to get only actual matches
w=ed(w)}// Add matches to results
m.apply(s,w),u&&!e&&w.length>0&&v+l.length>1&&C.uniqueSort(s)}return u&&(b=A,i=T),x},r?et(a):a))).selector=e}return d}/**
 * A low-level selection function that works with jQuery's compiled
 *  selector functions
 * @param {String|Function} selector A selector or a pre-compiled
 *  selector function built with jQuery selector compile
 * @param {Element} context
 * @param {Array} [results]
 * @param {Array} [seed] A set of elements to match against
 */function eh(e,t,r,i){var o,a,s,u,l,c="function"==typeof e&&e,f=!i&&eu(e=c.selector||e);// Try to minimize operations if there is only one selector in the list and no seed
// (the latter of which guarantees us context)
if(r=r||[],1===f.length){if(// Reduce context if the leading compound selector is an ID
(a=f[0]=f[0].slice(0)).length>2&&"ID"===(s=a[0]).type&&9===t.nodeType&&p&&n.relative[a[1].type]){if(!(t=(n.find.ID(s.matches[0].replace(V,J),t)||[])[0]))return r;c&&(t=t.parentNode),e=e.slice(a.shift().value.length)}for(// Fetch a seed set for right-to-left matching
o=z.needsContext.test(e)?0:a.length;// Abort if we hit a combinator
o--&&(s=a[o],!n.relative[u=s.type]);)if((l=n.find[u])&&(i=l(s.matches[0].replace(V,J),Y.test(a[0].type)&&eo(t.parentNode)||t))){if(// If seed is empty or no tokens remain, we can return early
a.splice(o,1),!(e=i.length&&el(a)))return m.apply(r,i),r;break}}return(// Compile and execute a filtering function if one is not provided
// Provide `match` to avoid retokenization if we modified the selector above
(c||ep(e,f))(i,t,!p,r,!t||Y.test(e)&&eo(t.parentNode)||t),r)}es.prototype=n.filters=n.pseudos,n.setFilters=new es,// One-time assignments
// Support: Android <=4.0 - 4.1+
// Sort stability
h.sortStable=x.split("").sort(q).join("")===x,// Initialize against the default document
ea(),// Support: Android <=4.0 - 4.1+
// Detached nodes confoundingly follow *each other*
h.sortDetached=en(function(e){// Should return 1, but returns 4 (following)
return 1&e.compareDocumentPosition(c.createElement("fieldset"))}),C.find=Z,// Deprecated
C.expr[":"]=C.expr.pseudos,C.unique=C.uniqueSort,// These have always been private, but they used to be documented as part of
// Sizzle so let's maintain them for now for backwards compatibility purposes.
Z.compile=ep,Z.select=eh,Z.setDocument=ea,Z.tokenize=eu,Z.escape=C.escapeSelector,Z.getText=C.text,Z.isXML=C.isXMLDoc,Z.selectors=C.expr,Z.support=C.support,Z.uniqueSort=C.uniqueSort;/* eslint-enable */}();var q=function(e,t,n){for(var r=[],i=void 0!==n;(e=e[t])&&9!==e.nodeType;)if(1===e.nodeType){if(i&&C(e).is(n))break;r.push(e)}return r},B=function(e,t){for(var n=[];e;e=e.nextSibling)1===e.nodeType&&e!==t&&n.push(e);return n},O=C.expr.match.needsContext,P=/^<([a-z][^\/\0>:\x20\t\r\n\f]*)[\x20\t\r\n\f]*\/?>(?:<\/\1>|)$/i;// Implement the identical functionality for filter and not
function I(e,t,n){return g(t)?C.grep(e,function(e,r){return!!t.call(e,r,e)!==n}):t.nodeType?C.grep(e,function(e){return e===t!==n}):"string"!=typeof t?C.grep(e,function(e){return u.call(t,e)>-1!==n}):C.filter(t,e,n)}C.filter=function(e,t,n){var r=t[0];return(n&&(e=":not("+e+")"),1===t.length&&1===r.nodeType)?C.find.matchesSelector(r,e)?[r]:[]:C.find.matches(e,C.grep(t,function(e){return 1===e.nodeType}))},C.fn.extend({find:function(e){var t,n,r=this.length,i=this;if("string"!=typeof e)return this.pushStack(C(e).filter(function(){for(t=0;t<r;t++)if(C.contains(i[t],this))return!0}));for(t=0,n=this.pushStack([]);t<r;t++)C.find(e,i[t],n);return r>1?C.uniqueSort(n):n},filter:function(e){return this.pushStack(I(this,e||[],!1))},not:function(e){return this.pushStack(I(this,e||[],!0))},is:function(e){return!!I(this,// so $("p:first").is("p:last") won't return true for a doc with two "p".
"string"==typeof e&&O.test(e)?C(e):e||[],!1).length}});// Initialize a jQuery object
// A central reference to the root jQuery(document)
var H,// Prioritize #id over <tag> to avoid XSS via location.hash (trac-9521)
// Strict HTML recognition (trac-11290: must start with <)
// Shortcut simple #id case for speed
R=/^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]+))$/;// Give the init function the jQuery prototype for later instantiation
(C.fn.init=function(e,t,n){var r,i;// HANDLE: $(""), $(null), $(undefined), $(false)
if(!e)return this;// Handle HTML strings
if(// Method init() accepts an alternate rootjQuery
// so migrate can support jQuery.sub (gh-2101)
n=n||H,"string"==typeof e){// Match html or make sure no context is specified for #id
if((r="<"===e[0]&&">"===e[e.length-1]&&e.length>=3?[null,e,null]:R.exec(e))&&(r[1]||!t)){// HANDLE: $(html) -> $(array)
if(!r[1])return(i=y.getElementById(r[2]))&&(// Inject the element directly into the jQuery object
this[0]=i,this.length=1),this;// HANDLE: $(html, props)
if(t=t instanceof C?t[0]:t,// Option to run scripts is true for back-compat
// Intentionally let the error be thrown if parseHTML is not present
C.merge(this,C.parseHTML(r[1],t&&t.nodeType?t.ownerDocument||t:y,!0)),P.test(r[1])&&C.isPlainObject(t))for(r in t)g(this[r])?this[r](t[r]):this.attr(r,t[r]);return this}return!t||t.jquery?(t||n).find(e):this.constructor(t).find(e);// HANDLE: $(DOMElement)
}return e.nodeType?(this[0]=e,this.length=1,this):g(e)?void 0!==n.ready?n.ready(e):e(C):C.makeArray(e,this)}).prototype=C.fn,// Initialize central reference
H=C(y);var _=/^(?:parents|prev(?:Until|All))/,F={children:!0,contents:!0,next:!0,prev:!0};function W(e,t){for(;(e=e[t])&&1!==e.nodeType;);return e}C.fn.extend({has:function(e){var t=C(e,this),n=t.length;return this.filter(function(){for(var e=0;e<n;e++)if(C.contains(this,t[e]))return!0})},closest:function(e,t){var n,r=0,i=this.length,o=[],a="string"!=typeof e&&C(e);// Positional selectors never match, since there's no _selection_ context
if(!O.test(e)){for(;r<i;r++)for(n=this[r];n&&n!==t;n=n.parentNode)if(n.nodeType<11&&(a?a.index(n)>-1:1===n.nodeType&&C.find.matchesSelector(n,e))){o.push(n);break}}return this.pushStack(o.length>1?C.uniqueSort(o):o)},// Determine the position of an element within the set
index:function(e){return(// No argument, return index in parent
e?"string"==typeof e?u.call(C(e),this[0]):u.call(this,e.jquery?e[0]:e):this[0]&&this[0].parentNode?this.first().prevAll().length:-1)},add:function(e,t){return this.pushStack(C.uniqueSort(C.merge(this.get(),C(e,t))))},addBack:function(e){return this.add(null==e?this.prevObject:this.prevObject.filter(e))}}),C.each({parent:function(e){var t=e.parentNode;return t&&11!==t.nodeType?t:null},parents:function(e){return q(e,"parentNode")},parentsUntil:function(e,t,n){return q(e,"parentNode",n)},next:function(e){return W(e,"nextSibling")},prev:function(e){return W(e,"previousSibling")},nextAll:function(e){return q(e,"nextSibling")},prevAll:function(e){return q(e,"previousSibling")},nextUntil:function(e,t,n){return q(e,"nextSibling",n)},prevUntil:function(e,t,n){return q(e,"previousSibling",n)},siblings:function(e){return B((e.parentNode||{}).firstChild,e)},children:function(e){return B(e.firstChild)},contents:function(e){return null!=e.contentDocument&&// Support: IE 11+
// <object> elements with no `data` attribute has an object
// `contentDocument` with a `null` prototype.
i(e.contentDocument)?e.contentDocument:(A(e,"template")&&(e=e.content||e),C.merge([],e.childNodes))}},function(e,t){C.fn[e]=function(n,r){var i=C.map(this,t,n);return"Until"!==e.slice(-5)&&(r=n),r&&"string"==typeof r&&(i=C.filter(r,i)),this.length>1&&(F[e]||C.uniqueSort(i),_.test(e)&&i.reverse()),this.pushStack(i)}});var $=/[^\x20\t\r\n\f]+/g;function z(e){return e}function U(e){throw e}function X(e,t,n,r){var i;try{// Check for promise aspect first to privilege synchronous behavior
e&&g(i=e.promise)?i.call(e).done(t).fail(n):e&&g(i=e.then)?i.call(e,t,n):// * false: [ value ].slice( 0 ) => resolve( value )
// * true: [ value ].slice( 1 ) => resolve()
t.apply(void 0,[e].slice(r));// For Promises/A+, convert exceptions into rejections
// Since jQuery.when doesn't unwrap thenables, we can skip the extra checks appearing in
// Deferred#then to conditionally suppress rejection.
}catch(e){// Support: Android 4.0 only
// Strict mode functions invoked without .call/.apply get global-object context
n.apply(void 0,[e])}}/*
 * Create a callback list using the following parameters:
 *
 *	options: an optional list of space-separated options that will change how
 *			the callback list behaves or a more traditional option object
 *
 * By default a callback list will act like an event callback list and can be
 * "fired" multiple times.
 *
 * Possible options:
 *
 *	once:			will ensure the callback list can only be fired once (like a Deferred)
 *
 *	memory:			will keep track of previous values and will call any callback added
 *					after the list has been fired right away with the latest "memorized"
 *					values (like a Deferred)
 *
 *	unique:			will ensure a callback can only be added once (no duplicate in the list)
 *
 *	stopOnFalse:	interrupt callings when a callback returns false
 *
 */C.Callbacks=function(e){// Convert options from String-formatted to Object-formatted if needed
// (we check in cache first)
e="string"==typeof e?(t=e,n={},C.each(t.match($)||[],function(e,t){n[t]=!0}),n):C.extend({},e);var t,n,r,i,o,a,s=[],u=[],l=-1,c=function(){for(// Enforce single-firing
a=a||e.once,// Execute callbacks for all pending executions,
// respecting firingIndex overrides and runtime changes
o=r=!0;u.length;l=-1)for(i=u.shift();++l<s.length;)!1===s[l].apply(i[0],i[1])&&e.stopOnFalse&&(// Jump to end and forget the data so .add doesn't re-fire
l=s.length,i=!1);e.memory||(i=!1),r=!1,a&&(s=i?[]:"")},f={// Add a callback or a collection of callbacks to the list
add:function(){return s&&(i&&!r&&(l=s.length-1,u.push(i)),function t(n){C.each(n,function(n,r){g(r)?e.unique&&f.has(r)||s.push(r):r&&r.length&&"string"!==b(r)&&t(r)})}(arguments),i&&!r&&c()),this},// Remove a callback from the list
remove:function(){return C.each(arguments,function(e,t){for(var n;(n=C.inArray(t,s,n))>-1;)s.splice(n,1),n<=l&&l--}),this},// Check if a given callback is in the list.
// If no argument is given, return whether or not list has callbacks attached.
has:function(e){return e?C.inArray(e,s)>-1:s.length>0},// Remove all callbacks from the list
empty:function(){return s&&(s=[]),this},// Disable .fire and .add
// Abort any current/pending executions
// Clear all callbacks and values
disable:function(){return a=u=[],s=i="",this},disabled:function(){return!s},// Disable .fire
// Also disable .add unless we have memory (since it would have no effect)
// Abort any pending executions
lock:function(){return a=u=[],i||r||(s=i=""),this},locked:function(){return!!a},// Call all callbacks with the given context and arguments
fireWith:function(e,t){return a||(t=[e,(t=t||[]).slice?t.slice():t],u.push(t),r||c()),this},// Call all the callbacks with the given arguments
fire:function(){return f.fireWith(this,arguments),this},// To know if the callbacks have already been called at least once
fired:function(){return!!o}};return f},C.extend({Deferred:function(t){var n=[// action, add listener, callbacks,
// ... .then handlers, argument index, [final state]
["notify","progress",C.Callbacks("memory"),C.Callbacks("memory"),2],["resolve","done",C.Callbacks("once memory"),C.Callbacks("once memory"),0,"resolved"],["reject","fail",C.Callbacks("once memory"),C.Callbacks("once memory"),1,"rejected"]],r="pending",i={state:function(){return r},always:function(){return o.done(arguments).fail(arguments),this},catch:function(e){return i.then(null,e)},// Keep pipe for back-compat
pipe:function(){var e=arguments;return C.Deferred(function(t){C.each(n,function(n,r){// Map tuples (progress, done, fail) to arguments (done, fail, progress)
var i=g(e[r[4]])&&e[r[4]];// deferred.progress(function() { bind to newDefer or newDefer.notify })
// deferred.done(function() { bind to newDefer or newDefer.resolve })
// deferred.fail(function() { bind to newDefer or newDefer.reject })
o[r[1]](function(){var e=i&&i.apply(this,arguments);e&&g(e.promise)?e.promise().progress(t.notify).done(t.resolve).fail(t.reject):t[r[0]+"With"](this,i?[e]:arguments)})}),e=null}).promise()},then:function(t,r,i){var o=0;function a(t,n,r,i){return function(){var s=this,u=arguments,l=function(){var e,l;// Support: Promises/A+ section 2.3.3.3.3
// https://promisesaplus.com/#point-59
// Ignore double-resolution attempts
if(!(t<o)){// Support: Promises/A+ section 2.3.1
// https://promisesaplus.com/#point-48
if((e=r.apply(s,u))===n.promise())throw TypeError("Thenable self-resolution");// Handle a returned thenable
g(// Support: Promises/A+ sections 2.3.3.1, 3.5
// https://promisesaplus.com/#point-54
// https://promisesaplus.com/#point-75
// Retrieve `then` only once
l=e&&// Support: Promises/A+ section 2.3.4
// https://promisesaplus.com/#point-64
// Only check objects and functions for thenability
("object"==typeof e||"function"==typeof e)&&e.then)?i?l.call(e,a(o,n,z,i),a(o,n,U,i)):(// ...and disregard older resolution values
o++,l.call(e,a(o,n,z,i),a(o,n,U,i),a(o,n,z,n.notifyWith))):(r!==z&&(s=void 0,u=[e]),// Process the value(s)
// Default process is resolve
(i||n.resolveWith)(s,u))}},c=i?l:function(){try{l()}catch(e){C.Deferred.exceptionHook&&C.Deferred.exceptionHook(e,c.error),t+1>=o&&(r!==U&&(s=void 0,u=[e]),n.rejectWith(s,u))}};// Support: Promises/A+ section 2.3.3.3.1
// https://promisesaplus.com/#point-57
// Re-resolve promises immediately to dodge false rejection from
// subsequent errors
t?c():(C.Deferred.getErrorHook?c.error=C.Deferred.getErrorHook():C.Deferred.getStackHook&&(c.error=C.Deferred.getStackHook()),e.setTimeout(c))}}return C.Deferred(function(e){// progress_handlers.add( ... )
n[0][3].add(a(0,e,g(i)?i:z,e.notifyWith)),// fulfilled_handlers.add( ... )
n[1][3].add(a(0,e,g(t)?t:z)),// rejected_handlers.add( ... )
n[2][3].add(a(0,e,g(r)?r:U))}).promise()},// Get a promise for this deferred
// If obj is provided, the promise aspect is added to the object
promise:function(e){return null!=e?C.extend(e,i):i}},o={};// All done!
return(// Add list-specific methods
C.each(n,function(e,t){var a=t[2],s=t[5];// promise.progress = list.add
// promise.done = list.add
// promise.fail = list.add
i[t[1]]=a.add,s&&a.add(function(){// state = "resolved" (i.e., fulfilled)
// state = "rejected"
r=s},// fulfilled_callbacks.disable
n[3-e][2].disable,// fulfilled_handlers.disable
n[3-e][3].disable,n[0][2].lock,n[0][3].lock),// progress_handlers.fire
// fulfilled_handlers.fire
// rejected_handlers.fire
a.add(t[3].fire),// deferred.notify = function() { deferred.notifyWith(...) }
// deferred.resolve = function() { deferred.resolveWith(...) }
// deferred.reject = function() { deferred.rejectWith(...) }
o[t[0]]=function(){return o[t[0]+"With"](this===o?void 0:this,arguments),this},// deferred.notifyWith = list.fireWith
// deferred.resolveWith = list.fireWith
// deferred.rejectWith = list.fireWith
o[t[0]+"With"]=a.fireWith}),// Make the deferred a promise
i.promise(o),t&&t.call(o,o),o)},// Deferred helper
when:function(e){var t=arguments.length,n=t,r=Array(n),i=o.call(arguments),a=C.Deferred(),s=function(e){return function(n){r[e]=this,i[e]=arguments.length>1?o.call(arguments):n,--t||a.resolveWith(r,i)}};// Single- and empty arguments are adopted like Promise.resolve
if(t<=1&&(X(e,a.done(s(n)).resolve,a.reject,!t),"pending"===a.state()||g(i[n]&&i[n].then)))return a.then();// Multiple arguments are aggregated like Promise.all array elements
for(;n--;)X(i[n],s(n),a.reject);return a.promise()}});// These usually indicate a programmer mistake during development,
// warn about them ASAP rather than swallowing them by default.
var Q=/^(Eval|Internal|Range|Reference|Syntax|Type|URI)Error$/;// If `jQuery.Deferred.getErrorHook` is defined, `asyncError` is an error
// captured before the async barrier to get the original error cause
// which may otherwise be hidden.
C.Deferred.exceptionHook=function(t,n){// Support: IE 8 - 9 only
// Console exists when dev tools are open, which can happen at any time
e.console&&e.console.warn&&t&&Q.test(t.name)&&e.console.warn("jQuery.Deferred exception: "+t.message,t.stack,n)},C.readyException=function(t){e.setTimeout(function(){throw t})};// The deferred used on DOM ready
var Y=C.Deferred();// The ready event handler and self cleanup method
function V(){y.removeEventListener("DOMContentLoaded",V),e.removeEventListener("load",V),C.ready()}C.fn.ready=function(e){return Y.then(e)// Wrap jQuery.readyException in a function so that the lookup
// happens at the time of error handling instead of callback
// registration.
.catch(function(e){C.readyException(e)}),this},C.extend({// Is the DOM ready to be used? Set to true once it occurs.
isReady:!1,// A counter to track how many items to wait for before
// the ready event fires. See trac-6781
readyWait:1,// Handle when the DOM is ready
ready:function(e){// Abort if there are pending holds or we're already ready
!(!0===e?--C.readyWait:C.isReady)&&(// Remember that the DOM is ready
C.isReady=!0,!0!==e&&--C.readyWait>0||// If there are functions bound, to execute
Y.resolveWith(y,[C]))}}),C.ready.then=Y.then,"complete"!==y.readyState&&("loading"===y.readyState||y.documentElement.doScroll)?(// Use the handy event callback
y.addEventListener("DOMContentLoaded",V),// A fallback to window.onload, that will always work
e.addEventListener("load",V)):e.setTimeout(C.ready);// Multifunctional method to get and set values of a collection
// The value/s can optionally be executed if it's a function
var J=function(e,t,n,r,i,o,a){var s=0,u=e.length,l=null==n;// Sets many values
if("object"===b(n))for(s in i=!0,n)J(e,t,s,n[s],!0,o,a);else if(void 0!==r&&(i=!0,g(r)||(a=!0),l&&(a?(t.call(e,r),t=null):(l=t,t=function(e,t,n){return l.call(C(e),n)})),t))for(;s<u;s++)t(e[s],n,a?r:r.call(e[s],s,t(e[s],n)));return i?e:l?t.call(e):u?t(e[0],n):o},G=/^-ms-/,K=/-([a-z])/g;// Used by camelCase as callback to replace()
function Z(e,t){return t.toUpperCase()}// Convert dashed to camelCase; used by the css and data modules
// Support: IE <=9 - 11, Edge 12 - 15
// Microsoft forgot to hump their vendor prefix (trac-9572)
function ee(e){return e.replace(G,"ms-").replace(K,Z)}var et=function(e){// Accepts only:
//  - Node
//    - Node.ELEMENT_NODE
//    - Node.DOCUMENT_NODE
//  - Object
//    - Any
return 1===e.nodeType||9===e.nodeType||!+e.nodeType};function en(){this.expando=C.expando+en.uid++}en.uid=1,en.prototype={cache:function(e){// Check if the owner object already has a cache
var t=e[this.expando];return!t&&(t={},et(e)&&(e.nodeType?e[this.expando]=t:Object.defineProperty(e,this.expando,{value:t,configurable:!0}))),t},set:function(e,t,n){var r,i=this.cache(e);// Handle: [ owner, key, value ] args
// Always use camelCase key (gh-2257)
if("string"==typeof t)i[ee(t)]=n;else for(r in t)i[ee(r)]=t[r];return i},get:function(e,t){return void 0===t?this.cache(e):e[this.expando]&&e[this.expando][ee(t)]},access:function(e,t,n){return(// In cases where either:
//
//   1. No key was specified
//   2. A string key was specified, but no value provided
//
// Take the "read" path and allow the get method to determine
// which value to return, respectively either:
//
//   1. The entire cache object
//   2. The data stored at the key
//
void 0===t||t&&"string"==typeof t&&void 0===n?this.get(e,t):(// When the key is not a string, or both a key and value
// are specified, set or extend (existing objects) with either:
//
//   1. An object of properties
//   2. A key and value
//
this.set(e,t,n),void 0!==n?n:t))},remove:function(e,t){var n,r=e[this.expando];if(void 0!==r){if(void 0!==t)for(n=(// We always set camelCase keys, so remove that.
t=Array.isArray(t)?t.map(ee):((t=ee(t))in r)?[t]:t.match($)||[]).length;n--;)delete r[t[n]];// Remove the expando if there's no more data
(void 0===t||C.isEmptyObject(r))&&(e.nodeType?e[this.expando]=void 0:delete e[this.expando])}},hasData:function(e){var t=e[this.expando];return void 0!==t&&!C.isEmptyObject(t)}};var er=new en,ei=new en,eo=/^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,ea=/[A-Z]/g;function es(e,t,n){var r,i;// If nothing was found internally, try to fetch any
// data from the HTML5 data-* attribute
if(void 0===n&&1===e.nodeType){if(r="data-"+t.replace(ea,"-$&").toLowerCase(),"string"==typeof(n=e.getAttribute(r))){try{i=n,n="true"===i||"false"!==i&&("null"===i?null:i===+i+""?+i:eo.test(i)?JSON.parse(i):i)}catch(e){}// Make sure we set the data so it isn't changed later
ei.set(e,t,n)}else n=void 0}return n}C.extend({hasData:function(e){return ei.hasData(e)||er.hasData(e)},data:function(e,t,n){return ei.access(e,t,n)},removeData:function(e,t){ei.remove(e,t)},// TODO: Now that all calls to _data and _removeData have been replaced
// with direct calls to dataPriv methods, these can be deprecated.
_data:function(e,t,n){return er.access(e,t,n)},_removeData:function(e,t){er.remove(e,t)}}),C.fn.extend({data:function(e,t){var n,r,i,o=this[0],a=o&&o.attributes;// Gets all values
if(void 0===e){if(this.length&&(i=ei.get(o),1===o.nodeType&&!er.get(o,"hasDataAttrs"))){for(n=a.length;n--;)// The attrs elements can be null (trac-14894)
a[n]&&0===(r=a[n].name).indexOf("data-")&&es(o,r=ee(r.slice(5)),i[r]);er.set(o,"hasDataAttrs",!0)}return i}return(// Sets multiple values
"object"==typeof e?this.each(function(){ei.set(this,e)}):J(this,function(t){var n;// The calling jQuery object (element matches) is not empty
// (and therefore has an element appears at this[ 0 ]) and the
// `value` parameter was not undefined. An empty jQuery object
// will result in `undefined` for elem = this[ 0 ] which will
// throw an exception if an attempt to read a data cache is made.
if(o&&void 0===t)return void 0!==// Attempt to get data from the cache
// The key will always be camelCased in Data
(n=ei.get(o,e))||void 0!==// Attempt to "discover" the data in
// HTML5 custom data-* attrs
(n=es(o,e))?n:// We tried really hard, but the data doesn't exist.
void 0;// Set the data...
this.each(function(){// We always store the camelCased key
ei.set(this,e,t)})},null,t,arguments.length>1,null,!0))},removeData:function(e){return this.each(function(){ei.remove(this,e)})}}),C.extend({queue:function(e,t,n){var r;if(e)return t=(t||"fx")+"queue",r=er.get(e,t),n&&(!r||Array.isArray(n)?r=er.access(e,t,C.makeArray(n)):r.push(n)),r||[]},dequeue:function(e,t){t=t||"fx";var n=C.queue(e,t),r=n.length,i=n.shift(),o=C._queueHooks(e,t);"inprogress"===i&&(i=n.shift(),r--),i&&("fx"===t&&n.unshift("inprogress"),// Clear up the last queue stop function
delete o.stop,i.call(e,function(){C.dequeue(e,t)},o)),!r&&o&&o.empty.fire()},// Not public - generate a queueHooks object, or return the current one
_queueHooks:function(e,t){var n=t+"queueHooks";return er.get(e,n)||er.access(e,n,{empty:C.Callbacks("once memory").add(function(){er.remove(e,[t+"queue",n])})})}}),C.fn.extend({queue:function(e,t){var n=2;return("string"!=typeof e&&(t=e,e="fx",n--),arguments.length<n)?C.queue(this[0],e):void 0===t?this:this.each(function(){var n=C.queue(this,e,t);// Ensure a hooks for this queue
C._queueHooks(this,e),"fx"===e&&"inprogress"!==n[0]&&C.dequeue(this,e)})},dequeue:function(e){return this.each(function(){C.dequeue(this,e)})},clearQueue:function(e){return this.queue(e||"fx",[])},// Get a promise resolved when queues of a certain type
// are emptied (fx is the type by default)
promise:function(e,t){var n,r=1,i=C.Deferred(),o=this,a=this.length,s=function(){--r||i.resolveWith(o,[o])};for("string"!=typeof e&&(t=e,e=void 0),e=e||"fx";a--;)(n=er.get(o[a],e+"queueHooks"))&&n.empty&&(r++,n.empty.add(s));return s(),i.promise(t)}});var eu=/[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,el=RegExp("^(?:([+-])=|)("+eu+")([a-z%]*)$","i"),ec=["Top","Right","Bottom","Left"],ef=y.documentElement,ed=function(e){return C.contains(e.ownerDocument,e)},ep={composed:!0};ef.getRootNode&&(ed=function(e){return C.contains(e.ownerDocument,e)||e.getRootNode(ep)===e.ownerDocument});var eh=function(e,t){// Inline style trumps all
return"none"===// isHiddenWithinTree might be called from jQuery#filter function;
// in that case, element will be second argument
(e=t||e).style.display||""===e.style.display&&// Otherwise, check computed style
// Support: Firefox <=43 - 45
// Disconnected elements can have computed display: none, so first confirm that elem is
// in the document.
ed(e)&&"none"===C.css(e,"display")};function eg(e,t,n,r){var i,o,a=20,s=r?function(){return r.cur()}:function(){return C.css(e,t,"")},u=s(),l=n&&n[3]||(C.cssNumber[t]?"":"px"),c=e.nodeType&&(C.cssNumber[t]||"px"!==l&&+u)&&el.exec(C.css(e,t));if(c&&c[3]!==l){for(// Support: Firefox <=54
// Halve the iteration target value to prevent interference from CSS upper bounds (gh-2144)
u/=2,// Trust units reported by jQuery.css
l=l||c[3],// Iteratively approximate from a nonzero starting point
c=+u||1;a--;)// Evaluate and update our best guess (doubling guesses that zero out).
// Finish if the scale equals or crosses 1 (making the old*new product non-positive).
C.style(e,t,c+l),(1-o)*(1-(o=s()/u||.5))<=0&&(a=0),c/=o;c*=2,C.style(e,t,c+l),// Make sure we update the tween properties later on
n=n||[]}return n&&(c=+c||+u||0,// Apply relative offset (+=/-=) if specified
i=n[1]?c+(n[1]+1)*n[2]:+n[2],r&&(r.unit=l,r.start=c,r.end=i)),i}var ev={};function ey(e,t){// Determine new display value for elements that need to change
for(var n,r,i=[],o=0,a=e.length;o<a;o++)(r=e[o]).style&&(n=r.style.display,t?("none"!==n||(i[o]=er.get(r,"display")||null,i[o]||(r.style.display="")),""===r.style.display&&eh(r)&&(i[o]=function(e){var t,n=e.ownerDocument,r=e.nodeName,i=ev[r];return i||(t=n.body.appendChild(n.createElement(r)),i=C.css(t,"display"),t.parentNode.removeChild(t),"none"===i&&(i="block"),ev[r]=i),i}(r))):"none"!==n&&(i[o]="none",// Remember what we're overwriting
er.set(r,"display",n)));// Set the display of the elements in a second loop to avoid constant reflow
for(o=0;o<a;o++)null!=i[o]&&(e[o].style.display=i[o]);return e}C.fn.extend({show:function(){return ey(this,!0)},hide:function(){return ey(this)},toggle:function(e){return"boolean"==typeof e?e?this.show():this.hide():this.each(function(){eh(this)?C(this).show():C(this).hide()})}});var em=/^(?:checkbox|radio)$/i,ex=/<([a-z][^\/\0>\x20\t\r\n\f]*)/i,eb=/^$|^module$|\/(?:java|ecma)script/i;e5=y.createDocumentFragment().appendChild(y.createElement("div")),// Support: Android 4.0 - 4.3 only
// Check state lost if the name is set (trac-11217)
// Support: Windows Web Apps (WWA)
// `name` and `type` must use .setAttribute for WWA (trac-14901)
(e6=y.createElement("input")).setAttribute("type","radio"),e6.setAttribute("checked","checked"),e6.setAttribute("name","t"),e5.appendChild(e6),// Support: Android <=4.1 only
// Older WebKit doesn't clone checked state correctly in fragments
h.checkClone=e5.cloneNode(!0).cloneNode(!0).lastChild.checked,// Support: IE <=11 only
// Make sure textarea (and checkbox) defaultValue is properly cloned
e5.innerHTML="<textarea>x</textarea>",h.noCloneChecked=!!e5.cloneNode(!0).lastChild.defaultValue,// Support: IE <=9 only
// IE <=9 replaces <option> tags with their contents when inserted outside of
// the select element.
e5.innerHTML="<option></option>",h.option=!!e5.lastChild;// We have to close these tags to support XHTML (trac-13200)
var ew={// XHTML parsers do not magically insert elements in the
// same way that tag soup parsers do. So we cannot shorten
// this by omitting <tbody> or other required elements.
thead:[1,"<table>","</table>"],col:[2,"<table><colgroup>","</colgroup></table>"],tr:[2,"<table><tbody>","</tbody></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],_default:[0,"",""]};function eT(e,t){// Support: IE <=9 - 11 only
// Use typeof to avoid zero-argument method invocation on host objects (trac-15151)
var n;return(n=void 0!==e.getElementsByTagName?e.getElementsByTagName(t||"*"):void 0!==e.querySelectorAll?e.querySelectorAll(t||"*"):[],void 0===t||t&&A(e,t))?C.merge([e],n):n}// Mark scripts as having already been evaluated
function eC(e,t){for(var n=0,r=e.length;n<r;n++)er.set(e[n],"globalEval",!t||er.get(t[n],"globalEval"))}ew.tbody=ew.tfoot=ew.colgroup=ew.caption=ew.thead,ew.th=ew.td,h.option||(ew.optgroup=ew.option=[1,"<select multiple='multiple'>","</select>"]);var ek=/<|&#?\w+;/;function eA(e,t,n,r,i){for(var o,a,s,u,l,c=t.createDocumentFragment(),f=[],d=0,p=e.length;d<p;d++)if((o=e[d])||0===o){// Add nodes directly
if("object"===b(o))// push.apply(_, arraylike) throws on ancient WebKit
C.merge(f,o.nodeType?[o]:o);else if(ek.test(o)){for(a=a||c.appendChild(t.createElement("div")),s=ew[(ex.exec(o)||["",""])[1].toLowerCase()]||ew._default,a.innerHTML=s[1]+C.htmlPrefilter(o)+s[2],// Descend through wrappers to the right content
l=s[0];l--;)a=a.lastChild;// Support: Android <=4.0 only, PhantomJS 1 only
// push.apply(_, arraylike) throws on ancient WebKit
C.merge(f,a.childNodes),// Ensure the created nodes are orphaned (trac-12392)
// Remember the top-level container
(a=c.firstChild).textContent=""}else f.push(t.createTextNode(o))}for(// Remove wrapper from fragment
c.textContent="",d=0;o=f[d++];){// Skip elements already in the context collection (trac-4087)
if(r&&C.inArray(o,r)>-1){i&&i.push(o);continue}// Capture executables
if(u=ed(o),// Append to fragment
a=eT(c.appendChild(o),"script"),u&&eC(a),n)for(l=0;o=a[l++];)eb.test(o.type||"")&&n.push(o)}return c}var eE=/^([^.]*)(?:\.(.+)|)/;function eS(){return!0}function eD(){return!1}function eN(e,t,n,r,i,o){var a,s;// Types can be a map of types/handlers
if("object"==typeof t){for(s in"string"!=typeof n&&(// ( types-Object, data )
r=r||n,n=void 0),t)eN(e,s,n,r,t[s],o);return e}if(null==r&&null==i?(// ( types, fn )
i=n,r=n=void 0):null==i&&("string"==typeof n?(// ( types, selector, fn )
i=r,r=void 0):(// ( types, data, fn )
i=r,r=n,n=void 0)),!1===i)i=eD;else if(!i)return e;return 1===o&&(a=i,// Use same guid so caller can remove using origFn
(i=function(e){return(// Can use an empty set, since event contains the info
C().off(e),a.apply(this,arguments))}).guid=a.guid||(a.guid=C.guid++)),e.each(function(){C.event.add(this,t,i,r,n)})}// Ensure the presence of an event listener that handles manually-triggered
// synthetic events by interrupting progress until reinvoked in response to
// *native* events that it fires directly, ensuring that state changes have
// already occurred before other listeners are invoked.
function eM(e,t,n){// Missing `isSetup` indicates a trigger call, which must force setup through jQuery.event.add
if(!n){void 0===er.get(e,t)&&C.event.add(e,t,eS);return}// Register the controller as a special universal handler for all event namespaces
er.set(e,t,!1),C.event.add(e,t,{namespace:!1,handler:function(e){var n,r=er.get(this,t);if(1&e.isTrigger&&this[t]){// Interrupt processing of the outer synthetic .trigger()ed event
if(r)(C.event.special[t]||{}).delegateType&&e.stopPropagation();else if(// Store arguments for use when handling the inner native event
// There will always be at least one argument (an event object), so this array
// will not be confused with a leftover capture object.
r=o.call(arguments),er.set(this,t,r),// Trigger the native event and capture its result
this[t](),n=er.get(this,t),er.set(this,t,!1),r!==n)return(// Cancel the outer synthetic event
e.stopImmediatePropagation(),e.preventDefault(),n)}else r&&(// ...and capture the result
er.set(this,t,C.event.trigger(r[0],r.slice(1),this)),// Abort handling of the native event by all jQuery handlers while allowing
// native handlers on the same element to run. On target, this is achieved
// by stopping immediate propagation just on the jQuery event. However,
// the native event is re-wrapped by a jQuery one on each level of the
// propagation so the only way to stop it for jQuery is to stop it for
// everyone via native `stopPropagation()`. This is not a problem for
// focus/blur which don't bubble, but it does also stop click on checkboxes
// and radios. We accept this limitation.
e.stopPropagation(),e.isImmediatePropagationStopped=eS)}})}/*
 * Helper functions for managing events -- not part of the public interface.
 * Props to Dean Edwards' addEvent library for many of the ideas.
 */C.event={global:{},add:function(e,t,n,r,i){var o,a,s,u,l,c,f,d,p,h,g,v=er.get(e);// Only attach events to objects that accept data
if(et(e))for(n.handler&&(n=(o=n).handler,i=o.selector),i&&C.find.matchesSelector(ef,i),n.guid||(n.guid=C.guid++),(u=v.events)||(u=v.events=Object.create(null)),(a=v.handle)||(a=v.handle=function(t){// Discard the second event of a jQuery.event.trigger() and
// when an event is called after a page has unloaded
return C.event.triggered!==t.type?C.event.dispatch.apply(e,arguments):void 0}),l=// Handle multiple events separated by a space
(t=(t||"").match($)||[""]).length;l--;)// There *must* be a type, no attaching namespace-only handlers
p=g=(s=eE.exec(t[l])||[])[1],h=(s[2]||"").split(".").sort(),p&&(// If event changes its type, use the special event handlers for the changed type
f=C.event.special[p]||{},// If selector defined, determine special event api type, otherwise given type
p=(i?f.delegateType:f.bindType)||p,// Update special based on newly reset type
f=C.event.special[p]||{},// handleObj is passed to all event handlers
c=C.extend({type:p,origType:g,data:r,handler:n,guid:n.guid,selector:i,needsContext:i&&C.expr.match.needsContext.test(i),namespace:h.join(".")},o),(d=u[p])||((d=u[p]=[]).delegateCount=0,(!f.setup||!1===f.setup.call(e,r,h,a))&&e.addEventListener&&e.addEventListener(p,a)),f.add&&(f.add.call(e,c),c.handler.guid||(c.handler.guid=n.guid)),i?d.splice(d.delegateCount++,0,c):d.push(c),// Keep track of which events have ever been used, for event optimization
C.event.global[p]=!0)},// Detach an event or set of events from an element
remove:function(e,t,n,r,i){var o,a,s,u,l,c,f,d,p,h,g,v=er.hasData(e)&&er.get(e);if(v&&(u=v.events)){for(l=// Once for each type.namespace in types; type may be omitted
(t=(t||"").match($)||[""]).length;l--;){// Unbind all events (on this namespace, if provided) for the element
if(p=g=(s=eE.exec(t[l])||[])[1],h=(s[2]||"").split(".").sort(),!p){for(p in u)C.event.remove(e,p+t[l],n,r,!0);continue}for(f=C.event.special[p]||{},d=u[p=(r?f.delegateType:f.bindType)||p]||[],s=s[2]&&RegExp("(^|\\.)"+h.join("\\.(?:.*\\.|)")+"(\\.|$)"),// Remove matching events
a=o=d.length;o--;)c=d[o],(i||g===c.origType)&&(!n||n.guid===c.guid)&&(!s||s.test(c.namespace))&&(!r||r===c.selector||"**"===r&&c.selector)&&(d.splice(o,1),c.selector&&d.delegateCount--,f.remove&&f.remove.call(e,c));a&&!d.length&&(f.teardown&&!1!==f.teardown.call(e,h,v.handle)||C.removeEvent(e,p,v.handle),delete u[p])}C.isEmptyObject(u)&&er.remove(e,"handle events")}},dispatch:function(e){var t,n,r,i,o,a,s=Array(arguments.length),u=C.event.fix(e),l=(er.get(this,"events")||Object.create(null))[u.type]||[],c=C.event.special[u.type]||{};for(t=1,// Use the fix-ed jQuery.Event rather than the (read-only) native event
s[0]=u;t<arguments.length;t++)s[t]=arguments[t];// Call the preDispatch hook for the mapped type, and let it bail if desired
if(u.delegateTarget=this,!c.preDispatch||!1!==c.preDispatch.call(this,u)){for(// Determine handlers
a=C.event.handlers.call(this,u,l),// Run delegates first; they may want to stop propagation beneath us
t=0;(i=a[t++])&&!u.isPropagationStopped();)for(u.currentTarget=i.elem,n=0;(o=i.handlers[n++])&&!u.isImmediatePropagationStopped();)// specially universal or its namespaces are a superset of the event's.
(!u.rnamespace||!1===o.namespace||u.rnamespace.test(o.namespace))&&(u.handleObj=o,u.data=o.data,void 0!==(r=((C.event.special[o.origType]||{}).handle||o.handler).apply(i.elem,s))&&!1===(u.result=r)&&(u.preventDefault(),u.stopPropagation()));return c.postDispatch&&c.postDispatch.call(this,u),u.result}},handlers:function(e,t){var n,r,i,o,a,s=[],u=t.delegateCount,l=e.target;// Find delegate handlers
if(u&&// Support: IE <=9
// Black-hole SVG <use> instance trees (trac-13180)
l.nodeType&&// Support: Firefox <=42
// Suppress spec-violating clicks indicating a non-primary pointer button (trac-3861)
// https://www.w3.org/TR/DOM-Level-3-Events/#event-type-click
// Support: IE 11 only
// ...but not arrow key "clicks" of radio inputs, which can have `button` -1 (gh-2343)
!("click"===e.type&&e.button>=1)){for(;l!==this;l=l.parentNode||this)// Don't process clicks on disabled elements (trac-6911, trac-8165, trac-11382, trac-11764)
if(1===l.nodeType&&!("click"===e.type&&!0===l.disabled)){for(n=0,o=[],a={};n<u;n++)void 0===a[// Don't conflict with Object.prototype properties (trac-13203)
i=(r=t[n]).selector+" "]&&(a[i]=r.needsContext?C(i,this).index(l)>-1:C.find(i,this,null,[l]).length),a[i]&&o.push(r);o.length&&s.push({elem:l,handlers:o})}}return(// Add the remaining (directly-bound) handlers
l=this,u<t.length&&s.push({elem:l,handlers:t.slice(u)}),s)},addProp:function(e,t){Object.defineProperty(C.Event.prototype,e,{enumerable:!0,configurable:!0,get:g(t)?function(){if(this.originalEvent)return t(this.originalEvent)}:function(){if(this.originalEvent)return this.originalEvent[e]},set:function(t){Object.defineProperty(this,e,{enumerable:!0,configurable:!0,writable:!0,value:t})}})},fix:function(e){return e[C.expando]?e:new C.Event(e)},special:{load:{// Prevent triggered image.load events from bubbling to window.load
noBubble:!0},click:{// Utilize native event to ensure correct state for checkable inputs
setup:function(e){// For mutual compressibility with _default, replace `this` access with a local var.
// `|| data` is dead code meant only to preserve the variable through minification.
var t=this||e;// Return false to allow normal processing in the caller
return em.test(t.type)&&t.click&&A(t,"input")&&eM(t,"click",!0),!1},trigger:function(e){// For mutual compressibility with _default, replace `this` access with a local var.
// `|| data` is dead code meant only to preserve the variable through minification.
var t=this||e;// Return non-false to allow normal event-path propagation
return em.test(t.type)&&t.click&&A(t,"input")&&eM(t,"click"),!0},// For cross-browser consistency, suppress native .click() on links
// Also prevent it if we're currently inside a leveraged native-event stack
_default:function(e){var t=e.target;return em.test(t.type)&&t.click&&A(t,"input")&&er.get(t,"click")||A(t,"a")}},beforeunload:{postDispatch:function(e){// Support: Firefox 20+
// Firefox doesn't alert if the returnValue field is not set.
void 0!==e.result&&e.originalEvent&&(e.originalEvent.returnValue=e.result)}}}},C.removeEvent=function(e,t,n){// This "if" is needed for plain objects
e.removeEventListener&&e.removeEventListener(t,n)},C.Event=function(e,t){// Allow instantiation without the 'new' keyword
if(!(this instanceof C.Event))return new C.Event(e,t);e&&e.type?(this.originalEvent=e,this.type=e.type,// Events bubbling up the document may have been marked as prevented
// by a handler lower down the tree; reflect the correct value.
this.isDefaultPrevented=e.defaultPrevented||void 0===e.defaultPrevented&&// Support: Android <=2.3 only
!1===e.returnValue?eS:eD,// Create target properties
// Support: Safari <=6 - 7 only
// Target should not be a text node (trac-504, trac-13143)
this.target=e.target&&3===e.target.nodeType?e.target.parentNode:e.target,this.currentTarget=e.currentTarget,this.relatedTarget=e.relatedTarget):this.type=e,t&&C.extend(this,t),// Create a timestamp if incoming event doesn't have one
this.timeStamp=e&&e.timeStamp||Date.now(),// Mark it as fixed
this[C.expando]=!0},// jQuery.Event is based on DOM3 Events as specified by the ECMAScript Language Binding
// https://www.w3.org/TR/2003/WD-DOM-Level-3-Events-20030331/ecma-script-binding.html
C.Event.prototype={constructor:C.Event,isDefaultPrevented:eD,isPropagationStopped:eD,isImmediatePropagationStopped:eD,isSimulated:!1,preventDefault:function(){var e=this.originalEvent;this.isDefaultPrevented=eS,e&&!this.isSimulated&&e.preventDefault()},stopPropagation:function(){var e=this.originalEvent;this.isPropagationStopped=eS,e&&!this.isSimulated&&e.stopPropagation()},stopImmediatePropagation:function(){var e=this.originalEvent;this.isImmediatePropagationStopped=eS,e&&!this.isSimulated&&e.stopImmediatePropagation(),this.stopPropagation()}},// Includes all common event props including KeyEvent and MouseEvent specific props
C.each({altKey:!0,bubbles:!0,cancelable:!0,changedTouches:!0,ctrlKey:!0,detail:!0,eventPhase:!0,metaKey:!0,pageX:!0,pageY:!0,shiftKey:!0,view:!0,char:!0,code:!0,charCode:!0,key:!0,keyCode:!0,button:!0,buttons:!0,clientX:!0,clientY:!0,offsetX:!0,offsetY:!0,pointerId:!0,pointerType:!0,screenX:!0,screenY:!0,targetTouches:!0,toElement:!0,touches:!0,which:!0},C.event.addProp),C.each({focus:"focusin",blur:"focusout"},function(e,t){function n(e){if(y.documentMode){// Support: IE 11+
// Attach a single focusin/focusout handler on the document while someone wants
// focus/blur. This is because the former are synchronous in IE while the latter
// are async. In other browsers, all those handlers are invoked synchronously.
// `handle` from private data would already wrap the event, but we need
// to change the `type` here.
var n=er.get(this,"handle"),r=C.event.fix(e);r.type="focusin"===e.type?"focus":"blur",r.isSimulated=!0,// First, handle focusin/focusout
n(e),r.target===r.currentTarget&&// `jQuery.event.add`, so event handle will already have been set
// by this point.
n(r)}else // while someone wants focusin/focusout.
C.event.simulate(t,e.target,C.event.fix(e))}C.event.special[e]={// Utilize native event if possible so blur/focus sequence is correct
setup:function(){var r;if(// Claim the first handler
// dataPriv.set( this, "focus", ... )
// dataPriv.set( this, "blur", ... )
eM(this,e,!0),!y.documentMode)return!1;// Support: IE 9 - 11+
// We use the same native handler for focusin & focus (and focusout & blur)
// so we need to coordinate setup & teardown parts between those events.
// Use `delegateType` as the key as `type` is already used by `leverageNative`.
(r=er.get(this,t))||this.addEventListener(t,n),er.set(this,t,(r||0)+1)},trigger:function(){// Return non-false to allow normal event-path propagation
return(// Force setup before trigger
eM(this,e),!0)},teardown:function(){var e;if(!y.documentMode)return!1;(e=er.get(this,t)-1)?er.set(this,t,e):(this.removeEventListener(t,n),er.remove(this,t))},// Suppress native focus or blur if we're currently inside
// a leveraged native-event stack
_default:function(t){return er.get(t.target,e)},delegateType:t},// Support: Firefox <=44
// Firefox doesn't have focus(in | out) events
// Related ticket - https://bugzilla.mozilla.org/show_bug.cgi?id=687787
//
// Support: Chrome <=48 - 49, Safari <=9.0 - 9.1
// focus(in | out) events fire after focus & blur events,
// which is spec violation - http://www.w3.org/TR/DOM-Level-3-Events/#events-focusevent-event-order
// Related ticket - https://bugs.chromium.org/p/chromium/issues/detail?id=449857
//
// Support: IE 9 - 11+
// To preserve relative focusin/focus & focusout/blur event order guaranteed on the 3.x branch,
// attach a single handler for both events in IE.
C.event.special[t]={setup:function(){// Handle: regular nodes (via `this.ownerDocument`), window
// (via `this.document`) & document (via `this`).
var r=this.ownerDocument||this.document||this,i=y.documentMode?this:r,o=er.get(i,t);o||(y.documentMode?this.addEventListener(t,n):r.addEventListener(e,n,!0)),er.set(i,t,(o||0)+1)},teardown:function(){var r=this.ownerDocument||this.document||this,i=y.documentMode?this:r,o=er.get(i,t)-1;o?er.set(i,t,o):(y.documentMode?this.removeEventListener(t,n):r.removeEventListener(e,n,!0),er.remove(i,t))}}}),// Create mouseenter/leave events using mouseover/out and event-time checks
// so that event delegation works in jQuery.
// Do the same for pointerenter/pointerleave and pointerover/pointerout
//
// Support: Safari 7 only
// Safari sends mouseenter too often; see:
// https://bugs.chromium.org/p/chromium/issues/detail?id=470258
// for the description of the bug (it existed in older Chrome versions as well).
C.each({mouseenter:"mouseover",mouseleave:"mouseout",pointerenter:"pointerover",pointerleave:"pointerout"},function(e,t){C.event.special[e]={delegateType:t,bindType:t,handle:function(e){var n,r=e.relatedTarget,i=e.handleObj;return r&&(r===this||C.contains(this,r))||(e.type=i.origType,n=i.handler.apply(this,arguments),e.type=t),n}}}),C.fn.extend({on:function(e,t,n,r){return eN(this,e,t,n,r)},one:function(e,t,n,r){return eN(this,e,t,n,r,1)},off:function(e,t,n){var r,i;if(e&&e.preventDefault&&e.handleObj)return(// ( event )  dispatched jQuery.Event
r=e.handleObj,C(e.delegateTarget).off(r.namespace?r.origType+"."+r.namespace:r.origType,r.selector,r.handler),this);if("object"==typeof e){// ( types-object [, selector] )
for(i in e)this.off(i,t,e[i]);return this}return(!1===t||"function"==typeof t)&&(// ( types [, fn] )
n=t,t=void 0),!1===n&&(n=eD),this.each(function(){C.event.remove(this,e,n,t)})}});var // In IE/Edge using regex groups here causes severe slowdowns.
// See https://connect.microsoft.com/IE/feedback/details/1736512/
ej=/<script|<style|<link/i,eL=/checked\s*(?:[^=]|=\s*.checked.)/i,eq=/^\s*<!\[CDATA\[|\]\]>\s*$/g;// Prefer a tbody over its parent table for containing new rows
function eB(e,t){return A(e,"table")&&A(11!==t.nodeType?t:t.firstChild,"tr")&&C(e).children("tbody")[0]||e}// Replace/restore the type attribute of script elements for safe DOM manipulation
function eO(e){return e.type=(null!==e.getAttribute("type"))+"/"+e.type,e}function eP(e){return"true/"===(e.type||"").slice(0,5)?e.type=e.type.slice(5):e.removeAttribute("type"),e}function eI(e,t){var n,r,i,o,a,s;if(1===t.nodeType){// 1. Copy private data: events, handlers, etc.
if(er.hasData(e)&&(s=er.get(e).events))for(i in er.remove(t,"handle events"),s)for(n=0,r=s[i].length;n<r;n++)C.event.add(t,i,s[i][n]);// 2. Copy user data
ei.hasData(e)&&(o=ei.access(e),a=C.extend({},o),ei.set(t,a))}}function eH(e,t,n,r){// Flatten any nested arrays
t=a(t);var i,o,s,u,l,c,f=0,d=e.length,p=d-1,v=t[0],y=g(v);// We can't cloneNode fragments that contain checked, in WebKit
if(y||d>1&&"string"==typeof v&&!h.checkClone&&eL.test(v))return e.each(function(i){var o=e.eq(i);y&&(t[0]=v.call(this,i,o.html())),eH(o,t,n,r)});if(d&&(o=(i=eA(t,e[0].ownerDocument,!1,e,r)).firstChild,1===i.childNodes.length&&(i=o),o||r)){// Use the original fragment for the last item
// instead of the first because it can end up
// being emptied incorrectly in certain situations (trac-8070).
for(u=(s=C.map(eT(i,"script"),eO)).length;f<d;f++)l=i,f!==p&&(l=C.clone(l,!0,!0),u&&// push.apply(_, arraylike) throws on ancient WebKit
C.merge(s,eT(l,"script"))),n.call(e[f],l,f);if(u)// Evaluate executable scripts on first document insertion
for(c=s[s.length-1].ownerDocument,// Re-enable scripts
C.map(s,eP),f=0;f<u;f++)l=s[f],eb.test(l.type||"")&&!er.access(l,"globalEval")&&C.contains(c,l)&&(l.src&&"module"!==(l.type||"").toLowerCase()?C._evalUrl&&!l.noModule&&C._evalUrl(l.src,{nonce:l.nonce||l.getAttribute("nonce")},c):// needed as in XML documents they're already not visible when
// inspecting element contents and in HTML documents they have no
// meaning but we're preserving that logic for backwards compatibility.
// This will be removed completely in 4.0. See gh-4904.
x(l.textContent.replace(eq,""),l,c))}return e}function eR(e,t,n){for(var r,i=t?C.filter(t,e):e,o=0;null!=(r=i[o]);o++)n||1!==r.nodeType||C.cleanData(eT(r)),r.parentNode&&(n&&ed(r)&&eC(eT(r,"script")),r.parentNode.removeChild(r));return e}C.extend({htmlPrefilter:function(e){return e},clone:function(e,t,n){var r,i,o,a,s=e.cloneNode(!0),u=ed(e);// Fix IE cloning issues
if(!h.noCloneChecked&&(1===e.nodeType||11===e.nodeType)&&!C.isXMLDoc(e))for(r=0,// We eschew jQuery#find here for performance reasons:
// https://jsperf.com/getall-vs-sizzle/2
a=eT(s),i=(o=eT(e)).length;r<i;r++)!// Fix IE bugs, see support tests
function(e,t){var n=t.nodeName.toLowerCase();// Fails to persist the checked state of a cloned checkbox or radio button.
"input"===n&&em.test(e.type)?t.checked=e.checked:("input"===n||"textarea"===n)&&(t.defaultValue=e.defaultValue)}(o[r],a[r]);// Copy the events from the original to the clone
if(t){if(n)for(r=0,o=o||eT(e),a=a||eT(s),i=o.length;r<i;r++)eI(o[r],a[r]);else eI(e,s)}// Return the cloned set
return(// Preserve script evaluation history
(a=eT(s,"script")).length>0&&eC(a,!u&&eT(e,"script")),s)},cleanData:function(e){for(var t,n,r,i=C.event.special,o=0;void 0!==(n=e[o]);o++)if(et(n)){if(t=n[er.expando]){if(t.events)for(r in t.events)i[r]?C.event.remove(n,r):C.removeEvent(n,r,t.handle);// Support: Chrome <=35 - 45+
// Assign undefined instead of using delete, see Data#remove
n[er.expando]=void 0}n[ei.expando]&&// Assign undefined instead of using delete, see Data#remove
(n[ei.expando]=void 0)}}}),C.fn.extend({detach:function(e){return eR(this,e,!0)},remove:function(e){return eR(this,e)},text:function(e){return J(this,function(e){return void 0===e?C.text(this):this.empty().each(function(){(1===this.nodeType||11===this.nodeType||9===this.nodeType)&&(this.textContent=e)})},null,e,arguments.length)},append:function(){return eH(this,arguments,function(e){(1===this.nodeType||11===this.nodeType||9===this.nodeType)&&eB(this,e).appendChild(e)})},prepend:function(){return eH(this,arguments,function(e){if(1===this.nodeType||11===this.nodeType||9===this.nodeType){var t=eB(this,e);t.insertBefore(e,t.firstChild)}})},before:function(){return eH(this,arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this)})},after:function(){return eH(this,arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this.nextSibling)})},empty:function(){for(var e,t=0;null!=(e=this[t]);t++)1===e.nodeType&&(// Prevent memory leaks
C.cleanData(eT(e,!1)),// Remove any remaining nodes
e.textContent="");return this},clone:function(e,t){return e=null!=e&&e,t=null==t?e:t,this.map(function(){return C.clone(this,e,t)})},html:function(e){return J(this,function(e){var t=this[0]||{},n=0,r=this.length;if(void 0===e&&1===t.nodeType)return t.innerHTML;// See if we can take a shortcut and just use innerHTML
if("string"==typeof e&&!ej.test(e)&&!ew[(ex.exec(e)||["",""])[1].toLowerCase()]){e=C.htmlPrefilter(e);try{for(;n<r;n++)t=this[n]||{},1===t.nodeType&&(C.cleanData(eT(t,!1)),t.innerHTML=e);t=0;// If using innerHTML throws an exception, use the fallback method
}catch(e){}}t&&this.empty().append(e)},null,e,arguments.length)},replaceWith:function(){var e=[];// Make the changes, replacing each non-ignored context element with the new content
return eH(this,arguments,function(t){var n=this.parentNode;0>C.inArray(this,e)&&(C.cleanData(eT(this)),n&&n.replaceChild(t,this));// Force callback invocation
},e)}}),C.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(e,t){C.fn[e]=function(e){for(var n,r=[],i=C(e),o=i.length-1,a=0;a<=o;a++)n=a===o?this:this.clone(!0),C(i[a])[t](n),// Support: Android <=4.0 only, PhantomJS 1 only
// .get() because push.apply(_, arraylike) throws on ancient WebKit
s.apply(r,n.get());return this.pushStack(r)}});var e_=RegExp("^("+eu+")(?!px)[a-z%]+$","i"),eF=/^--/,eW=function(t){// Support: IE <=11 only, Firefox <=30 (trac-15098, trac-14150)
// IE throws on elements created in popups
// FF meanwhile throws on frame elements through "defaultView.getComputedStyle"
var n=t.ownerDocument.defaultView;return n&&n.opener||(n=e),n.getComputedStyle(t)},e$=function(e,t,n){var r,i,o={};// Remember the old values, and insert the new ones
for(i in t)o[i]=e.style[i],e.style[i]=t[i];// Revert the old values
for(i in r=n.call(e),t)e.style[i]=o[i];return r},ez=RegExp(ec.join("|"),"i");function eU(e,t,n){var r,i,o,a,s=eF.test(t),// Retrieving style before computed somehow
// fixes an issue with getting wrong values
// on detached elements
u=e.style;return(n=n||eW(e))&&(// Support: IE <=9 - 11+
// IE only supports `"float"` in `getPropertyValue`; in computed styles
// it's only available as `"cssFloat"`. We no longer modify properties
// sent to `.css()` apart from camelCasing, so we need to check both.
// Normally, this would create difference in behavior: if
// `getPropertyValue` returns an empty string, the value returned
// by `.css()` would be `undefined`. This is usually the case for
// disconnected elements. However, in IE even disconnected elements
// with no styles return `"none"` for `getPropertyValue( "float" )`
a=n.getPropertyValue(t)||n[t],s&&a&&// Spec requires trimming whitespace for custom properties (gh-4926).
// Firefox only trims leading whitespace. Chrome just collapses
// both leading & trailing whitespace to a single space.
//
// Fall back to `undefined` if empty string returned.
// This collapses a missing definition with property defined
// and set to an empty string but there's no standard API
// allowing us to differentiate them without a performance penalty
// and returning `undefined` aligns with older jQuery.
//
// rtrimCSS treats U+000D CARRIAGE RETURN and U+000C FORM FEED
// as whitespace while CSS does not, but this is not a problem
// because CSS preprocessing replaces them with U+000A LINE FEED
// (which *is* CSS whitespace)
// https://www.w3.org/TR/css-syntax-3/#input-preprocessing
(a=a.replace(M,"$1")||void 0),""!==a||ed(e)||(a=C.style(e,t)),!h.pixelBoxStyles()&&e_.test(a)&&ez.test(t)&&(// Remember the original values
r=u.width,i=u.minWidth,o=u.maxWidth,// Put in the new values to get a computed value out
u.minWidth=u.maxWidth=u.width=a,a=n.width,// Revert the changed values
u.width=r,u.minWidth=i,u.maxWidth=o)),void 0!==a?// IE returns zIndex value as an integer.
a+"":a}function eX(e,t){// Define the hook, we'll check on the first run if it's really needed.
return{get:function(){if(e()){// Hook not needed (or it's not possible to use it due
// to missing dependency), remove it.
delete this.get;return}// Hook needed; redefine it so that the support test is not executed again.
return(this.get=t).apply(this,arguments)}}}!function(){// Executing both pixelPosition & boxSizingReliable tests require only one layout
// so they're executed at the same time to save the second computation.
function t(){// This is a singleton, we need to execute it only once
if(c){l.style.cssText="position:absolute;left:-11111px;width:60px;margin-top:1px;padding:0;border:0",c.style.cssText="position:relative;display:block;box-sizing:border-box;overflow:scroll;margin:auto;border:1px;padding:1px;width:60%;top:1%",ef.appendChild(l).appendChild(c);var t=e.getComputedStyle(c);r="1%"!==t.top,// Support: Android 4.0 - 4.3 only, Firefox <=3 - 44
u=12===n(t.marginLeft),// Support: Android 4.0 - 4.3 only, Safari <=9.1 - 10.1, iOS <=7.0 - 9.3
// Some styles come back with percentage values, even though they shouldn't
c.style.right="60%",a=36===n(t.right),// Support: IE 9 - 11 only
// Detect misreporting of content dimensions for box-sizing:border-box elements
i=36===n(t.width),// Support: IE 9 only
// Detect overflow:scroll screwiness (gh-3699)
// Support: Chrome <=64
// Don't get tricked when zoom affects offsetWidth (gh-4029)
c.style.position="absolute",o=12===n(c.offsetWidth/3),ef.removeChild(l),// Nullify the div so it wouldn't be stored in the memory and
// it will also be a sign that checks already performed
c=null}}function n(e){return Math.round(parseFloat(e))}var r,i,o,a,s,u,l=y.createElement("div"),c=y.createElement("div");// Finish early in limited (non-browser) environments
c.style&&(// Support: IE <=9 - 11 only
// Style of cloned element affects source element cloned (trac-8908)
c.style.backgroundClip="content-box",c.cloneNode(!0).style.backgroundClip="",h.clearCloneStyle="content-box"===c.style.backgroundClip,C.extend(h,{boxSizingReliable:function(){return t(),i},pixelBoxStyles:function(){return t(),a},pixelPosition:function(){return t(),r},reliableMarginLeft:function(){return t(),u},scrollboxSize:function(){return t(),o},// Support: IE 9 - 11+, Edge 15 - 18+
// IE/Edge misreport `getComputedStyle` of table rows with width/height
// set in CSS while `offset*` properties report correct values.
// Behavior in IE 9 is more subtle than in newer versions & it passes
// some versions of this test; make sure not to make it pass there!
//
// Support: Firefox 70+
// Only Firefox includes border widths
// in computed dimensions. (gh-4529)
reliableTrDimensions:function(){var t,n,r,i;return null==s&&(t=y.createElement("table"),n=y.createElement("tr"),r=y.createElement("div"),t.style.cssText="position:absolute;left:-11111px;border-collapse:separate",n.style.cssText="box-sizing:content-box;border:1px solid",// Support: Chrome 86+
// Height set through cssText does not get applied.
// Computed height then comes back as 0.
n.style.height="1px",r.style.height="9px",// Support: Android 8 Chrome 86+
// In our bodyBackground.html iframe,
// display for all div elements is set to "inline",
// which causes a problem only in Android 8 Chrome 86.
// Ensuring the div is `display: block`
// gets around this issue.
r.style.display="block",ef.appendChild(t).appendChild(n).appendChild(r),s=parseInt((i=e.getComputedStyle(n)).height,10)+parseInt(i.borderTopWidth,10)+parseInt(i.borderBottomWidth,10)===n.offsetHeight,ef.removeChild(t)),s}}))}();var eQ=["Webkit","Moz","ms"],eY=y.createElement("div").style,eV={};// Return a potentially-mapped jQuery.cssProps or vendor prefixed property
function eJ(e){return C.cssProps[e]||eV[e]||(e in eY?e:eV[e]=// Return a vendor-prefixed property or undefined
function(e){for(// Check for vendor prefixed names
var t=e[0].toUpperCase()+e.slice(1),n=eQ.length;n--;)if((e=eQ[n]+t)in eY)return e}(e)||e)}var // except "table", "table-cell", or "table-caption"
// See here for display values: https://developer.mozilla.org/en-US/docs/CSS/display
eG=/^(none|table(?!-c[ea]).+)/,eK={position:"absolute",visibility:"hidden",display:"block"},eZ={letterSpacing:"0",fontWeight:"400"};function e1(e,t,n){// Any relative (+/-) values have already been
// normalized at this point
var r=el.exec(t);return r?Math.max(0,r[2]-(n||0))+(r[3]||"px"):t}function e0(e,t,n,r,i,o){var a="width"===t?1:0,s=0,u=0,l=0;// Adjustment may not be necessary
if(n===(r?"border":"content"))return 0;for(;a<4;a+=2)"margin"===n&&(l+=C.css(e,n+ec[a],!0,i)),r?("content"===n&&(u-=C.css(e,"padding"+ec[a],!0,i)),"margin"!==n&&(u-=C.css(e,"border"+ec[a]+"Width",!0,i))):(// Add padding
u+=C.css(e,"padding"+ec[a],!0,i),"padding"!==n?u+=C.css(e,"border"+ec[a]+"Width",!0,i):s+=C.css(e,"border"+ec[a]+"Width",!0,i));return!r&&o>=0&&// Assuming integer scroll gutter, subtract the rest and round down
(u+=Math.max(0,Math.ceil(e["offset"+t[0].toUpperCase()+t.slice(1)]-o-u-s-.5))||0),u+l}function e2(e,t,n){// Start with computed style
var r=eW(e),i=(!h.boxSizingReliable()||n)&&"border-box"===C.css(e,"boxSizing",!1,r),o=i,a=eU(e,t,r),s="offset"+t[0].toUpperCase()+t.slice(1);// Support: Firefox <=54
// Return a confounding non-pixel value or feign ignorance, as appropriate.
if(e_.test(a)){if(!n)return a;a="auto"}// Adjust for the element's box model
return(!h.boxSizingReliable()&&i||// Support: IE 10 - 11+, Edge 15 - 18+
// IE/Edge misreport `getComputedStyle` of table rows with width/height
// set in CSS while `offset*` properties report correct values.
// Interestingly, in some cases IE 9 doesn't suffer from this issue.
!h.reliableTrDimensions()&&A(e,"tr")||// Fall back to offsetWidth/offsetHeight when value is "auto"
// This happens for inline elements with no explicit setting (gh-3571)
"auto"===a||// Support: Android <=4.1 - 4.3 only
// Also use offsetWidth/offsetHeight for misreported inline dimensions (gh-3602)
!parseFloat(a)&&"inline"===C.css(e,"display",!1,r))&&// Make sure the element is visible & connected
e.getClientRects().length&&(i="border-box"===C.css(e,"boxSizing",!1,r),// Where available, offsetWidth/offsetHeight approximate border box dimensions.
// Where not available (e.g., SVG), assume unreliable box-sizing and interpret the
// retrieved value as a content box dimension.
(o=s in e)&&(a=e[s])),// Normalize "" and auto
(a=parseFloat(a)||0)+e0(e,t,n||(i?"border":"content"),o,r,a)+"px"}function e4(e,t,n,r,i){return new e4.prototype.init(e,t,n,r,i)}C.extend({// Add in style property hooks for overriding the default
// behavior of getting and setting a style property
cssHooks:{opacity:{get:function(e,t){if(t){// We should always get a number back from opacity
var n=eU(e,"opacity");return""===n?"1":n}}}},// Don't automatically add "px" to these possibly-unitless properties
cssNumber:{animationIterationCount:!0,aspectRatio:!0,borderImageSlice:!0,columnCount:!0,flexGrow:!0,flexShrink:!0,fontWeight:!0,gridArea:!0,gridColumn:!0,gridColumnEnd:!0,gridColumnStart:!0,gridRow:!0,gridRowEnd:!0,gridRowStart:!0,lineHeight:!0,opacity:!0,order:!0,orphans:!0,scale:!0,widows:!0,zIndex:!0,zoom:!0,// SVG-related
fillOpacity:!0,floodOpacity:!0,stopOpacity:!0,strokeMiterlimit:!0,strokeOpacity:!0},// Add in properties whose names you wish to fix before
// setting or getting the value
cssProps:{},// Get and set the style property on a DOM Node
style:function(e,t,n,r){// Don't set styles on text and comment nodes
if(e&&3!==e.nodeType&&8!==e.nodeType&&e.style){// Make sure that we're working with the right name
var i,o,a,s=ee(t),u=eF.test(t),l=e.style;// Check if we're setting a value
if(u||(t=eJ(s)),// Gets hook for the prefixed version, then unprefixed version
a=C.cssHooks[t]||C.cssHooks[s],void 0===n)return(// If a hook was provided get the non-computed value from there
a&&"get"in a&&void 0!==(i=a.get(e,!1,r))?i:l[t]);// Make sure that null and NaN values aren't set (trac-7116)
"string"==(o=typeof n)&&(i=el.exec(n))&&i[1]&&(n=eg(e,t,i),// Fixes bug trac-9237
o="number"),null!=n&&n==n&&("number"!==o||u||(n+=i&&i[3]||(C.cssNumber[s]?"":"px")),h.clearCloneStyle||""!==n||0!==t.indexOf("background")||(l[t]="inherit"),a&&"set"in a&&void 0===(n=a.set(e,n,r))||(u?l.setProperty(t,n):l[t]=n))}},css:function(e,t,n,r){var i,o,a,s=ee(t);return(// Make numeric if forced or a qualifier was provided and val looks numeric
(eF.test(t)||(t=eJ(s)),// Try prefixed name followed by the unprefixed name
(a=C.cssHooks[t]||C.cssHooks[s])&&"get"in a&&(i=a.get(e,!0,n)),void 0===i&&(i=eU(e,t,r)),"normal"===i&&t in eZ&&(i=eZ[t]),""===n||n)?(o=parseFloat(i),!0===n||isFinite(o)?o||0:i):i)}}),C.each(["height","width"],function(e,t){C.cssHooks[t]={get:function(e,n,r){if(n)// but it must have a current display style that would benefit
return!eG.test(C.css(e,"display"))||// Support: Safari 8+
// Table columns in Safari have non-zero offsetWidth & zero
// getBoundingClientRect().width unless display is changed.
// Support: IE <=11 only
// Running getBoundingClientRect on a disconnected node
// in IE throws an error.
e.getClientRects().length&&e.getBoundingClientRect().width?e2(e,t,r):e$(e,eK,function(){return e2(e,t,r)})},set:function(e,n,r){var i,o=eW(e),// to avoid forcing a reflow.
a=!h.scrollboxSize()&&"absolute"===o.position,s=(a||r)&&"border-box"===C.css(e,"boxSizing",!1,o),u=r?e0(e,t,r,s,o):0;return s&&a&&(u-=Math.ceil(e["offset"+t[0].toUpperCase()+t.slice(1)]-parseFloat(o[t])-e0(e,t,"border",!1,o)-.5)),u&&(i=el.exec(n))&&"px"!==(i[3]||"px")&&(e.style[t]=n,n=C.css(e,t)),e1(e,n,u)}}}),C.cssHooks.marginLeft=eX(h.reliableMarginLeft,function(e,t){if(t)return(parseFloat(eU(e,"marginLeft"))||e.getBoundingClientRect().left-e$(e,{marginLeft:0},function(){return e.getBoundingClientRect().left}))+"px"}),// These hooks are used by animate to expand properties
C.each({margin:"",padding:"",border:"Width"},function(e,t){C.cssHooks[e+t]={expand:function(n){for(var r=0,i={},o="string"==typeof n?n.split(" "):[n];r<4;r++)i[e+ec[r]+t]=o[r]||o[r-2]||o[0];return i}},"margin"!==e&&(C.cssHooks[e+t].set=e1)}),C.fn.extend({css:function(e,t){return J(this,function(e,t,n){var r,i,o={},a=0;if(Array.isArray(t)){for(r=eW(e),i=t.length;a<i;a++)o[t[a]]=C.css(e,t[a],!1,r);return o}return void 0!==n?C.style(e,t,n):C.css(e,t)},e,t,arguments.length>1)}}),C.Tween=e4,e4.prototype={constructor:e4,init:function(e,t,n,r,i,o){this.elem=e,this.prop=n,this.easing=i||C.easing._default,this.options=t,this.start=this.now=this.cur(),this.end=r,this.unit=o||(C.cssNumber[n]?"":"px")},cur:function(){var e=e4.propHooks[this.prop];return e&&e.get?e.get(this):e4.propHooks._default.get(this)},run:function(e){var t,n=e4.propHooks[this.prop];return this.options.duration?this.pos=t=C.easing[this.easing](e,this.options.duration*e,0,1,this.options.duration):this.pos=t=e,this.now=(this.end-this.start)*t+this.start,this.options.step&&this.options.step.call(this.elem,this.now,this),n&&n.set?n.set(this):e4.propHooks._default.set(this),this}},e4.prototype.init.prototype=e4.prototype,e4.propHooks={_default:{get:function(e){var t;return(// Use a property on the element directly when it is not a DOM element,
// or when there is no matching style property that exists.
1!==e.elem.nodeType||null!=e.elem[e.prop]&&null==e.elem.style[e.prop]?e.elem[e.prop]:// Passing an empty string as a 3rd parameter to .css will automatically
// attempt a parseFloat and fallback to a string if the parse fails.
// Simple values such as "10px" are parsed to Float;
// complex values such as "rotate(1rad)" are returned as-is.
(t=C.css(e.elem,e.prop,""))&&"auto"!==t?t:0)},set:function(e){// Use step hook for back compat.
// Use cssHook if its there.
// Use .style if available and use plain properties where available.
C.fx.step[e.prop]?C.fx.step[e.prop](e):1===e.elem.nodeType&&(C.cssHooks[e.prop]||null!=e.elem.style[eJ(e.prop)])?C.style(e.elem,e.prop,e.now+e.unit):e.elem[e.prop]=e.now}}},// Support: IE <=9 only
// Panic based approach to setting things on disconnected nodes
e4.propHooks.scrollTop=e4.propHooks.scrollLeft={set:function(e){e.elem.nodeType&&e.elem.parentNode&&(e.elem[e.prop]=e.now)}},C.easing={linear:function(e){return e},swing:function(e){return .5-Math.cos(e*Math.PI)/2},_default:"swing"},C.fx=e4.prototype.init,// Back compat <1.8 extension point
C.fx.step={};var e5,e6,e3,e8,e7=/^(?:toggle|show|hide)$/,e9=/queueHooks$/;// Animations created synchronously will run synchronously
function te(){return e.setTimeout(function(){e3=void 0}),e3=Date.now()}// Generate parameters to create a standard animation
function tt(e,t){var n,r=0,i={height:e};for(// If we include width, step value is 1 to do all cssExpand values,
// otherwise step value is 2 to skip over Left and Right
t=t?1:0;r<4;r+=2-t)i["margin"+(n=ec[r])]=i["padding"+n]=e;return t&&(i.opacity=i.width=e),i}function tn(e,t,n){for(var r,i=(Animation.tweeners[t]||[]).concat(Animation.tweeners["*"]),o=0,a=i.length;o<a;o++)if(r=i[o].call(n,t,e))return r}function Animation(e,t,n){var r,i,o=0,a=Animation.prefilters.length,s=C.Deferred().always(function(){// Don't match elem in the :animated selector
delete u.elem}),u=function(){if(i)return!1;for(var t=e3||te(),n=Math.max(0,l.startTime+l.duration-t),r=1-(n/l.duration||0),o=0,a=l.tweens.length;o<a;o++)l.tweens[o].run(r);return(// If there's more to do, yield
(s.notifyWith(e,[l,r,n]),r<1&&a)?n:(a||s.notifyWith(e,[l,1,0]),// Resolve the animation and report its conclusion
s.resolveWith(e,[l]),!1))},l=s.promise({elem:e,props:C.extend({},t),opts:C.extend(!0,{specialEasing:{},easing:C.easing._default},n),originalProperties:t,originalOptions:n,startTime:e3||te(),duration:n.duration,tweens:[],createTween:function(t,n){var r=C.Tween(e,l.opts,t,n,l.opts.specialEasing[t]||l.opts.easing);return l.tweens.push(r),r},stop:function(t){var n=0,// otherwise we skip this part
r=t?l.tweens.length:0;if(i)return this;for(i=!0;n<r;n++)l.tweens[n].run(1);return t?(s.notifyWith(e,[l,1,0]),s.resolveWith(e,[l,t])):s.rejectWith(e,[l,t]),this}}),c=l.props;for(function(e,t){var n,r,i,o,a;// camelCase, specialEasing and expand cssHook pass
for(n in e)if(i=t[r=ee(n)],Array.isArray(o=e[n])&&(i=o[1],o=e[n]=o[0]),n!==r&&(e[r]=o,delete e[n]),(a=C.cssHooks[r])&&("expand"in a))// Not quite $.extend, this won't overwrite existing keys.
// Reusing 'index' because we have the correct "name"
for(n in o=a.expand(o),delete e[r],o)(n in e)||(e[n]=o[n],t[n]=i);else t[r]=i}(c,l.opts.specialEasing);o<a;o++)if(r=Animation.prefilters[o].call(l,e,c,l.opts))return g(r.stop)&&(C._queueHooks(l.elem,l.opts.queue).stop=r.stop.bind(r)),r;return C.map(c,tn,l),g(l.opts.start)&&l.opts.start.call(e,l),// Attach callbacks from options
l.progress(l.opts.progress).done(l.opts.done,l.opts.complete).fail(l.opts.fail).always(l.opts.always),C.fx.timer(C.extend(u,{elem:e,anim:l,queue:l.opts.queue})),l}C.Animation=C.extend(Animation,{tweeners:{"*":[function(e,t){var n=this.createTween(e,t);return eg(n.elem,e,el.exec(t),n),n}]},tweener:function(e,t){g(e)?(t=e,e=["*"]):e=e.match($);for(var n,r=0,i=e.length;r<i;r++)n=e[r],Animation.tweeners[n]=Animation.tweeners[n]||[],Animation.tweeners[n].unshift(t)},prefilters:[function(e,t,n){var r,i,o,a,s,u,l,c,f="width"in t||"height"in t,d=this,p={},h=e.style,g=e.nodeType&&eh(e),v=er.get(e,"fxshow");// Detect show/hide animations
for(r in n.queue||(null==(a=C._queueHooks(e,"fx")).unqueued&&(a.unqueued=0,s=a.empty.fire,a.empty.fire=function(){a.unqueued||s()}),a.unqueued++,d.always(function(){// Ensure the complete handler is called before this completes
d.always(function(){a.unqueued--,C.queue(e,"fx").length||a.empty.fire()})})),t)if(i=t[r],e7.test(i)){if(delete t[r],o=o||"toggle"===i,i===(g?"hide":"show")){// Pretend to be hidden if this is a "show" and
// there is still data from a stopped show/hide
if("show"!==i||!v||void 0===v[r])continue;g=!0}p[r]=v&&v[r]||C.style(e,r)}if(!(!// Bail out if this is a no-op like .hide().hide()
(u=!C.isEmptyObject(t))&&C.isEmptyObject(p)))for(r in f&&1===e.nodeType&&(// Support: IE <=9 - 11, Edge 12 - 15
// Record all 3 overflow attributes because IE does not infer the shorthand
// from identically-valued overflowX and overflowY and Edge just mirrors
// the overflowX value there.
n.overflow=[h.overflow,h.overflowX,h.overflowY],null==// Identify a display type, preferring old show/hide data over the CSS cascade
(l=v&&v.display)&&(l=er.get(e,"display")),"none"===(c=C.css(e,"display"))&&(l?c=l:(// Get nonempty value(s) by temporarily forcing visibility
ey([e],!0),l=e.style.display||l,c=C.css(e,"display"),ey([e]))),("inline"===c||"inline-block"===c&&null!=l)&&"none"===C.css(e,"float")&&(u||(d.done(function(){h.display=l}),null!=l||(l="none"===(c=h.display)?"":c)),h.display="inline-block")),n.overflow&&(h.overflow="hidden",d.always(function(){h.overflow=n.overflow[0],h.overflowX=n.overflow[1],h.overflowY=n.overflow[2]})),// Implement show/hide animations
u=!1,p)u||(v?"hidden"in v&&(g=v.hidden):v=er.access(e,"fxshow",{display:l}),o&&(v.hidden=!g),g&&ey([e],!0),/* eslint-disable no-loop-func */d.done(function(){for(r in g||ey([e]),er.remove(e,"fxshow"),p)C.style(e,r,p[r])})),// Per-property setup
u=tn(g?v[r]:0,r,d),r in v||(v[r]=u.start,g&&(u.end=u.start,u.start=0))}],prefilter:function(e,t){t?Animation.prefilters.unshift(e):Animation.prefilters.push(e)}}),C.speed=function(e,t,n){var r=e&&"object"==typeof e?C.extend({},e):{complete:n||!n&&t||g(e)&&e,duration:e,easing:n&&t||t&&!g(t)&&t};return C.fx.off?r.duration=0:"number"!=typeof r.duration&&(r.duration in C.fx.speeds?r.duration=C.fx.speeds[r.duration]:r.duration=C.fx.speeds._default),(null==r.queue||!0===r.queue)&&(r.queue="fx"),// Queueing
r.old=r.complete,r.complete=function(){g(r.old)&&r.old.call(this),r.queue&&C.dequeue(this,r.queue)},r},C.fn.extend({fadeTo:function(e,t,n,r){// Show any hidden elements after setting opacity to 0
return this.filter(eh).css("opacity",0).show()// Animate to the value specified
.end().animate({opacity:t},e,n,r)},animate:function(e,t,n,r){var i=C.isEmptyObject(e),o=C.speed(t,n,r),a=function(){// Operate on a copy of prop so per-property easing won't be lost
var t=Animation(this,C.extend({},e),o);// Empty animations, or finishing resolves immediately
(i||er.get(this,"finish"))&&t.stop(!0)};return a.finish=a,i||!1===o.queue?this.each(a):this.queue(o.queue,a)},stop:function(e,t,n){var r=function(e){var t=e.stop;delete e.stop,t(n)};return"string"!=typeof e&&(n=t,t=e,e=void 0),t&&this.queue(e||"fx",[]),this.each(function(){var t=!0,i=null!=e&&e+"queueHooks",o=C.timers,a=er.get(this);if(i)a[i]&&a[i].stop&&r(a[i]);else for(i in a)a[i]&&a[i].stop&&e9.test(i)&&r(a[i]);for(i=o.length;i--;)o[i].elem===this&&(null==e||o[i].queue===e)&&(o[i].anim.stop(n),t=!1,o.splice(i,1));// Start the next in the queue if the last step wasn't forced.
// Timers currently will call their complete callbacks, which
// will dequeue but only if they were gotoEnd.
(t||!n)&&C.dequeue(this,e)})},finish:function(e){return!1!==e&&(e=e||"fx"),this.each(function(){var t,n=er.get(this),r=n[e+"queue"],i=n[e+"queueHooks"],o=C.timers,a=r?r.length:0;// Look for any active animations, and finish them
for(// Enable finishing flag on private data
n.finish=!0,// Empty the queue first
C.queue(this,e,[]),i&&i.stop&&i.stop.call(this,!0),t=o.length;t--;)o[t].elem===this&&o[t].queue===e&&(o[t].anim.stop(!0),o.splice(t,1));// Look for any animations in the old queue and finish them
for(t=0;t<a;t++)r[t]&&r[t].finish&&r[t].finish.call(this);// Turn off finishing flag
delete n.finish})}}),C.each(["toggle","show","hide"],function(e,t){var n=C.fn[t];C.fn[t]=function(e,r,i){return null==e||"boolean"==typeof e?n.apply(this,arguments):this.animate(tt(t,!0),e,r,i)}}),// Generate shortcuts for custom animations
C.each({slideDown:tt("show"),slideUp:tt("hide"),slideToggle:tt("toggle"),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"},fadeToggle:{opacity:"toggle"}},function(e,t){C.fn[e]=function(e,n,r){return this.animate(t,e,n,r)}}),C.timers=[],C.fx.tick=function(){var e,t=0,n=C.timers;for(e3=Date.now();t<n.length;t++)// Run the timer and safely remove it when done (allowing for external removal)
(e=n[t])()||n[t]!==e||n.splice(t--,1);n.length||C.fx.stop(),e3=void 0},C.fx.timer=function(e){C.timers.push(e),C.fx.start()},C.fx.interval=13,C.fx.start=function(){e8||(e8=!0,function t(){e8&&(!1===y.hidden&&e.requestAnimationFrame?e.requestAnimationFrame(t):e.setTimeout(t,C.fx.interval),C.fx.tick())}())},C.fx.stop=function(){e8=null},C.fx.speeds={slow:600,fast:200,// Default speed
_default:400},// Based off of the plugin by Clint Helfers, with permission.
C.fn.delay=function(t,n){return t=C.fx&&C.fx.speeds[t]||t,n=n||"fx",this.queue(n,function(n,r){var i=e.setTimeout(n,t);r.stop=function(){e.clearTimeout(i)}})},tr=y.createElement("input"),ti=y.createElement("select").appendChild(y.createElement("option")),tr.type="checkbox",// Support: Android <=4.3 only
// Default value for a checkbox should be "on"
h.checkOn=""!==tr.value,// Support: IE <=11 only
// Must access selectedIndex to make default options select
h.optSelected=ti.selected,// Support: IE <=11 only
// An input loses its value after becoming a radio
(tr=y.createElement("input")).value="t",tr.type="radio",h.radioValue="t"===tr.value;var tr,ti,to,ta=C.expr.attrHandle;C.fn.extend({attr:function(e,t){return J(this,C.attr,e,t,arguments.length>1)},removeAttr:function(e){return this.each(function(){C.removeAttr(this,e)})}}),C.extend({attr:function(e,t,n){var r,i,o=e.nodeType;// Don't get/set attributes on text, comment and attribute nodes
if(3!==o&&8!==o&&2!==o){// Fallback to prop when attributes are not supported
if(void 0===e.getAttribute)return C.prop(e,t,n);if(1===o&&C.isXMLDoc(e)||(i=C.attrHooks[t.toLowerCase()]||(C.expr.match.bool.test(t)?to:void 0)),void 0!==n){if(null===n){C.removeAttr(e,t);return}return i&&"set"in i&&void 0!==(r=i.set(e,n,t))?r:(e.setAttribute(t,n+""),n)}return i&&"get"in i&&null!==(r=i.get(e,t))?r:null==(r=C.find.attr(e,t))?void 0:r}},attrHooks:{type:{set:function(e,t){if(!h.radioValue&&"radio"===t&&A(e,"input")){var n=e.value;return e.setAttribute("type",t),n&&(e.value=n),t}}}},removeAttr:function(e,t){var n,r=0,// https://html.spec.whatwg.org/multipage/syntax.html#attributes-2
i=t&&t.match($);if(i&&1===e.nodeType)for(;n=i[r++];)e.removeAttribute(n)}}),// Hooks for boolean attributes
to={set:function(e,t,n){return!1===t?C.removeAttr(e,n):e.setAttribute(n,n),n}},C.each(C.expr.match.bool.source.match(/\w+/g),function(e,t){var n=ta[t]||C.find.attr;ta[t]=function(e,t,r){var i,o,a=t.toLowerCase();return r||(// Avoid an infinite loop by temporarily removing this function from the getter
o=ta[a],ta[a]=i,i=null!=n(e,t,r)?a:null,ta[a]=o),i}});var ts=/^(?:input|select|textarea|button)$/i,tu=/^(?:a|area)$/i;// Strip and collapse whitespace according to HTML spec
// https://infra.spec.whatwg.org/#strip-and-collapse-ascii-whitespace
function tl(e){return(e.match($)||[]).join(" ")}function tc(e){return e.getAttribute&&e.getAttribute("class")||""}function tf(e){return Array.isArray(e)?e:"string"==typeof e&&e.match($)||[]}C.fn.extend({prop:function(e,t){return J(this,C.prop,e,t,arguments.length>1)},removeProp:function(e){return this.each(function(){delete this[C.propFix[e]||e]})}}),C.extend({prop:function(e,t,n){var r,i,o=e.nodeType;// Don't get/set properties on text, comment and attribute nodes
if(3!==o&&8!==o&&2!==o)return(1===o&&C.isXMLDoc(e)||(// Fix name and attach hooks
t=C.propFix[t]||t,i=C.propHooks[t]),void 0!==n)?i&&"set"in i&&void 0!==(r=i.set(e,n,t))?r:e[t]=n:i&&"get"in i&&null!==(r=i.get(e,t))?r:e[t]},propHooks:{tabIndex:{get:function(e){// Support: IE <=9 - 11 only
// elem.tabIndex doesn't always return the
// correct value when it hasn't been explicitly set
// Use proper attribute retrieval (trac-12072)
var t=C.find.attr(e,"tabindex");return t?parseInt(t,10):ts.test(e.nodeName)||tu.test(e.nodeName)&&e.href?0:-1}}},propFix:{for:"htmlFor",class:"className"}}),h.optSelected||(C.propHooks.selected={get:function(e){/* eslint no-unused-expressions: "off" */var t=e.parentNode;return t&&t.parentNode&&t.parentNode.selectedIndex,null},set:function(e){/* eslint no-unused-expressions: "off" */var t=e.parentNode;t&&(t.selectedIndex,t.parentNode&&t.parentNode.selectedIndex)}}),C.each(["tabIndex","readOnly","maxLength","cellSpacing","cellPadding","rowSpan","colSpan","useMap","frameBorder","contentEditable"],function(){C.propFix[this.toLowerCase()]=this}),C.fn.extend({addClass:function(e){var t,n,r,i,o,a;return g(e)?this.each(function(t){C(this).addClass(e.call(this,t,tc(this)))}):(t=tf(e)).length?this.each(function(){if(r=tc(this),n=1===this.nodeType&&" "+tl(r)+" "){for(o=0;o<t.length;o++)i=t[o],0>n.indexOf(" "+i+" ")&&(n+=i+" ");r!==// Only assign if different to avoid unneeded rendering.
(a=tl(n))&&this.setAttribute("class",a)}}):this},removeClass:function(e){var t,n,r,i,o,a;return g(e)?this.each(function(t){C(this).removeClass(e.call(this,t,tc(this)))}):arguments.length?(t=tf(e)).length?this.each(function(){if(r=tc(this),// This expression is here for better compressibility (see addClass)
n=1===this.nodeType&&" "+tl(r)+" "){for(o=0;o<t.length;o++)// Remove *all* instances
for(i=t[o];n.indexOf(" "+i+" ")>-1;)n=n.replace(" "+i+" "," ");r!==// Only assign if different to avoid unneeded rendering.
(a=tl(n))&&this.setAttribute("class",a)}}):this:this.attr("class","")},toggleClass:function(e,t){var n,r,i,o,a=typeof e,s="string"===a||Array.isArray(e);return g(e)?this.each(function(n){C(this).toggleClass(e.call(this,n,tc(this),t),t)}):"boolean"==typeof t&&s?t?this.addClass(e):this.removeClass(e):(n=tf(e),this.each(function(){if(s)for(i=0,// Toggle individual class names
o=C(this);i<n.length;i++)r=n[i],o.hasClass(r)?o.removeClass(r):o.addClass(r);else(void 0===e||"boolean"===a)&&((r=tc(this))&&er.set(this,"__className__",r),this.setAttribute&&this.setAttribute("class",r||!1===e?"":er.get(this,"__className__")||""))}))},hasClass:function(e){var t,n,r=0;for(t=" "+e+" ";n=this[r++];)if(1===n.nodeType&&(" "+tl(tc(n))+" ").indexOf(t)>-1)return!0;return!1}});var td=/\r/g;C.fn.extend({val:function(e){var t,n,r,i=this[0];return arguments.length?(r=g(e),this.each(function(n){var i;1!==this.nodeType||(null==(i=r?e.call(this,n,C(this).val()):e)?i="":"number"==typeof i?i+="":Array.isArray(i)&&(i=C.map(i,function(e){return null==e?"":e+""})),(t=C.valHooks[this.type]||C.valHooks[this.nodeName.toLowerCase()])&&"set"in t&&void 0!==t.set(this,i,"value")||(this.value=i))})):i?(t=C.valHooks[i.type]||C.valHooks[i.nodeName.toLowerCase()])&&"get"in t&&void 0!==(n=t.get(i,"value"))?n:"string"==typeof(n=i.value)?n.replace(td,""):null==n?"":n:void 0}}),C.extend({valHooks:{option:{get:function(e){var t=C.find.attr(e,"value");return null!=t?t:// option.text throws exceptions (trac-14686, trac-14858)
// Strip and collapse whitespace
// https://html.spec.whatwg.org/#strip-and-collapse-whitespace
tl(C.text(e))}},select:{get:function(e){var t,n,r,i=e.options,o=e.selectedIndex,a="select-one"===e.type,s=a?null:[],u=a?o+1:i.length;// Loop through all the selected options
for(r=o<0?u:a?o:0;r<u;r++)// Support: IE <=9 only
// IE8-9 doesn't update selected after form reset (trac-2551)
if(((n=i[r]).selected||r===o)&&// Don't return options that are disabled or in a disabled optgroup
!n.disabled&&(!n.parentNode.disabled||!A(n.parentNode,"optgroup"))){// We don't need an array for one selects
if(// Get the specific value for the option
t=C(n).val(),a)return t;// Multi-Selects return an array
s.push(t)}return s},set:function(e,t){for(var n,r,i=e.options,o=C.makeArray(t),a=i.length;a--;)/* eslint-disable no-cond-assign */((r=i[a]).selected=C.inArray(C.valHooks.option.get(r),o)>-1)&&(n=!0);return n||(e.selectedIndex=-1),o}}}}),// Radios and checkboxes getter/setter
C.each(["radio","checkbox"],function(){C.valHooks[this]={set:function(e,t){if(Array.isArray(t))return e.checked=C.inArray(C(e).val(),t)>-1}},h.checkOn||(C.valHooks[this].get=function(e){return null===e.getAttribute("value")?"on":e.value})});// Return jQuery for attributes-only inclusion
var tp=e.location,th={guid:Date.now()},tg=/\?/;// Cross-browser xml parsing
C.parseXML=function(t){var n,r;if(!t||"string"!=typeof t)return null;// Support: IE 9 - 11 only
// IE throws on parseFromString with invalid input.
try{n=new e.DOMParser().parseFromString(t,"text/xml")}catch(e){}return r=n&&n.getElementsByTagName("parsererror")[0],(!n||r)&&C.error("Invalid XML: "+(r?C.map(r.childNodes,function(e){return e.textContent}).join("\n"):t)),n};var tv=/^(?:focusinfocus|focusoutblur)$/,ty=function(e){e.stopPropagation()};C.extend(C.event,{trigger:function(t,n,r,i){var o,a,s,u,l,c,d,p,h=[r||y],m=f.call(t,"type")?t.type:t,x=f.call(t,"namespace")?t.namespace.split("."):[];// Don't do events on text and comment nodes
if(a=p=s=r=r||y,!(3===r.nodeType||8===r.nodeType||tv.test(m+C.event.triggered))&&(m.indexOf(".")>-1&&(m=// Namespaced trigger; create a regexp to match event type in handle()
(x=m.split(".")).shift(),x.sort()),l=0>m.indexOf(":")&&"on"+m,// Trigger bitmask: & 1 for native handlers; & 2 for jQuery (always true)
// Caller can pass in a jQuery.Event object, Object, or just an event type string
(t=t[C.expando]?t:new C.Event(m,"object"==typeof t&&t)).isTrigger=i?2:3,t.namespace=x.join("."),t.rnamespace=t.namespace?RegExp("(^|\\.)"+x.join("\\.(?:.*\\.|)")+"(\\.|$)"):null,// Clean up the event in case it is being reused
t.result=void 0,t.target||(t.target=r),// Clone any incoming data and prepend the event, creating the handler arg list
n=null==n?[t]:C.makeArray(n,[t]),// Allow special events to draw outside the lines
d=C.event.special[m]||{},i||!d.trigger||!1!==d.trigger.apply(r,n))){// Determine event propagation path in advance, per W3C events spec (trac-9951)
// Bubble up to document, then to window; watch for a global ownerDocument var (trac-9724)
if(!i&&!d.noBubble&&!v(r)){for(u=d.delegateType||m,tv.test(u+m)||(a=a.parentNode);a;a=a.parentNode)h.push(a),s=a;s===(r.ownerDocument||y)&&h.push(s.defaultView||s.parentWindow||e)}for(// Fire handlers on the event path
o=0;(a=h[o++])&&!t.isPropagationStopped();)p=a,t.type=o>1?u:d.bindType||m,// jQuery handler
(c=(er.get(a,"events")||Object.create(null))[t.type]&&er.get(a,"handle"))&&c.apply(a,n),// Native handler
(c=l&&a[l])&&c.apply&&et(a)&&(t.result=c.apply(a,n),!1===t.result&&t.preventDefault());return t.type=m,!i&&!t.isDefaultPrevented()&&(!d._default||!1===d._default.apply(h.pop(),n))&&et(r)&&l&&g(r[m])&&!v(r)&&(// Don't re-trigger an onFOO event when we call its FOO() method
(s=r[l])&&(r[l]=null),// Prevent re-triggering of the same event, since we already bubbled it above
C.event.triggered=m,t.isPropagationStopped()&&p.addEventListener(m,ty),r[m](),t.isPropagationStopped()&&p.removeEventListener(m,ty),C.event.triggered=void 0,s&&(r[l]=s)),t.result}},// Piggyback on a donor event to simulate a different one
// Used only for `focus(in | out)` events
simulate:function(e,t,n){var r=C.extend(new C.Event,n,{type:e,isSimulated:!0});C.event.trigger(r,null,t)}}),C.fn.extend({trigger:function(e,t){return this.each(function(){C.event.trigger(e,t,this)})},triggerHandler:function(e,t){var n=this[0];if(n)return C.event.trigger(e,t,n,!0)}});var tm=/\[\]$/,tx=/\r?\n/g,tb=/^(?:submit|button|image|reset|file)$/i,tw=/^(?:input|select|textarea|keygen)/i;// Serialize an array of form elements or a set of
// key/values into a query string
C.param=function(e,t){var n,r=[],i=function(e,t){// If value is a function, invoke it and use its return value
var n=g(t)?t():t;r[r.length]=encodeURIComponent(e)+"="+encodeURIComponent(null==n?"":n)};if(null==e)return"";// If an array was passed in, assume that it is an array of form elements.
if(Array.isArray(e)||e.jquery&&!C.isPlainObject(e))C.each(e,function(){i(this.name,this.value)});else // did it), otherwise encode params recursively.
for(n in e)!function e(t,n,r,i){var o;if(Array.isArray(n))C.each(n,function(n,o){r||tm.test(t)?i(t,o):e(t+"["+("object"==typeof o&&null!=o?n:"")+"]",o,r,i)});else if(r||"object"!==b(n))i(t,n);else for(o in n)e(t+"["+o+"]",n[o],r,i)}(n,e[n],t,i);// Return the resulting serialization
return r.join("&")},C.fn.extend({serialize:function(){return C.param(this.serializeArray())},serializeArray:function(){return this.map(function(){// Can add propHook for "elements" to filter or add form elements
var e=C.prop(this,"elements");return e?C.makeArray(e):this}).filter(function(){var e=this.type;// Use .is( ":disabled" ) so that fieldset[disabled] works
return this.name&&!C(this).is(":disabled")&&tw.test(this.nodeName)&&!tb.test(e)&&(this.checked||!em.test(e))}).map(function(e,t){var n=C(this).val();return null==n?null:Array.isArray(n)?C.map(n,function(e){return{name:t.name,value:e.replace(tx,"\r\n")}}):{name:t.name,value:n.replace(tx,"\r\n")}}).get()}});var tT=/%20/g,tC=/#.*$/,tk=/([?&])_=[^&]*/,tA=/^(.*?):[ \t]*([^\r\n]*)$/mg,tE=/^(?:GET|HEAD)$/,tS=/^\/\//,/* Prefilters
	 * 1) They are useful to introduce custom dataTypes (see ajax/jsonp.js for an example)
	 * 2) These are called:
	 *    - BEFORE asking for a transport
	 *    - AFTER param serialization (s.data is a string if s.processData is true)
	 * 3) key is the dataType
	 * 4) the catchall symbol "*" can be used
	 * 5) execution will start with transport dataType and THEN continue down to "*" if needed
	 */tD={},/* Transports bindings
	 * 1) key is the dataType
	 * 2) the catchall symbol "*" can be used
	 * 3) selection will start with transport dataType and THEN go to "*" if needed
	 */tN={},tM="*/".concat("*"),tj=y.createElement("a");// Base "constructor" for jQuery.ajaxPrefilter and jQuery.ajaxTransport
function tL(e){// dataTypeExpression is optional and defaults to "*"
return function(t,n){"string"!=typeof t&&(n=t,t="*");var r,i=0,o=t.toLowerCase().match($)||[];if(g(n))// For each dataType in the dataTypeExpression
for(;r=o[i++];)"+"===r[0]?(e[r=r.slice(1)||"*"]=e[r]||[]).unshift(n):(e[r]=e[r]||[]).push(n)}}// Base inspection function for prefilters and transports
function tq(e,t,n,r){var i={},o=e===tN;function a(s){var u;return i[s]=!0,C.each(e[s]||[],function(e,s){var l=s(t,n,r);return"string"!=typeof l||o||i[l]?o?!(u=l):void 0:(t.dataTypes.unshift(l),a(l),!1)}),u}return a(t.dataTypes[0])||!i["*"]&&a("*")}// A special extend for ajax options
// that takes "flat" options (not to be deep extended)
// Fixes trac-9887
function tB(e,t){var n,r,i=C.ajaxSettings.flatOptions||{};for(n in t)void 0!==t[n]&&((i[n]?e:r||(r={}))[n]=t[n]);return r&&C.extend(!0,e,r),e}tj.href=tp.href,C.extend({// Counter for holding the number of active queries
active:0,// Last-Modified header cache for next request
lastModified:{},etag:{},ajaxSettings:{url:tp.href,type:"GET",isLocal:/^(?:about|app|app-storage|.+-extension|file|res|widget):$/.test(tp.protocol),global:!0,processData:!0,async:!0,contentType:"application/x-www-form-urlencoded; charset=UTF-8",/*
		timeout: 0,
		data: null,
		dataType: null,
		username: null,
		password: null,
		cache: null,
		throws: false,
		traditional: false,
		headers: {},
		*/accepts:{"*":tM,text:"text/plain",html:"text/html",xml:"application/xml, text/xml",json:"application/json, text/javascript"},contents:{xml:/\bxml\b/,html:/\bhtml/,json:/\bjson\b/},responseFields:{xml:"responseXML",text:"responseText",json:"responseJSON"},// Data converters
// Keys separate source (or catchall "*") and destination types with a single space
converters:{// Convert anything to text
"* text":String,// Text to html (true = no transformation)
"text html":!0,// Evaluate text as a json expression
"text json":JSON.parse,// Parse text as xml
"text xml":C.parseXML},// For options that shouldn't be deep extended:
// you can add your own custom options here if
// and when you create one that shouldn't be
// deep extended (see ajaxExtend)
flatOptions:{url:!0,context:!0}},// Creates a full fledged settings object into target
// with both ajaxSettings and settings fields.
// If target is omitted, writes into ajaxSettings.
ajaxSetup:function(e,t){return t?tB(tB(e,C.ajaxSettings),t):tB(C.ajaxSettings,e)},ajaxPrefilter:tL(tD),ajaxTransport:tL(tN),// Main method
ajax:function(t,n){"object"==typeof t&&(n=t,t=void 0),// Force options to be an object
n=n||{};var r,i,o,a,s,u,l,c,f,d,p=C.ajaxSetup({},n),h=p.context||p,g=p.context&&(h.nodeType||h.jquery)?C(h):C.event,v=C.Deferred(),m=C.Callbacks("once memory"),x=p.statusCode||{},b={},w={},T="canceled",k={readyState:0,// Builds headers hashtable if needed
getResponseHeader:function(e){var t;if(l){if(!a)for(a={};t=tA.exec(o);)a[t[1].toLowerCase()+" "]=(a[t[1].toLowerCase()+" "]||[]).concat(t[2]);t=a[e.toLowerCase()+" "]}return null==t?null:t.join(", ")},// Raw string
getAllResponseHeaders:function(){return l?o:null},// Caches the header
setRequestHeader:function(e,t){return null==l&&(b[e=w[e.toLowerCase()]=w[e.toLowerCase()]||e]=t),this},// Overrides response content-type header
overrideMimeType:function(e){return null==l&&(p.mimeType=e),this},// Status-dependent callbacks
statusCode:function(e){var t;if(e){if(l)k.always(e[k.status]);else for(t in e)x[t]=[x[t],e[t]]}return this},// Cancel the request
abort:function(e){var t=e||T;return r&&r.abort(t),A(0,t),this}};// A cross-domain request is in order when the origin doesn't match the current origin.
if(// Attach deferreds
v.promise(k),// Add protocol if not provided (prefilters might expect it)
// Handle falsy url in the settings object (trac-10093: consistency with old signature)
// We also use the url parameter if available
p.url=((t||p.url||tp.href)+"").replace(tS,tp.protocol+"//"),// Alias method option to type as per ticket trac-12004
p.type=n.method||n.type||p.method||p.type,// Extract dataTypes list
p.dataTypes=(p.dataType||"*").toLowerCase().match($)||[""],null==p.crossDomain){u=y.createElement("a");// Support: IE <=8 - 11, Edge 12 - 15
// IE throws exception on accessing the href property if url is malformed,
// e.g. http://example.com:80x/
try{u.href=p.url,// Support: IE <=8 - 11 only
// Anchor's host property isn't correctly set when s.url is relative
u.href=u.href,p.crossDomain=tj.protocol+"//"+tj.host!=u.protocol+"//"+u.host}catch(e){// If there is an error parsing the URL, assume it is crossDomain,
// it can be rejected by the transport if it is invalid
p.crossDomain=!0}}// If request was aborted inside a prefilter, stop there
if(p.data&&p.processData&&"string"!=typeof p.data&&(p.data=C.param(p.data,p.traditional)),// Apply prefilters
tq(tD,p,n,k),l)return k;// Check for headers option
for(f in// We can fire global events as of now if asked to
// Don't fire events if jQuery.event is undefined in an AMD-usage scenario (trac-15118)
(c=C.event&&p.global)&&0==C.active++&&C.event.trigger("ajaxStart"),// Uppercase the type
p.type=p.type.toUpperCase(),// Determine if request has content
p.hasContent=!tE.test(p.type),// Save the URL in case we're toying with the If-Modified-Since
// and/or If-None-Match header later on
// Remove hash to simplify url manipulation
i=p.url.replace(tC,""),p.hasContent?p.data&&p.processData&&0===(p.contentType||"").indexOf("application/x-www-form-urlencoded")&&(p.data=p.data.replace(tT,"+")):(// Remember the hash so we can put it back
d=p.url.slice(i.length),p.data&&(p.processData||"string"==typeof p.data)&&(i+=(tg.test(i)?"&":"?")+p.data,// trac-9682: remove data so that it's not used in an eventual retry
delete p.data),!1===p.cache&&(i=i.replace(tk,"$1"),d=(tg.test(i)?"&":"?")+"_="+th.guid+++d),// Put hash and anti-cache on the URL that will be requested (gh-1732)
p.url=i+d),p.ifModified&&(C.lastModified[i]&&k.setRequestHeader("If-Modified-Since",C.lastModified[i]),C.etag[i]&&k.setRequestHeader("If-None-Match",C.etag[i])),(p.data&&p.hasContent&&!1!==p.contentType||n.contentType)&&k.setRequestHeader("Content-Type",p.contentType),// Set the Accepts header for the server, depending on the dataType
k.setRequestHeader("Accept",p.dataTypes[0]&&p.accepts[p.dataTypes[0]]?p.accepts[p.dataTypes[0]]+("*"!==p.dataTypes[0]?", "+tM+"; q=0.01":""):p.accepts["*"]),p.headers)k.setRequestHeader(f,p.headers[f]);// Allow custom headers/mimetypes and early abort
if(p.beforeSend&&(!1===p.beforeSend.call(h,k,p)||l))return k.abort();// If no transport, we auto-abort
if(// Aborting is no longer a cancellation
T="abort",// Install callbacks on deferreds
m.add(p.complete),k.done(p.success),k.fail(p.error),// Get transport
r=tq(tN,p,n,k)){// If request was aborted inside ajaxSend, stop there
if(k.readyState=1,c&&g.trigger("ajaxSend",[k,p]),l)return k;p.async&&p.timeout>0&&(s=e.setTimeout(function(){k.abort("timeout")},p.timeout));try{l=!1,r.send(b,A)}catch(e){// Rethrow post-completion exceptions
if(l)throw e;// Propagate others as results
A(-1,e)}}else A(-1,"No Transport");// Callback for when everything is done
function A(t,n,a,u){var f,d,y,b,w,T=n;// Ignore repeat invocations
l||(l=!0,s&&e.clearTimeout(s),// Dereference transport for early garbage collection
// (no matter how long the jqXHR object will be used)
r=void 0,// Cache response headers
o=u||"",// Set readyState
k.readyState=t>0?4:0,// Determine if successful
f=t>=200&&t<300||304===t,a&&(b=/* Handles responses to an ajax request:
 * - finds the right dataType (mediates between content-type and expected dataType)
 * - returns the corresponding response
 */function(e,t,n){// Remove auto dataType and get content-type in the process
for(var r,i,o,a,s=e.contents,u=e.dataTypes;"*"===u[0];)u.shift(),void 0===r&&(r=e.mimeType||t.getResponseHeader("Content-Type"));// Check if we're dealing with a known content-type
if(r){for(i in s)if(s[i]&&s[i].test(r)){u.unshift(i);break}}// Check to see if we have a response for the expected dataType
if(u[0]in n)o=u[0];else{// Try convertible dataTypes
for(i in n){if(!u[0]||e.converters[i+" "+u[0]]){o=i;break}a||(a=i)}// Or just use first one
o=o||a}// If we found a dataType
// We add the dataType to the list if needed
// and return the corresponding response
if(o)return o!==u[0]&&u.unshift(o),n[o]}(p,k,a)),!f&&C.inArray("script",p.dataTypes)>-1&&0>C.inArray("json",p.dataTypes)&&(p.converters["text script"]=function(){}),// Convert no matter what (that way responseXXX fields are always set)
b=/* Chain conversions given the request and the original response
 * Also sets the responseXXX fields on the jqXHR instance
 */function(e,t,n,r){var i,o,a,s,u,l={},c=e.dataTypes.slice();// Create converters map with lowercased keys
if(c[1])for(a in e.converters)l[a.toLowerCase()]=e.converters[a];// Convert to each sequential dataType
for(o=c.shift();o;)if(e.responseFields[o]&&(n[e.responseFields[o]]=t),!u&&r&&e.dataFilter&&(t=e.dataFilter(t,e.dataType)),u=o,o=c.shift()){// There's only work to do if current dataType is non-auto
if("*"===o)o=u;else if("*"!==u&&u!==o){// If none found, seek a pair
if(!// Seek a direct converter
(a=l[u+" "+o]||l["* "+o])){for(i in l)if(// If conv2 outputs current
(s=i.split(" "))[1]===o&&// If prev can be converted to accepted input
(a=l[u+" "+s[0]]||l["* "+s[0]])){// Condense equivalence converters
!0===a?a=l[i]:!0!==l[i]&&(o=s[0],c.unshift(s[1]));break}}// Apply converter (if not an equivalence)
if(!0!==a){// Unless errors are allowed to bubble, catch and return them
if(a&&e.throws)t=a(t);else try{t=a(t)}catch(e){return{state:"parsererror",error:a?e:"No conversion from "+u+" to "+o}}}}}return{state:"success",data:t}}(p,b,k,f),f?(p.ifModified&&((w=k.getResponseHeader("Last-Modified"))&&(C.lastModified[i]=w),(w=k.getResponseHeader("etag"))&&(C.etag[i]=w)),204===t||"HEAD"===p.type?T="nocontent":304===t?T="notmodified":(T=b.state,d=b.data,f=!(y=b.error))):(// Extract error from statusText and normalize for non-aborts
y=T,(t||!T)&&(T="error",t<0&&(t=0))),// Set data for the fake xhr object
k.status=t,k.statusText=(n||T)+"",f?v.resolveWith(h,[d,T,k]):v.rejectWith(h,[k,T,y]),// Status-dependent callbacks
k.statusCode(x),x=void 0,c&&g.trigger(f?"ajaxSuccess":"ajaxError",[k,p,f?d:y]),// Complete
m.fireWith(h,[k,T]),!c||(g.trigger("ajaxComplete",[k,p]),--C.active||C.event.trigger("ajaxStop")))}return k},getJSON:function(e,t,n){return C.get(e,t,n,"json")},getScript:function(e,t){return C.get(e,void 0,t,"script")}}),C.each(["get","post"],function(e,t){C[t]=function(e,n,r,i){// The url can be an options object (which then must have .url)
return g(n)&&(i=i||r,r=n,n=void 0),C.ajax(C.extend({url:e,type:t,dataType:i,data:n,success:r},C.isPlainObject(e)&&e))}}),C.ajaxPrefilter(function(e){var t;for(t in e.headers)"content-type"===t.toLowerCase()&&(e.contentType=e.headers[t]||"")}),C._evalUrl=function(e,t,n){return C.ajax({url:e,// Make this explicit, since user can override this through ajaxSetup (trac-11264)
type:"GET",dataType:"script",cache:!0,async:!1,global:!1,// Only evaluate the response if it is successful (gh-4126)
// dataFilter is not invoked for failure responses, so using it instead
// of the default converter is kludgy but it works.
converters:{"text script":function(){}},dataFilter:function(e){C.globalEval(e,t,n)}})},C.fn.extend({wrapAll:function(e){var t;return this[0]&&(g(e)&&(e=e.call(this[0])),// The elements to wrap the target around
t=C(e,this[0].ownerDocument).eq(0).clone(!0),this[0].parentNode&&t.insertBefore(this[0]),t.map(function(){for(var e=this;e.firstElementChild;)e=e.firstElementChild;return e}).append(this)),this},wrapInner:function(e){return g(e)?this.each(function(t){C(this).wrapInner(e.call(this,t))}):this.each(function(){var t=C(this),n=t.contents();n.length?n.wrapAll(e):t.append(e)})},wrap:function(e){var t=g(e);return this.each(function(n){C(this).wrapAll(t?e.call(this,n):e)})},unwrap:function(e){return this.parent(e).not("body").each(function(){C(this).replaceWith(this.childNodes)}),this}}),C.expr.pseudos.hidden=function(e){return!C.expr.pseudos.visible(e)},C.expr.pseudos.visible=function(e){return!!(e.offsetWidth||e.offsetHeight||e.getClientRects().length)},C.ajaxSettings.xhr=function(){try{return new e.XMLHttpRequest}catch(e){}};var tO={// File protocol always yields status code 0, assume 200
0:200,// Support: IE <=9 only
// trac-1450: sometimes IE returns 1223 when it should be 204
1223:204},tP=C.ajaxSettings.xhr();h.cors=!!tP&&"withCredentials"in tP,h.ajax=tP=!!tP,C.ajaxTransport(function(t){var n,r;// Cross domain only allowed if supported through XMLHttpRequest
if(h.cors||tP&&!t.crossDomain)return{send:function(i,o){var a,s=t.xhr();// Apply custom fields if provided
if(s.open(t.type,t.url,t.async,t.username,t.password),t.xhrFields)for(a in t.xhrFields)s[a]=t.xhrFields[a];// Set headers
for(a in t.mimeType&&s.overrideMimeType&&s.overrideMimeType(t.mimeType),t.crossDomain||i["X-Requested-With"]||(i["X-Requested-With"]="XMLHttpRequest"),i)s.setRequestHeader(a,i[a]);// Callback
n=function(e){return function(){n&&(n=r=s.onload=s.onerror=s.onabort=s.ontimeout=s.onreadystatechange=null,"abort"===e?s.abort():"error"===e?"number"!=typeof s.status?o(0,"error"):o(s.status,s.statusText):o(tO[s.status]||s.status,s.statusText,"text"!==// IE9 has no XHR2 but throws on binary (trac-11426)
// For XHR2 non-text, let the caller handle it (gh-2498)
(s.responseType||"text")||"string"!=typeof s.responseText?{binary:s.response}:{text:s.responseText},s.getAllResponseHeaders()))}},// Listen to events
s.onload=n(),r=s.onerror=s.ontimeout=n("error"),void 0!==s.onabort?s.onabort=r:s.onreadystatechange=function(){// Check readyState before timeout as it changes
4===s.readyState&&// but that will not handle a native abort
// Also, save errorCallback to a variable
// as xhr.onerror cannot be accessed
e.setTimeout(function(){n&&r()})},// Create the abort callback
n=n("abort");try{// Do send the request (this may raise an exception)
s.send(t.hasContent&&t.data||null)}catch(e){// trac-14683: Only rethrow if this hasn't been notified as an error yet
if(n)throw e}},abort:function(){n&&n()}}}),// Prevent auto-execution of scripts when no explicit dataType was provided (See gh-2432)
C.ajaxPrefilter(function(e){e.crossDomain&&(e.contents.script=!1)}),// Install script dataType
C.ajaxSetup({accepts:{script:"text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},contents:{script:/\b(?:java|ecma)script\b/},converters:{"text script":function(e){return C.globalEval(e),e}}}),// Handle cache's special case and crossDomain
C.ajaxPrefilter("script",function(e){void 0===e.cache&&(e.cache=!1),e.crossDomain&&(e.type="GET")}),// Bind script tag hack transport
C.ajaxTransport("script",function(e){// This transport only deals with cross domain or forced-by-attrs requests
if(e.crossDomain||e.scriptAttrs){var t,n;return{send:function(r,i){t=C("<script>").attr(e.scriptAttrs||{}).prop({charset:e.scriptCharset,src:e.url}).on("load error",n=function(e){t.remove(),n=null,e&&i("error"===e.type?404:200,e.type)}),// Use native DOM manipulation to avoid our domManip AJAX trickery
y.head.appendChild(t[0])},abort:function(){n&&n()}}}});var tI=[],tH=/(=)\?(?=&|$)|\?\?/;// Default jsonp settings
C.ajaxSetup({jsonp:"callback",jsonpCallback:function(){var e=tI.pop()||C.expando+"_"+th.guid++;return this[e]=!0,e}}),// Detect, normalize options and install callbacks for jsonp requests
C.ajaxPrefilter("json jsonp",function(t,n,r){var i,o,a,s=!1!==t.jsonp&&(tH.test(t.url)?"url":"string"==typeof t.data&&0===(t.contentType||"").indexOf("application/x-www-form-urlencoded")&&tH.test(t.data)&&"data");// Handle iff the expected data type is "jsonp" or we have a parameter to set
if(s||"jsonp"===t.dataTypes[0])// Delegate to script
return(// Get callback name, remembering preexisting value associated with it
i=t.jsonpCallback=g(t.jsonpCallback)?t.jsonpCallback():t.jsonpCallback,s?t[s]=t[s].replace(tH,"$1"+i):!1!==t.jsonp&&(t.url+=(tg.test(t.url)?"&":"?")+t.jsonp+"="+i),// Use data converter to retrieve json after script execution
t.converters["script json"]=function(){return a||C.error(i+" was not called"),a[0]},// Force json dataType
t.dataTypes[0]="json",// Install callback
o=e[i],e[i]=function(){a=arguments},// Clean-up function (fires after converters)
r.always(function(){void 0===o?C(e).removeProp(i):e[i]=o,t[i]&&(// Make sure that re-using the options doesn't screw things around
t.jsonpCallback=n.jsonpCallback,// Save the callback name for future use
tI.push(i)),a&&g(o)&&o(a[0]),a=o=void 0}),"script")}),// Support: Safari 8 only
// In Safari 8 documents created via document.implementation.createHTMLDocument
// collapse sibling forms: the second one becomes a child of the first one.
// Because of that, this security measure has to be disabled in Safari 8.
// https://bugs.webkit.org/show_bug.cgi?id=137337
h.createHTMLDocument=((n=y.implementation.createHTMLDocument("").body).innerHTML="<form></form><form></form>",2===n.childNodes.length),// Argument "data" should be string of html
// context (optional): If specified, the fragment will be created in this context,
// defaults to document
// keepScripts (optional): If true, will include scripts passed in the html string
C.parseHTML=function(e,t,n){var r,i,o;return"string"!=typeof e?[]:("boolean"==typeof t&&(n=t,t=!1),t||(h.createHTMLDocument?(// Set the base href for the created document
// so any parsed elements with URLs
// are based on the document's URL (gh-2965)
(r=(t=y.implementation.createHTMLDocument("")).createElement("base")).href=y.location.href,t.head.appendChild(r)):t=y),i=P.exec(e),o=!n&&[],i)?[t.createElement(i[1])]:(i=eA([e],t,o),o&&o.length&&C(o).remove(),C.merge([],i.childNodes))},/**
 * Load a url into a page
 */C.fn.load=function(e,t,n){var r,i,o,a=this,s=e.indexOf(" ");return s>-1&&(r=tl(e.slice(s)),e=e.slice(0,s)),g(t)?(// We assume that it's the callback
n=t,t=void 0):t&&"object"==typeof t&&(i="POST"),a.length>0&&C.ajax({url:e,// If "type" variable is undefined, then "GET" method will be used.
// Make value of this field explicit since
// user can override it through ajaxSetup method
type:i||"GET",dataType:"html",data:t}).done(function(e){// Save response for use in complete callback
o=arguments,a.html(r?// Exclude scripts to avoid IE 'Permission Denied' errors
C("<div>").append(C.parseHTML(e)).find(r):e);// If the request succeeds, this function gets "data", "status", "jqXHR"
// but they are ignored because response was set above.
// If it fails, this function gets "jqXHR", "status", "error"
}).always(n&&function(e,t){a.each(function(){n.apply(this,o||[e.responseText,t,e])})}),this},C.expr.pseudos.animated=function(e){return C.grep(C.timers,function(t){return e===t.elem}).length},C.offset={setOffset:function(e,t,n){var r,i,o,a,s,u,l=C.css(e,"position"),c=C(e),f={};"static"===l&&(e.style.position="relative"),s=c.offset(),o=C.css(e,"top"),u=C.css(e,"left"),("absolute"===l||"fixed"===l)&&(o+u).indexOf("auto")>-1?(a=(r=c.position()).top,i=r.left):(a=parseFloat(o)||0,i=parseFloat(u)||0),g(t)&&(t=t.call(e,n,C.extend({},s))),null!=t.top&&(f.top=t.top-s.top+a),null!=t.left&&(f.left=t.left-s.left+i),"using"in t?t.using.call(e,f):c.css(f)}},C.fn.extend({// offset() relates an element's border box to the document origin
offset:function(e){// Preserve chaining for setter
if(arguments.length)return void 0===e?this:this.each(function(t){C.offset.setOffset(this,e,t)});var t,n,r=this[0];if(r)return(// Return zeros for disconnected and hidden (display: none) elements (gh-2310)
// Support: IE <=11 only
// Running getBoundingClientRect on a
// disconnected node in IE throws an error
r.getClientRects().length?(// Get document-relative position by adding viewport scroll to viewport-relative gBCR
t=r.getBoundingClientRect(),n=r.ownerDocument.defaultView,{top:t.top+n.pageYOffset,left:t.left+n.pageXOffset}):{top:0,left:0})},// position() relates an element's margin box to its offset parent's padding box
// This corresponds to the behavior of CSS absolute positioning
position:function(){if(this[0]){var e,t,n,r=this[0],i={top:0,left:0};// position:fixed elements are offset from the viewport, which itself always has zero offset
if("fixed"===C.css(r,"position"))t=r.getBoundingClientRect();else{for(t=this.offset(),// Account for the *real* offset parent, which can be the document or its root element
// when a statically positioned element is identified
n=r.ownerDocument,e=r.offsetParent||n.documentElement;e&&(e===n.body||e===n.documentElement)&&"static"===C.css(e,"position");)e=e.parentNode;e&&e!==r&&1===e.nodeType&&(// Incorporate borders into its offset, since they are outside its content origin
i=C(e).offset(),i.top+=C.css(e,"borderTopWidth",!0),i.left+=C.css(e,"borderLeftWidth",!0))}// Subtract parent offsets and element margins
return{top:t.top-i.top-C.css(r,"marginTop",!0),left:t.left-i.left-C.css(r,"marginLeft",!0)}}},// This method will return documentElement in the following cases:
// 1) For the element inside the iframe without offsetParent, this method will return
//    documentElement of the parent window
// 2) For the hidden or detached element
// 3) For body or html element, i.e. in case of the html node - it will return itself
//
// but those exceptions were never presented as a real life use-cases
// and might be considered as more preferable results.
//
// This logic, however, is not guaranteed and can change at any point in the future
offsetParent:function(){return this.map(function(){for(var e=this.offsetParent;e&&"static"===C.css(e,"position");)e=e.offsetParent;return e||ef})}}),// Create scrollLeft and scrollTop methods
C.each({scrollLeft:"pageXOffset",scrollTop:"pageYOffset"},function(e,t){var n="pageYOffset"===t;C.fn[e]=function(r){return J(this,function(e,r,i){// Coalesce documents and windows
var o;if(v(e)?o=e:9===e.nodeType&&(o=e.defaultView),void 0===i)return o?o[t]:e[r];o?o.scrollTo(n?o.pageXOffset:i,n?i:o.pageYOffset):e[r]=i},e,r,arguments.length)}}),// Support: Safari <=7 - 9.1, Chrome <=37 - 49
// Add the top/left cssHooks using jQuery.fn.position
// Webkit bug: https://bugs.webkit.org/show_bug.cgi?id=29084
// Blink bug: https://bugs.chromium.org/p/chromium/issues/detail?id=589347
// getComputedStyle returns percent when specified for top/left/bottom/right;
// rather than make the css module depend on the offset module, just check for it here
C.each(["top","left"],function(e,t){C.cssHooks[t]=eX(h.pixelPosition,function(e,n){if(n)// If curCSS returns percentage, fallback to offset
return n=eU(e,t),e_.test(n)?C(e).position()[t]+"px":n})}),// Create innerHeight, innerWidth, height, width, outerHeight and outerWidth methods
C.each({Height:"height",Width:"width"},function(e,t){C.each({padding:"inner"+e,content:t,"":"outer"+e},function(n,r){// Margin is only for outerHeight, outerWidth
C.fn[r]=function(i,o){var a=arguments.length&&(n||"boolean"!=typeof i),s=n||(!0===i||!0===o?"margin":"border");return J(this,function(t,n,i){var o;return v(t)?0===r.indexOf("outer")?t["inner"+e]:t.document.documentElement["client"+e]:9===t.nodeType?(o=t.documentElement,Math.max(t.body["scroll"+e],o["scroll"+e],t.body["offset"+e],o["offset"+e],o["client"+e])):void 0===i?C.css(t,n,s):C.style(t,n,i,s)},t,a?i:void 0,a)}})}),C.each(["ajaxStart","ajaxStop","ajaxComplete","ajaxError","ajaxSuccess","ajaxSend"],function(e,t){C.fn[t]=function(e){return this.on(t,e)}}),C.fn.extend({bind:function(e,t,n){return this.on(e,null,t,n)},unbind:function(e,t){return this.off(e,null,t)},delegate:function(e,t,n,r){return this.on(t,e,n,r)},undelegate:function(e,t,n){// ( namespace ) or ( selector, types [, fn] )
return 1==arguments.length?this.off(e,"**"):this.off(t,e||"**",n)},hover:function(e,t){return this.on("mouseenter",e).on("mouseleave",t||e)}}),C.each("blur focus focusin focusout resize scroll click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup contextmenu".split(" "),function(e,t){// Handle event binding
C.fn[t]=function(e,n){return arguments.length>0?this.on(t,null,e,n):this.trigger(t)}});// Support: Android <=4.0 only
// Make sure we trim BOM and NBSP
// Require that the "whitespace run" starts from a non-whitespace
// to avoid O(N^2) behavior when the engine would try matching "\s+$" at each space position.
var tR=/^[\s\uFEFF\xA0]+|([^\s\uFEFF\xA0])[\s\uFEFF\xA0]+$/g;// Bind a function to a context, optionally partially applying any
// arguments.
// jQuery.proxy is deprecated to promote standards (specifically Function#bind)
// However, it is not slated for removal any time soon
C.proxy=function(e,t){var n,r,i;// Quick check to determine if target is callable, in the spec
// this throws a TypeError, but we will just return undefined.
if("string"==typeof t&&(n=e[t],t=e,e=n),g(e))return(// Simulated bind
r=o.call(arguments,2),// Set the guid of unique handler to the same of original handler, so it can be removed
(i=function(){return e.apply(t||this,r.concat(o.call(arguments)))}).guid=e.guid=e.guid||C.guid++,i)},C.holdReady=function(e){e?C.readyWait++:C.ready(!0)},C.isArray=Array.isArray,C.parseJSON=JSON.parse,C.nodeName=A,C.isFunction=g,C.isWindow=v,C.camelCase=ee,C.type=b,C.now=Date.now,C.isNumeric=function(e){// As of jQuery 3.0, isNumeric is limited to
// strings and numbers (primitives or objects)
// that can be coerced to finite numbers (gh-2662)
var t=C.type(e);return("number"===t||"string"===t)&&// parseFloat NaNs numeric-cast false positives ("")
// ...but misinterprets leading-number strings, particularly hex literals ("0x...")
// subtraction forces infinities to NaN
!isNaN(e-parseFloat(e))},C.trim=function(e){return null==e?"":(e+"").replace(tR,"$1")},"function"==typeof define&&define.amd&&define("jquery",[],function(){return C});var t_=e.jQuery,tF=e.$;return C.noConflict=function(t){return e.$===C&&(e.$=tF),t&&e.jQuery===C&&(e.jQuery=t_),C},void 0===t&&(e.jQuery=e.$=C),C},"object"==typeof i?// is present, execute the factory and get jQuery.
// For environments that do not have a `window` with a `document`
// (such as Node.js), expose a factory as module.exports.
// This accentuates the need for the creation of a real `window`.
// e.g. var jQuery = require("jquery")(window);
// See ticket trac-14549 for more info.
i=t.document?n(t,!0):function(e){if(!e.document)throw Error("jQuery requires a window with a document");return n(e)}:n(t);var o=class{constructor(t){this.$main=/*@__PURE__*/e(i)(t)}get $(){return this.$main}bindPublicMethods(...e){let t=this;e.forEach(function(e){if("function"!=typeof t[e])throw"Method not found: "+e;t[e]=t[e].bind(t)})}},a=class extends o{constructor(e){super(e),this.bindPublicMethods("initSoundCheck")}initSoundCheck(){let t=/*@__PURE__*/e(i)("#ring");this.hasNewQueueId()&&t.get(0).play();let n=this.getCalledQueueIds();this.writeCalledQueueIds(n)}hasNewQueueId(){let t=this.getCalledQueueIds(),n=window.bo.zmscalldisplay.queue.calledIds;return 0</*@__PURE__*/e(i)(t).not(n).get().length}getCalledQueueIds(){let t=[];return /*@__PURE__*/e(i)("#queueImport td.wartenummer span[data-status]").each(function(){("called"==/*@__PURE__*/e(i)(this).attr("data-status")||"pickup"==/*@__PURE__*/e(i)(this).attr("data-status"))&&t.push(/*@__PURE__*/e(i)(this).attr("data-appointment"))}),t.sort((e,t)=>e-t).join(","),t}writeCalledQueueIds(e){window.bo.zmscalldisplay.queue.calledIds=e}},s=class extends o{constructor(t){super(t),this.bindPublicMethods("initRequest","setInterval"),console.log("Found queueList container"),/*@__PURE__*/e(i)(window).on("load",()=>{this.initRequest()}),/*@__PURE__*/e(i).ajaxSetup({cache:!1})}initRequest(){let t={type:"POST",url:this.getUrl("/queue/"),data:window.bo.zmscalldisplay,timeout:1e3*window.bo.zmscalldisplay.reloadInterval-100};/*@__PURE__*/e(i).ajax(t).done(t=>{this.hideMessages(0),/*@__PURE__*/e(i)("#queueImport").html(t),new a().initSoundCheck(),this.getDestinationToNumber()}).fail(function(){/*@__PURE__*/e(i)(".fatal").show()}),this.setInterval()}setInterval(){var e=window.bo.zmscalldisplay.reloadInterval;setTimeout(this.initRequest,1e3*e)}getUrl(e){return window.bo.zmscalldisplay.includepath+e}getDestinationToNumber(){window.bo.zmscalldisplay.queue.showOnlyNumeric&&/*@__PURE__*/e(i)("#queueImport .destination").each(function(){let t=/*@__PURE__*/e(i)(this).text();/\d/g.test(t)&&/*@__PURE__*/e(i)(this).text(t.replace(/\D/g,""))})}hideMessages(t=5e3){let n=/*@__PURE__*/e(i).find("[data-hide-message]");n.length&&setTimeout(()=>{/*@__PURE__*/e(i)(n).fadeOut()},t)}},u=class{constructor(){setInterval(this.setClock,1e3),this.setDate()}setClock(){let e=new Date,t=document.querySelector(".second-hand"),n=document.querySelector(".min-hand"),r=document.querySelector(".hour-hand"),i=e.getSeconds();t.style.transform=`rotate(${i/60*360+90}deg)`;let o=e.getMinutes();n.style.transform=`rotate(${o/60*360+i/60*6+90}deg)`;let a=e.getHours();r.style.transform=`rotate(${a/12*360+o/60*30+90}deg)`}setDate(){let e=document.querySelector("#aufrufanzeige_Datum"),t=new Date;e.innerHTML=new Intl.DateTimeFormat("de-DE",{weekday:"long",year:"numeric",month:"2-digit",day:"2-digit"}).format(t)}},l=class extends o{constructor(t){super(t),this.bindPublicMethods("load","setInterval"),console.log("Found waitingInfo container"),/*@__PURE__*/e(i)(window).on("load",()=>{this.load()}),/*@__PURE__*/e(i).ajaxSetup({cache:!1})}load(){new u;let t={type:"POST",url:this.getUrl("/waitinginfo/"),data:window.bo.zmscalldisplay,timeout:1e3*window.bo.zmscalldisplay.queue.timeWaitingInfo-100};/*@__PURE__*/e(i).ajax(t).done(e=>{this.hideMessages(0),this.setWaitingClients(e),this.setWaitingTime(e)}).fail(function(){/*@__PURE__*/e(i)(".fatal").show()}),this.setInterval()}setInterval(){var e=window.bo.zmscalldisplay.queue.timeWaitingInfo;setTimeout(this.load,1e3*e)}getUrl(e){return window.bo.zmscalldisplay.includepath+e}setWaitingClients(t){var n=/*@__PURE__*/e(i)(t).filter("div#waitingClients").text();0<=n&&/*@__PURE__*/e(i)("#wartende").html(n)}setWaitingTime(t){var n=/*@__PURE__*/e(i)(t).filter("div#waitingTime").text();/*@__PURE__*/e(i)("#wartezeit").html(n)}hideMessages(t=5e3){let n=/*@__PURE__*/e(i).find("[data-hide-message]");n.length&&setTimeout(()=>{/*@__PURE__*/e(i)(n).fadeOut()},t)}},c={},f=function(){//---------------------------------------------------------------------
// qrcode
//---------------------------------------------------------------------
/**
   * qrcode
   * @param typeNumber 1 to 40
   * @param errorCorrectionLevel 'L','M','Q','H'
   */var e,t,n,r,i,o,a,s=function(e,t){var n=e,r=l[t],i=null,o=0,a=null,s=[],u={},c=function(e,t){i=function(e){for(var t=Array(e),n=0;n<e;n+=1){t[n]=Array(e);for(var r=0;r<e;r+=1)t[n][r]=null}return t}(o=4*n+17),d(0,0),d(o-7,0),d(0,o-7),T(),w(),A(e,t),n>=7&&C(e),null==a&&(a=D(n,r,s)),E(a,t)},d=function(e,t){for(var n=-1;n<=7;n+=1)if(!(e+n<=-1)&&!(o<=e+n))for(var r=-1;r<=7;r+=1)t+r<=-1||o<=t+r||(0<=n&&n<=6&&(0==r||6==r)||0<=r&&r<=6&&(0==n||6==n)||2<=n&&n<=4&&2<=r&&r<=4?i[e+n][t+r]=!0:i[e+n][t+r]=!1)},b=function(){for(var e=0,t=0,n=0;n<8;n+=1){c(!0,n);var r=f.getLostPoint(u);(0==n||e>r)&&(e=r,t=n)}return t},w=function(){for(var e=8;e<o-8;e+=1)null==i[e][6]&&(i[e][6]=e%2==0);for(var t=8;t<o-8;t+=1)null==i[6][t]&&(i[6][t]=t%2==0)},T=function(){for(var e=f.getPatternPosition(n),t=0;t<e.length;t+=1)for(var r=0;r<e.length;r+=1){var o=e[t],a=e[r];if(null==i[o][a])for(var s=-2;s<=2;s+=1)for(var u=-2;u<=2;u+=1)-2==s||2==s||-2==u||2==u||0==s&&0==u?i[o+s][a+u]=!0:i[o+s][a+u]=!1}},C=function(e){for(var t=f.getBCHTypeNumber(n),r=0;r<18;r+=1){var a=!e&&(t>>r&1)==1;i[Math.floor(r/3)][r%3+o-8-3]=a}for(var r=0;r<18;r+=1){var a=!e&&(t>>r&1)==1;i[r%3+o-8-3][Math.floor(r/3)]=a}},A=function(e,t){// vertical
for(var n=r<<3|t,a=f.getBCHTypeInfo(n),s=0;s<15;s+=1){var u=!e&&(a>>s&1)==1;s<6?i[s][8]=u:s<8?i[s+1][8]=u:i[o-15+s][8]=u}// horizontal
for(var s=0;s<15;s+=1){var u=!e&&(a>>s&1)==1;s<8?i[8][o-s-1]=u:s<9?i[8][15-s-1+1]=u:i[8][15-s-1]=u}// fixed module
i[o-8][8]=!e},E=function(e,t){for(var n=-1,r=o-1,a=7,s=0,u=f.getMaskFunction(t),l=o-1;l>0;l-=2)for(6==l&&(l-=1);;){for(var c=0;c<2;c+=1)if(null==i[r][l-c]){var d=!1;s<e.length&&(d=(e[s]>>>a&1)==1),u(r,l-c)&&(d=!d),i[r][l-c]=d,-1==(a-=1)&&(s+=1,a=7)}if((r+=n)<0||o<=r){r-=n,n=-n;break}}},S=function(e,t){for(var n=0,r=0,i=0,o=Array(t.length),a=Array(t.length),s=0;s<t.length;s+=1){var u=t[s].dataCount,l=t[s].totalCount-u;r=Math.max(r,u),i=Math.max(i,l),o[s]=Array(u);for(var c=0;c<o[s].length;c+=1)o[s][c]=255&e.getBuffer()[c+n];n+=u;var d=f.getErrorCorrectPolynomial(l),h=p(o[s],d.getLength()-1).mod(d);a[s]=Array(d.getLength()-1);for(var c=0;c<a[s].length;c+=1){var g=c+h.getLength()-a[s].length;a[s][c]=g>=0?h.getAt(g):0}}for(var v=0,c=0;c<t.length;c+=1)v+=t[c].totalCount;for(var y=Array(v),m=0,c=0;c<r;c+=1)for(var s=0;s<t.length;s+=1)c<o[s].length&&(y[m]=o[s][c],m+=1);for(var c=0;c<i;c+=1)for(var s=0;s<t.length;s+=1)c<a[s].length&&(y[m]=a[s][c],m+=1);return y},D=function(e,t,n){for(var r=h.getRSBlocks(e,t),i=g(),o=0;o<n.length;o+=1){var a=n[o];i.put(a.getMode(),4),i.put(a.getLength(),f.getLengthInBits(a.getMode(),e)),a.write(i)}for(var s=0,o=0;o<r.length;o+=1)s+=r[o].dataCount;if(i.getLengthInBits()>8*s)throw"code length overflow. ("+i.getLengthInBits()+">"+8*s+")";// padding
for(i.getLengthInBits()+4<=8*s&&i.put(0,4);i.getLengthInBits()%8!=0;)i.putBit(!1);// padding
for(;!(i.getLengthInBits()>=8*s)&&(i.put(236,8),!(i.getLengthInBits()>=8*s));)i.put(17,8);return S(i,r)};u.addData=function(e,t){var n=null;switch(t=t||"Byte"){case"Numeric":n=v(e);break;case"Alphanumeric":n=y(e);break;case"Byte":n=m(e);break;case"Kanji":n=x(e);break;default:throw"mode:"+t}s.push(n),a=null},u.isDark=function(e,t){if(e<0||o<=e||t<0||o<=t)throw e+","+t;return i[e][t]},u.getModuleCount=function(){return o},u.make=function(){if(n<1){for(var e=1;e<40;e++){for(var t=h.getRSBlocks(e,r),i=g(),o=0;o<s.length;o++){var a=s[o];i.put(a.getMode(),4),i.put(a.getLength(),f.getLengthInBits(a.getMode(),e)),a.write(i)}for(var u=0,o=0;o<t.length;o++)u+=t[o].dataCount;if(i.getLengthInBits()<=8*u)break}n=e}c(!1,b())},u.createTableTag=function(e,t){e=e||2;var n="";n+='<table style=" border-width: 0px; border-style: none; border-collapse: collapse; padding: 0px; margin: '+(t=void 0===t?4*e:t)+'px;"><tbody>';for(var r=0;r<u.getModuleCount();r+=1){n+="<tr>";for(var i=0;i<u.getModuleCount();i+=1)n+='<td style=" border-width: 0px; border-style: none; border-collapse: collapse; padding: 0px; margin: 0px; width: '+e+"px; height: "+e+"px; background-color: "+(u.isDark(r,i)?"#000000":"#ffffff")+';"/>';n+="</tr>"}return n+"</tbody></table>"},u.createSvgTag=function(e,t,n,r){var i={};"object"==typeof arguments[0]&&(// Called by options.
i=arguments[0],// overwrite cellSize and margin.
e=i.cellSize,t=i.margin,n=i.alt,r=i.title),e=e||2,t=void 0===t?4*e:t,// Compose alt property surrogate
(n="string"==typeof n?{text:n}:n||{}).text=n.text||null,n.id=n.text?n.id||"qrcode-description":null,// Compose title property surrogate
(r="string"==typeof r?{text:r}:r||{}).text=r.text||null,r.id=r.text?r.id||"qrcode-title":null;var o,a,s,l,c=u.getModuleCount()*e+2*t,f="";for(l="l"+e+",0 0,"+e+" -"+e+",0 0,-"+e+"z ",f+='<svg version="1.1" xmlns="http://www.w3.org/2000/svg"'+(i.scalable?"":' width="'+c+'px" height="'+c+'px"')+(' viewBox="0 0 '+c+" ")+c+'"  preserveAspectRatio="xMinYMin meet"'+(r.text||n.text?' role="img" aria-labelledby="'+N([r.id,n.id].join(" ").trim())+'"':"")+">"+(r.text?'<title id="'+N(r.id)+'">'+N(r.text)+"</title>":"")+(n.text?'<description id="'+N(n.id)+'">'+N(n.text)+"</description>":"")+'<rect width="100%" height="100%" fill="white" cx="0" cy="0"/><path d="',a=0;a<u.getModuleCount();a+=1)for(o=0,s=a*e+t;o<u.getModuleCount();o+=1)u.isDark(a,o)&&(f+="M"+(o*e+t)+","+s+l);return f+'" stroke="transparent" fill="black"/></svg>'},u.createDataURL=function(e,t){e=e||2,t=void 0===t?4*e:t;var n=u.getModuleCount()*e+2*t,r=t,i=n-t;return k(n,n,function(t,n){if(!(r<=t)||!(t<i)||!(r<=n)||!(n<i))return 1;var o=Math.floor((t-r)/e),a=Math.floor((n-r)/e);return u.isDark(a,o)?0:1})},u.createImgTag=function(e,t,n){e=e||2,t=void 0===t?4*e:t;var r=u.getModuleCount()*e+2*t,i="";return i+='<img src="'+u.createDataURL(e,t)+'" width="'+r+'" height="'+r+'"',n&&(i+=' alt="'+N(n)+'"'),i+="/>"};var N=function(e){for(var t="",n=0;n<e.length;n+=1){var r=e.charAt(n);switch(r){case"<":t+="&lt;";break;case">":t+="&gt;";break;case"&":t+="&amp;";break;case'"':t+="&quot;";break;default:t+=r}}return t},M=function(e){e=void 0===e?2:e;var t,n,r,i,o,a=1*u.getModuleCount()+2*e,s=e,l=a-e,c={"██":"█","█ ":"▀"," █":"▄","  ":" "},f={"██":"▀","█ ":"▀"," █":" ","  ":" "},d="";for(t=0;t<a;t+=2){for(n=0,r=Math.floor((t-s)/1),i=Math.floor((t+1-s)/1);n<a;n+=1)o="█",s<=n&&n<l&&s<=t&&t<l&&u.isDark(r,Math.floor((n-s)/1))&&(o=" "),s<=n&&n<l&&s<=t+1&&t+1<l&&u.isDark(i,Math.floor((n-s)/1))?o+=" ":o+="█",// Output 2 characters per pixel, to create full square. 1 character per pixels gives only half width of square.
d+=e<1&&t+1>=l?f[o]:c[o];d+="\n"}return a%2&&e>0?d.substring(0,d.length-a-1)+Array(a+1).join("▀"):d.substring(0,d.length-1)};return u.createASCII=function(e,t){if((e=e||1)<2)return M(t);e-=1,t=void 0===t?2*e:t;var n,r,i,o,a=u.getModuleCount()*e+2*t,s=t,l=a-t,c=Array(e+1).join("██"),f=Array(e+1).join("  "),d="",p="";for(n=0;n<a;n+=1){for(r=0,i=Math.floor((n-s)/e),p="";r<a;r+=1)o=1,s<=r&&r<l&&s<=n&&n<l&&u.isDark(i,Math.floor((r-s)/e))&&(o=0),// Output 2 characters per pixel, to create full square. 1 character per pixels gives only half width of square.
p+=o?c:f;for(i=0;i<e;i+=1)d+=p+"\n"}return d.substring(0,d.length-1)},u.renderTo2dContext=function(e,t){t=t||2;for(var n=u.getModuleCount(),r=0;r<n;r++)for(var i=0;i<n;i++)e.fillStyle=u.isDark(r,i)?"black":"white",e.fillRect(r*t,i*t,t,t)},u};//---------------------------------------------------------------------
// qrcode.stringToBytes
//---------------------------------------------------------------------
s.stringToBytesFuncs={default:function(e){for(var t=[],n=0;n<e.length;n+=1){var r=e.charCodeAt(n);t.push(255&r)}return t}},s.stringToBytes=s.stringToBytesFuncs.default,//---------------------------------------------------------------------
// qrcode.createStringToBytes
//---------------------------------------------------------------------
/**
   * @param unicodeData base64 string of byte array.
   * [16bit Unicode],[16bit Bytes], ...
   * @param numChars
   */s.createStringToBytes=function(e,t){// create conversion map.
var n=function(){for(var n=T(e),r=function(){var e=n.read();if(-1==e)throw"eof";return e},i=0,o={};;){var a=n.read();if(-1==a)break;var s=r(),u=r(),l=r(),c=String.fromCharCode(a<<8|s),f=u<<8|l;o[c]=f,i+=1}if(i!=t)throw i+" != "+t;return o}();return function(e){for(var t=[],r=0;r<e.length;r+=1){var i=e.charCodeAt(r);if(i<128)t.push(i);else{var o=n[e.charAt(r)];"number"==typeof o?(255&o)==o?t.push(o):(// 2bytes
t.push(o>>>8),t.push(255&o)):t.push(63)}}return t}};//---------------------------------------------------------------------
// QRMode
//---------------------------------------------------------------------
var u={MODE_NUMBER:1,MODE_ALPHA_NUM:2,MODE_8BIT_BYTE:4,MODE_KANJI:8},l={L:1,M:0,Q:3,H:2},c={PATTERN000:0,PATTERN001:1,PATTERN010:2,PATTERN011:3,PATTERN100:4,PATTERN101:5,PATTERN110:6,PATTERN111:7},f=(e=[[],[6,18],[6,22],[6,26],[6,30],[6,34],[6,22,38],[6,24,42],[6,26,46],[6,28,50],[6,30,54],[6,32,58],[6,34,62],[6,26,46,66],[6,26,48,70],[6,26,50,74],[6,30,54,78],[6,30,56,82],[6,30,58,86],[6,34,62,90],[6,28,50,72,94],[6,26,50,74,98],[6,30,54,78,102],[6,28,54,80,106],[6,32,58,84,110],[6,30,58,86,114],[6,34,62,90,118],[6,26,50,74,98,122],[6,30,54,78,102,126],[6,26,52,78,104,130],[6,30,56,82,108,134],[6,34,60,86,112,138],[6,30,58,86,114,142],[6,34,62,90,118,146],[6,30,54,78,102,126,150],[6,24,50,76,102,128,154],[6,28,54,80,106,132,158],[6,32,58,84,110,136,162],[6,26,54,82,110,138,166],[6,30,58,86,114,142,170]],t={},n=function(e){for(var t=0;0!=e;)t+=1,e>>>=1;return t},t.getBCHTypeInfo=function(e){for(var t=e<<10;n(t)-n(1335)>=0;)t^=1335<<n(t)-n(1335);return(e<<10|t)^21522},t.getBCHTypeNumber=function(e){for(var t=e<<12;n(t)-n(7973)>=0;)t^=7973<<n(t)-n(7973);return e<<12|t},t.getPatternPosition=function(t){return e[t-1]},t.getMaskFunction=function(e){switch(e){case c.PATTERN000:return function(e,t){return(e+t)%2==0};case c.PATTERN001:return function(e,t){return e%2==0};case c.PATTERN010:return function(e,t){return t%3==0};case c.PATTERN011:return function(e,t){return(e+t)%3==0};case c.PATTERN100:return function(e,t){return(Math.floor(e/2)+Math.floor(t/3))%2==0};case c.PATTERN101:return function(e,t){return e*t%2+e*t%3==0};case c.PATTERN110:return function(e,t){return(e*t%2+e*t%3)%2==0};case c.PATTERN111:return function(e,t){return(e*t%3+(e+t)%2)%2==0};default:throw"bad maskPattern:"+e}},t.getErrorCorrectPolynomial=function(e){for(var t=p([1],0),n=0;n<e;n+=1)t=t.multiply(p([1,d.gexp(n)],0));return t},t.getLengthInBits=function(e,t){if(1<=t&&t<10)switch(e){case u.MODE_NUMBER:return 10;case u.MODE_ALPHA_NUM:return 9;case u.MODE_8BIT_BYTE:case u.MODE_KANJI:return 8;default:throw"mode:"+e}else if(t<27)switch(e){case u.MODE_NUMBER:return 12;case u.MODE_ALPHA_NUM:return 11;case u.MODE_8BIT_BYTE:return 16;case u.MODE_KANJI:return 10;default:throw"mode:"+e}else if(t<41)switch(e){case u.MODE_NUMBER:return 14;case u.MODE_ALPHA_NUM:return 13;case u.MODE_8BIT_BYTE:return 16;case u.MODE_KANJI:return 12;default:throw"mode:"+e}else throw"type:"+t},t.getLostPoint=function(e){// LEVEL1
for(var t=e.getModuleCount(),n=0,r=0;r<t;r+=1)for(var i=0;i<t;i+=1){for(var o=0,a=e.isDark(r,i),s=-1;s<=1;s+=1)if(!(r+s<0)&&!(t<=r+s))for(var u=-1;u<=1;u+=1)i+u<0||t<=i+u||0==s&&0==u||a!=e.isDark(r+s,i+u)||(o+=1);o>5&&(n+=3+o-5)}// LEVEL2
for(var r=0;r<t-1;r+=1)for(var i=0;i<t-1;i+=1){var l=0;e.isDark(r,i)&&(l+=1),e.isDark(r+1,i)&&(l+=1),e.isDark(r,i+1)&&(l+=1),e.isDark(r+1,i+1)&&(l+=1),(0==l||4==l)&&(n+=3)}// LEVEL3
for(var r=0;r<t;r+=1)for(var i=0;i<t-6;i+=1)e.isDark(r,i)&&!e.isDark(r,i+1)&&e.isDark(r,i+2)&&e.isDark(r,i+3)&&e.isDark(r,i+4)&&!e.isDark(r,i+5)&&e.isDark(r,i+6)&&(n+=40);for(var i=0;i<t;i+=1)for(var r=0;r<t-6;r+=1)e.isDark(r,i)&&!e.isDark(r+1,i)&&e.isDark(r+2,i)&&e.isDark(r+3,i)&&e.isDark(r+4,i)&&!e.isDark(r+5,i)&&e.isDark(r+6,i)&&(n+=40);for(var c=0,i=0;i<t;i+=1)for(var r=0;r<t;r+=1)e.isDark(r,i)&&(c+=1);return n+10*(Math.abs(100*c/t/t-50)/5)},t),d=function(){// initialize tables
for(var e=Array(256),t=Array(256),n=0;n<8;n+=1)e[n]=1<<n;for(var n=8;n<256;n+=1)e[n]=e[n-4]^e[n-5]^e[n-6]^e[n-8];for(var n=0;n<255;n+=1)t[e[n]]=n;var r={};return r.glog=function(e){if(e<1)throw"glog("+e+")";return t[e]},r.gexp=function(t){for(;t<0;)t+=255;for(;t>=256;)t-=255;return e[t]},r}();//---------------------------------------------------------------------
// qrPolynomial
//---------------------------------------------------------------------
function p(e,t){if(void 0===e.length)throw e.length+"/"+t;var n=function(){for(var n=0;n<e.length&&0==e[n];)n+=1;for(var r=Array(e.length-n+t),i=0;i<e.length-n;i+=1)r[i]=e[i+n];return r}(),r={};return r.getAt=function(e){return n[e]},r.getLength=function(){return n.length},r.multiply=function(e){for(var t=Array(r.getLength()+e.getLength()-1),n=0;n<r.getLength();n+=1)for(var i=0;i<e.getLength();i+=1)t[n+i]^=d.gexp(d.glog(r.getAt(n))+d.glog(e.getAt(i)));return p(t,0)},r.mod=function(e){if(r.getLength()-e.getLength()<0)return r;for(var t=d.glog(r.getAt(0))-d.glog(e.getAt(0)),n=Array(r.getLength()),i=0;i<r.getLength();i+=1)n[i]=r.getAt(i);for(var i=0;i<e.getLength();i+=1)n[i]^=d.gexp(d.glog(e.getAt(i))+t);// recursive call
return p(n,0).mod(e)},r}//---------------------------------------------------------------------
// QRRSBlock
//---------------------------------------------------------------------
var h=(r=[// L
// M
// Q
// H
// 1
[1,26,19],[1,26,16],[1,26,13],[1,26,9],// 2
[1,44,34],[1,44,28],[1,44,22],[1,44,16],// 3
[1,70,55],[1,70,44],[2,35,17],[2,35,13],// 4
[1,100,80],[2,50,32],[2,50,24],[4,25,9],// 5
[1,134,108],[2,67,43],[2,33,15,2,34,16],[2,33,11,2,34,12],// 6
[2,86,68],[4,43,27],[4,43,19],[4,43,15],// 7
[2,98,78],[4,49,31],[2,32,14,4,33,15],[4,39,13,1,40,14],// 8
[2,121,97],[2,60,38,2,61,39],[4,40,18,2,41,19],[4,40,14,2,41,15],// 9
[2,146,116],[3,58,36,2,59,37],[4,36,16,4,37,17],[4,36,12,4,37,13],// 10
[2,86,68,2,87,69],[4,69,43,1,70,44],[6,43,19,2,44,20],[6,43,15,2,44,16],// 11
[4,101,81],[1,80,50,4,81,51],[4,50,22,4,51,23],[3,36,12,8,37,13],// 12
[2,116,92,2,117,93],[6,58,36,2,59,37],[4,46,20,6,47,21],[7,42,14,4,43,15],// 13
[4,133,107],[8,59,37,1,60,38],[8,44,20,4,45,21],[12,33,11,4,34,12],// 14
[3,145,115,1,146,116],[4,64,40,5,65,41],[11,36,16,5,37,17],[11,36,12,5,37,13],// 15
[5,109,87,1,110,88],[5,65,41,5,66,42],[5,54,24,7,55,25],[11,36,12,7,37,13],// 16
[5,122,98,1,123,99],[7,73,45,3,74,46],[15,43,19,2,44,20],[3,45,15,13,46,16],// 17
[1,135,107,5,136,108],[10,74,46,1,75,47],[1,50,22,15,51,23],[2,42,14,17,43,15],// 18
[5,150,120,1,151,121],[9,69,43,4,70,44],[17,50,22,1,51,23],[2,42,14,19,43,15],// 19
[3,141,113,4,142,114],[3,70,44,11,71,45],[17,47,21,4,48,22],[9,39,13,16,40,14],// 20
[3,135,107,5,136,108],[3,67,41,13,68,42],[15,54,24,5,55,25],[15,43,15,10,44,16],// 21
[4,144,116,4,145,117],[17,68,42],[17,50,22,6,51,23],[19,46,16,6,47,17],// 22
[2,139,111,7,140,112],[17,74,46],[7,54,24,16,55,25],[34,37,13],// 23
[4,151,121,5,152,122],[4,75,47,14,76,48],[11,54,24,14,55,25],[16,45,15,14,46,16],// 24
[6,147,117,4,148,118],[6,73,45,14,74,46],[11,54,24,16,55,25],[30,46,16,2,47,17],// 25
[8,132,106,4,133,107],[8,75,47,13,76,48],[7,54,24,22,55,25],[22,45,15,13,46,16],// 26
[10,142,114,2,143,115],[19,74,46,4,75,47],[28,50,22,6,51,23],[33,46,16,4,47,17],// 27
[8,152,122,4,153,123],[22,73,45,3,74,46],[8,53,23,26,54,24],[12,45,15,28,46,16],// 28
[3,147,117,10,148,118],[3,73,45,23,74,46],[4,54,24,31,55,25],[11,45,15,31,46,16],// 29
[7,146,116,7,147,117],[21,73,45,7,74,46],[1,53,23,37,54,24],[19,45,15,26,46,16],// 30
[5,145,115,10,146,116],[19,75,47,10,76,48],[15,54,24,25,55,25],[23,45,15,25,46,16],// 31
[13,145,115,3,146,116],[2,74,46,29,75,47],[42,54,24,1,55,25],[23,45,15,28,46,16],// 32
[17,145,115],[10,74,46,23,75,47],[10,54,24,35,55,25],[19,45,15,35,46,16],// 33
[17,145,115,1,146,116],[14,74,46,21,75,47],[29,54,24,19,55,25],[11,45,15,46,46,16],// 34
[13,145,115,6,146,116],[14,74,46,23,75,47],[44,54,24,7,55,25],[59,46,16,1,47,17],// 35
[12,151,121,7,152,122],[12,75,47,26,76,48],[39,54,24,14,55,25],[22,45,15,41,46,16],// 36
[6,151,121,14,152,122],[6,75,47,34,76,48],[46,54,24,10,55,25],[2,45,15,64,46,16],// 37
[17,152,122,4,153,123],[29,74,46,14,75,47],[49,54,24,10,55,25],[24,45,15,46,46,16],// 38
[4,152,122,18,153,123],[13,74,46,32,75,47],[48,54,24,14,55,25],[42,45,15,32,46,16],// 39
[20,147,117,4,148,118],[40,75,47,7,76,48],[43,54,24,22,55,25],[10,45,15,67,46,16],// 40
[19,148,118,6,149,119],[18,75,47,31,76,48],[34,54,24,34,55,25],[20,45,15,61,46,16]],i=function(e,t){var n={};return n.totalCount=e,n.dataCount=t,n},o={},a=function(e,t){switch(t){case l.L:return r[(e-1)*4+0];case l.M:return r[(e-1)*4+1];case l.Q:return r[(e-1)*4+2];case l.H:return r[(e-1)*4+3];default:return}},o.getRSBlocks=function(e,t){var n=a(e,t);if(void 0===n)throw"bad rs block @ typeNumber:"+e+"/errorCorrectionLevel:"+t;for(var r=n.length/3,o=[],s=0;s<r;s+=1)for(var u=n[3*s+0],l=n[3*s+1],c=n[3*s+2],f=0;f<u;f+=1)o.push(i(l,c));return o},o),g=function(){var e=[],t=0,n={};return n.getBuffer=function(){return e},n.getAt=function(t){return(e[Math.floor(t/8)]>>>7-t%8&1)==1},n.put=function(e,t){for(var r=0;r<t;r+=1)n.putBit((e>>>t-r-1&1)==1)},n.getLengthInBits=function(){return t},n.putBit=function(n){var r=Math.floor(t/8);e.length<=r&&e.push(0),n&&(e[r]|=128>>>t%8),t+=1},n},v=function(e){var t=u.MODE_NUMBER,n={};n.getMode=function(){return t},n.getLength=function(t){return e.length},n.write=function(t){for(var n=0;n+2<e.length;)t.put(r(e.substring(n,n+3)),10),n+=3;n<e.length&&(e.length-n==1?t.put(r(e.substring(n,n+1)),4):e.length-n==2&&t.put(r(e.substring(n,n+2)),7))};var r=function(e){for(var t=0,n=0;n<e.length;n+=1)t=10*t+i(e.charAt(n));return t},i=function(e){if("0"<=e&&e<="9")return e.charCodeAt(0)-48;throw"illegal char :"+e};return n},y=function(e){var t=u.MODE_ALPHA_NUM,n={};n.getMode=function(){return t},n.getLength=function(t){return e.length},n.write=function(t){for(var n=0;n+1<e.length;)t.put(45*r(e.charAt(n))+r(e.charAt(n+1)),11),n+=2;n<e.length&&t.put(r(e.charAt(n)),6)};var r=function(e){if("0"<=e&&e<="9")return e.charCodeAt(0)-48;if("A"<=e&&e<="Z")return e.charCodeAt(0)-65+10;switch(e){case" ":return 36;case"$":return 37;case"%":return 38;case"*":return 39;case"+":return 40;case"-":return 41;case".":return 42;case"/":return 43;case":":return 44;default:throw"illegal char :"+e}};return n},m=function(e){var t=u.MODE_8BIT_BYTE,n=s.stringToBytes(e),r={};return r.getMode=function(){return t},r.getLength=function(e){return n.length},r.write=function(e){for(var t=0;t<n.length;t+=1)e.put(n[t],8)},r},x=function(e){var t=u.MODE_KANJI,n=s.stringToBytesFuncs.SJIS;if(!n)throw"sjis not supported.";!function(e,t){// self test for sjis support.
var r=n("友");if(2!=r.length||(r[0]<<8|r[1])!=38726)throw"sjis not supported."}(0,0);var r=n(e),i={};return i.getMode=function(){return t},i.getLength=function(e){return~~(r.length/2)},i.write=function(e){for(var t=0;t+1<r.length;){var n=(255&r[t])<<8|255&r[t+1];if(33088<=n&&n<=40956)n-=33088;else if(57408<=n&&n<=60351)n-=49472;else throw"illegal char at "+(t+1)+"/"+n;n=(n>>>8&255)*192+(255&n),e.put(n,13),t+=2}if(t<r.length)throw"illegal char at "+(t+1)},i},b=function(){var e=[],t={};return t.writeByte=function(t){e.push(255&t)},t.writeShort=function(e){t.writeByte(e),t.writeByte(e>>>8)},t.writeBytes=function(e,n,r){n=n||0,r=r||e.length;for(var i=0;i<r;i+=1)t.writeByte(e[i+n])},t.writeString=function(e){for(var n=0;n<e.length;n+=1)t.writeByte(e.charCodeAt(n))},t.toByteArray=function(){return e},t.toString=function(){var t="";t+="[";for(var n=0;n<e.length;n+=1)n>0&&(t+=","),t+=e[n];return t+"]"},t},w=function(){var e=0,t=0,n=0,r="",i={},o=function(e){r+=String.fromCharCode(a(63&e))},a=function(e){if(e<0);else if(e<26)return 65+e;else if(e<52)return 97+(e-26);else if(e<62)return 48+(e-52);else if(62==e)return 43;else if(63==e)return 47;throw"n:"+e};return i.writeByte=function(r){for(e=e<<8|255&r,t+=8,n+=1;t>=6;)o(e>>>t-6),t-=6},i.flush=function(){if(t>0&&(o(e<<6-t),e=0,t=0),n%3!=0)for(var i=3-n%3,a=0;a<i;a+=1)r+="="},i.toString=function(){return r},i},T=function(e){var t=0,n=0,r=0,i={};i.read=function(){for(;r<8;){if(t>=e.length){if(0==r)return -1;throw"unexpected end of file./"+r}var i=e.charAt(t);if(t+=1,"="==i)return r=0,-1;i.match(/^\s$/)||(n=n<<6|o(i.charCodeAt(0)),r+=6)}var a=n>>>r-8&255;return r-=8,a};var o=function(e){if(65<=e&&e<=90)return e-65;if(97<=e&&e<=122)return e-97+26;if(48<=e&&e<=57)return e-48+52;if(43==e)return 62;if(47==e)return 63;throw"c:"+e};return i},C=function(e,t){var n=Array(e*t),r={};r.setPixel=function(t,r,i){n[r*e+t]=i},r.write=function(n){//---------------------------------
// GIF Signature
n.writeString("GIF87a"),//---------------------------------
// Screen Descriptor
n.writeShort(e),n.writeShort(t),n.writeByte(128),n.writeByte(0),n.writeByte(0),//---------------------------------
// Global Color Map
// black
n.writeByte(0),n.writeByte(0),n.writeByte(0),// white
n.writeByte(255),n.writeByte(255),n.writeByte(255),//---------------------------------
// Image Descriptor
n.writeString(","),n.writeShort(0),n.writeShort(0),n.writeShort(e),n.writeShort(t),n.writeByte(0);var r=o(2);n.writeByte(2);for(var i=0;r.length-i>255;)n.writeByte(255),n.writeBytes(r,i,255),i+=255;n.writeByte(r.length-i),n.writeBytes(r,i,r.length-i),n.writeByte(0),//---------------------------------
// GIF Terminator
n.writeString(";")};var i=function(e){var t=0,n=0,r={};return r.write=function(r,i){if(r>>>i!=0)throw"length over";for(;t+i>=8;)e.writeByte(255&(r<<t|n)),i-=8-t,r>>>=8-t,n=0,t=0;n=r<<t|n,t+=i},r.flush=function(){t>0&&e.writeByte(n)},r},o=function(e){for(var t=1<<e,r=(1<<e)+1,o=e+1,s=a(),u=0;u<t;u+=1)s.add(String.fromCharCode(u));s.add(String.fromCharCode(t)),s.add(String.fromCharCode(r));var l=b(),c=i(l);// clear code
c.write(t,o);var f=0,d=String.fromCharCode(n[0]);for(f+=1;f<n.length;){var p=String.fromCharCode(n[f]);f+=1,s.contains(d+p)?d+=p:(c.write(s.indexOf(d),o),4095>s.size()&&(s.size()==1<<o&&(o+=1),s.add(d+p)),d=p)}return c.write(s.indexOf(d),o),// end code
c.write(r,o),c.flush(),l.toByteArray()},a=function(){var e={},t=0,n={};return n.add=function(r){if(n.contains(r))throw"dup key:"+r;e[r]=t,t+=1},n.size=function(){return t},n.indexOf=function(t){return e[t]},n.contains=function(t){return void 0!==e[t]},n};return r},k=function(e,t,n){for(var r=C(e,t),i=0;i<t;i+=1)for(var o=0;o<e;o+=1)r.setPixel(o,i,n(o,i));var a=b();r.write(a);for(var s=w(),u=a.toByteArray(),l=0;l<u.length;l+=1)s.writeByte(u[l]);return s.flush(),"data:image/gif;base64,"+s};//---------------------------------------------------------------------
// returns qrcode function.
return s}();f.stringToBytesFuncs["UTF-8"]=function(e){return(// http://stackoverflow.com/questions/18729405/how-to-convert-utf8-string-to-byte-array
function(e){for(var t=[],n=0;n<e.length;n++){var r=e.charCodeAt(n);r<128?t.push(r):r<2048?t.push(192|r>>6,128|63&r):r<55296||r>=57344?t.push(224|r>>12,128|r>>6&63,128|63&r):(n++,// UTF-16 encodes 0x10000-0x10FFFF by
// subtracting 0x10000 and splitting the
// 20 bits of 0x0-0xFFFFF into two halves
r=65536+((1023&r)<<10|1023&e.charCodeAt(n)),t.push(240|r>>18,128|r>>12&63,128|r>>6&63,128|63&r))}return t}(e))},r=function(){return f},"function"==typeof define&&define.amd?define([],r):c=r();var d=class extends o{constructor(t){super(t),this.bindPublicMethods("initQrCode"),console.log("Found qrCode"),/*@__PURE__*/e(i)(window).on("load",()=>{this.initQrCode()})}initQrCode(){let t=window.bo.zmscalldisplay.qrCode,n=this;/*@__PURE__*/e(i)("canvas.qrCode").each(function(r,o){"CANVAS"===o.nodeName&&(t.size=Math.min(o.offsetWidth,o.offsetHeight),t.text=window.location.origin+/*@__PURE__*/e(i)(o).data("text"),n.drawQrCode(o,t))})}drawQrCode(t,n){/*@__PURE__*/e(c).stringToBytes=/*@__PURE__*/e(c).stringToBytesFuncs["UTF-8"];let r=/*@__PURE__*/e(c)(n.version,n.ecLevel);r.addData(n.text),r.make();let i=t.getContext("2d");i.fillStyle=n.backgroundColor,i.fillRect(0,0,n.size,n.size);let o=r.getModuleCount(),a=n.size/(o+2*n.quiet),s=n.quiet*a;i.beginPath(),i.fillStyle=n.fillColor;for(let e=0;e<o;e+=1)for(let t=0;t<o;t+=1){let n=s+e*a,o=s+t*a;r.isDark(e,t)&&i.rect(o,n,a,a)}i.fill()}};window.bo={zmscalldisplay:{tableLayout:{multiColumns:1,maxResults:5,head:{left:"Nummer",right:"Platz"}},collections:{},queue:{calledIds:"",timeUntilOld:60,timeWaitingInfo:20,status:"called,pickup",showOnlyNumeric:!1},reloadInterval:10,animationEnd:"webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend",qrCode:{version:10,ecLevel:"M",fillColor:"#000",backgroundColor:"#fff",text:"no text",quiet:5}}},/*@__PURE__*/e(i)("#queueImport").each(function(){new s,new l}),new d,// Say hello
console.log("Welcome to the ZMS Calldisplay interface...")})();//# sourceMappingURL=index.js.map

//# sourceMappingURL=index.js.map
