package zms.ataf.ui.pages.citizenview.support;

import java.util.List;

/**
 * ATAF markers for Führerscheinstelle Allgemeinschalter under office 10308174:
 * <ul>
 *   <li>Scope 6 — Schalter A</li>
 *   <li>Scope 256 — Schalter B</li>
 *   <li>Scope 347 — Schalter C (hints in V54; 25-minute openings from after-hourly V001)</li>
 * </ul>
 * Distinct {@code scope.hint} (Kundenhinweis / Ort) and {@code infoForAppointment}
 * (Hinweis) come from Flyway V54. V55 gives A/B one internet seat on the scope-switch
 * day; after-hourly V002 keeps C off that day so a second same-time reserve still
 * switches A↔B. On other days a second reserve may land on any free Schalter.
 */
public final class FuehrerscheinstelleScopeHints {

    public static final String ZONE_A = "ATAF Kundenhinweis Schalter A";
    public static final String ZONE_B = "ATAF Kundenhinweis Schalter B";
    public static final String ZONE_C = "ATAF Kundenhinweis Schalter C";
    public static final String HINT_A = "ATAF Termin Hinweis Schalter A";
    public static final String HINT_B = "ATAF Termin Hinweis Schalter B";
    public static final String HINT_C = "ATAF Termin Hinweis Schalter C";
    public static final String OVERVIEW_HINT_HEADING = "Hinweis";
    public static final String DETAIL_CALLOUT_HEADER = "Hinweis zu Ihrem Termin";
    public static final String SELECTED_APPOINTMENT_HEADER = "Ausgewählter Termin";

    private static final List<String> CONCRETE = List.of("FS-A", "FS-B", "FS-C");

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
        return CONCRETE.contains(code);
    }

    public static List<String> concreteCodes() {
        return CONCRETE;
    }

    public static List<String> otherCodes(String code) {
        return CONCRETE.stream().filter(c -> !c.equals(code)).toList();
    }

    public static String zoneFor(String code) {
        if ("FS-A".equals(code)) {
            return ZONE_A;
        }
        if ("FS-B".equals(code)) {
            return ZONE_B;
        }
        if ("FS-C".equals(code)) {
            return ZONE_C;
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
        if ("FS-C".equals(code)) {
            return HINT_C;
        }
        throw new IllegalArgumentException("Unknown Führerscheinstelle code: " + code);
    }

    /**
     * Pair partner for A↔B scope-switch assertions. Prefer {@link #otherCodes} when C may
     * appear; this remains for call sites that still model a two-way switch.
     */
    public static String otherCode(String code) {
        if ("FS-A".equals(code)) {
            return "FS-B";
        }
        if ("FS-B".equals(code)) {
            return "FS-A";
        }
        if ("FS-C".equals(code)) {
            return "FS-A";
        }
        throw new IllegalArgumentException("Unknown Führerscheinstelle code: " + code);
    }

    /** Detect FS-A / FS-B / FS-C from painted Ort Kundenhinweis + Termin Hinweis. */
    public static String detectCode(String text) {
        if (text == null) {
            return null;
        }
        boolean zoneA = text.contains(ZONE_A);
        boolean zoneB = text.contains(ZONE_B);
        boolean zoneC = text.contains(ZONE_C);
        boolean hintA = text.contains(HINT_A);
        boolean hintB = text.contains(HINT_B);
        boolean hintC = text.contains(HINT_C);
        if (zoneA && hintA && !zoneB && !hintB && !zoneC && !hintC) {
            return "FS-A";
        }
        if (zoneB && hintB && !zoneA && !hintA && !zoneC && !hintC) {
            return "FS-B";
        }
        if (zoneC && hintC && !zoneA && !hintA && !zoneB && !hintB) {
            return "FS-C";
        }
        if (hintA && !hintB && !hintC) {
            return "FS-A";
        }
        if (hintB && !hintA && !hintC) {
            return "FS-B";
        }
        if (hintC && !hintA && !hintB) {
            return "FS-C";
        }
        if (zoneA && !zoneB && !zoneC) {
            return "FS-A";
        }
        if (zoneB && !zoneA && !zoneC) {
            return "FS-B";
        }
        if (zoneC && !zoneA && !zoneB) {
            return "FS-C";
        }
        return null;
    }

    public static boolean paintsFamilyHint(String text) {
        return text != null && (text.contains(HINT_A) || text.contains(HINT_B) || text.contains(HINT_C));
    }

    /** True when {@code text} shows this Schalter and none of the other concrete markers. */
    public static boolean paintsOnly(String text, String code) {
        if (text == null || !isConcrete(code)) {
            return false;
        }
        if (!text.contains(hintFor(code)) && !text.contains(zoneFor(code))) {
            return false;
        }
        for (String other : otherCodes(code)) {
            if (text.contains(hintFor(other)) || text.contains(zoneFor(other))) {
                return false;
            }
        }
        return true;
    }
}
