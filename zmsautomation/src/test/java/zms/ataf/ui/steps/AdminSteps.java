package zms.ataf.ui.steps;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Locale;
import java.time.DayOfWeek;
import java.time.Duration;
import java.time.Instant;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.LocalTime;
import java.time.format.DateTimeFormatter;
import java.time.temporal.ChronoUnit;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.function.Supplier;

import org.openqa.selenium.By;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.assertions.CustomAssertions;
import ataf.core.helpers.TestDataHelper;
import ataf.core.helpers.TestPropertiesHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.core.properties.DefaultValues;
import ataf.web.controls.WindowControls;
import ataf.web.model.LocatorType;
import ataf.web.model.WindowType;
import ataf.web.steps.Hook;
import ataf.web.utils.DriverUtil;
import io.cucumber.datatable.DataTable;
import io.cucumber.java.en.And;
import io.cucumber.java.en.Given;
import io.cucumber.java.en.Then;
import io.cucumber.java.en.When;
import zms.ataf.helpers.AccountCheckout;
import zms.ataf.helpers.BerlinTime;
import zms.ataf.helpers.RandomNameHelper;
import zms.ataf.rest.steps.CitizenApiSteps;
import zms.ataf.ui.pages.admin.AdminPage;
import zms.ataf.ui.pages.admin.AdminPageContext;
import zms.ataf.ui.pages.admin.administration.AuthoritiesAndLocationsPage;
import zms.ataf.ui.pages.admin.search.CustomerSearchPage;
import zms.ataf.ui.pages.admin.workview.counterprocessingstation.CounterProcessingStationPage;
import zms.ataf.ui.pages.admin.workview.counterprocessingstation.CounterSection;
import zms.ataf.ui.pages.admin.workview.counterprocessingstation.ProcessingStationSection;


public class AdminSteps {
    private final AdminPage ADMIN_PAGE;
    private final CounterProcessingStationPage COUNTER_PROCESSING_STATION_PAGE;
    private final CounterSection COUNTER_SECTION;
    private final AuthoritiesAndLocationsPage AUTHORITIES_AND_LOCATIONS_PAGE;

    private final ProcessingStationSection PROCESSING_STATION_SECTION;
    private final CustomerSearchPage CUSTOMER_SEARCH_PAGE;
    private int rememberedWaitingClients;
    private int reservedScopeId;
    private String reservedAppointmentClock;

    public AdminSteps() {
        ADMIN_PAGE = new AdminPage(DriverUtil.getDriver());
        COUNTER_PROCESSING_STATION_PAGE = new CounterProcessingStationPage(DriverUtil.getDriver(), ADMIN_PAGE.getContext());
        CUSTOMER_SEARCH_PAGE = new CustomerSearchPage(DriverUtil.getDriver(), ADMIN_PAGE.getContext());
        AUTHORITIES_AND_LOCATIONS_PAGE = new AuthoritiesAndLocationsPage(DriverUtil.getDriver(), ADMIN_PAGE.getContext());
        PROCESSING_STATION_SECTION = new ProcessingStationSection(DriverUtil.getDriver(), ADMIN_PAGE.getContext());
        COUNTER_SECTION = new CounterSection(DriverUtil.getDriver(), ADMIN_PAGE.getContext());
    }

    @Then("I should be on the administration start page.")
    public void dann_sollten_sie_sich_am_start_des_zeitmanagementsystem_befinden() {
        Assert.assertEquals(WindowControls.getActiveWindow().getWindowTitle(), AdminPageContext.START_PAGE_TITLE,
                "This is not the start page of the \"" + AdminPageContext.NAME + "\"");
        WindowControls.getActiveWindow().setWindowType(WindowType.getSystemWindowType("Admin"));
    }

    @When("I click the button {string} in the administration.")
    public void wenn_sie_im_zeitmanagementsystem_auf_die_schaltflaeche_string_klicken(String button) throws Exception {
        button = TestDataHelper.transformTestData(button);
        switch (button) {
        case "Anmelden":
            ADMIN_PAGE.clickOnLoginButton();
            break;
        case "Auswahl bestätigen":
            ADMIN_PAGE.clickOnApplySelectionButton();
            break;
        case "neue Öffnungszeit":
            AUTHORITIES_AND_LOCATIONS_PAGE.clickOnNewOpeningHoursButton();
            break;
        case "Alle Änderungen aktivieren":
            AUTHORITIES_AND_LOCATIONS_PAGE.clickOnSaveButton();
            break;
        case "Aufruf nächster Kunde":
            PROCESSING_STATION_SECTION.callNextCustomer();
            break;
        case "Ja, Kunde erschienen":
            COUNTER_PROCESSING_STATION_PAGE.clickOnCustomerAppearedButton();
            break;
        case "Ja, Kunden jetzt aufrufen":
            PROCESSING_STATION_SECTION.confirmCustomerCall();
            break;
        case "Nein, nächster Kunde bitte":
            PROCESSING_STATION_SECTION.clickOnNoCallNextCustomerButton();
            break;
        case "Nein, nicht erschienen":
            PROCESSING_STATION_SECTION.clickOnCustomerDidNotAppearButton();
            break;
        case "Fertig stellen":
            COUNTER_PROCESSING_STATION_PAGE.clickOnFinishButton();
            break;
        case "Abbrechen":
            PROCESSING_STATION_SECTION.clickOnCancelAppointment();
            break;
        case "Termin ändern":
            COUNTER_PROCESSING_STATION_PAGE.clickOnChangeAppointmentButton();
            break;
        case "Termin bearbeiten":
            COUNTER_PROCESSING_STATION_PAGE.clickOnEditProcessButton();
            break;
        case "Vorgangsnummer drucken":
        case "Wartenummer drucken":
            COUNTER_PROCESSING_STATION_PAGE.clickOnPrintAppointmentNumberButton();
            break;
        case "Schließen":
            COUNTER_PROCESSING_STATION_PAGE.clickOnCloseButton();
            break;
        case "Ok":
            COUNTER_PROCESSING_STATION_PAGE.clickOnOkButton();
            break;
        case "Zurück zum aktuellen Vorgang":
            PROCESSING_STATION_SECTION.clickStayOnCurrentProcessInConfirmDialog();
            break;
        case "Aktuellen Termin fertig stellen und Kunden aufrufen":
            PROCESSING_STATION_SECTION.clickFinishAndCallSelectedInConfirmDialog();
            break;
        default:
            throw new IllegalArgumentException("For button \"" + button + "\" no action is implemented yet!");
        }
    }

    //TODO: 1
    @When("I click the button {string} in the administration navigation.")
    public void wenn_sie_im_zeitmanagementsystem_in_der_navigationsleiste_auf_die_schaltflaeche_string_klicken(String button) {
        switch (button) {
        case "Tresen":
            ADMIN_PAGE.clickInNavigationOnTresenButton();
            break;
        }
    }

    @When("I click the button {string} in the administration header.")
    public void wenn_sie_im_zeitmanagementsystem_in_der_kopfzeile_auf_die_schaltflaeche_string_klicken(String button) {
        switch (button) {
        case "Auswahl ändern":
            ADMIN_PAGE.clickInHeaderOnChangeSelectionButton();
            break;
        }
    }

    @When("I select for {string} the value {string}.")
    public void wenn_sie_fuer_string_den_wert_string_auswaehlen(String type, String value) {
        value = TestDataHelper.transformTestData(value);
        switch (type) {
        case "Standort":
            ADMIN_PAGE.selectLocation(value);
            break;
        case "Öffnungszeiten Anmerkung":
            AUTHORITIES_AND_LOCATIONS_PAGE.enterNoteForOpeningHours(value);
            break;
        case "Öffnungszeiten Typ":
            AUTHORITIES_AND_LOCATIONS_PAGE.selectOpeningHoursType(value);
            break;
        case "Serie":
            AUTHORITIES_AND_LOCATIONS_PAGE.selectSeries(value);
            break;
        default:
            throw new IllegalArgumentException("For drop down list of type \"" + type + "\" no action is implemented yet!");
        }
    }

    @When("I enter in the field {string} the text {string}.")
    public void wenn_sie_in_feld_string_den_text_string_eingeben(String field, String text) {
        text = TestDataHelper.transformTestData(text);
        if ("Datum bis".equals(field)) {
            text = resolveClosingDate(text);
        }
        switch (field) {
        case "Platz-Nr. oder Tresen":
            ADMIN_PAGE.enterWorkstation(text);
            break;
        case "Uhrzeit von":
            AUTHORITIES_AND_LOCATIONS_PAGE.enterOpeningTime(text);
            break;
        case "Uhrzeit bis":
            String closingTime = text;
            // Make the closing time robust against running the test late in the day:
            // if the feature specifies 17:00, interpret it as "now plus up to 8 hours,
            // but never later than 22:00" in Europe/Berlin.
            if ("17:00".equals(text)) {
                LocalTime now = BerlinTime.now().truncatedTo(ChronoUnit.MINUTES);
                LocalTime maxByOffset = now.plusHours(8);
                LocalTime latestAllowed = LocalTime.of(22, 0);
                LocalTime effective = maxByOffset.isBefore(latestAllowed) ? maxByOffset : latestAllowed;
                // Safety net: ensure we never go backwards in time
                if (effective.isBefore(now)) {
                    effective = latestAllowed;
                }
                int slotMinutes = AUTHORITIES_AND_LOCATIONS_PAGE.getOpeningHoursSlotTimeInMinutes();
                effective = roundUpToSlotTime(effective, slotMinutes, latestAllowed);
                closingTime = effective.format(DateTimeFormatter.ofPattern("HH:mm"));
            }
            AUTHORITIES_AND_LOCATIONS_PAGE.enterClosingTime(closingTime);
            break;
        case "Datum bis":
            AUTHORITIES_AND_LOCATIONS_PAGE.enterClosingDate(text);
            break;
        default:
            throw new IllegalArgumentException("For text field \"" + field + "\" no action is implemented yet!");
        }
    }

    private LocalTime roundUpToSlotTime(LocalTime time, int slotMinutes, LocalTime latestAllowed) {
        if (slotMinutes <= 1) {
            return time;
        }
        int minute = time.getMinute();
        int roundedMinute = ((minute + slotMinutes - 1) / slotMinutes) * slotMinutes;
        LocalTime rounded = time.withSecond(0).withNano(0);
        if (roundedMinute >= 60) {
            rounded = rounded.plusHours(1).withMinute(0);
        } else {
            rounded = rounded.withMinute(roundedMinute);
        }
        return rounded.isAfter(latestAllowed) ? latestAllowed : rounded;
    }

    //TODO: 1
    @When("I click the entry {string} in the Administration menu.")
    public void wenn_sie_unter_dem_menue_administration_auf_den_eintrag_string_klicken(String entry) {
        entry = TestDataHelper.transformTestData(entry);
        switch (entry) {
        case "Behörden und Standorte":
            AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationAdminEntry();
            break;
        default:
            throw new IllegalArgumentException("For entry \"" + entry + "\" no action is implemented yet!");
        }
    }

