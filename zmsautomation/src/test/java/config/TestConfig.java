package config;

/**
 * Centralized configuration management for API tests.
 * Reads configuration from system properties, environment variables, or defaults.
 */
public final class TestConfig {

    private TestConfig() {
        // Utility class - prevent instantiation
    }

    /**
     * Get configuration value from system property, environment variable, or default.
     * Priority: System property > Environment variable > Default value
     *
     * @param propertyKey System property key
     * @param envKey Environment variable key
     * @param defaultValue Default value if neither property nor env var is set
     * @return Configuration value
     */
    private static String getConfigValue(String propertyKey, String envKey, String defaultValue) {
        String value = System.getProperty(propertyKey);
        if (value != null && !value.isEmpty()) {
            return value;
        }
        value = System.getenv(envKey);
        if (value != null && !value.isEmpty()) {
            return value;
        }
        return defaultValue;
    }

    /**
     * Base URI for ZMS API (api/2 endpoints).
     * Default: http://zms-web/terminvereinbarung/api/2
     */
    public static String getBaseUri() {
        return getConfigValue(
            "BASE_URI",
            "BASE_URI",
            "http://zms-web/terminvereinbarung/api/2"
        );
    }

    /**
     * Base URI for Citizen API endpoints.
     * Default: http://zms-web/terminvereinbarung/api/citizen
     */
    public static String getCitizenApiBaseUri() {
        return getConfigValue(
            "CITIZEN_API_BASE_URI",
            "CITIZEN_API_BASE_URI",
            "http://zms-web/terminvereinbarung/api/citizen"
        );
    }

    /**
     * Request timeout in milliseconds.
     * Default: 30000 (30 seconds)
     */
    public static int getRequestTimeout() {
        String timeout = getConfigValue("REQUEST_TIMEOUT", "REQUEST_TIMEOUT", "30000");
        try {
            return Integer.parseInt(timeout);
        } catch (NumberFormatException e) {
            return 30000;
        }
    }

    /**
     * Enable request/response logging.
     * Default: false
     */
    public static boolean isLoggingEnabled() {
        String logging = getConfigValue("ENABLE_LOGGING", "ENABLE_LOGGING", "false");
        return Boolean.parseBoolean(logging);
    }

    /**
     * Authentication token (if required).
     * Default: null (no authentication)
     */
    public static String getAuthToken() {
        return getConfigValue("AUTH_TOKEN", "AUTH_TOKEN", null);
    }
}
