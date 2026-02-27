package zms.ataf.ui.pages.admin.adminstration;

import java.time.Duration;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.LocalTime;
import java.time.ZoneId;
import java.time.format.DateTimeFormatter;
import java.util.Locale;
import java.util.Map;

import org.apache.commons.lang3.RandomStringUtils;
import org.openqa.selenium.Alert;
import org.openqa.selenium.By;
import org.openqa.selenium.Keys;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.interactions.Actions;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.openqa.selenium.support.ui.ExpectedConditions;
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
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//a[contains(normalize-space(.), 'Behörden und Standorte')]", LocatorType.XPATH, false, CONTEXT);
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//h1[@class='main-title' and text()='Behörden und Standorte']", LocatorType.XPATH, false),
                "Page title 'Behörden und Standorte' is not visible!");
    }

    public void clickOnLocationEntry(String location) {
        ScenarioLogManager.getLogger().info("Trying to click on '" + location + "' under Authorities and locations...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//li/a[contains(text(), '" + location + "')]", LocatorType.XPATH, false, CONTEXT);
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//h1[@class='main-title' and text()='Standort']", LocatorType.XPATH, false),
                "Page title 'Behörden und Standorte' is not visible!");
    }

    public void setMaxSlotsForLocation(String location, String number) {
        ScenarioLogManager.getLogger().info("Trying to set the maximal slots for location: " + location);
        enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, number, "//input[@name='preferences[client][slotsPerAppointment]']", LocatorType.XPATH);
    }

    public void setRepeatCallsForLocation(String location, String number) {
        ScenarioLogManager.getLogger().info("Trying to 'Wiederholungsaufrufe' for location: " + location);
        enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, number, "//input[@name='preferences[queue][callCountMax]']", LocatorType.XPATH);
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
        ScenarioLogManager.getLogger().info("Trying click the button 'Speichern' to save location changes...");
    
        // Click the "Speichern" button via XPATH (no CSS enum needed)
        String saveBtnXpath = "//button[`@name`='save' and contains(`@class`,'button--positive') and contains(`@class`,'type-save')]";
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, saveBtnXpath, LocatorType.XPATH, false);
    
        // Wait for the success message (valid XPath — no backticks)
        By successHeading = By.xpath("//section[contains(`@class`,'message--success')]//h2[contains(normalize-space(.),'Speichern erfolgreich')]");
        try {
            new WebDriverWait(DRIVER, Duration.ofSeconds(10))
                .until(ExpectedConditions.visibilityOfElementLocated(successHeading));
        } catch (org.openqa.selenium.TimeoutException e) {
            Assert.fail("Save message is not visible!");
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
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[text()='neue Öffnungszeit']", LocatorType.XPATH, false, CONTEXT);
        // get the last 'Öffnungszeit' beacue its the new one
        final String OPENING_HOURS_BUTTON_LOCATOR_XPATH = "(//h3[@class='accordion__heading']/button)[last()]";
        WebElement openingHoursButton = waitForElementByXpath(DEFAULT_EXPLICIT_WAIT_TIME, OPENING_HOURS_BUTTON_LOCATOR_XPATH, true, true);
        if (openingHoursButton.getAttribute("aria-expanded").equals("false")) {
            clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, openingHoursButton, false);
        }
        Assert.assertNotEquals(openingHoursButton.getAttribute("aria-expanded"), "false",
                "Click on \"neue Öffnungszeit\" button has failed! Opening hours form did not open!");
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
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[@type='save']", LocatorType.XPATH, false, CONTEXT);
        Alert alert = waitForAlertIsPresent(DEFAULT_EXPLICIT_WAIT_TIME);
        alert.accept();
        LocalDateTime saveDateTime = LocalDateTime.now(ZoneId.of("Europe/Berlin"));
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//div[@class='message message--success']", LocatorType.XPATH, false),
                "Click on \"Alle Änderungen aktivieren\" button has failed! Success message isn't visible!");
        Assert.assertEquals(
                getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "//div[@class='message message--success']", LocatorType.XPATH, CONTEXT).replaceAll("\\n", "")
                        .trim(),
                "Öffnungszeiten gespeichert, " + saveDateTime.format(DateTimeFormatter.ofPattern("dd.MM.yyyy", Locale.GERMANY)) + " um " + saveDateTime.format(
                        DateTimeFormatter.ofPattern("HH:mm", Locale.GERMANY)) + " Uhr",
                "Click on \"Alle Änderungen aktivieren\" button has failed! Success message text does not match expected value!");
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
