package ataf.core.data;

/**
 * Represents a system with a name and a URL. Instances of this class are immutable, with final
 * fields for the system's name and URL.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class System extends TestData {

    /**
     * The name of the system.
     */
    public final String NAME;

    /**
     * The URL associated with the system.
     */
    public final String URL;

    /**
     * Constructs a new System with the specified name and URL.
     *
     * @param name The name of the system.
     * @param url The URL associated with the system.
     */
    public System(String name, String url) {
        super("System");
        this.NAME = name;
        this.URL = url;
    }
}
