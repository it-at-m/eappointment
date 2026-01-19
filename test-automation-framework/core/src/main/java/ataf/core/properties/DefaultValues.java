package ataf.core.properties;

import ataf.core.clients.HttpClient;

/**
 * Class DefaultValues contains default configuration values for the Selenium WebDriver, logging,
 * and Jira integration used in the automation testing
 * framework.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class DefaultValues {
    // WebDriver configuration
    /**
     * The URL for the Selenium Grid.
     */
    public static final String SELENIUM_GRID_URL = "https://selenium.muenchen.de/wd/hub";

    /**
     * Flag to indicate whether to use incognito mode in the browser.
     */
    public static final boolean USE_INCOGNITO_MODE = true;

    /**
     * The browser to be used for the tests.
     */
    public static final String BROWSER = "firefox";

    /**
     * The version of the browser to be used.
     */
    public static final String BROWSER_VERSION = "128.4.0";

    /**
     * The directory path for firefox browser profiles.
     */
    public static final String FIREFOX_PROFILES_DIRECTORY = "src/test/resources/profiles/firefox/";

    /**
     * The directory path for Firefox extensions.
     */
    public static final String FIREFOX_EXTENSION_DIRECTORY = "src/test/resources/extensions/firefox/";

    /**
     * Flag to indicate whether to use a proxy server.
     */
    public static final boolean USE_PROXY = true;

    /**
     * Flag to indicate whether to use a PAC file for proxy configuration.
     */
    public static final boolean USE_PAC = false;

    /**
     * The URL for the PAC file.
     */
    public static final String PAC_URL = HttpClient.PROXY_PAC_URL;

    /**
     * The address of the proxy server.
     */
    public static final String PROXY_ADDRESS = "10.158.0.77";

    /**
     * The port number for the proxy server.
     */
    public static final int PROXY_PORT = 80;

    /**
     * A comma-separated list of hosts that should be accessed directly without using the proxy.
     */
    public static final String NO_PROXY = "";

    /**
     * The default time (in milliseconds) for scripts and page loads.
     */
    public static final long DEFAULT_SCRIPT_AND_PAGE_LOAD_TIME = 30000L;

    /**
     * The default implicit wait time (in milliseconds) for WebDriver.
     */
    public static final long DEFAULT_IMPLICIT_WAIT_TIME = 250L;

    /**
     * The default explicit wait time (in seconds) for WebDriver.
     */
    public static final int DEFAULT_EXPLICIT_WAIT_TIME = 60;

    /**
     * The default width of the browser window (in pixels).
     */
    public static final int SCREEN_WIDTH = 1920;

    /**
     * The default height of the browser window (in pixels).
     */
    public static final int SCREEN_HEIGHT = 1080;

    /**
     * Flag to indicate whether to maximize web-driver windows or not.
     */
    public static final boolean MAXIMIZE_WINDOWS = false;

    // Logger configuration
    /**
     * The logging level for the application.
     */
    public static final String LOG_LEVEL = "INFO";

    // Jira configuration
    /**
     * The summary for the automatically generated test execution in Jira.
     */
    public static final String TEST_EXECUTION_SUMMARY = "Automatisch generierte Testausführung";

    /**
     * The issue type ID for the test execution in Jira.
     */
    public static final String TEST_EXECUTION_ISSUE_TYPE_ID = "10202";

    /**
     * The label for automated test executions in Jira.
     */
    public static final String TEST_EXECUTION_LABELS_AUTOMATION_LABEL = "automatisiert";

    /**
     * The label indicating that a test execution is in progress in Jira.
     */
    public static final String TEST_EXECUTION_LABELS_IN_PROGRESS = "inAusführung";
}
