package zms.ataf.ui.pages.citizenview.steps;

import java.nio.charset.StandardCharsets;
import java.time.Duration;
import java.time.Instant;
import java.time.ZoneId;
import java.time.ZonedDateTime;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.Base64;
import java.util.List;
import java.util.Locale;

import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;

import ataf.core.helpers.TestDataHelper;
import ataf.core.helpers.TestPropertiesHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.web.utils.DriverUtil;
import zms.ataf.helpers.AccountCheckout;
import zms.ataf.rest.dto.zmscitizenapi.ThinnedProcess;
import zms.ataf.ui.pages.citizenview.CitizenViewPage;
import zms.ataf.ui.pages.citizenview.CitizenViewPageContext;
import zms.ataf.ui.pages.citizenview.support.CitizenViewWaits;
import zms.ataf.ui.pages.citizenview.support.ShadowDom;
import zms.ataf.ui.pages.citizenview.support.SlotBookingState;
import java.util.Objects;

/** My appointments, ICS, deep links, booking-process capture for cleanup. */
public final class MyAppointmentsStep {

    public static final String LOCALSTORAGE_APPOINTMENT_KEY = "lhm-appointment-data";

    private final CitizenViewPageContext context;
    private final ShadowDom shadow;
    private final SlotBookingState slotState;
    private final CitizenViewPage page;
    private final ContactStep contact;
    private final int defaultWaitSeconds;

    public MyAppointmentsStep(
            CitizenViewPageContext context,
            ShadowDom shadow,
            SlotBookingState slotState,
            CitizenViewPage page,
            ContactStep contact,
            int defaultWaitSeconds) {
        this.context = context;
        this.shadow = shadow;
        this.slotState = slotState;
        this.page = page;
        this.contact = contact;
        this.defaultWaitSeconds = defaultWaitSeconds;
    }

