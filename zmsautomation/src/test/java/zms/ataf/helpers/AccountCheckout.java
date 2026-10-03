package zms.ataf.helpers;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Collections;
import java.util.LinkedHashMap;
import java.util.LinkedHashSet;
import java.util.List;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.locks.ReentrantLock;

import ataf.core.logging.ScenarioLogManager;

/**
 * One login at a time per ZMS account. A scenario checks the account out at login
 * and {@link zms.ataf.hooks.AccountCheckoutHook} returns it when the scenario ends.
 * A second scenario that needs the same account waits at its login step.
 */
public final class AccountCheckout {

    private static final ConcurrentHashMap<String, ReentrantLock> LOCKS = new ConcurrentHashMap<>();
    private static final ThreadLocal<LinkedHashSet<String>> HELD = ThreadLocal.withInitial(LinkedHashSet::new);
    private static final ThreadLocal<Integer> DEPARTMENT = new ThreadLocal<>();

    /**
     * Superuser nutzer accounts ({@code Berechtigung} 90). A feature that says {@code ataf} takes a free one.
     * Sixteen UI threads plus one spare. Index 0 keeps the feature counter.
     */
    private static final List<String> SUPERUSERS = List.of(
            "ataf_superuser_1",
            "ataf_superuser_2",
            "ataf_superuser_3",
            "ataf_superuser_4",
            "ataf_superuser_5",
            "ataf_superuser_6",
            "ataf_superuser_7",
            "ataf_superuser_8",
            "ataf_superuser_9",
            "ataf_superuser_10",
            "ataf_superuser_11",
            "ataf_superuser_12",
            "ataf_superuser_13",
            "ataf_superuser_14",
            "ataf_superuser_15",
            "ataf_superuser_16",
            "ataf_superuser_17"
    );

    /**
     * Nutzer accounts with the role {@code agent_queue} (Sachbearbeitung Standard).
     * Not workstations and not a permission. A feature that says {@code agent_queue} takes a free one.
     * Thirty-two API threads plus one spare.
     */
    private static final List<String> AGENT_QUEUE_USERS = List.of(
            "ataf_agent_queue_1",
            "ataf_agent_queue_2",
            "ataf_agent_queue_3",
            "ataf_agent_queue_4",
            "ataf_agent_queue_5",
            "ataf_agent_queue_6",
            "ataf_agent_queue_7",
            "ataf_agent_queue_8",
            "ataf_agent_queue_9",
            "ataf_agent_queue_10",
            "ataf_agent_queue_11",
            "ataf_agent_queue_12",
            "ataf_agent_queue_13",
            "ataf_agent_queue_14",
            "ataf_agent_queue_15",
            "ataf_agent_queue_16",
            "ataf_agent_queue_17",
            "ataf_agent_queue_18",
            "ataf_agent_queue_19",
            "ataf_agent_queue_20",
            "ataf_agent_queue_21",
            "ataf_agent_queue_22",
            "ataf_agent_queue_23",
            "ataf_agent_queue_24",
            "ataf_agent_queue_25",
            "ataf_agent_queue_26",
            "ataf_agent_queue_27",
            "ataf_agent_queue_28",
            "ataf_agent_queue_29",
            "ataf_agent_queue_30",
            "ataf_agent_queue_31",
            "ataf_agent_queue_32",
            "ataf_agent_queue_33"
    );

    /**
     * Mail nutzer accounts. The login id is the raw name. A feature that says {@code _system_messenger}
     * takes a free one. Thirty-two API threads plus one spare.
     */
    private static final List<String> MESSENGERS = List.of(
            "ataf__system_messenger_1",
            "ataf__system_messenger_2",
            "ataf__system_messenger_3",
            "ataf__system_messenger_4",
            "ataf__system_messenger_5",
            "ataf__system_messenger_6",
            "ataf__system_messenger_7",
            "ataf__system_messenger_8",
            "ataf__system_messenger_9",
            "ataf__system_messenger_10",
            "ataf__system_messenger_11",
            "ataf__system_messenger_12",
            "ataf__system_messenger_13",
            "ataf__system_messenger_14",
            "ataf__system_messenger_15",
            "ataf__system_messenger_16",
            "ataf__system_messenger_17",
            "ataf__system_messenger_18",
            "ataf__system_messenger_19",
            "ataf__system_messenger_20",
            "ataf__system_messenger_21",
            "ataf__system_messenger_22",
            "ataf__system_messenger_23",
            "ataf__system_messenger_24",
            "ataf__system_messenger_25",
            "ataf__system_messenger_26",
            "ataf__system_messenger_27",
            "ataf__system_messenger_28",
            "ataf__system_messenger_29",
            "ataf__system_messenger_30",
            "ataf__system_messenger_31",
            "ataf__system_messenger_32",
            "ataf__system_messenger_33"
    );

