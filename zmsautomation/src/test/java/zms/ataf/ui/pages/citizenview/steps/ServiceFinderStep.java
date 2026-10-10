package zms.ataf.ui.pages.citizenview.steps;

import java.time.Duration;
import java.util.Locale;

import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.Keys;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.interactions.Actions;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.utils.DriverUtil;
import zms.ataf.ui.pages.citizenview.CitizenViewPageContext;
import zms.ataf.ui.pages.citizenview.support.CitizenViewWaits;
import zms.ataf.ui.pages.citizenview.support.ShadowDom;

/**
 * Service Finder step: search field, suggestions, and Häufig gesuchte Leistungen.
 */
public final class ServiceFinderStep {

    private static final String[] SERVICE_SUGGESTIONS = {
        "Wohnsitzanmeldung",
        "Reisepass",
        "Personalausweis",
        "Ausweis-Abholung",
        "Führerschein-Abholung",
        "eID-PIN",
        "Kfz-Ummeldung",
        "Kfz-Abmeldung"
    };


    private final CitizenViewPageContext context;
    private final ShadowDom shadow;
    private final CombinationStep combination;
    private final int defaultWaitSeconds;

    public ServiceFinderStep(
            CitizenViewPageContext context,
            ShadowDom shadow,
            CombinationStep combination,
            int defaultWaitSeconds) {
        this.context = context;
        this.shadow = shadow;
        this.combination = combination;
        this.defaultWaitSeconds = defaultWaitSeconds;
    }

    public void assertServiceFinderHeadingVisible() {
        context.set();
        ScenarioLogManager.getLogger().info("Checking that the zmscitizenview Service Finder is visible on the start page.");

        shadow.waitUntilDeepElementExists("zms-appointment-i18n-host", defaultWaitSeconds);
        Assert.assertTrue(
                shadow.deepElementExists("zms-appointment-i18n-host"),
                "Root element <zms-appointment-i18n-host> is not visible on the zmscitizenview start page.");

        RemoteWebDriver driver = DriverUtil.getDriver();
        String script =
                "function walk(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=walk(n.shadowRoot);"
                        + "var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=walk(c[i]);return s;}"
                        + "var t=walk(document.body);"
                        + "return t.indexOf('Leistung')>=0&&t.indexOf('Bürgerservice-Suche')>=0"
                        + "&&t.indexOf('Häufig gesuchte Leistungen')>=0;";

        Boolean textsVisible =
                new WebDriverWait(driver, Duration.ofSeconds(defaultWaitSeconds))
                        .until(
                                d ->
                                        Boolean.TRUE.equals(
                                                ((JavascriptExecutor) d).executeScript(script)));
        Assert.assertTrue(
                textsVisible,
                "Service Finder copy (Leistung / Bürgerservice-Suche / Häufig gesuchte Leistungen) not found"
                        + " in page+shadow DOM within timeout.");
        ScenarioLogManager.getLogger().info("Service Finder is visible on the start page.");
    }

    /**
     * ZMSKVR-1305 / ZMSKVR-1316: Leistung wechseln clears the jump-in hash and reloads the start
     * page (path only, no {@code #/services/...}).
     */
    public void clickChangeService() {
        context.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: click Leistung wechseln");
        Assert.assertTrue(
                shadow.clickButtonContaining("Leistung wechseln"),
                "Could not click \"Leistung wechseln\".");
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(
                        d -> {
                            String url = d.getCurrentUrl();
                            return url != null && !url.contains("#/services/");
                        });
    }

    /** ZMSKVR-84: the start page search box and the frequently requested service links. */
    public void assertServiceSearchAndSuggestions() {
        context.set();
        JsonNode state = serviceSearch("links", "");
        Assert.assertTrue(state.path("hasField").asBoolean(), "The service search field is not on the start page.");
        JsonNode links = state.path("links");
        Assert.assertEquals(
                links.size(),
                SERVICE_SUGGESTIONS.length,
                "Suggestion links were " + links);
        for (int i = 0; i < SERVICE_SUGGESTIONS.length; i++) {
            Assert.assertEquals(links.get(i).asText(), SERVICE_SUGGESTIONS[i], "Suggestion links were " + links);
        }
    }

