package zms.ataf.hooks;

import io.cucumber.java.Before;
import org.openqa.selenium.Cookie;
import org.openqa.selenium.WebDriver;

/**
 * Injects the mocked time into the web app for UI scenarios without depending on ATAF classes.
 * - Reads ZMS_TIMEADJUST from the environment (absolute datetime, e.g., "2026-03-02 10:00:00").
 * - Obtains WebDriver via reflection from ataf.web.util.DriverUtil#getDriver() after ATAF hooks ran.
 * - Navigates once to a root URL to establish a domain, then sets a "ZMS_TIMEADJUST" cookie.
 * - Works with existing /.htaccess that maps cookie/header/query into ZMS_TIMEADJUST per request.
 */
public class TimeMockHook {

    // Run late so ATAF's driver setup has already executed
    @Before(order = 10000)
    public void injectMockTimeCookie() {
        final String mock = System.getenv("ZMS_TIMEADJUST");
        if (mock == null || mock.isBlank()) {
            return;
        }

        final WebDriver driver = waitForDriver(10_000); // wait up to 10s for driver
        if (driver == null) {
            System.out.println("TimeMockHook: WebDriver not available after wait; skipping cookie injection.");
            return;
        }

        final String root = System.getenv().getOrDefault("ZMS_WEB_ROOT", "http://localhost/terminvereinbarung/");
        try {
            String current = "";
            try { current = driver.getCurrentUrl(); } catch (Exception ignored) {}
            if (current == null || current.isBlank() || "about:blank".equalsIgnoreCase(current)) {
                driver.get(root);
            }
        } catch (Exception e) {
            try { driver.get(root); } catch (Exception ignored) { return; }
        }

        try {
            Cookie cookie = new Cookie.Builder("ZMS_TIMEADJUST", mock)
                    .path("/")
                    .isHttpOnly(false)
                    .isSecure(false)
                    .build();
            driver.manage().addCookie(cookie);
            System.out.println("TimeMockHook: Injected ZMS_TIMEADJUST cookie: " + mock);
        } catch (Exception e) {
            System.out.println("TimeMockHook: Failed to inject time cookie: " + e.getMessage());
        }
    }

    private WebDriver waitForDriver(long millis) {
        long deadline = System.currentTimeMillis() + millis;
        while (System.currentTimeMillis() < deadline) {
            WebDriver d = tryGetDriverViaReflection();
            if (d != null) return d;
            try { Thread.sleep(200); } catch (InterruptedException ie) { Thread.currentThread().interrupt(); break; }
        }
        return null;
    }

    private WebDriver tryGetDriverViaReflection() {
        try {
            Class<?> util = Class.forName("ataf.web.util.DriverUtil");
            Object drv = util.getMethod("getDriver").invoke(null);
            if (drv instanceof WebDriver) {
                return (WebDriver) drv;
            }
        } catch (Throwable ignored) {
            // ATAF not on classpath or driver not created yet
        }
        return null;
    }
}