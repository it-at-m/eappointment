package ataf.core.clients;

import ataf.core.logging.ScenarioLogManager;
import org.apache.hc.core5.http.Header;

import java.time.LocalDateTime;
import java.time.ZoneOffset;
import java.util.concurrent.atomic.AtomicInteger;
import java.util.concurrent.atomic.AtomicLong;
import java.util.concurrent.atomic.AtomicReference;

/**
 * ConfluenceClient is a specialized HTTP client for interacting with the Confluence REST API. It
 * manages rate-limiting headers and provides methods to check
 * and update rate limit values.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class ConfluenceClient extends HttpClient {
    private static final AtomicInteger X_RATE_LIMIT_REMAINING = new AtomicInteger(-1);
    private static final AtomicLong RETRY_AFTER = new AtomicLong(5000L);
    private static final AtomicInteger X_RATE_LIMIT_FILL_RATE = new AtomicInteger(-1);
    private static final AtomicInteger X_RATE_LIMIT_INTERVAL_SECONDS = new AtomicInteger(-1);
    private static final AtomicReference<LocalDateTime> TIME_OF_LAST_REQUEST = new AtomicReference<>(LocalDateTime.now());

    /**
     * Default constructor for ConfluenceClient.
     */
    public ConfluenceClient() {
        super();
    }

    /**
     * Constructor for ConfluenceClient with PAC and target URL.
     *
     * @param pacUrl The Proxy Auto-Config (PAC) URL.
     * @param targetUrl The target URL for the Confluence instance.
     */
    public ConfluenceClient(String pacUrl, String targetUrl) {
        super(pacUrl, targetUrl);
    }

    /**
     * Retrieves the base URL for the Confluence REST API.
     *
     * @return The Confluence REST API base URL as a String.
     */
    public String getConfluenceRestApiUrl() {
        return "https://confluence.muenchen.de/rest/api/";
    }

    /**
     * Retrieves the remaining number of requests allowed before hitting the rate limit.
     *
     * @return The remaining number of requests as an integer.
     */
    public static int getXRateLimitRemaining() {
        ScenarioLogManager.getLogger().debug("X-RateLimit-Remaining: {}", X_RATE_LIMIT_REMAINING.get());
        return X_RATE_LIMIT_REMAINING.get();
    }

    /**
     * Checks if the time since the last request is before the interval allowed by the rate limit. If
     * sufficient time has passed, the remaining rate limit is
     * replenished.
     *
     * @return {@code true} if the time since the last request is before the interval, {@code false}
     *         otherwise.
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
     * Estimates the time in milliseconds until the next set of tokens for the rate limit becomes
     * available.
     *
     * @return The estimated time in milliseconds until the next tokens arrive.
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
     * Updates the rate limit values based on the provided HTTP headers.
     *
     * @param headers An array of HTTP headers from the Confluence API response.
     */
    public static void updateRateLimitHeaders(Header[] headers) {
        TIME_OF_LAST_REQUEST.set(LocalDateTime.now());
        boolean isXRateLimitRemainingUpdated = false;
        boolean isRetryAfterUpdated = false;
        boolean isXRateLimitFillRateUpdated = false;
        boolean isXRateLimitIntervalSecondsUpdated = false;
        for (Header header : headers) {
            if (isXRateLimitRemainingUpdated && isRetryAfterUpdated && isXRateLimitFillRateUpdated && isXRateLimitIntervalSecondsUpdated) {
                break;
            }
            switch (header.getName()) {
                case "X-RateLimit-Remaining":
                    try {
                        X_RATE_LIMIT_REMAINING.set(Integer.parseInt(header.getValue()));
                    } catch (NumberFormatException e) {
                        ScenarioLogManager.getLogger()
                                .error("NumberFormatException caught! Header value for \"X-RateLimit-Remaining\" is not an integer: {}", header.getValue());
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
                                .error("NumberFormatException caught! Header value for \"Retry-After\" is not a long: {}", header.getValue());
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
                                .error("NumberFormatException caught! Header value for \"X-RateLimit-FillRate\" is not an integer: {}", header.getValue());
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
                                .error("NumberFormatException caught! Header value for \"X-RateLimit-Interval-Seconds\" is not an integer: {}",
                                        header.getValue());
                        X_RATE_LIMIT_INTERVAL_SECONDS.set(30);
                    }
                    ScenarioLogManager.getLogger().debug("X-RateLimit-Interval-Seconds: {}", X_RATE_LIMIT_INTERVAL_SECONDS.get());
                    isXRateLimitIntervalSecondsUpdated = true;
                    break;
            }
        }
    }
}
