package ataf.core.context;

import io.cucumber.java.Scenario;

import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;

/**
 * Manages thread-specific {@link Scenario} objects, ensuring that each thread can store and
 * retrieve its associated scenario independently.
 * <p>
 * This class uses a {@link ConcurrentHashMap} to maintain a mapping between thread IDs and their
 * corresponding {@link Scenario} instances, enabling thread-safe
 * operations in multi-threaded environments.
 * <p>
 * Typical usage involves storing a scenario object for the current thread, retrieving it during
 * execution, and clearing it once it's no longer needed to
 * prevent memory leaks.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class ScenarioContext {
    private static final Map<Long, Scenario> SCENARIO_MAP = new ConcurrentHashMap<>();

    /**
     * Associates the specified {@link Scenario} with the current thread.
     * <p>
     * This method stores the scenario object in a thread-specific manner, allowing it to be retrieved
     * later using the {@link #get()} method.
     *
     * @param scenario the {@link Scenario} to associate with the current thread
     */
    public static void put(Scenario scenario) {
        SCENARIO_MAP.put(Thread.currentThread().getId(), scenario);
    }

    /**
     * Retrieves the {@link Scenario} associated with the current thread.
     * <p>
     * This method fetches the scenario object stored for the current thread. If no scenario is
     * associated, this method returns {@code null}.
     *
     * @return the {@link Scenario} associated with the current thread, or {@code null} if none exists
     */
    public static Scenario get() {
        return SCENARIO_MAP.get(Thread.currentThread().getId());
    }

    /**
     * Removes the {@link Scenario} associated with the current thread.
     * <p>
     * This method clears the scenario object stored for the current thread, preventing memory leaks and
     * ensuring that thread-specific data is properly cleaned
     * up after use.
     */
    public static void clear() {
        SCENARIO_MAP.remove(Thread.currentThread().getId());
    }
}
