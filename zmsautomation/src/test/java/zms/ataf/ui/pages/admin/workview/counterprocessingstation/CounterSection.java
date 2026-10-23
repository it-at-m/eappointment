package zms.ataf.ui.pages.admin.workview.counterprocessingstation;

import java.time.Duration;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.model.LocatorType;
import zms.ataf.ui.pages.admin.AdminPageContext;

/**
 * Tresen
 */
public class CounterSection extends CounterProcessingStationPage {

    public CounterSection(RemoteWebDriver driver, AdminPageContext adminPageContext) {
        super(driver, adminPageContext);
    }

    public void checkInformationVisible() {
        ScenarioLogManager.getLogger().info("Checking if the 'Informationen' panel is visible on 'Tresen' page.");
        // Wait for the panel (may load via data-reload) then check the four key headings (contains() tolerates whitespace)
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//h2[@class='board__heading' and contains(normalize-space(.), 'Informationen')]", LocatorType.XPATH, false),
                "'Informationen' panel heading not visible.");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//h4[contains(normalize-space(.), 'Fiktive Arbeitsplätze')]", LocatorType.XPATH, false),
                "'Fiktive Arbeitsplätze' not visible.");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//h4[contains(normalize-space(.), 'Anzahl offener Vorgänge')]", LocatorType.XPATH, false),
                "'Anzahl offener Vorgänge:' not visible.");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//h4[contains(normalize-space(.), 'davon vor nächstem Spontankunden')]", LocatorType.XPATH, false),
                "'davon vor nächstem Spontankunden:' not visible.");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//h4[contains(normalize-space(.), 'Wartezeit für neue Spontankunden')]", LocatorType.XPATH, false),
                "'Wartezeit für neue Spontankunden:' not visible.");
    }

    /** ZMS-3152 / ZMSALT-3381: average waiting times and Wartende Ampel under Informationen. */
    public void assertAverageWaitingTimeLabelsVisible() {
        ScenarioLogManager.getLogger().info("Checking average waiting time labels under Informationen...");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME,
                        "//h4[contains(normalize-space(.), 'Durchschnittliche Wartezeit von Terminkunden')]",
                        LocatorType.XPATH, false),
                "'Durchschnittliche Wartezeit von Terminkunden' not visible.");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME,
                        "//h4[contains(normalize-space(.), 'Durchschnittliche Wartezeit von Spontankunden')]",
                        LocatorType.XPATH, false),
                "'Durchschnittliche Wartezeit von Spontankunden' not visible.");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME,
                        "//h4[contains(@class,'wartende') and contains(normalize-space(.), 'Wartende')]",
                        LocatorType.XPATH, false),
                "'Wartende' not visible under Informationen.");
    }

    public WebElement waitingTrafficLightRow() {
        CONTEXT.set();
        // Sachbearbeiter next may expose data-waiting-clients-row; Tresen info uses li.traffic-light.
        By withScale = By.cssSelector("li.traffic-light[data-waiting-clients-row]");
        By legacy = By.cssSelector("li.traffic-light");
        By locator = DRIVER.findElements(withScale).isEmpty() ? legacy : withScale;
        return new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(locator));
    }

    /**
     * Painted Ampel on Tresen Informationen.
     * 0–10 green rgba(0, 255, 0, 0.5), 11–20 yellow rgba(255, 255, 0, 0.5),
     * 21–40 orange rgba(255, 127, 0, 0.5), 41+ red rgba(255, 0, 0, 0.5).
     */
    public void assertWaitingTrafficLightClass(String expectedClass) {
        int[] band = trafficLightBand(expectedClass);
        WebElement row = waitingTrafficLightRow();
        int count = waitingCountOn(row);
        for (int attempt = 1; count < band[0] && attempt <= 4; attempt++) {
            ScenarioLogManager.getLogger().info(
                    "Wartende is still {} ({} starts at {}). Reloading ({}/4).",
                    count, expectedClass, band[0], attempt);
            pauseBeforeTrafficLightReload();
            reloadAndReadWaitingClientsOnCounter();
            row = waitingTrafficLightRow();
            count = waitingCountOn(row);
        }
        Assert.assertTrue(
                count >= band[0] && count <= band[1],
                "Wartende " + count + " is outside the " + expectedClass + " band ("
                        + band[0] + "–" + (band[1] == Integer.MAX_VALUE ? "…" : band[1]) + ").");
        String classes = row.getAttribute("class") == null ? "" : row.getAttribute("class");
        Assert.assertTrue(
                classes.matches("(?s).*\\b" + expectedClass + "\\b.*"),
                "Expected traffic-light class \"" + expectedClass + "\" but was \"" + classes + "\".");
        for (String other : new String[] {"green", "yellow", "orange", "red"}) {
            if (other.equals(expectedClass)) {
                continue;
            }
            Assert.assertFalse(
                    classes.matches("(?s).*\\b" + other + "\\b.*"),
                    "Traffic light is " + expectedClass + " but also has class \"" + other + "\" (" + classes + ").");
        }
        assertTrafficLightBackground(row, expectedClass, band[2], band[3], band[4]);
    }

    /** ZMS-3152: colour follows Wartende (0–10 green, 11–20 yellow, 21–40 orange, 41+ red). */
    public void assertWaitingTrafficLightMatchesCount() {
        int count = readWaitingClientsOnCounter();
        String expected = trafficLightClassForCount(count);
        ScenarioLogManager.getLogger().info(
                "Wartende={} → expected Ampel colour {}", count, expected);
        assertWaitingTrafficLightClass(expected);
    }

    private static String trafficLightClassForCount(int count) {
        if (count <= 10) {
            return "green";
        }
        if (count <= 20) {
            return "yellow";
        }
        if (count <= 40) {
            return "orange";
        }
        return "red";
    }

    /** min, max, red, green, blue for the stylesheet colour. */
    private static int[] trafficLightBand(String color) {
        switch (color) {
            case "green":
                return new int[] {0, 10, 0, 255, 0};
            case "yellow":
                return new int[] {11, 20, 255, 255, 0};
            case "orange":
                return new int[] {21, 40, 255, 127, 0};
            case "red":
                return new int[] {41, Integer.MAX_VALUE, 255, 0, 0};
            default:
                throw new AssertionError("Unknown traffic-light colour \"" + color + "\".");
        }
    }

    private static final Pattern TRAFFIC_LIGHT_RGB = Pattern.compile(
            "rgba?\\(\\s*(\\d+)\\s*,\\s*(\\d+)\\s*,\\s*(\\d+)(?:\\s*,\\s*([0-9.]+))?\\s*\\)");

    private static void assertTrafficLightBackground(WebElement row, String color, int red, int green, int blue) {
        String background = row.getCssValue("background-color");
        ScenarioLogManager.getLogger().info("Ampel {} background-color={}", color, background);
        Matcher matcher = TRAFFIC_LIGHT_RGB.matcher(background == null ? "" : background);
        Assert.assertTrue(
                matcher.find(),
                "Traffic light background for " + color + " was \"" + background + "\".");
        int actualRed;
        int actualGreen;
        int actualBlue;
        String alphaGroup = matcher.group(4);
        double alpha = Double.NaN;
        try {
            actualRed = Integer.parseInt(matcher.group(1));
            actualGreen = Integer.parseInt(matcher.group(2));
            actualBlue = Integer.parseInt(matcher.group(3));
            if (alphaGroup != null) {
                alpha = Double.parseDouble(alphaGroup);
            }
        } catch (NumberFormatException e) {
            throw new AssertionError(
                    "Traffic light background for " + color + " was \"" + background + "\".", e);
        }
        boolean pure = near(actualRed, red) && near(actualGreen, green) && near(actualBlue, blue);
        boolean translucent = alphaGroup != null && Math.abs(alpha - 0.5d) <= 0.05d;
        boolean blendedOnWhite = alphaGroup == null
                && near(actualRed, blended(red))
                && near(actualGreen, blended(green))
                && near(actualBlue, blended(blue));
        Assert.assertTrue(
                (pure && translucent) || blendedOnWhite,
                "Expected " + color + " rgba(" + red + ", " + green + ", " + blue
                        + ", 0.5) but background-color was " + background + ".");
    }

    private static boolean near(int actual, int expected) {
        return Math.abs(actual - expected) <= 2;
    }

    /** 50% of the stylesheet colour over white, which some drivers return instead of rgba(). */
    private static int blended(int channel) {
        return (int) Math.round(channel * 0.5d + 255 * 0.5d);
    }

    private static void pauseBeforeTrafficLightReload() {
        try {
            Thread.sleep(1000L);
        } catch (InterruptedException interrupted) {
            Thread.currentThread().interrupt();
            Assert.fail("Interrupted while waiting for the traffic light to update");
        }
    }

    public void assertWaitingClientsAtLeast(int minimum) {
        int count = readWaitingClientsOnCounter();
        Assert.assertTrue(
                count >= minimum,
                "Expected Wartende >= " + minimum + " but was " + count + ".");
    }

    private static final By COUNTER_WAITING_COUNT = By.xpath(
            "//h4[contains(@class,'wartende')]/ancestor::li//span[contains(@class,'waiting-count')]");

    private static int waitingCountOn(WebElement row) {
        String text = row.findElement(By.cssSelector("span.waiting-count")).getText().trim();
        try {
            return Integer.parseInt(text);
        } catch (NumberFormatException e) {
            throw new AssertionError("Wartende under Informationen is not a number: \"" + text + "\"", e);
        }
    }

    /** Wartende under Informationen on Tresen. The panel arrives after its own reload. */
    public int readWaitingClientsOnCounter() {
        CONTEXT.set();
        WebElement count = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(COUNTER_WAITING_COUNT));
        String text = count.getText().trim();
        try {
            return Integer.parseInt(text);
        } catch (NumberFormatException e) {
            throw new AssertionError("Wartende under Informationen is not a number: \"" + text + "\"", e);
        }
    }

    public int reloadAndReadWaitingClientsOnCounter() {
        DRIVER.navigate().refresh();
        return readWaitingClientsOnCounter();
    }
}
