package zms.ataf.ui.pages.calldisplay;

import java.net.URLDecoder;
import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.time.Duration;
import java.util.ArrayList;
import java.util.List;
import java.util.Objects;

import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.WebDriverWait;

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

public class CalldisplayPageContext extends Context {

    public static final String TITLE = "Aufrufanzeige";

    private static final String SCOPE_LIST_KEY = "collections[scopelist]=";

    private WindowType windowType;

    CalldisplayPageContext(RemoteWebDriver driver) {
        super(driver);
    }

    public void open(String scopeList, String template) {
        navigateTo(buildUrl(scopeList, template));
    }

    public void replaceLocation(String from, String to) {
        String decoded = decodeUrl(DRIVER.getCurrentUrl());
        int key = decoded.indexOf(SCOPE_LIST_KEY);
        if (key < 0) {
            throw new IllegalStateException("Call display address has no scopelist. currentUrl=" + DRIVER.getCurrentUrl());
        }
        int start = key + SCOPE_LIST_KEY.length();
        int end = decoded.indexOf('&', start);
        if (end < 0) {
            end = decoded.length();
        }
        List<String> ids = new ArrayList<>(List.of(decoded.substring(start, end).split(",")));
        boolean replaced = false;
        for (int i = 0; i < ids.size(); i++) {
            if (from.equals(ids.get(i))) {
                ids.set(i, to);
                replaced = true;
            }
        }
        if (!replaced) {
            throw new IllegalStateException(
                    "Location " + from + " is not in the call display address. currentUrl=" + DRIVER.getCurrentUrl());
        }
        String template = templateFrom(decoded);
        ScenarioLogManager.getLogger().info("Call display: replace location {} with {}", from, to);
        navigateTo(buildUrl(String.join(",", ids), template));
    }

    public void reload() {
        ScenarioLogManager.getLogger().info("Call display: reload");
        try {
            DRIVER.navigate().refresh();
        } catch (TimeoutException e) {
            ScenarioLogManager.getLogger().warn("Call display reload timed out, waiting for the page.", e);
        }
        waitForCalldisplayDocument();
    }

    private void navigateTo(String url) {
        windowType = new WindowType("zmscalldisplay", new System("zmscalldisplay", resolveBaseUrl()));
        try {
            DRIVER.navigate().to(url);
        } catch (TimeoutException e) {
            ScenarioLogManager.getLogger().warn("Navigation to zmscalldisplay timed out, waiting for the page.", e);
        }
        waitForCalldisplayDocument();
        WindowControls.updateWindowList(DriverUtil.getDriver(), windowType);
        FrameControls.setCurrentFrame(FrameControls.DEFAULT_CONTENT);
        ScenarioLogManager.getLogger().info("Call display loaded: {}", url);
    }

    private void waitForCalldisplayDocument() {
        try {
            new WebDriverWait(DRIVER, Duration.ofSeconds(10)).until(driver -> {
                String current = driver.getCurrentUrl();
                return current != null && current.contains("/calldisplay");
            });
        } catch (TimeoutException e) {
            throw new TimeoutException(
                    "Call display did not load. currentUrl=" + DRIVER.getCurrentUrl(), e);
        }
    }

    private String buildUrl(String scopeList, String template) {
        return joinUrl(resolveBaseUrl(), "")
                + "?collections%5Bscopelist%5D="
                + URLEncoder.encode(scopeList, StandardCharsets.UTF_8)
                + "&template="
                + URLEncoder.encode(template, StandardCharsets.UTF_8);
    }

    private static String templateFrom(String decodedUrl) {
        int key = decodedUrl.indexOf("template=");
        if (key < 0) {
            return "default_counter";
        }
        int start = key + "template=".length();
        int end = decodedUrl.indexOf('&', start);
        return end < 0 ? decodedUrl.substring(start) : decodedUrl.substring(start, end);
    }

    private static String decodeUrl(String value) {
        try {
            return URLDecoder.decode(value == null ? "" : value, StandardCharsets.UTF_8);
        } catch (IllegalArgumentException e) {
            return value == null ? "" : value;
        }
    }

    String resolveBaseUrl() {
        String base;
        if (RunnerUtils.isJiraBasedTestExecution()) {
            base = Objects.requireNonNull(Environment.contains(TestExecutionContext.get().ENVIRONMENT))
                    .getSystemUrl("zmscalldisplay");
        } else {
            base = Objects.requireNonNull(
                    Environment.contains(TestProperties.getProperty("test.execution.test.environment", true)
                            .map(String.class::cast)
                            .orElse("")))
                    .getSystemUrl("zmscalldisplay");
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
        if (currentUrl != null && currentUrl.contains("calldisplay")) {
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
