package zms.ataf.ui.pages.statistics;

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


public class StatisticsPageContext extends Context {
    public static final String NAME = "Statistik";
    public static final String TITLE = "Anmeldung - ZMS Statistik";
    private WindowType windowType;

    StatisticsPageContext(RemoteWebDriver driver) {
        super(driver);
    }

    public void navigateToPage() {
        String statisticsUrl;
        if (RunnerUtils.isJiraBasedTestExecution()) {
            statisticsUrl = Objects.requireNonNull(Environment.contains(TestExecutionContext.get().ENVIRONMENT)).getSystemUrl("Statistik");
        } else {
            statisticsUrl = Objects.requireNonNull(
                            Environment.contains(TestProperties.getProperty("test.execution.test.environment", true).map(String.class::cast).orElse("")))
                    .getSystemUrl("Statistik");
        }
        windowType = new WindowType("Statistik", new System("Statistik", statisticsUrl));
        if (navigateToPageByUrl(DEFAULT_EXPLICIT_WAIT_TIME, statisticsUrl, StatisticsPageContext.TITLE)) {
            WindowControls.updateWindowList(DriverUtil.getDriver(), windowType);
            FrameControls.setCurrentFrame(FrameControls.DEFAULT_CONTENT);
        } else {
            Assert.fail("Could not navigate to statistics page!");
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
