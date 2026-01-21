package ataf.core.assertions.strategy.impl;

import ataf.core.assertions.strategy.AssertionStrategy;
import org.junit.jupiter.api.Assertions;

/**
 * A concrete implementation of {@link AssertionStrategy} using JUnit 5's assertion methods.
 * <p>
 * This class adapts the JUnit 5 assertion library to the common {@link AssertionStrategy}
 * interface, enabling unified assertion handling. Each method
 * corresponds to a specific assertion in {@link Assertions}.
 * </p>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class JUnitAssertionStrategy implements AssertionStrategy {

    /**
     * Asserts that the specified object is not null using JUnit 5's
     * {@link Assertions#assertNotNull(Object, String)}.
     *
     * @param object The object to check for nullity.
     * @param message The assertion message to display on failure.
     * @throws AssertionError if the object is null.
     */
    @Override
    public void assertNotNull(Object object, String message) {
        Assertions.assertNotNull(object, message);
    }

    /**
     * Fails a test with the specified message using JUnit 5's {@link Assertions#fail(String)}.
     *
     * @param message The assertion message to display on failure.
     * @throws AssertionError unconditionally.
     */
    @Override
    public void fail(String message) {
        Assertions.fail(message);
    }

    /**
     * Fails a test with the specified message and exception using JUnit 5's
     * {@link Assertions#fail(String, Throwable)}.
     *
     * @param message The assertion message to display on failure.
     * @param exception The exception to be thrown.
     * @throws AssertionError wrapping the provided exception.
     */
    @Override
    public void fail(String message, Exception exception) {
        Assertions.fail(message, exception);
    }

    /**
     * Asserts that the specified boolean condition is false using JUnit 5's
     * {@link Assertions#assertFalse(boolean, String)}.
     *
     * @param condition The condition to evaluate.
     * @param message The assertion message to display on failure.
     * @throws AssertionError if the condition is true.
     */
    @Override
    public void assertFalse(boolean condition, String message) {
        Assertions.assertFalse(condition, message);
    }

    /**
     * Asserts that the specified boolean condition is true using JUnit 5's
     * {@link Assertions#assertTrue(boolean, String)}.
     *
     * @param condition The condition to evaluate.
     * @param message The assertion message to display on failure.
     * @throws AssertionError if the condition is false.
     */
    @Override
    public void assertTrue(boolean condition, String message) {
        Assertions.assertTrue(condition, message);
    }

    /**
     * Asserts that two integers are equal using JUnit 5's
     * {@link Assertions#assertEquals(int, int, String)}.
     *
     * @param actual The actual value.
     * @param expected The expected value.
     * @param message The assertion message to display on failure.
     * @throws AssertionError if the actual value is not equal to the expected value.
     */
    @Override
    public void assertEquals(int actual, int expected, String message) {
        Assertions.assertEquals(expected, actual, message);
    }
}
