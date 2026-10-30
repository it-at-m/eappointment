package zms.ataf.helpers;

import java.io.IOException;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.LocalDate;
import java.time.LocalTime;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;
import java.util.Map;
import java.util.concurrent.TimeUnit;
import java.util.regex.Pattern;

import org.testng.Assert;

import ataf.core.logging.ScenarioLogManager;

/**
 * ZMSKVR-1250 / ZMSKVR-1357.
 * Live {@code buerger.way_time} is a clock time. The archive stores fractional minutes.
 * The daily bin only calculates yesterday; the same class accepts any day, so a scenario
 * can fill the waiting statistic without waiting for the cron.
 */
public final class WaitingTimeArchive {

    private static final Pattern CLOCK = Pattern.compile("\\d{2}:\\d{2}:\\d{2}");
    private static final DateTimeFormatter TIME = DateTimeFormatter.ofPattern("HH:mm:ss");
    private static final ThreadLocal<List<HourAverage>> EXPECTED = new ThreadLocal<>();

    private WaitingTimeArchive() {
    }

    public static final class HourAverage {
        public final LocalDate day;
        public final int hour;
        public final boolean shownInReport;
        public final int spontanCount;
        public final String spontanWaiting;
        public final String spontanWay;
        public final int appointmentCount;
        public final String appointmentWaiting;
        public final String appointmentWay;
        public final int totalCount;
        public final String totalWaiting;
        public final String totalWay;

        HourAverage(
                LocalDate day,
                int hour,
                int spontanCount,
                double spontanWaiting,
                double spontanWay,
                int appointmentCount,
                double appointmentWaiting,
                double appointmentWay) {
            this.day = day;
            this.hour = hour;
            this.shownInReport = hour >= 7 && hour <= 18;
            this.spontanCount = spontanCount;
            this.spontanWaiting = formatMinutes(spontanWaiting);
            this.spontanWay = formatMinutes(spontanWay);
            this.appointmentCount = appointmentCount;
            this.appointmentWaiting = formatMinutes(appointmentWaiting);
            this.appointmentWay = formatMinutes(appointmentWay);
            this.totalCount = spontanCount + appointmentCount;
            double totalWait = totalCount == 0
                    ? 0
                    : ((spontanWaiting * spontanCount) + (appointmentWaiting * appointmentCount)) / totalCount;
            double totalWay = totalCount == 0
                    ? 0
                    : ((spontanWay * spontanCount) + (appointmentWay * appointmentCount)) / totalCount;
            this.totalWaiting = formatMinutes(totalWait);
            this.totalWay = formatMinutes(totalWay);
        }
    }

    public static List<HourAverage> calculatedAverages() {
        List<HourAverage> averages = EXPECTED.get();
        Assert.assertNotNull(averages, "Calculate the waiting statistic before reading it.");
        return averages;
    }

    /** Same rule as {@code ReportHelper::formatTimeValue}: decimal minutes to mm:ss. */
    public static String formatMinutes(double minutes) {
        int totalSeconds = (int) Math.round(minutes * 60.0d);
        if (totalSeconds <= 0) {
            return "00:00";
        }
        return String.format(Locale.ROOT, "%02d:%02d", totalSeconds / 60, totalSeconds % 60);
    }

    public static int secondsOf(double minutes) {
        return (int) Math.round(minutes * 60.0d);
    }

    /**
     * A future appointment slot has no wait until the customer has an arrival.
     * The daily calculator also skips a zero waiting time, so the row would never
     * appear in the hour average. Stamp the arrival a few seconds before the call.
     */
    public static void stampArrivalSecondsAgo(String familyName, int seconds) {
        String clock = BerlinTime.now().minusSeconds(seconds).format(TIME);
        try (Connection connection = open();
                PreparedStatement update = connection.prepareStatement(
                        "UPDATE buerger SET wsm_aufnahmezeit = ? WHERE Name = ?")) {
            update.setString(1, clock);
            update.setString(2, familyName);
            int rows = update.executeUpdate();
            Assert.assertEquals(rows, 1,
                    "Expected one buerger row for " + familyName + " when stamping arrival " + clock);
            ScenarioLogManager.getLogger().info("Stamped arrival {} for {}", clock, familyName);
        } catch (SQLException exception) {
            throw new IllegalStateException("Could not stamp arrival for " + familyName, exception);
        }
    }

