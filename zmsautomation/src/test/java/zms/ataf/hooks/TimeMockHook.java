package zms.ataf.hooks;

import ataf.core.log.ScenarioLogManager;
import ataf.web.util.DriverUtil;
import io.cucumber.java.Before;
import org.openqa.selenium.Cookie;
import org.openqa.selenium.remote.RemoteWebDriver;

public class TimeMockHook {
    @Before(order = 0)
    public void injectMockTimeCookie() {
        String mock = System.getenv("ZMS_TIMEADJUST");
        if (mock == null || mock.isBlank()) return;

        RemoteWebDriver driver = DriverUtil.getDriver();
        try {
            String root = System.getenv().getOrDefault("ZMS_WEB_ROOT", "http://localhost/terminvereinbarung/");
            driver.get(root); // establish domain

            Cookie cookie = new Cookie.Builder("ZMS_TIMEADJUST", mock)
                    .domain("localhost") // adjust if different
                    .path("/")
                    .isHttpOnly(false)
                    .isSecure(false)
                    .build();
            driver.manage().addCookie(cookie);
            ScenarioLogManager.getLogger().info("Injected ZMS_TIMEADJUST cookie: " + mock);
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn("Failed to inject ZMS_TIMEADJUST cookie: " + e.getMessage());
        }
    }
}