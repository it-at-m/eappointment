package ataf.core.context;

import ataf.core.assertions.CustomAssertions;
import ataf.core.xray.Test;
import io.cucumber.java.Scenario;

import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;

/**
 * Manages the current {@link Test} instance associated with each thread during test execution.
 * <p>
 * This class allows for the retrieval, setting, and clearing of thread-specific {@link Test}
 * objects, ensuring that test data is correctly isolated and
 * accessible in multi-threaded environments.
 * <p>
 * It interacts with Cucumber's {@link Scenario} and the {@link TestExecutionContext} to determine
 * the appropriate test based on scenario tags.
 * <p>
 * Typical usage includes setting the current test at the beginning of a scenario, retrieving it
 * during execution, and clearing it afterward.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class TestContext {
    private static final Map<Long, Test> CURRENT_TEST_MAP = new ConcurrentHashMap<>();

    /**
     * Retrieves the current {@link Test} instance associated with the running thread.
     * <p>
     * This method fetches the {@link Test} object stored for the current thread. If no test is set, it
     * returns {@code null}.
     *
     * @return the current {@link Test} instance, or {@code null} if none is set
     */
    public static Test get() {
        return CURRENT_TEST_MAP.get(Thread.currentThread().getId());
    }

    /**
     * Sets the current {@link Test} instance for the running thread based on the provided
     * {@link Scenario}.
     * <p>
     * This method iterates over the scenario's source tags and retrieves the corresponding test from
     * the {@link TestExecutionContext} using the issue key
     * extracted from the tag. If no matching test is found, an assertion failure is triggered.
     *
     * @param scenario the Cucumber {@link Scenario} from which to determine the test
     * @throws AssertionError if no test could be associated with the scenario
     */
    public static void set(Scenario scenario) {
        for (String sourceTag : scenario.getSourceTagNames()) {
            Test test = TestExecutionContext.get().getTestByIssueKey(sourceTag.replace("@", ""));
            if (test != null) {
                CURRENT_TEST_MAP.put(Thread.currentThread().getId(), test);
                break;
            }
        }
        CustomAssertions.assertNotNull(CURRENT_TEST_MAP.get(Thread.currentThread().getId()),
                "Could not set current test! Scenario affected: " + scenario.getName());
    }

    /**
     * Clears the {@link Test} instance associated with the current thread.
     * <p>
     * This method removes the {@link Test} object stored for the current thread, ensuring that
     * thread-specific data is cleaned up after use.
     */
    public static void clear() {
        CURRENT_TEST_MAP.remove(Thread.currentThread().getId());
    }
}
