package zms.ataf.ui.pages.citizenview.support;

/**
 * ATAF markers for Führerscheinstelle Allgemeinschalter under office 10308174:
 * <ul>
 *   <li>Scope 6 — Schalter A</li>
 *   <li>Scope 256 — Schalter B</li>
 * </ul>
 * Distinct {@code scope.hint} (Kundenhinweis / Ort) and {@code infoForAppointment}
 * (Hinweis) come from Flyway V51. One internet seat each so a second same-time
 * reserve switches Schalter.
 */
public final class FuehrerscheinstelleScopeHints {

    public static final String ZONE_A = "ATAF Kundenhinweis Schalter A";
    public static final String ZONE_B = "ATAF Kundenhinweis Schalter B";
    public static final String HINT_A = "ATAF Termin Hinweis Schalter A";
    public static final String HINT_B = "ATAF Termin Hinweis Schalter B";
    public static final String OVERVIEW_HINT_HEADING = "Hinweis";
    public static final String DETAIL_CALLOUT_HEADER = "Hinweis zu Ihrem Termin";
    public static final String SELECTED_APPOINTMENT_HEADER = "Ausgewählter Termin";

    private static String rememberedApiSchalterCode;

    private FuehrerscheinstelleScopeHints() {}

    public static void rememberApiSchalterCode(String code) {
        rememberedApiSchalterCode = code;
    }

    public static String rememberedApiSchalterCode() {
        return rememberedApiSchalterCode;
    }

    public static boolean isSelectionFamily(String code) {
        return "FS".equals(code);
    }

    public static boolean isConcrete(String code) {
        return "FS-A".equals(code) || "FS-B".equals(code);
    }

    public static String zoneFor(String code) {
        if ("FS-A".equals(code)) {
            return ZONE_A;
        }
        if ("FS-B".equals(code)) {
            return ZONE_B;
        }
        throw new IllegalArgumentException("Unknown Führerscheinstelle code: " + code);
    }

    public static String hintFor(String code) {
        if ("FS-A".equals(code)) {
            return HINT_A;
        }
        if ("FS-B".equals(code)) {
            return HINT_B;
        }
        throw new IllegalArgumentException("Unknown Führerscheinstelle code: " + code);
    }

    public static String otherCode(String code) {
        if ("FS-A".equals(code)) {
            return "FS-B";
        }
        if ("FS-B".equals(code)) {
            return "FS-A";
        }
        throw new IllegalArgumentException("Unknown Führerscheinstelle code: " + code);
    }

    /** Detect FS-A / FS-B from painted Ort Kundenhinweis + Termin Hinweis. */
    public static String detectCode(String text) {
        if (text == null) {
            return null;
        }
        boolean zoneA = text.contains(ZONE_A);
        boolean zoneB = text.contains(ZONE_B);
        boolean hintA = text.contains(HINT_A);
        boolean hintB = text.contains(HINT_B);
        if (zoneA && hintA && !zoneB && !hintB) {
            return "FS-A";
        }
        if (zoneB && hintB && !zoneA && !hintA) {
            return "FS-B";
        }
        if (hintA && !hintB) {
            return "FS-A";
        }
        if (hintB && !hintA) {
            return "FS-B";
        }
        if (zoneA && !zoneB) {
            return "FS-A";
        }
        if (zoneB && !zoneA) {
            return "FS-B";
        }
        return null;
    }

    public static boolean paintsFamilyHint(String text) {
        return text != null && (text.contains(HINT_A) || text.contains(HINT_B));
    }
}
