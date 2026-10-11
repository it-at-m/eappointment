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

    /** ZMSKVR-514: after expand, "Weniger Leistungen anzeigen" with chevron-up. */
    public void assertShowLessServicesButtonVisible(boolean visible) {
        context.set();
        JsonNode btn = showLessServicesButtonSnapshot();
        Assert.assertEquals(
                btn.path("visible").asBoolean(false),
                visible,
                visible
                        ? "Expected Weniger Leistungen anzeigen on the service step: " + btn
                        : "Weniger Leistungen anzeigen should be hidden: " + btn);
        if (visible) {
            Assert.assertTrue(
                    btn.path("chevronUp").asBoolean(false),
                    "Weniger Leistungen anzeigen should use chevron-up: " + btn);
        }
    }

    /**
     * ZMSKVR-514 / ZMSKVR-1185: collapse back to three peers and scroll to the start of the
     * Leistung list (not the page bottom).
     */
    public void showLessCombinableServices() {
        context.set();
        Assert.assertTrue(
                clickShowLessServicesButton(),
                "Could not click Weniger Leistungen anzeigen.");
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> countVisibleCombinableServices() == 3);
        Assert.assertEquals(
                countVisibleCombinableServices(),
                3,
                "Combinable list did not collapse after Weniger Leistungen anzeigen.");
        // Product scrolls servicesRef (Leistung h2). Sticky chrome / short pages leave pageY
        // non-zero even when that heading is already in the upper viewport — assert the heading
        // band, not an absolute pageY.
        try {
            new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                    .until(d -> combinableListStartInUpperViewport(combinableListScrollSnapshot()));
        } catch (TimeoutException e) {
            throw new AssertionError(
                    "After Weniger Leistungen anzeigen the Leistung list start should be in the"
                            + " upper viewport: "
                            + combinableListScrollSnapshot(),
                    e);
        }
        JsonNode scroll = combinableListScrollSnapshot();
        Assert.assertTrue(
                combinableListStartInUpperViewport(scroll),
                "After Weniger Leistungen anzeigen the Leistung list start should be in the upper"
                        + " viewport: "
                        + scroll);
    }

    private static boolean combinableListStartInUpperViewport(JsonNode scroll) {
        if (scroll.path("nearPageBottom").asBoolean(false)) {
            return false;
        }
        // Prefer the Leistung h2 (servicesRef). Fall back to the selected primary service row —
        // on phone the sticky Anmelden can cover the h2 while the list start is still on screen.
        if (scroll.path("headingFound").asBoolean(false)) {
            double listTop = scroll.path("listTop").asDouble(9999);
            if (listTop > -120 && listTop < 320) {
                return true;
            }
        }
        double primaryTop = scroll.path("primaryTop").asDouble(9999);
        return primaryTop > -40 && primaryTop < 280;
    }

    /**
     * ZMSKVR-529: disabled plus/minus use transparent background, grey border and grey icon
     * (#7A8D9F).
     */
    public void assertDisabledCounterColors(String label) {
        context.set();
        JsonNode style = disabledCounterStyle(label);
        Assert.assertTrue(
                style.path("found").asBoolean(false),
                "No disabled plus/minus for \"" + label + "\": " + style);
        Assert.assertTrue(
                style.path("transparentBg").asBoolean(false),
                "Disabled counter for \"" + label + "\" should have a transparent/white background: "
                        + style);
        Assert.assertTrue(
                style.path("greyBorder").asBoolean(false),
                "Disabled counter for \"" + label + "\" should use border #7a8d9f: " + style);
        Assert.assertTrue(
                style.path("greyIcon").asBoolean(false),
                "Disabled counter for \"" + label + "\" should use icon/color #7a8d9f: " + style);
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

    /**
     * Every plus paired with a visible minus (muc-counter) is disabled — spa budget full,
     * including unselected combinable peers that no longer fit.
     */
    public void assertAllPlusButtonsDisabled() {
        context.set();
        try {
            new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                    .until(d -> {
                        String state = allPlusButtonsState();
                        return state.startsWith("ok:");
                    });
        } catch (TimeoutException e) {
            Assert.fail("Expected every combination-step plus to be disabled: " + allPlusButtonsState());
        }
    }

    /** Returns {@code ok:N} or {@code fail:…} describing visible counter plus states. */
    private String allPlusButtonsState() {
        String script =
                "function shown(el){var n=el;while(n&&n.nodeType===1){"
                        + "var st=window.getComputedStyle(n);"
                        + "if(st.display==='none'||st.visibility==='hidden'||st.opacity==='0')return false;"
                        + "if(n.parentElement){n=n.parentElement;continue;}"
                        + "var root=n.getRootNode&&n.getRootNode();n=root&&root.host?root.host:null;}return true;}"
                        + "function isDisabled(el){if(!el)return true;"
                        + "function off(node){return !!(node&&(node.disabled||node.hasAttribute&&node.hasAttribute('disabled')"
                        + "||(node.getAttribute&&node.getAttribute('aria-disabled')==='true')));}"
                        + "if(off(el))return true;"
                        + "var host=el.getRootNode&&el.getRootNode().host;return off(host);}"
                        + "function walk(n,fn){if(!n)return;fn(n);if(n.shadowRoot)walk(n.shadowRoot,fn);"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)walk(c[i],fn);}"
                        + "function buttonsNear(minus){var host=minus;"
                        + "for(var g=0;g<6&&host;g++){var found=[];"
                        + "walk(host,function(n){if((n.tagName||'').toUpperCase()==='BUTTON'&&shown(n))found.push(n);});"
                        + "if(found.length>=2)return found;"
                        + "if(host.parentElement)host=host.parentElement;"
                        + "else{var root=host.getRootNode&&host.getRootNode();host=root&&root.host?root.host:null;}}"
                        + "return [];}"
                        + "var minusBtns=[];"
                        + "walk(document.body,function(n){if((n.tagName||'').toUpperCase()!=='BUTTON'||!shown(n))return;"
                        + "var aria=(n.getAttribute('aria-label')||'').toLowerCase();"
                        + "if(aria.indexOf('reduzier')>=0)minusBtns.push(n);});"
                        + "if(minusBtns.length<2)return 'fail:only '+minusBtns.length+' minus buttons';"
                        + "var enabled=[];"
                        + "for(var i=0;i<minusBtns.length;i++){var minus=minusBtns[i];"
                        + "var btns=buttonsNear(minus);var plus=null;"
                        + "for(var j=0;j<btns.length;j++){if(btns[j]===minus)continue;"
                        + "var a=(btns[j].getAttribute('aria-label')||'').toLowerCase();"
                        + "if(a.indexOf('reduzier')>=0)continue;plus=btns[j];break;}"
                        + "if(!plus)return 'fail:no plus near '+ (minus.getAttribute('aria-label')||'');"
                        + "if(!isDisabled(plus))enabled.push(plus.getAttribute('aria-label')||'?');}"
                        + "return enabled.length?('fail:enabled '+enabled.join(' | ')):'ok:'+minusBtns.length;";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return raw == null ? "fail:null" : String.valueOf(raw);
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

    /**
     * Art des Termins (Vor-Ort / Telefon / Video): duration stays hidden until a variant
     * is chosen ({@code showEstimatedDuration} in ServiceFinder.vue).
     *
     * <p>muc-radio-button uses a closed shadow root, so {@code input.checked} is not readable.
     * Click the host (or any light-DOM match), then wait for the duration clock — that is the
     * Vue signal that {@code selectedVariant} stuck.
     */
    public void selectAppointmentType(String label) {
        context.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: select appointment type '{}'", label);
        shadow.waitUntilShadowContains("Art des Termins", defaultWaitSeconds);
        String clickScript =
                "var label=arguments[0];"
                        + "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=' '+textOf(n.shadowRoot);"
                        + "var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=' '+textOf(c[i]);return s;}"
                        + "function labelOf(n){"
                        + " var attr='';"
                        + " if(n.getAttribute)attr=n.getAttribute('label')||n.getAttribute('aria-label')||'';"
                        + " if(!attr&&typeof n.label==='string')attr=n.label;"
                        + " return (attr||textOf(n)).replace(/\\s+/g,' ').trim();}"
                        + "function matches(n){"
                        + " var t=labelOf(n);"
                        + " return t===label||t.indexOf(label+' ')===0||t.indexOf(label)>=0;}"
                        + "function clickable(n){"
                        + " if(!n)return null;"
                        + " if(n.shadowRoot){var inp=n.shadowRoot.querySelector("
                        + "  'input[type=radio],button,.m-radio,[role=radio],label');"
                        + "  if(inp)return inp;}"
                        + " if((n.tagName||'').toUpperCase()==='INPUT'&&(n.type||'')==='radio')return n;"
                        + " return n;}"
                        + "function hostRadio(n){var cur=n;while(cur&&cur.nodeType===1){"
                        + " if((cur.tagName||'').toUpperCase()==='MUC-RADIO-BUTTON')return cur;"
                        + " if(cur.parentElement){cur=cur.parentElement;continue;}"
                        + " var root=cur.getRootNode&&cur.getRootNode();cur=root&&root.host?root.host:null;}"
                        + " return n;}"
                        + "function walk(n){if(!n)return false;"
                        + " var tag=(n.tagName||'').toUpperCase();"
                        + " var role=n.getAttribute&&n.getAttribute('role');"
                        + " if(tag==='MUC-RADIO-BUTTON'||tag==='LABEL'||role==='radio'){"
                        + "  if(matches(n)){"
                        + "   var node=hostRadio(n);"
                        + "   var hit=clickable(node)||node;"
                        + "   hit.scrollIntoView({block:'center'});"
                        // Closed shadow: host click + value on the group when possible.
                        + "   hit.click();"
                        + "   if(node!==hit)try{node.click();}catch(e){}"
                        + "   var val='';"
                        + "   if(node.getAttribute)val=node.getAttribute('value')||'';"
                        + "   if(!val&&node.value!=null&&typeof node.value!=='object')val=String(node.value);"
                        + "   if(val){var g=node;while(g&&g.nodeType===1){"
                        + "    if((g.tagName||'').toUpperCase()==='MUC-RADIO-BUTTON-GROUP'){"
                        + "     try{g.value=val;}catch(e1){}"
                        + "     try{g.modelValue=val;}catch(e2){}"
                        + "     try{g.setAttribute('value',val);}catch(e3){}"
                        + "     try{g.dispatchEvent(new CustomEvent('update:modelValue',"
                        + "      {detail:val,bubbles:true}));}catch(e4){}"
                        + "     try{g.dispatchEvent(new Event('change',{bubbles:true}));}catch(e5){}"
                        + "     break;}"
                        + "    if(g.parentElement){g=g.parentElement;continue;}"
                        + "    var root=g.getRootNode&&g.getRootNode();g=root&&root.host?root.host:null;}}"
                        + "   return true;}"
                        + " }"
                        + " if(n.shadowRoot&&walk(n.shadowRoot))return true;"
                        + " var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i]))return true;"
                        + " return false;}"
                        + "return walk(document.body);";
        // Duration clock is light-DOM Vue UI; it appears only after selectedVariant is set.
        String durationScript =
                "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=' '+textOf(n.shadowRoot);"
                        + "var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=' '+textOf(c[i]);return s;}"
                        + "return textOf(document.body).indexOf('Voraussichtliche Termindauer')>=0;";
        boolean selected = false;
        try {
            selected = Boolean.TRUE.equals(new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                    .until(d -> {
                        Object clicked =
                                ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(clickScript, label);
                        if (!Boolean.TRUE.equals(clicked)) {
                            return null;
                        }
                        Object duration =
                                ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(durationScript);
                        return Boolean.TRUE.equals(duration) ? Boolean.TRUE : null;
                    }));
        } catch (TimeoutException e) {
            selected = false;
        }
        Assert.assertTrue(selected, "Could not select appointment type '" + label + "'.");
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

    private JsonNode showLessServicesButtonSnapshot() {
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
                        + "if(norm(n.textContent||'').indexOf('Weniger Leistungen anzeigen')<0||!shown(n))return null;"
                        + "return n;});"
                        + "if(!btn)return JSON.stringify({visible:false});"
                        + "var blob=' '+(btn.getAttribute('icon')||'');"
                        + "var uses=btn.querySelectorAll?btn.querySelectorAll('use'):[];"
                        + "for(var u=0;u<uses.length;u++){blob+=' '+(uses[u].getAttribute('href')||'')"
                        + "+' '+(uses[u].getAttribute('xlink:href')||'');}"
                        + "if(btn.shadowRoot){var su=btn.shadowRoot.querySelectorAll('use');"
                        + "for(var s=0;s<su.length;s++){blob+=' '+(su[s].getAttribute('href')||'')"
                        + "+' '+(su[s].getAttribute('xlink:href')||'');}}"
                        + "blob=blob.toLowerCase();"
                        + "return JSON.stringify({visible:true,chevronUp:blob.indexOf('chevron-up')>=0,blob:blob});";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        try {
            return new com.fasterxml.jackson.databind.ObjectMapper()
                    .readTree(raw == null ? "{\"visible\":false}" : String.valueOf(raw));
        } catch (Exception e) {
            throw new AssertionError("Could not read Weniger Leistungen anzeigen: " + raw, e);
        }
    }

    private boolean clickShowLessServicesButton() {
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
                        + "if(norm(n.textContent||'').indexOf('Weniger Leistungen anzeigen')<0||!shown(n))return null;"
                        + "return n;});"
                        + "if(!btn)return false;btn.scrollIntoView({block:'center'});btn.click();return true;";
        return Boolean.TRUE.equals(((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script));
    }

    private JsonNode combinableListScrollSnapshot() {
        String script =
                "function shown(el){var n=el;while(n&&n.nodeType===1){"
                        + "var st=window.getComputedStyle(n);"
                        + "if(st.display==='none'||st.visibility==='hidden'||st.opacity==='0')return false;"
                        + "if(n.parentElement){n=n.parentElement;continue;}"
                        + "var root=n.getRootNode&&n.getRootNode();n=root&&root.host?root.host:null;}return true;}"
                        + "function walk(n,fn){if(!n)return null;if(n.nodeType===1){var hit=fn(n);if(hit)return hit;}"
                        + "if(n.shadowRoot){var inner=walk(n.shadowRoot,fn);if(inner)return inner;}"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++){var next=walk(c[i],fn);if(next)return next;}return null;}"
                        + "var heading=walk(document.body,function(n){"
                        + "var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag!=='H2'||!shown(n))return null;"
                        + "var t=(n.textContent||'').replace(/\\s+/g,' ').trim();"
                        // Exact "Leistung" — not "Kombinierbare Leistungen" further down the step.
                        + "if(t==='Leistung')return n;return null;});"
                        + "var listTop=heading?heading.getBoundingClientRect().top:9999;"
                        + "var primary=walk(document.body,function(n){"
                        + "var tag=(n.tagName||'').toUpperCase();"
                        + "if((tag!=='A'&&tag!=='BUTTON')||!shown(n))return null;"
                        + "var t=(n.textContent||'').replace(/\\s+/g,' ').trim();"
                        + "if(t==='Personalausweis')return n;return null;});"
                        + "var primaryTop=primary?primary.getBoundingClientRect().top:9999;"
                        + "var doc=document.documentElement;"
                        + "var maxScroll=Math.max(0,(doc.scrollHeight||0)-(window.innerHeight||0));"
                        + "var y=window.pageYOffset||doc.scrollTop||0;"
                        // Short pages clamp near the bottom even after a top scroll; only flag real bottoms.
                        + "var nearBottom=maxScroll>200&&y>maxScroll-80;"
                        + "return JSON.stringify({headingFound:!!heading,listTop:listTop,"
                        + "primaryTop:primaryTop,pageY:y,maxScroll:maxScroll,nearPageBottom:nearBottom});";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        try {
            return new com.fasterxml.jackson.databind.ObjectMapper()
                    .readTree(raw == null ? "{}" : String.valueOf(raw));
        } catch (Exception e) {
            throw new AssertionError("Could not read combinable list scroll: " + raw, e);
        }
    }

    private JsonNode disabledCounterStyle(String label) {
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
                        + "if(off(el))return true;var host=el.getRootNode&&el.getRootNode().host;return off(host);}"
                        + "function rgbClose(c,r,g,b){var m=/rgba?\\((\\d+),\\s*(\\d+),\\s*(\\d+)/.exec(c||'');"
                        + "if(!m)return false;return Math.abs(+m[1]-r)<=8&&Math.abs(+m[2]-g)<=8&&Math.abs(+m[3]-b)<=8;}"
                        + "function paint(btn){var target=btn;"
                        + "if(btn.shadowRoot){var inner=btn.shadowRoot.querySelector('button,.m-button,.counter-btn--disabled');if(inner)target=inner;}"
                        + "var st=getComputedStyle(target);"
                        + "var bg=st.backgroundColor||'';var bd=st.borderTopColor||st.borderColor||'';var col=st.color||'';"
                        + "var transparentBg=bg==='transparent'||bg==='rgba(0, 0, 0, 0)'||rgbClose(bg,255,255,255);"
                        + "var greyBorder=rgbClose(bd,122,141,159);var greyIcon=rgbClose(col,122,141,159);"
                        + "return {found:true,bg:bg,border:bd,color:col,transparentBg:transparentBg,greyBorder:greyBorder,greyIcon:greyIcon};}"
                        + "function walk(n,fn){if(!n)return null;if(n.nodeType===1){var hit=fn(n);if(hit)return hit;}"
                        + "if(n.shadowRoot){var inner=walk(n.shadowRoot,fn);if(inner)return inner;}"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++){var next=walk(c[i],fn);if(next)return next;}return null;}"
                        + "var btn=walk(document.body,function(n){"
                        + "if((n.tagName||'').toUpperCase()!=='BUTTON'||!shown(n))return null;"
                        + "var aria=(n.getAttribute('aria-label')||'');"
                        + "if(key(aria).indexOf(labelKey)<0)return null;"
                        + "if(!isDisabled(n))return null;"
                        + "return n;});"
                        + "if(!btn)return JSON.stringify({found:false});"
                        + "return JSON.stringify(paint(btn));";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, label);
        try {
            return new com.fasterxml.jackson.databind.ObjectMapper()
                    .readTree(raw == null ? "{\"found\":false}" : String.valueOf(raw));
        } catch (Exception e) {
            throw new AssertionError("Could not read disabled counter style for \"" + label + "\": " + raw, e);
        }
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
