package ataf.web.steps;

import ataf.core.assertions.CustomAssertions;
import ataf.core.context.TestExecutionContext;
import ataf.core.data.Environment;
import ataf.core.data.System;
import ataf.core.data.TestUser;
import ataf.core.helpers.TestDataHelper;
import ataf.core.helpers.TestPropertiesHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.core.properties.DefaultValues;
import ataf.core.properties.TestProperties;
import ataf.core.utils.RunnerUtils;
import ataf.web.controls.FrameControls;
import ataf.web.controls.WindowControls;
import ataf.web.model.WindowType;
import ataf.web.pages.BasePage;
import ataf.web.pages.SingleSignOnPage;
import ataf.web.pages.chrome.ClearCachePage;
import ataf.web.utils.DriverUtil;
import io.cucumber.datatable.DataTable;
import io.cucumber.java.de.Und;
import io.cucumber.java.de.Wenn;

import java.util.Map;
import java.util.Set;
import java.util.concurrent.TimeUnit;

/**
 * BaseSteps provides common step definitions for browser interactions in a test automation
 * framework. It utilizes the BasePage class to perform various
 * operations such as refreshing pages, waiting, saving test data, navigating to URLs, switching
 * tabs, and clearing browser cache.
 * <p>
 * This class is designed to be used with a behavior-driven development (BDD) framework, allowing
 * for easy definition of step methods that correspond to user
 * actions.
 * <p>
 * Note: The methods in this class may depend on external classes such as BasePage, DriverUtil,
 * TestDataHelper, and others for their functionality.
 */
public class BaseSteps {
    private final BasePage BASE_PAGE;

    /***
     * Constructs a BaseSteps instance, initializing the BasePage with
     * the current WebDriver instance retrieved from DriverUtil.
     */
    public BaseSteps() {
        BASE_PAGE = new BasePage(DriverUtil.getDriver());
    }

    /***
     * Refreshes the current browser window.
     * This step is useful when the user wants to reload the page to ensure
     * they have the most recent content.
     */
    @Wenn("Sie die Seite neu laden.")
    public void wenn_sie_die_seite_neu_laden() {
        BASE_PAGE.refreshBrowser();
    }

    /***
     * Pauses the execution for a specified number of milliseconds.
     *
     * @param numberOfMillis The number of milliseconds to wait before continuing.
     *            This value is passed as a string and will be parsed into a long.
     */
    @Wenn("Sie für {string} Millisekunden warten.")
    public void wenn_sie_fuer_string_millisekunden_warten(String numberOfMillis) {
        try {
            long timeToWait = Long.parseLong(TestDataHelper.transformTestData(numberOfMillis));
            if (timeToWait > 0L) {
                ScenarioLogManager.getLogger().info("Waiting for {} milliseconds...", timeToWait);
                try {
                    Thread.sleep(timeToWait);
                } catch (InterruptedException e) {
                    ScenarioLogManager.getLogger().error(e.getMessage(), e);
                }
            } else {
                ScenarioLogManager.getLogger().error("Number of milliseconds must be greater than 0!");
            }
        } catch (NumberFormatException e) {
            ScenarioLogManager.getLogger().error("Given parameter is not a valid number to wait for!", e);
        }
    }

    /***
     * Saves a generic test data value associated with a specified parameter name.
     *
     * @param parameterValue The actual value to be saved. This value can be transformed
     *            for dynamic data handling.
     * @param parameterName The name under which the value will be stored and accessed.
     *            If an empty name is provided, an error will be logged.
     */
    @Wenn("Sie den Wert {string} für Parameter mit Namen {string} notieren.")
    public void wenn_sie_den_wert_string_fuer_parameter_mit_namen_string_notieren(String parameterValue, String parameterName) {
        parameterName = TestDataHelper.transformTestData(parameterName);
        if (parameterName.isEmpty()) {
            ScenarioLogManager.getLogger().error("Cannot save test data with an empty parameter name!");
        } else {
            parameterValue = TestDataHelper.transformTestData(parameterValue);
            if (TestDataHelper.getTestData(parameterName) != null) {
                ScenarioLogManager.getLogger().warn("Parameter name \"{}\" is already in use! Its value \"{}\" will be overwritten!", parameterName,
                        TestDataHelper.getTestData(parameterName));
            }
            ScenarioLogManager.getLogger().info("Storing test data {} as {}", parameterValue, parameterName);
            TestDataHelper.setTestData(parameterName, parameterValue);
        }
    }