    @When("I set for location {string} the maximum bookable slots per appointment to {string}.")
    public void wenn_sie_fuer_den_standort_die_anzahl_an_maximal_buchbaren_slots_pro_termin_auf_setzen(String standort, String anzahl) {
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationEntry(standort);
        AUTHORITIES_AND_LOCATIONS_PAGE.setMaxSlotsForLocation(standort, anzahl);
        AUTHORITIES_AND_LOCATIONS_PAGE.saveLocationChanges();
    }

    @When("I set for location {string} the repeat calls to {string}.")
    public void wenn_sie_fuer_den_standort_die_wiederholungsaufrufe_auf_setzen(String standort, String anzahl) {
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationEntry(standort);
        AUTHORITIES_AND_LOCATIONS_PAGE.setRepeatCallsForLocation(standort, anzahl);
        AUTHORITIES_AND_LOCATIONS_PAGE.saveLocationChanges();
    }

    @When("I click the opening-hours entry of {string} under authorities and locations.")
    public void wenn_sie_unter_behoerden_und_standorte_auf_den_oeffnungszeiten_eintrag_von_string_klicken(String location) {
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnOpeningHoursEntryBy(TestDataHelper.transformTestData(location));
    }

    @When("I click the location {string} under authorities and locations.")
    public void wenn_sie_unter_behoerden_und_standorte_auf_den_standort_klicken(String location) {
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationEntry(location);
    }

    @And("I open the opening-hours accordion {string}.")
    public void und_sie_die_oeffnungszeit_accordion_oeffnen(String accordionTitle) {
        accordionTitle = TestDataHelper.transformTestData(accordionTitle);
        AUTHORITIES_AND_LOCATIONS_PAGE.expandOpeningHoursAccordionByTitle(accordionTitle);
    }

    @When("I click day {string} under opening hours.")
    public void wenn_sie_unter_oeffnungszeiten_auf_tag_string_klicken(String day) {
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnDayEntry(TestDataHelper.transformTestData(day));
    }

    @When("I select {string} under weekdays.")
    public void wenn_sie_string_unter_wochentage_selektieren(String weekDay) {
        AUTHORITIES_AND_LOCATIONS_PAGE.selectWeekDay(TestDataHelper.transformTestData(weekDay));
    }

    @And("I select Saturday and Sunday of the current week.")
    public void und_sie_die_wochentage_samstag_und_sonntag_der_aktuellen_woche_selektieren() {
        AUTHORITIES_AND_LOCATIONS_PAGE.selectWeekendDaysOfCurrentWeek();
    }

    @Then("no error about weekdays that do not occur should be shown.")
    public void dann_sollte_keine_fehlermeldung_zu_nicht_vorkommenden_wochentagen_angezeigt_werden() {
        AUTHORITIES_AND_LOCATIONS_PAGE.assertNoMissingWeekdayError();
    }

    @Then("the button {string} should be enabled for saving the opening hours.")
    public void dann_die_schaltflaeche_sollte_zum_speichern_der_oeffnungszeiten_aktiv_sein(String button) {
        AUTHORITIES_AND_LOCATIONS_PAGE.assertOpeningHoursSaveButtonEnabled(TestDataHelper.transformTestData(button));
    }

    private String resolveClosingDate(String text) {
        DateTimeFormatter format = DateTimeFormatter.ofPattern("dd.MM.yyyy");
        LocalDate today = BerlinTime.today();
        if ("<heute+14_tage>".equals(text)) {
            return today.plusDays(14).format(format);
        }
        if ("<sonntag_dieser_woche>".equals(text)) {
            return today.with(DayOfWeek.SUNDAY).format(format);
        }
        return text;
    }

    @When("I select for appointment desks under {string} the count {int}.")
    public void wenn_sie_fuer_terminarbeitsplaetze_unter_string_die_anzahl_int_auswaehlen(String type, int number) {
        type = TestDataHelper.transformTestData(type);
        String numberOfCounters;
        if (number == 1) {
            numberOfCounters = "1 Arbeitsplatz";
        } else {
            numberOfCounters = number + " Arbeitsplätze";
        }
        switch (type) {
        case "Insgesamt":
            AUTHORITIES_AND_LOCATIONS_PAGE.selectOverallAvailableCounters(numberOfCounters);
            break;
        case "Internet":
            AUTHORITIES_AND_LOCATIONS_PAGE.selectInternetAvailableCounters(numberOfCounters);
            break;
        default:
            throw new IllegalArgumentException("For counter drop down list of type \"" + type + "\" no action is implemented yet!");
        }
    }

    @Then("the queue should be displayed.")
    public void dann_sollte_ihnen_die_warteschlange_angezeigt_werden() {
        COUNTER_PROCESSING_STATION_PAGE.checkQueueElementsVisible();
    }

    @When("I click the {string} link in the administration.")
    public void wenn_sie_im_zeitmanagementsystem_auf_den_string_link_klicken(String linkName) {
        linkName = TestDataHelper.transformTestData(linkName);
        switch (linkName) {
        case "Wochenkalender":
            COUNTER_PROCESSING_STATION_PAGE.clickOnWeeklyCalendarLink();
            break;
        case "Sachbearbeiterplatz":
            COUNTER_PROCESSING_STATION_PAGE.clickOnWorkstationLink();
            break;
        default:
            throw new IllegalArgumentException("For link \"" + linkName + "\" no action is implemented yet!");
        }
    }

    @Then("the week calendar opens.")
    public void dann_oeffnet_sich_der_wochenkalender() {
        COUNTER_PROCESSING_STATION_PAGE.checkIfWeeklyCalendarIsVisible();
    }

    @Then("all booked and available appointments of the current calendar week are displayed.")
    public void dann_werden_alle_gebuchten_und_verfuegbaren_termine_der_aktuellen_kalenderwoche_angezeigt() {
        COUNTER_PROCESSING_STATION_PAGE.checkIfAllBookedAndFreeSlotsAreVisible();
    }

    @When("I call the citizen with appointment number {string}.")
    public void wenn_sie_nun_den_buerger_bzw_die_buergerin_mit_der_terminnummer_aufrufen(String appointmentNumber) {
        appointmentNumber = TestDataHelper.transformTestData(appointmentNumber);
        COUNTER_PROCESSING_STATION_PAGE.clickOnAppointmentNumberLink(appointmentNumber);
    }

    @Then("the customer information should be displayed.")
    public void dann_sollten_die_kundeninformationen_angezeigt_werden() {
        COUNTER_PROCESSING_STATION_PAGE.checkCustomerInformation();
    }

    @When("I open the administration website.")
    public void wenn_sie_zur_webseite_der_administration_navigieren() {
        ADMIN_PAGE.navigateToPage();
    }

    @When("I change the appointment with number {string} to the time {string} after calling the citizen.")
    public void wenn_sie_nach_anruf_des_buergers_bzw_buergerin_den_termin_mit_der_nummer_string_auf_die_zeit_string_anpassen(String appointmentNumber,
            String timeSlot) {
        COUNTER_PROCESSING_STATION_PAGE.clickOnAppointmentNumberEditLink(TestDataHelper.transformTestData(appointmentNumber));
        COUNTER_PROCESSING_STATION_PAGE.selectTimeSlot(TestDataHelper.transformTestData(timeSlot));
    }

    @When("I delete the appointment with number {string} in the administration.")
    public void wenn_sie_im_zeitmanagementsystem_den_termin_mit_der_nummer_loeschen(String appointmentNumber) {
        COUNTER_PROCESSING_STATION_PAGE.clickOnDeleteAppointmentLink(TestDataHelper.transformTestData(appointmentNumber));
    }

    @When("I book an appointment customer with service {string} and name {string}.")
    public void sie_einen_terminkunden_mit_der_dienstleistung_und_dem_namen_buchen(String service, String name) {
        wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_dienstleistung_string_auswaehlen(service);
        selectCounterAppointmentTimeOrWalkIn();
        wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_den_namen_string_eingeben(name);
        wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_email_adresse_string_eingeben("<mailinator>");
        bookNamedAppointmentOrWalkIn(TestDataHelper.getTestData("customer_name"), TestDataHelper.getTestData("customer_email"));
    }

    @When("I delete the just booked appointment of {string} from the queue.")
    public void sie_den_gerade_gebuchten_termin_in_der_warteschlange_loeschen(String familyName) {
        COUNTER_PROCESSING_STATION_PAGE.deleteQueuedAppointmentByFamilyName(TestDataHelper.transformTestData(familyName));
    }

    @When("I sign in to the administration as {string}.")
    public void sie_sich_als_im_zeitmanagementsystem_anmelden(String username) throws Exception {
        ADMIN_PAGE.loginWithKeycloakUser(
                AccountCheckout.assignWorkstationLogin(TestDataHelper.transformTestData(username)));
    }

    @When("I search for {string} in the customer search.")
    public void sie_in_der_kundensuche_nach_suchen(String query) {
        CUSTOMER_SEARCH_PAGE.search(TestDataHelper.transformTestData(query));
    }

    @Then("the clerk filter is not visible in the customer search.")
    public void ist_der_sachbearbeiter_filter_nicht_sichtbar() {
        CUSTOMER_SEARCH_PAGE.assertClerkFilterHidden();
    }

    @Then("the customer search lists {string} before {string} before {string}.")
    public void listet_die_kundensuche_in_reihenfolge(String first, String second, String third) {
        CUSTOMER_SEARCH_PAGE.assertListedInOrder(
                TestDataHelper.transformTestData(first),
                TestDataHelper.transformTestData(second),
                TestDataHelper.transformTestData(third));
    }

    @Then("the customer search lists these names:")
    public void listetDieKundensucheDieseNamen(DataTable table) {
        List<String> names = new ArrayList<>();
        for (List<String> row : table.asLists()) {
            names.add(TestDataHelper.transformTestData(row.get(0)));
        }
        CUSTOMER_SEARCH_PAGE.assertNames(names);
    }

    @Then("the customer search shows {string} with status {string}, booked today and without a call time.")
    public void zeigt_den_status_mit_heutiger_buchung(String familyName, String statusLabel) {
        CUSTOMER_SEARCH_PAGE.assertStatusWithoutCall(
                TestDataHelper.transformTestData(familyName),
                TestDataHelper.transformTestData(statusLabel),
                CUSTOMER_SEARCH_PAGE.bookingDateDaysAgo(0));
    }

    @Then("the customer search shows {string} with status {string}, booked {int} days ago at {string} and without a call time.")
    public void zeigt_den_status_mit_buchung_ohne_aufruf(
            String familyName, String statusLabel, int daysAgo, String time) {
        CUSTOMER_SEARCH_PAGE.assertStatusWithoutCall(
                TestDataHelper.transformTestData(familyName),
                TestDataHelper.transformTestData(statusLabel),
                CUSTOMER_SEARCH_PAGE.bookingStampDaysAgo(daysAgo, time));
    }

