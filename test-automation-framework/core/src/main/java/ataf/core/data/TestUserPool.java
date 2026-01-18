package ataf.core.data;

import ataf.core.logging.ScenarioLogManager;
import org.jetbrains.annotations.NotNull;

import java.util.List;
import java.util.Map;
import java.util.Optional;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.ThreadLocalRandom;

/**
 * Manages a singleton pool of {@link TestUser} objects. Each {@code TestUserPool} instance
 * maintains a map of test users, allowing for controlled acquisition
 * and release of users based on their availability, type, and environment.
 * <p>
 * When a test user is requested via {@link #acquireTestUser(UserType, Environment, boolean)}, it is
 * marked as {@link UserStatus#IN_USE}, ensuring that
 * concurrent tests do not reuse the same user simultaneously. If no user of the requested type is
 * available, the calling thread will wait until a user becomes
 * available.
 * <p>
 * When a user is released via {@link #releaseUser(TestUser, boolean)}, the user's status is updated
 * to either {@link UserStatus#AVAILABLE} or
 * {@link UserStatus#SPENT}. Releasing a user and notifying the pool can unblock waiting threads so
 * that they can acquire a newly freed user.
 * </p>
 *
 * <p>
 * <strong>Usage Example:</strong>
 * </p>
 *
 * <pre>{@code
 * TestUserPool pool = TestUserPool.getInstance();
 * TestUser user = pool.acquireTestUser(UserType.ADMIN, Environment.PRODUCTION);
 *
 * // Perform operations with the acquired user...
 *
 * // Release user after operations
 * pool.releaseUser(user, false);
 * }</pre>
 *
 * <p>
 * <strong>Thread Safety:</strong>
 * </p>
 * <ul>
 * <li>This class uses synchronization to ensure that multiple threads can
 * safely acquire and release users.</li>
 * <li>The singleton instance is created with double-checked locking, and all
 * modifications to the internal user map are performed in thread-safe ways.</li>
 * </ul>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class TestUserPool {

    /**
     * The singleton instance of this pool, created on demand with double-checked locking.
     */
    private static volatile TestUserPool instance;

    /**
     * A thread-safe map storing test users keyed by their usernames.
     */
    private static final Map<String, TestUser> TEST_USERS_MAP = new ConcurrentHashMap<>();

    /**
     * Private constructor that populates the pool with all {@link TestUser} instances retrieved from
     * {@link TestUser#getTestUsers()}.
     */
    private TestUserPool() {
        for (TestUser testUser : TestUser.getTestUsers()) {
            ScenarioLogManager.getLogger().debug("Adding test user [{}] to pool", testUser.getUserName());
            TEST_USERS_MAP.put(testUser.getUserName(), testUser);
        }
    }

    /**
     * Retrieves the singleton instance of {@code TestUserPool}, creating it if it does not already
     * exist.
     *
     * @return the singleton {@code TestUserPool} instance
     */
    public static TestUserPool getInstance() {
        if (instance == null) {
            synchronized (TestUserPool.class) {
                if (instance == null) {
                    instance = new TestUserPool();
                }
            }
        }
        return instance;
    }

    /**
     * Adds the specified {@link TestUser} to the pool if it is not already present. If the user is
     * already in the pool, logs a warning.
     *
     * @param testUser the {@code TestUser} to add
     */
    public synchronized void addUser(@NotNull TestUser testUser) {
        String userName = testUser.getUserName();
        if (TEST_USERS_MAP.containsKey(userName)) {
            ScenarioLogManager.getLogger().warn("Test user [{}] is already in the pool. Skipping addUser.", userName);
        } else {
            TEST_USERS_MAP.put(userName, testUser);
            ScenarioLogManager.getLogger().info("Added test user [{}] to the pool.", userName);
        }
    }

    /**
     * Adds the specified list of {@link TestUser} instances to the pool, only adding those which are
     * not already present. For each user that is already in the
     * pool, logs a warning.
     *
     * @param testUsers the list of {@code TestUser} instances to add
     */
    public synchronized void addUsers(@NotNull List<TestUser> testUsers) {
        for (TestUser testUser : testUsers) {
            addUser(testUser);
        }
    }

    /**
     * Removes the specified {@link TestUser} from the pool if it is present. If the user is not in the
     * pool, logs a warning.
     *
     * @param testUser the {@code TestUser} to remove
     */
    public synchronized void removeUser(@NotNull TestUser testUser) {
        String userName = testUser.getUserName();
        if (!TEST_USERS_MAP.containsKey(userName)) {
            ScenarioLogManager.getLogger().warn("Test user [{}] is not in the pool. Skipping removeUser.", userName);
        } else {
            TEST_USERS_MAP.remove(userName);
            ScenarioLogManager.getLogger().info("Removed test user [{}] from the pool.", userName);
        }
    }

    /**
     * Removes the specified list of {@link TestUser} instances from the pool, only removing those which
     * are already present. For each user that is not in the
     * pool, logs a warning.
     *
     * @param testUsers the list of {@code TestUser} instances to remove
     */
    public synchronized void removeUsers(@NotNull List<TestUser> testUsers) {
        for (TestUser testUser : testUsers) {
            removeUser(testUser);
        }
    }

    /**
     * Acquires a {@link TestUser} from the pool matching the specified {@link UserType} and
     * {@link Environment}. If no matching user is currently available,
     * the calling thread waits until a user becomes available.
     * <div>
     * Depending on the value of {@code acquireRandom}, this method behaves as follows:
     * <ul>
     * <li><strong>{@code acquireRandom == true}</strong>: Returns the first matching
     * available user encountered during the search.</li>
     * <li><strong>{@code acquireRandom == false}</strong>: Collects all available
     * matching users, then randomly selects one of them.</li>
     * </ul>
     * Upon returning a test user, the user's status is updated to {@link UserStatus#IN_USE}.
     * </div>
     *
     * @param type the desired {@code UserType}
     * @param environment the desired {@code Environment}
     * @param acquireRandom if {@code true}, the first matching user will be returned immediately;
     *            otherwise, a user is chosen randomly from all matching,
     *            available users
     * @return a matching {@code TestUser} whose status is set to {@link UserStatus#IN_USE}
     * @throws InterruptedException if the current thread is interrupted while waiting
     */
    public synchronized TestUser acquireTestUser(UserType type, Environment environment, boolean acquireRandom) throws InterruptedException {
        ScenarioLogManager.getLogger().debug("Acquiring test user from pool with user type [{}] for environment [{}]", type, environment.getName());
        while (true) {
            if (acquireRandom) {
                // Collect all matching, available users
                List<TestUser> availableMatchingUsers = TEST_USERS_MAP.values().parallelStream()
                        .filter(testUser -> testUser.TYPE.equals(type) && testUser.ENVIRONMENT == environment && testUser.getStatus() == UserStatus.AVAILABLE)
                        .toList();
                if (!availableMatchingUsers.isEmpty()) {
                    // Use ThreadLocalRandom for efficient random selection
                    TestUser chosenUser = availableMatchingUsers.get(
                            ThreadLocalRandom.current().nextInt(availableMatchingUsers.size()));
                    chosenUser.setStatus(UserStatus.IN_USE);
                    return chosenUser;
                }
            } else {
                // Return the first matching user encountered
                Optional<TestUser> chosenUser = TEST_USERS_MAP.values().stream()
                        .filter(testUser -> testUser.TYPE.equals(type) && testUser.ENVIRONMENT == environment && testUser.getStatus() == UserStatus.AVAILABLE)
                        .findFirst();
                if (chosenUser.isPresent()) {
                    chosenUser.get().setStatus(UserStatus.IN_USE);
                    return chosenUser.get();
                }
            }

            ScenarioLogManager.getLogger().info("No user of type [{}] for environment [{}] available! Waiting...", type, environment.getName());

            // Wait until notified that a user may be available
            wait();
        }
    }

    /**
     * Releases a {@link TestUser} back to the pool, optionally marking it as {@link UserStatus#SPENT}.
     * If not spent, the user's status is reset to
     * {@link UserStatus#AVAILABLE}, and all waiting threads are notified that a user may be available
     * again.
     *
     * @param testUser the {@code TestUser} to release
     * @param isSpent {@code true} to mark the user as spent; {@code false} to set the user back to
     *            available
     */
    public synchronized void releaseUser(@NotNull TestUser testUser, boolean isSpent) {
        if (isSpent) {
            testUser.setStatus(UserStatus.SPENT);
        } else {
            testUser.setStatus(UserStatus.AVAILABLE);
            notifyAll(); // Notify waiting threads that a user may be available
        }
    }
}
