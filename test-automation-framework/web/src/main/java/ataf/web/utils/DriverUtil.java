package ataf.web.utils;

import ataf.core.logging.ScenarioLogManager;
import ataf.core.properties.DefaultValues;
import ataf.core.properties.TestProperties;
import org.jetbrains.annotations.NotNull;
import org.openqa.selenium.NoSuchSessionException;
import org.openqa.selenium.PageLoadStrategy;
import org.openqa.selenium.Proxy;
import org.openqa.selenium.SessionNotCreatedException;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.openqa.selenium.edge.EdgeDriver;
import org.openqa.selenium.edge.EdgeOptions;
import org.openqa.selenium.firefox.FirefoxDriver;
import org.openqa.selenium.firefox.FirefoxDriverLogLevel;
import org.openqa.selenium.firefox.FirefoxOptions;
import org.openqa.selenium.firefox.FirefoxProfile;
import org.openqa.selenium.logging.LogType;
import org.openqa.selenium.logging.LoggingPreferences;
import org.openqa.selenium.remote.CapabilityType;
import org.openqa.selenium.remote.LocalFileDetector;
import org.openqa.selenium.remote.RemoteWebDriver;

import java.io.File;
import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;
import java.nio.file.FileVisitResult;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.file.SimpleFileVisitor;
import java.nio.file.attribute.BasicFileAttributes;
import java.time.Duration;
import java.util.HashMap;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;
import java.util.logging.Level;

