package ataf.core.helpers;

import ataf.core.assertions.CustomAssertions;
import ataf.core.logging.ScenarioLogManager;
import ataf.core.utils.CryptoUtils;
import ataf.core.utils.DateUtils;

import javax.crypto.AEADBadTagException;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.time.temporal.ChronoUnit;
import java.time.temporal.IsoFields;
import java.util.HashMap;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * Helper class for managing test data and transforming parameters. This class provides methods to
 * encrypt, decrypt, set, and retrieve test data, as well as to
 * handle dynamic date parameters within test data strings.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class TestDataHelper {
    private static final Map<String, String> SUITE_TEST_DATA_MAP = new ConcurrentHashMap<>();
    private static final Map<Long, HashMap<String, String>> TEST_DATA_MAP = new ConcurrentHashMap<>();
    private static final Pattern TODAY_PARAMETER_PATTERN = Pattern.compile(
            "<heute(?:_(tag|woche|monat|jahr|invertiert|uhrzeit|stunde|minute|sekunde|uhrzeit_invertiert))?(?:([+-]\\d+)_(tage|TAGE|tag|TAG|wochen|WOCHEN|woche|WOCHE|monate|MONATE|monat|MONAT|jahre|JAHRE|jahr|JAHR|stunden|STUNDEN|stunde|STUNDE|minuten|MINUTEN|minute|MINUTE|sekunden|SEKUNDEN|sekunde|SEKUNDE))?>");
    private static final Pattern TEST_DATA_PARAMETER_PATTERN = Pattern.compile("<((Suite)?TestData)\\.([\\p{L}\\w_]+)>");

    /**
     * Retrieves the suite test data associated with the given key.
     *
     * @param key The key of the suite test data to retrieve.
     * @return The decrypted suite test data value, or {@code null} if not found.
     */
    public static String getSuiteTestData(String key) {
        return decryptTestData(SUITE_TEST_DATA_MAP.get(key));
    }

    /**
     * Sets the suite test data for the given key. The value will be encrypted before being stored.
     *
     * @param key The key to associate with the test data.
     * @param value The test data value to store.
     */
    public static void setSuiteTestData(String key, String value) {
        SUITE_TEST_DATA_MAP.put(key, encryptTestData(value));
    }

    /**
     * Clears all suite test data.
     */
    public static void flushMapSuiteTestData() {
        SUITE_TEST_DATA_MAP.clear();
    }

    /**
     * Initializes the test data map for the current thread.
     */
    public static void initializeTestDataMap() {
        TEST_DATA_MAP.put(Thread.currentThread().getId(), new HashMap<>());
    }

    /**
     * Retrieves the test data associated with the given key for the current thread.
     *
     * @param key The key of the test data to retrieve.
     * @return The decrypted test data value, or {@code null} if not found.
     */
    public static String getTestData(String key) {
        return decryptTestData(TEST_DATA_MAP.get(Thread.currentThread().getId()).get(key));
    }

    /**
     * Sets the test data for the given key for the current thread. The value will be encrypted before
     * being stored.
     *
     * @param key The key to associate with the test data.
     * @param value The test data value to store.
     */
    public static void setTestData(String key, String value) {
        TEST_DATA_MAP.get(Thread.currentThread().getId()).put(key, encryptTestData(value));
    }

    /**
     * Clears all test data for the current thread.
     */
    public static void flushMapTestData() {
        TEST_DATA_MAP.get(Thread.currentThread().getId()).clear();
        TEST_DATA_MAP.remove(Thread.currentThread().getId());
    }

    /**
     * Encrypts the given test data string.
     *
     * @param testData The test data string to encrypt.
     * @return The encrypted test data string.
     */
    public static String encryptTestData(String testData) {
        try {
            testData = "VERSCHLÜSSELTES_DATUM_".concat(CryptoUtils.encrypt(testData));
        } catch (Exception e) {
            CustomAssertions.fail(e.getMessage(), e);
        }
        return testData;
    }

    /**
     * Decrypts the given test data string if it is in the encrypted format.
     *
     * @param testData The test data string to decrypt.
     * @return The decrypted test data string, or the original string if not encrypted.
     */
    public static String decryptTestData(String testData) {
        if (testData != null && testData.length() > 22 && testData.startsWith("VERSCHLÜSSELTES_DATUM_")) {
            try {
                testData = CryptoUtils.decrypt(testData.substring(22));
            } catch (AEADBadTagException e) {
                CustomAssertions.fail("Provided test data encryption password is wrong!", e);
            } catch (Exception e) {
                CustomAssertions.fail(e.getMessage(), e);
            }
        }
        return testData;
    }

    /**
     * Processes a date or time parameter found in a string and returns the formatted date/time.
     *
     * @param found The found date/time parameter string.
     * @return The formatted date or time as a string.
     */
    private static String processDateParameter(String found) {
        int offset = 0;
        ChronoUnit chronoUnit = null;

        if (found.contains("+") || found.contains("-")) {
            String[] parts = found.split("[+-]");
            String additional = parts[1];
            offset = Integer.parseInt((found.contains("+") ? "+" : "-") + additional.split("_")[0]);
            String unit = additional.split("_")[1].toUpperCase();

            chronoUnit = switch (unit.substring(0, unit.length() - 1)) {
                case "WOCHE", "WOCHEN" -> ChronoUnit.WEEKS;
                case "TAG", "TAGE" -> ChronoUnit.DAYS;
                case "MONAT", "MONATE" -> ChronoUnit.MONTHS;
                case "JAHR", "JAHRE" -> ChronoUnit.YEARS;
                case "STUNDE", "STUNDEN" -> ChronoUnit.HOURS;
                case "MINUTE", "MINUTEN" -> ChronoUnit.MINUTES;
                case "SEKUNDE", "SEKUNDEN" -> ChronoUnit.SECONDS;
                default -> throw new IllegalArgumentException("Unknown date/time parameter: " + found);
            };
            found = parts[0] + ">";
        }

        LocalDateTime dateTime = DateUtils.getDateTimeWithOffset(offset, chronoUnit);
        DateTimeFormatter formatter;

        return switch (found) {
            // Date Formats
            case "<heute>" -> {
                formatter = DateTimeFormatter.ofPattern("dd.MM.yyyy");
                yield formatter.format(dateTime);
            }
            case "<heute_tag>" -> {
                formatter = DateTimeFormatter.ofPattern("dd");
                yield formatter.format(dateTime);
            }
            case "<heute_woche>" -> String.valueOf(dateTime.get(IsoFields.WEEK_OF_WEEK_BASED_YEAR));
            case "<heute_monat>" -> {
                formatter = DateTimeFormatter.ofPattern("MM");
                yield formatter.format(dateTime);
            }
            case "<heute_jahr>" -> {
                formatter = DateTimeFormatter.ofPattern("yyyy");
                yield formatter.format(dateTime);
            }
            case "<heute_invertiert>" -> {
                formatter = DateTimeFormatter.ofPattern("yyyy.MM.dd");
                yield formatter.format(dateTime);
            }

            // Time Formats
            case "<heute_uhrzeit>" -> {
                formatter = DateTimeFormatter.ofPattern("HH:mm:ss");
                yield formatter.format(dateTime);
            }
            case "<heute_stunde>" -> {
                formatter = DateTimeFormatter.ofPattern("HH");
                yield formatter.format(dateTime);
            }
            case "<heute_minute>" -> {
                formatter = DateTimeFormatter.ofPattern("mm");
                yield formatter.format(dateTime);
            }
            case "<heute_sekunde>" -> {
                formatter = DateTimeFormatter.ofPattern("ss");
                yield formatter.format(dateTime);
            }
            case "<heute_uhrzeit_invertiert>" -> {
                formatter = DateTimeFormatter.ofPattern("HH.mm.ss");
                yield formatter.format(dateTime);
            }

            default -> found;
        };
    }

    /**
     * Retrieves the test data value based on its type and key.
     *
     * @param type The type of the test data (e.g., "SuiteTestData" or "TestData").
     * @param key The key of the test data to retrieve.
     * @return The corresponding test data value.
     */
    private static String getTestDataValue(String type, String key) {
        return type.equals("SuiteTestData") ? getSuiteTestData(key) : getTestData(key);
    }

    /**
     * Transforms parameters in a given string value by replacing dynamic date parameters and test data
     * parameters with their corresponding values.
     *
     * <div>This method processes two types of placeholders:
     * <ul>
     * <li><b>Dynamic Date & Time Parameters:</b> Recognizes placeholders such as {@code <heute>},
     * {@code <heute_tag>},
     * {@code <heute_woche>}, {@code <heute_monat>}, {@code <heute_jahr>}, and
     * {@code <heute_invertiert>} for date transformations.</li>
     * <li><b>Dynamic Time Parameters:</b> Supports placeholders like {@code <heute_uhrzeit>},
     * {@code <heute_stunde>},
     * {@code <heute_minute>}, and {@code <heute_sekunde>}, as well as their respective offset
     * variations (e.g., {@code <heute+2_stunden>}).</li>
     * <li><b>Test Data Parameters:</b> Matches test data references such as {@code <TestData.key>} and
     * {@code <SuiteTestData.key>}
     * and replaces them with corresponding test values.</li>
     * </ul>
     *
     * <p>
     * The method also handles offsets for both date and time units (e.g., {@code <heute+7_tage>},
     * {@code <heute-3_stunden>}).
     * If an unknown or malformed placeholder remains in the transformed string, a warning is logged to
     * assist in debugging.</div>
     *
     * @param parameterValue The input string containing placeholders to be transformed.
     * @return The transformed string with replaced parameters.
     * @throws IllegalArgumentException if a test data parameter cannot be resolved.
     */
    private static String transformParameters(String parameterValue) {
        String transformedParameter = parameterValue;

        // Transform dynamic date parameters
        Matcher todayParameterMatcher = TODAY_PARAMETER_PATTERN.matcher(transformedParameter);
        StringBuilder stringBuilder = new StringBuilder();

        int lastEnd = 0;
        while (todayParameterMatcher.find()) {
            stringBuilder.append(transformedParameter, lastEnd, todayParameterMatcher.start());
            try {
                String replacement = processDateParameter(todayParameterMatcher.group());
                stringBuilder.append(replacement);
            } catch (Exception e) {
                ScenarioLogManager.getLogger().error(
                        "Error converting date parameter to desired date. Please check the documentation. Value was {}. Error was: {}. See attached Stacktrace. Will continue using {}as String.",
                        transformedParameter, e.getMessage(), transformedParameter, e);
                stringBuilder.append(todayParameterMatcher.group());
            }
            lastEnd = todayParameterMatcher.end();
        }
        stringBuilder.append(transformedParameter.substring(lastEnd));
        transformedParameter = stringBuilder.toString();

        // Transform test data parameters
        Matcher testDataParameterMatcher = TEST_DATA_PARAMETER_PATTERN.matcher(transformedParameter);
        stringBuilder.delete(0, stringBuilder.length() - 1);
        stringBuilder.setLength(0);

        lastEnd = 0;
        while (testDataParameterMatcher.find()) {
            stringBuilder.append(transformedParameter, lastEnd, testDataParameterMatcher.start());
            String key = testDataParameterMatcher.group(3);
            String value = getTestDataValue(testDataParameterMatcher.group(1), key);
            if (value == null) {
                throw new IllegalArgumentException("For given " + (testDataParameterMatcher.group(1).equals("SuiteTestData") ? "suite test data" : "test data")
                        + " parameter \"" + key + "\" no value could be found!");
            } else {
                stringBuilder.append(value);
            }
            lastEnd = testDataParameterMatcher.end();
        }
        stringBuilder.append(transformedParameter.substring(lastEnd));
        transformedParameter = stringBuilder.toString();

        // Check for unprocessed placeholders and log a warning
        if (transformedParameter.matches(".*<[^<>]+>.*")) {
            ScenarioLogManager.getLogger().warn(
                    "Potentially incorrect placeholder detected in transformed string: '{}'. Make sure all placeholders are correctly formatted and match supported patterns. See for details: https://confluence.muenchen.de/pages/viewpage.action?spaceKey=ATAF&title=API-Dokumentation",
                    transformedParameter);
        }

        return transformedParameter;
    }

    /**
     * Transforms the provided test data string by decrypting it and processing its parameters.
     *
     * @param parameterNameOfValueToGet The test data string to transform.
     * @return The transformed string with decrypted and processed parameters.
     */
    public static String transformTestData(String parameterNameOfValueToGet) {
        // Decrypt test data
        parameterNameOfValueToGet = decryptTestData(parameterNameOfValueToGet);

        // Transform parameters
        parameterNameOfValueToGet = transformParameters(parameterNameOfValueToGet);

        return parameterNameOfValueToGet;
    }

    /**
     * Retrieves all suite test data as a formatted string.
     *
     * @return A string containing all suite test data in key: value format.
     */
    public static String getSuiteTestData() {
        StringBuilder testData = new StringBuilder();
        for (Map.Entry<String, String> entry : SUITE_TEST_DATA_MAP.entrySet()) {
            if (!testData.isEmpty()) {
                testData.append('\n');
            }
            testData.append(entry.getKey());
            testData.append(':');
            testData.append(' ');
            testData.append(decryptTestData(entry.getValue()));
        }
        return testData.toString();
    }

    /**
     * Retrieves all test data for the current thread as a formatted string.
     *
     * @return A string containing all test data in key: value format.
     */
    public static String getTestData() {
        StringBuilder testData = new StringBuilder();
        for (Map.Entry<String, String> entry : TEST_DATA_MAP.get(Thread.currentThread().getId()).entrySet()) {
            if (!testData.isEmpty()) {
                testData.append('\n');
            }
            testData.append(entry.getKey());
            testData.append(':');
            testData.append(' ');
            testData.append(decryptTestData(entry.getValue()));
        }
        return testData.toString();
    }
}
