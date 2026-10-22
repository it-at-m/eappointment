package zms.ataf.ui.pages.citizenview.steps;

import java.time.Duration;

import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import com.fasterxml.jackson.databind.JsonNode;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.utils.DriverUtil;
import config.TestConfig;
import zms.ataf.helpers.CaptchaClient;
import zms.ataf.ui.pages.citizenview.CitizenViewPageContext;
import zms.ataf.ui.pages.citizenview.support.CitizenViewJson;
import zms.ataf.ui.pages.citizenview.support.ShadowDom;

/**
 * Leistung captcha: Altcha widget wait, CaptchaService solve via the browser API path, and the
 * captcha-session callout.
 */
public final class CaptchaStep {

    private static final String DE_WEITER = "Weiter";
    private static final String DE_CAPTCHA_LABEL = "Ich bin kein Bot";
    private static final String DE_RESTART_BOOKING = "Buchung neu starten";
    private static final String DE_SESSION_TIMEOUT_HEADER = "Ihre Sitzung ist abgelaufen.";
    private static final String DE_SESSION_TIMEOUT_TEXT =
            "Seit Ihrer Bot-Überprüfung ist zu viel Zeit vergangen. Bitte starten Sie erneut mit der Terminbuchung, um die Prüfung zu aktualisieren.";

    private final CitizenViewPageContext context;
    private final ShadowDom shadow;
    private final CitizenViewJson json;
    private final int defaultWaitSeconds;

    public CaptchaStep(
            CitizenViewPageContext context,
            ShadowDom shadow,
            CitizenViewJson json,
            int defaultWaitSeconds) {
        this.context = context;
        this.shadow = shadow;
        this.json = json;
        this.defaultWaitSeconds = defaultWaitSeconds;
    }

    public void assertCaptchaSessionCalloutVisible() {
        context.set();
        shadow.waitUntilShadowContains(DE_SESSION_TIMEOUT_HEADER, defaultWaitSeconds);
        Assert.assertTrue(
                shadow.shadowDomContainsText(DE_SESSION_TIMEOUT_TEXT),
                "Captcha session callout text missing.");
        Assert.assertTrue(
                shadow.shadowDomContainsText(DE_RESTART_BOOKING),
                "Captcha session callout must offer Buchung neu starten.");
    }

    public void assertCaptchaSessionCalloutNotVisible() {
        context.set();
        Assert.assertFalse(
                shadow.shadowDomContainsText(DE_SESSION_TIMEOUT_HEADER),
                "Captcha session callout header must not remain after a fresh captcha.");
        Assert.assertFalse(
                shadow.shadowDomContainsText(DE_SESSION_TIMEOUT_TEXT),
                "Captcha session callout text must not remain after a fresh captcha.");
    }