    /** Live {@code buerger.waiting_time} or {@code way_time} after the status change has committed. */
    public static void assertStoredClock(int processId, String column) {
        String sql = "SELECT TIME_FORMAT(waiting_time, '%H:%i:%s'), TIME_FORMAT(way_time, '%H:%i:%s'), "
                + "TIME_FORMAT(wsm_aufnahmezeit, '%H:%i:%s'), TIME_FORMAT(Uhrzeit, '%H:%i:%s'), "
                + "TIME_FORMAT(aufrufzeit, '%H:%i:%s'), Name FROM buerger WHERE BuergerID = ?";
        try (Connection connection = open();
                PreparedStatement query = connection.prepareStatement(sql)) {
            query.setInt(1, processId);
            try (ResultSet rows = query.executeQuery()) {
                Assert.assertTrue(rows.next(), "No live buerger row for id " + processId);
                String waiting = rows.getString(1);
                String way = rows.getString(2);
                String detail = "id=" + processId
                        + " name=" + rows.getString(6)
                        + " waiting=" + waiting
                        + " way=" + way
                        + " arrival=" + rows.getString(3)
                        + " uhrzeit=" + rows.getString(4)
                        + " call=" + rows.getString(5);
                ScenarioLogManager.getLogger().info("Stored clocks {}", detail);
                String value = "way_time".equals(column) ? way : waiting;
                Assert.assertNotNull(value, column + " is empty. " + detail);
                Assert.assertTrue(CLOCK.matcher(value).matches(),
                        column + " should be HH:MM:SS. " + detail);
                int storedSeconds = LocalTime.parse(value, TIME).toSecondOfDay();
                Assert.assertTrue(storedSeconds >= 1 && storedSeconds < 300,
                        column + " should keep a few seconds. " + detail);
            }
        } catch (SQLException exception) {
            throw new IllegalStateException("Could not read " + column + " for id " + processId, exception);
        }
    }

    public static void assertLiveClockTimes(String familyName) {
        try (Connection connection = open();
                PreparedStatement query = connection.prepareStatement(
                        "SELECT TIME_FORMAT(waiting_time, '%H:%i:%s'), TIME_FORMAT(way_time, '%H:%i:%s') "
                                + "FROM buerger WHERE Name = ?")) {
            query.setString(1, familyName);
            try (ResultSet rows = query.executeQuery()) {
                Assert.assertTrue(rows.next(), "No live buerger row for " + familyName);
                String waiting = rows.getString(1);
                String way = rows.getString(2);
                ScenarioLogManager.getLogger()
                        .info("Live times for {}: waiting={} way={}", familyName, waiting, way);
                assertClock(waiting, "waiting_time");
                assertClock(way, "way_time");
                int waySeconds = LocalTime.parse(way, TIME).toSecondOfDay();
                Assert.assertTrue(waySeconds >= 1 && waySeconds < 300,
                        "Way time for " + familyName + " should be a few seconds, was " + way);
                Assert.assertFalse(rows.next(), "More than one live row for " + familyName);
            }
        } catch (SQLException exception) {
            throw new IllegalStateException("Could not read live times for " + familyName, exception);
        }
    }

    public static ArchivedTimes assertArchivedSecondPrecision(String familyName) {
        ArchivedTimes archive = readArchive("buergerarchiv", familyName);
        ArchivedTimes today = readArchive("buergerarchivtoday", familyName);
        Assert.assertEquals(today.waitingMinutes, archive.waitingMinutes, 0.001,
                "buergerarchivtoday waiting_time");
        Assert.assertEquals(today.wayMinutes, archive.wayMinutes, 0.001,
                "buergerarchivtoday way_time");
        Assert.assertEquals(today.processingMinutes, archive.processingMinutes, 0.001,
                "buergerarchivtoday processing_time");
        Assert.assertTrue(archive.waitingMinutes > 0,
                "Waiting time must be stored or the daily statistic skips the row. Was "
                        + archive.waitingMinutes);
        assertFractionalMinutes(familyName, "waiting_time", archive.waitingMinutes);
        assertFractionalMinutes(familyName, "way_time", archive.wayMinutes);
        assertFractionalMinutes(familyName, "processing_time", archive.processingMinutes);
        return archive;
    }

