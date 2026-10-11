package zms.ataf.ui.pages.citizenview.steps;

import java.time.Duration;

import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import com.fasterxml.jackson.databind.JsonNode;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.utils.DriverUtil;
import zms.ataf.helpers.ViewportSizes;
import zms.ataf.ui.pages.citizenview.CitizenViewPageContext;
import zms.ataf.ui.pages.citizenview.support.CitizenViewWaits;
import zms.ataf.ui.pages.citizenview.support.ShadowDom;

/**
 * Leistung / kombinierbare Leistungen step: counters, duration, expand list.
 */
public final class CombinationStep {

    private final CitizenViewPageContext context;
    private final ShadowDom shadow;
    private final int defaultWaitSeconds;

    public CombinationStep(CitizenViewPageContext context, ShadowDom shadow, int defaultWaitSeconds) {
        this.context = context;
        this.shadow = shadow;
        this.defaultWaitSeconds = defaultWaitSeconds;
    }

    /**
     * Assert that the UI shows an estimated duration with the expected number of minutes. This is a generic shadow-DOM
     * text assertion used for the service combination step, selected-appointment callout, and booking summaries.
     */
    public void assertEstimatedDurationMinutes(int minutes, String where) {
        context.set();
        String minutesText = minutes + " Minuten";
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: checking estimated duration = {} in {}", minutesText, where);
        // Some views (especially after opening deep links) may need a brief moment to render
        try {
            Thread.sleep(3000L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
        shadow.waitUntilShadowContains("Voraussichtliche Termindauer", defaultWaitSeconds);
        Assert.assertTrue(
                shadow.shadowDomContainsText("Voraussichtliche Termindauer"),
                "Expected 'Voraussichtliche Termindauer' text to be visible in " + where);
        Assert.assertTrue(
                shadow.shadowDomContainsText(minutesText),
                "Expected estimated duration '" + minutesText + "' to be visible in " + where);
    }

    /** Clock illustration beside Voraussichtliche Termindauer on the service combination step. */
    public void assertEstimatedDurationShownWithClock(int minutes) {
        assertEstimatedDurationMinutes(minutes, "service combination step");
        Assert.assertTrue(
                durationClockIsVisible(),
                "Expected the clock beside Voraussichtliche Termindauer.");
    }

    /** ZMSKVR-1501: the broken 15-minute mapping showed 135 minutes for a 45-minute service. */
    public void assertEstimatedDurationMinutesNot(int minutes) {
        context.set();
        String minutesText = minutes + " Minuten";
        Assert.assertFalse(
                shadow.shadowDomContainsText(minutesText),
                "Duration '" + minutesText + "' must not be shown.");
    }

    /**
     * Increase the quantity of a subservice by clicking the "+" control on its counter, resolving the subservice by
     * visible name. If the subservice is not yet visible (hidden behind "Alle Leistungen anzeigen"), this method will
     * first click that button once and retry.
     */
    public void addSubserviceByName(String subserviceLabel, int quantity) {
        context.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: add subservice '{}' quantity {}", subserviceLabel, quantity);
        // Give the combination list a brief moment to settle (especially after jump-in or service selection).
        try {
            Thread.sleep(500L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
        for (int i = 0; i < quantity; i++) {
            boolean ok = deepAddSubserviceOnceByName(subserviceLabel);
            Assert.assertTrue(
                    ok, "Could not increase subservice counter for '" + subserviceLabel + "' (iteration " + (i + 1) + ")");
            // Small delay after each click so Vue state and duration can update before the next assertion.
            try {
                Thread.sleep(500L);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
            }
        }
    }

    public void increaseSelectedService(String label) {
        context.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: increase service count for {}", label);
        Assert.assertTrue(
                pressServiceCounter(label, true),
                "Could not increase the count for \"" + label + "\".");
        CitizenViewWaits.sleepQuiet(500L);
    }

    public void decreaseSelectedService(String label) {
        context.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: decrease service count for {}", label);
        Assert.assertTrue(
                pressServiceCounter(label, false),
                "Could not decrease the count for \"" + label + "\".");
        CitizenViewWaits.sleepQuiet(500L);
    }

    /** The first selected service cannot be set to 0. */
    public void assertSelectedServiceCannotDropBelowOne(String label) {
        context.set();
        assertServiceCounter(label, 1);
        String state = serviceCounterButtonState(label, false);
        Assert.assertNotEquals(
                "missing", state, "Minus for \"" + label + "\" was not on the service page.");
        assertServiceCounter(label, 1);
    }

    /** ZMSKVR-106: the service step continues with the label Weiter. */
    public void assertWeiterButtonSays(String label) {
        context.set();
        Assert.assertTrue(
                weiterButtonIsExact(label),
                "Expected the continue button on the service page to say " + label + ".");
    }

    /** ZMSKVR-321: heading above the optional combinable peers on the Leistung step. */
    public void assertCombinableServicesHeadingVisible() {
        context.set();
        shadow.waitUntilShadowContains("Kombinierbare Leistungen", defaultWaitSeconds);
        Assert.assertTrue(
                shadow.shadowDomContainsText("Kombinierbare Leistungen"),
                "Expected the Kombinierbare Leistungen heading on the service step.");
    }

    /** Visible rows under Kombinierbare Leistungen (not the main selected service above). */
    public void assertCombinableServiceCount(int expected) {
        context.set();
        try {
            new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                    .until(d -> countVisibleCombinableServices() == expected);
        } catch (TimeoutException e) {
            Assert.fail(
                    "Expected "
                            + expected
                            + " combinable services, found "
                            + countVisibleCombinableServices()
                            + ".");
        }
        Assert.assertEquals(
                countVisibleCombinableServices(),
                expected,
                "Expected " + expected + " combinable services, found "
                        + countVisibleCombinableServices()
                        + ".");
    }

    public void assertCombinableServiceCountGreaterThan(int minimum) {
        context.set();
        int actual = countVisibleCombinableServices();
        Assert.assertTrue(
                actual > minimum,
                "Expected more than " + minimum + " combinable services, found " + actual + ".");
    }

    public void assertShowAllServicesButtonVisible(boolean visible) {
        context.set();
        boolean found = showAllServicesButtonVisible();
        Assert.assertEquals(
                found,
                visible,
                visible
                        ? "Expected Alle Leistungen anzeigen on the service step."
                        : "Alle Leistungen anzeigen should be hidden on the service step.");
    }

    public void showAllCombinableServices() {
        context.set();
        Assert.assertTrue(
                clickShowAllServicesButton(),
                "Could not click Alle Leistungen anzeigen.");
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> countVisibleCombinableServices() > 3);
        Assert.assertTrue(
                countVisibleCombinableServices() > 3,
                "Combinable list did not expand after Alle Leistungen anzeigen.");
    }

    public void assertSecondaryPlusAndMinus(String label) {
        context.set();
        JsonNode counter = waitForServiceCounter(label);
        Assert.assertTrue(counter.path("minusSecondary").asBoolean(), "Minus for \"" + label + "\" is not secondary: " + counter);
        Assert.assertTrue(counter.path("plusSecondary").asBoolean(), "Plus for \"" + label + "\" is not secondary: " + counter);
        Assert.assertEquals(counter.path("minusIcon").asText(), "minus", "Minus icon for \"" + label + "\": " + counter);
        Assert.assertEquals(counter.path("plusIcon").asText(), "plus", "Plus icon for \"" + label + "\": " + counter);
    }

    public void assertMinusButton(String label, boolean disabled) {
        context.set();
        JsonNode counter = waitForServiceCounter(label);
        String state = counter.path("minus").asText();
        Assert.assertEquals(
                state,
                disabled ? "disabled" : "enabled",
                "Minus for \"" + label + "\" was " + state);
    }

    public void assertPlusButton(String label, boolean disabled) {
        context.set();
        JsonNode counter = waitForServiceCounter(label);
        String state = counter.path("plus").asText();
        Assert.assertEquals(
                state,
                disabled ? "disabled" : "enabled",
                "Plus for \"" + label + "\" was " + state);
    }

    /** ZMSKVR-106: the service name links to its description on muenchen.de. */
    public void assertServiceDescriptionLink(String label, String serviceId) {
        context.set();
        JsonNode counter = waitForServiceCounter(label);
        String href = counter.path("href").asText();
        Assert.assertTrue(
                href.contains("stadt.muenchen.de/service/info/" + serviceId),
                "\"" + label + "\" should link to its service description, href was " + href);
    }

    /** ZMSKVR-249: on a wide window the count and buttons sit left of the service name. */
    public void assertCountBesideNameOnDesktop(String label) {
        context.set();
        RemoteWebDriver driver = DriverUtil.getDriver();
        driver.manage().window().setSize(ViewportSizes.DESKTOP);
        CitizenViewWaits.sleepQuiet(400L);
        JsonNode wide = waitForServiceCounter(label);
        Assert.assertTrue(wide.path("nameLeft").asDouble() >= 0, "No service-name link for \"" + label + "\".");
        Assert.assertTrue(
                wide.path("controlsRight").asDouble() <= wide.path("nameLeft").asDouble() + 12
                        && wide.path("controlsBottom").asDouble() >= wide.path("nameTop").asDouble() - 8
                        && wide.path("controlsTop").asDouble() <= wide.path("nameBottom").asDouble() + 8,
                "On a wide window the count sits left of \"" + label + "\": " + wide);
    }

    /** ZMSKVR-249: on a phone the count and buttons sit below the service name. */
    public void assertCountBelowNameOnPhone(String label) {
        context.set();
        RemoteWebDriver driver = DriverUtil.getDriver();
        driver.manage().window().setSize(ViewportSizes.MOBILE);
        CitizenViewWaits.sleepQuiet(400L);
        JsonNode narrow = waitForServiceCounter(label);
        Assert.assertTrue(narrow.path("nameBottom").asDouble() >= 0, "No service-name link for \"" + label + "\".");
        Assert.assertTrue(
                narrow.path("controlsTop").asDouble() >= narrow.path("nameBottom").asDouble() - 8,
                "On a phone the count sits below \"" + label + "\": " + narrow);
    }

    /** Click plus until the service's own maximum disables it. */
    public void raiseServiceUntilPlusDisabled(String label) {
        context.set();
        for (int i = 0; i < 8; i++) {
            JsonNode counter = waitForServiceCounter(label);
            if ("disabled".equals(counter.path("plus").asText())) {
                return;
            }
            Assert.assertEquals(
                    counter.path("plus").asText(),
                    "enabled",
                    "Plus for \"" + label + "\" was " + counter.path("plus").asText());
            int current = displayedServiceCount(label);
            increaseSelectedService(label);
            int next = current + 1;
            new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                    .until(d -> serviceCounterShows(label, next)
                            || "disabled".equals(queryServiceCounter(label).path("plus").asText()));
        }
        Assert.fail("Plus for \"" + label + "\" was still enabled after 8 increases.");
    }

    public void assertPassOnlyCombinationServicesVisible() {
        context.set();
        shadow.waitUntilShadowContains("Reisepass", defaultWaitSeconds);
        Assert.assertTrue(shadow.shadowDomContainsText("Reisepass"), "Expected Reisepass on Pass-only combination step");
        Assert.assertTrue(shadow.shadowDomContainsText("Personalausweis"), "Expected Personalausweis (Pass family)");
        Assert.assertTrue(
                shadow.shadowDomContainsText("Vorläufiger Reisepass")
                        || shadow.shadowDomContainsText("vorläufiger Reisepass")
                        || shadow.shadowDomContainsText("Vorläufiger"),
                "Expected Vorläufiger Reisepass (or label) on Pass-only step");
    }

    /** Jump-in: combination step shows Weiter + optional counters. */
    public void assertCombinationStepVisible() {
        context.set();
        // Combination (Ort/Zeit) step is usually identified by the "Kombinierbare Leistungen" heading.
        // For flows without combinable services (e.g. Abholung-only), this heading is absent; in those
        // cases we fall back to the presence of the "Leistung wechseln" back button as the indicator
        // that the Leistung step has been replaced by the combination step.
        String deHeading = "Kombinierbare Leistungen";
        String enHeading = "Combinable services";
        String backButton = "Leistung wechseln";
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> shadow.shadowDomContainsText(deHeading)
                        || shadow.shadowDomContainsText(enHeading)
                        || shadow.shadowDomContainsText(backButton));
        Assert.assertTrue(
                shadow.shadowDomContainsText(deHeading)
                        || shadow.shadowDomContainsText(enHeading)
                        || shadow.shadowDomContainsText(backButton),
                "Expected combination step after service selection or jump-in "
                        + "(Kombinierbare Leistungen / Combinable services heading, or Leistung wechseln back button).");
    }

    public void assertServiceCounter(String label, int count) {
        context.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> serviceCounterShows(label, count));
        Assert.assertTrue(
                serviceCounterShows(label, count),
                "Service counter for \"" + label + "\" should still be " + count + ".");
    }

    public boolean durationClockIsVisible() {
        String script =
                "function shown(el){var n=el;while(n&&n.nodeType===1){"
                        + "var st=window.getComputedStyle(n);"
                        + "if(st.display==='none'||st.visibility==='hidden'||st.opacity==='0')return false;"
                        + "if(n.parentElement){n=n.parentElement;continue;}"
                        + "var root=n.getRootNode&&n.getRootNode();n=root&&root.host?root.host:null;}"
                        + "return true;}"
                        + "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=' '+textOf(n.shadowRoot);"
                        + "var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=' '+textOf(c[i]);return s;}"
                        + "function walk(n){if(!n)return false;"
                        + "if((n.tagName||'').toUpperCase()==='SVG'&&(n.getAttribute('viewBox')||'')==='0 0 56 56'){"
                        + "var host=n;while(host&&host.nodeType===1){"
                        + "if(textOf(host).indexOf('Voraussichtliche Termindauer')>=0&&shown(n))return true;"
                        + "if(host.parentElement){host=host.parentElement;continue;}"
                        + "var root=host.getRootNode&&host.getRootNode();host=root&&root.host?root.host:null;}"
                        + "}"
                        + "if(n.shadowRoot&&walk(n.shadowRoot))return true;"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i]))return true;return false;}"
                        + "return walk(document.body);";
        Object found = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return Boolean.TRUE.equals(found);
    }

    public int countVisibleCombinableServices() {
        String script =
                "function shown(el){var n=el;while(n&&n.nodeType===1){"
                        + "var st=window.getComputedStyle(n);"
                        + "if(st.display==='none'||st.visibility==='hidden'||st.opacity==='0')return false;"
                        + "if(n.parentElement){n=n.parentElement;continue;}"
                        + "var root=n.getRootNode&&n.getRootNode();n=root&&root.host?root.host:null;}return true;}"
                        + "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=textOf(n.shadowRoot);var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=textOf(c[i]);return s;}"
                        + "function walk(n,fn){if(!n)return null;if(n.nodeType===1){var hit=fn(n);if(hit)return hit;}"
                        + "if(n.shadowRoot){var inner=walk(n.shadowRoot,fn);if(inner)return inner;}"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++){var next=walk(c[i],fn);if(next)return next;}return null;}"
                        + "var list=walk(document.body,function(el){"
                        + "if(!el.classList||!el.classList.contains('m-listing__list'))return null;"
                        + "var host=el;while(host&&host.nodeType===1){"
                        + "if(textOf(host).indexOf('Kombinierbare Leistungen')>=0)return el;"
                        + "if(host.parentElement){host=host.parentElement;continue;}"
                        + "var root=host.getRootNode&&host.getRootNode();host=root&&root.host?root.host:null;}"
                        + "return null;});"
                        + "if(!list)return 0;"
                        + "var items=list.querySelectorAll(':scope > li.m-listing__list-item, :scope > .m-listing__list-item');"
                        + "if(!items.length)items=list.querySelectorAll('li.m-listing__list-item');"
                        + "var n=0;for(var i=0;i<items.length;i++)if(shown(items[i]))n++;return n;";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return raw instanceof Number ? ((Number) raw).intValue() : 0;
    }

    public boolean showAllServicesButtonVisible() {
        String script =
                "function shown(el){var n=el;while(n&&n.nodeType===1){"
                        + "var st=window.getComputedStyle(n);"
                        + "if(st.display==='none'||st.visibility==='hidden'||st.opacity==='0')return false;"
                        + "if(n.parentElement){n=n.parentElement;continue;}"
                        + "var root=n.getRootNode&&n.getRootNode();n=root&&root.host?root.host:null;}return true;}"
                        + "function norm(t){return (t||'').replace(/\\s+/g,' ').trim();}"
                        + "function walk(n,fn){if(!n)return false;if(fn(n))return true;if(n.shadowRoot&&walk(n.shadowRoot,fn))return true;"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i],fn))return true;return false;}"
                        + "return walk(document.body,function(n){"
                        + "var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag!=='BUTTON'&&tag!=='MUC-BUTTON')return false;"
                        + "return norm(n.textContent||'').indexOf('Alle Leistungen anzeigen')>=0&&shown(n);});";
        return Boolean.TRUE.equals(((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script));
    }

    public boolean clickShowAllServicesButton() {
        String script =
                "function shown(el){var n=el;while(n&&n.nodeType===1){"
                        + "var st=window.getComputedStyle(n);"
                        + "if(st.display==='none'||st.visibility==='hidden'||st.opacity==='0')return false;"
                        + "if(n.parentElement){n=n.parentElement;continue;}"
                        + "var root=n.getRootNode&&n.getRootNode();n=root&&root.host?root.host:null;}return true;}"
                        + "function norm(t){return (t||'').replace(/\\s+/g,' ').trim();}"
                        + "function walk(n,fn){if(!n)return null;if(n.nodeType===1){var hit=fn(n);if(hit)return hit;}"
                        + "if(n.shadowRoot){var inner=walk(n.shadowRoot,fn);if(inner)return inner;}"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++){var next=walk(c[i],fn);if(next)return next;}return null;}"
                        + "var btn=walk(document.body,function(n){"
                        + "var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag!=='BUTTON'&&tag!=='MUC-BUTTON')return null;"
                        + "if(norm(n.textContent||'').indexOf('Alle Leistungen anzeigen')<0||!shown(n))return null;"
                        + "return n;});"
                        + "if(!btn)return false;btn.scrollIntoView({block:'center'});btn.click();return true;";
        return Boolean.TRUE.equals(((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script));
    }

    public int displayedServiceCount(String label) {
        for (int n = 0; n <= 8; n++) {
            if (serviceCounterShows(label, n)) {
                return n;
            }
        }
        Assert.fail("No displayed count for \"" + label + "\".");
        return -1;
    }

    public JsonNode waitForServiceCounter(String label) {
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> !"missing".equals(queryServiceCounter(label).path("minus").asText()));
        JsonNode counter = queryServiceCounter(label);
        Assert.assertNotEquals(
                "missing",
                counter.path("minus").asText(),
                "No count control for \"" + label + "\".");
        return counter;
    }

    public JsonNode queryServiceCounter(String label) {
        String script =
                "var label=arguments[0];"
                        + "function norm(t){return (t||'').replace(/\\s+/g,' ').trim();}"
                        + "function key(t){return norm(t).replace(/-/g,'').toLowerCase();}"
                        + "var labelKey=key(label);"
                        + "function shown(el){var n=el;while(n&&n.nodeType===1){"
                        + "var st=window.getComputedStyle(n);"
                        + "if(st.display==='none'||st.visibility==='hidden'||st.opacity==='0')return false;"
                        + "if(n.parentElement){n=n.parentElement;continue;}"
                        + "var root=n.getRootNode&&n.getRootNode();n=root&&root.host?root.host:null;}return true;}"
                        + "function isDisabled(el){if(!el)return true;"
                        + "function off(node){return !!(node&&(node.disabled||node.hasAttribute&&node.hasAttribute('disabled')"
                        + "||(node.getAttribute&&node.getAttribute('aria-disabled')==='true')));}"
                        + "if(off(el))return true;"
                        + "var host=el.getRootNode&&el.getRootNode().host;return off(host);}"
                        + "function paint(n){var icon='',secondary=false,blob='';"
                        + "function note(el){if(!el||!el.getAttribute)return;"
                        + "var ic=el.getAttribute('icon')||'';"
                        + "var href=el.getAttribute('href')||el.getAttribute('xlink:href')||'';"
                        + "var cls=(typeof el.className==='string')?el.className:'';"
                        + "var v=el.getAttribute('variant')||'';"
                        + "if(!icon&&ic)icon=ic;"
                        + "if(v==='secondary'||cls.indexOf('secondary')>=0)secondary=true;"
                        + "blob+=' '+ic+' '+href+' '+cls;}"
                        + "function scan(el,depth){if(!el||depth>8)return;note(el);"
                        + "if(el.shadowRoot)scan(el.shadowRoot,depth+1);"
                        + "var kids=el.children;if(kids)for(var i=0;i<kids.length;i++)scan(kids[i],depth+1);}"
                        + "scan(n,0);var cur=n,guard=0;"
                        + "while(cur&&guard++<6){note(cur);"
                        + "if(cur.parentElement)cur=cur.parentElement;"
                        + "else{var root=cur.getRootNode&&cur.getRootNode();cur=root&&root.host?root.host:null;}}"
                        + "return {icon:icon,secondary:secondary,blob:blob.toLowerCase()};}"
                        + "function walk(n,fn){if(!n)return;fn(n);if(n.shadowRoot)walk(n.shadowRoot,fn);"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)walk(c[i],fn);}"
                        + "function consider(best,btn){var aria=btn.getAttribute('aria-label')||'';"
                        + "if(!shown(btn)||key(aria).indexOf(labelKey)<0)return best;"
                        + "if(!best||aria.length<best.aria.length)return {btn:btn,aria:aria};return best;}"
                        + "var minus=null,plus=null;"
                        + "walk(document.body,function(n){var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag!=='BUTTON')return;var aria=(n.getAttribute('aria-label')||'').toLowerCase();"
                        + "if(aria.indexOf('reduzier')>=0)minus=consider(minus,n);"
                        + "else if(key(aria).indexOf(labelKey)>=0)plus=consider(plus,n);});"
                        + "if(!minus){var show=null;walk(document.body,function(n){"
                        + "var tag=(n.tagName||'').toUpperCase();"
                        + "if((tag==='BUTTON'||tag==='MUC-BUTTON')&&norm(n.textContent||'').indexOf('Alle Leistungen anzeigen')>=0&&shown(n))show=n;});"
                        + "if(show){show.click();return JSON.stringify({minus:'missing',revealed:true});}"
                        + "return JSON.stringify({minus:'missing'});}"
                        + "var link=null,linkLen=100000;walk(document.body,function(n){"
                        + "if((n.tagName||'').toUpperCase()!=='A')return;"
                        + "var href=n.getAttribute('href')||'';"
                        + "if(href.indexOf('stadt.muenchen.de/service/info/')<0)return;"
                        + "var nameKey=key(n.textContent||'');"
                        + "if(nameKey.indexOf(labelKey)<0||nameKey.length>=linkLen)return;"
                        + "link=n;linkLen=nameKey.length;});"
                        + "function box(el){if(!el||!el.getBoundingClientRect)return null;var r=el.getBoundingClientRect();"
                        + "return {left:r.left,right:r.right,top:r.top,bottom:r.bottom};}"
                        + "var mb=box(minus.btn),pb=plus?box(plus.btn):mb,lb=box(link);"
                        + "var mp=paint(minus.btn),pp=plus?paint(plus.btn):{icon:'',secondary:false,blob:''};"
                        + "function iconName(p,word){"
                        + "if((p.icon||'').indexOf(word)>=0||p.blob.indexOf(word)>=0)return word;return '';}"
                        + "return JSON.stringify({"
                        + "minus:isDisabled(minus.btn)?'disabled':'enabled',"
                        + "plus:!plus?'missing':(isDisabled(plus.btn)?'disabled':'enabled'),"
                        + "minusSecondary:mp.secondary,plusSecondary:pp.secondary,"
                        + "minusIcon:iconName(mp,'minus'),plusIcon:iconName(pp,'plus'),"
                        + "href:link?link.getAttribute('href'):'',"
                        + "controlsRight:Math.max(mb.right,pb.right),controlsTop:Math.min(mb.top,pb.top),"
                        + "controlsBottom:Math.max(mb.bottom,pb.bottom),"
                        + "nameLeft:lb?lb.left:-1,nameTop:lb?lb.top:-1,nameBottom:lb?lb.bottom:-1"
                        + "});";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, label);
        try {
            return new com.fasterxml.jackson.databind.ObjectMapper()
                    .readTree(raw == null ? "{\"minus\":\"missing\"}" : String.valueOf(raw));
        } catch (Exception e) {
            throw new AssertionError("Could not read the count for \"" + label + "\": " + raw, e);
        }
    }

    public boolean weiterButtonIsExact(String label) {
        String script =
                "var label=arguments[0];"
                        + "function norm(t){return (t||'').replace(/\\s+/g,' ').trim();}"
                        + "function walk(n){if(!n)return false;var tag=(n.tagName||'').toUpperCase();"
                        + "if((tag==='BUTTON'||tag==='MUC-BUTTON')&&norm(n.textContent)===label)return true;"
                        + "if(n.shadowRoot&&walk(n.shadowRoot))return true;"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i]))return true;return false;}"
                        + "return walk(document.body);";
        Object found = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, label);
        return Boolean.TRUE.equals(found);
    }

    public boolean pressServiceCounter(String label, boolean increase) {
        return "clicked".equals(serviceCounterButtonState(label, increase));
    }

    public String serviceCounterButtonState(String label, boolean increase) {
        String script =
                "var label=arguments[0];var increase=arguments[1]===true;"
                        + "function norm(t){return (t||'').replace(/\\s+/g,' ').trim();}"
                        + "function key(t){return norm(t).replace(/-/g,'').toLowerCase();}"
                        + "var labelKey=key(label);"
                        + "function shown(el){var n=el;while(n&&n.nodeType===1){"
                        + "var st=window.getComputedStyle(n);"
                        + "if(st.display==='none'||st.visibility==='hidden'||st.opacity==='0')return false;"
                        + "if(n.parentElement){n=n.parentElement;continue;}"
                        + "var root=n.getRootNode&&n.getRootNode();n=root&&root.host?root.host:null;}return true;}"
                        + "function matches(aria){if(!aria)return false;var lower=aria.toLowerCase();"
                        + "var reduce=lower.indexOf('reduzier')>=0;"
                        + "if(increase&&reduce)return false;if(!increase&&!reduce)return false;"
                        + "return key(aria).indexOf(labelKey)>=0;}"
                        + "var found=null;"
                        + "function walk(n){if(!n||found)return;var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag==='BUTTON'&&matches(n.getAttribute('aria-label')||'')&&shown(n)){found=n;return;}"
                        + "if(n.shadowRoot)walk(n.shadowRoot);"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)walk(c[i]);}"
                        + "walk(document.body);if(!found)return 'missing';"
                        + "if(found.disabled||found.getAttribute('aria-disabled')==='true')return 'disabled';"
                        + "found.scrollIntoView({block:'center'});found.click();return 'clicked';";
        Object state = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, label, increase);
        return state == null ? "missing" : String.valueOf(state);
    }

    /**
     * JS helper: try to click the "+" button for a subservice counter with a matching label once. Returns true on
     * success. If the subservice is not found, it will attempt to click "Alle Leistungen anzeigen" once and search
     * again.
     */
    public boolean deepAddSubserviceOnceByName(String subserviceLabel) {
        context.set();
        String esc = subserviceLabel.replace("\\", "\\\\").replace("'", "\\'");
        String script =
                "var label='" + esc + "';"
                        + "function norm(t){return (t||'').replace(/\\s+/g,' ').trim();}"
                        + "function key(t){return norm(t).replace(/-/g,'').toLowerCase();}"
                        + "var labelKey = key(label);"
                        + "function findPlusButtonDeep(root){"
                        + "  if(!root)return null;"
                        + "  if(root.nodeType===1 && root.tagName==='BUTTON'){"
                        + "    var aria=root.getAttribute('aria-label')||'';"
                        + "    if(aria && !root.disabled){"
                        + "      var lower=aria.toLowerCase();"
                        + "      if(lower.indexOf('reduzier')>=0){}"
                        + "      else {"
                        + "        var aKey=key(aria);"
                        + "        if(aKey.indexOf(labelKey)>=0)return root;"
                        + "      }"
                        + "    }"
                        + "  }"
                        + "  if(root.shadowRoot){"
                        + "    var r=findPlusButtonDeep(root.shadowRoot);"
                        + "    if(r)return r;"
                        + "  }"
                        + "  var kids=root.children||[];"
                        + "  for(var i=0;i<kids.length;i++){"
                        + "    var r2=findPlusButtonDeep(kids[i]);"
                        + "    if(r2)return r2;"
                        + "  }"
                        + "  return null;"
                        + "}"
                        + "function findShowAllDeep(root){"
                        + "  if(!root)return null;"
                        + "  if(root.nodeType===1){"
                        + "    var tag=(root.tagName||'').toUpperCase();"
                        + "    if((tag==='BUTTON'||tag==='MUC-BUTTON')){"
                        + "      var txt=norm(root.textContent||'');"
                        + "      if(txt.indexOf('Alle Leistungen anzeigen')>=0 && !root.disabled)return root;"
                        + "    }"
                        + "    if(root.shadowRoot){"
                        + "      var r=findShowAllDeep(root.shadowRoot);"
                        + "      if(r)return r;"
                        + "    }"
                        + "    var kids=root.children||[];"
                        + "    for(var i=0;i<kids.length;i++){"
                        + "      var r2=findShowAllDeep(kids[i]);"
                        + "      if(r2)return r2;"
                        + "    }"
                        + "  }"
                        + "  return null;"
                        + "}"
                        + "function clickPlusOnButton(btn){"
                        + "  if(!btn)return false;"
                        + "  if(btn.disabled)return false;"
                        + "  btn.scrollIntoView({block:'center'});"
                        + "  try{"
                        + "    btn.style.outline='4px solid #ffbf00';"
                        + "    btn.style.outlineOffset='3px';"
                        + "    btn.style.backgroundColor='rgba(255,191,0,0.25)';"
                        + "  }catch(e){}"
                        + "  btn.click();"
                        + "  return true;"
                        + "}"
                        + "var btn=findPlusButtonDeep(document.body);"
                        + "if(!btn){"
                        + "  var showAll=findShowAllDeep(document.body);"
                        + "  if(showAll){showAll.scrollIntoView({block:'center'});showAll.click();}"
                        + "  btn=findPlusButtonDeep(document.body);"
                        + "}"
                        + "return clickPlusOnButton(btn);";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return Boolean.TRUE.equals(o);
    }

    public boolean serviceCounterShows(String label, int count) {
        String script =
                "var label=arguments[0];var count=String(arguments[1]);"
                        + "function walk(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=' '+walk(n.shadowRoot);"
                        + "var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=' '+walk(c[i]);return s;}"
                        + "var text=walk(document.body).replace(/\\s+/g,' ');"
                        + "var needle='Aktuell ausgewählte Anzahl für ';var from=0;"
                        + "while(true){var at=text.indexOf(needle,from);if(at<0)return false;"
                        + "var rest=text.substring(at+needle.length);var ist=rest.indexOf(' ist ');"
                        + "if(ist>=0){var name=rest.substring(0,ist);var num=rest.substring(ist+5).match(/^(\\d+)/);"
                        + "if(name.indexOf(label)>=0&&num&&num[1]===count)return true;}"
                        + "from=at+needle.length;}"
                        + "return false;";
        Object found = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, label, count);
        return Boolean.TRUE.equals(found);
    }

}
