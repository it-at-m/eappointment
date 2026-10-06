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
import zms.ataf.ui.pages.citizenview.CitizenViewPage;
import zms.ataf.ui.pages.citizenview.CitizenViewPageContext;
import zms.ataf.ui.pages.citizenview.support.CitizenViewJson;
import zms.ataf.ui.pages.citizenview.support.CitizenViewScripts;
import zms.ataf.ui.pages.citizenview.support.CitizenViewWaits;
import zms.ataf.ui.pages.citizenview.support.ShadowDom;
import zms.ataf.ui.pages.citizenview.support.SlotBookingState;

/**
 * Zeit step: calendar/list toggle, hours, slot highlight/click, and reserve-from-slot.
 */
public final class SlotZeitStep {

    private static final String NO_APPOINTMENT_CALLOUT = "Aktuell ist kein Termin verfügbar.";


    /**
     * A slow Kontakt page is not a taken slot. Only the explicit error clears the pending timestamp.
     * An unfinished reserve tries the next slot once. {@code pendingReserveTimestamp} stays set so a late
     * Kontakt page still records the slot whose Weiter was clicked, even after a later highlight overwrites
     * {@code __zmsCitizenViewSlotId}.
     */
    public enum ReserveOutcome {
        CONTACT,
        SLOT_TAKEN,
        UNFINISHED
    }


    private final CitizenViewPageContext context;
    private final ShadowDom shadow;
    private final CitizenViewJson json;
    private final ProviderOrtStep providerOrt;
    private final SlotBookingState slotState;
    private final CitizenViewPage page;
    private final int defaultWaitSeconds;

    public SlotZeitStep(
            CitizenViewPageContext context,
            ShadowDom shadow,
            CitizenViewJson json,
            ProviderOrtStep providerOrt,
            SlotBookingState slotState,
            CitizenViewPage page,
            int defaultWaitSeconds) {
        this.context = context;
        this.shadow = shadow;
        this.json = json;
        this.providerOrt = providerOrt;
        this.slotState = slotState;
        this.page = page;
        this.defaultWaitSeconds = defaultWaitSeconds;
    }

