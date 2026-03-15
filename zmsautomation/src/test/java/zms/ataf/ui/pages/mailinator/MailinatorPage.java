package zms.ataf.ui.pages.mailinator;

import java.time.Duration;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.Locale;
import java.util.concurrent.atomic.AtomicReference;

import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedCondition;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.data.System;
import ataf.core.helpers.TestDataHelper;
import ataf.core.helpers.TestPropertiesHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.core.properties.DefaultValues;
import ataf.web.controls.FrameControls;
import ataf.web.controls.WindowControls;
import ataf.web.model.Frame;
import ataf.web.model.LocatorType;
import ataf.web.model.WindowType;
import ataf.web.pages.BasePage;
import ataf.web.utils.DriverUtil;


public class MailinatorPage extends BasePage {

    private final MailinatorPageContext CONTEXT;
    private final String ACTIVATION_MESSAGE_LOCATOR_XPATH = "//td[contains(text(),'Aktivieren Sie Ihren Termin')]";
    private final Frame ACTIVATION_MESSAGE_FRAME = new Frame("activation message frame", "html_msg_body", LocatorType.ID, FrameControls.DEFAULT_CONTENT);
    private final String ACTIVATION_LINK_LOCATOR_XPATH = "/html/body/div/strong/a[text()='Termin aktivieren']";
    private final String GO_BUTTON_LOCATOR_CSS_SELECTOR = "button.primary-btn";
    public final long EMAIL_WAIT_TIME;

    public MailinatorPage(RemoteWebDriver driver) {
        super(driver);
        CONTEXT = new MailinatorPageContext(driver);
        EMAIL_WAIT_TIME = DEFAULT_EXPLICIT_WAIT_TIME * 8L;
    }

    public void navigateToPage() {
        if (navigateToPageByUrl(DEFAULT_EXPLICIT_WAIT_TIME, MailinatorPageContext.URL, MailinatorPageContext.TITLE)) {
            WindowControls.updateWindowList(DriverUtil.getDriver(), new WindowType("Mailinator", new System("Mailinator", MailinatorPageContext.URL)));
            CONTEXT.setWindow(WindowControls.getActiveWindow());
            FrameControls.setCurrentFrame(FrameControls.DEFAULT_CONTENT);
        } else {
            Assert.fail("Could not navigate to Mailinator!");
        }
    }