    /**
     * ZMSKVR-506 / ZMSKVR-470 / ZMSKVR-597: label, hidden Altcha footer/logo, and muenchen.de CSS
     * tokens on Leistung before verification.
     */
    public void assertCaptchaDesign() {
        context.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert captcha design (label, footer hidden, CSS tokens)");
        shadow.waitUntilShadowContains(DE_CAPTCHA_LABEL, defaultWaitSeconds);
        Assert.assertTrue(
                shadow.shadowDomContainsText(DE_CAPTCHA_LABEL),
                "Captcha label \"Ich bin kein Bot.\" must be visible on Leistung.");

        JsonNode design =
                json.citizenJson(
                        "(function(){"
                                + "var hosts=cssAll('.altcha-captcha');"
                                + "var widgets=cssAll('altcha-widget');"
                                + "var host=hosts.length?hosts[0]:null;"
                                + "var widget=widgets.length?widgets[0]:null;"
                                + "var css={};"
                                + "if(host){var st=getComputedStyle(host);"
                                + "css.borderColor=(st.getPropertyValue('--altcha-border-color')||'').trim();"
                                + "css.borderRadius=(st.getPropertyValue('--altcha-border-radius')||'').trim();"
                                + "css.checkboxBorder=(st.getPropertyValue('--altcha-checkbox-border-color')||'').trim();"
                                + "css.checkboxWidth=(st.getPropertyValue('--altcha-checkbox-border-width')||'').trim();"
                                + "css.checkboxRadius=(st.getPropertyValue('--altcha-checkbox-border-radius')||'').trim();"
                                + "css.textColor=(st.getPropertyValue('--altcha-color-base-content')||'').trim();}"
                                + "var config=null;"
                                + "if(widget){var raw=widget.getAttribute('configuration');"
                                + "if(!raw&&widget.configuration!=null)raw=widget.configuration;"
                                + "if(typeof raw==='string'&&raw){try{config=JSON.parse(raw);}catch(e){config=null;}}"
                                + "else if(raw&&typeof raw==='object')config=raw;}"
                                + "var altchaLink=false;var checkbox=false;var altchaBrand=false;"
                                + "function scan(n){if(!n)return;"
                                + "if(n.tagName&&String(n.tagName).toLowerCase()==='a'){"
                                + "var href=((n.getAttribute&&n.getAttribute('href'))||'').toLowerCase();"
                                + "if(href.indexOf('altcha.org')>=0)altchaLink=true;}"
                                + "if(n.nodeType===3){var tv=(n.nodeValue||'').replace(/\\s+/g,' ').trim();"
                                + "if(tv==='ALTCHA'||tv.indexOf('ALTCHA')===0)altchaBrand=true;}"
                                + "if(n.tagName&&String(n.tagName).toLowerCase()==='input'"
                                + "&&((n.getAttribute&&n.getAttribute('type'))||'').toLowerCase()==='checkbox')"
                                + "checkbox=true;"
                                + "if(n.shadowRoot)scan(n.shadowRoot);"
                                + "var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)scan(c[i]);}"
                                + "if(widget)scan(widget);"
                                + "return {hostPresent:!!host,widgetPresent:!!widget,css:css,config:config,"
                                + "altchaLink:altchaLink,altchaBrand:altchaBrand,checkbox:checkbox};})()");

        Assert.assertTrue(
                design.path("hostPresent").asBoolean(false),
                "altcha-captcha host missing: " + design);
        Assert.assertTrue(
                design.path("widgetPresent").asBoolean(false),
                "altcha-widget missing: " + design);
        Assert.assertTrue(
                design.path("checkbox").asBoolean(false),
                "Captcha checkbox missing: " + design);
        Assert.assertFalse(
                design.path("altchaLink").asBoolean(true),
                "Altcha footer link must be hidden: " + design);
        Assert.assertFalse(
                design.path("altchaBrand").asBoolean(true),
                "Altcha footer brand text must be hidden: " + design);

        JsonNode config = design.path("config");
        Assert.assertFalse(config.isMissingNode() || config.isNull(), "Altcha configuration missing: " + design);
        Assert.assertTrue(config.path("hideFooter").asBoolean(false), "hideFooter must be true: " + design);
        Assert.assertTrue(config.path("hideLogo").asBoolean(false), "hideLogo must be true: " + design);

        JsonNode css = design.path("css");
        assertCssTokenContains(css, "borderColor", "#bdd4ea", "beau-blue");
        assertCssTokenContains(css, "checkboxBorder", "#005a9f", "mde-blue");
        assertCssTokenContains(css, "textColor", "#3a5368", "neutral-grey");
        Assert.assertEquals(
                normalizeCssLength(css.path("borderRadius").asText("")),
                "0",
                "Captcha border-radius: " + css);
        Assert.assertEquals(
                normalizeCssLength(css.path("checkboxRadius").asText("")),
                "0",
                "Captcha checkbox border-radius: " + css);
        Assert.assertEquals(
                normalizeCssLength(css.path("checkboxWidth").asText("")),
                "2px",
                "Captcha checkbox border-width: " + css);
    }

    public void assertLeistungWeiterDisabled() {
        context.set();
        JsonNode state = json.citizenJson("btnState(document.body,'" + DE_WEITER + "')");
        Assert.assertTrue(state.path("present").asBoolean(false), "Weiter missing on Leistung: " + state);
        Assert.assertTrue(
                state.path("disabled").asBoolean(false),
                "Weiter must stay disabled until captcha verification: " + state);
    }

    public void assertLeistungWeiterEnabled() {
        context.set();
        JsonNode state = json.citizenJson("btnState(document.body,'" + DE_WEITER + "')");
        Assert.assertTrue(state.path("present").asBoolean(false), "Weiter missing on Leistung: " + state);
        Assert.assertFalse(
                state.path("disabled").asBoolean(true),
                "Weiter should be enabled after captcha verification: " + state);
    }

