package zms.ataf.ui.pages.admin.workview.counterprocessingstation;

import java.security.SecureRandom;
import java.time.Duration;
import java.time.LocalDate;
import java.time.LocalTime;
import java.time.format.DateTimeFormatter;
import java.util.HashSet;
import java.util.List;
import java.util.Set;
import java.util.Locale;
import java.util.concurrent.atomic.AtomicBoolean;
import java.util.concurrent.atomic.AtomicReference;
import java.util.regex.Matcher;
import java.util.regex.Pattern;
import java.util.stream.Collectors;

import org.openqa.selenium.Alert;
import org.openqa.selenium.By;
import org.openqa.selenium.ElementClickInterceptedException;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.Keys;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebElement;
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
import zms.ataf.helpers.AppointmentCountHelper;
import zms.ataf.helpers.BerlinTime;
import zms.ataf.ui.pages.admin.AdminPage;
import zms.ataf.ui.pages.admin.AdminPageContext;

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

        String numOnly = transactionNumber == null
                ? ""
                : transactionNumber.replaceAll("\\D+", "");
    
        ScenarioLogManager.getLogger().info(
                "Checking for "
                        + (isSpontaneousCustomer ? "spontaneous " : "")
                        + "customer with Transaction number: ("
                        + numOnly + ") in waiting list..."
        );
    
        CONTEXT.waitForSpinners();
    
        showSpontaneousCustomers(isSpontaneousCustomer);
    
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(20));
    
        // Wait for at least one row in queue table
        By firstRow = By.cssSelector(
                "#table-queued-appointments tbody tr"
        );
    
        wait.until(ExpectedConditions.presenceOfElementLocated(firstRow));
    
        // Now scroll safely
        WebElement table = DRIVER.findElement(
                By.id("table-queued-appointments")
        );
    
        scrollToCenterByVisibleElement(table);
    
        // The queue shows the display number. Briefbüro prints a prefix (X0723);
        // other scopes print the process id. Match either form.
        String shown = transactionNumber == null ? "" : transactionNumber.trim();
        By rowByNumber = By.xpath(
                "//table[@id='table-queued-appointments']" +
                "//tbody/tr[.//td[normalize-space()='" + shown + "'" +
                " or normalize-space()='" + numOnly + "']]"
        );
    
        WebElement row = wait.until(
                ExpectedConditions.visibilityOfElementLocated(rowByNumber)
        );
    
        Assert.assertTrue(
                row.isDisplayed(),
                "Customer with transaction number "
                        + numOnly
                        + " is not visible in queue!"
        );
    }

    public void isCustomerVisibleInParkingTableByNumber(String number) {
        String numOnly = number == null ? "" : number.replaceAll("\\D+", "");
        ScenarioLogManager.getLogger().info("Checking parked list by Nr. (" + numOnly + ")...");
    
        CONTEXT.waitForSpinners();
    
        By parkedTable = By.id(APPOINTMENT_PARKED_TABLE_LOCATOR_ID); // "table-parked-appointments"
        WebDriverWait wait = new WebDriverWait(DRIVER, java.time.Duration.ofSeconds(20));
        wait.until(org.openqa.selenium.support.ui.ExpectedConditions.visibilityOfElementLocated(parkedTable));
    
        scrollToCenterByVisibleElement(findElementByLocatorType(APPOINTMENT_PARKED_TABLE_LOCATOR_ID, LocatorType.ID, false));
    
        // 3rd column (Nr.) = td with 2 preceding siblings
        By rowByNr = By.xpath(
            "//table[@id='" + APPOINTMENT_PARKED_TABLE_LOCATOR_ID + "']" +
            "//tbody/tr[td[count(preceding-sibling::td)=2][normalize-space(.)='" + numOnly + "']]");
        org.openqa.selenium.WebElement row =
            wait.until(org.openqa.selenium.support.ui.ExpectedConditions.presenceOfElementLocated(rowByNr));
    
        org.testng.Assert.assertTrue(row.isDisplayed(),
            "Parked row with Nr. '" + numOnly + "' not visible in " + APPOINTMENT_PARKED_TABLE_LOCATOR_ID + "!");
    }

    public void isCustomerVisibleInParkingTableByName(String customerName) {
        ScenarioLogManager.getLogger().info("Checking parked list by Name (" + customerName + ")...");
    
        CONTEXT.waitForSpinners();
    
        By parkedTable = By.id(APPOINTMENT_PARKED_TABLE_LOCATOR_ID);
        WebDriverWait wait = new WebDriverWait(DRIVER, java.time.Duration.ofSeconds(20));
        wait.until(org.openqa.selenium.support.ui.ExpectedConditions.visibilityOfElementLocated(parkedTable));
    
        scrollToCenterByVisibleElement(findElementByLocatorType(APPOINTMENT_PARKED_TABLE_LOCATOR_ID, LocatorType.ID, false));
    
        // 5th column (Name) = td with 4 preceding siblings
        By rowByName = By.xpath(
            "//table[@id='" + APPOINTMENT_PARKED_TABLE_LOCATOR_ID + "']" +
            "//tbody/tr[td[count(preceding-sibling::td)=4][normalize-space(.)='" + customerName + "']]");
        org.openqa.selenium.WebElement row =
            wait.until(org.openqa.selenium.support.ui.ExpectedConditions.presenceOfElementLocated(rowByName));
    
        org.testng.Assert.assertTrue(row.isDisplayed(),
            "Parked row with Name '" + customerName + "' not visible in " + APPOINTMENT_PARKED_TABLE_LOCATOR_ID + "!");
    }

    public void isCustomerVisibleInParkingTable(String token) {
        String numOnly = token == null ? "" : token.replaceAll("\\D+", "");
        if (!numOnly.isBlank()) {
            try {
                isCustomerVisibleInParkingTableByNumber(numOnly);
                return;
            } catch (org.openqa.selenium.TimeoutException ignore) {
                ScenarioLogManager.getLogger().warn("No parked row found by Nr. '" + numOnly + "', falling back to Name: " + token);
            }
        }
        isCustomerVisibleInParkingTableByName(token);
    }

    public void isCustomerVisibleInMissedTable(String transactionNumber, boolean isSpontaneousCustomer) {
        ScenarioLogManager.getLogger().info("Checking for " + (isSpontaneousCustomer ?
                "spontaneous " :
                "") + "customer with Transaction number: (" + transactionNumber + ") to be visible in missed list...");
        CONTEXT.waitForSpinners();
        scrollToCenterByVisibleElement(findElementByLocatorType(APPOINTMENT_MISSED_TABLE_LOCATOR_ID, LocatorType.ID, false));
        showSpontaneousCustomers(isSpontaneousCustomer);
        // Use the transaction number to verify its presence in the 'Nr.' column
        checkForValuesInMissedTableColumn("Nr.", transactionNumber);
    }

    public void isCustomerVisibleInFinishedTable(String customer) {
        ScenarioLogManager.getLogger().info("Checking for customer(" + customer + ") under finished appointments...");
        showTheFinishedAppointmentTable();
        CONTEXT.waitForSpinners();
    
        By finishedTable = By.id(APPOINTMENT_FINISHED_TABLE_LOCATOR_ID);
        WebDriverWait w = new WebDriverWait(DRIVER, Duration.ofSeconds(15));
    
        try {
            w.until(ExpectedConditions.visibilityOfElementLocated(finishedTable));
        } catch (TimeoutException first) {
            ScenarioLogManager.getLogger().warn("Finished table not visible on first attempt, retrying...");
            showTheFinishedAppointmentTable();
            CONTEXT.waitForSpinners();
            try {
                w.until(ExpectedConditions.visibilityOfElementLocated(finishedTable));
            } catch (TimeoutException second) {
                Assert.fail("Finished appointments table did not become visible after retry.");
            }
        }
    
        scrollToCenterByVisibleElement(findElementByLocatorType(APPOINTMENT_FINISHED_TABLE_LOCATOR_ID, LocatorType.ID, false));
        checkForValuesInFinishedTableColumn("Name", customer);
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
        AppointmentCountHelper.incrementAppointmentCount();
        AppointmentCountHelper.incrementAppointmentCanceledCount();
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
        AppointmentCountHelper.incrementAppointmentCanceledCount();
    }

    /**
     * Deletes the queue row for a customer just booked in this scenario.
     * The trash icon uses the internal process id, which is not the number shown as Termin-Nr.
     */
    /**
     * A forwarded Terminkunde is queued without an appointment time, so the Prio field is visible.
     * Mittel is the selected option when the stored priority is 2.
     */
    public void assertQueuedCustomerPriority(String familyName, String priorityLabel) {
        CONTEXT.set();
        showSpontaneousCustomers(true);
        CONTEXT.waitForSpinners();
        String editLink = "//table[@id='table-queued-appointments']//tr["
                + "td[contains(@class,'callnextclient') and normalize-space(.)='" + familyName + "']]"
                + "//a[contains(@class,'process-edit')]";
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, editLink, LocatorType.XPATH, false, CONTEXT);
        WebElement priority = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector("select[name='priority']")));
        String selected = new Select(priority).getFirstSelectedOption().getText().trim();
        Assert.assertEquals(selected, priorityLabel,
                "Expected priority \"" + priorityLabel + "\" for " + familyName + ", but found \"" + selected + "\".");
    }

    public void deleteQueuedAppointmentByFamilyName(String familyName) {
        CONTEXT.set();
        if ("true".equals(TestDataHelper.getTestData("appointment_booked_as_walk_in"))) {
            showSpontaneousCustomers(true);
            CONTEXT.waitForSpinners();
        }
        ScenarioLogManager.getLogger().info("Deleting queued appointment for {}", familyName);
        String deleteLink = "//table[@id='table-queued-appointments']//tr["
                + "td[contains(@class,'callnextclient') and normalize-space(.)='" + familyName + "']]"
                + "//a[contains(@class,'process-delete')]";
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, deleteLink, LocatorType.XPATH, false, CONTEXT);
        WebElement messageTitleElement = findElementByLocatorType("section.board.dialog h2.board__heading", LocatorType.CSSSELECTOR, false);
        Assert.assertTrue(
                messageTitleElement.getText().contains("Eintrag löschen"),
                "Delete confirmation did not open for " + familyName);
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "a.button.button--destructive.button-ok", LocatorType.CSSSELECTOR, false, CONTEXT);
        messageTitleElement = findElementByLocatorType("h2.message__heading.title", LocatorType.CSSSELECTOR, false);
        Assert.assertEquals(messageTitleElement.getText(), "Vorgang gelöscht",
                "Deleting the appointment for " + familyName + " did not succeed.");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "button.button-ok", LocatorType.CSSSELECTOR, false);
    }

    public void enterDateInNewAppointmentTextField(String date) {
        ScenarioLogManager.getLogger().info("Trying to enter date \"" + date + "\" in new appointment text field...");

        // process_date is a React datepicker. The old calendar tile (div[data-date]) is not on this form.
        LocalDate dateDesired = LocalDate.parse(date, DateTimeFormatter.ofPattern("dd.MM.yyyy", Locale.GERMAN));
        int maxDays = TestPropertiesHelper.getPropertyAsInteger("numberOfRetries", true, 3) * 3;
        for (int count = 0; count <= maxDays; count++) {
            if (count > 0) {
                dateDesired = dateDesired.plusDays(1L);
                date = dateDesired.format(DateTimeFormatter.ofPattern("dd.MM.yyyy", Locale.GERMAN));
                ScenarioLogManager.getLogger().info("The day is not selectable. Trying {}", date);
            }
            if (chooseDateInAppointmentPicker(dateDesired, date)) {
                TestDataHelper.setTestData("new_appointment_date", date);
                return;
            }
        }
        Assert.fail("No selectable appointment day found from " + date);
    }

    /** @return false when the day is shown but disabled, so the caller can try the next day. */
    private boolean chooseDateInAppointmentPicker(LocalDate target, String date) {
        WebElement dateField = findElementByLocatorType("process_date", LocatorType.ID, true);
        dateField.sendKeys(Keys.ESCAPE);
        WebElement opener = findElementByLocatorType("#appointment-datepicker a.calendar-placement", LocatorType.CSSSELECTOR, true);
        ((JavascriptExecutor) DRIVER).executeScript("arguments[0].click();", opener);

        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        By monthHeader = By.cssSelector(".react-datepicker__current-month");
        wait.until(ExpectedConditions.visibilityOfElementLocated(monthHeader));

        DateTimeFormatter monthYear = DateTimeFormatter.ofPattern("MMMM yyyy", Locale.GERMAN);
        String targetMonth = target.format(monthYear);
        DateTimeFormatter shownMonth = DateTimeFormatter.ofPattern("d MMMM yyyy", Locale.GERMAN);
        String shown = "";
        boolean reachedMonth = false;
        for (int step = 0; step < 14; step++) {
            shown = DRIVER.findElement(monthHeader).getText().replace('\u00a0', ' ').trim();
            if (shown.equalsIgnoreCase(targetMonth)) {
                reachedMonth = true;
                break;
            }
            LocalDate shownDate = LocalDate.parse("1 " + shown, shownMonth);
            String navigation = shownDate.isBefore(target.withDayOfMonth(1))
                    ? ".react-datepicker__navigation--next"
                    : ".react-datepicker__navigation--previous";
            DRIVER.findElement(By.cssSelector(navigation)).click();
        }
        if (!reachedMonth) {
            Assert.fail("Appointment calendar did not reach " + targetMonth + ". It still shows " + shown + ".");
        }

        String dayText = Integer.toString(target.getDayOfMonth());
        By day = By.xpath("//div[contains(@class,'react-datepicker__day')"
                + " and not(contains(@class,'outside-month'))"
                + " and normalize-space(.)='" + dayText + "']");
        WebElement dayElement = wait.until(ExpectedConditions.presenceOfElementLocated(day));
        String dayClass = dayElement.getAttribute("class");
        if (dayClass != null && dayClass.contains("disabled")) {
            dateField.sendKeys(Keys.ESCAPE);
            return false;
        }
        wait.until(ExpectedConditions.elementToBeClickable(dayElement)).click();
        wait.until(ExpectedConditions.attributeToBe(By.id("process_date"), "value", date));
        CONTEXT.waitForSpinners();
        return true;
    }

    /**
     * Today's time list always offers Spontankunde ({@code 00-00}) even when no appointment slot is left.
     */
    private boolean selectWalkInOption(Select timeList, WebElement select) {
        boolean hasWalkIn = timeList.getOptions().stream().anyMatch(option -> option.getText().contains("Spontankunde"));
        if (!hasWalkIn) {
            return false;
        }
        timeList.selectByValue("00-00");
        fireProcessTimeChange(select);
        TestDataHelper.setTestData("new_appointment_time", "00:00");
        TestDataHelper.setTestData("appointment_booked_as_walk_in", "true");
        ScenarioLogManager.getLogger().info("No appointment slot left; selected Spontankunde.");
        return true;
    }

    public boolean hasBookAppointmentButton() {
        List<WebElement> buttons = DRIVER.findElements(By.cssSelector("button.process-reserve"));
        return !buttons.isEmpty() && buttons.get(0).isDisplayed();
    }

    public void selectWalkInCustomer() {
        WebElement select = findElementByLocatorType(APPOINTMENT_TIME_LOCATOR_ID, LocatorType.ID, true);
        Assert.assertTrue(selectWalkInOption(new Select(select), select), "Spontankunde is not in the time list.");
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.elementToBeClickable(By.cssSelector("button.process-queue")));
    }

    /**
     * Firefox applies {@code selectByValue} without the {@code change} event jQuery uses to load
     * {@code button.process-reserve}. Without that event the form stays on Spontankunden hinzufügen.
     */
    private void fireProcessTimeChange(WebElement select) {
        ((JavascriptExecutor) DRIVER).executeScript(
                "var select = arguments[0];"
                        + "select.dispatchEvent(new Event('change', {bubbles: true}));"
                        + "var jq = window.jQuery || window.$;"
                        + "if (jq) { jq(select).trigger('change'); }",
                select);
    }

    public void selectTimeInNewAppointmentDropDownList(String time) {
        selectTimeInNewAppointmentDropDownList(time, Set.of(), false);
    }

    public void selectTimeInNewAppointmentDropDownList(String time, Set<String> excludedTimes) {
        selectTimeInNewAppointmentDropDownList(time, excludedTimes, false);
    }

    public void selectTimeInNewAppointmentDropDownList(String time, Set<String> excludedTimes, boolean fallBackToWalkIn) {
        TestDataHelper.setTestData("appointment_booked_as_walk_in", "false");
        ScenarioLogManager.getLogger().info("Trying to select time \"" + time + "\" in new appointment drop down list...");
        Pattern timeSlotPattern = Pattern.compile("([0-9][0-9]:[0-9][0-9]) \\(noch ([0-9]) frei\\)");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.ignoring(StaleElementReferenceException.class, ElementClickInterceptedException.class);
        wait.pollingEvery(Duration.ofMillis(1000L));
        wait.withMessage("Could not locate any time slot elements in time!");
        int[] daysAhead = { 0 };
        try {
            wait.until((ExpectedCondition<Boolean>) waitDriver -> {
                TestDataHelper.setTestData("appointment_booked_as_walk_in", "false");
                CONTEXT.waitForSpinners();
                WebElement newAppointmentTimeDropDownList = findElementByLocatorType(APPOINTMENT_TIME_LOCATOR_ID, LocatorType.ID, true);
                scrollToCenterByVisibleElement(newAppointmentTimeDropDownList);
                Select newAppointmentTimeDropDownListSelections = new Select(newAppointmentTimeDropDownList);
                List<WebElement> options = newAppointmentTimeDropDownListSelections.getOptions();
                if (!options.isEmpty()) {
                    switch (time) {
                    case "<beliebig>":
                    case "<nächste>":
                        List<WebElement> bookableTimeSlots = options.stream()
                                .filter(option -> !option.getText().contains("Spontankunde"))
                                .filter(option -> {
                                    Matcher matcher = timeSlotPattern.matcher(option.getText());
                                    return matcher.find() && !excludedTimes.contains(matcher.group(1));
                                })
                                .collect(Collectors.toList());
                        if (bookableTimeSlots.isEmpty()) {
                            if (fallBackToWalkIn && selectWalkInOption(newAppointmentTimeDropDownListSelections, newAppointmentTimeDropDownList)) {
                                break;
                            }
                            return moveToNextDayWithSlots(daysAhead);
                        }
                        WebElement webElement;
                        if (time.equals("<beliebig>")) {
                            final SecureRandom SECURE_RANDOM = new SecureRandom();
                            webElement = bookableTimeSlots.get(SECURE_RANDOM.nextInt(bookableTimeSlots.size()));
                        } else {
                            webElement = bookableTimeSlots.get(0);
                        }
                        Matcher timeSlotMatcher = timeSlotPattern.matcher(webElement.getText());
                        timeSlotMatcher.find();
                        newAppointmentTimeDropDownListSelections.selectByValue(webElement.getAttribute("value"));
                        fireProcessTimeChange(newAppointmentTimeDropDownList);
                        ScenarioLogManager.getLogger().info("Time \"" + timeSlotMatcher.group(1) + "\" selected!");
                        TestDataHelper.setTestData("new_appointment_time", timeSlotMatcher.group(1));
                        break;
                    default:
                        for (WebElement webElementInList : options) {
                            if (webElementInList.getText().contains(time)) {
                                newAppointmentTimeDropDownListSelections.selectByValue(time.replaceFirst(":", "-"));
                                fireProcessTimeChange(newAppointmentTimeDropDownList);
                                break;
                            }
                        }
                        TestDataHelper.setTestData("new_appointment_time", time);
                    }
                    CONTEXT.waitForSpinners();
                    // shifting focus away from the dropdown
                    clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "h2.board__heading", LocatorType.CSSSELECTOR, false);
                    // on false it will retry
                    String expectedTime = TestDataHelper.getTestData("new_appointment_time").replaceFirst(":", "-");
                    if (!findElementByLocatorType(APPOINTMENT_TIME_LOCATOR_ID, LocatorType.ID, true).getAttribute("value")
                            .equals(expectedTime)) {
                        ScenarioLogManager.getLogger().warn("Time not selected! Retrying...");
                        return false;
                    }
                    boolean bookedAsWalkIn = "true".equals(TestDataHelper.getTestData("appointment_booked_as_walk_in"));
                    String submitButton = bookedAsWalkIn ? "button.process-queue" : "button.process-reserve";
                    if (DRIVER.findElements(By.cssSelector(submitButton)).isEmpty()) {
                        ScenarioLogManager.getLogger().warn(
                                "Time \"" + expectedTime + "\" is set, the booking button is not in the form yet. Retrying...");
                        return false;
                    }
                    return true;
                } else {
                    return moveToNextDayWithSlots(daysAhead);
                }
            });
        } catch (Exception e) {
            Assert.fail("Selecting time \"" + TestDataHelper.getTestData("new_appointment_time") + "\" in new appointment drop down list has failed,", e);
        }
    }

    /**
     * Today's Terminkunde list is empty once fewer than three hours remain until 23:55.
     * The next day is opened for the whole day, so the form moves there and the time list is read again.
     */
    private boolean moveToNextDayWithSlots(int[] daysAhead) {
        if (daysAhead[0] >= 7) {
            return false;
        }
        daysAhead[0]++;
        LocalDate day = BerlinTime.today().plusDays(daysAhead[0]);
        String date = day.format(DateTimeFormatter.ofPattern("dd.MM.yyyy", Locale.GERMAN));
        ScenarioLogManager.getLogger().info("No Terminkunde slot left; opening {}", date);
        enterDateInNewAppointmentTextField(date);
        return false;
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

    public void increaseSelectedServiceCount(String service, int times) {
        ScenarioLogManager.getLogger().info("Increasing the count of selected service \"{}\" by {}", service, times);
        By plus = By.xpath(selectedServiceRow(service) + "//input[@class='plus']");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        for (int i = 0; i < times; i++) {
            wait.until(ExpectedConditions.elementToBeClickable(plus)).click();
            CONTEXT.waitForSpinners();
        }
    }

    public void clearSelectedServiceList() {
        ScenarioLogManager.getLogger().info("Clearing the selected service list...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "button.clear-list", LocatorType.CSSSELECTOR, false);
        CONTEXT.waitForSpinners();
    }

    public void assertSelectedServiceCount(String service, int expectedCount) {
        By count = By.xpath(selectedServiceRow(service) + "//span[@class='request-count']");
        WebElement countElement = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(count));
        Assert.assertEquals(countElement.getText().trim(), Integer.toString(expectedCount),
                "Selected service \"" + service + "\" has the wrong count.");
    }

    public void assertSelectedServiceHidden(String service) {
        By row = By.xpath(selectedServiceRow(service));
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .withMessage("Service \"" + service + "\" is still in the selected list.")
                .until(driver -> driver.findElements(row).stream().noneMatch(WebElement::isDisplayed));
    }

    private String selectedServiceRow(String service) {
        return "//ul[@aria-label='Dienstleistungen Abwahlliste']//li[.//span[normalize-space(.)='" + service + "']]";
    }

    public void clickOnBookAppointmentButton(boolean assertErrors) {
        ScenarioLogManager.getLogger().info("Trying to click on \"book appointment\" button...");
    
        // Always reset context to avoid iframe / window leakage from previous tests
        DRIVER.switchTo().defaultContent();
    
        // Wait for loading spinners to disappear BEFORE interacting
        CONTEXT.waitForSpinners();
    
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME * 2L));
        AtomicReference<String> newAppointmentNumber = new AtomicReference<>("");
        AtomicBoolean validationErrorsVisible = new AtomicBoolean(false);
        Set<String> skippedTimes = new HashSet<>();
        final int maxBookingAttempts = 6;
        boolean bookingAttempted = false;
        fillCustomTextfieldsForSpontaneousCustomerIfNeeded();
    
        for (int attempt = 1; attempt <= maxBookingAttempts; attempt++) {
            if (attempt > 1 && bookingAttempted) {
                String previousTime = TestDataHelper.getTestData("new_appointment_time");
                if (previousTime != null && !previousTime.isBlank()) {
                    skippedTimes.add(previousTime);
                }
                ScenarioLogManager.getLogger().warn(
                        "Slot \"" + previousTime + "\" was not booked; trying the next available slot (attempt "
                                + attempt + "/" + maxBookingAttempts + ")...");
                CONTEXT.waitForSpinners();
                selectTimeInNewAppointmentDropDownList("<nächste>", skippedTimes);
            }
    
            WebElement bookButton = wait.until(
                    ExpectedConditions.elementToBeClickable(
                            By.cssSelector("button.process-reserve")
                    )
            );
    
            bookingAttempted = false;
            try {
                ((JavascriptExecutor) DRIVER).executeScript("arguments[0].scrollIntoView({block:'center'});", bookButton);
                ((JavascriptExecutor) DRIVER).executeScript("arguments[0].click();", bookButton);
                bookingAttempted = true;
            } catch (StaleElementReferenceException | ElementClickInterceptedException | TimeoutException e) {
                ScenarioLogManager.getLogger().warn(
                        "Book click did not complete (attempt " + attempt + "/" + maxBookingAttempts + ").");
                continue;
            }
    
            CONTEXT.waitForSpinners();
    
            try {
                wait.until(driver -> {
                    CONTEXT.waitForSpinners();
    
                    List<WebElement> errorElements = driver.findElements(By.cssSelector("ul.error-list li[data-key]"));
    
                    if (!errorElements.isEmpty()) {
                        for (WebElement element : errorElements) {
                            String key = element.getAttribute("data-key");
                            ScenarioLogManager.getLogger()
                                    .error("Booking rejected ({}): {}", key, element.getText());
                            switch (key) {
                            case "familyName":
                                TestDataHelper.setTestData(
                                        "Fehler-Name",
                                        "Fehler: Es muss ein aussagekräftiger Name eingegeben werden."
                                );
                                break;
                            case "email":
                                TestDataHelper.setTestData(
                                        "Fehler-Email",
                                        "Fehler: Für den Email-Versand muss eine gültige E-Mail Adresse angegeben werden."
                                );
                                break;
                            case "requests":
                                TestDataHelper.setTestData(
                                        "Fehler-Dienstleistung",
                                        "Fehler: Es muss mindestens eine Dienstleistung ausgewählt werden!"
                                );
                                break;
                            case "customTextfield":
                            case "customTextfield2":
                                TestDataHelper.setTestData(
                                        "Fehler-Bemerkung",
                                        "Fehler: Die zusätzliche Bemerkung fehlt."
                                );
                                break;
                            default:
                                break;
                            }
                        }
                        validationErrorsVisible.set(true);
                        return true;
                    }
    
                    List<WebElement> successHeader = driver.findElements(
                            By.xpath("//h2[normalize-space()='Termin wurde erfolgreich eingetragen']")
                    );
    
                    if (!successHeader.isEmpty()) {
                        WebElement dtElement = driver.findElement(
                                By.xpath("//dt[starts-with(normalize-space(), 'Termin-Nr.')]")
                        );
                        Matcher matcher = Pattern.compile("Termin-Nr\\.\\s*([A-Za-z]*[0-9]+)").matcher(dtElement.getText());
                        if (matcher.find()) {
                            newAppointmentNumber.set(matcher.group(1));
                            return true;
                        }
                    }
    
                    return false;
                });
            } catch (TimeoutException e) {
                ScenarioLogManager.getLogger().error(
                        "Timeout while waiting for success or error message after booking click (attempt "
                                + attempt + "/" + maxBookingAttempts + ").");
            }
    
            if (validationErrorsVisible.get() || !newAppointmentNumber.get().isEmpty()) {
                break;
            }
        }
    
        // -----------------------------
        // Assertions
        // -----------------------------
        if (assertErrors) {
            Assert.assertNotEquals(
                    newAppointmentNumber.get(),
                    "",
                    "Click on \"book appointment\" button has failed! Appointment number is not displayed."
            );
    
            Assert.assertNull(
                    TestDataHelper.getTestData("Fehler-Name"),
                    TestDataHelper.getTestData("Fehler-Name") + ","
            );
    
            Assert.assertNull(
                    TestDataHelper.getTestData("Fehler-Email"),
                    TestDataHelper.getTestData("Fehler-Email") + ","
            );
    
            Assert.assertNull(
                    TestDataHelper.getTestData("Fehler-Dienstleistung"),
                    TestDataHelper.getTestData("Fehler-Dienstleistung") + ","
            );
        }
    
        if (!newAppointmentNumber.get().isEmpty()) {
            TestDataHelper.setTestData("new_appointment_number", newAppointmentNumber.get());
        }
    }

    /**
     * Fills {@code customTextfield} / {@code customTextfield2} when the location enables them
     * (e.g. required "Zusätzliche Bemerkungen" on Ruppertstraße). No-op if those text areas are absent.
     */
    public void fillCustomTextfieldsForSpontaneousCustomerIfNeeded() {
        ScenarioLogManager.getLogger().info("Filling scope custom text fields on spontaneous customer form if present...");
        CONTEXT.set();
        CONTEXT.waitForSpinners();
        for (String fieldName : new String[] { "customTextfield", "customTextfield2" }) {
            List<WebElement> found = DRIVER.findElements(By.xpath("//textarea[@name='" + fieldName + "']"));
            if (found.isEmpty()) {
                continue;
            }
            WebElement textarea = found.get(0);
            if (!textarea.isDisplayed()) {
                continue;
            }
            String current = textarea.getAttribute("value");
            if (current == null || current.isBlank()) {
                current = textarea.getText();
            }
            if (current != null && !current.isBlank()) {
                continue;
            }
            String text = "ATAF UI-Test " + fieldName;
            enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, text, textarea);
            TestDataHelper.setTestData("spontaneous_" + fieldName, text);
        }
    }

    public void fillCustomTextfield(String fieldName, String value) {
        if (value == null || value.isBlank()) {
            return;
        }
        CONTEXT.set();
        WebElement textarea = findElementByLocatorType("//textarea[@name='" + fieldName + "']", LocatorType.XPATH, true);
        enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, value, textarea);
    }

    public String clickOnAddSpontaneousCustomer() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Add spontaneous customer\"  button...");
        fillCustomTextfieldsForSpontaneousCustomerIfNeeded();
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME * 2, "//button[text()='Spontankunden hinzufügen']", LocatorType.XPATH, false, CONTEXT);
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME * 2, "//h2[text()='Spontankunde wurde erfolgreich eingetragen']", LocatorType.XPATH, false, CONTEXT),
                "Click on \"Add spontaneous customer\"  button has failed! Success message is not displayed!");
        Pattern appointmentNumberPattern = Pattern.compile("^Termin-Nr\\.\\s*([A-Za-z]*[0-9]+).*");
        Matcher appointmentNumberMatcher = appointmentNumberPattern.matcher(
                getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME * 2, "//dt[starts-with(normalize-space(), 'Termin-Nr.')]", LocatorType.XPATH, CONTEXT));
        Assert.assertTrue(appointmentNumberMatcher.find(), "Click on \"Add spontaneous customer\"  button has failed! Waiting number is not displayed!");
        TestDataHelper.setTestData("new_waiting_number", appointmentNumberMatcher.group(1));
        return appointmentNumberMatcher.group(1);
    }

    public void clickOnEditProcessButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"edit process\" button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[contains(text(),'Termin bearbeiten')]", LocatorType.XPATH, false, CONTEXT);
    }

    public void checkProcessEditFormIsVisible() {
        ScenarioLogManager.getLogger().info("Checking if process edit form is visible...");
        CONTEXT.waitForSpinners();

        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//input[@id='process_date']", LocatorType.XPATH, false, CONTEXT), "Process edit form is not visible!");

        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.withMessage("The expected walk-in customer was not loaded into the edit form!");

        wait.until(ExpectedConditions.textToBePresentInElementValue(By.xpath("//input[@name='familyName']"), TestDataHelper.getTestData("customer_name")));
    }

    public void checkAppointmentEditFormIsVisible() {
        ScenarioLogManager.getLogger().info("Checking if appointment edit form is visible...");
        CONTEXT.waitForSpinners();

        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//input[@id='process_date']", LocatorType.XPATH, false, CONTEXT), "Appointment edit form is not visible!");

        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.withMessage("The expected appointment customer was not loaded into the edit form!");

        wait.until(ExpectedConditions.textToBePresentInElementValue(By.xpath("//input[@name='familyName']"), TestDataHelper.getTestData("new_appointment_customer_name"))); 
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
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[contains(text(),'Wartenummer drucken')]", LocatorType.XPATH, false, CONTEXT);
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
    
        CONTEXT.waitForSpinners();
    
        final By headerToggle = By.id("finished-appointments-control");          // <h2 id="finished-appointments-control">
        final By finishedTable = By.id(APPOINTMENT_FINISHED_TABLE_LOCATOR_ID);   // "table-finished-appointments"
    
        WebDriverWait wait = new WebDriverWait(DRIVER, java.time.Duration.ofSeconds(20));
    
        // Ensure the header control is visible and on screen
        org.openqa.selenium.WebElement header =
                wait.until(org.openqa.selenium.support.ui.ExpectedConditions.visibilityOfElementLocated(headerToggle));
        scrollToCenterByVisibleElement(header);
    
        // If table is already visible, nothing to do
        if (isElementVisible(finishedTable, 2)) {
            return;
        }
    
        // Toggle open and wait; retry once if needed (accounts for animation/rerenders)
        for (int attempt = 0; attempt < 2; attempt++) {
            header.click();
            try {
                // brief settle for animation
                Thread.sleep(200);
            } catch (InterruptedException ie) {
                Thread.currentThread().interrupt();
            }
            CONTEXT.waitForSpinners();
            try {
                wait.until(org.openqa.selenium.support.ui.ExpectedConditions
                        .visibilityOfElementLocated(finishedTable));
                return; // opened successfully
            } catch (org.openqa.selenium.TimeoutException e) {
                if (attempt == 0) {
                    // re-fetch header and try once more
                    header = DRIVER.findElement(headerToggle);
                } else {
                    org.testng.Assert.fail("Finished appointments table did not become visible after toggling.");
                }
            }
        }
    }

    private boolean isElementVisible(By locator, int timeoutSeconds) {
        try {
            new org.openqa.selenium.support.ui.WebDriverWait(DRIVER, java.time.Duration.ofSeconds(timeoutSeconds))
                .until(org.openqa.selenium.support.ui.ExpectedConditions.visibilityOfElementLocated(locator));
            return true;
        } catch (org.openqa.selenium.TimeoutException e) {
            return false;
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
        // Columns: 6=Wartezeit, 7=Wegezeit, 8=Bearbeitungszeit (Wegezeit added in ZMSKVR-1250)
        String xpath = "//table[@id='table-finished-appointments']//tr[td[position() = 3 and contains(., '" + customer + "')]]/td[8]";
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

    public void checkForValuesInQueueColumn(String column, String... searchStrings) {
        ScenarioLogManager.getLogger().info("Checking for values to be visible in '{}' column of the waiting list...", column);
        CONTEXT.waitForSpinners();
        scrollToCenterByVisibleElement(findElementByLocatorType(APPOINTMENT_QUEUE_TABLE_LOCATOR_ID, LocatorType.ID, false));
        areValuesVisibleInTableColumn(APPOINTMENT_QUEUE_TABLE_LOCATOR_ID, LocatorType.ID, column, searchStrings);
    }

    public void isQueueEmpty() {
        ScenarioLogManager.getLogger().info("Checking for the queue to be empty...");
        CONTEXT.waitForSpinners();
        boolean isQueueInvisible = isWebElementInvisible(DEFAULT_EXPLICIT_WAIT_TIME, APPOINTMENT_QUEUE_TABLE_LOCATOR_ID, LocatorType.ID);
        Assert.assertTrue(isQueueInvisible, "Queue is not empty: The appointment queue table is still visible.");
    }

    public void checkForValuesInParkingTableColumn(String column, String... searchStrings) {
        ScenarioLogManager.getLogger().info("Checking for values to be visible in '{}' column of the parking table...", column);
        CONTEXT.waitForSpinners();
        scrollToCenterByVisibleElement(findElementByLocatorType(APPOINTMENT_PARKED_TABLE_LOCATOR_ID, LocatorType.ID, false));
        areValuesVisibleInTableColumn(APPOINTMENT_PARKED_TABLE_LOCATOR_ID, LocatorType.ID, column, searchStrings);
    }

    public void checkForValuesInFinishedTableColumn(String column, String... searchStrings) {
        ScenarioLogManager.getLogger().info("Checking for values to be visible in '{}' column of the finished table...", column);
        CONTEXT.waitForSpinners();
        scrollToCenterByVisibleElement(findElementByLocatorType(APPOINTMENT_FINISHED_TABLE_LOCATOR_ID, LocatorType.ID, false));
        areValuesVisibleInTableColumn(APPOINTMENT_FINISHED_TABLE_LOCATOR_ID, LocatorType.ID, column, searchStrings);
    }

    private static final By WAITING_CLIENTS_EFFECTIVE =
            By.cssSelector("span.waiting-count[data-waiting-clients-effective]");

    /** Visible Wartende count. Does not reload the page. */
    public int readWaitingClientsEffective() {
        CONTEXT.set();
        WebElement count = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(WAITING_CLIENTS_EFFECTIVE));
        String text = count.getText().trim();
        try {
            return Integer.parseInt(text);
        } catch (NumberFormatException e) {
            throw new AssertionError("Wartende count is not a number: \"" + text + "\"", e);
        }
    }

    /**
     * The workstation reloads the queue itself every 60 seconds and copies the new count into Wartende.
     * Poll the number already on the page. Do not call refresh.
     */
    public void waitUntilWaitingClientsEffectiveAtLeast(int expected, int timeoutSeconds) {
        CONTEXT.set();
        long deadline = System.currentTimeMillis() + timeoutSeconds * 1000L;
        int latest = -1;
        while (System.currentTimeMillis() < deadline) {
            try {
                latest = Integer.parseInt(DRIVER.findElement(WAITING_CLIENTS_EFFECTIVE).getText().trim());
                if (latest >= expected) {
                    ScenarioLogManager.getLogger()
                            .info("Wartende reached {} (expected at least {}) without a page reload", latest, expected);
                    return;
                }
            } catch (StaleElementReferenceException | NumberFormatException ignored) {
                // The queue partial is being replaced by its own reload.
            }
            try {
                Thread.sleep(1000L);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
                break;
            }
        }
        Assert.fail("Wartende stayed at " + latest + ". Expected at least " + expected
                + " within " + timeoutSeconds + " seconds, without reloading the page.");
    }

    public void checkForValuesInMissedTableColumn(String column, String... searchStrings) {
        ScenarioLogManager.getLogger().info("Checking for values to be visible in '{}' column of the missed table...", column);
        CONTEXT.waitForSpinners();
        scrollToCenterByVisibleElement(findElementByLocatorType(APPOINTMENT_MISSED_TABLE_LOCATOR_ID, LocatorType.ID, false));
        areValuesVisibleInTableColumn(APPOINTMENT_MISSED_TABLE_LOCATOR_ID, LocatorType.ID, column, searchStrings);

    }

    private WebElement queueBoard() {
        CONTEXT.set();
        return new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(".queue-table")));
    }

    /** Blue Terminübersicht bar above the tables. */
    private WebElement queueBar() {
        return queueBoard().findElement(By.cssSelector(".board__actions"));
    }

    public void assertQueueBarDateVisible() {
        WebElement date = queueBar().findElement(By.cssSelector("strong.date"));
        Assert.assertTrue(date.isDisplayed(), "Das Datum in der blauen Warteschlangenleiste ist nicht sichtbar.");
        Assert.assertFalse(date.getText().trim().isEmpty(), "Das Datum in der blauen Warteschlangenleiste ist leer.");
    }

    public void assertListenNeuLadenVisible() {
        WebElement reload = queueBar().findElement(By.cssSelector("a.reload"));
        Assert.assertTrue(reload.isDisplayed(), "Listen neu laden in der blauen Leiste ist nicht sichtbar.");
        Assert.assertEquals(
                reload.getAttribute("title"),
                "Listen neu laden",
                "Die Schaltfläche in der blauen Leiste ist nicht mit Listen neu laden beschriftet.");
    }

    /** The button under the queue tables, not the reload control in the blue bar. */
    public void assertWarteschlangeAktualisierenHidden() {
        Assert.assertTrue(
                queueBoard().findElements(By.cssSelector("button.button-reload")).isEmpty(),
                "Der Button Warteschlange aktualisieren unter der Warteschlange ist sichtbar.");
    }

    public void assertQueueBarDayNavigationHidden() {
        Assert.assertTrue(
                queueBar().findElements(By.cssSelector(".calendar-navigation")).isEmpty(),
                "Heute einschließlich Zurück- und Weiter-Pfeile ist in der Warteschlangenleiste sichtbar.");
    }

    public void assertSpontankundenEinblendenHidden() {
        Assert.assertTrue(
                queueBar().findElements(By.xpath(".//label[contains(.,'Spontankunden einblenden')]")).isEmpty(),
                "Spontankunden einblenden ist in der Warteschlangenleiste sichtbar.");
    }

    public void assertQueueDownloadHidden() {
        Assert.assertTrue(
                queueBar().findElements(By.cssSelector("a.download")).isEmpty(),
                "Der Download der Warteschlange ist sichtbar.");
    }

    public void assertQueuePrintHidden() {
        Assert.assertTrue(
                queueBar().findElements(By.cssSelector("a.print")).isEmpty(),
                "Die Druckfunktion der Warteschlange ist sichtbar.");
    }

    public void assertClusterScopeDropdownVisible() {
        WebElement dropdown = queueBar().findElement(By.cssSelector(".switchcluster select[name='scope']"));
        Assert.assertTrue(dropdown.isDisplayed(), "Das Standort-Dropdown in der blauen Leiste ist nicht sichtbar.");
    }

    /**
     * The Auswahlliste label is "name (45 min)". Selecting the service hides that row and moves
     * the plain name to the Abwahlliste, so the label is read from text content.
     * Termindauer then shows the same number. The broken mapping showed 135.
     */
    public void assertAppointmentFormDuration(String serviceFragment, int minutes, int wrongMinutes) {
        CONTEXT.set();
        CONTEXT.waitForSpinners();
        By label = By.xpath(
                "//ul[@aria-label='Dienstleistungen Auswahlliste']//span[contains(.,'" + serviceFragment + "')]");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement span = wait.until(ExpectedConditions.presenceOfElementLocated(label));
        String text = String.valueOf(((JavascriptExecutor) DRIVER)
                        .executeScript("return (arguments[0].textContent || '').replace(/\\s+/g, ' ').trim();", span));
        Assert.assertTrue(
                text.contains("(" + minutes + " min)"),
                "Expected (" + minutes + " min) on the service. actual=" + text);
        Assert.assertFalse(
                text.contains("(" + wrongMinutes + " min)"),
                "Service duration must not be (" + wrongMinutes + " min). actual=" + text);
        WebElement slotCount = wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("appointmentForm_slotCount")));
        String selected = new Select(slotCount).getFirstSelectedOption().getText().trim();
        Assert.assertEquals(selected, String.valueOf(minutes), "Termindauer select. actual=" + selected);
        List<WebElement> dates = DRIVER.findElements(By.id("process_selected_date"));
        if (!dates.isEmpty()) {
            String iso = dates.get(0).getAttribute("value");
            if (iso != null && !iso.isBlank()) {
                TestDataHelper.setTestData("new_appointment_iso_date", iso);
            }
        }
    }

    /** Gesamtübersicht cell for the booked number. The title is "HH:mm – HH:mm". */
    public void assertOverallCalendarAppointmentSpansMinutes(int minutes) {
        CONTEXT.set();
        CONTEXT.waitForSpinners();
        String number = TestDataHelper.getTestData("new_appointment_number");
        Assert.assertNotNull(number, "No booked appointment number for the Gesamtübersicht.");
        String iso = TestDataHelper.getTestData("new_appointment_iso_date");
        if (iso == null || iso.isBlank()) {
            String german = TestDataHelper.getTestData("new_appointment_date");
            if (german != null && !german.isBlank()) {
                iso = LocalDate.parse(german, DateTimeFormatter.ofPattern("dd.MM.yyyy", Locale.GERMAN))
                        .format(DateTimeFormatter.ISO_LOCAL_DATE);
            } else {
                iso = BerlinTime.today().format(DateTimeFormatter.ISO_LOCAL_DATE);
            }
        }
        WebElement from = findElementByLocatorType("calendar-date-from", LocatorType.ID, false);
        WebElement until = findElementByLocatorType("calendar-date-until", LocatorType.ID, false);
        ((JavascriptExecutor) DRIVER).executeScript(
                "arguments[0].value=arguments[2]; arguments[1].value=arguments[2];", from, until, iso);
        Select scopes = new Select(findElementByLocatorType("scope-select", LocatorType.ID, false));
        scopes.deselectAll();
        scopes.selectByValue("319");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[normalize-space()='Übernehmen']", LocatorType.XPATH, false, CONTEXT);
        By cellLabel = By.xpath(
                "//span[contains(@class,'overall-calendar-termin-label') and normalize-space(.)='" + number + "']");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement label = wait.until(ExpectedConditions.visibilityOfElementLocated(cellLabel));
        WebElement cell = label.findElement(By.xpath("./parent::*"));
        String title = cell.getAttribute("title");
        Assert.assertNotNull(title, "Gesamtübersicht cell for " + number + " has no time title.");
        Matcher matcher = Pattern.compile("(\\d{2}:\\d{2})\\s*[–-]\\s*(\\d{2}:\\d{2})").matcher(title);
        Assert.assertTrue(matcher.find(), "Could not read the appointment span from \"" + title + "\".");
        int span = (int) java.time.Duration.between(LocalTime.parse(matcher.group(1)), LocalTime.parse(matcher.group(2)))
                .toMinutes();
        Assert.assertEquals(span, minutes, "Gesamtübersicht span for " + number + " from title \"" + title + "\".");
    }

    private String overallViewDay;
    private String overallViewLocation;
    private int overallViewScopeId;

    /**
     * One location, one day. The day is the date already offered as "Von", so it stays inside the
     * calendar's allowed range.
     */
    public void showLocationInOverallViewForOneDay(String locationName, int scopeId) {
        CONTEXT.set();
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement from = wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("calendar-date-from")));
        String date = from.getAttribute("value");
        if (date == null || date.isBlank()) {
            throw new IllegalStateException("The overall view has no start date.");
        }
        overallViewDay = date;
        overallViewLocation = locationName;
        overallViewScopeId = scopeId;
        WebElement until = DRIVER.findElement(By.id("calendar-date-until"));
        ((JavascriptExecutor) DRIVER).executeScript(
                "arguments[0].value=arguments[2]; arguments[1].value=arguments[2];", from, until, date);
        Select scopes = new Select(wait.until(ExpectedConditions.presenceOfElementLocated(By.id("scope-select"))));
        scopes.deselectAll();
        scopes.selectByValue(Integer.toString(scopeId));
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[normalize-space()='Übernehmen']", LocatorType.XPATH, false, CONTEXT);
    }

    public void assertOverallViewLinksLocationToOpeningHours(String linkLabel) {
        CONTEXT.set();
        if (overallViewDay == null || overallViewLocation == null || overallViewScopeId <= 0) {
            throw new IllegalStateException("The overall view day and location were not chosen.");
        }
        String locationName = overallViewLocation;
        int scopeId = overallViewScopeId;
        String hrefPart = "/scope/" + scopeId + "/availability/day/" + overallViewDay + "/";
        By linkBy = By.xpath(
                "//*[contains(@class,'overall-calendar-scope-header')]"
                        + "[.//*[contains(@class,'overall-calendar-scope-name') and contains(normalize-space(.),\""
                        + locationName + "\")]]"
                        + "//a[@title=\"" + linkLabel + "\"]");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement link = wait.until(ExpectedConditions.presenceOfElementLocated(linkBy));
        String href = link.getAttribute("href");
        Assert.assertNotNull(href, "The opening-hours link has no address.");
        Assert.assertTrue(href.contains(hrefPart),
                "The opening-hours link should point at " + hrefPart + " but was " + href);
        Assert.assertEquals(link.getAttribute("target"), "_blank",
                "The opening hours should open in a new tab.");
        Assert.assertEquals(link.getAttribute("title"), linkLabel,
                "The link label should say where it leads.");
        Assert.assertFalse(link.findElements(By.cssSelector("i.fa-clock")).isEmpty(),
                "The opening-hours link should show the clock icon.");
        String dayPart = LocalDate.parse(overallViewDay).format(DateTimeFormatter.ofPattern("dd.MM."));
        WebElement dayLabel = DRIVER.findElement(By.cssSelector(".overall-calendar-day-label"));
        Assert.assertTrue(dayLabel.getText().contains(dayPart),
                "The day header should show " + dayPart + " but was \"" + dayLabel.getText() + "\".");
    }

    /**
     * A walk-in window that is outside the appointment hours of the same opening.
     * {@code gapEnd} is exclusive. {@code appointmentStart} is a time that must still be shown.
     */
    public static final class WalkInOpening {
        public final int scopeId;
        public final LocalDate day;
        public final LocalTime gapStart;
        public final LocalTime gapEnd;
        public final LocalTime appointmentStart;

        public WalkInOpening(int scopeId, LocalDate day, LocalTime gapStart, LocalTime gapEnd, LocalTime appointmentStart) {
            this.scopeId = scopeId;
            this.day = day;
            this.gapStart = gapStart;
            this.gapEnd = gapEnd;
            this.appointmentStart = appointmentStart;
        }
    }

    private LocalDate overallViewStart;
    private WalkInOpening walkInOpening;

    public LocalDate overallViewStart() {
        if (overallViewStart == null) {
            throw new IllegalStateException("The overall view has no start date.");
        }
        return overallViewStart;
    }

    public List<String> overallViewScopeOptionValues() {
        Select scopes = new Select(DRIVER.findElement(By.id("scope-select")));
        return scopes.getOptions().stream().map(option -> option.getAttribute("value")).collect(Collectors.toList());
    }

    public void showEveryLocationInOverallView(int days) {
        CONTEXT.set();
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(90));
        WebElement toggle = wait.until(ExpectedConditions.elementToBeClickable(By.id("select-all-scopes")));
        toggle.click();
        WebElement from = DRIVER.findElement(By.id("calendar-date-from"));
        String startValue = from.getAttribute("value");
        if (startValue == null || startValue.isBlank()) {
            throw new IllegalStateException("The overall view has no start date.");
        }
        overallViewStart = LocalDate.parse(startValue);
        LocalDate until = overallViewStart.plusDays(days - 1L);
        WebElement untilInput = DRIVER.findElement(By.id("calendar-date-until"));
        ((JavascriptExecutor) DRIVER).executeScript(
                "arguments[0].value=arguments[1]; arguments[2].value=arguments[3];",
                from, startValue, untilInput, until.toString());
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[normalize-space()='Übernehmen']", LocatorType.XPATH, false, CONTEXT);
        wait.until(ExpectedConditions.numberOfElementsToBeMoreThan(By.cssSelector(".overall-calendar-day-label"), 1));
        wait.until(ExpectedConditions.numberOfElementsToBeMoreThan(By.cssSelector(".overall-calendar-scope-header"), 0));
    }

    public void openOverallViewFullScreen() {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement button = wait.until(ExpectedConditions.elementToBeClickable(By.id("calendar-fullscreen")));
        button.click();
        wait.until(ExpectedConditions.attributeContains(
                By.cssSelector(".overall-calendar-wrapper"), "class", "fullscreen"));
    }

    public void scrollOverallViewToTheRight() {
        Object scrolled = ((JavascriptExecutor) DRIVER).executeScript(
                "var wrapper = document.querySelector('.overall-calendar-wrapper');"
                        + "wrapper.scrollLeft = wrapper.scrollWidth;"
                        + "return wrapper.scrollLeft;");
        Assert.assertTrue(scrolled instanceof Number && ((Number) scrolled).intValue() > 0,
                "The overall view did not scroll sideways.");
    }

    public void assertEachVisibleLocationShowsItsDate() {
        Object result = ((JavascriptExecutor) DRIVER).executeScript(
                "var wrapper = document.querySelector('.overall-calendar-wrapper');"
                        + "var view = wrapper.getBoundingClientRect();"
                        + "function span(el) {"
                        + "  var parts = el.style.gridColumn.split('/');"
                        + "  var start = parseInt(parts[0], 10);"
                        + "  var width = parseInt(parts[1].replace('span', ''), 10);"
                        + "  return [start, start + width - 1];"
                        + "}"
                        + "var days = Array.from(document.querySelectorAll('.overall-calendar-day-header'));"
                        + "var scopes = Array.from(document.querySelectorAll('.overall-calendar-scope-header'));"
                        + "if (scopes.length === 0) return 'no locations';"
                        + "var seen = 0;"
                        + "for (var i = 0; i < scopes.length; i++) {"
                        + "  var box = scopes[i].getBoundingClientRect();"
                        + "  if (box.right < view.left || box.left > view.right || box.width === 0) continue;"
                        + "  seen++;"
                        + "  var place = span(scopes[i]);"
                        + "  var day = null;"
                        + "  for (var d = 0; d < days.length; d++) {"
                        + "    var dayPlace = span(days[d]);"
                        + "    if (place[0] >= dayPlace[0] && place[1] <= dayPlace[1]) day = days[d];"
                        + "  }"
                        + "  if (!day) return 'location without a day';"
                        + "  var label = day.querySelector('.overall-calendar-day-label');"
                        + "  if (!label || !/\\d{2}\\.\\d{2}\\./.test(label.textContent)) return 'day has no date';"
                        + "  var labelBox = label.getBoundingClientRect();"
                        + "  if (labelBox.right < view.left || labelBox.left > view.right || labelBox.width < 2) {"
                        + "    return 'date not in view: ' + label.textContent;"
                        + "  }"
                        + "}"
                        + "return seen > 0 ? 'ok' : 'no location in view';");
        Assert.assertEquals(String.valueOf(result), "ok", "A shown location has no readable date.");
    }

    public void assertOverallViewHasNoAxisLabels(String rowLabel, String columnLabel) {
        List<WebElement> corners = DRIVER.findElements(By.cssSelector("#overall-calendar .overall-calendar-empty-header"));
        Assert.assertEquals(corners.size(), 2, "The overall view should have two empty corner cells.");
        for (WebElement corner : corners) {
            String text = corner.getText().trim();
            Assert.assertNotEquals(text, rowLabel, "The row label should be gone.");
            Assert.assertNotEquals(text, columnLabel, "The column label should be gone.");
            Assert.assertTrue(text.isEmpty(), "The corner cell should be empty but was \"" + text + "\".");
        }
    }

    public void assertHourLabelSitsOnTheHourRow() {
        Object result = ((JavascriptExecutor) DRIVER).executeScript(
                "var label = document.querySelector('.overall-calendar-time-hour .overall-calendar-time-label');"
                        + "if (!label || !/^\\d{2}:00$/.test(label.textContent.trim())) return 'missing hour label';"
                        + "var row = label.parentElement.style.gridRow.split('/')[0].trim();"
                        + "var stripes = Array.from(document.querySelectorAll('.overall-calendar-stripe-hour'));"
                        + "var stripe = stripes.find(function (item) {"
                        + "  return item.style.gridRow.split('/')[0].trim() === row;"
                        + "});"
                        + "if (!stripe) return 'missing hour line';"
                        + "var labelBox = label.getBoundingClientRect();"
                        + "var lineBox = stripe.getBoundingClientRect();"
                        + "var labelMiddle = labelBox.top + labelBox.height / 2;"
                        + "var lineMiddle = lineBox.top + lineBox.height / 2;"
                        + "if (labelMiddle <= lineMiddle) return 'the hour label sits on the hour line';"
                        + "return 'ok';");
        Assert.assertEquals(String.valueOf(result), "ok", "The hour label should sit on the first row of the hour.");
    }

    public void assertDayLinesKeepOneWidth() {
        Object result = ((JavascriptExecutor) DRIVER).executeScript(
                "var widths = Array.from(document.querySelectorAll('.overall-calendar-day-separator')).map(function (line) {"
                        + "  return Math.round(line.getBoundingClientRect().width);"
                        + "});"
                        + "if (widths.length < 2) return 'not enough day lines';"
                        + "var same = widths.every(function (width) { return width === widths[0] && width >= 3; });"
                        + "if (!same) return 'day line widths ' + widths.slice(0, 6).join(',');"
                        + "function sideBorder(selector) {"
                        + "  var style = getComputedStyle(document.querySelector(selector));"
                        + "  return style.borderLeftWidth === '0px' && style.borderRightWidth === '0px';"
                        + "}"
                        + "if (!sideBorder('.overall-calendar-scope-header')) return 'location header has a side border';"
                        + "if (!sideBorder('.overall-calendar-day-header')) return 'day header has a side border';"
                        + "return 'ok';");
        Assert.assertEquals(String.valueOf(result), "ok", "The day lines should keep one width.");
    }

    public void showWalkInOpening(WalkInOpening opening) {
        CONTEXT.set();
        walkInOpening = opening;
        WebElement fullscreen = DRIVER.findElement(By.id("calendar-fullscreen"));
        if (DRIVER.findElement(By.cssSelector(".overall-calendar-wrapper")).getAttribute("class").contains("fullscreen")) {
            fullscreen.click();
        }
        WebElement from = DRIVER.findElement(By.id("calendar-date-from"));
        WebElement until = DRIVER.findElement(By.id("calendar-date-until"));
        String day = opening.day.toString();
        ((JavascriptExecutor) DRIVER).executeScript(
                "arguments[0].value=arguments[2]; arguments[1].value=arguments[2];", from, until, day);
        Select scopes = new Select(DRIVER.findElement(By.id("scope-select")));
        scopes.deselectAll();
        scopes.selectByValue(Integer.toString(opening.scopeId));
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[normalize-space()='Übernehmen']", LocatorType.XPATH, false, CONTEXT);
        String dayPart = opening.day.format(DateTimeFormatter.ofPattern("dd.MM."));
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .ignoring(StaleElementReferenceException.class)
                .until(driver -> driver.findElements(By.cssSelector(".overall-calendar-scope-header")).size() == 1
                        && driver.findElements(By.cssSelector(".overall-calendar-day-label")).size() == 1
                        && driver.findElement(By.cssSelector(".overall-calendar-day-label")).getText().contains(dayPart)
                        && !driver.findElements(By.cssSelector("#overall-calendar .overall-calendar-open")).isEmpty());
    }

    public void assertWalkInHoursAreNotWhite() {
        if (walkInOpening == null) {
            throw new IllegalStateException("No walk-in opening was chosen.");
        }
        DateTimeFormatter clock = DateTimeFormatter.ofPattern("HH:mm");
        String appointment = walkInOpening.appointmentStart.format(clock);
        boolean appointmentShown = false;
        Object titles = ((JavascriptExecutor) DRIVER).executeScript(
                "return Array.from(document.querySelectorAll('#overall-calendar .overall-calendar-open')).map(function (cell) {"
                        + "return cell.getAttribute('title') || '';"
                        + "});");
        if (!(titles instanceof List<?>)) {
            throw new IllegalStateException("The overall view returned no opening cells.");
        }
        for (Object titleObject : (List<?>) titles) {
            String title = String.valueOf(titleObject);
            Matcher matcher = Pattern.compile("(\\d{2}:\\d{2})\\s*[–-]\\s*(\\d{2}:\\d{2})").matcher(title);
            if (!matcher.find()) {
                continue;
            }
            LocalTime start = LocalTime.parse(matcher.group(1));
            if (!start.isBefore(walkInOpening.gapStart) && start.isBefore(walkInOpening.gapEnd)) {
                Assert.fail("Walk-in time " + title + " is shown in white.");
            }
            if (matcher.group(1).equals(appointment)) {
                appointmentShown = true;
            }
        }
        Assert.assertTrue(appointmentShown,
                "The appointment hour " + appointment + " should still be shown for scope " + walkInOpening.scopeId + ".");
    }
}
