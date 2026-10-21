package zms.ataf.ui.pages.citizenview.support;

import java.io.IOException;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;
import java.sql.Connection;
import java.sql.Date;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.LocalDate;
import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.TimeUnit;

import ataf.core.logging.ScenarioLogManager;

/**
 * Restores Flyway V61 day+7 availability for Pasing/Föhring/Neuperlach/Riem after booked-out
 * scenarios reserved every seat.
 *
 * <p>Citizen availability comes from the precomputed {@code slot} / {@code slot_process} tables.
 * Raising {@code oeffnungszeit} seat counts alone does nothing until {@code calculateSlots}
 * rebuilds those rows (same as {@code zmsautomation-test} after Flyway).
 */
public final class FeuerwacheOrderDaySeats {

    private static final int[] ORDER_DAY_OPENING_IDS = {136560, 136561, 136562, 136563};
    private static final int[] ORDER_DAY_SCOPE_IDS = {268, 274, 277, 280};
    private static final int SEATS = 50;

    private FeuerwacheOrderDaySeats() {}

    public static void restore() {
        try (Connection connection = openZmsConnection()) {
            LocalDate orderDay = orderDayFromDb(connection);
            int removedBookings = deleteBookingsOnOrderDay(connection, orderDay);
            int clearedSlots = clearSlotsOnOrderDay(connection, orderDay);
            bumpOpeningSeats(connection);
            recalculateSlots();
            ScenarioLogManager.getLogger()
                    .info(
                            "Restored Feuerwache V61 order-day {} (seats={}, deletedBookings={}, clearedSlots={})",
                            orderDay,
                            SEATS,
                            removedBookings,
                            clearedSlots);
        } catch (SQLException e) {
            throw new IllegalStateException("Failed to restore Feuerwache order-day seats", e);
        }
    }

    private static LocalDate orderDayFromDb(Connection connection) throws SQLException {
        String startColumn = columnExists(connection, "start_date") ? "start_date" : "Startdatum";
        try (PreparedStatement statement =
                connection.prepareStatement(
                        "SELECT `"
                                + startColumn
                                + "` FROM oeffnungszeit WHERE OeffnungszeitID = 136560 LIMIT 1")) {
            try (ResultSet rows = statement.executeQuery()) {
                if (!rows.next() || rows.getDate(1) == null) {
                    throw new SQLException(
                            "V61 order-day opening 136560 missing; run Flyway migrate first");
                }
                return rows.getDate(1).toLocalDate();
            }
        }
    }

    private static void bumpOpeningSeats(Connection connection) throws SQLException {
        boolean hasDeAppt = columnExists(connection, "Anzahlterminarbeitsplaetze");
        boolean hasEnAppt = columnExists(connection, "appointment_workstation_count");
        boolean hasDeWs = columnExists(connection, "Anzahlarbeitsplaetze");
        boolean hasEnWs = columnExists(connection, "workstation_count");
        if (!hasDeAppt && !hasEnAppt) {
            throw new SQLException("oeffnungszeit has neither DE nor EN appointment seat columns");
        }
        List<String> sets = new ArrayList<>();
        if (hasDeAppt) {
            sets.add("Anzahlterminarbeitsplaetze = ?");
        }
        if (hasEnAppt) {
            sets.add("appointment_workstation_count = ?");
        }
        if (hasDeWs) {
            sets.add("Anzahlarbeitsplaetze = ?");
        }
        if (hasEnWs) {
            sets.add("workstation_count = ?");
        }
        String sql =
                "UPDATE oeffnungszeit SET "
                        + String.join(", ", sets)
                        + " WHERE OeffnungszeitID IN (?,?,?,?)";
        try (PreparedStatement statement = connection.prepareStatement(sql)) {
            int index = 1;
            int bindCount = sets.size();
            for (int i = 0; i < bindCount; i++) {
                statement.setInt(index++, SEATS);
            }
            for (int id : ORDER_DAY_OPENING_IDS) {
                statement.setInt(index++, id);
            }
            int updated = statement.executeUpdate();
            ScenarioLogManager.getLogger()
                    .info("Updated {} V61 order-day opening row(s) to {} seats", updated, SEATS);
        }
    }

