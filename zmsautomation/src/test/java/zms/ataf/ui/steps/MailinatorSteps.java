package zms.ataf.ui.steps;

import org.testng.Assert;

import ataf.core.helpers.TestDataHelper;
import ataf.web.utils.DriverUtil;
import io.cucumber.java.de.Dann;
import io.cucumber.java.de.Wenn;
import zms.ataf.ui.pages.mailinator.MailinatorPage;
import zms.ataf.ui.pages.mailinator.MailinatorPageContext;


public class MailinatorSteps {
    private final MailinatorPage MAILINATOR_PAGE;

    public MailinatorSteps() {
        MAILINATOR_PAGE = new MailinatorPage(DriverUtil.getDriver());
    }

    @Wenn("Sie zur Webseite von Mailinator navigieren.")
    public void wenn_sie_zur_webseite_von_mailinator_navigieren() {
        MAILINATOR_PAGE.navigateToPage();
    }

    @Wenn("Sie auf " + MailinatorPageContext.NAME + " ins Textfeld Inbox die E-Mail-Adresse {string} eingeben.")
    public void wenn_sie_auf_mailinatordotcom_ins_textfeld_nbox_die_e_mail_adresse_string_eingeben(String email) {
        email = TestDataHelper.transformTestData(email);
        MAILINATOR_PAGE.enterInboxName(email);
    }

    @Wenn("Sie auf " + MailinatorPageContext.NAME + " auf den Button {string} klicken.")
    public void wenn_sie_auf_mailinatordotcom_auf_den_button_string_klicken(String button) {
        button = TestDataHelper.transformTestData(button);
        switch (button) {
        case "GO":
            MAILINATOR_PAGE.clickOnGoButton();
            break;
        default:
            throw new IllegalArgumentException("For button \"" + button + "\" no action is implemented yet!");
        }
    }

    @Dann("warten Sie auf die Nachricht mit dem Aktivierungslink für Ihren Termin.")
    public void dann_warten_sie_auf_die_nachricht_mit_dem_aktivierungslink_fuer_ihren_termin() {
        Exception exception = MAILINATOR_PAGE.waitForActivationMessage();
        if (exception != null) {
            Assert.fail("Activation message was not visible after waiting for " + MAILINATOR_PAGE.EMAIL_WAIT_TIME + " seconds!", exception);
        }
    }

    @Wenn("Sie nun die Nachricht öffnen.")
    public void wenn_sie_nun_die_nachricht_oeffnen() {
        MAILINATOR_PAGE.clickOnActivationMessage();
    }

    @Dann("sollten Sie den Aktivierungslink zu Ihrem gebuchten Termin finden können.")
    public void dann_sollten_sie_den_aktivierungslink_zu_ihrem_gebuchten_termin_finden_koennen() {
        MAILINATOR_PAGE.checkActivationMessageContents();
    }

    @Wenn("Sie auf den Aktivierungslink klicken.")
    public void wenn_sie_auf_den_aktivierungslink_klicken() {
        MAILINATOR_PAGE.clickOnActivationLink();
    }

    @Dann("Sie sollten nun eine E-Mail zur Terminbestätigung erhalten haben.")
    public void dann_sie_sollten_nun_eine_email_zur_terminbestaetigung_erhalten_haben() {
        MAILINATOR_PAGE.checkForConfirmationMessage();
    }

    @Dann("Sie sollten nun eine E-Mail zur Terminabsage erhalten haben.")
    public void dann_sie_sollten_nun_eine_email_zur_terminabsage_erhaltenhaben() {
        MAILINATOR_PAGE.checkForCancellationMessage();
    }
}
