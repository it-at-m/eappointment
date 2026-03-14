package zms.ataf.ui.pages.citizenview;

import java.util.Objects;

import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.remote.RemoteWebDriver;

import ataf.core.context.TestExecutionContext;
import ataf.core.data.Environment;
import ataf.core.data.System;
import ataf.core.logging.ScenarioLogManager;
import ataf.core.properties.TestProperties;
import ataf.core.utils.RunnerUtils;
import ataf.web.controls.FrameControls;
import ataf.web.controls.WindowControls;
import ataf.web.model.WindowType;
import ataf.web.pages.Context;
import ataf.web.utils.DriverUtil;

public class CitizenViewPageContext extends Context {

    public static final String NAME = "zmscitizenview";
    public static final String TITLE = "Terminvereinbarung Bürgeransicht Webcomponent";

    private WindowType windowType;
    String lastCitizenViewUrl;

    CitizenViewPageContext(RemoteWebDriver driver) {
        super(driver);
    }

    public void navigateToPage() {
        String citizenViewUrl;
        if (RunnerUtils.isJiraBasedTestExecution()) {
            citizenViewUrl = Objects.requireNonNull(Environment.contains(TestExecutionContext.get().ENVIRONMENT))
                    .getSystemUrl("zmscitizenview");
        } else {
            citizenViewUrl = Objects.requireNonNull(
                    Environment.contains(TestProperties.getProperty("test.execution.test.environment", true)
                            .map(String.class::cast)
                            .orElse("")))
                    .getSystemUrl("zmscitizenview");
        }

        lastCitizenViewUrl = citizenViewUrl;
        windowType = new WindowType(NAME, new System(NAME, citizenViewUrl));
        try {
            // For the Vite-powered zmscitizenview dev server we don't enforce a full
            // page-load; if the dev client keeps the page "loading", Selenium may fire a
            // TimeoutException even though the app is usable. In that case we log and
            // continue, letting the UI assertions verify the page instead.
            DRIVER.navigate().to(citizenViewUrl);
        } catch (TimeoutException e) {
            ScenarioLogManager.getLogger().warn(
                    "Navigation to zmscitizenview timed out in WebDriver, continuing to UI assertions anyway.", e);
        }
        WindowControls.updateWindowList(DriverUtil.getDriver(), windowType);
        FrameControls.setCurrentFrame(FrameControls.DEFAULT_CONTENT);

        // Give the webcomponent a brief moment to bootstrap before we start
        // asserting on its DOM state.
        try {
            Thread.sleep(3000L);
        } catch (InterruptedException ie) {
            Thread.currentThread().interrupt();
        }
    }

    /**
     * Opens zmscitizenview on the jump-in route: service and location preselected; UI shows the
     * service combination (quantity) step, not the service finder.
     */
    public void navigateWithJumpIn(String serviceId, String locationId) {
        String citizenViewUrl;
        if (RunnerUtils.isJiraBasedTestExecution()) {
            citizenViewUrl = Objects.requireNonNull(Environment.contains(TestExecutionContext.get().ENVIRONMENT))
                    .getSystemUrl("zmscitizenview");
        } else {
            citizenViewUrl = Objects.requireNonNull(
                    Environment.contains(TestProperties.getProperty("test.execution.test.environment", true)
                            .map(String.class::cast)
                            .orElse("")))
                    .getSystemUrl("zmscitizenview");
        }
        lastCitizenViewUrl = citizenViewUrl;
        windowType = new WindowType(NAME, new System(NAME, citizenViewUrl));
        int hashIdx = citizenViewUrl.indexOf('#');
        String base = hashIdx >= 0 ? citizenViewUrl.substring(0, hashIdx) : citizenViewUrl;
        String jumpInUrl = base + "#/services/" + serviceId + "/locations/" + locationId;
        try {
            DRIVER.navigate().to(jumpInUrl);
        } catch (TimeoutException e) {
            ScenarioLogManager.getLogger().warn("Jump-in navigation timed out, continuing.", e);
        }
        WindowControls.updateWindowList(DriverUtil.getDriver(), windowType);
        FrameControls.setCurrentFrame(FrameControls.DEFAULT_CONTENT);
        ScenarioLogManager.getLogger().info("Jump-in loaded: {}", jumpInUrl);
        // Vue + offices-and-services fetch: wait until error callout or combination UI is present
        waitJumpInDomSettled();
    }

    /** Poll shadow DOM until invalid jump-in, Weiter, or Service Finder copy appears (max ~25s). */
    void waitJumpInDomSettled() {
        String script =
                "function walk(n){var s='';if(!n)return s;if(n.nodeType===3)return n.nodeValue||'';"
                        + "if(n.shadowRoot)s+=walk(n.shadowRoot);var c=n.childNodes;if(c)for(var i=0;i<c.length;i++)s+=walk(c[i]);return s;}"
                        + "var t=walk(document.body);"
                        + "return t.indexOf('Diese Ansicht kann nicht geladen werden')>=0||t.indexOf('This view cannot be loaded')>=0"
                        + "||t.indexOf('Weiter')>=0||t.indexOf('Kombinierbare Leistungen')>=0"
                        + "||t.indexOf('Bürgerservice-Suche')>=0;";
        long deadline = System.currentTimeMillis() + 25_000L;
        while (System.currentTimeMillis() < deadline) {
            try {
                Object o = ((org.openqa.selenium.JavascriptExecutor) DRIVER).executeScript(script);
                if (Boolean.TRUE.equals(o)) {
                    return;
                }
            } catch (Exception e) {
                ScenarioLogManager.getLogger().debug("waitJumpInDomSettled: {}", e.toString());
            }
            try {
                Thread.sleep(400L);
            } catch (InterruptedException ie) {
                Thread.currentThread().interrupt();
                return;
            }
        }
        ScenarioLogManager.getLogger().warn("Jump-in DOM still unsettled after 25s (half-blank risk).");
    }

    @Override
    public void set() {
        if (!WindowControls.getActiveWindow().getWindowTitle().equals(TITLE)) {
            if (WindowControls.isWindowWithTitleInList(TITLE)) {
                WindowControls.switchToWindow(DRIVER, TITLE);
            } else {
                WindowControls.switchToOpenedWindow(DRIVER, DEFAULT_EXPLICIT_WAIT_TIME, windowType, TITLE);
            }
        }
    }
}
