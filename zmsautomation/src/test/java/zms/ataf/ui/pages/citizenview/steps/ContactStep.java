package zms.ataf.ui.pages.citizenview.steps;

import java.time.Duration;

import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.helpers.TestDataHelper;
import ataf.core.helpers.TestPropertiesHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.web.utils.DriverUtil;
import zms.ataf.helpers.AccountCheckout;
import zms.ataf.helpers.RandomNameHelper;
import zms.ataf.helpers.ViewportSizes;
import zms.ataf.ui.pages.citizenview.CitizenViewPage;
import zms.ataf.ui.pages.citizenview.CitizenViewPageContext;
import zms.ataf.ui.pages.citizenview.support.CitizenViewWaits;
import zms.ataf.ui.pages.citizenview.support.ShadowDom;

/** Kontakt step: form fill, custom fields, Bürger-Login on Kontakt. */
public final class ContactStep {

    private final CitizenViewPageContext context;
    private final ShadowDom shadow;
    private final CitizenViewPage page;
    private final int defaultWaitSeconds;

    public ContactStep(CitizenViewPageContext context, ShadowDom shadow, CitizenViewPage page, int defaultWaitSeconds) {
        this.context = context;
        this.shadow = shadow;
        this.page = page;
        this.defaultWaitSeconds = defaultWaitSeconds;
    }

