package zms.ataf.ui.steps;

import ataf.web.utils.DriverUtil;
import io.cucumber.java.de.Dann;
import io.cucumber.java.de.Wenn;
import zms.ataf.ui.pages.citizenview.CitizenViewPage;
import zms.ataf.ui.pages.citizenview.CitizenViewPageContext;

public class CitizenViewSteps {

    private final CitizenViewPage CITIZEN_VIEW_PAGE;

    public CitizenViewSteps() {
        CITIZEN_VIEW_PAGE = new CitizenViewPage(DriverUtil.getDriver());
    }

    @Wenn("Sie zur Webseite der " + CitizenViewPageContext.NAME + " navigieren.")
    public void sie_zur_webseite_der_citizenview_navigieren() {
        CITIZEN_VIEW_PAGE.navigateToPage();
    }

    @Dann("wird der Service Finder auf der Startseite der " + CitizenViewPageContext.NAME + " angezeigt.")
    public void wird_der_service_finder_auf_der_startseite_angezeigt() {
        CITIZEN_VIEW_PAGE.assertServiceFinderHeadingVisible();
    }
}

