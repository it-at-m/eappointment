package zms.ataf.support;

import static org.assertj.core.api.Assertions.assertThat;

import org.testng.annotations.Test;

class PublicHolidayCheckerTest {

    @Test
    void detectsKnownHolidayFromMigrationData() {
        assertThat(PublicHolidayChecker.isPublicHoliday(java.time.LocalDate.parse("2026-05-14"))).isTrue();
        assertThat(PublicHolidayChecker.isPublicHoliday(java.time.LocalDate.parse("2026-05-15"))).isFalse();
    }
}