    private static int clearSlotsOnOrderDay(Connection connection, LocalDate orderDay)
            throws SQLException {
        if (!tableExists(connection, "slot")) {
            ScenarioLogManager.getLogger().warn("No slot table; skipping slot cleanup");
            return 0;
        }
        String scopeCol = columnExists(connection, "slot", "scopeID") ? "scopeID" : "scope_id";
        String yearCol = columnExists(connection, "slot", "year") ? "year" : null;
        if (yearCol == null) {
            ScenarioLogManager.getLogger().warn("slot table missing year column; skipping");
            return 0;
        }
        List<Long> slotIds = new ArrayList<>();
        String selectSql =
                "SELECT slotID FROM slot WHERE `"
                        + scopeCol
                        + "` IN (?,?,?,?) AND year = ? AND month = ? AND day = ?";
        try (PreparedStatement select = connection.prepareStatement(selectSql)) {
            int index = 1;
            for (int scopeId : ORDER_DAY_SCOPE_IDS) {
                select.setInt(index++, scopeId);
            }
            select.setInt(index++, orderDay.getYear());
            select.setInt(index++, orderDay.getMonthValue());
            select.setInt(index, orderDay.getDayOfMonth());
            try (ResultSet rs = select.executeQuery()) {
                while (rs.next()) {
                    slotIds.add(rs.getLong(1));
                }
            }
        }
        if (slotIds.isEmpty()) {
            ScenarioLogManager.getLogger()
                    .info("No slot rows on Feuerwache order day {} yet", orderDay);
            return 0;
        }
        if (tableExists(connection, "slot_process")) {
            for (Long slotId : slotIds) {
                try (PreparedStatement delete =
                        connection.prepareStatement("DELETE FROM slot_process WHERE slotID = ?")) {
                    delete.setLong(1, slotId);
                    delete.executeUpdate();
                }
            }
        }
        if (tableExists(connection, "slot_hiera")) {
            for (Long slotId : slotIds) {
                try (PreparedStatement delete =
                        connection.prepareStatement(
                                "DELETE FROM slot_hiera WHERE slotID = ? OR ancestorID = ?")) {
                    delete.setLong(1, slotId);
                    delete.setLong(2, slotId);
                    delete.executeUpdate();
                }
            }
        }
        int removed = 0;
        try (PreparedStatement delete =
                connection.prepareStatement(
                        "DELETE FROM slot WHERE `"
                                + scopeCol
                                + "` IN (?,?,?,?) AND year = ? AND month = ? AND day = ?")) {
            int index = 1;
            for (int scopeId : ORDER_DAY_SCOPE_IDS) {
                delete.setInt(index++, scopeId);
            }
            delete.setInt(index++, orderDay.getYear());
            delete.setInt(index++, orderDay.getMonthValue());
            delete.setInt(index, orderDay.getDayOfMonth());
            removed = delete.executeUpdate();
        }
        ScenarioLogManager.getLogger()
                .info("Cleared {} slot row(s) on Feuerwache order day {}", removed, orderDay);
        return removed;
    }

    private static int deleteBookingsOnOrderDay(Connection connection, LocalDate orderDay)
            throws SQLException {
        boolean hasStandort = columnExists(connection, "buerger", "StandortID");
        boolean hasScope = columnExists(connection, "buerger", "scope_id");
        boolean hasDatum = columnExists(connection, "buerger", "Datum");
        boolean hasDate = columnExists(connection, "buerger", "date");
        if ((!hasStandort && !hasScope) || (!hasDatum && !hasDate)) {
            ScenarioLogManager.getLogger()
                    .warn("Skipping order-day booking cleanup; unexpected buerger columns");
            return 0;
        }

        List<String> scopePredicates = new ArrayList<>();
        if (hasStandort) {
            scopePredicates.add("`StandortID` IN (?,?,?,?)");
        }
        if (hasScope) {
            scopePredicates.add("`scope_id` IN (?,?,?,?)");
        }
        List<String> datePredicates = new ArrayList<>();
        if (hasDatum) {
            datePredicates.add("DATE(`Datum`) = ?");
        }
        if (hasDate) {
            datePredicates.add("DATE(`date`) = ?");
        }
        String where =
                "("
                        + String.join(" OR ", scopePredicates)
                        + ") AND ("
                        + String.join(" OR ", datePredicates)
                        + ")";

        String idCol = columnExists(connection, "buerger", "BuergerID") ? "BuergerID" : "id";
        List<Long> processIds = new ArrayList<>();
        try (PreparedStatement select =
                connection.prepareStatement("SELECT `" + idCol + "` FROM buerger WHERE " + where)) {
            bindOrderDayFilter(select, hasStandort, hasScope, hasDatum, hasDate, orderDay);
            try (ResultSet rs = select.executeQuery()) {
                while (rs.next()) {
                    processIds.add(rs.getLong(1));
                }
            }
        }
        for (Long processId : processIds) {
            deleteProcessDependencies(connection, processId);
        }
        int removed;
        try (PreparedStatement delete =
                connection.prepareStatement("DELETE FROM buerger WHERE " + where)) {
            bindOrderDayFilter(delete, hasStandort, hasScope, hasDatum, hasDate, orderDay);
            removed = delete.executeUpdate();
        }
        ScenarioLogManager.getLogger()
                .info(
                        "Deleted {} buerger row(s) on Feuerwache order day {} (matched {})",
                        removed,
                        orderDay,
                        processIds.size());
        return removed;
    }

