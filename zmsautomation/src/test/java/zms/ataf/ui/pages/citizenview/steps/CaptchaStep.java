package zms.ataf.ui.pages.citizenview.steps;

import java.time.Duration;

import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.utils.DriverUtil;
import config.TestConfig;
import zms.ataf.helpers.CaptchaClient;
import zms.ataf.ui.pages.citizenview.CitizenViewPageContext;
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
    private final int defaultWaitSeconds;

    public CaptchaStep(CitizenViewPageContext context, ShadowDom shadow, int defaultWaitSeconds) {
        this.context = context;
        this.shadow = shadow;
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
}
