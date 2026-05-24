package zms.ataf.runner;

import ataf.core.runner.BasicTestNGRunner;
import zms.ataf.data.TestData;

/**
 * TestNG runner for API-only Cucumber tests (no Selenium).
 * Glue and feature paths are set via Surefire system properties in the ataf-api profile.
 */
public class ApiTestRunner extends BasicTestNGRunner {
    static {
        TestData.init();
    }
}
