package ataf.core.clients;

import ataf.core.assertions.CustomAssertions;
import ataf.core.assertions.strategy.impl.TestNGAssertionStrategy;
import ataf.core.helpers.AuthenticationHelper;
import org.identityconnectors.common.security.GuardedString;
import org.mockito.Mockito;
import org.testng.Assert;
import org.testng.annotations.AfterClass;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;

import static org.mockito.Mockito.when;

/**
 * @author Ludwig Haas (ex.haas02)
 */
public class AuthHeaderTest {

    private JiraClient jiraClient;

    @BeforeClass
    public void setUp() {
        CustomAssertions.setStrategy(new TestNGAssertionStrategy());
        jiraClient = new JiraClient();
    }

    @AfterClass
    public void tearDown() {
        jiraClient.closeHttpClient();
    }

    @Test
    public void testAddBasicAuthHeader_credentialsNotSet_throwsAssertionError() {
        try (var ignored = Mockito.mockStatic(AuthenticationHelper.class)) {
            when(AuthenticationHelper.credentialsHaveNotBeenSet()).thenReturn(true);
            when(AuthenticationHelper.getAuthenticationMethod()).thenReturn(HttpClient.AuthenticationMethod.BasicAuth);

            Assert.assertThrows("AssertionError expected due to missing credentials!", AssertionError.class,
                    () -> jiraClient.executeHttpGetRequest(JiraClient.JIRA_REST_API_URL + "myself", AuthenticationHelper.getAuthenticationMethod()));
        }
    }

    @Test
    public void testAddBasicAuthHeader_userNameBlank_throwsIllegalStateException() {
        try (var ignored = Mockito.mockStatic(AuthenticationHelper.class)) {
            when(AuthenticationHelper.credentialsHaveNotBeenSet()).thenReturn(false);
            when(AuthenticationHelper.getAuthenticationMethod()).thenReturn(HttpClient.AuthenticationMethod.BasicAuth);
            when(AuthenticationHelper.getUserName()).thenReturn(new GuardedString("".toCharArray()));
            when(AuthenticationHelper.getUserPassword()).thenReturn(new GuardedString("password".toCharArray()));

            Assert.assertEquals(jiraClient.executeHttpGetRequest(JiraClient.JIRA_REST_API_URL + "myself", AuthenticationHelper.getAuthenticationMethod()),
                    "Username must be supplied and must not be blank!", "IllegalStateException expected due to blank username");
        }
    }

    @Test
    public void testAddBasicAuthHeader_userPasswordBlank_throwsIllegalStateException() {
        try (var ignored = Mockito.mockStatic(AuthenticationHelper.class)) {
            when(AuthenticationHelper.credentialsHaveNotBeenSet()).thenReturn(false);
            when(AuthenticationHelper.getAuthenticationMethod()).thenReturn(HttpClient.AuthenticationMethod.BasicAuth);
            when(AuthenticationHelper.getUserName()).thenReturn(new GuardedString("user".toCharArray()));
            when(AuthenticationHelper.getUserPassword()).thenReturn(new GuardedString(" ".toCharArray()));

            Assert.assertEquals(jiraClient.executeHttpGetRequest(JiraClient.JIRA_REST_API_URL + "myself", AuthenticationHelper.getAuthenticationMethod()),
                    "Password must be supplied and must not be blank!", "IllegalStateException expected due to blank password");
        }
    }

    @Test
    public void testAddBasicAuthHeader_success_addsHeader() {
        try (var ignored = Mockito.mockStatic(AuthenticationHelper.class)) {
            when(AuthenticationHelper.credentialsHaveNotBeenSet()).thenReturn(false);
            when(AuthenticationHelper.getAuthenticationMethod()).thenReturn(HttpClient.AuthenticationMethod.BasicAuth);
            when(AuthenticationHelper.getUserName()).thenReturn(new GuardedString("user".toCharArray()));
            when(AuthenticationHelper.getUserPassword()).thenReturn(new GuardedString("pw".toCharArray()));

            jiraClient.executeHttpGetRequest(JiraClient.JIRA_REST_API_URL + "myself", AuthenticationHelper.getAuthenticationMethod());
            Assert.assertEquals(jiraClient.getLastRequestStatusCode(), 401);
        }
    }

    @Test
    public void testAddAuthorizationHeader_tokenNotSet_throwsAssertionError() {
        try (var ignored = Mockito.mockStatic(AuthenticationHelper.class)) {
            when(AuthenticationHelper.authorizationTokenHasNotBeenSet()).thenReturn(true);
            when(AuthenticationHelper.getAuthenticationMethod()).thenReturn(HttpClient.AuthenticationMethod.AccessToken);
            when(AuthenticationHelper.getAuthorizationToken()).thenReturn(null);

            Assert.assertThrows("AssertionError expected due to missing token!", AssertionError.class,
                    () -> jiraClient.executeHttpGetRequest(JiraClient.JIRA_REST_API_URL + "myself", AuthenticationHelper.getAuthenticationMethod()));
        }
    }

    @Test
    public void testAddAuthorizationHeader_tokenBlank_throwsIllegalStateException() {
        try (var ignored = Mockito.mockStatic(AuthenticationHelper.class)) {
            when(AuthenticationHelper.authorizationTokenHasNotBeenSet()).thenReturn(false);
            when(AuthenticationHelper.getAuthenticationMethod()).thenReturn(HttpClient.AuthenticationMethod.AccessToken);
            when(AuthenticationHelper.getAuthorizationToken()).thenReturn(new GuardedString(" ".toCharArray()));

            Assert.assertEquals(jiraClient.executeHttpGetRequest(JiraClient.JIRA_REST_API_URL + "myself", AuthenticationHelper.getAuthenticationMethod()),
                    "Jira authorization token must be supplied and must not be blank!", "IllegalStateException expected due to blank token");
        }
    }

    @Test
    public void testAddAuthorizationHeader_success_addsHeader() {
        try (var ignored = Mockito.mockStatic(AuthenticationHelper.class)) {
            when(AuthenticationHelper.authorizationTokenHasNotBeenSet()).thenReturn(false);
            when(AuthenticationHelper.getAuthenticationMethod()).thenReturn(HttpClient.AuthenticationMethod.AccessToken);
            when(AuthenticationHelper.getAuthorizationToken()).thenReturn(new GuardedString("token123".toCharArray()));

            jiraClient.executeHttpGetRequest(JiraClient.JIRA_REST_API_URL + "myself", AuthenticationHelper.getAuthenticationMethod());
            Assert.assertEquals(jiraClient.getLastRequestStatusCode(), 401);
        }
    }
}
