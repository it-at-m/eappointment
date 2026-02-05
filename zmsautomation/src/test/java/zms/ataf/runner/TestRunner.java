package zms.ataf.runner;

import org.junit.platform.suite.api.IncludeEngines;
import org.junit.platform.suite.api.SelectClasspathResource;
import org.junit.platform.suite.api.Suite;
import zms.ataf.data.TestData;

/**
 * JUnit Platform test suite for running Cucumber tests with ATAF.
 * Feature files are discovered from the features directory on the classpath.
 */
@Suite
@IncludeEngines("cucumber")
@SelectClasspathResource("features")
public class TestRunner {
    static {
        TestData.init();
    }
}