    /**
     * Runs the daily calculator for the archive dates of these customers and checks
     * {@code wartenrstatistik} against the archive averages.
     */
    public static List<HourAverage> calculateAndExpect(String... familyNames) {
        List<ArchivedTimes> ours = new ArrayList<>();
        for (String familyName : familyNames) {
            ours.add(readArchive("buergerarchiv", familyName));
        }
        List<LocalDate> days = ours.stream().map(row -> row.day).distinct().toList();
        for (LocalDate day : days) {
            runCalculator(day);
        }
        List<HourAverage> expected = new ArrayList<>();
        for (ArchivedTimes sample : ours) {
            if (expected.stream().anyMatch(hour -> hour.day.equals(sample.day) && hour.hour == sample.hour)) {
                continue;
            }
            expected.add(expectHour(sample.scopeId, sample.day, sample.hour));
        }
        EXPECTED.set(expected);
        return expected;
    }

    private static HourAverage expectHour(int scopeId, LocalDate day, int hour) {
        List<ArchivedTimes> rows = readScopeDay(scopeId, day);
        double spontanWait = 0;
        double spontanWay = 0;
        int spontanCount = 0;
        double appointmentWait = 0;
        double appointmentWay = 0;
        int appointmentCount = 0;
        for (ArchivedTimes row : rows) {
            if (row.hour != hour || row.waitingMinutes <= 0) {
                continue;
            }
            if (row.withAppointment) {
                appointmentCount++;
                appointmentWait += row.waitingMinutes;
                appointmentWay += row.wayMinutes;
            } else {
                spontanCount++;
                spontanWait += row.waitingMinutes;
                spontanWay += row.wayMinutes;
            }
        }
        double spontanWaitAvg = average(spontanWait, spontanCount);
        double spontanWayAvg = average(spontanWay, spontanCount);
        double appointmentWaitAvg = average(appointmentWait, appointmentCount);
        double appointmentWayAvg = average(appointmentWay, appointmentCount);
        assertStatisticColumn(scopeId, day, hour, "spontaneous", spontanCount, spontanWaitAvg, spontanWayAvg);
        assertStatisticColumn(scopeId, day, hour, "appointment", appointmentCount, appointmentWaitAvg, appointmentWayAvg);
        HourAverage average = new HourAverage(
                day,
                hour,
                spontanCount,
                spontanWaitAvg,
                spontanWayAvg,
                appointmentCount,
                appointmentWaitAvg,
                appointmentWayAvg);
        ScenarioLogManager.getLogger().info(
                "Waiting statistic {} hour {} spontan {}/{} termin {}/{} total {}/{}",
                day,
                hour,
                average.spontanWaiting,
                average.spontanWay,
                average.appointmentWaiting,
                average.appointmentWay,
                average.totalWaiting,
                average.totalWay);
        return average;
    }

    private static void assertStatisticColumn(
            int scopeId,
            LocalDate day,
            int hour,
            String suffix,
            int count,
            double waiting,
            double way) {
        String prefix = String.format(Locale.ROOT, "hour_%02d_", hour);
        String sql = "SELECT `" + prefix + "waiting_count_" + suffix + "`, `"
                + prefix + "waiting_time_" + suffix + "`, `"
                + prefix + "way_time_" + suffix + "` "
                + "FROM wartenrstatistik WHERE standortid = ? AND datum = ?";
        try (Connection connection = open();
                PreparedStatement query = connection.prepareStatement(sql)) {
            query.setInt(1, scopeId);
            query.setDate(2, java.sql.Date.valueOf(day));
            try (ResultSet rows = query.executeQuery()) {
                Assert.assertTrue(rows.next(),
                        "No wartenrstatistik row for scope " + scopeId + " on " + day);
                Assert.assertEquals(rows.getInt(1), count, prefix + "waiting_count_" + suffix);
                Assert.assertEquals(rows.getDouble(2), waiting, 0.02, prefix + "waiting_time_" + suffix);
                Assert.assertEquals(rows.getDouble(3), way, 0.02, prefix + "way_time_" + suffix);
            }
        } catch (SQLException exception) {
            throw new IllegalStateException("Could not read wartenrstatistik", exception);
        }
    }

