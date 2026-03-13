package zms.ataf.ui.pages.citizenview;

import java.util.Objects;

import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.testng.Assert;

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
import ataf.web.steps.Hook;
import ataf.web.utils.DriverUtil;

public class CitizenViewPageContext extends Context {

    public static final String NAME = "zmscitizenview";
    public static final String TITLE = "Terminvereinbarung Bürgeransicht Webcomponent";

    private WindowType windowType;

    CitizenViewPageContext(RemoteWebDriver driver) {
        super(driver);
    }

    /**
     * Open the Citizen API via refarch-gateway in the browser so reports show JSON (or error)
     * from the same URL the app uses for fetch. Optional screenshot for ATAF reports.
     * URL: env REFARCH_GATEWAY_OFFICES_URL or default refarch-gateway:8080/.../offices-and-services/
     */
    public void navigateToGatewayOfficesApi() {
        String url =
                System.getenv()
                        .getOrDefault(
                                "REFARCH_GATEWAY_OFFICES_URL",
                                "http://refarch-gateway:8080/buergeransicht/api/citizen/offices-and-services/");
        if (url.isBlank()) {
            ScenarioLogManager.getLogger().info("REFARCH_GATEWAY_OFFICES_URL empty — skip gateway navigation");
            return;
        }
        ScenarioLogManager.getLogger().info("Navigate to gateway Citizen API (screenshot): " + url);
        try {
            DRIVER.navigate().to(url);
        } catch (TimeoutException e) {
            ScenarioLogManager.getLogger().warn("Gateway URL navigation timed out; continuing.", e);
        }
        Hook.makeScreenshot(DRIVER, "gateway_offices_and_services");
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

