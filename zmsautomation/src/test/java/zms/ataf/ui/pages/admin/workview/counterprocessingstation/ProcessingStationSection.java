package zms.ataf.ui.pages.admin.workview.counterprocessingstation;

import java.time.Duration;
import java.util.List;
import java.util.concurrent.atomic.AtomicReference;

import org.openqa.selenium.By;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedCondition;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.helpers.TestPropertiesHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.core.properties.DefaultValues;
import ataf.web.model.LocatorType;
import zms.ataf.ui.pages.admin.AdminPageContext;

/**
 * Sachbearbeiterplatz URL: /terminvereinbarung/admin/workstation/
 */
public class ProcessingStationSection extends CounterProcessingStationPage {

    public ProcessingStationSection(RemoteWebDriver driver, AdminPageContext adminPageContext) {
        super(driver, adminPageContext);
    }

    public void callNextCustomer() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Aufruf nächster Kunde\" button...");
        CONTEXT.set();
        final String CALL_NEXT_CUSTOMER_BUTTON_LOCATOR_XPATH = "//button[@title='Nächsten Kunden aufrufen']";
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, CALL_NEXT_CUSTOMER_BUTTON_LOCATOR_XPATH, LocatorType.XPATH, true, CONTEXT),
                "Button 'Nächsten Kunden aufrufen' is not visible!");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, CALL_NEXT_CUSTOMER_BUTTON_LOCATOR_XPATH, LocatorType.XPATH, false, CONTEXT);
    }

    public void confirmCustomerCall() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Ja, Kunden jetzt aufrufen\" button...");
        final String PRECALL_CUSTOMER_BUTTON_LOCATOR_XPATH = "//button[text()='Ja, Kunden jetzt aufrufen' and contains(@class, 'client-precall_button-success')]";
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, PRECALL_CUSTOMER_BUTTON_LOCATOR_XPATH, LocatorType.XPATH, true),
                "Button 'Ja, Kunden jetzt aufrufen' is not visible!");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, PRECALL_CUSTOMER_BUTTON_LOCATOR_XPATH, LocatorType.XPATH, false);
    }

    public void callCustomerWithSpecificNote(String note) {
        ScenarioLogManager.getLogger().info("Trying to call customer with note \"" + note + "\"...");
        final String CUSTOMER_NR_USING_NOTE_XPATH = "//tr[td[contains(., '" + note + "')]]/td[@class='callnextclient']/a";
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, CUSTOMER_NR_USING_NOTE_XPATH, LocatorType.XPATH, true, CONTEXT),
                "Note: " + note + " is not visible!");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, CUSTOMER_NR_USING_NOTE_XPATH, LocatorType.XPATH, false, CONTEXT);
    }

    public void callCustomerFromQueueWithNumber(String number) {
        ScenarioLogManager.getLogger().info("Trying to call customer from queue with number \"" + number + "\"...");
        final String CUSTOMER_NR_USING_NUMBER_XPATH = "//table[@id='table-queued-appointments']/tbody/tr/td[3]/a[contains(text(), '" + number + "')]";
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, CUSTOMER_NR_USING_NUMBER_XPATH, LocatorType.XPATH, true, CONTEXT),
                "Number: " + number + " is not visible!");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, CUSTOMER_NR_USING_NUMBER_XPATH, LocatorType.XPATH, false, CONTEXT);
    }

    public void callCustomerFromQueueWithName(String name) {
        ScenarioLogManager.getLogger().info("Trying to call customer from queue with name \"" + name + "\"...");
        WebElement link = getTableCellElement("table-queued-appointments", LocatorType.ID, "Name (Aufrufe)", name);
        link.findElement(By.cssSelector("a")).click();
    }

    public void callCustomerFromParkingTableWithNumber(String number) {
        ScenarioLogManager.getLogger()
            .info("Trying to call customer from parking table with number \"" + number + "\"...");
    
        String numOnly = number == null ? "" : number.replaceAll("\\D+", "");
    
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(15));
    
        // Wait until parked table is visible
        By parkedTable = By.id("table-parked-appointments");
        wait.until(ExpectedConditions.visibilityOfElementLocated(parkedTable));
    
        // ✅ FIXED: removed invalid backticks from XPath
        By rowByNumber = By.xpath(
            "//table[@id='table-parked-appointments']//tbody/tr[.//td[normalize-space()='" 
            + numOnly + "']]"
        );
    
        // Wait until the specific row is present
        WebElement row = wait.until(
            ExpectedConditions.presenceOfElementLocated(rowByNumber)
        );
    
        // Scroll to row (helps avoid click interception)
        scrollToCenterByVisibleElement(row);
    
        // Re-locate row to avoid stale element after scroll/re-render
        row = DRIVER.findElement(rowByNumber);
    
        // Adjust selector if needed to target the exact call button
        WebElement callBtn = row.findElement(By.cssSelector("button, a"));
    
        // Use your existing wrapper for clicking
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, callBtn, false);
    }

    public void validateCustomerCall() {
        CONTEXT.waitForSpinners();
        ScenarioLogManager.getLogger().info("Trying to validate if the click on \"Aufruf nächster Kunde\" button was successful...");
        AtomicReference<String> errorMessage = new AtomicReference<>("");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        try {
            wait.until((ExpectedCondition<Boolean>) waitDriver -> {
                // wird angezeigt, wenn es aktuell keine wartenden Kunden gibt.
                boolean isThankingMessageVisible = !findElementsByLocatorType(
                        TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                        "//section[@class='dialog message' and @role='alert']/div[@class='message__body'][contains(text(), 'Vielen Dank für die fleißigen Aufrufe.')]",
                        LocatorType.XPATH).isEmpty();
                boolean isErrorMessageDisplayed = !findElementsByLocatorType(
                        TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME), ".message--error",
                        LocatorType.CSSSELECTOR).isEmpty();
                if (!isThankingMessageVisible && !isErrorMessageDisplayed) {
                    // Überprüfen, ob der Abschnitt, der anzeigt, dass ein Kunde aufgerufen wurde, sichtbar ist.
                    boolean isSectionVisible = !findElementsByLocatorType(
                            TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                            "//section[contains(@class, 'board client')]",
                            LocatorType.XPATH).isEmpty();
                    if (isSectionVisible) {
                        // Überprüfen, ob die Überschrift mit "Kundeninformationen" im aufgerufenen Kundenabschnitt sichtbar ist.
                        boolean isH2CustomerInfoVisible = !findElementsByLocatorType(
                                TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                                "//h2[contains(., 'Kundeninformationen')]", LocatorType.XPATH).isEmpty();
                        if (!isH2CustomerInfoVisible) {
                            // Den Test fehlschlagen lassen, wenn die Überschrift "Kundeninformationen" nicht sichtbar ist.
                            errorMessage.set("'Kundeninformationen' heading is not visible.");
                            return false;
                        } else {
                            String[] textsToCheck = { "Name", "Anliegen" }; // Felder Telefon und E-Mail entfernt da keine Pflichtfelder!
                            for (String text : textsToCheck) {
                                boolean isTextVisible = !findElementsByLocatorType(
                                        TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                                        "//section[contains(@class, 'board client')]//dt[contains(text(), '" + text + "')]", LocatorType.XPATH).isEmpty();
                                // Sicherstellen, dass jedes Detail sichtbar ist; andernfalls wird der Test mit einer Nachricht über das fehlende Detail fehlschlagen.
                                if (!isTextVisible) {
                                    errorMessage.set(text + " detail is not visible.");
                                    return false;
                                }
                            }
                            boolean timeSinceCustomerCall = findElementsByLocatorType(
                                    TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                                    "//section[contains(@class, 'board client')]//h4[contains(text(), 'Zeit seit Kundenaufruf')]", LocatorType.XPATH).isEmpty();
                            if (timeSinceCustomerCall) {
                                errorMessage.set("<h4>Zeit seit Kundenaufruf:</h4> is not visible.");
                                return false;
                            }
                        }
                    } else {
                        // Den Test fehlschlagen lassen, wenn weder die Nachricht noch der Abschnitt mit Kundeninformationen sichtbar sind.
                        errorMessage.set("Neither thanking message 'Vielen Dank für die fleißigen Aufrufe.' nor section for customer information are visible.");
                        return false;
                    }
                }
                if (isErrorMessageDisplayed) {
                    errorMessage.set(
                            getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "h2.message__heading.title", LocatorType.CSSSELECTOR) + ". " + getWebElementText(
                                    DEFAULT_EXPLICIT_WAIT_TIME, "div.message__body", LocatorType.CSSSELECTOR));
                }
                return true;
            });
        } catch (TimeoutException e) {
            Assert.fail(errorMessage.get(), e);
        }
        Assert.assertEquals(errorMessage.get(), "", errorMessage.get());
    }

    public void validateCustomerCallWithNumber(String number) {
        CONTEXT.waitForSpinners();
        ScenarioLogManager.getLogger().info("Trying to validate if customer with number '" + number + "' was successfully called...");
        AtomicReference<String> errorMessage = new AtomicReference<>("");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        try {
            wait.until((ExpectedCondition<Boolean>) waitDriver -> {
                boolean isThankingMessageVisible = !findElementsByLocatorType(
                        TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                        "//section[@class='dialog message' and @role='alert']/div[@class='message__body'][contains(text(), 'Vielen Dank für die fleißigen Aufrufe.')]",
                        LocatorType.XPATH).isEmpty();
                boolean isErrorMessageDisplayed = !findElementsByLocatorType(
                        TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME), ".message--error",
                        LocatorType.CSSSELECTOR).isEmpty();
                if (!isThankingMessageVisible && !isErrorMessageDisplayed) {
                    boolean isSectionVisible = !findElementsByLocatorType(
                            TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                            "//section[contains(@class, 'board client')]",
                            LocatorType.XPATH).isEmpty();
                    if (isSectionVisible) {
                        boolean isH2CustomerInfoVisible = !findElementsByLocatorType(
                                TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                                "//h2[contains(., 'Kundeninformationen')]", LocatorType.XPATH).isEmpty();
                        if (!isH2CustomerInfoVisible) {
                            errorMessage.set("'Kundeninformationen' heading is not visible.");
                            return false;
                        } else {
                            String[] textsToCheck = { "Name", "Anliegen" };
                            for (String text : textsToCheck) {
                                boolean isTextVisible = !findElementsByLocatorType(
                                        TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                                        "//section[contains(@class, 'board client')]//dt[contains(text(), '" + text + "')]", LocatorType.XPATH).isEmpty();
                                if (!isTextVisible) {
                                    errorMessage.set(text + " detail is not visible.");
                                    return false;
                                }
                                if (text.equals("Name")) {
                                    boolean isSpecificNameVisible = !findElementsByLocatorType(
                                            TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                                            "//section[contains(@class, 'board client')]//dt[contains(text(), 'Name')]/following-sibling::dd[1][contains(., '(Wartenr. " + number + ")')]",
                                            LocatorType.XPATH).isEmpty();
                                    if (!isSpecificNameVisible) {
                                        errorMessage.set("Specific 'Name' detail with '(Wartenr. " + number + ")' is not visible.");
                                        return false;
                                    }
                                }
                            }
                            boolean timeSinceCustomerCall = findElementsByLocatorType(
                                    TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                                    "//section[contains(@class, 'board client')]//h4[contains(text(), 'Zeit seit Kundenaufruf')]", LocatorType.XPATH).isEmpty();
                            if (timeSinceCustomerCall) {
                                errorMessage.set("<h4>Zeit seit Kundenaufruf:</h4> is not visible.");
                                return false;
                            }
                        }
                    } else {
                        errorMessage.set("Neither thanking message 'Vielen Dank für die fleißigen Aufrufe.' nor section for customer information are visible.");
                        return false;
                    }
                }
                if (isErrorMessageDisplayed) {
                    errorMessage.set(
                            getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "h2.message__heading.title", LocatorType.CSSSELECTOR) + ". " + getWebElementText(
                                    DEFAULT_EXPLICIT_WAIT_TIME, "div.message__body", LocatorType.CSSSELECTOR));
                }
                return true;
            });
        } catch (TimeoutException e) {
            Assert.fail(errorMessage.get(), e);
        }
        Assert.assertEquals(errorMessage.get(), "", errorMessage.get());
    }

    public void checkCustomerCallVisible() {
        ScenarioLogManager.getLogger().info("Checking if the 'Nächsten Kunden aufrufen' button is visible.");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//button[@title='Nächsten Kunden aufrufen']", LocatorType.XPATH, true),
                "'Nächsten Kunden aufrufen' button is not visible");
    }

    public void clickOnYesCustomerAppeared() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Ja, Kunde erschienen\" button...");
        final String locator = "//button[@type='button' and contains(@class, 'client-called_button-success') and text()='Ja, Kunde erschienen']";
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, locator, LocatorType.XPATH, true, CONTEXT),
                "Button 'Ja, Kunde erschienen' is not visible!");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, locator, LocatorType.XPATH, false, CONTEXT);
    }

    public void clickOnNoAndCallNextCustomer() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Nein, nächster Kunde bitte\" button...");
        final String locator = "//button[@type='button' and contains(@class, 'client-called_button-skip') and text()='Nein, nächster Kunde bitte']";
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, locator, LocatorType.XPATH, true),
                "Button 'Nein, nächster Kunde bitte' is not visible!");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, locator, LocatorType.XPATH, false);
    }

    public void clickOnNoCustomerDidNotAppear() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Nein, nicht erschienen\" button...");
        final String locator = "//button[@type='button' and contains(@class, 'client-called_button-abort') and text()='Nein, nicht erschienen']";
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, locator, LocatorType.XPATH, true), "Button 'Nein, nicht erschiene' is not visible!");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, locator, LocatorType.XPATH, false);
    }

    public void clickOnFinaliseAppointment() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Fertig stellen\" button...");
        final String locator = "//a[contains(@class, 'button-finish') and text()='Fertig stellen']";
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, locator, LocatorType.XPATH, true, CONTEXT),
                "Button 'Fertig stellen' is not visible!");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, locator, LocatorType.XPATH, false, CONTEXT);
    }

    public void clickOnParkAppointment() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Parken\" button...");
        final String locator = "//button[contains(@class, 'client-called_button-parked') and normalize-space(text())='Parken']";
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, locator, LocatorType.XPATH, true), "Button 'Parken' is not visible!");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, locator, LocatorType.XPATH, false);
    }

    public void clickOnCancelAppointment() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Abbrechen\" button...");
        final String locator = "//button[contains(@class, 'button-cancel') and normalize-space(text())='Abbrechen']";
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, locator, LocatorType.XPATH, true), "Button 'Parken' is not visible!");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, locator, LocatorType.XPATH, false);
    }

    public void clickOnForwardAppointment() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Weiterleiten\" button...");
        final String locator = "//a[contains(@class, 'button') and normalize-space(text())='Weiterleiten']";
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, locator, LocatorType.XPATH, true), "Button 'Parken' is not visible!");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, locator, LocatorType.XPATH, false);
    }

    public void clickOnAbortAppointment() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Abbruch\" button...");
        final String locator = "//button[@class='button button--destructive button--fullwidth button-cancel left' and text()='Abbruch']\n";
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, locator, LocatorType.XPATH, true), "Button 'Abbruch' is not visible!");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, locator, LocatorType.XPATH, false);
    }

    public void selectLocationForAppointmentForwarding(String location) {
        ScenarioLogManager.getLogger().info("Trying to select location for appointment forwarding...");
        String xpath = "//select[@name='location']";
        WebElement competentBody = findElementByLocatorType(xpath, LocatorType.XPATH, true);
        Assert.assertNotNull(competentBody, "Location dropdown element not found!");
        scrollToCenterByVisibleElement(competentBody);
        Select competentBodySelections = new Select(competentBody);
        List<WebElement> options = competentBodySelections.getOptions();
        boolean locationFound = false;
        for (WebElement option : options) {
            if (option.getText().equals(location)) {
                locationFound = true;
                break;
            }
        }
        Assert.assertTrue(locationFound, "Location '" + location + "' not found in dropdown!");

        competentBodySelections.selectByVisibleText(location);
    }

    public void enterNoteForAppointmentForwarding(String note) {
        ScenarioLogManager.getLogger().info("Trying to enter note for appointment forwarding...");
        String xpath = "//textarea[@name='amendment']";
        WebElement textarea = findElementByLocatorType(xpath, LocatorType.XPATH, true);
        Assert.assertNotNull(textarea, "Textarea 'Anmerkung' not found!");
        textarea.sendKeys(note);
    }

    public void submitForwardAppointment() {
        ScenarioLogManager.getLogger().info("Trying to click on 'Termin buchen' for appointment forwarding...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[contains(normalize-space(), 'Termin buchen')]", LocatorType.XPATH, false, CONTEXT);
    }

    public void checkForNoWaitingCustomersMessage() {
        ScenarioLogManager.getLogger().info("Check for the message 'Aktuell gibt es keine wartenden Kunden'...");
        String xpath = "//section[@class='dialog message' and @role='alert']/h2[@class='message__heading'][contains(text(), 'Aktuell gibt es keine wartenden Kunden')]";
        boolean isMessageVisible = isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, xpath, LocatorType.XPATH, false);
        Assert.assertTrue(isMessageVisible, "The expected message 'Aktuell gibt es keine wartenden Kunden' was not visible.");
    }

    public void checkForCustomerNameUnderCustomerInformation(String name) {
        ScenarioLogManager.getLogger().info("Checking if customer name match and is visible under 'Kundeninformation'...");
        String customerName = getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "//following-sibling::dt[contains(text(),'Name')]/following-sibling::dd[1]",
                LocatorType.XPATH, CONTEXT);
        Assert.assertTrue(customerName.contains(name),
                "Customer name is not correct! Expected: [" + name + "] but found [" + customerName.replaceAll("[\\r\\n]", "") + "]");
    }

    public void checkForWaitingNumberUnderCustomerInformation(String number) {
        ScenarioLogManager.getLogger().info("Checking if waiting number match and is visible under 'Kundeninformation'...");
        String waitingNumber = getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "//following-sibling::dt[contains(text(),'Name')]/following-sibling::dd[1]",
                LocatorType.XPATH, CONTEXT);
        Assert.assertTrue(waitingNumber.contains(number),
                "Waiting number is not correct! Expected: [" + number + "] but found [" + waitingNumber.replaceAll("[\\r\\n]", "") + "]");
    }

    public void checkForServiceUnderCustomerInformation(String service) {
        ScenarioLogManager.getLogger().info("Checking if service match and is visible under 'Kundeninformation'...");
        Assert.assertEquals(getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "//following-sibling::dt[contains(text(),'Anliegen')]/following-sibling::dd[1]/ul/li",
                LocatorType.XPATH, CONTEXT), service, "Service does not match expected value!");
    }

    public void checkForNoteUnderCustomerInformation(String expectedNote) {
        ScenarioLogManager.getLogger().info("Checking if 'Anmerkung' match and is visible under 'Kundeninformation'...");
        String note = getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "//following-sibling::dt[contains(text(),'Anmerkung')]/following-sibling::dd[1]",
                LocatorType.XPATH, CONTEXT);
        Assert.assertTrue(note.contains(expectedNote),
                "Note is not correct! Expected: [" + expectedNote + "] but found [" + note.replaceAll("[\\r\\n]", "") + "]");
    }

    public void checkForPhoneNumberUnderCustomerInformation(String expectedNumber) {
        ScenarioLogManager.getLogger().info("Checking if phone number match and is visible under 'Kundeninformation'...");
        String phone = getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "//following-sibling::dt[contains(text(),'Telefon')]/following-sibling::dd[1]",
                LocatorType.XPATH, CONTEXT);
        Assert.assertTrue(phone.contains(expectedNumber),
                "Phone number is not correct! Expected: [" + expectedNumber + "] but found [" + phone.replaceAll("[\\r\\n]", "") + "]");
    }

    public void checkForEmailUnderCustomerInformation(String expectedEmail) {
        ScenarioLogManager.getLogger().info("Checking if email match and is visible under 'Kundeninformation'...");
        String email = getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "//following-sibling::dt[contains(text(),'E-Mail')]/following-sibling::dd[1]",
                LocatorType.XPATH, CONTEXT);
        Assert.assertTrue(email.contains(expectedEmail),
                "Email is not correct! Expected: [" + expectedEmail + "] but found [" + email.replaceAll("[\\r\\n]", "") + "]");
    }

    public void checkForWaitingTimeUnderCustomerInformation() {
        ScenarioLogManager.getLogger().info("Checking if waiting time is visible under 'Kundeninformation'...");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//following-sibling::dt[contains(text(),'Wartezeit')]/following-sibling::dd[1]",
                LocatorType.XPATH, false));
    }

    public void checkForTimeSinceCustomerCallUnderCustomerInformation() {
        ScenarioLogManager.getLogger().info("Checking if time since customer call is visible under 'Kundeninformation'...");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "clock", LocatorType.ID, false));
    }

}
