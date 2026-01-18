package ataf.core.assertions.strategy;

/**
 * An interface defining a common strategy for different assertion frameworks. This allows the
 * selection of assertion methods at runtime.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public interface AssertionStrategy {

    /**
     * Asserts that the specified object is not null.
     *
     * @param object The object to check for nullity.
     * @param message The assertion message to display on failure.
     * @throws AssertionError if the object is null.
     */
    void assertNotNull(Object object, String message);

    /**
     * Fails a test with the specified message.
     *
     * @param message The assertion message to display on failure.
     * @throws AssertionError unconditionally.
     */
    void fail(String message);

    /**
     * Fails a test with the specified message and exception.
     *
     * @param message The assertion message to display on failure.
     * @param exception The exception to be thrown.
     * @throws AssertionError wrapping the provided exception.
     */
    void fail(String message, Exception exception);

    /**
     * Asserts that the specified boolean condition is false.
     *
     * @param condition The condition to evaluate.
     * @param message The assertion message to display on failure.
     * @throws AssertionError if the condition is true.
     */
    void assertFalse(boolean condition, String message);

    /**
     * Asserts that the specified boolean condition is true.
     *
     * @param condition The condition to evaluate.
     * @param message The assertion message to display on failure.
     * @throws AssertionError if the condition is false.
     */
    void assertTrue(boolean condition, String message);

    /**
     * Asserts that two integers are equal.
     *
     * @param actual The actual value.
     * @param expected The expected value.
     * @param message The assertion message to display on failure.
     * @throws AssertionError if the actual value is not equal to the expected value.
     */
    void assertEquals(int actual, int expected, String message);
}