    public static String ensureAbsoluteCitizenViewUrl(String url) {
        if (url == null || url.isBlank()) {
            return url;
        }
        String u = url.trim();
        if (u.length() >= 7 && u.regionMatches(true, 0, "http://", 0, 7)) {
            return u;
        }
        if (u.length() >= 8 && u.regionMatches(true, 0, "https://", 0, 8)) {
            return u;
        }
        if (u.startsWith("//")) {
            return "http:" + u;
        }
        if (u.startsWith("#")) {
            String origin = Objects.requireNonNullElse(
                System.getenv("CITIZEN_VIEW_BASE_URI"),
                "http://localhost:8082/"
            ).trim();
            if (!origin.endsWith("/")) {
                origin = origin + "/";
            }
            return origin + u;
        }
        return "http://" + u;
    }
    public ThinnedProcess syncBookingProcessFromLocalStorage() throws Exception {
        context.set();
        ThinnedProcess already = zms.ataf.rest.steps.CitizenApiSteps.getBookingProcess();
        if (hasCancelCredentials(already)) {
            ScenarioLogManager.getLogger().info("zmscitizenview: booking process already set (from reserve step), skipping localStorage read");
            return already;
        }
        captureBookingProcessForCleanup();
        already = zms.ataf.rest.steps.CitizenApiSteps.getBookingProcess();
        if (hasCancelCredentials(already)) {
            return already;
        }
        String json =
                (String)
                        ((JavascriptExecutor) DriverUtil.getDriver())
                                .executeScript(
                                        "return localStorage.getItem('" + MyAppointmentsStep.LOCALSTORAGE_APPOINTMENT_KEY + "');");
        if (json == null || json.isBlank()) {
            captureBookingProcessForCleanup();
            ThinnedProcess captured = zms.ataf.rest.steps.CitizenApiSteps.getBookingProcess();
            if (captured != null && captured.getProcessId() != null) {
                return captured;
            }
            ScenarioLogManager.getLogger().info("zmscitizenview: localStorage lhm-appointment-data not available; ensure continueFromPreconfirmStep captured process from confirm link on page");
            return null;
        }
        ThinnedProcess p = parseAndSetBookingProcessFromJson(json);
        Assert.assertNotNull(p, "appointment.processId or authKey missing in localStorage");
        return p;
    }
    public void trySyncBookingProcessFromLocalStorageOnce() {
        context.set();
        if (hasCancelCredentials(zms.ataf.rest.steps.CitizenApiSteps.getBookingProcess())) {
            return;
        }
        if (trySetBookingProcessFromLocalStorage()) {
            return;
        }
        if (trySetBookingProcessFromVueAppointment()) {
            return;
        }
        trySetBookingProcessFromConfirmLinkOnPage();
    }
    private static boolean hasCancelCredentials(ThinnedProcess process) {
        return process != null
                && process.getProcessId() != null
                && process.getAuthKey() != null
                && !process.getAuthKey().isBlank();
    }
    private boolean trySetBookingProcessFromLocalStorage() {
        context.set();
        String json =
                (String)
                        ((JavascriptExecutor) DriverUtil.getDriver())
                                .executeScript(
                                        "return localStorage.getItem('" + MyAppointmentsStep.LOCALSTORAGE_APPOINTMENT_KEY + "');");
        if (json == null || json.isBlank()) {
            return false;
        }
        try {
            ThinnedProcess p = parseAndSetBookingProcessFromJson(json);
            if (p != null) {
                ScenarioLogManager.getLogger().info("zmscitizenview: captured booking process from localStorage after activation callout (processId={})", p.getProcessId());
                return true;
            }
        } catch (Exception e) {
            ScenarioLogManager.getLogger().debug("zmscitizenview: could not parse localStorage after activation callout", e);
        }
        return false;
    }
    private void trySetBookingProcessFromConfirmLinkOnPage() {
        context.set();
        String script =
                "var out=null;function walk(root){if(!root)return;if(root.shadowRoot&&walk(root.shadowRoot))return true;"
                        + "var as=root.querySelectorAll('a[href]');for(var i=0;i<as.length;i++){var h=as[i].getAttribute('href')||'';var idx=h.indexOf('appointment/confirm/');if(idx>=0){var rest=h.substring(idx+'appointment/confirm/'.length);var end=rest.indexOf('?');if(end>=0)rest=rest.substring(0,end);out=rest;return true;}}"
                        + "var c=root.children;for(var j=0;j<c.length;j++)if(walk(c[j]))return true;return false;}"
                        + "walk(document.body);return out;";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        if (!(raw instanceof String) || ((String) raw).isBlank()) {
            return;
        }
        String b64 = (String) raw;
        try {
            String decoded = new String(Base64.getDecoder().decode(b64), StandardCharsets.UTF_8);
            JsonNode node = new ObjectMapper().readTree(decoded);
            JsonNode idNode = node.path("id");
            JsonNode keyNode = node.path("authKey");
            if (idNode.isMissingNode() || keyNode.isMissingNode() || idNode.isNull() || keyNode.isNull()) {
                return;
            }
            int processId = idNode.asInt();
            String authKey = keyNode.asText();
            ThinnedProcess p = new ThinnedProcess();
            p.setProcessId(processId);
            p.setAuthKey(authKey);
            zms.ataf.rest.steps.CitizenApiSteps.setBookingProcess(p);
            ScenarioLogManager.getLogger().info("zmscitizenview: captured booking process from confirm link on activation callout (processId={})", processId);
        } catch (Exception e) {
            ScenarioLogManager.getLogger().debug("zmscitizenview: could not parse confirm link from page", e);
        }
    }
    private ThinnedProcess parseAndSetBookingProcessFromJson(String json) throws Exception {
        ObjectMapper mapper = new ObjectMapper();
        JsonNode root = mapper.readTree(json);
        JsonNode appointment = root.path("appointment");
        Integer processId = null;
        if (appointment.has("processId") && !appointment.get("processId").isNull()) {
            processId = appointment.get("processId").asInt();
        }
        String authKey = appointment.path("authKey").asText(null);
        if (processId == null || authKey == null) {
            return null;
        }
        ThinnedProcess p = new ThinnedProcess();
        p.setProcessId(processId);
        p.setAuthKey(authKey);
        zms.ataf.rest.steps.CitizenApiSteps.setBookingProcess(p);
        return p;
    }
    public void openConfirmationDeepLinkInBrowser() {
        context.set();
        String url = resolveConfirmationDeepLinkUrl();
        ScenarioLogManager.getLogger().info("zmscitizenview: navigating to confirmation URL: {}", url);
        try {
            DriverUtil.getDriver().navigate().to(url);
            // Do not refresh. Same-tab hash routing now handles confirm links (ZMSKVR-1121).
            // navigate()+refresh() can confirm twice and leave activation-expired UI instead of success.
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn("Navigate to confirm URL", e);
        }
        try {
            Thread.sleep(10000L);
        } catch (InterruptedException ie) {
            Thread.currentThread().interrupt();
        }
    }
    public void reopenConfirmationDeepLinkInBrowser() {
        context.set();
        String confirmUrl = resolveConfirmationDeepLinkUrl();
        String base = confirmUrl;
        int hashIdx = base.indexOf('#');
        if (hashIdx >= 0) {
            base = base.substring(0, hashIdx);
        }
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: reopening confirmation deep link (leave hash, then confirm again)");
        try {
            DriverUtil.getDriver().navigate().to(base + "#/");
            Thread.sleep(1000L);
            DriverUtil.getDriver().navigate().to(confirmUrl);
        } catch (InterruptedException ie) {
            Thread.currentThread().interrupt();
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn("Reopen confirm URL", e);
        }
        try {
            Thread.sleep(10000L);
        } catch (InterruptedException ie) {
            Thread.currentThread().interrupt();
        }
    }
    private String resolveConfirmationDeepLinkUrl() {
        String url = zms.ataf.rest.steps.CitizenApiSteps.getBookingConfirmUrl();
        if (url != null && !url.isBlank()) {
            ScenarioLogManager.getLogger().info("zmscitizenview: opening confirmation deep link (URL from mail body)");
        } else {
            String processId = zms.ataf.rest.steps.CitizenApiSteps.getBookingConfirmProcessId();
            String authKey = zms.ataf.rest.steps.CitizenApiSteps.getBookingConfirmAuthKey();
            boolean fromMail = processId != null && authKey != null;
            if (!fromMail) {
                ThinnedProcess p = zms.ataf.rest.steps.CitizenApiSteps.getBookingProcess();
                Assert.assertNotNull(p, "No booking process; sync localStorage and fetch preconfirmation mail first");
                processId = String.valueOf(p.getProcessId());
                authKey = p.getAuthKey();
            }
            ScenarioLogManager.getLogger().info("zmscitizenview: opening confirmation deep link (credentials from {})", fromMail ? "GET /mails/" : "localStorage");
            String payload =
                    "{\"id\":"
                            + processId
                            + ",\"authKey\":"
                            + ShadowDom.mapperQuote(authKey)
                            + "}";
            String b64 = Base64.getEncoder().encodeToString(payload.getBytes(StandardCharsets.UTF_8));
            String base = context.lastCitizenViewUrl != null ? context.lastCitizenViewUrl : "";
            int hashIdx = base.indexOf('#');
            if (hashIdx >= 0) {
                base = base.substring(0, hashIdx);
            }
            url = base + "#/appointment/confirm/" + b64;
        }
        return ensureAbsoluteCitizenViewUrl(url);
    }
    public void openAppointmentViewDeepLinkInBrowser() {
        context.openCitizenViewIfNotAlreadyOpen();
        String url = zms.ataf.rest.steps.CitizenApiSteps.getBookingAppointmentUrl();
        if (url == null || url.isBlank()) {
            ScenarioLogManager.getLogger().warn("zmscitizenview: no appointment view URL set; fetch the confirmation mail (second mail) first.");
        }
        Assert.assertNotNull(url, "No appointment view URL; fetch the confirmation mail first.");
        url = ensureAbsoluteCitizenViewUrl(url);
        ScenarioLogManager.getLogger().info("zmscitizenview: navigating to appointment view URL (from second email): {}", url);
        try {
            DriverUtil.getDriver().navigate().to(url);
            // Do not refresh — appointmentHash is applied via hashchange (ZMSKVR-1121).
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn("Navigate to appointment view URL", e);
        }
        waitForAppointmentDetailShellAfterNavigation();
    }
    private void waitForAppointmentDetailShellAfterNavigation() {
        context.set();
        try {
            new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                    .until(d -> shadow.deepVisibleCssExists(".m-contact") || shadow.deepVisibleCssExists("#timeTitleElement"));
            ScenarioLogManager.getLogger()
                    .info("zmscitizenview: visible appointment shell (.m-contact or #timeTitleElement)");
        } catch (TimeoutException e) {
            ScenarioLogManager.getLogger()
                    .warn(
                            "zmscitizenview: visible appointment shell not found after {}s; provider assertion will retry",
                            defaultWaitSeconds);
        }
    }
    public void captureBookingProcessForCleanup() {
        context.set();
        trySetBookingProcessFromPage();
    }
    private boolean trySetBookingProcessFromCapturedApiResponse() {
        context.set();
        Object raw =
                ((JavascriptExecutor) DriverUtil.getDriver())
                        .executeScript("return window.__zmsCapturedBooking || null;");
        if (!(raw instanceof String captured) || !captured.contains("|")) {
            return false;
        }
        int split = captured.indexOf('|');
        String idText = captured.substring(0, split);
        String authKey = captured.substring(split + 1);
        try {
            int processId = Integer.parseInt(idText);
            if (processId <= 0 || authKey.isBlank()) {
                return false;
            }
            ThinnedProcess p = new ThinnedProcess();
            p.setProcessId(processId);
            p.setAuthKey(authKey);
            zms.ataf.rest.steps.CitizenApiSteps.setBookingProcess(p);
            ScenarioLogManager.getLogger()
                    .info("zmscitizenview: captured booking process from appointment response (processId={})", processId);
            return true;
        } catch (NumberFormatException e) {
            ScenarioLogManager.getLogger().debug("zmscitizenview: captured appointment process id was not numeric", e);
            return false;
        }
    }
    private boolean trySetBookingProcessFromVueAppointment() {
        context.set();
        String script =
                "function creds(state){"
                        + " if(!state)return null;"
                        + " var raw=state.appointment;"
                        + " var v=raw&&raw.__v_isRef?raw.value:raw;"
                        + " if(v&&v.processId&&v.authKey)return {processId:v.processId,authKey:String(v.authKey)};"
                        + " return null;"
                        + "}"
                        + "function walkInst(inst,depth,seen){"
                        + " if(!inst||depth>80||seen.has(inst))return null;"
                        + " seen.add(inst);"
                        + " var hit=creds(inst.setupState)||creds(inst.exposed);"
                        + " if(hit)return hit;"
                        + " return inst.subTree?walkNode(inst.subTree,depth+1,seen):null;"
                        + "}"
                        + "function walkNode(node,depth,seen){"
                        + " if(!node||depth>80)return null;"
                        + " if(node.component){var a=walkInst(node.component,depth+1,seen);if(a)return a;}"
                        + " var kids=node.children;"
                        + " if(kids&&kids.length)for(var k=0;k<kids.length;k++){"
                        + "  var b=walkNode(kids[k],depth+1,seen);if(b)return b;"
                        + " }"
                        + " return null;"
                        + "}"
                        + "function walkDom(root,seen){"
                        + " if(!root||!root.querySelectorAll)return null;"
                        + " var nodes=root.querySelectorAll('*');"
                        + " for(var i=0;i<nodes.length;i++){"
                        + "  var el=nodes[i];"
                        + "  if(el._instance){var hit=walkInst(el._instance,0,seen);if(hit)return hit;}"
                        + "  if(el.shadowRoot){var inner=walkDom(el.shadowRoot,seen);if(inner)return inner;}"
                        + " }"
                        + " return null;"
                        + "}"
                        + "var seen=new Set();"
                        + "return walkDom(document.body,seen)||walkDom(document.documentElement,seen);";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        if (!(raw instanceof java.util.Map<?, ?> map)) {
            return false;
        }
        Object idRaw = map.get("processId");
        Object keyRaw = map.get("authKey");
        if (idRaw == null || keyRaw == null) {
            return false;
        }
        try {
            int processId = idRaw instanceof Number number
                    ? number.intValue()
                    : Integer.parseInt(String.valueOf(idRaw));
            String authKey = String.valueOf(keyRaw);
            if (processId <= 0 || authKey.isBlank()) {
                return false;
            }
            ThinnedProcess p = new ThinnedProcess();
            p.setProcessId(processId);
            p.setAuthKey(authKey);
            zms.ataf.rest.steps.CitizenApiSteps.setBookingProcess(p);
            ScenarioLogManager.getLogger()
                    .info("zmscitizenview: captured booking process from Vue appointment (processId={})", processId);
            return true;
        } catch (NumberFormatException e) {
            ScenarioLogManager.getLogger().debug("zmscitizenview: Vue appointment process id was not numeric", e);
            return false;
        }
    }
    private void trySetBookingProcessIdFromDom() {
        context.set();
        String script =
                "function walk(root){if(!root)return null;"
                        + "if(root.id){var m=String(root.id).match(/^process-(\\d+)-/);if(m)return m[1];}"
                        + "if(root.shadowRoot){var s=walk(root.shadowRoot);if(s)return s;}"
                        + "var c=root.children;if(c)for(var i=0;i<c.length;i++){var f=walk(c[i]);if(f)return f;}"
                        + "return null;}"
                        + "return walk(document.body);";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        if (raw == null) {
            return;
        }
        try {
            int processId = Integer.parseInt(String.valueOf(raw));
            if (processId <= 0) {
                return;
            }
            ThinnedProcess existing = zms.ataf.rest.steps.CitizenApiSteps.getBookingProcess();
            if (existing != null && processId == (existing.getProcessId() == null ? -1 : existing.getProcessId())) {
                return;
            }
            ThinnedProcess p = new ThinnedProcess();
            p.setProcessId(processId);
            zms.ataf.rest.steps.CitizenApiSteps.setBookingProcess(p);
            ScenarioLogManager.getLogger()
                    .info("zmscitizenview: captured booking processId={} from summary DOM", processId);
        } catch (NumberFormatException e) {
            ScenarioLogManager.getLogger().debug("zmscitizenview: summary process id was not numeric", e);
        }
    }
    private boolean trySetBookingProcessFromSessionAuthHash() {
        context.set();
        Object raw =
                ((JavascriptExecutor) DriverUtil.getDriver())
                        .executeScript("return sessionStorage.getItem('lhm-appointment-auth-hash');");
        return raw instanceof String && setBookingProcessFromAppointmentHash((String) raw);
    }
    private boolean trySetBookingProcessFromCurrentReservedHash() {
        context.set();
        String current = DriverUtil.getDriver().getCurrentUrl();
        if (current == null) {
            return false;
        }
        int idx = current.indexOf("#/appointment/");
        if (idx < 0 || current.contains("#/appointment/confirm/")) {
            return false;
        }
        String rest = current.substring(idx + "#/appointment/".length());
        int end = rest.indexOf('?');
        if (end >= 0) {
            rest = rest.substring(0, end);
        }
        return setBookingProcessFromAppointmentHash(rest);
    }
    private boolean setBookingProcessFromAppointmentHash(String hash) {
        if (hash == null || hash.isBlank()) {
            return false;
        }
        String b64 = hash.trim();
        int padding = (4 - (b64.length() % 4)) % 4;
        if (padding > 0) {
            b64 = b64 + "=".repeat(padding);
        }
        try {
            String decoded = new String(Base64.getDecoder().decode(b64), StandardCharsets.UTF_8);
            JsonNode node = new ObjectMapper().readTree(decoded);
            JsonNode idNode = node.path("id");
            JsonNode keyNode = node.path("authKey");
            if (idNode.isMissingNode() || keyNode.isMissingNode() || idNode.isNull() || keyNode.isNull()) {
                return false;
            }
            ThinnedProcess p = new ThinnedProcess();
            p.setProcessId(idNode.asInt());
            p.setAuthKey(keyNode.asText());
            zms.ataf.rest.steps.CitizenApiSteps.setBookingProcess(p);
            ScenarioLogManager.getLogger()
                    .info("zmscitizenview: captured booking process from appointment hash (processId={})", p.getProcessId());
            return true;
        } catch (Exception e) {
            ScenarioLogManager.getLogger().debug("zmscitizenview: could not parse appointment hash", e);
            return false;
        }
    }
    private static final ZoneId BERLIN = ZoneId.of("Europe/Berlin");
    private static final DateTimeFormatter TEASER_DATE_TIME =
            DateTimeFormatter.ofPattern("EEEE, dd.MM.uuuu, HH:mm", Locale.GERMAN);
    private static final DateTimeFormatter ICS_DATE_TIME = DateTimeFormatter.ofPattern("yyyyMMdd'T'HHmmss");
    private static final String ICS_DOWNLOAD_LABEL = "Termin herunterladen (ics)";
    private String capturedIcs;
    public void rememberSelectedAppointmentTime() {
        context.set();
        if (slotState.rememberedAppointmentEpoch == null || slotState.rememberedAppointmentEpoch <= 0) {
            long timestamp = page.readStoredSlotTimestamp();
            Assert.assertTrue(timestamp > 0, "Selected timeslot id has no timestamp.");
            slotState.rememberedAppointmentEpoch = timestamp;
        }
        TestDataHelper.setTestData(
                "citizenview_selected_slot_timestamp",
                String.valueOf(slotState.rememberedAppointmentEpoch));
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: remembered appointment time {}", slotState.rememberedAppointmentEpoch);
    }
    public void openMyAppointments() {
        context.set();
        // Always cache-bust: Firefox/Edge treat navigate() to the same overview URL as a no-op,
        // and cancel can leave the DBS session showing only Anmelden on a blank overview.
        String overview = myAppointmentsOverviewUrl() + "?r=" + System.currentTimeMillis();
        ScenarioLogManager.getLogger().info("zmscitizenview: open Meine Termine {}", overview);
        navigateToMyAppointments(overview);
        if (!browserIsOnMyAppointments()) {
            ScenarioLogManager.getLogger()
                    .warn(
                            "zmscitizenview: Meine Termine stayed on {}; opening it again",
                            DriverUtil.getDriver().getCurrentUrl());
            navigateToMyAppointments(overview);
        }
        waitUntilNeueTerminVisible();
    }
    public void assertMyAppointmentsLists(String... serviceNames) {
        context.set();
        waitUntilNeueTerminVisible();
        for (String serviceName : serviceNames) {
            waitForTeaserText(serviceName);
            Assert.assertEquals(
                    countTeasers(serviceName),
                    1,
                    "Meine Termine should list \"" + serviceName + "\" once.");
        }
    }
    public void assertMyAppointmentsDoesNotList(String serviceName) {
        context.set();
        waitUntilNeueTerminVisible();
        Assert.assertTrue(
                myAppointmentsFinishedLoading(),
                "Meine Termine did not finish loading.");
        Assert.assertEquals(
                countTeasers(serviceName),
                0,
                "Meine Termine still lists \"" + serviceName + "\".");
    }
    private void waitUntilNeueTerminVisible() {
        String loadError = "Ihre Termine können zur Zeit nicht geladen werden.";
        for (int attempt = 1; attempt <= 5; attempt++) {
            // Full-page hop from booking: dbs-login may show Mein Bereich from localStorage
            // while Vue still waits for authorization-event. Reloading that window kills the
            // session republish and leaves a blank overview (no Neuer Termin, no Anmelden).
            long deadline = System.currentTimeMillis() + 45_000L;
            while (System.currentTimeMillis() < deadline) {
                if (myAppointmentsFinishedLoading()) {
                    return;
                }
                if (myAppointmentsShowsLoggedOut()) {
                    ScenarioLogManager.getLogger()
                            .warn(
                                    "zmscitizenview: Meine Termine shows Anmelden (attempt {}/5); logging in again",
                                    attempt);
                    reloginForMyAppointments();
                    break;
                }
                if (shadow.shadowDomContainsText(loadError)) {
                    ScenarioLogManager.getLogger()
                            .warn(
                                    "zmscitizenview: Meine Termine appointment load error (attempt {}/5)",
                                    attempt);
                    break;
                }
                if (myAppointmentsAwaitingAuthOrSkeleton()) {
                    requestMyAppointmentsAuthReplay();
                }
                try {
                    Thread.sleep(250L);
                } catch (InterruptedException e) {
                    Thread.currentThread().interrupt();
                    return;
                }
            }
            if (myAppointmentsFinishedLoading()) {
                return;
            }
            if (attempt == 5) {
                break;
            }
            ScenarioLogManager.getLogger()
                    .warn(
                            "zmscitizenview: Meine Termine has no Neuer Termin (attempt {}/5)",
                            attempt);
            reloadMyAppointments();
        }
        Assert.assertTrue(
                myAppointmentsFinishedLoading(),
                "Meine Termine did not show Neuer Termin after opening the overview.");
    }
    private boolean myAppointmentsAwaitingAuthOrSkeleton() {
        if (!browserIsOnMyAppointments()) {
            return false;
        }
        if (shadow.deepElementExists(".skeleton-loader")) {
            return true;
        }
        return shadow.shadowDomContainsText("Mein Bereich")
                && !shadow.shadowDomContainsText("Neuer Termin")
                && !shadow.shadowDomContainsText("Kommende Termine")
                && !shadow.shadowDomContainsText("Anmelden");
    }
    private void requestMyAppointmentsAuthReplay() {
        try {
            ((JavascriptExecutor) DriverUtil.getDriver())
                    .executeScript(
                            "document.dispatchEvent(new CustomEvent('authorization-event-subscribe'));");
        } catch (Exception ignored) {
            // Best-effort; wait loop continues.
        }
    }
    private boolean myAppointmentsFinishedLoading() {
        if (shadow.shadowDomContainsText("Neuer Termin")) {
            return true;
        }
        // Empty overview still shows Kommende Termine (0) once the skeleton is gone.
        return shadow.shadowDomContainsText("Kommende Termine (0)") && !shadow.deepElementExists(".skeleton-loader");
    }
    private boolean myAppointmentsShowsLoggedOut() {
        return browserIsOnMyAppointments()
                && shadow.shadowDomContainsText("Anmelden")
                && !shadow.shadowDomContainsText("Mein Bereich")
                && !shadow.shadowDomContainsText("Neuer Termin")
                && !shadow.shadowDomContainsText("Kommende Termine");
    }
    private void reloginForMyAppointments() {
        try {
            if (!contact.clickInAppBuergerLoginAnmelden()) {
                shadow.waitForAndClickButtonContaining("Anmelden", defaultWaitSeconds);
            }
            WebDriverWait wait = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds));
            wait.until(d -> shadow.shadowDomContainsText("Mein Bereich")
                    || shadow.shadowDomContainsText("Neuer Termin")
                    || !d.findElements(By.id("username")).isEmpty());
            if (!DriverUtil.getDriver().findElements(By.id("username")).isEmpty()) {
                String username =
                        TestPropertiesHelper.getPropertyAsString("citizenUserName", true, "citizen");
                String password =
                        TestPropertiesHelper.getPropertyAsString("citizenUserPassword", true, "vorschau");
                contact.completeKeycloakLoginForm(AccountCheckout.assignCitizenLogin(username), password);
            }
            CitizenViewWaits.waitWithThreeWindows(
                    () -> shadow.shadowDomContainsText("Mein Bereich")
                            || shadow.shadowDomContainsText("Neuer Termin")
                            || shadow.shadowDomContainsText("Kommende Termine"),
                    "Meine Termine after re-login");
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn("zmscitizenview: Meine Termine re-login failed", e);
        }
        if (!browserIsOnMyAppointments() || !myAppointmentsFinishedLoading()) {
            reloadMyAppointments();
        }
    }
    private String myAppointmentsOverviewUrl() {
        String current = DriverUtil.getDriver().getCurrentUrl();
        Assert.assertTrue(current != null && !current.isBlank(), "Citizen view URL is missing.");
        int hash = current.indexOf('#');
        String withoutHash = hash >= 0 ? current.substring(0, hash) : current;
        // Strip a previous cache-bust query so we always rebuild from the path.
        int query = withoutHash.indexOf('?');
        if (query >= 0) {
            withoutHash = withoutHash.substring(0, query);
        }
        int slash = withoutHash.lastIndexOf('/');
        return withoutHash.substring(0, slash + 1) + "appointment-overview.html";
    }
    private boolean browserIsOnMyAppointments() {
        String current = DriverUtil.getDriver().getCurrentUrl();
        return current != null && current.contains("appointment-overview");
    }
    private void navigateToMyAppointments(String overview) {
        try {
            DriverUtil.getDriver().navigate().to(overview);
        } catch (TimeoutException e) {
            ScenarioLogManager.getLogger().warn("Meine Termine navigation timed out, continuing.", e);
        }
    }
    private void reloadMyAppointments() {
        String overview = myAppointmentsOverviewUrl() + "?r=" + System.currentTimeMillis();
        ScenarioLogManager.getLogger()
                .warn("zmscitizenview: opening Meine Termine again {}", overview);
        navigateToMyAppointments(overview);
    }
    public void rememberMyAppointmentsAppointment(String serviceName) {
        context.set();
        String number = appointmentNumberOnMyAppointments(serviceName);
        Assert.assertFalse(number.isBlank(), "Meine Termine has no number for \"" + serviceName + "\".");
        TestDataHelper.setTestData(myAppointmentsNumberKey(serviceName), number);
    }
    public void assertMyAppointmentsAppointmentReplaced(String serviceName) {
        context.set();
        String previous = TestDataHelper.getTestData(myAppointmentsNumberKey(serviceName));
        String current = appointmentNumberOnMyAppointments(serviceName);
        Assert.assertFalse(current.isBlank(), "Meine Termine has no number for \"" + serviceName + "\".");
        Assert.assertNotEquals(
                current,
                previous,
                "Meine Termine still shows the original appointment for \"" + serviceName + "\".");
    }
    public void assertMyAppointmentsAppointmentUnchanged(String serviceName) {
        context.set();
        String previous = TestDataHelper.getTestData(myAppointmentsNumberKey(serviceName));
        String current = appointmentNumberOnMyAppointments(serviceName);
        Assert.assertEquals(
                current,
                previous,
                "Meine Termine changed the appointment for \"" + serviceName + "\".");
    }
    private String appointmentNumberOnMyAppointments(String serviceName) {
        String script =
                "var name=arguments[0];"
                        + "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=textOf(n.shadowRoot);var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=textOf(c[i]);return s;}"
                        + "function walk(n,fn){if(!n)return null;if(n.nodeType===1){var hit=fn(n);if(hit)return hit;}"
                        + "if(n.shadowRoot){var inner=walk(n.shadowRoot,fn);if(inner)return inner;}"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++){var next=walk(c[i],fn);if(next)return next;}return null;}"
                        + "return walk(document.body,function(el){"
                        + "if(!el.classList||!el.classList.contains('card'))return null;"
                        + "var text=textOf(el).replace(/\\s+/g,' ').trim();"
                        + "if(text.indexOf('1x '+name)<0)return null;"
                        + "var match=text.match(/Terminnummer:\\s*(\\S+)/);"
                        + "return match?match[1]:'';});";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, serviceName);
        return raw == null ? "" : raw.toString();
    }
    private static String myAppointmentsNumberKey(String serviceName) {
        return "my_appointments_number_" + serviceName;
    }
    private int countTeasers(String serviceName) {
        String script =
                "var name=arguments[0];"
                        + "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=textOf(n.shadowRoot);var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=textOf(c[i]);return s;}"
                        + "function walk(n,fn){if(!n)return;if(n.nodeType===1)fn(n);if(n.shadowRoot)walk(n.shadowRoot,fn);"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)walk(c[i],fn);}"
                        + "var n=0;walk(document.body,function(el){"
                        + "if(!el.classList||!el.classList.contains('card'))return;"
                        + "if(textOf(el).replace(/\\s+/g,' ').indexOf('1x '+name)>=0)n++;});"
                        + "return n;";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, serviceName);
        if (raw instanceof Number number) {
            return number.intValue();
        }
        return 0;
    }
    public void assertMyAppointmentsTeaser(String serviceName, String typeLabel, String locationText) {
        context.set();
        Assert.assertNotNull(slotState.rememberedAppointmentEpoch, "Selected appointment time was not remembered.");
        ZonedDateTime when = Instant.ofEpochSecond(slotState.rememberedAppointmentEpoch).atZone(BERLIN);
        String dateTime = TEASER_DATE_TIME.format(when);
        String monthStem = when.format(DateTimeFormatter.ofPattern("MMM", Locale.GERMAN))
                .replace(".", "")
                .substring(0, 3)
                .toUpperCase(Locale.GERMAN);
        String day = Integer.toString(when.getDayOfMonth());
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: assert teaser {} type {} place {} at {}",
                        serviceName,
                        typeLabel,
                        locationText,
                        dateTime);
        String card = waitForTeaserText(serviceName);
        Assert.assertTrue(card.contains(typeLabel), "Teaser is missing type \"" + typeLabel + "\". Text: " + card);
        Assert.assertTrue(
                card.contains("1x " + serviceName),
                "Teaser title is missing \"1x " + serviceName + "\". Text: " + card);
        Assert.assertTrue(
                card.contains(locationText),
                "Teaser place is missing \"" + locationText + "\". Text: " + card);
        Assert.assertTrue(card.contains(dateTime), "Teaser time is missing \"" + dateTime + "\". Text: " + card);
        Assert.assertTrue(card.contains("Uhr"), "Teaser time is missing \"Uhr\". Text: " + card);
        Assert.assertTrue(
                card.toUpperCase(Locale.GERMAN).contains(day) && card.toUpperCase(Locale.GERMAN).contains(monthStem),
                "Teaser calendar leaf is missing " + day + " " + monthStem + ". Text: " + card);
    }
    public void openMyAppointmentsTeaser(String serviceName) {
        context.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: open teaser {}", serviceName);
        // Match the teaser title ("1x …"), same as countTeasers / assertMyAppointmentsTeaser.
        String needle = "1x " + serviceName;
        // Firefox often accepts a muc-card click without navigating. Prefer the card href.
        String findHref =
                "var name=arguments[0];"
                        + "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=textOf(n.shadowRoot);var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=textOf(c[i]);return s;}"
                        + "function walk(n,fn){if(!n)return false;if(fn(n))return true;if(n.shadowRoot&&walk(n.shadowRoot,fn))return true;"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i],fn))return true;return false;}"
                        + "var card=null;"
                        + "walk(document.body,function(n){"
                        + "var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag!=='MUC-CARD'&&tag!=='A')return false;"
                        + "if(textOf(n).replace(/\\s+/g,' ').indexOf(name)<0)return false;"
                        + "card=n;return true;});"
                        + "if(!card)return '';"
                        + "var href=(card.href&&String(card.href))||(card.getAttribute&&card.getAttribute('href'))||'';"
                        + "if(!href&&card.shadowRoot){var a=card.shadowRoot.querySelector('a[href]');"
                        + "if(a)href=a.href||a.getAttribute('href')||'';}"
                        + "if(!href&&(card.tagName||'').toUpperCase()==='A')href=card.href||card.getAttribute('href')||'';"
                        + "return href||'';";
        String clickCard =
                "var name=arguments[0];"
                        + "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=textOf(n.shadowRoot);var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=textOf(c[i]);return s;}"
                        + "function walk(n,fn){if(!n)return false;if(fn(n))return true;if(n.shadowRoot&&walk(n.shadowRoot,fn))return true;"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i],fn))return true;return false;}"
                        + "var card=null;"
                        + "walk(document.body,function(n){"
                        + "var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag!=='MUC-CARD'&&tag!=='A')return false;"
                        + "if(textOf(n).replace(/\\s+/g,' ').indexOf(name)<0)return false;"
                        + "card=n;return true;});"
                        + "if(!card)return false;"
                        + "var hit=card;"
                        + "if(card.shadowRoot){var a=card.shadowRoot.querySelector('a[href]');if(a)hit=a;}"
                        + "hit.scrollIntoView({block:'center'});hit.click();return true;";
        // Detail can take well over 5s under a busy chrome shard; a short settle + immediate
        // re-navigate reloads the page before Termin absagen appears.
        long deadline = System.currentTimeMillis() + Math.max(90, defaultWaitSeconds) * 1000L;
        boolean opened = false;
        while (System.currentTimeMillis() < deadline) {
            if (shadow.shadowDomContainsText("Termin absagen")) {
                opened = true;
                break;
            }
            Object hrefRaw =
                    ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(findHref, needle);
            String href = hrefRaw == null ? "" : String.valueOf(hrefRaw).trim();
            if (href.isEmpty()) {
                if (myAppointmentsShowsLoggedOut()) {
                    ScenarioLogManager.getLogger()
                            .warn(
                                    "zmscitizenview: Meine Termine shows Anmelden before opening teaser; logging in again");
                    reloginForMyAppointments();
                } else if (!browserIsOnMyAppointments() || !myAppointmentsFinishedLoading()) {
                    ScenarioLogManager.getLogger()
                            .warn(
                                    "zmscitizenview: returning to Meine Termine before opening teaser {}",
                                    serviceName);
                    reloadMyAppointments();
                    waitUntilNeueTerminVisible();
                } else {
                    waitForTeaserText(serviceName);
                }
                hrefRaw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(findHref, needle);
                href = hrefRaw == null ? "" : String.valueOf(hrefRaw).trim();
            }
            if (!href.isEmpty()) {
                try {
                    if (href.startsWith("/")) {
                        String current = DriverUtil.getDriver().getCurrentUrl();
                        int slash = current.indexOf('/', current.indexOf("://") + 3);
                        href = (slash > 0 ? current.substring(0, slash) : current) + href;
                    }
                    DriverUtil.getDriver().navigate().to(href);
                } catch (TimeoutException e) {
                    ScenarioLogManager.getLogger().warn("Meine Termine teaser navigation timed out", e);
                }
            } else {
                ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(clickCard, needle);
            }
            CitizenViewWaits.waitWithThreeWindows(
                    () -> shadow.shadowDomContainsText("Termin absagen"),
                    "Appointment detail after opening teaser " + serviceName);
            if (shadow.shadowDomContainsText("Termin absagen")) {
                opened = true;
                break;
            }
            ScenarioLogManager.getLogger()
                    .warn(
                            "zmscitizenview: teaser {} did not open detail; trying again",
                            serviceName);
            if (!browserIsOnMyAppointments()) {
                reloadMyAppointments();
                waitUntilNeueTerminVisible();
            }
        }
        Assert.assertTrue(
                opened || shadow.shadowDomContainsText("Termin absagen"),
                "Appointment detail did not show Termin absagen after opening \"" + serviceName + "\".");
    }
    private String waitForTeaserText(String serviceName) {
        long deadline = System.currentTimeMillis() + Math.max(30, defaultWaitSeconds) * 1000L;
        String last = "";
        while (System.currentTimeMillis() < deadline) {
            last = teaserText(serviceName);
            if (last.contains(serviceName) && last.contains("Terminnummer")) {
                return last;
            }
            try {
                Thread.sleep(400L);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
                break;
            }
        }
        Assert.fail("Meine Termine teaser for \"" + serviceName + "\" did not appear. Last text: " + last);
        return last;
    }
    private String teaserText(String serviceName) {
        String script =
                "var name=arguments[0];"
                        + "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=textOf(n.shadowRoot);var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=textOf(c[i]);return s;}"
                        + "function walk(n,fn){if(!n)return false;if(fn(n))return true;if(n.shadowRoot&&walk(n.shadowRoot,fn))return true;"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i],fn))return true;return false;}"
                        + "var best='';"
                        + "walk(document.body,function(n){"
                        + "if(n.nodeType!==1)return false;"
                        + "var t=textOf(n);"
                        + "if(t.indexOf(name)<0||t.indexOf('Terminnummer')<0)return false;"
                        + "if(!best||t.length<best.length)best=t;"
                        + "return false;});"
                        + "return best;";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, serviceName);
        return raw instanceof String ? (String) raw : "";
    }
    public void assertIcsDownloadOfferedOnDetailIntro() {
        context.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: assert ICS download in the detail intro");
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText(ICS_DOWNLOAD_LABEL),
                "ICS download link in the appointment detail intro");
        Assert.assertTrue(
                shadow.shadowDomContainsText(ICS_DOWNLOAD_LABEL),
                "The appointment detail intro should offer \"" + ICS_DOWNLOAD_LABEL + "\".");
    }
    public void downloadAppointmentIcs() {
        context.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: download appointment ICS");
        RemoteWebDriver driver = DriverUtil.getDriver();
        ((JavascriptExecutor) driver)
                .executeScript(
                        "window.__zmsCapturedIcs='';"
                                + "if(!window.__zmsIcsHooked){"
                                + "window.__zmsIcsHooked=true;"
                                + "var NativeBlob=window.Blob;"
                                + "function CapturingBlob(parts,options){"
                                + "var blob=new NativeBlob(parts,options);"
                                + "var type=options&&options.type?String(options.type):'';"
                                + "if(type.indexOf('text/calendar')>=0){"
                                + "window.__zmsCapturedIcs=Array.prototype.map.call(parts,function(p){"
                                + "return typeof p==='string'?p:'';}).join('');}"
                                + "return blob;}"
                                + "CapturingBlob.prototype=NativeBlob.prototype;"
                                + "window.Blob=CapturingBlob;}");
        String clickScript =
                "var label=arguments[0];"
                        + "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=textOf(n.shadowRoot);var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=textOf(c[i]);return s;}"
                        + "function walk(n,fn){if(!n)return false;if(fn(n))return true;if(n.shadowRoot&&walk(n.shadowRoot,fn))return true;"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i],fn))return true;return false;}"
                        + "var hit=null;"
                        + "walk(document.body,function(n){"
                        + "var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag!=='MUC-LINK'&&tag!=='A')return false;"
                        + "if(textOf(n).indexOf(label)<0)return false;"
                        + "hit=n;return true;});"
                        + "if(!hit)return false;"
                        + "hit.scrollIntoView({block:'center'});hit.click();return true;";
        boolean clicked = false;
        long deadline = System.currentTimeMillis() + defaultWaitSeconds * 1000L;
        while (System.currentTimeMillis() < deadline && !clicked) {
            Object result = ((JavascriptExecutor) driver).executeScript(clickScript, ICS_DOWNLOAD_LABEL);
            clicked = Boolean.TRUE.equals(result);
            if (!clicked) {
                try {
                    Thread.sleep(300L);
                } catch (InterruptedException e) {
                    Thread.currentThread().interrupt();
                    break;
                }
            }
        }
        Assert.assertTrue(clicked, "Could not click \"" + ICS_DOWNLOAD_LABEL + "\".");
        new WebDriverWait(driver, Duration.ofSeconds(defaultWaitSeconds))
                .until(
                        d -> {
                            Object raw =
                                    ((JavascriptExecutor) d)
                                            .executeScript("return window.__zmsCapturedIcs || '';");
                            return raw instanceof String && !((String) raw).isBlank();
                        });
        Object raw = ((JavascriptExecutor) driver).executeScript("return window.__zmsCapturedIcs || '';");
        capturedIcs = raw instanceof String ? (String) raw : "";
        Assert.assertFalse(capturedIcs.isBlank(), "The ICS download did not produce a calendar file.");
    }
    public void assertDownloadedIcsContainsBookedAppointment() {
        context.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: assert downloaded ICS contents");
        Assert.assertNotNull(capturedIcs, "No ICS file was downloaded.");
        Assert.assertTrue(capturedIcs.contains("BEGIN:VCALENDAR"), "ICS is missing BEGIN:VCALENDAR.");
        Assert.assertTrue(capturedIcs.contains("BEGIN:VEVENT"), "ICS is missing BEGIN:VEVENT.");
        Assert.assertTrue(capturedIcs.contains("END:VCALENDAR"), "ICS is missing END:VCALENDAR.");
        Assert.assertTrue(
                capturedIcs.contains("SUMMARY:") && capturedIcs.contains("Abholung Personalausweis"),
                "ICS SUMMARY should name the booked service. Was: " + icsSummaryLine());
        Assert.assertTrue(capturedIcs.contains("LOCATION:"), "ICS is missing LOCATION.");
        Assert.assertTrue(
                capturedIcs.contains("DTEND;TZID=Europe/Berlin:"), "ICS is missing DTEND.");
        Assert.assertTrue(
                slotState.rememberedAppointmentEpoch != null && slotState.rememberedAppointmentEpoch > 0,
                "The booked appointment time was not remembered.");
        String start =
                Instant.ofEpochSecond(slotState.rememberedAppointmentEpoch).atZone(BERLIN).format(ICS_DATE_TIME);
        Assert.assertTrue(
                capturedIcs.contains("DTSTART;TZID=Europe/Berlin:" + start),
                "ICS DTSTART should be the booked slot " + start + ".");
    }
    private String icsSummaryLine() {
        if (capturedIcs == null) {
            return "";
        }
        return capturedIcs.lines().filter(line -> line.startsWith("SUMMARY:")).findFirst().orElse("");
    }


    public void trySetBookingProcessFromPage() {
        if (trySetBookingProcessFromLocalStorage()) {
            return;
        }
        if (trySetBookingProcessFromSessionAuthHash()) {
            return;
        }
        if (trySetBookingProcessFromCurrentReservedHash()) {
            return;
        }
        if (trySetBookingProcessFromVueAppointment()) {
            return;
        }
        if (trySetBookingProcessFromCapturedApiResponse()) {
            return;
        }
        trySetBookingProcessIdFromDom();
    }
    /**
     * ZMSKVR-1088 / ZMSKVR-1342: teaser H3 lists main service then combined services in booking
     * order (not alphabetical). Bound to the process just booked via Terminnummer.
     */
    public void assertMyAppointmentsTeaserServiceTitleOrder(String orderedNamesCsv) {
        context.set();
        List<String> expected = splitOrderedServiceNames(orderedNamesCsv);
        Assert.assertFalse(expected.isEmpty(), "Expected at least one service name in the title order.");
        trySetBookingProcessFromPage();
        ThinnedProcess booked = zms.ataf.rest.steps.CitizenApiSteps.getBookingProcess();
        Assert.assertNotNull(booked, "Need the booked process to bind the teaser card.");
        Assert.assertNotNull(booked.getProcessId(), "Booked processId required for teaser card.");
        String card = waitForTeaserTextContaining("Terminnummer: " + booked.getProcessId());
        assertServiceTitleOrder(card, expected, "Meine Termine teaser");
    }

    /**
     * ZMSKVR-1088 / ZMSKVR-1342: detail muc-intro title lists main service then combined services
     * in booking order (not alphabetical).
     */
    public void assertAppointmentDetailServiceTitleOrder(String orderedNamesCsv) {
        context.set();
        List<String> expected = splitOrderedServiceNames(orderedNamesCsv);
        Assert.assertFalse(expected.isEmpty(), "Expected at least one service name in the title order.");
        CitizenViewWaits.waitWithThreeWindows(
                () -> {
                    String title = detailIntroTitleText();
                    return title.contains("1x " + expected.get(0));
                },
                "Appointment detail title with main service");
        String title = detailIntroTitleText();
        assertServiceTitleOrder(title, expected, "appointment detail");
    }

    private String waitForTeaserTextContaining(String fragment) {
        long deadline = System.currentTimeMillis() + Math.max(30, defaultWaitSeconds) * 1000L;
        String last = "";
        while (System.currentTimeMillis() < deadline) {
            last = teaserTextContaining(fragment);
            if (last.contains(fragment) && last.contains("Terminnummer")) {
                return last;
            }
            try {
                Thread.sleep(400L);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
                break;
            }
        }
        Assert.fail("Meine Termine teaser containing \"" + fragment + "\" did not appear. Last text: " + last);
        return last;
    }

    private String teaserTextContaining(String fragment) {
        String script =
                "var needle=arguments[0];"
                        + "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=textOf(n.shadowRoot);var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=textOf(c[i]);return s;}"
                        + "function walk(n,fn){if(!n)return false;if(fn(n))return true;if(n.shadowRoot&&walk(n.shadowRoot,fn))return true;"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i],fn))return true;return false;}"
                        + "var best='';"
                        + "walk(document.body,function(n){"
                        + "if(n.nodeType!==1)return false;"
                        + "var t=textOf(n);"
                        + "if(t.indexOf(needle)<0||t.indexOf('Terminnummer')<0)return false;"
                        + "if(!best||t.length<best.length)best=t;"
                        + "return false;});"
                        + "return best;";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, fragment);
        return raw instanceof String ? (String) raw : "";
    }

    /**
     * Service lines from the appointment-detail intro (formatMultilineTitle). MucIntro is a Vue
     * SFC that renders {@code div.m-intro > h1}, not a {@code muc-intro} custom element, so the
     * title prop is never a DOM attribute — read the painted h1 (fallback: text before
     * Terminnummer), same deep walk as teaser asserts.
     */
    private String detailIntroTitleText() {
        String script =
                "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=' '+textOf(n.shadowRoot);"
                        + "if(n.assignedNodes){var a=n.assignedNodes({flatten:true});"
                        + "for(var j=0;j<a.length;j++)s+=' '+textOf(a[j]);}"
                        + "var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=' '+textOf(c[i]);"
                        + "return s;}"
                        + "function walk(n,fn){if(!n)return null;var r=fn(n);if(r)return r;"
                        + "if(n.shadowRoot){r=walk(n.shadowRoot,fn);if(r)return r;}"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++){r=walk(c[i],fn);if(r)return r;}"
                        + "return null;}"
                        + "var fromH1=walk(document.body,function(n){"
                        + "if((n.tagName||'').toUpperCase()!=='H1')return null;"
                        + "var t=textOf(n).replace(/\\s+/g,' ').trim();"
                        + "return t.indexOf('1x ')>=0?t:null;"
                        + "});"
                        + "if(fromH1)return fromH1;"
                        + "var best='';"
                        + "walk(document.body,function(n){"
                        + "if(n.nodeType!==1)return null;"
                        + "var t=textOf(n).replace(/\\s+/g,' ').trim();"
                        + "if(t.indexOf('1x ')<0||t.indexOf('Terminnummer')<0)return null;"
                        + "if(!best||t.length<best.length)best=t;"
                        + "return null;});"
                        + "if(!best)return '';"
                        + "var start=best.indexOf('1x ');"
                        + "var end=best.indexOf('Terminnummer');"
                        + "return (end>start?best.substring(start,end):best.substring(start)).trim();";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return raw == null ? "" : raw.toString().replace('\n', ' ').replaceAll("\\s+", " ").trim();
    }

    private static List<String> splitOrderedServiceNames(String orderedNamesCsv) {
        List<String> names = new ArrayList<>();
        if (orderedNamesCsv == null || orderedNamesCsv.isBlank()) {
            return names;
        }
        for (String part : orderedNamesCsv.split(",")) {
            String trimmed = part.trim();
            if (!trimmed.isEmpty()) {
                names.add(trimmed);
            }
        }
        return names;
    }

    private static void assertServiceTitleOrder(String text, List<String> expected, String where) {
        String normalized = text == null ? "" : text.replace('\n', ' ').replaceAll("\\s+", " ");
        int previous = -1;
        for (String name : expected) {
            String needle = "1x " + name;
            int idx = normalized.indexOf(needle);
            Assert.assertTrue(
                    idx > previous,
                    where
                            + " must list \""
                            + needle
                            + "\" after earlier services (booking order, not A–Z). Text: "
                            + normalized);
            previous = idx;
        }
    }

    public void assertAppointmentDetailLocation(
            String typeLabel, String place, String locationText, String preparationHint, String extra) {
        context.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert appointment detail location for {}", typeLabel);
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText(locationText) && shadow.shadowDomHasHeading(2, "Ort"),
                "Ort section on the appointment detail");
        Assert.assertTrue(shadow.shadowDomHasHeading(2, "Ort"), "The detail page is missing the Ort heading.");
        Assert.assertTrue(
                shadow.shadowDomContainsText(typeLabel),
                "Detail intro is missing \"" + typeLabel + "\".");
        Assert.assertTrue(shadow.shadowDomContainsText(place), "Detail place is missing \"" + place + "\".");
        Assert.assertTrue(
                shadow.shadowDomContainsText(locationText),
                "Ort section is missing \"" + locationText + "\".");
        Assert.assertTrue(
                shadow.shadowDomContainsText(preparationHint),
                "Ort section is missing \"" + preparationHint + "\".");
        if (extra != null && !extra.isBlank()) {
            Assert.assertTrue(shadow.shadowDomContainsText(extra), "Ort section is missing \"" + extra + "\".");
        }
        if ("Videoberatung".equals(typeLabel)) {
            Assert.assertTrue(
                    shadow.shadowDomContainsText("Info-Seite zur Videoberatung"),
                    "Ort section is missing the video consultation info link.");
            Assert.assertFalse(
                    shadow.shadowDomContainsText("Wir rufen Sie unter der von Ihnen angegebenen Nummer an:"),
                    "Video detail should not show the telephone location text.");
        } else {
            Assert.assertFalse(
                    shadow.shadowDomContainsText("Info-Seite zur Videoberatung"),
                    "Phone detail should not show the video consultation info link.");
        }
    }
}
