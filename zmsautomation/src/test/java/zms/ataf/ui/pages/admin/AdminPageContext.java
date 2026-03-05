package zms.ataf.ui.pages.admin;

import java.util.ArrayList;
import java.util.List;
import java.util.Objects;

import org.openqa.selenium.By;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.WebElement;
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
import ataf.web.model.LocatorType;
import ataf.web.model.Window;
import ataf.web.model.WindowType;
import ataf.web.pages.Context;
import ataf.web.steps.Hook;
import ataf.web.utils.DriverUtil;


public class AdminPageContext extends Context {
    public static final String NAME = "Zeitmanagementsystem";
    public static final String START_PAGE_TITLE = "Anmeldung - ZMS";
    private Window window;
    private WindowType windowType;

    AdminPageContext(RemoteWebDriver driver) {
        super(driver);
    }

    public void navigateToPage() {
        String adminUrl;
        if (RunnerUtils.isJiraBasedTestExecution()) {
            adminUrl = Objects.requireNonNull(Environment.contains(TestExecutionContext.get().ENVIRONMENT)).getSystemUrl("Admin");
        } else {
            adminUrl = Objects.requireNonNull(
                            Environment.contains(TestProperties.getProperty("test.execution.test.environment", true).map(String.class::cast).orElse("")))
                    .getSystemUrl("Admin");
        }
        windowType = new WindowType("Admin", new System("Admin", adminUrl));
        if (navigateToPageByUrl(DEFAULT_EXPLICIT_WAIT_TIME, adminUrl, START_PAGE_TITLE)) {
            WindowControls.updateWindowList(DriverUtil.getDriver(), windowType);
            FrameControls.setCurrentFrame(FrameControls.DEFAULT_CONTENT);
            window = WindowControls.getActiveWindow();
        } else {
            Assert.fail("Could not navigate to Administration!");
        }
    }

    public void waitForSpinners() {
        List<WebElement> spinnerElements = findElementsByLocatorType(1000L, "//div[@class='spinner']", LocatorType.XPATH);
        if (!spinnerElements.isEmpty()) {
            waitForElementToDisappearByXpath(DEFAULT_EXPLICIT_WAIT_TIME, "//div[@class='spinner']");
        }
    }

    private void getErrorMessageText(StringBuilder stringBuilder, WebElement webElement, String locatorXpath) {
        try {
            String errorMessageText = webElement.findElement(By.xpath(locatorXpath)).getText().trim();
            if (!stringBuilder.isEmpty()) {
                stringBuilder.append('\n');
            }
            stringBuilder.append(errorMessageText);
        } catch (NoSuchElementException | StaleElementReferenceException ignore) {
        }

    }

    @Override
    public void set() {
        //Switch to admin window
        if (window != null) {
            if (!WindowControls.getActiveWindow().equals(window)) {
                WindowControls.switchToWindow(DRIVER, window);
            }
            WindowControls.updateWindowList(DriverUtil.getDriver(), windowType);
        } else {
            window = WindowControls.newWindow(DRIVER, windowType, org.openqa.selenium.WindowType.TAB);
            navigateToPage();
        }

        //Wait for spinners having finished loading
        waitForSpinners();

        //Check for any errors
        List<WebElement> errorMessages = findElementsByLocatorType(500L, "//div[contains(@class,'message--error')]", LocatorType.XPATH);
        if (!errorMessages.isEmpty()) {
            List<String> errorMessageTexts = new ArrayList<>();
            StringBuilder errorMessage = new StringBuilder();
            int count = 1;
            for (WebElement messageElement : errorMessages) {
                if (messageElement.isDisplayed()) {
                    moveToElementAction(messageElement);
                    Hook.makeScreenshot(DRIVER, "zms_admin_error_message_" + count);
                    getErrorMessageText(errorMessage, messageElement, "./h3[1]");
                    getErrorMessageText(errorMessage, messageElement, "./p[1]");
                    getErrorMessageText(errorMessage, messageElement, "./h3[2]");
                    getErrorMessageText(errorMessage, messageElement, "./p[1]");
                    try {
                        for (WebElement traceElement : messageElement.findElements(By.xpath("./div/ul/li"))) {
                            errorMessage.append('\n');
                            errorMessage.append(traceElement.findElement(By.xpath("./div[1]")).getText().trim());
                            errorMessage.append(' ');
                            errorMessage.append(traceElement.findElement(By.xpath("./div[2]")).getText().trim());
                        }
                    } catch (NoSuchElementException | StaleElementReferenceException ignore) {
                    }
                    if (!errorMessage.isEmpty()) {
                        errorMessageTexts.add(errorMessage.toString());
                        errorMessage.delete(0, errorMessage.length() - 1);
                        errorMessage.setLength(0);
                        count++;
                    }
                }
            }
            if (!errorMessageTexts.isEmpty()) {
                errorMessageTexts.forEach(ScenarioLogManager.getLogger()::error);
            }
        }
    }
}
