package zms.ataf.hooks;

import org.openqa.selenium.remote.RemoteWebDriver;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.utils.DriverUtil;
import io.cucumber.java.Before;
import zms.ataf.helpers.ViewportSizes;

/**
 * After ATAF starts the browser for {@code @web}, shrink the window for {@code @mobile}
 * scenarios (phone CSS viewport used by zmscitizenview layout checks).
 */
public class MobileViewportHook {

    /** After ATAF {@code @web} init (default 10000) and {@link WebDriverQuitHook} capture (10001). */
    @Before(value = "@mobile", order = 10002)
    public void setMobileViewport() {
        RemoteWebDriver driver = DriverUtil.getDriver();
        driver.manage().window().setSize(ViewportSizes.MOBILE);
        ScenarioLogManager.getLogger()
                .info("Viewport set to mobile {}x{} for @mobile scenario",
                        ViewportSizes.MOBILE.getWidth(), ViewportSizes.MOBILE.getHeight());
    }
}
