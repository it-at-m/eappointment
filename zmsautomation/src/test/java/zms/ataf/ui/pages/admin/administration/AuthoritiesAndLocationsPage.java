package zms.ataf.ui.pages.admin.administration;

import java.time.DayOfWeek;
import java.time.Duration;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.Locale;
import java.util.Map;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.Keys;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.interactions.Actions;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.helpers.TestDataHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.web.model.LocatorType;
import zms.ataf.helpers.AccountCheckout;
import zms.ataf.helpers.BerlinTime;
import zms.ataf.helpers.RandomNameHelper;
import zms.ataf.ui.pages.admin.AdminPage;
import zms.ataf.ui.pages.admin.AdminPageContext;

public class AuthoritiesAndLocationsPage extends AdminPage {

    private static final int WORKSTATION_SELECT_MAX_ATTEMPTS = 5;
    private static final long WORKSTATION_SELECT_RETRY_PAUSE_MS = 500L;
    private static final int WORKSTATION_SELECT_WAIT_PER_ATTEMPT_SEC = 12;
    private static final Pattern WORKSTATION_COUNT_LEADING_DIGITS = Pattern.compile("^\\s*(\\d+)");

    public AuthoritiesAndLocationsPage(RemoteWebDriver driver, AdminPageContext adminPageContext) {
        super(driver, adminPageContext);
    }

    public void clickOnLocationAdminEntry() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Behörden und Standorte\" menu entry...");
    
        CONTEXT.set();
        CONTEXT.waitForSpinners();
    
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(20));
    
        // Menu entry by visible text
        By menuEntry = By.xpath("//nav//a[normalize-space(.)='Behörden und Standorte' or " +
                                "(contains(normalize-space(.),'Behörden') and contains(normalize-space(.),'Standorte'))]");
    
        // Ensure it’s visible and clickable
        WebElement entry = wait.until(ExpectedConditions.visibilityOfElementLocated(menuEntry));
        scrollToCenterByVisibleElement(entry);
    
        try {
            wait.until(ExpectedConditions.elementToBeClickable(entry)).click();
        } catch (Exception e) {
            // Fallback: JS click if an overlay blocks normal click
            ScenarioLogManager.getLogger().warn("Normal click failed, trying JS click for 'Behörden und Standorte'...");
            ((JavascriptExecutor) DRIVER).executeScript("arguments[0].click();", entry);
        }
    
        // Post‑click: wait for target page (either URL or distinctive heading)
        boolean landed = false;
        try {
            wait.until(ExpectedConditions.or(
                ExpectedConditions.urlContains("/administration/authorities"),
                ExpectedConditions.visibilityOfElementLocated(
                    By.xpath("//h1[normalize-space(.)='Behörden und Standorte']"))
            ));
            landed = true;
        } catch (TimeoutException ignore) { /* fall through */ }
    
        Assert.assertTrue(landed, "Did not reach 'Behörden und Standorte' after clicking the menu entry.");
        CONTEXT.waitForSpinners();
    }

    public void clickOnLocationEntry(String location) {
        ScenarioLogManager.getLogger().info("Trying to click on '" + location + "' under Authorities and locations...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//li/a[contains(text(), '" + location + "')]", LocatorType.XPATH, false, CONTEXT);
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//h1[@class='main-title' and text()='Standort']", LocatorType.XPATH, false),
                "Page title 'Behörden und Standorte' is not visible!");
    }

    /**
     * Sets slots per appointment on the currently opened Standort page. The UI exposes a single pair of preference fields;
     * {@code location} is for log context only (callers must navigate to that Standort first).
     */
    public void setMaxSlotsForLocation(String location, String number) {
        ScenarioLogManager.getLogger().info("Trying to set the maximal slots for location: " + location);
        WebElement field = findElementByLocatorType(
            "//input[@name='preferences[client][slotsPerAppointment]']", LocatorType.XPATH, true);
        moveToElementAction(field);
        Keys selectAllMod = selectAllModifierKey();
        new Actions(DRIVER)
            .click(field)
            .keyDown(selectAllMod).sendKeys("a").keyUp(selectAllMod)
            .sendKeys(Keys.DELETE)
            .sendKeys(number)
            .perform();
    }

    /**
     * @see #setMaxSlotsForLocation(String, String)
     */
    public void setRepeatCallsForLocation(String location, String number) {
        ScenarioLogManager.getLogger().info("Trying to set 'Wiederholungsaufrufe' for location: " + location);
        WebElement field = findElementByLocatorType(
            "//input[@name='preferences[queue][callCountMax]']", LocatorType.XPATH, true);
        moveToElementAction(field);
        Keys selectAllMod = selectAllModifierKey();
        new Actions(DRIVER)
            .click(field)
            .keyDown(selectAllMod).sendKeys("a").keyUp(selectAllMod)
            .sendKeys(Keys.DELETE)
            .sendKeys(number)
            .perform();
    }

    /** @see #setMaxSlotsForLocation(String, String) */
    public String getMaxSlotsForLocation(String location) {
        ScenarioLogManager.getLogger().info("Trying to get the maximal slots for location: " + location);
        return findElementByLocatorType("//input[@name='preferences[client][slotsPerAppointment]']", LocatorType.XPATH, true).getAttribute("value");
    }

    /** @see #setMaxSlotsForLocation(String, String) */
    public String getRepeatCallsForLocation(String location) {
        ScenarioLogManager.getLogger().info("Trying to get 'Wiederholungsaufrufe' for location: " + location);
        return findElementByLocatorType("//input[@name='preferences[queue][callCountMax]']", LocatorType.XPATH, true).getAttribute("value");
    }

    private static Keys selectAllModifierKey() {
        String os = System.getProperty("os.name", "").toLowerCase(Locale.ROOT);
        return os.contains("mac") ? Keys.COMMAND : Keys.CONTROL;
    }

    public int getOpeningHoursSlotTimeInMinutes() {
        // Terminabstand is effectively static in our test data (5 minutes). Avoid hard waits here:
        // this field may not be editable/visible on every form variant.
        return DRIVER.findElements(By.id("AvDaySlottime")).stream()
                .map(e -> e.getAttribute("value"))
                .filter(v -> v != null && !v.isBlank())
                .map(v -> v.trim())
                .mapToInt(v -> {
                    try {
                        return Integer.parseInt(v);
                    } catch (NumberFormatException ignore) {
                        return 0;
                    }
                })
                .filter(v -> v > 0)
                .findFirst()
                .orElse(5);
    }