    /***
     * Navigates to a specified web page by its URL and verifies the page title.
     *
     * @param url The URL of the web page to navigate to.
     * @param pageTitle The expected title of the page after navigation, used for verification.
     */
    @Wenn("Sie zur Webseite mit Webadresse {string} und Titel {string} navigieren.")
    public void wenn_sie_zur_webseite_mit_webadresse_string_und_titel_string_navigieren(String url, String pageTitle) {
        url = TestDataHelper.transformTestData(url);
        try {
            if (BASE_PAGE.navigateToPageByUrl(60, url, TestDataHelper.transformTestData(pageTitle))) {
                WindowControls.updateWindowList(DriverUtil.getDriver(), new WindowType(pageTitle, new System(pageTitle, url)));
                FrameControls.setCurrentFrame(FrameControls.DEFAULT_CONTENT);
            } else {
                CustomAssertions.fail("Could not navigate to address: " + url);
            }
        } catch (Exception e) {
            CustomAssertions.fail("Could not navigate to address [" + url + "],", e);
        }
    }

    /***
     * Navigates to a specified system per SSO.
     *
     * @param user The SSO account.
     * @param system The system where the user logs on.
     */
    @Wenn("Sie per SingleSignOn mit dem User {string} auf das System {string} navigieren.")
    public void wenn_sie_per_sso_auf_das_system_navigieren(String user, String system) {
        if (TestPropertiesHelper.getPropertyAsBoolean("useIncognitoMode", true, DefaultValues.USE_INCOGNITO_MODE)) {
            Environment environment;
            if (RunnerUtils.isJiraBasedTestExecution()) {
                environment = Environment.contains(TestExecutionContext.get().ENVIRONMENT);
            } else {
                environment = Environment.NONE;
            }
            CustomAssertions.assertNotNull(environment, "Environment could not be retrieved! Check test environment in Jira");
            String systemUrl = environment.getSystemUrl(TestDataHelper.transformTestData(system));
            CustomAssertions.assertNotNull(systemUrl, "System [" + system + "] not specified!");
            TestUser testUser = TestUser.getTestUser(TestDataHelper.transformTestData(user));
            CustomAssertions.assertNotNull(testUser, "User [" + user + "] not found in test data!");

            new SingleSignOnPage(DriverUtil.getDriver()).executeSingleSignOnLogin(testUser.getUserName(), testUser.getPassword());
        } else {
            ScenarioLogManager.getLogger().warn("Skipping SSO login step. Manually login only in incognito mode!");
        }
    }

    /***
     * Switches to an open browser tab identified by its title.
     *
     * @param tabTitle The title of the tab to switch to.
     *            If no tab with the given title is found, the method will fail.
     */
    @Wenn("Sie zum geöffneten Browsertab mit Titel {string} wechseln.")
    public void wenn_sie_zum_geoeffneten_browsertab_mit_titel_string_wechseln(String tabTitle) {
        tabTitle = TestDataHelper.transformTestData(tabTitle);
        if (WindowControls.isWindowWithTitleInList(tabTitle)) {
            WindowControls.switchToWindow(DriverUtil.getDriver(), tabTitle);
        } else {
            CustomAssertions.fail("No browser tab or window with title \"" + tabTitle + "\" is open or registered!");
        }
    }

    /***
     * Deletes the browser's cache.
     * This is particularly useful in testing scenarios where
     * cache might interfere with the tests.
     * <p>
     * Note: Currently, only the Chrome browser is supported for cache deletion.
     *
     * @throws IllegalArgumentException If the browser is not supported for cache deletion.
     */
    @Wenn("Sie den Zwischenspeicher des Browsers löschen.")
    public void wenn_sie_den_zwischenspeicher_des_browsers_loeschen() {
        String browser = TestProperties.getProperty("browser", true, DefaultValues.BROWSER)
                .orElse(DefaultValues.BROWSER_VERSION);
        if ("chrome".equals(browser)) {
            if (!TestProperties.getProperty("useIncognitoMode", true, DefaultValues.USE_INCOGNITO_MODE)
                    .orElse(DefaultValues.USE_INCOGNITO_MODE)) {
                ClearCachePage clearCachePage = new ClearCachePage(DriverUtil.getDriver());
                clearCachePage.navigateToClearCachePage();
                clearCachePage.clickOnClearBrowserCacheButton();
            }
        } else {
            throw new IllegalArgumentException("For browser \"" + browser + "\", the browser cache deletion is not implemented yet!");
        }
    }

