package ataf.web.pages;

import ataf.core.logging.ScenarioLogManager;
import ataf.core.properties.DefaultValues;
import ataf.core.properties.TestProperties;
import ataf.web.model.LocatorType;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.time.Duration;

/**
 * Represents a Single Sign-On (SSO) login page and provides methods to perform SSO login
 * operations. This class extends the {@link BasePage} and utilizes the
 * WebDriver to interact with the SSO page elements.
 *
 * <p>
 * This class is specifically designed to handle login operations for applications using the SSO
 * system of the "muenchen.de" domain. It includes support for
 * both automatic URL modification in Chrome browsers and manual entry of username and password.
 * </p>
 *
 * <p>
 * Usage example:
 * </p>
 *
 * <pre>
 * {@code
 * SingleSignOnPage ssoPage = new SingleSignOnPage(driver);
 * ssoPage.executeSingleSignOnLogin("username", "password");
 * }
 * </pre>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class SingleSignOnPage extends BasePage {
    /**
     * Constructs a new SingleSignOnPage.
     *
     * @param driver the RemoteWebDriver instance to be used for interacting with the SSO page.
     */
    public SingleSignOnPage(RemoteWebDriver driver) {
        super(driver);
    }

    /**
     * Executes the Single Sign-On login process by entering the provided username and password into the
     * corresponding fields and clicking the login button. If
     * the browser is Chrome, it modifies the URL to include the username and password for automatic
     * login.
     *
     * @param userName the username to be entered in the SSO login form.
     * @param password the password to be entered in the SSO login form.
     */
    public void executeSingleSignOnLogin(String userName, String password) {
        if ("chrome".equalsIgnoreCase(
                TestProperties.getProperty("browser", true, DefaultValues.BROWSER).orElse(DefaultValues.BROWSER))) {
            WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
            wait.until(ExpectedConditions.urlMatches("sso(dev|test)\\.muenchen\\.de"));
            DRIVER.navigate().to(DRIVER.getCurrentUrl().replaceFirst("https://",
                    "https://" + URLEncoder.encode(userName, StandardCharsets.UTF_8) + ":" + URLEncoder.encode(password, StandardCharsets.UTF_8) + "@"));
        }

        ScenarioLogManager.getLogger().info("Trying to enter user name...");
        enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, userName, "username", LocatorType.ID);

        ScenarioLogManager.getLogger().info("Trying to enter password...");
        enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, password, "password", LocatorType.ID);

        ScenarioLogManager.getLogger().info("Trying to click on \"Login\" button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "kc-login", LocatorType.ID, false);
    }
}
