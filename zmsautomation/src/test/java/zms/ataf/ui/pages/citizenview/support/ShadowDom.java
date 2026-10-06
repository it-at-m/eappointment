package zms.ataf.ui.pages.citizenview.support;

import java.time.Duration;

import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.utils.DriverUtil;
import zms.ataf.ui.pages.citizenview.CitizenViewPageContext;

/**
 * Shadow-DOM probes and clicks for zmscitizenview (deep query / text walk).
 */
public final class ShadowDom {

    private final CitizenViewPageContext context;
    private final int defaultWaitSeconds;

    public ShadowDom(CitizenViewPageContext context, int defaultWaitSeconds) {
        this.context = context;
        this.defaultWaitSeconds = defaultWaitSeconds;
    }

    /**
     * True if substring appears anywhere in document + shadow DOM text.
     * Also walks slotted nodes and same-origin frames, and folds whitespace, so a painted
     * callout such as "Sie sind angemeldet." matches even when its text is split across nodes.
     */
    public boolean shadowDomContainsText(String substring) {
        context.set();
        String esc = substring.replace("\\", "\\\\").replace("'", "\\'");
        String script =
                "var sub='" + esc + "'.replace(/\\s+/g,' ').trim();"
                        + "function walk(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=' '+walk(n.shadowRoot);"
                        + "if(n.assignedNodes){var a=n.assignedNodes({flatten:true});"
                        + "for(var j=0;j<a.length;j++)s+=' '+walk(a[j]);}"
                        + "var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=' '+walk(c[i]);"
                        + "if(n.nodeType===1){var tag=n.tagName;"
                        + "if(tag==='INPUT'||tag==='TEXTAREA')s+=' '+(n.value||'');"
                        + "if(n.contentDocument){try{s+=' '+walk(n.contentDocument.body);}catch(e){}}}"
                        + "return s;}"
                        + "return walk(document.documentElement).replace(/\\s+/g,' ').indexOf(sub)>=0;";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return Boolean.TRUE.equals(o);
    }

    /** True when an {@code h1}–{@code h6} in the shadow tree has this exact text. Level 2 is an {@code h2}. */

