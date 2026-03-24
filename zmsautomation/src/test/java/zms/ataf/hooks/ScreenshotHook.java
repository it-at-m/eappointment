package zms.ataf.hooks;

import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.time.OffsetDateTime;
import java.time.format.DateTimeFormatter;
import java.util.Locale;
import java.util.Set;
import java.util.TreeSet;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

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

    /** Jira-style keys on tags: ZMS-123, ZMSKVR-456, GH-789 */
    private static final Pattern TICKET_TAG = Pattern.compile("(?i)^(ZMSKVR|ZMS|GH)-(\\d+)$");

    private static final Set<String> MODULE_TAGS = Set.of(
        "zmsadmin",
        "zmscitizenview",
        "zmsstatistic",
        "buergeransicht",
        "zmsticketprinter",
        "zmscalldisplay");

    @AfterStep
    public void afterStep(Scenario scenario) {
        if (!ENABLED) return;

        // Only run for UI scenarios that actually use a WebDriver (@web tag).
        if (!scenario.getSourceTagNames().contains("@web")) {
            return;
        }
        try {
            RemoteWebDriver driver = DriverUtil.getDriver();
            String url = driver.getCurrentUrl();
            // Background REST steps run before first navigation — Firefox is still on about:newtab.
            // Skip screenshots until the app URL is loaded (avoids dozens of empty-tab PNGs).
            if (url.startsWith("about:")
                    || "data:,".equals(url)
                    || (url.contains("mozilla.org") && !url.contains("808"))) {
                return;
            }
            if ("zmscitizenview".equals(resolveModuleFolder(scenario))) {
                if (!url.contains("#/") && !url.contains("citizenview") && !url.contains("8082")
                        && !url.contains("buergeransicht") && !url.contains("localhost")) {
                    return;
                }
            }
            byte[] png = ((TakesScreenshot) driver).getScreenshotAs(OutputType.BYTES);

            // 1) Attach to Cucumber report (shows up in the HTML report)
            scenario.attach(png, "image/png", "after-step");

            // 2) Persist: target/screenshots/<module>/<TICKETS>_scenario_name/<timestamp>.png
            String module = resolveModuleFolder(scenario);
            String ticketPrefix = resolveTicketPrefix(scenario);
            String scenarioPart =
                scenario.getName().replaceAll("[^a-zA-Z0-9._\\-]+", "_");
            String folder = ticketPrefix + scenarioPart;
            String ts = DateTimeFormatter.ofPattern("yyyyMMdd-HHmmss.SSS", Locale.ROOT)
                                         .format(OffsetDateTime.now());
            Path out = Paths.get("target", "screenshots", module, folder, ts + ".png");
            Files.createDirectories(out.getParent());
            Files.write(out, png);
        } catch (Throwable ignored) {
            // Never fail the test due to screenshot issues
        }
    }

    static String resolveModuleFolder(Scenario scenario) {
        for (String raw : scenario.getSourceTagNames()) {
            String tag = raw.startsWith("@") ? raw.substring(1) : raw;
            if (MODULE_TAGS.contains(tag.toLowerCase(Locale.ROOT))) {
                return tag.toLowerCase(Locale.ROOT);
            }
        }
        String uri = scenario.getUri().toString().replace('\\', '/');
        for (String m : MODULE_TAGS) {
            if (uri.contains("/ui/" + m + "/")) {
                return m;
            }
        }
        int ui = uri.indexOf("/ui/");
        if (ui >= 0) {
            String rest = uri.substring(ui + 4);
            int slash = rest.indexOf('/');
            if (slash > 0) {
                String segment = rest.substring(0, slash);
                if (segment.matches("[a-zA-Z][a-zA-Z0-9_-]*")) {
                    return segment;
                }
            }
        }
        return "ui-other";
    }

    /** Sorted unique ticket keys, joined with "_", plus trailing "_" for folder prefix. */
    static String resolveTicketPrefix(Scenario scenario) {
        TreeSet<String> tickets = new TreeSet<>();
        for (String raw : scenario.getSourceTagNames()) {
            String tag = raw.startsWith("@") ? raw.substring(1) : raw;
            Matcher m = TICKET_TAG.matcher(tag);
            if (m.matches()) {
                tickets.add(m.group(1).toUpperCase(Locale.ROOT) + "-" + m.group(2));
            }
        }
        if (tickets.isEmpty()) {
            return "";
        }
        return String.join("_", tickets) + "_";
    }
}
