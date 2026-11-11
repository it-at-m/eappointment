package zms.ataf.helpers;

/**
 * ZMSKVR-1018 / ZMSKVR-1388. Own sample, not the ticket text.
 * Exactly 250 characters. Two newlines and one tab sit in the middle, so a
 * later trim does not drop them, and each counts as one character. Writing
 * those newlines as {@code <br />} makes the raw string longer than 250 and
 * longer than the varchar(255) column.
 */
public final class PlainTextLengthSample {

    public static final int CUSTOM_TEXT_MAX = 250;

    private static final String HEAD = "ATAF Hinweis Zeile";
    private static final String MIDDLE = "\n\tEinrueckung\nFolge";

    private PlainTextLengthSample() {
    }

    public static String customTextAtLimit() {
        int pad = CUSTOM_TEXT_MAX - HEAD.length() - MIDDLE.length();
        if (pad < 1) {
            throw new IllegalStateException("free-text sample markers exceed " + CUSTOM_TEXT_MAX);
        }
        String text = HEAD + "X".repeat(pad) + MIDDLE;
        if (text.length() != CUSTOM_TEXT_MAX || text.indexOf('\n') < 0 || text.indexOf('\t') < 0) {
            throw new IllegalStateException(
                    "free-text sample length " + text.length() + " expected " + CUSTOM_TEXT_MAX);
        }
        return text;
    }

    public static String customTextOverLimit() {
        return customTextAtLimit() + "Y";
    }

    /** Same plain text with newlines written as break tags. Raw length is over the limit. */
    public static String customTextAtLimitWithBreakTags() {
        String tagged = customTextAtLimit().replace("\n", "<br />");
        if (tagged.length() <= CUSTOM_TEXT_MAX || tagged.indexOf('\n') >= 0) {
            throw new IllegalStateException("break-tag sample should be longer than the plain limit");
        }
        return tagged;
    }

    public static String amendmentWithBreak() {
        return "ATAF Anmerkung\n\tzweite Zeile";
    }

    public static String shortSecondFreeText() {
        return "ATAF Gruppe";
    }
}
