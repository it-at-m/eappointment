package ataf.core.reader.resolver;

import ataf.core.data.UserType;

/**
 * Provides methods for determining user-specific details—such as username, password, and
 * {@link UserType}—based on a raw string value. Implementations can use
 * any desired logic, such as mapping from a configuration file, pattern matching, or database
 * lookups, to resolve the user data.
 *
 * <p>
 * This interface is designed to offer a flexible approach to user creation and customization
 * without embedding these details directly into the code.
 * </p>
 *
 * <p>
 * <strong>Example Usage:</strong>
 * </p>
 *
 * <pre>
 * TestUserResolver resolver = new TestUserResolver() {
 *     &#64;Override
 *     public String resolveName(String rawValue) {
 *         // Logic to extract a username
 *         return "extractedUsername";
 *     }
 *
 *     &#64;Override
 *     public String resolvePassword(String rawValue) {
 *         // Logic to derive a password
 *         return "extractedPassword";
 *     }
 *
 *     &#64;Override
 *     public UserType resolveType(String rawValue) {
 *         if ("admin".equalsIgnoreCase(rawValue)) {
 *             return UserType.create("ADMIN");
 *         }
 *         return UserType.NONE;
 *     }
 * };
 * </pre>
 *
 * <p>
 * By abstracting user resolution into this interface, different sources or formats (e.g., CSV
 * files, JSON payloads) can easily adapt the process of user
 * creation without changing the rest of the system.
 * </p>
 *
 * <p>
 * <strong>Thread Safety:</strong> Implementations should ensure that any
 * shared resources used in the resolution process are managed safely when used in multi-threaded
 * scenarios.
 * </p>
 *
 * <p>
 * <strong>Author:</strong> Ludwig Haas (ex.haas02)
 * </p>
 */
public interface TestUserResolver {

    /**
     * Resolves a string value to a corresponding username.
     *
     * @param rawValue the raw string used to determine the username
     * @return the resolved username, or {@code null} if the resolution fails
     */
    String resolveName(String rawValue);

    /**
     * Resolves a string value to a corresponding password.
     *
     * @param rawValue the raw string used to determine the password
     * @return the resolved password, or {@code null} if the resolution fails
     */
    String resolvePassword(String rawValue);

    /**
     * Resolves a string value to a corresponding {@link UserType}.
     *
     * @param rawValue the raw string used to determine the user type
     * @return the resolved {@code UserType}, or {@link UserType#NONE} if no specific match is found
     */
    UserType resolveType(String rawValue);
}
