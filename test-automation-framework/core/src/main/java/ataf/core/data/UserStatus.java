package ataf.core.data;

/**
 * Represents the status of a user in the system. This enumeration defines three possible states:
 * <ul>
 * <li>{@link #AVAILABLE} - The user is available for assignment.</li>
 * <li>{@link #IN_USE} - The user is currently in use.</li>
 * <li>{@link #SPENT} - The user is no longer available.</li>
 * </ul>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public enum UserStatus {
    /** The user is available for assignment. */
    AVAILABLE,

    /** The user is currently in use. */
    IN_USE,

    /** The user is no longer available. */
    SPENT
}
