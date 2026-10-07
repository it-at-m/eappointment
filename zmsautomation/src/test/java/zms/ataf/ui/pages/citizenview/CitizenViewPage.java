package zms.ataf.ui.pages.citizenview;

import java.time.Duration;
import java.util.Set;

import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import com.fasterxml.jackson.databind.JsonNode;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.pages.BasePage;
import ataf.web.utils.DriverUtil;
import zms.ataf.rest.dto.zmscitizenapi.ThinnedProcess;
import zms.ataf.ui.pages.citizenview.support.ShadowDom;
import zms.ataf.ui.pages.citizenview.steps.CaptchaStep;
import zms.ataf.ui.pages.citizenview.steps.CombinationStep;
import zms.ataf.ui.pages.citizenview.steps.ServiceFinderStep;
import zms.ataf.ui.pages.citizenview.steps.ProviderLocationStep;
import zms.ataf.ui.pages.citizenview.steps.TimeSlotStep;
import zms.ataf.ui.pages.citizenview.steps.BookingStepperStep;
import zms.ataf.ui.pages.citizenview.steps.JumpInErrorStep;
import zms.ataf.ui.pages.citizenview.steps.ContactStep;
import zms.ataf.ui.pages.citizenview.steps.OverviewStep;
import zms.ataf.ui.pages.citizenview.steps.MyAppointmentsStep;
import zms.ataf.ui.pages.citizenview.support.CitizenViewJson;
import zms.ataf.ui.pages.citizenview.support.SlotBookingState;

public class CitizenViewPage extends BasePage {

    /** Same key as zmscitizenview LOCALSTORAGE_PARAM_APPOINTMENT_DATA */
    public static final String LOCALSTORAGE_APPOINTMENT_KEY = "lhm-appointment-data";

    private static final String DE_WEITER = "Weiter";
    private static final String DE_BACK = "Zurück";
    private static final String DE_RESTART_BOOKING = "Buchung neu starten";
    private static final String DE_RESERVATION_EXPIRED_HEADER = "Ihr Termin kann nicht mehr reserviert werden.";
    private static final String DE_RESERVATION_EXPIRED_TEXT =
            "Leider ist die Zeit für die Reservierung Ihres Termins abgelaufen. Bitte vereinbaren Sie den Termin erneut.";
    private static final String ALREADY_ACTIVATED_BANNER_MARKER =
            "Sie haben Ihren Termin bereits aktiviert.";
    private static final String CONFIRMATION_SUCCESS_TEXT =
            "Eine Bestätigung und weitere Informationen zu Ihrem Termin erhalten Sie per E-Mail. Wir freuen uns auf Ihren Besuch.";
    private static final String CANCELLATION_SUCCESS_HEADING =
            "Sie haben Ihren Termin erfolgreich abgesagt.";
    private static final String CANCELLATION_SUCCESS_TEXT =
            "Danke, dass Sie Ihren Termin für andere freigegeben haben.";

    /** German invalid jump-in callout ({@code de-DE.json}). */
    public static final String DE_INVALID_JUMPIN_HEADER = "Diese Ansicht kann nicht geladen werden.";

    public static final String DE_INVALID_JUMPIN_TEXT =
            "Der Link zu dieser Seite ist leider fehlerhaft. Starten Sie die Terminvereinbarung neu";


    public static final String EN_INVALID_JUMPIN_HEADER = "This view cannot be loaded.";
    public static final String EN_INVALID_JUMPIN_TEXT =
            "The link to this page is unfortunately incorrect";

    /** German appointment-not-available callout ({@code de-DE.json} apiErrorAppointmentNotAvailable*). */
    public static final String DE_APPOINTMENT_NOT_AVAILABLE_HEADER =
            "Ihr gewählter Termin ist nicht mehr verfügbar.";

    public static final String DE_APPOINTMENT_NOT_AVAILABLE_TEXT =
            "Leider hat inzwischen eine andere Person Ihren gewünschten Termin gebucht. Bitte wählen Sie einen neuen Termin aus.";

    private final CitizenViewPageContext CONTEXT;

    private final ShadowDom shadow;

    private final CombinationStep combination;

    private final ServiceFinderStep serviceFinder;

    private final CitizenViewJson json;

    private final SlotBookingState slotState;

    private final ProviderLocationStep providerLocation;

    private final TimeSlotStep timeSlot;

    private final BookingStepperStep bookingStepper;

    private final JumpInErrorStep jumpInError;

    private final ContactStep contact;

    private final OverviewStep overview;

    private final MyAppointmentsStep myAppointments;

    private final CaptchaStep captcha;


