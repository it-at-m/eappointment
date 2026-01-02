package ataf.web.steps;

import ataf.core.context.ScenarioContext;
import ataf.core.context.TestContext;
import ataf.core.context.TestExecutionContext;
import ataf.core.helpers.TestDataHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.core.properties.DefaultValues;
import ataf.core.properties.TestProperties;
import ataf.core.properties.TestProperty;
import ataf.core.utils.CucumberUtils;
import ataf.core.utils.DateUtils;
import ataf.core.utils.RunnerUtils;
import ataf.core.xray.TestStatus;
import ataf.web.controls.FrameControls;
import ataf.web.controls.WindowControls;
import ataf.web.utils.DriverUtil;
import io.cucumber.java.After;
import io.cucumber.java.Before;
import io.cucumber.java.BeforeStep;
import io.cucumber.java.Scenario;
import org.openqa.selenium.Dimension;
import org.openqa.selenium.OutputType;
import org.openqa.selenium.remote.RemoteWebDriver;

import java.io.IOException;
import java.net.MalformedURLException;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.util.function.Predicate;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * Provides setup and teardown functionality for test scenarios in a behavior-driven development
 * (BDD) framework.
 * <p>
 * This class integrates with the Cucumber framework to handle test lifecycle events such as
 * initialization, capturing screenshots on failures, attaching test
 * data, managing WebDriver instances, and handling test statuses.
 * <p>
 * It leverages various utility classes for managing the scenario context, test execution context,
 * and test properties, and supports Jira-based test management
 * if applicable.
 * <p>
 * The class is designed to work in a multi-threaded environment with support for thread-safe
 * operations.
 *
 * <p>
 * Note: This class assumes that Cucumber and WebDriver are correctly configured in the test
 * environment.
 * </p>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class Hook {

    /**
     * Captures a screenshot from the provided WebDriver instance and saves it.
     * <p>
     * The screenshot is saved to the target directory if running locally and is also attached to the
     * current scenario in the test report. This method is
     * typically called during test failures to capture the state of the application under test.
     *
     * @param driver The RemoteWebDriver instance used to capture the screenshot.
     * @param fileName The name to be used for the screenshot file (without extension).
     */
    public static void makeScreenshot(RemoteWebDriver driver, String fileName) {
        try {
            final byte[] screenshot = driver.getScreenshotAs(OutputType.BYTES);
            if (DriverUtil.isLocalExecution()) {
                try {
                    Files.write(Paths.get("./target/" + fileName + ".png"), screenshot);
                } catch (IOException e) {
                    ScenarioLogManager.getLogger().error("Writing of local screenshot copy of file has \"{}.png\" failed,", fileName, e);
                }
            }
            ScenarioContext.get().attach(screenshot, "image/png", fileName + ".png");
            ScenarioContext.get().log("Made screenshot \"" + fileName + ".png" + "\"");
        } catch (Exception e) {
            ScenarioLogManager.getLogger().error("Making of screenshot has failed,", e);
        }
    }

    /**
     * Overwrites specific test properties based on the source tags provided by the given scenario.
     * <p>
     * This method inspects each tag in {@code scenario.getSourceTagNames()}. Depending on the tag:
     * <ul>
     * <li>@firefox, @chrome, or @edge sets the "browser" property.</li>
     * <li>@proxy sets the "useProxy" property to true.</li>
     * <li>@incognito, @inkognito, or @private sets the "useIncognitoMode" property to true.</li>
     * <li>Any tag matching a numerical version pattern (e.g., @91, @91.0.4472.77) sets the
     * "browserVersion" property.</li>
     * </ul>
     *
     * @param scenario the Cucumber scenario whose source tags will be used to determine test properties
     */
    private void overwritePropertiesFromSourceTags(Scenario scenario) {
        final Pattern BROWSER_VERSION_PATTERN = Pattern.compile("@(\\d+(?:\\.\\d+)*)");
        for (String sourceTag : scenario.getSourceTagNames()) {

            switch (sourceTag) {
                case "@firefox" -> new TestProperty<>("browser", "firefox");
                case "@chrome" -> new TestProperty<>("browser", "chrome");
                case "@edge" -> new TestProperty<>("browser", "edge");
                case "@proxy" -> new TestProperty<>("useProxy", true);
                case "@incognito", "@inkognito", "@private", "@privat" -> new TestProperty<>("useIncognitoMode", true);
            }

            Matcher browserVersionMatcher = BROWSER_VERSION_PATTERN.matcher(sourceTag);
            if (browserVersionMatcher.find()) {
                new TestProperty<>("browserVersion", browserVersionMatcher.group(1));
            }
        }
    }

    /**
     * Initializes the test scenario by setting up the WebDriver, configuring screen dimensions, and
     * initializing necessary data structures for the test
     * execution.
     * <p>
     * This method also sets up the scenario and test execution context, assigns statuses, and prepares
     * the environment for Jira-based test management if
     * applicable.
     *
     * @param scenario The Cucumber scenario being executed.
     * @throws MalformedURLException If the URL for the WebDriver is malformed.
     */
    @Before(value = "@web")
    public void initializeTest(Scenario scenario) throws MalformedURLException {
        overwritePropertiesFromSourceTags(scenario);
        boolean isLocalExecution = scenario.getSourceTagNames().stream().anyMatch(Predicate.isEqual("@executeLocally"));
        ScenarioContext.put(scenario);
        if (RunnerUtils.isJiraBasedTestExecution()) {
            TestExecutionContext.set(scenario);
            TestExecutionContext.get().assign();
            TestContext.set(scenario);
            TestContext.get().assign();
            TestContext.get().setStatus(TestStatus.EXECUTING);
        }
        ScenarioLogManager.getLogger().info(">>>>>>>Start of test scenario: {}>>>>>>>", scenario.getName());
        RemoteWebDriver driver = DriverUtil.initDriver(isLocalExecution);
        if (TestProperties.getProperty("maximizeWindows", true, DefaultValues.MAXIMIZE_WINDOWS).orElse(DefaultValues.MAXIMIZE_WINDOWS)) {
            driver.manage().window().maximize();
        } else {
            driver.manage().window().setSize(new Dimension(
                    TestProperties.getProperty("screenWidth", true, DefaultValues.SCREEN_WIDTH)
                            .orElse(DefaultValues.SCREEN_WIDTH),
                    TestProperties.getProperty("screenHeight", true, DefaultValues.SCREEN_HEIGHT)
                            .orElse(DefaultValues.SCREEN_HEIGHT)));
        }
        TestDataHelper.initializeTestDataMap();
        FrameControls.setCurrentFrame(FrameControls.DEFAULT_CONTENT);
        WindowControls.initializeWindowMap();
        WindowControls.initializeLastActiveWindowListIndexMap();
        WindowControls.initializeActiveWindowListIndexMap();
    }

    /**
     * Cleans up after the test scenario execution by taking a screenshot if the test failed, attaching
     * test data, resetting the WebDriver, and clearing
     * relevant contexts.
     * <p>
     * This method logs the end of the scenario and updates the test status if Jira-based test execution
     * is enabled.
     *
     * @param scenario The Cucumber scenario being executed, used to check the outcome.
     */
    @After(value = "@web")
    public void tearDownTest(Scenario scenario) {
        if (scenario.isFailed()) {
            makeScreenshot(DriverUtil.getDriver(), DateUtils.getFileTimestamp() + "_Screenshot_of_failure");
            ScenarioLogManager.getLogger().error("Test failed: {}", scenario.getName());
            if (RunnerUtils.isJiraBasedTestExecution()) {
                TestContext.get().setStatus(TestStatus.FAIL);
            }
        } else {
            ScenarioLogManager.getLogger().info("Test passed: {}", scenario.getName());
            if (RunnerUtils.isJiraBasedTestExecution()) {
                TestContext.get().setStatus(TestStatus.PASS);
            }
        }
        CucumberUtils.attachTestData();
        TestDataHelper.flushMapTestData();
        FrameControls.clearCurrentFrame();
        WindowControls.clearWindowList();
        DriverUtil.closeDriver();
        ScenarioLogManager.getLogger().info("<<<<<<<End of test scenario: {}<<<<<<<", scenario.getName());
        if (RunnerUtils.isJiraBasedTestExecution()) {
            CucumberUtils.attachLogFileAsEvidence();
        }
        ScenarioContext.clear();
        ScenarioLogManager.clear();
        TestContext.clear();
    }

    /**
     * Waits for a predefined amount of time before executing the next step in the scenario.
     * <p>
     * This method is typically used in UI tests to handle timing issues where certain elements may not
     * be immediately available or fully loaded.
     */
    @BeforeStep(value = "@web")
    public void waitBeforeStep() {
        try {
            long defaultImplicitWaitTime = TestProperties.getProperty("defaultImplicitWaitTime", true, DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME)
                    .orElse(DefaultValues.DEFAULT_IMPLICIT_WAIT_TIME);
            ScenarioLogManager.getLogger().info("Wait for {} ms...", defaultImplicitWaitTime);
            Thread.sleep(defaultImplicitWaitTime);
        } catch (InterruptedException e) {
            ScenarioLogManager.getLogger().error(e.getMessage(), e);
        }
    }
}
