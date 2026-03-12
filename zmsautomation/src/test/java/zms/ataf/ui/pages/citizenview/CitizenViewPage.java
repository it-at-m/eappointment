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
     * Basic smoke check that the Service Finder webcomponent is rendered on the
     * start page.
     *
     * For zmscitizenview we don't need to pierce into the Vue app; it is
     * sufficient to assert that the root host element for the appointment
     * component hierarchy is present and visible:
     *   <zms-appointment-i18n-host><zms-appointment-wrapped/></zms-appointment-i18n-host>
     */
    public void assertServiceFinderHeadingVisible() {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Checking that the zmscitizenview appointment webcomponent root is visible.");

        boolean hostVisible = isWebElementVisible(
                DEFAULT_EXPLICIT_WAIT_TIME,
                "//zms-appointment-i18n-host",
                LocatorType.XPATH,
                true);

        Assert.assertTrue(
                hostVisible,
                "Root element <zms-appointment-i18n-host> is not visible on the zmscitizenview start page.");
    }
}

