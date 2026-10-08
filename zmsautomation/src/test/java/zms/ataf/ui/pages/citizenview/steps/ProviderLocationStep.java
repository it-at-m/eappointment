package zms.ataf.ui.pages.citizenview.steps;

import java.time.Duration;
import java.util.HashSet;
import java.util.Set;
import java.util.function.BooleanSupplier;
import java.util.function.IntConsumer;
import java.util.function.IntSupplier;

import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import com.fasterxml.jackson.databind.JsonNode;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.utils.DriverUtil;
import zms.ataf.ui.pages.citizenview.CitizenViewPageContext;
import zms.ataf.ui.pages.citizenview.support.CitizenViewJson;
import zms.ataf.ui.pages.citizenview.support.CitizenViewWaits;
import zms.ataf.ui.pages.citizenview.support.ShadowDom;
import zms.ataf.ui.pages.citizenview.support.SlotBookingState;
import org.openqa.selenium.TimeoutException;

/**
 * Location step: provider checkboxes, single-provider teaser, office order.
 */
public final class ProviderLocationStep {

    static final String[] OFFICE_FREQUENCY = {
        "Bürgerbüro Ruppertstraße",
        "Bürgerbüro Orleansplatz",
        "Bürgerbüro Pasing",
        "Bürgerbüro Riesenfeldstraße",
        "Bürgerbüro Forstenrieder Allee",
        "Bürgerbüro Leonrodstraße"
    };
    static final String OFFICE_SCHEIDPLATZ = "Bürgerbüro Scheidplatz";


    private final CitizenViewPageContext context;
    private final ShadowDom shadow;
    private final CitizenViewJson json;
    private final SlotBookingState slotState;
    private final int defaultWaitSeconds;
    private IntConsumer waitForSlots;
    private IntSupplier slotBookingWaitTimeoutSeconds;
    private BooleanSupplier spinnerVisible;

    public ProviderLocationStep(
            CitizenViewPageContext context,
            ShadowDom shadow,
            CitizenViewJson json,
            SlotBookingState slotState,
            int defaultWaitSeconds) {
        this.context = context;
        this.shadow = shadow;
        this.json = json;
        this.slotState = slotState;
        this.defaultWaitSeconds = defaultWaitSeconds;
    }

    /** Wire slot waits after TimeSlotStep exists (avoids ctor cycles). */
    public void setSlotWaitBridge(IntConsumer waitForSlots, IntSupplier slotBookingWaitTimeoutSeconds,
            BooleanSupplier spinnerVisible) {
        this.waitForSlots = waitForSlots;
        this.slotBookingWaitTimeoutSeconds = slotBookingWaitTimeoutSeconds;
        this.spinnerVisible = spinnerVisible;
    }


    public boolean locationStepShowsProvider(int officeId) {
        context.set();
        return shadow.deepElementExists("#checkbox-provider-" + officeId)
                || deepLocationSingleProviderTeaserPresent(officeId);
    }

