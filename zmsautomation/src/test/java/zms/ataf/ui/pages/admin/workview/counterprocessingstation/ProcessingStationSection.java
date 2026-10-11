package zms.ataf.ui.pages.admin.workview.counterprocessingstation;

import java.time.Duration;
import java.util.List;
import java.util.concurrent.atomic.AtomicReference;

import org.openqa.selenium.By;
import org.openqa.selenium.ElementClickInterceptedException;
import org.openqa.selenium.StaleElementReferenceException;
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
import zms.ataf.helpers.AccountCheckout;
import zms.ataf.ui.pages.admin.AdminPageContext;

/**
 * Sachbearbeiterplatz URL: /terminvereinbarung/admin/workstation/
 */
public class ProcessingStationSection extends CounterProcessingStationPage {

    private static final String[] CUSTOMER_ACTION_LABELS = {"Fertig stellen", "Weiterleiten", "Parken", "Abbrechen"};

    public ProcessingStationSection(RemoteWebDriver driver, AdminPageContext adminPageContext) {
        super(driver, adminPageContext);
    }

    /**
     * Marks every customer already waiting at this Standort as not appeared.
     * A later "Aufruf nächster Kunde" then reaches the customers this scenario just added.
     */
    public void dismissCustomersAlreadyWaiting() {
        ScenarioLogManager.getLogger().info("Dismissing customers already waiting at this Standort...");
        final String emptyMessage =
                "//h2[contains(., 'Aktuell gibt es keine wartenden Kunden')]"
                        + " | //div[contains(@class,'message__body') and contains(., 'Vielen Dank für die fleißigen Aufrufe.')]";
        final String precall =
                "//button[text()='Ja, Kunden jetzt aufrufen' and contains(@class, 'client-precall_button-success')]";
        final String called = "//button[contains(@class,'client-called_button-success')]";
        for (int i = 0; i < 8; i++) {
            callNextCustomer();
            if (isWebElementVisible(5, emptyMessage, LocatorType.XPATH, false, CONTEXT)) {
                ScenarioLogManager.getLogger().info("No further customers were waiting.");
                return;
            }
            if (isWebElementVisible(5, precall, LocatorType.XPATH, false, CONTEXT)) {
                clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, precall, LocatorType.XPATH, false, CONTEXT);
            }
            if (!isWebElementVisible(15, called, LocatorType.XPATH, false, CONTEXT)) {
                ScenarioLogManager.getLogger().info("Next call did not open a customer; leaving the queue as it is.");
                return;
            }
            clickOnNoCustomerDidNotAppear();
        }
        CONTEXT.set();
        int stillWaiting = DRIVER.findElements(By.cssSelector("#table-queued-appointments tbody tr")).size();
        Assert.assertEquals(
                stillWaiting,
                0,
                "Customers were still waiting after dismissing eight of them.");
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
        final String PRECALL_CUSTOMER_BUTTON_LOCATOR_XPATH =
                "//button[text()='Ja, Kunden jetzt aufrufen' and contains(@class, 'client-precall_button-success')]";
        // zmsadmin's WorkstationProcessNext controller only renders the precall confirmation
        // (workstationProcessPreCall) when the next process has an Anmerkung; otherwise it
        // redirects straight to workstationProcessCalled, so the precall step is legitimately
        // skipped and the called view's "Ja, Kunde erschienen" button is shown instead.
        final String CALLED_VIEW_BUTTON_LOCATOR_XPATH = "//button[contains(@class,'client-called_button-success')]";
        final int settleSeconds = 15;
        if (isWebElementVisible(settleSeconds, PRECALL_CUSTOMER_BUTTON_LOCATOR_XPATH, LocatorType.XPATH, true)) {
            clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, PRECALL_CUSTOMER_BUTTON_LOCATOR_XPATH, LocatorType.XPATH, false);
            return;
        }
        if (isWebElementVisible(settleSeconds, CALLED_VIEW_BUTTON_LOCATOR_XPATH, LocatorType.XPATH, false)) {
            ScenarioLogManager.getLogger().info(
                    "Precall confirmation skipped by server (next process has no Anmerkung); already on called view.");
            return;
        }
        Assert.fail("Neither precall confirmation 'Ja, Kunden jetzt aufrufen' nor called view "
                + "'Ja, Kunde erschienen' became visible after clicking 'Aufruf nächster Kunde'.");
    }

    /**
     * Precall Kundeninformationen board (only via Aufruf nächster Kunde when the process has Anmerkung).
     */
    public void assertPrecallConfirmationVisible() {
        ScenarioLogManager.getLogger().info("Checking precall Kundeninformationen confirmation...");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.withMessage("Precall Kundeninformationen heading is not visible!");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.xpath(
                "//section[contains(@class,'client-precall')]//h2[contains(.,'Kundeninformationen')]"
                        + " | //*[contains(@class,'client-precall')]//*[contains(.,'Kundeninformationen')]"
        )));
        wait.withMessage("Precall question 'Möchten Sie den Kunden aufrufen?' is not visible!");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.xpath(
                "//*[contains(.,'Möchten Sie den Kunden aufrufen?')]"
        )));
        wait.withMessage("Precall button 'Ja, Kunden jetzt aufrufen' is not visible!");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.xpath(
                "//button[text()='Ja, Kunden jetzt aufrufen' and contains(@class, 'client-precall_button-success')]"
        )));
    }

    /**
     * After another clerk already called the process, confirming precall must not assign it here.
     * Wait for a settled failure signal (has_called_process, ProcessNotCallable, or client-next
     * after cancel fallback). Do not treat a blank mid-load panel as success.
     */
    public void assertPrecallConfirmDidNotTakeCustomer() {
        ScenarioLogManager.getLogger().info(
                "Checking that precall confirm did not take a customer already held elsewhere...");
        By alreadyCalled = By.xpath(
                "//section[contains(@class,'message--error')]//*[contains(text(),"
                        + "'Bitte schließen Sie den aktuellen Vorgang zuerst ab.')]");
        By notCallable = By.xpath(
                "//*[contains(@class,'exceptionData-headline') and contains(.,"
                        + "'Termin kann nicht aufgerufen oder bearbeitet werden')]");
        // client-next replaces client-precall after cancel fallback; not the queue-table call button.
        By settledClientNext = By.cssSelector("section.client-next, section.board.client-next");
        By calledSuccess = By.xpath("//button[contains(@class,'client-called_button-success')]");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.withMessage(
                "Expected has_called_process, ProcessNotCallable, or settled client-next after precall confirm!");
        wait.until(driver -> !driver.findElements(alreadyCalled).isEmpty()
                || !driver.findElements(notCallable).isEmpty()
                || !driver.findElements(settledClientNext).isEmpty());
        Assert.assertTrue(
                DRIVER.findElements(calledSuccess).isEmpty(),
                "UI must not show 'Ja, Kunde erschienen' after losing the precall race.");
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
        clickQueueCallLinkWhenReady(queueCallLinkByName(name));
    }

    /** After the no-show lockout ends, the bold lockout text is replaced by the call link. */
    public void waitUntilCustomerCallLinkVisible(String name) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.ignoring(StaleElementReferenceException.class);
        wait.withMessage("Call link for \"" + name + "\" did not return after ending the no-show lockout.");
        wait.until(driver -> driver.findElements(queueCallLinkByName(name)).stream()
                .anyMatch(WebElement::isDisplayed));
    }

    private static By queueCallLinkByName(String name) {
        // Repeat calls keep the count in a sibling <small>; the <a> text stays the family name.
        // During the five-minute no-show lockout there is no <a> — only bold text.
        return By.xpath(
                "//table[@id='table-queued-appointments']//td[contains(@class,'callnextclient')]"
                        + "//a[@title='Diesen Bürger aufrufen' and normalize-space(.)='" + name + "']");
    }

    /**
     * Queue / cluster refresh can cover the call link with {@code div.loader}. Static loader
     * nodes also sit in the page, so we retry on intercept instead of requiring every loader gone.
     */
    private void clickQueueCallLinkWhenReady(By callLink) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.ignoring(StaleElementReferenceException.class, ElementClickInterceptedException.class);
        wait.withMessage("Call link not clickable (lockout still on, or loader covering the row): " + callLink);
        wait.until(driver -> {
            List<WebElement> links = driver.findElements(callLink);
            for (WebElement link : links) {
                try {
                    if (!link.isDisplayed()) {
                        continue;
                    }
                    scrollToCenterByVisibleElement(link);
                    link.click();
                    return true;
                } catch (StaleElementReferenceException | ElementClickInterceptedException ignored) {
                    // Cluster / queue reload or loader covered the row — try again.
                }
            }
            return false;
        });
    }

    public void callCustomerFromParkingTableWithNumber(String number) {
        String numOnly = number == null ? "" : number.replaceAll("\\D+", "");
        By parkedTable = By.id("table-parked-appointments");
        WebDriverWait wait = new WebDriverWait(DRIVER, java.time.Duration.ofSeconds(20));
        wait.until(org.openqa.selenium.support.ui.ExpectedConditions.visibilityOfElementLocated(parkedTable));
    
        // Row anchored by 3rd column (Nr.)
        By rowByNr = By.xpath(
            "//table[@id='table-parked-appointments']" +
            "//tbody/tr[td[count(preceding-sibling::td)=2][normalize-space(.)='" + numOnly + "']]");
        org.openqa.selenium.WebElement row =
            wait.until(org.openqa.selenium.support.ui.ExpectedConditions.presenceOfElementLocated(rowByNr));
    
        scrollToCenterByVisibleElement(row);
    
        // Re-find inside the row to avoid stale element on re-render
        row = DRIVER.findElement(rowByNr);
        org.openqa.selenium.WebElement recall =
            row.findElement(org.openqa.selenium.By.cssSelector("a[title='wieder aufrufen']"));
    
        wait.until(org.openqa.selenium.support.ui.ExpectedConditions.elementToBeClickable(recall));
        scrollToCenterByVisibleElement(recall);
        recall.click();
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
        AccountCheckout.checkout("scope:" + location);
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

    public void assertCallOtherProcessConfirmDialogVisible() {
        ScenarioLogManager.getLogger().info("Checking confirm dialog for switching queue customer...");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.withMessage("Confirm dialog for switching queue customer is not visible!");
        wait.until(ExpectedConditions.visibilityOfElementLocated(
                By.cssSelector("section.board.dialog a.button-abort")));
        Assert.assertFalse(findElementsByLocatorType(
                TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                "//section[contains(@class,'dialog')]//*[contains(text(),'Aktuell ist ein Kundenkontakt aktiv')]",
                LocatorType.XPATH).isEmpty(),
                "Expected confirm dialog text about a customer already in progress.");
    }

    public void assertCallOtherProcessConfirmDialogNotVisible() {
        ScenarioLogManager.getLogger().info("Checking confirm dialog for switching queue customer is not visible...");
        Assert.assertTrue(findElementsByLocatorType(
                TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                "section.board.dialog a.button-abort",
                LocatorType.CSSSELECTOR).isEmpty(),
                "Confirm dialog for switching queue customer should not be visible.");
        Assert.assertTrue(findElementsByLocatorType(
                TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                "//section[contains(@class,'board') and contains(@class,'dialog')]//*[contains(text(),'Aktuell ist ein Kundenkontakt aktiv')]",
                LocatorType.XPATH).isEmpty(),
                "Confirm dialog text for switching queue customer should not be visible.");
    }

    public void assertAlreadyCalledProcessErrorVisible() {
        ScenarioLogManager.getLogger().info("Checking error that a process is already called...");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.withMessage("Already-called process error message is not visible!");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.xpath(
                "//section[contains(@class,'message--error')]//*[contains(text(),"
                        + "'Bitte schließen Sie den aktuellen Vorgang zuerst ab.')]"
        )));
    }

    /**
     * Queue pick after another workstation already called the process
     * ({@code processnotcallable.twig} via GET /process/{id}/).
     */
    public void assertProcessNotCallableByOtherWorkstationVisible() {
        ScenarioLogManager.getLogger().info(
            "Checking ProcessNotCallable error (already handled by another workstation)...");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.withMessage("ProcessNotCallable headline is not visible!");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.xpath(
                "//*[contains(@class,'exceptionData-headline') and contains(.,"
                        + "'Termin kann nicht aufgerufen oder bearbeitet werden')]"
        )));
        wait.withMessage("ProcessNotCallable body about another workstation is not visible!");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.xpath(
                "//*[contains(.,'bereits von einem anderen Arbeitsplatz bearbeitet wird')]"
        )));
        wait.withMessage("ProcessNotCallable status 'aufgerufen' is not visible!");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.xpath(
                "//*[contains(.,'Aktueller Status') and contains(.,'aufgerufen')]"
        )));
    }

    public void openWorkstationCallForProcessId(String processId) {
        ScenarioLogManager.getLogger().info("Opening workstation call for process {}...", processId);
        Assert.assertTrue(processId != null && processId.matches("\\d+"),
                "Expected numeric process id, got: " + processId);
        By callLink = By.cssSelector("a[data-process='" + processId + "']");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(Math.min(DEFAULT_EXPLICIT_WAIT_TIME, 15)));
        wait.ignoring(StaleElementReferenceException.class, ElementClickInterceptedException.class);
        try {
            wait.until(driver -> {
                List<WebElement> links = driver.findElements(callLink);
                if (links.isEmpty() || !links.get(0).isDisplayed()) {
                    return false;
                }
                scrollToCenterByVisibleElement(links.get(0));
                links.get(0).click();
                return true;
            });
            return;
        } catch (TimeoutException | ElementClickInterceptedException ignored) {
            // Fall through to direct navigation when the queue row never becomes clickable.
        }
        String current = DRIVER.getCurrentUrl();
        String base = current.replaceAll("[?#].*$", "");
        if (!base.endsWith("/")) {
            base = base + "/";
        }
        DRIVER.navigate().to(base + "?calledprocess=" + processId);
    }

    public void assertCustomerAppearedButtonVisible() {
        ScenarioLogManager.getLogger().info("Checking that \"Ja, Kunde erschienen\" is visible...");
        Assert.assertTrue(isWebElementVisible(
                DEFAULT_EXPLICIT_WAIT_TIME,
                "//button[contains(normalize-space(.),'Ja, Kunde erschienen')]",
                LocatorType.XPATH,
                false,
                CONTEXT),
                "Expected button \"Ja, Kunde erschienen\" to be visible (called status).");
    }

    public void clickStayOnCurrentProcessInConfirmDialog() {
        ScenarioLogManager.getLogger().info("Clicking \"Zurück zum aktuellen Vorgang\"...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "section.board.dialog a.button-abort", LocatorType.CSSSELECTOR, false, CONTEXT);
    }

    public void clickFinishAndCallSelectedInConfirmDialog() {
        ScenarioLogManager.getLogger().info("Clicking \"Aktuellen Termin fertig stellen und Kunden aufrufen\"...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "section.board.dialog a.button-ok", LocatorType.CSSSELECTOR, false, CONTEXT);
    }

    public void assertStatisticToggleLabel(String label) {
        ScenarioLogManager.getLogger().info("Checking the statistics toggle label \"{}\"...", label);
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement toggleLabel = wait.until(ExpectedConditions.visibilityOfElementLocated(
                By.cssSelector(".statistic-additional-department-requests__label")));
        Assert.assertEquals(toggleLabel.getText().trim(), label,
                "Statistics toggle has the wrong label.");
    }

    public void clickStatisticToggle(String label) {
        ScenarioLogManager.getLogger().info("Clicking the statistics toggle \"{}\"...", label);
        assertStatisticToggleLabel(label);
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME,
                "button.statistic-additional-department-requests__toggle",
                LocatorType.CSSSELECTOR, false, CONTEXT);
        CONTEXT.waitForSpinners();
    }

    public void assertScopeStatisticServiceVisible(String service) {
        ScenarioLogManager.getLogger().info("Checking scope service \"{}\" on the finish form...", service);
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.until(ExpectedConditions.visibilityOfElementLocated(scopeStatisticService(service)));
    }

    public void assertAdditionalStatisticServiceDisplayed(String service, boolean displayed) {
        ScenarioLogManager.getLogger().info(
                "Checking additional service \"{}\" displayed={}...", service, displayed);
        By locator = additionalStatisticService(service);
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        if (displayed) {
            wait.until(ExpectedConditions.visibilityOfElementLocated(locator));
            return;
        }
        wait.withMessage("Additional service \"" + service + "\" is still visible.")
                .until(driver -> driver.findElements(locator).stream().noneMatch(WebElement::isDisplayed));
    }

    public void increaseAdditionalStatisticService(String service, int times) {
        ScenarioLogManager.getLogger().info(
                "Increasing additional service \"{}\" by {}", service, times);
        By increment = By.xpath(
                "//div[contains(@class,'statistic-additional-department-requests__panel')]"
                        + "//div[contains(@class,'form-input-counter')][.//label[normalize-space()='"
                        + service + "']]//button[contains(@class,'increment')]");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        for (int i = 0; i < times; i++) {
            wait.until(ExpectedConditions.elementToBeClickable(increment)).click();
        }
    }

    public void submitStatisticsFinish() {
        ScenarioLogManager.getLogger().info("Submitting the statistics finish form...");
        By submit = By.xpath(
                "//form[contains(@class,'form--base')]//button[@type='submit' and contains(normalize-space(.),'Bearbeitung abschließen')]");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement button = wait.until(ExpectedConditions.elementToBeClickable(submit));
        button.click();
        CONTEXT.waitForSpinners();
    }

    private By scopeStatisticService(String service) {
        return By.xpath("//legend[normalize-space()='Dienstleistungen Erfassen']/ancestor::fieldset[1]"
                + "//label[normalize-space()='" + service + "']");
    }

    private By additionalStatisticService(String service) {
        return By.xpath("//div[contains(@class,'statistic-additional-department-requests__panel')]"
                + "//label[normalize-space()='" + service + "']");
    }

    public void completeStatisticsFinishIfPresent() {
        ScenarioLogManager.getLogger().info("Completing statistics finish form if present...");
        final String submitLocator = "//button[contains(text(),'Bearbeitung abschließen') or @value='submit']"
                + " | //input[@type='submit' and contains(@value,'Bearbeitung abschließen')]";
        if (!isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, submitLocator, LocatorType.XPATH, true, CONTEXT)
                && !isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "form.form--base button[type='submit']", LocatorType.CSSSELECTOR, true, CONTEXT)) {
            return;
        }
        if (isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "input[name='noRequestsPerformed']", LocatorType.CSSSELECTOR, true, CONTEXT)) {
            WebElement noRequestsPerformed = DRIVER.findElement(By.cssSelector("input[name='noRequestsPerformed']"));
            // Ensure checked so submit can proceed without selecting services (default is unchecked).
            if (!noRequestsPerformed.isSelected()) {
                noRequestsPerformed.click();
            }
        }
        if (isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "form.form--base button[type='submit']", LocatorType.CSSSELECTOR, true, CONTEXT)) {
            clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "form.form--base button[type='submit']", LocatorType.CSSSELECTOR, false, CONTEXT);
        } else {
            clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, submitLocator, LocatorType.XPATH, false, CONTEXT);
        }
        CONTEXT.waitForSpinners();
    }

    /**
     * Kundeninformationen while a process is in processing: Fertig stellen, Weiterleiten, Parken, Abbrechen.
     * Locked on the redirect page (ZMSKVR-157); clickable again after "Abbrechen der Weiterleitung".
     */
    public void assertCustomerActionsEnabled() {
        ScenarioLogManager.getLogger().info("Checking that customer actions are visible and clickable...");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector("section.client-info[data-actions-locked='0']")));
        for (String label : CUSTOMER_ACTION_LABELS) {
            WebElement action = wait.until(ExpectedConditions.visibilityOfElementLocated(customerActionLocator(label, true)));
            Assert.assertTrue(action.isDisplayed(), "Customer action '" + label + "' is not visible.");
            Assert.assertTrue(action.isEnabled(), "Customer action '" + label + "' is visible but not clickable.");
        }
    }

    public void assertCustomerActionsDisabled() {
        ScenarioLogManager.getLogger().info("Checking that customer actions stay visible but have no function...");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector("section.client-info[data-actions-locked='1']")));
        for (String label : CUSTOMER_ACTION_LABELS) {
            WebElement action = wait.until(ExpectedConditions.visibilityOfElementLocated(customerActionLocator(label, false)));
            Assert.assertTrue(action.isDisplayed(), "Customer action '" + label + "' is not visible while forwarding.");
            Assert.assertFalse(action.isEnabled(), "Customer action '" + label + "' is still clickable while forwarding.");
        }
    }

    public void assertForwardingFormVisible() {
        ScenarioLogManager.getLogger().info("Checking that the forwarding form is visible...");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.until(ExpectedConditions.urlContains("/workstation/process/redirect/"));
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.xpath(
                "//section[contains(@class,'appointment-form')]//h2[contains(@class,'board__heading') and contains(normalize-space(.), 'Termin Weiterleiten')]")));
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//select[@name='location']", LocatorType.XPATH, true, CONTEXT),
                "Forwarding location dropdown is not visible.");
    }

    public void assertCancelForwardingButtonBlue() {
        ScenarioLogManager.getLogger().info("Checking the blue cancel-forwarding button...");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement cancel = wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector("a.button-redirect-cancel")));
        Assert.assertEquals(cancel.getText().trim(), "Abbrechen der Weiterleitung",
                "Cancel forwarding button has the wrong label.");
        String background = cancel.getCssValue("background-color").replace(" ", "");
        Assert.assertTrue(background.contains("0,83,180"),
                "Cancel forwarding button should be blue (#0053B4) but background-color was " + cancel.getCssValue("background-color") + ".");
    }

    public void clickCancelForwarding() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Abbrechen der Weiterleitung\"...");
        final String locator = "//a[contains(@class,'button-redirect-cancel') and normalize-space()='Abbrechen der Weiterleitung']";
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, locator, LocatorType.XPATH, true, CONTEXT),
                "Button 'Abbrechen der Weiterleitung' is not visible!");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, locator, LocatorType.XPATH, false, CONTEXT);
    }

    public void assertAppointmentFormVisible() {
        ScenarioLogManager.getLogger().info("Checking that the appointment form is back...");
        CONTEXT.set();
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.until(driver -> driver.getCurrentUrl() == null || !driver.getCurrentUrl().contains("/process/redirect/"));
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.xpath(
                "//section[contains(@class,'appointment-form')]//h2[contains(@class,'board__heading') and contains(normalize-space(.), 'Termin erstellen')]")));
        Assert.assertTrue(DRIVER.findElements(By.cssSelector("a.button-redirect-cancel")).isEmpty(),
                "Cancel forwarding button is still visible after leaving the forwarding form.");
    }

    private By customerActionLocator(String label, boolean enabled) {
        String section = "//section[contains(@class,'client-info')]";
        String exact = "normalize-space()='" + label + "'";
        if (!enabled) {
            return By.xpath(section + "//button[" + exact + " and @disabled]");
        }
        if ("Parken".equals(label) || "Abbrechen".equals(label)) {
            return By.xpath(section + "//button[" + exact + " and not(@disabled)]");
        }
        return By.xpath(section + "//a[" + exact + "]");
    }

}
