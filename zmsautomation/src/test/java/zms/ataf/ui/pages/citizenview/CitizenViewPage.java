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
     * Basic smoke check that the Service Finder (appointment webcomponent) is
     * rendered on the start page.
     *
     * We assert:
     * - the root host element <zms-appointment-i18n-host> is visible
     * - the main Service Finder texts from ServiceFinder.vue / de-DE.json are present:
     *   - "Leistung" (t("service"))
     *   - "Bürgerservice-Suche" (t("serviceSearch"))
     *   - "Häufig gesuchte Leistungen" (t("oftenSearchedService"))
     */
    public void assertServiceFinderHeadingVisible() {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Checking that the zmscitizenview Service Finder is visible on the start page.");

        // Root host element of the appointment webcomponent hierarchy
        boolean hostVisible = isWebElementVisible(
                DEFAULT_EXPLICIT_WAIT_TIME,
                "//zms-appointment-i18n-host",
                LocatorType.XPATH,
                true);

        Assert.assertTrue(
                hostVisible,
                "Root element <zms-appointment-i18n-host> is not visible on the zmscitizenview start page.");

        // Main section heading "Leistung"
        boolean serviceHeadingVisible = isWebElementVisible(
                DEFAULT_EXPLICIT_WAIT_TIME,
                "//h2[normalize-space()='Leistung']",
                LocatorType.XPATH,
                false);
        Assert.assertTrue(
                serviceHeadingVisible,
                "Service Finder heading \"Leistung\" is not visible on the zmscitizenview start page.");

        // Search label "Bürgerservice-Suche"
        boolean serviceSearchVisible = isWebElementVisible(
                DEFAULT_EXPLICIT_WAIT_TIME,
                "//*[normalize-space()='Bürgerservice-Suche']",
                LocatorType.XPATH,
                false);
        Assert.assertTrue(
                serviceSearchVisible,
                "\"Bürgerservice-Suche\" label is not visible on the zmscitizenview start page.");

        // Section heading "Häufig gesuchte Leistungen"
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

