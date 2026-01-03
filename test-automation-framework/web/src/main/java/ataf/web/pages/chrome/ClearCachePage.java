package ataf.web.pages.chrome;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.model.LocatorType;
import ataf.web.pages.BasePage;
import org.openqa.selenium.By;
import org.openqa.selenium.SearchContext;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

import java.time.Duration;

/**
 * Represents the Google Chrome clear cache page and provides methods to interact with it.
 * <p>
 * This class extends the {@link BasePage} and includes functionality to navigate to the clear cache
 * settings and perform actions such as clearing the browser
 * cache.
 * </p>
 *
 * <p>
 * Author: Ludwig Haas (ex.haas02)
 * </p>
 */
public class ClearCachePage extends BasePage {
    /**
     * Context description for this page
     */
    public static final String CONTEXT = "Google Chrome clear cache page";

    /**
     * Constructs a ClearCachePage with the specified WebDriver.
     *
     * @param driver the RemoteWebDriver instance used to interact with the browser.
     */
    public ClearCachePage(RemoteWebDriver driver) {
        super(driver); // Calls the constructor of the parent class
    }

    /**
     * Navigates to the Chrome clear cache settings page.
     */
    public void navigateToClearCachePage() {
        DRIVER.navigate().to("chrome://settings/clearBrowserData"); // Opens the clear cache page
    }

    /**
     * Clicks on the "Clear browser cache" button.
     * <p>
     * This method identifies the "clear data" button within a series of nested Shadow DOM elements and
     * clicks it. It also waits for any spinner element to be
     * visible and then invisible to ensure that the action has been completed.
     * </p>
     */
    public void clickOnClearBrowserCacheButton() {
        // Begin identifying the clear data button via nested Shadow DOM elements
        // Get 1st parent
        WebElement root1 = findElementByLocatorType("settings-ui", LocatorType.CSSSELECTOR, true, DEFAULT_EXPLICIT_WAIT_TIME, true, false);

        // Get 1st shadow root element
        SearchContext shadowRoot1 = expandShadowRootElement(root1);

        // Get 2nd parent
        WebElement root2 = shadowRoot1.findElement(By.cssSelector("settings-main"));
        // Get 2nd shadow root element
        SearchContext shadowRoot2 = expandShadowRootElement(root2);

        // Get 3rd parent
        WebElement root3 = shadowRoot2.findElement(By.cssSelector("settings-basic-page"));
        // Get 3rd shadow root element
        SearchContext shadowRoot3 = expandShadowRootElement(root3);

        // Get 4th parent
        WebElement root4 = shadowRoot3.findElement(By.cssSelector("settings-section > settings-privacy-page"));
        // Get 4th shadow root element
        SearchContext shadowRoot4 = expandShadowRootElement(root4);

        // Get 5th parent
        WebElement root5 = shadowRoot4.findElement(By.cssSelector("settings-clear-browsing-data-dialog"));
        // Get 5th shadow root element
        SearchContext shadowRoot5 = expandShadowRootElement(root5);

        // Get 6th parent
        WebElement root6 = shadowRoot5.findElement(By.cssSelector("#clearBrowsingDataDialog"));

        // Get button (finally!)
        WebElement clearDataButton = root6.findElement(By.cssSelector("#clearBrowsingDataConfirm"));
        // End identifying the clear data button via nested Shadow DOM elements
        ScenarioLogManager.getLogger().info("Trying to click on \"clear data\" button!");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, clearDataButton, true); // Click that hard-to-reach button!

        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        try {
            // Get 7th parent
            WebElement root7 = root6.findElement(By.cssSelector("paper-spinner-lite"));
            // Get 6th shadow root element
            SearchContext shadowRoot6 = expandShadowRootElement(root7);

            // Starting wait on spinner before proceeding...
            wait.until(ExpectedConditions.visibilityOf(shadowRoot6.findElement(By.cssSelector("#spinnerContainer"))));
            wait.until(ExpectedConditions.invisibilityOf(shadowRoot6.findElement(By.cssSelector("#spinnerContainer"))));
        } catch (StaleElementReferenceException ignore) {
            // Continue if the element is no longer attached to the DOM
        }
    }
}
