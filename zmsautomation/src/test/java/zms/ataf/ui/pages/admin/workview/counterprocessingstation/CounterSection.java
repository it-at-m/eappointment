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

    private static final By COUNTER_WAITING_COUNT = By.xpath(
            "//div[contains(@class,'queue-info')]//h4[contains(@class,'wartende')]"
                    + "/ancestor::li//span[contains(@class,'waiting-count')]");

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
