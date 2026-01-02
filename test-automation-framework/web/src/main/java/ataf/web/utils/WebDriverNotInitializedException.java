package ataf.web.utils;

/**
 * Exception thrown when a WebDriver instance is not initialized for the current thread.
 * <p>
 * This typically indicates that the WebDriver was not properly set up for the thread executing the
 * test. It may also suggest a missing or misconfigured test
 * setup.
 * </p>
 *
 * <p>
 * Example usage:
 * </p>
 *
 * <pre>{@code
 * if (driver == null) {
 *     throw new WebDriverNotInitializedException(
 *             "For thread [" + Thread.currentThread().getId() +
 *                     "] no WebDriver instance was created!");
 * }
 * }</pre>
 *
 * <p>
 * Common scenarios where this exception may occur:
 * </p>
 * <ul>
 * <li>A required tag (e.g., 'web') was not added to the test scenario.</li>
 * <li>The WebDriver instance was not initialized before test execution.</li>
 * <li>A misconfiguration in the thread-to-WebDriver mapping.</li>
 * </ul>
 *
 * @author Ludwig Haas (ex.haas02)
 * @see RuntimeException
 */
public class WebDriverNotInitializedException extends RuntimeException {
    /**
     * Constructs a new WebDriverNotInitializedException with the specified detail message.
     *
     * @param message the detail message that explains the cause of the exception.
     */
    public WebDriverNotInitializedException(String message) {
        super(message);
    }
}
