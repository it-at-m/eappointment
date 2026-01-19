package ataf.example.pages;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.model.LocatorType;
import ataf.web.pages.BasePage;
import org.openqa.selenium.By;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.SearchContext;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import java.time.Duration;
import java.util.List;

/**
 * @author Ludwig Haas (ex.haas02)
 */
public class WiLMAPage extends BasePage {

    public WiLMAPage(RemoteWebDriver driver) {
        super(driver);
    }

    private final String SKIP_BUTTON_LOCATOR_CSS_SELECTOR = ".cat-mr-auto";
    private final String NEXT_BUTTON_LOCATOR_XPATH = "//button[@aria-label='Zum nächsten Schritt']";
    private final String BACK_BUTTON_LOCATOR_XPATH = "//button[@aria-label='Zum vorherigen Schritt']";
    private final String END_BUTTON_LOCATOR_XPATH = "//button[@aria-label='Beende die Tour']";

    public void clickOnAvatarIcon() {
        ScenarioLogManager.getLogger().info("Trying to click on avatar icon...");
        SearchContext catButtonShadowRoot = expandShadowRootElement(findElementByLocatorType(
                "li.nav-item-right:nth-child(4) > coyo-user-menu-btn:nth-child(1) > cat-dropdown:nth-child(1) > cat-button:nth-child(1)",
                LocatorType.CSSSELECTOR, false));
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, catButtonShadowRoot.findElement(By.cssSelector(".cat-button")), false);
    }

    public boolean isUserMenuOpen() {
        ScenarioLogManager.getLogger().info("Checking if user menu is open...");
        try {
            WebDriverWait webDriverWait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
            webDriverWait.ignoring(NoSuchElementException.class);
            webDriverWait.until(ExpectedConditions.visibilityOf(DRIVER.findElement(By.cssSelector("#user-menu-content"))));
            return true;
        } catch (TimeoutException e) {
            return false;
        }
    }

    public boolean isStartTourMenuEntryVisible() {
        ScenarioLogManager.getLogger().info("Checking if \"Tour starten\" menu entry is visible...");
        SearchContext catButtonShadowRoot = expandShadowRootElement(
                findElementByLocatorType("#user-menu-content > coyo-user-menu > ul > li:nth-child(15) > cat-button",
                        LocatorType.CSSSELECTOR, false));
        try {
            WebDriverWait webDriverWait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
            webDriverWait.ignoring(NoSuchElementException.class);
            webDriverWait.until(
                    ExpectedConditions.visibilityOf(catButtonShadowRoot.findElement(By.cssSelector(".cat-button"))));
            return true;
        } catch (TimeoutException e) {
            return false;
        }
    }

    public void clickOnStartTourMenuEntry() {
        ScenarioLogManager.getLogger().info("Trying to click \"Tour starten\" menu entry...");
        SearchContext catButtonShadowRoot = expandShadowRootElement(findElementByLocatorType(
                "#user-menu-content > coyo-user-menu > ul > li:nth-child(15) > cat-button",
                LocatorType.CSSSELECTOR, false));
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, catButtonShadowRoot.findElement(By.cssSelector(".cat-button")), false);
    }

    public boolean isDialogPopUpVisible() {
        ScenarioLogManager.getLogger().info("Checking if dialog pop up is visible...");
        return isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "cdk-overlay-0", LocatorType.ID, false);
    }

    public void clickOnPopUpButtonWithText(String buttonText) {
        ScenarioLogManager.getLogger().info("Trying to click on pop up button with text \"{}\"...", buttonText);
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//div[@class='cui-dialog-content']/div/button/span[contains(string(),'" + buttonText + "')]",
                LocatorType.XPATH, false);
    }

    public void checkIfTourHasStarted() {
        ScenarioLogManager.getLogger().info("Checking if tour has started by verifying if elements are visible...");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, ".shepherd-header", LocatorType.CSSSELECTOR, false), "Tour header is not visible!");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "navigation--home-description", LocatorType.ID, false),
                "Tour description is not visible!");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, SKIP_BUTTON_LOCATOR_CSS_SELECTOR, LocatorType.CSSSELECTOR, false),
                "Skip button is not visible!");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, NEXT_BUTTON_LOCATOR_XPATH, LocatorType.XPATH, false), "Next button is not visible!");
    }

    private WebElement getEnabledButton(String locator, LocatorType locatorType) {
        switch (locatorType) {
            case ID -> waitForElementById(DEFAULT_EXPLICIT_WAIT_TIME, locator, false, false);
            case CSSSELECTOR -> waitForElementByCssSelector(DEFAULT_EXPLICIT_WAIT_TIME, locator, false, false);
            case NAME -> waitForElementByName(DEFAULT_EXPLICIT_WAIT_TIME, locator, false, false);
            case CLASS -> waitForElementByClass(DEFAULT_EXPLICIT_WAIT_TIME, locator, false, false);
            case XPATH -> waitForElementByXpath(DEFAULT_EXPLICIT_WAIT_TIME, locator, false, false);
            case TAGNAME -> waitForElementByTagName(DEFAULT_EXPLICIT_WAIT_TIME, locator, false, false);
            case LINKTEXT -> waitForElementByLinkText(DEFAULT_EXPLICIT_WAIT_TIME, locator, false, false);
            case TEXT -> waitForElementByXpath(DEFAULT_EXPLICIT_WAIT_TIME, "//*[text()='" + locator + "']", false, false);
        }

        // Every time the next button is click a new copy of the button is added to the DOM and deactivated. So this makes sure the latest button is clicked!
        List<WebElement> nextButtons = findElementsByLocatorType(DEFAULT_IMPLICIT_WAIT_TIME, locator, locatorType);
        WebElement webElement = nextButtons.stream()
                .filter(WebElement::isEnabled)
                .reduce((first, second) -> second) // take the last enabled button
                .orElseThrow(() -> new NoSuchElementException("No active Next button found"));

        return webElement;
    }

    public void clickOnNextButton() {
        ScenarioLogManager.getLogger().info("Trying to click \"Next\" button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, getEnabledButton(NEXT_BUTTON_LOCATOR_XPATH, LocatorType.XPATH), false);
    }

    public void checkIfPagesTopicIsDisplayed() {
        ScenarioLogManager.getLogger().info("Checking if information for topic pages is displayed by verifying if elements are visible...");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "h3#navigation--pages-label.shepherd-title", LocatorType.CSSSELECTOR, false),
                "Pages topic header is not visible!");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "div#navigation--pages-description.shepherd-text", LocatorType.CSSSELECTOR, false),
                "Pages topic description is not visible!");
        Assert.assertTrue(getEnabledButton(SKIP_BUTTON_LOCATOR_CSS_SELECTOR, LocatorType.CSSSELECTOR).isDisplayed(), "Skip button is not visible!");
        Assert.assertTrue(getEnabledButton(NEXT_BUTTON_LOCATOR_XPATH, LocatorType.XPATH).isDisplayed(), "Next button is not visible!");
    }

    public void checkIfWorkSpaceTopicIsDisplayed() {
        ScenarioLogManager.getLogger().info("Checking if information for topic work spaces is displayed by verifying if elements are visible...");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "h3#navigation--workspaces-label.shepherd-title", LocatorType.CSSSELECTOR, false),
                "Work spaces topic header is not visible!");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "div#navigation--workspaces-description.shepherd-text", LocatorType.CSSSELECTOR, false),
                "Work spaces topic description is not visible!");
        Assert.assertTrue(getEnabledButton(SKIP_BUTTON_LOCATOR_CSS_SELECTOR, LocatorType.CSSSELECTOR).isDisplayed(), "Skip button is not visible!");
        Assert.assertTrue(getEnabledButton(BACK_BUTTON_LOCATOR_XPATH, LocatorType.XPATH).isDisplayed(), "Back button is not visible!");
        Assert.assertTrue(getEnabledButton(NEXT_BUTTON_LOCATOR_XPATH, LocatorType.XPATH).isDisplayed(), "Next button is not visible!");
    }

    public void checkIfSearchTopicIsDisplayed() {
        ScenarioLogManager.getLogger().info("Checking if information for topic search is displayed by verifying if elements are visible...");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "h3#navigation--search-label.shepherd-title", LocatorType.CSSSELECTOR, false),
                "Search topic header is not visible!");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "div#navigation--search-description.shepherd-text", LocatorType.CSSSELECTOR, false),
                "Search topic description is not visible!");
        Assert.assertTrue(getEnabledButton(SKIP_BUTTON_LOCATOR_CSS_SELECTOR, LocatorType.CSSSELECTOR).isDisplayed(), "Skip button is not visible!");
        Assert.assertTrue(getEnabledButton(BACK_BUTTON_LOCATOR_XPATH, LocatorType.XPATH).isDisplayed(), "Back button is not visible!");
        Assert.assertTrue(getEnabledButton(NEXT_BUTTON_LOCATOR_XPATH, LocatorType.XPATH).isDisplayed(), "Next button is not visible!");
    }

    public void checkIfColleaguesTopicIsDisplayed() {
        ScenarioLogManager.getLogger().info("Checking if information for topic colleagues is displayed by verifying if elements are visible...");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "h3#navigation--colleagues-label.shepherd-title", LocatorType.CSSSELECTOR, false),
                "Colleagues topic header is not visible!");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "div#navigation--colleagues-description.shepherd-text", LocatorType.CSSSELECTOR, false),
                "Colleagues topic description is not visible!");
        Assert.assertTrue(getEnabledButton(BACK_BUTTON_LOCATOR_XPATH, LocatorType.XPATH).isDisplayed(), "Back button is not visible!");
        Assert.assertTrue(getEnabledButton(END_BUTTON_LOCATOR_XPATH, LocatorType.XPATH).isDisplayed(), "End button is not visible!");
    }

    public void clickOnEndButton() {
        ScenarioLogManager.getLogger().info("Trying to click \"End\" button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, getEnabledButton(END_BUTTON_LOCATOR_XPATH, LocatorType.XPATH), false);
    }

    public void checkIfTourHasEnded() {
        ScenarioLogManager.getLogger().info("Checking if tour has ended...");
        Assert.assertTrue(isWebElementInvisible(DEFAULT_EXPLICIT_WAIT_TIME, ".shepherd-modal-is-visible", LocatorType.CSSSELECTOR),
                "WiLMA is still blocked by tour dialog!");
    }
}
