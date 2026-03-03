package zms.ataf.hooks;

import io.cucumber.java.Before;
import org.openqa.selenium.Cookie;
import org.openqa.selenium.WebDriver;

/**
 * Injects the mocked time into the web app for UI scenarios without depending on ATAF classes.
 * - Reads ZMS_TIMEADJUST from the environment (absolute datetime, e.g., "2026-03-02 10:00:00").
 * - Tries to obtain the WebDriver via reflection from ataf.web.util.DriverUtil#getDriver() if available.
 * - Navigates once to a root URL to establish a domain, then sets a "ZMS_TIMEADJUST" cookie.
 * - Works with the existing /.htaccess mapping that reads cookie/header/query into ZMS_TIMEADJUST per request.
 */
public class TimeMockHook {

    @Before(order = 0)
    public void injectMockTimeCookie() {
        final String mock = System.getenv("ZMS_TIMEADJUST");
        if (mock == null || mock.isBlank()) {
            // No mocked time configured -> nothing to inject
            return;
        }

        final WebDriver driver = tryGetDriverViaReflection();
        if (driver == null) {
            // Driver not available in this profile/run; skip quietly
            System.out.println("TimeMockHook: WebDriver not available; skipping time cookie injection.");
            return;
        }

        // Establish a domain before adding the cookie
        final String root = System.getenv().getOrDefault("ZMS_WEB_ROOT", "http://localhost/terminvereinbarung/");
        try {
            String current = "";
            try { current = driver.getCurrentUrl(); } catch (Exception ignored) {}
            if (current == null || current.isBlank() || "about:blank".equalsIgnoreCase(current)) {
                driver.get(root);
            }
        } catch (Exception e) {
            // As a fallback, try once more to reach the root; if it still fails, bail out silently
            try { driver.get(root); } catch (Exception ignored) { return; }
        }

        try {
            Cookie cookie = new Cookie.Builder("ZMS_TIMEADJUST", mock)
                    .path("/")               // apply to whole app
                    .isHttpOnly(false)
                    .isSecure(false)
                    .build();
            driver.manage().addCookie(cookie);
            System.out.println("TimeMockHook: Injected ZMS_TIMEADJUST cookie: " + mock);
        } catch (Exception e) {
            System.out.println("TimeMockHook: Failed to inject time cookie: " + e.getMessage());
        }
    }

    /**
     * Attempts to obtain the WebDriver via ATAF's DriverUtil#getDriver() using reflection,
     * so this class compiles even when ATAF is not on the classpath.
     */
    private WebDriver tryGetDriverViaReflection() {
        try {
            Class<?> util = Class.forName("ataf.web.util.DriverUtil");
            Object drv = util.getMethod("getDriver").invoke(null);
            if (drv instanceof WebDriver) {
                return (WebDriver) drv;
            }
        } catch (Throwable ignored) {
            // ATAF not on classpath or method not available — fall through
        }
        return null;
    }
}