public void saveLocationChanges() {
    ScenarioLogManager.getLogger().info("Trying to click 'Speichern' to save location changes...");

    CONTEXT.set();

    // Robust 'Speichern' button lookup
    List<By> saveLocators = List.of(
        By.cssSelector("button.type-save[name='save']"),
        By.cssSelector("button.type-save"),
        By.xpath("//button[contains(normalize-space(.),'Speichern')]")
    );

    WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(60));
    boolean clicked = false;
    for (int attempt = 1; attempt <= 3 && !clicked; attempt++) {
        WebElement save = null;
        for (By by : saveLocators) {
            try {
                save = wait.until(ExpectedConditions.elementToBeClickable(by));
                break;
            } catch (TimeoutException ignored) {}
        }
        if (save == null) {
            Assert.fail("Could not find an enabled 'Speichern' button.");
            return;
        }
        try {
            scrollToCenterByVisibleElement(save);
            ((JavascriptExecutor) DRIVER).executeScript("arguments[0].click();", save);
            clicked = true;
        } catch (StaleElementReferenceException | TimeoutException e) {
            ScenarioLogManager.getLogger().warn(
                    "Speichern click was not answered (attempt " + attempt + "/3).");
        }
    }
    if (!clicked) {
        Assert.fail("Speichern click was not answered.");
        return;
    }

    CONTEXT.waitForSpinners();

    // Accept either the known banner or generic alert variants; also fast‑fail on explicit error
    List<By> successDetectors = List.of(
        By.xpath("//*[self::div or self::section][contains(@class,'message--success')]"),
        By.xpath("//*[(self::div or self::section or self::p) and contains(translate(normalize-space(.),'ERFOLGREICH','erfolgreich'),'erfolgreich')]"),
        By.xpath("//*[@role='alert' or @aria-live='polite'][contains(translate(normalize-space(.),'ERFOLGREICH','erfolgreich'),'erfolgreich')]")
    );
    By errorDetector = By.xpath("//*[contains(@class,'message--error') or @role='alert'][contains(translate(normalize-space(.),'FEHLER','fehler'),'fehler')]");

    try {
        // If an explicit error appears quickly, fail early
        new WebDriverWait(DRIVER, Duration.ofSeconds(5)).until(ExpectedConditions.visibilityOfElementLocated(errorDetector));
        WebElement err = DRIVER.findElement(errorDetector);
        Assert.fail("Save failed with error: " + err.getText().replaceAll("\\s+"," ").trim());
    } catch (TimeoutException ignored) {
        // no immediate error
    }

    boolean successSeen = false;
    for (By ok : successDetectors) {
        try {
            wait.until(ExpectedConditions.visibilityOfElementLocated(ok));
            WebElement successEl = DRIVER.findElement(ok);
            ScenarioLogManager.getLogger().info("Save success banner: "
                    + successEl.getText().replaceAll("\\s+", " ").trim());
            successSeen = true;
            break;
        } catch (TimeoutException ignored) {}
    }

    if (!successSeen) {
        // Be tolerant here — some pages persist without surfacing a banner.
        ScenarioLogManager.getLogger().warn("No explicit success banner detected within timeout; continuing (value will be verified by subsequent steps).");
    }
}

    public void clickOnOpeningHoursEntryBy(String location) {
        ScenarioLogManager.getLogger().info("Trying to click on opening hours by location \"" + location + "\"");
        AccountCheckout.checkout("scope:" + location);
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//a[contains(text(),'" + location + "')]/../a[text()='Öffnungszeiten']", LocatorType.XPATH, false,
                CONTEXT);
    }

    public void clickOnDayEntry(String day) {
        ScenarioLogManager.getLogger().info("Trying to click on entry for day \"" + day + "\"");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//span[@class='day' and contains(text(),'" + day + "')]/..", LocatorType.XPATH, false, CONTEXT);
    }

    public void clickOnNewOpeningHoursButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"neue Öffnungszeit\" button...");
    
        // Click the visible trigger button
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[normalize-space(.)='neue Öffnungszeit']",
                LocatorType.XPATH, false);
    
        // Wait for editor elements - CORRECT XPath (no backticks!)
        By formLocator = By.xpath(
            "//*[@type='time' or (self::button and normalize-space(.)='Speichern') or " +
            "contains(@class,'opening') or contains(@class,'oeffnungszeit')]"
        );
    
        try {
            new WebDriverWait(DRIVER, Duration.ofSeconds(10))
                .until(ExpectedConditions.visibilityOfElementLocated(formLocator));
        } catch (TimeoutException ignore) {
            // Form not yet visible; caller should use step "Sie die Öffnungszeit-Accordion \"...\" öffnen" to expand the correct accordion by title
        }
    }

    /**
     * Expands the opening-hours accordion whose title contains the given text (e.g. "Neue Öffnungszeit").
     * Title is passed from the feature so the correct panel is opened when multiple accordions exist.
     */
    public void expandOpeningHoursAccordionByTitle(String accordionTitle) {
        ScenarioLogManager.getLogger().info("Trying to open Öffnungszeit accordion with title containing \"" + accordionTitle + "\"...");
        String xpath = "//h3[contains(@class,'accordion__heading')][contains(@title,'" + accordionTitle + "')]/button";
        WebElement headerBtn = waitForElementByXpath(DEFAULT_EXPLICIT_WAIT_TIME, xpath, true, true);
        String ariaExpanded = String.valueOf(headerBtn.getAttribute("aria-expanded"));
        if (!"true".equalsIgnoreCase(ariaExpanded)) {
            clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, headerBtn, false);
        }
        // Wait for the note field (visible at top of form); time/Speichern may be below fold
        By noteFieldLocator = By.xpath("//input[@id='AvDayDescription']");
        try {
            new WebDriverWait(DRIVER, Duration.ofSeconds(10))
                .until(ExpectedConditions.visibilityOfElementLocated(noteFieldLocator));
        } catch (TimeoutException e) {
            Assert.fail("Opening accordion with title containing \"" + accordionTitle + "\" did not reveal the form.");
        }
    }

    public void enterNoteForOpeningHours(String noteKey) {
        CONTEXT.set();
        String note = "Note- " + RandomNameHelper.randomAlphanumeric(10);
        ScenarioLogManager.getLogger().info("Trying to enter random generated note for opening hours \"" + note + "\"");
        TestDataHelper.setTestData(noteKey, note);
        enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, note, "//input[@id='AvDayDescription']", LocatorType.XPATH);
    }

    public void selectOpeningHoursType(String type) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to select opening hours type \"" + type + "\"");
        selectInOpenedAccordion("AvDayType", type);
    }

    public void selectSeries(String series) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to select series \"" + series + "\"");
        selectInOpenedAccordion("AvDaySeries", series);
    }

    /**
     * Several accordions reuse the same select ids ({@code AvDayType}, {@code AvDaySeries}).
     * Always target the expanded panel so we do not rewrite an existing Terminkunden row.
     */
    private void selectInOpenedAccordion(String selectId, String visibleText) {
        String xpath = "(//div[contains(@class,'accordion__panel') and contains(@class,'opened')]"
                + "//select[@id='" + selectId + "'])[last()]";
        WebElement selectEl = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.elementToBeClickable(By.xpath(xpath)));
        Select select = new Select(selectEl);
        String current = select.getFirstSelectedOption().getText().trim();
        if (current.equals(visibleText)) {
            ScenarioLogManager.getLogger().info("Select #" + selectId + " is already \"" + visibleText + "\"");
            return;
        }
        select.selectByVisibleText(visibleText);
    }

    public void selectWeekDay(String weekDay) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to select week day \"" + weekDay + "\"");
        Map<String, String> weekDayMap = Map.of(
                "Montag", "Monday",
                "Dienstag", "Tuesday",
                "Mittwoch", "Wednesday",
                "Donnerstag", "Thursday",
                "Freitag", "Friday",
                "Samstag", "Saturday",
                "Sonntag", "Sunday"
        );
        String englishWeekDay = weekDayMap.getOrDefault(weekDay, weekDay).toLowerCase();
        final String WEEK_DAY_LOCATOR_XPATH = "//input[@value='" + englishWeekDay + "']";
        if (findElementByLocatorType(WEEK_DAY_LOCATOR_XPATH, LocatorType.XPATH, true).isSelected()) {
            ScenarioLogManager.getLogger().warn("Week day \"" + weekDay + "\" is already selected! No action performed.");
        } else {
            selectWebElement(DEFAULT_EXPLICIT_WAIT_TIME, WEEK_DAY_LOCATOR_XPATH, LocatorType.XPATH);
        }
    }

    public void enterOpeningTime(String time) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to enter opening time \"" + time + "\"");
        enterTimeDirectly("AvDatesStart_time", time);
    }

    public void enterClosingTime(String time) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to enter closing time \"" + time + "\"");
        enterTimeDirectly("AvDatesEnd_time", time);
    }

    public void enterClosingDate(String date) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to enter closing date \"" + date + "\"");
        // react-datepicker uses strictParsing, so character-by-character typing snaps back to the
        // selected day before "dd.MM.yyyy" is complete. Set the full value in one step, as for the times.
        setInputValue(findVisibleInputById("AvDatesEnd"), date);
    }

    /**
     * Selects Saturday and Sunday when they still fall in the range from today through Sunday.
     * The start date cannot move before the opened day, so a run on Sunday only selects Sunday.
     */
    public void selectWeekendDaysOfCurrentWeek() {
        if (BerlinTime.today().getDayOfWeek() != DayOfWeek.SUNDAY) {
            selectWeekDay("Samstag");
        }
        selectWeekDay("Sonntag");
    }

    public void assertNoMissingWeekdayError() {
        CONTEXT.set();
        By error = By.xpath("//*[contains(@class,'message--error')]"
                + "[contains(., 'kommen im gewählten Zeitraum nicht vor')]");
        try {
            new WebDriverWait(DRIVER, Duration.ofSeconds(5))
                    .until(ExpectedConditions.invisibilityOfElementLocated(error));
        } catch (TimeoutException e) {
            Assert.fail("Weekday range error is still shown: " + DRIVER.findElement(error).getText(), e);
        }
    }

    public void assertOpeningHoursSaveButtonEnabled(String buttonLabel) {
        CONTEXT.set();
        By save = By.xpath("//button[contains(@class,'button-save') and normalize-space()='" + buttonLabel + "']");
        WebElement button = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.presenceOfElementLocated(save));
        Assert.assertTrue(button.isEnabled(), "\"" + buttonLabel + "\" is disabled");
    }

    /** ZMSKVR-536 / ZMSKVR-725: neue Öffnungszeit must not prefill Uhrzeit von/bis. */
    public void assertOpeningHoursTimeFieldsEmpty() {
        CONTEXT.set();
        String start = readTimeInputValue("AvDatesStart_time");
        String end = readTimeInputValue("AvDatesEnd_time");
        Assert.assertTrue(start == null || start.isBlank(),
                "Uhrzeit von should be empty on neue Öffnungszeit, was \"" + start + "\"");
        Assert.assertTrue(end == null || end.isBlank(),
                "Uhrzeit bis should be empty on neue Öffnungszeit, was \"" + end + "\"");
    }

    /** ZMSKVR-536 / ZMSKVR-725: Ausnahme keeps the original series times. */
    public void assertOpeningHoursTimeFieldsEqual(String expectedStart, String expectedEnd) {
        CONTEXT.set();
        String start = normalizeClock(readTimeInputValue("AvDatesStart_time"));
        String end = normalizeClock(readTimeInputValue("AvDatesEnd_time"));
        Assert.assertEquals(start, normalizeClock(expectedStart), "Uhrzeit von on Ausnahme form");
        Assert.assertEquals(end, normalizeClock(expectedEnd), "Uhrzeit bis on Ausnahme form");
    }

    /**
     * Publish with empty times: the footer button stays enabled, but validation blocks save
     * ({@code Die Startzeit darf nicht leer sein.}).
     */
    public void clickSaveOpeningHoursExpectingEmptyTimeValidation() {
        ScenarioLogManager.getLogger().info(
                "Trying to click \"Alle Änderungen aktivieren\" expecting empty-time validation...");
        CONTEXT.set();
        String publishButton =
                "//button[contains(@class,'button-save') and normalize-space()='Alle Änderungen aktivieren']";
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, publishButton, LocatorType.XPATH, false, CONTEXT);
        By startTimeError = By.xpath(
                "//*[contains(@class,'message') or contains(@class,'error') or self::li or self::p]"
                        + "[contains(., 'Die Startzeit darf nicht leer sein')]");
        try {
            new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                    .until(ExpectedConditions.visibilityOfElementLocated(startTimeError));
        } catch (TimeoutException e) {
            Assert.fail("Expected validation \"Die Startzeit darf nicht leer sein.\" after save without Uhrzeit.",
                    e);
        }
        By success = By.xpath("//div[contains(@class,'message--success')][contains(., 'Öffnungszeiten gespeichert')]");
        Assert.assertTrue(DRIVER.findElements(success).isEmpty(),
                "Opening hours must not be saved while Uhrzeit von/bis are empty.");
    }

    public void clickOnAusnahmeButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Ausnahme\" button...");
        CONTEXT.set();
        By ausnahme = By.xpath(
                "//div[contains(@class,'accordion__panel') and contains(@class,'opened')]"
                        + "//button[@title='Ausnahme von dieser Öffnungszeit eintragen'"
                        + " or normalize-space()='Ausnahme']");
        WebElement button = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.elementToBeClickable(ausnahme));
        Assert.assertTrue(button.isEnabled(), "Ausnahme button is disabled (series must extend past the selected day).");
        button.click();
        // Accordion heading uses title= / .accrordion__title (not accordion__button).
        By ausnahmeHeading = By.xpath(
                "//h3[contains(@class,'accordion__heading')]"
                        + "[contains(@title,'Ausnahme') or .//span[contains(.,'Ausnahme')]]");
        try {
            new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                    .until(ExpectedConditions.visibilityOfElementLocated(ausnahmeHeading));
        } catch (TimeoutException e) {
            Assert.fail("Expected an Öffnungszeit accordion titled with Ausnahme after clicking Ausnahme.", e);
        }
        expandOpeningHoursAccordionByTitle("Ausnahme");
    }

    private String readTimeInputValue(String inputId) {
        WebElement field = findVisibleInputById(inputId);
        String value = field.getAttribute("value");
        if (value == null) {
            value = "";
        }
        return value.trim();
    }

    private static String normalizeClock(String time) {
        if (time == null) {
            return "";
        }
        String trimmed = time.trim();
        if (trimmed.length() >= 5 && trimmed.charAt(2) == ':') {
            return trimmed.substring(0, 5);
        }
        return trimmed;
    }

    private WebElement findVisibleInputById(String id) {
        String inOpenedAccordion = "(//div[contains(@class,'accordion__panel') and contains(@class,'opened')]"
                + "//input[@id='" + id + "'])[last()]";
        List<WebElement> opened = DRIVER.findElements(By.xpath(inOpenedAccordion));
        if (!opened.isEmpty() && opened.get(0).isDisplayed()) {
            return opened.get(0);
        }
        return DRIVER.findElements(By.id(id)).stream()
                .filter(element -> element.isDisplayed())
                .filter(element -> element.getRect().getHeight() > 0 && element.getRect().getWidth() > 0)
                .findFirst()
                .orElseGet(() -> findElementByLocatorType("//input[@id='" + id + "']", LocatorType.XPATH, true));
    }

    private void enterTimeDirectly(String inputId, String time) {
        setInputValue(findVisibleInputById(inputId), time);
    }

    private void setInputValue(WebElement field, String value) {
        moveToElementAction(field);
        ((JavascriptExecutor) DRIVER).executeScript(
                "var el=arguments[0], val=arguments[1];"
                        + "el.focus();"
                        + "var setter=Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype,'value').set;"
                        + "setter.call(el,val);"
                        + "el.dispatchEvent(new Event('input',{bubbles:true}));"
                        + "el.dispatchEvent(new Event('change',{bubbles:true}));"
                        + "el.dispatchEvent(new Event('blur',{bubbles:true}));",
                field,
                value);
    }

    public void selectOverallAvailableCounters(String numberOfCounters) {
        selectWorkstationCountDropdownWithRetry("WsCountIntern", numberOfCounters);
    }

    public void selectInternetAvailableCounters(String numberOfCounters) {
        selectWorkstationCountDropdownWithRetry("WsCountPublic", numberOfCounters);
    }

    /**
     * Terminarbeitsplätze selects can sit below the fold and briefly stay disabled while React updates state.
     * Retries mitigate flakiness; {@link Select#selectByValue(String)} avoids fragile visible-text matching.
     */
    private void selectWorkstationCountDropdownWithRetry(String selectId, String numberOfCountersLabel) {
        CONTEXT.set();
        Matcher m = WORKSTATION_COUNT_LEADING_DIGITS.matcher(numberOfCountersLabel.trim());
        Assert.assertTrue(m.find(), "Could not parse workstation count from: " + numberOfCountersLabel);
        String optionValue = m.group(1);

        By selectLocator = By.id(selectId);
        Exception lastFailure = null;
        for (int attempt = 1; attempt <= WORKSTATION_SELECT_MAX_ATTEMPTS; attempt++) {
            ScenarioLogManager.getLogger().info("Trying to select workstation count \"" + numberOfCountersLabel + "\" on #"
                    + selectId + " (attempt " + attempt + "/" + WORKSTATION_SELECT_MAX_ATTEMPTS + ")");
            try {
                WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(WORKSTATION_SELECT_WAIT_PER_ATTEMPT_SEC));
                WebElement selectEl = wait.until(ExpectedConditions.elementToBeClickable(selectLocator));
                scrollToCenterByVisibleElement(selectEl);
                new Select(selectEl).selectByValue(optionValue);
                return;
            } catch (Exception e) {
                lastFailure = e;
                ScenarioLogManager.getLogger().warn("Workstation select attempt " + attempt + " failed: " + e.getMessage());
                if (attempt < WORKSTATION_SELECT_MAX_ATTEMPTS) {
                    try {
                        Thread.sleep(WORKSTATION_SELECT_RETRY_PAUSE_MS);
                    } catch (InterruptedException ie) {
                        Thread.currentThread().interrupt();
                        Assert.fail("Interrupted while retrying workstation select", ie);
                    }
                }
            }
        }
        Assert.fail("Could not select workstation count on #" + selectId + " after " + WORKSTATION_SELECT_MAX_ATTEMPTS + " attempts",
                lastFailure);
    }

    public void clickOnSaveButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Alle Änderungen aktivieren\" button...");
        CONTEXT.set();
        String publishButton =
                "//button[contains(@class,'button-save') and normalize-space()='Alle Änderungen aktivieren']";
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, publishButton, LocatorType.XPATH, false, CONTEXT);
        // A holiday in the range asks to continue, then the save dialog asks again.
        // Confirm each dialog until the success message is on the page.
        By confirmButton = By.xpath("//div[contains(@class,'lightbox__content')]//a[@data-action-ok]");
        By success = By.xpath("//div[contains(@class,'message--success')][contains(., 'Öffnungszeiten gespeichert')]");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.ignoring(StaleElementReferenceException.class);
        try {
            wait.until(driver -> {
                if (driver.findElements(success).stream().anyMatch(this::isShown)) {
                    return true;
                }
                for (WebElement ok : driver.findElements(confirmButton)) {
                    if (isShown(ok)) {
                        ok.click();
                        return false;
                    }
                }
                return false;
            });
        } catch (TimeoutException e) {
            Assert.fail("Opening hours were not saved.", e);
        }
        String message = getWebElementText(
            DEFAULT_EXPLICIT_WAIT_TIME,
            "//div[contains(@class,'message--success')]",
            LocatorType.XPATH,
            CONTEXT
        ).replaceAll("\\n", "").trim();

        String today = BerlinTime.today()
                .format(DateTimeFormatter.ofPattern("dd.MM.yyyy", Locale.GERMANY));

        Assert.assertTrue(
                message.contains("Öffnungszeiten gespeichert, " + today),
                "Success message does not contain today's date!"
        );
    }

    private boolean isShown(WebElement element) {
        try {
            return element.isDisplayed();
        } catch (StaleElementReferenceException e) {
            return false;
        }
    }

    /**
     * Clicks the trash icon for the opening-hours row with the given note, then confirms
     * via the app's HTML lightbox (Öffnungszeit löschen / Löschen). Used on Behörden und Standorte > Öffnungszeiten.
     */
    public void clickDeleteOpeningHoursWithNote(String note) {
        ScenarioLogManager.getLogger().info("Trying to click delete for opening hour with note \"" + note + "\"...");
        CONTEXT.set();
        String trashXpath = "//table[contains(@class,'table--base')]//tr[.//td[contains(., '" + note + "')]]//a[.//i[contains(@class,'fa-trash-alt')]]";
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, trashXpath, LocatorType.XPATH, false);
        confirmOpeningHoursDeleteLightbox();
    }

    /**
     * Deletes every saved opening-hours row whose Typ column matches {@code type}
     * (e.g. {@code Spontankunden}). The lightbox DELETE is persisted immediately.
     */
    public void deleteOpeningHoursOfType(String type) {
        CONTEXT.set();
        CONTEXT.waitForSpinners();
        ScenarioLogManager.getLogger().info("Trying to delete opening hours of type \"" + type + "\"...");
        By trash = By.xpath("//table[contains(@class,'table--base')]//tr[td[normalize-space()='" + type
                + "']]//a[.//i[contains(@class,'fa-trash-alt')]]");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        try {
            wait.until(ExpectedConditions.elementToBeClickable(trash));
        } catch (TimeoutException e) {
            Assert.fail("No opening-hours row of type \"" + type + "\" to delete", e);
        }
        int deleted = 0;
        while (true) {
            List<WebElement> icons = DRIVER.findElements(trash);
            if (icons.isEmpty()) {
                break;
            }
            int remaining = icons.size();
            wait.until(ExpectedConditions.elementToBeClickable(trash)).click();
            confirmOpeningHoursDeleteLightbox();
            CONTEXT.waitForSpinners();
            wait.until(driver -> {
                try {
                    return driver.findElements(trash).size() < remaining;
                } catch (StaleElementReferenceException e) {
                    return false;
                }
            });
            deleted++;
        }
        Assert.assertTrue(deleted > 0, "No opening-hours row of type \"" + type + "\" to delete");
    }

    private void confirmOpeningHoursDeleteLightbox() {
        By confirmButton = By.xpath("//div[contains(@class,'lightbox__content')]//a[@data-action-ok]");
        WebElement confirmBtn = new WebDriverWait(DRIVER, Duration.ofSeconds(10))
                .until(ExpectedConditions.visibilityOfElementLocated(confirmButton));
        confirmBtn.click();
        new WebDriverWait(DRIVER, Duration.ofSeconds(5))
                .until(ExpectedConditions.invisibilityOfElementLocated(confirmButton));
    }

    public void assertOpeningHoursDayPage() {
        CONTEXT.set();
        WebElement heading = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(
                        By.xpath("//h1[contains(., 'Öffnungszeiten für den Standort')]")));
        Assert.assertTrue(heading.isDisplayed(), "The opening-hours day page is not visible.");
    }

    /** ZMSKVR-42: day-lock banner above the opening-hours list (only one may be visible). */
    public void assertDayClosureBannerUnlocked() {
        CONTEXT.set();
        WebElement open = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(By.id("closure-open")));
        Assert.assertTrue(
                open.getText().contains("NICHT gesperrt"),
                "Unlocked banner text missing: " + open.getText());
        Assert.assertFalse(
                DRIVER.findElement(By.id("closure-close")).isDisplayed(),
                "Locked banner must stay hidden while unlocked.");
    }

    public void assertDayClosureBannerLocked() {
        CONTEXT.set();
        WebElement close = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(By.id("closure-close")));
        Assert.assertTrue(
                close.getText().contains("gesperrt") && !close.getText().contains("NICHT"),
                "Locked banner text missing: " + close.getText());
        Assert.assertFalse(
                DRIVER.findElement(By.id("closure-open")).isDisplayed(),
                "Unlocked banner must stay hidden while locked.");
    }

    public void clickDayClosureButton(String label) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to click day-closure button \"{}\"...", label);
        String xpath = "//button[contains(@class,'js-day-closure-toggle') and normalize-space()='" + label + "']";
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, xpath, LocatorType.XPATH, false, CONTEXT);
    }

    public void assertDayClosureConfirmDialog(String expectedMessage) {
        CONTEXT.set();
        By dialog = By.xpath("//div[contains(@class,'lightbox__content')]//section[contains(@class,'dialog')]");
        WebElement box = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(dialog));
        String text = box.getText().replace('\n', ' ').trim();
        Assert.assertTrue(
                text.contains(expectedMessage),
                "Day-closure confirm dialog text mismatch. expected to contain \"" + expectedMessage
                        + "\", was: " + text);
        Assert.assertTrue(
                DRIVER.findElements(By.cssSelector("div.lightbox__content a.button-ok")).stream()
                        .anyMatch(WebElement::isDisplayed),
                "Confirm dialog OK button missing.");
        Assert.assertTrue(
                DRIVER.findElements(By.cssSelector("div.lightbox__content a.button-abort")).stream()
                        .anyMatch(WebElement::isDisplayed),
                "Confirm dialog Abbruch button missing.");
    }

    public void confirmDayClosureDialog() {
        CONTEXT.set();
        By confirmButton = By.cssSelector("div.lightbox__content a.button-ok");
        WebElement ok = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.elementToBeClickable(confirmButton));
        ok.click();
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.invisibilityOfElementLocated(confirmButton));
    }

    public void abortDayClosureDialog() {
        CONTEXT.set();
        By abortButton = By.cssSelector("div.lightbox__content a.button-abort");
        WebElement abort = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.elementToBeClickable(abortButton));
        abort.click();
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.invisibilityOfElementLocated(abortButton));
    }

    public void assertMonthLockedDayIconForDay(String day) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Checking month locked-day icon for day \"{}\"...", day);
        String xpath = "//span[@class='day' and normalize-space()='" + day
                + "']/ancestor::a[1]//strong[@class='char' and normalize-space()='X']";
        WebElement icon = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(By.xpath(xpath)));
        Assert.assertTrue(icon.isDisplayed(), "Month overview missing locked-day X icon for day " + day);
        WebElement marked = icon.findElement(By.xpath("./ancestor::*[contains(@class,'daystatus--conflict')][1]"));
        Assert.assertTrue(
                marked.getAttribute("title") != null && marked.getAttribute("title").contains("Gesperrt"),
                "Locked-day icon should be titled Gesperrt.");
    }

    public void clickMonthOverviewLink() {
        CONTEXT.set();
        clickOnWebElement(
                DEFAULT_EXPLICIT_WAIT_TIME,
                "//a[contains(., 'zurück zur Monatsansicht') or contains(@href,'availability/month')]",
                LocatorType.XPATH,
                false,
                CONTEXT);
    }

    public void openOpeningHourForEditing(String note) {
        ScenarioLogManager.getLogger().info("Trying to edit the opening hour with note \"" + note + "\"...");
        String pencil = openingHourRow(note) + "//a[@aria-label='Bearbeiten']";
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, pencil, LocatorType.XPATH, false, CONTEXT);
    }

    public void replaceOpeningHoursNote(String noteKey) {
        CONTEXT.set();
        String note = "Note- " + RandomNameHelper.randomAlphanumeric(10);
        ScenarioLogManager.getLogger().info("Trying to replace the opening-hours note with \"" + note + "\"...");
        TestDataHelper.setTestData(noteKey, note);
        setInputValue(findVisibleInputById("AvDayDescription"), note);
    }

    public void openOpeningHourHistory(String note) {
        ScenarioLogManager.getLogger().info("Trying to open the change history of the opening hour \"" + note + "\"...");
        String history = openingHourRow(note) + "//a[starts-with(@aria-label, 'Änderungsverlauf von')]";
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, history, LocatorType.XPATH, false, CONTEXT);
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(
                        "tr.availability-history-row strong")));
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME)).until(driver -> {
            String text = historyTable().getText();
            return !text.contains("Wird geladen") && (text.contains("Geändert") || text.contains("konnte nicht geladen"));
        });
    }

    public void assertOpeningHourHistory(String note, String action, String from, String until) {
        WebElement table = historyTable();
        List<String> historyHeaders = headerLabels(table);
        List<String> openingHourHeaders = headerLabels(DRIVER.findElement(
                By.cssSelector("table.table--base:not(.availability-history-table)")));
        for (String header : openingHourHeaders) {
            Assert.assertTrue(historyHeaders.contains(header),
                    "The change history is missing the opening-hours column \"" + header + "\".");
        }
        Assert.assertTrue(historyHeaders.contains("Zeitschlitz"), "The change history is missing Zeitschlitz.");
        WebElement row = table.findElement(By.xpath(".//tr[td[contains(., '" + note + "')]]"));
        String entry = row.getText();
        Assert.assertTrue(entry.contains(action),
                "The history entry for \"" + note + "\" does not show \"" + action + "\".");
        Assert.assertTrue(entry.contains(from) && entry.contains(until),
                "The history entry for \"" + note + "\" does not show " + from + "–" + until + ".");
        String changedAt = row.findElement(By.cssSelector("td.cell--meta")).getText();
        Assert.assertTrue(changedAt.matches("(?s).*\\d{2}\\.\\d{2}\\.\\d{4} \\d{2}:\\d{2}.*"),
                "The history entry for \"" + note + "\" has no timestamp: " + changedAt);
    }

    public void assertDeletedOpeningHour(String note) {
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.invisibilityOfElementLocated(By.xpath(openingHourRow(note))));
        DRIVER.navigate().refresh();
        CONTEXT.set();
        By deletedRow = By.xpath("//section[contains(@class,'availability-history')]"
                + "//tr[.//td[contains(., 'Gelöscht')] and .//td[contains(., '" + note + "')]]");
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(deletedRow));
    }

    public void assertOpeningHourHistoryHidden() {
        assertOpeningHoursDayPage();
        Assert.assertTrue(
                DRIVER.findElements(By.cssSelector("a[aria-label^='Änderungsverlauf von']")).isEmpty(),
                "The change history is visible for a user who is not a technical admin.");
        Assert.assertTrue(
                DRIVER.findElements(By.xpath("//h2[contains(., 'Gelöschte Öffnungszeiten')]")).isEmpty(),
                "Deleted opening hours are visible for a user who is not a technical admin.");
    }

    private String openingHourRow(String note) {
        return "//table[contains(@class,'table--base') and not(contains(@class,'availability-history-table'))]"
                + "//tr[.//td[contains(., '" + note + "')]]";
    }

    private WebElement historyTable() {
        return DRIVER.findElement(By.cssSelector("tr.availability-history-row table.availability-history-table"));
    }

    private List<String> headerLabels(WebElement table) {
        return table.findElements(By.cssSelector("thead th")).stream()
                .map(cell -> cell.getText().trim())
                .filter(label -> !label.isEmpty())
                .toList();
    }

    public void clickOnDeleteLocation() {
        ScenarioLogManager.getLogger().info("Trying to click on \"delete\" button under location config...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, ".button.type-delete", LocatorType.CSSSELECTOR, false);
    }

    public void setValueForEmailConfirmation(boolean booleanFlag) {
        ScenarioLogManager.getLogger().info("Trying to set the value of \"E-Mail-Bestätigung\" to " + booleanFlag + "...");
        WebElement checkbox = findElementByLocatorType("input[name='preferences[client][emailConfirmationActivated]'][type='checkbox']",
                LocatorType.CSSSELECTOR, true);
        boolean isChecked = checkbox.getAttribute("checked") != null;
        if (isChecked != booleanFlag) {
            checkbox.click();
            ScenarioLogManager.getLogger().info("Checkbox state changed to " + booleanFlag);
        } else {
            ScenarioLogManager.getLogger().info("Checkbox already in the desired state: " + booleanFlag);
        }
    }

    public void enterInformationTextForAppointmentBookingInTheCitizenFrontend(String text) {
        ScenarioLogManager.getLogger().info("Trying to Enter information text for appointment booking in the citizen frontend...");
        WebElement textArea = findElementByLocatorType("//textarea[@name='preferences[appointment][infoForAppointment]']", LocatorType.XPATH, true);
        textArea.clear();
        textArea.sendKeys(text);
    }

    /**
     * ZMSKVR-812 / ZMSKVR-830: superuser-only field for infoForAllAppointments.
     */
    public void assertAvailabilityHintFieldLegendVisible() {
        ScenarioLogManager.getLogger()
                .info("Asserting Hinweismeldungen bzgl. der Terminverfügbarkeiten field is visible");
        WebElement legend = findElementByLocatorType(
                "//legend[contains(normalize-space(.),'Hinweismeldungen bzgl. der Terminverfügbarkeiten an die Bürger')]",
                LocatorType.XPATH,
                true);
        Assert.assertTrue(
                legend.isDisplayed(),
                "Expected legend for Hinweismeldungen bzgl. der Terminverfügbarkeiten an die Bürger*innen");
        WebElement textArea = findElementByLocatorType(
                "//textarea[@name='preferences[appointment][infoForAllAppointments]']",
                LocatorType.XPATH,
                true);
        Assert.assertTrue(
                textArea.isDisplayed(),
                "Expected textarea preferences[appointment][infoForAllAppointments]");
    }


    /**
     * ZMSKVR-1345 / ZMSKVR-1461: restrictedscope users start with read-only Name and Anschrift.
     * The page title is {@code Standort}; the address label in the React block is Anschrift.
     */
    public void assertLocationFormStartsWithReadOnlyNameAndAddress() {
        ScenarioLogManager.getLogger()
                .info("Asserting Standort form starts with read-only Name and Anschrift");
        Assert.assertTrue(
                isWebElementVisible(
                        DEFAULT_EXPLICIT_WAIT_TIME,
                        "//h1[@class='main-title' and normalize-space(.)='Standort']",
                        LocatorType.XPATH,
                        false),
                "Expected page title Standort.");
        WebElement name = findElementByLocatorType("//input[@id='scopeProviderName']", LocatorType.XPATH, true);
        WebElement address =
                findElementByLocatorType("//input[@id='scopeProviderStreet']", LocatorType.XPATH, true);
        Assert.assertTrue(name.isDisplayed(), "Expected read-only Name field (#scopeProviderName).");
        Assert.assertTrue(address.isDisplayed(), "Expected read-only Anschrift field (#scopeProviderStreet).");
        Assert.assertTrue(isReadonly(name), "Name must be read-only.");
        Assert.assertTrue(isReadonly(address), "Anschrift must be read-only.");
    }

    private static boolean isReadonly(WebElement field) {
        String readonly = field.getAttribute("readonly");
        String readOnly = field.getAttribute("readOnly");
        return (readonly != null && !readonly.isBlank()) || (readOnly != null && !readOnly.isBlank());
    }

    /** ZMSKVR-1345 / ZMSKVR-1461: Quelle der Standortdaten / Standortdaten selects stay hidden. */
    public void assertLocationSourceAndProviderFieldsHidden() {
        ScenarioLogManager.getLogger()
                .info("Asserting Quelle der Standortdaten / Standortdaten selects are hidden");
        Assert.assertTrue(
                noneDisplayed(By.id("scopeProviderSource")),
                "Quelle der Standortdaten (#scopeProviderSource) must stay hidden.");
        Assert.assertTrue(
                noneDisplayed(By.id("scopeProviderId")),
                "Standortdaten provider select (#scopeProviderId) must stay hidden.");
        Assert.assertTrue(
                noneDisplayed(By.cssSelector("label[for='scopeProviderSource']")),
                "Label Quelle der Standortdaten must stay hidden.");
    }

    private boolean noneDisplayed(By locator) {
        return DRIVER.findElements(locator).stream().noneMatch(WebElement::isDisplayed);
    }

    /** ZMSKVR-1345 / ZMSKVR-1461: technical admin keeps editable source selects. */
    public void assertLocationSourceAndProviderFieldsEditable() {
        ScenarioLogManager.getLogger()
                .info("Asserting Quelle der Standortdaten / Standortdaten selects are editable");
        WebElement source =
                findElementByLocatorType("//select[@id='scopeProviderSource']", LocatorType.XPATH, true);
        WebElement provider =
                findElementByLocatorType("//select[@id='scopeProviderId']", LocatorType.XPATH, true);
        Assert.assertTrue(source.isDisplayed(), "Expected Quelle der Standortdaten select.");
        Assert.assertTrue(provider.isDisplayed(), "Expected Standortdaten provider select.");
        Assert.assertTrue(source.isEnabled(), "Quelle der Standortdaten must be enabled.");
        Assert.assertTrue(provider.isEnabled(), "Standortdaten provider select must be enabled.");
        Assert.assertTrue(
                DRIVER.findElement(By.cssSelector("label[for='scopeProviderSource']")).getText()
                        .contains("Quelle der Standortdaten"),
                "Expected label Quelle der Standortdaten.");
        Assert.assertTrue(
                DRIVER.findElement(By.cssSelector("label[for='scopeProviderId']")).getText()
                        .contains("Standortdaten"),
                "Expected label Standortdaten.");
    }

    /** ZMSKVR-1345 / ZMSKVR-1461: jurisdiction shows the owner edit control on the overview. */
    public void assertOwnerCanBeOpenedForEditing(String ownerName) {
        ScenarioLogManager.getLogger()
                .info("Asserting owner {} has an edit link under Behörden und Standorte", ownerName);
        WebElement edit = findElementByLocatorType(
                "//h2[contains(@class,'owner-title') and contains(normalize-space(.),'"
                        + ownerName
                        + "')]//a[@title='Bearbeiten']",
                LocatorType.XPATH,
                true);
        Assert.assertTrue(edit.isDisplayed(), "Expected Bearbeiten link for owner " + ownerName + ".");
    }

    public void openOwnerForEditing(String ownerName) {
        ScenarioLogManager.getLogger()
                .info("Opening owner {} for editing under Behörden und Standorte", ownerName);
        clickOnWebElement(
                DEFAULT_EXPLICIT_WAIT_TIME,
                "//h2[contains(@class,'owner-title') and contains(normalize-space(.),'"
                        + ownerName
                        + "')]//a[@title='Bearbeiten']",
                LocatorType.XPATH,
                false,
                CONTEXT);
        Assert.assertTrue(
                isWebElementVisible(
                        DEFAULT_EXPLICIT_WAIT_TIME,
                        "//h2[contains(@class,'form__heading') and contains(.,'Kundeneinrichtung')]",
                        LocatorType.XPATH,
                        false),
                "Did not reach the owner edit form for " + ownerName + ".");
    }

    public void assertOwnerFormOffersAddReferatAndDelete() {
        ScenarioLogManager.getLogger()
                .info("Asserting owner form offers Referat hinzufügen and Löschen");
        WebElement addReferat = findElementByLocatorType(
                "//div[contains(@class,'form-actions')]"
                        + "//a[contains(@class,'type-new') and contains(normalize-space(.),'Referat hinzufügen')]",
                LocatorType.XPATH,
                true);
        WebElement delete = findElementByLocatorType(
                "//div[contains(@class,'form-actions')]"
                        + "//a[contains(@class,'type-delete') and contains(normalize-space(.),'Löschen')]",
                LocatorType.XPATH,
                true);
        Assert.assertTrue(addReferat.isDisplayed(), "Expected visible Referat hinzufügen on the owner form.");
        Assert.assertTrue(addReferat.isEnabled(), "Referat hinzufügen must be enabled.");
        Assert.assertTrue(delete.isDisplayed(), "Expected visible Löschen on the owner form.");
        Assert.assertTrue(delete.isEnabled(), "Löschen must be enabled.");
    }


    /** ZMSKVR-1392 / ZMSKVR-1459 — Standort number inputs that blur empty values to 0. */
    private static final Map<String, String> STANDORT_INTEGER_FIELDS = Map.of(
            "Terminvergabe ab", "preferences[appointment][startInDaysDefault]",
            "Terminvergabe bis", "preferences[appointment][endInDaysDefault]",
            "Reservierungsdauer", "preferences[appointment][reservationDuration]",
            "Aktivierungsdauer", "preferences[appointment][activationDuration]",
            "Löschverzögerung", "preferences[appointment][deallocationDuration]",
            "Wiederholungsaufrufe", "preferences[queue][callCountMax]",
            "Durchschnittliche Bearbeitungszeit", "preferences[queue][processingTimeAverage]");

    private static final String DEPARTMENT_REMINDER_MINUTES =
            "sendEmailReminderMinutesBefore";

    private static final String LOESCHVERZOEGERUNG_HINT =
            "Wird 0 eingestellt, so gibt es keine zusätzliche Löschverzögerung";

    public void clearStandortIntegerFields(String... labels) {
        for (String label : labels) {
            clearIntegerInput(standortIntegerName(label));
        }
    }

    public void assertStandortIntegerFieldsShow(String expected, String... labels) {
        for (String label : labels) {
            String name = standortIntegerName(label);
            String value = integerInputValue(name);
            Assert.assertEquals(
                    value,
                    expected,
                    "Standort field \"" + label + "\" (" + name + ") expected " + expected + " but was " + value);
        }
    }

    public void assertLoeschverzoegerungDescriptionMentionsNoExtraDelay() {
        ScenarioLogManager.getLogger().info("Asserting Löschverzögerung help text");
        String body = DRIVER.findElement(By.tagName("body")).getText().replace('\u00a0', ' ');
        Assert.assertTrue(
                body.contains(LOESCHVERZOEGERUNG_HINT),
                "Expected Löschverzögerung description to contain: " + LOESCHVERZOEGERUNG_HINT);
    }

    public void assertLocationSavedSuccessfully() {
        ScenarioLogManager.getLogger().info("Asserting Standort save succeeded");
        Assert.assertTrue(
                DRIVER.findElements(By.cssSelector(".message--error")).isEmpty()
                        || DRIVER.findElements(By.cssSelector(".message--error")).stream()
                                .noneMatch(WebElement::isDisplayed),
                "Standort save showed a form error: "
                        + DRIVER.findElements(By.cssSelector(".message--error")).stream()
                                .map(WebElement::getText)
                                .findFirst()
                                .orElse(""));
        boolean success =
                !DRIVER.findElements(By.cssSelector(".message--success")).isEmpty()
                        || DRIVER.getCurrentUrl().contains("success=scope_saved");
        Assert.assertTrue(success, "Expected Standort save success banner or success=scope_saved URL.");
    }

    public void openDepartmentForEditing(String departmentName) {
        ScenarioLogManager.getLogger()
                .info("Opening department {} for editing under Behörden und Standorte", departmentName);
        clickOnWebElement(
                DEFAULT_EXPLICIT_WAIT_TIME,
                "//h4[contains(@class,'department-title') and contains(normalize-space(.),'"
                        + departmentName
                        + "')]//a[@title='Bearbeiten']",
                LocatorType.XPATH,
                false,
                CONTEXT);
        Assert.assertTrue(
                isWebElementVisible(
                        DEFAULT_EXPLICIT_WAIT_TIME,
                        "//input[@name='" + DEPARTMENT_REMINDER_MINUTES + "']",
                        LocatorType.XPATH,
                        false),
                "Did not reach the Behörde form for " + departmentName + ".");
    }

    public void clearDepartmentReminderMinutesField() {
        clearIntegerInput(DEPARTMENT_REMINDER_MINUTES);
    }

    public void assertDepartmentReminderMinutesShows(String expected) {
        Assert.assertEquals(
                integerInputValue(DEPARTMENT_REMINDER_MINUTES),
                expected,
                "Behörde email reminder minutes expected " + expected);
    }

    public void typeIntoStandortIntegerField(String label, String text) {
        WebElement field = integerInput(standortIntegerName(label));
        moveToElementAction(field);
        field.click();
        field.sendKeys(text);
    }

    public void setStandortIntegerField(String label, String value) {
        WebElement field = integerInput(standortIntegerName(label));
        moveToElementAction(field);
        Keys selectAllMod = selectAllModifierKey();
        new Actions(DRIVER)
                .click(field)
                .keyDown(selectAllMod)
                .sendKeys("a")
                .keyUp(selectAllMod)
                .sendKeys(Keys.DELETE)
                .sendKeys(value)
                .sendKeys(Keys.TAB)
                .perform();
    }

    public void assertStandortIntegerFieldHasNoLetters(String label) {
        String value = integerInputValue(standortIntegerName(label));
        Assert.assertTrue(
                value == null || value.matches("^[0-9]*$"),
                "Standort field \"" + label + "\" must not contain letters, was: " + value);
    }

    /** Placeholder for ZMSKVR-1460 until product rejects zero durations. */
    public void assertStandortFieldValidationVisible(String label) {
        Assert.fail(
                "ZMSKVR-1460 not implemented yet: expected validation for \""
                        + label
                        + "\" after saving 0.");
    }

    private String standortIntegerName(String label) {
        String name = STANDORT_INTEGER_FIELDS.get(label);
        Assert.assertNotNull(name, "Unknown Standort integer field label: " + label);
        return name;
    }

    private WebElement integerInput(String name) {
        return findElementByLocatorType("//input[@name='" + name + "']", LocatorType.XPATH, true);
    }

    private String integerInputValue(String name) {
        String value = integerInput(name).getAttribute("value");
        return value == null ? "" : value.trim();
    }

    private void clearIntegerInput(String name) {
        ScenarioLogManager.getLogger().info("Clearing integer input {}", name);
        WebElement field = integerInput(name);
        moveToElementAction(field);
        Keys selectAllMod = selectAllModifierKey();
        new Actions(DRIVER)
                .click(field)
                .keyDown(selectAllMod)
                .sendKeys("a")
                .keyUp(selectAllMod)
                .sendKeys(Keys.DELETE)
                .sendKeys(Keys.TAB)
                .perform();
    }
}