    /***
     * Waits for a specified number of minutes until changes are applied.
     *
     * @param minutes The number of minutes to wait for changes to take effect.
     */
    @Und("Sie {string} Minuten warten bis die Änderungen übernommen wurden.")
    public void sie_string_minuten_warten_bis_die_aenderungen_uebernommen_wurden(String minutes) {
        BASE_PAGE.waitFor(Integer.parseInt(minutes), TimeUnit.MINUTES, true);
    }

    /**
     * Saves a set of test data rows from a Cucumber DataTable into either the scenario or suite scope.
     * <p>
     * Each row must at least contain:
     * <ul>
     * <li><strong>Parametername</strong> - The key under which the value will be stored.</li>
     * <li><strong>Parameterwert</strong> - The actual value to be saved (will be transformed if
     * needed).</li>
     * <li><strong>Anwendungsbereich</strong> (optional) - Determines whether the test data is stored in
     * the scenario scope
     * or the suite (test execution) scope. Possible values: "Szenario" or "Testausführung". Default is
     * "Szenario".</li>
     * </ul>
     * Any additional columns will be ignored, and a warning will be logged.
     *
     * @param dataTable Cucumber DataTable containing the test data. Each row should have:
     *            Parametername, Parameterwert, and optionally scope.
     */
    @Wenn("Sie folgende Testdaten notieren:")
    public void wenn_sie_folgende_testdaten_notieren(DataTable dataTable) {
        Set<String> validColumns = Set.of("Parametername", "Parameterwert", "Anwendungsbereich");

        // Iterate through each row of the DataTable
        for (Map<String, String> row : dataTable.asMaps(String.class, String.class)) {

            // Check for unexpected columns and log them
            for (Map.Entry<String, String> entry : row.entrySet()) {
                if (!validColumns.contains(entry.getKey())) {
                    ScenarioLogManager.getLogger().warn(
                            "Ignoring unexpected column: '{}' with value: '{}'",
                            entry.getKey(), entry.getValue());
                }
            }

            String parameterName = TestDataHelper.transformTestData(row.get("Parametername"));
            String parameterValue = TestDataHelper.transformTestData(row.get("Parameterwert"));

            // If scope is missing or empty, default to 'Szenario'
            String scope = row.containsKey("Anwendungsbereich") && row.get("Anwendungsbereich") != null
                    ? row.get("Anwendungsbereich").trim().toLowerCase()
                    : "Szenario";

            // Validate parameterName
            if (parameterName.isEmpty()) {
                ScenarioLogManager.getLogger().error(
                        "Cannot save test data because no parameter name was provided!");
                continue;
            }

            // Retrieve current value based on scope
            String currentValue = "Testausführung".equalsIgnoreCase(scope)
                    ? TestDataHelper.getSuiteTestData(parameterName)
                    : TestDataHelper.getTestData(parameterName);

            // Warn if value already exists
            if (currentValue != null) {
                ScenarioLogManager.getLogger().warn(
                        "Parameter name \"{}\" already in use (scope: {}), value \"{}\" will be overwritten.",
                        parameterName, scope, currentValue);
            }

            // Store the new value in the appropriate scope
            if ("Testausführung".equalsIgnoreCase(scope)) {
                ScenarioLogManager.getLogger().info(
                        "Storing test data {} as {} in SUITE scope", parameterValue, parameterName);
                TestDataHelper.setSuiteTestData(parameterName, parameterValue);
            } else {
                ScenarioLogManager.getLogger().info(
                        "Storing test data {} as {} in SCENARIO scope", parameterValue, parameterName);
                TestDataHelper.setTestData(parameterName, parameterValue);
            }
        }
    }
}
