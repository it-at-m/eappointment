package zms.ataf.ui.pages.buergeransicht;

import java.security.SecureRandom;
import java.time.Duration;
import java.time.temporal.ChronoUnit;
import java.util.List;
import java.util.concurrent.atomic.AtomicBoolean;
import java.util.concurrent.atomic.AtomicReference;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.openqa.selenium.By;
import org.openqa.selenium.DetachedShadowRootException;
import org.openqa.selenium.ElementNotInteractableException;
import org.openqa.selenium.Keys;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.SearchContext;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebDriverException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedCondition;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.helpers.TestDataHelper;
import ataf.core.helpers.TestPropertiesHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.web.controls.WindowControls;
import ataf.web.model.LocatorType;
import ataf.web.pages.BasePage;
import ataf.web.pages.Context;


public class BuergeransichtPage extends BasePage {
    private final BuergeransichtPageContext CONTEXT;
    private final String DATE_PICKER_BUTTON_LOCATOR_CSS_SELECTOR = "div.v-date-picker-header__value > div > button";
    private final String APPOINTMENT_DATA_TEXT_LOCATOR_CSS_SELECTOR = "#panel2 > button > div.row.no-gutters > div.text--secondary.col-md-9.col-12 > span > b";
    private SearchContext shadowRootSearchContext = null;
    private boolean secondClickOnChangeAppointmentButton = false;

    public BuergeransichtPage(RemoteWebDriver driver) {
        super(driver);
        CONTEXT = new BuergeransichtPageContext(driver);
    }

    public void navigateToPage() {
        CONTEXT.navigateToPage();
    }

    private SearchContext getShadowRoot() {
        CONTEXT.set();
        if (shadowRootSearchContext == null) {
            ScenarioLogManager.getLogger().info("Trying to expand shadow root on \"zms-appointment\"");
            shadowRootSearchContext = expandShadowRootElement(findElementByLocatorType("//zms-appointment", LocatorType.XPATH, false));
        }
        return shadowRootSearchContext;
    }

