package zms.ataf.hooks;

import org.openqa.selenium.remote.RemoteWebDriver;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.utils.DriverUtil;
import io.cucumber.java.After;
import io.cucumber.java.Before;
import io.cucumber.java.Scenario;

/**
 * ATAF {@code DriverUtil.closeDriver()} only calls {@code quit()} on Selenium Grid.
 * Local {@code @executeLocally} runs just drop the thread map, so Chrome/Edge/Firefox
 * processes pile up and the next session hangs.
 */
public class WebDriverQuitHook {

    private static final ThreadLocal<RemoteWebDriver> LOCAL_DRIVER = new ThreadLocal<>();

    @Before(value = "@web", order = 10001)
    public void captureDriver(Scenario scenario) {
        try {
            LOCAL_DRIVER.set(DriverUtil.getDriver());
        } catch (RuntimeException e) {
            ScenarioLogManager.getLogger().warn("Could not capture WebDriver for local quit: {}", e.toString());
        }
    }

    @After(value = "@web", order = 9999)
    public void quitLocalDriver(Scenario scenario) {
        RemoteWebDriver driver = LOCAL_DRIVER.get();
        LOCAL_DRIVER.remove();
        if (driver == null || !DriverUtil.isLocalExecution()) {
            return;
        }
        try {
            driver.quit();
        } catch (RuntimeException e) {
            ScenarioLogManager.getLogger().warn("WebDriver quit failed: {}", e.toString());
        }
    }
}