    public CitizenViewPage(RemoteWebDriver driver) {
        super(driver);
        CONTEXT = new CitizenViewPageContext(driver);
        shadow = new ShadowDom(CONTEXT, DEFAULT_EXPLICIT_WAIT_TIME);
        combination = new CombinationStep(CONTEXT, shadow, DEFAULT_EXPLICIT_WAIT_TIME);
        serviceFinder = new ServiceFinderStep(CONTEXT, shadow, combination, DEFAULT_EXPLICIT_WAIT_TIME);
        json = new CitizenViewJson(CONTEXT);
        slotState = new SlotBookingState();
        providerLocation = new ProviderLocationStep(CONTEXT, shadow, json, slotState, DEFAULT_EXPLICIT_WAIT_TIME);
        timeSlot = new TimeSlotStep(CONTEXT, shadow, json, providerLocation, slotState, this, DEFAULT_EXPLICIT_WAIT_TIME);
        providerLocation.setSlotWaitBridge(
                seconds -> timeSlot.waitUntilAppointmentSlotsReady(seconds),
                () -> timeSlot.slotBookingWaitTimeoutSeconds(),
                () -> timeSlot.deepMucSpinnerVisible());
        bookingStepper = new BookingStepperStep(CONTEXT, shadow, DEFAULT_EXPLICIT_WAIT_TIME);
        jumpInError = new JumpInErrorStep(CONTEXT, shadow, DEFAULT_EXPLICIT_WAIT_TIME);
        contact = new ContactStep(CONTEXT, shadow, this, DEFAULT_EXPLICIT_WAIT_TIME);
        overview = new OverviewStep(CONTEXT, shadow, this, DEFAULT_EXPLICIT_WAIT_TIME);
        myAppointments = new MyAppointmentsStep(CONTEXT, shadow, slotState, this, contact, DEFAULT_EXPLICIT_WAIT_TIME);
        captcha = new CaptchaStep(CONTEXT, shadow, DEFAULT_EXPLICIT_WAIT_TIME);
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

    /** True when a visible button's own text is exactly {@code label}, ignoring stepper "Zurück zu Schritt". */
    private boolean visibleButtonTextEquals(String label) {
        CONTEXT.set();
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
                        + "if(t===label&&visible(n))return true;}"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i]))return true;return false;}"
                        + "return walk(document.body);";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return Boolean.TRUE.equals(o);
    }

    /**
     * Quantity shown beside a selected service. The patternlab counter is a Vue component, so the DOM has the number
     * and the service name as text (or an input value) inside the appointment shadow root, not a muc-counter tag.
     */
    private String counterValueBeside(String serviceName) {
        CONTEXT.set();
        String esc = serviceName.replace("\\", "\\\\").replace("'", "\\'");
        String script =
                "var name='" + esc + "';"
                        + "function flat(n){var parts=[];"
                        + "function rec(node){if(!node)return;"
                        + "if(node.nodeType===3){parts.push(node.nodeValue||'');return;}"
                        + "var tag=(node.tagName||'').toUpperCase();"
                        + "if((tag==='INPUT'||tag==='TEXTAREA')&&node.value!=null)parts.push(' '+node.value+' ');"
                        + "if(node.shadowRoot)rec(node.shadowRoot);"
                        + "var c=node.childNodes;if(c)for(var i=0;i<c.length;i++)rec(c[i]);}"
                        + "rec(n);return parts.join(' ');}"
                        + "function walk(n, acc){if(!n||n.nodeType===3)return;"
                        + "if(n.shadowRoot)walk(n.shadowRoot, acc);"
                        + "var t=flat(n);if(t.indexOf(name)>=0)acc.push(t);"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)walk(c[i], acc);}"
                        + "var found=[];walk(document.body, found);"
                        + "var best=null;var bestLen=1e15;"
                        + "for(var i=0;i<found.length;i++){"
                        + "var t=found[i];var rest=t.split(name).join(' ');"
                        + "var m=rest.match(/\\b(\\d+)\\b/);"
                        + "if(!m)continue;if(t.length<bestLen){bestLen=t.length;best=m[1];}}"
                        + "return best;";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return o == null ? null : String.valueOf(o);
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
     * Assert that the UI shows an estimated duration with the expected number of minutes. This is a generic shadow-DOM
     * text assertion used for the service combination step, selected-appointment callout, and booking summaries.
     */
/**
     * ZMSKVR-106: Patternlab secondary buttons. Minus reduces, plus increases, each with its icon.
     */





















    public void assertInvalidJumpinLinkCalloutVisible() {
        jumpInError.assertInvalidJumpinLinkCalloutVisible();
    }

    public void assertInvalidJumpinRestartButtonVisible() {
        jumpInError.assertInvalidJumpinRestartButtonVisible();
    }

    public void clickInvalidJumpinRestartButton() {
        jumpInError.clickInvalidJumpinRestartButton();
    }

    public void assertAddressHasNoJumpIn() {
        jumpInError.assertAddressHasNoJumpIn();
    }

