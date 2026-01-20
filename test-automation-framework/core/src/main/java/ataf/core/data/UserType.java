package ataf.core.data;

import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;

/**
 * Represents the type of a user, identified by a string value. This class provides mechanisms to
 * retrieve or dynamically create user types, as well as a
 * predefined constant for a "NONE" user type.
 * <p>
 * Each {@code UserType} is registered in an internal map to ensure a consistent reference by string
 * value.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class UserType {

    private static final Map<String, UserType> TYPES = new ConcurrentHashMap<>();

    /**
     * A predefined constant representing an undefined or "NONE" user type.
     */
    public static final UserType NONE = create("NONE");

    private final String value;

    /**
     * Constructs a new UserType with the specified value.
     *
     * @param value the string value representing this user type
     */
    private UserType(String value) {
        this.value = value;
    }

    /**
     * Returns the string representation of this {@code UserType}.
     *
     * @return the string value of this user type
     */
    public String get() {
        return value;
    }

    /**
     * Retrieves the {@code UserType} associated with the given string value. If no matching user type
     * is found, returns {@link #NONE}.
     *
     * @param value the string value representing a user type
     * @return the corresponding {@code UserType}, or {@code NONE} if none is found
     */
    public static UserType fromString(String value) {
        return TYPES.getOrDefault(value, NONE);
    }

    /**
     * Creates a new {@code UserType} for the specified value if it does not already exist. If a
     * matching type is found, returns the existing instance.
     *
     * @param value the string value for which to create or retrieve a {@code UserType}
     * @return a {@code UserType} associated with the given value
     */
    public static UserType create(String value) {
        return TYPES.computeIfAbsent(value, UserType::new);
    }

    /**
     * Returns the string representation of this {@code UserType}.
     *
     * @return the string value of this user type
     */
    @Override
    public String toString() {
        return value;
    }
}