    private static void bindOrderDayFilter(
            PreparedStatement statement,
            boolean hasStandort,
            boolean hasScope,
            boolean hasDatum,
            boolean hasDate,
            LocalDate orderDay)
            throws SQLException {
        int index = 1;
        if (hasStandort) {
            for (int scopeId : ORDER_DAY_SCOPE_IDS) {
                statement.setInt(index++, scopeId);
            }
        }
        if (hasScope) {
            for (int scopeId : ORDER_DAY_SCOPE_IDS) {
                statement.setInt(index++, scopeId);
            }
        }
        Date sqlDay = Date.valueOf(orderDay);
        if (hasDatum) {
            statement.setDate(index++, sqlDay);
        }
        if (hasDate) {
            statement.setDate(index, sqlDay);
        }
    }

    private static void deleteProcessDependencies(Connection connection, long processId)
            throws SQLException {
        if (tableExists(connection, "slot_process")) {
            try (PreparedStatement statement =
                    connection.prepareStatement("DELETE FROM slot_process WHERE processID = ?")) {
                statement.setLong(1, processId);
                statement.executeUpdate();
            }
        }
        for (String table : new String[] {"buergeranliegen", "overview_calendar"}) {
            if (!tableExists(connection, table)) {
                continue;
            }
            String column = columnExists(connection, table, "BuergerID") ? "BuergerID" : "process_id";
            if (!columnExists(connection, table, column)) {
                continue;
            }
            try (PreparedStatement statement =
                    connection.prepareStatement(
                            "DELETE FROM `" + table + "` WHERE `" + column + "` = ?")) {
                statement.setLong(1, processId);
                statement.executeUpdate();
            }
        }
    }

    /**
     * Same fallback as {@code zmsautomation-test}: rebuild slots from openings under mocked time.
     */
    private static void recalculateSlots() {
        Path backend = Path.of(System.getProperty("user.dir")).resolve("../zmsbackend").normalize();
        if (!Files.isRegularFile(backend.resolve("bin/calculateSlots"))) {
            backend = Path.of("/var/www/html/zmsbackend");
        }
        Path script = backend.resolve("bin/calculateSlots").toAbsolutePath();
        if (!Files.isRegularFile(script)) {
            throw new IllegalStateException("calculateSlots not found at " + script);
        }
        ProcessBuilder builder =
                new ProcessBuilder(phpBinary(), script.toString(), "--commit", "--delete");
        builder.directory(backend.toFile());
        builder.environment().putIfAbsent("ZMS_ENV", "dev");
        builder.environment().put("ZMS_CRONROOT", "1");
        String timeAdjust = System.getenv("ZMS_TIMEADJUST");
        if (timeAdjust != null && !timeAdjust.isBlank()) {
            builder.environment().put("ZMS_TIMEADJUST", timeAdjust);
        }
        builder.redirectErrorStream(true);
        try {
            Process process = builder.start();
            String output = new String(process.getInputStream().readAllBytes(), StandardCharsets.UTF_8);
            boolean finished = process.waitFor(180, TimeUnit.SECONDS);
            if (!finished) {
                process.destroyForcibly();
                throw new IllegalStateException("calculateSlots timed out. output=" + truncate(output));
            }
            if (process.exitValue() != 0) {
                throw new IllegalStateException(
                        "calculateSlots exit=" + process.exitValue() + " output=" + truncate(output));
            }
            ScenarioLogManager.getLogger()
                    .info("calculateSlots --commit --delete OK: {}", truncate(output));
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
            throw new IllegalStateException("Interrupted while running calculateSlots", e);
        } catch (IOException e) {
            throw new IllegalStateException("Failed to run calculateSlots", e);
        }
    }

    private static String phpBinary() {
        String fromEnv = System.getenv("PHP_BINARY");
        if (fromEnv != null && !fromEnv.isBlank()) {
            return fromEnv.trim();
        }
        return "php";
    }

    private static String truncate(String text) {
        if (text == null) {
            return "";
        }
        String compact = text.replaceAll("\\s+", " ").trim();
        return compact.length() <= 800 ? compact : compact.substring(0, 800) + "…";
    }

    private static boolean columnExists(Connection connection, String column) throws SQLException {
        return columnExists(connection, "oeffnungszeit", column);
    }

    private static boolean columnExists(Connection connection, String table, String column)
            throws SQLException {
        try (PreparedStatement statement =
                connection.prepareStatement(
                        "SELECT 1 FROM information_schema.COLUMNS "
                                + "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? "
                                + "LIMIT 1")) {
            statement.setString(1, table);
            statement.setString(2, column);
            try (ResultSet rs = statement.executeQuery()) {
                return rs.next();
            }
        }
    }

    private static boolean tableExists(Connection connection, String table) throws SQLException {
        try (PreparedStatement statement =
                connection.prepareStatement(
                        "SELECT 1 FROM information_schema.TABLES "
                                + "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1")) {
            statement.setString(1, table);
            try (ResultSet rs = statement.executeQuery()) {
                return rs.next();
            }
        }
    }

    private static Connection openZmsConnection() throws SQLException {
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
