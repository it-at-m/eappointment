package zms.ataf.ui.pages.citizenview.steps;

import java.time.Duration;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.utils.DriverUtil;
import zms.ataf.ui.pages.citizenview.CitizenViewPageContext;
import zms.ataf.ui.pages.citizenview.support.CitizenViewWaits;
import zms.ataf.ui.pages.citizenview.support.ShadowDom;

/**
 * ZMSKVR-501 timeout warning banner above the stepper (last 60s of captcha or reservation).
 */
public final class TimeoutWarningStep {

    private static final String BANNER_SELECTOR = "[data-test='timeout-warning-banner']";
    private static final String DE_LABEL = "Verbleibende Zeit:";
    private static final Pattern SECONDS =
            Pattern.compile("Verbleibende Zeit:\\s*(\\d+)\\s+Sekunden", Pattern.CASE_INSENSITIVE);

    private final CitizenViewPageContext context;
    private final ShadowDom shadow;

    public TimeoutWarningStep(CitizenViewPageContext context, ShadowDom shadow, int defaultWaitSeconds) {
        this.context = context;
        this.shadow = shadow;
    }

    public void waitUntilTimeoutWarningBannerVisible(int maxSeconds) {
        context.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: waiting up to {}s for timeout warning banner", maxSeconds);
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(maxSeconds))
                .pollingEvery(Duration.ofMillis(500))
                .until(d -> bannerVisibleWithCountdown());
    }

    public void assertTimeoutWarningBannerVisible() {
        context.set();
        Assert.assertTrue(
                bannerVisibleWithCountdown(),
                "Timeout warning banner missing or without countdown. text=" + bannerText());
    }

    public void assertTimeoutWarningBannerNotVisible() {
        context.set();
        Assert.assertFalse(
                shadow.deepElementExists(BANNER_SELECTOR),
                "Timeout warning banner must not be visible. text=" + bannerText());
    }

    /**
     * Confirms the live countdown decreases without a page reload (ZMSKVR-501).
     */
    public void assertTimeoutWarningCountdownTicksDown() {
        context.set();
        assertTimeoutWarningBannerVisible();
        int first = remainingSecondsFromBanner();
        Assert.assertTrue(
                first >= 1 && first <= 60,
                "Expected countdown in the last minute (1–60), got " + first);
        CitizenViewWaits.sleepQuiet(2500L);
        int second = remainingSecondsFromBanner();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: timeout warning countdown {}s → {}s", first, second);
        Assert.assertTrue(
                second < first,
                "Countdown should tick down without reload: was " + first + "s, now " + second + "s");
        Assert.assertTrue(second >= 1, "Countdown should still show at least 1 second while banner is up");
    }

    private boolean bannerVisibleWithCountdown() {
        if (!shadow.deepElementExists(BANNER_SELECTOR)) {
            return false;
        }
        String text = bannerText();
        return text != null && text.contains(DE_LABEL) && SECONDS.matcher(text).find();
    }

    private int remainingSecondsFromBanner() {
        String text = bannerText();
        Assert.assertNotNull(text, "Timeout warning banner text missing");
        Matcher m = SECONDS.matcher(text);
        Assert.assertTrue(m.find(), "Could not parse seconds from banner: " + text);
        return Integer.parseInt(m.group(1));
    }

    private String bannerText() {
        Object o =
                ((JavascriptExecutor) DriverUtil.getDriver())
                        .executeScript(
                                "function find(root){if(!root)return null;"
                                        + "var el=root.querySelector&&root.querySelector(\"[data-test='timeout-warning-banner']\");"
                                        + "if(el)return el;"
                                        + "var all=root.querySelectorAll?root.querySelectorAll('*'):[];"
                                        + "for(var i=0;i<all.length;i++){"
                                        + "if(all[i].shadowRoot){var f=find(all[i].shadowRoot);if(f)return f;}}"
                                        + "return null;}"
                                        + "var b=find(document.body);"
                                        + "return b?(b.textContent||'').replace(/\\s+/g,' ').trim():null;");
        return o == null ? null : String.valueOf(o);
    }
}