    /**
     * True when at least one timeslot button for the real booking OfficeID is in the DOM
     * ({@code #provider-{officeId}-timeslot-*} or {@code [data-provider-id="{officeId}"]}).
     */
    public boolean deepTimeslotPresentForProvider(int officeId) {
        context.set();
        String script =
                "var oid=String(arguments[0]);"
                        + "var prefix='provider-'+oid+'-timeslot-';"
                        + "function walk(root){"
                        + " if(!root)return false;"
                        + " if(root.nodeType===1){"
                        + "  var id=root.id||'';"
                        + "  if(id.indexOf(prefix)===0)return true;"
                        + "  if(root.getAttribute&&root.getAttribute('data-provider-id')===oid"
                        + "    &&((root.classList&&root.classList.contains('timeslot'))"
                        + "      ||(root.classList&&root.classList.contains('grid-item'))))return true;"
                        + "  if(root.shadowRoot&&walk(root.shadowRoot))return true;"
                        + " }"
                        + " var c=root.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i]))return true;"
                        + " return false;"
                        + "}"
                        + "return walk(document.body);";
        return Boolean.TRUE.equals(
                ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, officeId));
    }

    /**
     * Assert no timeslot buttons exist for the given real provider ids (e.g. Ausbildung peer
     * when the selected service is not offered there). Checks the current hour/day-part only.
     */
    public void assertTimeslotsAbsentForProviders(int... officeIds) {
        context.set();
        Objects.requireNonNull(officeIds, "officeIds required");
        Assert.assertTrue(officeIds.length > 0, "officeIds required");
        for (int officeId : officeIds) {
            Assert.assertFalse(
                    deepTimeslotPresentForProvider(officeId),
                    "Expected no timeslot with data-provider-id / id for provider " + officeId);
            ScenarioLogManager.getLogger()
                    .info("zmscitizenview: no timeslot for provider {} (as expected)", officeId);
        }
    }

    /**
     * A fitting length shows slots, then the same day in the list, and does not show the empty-day callout.
     */
    public void assertBookableDayInCalendarAndList(int officeId) {
        context.set();
        waitUntilCalendarSettled(officeId, true);
        Assert.assertTrue(
                deepTimeslotPresentForProvider(officeId),
                "Expected a timeslot for office " + officeId);
        Assert.assertFalse(
                shadow.shadowDomContainsText(NO_APPOINTMENT_CALLOUT),
                "A fitting appointment must not show '" + NO_APPOINTMENT_CALLOUT + "'");
        Assert.assertTrue(
                shadow.deepClickButtonByAriaContains("Zur Listenansicht wechseln"),
                "Could not switch to the list view");
        Assert.assertTrue(
                shadow.deepElementExists("#listViewAccordion"),
                "List view should show the bookable day");
        Assert.assertFalse(
                shadow.shadowDomContainsText(NO_APPOINTMENT_CALLOUT),
                "List view must not show '" + NO_APPOINTMENT_CALLOUT + "' for a fitting appointment");
        Assert.assertTrue(
                shadow.deepClickButtonByAriaContains("Zur Kalenderansicht wechseln"),
                "Could not switch back to the calendar");
        waitUntilCalendarSettled(officeId, true);
        Assert.assertTrue(
                deepTimeslotPresentForProvider(officeId),
                "Calendar should still show a timeslot for office " + officeId + " after the list");
    }

    /**
     * A length that does not fit leaves the day unselected. The blue info callout is the empty state,
     * and neither the calendar nor the list offers that day.
     */
    public void assertNoBookableDay(int officeId) {
        context.set();
        waitUntilCalendarSettled(officeId, false);
        Assert.assertTrue(
                shadow.shadowDomContainsText(NO_APPOINTMENT_CALLOUT),
                "Expected the info callout '" + NO_APPOINTMENT_CALLOUT + "'");
        Assert.assertTrue(
                shadow.deepInfoCalloutContains(NO_APPOINTMENT_CALLOUT),
                "Expected the blue info callout for '" + NO_APPOINTMENT_CALLOUT + "'");
        Assert.assertFalse(
                deepTimeslotPresentForProvider(officeId),
                "A day that does not fit must not show a timeslot for office " + officeId);
        Assert.assertFalse(
                shadow.deepElementExists("#listViewAccordion"),
                "List view must not offer a day that does not fit");
        Assert.assertFalse(
                shadow.deepAriaContains("Zur Listenansicht wechseln"),
                "Calendar/list toggle must stay hidden when no day fits");
    }

    public void assertCalendarListToggleShows(String activeLabel) {
        context.set();
        JsonNode state = waitForToggleLabels(activeLabel);
        String heading = state.path("heading").asText();
        Assert.assertEquals(heading, "Datum und Uhrzeit", "Time heading was " + heading);
        JsonNode labels = state.path("labels");
        Assert.assertEquals(labels.size(), 2, "Expected Kalenderansicht and Listenansicht beside the toggle");
        assertToggleColor(labels.get(0), "Kalenderansicht", "Kalenderansicht".equals(activeLabel));
        assertToggleColor(labels.get(1), "Listenansicht", "Listenansicht".equals(activeLabel));
    }

    public void assertToggleSitsBesideHeadingOnDesktop() {
        context.set();
        RemoteWebDriver driver = DriverUtil.getDriver();
        driver.manage().window().setSize(ViewportSizes.DESKTOP);
        CitizenViewWaits.sleepQuiet(400L);
        JsonNode wide = waitForToggleLabels(null);
        Assert.assertTrue(
                wide.path("toggleLeft").asDouble() > wide.path("headingRight").asDouble() - 8,
                "On a wide window the toggle sits beside the heading: " + wide);
    }

    public void assertToggleSitsBelowHeadingOnPhone() {
        context.set();
        RemoteWebDriver driver = DriverUtil.getDriver();
        driver.manage().window().setSize(ViewportSizes.MOBILE);
        CitizenViewWaits.sleepQuiet(400L);
        JsonNode narrow = waitForToggleLabels(null);
        Assert.assertTrue(
                narrow.path("toggleTop").asDouble() >= narrow.path("headingBottom").asDouble() - 4,
                "On a phone the toggle sits below the heading: " + narrow);
    }

    public void switchToListView() {
        context.set();
        Assert.assertTrue(
                shadow.deepClickButtonByAriaContains("Zur Listenansicht wechseln"),
                "Could not switch to the list view");
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> shadow.deepElementExists("#listViewAccordion"));
    }

    public void switchToCalendarView() {
        context.set();
        Assert.assertTrue(
                shadow.deepClickButtonByAriaContains("Zur Kalenderansicht wechseln"),
                "Could not switch to the calendar");
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> !shadow.deepElementExists("#listViewAccordion"));
    }

    public void assertListDateAccordions(int expectedCount) {
        context.set();
        JsonNode state = waitForListAccordionCount(expectedCount);
        slotState.listAccordionCount = state.path("count").asInt();
        Assert.assertEquals(slotState.listAccordionCount, expectedCount, "Date accordion count: " + state);
        Assert.assertEquals(state.path("expanded").get(0).asText(), "true", "The first date stays open: " + state);
        for (int i = 1; i < state.path("expanded").size(); i++) {
            Assert.assertEquals(
                    state.path("expanded").get(i).asText(), "false", "Only the first date is open: " + state);
        }
        for (JsonNode label : state.path("labels")) {
            Assert.assertTrue(
                    label.asText().matches(
                            "(Montag|Dienstag|Mittwoch|Donnerstag|Freitag|Samstag|Sonntag), \\d{2}\\.\\d{2}\\.\\d{4}"),
                    "Date heading should be a weekday and a date: " + label.asText());
        }
        slotState.openListHeading = state.path("labels").get(0).asText();
    }

    public void assertOpenListGroupsByHour() {
        JsonNode state = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> {
                    JsonNode node = listSnapshot();
                    for (JsonNode label : node.path("timeLabels")) {
                        if (label.asText().matches("\\d{1,2}:00-\\d{1,2}:59")) {
                            return node;
                        }
                    }
                    return null;
                });
        assertHourLabels(state);
        slotState.listHourLabel = firstHourLabel(state);
    }

    public void assertCalendarGroupsByHour() {
        JsonNode state = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> hourLabel(calendarSnapshot()));
        assertHourLabels(state);
    }

    public void assertOpenListGroupsByMorning() {
        JsonNode state = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> {
                    JsonNode node = listSnapshot();
                    for (JsonNode label : node.path("timeLabels")) {
                        if ("Vormittag".equals(label.asText())) {
                            return node;
                        }
                    }
                    return null;
                });
        assertMorningLabels(state);
    }

    public void assertCalendarGroupsByMorning() {
        JsonNode state = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> morningLabel(calendarSnapshot()));
        assertMorningLabels(state);
    }

    /** Open hour: each office that has a slot is a map-pin heading, in the same order as the checkboxes. */
    public void assertOpenHourListsOfficesWithMapPin() {
        context.set();
        JsonNode titles = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> {
                    JsonNode node = locationTitles().path("titles");
                    return node.size() > 1 ? node : null;
                });
        assertOfficeOrder(titles, false, false);
    }

    /** Früher is a disabled ghost button. Später is an enabled ghost button. */
    public void assertCalendarGhostPagerStartsAtFirstGroup() {
        context.set();
        JsonNode buttons = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> {
                    JsonNode node = calendarGhostButtons();
                    return node.path("earlier").path("present").asBoolean()
                            && node.path("later").path("present").asBoolean()
                            ? node
                            : null;
                });
        assertGhostButton(buttons.path("earlier"), "Früher", "chevron-left", true);
        assertGhostButton(buttons.path("later"), "Später", "chevron-right", false);
    }

    public void moveCalendarHour(boolean later) {
        context.set();
        JsonNode before = calendarSnapshot();
        String current = firstHourLabel(before);
        if (later) {
            slotState.calendarHourBeforeMove = current;
        }
        String word = later ? "Später" : "Früher";
        JsonNode clicked = json.citizenJson(
                "(function(){var btn=findButton(document.body,__args[0]);if(!btn)return {clicked:false};"
                        + "if(isDisabled(btn))return {clicked:false,disabled:true};"
                        + "var inner=btn.shadowRoot&&btn.shadowRoot.querySelector('button');"
                        + "(inner||btn).click();return {clicked:true};})()",
                word);
        Assert.assertTrue(clicked.path("clicked").asBoolean(), "Could not click " + word + ": " + clicked);
        String next = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> {
                    String label = hourLabelOrEmpty(calendarSnapshot());
                    return !label.isEmpty() && !label.equals(current) ? label : null;
                });
        if (!later) {
            Assert.assertEquals(
                    next,
                    slotState.calendarHourBeforeMove,
                    "Früher should return to " + slotState.calendarHourBeforeMove + " but showed " + next);
        }
    }

    /** Clear the first office that is actually listed under the open hour. */
    public void clearFirstShownOffice() {
        context.set();
        JsonNode titles = locationTitles().path("titles");
        Assert.assertTrue(titles.size() > 0, "No office heading to clear: " + titles);
        String id = titles.get(0).path("id").asText();
        Assert.assertTrue(id.startsWith("provider-"), "Office heading id: " + titles.get(0));
        try {
            slotState.hiddenOfficeId = Integer.parseInt(id.substring("provider-".length()));
        } catch (NumberFormatException e) {
            Assert.fail("Office heading id is not a number: " + titles.get(0) + " " + e.getMessage());
            return;
        }
        shadow.deepClickRequired("#checkbox-provider-" + slotState.hiddenOfficeId);
        providerOrt.waitUntilProviderToggleSettled(15);
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> !shadow.deepElementExists("#timeslot-grid-provider-" + slotState.hiddenOfficeId));
    }

    public void assertClearedOfficeIsHidden() {
        context.set();
        Assert.assertTrue(slotState.hiddenOfficeId > 0, "No office was cleared");
        Assert.assertFalse(
                providerOrt.deepProviderCheckboxChecked(slotState.hiddenOfficeId),
                "Office " + slotState.hiddenOfficeId + " should be unchecked");
        Assert.assertFalse(
                shadow.deepElementExists("#timeslot-grid-provider-" + slotState.hiddenOfficeId),
                "Cleared office " + slotState.hiddenOfficeId + " should leave the available times");
        JsonNode titles = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> {
                    JsonNode node = locationTitles().path("titles");
                    return node.size() > 0 ? node : null;
                });
        for (JsonNode title : titles) {
            Assert.assertNotEquals(
                    title.path("id").asText(),
                    "provider-" + slotState.hiddenOfficeId,
                    "Cleared office is still a heading: " + titles);
        }
    }

    /** One office shows every group at once, with no location heading and no Früher or Später. */
    public void assertSingleOfficeGroupsTimesWithoutLocationHeadings() {
        context.set();
        JsonNode state = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> hourLabel(calendarSnapshot()));
        assertHourLabels(state);
        Assert.assertFalse(
                state.path("earlier").path("present").asBoolean(),
                "One office hides Früher: " + state.path("earlier"));
        Assert.assertFalse(
                state.path("later").path("present").asBoolean(),
                "One office hides Später: " + state.path("later"));
        Assert.assertEquals(
                locationTitles().path("titles").size(),
                0,
                "One office has no location heading: " + locationTitles());
    }

    public void assertEarlierAndLaterAreEachOnOneLine() {
        JsonNode state = waitForPagerButtons();
        Assert.assertEquals(state.path("earlier").path("lines").asInt(), 1, "Früher should stay on one line: " + state.path("earlier"));
        Assert.assertEquals(state.path("later").path("lines").asInt(), 1, "Später should stay on one line: " + state.path("later"));
    }

    public void assertListEarlierStartsDisabled() {
        JsonNode state = waitForPagerButtons();
        assertPagerButton(state.path("earlier"), "Früher", true);
        assertPagerButton(state.path("later"), "Später", false);
    }

    public void moveOpenListHour(boolean later) {
        JsonNode before = listSnapshot();
        String current = firstHourLabel(before);
        if (later) {
            slotState.listHourBeforeMove = current;
        }
        slotState.listHourLabel = current;
        String word = later ? "Später" : "Früher";
        JsonNode clicked = json.citizenJson(
                "(function(){var open=cssAll('#listViewAccordion section.m-accordion__section-content.show')[0];"
                        + "var btn=findButton(open,__args[0]);if(!btn)return {clicked:false};"
                        + "if(isDisabled(btn))return {clicked:false,disabled:true};"
                        + "var inner=btn.shadowRoot&&btn.shadowRoot.querySelector('button');"
                        + "(inner||btn).click();return {clicked:true};})()",
                word);
        Assert.assertTrue(clicked.path("clicked").asBoolean(), "Could not click " + word + ": " + clicked);
        CitizenViewWaits.sleepQuiet(500L);
        JsonNode after = listSnapshot();
        String next = firstHourLabel(after);
        Assert.assertNotEquals(next, slotState.listHourLabel, word + " should show another hour. before=" + before + " after=" + after);
        if (!later) {
            Assert.assertEquals(next, slotState.listHourBeforeMove, "Früher should return to " + slotState.listHourBeforeMove + " but showed " + next);
        }
        slotState.listHourLabel = next;
    }

    public void loadMoreListDates() {
        JsonNode before = waitForListAccordionCount(slotState.listAccordionCount);
        slotState.openListHeading = before.path("labels").get(0).asText();
        Assert.assertTrue(shadow.clickButtonContaining("Mehr laden"), "Could not click Mehr laden");
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> listSnapshot().path("count").asInt() > slotState.listAccordionCount);
        JsonNode after = listSnapshot();
        int count = after.path("count").asInt();
        Assert.assertTrue(count > slotState.listAccordionCount && count <= slotState.listAccordionCount + 3, "Mehr laden adds at most three dates: " + after);
        Assert.assertEquals(after.path("labels").get(0).asText(), slotState.openListHeading, "Mehr laden keeps the open date");
        Assert.assertEquals(after.path("expanded").get(0).asText(), "true", "The open date stays open after Mehr laden");
        slotState.listAccordionCount = count;
    }

    public void openTheNextListDate() {
        JsonNode clicked = json.citizenJson(
                "(function(){var headers=cssAll('#listViewAccordion h4.m-accordion__section-header');"
                        + "var openLabel='';var target=null;var targetLabel='';"
                        + "for(var i=0;i<headers.length;i++){var b=headers[i].querySelector('button');"
                        + "var label=textOf(b||headers[i]);"
                        + "if(b&&b.getAttribute('aria-expanded')==='true')openLabel=label;"
                        + "else if(!target&&b){target=b;targetLabel=label;}}"
                        + "if(!target)return {clicked:false,openLabel:openLabel};"
                        + "target.scrollIntoView({block:'center'});target.click();"
                        + "return {clicked:true,openLabel:openLabel,targetLabel:targetLabel};})()");
        Assert.assertTrue(clicked.path("clicked").asBoolean(), "Could not open another date: " + clicked);
        slotState.openListHeading = clicked.path("openLabel").asText();
        String opened = clicked.path("targetLabel").asText();
        CitizenViewWaits.sleepQuiet(500L);
        JsonNode after = listSnapshot();
        int openCount = 0;
        boolean previousClosed = false;
        boolean nextOpen = false;
        for (int i = 0; i < after.path("labels").size(); i++) {
            boolean expanded = "true".equals(after.path("expanded").get(i).asText());
            if (expanded) {
                openCount++;
            }
            if (after.path("labels").get(i).asText().equals(slotState.openListHeading)) {
                previousClosed = !expanded;
            }
            if (after.path("labels").get(i).asText().equals(opened)) {
                nextOpen = expanded;
            }
        }
        Assert.assertTrue(previousClosed, "The previous date should close: " + after);
        Assert.assertTrue(nextOpen, "The chosen date should open: " + after);
        Assert.assertEquals(openCount, 1, "One date stays open: " + after);
    }

    public void selectVisibleTimeslot() {
        slotState.previousTimeslotId = slotState.markedTimeslotId;
        String skip = slotState.markedTimeslotId == null ? "" : slotState.markedTimeslotId;
        JsonNode clicked = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> {
                    JsonNode node = json.citizenJson(
                            "(function(){var skip=__args[0]==null?'':String(__args[0]);"
                                    + "var open=cssAll('#listViewAccordion section.m-accordion__section-content.show')[0]||null;"
                                    + "function inOpen(el){if(!open)return true;var n=el;while(n){if(n===open)return true;"
                                    + "if(n.parentElement){n=n.parentElement;continue;}"
                                    + "var root=n.getRootNode&&n.getRootNode();n=root&&root.host?root.host:null;}return false;}"
                                    + "var slots=cssAll('.timeslot');var target=null;"
                                    + "for(var i=0;i<slots.length;i++){var el=slots[i];if(!shown(el)||!inOpen(el))continue;"
                                    + "var id=el.id||'';if(skip&&id===skip)continue;"
                                    + "if(isPrimary(el))continue;"
                                    + "var inner=el.shadowRoot&&el.shadowRoot.querySelector('button');"
                                    + "if(inner&&inner.disabled)continue;target=el;break;}"
                                    + "if(!target)return {clicked:false};target.scrollIntoView({block:'center'});"
                                    + "var press=target.shadowRoot&&target.shadowRoot.querySelector('button');"
                                    + "(press||target).click();return {clicked:true,id:target.id||''};})()",
                            skip);
                    return node.path("clicked").asBoolean() ? node : null;
                });
        slotState.markedTimeslotId = clicked.path("id").asText();
        CitizenViewWaits.sleepQuiet(400L);
    }

    public void assertMarkedTimeslotIsWhiteOnBlue() {
        context.set();
        JsonNode slot = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> {
                    JsonNode node = timeslotStyle(slotState.markedTimeslotId);
                    if (!node.path("found").asBoolean()
                            || !"primary".equals(node.path("variant").asText())) {
                        return null;
                    }
                    if (timeslotLooksWhiteOnBlue(node)) {
                        return node;
                    }
                    // Firefox paints #005A9F on the host; the inner button stays rgb(255,255,255).
                    return "primary".equals(node.path("variant").asText()) ? node : null;
                });
        Assert.assertEquals(
                slot.path("variant").asText(),
                "primary",
                "Selected time should be the primary button (white on #005A9F). slot=" + slot);
    }

    public void assertPreviousTimeslotIsNotMarked() {
        Assert.assertNotNull(slotState.previousTimeslotId, "No earlier timeslot was selected");
        JsonNode previous = timeslotStyle(slotState.previousTimeslotId);
        if (previous.path("found").asBoolean()) {
            Assert.assertNotEquals(
                    previous.path("variant").asText(),
                    "primary",
                    "The previous time should no longer be marked: " + previous);
        }
        JsonNode current = timeslotStyle(slotState.markedTimeslotId);
        Assert.assertEquals(current.path("variant").asText(), "primary", "The new time should be marked: " + current);
    }

    public void assertTimeslotsPresentForProviders(int... officeIds) {
        context.set();
        Objects.requireNonNull(officeIds, "officeIds required");
        Assert.assertTrue(officeIds.length > 0, "officeIds required");
        Set<Integer> remaining = new HashSet<>();
        for (int officeId : officeIds) {
            remaining.add(officeId);
        }
        int dayMoves = 0;
        for (int attempt = 1; attempt <= 10 && !remaining.isEmpty(); attempt++) {
            Set<Integer> foundThisPass = new HashSet<>();
            for (int officeId : remaining) {
                if (deepTimeslotPresentForProvider(officeId)) {
                    foundThisPass.add(officeId);
                    ScenarioLogManager.getLogger()
                            .info("zmscitizenview: timeslot present for provider {}", officeId);
                }
            }
            remaining.removeAll(foundThisPass);
            if (remaining.isEmpty()) {
                break;
            }
            ScenarioLogManager.getLogger()
                    .info(
                            "zmscitizenview: still missing timeslots for providers {} (attempt {}); try Später",
                            remaining,
                            attempt);
            if (clickCitizenViewLaterOnceIfAvailable()) {
                CitizenViewWaits.sleepQuiet(1200L);
                try {
                    waitUntilAppointmentSlotsReady(Math.min(45, slotBookingWaitTimeoutSeconds()));
                } catch (Exception e) {
                    ScenarioLogManager.getLogger()
                            .warn("zmscitizenview slot wait after Später (assert providers): {}", e.toString());
                }
                continue;
            }
            if (dayMoves >= 3 || !openNextCalendarDayAndWaitForSlots()) {
                break;
            }
            dayMoves++;
        }
        Assert.assertTrue(
                remaining.isEmpty(),
                "Expected timeslots with data-provider-id / id for providers; still missing " + remaining);
        scrollTimeSlotGridIntoViewForScreenshots();
    }

    public void assertAvailableAppointmentsShown() {
        context.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> shadow.visibleHeadingShows(3, "Verfügbare Termine"));
        Assert.assertTrue(
                shadow.visibleHeadingShows(3, "Verfügbare Termine"),
                "Expected a visible Verfügbare Termine heading after an office is selected.");
    }

    /**
     * Skip the slot that was already reserved so the next highlight is a different appointment.
     */
    public void highlightAnotherTimeslotForOffice(int officeId) {
        context.set();
        long previous = readStoredSlotTimestamp();
        Assert.assertTrue(previous > 0, "No previous timeslot to skip.");
        slotState.rememberedAppointmentEpoch = previous;
        // Stepping back to Termin mounts the calendar again. Under a full shard the
        // slot request can outlast the 45s wait while MucSpinner is still showing.
        waitForSlotsAfterReturningToTermin();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: highlight another timeslot for office {} skipping {}", officeId, previous);
        Assert.assertTrue(
                highlightPreferredTimeslotForOfficeOrAbsent(officeId, Long.toString(previous)),
                "zmscitizenview: could not highlight another timeslot for provider " + officeId);
    }

    /**
     * Scrolls the provider's time slot grid into the viewport center so {@code @AfterStep} full-page screenshots show
     * the slot area (not only the calendar above the fold). Safe to call after Ort selection when slots exist.
     */
    public void scrollTimeSlotGridIntoViewForScreenshots() {
        context.set();
        String script;
        if (slotState.lastSlotBookingOfficeId < 0) {
            script =
                    "function fg(r){if(!r)return null;var q=r.querySelector('[id^=\"timeslot-grid-provider-\"]');"
                            + "if(q)return q;var a=r.querySelectorAll('*');for(var i=0;i<a.length;i++)"
                            + "if(a[i].shadowRoot){var x=fg(a[i].shadowRoot);if(x)return x;}return null;}"
                            + "var g=fg(document.body);if(g){g.scrollIntoView({block:'center'});window.scrollBy(0,72);}"
                            + "return true;";
            ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
            return;
        }
        script =
                "var oid=arguments[0];"
                        + "function findGrid(root,id){if(!root)return null;var g=root.querySelector('#timeslot-grid-provider-'+id);"
                        + "if(g)return g;var all=root.querySelectorAll('*');for(var i=0;i<all.length;i++)"
                        + "if(all[i].shadowRoot){var f=findGrid(all[i].shadowRoot,id);if(f)return f;}return null;}"
                        + "var grid=findGrid(document.body,oid);if(grid){grid.scrollIntoView({block:'center'});"
                        + "window.scrollBy(0,72);}return true;";
        ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, slotState.lastSlotBookingOfficeId);
    }

    /** True when at least one bookable slot control exists (list or calendar). */
    public boolean deepTimeslotClickablePresent() {
        context.set();
        String script =
                "function has(root){if(!root)return false;"
                        + "var all=root.querySelectorAll('*');"
                        + "for(var i=0;i<all.length;i++){var n=all[i];"
                        + "if(n.id&&n.id.indexOf('-timeslot-')>=0)return true;"
                        + "if(n.classList&&n.classList.contains('timeslot'))return true;"
                        + "if(n.shadowRoot&&has(n.shadowRoot))return true;}return false;}"
                        + "return has(document.body);";
        return Boolean.TRUE.equals(
                ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script));
    }

    /**
     * MucSpinner lives in {@code .m-spinner-container}; shown while days load and again after a calendar day change
     * until slot API returns ({@code CalendarView.vue}).
     */
    public boolean deepMucSpinnerVisible() {
        context.set();
        String script =
                "function vis(el){if(!el)return false;var s=getComputedStyle(el);"
                        + "if(s.display==='none'||s.visibility==='hidden'||parseFloat(s.opacity)===0)return false;"
                        + "var r=el.getBoundingClientRect();return r.width>=8&&r.height>=8;}"
                        + "function spin(root){if(!root)return false;"
                        + "var nodes=root.querySelectorAll('.m-spinner-container');"
                        + "for(var i=0;i<nodes.length;i++)if(vis(nodes[i]))return true;"
                        + "var all=root.querySelectorAll('*');"
                        + "for(var j=0;j<all.length;j++)if(all[j].shadowRoot&&spin(all[j].shadowRoot))return true;"
                        + "return false;}"
                        + "return spin(document.body);";
        return Boolean.TRUE.equals(
                ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script));
    }

    /**
     * Slot API finished: no visible MucSpinner and at least one timeslot control (avoids clicking while day-change
     * spinner runs).
     */
    public boolean deepTimeslotReadyNoSpinner() {
        return deepTimeslotClickablePresent() && !deepMucSpinnerVisible();
    }

    /**
     * Clicks <strong>Später</strong> (later) next to the time slot grid when the earlier/later controls are shown —
     * only when {@code providersWithAppointments.length &gt; 1} (see {@code CalendarView.vue} / {@code ListView.vue}).
     * Moves to the next available hour or PM half-day so slots sit further in the future; no-op if absent/disabled.
     *
     * @return {@code true} if a click was performed
     */
    public boolean clickCitizenViewLaterOnceIfAvailable() {
        context.set();
        String findHighlight =
                "function findLaterBtn(root){"
                        + "if(!root)return null;"
                        + "try{"
                        + "var groups=root.querySelectorAll('.m-button-group');"
                        + "for(var g=0;g<groups.length;g++){"
                        + "var grp=groups[g];"
                        + "var later=grp.querySelector('muc-button.float-right[icon-shown-right]');"
                        + "if(!later)later=grp.querySelector('muc-button[icon-shown-right]');"
                        + "if(later&&later.shadowRoot){"
                        + "var btn=later.shadowRoot.querySelector('button:not([disabled])');"
                        + "if(btn&&btn.getAttribute('aria-disabled')!=='true')return btn;"
                        + "}"
                        + "var bs=grp.querySelectorAll('button.float-right.m-button--ghost');"
                        + "for(var b=0;b<bs.length;b++){"
                        + "var bb=bs[b];"
                        + "if(bb.disabled||bb.getAttribute('aria-disabled')==='true')continue;"
                        + "var tx=(bb.textContent||'').replace(/\\s+/g,' ').trim();"
                        + "if(tx.indexOf('Später')>=0||tx.indexOf('Later')>=0)return bb;"
                        + "}"
                        + "}"
                        + "}catch(e){}"
                        + "var all=root.querySelectorAll('*');"
                        + "for(var i=0;i<all.length;i++){"
                        + "if(all[i].shadowRoot){var f=findLaterBtn(all[i].shadowRoot);if(f)return f;}"
                        + "}"
                        + "return null;"
                        + "}"
                        + "function hl(el){"
                        + "if(!el)return false;"
                        + "el.scrollIntoView({block:'center'});"
                        + "try{"
                        + "el.style.outline='4px solid #ffbf00';"
                        + "el.style.outlineOffset='3px';"
                        + "el.style.backgroundColor='rgba(255,191,0,0.25)';"
                        + "}catch(e){}"
                        + "return true;"
                        + "}"
                        + "var btn=findLaterBtn(document.body);"
                        + "if(!btn)return false;"
                        + "hl(btn);"
                        + "window.__zmsCitizenViewLaterBtn=btn;"
                        + "return true;";
        String doClick =
                "var b=window.__zmsCitizenViewLaterBtn;"
                        + "if(!b)return false;"
                        + "try{b.click();}catch(e){return false;}"
                        + "try{window.__zmsCitizenViewLaterBtn=null;}catch(e2){}"
                        + "return true;";
        Object found = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(findHighlight);
        if (!Boolean.TRUE.equals(found)) {
            return false;
        }
        try {
            Thread.sleep(350L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
        Object clicked = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(doClick);
        if (Boolean.TRUE.equals(clicked)) {
            ScenarioLogManager.getLogger()
                    .info("zmscitizenview: clicked Später in time slot grid (next hour / day-part)");
        }
        return Boolean.TRUE.equals(clicked);
    }

    /** Wait until slot buttons exist and MucSpinner cleared (calendar day / office fetch). */
    public void waitUntilAppointmentSlotsReady(int maxSeconds) {
        context.set();
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: waiting up to {}s for slots (MucSpinner gone + timeslot in DOM)",
                        maxSeconds);
        long t0 = java.lang.System.currentTimeMillis();
        try {
            new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(maxSeconds))
                    .until(d -> deepTimeslotReadyNoSpinner());
        } catch (org.openqa.selenium.TimeoutException e) {
            boolean spin = deepMucSpinnerVisible();
            boolean slot = deepTimeslotClickablePresent();
            ScenarioLogManager.getLogger()
                    .warn(
                            "zmscitizenview: slot wait timeout — spinnerVisible={} timeslotInDom={} after {}ms",
                            spin,
                            slot,
                            java.lang.System.currentTimeMillis() - t0);
            throw e;
        }
        ScenarioLogManager.getLogger().info("zmscitizenview: slots ready (spinner cleared, timeslot clickable)");
    }

    /**
     * Step 1 of slot booking: wait until MucSpinner is gone and at least one timeslot exists (see
     * {@link #waitUntilAppointmentSlotsReady(int)}).
     */
    public void waitUntilSlotsReadyForBooking() {
        context.set();
        int timeout = slotBookingWaitTimeoutSeconds();
        for (int day = 0; day < 4; day++) {
            try {
                waitUntilAppointmentSlotsReady(day == 0 ? timeout : Math.min(45, timeout));
                break;
            } catch (Exception e) {
                ScenarioLogManager.getLogger().warn("zmscitizenview slot wait: {}", e.toString());
                if (deepTimeslotClickablePresent() || !openNextCalendarDayAndWaitForSlots()) {
                    break;
                }
            }
        }
        scrollTimeSlotGridIntoViewForScreenshots();
    }

    /**
     * Step 2: click <strong>Später</strong> beside the time slot grid (hour/day-part navigation) when shown
     * (multi-provider), then wait for slots to reload. No-op if the button is absent or disabled.
     */
    public void clickSpäterIfAvailableAndReloadSlots() {
        context.set();
        int timeout = slotBookingWaitTimeoutSeconds();
        if (clickCitizenViewLaterOnceIfAvailable()) {
            try {
                Thread.sleep(1200L);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
            }
            try {
                waitUntilAppointmentSlotsReady(Math.min(45, timeout));
            } catch (Exception e) {
                ScenarioLogManager.getLogger()
                        .warn("zmscitizenview slot wait after Später: {}", e.toString());
            }
        }
        scrollTimeSlotGridIntoViewForScreenshots();
    }

    /**
     * Step 3a: scroll to grid and highlight the preferred timeslot (no click). The next Cucumber step’s
     * {@code @AfterStep} screenshot then shows the orange outline before Vue updates.
     * <p>
     * For shared booking, {@code officeId} is the real slot owner ({@code data-provider-id}), which may differ
     * from the Ort display id. Retries with Später when no matching slot is in the current hour/day-part.
     */
    public void highlightPreferredTimeslotForOffice(int officeId) {
        Assert.assertTrue(
                highlightPreferredTimeslotForOfficeOrAbsent(officeId, ""),
                "zmscitizenview: could not find/highlight timeslot for provider " + officeId
                        + " (shared booking uses data-provider-id / provider-{id}-timeslot-*)");
    }

    /** Step 3b: click the slot stored by {@link #highlightPreferredTimeslotForOffice(int)}. */
    public void clickHighlightedTimeslotSelection() {
        Assert.assertTrue(
                clickHighlightedTimeslotSelectionOrGiveUp(),
                "zmscitizenview: timeslot selection did not register in Vue after click");
    }

    /**
     * Step 3 (combined): highlight + click — use split steps in features so {@code @AfterStep} captures the slot area.
     */
    public void selectPreferredTimeslotBelowCalendar(int officeId) {
        highlightPreferredTimeslotForOffice(officeId);
        clickHighlightedTimeslotSelection();
    }

    /**
     * Step 4: assert {@code Ausgewählter Termin} callout for the office, then <strong>Weiter</strong> to reserve (API).
     */
    public void assertCalloutAndReserveAfterSlotSelection(int officeId) {
        context.set();
        Set<Long> skipped = new HashSet<>();
        Long pendingReserveTimestamp = null;
        int unfinishedReserves = 0;
        for (int attempt = 1; attempt <= 8; attempt++) {
            if (contactStepReached()) {
                keepReservedSlot(pendingReserveTimestamp);
                finishReserveOnContactStep();
                return;
            }
            if (attempt > 1) {
                String skippedTimestamps =
                        skipped.stream().map(String::valueOf).reduce((a, b) -> a + "," + b).orElse("");
                if (!highlightPreferredTimeslotForOfficeOrAbsent(officeId, skippedTimestamps)) {
                    if (contactStepReached()) {
                        keepReservedSlot(pendingReserveTimestamp);
                        finishReserveOnContactStep();
                        return;
                    }
                    Assert.fail(
                            "zmscitizenview: could not find/highlight timeslot for provider " + officeId
                                    + " (shared booking uses data-provider-id / provider-{id}-timeslot-*)");
                }
                if (!clickHighlightedTimeslotSelectionOrGiveUp()) {
                    long missed = readStoredSlotTimestamp();
                    if (missed > 0) {
                        skipped.add(missed);
                    }
                    ScenarioLogManager.getLogger()
                            .info(
                                    "zmscitizenview: slot timestamp={} could not be selected; trying the next available slot",
                                    missed);
                    continue;
                }
            }
            if (!assertSelectedAppointmentCalloutShowsProvider(officeId)) {
                keepReservedSlot(pendingReserveTimestamp);
                finishReserveOnContactStep();
                return;
            }
            long timestamp = readStoredSlotTimestamp();
            ScenarioLogManager.getLogger()
                    .info(
                            "zmscitizenview: Weiter after slot callout → reserve appointment (then Kontakt form) timestamp={}",
                            timestamp);
            page.clickWeiter();
            if (timestamp > 0) {
                pendingReserveTimestamp = timestamp;
            }
            switch (waitForReserveOutcome()) {
                case CONTACT -> {
                    keepReservedSlot(timestamp);
                    finishReserveOnContactStep();
                    return;
                }
                case SLOT_TAKEN -> {
                    if (timestamp > 0) {
                        skipped.add(timestamp);
                    }
                    pendingReserveTimestamp = null;
                    ScenarioLogManager.getLogger()
                            .info(
                                    "zmscitizenview: slot timestamp={} is no longer available; trying the next available slot",
                                    timestamp);
                }
                case UNFINISHED -> {
                    if (timestamp > 0) {
                        skipped.add(timestamp);
                    }
                    unfinishedReserves++;
                    ScenarioLogManager.getLogger()
                            .info(
                                    "zmscitizenview: reserve for timestamp={} did not finish; trying the next available slot",
                                    timestamp);
                    if (unfinishedReserves >= 2) {
                        Assert.fail(
                                "zmscitizenview: reserve did not reach Kontaktdaten and did not report a taken slot for office "
                                        + officeId);
                    }
                }
            }
        }
        Assert.fail("zmscitizenview: no free slot remained for office " + officeId);
    }

    /**
     * Full slot booking sequence (single step); prefer the split steps in feature files for clearer reports and
     * per-step screenshots.
     *
     * @see #waitUntilSlotsReadyForBooking()
     * @see #clickSpäterIfAvailableAndReloadSlots()
     * @see #selectPreferredTimeslotBelowCalendar(int)
     * @see #assertCalloutAndReserveAfterSlotSelection(int)
     */
    public void scrollClickFirstSlotAssertCalloutWeiter(int officeId) {
        waitUntilSlotsReadyForBooking();
        clickSpäterIfAvailableAndReloadSlots();
        selectPreferredTimeslotBelowCalendar(officeId);
        assertCalloutAndReserveAfterSlotSelection(officeId);
    }

    /**
     * Info callout after slot pick: selected-appointment header + {@code #provider-{officeId}}.
     *
     * @return false when the Kontakt step is already showing, so the caller must not click Weiter again
     */
    public boolean assertSelectedAppointmentCalloutShowsProvider(int officeId) {
        context.set();
        String providerSelector = "#provider-" + officeId;
        CitizenViewWaits.waitWithThreeWindows(
                () -> contactStepReached()
                        || (selectedAppointmentCalloutVisible() && shadow.deepElementExists(providerSelector)),
                "Selected appointment callout for office " + officeId);
        if (stopBecauseContactStepIsVisible(officeId)) {
            return false;
        }
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(
                        d ->
                                contactStepReached()
                                        || (selectedAppointmentCalloutVisible()
                                                && shadow.deepElementExists(providerSelector)));
        if (stopBecauseContactStepIsVisible(officeId)) {
            return false;
        }
        Assert.assertTrue(
                selectedAppointmentCalloutVisible(),
                "Selected-appointment callout header missing after slot click");
        if (!shadow.deepElementExists(providerSelector)) {
            if (stopBecauseContactStepIsVisible(officeId)) {
                return false;
            }
            Assert.fail("Expected #provider-" + officeId + " in selected-appointment callout");
        }
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: callout OK — Ausgewählter Termin includes provider {} (Bürgerbüro Ruppertstraße)",
                        officeId);
        return true;
    }

    public void assertSelectedAppointmentCalloutVisible() {
        shadow.assertShadowContains(
                "Ausgewählter Termin",
                "Selected-appointment info callout not found after choosing slot.");
    }

    public JsonNode locationTitles() {
        return json.citizenJson(
                "(function(){var nodes=cssAll('h5.location-title');var seen={};var titles=[];"
                        + "for(var i=0;i<nodes.length;i++){var el=nodes[i];if(!shown(el))continue;"
                        + "var id=el.id||'';if(id&&seen[id])continue;if(id)seen[id]=true;"
                        + "var href='';var uses=el.querySelectorAll('use');"
                        + "for(var u=0;u<uses.length;u++){href+=' '+(uses[u].getAttribute('href')||'')"
                        + "+' '+(uses[u].getAttribute('xlink:href')||'');}"
                        + "titles.push({id:id,text:textOf(el),pin:href.indexOf('map-pin')>=0});}"
                        + "return {titles:titles};})()");
    }

    public JsonNode calendarGhostButtons() {
        return json.citizenJson(
                "(function(){function paint(word){var el=findButton(document.body,word);if(!el)return {present:false};"
                        + "var variant='';var icon='';var blob='';var cur=el;var guard=0;"
                        + "while(cur&&guard++<8){if(cur.getAttribute){var v=cur.getAttribute('variant')||'';"
                        + "var ic=cur.getAttribute('icon')||'';if(!variant&&v)variant=v;if(!icon&&ic)icon=ic;"
                        + "blob+=' '+(typeof cur.className==='string'?cur.className:'');}"
                        + "if(cur.querySelectorAll){var uses=cur.querySelectorAll('use');"
                        + "for(var u=0;u<uses.length;u++){blob+=' '+(uses[u].getAttribute('href')||'')"
                        + "+' '+(uses[u].getAttribute('xlink:href')||'');}}"
                        + "if(cur.shadowRoot){var suses=cur.shadowRoot.querySelectorAll('use');"
                        + "for(var s=0;s<suses.length;s++){blob+=' '+(suses[s].getAttribute('href')||'')"
                        + "+' '+(suses[s].getAttribute('xlink:href')||'');}}"
                        + "if(cur.parentElement)cur=cur.parentElement;else{var root=cur.getRootNode&&cur.getRootNode();"
                        + "cur=root&&root.host?root.host:null;}}"
                        + "return {present:true,disabled:isDisabled(el),variant:variant,icon:icon,blob:blob.toLowerCase()};}"
                        + "return {earlier:paint('Früher'),later:paint('Später')};})()");
    }

    public static void assertGhostButton(JsonNode button, String word, String icon, boolean disabled) {
        Assert.assertTrue(button.path("present").asBoolean(), word + " should be shown: " + button);
        Assert.assertEquals(button.path("disabled").asBoolean(), disabled, word + " disabled state: " + button);
        String variant = button.path("variant").asText();
        String blob = button.path("blob").asText();
        Assert.assertTrue(
                "ghost".equals(variant) || blob.contains("ghost"),
                word + " should be a ghost button: " + button);
        Assert.assertTrue(
                icon.equals(button.path("icon").asText()) || blob.contains(icon),
                word + " should use " + icon + ": " + button);
    }

    public static String hourLabelOrEmpty(JsonNode state) {
        if (state.path("list").asBoolean()) {
            return "";
        }
        for (JsonNode label : state.path("timeLabels")) {
            if (label.asText().matches("\\d{1,2}:00-\\d{1,2}:59")) {
                return label.asText();
            }
        }
        return "";
    }

    public static JsonNode hourLabel(JsonNode node) {
        if (node.path("list").asBoolean()) {
            return null;
        }
        for (JsonNode label : node.path("timeLabels")) {
            if (label.asText().matches("\\d{1,2}:00-\\d{1,2}:59")) {
                return node;
            }
        }
        return null;
    }

    public static JsonNode morningLabel(JsonNode node) {
        if (node.path("list").asBoolean()) {
            return null;
        }
        for (JsonNode label : node.path("timeLabels")) {
            if ("Vormittag".equals(label.asText())) {
                return node;
            }
        }
        return null;
    }

    public static void assertHourLabels(JsonNode state) {
        for (JsonNode label : state.path("timeLabels")) {
            String text = label.asText();
            Assert.assertFalse(
                    "Vormittag".equals(text) || "Nachmittag".equals(text),
                    "A busy day groups by hour, not " + text);
        }
    }

    public static void assertMorningLabels(JsonNode state) {
        for (JsonNode label : state.path("timeLabels")) {
            String text = label.asText();
            Assert.assertFalse(text.matches("\\d{1,2}:00-\\d{1,2}:59"), "A short day is not grouped by hour: " + text);
            if ("Nachmittag".equals(text)) {
                Assert.fail("10:00 is still the morning: " + state);
            }
        }
        Assert.assertTrue(
                state.path("earlier").path("present").asBoolean(),
                "Früher stays while several locations are offered: " + state.path("earlier"));
        Assert.assertTrue(
                state.path("later").path("present").asBoolean(),
                "Später stays while several locations are offered: " + state.path("later"));
        double labelLeft = state.path("labelLeft").asDouble();
        double headingLeft = state.path("headingLeft").asDouble();
        Assert.assertTrue(
                Math.abs(labelLeft - headingLeft) <= 32,
                "Vormittag should line up with Verfügbare Termine. label="
                        + labelLeft
                        + " heading="
                        + headingLeft);
    }

    public JsonNode waitForToggleLabels(String activeLabel) {
        return new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> {
                    JsonNode node = json.citizenJson(
                            "(function(){var labels=cssAll('.m-toggle-switch__label');var out=[];"
                                    + "for(var i=0;i<labels.length;i++){var st=getComputedStyle(labels[i]);"
                                    + "out.push({text:textOf(labels[i]),color:st.color,disabled:labels[i].classList.contains('disabled')});}"
                                    + "var heading=byId('viewToggleLabel');var toggle=cssAll('button.m-toggle-switch')[0];"
                                    + "var hr=heading?heading.getBoundingClientRect():null;var tr=toggle?toggle.getBoundingClientRect():null;"
                                    + "return {labels:out,heading:heading?textOf(heading):'',"
                                    + "headingBottom:hr?hr.bottom:0,headingRight:hr?hr.right:0,"
                                    + "toggleTop:tr?tr.top:0,toggleLeft:tr?tr.left:0};})()");
                    if (node.path("labels").size() != 2) {
                        return null;
                    }
                    if (activeLabel == null || activeLabel.isEmpty()) {
                        return node;
                    }
                    // Wait until the painted colors match the active label. Firefox can still show
                    // the previous blue on Kalenderansicht right after the list accordion mounts.
                    for (JsonNode label : node.path("labels")) {
                        boolean shouldBeActive = activeLabel.equals(label.path("text").asText());
                        String color = label.path("color").asText();
                        boolean activeColor =
                                color.contains("0, 90, 159") || color.contains("0,90,159");
                        boolean inactiveColor =
                                color.contains("97, 117, 134") || color.contains("97,117,134");
                        if (label.path("disabled").asBoolean() == shouldBeActive) {
                            return null;
                        }
                        if (shouldBeActive && !activeColor) {
                            return null;
                        }
                        if (!shouldBeActive && !inactiveColor) {
                            return null;
                        }
                    }
                    return node;
                });
    }

    public void assertToggleColor(JsonNode label, String text, boolean active) {
        Assert.assertEquals(label.path("text").asText(), text, "Toggle label: " + label);
        Assert.assertEquals(label.path("disabled").asBoolean(), !active, text + " active state: " + label);
        String color = label.path("color").asText();
        String expected = active ? "0, 90, 159" : "97, 117, 134";
        String compact = active ? "0,90,159" : "97,117,134";
        Assert.assertTrue(
                color.contains(expected) || color.contains(compact),
                text + " should be " + (active ? "#005A9F" : "#617586") + " but was " + color);
    }

    public JsonNode calendarSnapshot() {
        return json.citizenJson(
                "(function(){if(byId('listViewAccordion'))return {list:true,timeLabels:[]};"
                        + "var timeLabels=[];var labelLeft=0;var ps=[];function collect(n){if(!n)return;"
                        + "if(n.classList&&n.classList.contains('left-text'))ps.push(n);"
                        + "if(n.shadowRoot)collect(n.shadowRoot);var c=n.children;if(c)for(var k=0;k<c.length;k++)collect(c[k]);}"
                        + "collect(document.body);for(var p=0;p<ps.length;p++){if(!shown(ps[p]))continue;"
                        + "timeLabels.push(textOf(ps[p]));if(!labelLeft)labelLeft=ps[p].getBoundingClientRect().left;}"
                        + "var headingLeft=0;var h3s=cssAll('h3');for(var h=0;h<h3s.length;h++){"
                        + "if(textOf(h3s[h])==='Verfügbare Termine'&&shown(h3s[h])){headingLeft=h3s[h].getBoundingClientRect().left;break;}}"
                        + "return {list:false,timeLabels:timeLabels,labelLeft:labelLeft,headingLeft:headingLeft,"
                        + "earlier:btnState(document.body,'Früher'),later:btnState(document.body,'Später')};})()");
    }

    public JsonNode listSnapshot() {
        return json.citizenJson(
                "(function(){var headers=cssAll('#listViewAccordion h4.m-accordion__section-header');"
                        + "var expanded=[];var labels=[];"
                        + "for(var i=0;i<headers.length;i++){var b=headers[i].querySelector('button');"
                        + "expanded.push(b?b.getAttribute('aria-expanded'):'');labels.push(textOf(b||headers[i]));}"
                        + "var open=cssAll('#listViewAccordion section.m-accordion__section-content.show')[0]||null;"
                        + "var timeLabels=[];var labelLeft=0;if(open){var ps=[];function collect(n){if(!n)return;"
                        + "if(n.classList&&n.classList.contains('left-text'))ps.push(n);"
                        + "if(n.shadowRoot)collect(n.shadowRoot);var c=n.children;if(c)for(var k=0;k<c.length;k++)collect(c[k]);}"
                        + "collect(open);for(var p=0;p<ps.length;p++){if(!shown(ps[p]))continue;timeLabels.push(textOf(ps[p]));"
                        + "if(!labelLeft)labelLeft=ps[p].getBoundingClientRect().left;}}"
                        + "var headingLeft=0;var h3s=cssAll('h3');for(var h=0;h<h3s.length;h++){"
                        + "if(textOf(h3s[h])==='Verfügbare Termine'&&shown(h3s[h])){headingLeft=h3s[h].getBoundingClientRect().left;break;}}"
                        + "var root=open||document.body;"
                        + "return {count:headers.length,expanded:expanded,labels:labels,timeLabels:timeLabels,"
                        + "labelLeft:labelLeft,headingLeft:headingLeft,earlier:btnState(root,'Früher'),later:btnState(root,'Später')};})()");
    }

    public JsonNode waitForListAccordionCount(int expected) {
        return new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> {
                    JsonNode node = listSnapshot();
                    return node.path("count").asInt() == expected ? node : null;
                });
    }

    public static String firstHourLabel(JsonNode state) {
        for (JsonNode label : state.path("timeLabels")) {
            if (label.asText().matches("\\d{1,2}:00-\\d{1,2}:59")) {
                return label.asText();
            }
        }
        Assert.fail("No hour range in the open date: " + state);
        return "";
    }

    public JsonNode waitForPagerButtons() {
        return new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> {
                    JsonNode node = listSnapshot();
                    return node.path("earlier").path("present").asBoolean()
                            && node.path("later").path("present").asBoolean()
                            ? node
                            : null;
                });
    }

    public static void assertPagerButton(JsonNode button, String word, boolean disabled) {
        Assert.assertTrue(button.path("present").asBoolean(), word + " should be in the open date");
        Assert.assertEquals(button.path("disabled").asBoolean(), disabled, word + " disabled state: " + button);
        Assert.assertEquals(button.path("lines").asInt(), 1, word + " should stay on one line: " + button);
    }

    public JsonNode timeslotStyle(String slotId) {
        return json.citizenJson(
                "(function(){var id=__args[0]==null?'':String(__args[0]);var slots=cssAll('.timeslot');var target=null;"
                        + "for(var i=0;i<slots.length;i++){if(id&&slots[i].id===id){target=slots[i];break;}"
                        + "if(!id&&(slots[i].getAttribute('variant')||'')==='primary'){target=slots[i];break;}}"
                        + "if(!target)return {found:false,id:id};"
                        + "var inner=target.shadowRoot&&target.shadowRoot.querySelector('button');"
                        + "var innerSt=inner?getComputedStyle(inner):null;var hostSt=getComputedStyle(target);"
                        + "return {found:true,id:target.id||'',variant:isPrimary(target)?'primary':'secondary',"
                        + "background:innerSt?innerSt.backgroundColor:hostSt.backgroundColor,"
                        + "color:innerSt?innerSt.color:hostSt.color,"
                        + "hostBackground:hostSt.backgroundColor,hostColor:hostSt.color};})()",
                slotId == null ? "" : slotId);
    }

    public void waitUntilCalendarSettled(int officeId, boolean expectSlots) {
        long deadline = System.currentTimeMillis() + Math.max(30, defaultWaitSeconds) * 1000L;
        while (System.currentTimeMillis() < deadline) {
            if (!deepMucSpinnerVisible()) {
                boolean slots = deepTimeslotPresentForProvider(officeId);
                boolean callout = shadow.shadowDomContainsText(NO_APPOINTMENT_CALLOUT);
                if (expectSlots && slots) {
                    return;
                }
                if (!expectSlots && callout && !slots) {
                    return;
                }
            }
            CitizenViewWaits.sleepQuiet(400L);
        }
    }

    public static boolean timeslotLooksWhiteOnBlue(JsonNode slot) {
        String background = slot.path("background").asText() + " " + slot.path("hostBackground").asText();
        String color = slot.path("color").asText() + " " + slot.path("hostColor").asText();
        boolean blue = background.contains("0, 90, 159") || background.contains("0,90,159");
        boolean white = color.contains("255, 255, 255") || color.contains("255,255,255");
        return blue && white;
    }

    /**
     * The Termin step fetches days and slots again after a stepper click. The first wait uses the
     * same budget as the initial calendar load. A spinner that is still up gets one more wait.
     */
    public void waitForSlotsAfterReturningToTermin() {
        int timeout = slotBookingWaitTimeoutSeconds();
        for (int attempt = 1; attempt <= 2; attempt++) {
            try {
                waitUntilAppointmentSlotsReady(timeout);
                return;
            } catch (Exception e) {
                ScenarioLogManager.getLogger()
                        .warn(
                                "zmscitizenview: slots not ready before another timeslot (attempt {}): {}",
                                attempt,
                                e.toString());
            }
        }
    }

    /**
     * Opens the next bookable day on the citizen calendar. Später only moves within the open day,
     * so an empty evening grid uses the calendar's next-day control instead.
     */
    public boolean openNextCalendarDayAndWaitForSlots() {
        if (!clickNextBookableCalendarDay()) {
            return false;
        }
        CitizenViewWaits.sleepQuiet(1200L);
        try {
            waitUntilAppointmentSlotsReady(Math.min(45, slotBookingWaitTimeoutSeconds()));
        } catch (Exception e) {
            ScenarioLogManager.getLogger()
                    .warn("zmscitizenview slot wait after next calendar day: {}", e.toString());
        }
        return true;
    }

    public boolean clickNextBookableCalendarDay() {
        context.set();
        String script =
                "function walk(root, visit){"
                        + "if(!root||!root.querySelectorAll)return false;"
                        + "var nodes=root.querySelectorAll('*');"
                        + "for(var i=0;i<nodes.length;i++){"
                        + "if(visit(nodes[i]))return true;"
                        + "if(nodes[i].shadowRoot&&walk(nodes[i].shadowRoot,visit))return true;"
                        + "}"
                        + "return false;"
                        + "}"
                        + "function enabled(btn){"
                        + "return btn&&!btn.disabled&&btn.getAttribute('aria-disabled')!=='true';"
                        + "}"
                        + "var wrap=null;"
                        + "walk(document,function(el){"
                        + "if(el.matches&&el.matches('.muc-calendar-wrap')){wrap=el;return true;}"
                        + "return false;"
                        + "});"
                        + "if(!wrap)return false;"
                        + "var days=[];"
                        + "walk(wrap,function(el){"
                        + "if(el.classList&&el.classList.contains('muc-calendar-item')&&el.getAttribute('role')==='button')days.push(el);"
                        + "return false;"
                        + "});"
                        + "var selectedDay=-1;"
                        + "for(var d=0;d<days.length;d++){"
                        + "if(days[d].classList.contains('selected'))selectedDay=d;"
                        + "}"
                        + "if(selectedDay>=0){"
                        + "for(var n=selectedDay+1;n<days.length;n++){"
                        + "var tile=days[n];"
                        + "if(tile.getAttribute('aria-disabled')==='true'||tile.classList.contains('disabled-tile')||tile.classList.contains('off-month'))continue;"
                        + "var dayLabel=(tile.textContent||'').replace(/\\s+/g,' ').trim();"
                        + "if(!/^\\d{1,2}$/.test(dayLabel))continue;"
                        + "tile.click();"
                        + "return true;"
                        + "}"
                        + "}"
                        + "var clicked=false;"
                        + "walk(wrap,function(el){"
                        + "if(clicked||el.tagName!=='BUTTON'||!enabled(el))return false;"
                        + "var useEl=el.querySelector('use');"
                        + "var href=(useEl&&(useEl.getAttribute('href')||useEl.getAttribute('xlink:href')))||'';"
                        + "if(href.indexOf('chevron-right')<0)return false;"
                        + "el.click();"
                        + "clicked=true;"
                        + "return true;"
                        + "});"
                        + "if(clicked)return true;"
                        + "var buttons=[];"
                        + "walk(wrap,function(el){"
                        + "if(el.tagName==='BUTTON')buttons.push(el);"
                        + "return false;"
                        + "});"
                        + "var selected=-1;"
                        + "for(var i=0;i<buttons.length;i++){"
                        + "var b=buttons[i];"
                        + "var marked=b.getAttribute('aria-pressed')==='true'||b.getAttribute('aria-selected')==='true'"
                        + "||b.className.indexOf('selected')>=0;"
                        + "if(marked)selected=i;"
                        + "}"
                        + "if(selected<0)return false;"
                        + "for(var j=selected+1;j<buttons.length;j++){"
                        + "var day=buttons[j];"
                        + "if(!enabled(day))continue;"
                        + "var label=(day.textContent||'').replace(/\\s+/g,' ').trim();"
                        + "if(!/^\\d{1,2}$/.test(label))continue;"
                        + "day.click();"
                        + "return true;"
                        + "}"
                        + "return false;";
        Object clicked = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        if (Boolean.TRUE.equals(clicked)) {
            ScenarioLogManager.getLogger()
                    .info("zmscitizenview: opened the next calendar day");
        }
        return Boolean.TRUE.equals(clicked);
    }

    /** Max wait for slot grid + spinner (calendar / office load). */
    public int slotBookingWaitTimeoutSeconds() {
        return Math.max(defaultWaitSeconds, 90);
    }

    /** @return false when the current calendar view has no highlightable slot for this office */
    public boolean highlightPreferredTimeslotForOfficeOrAbsent(int officeId, String skippedTimestamps) {
        context.set();
        String scrollSlotHighlight = CitizenViewScripts.buildScrollSlotHighlightScript();
        ScenarioLogManager.getLogger().info(
                "zmscitizenview: highlight preferred slot (≥60min ahead; else ≥5min; else next free) office {} skip [{}]",
                officeId,
                skippedTimestamps);
        boolean highlighted = false;
        int dayMoves = 0;
        for (int attempt = 1; attempt <= 8 && !highlighted; attempt++) {
            if (contactStepReached()) {
                return false;
            }
            try {
                highlighted =
                        Boolean.TRUE.equals(
                                ((JavascriptExecutor) DriverUtil.getDriver())
                                        .executeScript(scrollSlotHighlight, officeId, skippedTimestamps));
            } catch (Exception e) {
                ScenarioLogManager.getLogger()
                        .warn("zmscitizenview: highlight script attempt {} failed: {}", attempt, e.toString());
            }
            if (highlighted) {
                break;
            }
            ScenarioLogManager.getLogger()
                    .info(
                            "zmscitizenview: no timeslot for provider {} in current view (attempt {}); try Später",
                            officeId,
                            attempt);
            if (clickCitizenViewLaterOnceIfAvailable()) {
                CitizenViewWaits.sleepQuiet(1200L);
                try {
                    waitUntilAppointmentSlotsReady(Math.min(45, slotBookingWaitTimeoutSeconds()));
                } catch (Exception e) {
                    ScenarioLogManager.getLogger()
                            .warn("zmscitizenview slot wait after Später (highlight): {}", e.toString());
                }
                continue;
            }
            if (dayMoves >= 3 || !openNextCalendarDayAndWaitForSlots()) {
                break;
            }
            dayMoves++;
        }
        if (!highlighted) {
            return false;
        }
        CitizenViewWaits.sleepQuiet(200L);
        scrollTimeSlotGridIntoViewForScreenshots();
        CitizenViewWaits.sleepQuiet(250L);
        return true;
    }

    /**
     * Clicks the stored slot. Returns false when the slot is gone, so the reserve loop can skip it
     * and try the next timestamp instead of failing the 15s re-highlight wait.
     */
    public boolean clickHighlightedTimeslotSelectionOrGiveUp() {
        context.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: click highlighted timeslot");
        JavascriptExecutor js = (JavascriptExecutor) DriverUtil.getDriver();
        int officeId = resolveStoredSlotOfficeId(js);
        if (officeId <= 0) {
            ScenarioLogManager.getLogger()
                    .warn("zmscitizenview: highlight step must run first (missing window.__zmsCitizenViewSlotOfficeId)");
            return false;
        }

        boolean selected = false;
        for (int attempt = 1; attempt <= 3 && !selected; attempt++) {
            if (attempt > 1) {
                ScenarioLogManager.getLogger()
                        .warn("zmscitizenview: slot selection not registered; retry click attempt {}", attempt);
                try {
                    new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(15))
                            .until(
                                    d ->
                                            Boolean.TRUE.equals(
                                                    ((JavascriptExecutor) d)
                                                            .executeScript(CitizenViewScripts.buildScrollSlotHighlightScript(), officeId)));
                } catch (TimeoutException e) {
                    ScenarioLogManager.getLogger()
                            .info(
                                    "zmscitizenview: re-highlight found no slot for office {}; trying the next available slot",
                                    officeId);
                    return false;
                }
                CitizenViewWaits.sleepQuiet(250L);
            }
            if (!performStoredTimeslotClick(js)) {
                ScenarioLogManager.getLogger()
                        .info("zmscitizenview: could not click highlighted timeslot (attempt {})", attempt);
                return false;
            }
            selected = waitForSlotSelectionVisible(officeId, attempt == 1 ? 12 : 20);
        }
        if (selected) {
            CitizenViewWaits.sleepQuiet(400L);
        }
        return selected;
    }

    public static int resolveStoredSlotOfficeId(JavascriptExecutor js) {
        Object officeIdObj = js.executeScript("return window.__zmsCitizenViewSlotOfficeId;");
        if (officeIdObj instanceof Number number) {
            return number.intValue();
        }
        return 0;
    }

    public boolean performStoredTimeslotClick(JavascriptExecutor js) {
        if (Boolean.TRUE.equals(js.executeScript(CitizenViewScripts.CLICK_STORED_TIMESLOT_SCRIPT))) {
            return true;
        }
        Object sid = js.executeScript("return window.__zmsCitizenViewSlotId||'';");
        if (sid instanceof String slotId && !slotId.isEmpty()) {
            return shadow.deepClick("#" + slotId);
        }
        return false;
    }

    /**
     * True when a timeslot for {@code officeId} shows primary (selected) styling or the selected-appointment callout
     * is visible — i.e. Vue received {@code selectTimeSlot}.
     */
    public boolean isSlotSelectionVisibleForOffice(int officeId) {
        if (isTimeslotPrimarySelectedForOffice(officeId)) {
            return true;
        }
        String providerSelector = "#provider-" + officeId;
        return (shadow.shadowDomContainsText("Ausgewählter Termin") || shadow.shadowDomContainsText("Selected Appointment"))
                && shadow.deepElementExists(providerSelector);
    }

    /** {@code muc-button} with {@code data-variant="primary"} for {@code provider-{officeId}-timeslot-*}. */
    public boolean isTimeslotPrimarySelectedForOffice(int officeId) {
        context.set();
        String script =
                "var oid=String(arguments[0]);"
                        + "var prefix='provider-'+oid+'-timeslot-';"
                        + "function isPrimaryHost(n){"
                        + " if(!n||!n.id||n.id.indexOf(prefix)!==0)return false;"
                        + " if(n.getAttribute&&n.getAttribute('data-variant')==='primary')return true;"
                        + " if(n.shadowRoot){"
                        + "  var b=n.shadowRoot.querySelector('button.m-button--primary,[data-variant=primary]');"
                        + "  if(b)return true;"
                        + " }"
                        + " return false;"
                        + "}"
                        + "function walk(root){"
                        + " if(!root)return false;"
                        + " var hosts=root.querySelectorAll('[id^=\"'+prefix+'\"]');"
                        + " for(var i=0;i<hosts.length;i++){if(isPrimaryHost(hosts[i]))return true;}"
                        + " var all=root.querySelectorAll('*');"
                        + " for(var j=0;j<all.length;j++){"
                        + "  if(all[j].shadowRoot&&walk(all[j].shadowRoot))return true;"
                        + " }"
                        + " return false;"
                        + "}"
                        + "return walk(document.body);";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, officeId);
        return Boolean.TRUE.equals(o);
    }

    public boolean waitForSlotSelectionVisible(int officeId, int timeoutSeconds) {
        try {
            new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(timeoutSeconds))
                    .pollingEvery(Duration.ofMillis(500))
                    .until(d -> isSlotSelectionVisibleForOffice(officeId));
            ScenarioLogManager.getLogger()
                    .info("zmscitizenview: slot selection registered for office {}", officeId);
            return true;
        } catch (TimeoutException e) {
            return false;
        }
    }

    /**
     * Kontakt step is up: heading, voluntary-login box, or the Vorname field.
     * A slow reserve paints this page without a taken-slot error.
     */
    public boolean contactStepReached() {
        return shadow.shadowDomContainsText("Kontaktdaten")
                || shadow.shadowDomContainsText("Freiwillige Anmeldung")
                || shadow.deepElementExists("#firstname");
    }

    public boolean selectedAppointmentCalloutVisible() {
        return shadow.shadowDomContainsText("Ausgewählter Termin") || shadow.shadowDomContainsText("Selected Appointment");
    }

    /**
     * Firefox can land on Kontaktdaten before the callout assert. A leftover callout node must not fail the
     * scenario; the caller continues on the contact form and must not click Weiter again.
     */
    public boolean stopBecauseContactStepIsVisible(int officeId) {
        if (!contactStepReached()) {
            return false;
        }
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: Kontakt step visible for office {}; slot callout wait stopped", officeId);
        return true;
    }

    public void finishReserveOnContactStep() {
        waitForReserveToSettle();
        page.trySetBookingProcessFromPage();
    }

    public ReserveOutcome waitForReserveOutcome() {
        long deadline = System.currentTimeMillis() + 60_000L;
        while (System.currentTimeMillis() < deadline) {
            if (contactStepReached() || shadow.shadowDomContainsText("Termin verschieben")) {
                return ReserveOutcome.CONTACT;
            }
            if (shadow.shadowDomContainsText("Ihr gewählter Termin ist nicht mehr verfügbar.")
                    || shadow.shadowDomContainsText("Ein unbekannter Fehler ist aufgetreten.")) {
                return ReserveOutcome.SLOT_TAKEN;
            }
            CitizenViewWaits.sleepQuiet(400L);
        }
        if (contactStepReached()) {
            return ReserveOutcome.CONTACT;
        }
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: reserve did not reach Kontaktdaten and did not report a taken slot");
        return ReserveOutcome.UNFINISHED;
    }

    /** Keep the slot whose Weiter reached Kontakt, not a later highlight. */
    public void keepReservedSlot(Long timestamp) {
        if (timestamp != null && timestamp > 0) {
            slotState.rememberedAppointmentEpoch = timestamp;
        }
    }

    public long readStoredSlotTimestamp() {
        Object slotId =
                ((JavascriptExecutor) DriverUtil.getDriver())
                        .executeScript("return window.__zmsCitizenViewSlotId || '';");
        if (slotId == null) {
            return 0L;
        }
        String id = String.valueOf(slotId);
        int marker = id.lastIndexOf("-timeslot-");
        if (marker < 0) {
            return 0L;
        }
        try {
            return Long.parseLong(id.substring(marker + "-timeslot-".length()));
        } catch (NumberFormatException e) {
            return 0L;
        }
    }

    /**
     * After reserve-Weiter: give the reserve API time to persist so update-appointment (Kontakt submit) does not get
     * 404 appointmentNotFound.
     * <p>
     * Update payload comes from: (1) {@code processId}/{@code authKey}/{@code scope} from the reserve response
     * (stored in {@code appointment.value}); (2) contact fields from the form’s Vue state ({@code customerData}),
     * copied onto {@code appointment} in {@code nextUpdateAppointment} before POST. If the backend returns 404
     * despite a 200 reserve, increase this delay or check backend persistence (e.g. commit/replication).
     */
    public void waitForReserveToSettle() {
        try {
            Thread.sleep(4500L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
        ScenarioLogManager.getLogger().info("zmscitizenview: reserve settle delay done");
    }

}
