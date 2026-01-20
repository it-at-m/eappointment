package ataf.core.helpers;

import ataf.core.assertions.CustomAssertions;
import ataf.core.assertions.strategy.impl.TestNGAssertionStrategy;
import ataf.core.utils.CryptoUtils;
import org.apache.commons.lang3.RandomStringUtils;
import org.testng.Assert;
import org.testng.annotations.AfterClass;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;

import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.time.temporal.IsoFields;

public class TestDataHelperTest {
    @BeforeClass
    public void testSetup() {
        CustomAssertions.setStrategy(new TestNGAssertionStrategy());
        TestDataHelper.initializeTestDataMap();
        CryptoUtils.setSecret(RandomStringUtils.secure().next(64).toCharArray());
    }

    @Test
    public void testTransformTestDataParameterTest() {
        final String CUSTOMER_SURNAME = "Vukmir";
        final String CUSTOMER_NAME = "Milosch";
        final String TICKET_IDENTIFIER = "133305072024";

        TestDataHelper.setTestData("customer_surname", CUSTOMER_SURNAME);
        TestDataHelper.setTestData("customer_name", CUSTOMER_NAME);
        TestDataHelper.setSuiteTestData("ticket_identifier", TICKET_IDENTIFIER);

        Assert.assertEquals(TestDataHelper.getTestData("customer_surname"), CUSTOMER_SURNAME);
        Assert.assertEquals(TestDataHelper.getTestData("customer_name"), CUSTOMER_NAME);
        Assert.assertEquals(TestDataHelper.getSuiteTestData("ticket_identifier"), TICKET_IDENTIFIER);

        final String TEST_DATA_PARAMETER_TEST_EXPECTED = "Hallo mein name ist " + CUSTOMER_NAME + " " + CUSTOMER_SURNAME + ". Meine Ticket nummer lautet: "
                + TICKET_IDENTIFIER;
        final String TEST_DATA_PARAMETER_TEST_ACTUAL = "Hallo mein name ist <TestData.customer_name> <TestData.customer_surname>. Meine Ticket nummer lautet: <SuiteTestData.ticket_identifier>";
        Assert.assertEquals(TestDataHelper.transformTestData(TEST_DATA_PARAMETER_TEST_ACTUAL), TEST_DATA_PARAMETER_TEST_EXPECTED);
    }

    @Test
    public void testTransformTestDataParameterWithSpecialCharacters() {
        final String NAME = "Götz";
        final String CITY = "München_123";

        TestDataHelper.setTestData("name", NAME);
        TestDataHelper.setTestData("city", CITY);

        final String expected = "Mein Name ist " + NAME + " und ich wohne in " + CITY + ".";
        final String actual = "Mein Name ist <TestData.name> und ich wohne in <TestData.city>.";

        Assert.assertEquals(TestDataHelper.transformTestData(actual), expected);
    }

    @Test
    public void testTransformTestDataParameterWithMissingKey() {
        final String actual = "Mein Name ist <TestData.unknown_key>.";
        Assert.assertThrows(IllegalArgumentException.class, () -> TestDataHelper.transformTestData(actual)); // Should throw an IllegalArgumentException
    }

    @Test
    public void testTransformTestDataParameterWithSuiteTestData() {
        final String ORDER_ID = "A12345";
        final String USER_ID = "U98765";

        TestDataHelper.setTestData("user_id", USER_ID);
        TestDataHelper.setSuiteTestData("order_id", ORDER_ID);

        final String expected = "Bestellung " + ORDER_ID + " gehört Benutzer " + USER_ID + ".";
        final String actual = "Bestellung <SuiteTestData.order_id> gehört Benutzer <TestData.user_id>.";

        Assert.assertEquals(TestDataHelper.transformTestData(actual), expected);
    }

    @Test
    public void testTransformTestDataParameterWithoutPlaceholders() {
        final String actual = "Dies ist ein normaler Satz ohne Platzhalter.";
        Assert.assertEquals(TestDataHelper.transformTestData(actual), actual);
    }

