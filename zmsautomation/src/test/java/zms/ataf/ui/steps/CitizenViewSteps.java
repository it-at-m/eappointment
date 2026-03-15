package zms.ataf.ui.steps;

import ataf.core.helpers.TestDataHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.web.utils.DriverUtil;
import io.cucumber.java.en.Given;
import io.cucumber.java.en.Then;
import io.cucumber.java.en.When;
import zms.ataf.ui.pages.citizenview.CitizenViewPage;

/**
 * All zmscitizenview UI steps (English). Service Finder smoke + full booking flow; see
 * {@code features/ui/zmscitizenview/ServiceFinder.feature} and
 * {@code zmskvr-1124_booking_ruppertstrasse_pass_calendar_jumpin_links.feature}.
 */
public class CitizenViewSteps {

    private final CitizenViewPage page;

    public CitizenViewSteps() {
        page = new CitizenViewPage(DriverUtil.getDriver());
    }

    @Given("I open the zmscitizenview booking page")
    public void iOpenTheZmscitizenviewBookingPage() {
        ScenarioLogManager.getLogger().info("zmscitizenview: open booking page (Service Finder)");
        page.navigateToPage();
    }

    @Then("the Service Finder should be visible on the start page")
    public void theServiceFinderShouldBeVisibleOnTheStartPage() {
        page.assertServiceFinderHeadingVisible();
    }

    @Given("I open zmscitizenview with jump-in service {string} and location {string}")
    public void iOpenZmscitizenviewWithJumpIn(String serviceId, String locationId) {
        String s = TestDataHelper.transformTestData(serviceId);
        String l = TestDataHelper.transformTestData(locationId);
        ScenarioLogManager.getLogger().info("zmscitizenview: jump-in service={} location={}", s, l);
        page.navigateWithJumpIn(s, l);
    }

    @Then("the service combination step should be visible")
    public void theServiceCombinationStepShouldBeVisible() {
        page.assertCombinationStepVisible();
    }

    @When("I continue from the service combination step")
    public void iContinueFromTheServiceCombinationStep() {
        ScenarioLogManager.getLogger().info("zmscitizenview: Weiter (combination → office/time)");
        page.clickWeiter();
    }

    @When("I select service {string} from the service finder and continue")
    public void iSelectServiceFromTheServiceFinderAndContinue(String serviceLabel) {
        page.selectServiceByLabel(TestDataHelper.transformTestData(serviceLabel));
    }

    @When("I select office {int} in the citizen view")
    public void iSelectOfficeInTheCitizenView(int officeId) {
        ScenarioLogManager.getLogger().info("zmscitizenview: select office {}", officeId);
        page.selectOfficeById(officeId);
        try {
            Thread.sleep(4000L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
    }

    @When("I choose the first slot below the calendar for office {int} and continue in the citizen view")
    public void iChooseFirstSlotBelowCalendarForOfficeAndContinue(int officeId) {
        page.scrollClickFirstSlotAssertCalloutWeiter(officeId);
    }

    /**
     * Update-appointment (Kontakt) step only — run <em>after</em> reserve (first Weiter after slot). Fills form and
     * clicks Weiter to update; then preconfirm page with privacy checkboxes.
     */
    @When("I enter default contact details in the citizen view")
    public void iEnterDefaultContactDetails() {
        page.fillContactDetailsRandom();
        page.clickWeiter(30);
        page.waitForPreconfirmPageAfterUpdate(15);
    }

    @When("I accept privacy and communication in the citizen view")
    public void iAcceptPrivacyAndCommunication() {
        page.acceptPrivacyAndCommunication();
    }

    /** Preconfirm page: privacy + this Weiter → activation callout (not before Kontakt). */
    @When("I continue from the preconfirm step in the citizen view")
    public void iContinueFromThePreconfirmStepInTheCitizenView() {
        page.continueFromPreconfirmStep();
    }

    @When("I reserve the appointment in the citizen view")
    public void iReserveTheAppointmentInTheCitizenView() {
        ScenarioLogManager.getLogger()
                .info(
                        "zmscitizenview: Termin reservieren (legacy); flow uses Weiter after slot to reserve — prefer that");
        page.clickReserveAppointment();
        try {
            Thread.sleep(5000L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
    }

    @Then("the preconfirmation callout should be visible in the citizen view")
    public void thePreconfirmationCalloutShouldBeVisible() {
        page.assertPreconfirmationCalloutVisible();
    }

    @When("I sync the booking process from citizen view localStorage")
    public void iSyncTheBookingProcessFromCitizenViewLocalStorage() throws Exception {
        page.syncBookingProcessFromLocalStorage();
    }

    @When("I open the confirmation deep link in the browser")
    public void iOpenTheConfirmationDeepLinkInTheBrowser() {
        page.openConfirmationDeepLinkInBrowser();
    }

    @Then("the confirmation success callout should be visible in the citizen view")
    public void theConfirmationSuccessCalloutShouldBeVisible() {
        page.assertConfirmationSuccessCalloutVisible();
    }

    @Then("the selected appointment callout should be visible in the citizen view")
    public void theSelectedAppointmentCalloutShouldBeVisible() {
        page.assertSelectedAppointmentCalloutVisible();
    }

    @Then("the invalid jump-in callout should be visible in the citizen view")
    public void theInvalidJumpinCalloutShouldBeVisible() {
        page.assertInvalidJumpinLinkCalloutVisible();
    }

    @Then("provider checkbox {int} should be visible in the citizen view")
    public void providerCheckboxShouldBeVisible(int officeId) {
        page.assertProviderCheckboxPresent(officeId);
    }

    @Then("provider checkbox {int} should not appear in the citizen view")
    public void providerCheckboxShouldNotAppear(int officeId) {
        try {
            Thread.sleep(2000L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
        page.assertProviderCheckboxAbsent(officeId);
    }

    @Then("the booking summary should show provider {int} in the citizen view")
    public void theBookingSummaryShouldShowProvider(int officeId) {
        page.assertProviderSummaryVisible(officeId);
    }

    @Then("only Pass calendar services should be offered on the combination step")
    public void onlyPassCalendarServicesOnCombinationStep() {
        page.assertPassOnlyCombinationServicesVisible();
    }
}