    /** True when an {@code h1}–{@code h6} in the shadow tree has this exact text. Level 2 is an {@code h2}. */
    public boolean shadowDomHasHeading(int level, String heading) {
        Assert.assertTrue(level >= 1 && level <= 6, "Heading level must be 1 to 6.");
        String script =
                "var tagName=arguments[0];var heading=arguments[1];"
                        + "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=textOf(n.shadowRoot);var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=textOf(c[i]);return s;}"
                        + "function walk(n,fn){if(!n)return false;if(fn(n))return true;if(n.shadowRoot&&walk(n.shadowRoot,fn))return true;"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i],fn))return true;return false;}"
                        + "return walk(document.body,function(n){"
                        + "var tag=(n.tagName||'').toUpperCase();"
                        + "return tag===tagName&&textOf(n).trim()===heading;});";
        Object raw =
                ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, "H" + level, heading);
        return Boolean.TRUE.equals(raw);
    }

    /** Heading text that is actually painted. Hidden copies under {@code v-show} do not count. */

    /** Heading text that is actually painted. Hidden copies under {@code v-show} do not count. */
    public boolean visibleHeadingShows(int level, String heading) {
        String script =
                "var tagName=arguments[0];var heading=arguments[1];"
                        + "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=textOf(n.shadowRoot);var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=textOf(c[i]);return s;}"
                        + "function shown(el){var n=el;while(n&&n.nodeType===1){"
                        + "var st=window.getComputedStyle(n);"
                        + "if(st.display==='none'||st.visibility==='hidden'||st.opacity==='0')return false;"
                        + "if(n.parentElement){n=n.parentElement;continue;}"
                        + "var root=n.getRootNode&&n.getRootNode();n=root&&root.host?root.host:null;}"
                        + "return true;}"
                        + "function walk(n){if(!n)return false;"
                        + "var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag===tagName&&textOf(n).replace(/\\s+/g,' ').trim()===heading&&shown(n))return true;"
                        + "if(n.shadowRoot&&walk(n.shadowRoot))return true;"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i]))return true;return false;}"
                        + "return walk(document.body);";
        Object raw =
                ((JavascriptExecutor) DriverUtil.getDriver())
                        .executeScript(script, "H" + level, heading);
        return Boolean.TRUE.equals(raw);
    }


    public void waitUntilShadowContains(String substring, int seconds) {
        context.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(seconds))
                .until(d -> shadowDomContainsText(substring));
    }

    /**
     * True once the given service label appears somewhere in the DOM/shadow DOM
     * <em>outside</em> the static "Häufig gesuchte Leistungen" quick-link list.
     * This is a proxy for "offices-and-services have loaded and the label is
     * available in API-backed UI (e.g. select options)".
     */

    public void assertShadowContains(String substring, String message) {
        waitUntilShadowContains(substring, defaultWaitSeconds);
        Assert.assertTrue(shadowDomContainsText(substring), message);
    }

    /**
     * Find first element matching CSS in document or any shadow root; click via JS.
     */

    /**
     * Find first element matching CSS in document or any shadow root; click via JS.
     */
    public boolean deepClick(String cssSelector) {
        context.set();
        String script =
                "var sel=arguments[0];function find(root){if(!root)return null;var q=root.querySelector(sel);if(q)return q;"
                        + "var all=root.querySelectorAll('*');for(var i=0;i<all.length;i++){if(all[i].shadowRoot){var f=find(all[i].shadowRoot);if(f)return f;}}return null;}"
                        + "var e=document.querySelector(sel)||find(document.body);if(e){e.scrollIntoView({block:'center'});e.click();return true;}return false;";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, cssSelector);
        return Boolean.TRUE.equals(o);
    }


    public void deepClickRequired(String cssSelector) {
        Assert.assertTrue(deepClick(cssSelector), "Could not click: " + cssSelector);
    }

    /** True if an element matching {@code cssSelector} exists in document or any open shadow root. */

    /** True if an element matching {@code cssSelector} exists in document or any open shadow root. */
    public boolean deepElementExists(String cssSelector) {
        context.set();
        String script =
                "var sel=arguments[0];function find(root){if(!root)return null;var q=root.querySelector(sel);if(q)return q;"
                        + "var all=root.querySelectorAll('*');for(var i=0;i<all.length;i++){if(all[i].shadowRoot){var f=find(all[i].shadowRoot);if(f)return f;}}return null;}"
                        + "return !!(document.querySelector(sel)||find(document.body));";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, cssSelector);
        return Boolean.TRUE.equals(o);
    }


    public void waitUntilDeepElementExists(String cssSelector, int seconds) {
        context.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(seconds))
                .until(d -> deepElementExists(cssSelector));
    }

    /**
     * Ort step: either multi-provider ({@code #checkbox-provider-{id}}) or single-provider teaser
     * ({@code h3#provider-{id}}) — see ProviderSelection.vue.
     */

    public boolean deepVisibleCssExists(String cssSelector) {
        context.set();
        String script =
                "var sel=arguments[0];"
                        + "function visible(el){"
                        + " if(!el||el.nodeType!==1)return false;"
                        + " var n=el;"
                        + " while(n){"
                        + "  if(n.nodeType===1){"
                        + "   try{var st=getComputedStyle(n);if(st.display==='none'||st.visibility==='hidden')return false;}catch(e0){}"
                        + "  }"
                        + "  if(n.parentElement){n=n.parentElement;continue;}"
                        + "  var root=n.getRootNode&&n.getRootNode();"
                        + "  if(root&&root.host){n=root.host;continue;}"
                        + "  break;"
                        + " }"
                        + " try{return el.getClientRects().length>0;}catch(e1){return true;}"
                        + "}"
                        + "function find(root){if(!root)return false;"
                        + " try{var q=root.querySelectorAll(sel);for(var i=0;i<q.length;i++)if(visible(q[i]))return true;}catch(e2){}"
                        + " if(root.shadowRoot&&find(root.shadowRoot))return true;"
                        + " var c=root.children;if(c)for(var j=0;j<c.length;j++)if(find(c[j]))return true;"
                        + " return false;}"
                        + "return find(document.body);";
        return Boolean.TRUE.equals(
                ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, cssSelector));
    }


    /**
     * Set value on input/textarea. {@code muc-input} / {@code muc-text-area} use host ids ({@code firstname},
     * {@code mailaddress}) with the real control inside <strong>shadow DOM</strong>; also tries {@code input-*} ids.
     * Dispatches {@code InputEvent} so Vue v-model updates (plain {@code value=} is not enough).
     */
    public boolean deepSetById(String id, String value) {
        context.set();
        String script =
                "var want=arguments[0],v=arguments[1]==null?'':String(arguments[1]);"
                        // MucInput → input-{id}; MucTextArea → textarea-{id} (id prop is not on the host).
                        + "var ids=[];ids.push(want);"
                        + "if(want.indexOf('input-')===0)ids.push(want.slice(6));else ids.push('input-'+want);"
                        + "if(want.indexOf('textarea-')===0)ids.push(want.slice(9));else ids.push('textarea-'+want);"
                        + "function byId(root,id){try{if(root.getElementById)return root.getElementById(id);}catch(e0){}"
                        + "try{return root.querySelector('#'+id.replace(/([^a-zA-Z0-9_-])/g,'\\\\$1'));}catch(e1){return root.querySelector('[id=\"'+id.replace(/\"/g,'')+'\"]');}}"
                        + "function resolve(el){if(!el)return null;if(el.tagName==='INPUT'||el.tagName==='TEXTAREA')return el;"
                        + "if(el.shadowRoot){var q=el.shadowRoot.querySelector('input:not([type=hidden]):not([type=checkbox]):not([type=radio]),textarea');if(q)return q;}return null;}"
                        + "function scanRoot(root){if(!root)return null;for(var i=0;i<ids.length;i++){var el=byId(root,ids[i]);var r=resolve(el);if(r)return r;}"
                        + "var nodes=root.querySelectorAll('*');for(var j=0;j<nodes.length;j++){if(nodes[j].shadowRoot){var r2=scanRoot(nodes[j].shadowRoot);if(r2)return r2;}}return null;}"
                        + "var e=scanRoot(document);if(!e)e=scanRoot(document.body);"
                        + "if(e){e.scrollIntoView({block:'center'});e.focus();e.value=v;"
                        + "try{e.dispatchEvent(new InputEvent('input',{bubbles:true,cancelable:true,inputType:'insertReplacementText',data:v}));}catch(ex){e.dispatchEvent(new Event('input',{bubbles:true}));}"
                        + "e.dispatchEvent(new Event('change',{bubbles:true}));return true;}return false;";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, id, value);
        return Boolean.TRUE.equals(o);
    }

    /** Current value of the same shadow input {@link #deepSetById(String, String)} writes. */

    /** Current value of the same shadow input {@link #deepSetById(String, String)} writes. */
    public String deepInputValue(String id) {
        context.set();
        String script =
                "var want=arguments[0];"
                        + "var ids=[];ids.push(want);"
                        + "if(want.indexOf('input-')===0)ids.push(want.slice(6));else ids.push('input-'+want);"
                        + "if(want.indexOf('textarea-')===0)ids.push(want.slice(9));else ids.push('textarea-'+want);"
                        + "function byId(root,id){try{if(root.getElementById)return root.getElementById(id);}catch(e0){}"
                        + "try{return root.querySelector('#'+id.replace(/([^a-zA-Z0-9_-])/g,'\\\\$1'));}catch(e1){return root.querySelector('[id=\"'+id.replace(/\"/g,'')+'\"]');}}"
                        + "function resolve(el){if(!el)return null;if(el.tagName==='INPUT'||el.tagName==='TEXTAREA')return el;"
                        + "if(el.shadowRoot){var q=el.shadowRoot.querySelector('input:not([type=hidden]):not([type=checkbox]):not([type=radio]),textarea');if(q)return q;}return null;}"
                        + "function scanRoot(root){if(!root)return null;for(var i=0;i<ids.length;i++){var el=byId(root,ids[i]);var r=resolve(el);if(r)return r;}"
                        + "var nodes=root.querySelectorAll('*');for(var j=0;j<nodes.length;j++){if(nodes[j].shadowRoot){var r2=scanRoot(nodes[j].shadowRoot);if(r2)return r2;}}return null;}"
                        + "var e=scanRoot(document);if(!e)e=scanRoot(document.body);"
                        + "return e ? String(e.value == null ? '' : e.value) : null;";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, id);
        return o == null ? null : String.valueOf(o);
    }

    /**
     * True when the resolved input/textarea (or its muc-input / muc-text-area host) is disabled or
     * read-only. Same id resolution as {@link #deepSetById(String, String)}.
     */

    /**
     * True when the resolved input/textarea (or its muc-input / muc-text-area host) is disabled or
     * read-only. Same id resolution as {@link #deepSetById(String, String)}.
     */
    public boolean deepControlDisabled(String id) {
        context.set();
        String script =
                "var want=arguments[0];"
                        + "var ids=[];ids.push(want);"
                        + "if(want.indexOf('input-')===0)ids.push(want.slice(6));else ids.push('input-'+want);"
                        + "if(want.indexOf('textarea-')===0)ids.push(want.slice(9));else ids.push('textarea-'+want);"
                        + "function byId(root,id){try{if(root.getElementById)return root.getElementById(id);}catch(e0){}"
                        + "try{return root.querySelector('#'+id.replace(/([^a-zA-Z0-9_-])/g,'\\\\$1'));}catch(e1){"
                        + "return root.querySelector('[id=\"'+id.replace(/\"/g,'')+'\"]');}}"
                        + "function resolve(el){if(!el)return null;if(el.tagName==='INPUT'||el.tagName==='TEXTAREA')return el;"
                        + "if(el.shadowRoot){var q=el.shadowRoot.querySelector("
                        + "'input:not([type=hidden]):not([type=checkbox]):not([type=radio]),textarea');if(q)return q;}"
                        + "return null;}"
                        + "function scanRoot(root){if(!root)return null;for(var i=0;i<ids.length;i++){"
                        + "var el=byId(root,ids[i]);var r=resolve(el);if(r)return {host:el,ctrl:r};}"
                        + "var nodes=root.querySelectorAll('*');for(var j=0;j<nodes.length;j++){"
                        + "if(nodes[j].shadowRoot){var r2=scanRoot(nodes[j].shadowRoot);if(r2)return r2;}}return null;}"
                        + "var found=scanRoot(document);if(!found)found=scanRoot(document.body);"
                        + "if(!found||!found.ctrl)return null;"
                        + "var e=found.ctrl,h=found.host;"
                        + "return !!(e.disabled||e.readOnly||e.getAttribute('aria-disabled')==='true'"
                        + "||(h&&(h.disabled||h.hasAttribute('disabled')||h.getAttribute('aria-disabled')==='true')));";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, id);
        return Boolean.TRUE.equals(o);
    }

    /**
     * Click first button whose visible text includes label (shadow-safe). Includes BUTTON, A, and MUC-BUTTON (modal confirm/cancel).
     * Skips muc-stepper items ("Zurück zu Schritt: …"); those are not the form Zurück.
     */

    /** Read value of input/textarea resolved from host id (shadow-safe). */
    public String deepGetById(String id) {
        context.set();
        String script =
                "var want=arguments[0];"
                        + "var ids=[];ids.push(want);"
                        + "if(want.indexOf('input-')===0)ids.push(want.slice(6));else ids.push('input-'+want);"
                        + "if(want.indexOf('textarea-')===0)ids.push(want.slice(9));else ids.push('textarea-'+want);"
                        + "function byId(root,id){try{if(root.getElementById)return root.getElementById(id);}catch(e0){}"
                        + "try{return root.querySelector('#'+id.replace(/([^a-zA-Z0-9_-])/g,'\\\\$1'));}catch(e1){return root.querySelector('[id=\"'+id.replace(/\"/g,'')+'\"]');}}"
                        + "function resolve(el){if(!el)return null;if(el.tagName==='INPUT'||el.tagName==='TEXTAREA')return el;"
                        + "if(el.shadowRoot){var q=el.shadowRoot.querySelector('input:not([type=hidden]):not([type=checkbox]):not([type=radio]),textarea');if(q)return q;}return null;}"
                        + "function scanRoot(root){if(!root)return null;for(var i=0;i<ids.length;i++){var el=byId(root,ids[i]);var r=resolve(el);if(r)return r;}"
                        + "var nodes=root.querySelectorAll('*');for(var j=0;j<nodes.length;j++){if(nodes[j].shadowRoot){var r2=scanRoot(nodes[j].shadowRoot);if(r2)return r2;}}return null;}"
                        + "var e=scanRoot(document);if(!e)e=scanRoot(document.body);"
                        + "return e?String(e.value||''):null;";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, id);
        return o == null ? null : String.valueOf(o);
    }


    /**
     * Click first button whose visible text includes label (shadow-safe). Includes BUTTON, A, and MUC-BUTTON (modal confirm/cancel).
     * Skips muc-stepper items ("Zurück zu Schritt: …"); those are not the form Zurück.
     */
    public boolean clickButtonContaining(String text) {
        context.set();
        String esc = text.replace("\\", "\\\\").replace("'", "\\'");
        // Prefer visible buttons only: AppointmentSelection stays mounted (v-show) on Kontakt/Übersicht
        // and its Weiter must not steal the click from CustomerInfo/AppointmentSummary.
        String script =
                "var label='" + esc + "';"
                        + "function shown(el){var n=el;while(n&&n.nodeType===1){"
                        + "var st=window.getComputedStyle(n);"
                        + "if(st.display==='none'||st.visibility==='hidden'||st.opacity==='0')return false;"
                        + "if(n.parentElement){n=n.parentElement;continue;}"
                        + "var root=n.getRootNode&&n.getRootNode();n=root&&root.host?root.host:null;}"
                        + "return true;}"
                        + "function visible(el){if(!el||!el.getBoundingClientRect)return false;"
                        + "var r=el.getBoundingClientRect();if(r.width<=0||r.height<=0)return false;"
                        + "return shown(el);}"
                        + "function walkClick(n){if(!n)return false;if(n.shadowRoot&&walkClick(n.shadowRoot))return true;"
                        + "var tag=(n.tagName||'').toUpperCase();var isBtn=(tag==='BUTTON'||tag==='A'||tag==='MUC-BUTTON');"
                        + "if(isBtn){var t=(n.textContent||'').trim();"
                        + "if(t.indexOf('Zurück zu Schritt')>=0)return false;"
                        + "if(t.indexOf(label)>=0&&!n.disabled&&!(n.hasAttribute&&n.hasAttribute('disabled'))"
                        + "&&n.getAttribute&&n.getAttribute('aria-disabled')!=='true'&&visible(n))"
                        + "{n.scrollIntoView({block:'center'});n.click();return true;}}"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walkClick(c[i]))return true;return false;}"
                        + "return walkClick(document.body);";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return Boolean.TRUE.equals(o);
    }


    public boolean clickButtonWithExactText(String text) {
        context.set();
        String script =
                "var label=arguments[0];"
                        + "function box(el){if(!el||el.nodeType!==1||!el.getBoundingClientRect)return false;"
                        + "var r=el.getBoundingClientRect();if(r.width<=0||r.height<=0)return false;"
                        + "var st=window.getComputedStyle(el);return st.visibility!=='hidden'&&st.display!=='none'&&st.opacity!=='0';}"
                        + "function painted(el){if(box(el))return el;var found=null;"
                        + "function w(n){if(!n||found)return;if(n.nodeType===1&&n!==el&&box(n)){found=n;return;}"
                        + "if(n.shadowRoot)w(n.shadowRoot);var c=n.children;if(c)for(var i=0;i<c.length;i++)w(c[i]);}"
                        + "w(el);return found;}"
                        + "function norm(s){return (s||'').replace(/\\s+/g,' ').trim();}"
                        + "function walkClick(n){if(!n)return false;"
                        + "var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag==='BUTTON'||tag==='A'||tag==='MUC-BUTTON'){"
                        + "if(norm(n.textContent)===label&&!n.disabled"
                        + "&&!(n.getAttribute&&n.getAttribute('aria-disabled')==='true')){"
                        + "var hit=painted(n);if(hit){hit.scrollIntoView({block:'center'});hit.click();return true;}}}"
                        + "if(n.shadowRoot&&walkClick(n.shadowRoot))return true;"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walkClick(c[i]))return true;return false;}"
                        + "return walkClick(document.body);";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, text);
        return Boolean.TRUE.equals(o);
    }


    /** Wait up to timeoutSeconds for a clickable button whose text contains label (shadow-safe), then click it. */
    public void waitForAndClickButtonContaining(String label, int timeoutSeconds) {
        context.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: waiting up to {}s for clickable button containing '{}', then clicking", timeoutSeconds, label);
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(timeoutSeconds))
                .until(d -> clickButtonContaining(label));
    }

    /**
     * After clicking Weiter on the Kontakt form: wait for the update-appointment response and for the preconfirm
     * page (electronic communication checkbox). The Kontakt Weiter is only disabled <em>after</em> click while the request runs.
     */

    public boolean deepClickButtonByAriaContains(String fragment) {
        context.set();
        String script =
                "var needle=arguments[0];"
                        + "function walk(root){"
                        + " if(!root)return null;"
                        + " var nodes=root.querySelectorAll('button');"
                        + " for(var i=0;i<nodes.length;i++){"
                        + "  var aria=nodes[i].getAttribute('aria-label')||'';"
                        + "  if(aria.indexOf(needle)>=0)return nodes[i];"
                        + " }"
                        + " var all=root.querySelectorAll('*');"
                        + " for(var j=0;j<all.length;j++){"
                        + "  if(all[j].shadowRoot){var found=walk(all[j].shadowRoot);if(found)return found;}"
                        + " }"
                        + " return null;"
                        + "}"
                        + "var button=walk(document.body);"
                        + "if(!button)return false;"
                        + "button.scrollIntoView({block:'center'});button.click();return true;";
        Object clicked = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, fragment);
        return Boolean.TRUE.equals(clicked);
    }


    public boolean deepAriaContains(String fragment) {
        context.set();
        String script =
                "var needle=arguments[0];"
                        + "function walk(root){"
                        + " if(!root)return false;"
                        + " var nodes=root.querySelectorAll('[aria-label]');"
                        + " for(var i=0;i<nodes.length;i++){"
                        + "  if((nodes[i].getAttribute('aria-label')||'').indexOf(needle)>=0)return true;"
                        + " }"
                        + " var all=root.querySelectorAll('*');"
                        + " for(var j=0;j<all.length;j++){"
                        + "  if(all[j].shadowRoot&&walk(all[j].shadowRoot))return true;"
                        + " }"
                        + " return false;"
                        + "}"
                        + "return walk(document.body);";
        return Boolean.TRUE.equals(
                ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, fragment));
    }


    public boolean deepInfoCalloutContains(String text) {
        context.set();
        String script =
                "var needle=arguments[0];"
                        + "function textOf(node){return (node.innerText||node.textContent||'');}"
                        + "function isInfoCallout(node){"
                        + " if(!node||!node.classList)return false;"
                        + " if(!node.classList.contains('m-callout')||!node.classList.contains('m-callout--default'))return false;"
                        + " return textOf(node).indexOf(needle)>=0;"
                        + "}"
                        + "function walk(root){"
                        + " if(!root)return false;"
                        + " var nodes=root.querySelectorAll('.m-callout');"
                        + " for(var i=0;i<nodes.length;i++){if(isInfoCallout(nodes[i]))return true;}"
                        + " var all=root.querySelectorAll('*');"
                        + " for(var j=0;j<all.length;j++){"
                        + "  if(all[j].shadowRoot&&walk(all[j].shadowRoot))return true;"
                        + " }"
                        + " return false;"
                        + "}"
                        + "return walk(document.body);";
        return Boolean.TRUE.equals(
                ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, text));
    }

    /**
     * Assert timeslot buttons exist for each real provider id (shared booking peers under one Ort grid).
     * Clicks Später across hour/day-parts until every provider has been seen at least once.
     */

    public boolean shadowHrefContains(String href) {
        String script =
                "var href=arguments[0];"
                        + "function walk(root){"
                        + " if(!root)return false;"
                        + " if(root.nodeType===1){"
                        + "  if((root.tagName||'').toUpperCase()==='A'){"
                        + "   var h=root.getAttribute('href')||'';"
                        + "   if(h.indexOf(href)>=0)return true;"
                        + "  }"
                        + "  if(root.shadowRoot&&walk(root.shadowRoot))return true;"
                        + " }"
                        + " var c=root.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i]))return true;"
                        + " return false;"
                        + "}"
                        + "return walk(document.body);";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, href);
        return Boolean.TRUE.equals(raw);
    }

    public void assertShadowHref(String href) {
        Assert.assertTrue(shadowHrefContains(href), "Expected link href: " + href);
    }


    public static String mapperQuote(String s) {
        if (s == null) {
            return "null";
        }
        return "\"" + s.replace("\\", "\\\\").replace("\"", "\\\"") + "\"";
    }
}
