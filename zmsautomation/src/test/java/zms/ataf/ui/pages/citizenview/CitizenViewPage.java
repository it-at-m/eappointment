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
     * We assert a few key German labels that come from ServiceFinder.vue /
     * de-DE.json so the test matches the actual UI:
     * - "Leistung" (t("service"))
     * - "Bürgerservice-Suche" (t("serviceSearch"))
     * - "Häufig gesuchte Leistungen" (t("oftenSearchedService"))
     */
    public void assertServiceFinderHeadingVisible() {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Checking that the Service Finder texts are visible on zmscitizenview.");

        // Main section heading
        boolean serviceHeadingVisible = isWebElementVisible(
                DEFAULT_EXPLICIT_WAIT_TIME,
                "//h2[normalize-space()='Leistung']",
                LocatorType.XPATH,
                false);
        Assert.assertTrue(
                serviceHeadingVisible,
                "Service Finder heading \"Leistung\" is not visible on the zmscitizenview start page.");

        // Search label
        boolean serviceSearchVisible = isWebElementVisible(
                DEFAULT_EXPLICIT_WAIT_TIME,
                "//*[normalize-space()='Bürgerservice-Suche']",
                LocatorType.XPATH,
                false);
        Assert.assertTrue(
                serviceSearchVisible,
                "\"Bürgerservice-Suche\" label is not visible on the zmscitizenview start page.");

        // Often searched services section
        boolean oftenSearchedVisible = isWebElementVisible(
                DEFAULT_EXPLICIT_WAIT_TIME,
                "//*[normalize-space()='Häufig gesuchte Leistungen']",
                LocatorType.XPATH,
                false);
        Assert.assertTrue(
                oftenSearchedVisible,
                "\"Häufig gesuchte Leistungen\" section heading is not visible on the zmscitizenview start page.");
    }
}

