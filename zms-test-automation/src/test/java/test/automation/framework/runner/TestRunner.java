package test.automation.framework.runner;

import ataf.core.runner.BasicTestNGRunner;
import test.automation.framework.base.TestData;

/**
 * This class is used for running the cucumber tests. By default all feature files and scenarios under src/test/resources/features are taken under
 * consideration. This is where the scenarios for local tests (e.g. by the developers should be placed.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class TestRunner extends BasicTestNGRunner {
    static {
        TestData.init();
    }
}
