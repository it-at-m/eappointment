package zms.ataf.ui.pages.citizenview;

import java.net.URI;
import java.nio.charset.StandardCharsets;
import java.time.Duration;
import java.util.Base64;
import java.util.HashSet;
import java.util.Objects;
import java.util.Set;
import java.util.function.BooleanSupplier;

import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;

import ataf.core.helpers.TestDataHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.web.model.LocatorType;
import ataf.web.pages.BasePage;
import ataf.web.utils.DriverUtil;
import zms.ataf.helpers.RandomNameHelper;
import zms.ataf.rest.dto.zmscitizenapi.ThinnedProcess;

/**
 * zmscitizenview booking flow: all meaningful DOM lives under Vue custom elements / shadow roots.
 * Interactions use JS that searches open shadow trees (deep query / text walk).
 */
public class CitizenViewPage extends BasePage {

    /** Same key as zmscitizenview LOCALSTORAGE_PARAM_APPOINTMENT_DATA */
    public static final String LOCALSTORAGE_APPOINTMENT_KEY = "lhm-appointment-data";

    private static final String DE_WEITER = "Weiter";
    private static final String DE_RESERVE = "Termin reservieren";

    /** German invalid jump-in callout ({@code de-DE.json}). */
    public static final String DE_INVALID_JUMPIN_HEADER = "Diese Ansicht kann nicht geladen werden.";

    public static final String DE_INVALID_JUMPIN_TEXT =
            "Der Link zu dieser Seite ist leider fehlerhaft. Starten Sie die Terminvereinbarung neu";

    private static final String EN_INVALID_JUMPIN_HEADER = "This view cannot be loaded.";
    private static final String EN_INVALID_JUMPIN_TEXT =
            "The link to this page is unfortunately incorrect";

    private final CitizenViewPageContext CONTEXT;

    /**
     * Last office chosen on the Ort step; used to scroll {@code #timeslot-grid-provider-{id}} into view before
     * {@code @AfterStep} screenshots.
     */
    private int lastSlotBookingOfficeId = -1;

    public CitizenViewPage(RemoteWebDriver driver) {
        super(driver);
        CONTEXT = new CitizenViewPageContext(driver);
    }

    public CitizenViewPageContext getContext() {
        return CONTEXT;
    }

    public void navigateToPage() {
        CONTEXT.navigateToPage();
    }

    public void navigateWithJumpIn(String serviceId, String locationId) {
        CONTEXT.navigateWithJumpIn(serviceId, locationId);
    }

    /**
     * Generic helper for asynchronous transitions after actions such as Weiter / confirm links.
     * Waits in four windows: 5s, then +10s, then +15s, then +30s (total 60s) while polling {@code condition}.
     */
    private void waitWithThreeWindows(BooleanSupplier condition, String context) {
        long deadlineFirst = System.currentTimeMillis() + 5000L;
        while (!condition.getAsBoolean() && System.currentTimeMillis() < deadlineFirst) {
            try {
                Thread.sleep(250L);
            } catch (InterruptedException ie) {
                Thread.currentThread().interrupt();
                return;
            }
        }
        if (condition.getAsBoolean()) {
            return;
        }
        ScenarioLogManager.getLogger()
                .warn("{} not visible after first 5s window; retrying for additional 10s", context);
        long deadlineSecond = System.currentTimeMillis() + 10000L;
        while (!condition.getAsBoolean() && System.currentTimeMillis() < deadlineSecond) {
            try {
                Thread.sleep(250L);
            } catch (InterruptedException ie) {
                Thread.currentThread().interrupt();
                return;
            }
        }
        if (condition.getAsBoolean()) {
            return;
        }
        ScenarioLogManager.getLogger()
                .warn("{} not visible after first 15s window; retrying for additional 15s", context);
        long deadlineThird = System.currentTimeMillis() + 15000L;
        while (!condition.getAsBoolean() && System.currentTimeMillis() < deadlineThird) {
            try {
                Thread.sleep(250L);
            } catch (InterruptedException ie) {
                Thread.currentThread().interrupt();
                return;
            }
        }
        if (condition.getAsBoolean()) {
            return;
        }
        ScenarioLogManager.getLogger()
                .warn("{} still not visible after 30s; retrying for final 30s window", context);
        long deadlineFourth = System.currentTimeMillis() + 30000L;
        while (!condition.getAsBoolean() && System.currentTimeMillis() < deadlineFourth) {
            try {
                Thread.sleep(250L);
            } catch (InterruptedException ie) {
                Thread.currentThread().interrupt();
                return;
            }
        }
    }

    public void assertServiceFinderHeadingVisible() {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Checking that the zmscitizenview Service Finder is visible on the start page.");

        boolean hostVisible = isWebElementVisible(
                DEFAULT_EXPLICIT_WAIT_TIME,
                "//zms-appointment-i18n-host",
                LocatorType.XPATH,
                true);

        Assert.assertTrue(
                hostVisible,
                "Root element <zms-appointment-i18n-host> is not visible on the zmscitizenview start page.");

        RemoteWebDriver driver = DriverUtil.getDriver();
        String script =
                "function walk(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=walk(n.shadowRoot);"
                        + "var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=walk(c[i]);return s;}"
                        + "var t=walk(document.body);"
                        + "return t.indexOf('Leistung')>=0&&t.indexOf('Bürgerservice-Suche')>=0"
                        + "&&t.indexOf('Häufig gesuchte Leistungen')>=0;";

        Boolean textsVisible =
                new WebDriverWait(driver, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                        .until(
                                d ->
                                        Boolean.TRUE.equals(
                                                ((JavascriptExecutor) d).executeScript(script)));
        Assert.assertTrue(
                textsVisible,
                "Service Finder copy (Leistung / Bürgerservice-Suche / Häufig gesuchte Leistungen) not found"
                        + " in page+shadow DOM within timeout.");
        ScenarioLogManager.getLogger().info("Service Finder is visible on the start page.");
    }

    /** True if substring appears anywhere in document + shadow DOM text. */
    public boolean shadowDomContainsText(String substring) {
        CONTEXT.set();
        String esc = substring.replace("\\", "\\\\").replace("'", "\\'");
        String script =
                "var sub='" + esc + "';function walk(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=walk(n.shadowRoot);var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=walk(c[i]);return s;}"
                        + "return walk(document.body).indexOf(sub)>=0;";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return Boolean.TRUE.equals(o);
    }

    public void waitUntilShadowContains(String substring, int seconds) {
        CONTEXT.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(seconds))
                .until(d -> shadowDomContainsText(substring));
    }

    /**
     * True once the given service label appears somewhere in the DOM/shadow DOM
     * <em>outside</em> the static "Häufig gesuchte Leistungen" quick-link list.
     * This is a proxy for "offices-and-services have loaded and the label is
     * available in API-backed UI (e.g. select options)".
     */
    private boolean serviceLabelReadyForSelection(String serviceLabel) {
        CONTEXT.set();
        String esc = serviceLabel.replace("\\", "\\\\").replace("'", "\\'");
        String script =
                "var label='" + esc + "';"
                        + "function norm(t){return (t||'').replace(/\\s+/g,' ').trim();}"
                        + "function insideQuick(el){"
                        + "  while(el){"
                        + "    if(el.classList&&el.classList.contains('m-linklist-inline__list'))return true;"
                        + "    var root=el.getRootNode&&el.getRootNode();"
                        + "    if(root&&root.host){el=root.host;}else{el=el.parentNode;}"
                        + "  }"
                        + "  return false;"
                        + "}"
                        + "function has(root){"
                        + "  if(!root)return false;"
                        + "  var all=root.querySelectorAll('*');"
                        + "  for(var i=0;i<all.length;i++){"
                        + "    var el=all[i];"
                        + "    if(insideQuick(el))continue;"
                        + "    var txt=norm(el.textContent);"
                        + "    if(txt&&txt.indexOf(label)>=0)return true;"
                        + "    if(el.shadowRoot&&has(el.shadowRoot))return true;"
                        + "  }"
                        + "  return false;"
                        + "}"
                        + "return has(document.body);";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return Boolean.TRUE.equals(o);
    }

