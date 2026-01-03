package ataf.core.clients;

import ataf.core.assertions.CustomAssertions;
import ataf.core.helpers.AuthenticationHelper;
import ataf.core.logging.ScenarioLogManager;
import com.google.gson.Gson;
import com.google.gson.JsonObject;
import org.apache.hc.core5.http.Header;

import java.time.LocalDateTime;
import java.time.ZoneOffset;
import java.util.concurrent.atomic.AtomicInteger;
import java.util.concurrent.atomic.AtomicLong;
import java.util.concurrent.atomic.AtomicReference;

/**
 * A client for interacting with Jira's REST API, extending the base HttpClient. This client handles
 * rate limiting and retry logic based on the headers returned
 * by Jira.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class JiraClient extends HttpClient {
    private static final AtomicInteger X_RATE_LIMIT_REMAINING = new AtomicInteger(-1);
    private static final AtomicLong RETRY_AFTER = new AtomicLong(5000L);
    private static final AtomicInteger X_RATE_LIMIT_FILL_RATE = new AtomicInteger(-1);
    private static final AtomicInteger X_RATE_LIMIT_INTERVAL_SECONDS = new AtomicInteger(-1);
    private static final AtomicReference<LocalDateTime> TIME_OF_LAST_REQUEST = new AtomicReference<>(LocalDateTime.now());

    /**
     * Constant for the Jira REST API which is set to
     * <a href="https://jira.muenchen.de/rest/api/2/">https://jira.muenchen.de/rest/api/2/</a>
     */
    public static final String JIRA_REST_API_URL = "https://jira.muenchen.de/rest/api/2/";

    /**
     * Constant for the Jira REST API which is set to
     * <a href="https://jira.muenchen.de/rest/raven/1.0/">https://jira.muenchen.de/rest/raven/1.0/</a>
     */
    public static final String JIRA_XRAY_REST_API_URL = "https://jira.muenchen.de/rest/raven/1.0/";

    /**
     * Default constructor for JiraClient, using no proxy or specific PAC URL.
     */
    public JiraClient() {
        super();
    }

    /**
     * Constructor for JiraClient that uses a PAC (Proxy Auto-Config) URL and a target URL.
     *
     * @param pacUrl The PAC URL to configure the proxy settings.
     * @param targetUrl The target URL to connect to.
     */
    public JiraClient(String pacUrl, String targetUrl) {
        super(pacUrl, targetUrl);
    }

    /**
     * Constructor for JiraClient that uses a proxy hostname and port.
     *
     * @param proxyHostname The proxy server hostname.
     * @param proxyPort The proxy server port.
     */
    public JiraClient(String proxyHostname, int proxyPort) {
        super(proxyHostname, proxyPort);
    }

    /**
     * Retrieves the current value of the X-RateLimit-Remaining header.
     *
     * @return The number of remaining API calls before the rate limit is exceeded.
     */
    public static int getXRateLimitRemaining() {
        ScenarioLogManager.getLogger().debug("X-RateLimit-Remaining: {}", X_RATE_LIMIT_REMAINING.get());
        return X_RATE_LIMIT_REMAINING.get();
    }

    /**
     * Checks if the time since the last request is within the rate limit interval. If enough time has
     * passed, it updates the X-RateLimit-Remaining based on the
     * fill rate.
     *
     * @return {@code true} if the time since the last request is before the rate limit interval;
     *         {@code false} otherwise.
     */
    public static boolean isTimeSinceLastRequestBeforeInterval() {
        ScenarioLogManager.getLogger().debug("Time since last is before interval: {}",
                LocalDateTime.now().isAfter(TIME_OF_LAST_REQUEST.get().plusSeconds(X_RATE_LIMIT_INTERVAL_SECONDS.get())));
        if (LocalDateTime.now().isAfter(TIME_OF_LAST_REQUEST.get().plusSeconds(X_RATE_LIMIT_INTERVAL_SECONDS.get()))) {
            X_RATE_LIMIT_REMAINING.getAndAdd(X_RATE_LIMIT_FILL_RATE.get());
            return false;
        } else {
            return true;
        }
    }

    /**
     * Estimates the time in milliseconds until the next tokens arrive, based on the rate limit headers.
     *
     * @return The estimated time in milliseconds until the next tokens are available.
     */
    public static long getEstimatedTimeInMillisUntilNextTokensArrive() {
        long secondsSinceLastRequest = LocalDateTime.now().toEpochSecond(ZoneOffset.UTC) - TIME_OF_LAST_REQUEST.get().toEpochSecond(ZoneOffset.UTC);
        long estimatedTimeInMillisUntilNextTokensArrive;
        if (secondsSinceLastRequest < X_RATE_LIMIT_INTERVAL_SECONDS.get()) {
            estimatedTimeInMillisUntilNextTokensArrive = (X_RATE_LIMIT_INTERVAL_SECONDS.get() - secondsSinceLastRequest) * 1000L;
        } else {
            estimatedTimeInMillisUntilNextTokensArrive = Math.max((X_RATE_LIMIT_INTERVAL_SECONDS.get() * 1000L), RETRY_AFTER.get());
        }
        ScenarioLogManager.getLogger()
                .debug("Estimated time in ms until next {} token/s arrive: {}", X_RATE_LIMIT_FILL_RATE.get(), estimatedTimeInMillisUntilNextTokensArrive);
        return estimatedTimeInMillisUntilNextTokensArrive;
    }

    /**
     * Updates the rate limit information based on the headers returned from a Jira API response.
     *
     * @param headers The array of headers from the HTTP response.
     */
    public static void updateRateLimitHeaders(Header[] headers) {
        TIME_OF_LAST_REQUEST.set(LocalDateTime.now());
        boolean isXRateLimitRemainingUpdated = false;
        boolean isRetryAfterUpdated = false;
        boolean isXRateLimitFillRateUpdated = false;
        boolean isXRateLimitIntervalSecondsUpdated = false;
        for (Header header : headers) {
            ScenarioLogManager.getLogger().debug("Header: {}:{}", header.getName(), header.getValue());
            if (isXRateLimitRemainingUpdated && isRetryAfterUpdated && isXRateLimitFillRateUpdated && isXRateLimitIntervalSecondsUpdated) {
                break;
            }
            switch (header.getName()) {
                case "X-RateLimit-Remaining":
                    try {
                        X_RATE_LIMIT_REMAINING.set(Integer.parseInt(header.getValue()));
                    } catch (NumberFormatException e) {
                        ScenarioLogManager.getLogger()
                                .error("NumberFormatException caught! Header value for \"X-RateLimit-Remaining\" is no integer: {}", header.getValue());
                        X_RATE_LIMIT_REMAINING.set(0);
                    }
                    ScenarioLogManager.getLogger().debug("X-RateLimit-Remaining: {}", X_RATE_LIMIT_REMAINING.get());
                    isXRateLimitRemainingUpdated = true;
                    break;
                case "Retry-After":
                    try {
                        RETRY_AFTER.set((Long.parseLong(header.getValue()) * 1000) + 5000L);
                    } catch (NumberFormatException e) {
                        ScenarioLogManager.getLogger()
                                .error("NumberFormatException caught! Header value for \"Retry-After\" is no long: {}", header.getValue());
                        RETRY_AFTER.set(5000L);
                    }
                    ScenarioLogManager.getLogger().debug("Retry-After: {}", RETRY_AFTER.get());
                    isRetryAfterUpdated = true;
                    break;
                case "X-RateLimit-FillRate":
                    try {
                        X_RATE_LIMIT_FILL_RATE.set(Integer.parseInt(header.getValue()));
                    } catch (NumberFormatException e) {
                        ScenarioLogManager.getLogger()
                                .error("NumberFormatException caught! Header value for \"X-RateLimit-FillRate\" is no integer: {}", header.getValue());
                        X_RATE_LIMIT_FILL_RATE.set(1);
                    }
                    ScenarioLogManager.getLogger().debug("X-RateLimit-FillRate: {}", X_RATE_LIMIT_FILL_RATE.get());
                    isXRateLimitFillRateUpdated = true;
                    break;
                case "X-RateLimit-Interval-Seconds":
                    try {
                        X_RATE_LIMIT_INTERVAL_SECONDS.set(Integer.parseInt(header.getValue()));
                    } catch (NumberFormatException e) {
                        ScenarioLogManager.getLogger()
                                .error("NumberFormatException caught! Header value for \"X-RateLimit-Interval-Seconds\" is no integer: {}", header.getValue());
                        X_RATE_LIMIT_INTERVAL_SECONDS.set(30);
                    }
                    ScenarioLogManager.getLogger().debug("X-RateLimit-Interval-Seconds: {}", X_RATE_LIMIT_INTERVAL_SECONDS.get());
                    isXRateLimitIntervalSecondsUpdated = true;
                    break;
            }
        }
    }

    /**
     * Retrieves the username of the currently logged-in user from the JIRA REST API.
     *
     * <p>
     * This method sends an HTTP GET request to the JIRA REST API endpoint `myself` to fetch the details
     * of the authenticated user. It expects a valid
     * authentication method to be provided by {@link AuthenticationHelper#getAuthenticationMethod()}
     * and checks if the request returns a status code of 200
     * (OK). If the response is successful, the method parses the JSON response to extract and return
     * the "name" attribute of the user.
     * </p>
     *
     * @return the username of the currently logged-in user
     * @throws IllegalStateException if the response from the JIRA REST API is null or the status code
     *             is not 200
     */
    public String getCurrentlyLoggedInUserName() {
        String jsonResult = executeHttpGetRequest(JIRA_REST_API_URL + "myself", AuthenticationHelper.getAuthenticationMethod());
        CustomAssertions.assertNotNull(jsonResult, "HTTP GET request \"" + JIRA_REST_API_URL + "myself\" returned an empty result!");
        CustomAssertions.assertEquals(getLastRequestStatusCode(), 200, jsonResult);
        Gson gson = new Gson();
        JsonObject jsonObject = gson.fromJson(jsonResult, JsonObject.class);
        return jsonObject.get("name").getAsString();
    }
}
