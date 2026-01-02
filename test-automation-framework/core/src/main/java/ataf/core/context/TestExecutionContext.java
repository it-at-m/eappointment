package ataf.core.context;

import ataf.core.utils.RunnerUtils;
import ataf.core.xray.TestExecution;
import io.cucumber.java.Scenario;

import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;

/**
 * Manages the current {@link TestExecution} instance associated with each thread during test
 * execution.
 * <p>
 * This class enables thread-specific storage and retrieval of {@link TestExecution} objects,
 * ensuring that each thread handles its own execution context
 * independently.
 * <p>
 * It uses scenario tags to map the appropriate {@link TestExecution} instance for the thread.
 * <p>
 * Typical usage involves setting the current test execution at the beginning of a scenario,
 * retrieving it during execution, and clearing it afterward to free
 * resources.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class TestExecutionContext {
    private static final Map<Long, TestExecution> CURRENT_TEST_EXECUTION_MAP = new ConcurrentHashMap<>();

    /**
     * Retrieves the current {@link TestExecution} instance associated with the running thread.
     * <p>
     * This method fetches the {@link TestExecution} object stored for the current thread. If no
     * execution context is set, it returns {@code null}.
     *
     * @return the current {@link TestExecution} instance, or {@code null} if none is set
     */
    public static TestExecution get() {
        return CURRENT_TEST_EXECUTION_MAP.get(Thread.currentThread().getId());
    }

    /**
     * Sets the current {@link TestExecution} instance for the running thread based on the provided
     * {@link Scenario}.
     * <p>
     * This method retrieves the appropriate {@link TestExecution} from the
     * `RunnerUtils.TEST_EXECUTION_MAP` using the first tag from the scenario's source
     * tags. The retrieved {@link TestExecution} is then associated with the current thread.
     *
     * @param scenario the Cucumber {@link Scenario} from which to determine the test execution context
     */
    public static void set(Scenario scenario) {
        CURRENT_TEST_EXECUTION_MAP.put(Thread.currentThread().getId(),
                RunnerUtils.TEST_EXECUTION_MAP.get(scenario.getSourceTagNames().toArray()[0].toString().replace("@", "")));
    }

    /**
     * Clears the current {@link TestExecution} instance for the running thread.
     * <p>
     * This method removes the {@link TestExecution} object stored for the current thread, ensuring that
     * thread-specific data is properly cleaned up after use.
     */
    public static void clear() {
        CURRENT_TEST_EXECUTION_MAP.clear();
    }
}