    /** Wait until the service label is present outside the quick-link list (API-backed UI ready). */
    private void waitUntilServiceLabelReadyForSelection(String serviceLabel, int seconds) {
        CONTEXT.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(seconds))
                .until(d -> serviceLabelReadyForSelection(serviceLabel));
    }

    /**
     * Assert that the UI shows an estimated duration with the expected number of minutes. This is a generic shadow-DOM
     * text assertion used for the service combination step, selected-appointment callout, and booking summaries.
     */
    public void assertEstimatedDurationMinutes(int minutes, String context) {
        CONTEXT.set();
        String minutesText = minutes + " Minuten";
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: checking estimated duration = {} in {}", minutesText, context);
        // Some views (especially after opening deep links) may need a brief moment to render
        try {
            Thread.sleep(3000L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
        waitUntilShadowContains("Voraussichtliche Termindauer", DEFAULT_EXPLICIT_WAIT_TIME);
        Assert.assertTrue(
                shadowDomContainsText("Voraussichtliche Termindauer"),
                "Expected 'Voraussichtliche Termindauer' text to be visible in " + context);
        Assert.assertTrue(
                shadowDomContainsText(minutesText),
                "Expected estimated duration '" + minutesText + "' to be visible in " + context);
    }

    /**
     * Increase the quantity of a subservice by clicking the "+" control on its counter, resolving the subservice by
     * visible name. If the subservice is not yet visible (hidden behind "Alle Leistungen anzeigen"), this method will
     * first click that button once and retry.
     */
    public void addSubserviceByName(String subserviceLabel, int quantity) {
        CONTEXT.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: add subservice '{}' quantity {}", subserviceLabel, quantity);
        // Give the combination list a brief moment to settle (especially after jump-in or service selection).
        try {
            Thread.sleep(500L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
        for (int i = 0; i < quantity; i++) {
            boolean ok = deepAddSubserviceOnceByName(subserviceLabel);
            Assert.assertTrue(
                    ok, "Could not increase subservice counter for '" + subserviceLabel + "' (iteration " + (i + 1) + ")");
            // Small delay after each click so Vue state and duration can update before the next assertion.
            try {
                Thread.sleep(500L);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
            }
        }
    }

    /**
     * JS helper: try to click the "+" button for a subservice counter with a matching label once. Returns true on
     * success. If the subservice is not found, it will attempt to click "Alle Leistungen anzeigen" once and search
     * again.
     */
    private boolean deepAddSubserviceOnceByName(String subserviceLabel) {
        CONTEXT.set();
        String esc = subserviceLabel.replace("\\", "\\\\").replace("'", "\\'");
        String script =
                "var label='" + esc + "';"
                        + "function norm(t){return (t||'').replace(/\\s+/g,' ').trim();}"
                        + "function key(t){return norm(t).replace(/-/g,'').toLowerCase();}"
                        + "var labelKey = key(label);"
                        + "function findPlusButtonDeep(root){"
                        + "  if(!root)return null;"
                        + "  if(root.nodeType===1 && root.tagName==='BUTTON'){"
                        + "    var aria=root.getAttribute('aria-label')||'';"
                        + "    if(aria && !root.disabled){"
                        + "      var lower=aria.toLowerCase();"
                        + "      if(lower.indexOf('reduzier')>=0){}"
                        + "      else {"
                        + "        var aKey=key(aria);"
                        + "        if(aKey.indexOf(labelKey)>=0)return root;"
                        + "      }"
                        + "    }"
                        + "  }"
                        + "  if(root.shadowRoot){"
                        + "    var r=findPlusButtonDeep(root.shadowRoot);"
                        + "    if(r)return r;"
                        + "  }"
                        + "  var kids=root.children||[];"
                        + "  for(var i=0;i<kids.length;i++){"
                        + "    var r2=findPlusButtonDeep(kids[i]);"
                        + "    if(r2)return r2;"
                        + "  }"
                        + "  return null;"
                        + "}"
                        + "function findShowAllDeep(root){"
                        + "  if(!root)return null;"
                        + "  if(root.nodeType===1){"
                        + "    var tag=(root.tagName||'').toUpperCase();"
                        + "    if((tag==='BUTTON'||tag==='MUC-BUTTON')){"
                        + "      var txt=norm(root.textContent||'');"
                        + "      if(txt.indexOf('Alle Leistungen anzeigen')>=0 && !root.disabled)return root;"
                        + "    }"
                        + "    if(root.shadowRoot){"
                        + "      var r=findShowAllDeep(root.shadowRoot);"
                        + "      if(r)return r;"
                        + "    }"
                        + "    var kids=root.children||[];"
                        + "    for(var i=0;i<kids.length;i++){"
                        + "      var r2=findShowAllDeep(kids[i]);"
                        + "      if(r2)return r2;"
                        + "    }"
                        + "  }"
                        + "  return null;"
                        + "}"
                        + "function clickPlusOnButton(btn){"
                        + "  if(!btn)return false;"
                        + "  if(btn.disabled)return false;"
                        + "  btn.scrollIntoView({block:'center'});"
                        + "  try{"
                        + "    btn.style.outline='4px solid #ffbf00';"
                        + "    btn.style.outlineOffset='3px';"
                        + "    btn.style.backgroundColor='rgba(255,191,0,0.25)';"
                        + "  }catch(e){}"
                        + "  btn.click();"
                        + "  return true;"
                        + "}"
                        + "var btn=findPlusButtonDeep(document.body);"
                        + "if(!btn){"
                        + "  var showAll=findShowAllDeep(document.body);"
                        + "  if(showAll){showAll.scrollIntoView({block:'center'});showAll.click();}"
                        + "  btn=findPlusButtonDeep(document.body);"
                        + "}"
                        + "return clickPlusOnButton(btn);";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return Boolean.TRUE.equals(o);
    }

    public void assertShadowContains(String substring, String message) {
        waitUntilShadowContains(substring, DEFAULT_EXPLICIT_WAIT_TIME);
        Assert.assertTrue(shadowDomContainsText(substring), message);
    }

    /**
     * Find first element matching CSS in document or any shadow root; click via JS.
     */
    public boolean deepClick(String cssSelector) {
        CONTEXT.set();
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
    public boolean deepElementExists(String cssSelector) {
        CONTEXT.set();
        String script =
                "var sel=arguments[0];function find(root){if(!root)return null;var q=root.querySelector(sel);if(q)return q;"
                        + "var all=root.querySelectorAll('*');for(var i=0;i<all.length;i++){if(all[i].shadowRoot){var f=find(all[i].shadowRoot);if(f)return f;}}return null;}"
                        + "return !!(document.querySelector(sel)||find(document.body));";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, cssSelector);
        return Boolean.TRUE.equals(o);
    }

    public void assertInvalidJumpinLinkCalloutVisible() {
        CONTEXT.set();
        int sec = Math.min(25, DEFAULT_EXPLICIT_WAIT_TIME);
        long deadline = System.currentTimeMillis() + sec * 1000L;
        while (System.currentTimeMillis() < deadline) {
            if (shadowDomContainsText(DE_INVALID_JUMPIN_HEADER) && shadowDomContainsText(DE_INVALID_JUMPIN_TEXT)) {
                return;
            }
            if (shadowDomContainsText(EN_INVALID_JUMPIN_HEADER) && shadowDomContainsText(EN_INVALID_JUMPIN_TEXT)) {
                return;
            }
            try {
                Thread.sleep(300L);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
                break;
            }
        }
        Assert.assertTrue(
                (shadowDomContainsText(DE_INVALID_JUMPIN_HEADER) || shadowDomContainsText(EN_INVALID_JUMPIN_HEADER))
                        && (shadowDomContainsText(DE_INVALID_JUMPIN_TEXT) || shadowDomContainsText(EN_INVALID_JUMPIN_TEXT)),
                "Invalid jump-in callout not found (de or en). Expected for invalid service–office pairs only.");
    }

    public void waitUntilDeepElementExists(String cssSelector, int seconds) {
        CONTEXT.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(seconds))
                .until(d -> deepElementExists(cssSelector));
    }

    /**
     * Ort step: either multi-provider ({@code #checkbox-provider-{id}}) or single-provider teaser
     * ({@code h3#provider-{id}}) — see ProviderSelection.vue.
     */
    public boolean ortStepShowsProvider(int officeId) {
        CONTEXT.set();
        return deepElementExists("#checkbox-provider-" + officeId)
                || deepOrtSingleProviderTeaserPresent(officeId);
    }

    /** Single-provider layout: teaser headline {@code #provider-{id}} under Ort (no checkboxes). */
    private boolean deepOrtSingleProviderTeaserPresent(int officeId) {
        CONTEXT.set();
        String script =
                "var id='provider-'+arguments[0];function has(root){if(!root)return false;"
                        + "var h=root.querySelector('h3#'+id+'.m-teaser-contained-contact__headline');"
                        + "if(h)return true;var all=root.querySelectorAll('*');"
                        + "for(var i=0;i<all.length;i++)if(all[i].shadowRoot&&has(all[i].shadowRoot))return true;return false;}"
                        + "return has(document.body);";
        return Boolean.TRUE.equals(
                ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, officeId));
    }

    public void assertProviderCheckboxPresent(int officeId) {
        CONTEXT.set();
        waitUntilOrtStepShowsProvider(officeId, DEFAULT_EXPLICIT_WAIT_TIME);
        logOrtProviderResolution(officeId);
        Assert.assertTrue(ortStepShowsProvider(officeId), "Ort must show provider " + officeId);
    }

    public void assertProviderCheckboxAbsent(int officeId) {
        CONTEXT.set();
        Assert.assertFalse(
                deepElementExists("#checkbox-provider-" + officeId),
                "Provider checkbox for office " + officeId + " must not appear for this jump-in/service.");
        Assert.assertFalse(
                deepOrtSingleProviderTeaserPresent(officeId),
                "Single-provider Ort teaser for office " + officeId + " must not appear.");
    }

    /**
     * Reserve / preconfirm / confirm screens expose {@code <p id="provider-{officeId}">…</p>} (summary).
     * Asserts that block is present so the appointment is tied to the correct calendar/office.
     */
    public void assertProviderSummaryVisible(int officeId) {
        CONTEXT.set();
        String sel = "#provider-" + officeId;
        waitWithThreeWindows(() -> deepElementExists(sel), "Provider summary " + sel);
        Assert.assertTrue(deepElementExists(sel), "Expected booking summary provider block: " + sel);
        Assert.assertTrue(
                shadowDomContainsText("Bürgerbüro Ruppertstraße"),
                "Expected standort label near provider-" + officeId);
    }

    /** On Passkalender jump-in, only Pass services should be combinable (names from API). */
    public void assertPassOnlyCombinationServicesVisible() {
        CONTEXT.set();
        waitUntilShadowContains("Reisepass", DEFAULT_EXPLICIT_WAIT_TIME);
        Assert.assertTrue(shadowDomContainsText("Reisepass"), "Expected Reisepass on Pass-only combination step");
        Assert.assertTrue(shadowDomContainsText("Personalausweis"), "Expected Personalausweis (Pass family)");
        Assert.assertTrue(
                shadowDomContainsText("Vorläufiger Reisepass")
                        || shadowDomContainsText("vorläufiger Reisepass")
                        || shadowDomContainsText("Vorläufiger"),
                "Expected Vorläufiger Reisepass (or label) on Pass-only step");
    }

    /**
     * Set value on input/textarea. {@code muc-input} / {@code muc-text-area} use host ids ({@code firstname},
     * {@code mailaddress}) with the real control inside <strong>shadow DOM</strong>; also tries {@code input-*} ids.
     * Dispatches {@code InputEvent} so Vue v-model updates (plain {@code value=} is not enough).
     */
    public boolean deepSetById(String id, String value) {
        CONTEXT.set();
        String script =
                "var want=arguments[0],v=arguments[1]==null?'':String(arguments[1]);"
                        + "var ids=[];ids.push(want);if(want.indexOf('input-')===0)ids.push(want.slice(6));else ids.push('input-'+want);"
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

    /** Click first button whose visible text includes label (shadow-safe). Includes BUTTON, A, and MUC-BUTTON (modal confirm/cancel). */
    public boolean clickButtonContaining(String text) {
        CONTEXT.set();
        String esc = text.replace("\\", "\\\\").replace("'", "\\'");
        String script =
                "var label='" + esc + "';function walkClick(n){if(!n)return false;if(n.shadowRoot&&walkClick(n.shadowRoot))return true;"
                        + "var tag=(n.tagName||'').toUpperCase();var isBtn=(tag==='BUTTON'||tag==='A'||tag==='MUC-BUTTON');"
                        + "if(isBtn){var t=(n.textContent||'').trim();if(t.indexOf(label)>=0&&!n.disabled){n.scrollIntoView({block:'center'});n.click();return true;}}"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walkClick(c[i]))return true;return false;}"
                        + "return walkClick(document.body);";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return Boolean.TRUE.equals(o);
    }

    public void clickWeiter() {
        clickWeiter(DEFAULT_EXPLICIT_WAIT_TIME);
    }

    /** Wait up to timeoutSeconds for a clickable Weiter, then click it (e.g. use 30 on Kontakt step). */
    public void clickWeiter(int timeoutSeconds) {
        waitForAndClickButtonContaining(DE_WEITER, timeoutSeconds);
    }

    /** Wait up to timeoutSeconds for a clickable button whose text contains label (shadow-safe), then click it. */
    public void waitForAndClickButtonContaining(String label, int timeoutSeconds) {
        CONTEXT.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: waiting up to {}s for clickable button containing '{}', then clicking", timeoutSeconds, label);
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(timeoutSeconds))
                .until(d -> clickButtonContaining(label));
    }

    /**
     * After clicking Weiter on the Kontakt form: wait for the update-appointment response and for the preconfirm
     * page (privacy checkboxes). The Kontakt Weiter is only disabled <em>after</em> click while the request runs.
     */
    public void waitForPreconfirmPageAfterUpdate() {
        CONTEXT.set();
        String sel = "#checkbox-privacy-policy";
        waitWithThreeWindows(() -> deepElementExists(sel), "Preconfirm page " + sel);
        Assert.assertTrue(
                deepElementExists(sel),
                "Preconfirm page (privacy checkbox " + sel + ") not visible after Kontakt Weiter with retries.");
        ScenarioLogManager.getLogger().info("zmscitizenview: preconfirm page visible");
    }

    /** Jump-in: combination step shows Weiter + optional counters. */
    public void assertCombinationStepVisible() {
        CONTEXT.set();
        // Combination (Ort/Zeit) step is usually identified by the "Kombinierbare Leistungen" heading.
        // For flows without combinable services (e.g. Abholung-only), this heading is absent; in those
        // cases we fall back to the presence of the "Leistung wechseln" back button as the indicator
        // that the Leistung step has been replaced by the combination step.
        String deHeading = "Kombinierbare Leistungen";
        String enHeading = "Combinable services";
        String backButton = "Leistung wechseln";
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(d -> shadowDomContainsText(deHeading)
                        || shadowDomContainsText(enHeading)
                        || shadowDomContainsText(backButton));
        Assert.assertTrue(
                shadowDomContainsText(deHeading)
                        || shadowDomContainsText(enHeading)
                        || shadowDomContainsText(backButton),
                "Expected combination step after service selection or jump-in "
                        + "(Kombinierbare Leistungen / Combinable services heading, or Leistung wechseln back button).");
    }

    /** Full entry: select service via \"Häufig gesuchte Leistungen\" link and navigate to combination step. */
    public void selectServiceByLabel(String serviceLabel) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Service Finder: searching for and clicking service '{}'", serviceLabel);
        // Ensure the Service Finder step is visible first (same heuristic as assertServiceFinderHeadingVisible).
        waitUntilShadowContains("Bürgerservice-Suche", DEFAULT_EXPLICIT_WAIT_TIME);
        // Wait until the desired service label is present in API-backed UI (e.g. select options),
        // not just in the static quick-link list. This ensures offices-and-services have loaded.
        ScenarioLogManager.getLogger()
                .info("Service Finder: waiting for label '{}' to be ready in API-backed UI (up to 20s)", serviceLabel);
        waitUntilServiceLabelReadyForSelection(serviceLabel, 20);
        // Click only the \"Häufig gesuchte Leistungen\" quick link (not the search dropdown).
        // Simulate a full user click: focus, pointer events, then click (so Vue @click fires).
        String esc = serviceLabel.replace("\\", "\\\\").replace("'", "\\'");
        String js =
                "var label='" + esc + "';"
                        + "function matchText(t){"
                        + "  if(!t)return false;"
                        + "  var s=String(t).replace(/\\s+/g,' ').trim();"
                        + "  return s===label || s.indexOf(label)>=0;"
                        + "}"
                        + "function findQuickLinkInRoot(root){"
                        + "  if(!root)return null;"
                        + "  var lists=root.querySelectorAll('.m-linklist-inline__list');"
                        + "  for(var i=0;i<lists.length;i++){"
                        + "    var as=lists[i].querySelectorAll('a');"
                        + "    for(var j=0;j<as.length;j++){"
                        + "      var el=as[j];"
                        + "      var t=(el.textContent||'');"
                        + "      if(matchText(t))return el;"
                        + "    }"
                        + "  }"
                        + "  return null;"
                        + "}"
                        + "function findQuickLinkDeep(root){"
                        + "  if(!root)return null;"
                        + "  var link=findQuickLinkInRoot(root);"
                        + "  if(link)return link;"
                        + "  var all=root.querySelectorAll('*');"
                        + "  for(var k=0;k<all.length;k++){"
                        + "    if(all[k].shadowRoot){"
                        + "      var r=findQuickLinkDeep(all[k].shadowRoot);"
                        + "      if(r)return r;"
                        + "    }"
                        + "  }"
                        + "  return null;"
                        + "}"
                        + "var link=findQuickLinkDeep(document.documentElement)||findQuickLinkDeep(document.body);"
                        + "if(link){"
                        + "  link.scrollIntoView({block:'center'});"
                        + "  link.focus();"
                        + "  try{"
                        + "    link.style.outline='4px solid #ffbf00';"
                        + "    link.style.outlineOffset='3px';"
                        + "    link.style.backgroundColor='rgba(255,191,0,0.25)';"
                        + "  }catch(e){}"
                        + "  var r=link.getBoundingClientRect();"
                        + "  var x=r.left+r.width/2; var y=r.top+r.height/2;"
                        + "  var opts={bubbles:true,cancelable:true,view:window,clientX:x,clientY:y};"
                        + "  link.dispatchEvent(new MouseEvent('mousedown',opts));"
                        + "  link.dispatchEvent(new MouseEvent('mouseup',opts));"
                        + "  link.dispatchEvent(new MouseEvent('click',opts));"
                        + "  return true;"
                        + "}return false;";
        Object clicked = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(js);
        if (Boolean.TRUE.equals(clicked)) {
            ScenarioLogManager.getLogger().info("Service Finder: found and clicked link for '{}'", serviceLabel);
        } else {
            ScenarioLogManager.getLogger().warn("Service Finder: did not find or click link for '{}'", serviceLabel);
        }
        Assert.assertTrue(
                Boolean.TRUE.equals(clicked),
                "Service Finder: could not find or click link for service '" + serviceLabel + "'");
        // Clicking a service link auto-advances to the combination (Ort/Zeit) step; no Weiter on this page.
        try {
            Thread.sleep(2000L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
        try {
            assertCombinationStepVisible();
        } catch (TimeoutException first) {
            // In rare cases the first click may race with offices-and-services loading.
            // Retry once if the combination heading did not appear yet.
            ScenarioLogManager.getLogger()
                    .warn("Service Finder: combination step did not appear after first click on '{}', retrying once",
                            serviceLabel);
            try {
                Thread.sleep(1000L);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
            }
            Boolean retried =
                    (Boolean)
                            ((JavascriptExecutor) DriverUtil.getDriver())
                                    .executeScript(js);
            ScenarioLogManager.getLogger()
                    .info("Service Finder: retry click on '{}' success={}", serviceLabel, retried);
            assertCombinationStepVisible();
        }
    }

    /**
     * Jump-in can pre-select the only provider; clicking again toggles off. True if that office is already on.
     */
    public boolean deepProviderCheckboxChecked(int officeId) {
        CONTEXT.set();
        String script =
                "var id=arguments[0];function find(root,id){if(!root)return null;"
                        + "var q=root.querySelector('#checkbox-provider-'+id);if(q)return q;"
                        + "var all=root.querySelectorAll('*');for(var i=0;i<all.length;i++){if(all[i].shadowRoot){var f=find(all[i].shadowRoot,id);if(f)return f;}}return null;}"
                        + "var e=document.querySelector('#checkbox-provider-'+id)||find(document.body,id);if(!e)return false;"
                        + "if(e.tagName==='INPUT'&&e.type==='checkbox')return !!e.checked;"
                        + "if(e.shadowRoot){var inp=e.shadowRoot.querySelector('input[type=checkbox]');if(inp)return !!inp.checked;}"
                        + "var inp2=e.querySelector('input[type=checkbox]');if(inp2)return !!inp2.checked;"
                        + "return e.getAttribute('aria-checked')==='true'||e.classList.contains('is-selected');";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, officeId);
        return Boolean.TRUE.equals(o);
    }

    public void selectOfficeById(int officeId) {
        CONTEXT.set();
        logOrtProviderResolution(officeId);
        if (deepElementExists("#checkbox-provider-" + officeId)) {
            if (deepProviderCheckboxChecked(officeId)) {
                ScenarioLogManager.getLogger()
                        .info(
                                "zmscitizenview: Ort checkbox provider {} already checked (jump-in); skip click",
                                officeId);
            } else {
                deepClickRequired("#checkbox-provider-" + officeId);
                ScenarioLogManager.getLogger()
                        .info("zmscitizenview: clicked Ort checkbox provider {}", officeId);
            }
        } else if (deepOrtSingleProviderTeaserPresent(officeId)) {
            ScenarioLogManager.getLogger()
                    .info(
                            "zmscitizenview: Ort single-provider teaser already selected provider {} (no checkbox)",
                            officeId);
        } else {
            Assert.fail("Ort: no checkbox and no single-provider teaser for provider " + officeId);
        }
        lastSlotBookingOfficeId = officeId;
    }

    /**
     * Scrolls the provider's time slot grid into the viewport center so {@code @AfterStep} full-page screenshots show
     * the slot area (not only the calendar above the fold). Safe to call after Ort selection when slots exist.
     */
    public void scrollTimeSlotGridIntoViewForScreenshots() {
        CONTEXT.set();
        String script;
        if (lastSlotBookingOfficeId < 0) {
            script =
                    "function fg(r){if(!r)return null;var q=r.querySelector('[id^=\"timeslot-grid-provider-\"]');"
                            + "if(q)return q;var a=r.querySelectorAll('*');for(var i=0;i<a.length;i++)"
                            + "if(a[i].shadowRoot){var x=fg(a[i].shadowRoot);if(x)return x;}return null;}"
                            + "var g=fg(document.body);if(g){g.scrollIntoView({block:'center'});window.scrollBy(0,72);}"
                            + "return true;";
            ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
            return;
        }
        script =
                "var oid=arguments[0];"
                        + "function findGrid(root,id){if(!root)return null;var g=root.querySelector('#timeslot-grid-provider-'+id);"
                        + "if(g)return g;var all=root.querySelectorAll('*');for(var i=0;i<all.length;i++)"
                        + "if(all[i].shadowRoot){var f=findGrid(all[i].shadowRoot,id);if(f)return f;}return null;}"
                        + "var grid=findGrid(document.body,oid);if(grid){grid.scrollIntoView({block:'center'});"
                        + "window.scrollBy(0,72);}return true;";
        ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, lastSlotBookingOfficeId);
    }

    /**
     * Normalize Ort provider checkboxes so only {@code allowedOfficeIds} remain checked.
     * Single-provider teaser layouts have no checkboxes and are left unchanged.
     */
    public void keepOnlyProviderCheckboxesChecked(Set<Integer> allowedOfficeIds) {
        CONTEXT.set();
        Set<Integer> allowed = new HashSet<>(allowedOfficeIds);
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: keep only providers {} checked on Ort step", allowed);

        String script =
                "function collect(root,out){"
                        + "  if(!root)return;"
                        + "  var nodes=root.querySelectorAll('[id^=\"checkbox-provider-\"]');"
                        + "  for(var i=0;i<nodes.length;i++){if(nodes[i]&&nodes[i].id)out.push(nodes[i].id);}"
                        + "  var all=root.querySelectorAll('*');"
                        + "  for(var j=0;j<all.length;j++)if(all[j].shadowRoot)collect(all[j].shadowRoot,out);"
                        + "}"
                        + "var ids=[];collect(document.body,ids);"
                        + "return ids;";
        String allowedCsv = allowed.stream().map(String::valueOf).reduce((a, b) -> a + "," + b).orElse("");
        Object idsObj = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, allowedCsv);
        Set<Integer> presentIds = new HashSet<>();
        if (idsObj instanceof java.util.List<?>) {
            for (Object rawId : (java.util.List<?>) idsObj) {
                String idStr = String.valueOf(rawId);
                if (idStr.startsWith("checkbox-provider-")) {
                    try {
                        presentIds.add(Integer.parseInt(idStr.substring("checkbox-provider-".length())));
                    } catch (NumberFormatException ignored) {
                        // Ignore malformed provider ids.
                    }
                }
            }
        }
        int checkboxCount = presentIds.size();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: Ort provider checkbox count detected={}", checkboxCount);

        if (checkboxCount == 0) {
            ScenarioLogManager.getLogger()
                    .info("zmscitizenview: no provider checkboxes found (single-provider teaser layout), nothing to normalize");
            return;
        }

        for (Integer officeId : presentIds) {
            boolean shouldBeChecked = allowed.contains(officeId);
            boolean currentlyChecked = deepProviderCheckboxChecked(officeId);
            if (shouldBeChecked != currentlyChecked) {
                deepClickRequired("#checkbox-provider-" + officeId);
                waitUntilProviderToggleSettled(15);
            }
        }

        for (Integer officeId : allowed) {
            Assert.assertTrue(
                    deepProviderCheckboxChecked(officeId),
                    "Expected provider checkbox " + officeId + " to be checked after provider normalization.");
        }
    }

    /** Wait until provider-toggle spinner activity has settled (best effort). */
    private void waitUntilProviderToggleSettled(int maxSeconds) {
        CONTEXT.set();
        long deadline = System.currentTimeMillis() + maxSeconds * 1000L;
        while (System.currentTimeMillis() < deadline) {
            if (!deepMucSpinnerVisible()) {
                return;
            }
            try {
                Thread.sleep(250L);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
                return;
            }
        }
    }

    /**
     * Waits after combination → Ort/ Zeit: multi-provider checkboxes or single-provider teaser.
     */
    public void waitUntilOrtStepShowsProvider(int officeId, int maxSeconds) {
        CONTEXT.set();
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: Ort step — start waiting for provider {} (checkbox or single-provider teaser), up to {}s",
                        officeId,
                        maxSeconds);
        long deadline = java.lang.System.currentTimeMillis() + maxSeconds * 1000L;
        while (java.lang.System.currentTimeMillis() < deadline) {
            if (ortStepShowsProvider(officeId)) {
                ScenarioLogManager.getLogger()
                        .info(
                                "zmscitizenview: Ort step — provider {} found ({})",
                                officeId,
                                deepElementExists("#checkbox-provider-" + officeId)
                                        ? "checkbox list"
                                        : "single-provider teaser");
                return;
            }
            try {
                Thread.sleep(500L);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
                break;
            }
        }
        logOrtProviderResolution(officeId);
        Assert.fail(
                "Ort step did not show provider "
                        + officeId
                        + " within "
                        + maxSeconds
                        + "s (no checkbox-provider-"
                        + officeId
                        + " and no single-provider teaser)");
    }

    /**
     * Logs checkbox ids in DOM + whether single-provider teaser matches; asserts expected provider is shown in Ort.
     */
    public void logOrtProviderResolution(int expectedOfficeId) {
        CONTEXT.set();
        String script =
                "var ids=[];function collect(r){if(!r)return;var a=r.querySelectorAll('[id^=\"checkbox-provider-\"]');"
                        + "for(var i=0;i<a.length;i++)ids.push(a[i].id);var q=r.querySelectorAll('*');"
                        + "for(var j=0;j<q.length;j++)if(q[j].shadowRoot)collect(q[j].shadowRoot);}"
                        + "collect(document.body);return ids.join(',');";
        String found =
                String.valueOf(((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script));
        boolean checkboxOk = found.contains("checkbox-provider-" + expectedOfficeId);
        boolean teaserOk = deepOrtSingleProviderTeaserPresent(expectedOfficeId);
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: Ort provider resolution — checkboxIds=[{}] checkboxHit={} singleProviderTeaser={} expected={}",
                        found,
                        checkboxOk,
                        teaserOk,
                        expectedOfficeId);
        Assert.assertTrue(
                checkboxOk || teaserOk,
                "Ort must list provider "
                        + expectedOfficeId
                        + " (checkbox or single teaser); checkboxes=["
                        + found
                        + "] teaser="
                        + teaserOk);
    }

    /** True when at least one bookable slot control exists (list or calendar). */
    public boolean deepTimeslotClickablePresent() {
        CONTEXT.set();
        String script =
                "function has(root){if(!root)return false;"
                        + "var all=root.querySelectorAll('*');"
                        + "for(var i=0;i<all.length;i++){var n=all[i];"
                        + "if(n.id&&n.id.indexOf('-timeslot-')>=0)return true;"
                        + "if(n.classList&&n.classList.contains('timeslot'))return true;"
                        + "if(n.shadowRoot&&has(n.shadowRoot))return true;}return false;}"
                        + "return has(document.body);";
        return Boolean.TRUE.equals(
                ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script));
    }

    /**
     * MucSpinner lives in {@code .m-spinner-container}; shown while days load and again after a calendar day change
     * until slot API returns ({@code CalendarView.vue}).
     */
    public boolean deepMucSpinnerVisible() {
        CONTEXT.set();
        String script =
                "function vis(el){if(!el)return false;var s=getComputedStyle(el);"
                        + "if(s.display==='none'||s.visibility==='hidden'||parseFloat(s.opacity)===0)return false;"
                        + "var r=el.getBoundingClientRect();return r.width>=8&&r.height>=8;}"
                        + "function spin(root){if(!root)return false;"
                        + "var nodes=root.querySelectorAll('.m-spinner-container');"
                        + "for(var i=0;i<nodes.length;i++)if(vis(nodes[i]))return true;"
                        + "var all=root.querySelectorAll('*');"
                        + "for(var j=0;j<all.length;j++)if(all[j].shadowRoot&&spin(all[j].shadowRoot))return true;"
                        + "return false;}"
                        + "return spin(document.body);";
        return Boolean.TRUE.equals(
                ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script));
    }

    /**
     * Slot API finished: no visible MucSpinner and at least one timeslot control (avoids clicking while day-change
     * spinner runs).
     */
    public boolean deepTimeslotReadyNoSpinner() {
        return deepTimeslotClickablePresent() && !deepMucSpinnerVisible();
    }

    /**
     * Clicks <strong>Später</strong> (later) next to the time slot grid when the earlier/later controls are shown —
     * only when {@code providersWithAppointments.length &gt; 1} (see {@code CalendarView.vue} / {@code ListView.vue}).
     * Moves to the next available hour or PM half-day so slots sit further in the future; no-op if absent/disabled.
     *
     * @return {@code true} if a click was performed
     */
    public boolean clickCitizenViewLaterOnceIfAvailable() {
        CONTEXT.set();
        String findHighlight =
                "function findLaterBtn(root){"
                        + "if(!root)return null;"
                        + "try{"
                        + "var groups=root.querySelectorAll('.m-button-group');"
                        + "for(var g=0;g<groups.length;g++){"
                        + "var grp=groups[g];"
                        + "var later=grp.querySelector('muc-button.float-right[icon-shown-right]');"
                        + "if(!later)later=grp.querySelector('muc-button[icon-shown-right]');"
                        + "if(later&&later.shadowRoot){"
                        + "var btn=later.shadowRoot.querySelector('button:not([disabled])');"
                        + "if(btn&&btn.getAttribute('aria-disabled')!=='true')return btn;"
                        + "}"
                        + "var bs=grp.querySelectorAll('button.float-right.m-button--ghost');"
                        + "for(var b=0;b<bs.length;b++){"
                        + "var bb=bs[b];"
                        + "if(bb.disabled||bb.getAttribute('aria-disabled')==='true')continue;"
                        + "var tx=(bb.textContent||'').replace(/\\s+/g,' ').trim();"
                        + "if(tx.indexOf('Später')>=0||tx.indexOf('Later')>=0)return bb;"
                        + "}"
                        + "}"
                        + "}catch(e){}"
                        + "var all=root.querySelectorAll('*');"
                        + "for(var i=0;i<all.length;i++){"
                        + "if(all[i].shadowRoot){var f=findLaterBtn(all[i].shadowRoot);if(f)return f;}"
                        + "}"
                        + "return null;"
                        + "}"
                        + "function hl(el){"
                        + "if(!el)return false;"
                        + "el.scrollIntoView({block:'center'});"
                        + "try{"
                        + "el.style.outline='4px solid #ffbf00';"
                        + "el.style.outlineOffset='3px';"
                        + "el.style.backgroundColor='rgba(255,191,0,0.25)';"
                        + "}catch(e){}"
                        + "return true;"
                        + "}"
                        + "var btn=findLaterBtn(document.body);"
                        + "if(!btn)return false;"
                        + "hl(btn);"
                        + "window.__zmsCitizenViewLaterBtn=btn;"
                        + "return true;";
        String doClick =
                "var b=window.__zmsCitizenViewLaterBtn;"
                        + "if(!b)return false;"
                        + "try{b.click();}catch(e){return false;}"
                        + "try{window.__zmsCitizenViewLaterBtn=null;}catch(e2){}"
                        + "return true;";
        Object found = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(findHighlight);
        if (!Boolean.TRUE.equals(found)) {
            return false;
        }
        try {
            Thread.sleep(350L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
        Object clicked = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(doClick);
        if (Boolean.TRUE.equals(clicked)) {
            ScenarioLogManager.getLogger()
                    .info("zmscitizenview: clicked Später in time slot grid (next hour / day-part)");
        }
        return Boolean.TRUE.equals(clicked);
    }

    /** Wait until slot buttons exist and MucSpinner cleared (calendar day / office fetch). */
    public void waitUntilAppointmentSlotsReady(int maxSeconds) {
        CONTEXT.set();
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: waiting up to {}s for slots (MucSpinner gone + timeslot in DOM)",
                        maxSeconds);
        long t0 = java.lang.System.currentTimeMillis();
        try {
            new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(maxSeconds))
                    .until(d -> deepTimeslotReadyNoSpinner());
        } catch (org.openqa.selenium.TimeoutException e) {
            boolean spin = deepMucSpinnerVisible();
            boolean slot = deepTimeslotClickablePresent();
            ScenarioLogManager.getLogger()
                    .warn(
                            "zmscitizenview: slot wait timeout — spinnerVisible={} timeslotInDom={} after {}ms",
                            spin,
                            slot,
                            java.lang.System.currentTimeMillis() - t0);
            throw e;
        }
        ScenarioLogManager.getLogger().info("zmscitizenview: slots ready (spinner cleared, timeslot clickable)");
    }

    /** Max wait for slot grid + spinner (calendar / office load). */
    private int slotBookingWaitTimeoutSeconds() {
        return Math.max(DEFAULT_EXPLICIT_WAIT_TIME, 90);
    }

    /**
     * Step 1 of slot booking: wait until MucSpinner is gone and at least one timeslot exists (see
     * {@link #waitUntilAppointmentSlotsReady(int)}).
     */
    public void waitUntilSlotsReadyForBooking() {
        CONTEXT.set();
        int timeout = slotBookingWaitTimeoutSeconds();
        try {
            waitUntilAppointmentSlotsReady(timeout);
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn("zmscitizenview slot wait: {}", e.toString());
        }
        scrollTimeSlotGridIntoViewForScreenshots();
    }

    /**
     * Step 2: click <strong>Später</strong> beside the time slot grid (hour/day-part navigation) when shown
     * (multi-provider), then wait for slots to reload. No-op if the button is absent or disabled.
     */
    public void clickSpäterIfAvailableAndReloadSlots() {
        CONTEXT.set();
        int timeout = slotBookingWaitTimeoutSeconds();
        if (clickCitizenViewLaterOnceIfAvailable()) {
            try {
                Thread.sleep(1200L);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
            }
            try {
                waitUntilAppointmentSlotsReady(Math.min(45, timeout));
            } catch (Exception e) {
                ScenarioLogManager.getLogger()
                        .warn("zmscitizenview slot wait after Später: {}", e.toString());
            }
        }
        scrollTimeSlotGridIntoViewForScreenshots();
    }

    /** JS fragment: pick target slot + highlight + store in {@code window.__zmsCitizenViewSlotTarget} (no click). */
    private static String buildScrollSlotHighlightScript() {
        return "var oid=arguments[0];"
                + "function findGrid(root,id){if(!root)return null;var g=root.querySelector('#timeslot-grid-provider-'+id);"
                + "if(g)return g;var all=root.querySelectorAll('*');for(var i=0;i<all.length;i++)"
                + "if(all[i].shadowRoot){var f=findGrid(all[i].shadowRoot,id);if(f)return f;}return null;}"
                + "var grid=findGrid(document.body,oid);if(grid){grid.scrollIntoView({block:'start'});}"
                + "window.scrollBy(0,200);"
                + "function collectSlots(root,arr){if(!root)return;var n=root;"
                + "if(n.nodeType===1){"
                + " if((n.id&&n.id.indexOf('-timeslot-')>=0)||(n.classList&&n.classList.contains('timeslot'))){"
                + "   arr.push(n);"
                + " }"
                + " if(n.shadowRoot)collectSlots(n.shadowRoot,arr);"
                + "}"
                + "var c=n.children; if(c)for(var i=0;i<c.length;i++)collectSlots(c[i],arr);}"
                + "var slots=[];collectSlots(document.body,slots);"
                + "if(!slots.length)return false;"
                + "var minTs=Math.floor(Date.now()/1000)+3600;"
                + "function slotTs(node){"
                + " if(!node||!node.id)return null;"
                + " var m=node.id.match(/-timeslot-(\\d+)$/);"
                + " return m?parseInt(m[1],10):null;}"
                + "var target=null;"
                + "for(var j=0;j<slots.length;j++){"
                + " var ts=slotTs(slots[j]);"
                + " if(ts!==null&&ts>=minTs){target=slots[j];break;}"
                + "}"
                + "if(!target){"
                + " var nowSec=Math.floor(Date.now()/1000);"
                + " var minSafe=nowSec+300;"
                + " var best=null,bestTs=-1;"
                + " for(var k=0;k<slots.length;k++){"
                + "  var ts2=slotTs(slots[k]);"
                + "  if(ts2!==null&&ts2>=minSafe&&ts2>bestTs){best=slots[k];bestTs=ts2;}"
                + " }"
                + " target=best;"
                + "}"
                + "if(!target){"
                + " var idx = slots.length>2?2:(slots.length>1?1:0);"
                + " target = slots[idx];"
                + "}"
                + "function highlightSlot(node){"
                + " if(!node)return;"
                + " node.scrollIntoView({block:'center'});"
                + " try{"
                + " node.style.outline='4px solid #ffbf00';"
                + " node.style.outlineOffset='3px';"
                + " node.style.backgroundColor='rgba(255,191,0,0.25)';"
                + " if(node.shadowRoot){"
                + "  var ib=node.shadowRoot.querySelector('button');"
                + "  if(ib){"
                + "   ib.style.outline='4px solid #ffbf00';"
                + "   ib.style.outlineOffset='3px';"
                + "   ib.style.backgroundColor='rgba(255,191,0,0.2)';"
                + "  }"
                + " }"
                + " }catch(e){}"
                + "}"
                + "highlightSlot(target);"
                + "window.__zmsCitizenViewSlotTarget=target;"
                + "return true;";
    }

    private static final String SCROLL_SLOT_CLICK_SCRIPT =
            "var t=window.__zmsCitizenViewSlotTarget;"
                    + "if(!t)return false;"
                    + "function clickSlotNode(node){if(!node)return false;"
                    + " if(node.shadowRoot){var b=node.shadowRoot.querySelector('button:not([disabled])');if(b){b.click();return true;}}"
                    + " try{node.click();return true;}catch(e){}"
                    + " return false;}"
                    + "var ok=clickSlotNode(t);"
                    + "try{window.__zmsCitizenViewSlotTarget=null;}catch(e){}"
                    + "return ok;";

    /**
     * Step 3a: scroll to grid and highlight the preferred timeslot (no click). The next Cucumber step’s
     * {@code @AfterStep} screenshot then shows the orange outline before Vue updates.
     */
    public void highlightPreferredTimeslotForOffice(int officeId) {
        CONTEXT.set();
        String scrollSlotHighlight = buildScrollSlotHighlightScript();
        ScenarioLogManager.getLogger().info(
                "zmscitizenview: highlight preferred slot (≥60min ahead; else ≥5min; else 3rd/2nd/1st) office {}",
                officeId);
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(30))
                .until(
                        d ->
                                Boolean.TRUE.equals(
                                        ((JavascriptExecutor) d).executeScript(scrollSlotHighlight, officeId)));
        try {
            Thread.sleep(200L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
        scrollTimeSlotGridIntoViewForScreenshots();
        try {
            Thread.sleep(250L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
    }

    /** Step 3b: click the slot stored by {@link #highlightPreferredTimeslotForOffice(int)}. */
    public void clickHighlightedTimeslotSelection() {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: click highlighted timeslot");
        Object slotClickResult =
                ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(SCROLL_SLOT_CLICK_SCRIPT);
        Assert.assertTrue(
                Boolean.TRUE.equals(slotClickResult),
                "zmscitizenview: could not click highlighted timeslot");
        try {
            Thread.sleep(800L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
    }

    /**
     * Step 3 (combined): highlight + click — use split steps in features so {@code @AfterStep} captures the slot area.
     */
    public void selectPreferredTimeslotBelowCalendar(int officeId) {
        highlightPreferredTimeslotForOffice(officeId);
        clickHighlightedTimeslotSelection();
    }

    /**
     * Step 4: assert {@code Ausgewählter Termin} callout for the office, then <strong>Weiter</strong> to reserve (API).
     */
    public void assertCalloutAndReserveAfterSlotSelection(int officeId) {
        CONTEXT.set();
        assertSelectedAppointmentCalloutShowsProvider(officeId);
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: Weiter after slot callout → reserve appointment (then Kontakt form)");
        clickWeiter();
        waitForReserveToSettle();
    }

    /**
     * Full slot booking sequence (single step); prefer the split steps in feature files for clearer reports and
     * per-step screenshots.
     *
     * @see #waitUntilSlotsReadyForBooking()
     * @see #clickSpäterIfAvailableAndReloadSlots()
     * @see #selectPreferredTimeslotBelowCalendar(int)
     * @see #assertCalloutAndReserveAfterSlotSelection(int)
     */
    public void scrollClickFirstSlotAssertCalloutWeiter(int officeId) {
        waitUntilSlotsReadyForBooking();
        clickSpäterIfAvailableAndReloadSlots();
        selectPreferredTimeslotBelowCalendar(officeId);
        assertCalloutAndReserveAfterSlotSelection(officeId);
    }

    /**
     * After reserve-Weiter: give the reserve API time to persist so update-appointment (Kontakt submit) does not get
     * 404 appointmentNotFound.
     * <p>
     * Update payload comes from: (1) {@code processId}/{@code authKey}/{@code scope} from the reserve response
     * (stored in {@code appointment.value}); (2) contact fields from the form’s Vue state ({@code customerData}),
     * copied onto {@code appointment} in {@code nextUpdateAppointment} before POST. If the backend returns 404
     * despite a 200 reserve, increase this delay or check backend persistence (e.g. commit/replication).
     */
    private void waitForReserveToSettle() {
        try {
            Thread.sleep(4500L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
        ScenarioLogManager.getLogger().info("zmscitizenview: reserve settle delay done");
    }

    /** Info callout after slot pick: {@code Ausgewählter Termin} + {@code #provider-{officeId}}. */
    public void assertSelectedAppointmentCalloutShowsProvider(int officeId) {
        CONTEXT.set();
        waitUntilShadowContains("Ausgewählter Termin", DEFAULT_EXPLICIT_WAIT_TIME);
        Assert.assertTrue(
                shadowDomContainsText("Ausgewählter Termin"),
                "Ausgewählter Termin callout missing after slot click");
        Assert.assertTrue(
                deepElementExists("#provider-" + officeId),
                "Expected #provider-" + officeId + " in selected-appointment callout");
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: callout OK — Ausgewählter Termin includes provider {} (Bürgerbüro Ruppertstraße)",
                        officeId);
    }

    /** Fixed test phone; never random (avoid real subscriber numbers). */
    public static final String CONTACT_PHONE_E2E = "+491234567890";

    /** Short Lorem for required custom remarks (under 250). */
    private static final String CONTACT_LOREM_REQUIRED =
            "Lorem ipsum dolor sit amet, consectetur adipiscing elit. E2E Pflichtfeld.";

    public void fillContactDetails(String firstName, String lastName, String email, String phone) {
        CONTEXT.set();
        deepSetById("firstname", firstName);
        deepSetById("lastname", lastName);
        deepSetById("mailaddress", email);
        if (deepContactPhoneFieldExists()) {
            deepSetById("telephonenumber", phone);
        }
    }

    /** Phone field: host {@code id=\"telephonenumber\"} or inner {@code input-telephonenumber}. */
    public boolean deepContactPhoneFieldExists() {
        return deepElementExists("#telephonenumber")
                || deepElementExists("#input-telephonenumber")
                || deepElementExists("muc-input#telephonenumber");
    }

    /**
     * Kontakt step: same name/email approach as zmsadmin ({@link RandomNameHelper} + mailinator).
     * Vorname/Nachname split; phone only if field exists (optional or required); required custom
     * text areas only → {@link #CONTACT_LOREM_REQUIRED}.
     */
    public void fillContactDetailsRandom() {
        CONTEXT.set();
        waitUntilShadowContains("Kontaktdaten", Math.max(30, DEFAULT_EXPLICIT_WAIT_TIME));
        try {
            Thread.sleep(600L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
        String fullName;
        if (TestDataHelper.getTestData("customer_name") != null) {
            fullName = TestDataHelper.getTestData("customer_name");
        } else {
            fullName = RandomNameHelper.generateRandomName();
        }
        String[] parts = RandomNameHelper.splitFullNameIntoFirstAndLast(fullName);
        String email = RandomNameHelper.getEmailConformName(fullName) + "@mailinator.com";
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: Kontakt — Vorname={} Nachname={} E-Mail={}",
                        parts[0],
                        parts[1],
                        email);
        boolean ok1 = deepSetById("firstname", parts[0]);
        boolean ok2 = deepSetById("lastname", parts[1]);
        boolean ok3 = deepSetById("mailaddress", email);
        Assert.assertTrue(ok1, "Kontakt: could not set Vorname (muc-input shadow)");
        Assert.assertTrue(ok2, "Kontakt: could not set Nachname (muc-input shadow)");
        Assert.assertTrue(ok3, "Kontakt: could not set E-Mail (muc-input shadow)");
        if (deepContactPhoneFieldExists()) {
            deepSetById("telephonenumber", CONTACT_PHONE_E2E);
            ScenarioLogManager.getLogger().info("zmscitizenview: Kontakt — Telefon (field present)");
        }
        fillRequiredCustomTextAreasInShadow();
        fillOptionalContactRemarksIfPresent();
        try {
            Thread.sleep(500L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
    }

    /**
     * Both Bemerkung fields are often optional but still block or confuse validation if left totally empty in some
     * builds; fill with short Lorem when the textarea exists inside {@code muc-text-area} shadow (not only when HTML
     * required).
     */
    private void fillOptionalContactRemarksIfPresent() {
        CONTEXT.set();
        String script =
                "var lorem=arguments[0];var n=0;"
                        + "function fillTa(root){if(!root)return;var tas=root.querySelectorAll('textarea');"
                        + "for(var i=0;i<tas.length;i++){var e=tas[i];if(e.offsetParent===null)continue;"
                        + "if(e.value&&e.value.trim())continue;"
                        + "var lab=(e.getAttribute('aria-label')||e.placeholder||'');"
                        + "e.value=lorem.substring(0,Math.min(120,lorem.length));"
                        + "try{e.dispatchEvent(new InputEvent('input',{bubbles:true,inputType:'insertReplacementText',data:e.value}));}catch(x){e.dispatchEvent(new Event('input',{bubbles:true}));}"
                        + "e.dispatchEvent(new Event('change',{bubbles:true}));n++;}"
                        + "var all=root.querySelectorAll('*');for(var j=0;j<all.length;j++)if(all[j].shadowRoot)fillTa(all[j].shadowRoot);}"
                        + "fillTa(document.body);return n;";
        Object n =
                ((JavascriptExecutor) DriverUtil.getDriver())
                        .executeScript(script, CONTACT_LOREM_REQUIRED);
        if (n instanceof Number && ((Number) n).intValue() > 0) {
            ScenarioLogManager.getLogger()
                    .info("zmscitizenview: Kontakt — filled {} Bemerkung textarea(s) (optional)", n);
        }
    }

    /**
     * Fills only {@code textarea} nodes that are required (HTML or aria-required), in open shadow trees.
     * Skips optional custom fields; does not touch name/email/phone inputs.
     */
    private void fillRequiredCustomTextAreasInShadow() {
        CONTEXT.set();
        String script =
                "var lorem=arguments[0];function req(t){return t&&(t.required||t.getAttribute('aria-required')==='true');}"
                        + "function vis(t){try{return t.offsetParent!==null||t.getClientRects().length>0;}catch(e){return true;}}"
                        + "function fire(e){e.value=lorem;try{e.dispatchEvent(new InputEvent('input',{bubbles:true,inputType:'insertReplacementText',data:lorem}));}catch(x){e.dispatchEvent(new Event('input',{bubbles:true}));}e.dispatchEvent(new Event('change',{bubbles:true}));}"
                        + "var n=0;function walk(r){if(!r)return;var ta=r.querySelectorAll?r.querySelectorAll('textarea'):[];"
                        + "for(var i=0;i<ta.length;i++){var e=ta[i];if(req(e)&&vis(e)&&(!e.value||!e.value.trim())){fire(e);n++;}}"
                        + "var all=r.querySelectorAll('*');for(var j=0;j<all.length;j++)if(all[j].shadowRoot)walk(all[j].shadowRoot);}"
                        + "walk(document.body);return n;";
        Object n =
                ((JavascriptExecutor) DriverUtil.getDriver())
                        .executeScript(script, CONTACT_LOREM_REQUIRED);
        if (n instanceof Number && ((Number) n).intValue() > 0) {
            ScenarioLogManager.getLogger()
                    .info("zmscitizenview: Kontakt — filled {} required Bemerkung(en)", n);
        }
    }

    public void acceptPrivacyAndCommunication() {
        CONTEXT.set();
        deepClick("#checkbox-privacy-policy");
        deepClick("#checkbox-electronic-communication");
    }

    /**
     * Legacy: some builds exposed a primary “Termin reservieren” before the two-step reserve/update flow. Prefer
     * {@link #scrollClickFirstSlotAssertCalloutWeiter(int)} (Weiter = reserve) then {@link #fillContactDetailsRandom()}
     * + {@link #clickWeiter()} (update).
     */
    public void clickReserveAppointment() {
        CONTEXT.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(
                        d -> {
                            String script =
                                    "function find(root){if(!root)return null;var q=root.querySelector('button.m-button--primary');if(q&&!(q.disabled)&&((q.textContent||'').indexOf('Termin reservieren')>=0))return q;"
                                            + "var all=root.querySelectorAll('*');for(var i=0;i<all.length;i++){if(all[i].shadowRoot){var f=find(all[i].shadowRoot);if(f)return f;}}return null;}"
                                            + "var e=find(document.body);if(e){e.click();return true;}return false;";
                            return Boolean.TRUE.equals(((JavascriptExecutor) d).executeScript(script));
                        });
    }

    /** Preconfirm page: after privacy checkboxes, primary "Termin reservieren" button leads to activation (“Aktivieren Sie Ihren Termin.”). */
    public void continueFromPreconfirmStep() {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: preconfirm → Termin reservieren (activation callout)");
        waitForAndClickButtonContaining(DE_RESERVE, DEFAULT_EXPLICIT_WAIT_TIME);
        String marker = "Aktivieren Sie Ihren Termin.";
        waitWithThreeWindows(() -> shadowDomContainsText(marker), "Activation callout");
        Assert.assertTrue(
                shadowDomContainsText(marker),
                "Activation callout (Aktivieren Sie Ihren Termin.) not visible after Termin reservieren with retries.");
        ScenarioLogManager.getLogger().info("zmscitizenview: activation callout appeared");
        trySyncBookingProcessFromLocalStorageOnce();
    }

    /** Activation callout after "Termin reservieren": heading + time limit. Time is location-specific (e.g. 30 → "30 Minuten"). Reserve API may take several seconds, so we wait up to 25s for the callout. */
    public void assertPreconfirmationCalloutVisible(int activationMinutes) {
        CONTEXT.set();
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: waiting in 5s + 10s + 15s windows (30s total) for activation callout (Aktivieren Sie Ihren Termin., {} Minuten)",
                        activationMinutes);
        String heading = "Aktivieren Sie Ihren Termin.";
        waitWithThreeWindows(() -> shadowDomContainsText(heading), "Preconfirmation callout heading");
        Assert.assertTrue(
                shadowDomContainsText(heading),
                "Preconfirmation warning callout (Aktivieren Sie Ihren Termin.) not found after reserve with retries.");
        String timeText = activationMinutes + " Minuten";
        Assert.assertTrue(shadowDomContainsText(timeText),
                "Preconfirmation callout should mention activation time limit (" + timeText + ").");
        ScenarioLogManager.getLogger().info("zmscitizenview: activation callout visible with {} Minuten", activationMinutes);
    }

    public void assertConfirmationSuccessCalloutVisible() {
        ScenarioLogManager.getLogger().info("zmscitizenview: checking for confirmation success callout (Ihr Termin wurde gebucht.)");
        assertShadowContains(
                "Ihr Termin wurde gebucht.",
                "Confirmation success callout not found after opening confirm link.");
        ScenarioLogManager.getLogger().info("zmscitizenview: confirmation success callout found");
    }

    public void assertSelectedAppointmentCalloutVisible() {
        assertShadowContains(
                "Ausgewählter Termin",
                "Selected-appointment info callout not found after choosing slot.");
    }

    /** Click "Termin absagen" below the summary (sends cancel request), then wait for the cancellation success callout. */
    public void clickCancelAppointmentAndConfirm() {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: clicking cancel appointment button (Termin absagen)");
        waitForAndClickButtonContaining("Termin absagen", DEFAULT_EXPLICIT_WAIT_TIME);
        String marker = "Sie haben Ihren Termin erfolgreich abgesagt.";
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: waiting in 5s + 10s + 15s windows (30s total) for cancellation success callout");
        waitWithThreeWindows(() -> shadowDomContainsText(marker), "Cancellation success callout");
        Assert.assertTrue(
                shadowDomContainsText(marker),
                "Cancellation success callout (Sie haben Ihren Termin erfolgreich abgesagt.) not visible after Termin absagen with retries.");
    }

    public void assertCancellationSuccessCalloutVisible() {
        ScenarioLogManager.getLogger().info("zmscitizenview: checking for cancellation success callout (Sie haben Ihren Termin erfolgreich abgesagt.)");
        assertShadowContains(
                "Sie haben Ihren Termin erfolgreich abgesagt.",
                "Cancellation success callout not found after cancelling appointment.");
        ScenarioLogManager.getLogger().info("zmscitizenview: cancellation success callout found");
    }

    /**
     * Reads {@value #LOCALSTORAGE_APPOINTMENT_KEY} and sets {@link zms.ataf.rest.steps.CitizenApiSteps} booking process
     * so mail steps can run after UI preconfirm. If process was already set (e.g. by continueFromPreconfirmStep), returns it.
     */
    public ThinnedProcess syncBookingProcessFromLocalStorage() throws Exception {
        CONTEXT.set();
        ThinnedProcess already = zms.ataf.rest.steps.CitizenApiSteps.getBookingProcess();
        if (already != null) {
            ScenarioLogManager.getLogger().info("zmscitizenview: booking process already set (from reserve step), skipping localStorage read");
            return already;
        }
        String json =
                (String)
                        ((JavascriptExecutor) DriverUtil.getDriver())
                                .executeScript(
                                        "return localStorage.getItem('" + LOCALSTORAGE_APPOINTMENT_KEY + "');");
        if (json == null || json.isBlank()) {
            ScenarioLogManager.getLogger().info("zmscitizenview: localStorage lhm-appointment-data not available; ensure continueFromPreconfirmStep captured process from confirm link on page");
            return null;
        }
        ThinnedProcess p = parseAndSetBookingProcessFromJson(json);
        Assert.assertNotNull(p, "appointment.processId or authKey missing in localStorage");
        return p;
    }

    /** Try to set booking process after activation callout: first from localStorage, then from confirm link on page (same process id as mail). */
    private void trySyncBookingProcessFromLocalStorageOnce() {
        CONTEXT.set();
        if (trySetBookingProcessFromLocalStorage()) {
            return;
        }
        trySetBookingProcessFromConfirmLinkOnPage();
    }

    /** @return true if process was set from localStorage */
    private boolean trySetBookingProcessFromLocalStorage() {
        CONTEXT.set();
        String json =
                (String)
                        ((JavascriptExecutor) DriverUtil.getDriver())
                                .executeScript(
                                        "return localStorage.getItem('" + LOCALSTORAGE_APPOINTMENT_KEY + "');");
        if (json == null || json.isBlank()) {
            return false;
        }
        try {
            ThinnedProcess p = parseAndSetBookingProcessFromJson(json);
            if (p != null) {
                ScenarioLogManager.getLogger().info("zmscitizenview: captured booking process from localStorage after activation callout (processId={})", p.getProcessId());
                return true;
            }
        } catch (Exception e) {
            ScenarioLogManager.getLogger().debug("zmscitizenview: could not parse localStorage after activation callout", e);
        }
        return false;
    }

    /** Find confirm link (#/appointment/confirm/{base64}) on page, decode id/authKey, set booking process so mail step can find by process id. */
    private void trySetBookingProcessFromConfirmLinkOnPage() {
        CONTEXT.set();
        String script =
                "var out=null;function walk(root){if(!root)return;if(root.shadowRoot&&walk(root.shadowRoot))return true;"
                        + "var as=root.querySelectorAll('a[href]');for(var i=0;i<as.length;i++){var h=as[i].getAttribute('href')||'';var idx=h.indexOf('appointment/confirm/');if(idx>=0){var rest=h.substring(idx+'appointment/confirm/'.length);var end=rest.indexOf('?');if(end>=0)rest=rest.substring(0,end);out=rest;return true;}}"
                        + "var c=root.children;for(var j=0;j<c.length;j++)if(walk(c[j]))return true;return false;}"
                        + "walk(document.body);return out;";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        if (!(raw instanceof String) || ((String) raw).isBlank()) {
            return;
        }
        String b64 = (String) raw;
        try {
            String decoded = new String(Base64.getDecoder().decode(b64), StandardCharsets.UTF_8);
            JsonNode node = new ObjectMapper().readTree(decoded);
            JsonNode idNode = node.path("id");
            JsonNode keyNode = node.path("authKey");
            if (idNode.isMissingNode() || keyNode.isMissingNode() || idNode.isNull() || keyNode.isNull()) {
                return;
            }
            int processId = idNode.asInt();
            String authKey = keyNode.asText();
            ThinnedProcess p = new ThinnedProcess();
            p.setProcessId(processId);
            p.setAuthKey(authKey);
            zms.ataf.rest.steps.CitizenApiSteps.setBookingProcess(p);
            ScenarioLogManager.getLogger().info("zmscitizenview: captured booking process from confirm link on activation callout (processId={})", processId);
        } catch (Exception e) {
            ScenarioLogManager.getLogger().debug("zmscitizenview: could not parse confirm link from page", e);
        }
    }

    private ThinnedProcess parseAndSetBookingProcessFromJson(String json) throws Exception {
        ObjectMapper mapper = new ObjectMapper();
        JsonNode root = mapper.readTree(json);
        JsonNode appointment = root.path("appointment");
        Integer processId = null;
        if (appointment.has("processId") && !appointment.get("processId").isNull()) {
            processId = appointment.get("processId").asInt();
        }
        String authKey = appointment.path("authKey").asText(null);
        if (processId == null || authKey == null) {
            return null;
        }
        ThinnedProcess p = new ThinnedProcess();
        p.setProcessId(processId);
        p.setAuthKey(authKey);
        zms.ataf.rest.steps.CitizenApiSteps.setBookingProcess(p);
        return p;
    }

    /** Navigate to zmscitizenview confirm page. Prefer URL extracted from mail body (GET /mails/); else build from confirm credentials or booking process. */
    public void openConfirmationDeepLinkInBrowser() {
        CONTEXT.set();
        String url = zms.ataf.rest.steps.CitizenApiSteps.getBookingConfirmUrl();
        if (url != null && !url.isBlank()) {
            ScenarioLogManager.getLogger().info("zmscitizenview: opening confirmation deep link (URL from mail body)");
        } else {
            String processId = zms.ataf.rest.steps.CitizenApiSteps.getBookingConfirmProcessId();
            String authKey = zms.ataf.rest.steps.CitizenApiSteps.getBookingConfirmAuthKey();
            boolean fromMail = processId != null && authKey != null;
            if (!fromMail) {
                ThinnedProcess p = zms.ataf.rest.steps.CitizenApiSteps.getBookingProcess();
                Assert.assertNotNull(p, "No booking process; sync localStorage and fetch preconfirmation mail first");
                processId = String.valueOf(p.getProcessId());
                authKey = p.getAuthKey();
            }
            ScenarioLogManager.getLogger().info("zmscitizenview: opening confirmation deep link (credentials from {})", fromMail ? "GET /mails/" : "localStorage");
            String payload =
                    "{\"id\":"
                            + processId
                            + ",\"authKey\":"
                            + mapperQuote(authKey)
                            + "}";
            String b64 = Base64.getEncoder().encodeToString(payload.getBytes(StandardCharsets.UTF_8));
            String base = CONTEXT.lastCitizenViewUrl != null ? CONTEXT.lastCitizenViewUrl : "";
            int hashIdx = base.indexOf('#');
            if (hashIdx >= 0) {
                base = base.substring(0, hashIdx);
            }
            url = base + "#/appointment/confirm/" + b64;
        }
        url = ensureAbsoluteCitizenViewUrl(url);
        ScenarioLogManager.getLogger().info("zmscitizenview: navigating to confirmation URL: {}", url);
        navigateCitizenViewUrl(url);
        try {
            Thread.sleep(2000L);
        } catch (InterruptedException ie) {
            Thread.currentThread().interrupt();
        }
    }

    /** Navigate to the appointment view URL extracted from the confirmation mail (link without /confirm/), then refresh so the app loads it. */
    public void openAppointmentViewDeepLinkInBrowser() {
        CONTEXT.set();
        String url = zms.ataf.rest.steps.CitizenApiSteps.getBookingAppointmentUrl();
        if (url == null || url.isBlank()) {
            ScenarioLogManager.getLogger().warn("zmscitizenview: no appointment view URL set; fetch the confirmation mail (second mail) first.");
        }
        Assert.assertNotNull(url, "No appointment view URL; fetch the confirmation mail first.");
        url = ensureAbsoluteCitizenViewUrl(url);
        ScenarioLogManager.getLogger().info("zmscitizenview: navigating to appointment view URL (from second email): {}", url);
        navigateCitizenViewUrl(url);
        try {
            Thread.sleep(2000L);
        } catch (InterruptedException ie) {
            Thread.currentThread().interrupt();
        }
    }

    /**
     * Safari (and {@code pageLoadStrategy=eager}) often hit WebDriver timeouts on {@code navigate().to()} when only
     * the URL fragment changes — the document does not reload. Assign {@code location.href} in-page so the hash
     * updates immediately; use normal navigation when switching origin or path.
     */
    private void navigateCitizenViewUrl(String url) {
        RemoteWebDriver driver = DriverUtil.getDriver();
        String target = ensureAbsoluteCitizenViewUrl(url);
        try {
            String current = driver.getCurrentUrl();
            if (isSameDocumentCitizenViewNavigation(current, target)) {
                ((JavascriptExecutor) driver).executeScript(
                        "window.location.href = arguments[0];", target);
            } else {
                driver.navigate().to(target);
            }
        } catch (Exception e) {
            ScenarioLogManager.getLogger()
                    .error("navigateCitizenViewUrl failed (target URL: " + target + ")", e);
            throw new RuntimeException("navigateCitizenViewUrl failed for " + target, e);
        }
    }

    private static boolean isSameDocumentCitizenViewNavigation(String currentUrl, String targetUrl) {
        try {
            URI cur = URI.create(currentUrl);
            URI tgt = URI.create(targetUrl);
            if (cur.getScheme() == null || tgt.getScheme() == null) {
                return false;
            }
            if (!cur.getScheme().equalsIgnoreCase(tgt.getScheme())) {
                return false;
            }
            if (!Objects.equals(hostKey(cur), hostKey(tgt))) {
                return false;
            }
            if (cur.getPort() != tgt.getPort()) {
                return false;
            }
            return normalizedPath(cur).equals(normalizedPath(tgt));
        } catch (IllegalArgumentException e) {
            return false;
        }
    }

    private static String hostKey(URI u) {
        String h = u.getHost();
        return h == null ? "" : h.toLowerCase();
    }

    private static String normalizedPath(URI u) {
        String p = u.getPath();
        if (p == null || p.isEmpty()) {
            return "/";
        }
        return p;
    }

    private static String mapperQuote(String s) {
        if (s == null) {
            return "null";
        }
        return "\"" + s.replace("\\", "\\\\").replace("\"", "\\\"") + "\"";
    }

    /**
     * Mail bodies often contain {@code localhost:8082/#/...} without a scheme. WebDriver then mis-resolves the URL
     * (e.g. only {@code http://localhost:8082/#}). Always produce a proper absolute URL with {@code http://} or {@code https://}.
     */
    static String ensureAbsoluteCitizenViewUrl(String url) {
        if (url == null || url.isBlank()) {
            return url;
        }
        String u = url.trim();
        if (u.length() >= 7 && u.regionMatches(true, 0, "http://", 0, 7)) {
            return u;
        }
        if (u.length() >= 8 && u.regionMatches(true, 0, "https://", 0, 8)) {
            return u;
        }
        if (u.startsWith("//")) {
            return "http:" + u;
        }
        if (u.startsWith("#")) {
            String origin = Objects.requireNonNullElse(
                System.getenv("CITIZEN_VIEW_BASE_URI"),
                "http://localhost:8082/"
            ).trim();
            if (!origin.endsWith("/")) {
                origin = origin + "/";
            }
            return origin + u;
        }
        return "http://" + u;
    }
}
