package zms.ataf.ui.pages.citizenview.support;

/**
 * ATAF markers for Ruppertstraße Hauptkalender scopes 160 (WB04) and 181 (WB03).
 * See Flyway {@code V50__ZMSKVR-1051_ZMSKVR-1309_wb03_wb04_scope_hints.sql}.
 */
public final class RuppertstrasseWartezoneHints {

    public static final String ZONE_WB03 = "Wartebereich 03";
    public static final String ZONE_WB04 = "Wartebereich 04";
    public static final String HINT_WB03 = "ATAF Hinweis WB03";
    public static final String HINT_WB04 = "ATAF Hinweis WB04";
    public static final String LINK_WB03 = "ATAF Link WB03";
    public static final String LINK_WB04 = "ATAF Link WB04";
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

    public static String zoneFor(String code) {
        if ("WB03".equals(code)) {
            return ZONE_WB03;
        }
        if ("WB04".equals(code)) {
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
        throw new IllegalArgumentException("Unknown Wartezone code: " + code);
    }

    public static String linkLabelFor(String code) {
        if ("WB03".equals(code)) {
            return LINK_WB03;
        }
        if ("WB04".equals(code)) {
            return LINK_WB04;
        }
        throw new IllegalArgumentException("Unknown Wartezone code: " + code);
    }

    public static String otherCode(String code) {
        if ("WB03".equals(code)) {
            return "WB04";
        }
        if ("WB04".equals(code)) {
            return "WB03";
        }
        throw new IllegalArgumentException("Unknown Wartezone code: " + code);
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