/**
 * DriverUtil is a utility class for managing WebDriver instances in a Selenium-based test
 * automation framework. It provides methods to initialize, retrieve,
 * and close WebDriver instances for various browsers including Firefox, Chrome, and Edge, and
 * supports both local and Selenium Grid executions.
 * <p>
 * The class maintains a map of WebDriver instances associated with the current thread, allowing for
 * thread-safe WebDriver management during parallel test
 * execution. Additionally, it manages proxy settings and browser-specific configurations.
 *
 * <p>
 * Note: This class is designed to be used in conjunction with the TestProperties and DefaultValues
 * classes to retrieve configuration properties for browser
 * setup.
 * </p>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public final class DriverUtil {
    private static final Map<Long, RemoteWebDriver> REMOTE_WEB_DRIVER_MAP = new ConcurrentHashMap<>();
    private static final Map<Long, Boolean> IS_LOCAL_EXECUTION_MAP = new ConcurrentHashMap<>();

    /**
     * Retrieves the {@link RemoteWebDriver} instance associated with the current thread.
     * <p>
     * This method ensures that each thread has access to its corresponding WebDriver instance as stored
     * in the {@code REMOTE_WEB_DRIVER_MAP}. If no WebDriver
     * instance exists for the current thread, an error is logged, and a
     * {@link WebDriverNotInitializedException} is thrown.
     * </p>
     *
     * <p>
     * Common usage:
     * </p>
     *
     * <pre>{@code
     * RemoteWebDriver driver = DriverUtil.getDriver();
     * driver.get("https://example.com");
     * }</pre>
     *
     * @return the {@link RemoteWebDriver} instance associated with the current thread.
     * @throws WebDriverNotInitializedException if no WebDriver instance is found for the current
     *             thread.
     *
     *             <p>
     *             Potential causes for the exception:
     *             </p>
     *             <ul>
     *             <li>The WebDriver was not initialized for the current thread.</li>
     *             <li>A necessary tag (e.g., 'web') was not added to the Cucumber scenario.</li>
     *             </ul>
     */
    public static RemoteWebDriver getDriver() {
        RemoteWebDriver driver = REMOTE_WEB_DRIVER_MAP.get(Thread.currentThread().getId());
        if (driver == null) {
            ScenarioLogManager.getLogger()
                    .error("No WebDriver instance for thread [{}]. Possible missing 'web' tag in Cucumber scenario.", Thread.currentThread().getId());
            throw new WebDriverNotInitializedException("For thread [" + Thread.currentThread()
                    .getId()
                    + "] no WebDriver instance was created! Did you forget to add the 'web' tag to your Cucumber scenario? See: https://git.muenchen.de/test-tool-projects/agile-test-automation-framework/java/test-automation-framework/-/tree/main#verwendung");
        }
        return driver;
    }

    /***
     * Initializes a new WebDriver instance based on the provided
     * execution mode (local or Selenium Grid). This method configures
     * the browser options, proxy settings, and other parameters
     * required for the WebDriver.
     *
     * @param isLocalExecution Indicates whether the tests are being
     *            executed locally or on a Selenium Grid.
     * @return The initialized RemoteWebDriver instance.
     * @throws MalformedURLException If the URL for the Selenium Grid is malformed.
     */
    public static RemoteWebDriver initDriver(boolean isLocalExecution) throws MalformedURLException {
        // Get required property values
        final String BROWSER = TestProperties.getProperty("browser", true, DefaultValues.BROWSER).orElse(DefaultValues.BROWSER);
        final String BROWSER_VERSION = TestProperties.getProperty("browserVersion", true, DefaultValues.BROWSER_VERSION).orElse(DefaultValues.BROWSER_VERSION);
        final boolean USE_PROXY = TestProperties.getProperty("useProxy", true, DefaultValues.USE_PROXY).orElse(DefaultValues.USE_PROXY);
        final boolean USE_INCOGNITO_MODE = TestProperties.getProperty("useIncognitoMode", true, DefaultValues.USE_INCOGNITO_MODE)
                .orElse(DefaultValues.USE_INCOGNITO_MODE);
        final String SELENIUM_GRID_URL = TestProperties.getProperty("seleniumGridUrl", true, DefaultValues.SELENIUM_GRID_URL)
                .orElse(DefaultValues.SELENIUM_GRID_URL);
        final long DEFAULT_SCRIPT_AND_PAGE_LOAD_TIME = TestProperties.getProperty("defaultScriptAndPageLoadTime", true,
                DefaultValues.DEFAULT_SCRIPT_AND_PAGE_LOAD_TIME)
                .orElse(DefaultValues.DEFAULT_SCRIPT_AND_PAGE_LOAD_TIME);
        final String LOG_LEVEL = TestProperties.getProperty("logLevel", true, DefaultValues.LOG_LEVEL)
                .orElse(DefaultValues.LOG_LEVEL);

        if (isLocalExecution) {
            ScenarioLogManager.getLogger().info("Test will be executed locally!");
            IS_LOCAL_EXECUTION_MAP.put(Thread.currentThread().getId(), true);
        } else {
            ScenarioLogManager.getLogger().info("Test will be executed on Selenium Grid!");
            IS_LOCAL_EXECUTION_MAP.put(Thread.currentThread().getId(), false);
        }

        Proxy proxy = getProxy();

        RemoteWebDriver driver;
        switch (BROWSER) {
            case "firefox":
                FirefoxOptions firefoxOptions = new FirefoxOptions();
                final Path FIREFOX_PROFILE_DIRECTORY_PATH = Paths.get(
                        TestProperties.getProperty("firefoxProfileDirectory", true, DefaultValues.FIREFOX_PROFILES_DIRECTORY)
                                .orElse(DefaultValues.FIREFOX_PROFILES_DIRECTORY));
                final String FIREFOX_PROFILE_NAME = (String) TestProperties.getProperty("firefoxProfileName").orElse("");
                FirefoxProfile firefoxProfile;
                if (Files.exists(FIREFOX_PROFILE_DIRECTORY_PATH)) {
                    if (FIREFOX_PROFILE_NAME.isEmpty()) {
                        ScenarioLogManager.getLogger().warn("Firefox profile name property [firefoxProfileName] was not set! Loading default profile.");
                        firefoxProfile = new FirefoxProfile();
                    } else {
                        final Path FIREFOX_PROFILE_FULL_PATH = FIREFOX_PROFILE_DIRECTORY_PATH.resolve(FIREFOX_PROFILE_NAME);
                        if (Files.exists(FIREFOX_PROFILE_FULL_PATH)) {
                            firefoxProfile = new FirefoxProfile(FIREFOX_PROFILE_FULL_PATH.toFile());
                            firefoxProfile.setPreference("security.default_personal_cert", "Select Automatically");

                            firefoxProfile.setPreference("browser.download.folderList", 1);
                            firefoxProfile.setPreference("browser.download.manager.showWhenStarting", false);
                            firefoxProfile.setPreference("browser.download.manager.focusWhenStarting", false);
                            firefoxProfile.setPreference("browser.download.useDownloadDir", true);
                            firefoxProfile.setPreference("browser.helperApps.alwaysAsk.force", false);
                            firefoxProfile.setPreference("browser.download.manager.alertOnEXEOpen", false);
                            firefoxProfile.setPreference("browser.download.manager.closeWhenDone", true);
                            firefoxProfile.setPreference("browser.download.manager.showAlertOnComplete", false);
                            firefoxProfile.setPreference("browser.download.manager.useWindow", false);

                            // You will need to find the content-type of your app and set it here.
                            firefoxProfile.setPreference("browser.helperApps.neverAsk.saveToDisk", "application/octet-stream");
                        } else {
                            ScenarioLogManager.getLogger()
                                    .warn("Directory path [{}] for Firefox profile [{}] does not exist! Loading default profile.", FIREFOX_PROFILE_FULL_PATH,
                                            FIREFOX_PROFILE_NAME);
                            firefoxProfile = new FirefoxProfile();
                        }
                    }
                } else {
                    ScenarioLogManager.getLogger()
                            .debug("Directory path [{}] for Firefox profile does not exist! Loading default profile.", FIREFOX_PROFILE_DIRECTORY_PATH);
                    firefoxProfile = new FirefoxProfile();
                }

                final Path EXTENSION_DIRECTORY = Paths.get(
                        TestProperties.getProperty("firefoxExtensionDirectory", true, DefaultValues.FIREFOX_EXTENSION_DIRECTORY)
                                .orElse(DefaultValues.FIREFOX_EXTENSION_DIRECTORY));
                if (Files.exists(EXTENSION_DIRECTORY)) {
                    try {
                        Files.walkFileTree(EXTENSION_DIRECTORY, new SimpleFileVisitor<>() {
                            @Override
                            public @NotNull
                            FileVisitResult visitFile(Path file, @NotNull BasicFileAttributes attrs) {
                                if (file.toString().endsWith(".xpi")) {
                                    firefoxProfile.addExtension(new File(file.toAbsolutePath().normalize().toString()));
                                }
                                return FileVisitResult.CONTINUE;
                            }

                            @Override
                            public @NotNull
                            FileVisitResult visitFileFailed(Path file, @NotNull IOException exc) {
                                ScenarioLogManager.getLogger().error("Failed to access Firefox extension file: {}", file.toString());
                                return FileVisitResult.CONTINUE;
                            }
                        });
                    } catch (IOException e) {
                        ScenarioLogManager.getLogger().error("An IO error occurred while looking for Firefox extension (.xpi) files!", e);
                    }
                } else {
                    ScenarioLogManager.getLogger().debug("Directory path [{}] for Firefox extension (.xpi) files does not exist!", EXTENSION_DIRECTORY);
                }

                firefoxOptions.setEnableDownloads(true);
                if (USE_PROXY) {
                    firefoxOptions.setProxy(proxy);
                    ScenarioLogManager.getLogger().info("Proxy set");
                } else {
                    ScenarioLogManager.getLogger().info("Proxy ignored!");
                }

                if (USE_INCOGNITO_MODE) {
                    firefoxOptions.addArguments("-private-window");
                }
                firefoxOptions.setAcceptInsecureCerts(true);
                firefoxOptions.addPreference("dom.webnotifications.enabled", false);

                switch (LOG_LEVEL.toUpperCase()) {
                    case "ERROR":
                        firefoxOptions.setLogLevel(FirefoxDriverLogLevel.ERROR);
                        break;
                    case "WARN":
                        firefoxOptions.setLogLevel(FirefoxDriverLogLevel.WARN);
                        break;
                    case "DEBUG":
                        firefoxOptions.setLogLevel(FirefoxDriverLogLevel.DEBUG);
                        break;
                    case "INFO":
                    default:
                        firefoxOptions.setLogLevel(FirefoxDriverLogLevel.INFO);
                }

                firefoxOptions.setProfile(firefoxProfile);
                if (isLocalExecution) {
                    driver = new FirefoxDriver(firefoxOptions);
                } else {
                    firefoxOptions.setCapability(CapabilityType.BROWSER_NAME, BROWSER);
                    if (!BROWSER_VERSION.isEmpty()) {
                        firefoxOptions.setCapability(CapabilityType.BROWSER_VERSION, BROWSER_VERSION);
                    }
                    firefoxOptions.setCapability(CapabilityType.PLATFORM_NAME, "LINUX");
                    firefoxOptions.addArguments("--headless");
                    driver = new RemoteWebDriver(new URL(SELENIUM_GRID_URL), firefoxOptions);
                }
                break;
            case "edge":
                EdgeOptions edgeOptions = new EdgeOptions();
                edgeOptions.setEnableDownloads(true);
                if (USE_PROXY) {
                    edgeOptions.setProxy(proxy);
                    ScenarioLogManager.getLogger().info("Proxy set");
                } else {
                    ScenarioLogManager.getLogger().info("Proxy ignored!");
                }

                if (USE_INCOGNITO_MODE) {
                    edgeOptions.addArguments("--InPrivate");
                }
                edgeOptions.setPlatformName("WINDOWS");
                edgeOptions.setAcceptInsecureCerts(true);

                LoggingPreferences edgeLogPrefs = new LoggingPreferences();
                Level edgeLogLevel = switch (LOG_LEVEL.toUpperCase()) {
                    case "ERROR" -> Level.SEVERE;
                    case "WARN" -> Level.WARNING;
                    case "DEBUG" -> Level.FINE;
                    default -> Level.INFO;
                };
                edgeLogPrefs.enable(LogType.BROWSER, edgeLogLevel);
                edgeOptions.setCapability("ms:loggingPrefs", edgeLogPrefs);

                if (isLocalExecution) {
                    driver = new EdgeDriver(edgeOptions);
                } else {
                    edgeOptions.setCapability(CapabilityType.BROWSER_NAME, BROWSER);
                    if (!BROWSER_VERSION.isEmpty()) {
                        edgeOptions.setCapability(CapabilityType.BROWSER_VERSION, BROWSER_VERSION);
                    }
                    edgeOptions.setCapability(CapabilityType.PLATFORM_NAME, "WINDOWS");
                    driver = new RemoteWebDriver(new URL(SELENIUM_GRID_URL), edgeOptions);
                }
                break;
            case "chrome":
                ChromeOptions chromeOptions = new ChromeOptions();
                chromeOptions.setEnableDownloads(true);
                // Create a map to store preferences
                Map<String, Object> preferences = new HashMap<>();
                // add key and value to map as follows to switch off browser notification
                // Pass argument 1 to allow and 2 to block
                preferences.put("profile.default_content_setting_values.notifications", 2);
                chromeOptions.setExperimentalOption("prefs", preferences);

                if (USE_PROXY) {
                    chromeOptions.setProxy(proxy);
                    ScenarioLogManager.getLogger().info("Proxy set");
                } else {
                    ScenarioLogManager.getLogger().info("Proxy ignored!");
                }

                if (USE_INCOGNITO_MODE) {
                    chromeOptions.addArguments("--incognito");
                }
                chromeOptions.setPageLoadStrategy(PageLoadStrategy.EAGER);
                chromeOptions.addArguments("--lang=de");
                chromeOptions.addArguments("--disable-gpu");
                chromeOptions.addArguments("--start-maximized");
                chromeOptions.addArguments("--disable-popup-blocking");
                chromeOptions.addArguments("--disable-web-security");
                chromeOptions.addArguments("--enable-automation");
                chromeOptions.addArguments("--no-sandbox");
                chromeOptions.addArguments("--disable-infobars");
                chromeOptions.addArguments("--disable-dev-shm-usage");
                chromeOptions.addArguments("--disable-browser-side-navigation");
                chromeOptions.addArguments("--disable-site-isolation-trials");
                if (!BROWSER_VERSION.isEmpty() && isVersionLessOrEqual(BROWSER_VERSION, "94")) {
                    chromeOptions.addArguments("--disable-features=SameSiteByDefaultCookies,CookiesWithoutSameSiteMustBeSecure");
                }
                if (!BROWSER_VERSION.isEmpty() && isVersionLessOrEqual(BROWSER_VERSION, "85")) {
                    chromeOptions.setExperimentalOption("useAutomationExtension", false);
                }
                chromeOptions.setAcceptInsecureCerts(true);

                LoggingPreferences chromeLogPrefs = new LoggingPreferences();
                Level chromeLogLevel = switch (LOG_LEVEL.toUpperCase()) {
                    case "ERROR" -> Level.SEVERE;
                    case "WARN" -> Level.WARNING;
                    case "DEBUG" -> Level.FINE;
                    default -> Level.INFO;
                };
                chromeLogPrefs.enable(LogType.BROWSER, chromeLogLevel);
                chromeOptions.setCapability("goog:loggingPrefs", chromeLogPrefs);

                if (isLocalExecution) {
                    driver = new ChromeDriver(chromeOptions);
                } else {
                    chromeOptions.setCapability(CapabilityType.BROWSER_NAME, BROWSER);
                    if (!BROWSER_VERSION.isEmpty()) {
                        chromeOptions.setCapability(CapabilityType.BROWSER_VERSION, BROWSER_VERSION);
                    }
                    chromeOptions.setCapability(CapabilityType.PLATFORM_NAME, "LINUX");
                    driver = new RemoteWebDriver(new URL(SELENIUM_GRID_URL), chromeOptions);
                }
                break;
            default:
                throw new IllegalArgumentException(
                        "Browser \"" + BROWSER + "\" is not supported by test automation framework! Supported browsers are: firefox, edge and chrome");
        }

        if (!isLocalExecution) {
            driver.setFileDetector(new LocalFileDetector());
        }

        driver.manage().timeouts().scriptTimeout(Duration.ofMillis(DEFAULT_SCRIPT_AND_PAGE_LOAD_TIME));
        driver.manage().timeouts().pageLoadTimeout(Duration.ofMillis(DEFAULT_SCRIPT_AND_PAGE_LOAD_TIME));
        driver.manage().timeouts().implicitlyWait(Duration.ofMillis(
                TestProperties.getProperty("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME)
                        .orElse(DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME)));

        REMOTE_WEB_DRIVER_MAP.put(Thread.currentThread().getId(), driver);

        return driver;
    }

    /***
     * Retrieves the proxy settings based on the configuration properties.
     *
     * @return The configured Proxy object with the appropriate settings.
     */
    @NotNull
    private static Proxy getProxy() {
        Proxy proxy = new Proxy();
        String proxyAddress = TestProperties.getProperty("proxyAddress", true, DefaultValues.PROXY_ADDRESS).orElse(DefaultValues.PROXY_ADDRESS);
        int proxyPort = TestProperties.getProperty("proxyPort", true, DefaultValues.PROXY_PORT).orElse(DefaultValues.PROXY_PORT);
        String noProxy = TestProperties.getProperty("noProxy", true, DefaultValues.NO_PROXY).orElse(DefaultValues.NO_PROXY);
        if (!noProxy.isEmpty()) {
            proxy.setNoProxy(noProxy);
        }
        if (TestProperties.getProperty("usePAC", true, DefaultValues.USE_PAC).orElse(DefaultValues.USE_PAC)) {
            proxy.setProxyType(Proxy.ProxyType.PAC);
            proxy.setProxyAutoconfigUrl(TestProperties.getProperty("pACUrl", true, DefaultValues.PAC_URL).orElse(DefaultValues.PAC_URL));
        } else {
            proxy.setHttpProxy(proxyAddress + ":" + proxyPort);
            proxy.setSslProxy(proxyAddress + ":" + proxyPort);
        }
        return proxy;
    }

    /**
     * Compares two version strings in the format "major.minor.patch". If the target version has fewer
     * parts than the current version, only the common parts are
     * compared. Extra parts in the current version are ignored during comparison.
     * <p>
     * Example: - "94.5.146" compared to "94" only checks the major version (94 vs. 94) and returns
     * true. - "95.0.0" compared to "94" returns false since 95 >
     * 94.
     *
     * @param currentVersion The current version string (e.g., "94.5.146").
     * @param targetVersion The target version string (e.g., "94", "94.0", or "94.0.0").
     * @return true if the current version is less than or equal to the target version, false otherwise.
     * @throws NumberFormatException if the version strings contain non-numeric values.
     */
    public static boolean isVersionLessOrEqual(String currentVersion, String targetVersion) {
        String[] currentParts = currentVersion.split("\\.");
        String[] targetParts = targetVersion.split("\\.");

        int length = Math.min(currentParts.length, targetParts.length); // Ensure we compare at least major.minor.patch

        for (int i = 0; i < length; i++) {
            int current = Integer.parseInt(currentParts[i]);
            int target = Integer.parseInt(targetParts[i]);

            if (current < target) return true;
            if (current > target) return false;
        }

        return true; // Versions are equal
    }

    /***
     * Checks if the current execution is local.
     *
     * @return true if the tests are being executed locally; false if
     *         executed on a Selenium Grid.
     */
    public static boolean isLocalExecution() {
        return IS_LOCAL_EXECUTION_MAP.get(Thread.currentThread().getId());
    }

    /***
     * Closes the WebDriver instance associated with the current thread
     * and removes it from the tracking map. If the WebDriver instance is
     * running on Selenium Grid, it attempts to quit the session.
     */
    public static void closeDriver() {
        RemoteWebDriver driver = REMOTE_WEB_DRIVER_MAP.get(Thread.currentThread().getId());
        if (driver != null) {
            if (!isLocalExecution()) {
                try {
                    driver.quit();
                    driver = null;
                } catch (NoSuchMethodError | NoSuchSessionException | SessionNotCreatedException e) {
                    ScenarioLogManager.getLogger().error("An error occurred while closing the driver: ", e);
                }
            }
        }
        REMOTE_WEB_DRIVER_MAP.remove(Thread.currentThread().getId());
    }
}
