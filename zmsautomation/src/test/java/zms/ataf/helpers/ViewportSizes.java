package zms.ataf.helpers;

import org.openqa.selenium.Dimension;

/**
 * Fixed window sizes for citizenview responsive UI tests.
 * Phone width stays under the zmscitizenview xs breakpoint (600px).
 */
public final class ViewportSizes {

    public static final Dimension DESKTOP = new Dimension(1400, 900);
    public static final Dimension MOBILE = new Dimension(390, 844);

    private ViewportSizes() {
    }
}
