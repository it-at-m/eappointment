package zms.ataf.helpers;

import java.util.ArrayList;
import java.util.LinkedHashSet;
import java.util.List;
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

    /** UI default {@code ataf}. Sixteen threads plus one spare. */
    private static final List<String> SUPERUSERS = List.of(
            "ataf",
            "ataf_2",
            "ataf_3",
            "ataf_4",
            "ataf_5",
            "ataf_6",
            "ataf_7",
            "ataf_8",
            "ataf_9",
            "ataf_10",
            "ataf_11",
            "ataf_12",
            "ataf_13",
            "ataf_14",
            "ataf_15",
            "ataf_16",
            "ataf_17"
    );

    /** API default {@code agent_queue}. Thirty-two threads plus one spare. */
    private static final List<String> WORKSTATIONS = List.of(
            "agent_queue",
            "agent_queue_2",
            "agent_queue_3",
            "agent_queue_4",
            "agent_queue_5",
            "agent_queue_6",
            "agent_queue_7",
            "agent_queue_8",
            "agent_queue_9",
            "agent_queue_10",
            "agent_queue_11",
            "agent_queue_12",
            "agent_queue_13",
            "agent_queue_14",
            "agent_queue_15",
            "agent_queue_16",
            "agent_queue_17",
            "agent_queue_18",
            "agent_queue_19",
            "agent_queue_20",
            "agent_queue_21",
            "agent_queue_22",
            "agent_queue_23",
            "agent_queue_24",
            "agent_queue_25",
            "agent_queue_26",
            "agent_queue_27",
            "agent_queue_28",
            "agent_queue_29",
            "agent_queue_30",
            "agent_queue_31",
            "agent_queue_32",
            "agent_queue_33"
    );

    /** Mail login id is the raw nutzer name. Thirty-two API threads plus one spare. */
    private static final List<String> MESSENGERS = List.of(
            "_system_messenger",
            "_system_messenger_2",
            "_system_messenger_3",
            "_system_messenger_4",
            "_system_messenger_5",
            "_system_messenger_6",
            "_system_messenger_7",
            "_system_messenger_8",
            "_system_messenger_9",
            "_system_messenger_10",
            "_system_messenger_11",
            "_system_messenger_12",
            "_system_messenger_13",
            "_system_messenger_14",
            "_system_messenger_15",
            "_system_messenger_16",
            "_system_messenger_17",
            "_system_messenger_18",
            "_system_messenger_19",
            "_system_messenger_20",
            "_system_messenger_21",
            "_system_messenger_22",
            "_system_messenger_23",
            "_system_messenger_24",
            "_system_messenger_25",
            "_system_messenger_26",
            "_system_messenger_27",
            "_system_messenger_28",
            "_system_messenger_29",
            "_system_messenger_30",
            "_system_messenger_31",
            "_system_messenger_32",
            "_system_messenger_33"
    );

    /** Citizen bookings are keyed by the Keycloak username. Thirty-two API threads plus one spare. */
    private static final List<String> CITIZENS = List.of(
            "citizen",
            "citizen_2",
            "citizen_3",
            "citizen_4",
            "citizen_5",
            "citizen_6",
            "citizen_7",
            "citizen_8",
            "citizen_9",
            "citizen_10",
            "citizen_11",
            "citizen_12",
            "citizen_13",
            "citizen_14",
            "citizen_15",
            "citizen_16",
            "citizen_17",
            "citizen_18",
            "citizen_19",
            "citizen_20",
            "citizen_21",
            "citizen_22",
            "citizen_23",
            "citizen_24",
            "citizen_25",
            "citizen_26",
            "citizen_27",
            "citizen_28",
            "citizen_29",
            "citizen_30",
            "citizen_31",
            "citizen_32",
            "citizen_33"
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
     * Default {@code ataf} and {@code agent_queue} logins take a free member of that pool.
     * Any other name stays on that exact account. Returns the Keycloak username to type.
     */
    public static String assignWorkstationLogin(String loginName) {
        String bare = stripKeycloak(loginName);
        if ("ataf".equals(bare)) {
            return checkoutFree(SUPERUSERS, true);
        }
        if ("agent_queue".equals(bare)) {
            return checkoutFree(WORKSTATIONS, true);
        }
        checkoutWorkstation(bare);
        return bare;
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
     * Feature counters stay as written for {@code ataf}. Spare superusers use the same counter
     * plus 100 times their pool index, so two queue scenarios do not sit on one Platz.
     */
    public static String queueDesk(String requestedDesk) {
        return offsetDesk(requestedDesk, SUPERUSERS, true);
    }

    /**
     * API workstation updates use the same counter string. Spare {@code agent_queue} users
     * take that counter plus 100 times their pool index.
     */
    public static String workstationCounter(String requestedCounter) {
        return offsetDesk(requestedCounter, WORKSTATIONS, true);
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

    private static void log(String message) {
        try {
            ScenarioLogManager.getLogger().info(message);
        } catch (Throwable ignored) {
            // ScenarioLogManager is not usable off the Cucumber thread.
        }
    }
}
