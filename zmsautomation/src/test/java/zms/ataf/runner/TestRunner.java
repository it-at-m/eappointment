package zms.ataf.runner;

import ataf.core.runner.BasicTestNGRunner;
import zms.ataf.data.TestData;

/**
 * TestNG runner for running Cucumber tests with ATAF.
 * Feature files are discovered from the features directory on the classpath via cucumber.properties.
 * This follows the same pattern as zms-test-automation.
 */
public class TestRunner extends BasicTestNGRunner {
    static {
        TestData.init();
    }
}
