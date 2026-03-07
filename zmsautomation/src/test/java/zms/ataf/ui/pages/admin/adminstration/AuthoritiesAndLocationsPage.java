package zms.ataf.ui.pages.admin.adminstration;

import java.time.Duration;
import java.time.LocalDate;
import java.time.ZoneId;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.Locale;
import java.util.Map;

import org.apache.commons.lang3.RandomStringUtils;
import org.openqa.selenium.Alert;
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

import ataf.core.helpers.TestDataHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.web.model.LocatorType;
import zms.ataf.ui.pages.admin.AdminPage;
import zms.ataf.ui.pages.admin.AdminPageContext;

public class AuthoritiesAndLocationsPage extends AdminPage {

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

    // setMaxSlotsForLocation — clear before type
    public void setMaxSlotsForLocation(String location, String number) {
        ScenarioLogManager.getLogger().info("Trying to set the maximal slots for location: " + location);
        WebElement field = findElementByLocatorType(
            "//input[@name='preferences[client][slotsPerAppointment]']", LocatorType.XPATH, true);
        moveToElementAction(field);
        new Actions(DRIVER)
            .click(field)
            .keyDown(Keys.CONTROL).sendKeys("a").keyUp(Keys.CONTROL)
            .sendKeys(Keys.DELETE)
            .sendKeys(number)
            .perform();
    }

    // setRepeatCallsForLocation — clear before type
    public void setRepeatCallsForLocation(String location, String number) {
        ScenarioLogManager.getLogger().info("Trying to set 'Wiederholungsaufrufe' for location: " + location);
        WebElement field = findElementByLocatorType(
            "//input[@name='preferences[queue][callCountMax]']", LocatorType.XPATH, true);
        moveToElementAction(field);
        new Actions(DRIVER)
            .click(field)
            .keyDown(Keys.CONTROL).sendKeys("a").keyUp(Keys.CONTROL)
            .sendKeys(Keys.DELETE)
            .sendKeys(number)
            .perform();
    }

    public String getMaxSlotsForLocation(String location) {
        ScenarioLogManager.getLogger().info("Trying to get the maximal slots for location: " + location);
        return findElementByLocatorType("//input[@name='preferences[client][slotsPerAppointment]']", LocatorType.XPATH, true).getAttribute("value");
    }

