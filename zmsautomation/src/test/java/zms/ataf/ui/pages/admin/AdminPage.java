package zms.ataf.ui.pages.admin;

import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.time.Duration;
import java.util.List;

import org.openqa.selenium.By;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedCondition;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.helpers.AuthenticationHelper;
import ataf.core.helpers.TestDataHelper;
import ataf.core.helpers.TestPropertiesHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.core.properties.DefaultValues;
import ataf.web.model.LocatorType;
import ataf.web.pages.BasePage;
import ataf.web.utils.DriverUtil;
import zms.ataf.data.TestData;


public class AdminPage extends BasePage {

    protected final AdminPageContext CONTEXT;

    public AdminPage(RemoteWebDriver driver) {
        super(driver);
        CONTEXT = new AdminPageContext(driver);
    }

    public AdminPage(RemoteWebDriver driver, AdminPageContext adminPageContext) {
        super(driver);
        CONTEXT = adminPageContext;
    }

    public AdminPageContext getContext() {
        return CONTEXT;
    }

    public void navigateToPage() {
        CONTEXT.navigateToPage();
    }

    public void clickOnLoginButton() throws Exception {
        ScenarioLogManager.getLogger().info("Trying to click on \"Login\" button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[@type='submit' and @value='keycloak']", LocatorType.XPATH, false);
        if (!DriverUtil.isLocalExecution() || TestPropertiesHelper.getPropertyAsBoolean("useIncognitoMode", true, DefaultValues.USE_INCOGNITO_MODE)) {
            ScenarioLogManager.getLogger().info("SSO-Login page detected!");

            final StringBuilder clearUserName = new StringBuilder();
            final StringBuilder clearPassword = new StringBuilder();
            Exception exception = null;
            try {
                AuthenticationHelper.getUserName().access(clearUserName::append);
                AuthenticationHelper.getUserPassword().access(clearPassword::append);
                // Wait for Keycloak login form (local and ssodev both use id="username")
                WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
                wait.until(ExpectedConditions.presenceOfElementLocated(By.id("username")));
                if ("chrome".equals(TestPropertiesHelper.getPropertyAsString("browser", true, DefaultValues.BROWSER))) {
                    String ssoHost = TestData.getSsoHost();
                    wait.until(ExpectedConditions.presenceOfElementLocated(By.id("kc-login")));
                    DRIVER.navigate().to(DRIVER.getCurrentUrl().replaceFirst("https://",
                            "https://" + URLEncoder.encode(clearUserName.toString(), StandardCharsets.UTF_8) + ":" + URLEncoder.encode(clearPassword.toString(),
                                    StandardCharsets.UTF_8) + "@"));
                }

                ScenarioLogManager.getLogger().info("Trying to enter user name...");
                enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, clearUserName.toString(), "username", LocatorType.ID);

                ScenarioLogManager.getLogger().info("Trying to enter password...");
                enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, clearPassword.toString(), "password", LocatorType.ID);

                ScenarioLogManager.getLogger().info("Trying to click on \"Login\" button...");
                clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "kc-login", LocatorType.ID, false);
                ScenarioLogManager.getLogger().info("SSO login submitted successfully.");
            } catch (Exception e) {
                ScenarioLogManager.getLogger().error(e.getMessage(), e);
                exception = e;
            } finally {
                clearUserName.setLength(0);
                clearPassword.setLength(0);
                if (exception != null) {
                    throw exception;
                }
            }
        }
    }

    public void selectLocation(String location) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to select location \"" + location + "\"");
        selectDropDownListValueByVisibleText(DEFAULT_EXPLICIT_WAIT_TIME, "scope", LocatorType.NAME, location);
        TestDataHelper.setTestData("location", location);
    }

    public void enterWorkstation(String workstation) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to enter workstation \"" + workstation + "\"");
        enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, workstation, "workstation", LocatorType.NAME);
        TestDataHelper.setTestData("workstation", workstation);
    }

    public void clickOnApplySelectionButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Auswahl bestätigen\" button...");
        CONTEXT.set();
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[@type='submit' and @value='weiter']", LocatorType.XPATH, false, CONTEXT);
    }

    //prüfen ob die eingegebenen Standort und Platz-Nr im Seitenkopf angezeigt werden.
    public void enteredWorkplaceInformationMatchWithPageHeader() {
        String actualLocation = findElementByLocatorType(".user-scope.header-scope-title", LocatorType.CSSSELECTOR, true)
                .getAttribute("title");
        String expectedLocation = TestDataHelper.getTestData("location");
        boolean location = expectedLocation.contains(actualLocation);

        String actualWorkstation = findElementByLocatorType("//li[contains(@class, 'user-workstation')]/div/strong", LocatorType.XPATH, true)
                .getText();
        String expectedWorkstation = TestDataHelper.getTestData("workstation");
        boolean workstation = actualWorkstation.contains(expectedWorkstation);

        Assert.assertTrue(location,
                String.format("Der Standort stimmt nicht mit dem Seitenkopf überein. Erwartet: %s, Gefunden: %s", expectedLocation, actualLocation));
        Assert.assertTrue(workstation,
                String.format("Die Arbeitsplatznummer stimmt nicht mit dem Seitenkopf überein. Erwartet: %s, Gefunden: %s", expectedWorkstation,
                        actualWorkstation));
    }

    public void clickInHeaderOnChangeSelectionButton() {
        ScenarioLogManager.getLogger().info("Trying to click on 'Auswahl ändern' Button");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//i[@class='fas fa-edit']", LocatorType.XPATH, true, CONTEXT);
    }

    public void clickInNavigationOnTresenButton() {
        ScenarioLogManager.getLogger().info("Trying to click on 'Tresen' Button");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//a[@title='Termine vereinbaren, ändern & löschen und die Warteschlange verwalten']", LocatorType.XPATH,
                true, CONTEXT);
    }

    public void checkForLocationPage() {
        ScenarioLogManager.getLogger().info("Checking if 'Standort auswählen' page is correctly loaded.");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//h2[@class='board__heading']", LocatorType.XPATH, false),
                "'Standort und Arbeitsplatz auswählen' Header is not visible");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//button[@value='weiter']", LocatorType.XPATH, true),
                "'Auswahl bestätigen' Button is not visible");
    }

    public boolean isPopUpVisible(String popUpName) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.pollingEvery(Duration.ofMillis(500L));
        try {
            wait.until((ExpectedCondition<Boolean>) waitDriver -> {
                List<WebElement> titleElements = findElementsByLocatorType(0L,
                        "//div[@role='dialog']/section[contains(@class, 'dialog')]/h2[contains(@class, 'title')]", LocatorType.XPATH);
                if (!titleElements.isEmpty()) {
                    for (WebElement titleElement : titleElements) {
                        if (titleElement.isDisplayed()) {
                            return titleElement.getText().contains(popUpName);
                        }
                    }
                }
                return false;
            });
        } catch (TimeoutException e) {
            return false;
        }
        return true;
    }

    public boolean isPopUpInvisible(String popUpName) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.pollingEvery(Duration.ofMillis(500L));
        try {
            wait.until(ExpectedConditions.invisibilityOfElementLocated(By.xpath(
                    String.format("//div[@role='dialog']/section[contains(@class, 'dialog')]/h2[contains(@class, 'title') and text()='%s']", popUpName))));
        } catch (TimeoutException e) {
            return false;
        }
        return true;
    }

}
