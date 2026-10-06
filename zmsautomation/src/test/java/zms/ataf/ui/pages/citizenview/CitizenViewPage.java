package zms.ataf.ui.pages.citizenview;

import java.nio.charset.StandardCharsets;
import java.time.Duration;
import java.time.Instant;
import java.time.ZoneId;
import java.time.ZonedDateTime;
import java.time.format.DateTimeFormatter;
import java.util.Base64;
import java.util.HashSet;
import java.util.Locale;
import java.util.Objects;
import java.util.Set;
import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.Keys;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.interactions.Actions;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;

import ataf.core.helpers.TestDataHelper;
import ataf.core.helpers.TestPropertiesHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.web.model.LocatorType;
import ataf.web.pages.BasePage;
import ataf.web.utils.DriverUtil;
import zms.ataf.helpers.AccountCheckout;
import zms.ataf.helpers.RandomNameHelper;
import zms.ataf.helpers.ViewportSizes;
import zms.ataf.rest.dto.zmscitizenapi.ThinnedProcess;
import zms.ataf.ui.pages.citizenview.support.CitizenViewScripts;
import zms.ataf.ui.pages.citizenview.support.CitizenViewWaits;
import zms.ataf.ui.pages.citizenview.support.ShadowDom;
import zms.ataf.ui.pages.citizenview.steps.CombinationStep;
import zms.ataf.ui.pages.citizenview.steps.ServiceFinderStep;
import zms.ataf.ui.pages.citizenview.steps.ProviderOrtStep;
import zms.ataf.ui.pages.citizenview.steps.SlotZeitStep;
import zms.ataf.ui.pages.citizenview.support.CitizenViewJson;
import zms.ataf.ui.pages.citizenview.support.SlotBookingState;

    /**
 * zmscitizenview booking flow: all meaningful DOM lives under Vue custom elements / shadow roots.
 * Interactions use JS that searches open shadow trees (deep query / text walk).
 */
public class CitizenViewPage extends BasePage {

    /** Same key as zmscitizenview LOCALSTORAGE_PARAM_APPOINTMENT_DATA */
    public static final String LOCALSTORAGE_APPOINTMENT_KEY = "lhm-appointment-data";

    private static final String DE_WEITER = "Weiter";
    private static final String DE_RESERVE = "Termin reservieren";
    private static final String ALREADY_ACTIVATED_BANNER_MARKER =
            "Sie haben Ihren Termin bereits aktiviert.";
    private static final String RESCHEDULE_APPOINTMENT_BUTTON = "Termin verschieben";
    private static final String CANCEL_RESCHEDULE_BUTTON = "Verschieben abbrechen";
    private static final String ACTIVATION_CALLOUT_HEADING = "Aktivieren Sie Ihren Termin.";
    private static final String CONFIRMATION_SUCCESS_HEADING = "Ihr Termin wurde gebucht.";
    private static final String CONFIRMATION_SUCCESS_TEXT =
            "Eine Bestätigung und weitere Informationen zu Ihrem Termin erhalten Sie per E-Mail. Wir freuen uns auf Ihren Besuch.";
    private static final String VIEW_APPOINTMENT_BUTTON = "Termin ansehen";
    private static final String BOOK_ANOTHER_APPOINTMENT_BUTTON = "Weiteren Termin vereinbaren";
    private static final String CANCELLATION_SUCCESS_HEADING =
            "Sie haben Ihren Termin erfolgreich abgesagt.";
    private static final String CANCELLATION_SUCCESS_TEXT =
            "Danke, dass Sie Ihren Termin für andere freigegeben haben.";

    /** German invalid jump-in callout ({@code de-DE.json}). */
    public static final String DE_INVALID_JUMPIN_HEADER = "Diese Ansicht kann nicht geladen werden.";

    public static final String DE_INVALID_JUMPIN_TEXT =
            "Der Link zu dieser Seite ist leider fehlerhaft. Starten Sie die Terminvereinbarung neu";


    private static final String EN_INVALID_JUMPIN_HEADER = "This view cannot be loaded.";
    private static final String EN_INVALID_JUMPIN_TEXT =
            "The link to this page is unfortunately incorrect";

    private final CitizenViewPageContext CONTEXT;

    private final ShadowDom shadow;

    private final CombinationStep combination;

    private final ServiceFinderStep serviceFinder;

    private final CitizenViewJson json;

    private final SlotBookingState slotState;

    private final ProviderOrtStep providerOrt;

    private final SlotZeitStep slotZeit;