    /**
     * Citizen Keycloak users. Bookings are keyed by this username. A feature that says {@code citizen}
     * takes a free one. Thirty-two API threads plus one spare.
     */
    private static final List<String> CITIZENS = List.of(
            "ataf_citizen_1",
            "ataf_citizen_2",
            "ataf_citizen_3",
            "ataf_citizen_4",
            "ataf_citizen_5",
            "ataf_citizen_6",
            "ataf_citizen_7",
            "ataf_citizen_8",
            "ataf_citizen_9",
            "ataf_citizen_10",
            "ataf_citizen_11",
            "ataf_citizen_12",
            "ataf_citizen_13",
            "ataf_citizen_14",
            "ataf_citizen_15",
            "ataf_citizen_16",
            "ataf_citizen_17",
            "ataf_citizen_18",
            "ataf_citizen_19",
            "ataf_citizen_20",
            "ataf_citizen_21",
            "ataf_citizen_22",
            "ataf_citizen_23",
            "ataf_citizen_24",
            "ataf_citizen_25",
            "ataf_citizen_26",
            "ataf_citizen_27",
            "ataf_citizen_28",
            "ataf_citizen_29",
            "ataf_citizen_30",
            "ataf_citizen_31",
            "ataf_citizen_32",
            "ataf_citizen_33"
    );

    private AccountCheckout() {
    }

    /**
     * Nutzer name updated by workstation password login and by admin/statistic OIDC
     * ({@code preferred_username@keycloak}).
     */
    public static String workstationAccountId(String loginName) {
        if (loginName.endsWith("@keycloak")) {
            return loginName;
        }
        return loginName + "@keycloak";
    }

    public static void checkoutWorkstation(String loginName) {
        checkout(workstationAccountId(loginName));
    }

    /**
     * {@code ataf} takes a free {@code ataf_superuser_*} account. {@code agent_queue} takes a free
     * {@code ataf_agent_queue_*} account. {@code user_admin} takes a free {@code ataf_user_admin_*}
     * account at random. Each of those accounts is Benutzerverwaltung for one department.
     * The scenario uses that account's department. It waits while every such account is in use.
     * Any other name stays on that exact account. Returns the Keycloak username to type.
     */
    public static String assignWorkstationLogin(String loginName) {
        String bare = stripKeycloak(loginName);
        if ("ataf".equals(bare)) {
            return checkoutFree(SUPERUSERS, true);
        }
        if ("agent_queue".equals(bare)) {
            return checkoutFree(AGENT_QUEUE_USERS, true);
        }
        if ("user_admin".equals(bare)) {
            return assignRandomUserAdminLogin(userAdminDepartments());
        }
        checkoutWorkstation(bare);
        return bare;
    }

    /**
     * Shuffles {@code loginToDepartment} and checks out the first free account.
     * The remembered department is the one that account has.
     */
    public static String assignRandomUserAdminLogin(Map<String, Integer> loginToDepartment) {
        if (loginToDepartment == null || loginToDepartment.isEmpty()) {
            throw new IllegalStateException("No user_admin pool account has a department.");
        }
        List<String> logins = new ArrayList<>(loginToDepartment.keySet());
        Collections.shuffle(logins);
        Map<String, Integer> shuffled = new LinkedHashMap<>();
        for (String login : logins) {
            shuffled.put(login, loginToDepartment.get(login));
        }
        return assignUserAdminLogin(shuffled);
    }

    /**
     * Checks out one of {@code loginToDepartment} and remembers that account's department.
     * Waits until one of those accounts is free. An empty map means none of them has a department.
     * Map order is the order in which free accounts are tried.
     */
    public static String assignUserAdminLogin(Map<String, Integer> loginToDepartment) {
        if (loginToDepartment == null || loginToDepartment.isEmpty()) {
            throw new IllegalStateException("No user_admin pool account has a department.");
        }
        String login = checkoutFree(new ArrayList<>(loginToDepartment.keySet()), true);
        Integer departmentId = loginToDepartment.get(login);
        if (departmentId == null || departmentId == 0) {
            throw new IllegalStateException("Checked out " + login + " without a department.");
        }
        DEPARTMENT.set(departmentId);
        log("Using department " + departmentId + " from " + login);
        return login;
    }

    /** Department of the user-admin pool account this thread checked out, or null. */
    public static Integer checkedOutDepartmentId() {
        return DEPARTMENT.get();
    }

    /**
     * Default {@code _system_messenger} takes a free messenger. The returned id is posted as-is.
     */
    public static String assignMessengerLogin(String loginName) {
        if ("_system_messenger".equals(loginName)) {
            return checkoutFree(MESSENGERS, false);
        }
        checkout(loginName);
        return loginName;
    }

    /**
     * Default {@code citizen} takes a free citizen so parallel bookings do not share one external id.
     */
    public static String assignCitizenLogin(String loginName) {
        if ("citizen".equals(loginName)) {
            return checkoutFree(CITIZENS, false);
        }
        checkout(loginName);
        return loginName;
    }

