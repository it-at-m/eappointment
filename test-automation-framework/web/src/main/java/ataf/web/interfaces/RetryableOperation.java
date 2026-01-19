package ataf.web.interfaces;

/**
 * Functional interface representing a retryable operation. Implementations of this interface should
 * define a single method, {@code execute}, that attempts to
 * perform an operation and returns a boolean indicating success or failure.
 * <p>
 * This interface is intended to be used in scenarios where an operation might fail and needs to be
 * retried multiple times.
 *
 * <p>
 * Example usage:
 * </p>
 *
 * <pre>
 * {@code
 * RetryableOperation operation = () -> {
 *     try {
 *         // Perform some action
 *         return true;
 *     } catch (Exception e) {
 *         // Handle failure and decide whether to retry
 *         return false;
 *     }
 * };
 * }
 * </pre>
 *
 * @author Mohamad Daaeboul
 */
@FunctionalInterface
public interface RetryableOperation {

    /**
     * Attempts to perform an operation.
     *
     * @return {@code true} if the operation was successful, {@code false} otherwise.
     */
    boolean execute();
}