    private static double average(double sum, int count) {
        if (count <= 0) {
            return 0;
        }
        return Math.round((sum / count) * 100.0d) / 100.0d;
    }

    private static void assertFractionalMinutes(String familyName, String column, double minutes) {
        double wholeMinutes = Math.rint(minutes);
        Assert.assertTrue(Math.abs(minutes - wholeMinutes) > 0.001,
                familyName + " " + column + " is still a whole minute (" + minutes
                        + "). Wegezeit, Wartezeit and Bearbeitungszeit must keep the seconds.");
        Assert.assertTrue(secondsOf(minutes) < 300,
                familyName + " " + column + " is unexpectedly long: " + minutes + " minutes");
    }

    private static int hourOf(String timestamp) {
        if (timestamp == null || timestamp.length() < 2) {
            return -1;
        }
        try {
            return Integer.parseInt(timestamp.substring(0, 2));
        } catch (NumberFormatException exception) {
            throw new IllegalArgumentException("Archive timestamp is not a clock time: " + timestamp, exception);
        }
    }

    private static void assertClock(String value, String column) {
        Assert.assertNotNull(value, column + " is empty");
        Assert.assertTrue(CLOCK.matcher(value).matches(),
                column + " should be HH:MM:SS and was " + value);
    }

    public static final class ArchivedTimes {
        public final int scopeId;
        public final LocalDate day;
        public final int hour;
        public final boolean withAppointment;
        public final double waitingMinutes;
        public final double wayMinutes;
        public final double processingMinutes;

        ArchivedTimes(
                int scopeId,
                LocalDate day,
                int hour,
                boolean withAppointment,
                double waitingMinutes,
                double wayMinutes,
                double processingMinutes) {
            this.scopeId = scopeId;
            this.day = day;
            this.hour = hour;
            this.withAppointment = withAppointment;
            this.waitingMinutes = waitingMinutes;
            this.wayMinutes = wayMinutes;
            this.processingMinutes = processingMinutes;
        }
    }

    private static ArchivedTimes readArchive(String table, String familyName) {
        String sql = "SELECT StandortID, Datum, TIME_FORMAT(`Timestamp`, '%H:%i:%s'), mitTermin, "
                + "waiting_time, way_time, processing_time FROM " + table + " WHERE name = ?";
        try (Connection connection = open();
                PreparedStatement query = connection.prepareStatement(sql)) {
            query.setString(1, familyName);
            try (ResultSet rows = query.executeQuery()) {
                Assert.assertTrue(rows.next(), "No " + table + " row for " + familyName);
                ArchivedTimes times = mapRow(rows);
                Assert.assertFalse(rows.next(), "More than one " + table + " row for " + familyName);
                ScenarioLogManager.getLogger().info(
                        "{} {} hour {} appointment={} wait={} way={} processing={}",
                        table,
                        familyName,
                        times.hour,
                        times.withAppointment,
                        times.waitingMinutes,
                        times.wayMinutes,
                        times.processingMinutes);
                return times;
            }
        } catch (SQLException exception) {
            throw new IllegalStateException("Could not read " + table + " for " + familyName, exception);
        }
    }

    private static List<ArchivedTimes> readScopeDay(int scopeId, LocalDate day) {
        String sql = "SELECT StandortID, Datum, TIME_FORMAT(`Timestamp`, '%H:%i:%s'), mitTermin, "
                + "waiting_time, way_time, processing_time FROM buergerarchiv "
                + "WHERE StandortID = ? AND Datum = ?";
        List<ArchivedTimes> rows = new ArrayList<>();
        try (Connection connection = open();
                PreparedStatement query = connection.prepareStatement(sql)) {
            query.setInt(1, scopeId);
            query.setDate(2, java.sql.Date.valueOf(day));
            try (ResultSet result = query.executeQuery()) {
                while (result.next()) {
                    rows.add(mapRow(result));
                }
            }
        } catch (SQLException exception) {
            throw new IllegalStateException("Could not read the archive for scope " + scopeId, exception);
        }
        return rows;
    }

