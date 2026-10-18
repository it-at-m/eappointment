package zms.ataf.ui.pages.admin.workview.counterprocessingstation;

import java.time.Duration;

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

    public void assertWaitingTrafficLightClass(String expectedClass) {
        CONTEXT.set();
        WebElement row = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(
                        By.cssSelector("li.traffic-light[data-waiting-clients-row]")));
        String classes = row.getAttribute("class") == null ? "" : row.getAttribute("class");
        Assert.assertTrue(
                classes.matches("(?s).*\\b" + expectedClass + "\\b.*"),
                "Expected traffic-light class \"" + expectedClass + "\" but was \"" + classes + "\".");
        Assert.assertEquals(
                row.getAttribute("data-waiting-clients-green-max"), "10",
                "Ampel greenMax should come from the central scale.");
        Assert.assertEquals(
                row.getAttribute("data-waiting-clients-yellow-max"), "20",
                "Ampel yellowMax should come from the central scale.");
        Assert.assertEquals(
                row.getAttribute("data-waiting-clients-orange-max"), "40",
                "Ampel orangeMax should come from the central scale.");
    }

    /** ZMS-3152: colour follows Wartende using the central QAppoint thresholds. */
    public void assertWaitingTrafficLightMatchesCount() {
        int count = readWaitingClientsOnCounter();
        String expected;
        if (count <= 10) {
            expected = "green";
        } else if (count <= 20) {
            expected = "yellow";
        } else if (count <= 40) {
            expected = "orange";
        } else {
            expected = "red";
        }
        ScenarioLogManager.getLogger().info(
                "Wartende={} → expected Ampel class {}", count, expected);
        assertWaitingTrafficLightClass(expected);
    }

    private static final By COUNTER_WAITING_COUNT = By.xpath(
            "//h4[contains(@class,'wartende')]/ancestor::li//span[contains(@class,'waiting-count')]");

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
