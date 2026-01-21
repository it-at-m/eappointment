package ataf.core.assertions;

import ataf.core.assertions.strategy.AssertionStrategy;

/**
 * A wrapper class for dynamic assertion methods that adapts to the underlying assertion framework.
 * <p>
 * This class provides static methods that delegate to the currently set {@link AssertionStrategy}.
 * Set the strategy at runtime to switch between different
 * assertion frameworks.
 * </p>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class CustomAssertions {
    private static AssertionStrategy strategy;

    /**
     * Sets the current {@link AssertionStrategy} to use for assertions.
     *
     * @param strategy The strategy to use for assertions.
     */
    public static void setStrategy(AssertionStrategy strategy) {
        CustomAssertions.strategy = strategy;
    }

    /**
     * Asserts that the specified object is not null using the currently active
     * {@link AssertionStrategy}.
     *
     * @param object The object to check for nullity.
     * @param message The assertion message to display on failure.
     * @throws AssertionError if the object is null.
     */
    public static void assertNotNull(Object object, String message) {
        strategy.assertNotNull(object, message);
    }

    /**
     * Fails a test with the specified message using the currently active {@link AssertionStrategy}.
     *
     * @param message The assertion message to display on failure.
     * @throws AssertionError unconditionally.
     */
    public static void fail(String message) {
        strategy.fail(message);
    }

    /**
     * Fails a test with the specified message and exception using the currently active
     * {@link AssertionStrategy}.
     *
     * @param message The assertion message to display on failure.
     * @param exception The exception to be thrown.
     * @throws AssertionError wrapping the provided exception.
     */
    public static void fail(String message, Exception exception) {
        strategy.fail(message, exception);
    }

    /**
     * Asserts that the specified boolean condition is false using the currently active
     * {@link AssertionStrategy}.
     *
     * @param condition The condition to evaluate.
     * @param message The assertion message to display on failure.
     * @throws AssertionError if the condition is true.
     */
    public static void assertFalse(boolean condition, String message) {
        strategy.assertFalse(condition, message);
    }

    /**
     * Asserts that the specified boolean condition is true using the currently active
     * {@link AssertionStrategy}.
     *
     * @param condition The condition to evaluate.
     * @param message The assertion message to display on failure.
     * @throws AssertionError if the condition is false.
     */
    public static void assertTrue(boolean condition, String message) {
        strategy.assertTrue(condition, message);
    }

    /**
     * Asserts that two integers are equal using the currently active {@link AssertionStrategy}.
     *
     * @param actual The actual value.
     * @param expected The expected value.
     * @param message The assertion message to display on failure.
     * @throws AssertionError if the actual value is not equal to the expected value.
     */
    public static void assertEquals(int actual, int expected, String message) {
        strategy.assertEquals(actual, expected, message);
    }
}
