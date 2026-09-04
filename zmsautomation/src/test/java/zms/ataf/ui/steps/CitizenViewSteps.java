package zms.ataf.ui.steps;

import java.util.ArrayList;
import java.util.HashSet;
import java.util.List;
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
 * {@code features/ui/zmscitizenview/ServiceFinder.feature},
 * {@code zmskvr-1124_booking_ruppertstrasse_pass_calendar_jumpin_links.feature}, and
 * {@code zmskvr-1046_booking_ruppertstrasse_shared_booking_ausbildung.feature}.
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

    @When("I wait for appointment slots to be ready in the citizen view")
    public void iWaitForAppointmentSlotsToBeReadyInTheCitizenView() {
        ScenarioLogManager.getLogger().info("zmscitizenview: wait for appointment slots (spinner cleared)");
        page.waitUntilSlotsReadyForBooking();
    }

    @When("I click Später in the time slot grid if available in the citizen view")
    public void iClickSpaeterInTheTimeSlotGridIfAvailableInTheCitizenView() {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: Später beside time slot grid (later hour/day-part) if multi-provider UI");
        page.clickSpäterIfAvailableAndReloadSlots();
    }

    @When("I scroll to and highlight the preferred timeslot for office {int} in the citizen view")
    public void iScrollToAndHighlightPreferredTimeslotForOfficeInTheCitizenView(int officeId) {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: scroll + highlight preferred timeslot for office {} (screenshot before click)",
                        officeId);
        page.highlightPreferredTimeslotForOffice(officeId);
    }

    @When("I click the highlighted timeslot in the citizen view")
    public void iClickTheHighlightedTimeslotInTheCitizenView() {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: click highlighted timeslot (after highlight step screenshot)");
        page.clickHighlightedTimeslotSelection();
    }

    @When("I select a preferred timeslot below the calendar for office {int} in the citizen view")
    public void iSelectPreferredTimeslotBelowCalendarForOfficeInTheCitizenView(int officeId) {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: select preferred timeslot below calendar for office {}", officeId);
        page.selectPreferredTimeslotBelowCalendar(officeId);
    }

    @When("I continue after slot selection with Weiter for office {int} in the citizen view")
    public void iContinueAfterSlotSelectionWithWeiterForOfficeInTheCitizenView(int officeId) {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: Ausgewählter Termin + Weiter (reserve) for office {}", officeId);
        page.assertCalloutAndReserveAfterSlotSelection(officeId);
    }

    /**
     * Legacy single-step slot booking (all of the above in one step). Prefer the split steps in feature files for
     * clearer Cucumber reports and per-step screenshots.
     */
    @When("I choose the first slot below the calendar for office {int} and continue in the citizen view")
    public void iChooseFirstSlotBelowCalendarForOfficeAndContinue(int officeId) {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: choose first slot below calendar for office {} and Weiter (combined step)",
                        officeId);
        page.scrollClickFirstSlotAssertCalloutWeiter(officeId);
    }

    /**
     * Update-appointment (Kontakt) step only — run <em>after</em> reserve (first Weiter after slot). Fills form and
     * clicks Weiter to update; then preconfirm page with communication checkbox.
     */
    @When("I enter default contact details in the citizen view")
    public void iEnterDefaultContactDetails() {
        ScenarioLogManager.getLogger().info("zmscitizenview: fill default contact details and Weiter");
        page.fillContactDetailsRandom();
        page.clickWeiter(30);
        page.waitForPreconfirmPageAfterUpdate();
    }

    /**
     * ZMSKVR-833: first booking at a scope where custom text is optional — fill Kontakt but stay on
     * the form (no Weiter) so the missing Pflichtfeld is still empty for later rebooking.
     */
    @When("I enter contact details without optional remarks in the citizen view")
    public void iEnterContactDetailsWithoutOptionalRemarks() {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: fill contact details without optional Bemerkung (stay on Kontakt)");
        page.fillContactDetailsRandomWithoutOptionalRemarks();
    }

    /** ZMSKVR-833: rebooking reserve must land on Kontakt, not skip to Übersicht. */
    @Then("the contact form should be visible in the citizen view")
    public void theContactFormShouldBeVisibleInTheCitizenView() {
        ScenarioLogManager.getLogger().info("zmscitizenview: assert Kontakt form (Kontaktdaten) visible");
        page.assertContactFormVisible();
    }

    @Then("the filled name and email fields should be locked on the contact form in the citizen view")
    public void theFilledNameAndEmailFieldsShouldBeLockedOnTheContactForm() {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert Vorname/Nachname/E-Mail locked on rebooking Kontakt");
        page.assertFilledNameAndEmailLockedOnContactForm();
    }

    @Then("the required custom text field should be editable on the contact form in the citizen view")
    public void theRequiredCustomTextFieldShouldBeEditableOnTheContactForm() {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert required Bemerkung editable on rebooking Kontakt");
        page.assertRequiredCustomTextFieldEditableOnContactForm();
    }

    @When("I fill required custom text fields on the contact form in the citizen view")
    public void iFillRequiredCustomTextFieldsOnTheContactForm() {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: fill required Bemerkung on rebooking Kontakt");
        page.fillRequiredCustomTextFieldsOnContactForm();
    }

    @When("I accept communication in the citizen view")
    public void iAcceptCommunication() {
        ScenarioLogManager.getLogger().info("zmscitizenview: accept electronic communication");
        page.acceptCommunication();
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

    /** ZMSKVR-1500: reopen after success; leaves confirm hash first so the SPA remounts the route. */
    @When("I reopen the confirmation deep link in the browser")
    public void iReopenTheConfirmationDeepLinkInTheBrowser() {
        ScenarioLogManager.getLogger().info("zmscitizenview: reopen confirmation deep link in browser");
        page.reopenConfirmationDeepLinkInBrowser();
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

    /** ZMSKVR-1500 */
    @Then("the already activated appointment banner should be visible in the citizen view")
    public void theAlreadyActivatedAppointmentBannerShouldBeVisible() {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert already-activated appointment MucBanner success visible");
        page.assertAlreadyActivatedAppointmentBannerVisible();
    }

    /** ZMSKVR-1500: banner must not remain on the rebooking confirm summary. */
    @Then("the already activated appointment banner should not be visible in the citizen view")
    public void theAlreadyActivatedAppointmentBannerShouldNotBeVisible() {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert already-activated appointment MucBanner is hidden");
        page.assertAlreadyActivatedAppointmentBannerNotVisible();
    }

    /** ZMSKVR-1500: Termin verschieben from already-activated / appointment overview. */
    @When("I reschedule the appointment in the citizen view")
    public void iRescheduleTheAppointmentInTheCitizenView() {
        ScenarioLogManager.getLogger().info("zmscitizenview: reschedule appointment via Termin verschieben");
        page.clickRescheduleAppointment();
    }

    /** ZMSKVR-1500: rebooking confirm summary reached (Verschieben abbrechen shown). */
    @Then("the cancel reschedule button should be visible in the citizen view")
    public void theCancelRescheduleButtonShouldBeVisible() {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert cancel reschedule button (Verschieben abbrechen) visible");
        page.assertCancelRescheduleButtonVisible();
    }

    /** ZMSKVR-1500: Verschieben abbrechen → back to overview with already-activated banner. */
    @When("I cancel the reschedule in the citizen view")
    public void iCancelTheRescheduleInTheCitizenView() {
        ScenarioLogManager.getLogger().info("zmscitizenview: cancel reschedule via Verschieben abbrechen");
        page.clickCancelReschedule();
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

    @Then("timeslots for providers {string} should be visible in the citizen view")
    public void timeslotsForProvidersShouldBeVisible(String officeIdsCsv) {
        String raw = TestDataHelper.transformTestData(officeIdsCsv);
        List<Integer> ids = new ArrayList<>();
        for (String token : raw.split(",")) {
            String trimmed = token.trim();
            if (trimmed.isEmpty()) {
                continue;
            }
            ids.add(parseIntOrFail(trimmed, "officeId"));
        }
        int[] officeIds = ids.stream().mapToInt(Integer::intValue).toArray();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert timeslots present for providers {}", ids);
        page.assertTimeslotsPresentForProviders(officeIds);
    }

    @Then("timeslots for providers {string} should not appear in the citizen view")
    public void timeslotsForProvidersShouldNotAppear(String officeIdsCsv) {
        String raw = TestDataHelper.transformTestData(officeIdsCsv);
        List<Integer> ids = new ArrayList<>();
        for (String token : raw.split(",")) {
            String trimmed = token.trim();
            if (trimmed.isEmpty()) {
                continue;
            }
            ids.add(parseIntOrFail(trimmed, "officeId"));
        }
        int[] officeIds = ids.stream().mapToInt(Integer::intValue).toArray();
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert timeslots absent for providers {}", ids);
        page.assertTimeslotsAbsentForProviders(officeIds);
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
            allowedOfficeIds.add(parseIntOrFail(trimmed, "officeId"));
        }
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: keep only providers {} checked", allowedOfficeIds);
        page.keepOnlyProviderCheckboxesChecked(allowedOfficeIds);
    }

    private int parseIntOrFail(String value, String label) {
        try {
            return Integer.parseInt(value);
        } catch (NumberFormatException nfe) {
            throw new AssertionError("Failed to parse integer for " + label + " from value \"" + value + "\"", nfe);
        }
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

    @When("I fill contact details without continuing in the citizen view")
    public void iFillContactDetailsWithoutContinuing() {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: fill Kontakt form without Weiter (phone/Zusatzfelder)");
        page.fillContactDetailsRandomWithoutContinue();
    }

    @When("I continue from the contact form in the citizen view")
    public void iContinueFromTheContactFormInTheCitizenView() {
        ScenarioLogManager.getLogger().info("zmscitizenview: Kontakt Weiter → Übersicht");
        page.continueFromContactFormToSummary();
    }

    @Then("the telephone and custom text fields should remain visible with entered values in the citizen view")
    public void theTelephoneAndCustomTextFieldsShouldRemainVisibleWithEnteredValues() {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert phone + Zusatzfelder still visible with values");
        page.assertContactPhoneAndCustomFieldsVisibleWithValues();
    }

    @When("I log in via Bürger-Login with Keycloak in the citizen view")
    public void iLogInViaBuergerLoginWithKeycloak() throws Exception {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: Bürger-Login → Keycloak citizen user (dbs-fragments)");
        page.loginViaBuergerLoginWithKeycloak();
    }

    @Then("I should be logged in on the contact form in the citizen view")
    public void iShouldBeLoggedInOnTheContactForm() {
        ScenarioLogManager.getLogger().info("zmscitizenview: assert Sie sind angemeldet on Kontakt");
        page.assertCitizenLoggedInOnContactForm();
    }

    @Then("the booking summary should show Scheidplatz location for provider {int} in the citizen view")
    public void theBookingSummaryShouldShowScheidplatzLocation(int officeId) {
        ScenarioLogManager.getLogger()
                .info("zmscitizenview: assert Übersicht Ort for Scheidplatz provider {}", officeId);
        page.assertScheidplatzLocationOnSummary(officeId);
    }

    @When("I go back from the booking summary to the contact form in the citizen view")
    public void iGoBackFromTheBookingSummaryToTheContactForm() {
        ScenarioLogManager.getLogger().info("zmscitizenview: Zurück Übersicht → Kontakt");
        page.goBackFromBookingSummaryToContact();
    }
}
