package zms.ataf.ui.pages.citizenview;

import java.util.Objects;

import org.openqa.selenium.remote.RemoteWebDriver;
import org.testng.Assert;

import ataf.core.context.TestExecutionContext;
import ataf.core.data.Environment;
import ataf.core.data.System;
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

        // For the Vite-powered zmscitizenview dev server we don't rely on the page
        // title (it may be empty or localized differently). Just navigate to the URL
        // and let the scenario assertions verify that the Service Finder is rendered.
        windowType = new WindowType(NAME, new System(NAME, citizenViewUrl));
        DRIVER.navigate().to(citizenViewUrl);
        WindowControls.updateWindowList(DriverUtil.getDriver(), windowType);
        FrameControls.setCurrentFrame(FrameControls.DEFAULT_CONTENT);
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

