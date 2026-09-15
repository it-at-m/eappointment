package zms.ataf.helpers;

import ataf.core.helpers.TestDataHelper;

/**
 * Scenario-scoped counters used by Mailinator assertions when appointments are
 * created or cancelled from the Admin UI.
 */
public final class AppointmentCountHelper {

    private AppointmentCountHelper() {
        throw new UnsupportedOperationException("Utility class");
    }

    public static void incrementAppointmentCount() {
        increment("appointment_count");
    }

    public static void incrementAppointmentCanceledCount() {
        increment("appointment_canceled_count");
    }

    private static void increment(String key) {
        if (TestDataHelper.getTestData(key) != null) {
            TestDataHelper.setTestData(key, String.valueOf(parseIntOrFail(TestDataHelper.getTestData(key), key) + 1));
        } else {
            TestDataHelper.setTestData(key, "1");
        }
    }

    private static int parseIntOrFail(String value, String label) {
        try {
            return Integer.parseInt(value);
        } catch (NumberFormatException nfe) {
            throw new AssertionError(
                    "Failed to parse integer for " + label + " from value \"" + value + "\"",
                    nfe);
        }
    }
}
