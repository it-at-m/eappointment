package zms.ataf.ui.pages.admin.workview.counterprocessingstation;

import org.openqa.selenium.remote.RemoteWebDriver;
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
}
