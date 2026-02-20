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
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//h4[normalize-space()='Fiktive Arbeitsplätze:']", LocatorType.XPATH, false),
                "'Fiktive Arbeitsplätze' not visible.");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//h4[normalize-space()='Anzahl offener Vorgänge:']", LocatorType.XPATH, false),
                "'Anzahl offener Vorgänge:' not visible.");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//h4[normalize-space()='davon vor nächstem Spontankunden:']", LocatorType.XPATH, false),
                "'davon vor nächstem Spontankunden:' not visible.");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//h4[normalize-space()='Wartezeit für neue Spontankunden:']", LocatorType.XPATH,
                        false), "'Wartezeit für neue Spontankunden:' not visible.");
    }
}
