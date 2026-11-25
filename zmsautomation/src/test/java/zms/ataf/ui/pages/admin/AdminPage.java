package zms.ataf.ui.pages.admin;

import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.time.Duration;
import java.util.List;

import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedCondition;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.helpers.AuthenticationHelper;
import ataf.core.helpers.TestDataHelper;
import ataf.core.helpers.TestPropertiesHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.core.properties.DefaultValues;
import ataf.web.model.LocatorType;
import ataf.web.pages.BasePage;
import zms.ataf.helpers.AccountCheckout;


public class AdminPage extends BasePage {

    private static final String HEADER_CHANGE_SELECTION_LOCATOR = ".page-header__scope a[title=\"Auswahl ändern\"]";
    private static final String NAV_SELECT_LOCATION_LOCATOR = "//nav//a[normalize-space()='Standort auswählen']";
    /** Workstation select form. The queue cluster switch is also name="scope" and must not match. */
    private static final By WORKSTATION_SELECT_FORM = By.cssSelector("[data-scope-select-form]");
    private static final String WORKSTATION_SCOPE_SELECT = "[data-scope-select-form] select[name='scope']";

    protected final AdminPageContext CONTEXT;

    public AdminPage(RemoteWebDriver driver) {
        super(driver);
        CONTEXT = new AdminPageContext(driver);
    }

    public AdminPage(RemoteWebDriver driver, AdminPageContext adminPageContext) {
        super(driver);
        CONTEXT = adminPageContext;
    }

    public AdminPageContext getContext() {
        return CONTEXT;
    }

    public void navigateToPage() {
        CONTEXT.navigateToPage();
    }

    public void clickOnLoginButton() throws Exception {
        final StringBuilder clearUserName = new StringBuilder();
        AuthenticationHelper.getUserName().access(clearUserName::append);
        String assigned = AccountCheckout.assignWorkstationLogin(clearUserName.toString());
        clearUserName.setLength(0);
        loginWithKeycloakUser(assigned);
    }

    /** Keycloak login for one workstation user. The caller checks the account out. */
    public void loginWithKeycloakUser(String username) throws Exception {
        loginWithKeycloakUser(username, By.name("scope"));
    }

    /**
     * Same login, but waits for {@code ready} instead of the location form.
     * Benutzerverwaltung and Innenrevision never reach that form.
     */
    public void loginWithKeycloakUser(String username, By ready) throws Exception {
        if (isAlreadyLoggedIn()) {
            ScenarioLogManager.getLogger().info("Already logged in, skipping SSO login.");
            return;
        }
        ScenarioLogManager.getLogger().info("Trying to click on \"Login\" button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[@type='submit' and @value='keycloak']", LocatorType.XPATH, false);
        ScenarioLogManager.getLogger().info("SSO-Login page detected!");

        final StringBuilder clearPassword = new StringBuilder();
        Exception exception = null;
        try {
            AuthenticationHelper.getUserPassword().access(clearPassword::append);
            // Wait for Keycloak login form (local and ssodev both use id="username")
            WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
            wait.until(ExpectedConditions.presenceOfElementLocated(By.id("username")));
            if ("chrome".equals(TestPropertiesHelper.getPropertyAsString("browser", true, DefaultValues.BROWSER))) {
                wait.until(ExpectedConditions.presenceOfElementLocated(By.id("kc-login")));
                String credentials = URLEncoder.encode(username, StandardCharsets.UTF_8) + ":"
                        + URLEncoder.encode(clearPassword.toString(), StandardCharsets.UTF_8) + "@";
                DRIVER.navigate().to(DRIVER.getCurrentUrl().replaceFirst("^(https?://)", "$1" + credentials));
            }

            ScenarioLogManager.getLogger().info("Trying to enter user name...");
            enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, username, "username", LocatorType.ID);

            ScenarioLogManager.getLogger().info("Trying to enter password...");
            enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, clearPassword.toString(), "password", LocatorType.ID);

