package zms.ataf.ui.pages.citizenview.steps;

import java.time.Duration;

import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.utils.DriverUtil;
import zms.ataf.ui.pages.citizenview.CitizenViewPage;
import zms.ataf.ui.pages.citizenview.CitizenViewPageContext;
import zms.ataf.ui.pages.citizenview.support.CitizenViewWaits;
import zms.ataf.ui.pages.citizenview.support.RuppertstrasseWartezoneHints;
import zms.ataf.ui.pages.citizenview.support.ShadowDom;
import zms.ataf.ui.pages.citizenview.support.SlotBookingState;
import java.nio.charset.StandardCharsets;
import java.util.Base64;
import zms.ataf.rest.dto.zmscitizenapi.ThinnedProcess;

/** Übersicht / preconfirm: legal notices, reserve, confirmation callouts. */
public final class OverviewStep {

    private final CitizenViewPageContext context;
    private final ShadowDom shadow;
    private final CitizenViewPage page;
    private final SlotBookingState slotState;
    private final int defaultWaitSeconds;

    public OverviewStep(
            CitizenViewPageContext context,
            ShadowDom shadow,
            CitizenViewPage page,
            SlotBookingState slotState,
            int defaultWaitSeconds) {
        this.context = context;
        this.shadow = shadow;
        this.page = page;
        this.slotState = slotState;
        this.defaultWaitSeconds = defaultWaitSeconds;
    }
    private static final String DE_RESERVE = "Termin reservieren";
    private static final String RESCHEDULE_APPOINTMENT_BUTTON = "Termin verschieben";
    private static final String CANCEL_RESCHEDULE_BUTTON = "Verschieben abbrechen";
    private static final String ACTIVATION_CALLOUT_HEADING = "Aktivieren Sie Ihren Termin.";
    private static final String CONFIRMATION_SUCCESS_HEADING = "Ihr Termin wurde gebucht.";
    private static final String CONFIRMATION_SUCCESS_TEXT =
            "Eine Bestätigung und weitere Informationen zu Ihrem Termin erhalten Sie per E-Mail. Wir freuen uns auf Ihren Besuch.";
    private static final String VIEW_APPOINTMENT_BUTTON = "Termin ansehen";
    private static final String BOOK_ANOTHER_APPOINTMENT_BUTTON = "Weiteren Termin vereinbaren";
    private static final String CANCELLATION_SUCCESS_HEADING =
            "Sie haben Ihren Termin erfolgreich abgesagt.";
    private static final String CANCELLATION_SUCCESS_TEXT =
            "Danke, dass Sie Ihren Termin für andere freigegeben haben.";
    private static final String ALREADY_ACTIVATED_BANNER_MARKER =
            "Sie haben Ihren Termin bereits aktiviert.";
    public void assertProviderSummaryVisible(int officeId) {
        assertProviderSummaryVisible(officeId, "Bürgerbüro Ruppertstraße");
    }
    public String deepVisibleProviderSummaryText(int officeId) {
        context.set();
        String script =
                "var want='provider-'+String(arguments[0]);"
                        + "function visible(el){"
                        + " if(!el||el.nodeType!==1)return false;"
                        + " var n=el;"
                        + " while(n){"
                        + "  if(n.nodeType===1){"
                        + "   try{var st=getComputedStyle(n);if(st.display==='none'||st.visibility==='hidden')return false;}catch(e0){}"
                        + "  }"
                        + "  if(n.parentElement){n=n.parentElement;continue;}"
                        + "  var root=n.getRootNode&&n.getRootNode();"
                        + "  if(root&&root.host){n=root.host;continue;}"
                        + "  break;"
                        + " }"
                        + " try{return el.getClientRects().length>0;}catch(e1){return true;}"
                        + "}"
                        + "function collect(root,out){"
                        + " if(!root)return;"
                        + " if(root.nodeType===1&&root.id===want)out.push(root);"
                        + " if(root.shadowRoot)collect(root.shadowRoot,out);"
                        + " var c=root.children;if(c)for(var i=0;i<c.length;i++)collect(c[i],out);"
                        + "}"
                        + "var found=[];collect(document.body,found);"
                        + "for(var i=0;i<found.length;i++){"
                        + " var el=found[i];"
                        + " if(!visible(el))continue;"
                        + " var tag=(el.tagName||'').toLowerCase();"
                        + " if(tag.indexOf('checkbox')>=0)continue;"
                        + " if(tag==='input')continue;"
                        + " var txt=(el.innerText||el.textContent||'').replace(/\\s+/g,' ').trim();"
                        + " if(txt)return txt;"
                        + "}"
                        + "return null;";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, officeId);
        return o == null ? null : String.valueOf(o);
    }
    public boolean deepVisibleProviderSummaryExists(int officeId) {
        String text = deepVisibleProviderSummaryText(officeId);
        return text != null && !text.isBlank();
    }
    public void assertProviderSummaryVisible(int officeId, String expectedStandortLabel) {
        context.set();
        String sel = "#provider-" + officeId;
        CitizenViewWaits.waitWithThreeWindows(
                () -> deepVisibleProviderSummaryExists(officeId), "Provider summary " + sel);
        if (!deepVisibleProviderSummaryExists(officeId)) {
            ScenarioLogManager.getLogger()
                    .warn(
                            "Visible provider summary {} not found after 60s; waiting up to 30s more after deep-link navigation",
                            sel);
            try {
                new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(30))
                        .until(d -> deepVisibleProviderSummaryExists(officeId));
            } catch (TimeoutException e) {
                ScenarioLogManager.getLogger()
                        .warn("Visible provider summary {} still not found after extended wait", sel);
            }
        }
        String summaryText = deepVisibleProviderSummaryText(officeId);
        Assert.assertNotNull(summaryText, "Expected visible booking summary provider block: " + sel);
        Assert.assertTrue(
                summaryText.contains(expectedStandortLabel),
                "Expected standort label in visible summary "
                        + sel
                        + ": "
                        + expectedStandortLabel
                        + " actual="
                        + summaryText);
    }
    public void assertStandardLegalNotices() {
        context.set();
        shadow.waitUntilShadowContains("Rechtliche Hinweise", defaultWaitSeconds);
        Assert.assertTrue(
                shadow.shadowDomHasHeading(3, "Rechtliche Hinweise"),
                "Expected h3 Rechtliche Hinweise above Termin reservieren.");
        Assert.assertFalse(
                shadow.shadowDomContainsText("Einwilligungen"),
                "The consent heading Einwilligungen should be gone.");
        Assert.assertTrue(
                shadow.shadowDomHasHeading(4, "Datenschutz und Datenverarbeitung"),
                "Expected h4 Datenschutz und Datenverarbeitung.");
        Assert.assertTrue(
                shadow.shadowDomContainsText("Datenschutzhinweise Terminvereinbarung"),
                "Expected the privacy link text.");
        Assert.assertTrue(
                shadow.shadowHrefContains(
                        "https://stadt.muenchen.de/dam/jcr:26e72fa3-cec7-4628-9a0a-272c330a2bd2/23_07_Art_13_DSGVO.pdf"),
                "Expected the shipped privacy PDF link.");
        Assert.assertTrue(
                shadow.shadowDomHasHeading(4, "Elektronische Kommunikation"),
                "Expected h4 Elektronische Kommunikation.");
        Assert.assertTrue(
                shadow.deepElementExists("#checkbox-electronic-communication"),
                "Expected the electronic communication checkbox.");
        Assert.assertFalse(
                privacyAcknowledgementCheckboxPresent(),
                "Privacy acknowledgement checkbox should be gone.");
    }
    public void assertSelectedAppointmentPlaceExclusive(String heading, String exclusive) {
        if (!"yes".equals(exclusive)) {
            return;
        }
        context.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> selectedAppointmentPlaceText() != null);
        String text = selectedAppointmentPlaceText();
        Assert.assertEquals(
                text,
                heading,
                "Selected appointment place should be only the variant label.");
        Assert.assertFalse(
                selectedAppointmentPlaceHasIcon(),
                "Selected appointment place should not show a variant icon. text=" + text);
    }
    public void assertBookingOverviewPlace(int officeId, String heading, String hint) {
        String text = visibleProviderSummaryOrFail(officeId);
        Assert.assertTrue(
                text.contains(heading),
                "Expected place heading in office " + officeId + " summary: " + heading + " actual=" + text);
        if (hint != null && !hint.isBlank()) {
            Assert.assertTrue(
                    text.contains(hint),
                    "Expected place hint in office " + officeId + " summary: " + hint + " actual=" + text);
        }
    }
    public void assertBookingOverviewPlaceIncludes(int officeId, String fragment) {
        if (fragment == null || fragment.isBlank()) {
            return;
        }
        String text = visibleProviderSummaryOrFail(officeId);
        Assert.assertTrue(
                text.contains(fragment),
                "Expected place text in office " + officeId + " summary: " + fragment + " actual=" + text);
    }
    public void assertBookingOverviewPlaceExcludes(int officeId, String fragment) {
        if (fragment == null || fragment.isBlank()) {
            return;
        }
        String text = visibleProviderSummaryOrFail(officeId);
        Assert.assertFalse(
                text.contains(fragment),
                "Place for office " + officeId + " should not contain: " + fragment + " actual=" + text);
    }

    /**
     * ZMSKVR-1051 / ZMSKVR-1309: overview Ort Wartezone and Hinweis marker must belong to the same
     * Ruppertstraße scope. Remembers the code for a later “other Wartebereich” booking.
     */
    public String assertAndRememberMatchingRuppertstrasseWartezoneHint(int officeId) {
        context.set();
        CitizenViewWaits.waitWithThreeWindows(
                () -> {
                    String t = deepDocumentText();
                    return RuppertstrasseWartezoneHints.detectCode(t) != null
                            && t.contains(RuppertstrasseWartezoneHints.OVERVIEW_HINT_HEADING);
                },
                "Overview Wartezone + ATAF scope hint");
        String text = deepDocumentText();
        String code = RuppertstrasseWartezoneHints.detectCode(text);
        Assert.assertNotNull(
                code,
                "Overview must show a matching Wartebereich 03/04 with ATAF Hinweis WB03/WB04. Text: " + text);
        Assert.assertTrue(
                text.contains(RuppertstrasseWartezoneHints.zoneFor(code)),
                "Overview Ort missing " + RuppertstrasseWartezoneHints.zoneFor(code) + ". Text: " + text);
        Assert.assertTrue(
                text.contains(RuppertstrasseWartezoneHints.hintFor(code)),
                "Overview Hinweis missing " + RuppertstrasseWartezoneHints.hintFor(code) + ". Text: " + text);
        Assert.assertFalse(
                text.contains(RuppertstrasseWartezoneHints.hintFor(RuppertstrasseWartezoneHints.otherCode(code))),
                "Overview must not mix the other Wartebereich hint. Text: " + text);
        // Ort summary also carries the Wartezone for office 10489.
        String place = visibleProviderSummaryOrFail(officeId);
        Assert.assertTrue(
                place.contains(RuppertstrasseWartezoneHints.zoneFor(code)),
                "Provider summary missing Wartezone " + code + ". Text: " + place);
        slotState.rememberedWartezoneCode = code;
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: overview matched Ruppertstraße Wartezone {} for office {}", code, officeId);
        return code;
    }

    /** ZMSKVR-1051 / ZMSKVR-1309 step 10: second booking must land on the other Wartebereich. */
    public void assertOtherRuppertstrasseWartezoneHint(int officeId) {
        Assert.assertNotNull(
                slotState.rememberedWartezoneCode,
                "First Wartezone was not remembered before the second booking.");
        String first = slotState.rememberedWartezoneCode;
        String expected = RuppertstrasseWartezoneHints.otherCode(first);
        String code = assertAndRememberMatchingRuppertstrasseWartezoneHint(officeId);
        Assert.assertEquals(
                code,
                expected,
                "Second same-time booking should switch Wartebereich from " + first + " to " + expected + ".");
    }

    /**
     * ZMSKVR-843 / ZMSKVR-1014: overview Hinweis is in a dedicated block under the Hinweis heading.
     * Plain text is wrapped in {@code <p>}. HTML markers (ATAF links) go through
     * {@code containsParagraphTag} + DOMParser, which treats inline HTML as a {@code <p>} and
     * therefore paint a {@code <div>} host — both are accepted as the overview block wrapper.
     */
    public void assertOverviewScopeHintWrappedInParagraph() {
        context.set();
        String code = slotState.rememberedWartezoneCode;
        Assert.assertNotNull(code, "Need a remembered Wartezone before asserting overview hint wrap.");
        String needle = RuppertstrasseWartezoneHints.hintFor(code);
        Object raw =
                ((JavascriptExecutor) DriverUtil.getDriver())
                        .executeScript(
                                "var needle=arguments[0];"
                                        + "function textOf(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                                        + "if(n.shadowRoot)s+=' '+textOf(n.shadowRoot);"
                                        + "if(n.assignedNodes){var a=n.assignedNodes({flatten:true});"
                                        + "for(var j=0;j<a.length;j++)s+=' '+textOf(a[j]);}"
                                        + "var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=' '+textOf(c[i]);return s;}"
                                        + "var best=null,bestLen=1e9;"
                                        + "function consider(n){if(!n||n.nodeType!==1)return;"
                                        + "var tag=(n.tagName||'').toUpperCase();"
                                        + "if(tag!=='P'&&tag!=='DIV')return;"
                                        + "var t=textOf(n).replace(/\\s+/g,' ').trim();"
                                        + "if(t.indexOf(needle)<0)return;"
                                        + "if(t.length<bestLen){bestLen=t.length;best=tag;}}"
                                        + "function walk(n){if(!n)return;consider(n);"
                                        + "if(n.shadowRoot)walk(n.shadowRoot);"
                                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)walk(c[i]);}"
                                        + "walk(document.body);return best||'';");
        String tag = raw == null ? "" : raw.toString();
        Assert.assertTrue(
                "P".equals(tag) || "DIV".equals(tag),
                "Overview Hinweis \""
                        + needle
                        + "\" must sit in a dedicated <p> or <div> block (ZMSKVR-843). Found: "
                        + tag);
    }

    /** ZMSKVR-1530: overview Hinweis HTML is painted (link text, no raw tags). */
    public void assertOverviewScopeHintHtmlRendered() {
        context.set();
        String code = slotState.rememberedWartezoneCode;
        Assert.assertNotNull(code, "Need a remembered Wartezone before asserting overview HTML.");
        String linkLabel = RuppertstrasseWartezoneHints.linkLabelFor(code);
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText(linkLabel), "Overview ATAF hint link label");
        Assert.assertTrue(shadow.shadowDomContainsText(linkLabel), "Overview missing rendered link " + linkLabel);
        Assert.assertTrue(
                shadow.shadowHrefContains(RuppertstrasseWartezoneHints.HINT_HREF),
                "Overview hint must expose href " + RuppertstrasseWartezoneHints.HINT_HREF);
        Assert.assertFalse(
                shadow.shadowDomContainsText("<a href"),
                "Overview Hinweis must not show raw <a> markup.");
        Assert.assertFalse(
                shadow.shadowDomContainsText("<br>"),
                "Overview Hinweis must not show raw <br> markup.");
        Assert.assertFalse(
                shadow.shadowDomContainsText("<em>") || shadow.shadowDomContainsText("<strong>"),
                "Overview Hinweis must not show raw emphasis tags.");
    }

    private String deepDocumentText() {
        Object o =
                ((JavascriptExecutor) DriverUtil.getDriver())
                        .executeScript(
                                "function walk(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                                        + "if(n.shadowRoot)s+=' '+walk(n.shadowRoot);"
                                        + "if(n.assignedNodes){var a=n.assignedNodes({flatten:true});"
                                        + "for(var j=0;j<a.length;j++)s+=' '+walk(a[j]);}"
                                        + "var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=' '+walk(c[i]);"
                                        + "return s;}"
                                        + "return walk(document.documentElement).replace(/\\s+/g,' ').trim();");
        return o == null ? "" : o.toString();
    }
    public void assertVideoLegalNotices(String legal) {
        context.set();
        if (!"yes".equals(legal)) {
            Assert.assertFalse(
                    shadow.shadowDomContainsText("Nutzungsbedingungen Videoberatung"),
                    "Video consultation terms should be hidden for this variant.");
            return;
        }
        shadow.waitUntilShadowContains("Nutzungsbedingungen Videoberatung", defaultWaitSeconds);
        Assert.assertTrue(
                shadow.shadowDomHasHeading(4, "Datenschutz und Datenverarbeitung"),
                "Expected h4 Datenschutz und Datenverarbeitung.");
        Assert.assertTrue(
                shadow.shadowDomHasHeading(4, "Elektronische Kommunikation"),
                "Expected h4 Elektronische Kommunikation.");
        Assert.assertTrue(
                shadow.shadowDomHasHeading(4, "Nutzungsbedingungen Videoberatung"),
                "Expected h4 Nutzungsbedingungen Videoberatung.");
        shadow.assertShadowHref("https://stadt.muenchen.de/dam/jcr:26e72fa3-cec7-4628-9a0a-272c330a2bd2/23_07_Art_13_DSGVO.pdf");
        shadow.assertShadowHref("https://stadt.muenchen.de/dam/DSGVO/Datenschutzhinweise-Videoberatung.pdf");
        shadow.assertShadowHref("https://stadt.muenchen.de/infos/elektronische-kommunikation.html");
        shadow.assertShadowHref("https://stadt.muenchen.de/dam/DSGVO/Nutzungsbedingungen-Videoberatung.pdf");
    }
    public void assertReserveAppointmentButtonEnabled(boolean enabled) {
        context.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> reserveAppointmentButtonState() != null);
        String state = reserveAppointmentButtonState();
        Assert.assertEquals(
                state,
                enabled ? "enabled" : "disabled",
                "Termin reservieren should be " + (enabled ? "enabled" : "disabled") + ".");
    }
    public void assertReserveAppointmentButtonAfterCommunication(String legal) {
        assertReserveAppointmentButtonEnabled(!"yes".equals(legal));
    }
    public void assertServiceLinkPointsToMunichDe() {
        context.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> shadow.shadowHrefContains("https://stadt.muenchen.de/service/info/"));
        Assert.assertTrue(
                shadow.shadowHrefContains("https://stadt.muenchen.de/service/info/"),
                "Expected a service link to https://stadt.muenchen.de/service/info/.");
    }

    /** Leistung block on Übersicht / rebook-or-cancel dialog (e.g. internal service from appointment). */
    public void assertOverviewServiceVisible(int serviceId, String serviceName) {
        context.set();
        String sel = "#service-" + serviceId;
        CitizenViewWaits.waitWithThreeWindows(
                () -> deepVisibleServiceSummaryExists(serviceId), "Service summary " + sel);
        String text = deepVisibleServiceSummaryText(serviceId);
        Assert.assertNotNull(text, "Expected visible booking summary service block: " + sel);
        Assert.assertTrue(
                text.contains(serviceName),
                "Expected service name in " + sel + ": " + serviceName + " actual=" + text);
    }

    private boolean deepVisibleServiceSummaryExists(int serviceId) {
        Object raw =
                ((JavascriptExecutor) DriverUtil.getDriver())
                        .executeScript(
                                "var id='service-'+" + serviceId + ";"
                                        + "function shown(n){if(!n)return false;var s=getComputedStyle(n);"
                                        + "return s&&s.display!='none'&&s.visibility!='hidden'&&n.offsetParent!==null;}"
                                        + "function walk(root){if(!root)return null;if(root.id===id&&shown(root))return root;"
                                        + "if(root.shadowRoot){var s=walk(root.shadowRoot);if(s)return s;}"
                                        + "var c=root.children||[];for(var i=0;i<c.length;i++){var f=walk(c[i]);if(f)return f;}"
                                        + "return null;}"
                                        + "return !!walk(document.body);");
        return Boolean.TRUE.equals(raw);
    }

    private String deepVisibleServiceSummaryText(int serviceId) {
        Object raw =
                ((JavascriptExecutor) DriverUtil.getDriver())
                        .executeScript(
                                "var id='service-'+" + serviceId + ";"
                                        + "function shown(n){if(!n)return false;var s=getComputedStyle(n);"
                                        + "return s&&s.display!='none'&&s.visibility!='hidden'&&n.offsetParent!==null;}"
                                        + "function walk(root){if(!root)return null;if(root.id===id&&shown(root))return root;"
                                        + "if(root.shadowRoot){var s=walk(root.shadowRoot);if(s)return s;}"
                                        + "var c=root.children||[];for(var i=0;i<c.length;i++){var f=walk(c[i]);if(f)return f;}"
                                        + "return null;}"
                                        + "var n=walk(document.body);return n?((n.innerText||n.textContent||'')+''):null;");
        if (raw == null) {
            return null;
        }
        String text = String.valueOf(raw).replaceAll("\\s+", " ").trim();
        return text.isEmpty() ? null : text;
    }
    private String visibleProviderSummaryOrFail(int officeId) {
        context.set();
        CitizenViewWaits.waitWithThreeWindows(
                () -> deepVisibleProviderSummaryExists(officeId), "Provider summary #provider-" + officeId);
        String text = deepVisibleProviderSummaryText(officeId);
        Assert.assertNotNull(text, "Expected visible booking summary provider block #provider-" + officeId);
        return text;
    }
    private String selectedAppointmentPlaceText() {
        Object raw =
                ((JavascriptExecutor) DriverUtil.getDriver())
                        .executeScript(selectedAppointmentPlaceScript(false));
        if (raw == null) {
            return null;
        }
        String text = String.valueOf(raw).replaceAll("\\s+", " ").trim();
        return text.isEmpty() ? null : text;
    }
    private boolean selectedAppointmentPlaceHasIcon() {
        Object raw =
                ((JavascriptExecutor) DriverUtil.getDriver())
                        .executeScript(selectedAppointmentPlaceScript(true));
        return Boolean.TRUE.equals(raw);
    }
    private static String selectedAppointmentPlaceScript(boolean icon) {
        String result = icon
                ? "return !!(el.querySelector && el.querySelector('svg,use'));"
                : "return (el.innerText||el.textContent||'').replace(/\\s+/g,' ').trim();";
        return "function visible(el){"
                + " if(!el||el.nodeType!==1)return false;"
                + " var n=el;"
                + " while(n){"
                + "  if(n.nodeType===1){try{var st=getComputedStyle(n);if(st.display==='none'||st.visibility==='hidden')return false;}catch(e0){}}"
                + "  if(n.parentElement){n=n.parentElement;continue;}"
                + "  var root=n.getRootNode&&n.getRootNode();"
                + "  if(root&&root.host){n=root.host;continue;}"
                + "  break;"
                + " }"
                + " try{return el.getClientRects().length>0;}catch(e1){return true;}"
                + "}"
                + "function walk(root){"
                + " if(!root)return null;"
                + " if(root.nodeType===1){"
                + "  var cls=root.className&&root.className.baseVal!==undefined?root.className.baseVal:root.className;"
                + "  if(typeof cls==='string'&&cls.indexOf('m-teaser-contained-contact__summary')>=0&&visible(root)){"
                + "   var el=root;"
                + result
                + "  }"
                + "  if(root.shadowRoot){var s=walk(root.shadowRoot);if(s)return s;}"
                + " }"
                + " var c=root.children;if(c)for(var i=0;i<c.length;i++){var f=walk(c[i]);if(f)return f;}"
                + " return null;"
                + "}"
                + "return walk(document.body);";
    }
    private String reserveAppointmentButtonState() {
        String script =
                "function visible(el){"
                        + " if(!el||el.nodeType!==1)return false;"
                        + " try{var st=getComputedStyle(el);if(st.display==='none'||st.visibility==='hidden')return false;"
                        + " return el.getClientRects().length>0;}catch(e){return true;}"
                        + "}"
                        + "function off(el){"
                        + " if(!el)return false;"
                        + " if(el.disabled===true||(el.hasAttribute&&el.hasAttribute('disabled'))||el.getAttribute('aria-disabled')==='true')return true;"
                        + " var inner=el.shadowRoot&&el.shadowRoot.querySelector&&el.shadowRoot.querySelector('button,[aria-disabled]');"
                        + " return !!(inner&&(inner.disabled===true||inner.hasAttribute('disabled')||inner.getAttribute('aria-disabled')==='true'));"
                        + "}"
                        + "function walk(root){"
                        + " if(!root)return null;"
                        + " if(root.nodeType===1){"
                        + "  var tag=(root.tagName||'').toUpperCase();"
                        + "  var txt=((root.innerText||root.textContent||'')+'').replace(/\\s+/g,' ').trim();"
                        + "  if((tag==='MUC-BUTTON'||tag==='BUTTON')&&txt.indexOf('Termin reservieren')>=0&&visible(root)){"
                        + "   return off(root)?'disabled':'enabled';"
                        + "  }"
                        + "  if(root.shadowRoot){var s=walk(root.shadowRoot);if(s)return s;}"
                        + " }"
                        + " var c=root.children;if(c)for(var i=0;i<c.length;i++){var f=walk(c[i]);if(f)return f;}"
                        + " return null;"
                        + "}"
                        + "return walk(document.body);";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return raw == null ? null : String.valueOf(raw);
    }
    private boolean privacyAcknowledgementCheckboxPresent() {
        String script =
                "function walk(root){"
                        + " if(!root)return false;"
                        + " if(root.nodeType===1){"
                        + "  var tag=(root.tagName||'').toUpperCase();"
                        + "  var type=(root.getAttribute&&root.getAttribute('type')||'').toLowerCase();"
                        + "  if((tag==='INPUT'&&type==='checkbox')||tag.indexOf('CHECKBOX')>=0){"
                        + "   var id=(root.id||'')+' '+(root.getAttribute('name')||'')+' '+(root.getAttribute('aria-label')||'');"
                        + "   var label='';"
                        + "   if(root.id){"
                        + "    var rootNode=root.getRootNode?root.getRootNode():document;"
                        + "    var lab=rootNode.querySelector?rootNode.querySelector('label[for=\"'+root.id+'\"]'):null;"
                        + "    if(lab)label=lab.textContent||'';"
                        + "   }"
                        + "   var blob=(id+' '+label).toLowerCase();"
                        + "   if(blob.indexOf('datenschutz')>=0||blob.indexOf('dsgvo')>=0||blob.indexOf('einwilligung')>=0)return true;"
                        + "  }"
                        + "  if(root.shadowRoot&&walk(root.shadowRoot))return true;"
                        + " }"
                        + " var c=root.children;if(c)for(var i=0;i<c.length;i++)if(walk(c[i]))return true;"
                        + " return false;"
                        + "}"
                        + "return walk(document.body);";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return Boolean.TRUE.equals(raw);
    }
    public void waitForPreconfirmPageAfterUpdate() {
        context.set();
        String sel = "#checkbox-electronic-communication";
        CitizenViewWaits.waitWithThreeWindows(() -> shadow.deepElementExists(sel), "Preconfirm page " + sel);
        Assert.assertTrue(
                shadow.deepElementExists(sel),
                "Preconfirm page (electronic communication checkbox " + sel + ") not visible after Kontakt Weiter with retries.");
        ScenarioLogManager.getLogger().info("zmscitizenview: preconfirm page visible");
    }
    public void acceptCommunication() {
        context.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: accept electronic communication (visible label)");
        acceptVisibleCheckbox("checkbox-electronic-communication", "Electronic communication");
    }
    public void acceptVideoConsultationTermsIfShown() {
        context.set();
        if (!shadow.shadowDomContainsText("Nutzungsbedingungen Videoberatung")) {
            ScenarioLogManager.getLogger().info("zmscitizenview: video consultation terms are not shown");
            return;
        }
        ScenarioLogManager.getLogger().info("zmscitizenview: accept video consultation terms (visible label)");
        acceptVisibleCheckbox("checkbox-video-consultation", "Video consultation terms");
    }
    private void acceptVisibleCheckbox(String checkboxId, String label) {
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> clickVisibleCheckboxLabel(checkboxId));
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> isCheckboxChecked(checkboxId));
        Assert.assertTrue(isCheckboxChecked(checkboxId), label + " checkbox was not checked (visible label click).");
    }
    private boolean clickVisibleCheckboxLabel(String checkboxId) {
        String script =
                "var checkboxId=arguments[0];"
                        + "function vis(el){if(!el||!el.getBoundingClientRect)return false;"
                        + "var r=el.getBoundingClientRect();if(r.width<=0||r.height<=0)return false;"
                        + "var st=window.getComputedStyle(el);return st.visibility!=='hidden'&&st.display!=='none'&&st.opacity!=='0';}"
                        + "function walk(root){if(!root)return null;"
                        + "var labels=root.querySelectorAll?root.querySelectorAll('label[for=\"'+checkboxId+'\"]'):[];"
                        + "for(var i=0;i<labels.length;i++)if(vis(labels[i]))return labels[i];"
                        + "var all=root.querySelectorAll?root.querySelectorAll('*'):[];"
                        + "for(var j=0;j<all.length;j++)if(all[j].shadowRoot){var f=walk(all[j].shadowRoot);if(f)return f;}"
                        + "return null;}"
                        + "var lab=walk(document.body);if(!lab)return false;"
                        + "var root=lab.getRootNode?lab.getRootNode():document;"
                        + "var input=root.querySelector?root.querySelector('#'+checkboxId):null;"
                        + "if(input&&input.checked)return true;"
                        + "lab.scrollIntoView({block:'center'});lab.click();return true;";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, checkboxId);
        return Boolean.TRUE.equals(o);
    }
    private boolean isCheckboxChecked(String checkboxId) {
        String script =
                "var checkboxId=arguments[0];"
                        + "function vis(el){if(!el||!el.getBoundingClientRect)return false;"
                        + "var r=el.getBoundingClientRect();if(r.width<=0||r.height<=0)return false;"
                        + "var st=window.getComputedStyle(el);return st.visibility!=='hidden'&&st.display!=='none'&&st.opacity!=='0';}"
                        + "function walk(root){if(!root)return null;"
                        + "var labels=root.querySelectorAll?root.querySelectorAll('label[for=\"'+checkboxId+'\"]'):[];"
                        + "for(var i=0;i<labels.length;i++)if(vis(labels[i])){"
                        + "var rn=labels[i].getRootNode?labels[i].getRootNode():document;"
                        + "return rn.querySelector?rn.querySelector('#'+checkboxId):null;}"
                        + "var all=root.querySelectorAll?root.querySelectorAll('*'):[];"
                        + "for(var j=0;j<all.length;j++)if(all[j].shadowRoot){var f=walk(all[j].shadowRoot);if(f)return f;}"
                        + "return null;}"
                        + "var input=walk(document.body);return !!(input&&input.checked);";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, checkboxId);
        return Boolean.TRUE.equals(o);
    }
    public void clickReserveAppointment() {
        context.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(
                        d -> {
                            String script =
                                    "function find(root){if(!root)return null;var q=root.querySelector('button.m-button--primary');if(q&&!(q.disabled)&&((q.textContent||'').indexOf('Termin reservieren')>=0))return q;"
                                            + "var all=root.querySelectorAll('*');for(var i=0;i<all.length;i++){if(all[i].shadowRoot){var f=find(all[i].shadowRoot);if(f)return f;}}return null;}"
                                            + "var e=find(document.body);if(e){e.click();return true;}return false;";
                            return Boolean.TRUE.equals(((JavascriptExecutor) d).executeScript(script));
                        });
    }
    public void continueFromPreconfirmStep() {
        context.set();
        page.captureBookingProcessForCleanup();
        ScenarioLogManager.getLogger().info("zmscitizenview: preconfirm → Termin reservieren (activation callout)");
        shadow.waitForAndClickButtonContaining(DE_RESERVE, defaultWaitSeconds);
        CitizenViewWaits.waitWithThreeWindows(() -> shadow.shadowDomContainsText(ACTIVATION_CALLOUT_HEADING), "Activation callout");
        Assert.assertTrue(
                shadow.shadowDomContainsText(ACTIVATION_CALLOUT_HEADING),
                "Activation callout (Aktivieren Sie Ihren Termin.) not visible after Termin reservieren with retries.");
        ScenarioLogManager.getLogger().info("zmscitizenview: activation callout appeared");
        page.trySyncBookingProcessFromLocalStorageOnce();
    }
    public void assertPreconfirmationCalloutVisible(int activationMinutes) {
        context.set();
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: waiting in 5s + 10s + 15s windows (30s total) for activation callout (Aktivieren Sie Ihren Termin., {} Minuten)",
                        activationMinutes);
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText(ACTIVATION_CALLOUT_HEADING), "Preconfirmation callout heading");
        Assert.assertTrue(
                shadow.shadowDomContainsText(ACTIVATION_CALLOUT_HEADING),
                "Preconfirmation warning callout (Aktivieren Sie Ihren Termin.) not found after reserve with retries.");
        String timeText = activationMinutes + " Minuten";
        Assert.assertTrue(shadow.shadowDomContainsText(timeText),
                "Preconfirmation callout should mention activation time limit (" + timeText + ").");
        ScenarioLogManager.getLogger().info("zmscitizenview: activation callout visible with {} Minuten", activationMinutes);
    }
    public void assertConfirmationSuccessCalloutVisible() {
        ScenarioLogManager.getLogger().info("zmscitizenview: checking for confirmation success callout (Ihr Termin wurde gebucht.)");
        shadow.assertShadowContains(
                CONFIRMATION_SUCCESS_HEADING,
                "Confirmation success callout not found after opening confirm link.");
        ScenarioLogManager.getLogger().info("zmscitizenview: confirmation success callout found");
    }
    public void confirmLoggedInBookingFromSummary() {
        context.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: logged-in summary → confirm (Termin reservieren)");
        shadow.waitForAndClickButtonContaining(DE_RESERVE, defaultWaitSeconds);
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText(CONFIRMATION_SUCCESS_HEADING),
                "Logged-in confirmation success");
        Assert.assertTrue(
                shadow.shadowDomContainsText(CONFIRMATION_SUCCESS_HEADING),
                "Confirmation success callout (Ihr Termin wurde gebucht.) not visible after logged-in booking.");
        Assert.assertFalse(
                shadow.shadowDomContainsText(ACTIVATION_CALLOUT_HEADING),
                "Logged-in booking must not show the activation callout (Aktivieren Sie Ihren Termin.).");
        page.trySyncBookingProcessFromLocalStorageOnce();
    }
    public void assertLoggedInConfirmationSuccessDetailsVisible() {
        context.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert logged-in confirmation success details");
        assertConfirmationSuccessCalloutVisible();
        Assert.assertTrue(
                shadow.shadowDomContainsText(CONFIRMATION_SUCCESS_TEXT),
                "Confirmation success callout must include the booking-confirmation mail text.");
        Assert.assertTrue(
                shadow.shadowDomContainsText(VIEW_APPOINTMENT_BUTTON),
                "Logged-in confirmation must show primary action 'Termin ansehen'.");
        Assert.assertTrue(
                shadow.shadowDomContainsText(BOOK_ANOTHER_APPOINTMENT_BUTTON),
                "Logged-in confirmation must show secondary action 'Weiteren Termin vereinbaren'.");
    }

    /** Opens Termin-Detail for the appointment just confirmed (logged-in success callout). */
    public void openAppointmentFromConfirmationSuccess() {
        context.set();
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText(VIEW_APPOINTMENT_BUTTON),
                "Termin ansehen on confirmation success");
        shadow.waitForAndClickButtonContaining(VIEW_APPOINTMENT_BUTTON, defaultWaitSeconds);
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText("Termin absagen")
                        || shadow.shadowDomHasHeading(2, "Ort"),
                "Appointment detail after Termin ansehen");
    }
    public void confirmRebookingFromSummary() {
        context.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: rebooking summary → confirm (Termin verschieben)");
        shadow.waitForAndClickButtonContaining(RESCHEDULE_APPOINTMENT_BUTTON, defaultWaitSeconds);
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText(CONFIRMATION_SUCCESS_HEADING), "Rebooking confirmation success");
        Assert.assertTrue(
                shadow.shadowDomContainsText(CONFIRMATION_SUCCESS_HEADING),
                "Confirmation success callout (Ihr Termin wurde gebucht.) not visible after guest rebooking.");
        Assert.assertFalse(
                shadow.shadowDomContainsText(ACTIVATION_CALLOUT_HEADING),
                "Guest rebooking must not show the activation callout (Aktivieren Sie Ihren Termin.).");
        page.trySyncBookingProcessFromLocalStorageOnce();
    }
    public void assertPreconfirmationCalloutNotVisible() {
        context.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: asserting activation callout is hidden ({})", ACTIVATION_CALLOUT_HEADING);
        Assert.assertFalse(
                shadow.shadowDomContainsText(ACTIVATION_CALLOUT_HEADING),
                "Activation callout (Aktivieren Sie Ihren Termin.) must not be visible.");
    }
    public void assertAlreadyActivatedAppointmentBannerVisible() {
        context.set();
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: waiting for already-activated MucBanner success ({})",
                        ALREADY_ACTIVATED_BANNER_MARKER);
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText(ALREADY_ACTIVATED_BANNER_MARKER),
                "Already-activated appointment banner");
        Assert.assertTrue(
                shadow.shadowDomContainsText(ALREADY_ACTIVATED_BANNER_MARKER),
                "Already-activated MucBanner success not found after reopening confirm link.");
        ScenarioLogManager.getLogger().info("zmscitizenview: already-activated appointment banner found");
    }
    public void assertAlreadyActivatedAppointmentBannerNotVisible() {
        context.set();
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: asserting already-activated MucBanner is hidden ({})",
                        ALREADY_ACTIVATED_BANNER_MARKER);
        Assert.assertFalse(
                shadow.shadowDomContainsText(ALREADY_ACTIVATED_BANNER_MARKER),
                "Already-activated MucBanner must not remain visible while rescheduling from a confirm link.");
    }
    public void clickRescheduleAppointment() {
        context.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: clicking reschedule appointment button ({})", RESCHEDULE_APPOINTMENT_BUTTON);
        shadow.waitForAndClickButtonContaining(RESCHEDULE_APPOINTMENT_BUTTON, defaultWaitSeconds);
    }
    public void rescheduleFromMyAppointments() {
        context.set();
        clickRescheduleAppointment();
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText("Verschiebung Ihres Termins"),
                "Reschedule dialog");
        ScenarioLogManager.getLogger().info("zmscitizenview: confirm reschedule dialog (Verschieben)");
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> shadow.clickButtonWithExactText("Verschieben"));
    }
    public void assertCancelRescheduleButtonVisible() {
        context.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: waiting for cancel-reschedule button ({})", CANCEL_RESCHEDULE_BUTTON);
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText(CANCEL_RESCHEDULE_BUTTON), "Cancel reschedule button");
        Assert.assertTrue(
                shadow.shadowDomContainsText(CANCEL_RESCHEDULE_BUTTON),
                "Cancel reschedule button (Verschieben abbrechen) not found after rebooking slot selection.");
    }
    public void assertRescheduleOrCancelActionsVisible() {
        context.set();
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: waiting for reschedule and cancel actions ({} / Termin absagen)",
                        RESCHEDULE_APPOINTMENT_BUTTON);
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText(RESCHEDULE_APPOINTMENT_BUTTON)
                        && shadow.shadowDomContainsText("Termin absagen"),
                "Reschedule or cancel actions");
        Assert.assertTrue(
                shadow.shadowDomContainsText(RESCHEDULE_APPOINTMENT_BUTTON),
                "Termin verschieben not visible after returning from the Termin step.");
        Assert.assertTrue(
                shadow.shadowDomContainsText("Termin absagen"),
                "Termin absagen not visible after returning from the Termin step.");
    }
    public void clickCancelReschedule() {
        context.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: clicking cancel reschedule button ({})", CANCEL_RESCHEDULE_BUTTON);
        shadow.waitForAndClickButtonContaining(CANCEL_RESCHEDULE_BUTTON, defaultWaitSeconds);
    }
    public void clickCancelAppointmentAndConfirm() {
        context.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: clicking cancel appointment button (Termin absagen)");
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> shadow.clickButtonWithExactText("Termin absagen")
                        || shadow.clickButtonContaining("Termin absagen"));
        confirmCancelAppointmentDialogIfShown();
        String marker = CANCELLATION_SUCCESS_HEADING;
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: waiting in 5s + 10s + 15s windows (30s total) for cancellation success callout");
        CitizenViewWaits.waitWithThreeWindows(() -> shadow.shadowDomContainsText(marker), "Cancellation success callout");
        Assert.assertTrue(
                shadow.shadowDomContainsText(marker),
                "Cancellation success callout (Sie haben Ihren Termin erfolgreich abgesagt.) not visible after Termin absagen with retries.");
    }
    private void confirmCancelAppointmentDialogIfShown() {
        String heading = "Absage Ihres Termins";
        try {
            new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(5))
                    .until(d -> shadow.shadowDomContainsText(heading));
        } catch (TimeoutException e) {
            ScenarioLogManager.getLogger().info("zmscitizenview: cancel dialog was not shown");
            return;
        }
        ScenarioLogManager.getLogger().info("zmscitizenview: confirm cancel dialog (Absagen)");
        shadow.waitForAndClickButtonContaining("Absagen", defaultWaitSeconds);
    }
    public void assertCancellationSuccessCalloutVisible() {
        ScenarioLogManager.getLogger().info("zmscitizenview: checking for cancellation success callout (Sie haben Ihren Termin erfolgreich abgesagt.)");
        shadow.assertShadowContains(
                CANCELLATION_SUCCESS_HEADING,
                "Cancellation success callout not found after cancelling appointment.");
        ScenarioLogManager.getLogger().info("zmscitizenview: cancellation success callout found");
    }
    public void assertCancellationSuccessDetailsVisible() {
        context.set();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert cancellation success callout heading and thank-you text");
        assertCancellationSuccessCalloutVisible();
        Assert.assertTrue(
                shadow.shadowDomContainsText(CANCELLATION_SUCCESS_TEXT),
                "Cancellation success callout must include the thank-you text.");
    }
    public void continueFromContactFormToSummary() {
        context.set();
        page.clickWeiter(30);
        waitForPreconfirmPageAfterUpdate();
    }
    public void assertScheidplatzLocationOnSummary(int officeId) {
        assertProviderSummaryVisible(officeId, "Bürgerbüro Scheidplatz");
        String summaryText = deepVisibleProviderSummaryText(officeId);
        Assert.assertNotNull(summaryText, "Expected visible Scheidplatz summary for provider-" + officeId);
        Assert.assertTrue(
                summaryText.contains("Belgradstraße") || summaryText.contains("Riesenfeldstraße"),
                "Ort address for Scheidplatz should show Belgradstraße or Riesenfeldstraße in visible summary. actual="
                        + summaryText);
        Assert.assertTrue(
                summaryText.contains("80804") && summaryText.contains("München"),
                "Ort address for Scheidplatz should show postal code/city in visible summary. actual="
                        + summaryText);
    }
    public void goBackFromBookingSummaryToContact() {
        context.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: Zurück from booking summary to Kontakt");
        shadow.waitForAndClickButtonContaining("Zurück", defaultWaitSeconds);
        shadow.waitUntilShadowContains("Kontaktdaten", Math.max(30, defaultWaitSeconds));
    }
    public void reloadReservedAppointmentHash() {
        context.set();
        page.trySetBookingProcessFromPage();
        String url = resolveReservedAppointmentHashUrl();
        String current = DriverUtil.getDriver().getCurrentUrl();
        boolean alreadyOnReservedHash = current != null
                && current.contains("#/appointment/")
                && !current.contains("#/appointment/confirm/");
        ScenarioLogManager.getLogger().info("zmscitizenview: reload reserved appointment hash {}", url);
        try {
            if (!alreadyOnReservedHash) {
                DriverUtil.getDriver().navigate().to(url);
            }
            DriverUtil.getDriver().navigate().refresh();
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn("Reload reserved appointment hash", e);
        }
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText("Kontaktdaten")
                        || shadow.deepElementExists("#checkbox-electronic-communication")
                        || shadow.shadowDomContainsText("Sie sind angemeldet"),
                "Reserved hash resume after reload");
    }
    public void assertAppointmentManagementActionsNotVisible() {
        context.set();
        Assert.assertFalse(
                shadow.shadowDomContainsText(RESCHEDULE_APPOINTMENT_BUTTON),
                "Reserved hash resume must not show Termin verschieben (confirmed-appointment management).");
        Assert.assertFalse(
                shadow.shadowDomContainsText(CANCEL_RESCHEDULE_BUTTON),
                "Reserved hash resume must not show Verschieben abbrechen (rebooking).");
    }

    public void assertRescheduleAppointmentButtonVisible() {
        context.set();
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.visibleButtonContaining(RESCHEDULE_APPOINTMENT_BUTTON),
                "Termin verschieben button");
        Assert.assertTrue(
                shadow.visibleButtonContaining(RESCHEDULE_APPOINTMENT_BUTTON),
                "Termin verschieben must be visible when scope.rebookingDisabled is false.");
    }

    /** ZMSKVR-1620 / ZMSKVR-1691: Umbuchung deaktiviert — Termin verschieben hidden, Termin absagen stays. */
    public void assertRescheduleAppointmentButtonNotVisible() {
        context.set();
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText("Termin absagen"),
                "Cancel appointment button with rebooking disabled");
        Assert.assertFalse(
                shadow.visibleButtonContaining(RESCHEDULE_APPOINTMENT_BUTTON),
                "Termin verschieben must stay hidden when scope.rebookingDisabled is true.");
    }

    public void assertCancelAppointmentButtonVisible() {
        context.set();
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.shadowDomContainsText("Termin absagen"), "Cancel appointment button");
        Assert.assertTrue(
                shadow.shadowDomContainsText("Termin absagen"),
                "Termin absagen must remain visible when rebooking is disabled.");
    }
    public void assertElectronicCommunicationCheckboxVisible() {
        context.set();
        CitizenViewWaits.waitWithThreeWindows(
                () -> shadow.deepElementExists("#checkbox-electronic-communication"),
                "Electronic communication checkbox on book overview");
        Assert.assertTrue(
                shadow.deepElementExists("#checkbox-electronic-communication"),
                "Expected #checkbox-electronic-communication on the book/overview after reserved hash resume.");
    }
    private String resolveReservedAppointmentHashUrl() {
        String current = DriverUtil.getDriver().getCurrentUrl();
        if (current != null) {
            int hashIdx = current.indexOf("#/appointment/");
            if (hashIdx >= 0 && !current.contains("#/appointment/confirm/")) {
                return current;
            }
        }
        ThinnedProcess process = zms.ataf.rest.steps.CitizenApiSteps.getBookingProcess();
        Assert.assertNotNull(process, "No booking process for reserved hash; login or reserve first.");
        Assert.assertNotNull(process.getProcessId(), "Booking process has no processId for reserved hash.");
        Assert.assertNotNull(process.getAuthKey(), "Booking process has no authKey for reserved hash.");
        String payload =
                "{\"id\":"
                        + process.getProcessId()
                        + ",\"authKey\":"
                        + ShadowDom.mapperQuote(process.getAuthKey())
                        + "}";
        String b64 = Base64.getEncoder().encodeToString(payload.getBytes(StandardCharsets.UTF_8));
        String base = context.lastCitizenViewUrl != null ? context.lastCitizenViewUrl : "";
        int hashIdx = base.indexOf('#');
        if (hashIdx >= 0) {
            base = base.substring(0, hashIdx);
        }
        if (current != null && (base == null || base.isBlank())) {
            int currentHash = current.indexOf('#');
            base = currentHash >= 0 ? current.substring(0, currentHash) : current;
        }
        return MyAppointmentsStep.ensureAbsoluteCitizenViewUrl(base + "#/appointment/" + b64);
    }
}
