package ataf.web.pages;

import ataf.core.assertions.CustomAssertions;
import ataf.core.logging.ScenarioLogManager;
import ataf.core.properties.DefaultValues;
import ataf.core.properties.TestProperties;
import ataf.web.interfaces.RetryableOperation;
import ataf.web.model.LocatorType;
import org.jetbrains.annotations.NotNull;
import org.openqa.selenium.Alert;
import org.openqa.selenium.By;
import org.openqa.selenium.ElementNotInteractableException;
import org.openqa.selenium.JavascriptException;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.Keys;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.NoSuchShadowRootException;
import org.openqa.selenium.SearchContext;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebDriverException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.interactions.Actions;
import org.openqa.selenium.interactions.MoveTargetOutOfBoundsException;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedCondition;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;

import java.time.Duration;
import java.util.List;
import java.util.OptionalInt;
import java.util.concurrent.TimeUnit;
import java.util.concurrent.atomic.AtomicReference;
import java.util.stream.IntStream;

/**
 * This is the base class for page objects. All common elements (e.g., menus, footers, etc.), used
 * by multiple pages of the target application, can be defined
 * here.
 *
 * @author Ludwig Haas (ex.haas02), Mohamad Daaeboul
 */
public class BasePage {
    /**
     * Constant WebDriver object of type {@link RemoteWebDriver}
     */
    protected final RemoteWebDriver DRIVER;

    /**
     * Constant for default explicit wait time in seconds. This can be used for all conditional waits.
     */
    protected final int DEFAULT_EXPLICIT_WAIT_TIME;

    /**
     * Constant for default implicit wait time in milliseconds. This wait is executed in between all
     * WebDriver actions.
     */
    protected final long DEFAULT_IMPLICIT_WAIT_TIME;

    /**
     * Constructs a new BasePage object.
     *
     * @param driver the RemoteWebDriver instance used to interact with the browser.
     */
    public BasePage(RemoteWebDriver driver) {
        this.DRIVER = driver;
        this.DEFAULT_EXPLICIT_WAIT_TIME = TestProperties.getProperty("defaultExplicitWaitTime", true, DefaultValues.DEFAULT_EXPLICIT_WAIT_TIME)
                .orElse(DefaultValues.DEFAULT_EXPLICIT_WAIT_TIME);
        this.DEFAULT_IMPLICIT_WAIT_TIME = TestProperties.getProperty("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME)
                .orElse(DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME);
    }

    /**
     * Waits for an alert to be present within the specified timeout.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the alert to be present, in seconds.
     * @return the alert that is present.
     */
    public Alert waitForAlertIsPresent(int explicitWaitTimeOut) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for an alert to be present!");
        return wait.until(ExpectedConditions.alertIsPresent());
    }