    /**
     * {@code ataf_superuser_1} keeps the counter from the feature. Later superusers use that counter
     * plus 100 times their pool index, so two queue scenarios do not sit on one Platz.
     */
    public static String queueDesk(String requestedDesk) {
        return offsetDesk(requestedDesk, SUPERUSERS, true);
    }

    /**
     * API updates for the {@code agent_queue} role use the same counter string. Spare users
     * take that counter plus 100 times their pool index.
     */
    public static String workstationCounter(String requestedCounter) {
        return offsetDesk(requestedCounter, AGENT_QUEUE_USERS, true);
    }

    private static String offsetDesk(String requested, List<String> pool, boolean workstation) {
        LinkedHashSet<String> held = HELD.get();
        int index = 0;
        for (int i = 0; i < pool.size(); i++) {
            if (held.contains(accountId(pool.get(i), workstation))) {
                index = i;
                break;
            }
        }
        if (index == 0 || requested == null) {
            return requested;
        }
        try {
            int desk = Integer.parseInt(requested.trim());
            return Integer.toString(desk + index * 100);
        } catch (NumberFormatException ignored) {
            return requested;
        }
    }

    /**
     * Blocks until {@code accountId} is free, then holds it for this thread.
     * A second call on the same thread does not lock again.
     */
    public static void checkout(String accountId) {
        if (accountId == null || accountId.isBlank()) {
            throw new IllegalArgumentException("account id is blank");
        }
        LinkedHashSet<String> held = HELD.get();
        if (held.contains(accountId)) {
            return;
        }
        ReentrantLock lock = LOCKS.computeIfAbsent(accountId, ignored -> new ReentrantLock(true));
        if (lock.isLocked()) {
            log("Waiting for account " + accountId);
        }
        lock.lock();
        held.add(accountId);
        log("Checked out account " + accountId);
    }

    private static String checkoutFree(List<String> logins, boolean workstation) {
        LinkedHashSet<String> held = HELD.get();
        for (String login : logins) {
            if (held.contains(accountId(login, workstation))) {
                return login;
            }
        }
        while (true) {
            for (String login : logins) {
                String accountId = accountId(login, workstation);
                ReentrantLock lock = LOCKS.computeIfAbsent(accountId, ignored -> new ReentrantLock(true));
                if (lock.tryLock()) {
                    held.add(accountId);
                    log("Checked out account " + accountId);
                    return login;
                }
            }
            String first = logins.get(0);
            String firstId = accountId(first, workstation);
            log("Waiting for a free account among " + logins);
            LOCKS.get(firstId).lock();
            if (!held.contains(firstId)) {
                held.add(firstId);
                log("Checked out account " + firstId);
                return first;
            }
            LOCKS.get(firstId).unlock();
        }
    }

    private static String accountId(String login, boolean workstation) {
        return workstation ? workstationAccountId(login) : login;
    }

    private static String stripKeycloak(String loginName) {
        if (loginName != null && loginName.endsWith("@keycloak")) {
            return loginName.substring(0, loginName.length() - "@keycloak".length());
        }
        return loginName;
    }

    public static void releaseAll() {
        DEPARTMENT.remove();
        LinkedHashSet<String> held = HELD.get();
        List<String> keys = new ArrayList<>(held);
        HELD.remove();
        for (int i = keys.size() - 1; i >= 0; i--) {
            String accountId = keys.get(i);
            ReentrantLock lock = LOCKS.get(accountId);
            if (lock != null && lock.isHeldByCurrentThread()) {
                lock.unlock();
                log("Released account " + accountId);
            }
        }
    }

    private static Map<String, Integer> userAdminDepartments() {
        Map<String, Integer> byLogin = new LinkedHashMap<>();
        String sql = "SELECT Name, BehoerdenID FROM nutzer "
                + "WHERE Name LIKE 'ataf_user_admin_%@keycloak' AND BehoerdenID <> 0 "
                + "ORDER BY NutzerID";
        try (Connection connection = openConnection();
                PreparedStatement statement = connection.prepareStatement(sql);
                ResultSet rows = statement.executeQuery()) {
            while (rows.next()) {
                int departmentId = rows.getInt("BehoerdenID");
                if (departmentId != 0) {
                    byLogin.put(stripKeycloak(rows.getString("Name")), departmentId);
                }
            }
        } catch (SQLException e) {
            throw new IllegalStateException("Could not read the user_admin pool departments.", e);
        }
        return byLogin;
    }

    private static Connection openConnection() throws SQLException {
        String host = envOrDefault("MYSQL_HOST", "db");
        String port = mysqlPort(envOrDefault("MYSQL_PORT", "3306"));
        String database = envOrDefault("MYSQL_DATABASE", "db");
        String user = envOrDefault("MYSQL_USER", "db");
        String url = "jdbc:mysql://" + host + ":" + port + "/" + database;
        log("Opening suite database " + url + " as " + user);
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

    private static void log(String message) {
        try {
            ScenarioLogManager.getLogger().info(message);
        } catch (Throwable ignored) {
            // ScenarioLogManager is not usable off the Cucumber thread.
        }
    }
}
