package zms.ataf.ui.steps;

import org.testng.Assert;

import ataf.core.helpers.TestDataHelper;
import ataf.web.utils.DriverUtil;
import io.cucumber.java.en.Then;
import io.cucumber.java.en.When;
import zms.ataf.ui.pages.mailinator.MailinatorPage;


public class MailinatorSteps {
    private final MailinatorPage MAILINATOR_PAGE;

    public MailinatorSteps() {
        MAILINATOR_PAGE = new MailinatorPage(DriverUtil.getDriver());
    }

    @When("I open the Mailinator website.")
    public void wenn_sie_zur_webseite_von_mailinator_navigieren() {
        MAILINATOR_PAGE.navigateToPage();
    }

    @When("I enter the email address {string} in the inbox field on Mailinator.com.")
    public void wenn_sie_auf_mailinatordotcom_ins_textfeld_nbox_die_e_mail_adresse_string_eingeben(String email) {
        email = TestDataHelper.transformTestData(email);
        MAILINATOR_PAGE.enterInboxName(email);
    }

    @When("I click the button {string} on Mailinator.com.")
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

    @Then("I wait for the message with the activation link for the appointment.")
    public void dann_warten_sie_auf_die_nachricht_mit_dem_aktivierungslink_fuer_ihren_termin() {
        Exception exception = MAILINATOR_PAGE.waitForActivationMessage();
        if (exception != null) {
            Assert.fail("Activation message was not visible after waiting for " + MAILINATOR_PAGE.EMAIL_WAIT_TIME + " seconds!", exception);
        }
    }

    @When("I open the message.")
    public void wenn_sie_nun_die_nachricht_oeffnen() {
        MAILINATOR_PAGE.clickOnActivationMessage();
    }

    @Then("I should find the activation link for the booked appointment.")
    public void dann_sollten_sie_den_aktivierungslink_zu_ihrem_gebuchten_termin_finden_koennen() {
        MAILINATOR_PAGE.checkActivationMessageContents();
    }

    @When("I click the activation link.")
    public void wenn_sie_auf_den_aktivierungslink_klicken() {
        MAILINATOR_PAGE.clickOnActivationLink();
    }

    @Then("I should have received an appointment-confirmation email.")
    public void dann_sie_sollten_nun_eine_email_zur_terminbestaetigung_erhalten_haben() {
        MAILINATOR_PAGE.checkForConfirmationMessage();
    }

    @Then("I should have received an appointment-cancellation email.")
    public void dann_sie_sollten_nun_eine_email_zur_terminabsage_erhaltenhaben() {
        MAILINATOR_PAGE.checkForCancellationMessage();
    }
}