    /**
     * Waits for a specified web element to be either clickable or visible within the specified timeout.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the web element, in seconds.
     * @param webElement the web element to wait for.
     * @param checkForClickable if true, wait for the element to be clickable; otherwise, wait for it to
     *            be visible.
     * @return the found web element.
     */
    public WebElement waitForElement(int explicitWaitTimeOut, WebElement webElement, boolean checkForClickable) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        if (checkForClickable) {
            wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element \"" + webElement.toString() + "\" to be clickable!");
            return wait.until(ExpectedConditions.elementToBeClickable(webElement));
        } else {
            wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element \"" + webElement.toString() + "\" to be visible!");
            return wait.until(ExpectedConditions.visibilityOf(webElement));
        }
    }

    /**
     * Waits for an element identified by its ID to be present, visible, or clickable within the
     * specified timeout.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element, in seconds.
     * @param id the ID of the web element to wait for.
     * @param checkForVisible if true, wait for the element to be visible.
     * @param checkForClickable if true, wait for the element to be clickable.
     * @return the found web element.
     */
    public WebElement waitForElementById(int explicitWaitTimeOut, String id, boolean checkForVisible, boolean checkForClickable) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by id attribute \"" + id + "\" to be present!");
        WebElement webElementFound = wait.until(ExpectedConditions.presenceOfElementLocated(By.id(id)));
        if (checkForVisible) {
            wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by id attribute \"" + id + "\" to be visible!");
            webElementFound = wait.until(ExpectedConditions.visibilityOfElementLocated(By.id(id)));
        }
        if (checkForClickable) {
            wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by id attribute \"" + id + "\" to be clickable!");
            webElementFound = wait.until(ExpectedConditions.elementToBeClickable(By.id(id)));
        }
        return webElementFound;
    }

    /**
     * Waits for an element identified by its name to be present, visible, or clickable within the
     * specified timeout.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element, in seconds.
     * @param name the name of the web element to wait for.
     * @param checkForVisible if true, wait for the element to be visible.
     * @param checkForClickable if true, wait for the element to be clickable.
     * @return the found web element.
     */
    public WebElement waitForElementByName(int explicitWaitTimeOut, String name, boolean checkForVisible, boolean checkForClickable) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by name attribute \"" + name + "\" to be present!");
        WebElement webElementFound = wait.until(ExpectedConditions.presenceOfElementLocated(By.name(name)));
        if (checkForVisible) {
            wait.withMessage(
                    "Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by name attribute \"" + name + "\" to be visible!");
            webElementFound = wait.until(ExpectedConditions.visibilityOfElementLocated(By.name(name)));
        }
        if (checkForClickable) {
            wait.withMessage(
                    "Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by name attribute \"" + name + "\" to be clickable!");
            webElementFound = wait.until(ExpectedConditions.elementToBeClickable(By.name(name)));
        }
        return webElementFound;
    }

    /**
     * Waits for an element identified by its XPath to be present, visible, or clickable within the
     * specified timeout.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element, in seconds.
     * @param xpath the XPath of the web element to wait for.
     * @param checkForVisible if true, wait for the element to be visible.
     * @param checkForClickable if true, wait for the element to be clickable.
     * @return the found web element.
     */
    public WebElement waitForElementByXpath(int explicitWaitTimeOut, String xpath, boolean checkForVisible, boolean checkForClickable) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by XPATH \"" + xpath + "\" to be present!");
        WebElement webElementFound = wait.until(ExpectedConditions.presenceOfElementLocated(By.xpath(xpath)));
        if (checkForVisible) {
            wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by XPATH \"" + xpath + "\" to be visible!");
            webElementFound = wait.until(ExpectedConditions.visibilityOfElementLocated(By.xpath(xpath)));
        }
        if (checkForClickable) {
            wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by XPATH \"" + xpath + "\" to be clickable!");
            webElementFound = wait.until(ExpectedConditions.elementToBeClickable(By.xpath(xpath)));
        }
        return webElementFound;
    }

    /**
     * Waits for an element identified by its link text to be present, visible, or clickable within the
     * specified timeout.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element, in seconds.
     * @param linkText the link text of the web element to wait for.
     * @param checkForVisible if true, wait for the element to be visible.
     * @param checkForClickable if true, wait for the element to be clickable.
     * @return the found web element.
     */
    public WebElement waitForElementByLinkText(int explicitWaitTimeOut, String linkText, boolean checkForVisible, boolean checkForClickable) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by link text \"" + linkText + "\" to be present!");
        WebElement webElementFound = wait.until(ExpectedConditions.presenceOfElementLocated(By.linkText(linkText)));
        if (checkForVisible) {
            wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by link text \"" + linkText + "\" to be visible!");
            webElementFound = wait.until(ExpectedConditions.visibilityOfElementLocated(By.linkText(linkText)));
        }
        if (checkForClickable) {
            wait.withMessage(
                    "Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by link text \"" + linkText + "\" to be clickable!");
            webElementFound = wait.until(ExpectedConditions.elementToBeClickable(By.linkText(linkText)));
        }
        return webElementFound;
    }

    /**
     * Waits for an element identified by its class attribute to be present, visible, or clickable
     * within the specified timeout.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element, in seconds.
     * @param classAttribute the class attribute of the web element to wait for.
     * @param checkForVisible if true, wait for the element to be visible.
     * @param checkForClickable if true, wait for the element to be clickable.
     * @return the found web element.
     */
    public WebElement waitForElementByClass(int explicitWaitTimeOut, String classAttribute, boolean checkForVisible, boolean checkForClickable) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage(
                "Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by class attribute \"" + classAttribute + "\" to be present!");
        WebElement webElementFound = wait.until(ExpectedConditions.presenceOfElementLocated(By.className(classAttribute)));
        if (checkForVisible) {
            wait.withMessage(
                    "Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by class attribute \"" + classAttribute
                            + "\" to be visible!");
            webElementFound = wait.until(ExpectedConditions.visibilityOfElementLocated(By.className(classAttribute)));
        }
        if (checkForClickable) {
            wait.withMessage(
                    "Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by class attribute \"" + classAttribute
                            + "\" to be clickable!");
            webElementFound = wait.until(ExpectedConditions.elementToBeClickable(By.className(classAttribute)));
        }
        return webElementFound;
    }

    /**
     * Waits for an element identified by its CSS selector to be present, visible, or clickable within
     * the specified timeout.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element, in seconds.
     * @param cssSelector the CSS selector of the web element to wait for.
     * @param checkForVisible if true, wait for the element to be visible.
     * @param checkForClickable if true, wait for the element to be clickable.
     * @return the found web element.
     */
    public WebElement waitForElementByCssSelector(int explicitWaitTimeOut, String cssSelector, boolean checkForVisible, boolean checkForClickable) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage(
                "Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by CSS selector \"" + cssSelector + "\" to be present!");
        WebElement webElementFound = wait.until(ExpectedConditions.presenceOfElementLocated(By.cssSelector(cssSelector)));
        if (checkForVisible) {
            wait.withMessage(
                    "Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by CSS selector \"" + cssSelector + "\" to be visible!");
            webElementFound = wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(cssSelector)));
        }
        if (checkForClickable) {
            wait.withMessage(
                    "Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by CSS selector \"" + cssSelector + "\" to be clickable!");
            webElementFound = wait.until(ExpectedConditions.elementToBeClickable(By.cssSelector(cssSelector)));
        }
        return webElementFound;
    }

    /**
     * Waits for an element identified by its tag name to be present, visible, or clickable within the
     * specified timeout.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element, in seconds.
     * @param tagName the tag name of the web element to wait for.
     * @param checkForVisible if true, wait for the element to be visible.
     * @param checkForClickable if true, wait for the element to be clickable.
     * @return the found web element.
     */
    public WebElement waitForElementByTagName(int explicitWaitTimeOut, String tagName, boolean checkForVisible, boolean checkForClickable) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by tag name \"" + tagName + "\" to be present!");
        WebElement webElementFound = wait.until(ExpectedConditions.presenceOfElementLocated(By.tagName(tagName)));
        if (checkForVisible) {
            wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by tag name \"" + tagName + "\" to be visible!");
            webElementFound = wait.until(ExpectedConditions.visibilityOfElementLocated(By.tagName(tagName)));
        }
        if (checkForClickable) {
            wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by tag name \"" + tagName + "\" to be clickable!");
            webElementFound = wait.until(ExpectedConditions.elementToBeClickable(By.tagName(tagName)));
        }
        return webElementFound;
    }

    /**
     * Waits for a specified web element to disappear within the specified timeout.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the web element to disappear, in seconds.
     * @param webElement the web element to wait for.
     */
    public void waitForElementToDisappear(int explicitWaitTimeOut, WebElement webElement) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element \"" + webElement.toString() + "\" to disappear!");
        wait.until(ExpectedConditions.invisibilityOf(webElement));
    }

    /**
     * Waits for an element identified by its ID to disappear within the specified timeout.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element to disappear, in seconds.
     * @param id the ID of the web element to wait for.
     */
    public void waitForElementToDisappearById(int explicitWaitTimeOut, String id) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by id attribute \"" + id + "\" to disappear!");
        wait.until(ExpectedConditions.invisibilityOfElementLocated(By.id(id)));
    }

    /**
     * Waits for an element identified by its name to disappear within the specified timeout.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element to disappear, in seconds.
     * @param name the name of the web element to wait for.
     */
    public void waitForElementToDisappearByName(int explicitWaitTimeOut, String name) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by name attribute \"" + name + "\" to disappear!");
        wait.until(ExpectedConditions.invisibilityOfElementLocated(By.name(name)));
    }

    /**
     * Waits for an element identified by its XPath to disappear within the specified timeout.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element to disappear, in seconds.
     * @param xpath the XPath of the web element to wait for.
     */
    public void waitForElementToDisappearByXpath(int explicitWaitTimeOut, String xpath) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by XPATH \"" + xpath + "\" to disappear!");
        wait.until(ExpectedConditions.invisibilityOfElementLocated(By.xpath(xpath)));
    }

    /**
     * Waits for an element identified by its link text to disappear within the specified timeout.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element to disappear, in seconds.
     * @param linkText the link text of the web element to wait for.
     */
    public void waitForElementToDisappearByLinkText(int explicitWaitTimeOut, String linkText) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by link text \"" + linkText + "\" to disappear!");
        wait.until(ExpectedConditions.invisibilityOfElementLocated(By.linkText(linkText)));
    }

    /**
     * Waits for an element identified by its class attribute to disappear within the specified timeout.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element to disappear, in seconds.
     * @param classAttribute the class attribute of the web element to wait for.
     */
    public void waitForElementToDisappearByClass(int explicitWaitTimeOut, String classAttribute) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage(
                "Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by class attribute \"" + classAttribute + "\" to disappear!");
        wait.until(ExpectedConditions.invisibilityOfElementLocated(By.className(classAttribute)));
    }

    /**
     * Waits for an element identified by its CSS selector to disappear within the specified timeout.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element to disappear, in seconds.
     * @param cssSelector the CSS selector of the web element to wait for.
     */
    public void waitForElementToDisappearByCssSelector(int explicitWaitTimeOut, String cssSelector) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage(
                "Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by CSS selector \"" + cssSelector + "\" to disappear!");
        wait.until(ExpectedConditions.invisibilityOfElementLocated(By.cssSelector(cssSelector)));
    }

    /**
     * Waits for an element identified by its tag name to disappear within the specified timeout.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element to disappear, in seconds.
     * @param tagName the tag name of the web element to wait for.
     */
    public void waitForElementToDisappearByTagName(int explicitWaitTimeOut, String tagName) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage("Waited " + explicitWaitTimeOut + " seconds in vain for web element, identified by tag name \"" + tagName + "\" to disappear!");
        wait.until(ExpectedConditions.invisibilityOfElementLocated(By.tagName(tagName)));
    }

    /**
     * Finds a web element by the specified locator type and optional wait conditions.
     *
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param explicitlyWait whether to apply explicit wait conditions
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param explicitlyWaitForVisible whether to wait for the element to be visible
     * @param explicitlyWaitForClickable whether to wait for the element to be clickable
     * @return the found WebElement
     * @throws IllegalArgumentException if the specified locator type is not supported
     */
    public WebElement findElementByLocatorType(String locator, @NotNull LocatorType locatorType, boolean explicitlyWait, int explicitWaitTimeOut,
            boolean explicitlyWaitForVisible, boolean explicitlyWaitForClickable) {
        switch (locatorType) {
            case ID:
                if (explicitlyWait) {
                    return waitForElementById(explicitWaitTimeOut, locator, explicitlyWaitForVisible, explicitlyWaitForClickable);
                } else {
                    return DRIVER.findElement(By.id(locator));
                }
            case NAME:
                if (explicitlyWait) {
                    return waitForElementByName(explicitWaitTimeOut, locator, explicitlyWaitForVisible, explicitlyWaitForClickable);
                } else {
                    return DRIVER.findElement(By.name(locator));
                }
            case XPATH:
                if (explicitlyWait) {
                    return waitForElementByXpath(explicitWaitTimeOut, locator, explicitlyWaitForVisible, explicitlyWaitForClickable);
                } else {
                    return DRIVER.findElement(By.xpath(locator));
                }
            case LINKTEXT:
                if (explicitlyWait) {
                    return waitForElementByLinkText(explicitWaitTimeOut, locator, explicitlyWaitForVisible, explicitlyWaitForClickable);
                } else {
                    return DRIVER.findElement(By.linkText(locator));
                }
            case CLASS:
                if (explicitlyWait) {
                    return waitForElementByClass(explicitWaitTimeOut, locator, explicitlyWaitForVisible, explicitlyWaitForClickable);
                } else {
                    return DRIVER.findElement(By.className(locator));
                }
            case CSSSELECTOR:
                if (explicitlyWait) {
                    return waitForElementByCssSelector(explicitWaitTimeOut, locator, explicitlyWaitForVisible, explicitlyWaitForClickable);
                } else {
                    return DRIVER.findElement(By.cssSelector(locator));
                }
            case TAGNAME:
                if (explicitlyWait) {
                    return waitForElementByTagName(explicitWaitTimeOut, locator, explicitlyWaitForVisible, explicitlyWaitForClickable);
                } else {
                    return DRIVER.findElement(By.tagName(locator));
                }
            case TEXT:
                if (explicitlyWait) {
                    return waitForElementByXpath(explicitWaitTimeOut, "//*[contains(text(), '" + locator + "')]", explicitlyWaitForVisible,
                            explicitlyWaitForClickable);
                } else {
                    return DRIVER.findElement(By.xpath("//*[contains(text(), '" + locator + "')]"));
                }
            default:
                throw new IllegalArgumentException("Locator type " + locatorType + " is not supported by implementation!");
        }
    }

    /**
     * Finds a web element by the specified locator type, with a default explicit wait time.
     *
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param explicitlyWaitForClickable whether to wait for the element to be clickable
     * @return the found WebElement
     */
    public WebElement findElementByLocatorType(String locator, @NotNull LocatorType locatorType, boolean explicitlyWaitForClickable) {
        return findElementByLocatorType(locator, locatorType, true, DEFAULT_EXPLICIT_WAIT_TIME, true, explicitlyWaitForClickable);
    }

    /**
     * Finds a web element by the specified locator type without waiting.
     *
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @return the found WebElement
     */
    public WebElement findElementByLocatorTypeNoWait(String locator, @NotNull LocatorType locatorType) {
        return findElementByLocatorType(locator, locatorType, false, 0, false, false);
    }

    /**
     * Finds a list of web elements by the specified locator type with optional wait conditions.
     *
     * @param implicitWaitTimeOut the time to set for implicit waits
     * @param locator the locator string to find the web elements
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param explicitlyWait whether to apply explicit wait conditions
     * @param explicitWaitTimeOut the maximum time to wait for the elements (in seconds)
     * @param explicitlyWaitForVisible whether to wait for the elements to be visible
     * @param explicitlyWaitForClickable whether to wait for the elements to be clickable
     * @return a list of found WebElements
     */
    public List<WebElement> findElementsByLocatorType(long implicitWaitTimeOut, String locator, @NotNull LocatorType locatorType, boolean explicitlyWait,
            int explicitWaitTimeOut, boolean explicitlyWaitForVisible, boolean explicitlyWaitForClickable) {
        DRIVER.manage().timeouts().implicitlyWait(Duration.ofMillis(implicitWaitTimeOut));
        List<WebElement> liWebElementsResult;
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        liWebElementsResult = switch (locatorType) {
            case ID -> {
                if (explicitlyWait) {
                    wait.withMessage(
                            "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by id attribute \"" + locator
                                    + "\" to be present!");
                    wait.until(ExpectedConditions.presenceOfElementLocated(By.id(locator)));
                    if (explicitlyWaitForVisible) {
                        wait.withMessage(
                                "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by id attribute \"" + locator
                                        + "\" to be visible!");
                        wait.until(ExpectedConditions.visibilityOfElementLocated(By.id(locator)));
                    }
                    if (explicitlyWaitForClickable) {
                        wait.withMessage(
                                "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by id attribute \"" + locator
                                        + "\" to be clickable!");
                        wait.until(ExpectedConditions.elementToBeClickable(By.id(locator)));
                    }
                }
                yield DRIVER.findElements(By.id(locator));
            }
            case NAME -> {
                if (explicitlyWait) {
                    wait.withMessage(
                            "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by name attribute \"" + locator
                                    + "\" to be present!");
                    wait.until(ExpectedConditions.presenceOfElementLocated(By.name(locator)));
                    if (explicitlyWaitForVisible) {
                        wait.withMessage(
                                "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by name attribute \"" + locator
                                        + "\" to be visible!");
                        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name(locator)));
                    }
                    if (explicitlyWaitForClickable) {
                        wait.withMessage(
                                "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by name attribute \"" + locator
                                        + "\" to be clickable!");
                        wait.until(ExpectedConditions.elementToBeClickable(By.name(locator)));
                    }
                }
                yield DRIVER.findElements(By.name(locator));
            }
            case XPATH -> {
                if (explicitlyWait) {
                    wait.withMessage(
                            "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by XPATH \"" + locator + "\" to be present!");
                    wait.until(ExpectedConditions.presenceOfElementLocated(By.xpath(locator)));
                    if (explicitlyWaitForVisible) {
                        wait.withMessage(
                                "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by XPATH \"" + locator
                                        + "\" to be visible!");
                        wait.until(ExpectedConditions.visibilityOfElementLocated(By.xpath(locator)));
                    }
                    if (explicitlyWaitForClickable) {
                        wait.withMessage(
                                "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by XPATH \"" + locator
                                        + "\" to be clickable!");
                        wait.until(ExpectedConditions.elementToBeClickable(By.xpath(locator)));
                    }
                }
                yield DRIVER.findElements(By.xpath(locator));
            }
            case LINKTEXT -> {
                if (explicitlyWait) {
                    wait.withMessage(
                            "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by link text \"" + locator
                                    + "\" to be present!");
                    wait.until(ExpectedConditions.presenceOfElementLocated(By.linkText(locator)));
                    if (explicitlyWaitForVisible) {
                        wait.withMessage(
                                "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by link text \"" + locator
                                        + "\" to be visible!");
                        wait.until(ExpectedConditions.visibilityOfElementLocated(By.linkText(locator)));
                    }
                    if (explicitlyWaitForClickable) {
                        wait.withMessage(
                                "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by link text \"" + locator
                                        + "\" to be clickable!");
                        wait.until(ExpectedConditions.elementToBeClickable(By.linkText(locator)));
                    }
                }
                yield DRIVER.findElements(By.linkText(locator));
            }
            case CLASS -> {
                if (explicitlyWait) {
                    wait.withMessage(
                            "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by class attribute \"" + locator
                                    + "\" to be present!");
                    wait.until(ExpectedConditions.presenceOfElementLocated(By.className(locator)));
                    if (explicitlyWaitForVisible) {
                        wait.withMessage(
                                "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by class attribute \"" + locator
                                        + "\" to be visible!");
                        wait.until(ExpectedConditions.visibilityOfElementLocated(By.className(locator)));
                    }
                    if (explicitlyWaitForClickable) {
                        wait.withMessage(
                                "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by class attribute \"" + locator
                                        + "\" to be clickable!");
                        wait.until(ExpectedConditions.elementToBeClickable(By.className(locator)));
                    }
                }
                yield DRIVER.findElements(By.className(locator));
            }
            case CSSSELECTOR -> {
                if (explicitlyWait) {
                    wait.withMessage(
                            "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by CSS selector \"" + locator
                                    + "\" to be present!");
                    wait.until(ExpectedConditions.presenceOfElementLocated(By.cssSelector(locator)));
                    if (explicitlyWaitForVisible) {
                        wait.withMessage(
                                "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by CSS selector \"" + locator
                                        + "\" to be visible!");
                        wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(locator)));
                    }
                    if (explicitlyWaitForClickable) {
                        wait.withMessage(
                                "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by CSS selector \"" + locator
                                        + "\" to be clickable!");
                        wait.until(ExpectedConditions.elementToBeClickable(By.cssSelector(locator)));
                    }
                }
                yield DRIVER.findElements(By.cssSelector(locator));
            }
            case TAGNAME -> {
                if (explicitlyWait) {
                    wait.withMessage(
                            "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by tag name \"" + locator
                                    + "\" to be present!");
                    wait.until(ExpectedConditions.presenceOfElementLocated(By.tagName(locator)));
                    if (explicitlyWaitForVisible) {
                        wait.withMessage(
                                "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by tag name \"" + locator
                                        + "\" to be visible!");
                        wait.until(ExpectedConditions.visibilityOfElementLocated(By.tagName(locator)));
                    }
                    if (explicitlyWaitForClickable) {
                        wait.withMessage(
                                "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by tag name \"" + locator
                                        + "\" to be clickable!");
                        wait.until(ExpectedConditions.elementToBeClickable(By.tagName(locator)));
                    }
                }
                yield DRIVER.findElements(By.tagName(locator));
            }
            case TEXT -> {
                if (explicitlyWait) {
                    wait.withMessage(
                            "Waited " + explicitWaitTimeOut + " seconds in vain for any web elements, identified by its text \"" + locator
                                    + "\" to be clickable!");
                    waitForElementByXpath(explicitWaitTimeOut, "//*[contains(text(), '" + locator + "')]", explicitlyWaitForVisible,
                            explicitlyWaitForClickable);
                }
                yield DRIVER.findElements(By.xpath("//*[contains(text(), '" + locator + "')]"));
            }
        };
        DRIVER.manage().timeouts().implicitlyWait(Duration.ofMillis(DEFAULT_IMPLICIT_WAIT_TIME));
        return liWebElementsResult;
    }

    /**
     * Finds a list of web elements by the specified locator type, with a default explicit wait time and
     * visibility.
     *
     * @param locator the locator string to find the web elements
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param explicitlyWait whether to apply explicit wait conditions
     * @param explicitWaitTimeOut the maximum time to wait for the elements (in seconds)
     * @param explicitlyWaitForClickable whether to wait for the elements to be clickable
     * @return a list of found WebElements
     */
    public List<WebElement> findElementsByLocatorType(String locator, @NotNull LocatorType locatorType, boolean explicitlyWait, int explicitWaitTimeOut,
            boolean explicitlyWaitForClickable) {
        return findElementsByLocatorType(600L, locator, locatorType, explicitlyWait, explicitWaitTimeOut, true, explicitlyWaitForClickable);
    }

    /**
     * Finds a list of web elements by the specified locator type without waiting.
     *
     * @param implicitWaitTimeOut the time to set for implicit waits
     * @param locator the locator string to find the web elements
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @return a list of found WebElements
     */
    public List<WebElement> findElementsByLocatorType(long implicitWaitTimeOut, String locator, @NotNull LocatorType locatorType) {
        return findElementsByLocatorType(implicitWaitTimeOut, locator, locatorType, false, 0, false, false);
    }

    /**
     * Waits for the page to load completely by checking the ready state.
     *
     * @throws IllegalStateException if the page is not ready after the timeout
     */
    public void waitForPageToLoad() {
        long millis = DEFAULT_IMPLICIT_WAIT_TIME;
        int iterations = 1;
        do {
            try {
                Thread.sleep(millis);
            } catch (InterruptedException e) {
                ScenarioLogManager.getLogger().error("Wait for page to load has been interrupted!", e);
            }
            iterations++;
        } while (!isPageInReadyState() || ((millis * iterations) / 1000.0) > DEFAULT_EXPLICIT_WAIT_TIME);

        if (!isPageInReadyState()) {
            throw new IllegalStateException("Page was still not ready after the timeout.");
        }
    }

    /**
     * Checks if the page is in the ready state.
     *
     * @return true if the page is ready, false otherwise
     */
    private boolean isPageInReadyState() {
        return ((JavascriptExecutor) DRIVER).executeScript("return document.readyState").toString().equals("complete");
    }

    /**
     * Waits for both JavaScript and jQuery to load completely.
     */
    public void waitForJSandJQueryToLoad() {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.withMessage("Waited " + DEFAULT_EXPLICIT_WAIT_TIME + " seconds in vain for JavaScript and jQuery to load!");

        // wait for jQuery to load
        ExpectedCondition<Boolean> jQueryLoad = waitDriver -> {
            CustomAssertions.assertNotNull(waitDriver, "WebDriver object for waits must not be null!");
            try {
                boolean result = ((Long) ((JavascriptExecutor) waitDriver).executeScript("return jQuery.active") == 0);
                try {
                    Thread.sleep(DEFAULT_IMPLICIT_WAIT_TIME);
                } catch (InterruptedException ignore) {
                }
                return result;
            } catch (Exception e) {
                // no jQuery present
                return true;
            }
        };

        // wait for JavaScript to load
        ExpectedCondition<Boolean> jsLoad = waitDriver -> {
            CustomAssertions.assertNotNull(waitDriver, "WebDriver object for waits must not be null!");
            boolean result = ((JavascriptExecutor) waitDriver).executeScript("return document.readyState").toString().equals("complete");
            try {
                Thread.sleep(DEFAULT_IMPLICIT_WAIT_TIME);
            } catch (InterruptedException ignore) {
            }
            return result;
        };

        wait.until(jQueryLoad);
        wait.until(jsLoad);
    }

    /**
     * Checks if a given web element is visible in the viewport.
     *
     * @param element the web element to check
     * @return true if the element is visible in the viewport, false otherwise
     */
    public boolean isWebElementVisibleInViewport(WebElement element) {
        return (boolean) ((JavascriptExecutor) DRIVER).executeScript(
                "var elem = arguments[0], box = elem.getBoundingClientRect(), cx = box.left + box.width / 2, cy = box.top + box.height / 2,  e = document.elementFromPoint(cx, cy); for (; e; e = e.parentElement) { if (e === elem) return true; } return false;",
                element);
    }

    /**
     * Moves to the specified web element, scrolling to bring it into view if necessary.
     *
     * @param webElement the web element to move to
     */
    public void moveToElementAction(WebElement webElement) {
        try {
            if (!isWebElementVisibleInViewport(webElement)) {
                scrollToCenterByVisibleElement(webElement);
                new Actions(DRIVER).moveToElement(webElement).build().perform();
            }
        } catch (MoveTargetOutOfBoundsException | JavascriptException ignore) {
        }
    }

    /**
     * Selects a web element identified by the specified locator type, waiting until it is selectable.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     */
    public void selectWebElement(int explicitWaitTimeOut, String locator, LocatorType locatorType) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage(
                "Tried to select web element, identified by locator (" + locatorType + ") \"" + locator + "\" for " + explicitWaitTimeOut + " seconds!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            try {
                moveToElementAction(findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, false, false));
                WebElement welToSelect = findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, true, true);
                if (!welToSelect.isSelected()) {
                    welToSelect.sendKeys(Keys.SPACE);
                }
                return true;
            } catch (StaleElementReferenceException | ElementNotInteractableException exp) {
                return false;
            }
        });
    }

    /**
     * Deselects a web element identified by the specified locator type, waiting until it is
     * deselectable.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     */
    public void deselectWebElement(int explicitWaitTimeOut, String locator, LocatorType locatorType) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage(
                "Tried to deselect web element, identified by locator (" + locatorType + ") \"" + locator + "\" for " + explicitWaitTimeOut + " seconds!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            try {
                moveToElementAction(findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, false, false));
                WebElement welToDeselect = findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, true, true);
                if (welToDeselect.isSelected()) {
                    welToDeselect.sendKeys(Keys.SPACE);
                }
                return true;
            } catch (StaleElementReferenceException | ElementNotInteractableException exp) {
                return false;
            }
        });
    }

    /**
     * Clicks on a web element identified by the specified locator.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param waitForClickable indicates whether to wait until the element is clickable
     * @param useJavaScriptForClick indicates whether to use JavaScript to click the element
     * @param context optional context to set during the click action
     */
    public void clickOnWebElement(int explicitWaitTimeOut, String locator, LocatorType locatorType, boolean waitForClickable, boolean useJavaScriptForClick,
            Context context) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage(
                "Tried to click on web element, identified by locator (" + locatorType + ") \"" + locator + "\" for " + explicitWaitTimeOut + " seconds!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            CustomAssertions.assertNotNull(waitDriver, "WebDriver object for waits must not be null!");
            try {
                if (context != null) {
                    context.set();
                }
                moveToElementAction(findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, false, false));
                WebElement webElement = findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, true, waitForClickable);
                if (useJavaScriptForClick) {
                    ((JavascriptExecutor) waitDriver).executeScript("arguments[0].click();", webElement);
                    try {
                        Thread.sleep(DEFAULT_IMPLICIT_WAIT_TIME);
                    } catch (InterruptedException ignore) {
                    }
                } else {
                    webElement.click();
                }
                return true;
            } catch (StaleElementReferenceException | ElementNotInteractableException exp) {
                return false;
            }
        });
    }

    /**
     * Clicks on a web element identified by the specified locator, using default parameters.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param waitForClickable indicates whether to wait until the element is clickable
     * @param useJavaScriptForClick indicates whether to use JavaScript to click the element
     */
    public void clickOnWebElement(int explicitWaitTimeOut, String locator, LocatorType locatorType, boolean waitForClickable, boolean useJavaScriptForClick) {
        clickOnWebElement(explicitWaitTimeOut, locator, locatorType, waitForClickable, useJavaScriptForClick, null);
    }

    /**
     * Clicks on a web element identified by the specified locator, using JavaScript to click.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param useJavaScriptForClick indicates whether to use JavaScript to click the element
     * @param context optional context to set during the click action
     */
    public void clickOnWebElement(int explicitWaitTimeOut, String locator, LocatorType locatorType, boolean useJavaScriptForClick, Context context) {
        clickOnWebElement(explicitWaitTimeOut, locator, locatorType, true, useJavaScriptForClick, context);
    }

    /**
     * Clicks on a web element identified by the specified locator, using JavaScript to click with
     * default parameters.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param useJavaScriptForClick indicates whether to use JavaScript to click the element
     */
    public void clickOnWebElement(int explicitWaitTimeOut, String locator, LocatorType locatorType, boolean useJavaScriptForClick) {
        clickOnWebElement(explicitWaitTimeOut, locator, locatorType, true, useJavaScriptForClick, null);
    }

    /**
     * Clicks on a specified web element, using JavaScript to click.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param webElement the web element to be clicked
     * @param useJavaScriptForClick indicates whether to use JavaScript to click the element
     * @param context optional context to set during the click action
     */
    public void clickOnWebElement(int explicitWaitTimeOut, WebElement webElement, boolean useJavaScriptForClick, Context context) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage("Tried to click on web element \"" + webElement.toString() + "\" for " + explicitWaitTimeOut + " seconds!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            CustomAssertions.assertNotNull(waitDriver, "WebDriver object for waits must not be null!");
            try {
                if (context != null) {
                    context.set();
                }
                moveToElementAction(webElement);
                if (useJavaScriptForClick) {
                    ((JavascriptExecutor) waitDriver).executeScript("arguments[0].click();", webElement);
                    try {
                        Thread.sleep(DEFAULT_IMPLICIT_WAIT_TIME);
                    } catch (InterruptedException ignore) {
                    }
                } else {
                    webElement.click();
                }
                return true;
            } catch (StaleElementReferenceException | ElementNotInteractableException exp) {
                return false;
            }
        });
    }

    /**
     * Clicks on a specified web element with default parameters.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param webElement the web element to be clicked
     * @param useJavaScriptForClick indicates whether to use JavaScript to click the element
     */
    public void clickOnWebElement(int explicitWaitTimeOut, WebElement webElement, boolean useJavaScriptForClick) {
        clickOnWebElement(explicitWaitTimeOut, webElement, useJavaScriptForClick, null);
    }

    /**
     * Closes popups by clicking on elements identified by the specified locator.
     *
     * @param explicitWaitTimeOut the maximum time to wait for each element (in seconds)
     * @param locator the locator string to find the popup elements
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     */
    public void closePopupsByClickingOnElements(int explicitWaitTimeOut, String locator, LocatorType locatorType) {
        List<WebElement> lisPopups = findElementsByLocatorType(locator, locatorType, false, 0, false);
        if (!lisPopups.isEmpty()) {
            lisPopups.forEach(webElement -> {
                moveToElementAction(webElement);
                waitForElement(explicitWaitTimeOut, webElement, true);
                clickOnWebElement(explicitWaitTimeOut, webElement, true);
                waitForElementToDisappear(explicitWaitTimeOut, webElement);
            });
        }
    }

    /**
     * Enters text into a web element identified by the specified locator.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param text the text to enter into the web element
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param clear indicates whether to clear the existing text before entering new text
     * @param useJavaScript indicates whether to use JavaScript to enter text
     */
    public void enterTextInWebElement(int explicitWaitTimeOut, String text, String locator, LocatorType locatorType, boolean clear, boolean useJavaScript) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage(
                "Tried to enter text in web element, identified by locator (" + locatorType + ") \"" + locator + "\" for " + explicitWaitTimeOut + " seconds!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            CustomAssertions.assertNotNull(waitDriver, "WebDriver object for waits must not be null!");
            try {
                moveToElementAction(findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, false, false));
                WebElement welToEnterTextIn = findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, true, true);
                if (clear) {
                    welToEnterTextIn.clear();
                }
                if (!text.isEmpty()) {
                    if (useJavaScript) {
                        ((JavascriptExecutor) waitDriver).executeScript("arguments[0].value = '" + text + "'", welToEnterTextIn);
                        try {
                            Thread.sleep(DEFAULT_IMPLICIT_WAIT_TIME);
                        } catch (InterruptedException ignore) {
                        }
                    } else {
                        welToEnterTextIn.sendKeys(text);
                    }
                }
                return true;
            } catch (StaleElementReferenceException | ElementNotInteractableException exp) {
                return false;
            }
        });
    }

    /**
     * Enters text into a web element identified by the specified locator with default parameters.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param text the text to enter into the web element
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     */
    public void enterTextInWebElement(int explicitWaitTimeOut, String text, String locator, LocatorType locatorType) {
        enterTextInWebElement(explicitWaitTimeOut, text, locator, locatorType, true, false);
    }

    /**
     * Enters text into a specified web element, optionally clearing existing text and using JavaScript
     * for input.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param text the text to enter into the web element
     * @param webElement the web element to enter text into
     * @param clear indicates whether to clear existing text before entering new text
     * @param useJavaScript indicates whether to use JavaScript to enter text
     */
    public void enterTextInWebElement(int explicitWaitTimeOut, String text, WebElement webElement, boolean clear, boolean useJavaScript) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage("Tried to enter text in web element \"" + webElement.toString() + "\" for " + explicitWaitTimeOut + " seconds!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            CustomAssertions.assertNotNull(waitDriver, "WebDriver object for waits must not be null!");
            try {
                moveToElementAction(webElement);
                if (clear) {
                    webElement.clear();
                }
                if (!text.isEmpty()) {
                    if (useJavaScript) {
                        ((JavascriptExecutor) waitDriver).executeScript("arguments[0].value = '" + text + "'", webElement);
                        try {
                            Thread.sleep(DEFAULT_IMPLICIT_WAIT_TIME);
                        } catch (InterruptedException ignore) {
                        }
                    } else {
                        webElement.sendKeys(text);
                    }
                }
                return true;
            } catch (StaleElementReferenceException | ElementNotInteractableException exp) {
                return false;
            }
        });
    }

    /**
     * Enters text into a specified web element using default parameters (clears existing text and does
     * not use JavaScript).
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param text the text to enter into the web element
     * @param webElement the web element to enter text into
     */
    public void enterTextInWebElement(int explicitWaitTimeOut, String text, WebElement webElement) {
        enterTextInWebElement(explicitWaitTimeOut, text, webElement, true, false);
    }

    /**
     * Selects a value from a dropdown list by its visible text.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the dropdown element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param dropBoxValue the visible text of the option to select
     */
    public void selectDropDownListValueByVisibleText(int explicitWaitTimeOut, String locator, LocatorType locatorType, String dropBoxValue) {
        checkForTextInDropdown(locator, dropBoxValue, locatorType);
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage(
                "Tried to select drop down list value by visible text, identified by locator (" + locatorType + ") \"" + locator + "\" for "
                        + explicitWaitTimeOut + " seconds!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            try {
                moveToElementAction(findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, false, false));
                WebElement webElement = findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, true, true);
                Select selToSelectFrom = new Select(webElement);
                if (!selToSelectFrom.getFirstSelectedOption().getText().equals(dropBoxValue)) {
                    selToSelectFrom.selectByVisibleText(dropBoxValue);
                }
                return true;
            } catch (StaleElementReferenceException | ElementNotInteractableException exp) {
                return false;
            }
        });
    }

    /**
     * Selects a value from a dropdown list that contains the specified visible text.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the dropdown element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param dropBoxValue the visible text that should be contained in the option to select
     */
    public void selectDropDownListValueByContainingVisibleText(int explicitWaitTimeOut, String locator, LocatorType locatorType, String dropBoxValue) {
        checkForTextInDropdown(locator, dropBoxValue, locatorType);
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage(
                "Tried to select drop down list value by containing visible text, identified by locator (" + locatorType + ") \"" + locator + "\" for "
                        + explicitWaitTimeOut + " seconds!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            try {
                moveToElementAction(findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, false, false));
                WebElement selectElement = findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, true, true);
                Select selToSelectFrom = new Select(selectElement);
                List<WebElement> options = selToSelectFrom.getOptions();
                for (WebElement option : options) {
                    if (option.getText().contains(dropBoxValue)) {
                        selToSelectFrom.selectByVisibleText(option.getText());
                    }
                }
                return true;
            } catch (StaleElementReferenceException | ElementNotInteractableException exp) {
                return false;
            }
        });
    }

    /**
     * Selects a value from a dropdown list using the visible text of a specific WebElement.
     *
     * @param selectElement the dropdown WebElement to select from
     * @param dropBoxValue the visible text of the option to select
     */
    public void selectDropDownListValueByVisibleText(WebElement selectElement, String dropBoxValue) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            try {
                Select selToSelectFrom = new Select(selectElement);
                moveToElementAction(selectElement);
                if (!selToSelectFrom.getFirstSelectedOption().getText().equals(dropBoxValue)) {
                    selToSelectFrom.selectByVisibleText(dropBoxValue);
                }
                return true;
            } catch (StaleElementReferenceException | ElementNotInteractableException exp) {
                return false;
            }
        });
    }

    /**
     * Selects a value from a dropdown list using the value attribute of a specific WebElement.
     *
     * @param selectElement the dropdown WebElement to select from
     * @param dropBoxValue the value attribute of the option to select
     */
    public void selectDropDownListValueByValue(WebElement selectElement, String dropBoxValue) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            try {
                Select selToSelectFrom = new Select(selectElement);
                moveToElementAction(selectElement);
                String value = selToSelectFrom.getFirstSelectedOption().getDomProperty("value");
                if (value != null && !value.equals(dropBoxValue)) {
                    selToSelectFrom.selectByValue(dropBoxValue);
                }
                return true;
            } catch (StaleElementReferenceException | ElementNotInteractableException exp) {
                return false;
            }
        });
    }

    /**
     * Selects a value from a dropdown list identified by its value attribute.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the dropdown element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param dropBoxValue the value attribute of the option to select
     */
    public void selectDropDownListValueByValue(int explicitWaitTimeOut, String locator, LocatorType locatorType, String dropBoxValue) {
        checkForValueInDropdown(locator, dropBoxValue, locatorType);
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage(
                "Tried to select drop down list value by value, identified by locator (" + locatorType + ") \"" + locator + "\" for " + explicitWaitTimeOut
                        + " seconds!");

        WebElement webElement = findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, true, true);
        selectDropDownListValueByValue(webElement, dropBoxValue);
    }

    /**
     * Selects a value from a multi-select dropdown list.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the multi-select element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param multiListValue the visible text of the option to select
     */
    public void selectMultiListValue(int explicitWaitTimeOut, String locator, LocatorType locatorType, String multiListValue) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage(
                "Tried to select multi list value, identified by locator (" + locatorType + ") \"" + locator + "\" for " + explicitWaitTimeOut + " seconds!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            try {
                moveToElementAction(findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, false, false));
                WebElement webElement = findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, true, true);
                new Select(webElement).selectByVisibleText(multiListValue);
                return true;
            } catch (StaleElementReferenceException | ElementNotInteractableException exp) {
                return false;
            }
        });
    }

    /**
     * Selects an option from a dropdown list by its value attribute.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param value the value attribute of the option to select
     */
    public void selectDropdownByValue(int explicitWaitTimeOut, String value) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage("Tried to select drop down by value for " + explicitWaitTimeOut + " seconds!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            try {
                String xpath = "//option[@value='" + value + "']//..";
                moveToElementAction(findElementByLocatorType(xpath, LocatorType.XPATH, true, explicitWaitTimeOut, false, false));
                WebElement select = findElementByLocatorType(xpath, LocatorType.XPATH, true, explicitWaitTimeOut, true, false);
                new Select(select).selectByValue(value);
                return true;
            } catch (StaleElementReferenceException | ElementNotInteractableException exp) {
                return false;
            }
        });
    }

    /**
     * Checks the text of a web element against a specified string using a specified check method.
     *
     * @param ignoreTimeout whether to ignore the timeout exception if the element is not found
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param CheckString the string to check against the web element's text
     * @param checkMethod the method of comparison (e.g., "EQUALS", "CONTAINS", "STARTSWITH",
     *            "ENDSWITH", "REGEXP")
     * @return true if the web element's text matches the criteria; false otherwise
     * @throws IllegalArgumentException if an unsupported check method is provided
     */
    private boolean checkWebElementText(boolean ignoreTimeout, int explicitWaitTimeOut, String locator, LocatorType locatorType, String CheckString,
            @NotNull String checkMethod) {
        AtomicReference<Boolean> atmblnResult = new AtomicReference<>(false);
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage(
                "Tried to check web element text, identified by locator (" + locatorType + ") \"" + locator + "\" witch method \"" + checkMethod + "\" for "
                        + explicitWaitTimeOut + " seconds!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            try {
                moveToElementAction(findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, false, false));
                WebElement welToCheckText = findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, true, false);
                String TextToCheck = welToCheckText.getText();

                // switch for check method
                ScenarioLogManager.getLogger()
                        .info("Checking result \"{}\" with expected text \"{}\" using method \"{}\"", TextToCheck, CheckString, checkMethod);
                switch (checkMethod.toUpperCase()) {
                    case "EQUALS":
                        atmblnResult.set(TextToCheck.equals(CheckString));
                        break;
                    case "CONTAINS":
                        atmblnResult.set(welToCheckText.getText().contains(CheckString));
                        break;
                    case "STARTSWITH":
                        atmblnResult.set(welToCheckText.getText().startsWith(CheckString));
                        break;
                    case "ENDSWITH":
                        atmblnResult.set(welToCheckText.getText().endsWith(CheckString));
                        break;
                    case "REGEXP":
                        atmblnResult.set(welToCheckText.getText().matches(CheckString));
                        break;
                    default:
                        throw new IllegalArgumentException("Check method " + checkMethod + " is not supported by implementation!");
                }
                return atmblnResult.get();
            } catch (StaleElementReferenceException | ElementNotInteractableException exp) {
                return false;
            } catch (TimeoutException exp) {
                if (ignoreTimeout) {
                    return true;
                } else {
                    throw exp;
                }
            }
        });
        return atmblnResult.get();
    }

    /**
     * Checks the text of a web element for equality with a specified string.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param StringToCheckFor the string to check against the web element's text
     * @return true if the web element's text matches the specified string; false otherwise
     */
    public boolean checkWebElementTextUsingEquals(int explicitWaitTimeOut, String locator, LocatorType locatorType, String StringToCheckFor) {
        return checkWebElementText(false, explicitWaitTimeOut, locator, locatorType, StringToCheckFor, "EQUALS");
    }

    /**
     * Checks the text of a web element for equality with a specified string, with an option to ignore
     * timeout.
     *
     * @param ignoreTimeout whether to ignore the timeout exception if the element is not found
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param StringToCheckFor the string to check against the web element's text
     * @return true if the web element's text matches the specified string; false otherwise
     */
    public boolean checkWebElementTextUsingEquals(boolean ignoreTimeout, int explicitWaitTimeOut, String locator, LocatorType locatorType,
            String StringToCheckFor) {
        return checkWebElementText(ignoreTimeout, explicitWaitTimeOut, locator, locatorType, StringToCheckFor, "EQUALS");
    }

    /**
     * Checks if the text of a web element contains a specified string.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param StringToCheckFor the string to check against the web element's text
     * @return true if the web element's text contains the specified string; false otherwise
     */
    public boolean checkWebElementTextUsingContains(int explicitWaitTimeOut, String locator, LocatorType locatorType, String StringToCheckFor) {
        return checkWebElementText(false, explicitWaitTimeOut, locator, locatorType, StringToCheckFor, "CONTAINS");
    }

    /**
     * Checks if the text of a web element contains a specified string, with an option to ignore
     * timeout.
     *
     * @param ignoreTimeout whether to ignore the timeout exception if the element is not found
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param StringToCheckFor the string to check against the web element's text
     * @return true if the web element's text contains the specified string; false otherwise
     */
    public boolean checkWebElementTextUsingContains(boolean ignoreTimeout, int explicitWaitTimeOut, String locator, LocatorType locatorType,
            String StringToCheckFor) {
        return checkWebElementText(ignoreTimeout, explicitWaitTimeOut, locator, locatorType, StringToCheckFor, "CONTAINS");
    }

    /**
     * Checks if the text of a web element starts with a specified string.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param StringToCheckFor the string to check against the web element's text
     * @return true if the web element's text starts with the specified string; false otherwise
     */
    public boolean checkWebElementTextUsingStartswith(int explicitWaitTimeOut, String locator, LocatorType locatorType, String StringToCheckFor) {
        return checkWebElementText(false, explicitWaitTimeOut, locator, locatorType, StringToCheckFor, "STARTSWITH");
    }

    /**
     * Checks if the text of a web element starts with a specified string, with an option to ignore
     * timeout.
     *
     * @param ignoreTimeout whether to ignore the timeout exception if the element is not found
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param StringToCheckFor the string to check against the web element's text
     * @return true if the web element's text starts with the specified string; false otherwise
     */
    public boolean checkWebElementTextUsingStartswith(boolean ignoreTimeout, int explicitWaitTimeOut, String locator, LocatorType locatorType,
            String StringToCheckFor) {
        return checkWebElementText(ignoreTimeout, explicitWaitTimeOut, locator, locatorType, StringToCheckFor, "STARTSWITH");
    }

    /**
     * Checks if the text of a web element ends with a specified string.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param StringToCheckFor the string to check against the web element's text
     * @return true if the web element's text ends with the specified string; false otherwise
     */
    public boolean checkWebElementTextUsingEndswith(int explicitWaitTimeOut, String locator, LocatorType locatorType, String StringToCheckFor) {
        return checkWebElementText(false, explicitWaitTimeOut, locator, locatorType, StringToCheckFor, "ENDSWITH");
    }

    /**
     * Checks if the text of a web element ends with a specified string, with an option to ignore
     * timeout.
     *
     * @param ignoreTimeout whether to ignore the timeout exception if the element is not found
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param StringToCheckFor the string to check against the web element's text
     * @return true if the web element's text ends with the specified string; false otherwise
     */
    public boolean checkWebElementTextUsingEndswith(boolean ignoreTimeout, int explicitWaitTimeOut, String locator, LocatorType locatorType,
            String StringToCheckFor) {
        return checkWebElementText(ignoreTimeout, explicitWaitTimeOut, locator, locatorType, StringToCheckFor, "ENDSWITH");
    }

    /**
     * Checks if the text of a web element matches a specified regular expression.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param StringToCheckFor the regular expression to check against the web element's text
     * @return true if the web element's text matches the specified regular expression; false otherwise
     */
    public boolean checkWebElementTextUsingRegexp(int explicitWaitTimeOut, String locator, LocatorType locatorType, String StringToCheckFor) {
        return checkWebElementText(false, explicitWaitTimeOut, locator, locatorType, StringToCheckFor, "REGEXP");
    }

    /**
     * Checks if the text of a web element matches a specified regular expression, with an option to
     * ignore timeout.
     *
     * @param ignoreTimeout whether to ignore the timeout exception if the element is not found
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param StringToCheckFor the regular expression to check against the web element's text
     * @return true if the web element's text matches the specified regular expression; false otherwise
     */
    public boolean checkWebElementTextUsingRegexp(boolean ignoreTimeout, int explicitWaitTimeOut, String locator, LocatorType locatorType,
            String StringToCheckFor) {
        return checkWebElementText(ignoreTimeout, explicitWaitTimeOut, locator, locatorType, StringToCheckFor, "REGEXP");
    }

    /**
     * Scrolls the page vertically to bring a specified web element into view.
     *
     * @param webElement the web element to scroll to
     */
    public void scrollVerticallyByVisibleElement(WebElement webElement) {
        ((JavascriptExecutor) DRIVER).executeScript("arguments[0].scrollIntoView();", webElement);
        try {
            Thread.sleep(DEFAULT_IMPLICIT_WAIT_TIME);
        } catch (InterruptedException ignore) {
        }
    }

    /**
     * Scrolls the page to the center of a specified web element.
     *
     * @param webElement the web element to scroll to
     */
    public void scrollToCenterByVisibleElement(WebElement webElement) {
        ((JavascriptExecutor) DRIVER).executeScript("arguments[0].scrollIntoView({block: 'center'});", webElement);
        try {
            Thread.sleep(DEFAULT_IMPLICIT_WAIT_TIME);
        } catch (InterruptedException ignore) {
        }
    }

    /**
     * Scrolls the page vertically to the bottom.
     */
    public void scrollVerticallyToBottomOfPage() {
        ((JavascriptExecutor) DRIVER).executeScript("window.scrollTo(0, document.body.scrollHeight)");
        try {
            Thread.sleep(DEFAULT_IMPLICIT_WAIT_TIME);
        } catch (InterruptedException ignore) {
        }
    }

    /**
     * Retrieves the text of a web element after waiting for it to be visible.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @param context an optional context object for additional actions
     * @return the text of the web element, or an empty string if not found
     */
    public String getWebElementText(int explicitWaitTimeOut, String locator, LocatorType locatorType, Context context) {
        AtomicReference<String> atmstrResult = new AtomicReference<>("");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage(
                "Tried to get web element text, identified by locator (" + locatorType + ") \"" + locator + "\" for " + explicitWaitTimeOut + " seconds!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            try {
                if (context != null) {
                    context.set();
                }
                moveToElementAction(findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, false, false));
                WebElement welToGetTextFrom = findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, true, false);
                atmstrResult.set(welToGetTextFrom.getText());
                return true;
            } catch (StaleElementReferenceException | ElementNotInteractableException exp) {
                return false;
            }
        });
        return atmstrResult.get();
    }

    /**
     * Retrieves the text of a web element after waiting for it to be visible.
     *
     * @param explicitWaitTimeOut the maximum time to wait for the element (in seconds)
     * @param locator the locator string to find the web element
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     * @return the text of the web element, or an empty string if not found
     */
    public String getWebElementText(int explicitWaitTimeOut, String locator, LocatorType locatorType) {
        return getWebElementText(explicitWaitTimeOut, locator, locatorType, null);
    }

    /**
     * Generates an XPath expression for multiple content predicates based on specified criteria.
     *
     * @param contains whether to use contains or exact match in the XPath expression
     * @param split the strings to include in the XPath expression
     * @return the generated XPath expression
     */
    public String getMultipleContentPredicateXpath(Boolean contains, String... split) {
        StringBuilder xpath = new StringBuilder();
        for (String string : split) {
            if (!xpath.isEmpty()) {
                xpath.append(" or ");
            }
            if (contains) {
                xpath.append("contains(., '").append(string).append("')");
            } else {
                xpath.append("text()='").append(string).append("'");
            }
        }
        return "[" + xpath + "]";
    }

    /**
     * Expands the shadow root of a specified web element.
     *
     * @param shadowHost the web element that is a shadow host
     * @return the expanded shadow root as a SearchContext
     */
    public SearchContext expandShadowRootElement(WebElement shadowHost) {
        AtomicReference<SearchContext> shadowRootElement = new AtomicReference<>();
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.withMessage("Tried to expand shadow root of web element (" + shadowHost + ") for " + DEFAULT_EXPLICIT_WAIT_TIME + " seconds!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            try {
                SearchContext shadowRoot = shadowHost.getShadowRoot();
                shadowRootElement.set(shadowRoot);
                return true;
            } catch (NoSuchShadowRootException e) {
                ScenarioLogManager.getLogger().debug("NoSuchShadowRootException caught!", e);
                return false;
            }
        });
        return shadowRootElement.get();
    }

    /**
     * Checks if a specified value is present in a dropdown menu identified by its locator.
     *
     * @param locator the locator string to find the dropdown web element
     * @param valueToSelect the value to check for in the dropdown options
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     */
    public void checkForTextInDropdown(String locator, String valueToSelect, LocatorType locatorType) {
        AtomicReference<Select> selectAtomicReference = new AtomicReference<>();
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.withMessage(
                "Tried to check text in drop down, identified by locator (" + locatorType + ") \"" + locator + "\" for " + DEFAULT_EXPLICIT_WAIT_TIME
                        + " seconds!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            try {
                moveToElementAction(findElementByLocatorType(locator, locatorType, true, DEFAULT_EXPLICIT_WAIT_TIME, false, false));
                WebElement webElement = findElementByLocatorType(locator, locatorType, true, DEFAULT_EXPLICIT_WAIT_TIME, true, false);
                selectAtomicReference.set(new Select(webElement));
                return true;
            } catch (StaleElementReferenceException | ElementNotInteractableException exp) {
                return false;
            }
        });

        List<WebElement> selectOptions = selectAtomicReference.get().getOptions();
        StringBuilder optionText = new StringBuilder();
        for (WebElement selectOption : selectOptions) {
            optionText.append(selectOption.getText()).append(" | ");
        }
        if (optionText.toString().contains(valueToSelect)) {
            ScenarioLogManager.getLogger().info("Text \"{}\" found in Dropdown", valueToSelect);
        } else {
            throw new IllegalArgumentException(
                    "Text \"" + valueToSelect + "\" NOT found in Dropdown. Valid Options: " + optionText.substring(0, optionText.length() - 2));
        }
    }

    /**
     * Checks if a specified value is present in a dropdown menu identified by its locator.
     *
     * @param locator the locator string to find the dropdown web element
     * @param valueToSelect the value to check for in the dropdown options
     * @param locatorType the type of locator (e.g., ID, NAME, XPATH, etc.)
     */
    public void checkForValueInDropdown(String locator, String valueToSelect, LocatorType locatorType) {
        AtomicReference<Select> selectAtomicReference = new AtomicReference<>();
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.withMessage(
                "Tried to check value in drop down, identified by locator (" + locatorType + ") \"" + locator + "\" for " + DEFAULT_EXPLICIT_WAIT_TIME
                        + " seconds!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            try {
                moveToElementAction(findElementByLocatorType(locator, locatorType, true, DEFAULT_EXPLICIT_WAIT_TIME, false, false));
                WebElement webElement = findElementByLocatorType(locator, locatorType, true, DEFAULT_EXPLICIT_WAIT_TIME, true, false);
                selectAtomicReference.set(new Select(webElement));
                return true;
            } catch (StaleElementReferenceException | ElementNotInteractableException exp) {
                return false;
            }
        });

        List<WebElement> selectOptions = selectAtomicReference.get().getOptions();
        StringBuilder optionText = new StringBuilder();
        for (WebElement selectOption : selectOptions) {
            optionText.append(selectOption.getDomProperty("value")).append(" | ");
        }
        if (optionText.toString().contains(valueToSelect)) {
            ScenarioLogManager.getLogger().info("Value \"{}\" found in Dropdown", valueToSelect);
        } else {
            throw new IllegalArgumentException(
                    "Value \"" + valueToSelect + "\" NOT found in Dropdown. Valid Options: \"" + optionText.substring(0, optionText.length() - 2));
        }
    }

    /**
     * Refreshes the current browser window and waits for the page to load completely.
     * <p>
     * This method uses WebDriver to refresh the browser window and then employs an explicit wait to
     * ensure the page is fully loaded before proceeding.
     */
    public void refreshBrowser() {
        ScenarioLogManager.getLogger().info("Trying to refresh browser window...");
        DRIVER.navigate().refresh();
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.withMessage("Waited " + DEFAULT_EXPLICIT_WAIT_TIME + " seconds in vain for the browser window to be refreshed!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            try {
                if (isPageInReadyState()) {
                    waitForPageToLoad();
                    waitForJSandJQueryToLoad();
                    return true;
                }
            } catch (JavascriptException ignore) {
            }
            return false;
        });
    }

    /**
     * Checks the visibility of a specified WebElement.
     *
     * @param element the WebElement to check
     * @param isVisible expected visibility state (true for visible, false for not visible)
     */
    public void checkElementVisibility(WebElement element, boolean isVisible) {
        CustomAssertions.assertNotNull(element, "WebElement provided is null");

        if (isVisible) {
            CustomAssertions.assertTrue(element.isDisplayed(), "Element is expected to be visible, but is not visible");
        } else {
            CustomAssertions.assertFalse(element.isDisplayed(), "Element is expected to be not visible, but is visible");
        }
    }

    /**
     * Checks if a specified WebElement is enabled or disabled.
     *
     * @param element the WebElement to check
     * @param isEnabled expected enabled state (true for enabled, false for disabled; null means no
     *            check)
     */
    public void checkElementIsEnabled(WebElement element, Boolean isEnabled) {
        CustomAssertions.assertNotNull(element, "WebElement provided is null");

        if (isEnabled != null) {
            if (isEnabled) {
                CustomAssertions.assertTrue(element.isEnabled(), "Element is expected to be enabled, but is disabled");
            } else {
                CustomAssertions.assertFalse(element.isEnabled(), "Element is expected to be disabled, but is enabled");
            }
        }
    }

    /**
     * Checks the visibility of a WebElement based on its locator and type.
     *
     * @param explicitWaitTimeOut timeout for the wait in seconds
     * @param locator the locator string for the WebElement
     * @param locatorType the type of locator (ID, NAME, XPATH, etc.)
     * @param clickable whether the WebElement should also be clickable
     * @param context an optional context to set if applicable
     * @return true if the WebElement is visible (and clickable if specified), false otherwise
     */
    public boolean isWebElementVisible(int explicitWaitTimeOut, String locator, LocatorType locatorType, boolean clickable, Context context) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        try {
            wait.until((ExpectedCondition<Boolean>) waitDriver -> {
                CustomAssertions.assertNotNull(waitDriver, "WebDriver object for waits must not be null!");
                try {
                    if (context != null) {
                        context.set();
                    }
                    WebElement webElement = switch (locatorType) {
                        case ID -> waitDriver.findElement(By.id(locator));
                        case NAME -> waitDriver.findElement(By.name(locator));
                        case XPATH -> waitDriver.findElement(By.xpath(locator));
                        case LINKTEXT -> waitDriver.findElement(By.linkText(locator));
                        case CLASS -> waitDriver.findElement(By.className(locator));
                        case CSSSELECTOR -> waitDriver.findElement(By.cssSelector(locator));
                        case TAGNAME -> waitDriver.findElement(By.tagName(locator));
                        case TEXT -> waitDriver.findElement(By.xpath("//*[contains(text(), '" + locator + "')]"));
                    };
                    if (clickable) {
                        return webElement.isDisplayed() && webElement.isEnabled();
                    } else {
                        return webElement.isDisplayed();
                    }
                } catch (WebDriverException e) {
                    if (e instanceof NoSuchElementException || e instanceof StaleElementReferenceException || e instanceof ElementNotInteractableException
                            || e.getMessage()
                                    .contains("cannot determine loading status")
                            || e.getMessage().contains("target frame detached")) {
                        return false;
                    } else {
                        throw e;
                    }
                }
            });
        } catch (TimeoutException ignore) {
            return false;
        }
        return true;
    }

    /**
     * Overloaded method to check the visibility of a WebElement without context.
     *
     * @param explicitWaitTimeOut timeout for the wait in seconds
     * @param locator the locator string for the WebElement
     * @param locatorType the type of locator (ID, NAME, XPATH, etc.)
     * @param clickable whether the WebElement should also be clickable
     * @return true if the WebElement is visible (and clickable if specified), false otherwise
     */
    public boolean isWebElementVisible(int explicitWaitTimeOut, String locator, LocatorType locatorType, boolean clickable) {
        return isWebElementVisible(explicitWaitTimeOut, locator, locatorType, clickable, null);
    }

    /**
     * Checks if a WebElement is invisible based on its locator and type.
     *
     * @param explicitWaitTimeOut timeout for the wait in seconds
     * @param locator the locator string for the WebElement
     * @param locatorType the type of locator (ID, NAME, XPATH, etc.)
     * @param context an optional context to set if applicable
     * @return true if the WebElement is not visible, false otherwise
     */
    public boolean isWebElementInvisible(int explicitWaitTimeOut, String locator, LocatorType locatorType, Context context) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        try {
            wait.until((ExpectedCondition<Boolean>) waitDriver -> {
                CustomAssertions.assertNotNull(waitDriver, "WebDriver object for waits must not be null!");
                try {
                    if (context != null) {
                        context.set();
                    }
                    WebElement webElement = switch (locatorType) {
                        case ID -> waitDriver.findElement(By.id(locator));
                        case NAME -> waitDriver.findElement(By.name(locator));
                        case XPATH -> waitDriver.findElement(By.xpath(locator));
                        case LINKTEXT -> waitDriver.findElement(By.linkText(locator));
                        case CLASS -> waitDriver.findElement(By.className(locator));
                        case CSSSELECTOR -> waitDriver.findElement(By.cssSelector(locator));
                        case TAGNAME -> waitDriver.findElement(By.tagName(locator));
                        case TEXT -> waitDriver.findElement(By.xpath("//*[contains(text(), '" + locator + "')]"));
                    };
                    return !webElement.isDisplayed();
                } catch (NoSuchElementException | StaleElementReferenceException ignore) {
                    return true;
                } catch (WebDriverException e) {
                    if (e instanceof ElementNotInteractableException || e.getMessage().contains("cannot determine loading status") || e.getMessage()
                            .contains("target frame detached")) {
                        return false;
                    } else {
                        throw e;
                    }
                }
            });
        } catch (TimeoutException e) {
            return false;
        }
        return true;
    }

    /**
     * Checks if a WebElement is invisible based on its locator and type.
     *
     * @param explicitWaitTimeOut the timeout duration in seconds to wait for the element
     * @param locator the locator string for the WebElement
     * @param locatorType the type of locator (ID, NAME, XPATH, etc.)
     * @return true if the WebElement is not visible, false otherwise
     */
    public boolean isWebElementInvisible(int explicitWaitTimeOut, String locator, LocatorType locatorType) {
        return isWebElementInvisible(explicitWaitTimeOut, locator, locatorType, null);
    }

    /**
     * Checks if a WebElement is present based on its locator and type.
     *
     * @param explicitWaitTimeOut the timeout duration in seconds to wait for the element
     * @param locator the locator string for the WebElement
     * @param locatorType the type of locator (ID, NAME, XPATH, etc.)
     * @param context an optional context to set if applicable
     * @return true if the WebElement is present, false otherwise
     */
    public boolean isWebElementPresent(int explicitWaitTimeOut, String locator, LocatorType locatorType, Context context) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        try {
            wait.until((ExpectedCondition<Boolean>) waitDriver -> {
                CustomAssertions.assertNotNull(waitDriver, "WebDriver object for waits must not be null!");
                try {
                    if (context != null) {
                        context.set();
                    }
                    WebElement webElement = switch (locatorType) {
                        case ID -> waitDriver.findElement(By.id(locator));
                        case NAME -> waitDriver.findElement(By.name(locator));
                        case XPATH -> waitDriver.findElement(By.xpath(locator));
                        case LINKTEXT -> waitDriver.findElement(By.linkText(locator));
                        case CLASS -> waitDriver.findElement(By.className(locator));
                        case CSSSELECTOR -> waitDriver.findElement(By.cssSelector(locator));
                        case TAGNAME -> waitDriver.findElement(By.tagName(locator));
                        case TEXT -> waitDriver.findElement(By.xpath("//*[contains(text(), '" + locator + "')]"));
                    };
                    return webElement != null;
                } catch (WebDriverException e) {
                    if (e instanceof NoSuchElementException || e instanceof StaleElementReferenceException || e instanceof ElementNotInteractableException
                            || e.getMessage()
                                    .contains("cannot determine loading status")
                            || e.getMessage().contains("target frame detached")) {
                        return false;
                    } else {
                        throw e;
                    }
                }
            });
        } catch (TimeoutException ignore) {
            return false;
        }
        return true;
    }

    /**
     * Overloaded method to check if a WebElement is present without context.
     *
     * @param explicitWaitTimeOut the timeout duration in seconds to wait for the element
     * @param locator the locator string for the WebElement
     * @param locatorType the type of locator (ID, NAME, XPATH, etc.)
     * @return true if the WebElement is present, false otherwise
     */
    public boolean isWebElementPresent(int explicitWaitTimeOut, String locator, LocatorType locatorType) {
        return isWebElementPresent(explicitWaitTimeOut, locator, locatorType, null);
    }

    /**
     * Checks if a specified WebElement is enabled.
     *
     * @param webElement the WebElement to check
     * @return true if the WebElement is enabled, false if it is disabled or not found
     */
    public boolean isWebElementEnabled(WebElement webElement) {
        try {
            return webElement.isEnabled();
        } catch (NoSuchElementException | StaleElementReferenceException e) {
            return false;
        }
    }

    /**
     * Retrieves the selected option from a dropdown based on its locator and type.
     *
     * @param explicitWaitTimeOut the timeout duration in seconds to wait for the dropdown
     * @param locator the locator string for the dropdown WebElement
     * @param locatorType the type of locator (ID, NAME, XPATH, etc.)
     * @return the text of the selected option, or an empty string if not found
     */
    public String getSelectedOptionFromDropdown(int explicitWaitTimeOut, String locator, LocatorType locatorType) {
        try {
            Select selToSelectFrom = new Select(findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, true, false));
            return selToSelectFrom.getFirstSelectedOption().getText();
        } catch (TimeoutException e) {
            return "";
        }
    }

    /**
     * Retrieves the value of a specified attribute from a WebElement based on its locator and type.
     *
     * @param explicitWaitTimeOut the timeout duration in seconds to wait for the element
     * @param locator the locator string for the WebElement
     * @param locatorType the type of locator (ID, NAME, XPATH, etc.)
     * @param attributeName the name of the attribute to retrieve
     * @return the value of the specified attribute, or an empty string if the attribute does not exist
     */
    public String getAttributeValue(int explicitWaitTimeOut, String locator, LocatorType locatorType, String attributeName) {
        try {
            return findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, true, false).getDomAttribute(attributeName);
        } catch (TimeoutException e) {
            return "";
        }
    }

    /**
     * Waits for a WebElement to be located by its locator type and returns it.
     *
     * @param explicitWaitTimeOut the timeout duration in seconds to wait for the element
     * @param locator the locator string for the WebElement
     * @param locatorType the type of locator (ID, NAME, XPATH, etc.)
     * @param waitForClickable whether to wait for the element to be clickable
     * @return the located WebElement
     * @throws IllegalArgumentException if the locator type is not supported
     */
    public WebElement waitForElementByLocatorType(int explicitWaitTimeOut, String locator, LocatorType locatorType, boolean waitForClickable) {
        return switch (locatorType) {
            case ID -> waitForElementById(explicitWaitTimeOut, locator, true, waitForClickable);
            case NAME -> waitForElementByName(explicitWaitTimeOut, locator, true, waitForClickable);
            case XPATH -> waitForElementByXpath(explicitWaitTimeOut, locator, true, waitForClickable);
            case LINKTEXT -> waitForElementByLinkText(explicitWaitTimeOut, locator, true, waitForClickable);
            case CLASS -> waitForElementByClass(explicitWaitTimeOut, locator, true, waitForClickable);
            case CSSSELECTOR -> waitForElementByCssSelector(explicitWaitTimeOut, locator, true, waitForClickable);
            case TAGNAME -> waitForElementByTagName(explicitWaitTimeOut, locator, true, waitForClickable);
            default -> throw new IllegalArgumentException("Locator type " + locator + " is not supported by implementation!");
        };
    }

    /**
     * Navigates to a specified page URL and waits for the page title to match the expected title.
     *
     * @param explicitWaitTimeOut the timeout duration in seconds to wait for the title
     * @param pageUrl the URL of the page to navigate to
     * @param expectedPageTitle the expected title of the page to wait for
     * @return true if navigation and title match are successful, false otherwise
     */
    public boolean navigateToPageByUrl(int explicitWaitTimeOut, String pageUrl, String expectedPageTitle) {
        try {
            ScenarioLogManager.getLogger().info("Trying to navigate to \"{}\"", pageUrl);
            DRIVER.navigate().to(pageUrl);
        } catch (Exception e) {
            ScenarioLogManager.getLogger().error(e.getMessage(), e);
            return false;
        }
        try {
            ScenarioLogManager.getLogger().info("Waiting until page title is \"{}\"", expectedPageTitle);
            WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
            wait.until(ExpectedConditions.titleContains(expectedPageTitle));
        } catch (Exception e) {
            ScenarioLogManager.getLogger().error(e.getMessage(), e);
            return false;
        }
        return true;
    }

    /**
     * Sends a specified key to a WebElement identified by its locator and type.
     *
     * @param explicitWaitTimeOut the timeout duration in seconds to wait for the element
     * @param locator the locator string for the WebElement
     * @param locatorType the type of locator (ID, NAME, XPATH, etc.)
     * @param waitForClickable whether to wait for the element to be clickable
     * @param keys the key(s) to send to the element
     */
    public void sendKeyToElement(int explicitWaitTimeOut, String locator, LocatorType locatorType, boolean waitForClickable, Keys keys) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        wait.withMessage(
                "Tried to send key \"" + keys.toString() + "\" to web element, identified by locator (" + locatorType + ") \"" + locator + "\" for "
                        + explicitWaitTimeOut + " seconds!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            try {
                findElementByLocatorType(locator, locatorType, true, explicitWaitTimeOut, true, waitForClickable).sendKeys(keys);
                return true;
            } catch (StaleElementReferenceException | ElementNotInteractableException exp) {
                return false;
            }
        });
    }

    /**
     * Navigates to a specified page URL.
     *
     * @param pageUrl the URL of the page to navigate to
     */
    public void navigateToPage(@NotNull String pageUrl) {
        ScenarioLogManager.getLogger().info("Trying to navigate to \"{}\"", pageUrl);
        DRIVER.navigate().to(pageUrl);
    }

    /**
     * Waits for the current page to contain the expected page title.
     *
     * @param expectedPageTitle the expected title of the page to wait for
     */
    public void waitForPage(String expectedPageTitle) {
        ScenarioLogManager.getLogger().info("Waiting until page title contains \"{}\"", expectedPageTitle);
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.until(ExpectedConditions.titleContains(expectedPageTitle));
    }

    /**
     * Pauses execution for a specified duration.
     *
     * @param duration the duration to wait
     * @param unit the time unit of the duration (e.g., seconds, milliseconds)
     * @param logCountdown whether to log the countdown
     */
    public void waitFor(int duration, TimeUnit unit, boolean logCountdown) {
        ScenarioLogManager.getLogger().info("Explicit wait started: Waiting for {} {}", duration, unit);

        long timeoutMillis = unit.toMillis(duration);

        if (logCountdown) {
            long totalSeconds = unit.toSeconds(duration);
            for (long i = totalSeconds; i >= 0; i--) {
                String formattedTime = getFormattedTime(i);

                ScenarioLogManager.getLogger().info("Time remaining: {}", formattedTime);

                try {
                    Thread.sleep(1000);
                } catch (InterruptedException e) {
                    Thread.currentThread().interrupt();
                    ScenarioLogManager.getLogger().error("Countdown logging interrupted", e);
                    break;
                }
            }
        } else {
            try {
                Thread.sleep(timeoutMillis);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
                ScenarioLogManager.getLogger().error("Wait interrupted", e);
            }
        }
    }

    /**
     * Formats a given time in seconds into a human-readable format.
     *
     * @param i the time in seconds to format
     * @return the formatted time as a string
     */
    private static String getFormattedTime(long i) {
        long hours = i / 3600;
        long minutes = (i % 3600) / 60;
        long secs = i % 60;

        String formattedTime;
        if (hours > 0) {
            formattedTime = String.format("%02d:%02d:%02d", hours, minutes, secs);
        } else if (minutes > 0) {
            formattedTime = String.format("%02d:%02d", minutes, secs);
        } else {
            formattedTime = String.format("%02d seconds", secs);
        }
        return formattedTime;
    }

    /**
     * Retries a specified operation a defined number of times with a delay between retries.
     *
     * @param operation the operation to retry
     * @param maxRetries the maximum number of retries allowed
     * @param retryDelay the delay between retries in milliseconds
     * @param failureMessage the message to log if the operation fails after retries
     */
    public void retryOperation(RetryableOperation operation, int maxRetries, long retryDelay, String failureMessage) {
        int retries = 0;
        while (retries < maxRetries) {
            try {
                if (operation.execute()) {
                    return; // If operation succeeds and condition is met, exit the method
                }
            } catch (Exception e) {
                ScenarioLogManager.getLogger().info("Retry {} failed: {}", retries + 1, e.getMessage());
                if (retries >= maxRetries - 1) {
                    ScenarioLogManager.getLogger().error("Max retries reached. Failing the operation.");
                    CustomAssertions.fail("Max retries reached: " + failureMessage, e);
                }
            }
            retries++;
            ScenarioLogManager.getLogger().info("Retrying...");
            try {
                Thread.sleep(retryDelay); // Delay between retries
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
                throw new RuntimeException("Retry operation interrupted.", e);
            }
        }
        CustomAssertions.fail("Max retries reached without meeting the condition: " + failureMessage);
    }

    /**
     * Overloaded method to retry a specified operation with default retry delay.
     *
     * @param operation the operation to retry
     * @param maxRetries the maximum number of retries allowed
     * @param failureMessage the message to log if the operation fails after retries
     */
    public void retryOperation(RetryableOperation operation, int maxRetries, String failureMessage) {
        retryOperation(operation, maxRetries, 0, failureMessage);
    }

    /**
     * Checks if specified values are visible in a specific column of a table.
     *
     * @param tableLocator the locator string for the table WebElement
     * @param tableLocatorType the type of locator (ID, NAME, XPATH, etc.)
     * @param columnName the name of the column to check for values
     * @param searchStrings the values to check for in the column
     */
    public void areValuesVisibleInTableColumn(String tableLocator, LocatorType tableLocatorType, String columnName, String... searchStrings) {
        ScenarioLogManager.getLogger().info("Checking for values to be visible in column '{}' of the table located by '{}'...", columnName, tableLocator);

        // Find the table element
        WebElement table = findElementByLocatorType(tableLocator, tableLocatorType, true);

        // Find the index of the column with the header specified by 'columnName'
        List<WebElement> headerElements = table.findElements(By.xpath(".//thead//th"));
        OptionalInt columnIndexOpt = IntStream.range(0, headerElements.size())
                .filter(i -> columnName.equalsIgnoreCase(headerElements.get(i).getText().trim()))
                .findFirst();

        CustomAssertions.assertTrue(columnIndexOpt.isPresent(), "Column '" + columnName + "' not found in the table located by '" + tableLocator + "'.");

        int columnIndex = columnIndexOpt.getAsInt() + 1; // XPath's indices are 1-based

        // Use the index to locate elements in the specified column within the table
        List<String> columnElements = table.findElements(By.xpath(".//tbody//tr//td[" + columnIndex + "]"))
                .stream()
                .map(WebElement::getText)
                .toList();

        // Check if each specified string is present in the column elements
        for (String searchString : searchStrings) {
            boolean isStringPresent = columnElements.stream().anyMatch(text -> text.trim().equals(searchString));
            CustomAssertions.assertTrue(isStringPresent,
                    "String '" + searchString + "' is NOT visible in column '" + columnName + "' of the table located by '" + tableLocator + "'.");
            ScenarioLogManager.getLogger().info("String '{}' is visible in column '{}' of the table located by '{}'.", searchString, columnName, tableLocator);
        }
    }

    /**
     * Retrieves a specific cell element from a table based on its column name and search string.
     *
     * @param tableLocator the locator string for the table WebElement
     * @param tableLocatorType the type of locator (ID, NAME, XPATH, etc.)
     * @param columnName the name of the column to search
     * @param searchString the string to find within the specified column
     * @return the WebElement representing the cell, or null if not found
     * @throws AssertionError if the specified search string is not found in the column
     */
    public WebElement getTableCellElement(String tableLocator, LocatorType tableLocatorType, String columnName, String searchString) {
        ScenarioLogManager.getLogger()
                .info("Getting cell element for string '{}' in column '{}' of the table located by '{}'...", searchString, columnName, tableLocator);

        // Find the table element
        WebElement table = findElementByLocatorType(tableLocator, tableLocatorType, true);

        // Find the index of the column with the header specified by 'columnName'
        List<WebElement> headerElements = table.findElements(By.xpath(".//thead//th"));
        OptionalInt columnIndexOpt = IntStream.range(0, headerElements.size())
                .filter(i -> columnName.equalsIgnoreCase(headerElements.get(i).getText().trim()))
                .findFirst();

        CustomAssertions.assertTrue(columnIndexOpt.isPresent(), "Column '" + columnName + "' not found in the table located by '" + tableLocator + "'.");

        int columnIndex = columnIndexOpt.getAsInt() + 1; // XPath's indices are 1-based

        // Use the index to locate the cell element in the specified column within the table
        List<WebElement> columnElements = table.findElements(By.xpath(".//tbody//tr//td[" + columnIndex + "]"));

        for (WebElement cellElement : columnElements) {
            if (cellElement.getText().trim().equals(searchString)) {
                ScenarioLogManager.getLogger()
                        .info("Found cell element for string '{}' in column '{}' of the table located by '{}'.", searchString, columnName, tableLocator);
                return cellElement;
            }
        }

        CustomAssertions.fail("String '" + searchString + "' not found in column '" + columnName + "' of the table located by '" + tableLocator + "'.");
        return null;
    }
}
