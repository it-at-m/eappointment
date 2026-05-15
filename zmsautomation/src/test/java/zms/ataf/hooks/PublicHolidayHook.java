package zms.ataf.hooks;

import org.testng.SkipException;

import ataf.core.logging.ScenarioLogManager;
import io.cucumber.java.Before;
import zms.ataf.support.PublicHolidayChecker;

/**
 * Skips scenarios tagged {@code @NotOnHoliday} when today is a public holiday in feiertage test data.
 */
public class PublicHolidayHook {

    @Before("@NotOnHoliday")
    public void skipWhenTodayIsPublicHoliday() {
        if (PublicHolidayChecker.isTodayPublicHoliday()) {
            String message = "Skipped @NotOnHoliday scenario: today is a public holiday in feiertage test data";
            ScenarioLogManager.getLogger().warn(message);
            throw new SkipException(message);
        }
    }
}