    public void reloadCitizenView() {
        context.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: reload the booking page");
        DriverUtil.getDriver().navigate().refresh();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> shadow.shadowDomContainsText("Bürgerservice-Suche"));
    }

    /** Click the search field. The list opens underneath it. */
    public void clickServiceSearchField() {
        context.set();
        waitUntilServiceOptionsLoaded();
        serviceSearch("click", "");
        waitUntilServiceListOpen();
    }

    /**
     * From the Leistung heading, Tab lands on the search field. Enter opens the list.
     * Enter on an already-open list selects a row, so the list must be closed first.
     */
    public void openServiceListWithTabAndEnter() {
        context.set();
        waitUntilServiceOptionsLoaded();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> {
                    if (!serviceSearch("read", "").path("open").asBoolean()) {
                        return true;
                    }
                    serviceSearch("close", "");
                    return !serviceSearch("read", "").path("open").asBoolean();
                });
        serviceSearch("focus-heading", "");
        Actions actions = new Actions(DriverUtil.getDriver());
        boolean focused = false;
        for (int i = 0; i < 8; i++) {
            if (serviceSearch("focused", "").path("focused").asBoolean()) {
                focused = true;
                break;
            }
            actions.sendKeys(Keys.TAB).perform();
            CitizenViewWaits.sleepQuiet(150L);
        }
        Assert.assertTrue(focused, "Tab did not reach the service search field.");
        actions.sendKeys(Keys.ENTER).perform();
        if (!serviceSearch("read", "").path("open").asBoolean()) {
            // Enter sometimes lands before Choices is ready; open with a click instead.
            serviceSearch("close", "");
            serviceSearch("click", "");
        }
        waitUntilServiceListOpen();
    }

    public void assertServiceListOpenUnderField() {
        context.set();
        // The dropdown closes when the step ends. Open it again, then check that it sits under the field.
        waitUntilServiceOptionsLoaded();
        if (!serviceSearch("read", "").path("open").asBoolean()) {
            serviceSearch("click", "");
        }
        JsonNode state = waitUntilServiceListOpen();
        Assert.assertTrue(
                state.path("under").asBoolean(),
                "The service list should open under the search field: " + state);
    }

    public void assertServiceListAlphabetical() {
        context.set();
        JsonNode state = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> {
                    JsonNode node = serviceSearch("read", "");
                    if (node.path("names").size() > 1) {
                        return node;
                    }
                    // An open list can still show the empty Choices notice until the services arrive.
                    if (serviceSearch("options", "").path("count").asInt() > 1) {
                        serviceSearch("close", "");
                        serviceSearch("click", "");
                        node = serviceSearch("read", "");
                        if (node.path("names").size() > 1) {
                            return node;
                        }
                    }
                    return null;
                });
        Assert.assertTrue(
                state.path("alphabetical").asBoolean(),
                "The service list is not alphabetical: " + state.path("names"));
    }

    public void typeIntoServiceSearch(String query) {
        context.set();
        waitForFilteredServiceNames(query);
    }

    public void assertServiceListContainsOnly(String query) {
        context.set();
        JsonNode names = waitForFilteredServiceNames(query).path("names");
        Assert.assertTrue(names.size() > 0, "The service list is empty for \"" + query + "\".");
        String folded = query.toLowerCase(Locale.ROOT);
        for (JsonNode name : names) {
            Assert.assertTrue(
                    name.asText().toLowerCase(Locale.ROOT).contains(folded),
                    "\"" + name.asText() + "\" does not contain \"" + query + "\". List: " + names);
        }
    }

    public void assertServiceListIncludesAndNot(String present, String absent) {
        context.set();
        JsonNode names = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> {
                    JsonNode list = currentServiceListNames();
                    if (list == null || list.size() == 0) {
                        return null;
                    }
                    boolean found = false;
                    for (JsonNode name : list) {
                        if (name.asText().equals(absent)) {
                            return null;
                        }
                        if (name.asText().equals(present)) {
                            found = true;
                        }
                    }
                    return found ? list : null;
                });
        boolean found = false;
        for (JsonNode name : names) {
            String text = name.asText();
            Assert.assertNotEquals(text, absent, "\"" + absent + "\" is still in the service list: " + names);
            if (text.equals(present)) {
                found = true;
            }
        }
        Assert.assertTrue(found, "\"" + present + "\" is not in the service list: " + names);
    }

    /** Choose a row in the open list. That opens the Leistung step for the service. */
    public void chooseServiceFromOpenList(String label) {
        context.set();
        JsonNode state = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> {
                    currentServiceListNames();
                    JsonNode chosen = serviceSearch("choose", label);
                    return chosen.path("chosen").asBoolean() ? chosen : null;
                });
        Assert.assertTrue(
                state.path("chosen").asBoolean(),
                "Could not choose \"" + label + "\" from the service list.");
        combination.assertCombinationStepVisible();
    }

    /** Full entry: select service via \"Häufig gesuchte Leistungen\" link and navigate to combination step. */
    public void selectServiceByLabel(String serviceLabel) {
        context.set();
        ScenarioLogManager.getLogger().info("Service Finder: searching for and clicking service '{}'", serviceLabel);
        // Ensure the Service Finder step is visible first (same heuristic as assertServiceFinderHeadingVisible).
        shadow.waitUntilShadowContains("Bürgerservice-Suche", defaultWaitSeconds);
        // Wait until the desired service label is present in API-backed UI (e.g. select options),
        // not just in the static quick-link list. This ensures offices-and-services have loaded.
        ScenarioLogManager.getLogger()
                .info("Service Finder: waiting for label '{}' to be ready in API-backed UI (up to 20s)", serviceLabel);
        waitUntilServiceLabelReadyForSelection(serviceLabel, 20);
        // Click only the \"Häufig gesuchte Leistungen\" quick link (not the search dropdown).
        // Simulate a full user click: focus, pointer events, then click (so Vue @click fires).
        String esc = serviceLabel.replace("\\", "\\\\").replace("'", "\\'");
        String js =
                "var label='" + esc + "';"
                        + "function matchText(t){"
                        + "  if(!t)return false;"
                        + "  var s=String(t).replace(/\\s+/g,' ').trim();"
                        + "  return s===label || s.indexOf(label)>=0;"
                        + "}"
                        + "function findQuickLinkInRoot(root){"
                        + "  if(!root)return null;"
                        + "  var lists=root.querySelectorAll('.m-linklist-inline__list');"
                        + "  for(var i=0;i<lists.length;i++){"
                        + "    var as=lists[i].querySelectorAll('a');"
                        + "    for(var j=0;j<as.length;j++){"
                        + "      var el=as[j];"
                        + "      var t=(el.textContent||'');"
                        + "      if(matchText(t))return el;"
                        + "    }"
                        + "  }"
                        + "  return null;"
                        + "}"
                        + "function findQuickLinkDeep(root){"
                        + "  if(!root)return null;"
                        + "  var link=findQuickLinkInRoot(root);"
                        + "  if(link)return link;"
                        + "  var all=root.querySelectorAll('*');"
                        + "  for(var k=0;k<all.length;k++){"
                        + "    if(all[k].shadowRoot){"
                        + "      var r=findQuickLinkDeep(all[k].shadowRoot);"
                        + "      if(r)return r;"
                        + "    }"
                        + "  }"
                        + "  return null;"
                        + "}"
                        + "var link=findQuickLinkDeep(document.documentElement)||findQuickLinkDeep(document.body);"
                        + "if(link){"
                        + "  link.scrollIntoView({block:'center'});"
                        + "  link.focus();"
                        + "  try{"
                        + "    link.style.outline='4px solid #ffbf00';"
                        + "    link.style.outlineOffset='3px';"
                        + "    link.style.backgroundColor='rgba(255,191,0,0.25)';"
                        + "  }catch(e){}"
                        + "  var r=link.getBoundingClientRect();"
                        + "  var x=r.left+r.width/2; var y=r.top+r.height/2;"
                        + "  var opts={bubbles:true,cancelable:true,view:window,clientX:x,clientY:y};"
                        + "  link.dispatchEvent(new MouseEvent('mousedown',opts));"
                        + "  link.dispatchEvent(new MouseEvent('mouseup',opts));"
                        + "  link.dispatchEvent(new MouseEvent('click',opts));"
                        + "  return true;"
                        + "}return false;";
        Object clicked = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(js);
        if (Boolean.TRUE.equals(clicked)) {
            ScenarioLogManager.getLogger().info("Service Finder: found and clicked link for '{}'", serviceLabel);
        } else {
            ScenarioLogManager.getLogger().warn("Service Finder: did not find or click link for '{}'", serviceLabel);
        }
        Assert.assertTrue(
                Boolean.TRUE.equals(clicked),
                "Service Finder: could not find or click link for service '" + serviceLabel + "'");
        // Clicking a service link auto-advances to the combination (Ort/Zeit) step; no Weiter on this page.
        try {
            Thread.sleep(2000L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
        try {
            combination.assertCombinationStepVisible();
        } catch (TimeoutException first) {
            // In rare cases the first click may race with offices-and-services loading.
            // Retry once if the combination heading did not appear yet.
            ScenarioLogManager.getLogger()
                    .warn("Service Finder: combination step did not appear after first click on '{}', retrying once",
                            serviceLabel);
            try {
                Thread.sleep(1000L);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
            }
            Boolean retried =
                    (Boolean)
                            ((JavascriptExecutor) DriverUtil.getDriver())
                                    .executeScript(js);
            ScenarioLogManager.getLogger()
                    .info("Service Finder: retry click on '{}' success={}", serviceLabel, retried);
            combination.assertCombinationStepVisible();
        }
    }

    public WebElement serviceSearchInput() {
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver())
                .executeScript(
                        "function walk(n){if(!n)return null;"
                                + "if(n.querySelector){var f=n.querySelector('.choices__input--cloned');if(f)return f;}"
                                + "if(n.shadowRoot){var s=walk(n.shadowRoot);if(s)return s;}"
                                + "var ch=n.children;if(ch)for(var i=0;i<ch.length;i++){var r=walk(ch[i]);if(r)return r;}"
                                + "return null;}return walk(document.body);");
        return raw instanceof WebElement ? (WebElement) raw : null;
    }

    public JsonNode waitForFilteredServiceNames(String query) {
        String folded = query.toLowerCase(Locale.ROOT);
        // Choices filter (esp. Edge / short queries like "z") can lag under shard load.
        int waitSeconds = Math.max(defaultWaitSeconds, 90);
        final long[] lastTypeMs = {0L};
        typeServiceSearchQuery(query, true);
        lastTypeMs[0] = System.currentTimeMillis();
        return new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(waitSeconds))
                .until(d -> {
                    if (!serviceSearch("read", "").path("open").asBoolean()) {
                        serviceSearch("click", "");
                        return null;
                    }
                    JsonNode read = serviceSearch("read", "");
                    JsonNode names = read.path("names");
                    boolean allMatch = names.size() > 0;
                    for (JsonNode name : names) {
                        if (!name.asText().toLowerCase(Locale.ROOT).contains(folded)) {
                            allMatch = false;
                            break;
                        }
                    }
                    if (allMatch) {
                        return read;
                    }
                    // Retype at most every 2s — retyping every poll resets Choices debounce forever.
                    long now = System.currentTimeMillis();
                    if (now - lastTypeMs[0] >= 2000L) {
                        typeServiceSearchQuery(query, true);
                        lastTypeMs[0] = now;
                    }
                    return null;
                });
    }

    /** Reopen the list if AfterStep closed it, then type {@code query} via Choices JS {@code type}. */
    public boolean applyServiceSearchQuery(String query) {
        if (!serviceSearch("read", "").path("open").asBoolean()) {
            serviceSearch("click", "");
        }
        WebElement field = serviceSearchInput();
        if (field == null && !serviceSearch("options", "").path("choicesReady").asBoolean()) {
            return false;
        }
        try {
            String current = field == null ? null : field.getAttribute("value");
            if (!query.equals(current)) {
                typeServiceSearchQuery(query, false);
            }
            return true;
        } catch (Exception e) {
            typeServiceSearchQuery(query, true);
            return true;
        }
    }

    /**
     * Prefer the in-page Choices {@code type} mode (sets value + InputEvent in the shadow tree).
     * Selenium sendKeys alone is flaky for short queries under Chromium/Edge shard load.
     */
    private void typeServiceSearchQuery(String query, boolean forceRetype) {
        if (!forceRetype) {
            WebElement field = serviceSearchInput();
            try {
                if (field != null && query.equals(field.getAttribute("value"))) {
                    return;
                }
            } catch (Exception ignored) {
                // fall through to type
            }
        }
        if (!serviceSearch("read", "").path("open").asBoolean()) {
            serviceSearch("click", "");
        }
        serviceSearch("type", query);
        CitizenViewWaits.sleepQuiet(450L);
    }

    public JsonNode currentServiceListNames() {
        WebElement field = serviceSearchInput();
        String typed = null;
        try {
            typed = field == null ? null : field.getAttribute("value");
        } catch (Exception e) {
            return null;
        }
        if (typed != null && !typed.isBlank()) {
            if (!applyServiceSearchQuery(typed)) {
                return null;
            }
        } else if (!serviceSearch("read", "").path("open").asBoolean()) {
            serviceSearch("click", "");
        }
        JsonNode read = serviceSearch("read", "");
        return read.path("open").asBoolean() ? read.path("names") : null;
    }

    /** The search field stays empty until offices-and-services fills its options. */
    public void waitUntilServiceOptionsLoaded() {
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(
                        d -> {
                            JsonNode options = serviceSearch("options", "");
                            return options.path("count").asInt() > 1
                                    && options.path("choicesReady").asBoolean();
                        });
    }

    /**
     * After reload / tab+enter, Choices often needs more than one click before the dropdown
     * shows real service names (open:true with an empty list is still a miss). Return the
     * successful read from the wait — a second read can see a closed list a tick later.
     */
    public JsonNode waitUntilServiceListOpen() {
        final long[] lastOpenAttemptMs = {0L};
        final int[] attempt = {0};
        JsonNode opened =
                new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(Math.max(defaultWaitSeconds, 30)))
                        .until(
                                d -> {
                                    JsonNode state = serviceSearch("read", "");
                                    if (serviceListShowsNames(state)) {
                                        return state;
                                    }
                                    long now = System.currentTimeMillis();
                                    if (now - lastOpenAttemptMs[0] < 700L) {
                                        return null;
                                    }
                                    lastOpenAttemptMs[0] = now;
                                    attempt[0]++;
                                    // Half-open or stale dropdown: close, then open with a fuller click sequence.
                                    if (state.path("open").asBoolean() || attempt[0] % 3 == 0) {
                                        serviceSearch("close", "");
                                        CitizenViewWaits.sleepQuiet(100L);
                                    }
                                    serviceSearch("click", "");
                                    CitizenViewWaits.sleepQuiet(200L);
                                    state = serviceSearch("read", "");
                                    return serviceListShowsNames(state) ? state : null;
                                });
        Assert.assertTrue(
                serviceListShowsNames(opened),
                "The service list did not open: " + opened);
        return opened;
    }

    private static boolean serviceListShowsNames(JsonNode state) {
        return state != null
                && state.path("open").asBoolean()
                && state.path("names").size() > 0;
    }

    public JsonNode serviceSearch(String mode, String text) {
        String script =
                "var mode=arguments[0];var text=arguments[1]||'';"
                        + "function norm(t){return (t||'').replace(/\\s+/g,' ').trim();}"
                        + "function walk(n,fn){if(!n)return;fn(n);if(n.shadowRoot)walk(n.shadowRoot,fn);"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)walk(c[i],fn);}"
                        + "function up(n){if(!n)return null;if(n.parentElement)return n.parentElement;"
                        + "var root=n.getRootNode&&n.getRootNode();return root&&root.host?root.host:null;}"
                        + "function findSelect(){var found=null;walk(document.body,function(n){"
                        + "if(!found&&(n.id||'')==='select-service-search')found=n;});return found;}"
                        + "function choicesOf(select){var n=select;while(n){"
                        + "if(n.classList&&n.classList.contains('choices'))return n;n=up(n);}return null;}"
                        + "function dropdown(choices){return choices?choices.querySelector('.choices__list--dropdown'):null;}"
                        + "function isOpen(choices){var d=dropdown(choices);return !!(d&&(d.getAttribute('aria-expanded')==='true'"
                        + "||d.classList.contains('is-active')));}"
                        + "function shown(el){if(!el||el.hidden)return false;var st=window.getComputedStyle(el);"
                        + "return st.display!=='none'&&st.visibility!=='hidden'&&st.opacity!=='0';}"
                        + "function placeholder(name){return name==='Keine Leistung gefunden'||name==='No service found'"
                        + "||name==='No choices to choose from'||name==='No results found'"
                        + "||name==='Leistung auswählen'||name==='Enter search term';}"
                        + "function namesOf(choices){var d=dropdown(choices);var names=[];if(!d)return names;"
                        + "var items=d.querySelectorAll('.choices__item--choice');"
                        + "for(var i=0;i<items.length;i++){var name=norm(items[i].textContent);"
                        + "if(!shown(items[i])||!name||placeholder(name))continue;names.push(name);}return names;}"
                        + "function optionCount(select){var n=0;if(!select)return n;var opts=select.querySelectorAll('option');"
                        + "for(var i=0;i<opts.length;i++){var name=norm(opts[i].textContent);if(name&&!placeholder(name))n++;}return n;}"
                        + "function deepActive(){var el=document.activeElement,guard=0;"
                        + "while(el&&el.shadowRoot&&el.shadowRoot.activeElement&&guard++<10)el=el.shadowRoot.activeElement;return el;}"
                        + "var select=findSelect();var choices=choicesOf(select);"
                        + "function pointerClick(el){if(!el)return;"
                        + "el.scrollIntoView({block:'center'});"
                        + "var r=el.getBoundingClientRect();var x=r.left+Math.max(r.width,1)/2;var y=r.top+Math.max(r.height,1)/2;"
                        + "var opts={bubbles:true,cancelable:true,view:window,clientX:x,clientY:y,button:0};"
                        + "el.dispatchEvent(new MouseEvent('pointerdown',opts));"
                        + "el.dispatchEvent(new MouseEvent('mousedown',opts));"
                        + "el.dispatchEvent(new MouseEvent('pointerup',opts));"
                        + "el.dispatchEvent(new MouseEvent('mouseup',opts));"
                        + "el.dispatchEvent(new MouseEvent('click',opts));}"
                        + "if(mode==='links'){var links=[];walk(document.body,function(n){"
                        + "if(!n.classList||!n.classList.contains('m-linklist-inline__list'))return;"
                        + "var as=n.querySelectorAll('a');for(var i=0;i<as.length;i++)links.push(norm(as[i].textContent));});"
                        + "return JSON.stringify({hasField:!!select,links:links});}"
                        + "if(mode==='options'){var ready=!!(select&&choicesOf(select)"
                        + "&&choicesOf(select).querySelector('.choices__inner')&&optionCount(select)>1);"
                        + "return JSON.stringify({count:optionCount(select),choicesReady:ready});}"
                        + "if(!choices)return JSON.stringify({open:false,hasField:false});"
                        + "if(mode==='click'){var inner=choices.querySelector('.choices__inner');"
                        + "var input=choices.querySelector('.choices__input--cloned');"
                        + "pointerClick(inner);"
                        + "if(!isOpen(choices)&&input){pointerClick(input);try{input.focus();}catch(e){}}"
                        + "if(!isOpen(choices)&&inner){try{inner.click();}catch(e2){}}"
                        + "return JSON.stringify({open:isOpen(choices)});}"
                        + "if(mode==='close'){var inputClose=choices.querySelector('.choices__input--cloned');"
                        + "if(inputClose){inputClose.focus();"
                        + "inputClose.dispatchEvent(new KeyboardEvent('keydown',{key:'Escape',code:'Escape',keyCode:27,which:27,bubbles:true,cancelable:true}));}"
                        + "if(isOpen(choices)){document.body.click();}"
                        + "if(isOpen(choices)){var inner2=choices.querySelector('.choices__inner');if(inner2)inner2.click();}"
                        + "return JSON.stringify({open:isOpen(choices)});}"
                        + "if(mode==='focus-heading'){var heading=null;walk(document.body,function(n){"
                        + "if(!heading&&(n.tagName||'').toUpperCase()==='H2'&&norm(n.textContent)==='Leistung')heading=n;});"
                        + "if(heading){heading.setAttribute('tabindex','-1');heading.focus();}"
                        + "return JSON.stringify({focused:!!heading});}"
                        + "if(mode==='focused'){var el=deepActive(),inside=false,n=el;"
                        + "while(n){if(n===choices){inside=true;break;}n=up(n);}"
                        + "return JSON.stringify({focused:inside});}"
                        + "if(mode==='type'){if(!isOpen(choices)){var openInner=choices.querySelector('.choices__inner');"
                        + "if(openInner){openInner.scrollIntoView({block:'center'});openInner.click();}}"
                        + "var field=choices.querySelector('.choices__input--cloned');"
                        + "if(field){field.focus();field.value='';"
                        + "field.dispatchEvent(new Event('input',{bubbles:true}));field.value=text;"
                        + "try{field.dispatchEvent(new InputEvent('input',{bubbles:true,data:text,inputType:'insertText'}));}"
                        + "catch(e){field.dispatchEvent(new Event('input',{bubbles:true}));}"
                        + "var last=text.slice(-1)||' ';field.dispatchEvent(new KeyboardEvent('keyup',"
                        + "{key:last,keyCode:last.charCodeAt(0),which:last.charCodeAt(0),bubbles:true}));}}"
                        + "if(mode==='choose'){var picked=false;var d=dropdown(choices);"
                        + "var items=d?d.querySelectorAll('.choices__item--choice'):[];"
                        + "for(var i=0;i<items.length;i++){if(shown(items[i])&&norm(items[i].textContent)===norm(text)){"
                        + "var el=items[i].matches&&items[i].matches('[data-choice]')?items[i]"
                        + ":(items[i].querySelector('[data-choice]')||items[i]);"
                        + "var r=el.getBoundingClientRect();var x=r.left+Math.max(r.width,1)/2;var y=r.top+Math.max(r.height,1)/2;"
                        + "var opts={bubbles:true,cancelable:true,view:window,clientX:x,clientY:y,button:0};"
                        + "el.dispatchEvent(new MouseEvent('mousedown',opts));"
                        + "el.dispatchEvent(new MouseEvent('mouseup',opts));"
                        + "el.dispatchEvent(new MouseEvent('click',opts));"
                        + "picked=true;break;}}"
                        + "return JSON.stringify({chosen:picked});}"
                        + "var list=namesOf(choices);"
                        + "var sorted=list.slice().sort(function(a,b){return a.localeCompare(b,undefined,"
                        + "{sensitivity:'base',ignorePunctuation:true,numeric:true});});"
                        + "var innerBox=choices.querySelector('.choices__inner');var drop=dropdown(choices);"
                        + "var under=false;if(innerBox&&drop){var ir=innerBox.getBoundingClientRect();"
                        + "var dr=drop.getBoundingClientRect();under=dr.top>=ir.bottom-12;}"
                        + "return JSON.stringify({open:isOpen(choices),under:under,names:list,"
                        + "alphabetical:JSON.stringify(list)===JSON.stringify(sorted)});";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, mode, text);
        try {
            return new ObjectMapper().readTree(raw == null ? "{}" : String.valueOf(raw));
        } catch (Exception e) {
            throw new AssertionError("Could not read the service search: " + raw, e);
        }
    }

    /**
     * True once the given service label appears somewhere in the DOM/shadow DOM
     * <em>outside</em> the static "Häufig gesuchte Leistungen" quick-link list.
     * This is a proxy for "offices-and-services have loaded and the label is
     * available in API-backed UI (e.g. select options)".
     */
    public boolean serviceLabelReadyForSelection(String serviceLabel) {
        context.set();
        String esc = serviceLabel.replace("\\", "\\\\").replace("'", "\\'");
        String script =
                "var label='" + esc + "';"
                        + "function norm(t){return (t||'').replace(/\\s+/g,' ').trim();}"
                        + "function insideQuick(el){"
                        + "  while(el){"
                        + "    if(el.classList&&el.classList.contains('m-linklist-inline__list'))return true;"
                        + "    var root=el.getRootNode&&el.getRootNode();"
                        + "    if(root&&root.host){el=root.host;}else{el=el.parentNode;}"
                        + "  }"
                        + "  return false;"
                        + "}"
                        + "function has(root){"
                        + "  if(!root)return false;"
                        + "  var all=root.querySelectorAll('*');"
                        + "  for(var i=0;i<all.length;i++){"
                        + "    var el=all[i];"
                        + "    if(insideQuick(el))continue;"
                        + "    if(el.querySelector&&el.querySelector('.m-linklist-inline__list'))continue;"
                        + "    var txt=norm(el.textContent);"
                        + "    if(txt&&txt.indexOf(label)>=0)return true;"
                        + "    if(el.shadowRoot&&has(el.shadowRoot))return true;"
                        + "  }"
                        + "  return false;"
                        + "}"
                        + "return has(document.body);";
        Object o = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        return Boolean.TRUE.equals(o);
    }

    public void waitUntilServiceLabelReadyForSelection(String serviceLabel, int seconds) {
        context.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(seconds))
                .until(d -> serviceLabelReadyForSelection(serviceLabel));
    }

}
