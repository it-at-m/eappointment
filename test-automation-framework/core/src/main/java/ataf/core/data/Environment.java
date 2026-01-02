package ataf.core.data;

import org.jetbrains.annotations.NotNull;
import org.jetbrains.annotations.Nullable;

import java.util.HashSet;
import java.util.Objects;
import java.util.Set;
import java.util.TreeSet;
import java.util.concurrent.atomic.AtomicInteger;

/**
 * Represents an environment in the system, containing a set of systems. This class is comparable
 * based on the rank of the environment, and also maintains a
 * static collection of all environments.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class Environment extends TestData implements Comparable<Environment> {
    private static final Set<Environment> ENVIRONMENTS = new TreeSet<>();
    private static final AtomicInteger currentRank = new AtomicInteger(0);
    private final String name;
    private final int rank;
    private final Set<System> systems;

    /**
     * Constant for NONE value of environment.
     */
    public static final Environment NONE = new Environment("");

    /**
     * Constructs a new Environment with the specified name. The rank of the environment is
     * automatically assigned based on the creation order.
     *
     * @param name The name of the environment.
     */
    public Environment(String name) {
        super("Environment");
        this.name = name;
        this.rank = currentRank.getAndAccumulate(1, Integer::sum);
        this.systems = new HashSet<>();
        ENVIRONMENTS.add(this);
    }

    /**
     * Constructs a new Environment with the specified data type and name. The rank of the environment
     * is automatically assigned based on the creation order.
     *
     * @param dataType The type of data associated with this environment.
     * @param name The name of the environment.
     * @deprecated This constructor is deprecated due to changes in the environment initialization
     *             process. Use {@link #Environment(String)} instead, which
     *             simplifies the construction by removing the data type parameter.
     */
    @Deprecated
    public Environment(String dataType, String name) {
        super(dataType);
        this.name = name;
        this.rank = currentRank.getAndAccumulate(1, Integer::sum);
        this.systems = new HashSet<>();
        ENVIRONMENTS.add(this);
    }

    /**
     * Retrieves the name of this environment.
     *
     * @return The name of the environment.
     */
    public String getName() {
        return name;
    }

    /**
     * Retrieves the rank of this environment.
     *
     * @return The rank of the environment, based on its creation order.
     */
    public int getRank() {
        return rank;
    }

    /**
     * Retrieves the URL of a system within this environment by its name.
     *
     * @param systemName The name of the system to look up.
     * @return The URL of the system if found, or an empty string if the system is not present.
     */
    public String getSystemUrl(String systemName) {
        return systems.stream()
                .filter(system -> Objects.equals(system.NAME, systemName))
                .map(system -> system.URL)
                .findFirst()
                .orElse("");
    }

    /**
     * Adds a new system to this environment, identified by a name and URL.
     *
     * @param name The name of the system to add.
     * @param url The URL associated with the system.
     */
    public void addSystem(String name, String url) {
        ENVIRONMENTS.remove(this);
        systems.add(new System(name, url));
        ENVIRONMENTS.add(this);
    }

    /**
     * Checks if an environment with the specified name exists within the static collection of
     * environments.
     *
     * @param name The name of the environment to search for.
     * @return The matching environment if found, or {@code null} if no match is found.
     */
    public static @Nullable
    Environment contains(String name) {
        return ENVIRONMENTS.stream()
                .filter(environment -> Objects.equals(environment.getName(), name))
                .findFirst()
                .orElse(null);
    }

    /**
     * Compares this environment to another based on their ranks.
     *
     * @param other The other environment to compare to.
     * @return A negative integer, zero, or a positive integer as this environment's rank is less than,
     *         equal to, or greater than the specified environment's
     *         rank.
     */
    @Override
    public int compareTo(@NotNull Environment other) {
        return Integer.compare(this.rank, other.rank);
    }
}
