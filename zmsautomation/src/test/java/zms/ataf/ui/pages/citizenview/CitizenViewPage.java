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

import ataf.core.logging.ScenarioLogManager;
import ataf.web.model.LocatorType;
import ataf.web.pages.BasePage;
import ataf.web.utils.DriverUtil;
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

    public void assertProviderCheckboxPresent(int officeId) {
        CONTEXT.set();
        String sel = "#checkbox-provider-" + officeId;
        waitUntilDeepElementExists(sel, DEFAULT_EXPLICIT_WAIT_TIME);
        logProviderCheckboxesVisible(officeId);
        Assert.assertTrue(deepElementExists(sel), "Expected provider checkbox in DOM: " + sel);
    }

    public void assertProviderCheckboxAbsent(int officeId) {
        CONTEXT.set();
        Assert.assertFalse(
                deepElementExists("#checkbox-provider-" + officeId),
                "Provider checkbox for office " + officeId + " must not appear for this jump-in/service.");
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

    /** Set value on input/textarea found by id anywhere in shadow DOM. */
    public boolean deepSetById(String id, String value) {
        CONTEXT.set();
        String script =
                "var id=arguments[0],v=arguments[1];function find(root){if(!root)return null;var q=root.getElementById?root.getElementById(id):root.querySelector('#'+id);"
                        + "if(q&&(q.tagName==='INPUT'||q.tagName==='TEXTAREA'))return q;var all=root.querySelectorAll('*');"
                        + "for(var i=0;i<all.length;i++){if(all[i].shadowRoot){var f=find(all[i].shadowRoot);if(f)return f;}}return null;}"
                + "var e=find(document.body);if(e){e.scrollIntoView({block:'center'});e.focus();e.value=v;"
                + "e.dispatchEvent(new Event('input',{bubbles:true}));e.dispatchEvent(new Event('change',{bubbles:true}));return true;}return false;";
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

    public void selectOfficeById(int officeId) {
        CONTEXT.set();
        logProviderCheckboxesVisible(officeId);
        deepClickRequired("#checkbox-provider-" + officeId);
    }

    /**
     * Logs every {@code #checkbox-provider-*} in DOM (shadow-safe). Confirms {@code expectedOfficeId} is in the Ort
     * list before we tick it.
     */
    public void logProviderCheckboxesVisible(int expectedOfficeId) {
        CONTEXT.set();
        String script =
                "var ids=[];function collect(r){if(!r)return;var a=r.querySelectorAll('[id^=\"checkbox-provider-\"]');"
                        + "for(var i=0;i<a.length;i++)ids.push(a[i].id);var q=r.querySelectorAll('*');"
                        + "for(var j=0;j<q.length;j++)if(q[j].shadowRoot)collect(q[j].shadowRoot);}"
                        + "collect(document.body);return ids.join(',');";
        String found =
                String.valueOf(((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script));
        boolean ok = found.contains("checkbox-provider-" + expectedOfficeId);
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview Ort checkboxes in DOM: [{}] | expected provider {} present={}",
                        found,
                        expectedOfficeId,
                        ok);
        Assert.assertTrue(
                ok,
                "Expected #checkbox-provider-" + expectedOfficeId + " in list; saw: " + found);
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

    /** Wait until slot buttons exist; API slow after office/day selection in calendar. */
    public void waitUntilAppointmentSlotsReady(int maxSeconds) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: waiting up to {}s for appointment slots", maxSeconds);
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(maxSeconds))
                .until(d -> deepTimeslotClickablePresent());
    }

    /**
     * Below the calendar: scroll to slot grid, wait for API, click first {@code muc-button.timeslot} (prefer grid for
     * {@code officeId}). First day stays as preselected — no calendar toggle.
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
        ScenarioLogManager.getLogger().info("zmscitizenview: Weiter after Ausgewählter Termin callout");
        clickWeiter();
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

    public void fillContactDetails(String firstName, String lastName, String email, String phone) {
        CONTEXT.set();
        deepSetById("input-firstname", firstName);
        deepSetById("input-lastname", lastName);
        deepSetById("input-mailaddress", email);
        deepSetById("input-telephonenumber", phone);
    }

    public void acceptPrivacyAndCommunication() {
        CONTEXT.set();
        deepClick("#checkbox-privacy-policy");
        deepClick("#checkbox-electronic-communication");
    }

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
