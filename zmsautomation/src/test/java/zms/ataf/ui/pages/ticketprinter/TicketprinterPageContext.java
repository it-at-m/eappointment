package zms.ataf.ui.pages.ticketprinter;

import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.util.Map;
import java.util.Objects;

import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.chromium.HasCdp;
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

public class TicketprinterPageContext extends Context {

    public static final String NAME = "Ticketausgabe";
    public static final String TITLE = "ZMS Ticketprinter";

    private static final String PRINT_STUB = "window.print = function(){};"
            + "(function(orig){window.setTimeout=function(fn,delay){"
            + "if(delay===1500){return 0;}return orig.apply(this,arguments);};})(window.setTimeout);";

    private WindowType windowType;

    TicketprinterPageContext(RemoteWebDriver driver) {
        super(driver);
    }

    public void navigateToScope(String scopeId) {
        navigateTo(joinUrl(resolveBaseUrl(), "scope/" + scopeId + "/"));
    }

    public void navigateToRequest(String scopeId, String requestId) {
        navigateToButtonList("r" + scopeId + "-" + requestId);
    }

    public void navigateToButtonList(String buttonList) {
        String query = "ticketprinter%5Bbuttonlist%5D="
                + URLEncoder.encode(buttonList, StandardCharsets.UTF_8);
        navigateTo(joinUrl(resolveBaseUrl(), "") + "?" + query);
    }

    private void navigateTo(String url) {
        stubWindowPrint();
        windowType = new WindowType("zmsticketprinter", new System("zmsticketprinter", resolveBaseUrl()));
        try {
            DRIVER.navigate().to(url);
        } catch (TimeoutException e) {
            ScenarioLogManager.getLogger().warn("Navigation to zmsticketprinter timed out, continuing.", e);
        }
        WindowControls.updateWindowList(DriverUtil.getDriver(), windowType);
        FrameControls.setCurrentFrame(FrameControls.DEFAULT_CONTENT);
        ScenarioLogManager.getLogger().info("Ticketprinter loaded: {}", url);
    }

    /**
     * process.js calls {@code window.print()} on load, which blocks Chrome on the print dialog.
     * Stub it on every new document in this Chromium session.
     */
    void stubWindowPrint() {
        if (DRIVER instanceof HasCdp cdp) {
            try {
                cdp.executeCdpCommand("Page.addScriptToEvaluateOnNewDocument", Map.of("source", PRINT_STUB));
            } catch (RuntimeException e) {
                ScenarioLogManager.getLogger().warn("Could not stub window.print via CDP: {}", e.toString());
            }
        }
    }

    String resolveBaseUrl() {
        String base;
        if (RunnerUtils.isJiraBasedTestExecution()) {
            base = Objects.requireNonNull(Environment.contains(TestExecutionContext.get().ENVIRONMENT))
                    .getSystemUrl("zmsticketprinter");
        } else {
            base = Objects.requireNonNull(
                    Environment.contains(TestProperties.getProperty("test.execution.test.environment", true)
                            .map(String.class::cast)
                            .orElse("")))
                    .getSystemUrl("zmsticketprinter");
        }
        return base.endsWith("/") ? base : base + "/";
    }

    private static String joinUrl(String base, String path) {
        if (path == null || path.isEmpty()) {
            return base;
        }
        return base + path.replaceFirst("^/", "");
    }

    @Override
    public void set() {
        String currentUrl = DRIVER.getCurrentUrl();
        if (currentUrl != null && currentUrl.contains("ticketprinter")) {
            return;
        }
        if (WindowControls.isWindowWithTitleInList(TITLE)) {
            WindowControls.switchToWindow(DRIVER, TITLE);
            return;
        }
        if (windowType != null) {
            WindowControls.switchToOpenedWindow(DRIVER, DEFAULT_EXPLICIT_WAIT_TIME, windowType, TITLE);
        }
    }
}
