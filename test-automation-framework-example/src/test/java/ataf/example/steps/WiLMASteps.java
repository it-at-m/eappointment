package ataf.example.steps;

import ataf.core.context.TestExecutionContext;
import ataf.core.data.Environment;
import ataf.core.helpers.AuthenticationHelper;
import ataf.core.helpers.TestDataHelper;
import ataf.core.helpers.TestPropertiesHelper;
import ataf.core.properties.DefaultValues;
import ataf.core.utils.RunnerUtils;
import ataf.example.data.TestData;
import ataf.example.pages.WiLMAPage;
import ataf.web.pages.SingleSignOnPage;
import ataf.web.utils.DriverUtil;
import io.cucumber.java.de.Angenommen;
import io.cucumber.java.de.Dann;
import io.cucumber.java.de.Und;
import io.cucumber.java.de.Wenn;
import org.testng.Assert;

/**
 * @author Ludwig Haas (ex.haas02)
 */
public class WiLMASteps {
    private final WiLMAPage WILMA_PAGE;

    public WiLMASteps() {
        WILMA_PAGE = new WiLMAPage(DriverUtil.getDriver());
    }

    @Angenommen("man befindet sich auf der WiLMA Startseite.")
    public void angenommenManBefindetSichAufDerWiLMAStartseite() {
        Environment environment;
        if (RunnerUtils.isJiraBasedTestExecution()) {
            environment = Environment.contains(TestExecutionContext.get().ENVIRONMENT);
        } else {
            environment = TestData.PRODUCTION;
        }
        Assert.assertNotNull(environment, "Test environment has not been set! Check your test execution issue. Valid environments: [PROD]");

        // Navigiere zur WiLMA Startseite
        WILMA_PAGE.navigateToPage(environment.getSystemUrl("WiLMA"));

        final StringBuilder clearUserName = new StringBuilder();
        final StringBuilder clearUserPassword = new StringBuilder();
        try {
            // Hole sicher die LDAP Credentials
            AuthenticationHelper.getUserName().access(clearUserName::append);
            AuthenticationHelper.getUserPassword().access(clearUserPassword::append);

            if (TestPropertiesHelper.getPropertyAsBoolean("useIncognitoMode", true, DefaultValues.USE_INCOGNITO_MODE)) {
                // Logge dich bei SSO ein
                new SingleSignOnPage(DriverUtil.getDriver()).executeSingleSignOnLogin(clearUserName.toString(), clearUserPassword.toString());
            }

        } finally {
            // Lösche LDAP Credentials
            clearUserName.delete(0, clearUserName.length() - 1);
            clearUserName.setLength(0);
            clearUserPassword.delete(0, clearUserPassword.length() - 1);
            clearUserPassword.setLength(0);
        }

        // Warte auf WiLMA Startseite
        WILMA_PAGE.waitForPage("WiLMA | Startseite");
    }

    @Wenn("Sie nun auf Ihr Avatar oben rechts klicken.")
    public void wennSieNunAufIhrAvatarObenRechtsKlicken() {
        WILMA_PAGE.clickOnAvatarIcon();
    }

    @Dann("sollte sich ein Menü öffnen.")
    public void dannSollteSichEinMenueOeffnen() {
        Assert.assertTrue(WILMA_PAGE.isUserMenuOpen(), "User menu is not visibly open!");
    }

    @Und("es sollte einen Menüeintrag {string} zu sehen sein.")
    public void undEsSollteEinenMenueeintragStringZuSehenSein(String menuEntry) {
        switch (TestDataHelper.transformTestData(menuEntry)) {
            case "Tour starten":
                Assert.assertTrue(WILMA_PAGE.isStartTourMenuEntryVisible(), "\"" + menuEntry + "\" menu entry is not visible!");
                break;
            default:
                throw new IllegalArgumentException("For menu entry \"" + menuEntry + "\" no check has been implemented yet!");
        }
    }

    @Wenn("Sie nun auf {string} klicken.")
    public void wennSieNunAufStringKlicken(String menuEntry) {
        switch (TestDataHelper.transformTestData(menuEntry)) {
            case "Tour starten":
                WILMA_PAGE.clickOnStartTourMenuEntry();
                break;
            default:
                throw new IllegalArgumentException("For menu entry \"" + menuEntry + "\" no action has been implemented yet!");
        }
    }

    @Dann("erscheint ein pop up.")
    public void dannErscheintEinPopUp() {
        Assert.assertTrue(WILMA_PAGE.isDialogPopUpVisible(), "Dialog pop up is not visible!");
    }

    @Wenn("Sie nun im pop up auf die Schaltfläche {string} klicken.")
    public void wennSieNunImPopUpAufDieSchaltflaecheKlicken(String buttonText) {
        buttonText = TestDataHelper.transformTestData(buttonText);
        switch (buttonText) {
            case "Hauptnavigation":
            case "Timeline":
            case "Private Nachrichten":
                WILMA_PAGE.clickOnPopUpButtonWithText(buttonText);
                break;
            case "Weiter":
                WILMA_PAGE.clickOnNextButton();
                break;
            case "Beenden":
                WILMA_PAGE.clickOnEndButton();
                break;
            default:
                throw new IllegalArgumentException("For button with text \"" + buttonText + "\" no action has been implemented yet!");
        }
    }

    @Dann("sollte die Tour starten.")
    public void dannSollteDieTourStarten() {
        WILMA_PAGE.checkIfTourHasStarted();
    }

    @Dann("sollte die Erläuterung zu {string} angezeigt werden.")
    public void dannSollteDieErlaeuterungZuAngezeigtWerden(String topic) {
        topic = TestDataHelper.transformTestData(topic);
        switch (topic) {
            case "Seiten":
                WILMA_PAGE.checkIfPagesTopicIsDisplayed();
                break;
            case "Arbeitsräume":
                WILMA_PAGE.checkIfWorkSpaceTopicIsDisplayed();
                break;
            case "Suche":
                WILMA_PAGE.checkIfSearchTopicIsDisplayed();
                break;
            case "Kolleg*innen":
                WILMA_PAGE.checkIfColleaguesTopicIsDisplayed();
                break;
            default:
                throw new IllegalArgumentException("For topic \"" + topic + "\" no check has been implemented yet!");
        }
    }

    @Dann("sollte die Tour beendet worden sein.")
    public void dannSollteDieTourBeendetWordenSein() {
        WILMA_PAGE.checkIfTourHasEnded();
    }
}