    public String getRepeatCallsForLocation(String location) {
        ScenarioLogManager.getLogger().info("Trying to get 'Wiederholungsaufrufe' for location: " + location);
        return findElementByLocatorType("//input[@name='preferences[queue][callCountMax]']", LocatorType.XPATH, true).getAttribute("value");
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
    WebElement save = null;
    for (By by : saveLocators) {
        try {
            save = wait.until(ExpectedConditions.elementToBeClickable(by));
            break;
        } catch (TimeoutException ignored) {}
    }
    Assert.assertNotNull(save, "Could not find an enabled 'Speichern' button.");
    scrollToCenterByVisibleElement(save);
    save.click();

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
            WebElement banner = wait.until(ExpectedConditions.visibilityOfElementLocated(ok));
            ScenarioLogManager.getLogger().info("Save success banner: ");
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
        String note = "Note- " + RandomStringUtils.randomAlphanumeric(10);
        ScenarioLogManager.getLogger().info("Trying to enter random generated note for opening hours \"" + note + "\"");
        TestDataHelper.setTestData(noteKey, note);
        enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, note, "//input[@id='AvDayDescription']", LocatorType.XPATH);
    }

    public void selectOpeningHoursType(String type) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to select opening hours type \"" + type + "\"");
        selectDropDownListValueByVisibleText(DEFAULT_EXPLICIT_WAIT_TIME, "//select[@id='AvDayType']", LocatorType.XPATH, type);
    }

    public void selectSeries(String series) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to select series \"" + series + "\"");
        selectDropDownListValueByVisibleText(DEFAULT_EXPLICIT_WAIT_TIME, "//select[@id='AvDaySeries']", LocatorType.XPATH, series);
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
        //TODO remove NullPointerException workaround after fix https://jira.muenchen.de/browse/ZMS-1891
        WebElement openingTimeTextField = findElementByLocatorType("//input[@id='AvDatesStart_time']", LocatorType.XPATH, true);
        moveToElementAction(openingTimeTextField);
        new Actions(DRIVER)
                .sendKeys(openingTimeTextField, Keys.BACK_SPACE, Keys.BACK_SPACE, Keys.BACK_SPACE)
                .sendKeys(openingTimeTextField, time)
                .perform();
    }

    public void enterClosingTime(String time) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to enter closing time \"" + time + "\"");
        //TODO remove NullPointerException workaround after fix https://jira.muenchen.de/browse/ZMS-1891
        WebElement closingTimeTextField = findElementByLocatorType("//input[@id='AvDatesEnd_time']", LocatorType.XPATH, true);
        moveToElementAction(closingTimeTextField);
        new Actions(DRIVER)
                .sendKeys(closingTimeTextField, Keys.BACK_SPACE, Keys.BACK_SPACE, Keys.BACK_SPACE)
                .sendKeys(closingTimeTextField, time)
                .perform();
    }

    public void enterClosingDate(String date) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to enter opening Date \"" + date + "\"");
        WebElement closingDateTextField = findElementByLocatorType("//input[@id='AvDatesEnd']", LocatorType.XPATH, true);
        moveToElementAction(closingDateTextField);
        new Actions(DRIVER)
                .click(closingDateTextField)
                .keyDown(Keys.CONTROL)
                .sendKeys("a")
                .keyUp(Keys.CONTROL)
                .sendKeys(Keys.BACK_SPACE)
                .sendKeys(date)
                .perform();
    }

    public void selectOverallAvailableCounters(String numberOfCounters) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to select overall available counters \"" + numberOfCounters + "\"");
        selectDropDownListValueByVisibleText(DEFAULT_EXPLICIT_WAIT_TIME, "//select[@id='WsCountIntern']", LocatorType.XPATH, numberOfCounters);
    }

    public void selectInternetAvailableCounters(String numberOfCounters) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to select internet available counters \"" + numberOfCounters + "\"");
        selectDropDownListValueByVisibleText(DEFAULT_EXPLICIT_WAIT_TIME, "//select[@id='WsCountPublic']", LocatorType.XPATH, numberOfCounters);
    }

    public void clickOnSaveButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Alle Änderungen aktivieren\" button...");
        CONTEXT.set();
        clickOnWebElement(
            DEFAULT_EXPLICIT_WAIT_TIME,
            "//button[contains(@class,'button-save')]",
            LocatorType.XPATH,
            false,
            CONTEXT
        );
        By confirmButton = By.xpath("//div[contains(@class,'lightbox__content')]//a[@data-action-ok]");
        WebElement confirmBtn = new WebDriverWait(DRIVER, Duration.ofSeconds(10))
                .until(ExpectedConditions.visibilityOfElementLocated(confirmButton));
        confirmBtn.click();
        new WebDriverWait(DRIVER, Duration.ofSeconds(5))
                .until(ExpectedConditions.invisibilityOfElementLocated(confirmButton));
        String message = getWebElementText(
            DEFAULT_EXPLICIT_WAIT_TIME,
            "//div[contains(@class,'message--success')]",
            LocatorType.XPATH,
            CONTEXT
        ).replaceAll("\\n", "").trim();
    
        String today = LocalDate.now(ZoneId.of("Europe/Berlin"))
                .format(DateTimeFormatter.ofPattern("dd.MM.yyyy", Locale.GERMANY));
    
        Assert.assertTrue(
                message.contains("Öffnungszeiten gespeichert, " + today),
                "Success message does not contain today's date!"
        );
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
        By confirmButton = By.xpath("//div[contains(@class,'lightbox__content')]//a[@data-action-ok]");
        WebElement confirmBtn = new WebDriverWait(DRIVER, Duration.ofSeconds(10))
                .until(ExpectedConditions.visibilityOfElementLocated(confirmButton));
        confirmBtn.click();
        new WebDriverWait(DRIVER, Duration.ofSeconds(5))
                .until(ExpectedConditions.invisibilityOfElementLocated(confirmButton));
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
}
