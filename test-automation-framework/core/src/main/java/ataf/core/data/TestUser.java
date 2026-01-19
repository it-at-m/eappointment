package ataf.core.data;

import ataf.core.helpers.TestDataHelper;

import java.util.List;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;

/**
 * Represents a test user for automated tests. This class extends {@link TestData} and stores
 * essential information such as username, encrypted password,
 * environment, and user type. It also maintains an internal map for quick retrieval of existing
 * test users by username. The password is decrypted upon access
 * through {@link #getPassword()}.
 * <p>
 * Synchronization is used for methods that access or modify the user's status to ensure thread
 * safety.
 * </p>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class TestUser extends TestData {

    /**
     * A concurrent map that stores test users keyed by their usernames.
     */
    private static final Map<String, TestUser> TEST_USERS = new ConcurrentHashMap<>(0);

    /**
     * The environment associated with this test user.
     */
    public final Environment ENVIRONMENT;

    /**
     * The user type associated with this test user.
     */
    public final UserType TYPE;

    /**
     * The username of this test user.
     */
    private final String userName;

    /**
     * The encrypted password of this test user.
     */
    private final String password;

    /**
     * The current status of this test user, indicating whether it is available or in use.
     */
    private volatile UserStatus status;

    /**
     * Constructs a new {@code TestUser} with all specified parameters. The user is then stored in the
     * internal map.
     *
     * @param userName the username for this test user
     * @param password the encrypted password for this test user
     * @param environment the environment to which this test user belongs
     * @param type the type of this test user (e.g., admin, guest, etc.)
     */
    public TestUser(String userName, String password, Environment environment, UserType type) {
        super("TestUser");
        this.userName = userName;
        this.password = password;
        this.status = UserStatus.AVAILABLE;
        this.ENVIRONMENT = environment;
        this.TYPE = type;
        TEST_USERS.put(userName, this);
    }

    /**
     * Constructs a new {@code TestUser} with the specified username and password, defaulting the
     * environment to {@link Environment#NONE} and the user type to
     * {@link UserType#NONE}.
     *
     * @param userName the username for this test user
     * @param password the encrypted password for this test user
     */
    public TestUser(String userName, String password) {
        this(userName, password, Environment.NONE, UserType.NONE);
    }

    /**
     * Constructs a new {@code TestUser} with the specified username, password, and user type,
     * defaulting the environment to {@link Environment#NONE}.
     *
     * @param userName the username for this test user
     * @param password the encrypted password for this test user
     * @param type the user type for this test user
     */
    public TestUser(String userName, String password, UserType type) {
        this(userName, password, Environment.NONE, type);
    }

    /**
     * Retrieves the {@code TestUser} associated with the given username, or {@code null} if no matching
     * user is found.
     *
     * @param userName the username to look up
     * @return the matching {@code TestUser}, or {@code null} if not found
     */
    public static TestUser getTestUser(String userName) {
        return TEST_USERS.get(userName);
    }

    /**
     * Returns a list of all {@code TestUser} instances currently stored in the internal map.
     *
     * @return a list of {@code TestUser} objects
     */
    public static List<TestUser> getTestUsers() {
        return TEST_USERS.values().parallelStream().toList();
    }

    /**
     * Returns the username of this test user.
     *
     * @return the username
     */
    public String getUserName() {
        return userName;
    }

    /**
     * Returns the decrypted password of this test user. Decryption is performed using the
     * {@link TestDataHelper#decryptTestData(String)} method.
     *
     * @return the decrypted password
     */
    public String getPassword() {
        return TestDataHelper.decryptTestData(password);
    }

    /**
     * Retrieves the current status of this test user in a thread-safe manner.
     *
     * @return the user's current status
     */
    synchronized UserStatus getStatus() {
        return status;
    }

    /**
     * Sets the status of this test user in a thread-safe manner.
     *
     * @param status the new status to be set
     */
    synchronized void setStatus(UserStatus status) {
        this.status = status;
    }
}
