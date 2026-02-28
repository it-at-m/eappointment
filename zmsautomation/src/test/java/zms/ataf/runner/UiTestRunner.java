package zms.ataf.runner;

import ataf.core.runner.BasicTestNGRunner;
import zms.ataf.data.TestData;

/**
 * TestNG runner for UI-only Cucumber tests (Selenium/ATAF web).
 * Glue and feature paths are set via Surefire system properties in the ataf-ui profile.
 */
public class UiTestRunner extends BasicTestNGRunner {
    static {
        TestData.init();
    }
}