            ScenarioLogManager.getLogger().info("Trying to click on \"Login\" button...");
            WebElement kcLogin = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                    .until(ExpectedConditions.elementToBeClickable(By.id("kc-login")));
            ((JavascriptExecutor) DRIVER).executeScript("arguments[0].click();", kcLogin);
            // Restored workstation sessions skip the scope form and land on the queue header.
            new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                    .until(ExpectedConditions.or(
                            ExpectedConditions.presenceOfElementLocated(ready),
                            ExpectedConditions.presenceOfElementLocated(
                                    By.cssSelector(HEADER_CHANGE_SELECTION_LOCATOR))));
            ScenarioLogManager.getLogger().info("SSO login submitted successfully.");
        } catch (Exception e) {
            ScenarioLogManager.getLogger().error(e.getMessage(), e);
            exception = e;
        } finally {
            clearPassword.setLength(0);
            if (exception != null) {
                throw exception;
            }
        }
    }

    private boolean isAlreadyLoggedIn() {
        return isWebElementVisible(2, "//*[contains(text(),'Sie sind bereits angemeldet')]", LocatorType.XPATH, false)
                || isWebElementVisible(1, HEADER_CHANGE_SELECTION_LOCATOR, LocatorType.CSSSELECTOR, false);
    }

    public void selectLocation(String location) {
        CONTEXT.set();
        ensureScopeSelectionForm();
        ScenarioLogManager.getLogger().info("Trying to select location \"" + location + "\"");
        AccountCheckout.checkout("scope:" + location);
        // The combobox clips the native <select>. Firefox will not click that option, so
        // select-by-visible-text waits out. Set the option value once its label is present.
        StaleElementReferenceException lastStale = null;
        for (int attempt = 1; attempt <= 3; attempt++) {
            try {
                setScopeValue(waitForScopeValueByLabel(location));
                TestDataHelper.setTestData("location", location);
                return;
            } catch (StaleElementReferenceException stale) {
                lastStale = stale;
                ScenarioLogManager.getLogger()
                        .warn("Standort select went stale (attempt {}); refreshing form.", attempt);
                ensureScopeSelectionForm();
            }
        }
        throw lastStale;
    }

    /**
     * Picks the Standort option whose value is the scope id. The visible label is
     * {@code contact.name + " " + shortName}, and a trailing space in the short name
     * must not change which Standort is selected.
     */
    public void selectLocationByScopeId(int scopeId) {
        CONTEXT.set();
        ensureScopeSelectionForm();
        String scopeValue = Integer.toString(scopeId);
        WebElement selectEl = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.presenceOfElementLocated(By.cssSelector(WORKSTATION_SCOPE_SELECT)));
        String visible = null;
        for (WebElement option : selectEl.findElements(By.tagName("option"))) {
            if (scopeValue.equals(option.getAttribute("value"))) {
                visible = optionLabel(option);
                break;
            }
        }
        if (visible == null || visible.isBlank()) {
            throw new IllegalStateException("No Standort option for scope " + scopeId);
        }
        ScenarioLogManager.getLogger().info("Trying to select location \"" + visible + "\" for scope " + scopeId);
        AccountCheckout.checkout("scope:" + visible);
        setScopeValue(scopeValue);
        TestDataHelper.setTestData("location", visible);
    }

    /** textContent stays readable after the combobox clips the native options. */
    private static String optionLabel(WebElement option) {
        String text = option.getAttribute("textContent");
        if (text == null || text.isBlank()) {
            text = option.getText();
        }
        return text == null ? "" : text.trim().replaceAll("\\s+", " ");
    }

    private String waitForScopeValueByLabel(String location) {
        String wanted = location.trim().replaceAll("\\s+", " ");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.ignoring(StaleElementReferenceException.class);
        return wait.until(driver -> {
            WebElement selectEl = driver.findElement(By.cssSelector(WORKSTATION_SCOPE_SELECT));
            for (WebElement option : selectEl.findElements(By.tagName("option"))) {
                if (!wanted.equals(optionLabel(option))) {
                    continue;
                }
                String value = option.getAttribute("value");
                if (value != null && !value.isBlank()) {
                    return value;
                }
            }
            return null;
        });
    }

    /** Same assignment as the combobox choose(): the clipped option is not clickable in Firefox. */
    private void setScopeValue(String scopeValue) {
        WebElement selectEl = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .ignoring(StaleElementReferenceException.class)
                .until(ExpectedConditions.presenceOfElementLocated(By.cssSelector(WORKSTATION_SCOPE_SELECT)));
        ((JavascriptExecutor) DRIVER).executeScript(
                "const select = arguments[0];"
                        + "const value = String(arguments[1]);"
                        + "select.value = value;"
                        + "select.dispatchEvent(new Event('change', { bubbles: true }));"
                        + "const toggle = select.closest('.scope-combobox')?.querySelector('.scope-combobox__toggle');"
                        + "const option = [...select.options].find((item) => item.value === value);"
                        + "if (toggle && option) {"
                        + "  toggle.textContent = '(Id: ' + value + ') ' + option.textContent.trim();"
                        + "}",
                selectEl,
                scopeValue);
    }

    public void enterWorkstation(String workstation) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to enter workstation \"" + workstation + "\"");
        workstation = AccountCheckout.queueDesk(workstation);
        enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, workstation, "workstation", LocatorType.NAME);
        TestDataHelper.setTestData("workstation", workstation);
    }

    public void clickOnApplySelectionButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Auswahl bestätigen\" button...");
        CONTEXT.set();
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[@type='submit' and @value='weiter']", LocatorType.XPATH, false, CONTEXT);
    }

    //prüfen ob die eingegebenen Standort und Platz-Nr im Seitenkopf angezeigt werden.
    public void enteredWorkplaceInformationMatchWithPageHeader() {
        String actualLocation = findElementByLocatorType(".user-scope.header-scope-title", LocatorType.CSSSELECTOR, true)
                .getAttribute("title");
        String expectedLocation = TestDataHelper.getTestData("location");
        boolean location = expectedLocation.contains(actualLocation);

        String actualWorkstation = findElementByLocatorType("//li[contains(@class, 'user-workstation')]/div/strong", LocatorType.XPATH, true)
                .getText();
        String expectedWorkstation = TestDataHelper.getTestData("workstation");
        boolean workstation = actualWorkstation.contains(expectedWorkstation);

        Assert.assertTrue(location,
                String.format("Der Standort stimmt nicht mit dem Seitenkopf überein. Erwartet: %s, Gefunden: %s", expectedLocation, actualLocation));
        Assert.assertTrue(workstation,
                String.format("Die Arbeitsplatznummer stimmt nicht mit dem Seitenkopf überein. Erwartet: %s, Gefunden: %s", expectedWorkstation,
                        actualWorkstation));
    }

    public void clickInHeaderOnChangeSelectionButton() {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to click on 'Auswahl ändern' Button");
        // The queue renders its own select[name=scope] (cluster switch, often disabled).
        // Treating that as the Standort form skips "Auswahl ändern" and then waits on a
        // control that never lists the target location.
        By header = By.cssSelector(HEADER_CHANGE_SELECTION_LOCATOR);
        By nav = By.xpath(NAV_SELECT_LOCATION_LOCATOR);
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.pollingEvery(Duration.ofMillis(250));
        wait.ignoring(StaleElementReferenceException.class);
        wait.withMessage("Waited " + DEFAULT_EXPLICIT_WAIT_TIME
                + " seconds for the Standort form or 'Auswahl ändern'.");
        boolean[] opened = {false};
        wait.until((ExpectedCondition<Boolean>) driver -> openScopeSelection(driver, header, nav, opened));
    }

    private boolean workstationSelectFormOpen(WebDriver driver) {
        return !driver.findElements(WORKSTATION_SELECT_FORM).isEmpty();
    }

    private boolean openScopeSelection(WebDriver driver, By header, By nav, boolean[] opened) {
        if (workstationSelectFormOpen(driver)) {
            if (!opened[0]) {
                ScenarioLogManager.getLogger().info("Standort form already open; skipping 'Auswahl ändern'.");
            }
            return true;
        }
        // One navigation. Polling used to click again every 250ms, and Firefox was still
        // loading the select page, so each click cancelled the previous one.
        if (opened[0]) {
            return false;
        }
        if (openFirstDisplayed(driver, header)) {
            opened[0] = true;
            return workstationSelectFormOpen(driver);
        }
        if (openFirstDisplayed(driver, nav)) {
            opened[0] = true;
            ScenarioLogManager.getLogger().info("Header 'Auswahl ändern' not visible, using nav link 'Standort auswählen'");
            return workstationSelectFormOpen(driver);
        }
        return false;
    }

    private boolean openFirstDisplayed(WebDriver driver, By locator) {
        for (WebElement element : driver.findElements(locator)) {
            try {
                if (!element.isDisplayed()) {
                    continue;
                }
                String href = element.getAttribute("href");
                if (href == null || href.isBlank()) {
                    continue;
                }
                driver.navigate().to(href);
                return true;
            } catch (StaleElementReferenceException ex) {
                return false;
            }
        }
        return false;
    }

    /** Opens the Standort form when SSO restored an existing workstation session. */
    private void ensureScopeSelectionForm() {
        if (workstationSelectFormOpen(DRIVER)) {
            return;
        }
        ScenarioLogManager.getLogger().info("Scope form missing after login; opening Standort selection.");
        clickInHeaderOnChangeSelectionButton();
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.presenceOfElementLocated(WORKSTATION_SELECT_FORM));
    }

    public void clickInNavigationOnOverallCalendar() {
        ScenarioLogManager.getLogger().info("Trying to open the Gesamtübersicht...");
        clickOnWebElement(
                DEFAULT_EXPLICIT_WAIT_TIME,
                "//nav//a[normalize-space()='Gesamtübersicht']",
                LocatorType.XPATH,
                true,
                CONTEXT);
    }

    public void clickInNavigationOnWorkstation() {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to open the Sachbearbeiterplatz...");
        List<WebElement> nav = DRIVER.findElements(By.xpath("//nav//a[contains(@href,'workstation')]"));
        if (nav.stream().anyMatch(WebElement::isDisplayed)) {
            clickOnWebElement(
                    DEFAULT_EXPLICIT_WAIT_TIME,
                    "//nav//a[contains(@href,'workstation')]",
                    LocatorType.XPATH,
                    true,
                    CONTEXT);
            return;
        }
        // Gesamtübersicht has no sidebar. Zurück points at the counter and, on click, follows
        // document.referrer (the edit form). The queue lives on the Sachbearbeiterplatz.
        WebElement back = findElementByLocatorType("back-button", LocatorType.ID, true);
        String bookedDate = TestDataHelper.getTestData("new_appointment_iso_date");
        String dateQuery = bookedDate == null || bookedDate.isBlank() ? "" : "?date=" + bookedDate;
        ((JavascriptExecutor) DRIVER).executeScript(
                "window.location.assign(arguments[0].href.replace(/counter\\/?$/, 'workstation/') + arguments[1]);",
                back,
                dateQuery);
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.textToBePresentInElementLocated(By.cssSelector("h1"), "Sachbearbeiterplatz"));
    }

    public void clickInNavigationOnTresenButton() {
        ScenarioLogManager.getLogger().info("Trying to click on 'Tresen' Button");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//a[@title='Termine vereinbaren, ändern & löschen und die Warteschlange verwalten']", LocatorType.XPATH,
                true, CONTEXT);
    }

    public void checkForLocationPage() {
        ScenarioLogManager.getLogger().info("Checking if 'Standort auswählen' page is correctly loaded.");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//h2[@class='board__heading']", LocatorType.XPATH, false),
                "'Standort und Arbeitsplatz auswählen' Header is not visible");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//button[@value='weiter']", LocatorType.XPATH, true),
                "'Auswahl bestätigen' Button is not visible");
    }

    public boolean isPopUpVisible(String popUpName) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.pollingEvery(Duration.ofMillis(500L));
        try {
            wait.until((ExpectedCondition<Boolean>) waitDriver -> {
                List<WebElement> titleElements = findElementsByLocatorType(0L,
                        "//div[@role='dialog']/section[contains(@class, 'dialog')]/h2[contains(@class, 'title')]", LocatorType.XPATH);
                if (!titleElements.isEmpty()) {
                    for (WebElement titleElement : titleElements) {
                        if (titleElement.isDisplayed()) {
                            return titleElement.getText().contains(popUpName);
                        }
                    }
                }
                return false;
            });
        } catch (TimeoutException e) {
            return false;
        }
        return true;
    }

    public boolean isPopUpInvisible(String popUpName) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.pollingEvery(Duration.ofMillis(500L));
        try {
            wait.until(ExpectedConditions.invisibilityOfElementLocated(By.xpath(
                    String.format("//div[@role='dialog']/section[contains(@class, 'dialog')]/h2[contains(@class, 'title') and text()='%s']", popUpName))));
        } catch (TimeoutException e) {
            return false;
        }
        return true;
    }

    /** ZMS-3212 / ZMSALT-3577: Tresen login must not show Sachbearbeiterplatz. */
    public void assertNavigationDoesNotShow(String label) {
        CONTEXT.set();
        waitForPrimaryNavigation();
        String compact = label.replace("\u00ad", "").replace("-", "");
        boolean visible = DRIVER.findElements(By.cssSelector("nav.navigation-primary a")).stream()
                .filter(WebElement::isDisplayed)
                .map(a -> a.getText().replace("\u00ad", "").replace("\n", "").replace("-", "").trim())
                .anyMatch(t -> t.equalsIgnoreCase(compact) || t.contains(compact));
        Assert.assertFalse(visible, "Navigation must not show \"" + label + "\" for Tresen login.");
    }

    /** Active Notruf covers the page until Ausblenden is used. */
    public void minimizeNotrufOverlay() {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Minimizing the Notruf overlay...");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement hide = wait.until(ExpectedConditions.elementToBeClickable(
                By.cssSelector("button.emergency__button-hide")));
        hide.click();
        wait.until(ExpectedConditions.presenceOfElementLocated(
                By.cssSelector(".emergency[data-minimized]")));
    }

    /** ZMS-3212: NOTRUF trigger is only rendered when workstation.name is set. */
    public void assertNotrufTriggerNotVisible() {
        CONTEXT.set();
        waitForPrimaryNavigation();
        List<WebElement> triggers = DRIVER.findElements(By.cssSelector("button.emergency__button-trigger"));
        boolean visible = triggers.stream().anyMatch(WebElement::isDisplayed);
        Assert.assertFalse(visible, "NOTRUF trigger must stay hidden without Platz-Nr.");
    }

    private void waitForPrimaryNavigation() {
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.presenceOfElementLocated(By.cssSelector("nav.navigation-primary")));
    }

}
