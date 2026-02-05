package zms.ataf.runner;

import org.junit.platform.suite.api.IncludeEngines;
import org.junit.platform.suite.api.Suite;

import zms.ataf.data.TestData;

/**
 * JUnit Platform test suite for running Cucumber tests with ATAF.
 * Feature files are discovered via cucumber.properties configuration.
 */
@Suite
@IncludeEngines("cucumber")
public class TestRunner {
    static {
        TestData.init();
    }
}