    public void enterInboxName(String emailAddress) {
        if (CONTEXT.isMessageOpen()) {
            clickOnBackToInboxLink();
        }
        ScenarioLogManager.getLogger().info("Trying to enter inbox name of email address \"" + emailAddress + "\"...");
        String inboxName = emailAddress.split("@")[0];
        enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, inboxName, "search", LocatorType.ID);
    }

    public void clickOnGoButton() {
        if (CONTEXT.isMessageOpen()) {
            clickOnBackToInboxLink();
        }
        ScenarioLogManager.getLogger().info("Trying to click on \"GO\" button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[text()='GO']", LocatorType.XPATH, false, CONTEXT);
    }

    public Exception waitForActivationMessage() {
        if (CONTEXT.isMessageOpen()) {
            clickOnBackToInboxLink();
        }
        CONTEXT.set();
        int activationMessageAmount = Integer.parseInt(TestDataHelper.getTestData("appointment_count"));
        AtomicReference<Exception> exceptionAtomicReference = new AtomicReference<>(null);
        ScenarioLogManager.getLogger().info("Waiting for activation message...");
        try {
            WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(EMAIL_WAIT_TIME));
            wait.pollingEvery(Duration.ofMillis(1000L));
            wait.until((ExpectedCondition<Boolean>) waitDriver -> {
                List<WebElement> activationMessages = findElementsByLocatorType(
                        TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                        ACTIVATION_MESSAGE_LOCATOR_XPATH, LocatorType.XPATH);
                if (activationMessages.size() == activationMessageAmount) {
                    return true;
                } else {
                    clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, GO_BUTTON_LOCATOR_CSS_SELECTOR, LocatorType.CSSSELECTOR, false);
                    return false;
                }
            });
        } catch (Exception e) {
            exceptionAtomicReference.set(e);
        }
        return exceptionAtomicReference.get();
    }

    public void clickOnActivationMessage() {
        if (CONTEXT.isMessageOpen()) {
            clickOnBackToInboxLink();
        }
        CONTEXT.set();
        int activationMessageAmount = Integer.parseInt(TestDataHelper.getTestData("appointment_count"));
        ScenarioLogManager.getLogger().info("Trying to click on \"activation\" message...");
        try {
            WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(EMAIL_WAIT_TIME));
            wait.pollingEvery(Duration.ofMillis(1000L));
            wait.until((ExpectedCondition<Boolean>) waitDriver -> {
                List<WebElement> activationMessages = findElementsByLocatorType(
                        TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                        ACTIVATION_MESSAGE_LOCATOR_XPATH, LocatorType.XPATH);
                if (activationMessages.size() == activationMessageAmount) {
                    clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, activationMessages.get(0), false);
                    return true;
                } else {
                    clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, GO_BUTTON_LOCATOR_CSS_SELECTOR, LocatorType.CSSSELECTOR, false);
                    return false;
                }
            });
        } catch (TimeoutException e) {
            Assert.fail("Activation message was not received in time!", e);
        }
        Assert.assertTrue(isWebElementInvisible(DEFAULT_EXPLICIT_WAIT_TIME, ACTIVATION_MESSAGE_LOCATOR_XPATH, LocatorType.XPATH, CONTEXT),
                "Click on activation message has failed!");
        CONTEXT.setMessageOpen(true);
    }

    public void checkActivationMessageContents() {
        if (!FrameControls.getCurrentFrame().equals(ACTIVATION_MESSAGE_FRAME)) {
            FrameControls.switchToFrame(DRIVER, DEFAULT_EXPLICIT_WAIT_TIME, ACTIVATION_MESSAGE_FRAME);
        }
        LocalDate reservationDate = LocalDate.parse(
                TestDataHelper.getTestData("day") + "." + TestDataHelper.getTestData("month") + "." + TestDataHelper.getTestData("year"),
                DateTimeFormatter.ofPattern("dd.MM.yyyy"));
        String reservationDateString = reservationDate.format(DateTimeFormatter.ofPattern("eeee, dd. MMMM yyyy", Locale.GERMANY));
        String reservationTime = TestDataHelper.getTestData("time");
        if (reservationTime.length() == 4) {
            reservationTime = '0' + reservationTime;
        }
        //        ScenarioLogManager.getLogger().info("Checking if activation message has all contents!");
        //        Assert.assertEquals(getWebElementText(DEFAULT_EXPLICIT_WAIT_TIME, "/html/body/div", LocatorType.XPATH, CONTEXT).replaceAll("[\\r\\n]", "").trim(),
        //                "Achtung! Dies ist eine automatisch erstellte E-Mail. Bitte antworten Sie nicht auf diese Mail, sie kann nicht bearbeitet werden." +
        //                        "Guten Tag " + TestDataHelper.getTestData("customer_name") + "," +
        //                        "vielen Dank für die Terminanfrage." +
        //                        "Klicken Sie bitte auf den unten stehenden Link, um den Termin am " + reservationDateString + " um " + reservationTime + " Uhr verbindlich zu reservieren." +
        //                        "Termin bestätigen" +
        //                        "Bitte beachten Sie, dass Ihre Terminanfrage ohne eine Bestätigung nach Ablauf von 15 Minuten gelöscht wird." +
        //                        "Nach Aktivierung des Termins erhalten Sie eine Bestätigung mit der Terminnummer und weiteren Einzelheiten, die Sie für Ihre Vorsprache benötigen." +
        //                        "Mit freundlichen Grüßen" +
        //                        "Ihr " + TestDataHelper.getTestData("office"),
        //                "Welcome text does not match expected text!");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, ACTIVATION_LINK_LOCATOR_XPATH, LocatorType.XPATH, true, CONTEXT),
                "Reservation activation link is not visible!");
        Assert.assertFalse(findElementByLocatorTypeNoWait(ACTIVATION_LINK_LOCATOR_XPATH, LocatorType.XPATH).getAttribute("href").isEmpty(),
                "Reservation activation link is pointing nowhere!");
    }

    public void clickOnActivationLink() {
        ScenarioLogManager.getLogger().info("Trying to click on \"reservation activation\" link...");
        scrollToCenterByVisibleElement(findElementByLocatorType(ACTIVATION_LINK_LOCATOR_XPATH, LocatorType.XPATH, true));
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, ACTIVATION_LINK_LOCATOR_XPATH, LocatorType.XPATH, false, CONTEXT);
    }

    public void clickOnBackToInboxLink() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Back to Inbox\" link...");
        final String BACK_TO_INBOX_LINK_LOCATOR_XPATH = "//a[contains(text(), 'Back to Inbox')]";
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, BACK_TO_INBOX_LINK_LOCATOR_XPATH, LocatorType.XPATH, false, CONTEXT);
        Assert.assertTrue(isWebElementInvisible(DEFAULT_EXPLICIT_WAIT_TIME, BACK_TO_INBOX_LINK_LOCATOR_XPATH, LocatorType.XPATH, CONTEXT),
                "Click on \"back to inbox\" link has failed!");
        CONTEXT.setMessageOpen(false);
    }

    public void checkForConfirmationMessage() {
        if (CONTEXT.isMessageOpen()) {
            clickOnBackToInboxLink();
        }
        CONTEXT.set();
        int appointmentMessageAmount = Integer.parseInt(TestDataHelper.getTestData("appointment_count"));
        ScenarioLogManager.getLogger().info("Checking if confirmation message is visible!");
        try {
            WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(EMAIL_WAIT_TIME));
            wait.pollingEvery(Duration.ofMillis(1000L));
            wait.withMessage("Confirmation message was not received in time!");
            wait.until((ExpectedCondition<Boolean>) waitDriver -> {
                List<WebElement> confirmationMessages = findElementsByLocatorType(
                        TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                        "//td[contains(text(),'Bestätigung Ihres Termin')]", LocatorType.XPATH);
                if (confirmationMessages.size() == appointmentMessageAmount) {
                    return true;
                } else {
                    clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, GO_BUTTON_LOCATOR_CSS_SELECTOR, LocatorType.CSSSELECTOR, false);
                    return false;
                }
            });
        } catch (TimeoutException e) {
            Assert.fail("Confirmation message was not received in time!", e);
        }
    }

    public void checkForCancellationMessage() {
        if (CONTEXT.isMessageOpen()) {
            clickOnBackToInboxLink();
        }
        CONTEXT.set();
        int appointmentsCanceledAmount;
        if (TestDataHelper.getTestData("appointment_canceled_count") != null){
            appointmentsCanceledAmount = Integer.parseInt(TestDataHelper.getTestData("appointment_canceled_count"));
        } else {
            appointmentsCanceledAmount = 0;
        }
        ScenarioLogManager.getLogger().info("Checking if cancellation message is visible!");
        try {
            WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(EMAIL_WAIT_TIME));
            wait.pollingEvery(Duration.ofMillis(1000L));
            wait.withMessage("Cancellation message was not received in time!");
            wait.until((ExpectedCondition<Boolean>) waitDriver -> {
                List<WebElement> confirmationMessages = findElementsByLocatorType(
                        TestPropertiesHelper.getPropertyAsLong("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME),
                        "//td[contains(text(),'Absage Ihres Termins')]", LocatorType.XPATH);
                if (confirmationMessages.size() == appointmentsCanceledAmount) {
                    return true;
                } else {
                    clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, GO_BUTTON_LOCATOR_CSS_SELECTOR, LocatorType.CSSSELECTOR, false);
                    return false;
                }
            });
        } catch (TimeoutException e) {
            Assert.fail("Cancellation message was not received in time!", e);
        }
    }
}