    /** Single-provider layout: teaser headline {@code #provider-{id}} under Ort (no checkboxes). */
    public boolean deepLocationSingleProviderTeaserPresent(int officeId) {
        context.set();
        String script =
                "var id='provider-'+arguments[0];function has(root){if(!root)return false;"
                        + "var h=root.querySelector('h3#'+id+'.m-teaser-contained-contact__headline');"
                        + "if(h)return true;var all=root.querySelectorAll('*');"
                        + "for(var i=0;i<all.length;i++)if(all[i].shadowRoot&&has(all[i].shadowRoot))return true;return false;}"
                        + "return has(document.body);";
        return Boolean.TRUE.equals(
                ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, officeId));
    }

    public void assertProviderCheckboxPresent(int officeId) {
        context.set();
        waitUntilLocationStepShowsProvider(officeId, defaultWaitSeconds);
        logLocationProviderResolution(officeId);
        Assert.assertTrue(locationStepShowsProvider(officeId), "Ort must show provider " + officeId);
    }

    public void assertProviderCheckboxAbsent(int officeId) {
        context.set();
        Assert.assertFalse(
                shadow.deepElementExists("#checkbox-provider-" + officeId),
                "Provider checkbox for office " + officeId + " must not appear for this jump-in/service.");
        Assert.assertFalse(
                deepLocationSingleProviderTeaserPresent(officeId),
                "Single-provider Ort teaser for office " + officeId + " must not appear.");
    }

    /**
     * Ort checkboxes start selected. Ranked Bürgerbüros follow frequency order. Scheidplatz has no
     * frequency rank in the catalog, so it follows them. Each checkbox also shows its address.
     */
    public void assertOfficesCheckedInFrequencyOrder() {
        context.set();
        JsonNode offices;
        try {
            offices = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                    .until(d -> {
                        JsonNode node = providerCheckboxes().path("offices");
                        return node.size() > 1 ? node : null;
                    });
        } catch (TimeoutException e) {
            Assert.fail("Location checkboxes did not appear: " + providerCheckboxes());
            return;
        }
        assertOfficeOrder(offices, true, true);
    }

    /** One bookable office: a contact tile, no location checkboxes. */
    public void assertSingleOfficeTile(int officeId, String name, String street) {
        context.set();
        JsonNode tile = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> {
                    JsonNode node = officeTile(officeId);
                    return node.path("name").asText().contains(name) ? node : null;
                });
        Assert.assertEquals(tile.path("checkboxes").asInt(), 0, "One office has no checkboxes: " + tile);
        Assert.assertEquals(tile.path("name").asText(), name, "Tile name: " + tile);
        Assert.assertTrue(tile.path("text").asText().contains(street), "Tile should show the street: " + tile);
        String icons = tile.path("icons").asText();
        Assert.assertTrue(icons.contains("icon-place"), "Tile should use the place icon: " + tile);
        Assert.assertTrue(icons.contains("icon-map-pin"), "Tile should use the map pin: " + tile);
    }

    /**
     * Jump-in can pre-select the only provider; clicking again toggles off. True if that office is already on.
     */
    public boolean deepProviderCheckboxChecked(int officeId) {
        context.set();
        String script =
                "var id=arguments[0];function find(root,id){if(!root)return null;"
                        + "var q=root.querySelector('#checkbox-provider-'+id);if(q)return q;"
                        + "var all=root.querySelectorAll('*');for(var i=0;i<all.length;i++){if(all[i].shadowRoot){var f=find(all[i].shadowRoot,id);if(f)return f;}}return null;}"
                        + "var e=document.querySelector('#checkbox-provider-'+id)||find(document.body,id);if(!e)return false;"
                        + "if(e.tagName==='INPUT'&&e.type==='checkbox')return !!e.checked;"
                        + "if(e.shadowRoot){var inp=e.shadowRoot.querySelector('input[type=checkbox]');if(inp)return !!inp.checked;}"
                        + "var inp2=e.querySelector('input[type=checkbox]');if(inp2)return !!inp2.checked;"
                        + "return e.getAttribute('aria-checked')==='true'||e.classList.contains('is-selected');";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, officeId);
        return Boolean.TRUE.equals(o);
    }

    public void selectOfficeById(int officeId) {
        context.set();
        logLocationProviderResolution(officeId);
        if (shadow.deepElementExists("#checkbox-provider-" + officeId)) {
            if (deepProviderCheckboxChecked(officeId)) {
                ScenarioLogManager.getLogger()
                        .info(
                                "zmscitizenview: Ort checkbox provider {} already checked (jump-in); skip click",
                                officeId);
            } else {
                shadow.deepClickRequired("#checkbox-provider-" + officeId);
                ScenarioLogManager.getLogger()
                        .info("zmscitizenview: clicked Ort checkbox provider {}", officeId);
            }
        } else if (deepLocationSingleProviderTeaserPresent(officeId)) {
            ScenarioLogManager.getLogger()
                    .info(
                            "zmscitizenview: Ort single-provider teaser already selected provider {} (no checkbox)",
                            officeId);
        } else {
            Assert.fail("Ort: no checkbox and no single-provider teaser for provider " + officeId);
        }
        slotState.lastSlotBookingOfficeId = officeId;
    }

    /**
     * Waits after combination → Ort/ Zeit: multi-provider checkboxes or single-provider teaser.
     */
    public void waitUntilLocationStepShowsProvider(int officeId, int maxSeconds) {
        context.set();
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: Ort step — start waiting for provider {} (checkbox or single-provider teaser), up to {}s",
                        officeId,
                        maxSeconds);
        long deadline = java.lang.System.currentTimeMillis() + maxSeconds * 1000L;
        while (java.lang.System.currentTimeMillis() < deadline) {
            if (locationStepShowsProvider(officeId)) {
                ScenarioLogManager.getLogger()
                        .info(
                                "zmscitizenview: Ort step — provider {} found ({})",
                                officeId,
                                shadow.deepElementExists("#checkbox-provider-" + officeId)
                                        ? "checkbox list"
                                        : "single-provider teaser");
                return;
            }
            try {
                Thread.sleep(500L);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
                break;
            }
        }
        logLocationProviderResolution(officeId);
        Assert.fail(
                "Ort step did not show provider "
                        + officeId
                        + " within "
                        + maxSeconds
                        + "s (no checkbox-provider-"
                        + officeId
                        + " and no single-provider teaser)");
    }

    /**
     * Logs checkbox ids in DOM + whether single-provider teaser matches; asserts expected provider is shown in Ort.
     */
    public void logLocationProviderResolution(int expectedOfficeId) {
        context.set();
        String script =
                "var ids=[];function collect(r){if(!r)return;var a=r.querySelectorAll('[id^=\"checkbox-provider-\"]');"
                        + "for(var i=0;i<a.length;i++)ids.push(a[i].id);var q=r.querySelectorAll('*');"
                        + "for(var j=0;j<q.length;j++)if(q[j].shadowRoot)collect(q[j].shadowRoot);}"
                        + "collect(document.body);return ids.join(',');";
        String found =
                String.valueOf(((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script));
        boolean checkboxOk = found.contains("checkbox-provider-" + expectedOfficeId);
        boolean teaserOk = deepLocationSingleProviderTeaserPresent(expectedOfficeId);
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: Ort provider resolution — checkboxIds=[{}] checkboxHit={} singleProviderTeaser={} expected={}",
                        found,
                        checkboxOk,
                        teaserOk,
                        expectedOfficeId);
        Assert.assertTrue(
                checkboxOk || teaserOk,
                "Ort must list provider "
                        + expectedOfficeId
                        + " (checkbox or single teaser); checkboxes=["
                        + found
                        + "] teaser="
                        + teaserOk);
    }

    public JsonNode providerCheckboxes() {
        return json.citizenJson(
                "(function(){function inView(el){var n=el;var self=true;while(n&&n.nodeType===1){"
                        + "var st=window.getComputedStyle(n);if(st.display==='none'||st.visibility==='hidden')return false;"
                        + "if(!self&&st.opacity==='0')return false;self=false;"
                        + "if(n.parentElement){n=n.parentElement;continue;}"
                        + "var root=n.getRootNode&&n.getRootNode();n=root&&root.host?root.host:null;}return true;}"
                        + "function officeText(el){var cur=el;var guard=0;var text='';"
                        + "while(cur&&guard++<8){text=textOf(cur);var boxes=0;"
                        + "if(cur.querySelectorAll){boxes=cur.querySelectorAll('[id^=\"checkbox-provider-\"]').length;}"
                        + "if(text.indexOf('Bürgerbüro')>=0&&boxes<=1)return text;"
                        + "if(cur.parentElement){cur=cur.parentElement;continue;}"
                        + "var root=cur.getRootNode&&cur.getRootNode();cur=root&&root.host?root.host:null;}"
                        + "return text;}"
                        + "var nodes=cssAll('[id^=\"checkbox-provider-\"]');var seen={};var offices=[];"
                        + "for(var i=0;i<nodes.length;i++){var el=nodes[i];var id=el.id||'';"
                        + "if(!/^checkbox-provider-\\d+$/.test(id)||seen[id]||!inView(el))continue;seen[id]=true;"
                        + "var checked=false;if(el.tagName==='INPUT'&&el.type==='checkbox')checked=!!el.checked;"
                        + "else if(el.shadowRoot){var inp=el.shadowRoot.querySelector('input[type=checkbox]');"
                        + "if(inp)checked=!!inp.checked;}if(!checked){var inp2=el.querySelector&&el.querySelector('input[type=checkbox]');"
                        + "if(inp2)checked=!!inp2.checked;else checked=el.getAttribute('aria-checked')==='true'"
                        + "||(el.classList&&el.classList.contains('is-selected'));}"
                        + "offices.push({id:id,text:officeText(el),checked:!!checked});}"
                        + "return {offices:offices};})()");
    }

    public JsonNode officeTile(int officeId) {
        return json.citizenJson(
                "(function(){var id='provider-'+__args[0];var boxes=cssAll('[id^=\"checkbox-provider-\"]');"
                        + "var seen={};var checkboxCount=0;for(var i=0;i<boxes.length;i++){var box=boxes[i];"
                        + "if(!shown(box)||seen[box.id]||!/^checkbox-provider-\\d+$/.test(box.id||''))continue;"
                        + "seen[box.id]=true;checkboxCount++;}"
                        + "var heads=cssAll('h3.m-teaser-contained-contact__headline');var h=null;"
                        + "for(var n=0;n<heads.length;n++){if(heads[n].id===id&&shown(heads[n])){h=heads[n];break;}}"
                        + "var teaser=h;while(teaser&&!(teaser.classList&&teaser.classList.contains('m-teaser-contained-contact'))){"
                        + "if(teaser.parentElement)teaser=teaser.parentElement;else{var root=teaser.getRootNode&&teaser.getRootNode();"
                        + "teaser=root&&root.host?root.host:null;}}"
                        + "var icons='';if(teaser){var uses=teaser.querySelectorAll('use');"
                        + "for(var u=0;u<uses.length;u++){icons+=' '+(uses[u].getAttribute('href')||'')"
                        + "+' '+(uses[u].getAttribute('xlink:href')||'');}}"
                        + "return {checkboxes:checkboxCount,name:h?textOf(h):'',text:teaser?textOf(teaser):'',icons:icons};})()",
                officeId);
    }

    public void assertOfficeOrder(JsonNode offices, boolean requireChecked, boolean requireAllKnown) {
        int lastRank = -1;
        boolean scheidplatz = false;
        Set<String> seen = new HashSet<>();
        Assert.assertTrue(offices.size() >= 2, "Expected several offices: " + offices);
        for (JsonNode office : offices) {
            String text = office.path("text").asText();
            if (requireChecked) {
                Assert.assertTrue(office.path("checked").asBoolean(), "Checkbox starts selected: " + office);
                Assert.assertTrue(text.matches(".*\\d.*"), "Checkbox should show the address: " + text);
            } else {
                Assert.assertTrue(office.path("pin").asBoolean(), "Location heading needs a map pin: " + office);
            }
            String name = frequencyName(text);
            if (name == null) {
                Assert.assertTrue(
                        text.contains(OFFICE_SCHEIDPLATZ),
                        "Unexpected office: " + text + " offices=" + offices);
                Assert.assertFalse(scheidplatz, "Scheidplatz appears twice: " + offices);
                if (requireAllKnown) {
                    Assert.assertEquals(
                            lastRank,
                            OFFICE_FREQUENCY.length - 1,
                            "Scheidplatz follows the ranked Bürgerbüros: " + offices);
                }
                scheidplatz = true;
                continue;
            }
            Assert.assertFalse(scheidplatz, "A ranked office follows Scheidplatz: " + offices);
            int rank = frequencyRank(name);
            Assert.assertTrue(rank > lastRank, "Office order broke at " + name + ": " + offices);
            lastRank = rank;
            seen.add(name);
        }
        Assert.assertTrue(
                seen.contains("Bürgerbüro Ruppertstraße"),
                "Bürgerbüro Ruppertstraße should be listed: " + offices);
        if (requireAllKnown) {
            String[] required = {
                "Bürgerbüro Ruppertstraße",
                "Bürgerbüro Orleansplatz",
                "Bürgerbüro Pasing",
                "Bürgerbüro Forstenrieder Allee",
                "Bürgerbüro Leonrodstraße"
            };
            for (String name : required) {
                Assert.assertTrue(seen.contains(name), "Missing " + name + " in " + offices);
            }
            Assert.assertTrue(
                    scheidplatz,
                    "Bürgerbüro Scheidplatz should follow the ranked offices: " + offices);
        }
    }

    public static String frequencyName(String text) {
        String found = null;
        for (String name : OFFICE_FREQUENCY) {
            if (text.contains(name) && (found == null || name.length() > found.length())) {
                found = name;
            }
        }
        return found;
    }

    public static int frequencyRank(String name) {
        for (int i = 0; i < OFFICE_FREQUENCY.length; i++) {
            if (OFFICE_FREQUENCY[i].equals(name)) {
                return i;
            }
        }
        return -1;
    }

    /** Wait until provider-toggle spinner activity has settled (best effort). */
    public void waitUntilProviderToggleSettled(int maxSeconds) {
        context.set();
        long deadline = System.currentTimeMillis() + maxSeconds * 1000L;
        while (System.currentTimeMillis() < deadline) {
            if (spinnerVisible == null || !spinnerVisible.getAsBoolean()) {
                return;
            }
            try {
                Thread.sleep(250L);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
                return;
            }
        }
    }


    /**
     * Red MucCheckboxGroup error under Ort when no location is selected
     * ({@code errorMessageProviderSelection}).
     */
    public void assertProviderSelectionErrorVisible() {
        context.set();
        String message = "Bitte wählen Sie mindestens einen Ort aus, um passende Termine zu sehen.";
        long deadline = System.currentTimeMillis() + 20_000L;
        while (System.currentTimeMillis() < deadline) {
            if (shadow.shadowDomContainsText(message)) {
                ScenarioLogManager.getLogger()
                        .info("zmscitizenview: Ort provider-selection error visible");
                return;
            }
            CitizenViewWaits.sleepQuiet(250L);
        }
        Assert.assertTrue(
                shadow.shadowDomContainsText(message),
                "Expected Ort error \"" + message + "\" after unchecking all providers");
    }

    public void keepOnlyProviderCheckboxesChecked(Set<Integer> allowedOfficeIds) {
        context.set();
        Set<Integer> allowed = new HashSet<>(allowedOfficeIds);
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: keep only providers {} checked on Ort step", allowed);

        String script =
                "function collect(root,out){"
                        + "  if(!root)return;"
                        + "  var nodes=root.querySelectorAll('[id^=\"checkbox-provider-\"]');"
                        + "  for(var i=0;i<nodes.length;i++){if(nodes[i]&&nodes[i].id)out.push(nodes[i].id);}"
                        + "  var all=root.querySelectorAll('*');"
                        + "  for(var j=0;j<all.length;j++)if(all[j].shadowRoot)collect(all[j].shadowRoot,out);"
                        + "}"
                        + "var ids=[];collect(document.body,ids);"
                        + "return ids;";
        String allowedCsv = allowed.stream().map(String::valueOf).reduce((a, b) -> a + "," + b).orElse("");
        Object idsObj = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, allowedCsv);
        Set<Integer> presentIds = new HashSet<>();
        if (idsObj instanceof java.util.List<?>) {
            for (Object rawId : (java.util.List<?>) idsObj) {
                String idStr = String.valueOf(rawId);
                if (idStr.startsWith("checkbox-provider-")) {
                    try {
                        presentIds.add(Integer.parseInt(idStr.substring("checkbox-provider-".length())));
                    } catch (NumberFormatException ignored) {
                        // Ignore malformed provider ids.
                    }
                }
            }
        }
        int checkboxCount = presentIds.size();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: Ort provider checkbox count detected={}", checkboxCount);

        if (checkboxCount == 0) {
            ScenarioLogManager.getLogger()
                    .info("zmscitizenview: no provider checkboxes found (single-provider teaser layout), nothing to normalize");
            return;
        }

        for (Integer officeId : presentIds) {
            boolean shouldBeChecked = allowed.contains(officeId);
            boolean currentlyChecked = deepProviderCheckboxChecked(officeId);
            if (shouldBeChecked != currentlyChecked) {
                shadow.deepClickRequired("#checkbox-provider-" + officeId);
                waitUntilProviderToggleSettled(15);
            }
        }

        for (Integer officeId : allowed) {
            Assert.assertTrue(
                    deepProviderCheckboxChecked(officeId),
                    "Expected provider checkbox " + officeId + " to be checked after provider normalization.");
        }

        if (allowed.size() == 1) {
            slotState.lastSlotBookingOfficeId = allowed.iterator().next();
            waitUntilProviderToggleSettled(30);
            try {
                waitForSlots.accept(Math.min(60, slotBookingWaitTimeoutSeconds.getAsInt()));
            } catch (Exception e) {
                ScenarioLogManager.getLogger()
                        .warn("zmscitizenview: slot wait after provider normalization: {}", e.toString());
            }
        }
    }

}