    private static ArchivedTimes mapRow(ResultSet rows) throws SQLException {
        String timestamp = rows.getString(3);
        int hour = hourOf(timestamp);
        return new ArchivedTimes(
                rows.getInt(1),
                rows.getDate(2).toLocalDate(),
                hour,
                rows.getInt(4) == 1,
                rows.getDouble(5),
                rows.getDouble(6),
                rows.getDouble(7));
    }

    private static void runCalculator(LocalDate day) {
        Path backend = Path.of(System.getProperty("user.dir")).resolve("../zmsbackend").normalize();
        if (!Files.isRegularFile(backend.resolve("bin/script_bootstrap.php"))) {
            backend = Path.of("/var/www/html/zmsbackend");
        }
        Assert.assertTrue(Files.isRegularFile(backend.resolve("bin/script_bootstrap.php")),
                "zmsbackend bootstrap is missing at " + backend);
        String php = Files.isExecutable(Path.of("/usr/local/bin/php")) ? "/usr/local/bin/php" : "/usr/bin/php";
        String code = "require 'bin/script_bootstrap.php';"
                + "(new \\BO\\Zmsbackend\\Helper\\CalculateDailyWaitingStatisticByCron())"
                + "->run(new \\DateTimeImmutable(getenv('WAITING_STATISTIC_DAY') . ' 00:00:00'), true);"
                + "echo \"ok\\n\";";
        ProcessBuilder builder = new ProcessBuilder(php, "-r", code);
        builder.directory(backend.toFile());
        Map<String, String> environment = builder.environment();
        environment.putIfAbsent("ZMS_ENV", "dev");
        environment.put("ZMS_CRONROOT", "1");
        environment.put("WAITING_STATISTIC_DAY", day.toString());
        builder.redirectErrorStream(true);
        String logged = "";
        try {
            for (int attempt = 1; attempt <= 2; attempt++) {
                Process process = builder.start();
                StringBuffer output = new StringBuffer();
                Thread reader = new Thread(() -> {
                    try {
                        output.append(new String(process.getInputStream().readAllBytes(), StandardCharsets.UTF_8));
                    } catch (IOException io) {
                        output.append(io.getMessage());
                    }
                }, "waiting-statistic-output");
                reader.setDaemon(true);
                reader.start();
                boolean finished = process.waitFor(90, TimeUnit.SECONDS);
                if (!finished) {
                    process.destroyForcibly();
                }
                reader.join(2000L);
                logged = output.toString();
                if (finished && process.exitValue() == 0 && logged.contains("ok")) {
                    ScenarioLogManager.getLogger()
                            .info("Calculated the waiting statistic for {}. {}", day, truncate(logged));
                    return;
                }
                ScenarioLogManager.getLogger().warn(
                        "Waiting statistic for {} failed on attempt {}: {}", day, attempt, truncate(logged));
            }
        } catch (IOException | InterruptedException exception) {
            if (exception instanceof InterruptedException) {
                Thread.currentThread().interrupt();
            }
            throw new IllegalStateException("Could not calculate the waiting statistic for " + day, exception);
        }
        Assert.fail("Waiting statistic for " + day + " did not finish. " + truncate(logged));
    }

    private static String truncate(String text) {
        if (text == null) {
            return "";
        }
        String flat = text.replaceAll("\\s+", " ").trim();
        return flat.length() <= 800 ? flat : flat.substring(0, 800);
    }

    private static Connection open() throws SQLException {
        String host = envOrDefault("MYSQL_HOST", "db");
        String port = mysqlPort(envOrDefault("MYSQL_PORT", "3306"));
        String database = envOrDefault("MYSQL_DATABASE", "db");
        String user = envOrDefault("MYSQL_USER", "db");
        String url = "jdbc:mysql://" + host + ":" + port + "/" + database;
        return DriverManager.getConnection(url, user, envOrDefault("MYSQL_PASSWORD", "db"));
    }

    private static String mysqlPort(String raw) {
        int colon = raw.lastIndexOf(':');
        if (colon >= 0 && colon < raw.length() - 1) {
            return raw.substring(colon + 1);
        }
        return raw;
    }

    private static String envOrDefault(String name, String fallback) {
        String value = System.getenv(name);
        if (value == null || value.isBlank()) {
            return fallback;
        }
        return value.trim();
    }
}
