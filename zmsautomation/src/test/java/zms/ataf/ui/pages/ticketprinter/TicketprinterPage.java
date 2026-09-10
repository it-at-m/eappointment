package zms.ataf.ui.pages.ticketprinter;

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
import ataf.core.logging.ScenarioLogManager;
import ataf.web.model.LocatorType;
import ataf.web.pages.BasePage;

public class TicketprinterPage extends BasePage {

    private static final String CLOSED_TEXT = "Kundenservice ist geschlossen";
    private static final By WAITING_NUMBER = By.cssSelector(".noprint .nummernanzeige");
    private static final By WAITING_NUMBER_FALLBACK = By.cssSelector(".nummernanzeige");

    private final TicketprinterPageContext CONTEXT;

    public TicketprinterPage(RemoteWebDriver driver) {
        super(driver);
        CONTEXT = new TicketprinterPageContext(driver);
    }

    public void openScope(String scopeId) {
        ScenarioLogManager.getLogger().info("Opening ticketprinter for scope {}", scopeId);
        CONTEXT.navigateToScope(scopeId);
        failIfClosed();
    }

    public void openRequest(String scopeId, String requestId) {
        ScenarioLogManager.getLogger().info("Opening ticketprinter for request {} at scope {}", requestId, scopeId);
        CONTEXT.navigateToRequest(scopeId, requestId);
        failIfClosed();
    }

    public void assertWaitingNumberButtonVisible(String label) {
        WebElement button = waitForWaitingNumberButton(label);
        Assert.assertTrue(button.isDisplayed(), "Ticketprinter button \"" + label + "\" is not visible");
        Assert.assertTrue(button.isEnabled(), "Ticketprinter button \"" + label + "\" is disabled");
    }

    public void clickWaitingNumberButton(String label) {
        CONTEXT.stubWindowPrint();
        WebElement button = waitForWaitingNumberButton(label);
        ((JavascriptExecutor) DRIVER).executeScript("window.print = function(){};");
        ScenarioLogManager.getLogger().info("Clicking ticketprinter button \"{}\"", label);
        ((JavascriptExecutor) DRIVER).executeScript("arguments[0].click();", button);
    }

    public void assertWaitingNumberShown() {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(10));
        WebElement numberEl;
        try {
            numberEl = wait.until(ExpectedConditions.visibilityOfElementLocated(WAITING_NUMBER));
        } catch (TimeoutException first) {
            numberEl = wait.until(ExpectedConditions.visibilityOfElementLocated(WAITING_NUMBER_FALLBACK));
        }
        String number = numberEl.getText().trim();
        Assert.assertFalse(number.isEmpty(), "Waiting number was empty on the process page");
        TestDataHelper.setTestData("wartenummer", number);
        ScenarioLogManager.getLogger().info("Ticketprinter waiting number: {}", number);
        Assert.assertTrue(
                DRIVER.getPageSource().contains("Ihre Wartenummer lautet")
                        || DRIVER.getPageSource().contains("Ihre Wartenummer:"),
                "Process page did not show the waiting-number confirmation");
    }

    private WebElement waitForWaitingNumberButton(String label) {
        failIfClosed();
        String xpath = "//button[contains(@class,'eintragen') and contains(normalize-space(.),'" + label + "')]";
        try {
            return new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                    .until(ExpectedConditions.elementToBeClickable(By.xpath(xpath)));
        } catch (TimeoutException e) {
            Assert.fail("Ticketprinter button \"" + label + "\" was not clickable. currentUrl="
                    + DRIVER.getCurrentUrl(), e);
            return null;
        }
    }

    private void failIfClosed() {
        if (isWebElementVisible(2, "//*[contains(text(),'" + CLOSED_TEXT + "')]", LocatorType.XPATH, false)) {
            Assert.fail("Ticketprinter shows \"" + CLOSED_TEXT
                    + "\" — Spontankunden opening hours for the current time are missing. currentUrl="
                    + DRIVER.getCurrentUrl());
        }
    }
}
