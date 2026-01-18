package ataf.core.assertions.strategy.impl;

import ataf.core.assertions.strategy.AssertionStrategy;
import org.testng.Assert;

/**
 * A concrete implementation of {@link AssertionStrategy} using TestNG's assertion methods.
 * <p>
 * This class adapts the TestNG assertion library to the common {@link AssertionStrategy} interface,
 * enabling unified assertion handling. Each method
 * corresponds to a specific assertion in {@link Assert}.
 * </p>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class TestNGAssertionStrategy implements AssertionStrategy {

    /**
     * Asserts that the specified object is not null using TestNG's
     * {@link Assert#assertNotNull(Object, String)}.
     *
     * @param object The object to check for nullity.
     * @param message The assertion message to display on failure.
     * @throws AssertionError if the object is null.
     */
    @Override
    public void assertNotNull(Object object, String message) {
        Assert.assertNotNull(object, message);
    }

    /**
     * Fails a test with the specified message using TestNG's {@link Assert#fail(String)}.
     *
     * @param message The assertion message to display on failure.
     * @throws AssertionError unconditionally.
     */
    @Override
    public void fail(String message) {
        Assert.fail(message);
    }

    /**
     * Fails a test with the specified message and exception using TestNG's
     * {@link Assert#fail(String, Throwable)}.
     *
     * @param message The assertion message to display on failure.
     * @param exception The exception to be thrown.
     * @throws AssertionError wrapping the provided exception.
     */
    @Override
    public void fail(String message, Exception exception) {
        Assert.fail(message, exception);
    }

    /**
     * Asserts that the specified boolean condition is false using TestNG's
     * {@link Assert#assertFalse(boolean, String)}.
     *
     * @param condition The condition to evaluate.
     * @param message The assertion message to display on failure.
     * @throws AssertionError if the condition is true.
     */
    @Override
    public void assertFalse(boolean condition, String message) {
        Assert.assertFalse(condition, message);
    }

    /**
     * Asserts that the specified boolean condition is true using TestNG's
     * {@link Assert#assertTrue(boolean, String)}.
     *
     * @param condition The condition to evaluate.
     * @param message The assertion message to display on failure.
     * @throws AssertionError if the condition is false.
     */
    @Override
    public void assertTrue(boolean condition, String message) {
        Assert.assertTrue(condition, message);
    }

    /**
     * Asserts that two integers are equal using TestNG's {@link Assert#assertEquals(int, int, String)}.
     *
     * @param actual The actual value.
     * @param expected The expected value.
     * @param message The assertion message to display on failure.
     * @throws AssertionError if the actual value is not equal to the expected value.
     */
    @Override
    public void assertEquals(int actual, int expected, String message) {
        Assert.assertEquals(actual, expected, message);
    }
}
