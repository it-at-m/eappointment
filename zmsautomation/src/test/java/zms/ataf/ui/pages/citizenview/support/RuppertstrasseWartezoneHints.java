package zms.ataf.ui.pages.citizenview.support;

/**
 * ATAF markers for Ruppertstraße scopes:
 * <ul>
 *   <li>Hauptkalender 10489: 160 (WB04) / 181 (WB03) with distinct ATAF HTML — Flyway V50</li>
 *   <li>Passkalender 10502: 172 (WB04) / 184 (WB03) with plain Passfoto Hinweis — Flyway V5/V6</li>
 * </ul>
 */
public final class RuppertstrasseWartezoneHints {

    public static final String ZONE_WB03 = "Wartebereich 03";
    public static final String ZONE_WB04 = "Wartebereich 04";
    public static final String HINT_WB03 = "ATAF Hinweis WB03";
    public static final String HINT_WB04 = "ATAF Hinweis WB04";
    public static final String HINT_PASSFOTO = "Hinweis zum Passfoto";
    public static final String LINK_WB03 = "ATAF Link WB03";
    public static final String LINK_WB04 = "ATAF Link WB04";
    public static final String ITALIC_WB03 = "kursiv WB03";
    public static final String ITALIC_WB04 = "kursiv WB04";
    public static final String BOLD_WB03 = "fett WB03";
    public static final String BOLD_WB04 = "fett WB04";
    public static final String HINT_HREF = "https://www.wikipedia.de/";
    public static final String DETAIL_CALLOUT_HEADER = "Hinweis zu Ihrem Termin";
    public static final String OVERVIEW_HINT_HEADING = "Hinweis";
    public static final String SELECTED_APPOINTMENT_HEADER = "Ausgewählter Termin";

    /** Shared between UI and Citizen API scenarios in the same JVM run. */
    private static String rememberedApiWartezoneCode;

    private RuppertstrasseWartezoneHints() {}

    public static void rememberApiWartezoneCode(String code) {
        rememberedApiWartezoneCode = code;
    }

    public static String rememberedApiWartezoneCode() {
        return rememberedApiWartezoneCode;
    }

    /** {@code true} for ATAF HTML markers (WB03/WB04); {@code false} for plain Passfoto. */
    public static boolean isAtafHtmlHint(String code) {
        return "WB03".equals(code) || "WB04".equals(code);
    }

    /**
     * Ausgewählter Termin only paints {@code infoForAppointment}, not {@code scope.hint}
     * (Wartebereich). Passfoto scopes share one hint, so selection uses {@code PASSFOTO};
     * overview/detail then pin the concrete WB03/WB04-PASSFOTO code from Ort.
     */
    public static boolean isPassfotoHint(String code) {
        return "PASSFOTO".equals(code)
                || "WB03-PASSFOTO".equals(code)
                || "WB04-PASSFOTO".equals(code);
    }

    public static String zoneFor(String code) {
        if ("WB03".equals(code) || "WB03-PASSFOTO".equals(code)) {
            return ZONE_WB03;
        }
        if ("WB04".equals(code) || "WB04-PASSFOTO".equals(code)) {
            return ZONE_WB04;
        }
        throw new IllegalArgumentException("Unknown Wartezone code: " + code);
    }

    public static String hintFor(String code) {
        if ("WB03".equals(code)) {
            return HINT_WB03;
        }
        if ("WB04".equals(code)) {
            return HINT_WB04;
        }
        if (isPassfotoHint(code)) {
            return HINT_PASSFOTO;
        }
        throw new IllegalArgumentException("Unknown Wartezone code: " + code);
    }

    public static String linkLabelFor(String code) {
        if ("WB03".equals(code)) {
            return LINK_WB03;
        }
        if ("WB04".equals(code)) {
            return LINK_WB04;
        }
        throw new IllegalArgumentException("No ATAF link label for Wartezone code: " + code);
    }

    public static String italicFor(String code) {
        if ("WB03".equals(code)) {
            return ITALIC_WB03;
        }
        if ("WB04".equals(code)) {
            return ITALIC_WB04;
        }
        throw new IllegalArgumentException("No ATAF italic for Wartezone code: " + code);
    }

    public static String boldFor(String code) {
        if ("WB03".equals(code)) {
            return BOLD_WB03;
        }
        if ("WB04".equals(code)) {
            return BOLD_WB04;
        }
        throw new IllegalArgumentException("No ATAF bold for Wartezone code: " + code);
    }

    public static String otherCode(String code) {
        if ("WB03".equals(code)) {
            return "WB04";
        }
        if ("WB04".equals(code)) {
            return "WB03";
        }
        if ("WB03-PASSFOTO".equals(code)) {
            return "WB04-PASSFOTO";
        }
        if ("WB04-PASSFOTO".equals(code)) {
            return "WB03-PASSFOTO";
        }
        throw new IllegalArgumentException("Unknown Wartezone code: " + code);
    }

    /** Resolve WB03-PASSFOTO / WB04-PASSFOTO from overview/detail Ort text + Passfoto hint. */
    public static String detectPassfotoCode(String text) {
        if (text == null || !text.contains(HINT_PASSFOTO)) {
            return null;
        }
        boolean zone03 = text.contains(ZONE_WB03);
        boolean zone04 = text.contains(ZONE_WB04);
        if (zone03 && !zone04) {
            return "WB03-PASSFOTO";
        }
        if (zone04 && !zone03) {
            return "WB04-PASSFOTO";
        }
        return null;
    }

    /** Detect WB03/WB04 from painted page text that includes both Wartezone and ATAF hint. */
    public static String detectCode(String text) {
        if (text == null) {
            return null;
        }
        boolean zone03 = text.contains(ZONE_WB03);
        boolean zone04 = text.contains(ZONE_WB04);
        boolean hint03 = text.contains(HINT_WB03);
        boolean hint04 = text.contains(HINT_WB04);
        if (zone03 && hint03 && !zone04 && !hint04) {
            return "WB03";
        }
        if (zone04 && hint04 && !zone03 && !hint03) {
            return "WB04";
        }
        if (hint03 && !hint04) {
            return "WB03";
        }
        if (hint04 && !hint03) {
            return "WB04";
        }
        if (zone03 && !zone04) {
            return "WB03";
        }
        if (zone04 && !zone03) {
            return "WB04";
        }
        return null;
    }
}
