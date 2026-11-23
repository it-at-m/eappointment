//#region ../mde-storybook/node_modules/@lit/reactive-element/css-tag.js
var e = globalThis, t = e.ShadowRoot && (e.ShadyCSS === void 0 || e.ShadyCSS.nativeShadow) && "adoptedStyleSheets" in Document.prototype && "replace" in CSSStyleSheet.prototype, n = Symbol(), r = /* @__PURE__ */ new WeakMap(), i = class {
	constructor(e, t, r) {
		if (this._$cssResult$ = !0, r !== n) throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");
		this.cssText = e, this.t = t;
	}
	get styleSheet() {
		let e = this.o, n = this.t;
		if (t && e === void 0) {
			let t = n !== void 0 && n.length === 1;
			t && (e = r.get(n)), e === void 0 && ((this.o = e = new CSSStyleSheet()).replaceSync(this.cssText), t && r.set(n, e));
		}
		return e;
	}
	toString() {
		return this.cssText;
	}
}, a = (e) => new i(typeof e == "string" ? e : e + "", void 0, n), o = (n, r) => {
	if (t) n.adoptedStyleSheets = r.map((e) => e instanceof CSSStyleSheet ? e : e.styleSheet);
	else for (let t of r) {
		let r = document.createElement("style"), i = e.litNonce;
		i !== void 0 && r.setAttribute("nonce", i), r.textContent = t.cssText, n.appendChild(r);
	}
}, s = t ? (e) => e : (e) => e instanceof CSSStyleSheet ? ((e) => {
	let t = "";
	for (let n of e.cssRules) t += n.cssText;
	return a(t);
})(e) : e, { is: c, defineProperty: l, getOwnPropertyDescriptor: u, getOwnPropertyNames: d, getOwnPropertySymbols: ee, getPrototypeOf: te } = Object, f = globalThis, ne = f.trustedTypes, re = ne ? ne.emptyScript : "", ie = f.reactiveElementPolyfillSupport, p = (e, t) => e, m = {
	toAttribute(e, t) {
		switch (t) {
			case Boolean:
				e = e ? re : null;
				break;
			case Object:
			case Array: e = e == null ? e : JSON.stringify(e);
		}
		return e;
	},
	fromAttribute(e, t) {
		let n = e;
		switch (t) {
			case Boolean:
				n = e !== null;
				break;
			case Number:
				n = e === null ? null : Number(e);
				break;
			case Object:
			case Array: try {
				n = JSON.parse(e);
			} catch {
				n = null;
			}
		}
		return n;
	}
}, h = (e, t) => !c(e, t), ae = {
	attribute: !0,
	type: String,
	converter: m,
	reflect: !1,
	useDefault: !1,
	hasChanged: h
};
Symbol.metadata ??= Symbol("metadata"), f.litPropertyMetadata ??= /* @__PURE__ */ new WeakMap();
var g = class extends HTMLElement {
	static addInitializer(e) {
		this._$Ei(), (this.l ??= []).push(e);
	}
	static get observedAttributes() {
		return this.finalize(), this._$Eh && [...this._$Eh.keys()];
	}
	static createProperty(e, t = ae) {
		if (t.state && (t.attribute = !1), this._$Ei(), this.prototype.hasOwnProperty(e) && ((t = Object.create(t)).wrapped = !0), this.elementProperties.set(e, t), !t.noAccessor) {
			let n = Symbol(), r = this.getPropertyDescriptor(e, n, t);
			r !== void 0 && l(this.prototype, e, r);
		}
	}
	static getPropertyDescriptor(e, t, n) {
		let { get: r, set: i } = u(this.prototype, e) ?? {
			get() {
				return this[t];
			},
			set(e) {
				this[t] = e;
			}
		};
		return {
			get: r,
			set(t) {
				let a = r?.call(this);
				i?.call(this, t), this.requestUpdate(e, a, n);
			},
			configurable: !0,
			enumerable: !0
		};
	}
	static getPropertyOptions(e) {
		return this.elementProperties.get(e) ?? ae;
	}
	static _$Ei() {
		if (this.hasOwnProperty(p("elementProperties"))) return;
		let e = te(this);
		e.finalize(), e.l !== void 0 && (this.l = [...e.l]), this.elementProperties = new Map(e.elementProperties);
	}
	static finalize() {
		if (this.hasOwnProperty(p("finalized"))) return;
		if (this.finalized = !0, this._$Ei(), this.hasOwnProperty(p("properties"))) {
			let e = this.properties, t = [...d(e), ...ee(e)];
			for (let n of t) this.createProperty(n, e[n]);
		}
		let e = this[Symbol.metadata];
		if (e !== null) {
			let t = litPropertyMetadata.get(e);
			if (t !== void 0) for (let [e, n] of t) this.elementProperties.set(e, n);
		}
		this._$Eh = /* @__PURE__ */ new Map();
		for (let [e, t] of this.elementProperties) {
			let n = this._$Eu(e, t);
			n !== void 0 && this._$Eh.set(n, e);
		}
		this.elementStyles = this.finalizeStyles(this.styles);
	}
	static finalizeStyles(e) {
		let t = [];
		if (Array.isArray(e)) {
			let n = new Set(e.flat(Infinity).reverse());
			for (let e of n) t.unshift(s(e));
		} else e !== void 0 && t.push(s(e));
		return t;
	}
	static _$Eu(e, t) {
		let n = t.attribute;
		return !1 === n ? void 0 : typeof n == "string" ? n : typeof e == "string" ? e.toLowerCase() : void 0;
	}
	constructor() {
		super(), this._$Ep = void 0, this.isUpdatePending = !1, this.hasUpdated = !1, this._$Em = null, this._$Ev();
	}
	_$Ev() {
		this._$ES = new Promise((e) => this.enableUpdating = e), this._$AL = /* @__PURE__ */ new Map(), this._$E_(), this.requestUpdate(), this.constructor.l?.forEach((e) => e(this));
	}
	addController(e) {
		(this._$EO ??= /* @__PURE__ */ new Set()).add(e), this.renderRoot !== void 0 && this.isConnected && e.hostConnected?.();
	}
	removeController(e) {
		this._$EO?.delete(e);
	}
	_$E_() {
		let e = /* @__PURE__ */ new Map(), t = this.constructor.elementProperties;
		for (let n of t.keys()) this.hasOwnProperty(n) && (e.set(n, this[n]), delete this[n]);
		e.size > 0 && (this._$Ep = e);
	}
	createRenderRoot() {
		let e = this.shadowRoot ?? this.attachShadow(this.constructor.shadowRootOptions);
		return o(e, this.constructor.elementStyles), e;
	}
	connectedCallback() {
		this.renderRoot ??= this.createRenderRoot(), this.enableUpdating(!0), this._$EO?.forEach((e) => e.hostConnected?.());
	}
	enableUpdating(e) {}
	disconnectedCallback() {
		this._$EO?.forEach((e) => e.hostDisconnected?.());
	}
	attributeChangedCallback(e, t, n) {
		this._$AK(e, n);
	}
	_$ET(e, t) {
		let n = this.constructor.elementProperties.get(e), r = this.constructor._$Eu(e, n);
		if (r !== void 0 && !0 === n.reflect) {
			let i = (n.converter?.toAttribute === void 0 ? m : n.converter).toAttribute(t, n.type);
			this._$Em = e, i == null ? this.removeAttribute(r) : this.setAttribute(r, i), this._$Em = null;
		}
	}
	_$AK(e, t) {
		let n = this.constructor, r = n._$Eh.get(e);
		if (r !== void 0 && this._$Em !== r) {
			let e = n.getPropertyOptions(r), i = typeof e.converter == "function" ? { fromAttribute: e.converter } : e.converter?.fromAttribute === void 0 ? m : e.converter;
			this._$Em = r;
			let a = i.fromAttribute(t, e.type);
			this[r] = a ?? this._$Ej?.get(r) ?? a, this._$Em = null;
		}
	}
	requestUpdate(e, t, n, r = !1, i) {
		if (e !== void 0) {
			let a = this.constructor;
			if (!1 === r && (i = this[e]), n ??= a.getPropertyOptions(e), !((n.hasChanged ?? h)(i, t) || n.useDefault && n.reflect && i === this._$Ej?.get(e) && !this.hasAttribute(a._$Eu(e, n)))) return;
			this.C(e, t, n);
		}
		!1 === this.isUpdatePending && (this._$ES = this._$EP());
	}
	C(e, t, { useDefault: n, reflect: r, wrapped: i }, a) {
		n && !(this._$Ej ??= /* @__PURE__ */ new Map()).has(e) && (this._$Ej.set(e, a ?? t ?? this[e]), !0 !== i || a !== void 0) || (this._$AL.has(e) || (this.hasUpdated || n || (t = void 0), this._$AL.set(e, t)), !0 === r && this._$Em !== e && (this._$Eq ??= /* @__PURE__ */ new Set()).add(e));
	}
	async _$EP() {
		this.isUpdatePending = !0;
		try {
			await this._$ES;
		} catch (e) {
			Promise.reject(e);
		}
		let e = this.scheduleUpdate();
		return e != null && await e, !this.isUpdatePending;
	}
	scheduleUpdate() {
		return this.performUpdate();
	}
	performUpdate() {
		if (!this.isUpdatePending) return;
		if (!this.hasUpdated) {
			if (this.renderRoot ??= this.createRenderRoot(), this._$Ep) {
				for (let [e, t] of this._$Ep) this[e] = t;
				this._$Ep = void 0;
			}
			let e = this.constructor.elementProperties;
			if (e.size > 0) for (let [t, n] of e) {
				let { wrapped: e } = n, r = this[t];
				!0 !== e || this._$AL.has(t) || r === void 0 || this.C(t, void 0, n, r);
			}
		}
		let e = !1, t = this._$AL;
		try {
			e = this.shouldUpdate(t), e ? (this.willUpdate(t), this._$EO?.forEach((e) => e.hostUpdate?.()), this.update(t)) : this._$EM();
		} catch (t) {
			throw e = !1, this._$EM(), t;
		}
		e && this._$AE(t);
	}
	willUpdate(e) {}
	_$AE(e) {
		this._$EO?.forEach((e) => e.hostUpdated?.()), this.hasUpdated || (this.hasUpdated = !0, this.firstUpdated(e)), this.updated(e);
	}
	_$EM() {
		this._$AL = /* @__PURE__ */ new Map(), this.isUpdatePending = !1;
	}
	get updateComplete() {
		return this.getUpdateComplete();
	}
	getUpdateComplete() {
		return this._$ES;
	}
	shouldUpdate(e) {
		return !0;
	}
	update(e) {
		this._$Eq &&= this._$Eq.forEach((e) => this._$ET(e, this[e])), this._$EM();
	}
	updated(e) {}
	firstUpdated(e) {}
};
g.elementStyles = [], g.shadowRootOptions = { mode: "open" }, g[p("elementProperties")] = /* @__PURE__ */ new Map(), g[p("finalized")] = /* @__PURE__ */ new Map(), ie?.({ ReactiveElement: g }), (f.reactiveElementVersions ??= []).push("2.1.2");
//#endregion
//#region ../mde-storybook/node_modules/lit-html/lit-html.js
var _ = globalThis, oe = (e) => e, v = _.trustedTypes, se = v ? v.createPolicy("lit-html", { createHTML: (e) => e }) : void 0, ce = "$lit$", y = `lit$${Math.random().toFixed(9).slice(2)}$`, le = "?" + y, ue = `<${le}>`, b = document, x = () => b.createComment(""), S = (e) => e === null || typeof e != "object" && typeof e != "function", C = Array.isArray, de = (e) => C(e) || typeof e?.[Symbol.iterator] == "function", w = "[ 	\n\f\r]", T = /<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g, fe = /-->/g, pe = />/g, E = RegExp(`>|${w}(?:([^\\s"'>=/]+)(${w}*=${w}*(?:[^ \t\n\f\r"'\`<>=]|("|')|))|$)`, "g"), D = /'/g, O = /"/g, k = /^(?:script|style|textarea|title)$/i, A = ((e) => (t, ...n) => ({
	_$litType$: e,
	strings: t,
	values: n
}))(1), j = Symbol.for("lit-noChange"), M = Symbol.for("lit-nothing"), me = /* @__PURE__ */ new WeakMap(), N = b.createTreeWalker(b, 129);
function he(e, t) {
	if (!C(e) || !e.hasOwnProperty("raw")) throw Error("invalid template strings array");
	return se === void 0 ? t : se.createHTML(t);
}
var ge = (e, t) => {
	let n = e.length - 1, r = [], i, a = t === 2 ? "<svg>" : t === 3 ? "<math>" : "", o = T;
	for (let t = 0; t < n; t++) {
		let n = e[t], s, c, l = -1, u = 0;
		for (; u < n.length && (o.lastIndex = u, c = o.exec(n), c !== null);) u = o.lastIndex, o === T ? c[1] === "!--" ? o = fe : c[1] === void 0 ? c[2] === void 0 ? c[3] !== void 0 && (o = E) : (k.test(c[2]) && (i = RegExp("</" + c[2], "g")), o = E) : o = pe : o === E ? c[0] === ">" ? (o = i ?? T, l = -1) : c[1] === void 0 ? l = -2 : (l = o.lastIndex - c[2].length, s = c[1], o = c[3] === void 0 ? E : c[3] === "\"" ? O : D) : o === O || o === D ? o = E : o === fe || o === pe ? o = T : (o = E, i = void 0);
		let d = o === E && e[t + 1].startsWith("/>") ? " " : "";
		a += o === T ? n + ue : l >= 0 ? (r.push(s), n.slice(0, l) + ce + n.slice(l) + y + d) : n + y + (l === -2 ? t : d);
	}
	return [he(e, a + (e[n] || "<?>") + (t === 2 ? "</svg>" : t === 3 ? "</math>" : "")), r];
}, P = class e {
	constructor({ strings: t, _$litType$: n }, r) {
		let i;
		this.parts = [];
		let a = 0, o = 0, s = t.length - 1, c = this.parts, [l, u] = ge(t, n);
		if (this.el = e.createElement(l, r), N.currentNode = this.el.content, n === 2 || n === 3) {
			let e = this.el.content.firstChild;
			e.replaceWith(...e.childNodes);
		}
		for (; (i = N.nextNode()) !== null && c.length < s;) {
			if (i.nodeType === 1) {
				if (i.hasAttributes()) for (let e of i.getAttributeNames()) if (e.endsWith(ce)) {
					let t = u[o++], n = i.getAttribute(e).split(y), r = /([.?@])?(.*)/.exec(t);
					c.push({
						type: 1,
						index: a,
						name: r[2],
						strings: n,
						ctor: r[1] === "." ? ve : r[1] === "?" ? ye : r[1] === "@" ? be : L
					}), i.removeAttribute(e);
				} else e.startsWith(y) && (c.push({
					type: 6,
					index: a
				}), i.removeAttribute(e));
				if (k.test(i.tagName)) {
					let e = i.textContent.split(y), t = e.length - 1;
					if (t > 0) {
						i.textContent = v ? v.emptyScript : "";
						for (let n = 0; n < t; n++) i.append(e[n], x()), N.nextNode(), c.push({
							type: 2,
							index: ++a
						});
						i.append(e[t], x());
					}
				}
			} else if (i.nodeType === 8) if (i.data === le) c.push({
				type: 2,
				index: a
			});
			else {
				let e = -1;
				for (; (e = i.data.indexOf(y, e + 1)) !== -1;) c.push({
					type: 7,
					index: a
				}), e += y.length - 1;
			}
			a++;
		}
	}
	static createElement(e, t) {
		let n = b.createElement("template");
		return n.innerHTML = e, n;
	}
};
function F(e, t, n = e, r) {
	if (t === j) return t;
	let i = r === void 0 ? n._$Cl : n._$Co?.[r], a = S(t) ? void 0 : t._$litDirective$;
	return i?.constructor !== a && (i?._$AO?.(!1), a === void 0 ? i = void 0 : (i = new a(e), i._$AT(e, n, r)), r === void 0 ? n._$Cl = i : (n._$Co ??= [])[r] = i), i !== void 0 && (t = F(e, i._$AS(e, t.values), i, r)), t;
}
var _e = class {
	constructor(e, t) {
		this._$AV = [], this._$AN = void 0, this._$AD = e, this._$AM = t;
	}
	get parentNode() {
		return this._$AM.parentNode;
	}
	get _$AU() {
		return this._$AM._$AU;
	}
	u(e) {
		let { el: { content: t }, parts: n } = this._$AD, r = (e?.creationScope ?? b).importNode(t, !0);
		N.currentNode = r;
		let i = N.nextNode(), a = 0, o = 0, s = n[0];
		for (; s !== void 0;) {
			if (a === s.index) {
				let t;
				s.type === 2 ? t = new I(i, i.nextSibling, this, e) : s.type === 1 ? t = new s.ctor(i, s.name, s.strings, this, e) : s.type === 6 && (t = new xe(i, this, e)), this._$AV.push(t), s = n[++o];
			}
			a !== s?.index && (i = N.nextNode(), a++);
		}
		return N.currentNode = b, r;
	}
	p(e) {
		let t = 0;
		for (let n of this._$AV) n !== void 0 && (n.strings === void 0 ? n._$AI(e[t]) : (n._$AI(e, n, t), t += n.strings.length - 2)), t++;
	}
}, I = class e {
	get _$AU() {
		return this._$AM?._$AU ?? this._$Cv;
	}
	constructor(e, t, n, r) {
		this.type = 2, this._$AH = M, this._$AN = void 0, this._$AA = e, this._$AB = t, this._$AM = n, this.options = r, this._$Cv = r?.isConnected ?? !0;
	}
	get parentNode() {
		let e = this._$AA.parentNode, t = this._$AM;
		return t !== void 0 && e?.nodeType === 11 && (e = t.parentNode), e;
	}
	get startNode() {
		return this._$AA;
	}
	get endNode() {
		return this._$AB;
	}
	_$AI(e, t = this) {
		e = F(this, e, t), S(e) ? e === M || e == null || e === "" ? (this._$AH !== M && this._$AR(), this._$AH = M) : e !== this._$AH && e !== j && this._(e) : e._$litType$ === void 0 ? e.nodeType === void 0 ? de(e) ? this.k(e) : this._(e) : this.T(e) : this.$(e);
	}
	O(e) {
		return this._$AA.parentNode.insertBefore(e, this._$AB);
	}
	T(e) {
		this._$AH !== e && (this._$AR(), this._$AH = this.O(e));
	}
	_(e) {
		this._$AH !== M && S(this._$AH) ? this._$AA.nextSibling.data = e : this.T(b.createTextNode(e)), this._$AH = e;
	}
	$(e) {
		let { values: t, _$litType$: n } = e, r = typeof n == "number" ? this._$AC(e) : (n.el === void 0 && (n.el = P.createElement(he(n.h, n.h[0]), this.options)), n);
		if (this._$AH?._$AD === r) this._$AH.p(t);
		else {
			let e = new _e(r, this), n = e.u(this.options);
			e.p(t), this.T(n), this._$AH = e;
		}
	}
	_$AC(e) {
		let t = me.get(e.strings);
		return t === void 0 && me.set(e.strings, t = new P(e)), t;
	}
	k(t) {
		C(this._$AH) || (this._$AH = [], this._$AR());
		let n = this._$AH, r, i = 0;
		for (let a of t) i === n.length ? n.push(r = new e(this.O(x()), this.O(x()), this, this.options)) : r = n[i], r._$AI(a), i++;
		i < n.length && (this._$AR(r && r._$AB.nextSibling, i), n.length = i);
	}
	_$AR(e = this._$AA.nextSibling, t) {
		for (this._$AP?.(!1, !0, t); e !== this._$AB;) {
			let t = oe(e).nextSibling;
			oe(e).remove(), e = t;
		}
	}
	setConnected(e) {
		this._$AM === void 0 && (this._$Cv = e, this._$AP?.(e));
	}
}, L = class {
	get tagName() {
		return this.element.tagName;
	}
	get _$AU() {
		return this._$AM._$AU;
	}
	constructor(e, t, n, r, i) {
		this.type = 1, this._$AH = M, this._$AN = void 0, this.element = e, this.name = t, this._$AM = r, this.options = i, n.length > 2 || n[0] !== "" || n[1] !== "" ? (this._$AH = Array(n.length - 1).fill(/* @__PURE__ */ new String()), this.strings = n) : this._$AH = M;
	}
	_$AI(e, t = this, n, r) {
		let i = this.strings, a = !1;
		if (i === void 0) e = F(this, e, t, 0), a = !S(e) || e !== this._$AH && e !== j, a && (this._$AH = e);
		else {
			let r = e, o, s;
			for (e = i[0], o = 0; o < i.length - 1; o++) s = F(this, r[n + o], t, o), s === j && (s = this._$AH[o]), a ||= !S(s) || s !== this._$AH[o], s === M ? e = M : e !== M && (e += (s ?? "") + i[o + 1]), this._$AH[o] = s;
		}
		a && !r && this.j(e);
	}
	j(e) {
		e === M ? this.element.removeAttribute(this.name) : this.element.setAttribute(this.name, e ?? "");
	}
}, ve = class extends L {
	constructor() {
		super(...arguments), this.type = 3;
	}
	j(e) {
		this.element[this.name] = e === M ? void 0 : e;
	}
}, ye = class extends L {
	constructor() {
		super(...arguments), this.type = 4;
	}
	j(e) {
		this.element.toggleAttribute(this.name, !!e && e !== M);
	}
}, be = class extends L {
	constructor(e, t, n, r, i) {
		super(e, t, n, r, i), this.type = 5;
	}
	_$AI(e, t = this) {
		if ((e = F(this, e, t, 0) ?? M) === j) return;
		let n = this._$AH, r = e === M && n !== M || e.capture !== n.capture || e.once !== n.once || e.passive !== n.passive, i = e !== M && (n === M || r);
		r && this.element.removeEventListener(this.name, this, n), i && this.element.addEventListener(this.name, this, e), this._$AH = e;
	}
	handleEvent(e) {
		typeof this._$AH == "function" ? this._$AH.call(this.options?.host ?? this.element, e) : this._$AH.handleEvent(e);
	}
}, xe = class {
	constructor(e, t, n) {
		this.element = e, this.type = 6, this._$AN = void 0, this._$AM = t, this.options = n;
	}
	get _$AU() {
		return this._$AM._$AU;
	}
	_$AI(e) {
		F(this, e);
	}
}, Se = _.litHtmlPolyfillSupport;
Se?.(P, I), (_.litHtmlVersions ??= []).push("3.3.3");
var Ce = (e, t, n) => {
	let r = n?.renderBefore ?? t, i = r._$litPart$;
	if (i === void 0) {
		let e = n?.renderBefore ?? null;
		r._$litPart$ = i = new I(t.insertBefore(x(), e), e, void 0, n ?? {});
	}
	return i._$AI(e), i;
}, R = globalThis, z = class extends g {
	constructor() {
		super(...arguments), this.renderOptions = { host: this }, this._$Do = void 0;
	}
	createRenderRoot() {
		let e = super.createRenderRoot();
		return this.renderOptions.renderBefore ??= e.firstChild, e;
	}
	update(e) {
		let t = this.render();
		this.hasUpdated || (this.renderOptions.isConnected = this.isConnected), super.update(e), this._$Do = Ce(t, this.renderRoot, this.renderOptions);
	}
	connectedCallback() {
		super.connectedCallback(), this._$Do?.setConnected(!0);
	}
	disconnectedCallback() {
		super.disconnectedCallback(), this._$Do?.setConnected(!1);
	}
	render() {
		return j;
	}
};
z._$litElement$ = !0, z.finalized = !0, R.litElementHydrateSupport?.({ LitElement: z });
var we = R.litElementPolyfillSupport;
we?.({ LitElement: z }), (R.litElementVersions ??= []).push("4.2.2");
//#endregion
//#region ../mde-storybook/src/components/icon/icon-sprite.js
var Te = class extends z {
	createRenderRoot() {
		return this;
	}
	render() {
		return A`
      <svg width="0" height="0" display="none" class="hidden">
        <symbol viewBox="0 0 32 32" id="m-icon-accessibility">
          <path
            d="M17.3334 2.66656a2.66656 2.66656 0 0 0 0 5.33312 2.66656 2.66656 0 0 0 0 -5.33312zm8 14.6669v-2.66656a7.31232 7.31232 0 0 1 -5.3248 -2.56704l-0.00832 -0.01024v-0.08928a2.66656 2.66656 0 0 0 -5.33312 0v8a2.66656 2.66656 0 0 0 2.66656 2.66656h5.33344v6.66656h2.66656V22.6669a2.66656 2.66656 0 0 0 -2.66656 -2.66656h-2.66656V15.623c1.48192 1.06656 3.33248 1.70688 5.33248 1.71072h0.00096z"
          ></path>
          <path
            d="M17.088 24c-0.56384 1.5616 -2.03008 2.6592 -3.75392 2.66656h-0.00096c-2.20928 0 -4 -1.79072 -4 -4s1.79072 -4 4 -4V16c-3.68 0.00448 -6.66208 2.98656 -6.66656 6.66624v0.00032c0.00448 3.68 2.98656 6.66208 6.66624 6.66656h0.00032c3.20864 -0.00416 5.88704 -2.27072 6.52576 -5.2896l0.00768 -0.04352z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-accessibility-alt">
          <path
            d="M18.6666 5.33344a2.66656 2.66656 0 1 1 -5.33312 0 2.66656 2.66656 0 0 1 5.33312 0z"
          ></path>
          <path
            d="M27 8.47072l-1.73344 -2.04928c-2.6352 1.74528 -5.84992 2.8192 -9.30944 2.91136l-0.024 0.00064h-0.28672c-3.35744 -0.0352 -6.4592 -1.10208 -9.01056 -2.89792l0.05056 0.03392 -1.71872 2.04256c1.76704 1.2736 3.83424 2.26688 6.06496 2.85984l0.1312 0.02976c0.27872 0.072 0.57472 0.13344 0.87616 0.20128a2.52544 2.52544 0 0 0 -0.04 0.39424v17.3366h2.66656v-8h2.66656v8h2.66656V12.0003a2.68992 2.68992 0 0 0 -0.05344 -0.51456l0.00288 0.01696c2.6752 -0.64608 5.02944 -1.69088 7.13344 -3.08352l-0.08288 0.05152z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-accessibility-circle">
          <path
            d="M16 2.667C8.647 2.667 2.667 8.647 2.667 16C2.667 23.353 8.647 29.333 16 29.333C23.353 29.333 29.333 23.353 29.333 16C29.333 8.647 23.353 2.667 16 2.667ZM16 5.333C21.891 5.333 26.667 10.109 26.667 16C26.667 21.891 21.891 26.667 16 26.667C10.109 26.667 5.333 21.891 5.333 16C5.333 10.109 10.109 5.333 16 5.333ZM16 7.333C14.893 7.333 14 8.227 14 9.333C14 10.44 14.893 11.333 16 11.333C17.107 11.333 18 10.44 18 9.333C18 8.227 17.107 7.333 16 7.333ZM22.367 11.32C22.26 11.32 22.16 11.34 22.06 11.367L17.527 12.66H14.473L9.94 11.367C9.847 11.34 9.747 11.32 9.647 11.327C9.093 11.327 8.653 11.787 8.653 12.333C8.653 12.78 8.96 13.173 9.387 13.287L13.993 14.607V17.48L12.06 22.64C11.86 23.153 12.113 23.733 12.627 23.933C13.14 24.133 13.72 23.88 13.92 23.367C13.92 23.36 13.92 23.353 13.927 23.347L15.993 17.84L18.06 23.347C18.247 23.867 18.82 24.133 19.34 23.947C19.86 23.76 20.127 23.187 19.94 22.667C19.94 22.66 19.94 22.653 19.933 22.647L18 17.487V14.613L22.607 13.293C23.14 13.16 23.473 12.62 23.34 12.087C23.227 11.64 22.827 11.327 22.367 11.327V11.32Z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-alert">
          <path
            d="M16 29.333c2.637 0 5.215-0.782 7.408-2.247s3.902-3.547 4.911-5.984c1.009-2.436 1.273-5.117 0.759-7.704s-1.784-4.962-3.649-6.827c-1.865-1.865-4.241-3.135-6.827-3.649s-5.267-0.25-7.704 0.759c-2.436 1.009-4.519 2.718-5.984 4.911s-2.247 4.771-2.247 7.408c0 1.751 0.345 3.485 1.015 5.102s1.652 3.088 2.89 4.326 2.708 2.22 4.326 2.89c1.618 0.67 3.352 1.015 5.102 1.015zM16 5.333c2.11 0 4.172 0.626 5.926 1.798s3.121 2.838 3.928 4.787c0.807 1.949 1.019 4.094 0.607 6.163s-1.427 3.97-2.919 5.462c-1.492 1.492-3.392 2.508-5.461 2.919s-4.214 0.2-6.163-0.607c-1.949-0.807-3.615-2.174-4.787-3.928s-1.798-3.816-1.798-5.926c0.003-2.828 1.128-5.539 3.128-7.539s4.711-3.125 7.539-3.128z"
          ></path>
          <path d="M14.667 17.333h2.667v-8h-2.667v8z"></path>
          <path
            d="M16 19.667c-0.921 0-1.667 0.746-1.667 1.667s0.746 1.667 1.667 1.667c0.921 0 1.667-0.746 1.667-1.667s-0.746-1.667-1.667-1.667z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-lock">
          <path
            d="M24.8186 13.3334h-2.152v-4c0 -3.68192 -2.98464 -6.66656 -6.66656 -6.66656 -3.68192 0 -6.66656 2.98464 -6.66656 6.66656v4h-2.15232a1.84 1.84 0 0 0 -1.30624 0.54144 1.84096 1.84096 0 0 0 -0.54144 1.30624v12.3046a1.84 1.84 0 0 0 0.54144 1.30624 1.84096 1.84096 0 0 0 1.30624 0.54144H24.8192a1.84 1.84 0 0 0 1.30624 -0.54144 1.84096 1.84096 0 0 0 0.54144 -1.30624V15.1811a1.84 1.84 0 0 0 -0.54144 -1.30624 1.84096 1.84096 0 0 0 -1.30624 -0.54144h-0.00032zM12 9.33344c0 -2.20928 1.79072 -4 4 -4s4 1.79072 4 4v4h-8zM24 26.6666H8V16h16z"
          ></path>
          <path
            d="M18.6666 21.3334a2.66656 2.66656 0 1 1 -5.33312 0 2.66656 2.66656 0 0 1 5.33312 0z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-unlock">
          <path
            d="M24.8186 13.3334h-2.152v-4c0 -3.68192 -2.98464 -6.66656 -6.66656 -6.66656 -2.59008 0.0064 -4.83136 1.49344 -5.92256 3.6592l-0.01728 0.03808 2.37728 1.18784c0.66592 -1.32128 2.00928 -2.21312 3.56192 -2.21856H16c2.20928 0 4 1.79072 4 4v4H7.18112a1.84 1.84 0 0 0 -1.30624 0.54144 1.84096 1.84096 0 0 0 -0.54144 1.30624v12.3046a1.84 1.84 0 0 0 0.54144 1.30624 1.84096 1.84096 0 0 0 1.30624 0.54144H24.8192a1.84 1.84 0 0 0 1.30624 -0.54144 1.84096 1.84096 0 0 0 0.54144 -1.30624V15.1811a1.84 1.84 0 0 0 -0.54144 -1.30624 1.84096 1.84096 0 0 0 -1.30624 -0.54144h-0.00032zM24 26.6666H8V16h16z"
          ></path>
          <path
            d="M18.6666 21.3334a2.66656 2.66656 0 1 1 -5.33312 0 2.66656 2.66656 0 0 1 5.33312 0z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-arrow-down">
          <path
            d="M16.9427 26.2666l9.72416 -9.712 -1.89056 -1.888L17.3338 22.112V5.33344h-2.66656V22.112L7.22464 14.6666l-1.89056 1.888 9.72384 9.712c0.24128 0.24128 0.57472 0.3904 0.94272 0.3904s0.70144 -0.14912 0.94272 -0.3904z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-arrow-left">
          <path
            d="M5.72672 15.0573l9.71872 -9.72416 1.89056 1.89056L9.888 14.6662h16.7814v2.66656H9.888l7.448 7.44256 -1.89056 1.89056 -9.71872 -9.72384c-0.24128 -0.24128 -0.3904 -0.57472 -0.3904 -0.94272s0.14912 -0.70144 0.3904 -0.94272z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-arrow-right">
          <path
            d="M26.2666 15.0573l-9.712 -9.72416 -1.888 1.89056L22.112 14.6662H5.33344v2.66656H22.112L14.6666 24.7754l1.888 1.89056 9.712 -9.72384c0.24128 -0.24128 0.3904 -0.57472 0.3904 -0.94272s-0.14912 -0.70144 -0.3904 -0.94272z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-arrow-up">
          <path
            d="M16.9386 5.73344l9.72256 9.712 -1.88544 1.888L17.3382 9.888v16.7786h-2.66656V9.888L7.22368 17.3334l-1.88544 -1.888 9.71872 -9.712c0.24096 -0.24 0.57344 -0.38848 0.9408 -0.38848s0.69952 0.14848 0.9408 0.38848z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-battery">
          <path
            d="M26.6666 12v-1.33344A2.66656 2.66656 0 0 0 24 8H5.33344a2.66656 2.66656 0 0 0 -2.66656 2.66656v10.6666a2.66656 2.66656 0 0 0 2.66656 2.66656H24a2.66656 2.66656 0 0 0 2.66656 -2.66656v-1.33344h2.66656v-8zM24 21.3334H16V10.6669h8z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-bell">
          <path
            d="M4.48416 25.7802v-2.936h2.92288v-9.28032c0 -1.97984 0.57728 -3.75552 1.73152 -5.32736s2.68576 -2.57696 4.59456 -3.016v-0.84544c0 -0.6256 0.2208 -1.14944 0.66208 -1.5712s0.97344 -0.63264 1.59552 -0.63264c0.62208 0 1.15392 0.21088 1.59552 0.63264s0.66208 0.9456 0.66208 1.5712v0.84544c1.92096 0.43904 3.46144 1.44128 4.62112 3.00672s1.73952 3.34432 1.73952 5.33664v9.28032h2.90688v2.936h-23.032zM16.0032 29.8288c-0.76416 0 -1.42784 -0.27648 -1.9904 -0.8288s-0.84416 -1.21696 -0.84416 -1.99296h5.66272c0 0.78368 -0.27776 1.44992 -0.8336 1.99872s-1.2208 0.82304 -1.99456 0.82304zM10.3302 22.8442h11.3427v-9.28032c0 -1.57728 -0.54912 -2.92224 -1.64736 -4.0352s-2.43584 -1.66944 -4.01312 -1.66944c-1.57728 0 -2.91872 0.55648 -4.024 1.66944s-1.65824 2.45792 -1.65824 4.0352v9.28032z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-bell-active">
          <path
            d="M15.935 2.667c0.561 0 1.010 0.449 1.010 1.010v1.404c3.958 0.505 7.047 3.902 7.047 8v2.835c0 1.235 0.421 2.443 1.207 3.397l1.29 1.516c0.646 0.814 0.786 1.881 0.337 2.807s-1.348 1.516-2.387 1.516h-3.874c-0.225 2.33-2.189 4.182-4.603 4.182s-4.38-1.824-4.604-4.182h-3.872c-1.038 0-1.938-0.589-2.387-1.516s-0.308-2.021 0.337-2.807l1.234-1.516c0.786-0.954 1.207-2.162 1.207-3.397v-2.835c0-4.098 3.088-7.494 7.046-8v-1.404c0-0.561 0.449-1.010 1.010-1.010zM13.381 25.122c0.196 1.235 1.262 2.19 2.553 2.19s2.358-0.955 2.555-2.19h-5.108zM15.935 7.017c-3.34 0-6.034 2.724-6.062 6.064v2.807c-0 1.712-0.59 3.368-1.656 4.687l-1.234 1.516c-0.224 0.28-0.141 0.561-0.085 0.673s0.196 0.366 0.561 0.366h16.954c0.365-0 0.505-0.254 0.561-0.366s0.14-0.393-0.112-0.673l-1.236-1.516c-1.067-1.319-1.656-2.975-1.656-4.687v-2.836c-0-3.312-2.695-6.035-6.035-6.035z"
          ></path>
          <path
            d="M8.918 2.891c0.449-0.309 1.095-0.196 1.404 0.253s0.196 1.095-0.253 1.404c-1.993 1.404-3.453 3.425-4.099 5.755-0.113 0.449-0.534 0.757-0.954 0.729-0.112 0-0.196 0.001-0.28-0.027-0.533-0.14-0.842-0.702-0.702-1.236 0.786-2.779 2.526-5.221 4.884-6.878z"
          ></path>
          <path
            d="M21.549 3.143c0.309-0.449 0.955-0.561 1.404-0.253 2.358 1.656 4.098 4.099 4.884 6.878 0.14 0.533-0.169 1.095-0.675 1.236-0.112 0.028-0.196 0.027-0.28 0.027-0.449 0-0.871-0.28-0.983-0.729-0.646-2.33-2.105-4.38-4.098-5.755-0.449-0.309-0.561-0.955-0.253-1.404z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-bullet">
          <path
            d="M16 12.024c2.20928 0 4 1.79072 4 4s-1.79072 4 -4 4 -4 -1.79072 -4 -4 1.79072 -4 4 -4zm0 -2.66656c-3.68192 0 -6.66656 2.98464 -6.66656 6.66656 0 3.68192 2.98464 6.66656 6.66656 6.66656 3.68192 0 6.66656 -2.98464 6.66656 -6.66656 0 -3.68192 -2.98464 -6.66656 -6.66656 -6.66656z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-calendar">
          <path
            d="M25.3334 5.33344h-2.66656V2.66688h-2.66656v2.66656h-8V2.66688h-2.66656v2.66656h-2.66656A2.66656 2.66656 0 0 0 4.00064 8v18.6666a2.66656 2.66656 0 0 0 2.66656 2.66656h18.6666a2.66656 2.66656 0 0 0 2.66656 -2.66656V8a2.66656 2.66656 0 0 0 -2.66656 -2.66656zm0 21.3331H6.66688V13.3331h18.6666zm0 -16H6.66688V8h18.6666z"
          ></path>
          <path
            d="M20 21.3334h2.66656V24H20v-2.66656zm-5.33344 0h2.66656V24h-2.66656v-2.66656zm-5.33312 0H12V24h-2.66656v-2.66656zM20 16h2.66656v2.66656H20V16zm-5.33344 0h2.66656v2.66656h-2.66656V16zm-5.33312 0H12v2.66656h-2.66656V16z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-check">
          <path
            d="M12 24.6666a1.3296 1.3296 0 0 1 -0.94272 -0.39072L4 17.2131l1.88 -1.88L12 21.4477l14.1107 -14.1146 1.88928 1.88544 -15.0586 15.0573a1.33024 1.33024 0 0 1 -0.94112 0.39072z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-chevron-down">
          <path
            d="M24.9667 12.9414l-1.88544 -1.88544 -7.05728 7.05728L8.96672 11.056l-1.88544 1.88544 8 8c0.24128 0.24128 0.57472 0.3904 0.94272 0.3904s0.70144 -0.14912 0.94272 -0.3904z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-chevron-left">
          <path
            d="M19.0573 24.968l1.88544 -1.88544 -7.05728 -7.05728 7.05728 -7.05728 -1.88544 -1.88544 -8 8c-0.24128 0.24128 -0.3904 0.57472 -0.3904 0.94272s0.14912 0.70144 0.3904 0.94272z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-chevron-right">
          <path
            d="M12.9427 24.9667l-1.88544 -1.88544 7.05728 -7.05728 -7.05728 -7.05728 1.88544 -1.88544 8 8c0.24128 0.24128 0.3904 0.57472 0.3904 0.94272s-0.14912 0.70144 -0.3904 0.94272z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-chevron-up">
          <path
            d="M24.9667 19.0573l-1.88544 1.88544 -7.05728 -7.05728 -7.05728 7.05728 -1.88544 -1.88544 8 -8c0.24128 -0.24128 0.57472 -0.3904 0.94272 -0.3904s0.70144 0.14912 0.94272 0.3904z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-close">
          <path
            d="M26.6291 7.32096l-1.95008 -1.95008L16 14.0499 7.32096 5.37088l-1.95008 1.95008L14.0499 16 5.37088 24.679l1.95008 1.95008L16 17.9501l8.67904 8.67904 1.95008 -1.95008L17.9501 16l8.67904 -8.67904z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-copy-link">
          <path
            d="M14.9254 25.1053a5.37024 5.37024 0 0 1 -3.81792 1.58656h-0.01472 0.00064 -0.01376c-1.49152 0 -2.8416 -0.60672 -3.81664 -1.58656l-0.34272 -0.34272a5.36704 5.36704 0 0 1 -1.58656 -3.81664v-0.02816c0 -1.49184 0.6064 -2.84224 1.58656 -3.8176l3.66432 -3.66272 -1.88672 -1.88544 -3.66272 3.66272c-1.46304 1.46336 -2.36768 3.4848 -2.36768 5.71744s0.90496 4.25408 2.36768 5.71744l0.34144 0.34144a8.03424 8.03424 0 0 0 5.70656 2.368h0.01152 -0.00064 0.01088a8.03424 8.03424 0 0 0 5.70656 -2.368l3.66272 -3.664 -1.88544 -1.88544zm12.04 -19.704L26.624 5.05984a8.03424 8.03424 0 0 0 -5.70656 -2.368h-0.01152 0.00064 -0.01088a8.03424 8.03424 0 0 0 -5.70656 2.368L11.5264 8.72256l1.88544 1.88672 3.66272 -3.664a5.37024 5.37024 0 0 1 3.81792 -1.58656h0.01472 -0.00064 0.01376c1.49152 0 2.8416 0.60672 3.81664 1.58656l0.34272 0.34272a5.36704 5.36704 0 0 1 1.58656 3.81664v0.01472 -0.00064 0.01408c0 1.49184 -0.6064 2.84224 -1.58656 3.8176L21.4154 18.6131l1.88672 1.88544 3.66272 -3.66272c1.46336 -1.46304 2.36832 -3.48448 2.36832 -5.71744s-0.90496 -4.25408 -2.36832 -5.71744z"
          ></path>
          <path
            d="M9.72384 20.416l10.0003 -10.0003 1.88576 1.88576L11.6096 22.3018 9.72384 20.416z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-download">
          <path
            d="M24 17.3334v8H8v-8h-2.66656v8A2.66656 2.66656 0 0 0 8 28h16a2.66656 2.66656 0 0 0 2.66656 -2.66656v-8z"
          ></path>
          <path
            d="M14.6666 15.448V4h2.66656v11.448l3.05728 -3.05728 1.88544 1.88544L16.9424 19.6096c-0.24128 0.24128 -0.57472 0.3904 -0.94272 0.3904s-0.70144 -0.14912 -0.94272 -0.3904L9.72352 14.2762l1.88544 -1.88544z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-edit">
          <path
            d="M4.0016 22.9997v4.99936h4.99936l14.7446 -14.7443 -4.99936 -4.99936 -14.7446 14.7443zM27.6118 9.38816c0.52 -0.52 0.52 -1.36 0 -1.87968l-3.11968 -3.11968c-0.52 -0.52 -1.36 -0.52 -1.87968 0l-2.43968 2.43968 4.99936 4.99936 2.43968 -2.43968z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-ellipsis-horizontal">
          <path
            d="M26.6666 16a2.66656 2.66656 0 1 1 -5.33312 0 2.66656 2.66656 0 0 1 5.33312 0zm-8 0a2.66656 2.66656 0 1 1 -5.33312 0 2.66656 2.66656 0 0 1 5.33312 0zm-8 0a2.66656 2.66656 0 1 1 -5.33312 0 2.66656 2.66656 0 0 1 5.33312 0z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-ellipsis-vertical">
          <path
            d="M18.6666 8a2.66656 2.66656 0 1 1 -5.33312 0 2.66656 2.66656 0 0 1 5.33312 0zm0 8a2.66656 2.66656 0 1 1 -5.33312 0 2.66656 2.66656 0 0 1 5.33312 0zm0 8a2.66656 2.66656 0 1 1 -5.33312 0 2.66656 2.66656 0 0 1 5.33312 0z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-expand">
          <path
            d="M8.552 25.3334l7.05728 -7.05728 -1.88544 -1.88544 -7.05728 7.05728V18.6666H4v8a1.33344 1.33344 0 0 0 1.33344 1.33344h8v-2.66656zm14.896 -18.6669L16.3907 13.7238l1.88544 1.88544 7.05728 -7.05728v4.78144H28v-8a1.33344 1.33344 0 0 0 -1.33344 -1.33344h-8v2.66656z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-ext-link">
          <path
            d="M26.6666 4.024h-8v2.66656h4.78144L15.0573 15.0813l1.88544 1.88544L25.3334 8.576v4.78144H28v-8a1.33344 1.33344 0 0 0 -1.33344 -1.33344z"
          ></path>
          <path
            d="M25.3334 25.3574H6.66688V6.69088h6.66656v-2.66656H6.66688a2.66656 2.66656 0 0 0 -2.66656 2.66656v18.6666a2.66656 2.66656 0 0 0 2.66656 2.66656h18.6666a2.66656 2.66656 0 0 0 2.66656 -2.66656V18.6909h-2.66656z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-eye">
          <path
            d="M15.8573 26.6666c-0.15328 0 -0.30528 0 -0.45856 -0.01056C7.81728 26.2934 3.01216 16.9987 2.81088 16.6038c-0.0912 -0.17568 -0.14464 -0.38368 -0.14464 -0.60384s0.05344 -0.42816 0.14816 -0.61152l-0.00352 0.00736c0.20128 -0.4 5.00672 -9.68928 12.5878 -10.0522q7.23744 -0.30144 13.7267 9.94144c0.13024 0.20224 0.20768 0.4496 0.20768 0.71456s-0.07744 0.51232 -0.21088 0.72l0.0032 -0.00544c-4.192 6.60672 -8.656 9.952 -13.2678 9.952zM5.52672 16c1.12128 1.91872 4.944 7.75456 10 7.992 3.60672 0.18944 7.24928 -2.51744 10.88 -7.992 -3.62944 -5.47328 -7.30784 -8.17216 -10.88 -7.992 -5.056 0.23744 -8.87872 6.07328 -10 7.992z"
          ></path>
          <path
            d="M16 21.3334c-2.9456 0 -5.33344 -2.38784 -5.33344 -5.33344S13.0544 10.6666 16 10.6666c2.9456 0 5.33344 2.38784 5.33344 5.33344 0 2.9456 -2.38784 5.33344 -5.33344 5.33344zm0 -8A2.66656 2.66656 0 1 0 18.6666 16 2.66656 2.66656 0 0 0 16 13.3334z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-facebook">
          <path
            d="M18.3213 29.3334V17.1696h4.08416l0.61056 -4.74016H18.3213v-3.02656c0 -1.37344 0.38144 -2.30816 2.34944 -2.30816h2.51072V2.85344c-1.06304 -0.11904 -2.29536 -0.18688 -3.54368 -0.18688l-0.12 0.00032h0.00608c-3.62016 0 -6.09728 2.20928 -6.09728 6.26656v3.496H9.33312v4.74016h4.09344v12.1642z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-file">
          <path
            d="M25.8854 8.552l-5.104 -5.104a2.65984 2.65984 0 0 0 -1.88544 -0.78144H8a2.66656 2.66656 0 0 0 -2.66656 2.66656v21.3334A2.66656 2.66656 0 0 0 8 29.3331h16a2.66656 2.66656 0 0 0 2.66656 -2.66656V10.4371a2.65696 2.65696 0 0 0 -0.78144 -1.88544zM24 26.6666H8V5.33312h10.896L24 10.4371z"
          ></path>
          <path
            d="M10.6666 16h10.6666v2.66656H10.6666V16zm0 -5.33344h8v2.66656h-8v-2.66656zm0 10.6669h10.6666V24H10.6666v-2.66656z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-print">
          <path
            d="M8 13.333V5.333A2.667 2.667 0 0 1 10.667 2.667H21.333A2.667 2.667 0 0 1 24 5.333V13.333H21.333V5.333H10.667V13.333Z"
          ></path>
          <path
            d="M9.333 24H5.333A2.667 2.667 0 0 1 2.667 21.333V13.333A2.667 2.667 0 0 1 5.333 10.667H26.667A2.667 2.667 0 0 1 29.333 13.333V21.333A2.667 2.667 0 0 1 26.667 24H22.667V21.333H26.667V13.333H5.333V21.333H9.333Z"
          ></path>
          <path
            d="M10.667 16H21.333A2.667 2.667 0 0 1 24 18.667V26.667A2.667 2.667 0 0 1 21.333 29.333H10.667A2.667 2.667 0 0 1 8 26.667V18.667A2.667 2.667 0 0 1 10.667 16ZM10.667 18.667V26.667H21.333V18.667Z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-filter">
          <path
            d="M12 6.69056v-2.66656h-2.66656v2.66656H4v2.66656h5.33344v2.66656H12v-2.66656h16v-2.66656zm0 13.3334h-2.66656v2.66656H4v2.66656h5.33344v2.66656H12v-2.66656h16v-2.66656h-16zm10.6666 -8H20v2.66656h-16v2.66656h16v2.66656h2.66656v-2.66656H28v-2.66656H22.6666z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-floppy">
          <path
            d="M25.6666 8.53344L20.7331 4.584a2.64992 2.64992 0 0 0 -1.66528 -0.584H8a2.66656 2.66656 0 0 0 -2.66656 2.66656v18.6666A2.66656 2.66656 0 0 0 8 27.9997h16a2.66656 2.66656 0 0 0 2.66656 -2.66656V10.6144a2.66144 2.66144 0 0 0 -0.99488 -2.07744l-0.00512 -0.00384zM24 25.3334H8V6.66688h2.66656v5.33344h10.6666V8.4816l2.66656 2.13344z"
          ></path>
          <path
            d="M10.6666 14.6666h10.6666v2.66656H10.6666v-2.66656zm0 5.33344h10.6666v2.66656H10.6666V20z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-heart">
          <path
            d="M21.5507 6.69056a5.17664 5.17664 0 0 1 4.0048 1.8976l0.00736 0.00896a5.4016 5.4016 0 0 1 1.0976 3.2768c0 1.54752 -0.64608 2.944 -1.68352 3.93472l-0.00224 0.00192L16 24.8051 7.02528 15.8106a5.42592 5.42592 0 0 1 -1.68544 -3.93664c0 -1.23648 0.41248 -2.37632 1.1072 -3.28992l-0.00992 0.01344c0.95712 -1.16608 2.39808 -1.90464 4.01184 -1.90656h0.01376c1.42688 0 2.71808 0.58176 3.64896 1.52096l0.00032 0.00032 1.888 1.89184 1.888 -1.89184a5.1248 5.1248 0 0 1 3.65024 -1.52128h0.01312 -0.00064zm0 -2.66656h-0.00864a7.79264 7.79264 0 0 0 -5.54176 2.30368l-0.00032 0.00032a7.79008 7.79008 0 0 0 -5.5408 -2.304h-0.01024 0.00064 -0.00768c-2.4384 0 -4.616 1.1152 -6.05088 2.86368l-0.01088 0.01376c-1.06272 1.35808 -1.70432 3.09088 -1.70432 4.97312a8.08384 8.08384 0 0 0 2.45984 5.81696l0.00192 0.00192 9.91456 9.93728A1.32928 1.32928 0 0 0 16 28.0307h0.00032a1.32992 1.32992 0 0 0 0.94784 -0.39968l0.00032 -0.00032 9.91456 -9.93728c1.51904 -1.4752 2.46176 -3.53696 2.46176 -5.81888a8.0736 8.0736 0 0 0 -1.71776 -4.99104l0.01344 0.01792c-1.44576 -1.76192 -3.62368 -2.87744 -6.06176 -2.87744h-0.008 0.00032z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-heart-full">
          <path
            d="M21.542 4.035c-1.031-0.003-2.053 0.198-3.007 0.592s-1.819 0.972-2.548 1.702c-0.726-0.733-1.59-1.314-2.542-1.712s-1.973-0.602-3.005-0.603c-1.162-0.003-2.31 0.252-3.361 0.748s-1.978 1.219-2.714 2.118c-1.227 1.574-1.831 3.546-1.695 5.538s1.002 3.863 2.432 5.256l9.896 9.956c0.123 0.126 0.271 0.227 0.433 0.296s0.337 0.105 0.514 0.106c0.177-0 0.351-0.036 0.514-0.104s0.311-0.168 0.434-0.294l9.933-9.919c1.435-1.387 2.308-3.256 2.451-5.247s-0.453-3.965-1.674-5.544c-0.733-0.901-1.657-1.628-2.706-2.128s-2.196-0.759-3.358-0.76z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-smiley-happy">
          <path
            d="M20.667 14.667c0.556 0 1.028-0.194 1.417-0.583s0.583-0.861 0.583-1.417c0-0.556-0.194-1.028-0.583-1.417s-0.861-0.583-1.417-0.583c-0.556 0-1.028 0.194-1.417 0.583s-0.583 0.861-0.583 1.417c0 0.556 0.194 1.028 0.583 1.417s0.861 0.583 1.417 0.583zM11.333 14.667c0.556 0 1.028-0.194 1.417-0.583s0.583-0.861 0.583-1.417c0-0.556-0.194-1.028-0.583-1.417s-0.861-0.583-1.417-0.583c-0.556 0-1.028 0.194-1.417 0.583s-0.583 0.861-0.583 1.417c0 0.556 0.194 1.028 0.583 1.417s0.861 0.583 1.417 0.583zM20.117 22.050c0.79-0.548 1.44-1.207 1.952-1.978 0.421-0.634-0.095-1.405-0.856-1.405-0.38 0-0.725 0.205-0.953 0.51-0.438 0.586-0.974 1.066-1.61 1.44-0.811 0.478-1.694 0.717-2.65 0.717s-1.839-0.239-2.65-0.717c-0.636-0.374-1.172-0.855-1.61-1.44-0.227-0.305-0.573-0.51-0.953-0.51-0.761 0-1.277 0.771-0.856 1.405 0.512 0.771 1.162 1.43 1.952 1.978 1.233 0.856 2.606 1.283 4.117 1.283s2.883-0.428 4.117-1.283zM10.8 28.283c-1.622-0.7-3.033-1.65-4.233-2.85s-2.15-2.611-2.85-4.233c-0.7-1.622-1.050-3.356-1.050-5.2s0.35-3.578 1.050-5.2c0.7-1.622 1.65-3.033 2.85-4.233s2.611-2.15 4.233-2.85c1.622-0.7 3.356-1.050 5.2-1.050s3.578 0.35 5.2 1.050c1.622 0.7 3.033 1.65 4.233 2.85s2.15 2.611 2.85 4.233c0.7 1.622 1.050 3.356 1.050 5.2s-0.35 3.578-1.050 5.2c-0.7 1.622-1.65 3.033-2.85 4.233s-2.611 2.15-4.233 2.85c-1.622 0.7-3.356 1.050-5.2 1.050s-3.578-0.35-5.2-1.050zM23.567 23.567c2.067-2.067 3.1-4.589 3.1-7.567s-1.033-5.5-3.1-7.567-4.589-3.1-7.567-3.1c-2.978 0-5.5 1.033-7.567 3.1s-3.1 4.589-3.1 7.567c0 2.978 1.033 5.5 3.1 7.567s4.589 3.1 7.567 3.1c2.978 0 5.5-1.033 7.567-3.1z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-home">
          <path
            d="M30.1866 16.4147L16.9558 3.41472c-0.24512 -0.24448 -0.58368 -0.39552 -0.95744 -0.39552s-0.712 0.15104 -0.95744 0.39584L1.81152 16.415l1.91456 2.096 1.82816 -1.79584v10.8598l-0.00032 0.0256a1.41888 1.41888 0 0 0 1.39072 1.41856h5.57184a1.41888 1.41888 0 0 0 1.39232 -1.41856l-0.00032 -0.02688v0.00128 -7.22272h4.17856v7.22272l-0.00032 0.0256a1.41888 1.41888 0 0 0 1.39072 1.41856h5.57184a1.41888 1.41888 0 0 0 1.39232 -1.41856l-0.00032 -0.02688v0.00128 -10.8598l1.82656 1.79584zM23.6534 26.1306h-2.78528V18.9078l0.00032 -0.0256a1.41888 1.41888 0 0 0 -1.39072 -1.41856H12.5139a1.4176 1.4176 0 0 0 -1.392 1.44544v-0.00128 7.22272h-2.78528V13.9773l7.65984 -7.52544 7.66016 7.52544z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-information">
          <path
            d="M16 2.69056c-7.36384 0 -13.3334 5.9696 -13.3334 13.3334S8.63616 29.3574 16 29.3574s13.3334 -5.9696 13.3334 -13.3334S23.3638 2.69056 16 2.69056zm0 24c-5.89088 0 -10.6666 -4.77568 -10.6666 -10.6666S10.1091 5.35744 16 5.35744s10.6666 4.77568 10.6666 10.6666c-0.00672 5.88832 -4.77824 10.6598 -10.6659 10.6666H16z"
          ></path>
          <path
            d="M14.6666 14.6906h2.66656v8h-2.66656v-8zm3 -4a1.66656 1.66656 0 1 1 -3.33344 0 1.66656 1.66656 0 0 1 3.33344 0z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-help">
          <path
            d="M17.117 23.517c0.322-0.322 0.483-0.717 0.483-1.183s-0.161-0.861-0.483-1.183c-0.322-0.322-0.717-0.483-1.183-0.483s-0.861 0.161-1.183 0.483c-0.322 0.322-0.483 0.717-0.483 1.183s0.161 0.861 0.483 1.183c0.322 0.322 0.717 0.483 1.183 0.483s0.861-0.161 1.183-0.483zM14.733 18.867h2.467c0-0.733 0.083-1.311 0.25-1.733s0.639-1 1.417-1.733c0.578-0.578 1.033-1.128 1.367-1.65s0.5-1.15 0.5-1.883c0-1.244-0.456-2.2-1.367-2.867s-1.989-1-3.233-1c-1.267 0-2.294 0.333-3.083 1s-1.339 1.467-1.65 2.4l2.2 0.867c0.111-0.4 0.361-0.833 0.75-1.3s0.983-0.7 1.783-0.7c0.711 0 1.244 0.194 1.6 0.583s0.533 0.817 0.533 1.283c0 0.444-0.133 0.861-0.4 1.25s-0.6 0.75-1 1.083c-0.978 0.867-1.578 1.522-1.8 1.967s-0.333 1.256-0.333 2.433zM16 29.333c-1.844 0-3.578-0.35-5.2-1.050s-3.033-1.65-4.233-2.85c-1.2-1.2-2.15-2.611-2.85-4.233s-1.050-3.356-1.050-5.2c0-1.844 0.35-3.578 1.050-5.2s1.65-3.033 2.85-4.233c1.2-1.2 2.611-2.15 4.233-2.85s3.356-1.050 5.2-1.050c1.844 0 3.578 0.35 5.2 1.050s3.033 1.65 4.233 2.85c1.2 1.2 2.15 2.611 2.85 4.233s1.050 3.356 1.050 5.2c0 1.844-0.35 3.578-1.050 5.2s-1.65 3.033-2.85 4.233c-1.2 1.2-2.611 2.15-4.233 2.85s-3.356 1.050-5.2 1.050zM16 26.667c2.978 0 5.5-1.033 7.567-3.1s3.1-4.589 3.1-7.567c0-2.978-1.033-5.5-3.1-7.567s-4.589-3.1-7.567-3.1c-2.978 0-5.5 1.033-7.567 3.1s-3.1 4.589-3.1 7.567c0 2.978 1.033 5.5 3.1 7.567s4.589 3.1 7.567 3.1z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-instagram">
          <path
            d="M16.0067 9.16256h-0.00128c-3.776 0 -6.83744 3.06112 -6.83744 6.83744s3.06112 6.83744 6.83744 6.83744c3.776 0 6.83744 -3.06112 6.83744 -6.83744v-0.01216c0 -3.7696 -3.05568 -6.82528 -6.82528 -6.82528h-0.0112 0.00064zm0 11.2813h-0.00128c-2.45504 0 -4.44544 -1.9904 -4.44544 -4.44544s1.9904 -4.44544 4.44544 -4.44544 4.44544 1.9904 4.44544 4.44544c-0.00384 2.45312 -1.99104 4.44064 -4.44352 4.44544h-0.00032zm8.70528 -11.5613a1.6 1.6 0 1 1 -1.6 -1.6h0.00928a1.5904 1.5904 0 0 1 1.59072 1.59072v0.00992 -0.00064zm4.53344 1.61888a7.86496 7.86496 0 0 0 -2.1568 -5.59168l0.00352 0.00384a7.91072 7.91072 0 0 0 -5.59424 -2.15456h0.00768c-2.20128 -0.12544 -8.8 -0.12544 -11 0a7.90048 7.90048 0 0 0 -5.59584 2.15328l0.00416 -0.00384a7.88768 7.88768 0 0 0 -2.15328 5.59648v-0.00832c-0.12544 2.20128 -0.12544 8.8 0 11.0026a7.86624 7.86624 0 0 0 2.1568 5.5904l-0.00352 -0.00384a7.91936 7.91936 0 0 0 5.59424 2.15456h-0.00736c2.20128 0.12544 8.8 0.12544 11 0a7.86368 7.86368 0 0 0 5.5904 -2.15808l-0.00352 0.00352a7.91264 7.91264 0 0 0 2.15296 -5.59552v0.00768c0.12544 -2.20128 0.12544 -8.8 0 -10.9958zm-2.8496 13.3587c-0.46752 1.16224 -1.3712 2.06592 -2.50304 2.52256l-0.03008 0.01088c-1.75456 0.696 -5.91872 0.53344 -7.85856 0.53344s-6.10944 0.15456 -7.85856 -0.53344c-1.16224 -0.46752 -2.06592 -1.3712 -2.52256 -2.50304l-0.01088 -0.03008c-0.696 -1.75584 -0.53344 -5.92 -0.53344 -7.86656s-0.15456 -6.11072 0.53344 -7.86656c0.46752 -1.16224 1.3712 -2.06592 2.50304 -2.52256l0.03008 -0.01088c1.75456 -0.696 5.91872 -0.53344 7.85856 -0.53344s6.10944 -0.15456 7.85856 0.53344c1.16224 0.46752 2.06592 1.3712 2.52256 2.50304l0.01088 0.03008c0.696 1.75616 0.53344 5.92 0.53344 7.86656s0.16256 6.11744 -0.53344 7.86656z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-language">
          <path
            d="M16 2.66656C8.63616 2.66656 2.66656 8.63616 2.66656 16S8.63616 29.3334 16 29.3334 29.3334 23.3638 29.3334 16 23.3638 2.66656 16 2.66656zm7.34144 12.6307a20.6445 20.6445 0 0 0 -0.56192 -4.24032l0.02848 0.14016c1.86624 0.6816 3.4816 1.57056 4.93312 2.66496l-0.04768 -0.03456a11.296 11.296 0 0 1 0.17056 1.43488l0.0016 0.032zm-1.37216 0H16.8467v-5.264c1.60192 0.0528 3.12448 0.29568 4.57632 0.7072l-0.13376 -0.03232c0.39296 1.36544 0.6384 2.93984 0.67968 4.56544l0.00032 0.024zM16.8467 8.63872V4.13344c0.4992 0.03456 0.95328 0.09312 1.39904 0.17696l-0.0736 -0.01152c1.05248 1.39328 1.93632 2.99616 2.57408 4.7184l0.04192 0.1296c-1.16704 -0.28096 -2.52096 -0.46368 -3.90976 -0.5072l-0.03136 -0.00064zm-1.42816 0.01184a20.9888 20.9888 0 0 0 -4.3904 0.63968l0.14496 -0.03168c0.68288 -1.89312 1.58112 -3.5328 2.69056 -5.00352l-0.03456 0.04768a11.3158 11.3158 0 0 1 1.56288 -0.1776l0.02624 -0.00096zm0 1.38816v5.25856H10.0307a18.913 18.913 0 0 1 0.66688 -4.5712l-0.03104 0.13248c1.4032 -0.44128 3.02688 -0.73504 4.70592 -0.81824l0.04608 -0.00192zM8.63584 15.2973H4.13312a12.1664 12.1664 0 0 1 0.17504 -1.48416l-0.0112 0.0736c1.3824 -0.92928 2.96576 -1.76704 4.63616 -2.43296l0.17184 -0.06048c-0.26688 1.16 -0.43616 2.50272 -0.4688 3.87968l-0.00032 0.02432zm-4.5024 1.42816h4.52544a21.064 21.064 0 0 0 0.59168 4.06688l-0.0304 -0.14432c-1.88608 -0.73024 -3.5088 -1.58176 -5.01728 -2.59104l0.09184 0.05792a11.1786 11.1786 0 0 1 -0.16 -1.35712l-0.0016 -0.032zm5.91456 0h5.37056v5.23456c-1.67072 -0.0816 -3.24384 -0.35904 -4.74176 -0.81056l0.13888 0.03584c-0.40192 -1.31296 -0.67648 -2.83584 -0.76576 -4.40832l-0.00224 -0.05184zm5.37056 6.608V27.8816a11.8605 11.8605 0 0 1 -1.60384 -0.18016l0.072 0.01088c-0.95232 -1.41888 -1.80416 -3.04448 -2.47424 -4.76096l-0.0592 -0.17216c1.20128 0.3008 2.59584 0.50016 4.02784 0.5536l0.03776 0.00128zm1.42816 0a20.8592 20.8592 0 0 0 3.9168 -0.50656l-0.1392 0.02656c-0.72768 1.86016 -1.57152 3.45952 -2.56832 4.94752l0.05792 -0.09152c-0.35392 0.0672 -0.78816 0.12256 -1.22912 0.15392l-0.03744 0.00224zm0 -1.36416V16.7293H21.952c-0.09568 1.6768 -0.38624 3.24832 -0.85056 4.74304l0.03712 -0.1392c-1.2736 0.35488 -2.74528 0.5824 -4.26208 0.63136l-0.03008 0.00064zm6.48544 -5.24H27.8656a11.9206 11.9206 0 0 1 -0.18208 1.5184l0.0112 -0.07296c-1.4256 1.0768 -3.06784 1.97632 -4.83328 2.61856l-0.13056 0.0416c0.31648 -1.20928 0.53216 -2.61632 0.60096 -4.06208l0.00192 -0.04736zm3.75712 -5.02528c-1.3552 -0.83424 -2.91744 -1.55552 -4.5664 -2.0832l-0.15232 -0.04224c-0.57088 -1.78336 -1.28192 -3.328 -2.15264 -4.7648l0.05536 0.09824c3.13216 1.23488 5.568 3.66176 6.78624 6.70752l0.02848 0.08064zM11.7002 4.912c-0.85408 1.38528 -1.58816 2.98208 -2.12 4.6688l-0.0416 0.15232c-1.80064 0.61184 -3.34944 1.33312 -4.80352 2.19712l0.11552 -0.06368c1.21664 -3.19968 3.67392 -5.69152 6.76864 -6.9264l0.08064 -0.02848zM4.85344 20.1414c1.3808 0.82304 2.97792 1.55616 4.6544 2.11488l0.17504 0.05056c0.6176 1.85728 1.35648 3.45824 2.24768 4.9584l-0.06368 -0.11584C8.61504 25.9126 6.0928 23.392 4.88096 20.2234l-0.02752 -0.08192zm15.28 7.008c0.81024 -1.35296 1.53792 -2.91808 2.09952 -4.56l0.05248 -0.176c1.83712 -0.57312 3.43136 -1.30048 4.91104 -2.19776l-0.0976 0.05504c-1.25888 3.18944 -3.75616 5.65824 -6.88352 6.8512l-0.08192 0.02752z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-globe">
          <path
            d="M16 2.667C8.64 2.675 2.675 8.639 2.667 15.999V16C2.675 23.36 8.639 29.325 15.999 29.333H16C23.36 29.325 29.325 23.36 29.333 16.001V16C29.325 8.64 23.36 2.675 16.001 2.667H16ZM26.577 14.685H22.614C22.412 11.572 21.577 8.695 20.237 6.124L20.294 6.243C23.673 7.763 26.088 10.895 26.572 14.632L26.578 14.685H26.577ZM16.692 5.368C18.559 7.973 19.761 11.169 19.991 14.631L19.994 14.686H12.046C12.419 11.175 13.617 8.005 15.439 5.29L15.392 5.364C15.595 5.352 15.792 5.334 16 5.334C16.234 5.334 16.463 5.352 16.692 5.368ZM11.859 6.168C10.537 8.638 9.658 11.534 9.41 14.607L9.405 14.686H5.423C5.918 10.836 8.399 7.664 11.789 6.198L11.858 6.171L11.859 6.168ZM5.43 17.352H9.388C9.595 20.451 10.427 23.312 11.759 25.871L11.703 25.752C8.338 24.237 5.931 21.124 5.436 17.407L5.43 17.352ZM15.304 26.631C13.447 24.036 12.247 20.854 12.011 17.408L12.008 17.352H19.952C19.576 20.847 18.384 24.004 16.572 26.711L16.619 26.636C16.415 26.647 16.219 26.667 16.007 26.667C15.766 26.667 15.535 26.647 15.304 26.631H15.304ZM20.145 25.831C21.46 23.372 22.335 20.49 22.587 17.431L22.592 17.352H26.57C26.064 21.184 23.591 24.339 20.214 25.801L20.145 25.828L20.145 25.831Z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-list">
          <path
            d="M10.6666 6.69056h16v2.66656h-16v-2.66656zm-5.33312 0H8v2.66656h-2.66656v-2.66656zm0 8H8v2.66656h-2.66656v-2.66656zm0 8H8v2.66656h-2.66656v-2.66656zm5.33312 -8h16v2.66656h-16v-2.66656zm0 8h16v2.66656h-16v-2.66656z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-grid">
          <path
            d="M6.667 4H12C13.473 4 14.667 5.194 14.667 6.667V12C14.667 13.473 13.473 14.667 12 14.667H6.667C5.194 14.667 4 13.473 4 12V6.667C4 5.194 5.194 4 6.667 4ZM6.667 6.667V12H12V6.667Z"
          ></path>
          <path
            d="M20 4H25.333C26.806 4 28 5.194 28 6.667V12C28 13.473 26.806 14.667 25.333 14.667H20C18.527 14.667 17.333 13.473 17.333 12V6.667C17.333 5.194 18.527 4 20 4ZM20 6.667V12H25.333V6.667Z"
          ></path>
          <path
            d="M6.667 17.333H12C13.473 17.333 14.667 18.527 14.667 20V25.333C14.667 26.806 13.473 28 12 28H6.667C5.194 28 4 26.806 4 25.333V20C4 18.527 5.194 17.333 6.667 17.333ZM6.667 20V25.333H12V20Z"
          ></path>
          <path
            d="M20 17.333H25.333C26.806 17.333 28 18.527 28 20V25.333C28 26.806 26.806 28 25.333 28H20C18.527 28 17.333 26.806 17.333 25.333V20C17.333 18.527 18.527 17.333 20 17.333ZM20 20V25.333H25.333V20Z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-checklist">
          <path
            d="M4 9.054L7.482 12.536L13.333 6.684L11.447 4.798L7.482 8.764L5.886 7.168Z"
          ></path>
          <path d="M16 7.333H28V10H16Z"></path>
          <path
            d="M4 17.869H13.333V27.202H4ZM6.667 20.536V24.536H10.667V20.536Z"
          ></path>
          <path d="M16 21.202H28V23.869H16Z"></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-location-android">
          <path
            d="M18.6666 16a2.66656 2.66656 0 1 1 -5.33312 0 2.66656 2.66656 0 0 1 5.33312 0z"
          ></path>
          <path
            d="M29.3334 14.6666h-2.75872c-0.62656 -4.83072 -4.41056 -8.61472 -9.18784 -9.23552l-0.05344 -0.00576V2.66656h-2.66656v2.75872c-4.83072 0.62656 -8.61472 4.41056 -9.23552 9.18784l-0.00576 0.05344H2.66688v2.66656h2.75872c0.62656 4.83072 4.41056 8.61472 9.18784 9.23552l0.05344 0.00576v2.75872h2.66656v-2.75872c4.83072 -0.62656 8.61472 -4.41056 9.23552 -9.18784l0.00576 -0.05344h2.75872zM16 24c-4.416 -0.00544 -7.99456 -3.584 -8 -7.99936V16c0.00544 -4.416 3.584 -7.99456 7.99936 -8H16c4.416 0.00544 7.99456 3.584 8 7.99936V16c-0.00544 4.416 -3.584 7.99456 -7.99936 8H16z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-location-ios">
          <path
            d="M16.9706 28h-0.00736a1.3328 1.3328 0 0 1 -1.18912 -0.72992l-0.00352 -0.00768 -3.68 -7.36 -7.36 -3.68a1.336 1.336 0 0 1 -0.7376 -1.19296l0.00032 -0.02592v0.00128a1.33408 1.33408 0 0 1 0.77312 -1.18592l0.00832 -0.00352 21.3334 -9.696a1.31136 1.31136 0 0 1 0.55488 -0.12096c0.36672 0 0.69888 0.14816 0.93984 0.38752a1.328 1.328 0 0 1 0.38752 0.93984c0 0.20128 -0.04448 0.392 -0.12448 0.56288l0.00352 -0.00832 -9.696 21.3334a1.336 1.336 0 0 1 -1.18816 0.78144h-0.00128zM8.42944 15.0867l5.25856 2.62944a1.35168 1.35168 0 0 1 0.59232 0.58848l0.00352 0.00768 2.62944 5.25856 7.06656 -15.552z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-mail">
          <path
            d="M26.6666 5.33472H5.33312a2.66656 2.66656 0 0 0 -2.66656 2.66656v16a2.66656 2.66656 0 0 0 2.66656 2.66656h21.3334a2.66656 2.66656 0 0 0 2.66656 -2.66656v-16a2.66656 2.66656 0 0 0 -2.66656 -2.66656zm-2.14656 2.66656L16 15.5747 7.48 8.00128zm-19.1866 16V9.65984l9.78144 8.69472a1.31936 1.31936 0 0 0 0.88352 0.33728h0.00384c0.33984 0 0.64992 -0.128 0.88448 -0.33856l-0.00128 0.00096 9.78144 -8.69472v14.3414z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-map">
          <path
            d="M28.7802 4.25056a1.32928 1.32928 0 0 0 -1.20928 -0.17856l0.00928 -0.00288L20 6.59456l-7.57856 -2.52544a1.32352 1.32352 0 0 0 -0.85216 0.00256l0.00928 -0.00288 -8 2.66656c-0.53408 0.18176 -0.91168 0.67872 -0.912 1.264v18.6666a1.33344 1.33344 0 0 0 1.33344 1.33344h0.00064c0.15072 0 0.29504 -0.02528 0.42976 -0.072l-0.00928 0.00288 7.57856 -2.52544 7.57856 2.52544a1.32352 1.32352 0 0 0 0.85216 -0.00256l-0.00928 0.00288 8 -2.66656c0.53408 -0.18176 0.91168 -0.67872 0.912 -1.264V5.33216a1.3312 1.3312 0 0 0 -0.54944 -1.07872l-0.00384 -0.00256zm-15.4467 2.93216L18.6669 8.96v15.8573L13.3334 23.04zm-8 1.77856l5.33344 -1.77856V23.04l-5.33344 1.776zm21.3331 14.0774l-5.33344 1.77856V8.96l5.33344 -1.776z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-map-pin">
          <path
            d="M16.0013 8.024c-2.9456 0 -5.33344 2.38784 -5.33344 5.33344 0 2.9456 2.38784 5.33344 5.33344 5.33344 2.9456 0 5.33344 -2.38784 5.33344 -5.33344 0 -2.9456 -2.38784 -5.33344 -5.33344 -5.33344zm0 8a2.66656 2.66656 0 0 1 0 -5.33312 2.66656 2.66656 0 0 1 0 5.33312z"
          ></path>
          <path
            d="M26.8093 11.952C26.0109 7.12768 22.1718 3.39776 17.3581 2.77248l-0.05536 -0.00576a10.64 10.64 0 0 0 -1.28032 -0.07488 10.9491 10.9491 0 0 0 -7.30144 2.77536l0.0112 -0.0096c-2.25152 2.01248 -3.6624 4.92448 -3.664 8.1664v0.00032a10.872 10.872 0 0 0 1.49472 5.51808l-0.02784 -0.05152c1.72544 2.984 8.24384 9.59072 8.52128 9.86656a1.32928 1.32928 0 0 0 0.94784 0.4h0.00032a1.32992 1.32992 0 0 0 0.94784 -0.39968l0.00032 -0.00032c0.27456 -0.27872 6.73344 -6.84672 8.46656 -9.776 0.95552 -1.58688 1.52096 -3.50272 1.52096 -5.5504 0 -0.59264 -0.04736 -1.1744 -0.13856 -1.74144l0.00832 0.0624zM23.1226 17.8266c-2.2608 3.06368 -4.59616 5.76864 -7.11968 8.28512l-0.0016 0.0016a68.6115 68.6115 0 0 1 -7.02592 -8.16544l-0.13408 -0.192c-0.69504 -1.184 -1.10528 -2.60736 -1.10528 -4.12672v-0.00704 0.00032c0.00192 -2.4512 1.06848 -4.6528 2.76256 -6.16768l0.008 -0.00704a8.12384 8.12384 0 0 1 5.45472 -2.09056h0.03072 -0.0016 0.00064c0.3536 0 0.7024 0.0208 1.04512 0.06144l-0.0416 -0.00384c3.67648 0.47456 6.57568 3.29088 7.17056 6.8848l0.00672 0.04832c0.06336 0.384 0.09952 0.82688 0.09952 1.27776 0 1.54944 -0.42688 2.99936 -1.1696 4.2384l0.0208 -0.03744z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-menu">
          <path
            d="M5.33344 6.69056h21.3334v2.66656H5.33344v-2.66656zm0 8h21.3334v2.66656H5.33344v-2.66656zm0 8h21.3334v2.66656H5.33344v-2.66656z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-minus">
          <path d="M5.33344 14.6666v2.66656h21.3334v-2.66656z"></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-parking">
          <path
            d="M26.6666 2.66656H5.33312a2.66656 2.66656 0 0 0 -2.66656 2.66656v21.3334a2.66656 2.66656 0 0 0 2.66656 2.66656h21.3334a2.66656 2.66656 0 0 0 2.66656 -2.66656V5.33312a2.66656 2.66656 0 0 0 -2.66656 -2.66656zm0 24H5.33312V5.33312h21.3334z"
          ></path>
          <path
            d="M14.8813 17.9238h1.21344a5.7232 5.7232 0 0 0 3.83968 -1.1504l-0.0144 0.01056a4.06592 4.06592 0 0 0 1.34464 -3.30432l0.00064 0.01216a3.79648 3.79648 0 0 0 -1.26176 -3.11136l-0.00384 -0.0032a5.62112 5.62112 0 0 0 -3.296 -1.0576c-0.1376 0 -0.27392 0.0048 -0.40864 0.0144l0.01824 -0.00096H12.0547v13.3334h2.82656zm0 -6.27456h1.28672a2.52736 2.52736 0 0 1 1.7072 0.47968l-0.00704 -0.00512a1.83936 1.83936 0 0 1 0.54368 1.4736l0.00032 -0.00672a1.8 1.8 0 0 1 -0.64512 1.4976l-0.00288 0.00224a3.07104 3.07104 0 0 1 -1.72448 0.52448 3.0848 3.0848 0 0 1 -0.23808 -0.00896l0.01056 0.00064h-0.93344z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-pause">
          <path
            d="M9.33344 6.66656H12A1.33344 1.33344 0 0 1 13.3334 8v16a1.33344 1.33344 0 0 1 -1.33344 1.33344h-2.66656A1.33344 1.33344 0 0 1 8 24V8a1.33344 1.33344 0 0 1 1.33344 -1.33344zm10.6666 0.024h2.66656A1.33344 1.33344 0 0 1 24 8.024v16a1.33344 1.33344 0 0 1 -1.33344 1.33344H20a1.33344 1.33344 0 0 1 -1.33344 -1.33344v-16a1.33344 1.33344 0 0 1 1.33344 -1.33344z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-play">
          <path
            d="M12 25.3574a1.33344 1.33344 0 0 1 -1.33344 -1.33344v-16a1.33344 1.33344 0 0 1 2.13664 -1.06432l-0.0032 -0.00256 10.6666 8a1.33248 1.33248 0 0 1 0.00352 2.13088l-0.0032 0.00256 -10.6666 8a1.32448 1.32448 0 0 1 -0.8 0.26656z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-plus">
          <path
            d="M26.6666 14.6666H17.3331V5.33312h-2.66656v9.33344H5.33312v2.66656h9.33344v9.33344h2.66656V17.3331h9.33344z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-search">
          <path
            d="M26.2758 24.4147L21.944 20.0813C23.2266 18.423 24 16.3139 24 14.0246v-0.00064c0 -5.52288 -4.47712 -10 -10 -10S4 8.50112 4 14.024s4.47712 10 10 10h0.0016a9.90144 9.90144 0 0 0 6.08 -2.07424l-0.02304 0.01696 4.33216 4.33344zM6.66656 14.024c0.00448 -4.04832 3.28512 -7.32864 7.3328 -7.33344h0.00032c4.04832 0.00448 7.32864 3.28512 7.33344 7.3328v0.00032C21.3286 18.072 18.048 21.3523 14.0003 21.3571h-0.00032c-4.04832 -0.00448 -7.32864 -3.28512 -7.33344 -7.3328v-0.00032z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-settings">
          <path
            d="M27.237 17.867l-0.679-0.392c0.069-0.444 0.108-0.955 0.108-1.476s-0.039-1.032-0.115-1.532l0.007 0.056 0.679-0.392c0.803-0.469 1.334-1.328 1.334-2.31 0-0.491-0.133-0.952-0.365-1.347l0.007 0.012-1.333-2.309c-0.471-0.802-1.33-1.332-2.312-1.332-0.49 0-0.948 0.132-1.343 0.361l0.013-0.007-0.683 0.4c-0.736-0.582-1.579-1.081-2.484-1.458l-0.070-0.026v-0.783c0-1.473-1.194-2.667-2.667-2.667v0h-2.667c-1.473 0-2.667 1.194-2.667 2.667v0 0.783c-0.976 0.402-1.818 0.899-2.577 1.497l0.023-0.017-0.684-0.4c-0.381-0.221-0.839-0.352-1.327-0.352-0.983 0-1.842 0.529-2.308 1.318l-0.007 0.012-1.333 2.309c-0.225 0.383-0.358 0.843-0.358 1.334 0 0.982 0.531 1.84 1.321 2.303l0.012 0.007 0.679 0.392c-0.069 0.444-0.108 0.955-0.108 1.476s0.039 1.032 0.115 1.532l-0.007-0.056-0.679 0.392c-0.803 0.469-1.334 1.328-1.334 2.31 0 0.491 0.133 0.952 0.365 1.347l-0.007-0.012 1.333 2.309c0.471 0.802 1.33 1.332 2.312 1.332 0.49 0 0.948-0.132 1.343-0.361l-0.013 0.007 0.683-0.4c0.736 0.584 1.579 1.084 2.484 1.463l0.070 0.026v0.783c0 1.473 1.194 2.667 2.667 2.667v0h2.667c1.473 0 2.667-1.194 2.667-2.667v0-0.783c0.976-0.402 1.818-0.899 2.577-1.497l-0.023 0.017 0.684 0.4c0.381 0.222 0.84 0.353 1.328 0.353 0.982 0 1.841-0.53 2.306-1.319l0.007-0.012 1.333-2.309c0.227-0.384 0.36-0.845 0.36-1.338 0-0.983-0.532-1.842-1.324-2.304l-0.013-0.007zM23.771 14.147c0.143 0.556 0.225 1.195 0.225 1.852s-0.082 1.296-0.237 1.905l0.012-0.053-0.228 0.965 2.357 1.361-1.333 2.309-2.359-1.361-0.723 0.68c-0.879 0.835-1.952 1.474-3.143 1.842l-0.057 0.015-0.952 0.284v2.72h-2.667v-2.72l-0.953-0.283c-1.248-0.384-2.321-1.023-3.204-1.86l-0.719-0.677-2.359 1.361-1.333-2.309 2.357-1.361-0.228-0.965c-0.143-0.556-0.225-1.195-0.225-1.852s0.082-1.296 0.237-1.905l-0.012 0.053 0.228-0.965-2.357-1.361 1.333-2.309 2.359 1.361 0.723-0.68c0.879-0.835 1.952-1.474 3.143-1.842l0.057-0.015 0.953-0.283v-2.72h2.667v2.72l0.953 0.283c1.248 0.384 2.321 1.023 3.204 1.86l0.719 0.677 2.359-1.361 1.333 2.309-2.355 1.361z"
          ></path>
          <path
            d="M16 10.667c-2.946 0-5.333 2.388-5.333 5.333s2.388 5.333 5.333 5.333c2.946 0 5.333-2.388 5.333-5.333v0c0-2.946-2.388-5.333-5.333-5.333v0zM16 18.667c-1.473 0-2.667-1.194-2.667-2.667s1.194-2.667 2.667-2.667c1.473 0 2.667 1.194 2.667 2.667v0c0 1.473-1.194 2.667-2.667 2.667v0z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-share-ios">
          <path
            d="M24 17.3334v8H8v-8h-2.66656v8A2.66656 2.66656 0 0 0 8 28h16a2.66656 2.66656 0 0 0 2.66656 -2.66656v-8z"
          ></path>
          <path
            d="M14.6666 8.552V20h2.66656V8.552l3.05728 3.05728 1.88544 -1.88544L16.9424 4.3904c-0.24128 -0.24128 -0.57472 -0.3904 -0.94272 -0.3904s-0.70144 0.14912 -0.94272 0.3904L9.72352 9.72384l1.88544 1.88544z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-shrink">
          <path
            d="M11.0573 19.0573L4 26.1146l1.88544 1.88544 7.05728 -7.05728v4.78144h2.66656v-8a1.33344 1.33344 0 0 0 -1.33344 -1.33344h-8v2.66656zm9.88544 -6.11456L28 5.88544 26.1146 4 19.0573 11.0573V6.27584h-2.66656v8a1.33344 1.33344 0 0 0 1.33344 1.33344h8v-2.66656z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-sign-out">
          <path
            d="M14.6666 24h-8V8h8v-2.66656h-8A2.66656 2.66656 0 0 0 4 8v16a2.66656 2.66656 0 0 0 2.66656 2.66656h8z"
          ></path>
          <path
            d="M23.448 14.6666H12v2.66656h11.448l-3.05728 3.05728 1.88544 1.88544L27.6096 16.9424c0.24128 -0.24128 0.3904 -0.57472 0.3904 -0.94272s-0.14912 -0.70144 -0.3904 -0.94272L22.2762 9.72352l-1.88544 1.88544z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-star">
          <path
            d="M22.592 27.5027a1.32704 1.32704 0 0 1 -0.6272 -0.1568l0.00704 0.00352 -5.97216 -3.14016 -5.97216 3.14016a1.31424 1.31424 0 0 1 -0.62048 0.15328c-0.2944 0 -0.56672 -0.09536 -0.7872 -0.25728l0.00384 0.00256a1.3328 1.3328 0 0 1 -0.53216 -1.312l-0.00128 0.008 1.14144 -6.65056 -4.832 -4.71072a1.33024 1.33024 0 0 1 -0.40256 -0.95456c0 -0.1472 0.024 -0.28896 0.06784 -0.42144l-0.00256 0.00928a1.33728 1.33728 0 0 1 1.0688 -0.9056l0.00736 -0.00096 6.67744 -0.96928 2.98656 -6.05056a1.38496 1.38496 0 0 1 2.3856 -0.0064l0.00352 0.0064 2.98656 6.05056 6.67744 0.96928a1.3376 1.3376 0 0 1 1.07328 0.89728l0.00256 0.00928a1.33056 1.33056 0 0 1 -0.33696 1.3664l-0.00032 0.00032 -4.832 4.71072 1.14144 6.65056a1.33088 1.33088 0 0 1 -0.5296 1.30112l-0.00384 0.00256a1.33184 1.33184 0 0 1 -0.77824 0.25472h-0.00032z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-telephone">
          <path
            d="M24.3053 28.1507c-0.07168 0.00096 -0.15648 0.00128 -0.24128 0.00128 -5.58848 0 -10.6531 -2.2432 -14.3427 -5.8784l0.00256 0.00256c-3.63744 -3.7376 -5.88032 -8.848 -5.88032 -14.4822 0 -0.93408 0.06176 -1.85408 0.18112 -2.75552l-0.01152 0.1056 0.16384 -1.144h4.40128c1.864 0 3.43008 1.27488 3.87392 3.00032l0.00608 0.02784 0.46144 1.85056c0.07424 0.28928 0.11712 0.62144 0.11712 0.96352 0 1.10624 -0.44672 2.10816 -1.16928 2.83552L8.47424 16.071c1.65568 3.22848 4.21952 5.79232 7.35328 7.40384l0.09472 0.04416 3.39328 -3.39328a4.0192 4.0192 0 0 1 2.83936 -1.16928c0.34048 0 0.67136 0.04224 0.9872 0.1216l-0.02784 -0.00608 1.84928 0.46272c1.7568 0.44704 3.03552 2.01472 3.03584 3.88128v4.40672l-1.144 0.16416c-0.73952 0.10464 -1.5936 0.16416 -2.46176 0.16416l-0.09376 -0.00032h0.0048z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-time">
          <path
            d="M16 2.69056c-7.36384 0 -13.3334 5.9696 -13.3334 13.3334S8.63616 29.3574 16 29.3574s13.3334 -5.9696 13.3334 -13.3334S23.3638 2.69056 16 2.69056zm0 24c-5.88832 -0.00672 -10.6598 -4.77824 -10.6666 -10.6659v-0.00064c0.00672 -5.88832 4.77824 -10.6598 10.6659 -10.6666H16c5.88832 0.00672 10.6598 4.77824 10.6666 10.6659v0.00064c-0.00672 5.88832 -4.77824 10.6598 -10.6659 10.6666H16z"
          ></path>
          <path
            d="M17.3334 9.35744h-2.66656v6.66656a1.33344 1.33344 0 0 0 1.33344 1.33344h6.66656v-2.66656H17.3334z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-timer">
          <path
            d="M10.19 2.667c-3.345 1.466-6.056 4.174-7.524 7.515l1.79 0.758c1.274-2.894 3.595-5.212 6.492-6.485l-0.758-1.788zM16.015 4.364c-6.424 0-11.65 5.22-11.65 11.636s5.226 11.636 11.65 11.636c6.424 0 11.65-5.22 11.65-11.636s-5.226-11.636-11.65-11.636zM16.015 6.303c5.373 0 9.708 4.33 9.708 9.697s-4.335 9.697-9.708 9.697c-5.374 0-9.708-4.329-9.708-9.697s4.334-9.697 9.708-9.697zM15.044 8.242v8.727h6.796v-1.939h-4.854v-6.788h-1.942zM27.574 21.061c-1.274 2.894-3.595 5.212-6.492 6.485l0.759 1.788c3.345-1.466 6.026-4.174 7.493-7.515l-1.76-0.757z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-trash">
          <path
            d="M12 12h2.66656v10.6666H12V12zm5.33344 0H20v10.6666h-2.66656V12z"
          ></path>
          <path
            d="M28 6.66656H22.6666l-0.59616 -1.192c-0.44736 -0.88128 -1.3472 -1.47456 -2.38528 -1.47456H12.3146a2.6656 2.6656 0 0 0 -2.37824 1.45952l-0.00704 0.01504 -0.59616 1.192H3.99968v2.66656h2.66656v16a2.66656 2.66656 0 0 0 2.66656 2.66656h13.3334a2.66656 2.66656 0 0 0 2.66656 -2.66656v-16h2.66656zM22.6666 25.3334H9.33312v-16h13.3334z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-upload">
          <path
            d="M24 14.6666v-8H8v8h-2.66656v-8A2.66656 2.66656 0 0 1 8 4h16a2.66656 2.66656 0 0 1 2.66656 2.66656v8z"
          ></path>
          <path
            d="M14.6666 16.552V28h2.66656V16.552l3.05728 3.05728 1.88544 -1.88544L16.9424 12.3904c-0.24128 -0.24128 -0.57472 -0.3904 -0.94272 -0.3904s-0.70144 0.14912 -0.94272 0.3904L9.72352 17.7238l1.88544 1.88544z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-user">
          <path
            d="M26.2186 23.8547l-2.66656 -4c-0.4848 -0.72032 -1.29728 -1.18784 -2.21856 -1.18784H10.6669a2.664 2.664 0 0 0 -2.21248 1.17856l-0.00608 0.0096 -2.66656 4a2.64256 2.64256 0 0 0 -0.44832 1.48c0 0.46048 0.1168 0.89344 0.32192 1.27136l-0.00704 -0.01408c0.45888 0.84448 1.3392 1.408 2.35104 1.408h16.0013a2.6656 2.6656 0 0 0 2.34368 -1.39392l0.00704 -0.01408a2.6336 2.6336 0 0 0 0.31552 -1.25856 2.65472 2.65472 0 0 0 -0.45376 -1.48832l0.00608 0.0096zM8 25.3334l2.66656 -4h10.6666l2.66656 4zM16 16c3.3136 0 6 -2.6864 6 -6S19.3136 4 16 4s-6 2.6864 -6 6S12.6864 16 16 16zm0 -9.33344c1.84096 0 3.33344 1.49248 3.33344 3.33344S17.841 13.3334 16 13.3334 12.6666 11.841 12.6666 10 14.159 6.66656 16 6.66656z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-user-avatar">
          <path
            d="M7.8 22.8c1.133-0.867 2.4-1.55 3.8-2.050s2.867-0.75 4.4-0.75c1.533 0 3 0.25 4.4 0.75s2.667 1.183 3.8 2.050c0.778-0.911 1.383-1.944 1.817-3.1s0.65-2.389 0.65-3.7c0-2.956-1.039-5.472-3.117-7.55s-4.594-3.117-7.55-3.117c-2.956 0-5.472 1.039-7.55 3.117s-3.117 4.594-3.117 7.55c0 1.311 0.217 2.544 0.65 3.7s1.039 2.189 1.817 3.1zM12.683 15.983c-0.9-0.9-1.35-2.006-1.35-3.317s0.45-2.417 1.35-3.317 2.006-1.35 3.317-1.35c1.311 0 2.417 0.45 3.317 1.35s1.35 2.006 1.35 3.317c0 1.311-0.45 2.417-1.35 3.317s-2.006 1.35-3.317 1.35c-1.311 0-2.417-0.45-3.317-1.35zM16 29.333c-1.844 0-3.578-0.35-5.2-1.050s-3.033-1.65-4.233-2.85c-1.2-1.2-2.15-2.611-2.85-4.233s-1.050-3.356-1.050-5.2c0-1.844 0.35-3.578 1.050-5.2s1.65-3.033 2.85-4.233c1.2-1.2 2.611-2.15 4.233-2.85s3.356-1.050 5.2-1.050c1.844 0 3.578 0.35 5.2 1.050s3.033 1.65 4.233 2.85c1.2 1.2 2.15 2.611 2.85 4.233s1.050 3.356 1.050 5.2c0 1.844-0.35 3.578-1.050 5.2s-1.65 3.033-2.85 4.233c-1.2 1.2-2.611 2.15-4.233 2.85s-3.356 1.050-5.2 1.050zM19.333 26.15c1.044-0.344 2-0.839 2.867-1.483-0.867-0.644-1.822-1.139-2.867-1.483s-2.156-0.517-3.333-0.517c-1.178 0-2.289 0.172-3.333 0.517s-2 0.839-2.867 1.483c0.867 0.644 1.822 1.139 2.867 1.483s2.156 0.517 3.333 0.517 2.289-0.172 3.333-0.517zM17.433 14.1c0.378-0.378 0.567-0.856 0.567-1.433s-0.189-1.056-0.567-1.433c-0.378-0.378-0.856-0.567-1.433-0.567s-1.056 0.189-1.433 0.567c-0.378 0.378-0.567 0.856-0.567 1.433s0.189 1.056 0.567 1.433c0.378 0.378 0.856 0.567 1.433 0.567s1.056-0.189 1.433-0.567z"
          ></path>
        </symbol>
        <symbol id="m-icon-warning" viewBox="0 0 32 32">
          <path
            d="M10.979 6.475c2.23-3.628 7.403-3.741 9.817-0.34l0.225 0.34 0.031 0.053 7.769 14.186c0.512 0.851 0.986 2.069 0.611 3.365-0.712 2.455-2.975 4.254-5.662 4.254h-15.539c-2.687 0-4.95-1.799-5.662-4.254-0.376-1.296 0.098-2.514 0.611-3.365l7.768-14.186c0.010-0.018 0.021-0.036 0.031-0.053zM18.909 7.752c-1.341-2.158-4.477-2.158-5.819 0l-7.749 14.147c-0.010 0.018-0.019 0.036-0.030 0.053-0.38 0.619-0.471 1.104-0.373 1.44 0.415 1.431 1.735 2.474 3.293 2.474h15.539c1.558 0 2.878-1.043 3.293-2.474 0.098-0.336 0.007-0.822-0.373-1.44-0.011-0.017-0.020-0.035-0.030-0.053l-7.75-14.147z"
          ></path>
          <path d="M14.767 17.233h2.467v-7.4h-2.467v7.4z"></path>
          <path
            d="M16 19.392c-0.851 0-1.542 0.69-1.542 1.542s0.69 1.542 1.542 1.542c0.852 0 1.542-0.69 1.542-1.542s-0.69-1.542-1.542-1.542z"
          ></path>
        </symbol>
        <symbol id="m-icon-alert-filled" viewBox="0 0 32 32">
          <path
            d="M16 2.667c-7.363 0-13.333 5.971-13.333 13.333s5.971 13.333 13.333 13.333c7.363 0 13.333-5.971 13.333-13.333s-5.971-13.333-13.333-13.333zM14.667 9.333h2.667v8h-2.667v-8zM16 23c-0.92 0-1.667-0.747-1.667-1.667s0.747-1.667 1.667-1.667c0.92 0 1.667 0.747 1.667 1.667s-0.747 1.667-1.667 1.667z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-web">
          <path
            d="M16 2.672C8.63968 2.68032 2.67488 8.6448 2.66656 16.0045v0.00064c0.00832 7.36032 5.9728 13.3251 13.3325 13.3334h0.00064C23.36 29.3302 29.3248 23.3658 29.3331 16.0061v-0.00064C29.3248 8.64512 23.3603 2.68032 16.0006 2.672H16zm10.5773 12.0186H22.6134c-0.20128 -3.1136 -1.03616 -5.99008 -2.37632 -8.56128l0.05632 0.11872c3.37984 1.52 5.79456 4.65184 6.2784 8.38912l0.00576 0.05344zM16.6922 5.37344c1.86624 2.6048 3.06848 5.80064 3.29856 9.26272l0.00288 0.05472H12.0458c0.37344 -3.51008 1.57088 -6.68032 3.3936 -9.39584l-0.04704 0.07456c0.20256 -0.01184 0.4 -0.03072 0.608 -0.03072 0.23328 0 0.46272 0.01856 0.69184 0.03456zm-4.8336 0.8c-1.32192 2.46944 -2.20032 5.36544 -2.44832 8.43936l-0.00512 0.07808H5.4224c0.49568 -3.84928 2.97632 -7.02112 6.36672 -8.488l0.06912 -0.02656zm-6.42912 11.184h3.95872c0.2064 3.09856 1.03872 5.95968 2.3712 8.51904l-0.05632 -0.11904c-3.36512 -1.5152 -5.77184 -4.62816 -6.26752 -8.34528l-0.00608 -0.05472zm9.87456 9.2784c-1.8576 -2.59488 -3.05664 -5.77632 -3.2928 -9.2224l-0.0032 -0.05632H19.952c-0.37632 3.49568 -1.56832 6.65248 -3.38048 9.35904l0.04736 -0.07488c-0.20416 0.01056 -0.4 0.03072 -0.61184 0.03072 -0.24128 0 -0.472 -0.02016 -0.70272 -0.03616zm4.84128 -0.8c1.31456 -2.45856 2.19008 -5.34048 2.44128 -8.39936l0.00512 -0.07904h3.97856c-0.50624 3.832 -2.97952 6.98656 -6.35616 8.44928l-0.06912 0.02656z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-youtube">
          <path
            d="M28.776 9.6c-0.31456 -1.15648 -1.20352 -2.05024 -2.33376 -2.3664l-0.02368 -0.00576C24.3386 6.66656 16 6.66656 16 6.66656s-8.33856 0 -10.4186 0.56128c-1.1536 0.3216 -2.04256 1.21536 -2.352 2.34848l-0.00544 0.02336C2.86912 11.4752 2.66592 13.633 2.66592 15.8378l0.00064 0.23392v-0.01184l-0.00064 0.22176c0 2.20512 0.2032 4.36256 0.59136 6.45504l-0.03328 -0.21696c0.31424 1.15712 1.2032 2.05152 2.33376 2.36768l0.02368 0.00576C7.66144 25.4544 16 25.4544 16 25.4544s8.33856 0 10.4186 -0.56128c1.15392 -0.32192 2.0432 -1.21632 2.352 -2.34976l0.00544 -0.02336c0.35488 -1.87552 0.55808 -4.03328 0.55808 -6.23808l-0.00064 -0.23392v0.01184l0.00064 -0.22176c0 -2.20512 -0.2032 -4.36256 -0.59136 -6.45504l0.03328 0.21696zM13.2733 20.0253V12.096l6.96928 3.96544z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-calendar-day">
          <path
            d="M25.333 5.333h-2.667v-2.667h-2.667v2.667h-8v-2.667h-2.667v2.667h-2.667c-1.473 0-2.667 1.194-2.667 2.667v0 18.667c0 1.473 1.194 2.667 2.667 2.667v0h18.667c1.473 0 2.667-1.194 2.667-2.667v0-18.667c0-1.473-1.194-2.667-2.667-2.667v0zM25.333 26.667h-18.667v-13.333h18.667zM25.333 10.667h-18.667v-2.667h18.667z"
          ></path>
          <path d="M17.333 18.667h5.333v5.333h-5.333v-5.333z"></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-calendar-down">
          <path
            d="M11.067 19.541l4.191 4.192c0.19 0.19 0.452 0.307 0.742 0.307s0.552-0.117 0.742-0.307l4.192-4.192-1.484-1.484-2.4 2.407v-4.464h-2.099v4.464l-2.407-2.407z"
          ></path>
          <path
            d="M25.333 5.357h-2.667v-2.667h-2.667v2.667h-8v-2.667h-2.667v2.667h-2.667c-1.473 0-2.667 1.194-2.667 2.667v0 18.667c0 1.473 1.194 2.667 2.667 2.667v0h18.667c1.473 0 2.667-1.194 2.667-2.667v0-18.667c0-1.473-1.194-2.667-2.667-2.667v0zM25.333 26.691h-18.667v-13.333h18.667zM25.333 10.691h-18.667v-2.667h18.667z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-calendar-left">
          <path
            d="M16.48 24.964l-4.196-4.199c-0.19-0.19-0.307-0.452-0.307-0.742s0.117-0.552 0.307-0.742l4.197-4.197 1.485 1.484-2.4 2.4h4.464v2.099h-4.472l2.4 2.407z"
          ></path>
          <path
            d="M25.333 5.357h-2.667v-2.667h-2.667v2.667h-8v-2.667h-2.667v2.667h-2.667c-1.473 0-2.667 1.194-2.667 2.667v0 18.667c0 1.473 1.194 2.667 2.667 2.667v0h18.667c1.473 0 2.667-1.194 2.667-2.667v0-18.667c0-1.473-1.194-2.667-2.667-2.667v0zM25.333 26.691h-18.667v-13.333h18.667zM25.333 10.691h-18.667v-2.667h18.667z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-calendar-right">
          <path
            d="M15.519 24.964l4.197-4.199c0.19-0.19 0.307-0.452 0.307-0.742s-0.117-0.552-0.307-0.742l-4.197-4.197-1.485 1.484 2.407 2.4h-4.464v2.099h4.464l-2.407 2.4z"
          ></path>
          <path
            d="M25.333 5.357h-2.667v-2.667h-2.667v2.667h-8v-2.667h-2.667v2.667h-2.667c-1.473 0-2.667 1.194-2.667 2.667v0 18.667c0 1.473 1.194 2.667 2.667 2.667v0h18.667c1.473 0 2.667-1.194 2.667-2.667v0-18.667c0-1.473-1.194-2.667-2.667-2.667v0zM25.333 26.691h-18.667v-13.333h18.667zM25.333 10.691h-18.667v-2.667h18.667z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-calendar-up">
          <path
            d="M12.544 21.989l2.407-2.407v4.464h2.1v-4.464l2.4 2.407 1.483-1.484-4.197-4.197c-0.19-0.19-0.452-0.307-0.742-0.307s-0.552 0.117-0.742 0.307v0l-4.185 4.197z"
          ></path>
          <path
            d="M25.333 5.357h-2.667v-2.667h-2.667v2.667h-8v-2.667h-2.667v2.667h-2.667c-1.473 0-2.667 1.194-2.667 2.667v0 18.667c0 1.473 1.194 2.667 2.667 2.667v0h18.667c1.473 0 2.667-1.194 2.667-2.667v0-18.667c0-1.473-1.194-2.667-2.667-2.667v0zM25.333 26.691h-18.667v-13.333h18.667zM25.333 10.691h-18.667v-2.667h18.667z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-calendar-week">
          <path
            d="M25.333 5.333h-2.667v-2.667h-2.667v2.667h-8v-2.667h-2.667v2.667h-2.667c-1.473 0-2.667 1.194-2.667 2.667v0 18.667c0 1.473 1.194 2.667 2.667 2.667v0h18.667c1.473 0 2.667-1.194 2.667-2.667v0-18.667c0-1.473-1.194-2.667-2.667-2.667v0zM25.333 26.667h-18.667v-13.333h18.667zM25.333 10.667h-18.667v-2.667h18.667z"
          ></path>
          <path d="M9.333 16h13.333v2.667h-13.333v-2.667z"></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-calendar-weekend">
          <path
            d="M25.333 5.333h-2.667v-2.667h-2.667v2.667h-8v-2.667h-2.667v2.667h-2.667c-1.473 0-2.667 1.194-2.667 2.667v0 18.667c0 1.473 1.194 2.667 2.667 2.667v0h18.667c1.473 0 2.667-1.194 2.667-2.667v0-18.667c0-1.473-1.194-2.667-2.667-2.667v0zM25.333 26.667h-18.667v-13.333h18.667zM25.333 10.667h-18.667v-2.667h18.667z"
          ></path>
          <path d="M20 16h2.667v8h-2.667v-8z"></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-calendar-check">
          <path
            d="M12 2.667V5.333H20V2.667H22.668V5.333H25.335C26.808 5.334 28 6.528 28 8.001V26.668C28 28.141 26.806 29.335 25.333 29.335H6.667C5.194 29.335 4 28.141 4 26.668V8.001C4 6.528 5.194 5.333 6.667 5.333H9.333V2.667H12ZM6.668 13.335V26.668H25.335V13.335H6.668ZM22.815 16.68L14.602 25.28L9.721 20.413L11.615 18.519L14.401 21.32L20.922 14.786L22.815 16.68ZM6.668 8.001V10.668H25.335V8.001H6.668Z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-camera">
          <path
            d="M25.333 6.643h-2.667l-1.2-1.6c-0.492-0.651-1.264-1.067-2.133-1.067h-6.667c-0.869 0-1.642 0.416-2.128 1.060l-0.005 0.007-1.2 1.6h-2.667c-1.473 0-2.667 1.194-2.667 2.667v0 16c0 1.473 1.194 2.667 2.667 2.667v0h18.667c1.473 0 2.667-1.194 2.667-2.667v0-16c0-1.473-1.194-2.667-2.667-2.667v0zM25.333 25.309h-18.667v-16h4l2-2.667h6.667l2 2.667h4z"
          ></path>
          <path
            d="M16.948 10.716c-0.284-0.048-0.612-0.075-0.946-0.075-3.314 0-6 2.686-6 6s2.686 6 6 6c3.314 0 6-2.686 6-6 0-0.335-0.028-0.663-0.080-0.982l0.005 0.035c-0.421-2.562-2.416-4.557-4.944-4.972l-0.035-0.005zM16.829 19.875c-0.249 0.067-0.534 0.105-0.829 0.105-1.841 0-3.333-1.492-3.333-3.333s1.492-3.333 3.333-3.333c1.841 0 3.333 1.492 3.333 3.333 0 0.294-0.038 0.58-0.11 0.852l0.005-0.023c-0.312 1.179-1.221 2.088-2.377 2.395l-0.023 0.005z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-danger">
          <path
            d="M16 29.333c7.364 0 13.333-5.97 13.333-13.333s-5.97-13.333-13.333-13.333c-7.364 0-13.333 5.97-13.333 13.333v0c0 7.364 5.97 13.333 13.333 13.333v0zM16 5.333c5.891 0 10.667 4.776 10.667 10.667s-4.776 10.667-10.667 10.667c-5.891 0-10.667-4.776-10.667-10.667v0c0.007-5.888 4.778-10.66 10.666-10.667h0.001z"
          ></path>
          <path d="M17.333 17.333h-2.667v-8h2.667v8z"></path>
          <path
            d="M14.333 21.333c0-0.92 0.746-1.667 1.667-1.667s1.667 0.746 1.667 1.667c0 0.92-0.746 1.667-1.667 1.667s-1.667-0.746-1.667-1.667z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-danger-filled">
          <path
            d="M16 2.667c-7.363 0-13.333 5.971-13.333 13.333s5.971 13.333 13.333 13.333c7.363 0 13.333-5.971 13.333-13.333s-5.971-13.333-13.333-13.333zM14.667 9.333h2.667v8h-2.667v-8zM16 23c-0.92 0-1.667-0.747-1.667-1.667s0.747-1.667 1.667-1.667c0.92 0 1.667 0.747 1.667 1.667s-0.747 1.667-1.667 1.667z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-desk-bell">
          <path
            d="M17.333 20v-2.667h9.333c0-0.001 0-0.002 0-0.002 0-4.387-2.648-8.156-6.434-9.794l-0.069-0.027c0.087-0.252 0.137-0.542 0.137-0.844 0-1.473-1.194-2.667-2.667-2.667-0 0-0.001 0-0.001 0h-3.267c-0.001 0-0.002 0-0.003 0-1.473 0-2.667 1.194-2.667 2.667 0 0.301 0.050 0.591 0.142 0.861l-0.005-0.019c-3.853 1.666-6.5 5.433-6.5 9.819 0 0.002 0 0.004 0 0.005v-0h9.333v2.667h-10.667l2.059 6.176c0.364 1.069 1.359 1.824 2.53 1.824 0.001 0 0.002 0 0.004 0h14.824c0.001 0 0.002 0 0.003 0 1.171 0 2.166-0.755 2.524-1.805l0.005-0.019 2.051-6.176zM16 9.333c3.461 0.004 6.408 2.202 7.525 5.277l0.018 0.056h-15.085c1.135-3.132 4.082-5.329 7.542-5.333h0.001zM23.412 25.333h-14.823l-0.889-2.667h16.6z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-double-down">
          <path
            d="M17.139 17.164l8-8-1.887-1.885-7.057 7.057-7.056-7.057-1.887 1.885 8 8c0.241 0.242 0.575 0.391 0.943 0.391s0.702-0.149 0.943-0.391v0z"
          ></path>
          <path
            d="M17.139 24.331l8-8-1.887-1.885-7.057 7.057-7.056-7.057-1.887 1.885 8 8c0.241 0.242 0.575 0.391 0.943 0.391s0.702-0.149 0.943-0.391v0z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-double-left">
          <path
            d="M15.031 16.943l8 8 1.885-1.885-7.049-7.057 7.056-7.057-1.885-1.885-8 8c-0.243 0.242-0.394 0.576-0.394 0.946 0 0.366 0.148 0.699 0.387 0.94v0z"
          ></path>
          <path
            d="M7.867 16.943l8 8 1.885-1.885-7.059-7.057 7.056-7.057-1.883-1.885-8 8c-0.241 0.241-0.39 0.575-0.39 0.943s0.149 0.701 0.39 0.943v0z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-double-right">
          <path
            d="M17.36 15.057l-8-8-1.887 1.885 7.060 7.057-7.060 7.057 1.887 1.885 8-8c0.241-0.241 0.39-0.575 0.39-0.943s-0.149-0.701-0.39-0.943v0z"
          ></path>
          <path
            d="M24.533 15.057l-8-8-1.893 1.885 7.057 7.057-7.057 7.057 1.893 1.885 8-8c0.241-0.241 0.39-0.575 0.39-0.943s-0.149-0.701-0.39-0.943v0z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-double-up">
          <path
            d="M15.252 14.836l-8 8 1.887 1.885 7.056-7.057 7.057 7.057 1.887-1.885-8-8c-0.241-0.242-0.575-0.391-0.943-0.391s-0.702 0.149-0.943 0.391v0z"
          ></path>
          <path
            d="M15.252 7.669l-8 8 1.887 1.885 7.056-7.057 7.057 7.057 1.887-1.885-8-8c-0.241-0.242-0.575-0.391-0.943-0.391s-0.702 0.149-0.943 0.391v0z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-easy-to-read">
          <path
            d="M15.749 12c2.761 0 5-2.239 5-5s-2.239-5-5-5c-2.761 0-5 2.239-5 5v0c0 2.761 2.239 5 5 5v0zM15.749 3.5c1.933 0 3.5 1.567 3.5 3.5s-1.567 3.5-3.5 3.5c-1.933 0-3.5-1.567-3.5-3.5v0c0.002-1.932 1.568-3.498 3.5-3.5h0z"
          ></path>
          <path
            d="M26.159 10.688c-0.116-0.076-0.258-0.121-0.41-0.121-0.11 0-0.214 0.023-0.308 0.066l0.005-0.002-9.696 4.287-9.696-4.287c-0.089-0.040-0.193-0.064-0.303-0.064-0.414 0-0.75 0.336-0.751 0.75v0 13.263c0 0.304 0.181 0.566 0.442 0.684l0.005 0.002 10 4.421c0.011 0 0.023 0 0.033 0.007 0.080 0.036 0.173 0.057 0.271 0.057s0.191-0.021 0.275-0.059l-0.004 0.002c0.011 0 0.021 0 0.033-0.007l10-4.421c0.265-0.12 0.445-0.381 0.445-0.685 0 0 0-0 0-0v0-13.263c0 0 0 0 0 0 0-0.262-0.135-0.493-0.339-0.628l-0.003-0.002zM6.5 12.468l8.5 3.757v11.624l-8.5-3.759zM25 24.091l-8.5 3.759v-11.624l8.5-3.757z"
          ></path>
          <path d="M7.749 16.347l6 2.652v-1.635l-6-2.657z"></path>
          <path d="M7.749 19.995l6 2.652v-1.64l-6-2.652z"></path>
          <path d="M7.749 23.348l6 2.652v-1.636l-6-2.656z"></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-eye-off">
          <path
            d="M3.448 5.333l3.945 3.945c-1.783 1.758-3.307 3.772-4.516 5.983l-0.067 0.134c-0.091 0.176-0.145 0.384-0.145 0.604s0.053 0.428 0.148 0.612l-0.004-0.007c0.201 0.4 5.007 9.689 12.588 10.052 0.153 0 0.305 0.011 0.459 0.011 2.496-0.061 4.776-0.936 6.599-2.367l-0.024 0.018 4.235 4.235 1.885-1.885-23.219-23.219zM15.532 23.992c-5.067-0.237-8.884-6.073-10-7.992 1.084-1.84 2.324-3.428 3.744-4.836l0.002-0.002 2.133 2.133c-0.467 0.768-0.744 1.696-0.744 2.689 0 0.005 0 0.011 0 0.016v-0.001c0 2.946 2.388 5.333 5.333 5.333v0c0.001 0 0.003 0 0.004 0 0.996 0 1.927-0.278 2.719-0.761l-0.023 0.013 1.816 1.815c-1.339 0.995-3.024 1.593-4.849 1.593-0.047 0-0.095-0-0.142-0.001h0.007zM16.683 18.568c-0.204 0.060-0.438 0.096-0.681 0.099h-0.002c-1.473 0-2.667-1.194-2.667-2.667v0c0.003-0.244 0.039-0.479 0.103-0.701l-0.004 0.019z"
          ></path>
          <path
            d="M29.125 15.285q-6.5-10.237-13.733-9.941c-1.299 0.076-2.508 0.385-3.61 0.886l0.064-0.026 2.077 2.079c0.471-0.144 1.017-0.242 1.581-0.274l0.019-0.001c3.579-0.199 7.251 2.519 10.88 7.992-0.698 1.043-1.365 1.934-2.074 2.79l0.044-0.055 1.9 1.9c0.965-1.154 1.901-2.433 2.754-3.771l0.089-0.149c0.133-0.204 0.212-0.454 0.212-0.722 0-0.262-0.076-0.507-0.206-0.713l0.003 0.005z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-follow-user">
          <path
            d="M20.885 19.855c-0.485-0.72-1.297-1.188-2.219-1.188h-10.667c-0.922 0-1.734 0.468-2.212 1.179l-0.006 0.010-2.667 4c-0.281 0.416-0.448 0.928-0.448 1.48 0 0.46 0.117 0.893 0.322 1.271l-0.007-0.014c0.459 0.844 1.339 1.408 2.351 1.408 0 0 0.001 0 0.001 0h16c1.012 0 1.892-0.564 2.344-1.394l0.007-0.014c0.198-0.364 0.315-0.797 0.315-1.257 0-0.552-0.167-1.064-0.454-1.489l0.006 0.010zM5.333 25.333l2.667-4h10.667l2.667 4z"
          ></path>
          <path
            d="M13.333 16c3.314 0 6-2.686 6-6v0c0-3.314-2.686-6-6-6v0c-3.314 0-6 2.686-6 6v0c0 3.314 2.686 6 6 6v0zM13.333 6.667c1.841 0 3.333 1.492 3.333 3.333v0c0 1.841-1.492 3.333-3.333 3.333v0c-1.841 0-3.333-1.492-3.333-3.333v0c0-1.841 1.492-3.333 3.333-3.333v0z"
          ></path>
          <path
            d="M29.333 13.333h-2.667v-2.667h-2.667v2.667h-2.667v2.667h2.667v2.667h2.667v-2.667h2.667z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-fsk-0">
          <path
            d="M26.667 2.667h-21.333c-1.473 0-2.667 1.194-2.667 2.667v0 21.333c0 1.473 1.194 2.667 2.667 2.667v0h21.333c1.473 0 2.667-1.194 2.667-2.667v0-21.333c0-1.473-1.194-2.667-2.667-2.667v0zM26.667 26.667h-21.333v-21.333h21.333z"
          ></path>
          <path
            d="M15.992 21.879c0.071 0.005 0.154 0.008 0.237 0.008 1.146 0 2.159-0.567 2.776-1.435l0.007-0.011c0.629-1.114 1-2.445 1-3.864 0-0.199-0.007-0.396-0.022-0.592l0.002 0.026c0.010-0.15 0.016-0.326 0.016-0.502 0-1.43-0.381-2.771-1.048-3.926l0.020 0.038c-0.7-0.917-1.795-1.503-3.026-1.503-1.201 0-2.272 0.558-2.968 1.429l-0.006 0.008c-0.628 1.119-0.997 2.455-0.997 3.878 0 0.204 0.008 0.406 0.022 0.606l-0.002-0.027c-0.011 0.153-0.017 0.332-0.017 0.512 0 1.423 0.379 2.758 1.041 3.908l-0.020-0.038c0.612 0.903 1.634 1.489 2.793 1.489 0.067 0 0.134-0.002 0.201-0.006l-0.009 0zM14.773 12.992c0.156-0.534 0.642-0.918 1.217-0.918s1.060 0.384 1.214 0.909l0.002 0.009c0.248 0.766 0.391 1.648 0.391 2.563 0 0.161-0.004 0.32-0.013 0.479l0.001-0.022c0.007 0.134 0.012 0.29 0.012 0.447 0 0.911-0.141 1.789-0.403 2.614l0.017-0.061c-0.158 0.536-0.645 0.92-1.222 0.92-0.573 0-1.057-0.379-1.217-0.899l-0.002-0.009c-0.237-0.765-0.374-1.644-0.374-2.556 0-0.16 0.004-0.32 0.012-0.478l-0.001 0.022c-0.008-0.137-0.012-0.297-0.012-0.458 0-0.914 0.138-1.796 0.395-2.625l-0.017 0.063z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-fsk-12">
          <path
            d="M26.667 2.667h-21.333c-1.473 0-2.667 1.194-2.667 2.667v0 21.333c0 1.473 1.194 2.667 2.667 2.667v0h21.333c1.473 0 2.667-1.194 2.667-2.667v0-21.333c0-1.473-1.194-2.667-2.667-2.667v0zM26.667 26.667h-21.333v-21.333h21.333z"
          ></path>
          <path
            d="M10.471 13.699c0.156-0.124 0.435-0.388 0.835-0.788l-0.039 1.187-0.023 1.087v6.609h2.413v-11.423h-1.984l-3.68 2.933 1.164 1.452z"
          ></path>
          <path
            d="M18.608 12.533c0.376-0.192 0.819-0.304 1.289-0.305h0c0.022-0.001 0.049-0.002 0.076-0.002 0.357 0 0.684 0.128 0.939 0.34l-0.002-0.002c0.236 0.236 0.383 0.561 0.383 0.921 0 0.036-0.002 0.072-0.004 0.108l0-0.005c0 0.001 0 0.002 0 0.004 0 0.319-0.063 0.624-0.178 0.901l0.006-0.016c-0.155 0.356-0.344 0.664-0.568 0.941l0.006-0.007q-0.391 0.504-1.664 1.808l-2.867 2.899v1.68h7.984v-2.036h-4.977v-0.109l1.467-1.383c0.736-0.649 1.406-1.325 2.028-2.044l0.022-0.027c0.34-0.408 0.628-0.875 0.846-1.38l0.014-0.037c0.176-0.431 0.277-0.932 0.277-1.456 0-0.009 0-0.017 0-0.026v0.001c0-0.015 0-0.032 0-0.050 0-0.579-0.169-1.118-0.461-1.571l0.007 0.012c-0.314-0.475-0.746-0.85-1.255-1.087l-0.019-0.008c-0.53-0.247-1.15-0.392-1.804-0.392-0.030 0-0.060 0-0.091 0.001h0.004c-0.019-0-0.042-0-0.064-0-0.498 0-0.981 0.059-1.445 0.169l0.042-0.009c-0.459 0.112-0.863 0.269-1.238 0.471l0.028-0.013c-0.529 0.311-0.986 0.647-1.407 1.023l0.008-0.007 1.313 1.555c0.38-0.323 0.808-0.609 1.267-0.844l0.037-0.017z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-fsk-16">
          <path
            d="M26.667 2.667h-21.333c-1.473 0-2.667 1.194-2.667 2.667v0 21.333c0 1.473 1.194 2.667 2.667 2.667v0h21.333c1.473 0 2.667-1.194 2.667-2.667v0-21.333c0-1.473-1.194-2.667-2.667-2.667v0zM26.667 26.667h-21.333v-21.333h21.333z"
          ></path>
          <path
            d="M10.471 13.6q0.235-0.188 0.835-0.789l-0.039 1.188-0.023 1.085v6.609h2.413v-11.416h-1.984l-3.68 2.933 1.164 1.453z"
          ></path>
          <path
            d="M17.909 21.269c0.602 0.369 1.331 0.588 2.112 0.588 0.033 0 0.067-0 0.1-0.001h-0.005c0.065 0.004 0.14 0.006 0.216 0.006 1.028 0 1.96-0.409 2.643-1.074l-0.001 0.001c0.644-0.705 1.038-1.648 1.038-2.682 0-0.075-0.002-0.149-0.006-0.223l0 0.010c0.004-0.067 0.007-0.146 0.007-0.225 0-0.927-0.33-1.776-0.88-2.437l0.005 0.006c-0.561-0.595-1.354-0.966-2.234-0.966-0.058 0-0.116 0.002-0.174 0.005l0.008-0c-0.052-0.004-0.112-0.005-0.172-0.005-0.982 0-1.839 0.533-2.298 1.326l-0.007 0.013h-0.101c0.004-0.757 0.174-1.474 0.475-2.116l-0.013 0.031c0.263-0.49 0.668-0.874 1.161-1.103l0.015-0.006c0.546-0.24 1.182-0.379 1.851-0.379 0.062 0 0.124 0.001 0.185 0.004l-0.009-0c0.488 0.001 0.962 0.058 1.417 0.166l-0.042-0.008v-1.931c-0.41-0.076-0.881-0.119-1.362-0.119-0.059 0-0.118 0.001-0.178 0.002h0.009c-0.12-0.010-0.259-0.015-0.4-0.015-1.532 0-2.913 0.641-3.891 1.67l-0.002 0.002c-0.91 1.232-1.456 2.781-1.456 4.458 0 0.207 0.008 0.412 0.025 0.615l-0.002-0.027c-0.002 0.052-0.002 0.113-0.002 0.174 0 0.916 0.189 1.788 0.531 2.579l-0.016-0.042c0.313 0.712 0.814 1.294 1.441 1.696l0.015 0.009zM18.861 16.603c0.319-0.295 0.747-0.476 1.217-0.476 0.013 0 0.026 0 0.039 0h-0.002c0.031-0.003 0.068-0.004 0.105-0.004 0.423 0 0.803 0.187 1.061 0.482l0.001 0.002c0.244 0.332 0.39 0.749 0.39 1.201 0 0.041-0.001 0.081-0.004 0.121l0-0.005c0.003 0.044 0.005 0.096 0.005 0.149 0 0.499-0.157 0.961-0.424 1.34l0.005-0.007c-0.265 0.319-0.661 0.521-1.105 0.521-0.029 0-0.057-0.001-0.086-0.003l0.004 0c-0.004 0-0.008 0-0.012 0-0.506 0-0.953-0.251-1.224-0.635l-0.003-0.005c-0.306-0.412-0.489-0.93-0.489-1.491 0-0.028 0-0.056 0.001-0.084v0.004c0.004-0.443 0.204-0.839 0.516-1.105l0.002-0.002z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-fsk-18">
          <path
            d="M26.667 2.667h-21.333c-1.473 0-2.667 1.194-2.667 2.667v0 21.333c0 1.473 1.194 2.667 2.667 2.667v0h21.333c1.473 0 2.667-1.194 2.667-2.667v0-21.333c0-1.473-1.194-2.667-2.667-2.667v0zM26.667 26.667h-21.333v-21.333h21.333z"
          ></path>
          <path
            d="M10.493 13.613q0.235-0.188 0.836-0.789l-0.039 1.188-0.024 1.085v6.609h2.415v-11.421h-1.985l-3.679 2.933 1.164 1.453z"
          ></path>
          <path
            d="M16.487 17.075c-0.325 0.471-0.519 1.055-0.519 1.684 0 0.018 0 0.035 0 0.052v-0.003c-0.002 0.039-0.003 0.085-0.003 0.131 0 0.868 0.418 1.639 1.063 2.123l0.007 0.005c0.753 0.506 1.68 0.807 2.677 0.807 0.094 0 0.188-0.003 0.281-0.008l-0.013 0.001c0.067 0.004 0.145 0.005 0.223 0.005 1.004 0 1.933-0.322 2.689-0.869l-0.013 0.009c0.672-0.501 1.102-1.295 1.102-2.188 0-0.028-0-0.055-0.001-0.083v0.004c0-0.001 0-0.003 0-0.004 0-0.618-0.21-1.187-0.563-1.639l0.004 0.006c-0.5-0.595-1.113-1.078-1.807-1.419l-0.033-0.014c0.628-0.285 1.152-0.7 1.556-1.214l0.007-0.009c0.304-0.423 0.485-0.95 0.485-1.521 0-0.013 0-0.027-0-0.040v0.002c0.001-0.021 0.001-0.046 0.001-0.071 0-0.799-0.394-1.505-0.998-1.936l-0.007-0.005c-0.75-0.468-1.661-0.745-2.637-0.745-0.984 0-1.902 0.282-2.677 0.77l0.021-0.012c-0.618 0.432-1.016 1.14-1.016 1.941 0 0.015 0 0.030 0 0.045v-0.002c0 0.006 0 0.013 0 0.020 0 0.562 0.16 1.086 0.438 1.53l-0.007-0.012c0.365 0.543 0.836 0.987 1.385 1.31l0.020 0.011c-0.673 0.31-1.232 0.765-1.658 1.326l-0.008 0.011zM18.972 12.219c0.252-0.198 0.574-0.318 0.924-0.318 0.025 0 0.051 0.001 0.076 0.002h-0.004c0.019-0.001 0.041-0.001 0.064-0.001 0.353 0 0.679 0.118 0.94 0.316l-0.004-0.003c0.239 0.191 0.392 0.483 0.392 0.81 0 0.015-0 0.029-0.001 0.044v-0.002c0 0.005 0 0.011 0 0.017 0 0.324-0.116 0.62-0.307 0.852l0.002-0.002c-0.293 0.312-0.648 0.561-1.047 0.727l-0.020 0.007c-0.426-0.187-0.788-0.444-1.088-0.76l-0.001-0.001c-0.188-0.224-0.301-0.516-0.301-0.834 0-0.002 0-0.004 0-0.007v0c-0.001-0.013-0.001-0.028-0.001-0.044 0-0.323 0.146-0.611 0.375-0.803l0.002-0.001zM19.865 16.731c0.55 0.264 1.023 0.589 1.438 0.975l-0.004-0.003c0.263 0.247 0.43 0.594 0.441 0.979v0.002c0.001 0.013 0.001 0.029 0.001 0.045 0 0.388-0.172 0.737-0.444 0.972l-0.002 0.001c-0.374 0.243-0.831 0.387-1.322 0.387-0.483 0-0.933-0.14-1.313-0.381l0.010 0.006c-0.288-0.234-0.471-0.588-0.471-0.985 0-0.022 0.001-0.044 0.002-0.065v0.003q0-1.172 1.665-1.937z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-fsk-6">
          <path
            d="M26.667 2.667h-21.333c-1.473 0-2.667 1.194-2.667 2.667v0 21.333c0 1.473 1.194 2.667 2.667 2.667v0h21.333c1.473 0 2.667-1.194 2.667-2.667v0-21.333c0-1.473-1.194-2.667-2.667-2.667v0zM26.667 26.667h-21.333v-21.333h21.333z"
          ></path>
          <path
            d="M13.933 21.269c0.603 0.369 1.332 0.588 2.113 0.588 0.033 0 0.066-0 0.099-0.001h-0.005c0.065 0.004 0.14 0.006 0.216 0.006 1.028 0 1.961-0.409 2.644-1.074l-0.001 0.001c0.643-0.705 1.036-1.647 1.036-2.681 0-0.075-0.002-0.15-0.006-0.224l0 0.010c0.004-0.068 0.007-0.147 0.007-0.227 0-0.926-0.33-1.775-0.878-2.436l0.005 0.006c-0.561-0.595-1.354-0.966-2.234-0.966-0.058 0-0.116 0.002-0.174 0.005l0.008-0c-0.052-0.004-0.111-0.005-0.172-0.005-0.982 0-1.839 0.533-2.297 1.326l-0.007 0.013h-0.101c0.005-0.757 0.174-1.473 0.473-2.117l-0.013 0.031c0.263-0.489 0.669-0.873 1.161-1.103l0.015-0.006c0.546-0.24 1.183-0.379 1.853-0.379 0.062 0 0.123 0.001 0.184 0.004l-0.009-0c0.488 0.001 0.962 0.058 1.417 0.166l-0.042-0.008v-1.931c-0.41-0.076-0.881-0.119-1.363-0.119-0.060 0-0.119 0.001-0.179 0.002h0.009c-0.12-0.010-0.259-0.015-0.399-0.015-1.531 0-2.913 0.641-3.891 1.67l-0.002 0.002c-0.91 1.232-1.456 2.781-1.456 4.458 0 0.207 0.008 0.412 0.025 0.615l-0.002-0.027c-0.002 0.053-0.003 0.115-0.003 0.177 0 0.915 0.189 1.786 0.529 2.576l-0.016-0.042c0.313 0.712 0.813 1.293 1.44 1.696l0.015 0.009zM14.887 16.603c0.318-0.295 0.746-0.476 1.216-0.476 0.013 0 0.027 0 0.040 0h-0.002c0.031-0.003 0.068-0.004 0.105-0.004 0.423 0 0.803 0.187 1.061 0.482l0.001 0.002c0.244 0.332 0.39 0.748 0.39 1.198 0 0.041-0.001 0.083-0.004 0.123l0-0.006c0.003 0.045 0.005 0.098 0.005 0.15 0 0.498-0.156 0.96-0.423 1.338l0.005-0.007c-0.265 0.319-0.661 0.521-1.105 0.521-0.029 0-0.058-0.001-0.087-0.003l0.004 0c-0.003 0-0.007 0-0.011 0-0.506 0-0.953-0.251-1.223-0.635l-0.003-0.005c-0.307-0.411-0.491-0.929-0.491-1.49 0-0.028 0-0.057 0.001-0.085v0.004c0.004-0.443 0.204-0.839 0.518-1.105l0.002-0.002z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-half-star">
          <path
            d="M27.935 13.215c-0.159-0.478-0.568-0.831-1.069-0.906l-0.007-0.001-6.677-0.969-2.987-6.051c-0.245-0.412-0.688-0.684-1.195-0.684s-0.949 0.272-1.191 0.677l-0.004 0.006-2.987 6.051-6.677 0.969c-0.508 0.076-0.917 0.429-1.073 0.897l-0.003 0.009c-0.041 0.123-0.065 0.265-0.065 0.412 0 0.374 0.154 0.712 0.402 0.955l0 0 4.832 4.711-1.141 6.651c-0.012 0.067-0.019 0.145-0.019 0.223 0 0.443 0.216 0.836 0.548 1.078l0.004 0.003c0.217 0.159 0.489 0.254 0.784 0.254 0.227 0 0.441-0.057 0.628-0.157l-0.007 0.004 5.969-3.14 5.972 3.14c0.18 0.096 0.393 0.153 0.62 0.153v0c0.295-0.001 0.567-0.096 0.788-0.257l-0.004 0.003c0.336-0.245 0.552-0.637 0.552-1.081 0-0.079-0.007-0.156-0.020-0.231l0.001 0.008-1.141-6.651 4.832-4.711c0.247-0.242 0.401-0.58 0.401-0.953 0-0.148-0.024-0.29-0.068-0.423l0.003 0.009zM20.403 17.872c-0.248 0.242-0.402 0.58-0.402 0.954 0 0.080 0.007 0.158 0.020 0.234l-0.001-0.008 0.8 4.679-4.2-2.208c-0.18-0.097-0.393-0.155-0.62-0.155-0 0-0.001 0-0.001 0v0-12.477l2.1 4.256c0.194 0.387 0.561 0.663 0.997 0.728l0.007 0.001 4.697 0.683z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-hourglass">
          <path
            d="M24 5.333v4.229l-6.437 6.437 6.437 6.437v4.229h-16v-4.229l6.437-6.437-6.437-6.437v-4.229h16zM24 2.667h-16c-1.473 0-2.667 1.194-2.667 2.667v0 4.229c0 0.736 0.299 1.403 0.781 1.885l4.552 4.552-4.552 4.552c-0.483 0.483-0.781 1.149-0.781 1.885v4.229c0 1.473 1.194 2.667 2.667 2.667v0h16c1.473 0 2.667-1.194 2.667-2.667v0-4.229c0-0.736-0.299-1.403-0.781-1.885l-4.552-4.552 4.552-4.552c0.483-0.483 0.781-1.149 0.781-1.885v-4.229c0-1.473-1.194-2.667-2.667-2.667v0z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-hourglass-end">
          <path
            d="M26.667 9.563v-4.229c0-1.473-1.194-2.667-2.667-2.667v0h-16c-1.473 0-2.667 1.194-2.667 2.667v0 4.229c0 0.736 0.299 1.403 0.781 1.885l4.552 4.552-4.552 4.552c-0.122 0.121-0.231 0.254-0.327 0.397l-0.006 0.009c-0.281 0.415-0.448 0.928-0.448 1.479v4.229c0 1.284 0.908 2.355 2.116 2.609l0.017 0.003c0.16 0.035 0.344 0.055 0.533 0.055h16c0.189 0 0.373-0.020 0.551-0.058l-0.017 0.003c1.226-0.257 2.133-1.328 2.133-2.612v-4.229c0-0.551-0.167-1.063-0.454-1.488l0.006 0.010c-0.102-0.152-0.212-0.285-0.333-0.407l-4.552-4.552 4.552-4.552c0.483-0.483 0.781-1.149 0.781-1.885v0zM24 9.563l-6.437 6.437h-3.125l-6.437-6.437v-4.229h16z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-hourglass-solid">
          <path
            d="M24 29.333h-16c-1.473 0-2.667-1.194-2.667-2.667v0-4.229c0-0.736 0.299-1.403 0.781-1.885l4.552-4.552-4.552-4.552c-0.483-0.483-0.781-1.149-0.781-1.885v-4.229c0-1.473 1.194-2.667 2.667-2.667v0h16c1.473 0 2.667 1.194 2.667 2.667v0 4.229c0 0.736-0.299 1.403-0.781 1.885l-4.552 4.552 4.552 4.552c0.483 0.483 0.781 1.149 0.781 1.885v4.229c0 1.473-1.194 2.667-2.667 2.667v0z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-hourglass-start">
          <path
            d="M26.667 9.563v-4.229c-0-1.284-0.908-2.355-2.116-2.609l-0.017-0.003c-0.16-0.035-0.344-0.055-0.533-0.055h-16c-0.189 0-0.373 0.020-0.551 0.058l0.017-0.003c-1.226 0.257-2.133 1.328-2.133 2.612v4.229c0 0.736 0.299 1.403 0.781 1.885l4.552 4.552-4.552 4.552c-0.483 0.483-0.781 1.149-0.781 1.885v4.229c0 1.473 1.194 2.667 2.667 2.667v0h16c1.473 0 2.667-1.194 2.667-2.667v0-4.229c0-0.736-0.299-1.403-0.781-1.885l-4.552-4.552 4.552-4.552c0.483-0.483 0.781-1.149 0.781-1.885v0zM19.448 17.885l4.552 4.552v4.229h-16v-4.229l6.437-6.437h3.125z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-image-gallery">
          <path
            d="M26.667 2.667h-16c-1.473 0-2.667 1.194-2.667 2.667v0 2.667h-2.667c-1.473 0-2.667 1.194-2.667 2.667v0 16c0 1.473 1.194 2.667 2.667 2.667v0h16c1.473 0 2.667-1.194 2.667-2.667v0-2.667h2.667c1.473 0 2.667-1.194 2.667-2.667v0-16c0-1.473-1.194-2.667-2.667-2.667v0zM21.333 26.667h-16v-16h2.667v10.667c0 1.473 1.194 2.667 2.667 2.667v0h10.667zM26.667 18.667l-3.067-3.067c-0.579-0.579-1.379-0.937-2.263-0.937s-1.684 0.358-2.263 0.937v0l-5.741 5.733h-2.667v-16h16z"
          ></path>
          <path
            d="M18.667 10.667c0 1.473-1.194 2.667-2.667 2.667s-2.667-1.194-2.667-2.667c0-1.473 1.194-2.667 2.667-2.667s2.667 1.194 2.667 2.667z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-movie">
          <path
            d="M25.333 4h-18.667c-1.473 0-2.667 1.194-2.667 2.667v0 18.667c0 1.473 1.194 2.667 2.667 2.667v0h18.667c1.473 0 2.667-1.194 2.667-2.667v0-18.667c0-1.473-1.194-2.667-2.667-2.667v0zM25.333 9.333h-2.667v-2.667h2.667zM9.333 14.667h-2.667v-2.667h2.667zM9.333 17.333v2.667h-2.667v-2.667zM12 6.667h8v18.667h-8zM22.667 17.333h2.667v2.667h-2.667zM22.667 14.667v-2.667h2.667v2.667zM9.333 6.667v2.667h-2.667v-2.667zM6.667 22.667h2.667v2.667h-2.667zM22.667 25.333v-2.667h2.667v2.667z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-munich">
          <path
            d="M3.141 0v19.159c0 3.418 1.346 6.662 3.763 9.079s5.661 3.763 9.079 3.763c0 0 0 0 0 0 7.111-0.035 12.876-5.799 12.876-12.876v-19.124h-25.717zM27.478 19.124c0 6.317-5.143 11.46-11.495 11.46 0 0 0 0 0 0-3.072 0-5.937-1.208-8.112-3.348-2.175-2.175-3.383-5.040-3.383-8.112v-17.778h22.99v17.778z"
          ></path>
          <path
            d="M8.112 8.078c0.035 0.035 0.035 0.104 0 0.207-0.311-0.069-0.552-0.276-0.691-0.207-0.035 0.035-0.035 0.035-0.104 0.138 0.242 0 0.621 0.242 0.621 0.242s-0.069 0.138-0.069 0.172c-0.311-0.104-0.345-0.172-0.656-0.242-0.035 0.138-0.069 0.138-0.104 0.242l2.244 1.208c0-0.414 0.035-0.656 0.035-1.001 0.449 0.138 0.311 0.621 0.311 1.105 0 1.001-0.035 8.388-0.035 13.394 0.863 0 1.484-0.276 1.968-1.070 0.138-0.138 0.276-0.449 0.276-1.761 0-1.381 0-6.662 0-6.662 0-0.311 0.207-0.759 0.587-1.070 0.276-0.242 0.483-0.38 0.828-0.449v10.943h5.592v-11.15c0.276 0.104 0.691 0.38 0.863 0.621s0.38 0.518 0.552 1.036c0.138 0.691 0.069 6.559 0.069 6.662 0 0.932-0.069 1.657 0.311 2.244 0.345 0.483 1.277 0.725 1.829 0.759v-14.429c0.104-0.035 0.207-0.069 0.311-0.069v1.174l1.312-0.794c-0.138-0.138-0.138-0.311-0.311-0.518-0.138 0.414-0.38 0.518-0.759 0.587-0.035-0.069-0.035-0.138-0.035-0.242 0.311-0.104 0.552-0.172 0.621-0.483 0.069-0.242-0.104-0.38 0-0.587 0.035-0.069 0.725-0.552 0.932-0.587 0.035 0.104 0.069 0.138 0.104 0.207-0.518 0.345-0.518 0.345-0.828 0.587l0.518 1.070 1.519-0.828-1.105-2.209-1.484 0.759c0.069 0.207 0.138 0.311 0.242 0.518-0.311 0.172-0.483 0.276-0.483 0.276-0.276 0.172 0.242 0.656-0.172 0.794-1.139 0.449-2.589 0.656-3.694 0.414-0.863-0.138-1.45-0.794-1.484-0.932-0.449-0.898 1.622-0.656 2.485-0.656-0.138-0.656-0.242-0.828-0.483-1.346-0.483-0.828-0.898-1.208-1.692-1.553-0.691-0.276-2.209-0.345-2.9-0.345 0.656 0.311 0.725 0.449 0.967 0.794 0.138 0.311 0.138 0.587 0.138 1.795 0 0.759-0.069 1.519-0.069 1.553l-0.345 0.104c0 0 0.104-2.831 0.035-3.038-0.069-0.38-0.483-0.691-0.794-0.898-0.242-0.104-0.518-0.207-0.725-0.138-0.863 0.38-0.414 1.208-0.691 2.485 0.138 0.035 0.138 0.035 0.276 0.069 0 0.276 0.035 0.449 0.069 0.794 0.759 0.069 1.208-0.035 1.277 0 0.172 0.172 0.449 0.691 0.483 0.794l-0.242 0.069c-0.069-0.035-0.311-0.38-0.414-0.518-0.691 0.794-1.761 1.001-2.692 1.070-0.587 0.069-2.416-0.311-2.865-0.587-0.414-0.276-0.207-0.691-0.242-0.828 0-0.035-0.932-0.794-1.277-1.036-0.311 0.414 0.311 0.691 0.449 0.898 0.207 0.311-0.035 0.794 0.518 1.105v0.242c-0.794-0.242-0.518-0.967-0.656-1.243-0.138-0.172-0.276-0.242-0.483-0.38-0.38-0.276-0.621-0.483-1.001-0.725-0.035 0.104-0.069 0.172-0.138 0.242 0.449 0.38 0.759 0.483 0.932 0.656 0.035 0-0.035 0.172-0.035 0.138-0.242-0.138-0.828-0.483-0.967-0.587-0.035 0.069-0.069 0.104-0.104 0.207 0.449 0.311 0.863 0.552 0.967 0.621 0.242 0.035 0.483 0.069 0.587 0.172zM14.844 4.833c0.276 0.207 0.242 0.276 0.242 0.518-0.242-0.207-0.276-0.207-0.242-0.518zM14.533 4.798c0.311 0.207 0.276 0.276 0.242 0.552-0.242-0.207-0.311-0.242-0.242-0.552zM14.222 4.798c0.276 0.242 0.276 0.207 0.207 0.552-0.207-0.207-0.276-0.242-0.207-0.552zM14.36 6.006v0c0.035-0.172 0.069-0.276 0.207-0.311 0.242-0.069 0.449 0.069 0.552 0.311-0.345 0.207-0.345 0.207-0.759 0zM15.43 5.351c-0.242-0.172-0.276-0.207-0.242-0.552 0.207 0.242 0.242 0.276 0.242 0.552zM13.532 9.217c0.104 0.38 0.345 0.552 0.656 0.759 0.449 0.311 1.139 0.552 1.933 0.552 0.932 0 1.761-0.345 2.14-0.759 0.207-0.172 0.345-0.311 0.414-0.587l0.38 0.104c-0.069 0.311-0.276 0.552-0.518 0.794-0.414 0.449-1.208 0.794-2.209 0.828v4.177h2.382v0.414h-2.382v7.422h-0.449v-7.353h-2.278v-0.414h2.278v-4.212c-0.828-0.035-1.484-0.311-1.933-0.621-0.38-0.242-0.656-0.552-0.794-0.967l0.38-0.138z"
          ></path>
          <path
            d="M13.773 24.302c-0.207 0.276-1.243 0.898-1.243 2.14 0.656-0.414 0.967-0.621 1.657-1.139 0.552-0.414 0.828-0.449 0.863-0.518 0 0 0-0.311 0-1.174-0.656 0-0.898 0-0.898 0-0.138 0.035-0.207 0.483-0.38 0.691z"
          ></path>
          <path
            d="M18.606 24.613c-0.276-0.242-0.656-1.001-0.656-1.001s-0.242 0-0.932 0c0 0.759 0 1.070 0.069 1.174 0.138 0.172 0.414 0.207 1.001 0.656 0.552 0.449 0.863 0.691 1.415 1.001-0.069-1.381-0.38-1.277-0.898-1.829z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-paperclip">
          <path
            d="M23.796 4.852c1.016 1.016 1.569 2.254 1.643 3.67l0.008 0.285v0.002c0 1.528-0.558 2.864-1.651 3.958l-8.474 8.474c-0.7 0.7-1.537 1.049-2.513 1.060-0.982 0.012-1.823-0.339-2.522-1.039-0.703-0.703-1.041-1.557-1.018-2.547 0.023-0.987 0.394-1.842 1.101-2.549l7.796-7.796c0.506-0.506 1.327-0.506 1.833 0s0.506 1.327 0 1.833l-7.826 7.826c-0.206 0.206-0.31 0.451-0.318 0.716-0.008 0.269 0.073 0.49 0.266 0.683 0.197 0.197 0.4 0.274 0.663 0.266 0.271-0.008 0.503-0.106 0.694-0.297l8.485-8.485c0.596-0.596 0.885-1.289 0.876-2.108v-0.004c0-0.811-0.3-1.516-0.887-2.103s-1.292-0.886-2.102-0.886c-0.816 0-1.52 0.282-2.113 0.875l-9.386 9.386c-0.976 0.977-1.445 2.135-1.426 3.507 0.019 1.374 0.518 2.547 1.509 3.538s2.175 1.499 3.548 1.519c1.37 0.019 2.541-0.459 3.529-1.447l8.737-8.737c0.506-0.506 1.327-0.506 1.833 0s0.506 1.327 0 1.833l-8.717 8.717c-1.487 1.487-3.28 2.232-5.369 2.21-2.085-0.022-3.887-0.775-5.374-2.262l-0.010-0.010c-1.486-1.485-2.25-3.276-2.272-5.362v-0.002c-0.011-2.076 0.722-3.88 2.2-5.359l9.365-9.365c1.084-1.084 2.41-1.629 3.945-1.64h0.004c1.534 0.011 2.86 0.556 3.945 1.64z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-place">
          <path d="M9.333 16h2.667v2.667h-2.667v-2.667z"></path>
          <path d="M9.333 10.667h2.667v2.667h-2.667v-2.667z"></path>
          <path
            d="M25.333 10.667h-8v-5.333c0-1.473-1.194-2.667-2.667-2.667v0c-0 0-0.001 0-0.001 0-0.275 0-0.54 0.042-0.788 0.12l0.019-0.005-8 2.4c-1.106 0.341-1.896 1.355-1.896 2.552v18.933c0 1.473 1.194 2.667 2.667 2.667v0h18.667c1.473 0 2.667-1.194 2.667-2.667v0-13.333c0-1.473-1.194-2.667-2.667-2.667v0zM20 13.333v2.667h-2.667v-2.667zM17.333 18.667h2.667v2.667h-2.667zM12 26.667v-5.333h-2.667v5.333h-2.667v-18.933l8-2.4v21.333zM17.333 26.667v-2.667h2.667v2.667zM25.333 26.667h-2.667v-2.667h2.667zM25.333 21.333h-2.667v-2.667h2.667zM25.333 16h-2.667v-2.667h2.667z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-play-circle">
          <path
            d="M13.733 22.667c-0.736 0-1.333-0.597-1.333-1.333v0-10.667c0 0 0-0 0-0.001 0-0.736 0.597-1.333 1.333-1.333 0.316 0 0.607 0.11 0.836 0.294l-0.003-0.002 6.667 5.333c0.306 0.246 0.5 0.621 0.5 1.041s-0.194 0.795-0.498 1.039l-0.003 0.002-6.667 5.333c-0.226 0.182-0.516 0.292-0.833 0.292-0 0-0 0-0.001 0v0z"
          ></path>
          <path
            d="M16 5.333c5.891 0 10.667 4.776 10.667 10.667s-4.776 10.667-10.667 10.667c-5.891 0-10.667-4.776-10.667-10.667v0c0.007-5.888 4.778-10.66 10.666-10.667h0.001zM16 2.667c-7.364 0-13.333 5.97-13.333 13.333s5.97 13.333 13.333 13.333c7.364 0 13.333-5.97 13.333-13.333v0c0-7.364-5.97-13.333-13.333-13.333v0z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-play-square">
          <path
            d="M26.667 2.667h-21.333c-1.473 0-2.667 1.194-2.667 2.667v0 21.333c0 1.473 1.194 2.667 2.667 2.667v0h21.333c1.473 0 2.667-1.194 2.667-2.667v0-21.333c0-1.473-1.194-2.667-2.667-2.667v0zM26.667 26.667h-21.333v-21.333h21.333z"
          ></path>
          <path
            d="M11.389 23.853c0.177 0.092 0.387 0.147 0.61 0.147 0 0 0.001 0 0.001 0v0c0.001 0 0.002 0 0.004 0 0.29 0 0.557-0.093 0.775-0.252l-0.004 0.003 9.333-6.667c0.339-0.245 0.557-0.639 0.557-1.084s-0.218-0.839-0.553-1.081l-0.004-0.003-9.333-6.667c-0.215-0.155-0.484-0.248-0.775-0.248-0.736 0-1.333 0.596-1.333 1.332v13.333c0 0 0 0.001 0 0.001 0 0.513 0.29 0.959 0.715 1.182l0.007 0.004z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-privacy">
          <path
            d="M25.885 8.552l-5.104-5.104c-0.483-0.483-1.149-0.781-1.885-0.781h-10.896c-1.473 0-2.667 1.194-2.667 2.667v0 21.333c0 1.473 1.194 2.667 2.667 2.667v0h16c1.473 0 2.667-1.194 2.667-2.667v0-16.229c0-0.736-0.299-1.403-0.781-1.885v0zM24 26.667h-16v-21.333h10.896l5.104 5.104z"
          ></path>
          <path
            d="M12.832 17.112c0-0.008 0-0.018 0-0.028 0-0.857 0.437-1.612 1.1-2.053l0.009-0.006c-0.668-0.383-1.11-1.092-1.11-1.904 0-0.015 0-0.029 0-0.044v0.002c-0-0.011-0-0.024-0-0.036 0-0.716 0.368-1.347 0.926-1.712l0.008-0.005c0.636-0.417 1.415-0.665 2.252-0.665 0.069 0 0.137 0.002 0.205 0.005l-0.010-0c1.104 0.025 2.143 0.281 3.077 0.721l-0.046-0.020-0.685 1.587q-0.568-0.267-1.156-0.48c-0.363-0.135-0.783-0.213-1.221-0.213-0.007 0-0.013 0-0.020 0h0.001c-0.041-0.003-0.088-0.005-0.136-0.005-0.309 0-0.602 0.073-0.861 0.202l0.011-0.005c-0.187 0.116-0.309 0.32-0.309 0.553 0 0.014 0 0.028 0.001 0.042v-0.002c0.012 0.301 0.172 0.563 0.408 0.715l0.004 0.002c0.393 0.266 0.844 0.497 1.322 0.671l0.044 0.014c0.782 0.273 1.45 0.697 2.001 1.239l-0.001-0.001c0.398 0.417 0.643 0.983 0.643 1.606 0 0.004 0 0.007 0 0.011v-0.001c0.004 0.053 0.006 0.114 0.006 0.176 0 0.824-0.412 1.552-1.041 1.989l-0.008 0.005c0.318 0.197 0.58 0.456 0.774 0.762l0.006 0.010c0.168 0.288 0.268 0.634 0.268 1.003 0 0.019-0 0.037-0.001 0.056v-0.003c0.001 0.019 0.001 0.042 0.001 0.064 0 0.786-0.392 1.48-0.991 1.898l-0.008 0.005c-0.689 0.461-1.536 0.737-2.448 0.737-0.080 0-0.16-0.002-0.239-0.006l0.011 0c-0.049 0.001-0.107 0.002-0.165 0.002-1.003 0-1.949-0.245-2.782-0.678l0.033 0.016v-1.733c0.424 0.218 0.923 0.415 1.442 0.565l0.061 0.015c0.419 0.136 0.902 0.223 1.403 0.238l0.008 0q1.62 0 1.62-0.977c0.001-0.012 0.001-0.025 0.001-0.039 0-0.182-0.058-0.351-0.157-0.489l0.002 0.003c-0.148-0.169-0.324-0.307-0.524-0.409l-0.010-0.004c-0.279-0.157-0.621-0.324-0.974-0.472l-0.068-0.025c-0.82-0.282-1.523-0.717-2.106-1.275l0.002 0.002c-0.357-0.414-0.574-0.957-0.574-1.551 0-0.027 0-0.053 0.001-0.079v0.004zM14.699 16.928c0.014 0.412 0.224 0.771 0.539 0.991l0.004 0.003c0.434 0.328 0.934 0.605 1.47 0.805l0.041 0.013c0.395-0.263 0.652-0.707 0.652-1.211 0-0.003 0-0.005 0-0.008v0c0-0.005 0-0.012 0-0.018 0-0.374-0.162-0.71-0.42-0.941l-0.001-0.001c-0.436-0.342-0.945-0.615-1.498-0.791l-0.034-0.009c-0.224 0.091-0.407 0.246-0.531 0.442l-0.003 0.004c-0.14 0.201-0.224 0.45-0.224 0.718 0 0.003 0 0.005 0 0.007v-0z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-route">
          <path
            d="M22.656 9.357v5.333h2.667v-5.333c0-2.946-2.388-5.333-5.333-5.333v0c-2.946 0-5.333 2.388-5.333 5.333v0 13.343c-0.001 1.467-1.189 2.656-2.656 2.657v0c-1.467-0.002-2.655-1.19-2.656-2.657v-5.343h-2.667v5.343c0.005 2.938 2.385 5.318 5.322 5.324h0.001c2.938-0.006 5.317-2.386 5.323-5.324v-13.343c0-1.473 1.194-2.667 2.667-2.667v0c1.473 0 2.667 1.194 2.667 2.667v0z"
          ></path>
          <path
            d="M23.292 18.755c-1.659 0.32-2.939 1.627-3.214 3.275l-0.003 0.023c-0.031 0.186-0.048 0.401-0.048 0.619 0 0.736 0.199 1.425 0.545 2.017l-0.010-0.019c0.759 1.080 1.552 2.028 2.417 2.905l-0.003-0.003c0.26 0.278 0.628 0.451 1.037 0.451s0.777-0.173 1.036-0.45l0.001-0.001c0.854-0.867 1.642-1.804 2.352-2.8l0.048-0.070c0.346-0.576 0.551-1.271 0.551-2.014 0-0.004 0-0.008 0-0.012v0.001c-0.001-2.203-1.787-3.988-3.989-3.988-0.254 0-0.503 0.024-0.743 0.069l0.025-0.004zM24.013 23.955c-0.786 0-1.423-0.637-1.423-1.423v0c0-0.786 0.637-1.423 1.423-1.424v0c0.787 0 1.424 0.637 1.424 1.424v0c0 0 0 0.001 0 0.001 0 0.787-0.637 1.424-1.424 1.424 0 0 0 0 0 0v0z"
          ></path>
          <path
            d="M9.049 12.907c0.854-0.867 1.642-1.804 2.352-2.8l0.048-0.070c0.346-0.576 0.551-1.271 0.551-2.014 0-0.004 0-0.008 0-0.012v0.001c-0.001-2.203-1.787-3.988-3.989-3.988-0.254 0-0.503 0.024-0.743 0.069l0.025-0.004c-1.659 0.32-2.939 1.627-3.214 3.275l-0.003 0.023c-0.031 0.186-0.048 0.401-0.048 0.619 0 0.736 0.199 1.425 0.545 2.017l-0.010-0.019c0.759 1.080 1.552 2.028 2.417 2.905l-0.003-0.003c0.26 0.278 0.628 0.451 1.037 0.451s0.777-0.173 1.036-0.45l0.001-0.001zM8.013 6.444c0.787 0 1.424 0.637 1.424 1.424v0c-0.001 0.786-0.638 1.423-1.424 1.423v0c-0.786 0-1.423-0.637-1.423-1.423v0c0-0.786 0.637-1.423 1.423-1.424v0z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-route-alt">
          <path
            d="M24 2.691h-16c-1.473 0-2.667 1.194-2.667 2.667v0 18.667c0 1.473 1.194 2.667 2.667 2.667v0h2l-2 2.667h3.333l2-2.667h5.333l2 2.667h3.333l-2-2.667h2c1.473 0 2.667-1.194 2.667-2.667v0-18.667c0-1.473-1.194-2.667-2.667-2.667v0zM24 24.024h-16v-8h16zM24 13.357h-16v-8h16z"
          ></path>
          <path d="M10.667 18.691h2.667v2.667h-2.667v-2.667z"></path>
          <path d="M18.667 18.691h2.667v2.667h-2.667v-2.667z"></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-share-android">
          <path
            d="M23.333 11.357c-1.339 0.002-2.545 0.57-3.391 1.477l-0.003 0.003-6.688-3.347c0.048-0.239 0.078-0.515 0.081-0.797v-0.003c0-2.577-2.089-4.667-4.667-4.667v0c-2.577 0-4.667 2.089-4.667 4.667v0c0 2.577 2.089 4.667 4.667 4.667v0c1.339-0.002 2.545-0.57 3.391-1.477l0.003-0.003 6.688 3.345c-0.048 0.239-0.078 0.516-0.081 0.798v0.003c0.004 0.285 0.033 0.561 0.086 0.829l-0.005-0.029-6.688 3.344c-0.849-0.909-2.055-1.476-3.393-1.477v0c-2.577 0-4.667 2.089-4.667 4.667v0c0 2.577 2.089 4.667 4.667 4.667v0c2.577 0 4.667-2.089 4.667-4.667v0c-0.004-0.285-0.033-0.561-0.086-0.829l0.005 0.029 6.689-3.345c0.848 0.91 2.054 1.478 3.392 1.479v0c2.577 0 4.667-2.089 4.667-4.667v0c0-2.577-2.089-4.667-4.667-4.667v0zM8.667 10.691c-1.105 0-2-0.895-2-2v0c0-1.105 0.895-2 2-2v0c1.105 0 2 0.895 2 2v0c0 1.105-0.895 2-2 2v0zM8.667 25.357c-1.105 0-2-0.895-2-2v0c0-1.105 0.895-2 2-2v0c1.105 0 2 0.895 2 2v0c0 1.105-0.895 2-2 2v0zM23.333 18.024c-1.105 0-2-0.895-2-2v0c0-1.105 0.895-2 2-2v0c1.105 0 2 0.895 2 2v0c0 1.105-0.895 2-2 2v0z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-shopping-cart">
          <path
            d="M10.936 29.333c-0.67456 0-1.2506-0.24032-1.7277-0.72064s-0.71584-1.0576-0.71584-1.7325 0.24032-1.2506 0.72064-1.7277c0.48032-0.47744 1.0576-0.71584 1.7325-0.71584s1.2506 0.24032 1.7277 0.72064c0.47744 0.48064 0.71584 1.0576 0.71584 1.7325s-0.24032 1.2506-0.72064 1.7277c-0.48032 0.47744-1.0576 0.71584-1.7325 0.71584zM24.052 29.333c-0.67456 0-1.2506-0.24032-1.7277-0.72064s-0.71584-1.0576-0.71584-1.7325 0.24032-1.2506 0.72064-1.7277c0.48064-0.47744 1.0576-0.71584 1.7325-0.71584s1.2506 0.24032 1.7277 0.72064c0.47744 0.48064 0.71584 1.0576 0.71584 1.7325s-0.24032 1.2506-0.72064 1.7277c-0.48064 0.47744-1.0576 0.71584-1.7325 0.71584zM9.8214 7.9107l3.1018 6.4659h9.2941l3.527-6.4659h-15.923zM8.503 5.2678h18.732c0.68384 0 1.2042 0.304 1.5606 0.91168s0.36416 1.2237 0.02272 1.8474l-4.151 7.5107c-0.26528 0.45152-0.608 0.8144-1.0291 1.089s-0.8896 0.41184-1.4061 0.41184h-9.7366l-1.5629 2.919h15.8v2.6429h-16.011c-1.0512 0-1.816-0.38912-2.2941-1.1674s-0.47584-1.5994 0.00736-2.464l1.9338-3.527-4.7728-10.133h-2.6659v-2.6432h4.3491l1.224 2.6013z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-discount">
          <path
            d="M16 29.333c-0.533 0-1.044-0.1-1.533-0.3s-0.922-0.489-1.3-0.867c-0.644-0.644-1.2-1.067-1.667-1.267s-1.167-0.3-2.1-0.3c-1.111 0-2.056-0.389-2.833-1.167s-1.167-1.722-1.167-2.833c0-0.933-0.1-1.633-0.3-2.1s-0.622-1.022-1.267-1.667c-0.378-0.378-0.667-0.811-0.867-1.3s-0.3-1-0.3-1.533c0-0.533 0.1-1.044 0.3-1.533s0.489-0.922 0.867-1.3c0.644-0.644 1.067-1.2 1.267-1.667s0.3-1.167 0.3-2.1c0-1.111 0.389-2.056 1.167-2.833s1.722-1.167 2.833-1.167c0.933 0 1.633-0.1 2.1-0.3s1.022-0.622 1.667-1.267c0.378-0.378 0.811-0.667 1.3-0.867s1-0.3 1.533-0.3c0.533 0 1.044 0.1 1.533 0.3s0.922 0.489 1.3 0.867c0.644 0.644 1.2 1.067 1.667 1.267s1.167 0.3 2.1 0.3c1.111 0 2.056 0.389 2.833 1.167s1.167 1.722 1.167 2.833c0 0.933 0.1 1.633 0.3 2.1s0.622 1.022 1.267 1.667c0.378 0.378 0.667 0.811 0.867 1.3s0.3 1 0.3 1.533c0 0.533-0.1 1.044-0.3 1.533s-0.489 0.922-0.867 1.3c-0.644 0.644-1.067 1.2-1.267 1.667s-0.3 1.167-0.3 2.1c0 1.111-0.389 2.056-1.167 2.833s-1.722 1.167-2.833 1.167c-0.933 0-1.633 0.1-2.1 0.3s-1.022 0.622-1.667 1.267c-0.378 0.378-0.811 0.667-1.3 0.867s-1 0.3-1.533 0.3zM16 26.667c0.178 0 0.35-0.039 0.517-0.117s0.306-0.172 0.417-0.283c0.911-0.911 1.767-1.528 2.567-1.85s1.833-0.483 3.1-0.483c0.378 0 0.694-0.128 0.95-0.383s0.383-0.572 0.383-0.95c0-1.289 0.161-2.328 0.483-3.117s0.939-1.639 1.85-2.55c0.267-0.267 0.4-0.578 0.4-0.933s-0.133-0.667-0.4-0.933c-0.911-0.911-1.528-1.767-1.85-2.567s-0.483-1.833-0.483-3.1c0-0.378-0.128-0.694-0.383-0.95s-0.572-0.383-0.95-0.383c-1.289 0-2.328-0.161-3.117-0.483s-1.639-0.939-2.55-1.85c-0.111-0.111-0.25-0.206-0.417-0.283s-0.339-0.117-0.517-0.117c-0.178 0-0.35 0.039-0.517 0.117s-0.306 0.172-0.417 0.283c-0.911 0.911-1.767 1.528-2.567 1.85s-1.833 0.483-3.1 0.483c-0.378 0-0.694 0.128-0.95 0.383s-0.383 0.572-0.383 0.95c0 1.289-0.161 2.328-0.483 3.117s-0.939 1.639-1.85 2.55c-0.267 0.267-0.4 0.578-0.4 0.933s0.133 0.667 0.4 0.933c0.911 0.911 1.528 1.767 1.85 2.567s0.483 1.833 0.483 3.1c0 0.378 0.128 0.694 0.383 0.95s0.572 0.383 0.95 0.383c1.289 0 2.328 0.161 3.117 0.483s1.639 0.939 2.55 1.85c0.111 0.111 0.25 0.206 0.417 0.283s0.339 0.117 0.517 0.117zM19.333 21.333c0.556 0 1.028-0.194 1.417-0.583s0.583-0.861 0.583-1.417c0-0.556-0.194-1.028-0.583-1.417s-0.861-0.583-1.417-0.583c-0.556 0-1.028 0.194-1.417 0.583s-0.583 0.861-0.583 1.417c0 0.556 0.194 1.028 0.583 1.417s0.861 0.583 1.417 0.583zM12.6 21.267l8.667-8.667-1.867-1.867-8.667 8.667 1.867 1.867zM14.083 14.083c0.389-0.389 0.583-0.861 0.583-1.417s-0.194-1.028-0.583-1.417c-0.389-0.389-0.861-0.583-1.417-0.583s-1.028 0.194-1.417 0.583c-0.389 0.389-0.583 0.861-0.583 1.417s0.194 1.028 0.583 1.417c0.389 0.389 0.861 0.583 1.417 0.583s1.028-0.194 1.417-0.583z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-sign-in">
          <path
            d="M17.333 8h8v16h-8v2.667h8c1.473 0 2.667-1.194 2.667-2.667v0-16c0-1.473-1.194-2.667-2.667-2.667v0h-8z"
          ></path>
          <path
            d="M15.448 17.333h-11.448v-2.667h11.448l-3.057-3.057 1.885-1.885 5.333 5.333c0.241 0.241 0.39 0.575 0.39 0.943s-0.149 0.701-0.39 0.943l-5.333 5.333-1.885-1.885z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-sign-language">
          <path
            d="M27.941 13.916l-0.267-4.517c0.001-0.026 0.002-0.056 0.002-0.086 0-1.204-0.976-2.18-2.18-2.18-1.152 0-2.096 0.894-2.175 2.026l-0 0.007s-0.035 0.516-0.047 0.823l-6.505-7.2c-0.415-0.479-1.024-0.781-1.704-0.781-0.444 0-0.859 0.129-1.208 0.352l0.009-0.005c-0.309 0.211-0.557 0.489-0.725 0.816l-0.006 0.012c-0.341-0.21-0.754-0.335-1.196-0.335-0.001 0-0.003 0-0.004 0h0c-1.182 0.013-2.135 0.974-2.135 2.157 0 0.358 0.087 0.696 0.242 0.993l-0.006-0.012c-0.83 0.311-1.41 1.098-1.41 2.020 0 0.358 0.088 0.696 0.243 0.993l-0.006-0.012c-0.368 0.14-0.677 0.363-0.916 0.647l-0.003 0.004c-0.299 0.379-0.479 0.863-0.479 1.389 0 0.598 0.233 1.142 0.613 1.545l-0.001-0.001 3.992 4.421h-4.337c-0.014-0-0.031-0.001-0.048-0.001-1.108 0-2.029 0.8-2.217 1.853l-0.002 0.014c-0.014 0.092-0.021 0.198-0.021 0.307 0 0.284 0.054 0.556 0.152 0.805l-0.005-0.015c-0.924 0.268-1.588 1.107-1.588 2.1 0 0.11 0.008 0.217 0.024 0.323l-0.001-0.012c0.141 0.852 0.755 1.53 1.558 1.763l0.016 0.004c-0.098 0.237-0.154 0.512-0.154 0.801 0 0.991 0.669 1.827 1.58 2.078l0.015 0.004c-0.098 0.24-0.155 0.519-0.155 0.811 0 0.108 0.008 0.214 0.023 0.318l-0.001-0.012c0.189 1.067 1.11 1.867 2.218 1.867 0.017 0 0.034-0 0.052-0.001h11.49c2.92-0.003 5.286-2.369 5.289-5.289v-4.115c-0.016-0.487-0.097-0.95-0.233-1.388l0.010 0.037 0.579-0.523c1.042-1.012 1.689-2.427 1.689-3.993 0-0.287-0.022-0.569-0.064-0.844l0.004 0.031zM24.475 24.716c-0.006 2.1-1.708 3.801-3.807 3.807h-11.495c-0.011 0.001-0.023 0.001-0.036 0.001-0.367 0-0.676-0.251-0.763-0.591l-0.001-0.005c-0.005-0.032-0.008-0.069-0.008-0.107 0-0.388 0.315-0.703 0.703-0.704h4.777v-1.476h-6.111c-0.011 0.001-0.023 0.001-0.036 0.001-0.368 0-0.676-0.252-0.763-0.593l-0.001-0.005c-0.005-0.032-0.008-0.069-0.008-0.107 0-0.387 0.314-0.701 0.701-0.701 0.001 0 0.001 0 0.002 0h6.219v-1.477h-7.557c-0.011 0.001-0.024 0.001-0.037 0.001-0.367 0-0.675-0.251-0.762-0.59l-0.001-0.005c-0.005-0.032-0.008-0.070-0.008-0.108 0-0.387 0.314-0.701 0.701-0.701 0 0 0 0 0 0h7.66v-1.477h-6.223c-0.388-0.001-0.703-0.316-0.703-0.704 0-0.038 0.003-0.075 0.009-0.111l-0.001 0.004c0.089-0.345 0.397-0.596 0.764-0.596 0.012 0 0.025 0 0.038 0.001h11.776c0.408-0.001 0.739-0.332 0.739-0.74 0-0.236-0.11-0.446-0.282-0.581l-0.002-0.001c-0.307-0.239-1.16-0.909-1.433-1.156-0.161-0.144-0.608-0.609-0.62-0.621-0.127-0.127-0.205-0.303-0.205-0.497s0.078-0.369 0.205-0.497v0c0.131-0.123 0.308-0.199 0.502-0.199 0.22 0 0.418 0.097 0.552 0.252l0.001 0.001 3.628 2.908c0.024 0.020 0.051 0.039 0.080 0.056l0.003 0.002c1.051 0.71 1.746 1.876 1.801 3.208l0 0.008zM23.48 16.167l-3.532-2.833c-0.4-0.382-0.943-0.618-1.541-0.618s-1.141 0.235-1.542 0.619l0.001-0.001c-0.393 0.394-0.636 0.938-0.636 1.539 0 0.595 0.239 1.135 0.625 1.528l-0-0s0.361 0.375 0.581 0.587h-3.371l-1.38-1.52-3.508-3.885c-0.143-0.143-0.231-0.34-0.231-0.558 0-0.164 0.050-0.316 0.135-0.442l-0.002 0.003c0.128-0.155 0.318-0.255 0.533-0.259h0.014c0 0 0.001 0 0.001 0 0.207 0 0.393 0.089 0.521 0.231l0.001 0.001 3.2 3.545 1.096-0.989-4.091-4.533c-0.143-0.142-0.232-0.34-0.232-0.557 0-0.164 0.050-0.316 0.136-0.443l-0.002 0.003c0.127-0.156 0.318-0.255 0.533-0.257h0c0.21 0.003 0.398 0.091 0.533 0.23l0 0 4.169 4.615 1.097-0.989-5.068-5.603c-0.142-0.143-0.23-0.34-0.23-0.557 0-0.164 0.050-0.316 0.135-0.441l-0.002 0.003c0.127-0.156 0.318-0.256 0.533-0.259h0.001c0.015-0.001 0.033-0.002 0.052-0.002 0.195 0 0.368 0.090 0.481 0.23l0.001 0.001 5.133 5.687 1.097-0.989-4.169-4.616c-0.112-0.124-0.18-0.289-0.18-0.47 0-0.243 0.124-0.458 0.311-0.584l0.003-0.002c0.107-0.058 0.235-0.092 0.37-0.092 0.246 0 0.466 0.113 0.61 0.29l0.001 0.001 7.893 8.739c0.136 0.15 0.331 0.244 0.549 0.244 0.408 0 0.739-0.331 0.739-0.739 0-0.019-0.001-0.038-0.002-0.057v0.003c-0.029-0.387-0.103-1.467-0.103-1.836 0-0.217 0.044-0.864 0.045-0.877 0.019-0.372 0.325-0.668 0.7-0.668 0.013 0 0.027 0 0.040 0.001h-0.002c0.378 0.032 0.672 0.346 0.672 0.73 0 0.031-0.002 0.062-0.006 0.093l0-0.004 0.267 4.641c0.002 0.037 0.007 0.072 0.014 0.106l-0.001-0.004c0.027 0.176 0.043 0.38 0.043 0.587 0 1.138-0.467 2.167-1.22 2.905l-0.001 0.001-0.209 0.189c-0.427-0.664-0.962-1.219-1.587-1.655l-0.019-0.012z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-solid-star">
          <path
            d="M22.592 27.501c-0.227 0-0.44-0.057-0.627-0.157l0.007 0.004-5.972-3.14-5.972 3.14c-0.18 0.097-0.394 0.153-0.62 0.153-0.294 0-0.567-0.095-0.787-0.257l0.004 0.003c-0.336-0.245-0.552-0.637-0.552-1.081 0-0.079 0.007-0.156 0.020-0.231l-0.001 0.008 1.141-6.651-4.832-4.711c-0.248-0.243-0.403-0.58-0.403-0.955 0-0.147 0.024-0.289 0.068-0.421l-0.003 0.009c0.159-0.478 0.568-0.831 1.069-0.906l0.007-0.001 6.677-0.969 2.987-6.051c0.245-0.412 0.688-0.684 1.195-0.684s0.949 0.272 1.191 0.677l0.004 0.006 2.987 6.051 6.677 0.969c0.508 0.076 0.917 0.429 1.073 0.897l0.003 0.009c0.041 0.123 0.065 0.265 0.065 0.412 0 0.374-0.154 0.712-0.402 0.955l-0 0-4.832 4.711 1.141 6.651c0.012 0.067 0.019 0.145 0.019 0.223 0 0.443-0.216 0.836-0.548 1.078l-0.004 0.003c-0.216 0.158-0.486 0.253-0.778 0.255h-0z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-speaker">
          <path
            d="M2 15.999c0.004 6.272 4.82 11.419 10.955 11.948l0.045 0.003v-1.501c-5.35-0.529-9.495-5.005-9.495-10.449s4.145-9.921 9.451-10.446l0.044-0.004v-1.501c-6.18 0.532-10.996 5.678-11 11.95v0z"
          ></path>
          <path
            d="M5 15.999c0.001 4.617 3.479 8.422 7.958 8.937l0.042 0.004v-1.516c-3.686-0.509-6.495-3.639-6.495-7.425s2.808-6.916 6.456-7.421l0.039-0.004v-1.516c-4.521 0.52-7.999 4.324-8 8.941v0z"
          ></path>
          <path
            d="M8 15.999c0.003 2.958 2.146 5.415 4.964 5.906l0.036 0.005v-1.529c-2.017-0.469-3.497-2.252-3.497-4.379s1.48-3.91 3.467-4.373l0.030-0.006v-1.532c-2.854 0.496-4.996 2.952-5 5.909v0z"
          ></path>
          <path
            d="M28.5 10.251h-3.485l-7.544-6.084c-0.128-0.103-0.292-0.166-0.471-0.166-0.414 0-0.75 0.336-0.751 0.75v22.5c0 0.414 0.336 0.75 0.751 0.75 0.179 0 0.343-0.063 0.472-0.167l-0.001 0.001 7.544-6.084h3.485c0.828 0 1.5-0.672 1.5-1.5v0-8.5c0-0.828-0.672-1.5-1.5-1.5v0zM28.5 20.251h-4.016l-6.735 5.432v-19.364l6.735 5.432h4.015z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-stand">
          <path
            d="M26.137 18.385h-0.597v-1.789c0-0.329-0.267-0.597-0.596-0.597s-0.596 0.267-0.596 0.597v1.789h-16.697v-1.789c0-0.329-0.267-0.597-0.596-0.597s-0.596 0.267-0.596 0.597v1.789h-0.596c-0.329 0-0.596 0.267-0.596 0.596v7.156c0 0.329 0.267 0.596 0.596 0.596h20.275c0.329 0 0.596-0.267 0.596-0.596v-7.156c0-0.329-0.267-0.596-0.596-0.596zM27.094 8.338c-0.25-1.752-1.774-3.072-3.542-3.072h-15.103c-1.769 0-3.292 1.32-3.542 3.072l-0.826 5.789c-0.031 0.223 0.064 0.445 0.249 0.574s0.426 0.142 0.624 0.036c0.804-0.434 2.323-1.122 2.979-1.122 0.627 0 1.018 0.217 1.513 0.491 0.593 0.329 1.265 0.701 2.38 0.701 1.195 0 1.942-0.377 2.6-0.71 0.534-0.27 0.958-0.483 1.574-0.483s1.040 0.213 1.574 0.483c0.658 0.333 1.405 0.71 2.6 0.71 1.115 0 1.788-0.372 2.382-0.701 0.495-0.274 0.885-0.491 1.512-0.491 0.655 0 2.174 0.688 2.979 1.121 0.198 0.106 0.44 0.093 0.624-0.036s0.28-0.351 0.249-0.574l-0.826-5.788z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-stopwatch">
          <path
            d="M10.92 6.473l-0.048-0.115c-0.13-0.303-0.205-0.655-0.205-1.025 0-1.473 1.194-2.667 2.666-2.667h5.333c1.473 0 2.666 1.194 2.666 2.667 0 0.37-0.076 0.723-0.212 1.043l0.007-0.017-0.048 0.115c0.915 0.433 1.703 0.929 2.423 1.508l-0.023-0.018 1.853-1.848 1.885 1.885-1.848 1.848c1.638 2.034 2.63 4.65 2.63 7.497 0 6.628-5.372 12-12 12s-12-5.372-12-12c0-4.783 2.798-8.912 6.847-10.84l0.073-0.031zM16 26.667c5.155 0 9.333-4.179 9.333-9.333s-4.179-9.333-9.333-9.333c-5.155 0-9.333 4.179-9.333 9.333v0c0.006 5.152 4.181 9.327 9.333 9.333h0.001z"
          ></path>
          <path
            d="M16 10.667v6.667h6.667c0 3.682-2.985 6.667-6.667 6.667s-6.667-2.985-6.667-6.667c0-3.682 2.985-6.667 6.667-6.667v0z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-ticket">
          <path
            d="M24 2.692h-5.333c0 1.473-1.194 2.667-2.667 2.667v0c-1.473 0-2.667-1.194-2.667-2.667v0h-5.333c-1.473 0-2.667 1.194-2.667 2.667v0 21.333c0 1.473 1.194 2.667 2.667 2.667v0h5.333c0-1.473 1.194-2.667 2.667-2.667v0c1.473 0 2.667 1.194 2.667 2.667v0h5.333c1.473 0 2.667-1.194 2.667-2.667v0-21.333c0-1.473-1.194-2.667-2.667-2.667v0zM24 17.359v9.333h-3.383c-0.939-1.605-2.654-2.666-4.617-2.667v0c-1.963 0.001-3.678 1.062-4.604 2.642l-0.014 0.025h-3.383v-9.333h2.667v-2.667h-2.667v-9.333h3.383c0.939 1.605 2.654 2.666 4.617 2.667v0c1.963-0.001 3.678-1.062 4.604-2.642l0.014-0.025h3.383v9.333h-2.667v2.667z"
          ></path>
          <path d="M13.333 14.692h5.333v2.667h-5.333v-2.667z"></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-leisure-pass">
          <path
            d="M26.667 5.333C28.139 5.333 29.333 6.527 29.333 8V24C29.333 25.473 28.139 26.667 26.667 26.667H5.333C3.861 26.667 2.667 25.473 2.667 24V8C2.667 6.527 3.861 5.333 5.333 5.333H26.667ZM6.667 8C5.93 8 5.333 8.597 5.333 9.333V22.667C5.333 23.403 5.93 24 6.667 24H25.333C26.07 24 26.667 23.403 26.667 22.667V9.333C26.667 8.597 26.07 8 25.333 8H6.667ZM16 14C17.415 14 18.771 14.562 19.771 15.563C20.771 16.563 21.333 17.919 21.333 19.333H24V22H8V19.333H10.667C10.667 17.919 11.229 16.563 12.229 15.563C13.229 14.562 14.585 14 16 14ZM16 16.667C15.293 16.667 14.615 16.948 14.115 17.448C13.615 17.948 13.333 18.626 13.333 19.333H18.667C18.667 18.626 18.385 17.948 17.885 17.448C17.385 16.948 16.707 16.667 16 16.667ZM11.943 13.391L10.057 15.276L8.177 13.396L10.063 11.51L11.943 13.391ZM23.823 13.396L21.943 15.276L20.057 13.391L21.937 11.51L23.823 13.396ZM17.333 12.667H14.667V10H17.333V12.667Z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-twitter">
          <path
            d="M11.053 27.005c0.030 0 0.065 0 0.1 0 8.542 0 15.467-6.925 15.467-15.467 0-0.035 0-0.070-0-0.106v0.005c0-0.236 0-0.472-0.016-0.707 1.075-0.782 1.98-1.72 2.704-2.79l0.025-0.039c-0.915 0.417-1.976 0.721-3.089 0.856l-0.052 0.005c1.129-0.686 1.977-1.738 2.389-2.988l0.011-0.039c-1 0.606-2.164 1.068-3.404 1.321l-0.071 0.012c-1.001-1.066-2.42-1.73-3.993-1.73-3.023 0-5.473 2.45-5.473 5.473v0c0 0.004 0 0.008 0 0.013 0 0.438 0.052 0.863 0.15 1.271l-0.007-0.037c-4.557-0.238-8.564-2.407-11.249-5.697l-0.022-0.028c-0.465 0.788-0.74 1.736-0.74 2.749 0 1.89 0.958 3.556 2.414 4.54l0.020 0.012c-0.915-0.027-1.767-0.276-2.51-0.695l0.027 0.014c0 0.023 0 0.045 0 0.069 0 2.639 1.868 4.841 4.354 5.358l0.036 0.006c-0.431 0.122-0.925 0.192-1.436 0.192-0.366 0-0.723-0.036-1.069-0.105l0.035 0.006c0.719 2.191 2.726 3.754 5.105 3.8h0.005c-1.846 1.46-4.208 2.343-6.776 2.343-0.008 0-0.017 0-0.025 0h0.001c-0.457 0-0.907-0.028-1.349-0.081l0.053 0.005c2.366 1.541 5.261 2.457 8.371 2.457 0.006 0 0.011 0 0.017 0h-0.001z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-twitter-x">
          <path
            d="M28.567 27.998l-9.875-14.118 8.531-9.877h-2.398l-7.215 8.33-5.826-8.33h-7.723l9.282 13.268-9.29 10.729h2.45l7.923-9.178 6.419 9.178h7.723zM10.86 6.036l13.978 19.927h-3.075l-13.978-19.927h3.075z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-unfollow-user">
          <path
            d="M20.885 19.855c-0.485-0.72-1.297-1.188-2.219-1.188h-10.667c-0.922 0-1.734 0.468-2.212 1.179l-0.006 0.010-2.667 4c-0.28 0.416-0.447 0.928-0.447 1.479 0 1.473 1.194 2.666 2.666 2.667h16c1.473-0 2.666-1.194 2.666-2.667 0-0.551-0.167-1.063-0.453-1.488l0.006 0.010zM5.333 25.333l2.667-4h10.667l2.667 4z"
          ></path>
          <path
            d="M13.333 16c3.314 0 6-2.686 6-6s-2.686-6-6-6c-3.314 0-6 2.686-6 6v0c0 3.314 2.686 6 6 6v0zM13.333 6.667c1.841 0 3.333 1.492 3.333 3.333s-1.492 3.333-3.333 3.333c-1.841 0-3.333-1.492-3.333-3.333v0c0-1.841 1.492-3.333 3.333-3.333v0z"
          ></path>
          <path d="M21.333 13.333h8v2.667h-8v-2.667z"></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-user-group">
          <path
            d="M19.552 19.855c-0.485-0.72-1.297-1.188-2.219-1.188h-8c-0.922 0-1.734 0.468-2.212 1.179l-0.006 0.010-2.667 4c-0.281 0.416-0.448 0.928-0.448 1.48 0 0.46 0.117 0.893 0.322 1.271l-0.007-0.014c0.459 0.844 1.339 1.408 2.351 1.408 0 0 0.001 0 0.001 0h13.333c1.012 0 1.892-0.564 2.344-1.394l0.007-0.014c0.198-0.364 0.315-0.797 0.315-1.257 0-0.552-0.167-1.064-0.454-1.489l0.006 0.010zM6.667 25.333l2.667-4h8l2.667 4z"
          ></path>
          <path
            d="M27.556 23.855l-2.5-4c-0.485-0.72-1.297-1.188-2.219-1.188h-1.504c0.053 0.068 0.119 0.121 0.167 0.193l3.433 5.14c0.216 0.335 0.378 0.726 0.463 1.146l0.004 0.022 0.029 0.163c0.036 0.187 0.057 0.402 0.057 0.621 0 0.573-0.142 1.113-0.393 1.587l0.009-0.019c-0.109 0.18-0.224 0.336-0.35 0.481l0.004-0.004h0.752c0.964-0.005 1.794-0.574 2.178-1.393l0.006-0.015c0.197-0.363 0.313-0.795 0.313-1.254 0-0.552-0.168-1.065-0.455-1.491l0.006 0.009z"
          ></path>
          <path
            d="M13.333 16c3.314 0 6-2.686 6-6v0c0-3.314-2.686-6-6-6v0c-3.314 0-6 2.686-6 6v0c0 3.314 2.686 6 6 6v0zM13.333 6.667c1.841 0 3.333 1.492 3.333 3.333v0c0 1.841-1.492 3.333-3.333 3.333v0c-1.841 0-3.333-1.492-3.333-3.333v0c0-1.841 1.492-3.333 3.333-3.333v0z"
          ></path>
          <path
            d="M24.667 10c-0.105-2.786-2.243-5.042-4.968-5.331l-0.024-0.002c1.431 1.306 2.326 3.18 2.326 5.262 0 0.025 0 0.050-0 0.075v-0.004c0 0.021 0 0.046 0 0.071 0 2.082-0.894 3.956-2.32 5.257l-0.005 0.005c2.749-0.291 4.887-2.547 4.992-5.323l0-0.010z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-video-camera">
          <path
            d="M28.667 8.18c-0.191-0.112-0.421-0.179-0.667-0.179s-0.476 0.066-0.673 0.182l0.006-0.004-4.667 2.667v-2.248c0-1.067-0.865-1.932-1.932-1.932v0h-16.133c-0.001 0-0.002 0-0.003 0-1.067 0-1.932 0.865-1.932 1.932v0 14.8c0 0.001 0 0.002 0 0.003 0 1.067 0.865 1.932 1.932 1.932h16.133c0.001 0 0.002 0 0.003 0 1.067 0 1.932-0.865 1.932-1.932v0-2.247l4.667 2.667c0.191 0.112 0.421 0.179 0.667 0.179 0.736 0 1.333-0.597 1.333-1.333v0-13.333c-0.001-0.491-0.266-0.919-0.66-1.15l-0.006-0.004zM20 22.667h-14.667v-13.333h14.667v13.333zM26.667 20.368l-4-2.284v-4.167l4-2.285z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-weather">
          <path
            d="M27.627 19.245c-0.036-0.104-0.057-0.213-0.099-0.316-0.159-0.393-0.331-0.727-0.529-1.044l0.016 0.028-0.012-0.024c-0.221-0.355-0.454-0.663-0.711-0.949l0.005 0.006c1.061-1.34 1.703-3.055 1.703-4.92 0-0.001 0-0.002 0-0.003v0c0-4.418-3.582-8-8-8v0c-3.954 0.004-7.235 2.876-7.877 6.647l-0.007 0.047c-0.283 0.017-0.561 0.049-0.836 0.093v0c-0.267 0.044-0.541 0.101-0.8 0.169h-0.012c-0.259 0.069-0.513 0.148-0.763 0.239l-0.027 0.011q-0.364 0.133-0.712 0.299l-0.049 0.024c-0.223 0.108-0.441 0.224-0.653 0.349l-0.072 0.044c-0.201 0.123-0.4 0.252-0.589 0.389l-0.099 0.075c-0.179 0.133-0.353 0.267-0.52 0.417-0.043 0.036-0.081 0.073-0.123 0.111-0.155 0.141-0.307 0.285-0.451 0.436-0.049 0.051-0.093 0.103-0.141 0.155-0.133 0.145-0.26 0.291-0.383 0.444-0.052 0.067-0.101 0.133-0.152 0.205-0.109 0.145-0.217 0.291-0.316 0.444-0.056 0.084-0.105 0.172-0.159 0.259-0.087 0.144-0.173 0.287-0.252 0.435-0.055 0.104-0.103 0.212-0.153 0.319-0.067 0.133-0.133 0.276-0.193 0.42-0.051 0.123-0.093 0.251-0.133 0.377s-0.099 0.267-0.133 0.4-0.077 0.297-0.115 0.445c-0.031 0.123-0.065 0.243-0.091 0.367-0.036 0.177-0.057 0.359-0.083 0.54-0.013 0.101-0.033 0.2-0.044 0.303-0.028 0.287-0.044 0.576-0.044 0.869 0.053 3.024 1.6 5.675 3.932 7.253l0.031 0.020c1.108 0.838 2.498 1.356 4.008 1.395l0.009 0h9.333c3.682 0 6.667-2.985 6.667-6.667v0c-0.003-0.452-0.052-0.892-0.141-1.316l0.008 0.043c-0.072-0.332-0.155-0.612-0.255-0.885l0.015 0.046zM20 6.691c2.946 0 5.333 2.388 5.333 5.333v0c0 0.004 0 0.007 0 0.011 0 1.239-0.428 2.379-1.144 3.278l0.008-0.011-0.032 0.041c-0.828-0.408-1.801-0.649-2.83-0.653h-1.373c-1.181-1.827-2.985-3.164-5.105-3.72l-0.060-0.013c0.498-2.449 2.634-4.267 5.194-4.267 0.004 0 0.007 0 0.011 0h-0.001zM21.333 25.357h-9.333c-0.217-0-0.43-0.020-0.636-0.059l0.021 0.003c-0.72-0.153-1.353-0.449-1.896-0.858l0.012 0.009c-1.676-1.11-2.78-2.968-2.835-5.087v-0.008c0.001-0.417 0.044-0.824 0.125-1.216l-0.007 0.039c0.035-0.169 0.099-0.327 0.147-0.491 0.062-0.246 0.127-0.448 0.201-0.644l-0.013 0.040c0.104-0.228 0.201-0.412 0.307-0.591l-0.016 0.029c0.079-0.145 0.145-0.296 0.236-0.433 0.14-0.2 0.277-0.374 0.424-0.539l-0.005 0.006c0.093-0.112 0.177-0.231 0.277-0.336 0.164-0.163 0.338-0.317 0.52-0.461l0.013-0.010c0.105-0.087 0.203-0.18 0.313-0.259 0.178-0.124 0.384-0.25 0.598-0.363l0.032-0.016c0.113-0.063 0.223-0.133 0.341-0.185 0.191-0.088 0.428-0.179 0.671-0.255l0.044-0.012c0.12-0.039 0.239-0.083 0.363-0.113 0.218-0.052 0.482-0.096 0.752-0.122l0.024-0.002c0.187-0.032 0.412-0.055 0.64-0.066l0.013-0.001c0.001 0 0.003 0 0.004 0 2.112 0 3.969 1.095 5.032 2.748l0.014 0.024 0.787 1.228h2.828c1.064 0.002 2.029 0.421 2.741 1.103l-0.002-0.001 0.231 0.267c0.419 0.468 0.732 1.038 0.899 1.669l0.006 0.028c0.008 0.033 0.023 0.064 0.031 0.097 0.059 0.252 0.093 0.54 0.093 0.837v0c0 2.209-1.791 4-4 4v0z"
          ></path>
        </symbol>
        <symbol viewBox="0 0 32 32" id="m-icon-whatsapp">
          <path
            d="M22.109 18.647c-0.335-0.167-1.963-0.964-2.267-1.067s-0.525-0.167-0.748 0.167-0.856 1.067-1.049 1.291-0.388 0.233-0.717 0.084c-1.027-0.416-1.91-0.972-2.675-1.658l0.009 0.007c-0.714-0.664-1.327-1.423-1.822-2.26l-0.026-0.047c-0.193-0.333-0.021-0.517 0.144-0.683s0.335-0.384 0.501-0.581c0.124-0.16 0.234-0.34 0.324-0.533l0.007-0.017c0.038-0.078 0.061-0.169 0.061-0.266 0-0.117-0.033-0.227-0.090-0.32l0.002 0.003c-0.084-0.167-0.747-1.8-1.024-2.451s-0.541-0.567-0.747-0.567-0.413-0.016-0.635-0.016c-0.35 0.009-0.663 0.161-0.884 0.399l-0.001 0.001c-0.716 0.678-1.162 1.635-1.162 2.696 0 0.019 0 0.038 0 0.058v-0.003c0.124 1.304 0.615 2.473 1.368 3.427l-0.011-0.014c1.436 2.115 3.345 3.806 5.583 4.945l0.084 0.039c0.793 0.333 1.411 0.533 1.893 0.699 0.398 0.125 0.855 0.196 1.33 0.196 0.268 0 0.531-0.023 0.787-0.067l-0.027 0.004c0.956-0.201 1.749-0.78 2.231-1.569l0.009-0.015c0.151-0.33 0.239-0.715 0.239-1.121 0-0.163-0.014-0.323-0.041-0.478l0.003 0.017c-0.083-0.151-0.3-0.233-0.633-0.384zM16.072 26.829h-0.017c-2.072-0.003-4.010-0.572-5.668-1.562l0.051 0.028-0.4-0.237-4.171 1.087 1.117-4.049-0.267-0.416c-1.045-1.654-1.665-3.667-1.665-5.824 0-6.086 4.934-11.020 11.020-11.020 3.051 0 5.813 1.24 7.809 3.244l0 0c1.996 1.973 3.232 4.71 3.232 7.737 0 0.011 0 0.021 0 0.032v-0.002c-0.028 6.072-4.957 10.984-11.033 10.984-0.002 0-0.004 0-0.006 0h0zM25.467 6.499c-2.428-2.37-5.751-3.831-9.416-3.832v0c-0.018 0-0.040 0-0.062 0-7.296 0-13.211 5.915-13.211 13.211 0 2.434 0.658 4.714 1.806 6.672l-0.034-0.062-1.884 6.847 7.039-1.836c1.834 1.009 4.019 1.604 6.343 1.608h0.008c0.003 0 0.007 0 0.011 0 7.31 0 13.238-5.912 13.267-13.215v-0.003c0-0.010 0-0.022 0-0.035 0-3.641-1.485-6.935-3.882-9.31l-0.001-0.001z"
          ></path>
        </symbol>
      </svg>
    `;
	}
};
customElements.define("m-icon-sprite", Te);
//#endregion
//#region ../mde-storybook/src/styles/shared.scss?inline
var B = ":host{color:var(--mde-color-body)}*,:before,:after{box-sizing:border-box}h1,h2,h3,h4,h5,h6{overflow-wrap:break-word;text-wrap:balance;margin-top:0;margin-bottom:0}p{overflow-wrap:break-word;text-wrap:pretty;margin-top:0;margin-bottom:0}button,input,select,textarea{font-optical-sizing:inherit}::slotted(p),::slotted(h1),::slotted(h2),::slotted(h3),::slotted(h4),::slotted(h5),::slotted(h6){margin:0}a{color:var(--mde-color-action-default);text-decoration:underline}a:hover{color:var(--mde-color-action-hover)}:focus{outline:var(--mde-focus-ring-width) solid var(--mde-color-focus-outline);outline-offset:var(--mde-focus-ring-offset)}:focus:not(:focus-visible){outline:0}:focus-visible{outline:var(--mde-focus-ring-width) solid var(--mde-color-focus-outline);outline-offset:var(--mde-focus-ring-offset)}m-icon{display:flex}.icon{fill:currentColor;flex-shrink:0;width:1.5rem;height:1.5rem}.visually-hidden,.visually-hidden-focusable:not(:focus):not(:focus-within){clip:rect(0, 0, 0, 0)!important;white-space:nowrap!important;border:0!important;width:1px!important;height:1px!important;margin:-1px!important;padding:0!important;position:absolute!important;overflow:hidden!important}", Ee = ":host{--offcanvas-bg:var(--mde-color-background-surface);--offcanvas-size:min(28rem, 100%);--offcanvas-inset-block-start:0;--offcanvas-inset-block-end:0;--_offcanvas-backdrop-bg:var(--mde-color-background-backdrop,#3a5368b3);--_offcanvas-closed:translateX(100%);--_offcanvas-inset:var(--offcanvas-inset-block-start) 0 var(--offcanvas-inset-block-end) auto;--_offcanvas-width:var(--offcanvas-size);--_offcanvas-height:auto;display:contents}:host([placement=start]){--_offcanvas-closed:translateX(-100%);--_offcanvas-inset:var(--offcanvas-inset-block-start) auto var(--offcanvas-inset-block-end) 0}:host([placement=top]){--_offcanvas-closed:translateY(-100%);--_offcanvas-inset:0 0 auto 0;--_offcanvas-width:100%;--_offcanvas-height:var(--offcanvas-block-size,auto)}:host([placement=bottom]){--_offcanvas-closed:translateY(100%);--_offcanvas-inset:auto 0 0 0;--_offcanvas-width:100%;--_offcanvas-height:var(--offcanvas-block-size,auto)}.m-offcanvas{inset:var(--_offcanvas-inset);z-index:var(--mde-z-index-header,100);width:var(--_offcanvas-width);height:var(--_offcanvas-height);overscroll-behavior:contain;max-width:100%;max-height:100%;color:inherit;background:var(--offcanvas-bg);opacity:var(--offcanvas-closed-opacity,1);transform:var(--offcanvas-closed-transform,var(--_offcanvas-closed));transition:transform var(--mde-motion-duration-base) ease, opacity var(--mde-motion-duration-base) ease, overlay var(--mde-motion-duration-base) allow-discrete, display var(--mde-motion-duration-base) allow-discrete;border:0;margin:0;padding:0;position:fixed}.m-offcanvas::backdrop{inset:var(--offcanvas-inset-block-start) 0 0 0;background:var(--_offcanvas-backdrop-bg)}.m-offcanvas[open]{opacity:1;transform:translate(0)}@starting-style{.m-offcanvas[open]{opacity:var(--offcanvas-closed-opacity,1);transform:var(--offcanvas-closed-transform,var(--_offcanvas-closed))}}.m-offcanvas__backdrop{inset:var(--offcanvas-inset-block-start) 0 0 0;z-index:calc(var(--mde-z-index-header,100) - 1);background:var(--_offcanvas-backdrop-bg);position:fixed}@media (prefers-reduced-motion:reduce){.m-offcanvas{transition:none}}", V = [
	"start",
	"end",
	"top",
	"bottom"
], De = 0, Oe = class extends z {
	static styles = [a(B), a(Ee)];
	static properties = {
		open: {
			type: Boolean,
			reflect: !0
		},
		placement: {
			type: String,
			reflect: !0
		},
		label: {
			type: String,
			reflect: !0
		},
		modal: {
			type: Boolean,
			reflect: !0
		},
		noHistory: {
			type: Boolean,
			attribute: "no-history"
		}
	};
	constructor() {
		super(), this.open = !1, this.placement = "end", this.label = void 0, this.modal = !0, this.noHistory = !1, this._triggerElement = null, this._historyKey = `m-offcanvas-${++De}-${Date.now()}`, this._historyPushed = !1, this._onPopState = () => {
			this._historyPushed && history.state?.mOffcanvas !== this._historyKey && (this._historyPushed = !1, this.close());
		};
	}
	connectedCallback() {
		super.connectedCallback(), window.addEventListener("popstate", this._onPopState);
	}
	get _dialogEl() {
		return this.renderRoot?.querySelector("dialog");
	}
	willUpdate(e) {
		e.has("placement") && !V.includes(this.placement) && (console.warn(`[m-offcanvas] Invalid placement "${this.placement}". Expected one of: ${V.join(", ")}.`), this.placement = "end");
	}
	updated(e) {
		if (!e.has("open")) return;
		let t = this._dialogEl;
		t && (this.open && !t.open ? (this._triggerElement = document.activeElement, this.modal ? t.showModal() : (t.show(), queueMicrotask(() => t.focus())), this._pushHistory(), this.dispatchEvent(new CustomEvent("m-offcanvas:open", {
			bubbles: !0,
			composed: !0
		}))) : !this.open && t.open && t.close());
	}
	disconnectedCallback() {
		super.disconnectedCallback(), window.removeEventListener("popstate", this._onPopState), this._popHistory(), this.open && this._returnFocus();
	}
	_pushHistory() {
		this.noHistory || this._historyPushed || (history.pushState({ mOffcanvas: this._historyKey }, ""), this._historyPushed = !0);
	}
	_popHistory() {
		this._historyPushed && (this._historyPushed = !1, history.state?.mOffcanvas === this._historyKey && history.back());
	}
	show() {
		this.open = !0;
	}
	close() {
		this.open = !1;
	}
	_handleClose() {
		this.open = !1, this._popHistory(), this.dispatchEvent(new CustomEvent("m-offcanvas:close", {
			bubbles: !0,
			composed: !0
		})), this._returnFocus();
	}
	_handleBackdropClick(e) {
		e.target === this._dialogEl && this.close();
	}
	_handleKeydown(e) {
		!this.modal && e.key === "Escape" && (e.preventDefault(), this.close());
	}
	_returnFocus() {
		let e = this._triggerElement;
		this._triggerElement = null;
		let t = document.activeElement;
		t && t !== document.body && !this.contains(t) || e && typeof e.focus == "function" && queueMicrotask(() => e.focus());
	}
	render() {
		return A`
      ${!this.modal && this.open ? A`<div
            class="m-offcanvas__backdrop"
            part="backdrop"
            @click="${this.close}"
          ></div>` : M}
      <dialog
        class="m-offcanvas"
        part="dialog"
        tabindex="-1"
        aria-label="${this.label ?? M}"
        aria-modal="${this.modal ? "true" : "false"}"
        @close="${this._handleClose}"
        @click="${this._handleBackdropClick}"
        @keydown="${this._handleKeydown}"
      >
        <slot></slot>
      </dialog>
    `;
	}
};
customElements.define("m-offcanvas", Oe);
//#endregion
//#region ../mde-storybook/src/components/header-action/header-action.scss?inline
var ke = ":host{--header-action-min-height:3rem;--header-action-gap:var(--mde-spacing-050);--header-action-color:var(--mde-color-white);--header-action-font-family:var(--mde-typography-font-family-base);--header-action-font-weight:var(--mde-typography-font-weight-bold);--header-action-icon-size:1.5rem;--header-action-padding:0;--header-action-focus-color:var(--mde-color-white);--header-action-circle-size:2.75rem;--header-action-circle-bg:var(--mde-color-blue-xdark);--header-action-circle-color:var(--mde-color-white);--header-action-circle-icon-size:1.25rem;--header-action-circle-radius:var(--mde-border-radius-pill);--header-action-count-size:1rem;--header-action-count-bg:var(--mde-color-error);--header-action-count-color:var(--mde-color-white);--header-action-count-border-color:var(--mde-color-white);--header-action-count-font-size:.625rem;--header-action-menu-width:18.5rem;--header-action-menu-max-width:calc(100vw - var(--mde-spacing-400));--header-action-menu-offset:2px;display:inline-flex}.m-header-action__control{gap:var(--header-action-gap);min-height:var(--header-action-min-height);padding:var(--header-action-padding);font-family:var(--header-action-font-family);font-weight:var(--header-action-font-weight);color:var(--header-action-color);align-items:center;text-decoration:none;display:flex}.m-header-action__control:hover,.m-header-action__control:focus-visible{color:var(--header-action-color)}.m-header-action__control:hover .m-header-action__label,.m-header-action__control:focus-visible .m-header-action__label{text-decoration:underline}.m-header-action__control:focus-visible{outline:var(--mde-focus-ring-width) solid var(--header-action-focus-color);outline-offset:var(--mde-focus-ring-offset)}.m-header-action__icon{flex-shrink:0;justify-content:center;align-items:center;display:flex;position:relative}.m-header-action__icon .icon{width:var(--header-action-icon-size);height:var(--header-action-icon-size);fill:currentColor}.m-header-action__count{min-width:var(--header-action-count-size);height:var(--header-action-count-size);padding:0 var(--mde-spacing-050);font-size:var(--header-action-count-font-size);font-weight:var(--mde-typography-font-weight-bold);color:var(--header-action-count-color);background:var(--header-action-count-bg);border:1.5px solid var(--header-action-count-border-color);border-radius:var(--mde-border-radius-pill);justify-content:center;align-items:center;line-height:1;display:flex;position:absolute;top:0;right:0;transform:translate(50%,-50%)}.m-header-action__dropdown{--dropdown-menu-offset:var(--header-action-menu-offset);--dropdown-menu-width:var(--header-action-menu-width);--dropdown-menu-max-width:var(--header-action-menu-max-width)}.m-header-action__dropdown::part(icon){display:none}.m-header-action__dropdown::part(menu){padding-top:0;overflow:hidden}.m-header-action__dropdown::part(toggle):hover{color:inherit}.m-header-action__dropdown::part(toggle):focus-visible{outline:var(--mde-focus-ring-width) solid var(--header-action-focus-color);outline-offset:var(--mde-focus-ring-offset)}:host([icon-only]) .m-header-action__control{width:var(--header-action-circle-size);height:var(--header-action-circle-size);min-height:var(--header-action-circle-size);color:var(--header-action-circle-color);background:var(--header-action-circle-bg);border-radius:var(--header-action-circle-radius);justify-content:center;gap:0;padding:0;position:relative}:host([icon-only]) .m-header-action__control:hover,:host([icon-only]) .m-header-action__control:focus-visible{color:var(--header-action-circle-color)}:host([icon-only]) .m-header-action__icon{position:static}:host([icon-only]) .m-header-action__icon .icon{width:var(--header-action-circle-icon-size);height:var(--header-action-circle-icon-size)}:host([icon-only]) .m-header-action__count{transform:translate(25%,-25%)}:host([icon-only]) .m-header-action__label{clip:rect(0, 0, 0, 0)!important;white-space:nowrap!important;border:0!important;width:1px!important;height:1px!important;margin:-1px!important;padding:0!important;position:absolute!important;overflow:hidden!important}@media (width<=991.98px){:host .m-header-action__control{width:var(--header-action-circle-size);height:var(--header-action-circle-size);min-height:var(--header-action-circle-size);color:var(--header-action-circle-color);background:var(--header-action-circle-bg);border-radius:var(--header-action-circle-radius);justify-content:center;gap:0;padding:0;position:relative}:host .m-header-action__control:hover,:host .m-header-action__control:focus-visible{color:var(--header-action-circle-color)}:host .m-header-action__icon{position:static}:host .m-header-action__icon .icon{width:var(--header-action-circle-icon-size);height:var(--header-action-circle-icon-size)}:host .m-header-action__count{transform:translate(25%,-25%)}:host .m-header-action__label{clip:rect(0, 0, 0, 0)!important;white-space:nowrap!important;border:0!important;width:1px!important;height:1px!important;margin:-1px!important;padding:0!important;position:absolute!important;overflow:hidden!important}}", Ae = "m-icon{flex-shrink:0;display:flex}.icon{width:var(--m-icon-size,1.5rem);height:var(--m-icon-size,1.5rem);fill:currentColor;flex-shrink:0}", je = class e extends z {
	createRenderRoot() {
		return this;
	}
	static properties = { name: {
		type: String,
		reflect: !0
	} };
	constructor() {
		super(), this.name = "";
	}
	firstUpdated() {
		if (!e._globalStyleApplied) {
			let t = document.createElement("style");
			t.textContent = Ae, document.head.appendChild(t), e._globalStyleApplied = !0;
		}
	}
	render() {
		return this.name ? A`
      <svg aria-hidden="true" class="icon">
        <use href="#m-icon-${this.name}"></use>
      </svg>
    ` : (console.warn("[m-icon] name property is required"), A``);
	}
};
customElements.define("m-icon", je);
//#endregion
//#region ../mde-storybook/src/components/dropdown/dropdown.scss?inline
var Me = ":host{--dropdown-toggle-gap:var(--mde-spacing-050);--dropdown-icon-size:1.5rem;--dropdown-menu-bg:var(--mde-component-dropdown-menu-background);--dropdown-menu-border-color:var(--mde-component-dropdown-menu-border-color);--dropdown-menu-min-width:13.1875rem;--dropdown-menu-width:fit-content;--dropdown-menu-max-width:none;--dropdown-menu-offset:0;--dropdown-menu-radius:var(--mde-component-dropdown-menu-radius);--dropdown-menu-shadow:var(--mde-component-dropdown-menu-shadow);display:block}:host([inline]){display:block}.m-dropdown{position:relative}.m-dropdown__toggle{align-items:center;gap:var(--dropdown-toggle-gap);width:100%;font:inherit;color:inherit;text-align:left;cursor:pointer;background:0 0;border:0;padding:0;display:flex}.m-dropdown__toggle:focus-visible{outline:var(--mde-focus-ring-width) solid var(--mde-color-focus-outline);outline-offset:var(--mde-focus-ring-offset)}.m-dropdown__icon{flex-shrink:0}.m-dropdown__icon .icon{width:var(--dropdown-icon-size);height:var(--dropdown-icon-size);fill:currentColor}:host([open]) .m-dropdown__icon{transform:rotate(180deg)}.m-dropdown__menu{min-width:var(--dropdown-menu-min-width);padding:var(--mde-component-dropdown-menu-padding-block) 0;background-color:var(--dropdown-menu-bg);border-radius:var(--dropdown-menu-radius);margin:0;list-style:none}:host([inline]) .m-dropdown__menu{background:0 0;width:100%}.m-dropdown__menu[popover]{width:var(--dropdown-menu-width);max-width:var(--dropdown-menu-max-width);padding-block:calc(var(--mde-component-dropdown-menu-padding-block) - 1px);margin-top:var(--dropdown-menu-offset);border:1px solid var(--dropdown-menu-border-color);box-shadow:var(--dropdown-menu-shadow);position:fixed}";
//#endregion
//#region ../mde-storybook/src/utils/inject-icon-sprite.js
async function H(e) {
	let t = e.shadowRoot;
	if (!t || t.querySelector("svg.m-sprite")) return;
	let n = e.ownerDocument, r = n.querySelector("m-icon-sprite");
	if (!r) {
		console.warn(`[${e.localName}] Icon sprite not found. Ensure m-icon-sprite is loaded before.`);
		return;
	}
	await (n.defaultView?.customElements ?? customElements).whenDefined("m-icon-sprite"), await r.updateComplete;
	let i = r.querySelector("svg");
	if (!i) {
		console.warn(`[${e.localName}] Icon sprite did not render an SVG.`);
		return;
	}
	let a = i.cloneNode(!0);
	a.classList.add("m-sprite"), a.removeAttribute("width"), a.removeAttribute("height"), a.style.cssText = "display: none !important; position: absolute;", t.insertBefore(a, t.firstChild);
}
//#endregion
//#region ../mde-storybook/src/components/dropdown/dropdown.js
var Ne = 0, Pe = "a,button,[role=\"menuitem\"]", Fe = class extends z {
	static styles = [a(B), a(Me)];
	static properties = {
		label: {
			type: String,
			reflect: !0
		},
		open: {
			type: Boolean,
			reflect: !0
		},
		inline: {
			type: Boolean,
			reflect: !0
		},
		align: {
			type: String,
			reflect: !0
		},
		navigation: {
			type: Boolean,
			reflect: !0
		},
		icon: { type: String }
	};
	constructor() {
		super(), this.label = "", this.open = !1, this.inline = !1, this.align = "start", this.navigation = !1, this.icon = "chevron-down", this._uid = ++Ne, this._menuId = `m-dropdown-menu-${this._uid}`, this._onDocClick = (e) => {
			this.inline || !this.open || e.composedPath().includes(this) || this._setOpen(!1);
		}, this._onDocKeydown = (e) => {
			this.inline || !this.open || e.key !== "Escape" || (this._setOpen(!1), this._toggleEl?.focus());
		};
	}
	disconnectedCallback() {
		super.disconnectedCallback(), this._unbindOpenListeners();
	}
	firstUpdated() {
		H(this);
	}
	_bindOpenListeners() {
		document.addEventListener("click", this._onDocClick), document.addEventListener("keydown", this._onDocKeydown), window.addEventListener("scroll", this._schedulePosition, !0), window.addEventListener("resize", this._schedulePosition);
	}
	_unbindOpenListeners() {
		document.removeEventListener("click", this._onDocClick), document.removeEventListener("keydown", this._onDocKeydown), window.removeEventListener("scroll", this._schedulePosition, !0), window.removeEventListener("resize", this._schedulePosition), this._positionRaf &&= (cancelAnimationFrame(this._positionRaf), 0);
	}
	get _toggleEl() {
		return this.renderRoot?.querySelector(".m-dropdown__toggle");
	}
	get _menuEl() {
		return this.renderRoot?.querySelector(".m-dropdown__menu");
	}
	_setOpen(e) {
		e !== this.open && (this.open = e, this.dispatchEvent(new CustomEvent("m-dropdown:toggle", {
			detail: { open: e },
			bubbles: !0,
			composed: !0
		})));
	}
	_toggle() {
		this._setOpen(!this.open);
	}
	show() {
		this._setOpen(!0);
	}
	close() {
		this._setOpen(!1);
	}
	updated(e) {
		if (!e.has("open") && !e.has("inline")) return;
		let t = this._menuEl;
		if (!(!t || typeof t.showPopover != "function")) if (this.open && !this.inline) {
			if (!t.matches(":popover-open")) try {
				t.showPopover();
			} catch {}
			this._position(), this._bindOpenListeners();
		} else t.matches?.(":popover-open") && t.hidePopover(), this._unbindOpenListeners();
	}
	_schedulePosition = () => {
		this._positionRaf ||= requestAnimationFrame(() => {
			this._positionRaf = 0, this._position();
		});
	};
	_position = () => {
		let e = this._menuEl, t = this._toggleEl;
		if (!e || !t || !e.matches?.(":popover-open")) return;
		let n = t.getBoundingClientRect();
		e.style.top = `${n.bottom}px`;
		let r = document.documentElement.clientWidth, i = Math.max(0, r - e.offsetWidth);
		this.align === "end" ? (e.style.left = "auto", e.style.right = `${Math.min(r - n.right, i)}px`) : (e.style.right = "auto", e.style.left = `${Math.min(n.left, i)}px`);
	};
	_menuItems() {
		let e = this.renderRoot?.querySelector("slot:not([name])"), t = e ? e.assignedElements({ flatten: !0 }) : [], n = [], r = (e) => {
			if (!(e instanceof SVGElement)) {
				if (e instanceof HTMLSlotElement) {
					e.assignedElements({ flatten: !0 }).forEach(r);
					return;
				}
				e.matches(Pe) && n.push(e);
				for (let t of (e.shadowRoot ?? e).children) r(t);
			}
		};
		return t.forEach(r), n.filter((e) => e.checkVisibility?.() ?? e.getClientRects().length > 0);
	}
	_focusItem(e, t = this._menuItems()) {
		t.length && t[(e + t.length) % t.length].focus();
	}
	_handleInlineEscape(e) {
		return e.key !== "Escape" || !this.open ? !1 : (e.preventDefault(), e.stopPropagation(), this._setOpen(!1), this._toggleEl?.focus(), !0);
	}
	_handleToggleKeydown(e) {
		if (this.inline) {
			this._handleInlineEscape(e);
			return;
		}
		(e.key === "ArrowDown" || e.key === "ArrowUp") && (e.preventDefault(), this._setOpen(!0), this.updateComplete.then(() => this._focusItem(e.key === "ArrowDown" ? 0 : -1)));
	}
	_handleMenuKeydown(e) {
		if (this.inline) {
			this._handleInlineEscape(e);
			return;
		}
		let t = this._menuItems(), n = t.indexOf(e.composedPath().find((e) => t.includes(e)));
		switch (e.key) {
			case "ArrowDown":
				e.preventDefault(), this._focusItem(n + 1, t);
				break;
			case "ArrowUp":
				e.preventDefault(), this._focusItem(n - 1, t);
				break;
			case "Home":
				e.preventDefault(), this._focusItem(0, t);
				break;
			case "End":
				e.preventDefault(), this._focusItem(-1, t);
				break;
			case "Escape":
				e.preventDefault(), this._setOpen(!1), this._toggleEl?.focus();
				break;
			case "Tab":
				this._setOpen(!1);
				break;
		}
	}
	render() {
		return A`
      <div class="m-dropdown">
        <button
          class="m-dropdown__toggle"
          part="toggle"
          type="button"
          aria-haspopup="${this.navigation ? M : "true"}"
          aria-expanded="${this.open ? "true" : "false"}"
          aria-controls="${this._menuId}"
          @click="${this._toggle}"
          @keydown="${this._handleToggleKeydown}"
        >
          <slot name="toggle"><span>${this.label}</span></slot>
          <m-icon
            class="m-dropdown__icon"
            part="icon"
            name="${this.icon || "chevron-down"}"
            aria-hidden="true"
          ></m-icon>
        </button>
        <div
          class="m-dropdown__menu"
          part="menu"
          id="${this._menuId}"
          popover="${this.inline ? M : "manual"}"
          ?hidden="${!this.open}"
          @keydown="${this._handleMenuKeydown}"
        >
          <slot></slot>
        </div>
      </div>
    `;
	}
};
customElements.define("m-dropdown", Fe);
//#endregion
//#region ../mde-storybook/src/components/header-action/header-action.js
var Ie = class extends z {
	static styles = [a(B), a(ke)];
	static properties = {
		icon: { type: String },
		label: { type: String },
		href: { type: String },
		iconOnly: {
			type: Boolean,
			attribute: "icon-only",
			reflect: !0
		},
		count: { type: Number },
		countMax: {
			type: Number,
			attribute: "count-max"
		},
		countLabel: {
			type: String,
			attribute: "count-label"
		}
	};
	constructor() {
		super(), this.icon = "", this.label = "", this.href = "", this.iconOnly = !1, this.count = 0, this.countMax = 9, this.countLabel = "{count}";
	}
	firstUpdated() {
		H(this);
	}
	close() {
		this.renderRoot?.querySelector("m-dropdown")?.close();
	}
	_onDropdownToggle(e) {
		if (!(e.target !== e.currentTarget || e.detail?.open)) for (let e of this.children) e.reset?.();
	}
	_renderContent() {
		let e = Number(this.count), t = e > 0;
		return A`
      <span class="m-header-action__icon">
        <m-icon name="${this.icon || M}" aria-hidden="true"></m-icon>
        ${t ? A`<span class="m-header-action__count" aria-hidden="true"
              >${e > this.countMax ? `${this.countMax}+` : e}</span
            >` : M}
      </span>
      <span class="m-header-action__label">${this.label}</span>
      ${t ? A`<span class="visually-hidden"
            >${(this.countLabel ?? "{count}").replace("{count}", String(e))}</span
          >` : M}
    `;
	}
	render() {
		return this.href ? A`
        <a class="m-header-action__control" part="control" href="${this.href}">
          ${this._renderContent()}
        </a>
      ` : A`
      <m-dropdown
        class="m-header-action__dropdown"
        navigation
        align="end"
        exportparts="toggle, menu"
        @m-dropdown:toggle="${this._onDropdownToggle}"
      >
        <span slot="toggle" class="m-header-action__control">
          ${this._renderContent()}
        </span>
        <slot></slot>
      </m-dropdown>
    `;
	}
};
customElements.define("m-header-action", Ie);
//#endregion
//#region ../mde-storybook/src/components/menu-list/menu-list.scss?inline
var Le = ":host{--menu-list-item-gap:var(--mde-spacing-100);--menu-list-item-padding-y:var(--mde-component-dropdown-item-padding-block);--menu-list-item-padding-x:var(--mde-component-dropdown-item-padding-inline);--menu-list-item-color:var(--mde-component-menu-list-item-color);--menu-list-item-hover-color:var(--mde-component-menu-list-item-hover-color);--menu-list-item-font-family:var(--mde-typography-font-family-base);--menu-list-item-font-size:1rem;--menu-list-item-line-height:1.5;--menu-list-item-font-weight:var(--mde-typography-font-weight-normal);--menu-list-item-emphasis-font-weight:var(--mde-typography-font-weight-bold);--menu-list-item-padding-start:var(--menu-list-item-padding-x);--menu-list-item-padding-end:var(--menu-list-item-padding-x);--menu-list-back-font-size:var(--menu-list-item-font-size);--menu-list-back-padding-bottom:var(--menu-list-item-padding-y);--menu-list-back-icon-size:var(--menu-list-icon-size);--menu-list-bg:transparent;--menu-list-inset-top:var(--mde-spacing-100);--menu-list-separator-width:0;--menu-list-separator-color:var(--mde-color-border);--menu-list-separator-inset:0;--menu-list-item-hover-bg:var(--mde-component-dropdown-item-hover-background);--menu-list-icon-size:1.5rem;--menu-list-header-bg:var(--mde-color-beau-blue-light);--menu-list-header-padding-y:var(--mde-spacing-150);--menu-list-header-padding-x:var(--mde-spacing-200);--menu-list-header-color:var(--mde-color-body);--menu-list-avatar-size:2.25rem;--menu-list-avatar-bg:var(--mde-color-blue);--menu-list-avatar-color:var(--mde-color-white);--menu-list-avatar-icon-size:1.25rem;--menu-list-divider-color:var(--mde-component-divider-color);--menu-list-divider-width:var(--mde-component-divider-width);--menu-list-divider-spacing-y:var(--mde-spacing-100);--menu-list-divider-spacing-x:var(--menu-list-item-padding-x);display:block}.m-menu-list{font-size:var(--menu-list-item-font-size);line-height:var(--menu-list-item-line-height);background:var(--menu-list-bg)}.m-menu-list__body:first-child{padding-top:var(--menu-list-inset-top)}.m-menu-list__header{gap:var(--mde-spacing-150);padding:var(--menu-list-header-padding-y) var(--menu-list-header-padding-x);margin-bottom:var(--mde-spacing-100);color:var(--menu-list-header-color);text-align:left;font-family:var(--menu-list-item-font-family);background:var(--menu-list-header-bg);flex-direction:column;display:flex}.m-menu-list__user{gap:var(--mde-spacing-150);align-items:center;display:flex}.m-menu-list__avatar{width:var(--menu-list-avatar-size);height:var(--menu-list-avatar-size);color:var(--menu-list-avatar-color);background:var(--menu-list-avatar-bg);border-radius:var(--mde-border-radius-pill);flex-shrink:0;justify-content:center;align-items:center;display:flex}.m-menu-list__avatar .icon{width:var(--menu-list-avatar-icon-size);height:var(--menu-list-avatar-icon-size)}.m-menu-list__user-info{gap:var(--mde-spacing-050);min-width:0;font-family:var(--menu-list-item-font-family);overflow-wrap:anywhere;flex-direction:column;line-height:1.4;display:flex}.m-menu-list__user-label,.m-menu-list__user-email{font-size:var(--mde-typography-size-xs)}.m-menu-list__user-name{font-size:var(--mde-typography-size-base);font-weight:var(--mde-typography-font-weight-bold)}.m-menu-list--drilled>.m-menu-list__header{display:none}.m-menu-list--drilled>.m-menu-list__body{padding-top:var(--menu-list-inset-top)}", Re = class extends z {
	static styles = [a(B), a(Le)];
	static properties = {
		userName: {
			type: String,
			attribute: "user-name"
		},
		userEmail: {
			type: String,
			attribute: "user-email"
		},
		userLabel: {
			type: String,
			attribute: "user-label"
		},
		userIcon: {
			type: String,
			attribute: "user-icon"
		},
		_hasHeaderSlot: { state: !0 },
		_drilled: { state: !0 }
	};
	constructor() {
		super(), this.userName = "", this.userEmail = "", this.userLabel = "Angemeldet als", this.userIcon = "user", this._hasHeaderSlot = !1, this._drilled = !1, this._slotObserver = null, this._onDropdownToggle = (e) => this._syncLevels(e.target);
	}
	connectedCallback() {
		super.connectedCallback(), this._detectHeaderSlot(), this._slotObserver = new MutationObserver(() => this._detectHeaderSlot()), this._slotObserver.observe(this, {
			childList: !0,
			subtree: !0,
			attributes: !0,
			attributeFilter: ["slot"]
		}), this.addEventListener("m-dropdown:toggle", this._onDropdownToggle);
	}
	disconnectedCallback() {
		super.disconnectedCallback(), this._slotObserver?.disconnect(), this.removeEventListener("m-dropdown:toggle", this._onDropdownToggle);
	}
	_detectHeaderSlot() {
		this._hasHeaderSlot = !!this.querySelector(":scope > [slot=\"header\"]");
	}
	firstUpdated() {
		H(this);
	}
	get _levels() {
		return [...this.querySelectorAll("m-dropdown")];
	}
	_syncLevels(e) {
		if (!(!(e instanceof HTMLElement) || !this.contains(e))) {
			if (e.open) for (let t of this._levels) t.open && t !== e && !t.contains(e) && t.close();
			this._drilled = this._levels.some((e) => e.open);
		}
	}
	reset() {
		for (let e of this._levels) e.close?.();
		this._drilled = !1;
	}
	_renderHeader() {
		return !this.userName && !this._hasHeaderSlot ? M : A`
      <div class="m-menu-list__header">
        ${this.userName ? A`<div class="m-menu-list__user">
              <span class="m-menu-list__avatar" aria-hidden="true">
                <m-icon name="${this.userIcon || M}"></m-icon>
              </span>
              <div class="m-menu-list__user-info">
                <span class="m-menu-list__user-label">${this.userLabel}</span>
                <span class="m-menu-list__user-name">${this.userName}</span>
                ${this.userEmail ? A`<span class="m-menu-list__user-email"
                      >${this.userEmail}</span
                    >` : M}
              </div>
            </div>` : M}
        <slot name="header"></slot>
      </div>
    `;
	}
	render() {
		return A`
      <div class="m-menu-list${this._drilled ? " m-menu-list--drilled" : ""}">
        ${this._renderHeader()}
        <div class="m-menu-list__body"><slot></slot></div>
      </div>
    `;
	}
};
customElements.define("m-menu-list", Re);
//#endregion
//#region ../mde-storybook/src/utils/breakpoints.js
var ze = Object.freeze({
	xs: 0,
	sm: 576,
	md: 768,
	lg: 992,
	xl: 1200,
	xxl: 1400
});
function U(e) {
	return window.matchMedia(`(min-width: ${ze[e]}px)`);
}
//#endregion
//#region ../mde-storybook/src/components/header/header.js
var W = ["offcanvas", "dropdown"], Be = 8, Ve = 2, G = typeof CSSStyleSheet < "u" && "adoptedStyleSheets" in Document.prototype ? new CSSStyleSheet() : null, He = 0, Ue = class extends g {
	static properties = { menuMode: {
		type: String,
		attribute: "menu-mode",
		reflect: !0
	} };
	constructor() {
		super(), this.menuMode = "offcanvas", this._mainPinned = !1, this._mainHidden = !1, this._lastScrollY = 0, this._scrollFrame = 0, this._mainEl = null, this._onScroll = () => {
			this._scrollFrame ||= requestAnimationFrame(() => {
				this._scrollFrame = 0, this._updateScroll(), this._positionMenu();
			});
		}, this._onFocusIn = () => {
			this._setMainHidden(!1);
		}, this._onFocusOut = (e) => this._handleFocusOut(e), this._onClick = (e) => this._handleClick(e), this._onOffcanvasToggle = (e) => this._handleOffcanvasToggle(e), this._mqlLg = null, this._markupObserver = null, this._resizeObserver = null, this._onMediaChangeLg = () => {
			this._setMainHidden(!1), this._syncMenuMode();
		};
	}
	createRenderRoot() {
		return this;
	}
	connectedCallback() {
		super.connectedCallback(), this._mqlLg = U("lg"), this._mqlLg.addEventListener("change", this._onMediaChangeLg), this._resizeObserver = new ResizeObserver(([e]) => {
			let t = `${e.borderBoxSize?.[0]?.blockSize ?? e.contentRect.height}px`;
			this.style.setProperty("--header-main-height", t), this._mainHeight = t, this._writePageSheet();
		}), G && ++He === 1 && (document.adoptedStyleSheets = [...document.adoptedStyleSheets, G]), this._markupObserver = new MutationObserver(() => this._syncMarkup()), this._markupObserver.observe(this, {
			childList: !0,
			subtree: !0
		}), this._syncMarkup(), this._lastScrollY = window.scrollY, this._updateScroll(), window.addEventListener("scroll", this._onScroll, { passive: !0 }), this.addEventListener("focusin", this._onFocusIn), this.addEventListener("focusout", this._onFocusOut), this.addEventListener("click", this._onClick), this.addEventListener("m-offcanvas:open", this._onOffcanvasToggle), this.addEventListener("m-offcanvas:close", this._onOffcanvasToggle);
	}
	disconnectedCallback() {
		super.disconnectedCallback(), this._mqlLg?.removeEventListener("change", this._onMediaChangeLg), this._markupObserver?.disconnect(), this._resizeObserver?.disconnect(), this._mainEl = null, window.removeEventListener("scroll", this._onScroll), this.removeEventListener("focusin", this._onFocusIn), this.removeEventListener("focusout", this._onFocusOut), this.removeEventListener("click", this._onClick), this.removeEventListener("m-offcanvas:open", this._onOffcanvasToggle), this.removeEventListener("m-offcanvas:close", this._onOffcanvasToggle), cancelAnimationFrame(this._scrollFrame), this._scrollFrame = 0, G && --He === 0 && (document.adoptedStyleSheets = document.adoptedStyleSheets.filter((e) => e !== G));
	}
	willUpdate(e) {
		e.has("menuMode") && !W.includes(this.menuMode) && (console.warn(`[m-header] Invalid menu-mode "${this.menuMode}". Expected one of: ${W.join(", ")}.`), this.menuMode = "offcanvas");
	}
	updated(e) {
		e.has("menuMode") && this._syncMenuMode();
	}
	get _menuOffcanvas() {
		return this.querySelector("m-offcanvas.m-header__offcanvas");
	}
	get _menuIsDropdown() {
		return this.menuMode === "dropdown" && !!this._mqlLg?.matches;
	}
	_syncMarkup() {
		let e = this.querySelector(".m-header__main-bar");
		e !== this._mainEl && (this._mainEl && this._resizeObserver?.unobserve(this._mainEl), this._mainEl = e, e && this._resizeObserver?.observe(e)), this._syncMenuMode();
	}
	_syncMenuMode() {
		let e = this._menuOffcanvas;
		if (!e) return;
		let t = !this._menuIsDropdown;
		e.modal !== t && (e.close?.(), e.modal = t);
	}
	_setMainHidden(e) {
		let t = this._mainHidden !== e;
		this._mainHidden = e, this.toggleAttribute("main-hidden", e), t && this._writePageSheet();
	}
	_writePageSheet() {
		if (!G || !this._mainHeight) return;
		let e = this._mainHidden ? "0px" : this._mainHeight;
		G.replaceSync(`html { scroll-padding-top: ${this._mainHeight}; --m-header-offset: ${e}; }`);
	}
	_updateScroll() {
		let e = this.querySelector(".m-header__main");
		if (!e) return;
		let t = window.scrollY, n = document.documentElement.scrollHeight - window.innerHeight;
		if (t < 0 || t > n) return;
		this._mainPinned = e.getBoundingClientRect().top < 0, this.toggleAttribute("pinned", this._mainPinned);
		let r = t - this._lastScrollY;
		!this._mainPinned || this._mqlLg?.matches || this.matches(":focus-within") ? (this._setMainHidden(!1), this._lastScrollY = t) : Math.abs(r) >= Be && (this._setMainHidden(r > 0), this._lastScrollY = t);
	}
	_positionMenu() {
		let e = this._menuOffcanvas;
		if (!e?.open || !this._menuIsDropdown) return;
		let t = this._triggersFor(e)[0];
		if (!t) return;
		let n = t.getBoundingClientRect(), r = document.documentElement.clientWidth;
		this.style.setProperty("--header-menu-top", `${n.bottom + Ve}px`), this.style.setProperty("--header-menu-end", `${Math.max(0, r - n.right)}px`);
	}
	_triggersFor(e) {
		return e.id ? [...this.querySelectorAll(".m-header__trigger")].filter((t) => t.getAttribute("aria-controls") === e.id) : [];
	}
	_handleClick(e) {
		let t = e.target, n = t.closest?.(".m-header__trigger");
		if (n && this.contains(n)) {
			let e = n.getAttribute("aria-controls"), t = e ? this.querySelector(`m-offcanvas#${CSS.escape(e)}`) : null;
			if (!t) return;
			t.show(), this._positionMenu();
			return;
		}
		let r = t.closest?.(".m-header__close");
		r && this.contains(r) && r.closest("m-offcanvas")?.close();
	}
	_handleFocusOut(e) {
		let t = this._menuOffcanvas;
		if (!t?.open || !this._menuIsDropdown) return;
		let n = e.relatedTarget;
		t.contains(e.target) && (!n || t.contains(n) || t.close());
	}
	_handleOffcanvasToggle(e) {
		let t = e.target;
		if (t.localName !== "m-offcanvas") return;
		let n = e.type === "m-offcanvas:open";
		for (let e of this._triggersFor(t)) e.setAttribute("aria-expanded", String(n));
		if (n) this._positionMenu();
		else for (let e of t.querySelectorAll("m-menu-list")) e.reset?.();
	}
	closeMenus() {
		for (let e of this.querySelectorAll("m-offcanvas, m-dropdown, m-header-action")) e.close?.();
	}
};
customElements.define("m-header", Ue);
//#endregion
//#region ../mde-storybook/src/components/search-block/search-block.scss?inline
var We = ":host{--search-block-height:3rem;--search-block-border-color:var(--mde-color-beau-blue);--search-block-bg:var(--mde-color-white);--search-block-color:var(--mde-color-body);--search-block-submit-bg:var(--mde-color-white);--search-block-submit-color:var(--mde-color-blue);--search-block-submit-border-color:var(--search-block-border-color);--search-block-submit-hover-bg:var(--mde-color-beau-blue-xlight);--search-block-focus-outline-color:var(--mde-color-focus-outline);display:block}.m-search-block{width:100%;display:flex}.m-search-block__input{min-width:0;height:var(--search-block-height);padding:var(--mde-spacing-100) var(--mde-spacing-200);font-family:inherit;font-size:var(--mde-typography-size-paragraph);line-height:var(--mde-typography-line-height-base);color:var(--search-block-color);background-color:var(--search-block-bg);border:1px solid var(--search-block-border-color);appearance:none;border-radius:0;flex:auto}.m-search-block__input:focus{z-index:1;outline:var(--mde-focus-ring-width) solid var(--search-block-focus-outline-color);outline-offset:var(--mde-focus-ring-offset)}.m-search-block__submit{flex:0 0 var(--search-block-height);width:var(--search-block-height);height:var(--search-block-height);color:var(--search-block-submit-color);cursor:pointer;background:var(--search-block-submit-bg);border:1px solid var(--search-block-submit-border-color);justify-content:center;align-items:center;margin-left:-1px;padding:0;display:flex}.m-search-block__submit .icon{fill:currentColor;width:1.5rem;height:1.5rem}.m-search-block__submit:hover{background:var(--search-block-submit-hover-bg)}.m-search-block__submit:focus{z-index:1;outline:var(--mde-focus-ring-width) solid var(--search-block-focus-outline-color);outline-offset:var(--mde-focus-ring-offset)}:host([variant=pill]){--search-block-height:3.25rem;--search-block-border-color:var(--mde-component-input-border-color);--search-block-submit-size:2.5rem;--search-block-submit-inset:var(--mde-spacing-100)}@media (width>=992px){:host([variant=pill]){--search-block-height:3.5rem}}:host([variant=pill]) .m-search-block{position:relative}:host([variant=pill]) .m-search-block__input{padding-inline:var(--mde-spacing-300) calc(var(--search-block-submit-size) + 2 * var(--search-block-submit-inset));font-size:var(--mde-typography-style-paragraph-font-size-mobile);border-radius:var(--mde-border-radius-pill)}:host([variant=pill]) .m-search-block__input::placeholder{color:var(--mde-color-text-muted);opacity:1}:host([variant=pill]) .m-search-block__submit{z-index:2;width:var(--search-block-submit-size);height:var(--search-block-submit-size);color:var(--mde-color-action-text);background:var(--mde-color-action-default);border-radius:var(--mde-border-radius-pill);border:0;flex:none;margin:0;position:absolute;inset-block-start:50%;inset-inline-end:var(--search-block-submit-inset);transform:translateY(-50%)}:host([variant=pill]) .m-search-block__submit .icon{width:1.25rem;height:1.25rem}:host([variant=pill]) .m-search-block__submit:hover{background:var(--mde-color-action-hover)}", Ge = 0, Ke = ["default", "pill"], qe = class extends z {
	static styles = [a(B), a(We)];
	static properties = {
		action: {
			type: String,
			reflect: !0
		},
		method: {
			type: String,
			reflect: !0
		},
		name: {
			type: String,
			reflect: !0
		},
		label: {
			type: String,
			reflect: !0
		},
		placeholder: {
			type: String,
			reflect: !0
		},
		submitLabel: {
			type: String,
			attribute: "submit-label",
			reflect: !0
		},
		variant: {
			type: String,
			reflect: !0
		}
	};
	constructor() {
		super(), this.action = "https://www.muenchen.de/volltextsuche.html", this.method = "get", this.name = "query", this.label = "Suchbegriff eingeben", this.placeholder = "Suchbegriff eingeben", this.submitLabel = "Suche starten", this.variant = "default", this._inputId = `m-search-block-input-${++Ge}`;
	}
	connectedCallback() {
		super.connectedCallback(), this.setAttribute("data-content", "");
	}
	willUpdate(e) {
		e.has("variant") && !Ke.includes(this.variant) && (console.warn(`[m-search-block] Invalid variant "${this.variant}". Expected one of: ${Ke.join(", ")}`), this.variant = "default");
	}
	firstUpdated() {
		H(this);
	}
	_handleSubmit(e) {
		let t = this.shadowRoot.querySelector("input")?.value?.trim() ?? "";
		if (!t) {
			e.preventDefault();
			return;
		}
		this.dispatchEvent(new CustomEvent("search", {
			detail: { query: t },
			bubbles: !0,
			composed: !0
		})), !e.defaultPrevented && (this.action || e.preventDefault());
	}
	render() {
		return A`
      <form
        action="${this.action || M}"
        method="${this.method || "get"}"
        role="search"
        class="m-search-block"
        aria-label="Suche"
        @submit="${this._handleSubmit}"
      >
        <label class="visually-hidden" for="${this._inputId}"
          >${this.label}</label
        >
        <input
          class="m-search-block__input"
          type="text"
          id="${this._inputId}"
          name="${this.name || "query"}"
          placeholder="${this.placeholder ?? M}"
        />
        <button class="m-search-block__submit" type="submit">
          <m-icon name="search" aria-hidden="true"></m-icon>
          <span class="visually-hidden">${this.submitLabel}</span>
        </button>
      </form>
    `;
	}
};
customElements.define("m-search-block", qe);
//#endregion
//#region ../mde-storybook/src/components/bb-banner/bb-banner.scss?inline
var Je = ":host{--bb-banner-background:linear-gradient(to bottom, var(--mde-component-bb-banner-background));--bb-banner-color:var(--mde-component-bb-banner-color);--bb-banner-ring-color:var(--mde-component-bb-banner-ring-color);--bb-banner-focus-color:var(--mde-component-bb-banner-focus-color);display:inline-block}.m-bb-banner{min-width:8.3125rem;height:2.5rem;font-family:var(--mde-typography-font-family-base);color:var(--bb-banner-color);-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;white-space:nowrap;background:var(--bb-banner-background);border-radius:var(--mde-border-radius-pill);flex-direction:column;justify-content:center;padding:0 1.25rem 0 .875rem;font-weight:550;line-height:1;display:flex;position:relative}.m-bb-banner:after{content:\"\";border:1px solid var(--bb-banner-ring-color);border-radius:inherit;pointer-events:none;position:absolute;inset:2px}.m-bb-banner,.m-bb-banner:active,.m-bb-banner:visited{text-decoration:none}.m-bb-banner:hover{color:var(--bb-banner-color);text-underline-offset:.2em;text-decoration:underline;text-decoration-thickness:max(1px,.0625em)}.m-bb-banner:focus-visible{outline:var(--mde-focus-ring-width) solid var(--bb-banner-focus-color);outline-offset:var(--mde-focus-ring-offset)}.m-bb-banner__adtag{letter-spacing:.01em;margin-bottom:.125rem;font-size:.625rem}.m-bb-banner__label{letter-spacing:.03em;font-size:.875rem}@media print{:host{display:none}}", Ye = class extends z {
	static styles = [a(B), a(Je)];
	static properties = {
		href: {
			type: String,
			reflect: !0
		},
		label: {
			type: String,
			reflect: !0
		},
		adtag: {
			type: String,
			reflect: !0
		}
	};
	constructor() {
		super(), this.href = "#", this.label = "Branchenbuch", this.adtag = "Anzeige";
	}
	render() {
		return A`
      <a class="m-bb-banner" href="${this.href || M}">
        <span class="m-bb-banner__adtag">${this.adtag}</span>
        <span class="m-bb-banner__label">${this.label}</span>
      </a>
    `;
	}
};
customElements.define("m-bb-banner", Ye);
//#endregion
//#region ../mde-storybook/src/components/breadcrumb/breadcrumb.js
var Xe = class extends g {
	static properties = { expanded: {
		type: Boolean,
		reflect: !0
	} };
	constructor() {
		super(), this.expanded = !1, this._onClick = (e) => this._handleClick(e);
	}
	createRenderRoot() {
		return this;
	}
	connectedCallback() {
		super.connectedCallback(), this.addEventListener("click", this._onClick);
	}
	disconnectedCallback() {
		super.disconnectedCallback(), this.removeEventListener("click", this._onClick);
	}
	updated(e) {
		super.updated(e), e.has("expanded") && this.querySelector(".m-breadcrumb__toggle")?.setAttribute("aria-expanded", String(this.expanded));
	}
	async _handleClick(e) {
		let t = e.target.closest?.(".m-breadcrumb__toggle");
		!t || !this.contains(t) || (this.expanded = !0, await this.updateComplete, t.closest(".m-breadcrumb__item")?.nextElementSibling?.querySelector("a")?.focus());
	}
};
customElements.define("m-breadcrumb", Xe);
//#endregion
//#region ../mde-storybook/src/components/a11y-actions/a11y-actions.js
var K = "m-a11y-actions", Ze = class extends g {
	constructor() {
		super(), this._onClick = (e) => this._handleClick(e), this._onKeydown = (e) => this._handleKeydown(e), this._onDialogClose = (e) => this._handleDialogClose(e);
	}
	createRenderRoot() {
		return this;
	}
	connectedCallback() {
		super.connectedCallback(), this.addEventListener("click", this._onClick), this.addEventListener("keydown", this._onKeydown), this.addEventListener("close", this._onDialogClose, !0);
	}
	disconnectedCallback() {
		super.disconnectedCallback(), this.removeEventListener("click", this._onClick), this.removeEventListener("keydown", this._onKeydown), this.removeEventListener("close", this._onDialogClose, !0);
	}
	_playerFor(e) {
		let t = e.getAttribute("aria-controls");
		return t && this.querySelector(`#${CSS.escape(t)}`) || e.closest(`.${K}__item`)?.querySelector(`.${K}__player`);
	}
	_toggleFor(e) {
		return e.id && this.querySelector(`[aria-controls="${CSS.escape(e.id)}"]`) || e.closest(`.${K}__item`)?.querySelector(`.${K}__sign-language`);
	}
	openPlayer(e = this.querySelector(`.${K}__player`)) {
		!e || e.open || (e.hasAttribute("tabindex") || (e.tabIndex = -1), e.show(), e.focus(), this._toggleFor(e)?.setAttribute("aria-expanded", "true"), this._dispatchToggle(!0));
	}
	closePlayer(e = this.querySelector(`.${K}__player[open]`)) {
		e?.open && e.close();
	}
	_handleClick(e) {
		let t = e.target, n = t.closest?.(`.${K}__sign-language`);
		if (n) {
			let e = this._playerFor(n);
			e?.open ? this.closePlayer(e) : this.openPlayer(e);
			return;
		}
		let r = t.closest?.(`.${K}__player-close`);
		if (r) {
			this.closePlayer(r.closest(`.${K}__player`));
			return;
		}
		t.closest?.(`.${K}__read-out`) && this.dispatchEvent(new CustomEvent(`${K}:read-out`, {
			bubbles: !0,
			composed: !0
		}));
	}
	_handleKeydown(e) {
		if (e.key !== "Escape") return;
		let t = e.target.closest?.(`.${K}__player`);
		t?.open && (e.preventDefault(), this.closePlayer(t));
	}
	_handleDialogClose(e) {
		let t = e.target;
		if (!t.classList?.contains(`${K}__player`)) return;
		this._pauseVideo(t);
		let n = this._toggleFor(t);
		n?.setAttribute("aria-expanded", "false");
		let r = document.activeElement;
		(!r || r === document.body || t.contains(r)) && n?.focus(), this._dispatchToggle(!1);
	}
	_dispatchToggle(e) {
		this.dispatchEvent(new CustomEvent(`${K}:sign-language-toggle`, {
			bubbles: !0,
			composed: !0,
			detail: { open: e }
		}));
	}
	_pauseVideo(e) {
		e.querySelectorAll("video").forEach((e) => e.pause());
		for (let t of e.querySelectorAll("iframe")) /youtube(-nocookie)?\.com\//.test(t.src) && t.contentWindow?.postMessage(JSON.stringify({
			event: "command",
			func: "pauseVideo",
			args: []
		}), new URL(t.src).origin);
	}
};
customElements.define("m-a11y-actions", Ze);
//#endregion
//#region ../mde-storybook/src/components/footer/footer.js
var q = "m-footer", Qe = "(max-width: 1199.98px)", $e = 0, et = class extends g {
	constructor() {
		super(), this._uid = ++$e, this._mql = null, this._onClick = (e) => this._handleClick(e), this._onMediaChange = () => this._syncAccordion();
	}
	createRenderRoot() {
		return this;
	}
	connectedCallback() {
		super.connectedCallback(), this.addEventListener("click", this._onClick), this._mql = window.matchMedia(Qe), this._mql.addEventListener("change", this._onMediaChange), this._syncAccordion();
	}
	disconnectedCallback() {
		super.disconnectedCallback(), this.removeEventListener("click", this._onClick), this._mql?.removeEventListener("change", this._onMediaChange), this._mql = null;
	}
	firstUpdated() {
		this._syncAccordion();
	}
	get _sections() {
		return [...this.querySelectorAll(`.${q}__section`)];
	}
	_syncAccordion() {
		let e = !!this._mql?.matches;
		this._sections.forEach((t, n) => {
			let r = t.querySelector(`.${q}__heading`), i = t.querySelector(`.${q}__links`);
			!r || !i || (e ? this._collapse(r, i, n) : this._expand(r, i));
		});
	}
	_collapse(e, t, n) {
		if (e.querySelector(`.${q}__toggle`)) return;
		t.id ||= `${q}-links-${this._uid}-${n + 1}`;
		let r = document.createElement("button");
		r.type = "button", r.className = `${q}__toggle`, r.setAttribute("aria-controls", t.id);
		let i = document.createElement("span");
		i.append(...e.childNodes);
		let a = document.createElement("m-icon");
		a.setAttribute("aria-hidden", "true"), r.append(i, a), e.append(r), this._setOpen(r, t, !1);
	}
	_expand(e, t) {
		let n = e.querySelector(`.${q}__toggle`);
		n && e.replaceChildren(...n.firstElementChild.childNodes), t.hidden = !1;
	}
	_setOpen(e, t, n) {
		e.setAttribute("aria-expanded", String(n)), e.querySelector("m-icon")?.setAttribute("name", n ? "minus" : "plus"), t.hidden = !n;
	}
	_handleClick(e) {
		let t = e.target.closest?.(`.${q}__toggle`);
		if (!t) return;
		let n = this.querySelector(`#${CSS.escape(t.getAttribute("aria-controls"))}`);
		n && this._setOpen(t, n, t.getAttribute("aria-expanded") !== "true");
	}
};
customElements.define("m-footer", et);
//#endregion
//#region ../mde-storybook/src/components/button/button.scss?inline
var tt = ":host{--button-primary-bg:var(--mde-color-action-default);--button-primary-text:var(--mde-color-action-text);--button-primary-border:var(--mde-component-button-primary-border-color);--button-primary-hover-bg:var(--mde-color-action-hover);--button-primary-hover-text:var(--mde-color-action-text);--button-primary-hover-border:var(--mde-component-button-primary-hover-border-color);--button-secondary-bg:var(--mde-color-white);--button-secondary-text:var(--mde-color-accent-default);--button-secondary-border:var(--mde-color-accent-default);--button-secondary-hover-bg:var(--mde-component-button-secondary-hover-background);--button-secondary-hover-text:var(--mde-component-button-secondary-hover-color);--button-secondary-hover-border:var(--mde-component-button-secondary-hover-border-color);--button-tertiary-bg:transparent;--button-tertiary-text:var(--mde-color-accent-default);--button-tertiary-border:transparent;--button-tertiary-hover-bg:transparent;--button-tertiary-hover-text:var(--mde-color-accent-hover);--button-tertiary-hover-border:transparent;--button-danger-bg:var(--mde-color-white);--button-danger-text:var(--mde-color-error);--button-danger-border:var(--mde-color-error);--button-danger-hover-bg:var(--mde-color-white);--button-danger-hover-text:var(--mde-color-error-dark);--button-danger-hover-border:var(--mde-color-error-dark);--button-disabled-bg:var(--mde-color-disabled-background);--button-disabled-text:var(--mde-color-disabled-text);--button-disabled-border:var(--mde-color-disabled-background)}:host([block]){display:block}.m-button{font-family:var(--mde-typography-font-family-base);cursor:pointer;border:1px solid var(--button-primary-border);border-radius:var(--mde-component-button-border-radius);background-color:var(--button-primary-bg);color:var(--button-primary-text);align-items:center;padding:.6875rem .875rem;font-size:1rem;font-weight:400;line-height:1.5;text-decoration:none;transition:background-color .35s,border-color .35s,color .35s;display:inline-flex}@media (prefers-reduced-motion:reduce){.m-button{transition:none}}@media (width>=1200px){.m-button{padding:.594rem .875rem;font-size:1.125rem}}.m-button:hover:where(:not(:disabled)){background-color:var(--button-primary-hover-bg);color:var(--button-primary-hover-text);border-color:var(--button-primary-hover-border)}.m-button:focus{z-index:1;outline:var(--mde-focus-ring-width) solid var(--mde-color-focus-outline);outline-offset:var(--mde-focus-ring-offset);box-shadow:0 0 0 .25rem #fff}.m-button:focus:not(:focus-visible){outline:0}.m-button:focus-visible{z-index:1;outline:var(--mde-focus-ring-width) solid var(--mde-color-focus-outline);outline-offset:var(--mde-focus-ring-offset);box-shadow:0 0 0 .25rem #fff}.m-button:disabled,.m-button--disabled{background-color:var(--button-disabled-bg);color:var(--button-disabled-text);border-color:var(--button-disabled-border);pointer-events:none}.m-button--block{justify-content:center;width:100%}.m-button__icon{fill:currentColor;flex-shrink:0;width:1.5rem;height:1.5rem}.m-button__icon--before{margin-left:0;margin-right:.75rem}.m-button__icon--after{margin-left:.75rem;margin-right:0}.m-button--secondary{border-color:var(--button-secondary-border);background-color:var(--button-secondary-bg);color:var(--button-secondary-text)}.m-button--secondary:hover:where(:not(:disabled)){background-color:var(--button-secondary-hover-bg);color:var(--button-secondary-hover-text);border-color:var(--button-secondary-hover-border);text-decoration:underline}.m-button--danger{border-color:var(--button-danger-border);background-color:var(--button-danger-bg);color:var(--button-danger-text)}.m-button--danger:hover:where(:not(:disabled)){background-color:var(--button-danger-hover-bg);color:var(--button-danger-hover-text);border-color:var(--button-danger-hover-border);text-decoration:underline}.m-button--tertiary,.m-button--ghost{border-color:var(--button-tertiary-border);background-color:var(--button-tertiary-bg);color:var(--button-tertiary-text);padding-left:0}.m-button--tertiary:hover:where(:not(:disabled)),.m-button--tertiary:focus:where(:not(:disabled)),.m-button--ghost:hover:where(:not(:disabled)),.m-button--ghost:focus:where(:not(:disabled)){background-color:var(--button-tertiary-hover-bg);color:var(--button-tertiary-hover-text);border-color:var(--button-tertiary-hover-border);text-decoration:underline}.m-button--icon-only{padding:.6875rem}@media (width>=1200px){.m-button--icon-only{padding:.594rem}}.m-button--small{padding:var(--mde-component-button-sm-padding-y) var(--mde-component-button-sm-padding-x);font-size:var(--mde-component-button-sm-font-size)}.m-button--small .m-button__icon{width:1.25rem;height:1.25rem}.m-button--small .m-button__icon--before{margin-right:var(--mde-spacing-050)}.m-button--small .m-button__icon--after{margin-left:var(--mde-spacing-050)}.m-button--small.m-button--icon-only{padding:var(--mde-component-button-sm-padding-y)}@media (width>=1200px){.m-button--small.m-button--icon-only{padding:var(--mde-component-button-sm-padding-y)}}@media (width<=575.98px){.m-button--align-center-sm{text-align:center;justify-content:center}}.m-button-group{flex-wrap:wrap;column-gap:1rem;margin:0;padding:0;list-style-type:none;display:flex}@media (width>=1200px){.m-button-group{column-gap:1.5rem}}.m-button-group .m-button{margin-bottom:1rem}@media (width>=1200px){.m-button-group .m-button{margin-bottom:1.5rem}}@media print{.m-button,.m-button:after{display:block}}", J = {
	de: {
		button: {
			newWindow: "(öffnet in neuem Fenster)",
			externalLink: "(externer Link)"
		},
		dialog: { close: "Schließen" },
		form: {
			optional: "– optional",
			required: "(erforderlich)",
			error: "Fehler:",
			characterCount: "{current}/{max}",
			characterCountExceeded: "{current}/{max} Zeichen (überschritten)",
			showMore: "Mehr anzeigen",
			showLess: "Weniger anzeigen",
			openDropdown: "Auswahlliste öffnen",
			searchPlaceholder: "Suchen",
			noResults: "Keine Ergebnisse gefunden",
			noChoices: "Keine Auswahlmöglichkeiten verfügbar",
			loading: "Lädt...",
			decrement: "Wert verringern",
			increment: "Wert erhöhen"
		},
		combobox: {
			toggle: "Auswahlliste öffnen",
			clear: "Auswahl löschen",
			resultsCount: "{count} Optionen verfügbar"
		},
		errorList: { heading: "Es ist ein Problem aufgetreten." }
	},
	en: {
		button: {
			newWindow: "(opens in new window)",
			externalLink: "(external link)"
		},
		dialog: { close: "Close" },
		form: {
			optional: "– optional",
			required: "(required)",
			error: "Error:",
			characterCount: "{current}/{max}",
			characterCountExceeded: "{current}/{max} characters (exceeded)",
			showMore: "Show more",
			showLess: "Show less",
			openDropdown: "Open dropdown list",
			searchPlaceholder: "search",
			noResults: "No results found",
			noChoices: "No choices to choose from",
			loading: "Loading...",
			decrement: "Decrease value",
			increment: "Increase value"
		},
		combobox: {
			toggle: "Open options",
			clear: "Clear selection",
			resultsCount: "{count} options available"
		},
		errorList: { heading: "A problem has occurred." }
	},
	it: {
		button: {
			newWindow: "(si apre in una nuova finestra)",
			externalLink: "(link esterno)"
		},
		dialog: { close: "Chiudi" },
		form: {
			optional: "– opzionale",
			required: "(obbligatorio)",
			error: "Errore:",
			characterCount: "{current}/{max}",
			characterCountExceeded: "{current}/{max} caratteri (superato)",
			showMore: "Mostra di più",
			showLess: "Mostra di meno",
			openDropdown: "Apri elenco a discesa",
			searchPlaceholder: "cerca",
			noResults: "Nessun risultato trovato",
			noChoices: "Nessuna scelta disponibile",
			loading: "Caricamento...",
			decrement: "Diminuisci valore",
			increment: "Aumenta valore"
		},
		combobox: {
			toggle: "Apri opzioni",
			clear: "Cancella selezione",
			resultsCount: "{count} opzioni disponibili"
		},
		errorList: { heading: "Si è verificato un problema." }
	},
	ar: {
		button: {
			newWindow: "(يفتح في نافذة جديدة)",
			externalLink: "(رابط خارجي)"
		},
		dialog: { close: "إغلاق" },
		form: {
			optional: "– اختياري",
			required: "(مطلوب)",
			error: "خطأ:",
			characterCount: "{current}/{max}",
			characterCountExceeded: "{current}/{max} حرف (تجاوز)",
			showMore: "عرض المزيد",
			showLess: "عرض أقل",
			openDropdown: "افتح القائمة المنسدلة",
			searchPlaceholder: "بحث",
			noResults: "لم يتم العثور على نتائج",
			noChoices: "لا توجد خيارات متاحة",
			loading: "جاري التحميل...",
			decrement: "إنقاص القيمة",
			increment: "زيادة القيمة"
		},
		combobox: {
			toggle: "افتح الخيارات",
			clear: "مسح الاختيار",
			resultsCount: "{count} خيارات متاحة"
		},
		errorList: { heading: "حدثت مشكلة." }
	}
}, Y = null;
function nt() {
	Y === null && (Y = (typeof document < "u" ? document.documentElement.lang : null) || "de");
}
function rt(e, t) {
	nt();
	let n = (J[Y] ?? J.de)[e] ?? J.de[e];
	return n?.[t] ? n[t] : (console.warn(`[i18n] Translation missing: ${Y}.${e}.${t}`), "");
}
function it() {
	return nt(), Y;
}
//#endregion
//#region ../mde-storybook/src/utils/i18n-mixin.js
var at = (e) => class extends e {
	connectedCallback() {
		super.connectedCallback(), this._localeChangeHandler = () => this.requestUpdate(), typeof document < "u" && document.addEventListener("mde-locale-change", this._localeChangeHandler);
	}
	disconnectedCallback() {
		super.disconnectedCallback(), typeof document < "u" && document.removeEventListener("mde-locale-change", this._localeChangeHandler);
	}
	t(e, t) {
		return rt(e, t);
	}
	get locale() {
		return it();
	}
}, ot = class e extends at(z) {
	static styles = [a(B), a(tt)];
	static SIZES = ["default", "small"];
	static properties = {
		variant: {
			type: String,
			reflect: !0
		},
		size: {
			type: String,
			reflect: !0
		},
		iconBefore: {
			type: String,
			attribute: "icon-before"
		},
		iconAfter: {
			type: String,
			attribute: "icon-after"
		},
		iconOnly: {
			type: String,
			attribute: "icon-only"
		},
		animatedIcon: {
			type: Boolean,
			attribute: "animated-icon"
		},
		block: {
			type: Boolean,
			reflect: !0
		},
		disabled: {
			type: Boolean,
			reflect: !0
		},
		href: { type: String },
		target: { type: String },
		rel: { type: String },
		type: { type: String },
		expanded: {
			type: Boolean,
			reflect: !0
		},
		controls: { type: String },
		haspopup: { type: String }
	};
	constructor() {
		super(), this.variant = "primary", this.size = "default", this.iconBefore = "", this.iconAfter = "", this.iconOnly = "", this.animatedIcon = !1, this.block = !1, this.disabled = !1, this.href = "", this.target = "", this.rel = "", this.type = "button", this.expanded = !1, this.controls = "", this.haspopup = "";
	}
	willUpdate(t) {
		t.has("size") && !e.SIZES.includes(this.size) && console.warn(`[m-button] Invalid size "${this.size}". Expected one of: ${e.SIZES.join(", ")}`);
	}
	firstUpdated() {
		H(this);
	}
	get _classes() {
		return [
			"m-button",
			`m-button--${this.variant}`,
			this.size === "small" ? "m-button--small" : "",
			this.block ? "m-button--block" : "",
			this.iconOnly ? "m-button--icon-only" : "",
			this.animatedIcon && this.iconAfter ? "m-button--animated-right" : "",
			this.animatedIcon && this.iconBefore ? "m-button--animated-left" : ""
		].filter(Boolean).join(" ");
	}
	get _rel() {
		return this.rel ? this.rel : this.target === "_blank" ? "noopener noreferrer" : M;
	}
	get _newWindowHint() {
		return this.target === "_blank" ? A`
      <span class="visually-hidden"> ${this.t("button", "newWindow")} </span>
    ` : M;
	}
	_renderIcon(e, t) {
		return A`
      <svg
        part="icon"
        aria-hidden="true"
        class="m-button__icon m-button__icon--${t}"
      >
        <use href="#m-icon-${e}"></use>
      </svg>
    `;
	}
	get _content() {
		return this.iconOnly ? A`
        ${this._renderIcon(this.iconOnly, "only")}
        <span class="visually-hidden">
          <slot></slot>
          ${this._newWindowHint}
        </span>
      ` : A`
      ${this.iconBefore ? this._renderIcon(this.iconBefore, "before") : M}
      <span>
        <slot></slot>
        ${this._newWindowHint}
      </span>
      ${this.iconAfter ? this._renderIcon(this.iconAfter, "after") : M}
    `;
	}
	get _ariaExpanded() {
		return this.controls || this.haspopup ? String(this.expanded) : M;
	}
	render() {
		return this.href ? A`
        <a
          part="button"
          class="${this._classes}"
          href="${this.href}"
          target="${this.target || M}"
          rel="${this._rel}"
          aria-expanded="${this._ariaExpanded}"
          aria-controls="${this.controls || M}"
          aria-haspopup="${this.haspopup || M}"
        >
          ${this._content}
        </a>
      ` : A`
      <button
        part="button"
        class="${this._classes}"
        type="${this.type}"
        ?disabled="${this.disabled}"
        aria-expanded="${this._ariaExpanded}"
        aria-controls="${this.controls || M}"
        aria-haspopup="${this.haspopup || M}"
      >
        ${this._content}
      </button>
    `;
	}
};
customElements.define("m-button", ot);
//#endregion
//#region ../mde-storybook/src/components/intro-carousel/intro-carousel.scss?inline
var st = ":host{display:block}.icon{width:var(--m-icon-size,1.5rem);height:var(--m-icon-size,1.5rem)}.m-intro-carousel__layout{flex-direction:column;display:flex}@media (width>=992px){.m-intro-carousel__layout{gap:var(--mde-component-stage-gap-lg)}.m-intro-carousel__layout--actions{gap:var(--mde-component-stage-gap-actions-lg)}}.m-intro-carousel__intro{gap:var(--mde-component-stage-heading-gap);padding:var(--mde-component-stage-heading-padding-block) var(--mde-spacing-200);flex-direction:column;display:flex}@media (width>=992px){.m-intro-carousel__intro{padding:0}}.m-intro-carousel__intro[hidden]{display:none}.m-intro-carousel__region{flex-direction:column;display:flex}@media (width>=992px){.m-intro-carousel__region{gap:var(--mde-component-stage-controls-gap-lg)}}.m-intro-carousel__viewport{position:relative;container-type:inline-size}.m-intro-carousel__nav{top:calc(100cqw / var(--intro-carousel-aspect-ratio) / 2);z-index:2;width:var(--mde-component-stage-nav-size);height:var(--mde-component-stage-nav-size);color:var(--mde-component-stage-nav-color);cursor:pointer;pointer-events:none;background:var(--mde-component-stage-nav-background);border:var(--mde-component-stage-nav-border-width) solid var(--mde-component-stage-nav-border-color);border-radius:var(--mde-border-radius-pill);opacity:0;--m-icon-size:var(--mde-component-stage-nav-icon-size);justify-content:center;align-items:center;padding:0;transition:opacity .15s ease-out;display:flex;position:absolute;transform:translateY(-50%)}@media (prefers-reduced-motion:reduce){.m-intro-carousel__nav{transition:none}}.m-intro-carousel__nav:focus-visible{outline:var(--mde-focus-ring-width) solid var(--mde-color-focus-outline);outline-offset:var(--mde-focus-ring-offset)}.m-intro-carousel__nav:is(:lang(ae),:lang(ar),:lang(arc),:lang(bcc),:lang(bqi),:lang(ckb),:lang(dv),:lang(fa),:lang(glk),:lang(he),:lang(ku),:lang(mzn),:lang(nqo),:lang(pnb),:lang(ps),:lang(sd),:lang(ug),:lang(ur),:lang(yi)) m-icon{transform:scaleX(-1)}.m-intro-carousel__nav--previous{inset-inline-start:var(--mde-spacing-150)}@media (width>=992px){.m-intro-carousel__nav--previous{inset-inline-start:calc(var(--mde-component-stage-nav-size) / -2)}}.m-intro-carousel__nav--next{inset-inline-end:var(--mde-spacing-150)}@media (width>=992px){.m-intro-carousel__nav--next{inset-inline-end:calc(var(--mde-component-stage-nav-size) / -2)}}.m-intro-carousel__viewport:hover .m-intro-carousel__nav,.m-intro-carousel__nav:focus-visible{pointer-events:auto;opacity:1}.m-intro-carousel__controls{gap:var(--mde-component-stage-controls-item-gap);padding-block:var(--mde-component-stage-controls-padding-block);justify-content:center;align-items:center;display:flex}@media (width>=992px){.m-intro-carousel__controls{padding-block:0}}.m-intro-carousel__rotation{width:var(--mde-component-stage-pause-size);height:var(--mde-component-stage-pause-size);color:var(--mde-component-stage-indicator-color);cursor:pointer;border:var(--mde-component-stage-pause-border-width) solid currentColor;border-radius:var(--mde-border-radius-pill);--m-icon-size:var(--mde-component-stage-pause-icon-size);background:0 0;justify-content:center;align-items:center;padding:0;display:flex}.m-intro-carousel__rotation:focus-visible{outline:var(--mde-focus-ring-width) solid var(--mde-color-focus-outline);outline-offset:var(--mde-focus-ring-offset)}.m-intro-carousel__indicators{align-items:center;display:flex}.m-intro-carousel__indicator{min-width:var(--mde-component-stage-indicator-target);height:var(--mde-component-stage-indicator-target);cursor:pointer;background:0 0;border:0;justify-content:center;align-items:center;padding:0;display:flex}.m-intro-carousel__indicator:focus-visible{outline:var(--mde-focus-ring-width) solid var(--mde-color-focus-outline);outline-offset:0;border-radius:var(--mde-border-radius-pill)}.m-intro-carousel__indicator[aria-current=true]{padding-inline:calc((var(--mde-component-stage-indicator-target) - var(--mde-component-stage-indicator-size)) / 2)}.m-intro-carousel__dot{width:var(--mde-component-stage-indicator-size);height:var(--mde-component-stage-indicator-size);background:var(--mde-component-stage-indicator-color);border-radius:var(--mde-border-radius-pill);display:block;overflow:hidden}.m-intro-carousel__indicator[aria-current=true] .m-intro-carousel__dot{width:var(--mde-component-stage-indicator-active-width);background:var(--mde-component-stage-indicator-track-color)}.m-intro-carousel__indicator:hover:not([aria-current=true]) .m-intro-carousel__dot{transform:scale(1.25)}.m-intro-carousel__fill{background:var(--mde-component-stage-indicator-color);border-radius:var(--mde-border-radius-pill);transform-origin:0;width:100%;height:100%;display:block;transform:scaleX(0)}.m-intro-carousel__fill:is(:lang(ae),:lang(ar),:lang(arc),:lang(bcc),:lang(bqi),:lang(ckb),:lang(dv),:lang(fa),:lang(glk),:lang(he),:lang(ku),:lang(mzn),:lang(nqo),:lang(pnb),:lang(ps),:lang(sd),:lang(ug),:lang(ur),:lang(yi)){transform-origin:100%}.m-intro-carousel__fill--full{transform:none}@media print{.m-intro-carousel__nav,.m-intro-carousel__controls{display:none}}", X = "m-intro-carousel", ct = class extends z {
	static styles = [a(B), a(st)];
	static properties = {
		autoplay: {
			type: Boolean,
			reflect: !0
		},
		interval: { type: Number },
		paused: {
			type: Boolean,
			reflect: !0
		},
		label: { type: String },
		previousLabel: {
			type: String,
			attribute: "previous-label"
		},
		nextLabel: {
			type: String,
			attribute: "next-label"
		},
		pauseLabel: {
			type: String,
			attribute: "pause-label"
		},
		playLabel: {
			type: String,
			attribute: "play-label"
		},
		slideLabel: {
			type: String,
			attribute: "slide-label"
		},
		indicatorLabel: {
			type: String,
			attribute: "indicator-label"
		},
		_index: { state: !0 },
		_hovered: { state: !0 },
		_titles: { state: !0 },
		_hasActions: { state: !0 },
		_hasIntro: { state: !0 }
	};
	constructor() {
		super(), this.autoplay = !1, this.interval = 7e3, this.paused = !1, this.label = "Aktuelle Themen", this.previousLabel = "Vorherige Folie", this.nextLabel = "Nächste Folie", this.pauseLabel = "Automatische Wiedergabe anhalten", this.playLabel = "Automatische Wiedergabe starten", this.slideLabel = "{n} von {total}", this.indicatorLabel = "Folie {n}: {title}", this._index = 0, this._hovered = !1, this._titles = [], this._hasActions = !1, this._hasIntro = !1, this._progress = null, this._progressIndex = -1, this._scrollFrame = 0, this._scrollTarget = -1, this._scrollTimer = 0, this._onScroll = (e) => {
			e.target === this._track && (this._scrollFrame ||= requestAnimationFrame(() => {
				this._scrollFrame = 0, this._syncIndexFromScroll();
			}));
		};
	}
	connectedCallback() {
		super.connectedCallback(), window.matchMedia("(prefers-reduced-motion: reduce)").matches && (this.paused = !0), this.addEventListener("scroll", this._onScroll, !0);
	}
	disconnectedCallback() {
		super.disconnectedCallback(), this.removeEventListener("scroll", this._onScroll, !0), cancelAnimationFrame(this._scrollFrame), clearTimeout(this._scrollTimer), this._progress?.cancel(), this._progress = null;
	}
	firstUpdated() {
		H(this);
	}
	updated(e) {
		e.has("interval") && (this._progress?.cancel(), this._progress = null);
		let t = this._slides.length;
		this._slides.forEach((e, n) => {
			e.setAttribute("role", "group"), e.setAttribute("aria-roledescription", "slide"), e.setAttribute("aria-label", this._format(this.slideLabel, {
				n: n + 1,
				total: t
			})), e.inert = n !== this._index;
		}), this._track?.setAttribute("aria-live", this._running ? "off" : "polite"), this._syncProgress();
	}
	get _track() {
		return this.querySelector(`:scope > .${X}__track`);
	}
	get _slides() {
		return [...this._track?.children ?? []];
	}
	get _hasAutoplay() {
		return this.autoplay && this._titles.length > 1;
	}
	get _running() {
		return this._hasAutoplay && !this.paused && !this._hovered;
	}
	goTo(e) {
		let t = this._slides.length;
		if (!t) return;
		let n = (e % t + t) % t;
		this._setIndex(n);
		let r = this._track;
		if (r?.clientWidth) {
			let e = (getComputedStyle(r).direction === "rtl" ? -1 : 1) * n * r.clientWidth;
			if (Math.abs(r.scrollLeft - e) < 1) return;
			this._scrollTarget = n, clearTimeout(this._scrollTimer), this._scrollTimer = setTimeout(() => {
				this._scrollTarget = -1, this._syncIndexFromScroll();
			}, 1e3);
			let t = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
			r.scrollTo({
				left: e,
				behavior: t ? "auto" : "smooth"
			});
		}
	}
	next() {
		this.goTo(this._index + 1);
	}
	previous() {
		this.goTo(this._index - 1);
	}
	_setIndex(e) {
		e !== this._index && (this._index = e, this.dispatchEvent(new CustomEvent(`${X}:change`, {
			bubbles: !0,
			composed: !0,
			detail: { index: e }
		})));
	}
	_syncIndexFromScroll() {
		let e = this._track;
		if (!e?.clientWidth) return;
		let t = Math.round(Math.abs(e.scrollLeft) / e.clientWidth);
		if (this._scrollTarget !== -1) {
			t === this._scrollTarget && (this._scrollTarget = -1);
			return;
		}
		this._setIndex(Math.min(Math.max(t, 0), this._slides.length - 1));
	}
	_onSlotChange() {
		this._titles = this._slides.map((e) => e.querySelector("h2, h3, h4")?.textContent.trim() ?? ""), this._hasActions = !!this.querySelector(":scope > m-a11y-actions[slot=\"intro\"]"), this._hasIntro = !!this.querySelector(":scope > [slot=\"intro\"]"), this._index >= this._titles.length && (this._index = 0);
	}
	_syncProgress() {
		let e = this.renderRoot.querySelector(`.${X}__indicator[aria-current] .${X}__fill`);
		if (!this._hasAutoplay || !e) {
			this._progress?.cancel(), this._progress = null;
			return;
		}
		(!this._progress || this._progressIndex !== this._index || this._progress.effect?.target !== e) && (this._progress?.cancel(), this._progress = e.animate([{ transform: "scaleX(0)" }, { transform: "scaleX(1)" }], {
			duration: this.interval,
			fill: "forwards"
		}), this._progressIndex = this._index, this._progress.onfinish = () => this.next()), this._running ? this._progress.play() : this._progress.pause();
	}
	_onFocusIn(e) {
		e.composedPath().some((e) => e.classList?.contains(`${X}__rotation`)) || (this.paused = !0);
	}
	_format(e, t) {
		return e.replace(/\{(\w+)\}/g, (e, n) => t[n] ?? "");
	}
	_renderNav() {
		return A`
      <button
        type="button"
        class="${X}__nav ${X}__nav--previous"
        @click=${this.previous}
      >
        <m-icon name="chevron-left" aria-hidden="true"></m-icon>
        <span class="visually-hidden">${this.previousLabel}</span>
      </button>
      <button
        type="button"
        class="${X}__nav ${X}__nav--next"
        @click=${this.next}
      >
        <m-icon name="chevron-right" aria-hidden="true"></m-icon>
        <span class="visually-hidden">${this.nextLabel}</span>
      </button>
    `;
	}
	_renderControls() {
		return A`
      <div class="${X}__controls">
        ${this._hasAutoplay ? A`<button
              type="button"
              class="${X}__rotation"
              @click=${() => this.paused = !this.paused}
            >
              <m-icon
                name=${this.paused ? "play" : "pause"}
                aria-hidden="true"
              ></m-icon>
              <span class="visually-hidden"
                >${this.paused ? this.playLabel : this.pauseLabel}</span
              >
            </button>` : M}
        <div class="${X}__indicators">
          ${this._titles.map((e, t) => {
			let n = t === this._index;
			return A`<button
              type="button"
              class="${X}__indicator"
              aria-current=${n ? "true" : M}
              @click=${() => this.goTo(t)}
            >
              <span class="${X}__dot" aria-hidden="true"
                >${n ? A`<span
                      class="${X}__fill ${this._hasAutoplay ? "" : `${X}__fill--full`}"
                    ></span>` : M}</span
              >
              <span class="visually-hidden"
                >${this._format(this.indicatorLabel, {
				n: t + 1,
				title: e
			})}</span
              >
            </button>`;
		})}
        </div>
      </div>
    `;
	}
	render() {
		let e = this._titles.length > 1;
		return A`
      <div
        class="${X}__layout ${this._hasActions ? `${X}__layout--actions` : ""}"
      >
        <div class="${X}__intro" ?hidden=${!this._hasIntro}>
          <slot name="intro" @slotchange=${this._onSlotChange}></slot>
        </div>
        <div
          class="${X}__region"
          role="region"
          aria-roledescription="carousel"
          aria-label=${this.label}
          @pointerenter=${() => this._hovered = !0}
          @pointerleave=${() => this._hovered = !1}
          @focusin=${this._onFocusIn}
        >
          <div class="${X}__viewport">
            <slot @slotchange=${this._onSlotChange}></slot>
            ${e ? this._renderNav() : M}
          </div>
          ${e ? this._renderControls() : M}
        </div>
      </div>
    `;
	}
};
customElements.define("m-intro-carousel", ct);
//#endregion
//#region ../mde-storybook/src/utils/choice-group-element.js
var lt = 0;
function ut(e, t) {
	return e.id ||= `${t}-${++lt}`, e.id;
}
var dt = class extends at(g) {
	static group = "";
	static list = "";
	static invalidInputs = !1;
	constructor() {
		super(), this._markedInvalid = /* @__PURE__ */ new WeakSet(), this._queued = !1, this._observer = new MutationObserver(() => this._queueSync());
	}
	createRenderRoot() {
		return this;
	}
	connectedCallback() {
		super.connectedCallback(), this._observer.observe(this, {
			subtree: !0,
			childList: !0,
			attributes: !0,
			attributeFilter: ["class", "data-indeterminate"]
		}), this._queueSync();
	}
	disconnectedCallback() {
		super.disconnectedCallback(), this._observer.disconnect();
	}
	updated() {
		this._sync();
	}
	get _fieldsets() {
		return [...this.querySelectorAll(`fieldset.${this.constructor.group}`)];
	}
	_queueSync() {
		this._queued || (this._queued = !0, queueMicrotask(() => {
			this._queued = !1, this._sync();
		}));
	}
	_sync() {
		let { list: e, invalidInputs: t } = this.constructor, n = this.getRootNode();
		for (let r of this._fieldsets) {
			let i = r.querySelector(":scope > .m-hint"), a = r.querySelector(":scope > .m-error-message"), o = [i && ut(i, `${e}-hint`), a && ut(a, `${e}-error`)].filter(Boolean), s = (r.getAttribute("aria-describedby") ?? "").split(/\s+/).filter((e) => e && !o.includes(e) && n.getElementById?.(e)), c = [...o, ...s].join(" ");
			c ? r.getAttribute("aria-describedby") !== c && r.setAttribute("aria-describedby", c) : r.removeAttribute("aria-describedby");
			let l = !!r.closest(".m-form-group")?.classList.contains("has-error");
			if (t) for (let t of r.querySelectorAll(`.${e}__input`)) l && !t.hasAttribute("aria-invalid") ? (t.setAttribute("aria-invalid", "true"), this._markedInvalid.add(t)) : !l && this._markedInvalid.has(t) && (t.removeAttribute("aria-invalid"), this._markedInvalid.delete(t));
		}
	}
}, ft = 0, pt = class extends dt {
	static group = "m-checkbox-group";
	static list = "m-checkboxes";
	static invalidInputs = !0;
	constructor() {
		super(), this._wired = /* @__PURE__ */ new WeakSet();
	}
	_sync() {
		super._sync();
		for (let e of this.querySelectorAll(".m-checkboxes__input[data-indeterminate]")) e.dataset.indeterminate !== "done" && (e.indeterminate = !0, e.dataset.indeterminate = "done");
		for (let e of this.querySelectorAll(".m-checkboxes__collapse__trigger")) this._syncCollapse(e);
	}
	_syncCollapse(e) {
		let t = e.closest(".m-checkboxes")?.querySelector(".m-checkboxes__collapse__container");
		if (t && !(e.dataset.bsToggle && window.bootstrap?.Collapse)) if (t.id ||= `m-checkboxes-more-${++ft}`, e.getAttribute("aria-controls") !== t.id && e.setAttribute("aria-controls", t.id), this._wired.has(e)) this._setOpen(e, t, e.getAttribute("aria-expanded") === "true");
		else {
			this._wired.add(e);
			let n = !!t.querySelector("input:checked");
			this._setOpen(e, t, n), e.addEventListener("click", () => this._setOpen(e, t, e.getAttribute("aria-expanded") !== "true"));
		}
	}
	_setOpen(e, t, n) {
		t.classList.toggle("show", n), e.setAttribute("aria-expanded", String(n));
		let r = e.querySelector("span"), i = this.t("form", n ? "showLess" : "showMore");
		r && r.textContent !== i && (r.textContent = i);
	}
};
customElements.define("m-checkbox", pt);
//#endregion
//#region ../mde-storybook/src/components/radio-group/radio-group.js
var mt = class extends dt {
	static group = "m-radio-group";
	static list = "m-radios";
	static invalidInputs = !1;
};
customElements.define("m-radio-group", mt);
//#endregion
//#region ../mde-storybook/src/components/filter/filter.js
var ht = ["client", "submit"], gt = 0;
function _t(e, t) {
	let { label: n, labelOne: r, labelNone: i, labelSr: a } = e.dataset;
	if (!n || t == null) return;
	let o = n;
	t === 0 && i !== void 0 ? o = i : t === 1 && r !== void 0 && (o = r);
	let s = e.querySelector("[data-label-target]") ?? e;
	s.textContent = o.replace("{count}", String(t)), a !== void 0 && (t > 0 ? e.setAttribute("aria-label", a.replace("{count}", String(t))) : e.removeAttribute("aria-label"));
}
var vt = class extends g {
	static properties = {
		mode: { type: String },
		count: { type: Number },
		open: {
			type: Boolean,
			reflect: !0
		},
		dirty: {
			type: Boolean,
			reflect: !0
		},
		statusLabel: {
			type: String,
			attribute: "status-label"
		},
		statusLabelOne: {
			type: String,
			attribute: "status-label-one"
		},
		statusLabelNone: {
			type: String,
			attribute: "status-label-none"
		}
	};
	constructor() {
		super(), this.mode = "client", this.count = null, this.open = !1, this.dirty = !1, this.statusLabel = "{count} Ergebnisse", this.statusLabelOne = "1 Ergebnis", this.statusLabelNone = "Keine Ergebnisse", this._resetByButton = !1, this._sheet = null, this._placeholder = null, this._opener = null, this._inlineQuery = U("lg"), this._onChange = () => this._handleChange(), this._onReset = () => setTimeout(() => this._handleReset()), this._onSubmit = (e) => this._handleSubmit(e), this._onClick = (e) => this._handleClick(e), this._onDocumentClick = (e) => this._handleDocumentClick(e), this._onBreakpoint = () => {
			this._inlineQuery.matches && this.hide();
		};
	}
	createRenderRoot() {
		return this;
	}
	get form() {
		return this.querySelector(".m-filter__form");
	}
	get params() {
		let e = this.form;
		return e ? new URLSearchParams(new FormData(e)) : new URLSearchParams();
	}
	connectedCallback() {
		super.connectedCallback(), this.addEventListener("change", this._onChange), this.addEventListener("reset", this._onReset), this.addEventListener("submit", this._onSubmit), this.addEventListener("click", this._onClick), document.addEventListener("click", this._onDocumentClick), this._inlineQuery.addEventListener("change", this._onBreakpoint), this._syncState();
	}
	disconnectedCallback() {
		super.disconnectedCallback(), this.removeEventListener("change", this._onChange), this.removeEventListener("reset", this._onReset), this.removeEventListener("submit", this._onSubmit), this.removeEventListener("click", this._onClick), document.removeEventListener("click", this._onDocumentClick), this._inlineQuery.removeEventListener("change", this._onBreakpoint), this.hide();
	}
	willUpdate(e) {
		e.has("mode") && !ht.includes(this.mode) && console.warn(`m-filter: invalid mode "${this.mode}". Use one of: ${ht.join(", ")}.`), e.has("open") && (this.open && !this._sheet?.open ? this.show() : !this.open && this._sheet?.open && this.hide());
	}
	updated(e) {
		super.updated(e), e.has("count") && (this.querySelectorAll(".m-filter__apply").forEach((e) => _t(e, this.count)), this.open && this.count !== null && this._announce()), e.has("open") && this._openers().forEach((e) => e.setAttribute("aria-expanded", String(this.open)));
	}
	setAvailable(e, t) {
		let n = this.form;
		n && n.querySelectorAll(`input[type=checkbox][name="${CSS.escape(e)}"]`).forEach((e) => {
			let n = e;
			n.disabled = !!t && !n.checked && !t.has(n.value);
		});
	}
	show() {
		if (this._sheet?.open) return;
		let e = this.querySelector(".m-filter__panel");
		if (!e || this._inlineQuery.matches) {
			this.open = !1;
			return;
		}
		let t = this._ensureSheet();
		this._placeholder = document.createComment("m-filter panel"), e.before(this._placeholder), t.append(e), this.open = !0, t.showModal();
	}
	hide() {
		this._sheet?.open && this._sheet.close();
	}
	reset() {
		this.form?.reset();
	}
	_ensureSheet() {
		if (this._sheet) return this._sheet;
		let e = document.createElement("dialog");
		e.className = "m-filter__sheet", e.id = `m-filter-sheet-${++gt}`;
		let t = this.querySelector(".m-filter__form")?.getAttribute("aria-labelledby");
		t && e.setAttribute("aria-labelledby", t), this._openers().forEach((t) => t.setAttribute("aria-controls", e.id));
		let n = document.createElement("div");
		return n.className = "m-filter__grip", n.setAttribute("aria-hidden", "true"), e.append(n), e.addEventListener("close", () => this._handleClose()), e.addEventListener("click", (t) => {
			t.target === e && this.hide();
		}), this.append(e), this._sheet = e, e;
	}
	_handleClose() {
		let e = this._sheet?.querySelector(".m-filter__panel");
		e && this._placeholder && this._placeholder.replaceWith(e), this._placeholder = null, this.open = !1, this._opener?.focus(), this._opener = null;
	}
	_handleChange() {
		this._syncState(), this.mode !== "submit" && this.dispatchEvent(new CustomEvent("m-filter-change", {
			bubbles: !0,
			composed: !0,
			detail: { params: this.params }
		}));
	}
	async _handleReset() {
		if (this.form?.querySelectorAll("input[type=checkbox]:disabled").forEach((e) => {
			e.disabled = !1;
		}), this._handleChange(), !this._resetByButton) return;
		this._resetByButton = !1, await this.updateComplete;
		let e = this.querySelector(".m-filter__panel .m-filter__reset");
		if (e && e.getClientRects().length > 0) return;
		let t = this.querySelector(".m-filter__title");
		t && (t.setAttribute("tabindex", "-1"), t.focus());
	}
	_announce() {
		let e = this.querySelector(".m-filter__panel");
		if (!e) return;
		let t = e.querySelector(".m-filter__status");
		t || (t = document.createElement("div"), t.className = "m-filter__status", t.setAttribute("role", "status"), e.prepend(t));
		let n = this.statusLabel;
		this.count === 0 ? n = this.statusLabelNone : this.count === 1 && (n = this.statusLabelOne), t.textContent = n.replace("{count}", String(this.count));
	}
	_openers() {
		return this.id ? [...document.querySelectorAll(`[data-filter-open="${CSS.escape(this.id)}"]`)] : [];
	}
	_handleSubmit(e) {
		this.mode !== "submit" && (e.preventDefault(), this.hide());
	}
	_handleClick(e) {
		e.target.closest?.(".m-filter__close") && this.hide(), e.target.closest?.(".m-filter__reset") && (this._resetByButton = !0);
	}
	_handleDocumentClick(e) {
		if (!this.id) return;
		let t = e.target.closest?.(`[data-filter-open="${CSS.escape(this.id)}"]`);
		t && (this._opener = t, this.show());
	}
	_syncState() {
		let e = this.form;
		if (!e) return;
		let t = [...e.querySelectorAll("input[type=checkbox], input[type=radio]")];
		this.dirty = t.some((e) => e.checked !== e.defaultChecked);
		let n = t.filter((e) => e.type === "checkbox" && e.checked).length;
		this._openers().forEach((e) => _t(e, n));
	}
};
customElements.define("m-filter", vt);
//#endregion
//#region ../mde-storybook/src/components/anchor-list/anchor-list.js
var yt = class extends g {
	static properties = { sticky: {
		type: Boolean,
		reflect: !0
	} };
	constructor() {
		super(), this.sticky = !1, this._row = null, this._stuckFrame = 0, this._onPageScroll = () => {
			this._stuckFrame ||= requestAnimationFrame(() => {
				this._stuckFrame = 0, this._updateStuck();
			});
		}, this._onScroll = () => this._updateFades(), this._resize = new ResizeObserver(() => {
			this._updateFades(), this._reveal(!1);
		}), this._sizedParent = null, this._size = new ResizeObserver(([e]) => {
			let t = e.borderBoxSize?.[0]?.blockSize ?? e.contentRect.height;
			this._sizedParent?.style.setProperty("--m-anchor-list-height", `${t}px`);
		}), this._mutations = new MutationObserver((e) => {
			e.some((e) => e.type === "childList") && this._connectRow(), this._reveal(!0);
		});
	}
	createRenderRoot() {
		return this;
	}
	connectedCallback() {
		super.connectedCallback(), this._mutations.observe(this, {
			subtree: !0,
			childList: !0,
			attributes: !0,
			attributeFilter: ["aria-current"]
		}), this._connectRow(), this.hasUpdated && this.sticky && this._watchPage(!0), requestAnimationFrame(() => this._reveal(!1));
	}
	disconnectedCallback() {
		super.disconnectedCallback(), this._mutations.disconnect(), this._resize.disconnect(), this._watchPage(!1), this._row?.removeEventListener("scroll", this._onScroll), this._row = null;
	}
	updated(e) {
		e.has("sticky") && this._watchPage(this.sticky);
	}
	_watchPage(e) {
		if (window.removeEventListener("scroll", this._onPageScroll), window.removeEventListener("resize", this._onPageScroll), cancelAnimationFrame(this._stuckFrame), this._stuckFrame = 0, this._size.disconnect(), this._sizedParent?.style.removeProperty("--m-anchor-list-height"), this._sizedParent = null, !e) {
			this.removeAttribute("data-stuck");
			return;
		}
		this._sizedParent = this.parentElement, this._size.observe(this), window.addEventListener("scroll", this._onPageScroll, { passive: !0 }), window.addEventListener("resize", this._onPageScroll, { passive: !0 }), this._onPageScroll();
	}
	_updateStuck() {
		if (!this.isConnected || !this.offsetParent) return;
		let e = parseFloat(getComputedStyle(this).top) || 0, t = Math.abs(this.getBoundingClientRect().top - e) < 1;
		this.toggleAttribute("data-stuck", t && window.scrollY > 0);
	}
	_connectRow() {
		let e = this.querySelector(".m-anchor-list__list");
		e !== this._row && (this._row?.removeEventListener("scroll", this._onScroll), this._resize.disconnect(), this._row = e, e && (e.addEventListener("scroll", this._onScroll, { passive: !0 }), this._resize.observe(e), this._updateFades()));
	}
	_updateFades() {
		let e = this._row;
		if (!e) return;
		let t = e.scrollWidth - e.clientWidth;
		this.toggleAttribute("data-more-start", t > 1 && e.scrollLeft > 1), this.toggleAttribute("data-more-end", t > 1 && e.scrollLeft < t - 1);
	}
	_reveal(e) {
		let t = this._row, n = t?.querySelector("[aria-current]");
		if (!n || t.scrollWidth <= t.clientWidth) return;
		let r = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
		t.scrollTo({
			left: n.offsetLeft - (t.clientWidth - n.offsetWidth) / 2,
			behavior: e && !r ? "smooth" : "auto"
		});
	}
};
customElements.define("m-anchor-list", yt);
//#endregion
//#region ../mde-storybook/src/components/service-list/service-list.js
var bt = ["topics", "title"], xt = 600, St = 150, Ct = 0;
function Z(e) {
	return e.replace(/-([a-z])/g, (e, t) => t.toUpperCase());
}
function wt(e) {
	return e.toLocaleLowerCase("de").normalize("NFD").replace(/[̀-ͯ]/g, "").trim();
}
function Q(e) {
	return e.querySelector("a")?.getAttribute("href") ?? e.textContent.trim();
}
function Tt(e) {
	let t = e.trim().charAt(0).normalize("NFD").charAt(0).toUpperCase();
	return /[A-Z]/.test(t) ? t : "#";
}
var Et = class extends g {
	static properties = {
		filter: { type: String },
		search: { type: String },
		query: { type: String },
		queryParam: {
			type: String,
			attribute: "query-param"
		},
		sortParam: {
			type: String,
			attribute: "sort-param"
		},
		view: {
			type: String,
			reflect: !0
		},
		azLabel: {
			type: String,
			attribute: "az-label"
		},
		azHeading: {
			type: String,
			attribute: "az-heading"
		},
		azEmpty: {
			type: String,
			attribute: "az-empty"
		},
		statusFor: {
			type: String,
			attribute: "status-for"
		}
	};
	constructor() {
		super(), this.filter = void 0, this.search = void 0, this.query = void 0, this.queryParam = "q", this.sortParam = "sort", this.view = "topics", this.azLabel = "Leistungen von A bis Z", this.azHeading = "Services mit {letter}", this.azEmpty = "keine Einträge", this.statusFor = "für „{query}“", this.count = 0, this._id = `m-service-list-${++Ct}`, this._filterEl = null, this._searchEl = null, this._liveTerm = null, this._params = new URLSearchParams(), this._spy = null, this._announceTimer = void 0, this._inputTimer = void 0, this._anchors = null, this._azKey = null, this._ready = !1, this._onFilterChange = (e) => this._apply(e.detail.params), this._onClick = (e) => this._handleClick(e), this._onInput = () => {
			this._liveTerm = this._searchEl?.value ?? "", clearTimeout(this._inputTimer), this._inputTimer = setTimeout(() => this._apply(this._params), St);
		}, this._onSearchSubmit = (e) => {
			e.preventDefault(), clearTimeout(this._inputTimer), this._apply(this._params, { immediate: !0 });
		};
	}
	createRenderRoot() {
		return this;
	}
	connectedCallback() {
		super.connectedCallback(), this.addEventListener("click", this._onClick), queueMicrotask(() => {
			this._connectSearch(), this._connectFilter();
		});
	}
	disconnectedCallback() {
		super.disconnectedCallback(), this.removeEventListener("click", this._onClick), this._filterEl?.removeEventListener("m-filter-change", this._onFilterChange), this._filterEl = null, this._searchEl?.removeEventListener("input", this._onInput), this._searchEl?.form?.removeEventListener("submit", this._onSearchSubmit), this._searchEl = null, this._spy?.disconnect(), clearTimeout(this._announceTimer), clearTimeout(this._inputTimer);
	}
	willUpdate(e) {
		e.has("view") && !bt.includes(this.view) && console.warn(`m-service-list: invalid view "${this.view}". Use one of: ${bt.join(", ")}.`);
	}
	updated(e) {
		super.updated(e), e.has("query") && this._ready && this._apply(this._params);
	}
	_connectSearch() {
		if (!this.search) return;
		let e = document.getElementById(this.search);
		if (!(e instanceof HTMLInputElement)) {
			console.warn(`m-service-list: no search input with id "${this.search}".`);
			return;
		}
		this._searchEl = e, e.addEventListener("input", this._onInput), e.form?.addEventListener("submit", this._onSearchSubmit), e.value && (this._liveTerm = e.value);
	}
	_connectFilter() {
		let e = this.filter ? document.getElementById(this.filter) : this.querySelector("m-filter");
		if (this._filterEl = e, !e) {
			this._apply(new URLSearchParams());
			return;
		}
		e.addEventListener("m-filter-change", this._onFilterChange), customElements.whenDefined("m-filter").then(() => {
			this._apply(e.params);
		});
	}
	get _searchTerm() {
		return (this._liveTerm ?? this.query ?? new URLSearchParams(window.location.search).get(this.queryParam) ?? "").trim();
	}
	_apply(e, { immediate: t = !1 } = {}) {
		this._params = e;
		let n = e.get(this.sortParam);
		n !== null && (this.view = n === "title" ? "title" : "topics");
		let r = wt(this._searchTerm), i = [...new Set(e.keys())].filter((e) => e !== this.sortParam), a = [...this.querySelectorAll(".m-service-list__topics .m-service-list__item")], o = (e) => !r || wt(e.textContent).includes(r), s = (t, n) => {
			let r = e.getAll(n);
			if (!r.length) return !0;
			let i = (t.dataset[Z(n)] ?? "").split(/\s+/);
			return r.some((e) => i.includes(e));
		}, c = /* @__PURE__ */ new Set();
		a.forEach((e) => {
			let t = o(e) && i.every((t) => s(e, t));
			e.hidden = !t, t && c.add(Q(e));
		}), this._hideEmpty(this.querySelector(".m-service-list__topics")), this.view === "title" && this._buildAz(a, c), this._updateAvailability(a, o, s), this.count = c.size, this._renderCount(), this._ready && this._announce(t), this._ready = !0;
	}
	_updateAvailability(e, t, n) {
		let r = this._filterEl, i = r?.form;
		if (!i || typeof r.setAvailable != "function") return;
		let a = [...new Set([...i.querySelectorAll("input[type=checkbox]")].map((e) => e.name))];
		a.forEach((i) => {
			let o = Z(i), s = a.filter((e) => e !== i), c = /* @__PURE__ */ new Set();
			e.forEach((e) => {
				!t(e) || !s.every((t) => n(e, t)) || (e.dataset[o] ?? "").split(/\s+/).filter(Boolean).forEach((e) => c.add(e));
			}), r.setAvailable(i, c);
		});
	}
	_hideEmpty(e) {
		e && (e.querySelectorAll(".m-service-list__group").forEach((e) => {
			e.hidden = !e.querySelector(".m-service-list__item:not([hidden])");
		}), e.querySelectorAll(".m-service-list__area").forEach((e) => {
			e.hidden = !e.querySelector(".m-service-list__group:not([hidden])");
		}));
	}
	_buildAz(e, t) {
		let n = this.querySelector(".m-service-list__az");
		if (!n) return;
		let r = [...t].join("\n");
		if (r === this._azKey && n.contains(this._anchors)) return;
		this._azKey = r;
		let i = /* @__PURE__ */ new Set(), a = /* @__PURE__ */ new Map();
		e.filter((e) => t.has(Q(e))).filter((e) => !i.has(Q(e)) && i.add(Q(e))).sort((e, t) => e.textContent.trim().localeCompare(t.textContent.trim(), "de")).forEach((e) => {
			let t = Tt(e.textContent);
			a.has(t) || a.set(t, []), a.get(t).push(e);
		});
		let o = "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("");
		if (!this._anchors) {
			let e = document.createElement("m-anchor-list");
			e.className = "m-service-list__anchors", e.setAttribute("sticky", "");
			let t = document.createElement("nav");
			t.setAttribute("aria-label", this.azLabel);
			let n = document.createElement("ul");
			n.className = "m-anchor-list__list", t.append(n), e.append(t), this._anchors = e;
		}
		let s = this._anchors, c = s.querySelector(".m-anchor-list__list");
		c.replaceChildren();
		let l = document.createDocumentFragment();
		if (o.forEach((e) => {
			let t = document.createElement("li"), n = a.get(e), r = `${this._id}-${e.toLowerCase()}`;
			if (n) {
				let i = document.createElement("a");
				i.className = "m-anchor-list__link", i.href = `#${r}`, i.textContent = e, t.append(i), l.append(this._azGroup(r, e, n));
			} else {
				let n = document.createElement("span");
				n.className = "m-anchor-list__link m-anchor-list__link--empty", n.textContent = e;
				let r = document.createElement("span");
				r.className = "visually-hidden", r.textContent = `, ${this.azEmpty}`, n.append(r), t.append(n);
			}
			c.append(t);
		}), a.has("#") && l.append(this._azGroup(`${this._id}-other`, "#", a.get("#"))), s.parentNode === n) {
			for (; s.nextSibling;) s.nextSibling.remove();
			for (; s.previousSibling;) s.previousSibling.remove();
			n.append(l);
		} else n.replaceChildren(s, l);
		this._observeAnchors(n);
	}
	_azGroup(e, t, n) {
		let r = document.createElement("section");
		r.className = "m-service-list__group", r.setAttribute("aria-labelledby", e);
		let i = document.createElement(this._groupHeadingTag);
		i.className = "m-service-list__group-title", i.id = e, i.textContent = this.azHeading.replace("{letter}", t);
		let a = document.createElement("div");
		a.className = "m-linklist m-linklist--list";
		let o = document.createElement("ul");
		return o.className = "m-linklist__list m-service-list__links", n.forEach((e) => {
			let t = e.cloneNode(!0);
			t.hidden = !1, o.append(t);
		}), a.append(o), r.append(i, a), r;
	}
	get _groupHeadingTag() {
		return this.querySelector(".m-service-list__group-title")?.tagName.toLowerCase() ?? "h3";
	}
	_observeAnchors(e) {
		if (this._spy?.disconnect(), !("IntersectionObserver" in window)) return;
		let t = new Map([...e.querySelectorAll("a.m-anchor-list__link")].map((e) => [e.getAttribute("href").slice(1), e]));
		this._spy = new IntersectionObserver((e) => {
			let n = e.find((e) => e.isIntersecting);
			if (!n) return;
			t.forEach((e) => e.removeAttribute("aria-current"));
			let r = t.get(n.target.id);
			r && r.setAttribute("aria-current", "true");
		}, { rootMargin: "0px 0px -70% 0px" }), e.querySelectorAll(".m-service-list__group-title").forEach((e) => this._spy.observe(e));
	}
	_renderCount() {
		let e = this.querySelector(".m-service-list__count");
		e?.dataset.label && (e.textContent = this._countText(e));
		let t = this.querySelector(".m-service-list__empty");
		if (t) {
			t.hidden = this.count > 0;
			let e = t.querySelector("[data-label]");
			e instanceof HTMLElement && (e.textContent = this._emptyText(e));
			let n = t.querySelector(".m-service-list__reset");
			n instanceof HTMLElement && (n.hidden = !this._filterEl?.dirty);
		}
		this._filterEl && (this._filterEl.count = this.count);
	}
	_countText(e) {
		let { label: t, labelOne: n } = e.dataset;
		return (this.count === 1 && n ? n : t).replace("{count}", String(this.count));
	}
	_emptyText(e) {
		let t = this._searchTerm, { label: n, labelNone: r } = e.dataset;
		return !t && r ? r : n.replace("{query}", t);
	}
	_announce(e) {
		clearTimeout(this._announceTimer);
		let t = () => {
			let e = this._statusEl, t = this.querySelector(".m-service-list__count"), n = this.querySelector(".m-service-list__empty [data-label]"), r = this._searchTerm, i;
			if (this.count === 0 && n) i = this._emptyText(n);
			else if (t?.dataset.label) i = this._countText(t), r && (i += ` ${this.statusFor.replace("{query}", r)}`);
			else return;
			e.textContent = "", requestAnimationFrame(() => {
				e.textContent = i;
			});
		};
		e ? t() : this._announceTimer = window.setTimeout(t, xt);
	}
	get _statusEl() {
		let e = this.querySelector(".m-service-list__status");
		return e || (e = document.createElement("div"), e.className = "m-service-list__status", e.setAttribute("role", "status"), (this.querySelector(".m-service-list__results") ?? this).prepend(e)), e;
	}
	_handleClick(e) {
		if (e.target.closest?.(".m-service-list__reset")) {
			this._filterEl?.reset(), requestAnimationFrame(() => {
				let e = this.querySelector(".m-service-list__count");
				e && (e.setAttribute("tabindex", "-1"), e.focus());
			});
			return;
		}
		let t = (e.target.closest?.("a.m-anchor-list__link"))?.getAttribute("href") ?? "", n = t.length > 1 && t.startsWith("#") ? this.querySelector(`#${CSS.escape(t.slice(1))}`) : null;
		if (!(n instanceof HTMLElement)) return;
		e.preventDefault();
		let r = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
		n.setAttribute("tabindex", "-1"), n.scrollIntoView({ behavior: r ? "auto" : "smooth" }), n.focus({ preventScroll: !0 }), history.replaceState(null, "", t);
	}
};
customElements.define("m-service-list", Et);
//#endregion
//#region ../mde-storybook/src/components/table-of-contents/table-of-contents.js
var Dt = 0, $ = "m-table-of-contents", Ot = class extends g {
	static properties = {
		aside: {
			type: Boolean,
			reflect: !0
		},
		open: {
			type: Boolean,
			reflect: !0
		},
		closeLabel: {
			type: String,
			attribute: "close-label"
		}
	};
	constructor() {
		super(), this.aside = !1, this.open = !1, this.closeLabel = "Schließen", this._bar = null, this._sheet = null, this._placeholder = null, this._sizedParent = null, this._wideQuery = U("xl"), this._onClick = (e) => this._handleClick(e), this._onBreakpoint = () => {
			this._wideQuery.matches && this.hide();
		}, this._mutations = new MutationObserver(() => {
			this._ensureBar() && this._mutations.disconnect();
		}), this._size = new ResizeObserver(([e]) => {
			let t = e.borderBoxSize?.[0]?.blockSize ?? e.contentRect.height;
			this._sizedParent?.style.setProperty("--m-table-of-contents-height", `${t}px`);
		});
	}
	createRenderRoot() {
		return this;
	}
	get _nav() {
		return this.querySelector(`nav.${$}`);
	}
	get _list() {
		return this.querySelector(`.${$}__list`);
	}
	connectedCallback() {
		super.connectedCallback(), this.addEventListener("click", this._onClick), this._wideQuery.addEventListener("change", this._onBreakpoint), this._ensureBar() || this._mutations.observe(this, {
			childList: !0,
			subtree: !0
		});
	}
	disconnectedCallback() {
		super.disconnectedCallback(), this.removeEventListener("click", this._onClick), this._wideQuery.removeEventListener("change", this._onBreakpoint), this._mutations.disconnect(), this._watchSize(!1), this.hide();
	}
	updated(e) {
		e.has("open") && (this.open && !this._sheet?.open ? this.show() : !this.open && this._sheet?.open && this.hide()), e.has("closeLabel") && this._sheet && this._sheet.querySelector(`.${$}__close`)?.setAttribute("aria-label", this.closeLabel);
	}
	show() {
		let e = this._list;
		if (this._sheet?.open) return;
		if (!e || this._wideQuery.matches) {
			this.open = !1;
			return;
		}
		let t = this._ensureSheet();
		this._placeholder = document.createComment(`${$} list`), e.before(this._placeholder), t.querySelector(`.${$}__sheet-nav`)?.append(e), this.open = !0, this._bar?.setAttribute("aria-expanded", "true"), t.showModal();
	}
	hide() {
		this._sheet?.open && (this._sheet.close(), this._handleClose());
	}
	_ensureBar() {
		if (this._bar) return !0;
		let e = this._nav, t = e?.querySelector(`.${$}__title`);
		if (!e || !t) return !1;
		let n = document.createElement("button");
		n.type = "button", n.className = `${$}__bar`, n.setAttribute("aria-haspopup", "dialog"), n.setAttribute("aria-expanded", "false");
		let r = document.createElement("span");
		r.className = `${$}__bar-label`, r.textContent = t.textContent?.trim() || "";
		let i = document.createElement("m-icon");
		return i.setAttribute("name", "chevron-down"), i.setAttribute("aria-hidden", "true"), n.append(r, i), e.prepend(n), this._bar = n, this._watchSize(!0), !0;
	}
	_watchSize(e) {
		this._size.disconnect(), this._sizedParent?.style.removeProperty("--m-table-of-contents-height"), this._sizedParent = null, !(!e || !this._bar) && (this._sizedParent = this.parentElement, this._size.observe(this._bar));
	}
	_ensureSheet() {
		if (this._sheet) return this._sheet;
		let e = `${$}-sheet-${++Dt}`, t = document.createElement("dialog");
		t.className = `${$}__sheet`, t.id = e;
		let n = document.createElement("div");
		n.className = `${$}__grip`, n.setAttribute("aria-hidden", "true");
		let r = document.createElement("div");
		r.className = `${$}__sheet-head`;
		let i = document.createElement("p");
		i.className = `${$}__sheet-title`, i.id = `${e}-title`, i.textContent = this._bar?.textContent?.trim() || "";
		let a = document.createElement("button");
		a.type = "button", a.className = `${$}__close`, a.setAttribute("aria-label", this.closeLabel);
		let o = document.createElement("m-icon");
		o.setAttribute("name", "close"), o.setAttribute("aria-hidden", "true"), a.append(o), r.append(i, a);
		let s = document.createElement("nav");
		return s.className = `${$}__sheet-nav`, s.setAttribute("aria-labelledby", i.id), t.setAttribute("aria-labelledby", i.id), t.append(n, r, s), t.addEventListener("close", () => this._handleClose()), t.addEventListener("click", (e) => {
			e.target === t && this.hide();
		}), this._bar?.setAttribute("aria-controls", e), this.append(t), this._sheet = t, t;
	}
	_handleClose() {
		if (!this._placeholder) return;
		let e = this._sheet?.querySelector(`.${$}__list`);
		if (e && this._placeholder.replaceWith(e), this._placeholder = null, this.open = !1, this._bar?.setAttribute("aria-expanded", "false"), !this.isConnected) return;
		let t = this._bar;
		t && t.getClientRects().length > 0 ? t.focus() : this._list?.querySelector("a")?.focus();
	}
	_handleClick(e) {
		let t = e.target;
		if (t.closest?.(`.${$}__bar`)) {
			this.show();
			return;
		}
		if (t.closest?.(`.${$}__close`)) {
			this.hide();
			return;
		}
		let n = t.closest?.(`a.${$}__link`);
		n && this._follow(n, e);
	}
	async _follow(e, t) {
		if (t.button !== 0 || t.metaKey || t.ctrlKey || t.shiftKey || t.altKey) return;
		let n = e.getAttribute("href") || "", r = null, i = "";
		try {
			i = decodeURIComponent(n.slice(1)), r = n.startsWith("#") && i ? document.getElementById(i) : null;
		} catch {}
		if (!r) {
			i && console.warn(`m-table-of-contents: no element with id "${i}".`), this.hide();
			return;
		}
		t.preventDefault(), this.hide(), await this.updateComplete, location.hash === n ? r.scrollIntoView({ block: "start" }) : location.hash = n, r.matches("a[href], button, input, select, textarea, [tabindex]") || r.setAttribute("tabindex", "-1"), r.focus({ preventScroll: !0 }), this.dispatchEvent(new CustomEvent(`${$}:navigate`, {
			bubbles: !0,
			composed: !0,
			detail: { id: i }
		}));
	}
};
customElements.define("m-table-of-contents", Ot);
//#endregion
