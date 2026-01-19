package ataf.core.runner;

import org.testng.annotations.DataProvider;

/**
 * A TestNG runner for executing Cucumber tests in parallel.
 *
 * <p>
 * This class extends {@link BasicTestNGRunner} and overrides the {@link #scenarios()} method to
 * enable parallel execution of test scenarios. By leveraging the
 * {@link DataProvider} annotation with the <code>parallel = true</code> setting, this runner allows
 * multiple scenarios to be executed concurrently, which can
 * significantly reduce the overall test execution time.
 * </p>
 *
 * <p>
 * Developers can use this runner to improve the efficiency of their testing by running scenarios in
 * parallel while still maintaining the setup and teardown
 * procedures defined in the parent class.
 * </p>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class ParallelTestNGRunner extends BasicTestNGRunner {

    /**
     * Provides the test scenarios to be executed in parallel.
     *
     * <p>
     * This method is annotated with {@link DataProvider} to indicate that it supplies data for the test
     * methods. The <code>parallel = true</code> setting
     * allows multiple scenarios to be run at the same time, enhancing test performance.
     * </p>
     *
     * @return An array of test scenarios from the parent class, ready for parallel execution.
     */
    @Override
    @DataProvider(parallel = true)
    public Object[][] scenarios() {
        return super.scenarios();
    }
}