    /**
     * The load-error line is what Leistung shows until captcha details return and the widget mounts.
     * Weiter stays disabled until captcha verification finishes.
     *
     * <p>Altcha's in-browser proof-of-work needs WebCrypto ({@code isSecureContext}). ATAF opens
     * {@code http://citizenview}, which Chrome often still solves but Firefox and Edge do not. Solve
     * through CaptchaService (same JWT TTL as a real widget verify) and complete Altcha's
     * {@code serververification} path so session-expiry callouts still arm on the token.
     *
     * <p>Solve via the gateway URL the browser uses ({@link TestConfig#getCitizenApiBrowserBaseUri()}),
     * not the direct zms-web citizen API. Captcha JWTs bind to client IP; a token minted on the
     * direct path is rejected as {@code captchaInvalid} on available-calendar.
     */
    public void waitUntilCaptchaCheckFinished(int widgetTimeoutSeconds, int solveTimeoutSeconds) {
        context.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: waiting up to {}s for the captcha widget to load", widgetTimeoutSeconds);
        shadow.waitUntilShadowContains(DE_CAPTCHA_LABEL, widgetTimeoutSeconds);
        clickCaptchaCheckboxIfUnchecked();
        String browserApi = TestConfig.getCitizenApiBrowserBaseUri();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: solving captcha via browser API path {}", browserApi);
        String token = CaptchaClient.solve(browserApi);
        if (!injectCaptchaServerVerification(token)) {
            throw new IllegalStateException(
                    "altcha-widget not found after captcha label appeared; cannot inject verification");
        }
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: injected CaptchaService JWT; waiting up to {}s for Weiter",
                        solveTimeoutSeconds);
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(solveTimeoutSeconds))
                .pollingEvery(Duration.ofMillis(500))
                .until(driver -> enabledVisibleButtonContains(DE_WEITER));
    }

    /** Altcha starts only after the checkbox is clicked. Repeated clicks are skipped once it is checked. */
    private boolean clickCaptchaCheckboxIfUnchecked() {
        context.set();
        String script =
                "function walk(n, fn){if(!n)return false;if(fn(n))return true;"
                        + "if(n.shadowRoot&&walk(n.shadowRoot, fn))return true;"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i], fn))return true;return false;}"
                        + "var clicked=false;"
                        + "walk(document.body, function(n){"
                        + "if(!n.querySelectorAll)return false;"
                        + "var text=(n.textContent||'');"
                        + "if(text.indexOf('Ich bin kein Bot')<0)return false;"
                        + "var inputs=n.querySelectorAll('input[type=checkbox]');"
                        + "for(var i=0;i<inputs.length;i++){"
                        + "var input=inputs[i];"
                        + "if(input.checked){clicked=true;return true;}"
                        + "input.click();clicked=true;return true;}"
                        + "return false;});"
                        + "return clicked;";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return Boolean.TRUE.equals(o);
    }

    /**
     * Completes the same Vue path as a successful Altcha {@code serververification} event
     * ({@code validationResult=true} + captcha JWT).
     */
    private boolean injectCaptchaServerVerification(String token) {
        context.set();
        String script =
                "function walk(n, fn){if(!n)return false;if(fn(n))return true;"
                        + "if(n.shadowRoot&&walk(n.shadowRoot, fn))return true;"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i], fn))return true;return false;}"
                        + "var widget=null;"
                        + "walk(document.body, function(n){"
                        + "if(n.tagName&&String(n.tagName).toLowerCase()==='altcha-widget'){widget=n;return true;}"
                        + "return false;});"
                        + "if(!widget)return false;"
                        + "widget.dispatchEvent(new CustomEvent('serververification',{detail:{"
                        + "meta:{success:true},data:{valid:true},token:arguments[0]}}));"
                        + "return true;";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, token);
        return Boolean.TRUE.equals(o);
    }

    private boolean enabledVisibleButtonContains(String label) {
        context.set();
        String esc = label.replace("\\", "\\\\").replace("'", "\\'");
        String script =
                "var label='" + esc + "';"
                        + "function visible(el){if(!el||!el.getBoundingClientRect)return false;"
                        + "var r=el.getBoundingClientRect();if(r.width<=0||r.height<=0)return false;"
                        + "var st=window.getComputedStyle(el);return st.visibility!=='hidden'&&st.display!=='none'&&st.opacity!=='0';}"
                        + "function walk(n){if(!n)return false;if(n.shadowRoot&&walk(n.shadowRoot))return true;"
                        + "var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag==='BUTTON'||tag==='A'||tag==='MUC-BUTTON'){"
                        + "var t=(n.innerText||n.textContent||'').trim();"
                        + "if(t.indexOf('Zurück zu Schritt')<0&&t.indexOf(label)>=0&&visible(n)"
                        + "&&!n.disabled&&!(n.hasAttribute&&n.hasAttribute('disabled'))"
                        + "&&n.getAttribute&&n.getAttribute('aria-disabled')!=='true')return true;}"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i]))return true;return false;}"
                        + "return walk(document.body);";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return Boolean.TRUE.equals(o);
    }

    private static void assertCssTokenContains(JsonNode css, String field, String hex, String token) {
        String value = css.path(field).asText("").toLowerCase();
        Assert.assertTrue(
                value.contains(hex.toLowerCase()) || value.contains(token.toLowerCase()),
                "Captcha CSS " + field + " should reference " + hex + " or " + token + ": " + css);
    }

    /** Strip spaces so {@code 0px} / {@code 0} / {@code 2px} compare cleanly. */
    private static String normalizeCssLength(String raw) {
        String v = raw == null ? "" : raw.trim().toLowerCase().replace(" ", "");
        if ("0px".equals(v) || "0rem".equals(v) || "0em".equals(v)) {
            return "0";
        }
        return v;
    }
}