    @Test
    public void testTransformTestDataParameterAtStartAndEnd() {
        final String FIRSTNAME = "Anna";
        final String LASTNAME = "Müller";

        TestDataHelper.setTestData("firstname", FIRSTNAME);
        TestDataHelper.setTestData("lastname", LASTNAME);

        final String expected = FIRSTNAME + " arbeitet mit " + LASTNAME;
        final String actual = "<TestData.firstname> arbeitet mit <TestData.lastname>";

        Assert.assertEquals(TestDataHelper.transformTestData(actual), expected);
    }

    @Test
    public void testTransformTestDataParameterWithSimilarKeys() {
        final String KEY_1 = "value1";
        final String KEY_2 = "value2";

        TestDataHelper.setTestData("key", KEY_1);
        TestDataHelper.setTestData("key_extended", KEY_2);

        final String expected = "key: " + KEY_1 + ", extended key: " + KEY_2;
        final String actual = "key: <TestData.key>, extended key: <TestData.key_extended>";

        Assert.assertEquals(TestDataHelper.transformTestData(actual), expected);
    }

    @Test
    public void todayTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute>"),
                LocalDateTime.now().format(DateTimeFormatter.ofPattern("dd.MM.yyyy")));
    }

    @Test
    public void todayDayTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_tag>"),
                LocalDateTime.now().format(DateTimeFormatter.ofPattern("dd")));
    }

    @Test
    public void todayWeekTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_woche>"),
                String.valueOf(LocalDateTime.now().get(IsoFields.WEEK_OF_WEEK_BASED_YEAR)));
    }

    @Test
    public void todayMonthTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_monat>"),
                LocalDateTime.now().format(DateTimeFormatter.ofPattern("MM")));
    }

    @Test
    public void todayYearTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_jahr>"),
                LocalDateTime.now().format(DateTimeFormatter.ofPattern("yyyy")));
    }

    @Test
    public void todayInvertedTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_invertiert>"),
                LocalDateTime.now().format(DateTimeFormatter.ofPattern("yyyy.MM.dd")));
    }

    @Test
    public void todayPlusSevenDaysTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute+7_tage>"),
                LocalDateTime.now().plusDays(7L).format(DateTimeFormatter.ofPattern("dd.MM.yyyy")));
    }

    @Test
    public void todayPlusTenWeeksTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute+10_WOCHEN>"),
                LocalDateTime.now().plusWeeks(10L).format(DateTimeFormatter.ofPattern("dd.MM.yyyy")));
    }

    @Test
    public void todayPlusThirteenMonthsTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute+13_monate>"),
                LocalDateTime.now().plusMonths(13L).format(DateTimeFormatter.ofPattern("dd.MM.yyyy")));
    }

    @Test
    public void todayPlusEightYearsTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute+8_jahre>"),
                LocalDateTime.now().plusYears(8L).format(DateTimeFormatter.ofPattern("dd.MM.yyyy")));
    }

    @Test
    public void todayMinusThreeDaysTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute-3_tage>"),
                LocalDateTime.now().minusDays(3L).format(DateTimeFormatter.ofPattern("dd.MM.yyyy")));
    }

    @Test
    public void todayMinusFiveWeeksTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute-5_WOCHEN>"),
                LocalDateTime.now().minusWeeks(5L).format(DateTimeFormatter.ofPattern("dd.MM.yyyy")));
    }

    @Test
    public void todayMinusSevenMonthsTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute-7_monate>"),
                LocalDateTime.now().minusMonths(7L).format(DateTimeFormatter.ofPattern("dd.MM.yyyy")));
    }

    @Test
    public void todayMinusFourYearsTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute-4_jahre>"),
                LocalDateTime.now().minusYears(4L).format(DateTimeFormatter.ofPattern("dd.MM.yyyy")));
    }

    @Test
    public void todayPlusSevenDaysDayTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_tag+7_tage>"),
                LocalDateTime.now().plusDays(7L).format(DateTimeFormatter.ofPattern("dd")));
    }

    @Test
    public void todayPlusTenWeeksDayTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_tag+10_WOCHEN>"),
                LocalDateTime.now().plusWeeks(10L).format(DateTimeFormatter.ofPattern("dd")));
    }

    @Test
    public void todayPlusThirteenMonthsDayTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_tag+13_monate>"),
                LocalDateTime.now().plusMonths(13L).format(DateTimeFormatter.ofPattern("dd")));
    }

    @Test
    public void todayPlusEightYearsDayTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_tag+8_jahre>"),
                LocalDateTime.now().plusYears(8L).format(DateTimeFormatter.ofPattern("dd")));
    }

    @Test
    public void todayMinusThreeDaysDayTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_tag-3_tage>"),
                LocalDateTime.now().minusDays(3L).format(DateTimeFormatter.ofPattern("dd")));
    }

    @Test
    public void todayMinusFiveWeeksDayTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_tag-5_WOCHEN>"),
                LocalDateTime.now().minusWeeks(5L).format(DateTimeFormatter.ofPattern("dd")));
    }

    @Test
    public void todayMinusSevenMonthsDayTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_tag-7_monate>"),
                LocalDateTime.now().minusMonths(7L).format(DateTimeFormatter.ofPattern("dd")));
    }

    @Test
    public void todayMinusFourYearsDayTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_tag-4_jahre>"),
                LocalDateTime.now().minusYears(4L).format(DateTimeFormatter.ofPattern("dd")));
    }

    @Test
    public void todayPlusSevenDaysWeekTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_woche+7_tage>"),
                String.valueOf(LocalDateTime.now().plusDays(7L).get(IsoFields.WEEK_OF_WEEK_BASED_YEAR)));
    }

    @Test
    public void todayPlusTenWeeksWeekTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_woche+10_WOCHEN>"),
                String.valueOf(LocalDateTime.now().plusWeeks(10L).get(IsoFields.WEEK_OF_WEEK_BASED_YEAR)));
    }

    @Test
    public void todayPlusThirteenMonthsWeekTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_woche+13_monate>"),
                String.valueOf(LocalDateTime.now().plusMonths(13L).get(IsoFields.WEEK_OF_WEEK_BASED_YEAR)));
    }

    @Test
    public void todayPlusEightYearsWeekTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_woche+8_jahre>"),
                String.valueOf(LocalDateTime.now().plusYears(8L).get(IsoFields.WEEK_OF_WEEK_BASED_YEAR)));
    }

    @Test
    public void todayMinusThreeDaysWeekTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_woche-3_tage>"),
                String.valueOf(LocalDateTime.now().minusDays(3L).get(IsoFields.WEEK_OF_WEEK_BASED_YEAR)));
    }

    @Test
    public void todayMinusFiveWeeksWeekTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_woche-5_WOCHEN>"),
                String.valueOf(LocalDateTime.now().minusWeeks(5L).get(IsoFields.WEEK_OF_WEEK_BASED_YEAR)));
    }

    @Test
    public void todayMinusSevenMonthsWeekTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_woche-7_monate>"),
                String.valueOf(LocalDateTime.now().minusMonths(7L).get(IsoFields.WEEK_OF_WEEK_BASED_YEAR)));
    }

    @Test
    public void todayMinusFourYearsWeekTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_woche-4_jahre>"),
                String.valueOf(LocalDateTime.now().minusYears(4L).get(IsoFields.WEEK_OF_WEEK_BASED_YEAR)));
    }

    @Test
    public void todayPlusSevenDaysMonthTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_monat+7_tage>"),
                LocalDateTime.now().plusDays(7L).format(DateTimeFormatter.ofPattern("MM")));
    }

    @Test
    public void todayPlusTenWeeksMonthTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_monat+10_WOCHEN>"),
                LocalDateTime.now().plusWeeks(10L).format(DateTimeFormatter.ofPattern("MM")));
    }

    @Test
    public void todayPlusThirteenMonthsMonthTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_monat+13_monate>"),
                LocalDateTime.now().plusMonths(13L).format(DateTimeFormatter.ofPattern("MM")));
    }

    @Test
    public void todayPlusEightYearsMonthTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_monat+8_jahre>"),
                LocalDateTime.now().plusYears(8L).format(DateTimeFormatter.ofPattern("MM")));
    }

    @Test
    public void todayMinusThreeDaysMonthTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_monat-3_tage>"),
                LocalDateTime.now().minusDays(3L).format(DateTimeFormatter.ofPattern("MM")));
    }

    @Test
    public void todayMinusFiveWeeksMonthTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_monat-5_WOCHEN>"),
                LocalDateTime.now().minusWeeks(5L).format(DateTimeFormatter.ofPattern("MM")));
    }

    @Test
    public void todayMinusSevenMonthsMonthTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_monat-7_monate>"),
                LocalDateTime.now().minusMonths(7L).format(DateTimeFormatter.ofPattern("MM")));
    }

    @Test
    public void todayMinusFourYearsMonthTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_monat-4_jahre>"),
                LocalDateTime.now().minusYears(4L).format(DateTimeFormatter.ofPattern("MM")));
    }

    @Test
    public void todayPlusSevenDaysYearTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_jahr+7_tage>"),
                LocalDateTime.now().plusDays(7L).format(DateTimeFormatter.ofPattern("yyyy")));
    }

    @Test
    public void todayPlusTenWeeksYearTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_jahr+10_WOCHEN>"),
                LocalDateTime.now().plusWeeks(10L).format(DateTimeFormatter.ofPattern("yyyy")));
    }

    @Test
    public void todayPlusThirteenMonthsYearTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_jahr+13_monate>"),
                LocalDateTime.now().plusMonths(13L).format(DateTimeFormatter.ofPattern("yyyy")));
    }

    @Test
    public void todayPlusEightYearsYearTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_jahr+8_jahre>"),
                LocalDateTime.now().plusYears(8L).format(DateTimeFormatter.ofPattern("yyyy")));
    }

    @Test
    public void todayMinusThreeDaysYearTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_jahr-3_tage>"),
                LocalDateTime.now().minusDays(3L).format(DateTimeFormatter.ofPattern("yyyy")));
    }

    @Test
    public void todayMinusFiveWeeksYearTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_jahr-5_WOCHEN>"),
                LocalDateTime.now().minusWeeks(5L).format(DateTimeFormatter.ofPattern("yyyy")));
    }

    @Test
    public void todayMinusSevenMonthsYearTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_jahr-7_monate>"),
                LocalDateTime.now().minusMonths(7L).format(DateTimeFormatter.ofPattern("yyyy")));
    }

    @Test
    public void todayMinusFourYearsYearTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_jahr-4_jahre>"),
                LocalDateTime.now().minusYears(4L).format(DateTimeFormatter.ofPattern("yyyy")));
    }

    @Test
    public void todayPlusSevenDaysInvertedTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_invertiert+7_tage>"),
                LocalDateTime.now().plusDays(7L).format(DateTimeFormatter.ofPattern("yyyy.MM.dd")));
    }

    @Test
    public void todayPlusTenWeeksInvertedTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_invertiert+10_WOCHEN>"),
                LocalDateTime.now().plusWeeks(10L).format(DateTimeFormatter.ofPattern("yyyy.MM.dd")));
    }

    @Test
    public void todayPlusThirteenMonthsInvertedTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_invertiert+13_monate>"),
                LocalDateTime.now().plusMonths(13L).format(DateTimeFormatter.ofPattern("yyyy.MM.dd")));
    }

    @Test
    public void todayPlusEightYearsInvertedTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_invertiert+8_jahre>"),
                LocalDateTime.now().plusYears(8L).format(DateTimeFormatter.ofPattern("yyyy.MM.dd")));
    }

    @Test
    public void todayMinusThreeDaysInvertedTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_invertiert-3_tage>"),
                LocalDateTime.now().minusDays(3L).format(DateTimeFormatter.ofPattern("yyyy.MM.dd")));
    }

    @Test
    public void todayMinusFiveWeeksInvertedTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_invertiert-5_WOCHEN>"),
                LocalDateTime.now().minusWeeks(5L).format(DateTimeFormatter.ofPattern("yyyy.MM.dd")));
    }

    @Test
    public void todayMinusSevenMonthsInvertedTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_invertiert-7_monate>"),
                LocalDateTime.now().minusMonths(7L).format(DateTimeFormatter.ofPattern("yyyy.MM.dd")));
    }

    @Test
    public void todayMinusFourYearsInvertedTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_invertiert-4_jahre>"),
                LocalDateTime.now().minusYears(4L).format(DateTimeFormatter.ofPattern("yyyy.MM.dd")));
    }

    @Test
    public void todayTimeTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_uhrzeit>"),
                LocalDateTime.now().format(DateTimeFormatter.ofPattern("HH:mm:ss")));
    }

    @Test
    public void todayHourTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_stunde>"),
                LocalDateTime.now().format(DateTimeFormatter.ofPattern("HH")));
    }

    @Test
    public void todayMinuteTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_minute>"),
                LocalDateTime.now().format(DateTimeFormatter.ofPattern("mm")));
    }

    @Test
    public void todaySecondTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_sekunde>"),
                LocalDateTime.now().format(DateTimeFormatter.ofPattern("ss")));
    }

    @Test
    public void todayTimeInvertedTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_uhrzeit_invertiert>"),
                LocalDateTime.now().format(DateTimeFormatter.ofPattern("HH.mm.ss")));
    }

    @Test
    public void todayPlusTwoHoursTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute+2_stunden>"),
                LocalDateTime.now().plusHours(2L).format(DateTimeFormatter.ofPattern("dd.MM.yyyy")));
    }

    @Test
    public void todayMinusFifteenMinutesTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute-15_minuten>"),
                LocalDateTime.now().minusMinutes(15L).format(DateTimeFormatter.ofPattern("dd.MM.yyyy")));
    }

    @Test
    public void todayTimePlusThreeHoursTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_uhrzeit+3_stunden>"),
                LocalDateTime.now().plusHours(3L).format(DateTimeFormatter.ofPattern("HH:mm:ss")));
    }

    @Test
    public void todayHourMinusOneHourTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_stunde-1_stunde>"),
                LocalDateTime.now().minusHours(1L).format(DateTimeFormatter.ofPattern("HH")));
    }

    @Test
    public void todayMinutePlusTenMinutesTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_minute+10_minuten>"),
                LocalDateTime.now().plusMinutes(10L).format(DateTimeFormatter.ofPattern("mm")));
    }

    @Test
    public void todaySecondMinusThirtySecondsTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_sekunde-30_sekunden>"),
                LocalDateTime.now().minusSeconds(30L).format(DateTimeFormatter.ofPattern("ss")));
    }

    @Test
    public void todayTimeInvertedPlusTwoHoursTest() {
        Assert.assertEquals(TestDataHelper.transformTestData("<heute_uhrzeit_invertiert+2_stunden>"),
                LocalDateTime.now().plusHours(2L).format(DateTimeFormatter.ofPattern("HH.mm.ss")));
    }

    @AfterClass
    public void testTearDown() {
        TestDataHelper.flushMapTestData();
        TestDataHelper.flushMapSuiteTestData();
        CryptoUtils.clearSecret();
    }
}
