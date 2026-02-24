package zms.ataf.ui.pages.admin.workview.counterprocessingstation;

import java.security.SecureRandom;
import java.time.Duration;
import java.time.LocalDate;
import java.time.LocalTime;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.Locale;
import java.util.concurrent.atomic.AtomicReference;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.openqa.selenium.Alert;
import org.openqa.selenium.By;
import org.openqa.selenium.ElementClickInterceptedException;
import org.openqa.selenium.Keys;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.interactions.Actions;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedCondition;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.helpers.TestDataHelper;
import ataf.core.helpers.TestPropertiesHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.core.properties.DefaultValues;
import ataf.web.model.LocatorType;
import zms.ataf.ui.pages.admin.AdminPage;
import zms.ataf.ui.pages.admin.AdminPageContext;
import zms.ataf.ui.pages.buergeransicht.BuergeransichtPageContext;

public class CounterProcessingStationPage extends AdminPage {
    private final String FINISH_BUTTON_LOCATOR_XPATH = "//a[contains(@class,'button-finish')]";
    private final String APPOINTMENT_QUEUE_TABLE_LOCATOR_ID = "table-queued-appointments";
    private final String APPOINTMENT_PARKED_TABLE_LOCATOR_ID = "table-parked-appointments";
    private final String APPOINTMENT_MISSED_TABLE_LOCATOR_ID = "table-missed-appointments";
    private final String APPOINTMENT_FINISHED_TABLE_LOCATOR_ID = "table-finished-appointments";
    private final String APPOINTMENT_TIME_LOCATOR_ID = "process_time";

    public CounterProcessingStationPage(RemoteWebDriver driver, AdminPageContext adminPageContext) {
        super(driver, adminPageContext);
    }

    public void clickOnWeeklyCalendarLink() {
        ScenarioLogManager.getLogger().info("Trying to click on \"weekly calendar\" link...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//a[text()='Wochenkalender']", LocatorType.XPATH, false, CONTEXT);
    }

