package zms.ataf.ui.pages.admin;

import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.time.Duration;
import java.time.Instant;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.ZoneId;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.Optional;
import java.util.regex.Pattern;

import org.openqa.selenium.By;
import org.openqa.selenium.Cookie;
import org.openqa.selenium.JavascriptExecutor;
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

    // 1) Read target time from env/sysprop and convert to epochMillis (TZ-aware)
    private String resolveMockNow() {
        String v = System.getenv("ZMS_TIMEADJUST");
        if (v == null || v.isBlank())
            v = System.getProperty("ZMS_TIMEADJUST");
        return (v != null && !v.isBlank()) ? v.trim() : null;
    }

    private Long resolveMockEpochMillis() {
        String raw = resolveMockNow();
        if (raw == null)
            return null;
        String v = raw.replace(' ', 'T'); // normalize "YYYY-MM-DD HH:mm" → ISO-ish
        ZoneId zone = Optional.ofNullable(System.getenv("TZ"))
                .filter(s -> !s.isBlank())
                .map(tz -> {
                    try {
                        return ZoneId.of(tz);
                    } catch (Exception e) {
                        return null;
                    }
                })
                .orElse(ZoneId.systemDefault());

        try { // full ISO with offset/Z
            if (Pattern.compile("[zZ]|[+-][0-9]{2}:[0-9]{2}").matcher(v).find()) {
                return Instant.parse(v).toEpochMilli();
            }
        } catch (Exception ignored) {
        }

        DateTimeFormatter[] fmts = new DateTimeFormatter[] {
                DateTimeFormatter.ofPattern("yyyy-MM-dd'T'HH:mm:ss"),
                DateTimeFormatter.ofPattern("yyyy-MM-dd'T'HH:mm")
        };
        for (DateTimeFormatter f : fmts) {
            try {
                return LocalDateTime.parse(v, f).atZone(zone).toInstant().toEpochMilli();
            } catch (Exception ignored) {
            }
        }
        try { // date only
            return LocalDate.parse(v.substring(0, 10), DateTimeFormatter.ISO_LOCAL_DATE)
                    .atStartOfDay(zone).toInstant().toEpochMilli();
        } catch (Exception ignored) {
        }
        return null;
    }

    // 2) Optional: set cookie (helps app-side and gives us a fallback)
    private void ensureMockTimeCookie() {
        String raw = resolveMockNow();
        if (raw == null)
            return;
        String cookieVal = raw.replace(' ', 'T');
        Cookie c = new Cookie.Builder("ZMS_TIMEADJUST", cookieVal).path("/").isHttpOnly(false).build();
        DRIVER.manage().addCookie(c);
        DRIVER.navigate().refresh();
        ScenarioLogManager.getLogger().info("Set client side mock time cookie to " + cookieVal);
    }

    // 3) The actual shim: override Date and performance.now using a Proxy; run in
    // current context
    private void injectClockShimWithEpoch(long serverMs) {
        String script = """
                    (function(){
                      if (window.__ZMS_CLOCK_SKEW_APPLIED__) return;
                      window.__ZMS_CLOCK_SKEW_APPLIED__ = true;
                      const NativeDate = Date;
                      const baseNow = NativeDate.now();
                      const skew    = serverMs - baseNow;

                      const FakeDate = new Proxy(NativeDate, {
                        construct(target, args) {
                          if (!args || args.length === 0) return new target(target.now() + skew);
                          return new target(...args);
                        },
                        apply(target, thisArg, args) {
                          if (!args || args.length === 0) return new target(target.now() + skew).toString();
                          return target.apply(thisArg, args);
                        }
                      });
                      // copy static props and adjust now()
                      Object.getOwnPropertyNames(NativeDate).forEach(k => { try { FakeDate[k] = NativeDate[k]; } catch(e){} });
                      FakeDate.now = () => NativeDate.now() + skew;

                      try {
                        Object.defineProperty(window, 'Date', { value: FakeDate, writable: false, configurable: true });
                        if (typeof globalThis !== 'undefined') {
                          Object.defineProperty(globalThis, 'Date', { value: FakeDate, writable: false, configurable: true });
                        }
                      } catch(e) { window.Date = FakeDate; }

                      if (window.performance && typeof performance.now === 'function') {
                        const base = performance.now(), start = NativeDate.now();
                        performance.now = () => base + ((NativeDate.now() - start) + skew);
                      }
                      console.log('[ATAF] Browser clock skew(ms):', skew);
                    })();
                """
                .replace("serverMs", Long.toString(serverMs));
        ((JavascriptExecutor) DRIVER).executeScript(script);
    }

    // 4) Apply shim in top window and recursively in all iframes
    private void applyShimInAllFramesRecursive(long serverMs) {
        // top-level
        injectClockShimWithEpoch(serverMs);
        // into frames
        List<WebElement> frames = DRIVER.findElements(By.tagName("iframe"));
        for (int i = 0; i < frames.size(); i++) {
            try {
                DRIVER.switchTo().frame(i);
                injectClockShimWithEpoch(serverMs);
                // recurse nested frames if any
                applyShimInAllFramesRecursiveWithinContext(serverMs);
            } catch (Exception ignored) {
            } finally {
                DRIVER.switchTo().parentFrame();
            }
        }
    }

    // helper for nested frames (current context)
    private void applyShimInAllFramesRecursiveWithinContext(long serverMs) {
        List<WebElement> nested = DRIVER.findElements(By.tagName("iframe"));
        for (int i = 0; i < nested.size(); i++) {
            try {
                DRIVER.switchTo().frame(i);
                injectClockShimWithEpoch(serverMs);
                applyShimInAllFramesRecursiveWithinContext(serverMs);
            } catch (Exception ignored) {
            } finally {
                DRIVER.switchTo().parentFrame();
            }
        }
    }

    // Deterministic shim: use epoch millis computed in Java, no fetch/parse in the
    // page
    public void alignBrowserClockWithServer() {
        Long serverMs = resolveMockEpochMillis();
        if (serverMs == null) {
            ScenarioLogManager.getLogger().warn("No valid ZMS_TIMEADJUST to inject; skipping clock shim.");
            return;
        }
        String script = """
                (function(){
                  if (window.__ZMS_CLOCK_SKEW_APPLIED__) return;
                  window.__ZMS_CLOCK_SKEW_APPLIED__ = true;
                  const serverMs = %d;
                  const NativeDate = Date;
                  const start = NativeDate.now();
                  const skew  = serverMs - start;

                  function MockDate(){ if (this instanceof MockDate) {
                      if (arguments.length === 0) return new NativeDate(NativeDate.now()+skew);
                      return new (Function.prototype.bind.apply(NativeDate, [null].concat(Array.from(arguments))))();
                    }
                    return NativeDate.apply(this, arguments);
                  }
                  MockDate.prototype = NativeDate.prototype;
                  MockDate.now   = function(){ return NativeDate.now() + skew; };
                  MockDate.UTC   = NativeDate.UTC;
                  MockDate.parse = NativeDate.parse;
                  window.Date    = MockDate;

                  if (window.performance && typeof performance.now === 'function') {
                    const base = performance.now(), s = NativeDate.now();
                    performance.now = function(){ return base + ((NativeDate.now() - s) + skew); };
                  }
                  console.log('[ATAF] Browser clock skew(ms):', skew);
                })();
                """.formatted(serverMs);

        try {
            ((JavascriptExecutor) DRIVER).executeScript(script);
            String jsNow = (String) ((JavascriptExecutor) DRIVER)
                    .executeScript("return new Date().toISOString()");
            ScenarioLogManager.getLogger().info("[CLIENT JS now] " + jsNow);
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn("Clock shim injection failed: " + e.getMessage());
        }
    }

    // 5) Public entry: compute epoch and apply everywhere; also log verification
    public void alignBrowserClockEverywhere() {
        Long serverMs = resolveMockEpochMillis();
        if (serverMs == null) {
            ScenarioLogManager.getLogger().warn("No valid ZMS_TIMEADJUST to inject; skipping clock shim.");
            return;
        }
        applyShimInAllFramesRecursive(serverMs);
        try {
            String href = String.valueOf(((JavascriptExecutor) DRIVER).executeScript("return location.href"));
            String topNow = String
                    .valueOf(((JavascriptExecutor) DRIVER).executeScript("return new Date().toISOString()"));
            ScenarioLogManager.getLogger().info("[CLOCK] href=" + href + " top.now=" + topNow);
            // Try first iframe, if exists
            List<WebElement> ifr = DRIVER.findElements(By.tagName("iframe"));
            if (!ifr.isEmpty()) {
                DRIVER.switchTo().frame(0);
                String fNow = String
                        .valueOf(((JavascriptExecutor) DRIVER).executeScript("return new Date().toISOString()"));
                ScenarioLogManager.getLogger().info("[CLOCK] iframe0.now=" + fNow);
                DRIVER.switchTo().parentFrame();
            }
        } catch (Exception ignored) {
        }
    }

    public void clickOnLoginButton() throws Exception {
        ScenarioLogManager.getLogger().info("Trying to click on \"Login\" button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[@type='submit' and @value='keycloak']",
                LocatorType.XPATH, false);
        if (!DriverUtil.isLocalExecution() || TestPropertiesHelper.getPropertyAsBoolean("useIncognitoMode", true,
                DefaultValues.USE_INCOGNITO_MODE)) {
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
                            "https://" + URLEncoder.encode(clearUserName.toString(), StandardCharsets.UTF_8) + ":"
                                    + URLEncoder.encode(clearPassword.toString(),
                                            StandardCharsets.UTF_8)
                                    + "@"));
                }

                ScenarioLogManager.getLogger().info("Trying to enter user name...");
                enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, clearUserName.toString(), "username", LocatorType.ID);

                ScenarioLogManager.getLogger().info("Trying to enter password...");
                enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, clearPassword.toString(), "password", LocatorType.ID);

                ScenarioLogManager.getLogger().info("Trying to click on \"Login\" button...");
                clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "kc-login", LocatorType.ID, false);
                ScenarioLogManager.getLogger().info("SSO login submitted successfully.");
                ensureMockTimeCookie();
                alignBrowserClockWithServer();
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
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[@type='submit' and @value='weiter']", LocatorType.XPATH,
                false, CONTEXT);
        CONTEXT.waitForSpinners();
        // Re-apply shim after navigation
        alignBrowserClockEverywhere();
    }

    // prüfen ob die eingegebenen Standort und Platz-Nr im Seitenkopf angezeigt
    // werden.
    public void enteredWorkplaceInformationMatchWithPageHeader() {
        String actualLocation = findElementByLocatorType(".user-scope.header-scope-title", LocatorType.CSSSELECTOR,
                true)
                .getAttribute("title");
        String expectedLocation = TestDataHelper.getTestData("location");
        boolean location = expectedLocation.contains(actualLocation);

        String actualWorkstation = findElementByLocatorType("//li[contains(@class, 'user-workstation')]/div/strong",
                LocatorType.XPATH, true)
                .getText();
        String expectedWorkstation = TestDataHelper.getTestData("workstation");
        boolean workstation = actualWorkstation.contains(expectedWorkstation);

        Assert.assertTrue(location,
                String.format("Der Standort stimmt nicht mit dem Seitenkopf überein. Erwartet: %s, Gefunden: %s",
                        expectedLocation, actualLocation));
        Assert.assertTrue(workstation,
                String.format(
                        "Die Arbeitsplatznummer stimmt nicht mit dem Seitenkopf überein. Erwartet: %s, Gefunden: %s",
                        expectedWorkstation,
                        actualWorkstation));
    }

    public void clickInHeaderOnChangeSelectionButton() {
        ScenarioLogManager.getLogger().info("Trying to click on 'Auswahl ändern' Button");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//i[@class='fas fa-edit']", LocatorType.XPATH, true, CONTEXT);
    }

    public void clickInNavigationOnTresenButton() {
        ScenarioLogManager.getLogger().info("Trying to click on 'Tresen' Button");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME,
                "//a[@title='Termine vereinbaren, ändern & löschen und die Warteschlange verwalten']",
                LocatorType.XPATH,
                true, CONTEXT);
    }

    public void checkForLocationPage() {
        ScenarioLogManager.getLogger().info("Checking if 'Standort auswählen' page is correctly loaded.");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//h2[@class='board__heading']", LocatorType.XPATH,
                        false),
                "'Standort und Arbeitsplatz auswählen' Header is not visible");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//button[@value='weiter']", LocatorType.XPATH, true),
                "'Auswahl bestätigen' Button is not visible");
    }

    public boolean isPopUpVisible(String popUpName) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.pollingEvery(Duration.ofMillis(500L));
        try {
            wait.until((ExpectedCondition<Boolean>) waitDriver -> {
                List<WebElement> titleElements = findElementsByLocatorType(0L,
                        "//div[@role='dialog']/section[contains(@class, 'dialog')]/h2[contains(@class, 'title')]",
                        LocatorType.XPATH);
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
                    String.format(
                            "//div[@role='dialog']/section[contains(@class, 'dialog')]/h2[contains(@class, 'title') and text()='%s']",
                            popUpName))));
        } catch (TimeoutException e) {
            return false;
        }
        return true;
    }

}
