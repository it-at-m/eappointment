package zms.ataf.helpers;

import java.time.LocalDate;

import org.testng.Assert;
import org.testng.annotations.Test;

public class FixtureHolidaysTest {

    @Test
    public void allerheiligenIsClosedAndTheNextDayIsOpen() {
        Assert.assertTrue(FixtureHolidays.isClosed(LocalDate.of(2026, 11, 1)));
        Assert.assertEquals(
                FixtureHolidays.firstOpenOnOrAfter(LocalDate.of(2026, 11, 1)),
                LocalDate.of(2026, 11, 2));
    }

    @Test
    public void germanUnityDay2026IsNotClosed() {
        LocalDate unityDay = LocalDate.of(2026, 10, 3);
        Assert.assertFalse(FixtureHolidays.isClosed(unityDay));
        Assert.assertEquals(FixtureHolidays.firstOpenOnOrAfter(unityDay), unityDay);
    }

    @Test
    public void christmasEveThroughBoxingDaySkipsToThe27th() {
        Assert.assertEquals(
                FixtureHolidays.firstOpenOnOrAfter(LocalDate.of(2026, 12, 24)),
                LocalDate.of(2026, 12, 27));
    }
}
