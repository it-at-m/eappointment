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
