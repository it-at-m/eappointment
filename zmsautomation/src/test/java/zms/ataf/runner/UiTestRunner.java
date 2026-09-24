package zms.ataf.runner;

import ataf.core.runner.ParallelTestNGRunner;
import zms.ataf.data.TestData;

/**
 * TestNG runner for UI-only Cucumber tests (Selenium/ATAF web).
 * Glue and feature paths are set via Surefire system properties in the ataf-ui profile.
 * Scenarios in this JVM run together, one browser per thread. Surefire
 * {@code dataproviderthreadcount} on the ataf-ui profile sets how many.
 * Each GitHub module shard is its own JVM.
 */
public class UiTestRunner extends ParallelTestNGRunner {
    static {
        TestData.init();
    }
}
