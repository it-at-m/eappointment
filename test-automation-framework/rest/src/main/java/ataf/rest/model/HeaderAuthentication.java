package ataf.rest.model;

/**
 * Enum representing the type of header-based authentication used in HTTP requests.
 *
 * <ul>
 * <li>{@link #BASIC_AUTH} - Represents Basic Authentication, where credentials are provided in the
 * header.</li>
 * <li>{@link #NO_HEADER} - Represents no authentication header being used.</li>
 * </ul>
 *
 * @author Titus Pelzl (ex.pelzl), Philipp Lehmann (ex.lehmann08)
 */
public enum HeaderAuthentication {
    /**
     * Basic Authentication, where the username and password are included in the request header.
     */
    BASIC_AUTH,

    /**
     * No authentication header is used in the request.
     */
    NO_HEADER
}
