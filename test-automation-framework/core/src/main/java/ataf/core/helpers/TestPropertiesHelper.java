package ataf.core.helpers;

import ataf.core.logging.ScenarioLogManager;
import ataf.core.properties.TestProperties;

import java.util.Optional;

/**
 * Helper class for retrieving typed property values from a configuration source. Supports various
 * primitive types and provides default values when properties
 * are not found or are not mandatory.
 *
 * <p>
 * Each method logs a warning if a requested property is missing and provides a fallback default
 * value.
 * </p>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class TestPropertiesHelper {
    /**
     * Retrieves a boolean property by name.
     *
     * @param propertyName the name of the property to retrieve
     * @param isMandatory whether the property is required
     * @param defaultValue the default value to use if the property is not found
     * @return the boolean value of the property, or {@code false} if not found
     */
    public static boolean getPropertyAsBoolean(String propertyName, boolean isMandatory, boolean defaultValue) {
        Optional<Boolean> optionalBoolean = TestProperties.getProperty(propertyName, isMandatory, defaultValue);
        if (optionalBoolean.isPresent()) {
            return optionalBoolean.get();
        } else {
            ScenarioLogManager.getLogger().warn("Property [{}] not found! Value will be set to [false]", propertyName);
            return false;
        }
    }

    /**
     * Retrieves a boolean property by name, without specifying a default value.
     *
     * @param propertyName the name of the property to retrieve
     * @param isMandatory whether the property is required
     * @return the boolean value of the property, or {@code false} if not found
     */
    public static boolean getPropertyAsBoolean(String propertyName, boolean isMandatory) {
        return getPropertyAsBoolean(propertyName, isMandatory, false);
    }

    /**
     * Retrieves a boolean property by name, assuming it is not mandatory.
     *
     * @param propertyName the name of the property to retrieve
     * @return the boolean value of the property, or {@code false} if not found
     */
    public static boolean getPropertyAsBoolean(String propertyName) {
        return getPropertyAsBoolean(propertyName, false, false);
    }

    /**
     * Retrieves a byte property by name.
     *
     * @param propertyName the name of the property to retrieve
     * @param isMandatory whether the property is required
     * @param defaultValue the default value to use if the property is not found
     * @return the byte value of the property, or {@code -1} if not found
     */
    public static byte getPropertyAsByte(String propertyName, boolean isMandatory, byte defaultValue) {
        Optional<Byte> optionalByte = TestProperties.getProperty(propertyName, isMandatory, defaultValue);
        if (optionalByte.isPresent()) {
            return optionalByte.get();
        } else {
            ScenarioLogManager.getLogger().warn("Property [{}] not found! Value will be set to [-1]", propertyName);
            return (byte) -1;
        }
    }

    /**
     * Retrieves a byte property by name, without specifying a default value.
     *
     * @param propertyName the name of the property to retrieve
     * @param isMandatory whether the property is required
     * @return the byte value of the property, or {@code -1} if not found
     */
    public static byte getPropertyAsByte(String propertyName, boolean isMandatory) {
        return getPropertyAsByte(propertyName, isMandatory, (byte) -1);
    }

    /**
     * Retrieves a byte property by name, assuming it is not mandatory.
     *
     * @param propertyName the name of the property to retrieve
     * @return the byte value of the property, or {@code -1} if not found
     */
    public static byte getPropertyAsByte(String propertyName) {
        return getPropertyAsByte(propertyName, false, (byte) -1);
    }

    /**
     * Retrieves a short property by name.
     *
     * @param propertyName the name of the property to retrieve
     * @param isMandatory whether the property is required
     * @param defaultValue the default value to use if the property is not found
     * @return the short value of the property, or {@code -1} if not found
     */
    public static short getPropertyAsShort(String propertyName, boolean isMandatory, short defaultValue) {
        Optional<Short> optionalShort = TestProperties.getProperty(propertyName, isMandatory, defaultValue);
        if (optionalShort.isPresent()) {
            return optionalShort.get();
        } else {
            ScenarioLogManager.getLogger().warn("Property [{}] not found! Value will be set to [-1]", propertyName);
            return (short) -1;
        }
    }

    /**
     * Retrieves a short property by name, without specifying a default value.
     *
     * @param propertyName the name of the property to retrieve
     * @param isMandatory whether the property is required
     * @return the short value of the property, or {@code -1} if not found
     */
    public static short getPropertyAsShort(String propertyName, boolean isMandatory) {
        return getPropertyAsShort(propertyName, isMandatory, (short) -1);
    }

    /**
     * Retrieves a short property by name, assuming it is not mandatory.
     *
     * @param propertyName the name of the property to retrieve
     * @return the short value of the property, or {@code -1} if not found
     */
    public static short getPropertyAsShort(String propertyName) {
        return getPropertyAsShort(propertyName, false, (short) -1);
    }

    /**
     * Retrieves an integer property by name.
     *
     * @param propertyName the name of the property to retrieve
     * @param isMandatory whether the property is required
     * @param defaultValue the default value to use if the property is not found
     * @return the integer value of the property, or {@code -1} if not found
     */
    public static int getPropertyAsInteger(String propertyName, boolean isMandatory, int defaultValue) {
        Optional<Integer> optionalInteger = TestProperties.getProperty(propertyName, isMandatory, defaultValue);
        if (optionalInteger.isPresent()) {
            return optionalInteger.get();
        } else {
            ScenarioLogManager.getLogger().warn("Property [{}] not found! Value will be set to [-1]", propertyName);
            return -1;
        }
    }

    /**
     * Retrieves an integer property by name, without specifying a default value.
     *
     * @param propertyName the name of the property to retrieve
     * @param isMandatory whether the property is required
     * @return the integer value of the property, or {@code -1} if not found
     */
    public static int getPropertyAsInteger(String propertyName, boolean isMandatory) {
        return getPropertyAsInteger(propertyName, isMandatory, -1);
    }

    /**
     * Retrieves an integer property by name, assuming it is not mandatory.
     *
     * @param propertyName the name of the property to retrieve
     * @return the integer value of the property, or {@code -1} if not found
     */
    public static int getPropertyAsInteger(String propertyName) {
        return getPropertyAsInteger(propertyName, false, -1);
    }

    /**
     * Retrieves a long property by name.
     *
     * @param propertyName the name of the property to retrieve
     * @param isMandatory whether the property is required
     * @param defaultValue the default value to use if the property is not found
     * @return the long value of the property, or {@code -1L} if not found
     */
    public static long getPropertyAsLong(String propertyName, boolean isMandatory, long defaultValue) {
        Optional<Long> optionalLong = TestProperties.getProperty(propertyName, isMandatory, defaultValue);
        if (optionalLong.isPresent()) {
            return optionalLong.get();
        } else {
            ScenarioLogManager.getLogger().warn("Property [{}] not found! Value will be set to [-1]", propertyName);
            return -1L;
        }
    }

    /**
     * Retrieves a long property by name, without specifying a default value.
     *
     * @param propertyName the name of the property to retrieve
     * @param isMandatory whether the property is required
     * @return the long value of the property, or {@code -1L} if not found
     */
    public static long getPropertyAsLong(String propertyName, boolean isMandatory) {
        return getPropertyAsLong(propertyName, isMandatory, -1L);
    }

    /**
     * Retrieves a long property by name, assuming it is not mandatory.
     *
     * @param propertyName the name of the property to retrieve
     * @return the long value of the property, or {@code -1L} if not found
     */
    public static long getPropertyAsLong(String propertyName) {
        return getPropertyAsLong(propertyName, false, -1L);
    }

    /**
     * Retrieves a float property by name.
     *
     * @param propertyName the name of the property to retrieve
     * @param isMandatory whether the property is required
     * @param defaultValue the default value to use if the property is not found
     * @return the float value of the property, or {@code -1.0F} if not found
     */
    public static float getPropertyAsFloat(String propertyName, boolean isMandatory, float defaultValue) {
        Optional<Float> optionalFloat = TestProperties.getProperty(propertyName, isMandatory, defaultValue);
        if (optionalFloat.isPresent()) {
            return optionalFloat.get();
        } else {
            ScenarioLogManager.getLogger().warn("Property [{}] not found! Value will be set to [-1.0]", propertyName);
            return (float) -1.0;
        }
    }

    /**
     * Retrieves a float property by name, without specifying a default value.
     *
     * @param propertyName the name of the property to retrieve
     * @param isMandatory whether the property is required
     * @return the float value of the property, or {@code -1.0F} if not found
     */
    public static float getPropertyAsFloat(String propertyName, boolean isMandatory) {
        return getPropertyAsFloat(propertyName, isMandatory, (float) -1.0);
    }

    /**
     * Retrieves a float property by name, assuming it is not mandatory.
     *
     * @param propertyName the name of the property to retrieve
     * @return the float value of the property, or {@code -1.0F} if not found
     */
    public static float getPropertyAsFloat(String propertyName) {
        return getPropertyAsFloat(propertyName, false, (float) -1.0);
    }

    /**
     * Retrieves a double property by name.
     *
     * @param propertyName the name of the property to retrieve
     * @param isMandatory whether the property is required
     * @param defaultValue the default value to use if the property is not found
     * @return the double value of the property, or {@code -1.0} if not found
     */
    public static double getPropertyAsDouble(String propertyName, boolean isMandatory, double defaultValue) {
        Optional<Double> optionalDouble = TestProperties.getProperty(propertyName, isMandatory, defaultValue);
        if (optionalDouble.isPresent()) {
            return optionalDouble.get();
        } else {
            ScenarioLogManager.getLogger().warn("Property [{}] not found! Value will be set to [-1.0]", propertyName);
            return -1.0;
        }
    }

    /**
     * Retrieves a double property by name, without specifying a default value.
     *
     * @param propertyName the name of the property to retrieve
     * @param isMandatory whether the property is required
     * @return the double value of the property, or {@code -1.0} if not found
     */
    public static double getPropertyAsDouble(String propertyName, boolean isMandatory) {
        return getPropertyAsDouble(propertyName, isMandatory, -1.0);
    }

    /**
     * Retrieves a double property by name, assuming it is not mandatory.
     *
     * @param propertyName the name of the property to retrieve
     * @return the double value of the property, or {@code -1.0} if not found
     */
    public static double getPropertyAsDouble(String propertyName) {
        return getPropertyAsDouble(propertyName, false, -1.0);
    }

    /**
     * Retrieves a string property by name.
     *
     * @param propertyName the name of the property to retrieve
     * @param isMandatory whether the property is required
     * @param defaultValue the default value to use if the property is not found
     * @return the string value of the property, or an empty string if not found
     */
    public static String getPropertyAsString(String propertyName, boolean isMandatory, String defaultValue) {
        Optional<String> optionalString = TestProperties.getProperty(propertyName, isMandatory, defaultValue);
        if (optionalString.isPresent()) {
            return optionalString.get();
        } else {
            ScenarioLogManager.getLogger().warn("Property [{}] not found! Value will be set to []", propertyName);
            return "";
        }
    }

    /**
     * Retrieves a string property by name, without specifying a default value.
     *
     * @param propertyName the name of the property to retrieve
     * @param isMandatory whether the property is required
     * @return the string value of the property, or an empty string if not found
     */
    public static String getPropertyAsString(String propertyName, boolean isMandatory) {
        return getPropertyAsString(propertyName, isMandatory, "");
    }

    /**
     * Retrieves a string property by name, assuming it is not mandatory.
     *
     * @param propertyName the name of the property to retrieve
     * @return the string value of the property, or an empty string if not found
     */
    public static String getPropertyAsString(String propertyName) {
        return getPropertyAsString(propertyName, false, "");
    }
}
