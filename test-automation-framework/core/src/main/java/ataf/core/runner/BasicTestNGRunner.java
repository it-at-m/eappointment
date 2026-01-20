package ataf.core.runner;

import ataf.core.assertions.CustomAssertions;
import ataf.core.assertions.strategy.impl.TestNGAssertionStrategy;
import ataf.core.utils.RunnerUtils;
import io.cucumber.testng.AbstractTestNGCucumberTests;
import org.testng.annotations.AfterSuite;
import org.testng.annotations.BeforeSuite;

/**
 * A TestNG runner for executing Cucumber tests.
 *
 * <p>
 * This class extends {@link AbstractTestNGCucumberTests} and is responsible for running Cucumber
 * tests defined in feature files located under the directory
 * <code>src/test/resources/features</code>. It serves as the entry point for executing
 * tests, making it easy for developers to run local tests.
 * </p>
 *
 * <p>
 * Before and after the execution of the test suite, setup and teardown methods are invoked to
 * manage the test environment appropriately.
 * </p>
 *
 * <p>
 * Developers should place their scenarios in the designated feature files within the specified
 * directory for local testing.
 * </p>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class BasicTestNGRunner extends AbstractTestNGCucumberTests {

    /**
     * Initializes the Basic TestNG Runner with TestNG-specific assertion strategy.
     * <p>
     * This constructor sets {@link CustomAssertions} to use {@link TestNGAssertionStrategy}, enabling
     * dynamic assertion handling for TestNG. All assertions
     * made using {@code CustomAssertions} will now delegate to TestNG's assertion methods.
     * </p>
     */
    public BasicTestNGRunner() {
        CustomAssertions.setStrategy(new TestNGAssertionStrategy());
    }

    /**
     * Executes setup operations before the test suite is run.
     *
     * <p>
     * This method is annotated with {@link BeforeSuite} and will run before any tests in the suite. It
     * is responsible for initializing the test environment by
     * calling {@link RunnerUtils#setupTestSuite()}.
     * </p>
     */
    @BeforeSuite(alwaysRun = true, groups = "beforeTestSuite")
    public void beforeTestSuite() {
        RunnerUtils.setupTestSuite();
    }

    /**
     * Executes teardown operations after the test suite has completed.
     *
     * <p>
     * This method is annotated with {@link AfterSuite} and will run after all tests in the suite have
     * been executed. It is responsible for cleaning up the test
     * environment by calling {@link RunnerUtils#tearDownTestSuite()}.
     * </p>
     */
    @AfterSuite(alwaysRun = true, groups = "afterTestSuite")
    public void afterTestSuite() {
        RunnerUtils.tearDownTestSuite();
    }
}
