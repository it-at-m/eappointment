package zms.ataf.ui.pages.buergeransicht;

import java.util.Objects;

import org.openqa.selenium.remote.RemoteWebDriver;
import org.testng.Assert;

import ataf.core.context.TestExecutionContext;
import ataf.core.data.Environment;
import ataf.core.data.System;
import ataf.core.helpers.TestDataHelper;
import ataf.core.properties.TestProperties;
import ataf.core.utils.RunnerUtils;
import ataf.web.controls.FrameControls;
import ataf.web.controls.WindowControls;
import ataf.web.model.WindowType;
import ataf.web.pages.Context;
import ataf.web.utils.DriverUtil;


public class BuergeransichtPageContext extends Context {
    public static final String NAME = "Bürgeransicht";
    public static final String TITLE = "ZMS Bürgeransicht";
    private WindowType windowType;

    BuergeransichtPageContext(RemoteWebDriver driver) {
        super(driver);
    }

    public void navigateToPage() {
        String buergeransichtUrl;
        if (RunnerUtils.isJiraBasedTestExecution()) {
            buergeransichtUrl = Objects.requireNonNull(Environment.contains(TestExecutionContext.get().ENVIRONMENT)).getSystemUrl("Bürgeransicht");
        } else {
            buergeransichtUrl = Objects.requireNonNull(
                            Environment.contains(TestProperties.getProperty("test.execution.test.environment", true).map(String.class::cast).orElse("")))
                    .getSystemUrl("Bürgeransicht");
        }
        windowType = new WindowType("Bürgeransicht", new System("Bürgeransicht", buergeransichtUrl));
        if (navigateToPageByUrl(DEFAULT_EXPLICIT_WAIT_TIME, buergeransichtUrl, BuergeransichtPageContext.TITLE)) {
            WindowControls.updateWindowList(DriverUtil.getDriver(), windowType);
            FrameControls.setCurrentFrame(FrameControls.DEFAULT_CONTENT);
        } else {
            Assert.fail("Could not navigate to Bürgeransicht!");
        }
    }

    public static void incrementAppointmentCount() {
        if (TestDataHelper.getTestData("appointment_count") != null) {
            TestDataHelper.setTestData("appointment_count", String.valueOf((Integer.parseInt(TestDataHelper.getTestData("appointment_count")) + 1)));
        } else {
            TestDataHelper.setTestData("appointment_count", "1");
        }
    }

    public static void incrementAppointmentCanceledCount() {
        if (TestDataHelper.getTestData("appointment_canceled_count") != null) {
            TestDataHelper.setTestData("appointment_canceled_count",
                    String.valueOf((Integer.parseInt(TestDataHelper.getTestData("appointment_canceled_count")) + 1)));
        } else {
            TestDataHelper.setTestData("appointment_canceled_count", "1");
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