    public CitizenViewPage(RemoteWebDriver driver) {
        super(driver);
        CONTEXT = new CitizenViewPageContext(driver);
        shadow = new ShadowDom(CONTEXT, DEFAULT_EXPLICIT_WAIT_TIME);
        combination = new CombinationStep(CONTEXT, shadow, DEFAULT_EXPLICIT_WAIT_TIME);
        serviceFinder = new ServiceFinderStep(CONTEXT, shadow, combination, DEFAULT_EXPLICIT_WAIT_TIME);
        json = new CitizenViewJson(CONTEXT);
        slotState = new SlotBookingState();
        providerOrt = new ProviderOrtStep(CONTEXT, shadow, json, slotState, DEFAULT_EXPLICIT_WAIT_TIME);
        slotZeit = new SlotZeitStep(CONTEXT, shadow, json, providerOrt, slotState, this, DEFAULT_EXPLICIT_WAIT_TIME);
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
     * True if substring appears anywhere in document + shadow DOM text.
     * Also walks slotted nodes and same-origin frames, and folds whitespace, so a painted
     * callout such as "Sie sind angemeldet." matches even when its text is split across nodes.
     */
    public boolean shadowDomContainsText(String substring) {
        return shadow.shadowDomContainsText(substring);
    }

    /** True when an {@code h1}–{@code h6} in the shadow tree has this exact text. Level 2 is an {@code h2}. */
    private boolean shadowDomHasHeading(int level, String heading) {
        return shadow.shadowDomHasHeading(level, heading);
    }

    /** Heading text that is actually painted. Hidden copies under {@code v-show} do not count. */
    private boolean visibleHeadingShows(int level, String heading) {
        return shadow.visibleHeadingShows(level, heading);
    }

    public void waitUntilShadowContains(String substring, int seconds) {
        shadow.waitUntilShadowContains(substring, seconds);
    }

    public void assertShadowContains(String substring, String message) {
        shadow.assertShadowContains(substring, message);
    }

    /**
     * Find first element matching CSS in document or any shadow root; click via JS.
     */
    public boolean deepClick(String cssSelector) {
        return shadow.deepClick(cssSelector);
    }

    public void deepClickRequired(String cssSelector) {
        shadow.deepClickRequired(cssSelector);
    }

    /** True if an element matching {@code cssSelector} exists in document or any open shadow root. */
    public boolean deepElementExists(String cssSelector) {
        return shadow.deepElementExists(cssSelector);
    }

    public void waitUntilDeepElementExists(String cssSelector, int seconds) {
        shadow.waitUntilDeepElementExists(cssSelector, seconds);
    }

    private boolean deepVisibleCssExists(String cssSelector) {
        return shadow.deepVisibleCssExists(cssSelector);
    }

    /**
     * Set value on input/textarea. {@code muc-input} / {@code muc-text-area} use host ids ({@code firstname},
     * {@code mailaddress}) with the real control inside <strong>shadow DOM</strong>; also tries {@code input-*} ids.
     * Dispatches {@code InputEvent} so Vue v-model updates (plain {@code value=} is not enough).
     */
    public boolean deepSetById(String id, String value) {
        return shadow.deepSetById(id, value);
    }

    /** Current value of the same shadow input {@link #deepSetById(String, String)} writes. */
    public String deepInputValue(String id) {
        return shadow.deepInputValue(id);
    }

    /**
     * True when the resolved input/textarea (or its muc-input / muc-text-area host) is disabled or
     * read-only. Same id resolution as {@link #deepSetById(String, String)}.
     */
    public boolean deepControlDisabled(String id) {
        return shadow.deepControlDisabled(id);
    }

    /** Read value of input/textarea resolved from host id (shadow-safe). */
    public String deepGetById(String id) {
        return shadow.deepGetById(id);
    }

    /**
     * Click first button whose visible text includes label (shadow-safe). Includes BUTTON, A, and MUC-BUTTON (modal confirm/cancel).
     * Skips muc-stepper items ("Zurück zu Schritt: …"); those are not the form Zurück.
     */
    public boolean clickButtonContaining(String text) {
        return shadow.clickButtonContaining(text);
    }

    private boolean clickButtonWithExactText(String text) {
        return shadow.clickButtonWithExactText(text);
    }

    /** Wait up to timeoutSeconds for a clickable button whose text contains label (shadow-safe), then click it. */
    public void waitForAndClickButtonContaining(String label, int timeoutSeconds) {
        shadow.waitForAndClickButtonContaining(label, timeoutSeconds);
    }

    public void clickWeiter() {
        clickWeiter(DEFAULT_EXPLICIT_WAIT_TIME);
    }

    /** Wait up to timeoutSeconds for a clickable Weiter, then click it (e.g. use 30 on Kontakt step). */
    public void clickWeiter(int timeoutSeconds) {
        waitForAndClickButtonContaining(DE_WEITER, timeoutSeconds);
    }


    private boolean deepClickButtonByAriaContains(String fragment) {
        return shadow.deepClickButtonByAriaContains(fragment);
    }

    private boolean deepAriaContains(String fragment) {
        return shadow.deepAriaContains(fragment);
    }

    private boolean deepInfoCalloutContains(String text) {
        return shadow.deepInfoCalloutContains(text);
    }

    private boolean shadowHrefContains(String href) {
        return shadow.shadowHrefContains(href);
    }

    private void assertShadowHref(String href) {
        shadow.assertShadowHref(href);
    }

    private static String mapperQuote(String s) {
        return ShadowDom.mapperQuote(s);
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

    /**
     * Assert that the UI shows an estimated duration with the expected number of minutes. This is a generic shadow-DOM
     * text assertion used for the service combination step, selected-appointment callout, and booking summaries.
     */
    public void assertServiceFinderHeadingVisible() {
        serviceFinder.assertServiceFinderHeadingVisible();
    }

    /** ZMSKVR-84: the start page search box and the frequently requested service links. */
    public void assertServiceSearchAndSuggestions() {
        serviceFinder.assertServiceSearchAndSuggestions();
    }

    public void reloadCitizenView() {
        serviceFinder.reloadCitizenView();
    }

    /** Click the search field. The list opens underneath it. */
    public void clickServiceSearchField() {
        serviceFinder.clickServiceSearchField();
    }

    /**
     * From the Leistung heading, Tab lands on the search field. Enter opens the list.
     * Enter on an already-open list selects a row, so the list must be closed first.
     */
    public void openServiceListWithTabAndEnter() {
        serviceFinder.openServiceListWithTabAndEnter();
    }

    public void assertServiceListOpenUnderField() {
        serviceFinder.assertServiceListOpenUnderField();
    }

    public void assertServiceListAlphabetical() {
        serviceFinder.assertServiceListAlphabetical();
    }

    public void typeIntoServiceSearch(String query) {
        serviceFinder.typeIntoServiceSearch(query);
    }

    public void assertServiceListContainsOnly(String query) {
        serviceFinder.assertServiceListContainsOnly(query);
    }

    public void assertServiceListIncludesAndNot(String present, String absent) {
        serviceFinder.assertServiceListIncludesAndNot(present, absent);
    }

    /** Choose a row in the open list. That opens the Leistung step for the service. */
    public void chooseServiceFromOpenList(String label) {
        serviceFinder.chooseServiceFromOpenList(label);
    }

    /** Full entry: select service via \"Häufig gesuchte Leistungen\" link and navigate to combination step. */
    public void selectServiceByLabel(String serviceLabel) {
        serviceFinder.selectServiceByLabel(serviceLabel);
    }

    private WebElement serviceSearchInput() {
        return serviceFinder.serviceSearchInput();
    }

    private JsonNode waitForFilteredServiceNames(String query) {
        return serviceFinder.waitForFilteredServiceNames(query);
    }

    /** Reopen the list if AfterStep closed it, then type {@code query} with sendKeys. */
    private boolean applyServiceSearchQuery(String query) {
        return serviceFinder.applyServiceSearchQuery(query);
    }

    private JsonNode currentServiceListNames() {
        return serviceFinder.currentServiceListNames();
    }

    /** The search field stays empty until offices-and-services fills its options. */
    private void waitUntilServiceOptionsLoaded() {
        serviceFinder.waitUntilServiceOptionsLoaded();
    }

    private JsonNode waitUntilServiceListOpen() {
        return serviceFinder.waitUntilServiceListOpen();
    }

    private JsonNode serviceSearch(String mode, String text) {
        return serviceFinder.serviceSearch(mode, text);
    }

    /**
     * True once the given service label appears somewhere in the DOM/shadow DOM
     * <em>outside</em> the static "Häufig gesuchte Leistungen" quick-link list.
     * This is a proxy for "offices-and-services have loaded and the label is
     * available in API-backed UI (e.g. select options)".
     */
    private boolean serviceLabelReadyForSelection(String serviceLabel) {
        return serviceFinder.serviceLabelReadyForSelection(serviceLabel);
    }

    private void waitUntilServiceLabelReadyForSelection(String serviceLabel, int seconds) {
        serviceFinder.waitUntilServiceLabelReadyForSelection(serviceLabel, seconds);
    }

    public void assertEstimatedDurationMinutes(int minutes, String context) {
        combination.assertEstimatedDurationMinutes(minutes, context);
    }

    /** Clock illustration beside Voraussichtliche Termindauer on the service combination step. */
    public void assertEstimatedDurationShownWithClock(int minutes) {
        combination.assertEstimatedDurationShownWithClock(minutes);
    }

    /** ZMSKVR-1501: the broken 15-minute mapping showed 135 minutes for a 45-minute service. */
    public void assertEstimatedDurationMinutesNot(int minutes) {
        combination.assertEstimatedDurationMinutesNot(minutes);
    }

    /**
     * Increase the quantity of a subservice by clicking the "+" control on its counter, resolving the subservice by
     * visible name. If the subservice is not yet visible (hidden behind "Alle Leistungen anzeigen"), this method will
     * first click that button once and retry.
     */
    public void addSubserviceByName(String subserviceLabel, int quantity) {
        combination.addSubserviceByName(subserviceLabel, quantity);
    }

    public void increaseSelectedService(String label) {
        combination.increaseSelectedService(label);
    }

    public void decreaseSelectedService(String label) {
        combination.decreaseSelectedService(label);
    }

    /** The first selected service cannot be set to 0. */
    public void assertSelectedServiceCannotDropBelowOne(String label) {
        combination.assertSelectedServiceCannotDropBelowOne(label);
    }

    /** ZMSKVR-106: the service step continues with the label Weiter. */
    public void assertWeiterButtonSays(String label) {
        combination.assertWeiterButtonSays(label);
    }

    /** ZMSKVR-321: heading above the optional combinable peers on the Leistung step. */
    public void assertCombinableServicesHeadingVisible() {
        combination.assertCombinableServicesHeadingVisible();
    }

    /** Visible rows under Kombinierbare Leistungen (not the main selected service above). */
    public void assertCombinableServiceCount(int expected) {
        combination.assertCombinableServiceCount(expected);
    }

    public void assertCombinableServiceCountGreaterThan(int minimum) {
        combination.assertCombinableServiceCountGreaterThan(minimum);
    }

    public void assertShowAllServicesButtonVisible(boolean visible) {
        combination.assertShowAllServicesButtonVisible(visible);
    }

    public void showAllCombinableServices() {
        combination.showAllCombinableServices();
    }

    public void assertSecondaryPlusAndMinus(String label) {
        combination.assertSecondaryPlusAndMinus(label);
    }

    public void assertMinusButton(String label, boolean disabled) {
        combination.assertMinusButton(label, disabled);
    }

    /** ZMSKVR-106: the service name links to its description on muenchen.de. */
    public void assertServiceDescriptionLink(String label, String serviceId) {
        combination.assertServiceDescriptionLink(label, serviceId);
    }

    /** ZMSKVR-249: on a wide window the count and buttons sit left of the service name. */
    public void assertCountBesideNameOnDesktop(String label) {
        combination.assertCountBesideNameOnDesktop(label);
    }

    /** ZMSKVR-249: on a phone the count and buttons sit below the service name. */
    public void assertCountBelowNameOnPhone(String label) {
        combination.assertCountBelowNameOnPhone(label);
    }

    /** Click plus until the service's own maximum disables it. */
    public void raiseServiceUntilPlusDisabled(String label) {
        combination.raiseServiceUntilPlusDisabled(label);
    }

    public void assertPassOnlyCombinationServicesVisible() {
        combination.assertPassOnlyCombinationServicesVisible();
    }

    /** Jump-in: combination step shows Weiter + optional counters. */
    public void assertCombinationStepVisible() {
        combination.assertCombinationStepVisible();
    }

    public void assertServiceCounter(String label, int count) {
        combination.assertServiceCounter(label, count);
    }

    private boolean durationClockIsVisible() {
        return combination.durationClockIsVisible();
    }

    private int countVisibleCombinableServices() {
        return combination.countVisibleCombinableServices();
    }

    private boolean showAllServicesButtonVisible() {
        return combination.showAllServicesButtonVisible();
    }

    private boolean clickShowAllServicesButton() {
        return combination.clickShowAllServicesButton();
    }

    private int displayedServiceCount(String label) {
        return combination.displayedServiceCount(label);
    }

    private JsonNode waitForServiceCounter(String label) {
        return combination.waitForServiceCounter(label);
    }

    private JsonNode queryServiceCounter(String label) {
        return combination.queryServiceCounter(label);
    }

    private boolean weiterButtonIsExact(String label) {
        return combination.weiterButtonIsExact(label);
    }

    private boolean pressServiceCounter(String label, boolean increase) {
        return combination.pressServiceCounter(label, increase);
    }

    private String serviceCounterButtonState(String label, boolean increase) {
        return combination.serviceCounterButtonState(label, increase);
    }

    /**
     * JS helper: try to click the "+" button for a subservice counter with a matching label once. Returns true on
     * success. If the subservice is not found, it will attempt to click "Alle Leistungen anzeigen" once and search
     * again.
     */
    private boolean deepAddSubserviceOnceByName(String subserviceLabel) {
        return combination.deepAddSubserviceOnceByName(subserviceLabel);
    }

    private boolean serviceCounterShows(String label, int count) {
        return combination.serviceCounterShows(label, count);
    }






























    /**
     * ZMSKVR-106: Patternlab secondary buttons. Minus reduces, plus increases, each with its icon.
     */





















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

    public void assertInvalidJumpinRestartButtonVisible() {
        CONTEXT.set();
        int sec = Math.min(15, DEFAULT_EXPLICIT_WAIT_TIME);
        long deadline = System.currentTimeMillis() + sec * 1000L;
        while (System.currentTimeMillis() < deadline) {
            if (invalidJumpinRestartButton(false)) {
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
                invalidJumpinRestartButton(false),
                "Restart button \"Termin vereinbaren\" is not visible on the invalid jump-in callout.");
    }

    public void clickInvalidJumpinRestartButton() {
        CONTEXT.set();
        Assert.assertTrue(
                invalidJumpinRestartButton(true),
                "Could not click \"Termin vereinbaren\" on the invalid jump-in callout.");
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(d -> {
                    String url = d.getCurrentUrl();
                    return url != null && !url.contains("#/services/");
                });
    }

    public void assertAddressHasNoJumpIn() {
        CONTEXT.set();
        String url = DriverUtil.getDriver().getCurrentUrl();
        Assert.assertFalse(
                url != null && url.contains("#/services/"),
                "Jump-in route is still in the address: " + url);
    }

    /**
     * Restart control on the invalid jump-in callout. The painted button can sit in the
     * shadow root of {@code muc-button}, whose host has no box of its own.
     */
    private boolean invalidJumpinRestartButton(boolean click) {
        String script =
                "var click=arguments[0];"
                        + "function box(el){if(!el||el.nodeType!==1||!el.getBoundingClientRect)return false;"
                        + "var r=el.getBoundingClientRect();if(r.width<=0||r.height<=0)return false;"
                        + "var st=window.getComputedStyle(el);return st.visibility!=='hidden'&&st.display!=='none'&&st.opacity!=='0';}"
                        + "function painted(el){if(box(el))return el;var found=null;"
                        + "function w(n){if(!n||found)return;if(n.nodeType===1&&n!==el&&box(n)){found=n;return;}"
                        + "if(n.shadowRoot)w(n.shadowRoot);var c=n.children;if(c)for(var i=0;i<c.length;i++)w(c[i]);}"
                        + "w(el);return found;}"
                        + "function walk(n,fn){if(!n)return false;if(fn(n))return true;"
                        + "if(n.shadowRoot&&walk(n.shadowRoot,fn))return true;"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i],fn))return true;return false;}"
                        + "var target=null;"
                        + "walk(document.body,function(n){"
                        + "var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag!=='MUC-BUTTON'&&tag!=='BUTTON'&&tag!=='A')return false;"
                        + "var label=((n.innerText||n.textContent||'')+'').replace(/\\s+/g,' ').trim();"
                        + "if(label.indexOf('Weiteren')>=0)return false;"
                        + "if(label.indexOf('Termin vereinbaren')<0&&label.indexOf('Book appointment')<0)return false;"
                        + "var hit=painted(n);if(!hit)return false;target=hit;return true;});"
                        + "if(!target)return false;"
                        + "if(click){target.scrollIntoView({block:'center'});target.click();}"
                        + "return true;";
        Object found = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, click);
        return Boolean.TRUE.equals(found);
    }


































































    /**
     * Reserve / preconfirm / confirm screens expose {@code <p id="provider-{officeId}">…</p>} (summary).
     * Asserts that block is present so the appointment is tied to the correct calendar/office.
     */
    public void assertProviderSummaryVisible(int officeId) {
        assertProviderSummaryVisible(officeId, "Bürgerbüro Ruppertstraße");
    }

    /**
     * Text of the <em>visible</em> booking-summary Ort block {@code #provider-{officeId}}.
     * Ignores hidden Ort-step checkboxes that reuse the same id while AppointmentSelection stays
     * mounted under {@code v-show}.
     */
    public String deepVisibleProviderSummaryText(int officeId) {
        CONTEXT.set();
        String script =
                "var want='provider-'+String(arguments[0]);"
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
                        + "function collect(root,out){"
                        + " if(!root)return;"
                        + " if(root.nodeType===1&&root.id===want)out.push(root);"
                        + " if(root.shadowRoot)collect(root.shadowRoot,out);"
                        + " var c=root.children;if(c)for(var i=0;i<c.length;i++)collect(c[i],out);"
                        + "}"
                        + "var found=[];collect(document.body,found);"
                        + "for(var i=0;i<found.length;i++){"
                        + " var el=found[i];"
                        + " if(!visible(el))continue;"
                        + " var tag=(el.tagName||'').toLowerCase();"
                        + " if(tag.indexOf('checkbox')>=0)continue;"
                        + " if(tag==='input')continue;"
                        + " var txt=(el.innerText||el.textContent||'').replace(/\\s+/g,' ').trim();"
                        + " if(txt)return txt;"
                        + "}"
                        + "return null;";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, officeId);
        return o == null ? null : String.valueOf(o);
    }

    public boolean deepVisibleProviderSummaryExists(int officeId) {
        String text = deepVisibleProviderSummaryText(officeId);
        return text != null && !text.isBlank();
    }

    /**
     * Reserve / preconfirm / confirm screens expose {@code <p id="provider-{officeId}">…</p>} (summary).
     * Asserts that the <em>visible</em> summary block contains the standort label (not hidden Ort checkboxes).
     */
    public void assertProviderSummaryVisible(int officeId, String expectedStandortLabel) {
        CONTEXT.set();
        String sel = "#provider-" + officeId;
        CitizenViewWaits.waitWithThreeWindows(
                () -> deepVisibleProviderSummaryExists(officeId), "Provider summary " + sel);
        if (!deepVisibleProviderSummaryExists(officeId)) {
            ScenarioLogManager.getLogger()
                    .warn(
                            "Visible provider summary {} not found after 60s; waiting up to 30s more after deep-link navigation",
                            sel);
            try {
                new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(30))
                        .until(d -> deepVisibleProviderSummaryExists(officeId));
            } catch (TimeoutException e) {
                ScenarioLogManager.getLogger()
                        .warn("Visible provider summary {} still not found after extended wait", sel);
            }
        }
        String summaryText = deepVisibleProviderSummaryText(officeId);
        Assert.assertNotNull(summaryText, "Expected visible booking summary provider block: " + sel);
        Assert.assertTrue(
                summaryText.contains(expectedStandortLabel),
                "Expected standort label in visible summary "
                        + sel
                        + ": "
                        + expectedStandortLabel
                        + " actual="
                        + summaryText);
    }

    /**
     * ZMSKVR-1491 / ZMSKVR-1510: last booking step uses Rechtliche Hinweise.
     * Privacy is a link only. The electronic-communication checkbox stays and is the only checkbox.
     */
    public void assertStandardLegalNotices() {
        CONTEXT.set();
        waitUntilShadowContains("Rechtliche Hinweise", DEFAULT_EXPLICIT_WAIT_TIME);
        Assert.assertTrue(
                shadowDomHasHeading(3, "Rechtliche Hinweise"),
                "Expected h3 Rechtliche Hinweise above Termin reservieren.");
        Assert.assertFalse(
                shadowDomContainsText("Einwilligungen"),
                "The consent heading Einwilligungen should be gone.");
        Assert.assertTrue(
                shadowDomHasHeading(4, "Datenschutz und Datenverarbeitung"),
                "Expected h4 Datenschutz und Datenverarbeitung.");
        Assert.assertTrue(
                shadowDomContainsText("Datenschutzhinweise Terminvereinbarung"),
                "Expected the privacy link text.");
        Assert.assertTrue(
                shadowHrefContains(
                        "https://stadt.muenchen.de/dam/jcr:26e72fa3-cec7-4628-9a0a-272c330a2bd2/23_07_Art_13_DSGVO.pdf"),
                "Expected the shipped privacy PDF link.");
        Assert.assertTrue(
                shadowDomHasHeading(4, "Elektronische Kommunikation"),
                "Expected h4 Elektronische Kommunikation.");
        Assert.assertTrue(
                deepElementExists("#checkbox-electronic-communication"),
                "Expected the electronic communication checkbox.");
        Assert.assertFalse(
                privacyAcknowledgementCheckboxPresent(),
                "Privacy acknowledgement checkbox should be gone.");
    }

    /**
     * Termin-step callout. Telephone and video show only the variant label, with no office name and no icon.
     * On-site variants keep the office name, so {@code exclusive} is {@code no} and this check is skipped.
     */
    public void assertSelectedAppointmentPlaceExclusive(String heading, String exclusive) {
        if (!"yes".equals(exclusive)) {
            return;
        }
        CONTEXT.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(d -> selectedAppointmentPlaceText() != null);
        String text = selectedAppointmentPlaceText();
        Assert.assertEquals(
                text,
                heading,
                "Selected appointment place should be only the variant label.");
        Assert.assertFalse(
                selectedAppointmentPlaceHasIcon(),
                "Selected appointment place should not show a variant icon. text=" + text);
    }

    /** Visible Ort block on the booking overview and on the mail-link overview. */
    public void assertBookingOverviewPlace(int officeId, String heading, String hint) {
        String text = visibleProviderSummaryOrFail(officeId);
        Assert.assertTrue(
                text.contains(heading),
                "Expected place heading in office " + officeId + " summary: " + heading + " actual=" + text);
        Assert.assertTrue(
                text.contains(hint),
                "Expected place hint in office " + officeId + " summary: " + hint + " actual=" + text);
    }

    public void assertBookingOverviewPlaceIncludes(int officeId, String fragment) {
        if (fragment == null || fragment.isBlank()) {
            return;
        }
        String text = visibleProviderSummaryOrFail(officeId);
        Assert.assertTrue(
                text.contains(fragment),
                "Expected place text in office " + officeId + " summary: " + fragment + " actual=" + text);
    }

    public void assertBookingOverviewPlaceExcludes(int officeId, String fragment) {
        if (fragment == null || fragment.isBlank()) {
            return;
        }
        String text = visibleProviderSummaryOrFail(officeId);
        Assert.assertFalse(
                text.contains(fragment),
                "Place for office " + officeId + " should not contain: " + fragment + " actual=" + text);
    }

    /**
     * Videoberatung legal block. {@code yes} requires the three h4 headings and the shipped links.
     * {@code no} requires that the video terms heading is absent.
     */
    public void assertVideoLegalNotices(String legal) {
        CONTEXT.set();
        if (!"yes".equals(legal)) {
            Assert.assertFalse(
                    shadowDomContainsText("Nutzungsbedingungen Videoberatung"),
                    "Video consultation terms should be hidden for this variant.");
            return;
        }
        waitUntilShadowContains("Nutzungsbedingungen Videoberatung", DEFAULT_EXPLICIT_WAIT_TIME);
        Assert.assertTrue(
                shadowDomHasHeading(4, "Datenschutz und Datenverarbeitung"),
                "Expected h4 Datenschutz und Datenverarbeitung.");
        Assert.assertTrue(
                shadowDomHasHeading(4, "Elektronische Kommunikation"),
                "Expected h4 Elektronische Kommunikation.");
        Assert.assertTrue(
                shadowDomHasHeading(4, "Nutzungsbedingungen Videoberatung"),
                "Expected h4 Nutzungsbedingungen Videoberatung.");
        assertShadowHref("https://stadt.muenchen.de/dam/jcr:26e72fa3-cec7-4628-9a0a-272c330a2bd2/23_07_Art_13_DSGVO.pdf");
        assertShadowHref("https://stadt.muenchen.de/dam/DSGVO/Datenschutzhinweise-Videoberatung.pdf");
        assertShadowHref("https://stadt.muenchen.de/infos/elektronische-kommunikation.html");
        assertShadowHref("https://stadt.muenchen.de/dam/DSGVO/Nutzungsbedingungen-Videoberatung.pdf");
    }

    public void assertReserveAppointmentButtonEnabled(boolean enabled) {
        CONTEXT.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(d -> reserveAppointmentButtonState() != null);
        String state = reserveAppointmentButtonState();
        Assert.assertEquals(
                state,
                enabled ? "enabled" : "disabled",
                "Termin reservieren should be " + (enabled ? "enabled" : "disabled") + ".");
    }

    /** After communication alone, Videoberatung stays disabled until the video terms are accepted. */
    public void assertReserveAppointmentButtonAfterCommunication(String legal) {
        assertReserveAppointmentButtonEnabled(!"yes".equals(legal));
    }

    public void assertServiceLinkPointsToMunichDe() {
        CONTEXT.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(d -> shadowHrefContains("https://stadt.muenchen.de/service/info/"));
        Assert.assertTrue(
                shadowHrefContains("https://stadt.muenchen.de/service/info/"),
                "Expected a service link to https://stadt.muenchen.de/service/info/.");
    }

    private String visibleProviderSummaryOrFail(int officeId) {
        CONTEXT.set();
        CitizenViewWaits.waitWithThreeWindows(
                () -> deepVisibleProviderSummaryExists(officeId), "Provider summary #provider-" + officeId);
        String text = deepVisibleProviderSummaryText(officeId);
        Assert.assertNotNull(text, "Expected visible booking summary provider block #provider-" + officeId);
        return text;
    }

    private String selectedAppointmentPlaceText() {
        Object raw =
                ((JavascriptExecutor) DriverUtil.getDriver())
                        .executeScript(selectedAppointmentPlaceScript(false));
        if (raw == null) {
            return null;
        }
        String text = String.valueOf(raw).replaceAll("\\s+", " ").trim();
        return text.isEmpty() ? null : text;
    }

    private boolean selectedAppointmentPlaceHasIcon() {
        Object raw =
                ((JavascriptExecutor) DriverUtil.getDriver())
                        .executeScript(selectedAppointmentPlaceScript(true));
        return Boolean.TRUE.equals(raw);
    }

    private static String selectedAppointmentPlaceScript(boolean icon) {
        String result = icon
                ? "return !!(el.querySelector && el.querySelector('svg,use'));"
                : "return (el.innerText||el.textContent||'').replace(/\\s+/g,' ').trim();";
        return "function visible(el){"
                + " if(!el||el.nodeType!==1)return false;"
                + " var n=el;"
                + " while(n){"
                + "  if(n.nodeType===1){try{var st=getComputedStyle(n);if(st.display==='none'||st.visibility==='hidden')return false;}catch(e0){}}"
                + "  if(n.parentElement){n=n.parentElement;continue;}"
                + "  var root=n.getRootNode&&n.getRootNode();"
                + "  if(root&&root.host){n=root.host;continue;}"
                + "  break;"
                + " }"
                + " try{return el.getClientRects().length>0;}catch(e1){return true;}"
                + "}"
                + "function walk(root){"
                + " if(!root)return null;"
                + " if(root.nodeType===1){"
                + "  var cls=root.className&&root.className.baseVal!==undefined?root.className.baseVal:root.className;"
                + "  if(typeof cls==='string'&&cls.indexOf('m-teaser-contained-contact__summary')>=0&&visible(root)){"
                + "   var el=root;"
                + result
                + "  }"
                + "  if(root.shadowRoot){var s=walk(root.shadowRoot);if(s)return s;}"
                + " }"
                + " var c=root.children;if(c)for(var i=0;i<c.length;i++){var f=walk(c[i]);if(f)return f;}"
                + " return null;"
                + "}"
                + "return walk(document.body);";
    }

    private String reserveAppointmentButtonState() {
        String script =
                "function visible(el){"
                        + " if(!el||el.nodeType!==1)return false;"
                        + " try{var st=getComputedStyle(el);if(st.display==='none'||st.visibility==='hidden')return false;"
                        + " return el.getClientRects().length>0;}catch(e){return true;}"
                        + "}"
                        + "function off(el){"
                        + " if(!el)return false;"
                        + " if(el.disabled===true||(el.hasAttribute&&el.hasAttribute('disabled'))||el.getAttribute('aria-disabled')==='true')return true;"
                        + " var inner=el.shadowRoot&&el.shadowRoot.querySelector&&el.shadowRoot.querySelector('button,[aria-disabled]');"
                        + " return !!(inner&&(inner.disabled===true||inner.hasAttribute('disabled')||inner.getAttribute('aria-disabled')==='true'));"
                        + "}"
                        + "function walk(root){"
                        + " if(!root)return null;"
                        + " if(root.nodeType===1){"
                        + "  var tag=(root.tagName||'').toUpperCase();"
                        + "  var txt=((root.innerText||root.textContent||'')+'').replace(/\\s+/g,' ').trim();"
                        + "  if((tag==='MUC-BUTTON'||tag==='BUTTON')&&txt.indexOf('Termin reservieren')>=0&&visible(root)){"
                        + "   return off(root)?'disabled':'enabled';"
                        + "  }"
                        + "  if(root.shadowRoot){var s=walk(root.shadowRoot);if(s)return s;}"
                        + " }"
                        + " var c=root.children;if(c)for(var i=0;i<c.length;i++){var f=walk(c[i]);if(f)return f;}"
                        + " return null;"
                        + "}"
                        + "return walk(document.body);";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return raw == null ? null : String.valueOf(raw);
    }

    private boolean privacyAcknowledgementCheckboxPresent() {
        String script =
                "function walk(root){"
                        + " if(!root)return false;"
                        + " if(root.nodeType===1){"
                        + "  var tag=(root.tagName||'').toUpperCase();"
                        + "  var type=(root.getAttribute&&root.getAttribute('type')||'').toLowerCase();"
                        + "  if((tag==='INPUT'&&type==='checkbox')||tag.indexOf('CHECKBOX')>=0){"
                        + "   var id=(root.id||'')+' '+(root.getAttribute('name')||'')+' '+(root.getAttribute('aria-label')||'');"
                        + "   var label='';"
                        + "   if(root.id){"
                        + "    var rootNode=root.getRootNode?root.getRootNode():document;"
                        + "    var lab=rootNode.querySelector?rootNode.querySelector('label[for=\"'+root.id+'\"]'):null;"
                        + "    if(lab)label=lab.textContent||'';"
                        + "   }"
                        + "   var blob=(id+' '+label).toLowerCase();"
                        + "   if(blob.indexOf('datenschutz')>=0||blob.indexOf('dsgvo')>=0||blob.indexOf('einwilligung')>=0)return true;"
                        + "  }"
                        + "  if(root.shadowRoot&&walk(root.shadowRoot))return true;"
                        + " }"
                        + " var c=root.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i]))return true;"
                        + " return false;"
                        + "}"
                        + "return walk(document.body);";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return Boolean.TRUE.equals(raw);
    }


    public void waitForPreconfirmPageAfterUpdate() {
        CONTEXT.set();
        String sel = "#checkbox-electronic-communication";
        CitizenViewWaits.waitWithThreeWindows(() -> deepElementExists(sel), "Preconfirm page " + sel);
        Assert.assertTrue(
                deepElementExists(sel),
                "Preconfirm page (electronic communication checkbox " + sel + ") not visible after Kontakt Weiter with retries.");
        ScenarioLogManager.getLogger().info("zmscitizenview: preconfirm page visible");
    }


    /**
     * ZMSKVR-92 / ZMSKVR-164. A finished step keeps its own icon and is the only clickable one
     * ({@code Zurück zu Schritt}). The active step is {@code aria-current=step}. A later step is not a button.
     */
    public void assertBookingStepperLabels() {
        CONTEXT.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(d -> "Leistung".equals(bookingStepLabel(0))
                        && "Termin".equals(bookingStepLabel(1))
                        && "Kontakt".equals(bookingStepLabel(2))
                        && "Übersicht".equals(bookingStepLabel(3)));
        Assert.assertEquals(bookingStepLabel(0), "Leistung", "First booking step.");
        Assert.assertEquals(bookingStepLabel(1), "Termin", "Second booking step.");
        Assert.assertEquals(bookingStepLabel(2), "Kontakt", "Third booking step.");
        Assert.assertEquals(bookingStepLabel(3), "Übersicht", "Fourth booking step.");
    }

    public void assertBookingStep(String label, String state, String icon) {
        CONTEXT.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(d -> bookingStepMatches(label, state, icon));
        Assert.assertTrue(
                bookingStepMatches(label, state, icon),
                "Booking step \"" + label + "\" should be " + state + " with icon " + icon
                        + ". Steps: " + readBookingSteps());
    }

    /**
     * Outline the finished step and leave it. The next step clicks it, so the screenshot after this
     * step still shows the orange mark.
     */
    public void highlightFinishedBookingStep(String label) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: highlight finished booking step {}", label);
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(d -> paintFinishedBookingStep(label));
        Assert.assertTrue(
                paintFinishedBookingStep(label),
                "Finished booking step \"" + label + "\" has no back button. Steps: " + readBookingSteps());
        try {
            Thread.sleep(200L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
    }

    public void clickHighlightedBookingStep() {
        CONTEXT.set();
        JavascriptExecutor js = (JavascriptExecutor) DriverUtil.getDriver();
        Object stored = js.executeScript("return window.__zmsCitizenViewStepperLabel || '';");
        String label = stored instanceof String text ? text : "";
        Assert.assertFalse(label.isBlank(), "No highlighted booking step to click.");
        ScenarioLogManager.getLogger().info("zmscitizenview: click highlighted booking step {}", label);
        Object clicked =
                js.executeScript(
                        "var button=window.__zmsCitizenViewStepperTarget;"
                                + "if(!button)return false;"
                                + "button.scrollIntoView({block:'center'});"
                                + "button.click();"
                                + "window.__zmsCitizenViewStepperTarget=null;"
                                + "return true;");
        Assert.assertTrue(Boolean.TRUE.equals(clicked), "Highlighted booking step could not be clicked.");
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(d -> {
                    JsonNode step = findBookingStep(label);
                    return step != null && step.path("current").asBoolean() && !step.path("done").asBoolean();
                });
    }


    public void assertEnteredContactDetailsStillPresent() {
        CONTEXT.set();
        Assert.assertFalse(lastContactFirstName.isBlank(), "No contact details were entered.");
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(d -> lastContactFirstName.equals(deepGetById("firstname")));
        Assert.assertEquals(deepGetById("firstname"), lastContactFirstName, "Vorname was cleared.");
        Assert.assertEquals(deepGetById("lastname"), lastContactLastName, "Nachname was cleared.");
        String email = deepGetById("mailaddress");
        Assert.assertNotNull(email, "E-Mail could not be read.");
        Assert.assertTrue(
                email.equalsIgnoreCase(lastContactEmail),
                "E-Mail was cleared. expected=" + lastContactEmail + " actual=" + email);
        if (deepContactPhoneFieldExists()) {
            String phone = deepGetById("telephonenumber");
            Assert.assertNotNull(phone, "Telephone could not be read.");
            Assert.assertTrue(
                    phone.contains(lastContactPhone) || phone.replaceAll("\\s+", "").contains("491234567890"),
                    "Telephone was cleared. expected=" + lastContactPhone + " actual=" + phone);
        }
    }





    private boolean bookingStepMatches(String label, String state, String icon) {
        JsonNode step = findBookingStep(label);
        if (step == null || !icon.equals(step.path("icon").asText())) {
            return false;
        }
        boolean current = step.path("current").asBoolean();
        boolean done = step.path("done").asBoolean();
        return switch (state) {
            case "current" -> current && !done;
            case "finished" -> done && !current;
            case "later" -> !done && !current;
            default -> false;
        };
    }

    private String bookingStepLabel(int index) {
        JsonNode steps = readBookingSteps();
        if (steps == null || index < 0 || index >= steps.size()) {
            return "";
        }
        return steps.get(index).path("label").asText();
    }

    private JsonNode findBookingStep(String label) {
        JsonNode steps = readBookingSteps();
        if (steps == null) {
            return null;
        }
        for (JsonNode step : steps) {
            if (label.equals(step.path("label").asText())) {
                return step;
            }
        }
        return null;
    }

    private JsonNode readBookingSteps() {
        String script =
                "function norm(s){return (s||'').replace(/\\s+/g,' ').trim();}"
                        + "function iconOf(li){var use=li.querySelector('use');if(!use)return '';"
                        + "var href=use.getAttribute('href')||use.getAttribute('xlink:href')||'';"
                        + "var mark=href.indexOf('#icon-');return mark>=0?href.substring(mark+6):href;}"
                        + "var found=null;"
                        + "function walk(n){if(!n||found)return;var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag==='OL'&&n.classList&&n.classList.contains('m-form-steps')){found=n;return;}"
                        + "if(n.shadowRoot)walk(n.shadowRoot);"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)walk(c[i]);}"
                        + "walk(document.body);if(!found)return '[]';"
                        + "var items=found.querySelectorAll('li.m-form-step');var steps=[];"
                        + "for(var i=0;i<items.length;i++){var li=items[i];"
                        + "var title=li.querySelector('.m-form-step__title');"
                        + "var button=li.querySelector('button.m-form-step__button');"
                        + "steps.push({label:norm(title?title.textContent:''),icon:iconOf(li),"
                        + "current:li.getAttribute('aria-current')==='step',done:!!button});}"
                        + "return JSON.stringify(steps);";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        if (!(raw instanceof String json) || json.isBlank()) {
            return null;
        }
        try {
            JsonNode node = new ObjectMapper().readTree(json);
            return node.isArray() ? node : null;
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn("zmscitizenview: booking stepper could not be read", e);
            return null;
        }
    }

    private boolean paintFinishedBookingStep(String label) {
        String script =
                "var label=arguments[0];"
                        + "function norm(s){return (s||'').replace(/\\s+/g,' ').trim();}"
                        + "function paint(node){if(!node)return;node.scrollIntoView({block:'center'});"
                        + "try{node.style.outline='4px solid #ffbf00';"
                        + "node.style.outlineOffset='3px';"
                        + "node.style.backgroundColor='rgba(255,191,0,0.25)';}catch(e){}}"
                        + "var found=null;"
                        + "function walk(n){if(!n||found)return;var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag==='OL'&&n.classList&&n.classList.contains('m-form-steps')){found=n;return;}"
                        + "if(n.shadowRoot)walk(n.shadowRoot);"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)walk(c[i]);}"
                        + "walk(document.body);if(!found)return false;"
                        + "var items=found.querySelectorAll('li.m-form-step');"
                        + "for(var i=0;i<items.length;i++){var li=items[i];"
                        + "var title=li.querySelector('.m-form-step__title');"
                        + "if(norm(title?title.textContent:'')!==label)continue;"
                        + "var button=li.querySelector('button.m-form-step__button');"
                        + "if(!button)return false;"
                        + "paint(li);paint(button);"
                        + "window.__zmsCitizenViewStepperTarget=button;"
                        + "window.__zmsCitizenViewStepperLabel=label;"
                        + "return true;}"
                        + "return false;";
        Object painted = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, label);
        return Boolean.TRUE.equals(painted);
    }





    /**
     * Normalize Ort provider checkboxes so only {@code allowedOfficeIds} remain checked.
     * Single-provider teaser layouts have no checkboxes and are left unchanged.
     */
    private JsonNode citizenJson(String expression, Object... args) {
        return json.citizenJson(expression, args);
    }

    public boolean ortStepShowsProvider(int officeId) {
        return providerOrt.ortStepShowsProvider(officeId);
    }

    /** Single-provider layout: teaser headline {@code #provider-{id}} under Ort (no checkboxes). */
    private boolean deepOrtSingleProviderTeaserPresent(int officeId) {
        return providerOrt.deepOrtSingleProviderTeaserPresent(officeId);
    }

    public void assertProviderCheckboxPresent(int officeId) {
        providerOrt.assertProviderCheckboxPresent(officeId);
    }

    public void assertProviderCheckboxAbsent(int officeId) {
        providerOrt.assertProviderCheckboxAbsent(officeId);
    }

    /**
     * Ort checkboxes start selected. Ranked Bürgerbüros follow frequency order. Scheidplatz has no
     * frequency rank in the catalog, so it follows them. Each checkbox also shows its address.
     */
    public void assertOfficesCheckedInFrequencyOrder() {
        providerOrt.assertOfficesCheckedInFrequencyOrder();
    }

    /** One bookable office: a contact tile, no location checkboxes. */
    public void assertSingleOfficeTile(int officeId, String name, String street) {
        providerOrt.assertSingleOfficeTile(officeId, name, street);
    }

    /**
     * Jump-in can pre-select the only provider; clicking again toggles off. True if that office is already on.
     */
    public boolean deepProviderCheckboxChecked(int officeId) {
        return providerOrt.deepProviderCheckboxChecked(officeId);
    }

    public void selectOfficeById(int officeId) {
        providerOrt.selectOfficeById(officeId);
    }

    /**
     * Waits after combination → Ort/ Zeit: multi-provider checkboxes or single-provider teaser.
     */
    public void waitUntilOrtStepShowsProvider(int officeId, int maxSeconds) {
        providerOrt.waitUntilOrtStepShowsProvider(officeId, maxSeconds);
    }

    /**
     * Logs checkbox ids in DOM + whether single-provider teaser matches; asserts expected provider is shown in Ort.
     */
    public void logOrtProviderResolution(int expectedOfficeId) {
        providerOrt.logOrtProviderResolution(expectedOfficeId);
    }

    private JsonNode providerCheckboxes() {
        return providerOrt.providerCheckboxes();
    }

    private JsonNode officeTile(int officeId) {
        return providerOrt.officeTile(officeId);
    }

    private void assertOfficeOrder(JsonNode offices, boolean requireChecked, boolean requireAllKnown) {
        providerOrt.assertOfficeOrder(offices, requireChecked, requireAllKnown);
    }

    private String frequencyName(String text) {
        return providerOrt.frequencyName(text);
    }

    private int frequencyRank(String name) {
        return providerOrt.frequencyRank(name);
    }

    /** Wait until provider-toggle spinner activity has settled (best effort). */
    private void waitUntilProviderToggleSettled(int maxSeconds) {
        providerOrt.waitUntilProviderToggleSettled(maxSeconds);
    }

    /**
     * True when at least one timeslot button for the real booking OfficeID is in the DOM
     * ({@code #provider-{officeId}-timeslot-*} or {@code [data-provider-id="{officeId}"]}).
     */
    public boolean deepTimeslotPresentForProvider(int officeId) {
        return slotZeit.deepTimeslotPresentForProvider(officeId);
    }

    /**
     * Assert no timeslot buttons exist for the given real provider ids (e.g. Ausbildung peer
     * when the selected service is not offered there). Checks the current hour/day-part only.
     */
    public void assertTimeslotsAbsentForProviders(int... officeIds) {
        slotZeit.assertTimeslotsAbsentForProviders(officeIds);
    }

    /**
     * A fitting length shows slots, then the same day in the list, and does not show the empty-day callout.
     */
    public void assertBookableDayInCalendarAndList(int officeId) {
        slotZeit.assertBookableDayInCalendarAndList(officeId);
    }

    /**
     * A length that does not fit leaves the day unselected. The blue info callout is the empty state,
     * and neither the calendar nor the list offers that day.
     */
    public void assertNoBookableDay(int officeId) {
        slotZeit.assertNoBookableDay(officeId);
    }

    public void assertCalendarListToggleShows(String activeLabel) {
        slotZeit.assertCalendarListToggleShows(activeLabel);
    }

    public void assertToggleSitsBesideHeadingOnDesktop() {
        slotZeit.assertToggleSitsBesideHeadingOnDesktop();
    }

    public void assertToggleSitsBelowHeadingOnPhone() {
        slotZeit.assertToggleSitsBelowHeadingOnPhone();
    }

    public void switchToListView() {
        slotZeit.switchToListView();
    }

    public void switchToCalendarView() {
        slotZeit.switchToCalendarView();
    }

    public void assertListDateAccordions(int expectedCount) {
        slotZeit.assertListDateAccordions(expectedCount);
    }

    public void assertOpenListGroupsByHour() {
        slotZeit.assertOpenListGroupsByHour();
    }

    public void assertCalendarGroupsByHour() {
        slotZeit.assertCalendarGroupsByHour();
    }

    public void assertOpenListGroupsByMorning() {
        slotZeit.assertOpenListGroupsByMorning();
    }

    public void assertCalendarGroupsByMorning() {
        slotZeit.assertCalendarGroupsByMorning();
    }

    /** Open hour: each office that has a slot is a map-pin heading, in the same order as the checkboxes. */
    public void assertOpenHourListsOfficesWithMapPin() {
        slotZeit.assertOpenHourListsOfficesWithMapPin();
    }

    /** Früher is a disabled ghost button. Später is an enabled ghost button. */
    public void assertCalendarGhostPagerStartsAtFirstGroup() {
        slotZeit.assertCalendarGhostPagerStartsAtFirstGroup();
    }

    public void moveCalendarHour(boolean later) {
        slotZeit.moveCalendarHour(later);
    }

    /** Clear the first office that is actually listed under the open hour. */
    public void clearFirstShownOffice() {
        slotZeit.clearFirstShownOffice();
    }

    public void assertClearedOfficeIsHidden() {
        slotZeit.assertClearedOfficeIsHidden();
    }

    /** One office shows every group at once, with no location heading and no Früher or Später. */
    public void assertSingleOfficeGroupsTimesWithoutLocationHeadings() {
        slotZeit.assertSingleOfficeGroupsTimesWithoutLocationHeadings();
    }

    public void assertEarlierAndLaterAreEachOnOneLine() {
        slotZeit.assertEarlierAndLaterAreEachOnOneLine();
    }

    public void assertListEarlierStartsDisabled() {
        slotZeit.assertListEarlierStartsDisabled();
    }

    public void moveOpenListHour(boolean later) {
        slotZeit.moveOpenListHour(later);
    }

    public void loadMoreListDates() {
        slotZeit.loadMoreListDates();
    }

    public void openTheNextListDate() {
        slotZeit.openTheNextListDate();
    }

    public void selectVisibleTimeslot() {
        slotZeit.selectVisibleTimeslot();
    }

    public void assertMarkedTimeslotIsWhiteOnBlue() {
        slotZeit.assertMarkedTimeslotIsWhiteOnBlue();
    }

    public void assertPreviousTimeslotIsNotMarked() {
        slotZeit.assertPreviousTimeslotIsNotMarked();
    }

    public void assertTimeslotsPresentForProviders(int... officeIds) {
        slotZeit.assertTimeslotsPresentForProviders(officeIds);
    }

    public void assertAvailableAppointmentsShown() {
        slotZeit.assertAvailableAppointmentsShown();
    }

    /**
     * Skip the slot that was already reserved so the next highlight is a different appointment.
     */
    public void highlightAnotherTimeslotForOffice(int officeId) {
        slotZeit.highlightAnotherTimeslotForOffice(officeId);
    }

    /**
     * Scrolls the provider's time slot grid into the viewport center so {@code @AfterStep} full-page screenshots show
     * the slot area (not only the calendar above the fold). Safe to call after Ort selection when slots exist.
     */
    public void scrollTimeSlotGridIntoViewForScreenshots() {
        slotZeit.scrollTimeSlotGridIntoViewForScreenshots();
    }

    /** True when at least one bookable slot control exists (list or calendar). */
    public boolean deepTimeslotClickablePresent() {
        return slotZeit.deepTimeslotClickablePresent();
    }

    /**
     * MucSpinner lives in {@code .m-spinner-container}; shown while days load and again after a calendar day change
     * until slot API returns ({@code CalendarView.vue}).
     */
    public boolean deepMucSpinnerVisible() {
        return slotZeit.deepMucSpinnerVisible();
    }

    /**
     * Slot API finished: no visible MucSpinner and at least one timeslot control (avoids clicking while day-change
     * spinner runs).
     */
    public boolean deepTimeslotReadyNoSpinner() {
        return slotZeit.deepTimeslotReadyNoSpinner();
    }

    /**
     * Clicks <strong>Später</strong> (later) next to the time slot grid when the earlier/later controls are shown —
     * only when {@code providersWithAppointments.length &gt; 1} (see {@code CalendarView.vue} / {@code ListView.vue}).
     * Moves to the next available hour or PM half-day so slots sit further in the future; no-op if absent/disabled.
     *
     * @return {@code true} if a click was performed
     */
    public boolean clickCitizenViewLaterOnceIfAvailable() {
        return slotZeit.clickCitizenViewLaterOnceIfAvailable();
    }

    /** Wait until slot buttons exist and MucSpinner cleared (calendar day / office fetch). */
    public void waitUntilAppointmentSlotsReady(int maxSeconds) {
        slotZeit.waitUntilAppointmentSlotsReady(maxSeconds);
    }

    /**
     * Step 1 of slot booking: wait until MucSpinner is gone and at least one timeslot exists (see
     * {@link #waitUntilAppointmentSlotsReady(int)}).
     */
    public void waitUntilSlotsReadyForBooking() {
        slotZeit.waitUntilSlotsReadyForBooking();
    }

    /**
     * Step 2: click <strong>Später</strong> beside the time slot grid (hour/day-part navigation) when shown
     * (multi-provider), then wait for slots to reload. No-op if the button is absent or disabled.
     */
    public void clickSpäterIfAvailableAndReloadSlots() {
        slotZeit.clickSpäterIfAvailableAndReloadSlots();
    }

    /**
     * Step 3a: scroll to grid and highlight the preferred timeslot (no click). The next Cucumber step’s
     * {@code @AfterStep} screenshot then shows the orange outline before Vue updates.
     * <p>
     * For shared booking, {@code officeId} is the real slot owner ({@code data-provider-id}), which may differ
     * from the Ort display id. Retries with Später when no matching slot is in the current hour/day-part.
     */
    public void highlightPreferredTimeslotForOffice(int officeId) {
        slotZeit.highlightPreferredTimeslotForOffice(officeId);
    }

    /** Step 3b: click the slot stored by {@link #highlightPreferredTimeslotForOffice(int)}. */
    public void clickHighlightedTimeslotSelection() {
        slotZeit.clickHighlightedTimeslotSelection();
    }

    /**
     * Step 3 (combined): highlight + click — use split steps in features so {@code @AfterStep} captures the slot area.
     */
    public void selectPreferredTimeslotBelowCalendar(int officeId) {
        slotZeit.selectPreferredTimeslotBelowCalendar(officeId);
    }

    /**
     * Step 4: assert {@code Ausgewählter Termin} callout for the office, then <strong>Weiter</strong> to reserve (API).
     */
    public void assertCalloutAndReserveAfterSlotSelection(int officeId) {
        slotZeit.assertCalloutAndReserveAfterSlotSelection(officeId);
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
        slotZeit.scrollClickFirstSlotAssertCalloutWeiter(officeId);
    }

    /**
     * Info callout after slot pick: selected-appointment header + {@code #provider-{officeId}}.
     *
     * @return false when the Kontakt step is already showing, so the caller must not click Weiter again
     */
    public boolean assertSelectedAppointmentCalloutShowsProvider(int officeId) {
        return slotZeit.assertSelectedAppointmentCalloutShowsProvider(officeId);
    }

    public void assertSelectedAppointmentCalloutVisible() {
        slotZeit.assertSelectedAppointmentCalloutVisible();
    }

    private JsonNode locationTitles() {
        return slotZeit.locationTitles();
    }

    private JsonNode calendarGhostButtons() {
        return slotZeit.calendarGhostButtons();
    }

    private void assertGhostButton(JsonNode button, String word, String icon, boolean disabled) {
        slotZeit.assertGhostButton(button, word, icon, disabled);
    }

    private String hourLabelOrEmpty(JsonNode state) {
        return slotZeit.hourLabelOrEmpty(state);
    }

    private JsonNode hourLabel(JsonNode node) {
        return slotZeit.hourLabel(node);
    }

    private JsonNode morningLabel(JsonNode node) {
        return slotZeit.morningLabel(node);
    }

    private void assertHourLabels(JsonNode state) {
        slotZeit.assertHourLabels(state);
    }

    private void assertMorningLabels(JsonNode state) {
        slotZeit.assertMorningLabels(state);
    }

    private JsonNode waitForToggleLabels(String activeLabel) {
        return slotZeit.waitForToggleLabels(activeLabel);
    }

    private void assertToggleColor(JsonNode label, String text, boolean active) {
        slotZeit.assertToggleColor(label, text, active);
    }

    private JsonNode calendarSnapshot() {
        return slotZeit.calendarSnapshot();
    }

    private JsonNode listSnapshot() {
        return slotZeit.listSnapshot();
    }

    private JsonNode waitForListAccordionCount(int expected) {
        return slotZeit.waitForListAccordionCount(expected);
    }

    private String firstHourLabel(JsonNode state) {
        return slotZeit.firstHourLabel(state);
    }

    private JsonNode waitForPagerButtons() {
        return slotZeit.waitForPagerButtons();
    }

    private void assertPagerButton(JsonNode button, String word, boolean disabled) {
        slotZeit.assertPagerButton(button, word, disabled);
    }

    private JsonNode timeslotStyle(String slotId) {
        return slotZeit.timeslotStyle(slotId);
    }

    private void waitUntilCalendarSettled(int officeId, boolean expectSlots) {
        slotZeit.waitUntilCalendarSettled(officeId, expectSlots);
    }

    private boolean timeslotLooksWhiteOnBlue(JsonNode slot) {
        return slotZeit.timeslotLooksWhiteOnBlue(slot);
    }

    /**
     * The Termin step fetches days and slots again after a stepper click. The first wait uses the
     * same budget as the initial calendar load. A spinner that is still up gets one more wait.
     */
    private void waitForSlotsAfterReturningToTermin() {
        slotZeit.waitForSlotsAfterReturningToTermin();
    }

    /**
     * Opens the next bookable day on the citizen calendar. Später only moves within the open day,
     * so an empty evening grid uses the calendar's next-day control instead.
     */
    private boolean openNextCalendarDayAndWaitForSlots() {
        return slotZeit.openNextCalendarDayAndWaitForSlots();
    }

    private boolean clickNextBookableCalendarDay() {
        return slotZeit.clickNextBookableCalendarDay();
    }

    /** Max wait for slot grid + spinner (calendar / office load). */
    private int slotBookingWaitTimeoutSeconds() {
        return slotZeit.slotBookingWaitTimeoutSeconds();
    }

    /** @return false when the current calendar view has no highlightable slot for this office */
    private boolean highlightPreferredTimeslotForOfficeOrAbsent(int officeId, String skippedTimestamps) {
        return slotZeit.highlightPreferredTimeslotForOfficeOrAbsent(officeId, skippedTimestamps);
    }

    /**
     * Clicks the stored slot. Returns false when the slot is gone, so the reserve loop can skip it
     * and try the next timestamp instead of failing the 15s re-highlight wait.
     */
    private boolean clickHighlightedTimeslotSelectionOrGiveUp() {
        return slotZeit.clickHighlightedTimeslotSelectionOrGiveUp();
    }

    private int resolveStoredSlotOfficeId(JavascriptExecutor js) {
        return slotZeit.resolveStoredSlotOfficeId(js);
    }

    private boolean performStoredTimeslotClick(JavascriptExecutor js) {
        return slotZeit.performStoredTimeslotClick(js);
    }

    /**
     * True when a timeslot for {@code officeId} shows primary (selected) styling or the selected-appointment callout
     * is visible — i.e. Vue received {@code selectTimeSlot}.
     */
    private boolean isSlotSelectionVisibleForOffice(int officeId) {
        return slotZeit.isSlotSelectionVisibleForOffice(officeId);
    }

    /** {@code muc-button} with {@code data-variant="primary"} for {@code provider-{officeId}-timeslot-*}. */
    private boolean isTimeslotPrimarySelectedForOffice(int officeId) {
        return slotZeit.isTimeslotPrimarySelectedForOffice(officeId);
    }

    private boolean waitForSlotSelectionVisible(int officeId, int timeoutSeconds) {
        return slotZeit.waitForSlotSelectionVisible(officeId, timeoutSeconds);
    }

    /**
     * Kontakt step is up: heading, voluntary-login box, or the Vorname field.
     * A slow reserve paints this page without a taken-slot error.
     */
    private boolean contactStepReached() {
        return slotZeit.contactStepReached();
    }

    private boolean selectedAppointmentCalloutVisible() {
        return slotZeit.selectedAppointmentCalloutVisible();
    }

    /**
     * Firefox can land on Kontaktdaten before the callout assert. A leftover callout node must not fail the
     * scenario; the caller continues on the contact form and must not click Weiter again.
     */
    private boolean stopBecauseContactStepIsVisible(int officeId) {
        return slotZeit.stopBecauseContactStepIsVisible(officeId);
    }

    private void finishReserveOnContactStep() {
        slotZeit.finishReserveOnContactStep();
    }

    private SlotZeitStep.ReserveOutcome waitForReserveOutcome() {
        return slotZeit.waitForReserveOutcome();
    }

    /** Keep the slot whose Weiter reached Kontakt, not a later highlight. */
    private void keepReservedSlot(Long timestamp) {
        slotZeit.keepReservedSlot(timestamp);
    }

    private long readStoredSlotTimestamp() {
        return slotZeit.readStoredSlotTimestamp();
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
        slotZeit.waitForReserveToSettle();
    }

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

        if (allowed.size() == 1) {
            slotState.lastSlotBookingOfficeId = allowed.iterator().next();
            waitUntilProviderToggleSettled(30);
            try {
                waitUntilAppointmentSlotsReady(Math.min(60, slotBookingWaitTimeoutSeconds()));
            } catch (Exception e) {
                ScenarioLogManager.getLogger()
                        .warn("zmscitizenview: slot wait after provider normalization: {}", e.toString());
            }
        }
    }







































    /** Fixed test phone; never random (avoid real subscriber numbers). */
    public static final String CONTACT_PHONE_E2E = "+491234567890";

    /** Short Lorem for required custom remarks (under 250). */
    private static final String CONTACT_LOREM_REQUIRED =
            "Lorem ipsum dolor sit amet, consectetur adipiscing elit. E2E Pflichtfeld.";

    /** Last Kontakt values filled by {@link #fillContactDetailsRandom()} for later asserts. */
    private String lastContactFirstName = "";
    private String lastContactLastName = "";
    private String lastContactEmail = "";
    private String lastContactPhone = CONTACT_PHONE_E2E;
    private String lastContactCustomText = CONTACT_LOREM_REQUIRED;


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
     * text areas only → {@link #CONTACT_LOREM_REQUIRED}. Also fills optional Bemerkung fields when
     * present.
     */
    public void fillContactDetailsRandom() {
        fillContactDetailsRandom(true);
    }

    /**
     * Like {@link #fillContactDetailsRandom()} but leaves optional Bemerkung empty so a later
     * rebooking onto a scope that requires custom text still has a missing Pflichtfeld (ZMSKVR-833).
     */
    public void fillContactDetailsRandomWithoutOptionalRemarks() {
        fillContactDetailsRandom(false);
    }

    private void fillContactDetailsRandom(boolean fillOptionalRemarks) {
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
        lastContactFirstName = parts[0];
        lastContactLastName = parts[1];
        lastContactEmail = email;
        zms.ataf.rest.steps.CitizenApiSteps.setBookingContactEmail(email);
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
        if (fillOptionalRemarks) {
            fillOptionalContactRemarksIfPresent();
        }
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

    /** ZMSKVR-833: after reserve on rebooking, Kontakt must appear (not skip to Übersicht). */
    public void assertContactFormVisible() {
        CONTEXT.set();
        waitUntilShadowContains("Kontaktdaten", Math.max(30, DEFAULT_EXPLICIT_WAIT_TIME));
        Assert.assertTrue(
                shadowDomContainsText("Kontaktdaten"),
                "Expected Kontakt form (Kontaktdaten) after rebooking to a scope with missing required fields.");
    }

    /** ZMSKVR-833: already provided name/email stay locked; citizen cannot change them. */
    public void assertFilledNameAndEmailLockedOnContactForm() {
        CONTEXT.set();
        waitUntilShadowContains("Kontaktdaten", Math.max(30, DEFAULT_EXPLICIT_WAIT_TIME));
        Assert.assertTrue(
                deepControlDisabled("firstname"),
                "Vorname should be locked on rebooking Kontakt when already filled.");
        Assert.assertTrue(
                deepControlDisabled("lastname"),
                "Nachname should be locked on rebooking Kontakt when already filled.");
        Assert.assertTrue(
                deepControlDisabled("mailaddress"),
                "E-Mail should be locked on rebooking Kontakt when already filled.");
    }

    /** ZMSKVR-833 / ZMSKVR-1025 / ZMSKVR-1648: empty required custom text stays editable. */
    public void assertRequiredCustomTextFieldEditableOnContactForm() {
        CONTEXT.set();
        Assert.assertTrue(
                deepContactCustomTextFieldExists(),
                "Required custom text field (#remarks) must be visible on rebooking Kontakt.");
        Assert.assertFalse(
                deepControlDisabled("remarks"),
                "Required custom text field must stay editable when empty on rebooking.");
    }

    /**
     * Fills missing required Bemerkung on rebooking Kontakt without touching locked name/email.
     */
    public void fillRequiredCustomTextFieldsOnContactForm() {
        CONTEXT.set();
        waitUntilShadowContains("Kontaktdaten", Math.max(30, DEFAULT_EXPLICIT_WAIT_TIME));
        fillRequiredCustomTextAreasInShadow();
        String remarks = deepGetById("remarks");
        if (remarks == null || remarks.isBlank()) {
            Assert.assertTrue(
                    deepSetById("remarks", CONTACT_LOREM_REQUIRED),
                    "Kontakt: could not set required Bemerkung (muc-text-area shadow)");
        }
        ScenarioLogManager.getLogger().info("zmscitizenview: Kontakt — required Bemerkung filled for rebooking");
    }

    /**
     * Pattern Lab hides {@code #checkbox-electronic-communication} ({@code opacity: 0}); the visible
     * target is {@code label[for=...]}. {@link #deepClick} can hit a non-visible match and leave
     * {@code electronicCommunication} false, so Termin verschieben stays disabled.
     */
    public void acceptCommunication() {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: accept electronic communication (visible label)");
        acceptVisibleCheckbox("checkbox-electronic-communication", "Electronic communication");
    }

    /**
     * Videoberatung keeps Termin reservieren disabled until the video terms are accepted.
     * Phone appointments do not show that checkbox.
     */
    public void acceptVideoConsultationTermsIfShown() {
        CONTEXT.set();
        if (!shadowDomContainsText("Nutzungsbedingungen Videoberatung")) {
            ScenarioLogManager.getLogger().info("zmscitizenview: video consultation terms are not shown");
            return;
        }
        ScenarioLogManager.getLogger().info("zmscitizenview: accept video consultation terms (visible label)");
        acceptVisibleCheckbox("checkbox-video-consultation", "Video consultation terms");
    }

    private void acceptVisibleCheckbox(String checkboxId, String label) {
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(d -> clickVisibleCheckboxLabel(checkboxId));
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(d -> isCheckboxChecked(checkboxId));
        Assert.assertTrue(isCheckboxChecked(checkboxId), label + " checkbox was not checked (visible label click).");
    }

    private boolean clickVisibleCheckboxLabel(String checkboxId) {
        String script =
                "var checkboxId=arguments[0];"
                        + "function vis(el){if(!el||!el.getBoundingClientRect)return false;"
                        + "var r=el.getBoundingClientRect();if(r.width<=0||r.height<=0)return false;"
                        + "var st=window.getComputedStyle(el);return st.visibility!=='hidden'&&st.display!=='none'&&st.opacity!=='0';}"
                        + "function walk(root){if(!root)return null;"
                        + "var labels=root.querySelectorAll?root.querySelectorAll('label[for=\"'+checkboxId+'\"]'):[];"
                        + "for(var i=0;i<labels.length;i++)if(vis(labels[i]))return labels[i];"
                        + "var all=root.querySelectorAll?root.querySelectorAll('*'):[];"
                        + "for(var j=0;j<all.length;j++)if(all[j].shadowRoot){var f=walk(all[j].shadowRoot);if(f)return f;}"
                        + "return null;}"
                        + "var lab=walk(document.body);if(!lab)return false;"
                        + "var root=lab.getRootNode?lab.getRootNode():document;"
                        + "var input=root.querySelector?root.querySelector('#'+checkboxId):null;"
                        + "if(input&&input.checked)return true;"
                        + "lab.scrollIntoView({block:'center'});lab.click();return true;";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, checkboxId);
        return Boolean.TRUE.equals(o);
    }

    private boolean isCheckboxChecked(String checkboxId) {
        String script =
                "var checkboxId=arguments[0];"
                        + "function vis(el){if(!el||!el.getBoundingClientRect)return false;"
                        + "var r=el.getBoundingClientRect();if(r.width<=0||r.height<=0)return false;"
                        + "var st=window.getComputedStyle(el);return st.visibility!=='hidden'&&st.display!=='none'&&st.opacity!=='0';}"
                        + "function walk(root){if(!root)return null;"
                        + "var labels=root.querySelectorAll?root.querySelectorAll('label[for=\"'+checkboxId+'\"]'):[];"
                        + "for(var i=0;i<labels.length;i++)if(vis(labels[i])){"
                        + "var rn=labels[i].getRootNode?labels[i].getRootNode():document;"
                        + "return rn.querySelector?rn.querySelector('#'+checkboxId):null;}"
                        + "var all=root.querySelectorAll?root.querySelectorAll('*'):[];"
                        + "for(var j=0;j<all.length;j++)if(all[j].shadowRoot){var f=walk(all[j].shadowRoot);if(f)return f;}"
                        + "return null;}"
                        + "var input=walk(document.body);return !!(input&&input.checked);";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, checkboxId);
        return Boolean.TRUE.equals(o);
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

    /** Preconfirm page: after communication checkbox, primary "Termin reservieren" button leads to activation (“Aktivieren Sie Ihren Termin.”). */
    public void continueFromPreconfirmStep() {
        CONTEXT.set();
        captureBookingProcessForCleanup();
        ScenarioLogManager.getLogger().info("zmscitizenview: preconfirm → Termin reservieren (activation callout)");
        waitForAndClickButtonContaining(DE_RESERVE, DEFAULT_EXPLICIT_WAIT_TIME);
        CitizenViewWaits.waitWithThreeWindows(() -> shadowDomContainsText(ACTIVATION_CALLOUT_HEADING), "Activation callout");
        Assert.assertTrue(
                shadowDomContainsText(ACTIVATION_CALLOUT_HEADING),
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
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadowDomContainsText(ACTIVATION_CALLOUT_HEADING), "Preconfirmation callout heading");
        Assert.assertTrue(
                shadowDomContainsText(ACTIVATION_CALLOUT_HEADING),
                "Preconfirmation warning callout (Aktivieren Sie Ihren Termin.) not found after reserve with retries.");
        String timeText = activationMinutes + " Minuten";
        Assert.assertTrue(shadowDomContainsText(timeText),
                "Preconfirmation callout should mention activation time limit (" + timeText + ").");
        ScenarioLogManager.getLogger().info("zmscitizenview: activation callout visible with {} Minuten", activationMinutes);
    }

    public void assertConfirmationSuccessCalloutVisible() {
        ScenarioLogManager.getLogger().info("zmscitizenview: checking for confirmation success callout (Ihr Termin wurde gebucht.)");
        assertShadowContains(
                CONFIRMATION_SUCCESS_HEADING,
                "Confirmation success callout not found after opening confirm link.");
        ScenarioLogManager.getLogger().info("zmscitizenview: confirmation success callout found");
    }

    /**
     * ZMSKVR-955: logged-in Übersicht books via Termin reservieren and must land on success,
     * not the activation callout. Communication is accepted in the previous Gherkin step so the
     * ATAF 250ms AfterStep delay can enable the button, same as guest preconfirm.
     */
    public void confirmLoggedInBookingFromSummary() {
        CONTEXT.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: logged-in summary → confirm (Termin reservieren)");
        waitForAndClickButtonContaining(DE_RESERVE, DEFAULT_EXPLICIT_WAIT_TIME);
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadowDomContainsText(CONFIRMATION_SUCCESS_HEADING),
                "Logged-in confirmation success");
        Assert.assertTrue(
                shadowDomContainsText(CONFIRMATION_SUCCESS_HEADING),
                "Confirmation success callout (Ihr Termin wurde gebucht.) not visible after logged-in booking.");
        Assert.assertFalse(
                shadowDomContainsText(ACTIVATION_CALLOUT_HEADING),
                "Logged-in booking must not show the activation callout (Aktivieren Sie Ihren Termin.).");
        trySyncBookingProcessFromLocalStorageOnce();
    }

    /**
     * ZMSKVR-965: green success callout after logged-in booking — heading, mail-confirmation
     * body, Termin ansehen, Weiteren Termin vereinbaren.
     */
    public void assertLoggedInConfirmationSuccessDetailsVisible() {
        CONTEXT.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert logged-in confirmation success details");
        assertConfirmationSuccessCalloutVisible();
        Assert.assertTrue(
                shadowDomContainsText(CONFIRMATION_SUCCESS_TEXT),
                "Confirmation success callout must include the booking-confirmation mail text.");
        Assert.assertTrue(
                shadowDomContainsText(VIEW_APPOINTMENT_BUTTON),
                "Logged-in confirmation must show primary action 'Termin ansehen'.");
        Assert.assertTrue(
                shadowDomContainsText(BOOK_ANOTHER_APPOINTMENT_BUTTON),
                "Logged-in confirmation must show secondary action 'Weiteren Termin vereinbaren'.");
    }

    /**
     * ZMSKVR-353: guest rebooking summary books via Termin verschieben and must land on success,
     * not the activation callout. Communication is accepted in the previous Gherkin step so the
     * ATAF 250ms AfterStep delay can enable the button, same as first-booking preconfirm.
     */
    public void confirmRebookingFromSummary() {
        CONTEXT.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: rebooking summary → confirm (Termin verschieben)");
        waitForAndClickButtonContaining(RESCHEDULE_APPOINTMENT_BUTTON, DEFAULT_EXPLICIT_WAIT_TIME);
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadowDomContainsText(CONFIRMATION_SUCCESS_HEADING), "Rebooking confirmation success");
        Assert.assertTrue(
                shadowDomContainsText(CONFIRMATION_SUCCESS_HEADING),
                "Confirmation success callout (Ihr Termin wurde gebucht.) not visible after guest rebooking.");
        Assert.assertFalse(
                shadowDomContainsText(ACTIVATION_CALLOUT_HEADING),
                "Guest rebooking must not show the activation callout (Aktivieren Sie Ihren Termin.).");
        trySyncBookingProcessFromLocalStorageOnce();
    }

    public void assertPreconfirmationCalloutNotVisible() {
        CONTEXT.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: asserting activation callout is hidden ({})", ACTIVATION_CALLOUT_HEADING);
        Assert.assertFalse(
                shadowDomContainsText(ACTIVATION_CALLOUT_HEADING),
                "Activation callout (Aktivieren Sie Ihren Termin.) must not be visible.");
    }

    /** ZMSKVR-1500: MucBanner success after reopening an already-used confirm deep link. */
    public void assertAlreadyActivatedAppointmentBannerVisible() {
        CONTEXT.set();
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: waiting for already-activated MucBanner success ({})",
                        ALREADY_ACTIVATED_BANNER_MARKER);
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadowDomContainsText(ALREADY_ACTIVATED_BANNER_MARKER),
                "Already-activated appointment banner");
        Assert.assertTrue(
                shadowDomContainsText(ALREADY_ACTIVATED_BANNER_MARKER),
                "Already-activated MucBanner success not found after reopening confirm link.");
        ScenarioLogManager.getLogger().info("zmscitizenview: already-activated appointment banner found");
    }

    /**
     * ZMSKVR-1500: banner must stay hidden on the rebooking confirm summary (confirm URL still active).
     * Call after {@link #assertCancelRescheduleButtonVisible()} so view 3 rebooking UI is ready.
     */
    public void assertAlreadyActivatedAppointmentBannerNotVisible() {
        CONTEXT.set();
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: asserting already-activated MucBanner is hidden ({})",
                        ALREADY_ACTIVATED_BANNER_MARKER);
        Assert.assertFalse(
                shadowDomContainsText(ALREADY_ACTIVATED_BANNER_MARKER),
                "Already-activated MucBanner must not remain visible while rescheduling from a confirm link.");
    }

    /** ZMSKVR-1500: start reschedule from already-activated / appointment overview. */
    public void clickRescheduleAppointment() {
        CONTEXT.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: clicking reschedule appointment button ({})", RESCHEDULE_APPOINTMENT_BUTTON);
        waitForAndClickButtonContaining(RESCHEDULE_APPOINTMENT_BUTTON, DEFAULT_EXPLICIT_WAIT_TIME);
    }

    /**
     * Meine Termine detail asks "Verschiebung Ihres Termins" and continues only after Verschieben.
     * The confirm label is exactly Verschieben, so it is not the Termin verschieben action behind the dialog.
     */
    public void rescheduleFromMeineTermine() {
        CONTEXT.set();
        clickRescheduleAppointment();
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadowDomContainsText("Verschiebung Ihres Termins"),
                "Reschedule dialog");
        ScenarioLogManager.getLogger().info("zmscitizenview: confirm reschedule dialog (Verschieben)");
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(d -> clickButtonWithExactText("Verschieben"));
    }

    /** ZMSKVR-1500: rebooking confirm summary shows Verschieben abbrechen. */
    public void assertCancelRescheduleButtonVisible() {
        CONTEXT.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: waiting for cancel-reschedule button ({})", CANCEL_RESCHEDULE_BUTTON);
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadowDomContainsText(CANCEL_RESCHEDULE_BUTTON), "Cancel reschedule button");
        Assert.assertTrue(
                shadowDomContainsText(CANCEL_RESCHEDULE_BUTTON),
                "Cancel reschedule button (Verschieben abbrechen) not found after rebooking slot selection.");
    }

    /**
     * ZMSKVR-1631 / ZMSKVR-1651: Verschieben abbrechen from the Termin step returns to the existing
     * appointment, where both Termin verschieben and Termin absagen are offered again.
     */
    public void assertRescheduleOrCancelActionsVisible() {
        CONTEXT.set();
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: waiting for reschedule and cancel actions ({} / Termin absagen)",
                        RESCHEDULE_APPOINTMENT_BUTTON);
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadowDomContainsText(RESCHEDULE_APPOINTMENT_BUTTON)
                        && shadowDomContainsText("Termin absagen"),
                "Reschedule or cancel actions");
        Assert.assertTrue(
                shadowDomContainsText(RESCHEDULE_APPOINTMENT_BUTTON),
                "Termin verschieben not visible after returning from the Termin step.");
        Assert.assertTrue(
                shadowDomContainsText("Termin absagen"),
                "Termin absagen not visible after returning from the Termin step.");
    }

    /** ZMSKVR-1500: abort reschedule and return to appointment overview. */
    public void clickCancelReschedule() {
        CONTEXT.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: clicking cancel reschedule button ({})", CANCEL_RESCHEDULE_BUTTON);
        waitForAndClickButtonContaining(CANCEL_RESCHEDULE_BUTTON, DEFAULT_EXPLICIT_WAIT_TIME);
    }


    /**
     * Click "Termin absagen". The detail view opens "Absage Ihres Termins"; confirm with "Absagen"
     * before the success callout appears.
     */
    public void clickCancelAppointmentAndConfirm() {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: clicking cancel appointment button (Termin absagen)");
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(d -> clickButtonWithExactText("Termin absagen")
                        || clickButtonContaining("Termin absagen"));
        confirmCancelAppointmentDialogIfShown();
        String marker = CANCELLATION_SUCCESS_HEADING;
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: waiting in 5s + 10s + 15s windows (30s total) for cancellation success callout");
        CitizenViewWaits.waitWithThreeWindows(() -> shadowDomContainsText(marker), "Cancellation success callout");
        Assert.assertTrue(
                shadowDomContainsText(marker),
                "Cancellation success callout (Sie haben Ihren Termin erfolgreich abgesagt.) not visible after Termin absagen with retries.");
    }

    /** Detail view asks for Absagen inside "Absage Ihres Termins" before the appointment is deleted. */
    private void confirmCancelAppointmentDialogIfShown() {
        String heading = "Absage Ihres Termins";
        try {
            new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(5))
                    .until(d -> shadowDomContainsText(heading));
        } catch (TimeoutException e) {
            ScenarioLogManager.getLogger().info("zmscitizenview: cancel dialog was not shown");
            return;
        }
        ScenarioLogManager.getLogger().info("zmscitizenview: confirm cancel dialog (Absagen)");
        waitForAndClickButtonContaining("Absagen", DEFAULT_EXPLICIT_WAIT_TIME);
    }

    public void assertCancellationSuccessCalloutVisible() {
        ScenarioLogManager.getLogger().info("zmscitizenview: checking for cancellation success callout (Sie haben Ihren Termin erfolgreich abgesagt.)");
        assertShadowContains(
                CANCELLATION_SUCCESS_HEADING,
                "Cancellation success callout not found after cancelling appointment.");
        ScenarioLogManager.getLogger().info("zmscitizenview: cancellation success callout found");
    }

    /**
     * ZMSKVR-112 / ZMSKVR-241 / ZMSKVR-1623: success callout heading and thank-you text after Termin absagen.
     */
    public void assertCancellationSuccessDetailsVisible() {
        CONTEXT.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert cancellation success callout heading and thank-you text");
        assertCancellationSuccessCalloutVisible();
        Assert.assertTrue(
                shadowDomContainsText(CANCELLATION_SUCCESS_TEXT),
                "Cancellation success callout must include the thank-you text.");
    }

    /**
     * Reads {@value #LOCALSTORAGE_APPOINTMENT_KEY} and sets {@link zms.ataf.rest.steps.CitizenApiSteps} booking process
     * so mail steps can run after UI preconfirm. If process was already set (e.g. by continueFromPreconfirmStep), returns it.
     */
    public ThinnedProcess syncBookingProcessFromLocalStorage() throws Exception {
        CONTEXT.set();
        ThinnedProcess already = zms.ataf.rest.steps.CitizenApiSteps.getBookingProcess();
        if (hasCancelCredentials(already)) {
            ScenarioLogManager.getLogger().info("zmscitizenview: booking process already set (from reserve step), skipping localStorage read");
            return already;
        }
        captureBookingProcessForCleanup();
        already = zms.ataf.rest.steps.CitizenApiSteps.getBookingProcess();
        if (hasCancelCredentials(already)) {
            return already;
        }
        String json =
                (String)
                        ((JavascriptExecutor) DriverUtil.getDriver())
                                .executeScript(
                                        "return localStorage.getItem('" + LOCALSTORAGE_APPOINTMENT_KEY + "');");
        if (json == null || json.isBlank()) {
            captureBookingProcessForCleanup();
            ThinnedProcess captured = zms.ataf.rest.steps.CitizenApiSteps.getBookingProcess();
            if (captured != null && captured.getProcessId() != null) {
                return captured;
            }
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
        if (hasCancelCredentials(zms.ataf.rest.steps.CitizenApiSteps.getBookingProcess())) {
            return;
        }
        if (trySetBookingProcessFromLocalStorage()) {
            return;
        }
        if (trySetBookingProcessFromVueAppointment()) {
            return;
        }
        trySetBookingProcessFromConfirmLinkOnPage();
    }

    private static boolean hasCancelCredentials(ThinnedProcess process) {
        return process != null
                && process.getProcessId() != null
                && process.getAuthKey() != null
                && !process.getAuthKey().isBlank();
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
        String url = resolveConfirmationDeepLinkUrl();
        ScenarioLogManager.getLogger().info("zmscitizenview: navigating to confirmation URL: {}", url);
        try {
            DriverUtil.getDriver().navigate().to(url);
            // Do not refresh. Same-tab hash routing now handles confirm links (ZMSKVR-1121).
            // navigate()+refresh() can confirm twice and leave activation-expired UI instead of success.
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn("Navigate to confirm URL", e);
        }
        try {
            Thread.sleep(10000L);
        } catch (InterruptedException ie) {
            Thread.currentThread().interrupt();
        }
    }

    /**
     * ZMSKVR-1500: reopen the same confirm deep link after a successful activation.
     * Leaving the confirm hash first is required so the SPA hash watch re-fires; navigating to the
     * identical URL does not remount and would leave the first success callout on screen.
     */
    public void reopenConfirmationDeepLinkInBrowser() {
        CONTEXT.set();
        String confirmUrl = resolveConfirmationDeepLinkUrl();
        String base = confirmUrl;
        int hashIdx = base.indexOf('#');
        if (hashIdx >= 0) {
            base = base.substring(0, hashIdx);
        }
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: reopening confirmation deep link (leave hash, then confirm again)");
        try {
            DriverUtil.getDriver().navigate().to(base + "#/");
            Thread.sleep(1000L);
            DriverUtil.getDriver().navigate().to(confirmUrl);
        } catch (InterruptedException ie) {
            Thread.currentThread().interrupt();
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn("Reopen confirm URL", e);
        }
        try {
            Thread.sleep(10000L);
        } catch (InterruptedException ie) {
            Thread.currentThread().interrupt();
        }
    }

    private String resolveConfirmationDeepLinkUrl() {
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
        return ensureAbsoluteCitizenViewUrl(url);
    }

    /** Navigate to the appointment view URL extracted from the confirmation mail (link without /confirm/). */
    public void openAppointmentViewDeepLinkInBrowser() {
        CONTEXT.set();
        String url = zms.ataf.rest.steps.CitizenApiSteps.getBookingAppointmentUrl();
        if (url == null || url.isBlank()) {
            ScenarioLogManager.getLogger().warn("zmscitizenview: no appointment view URL set; fetch the confirmation mail (second mail) first.");
        }
        Assert.assertNotNull(url, "No appointment view URL; fetch the confirmation mail first.");
        url = ensureAbsoluteCitizenViewUrl(url);
        ScenarioLogManager.getLogger().info("zmscitizenview: navigating to appointment view URL (from second email): {}", url);
        try {
            DriverUtil.getDriver().navigate().to(url);
            // Do not refresh — appointmentHash is applied via hashchange (ZMSKVR-1121).
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn("Navigate to appointment view URL", e);
        }
        waitForAppointmentDetailShellAfterNavigation();
    }

    /**
     * After opening an appointment deep link, wait for a <em>visible</em> shell.
     * Hash booking overview ({@code #/appointment/{hash}}) is AppointmentView ({@code .m-contact});
     * AppointmentDetailView uses {@code #timeTitleElement}. Ignore hidden keep-mounted calendar nodes.
     */
    private void waitForAppointmentDetailShellAfterNavigation() {
        CONTEXT.set();
        try {
            new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                    .until(d -> deepVisibleCssExists(".m-contact") || deepVisibleCssExists("#timeTitleElement"));
            ScenarioLogManager.getLogger()
                    .info("zmscitizenview: visible appointment shell (.m-contact or #timeTitleElement)");
        } catch (TimeoutException e) {
            ScenarioLogManager.getLogger()
                    .warn(
                            "zmscitizenview: visible appointment shell not found after {}s; provider assertion will retry",
                            DEFAULT_EXPLICIT_WAIT_TIME);
        }
    }

    public void fillContactDetailsRandomWithoutContinue() {
        fillContactDetailsRandom();
        lastContactPhone = CONTACT_PHONE_E2E;
        lastContactCustomText = CONTACT_LOREM_REQUIRED;
    }

    public void continueFromContactFormToSummary() {
        CONTEXT.set();
        clickWeiter(30);
        waitForPreconfirmPageAfterUpdate();
    }

    public boolean deepContactCustomTextFieldExists() {
        // MucTextArea binds the control as id="textarea-{prop}" (prop "remarks" is not on the host).
        return deepElementExists("#textarea-remarks")
                || deepElementExists("#remarks")
                || deepElementExists("muc-text-area#remarks")
                || deepElementExists("#input-remarks");
    }

    public boolean deepContactCustomTextField2Exists() {
        return deepElementExists("#textarea-remarks2")
                || deepElementExists("#remarks2")
                || deepElementExists("muc-text-area#remarks2")
                || deepElementExists("#input-remarks2");
    }

    public void assertContactPhoneAndCustomFieldsVisibleWithValues() {
        CONTEXT.set();
        waitUntilShadowContains("Kontaktdaten", Math.max(30, DEFAULT_EXPLICIT_WAIT_TIME));
        Assert.assertTrue(
                deepContactPhoneFieldExists(),
                "Telephone field (#telephonenumber) must remain visible on the Kontakt form.");
        Assert.assertTrue(
                deepContactCustomTextFieldExists(),
                "Custom text field (#remarks) must remain visible on the Kontakt form.");
        Assert.assertTrue(
                deepContactCustomTextField2Exists(),
                "Second custom text field (#remarks2) must remain visible on the Kontakt form.");
        String phone = deepGetById("telephonenumber");
        Assert.assertNotNull(phone, "Telephone field value could not be read.");
        Assert.assertTrue(
                phone.contains(lastContactPhone) || phone.equals(lastContactPhone),
                "Telephone field should keep entered value. expectedContains="
                        + lastContactPhone
                        + " actual="
                        + phone);
        String remarks = deepGetById("remarks");
        Assert.assertNotNull(remarks, "Custom text field value could not be read.");
        Assert.assertTrue(
                remarks.equals(lastContactCustomText) || remarks.contains(lastContactCustomText),
                "Custom text field should keep entered value. expected="
                        + lastContactCustomText
                        + " actual="
                        + remarks);
        String remarks2 = deepGetById("remarks2");
        Assert.assertNotNull(remarks2, "Second custom text field value could not be read.");
        Assert.assertTrue(
                remarks2.equals(lastContactCustomText) || remarks2.contains(lastContactCustomText),
                "Second custom text field should keep entered value. expected="
                        + lastContactCustomText
                        + " actual="
                        + remarks2);
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: Kontakt phone + Zusatzfelder still visible with values");
    }

    /**
     * Booking summary Ort block for Scheidplatz (provider id + name + street from scope address).
     */
    public void assertScheidplatzLocationOnSummary(int officeId) {
        assertProviderSummaryVisible(officeId, "Bürgerbüro Scheidplatz");
        String summaryText = deepVisibleProviderSummaryText(officeId);
        Assert.assertNotNull(summaryText, "Expected visible Scheidplatz summary for provider-" + officeId);
        Assert.assertTrue(
                summaryText.contains("Belgradstraße") || summaryText.contains("Riesenfeldstraße"),
                "Ort address for Scheidplatz should show Belgradstraße or Riesenfeldstraße in visible summary. actual="
                        + summaryText);
        Assert.assertTrue(
                summaryText.contains("80804") && summaryText.contains("München"),
                "Ort address for Scheidplatz should show postal code/city in visible summary. actual="
                        + summaryText);
    }

    /** Zurück from Übersicht (preconfirm) back to Kontakt form. */
    public void goBackFromBookingSummaryToContact() {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: Zurück from booking summary to Kontakt");
        waitForAndClickButtonContaining("Zurück", DEFAULT_EXPLICIT_WAIT_TIME);
        waitUntilShadowContains("Kontaktdaten", Math.max(30, DEFAULT_EXPLICIT_WAIT_TIME));
    }

    /**
     * Bürger-Login on Kontakt: click in-app Anmelden (saves localStorage via requestLogin),
     * complete Keycloak as {@code citizen}/{@code vorschau} (dbs-fragments client), wait until
     * logged-in callout returns. Avoids the host {@code dbs-login} chrome button, which does not
     * persist booking UI state before redirect.
     */
    public void loginViaBuergerLoginWithKeycloak() throws Exception {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: click in-app Anmelden (Bürger-Login)");
        try {
            new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                    .until(d -> clickInAppBuergerLoginAnmelden());
        } catch (TimeoutException e) {
            ScenarioLogManager.getLogger()
                    .warn("zmscitizenview: in-app Anmelden not found; falling back to any Anmelden button");
            waitForAndClickButtonContaining("Anmelden", DEFAULT_EXPLICIT_WAIT_TIME);
        }

        String username =
                TestPropertiesHelper.getPropertyAsString("citizenUserName", true, "citizen");
        String password =
                TestPropertiesHelper.getPropertyAsString("citizenUserPassword", true, "vorschau");
        username = AccountCheckout.assignCitizenLogin(username);
        completeKeycloakLoginForm(username, password);

        CitizenViewWaits.waitWithThreeWindows(
                () -> shadowDomContainsText("Sie sind angemeldet"),
                "Logged-in callout after Keycloak Bürger-Login");
        if (!shadowDomContainsText("Sie sind angemeldet.")) {
            ScenarioLogManager.getLogger()
                    .warn("zmscitizenview: Bürger-Login callout missing; submitting Keycloak again");
            if (!DriverUtil.getDriver().findElements(By.id("username")).isEmpty()) {
                completeKeycloakLoginForm(username, password);
            } else if (shadowDomContainsText("Anmelden")) {
                clickInAppBuergerLoginAnmelden();
                if (!DriverUtil.getDriver().findElements(By.id("username")).isEmpty()) {
                    completeKeycloakLoginForm(username, password);
                }
            }
            CitizenViewWaits.waitWithThreeWindows(
                    () -> shadowDomContainsText("Sie sind angemeldet"),
                    "Logged-in callout after Keycloak Bürger-Login retry");
        }
        Assert.assertTrue(
                shadowDomContainsText("Sie sind angemeldet."),
                "Expected 'Sie sind angemeldet.' after Bürger-Login. Kontakt was still showing Anmelden.");
        ScenarioLogManager.getLogger().info("zmscitizenview: Bürger-Login completed");
        trySetBookingProcessFromPage();
    }

    /**
     * A second booking in the same browser is already logged in. Keycloak may also skip the
     * form when the session is still valid, so do not wait for the username field in that case.
     */
    public void loginViaBuergerLoginWithKeycloakIfNeeded() throws Exception {
        CONTEXT.set();
        if (shadowDomContainsText("Sie sind angemeldet.")) {
            ScenarioLogManager.getLogger().info("zmscitizenview: already logged in, skipping Bürger-Login");
            return;
        }
        ScenarioLogManager.getLogger().info("zmscitizenview: click in-app Anmelden (Bürger-Login, if needed)");
        try {
            new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                    .until(d -> clickInAppBuergerLoginAnmelden());
        } catch (TimeoutException e) {
            waitForAndClickButtonContaining("Anmelden", DEFAULT_EXPLICIT_WAIT_TIME);
        }
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.until(d -> shadowDomContainsText("Sie sind angemeldet.")
                || !d.findElements(By.id("username")).isEmpty());
        if (!shadowDomContainsText("Sie sind angemeldet.")) {
            String username = TestPropertiesHelper.getPropertyAsString("citizenUserName", true, "citizen");
            String password = TestPropertiesHelper.getPropertyAsString("citizenUserPassword", true, "vorschau");
            completeKeycloakLoginForm(AccountCheckout.assignCitizenLogin(username), password);
        }
        CitizenViewWaits.waitWithThreeWindows(() -> shadowDomContainsText("Sie sind angemeldet"), "Logged-in callout");
        Assert.assertTrue(shadowDomContainsText("Sie sind angemeldet."), "Expected 'Sie sind angemeldet.'.");
    }

    /**
     * Local Keycloak has no Abbrechen. After Anmelden reaches the login form, load the citizen
     * origin the way a cancelled IdP does: {@code redirect_uri?error=access_denied} and no code.
     */
    public void cancelBuergerLoginOnKeycloakForm() throws Exception {
        CONTEXT.set();
        String citizenUrl = DriverUtil.getDriver().getCurrentUrl();
        ScenarioLogManager.getLogger().info("zmscitizenview: click in-app Anmelden (Bürger-Login cancel)");
        try {
            new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                    .until(d -> clickInAppBuergerLoginAnmelden());
        } catch (TimeoutException e) {
            ScenarioLogManager.getLogger()
                    .warn("zmscitizenview: in-app Anmelden not found; falling back to any Anmelden button");
            waitForAndClickButtonContaining("Anmelden", DEFAULT_EXPLICIT_WAIT_TIME);
        }

        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        try {
            wait.until(ExpectedConditions.presenceOfElementLocated(By.id("username")));
        } catch (TimeoutException e) {
            throw new TimeoutException(
                    "Keycloak login form (#username) not shown after Anmelden. currentUrl="
                            + DRIVER.getCurrentUrl(),
                    e);
        }
        String returnUrl = accessDeniedReturnUrl(citizenUrl);
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: return from Keycloak as cancelled login url={}", returnUrl);
        DriverUtil.getDriver().navigate().to(returnUrl);
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadowDomContainsText("Kontaktdaten"),
                "Kontakt after cancelled Bürger-Login");
        trySetBookingProcessFromPage();
    }

    /** Citizen OIDC redirect is origin and path only, so a cancel return has no hash and no code. */
    private String accessDeniedReturnUrl(String citizenUrl) {
        String base = citizenUrl == null ? "" : citizenUrl;
        int hash = base.indexOf('#');
        if (hash >= 0) {
            base = base.substring(0, hash);
        }
        int query = base.indexOf('?');
        if (query >= 0) {
            base = base.substring(0, query);
        }
        return base + "?error=access_denied";
    }

    public void assertContactEmailFieldEmpty() {
        CONTEXT.set();
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadowDomContainsText("Kontaktdaten"),
                "Kontakt form for empty email assertion");
        String email = deepInputValue("mailaddress");
        Assert.assertNotNull(email, "Kontakt E-Mail field was not found after cancelled Bürger-Login.");
        Assert.assertTrue(
                email.isBlank(),
                "Kontakt E-Mail should be empty after cancelled Bürger-Login, was: " + email);
        Assert.assertFalse(
                shadowDomContainsText("Sie sind angemeldet."),
                "Cancelled Bürger-Login must not leave the citizen logged in.");
    }

    /**
     * Clicks the Kontakt callout {@code muc-button} labeled Anmelden (not host {@code dbs-login}).
     * Uses pointer+click so Vue {@code @click} → {@code requestLogin} runs (localStorage + OIDC).
     */
    private boolean clickInAppBuergerLoginAnmelden() {
        CONTEXT.set();
        String script =
                "function inDbsLogin(n){while(n){if(n.tagName&&n.tagName.toLowerCase()==='dbs-login')return true;n=n.parentNode;"
                        + "if(n&&n.host)n=n.host;}return false;}"
                        + "function fire(el){el.scrollIntoView({block:'center'});"
                        + "try{el.dispatchEvent(new PointerEvent('pointerdown',{bubbles:true}));}catch(e0){}"
                        + "try{el.dispatchEvent(new MouseEvent('mousedown',{bubbles:true}));}catch(e1){}"
                        + "try{el.dispatchEvent(new PointerEvent('pointerup',{bubbles:true}));}catch(e2){}"
                        + "try{el.dispatchEvent(new MouseEvent('mouseup',{bubbles:true}));}catch(e3){}"
                        + "el.click();return true;}"
                        + "function walk(n){if(!n)return false;if(n.shadowRoot&&walk(n.shadowRoot))return true;"
                        + "var tag=(n.tagName||'').toUpperCase();"
                        + "if((tag==='MUC-BUTTON'||tag==='BUTTON')&&!inDbsLogin(n)){"
                        + "var t=(n.textContent||'').replace(/\\s+/g,' ').trim();"
                        + "if(t.indexOf('Anmelden')>=0&&!n.disabled)return fire(n);}"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i]))return true;return false;}"
                        + "return walk(document.body);";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return Boolean.TRUE.equals(o);
    }

    public void assertCitizenLoggedInOnContactForm() {
        CONTEXT.set();
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadowDomContainsText("Sie sind angemeldet"),
                "Logged-in callout on Kontakt");
        Assert.assertTrue(
                shadowDomContainsText("Sie sind angemeldet."),
                "Expected 'Sie sind angemeldet.' after Bürger-Login on Kontakt form.");
    }

    /**
     * Keycloak username/password + kc-login form submit for the citizen {@code dbs-fragments} client redirect.
     * Does not embed credentials in the browser URL (unlike some admin/statistic Chrome helpers).
     */
    private void completeKeycloakLoginForm(String username, String password) throws Exception {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        try {
            wait.until(ExpectedConditions.presenceOfElementLocated(By.id("username")));
        } catch (TimeoutException e) {
            throw new TimeoutException(
                    "Keycloak login form (#username) not shown after Anmelden. currentUrl="
                            + DRIVER.getCurrentUrl(),
                    e);
        }
        wait.until(ExpectedConditions.presenceOfElementLocated(By.id("kc-login")));
        ScenarioLogManager.getLogger().info("zmscitizenview: Keycloak login form detected url={}", DRIVER.getCurrentUrl());

        ScenarioLogManager.getLogger().info("zmscitizenview: entering Keycloak citizen credentials");
        enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, username, "username", LocatorType.ID);
        enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, password, "password", LocatorType.ID);
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "kc-login", LocatorType.ID, false);
        ScenarioLogManager.getLogger().info("zmscitizenview: Keycloak login submitted");
    }

    /**
     * ZMSKVR-1630 / ZMSKVR-1030: full reload of {@code #/appointment/{id+authKey}} so resume uses
     * the reserved process instead of leftover localStorage view state.
     */
    public void reloadReservedAppointmentHash() {
        CONTEXT.set();
        trySetBookingProcessFromPage();
        String url = resolveReservedAppointmentHashUrl();
        String current = DriverUtil.getDriver().getCurrentUrl();
        boolean alreadyOnReservedHash = current != null
                && current.contains("#/appointment/")
                && !current.contains("#/appointment/confirm/");
        ScenarioLogManager.getLogger().info("zmscitizenview: reload reserved appointment hash {}", url);
        try {
            if (!alreadyOnReservedHash) {
                DriverUtil.getDriver().navigate().to(url);
            }
            DriverUtil.getDriver().navigate().refresh();
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn("Reload reserved appointment hash", e);
        }
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadowDomContainsText("Kontaktdaten")
                        || deepElementExists("#checkbox-electronic-communication")
                        || shadowDomContainsText("Sie sind angemeldet"),
                "Reserved hash resume after reload");
    }

    public void assertAppointmentManagementActionsNotVisible() {
        CONTEXT.set();
        Assert.assertFalse(
                shadowDomContainsText(RESCHEDULE_APPOINTMENT_BUTTON),
                "Reserved hash resume must not show Termin verschieben (confirmed-appointment management).");
        Assert.assertFalse(
                shadowDomContainsText(CANCEL_RESCHEDULE_BUTTON),
                "Reserved hash resume must not show Verschieben abbrechen (rebooking).");
    }

    public void assertElectronicCommunicationCheckboxVisible() {
        CONTEXT.set();
        CitizenViewWaits.waitWithThreeWindows(
                () -> deepElementExists("#checkbox-electronic-communication"),
                "Electronic communication checkbox on book overview");
        Assert.assertTrue(
                deepElementExists("#checkbox-electronic-communication"),
                "Expected #checkbox-electronic-communication on the book/overview after reserved hash resume.");
    }

    private String resolveReservedAppointmentHashUrl() {
        String current = DriverUtil.getDriver().getCurrentUrl();
        if (current != null) {
            int hashIdx = current.indexOf("#/appointment/");
            if (hashIdx >= 0 && !current.contains("#/appointment/confirm/")) {
                return current;
            }
        }
        ThinnedProcess process = zms.ataf.rest.steps.CitizenApiSteps.getBookingProcess();
        Assert.assertNotNull(process, "No booking process for reserved hash; login or reserve first.");
        Assert.assertNotNull(process.getProcessId(), "Booking process has no processId for reserved hash.");
        Assert.assertNotNull(process.getAuthKey(), "Booking process has no authKey for reserved hash.");
        String payload =
                "{\"id\":"
                        + process.getProcessId()
                        + ",\"authKey\":"
                        + mapperQuote(process.getAuthKey())
                        + "}";
        String b64 = Base64.getEncoder().encodeToString(payload.getBytes(StandardCharsets.UTF_8));
        String base = CONTEXT.lastCitizenViewUrl != null ? CONTEXT.lastCitizenViewUrl : "";
        int hashIdx = base.indexOf('#');
        if (hashIdx >= 0) {
            base = base.substring(0, hashIdx);
        }
        if (current != null && (base == null || base.isBlank())) {
            int currentHash = current.indexOf('#');
            base = currentHash >= 0 ? current.substring(0, currentHash) : current;
        }
        return ensureAbsoluteCitizenViewUrl(base + "#/appointment/" + b64);
    }

    /**
     * Capture processId/authKey from localStorage, sessionStorage, or {@code #/appointment/{hash}}
     * so After-hook cancellation can free the reserved slot.
     */
    public void captureBookingProcessForCleanup() {
        CONTEXT.set();
        trySetBookingProcessFromPage();
    }

    void trySetBookingProcessFromPage() {
        if (trySetBookingProcessFromLocalStorage()) {
            return;
        }
        if (trySetBookingProcessFromSessionAuthHash()) {
            return;
        }
        if (trySetBookingProcessFromCurrentReservedHash()) {
            return;
        }
        if (trySetBookingProcessFromVueAppointment()) {
            return;
        }
        if (trySetBookingProcessFromCapturedApiResponse()) {
            return;
        }
        trySetBookingProcessIdFromDom();
    }

    /** processId|authKey remembered from the reserve or update response. */
    private boolean trySetBookingProcessFromCapturedApiResponse() {
        CONTEXT.set();
        Object raw =
                ((JavascriptExecutor) DriverUtil.getDriver())
                        .executeScript("return window.__zmsCapturedBooking || null;");
        if (!(raw instanceof String captured) || !captured.contains("|")) {
            return false;
        }
        int split = captured.indexOf('|');
        String idText = captured.substring(0, split);
        String authKey = captured.substring(split + 1);
        try {
            int processId = Integer.parseInt(idText);
            if (processId <= 0 || authKey.isBlank()) {
                return false;
            }
            ThinnedProcess p = new ThinnedProcess();
            p.setProcessId(processId);
            p.setAuthKey(authKey);
            zms.ataf.rest.steps.CitizenApiSteps.setBookingProcess(p);
            ScenarioLogManager.getLogger()
                    .info("zmscitizenview: captured booking process from appointment response (processId={})", processId);
            return true;
        } catch (NumberFormatException e) {
            ScenarioLogManager.getLogger().debug("zmscitizenview: captured appointment process id was not numeric", e);
            return false;
        }
    }

    /**
     * Guest preconfirm keeps processId and authKey on the Vue appointment, not in localStorage.
     * The summary DOM only exposes the process id.
     */
    private boolean trySetBookingProcessFromVueAppointment() {
        CONTEXT.set();
        String script =
                "function creds(state){"
                        + " if(!state)return null;"
                        + " var raw=state.appointment;"
                        + " var v=raw&&raw.__v_isRef?raw.value:raw;"
                        + " if(v&&v.processId&&v.authKey)return {processId:v.processId,authKey:String(v.authKey)};"
                        + " return null;"
                        + "}"
                        + "function walkInst(inst,depth,seen){"
                        + " if(!inst||depth>80||seen.has(inst))return null;"
                        + " seen.add(inst);"
                        + " var hit=creds(inst.setupState)||creds(inst.exposed);"
                        + " if(hit)return hit;"
                        + " return inst.subTree?walkNode(inst.subTree,depth+1,seen):null;"
                        + "}"
                        + "function walkNode(node,depth,seen){"
                        + " if(!node||depth>80)return null;"
                        + " if(node.component){var a=walkInst(node.component,depth+1,seen);if(a)return a;}"
                        + " var kids=node.children;"
                        + " if(kids&&kids.length)for(var k=0;k<kids.length;k++){"
                        + "  var b=walkNode(kids[k],depth+1,seen);if(b)return b;"
                        + " }"
                        + " return null;"
                        + "}"
                        + "function walkDom(root,seen){"
                        + " if(!root||!root.querySelectorAll)return null;"
                        + " var nodes=root.querySelectorAll('*');"
                        + " for(var i=0;i<nodes.length;i++){"
                        + "  var el=nodes[i];"
                        + "  if(el._instance){var hit=walkInst(el._instance,0,seen);if(hit)return hit;}"
                        + "  if(el.shadowRoot){var inner=walkDom(el.shadowRoot,seen);if(inner)return inner;}"
                        + " }"
                        + " return null;"
                        + "}"
                        + "var seen=new Set();"
                        + "return walkDom(document.body,seen)||walkDom(document.documentElement,seen);";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        if (!(raw instanceof java.util.Map<?, ?> map)) {
            return false;
        }
        Object idRaw = map.get("processId");
        Object keyRaw = map.get("authKey");
        if (idRaw == null || keyRaw == null) {
            return false;
        }
        try {
            int processId = idRaw instanceof Number number
                    ? number.intValue()
                    : Integer.parseInt(String.valueOf(idRaw));
            String authKey = String.valueOf(keyRaw);
            if (processId <= 0 || authKey.isBlank()) {
                return false;
            }
            ThinnedProcess p = new ThinnedProcess();
            p.setProcessId(processId);
            p.setAuthKey(authKey);
            zms.ataf.rest.steps.CitizenApiSteps.setBookingProcess(p);
            ScenarioLogManager.getLogger()
                    .info("zmscitizenview: captured booking process from Vue appointment (processId={})", processId);
            return true;
        } catch (NumberFormatException e) {
            ScenarioLogManager.getLogger().debug("zmscitizenview: Vue appointment process id was not numeric", e);
            return false;
        }
    }

    /** Summary nodes are {@code process-{id}-displayNumber-*}. The id is enough to match GET /mails/. */
    private void trySetBookingProcessIdFromDom() {
        CONTEXT.set();
        String script =
                "function walk(root){if(!root)return null;"
                        + "if(root.id){var m=String(root.id).match(/^process-(\\d+)-/);if(m)return m[1];}"
                        + "if(root.shadowRoot){var s=walk(root.shadowRoot);if(s)return s;}"
                        + "var c=root.children;if(c)for(var i=0;i<c.length;i++){var f=walk(c[i]);if(f)return f;}"
                        + "return null;}"
                        + "return walk(document.body);";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        if (raw == null) {
            return;
        }
        try {
            int processId = Integer.parseInt(String.valueOf(raw));
            if (processId <= 0) {
                return;
            }
            ThinnedProcess existing = zms.ataf.rest.steps.CitizenApiSteps.getBookingProcess();
            if (existing != null && processId == (existing.getProcessId() == null ? -1 : existing.getProcessId())) {
                return;
            }
            ThinnedProcess p = new ThinnedProcess();
            p.setProcessId(processId);
            zms.ataf.rest.steps.CitizenApiSteps.setBookingProcess(p);
            ScenarioLogManager.getLogger()
                    .info("zmscitizenview: captured booking processId={} from summary DOM", processId);
        } catch (NumberFormatException e) {
            ScenarioLogManager.getLogger().debug("zmscitizenview: summary process id was not numeric", e);
        }
    }

    private boolean trySetBookingProcessFromSessionAuthHash() {
        CONTEXT.set();
        Object raw =
                ((JavascriptExecutor) DriverUtil.getDriver())
                        .executeScript("return sessionStorage.getItem('lhm-appointment-auth-hash');");
        return raw instanceof String && setBookingProcessFromAppointmentHash((String) raw);
    }

    private boolean trySetBookingProcessFromCurrentReservedHash() {
        CONTEXT.set();
        String current = DriverUtil.getDriver().getCurrentUrl();
        if (current == null) {
            return false;
        }
        int idx = current.indexOf("#/appointment/");
        if (idx < 0 || current.contains("#/appointment/confirm/")) {
            return false;
        }
        String rest = current.substring(idx + "#/appointment/".length());
        int end = rest.indexOf('?');
        if (end >= 0) {
            rest = rest.substring(0, end);
        }
        return setBookingProcessFromAppointmentHash(rest);
    }

    private boolean setBookingProcessFromAppointmentHash(String hash) {
        if (hash == null || hash.isBlank()) {
            return false;
        }
        String b64 = hash.trim();
        int padding = (4 - (b64.length() % 4)) % 4;
        if (padding > 0) {
            b64 = b64 + "=".repeat(padding);
        }
        try {
            String decoded = new String(Base64.getDecoder().decode(b64), StandardCharsets.UTF_8);
            JsonNode node = new ObjectMapper().readTree(decoded);
            JsonNode idNode = node.path("id");
            JsonNode keyNode = node.path("authKey");
            if (idNode.isMissingNode() || keyNode.isMissingNode() || idNode.isNull() || keyNode.isNull()) {
                return false;
            }
            ThinnedProcess p = new ThinnedProcess();
            p.setProcessId(idNode.asInt());
            p.setAuthKey(keyNode.asText());
            zms.ataf.rest.steps.CitizenApiSteps.setBookingProcess(p);
            ScenarioLogManager.getLogger()
                    .info("zmscitizenview: captured booking process from appointment hash (processId={})", p.getProcessId());
            return true;
        } catch (Exception e) {
            ScenarioLogManager.getLogger().debug("zmscitizenview: could not parse appointment hash", e);
            return false;
        }
    }

    private static final ZoneId BERLIN = ZoneId.of("Europe/Berlin");
    private static final DateTimeFormatter TEASER_DATE_TIME =
            DateTimeFormatter.ofPattern("EEEE, dd.MM.uuuu, HH:mm", Locale.GERMAN);
    private static final DateTimeFormatter ICS_DATE_TIME = DateTimeFormatter.ofPattern("yyyyMMdd'T'HHmmss");
    private static final String ICS_DOWNLOAD_LABEL = "Termin herunterladen (ics)";

    private Long slotState.rememberedAppointmentEpoch;
    private String capturedIcs;

    /**
     * Unix time of the slot that reached Kontakt. Falls back to {@code window.__zmsCitizenViewSlotId}
     * when this scenario did not go through the reserve retry.
     */
    public void rememberSelectedAppointmentTime() {
        CONTEXT.set();
        if (slotState.rememberedAppointmentEpoch == null || slotState.rememberedAppointmentEpoch <= 0) {
            long timestamp = readStoredSlotTimestamp();
            Assert.assertTrue(timestamp > 0, "Selected timeslot id has no timestamp.");
            slotState.rememberedAppointmentEpoch = timestamp;
        }
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: remembered appointment time {}", slotState.rememberedAppointmentEpoch);
    }

    public void openMeineTermine() {
        CONTEXT.set();
        // Always cache-bust: Firefox/Edge treat navigate() to the same overview URL as a no-op,
        // and cancel can leave the DBS session showing only Anmelden on a blank overview.
        String overview = meineTermineOverviewUrl() + "?r=" + System.currentTimeMillis();
        ScenarioLogManager.getLogger().info("zmscitizenview: open Meine Termine {}", overview);
        navigateToMeineTermine(overview);
        if (!browserIsOnMeineTermine()) {
            ScenarioLogManager.getLogger()
                    .warn(
                            "zmscitizenview: Meine Termine stayed on {}; opening it again",
                            DriverUtil.getDriver().getCurrentUrl());
            navigateToMeineTermine(overview);
        }
        waitUntilNeueTerminVisible();
    }

    public void assertMeineTermineLists(String... serviceNames) {
        CONTEXT.set();
        waitUntilNeueTerminVisible();
        for (String serviceName : serviceNames) {
            waitForTeaserText(serviceName);
            Assert.assertEquals(
                    countTeasers(serviceName),
                    1,
                    "Meine Termine should list \"" + serviceName + "\" once.");
        }
    }

    public void assertMeineTermineDoesNotList(String serviceName) {
        CONTEXT.set();
        waitUntilNeueTerminVisible();
        Assert.assertTrue(
                meineTermineFinishedLoading(),
                "Meine Termine did not finish loading.");
        Assert.assertEquals(
                countTeasers(serviceName),
                0,
                "Meine Termine still lists \"" + serviceName + "\".");
    }

    private void waitUntilNeueTerminVisible() {
        String loadError = "Ihre Termine können zur Zeit nicht geladen werden.";
        for (int attempt = 1; attempt <= 5; attempt++) {
            // Full-page hop from booking: dbs-login may show Mein Bereich from localStorage
            // while Vue still waits for authorization-event. Reloading that window kills the
            // session republish and leaves a blank overview (no Neuer Termin, no Anmelden).
            long deadline = System.currentTimeMillis() + 45_000L;
            while (System.currentTimeMillis() < deadline) {
                if (meineTermineFinishedLoading()) {
                    return;
                }
                if (meineTermineShowsLoggedOut()) {
                    ScenarioLogManager.getLogger()
                            .warn(
                                    "zmscitizenview: Meine Termine shows Anmelden (attempt {}/5); logging in again",
                                    attempt);
                    reloginForMeineTermine();
                    break;
                }
                if (shadowDomContainsText(loadError)) {
                    ScenarioLogManager.getLogger()
                            .warn(
                                    "zmscitizenview: Meine Termine appointment load error (attempt {}/5)",
                                    attempt);
                    break;
                }
                if (meineTermineAwaitingAuthOrSkeleton()) {
                    requestMeineTermineAuthReplay();
                }
                try {
                    Thread.sleep(250L);
                } catch (InterruptedException e) {
                    Thread.currentThread().interrupt();
                    return;
                }
            }
            if (meineTermineFinishedLoading()) {
                return;
            }
            if (attempt == 5) {
                break;
            }
            ScenarioLogManager.getLogger()
                    .warn(
                            "zmscitizenview: Meine Termine has no Neuer Termin (attempt {}/5)",
                            attempt);
            reloadMeineTermine();
        }
        Assert.assertTrue(
                meineTermineFinishedLoading(),
                "Meine Termine did not show Neuer Termin after opening the overview.");
    }

    /**
     * Header already says Mein Bereich (or skeleton is painting) but the overview CE has not
     * mounted "Neuer Termin" yet — wait, do not reload.
     */
    private boolean meineTermineAwaitingAuthOrSkeleton() {
        if (!browserIsOnMeineTermine()) {
            return false;
        }
        if (deepElementExists(".skeleton-loader")) {
            return true;
        }
        return shadowDomContainsText("Mein Bereich")
                && !shadowDomContainsText("Neuer Termin")
                && !shadowDomContainsText("Kommende Termine")
                && !shadowDomContainsText("Anmelden");
    }

    /** Ask local-dbs-login to re-emit authorization-event for a late Vue listener. */
    private void requestMeineTermineAuthReplay() {
        try {
            ((JavascriptExecutor) DriverUtil.getDriver())
                    .executeScript(
                            "document.dispatchEvent(new CustomEvent('authorization-event-subscribe'));");
        } catch (Exception ignored) {
            // Best-effort; wait loop continues.
        }
    }

    private boolean meineTermineFinishedLoading() {
        if (shadowDomContainsText("Neuer Termin")) {
            return true;
        }
        // Empty overview still shows Kommende Termine (0) once the skeleton is gone.
        return shadowDomContainsText("Kommende Termine (0)") && !deepElementExists(".skeleton-loader");
    }

    /** Overview only mounts content when logged in; cancel sometimes drops the DBS session. */
    private boolean meineTermineShowsLoggedOut() {
        return browserIsOnMeineTermine()
                && shadowDomContainsText("Anmelden")
                && !shadowDomContainsText("Mein Bereich")
                && !shadowDomContainsText("Neuer Termin")
                && !shadowDomContainsText("Kommende Termine");
    }

    /**
     * Host {@code dbs-login} Anmelden on the overview (no Kontakt callout). Reuse the checked-out
     * citizen so the same appointments stay visible after cancel.
     */
    private void reloginForMeineTermine() {
        try {
            if (!clickInAppBuergerLoginAnmelden()) {
                waitForAndClickButtonContaining("Anmelden", DEFAULT_EXPLICIT_WAIT_TIME);
            }
            WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
            wait.until(d -> shadowDomContainsText("Mein Bereich")
                    || shadowDomContainsText("Neuer Termin")
                    || !d.findElements(By.id("username")).isEmpty());
            if (!DriverUtil.getDriver().findElements(By.id("username")).isEmpty()) {
                String username =
                        TestPropertiesHelper.getPropertyAsString("citizenUserName", true, "citizen");
                String password =
                        TestPropertiesHelper.getPropertyAsString("citizenUserPassword", true, "vorschau");
                completeKeycloakLoginForm(AccountCheckout.assignCitizenLogin(username), password);
            }
            CitizenViewWaits.waitWithThreeWindows(
                    () -> shadowDomContainsText("Mein Bereich")
                            || shadowDomContainsText("Neuer Termin")
                            || shadowDomContainsText("Kommende Termine"),
                    "Meine Termine after re-login");
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn("zmscitizenview: Meine Termine re-login failed", e);
        }
        if (!browserIsOnMeineTermine() || !meineTermineFinishedLoading()) {
            reloadMeineTermine();
        }
    }

    private String meineTermineOverviewUrl() {
        String current = DriverUtil.getDriver().getCurrentUrl();
        Assert.assertTrue(current != null && !current.isBlank(), "Citizen view URL is missing.");
        int hash = current.indexOf('#');
        String withoutHash = hash >= 0 ? current.substring(0, hash) : current;
        // Strip a previous cache-bust query so we always rebuild from the path.
        int query = withoutHash.indexOf('?');
        if (query >= 0) {
            withoutHash = withoutHash.substring(0, query);
        }
        int slash = withoutHash.lastIndexOf('/');
        return withoutHash.substring(0, slash + 1) + "appointment-overview.html";
    }

    private boolean browserIsOnMeineTermine() {
        String current = DriverUtil.getDriver().getCurrentUrl();
        return current != null && current.contains("appointment-overview");
    }

    private void navigateToMeineTermine(String overview) {
        try {
            DriverUtil.getDriver().navigate().to(overview);
        } catch (TimeoutException e) {
            ScenarioLogManager.getLogger().warn("Meine Termine navigation timed out, continuing.", e);
        }
    }

    /**
     * Opening the overview URL again does nothing when Firefox is already on that page, so a
     * load that never rendered "Neuer Termin" stays put. Use a fresh query string instead.
     */
    private void reloadMeineTermine() {
        String overview = meineTermineOverviewUrl() + "?r=" + System.currentTimeMillis();
        ScenarioLogManager.getLogger()
                .warn("zmscitizenview: opening Meine Termine again {}", overview);
        navigateToMeineTermine(overview);
    }

    public void rememberMeineTermineAppointment(String serviceName) {
        CONTEXT.set();
        String number = appointmentNumberOnMeineTermine(serviceName);
        Assert.assertFalse(number.isBlank(), "Meine Termine has no number for \"" + serviceName + "\".");
        TestDataHelper.setTestData(meineTermineNumberKey(serviceName), number);
    }

    public void assertMeineTermineAppointmentReplaced(String serviceName) {
        CONTEXT.set();
        String previous = TestDataHelper.getTestData(meineTermineNumberKey(serviceName));
        String current = appointmentNumberOnMeineTermine(serviceName);
        Assert.assertFalse(current.isBlank(), "Meine Termine has no number for \"" + serviceName + "\".");
        Assert.assertNotEquals(
                current,
                previous,
                "Meine Termine still shows the original appointment for \"" + serviceName + "\".");
    }

    public void assertMeineTermineAppointmentUnchanged(String serviceName) {
        CONTEXT.set();
        String previous = TestDataHelper.getTestData(meineTermineNumberKey(serviceName));
        String current = appointmentNumberOnMeineTermine(serviceName);
        Assert.assertEquals(
                current,
                previous,
                "Meine Termine changed the appointment for \"" + serviceName + "\".");
    }

    private String appointmentNumberOnMeineTermine(String serviceName) {
        String script =
                "var name=arguments[0];"
                        + "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=textOf(n.shadowRoot);var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=textOf(c[i]);return s;}"
                        + "function walk(n,fn){if(!n)return null;if(n.nodeType===1){var hit=fn(n);if(hit)return hit;}"
                        + "if(n.shadowRoot){var inner=walk(n.shadowRoot,fn);if(inner)return inner;}"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++){var next=walk(c[i],fn);if(next)return next;}return null;}"
                        + "return walk(document.body,function(el){"
                        + "if(!el.classList||!el.classList.contains('card'))return null;"
                        + "var text=textOf(el).replace(/\\s+/g,' ').trim();"
                        + "if(text.indexOf('1x '+name)<0)return null;"
                        + "var match=text.match(/Terminnummer:\\s*(\\S+)/);"
                        + "return match?match[1]:'';});";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, serviceName);
        return raw == null ? "" : raw.toString();
    }

    private static String meineTermineNumberKey(String serviceName) {
        return "meine_termine_number_" + serviceName;
    }

    /** Patternlab MucCard renders a {@code div.card}, and the title wraps onto a second line. */
    private int countTeasers(String serviceName) {
        String script =
                "var name=arguments[0];"
                        + "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=textOf(n.shadowRoot);var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=textOf(c[i]);return s;}"
                        + "function walk(n,fn){if(!n)return;if(n.nodeType===1)fn(n);if(n.shadowRoot)walk(n.shadowRoot,fn);"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)walk(c[i],fn);}"
                        + "var n=0;walk(document.body,function(el){"
                        + "if(!el.classList||!el.classList.contains('card'))return;"
                        + "if(textOf(el).replace(/\\s+/g,' ').indexOf('1x '+name)>=0)n++;});"
                        + "return n;";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, serviceName);
        if (raw instanceof Number number) {
            return number.intValue();
        }
        return 0;
    }

    public void assertMeineTermineTeaser(String serviceName, String typeLabel, String locationText) {
        CONTEXT.set();
        Assert.assertNotNull(slotState.rememberedAppointmentEpoch, "Selected appointment time was not remembered.");
        ZonedDateTime when = Instant.ofEpochSecond(slotState.rememberedAppointmentEpoch).atZone(BERLIN);
        String dateTime = TEASER_DATE_TIME.format(when);
        String monthStem = when.format(DateTimeFormatter.ofPattern("MMM", Locale.GERMAN))
                .replace(".", "")
                .substring(0, 3)
                .toUpperCase(Locale.GERMAN);
        String day = Integer.toString(when.getDayOfMonth());
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: assert teaser {} type {} place {} at {}",
                        serviceName,
                        typeLabel,
                        locationText,
                        dateTime);
        String card = waitForTeaserText(serviceName);
        Assert.assertTrue(card.contains(typeLabel), "Teaser is missing type \"" + typeLabel + "\". Text: " + card);
        Assert.assertTrue(
                card.contains("1x " + serviceName),
                "Teaser title is missing \"1x " + serviceName + "\". Text: " + card);
        Assert.assertTrue(
                card.contains(locationText),
                "Teaser place is missing \"" + locationText + "\". Text: " + card);
        Assert.assertTrue(card.contains(dateTime), "Teaser time is missing \"" + dateTime + "\". Text: " + card);
        Assert.assertTrue(card.contains("Uhr"), "Teaser time is missing \"Uhr\". Text: " + card);
        Assert.assertTrue(
                card.toUpperCase(Locale.GERMAN).contains(day) && card.toUpperCase(Locale.GERMAN).contains(monthStem),
                "Teaser calendar leaf is missing " + day + " " + monthStem + ". Text: " + card);
    }

    public void openMeineTermineTeaser(String serviceName) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: open teaser {}", serviceName);
        // Match the teaser title ("1x …"), same as countTeasers / assertMeineTermineTeaser.
        String needle = "1x " + serviceName;
        // Firefox often accepts a muc-card click without navigating. Prefer the card href.
        String findHref =
                "var name=arguments[0];"
                        + "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=textOf(n.shadowRoot);var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=textOf(c[i]);return s;}"
                        + "function walk(n,fn){if(!n)return false;if(fn(n))return true;if(n.shadowRoot&&walk(n.shadowRoot,fn))return true;"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i],fn))return true;return false;}"
                        + "var card=null;"
                        + "walk(document.body,function(n){"
                        + "var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag!=='MUC-CARD'&&tag!=='A')return false;"
                        + "if(textOf(n).replace(/\\s+/g,' ').indexOf(name)<0)return false;"
                        + "card=n;return true;});"
                        + "if(!card)return '';"
                        + "var href=(card.href&&String(card.href))||(card.getAttribute&&card.getAttribute('href'))||'';"
                        + "if(!href&&card.shadowRoot){var a=card.shadowRoot.querySelector('a[href]');"
                        + "if(a)href=a.href||a.getAttribute('href')||'';}"
                        + "if(!href&&(card.tagName||'').toUpperCase()==='A')href=card.href||card.getAttribute('href')||'';"
                        + "return href||'';";
        String clickCard =
                "var name=arguments[0];"
                        + "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=textOf(n.shadowRoot);var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=textOf(c[i]);return s;}"
                        + "function walk(n,fn){if(!n)return false;if(fn(n))return true;if(n.shadowRoot&&walk(n.shadowRoot,fn))return true;"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i],fn))return true;return false;}"
                        + "var card=null;"
                        + "walk(document.body,function(n){"
                        + "var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag!=='MUC-CARD'&&tag!=='A')return false;"
                        + "if(textOf(n).replace(/\\s+/g,' ').indexOf(name)<0)return false;"
                        + "card=n;return true;});"
                        + "if(!card)return false;"
                        + "var hit=card;"
                        + "if(card.shadowRoot){var a=card.shadowRoot.querySelector('a[href]');if(a)hit=a;}"
                        + "hit.scrollIntoView({block:'center'});hit.click();return true;";
        // Detail can take well over 5s under a busy chrome shard; a short settle + immediate
        // re-navigate reloads the page before Termin absagen appears.
        long deadline = System.currentTimeMillis() + Math.max(90, DEFAULT_EXPLICIT_WAIT_TIME) * 1000L;
        boolean opened = false;
        while (System.currentTimeMillis() < deadline) {
            if (shadowDomContainsText("Termin absagen")) {
                opened = true;
                break;
            }
            Object hrefRaw =
                    ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(findHref, needle);
            String href = hrefRaw == null ? "" : String.valueOf(hrefRaw).trim();
            if (href.isEmpty()) {
                if (meineTermineShowsLoggedOut()) {
                    ScenarioLogManager.getLogger()
                            .warn(
                                    "zmscitizenview: Meine Termine shows Anmelden before opening teaser; logging in again");
                    reloginForMeineTermine();
                } else if (!browserIsOnMeineTermine() || !meineTermineFinishedLoading()) {
                    ScenarioLogManager.getLogger()
                            .warn(
                                    "zmscitizenview: returning to Meine Termine before opening teaser {}",
                                    serviceName);
                    reloadMeineTermine();
                    waitUntilNeueTerminVisible();
                } else {
                    waitForTeaserText(serviceName);
                }
                hrefRaw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(findHref, needle);
                href = hrefRaw == null ? "" : String.valueOf(hrefRaw).trim();
            }
            if (!href.isEmpty()) {
                try {
                    if (href.startsWith("/")) {
                        String current = DriverUtil.getDriver().getCurrentUrl();
                        int slash = current.indexOf('/', current.indexOf("://") + 3);
                        href = (slash > 0 ? current.substring(0, slash) : current) + href;
                    }
                    DriverUtil.getDriver().navigate().to(href);
                } catch (TimeoutException e) {
                    ScenarioLogManager.getLogger().warn("Meine Termine teaser navigation timed out", e);
                }
            } else {
                ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(clickCard, needle);
            }
            CitizenViewWaits.waitWithThreeWindows(
                    () -> shadowDomContainsText("Termin absagen"),
                    "Appointment detail after opening teaser " + serviceName);
            if (shadowDomContainsText("Termin absagen")) {
                opened = true;
                break;
            }
            ScenarioLogManager.getLogger()
                    .warn(
                            "zmscitizenview: teaser {} did not open detail; trying again",
                            serviceName);
            if (!browserIsOnMeineTermine()) {
                reloadMeineTermine();
                waitUntilNeueTerminVisible();
            }
        }
        Assert.assertTrue(
                opened || shadowDomContainsText("Termin absagen"),
                "Appointment detail did not show Termin absagen after opening \"" + serviceName + "\".");
    }

    /**
     * ZMSKVR-1538: intro tagline and place, then the Ort section for a phone or video appointment.
     * {@code extra} is the video delay hint; phone leaves it blank.
     */
    public void assertAppointmentDetailLocation(
            String typeLabel, String place, String locationText, String preparationHint, String extra) {
        CONTEXT.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert appointment detail location for {}", typeLabel);
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadowDomContainsText(locationText) && shadowDomHasHeading(2, "Ort"),
                "Ort section on the appointment detail");
        Assert.assertTrue(shadowDomHasHeading(2, "Ort"), "The detail page is missing the Ort heading.");
        Assert.assertTrue(
                shadowDomContainsText(typeLabel),
                "Detail intro is missing \"" + typeLabel + "\".");
        Assert.assertTrue(shadowDomContainsText(place), "Detail place is missing \"" + place + "\".");
        Assert.assertTrue(
                shadowDomContainsText(locationText),
                "Ort section is missing \"" + locationText + "\".");
        Assert.assertTrue(
                shadowDomContainsText(preparationHint),
                "Ort section is missing \"" + preparationHint + "\".");
        if (extra != null && !extra.isBlank()) {
            Assert.assertTrue(shadowDomContainsText(extra), "Ort section is missing \"" + extra + "\".");
        }
        if ("Videoberatung".equals(typeLabel)) {
            Assert.assertTrue(
                    shadowDomContainsText("Info-Seite zur Videoberatung"),
                    "Ort section is missing the video consultation info link.");
            Assert.assertFalse(
                    shadowDomContainsText("Wir rufen Sie unter der von Ihnen angegebenen Nummer an:"),
                    "Video detail should not show the telephone location text.");
        } else {
            Assert.assertFalse(
                    shadowDomContainsText("Info-Seite zur Videoberatung"),
                    "Phone detail should not show the video consultation info link.");
        }
    }

    private String waitForTeaserText(String serviceName) {
        long deadline = System.currentTimeMillis() + Math.max(30, DEFAULT_EXPLICIT_WAIT_TIME) * 1000L;
        String last = "";
        while (System.currentTimeMillis() < deadline) {
            last = teaserText(serviceName);
            if (last.contains(serviceName) && last.contains("Terminnummer")) {
                return last;
            }
            try {
                Thread.sleep(400L);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
                break;
            }
        }
        Assert.fail("Meine Termine teaser for \"" + serviceName + "\" did not appear. Last text: " + last);
        return last;
    }

    private String teaserText(String serviceName) {
        String script =
                "var name=arguments[0];"
                        + "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=textOf(n.shadowRoot);var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=textOf(c[i]);return s;}"
                        + "function walk(n,fn){if(!n)return false;if(fn(n))return true;if(n.shadowRoot&&walk(n.shadowRoot,fn))return true;"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i],fn))return true;return false;}"
                        + "var best='';"
                        + "walk(document.body,function(n){"
                        + "if(n.nodeType!==1)return false;"
                        + "var t=textOf(n);"
                        + "if(t.indexOf(name)<0||t.indexOf('Terminnummer')<0)return false;"
                        + "if(!best||t.length<best.length)best=t;"
                        + "return false;});"
                        + "return best;";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, serviceName);
        return raw instanceof String ? (String) raw : "";
    }

    /** ZMSKVR-1334: intro link "Termin herunterladen (ics)" on the appointment detail page. */
    public void assertIcsDownloadOfferedOnDetailIntro() {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: assert ICS download in the detail intro");
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadowDomContainsText(ICS_DOWNLOAD_LABEL),
                "ICS download link in the appointment detail intro");
        Assert.assertTrue(
                shadowDomContainsText(ICS_DOWNLOAD_LABEL),
                "The appointment detail intro should offer \"" + ICS_DOWNLOAD_LABEL + "\".");
    }

    /**
     * ZMSKVR-1334: the link builds a calendar blob in the browser instead of navigating. Capture that
     * blob so the scenario can check the file without depending on the download folder.
     */
    public void downloadAppointmentIcs() {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: download appointment ICS");
        RemoteWebDriver driver = DriverUtil.getDriver();
        ((JavascriptExecutor) driver)
                .executeScript(
                        "window.__zmsCapturedIcs='';"
                                + "if(!window.__zmsIcsHooked){"
                                + "window.__zmsIcsHooked=true;"
                                + "var NativeBlob=window.Blob;"
                                + "function CapturingBlob(parts,options){"
                                + "var blob=new NativeBlob(parts,options);"
                                + "var type=options&&options.type?String(options.type):'';"
                                + "if(type.indexOf('text/calendar')>=0){"
                                + "window.__zmsCapturedIcs=Array.prototype.map.call(parts,function(p){"
                                + "return typeof p==='string'?p:'';}).join('');}"
                                + "return blob;}"
                                + "CapturingBlob.prototype=NativeBlob.prototype;"
                                + "window.Blob=CapturingBlob;}");
        String clickScript =
                "var label=arguments[0];"
                        + "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=textOf(n.shadowRoot);var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=textOf(c[i]);return s;}"
                        + "function walk(n,fn){if(!n)return false;if(fn(n))return true;if(n.shadowRoot&&walk(n.shadowRoot,fn))return true;"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i],fn))return true;return false;}"
                        + "var hit=null;"
                        + "walk(document.body,function(n){"
                        + "var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag!=='MUC-LINK'&&tag!=='A')return false;"
                        + "if(textOf(n).indexOf(label)<0)return false;"
                        + "hit=n;return true;});"
                        + "if(!hit)return false;"
                        + "hit.scrollIntoView({block:'center'});hit.click();return true;";
        boolean clicked = false;
        long deadline = System.currentTimeMillis() + DEFAULT_EXPLICIT_WAIT_TIME * 1000L;
        while (System.currentTimeMillis() < deadline && !clicked) {
            Object result = ((JavascriptExecutor) driver).executeScript(clickScript, ICS_DOWNLOAD_LABEL);
            clicked = Boolean.TRUE.equals(result);
            if (!clicked) {
                try {
                    Thread.sleep(300L);
                } catch (InterruptedException e) {
                    Thread.currentThread().interrupt();
                    break;
                }
            }
        }
        Assert.assertTrue(clicked, "Could not click \"" + ICS_DOWNLOAD_LABEL + "\".");
        new WebDriverWait(driver, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(
                        d -> {
                            Object raw =
                                    ((JavascriptExecutor) d)
                                            .executeScript("return window.__zmsCapturedIcs || '';");
                            return raw instanceof String && !((String) raw).isBlank();
                        });
        Object raw = ((JavascriptExecutor) driver).executeScript("return window.__zmsCapturedIcs || '';");
        capturedIcs = raw instanceof String ? (String) raw : "";
        Assert.assertFalse(capturedIcs.isBlank(), "The ICS download did not produce a calendar file.");
    }

    /**
     * ZMSKVR-1334: the file must carry the fields a calendar app needs for this appointment.
     * The mail template puts the request name in SUMMARY, not a fixed "München-Termin" label.
     */
    public void assertDownloadedIcsContainsBookedAppointment() {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: assert downloaded ICS contents");
        Assert.assertNotNull(capturedIcs, "No ICS file was downloaded.");
        Assert.assertTrue(capturedIcs.contains("BEGIN:VCALENDAR"), "ICS is missing BEGIN:VCALENDAR.");
        Assert.assertTrue(capturedIcs.contains("BEGIN:VEVENT"), "ICS is missing BEGIN:VEVENT.");
        Assert.assertTrue(capturedIcs.contains("END:VCALENDAR"), "ICS is missing END:VCALENDAR.");
        Assert.assertTrue(
                capturedIcs.contains("SUMMARY:") && capturedIcs.contains("Abholung Personalausweis"),
                "ICS SUMMARY should name the booked service. Was: " + icsSummaryLine());
        Assert.assertTrue(capturedIcs.contains("LOCATION:"), "ICS is missing LOCATION.");
        Assert.assertTrue(
                capturedIcs.contains("DTEND;TZID=Europe/Berlin:"), "ICS is missing DTEND.");
        Assert.assertTrue(
                slotState.rememberedAppointmentEpoch != null && slotState.rememberedAppointmentEpoch > 0,
                "The booked appointment time was not remembered.");
        String start =
                Instant.ofEpochSecond(slotState.rememberedAppointmentEpoch).atZone(BERLIN).format(ICS_DATE_TIME);
        Assert.assertTrue(
                capturedIcs.contains("DTSTART;TZID=Europe/Berlin:" + start),
                "ICS DTSTART should be the booked slot " + start + ".");
    }

    private String icsSummaryLine() {
        if (capturedIcs == null) {
            return "";
        }
        return capturedIcs.lines().filter(line -> line.startsWith("SUMMARY:")).findFirst().orElse("");
    }
}
