package ataf.core.helpers;

import ataf.core.clients.HttpClient;
import ataf.core.logging.ScenarioLogManager;
import org.identityconnectors.common.security.GuardedString;

import java.util.concurrent.atomic.AtomicBoolean;

/**
 * Helper class for managing authentication credentials and settings. This class provides methods to
 * set and retrieve user credentials, authorization tokens,
 * and the authentication method used for HTTP clients.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class AuthenticationHelper {
    private static GuardedString userName;
    private static final AtomicBoolean userNameSet = new AtomicBoolean(false);
    private static GuardedString userPassword;
    private static final AtomicBoolean userPasswordSet = new AtomicBoolean(false);
    private static GuardedString authorizationToken;
    private static final AtomicBoolean authorizationTokenSet = new AtomicBoolean(false);
    private static HttpClient.AuthenticationMethod authenticationMethod = HttpClient.AuthenticationMethod.None;

    /**
     * Sets the user name to be used for authentication. The user name can only be set once; subsequent
     * attempts will log a warning.
     *
     * @param userNameToSet The user name to set, as a char array.
     */
    public static void setUserName(char[] userNameToSet) {
        if (userNameSet.get()) {
            ScenarioLogManager.getLogger().warn("User name has been already set!");
        } else {
            userName = new GuardedString(userNameToSet);
            userName.makeReadOnly();
            userNameSet.set(true);
        }
    }

    /**
     * Sets the user password to be used for authentication. The password can only be set once;
     * subsequent attempts will log a warning.
     *
     * @param userPasswordToSet The user password to set, as a char array.
     */
    public static void setUserPassword(char[] userPasswordToSet) {
        if (userPasswordSet.get()) {
            ScenarioLogManager.getLogger().warn("Password has been already set!");
        } else {
            userPassword = new GuardedString(userPasswordToSet);
            userPassword.makeReadOnly();
            userPasswordSet.set(true);
        }
    }

    /**
     * Sets the authorization token to be used for authentication. The authorization token can only be
     * set once; subsequent attempts will log a warning.
     *
     * @param authorizationTokenToSet The authorization token to set, as a char array.
     */
    public static void setAuthorizationToken(char[] authorizationTokenToSet) {
        if (authorizationTokenSet.get()) {
            ScenarioLogManager.getLogger().warn("Authorization token has been already set!");
        } else {
            authorizationToken = new GuardedString(authorizationTokenToSet);
            authorizationToken.makeReadOnly();
            authorizationTokenSet.set(true);
        }
    }

    /**
     * Retrieves the user name set for authentication.
     *
     * @return The user name as a {@link GuardedString}.
     */
    public static GuardedString getUserName() {
        return userName;
    }

    /**
     * Retrieves the user password set for authentication.
     *
     * @return The user password as a {@link GuardedString}.
     */
    public static GuardedString getUserPassword() {
        return userPassword;
    }

    /**
     * Retrieves the authorization token set for authentication.
     *
     * @return The authorization token as a {@link GuardedString}.
     */
    public static GuardedString getAuthorizationToken() {
        return authorizationToken;
    }

    /**
     * Checks whether the required credentials have been set.
     *
     * @return {@code true} if either the user name or password has not been set; {@code false}
     *         otherwise.
     */
    public static boolean credentialsHaveNotBeenSet() {
        return !userNameSet.get() || !userPasswordSet.get();
    }

    /**
     * Checks whether the authorization token has been set.
     *
     * @return {@code true} if the authorization token has not been set; {@code false} otherwise.
     */
    public static boolean authorizationTokenHasNotBeenSet() {
        return !authorizationTokenSet.get();
    }

    /**
     * Disposes of the currently set credentials, making them unavailable for future use. Logs warnings
     * if credentials have not been set prior to disposal.
     */
    public static void disposeCredentials() {
        if (userNameSet.get()) {
            ScenarioLogManager.getLogger().info("User name disposed!");
            userName.dispose();
            userNameSet.set(false);
        } else {
            ScenarioLogManager.getLogger().warn("User name cannot be disposed! It has not been set yet.");
        }
        if (userPasswordSet.get()) {
            ScenarioLogManager.getLogger().info("Password disposed!");
            userPassword.dispose();
            userPasswordSet.set(false);
        } else {
            ScenarioLogManager.getLogger().warn("Password cannot be disposed! It has not been set yet.");
        }
        if (authorizationTokenSet.get()) {
            ScenarioLogManager.getLogger().info("Authorization token disposed!");
            authorizationToken.dispose();
            authorizationTokenSet.set(false);
        } else {
            ScenarioLogManager.getLogger().warn("Authorization token cannot be disposed! It has not been set yet.");
        }
    }

    /**
     * Retrieves the currently set authentication method.
     *
     * @return The current {@link HttpClient.AuthenticationMethod}.
     */
    public static HttpClient.AuthenticationMethod getAuthenticationMethod() {
        return AuthenticationHelper.authenticationMethod;
    }

    /**
     * Sets the authentication method to be used for HTTP clients.
     *
     * @param authenticationMethod The {@link HttpClient.AuthenticationMethod} to set.
     */
    public static void setAuthenticationMethod(HttpClient.AuthenticationMethod authenticationMethod) {
        AuthenticationHelper.authenticationMethod = authenticationMethod;
    }
}
