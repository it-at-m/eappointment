package zms.ataf.hooks;

import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.time.OffsetDateTime;
import java.time.format.DateTimeFormatter;
import java.util.Locale;

import org.openqa.selenium.OutputType;
import org.openqa.selenium.TakesScreenshot;
import org.openqa.selenium.remote.RemoteWebDriver;

import ataf.web.utils.DriverUtil;
import io.cucumber.java.AfterStep;
import io.cucumber.java.Scenario;

public class ScreenshotHook {
    // Default off to avoid huge artifacts; enable with: -DSCREENSHOT_EVERY_STEP=true
    private static final boolean ENABLED = Boolean.parseBoolean(
        System.getProperty("SCREENSHOT_EVERY_STEP",
            System.getenv().getOrDefault("SCREENSHOT_EVERY_STEP", "false"))
    );

    @AfterStep
    public void afterStep(Scenario scenario) {
        if (!ENABLED) return;
        try {
            RemoteWebDriver driver = DriverUtil.getDriver();
            byte[] png = ((TakesScreenshot) driver).getScreenshotAs(OutputType.BYTES);

            // 1) Attach to Cucumber report (shows up in the HTML report)
            scenario.attach(png, "image/png", "after-step");

            // 2) Also persist to disk: target/screenshots/<scenario>/<timestamp>.png
            String scenarioFolder = scenario.getName().replaceAll("[^a-zA-Z0-9._-]+", "_");
            String ts = DateTimeFormatter.ofPattern("yyyyMMdd-HHmmss.SSS", Locale.ROOT)
                                         .format(OffsetDateTime.now());
            Path out = Paths.get("target", "screenshots", scenarioFolder, ts + ".png");
            Files.createDirectories(out.getParent());
            Files.write(out, png);
        } catch (Throwable ignored) {
            // Never fail the test due to screenshot issues
        }
    }
}