    @Then("the customer search shows {string} with status {string}, booked {int} days ago at {string} and called {int} days ago at {string}.")
    public void zeigt_den_status_mit_buchung_und_aufruf(
            String familyName, String statusLabel, int bookedDaysAgo, String bookedTime, int calledDaysAgo, String calledTime) {
        CUSTOMER_SEARCH_PAGE.assertStatusWithCall(
                TestDataHelper.transformTestData(familyName),
                TestDataHelper.transformTestData(statusLabel),
                CUSTOMER_SEARCH_PAGE.bookingStampDaysAgo(bookedDaysAgo, bookedTime),
                CUSTOMER_SEARCH_PAGE.bookingStampDaysAgo(calledDaysAgo, calledTime));
    }

    @When("I book a walk-in customer with service {string}, name {string}, free text {string} and second free text {string}.")
    public void sie_einen_spontankunden_mit_freitextfeldern_buchen(
            String service, String name, String freeText, String secondFreeText) {
        wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_dienstleistung_string_auswaehlen(service);
        COUNTER_PROCESSING_STATION_PAGE.selectWalkInCustomer();
        COUNTER_PROCESSING_STATION_PAGE.enterNameInNewAppointmentTextField(name);
        String email = name.toLowerCase(Locale.ROOT).replaceAll("[^a-z0-9]", "") + "@mailinator.com";
        COUNTER_PROCESSING_STATION_PAGE.enterEmailInNewAppointmentTextField(email);
        COUNTER_PROCESSING_STATION_PAGE.fillCustomTextfield("customTextfield", freeText);
        COUNTER_PROCESSING_STATION_PAGE.fillCustomTextfield("customTextfield2", secondFreeText);
        COUNTER_PROCESSING_STATION_PAGE.clickOnAddSpontaneousCustomer();
        try {
            wenn_sie_im_zeitmanagementsystem_auf_die_schaltflaeche_string_klicken("Schließen");
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    @When("I return to the workstation.")
    public void sie_zum_sachbearbeiterplatz_zurueckkehren() {
        ADMIN_PAGE.clickInNavigationOnWorkstation();
    }

    @Then("the appointment form for {string} shows a duration of {int} minutes and not {int} minutes.")
    public void zeigt_das_terminformular_die_dauer(String service, int minutes, int wrongMinutes) {
        COUNTER_PROCESSING_STATION_PAGE.assertAppointmentFormDuration(
                TestDataHelper.transformTestData(service), minutes, wrongMinutes);
    }

    @When("I book the already selected appointment for {string}.")
    public void sie_den_bereits_gewaehlten_termin_buchen(String name) {
        COUNTER_PROCESSING_STATION_PAGE.selectTimeInNewAppointmentDropDownList("<nächste>", java.util.Set.of(), false);
        String familyName = TestDataHelper.transformTestData(name);
        COUNTER_PROCESSING_STATION_PAGE.enterNameInNewAppointmentTextField(familyName);
        String email = familyName.toLowerCase(java.util.Locale.ROOT).replaceAll("[^a-z0-9]", "") + "@mailinator.com";
        COUNTER_PROCESSING_STATION_PAGE.enterEmailInNewAppointmentTextField(email);
        COUNTER_PROCESSING_STATION_PAGE.enterPhoneNumberInNewAppointmentTextField("+491234567890");
        COUNTER_PROCESSING_STATION_PAGE.clickOnBookAppointmentButton(true);
    }

    @When("I open the overall view.")
    public void sie_die_gesamtuebersicht_oeffnen() {
        ADMIN_PAGE.clickInNavigationOnOverallCalendar();
    }

    @Then("the overall view shows the just booked appointment with a duration of {int} minutes.")
    public void zeigt_die_gesamtuebersicht_die_dauer(int minutes) {
        COUNTER_PROCESSING_STATION_PAGE.assertOverallCalendarAppointmentSpansMinutes(minutes);
    }

    @Then("the customer search shows the customer {string}.")
    public void zeigt_die_kundensuche_den_kunden(String familyName) {
        CUSTOMER_SEARCH_PAGE.assertCustomerListed(TestDataHelper.transformTestData(familyName));
    }

    @Then("the customer search does not show the customer {string}.")
    public void zeigt_die_kundensuche_den_kunden_nicht(String familyName) {
        CUSTOMER_SEARCH_PAGE.assertCustomerNotListed(TestDataHelper.transformTestData(familyName));
    }

    @Then("the customer search shows {string} with status {string} and a booking and cancellation time.")
    public void zeigt_die_kundensuche_den_status_mit_zeiten(String familyName, String statusLabel) {
        CUSTOMER_SEARCH_PAGE.assertCancelledStatus(
                TestDataHelper.transformTestData(familyName),
                TestDataHelper.transformTestData(statusLabel));
    }

    private void bookNamedAppointmentOrWalkIn(String name, String email) {
        if (!"true".equals(TestDataHelper.getTestData("appointment_booked_as_walk_in"))
                && COUNTER_PROCESSING_STATION_PAGE.hasBookAppointmentButton()) {
            COUNTER_PROCESSING_STATION_PAGE.clickOnBookAppointmentButton(true);
            COUNTER_PROCESSING_STATION_PAGE.clickOnCloseButton();
            return;
        }
        if (!"true".equals(TestDataHelper.getTestData("appointment_booked_as_walk_in"))) {
            COUNTER_PROCESSING_STATION_PAGE.selectWalkInCustomer();
            COUNTER_PROCESSING_STATION_PAGE.enterNameInNewAppointmentTextField(name);
            COUNTER_PROCESSING_STATION_PAGE.enterEmailInNewAppointmentTextField(email);
        }
        String waitingNumber = COUNTER_PROCESSING_STATION_PAGE.clickOnAddSpontaneousCustomer();
        TestDataHelper.setTestData("new_appointment_number", waitingNumber);
        COUNTER_PROCESSING_STATION_PAGE.clickOnCloseButton();
    }

    @When("I enter the date {string} under create appointment in the administration.")
    public void wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_das_datum_string_eingeben(String date) {
        COUNTER_PROCESSING_STATION_PAGE.enterDateInNewAppointmentTextField(TestDataHelper.transformTestData(date));
    }

    @When("I select the time {string} under create appointment in the administration.")
    public void wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_zeit_string_auswaehlen(String time) {
        COUNTER_PROCESSING_STATION_PAGE.selectTimeInNewAppointmentDropDownList(TestDataHelper.transformTestData(time));
    }

    @When("I enter the name {string} under create appointment in the administration.")
    public void wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_den_namen_string_eingeben(String name) {
        name = TestDataHelper.transformTestData(name);
        if (name.equals("<zufällig>")) {
            name = RandomNameHelper.generateRandomName();
        }
        TestDataHelper.setTestData("customer_name", name);
        COUNTER_PROCESSING_STATION_PAGE.enterNameInNewAppointmentTextField(TestDataHelper.transformTestData(name));
    }

    @When("I enter the phone number {string} under create appointment in the administration.")
    public void wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_telefonnummer_string_eingeben(String phoneNumber) {
        COUNTER_PROCESSING_STATION_PAGE.enterPhoneNumberInNewAppointmentTextField(TestDataHelper.transformTestData(phoneNumber));
    }

    @When("I enter the email address {string} under create appointment in the administration.")
    public void wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_email_adresse_string_eingeben(String email) {
        email = TestDataHelper.transformTestData(email);
        if (email.equals("<mailinator>")) {
            if (TestDataHelper.getTestData("customer_name") != null) {
                email = RandomNameHelper.getEmailConformName(TestDataHelper.getTestData("customer_name")) + "@mailinator.com";
            } else {
                email = RandomNameHelper.randomAlphanumeric(8) + "@mailinator.com";
            }

            Assert.assertTrue(email.matches("^[\\w-.]+@([\\w-]+\\.)+[\\w-]{2,4}$"));
            COUNTER_PROCESSING_STATION_PAGE.enterEmailInNewAppointmentTextField(email);
        }
        TestDataHelper.setTestData("customer_email", email);
        COUNTER_PROCESSING_STATION_PAGE.enterEmailInNewAppointmentTextField(TestDataHelper.transformTestData(email));
    }

    @And("I enter the note {string} under create appointment in the administration.")
    public void wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_anmerkung_string_eingeben(String note) {
        COUNTER_PROCESSING_STATION_PAGE.enterNoteInNewAppointmentTextField(TestDataHelper.transformTestData(note));
    }

    @When("I select the service {string} under create appointment in the administration.")
    public void wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_dienstleistung_string_auswaehlen(String service) {
        COUNTER_PROCESSING_STATION_PAGE.selectServiceInNewAppointmentMultiList(TestDataHelper.transformTestData(service));
    }

    @When("I click the button {string} under create appointment in the administration.")
    public void wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_auf_die_schaltflaeche_string_klicken(String button) {
        button = TestDataHelper.transformTestData(button);
        switch (button) {
        case "Termin buchen":
            COUNTER_PROCESSING_STATION_PAGE.clickOnBookAppointmentButton(true);
            break;
        case "Spontankunden hinzufügen":
            COUNTER_PROCESSING_STATION_PAGE.clickOnAddSpontaneousCustomer();
            break;
        case "Liste leeren":
            COUNTER_PROCESSING_STATION_PAGE.clearSelectedServiceList();
            break;
        default:
            throw new IllegalArgumentException("For button \"" + button + "\" no action is implemented yet!");
        }
    }

    @When("I increase the count of the selected service {string} by {int}.")
    public void wenn_sie_die_anzahl_der_ausgewaehlten_dienstleistung_um_erhoehen(String service, int times) {
        COUNTER_PROCESSING_STATION_PAGE.increaseSelectedServiceCount(service, times);
    }

    @Then("the count of the selected service {string} is {int}.")
    public void dann_ist_die_anzahl_der_ausgewaehlten_dienstleistung(String service, int count) {
        COUNTER_PROCESSING_STATION_PAGE.assertSelectedServiceCount(service, count);
    }

    @Then("the service {string} is not in the selected list.")
    public void dann_ist_die_dienstleistung_nicht_in_der_ausgewaehlten_liste(String service) {
        COUNTER_PROCESSING_STATION_PAGE.assertSelectedServiceHidden(service);
    }

    @Then("the appointment confirmation can be printed.")
    public void dann_kann_die_terminbestaetigung_gedruckt_werden() {
        WindowControls.switchToOpenedWindow(DriverUtil.getDriver(),
                TestPropertiesHelper.getPropertyAsInteger("defaultExplicitWaitTime", true, DefaultValues.DEFAULT_EXPLICIT_WAIT_TIME),
                WindowType.UNKNOWN, "Vorgangsnummer drucken - Zeitmanagementsystem", false);
        Hook.makeScreenshot(DriverUtil.getDriver(), "Terminbestätigung_" + TestDataHelper.getTestData("new_appointment_number"));
        COUNTER_PROCESSING_STATION_PAGE.checkAppointmentConfirmationPrint();
    }

    @Then("the active opening hours should be deletable.")
    public void dann_sollte_die_aktivierte_oeffnungszeit_loeschbar_sein() {
        COUNTER_PROCESSING_STATION_PAGE.clickOnDeleteIcon();
    }

    @Then("the active opening hours with the note {string} should be deletable.")
    public void dann_sollte_die_aktivierte_oeffnungszeit_mit_anmerkung_loeschbar_sein(String anmerkung) {
        String note = TestDataHelper.transformTestData(anmerkung);
        // Behörden und Standorte > Öffnungszeiten uses a custom dialog, not a browser alert
        AUTHORITIES_AND_LOCATIONS_PAGE.clickDeleteOpeningHoursWithNote(note);
    }

    @When("I delete the opening hours of type {string}.")
    public void wenn_sie_die_oeffnungszeit_vom_typ_loeschen(String type) {
        AUTHORITIES_AND_LOCATIONS_PAGE.deleteOpeningHoursOfType(TestDataHelper.transformTestData(type));
    }

    @When("the customers already waiting are finished as no-shows.")
    public void die_bereits_wartenden_kunden_als_nicht_erschienen_abgeschlossen_werden() {
        PROCESSING_STATION_SECTION.dismissCustomersAlreadyWaiting();
    }

    @When("the clerk calls the waiting customer.")
    public void wenn_der_sachbearbeiter_den_wartenden_kunden_aufruft() {
        PROCESSING_STATION_SECTION.callNextCustomer();
        PROCESSING_STATION_SECTION.confirmCustomerCall();
    }

    @When("the clerk calls the appointment customer with the note {string}.")
    public void wenn_der_sachbearbeiter_den_termin_kunden_mit_der_anmerkung_aufruft(String anmerkung) {
        PROCESSING_STATION_SECTION.callCustomerWithSpecificNote(anmerkung);
    }

    @When("the clerk calls {string} from the waiting list.")
    public void wenn_der_sachbearbeiter_den_kunden_mit_der_nummer_aus_der_warteliste_aufruft(String nummer) {
        if ("true".equals(TestDataHelper.getTestData("appointment_booked_as_walk_in"))) {
            COUNTER_PROCESSING_STATION_PAGE.showSpontaneousCustomers(true);
        }
        PROCESSING_STATION_SECTION.callCustomerFromQueueWithNumber(TestDataHelper.transformTestData(nummer));
    }

    @When("the clerk calls the customer {string} from the waiting list.")
    public void wenn_der_sachbearbeiter_den_kunden_mit_dem_namen_aus_der_warteliste_aufruft(String name) {
        PROCESSING_STATION_SECTION.callCustomerFromQueueWithName(TestDataHelper.transformTestData(name));
    }

    @When("the clerk calls {string} from the parked appointments.")
    public void wenn_der_sachbearbeiter_den_kunden_mit_der_nummer_aus_den_geparkten_terminen_aufruft(String nummer) {
        PROCESSING_STATION_SECTION.callCustomerFromParkingTableWithNumber(TestDataHelper.transformTestData(nummer));
    }

    @Then("the entered workstation information is shown in the page header.")
    public void dann_werden_eingegebene_arbeitsplatzinformationen_im_seitenkopf_angezeigt() {
        ADMIN_PAGE.enteredWorkplaceInformationMatchWithPageHeader();
    }

    @Then("the waiting customer is called.")
    public void wird_der_wartende_kunde_aufgerufen() {
        PROCESSING_STATION_SECTION.validateCustomerCall();
    }

    @Then("the waiting customer {string} is called.")
    public void wird_der_wartende_kunde_mit_der_nummer_aufgerufen(String termin) {
        PROCESSING_STATION_SECTION.validateCustomerCallWithNumber(TestDataHelper.transformTestData(termin));
    }

    @Then("the counter page is opened.")
    public void dann_wird_die_seite_tresen_geoeffnet() {
        COUNTER_SECTION.checkInformationVisible();
        //TODO Die Spalten werden nicht mehr angezeigt, erst wenn termin vorhanden sind
        //COUNTER_PROCESSING_STATION_PAGE.checkQueueElementsVisibleWithoutSMS();
    }

    @Then("the select-location page opens.")
    public void dann_oeffnet_sich_die_standort_auswaehlen_seite() {
        ADMIN_PAGE.checkForLocationPage();
    }

    @When("the current number of waiting customers is remembered.")
    public void die_aktuelle_anzahl_der_wartenden_gemerkt_wird() {
        rememberedWaitingClients = COUNTER_PROCESSING_STATION_PAGE.readWaitingClientsEffective();
        ScenarioLogManager.getLogger().info("Wartende before the new queue entries: {}", rememberedWaitingClients);
    }

    @When("the current number of waiting customers under information is remembered.")
    public void die_aktuelle_anzahl_der_wartenden_unter_informationen_gemerkt_wird() {
        rememberedWaitingClients = COUNTER_SECTION.readWaitingClientsOnCounter();
        ScenarioLogManager.getLogger().info(
                "Wartende under Informationen before the appointment: {}", rememberedWaitingClients);
    }

    @Then("the remembered number of waiting customers under information has increased by {int} when the appointment minute is reached.")
    public void ist_die_anzahl_der_wartenden_mit_erreichen_der_terminminute_erhoeht(int increase) {
        long appointment;
        try {
            appointment = Long.parseLong(TestDataHelper.getTestData("appointment_epoch"));
        } catch (NumberFormatException exception) {
            throw new AssertionError("Appointment time was not stored", exception);
        }
        long now = Instant.now().getEpochSecond();
        if (appointment - now > 45) {
            int before = COUNTER_SECTION.reloadAndReadWaitingClientsOnCounter();
            Assert.assertEquals(
                    before,
                    rememberedWaitingClients,
                    "Wartende under Informationen rose before the appointment minute");
        }
        while (Instant.now().getEpochSecond() < appointment) {
            try {
                Thread.sleep(500L);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
                Assert.fail("Interrupted while waiting for the appointment minute");
            }
        }
        int after = COUNTER_SECTION.reloadAndReadWaitingClientsOnCounter();
        long checkedAt = Instant.now().getEpochSecond();
        Assert.assertTrue(
                checkedAt < appointment + 60,
                "The check finished after the appointment minute (epoch " + checkedAt + ")");
        Assert.assertEquals(
                after,
                rememberedWaitingClients + increase,
                "Wartende under Informationen at the appointment minute");
    }

    @Then("the remembered number of waiting customers increases within {int} seconds by {int} without a page reload.")
    public void steigt_die_gemerkte_anzahl_der_wartenden_ohne_seitenaktualisierung(
            int timeoutSeconds, int increase) {
        COUNTER_PROCESSING_STATION_PAGE.waitUntilWaitingClientsEffectiveAtLeast(
                rememberedWaitingClients + increase, timeoutSeconds);
    }

    @Then("the workstation page is displayed.")
    public void dann_wird_die_seite_sachbearbeiterplatz_geoeffnet() {
        PROCESSING_STATION_SECTION.checkCustomerCallVisible();
        //TODO Die Spalten werden nicht mehr angezeigt, erst wenn termin vorhanden sind
        //COUNTER_PROCESSING_STATION_PAGE.checkQueueElementsVisibleWithoutSMS();
    }

    @Then("the date is visible in the blue queue bar.")
    public void ist_das_datum_in_der_blauen_warteschlangenleiste_sichtbar() {
        COUNTER_PROCESSING_STATION_PAGE.assertQueueBarDateVisible();
    }

    @Then("the button {string} is visible in the blue queue bar.")
    public void ist_in_der_blauen_warteschlangenleiste_die_schaltflaeche_sichtbar(String label) {
        Assert.assertEquals(label, "Listen neu laden");
        COUNTER_PROCESSING_STATION_PAGE.assertListenNeuLadenVisible();
    }

    @Then("the button {string} below the queue is not visible.")
    public void ist_der_button_unter_der_warteschlange_nicht_sichtbar(String label) {
        Assert.assertEquals(label, "Warteschlange aktualisieren");
        COUNTER_PROCESSING_STATION_PAGE.assertWarteschlangeAktualisierenHidden();
    }

    @Then("{string} including the day navigation is not visible in the queue bar.")
    public void ist_heute_einschliesslich_der_tagesnavigation_nicht_sichtbar(String label) {
        Assert.assertEquals(label, "Heute");
        COUNTER_PROCESSING_STATION_PAGE.assertQueueBarDayNavigationHidden();
    }

    @Then("{string} is not visible in the queue bar.")
    public void ist_in_der_warteschlangenleiste_nicht_sichtbar(String label) {
        Assert.assertEquals(label, "Spontankunden einblenden");
        COUNTER_PROCESSING_STATION_PAGE.assertSpontankundenEinblendenHidden();
    }

    @Then("the queue download is not visible.")
    public void ist_der_download_der_warteschlange_nicht_sichtbar() {
        COUNTER_PROCESSING_STATION_PAGE.assertQueueDownloadHidden();
    }

    @Then("the queue print function is not visible.")
    public void ist_die_druckfunktion_der_warteschlange_nicht_sichtbar() {
        COUNTER_PROCESSING_STATION_PAGE.assertQueuePrintHidden();
    }

    @Then("the location dropdown is visible in the blue queue bar.")
    public void ist_das_standort_dropdown_in_der_blauen_warteschlangenleiste_sichtbar() {
        COUNTER_PROCESSING_STATION_PAGE.assertClusterScopeDropdownVisible();
    }

    @Then("the edit form for the walk-in customer is displayed.")
    public void wird_das_bearbeitungsformular_fuer_den_spontankunden_angezeigt() {
        COUNTER_PROCESSING_STATION_PAGE.checkProcessEditFormIsVisible();
    }

    @Then("the edit form for the appointment customer is displayed.")
    public void wird_das_bearbeitungsformular_fuer_den_terminkunden_angezeigt() {
        COUNTER_PROCESSING_STATION_PAGE.checkAppointmentEditFormIsVisible();
    }

    @When("I book a walk-in customer for the service {string}.")
    public void wenn_sie_einen_spontan_kunden_fuer_die_dienstleistung_buchen(String dienstleistung) {
        List<String> services = Arrays.asList(dienstleistung.split(",\\s*"));
        services.forEach(this::wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_dienstleistung_string_auswaehlen);
        wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_auf_die_schaltflaeche_string_klicken("Spontankunden hinzufügen");
        try {
            wenn_sie_im_zeitmanagementsystem_auf_die_schaltflaeche_string_klicken("Schließen");
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    @When("I book a walk-in customer for the service:")
    public void wenn_sie_einen_spontankunden_fuer_die_dienstleistung_buchen(DataTable dataTable) {
        List<Map<String, String>> services = dataTable.asMaps(String.class, String.class);

        for (Map<String, String> service : services) {
            String dienstleistung = service.get("Dienstleistung");
            String terminName = service.get("Termin name");
            String kunde = service.get("Kunde");

            // Dienstleistung auswählen
            wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_dienstleistung_string_auswaehlen(dienstleistung);

            // ✅ UPDATED: Use RandomNameHelper instead of RandomNameGenerator
            String randomName = RandomNameHelper.generateRandomName();
            TestDataHelper.setTestData(kunde, randomName);
            COUNTER_PROCESSING_STATION_PAGE.enterNameInNewAppointmentTextField(TestDataHelper.getTestData(kunde));
            String waitingNumber = COUNTER_PROCESSING_STATION_PAGE.clickOnAddSpontaneousCustomer();

            // Terminname und Wartenummer in TestDataHelper speichern
            TestDataHelper.setTestData(terminName, waitingNumber);

            try {
                wenn_sie_im_zeitmanagementsystem_auf_die_schaltflaeche_string_klicken("Schließen");
            } catch (Exception e) {
                throw new RuntimeException(e);
            }
            COUNTER_PROCESSING_STATION_PAGE.isCustomerVisibleInQueue(TestDataHelper.getTestData("new_waiting_number"), true);
        }
    }

    @When("I book an appointment customer for the service:")
    public void wenn_sie_einen_terminkunden_fuer_die_dienstleistung_buchen(DataTable dataTable) {
        List<Map<String, String>> services = dataTable.asMaps(String.class, String.class);
    
        for (Map<String, String> service : services) {
            String dienstleistung = service.get("Dienstleistung");
            String terminName    = service.get("Termin name");
            String kunde         = service.get("Kunde");
    
            // Dienstleistung auswählen
            wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_dienstleistung_string_auswaehlen(dienstleistung);
    
            // Zeitslot wählen. Ohne Termin heute wird ein Spontankunde gebucht, der noch aufgerufen werden kann.
            COUNTER_PROCESSING_STATION_PAGE.selectTimeInNewAppointmentDropDownList("<nächste>", java.util.Set.of(), true);
    
            // Name + E-Mail setzen (RandomNameHelper)
            String randomName = RandomNameHelper.generateRandomName();
            TestDataHelper.setTestData(kunde, randomName);
            COUNTER_PROCESSING_STATION_PAGE.enterNameInNewAppointmentTextField(TestDataHelper.getTestData(kunde));
    
            String emailSafeName = randomName.replaceAll("[^a-zA-Z0-9]", "").toLowerCase();
            COUNTER_PROCESSING_STATION_PAGE.enterEmailInNewAppointmentTextField(emailSafeName + "@mailinator.com");

            if ("true".equals(TestDataHelper.getTestData("appointment_booked_as_walk_in"))) {
                String waitingNumber = COUNTER_PROCESSING_STATION_PAGE.clickOnAddSpontaneousCustomer();
                TestDataHelper.setTestData(terminName, waitingNumber.replaceAll("\\D+", ""));
                try {
                    wenn_sie_im_zeitmanagementsystem_auf_die_schaltflaeche_string_klicken("Schließen");
                } catch (Exception e) {
                    throw new RuntimeException(e);
                }
                COUNTER_PROCESSING_STATION_PAGE.isCustomerVisibleInQueue(waitingNumber, true);
                continue;
            }
    
            // Buchen
            COUNTER_PROCESSING_STATION_PAGE.clickOnBookAppointmentButton(false);
    
            // NPE-Guard: Warten bis "new_appointment_number" vom UI-Parselogik gesetzt wurde
            String apptNo = null;
            for (int i = 0; i < 15; i++) { // ~4.5s max (15 * 300ms)
                apptNo = TestDataHelper.getTestData("new_appointment_number");
                if (apptNo != null && !apptNo.isBlank()) break;
                try {
                    Thread.sleep(300);
                } catch (InterruptedException ie) {
                    Thread.currentThread().interrupt();
                    throw new RuntimeException("Interrupted while waiting for appointment number", ie);
                }
            }
    
            if (apptNo == null || apptNo.isBlank()) {
                CustomAssertions.fail("No appointment number captured after booking. " +
                    "Ensure the success dialog appeared and the number was parsed before encrypting/storing. " +
                    "Terminname: " + terminName + ", Dienstleistung: " + dienstleistung);
                throw new AssertionError(
                        "Unreachable: failure did not throw (apptNo was null/blank)");
            }
    
            // Keep a scope prefix (Briefbüro "X0723"). A plain process id has no letters to drop.
            String apptNoShown = apptNo.trim();
            if (apptNoShown.replaceAll("\\D+", "").isBlank()) {
                CustomAssertions.fail("Captured appointment number contains no digits: '" + apptNo + "'");
                throw new AssertionError("Unreachable: failure did not throw (apptNoShown had no digits)");
            }
    
            TestDataHelper.setTestData(terminName, apptNoShown);
    
            // Dialog bestätigen
            try {
                wenn_sie_im_zeitmanagementsystem_auf_die_schaltflaeche_string_klicken("Schließen");
            } catch (Exception e) {
                throw new RuntimeException(e);
            }
    
            // Sichtbarkeit in der Warteschlange prüfen
            COUNTER_PROCESSING_STATION_PAGE.isCustomerVisibleInQueue(apptNoShown, false);
        }
    }

    @Then("the walk-in customer is shown in the queue.")
    public void wird_der_spontankunde_in_der_warteschlange_angezeigt() {
        COUNTER_PROCESSING_STATION_PAGE.isCustomerVisibleInQueue(TestDataHelper.getTestData("new_waiting_number"), true);
    }

    private void selectCounterAppointmentTimeOrWalkIn() {
        COUNTER_PROCESSING_STATION_PAGE.selectTimeInNewAppointmentDropDownList("<beliebig>", Set.of(), true);
    }

    private void confirmCounterAppointmentBooking() {
        if (!"true".equals(TestDataHelper.getTestData("appointment_booked_as_walk_in"))
                && COUNTER_PROCESSING_STATION_PAGE.hasBookAppointmentButton()) {
            COUNTER_PROCESSING_STATION_PAGE.clickOnBookAppointmentButton(true);
            return;
        }
        if (!"true".equals(TestDataHelper.getTestData("appointment_booked_as_walk_in"))) {
            COUNTER_PROCESSING_STATION_PAGE.selectWalkInCustomer();
        }
        String waitingNumber = COUNTER_PROCESSING_STATION_PAGE.clickOnAddSpontaneousCustomer();
        TestDataHelper.setTestData("new_appointment_number", waitingNumber);
    }

    @When("I book an appointment customer with the selected service, time, name and a valid email address.")
    public void wenn_sie_einen_terminkunden_mit_ausgewaehlter_dienstleistung_uhrzeit_name_und_gueltige_email_adresse_buchen() {
        wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_dienstleistung_string_auswaehlen("<beliebig>");
        selectCounterAppointmentTimeOrWalkIn();
        
        // ✅ UPDATED: Use RandomNameHelper instead of RandomNameGenerator
        String randomName = RandomNameHelper.generateRandomName();
        COUNTER_PROCESSING_STATION_PAGE.enterNameInNewAppointmentTextField(randomName);
        
        // Create email-safe version of the name
        String emailSafeName = randomName.replaceAll("[^a-zA-Z0-9]", "").toLowerCase();
        COUNTER_PROCESSING_STATION_PAGE.enterEmailInNewAppointmentTextField(emailSafeName + "@mailinator.com");
        
        confirmCounterAppointmentBooking();
    }

    @When("I book an appointment customer with service {string}, time, name and a valid email address.")
    public void wenn_sie_einen_terminkunden_mit_der_dienstleistung_uhrzeit_name_und_gueltige_email_adresse_buchen(String dienstleistungen) {
        List<String> services = Arrays.asList(dienstleistungen.split(",\\s*"));
        services.forEach(this::wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_dienstleistung_string_auswaehlen);
        selectCounterAppointmentTimeOrWalkIn();
        
        // ✅ UPDATED: Use RandomNameHelper instead of RandomNameGenerator
        String randomName = RandomNameHelper.generateRandomName();
        COUNTER_PROCESSING_STATION_PAGE.enterNameInNewAppointmentTextField(randomName);
        
        // Create email-safe version of the name
        String emailSafeName = randomName.replaceAll("[^a-zA-Z0-9]", "").toLowerCase();
        COUNTER_PROCESSING_STATION_PAGE.enterEmailInNewAppointmentTextField(emailSafeName + "@mailinator.com");
        
        confirmCounterAppointmentBooking();
    }

    @When("I book an appointment customer with service {string}, time, name, a valid email address and the note {string}.")
    public void wenn_sie_einen_terminkunden_mit_der_dienstleistung_uhrzeit_name_gueltige_email_adresse_und_die_anmerkung_buchen(String dienstleistungen,
            String anmerkung) {
        List<String> services = Arrays.asList(dienstleistungen.split(",\\s*"));
        services.forEach(this::wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_dienstleistung_string_auswaehlen);
        selectCounterAppointmentTimeOrWalkIn();
        
        // ✅ UPDATED: Use RandomNameHelper instead of RandomNameGenerator
        String randomName = RandomNameHelper.generateRandomName();
        COUNTER_PROCESSING_STATION_PAGE.enterNameInNewAppointmentTextField(randomName);
        
        // Create email-safe version of the name
        String emailSafeName = randomName.replaceAll("[^a-zA-Z0-9]", "").toLowerCase();
        COUNTER_PROCESSING_STATION_PAGE.enterEmailInNewAppointmentTextField(emailSafeName + "@mailinator.com");
        
        COUNTER_PROCESSING_STATION_PAGE.enterNoteInNewAppointmentTextField(anmerkung);
        confirmCounterAppointmentBooking();
    }

    @Then("a popup {string} appears and the appointment is also visible in the queue.")
    public void es_erscheint_ein_popup_fenster_und_der_termin_ist_auch_in_der_warteschlange_sichtbar(String popUpName) {
        boolean bookedAsWalkIn = "true".equals(TestDataHelper.getTestData("appointment_booked_as_walk_in"));
        String visiblePopup = bookedAsWalkIn ? "Spontankunde wurde erfolgreich eingetragen" : popUpName;
        Assert.assertTrue(ADMIN_PAGE.isPopUpVisible(visiblePopup), String.format("Popup '%s' is not visible!", visiblePopup));
        try {
            wenn_sie_im_zeitmanagementsystem_auf_die_schaltflaeche_string_klicken("Schließen");
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
        String queueNumber = bookedAsWalkIn
                ? TestDataHelper.getTestData("new_waiting_number")
                : TestDataHelper.getTestData("new_appointment_number");
        COUNTER_PROCESSING_STATION_PAGE.isCustomerVisibleInQueue(queueNumber, bookedAsWalkIn);

    }

    @When("I book an appointment customer with the selected service and time.")
    public void wenn_sie_einen_terminkunden_mit_ausgewaehlter_dienstleistung_und_uhrzeit_buchen_erscheinen_zwei_fehlermeldungen() {
        wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_dienstleistung_string_auswaehlen("<beliebig>");
        COUNTER_PROCESSING_STATION_PAGE.selectTimeInNewAppointmentDropDownList("<beliebig>");
        COUNTER_PROCESSING_STATION_PAGE.clickOnBookAppointmentButton(false);
    }

    @Then("two error messages highlighted in red appear for name and email address.")
    public void erscheinen_zwei_fehlermeldungen_die_bei_name_und_email_adresse_rot_hinterlegt_sind() {
        Assert.assertNotNull(TestDataHelper.getTestData("Fehler-Name"), "Error message for the name field is not visible!");
        Assert.assertNotNull(TestDataHelper.getTestData("Fehler-Email"), "Error message for the email field is not visible!");
    }

    @Then("the customer should have arrived and the appointment should be finished.")
    public void sollte_der_kunde_erschienen_sein_und_der_termin_fertig_gestellt() {
        PROCESSING_STATION_SECTION.clickOnYesCustomerAppeared();
        PROCESSING_STATION_SECTION.clickOnFinaliseAppointment();
    }

    @Then("the customer should not have arrived.")
    public void sollteDerKundeNichtErschienenSein() {
        PROCESSING_STATION_SECTION.clickOnNoCustomerDidNotAppear();
    }

    @Then("the maximum number of bookable slots per appointment for location {string} is limited to {string}.")
    public void fuer_den_standort_ist_die_maximale_anzahl_buchbarer_slots_pro_termin_begrenzt(String standort, String anzahl) {
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationAdminEntry();
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationEntry(standort);
        String found = AUTHORITIES_AND_LOCATIONS_PAGE.getMaxSlotsForLocation(standort);
        Assert.assertEquals(found, anzahl,
                "Expected maximum slots for location '" + standort + "' to be '" + anzahl + "', but found '" + found + "' instead.");
    }

    @Then("repeat calls for location {string} are limited to {string}.")
    public void fuer_den_standort_sind_die_wiederholungsaufrufe_begrenzt(String standort, String anzahl) {
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationAdminEntry();
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationEntry(standort);
        String found = AUTHORITIES_AND_LOCATIONS_PAGE.getRepeatCallsForLocation(standort);
        Assert.assertEquals(found, anzahl,
                "Expected 'Wiederholungsaufrufe' for location '" + standort + "' to be '" + anzahl + "', but found '" + found + "' instead.");
    }

    @When("I park the appointment.")
    public void wenn_sie_den_termin_parken() {
        PROCESSING_STATION_SECTION.clickOnParkAppointment();
    }

    @Then("the forwarded customer {string} has priority {string}.")
    public void hat_der_weitergeleitete_kunde_die_prioritaet(String kunde, String prioritaet) {
        COUNTER_PROCESSING_STATION_PAGE.assertQueuedCustomerPriority(
                TestDataHelper.transformTestData(kunde), prioritaet);
    }

    @When("I forward the appointment to {string} with the note {string}.")
    public void wenn_sie_den_termin_weiterleiten(String standort, String anmerkung) {
        PROCESSING_STATION_SECTION.clickOnForwardAppointment();
        PROCESSING_STATION_SECTION.selectLocationForAppointmentForwarding(standort);
        PROCESSING_STATION_SECTION.enterNoteForAppointmentForwarding(anmerkung);
        PROCESSING_STATION_SECTION.submitForwardAppointment();
    }

    @Then("the customer actions finish, forward, park and cancel are clickable.")
    public void sind_die_kundenaktionen_anklickbar() {
        PROCESSING_STATION_SECTION.assertCustomerActionsEnabled();
    }

    @Then("the customer actions finish, forward, park and cancel are disabled.")
    public void sind_die_kundenaktionen_gesperrt() {
        PROCESSING_STATION_SECTION.assertCustomerActionsDisabled();
    }

    @When("I open the forward form.")
    public void sie_die_weiterleitung_oeffnen() {
        PROCESSING_STATION_SECTION.clickOnForwardAppointment();
    }

    @Then("the forward form is visible.")
    public void ist_das_weiterleitungsformular_sichtbar() {
        PROCESSING_STATION_SECTION.assertForwardingFormVisible();
    }

    @Then("the blue cancel-forward button is visible.")
    public void ist_die_blaue_schaltflaeche_abbrechen_der_weiterleitung_sichtbar() {
        PROCESSING_STATION_SECTION.assertCancelForwardingButtonBlue();
    }

    @When("I cancel the forward.")
    public void sie_die_weiterleitung_abbrechen() {
        PROCESSING_STATION_SECTION.clickCancelForwarding();
    }

    @Then("the create-appointment form is visible.")
    public void ist_das_terminerstellungsformular_sichtbar() {
        PROCESSING_STATION_SECTION.assertAppointmentFormVisible();
    }

    @Then("the appointment {string} appears under parked appointments.")
    public void erscheint_der_termin_unter_geparkte_termine(String termin) {
        PROCESSING_STATION_SECTION.isCustomerVisibleInParkingTable(TestDataHelper.transformTestData(termin));
    }

    @Given("the finished appointment table is displayed.")
    public void sieDieFertigeTermintabelleAnzeigen() {
        COUNTER_PROCESSING_STATION_PAGE.showTheFinishedAppointmentTable();
    }

    private Duration parseHmsDuration(String hms, String label) {
        try {
            String[] parts = hms.split(":");
            if (parts.length != 3) {
                throw new IllegalArgumentException(
                        "Expected format H:mm:ss for " + label + ", but got \"" + hms + "\"");
            }
            return Duration.ofHours(Long.parseLong(parts[0]))
                    .plusMinutes(Long.parseLong(parts[1]))
                    .plusSeconds(Long.parseLong(parts[2]));
        } catch (NumberFormatException nfe) {
            throw new AssertionError("Failed to parse numeric values for " + label + " from \"" + hms + "\"", nfe);
        }
    }

    // Es geht über den Kundennamen
    @Then("the customer {string} should appear under finished appointments.")
    public void sollte_der_kunde_unter_abgeschlossene_termine_erscheinen(String kunde) {
        COUNTER_PROCESSING_STATION_PAGE.isCustomerVisibleInFinishedTable(TestDataHelper.transformTestData(kunde));
    }

    @Then("the customer {string} should appear under missed appointments.")
    public void sollte_der_kunde_unter_verpasste_termine_erscheinen(String termin) {
        String terminName = TestDataHelper.transformTestData(termin);
        COUNTER_PROCESSING_STATION_PAGE.isCustomerVisibleInMissedTable(terminName, true);
    }

    @Then("the customer {string} should appear in the waiting list.")
    public void sollte_der_kunde_in_der_warteliste_erscheinen(String kunde) {
        COUNTER_PROCESSING_STATION_PAGE.isCustomerVisibleInQueue(TestDataHelper.transformTestData(kunde), true);
    }

    @Then("the waiting time H:mm:ss for {string} should be between {string} and {string}.")
    public void die_wartezeit_fuer_den_gegebenen_kunden_sollte_zwischen_zwei_werte_liegen(String kunde, String minimaleWartezeit, String maximaleWartezeit) {
        ScenarioLogManager.getLogger().info("Verifying if waiting time for {} is between {} and {}.", kunde, minimaleWartezeit, maximaleWartezeit);
        Duration minDuration = parseHmsDuration(minimaleWartezeit, "minimaleWartezeit");
        Duration maxDuration = parseHmsDuration(maximaleWartezeit, "maximaleWartezeit");

        Duration effektiveWartezeit = COUNTER_PROCESSING_STATION_PAGE.getFinishedAppointmentWaitingTime(TestDataHelper.transformTestData(kunde));
        // Überprüfen, ob die effektive Wartezeit innerhalb des angegebenen Bereichs liegt
        String errorMessage = String.format(
                "The wait time for %s should be between %s and %s, but was actually %s.",
                TestDataHelper.transformTestData(kunde),
                minDuration.toString(),
                maxDuration.toString(),
                effektiveWartezeit.toString()
        );
        Assert.assertTrue(effektiveWartezeit.compareTo(minDuration) >= 0 && effektiveWartezeit.compareTo(maxDuration) <= 0, errorMessage);
    }

    @Then("the processing time H:mm:ss for {string} should be between {string} and {string}.")
    public void die_bearbeitungszeit_fuer_den_gegebenen_kunden_sollte_zwischen_zwei_werte_liegen(String kunde, String minimaleBearbeitungszeit,
            String maximaleBearbeitungszeit) {
        ScenarioLogManager.getLogger()
                .info("Verifying if processing time for {} is between {} and {}.", kunde, minimaleBearbeitungszeit, maximaleBearbeitungszeit);
        Duration minDuration = parseHmsDuration(minimaleBearbeitungszeit, "minimaleBearbeitungszeit");
        Duration maxDuration = parseHmsDuration(maximaleBearbeitungszeit, "maximaleBearbeitungszeit");

        Duration effektiveBearbeitungszeit = COUNTER_PROCESSING_STATION_PAGE.getFinishedAppointmentProcessingTime(TestDataHelper.transformTestData(kunde));
        // Überprüfen, ob die effektive Bearbeitungszeit innerhalb des angegebenen Bereichs liegt
        String errorMessage = String.format(
                "The processing time for %s should be between %s and %s, but was actually %s.",
                TestDataHelper.transformTestData(kunde),
                minDuration.toString(),
                maxDuration.toString(),
                effektiveBearbeitungszeit.toString()
        );
        Assert.assertTrue(effektiveBearbeitungszeit.compareTo(minDuration) >= 0 && effektiveBearbeitungszeit.compareTo(maxDuration) <= 0, errorMessage);
    }

    @When("I select {string} in the cluster-location dropdown in the location-table menu.")
    public void wenn_sie_in_der_menuezeile_der_standorttabellen_im_dropdown_clusterstandort_auswaehlen(String standort) {
        COUNTER_PROCESSING_STATION_PAGE.SelectClusterLocation(standort);
        COUNTER_PROCESSING_STATION_PAGE.confirmClusterLocationSelection();
    }

    @Then("the cluster view is activated.")
    public void wird_die_clusteransicht_aktiviert() {
        Assert.assertTrue(
                ADMIN_PAGE.isWebElementVisible(
                        TestPropertiesHelper.getPropertyAsInteger("defaultExplicitWaitTime", true, DefaultValues.DEFAULT_EXPLICIT_WAIT_TIME),
                        "//div[contains(@class, 'message') and contains(., 'Clusteransicht aktiviert')]",
                        LocatorType.XPATH,
                        false
                ),
                "'Clusteransicht aktiviert' message is not visible."
        );
    }

    @And("the queue shows the short codes of these cluster locations:")
    public void in_der_warteschlange_sind_die_kuerzeln_fuer_folgende_standorten_des_clusters_zu_sehen(DataTable dataTable) {
        List<String> codes = dataTable.asList(String.class);
        COUNTER_PROCESSING_STATION_PAGE.showSpontaneousCustomers(true);
        COUNTER_PROCESSING_STATION_PAGE.checkForValuesInQueueColumn("Kürzel", codes.toArray(new String[0]));
        ScenarioLogManager.getLogger().info("Termin_SG11: " + TestDataHelper.getTestData("Termin_SG11"));
        ScenarioLogManager.getLogger().info("Termin_SG12: " + TestDataHelper.getTestData("Termin_SG12"));
        ScenarioLogManager.getLogger().info("Termin_SG41: " + TestDataHelper.getTestData("Termin_SG41"));
        ScenarioLogManager.getLogger().info("Termin_SG42: " + TestDataHelper.getTestData("Termin_SG42"));
        ScenarioLogManager.getLogger().info("kunde_SG11: " + TestDataHelper.getTestData("kunde_SG11"));
        ScenarioLogManager.getLogger().info("kunde_SG12: " + TestDataHelper.getTestData("kunde_SG12"));
        ScenarioLogManager.getLogger().info("kunde_SG41: " + TestDataHelper.getTestData("kunde_SG41"));
        ScenarioLogManager.getLogger().info("kunde_SG42: " + TestDataHelper.getTestData("kunde_SG42"));
    }

    @Then("the cluster view is deactivated and the view for {string} is activated.")
    public void wird_die_clusteransicht_deaktiviert_und_die_ansicht_fuer_wird_aktiviert(String standort) {
        ADMIN_PAGE.getContext().waitForSpinners();

        // Dropdown definieren
        Supplier<WebElement> dropdownSupplier = () -> ADMIN_PAGE.findElementByLocatorType(
                "//section[contains(@class, 'board appointment-form')]//div[contains(@class, 'board__body')]/form/div[contains(@class, 'switchcluster ')]//select",
                LocatorType.XPATH,
                false
        );
        WebElement dropdown = dropdownSupplier.get();

        // Überprüfen, ob das Dropdown angezeigt wird
        Assert.assertTrue(dropdown.isDisplayed(), "Expected dropdown to be displayed, but it was not.");

        // Warten, bis das Dropdown mit Wiederholungen deaktiviert ist
        WebDriverWait wait = new WebDriverWait(DriverUtil.getDriver(),
                Duration.ofSeconds(TestPropertiesHelper.getPropertyAsInteger("defaultExplicitWaitTime", true, DefaultValues.DEFAULT_EXPLICIT_WAIT_TIME)));
        // Wiederholen
        boolean isDropdownDisabled = wait.until(driver -> {
            try {
                WebElement freshDropdown = dropdownSupplier.get();
                return "true".equals(freshDropdown.getAttribute("disabled"));
            } catch (StaleElementReferenceException e) {
                return false;
            }
        });
        Assert.assertTrue(isDropdownDisabled, "Expected dropdown to be disabled, but it was not.");
        Assert.assertTrue(dropdownSupplier.get().getText().contains(standort), "Expected dropdown to contain the text: " + standort + ", but it did not.");
    }

    @Given("the location has no appointments in the queue.")
    public void fuer_den_standort_sind_keine_termine_in_der_warteschlange_vorhanden() {
        COUNTER_PROCESSING_STATION_PAGE.isQueueEmpty();
    }

    @Then("the message that no waiting customers are present appears.")
    public void erscheint_die_meldung_dass_keine_wartenden_kunden_vorhanden_sind() {
        PROCESSING_STATION_SECTION.checkForNoWaitingCustomersMessage();
    }

    @And("the queue name field of {string} states how long until the customer {string} can be called again.")
    public void im_namensfeld_der_warteschlange_vom_steht_wie_lange_es_noch_dauert_bis_der_kunde_nochmals_aufgerufen_werden_kann(String kundenNummer,
            String kundenNamen) {
        String nummer = TestDataHelper.transformTestData(kundenNummer);
        String name = TestDataHelper.transformTestData(kundenNamen);
        String xpath = "//table[@id='table-queued-appointments']//tr[td[3][a[normalize-space(text())='" + nummer + "'] or normalize-space(text())='" + nummer + "']]/td[4]";
        ScenarioLogManager.getLogger().info("xpath: " + xpath);
        WebElement element = ADMIN_PAGE.findElementByLocatorType(xpath, LocatorType.XPATH, false);

        // Improved message for the first assertion
        ScenarioLogManager.getLogger().info("element.getText(): " + element.getText());
        Assert.assertTrue(element.getText().contains(name), "Expected  customer name [" + name + "] is not visible!");
        // Regex pattern to match the dynamic time and duration values
        String pattern = "war um \\d{2}:\\d{2}:\\d{2} Uhr nicht anwesend und kann in \\d{2}:\\d{2} Minuten wieder aufgerufen werden.";
        Assert.assertTrue(element.getText().matches(".*" + pattern + ".*"), "The text did not match the expected pattern: " + pattern);
    }

    @Then("the customer name {string} is shown under customer information.")
    public void wird_der_kundennamen_unter_kundeninformation_angezeigt(String name) {
        String kundenName = TestDataHelper.transformTestData(name);
        PROCESSING_STATION_SECTION.checkForCustomerNameUnderCustomerInformation(kundenName);
    }

    @Then("the waiting number {string} is shown under customer information.")
    public void wird_die_wartenummer_unter_kundeninformation_angezeigt(String nummer) {
        String wartenummer = TestDataHelper.transformTestData(nummer);
        PROCESSING_STATION_SECTION.checkForWaitingNumberUnderCustomerInformation(wartenummer);
    }

    @Then("the service {string} is shown under customer information.")
    public void wird_die_dienstleistung_unter_kundeninformation_angezeigt(String dienstleistung) {
        String service = TestDataHelper.transformTestData(dienstleistung);
        PROCESSING_STATION_SECTION.checkForServiceUnderCustomerInformation(service);
    }

    @And("the note {string} is shown under customer information.")
    public void wird_die_anmerkung_unter_kundeninformation_angezeigt(String anmerkung) {
        String note = TestDataHelper.transformTestData(anmerkung);
        PROCESSING_STATION_SECTION.checkForNoteUnderCustomerInformation(note);
    }

    @And("the phone number {string} is shown under customer information.")
    public void wirdDieTelefinnummerUnterKundeninformationAngezeigt(String telefon) {
        String nummer = TestDataHelper.transformTestData(telefon);
        PROCESSING_STATION_SECTION.checkForPhoneNumberUnderCustomerInformation(nummer);
    }

    @And("the email {string} is shown under customer information.")
    public void wird_die_email_unter_kundeninformation_angezeigt(String email) {
        String emailAddress = TestDataHelper.transformTestData(email);
        PROCESSING_STATION_SECTION.checkForEmailUnderCustomerInformation(emailAddress);
    }

    @And("the waiting time is shown under customer information.")
    public void wird_die_wartezeit_unter_kundeninformation_angezeigt() {
        PROCESSING_STATION_SECTION.checkForWaitingTimeUnderCustomerInformation();
    }

    @And("the time since the customer was called is shown under customer information.")
    public void wird_die_zeit_seit_kundenaufruf_unter_kundeninformation_angezeigt() {
        PROCESSING_STATION_SECTION.checkForTimeSinceCustomerCallUnderCustomerInformation();
    }

    @Then("the confirmation dialog for switching the queue customer appears.")
    public void erscheint_das_bestaetigungsfenster_zum_wechsel_des_warteschlangen_kunden() {
        PROCESSING_STATION_SECTION.assertCallOtherProcessConfirmDialogVisible();
    }

    @Then("no confirmation dialog for switching the queue customer appears.")
    public void erscheint_kein_bestaetigungsfenster_zum_wechsel_des_warteschlangen_kunden() {
        PROCESSING_STATION_SECTION.assertCallOtherProcessConfirmDialogNotVisible();
    }

    @Then("the error that a process is already called appears.")
    public void erscheint_die_fehlermeldung_dass_bereits_ein_vorgang_aufgerufen_ist() {
        PROCESSING_STATION_SECTION.assertAlreadyCalledProcessErrorVisible();
    }

    @And("the button {string} is visible.")
    public void ist_die_schaltflaeche_sichtbar(String button) {
        if ("Ja, Kunde erschienen".equals(button)) {
            PROCESSING_STATION_SECTION.assertCustomerAppearedButtonVisible();
            return;
        }
        throw new IllegalArgumentException("For button \"" + button + "\" no visibility check is implemented yet!");
    }

    @And("I finish the statistics processing if it is open.")
    public void sie_ggf_die_statistikbearbeitung_abschliessen() {
        PROCESSING_STATION_SECTION.completeStatisticsFinishIfPresent();
    }

    @Then("the button {string} is shown in the statistics.")
    public void wird_die_schaltflaeche_in_der_statistik_angezeigt(String label) {
        PROCESSING_STATION_SECTION.assertStatisticToggleLabel(label);
    }

    @When("I click {string} in the statistics.")
    public void sie_in_der_statistik_auf_klicken(String label) {
        if ("Bearbeitung abschließen".equals(label)) {
            PROCESSING_STATION_SECTION.submitStatisticsFinish();
            return;
        }
        PROCESSING_STATION_SECTION.clickStatisticToggle(label);
    }

    @Then("the service {string} is visible under record services.")
    public void ist_die_dienstleistung_unter_dienstleistungen_erfassen_sichtbar(String service) {
        PROCESSING_STATION_SECTION.assertScopeStatisticServiceVisible(service);
    }

    @Then("the service {string} is visible under further services.")
    public void ist_die_dienstleistung_unter_weitere_dienstleistungen_sichtbar(String service) {
        PROCESSING_STATION_SECTION.assertAdditionalStatisticServiceDisplayed(service, true);
    }

    @Then("the service {string} is not visible under further services.")
    public void ist_die_dienstleistung_unter_weitere_dienstleistungen_nicht_sichtbar(String service) {
        PROCESSING_STATION_SECTION.assertAdditionalStatisticServiceDisplayed(service, false);
    }

    @When("I increase the count of service {string} under further services by {int}.")
    public void sie_die_anzahl_der_dienstleistung_unter_weitere_dienstleistungen_erhoehen(String service, int times) {
        PROCESSING_STATION_SECTION.increaseAdditionalStatisticService(service, times);
    }

    @When("I click the button {string} in the location configuration.")
    public void wenn_sie_unter_der_standortkonfiguration_auf_die_schaltflaeche_klicken(String button) {
        button = TestDataHelper.transformTestData(button);
        switch (button) {
        case "löschen":
            AUTHORITIES_AND_LOCATIONS_PAGE.clickOnDeleteLocation();
            break;
        default:
            throw new IllegalArgumentException("For button \"" + button + "\" no action is implemented yet!");
        }
    }

    @Then("a popup {string} appears to delete the location.")
    public void erscheint_ein_popup_fenster_zum_loeschen_vom_standort(String expectedText) {
    
        ScenarioLogManager.getLogger().info(
            "Checking delete confirmation dialog with text: " + expectedText);
    
        WebDriverWait wait = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(15));
    
        // Target ONLY the delete lightbox dialog
        By dialogLocator = By.xpath(
            "//div[contains(@class,'lightbox')]//section[contains(@class,'dialog')]"
        );
    
        WebElement dialog = wait.until(
            ExpectedConditions.visibilityOfElementLocated(dialogLocator)
        );
    
        String actualText = dialog.getText().trim();
    
        Assert.assertTrue(
            actualText.contains(expectedText),
            "Pop-up message does not contain expected text.\nExpected: "
                    + expectedText + "\nActual: " + actualText
        );
    }

    @When("I set the email-confirmation value for the location to {word}.")
    public void wenn_sie_fuer_den_standort_den_wert_fuer_die_email_bestaetigung_auf_setzen(String flag) {
        boolean booleanFlag = Boolean.parseBoolean(flag);
        AUTHORITIES_AND_LOCATIONS_PAGE.setValueForEmailConfirmation(booleanFlag);
    }

    @When("I enter {string} in the location field for appointment-booking information in the citizen frontend.")
    public void wenn_sie_fuer_den_standort_ins_textfeld_info_zu_terminbuchung_in_buergerfrontend_eingeben(String text) {
        AUTHORITIES_AND_LOCATIONS_PAGE.enterInformationTextForAppointmentBookingInTheCitizenFrontend(text);
    }

    @When("I save the changes to the location configuration.")
    public void wenn_sie_die_aenderungen_an_der_standortkonfiguration_speichern() {
        AUTHORITIES_AND_LOCATIONS_PAGE.saveLocationChanges();
    }

    @And("I wait {string} minutes for the changes to be applied.")
    public void und_sie_minuten_bis_die_aenderungen_uebernommen_werden_warten(String minuten) {
        int minutes = Integer.parseInt(TestDataHelper.transformTestData(minuten));
        ScenarioLogManager.getLogger().info("Waiting {} minutes for changes to be applied...", minutes);
        try {
            Thread.sleep(minutes * 60L * 1000L);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
            throw new RuntimeException("Wait for " + minutes + " minutes was interrupted", e);
        }
    }

    @When("I wait {string} milliseconds.")
    public void iWaitMilliseconds(String milliseconds) {
        long millis = Long.parseLong(TestDataHelper.transformTestData(milliseconds));
        ScenarioLogManager.getLogger().info("Waiting for {} milliseconds...", millis);
        try {
            Thread.sleep(millis);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
            throw new RuntimeException("Wait for " + millis + " milliseconds was interrupted", e);
        }
    }

    @Then("^I wait \"(\\d+)\" minutes? for the changes to be applied\\.$")
    public void sie_minute_bis_die_aenderungen_uebernommen_werden_warten(String minutesText) {
        int minutes;
        try {
            minutes = Integer.parseInt(minutesText.trim());
        } catch (NumberFormatException e) {
            throw new IllegalArgumentException("Invalid minutes value: " + minutesText, e);
        }
        ScenarioLogManager.getLogger().info("Waiting " + minutes + " minute(s) for changes to propagate...");
        try {
            Thread.sleep(Duration.ofMinutes(minutes).toMillis());
        } catch (InterruptedException ie) {
            Thread.currentThread().interrupt();
            throw new RuntimeException("Interrupted while waiting for changes to propagate", ie);
        }
    }

    @Then("the default email confirmation for location {string} is set to {word}.")
    public void fuer_den_standort_ist_der_standardwert_fuer_die_email_bestaetigung_auf_gesetzt(String standort, String flag) {
        boolean booleanFlag = Boolean.parseBoolean(flag);
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationAdminEntry();
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationEntry(standort);
        WebElement checkbox = AUTHORITIES_AND_LOCATIONS_PAGE.findElementByLocatorType(
                "input[name='preferences[client][emailConfirmationActivated]'][type='checkbox']", LocatorType.CSSSELECTOR, true);
        boolean isChecked = checkbox.getAttribute("checked") != null;
        Assert.assertEquals(isChecked, booleanFlag, "Für Standort " + standort + " ist die E-Mail-Bestätigung nicht auf " + booleanFlag + " gesetzt.");
    }

    @Then("the email-confirmation checkbox is {string}.")
    public void ist_die_checkbox_mit_email_bestaetigungnicht_ausgewaehlt(String status) {
        boolean shouldBeSelected = status.equalsIgnoreCase("ausgewählt");
        WebElement checkbox = AUTHORITIES_AND_LOCATIONS_PAGE.findElementByLocatorType("input[value='1'][name='sendMailConfirmation']",
                LocatorType.CSSSELECTOR, true);
        boolean isSelected = checkbox.isSelected();
        Assert.assertEquals(
                isSelected,
                shouldBeSelected,
                "Erwartet wurde, dass die Checkbox Mit E-Mail Bestätigung " + (shouldBeSelected ? "ausgewählt" : "nicht ausgewählt") + " ist, aber sie ist " + (
                        isSelected ?
                                "ausgewählt" :
                                "nicht ausgewählt") + "."
        );
    }

    @Then("location {string} has the text {string} as appointment-booking information.")
    public void ist_fuer_den_standort_der_text_als_info_fuer_terminbuchung_vorhanden(String standort, String expectedText) {
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationAdminEntry();
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationEntry(standort);
        String text = AUTHORITIES_AND_LOCATIONS_PAGE.getWebElementText(
                TestPropertiesHelper.getPropertyAsInteger("defaultExplicitWaitTime", true, DefaultValues.DEFAULT_EXPLICIT_WAIT_TIME),
                "//textarea[@name='preferences[appointment][infoForAppointment]']", LocatorType.XPATH);
        Assert.assertEquals(text, expectedText,
                "Der Text für die Terminbuchung am Standort " + standort + " stimmt nicht überein. Erwartet: '" + expectedText + "', erhalten: '" + text + "'");
    }

    /**
     * The suite clock ({@code ZMS_TIMEADJUST}) does not move while the scenario waits.
     * A reserved Wohnsitzanmeldung is therefore placed two minutes before that clock, with a
     * stored waiting time of two minutes. A confirmed row would show {@code +2 Min.} in Uhrzeit.
     */
    @When("the reserved appointment is moved to two minutes before the suite clock.")
    public void theReservedAppointmentIsMovedToTwoMinutesBeforeTheSuiteClock() {
        var process = CitizenApiSteps.getBookingProcess();
        if (process == null || process.getProcessId() == null) {
            throw new IllegalStateException("No reserved appointment was captured from the citizen view.");
        }
        int processId = process.getProcessId();
        LocalDateTime suiteClock = suiteClock();
        LocalDateTime appointment = suiteClock.minusMinutes(2);
        if (appointment.toLocalDate().isBefore(suiteClock.toLocalDate())) {
            appointment = suiteClock.toLocalDate().atTime(0, 0, 1);
        }
        long gapSeconds = Duration.between(appointment, suiteClock).getSeconds();
        if (gapSeconds < 60) {
            throw new IllegalStateException(
                    "Suite clock " + suiteClock + " is in the first minute of the day, so a reserved appointment"
                            + " cannot be one minute overdue on the same day.");
        }
        reservedAppointmentClock = appointment.format(DateTimeFormatter.ofPattern("HH:mm"));
        String sql = """
                UPDATE buerger
                   SET Datum = ?, Uhrzeit = ?, waiting_time = '00:02:00'
                 WHERE BuergerID = ?
                   AND bestaetigt = 0
                   AND vorlaeufigeBuchung = 1
                """;
        try (Connection connection = openZmsConnection();
                PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setString(1, appointment.toLocalDate().toString());
            statement.setString(2, appointment.toLocalTime().format(DateTimeFormatter.ofPattern("HH:mm:ss")));
            statement.setInt(3, processId);
            int updated = statement.executeUpdate();
            if (updated != 1) {
                throw new IllegalStateException(
                        "Process " + processId + " is not a reserved appointment (updated rows: " + updated + ").");
            }
            try (PreparedStatement scope = connection.prepareStatement(
                    "SELECT StandortID FROM buerger WHERE BuergerID = ?")) {
                scope.setInt(1, processId);
                try (ResultSet rows = scope.executeQuery()) {
                    if (!rows.next()) {
                        throw new IllegalStateException("Process " + processId + " disappeared after the time update.");
                    }
                    reservedScopeId = rows.getInt(1);
                }
            }
        } catch (SQLException e) {
            throw new IllegalStateException("Could not move reserved appointment " + processId + " before the suite clock.", e);
        }
        ScenarioLogManager.getLogger().info(
                "Reserved process {} at scope {} is now at {} (suite clock {})",
                processId, reservedScopeId, reservedAppointmentClock, suiteClock);
    }

    @When("I sign in at the workstation of the reserved appointment.")
    public void iSignInAtTheWorkstationOfTheReservedAppointment() throws Exception {
        if (reservedScopeId <= 0) {
            throw new IllegalStateException("The reserved appointment has no Standort.");
        }
        wenn_sie_im_zeitmanagementsystem_auf_die_schaltflaeche_string_klicken("Anmelden");
        ADMIN_PAGE.selectLocationByScopeId(reservedScopeId);
        wenn_sie_in_feld_string_den_text_string_eingeben("Platz-Nr. oder Tresen", "1");
        wenn_sie_im_zeitmanagementsystem_auf_die_schaltflaeche_string_klicken("Auswahl bestätigen");
    }

    @Then("the reserved appointment shows no waiting time in the queue time column.")
    public void theReservedAppointmentShowsNoWaitingTimeInTheQueueTimeColumn() {
        if (reservedAppointmentClock == null) {
            throw new IllegalStateException("The reserved appointment time was not remembered.");
        }
        WebDriverWait wait = new WebDriverWait(DriverUtil.getDriver(), Duration.ofSeconds(30));
        wait.until(ExpectedConditions.presenceOfElementLocated(By.cssSelector(".queue-table")));
        List<WebElement> rows = DriverUtil.getDriver().findElements(By.cssSelector(".queue-table tr.reserved"));
        WebElement match = null;
        for (WebElement row : rows) {
            if (row.getText().contains(reservedAppointmentClock)) {
                match = row;
                break;
            }
        }
        Assert.assertNotNull(match,
                "Reserved appointment at " + reservedAppointmentClock + " is not in the queue.");
        List<WebElement> waitingTime = match.findElements(By.cssSelector(".queue-table-amendment-time"));
        Assert.assertTrue(waitingTime.isEmpty(),
                "Reserved appointment at " + reservedAppointmentClock
                        + " shows a waiting time in Uhrzeit: " + match.getText());
        Assert.assertFalse(match.getText().matches("(?s).*\\+\\s*\\d+\\s*Min\\..*"),
                "Reserved appointment at " + reservedAppointmentClock
                        + " shows +Min. in Uhrzeit: " + match.getText());
    }

    private static LocalDateTime suiteClock() {
        String adjusted = System.getenv("ZMS_TIMEADJUST");
        if (adjusted != null && !adjusted.isBlank()) {
            return LocalDateTime.parse(adjusted.trim(), DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss"));
        }
        return LocalDateTime.now(BerlinTime.ZONE);
    }

    private static Connection openZmsConnection() throws SQLException {
        String host = envOrDefault("MYSQL_HOST", "db");
        String port = mysqlPort(envOrDefault("MYSQL_PORT", "3306"));
        String database = envOrDefault("MYSQL_DATABASE", "db");
        String user = envOrDefault("MYSQL_USER", "db");
        String url = "jdbc:mysql://" + host + ":" + port + "/" + database;
        ScenarioLogManager.getLogger().info("Opening suite database {} as {}", url, user);
        return DriverManager.getConnection(url, user, envOrDefault("MYSQL_PASSWORD", "db"));
    }

    /** The wrapper exports {@code MYSQL_PORT} as {@code tcp://db:3306} or as a bare port. */
    private static String mysqlPort(String raw) {
        int colon = raw.lastIndexOf(':');
        if (colon >= 0 && colon < raw.length() - 1) {
            return raw.substring(colon + 1);
        }
        return raw;
    }

    private static String envOrDefault(String name, String fallback) {
        String value = System.getenv(name);
        if (value == null || value.isBlank()) {
            return fallback;
        }
        return value.trim();
    }
}