    public void assertEnteredContactDetailsStillPresent() {
        context.set();
        Assert.assertFalse(lastContactFirstName.isBlank(), "No contact details were entered.");
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> lastContactFirstName.equals(shadow.deepGetById("firstname")));
        Assert.assertEquals(shadow.deepGetById("firstname"), lastContactFirstName, "Vorname was cleared.");
        Assert.assertEquals(shadow.deepGetById("lastname"), lastContactLastName, "Nachname was cleared.");
        String email = shadow.deepGetById("mailaddress");
        Assert.assertNotNull(email, "E-Mail could not be read.");
        Assert.assertTrue(
                email.equalsIgnoreCase(lastContactEmail),
                "E-Mail was cleared. expected=" + lastContactEmail + " actual=" + email);
        if (deepContactPhoneFieldExists()) {
            String phone = shadow.deepGetById("telephonenumber");
            Assert.assertNotNull(phone, "Telephone could not be read.");
            Assert.assertTrue(
                    phone.contains(lastContactPhone) || phone.replaceAll("\\s+", "").contains("491234567890"),
                    "Telephone was cleared. expected=" + lastContactPhone + " actual=" + phone);
        }
    }
    public static final String CONTACT_PHONE_E2E = "+491234567890";

    private static final String CONTACT_LOREM_REQUIRED =
            "Lorem ipsum dolor sit amet, consectetur adipiscing elit. E2E Pflichtfeld.";

    private String lastContactFirstName = "";
    private String lastContactLastName = "";
    private String lastContactEmail = "";
    private String lastContactPhone = CONTACT_PHONE_E2E;
    private String lastContactCustomText = CONTACT_LOREM_REQUIRED;

    public void fillContactDetails(String firstName, String lastName, String email, String phone) {
        context.set();
        shadow.deepSetById("firstname", firstName);
        shadow.deepSetById("lastname", lastName);
        shadow.deepSetById("mailaddress", email);
        if (deepContactPhoneFieldExists()) {
            shadow.deepSetById("telephonenumber", phone);
        }
    }
    public boolean deepContactPhoneFieldExists() {
        return shadow.deepElementExists("#telephonenumber")
                || shadow.deepElementExists("#input-telephonenumber")
                || shadow.deepElementExists("muc-input#telephonenumber");
    }
    public void fillContactDetailsRandom() {
        fillContactDetailsRandom(true);
    }
    public void fillContactDetailsRandomWithoutOptionalRemarks() {
        fillContactDetailsRandom(false);
    }
    private void fillContactDetailsRandom(boolean fillOptionalRemarks) {
        context.set();
        shadow.waitUntilShadowContains("Kontaktdaten", Math.max(30, defaultWaitSeconds));
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
        boolean ok1 = shadow.deepSetById("firstname", parts[0]);
        boolean ok2 = shadow.deepSetById("lastname", parts[1]);
        boolean ok3 = shadow.deepSetById("mailaddress", email);
        Assert.assertTrue(ok1, "Kontakt: could not set Vorname (muc-input shadow)");
        Assert.assertTrue(ok2, "Kontakt: could not set Nachname (muc-input shadow)");
        Assert.assertTrue(ok3, "Kontakt: could not set E-Mail (muc-input shadow)");
        if (deepContactPhoneFieldExists()) {
            shadow.deepSetById("telephonenumber", CONTACT_PHONE_E2E);
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
    private void fillOptionalContactRemarksIfPresent() {
        context.set();
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
    private void fillRequiredCustomTextAreasInShadow() {
        context.set();
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
    public void assertContactFormVisible() {
        context.set();
        shadow.waitUntilShadowContains("Kontaktdaten", Math.max(30, defaultWaitSeconds));
        Assert.assertTrue(
                shadow.shadowDomContainsText("Kontaktdaten"),
                "Expected Kontakt form (Kontaktdaten) after rebooking to a scope with missing required fields.");
    }

    /**
     * ZMSKVR-92 / ZMSKVR-164: after a second reserve the UI can briefly paint Kontakt then land on
     * Übersicht (contact already known). Open Kontakt from the stepper when that happens so the
     * form assert still checks preserved details.
     */
    public void assertContactFormVisibleAfterReserve() {
        context.set();
        long deadline = System.currentTimeMillis() + Math.max(60, defaultWaitSeconds) * 1000L;
        while (System.currentTimeMillis() < deadline) {
            if (shadow.shadowDomContainsText("Kontaktdaten") && shadow.deepElementExists("#firstname")) {
                ScenarioLogManager.getLogger().info("zmscitizenview: Kontakt form visible after reserve");
                return;
            }
            if (overviewShowsFinishedKontaktStep()) {
                ScenarioLogManager.getLogger()
                        .info("zmscitizenview: Übersicht after reserve — opening finished Kontakt step");
                page.highlightFinishedBookingStep("Kontakt");
                page.clickHighlightedBookingStep();
                shadow.waitUntilShadowContains("Kontaktdaten", Math.max(30, defaultWaitSeconds));
                Assert.assertTrue(
                        shadow.shadowDomContainsText("Kontaktdaten"),
                        "Expected Kontakt form after opening finished Kontakt from Übersicht.");
                return;
            }
            CitizenViewWaits.sleepQuiet(400L);
        }
        assertContactFormVisible();
    }

    private boolean overviewShowsFinishedKontaktStep() {
        if (!shadow.shadowDomContainsText("Ihr Termin")) {
            return false;
        }
        return page.bookingStepMatches("Übersicht", "current", "information")
                && page.bookingStepMatches("Kontakt", "finished", "mail");
    }
    public void assertFilledNameAndEmailLockedOnContactForm() {
        context.set();
        shadow.waitUntilShadowContains("Kontaktdaten", Math.max(30, defaultWaitSeconds));
        Assert.assertTrue(
                shadow.deepControlDisabled("firstname"),
                "Vorname should be locked on rebooking Kontakt when already filled.");
        Assert.assertTrue(
                shadow.deepControlDisabled("lastname"),
                "Nachname should be locked on rebooking Kontakt when already filled.");
        Assert.assertTrue(
                shadow.deepControlDisabled("mailaddress"),
                "E-Mail should be locked on rebooking Kontakt when already filled.");
    }
    public void assertRequiredCustomTextFieldEditableOnContactForm() {
        context.set();
        Assert.assertTrue(
                deepContactCustomTextFieldExists(),
                "Required custom text field (#remarks) must be visible on rebooking Kontakt.");
        Assert.assertFalse(
                shadow.deepControlDisabled("remarks"),
                "Required custom text field must stay editable when empty on rebooking.");
    }
    public void fillRequiredCustomTextFieldsOnContactForm() {
        context.set();
        shadow.waitUntilShadowContains("Kontaktdaten", Math.max(30, defaultWaitSeconds));
        fillRequiredCustomTextAreasInShadow();
        String remarks = shadow.deepGetById("remarks");
        if (remarks == null || remarks.isBlank()) {
            Assert.assertTrue(
                    shadow.deepSetById("remarks", CONTACT_LOREM_REQUIRED),
                    "Kontakt: could not set required Bemerkung (muc-text-area shadow)");
        }
        ScenarioLogManager.getLogger().info("zmscitizenview: Kontakt — required Bemerkung filled for rebooking");
    }
    public void fillContactDetailsRandomWithoutContinue() {
        fillContactDetailsRandom();
        lastContactPhone = CONTACT_PHONE_E2E;
        lastContactCustomText = CONTACT_LOREM_REQUIRED;
    }

    /**
     * ZMSKVR-1090: SSO user has Nachname only ({@code given_name} missing). Prefill must keep
     * Vorname empty and Nachname from the token; the citizen completes Vorname before booking.
     */
    public void assertSsoLastNamePrefillWithoutGivenName(String expectedLastName) {
        context.set();
        shadow.waitUntilShadowContains("Kontaktdaten", Math.max(30, defaultWaitSeconds));
        CitizenViewWaits.waitWithThreeWindows(
                () -> expectedLastName.equals(shadow.deepGetById("lastname")),
                "SSO Nachname prefill on Kontakt");
        String first = shadow.deepGetById("firstname");
        String last = shadow.deepGetById("lastname");
        Assert.assertEquals(
                last,
                expectedLastName,
                "Nachname should be prefilled from family_name. Was: " + last);
        Assert.assertTrue(
                first == null || first.isBlank(),
                "Vorname must stay empty when given_name is missing. Was: " + first);
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: Kontakt SSO prefill — Vorname empty, Nachname={}",
                        expectedLastName);
    }

    /**
     * Completes Kontakt after SSO without given_name: sets Vorname, keeps Nachname, fills mail /
     * phone / required Zusatzfelder like {@link #fillContactDetailsRandom()}.
     */
    public void completeContactWithMissingGivenName(String firstName, String expectedLastName) {
        context.set();
        assertSsoLastNamePrefillWithoutGivenName(expectedLastName);
        String email =
                RandomNameHelper.getEmailConformName(firstName + " " + expectedLastName)
                        + "@mailinator.com";
        lastContactFirstName = firstName;
        lastContactLastName = expectedLastName;
        lastContactEmail = email;
        lastContactPhone = CONTACT_PHONE_E2E;
        lastContactCustomText = CONTACT_LOREM_REQUIRED;
        zms.ataf.rest.steps.CitizenApiSteps.setBookingContactEmail(email);
        Assert.assertTrue(
                shadow.deepSetById("firstname", firstName),
                "Kontakt: could not set Vorname after SSO without given_name");
        Assert.assertTrue(
                shadow.deepSetById("mailaddress", email),
                "Kontakt: could not set E-Mail after SSO without given_name");
        if (deepContactPhoneFieldExists()) {
            shadow.deepSetById("telephonenumber", CONTACT_PHONE_E2E);
        }
        fillRequiredCustomTextAreasInShadow();
        fillOptionalContactRemarksIfPresent();
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: Kontakt completed Vorname={} Nachname={} E-Mail={}",
                        firstName,
                        expectedLastName,
                        email);
    }

    public void loginViaBuergerLoginWithKeycloakAs(String loginName) throws Exception {
        context.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: click in-app Anmelden (Bürger-Login as {})", loginName);
        try {
            new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                    .until(d -> clickInAppBuergerLoginAnmelden());
        } catch (TimeoutException e) {
            ScenarioLogManager.getLogger()
                    .warn("zmscitizenview: in-app Anmelden not found; falling back to any Anmelden button");
            shadow.waitForAndClickButtonContaining("Anmelden", defaultWaitSeconds);
        }

        String password =
                TestPropertiesHelper.getPropertyAsString("citizenUserPassword", true, "vorschau");
        String username = AccountCheckout.assignCitizenLogin(loginName);
        context.lastCitizenLoginUsername = username;
        completeKeycloakLoginForm(username, password);

        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText("Sie sind angemeldet"),
                "Logged-in callout after Keycloak Bürger-Login");
        if (!shadow.shadowDomContainsText("Sie sind angemeldet.")) {
            ScenarioLogManager.getLogger()
                    .warn("zmscitizenview: Bürger-Login callout missing; submitting Keycloak again");
            if (!DriverUtil.getDriver().findElements(By.id("username")).isEmpty()) {
                completeKeycloakLoginForm(username, password);
            } else if (shadow.shadowDomContainsText("Anmelden")) {
                clickInAppBuergerLoginAnmelden();
                if (!DriverUtil.getDriver().findElements(By.id("username")).isEmpty()) {
                    completeKeycloakLoginForm(username, password);
                }
            }
            CitizenViewWaits.waitWithThreeWindows(
                    () -> shadow.shadowDomContainsText("Sie sind angemeldet"),
                    "Logged-in callout after Keycloak Bürger-Login retry");
        }
        Assert.assertTrue(
                shadow.shadowDomContainsText("Sie sind angemeldet."),
                "Expected 'Sie sind angemeldet.' after Bürger-Login as " + loginName + ".");
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: Bürger-Login completed as {}", username);
        page.trySetBookingProcessFromPage();
    }

    public boolean deepContactCustomTextFieldExists() {
        // MucTextArea binds the control as id="textarea-{prop}" (prop "remarks" is not on the host).
        return shadow.deepElementExists("#textarea-remarks")
                || shadow.deepElementExists("#remarks")
                || shadow.deepElementExists("muc-text-area#remarks")
                || shadow.deepElementExists("#input-remarks");
    }
    public boolean deepContactCustomTextField2Exists() {
        return shadow.deepElementExists("#textarea-remarks2")
                || shadow.deepElementExists("#remarks2")
                || shadow.deepElementExists("muc-text-area#remarks2")
                || shadow.deepElementExists("#input-remarks2");
    }
    public void assertContactPhoneAndCustomFieldsVisibleWithValues() {
        context.set();
        shadow.waitUntilShadowContains("Kontaktdaten", Math.max(30, defaultWaitSeconds));
        Assert.assertTrue(
                deepContactPhoneFieldExists(),
                "Telephone field (#telephonenumber) must remain visible on the Kontakt form.");
        Assert.assertTrue(
                deepContactCustomTextFieldExists(),
                "Custom text field (#remarks) must remain visible on the Kontakt form.");
        Assert.assertTrue(
                deepContactCustomTextField2Exists(),
                "Second custom text field (#remarks2) must remain visible on the Kontakt form.");
        String phone = shadow.deepGetById("telephonenumber");
        Assert.assertNotNull(phone, "Telephone field value could not be read.");
        Assert.assertTrue(
                phone.contains(lastContactPhone) || phone.equals(lastContactPhone),
                "Telephone field should keep entered value. expectedContains="
                        + lastContactPhone
                        + " actual="
                        + phone);
        String remarks = shadow.deepGetById("remarks");
        Assert.assertNotNull(remarks, "Custom text field value could not be read.");
        Assert.assertTrue(
                remarks.equals(lastContactCustomText) || remarks.contains(lastContactCustomText),
                "Custom text field should keep entered value. expected="
                        + lastContactCustomText
                        + " actual="
                        + remarks);
        String remarks2 = shadow.deepGetById("remarks2");
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
    public void loginViaBuergerLoginWithKeycloak() throws Exception {
        context.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: click in-app Anmelden (Bürger-Login)");
        try {
            new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                    .until(d -> clickInAppBuergerLoginAnmelden());
        } catch (TimeoutException e) {
            ScenarioLogManager.getLogger()
                    .warn("zmscitizenview: in-app Anmelden not found; falling back to any Anmelden button");
            shadow.waitForAndClickButtonContaining("Anmelden", defaultWaitSeconds);
        }

        String username =
                TestPropertiesHelper.getPropertyAsString("citizenUserName", true, "citizen");
        String password =
                TestPropertiesHelper.getPropertyAsString("citizenUserPassword", true, "vorschau");
        username = AccountCheckout.assignCitizenLogin(username);
        context.lastCitizenLoginUsername = username;
        completeKeycloakLoginForm(username, password);

        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText("Sie sind angemeldet"),
                "Logged-in callout after Keycloak Bürger-Login");
        if (!shadow.shadowDomContainsText("Sie sind angemeldet.")) {
            ScenarioLogManager.getLogger()
                    .warn("zmscitizenview: Bürger-Login callout missing; submitting Keycloak again");
            if (!DriverUtil.getDriver().findElements(By.id("username")).isEmpty()) {
                completeKeycloakLoginForm(username, password);
            } else if (shadow.shadowDomContainsText("Anmelden")) {
                clickInAppBuergerLoginAnmelden();
                if (!DriverUtil.getDriver().findElements(By.id("username")).isEmpty()) {
                    completeKeycloakLoginForm(username, password);
                }
            }
            CitizenViewWaits.waitWithThreeWindows(
                    () -> shadow.shadowDomContainsText("Sie sind angemeldet"),
                    "Logged-in callout after Keycloak Bürger-Login retry");
        }
        Assert.assertTrue(
                shadow.shadowDomContainsText("Sie sind angemeldet."),
                "Expected 'Sie sind angemeldet.' after Bürger-Login. Kontakt was still showing Anmelden.");
        ScenarioLogManager.getLogger().info("zmscitizenview: Bürger-Login completed");
        page.trySetBookingProcessFromPage();
    }
    public void loginViaBuergerLoginWithKeycloakIfNeeded() throws Exception {
        context.set();
        if (shadow.shadowDomContainsText("Sie sind angemeldet.")) {
            ScenarioLogManager.getLogger().info("zmscitizenview: already logged in, skipping Bürger-Login");
            return;
        }
        ScenarioLogManager.getLogger().info("zmscitizenview: click in-app Anmelden (Bürger-Login, if needed)");
        try {
            new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                    .until(d -> clickInAppBuergerLoginAnmelden());
        } catch (TimeoutException e) {
            shadow.waitForAndClickButtonContaining("Anmelden", defaultWaitSeconds);
        }
        WebDriverWait wait = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds));
        wait.until(d -> shadow.shadowDomContainsText("Sie sind angemeldet.")
                || !d.findElements(By.id("username")).isEmpty());
        if (!shadow.shadowDomContainsText("Sie sind angemeldet.")) {
            String username = TestPropertiesHelper.getPropertyAsString("citizenUserName", true, "citizen");
            String password = TestPropertiesHelper.getPropertyAsString("citizenUserPassword", true, "vorschau");
            username = AccountCheckout.assignCitizenLogin(username);
            context.lastCitizenLoginUsername = username;
            completeKeycloakLoginForm(username, password);
        }
        CitizenViewWaits.waitWithThreeWindows(() -> shadow.shadowDomContainsText("Sie sind angemeldet"), "Logged-in callout");
        Assert.assertTrue(shadow.shadowDomContainsText("Sie sind angemeldet."), "Expected 'Sie sind angemeldet.'.");
    }
    public void cancelBuergerLoginOnKeycloakForm() throws Exception {
        context.set();
        String citizenUrl = DriverUtil.getDriver().getCurrentUrl();
        ScenarioLogManager.getLogger().info("zmscitizenview: click in-app Anmelden (Bürger-Login cancel)");
        try {
            new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                    .until(d -> clickInAppBuergerLoginAnmelden());
        } catch (TimeoutException e) {
            ScenarioLogManager.getLogger()
                    .warn("zmscitizenview: in-app Anmelden not found; falling back to any Anmelden button");
            shadow.waitForAndClickButtonContaining("Anmelden", defaultWaitSeconds);
        }

        WebDriverWait wait = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds));
        try {
            wait.until(ExpectedConditions.presenceOfElementLocated(By.id("username")));
        } catch (TimeoutException e) {
            throw new TimeoutException(
                    "Keycloak login form (#username) not shown after Anmelden. currentUrl="
                            + DriverUtil.getDriver().getCurrentUrl(),
                    e);
        }
        String returnUrl = accessDeniedReturnUrl(citizenUrl);
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: return from Keycloak as cancelled login url={}", returnUrl);
        DriverUtil.getDriver().navigate().to(returnUrl);
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText("Kontaktdaten"),
                "Kontakt after cancelled Bürger-Login");
        page.trySetBookingProcessFromPage();
    }
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
        context.set();
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText("Kontaktdaten"),
                "Kontakt form for empty email assertion");
        String email = shadow.deepInputValue("mailaddress");
        Assert.assertNotNull(email, "Kontakt E-Mail field was not found after cancelled Bürger-Login.");
        Assert.assertTrue(
                email.isBlank(),
                "Kontakt E-Mail should be empty after cancelled Bürger-Login, was: " + email);
        Assert.assertFalse(
                shadow.shadowDomContainsText("Sie sind angemeldet."),
                "Cancelled Bürger-Login must not leave the citizen logged in.");
    }
    boolean clickInAppBuergerLoginAnmelden() {
        context.set();
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
        context.set();
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText("Sie sind angemeldet"),
                "Logged-in callout on Kontakt");
        Assert.assertTrue(
                shadow.shadowDomContainsText("Sie sind angemeldet."),
                "Expected 'Sie sind angemeldet.' after Bürger-Login on Kontakt form.");
    }
    /**
     * ZMSKVR-718: fill a contact field to its max length and assert the counter error copy.
     *
     * @param fieldId host id ({@code firstname}, {@code lastname}, {@code mailaddress}, {@code remarks}, …)
     * @param maxChars maximum characters shown in the error (50 for name/mail, 250 for remarks)
     */
    public void assertMaxLengthCounterError(String fieldId, int maxChars) {
        context.set();
        // Name/mail: error at length >= max. Remarks: error at plainTextCharCount > max, and the
        // MucTextArea maxlength attribute blocks ordinary typing at exactly max.
        boolean remarks = fieldId != null && fieldId.startsWith("remarks");
        int fillLen = remarks ? maxChars + 1 : maxChars;
        if (remarks) {
            Assert.assertTrue(
                    deepSetByIdBypassingMaxlength(fieldId, "x".repeat(fillLen)),
                    "Could not fill #" + fieldId + " past maxlength");
        } else {
            Assert.assertTrue(shadow.deepSetById(fieldId, "x".repeat(fillLen)), "Could not fill #" + fieldId);
        }
        CitizenViewWaits.sleepQuiet(300L);
        String expected =
                "Ihre Eingabe in diesem Feld darf " + maxChars + " Zeichen nicht überschreiten.";
        Assert.assertTrue(
                fieldGroupContainsText(fieldId, expected),
                "Expected max-length counter error on #" + fieldId + " only: " + expected);
    }

    /** True when {@code expected} appears in the Muc form-group that owns {@code fieldId}. */
    private boolean fieldGroupContainsText(String fieldId, String expected) {
        String script =
                "var want=arguments[0],expected=arguments[1]==null?'':String(arguments[1]);"
                        + "var ids=[];ids.push(want);"
                        + "if(want.indexOf('textarea-')===0)ids.push(want.slice(9));else ids.push('textarea-'+want);"
                        + "if(want.indexOf('input-')===0)ids.push(want.slice(6));else ids.push('input-'+want);"
                        + "function byId(root,id){try{if(root.getElementById)return root.getElementById(id);}catch(e0){}"
                        + "try{return root.querySelector('#'+id.replace(/([^a-zA-Z0-9_-])/g,'\\\\$1'));}catch(e1){"
                        + "return root.querySelector('[id=\"'+id.replace(/\"/g,'')+'\"]');}}"
                        + "function resolve(el){if(!el)return null;if(el.tagName==='INPUT'||el.tagName==='TEXTAREA')return el;"
                        + "if(el.shadowRoot){var q=el.shadowRoot.querySelector('input:not([type=hidden]),textarea');if(q)return q;}"
                        + "return el;}"
                        + "function scanRoot(root){if(!root)return null;for(var i=0;i<ids.length;i++){var el=byId(root,ids[i]);if(el)return resolve(el);}"
                        + "var nodes=root.querySelectorAll('*');for(var j=0;j<nodes.length;j++){if(nodes[j].shadowRoot){var r2=scanRoot(nodes[j].shadowRoot);if(r2)return r2;}}return null;}"
                        + "var e=scanRoot(document);if(!e)e=scanRoot(document.body);if(!e)return false;"
                        + "var n=e;"
                        + "for(var g=0;g<12&&n;g++){"
                        + "if(n.classList&&n.classList.contains('m-form-group')){"
                        + "return (n.textContent||'').indexOf(expected)>=0;}"
                        + "if(n.parentElement){n=n.parentElement;continue;}"
                        + "var root=n.getRootNode&&n.getRootNode();n=root&&root.host?root.host:null;}"
                        + "return false;";
        Object o =
                ((JavascriptExecutor) DriverUtil.getDriver())
                        .executeScript(script, fieldId, expected);
        return Boolean.TRUE.equals(o);
    }

    /**
     * Like {@link ShadowDom#deepSetById} but clears the HTML maxlength so remarks can exceed 250
     * (product shows the overflow error only when {@code plainTextCharCount > 250}).
     */
    private boolean deepSetByIdBypassingMaxlength(String fieldId, String value) {
        String script =
                "var want=arguments[0],v=arguments[1]==null?'':String(arguments[1]);"
                        + "var ids=[];ids.push(want);"
                        + "if(want.indexOf('textarea-')===0)ids.push(want.slice(9));else ids.push('textarea-'+want);"
                        + "if(want.indexOf('input-')===0)ids.push(want.slice(6));else ids.push('input-'+want);"
                        + "function byId(root,id){try{if(root.getElementById)return root.getElementById(id);}catch(e0){}"
                        + "try{return root.querySelector('#'+id.replace(/([^a-zA-Z0-9_-])/g,'\\\\$1'));}catch(e1){"
                        + "return root.querySelector('[id=\"'+id.replace(/\"/g,'')+'\"]');}}"
                        + "function resolve(el){if(!el)return null;if(el.tagName==='INPUT'||el.tagName==='TEXTAREA')return el;"
                        + "if(el.shadowRoot){var q=el.shadowRoot.querySelector('input:not([type=hidden]),textarea');if(q)return q;}return null;}"
                        + "function scanRoot(root){if(!root)return null;for(var i=0;i<ids.length;i++){var el=byId(root,ids[i]);var r=resolve(el);if(r)return r;}"
                        + "var nodes=root.querySelectorAll('*');for(var j=0;j<nodes.length;j++){if(nodes[j].shadowRoot){var r2=scanRoot(nodes[j].shadowRoot);if(r2)return r2;}}return null;}"
                        + "var e=scanRoot(document);if(!e)e=scanRoot(document.body);"
                        + "if(!e)return false;e.removeAttribute('maxlength');e.scrollIntoView({block:'center'});e.focus();e.value=v;"
                        + "try{e.dispatchEvent(new InputEvent('input',{bubbles:true,cancelable:true,inputType:'insertReplacementText',data:v}));}"
                        + "catch(ex){e.dispatchEvent(new Event('input',{bubbles:true}));}"
                        + "e.dispatchEvent(new Event('change',{bubbles:true}));return true;";
        Object o =
                ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, fieldId, value);
        return Boolean.TRUE.equals(o);
    }

    /** ZMSKVR-716: Muc character counter stays at .875rem on desktop (MucTextArea only). */
    public void assertCharacterCountFontSizeDesktop() {
        context.set();
        RemoteWebDriver driver = DriverUtil.getDriver();
        driver.manage().window().setSize(ViewportSizes.DESKTOP);
        CitizenViewWaits.sleepQuiet(400L);
        Assert.assertTrue(
                shadow.deepElementExists("#remarks")
                        || shadow.deepElementExists("textarea#textarea-remarks")
                        || shadow.deepElementExists("#textarea-remarks"),
                "Expected remarks MucTextArea (only MucTextArea paints .m-character-count)");
        // Counter is a MucTextArea sibling — fill remarks so length/maxlength paint.
        Assert.assertTrue(shadow.deepSetById("remarks", "x".repeat(48)), "Could not fill #remarks");
        CitizenViewWaits.sleepQuiet(200L);
        String script =
                "function walk(n,fn){if(!n)return null;if(n.nodeType===1){var hit=fn(n);if(hit)return hit;}"
                        + "if(n.shadowRoot){var i=walk(n.shadowRoot,fn);if(i)return i;}"
                        + "var c=n.children;if(c)for(var k=0;k<c.length;k++){var x=walk(c[k],fn);if(x)return x;}return null;}"
                        + "var el=walk(document.body,function(n){"
                        + "if(n.classList&&n.classList.contains('m-character-count'))return n;return null;});"
                        + "if(!el)return null;"
                        + "var fs=getComputedStyle(el).fontSize;return {fontSize:fs,px:parseFloat(fs)};";
        Object raw = ((JavascriptExecutor) driver).executeScript(script);
        Assert.assertNotNull(raw, "No .m-character-count on the Kontakt form");
        @SuppressWarnings("unchecked")
        java.util.Map<String, Object> map = (java.util.Map<String, Object>) raw;
        double px = ((Number) map.get("px")).doubleValue();
        // 0.875rem at 16px root = 14px
        Assert.assertTrue(
                Math.abs(px - 14.0) <= 1.0,
                "Counter font-size on desktop should be .875rem (~14px), was " + map);
    }

    /**
     * ZMSKVR-719: long remarks grow the textarea ({@code lines + 2}) so the counter sits in the
     * reserved bottom band instead of covering typed text.
     */
    public void assertRemarksTextareaGrowsWithoutCounterOverlap() {
        context.set();
        RemoteWebDriver driver = DriverUtil.getDriver();
        driver.manage().window().setSize(ViewportSizes.MOBILE);
        CitizenViewWaits.sleepQuiet(400L);
        Assert.assertTrue(
                shadow.deepElementExists("#remarks")
                        || shadow.deepElementExists("textarea#textarea-remarks")
                        || shadow.deepElementExists("#textarea-remarks"),
                "Expected remarks textarea (custom textfield) for the overlap check");
        String longText =
                ("Lorem ipsum dolor sit amet, consectetuer adipiscing elit. ").repeat(4);
        Assert.assertTrue(shadow.deepSetById("remarks", longText), "Could not fill remarks");
        // textfieldRows grows on input; give Vue a tick
        CitizenViewWaits.sleepQuiet(500L);
        String script =
                "function walk(n,fn){if(!n)return null;if(n.nodeType===1){var hit=fn(n);if(hit)return hit;}"
                        + "if(n.shadowRoot){var i=walk(n.shadowRoot,fn);if(i)return i;}"
                        + "var c=n.children;if(c)for(var k=0;k<c.length;k++){var x=walk(c[k],fn);if(x)return x;}return null;}"
                        + "var ta=walk(document.body,function(n){"
                        + "if((n.tagName||'').toUpperCase()==='TEXTAREA'&&((n.id||'').indexOf('remarks')>=0||(n.getAttribute('id')||'')==='textarea-remarks'))return n;"
                        + "return null;});"
                        + "if(!ta)return {found:false};"
                        + "var counter=walk(ta.getRootNode&&ta.getRootNode().host?ta.getRootNode().host:document.body,function(n){"
                        + "if(n.classList&&n.classList.contains('m-character-count'))return n;return null;});"
                        + "if(!counter){var host=ta;for(var g=0;g<8&&host;g++){"
                        + "counter=walk(host,function(n){if(n.classList&&n.classList.contains('m-character-count'))return n;return null;});"
                        + "if(counter)break;"
                        + "if(host.parentElement)host=host.parentElement;else{var r=host.getRootNode&&host.getRootNode();host=r&&r.host?r.host:null;}}}"
                        + "var tr=ta.getBoundingClientRect();var rows=parseInt(ta.getAttribute('rows')||'0',10);"
                        + "var lineH=parseFloat(getComputedStyle(ta).lineHeight)||20;"
                        + "var cr=null;var inReservedBand=false;var noInnerScroll=false;"
                        + "if(counter){cr=counter.getBoundingClientRect();"
                        // Counter is drawn inside the textarea box; product keeps ~2 empty rows for it.
                        + "var bandTop=tr.bottom-Math.max(lineH*2.2,28);"
                        + "inReservedBand=cr.top>=bandTop-4&&cr.bottom<=tr.bottom+4;"
                        + "noInnerScroll=ta.scrollHeight<=ta.clientHeight+2;}"
                        + "return {found:true,rows:rows,height:tr.height,hasCounter:!!counter,"
                        + "inReservedBand:inReservedBand,noInnerScroll:noInnerScroll,"
                        + "lineH:lineH};";
        Object raw = ((JavascriptExecutor) driver).executeScript(script);
        Assert.assertNotNull(raw, "Could not measure remarks textarea");
        @SuppressWarnings("unchecked")
        java.util.Map<String, Object> map = (java.util.Map<String, Object>) raw;
        Assert.assertTrue(Boolean.TRUE.equals(map.get("found")), "remarks textarea missing: " + map);
        Assert.assertTrue(
                ((Number) map.get("rows")).intValue() >= 4 || ((Number) map.get("height")).doubleValue() >= 80,
                "Remarks textarea should grow with long input: " + map);
        Assert.assertTrue(
                Boolean.TRUE.equals(map.get("hasCounter")),
                "Remarks character counter missing: " + map);
        Assert.assertTrue(
                Boolean.TRUE.equals(map.get("inReservedBand")),
                "Counter should sit in the reserved bottom band of the grown textarea: " + map);
        Assert.assertTrue(
                Boolean.TRUE.equals(map.get("noInnerScroll")),
                "Grown remarks textarea should fit its text without inner scroll under the counter: "
                        + map);
    }

    void completeKeycloakLoginForm(String username, String password) throws Exception {
        var driver = DriverUtil.getDriver();
        WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(defaultWaitSeconds));
        try {
            wait.until(ExpectedConditions.presenceOfElementLocated(By.id("username")));
        } catch (TimeoutException e) {
            throw new TimeoutException(
                    "Keycloak login form (#username) not shown after Anmelden. currentUrl="
                            + driver.getCurrentUrl(),
                    e);
        }
        wait.until(ExpectedConditions.presenceOfElementLocated(By.id("kc-login")));
        ScenarioLogManager.getLogger().info("zmscitizenview: Keycloak login form detected url={}", driver.getCurrentUrl());

        ScenarioLogManager.getLogger().info("zmscitizenview: entering Keycloak citizen credentials");
        WebElement user = driver.findElement(By.id("username"));
        user.clear();
        user.sendKeys(username);
        WebElement pass = driver.findElement(By.id("password"));
        pass.clear();
        pass.sendKeys(password);
        driver.findElement(By.id("kc-login")).click();
        ScenarioLogManager.getLogger().info("zmscitizenview: Keycloak login submitted");
    }
}
