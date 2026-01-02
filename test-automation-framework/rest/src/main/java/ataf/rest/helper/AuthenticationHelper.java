package ataf.rest.helper;

import ataf.rest.model.HeaderAuthentication;
import io.restassured.RestAssured;
import io.restassured.specification.RequestSpecification;

/**
 * This class handles authentication for requests, using predefined credentials and headers.
 *
 * @author Titus Pelzl (ex.pelzl), Philipp Lehmann (ex.lehmann08)
 */
public class AuthenticationHelper {
    private static String username;
    private static String password;
    private static HeaderAuthentication headerAuthentication;

    private AuthenticationHelper() {
        // Private constructor to prevent instantiation
    }

    /***
     * Creates a {@link RequestSpecification} and uses the credentials that have been set
     * to automatically apply the correct headers.
     *
     * @return RequestSpecification with the proper authorization headers
     */
    public static RequestSpecification setAuthorization() {
        return switch (headerAuthentication) {
            case BASIC_AUTH -> RestAssured.given().auth().preemptive().basic(username, password);
            default -> RestAssured.given().auth().none();
        };
    }

    /***
     * Sets the authorization parameters and returns a {@link RequestSpecification}
     * with the appropriate headers.
     *
     * @param _headerAuthentication The authentication method to be used (e.g., BASIC_AUTH)
     * @param _username The username for authentication
     * @param _password The password for authentication
     * @return RequestSpecification with authorization set
     */
    public static RequestSpecification setParametersAndAuthorization(HeaderAuthentication _headerAuthentication, String _username, String _password) {
        headerAuthentication = _headerAuthentication;
        username = _username;
        password = _password;
        return setAuthorization();
    }

    /***
     * Gets the username that has been set for authentication.
     *
     * @return The current username
     */
    public static String getUsername() {
        return username;
    }

    /***
     * Sets the username for authentication.
     *
     * @param username The username to be set
     */
    public static void setUsername(String username) {
        AuthenticationHelper.username = username;
    }

    /***
     * Gets the password that has been set for authentication.
     *
     * @return The current password
     */
    public static String getPassword() {
        return password;
    }

    /***
     * Sets the password for authentication.
     *
     * @param password The password to be set
     */
    public static void setPassword(String password) {
        AuthenticationHelper.password = password;
    }

    /***
     * Gets the header authentication method that has been set.
     *
     * @return The current header authentication method
     */
    public static HeaderAuthentication getHeaderAuthentication() {
        return headerAuthentication;
    }

    /***
     * Sets the header authentication method.
     *
     * @param headerAuthentication The header authentication method to be set
     */
    public static void setHeaderAuthentication(HeaderAuthentication headerAuthentication) {
        AuthenticationHelper.headerAuthentication = headerAuthentication;
    }

    /***
     * Resets the authentication parameters (username, password, and header authentication).
     */
    public static void resetParameters() {
        headerAuthentication = null;
        username = null;
        password = null;
    }
}
