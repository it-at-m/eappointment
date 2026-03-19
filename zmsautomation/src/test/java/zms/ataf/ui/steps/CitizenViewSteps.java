package zms.ataf.ui.steps;

import java.util.HashSet;
import java.util.Set;

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
        ScenarioLogManager.getLogger().info("zmscitizenview: assert Service Finder visible on start page");
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
        ScenarioLogManager.getLogger().info("zmscitizenview: assert service combination step visible");
        page.assertCombinationStepVisible();
    }

    @Then("the estimated duration on the service combination step should be {int} minutes")
    public void theEstimatedDurationOnTheServiceCombinationStepShouldBeMinutes(int minutes) {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert estimated duration {} minutes on combination step", minutes);
        page.assertEstimatedDurationMinutes(minutes, "service combination step");
    }

    @When("I add subservice {string} with quantity {int} on the service combination step")
    public void iAddSubserviceWithQuantityOnTheServiceCombinationStep(String subserviceLabel, int quantity) {
        String label = TestDataHelper.transformTestData(subserviceLabel);
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: add subservice '{}' with quantity {} on combination step", label, quantity);
        page.addSubserviceByName(label, quantity);
    }

    @When("I continue from the service combination step")
    public void iContinueFromTheServiceCombinationStep() {
        ScenarioLogManager.getLogger().info("zmscitizenview: Weiter (combination → office/time)");
        page.clickWeiter();
    }

    @When("I select service {string} from the service finder and continue")
    public void iSelectServiceFromTheServiceFinderAndContinue(String serviceLabel) {
        String label = TestDataHelper.transformTestData(serviceLabel);
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: select service '{}' from Service Finder and auto-continue", label);
        page.selectServiceByLabel(label);
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
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: choose first slot below calendar for office {} and Weiter", officeId);
        page.scrollClickFirstSlotAssertCalloutWeiter(officeId);
    }

    /**
     * Update-appointment (Kontakt) step only — run <em>after</em> reserve (first Weiter after slot). Fills form and
     * clicks Weiter to update; then preconfirm page with privacy checkboxes.
     */
    @When("I enter default contact details in the citizen view")
    public void iEnterDefaultContactDetails() {
        ScenarioLogManager.getLogger().info("zmscitizenview: fill default contact details and Weiter");
        page.fillContactDetailsRandom();
        page.clickWeiter(30);
        page.waitForPreconfirmPageAfterUpdate(15);
    }

    @When("I accept privacy and communication in the citizen view")
    public void iAcceptPrivacyAndCommunication() {
        ScenarioLogManager.getLogger().info("zmscitizenview: accept privacy policy and electronic communication");
        page.acceptPrivacyAndCommunication();
    }

    /** Preconfirm page: privacy + this Weiter → activation callout (not before Kontakt). */
    @When("I continue from the preconfirm step in the citizen view")
    public void iContinueFromThePreconfirmStepInTheCitizenView() {
        ScenarioLogManager.getLogger().info("zmscitizenview: preconfirm → Termin reservieren (activation)");
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

    @Then("the preconfirmation callout should be visible with activation time {int} minutes in the citizen view")
    public void thePreconfirmationCalloutShouldBeVisibleWithActivationTime(int activationMinutes) {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert preconfirmation callout visible with activation time {} minutes",
                        activationMinutes);
        page.assertPreconfirmationCalloutVisible(activationMinutes);
    }

    @When("I sync the booking process from citizen view localStorage")
    public void iSyncTheBookingProcessFromCitizenViewLocalStorage() throws Exception {
        ScenarioLogManager.getLogger().info("zmscitizenview: sync booking process from localStorage");
        page.syncBookingProcessFromLocalStorage();
    }

    @When("I open the confirmation deep link in the browser")
    public void iOpenTheConfirmationDeepLinkInTheBrowser() {
        ScenarioLogManager.getLogger().info("zmscitizenview: open confirmation deep link in browser");
        page.openConfirmationDeepLinkInBrowser();
    }

    @When("I open the appointment view deep link in the browser")
    public void iOpenTheAppointmentViewDeepLinkInTheBrowser() {
        ScenarioLogManager.getLogger().info("zmscitizenview: open appointment view deep link in browser");
        page.openAppointmentViewDeepLinkInBrowser();
    }

    @Then("the confirmation success callout should be visible in the citizen view")
    public void theConfirmationSuccessCalloutShouldBeVisible() {
        ScenarioLogManager.getLogger().info("zmscitizenview: assert confirmation success callout visible");
        page.assertConfirmationSuccessCalloutVisible();
    }

    @When("I cancel the appointment in the citizen view")
    public void iCancelTheAppointmentInTheCitizenView() {
        ScenarioLogManager.getLogger().info("zmscitizenview: cancel appointment via Termin absagen");
        page.clickCancelAppointmentAndConfirm();
    }

    @Then("the cancellation success callout should be visible in the citizen view")
    public void theCancellationSuccessCalloutShouldBeVisible() {
        ScenarioLogManager.getLogger().info("zmscitizenview: assert cancellation success callout visible");
        page.assertCancellationSuccessCalloutVisible();
    }

    @Then("the selected appointment callout should be visible in the citizen view")
    public void theSelectedAppointmentCalloutShouldBeVisible() {
        ScenarioLogManager.getLogger().info("zmscitizenview: assert 'Ausgewählter Termin' callout visible");
        page.assertSelectedAppointmentCalloutVisible();
    }

    @Then("the invalid jump-in callout should be visible in the citizen view")
    public void theInvalidJumpinCalloutShouldBeVisible() {
        ScenarioLogManager.getLogger().info("zmscitizenview: assert invalid jump-in error callout visible");
        page.assertInvalidJumpinLinkCalloutVisible();
    }

    @Then("provider checkbox {int} should be visible in the citizen view")
    public void providerCheckboxShouldBeVisible(int officeId) {
        ScenarioLogManager.getLogger().info("zmscitizenview: assert provider checkbox {} visible", officeId);
        page.assertProviderCheckboxPresent(officeId);
    }

    @Then("provider checkbox {int} should not appear in the citizen view")
    public void providerCheckboxShouldNotAppear(int officeId) {
        try {
            Thread.sleep(2000L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
        ScenarioLogManager.getLogger().info("zmscitizenview: assert provider checkbox {} NOT visible", officeId);
        page.assertProviderCheckboxAbsent(officeId);
    }

    @When("I keep only providers {string} checked in the citizen view")
    public void iKeepOnlyProvidersCheckedInTheCitizenView(String officeIdsCsv) {
        String raw = TestDataHelper.transformTestData(officeIdsCsv);
        Set<Integer> allowedOfficeIds = new HashSet<>();
        for (String token : raw.split(",")) {
            String trimmed = token.trim();
            if (trimmed.isEmpty()) {
                continue;
            }
            allowedOfficeIds.add(Integer.parseInt(trimmed));
        }
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: keep only providers {} checked", allowedOfficeIds);
        page.keepOnlyProviderCheckboxesChecked(allowedOfficeIds);
    }

    @Then("the booking summary should show provider {int} in the citizen view")
    public void theBookingSummaryShouldShowProvider(int officeId) {
        ScenarioLogManager.getLogger().info("zmscitizenview: assert booking summary shows provider {}", officeId);
        page.assertProviderSummaryVisible(officeId);
    }

    @Then("the estimated duration in the booking summary should be {int} minutes in the citizen view")
    public void theEstimatedDurationInTheBookingSummaryShouldBeMinutesInTheCitizenView(int minutes) {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert estimated duration {} minutes in booking summary", minutes);
        page.assertEstimatedDurationMinutes(minutes, "booking summary view");
    }

    @Then("the estimated duration in the confirmation view should be {int} minutes in the citizen view")
    public void theEstimatedDurationInTheConfirmationViewShouldBeMinutesInTheCitizenView(int minutes) {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert estimated duration {} minutes in confirmation view", minutes);
        page.assertEstimatedDurationMinutes(minutes, "confirmation view");
    }

    @Then("only Pass calendar services should be offered on the combination step")
    public void onlyPassCalendarServicesOnCombinationStep() {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert only Pass calendar services offered on combination step");
        page.assertPassOnlyCombinationServicesVisible();
    }
}
