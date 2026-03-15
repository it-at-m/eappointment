package zms.ataf.ui.pages.citizenview;

import java.nio.charset.StandardCharsets;
import java.time.Duration;
import java.util.Base64;

import org.openqa.selenium.JavascriptExecutor;
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

    /** Click first button whose visible text includes label (shadow-safe). */
    public boolean clickButtonContaining(String text) {
        CONTEXT.set();
        String esc = text.replace("\\", "\\\\").replace("'", "\\'");
        String script =
                "var label='" + esc + "';function walkClick(n){if(!n)return false;if(n.shadowRoot&&walkClick(n.shadowRoot))return true;"
                        + "if(n.tagName==='BUTTON'||n.tagName==='A'){var t=(n.textContent||'').trim();if(t.indexOf(label)>=0&&!n.disabled){n.scrollIntoView({block:'center'});n.click();return true;}}"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walkClick(c[i]))return true;return false;}"
                        + "return walkClick(document.body);";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return Boolean.TRUE.equals(o);
    }

    public void clickWeiter() {
        CONTEXT.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(d -> clickButtonContaining(DE_WEITER));
    }

    /**
     * After clicking Weiter on the Kontakt form: wait for the update-appointment response and for the preconfirm
     * page to be shown (privacy checkboxes). Prevents the next step from running before the UI has navigated.
     */
    public void waitForPreconfirmPageAfterUpdate(int timeoutSeconds) {
        CONTEXT.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: waiting up to {}s for preconfirm page after Kontakt Weiter", timeoutSeconds);
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(timeoutSeconds))
                .until(d -> deepElementExists("#checkbox-privacy-policy"));
        ScenarioLogManager.getLogger().info("zmscitizenview: preconfirm page visible");
    }

    /** Jump-in: combination step shows Weiter + optional counters. */
    public void assertCombinationStepVisible() {
        CONTEXT.set();
        waitUntilShadowContains(DE_WEITER, DEFAULT_EXPLICIT_WAIT_TIME);
        Assert.assertTrue(
                shadowDomContainsText(DE_WEITER),
                "Expected combination step (button Weiter) after jump-in.");
    }

    /** Full entry: open choices, type filter, click option row containing serviceLabel. */
    public void selectServiceByLabel(String serviceLabel) {
        CONTEXT.set();
        deepClickRequired("#select-service-search");
        try {
            Thread.sleep(500);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
        deepSetById("select-service-search", serviceLabel);
        try {
            Thread.sleep(800);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
        String esc = serviceLabel.replace("\\", "\\\\").replace("'", "\\'");
        String script =
                "var label='" + esc + "';function walkClick(n){if(!n)return false;if(n.shadowRoot&&walkClick(n.shadowRoot))return true;"
                        + "if(n.classList&&n.classList.contains('choices__item--choice')||n.classList&&n.classList.contains('choices__item--selectable')){"
                        + "var t=(n.textContent||'');if(t.indexOf(label)>=0){n.scrollIntoView({block:'center'});n.click();return true;}}"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walkClick(c[i]))return true;return false;}"
                        + "return walkClick(document.body);";
        Object clicked = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        if (!Boolean.TRUE.equals(clicked)) {
            clickButtonContaining(serviceLabel);
        }
        clickWeiter();
    }

    /**
     * Jump-in can pre-select the only provider; clicking again toggles off. True if that office is already on.
     */
    public boolean deepProviderCheckboxChecked(int officeId) {
        CONTEXT.set();
        String sel = "#checkbox-provider-" + officeId;
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

    /**
     * Below the calendar: scroll to slot grid, wait for API, click first timeslot, assert {@code Ausgewählter Termin}
     * callout, then click <strong>Weiter</strong> — that call <em>reserves</em> the appointment (API reserve). The
     * update-appointment (Kontakt) form is shown only after this Weiter.
     */
    public void scrollClickFirstSlotAssertCalloutWeiter(int officeId) {
        CONTEXT.set();
        int timeout = Math.max(DEFAULT_EXPLICIT_WAIT_TIME, 90);
        try {
            waitUntilAppointmentSlotsReady(timeout);
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn("zmscitizenview slot wait: {}", e.toString());
        }
        String scrollClick =
                "var oid=arguments[0];"
                        + "function findGrid(root,id){if(!root)return null;var g=root.querySelector('#timeslot-grid-provider-'+id);"
                        + "if(g)return g;var all=root.querySelectorAll('*');for(var i=0;i<all.length;i++)"
                        + "if(all[i].shadowRoot){var f=findGrid(all[i].shadowRoot,id);if(f)return f;}return null;}"
                        + "var grid=findGrid(document.body,oid);if(grid){grid.scrollIntoView({block:'start'});}"
                        + "window.scrollBy(0,200);"
                        + "function clickSlot(n){if(!n)return false;if(n.nodeType===1){"
                        + "if(n.id&&n.id.indexOf('-timeslot-')>=0){if(n.shadowRoot){var b=n.shadowRoot.querySelector('button:not([disabled])');if(b){b.click();return true;}}try{n.click();return true;}catch(e){}}"
                        + "if(n.classList&&n.classList.contains('timeslot')){if(n.shadowRoot){var b2=n.shadowRoot.querySelector('button:not([disabled])');if(b2){b2.click();return true;}}try{n.click();return true;}catch(e2){}}}"
                        + "if(n.shadowRoot&&clickSlot(n.shadowRoot))return true;var c=n.children;if(c)for(var i=0;i<c.length;i++)if(clickSlot(c[i]))return true;return false;}"
                        + "return clickSlot(document.body);";
        ScenarioLogManager.getLogger().info("zmscitizenview: scroll + first slot office {}", officeId);
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(30))
                .until(
                        d ->
                                Boolean.TRUE.equals(
                                        ((JavascriptExecutor) d).executeScript(scrollClick, officeId)));
        try {
            Thread.sleep(800L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
        assertSelectedAppointmentCalloutShowsProvider(officeId);
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: Weiter after slot callout → reserve appointment (then Kontakt form)");
        clickWeiter();
        waitForReserveToSettle();
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

    /** Preconfirm page: after privacy checkboxes, primary Weiter leads to activation (“Aktivieren Sie Ihren Termin.”). */
    public void continueFromPreconfirmStep() {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: preconfirm → Weiter (activation callout)");
        clickWeiter();
        try {
            Thread.sleep(1500L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
    }

    public void assertPreconfirmationCalloutVisible() {
        assertShadowContains(
                "Aktivieren Sie Ihren Termin.",
                "Preconfirmation warning callout (Aktivieren Sie Ihren Termin.) not found after reserve.");
    }

    public void assertConfirmationSuccessCalloutVisible() {
        assertShadowContains(
                "Ihr Termin wurde gebucht.",
                "Confirmation success callout not found after opening confirm link.");
    }

    public void assertSelectedAppointmentCalloutVisible() {
        assertShadowContains(
                "Ausgewählter Termin",
                "Selected-appointment info callout not found after choosing slot.");
    }

    /**
     * Reads {@value #LOCALSTORAGE_APPOINTMENT_KEY} and sets {@link zms.ataf.rest.steps.CitizenApiSteps} booking process
     * so mail steps can run after UI preconfirm.
     */
    public ThinnedProcess syncBookingProcessFromLocalStorage() throws Exception {
        CONTEXT.set();
        String json =
                (String)
                        ((JavascriptExecutor) DriverUtil.getDriver())
                                .executeScript(
                                        "return localStorage.getItem('" + LOCALSTORAGE_APPOINTMENT_KEY + "');");
        Assert.assertNotNull(json, "localStorage lhm-appointment-data missing after UI flow");
        ObjectMapper mapper = new ObjectMapper();
        JsonNode root = mapper.readTree(json);
        JsonNode appointment = root.path("appointment");
        Integer processId = null;
        if (appointment.has("processId") && !appointment.get("processId").isNull()) {
            processId = appointment.get("processId").asInt();
        }
        String authKey = appointment.path("authKey").asText(null);
        Assert.assertNotNull(processId, "appointment.processId missing in localStorage");
        Assert.assertNotNull(authKey, "appointment.authKey missing in localStorage");
        ThinnedProcess p = new ThinnedProcess();
        p.setProcessId(processId);
        p.setAuthKey(authKey);
        zms.ataf.rest.steps.CitizenApiSteps.setBookingProcess(p);
        return p;
    }

    public void openConfirmationDeepLinkInBrowser() {
        CONTEXT.set();
        ThinnedProcess p = zms.ataf.rest.steps.CitizenApiSteps.getBookingProcess();
        Assert.assertNotNull(p, "No booking process; sync localStorage first");
        String payload =
                "{\"id\":"
                        + p.getProcessId()
                        + ",\"authKey\":"
                        + mapperQuote(p.getAuthKey())
                        + "}";
        String b64 = Base64.getEncoder().encodeToString(payload.getBytes(StandardCharsets.UTF_8));
        String base = CONTEXT.lastCitizenViewUrl != null ? CONTEXT.lastCitizenViewUrl : "";
        int hashIdx = base.indexOf('#');
        if (hashIdx >= 0) {
            base = base.substring(0, hashIdx);
        }
        String url = base + "#/appointment/confirm/" + b64;
        try {
            DriverUtil.getDriver().navigate().to(url);
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn("Navigate to confirm URL", e);
        }
        try {
            Thread.sleep(4000L);
        } catch (InterruptedException ie) {
            Thread.currentThread().interrupt();
        }
    }

    private static String mapperQuote(String s) {
        if (s == null) {
            return "null";
        }
        return "\"" + s.replace("\\", "\\\\").replace("\"", "\\\"") + "\"";
    }
}
