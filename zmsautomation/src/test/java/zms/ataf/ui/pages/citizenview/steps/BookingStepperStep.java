package zms.ataf.ui.pages.citizenview.steps;

import java.time.Duration;

import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import com.fasterxml.jackson.databind.JsonNode;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.utils.DriverUtil;
import zms.ataf.ui.pages.citizenview.CitizenViewPageContext;
import zms.ataf.ui.pages.citizenview.support.ShadowDom;
import com.fasterxml.jackson.databind.ObjectMapper;

/** Booking muc-stepper navigation and labels. */
public final class BookingStepperStep {
    private final CitizenViewPageContext context;
    private final ShadowDom shadow;
    private final int defaultWaitSeconds;

    public BookingStepperStep(CitizenViewPageContext context, ShadowDom shadow, int defaultWaitSeconds) {
        this.context = context; this.shadow = shadow; this.defaultWaitSeconds = defaultWaitSeconds;
    }

    /**
     * ZMSKVR-92 / ZMSKVR-164. A finished step keeps its own icon and is the only clickable one
     * ({@code Zurück zu Schritt}). The active step is {@code aria-current=step}. A later step is not a button.
     */
    public void assertBookingStepperLabels() {
        context.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> "Leistung".equals(bookingStepLabel(0))
                        && "Termin".equals(bookingStepLabel(1))
                        && "Kontakt".equals(bookingStepLabel(2))
                        && "Übersicht".equals(bookingStepLabel(3)));
        Assert.assertEquals(bookingStepLabel(0), "Leistung", "First booking step.");
        Assert.assertEquals(bookingStepLabel(1), "Termin", "Second booking step.");
        Assert.assertEquals(bookingStepLabel(2), "Kontakt", "Third booking step.");
        Assert.assertEquals(bookingStepLabel(3), "Übersicht", "Fourth booking step.");
    }

    public void assertBookingStep(String label, String state, String icon) {
        context.set();
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> bookingStepMatches(label, state, icon));
        Assert.assertTrue(
                bookingStepMatches(label, state, icon),
                "Booking step \"" + label + "\" should be " + state + " with icon " + icon
                        + ". Steps: " + readBookingSteps());
    }

    /**
     * Outline the finished step and leave it. The next step clicks it, so the screenshot after this
     * step still shows the orange mark.
     */
    public void highlightFinishedBookingStep(String label) {
        context.set();
        ScenarioLogManager.getLogger().info("zmscitizenview: highlight finished booking step {}", label);
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> paintFinishedBookingStep(label));
        Assert.assertTrue(
                paintFinishedBookingStep(label),
                "Finished booking step \"" + label + "\" has no back button. Steps: " + readBookingSteps());
        try {
            Thread.sleep(200L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
    }

    public void clickHighlightedBookingStep() {
        context.set();
        JavascriptExecutor js = (JavascriptExecutor) DriverUtil.getDriver();
        Object stored = js.executeScript("return window.__zmsCitizenViewStepperLabel || '';");
        String label = stored instanceof String text ? text : "";
        Assert.assertFalse(label.isBlank(), "No highlighted booking step to click.");
        ScenarioLogManager.getLogger().info("zmscitizenview: click highlighted booking step {}", label);
        Object clicked =
                js.executeScript(
                        "var button=window.__zmsCitizenViewStepperTarget;"
                                + "if(!button)return false;"
                                + "button.scrollIntoView({block:'center'});"
                                + "button.click();"
                                + "window.__zmsCitizenViewStepperTarget=null;"
                                + "return true;");
        Assert.assertTrue(Boolean.TRUE.equals(clicked), "Highlighted booking step could not be clicked.");
        new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(defaultWaitSeconds))
                .until(d -> {
                    JsonNode step = findBookingStep(label);
                    return step != null && step.path("current").asBoolean() && !step.path("done").asBoolean();
                });
    }

    public boolean bookingStepMatches(String label, String state, String icon) {
        JsonNode step = findBookingStep(label);
        if (step == null || !icon.equals(step.path("icon").asText())) {
            return false;
        }
        boolean current = step.path("current").asBoolean();
        boolean done = step.path("done").asBoolean();
        return switch (state) {
            case "current" -> current && !done;
            case "finished" -> done && !current;
            case "later" -> !done && !current;
            default -> false;
        };
    }

    public String bookingStepLabel(int index) {
        JsonNode steps = readBookingSteps();
        if (steps == null || index < 0 || index >= steps.size()) {
            return "";
        }
        return steps.get(index).path("label").asText();
    }

    public JsonNode findBookingStep(String label) {
        JsonNode steps = readBookingSteps();
        if (steps == null) {
            return null;
        }
        for (JsonNode step : steps) {
            if (label.equals(step.path("label").asText())) {
                return step;
            }
        }
        return null;
    }

    public JsonNode readBookingSteps() {
        String script =
                "function norm(s){return (s||'').replace(/\\s+/g,' ').trim();}"
                        + "function iconOf(li){var use=li.querySelector('use');if(!use)return '';"
                        + "var href=use.getAttribute('href')||use.getAttribute('xlink:href')||'';"
                        + "var mark=href.indexOf('#icon-');return mark>=0?href.substring(mark+6):href;}"
                        + "var found=null;"
                        + "function walk(n){if(!n||found)return;var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag==='OL'&&n.classList&&n.classList.contains('m-form-steps')){found=n;return;}"
                        + "if(n.shadowRoot)walk(n.shadowRoot);"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)walk(c[i]);}"
                        + "walk(document.body);if(!found)return '[]';"
                        + "var items=found.querySelectorAll('li.m-form-step');var steps=[];"
                        + "for(var i=0;i<items.length;i++){var li=items[i];"
                        + "var title=li.querySelector('.m-form-step__title');"
                        + "var button=li.querySelector('button.m-form-step__button');"
                        + "steps.push({label:norm(title?title.textContent:''),icon:iconOf(li),"
                        + "current:li.getAttribute('aria-current')==='step',done:!!button});}"
                        + "return JSON.stringify(steps);";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script);
        if (!(raw instanceof String json) || json.isBlank()) {
            return null;
        }
        try {
            JsonNode node = new ObjectMapper().readTree(json);
            return node.isArray() ? node : null;
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn("zmscitizenview: booking stepper could not be read", e);
            return null;
        }
    }

    public boolean paintFinishedBookingStep(String label) {
        String script =
                "var label=arguments[0];"
                        + "function norm(s){return (s||'').replace(/\\s+/g,' ').trim();}"
                        + "function paint(node){if(!node)return;node.scrollIntoView({block:'center'});"
                        + "try{node.style.outline='4px solid #ffbf00';"
                        + "node.style.outlineOffset='3px';"
                        + "node.style.backgroundColor='rgba(255,191,0,0.25)';}catch(e){}}"
                        + "var found=null;"
                        + "function walk(n){if(!n||found)return;var tag=(n.tagName||'').toUpperCase();"
                        + "if(tag==='OL'&&n.classList&&n.classList.contains('m-form-steps')){found=n;return;}"
                        + "if(n.shadowRoot)walk(n.shadowRoot);"
                        + "var c=n.children;if(c)for(var i=0;i<c.length;i++)walk(c[i]);}"
                        + "walk(document.body);if(!found)return false;"
                        + "var items=found.querySelectorAll('li.m-form-step');"
                        + "for(var i=0;i<items.length;i++){var li=items[i];"
                        + "var title=li.querySelector('.m-form-step__title');"
                        + "if(norm(title?title.textContent:'')!==label)continue;"
                        + "var button=li.querySelector('button.m-form-step__button');"
                        + "if(!button)return false;"
                        + "paint(li);paint(button);"
                        + "window.__zmsCitizenViewStepperTarget=button;"
                        + "window.__zmsCitizenViewStepperLabel=label;"
                        + "return true;}"
                        + "return false;";
        Object painted = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, label);
        return Boolean.TRUE.equals(painted);
    }

}
