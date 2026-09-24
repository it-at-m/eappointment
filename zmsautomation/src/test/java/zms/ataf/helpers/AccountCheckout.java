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

    /** UI default {@code ataf}. Thirty-two threads plus one spare. */
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
            "ataf_17",
            "ataf_18",
            "ataf_19",
            "ataf_20",
            "ataf_21",
            "ataf_22",
            "ataf_23",
            "ataf_24",
            "ataf_25",
            "ataf_26",
            "ataf_27",
            "ataf_28",
            "ataf_29",
            "ataf_30",
            "ataf_31",
            "ataf_32",
            "ataf_33");

    /** API default {@code agent_queue}. One hundred twenty-eight threads plus one spare. */
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
            "agent_queue_33",
            "agent_queue_34",
            "agent_queue_35",
            "agent_queue_36",
            "agent_queue_37",
            "agent_queue_38",
            "agent_queue_39",
            "agent_queue_40",
            "agent_queue_41",
            "agent_queue_42",
            "agent_queue_43",
            "agent_queue_44",
            "agent_queue_45",
            "agent_queue_46",
            "agent_queue_47",
            "agent_queue_48",
            "agent_queue_49",
            "agent_queue_50",
            "agent_queue_51",
            "agent_queue_52",
            "agent_queue_53",
            "agent_queue_54",
            "agent_queue_55",
            "agent_queue_56",
            "agent_queue_57",
            "agent_queue_58",
            "agent_queue_59",
            "agent_queue_60",
            "agent_queue_61",
            "agent_queue_62",
            "agent_queue_63",
            "agent_queue_64",
            "agent_queue_65",
            "agent_queue_66",
            "agent_queue_67",
            "agent_queue_68",
            "agent_queue_69",
            "agent_queue_70",
            "agent_queue_71",
            "agent_queue_72",
            "agent_queue_73",
            "agent_queue_74",
            "agent_queue_75",
            "agent_queue_76",
            "agent_queue_77",
            "agent_queue_78",
            "agent_queue_79",
            "agent_queue_80",
            "agent_queue_81",
            "agent_queue_82",
            "agent_queue_83",
            "agent_queue_84",
            "agent_queue_85",
            "agent_queue_86",
            "agent_queue_87",
            "agent_queue_88",
            "agent_queue_89",
            "agent_queue_90",
            "agent_queue_91",
            "agent_queue_92",
            "agent_queue_93",
            "agent_queue_94",
            "agent_queue_95",
            "agent_queue_96",
            "agent_queue_97",
            "agent_queue_98",
            "agent_queue_99",
            "agent_queue_100",
            "agent_queue_101",
            "agent_queue_102",
            "agent_queue_103",
            "agent_queue_104",
            "agent_queue_105",
            "agent_queue_106",
            "agent_queue_107",
            "agent_queue_108",
            "agent_queue_109",
            "agent_queue_110",
            "agent_queue_111",
            "agent_queue_112",
            "agent_queue_113",
            "agent_queue_114",
            "agent_queue_115",
            "agent_queue_116",
            "agent_queue_117",
            "agent_queue_118",
            "agent_queue_119",
            "agent_queue_120",
            "agent_queue_121",
            "agent_queue_122",
            "agent_queue_123",
            "agent_queue_124",
            "agent_queue_125",
            "agent_queue_126",
            "agent_queue_127",
            "agent_queue_128",
            "agent_queue_129");

    /** Mail login id is the raw nutzer name. One hundred twenty-eight API threads plus one spare. */
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
            "_system_messenger_33",
            "_system_messenger_34",
            "_system_messenger_35",
            "_system_messenger_36",
            "_system_messenger_37",
            "_system_messenger_38",
            "_system_messenger_39",
            "_system_messenger_40",
            "_system_messenger_41",
            "_system_messenger_42",
            "_system_messenger_43",
            "_system_messenger_44",
            "_system_messenger_45",
            "_system_messenger_46",
            "_system_messenger_47",
            "_system_messenger_48",
            "_system_messenger_49",
            "_system_messenger_50",
            "_system_messenger_51",
            "_system_messenger_52",
            "_system_messenger_53",
            "_system_messenger_54",
            "_system_messenger_55",
            "_system_messenger_56",
            "_system_messenger_57",
            "_system_messenger_58",
            "_system_messenger_59",
            "_system_messenger_60",
            "_system_messenger_61",
            "_system_messenger_62",
            "_system_messenger_63",
            "_system_messenger_64",
            "_system_messenger_65",
            "_system_messenger_66",
            "_system_messenger_67",
            "_system_messenger_68",
            "_system_messenger_69",
            "_system_messenger_70",
            "_system_messenger_71",
            "_system_messenger_72",
            "_system_messenger_73",
            "_system_messenger_74",
            "_system_messenger_75",
            "_system_messenger_76",
            "_system_messenger_77",
            "_system_messenger_78",
            "_system_messenger_79",
            "_system_messenger_80",
            "_system_messenger_81",
            "_system_messenger_82",
            "_system_messenger_83",
            "_system_messenger_84",
            "_system_messenger_85",
            "_system_messenger_86",
            "_system_messenger_87",
            "_system_messenger_88",
            "_system_messenger_89",
            "_system_messenger_90",
            "_system_messenger_91",
            "_system_messenger_92",
            "_system_messenger_93",
            "_system_messenger_94",
            "_system_messenger_95",
            "_system_messenger_96",
            "_system_messenger_97",
            "_system_messenger_98",
            "_system_messenger_99",
            "_system_messenger_100",
            "_system_messenger_101",
            "_system_messenger_102",
            "_system_messenger_103",
            "_system_messenger_104",
            "_system_messenger_105",
            "_system_messenger_106",
            "_system_messenger_107",
            "_system_messenger_108",
            "_system_messenger_109",
            "_system_messenger_110",
            "_system_messenger_111",
            "_system_messenger_112",
            "_system_messenger_113",
            "_system_messenger_114",
            "_system_messenger_115",
            "_system_messenger_116",
            "_system_messenger_117",
            "_system_messenger_118",
            "_system_messenger_119",
            "_system_messenger_120",
            "_system_messenger_121",
            "_system_messenger_122",
            "_system_messenger_123",
            "_system_messenger_124",
            "_system_messenger_125",
            "_system_messenger_126",
            "_system_messenger_127",
            "_system_messenger_128",
            "_system_messenger_129");

    /** Citizen bookings are keyed by the Keycloak username. One hundred twenty-eight API threads plus one spare. */
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
            "citizen_33",
            "citizen_34",
            "citizen_35",
            "citizen_36",
            "citizen_37",
            "citizen_38",
            "citizen_39",
            "citizen_40",
            "citizen_41",
            "citizen_42",
            "citizen_43",
            "citizen_44",
            "citizen_45",
            "citizen_46",
            "citizen_47",
            "citizen_48",
            "citizen_49",
            "citizen_50",
            "citizen_51",
            "citizen_52",
            "citizen_53",
            "citizen_54",
            "citizen_55",
            "citizen_56",
            "citizen_57",
            "citizen_58",
            "citizen_59",
            "citizen_60",
            "citizen_61",
            "citizen_62",
            "citizen_63",
            "citizen_64",
            "citizen_65",
            "citizen_66",
            "citizen_67",
            "citizen_68",
            "citizen_69",
            "citizen_70",
            "citizen_71",
            "citizen_72",
            "citizen_73",
            "citizen_74",
            "citizen_75",
            "citizen_76",
            "citizen_77",
            "citizen_78",
            "citizen_79",
            "citizen_80",
            "citizen_81",
            "citizen_82",
            "citizen_83",
            "citizen_84",
            "citizen_85",
            "citizen_86",
            "citizen_87",
            "citizen_88",
            "citizen_89",
            "citizen_90",
            "citizen_91",
            "citizen_92",
            "citizen_93",
            "citizen_94",
            "citizen_95",
            "citizen_96",
            "citizen_97",
            "citizen_98",
            "citizen_99",
            "citizen_100",
            "citizen_101",
            "citizen_102",
            "citizen_103",
            "citizen_104",
            "citizen_105",
            "citizen_106",
            "citizen_107",
            "citizen_108",
            "citizen_109",
            "citizen_110",
            "citizen_111",
            "citizen_112",
            "citizen_113",
            "citizen_114",
            "citizen_115",
            "citizen_116",
            "citizen_117",
            "citizen_118",
            "citizen_119",
            "citizen_120",
            "citizen_121",
            "citizen_122",
            "citizen_123",
            "citizen_124",
            "citizen_125",
            "citizen_126",
            "citizen_127",
            "citizen_128",
            "citizen_129");

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
