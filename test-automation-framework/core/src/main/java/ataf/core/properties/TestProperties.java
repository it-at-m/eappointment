package ataf.core.properties;

import ataf.core.logging.ScenarioLogManager;

import java.util.Map;
import java.util.Optional;
import java.util.Properties;
import java.util.Set;
import java.util.concurrent.ConcurrentHashMap;
import java.util.regex.Matcher;
import java.util.regex.Pattern;
import java.util.stream.Collectors;

/**
 * The TestProperties class manages the loading and retrieval of test properties from property files
 * and system properties. It also handles the conversion of
 * properties to their appropriate types and provides utilities for managing property files.
 *
 * <p>
 * Properties are loaded during the static initialization block and can be accessed through various
 * methods. The properties are categorized by their prefixes
 * and types.
 * </p>
 *
 * <p>
 * Additionally, this class defines custom exceptions for handling property loading errors and
 * mandatory property checks.
 * </p>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class TestProperties {
    private static final Map<String, TestProperty<?>> TEST_PROPERTIES_MAP = new ConcurrentHashMap<>();

    static {
        ScenarioLogManager.getLogger().info(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>Start of loading properties>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>");

        // Get all system properties before property files are applied
        Properties customProperties = new Properties();
        customProperties.putAll(System.getProperties());

        // Load properties files
        loadPropertyFile("cucumber.properties", false);
        loadPropertyFile("jira.properties", false);
        loadPropertyFile("testautomation.properties", true);

        // Load defaults
        Properties defaultProperties = System.getProperties();
        loadProperties(defaultProperties);

        // Overwrite defaults with CLI parameters
        Properties finalProperties = customProperties.entrySet().stream()
                .filter(entry -> !defaultProperties.containsKey(entry.getKey()) ||
                        !entry.getValue().equals(defaultProperties.get(entry.getKey())))
                .collect(Collectors.toMap(
                        Map.Entry::getKey,
                        Map.Entry::getValue,
                        (e1, e2) -> e1,
                        Properties::new));

        loadProperties(finalProperties);

        ScenarioLogManager.getLogger().info("<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<Finished loading of properties<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");
    }

    /**
     * Adds or replaces a property in the property map.
     *
     * @param <T> the type of the property value.
     * @param propertyName the name of the property to add or replace.
     * @param testProperty the property object to be added to the map.
     * @return {@code true} if the property already existed and was replaced, {@code false} if it was
     *         newly added.
     */
    static <T> boolean put(String propertyName, TestProperty<T> testProperty) {
        if (TEST_PROPERTIES_MAP.containsKey(propertyName)) {
            TEST_PROPERTIES_MAP.put(propertyName, testProperty);
            return true;
        } else {
            TEST_PROPERTIES_MAP.put(propertyName, testProperty);
            return false;
        }
    }

    /**
     * Checks if a property with the specified name was overwritten.
     *
     * @param propertyName the name of the property to check.
     * @return {@code true} if the property exists in the map and was overwritten; {@code false} if the
     *         property does not exist or was not overwritten.
     */
    public static boolean isOverwritten(String propertyName) {
        TestProperty<?> testProperty = TEST_PROPERTIES_MAP.get(propertyName);
        if (testProperty != null) {
            return testProperty.overwritten();
        }
        return false;
    }

    /**
     * Loads properties from a {@link Properties} object into the test property map. Only properties
     * with names matching specific patterns (e.g., "cucumber",
     * "jira", "taf", "testautomation") are loaded, and these may be further categorized by type (e.g.,
     * "boolean", "int", "string").
     * <p>
     * Properties identified as secret (e.g., containing "password" or "token" in the name) are logged
     * without their values to ensure security.
     *
     * @param properties the {@link Properties} object containing the properties to load.
     */
    private static void loadProperties(Properties properties) {
        // Get the set of property names and filter which ones are relevant test properties
        Set<String> propertyNames = properties.stringPropertyNames().stream()
                .filter(s -> s.matches("^(cucumber|jira|taf|testautomation)\\..*$"))
                .collect(Collectors.toSet());

        // Iterate over the property names and add each property to the map
        Pattern propertyNamePattern = Pattern.compile(
                "^(cucumber|jira|taf|testautomation)\\.((boolean|byte|short|int|long|float|double|string)\\.)?((secret)\\.)?(.*)$");
        for (String propertyName : propertyNames) {
            String propertyValue = properties.getProperty(propertyName);
            Matcher propertyNameMatcher = propertyNamePattern.matcher(propertyName);
            if (propertyNameMatcher.find()) {
                propertyName = propertyNameMatcher.group(6);
                boolean propertyIsSecret = ("secret").equals(propertyNameMatcher.group(5)) || propertyName.matches(".*([Pp]asswor[dt]|[Tt]oken).*");
                try {
                    String propertyType = propertyNameMatcher.group(3) == null ? "string" : propertyNameMatcher.group(3);
                    TestProperty<?> testProperty = switch (propertyType) {
                        case "boolean" -> new TestProperty<>(propertyName, Boolean.parseBoolean(propertyValue));
                        case "byte" -> new TestProperty<>(propertyName, Byte.parseByte(propertyValue));
                        case "short" -> new TestProperty<>(propertyName, Short.parseShort(propertyValue));
                        case "int" -> new TestProperty<>(propertyName, Integer.parseInt(propertyValue));
                        case "long" -> new TestProperty<>(propertyName, Long.parseLong(propertyValue));
                        case "float" -> new TestProperty<>(propertyName, Float.parseFloat(propertyValue));
                        case "double" -> new TestProperty<>(propertyName, Double.parseDouble(propertyValue));
                        default -> new TestProperty<>(propertyName, propertyValue);
                    };
                    if (propertyIsSecret) {
                        if (testProperty.overwritten()) {
                            ScenarioLogManager.getLogger().info("({}) Property [{}] has been successfully overwritten!", propertyType, propertyName);
                        } else {
                            ScenarioLogManager.getLogger().info("({}) Property [{}] has been successfully loaded!", propertyType, propertyName);
                        }
                    } else {
                        if (testProperty.overwritten()) {
                            ScenarioLogManager.getLogger()
                                    .info("({}) Property [{}] with value [{}] has been successfully overwritten!", propertyType, propertyName, propertyValue);
                        } else {
                            ScenarioLogManager.getLogger()
                                    .info("({}) Property [{}] with value [{}] has been successfully loaded!", propertyType, propertyName, propertyValue);
                        }
                    }
                } catch (Exception e) {
                    ScenarioLogManager.getLogger().error("Could not load property \"{}\"", propertyName, e);
                }
            }
        }
    }

    /**
     * Loads a property file and sets its properties to the system properties.
     *
     * @param fileName the name of the property file to load.
     * @param mandatory if true, an exception will be thrown if the file cannot be loaded.
     */
    public static void loadPropertyFile(String fileName, boolean mandatory) {
        try {
            Properties sysProperties = new Properties();
            sysProperties.load(TestProperties.class.getResourceAsStream("/" + fileName));

            Set<String> keys = sysProperties.stringPropertyNames();
            for (String key : keys) {
                String value = sysProperties.getProperty(key);
                System.setProperty(key, value);
            }
        } catch (Exception e) {
            if (mandatory) {
                throw new PropertyFileLoadException("Mandatory property file \"" + fileName + "\" could not be loaded!", e);
            }
        }
    }

    /**
     * Extracts a specific path from a comma-separated string based on the provided prefix.
     *
     * @param input the input string containing comma-separated values.
     * @param prefix the prefix to search for.
     * @return the extracted path associated with the given prefix.
     * @throws IllegalArgumentException if the prefix is not found in the input string.
     */
    public static String extractPath(String input, String prefix) {
        for (String part : input.split(",")) {
            if (part.startsWith(prefix + ":")) {
                return part.substring((prefix + ":").length());
            }
        }
        throw new IllegalArgumentException("Prefix '" + prefix + "' not found in input string.");
    }

    /**
     * Retrieves a known property.
     *
     * @param <T> the type of the value associated with the property.
     * @param propertyName the known name of the property.
     * @param isMandatory if true, will check if the desired property is not null; throws
     *            MandatoryPropertyNullException if it is null.
     * @param defaultValue an optional default value to use if the property is not set; if provided,
     *            this value will be added to the property map as a new
     *            TestProperty.
     * @return an Optional containing the property value if it exists; otherwise, empty.
     * @throws MandatoryPropertyNullException if the property is mandatory and has no value.
     * @throws IllegalArgumentException if the property value cannot be cast to the desired type.
     */
    @SuppressWarnings("unchecked")
    public static <T> Optional<T> getProperty(String propertyName, boolean isMandatory, T defaultValue) {
        TestProperty<?> testProperty = TEST_PROPERTIES_MAP.get(propertyName);
        if (testProperty == null && defaultValue != null) {
            testProperty = new TestProperty<>(propertyName, defaultValue);
        }
        if (isMandatory && testProperty == null) {
            throw new MandatoryPropertyNullException("Got null value for mandatory property [" + propertyName + "]");
        }
        if (testProperty != null) {
            try {
                return Optional.ofNullable((T) testProperty.value());
            } catch (ClassCastException e) {
                throw new IllegalArgumentException("Property [" + propertyName + "] cannot be cast to the desired type.", e);
            }
        }
        return Optional.empty();
    }

    /**
     * Retrieves a known property.
     *
     * @param <T> the type of the value associated with the property.
     * @param propertyName the known name of the property.
     * @param isMandatory if true, will check if the desired property is not null; throws
     *            MandatoryPropertyNullException if it is null.
     * @param defaultValue an optional default value to return in case the property is null.
     * @return an Optional containing the property value if it exists; otherwise, empty.
     * @throws MandatoryPropertyNullException if the property is mandatory and has no value.
     * @throws IllegalArgumentException if the property value cannot be cast to the desired type.
     * @deprecated This method is deprecated due to a new constructor for TestProperty. Use
     *             {@link #getProperty(String, boolean, Object)} instead, which
     *             directly accepts a default value of the specified type.
     */
    @Deprecated
    @SuppressWarnings("unchecked")
    public static <T> Optional<T> getProperty(String propertyName, boolean isMandatory, TestProperty<T> defaultValue) {
        TestProperty<?> testProperty = TEST_PROPERTIES_MAP.get(propertyName);
        if (testProperty == null && defaultValue != null) {
            testProperty = defaultValue;
        }
        if (isMandatory && testProperty == null) {
            throw new MandatoryPropertyNullException("Got null value for mandatory property [" + propertyName + "]");
        }
        if (testProperty != null) {
            try {
                return Optional.ofNullable((T) testProperty.value());
            } catch (ClassCastException e) {
                throw new IllegalArgumentException("Property [" + propertyName + "] cannot be cast to the desired type.", e);
            }
        }
        return Optional.empty();
    }

    /**
     * Retrieves a known property.
     *
     * @param <T> the type of the value associated with the property
     * @param propertyName the known name of the property.
     * @param isMandatory if true, will check if the desired property is not null; throws
     *            MandatoryPropertyNullException if it is null.
     * @return an Optional containing the property value if it exists; otherwise, empty.
     */
    public static <T> Optional<T> getProperty(String propertyName, boolean isMandatory) {
        return getProperty(propertyName, isMandatory, null);
    }

    /**
     * Retrieves a known property.
     *
     * @param <T> the type of the value associated with the property
     * @param propertyName the known name of the property.
     * @return an Optional containing the property value if it exists; otherwise, empty.
     */
    public static <T> Optional<T> getProperty(String propertyName) {
        return getProperty(propertyName, false, null);
    }

    /**
     * Exception used for errors when the property files cannot be loaded.
     */
    public static class PropertyFileLoadException extends RuntimeException {
        /**
         * @param message the exception message.
         */
        public PropertyFileLoadException(String message) {
            super(message);
        }

        /**
         * @param message the exception message.
         * @param cause the cause of the exception.
         */
        public PropertyFileLoadException(String message, Throwable cause) {
            super(message, cause);
        }

        /**
         * @param cause the cause of the exception.
         */
        public PropertyFileLoadException(Throwable cause) {
            super(cause);
        }
    }

    /**
     * Exception for mandatory properties with null values.
     */
    public static class MandatoryPropertyNullException extends RuntimeException {
        /**
         * @param message the exception message.
         */
        public MandatoryPropertyNullException(String message) {
            super(message);
        }

        /**
         * @param message the exception message.
         * @param cause the cause of the exception.
         */
        public MandatoryPropertyNullException(String message, Throwable cause) {
            super(message, cause);
        }

        /**
         * @param cause the cause of the exception.
         */
        public MandatoryPropertyNullException(Throwable cause) {
            super(cause);
        }
    }
}
