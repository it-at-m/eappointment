package zms.ataf.ui.pages.citizenview.support;

import java.util.function.BooleanSupplier;

import ataf.core.logging.ScenarioLogManager;

/**
 * Shared polling helpers for zmscitizenview page objects.
 */
public final class CitizenViewWaits {

    private CitizenViewWaits() {
    }

    /**
     * Generic helper for asynchronous transitions after actions such as Weiter / confirm links.
     * Waits in four windows: 5s, then +10s, then +15s, then +30s (total 60s) while polling {@code condition}.
     */
    public static void waitWithThreeWindows(BooleanSupplier condition, String context) {
        long deadlineFirst = System.currentTimeMillis() + 5000L;
        while (!condition.getAsBoolean() && System.currentTimeMillis() < deadlineFirst) {
            try {
                Thread.sleep(250L);
            } catch (InterruptedException ie) {
                Thread.currentThread().interrupt();
                return;
            }
        }
        if (condition.getAsBoolean()) {
            return;
        }
        ScenarioLogManager.getLogger()
                .warn("{} not visible after first 5s window; retrying for additional 10s", context);
        long deadlineSecond = System.currentTimeMillis() + 10000L;
        while (!condition.getAsBoolean() && System.currentTimeMillis() < deadlineSecond) {
            try {
                Thread.sleep(250L);
            } catch (InterruptedException ie) {
                Thread.currentThread().interrupt();
                return;
            }
        }
        if (condition.getAsBoolean()) {
            return;
        }
        ScenarioLogManager.getLogger()
                .warn("{} not visible after first 15s window; retrying for additional 15s", context);
        long deadlineThird = System.currentTimeMillis() + 15000L;
        while (!condition.getAsBoolean() && System.currentTimeMillis() < deadlineThird) {
            try {
                Thread.sleep(250L);
            } catch (InterruptedException ie) {
                Thread.currentThread().interrupt();
                return;
            }
        }
        if (condition.getAsBoolean()) {
            return;
        }
        ScenarioLogManager.getLogger()
                .warn("{} still not visible after 30s; retrying for final 30s window", context);
        long deadlineFourth = System.currentTimeMillis() + 30000L;
        while (!condition.getAsBoolean() && System.currentTimeMillis() < deadlineFourth) {
            try {
                Thread.sleep(250L);
            } catch (InterruptedException ie) {
                Thread.currentThread().interrupt();
                return;
            }
        }
    }

    public static void sleepQuiet(long millis) {
        try {
            Thread.sleep(millis);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
    }

    /**
     * Step 3 (combined): highlight + click — use split steps in features so {@code @AfterStep} captures the slot area.
     */

}
