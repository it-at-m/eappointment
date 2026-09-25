package zms.ataf.runner;

import ataf.core.runner.ParallelTestNGRunner;
import zms.ataf.data.TestData;

/**
 * TestNG runner for API-only Cucumber tests (no Selenium).
 * Glue and feature paths are set via Surefire system properties in the ataf-api profile.
 * Scenarios in this JVM run together. Surefire {@code dataproviderthreadcount} on the
 * ataf-api profile sets how many (default 32). {@code -Ddataproviderthreadcount=1} runs
 * one scenario at a time. Each GitHub module shard is its own JVM.
 */
public class ApiTestRunner extends ParallelTestNGRunner {
    static {
        TestData.init();
    }
}