/**
     * Restart control on the invalid jump-in callout. The painted button can sit in the
     * shadow root of {@code muc-button}, whose host has no box of its own.
     */
    private boolean invalidJumpinRestartButton(boolean click) {
        return jumpInError.invalidJumpinRestartButton(click);
    }

    public void keepOnlyProviderCheckboxesChecked(Set<Integer> allowedOfficeIds) {
        providerLocation.keepOnlyProviderCheckboxesChecked(allowedOfficeIds);
    }

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
     * ZMSKVR-92 / ZMSKVR-164. A finished step keeps its own icon and is the only clickable one
     * ({@code Zurück zu Schritt}). The active step is {@code aria-current=step}. A later step is not a button.
     */
    public void assertBookingStepperLabels() {
        bookingStepper.assertBookingStepperLabels();
    }

    public void assertBookingStep(String label, String state, String icon) {
        bookingStepper.assertBookingStep(label, state, icon);
    }

    /**
     * Outline the finished step and leave it. The next step clicks it, so the screenshot after this
     * step still shows the orange mark.
     */
    public void highlightFinishedBookingStep(String label) {
        bookingStepper.highlightFinishedBookingStep(label);
    }

    public void clickHighlightedBookingStep() {
        bookingStepper.clickHighlightedBookingStep();
    }

    private boolean bookingStepMatches(String label, String state, String icon) {
        return bookingStepper.bookingStepMatches(label, state, icon);
    }

    private String bookingStepLabel(int index) {
        return bookingStepper.bookingStepLabel(index);
    }

    private JsonNode findBookingStep(String label) {
        return bookingStepper.findBookingStep(label);
    }

    private JsonNode readBookingSteps() {
        return bookingStepper.readBookingSteps();
    }

    private boolean paintFinishedBookingStep(String label) {
        return bookingStepper.paintFinishedBookingStep(label);
    }





























    /**
     * Normalize Ort provider checkboxes so only {@code allowedOfficeIds} remain checked.
     * Single-provider teaser layouts have no checkboxes and are left unchanged.
     */
    private JsonNode citizenJson(String expression, Object... args) {
        return json.citizenJson(expression, args);
    }

    public boolean locationStepShowsProvider(int officeId) {
        return providerLocation.locationStepShowsProvider(officeId);
    }

    /** Single-provider layout: teaser headline {@code #provider-{id}} under Ort (no checkboxes). */
    private boolean deepLocationSingleProviderTeaserPresent(int officeId) {
        return providerLocation.deepLocationSingleProviderTeaserPresent(officeId);
    }

    public void assertProviderCheckboxPresent(int officeId) {
        providerLocation.assertProviderCheckboxPresent(officeId);
    }

    public void assertProviderCheckboxAbsent(int officeId) {
        providerLocation.assertProviderCheckboxAbsent(officeId);
    }

    /**
     * Ort checkboxes start selected. Ranked Bürgerbüros follow frequency order. Scheidplatz has no
     * frequency rank in the catalog, so it follows them. Each checkbox also shows its address.
     */
    public void assertOfficesCheckedInFrequencyOrder() {
        providerLocation.assertOfficesCheckedInFrequencyOrder();
    }

    /** One bookable office: a contact tile, no location checkboxes. */
    public void assertSingleOfficeTile(int officeId, String name, String street) {
        providerLocation.assertSingleOfficeTile(officeId, name, street);
    }

    /**
     * Jump-in can pre-select the only provider; clicking again toggles off. True if that office is already on.
     */
    public boolean deepProviderCheckboxChecked(int officeId) {
        return providerLocation.deepProviderCheckboxChecked(officeId);
    }

    public void selectOfficeById(int officeId) {
        providerLocation.selectOfficeById(officeId);
    }

    /**
     * Waits after combination → Ort/ Zeit: multi-provider checkboxes or single-provider teaser.
     */
    public void waitUntilLocationStepShowsProvider(int officeId, int maxSeconds) {
        providerLocation.waitUntilLocationStepShowsProvider(officeId, maxSeconds);
    }

    /**
     * Logs checkbox ids in DOM + whether single-provider teaser matches; asserts expected provider is shown in Ort.
     */
    public void logLocationProviderResolution(int expectedOfficeId) {
        providerLocation.logLocationProviderResolution(expectedOfficeId);
    }

    private JsonNode providerCheckboxes() {
        return providerLocation.providerCheckboxes();
    }

    private JsonNode officeTile(int officeId) {
        return providerLocation.officeTile(officeId);
    }

    private void assertOfficeOrder(JsonNode offices, boolean requireChecked, boolean requireAllKnown) {
        providerLocation.assertOfficeOrder(offices, requireChecked, requireAllKnown);
    }

    private String frequencyName(String text) {
        return providerLocation.frequencyName(text);
    }

    private int frequencyRank(String name) {
        return providerLocation.frequencyRank(name);
    }

    /** Wait until provider-toggle spinner activity has settled (best effort). */
    private void waitUntilProviderToggleSettled(int maxSeconds) {
        providerLocation.waitUntilProviderToggleSettled(maxSeconds);
    }

    /**
     * True when at least one timeslot button for the real booking OfficeID is in the DOM
     * ({@code #provider-{officeId}-timeslot-*} or {@code [data-provider-id="{officeId}"]}).
     */
    public boolean deepTimeslotPresentForProvider(int officeId) {
        return timeSlot.deepTimeslotPresentForProvider(officeId);
    }

    /**
     * Assert no timeslot buttons exist for the given real provider ids (e.g. Ausbildung peer
     * when the selected service is not offered there). Checks the current hour/day-part only.
     */
    public void assertTimeslotsAbsentForProviders(int... officeIds) {
        timeSlot.assertTimeslotsAbsentForProviders(officeIds);
    }

    /**
     * A fitting length shows slots, then the same day in the list, and does not show the empty-day callout.
     */
    public void assertBookableDayInCalendarAndList(int officeId) {
        timeSlot.assertBookableDayInCalendarAndList(officeId);
    }

    /**
     * A length that does not fit leaves the day unselected. The blue info callout is the empty state,
     * and neither the calendar nor the list offers that day.
     */
    public void assertNoBookableDay(int officeId) {
        timeSlot.assertNoBookableDay(officeId);
    }

    public void assertCalendarListToggleShows(String activeLabel) {
        timeSlot.assertCalendarListToggleShows(activeLabel);
    }

    public void assertToggleSitsBesideHeadingOnDesktop() {
        timeSlot.assertToggleSitsBesideHeadingOnDesktop();
    }

    public void assertToggleSitsBelowHeadingOnPhone() {
        timeSlot.assertToggleSitsBelowHeadingOnPhone();
    }

    public void switchToListView() {
        timeSlot.switchToListView();
    }

    public void switchToCalendarView() {
        timeSlot.switchToCalendarView();
    }

    public void assertListDateAccordions(int expectedCount) {
        timeSlot.assertListDateAccordions(expectedCount);
    }

    public void assertOpenListGroupsByHour() {
        timeSlot.assertOpenListGroupsByHour();
    }

    public void assertCalendarGroupsByHour() {
        timeSlot.assertCalendarGroupsByHour();
    }

    public void assertOpenListGroupsByMorning() {
        timeSlot.assertOpenListGroupsByMorning();
    }

    public void assertCalendarGroupsByMorning() {
        timeSlot.assertCalendarGroupsByMorning();
    }

    /** Open hour: each office that has a slot is a map-pin heading, in the same order as the checkboxes. */
    public void assertOpenHourListsOfficesWithMapPin() {
        timeSlot.assertOpenHourListsOfficesWithMapPin();
    }

    /** Früher is a disabled ghost button. Später is an enabled ghost button. */
    public void assertCalendarGhostPagerStartsAtFirstGroup() {
        timeSlot.assertCalendarGhostPagerStartsAtFirstGroup();
    }

    public void moveCalendarHour(boolean later) {
        timeSlot.moveCalendarHour(later);
    }

    /** Clear the first office that is actually listed under the open hour. */
    public void clearFirstShownOffice() {
        timeSlot.clearFirstShownOffice();
    }

    public void assertClearedOfficeIsHidden() {
        timeSlot.assertClearedOfficeIsHidden();
    }

    /** One office shows every group at once, with no location heading and no Früher or Später. */
    public void assertSingleOfficeGroupsTimesWithoutLocationHeadings() {
        timeSlot.assertSingleOfficeGroupsTimesWithoutLocationHeadings();
    }

    public void assertEarlierAndLaterAreEachOnOneLine() {
        timeSlot.assertEarlierAndLaterAreEachOnOneLine();
    }

    public void assertListEarlierStartsDisabled() {
        timeSlot.assertListEarlierStartsDisabled();
    }

    public void moveOpenListHour(boolean later) {
        timeSlot.moveOpenListHour(later);
    }

    public void loadMoreListDates() {
        timeSlot.loadMoreListDates();
    }

    public void openTheNextListDate() {
        timeSlot.openTheNextListDate();
    }

    public void selectVisibleTimeslot() {
        timeSlot.selectVisibleTimeslot();
    }

    public void assertMarkedTimeslotIsWhiteOnBlue() {
        timeSlot.assertMarkedTimeslotIsWhiteOnBlue();
    }

    public void assertPreviousTimeslotIsNotMarked() {
        timeSlot.assertPreviousTimeslotIsNotMarked();
    }

    public void assertTimeslotsPresentForProviders(int... officeIds) {
        timeSlot.assertTimeslotsPresentForProviders(officeIds);
    }

    public void assertAvailableAppointmentsShown() {
        timeSlot.assertAvailableAppointmentsShown();
    }

    /**
     * Skip the slot that was already reserved so the next highlight is a different appointment.
     */
    public void highlightAnotherTimeslotForOffice(int officeId) {
        timeSlot.highlightAnotherTimeslotForOffice(officeId);
    }

    /**
     * Scrolls the provider's time slot grid into the viewport center so {@code @AfterStep} full-page screenshots show
     * the slot area (not only the calendar above the fold). Safe to call after Ort selection when slots exist.
     */
    public void scrollTimeSlotGridIntoViewForScreenshots() {
        timeSlot.scrollTimeSlotGridIntoViewForScreenshots();
    }

    /** True when at least one bookable slot control exists (list or calendar). */
    public boolean deepTimeslotClickablePresent() {
        return timeSlot.deepTimeslotClickablePresent();
    }

    /**
     * MucSpinner lives in {@code .m-spinner-container}; shown while days load and again after a calendar day change
     * until slot API returns ({@code CalendarView.vue}).
     */
    public boolean deepMucSpinnerVisible() {
        return timeSlot.deepMucSpinnerVisible();
    }

    /**
     * Slot API finished: no visible MucSpinner and at least one timeslot control (avoids clicking while day-change
     * spinner runs).
     */
    public boolean deepTimeslotReadyNoSpinner() {
        return timeSlot.deepTimeslotReadyNoSpinner();
    }

    /**
     * Clicks <strong>Später</strong> (later) next to the time slot grid when the earlier/later controls are shown —
     * only when {@code providersWithAppointments.length &gt; 1} (see {@code CalendarView.vue} / {@code ListView.vue}).
     * Moves to the next available hour or PM half-day so slots sit further in the future; no-op if absent/disabled.
     *
     * @return {@code true} if a click was performed
     */
    public boolean clickCitizenViewLaterOnceIfAvailable() {
        return timeSlot.clickCitizenViewLaterOnceIfAvailable();
    }

    /** Wait until slot buttons exist and MucSpinner cleared (calendar day / office fetch). */
    public void waitUntilAppointmentSlotsReady(int maxSeconds) {
        timeSlot.waitUntilAppointmentSlotsReady(maxSeconds);
    }

    /**
     * Step 1 of slot booking: wait until MucSpinner is gone and at least one timeslot exists (see
     * {@link #waitUntilAppointmentSlotsReady(int)}).
     */
    public void waitUntilSlotsReadyForBooking() {
        timeSlot.waitUntilSlotsReadyForBooking();
    }

    /**
     * Step 2: click <strong>Später</strong> beside the time slot grid (hour/day-part navigation) when shown
     * (multi-provider), then wait for slots to reload. No-op if the button is absent or disabled.
     */
    public void clickSpäterIfAvailableAndReloadSlots() {
        timeSlot.clickSpäterIfAvailableAndReloadSlots();
    }

    /**
     * Step 3a: scroll to grid and highlight the preferred timeslot (no click). The next Cucumber step’s
     * {@code @AfterStep} screenshot then shows the orange outline before Vue updates.
     * <p>
     * For shared booking, {@code officeId} is the real slot owner ({@code data-provider-id}), which may differ
     * from the Ort display id. Retries with Später when no matching slot is in the current hour/day-part.
     */
    public void highlightPreferredTimeslotForOffice(int officeId) {
        timeSlot.highlightPreferredTimeslotForOffice(officeId);
    }

    /** Step 3b: click the slot stored by {@link #highlightPreferredTimeslotForOffice(int)}. */
    public void clickHighlightedTimeslotSelection() {
        timeSlot.clickHighlightedTimeslotSelection();
    }

    /**
     * Step 3 (combined): highlight + click — use split steps in features so {@code @AfterStep} captures the slot area.
     */
    public void selectPreferredTimeslotBelowCalendar(int officeId) {
        timeSlot.selectPreferredTimeslotBelowCalendar(officeId);
    }

    /**
     * Step 4: assert {@code Ausgewählter Termin} callout for the office, then <strong>Weiter</strong> to reserve (API).
     */
    public void assertCalloutAndReserveAfterSlotSelection(int officeId) {
        timeSlot.assertCalloutAndReserveAfterSlotSelection(officeId);
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
        timeSlot.scrollClickFirstSlotAssertCalloutWeiter(officeId);
    }

    /**
     * Info callout after slot pick: selected-appointment header + {@code #provider-{officeId}}.
     *
     * @return false when the Kontakt step is already showing, so the caller must not click Weiter again
     */
    public boolean assertSelectedAppointmentCalloutShowsProvider(int officeId) {
        return timeSlot.assertSelectedAppointmentCalloutShowsProvider(officeId);
    }

    public void assertSelectedAppointmentCalloutVisible() {
        timeSlot.assertSelectedAppointmentCalloutVisible();
    }

    public void assertAppointmentNoLongerAvailableCalloutVisible() {
        timeSlot.assertAppointmentNoLongerAvailableCalloutVisible();
    }

    public void assertStillOnAppointmentSelectionStep() {
        timeSlot.assertStillOnAppointmentSelectionStep();
    }

    private JsonNode locationTitles() {
        return timeSlot.locationTitles();
    }

    private JsonNode calendarGhostButtons() {
        return timeSlot.calendarGhostButtons();
    }

    private void assertGhostButton(JsonNode button, String word, String icon, boolean disabled) {
        timeSlot.assertGhostButton(button, word, icon, disabled);
    }

    private String hourLabelOrEmpty(JsonNode state) {
        return timeSlot.hourLabelOrEmpty(state);
    }

    private JsonNode hourLabel(JsonNode node) {
        return timeSlot.hourLabel(node);
    }

    private JsonNode morningLabel(JsonNode node) {
        return timeSlot.morningLabel(node);
    }

    private void assertHourLabels(JsonNode state) {
        timeSlot.assertHourLabels(state);
    }

    private void assertMorningLabels(JsonNode state) {
        timeSlot.assertMorningLabels(state);
    }

    private JsonNode waitForToggleLabels(String activeLabel) {
        return timeSlot.waitForToggleLabels(activeLabel);
    }

    private void assertToggleColor(JsonNode label, String text, boolean active) {
        timeSlot.assertToggleColor(label, text, active);
    }

    private JsonNode calendarSnapshot() {
        return timeSlot.calendarSnapshot();
    }

    private JsonNode listSnapshot() {
        return timeSlot.listSnapshot();
    }

    private JsonNode waitForListAccordionCount(int expected) {
        return timeSlot.waitForListAccordionCount(expected);
    }

    private String firstHourLabel(JsonNode state) {
        return timeSlot.firstHourLabel(state);
    }

    private JsonNode waitForPagerButtons() {
        return timeSlot.waitForPagerButtons();
    }

    private void assertPagerButton(JsonNode button, String word, boolean disabled) {
        timeSlot.assertPagerButton(button, word, disabled);
    }

    private JsonNode timeslotStyle(String slotId) {
        return timeSlot.timeslotStyle(slotId);
    }

    private void waitUntilCalendarSettled(int officeId, boolean expectSlots) {
        timeSlot.waitUntilCalendarSettled(officeId, expectSlots);
    }

    private boolean timeslotLooksWhiteOnBlue(JsonNode slot) {
        return timeSlot.timeslotLooksWhiteOnBlue(slot);
    }

    /**
     * The Termin step fetches days and slots again after a stepper click. The first wait uses the
     * same budget as the initial calendar load. A spinner that is still up gets one more wait.
     */
    private void waitForSlotsAfterReturningToTermin() {
        timeSlot.waitForSlotsAfterReturningToTermin();
    }

    /**
     * Opens the next bookable day on the citizen calendar. Später only moves within the open day,
     * so an empty evening grid uses the calendar's next-day control instead.
     */
    private boolean openNextCalendarDayAndWaitForSlots() {
        return timeSlot.openNextCalendarDayAndWaitForSlots();
    }

    public void selectSingleSeatDayAfterV19RangeAndWaitForSlots() {
        timeSlot.selectSingleSeatDayAfterV19RangeAndWaitForSlots();
    }

    private boolean clickNextBookableCalendarDay() {
        return timeSlot.clickNextBookableCalendarDay();
    }

    /** Max wait for slot grid + spinner (calendar / office load). */
    private int slotBookingWaitTimeoutSeconds() {
        return timeSlot.slotBookingWaitTimeoutSeconds();
    }

    /** @return false when the current calendar view has no highlightable slot for this office */
    private boolean highlightPreferredTimeslotForOfficeOrAbsent(int officeId, String skippedTimestamps) {
        return timeSlot.highlightPreferredTimeslotForOfficeOrAbsent(officeId, skippedTimestamps);
    }

    /**
     * Clicks the stored slot. Returns false when the slot is gone, so the reserve loop can skip it
     * and try the next timestamp instead of failing the 15s re-highlight wait.
     */
    private boolean clickHighlightedTimeslotSelectionOrGiveUp() {
        return timeSlot.clickHighlightedTimeslotSelectionOrGiveUp();
    }

    private int resolveStoredSlotOfficeId(JavascriptExecutor js) {
        return timeSlot.resolveStoredSlotOfficeId(js);
    }

    private boolean performStoredTimeslotClick(JavascriptExecutor js) {
        return timeSlot.performStoredTimeslotClick(js);
    }

    /**
     * True when a timeslot for {@code officeId} shows primary (selected) styling or the selected-appointment callout
     * is visible — i.e. Vue received {@code selectTimeSlot}.
     */
    private boolean isSlotSelectionVisibleForOffice(int officeId) {
        return timeSlot.isSlotSelectionVisibleForOffice(officeId);
    }

    /** {@code muc-button} with {@code data-variant="primary"} for {@code provider-{officeId}-timeslot-*}. */
    private boolean isTimeslotPrimarySelectedForOffice(int officeId) {
        return timeSlot.isTimeslotPrimarySelectedForOffice(officeId);
    }

    private boolean waitForSlotSelectionVisible(int officeId, int timeoutSeconds) {
        return timeSlot.waitForSlotSelectionVisible(officeId, timeoutSeconds);
    }

    /**
     * Kontakt step is up: heading, voluntary-login box, or the Vorname field.
     * A slow reserve paints this page without a taken-slot error.
     */
    private boolean contactStepReached() {
        return timeSlot.contactStepReached();
    }

    private boolean selectedAppointmentCalloutVisible() {
        return timeSlot.selectedAppointmentCalloutVisible();
    }

    /**
     * Firefox can land on Kontaktdaten before the callout assert. A leftover callout node must not fail the
     * scenario; the caller continues on the contact form and must not click Weiter again.
     */
    private boolean stopBecauseContactStepIsVisible(int officeId) {
        return timeSlot.stopBecauseContactStepIsVisible(officeId);
    }

    private void finishReserveOnContactStep() {
        timeSlot.finishReserveOnContactStep();
    }

    private TimeSlotStep.ReserveOutcome waitForReserveOutcome() {
        return timeSlot.waitForReserveOutcome();
    }

    /** Keep the slot whose Weiter reached Kontakt, not a later highlight. */
    private void keepReservedSlot(Long timestamp) {
        timeSlot.keepReservedSlot(timestamp);
    }

    public long readStoredSlotTimestamp() {
        return timeSlot.readStoredSlotTimestamp();
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
        timeSlot.waitForReserveToSettle();
    }


    public static final String CONTACT_PHONE_E2E = ContactStep.CONTACT_PHONE_E2E;

    public void assertEnteredContactDetailsStillPresent() {
        contact.assertEnteredContactDetailsStillPresent();
    }

    public void fillContactDetails(String firstName, String lastName, String email, String phone) {
        contact.fillContactDetails(firstName, lastName, email, phone);
    }

    public boolean deepContactPhoneFieldExists() {
        return contact.deepContactPhoneFieldExists();
    }

    public void fillContactDetailsRandom() {
        contact.fillContactDetailsRandom();
    }

    public void fillContactDetailsRandomWithoutOptionalRemarks() {
        contact.fillContactDetailsRandomWithoutOptionalRemarks();
    }

    public void assertContactFormVisible() {
        contact.assertContactFormVisible();
    }

    public void assertFilledNameAndEmailLockedOnContactForm() {
        contact.assertFilledNameAndEmailLockedOnContactForm();
    }

    public void assertRequiredCustomTextFieldEditableOnContactForm() {
        contact.assertRequiredCustomTextFieldEditableOnContactForm();
    }

    public void fillRequiredCustomTextFieldsOnContactForm() {
        contact.fillRequiredCustomTextFieldsOnContactForm();
    }

    public void fillContactDetailsRandomWithoutContinue() {
        contact.fillContactDetailsRandomWithoutContinue();
    }

    public boolean deepContactCustomTextFieldExists() {
        return contact.deepContactCustomTextFieldExists();
    }

    public boolean deepContactCustomTextField2Exists() {
        return contact.deepContactCustomTextField2Exists();
    }

    public void assertContactPhoneAndCustomFieldsVisibleWithValues() {
        contact.assertContactPhoneAndCustomFieldsVisibleWithValues();
    }

    public void loginViaBuergerLoginWithKeycloak() throws Exception {
        contact.loginViaBuergerLoginWithKeycloak();
    }

    public void loginViaBuergerLoginWithKeycloakIfNeeded() throws Exception {
        contact.loginViaBuergerLoginWithKeycloakIfNeeded();
    }

    public void cancelBuergerLoginOnKeycloakForm() throws Exception {
        contact.cancelBuergerLoginOnKeycloakForm();
    }

    public void assertContactEmailFieldEmpty() {
        contact.assertContactEmailFieldEmpty();
    }

    public void assertCitizenLoggedInOnContactForm() {
        contact.assertCitizenLoggedInOnContactForm();
    }

    public void assertProviderSummaryVisible(int officeId) {
        overview.assertProviderSummaryVisible(officeId);
    }

    public String deepVisibleProviderSummaryText(int officeId) {
        return overview.deepVisibleProviderSummaryText(officeId);
    }

    public boolean deepVisibleProviderSummaryExists(int officeId) {
        return overview.deepVisibleProviderSummaryExists(officeId);
    }

    public void assertProviderSummaryVisible(int officeId, String expectedStandortLabel) {
        overview.assertProviderSummaryVisible(officeId, expectedStandortLabel);
    }

    public void assertStandardLegalNotices() {
        overview.assertStandardLegalNotices();
    }

    public void assertSelectedAppointmentPlaceExclusive(String heading, String exclusive) {
        overview.assertSelectedAppointmentPlaceExclusive(heading, exclusive);
    }

    public void assertBookingOverviewPlace(int officeId, String heading, String hint) {
        overview.assertBookingOverviewPlace(officeId, heading, hint);
    }

    public void assertBookingOverviewPlaceIncludes(int officeId, String fragment) {
        overview.assertBookingOverviewPlaceIncludes(officeId, fragment);
    }

    public void assertBookingOverviewPlaceExcludes(int officeId, String fragment) {
        overview.assertBookingOverviewPlaceExcludes(officeId, fragment);
    }

    public void assertVideoLegalNotices(String legal) {
        overview.assertVideoLegalNotices(legal);
    }

    public void assertReserveAppointmentButtonEnabled(boolean enabled) {
        overview.assertReserveAppointmentButtonEnabled(enabled);
    }

    public void assertReserveAppointmentButtonAfterCommunication(String legal) {
        overview.assertReserveAppointmentButtonAfterCommunication(legal);
    }

    public void assertServiceLinkPointsToMunichDe() {
        overview.assertServiceLinkPointsToMunichDe();
    }

    public void waitForPreconfirmPageAfterUpdate() {
        overview.waitForPreconfirmPageAfterUpdate();
    }

    public void acceptCommunication() {
        overview.acceptCommunication();
    }

    public void acceptVideoConsultationTermsIfShown() {
        overview.acceptVideoConsultationTermsIfShown();
    }

    public void clickReserveAppointment() {
        overview.clickReserveAppointment();
    }

    public void continueFromPreconfirmStep() {
        overview.continueFromPreconfirmStep();
    }

    public void assertPreconfirmationCalloutVisible(int activationMinutes) {
        overview.assertPreconfirmationCalloutVisible(activationMinutes);
    }

    public void assertConfirmationSuccessCalloutVisible() {
        overview.assertConfirmationSuccessCalloutVisible();
    }

    public void confirmLoggedInBookingFromSummary() {
        overview.confirmLoggedInBookingFromSummary();
    }

    public void assertLoggedInConfirmationSuccessDetailsVisible() {
        overview.assertLoggedInConfirmationSuccessDetailsVisible();
    }

    public void confirmRebookingFromSummary() {
        overview.confirmRebookingFromSummary();
    }

    public void assertPreconfirmationCalloutNotVisible() {
        overview.assertPreconfirmationCalloutNotVisible();
    }

    public void assertAlreadyActivatedAppointmentBannerVisible() {
        overview.assertAlreadyActivatedAppointmentBannerVisible();
    }

    public void assertAlreadyActivatedAppointmentBannerNotVisible() {
        overview.assertAlreadyActivatedAppointmentBannerNotVisible();
    }

    public void clickRescheduleAppointment() {
        overview.clickRescheduleAppointment();
    }

    public void rescheduleFromMyAppointments() {
        overview.rescheduleFromMyAppointments();
    }

    public void assertCancelRescheduleButtonVisible() {
        overview.assertCancelRescheduleButtonVisible();
    }

    public void assertRescheduleOrCancelActionsVisible() {
        overview.assertRescheduleOrCancelActionsVisible();
    }

    public void clickCancelReschedule() {
        overview.clickCancelReschedule();
    }

    public void clickCancelAppointmentAndConfirm() {
        overview.clickCancelAppointmentAndConfirm();
    }

    public void assertCancellationSuccessCalloutVisible() {
        overview.assertCancellationSuccessCalloutVisible();
    }

    public void assertCancellationSuccessDetailsVisible() {
        overview.assertCancellationSuccessDetailsVisible();
    }

    public void continueFromContactFormToSummary() {
        overview.continueFromContactFormToSummary();
    }

    public void assertScheidplatzLocationOnSummary(int officeId) {
        overview.assertScheidplatzLocationOnSummary(officeId);
    }

    public void goBackFromBookingSummaryToContact() {
        overview.goBackFromBookingSummaryToContact();
    }

    public void reloadReservedAppointmentHash() {
        overview.reloadReservedAppointmentHash();
    }

    public void assertAppointmentManagementActionsNotVisible() {
        overview.assertAppointmentManagementActionsNotVisible();
    }

    public void assertCaptchaSessionCalloutVisible() {
        captcha.assertCaptchaSessionCalloutVisible();
    }

    public void assertCaptchaSessionCalloutNotVisible() {
        captcha.assertCaptchaSessionCalloutNotVisible();
    }

    public void assertReservationExpiredCalloutVisible() {
        CONTEXT.set();
        waitUntilShadowContains(DE_RESERVATION_EXPIRED_HEADER, DEFAULT_EXPLICIT_WAIT_TIME);
        Assert.assertTrue(
                shadowDomContainsText(DE_RESERVATION_EXPIRED_TEXT),
                "Reservation expired callout text missing.");
        Assert.assertTrue(
                shadowDomContainsText(DE_RESTART_BOOKING),
                "Reservation expired callout must offer Buchung neu starten.");
    }

    public void clickRestartBooking() {
        waitForAndClickButtonContaining(DE_RESTART_BOOKING, DEFAULT_EXPLICIT_WAIT_TIME);
    }

    public void assertBackButtonNotVisible() {
        CONTEXT.set();
        Assert.assertFalse(
                visibleButtonTextEquals(DE_BACK),
                "Zurück must be hidden while the restart callout is showing.");
    }

    public void assertBackButtonVisible() {
        CONTEXT.set();
        Assert.assertTrue(visibleButtonTextEquals(DE_BACK), "Zurück should be visible.");
    }

    public void waitUntilCaptchaCheckFinished(int widgetTimeoutSeconds, int solveTimeoutSeconds) {
        captcha.waitUntilCaptchaCheckFinished(widgetTimeoutSeconds, solveTimeoutSeconds);
    }

    public void assertSelectedServiceQuantity(String serviceName, int quantity) {
        CONTEXT.set();
        waitUntilShadowContains(serviceName, DEFAULT_EXPLICIT_WAIT_TIME);
        Assert.assertTrue(shadowDomContainsText(serviceName), "Expected selected service " + serviceName);
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .pollingEvery(Duration.ofMillis(500))
                .until(driver -> counterValueBeside(serviceName) != null);
        String counter = counterValueBeside(serviceName);
        Assert.assertEquals(
                counter,
                String.valueOf(quantity),
                "Expected quantity " + quantity + " for " + serviceName + " but the counter was " + counter);
    }

    public void assertElectronicCommunicationCheckboxVisible() {
        overview.assertElectronicCommunicationCheckboxVisible();
    }

    static String ensureAbsoluteCitizenViewUrl(String url) {
        return MyAppointmentsStep.ensureAbsoluteCitizenViewUrl(url);
    }

    public ThinnedProcess syncBookingProcessFromLocalStorage() throws Exception {
        return myAppointments.syncBookingProcessFromLocalStorage();
    }

    public void trySyncBookingProcessFromLocalStorageOnce() {
        myAppointments.trySyncBookingProcessFromLocalStorageOnce();
    }

    public void trySetBookingProcessFromPage() {
        myAppointments.trySetBookingProcessFromPage();
    }

    public void openConfirmationDeepLinkInBrowser() {
        myAppointments.openConfirmationDeepLinkInBrowser();
    }

    public void reopenConfirmationDeepLinkInBrowser() {
        myAppointments.reopenConfirmationDeepLinkInBrowser();
    }

    public void openAppointmentViewDeepLinkInBrowser() {
        myAppointments.openAppointmentViewDeepLinkInBrowser();
    }

    public void captureBookingProcessForCleanup() {
        myAppointments.captureBookingProcessForCleanup();
    }

    public void rememberSelectedAppointmentTime() {
        myAppointments.rememberSelectedAppointmentTime();
    }

    public void openMyAppointments() {
        myAppointments.openMyAppointments();
    }

    public void assertMyAppointmentsLists(String... serviceNames) {
        myAppointments.assertMyAppointmentsLists(serviceNames);
    }

    public void assertMyAppointmentsDoesNotList(String serviceName) {
        myAppointments.assertMyAppointmentsDoesNotList(serviceName);
    }

    public void rememberMyAppointmentsAppointment(String serviceName) {
        myAppointments.rememberMyAppointmentsAppointment(serviceName);
    }

    public void assertMyAppointmentsAppointmentReplaced(String serviceName) {
        myAppointments.assertMyAppointmentsAppointmentReplaced(serviceName);
    }

    public void assertMyAppointmentsAppointmentUnchanged(String serviceName) {
        myAppointments.assertMyAppointmentsAppointmentUnchanged(serviceName);
    }

    public void assertMyAppointmentsTeaser(String serviceName, String typeLabel, String locationText) {
        myAppointments.assertMyAppointmentsTeaser(serviceName, typeLabel, locationText);
    }

    public void openMyAppointmentsTeaser(String serviceName) {
        myAppointments.openMyAppointmentsTeaser(serviceName);
    }

    public void assertAppointmentDetailLocation(
            String typeLabel, String place, String locationText, String preparationHint, String extra) {
        myAppointments.assertAppointmentDetailLocation(typeLabel, place, locationText, preparationHint, extra);
    }

    public void assertIcsDownloadOfferedOnDetailIntro() {
        myAppointments.assertIcsDownloadOfferedOnDetailIntro();
    }

    public void downloadAppointmentIcs() {
        myAppointments.downloadAppointmentIcs();
    }

    public void assertDownloadedIcsContainsBookedAppointment() {
        myAppointments.assertDownloadedIcsContainsBookedAppointment();
    }

}
