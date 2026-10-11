package zms.ataf.ui.pages.citizenview.steps;

import java.time.Duration;

import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.web.utils.DriverUtil;
import zms.ataf.ui.pages.citizenview.CitizenViewPage;
import zms.ataf.ui.pages.citizenview.CitizenViewPageContext;
import zms.ataf.ui.pages.citizenview.support.ShadowDom;

/** Invalid jump-in callout and restart control. */
public final class JumpInErrorStep {
    private final CitizenViewPageContext context;
    private final ShadowDom shadow;
    private final int defaultWaitSeconds;

    public JumpInErrorStep(CitizenViewPageContext context, ShadowDom shadow, int defaultWaitSeconds) {
        this.context = context;
        this.shadow = shadow;
        this.defaultWaitSeconds = defaultWaitSeconds;
    }

    /**
     * ZMSKVR-106: Patternlab secondary buttons. Minus reduces, plus increases, each with its icon.
     */





















    public void assertInvalidJumpinLinkCalloutVisible() {
        context.set();
        // Offices-and-services can outlast 25s under a full shard before the invalid relation is known.
        int sec = Math.max(60, defaultWaitSeconds);
        long deadline = System.currentTimeMillis() + sec * 1000L;
        while (System.currentTimeMillis() < deadline) {
            if (invalidJumpinCalloutVisible()) {
                return;
            }
            try {
                Thread.sleep(300L);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
                break;
            }
        }
        Assert.assertTrue(
                invalidJumpinCalloutVisible(),
                "Invalid jump-in callout not found (de or en). Expected for invalid service–office pairs only.");
    }

    private boolean invalidJumpinCalloutVisible() {
        boolean de = shadow.shadowDomContainsText(CitizenViewPage.DE_INVALID_JUMPIN_HEADER)
                && shadow.shadowDomContainsText(CitizenViewPage.DE_INVALID_JUMPIN_TEXT);
        boolean en = shadow.shadowDomContainsText(CitizenViewPage.EN_INVALID_JUMPIN_HEADER)
                && shadow.shadowDomContainsText(CitizenViewPage.EN_INVALID_JUMPIN_TEXT);
        return de || en;
    }

    public void assertInvalidJumpinRestartButtonVisible() {
        context.set();
        int sec = Math.min(15, defaultWaitSeconds);
        long deadline = System.currentTimeMillis() + sec * 1000L;
        while (System.currentTimeMillis() < deadline) {
            if (invalidJumpinRestartButton(false)) {
                return;
            }
            try {
                Thread.sleep(300L);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
                break;
            }
        }
        Assert.assertTrue(
                invalidJumpinRestartButton(false),
                "Restart button \"Termin vereinbaren\" is not visible on the invalid jump-in callout.");
    }

    public void clickInvalidJumpinRestartButton() {
        context.set();
        Assert.assertTrue(
                invalidJumpinRestartButton(true),
                "Could not click \"Termin vereinbaren\" on the invalid jump-in callout.");
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> {
                    String url = d.getCurrentUrl();
                    return url != null && !url.contains("#/services/");
                });
    }

    public void assertAddressHasNoJumpIn() {
        context.set();
        String url = DriverUtil.getDriver().getCurrentUrl();
        Assert.assertFalse(
                url != null && url.contains("#/services/"),
                "Jump-in route is still in the address: " + url);
    }

    /**
     * Restart control on the invalid jump-in callout. The painted button can sit in the
     * shadow root of {@code muc-button}, whose host has no box of its own.
     */
    public boolean invalidJumpinRestartButton(boolean click) {
        String script =
                "var click=arguments[0];"
                        + "function box(el){if(!el||el.nodeType!==1||!el.getBoundingClientRect)return false;"
                        + "var r=el.getBoundingClientRect();if(r.width<=0||r.height<=0)return false;"
                        + "var st=window.getComputedStyle(el);return st.visibility!=='hidden'&&st.display!=='none'&&st.opacity!=='0';}"
                        + "function painted(el){if(box(el))return el;var found=null;"
                        + "function w(n){if(!n||found)return;if(n.nodeType===1&&n!==el&&box(n)){found=n;return;}"
                        + "if(n.shadowRoot)w(n.shadowRoot);var c=n.children;if(c)for(var i=0;i<c.length;i++)w(c[i]);}"
                        + "w(el);return found;}"
                        + "function walk(n,fn){if(!n)return false;if(fn(n))return true;"
                        + "if(n.shadowRoot&&walk(n.shadowRoot,fn))return true;"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i],fn))return true;return false;}"
                        + "var target=null;"
                        + "walk(document.body,function(n){"
                        + "var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag!=='MUC-BUTTON'&&tag!=='BUTTON'&&tag!=='A')return false;"
                        + "var label=((n.innerText||n.textContent||'')+'').replace(/\\s+/g,' ').trim();"
                        + "if(label.indexOf('Weiteren')>=0)return false;"
                        + "if(label.indexOf('Termin vereinbaren')<0&&label.indexOf('Book appointment')<0)return false;"
                        + "var hit=painted(n);if(!hit)return false;target=hit;return true;});"
                        + "if(!target)return false;"
                        + "if(click){target.scrollIntoView({block:'center'});target.click();}"
                        + "return true;";
        Object found = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, click);
        return Boolean.TRUE.equals(found);
    }

}
