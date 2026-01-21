package ataf.rest.steps;

import ataf.core.logging.ScenarioLogManager;
import io.cucumber.java.After;
import io.cucumber.java.AfterAll;
import io.cucumber.java.Before;
import io.cucumber.java.BeforeAll;
import io.cucumber.java.Scenario;

/**
 * This class contains hooks for setting up and tearing down test scenarios in Cucumber tests. Hooks
 * are methods that are executed before or after test
 * scenarios or the entire test suite.
 *
 * @author Philipp Lehmann (ex.lehmann08)
 */
public class Hook {

    /**
     * Executes once before all test scenarios in the test suite. Logs the start of the test framework
     * setup.
     */
    @BeforeAll
    public static void setupFramework() {
        ScenarioLogManager.getLogger().info("Hook: BeforeAll (Framework-API)");
    }

    /**
     * Executes before each test scenario. Logs the start of the test case setup and checks for
     * scenarios tagged with "@rest".
     *
     * @param scenario The Cucumber scenario being executed
     */
    @Before("@rest")
    public void setupTestCase(Scenario scenario) {
        ScenarioLogManager.getLogger().info("Hook: Before (Framework-API)");
        ScenarioLogManager.getLogger().info("Starting REST scenario setup");
        scenario.getSourceTagNames().forEach(s -> {
            if (s.equals("@rest")) {
                ScenarioLogManager.getLogger().info("REST scenario detected");
                ScenarioLogManager.getLogger().info("No specific Before-hook required (framework provided)");
            }
        });
    }

    /**
     * Executes after each test scenario. Logs the completion of the scenario and checks for scenarios
     * tagged with "@rest".
     *
     * @param scenario The Cucumber scenario that has completed
     */
    @After("@rest")
    public void sessionReset(Scenario scenario) {
        ScenarioLogManager.getLogger().info("Hook: After (Framework-API)");
        scenario.getSourceTagNames().forEach(s -> {
            if (s.equals("@rest")) {
                ScenarioLogManager.getLogger().info("No specific After-hook required (framework provided)");
            }
        });
    }

    /**
     * Executes once after all test scenarios in the test suite. Logs the completion of the test
     * framework teardown.
     */
    @AfterAll
    public static void beforeOrAfterAll() {
        ScenarioLogManager.getLogger().info("Hook: AfterAll (Framework-API)");
    }
}