    public void checkIfWeeklyCalendarIsVisible() {
        ScenarioLogManager.getLogger().info("Checking if \"weekly calendar\" is visible...");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//h1[@class='main-title']", LocatorType.XPATH, false, CONTEXT),
                "Main title is not visible!");
        Assert.assertEquals(getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "//h1[@class='main-title']", LocatorType.XPATH, CONTEXT), "Wochenkalender",
                "Main title does not match expected text!");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//section[contains(@class,'calendar-weektable')]", LocatorType.XPATH, false, CONTEXT),
                "Weekly calendar table is not visible!");
    }

    public void checkIfAllBookedAndFreeSlotsAreVisible() {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Checking if all booked and free slots are visible...");
        Assert.assertFalse(
                findElementsByLocatorType(TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                        "//td/div[contains(@class,'timeslot')]", LocatorType.XPATH).isEmpty());
    }

    public void clickOnWorkstationLink() {
        //TODO: gehört es nicht in der adminPage ?
        ScenarioLogManager.getLogger().info("Trying to click on \"workstation\" link...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//a[@href='/terminvereinbarung/admin/workstation/']", LocatorType.XPATH, false, CONTEXT);
    }

    public void clickOnAppointmentNumberLink(String appointmentNumber) {
        ScenarioLogManager.getLogger().info("Trying to click on \"appointment number\" link...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//a[@data-process='" + appointmentNumber + "' and contains(text(),'" + appointmentNumber + "')]",
                LocatorType.XPATH, false, CONTEXT);
    }

    public void clickOnCustomerAppearedButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"customer appeared\" button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[contains(@class,'client-called_button-success')]", LocatorType.XPATH, false, CONTEXT);
    }

    public void clickOnNoCallNextCustomerButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Nein, nächster Kunde bitte\" button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[contains(text(), 'Nein, nächster Kunde bitte')]", LocatorType.XPATH, false, CONTEXT);
    }

    public void clickOnCustomerDidNotAppearButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"customer didn't appear\" button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[contains(@class,'client-called_button-abort left')]", LocatorType.XPATH, false, CONTEXT);
    }

    public void checkCustomerInformation() {
        ScenarioLogManager.getLogger().info("Checking if customer name and appointment number match...");
        String customerName = getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "//following-sibling::dt[contains(text(),'Name')]/following-sibling::dd[1]",
                LocatorType.XPATH, CONTEXT);
        Assert.assertTrue(customerName.contains(TestDataHelper.getTestData("customer_name")),
                "Customer name is not correct! Expected: [" + TestDataHelper.getTestData("customer_name") + "] but found [" + customerName.replaceAll(
                        "[\\r\\n]", "") + "]");
        Assert.assertTrue(customerName.contains(TestDataHelper.getTestData("appointment_number")),
                "Appointment number is not correct! Expected: " + TestDataHelper.getTestData("appointment_number") + "] but found [" + customerName.replaceAll(
                        "[\\r\\n]", "") + "]");

        ScenarioLogManager.getLogger().info("Checking if service match...");
        Assert.assertEquals(getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "//following-sibling::dt[contains(text(),'Anliegen')]/following-sibling::dd[1]/ul/li",
                LocatorType.XPATH, CONTEXT), TestDataHelper.getTestData("service"), "Service does not match expected value!");

        if (TestDataHelper.getTestData("customer_email") != null) {
            ScenarioLogManager.getLogger().info("Checking if email match...");
            Assert.assertEquals(getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "//following-sibling::dt[contains(text(),'E-Mail')]/following-sibling::dd[1]",
                    LocatorType.XPATH, CONTEXT).trim(), TestDataHelper.getTestData("customer_email"), "Email does not match expected value!");
        }

        if (TestDataHelper.getTestData("customer_phone_number") != null) {
            ScenarioLogManager.getLogger().info("Checking if telephone number match...");
            Assert.assertEquals(getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "//following-sibling::dt[contains(text(),'Telefon')]/following-sibling::dd[1]",
                    LocatorType.XPATH, CONTEXT).trim(), TestDataHelper.getTestData("customer_phone_number"), "Telephone number does not match expected value!");
        }

        if (TestDataHelper.getTestData("custom_field_name") != null && TestDataHelper.getTestData("custom_field_text") != null) {
            ScenarioLogManager.getLogger().info("Checking if custom field text match...");
            Assert.assertEquals(getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME,
                            "//following-sibling::dt[contains(text(),'" + TestDataHelper.getTestData("custom_field_name") + "')]/following-sibling::dd[1]",
                            LocatorType.XPATH, CONTEXT).trim(), TestDataHelper.getTestData("custom_field_text"),
                    TestDataHelper.getTestData("custom_field_name") + " does not match expected value!");
        }

        ScenarioLogManager.getLogger().info("Checking if finish button is visible...");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, FINISH_BUTTON_LOCATOR_XPATH, LocatorType.XPATH, true, CONTEXT),
                "Finish button ist not visible!");

        ScenarioLogManager.getLogger().info("Checking if cancel button is visible...");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//button[contains(@class,'button-cancel')]", LocatorType.XPATH, true, CONTEXT),
                "Cancel button ist not visible!");
    }

    public void showSpontaneousCustomers(boolean shouldSelect) {
        WebElement spontaneousCustomersCheckbox = findElementByLocatorType("//input[@name='appointmentsOnly' and @type!='hidden']", LocatorType.XPATH, true);
        if (shouldSelect && !spontaneousCustomersCheckbox.isSelected()) {
            ScenarioLogManager.getLogger().info("Trying to click on \"Show spontaneous customers\" button...");
            selectWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//input[@name='appointmentsOnly' and @type!='hidden']", LocatorType.XPATH);
        } else if (!shouldSelect && spontaneousCustomersCheckbox.isSelected()) {
            ScenarioLogManager.getLogger().info("Trying to deselect \"Show spontaneous customers\" button...");
            selectWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//input[@name='appointmentsOnly' and @type!='hidden']", LocatorType.XPATH);
        }
    }

    public void isCustomerVisibleInQueue(String transactionNumber, boolean isSpontaneousCustomer) {
        ScenarioLogManager.getLogger().info("Checking for " + (isSpontaneousCustomer ?
                "spontaneous " :
                "") + "customer with Transaction number: (" + transactionNumber + ") to be visible in waiting list...");
        CONTEXT.waitForSpinners();
        scrollToCenterByVisibleElement(findElementByLocatorType(APPOINTMENT_QUEUE_TABLE_LOCATOR_ID, LocatorType.ID, false));
        showSpontaneousCustomers(isSpontaneousCustomer);
        // Use the transaction number to verify its presence in the 'Nr.' column
        checkForValuesInQueueColumn("Nr.", transactionNumber);
    }

    public void isCustomerVisibleInParkingTable(String transactionNumber, boolean isSpontaneousCustomer) {
        ScenarioLogManager.getLogger().info("Checking for " + (isSpontaneousCustomer ?
                "spontaneous " :
                "") + "customer with Transaction number: (" + transactionNumber + ") to be visible in parking list...");
        CONTEXT.waitForSpinners();
        scrollToCenterByVisibleElement(findElementByLocatorType(APPOINTMENT_PARKED_TABLE_LOCATOR_ID, LocatorType.ID, false));
        showSpontaneousCustomers(isSpontaneousCustomer);
        // Use the transaction number to verify its presence in the 'Nr.' column
        checkForValuesInParkingTableColumn("Nr.", transactionNumber);
    }

    public void isCustomerVisibleInFinishedTable(String customer) {
        ScenarioLogManager.getLogger().info("Checking for customer(" + customer + ") to be visible under finished appointments...");
        showTheFinishedAppointmentTable();
        CONTEXT.waitForSpinners();
        scrollToCenterByVisibleElement(findElementByLocatorType(APPOINTMENT_FINISHED_TABLE_LOCATOR_ID, LocatorType.ID, false));
        // Use the transaction number to verify its presence in the 'Name' column
        checkForValuesInFinishedTableColumn("Name", customer);
    }

    public void isCustomerVisibleInMissedTable(String transactionNumber, boolean isSpontaneousCustomer) {
        ScenarioLogManager.getLogger().info("Checking for " + (isSpontaneousCustomer ?
                "spontaneous " :
                "") + "customer with Transaction number: (" + transactionNumber + ") to be visible in parking list...");
        CONTEXT.waitForSpinners();
        scrollToCenterByVisibleElement(findElementByLocatorType(APPOINTMENT_MISSED_TABLE_LOCATOR_ID, LocatorType.ID, false));
        showSpontaneousCustomers(isSpontaneousCustomer);
        // Use the transaction number to verify its presence in the 'Nr.' column
        checkForValuesInMissedTableColumn("Nr.", transactionNumber);
    }

    public void clickOnFinishButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"finish\" button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, FINISH_BUTTON_LOCATOR_XPATH, LocatorType.XPATH, false, CONTEXT);
    }

    public void clickOnAppointmentNumberEditLink(String appointmentNumber) {
        ScenarioLogManager.getLogger().info("Trying to click on \"appointment number edit\" link...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//a[@data-id='" + appointmentNumber + "' and contains(@class,'process-edit')]", LocatorType.XPATH, false,
                CONTEXT);
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.withMessage("Appointment date does not match expected value!");
        wait.until(ExpectedConditions.textToBePresentInElementValue(By.id("process_date"),
                TestDataHelper.getTestData("day") + "." + TestDataHelper.getTestData("month") + "." + TestDataHelper.getTestData("year")));
        wait.withMessage("Customer name does not match expected value!");
        wait.until(ExpectedConditions.textToBePresentInElementValue(By.xpath("//input[@name='familyName']"), TestDataHelper.getTestData("customer_name")));
        if (TestDataHelper.getTestData("customer_phone_number") != null) {
            wait.withMessage("Customer telephone number does not match expected value!");
            wait.until(ExpectedConditions.textToBePresentInElementValue(By.xpath("//input[@name='telephone']"),
                    TestDataHelper.getTestData("customer_phone_number")));
        }
        if (TestDataHelper.getTestData("customer_email") != null) {
            wait.withMessage("Customer email address does not match expected value!");
            wait.until(ExpectedConditions.textToBePresentInElementValue(By.xpath("//input[@name='email']"), TestDataHelper.getTestData("customer_email")));
        }
    }

    public void selectTimeSlot(String timeSlot) {
        WebElement processTimeDropDownList = findElementByLocatorType(APPOINTMENT_TIME_LOCATOR_ID, LocatorType.ID, true);
        if (timeSlot.equals("<nächste>")) {
            List<WebElement> timeSlotOptions = new Select(processTimeDropDownList).getOptions();
            for (WebElement timeSlotOption : timeSlotOptions) {
                if (!timeSlotOption.isSelected() && !timeSlotOption.getText().equals("Spontankunde")) {
                    timeSlot = timeSlotOption.getAttribute("value");
                }
            }
        }
        ScenarioLogManager.getLogger().info("Trying to select time slot \"" + timeSlot + "\"...");
        selectDropDownListValueByValue(processTimeDropDownList, timeSlot);
        Assert.assertEquals(processTimeDropDownList.getAttribute("value"), timeSlot);
        TestDataHelper.setTestData("time", timeSlot.replaceFirst("-", ":"));
    }

    public void clickOnChangeAppointmentButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"change appointment\" button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[contains(@class,'button-submit process-change')]", LocatorType.XPATH, false, CONTEXT);
        WebElement messageTitleElement = findElementByLocatorType("h2.message__heading.title", LocatorType.CSSSELECTOR, false);
        Assert.assertEquals(messageTitleElement.getText(), "Vorgang wurde geändert.",
                "Click on \"change appointment\" button has failed! Message title does not match expected text!");
        Assert.assertEquals(
                getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "div.message__body", LocatorType.CSSSELECTOR, CONTEXT).trim().replaceAll("[\n\t]", ""),
                "Die Terminzeit des Vorgangs mit der Nummer " + TestDataHelper.getTestData("appointment_number") + " wurde erfolgreich geändert.OK",
                "Click on \"change appointment\" button has failed! Message text does not match expected text!");
        ScenarioLogManager.getLogger().info("Trying to click on \"ok\" button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "button.button-ok", LocatorType.CSSSELECTOR, false);
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.withMessage("Click on \"change appointment\" button has failed! Appointment time slot was not updated!");
        wait.until(ExpectedConditions.textToBePresentInElementLocated(
                By.xpath("//a[@data-process='" + TestDataHelper.getTestData("appointment_number") + "']/../../td[2]"), TestDataHelper.getTestData("time")));
        BuergeransichtPageContext.incrementAppointmentCount();
        BuergeransichtPageContext.incrementAppointmentCanceledCount();
    }

    public void clickOnDeleteAppointmentLink(String appointmentNumber) {
        ScenarioLogManager.getLogger().info("Trying to click on \"appointment number delete\" link...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//a[@data-id='" + appointmentNumber + "' and contains(@class,'process-delete')]", LocatorType.XPATH,
                false, CONTEXT);
        WebElement messageTitleElement = findElementByLocatorType("section.board.dialog > div > h2.board__heading", LocatorType.CSSSELECTOR, false);
        Assert.assertEquals(messageTitleElement.getText().trim(), "Eintrag löschen",
                "Click on \"delete appointment\" link has failed! Message title does not match expected text!");
        Assert.assertEquals(getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "section.board.dialog > div > p", LocatorType.CSSSELECTOR, CONTEXT).trim()
                        .replaceAll("[\n\t]", ""),
                "Wenn Sie den Vorgang mit der Nummer " + TestDataHelper.getTestData("appointment_number") + " (" + TestDataHelper.getTestData(
                        "customer_name") + ") löschen wollen, klicken Sie auf \"Eintrag löschen\".(Der Kunde wird darüber per E-Mail informiert.)",
                "Click on \"delete appointment\" link has failed! Message text does not match expected text!");
        ScenarioLogManager.getLogger().info("Trying to click on \"delete\" button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "a.button.button--destructive.button-ok", LocatorType.CSSSELECTOR, false, CONTEXT);
        messageTitleElement = findElementByLocatorType("h2.message__heading.title", LocatorType.CSSSELECTOR, false);
        Assert.assertEquals(messageTitleElement.getText(), "Vorgang gelöscht",
                "Click on \"delete appointment\" link has failed! Message title does not match expected text!");
        Assert.assertEquals(getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "div.message__body", LocatorType.CSSSELECTOR).trim().replaceAll("[\n\t]", ""),
                "Der Vorgang mit der Nummer " + TestDataHelper.getTestData("appointment_number") + " wurde erfolgreich entfernt.OK",
                "Click on \"delete appointment\" link has failed! Message text does not match expected text!");
        ScenarioLogManager.getLogger().info("Trying to click on \"ok\" button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "button.button-ok", LocatorType.CSSSELECTOR, false);
        Assert.assertTrue(isWebElementInvisible(DEFAULT_EXPLICIT_WAIT_TIME,
                        "//a[@data-process='" + appointmentNumber + "' and contains(text(),'" + appointmentNumber + "')]", LocatorType.XPATH, CONTEXT),
                "Click on \"delete appointment\" link has failed! Appointment with number \"" + appointmentNumber + "\" is still visible!");
        BuergeransichtPageContext.incrementAppointmentCanceledCount();
    }

    public void enterDateInNewAppointmentTextField(String date) {
        ScenarioLogManager.getLogger().info("Trying to enter date \"" + date + "\" in new appointment text field...");

        // Check if date has opening hours
        LocalDate dateDesired = LocalDate.parse(date, DateTimeFormatter.ofPattern("dd.MM.yyyy", Locale.GERMAN));
        WebElement calendarElementOfDesiredDate;
        int count = 0;
        do {
            if (count > 0) {
                // While the desired date has no opening hours try next day...
                ScenarioLogManager.getLogger().info("The desired date \"" + date + "\" has no opening hours! Trying next day...");
                dateDesired = dateDesired.plusDays(1L);
                date = dateDesired.format(DateTimeFormatter.ofPattern("dd.MM.yyyy", Locale.GERMAN));
            }
            calendarElementOfDesiredDate = findElementByLocatorType(
                    "//div[@data-date='" + dateDesired.format(DateTimeFormatter.ofPattern("yyyy-MM-dd", Locale.GERMAN)) + "']", LocatorType.XPATH, true);
            count++;
        } while (calendarElementOfDesiredDate.getAttribute("title")
                .contains("an diesem Tag sind keine Termine möglich") && count <= TestPropertiesHelper.getPropertyAsInteger(
                "numberOfRetries", true, 3) * 3);

        //TODO remove NullPointerException workaround after fix https://jira.muenchen.de/browse/ZMS-1891
        WebElement newAppointmentDateTextField = findElementByLocatorType("process_date", LocatorType.ID, true);
        new Actions(DRIVER)
                .sendKeys(newAppointmentDateTextField, Keys.BACK_SPACE, Keys.BACK_SPACE, Keys.BACK_SPACE, Keys.BACK_SPACE, Keys.BACK_SPACE, Keys.BACK_SPACE,
                        Keys.BACK_SPACE, Keys.BACK_SPACE)
                .sendKeys(newAppointmentDateTextField, date)
                .perform();
        CONTEXT.waitForSpinners();
        Assert.assertEquals(findElementByLocatorType("process_date", LocatorType.ID, true).getAttribute("value"), date,
                "Entering date \"" + date + "\" in new appointment text field has failed...");
        TestDataHelper.setTestData("new_appointment_date", date);
    }

    public void selectTimeInNewAppointmentDropDownList(String time) {
        ScenarioLogManager.getLogger().info("Trying to select time \"" + time + "\" in new appointment drop down list...");
        Pattern timeSlotPattern = Pattern.compile("([0-9][0-9]:[0-9][0-9]) \\(noch ([0-9]) frei\\)");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.ignoring(StaleElementReferenceException.class, ElementClickInterceptedException.class);
        wait.pollingEvery(Duration.ofMillis(1000L));
        wait.withMessage("Could not locate any time slot elements in time!");
        try {
            wait.until((ExpectedCondition<Boolean>) waitDriver -> {
                CONTEXT.waitForSpinners();
                WebElement newAppointmentTimeDropDownList = findElementByLocatorType(APPOINTMENT_TIME_LOCATOR_ID, LocatorType.ID, true);
                scrollToCenterByVisibleElement(newAppointmentTimeDropDownList);
                Select newAppointmentTimeDropDownListSelections = new Select(newAppointmentTimeDropDownList);
                List<WebElement> options = newAppointmentTimeDropDownListSelections.getOptions();
                if (!options.isEmpty()) {
                    switch (time) {
                    case "<beliebig>":
                    case "<nächste>":
                        WebElement webElement;
                        if (time.equals("<beliebig>")) {
                            final SecureRandom SECURE_RANDOM = new SecureRandom();
                            webElement = options.get(SECURE_RANDOM.nextInt(options.size() - 1));
                        } else {
                            //webElement = options.get(0);
                            // "Spontankunde" option is skipped when selecting the next available time slo
                            webElement = options.stream()
                                    .filter(option -> !option.getText().contains("Spontankunde"))
                                    .findFirst()
                                    .orElseThrow(() -> new NoSuchElementException("No available time slots"));
                        }
                        Matcher timeSlotMatcher = timeSlotPattern.matcher(webElement.getText());
                        if (timeSlotMatcher.find()) {
                            newAppointmentTimeDropDownListSelections.selectByValue(webElement.getAttribute("value"));
                            ScenarioLogManager.getLogger().info("Time \"" + timeSlotMatcher.group(1) + "\" selected!");
                            TestDataHelper.setTestData("new_appointment_time", timeSlotMatcher.group(1));
                        } else {
                            return false;
                        }
                        break;
                    default:
                        for (WebElement webElementInList : options) {
                            if (webElementInList.getText().contains(time)) {
                                newAppointmentTimeDropDownListSelections.selectByValue(time.replaceFirst(":", "-"));
                                break;
                            }
                        }
                        TestDataHelper.setTestData("new_appointment_time", time);
                    }
                    CONTEXT.waitForSpinners();
                    // shifting focus away from the dropdown
                    clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "h2.board__heading", LocatorType.CSSSELECTOR, false);
                    // on false it will retry
                    if (findElementByLocatorType(APPOINTMENT_TIME_LOCATOR_ID, LocatorType.ID, true).getAttribute("value")
                            .equals(TestDataHelper.getTestData("new_appointment_time").replaceFirst(":", "-"))) {
                        return true;
                    } else {
                        ScenarioLogManager.getLogger().warn("Time not selected! Retrying...");
                        return false;
                    }
                } else {
                    return false;
                }
            });
        } catch (Exception e) {
            Assert.fail("Selecting time \"" + TestDataHelper.getTestData("new_appointment_time") + "\" in new appointment drop down list has failed,", e);
        }
    }

    public void enterNameInNewAppointmentTextField(String name) {
        ScenarioLogManager.getLogger().info("Trying to enter name \"" + name + "\" in new appointment text field...");
        WebElement newAppointmentNameTextField = findElementByLocatorType("familyName", LocatorType.NAME, true);
        enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, name, newAppointmentNameTextField);
        Assert.assertEquals(newAppointmentNameTextField.getAttribute("value"), name,
                "Entering name \"" + name + "\" in new appointment text field has failed...");
        TestDataHelper.setTestData("new_appointment_customer_name", name);
    }

    public void enterPhoneNumberInNewAppointmentTextField(String phoneNumber) {
        ScenarioLogManager.getLogger().info("Trying to enter phone number \"" + phoneNumber + "\" in new appointment text field...");
        WebElement newAppointmentTelephoneTextField = findElementByLocatorType("telephone", LocatorType.NAME, true);
        enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, phoneNumber, newAppointmentTelephoneTextField);
        Assert.assertEquals(newAppointmentTelephoneTextField.getAttribute("value"), phoneNumber,
                "Entering phone number \"" + phoneNumber + "\" in new appointment text field has failed...");
        TestDataHelper.setTestData("new_appointment_customer_phone_number", phoneNumber);
    }

    public void enterEmailInNewAppointmentTextField(String email) {
        ScenarioLogManager.getLogger().info("Trying to enter email \"" + email + "\" in new appointment text field...");
        WebElement newAppointmentEmailTextField = findElementByLocatorType("email", LocatorType.NAME, true);
        enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, email, newAppointmentEmailTextField);
        Assert.assertEquals(newAppointmentEmailTextField.getAttribute("value"), email,
                "Entering email \"" + email + "\" in new appointment text field has failed...");
        TestDataHelper.setTestData("new_appointment_customer_email", email);
    }

    public void enterNoteInNewAppointmentTextField(String note) {
        ScenarioLogManager.getLogger().info("Trying to enter note \"" + note + "\" in new appointment text area...");
        WebElement newAppointmentNoteTextArea = findElementByLocatorType("amendment", LocatorType.NAME, true);
        enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, note, newAppointmentNoteTextArea);
        Assert.assertEquals(newAppointmentNoteTextArea.getAttribute("value"), note,
                "Entering email \"" + note + "\" in new appointment text are has failed...");
        TestDataHelper.setTestData("new_appointment_note", note);
    }

    public void selectServiceInNewAppointmentMultiList(String service) {
        ScenarioLogManager.getLogger().info("Trying to select service \"" + service + "\" in new appointment multi list...");
        CONTEXT.set();
        if (service.equals("<beliebig>")) {
            WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
            wait.until(ExpectedConditions.numberOfElementsToBeMoreThan(By.xpath("//div[@id='select-requests']/ul/li/div/label/span"), 1));
            List<WebElement> availableServices = findElementsByLocatorType(
                    TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                    "//div[@id='select-requests']/ul/li/div/label/span", LocatorType.XPATH);
            final SecureRandom SECURE_RANDOM = new SecureRandom();
            service = availableServices.get(SECURE_RANDOM.nextInt(availableServices.size() - 1)).getText().replaceAll(" \\([0-9]+ min\\)$", "");
            ScenarioLogManager.getLogger().info("Randomly found service \"" + service + "\"");
        }
        String checkboxXpath = "//div[@id='select-requests']/ul/li/div/label/span[contains(text(),'" + service + "')]/../input[@class='form-check-input']";
        WebElement serviceCheckbox = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.presenceOfElementLocated(By.xpath(checkboxXpath)));
        scrollToCenterByVisibleElement(serviceCheckbox);
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, checkboxXpath, LocatorType.XPATH, false);
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//ul[@aria-label='Dienstleistungen Abwahlliste']//span[contains(text(),'" + service + "')]",
                        LocatorType.XPATH, false), "Selecting service \"" + service + "\" in new appointment multi list has failed...");
        TestDataHelper.setTestData("new_appointment_service", service);
    }

    public void clickOnBookAppointmentButton(boolean assertErrors) {
        ScenarioLogManager.getLogger().info("Trying to click on \"book appointment\"  button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[text()='Termin buchen']", LocatorType.XPATH, false, CONTEXT);

        // Warten auf eine Fehlermeldung oder Erfolgsmeldung
        AtomicReference<String> newAppointmentNumber = new AtomicReference<>("");
        try {
            WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
            wait.until((ExpectedCondition<Boolean>) waitDriver -> {
                CONTEXT.waitForSpinners();

                // Prüfung auf Fehlermeldungen
                List<WebElement> errorMessageWebElements = findElementsByLocatorType(
                        TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                        "//li[@data-key='familyName'] | //li[@data-key='email'] | //li[@data-key='requests']", LocatorType.XPATH);
                if (!errorMessageWebElements.isEmpty()) {
                    for (WebElement errorMassageWebElement : errorMessageWebElements) {
                        if (errorMassageWebElement.getAttribute("data-key").equals("familyName")) {
                            TestDataHelper.setTestData("Fehler-Name", "Fehler: Es muss ein aussagekräftiger Name eingegeben werden.");
                        }
                        if (errorMassageWebElement.getAttribute("data-key").equals("email")) {
                            TestDataHelper.setTestData("Fehler-Email", "Fehler: Für den Email-Versand muss eine gültige E-Mail Adresse angegeben werden.");
                        }
                        if (errorMassageWebElement.getAttribute("data-key").equals("requests")) {
                            TestDataHelper.setTestData("Fehler-Dienstleistung", "Fehler: Es muss mindestens eine Dienstleistung ausgewählt werden!");
                        }
                    }
                    return true;
                }
                // Prüfung auf Erfolgsmeldung
                List<WebElement> successMessageWebElement = findElementsByLocatorType(
                        TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                        "//h2[text()='Termin erfolgreich eingetragen']", LocatorType.XPATH);
                if (!successMessageWebElement.isEmpty()) {
                    Pattern appointmentNumberPattern = Pattern.compile("^Termin-Nr\\.\\s*([0-9]+).*");
                    Matcher appointmentNumberMatcher = appointmentNumberPattern.matcher(
                            getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "//dt[starts-with(normalize-space(), 'Termin-Nr.')]",
                                    LocatorType.XPATH));
                    if (appointmentNumberMatcher.find()) {
                        newAppointmentNumber.set(appointmentNumberMatcher.group(1));
                        return true;
                    }
                }
                return false;
            });
        } catch (TimeoutException ignore) {

        }
        if (assertErrors) {
            Assert.assertNotEquals(newAppointmentNumber.get(), "", "Click on \"book appointment\" button has failed! Appointment number is not displayed,");
            Assert.assertNull(TestDataHelper.getTestData("Fehler-Name"), TestDataHelper.getTestData("Fehler-Name") + ",");
            Assert.assertNull(TestDataHelper.getTestData("Fehler-Email"), TestDataHelper.getTestData("Fehler-Email") + ",");
            Assert.assertNull(TestDataHelper.getTestData("Fehler-Dienstleistung"), TestDataHelper.getTestData("Fehler-Dienstleistung") + ",");
        }
        if (!newAppointmentNumber.get().isEmpty()) {
            TestDataHelper.setTestData("new_appointment_number", newAppointmentNumber.get());
        }
    }

    public String clickOnAddSpontaneousCustomer() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Add spontaneous customer\"  button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[text()='Spontankunden hinzufügen']", LocatorType.XPATH, false, CONTEXT);
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//h2[text()='Wartenummer wurde hinzugefügt']", LocatorType.XPATH, false, CONTEXT),
                "Click on \"Add spontaneous customer\"  button has failed! Success message is not displayed!");
        Pattern appointmentNumberPattern = Pattern.compile("^Termin-Nr\\.\\s*([0-9]+).*");
        Matcher appointmentNumberMatcher = appointmentNumberPattern.matcher(
                getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "//dt[starts-with(normalize-space(), 'Termin-Nr.')]", LocatorType.XPATH, CONTEXT));
        Assert.assertTrue(appointmentNumberMatcher.find(), "Click on \"Add spontaneous customer\"  button has failed! Waiting number is not displayed!");
        TestDataHelper.setTestData("new_waiting_number", appointmentNumberMatcher.group(1));
        return appointmentNumberMatcher.group(1);
    }

    public void clickOnCloseButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Close\"  button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[contains(text(),'Schließen')]", LocatorType.XPATH, false);
    }

    public void clickOnOkButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Ok\"  button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[contains(text(),'Ok')]", LocatorType.XPATH, false);
    }

    public void clickOnPrintAppointmentNumberButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"print appointment number\"  button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[contains(text(),'Vorgangsnummer drucken')]", LocatorType.XPATH, false);
    }

    public void checkAppointmentConfirmationPrint() {
        ScenarioLogManager.getLogger().info("Checking appointment confirmation print...");
        WebElement title = waitForElementByXpath(DEFAULT_EXPLICIT_WAIT_TIME, "//h1[@class='main-title']", false, false);
        Assert.assertEquals(title.getText(), "Vorgangsnummer drucken", "Title \"Vorgangsnummer drucken\" is not visible!");
        WebElement appointmentNumber = waitForElementByXpath(DEFAULT_EXPLICIT_WAIT_TIME, "//div[@class='print-number']", false, false);
        Assert.assertEquals(appointmentNumber.getText(), TestDataHelper.getTestData("new_appointment_number"),
                "Appointment number \"" + TestDataHelper.getTestData("new_appointment_number") + "\" is not visible!");
        String reservationDateString = LocalDate.parse(TestDataHelper.getTestData("new_appointment_date"), DateTimeFormatter.ofPattern("dd.MM.yyyy"))
                .format(DateTimeFormatter.ofPattern("eee dd.MM.yyyy", Locale.GERMANY));
        WebElement reservationDateAndTime = waitForElementByXpath(DEFAULT_EXPLICIT_WAIT_TIME, "//div[@class='print-content']/span/span", false, false);
        Assert.assertEquals(reservationDateAndTime.getText().trim(),
                "Ihr Termin ist am " + reservationDateString + " um " + TestDataHelper.getTestData("new_appointment_time") + " Uhr.",
                "Appointment date \"" + reservationDateString + "\" and time \"" + TestDataHelper.getTestData("new_appointment_time") + "\" are not visible!");
        WebElement location = waitForElementByXpath(DEFAULT_EXPLICIT_WAIT_TIME, "//div[@class='print-content']/span[2]", false, false);
        Assert.assertEquals(location.getText().split("\n")[2].trim(), "Standort: " + TestDataHelper.getTestData("location"),
                "Appointment location \"" + TestDataHelper.getTestData("location") + "\" is not visible!");
    }

    public void clickOnDeleteIcon() {
        ScenarioLogManager.getLogger().info("Trying to click on \"delete\" icon...");
        final String TRASH_BIN_ICON_LOCATOR_XPATH = "//i[contains(@class,'fa-trash-alt')]";
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, TRASH_BIN_ICON_LOCATOR_XPATH, LocatorType.XPATH, false);
        Alert alert = waitForAlertIsPresent(DEFAULT_EXPLICIT_WAIT_TIME);
        alert.accept();
        Assert.assertTrue(isWebElementInvisible(DEFAULT_EXPLICIT_WAIT_TIME, TRASH_BIN_ICON_LOCATOR_XPATH, LocatorType.XPATH),
                "Click on \"delete\" icon has failed! It is still visible!");
    }

    public void clickOnDeleteOpeningHoursWithNote(String note) {
        ScenarioLogManager.getLogger().info("Trying to click on \"delete\" opening hour with note...");
        final String TRASH_BIN_ICON_LOCATOR_XPATH = "(//tr[td[contains(text(), '" + note + "')]]//i[contains(@class, 'fa-trash-alt')])[last()]";
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, TRASH_BIN_ICON_LOCATOR_XPATH, LocatorType.XPATH, false);
        Alert alert = waitForAlertIsPresent(DEFAULT_EXPLICIT_WAIT_TIME);
        alert.accept();
        Assert.assertTrue(isWebElementInvisible(DEFAULT_EXPLICIT_WAIT_TIME, TRASH_BIN_ICON_LOCATOR_XPATH, LocatorType.XPATH),
                "Click on \"delete\" icon has failed! It is still visible!");

    }

    public void checkQueueElementsVisible() {
        ScenarioLogManager.getLogger().info("Checking if queue table is visible...");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, APPOINTMENT_QUEUE_TABLE_LOCATOR_ID, LocatorType.ID, false, CONTEXT),
                "Queue table is not visible!");
        scrollToCenterByVisibleElement(findElementByLocatorType(APPOINTMENT_QUEUE_TABLE_LOCATOR_ID, LocatorType.ID, false));

        ScenarioLogManager.getLogger().info("Checking if table header elements are visible...");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[1 and text() = 'Lfdnr.']",
                        LocatorType.XPATH, false, CONTEXT), "Column head \"Lfdnr.\" is not visible!");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[2 and text() = 'Uhrzeit']",
                        LocatorType.XPATH, false, CONTEXT), "Column head \"Uhrzeit\" is not visible!");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[3 and text() = 'Nr.']",
                LocatorType.XPATH, false, CONTEXT), "Column head \"Nr.\" is not visible!");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME,
                "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[contains(text(), 'Name') and .//small[contains(text(), '(Aufrufe)')]]",
                LocatorType.XPATH, false, CONTEXT), "Column head \"Name (Aufrufe)\" is not visible!");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[5 and text() = 'Telefon']",
                        LocatorType.XPATH, false, CONTEXT), "Column head \"Telefon\" is not visible!");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[6 and text() = 'Mail']",
                        LocatorType.XPATH, false, CONTEXT), "Column head \"Mail\" is not visible!");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME,
                        "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[7 and text() = 'Dienstleistung']", LocatorType.XPATH, false, CONTEXT),
                "Column head \"Dienstleistung\" is not visible!");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[8 and text() = 'Anmerkung']",
                        LocatorType.XPATH, false, CONTEXT), "Column head \"Anmerkung\" is not visible!");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[9 and text() = 'Wartezeit']",
                        LocatorType.XPATH, false, CONTEXT), "Column head \"Wartezeit\" is not visible!");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[10]", LocatorType.XPATH, false,
                        CONTEXT), "Column head for delete buttons is not visible!");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[11]", LocatorType.XPATH, false,
                        CONTEXT), "Column head for edit buttons is not visible!");
    }

    public void checkQueueElementsVisibleWithoutSMS() {
        ScenarioLogManager.getLogger().info("Checking if queue table is visible...");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, APPOINTMENT_QUEUE_TABLE_LOCATOR_ID, LocatorType.ID, false, CONTEXT),
                "Queue table is not visible!");
        scrollToCenterByVisibleElement(findElementByLocatorType(APPOINTMENT_QUEUE_TABLE_LOCATOR_ID, LocatorType.ID, false));

        ScenarioLogManager.getLogger().info("Checking if table header elements are visible...");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[1 and text() = 'Lfdnr.']",
                        LocatorType.XPATH, false, CONTEXT), "Column head \"Lfdnr.\" is not visible!");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[2 and text() = 'Uhrzeit']",
                        LocatorType.XPATH, false, CONTEXT), "Column head \"Uhrzeit\" is not visible!");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[3 and text() = 'Nr.']",
                LocatorType.XPATH, false, CONTEXT), "Column head \"Nr.\" is not visible!");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME,
                        "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[4 and string() = 'Name (Aufrufe)']", LocatorType.XPATH, false, CONTEXT),
                "Column head \"Name (Aufrufe)\" is not visible!");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[5 and text() = 'Telefon']",
                        LocatorType.XPATH, false, CONTEXT), "Column head \"Telefon\" is not visible!");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[6 and text() = 'Mail']",
                        LocatorType.XPATH, false, CONTEXT), "Column head \"Mail\" is not visible!");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME,
                        "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[7 and text() = 'Dienstleistung']", LocatorType.XPATH, false, CONTEXT),
                "Column head \"Dienstleistung\" is not visible!");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[8 and text() = 'Anmerkung']",
                        LocatorType.XPATH, false, CONTEXT), "Column head \"Anmerkung\" is not visible!");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[9 and text() = 'Wartezeit']",
                        LocatorType.XPATH, false, CONTEXT), "Column head \"Wartezeit\" is not visible!");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[10]", LocatorType.XPATH, false,
                        CONTEXT), "Column head for delete buttons is not visible!");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//table[@id='" + APPOINTMENT_QUEUE_TABLE_LOCATOR_ID + "']//th[11]", LocatorType.XPATH, false,
                        CONTEXT), "Column head for edit buttons is not visible!");
    }

    public void showTheFinishedAppointmentTable() {
        ScenarioLogManager.getLogger().info("Trying to show the finished appointments table...");
        WebElement control = findElementByLocatorType("finished-appointments-control", LocatorType.ID, true);
        WebElement upButton = control.findElement(By.className("fa-angle-up"));
        WebElement downButton = control.findElement(By.className("fa-angle-down"));

        if (downButton.isDisplayed()) {
            control.click();
            Assert.assertTrue(upButton.isDisplayed(), "Up button should be displayed after clicking to show the table.");
            Assert.assertFalse(downButton.isDisplayed(), "Down button should be hidden after clicking to show the table.");
        }
    }

    public Duration getFinishedAppointmentWaitingTime(String customer) {
        ScenarioLogManager.getLogger().info("Trying to get waiting time for " + customer + "  under the finished appointments table...");
        isCustomerVisibleInFinishedTable(customer);
        String xpath = "//table[@id='table-finished-appointments']//tr[td[position() = 3 and contains(., '" + customer + "')]]/td[6]";
        WebElement waitingTimeElement = DRIVER.findElement(By.xpath(xpath));

        String waitingTimeText = waitingTimeElement.getText();

        DateTimeFormatter formatter = DateTimeFormatter.ofPattern("H:mm:ss");
        LocalTime time = LocalTime.parse(waitingTimeText, formatter);
        Duration waitingTime = Duration.ofHours(time.getHour()).plusMinutes(time.getMinute()).plusSeconds(time.getSecond());

        return waitingTime;
    }

    public Duration getFinishedAppointmentProcessingTime(String customer) {
        ScenarioLogManager.getLogger().info("Trying to get processing time for " + customer + " under the finished appointments table...");
        String xpath = "//table[@id='table-finished-appointments']//tr[td[position() = 3 and contains(., '" + customer + "')]]/td[7]";
        WebElement processingTimeElement = DRIVER.findElement(By.xpath(xpath));
        Assert.assertTrue(processingTimeElement.isDisplayed(), "Customer '" + customer + "' not found in finished appointments!");
        String processingTimeText = processingTimeElement.getText();
        Assert.assertFalse(processingTimeText.isEmpty(), "Processing time for customer '" + customer + "' is empty!");
        DateTimeFormatter formatter = DateTimeFormatter.ofPattern("H:mm:ss");
        LocalTime time = LocalTime.parse(processingTimeText, formatter);
        Duration processingTime = Duration.ofHours(time.getHour()).plusMinutes(time.getMinute()).plusSeconds(time.getSecond());

        return processingTime;
    }

    public void SelectClusterLocation(String location) {
        String xpath = "//form[@action='/terminvereinbarung/admin/workstation/select/']//select[@name='scope']";
        WebElement clusterLocations = findElementByLocatorType(xpath, LocatorType.XPATH, true);
        Assert.assertNotNull(clusterLocations, "Cluster location dropdown element not found!");
        scrollToCenterByVisibleElement(clusterLocations);
        Select clusterLocationSelections = new Select(clusterLocations);
        List<WebElement> options = clusterLocationSelections.getOptions();
        boolean locationFound = false;
        for (WebElement option : options) {
            if (option.getText().equals(location)) {
                locationFound = true;
                break;
            }
        }
        Assert.assertTrue(locationFound, "Cluster location '" + location + "' not found in dropdown!");

        clusterLocationSelections.selectByVisibleText(location);
    }

    public void confirmClusterLocationSelection() {
        ScenarioLogManager.getLogger().info("Trying to confirm selection of cluster location...");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, ".button.button--default.button-ok", LocatorType.CSSSELECTOR, true));
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, ".button.button--default.button-ok", LocatorType.CSSSELECTOR, false);
    }

    public void isQueueEmpty() {
        ScenarioLogManager.getLogger().info("Checking for the queue to be empty...");
        CONTEXT.waitForSpinners();
        boolean isQueueInvisible = isWebElementInvisible(DEFAULT_EXPLICIT_WAIT_TIME, APPOINTMENT_QUEUE_TABLE_LOCATOR_ID, LocatorType.ID);
        Assert.assertTrue(isQueueInvisible, "Queue is not empty: The appointment queue table is still visible.");
    }

    // -------------------------------
    // Table-specific methods with logging
    // -------------------------------
    public void checkForValuesInQueueColumn(String column, String... searchStrings) {
        ScenarioLogManager.getLogger().info("Checking waiting list column '{}'", column);
        CONTEXT.waitForSpinners();
        WebElement table = findElementByLocatorType(APPOINTMENT_QUEUE_TABLE_LOCATOR_ID, LocatorType.ID, false);
        scrollToCenterByVisibleElement(table);

        logColumnValues(APPOINTMENT_QUEUE_TABLE_LOCATOR_ID, LocatorType.ID, column);

        // now call framework method
        areValuesVisibleInTableColumn(APPOINTMENT_QUEUE_TABLE_LOCATOR_ID, LocatorType.ID, column, searchStrings);
    }

    public void checkForValuesInParkingTableColumn(String column, String... searchStrings) {
        ScenarioLogManager.getLogger().info("Checking parked table column '{}'", column);
        CONTEXT.waitForSpinners();
        WebElement table = findElementByLocatorType(APPOINTMENT_PARKED_TABLE_LOCATOR_ID, LocatorType.ID, false);
        scrollToCenterByVisibleElement(table);

        logColumnValues(APPOINTMENT_PARKED_TABLE_LOCATOR_ID, LocatorType.ID, column);

        areValuesVisibleInTableColumn(APPOINTMENT_PARKED_TABLE_LOCATOR_ID, LocatorType.ID, column, searchStrings);
    }

    public void checkForValuesInFinishedTableColumn(String column, String... searchStrings) {
        ScenarioLogManager.getLogger().info("Checking finished table column '{}'", column);
        CONTEXT.waitForSpinners();
        WebElement table = findElementByLocatorType(APPOINTMENT_FINISHED_TABLE_LOCATOR_ID, LocatorType.ID, false);
        scrollToCenterByVisibleElement(table);

        logColumnValues(APPOINTMENT_FINISHED_TABLE_LOCATOR_ID, LocatorType.ID, column);

        areValuesVisibleInTableColumn(APPOINTMENT_FINISHED_TABLE_LOCATOR_ID, LocatorType.ID, column, searchStrings);
    }

    public void checkForValuesInMissedTableColumn(String column, String... searchStrings) {
        ScenarioLogManager.getLogger().info("Checking missed table column '{}'", column);
        CONTEXT.waitForSpinners();
        WebElement table = findElementByLocatorType(APPOINTMENT_MISSED_TABLE_LOCATOR_ID, LocatorType.ID, false);
        scrollToCenterByVisibleElement(table);

        logColumnValues(APPOINTMENT_MISSED_TABLE_LOCATOR_ID, LocatorType.ID, column);

        areValuesVisibleInTableColumn(APPOINTMENT_MISSED_TABLE_LOCATOR_ID, LocatorType.ID, column, searchStrings);
    }

    private void logColumnValues(String tableLocator, LocatorType tableLocatorType, String columnName) {
        WebElement table = findElementByLocatorType(tableLocator, tableLocatorType, true);
    
        // find column index
        List<WebElement> headerElements = table.findElements(By.xpath(".//thead//th"));
        OptionalInt columnIndexOpt = IntStream.range(0, headerElements.size())
                .filter(i -> columnName.equalsIgnoreCase(headerElements.get(i).getText().trim()))
                .findFirst();
    
        if (columnIndexOpt.isEmpty()) {
            ScenarioLogManager.getLogger().warn("Column '{}' not found in table '{}'", columnName, tableLocator);
            return;
        }
    
        int columnIndex = columnIndexOpt.getAsInt() + 1;
    
        // fetch all actual values in the column
        List<String> columnValues = table.findElements(By.xpath(".//tbody//tr//td[" + columnIndex + "]"))
                .stream()
                .map(e -> e.getText().trim())
                .toList();
    
        ScenarioLogManager.getLogger().info("Table '{}' - column '{}': actual values = {}", tableLocator, columnName, columnValues);
    }
}
