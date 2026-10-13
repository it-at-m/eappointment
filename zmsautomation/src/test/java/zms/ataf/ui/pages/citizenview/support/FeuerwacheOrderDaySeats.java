package zms.ataf.ui.pages.citizenview.support;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.LocalDate;

import ataf.core.logging.ScenarioLogManager;

/**
 * Restores Flyway V61 day+7 seats for Pasing/Föhring/Neuperlach/Riem after a parallel
 * booked-out scenario reserved every appointment on those offices.
 */
public final class FeuerwacheOrderDaySeats {

    /** Matches V61 OeffnungszeitIDs. */
    private static final int[] ORDER_DAY_OPENING_IDS = {136560, 136561, 136562, 136563};

    /** StandortIDs: 268 Pasing, 274 Föhring, 277 Neuperlach, 280 Riem. */
    private static final int[] ORDER_DAY_SCOPE_IDS = {268, 274, 277, 280};

    private static final int SEATS = 20;

    private FeuerwacheOrderDaySeats() {}

    public static void restore() {
        try (Connection connection = openZmsConnection()) {
            LocalDate orderDay = orderDayFromDb(connection);
            bumpOpeningSeats(connection);
            deleteBookingsOnOrderDay(connection, orderDay);
            ScenarioLogManager.getLogger()
                    .info(
                            "Restored Feuerwache V61 order-day seats for {} ({} appointment seats)",
                            orderDay,
                            SEATS);
        } catch (SQLException e) {
            throw new IllegalStateException("Failed to restore Feuerwache order-day seats", e);
        }
    }

    /** Prefer the day Flyway V61 wrote (OeffnungszeitID 136560), not JVM "today"+7. */
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
        boolean hasDe = columnExists(connection, "Anzahlterminarbeitsplaetze");
        boolean hasEn = columnExists(connection, "appointment_workstation_count");
        if (!hasDe && !hasEn) {
            throw new SQLException("oeffnungszeit has neither DE nor EN appointment seat columns");
        }
        StringBuilder sql = new StringBuilder("UPDATE oeffnungszeit SET ");
        if (hasDe && hasEn) {
            sql.append("Anzahlterminarbeitsplaetze = ?, appointment_workstation_count = ?");
        } else if (hasEn) {
            sql.append("appointment_workstation_count = ?");
        } else {
            sql.append("Anzahlterminarbeitsplaetze = ?");
        }
        sql.append(" WHERE OeffnungszeitID IN (?,?,?,?)");
        try (PreparedStatement statement = connection.prepareStatement(sql.toString())) {
            int index = 1;
            statement.setInt(index++, SEATS);
            if (hasDe && hasEn) {
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

    private static void deleteBookingsOnOrderDay(Connection connection, LocalDate orderDay)
            throws SQLException {
        boolean hasStandort = columnExists(connection, "buerger", "StandortID");
        boolean hasScope = columnExists(connection, "buerger", "scope_id");
        boolean hasDatum = columnExists(connection, "buerger", "Datum");
        boolean hasDate = columnExists(connection, "buerger", "date");
        if ((!hasStandort && !hasScope) || (!hasDatum && !hasDate)) {
            ScenarioLogManager.getLogger()
                    .warn("Skipping order-day booking cleanup; unexpected buerger columns");
            return;
        }
        String scopeCol = hasStandort ? "StandortID" : "scope_id";
        String dateCol = hasDatum ? "Datum" : "date";
        String placeholders = "?,?,?,?";
        String idCol = columnExists(connection, "buerger", "BuergerID") ? "BuergerID" : "id";
        String selectSql =
                "SELECT `"
                        + idCol
                        + "` FROM buerger WHERE `"
                        + scopeCol
                        + "` IN ("
                        + placeholders
                        + ") AND `"
                        + dateCol
                        + "` = ?";
        try (PreparedStatement select = connection.prepareStatement(selectSql)) {
            int index = 1;
            for (int scopeId : ORDER_DAY_SCOPE_IDS) {
                select.setInt(index++, scopeId);
            }
            select.setString(index, orderDay.toString());
            try (ResultSet rs = select.executeQuery()) {
                while (rs.next()) {
                    long processId = rs.getLong(1);
                    deleteProcessDependencies(connection, processId);
                }
            }
        }
        String deleteSql =
                "DELETE FROM buerger WHERE `"
                        + scopeCol
                        + "` IN ("
                        + placeholders
                        + ") AND `"
                        + dateCol
                        + "` = ?";
        try (PreparedStatement delete = connection.prepareStatement(deleteSql)) {
            int index = 1;
            for (int scopeId : ORDER_DAY_SCOPE_IDS) {
                delete.setInt(index++, scopeId);
            }
            delete.setString(index, orderDay.toString());
            int removed = delete.executeUpdate();
            ScenarioLogManager.getLogger()
                    .info("Deleted {} buerger row(s) on Feuerwache order day {}", removed, orderDay);
        }
    }

    private static void deleteProcessDependencies(Connection connection, long processId)
            throws SQLException {
        for (String table : new String[] {"buergeranliegen", "overview_calendar"}) {
            if (!tableExists(connection, table)) {
                continue;
            }
            String column = tableExistsColumn(connection, table, "BuergerID") ? "BuergerID" : "process_id";
            if (!tableExistsColumn(connection, table, column)) {
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

    private static boolean tableExistsColumn(Connection connection, String table, String column)
            throws SQLException {
        return columnExists(connection, table, column);
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
