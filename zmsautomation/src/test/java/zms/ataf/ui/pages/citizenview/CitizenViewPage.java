package zms.ataf.ui.pages.citizenview;

import org.openqa.selenium.remote.RemoteWebDriver;
import org.testng.Assert;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.model.LocatorType;
import ataf.web.pages.BasePage;

public class CitizenViewPage extends BasePage {

    private final CitizenViewPageContext CONTEXT;

    public CitizenViewPage(RemoteWebDriver driver) {
        super(driver);
        CONTEXT = new CitizenViewPageContext(driver);
    }

    public CitizenViewPageContext getContext() {
        return CONTEXT;
    }

    public void navigateToPage() {
        CONTEXT.navigateToPage();
    }

    /**
     * Basic smoke check that the Service Finder is rendered on the start page.
     * We assert the translated heading "Leistung" from t("service") is visible.
     */
    public void assertServiceFinderHeadingVisible() {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Checking that the Service Finder heading is visible on ZMS-Citizen-View.");
        boolean visible = isWebElementVisible(
                DEFAULT_EXPLICIT_WAIT_TIME,
                "//h2[normalize-space()='Leistung']",
                LocatorType.XPATH,
                false);
        Assert.assertTrue(
                visible,
                "Service Finder heading \"Leistung\" is not visible on the ZMS-Citizen-View start page.");
    }
}