    private WebElement waitForShadowRootElement(SearchContext initialSearchContext, By by, boolean clickable) {
        AtomicReference<WebElement> webElementAtomicReference = new AtomicReference<>();
        AtomicReference<SearchContext> searchContextReference = new AtomicReference<>(initialSearchContext);

        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));

        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            try {
                webElementAtomicReference.set(waitForElement(DEFAULT_EXPLICIT_WAIT_TIME, searchContextReference.get().findElement(by), clickable));
                return true;
            } catch (DetachedShadowRootException e) {
                //refresh the shadow root reference and retry
                shadowRootSearchContext = null;
                searchContextReference.set(getShadowRoot());
                return false;
            }
        });

        return webElementAtomicReference.get();
    }

    public boolean isWebElementVisible(int explicitWaitTimeOut, String locator, LocatorType locatorType, boolean clickable) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        try {
            wait.until((ExpectedCondition<Boolean>) waitDriver -> {
                Assert.assertNotNull(waitDriver);
                try {
                    WebElement webElement = switch (locatorType) {
                        case ID -> getShadowRoot().findElement(By.id(locator));
                        case NAME -> getShadowRoot().findElement(By.name(locator));
                        case XPATH -> getShadowRoot().findElement(By.xpath(locator));
                        case LINKTEXT -> getShadowRoot().findElement(By.linkText(locator));
                        case CLASS -> getShadowRoot().findElement(By.className(locator));
                        case CSSSELECTOR -> getShadowRoot().findElement(By.cssSelector(locator));
                        case TAGNAME -> getShadowRoot().findElement(By.tagName(locator));
                        case TEXT -> getShadowRoot().findElement(By.xpath("//*[contains(text(), '" + locator + "')]"));
                    };
                    if (clickable) {
                        return webElement.isDisplayed() && webElement.isEnabled();
                    } else {
                        return webElement.isDisplayed();
                    }
                } catch (WebDriverException e) {
                    if (e instanceof NoSuchElementException || e instanceof StaleElementReferenceException
                            || e instanceof ElementNotInteractableException
                            || e.getMessage().contains("cannot determine loading status")
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

    public boolean isWebElementInvisible(int explicitWaitTimeOut, String locator, LocatorType locatorType, Context context) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(explicitWaitTimeOut));
        try {
            wait.until((ExpectedCondition<Boolean>) waitDriver -> {
                Assert.assertNotNull(waitDriver);
                try {
                    if (context != null) {
                        context.set();
                    }
                    WebElement webElement = switch (locatorType) {
                        case ID -> getShadowRoot().findElement(By.id(locator));
                        case NAME -> getShadowRoot().findElement(By.name(locator));
                        case XPATH -> getShadowRoot().findElement(By.xpath(locator));
                        case LINKTEXT -> getShadowRoot().findElement(By.linkText(locator));
                        case CLASS -> getShadowRoot().findElement(By.className(locator));
                        case CSSSELECTOR -> getShadowRoot().findElement(By.cssSelector(locator));
                        case TAGNAME -> getShadowRoot().findElement(By.tagName(locator));
                        case TEXT -> getShadowRoot().findElement(By.xpath("//*[contains(text(), '" + locator + "')]"));
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

    public void selectService(String service) {
        ScenarioLogManager.getLogger().info("Trying to enter service \"" + service + "\"");
        WebElement servicesTextField = waitForShadowRootElement(getShadowRoot(), By.cssSelector("input#input-17"), true);
        enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, service, servicesTextField);
        servicesTextField.sendKeys(Keys.ARROW_DOWN);
        servicesTextField.sendKeys(Keys.ENTER);
        /*WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        AtomicReference<WebElement> serviceElement = new AtomicReference<>(null);
        try {
            wait.until((ExpectedCondition<Boolean>) waitDriver -> {
                List<WebElement> webElements = getShadowRoot().findElements(By.cssSelector("div.v-list-item__title"));
                for (WebElement webElement : webElements) {
                    ScenarioLogManager.getLogger().info("textt: " + webElement.getText());
                    if (webElement.getText().equals(service)) {
                        serviceElement.set(webElement);
                        return true;
                    }
                }
                return false;
            });
        } catch (TimeoutException ignore) {
        }
        Assert.assertNotNull(serviceElement.get(), "Clickable service element for \"" + service + "\" was not found,");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, serviceElement.get(), false);
        */
    }

    public void checkForCombinableServices(List<String> services) {
        ScenarioLogManager.getLogger().info("Trying to check if the combinable services are visible...");
        WebElement subservicesDiv = waitForShadowRootElement(getShadowRoot(), By.cssSelector("div.subservices"), true);
        Assert.assertNotNull(subservicesDiv, "Subservices div is not found.");

        List<WebElement> spans = subservicesDiv.findElements(By.cssSelector("span"));
        List<String> spanTexts = spans.stream().map(WebElement::getText).toList();

        for (String service : services) {
            Assert.assertTrue(spanTexts.contains(service), "The service '" + service + "' is not found in any span.");
        }
    }

    public void clickIncreaseButtonsByServiceName(String service) {
        ScenarioLogManager.getLogger().info("Trying to click '+' button for \"" + service + "\"");

        WebElement subservicesDiv = waitForShadowRootElement(getShadowRoot(), By.cssSelector("div.subservices"), true);
        Assert.assertNotNull(subservicesDiv, "Subservices div is not found.");

        List<WebElement> spans = subservicesDiv.findElements(By.cssSelector("span"));
        boolean serviceFound = false;

        for (WebElement span : spans) {
            if (span.getText().contains(service)) {
                WebElement listItem = span.findElement(By.xpath("./ancestor::div[contains(@class, 'v-list-item')]"));
                Assert.assertNotNull(listItem, "List item containing \"" + service + "\" is not found.");

                WebElement button = listItem.findElement(By.cssSelector("#button-up"));
                Assert.assertNotNull(button, "Button inside list item is not found.");

                if (button.getAttribute("class").contains("v-btn--disabled")) {
                    Assert.fail("Button for \"" + service + "\" is disabled.");
                }
                button.click();
                serviceFound = true;
                break;
            }
        }

        Assert.assertTrue(serviceFound, "Service \"" + service + "\" not found in the subservices.");
        ScenarioLogManager.getLogger().info("Clicked '+' button for \"" + service + "\" successfully.");
    }

    public void clickFirstEnabledIncreaseButtonByServiceNames(List<String> services) {
        ScenarioLogManager.getLogger().info("Trying to click '+' button for the first enabled service of the services in the list.");

        WebElement subservicesDiv = waitForShadowRootElement(getShadowRoot(), By.cssSelector("div.subservices"), true);
        Assert.assertNotNull(subservicesDiv, "Subservices div is not found.");

        List<WebElement> spans = subservicesDiv.findElements(By.cssSelector("div.v-list-item > span:first-of-type"));
        boolean serviceFound = false;

        for (String service : services) {
            for (WebElement span : spans) {
                if (span.getText().contains(service)) {
                    WebElement listItem = span.findElement(By.xpath("./ancestor::div[contains(@class, 'v-list-item')]"));
                    Assert.assertNotNull(listItem, "List item containing \"" + service + "\" is not found.");

                    WebElement button = listItem.findElement(By.cssSelector("#button-up"));
                    Assert.assertNotNull(button, "Button inside list item is not found.");

                    if (!button.getAttribute("class").contains("v-btn--disabled")) {
                        moveToElementAction(button);
                        button.click();
                        serviceFound = true;
                        ScenarioLogManager.getLogger().info("Clicked '+' button for \"" + service + "\" successfully.");
                        break;
                    }
                }
            }
            if (serviceFound) {
                break;
            }
        }

        Assert.assertTrue(serviceFound, "None of the services in the list have an enabled button to click.");
    }

    public void clickDecreaseButtonsByServiceName(String service) {
        ScenarioLogManager.getLogger().info("Trying to click '-' button for \"" + service + "\"");

        WebElement subservicesDiv = waitForShadowRootElement(getShadowRoot(), By.cssSelector("div.subservices"), true);
        Assert.assertNotNull(subservicesDiv, "Subservices div is not found.");

        List<WebElement> spans = subservicesDiv.findElements(By.cssSelector("span"));
        boolean serviceFound = false;

        for (WebElement span : spans) {
            if (span.getText().contains(service)) {
                WebElement listItem = span.findElement(By.xpath("./ancestor::div[contains(@class, 'v-list-item')]"));
                Assert.assertNotNull(listItem, "List item containing \"" + service + "\" is not found.");

                WebElement button = listItem.findElement(By.cssSelector(".button-down"));
                Assert.assertNotNull(button, "Button inside list item is not found.");

                ScenarioLogManager.getLogger().info("Button found: " + button);
                button.click();
                serviceFound = true;
                break;
            }
        }

        Assert.assertTrue(serviceFound, "Service \"" + service + "\" not found in the subservices.");
        ScenarioLogManager.getLogger().info("Clicked '+' button for \"" + service + "\" successfully.");
    }

    public void checkForWarningMessage(String message) {
        ScenarioLogManager.getLogger().info("Check if the warning messages match...");
        WebElement warning = waitForShadowRootElement(getShadowRoot(), By.cssSelector(".m-callout__content p"), true);
        Assert.assertTrue(warning.getText().contains(message), "Expected warning message '" + message + "' not found!");
    }

    public boolean IsWarningInVisible() {
        ScenarioLogManager.getLogger().info("Check if a warning message is invisible...");
        boolean isInvisible = isWebElementInvisible(DEFAULT_EXPLICIT_WAIT_TIME, "div.m-callout__content > p", LocatorType.CSSSELECTOR, null);
        ScenarioLogManager.getLogger().info("Warning message invisibility: " + isInvisible);
        return isInvisible;
    }

    public void clickOnContinueButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"continue\" button");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, getShadowRoot().findElement(By.cssSelector("button.button-next")), false);
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.pollingEvery(Duration.ofMillis(500L));
        wait.withMessage("Click on \"continue\" button has failed! Panel 1 for services did not collapse!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> waitForShadowRootElement(getShadowRoot(), By.cssSelector("#panel1"), true).getAttribute(
                "aria-expanded").equals("false"));
    }

    public void selectOffice(String office) {
        final String OFFICE_TAB_LOCATOR_CSS_SELECTOR = "div.v-tab";
        List<WebElement> webElements = getShadowRoot().findElements(By.cssSelector(OFFICE_TAB_LOCATOR_CSS_SELECTOR));
        WebElement officeElement = null;
        for (WebElement webElement : webElements) {
            ScenarioLogManager.getLogger().debug("[" + webElement.getText().trim() + "] vs [" + office.toUpperCase() + "]");
            if (webElement.isDisplayed() && webElement.getText().trim().equals(office.toUpperCase())) {
                officeElement = webElement;
                break;
            }
        }
        //Check if office is further right in the list...
        if (officeElement == null) {
            WebElement nextButton = getShadowRoot().findElement(By.cssSelector("div.v-slide-group__next"));
            if (!nextButton.getAttribute("class").contains("v-slide-group__next--disabled")) {
                clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, nextButton, false);
            }
            webElements = getShadowRoot().findElements(By.cssSelector(OFFICE_TAB_LOCATOR_CSS_SELECTOR));
            for (WebElement webElement : webElements) {
                ScenarioLogManager.getLogger().debug("[" + webElement.getText().trim() + "] vs [" + office.toUpperCase() + "]");
                if (webElement.isDisplayed() && webElement.getText().trim().equals(office.toUpperCase())) {
                    officeElement = webElement;
                    break;
                }
            }
        }
        Assert.assertNotNull(officeElement, "Clickable office element \"" + office + "\" was not found!");
        if (!officeElement.getAttribute("class").contains("v-tab--active")) {
            ScenarioLogManager.getLogger().info("Trying to select office \"" + office + "\"");
            clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, officeElement, false);
        } else {
            ScenarioLogManager.getLogger().info("Office \"" + office + "\" is already selected!");
        }
    }

    private String extractDateString(ChronoUnit chronoUnit, String textContainingDateString) {
        Pattern monthYearPattern = Pattern.compile("([A-Z][a-z]{2})?[a-z]*\\s*([0-9]{4})");
        Matcher monthYearMatcher = monthYearPattern.matcher(textContainingDateString);
        if (monthYearMatcher.find()) {
            switch (chronoUnit) {
            case MONTHS:
                if (monthYearMatcher.group(1) != null) {
                    return monthYearMatcher.group(1);
                } else {
                    break;
                }
            case YEARS:
                if (monthYearMatcher.group(2) != null) {
                    return monthYearMatcher.group(2);
                } else {
                    break;
                }
            }
        }
        return textContainingDateString;
    }

    public void selectYear(String year) {
        WebElement monthAndYearElement = waitForShadowRootElement(getShadowRoot(), By.cssSelector(DATE_PICKER_BUTTON_LOCATOR_CSS_SELECTOR), true);
        if (!extractDateString(ChronoUnit.YEARS, monthAndYearElement.getText()).equals(year)) {
            ScenarioLogManager.getLogger().info("Trying to select year \"" + year + "\"");
            clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, monthAndYearElement, false);
            WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
            wait.until(ExpectedConditions.stalenessOf(monthAndYearElement));
            WebElement yearElement = waitForShadowRootElement(getShadowRoot(), By.cssSelector(DATE_PICKER_BUTTON_LOCATOR_CSS_SELECTOR), true);
            clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, yearElement, false);
            wait.until(ExpectedConditions.stalenessOf(yearElement));
            List<WebElement> webElements = getShadowRoot().findElements(By.cssSelector("ul.v-date-picker-years > li"));
            for (WebElement webElement : webElements) {
                if (webElement.getText().equals(year)) {
                    clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, webElement, false);
                    break;
                }
            }
        } else {
            ScenarioLogManager.getLogger().info("Year \"" + year + "\" is already selected!");
        }
    }

    public void selectMonth(String month) {
        switch (month) {
        case "1":
        case "01":
            month = "Jan";
            break;
        case "2":
        case "02":
            month = "Feb";
            break;
        case "3":
        case "03":
            month = "Mär";
            break;
        case "4":
        case "04":
            month = "Apr";
            break;
        case "5":
        case "05":
            month = "Mai";
            break;
        case "6":
        case "06":
            month = "Jun";
            break;
        case "7":
        case "07":
            month = "Jul";
            break;
        case "8":
        case "08":
            month = "Aug";
            break;
        case "9":
        case "09":
            month = "Sep";
            break;
        case "10":
            month = "Okt";
            break;
        case "11":
            month = "Nov";
            break;
        case "12":
            month = "Dez";
            break;
        default:
            throw new IllegalArgumentException("For month \"" + month + "\" no conversion can be made! It must be in format MM, e.g. 01 or 10.");
        }
        if (!extractDateString(ChronoUnit.MONTHS,
                waitForShadowRootElement(getShadowRoot(), By.cssSelector(DATE_PICKER_BUTTON_LOCATOR_CSS_SELECTOR), true).getText()).equals(month)) {
            ScenarioLogManager.getLogger().info("Trying to select month \"" + month + "\"");
            WebElement monthAndYearElement = waitForShadowRootElement(getShadowRoot(), By.cssSelector(DATE_PICKER_BUTTON_LOCATOR_CSS_SELECTOR), true);
            clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, monthAndYearElement, false);
            WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
            wait.until(ExpectedConditions.stalenessOf(monthAndYearElement));
            List<WebElement> buttonWebElements = getShadowRoot().findElements(
                    By.cssSelector("div.v-date-picker-table.v-date-picker-table--month.theme--light > table > tbody > tr > td > button"));
            List<WebElement> divWebElements = getShadowRoot().findElements(
                    By.cssSelector("div.v-date-picker-table.v-date-picker-table--month.theme--light > table > tbody > tr > td > button > div"));
            for (WebElement divWebElement : divWebElements) {
                ScenarioLogManager.getLogger().info(divWebElement.getText());
                if (divWebElement.getText().equals(month.toUpperCase())) {
                    Assert.assertFalse(buttonWebElements.get(divWebElements.indexOf(divWebElement)).getAttribute("class").contains("v-btn--disabled"),
                            "Month \"" + month + "\" cannot be selected! Month is in the past or has no day which has any slots!");
                    clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, divWebElement, false);
                    break;
                }
            }
        } else {
            ScenarioLogManager.getLogger().info("Month \"" + month + "\" is already selected!");
        }
    }

    public void selectDay(String day) {
        ScenarioLogManager.getLogger().info("Trying to select day \"" + day + "\"");
        List<WebElement> webElements = getShadowRoot().findElements(
                By.cssSelector("div.v-date-picker-table.v-date-picker-table--date.theme--light > table > tbody > tr > td > button"));
        for (WebElement webElement : webElements) {
            WebElement childWebElement = webElement.findElement(By.cssSelector("div.v-btn__content"));
            if (childWebElement.getText().equals(day)) {
                Assert.assertFalse(webElement.getAttribute("class").contains("v-btn--disabled"), "Day \"" + day + "\" cannot be selected! No slots available!");
                if (webElement.getAttribute("class").contains("v-date-picker-table__current")) {
                    ScenarioLogManager.getLogger().info("Day \"" + day + "\" is already selected!");
                } else {
                    clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, webElement, false);
                }
                break;
            }
        }
    }

    public boolean isAlertPresent() {
        return !getShadowRoot().findElements(By.cssSelector("#appointments > div > div > div > div > div > div.v-alert__content")).isEmpty();
    }

    public void selectTime(String time) {
        ScenarioLogManager.getLogger().info("Trying to select time \"" + time + "\"");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.pollingEvery(Duration.ofMillis(1000L));
        wait.withMessage("Could not locate any time slot elements in time!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            List<WebElement> webElements = getShadowRoot().findElements(By.cssSelector("div.select-appointment"));
            if (!webElements.isEmpty()) {
                switch (time) {
                case "<beliebig>":
                case "<nächste>":
                    WebElement webElement;
                    String timeSelected;
                    final String ALERT_CONTENT_LOCATOR_CSS_SELECTOR = "#appointments > div > div > div > div > div > div.v-alert__content";
                    final SecureRandom SECURE_RANDOM = new SecureRandom();
                    int numberOfTries = 0;
                    do {
                        if (time.equals("<beliebig>")) {
                            webElement = webElements.get(SECURE_RANDOM.nextInt(webElements.size() - 1));
                        } else {
                            webElement = webElements.get(numberOfTries);
                        }
                        timeSelected = webElement.getText();
                        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, webElement, false);
                        ++numberOfTries;
                    } while (isAlertPresent() && numberOfTries <= TestPropertiesHelper.getPropertyAsInteger("numberOfRetries", true, 3));

                    if (isAlertPresent()) {
                        Assert.fail(getShadowRoot().findElement(By.cssSelector(ALERT_CONTENT_LOCATOR_CSS_SELECTOR)).getText());
                    }
                    TestDataHelper.setTestData("time", timeSelected);
                    break;
                default:
                    for (WebElement webElementInList : webElements) {
                        if (webElementInList.getText().contains(time)) {
                            clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, webElementInList, false);
                            break;
                        }
                    }
                    TestDataHelper.setTestData("time", time);
                }
                return true;
            } else {
                return false;
            }
        });
        wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.pollingEvery(Duration.ofMillis(500L));
        wait.withMessage("Selection of time \"" + time + "\" has failed! Panel 2 for appointment did not collapse!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> waitForShadowRootElement(getShadowRoot(), By.cssSelector("#panel2"), true).getAttribute(
                "aria-expanded").equals("false"));
    }

    public void enterCustomerName(String customerName) {
        ScenarioLogManager.getLogger().info("Trying to enter customer name \"" + customerName + "\"");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.pollingEvery(Duration.ofMillis(500L));
        wait.withMessage("Could not locate customer name text field in time!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            List<WebElement> webElements = getShadowRoot().findElements(By.cssSelector("input#customer-name"));
            if (!webElements.isEmpty()) {
                enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, customerName, webElements.get(0));
                return true;
            } else {
                return false;
            }
        });
    }

    public void enterCustomerEmail(String customerEmail) {
        ScenarioLogManager.getLogger().info("Trying to enter customer name \"" + customerEmail + "\"");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.pollingEvery(Duration.ofMillis(500L));
        wait.withMessage("Could not locate customer email text field in time!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            List<WebElement> webElements = getShadowRoot().findElements(By.cssSelector("input#customer-email"));
            if (!webElements.isEmpty()) {
                enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, customerEmail, webElements.get(0));
                return true;
            } else {
                return false;
            }
        });
    }

    public void selectDataPrivacyAgreementCheckBox() {
        ScenarioLogManager.getLogger().info("Trying to select \"data privacy agreement\" check box...");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.pollingEvery(Duration.ofMillis(500L));
        wait.withMessage("Could not locate data privacy agreement check box in time!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            List<WebElement> webElements = getShadowRoot().findElements(By.cssSelector("div.v-input--selection-controls__ripple"));
            if (!webElements.isEmpty()) {
                clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, webElements.get(0), false);
                return true;
            } else {
                return false;
            }
        });
    }

    public void clickOnContinueWithReservationButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"continue with reservation\" button...");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.pollingEvery(Duration.ofMillis(500L));
        wait.withMessage("Could not locate continue with reservation button in time!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            List<WebElement> webElements = getShadowRoot().findElements(By.cssSelector("button#customer-submit-button"));
            if (!webElements.isEmpty()) {
                clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, webElements.get(0), false);
                return true;
            } else {
                return false;
            }
        });
    }

    private void clickOnButtonBySpanText(String button, String spanText) {
        ScenarioLogManager.getLogger().info("Trying to click on \"" + button + "\" button...");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.pollingEvery(Duration.ofMillis(500L));
        wait.withMessage("Could not locate " + button + " button in time!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            List<WebElement> webElements = getShadowRoot().findElements(By.cssSelector("button.button-submit"));
            if (!webElements.isEmpty()) {
                for (WebElement webElement : webElements) {
                    if (webElement.findElement(By.cssSelector("span")).getText().contains(spanText)) {
                        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, webElement, false);
                        return true;
                    }
                }
            }
            return false;
        });
    }

    public void clickOnCompleteReservationButton() {
        //Updating test data values...
        String day = TestDataHelper.getTestData("day");
        String month = TestDataHelper.getTestData("month");
        String year = TestDataHelper.getTestData("year");
        String time = TestDataHelper.getTestData("time");
        String office = TestDataHelper.getTestData("office");

        Pattern pattern = Pattern.compile("([0-9]+)\\.([0-9]+)\\.([0-9]+) ([0-9]+:[0-9]+) (.+)");
        Matcher matcher = pattern.matcher(
                waitForShadowRootElement(getShadowRoot(), By.cssSelector(APPOINTMENT_DATA_TEXT_LOCATOR_CSS_SELECTOR), false).getText());
        if (matcher.find()) {
            String dayFound = matcher.group(1);
            if (day == null || !day.equals(dayFound)) {
                ScenarioLogManager.getLogger().info("Test data for day has not been set or is outdated. Setting found day to: " + dayFound);
                TestDataHelper.setTestData("day", dayFound);
            }
            String monthFound = matcher.group(2);
            if (month == null || !month.equals(monthFound)) {
                ScenarioLogManager.getLogger().info("Test data for month has not been set or is outdated. Setting found month to: " + monthFound);
                TestDataHelper.setTestData("month", monthFound);
            }
            String yearFound = matcher.group(3);
            if (year == null || !year.equals(yearFound)) {
                ScenarioLogManager.getLogger().info("Test data for year has not been set or is outdated. Setting found year to: " + yearFound);
                TestDataHelper.setTestData("year", yearFound);
            }
            String timeFound = matcher.group(4);
            if (time == null || !time.equals(timeFound)) {
                ScenarioLogManager.getLogger().info("Test data for time has not been set or is outdated. Setting found time to: " + timeFound);
                TestDataHelper.setTestData("time", timeFound);
            }
            String officeFound = matcher.group(5);
            if (office == null || !office.equals(officeFound)) {
                ScenarioLogManager.getLogger().info("Test data for time has not been set or is outdated. Setting found time to: " + officeFound);
                TestDataHelper.setTestData("office", officeFound);
            }
        }
        clickOnButtonBySpanText("complete reservation", "Reservierung abschließen");
        BuergeransichtPageContext.incrementAppointmentCount();
    }

    public void checkIfReservationApprovalIsDisplayed() {
        shadowRootSearchContext = null;
        CONTEXT.set();

        //Appointment number
        ScenarioLogManager.getLogger().info("Checking if appointment number is displayed!");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "div.appointment-number > b", LocatorType.CSSSELECTOR, false),
                "Appointment number is not displayed!");
        TestDataHelper.setTestData("appointment_number",
                waitForShadowRootElement(getShadowRoot(), By.cssSelector("div.appointment-number > b"), false).getText());

        //Service
        ScenarioLogManager.getLogger().info("Checking if selected service is displayed!");
        Assert.assertEquals(waitForShadowRootElement(getShadowRoot(),
                        By.cssSelector("#panel1 > button > div.row.no-gutters > div.text--secondary.col-md-9.col-12 > span > b"), false).getText(),
                "1 x " + TestDataHelper.getTestData("service"), "Service does not match expected value!");

        //Date, time and office
        ScenarioLogManager.getLogger().info("Checking if date, time and selected office are displayed!");
        Assert.assertEquals(waitForShadowRootElement(getShadowRoot(), By.cssSelector(APPOINTMENT_DATA_TEXT_LOCATOR_CSS_SELECTOR), false).getText(),
                TestDataHelper.getTestData("day") + "." + TestDataHelper.getTestData("month") + "." + TestDataHelper.getTestData(
                        "year") + " " + TestDataHelper.getTestData("time") + " " + TestDataHelper.getTestData("office"),
                "Date, time or office do not match expected value!");

        //Contact data
        ScenarioLogManager.getLogger().info("Checking if contact data is displayed!");
        String contactData = waitForShadowRootElement(getShadowRoot(),
                By.cssSelector("#panel3 > button > div.row.no-gutters > div.text--secondary.col-md-9.col-12 > span > span > b"), false).getText()
                .replaceAll("[\\r\\n]", "").trim();
        Assert.assertTrue(contactData.contains(TestDataHelper.getTestData("customer_name")),
                "Name of contact information does not match expected value! Expected [" + TestDataHelper.getTestData(
                        "customer_name") + "] but found [" + contactData + "]");
        if (TestDataHelper.getTestData("customer_email") != null) {
            Assert.assertTrue(contactData.contains(TestDataHelper.getTestData("customer_email")),
                    "E-mail address of contact information does not match expected value! Expected [" + TestDataHelper.getTestData(
                            "customer_email") + "] but found [" + contactData + "]");
        }
        if (TestDataHelper.getTestData("customer_phone_number") != null) {
            Assert.assertTrue(contactData.contains(TestDataHelper.getTestData("customer_phone_number")),
                    "Phone number of contact information does not match expected value! Expected [" + TestDataHelper.getTestData(
                            "customer_phone_number") + "] but found [" + contactData + "]");
        }
        if (TestDataHelper.getTestData("custom_field_name") != null && TestDataHelper.getTestData("custom_field_text") != null) {
            Assert.assertTrue(contactData.contains(TestDataHelper.getTestData("custom_field_text")),
                    "Name of contact information does not match expected value! Expected [" + TestDataHelper.getTestData(
                            "custom_field_text") + "] but found [" + contactData + "]");
        }

        //Reservation approval
        ScenarioLogManager.getLogger().info("Checking if reservation approval alert is displayed!");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "div.m-callout--success", LocatorType.CSSSELECTOR, false),
                "Reservation approval message is not displayed!");
        Assert.assertEquals(getShadowRoot().findElement(By.cssSelector("h2.m-callout__headline")).getText().trim(), "Ihr Termin wurde bestätigt.",
                "Expected reservation message title does not match result!");
        Assert.assertEquals(getShadowRoot().findElement(By.cssSelector("div.m-callout__content > p:nth-child(1)")).getText().trim(),
                "Eine Bestätigung und weitere Informationen zu Ihrem Termin erhalten Sie per E-Mail.",
                "Expected reservation message text does not match result!");

        //Buttons
        ScenarioLogManager.getLogger().info("Checking if change and cancel appointment buttons are displayed!");
        AtomicBoolean changeAppointmentButtonFound = new AtomicBoolean(false);
        AtomicBoolean cancelAppointmentButtonFound = new AtomicBoolean(false);
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.pollingEvery(Duration.ofMillis(500L));
        wait.withMessage("Could not locate change and or cancel appointment buttons in time!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            List<WebElement> webElements = getShadowRoot().findElements(By.cssSelector("button.button-submit"));
            if (!webElements.isEmpty()) {
                for (WebElement webElement : webElements) {
                    if (webElement.findElement(By.cssSelector("span")).getText().contains("Termin umbuchen")) {
                        changeAppointmentButtonFound.set(true);
                    }
                    if (webElement.findElement(By.cssSelector("span")).getText().contains("Termin absagen")) {
                        cancelAppointmentButtonFound.set(true);
                    }
                    if (changeAppointmentButtonFound.get() && cancelAppointmentButtonFound.get()) {
                        return true;
                    }
                }
            }
            return false;
        });
    }

    public void checkIfReservationCancellationIsDisplayed() {
        shadowRootSearchContext = null;
        CONTEXT.set();

        ScenarioLogManager.getLogger().info("Checking if reservation cancellation alert is displayed!");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "div.m-callout--success", LocatorType.CSSSELECTOR, false),
                "Reservation cancellation message is not displayed!");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement headline = wait.until(driver -> {
            WebElement element = getShadowRoot().findElement(By.cssSelector("h2.m-callout__headline"));
            return element.getText().trim().equals("Sie haben Ihren Termin erfolgreich abgesagt.") ? element : null;
        });
        Assert.assertEquals(headline.getText().trim(), "Sie haben Ihren Termin erfolgreich abgesagt.", "Expected reservation message title does not match result!");
        Assert.assertEquals(getShadowRoot().findElement(By.cssSelector("div.m-callout__content > p:nth-child(1)")).getText().trim(),
                "Danke, dass Sie Ihren Termin für andere Bürger*innen freigegeben haben.", "Expected reservation message text does not match result!");

    }

    public void enterCustomerTelephoneNumber(String number) {
        ScenarioLogManager.getLogger().info("Trying to enter customer telephone number \"" + number + "\"");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.pollingEvery(Duration.ofMillis(500L));
        wait.withMessage("Could not locate customer telephone number text field in time!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            List<WebElement> webElements = getShadowRoot().findElements(By.cssSelector("input#customer-telephone"));
            if (!webElements.isEmpty()) {
                enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, number, webElements.get(0));
                return true;
            } else {
                return false;
            }
        });
    }

    public void enterTextInCustomField(String text) {
        ScenarioLogManager.getLogger().info("Trying to enter text \"" + text + "\" in custom field...");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.pollingEvery(Duration.ofMillis(500L));
        wait.withMessage("Could not locate custom text field in time!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            List<WebElement> webElements = getShadowRoot().findElements(By.cssSelector("input#customer-custom-textfield"));
            if (!webElements.isEmpty()) {
                enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, text, webElements.get(0));
                return true;
            } else {
                return false;
            }
        });
    }

    public void clickOnChangeAppointmentButton() {
        clickOnButtonBySpanText("change appointment", "Termin umbuchen");
    }

    public void clickOnConfirmChangeAppointmentButton() {
        clickOnButtonBySpanText("complete reservation", "Reservierung abschließen");
        BuergeransichtPageContext.incrementAppointmentCount();
        BuergeransichtPageContext.incrementAppointmentCanceledCount();
    }

    public void clickOnCancelAppointmentButton() {
        clickOnButtonBySpanText("cancel appointment", "Termin absagen");
        BuergeransichtPageContext.incrementAppointmentCanceledCount();
    }

    public void clickOnYesButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"yes\" button...");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.pollingEvery(Duration.ofMillis(500L));
        wait.withMessage("Could not locate yes button in time!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            List<WebElement> webElements = getShadowRoot().findElements(By.cssSelector("button.button-yes"));
            if (!webElements.isEmpty()) {
                clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, webElements.get(0), false);
                return true;
            } else {
                return false;
            }
        });
    }

    public void clickOnNoButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"no\" button...");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.pollingEvery(Duration.ofMillis(500L));
        wait.withMessage("Could not locate no button in time!");
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            List<WebElement> webElements = getShadowRoot().findElements(By.cssSelector("button.m-button--secondary"));
            if (!webElements.isEmpty()) {
                clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, webElements.get(0), false);
                return true;
            } else {
                return false;
            }
        });
    }

    public void close() {
        WindowControls.closeWindow(DRIVER, BuergeransichtPageContext.TITLE);
    }

    public void selectLocation(String location) {
        ScenarioLogManager.getLogger().info("Trying to select location '" + location + "'...");

        WebElement locationTabsContainer = getShadowRoot().findElement(By.id("location-tabs"));

        try {
            WebElement desiredTab = locationTabsContainer.findElement(By.xpath(".//div[@role='tab' and contains(text(), '" + location + "')]"));
            desiredTab.click();

            // Verify that the tab is now selected
            verifyLocationTabSelected(location);
        } catch (NoSuchElementException e) {
            Assert.fail("Location '" + location + "' not found.");
        }
    }

    public void verifyLocationTabSelected(String location) {
        ScenarioLogManager.getLogger().info("Trying to verify that the location tab '" + location + "' is selected...");

        // Locate the container with id="location-tabs"
        WebElement locationTabsContainer = getShadowRoot().findElement(By.id("location-tabs"));

        // Using XPath to directly locate the tab with the specified location
        try {
            WebElement selectedTab = locationTabsContainer.findElement(By.xpath(".//div[@role='tab' and contains(text(), '" + location + "')]"));

            boolean isSelected = "true".equals(selectedTab.getAttribute("aria-selected"));
            Assert.assertTrue(isSelected, "Tab with location '" + location + "' is not selected.");
        } catch (NoSuchElementException e) {
            Assert.fail("Location '" + location + "' not found during verification.");
        }
    }

    public void calendarIsVisibleForAppointmentSelection() {
        ScenarioLogManager.getLogger().info("Check if the calender for appointment selection is visible...");
        WebElement calender = waitForShadowRootElement(getShadowRoot(), By.cssSelector(".v-picker.v-card.v-picker--date"), true);

        boolean calenderVisibility = calender.isDisplayed();
        Assert.assertTrue(calenderVisibility, "Calendar for appointment selection is not visible!");
    }

    public void slotsAreVisibleForAppointmentSelection() {
        ScenarioLogManager.getLogger().info("Check if the slots for appointment selection are visible...");
        WebElement slots = waitForShadowRootElement(getShadowRoot(), By.cssSelector("#appointments"), true);

        boolean slotsVisibility = slots.isDisplayed();
        Assert.assertTrue(slotsVisibility, "Slots for appointment selection are not visible!");
    }

    public void informationAboutTheAppointmentBookingIsVisibleToTheCustomer() {
        ScenarioLogManager.getLogger().info("Check if the information for the appointment booking is visible...");

        // Wait for the shadow DOM element and locate the <b> element inside the <p>
        WebElement info = waitForShadowRootElement(getShadowRoot(), By.cssSelector("p[data-v-1dd26cac][tabindex='5'] > b"), true);

        // Ensure the element is displayed
        Assert.assertTrue(info.isDisplayed(), "Information for the appointment booking is not visible!");

        String fontWeight = info.getCssValue("font-weight");
        boolean isBold = fontWeight.equals("bold") || Integer.parseInt(fontWeight) >= 700;
        Assert.assertTrue(isBold, "The text is not bold!");

        String backgroundColor = info.getCssValue("background-color");

        boolean isBackgroundRed = backgroundColor.equals("rgba(255, 0, 0, 1)") || backgroundColor.equals("rgb(255, 0, 0)");
        Assert.assertTrue(isBackgroundRed, "The background color is not Red!");

        ScenarioLogManager.getLogger().info("The information for the appointment booking is correctly styled and visible.");
    }

}
