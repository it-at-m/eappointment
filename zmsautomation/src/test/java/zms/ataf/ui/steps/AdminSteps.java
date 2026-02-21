package zms.ataf.ui.steps;

import java.time.Duration;
import java.util.Arrays;
import java.util.List;
import java.util.Map;
import java.util.function.Supplier;

import org.apache.commons.lang3.RandomStringUtils;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.helpers.TestDataHelper;
import ataf.core.helpers.TestPropertiesHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.core.properties.DefaultValues;
import ataf.web.controls.WindowControls;
import ataf.web.model.LocatorType;
import ataf.web.model.WindowType;
import ataf.web.pages.RandomNameGenerator;
import ataf.web.steps.Hook;
import ataf.web.utils.DriverUtil;
import io.cucumber.datatable.DataTable;
import io.cucumber.java.de.Angenommen;
import io.cucumber.java.de.Dann;
import io.cucumber.java.de.Gegebenseien;
import io.cucumber.java.de.Und;
import io.cucumber.java.de.Wenn;
import zms.ataf.ui.pages.admin.AdminPage;
import zms.ataf.ui.pages.admin.AdminPageContext;
import zms.ataf.ui.pages.admin.adminstration.AuthoritiesAndLocationsPage;
import zms.ataf.ui.pages.admin.workview.counterprocessingstation.CounterProcessingStationPage;
import zms.ataf.ui.pages.admin.workview.counterprocessingstation.CounterSection;
import zms.ataf.ui.pages.admin.workview.counterprocessingstation.ProcessingStationSection;


public class AdminSteps {
    private final AdminPage ADMIN_PAGE;
    private final CounterProcessingStationPage COUNTER_PROCESSING_STATION_PAGE;
    private final CounterSection COUNTER_SECTION;
    private final AuthoritiesAndLocationsPage AUTHORITIES_AND_LOCATIONS_PAGE;

    private final ProcessingStationSection PROCESSING_STATION_SECTION;

    public AdminSteps() {
        ADMIN_PAGE = new AdminPage(DriverUtil.getDriver());
        COUNTER_PROCESSING_STATION_PAGE = new CounterProcessingStationPage(DriverUtil.getDriver(), ADMIN_PAGE.getContext());
        AUTHORITIES_AND_LOCATIONS_PAGE = new AuthoritiesAndLocationsPage(DriverUtil.getDriver(), ADMIN_PAGE.getContext());
        PROCESSING_STATION_SECTION = new ProcessingStationSection(DriverUtil.getDriver(), ADMIN_PAGE.getContext());
        COUNTER_SECTION = new CounterSection(DriverUtil.getDriver(), ADMIN_PAGE.getContext());
    }

    @Dann("sollten Sie sich am Start des " + AdminPageContext.NAME + " befinden.")
    public void dann_sollten_sie_sich_am_start_des_zeitmanagementsystem_befinden() {
        Assert.assertEquals(WindowControls.getActiveWindow().getWindowTitle(), AdminPageContext.START_PAGE_TITLE,
                "This is not the start page of the \"" + AdminPageContext.NAME + "\"");
        WindowControls.getActiveWindow().setWindowType(WindowType.getSystemWindowType("Admin"));
    }

    @Wenn("Sie im " + AdminPageContext.NAME + " auf die Schaltfläche {string} klicken.")
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
        case "Vorgangsnummer drucken":
            COUNTER_PROCESSING_STATION_PAGE.clickOnPrintAppointmentNumberButton();
            break;
        case "Schließen":
            COUNTER_PROCESSING_STATION_PAGE.clickOnCloseButton();
            break;
        case "Ok":
            COUNTER_PROCESSING_STATION_PAGE.clickOnOkButton();
            break;
        default:
            throw new IllegalArgumentException("For button \"" + button + "\" no action is implemented yet!");
        }
    }

    //TODO: 1
    @Wenn("Sie im " + AdminPageContext.NAME + " in der Navigationsleite auf die Schaltfläche {string} klicken.")
    public void wenn_sie_im_zeitmanagementsystem_in_der_navigationsleiste_auf_die_schaltflaeche_string_klicken(String button) {
        switch (button) {
        case "Tresen":
            ADMIN_PAGE.clickInNavigationOnTresenButton();
            break;
        }
    }

    @Wenn("Sie im " + AdminPageContext.NAME + " in der Kopfzeile auf die Schaltfläche {string} klicken.")
    public void wenn_sie_im_zeitmanagementsystem_in_der_kopfzeile_auf_die_schaltflaeche_string_klicken(String button) {
        switch (button) {
        case "Auswahl ändern":
            ADMIN_PAGE.clickInHeaderOnChangeSelectionButton();
            break;
        }
    }

    @Wenn("Sie für {string} den Wert {string} auswählen.")
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

    @Wenn("Sie in Feld {string} den Text {string} eingeben.")
    public void wenn_sie_in_feld_string_den_text_string_eingeben(String field, String text) {
        text = TestDataHelper.transformTestData(text);
        switch (field) {
        case "Platz-Nr. oder Tresen":
            ADMIN_PAGE.enterWorkstation(text);
            break;
        case "Uhrzeit von":
            AUTHORITIES_AND_LOCATIONS_PAGE.enterOpeningTime(text);
            break;
        case "Uhrzeit bis":
            AUTHORITIES_AND_LOCATIONS_PAGE.enterClosingTime(text);
            break;
        case "Datum bis":
            AUTHORITIES_AND_LOCATIONS_PAGE.enterClosingDate(text);
            break;
        default:
            throw new IllegalArgumentException("For text field \"" + field + "\" no action is implemented yet!");
        }
    }

    //TODO: 1
    @Wenn("Sie unter dem Menü Administration auf den Eintrag {string} klicken.")
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

    @Wenn("Sie für den Standort {string} die Anzahl an maximal buchbaren Slots pro Termin auf {string} setzen.")
    public void wenn_sie_fuer_den_standort_die_anzahl_an_maximal_buchbaren_slots_pro_termin_auf_setzen(String standort, String anzahl) {
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationEntry(standort);
        AUTHORITIES_AND_LOCATIONS_PAGE.setMaxSlotsForLocation(standort, anzahl);
        AUTHORITIES_AND_LOCATIONS_PAGE.saveLocationChanges();
    }

    @Wenn("Sie für den Standort {string} die Wiederholungsaufrufe auf {string} setzen.")
    public void wenn_sie_fuer_den_standort_die_wiederholungsaufrufe_auf_setzen(String standort, String anzahl) {
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationEntry(standort);
        AUTHORITIES_AND_LOCATIONS_PAGE.setRepeatCallsForLocation(standort, anzahl);
        AUTHORITIES_AND_LOCATIONS_PAGE.saveLocationChanges();
    }

    @Wenn("Sie unter Behörden und Standorte auf den Öffnungszeiten Eintrag von {string} klicken.")
    public void wenn_sie_unter_behoerden_und_standorte_auf_den_oeffnungszeiten_eintrag_von_string_klicken(String location) {
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnOpeningHoursEntryBy(TestDataHelper.transformTestData(location));
    }

    @Wenn("Sie unter Behörden und Standorte auf den Standort {string} klicken.")
    public void wenn_sie_unter_behoerden_und_standorte_auf_den_standort_klicken(String location) {
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationEntry(location);
    }

    @Wenn("Sie unter Öffnungszeiten auf Tag {string} klicken.")
    public void wenn_sie_unter_oeffnungszeiten_auf_tag_string_klicken(String day) {
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnDayEntry(TestDataHelper.transformTestData(day));
    }

    @Wenn("Sie {string} unter Wochentage selektieren.")
    public void wenn_sie_string_unter_wochentage_selektieren(String weekDay) {
        AUTHORITIES_AND_LOCATIONS_PAGE.selectWeekDay(TestDataHelper.transformTestData(weekDay));
    }

    @Wenn("Sie für Terminarbeitsplätze unter {string} die Anzahl {int} auswählen.")
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
        case "Callcenter":
            AUTHORITIES_AND_LOCATIONS_PAGE.selectCallcenterAvailableCounters(numberOfCounters);
            break;
        case "Internet":
            AUTHORITIES_AND_LOCATIONS_PAGE.selectInternetAvailableCounters(numberOfCounters);
            break;
        default:
            throw new IllegalArgumentException("For counter drop down list of type \"" + type + "\" no action is implemented yet!");
        }
    }

    @Dann("sollte Ihnen die Warteschlange angezeigt werden.")
    public void dann_sollte_ihnen_die_warteschlange_angezeigt_werden() {
        COUNTER_PROCESSING_STATION_PAGE.checkQueueElementsVisible();
    }

    @Wenn("Sie im " + AdminPageContext.NAME + " auf den {string} Link klicken.")
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

    @Dann("öffnet sich der Wochenkalender.")
    public void dann_oeffnet_sich_der_wochenkalender() {
        COUNTER_PROCESSING_STATION_PAGE.checkIfWeeklyCalendarIsVisible();
    }

    @Dann("werden alle gebuchten und verfügbaren Termine der aktuellen Kalenderwoche angezeigt.")
    public void dann_werden_alle_gebuchten_und_verfuegbaren_termine_der_aktuellen_kalenderwoche_angezeigt() {
        COUNTER_PROCESSING_STATION_PAGE.checkIfAllBookedAndFreeSlotsAreVisible();
    }

    @Wenn("Sie nun den Bürger bzw. die Bürgerin mit der Terminnummer {string} aufrufen.")
    public void wenn_sie_nun_den_buerger_bzw_die_buergerin_mit_der_terminnummer_aufrufen(String appointmentNumber) {
        appointmentNumber = TestDataHelper.transformTestData(appointmentNumber);
        COUNTER_PROCESSING_STATION_PAGE.clickOnAppointmentNumberLink(appointmentNumber);
    }

    @Dann("sollten die Kundeninformationen angezeigt werden.")
    public void dann_sollten_die_kundeninformationen_angezeigt_werden() {
        COUNTER_PROCESSING_STATION_PAGE.checkCustomerInformation();
    }

    @Wenn("Sie zur Webseite der Administration navigieren.")
    public void wenn_sie_zur_webseite_der_administration_navigieren() {
        ADMIN_PAGE.navigateToPage();
    }

    @Wenn("Sie nach Anruf des Bürgers bzw. Bürgerin den Termin mit der Nummer {string} auf die Zeit {string} anpassen.")
    public void wenn_sie_nach_anruf_des_buergers_bzw_buergerin_den_termin_mit_der_nummer_string_auf_die_zeit_string_anpassen(String appointmentNumber,
            String timeSlot) {
        COUNTER_PROCESSING_STATION_PAGE.clickOnAppointmentNumberEditLink(TestDataHelper.transformTestData(appointmentNumber));
        COUNTER_PROCESSING_STATION_PAGE.selectTimeSlot(TestDataHelper.transformTestData(timeSlot));
    }

    @Wenn("Sie im " + AdminPageContext.NAME + " den Termin mit der Nummer {string} löschen.")
    public void wenn_sie_im_zeitmanagementsystem_den_termin_mit_der_nummer_loeschen(String appointmentNumber) {
        COUNTER_PROCESSING_STATION_PAGE.clickOnDeleteAppointmentLink(TestDataHelper.transformTestData(appointmentNumber));
    }

    @Wenn("Sie im " + AdminPageContext.NAME + " unter Termin erstellen das Datum {string} eingeben.")
    public void wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_das_datum_string_eingeben(String date) {
        COUNTER_PROCESSING_STATION_PAGE.enterDateInNewAppointmentTextField(TestDataHelper.transformTestData(date));
    }

    @Wenn("Sie im " + AdminPageContext.NAME + " unter Termin erstellen die Zeit {string} auswählen.")
    public void wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_zeit_string_auswaehlen(String time) {
        COUNTER_PROCESSING_STATION_PAGE.selectTimeInNewAppointmentDropDownList(TestDataHelper.transformTestData(time));
    }

    @Wenn("Sie im " + AdminPageContext.NAME + " unter Termin erstellen den Namen {string} eingeben.")
    public void wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_den_namen_string_eingeben(String name) {
        name = TestDataHelper.transformTestData(name);
        if (name.equals("<zufällig>")) {
            RandomNameGenerator randomNameGenerator = new RandomNameGenerator(DriverUtil.getDriver());
            randomNameGenerator.setRandomName();
            name = randomNameGenerator.getName() + " " + randomNameGenerator.getSurname();
        }
        TestDataHelper.setTestData("customer_name", name);
        COUNTER_PROCESSING_STATION_PAGE.enterNameInNewAppointmentTextField(TestDataHelper.transformTestData(name));
    }

    @Wenn("Sie im " + AdminPageContext.NAME + " unter Termin erstellen die Telefonnummer {string} eingeben.")
    public void wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_telefonnummer_string_eingeben(String phoneNumber) {
        COUNTER_PROCESSING_STATION_PAGE.enterPhoneNumberInNewAppointmentTextField(TestDataHelper.transformTestData(phoneNumber));
    }

    @Wenn("Sie im " + AdminPageContext.NAME + " unter Termin erstellen die E-mail-Adresse {string} eingeben.")
    public void wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_email_adresse_string_eingeben(String email) {
        email = TestDataHelper.transformTestData(email);
        if (email.equals("<mailinator>")) {
            if (TestDataHelper.getTestData("customer_name") != null) {
                email = RandomNameGenerator.getEmailConformName(TestDataHelper.getTestData("customer_name")) + "@mailinator.com";
            } else {
                email = RandomStringUtils.randomAlphanumeric(8).toLowerCase() + "@mailinator.com";
            }

            Assert.assertTrue(email.matches("^[\\w-.]+@([\\w-]+\\.)+[\\w-]{2,4}$"));
            COUNTER_PROCESSING_STATION_PAGE.enterEmailInNewAppointmentTextField(email);
        }
        TestDataHelper.setTestData("customer_email", email);
        COUNTER_PROCESSING_STATION_PAGE.enterEmailInNewAppointmentTextField(TestDataHelper.transformTestData(email));
    }

    @Und("Sie im " + AdminPageContext.NAME + " unter Termin erstellen die Anmerkung {string} eingeben.")
    public void wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_anmerkung_string_eingeben(String note) {
        COUNTER_PROCESSING_STATION_PAGE.enterNoteInNewAppointmentTextField(TestDataHelper.transformTestData(note));
    }

    @Wenn("Sie im " + AdminPageContext.NAME + " unter Termin erstellen die Dienstleistung {string} auswählen.")
    public void wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_dienstleistung_string_auswaehlen(String service) {
        COUNTER_PROCESSING_STATION_PAGE.selectServiceInNewAppointmentMultiList(TestDataHelper.transformTestData(service));
    }

    @Wenn("Sie im " + AdminPageContext.NAME + " unter Termin erstellen auf die Schaltfläche {string} klicken.")
    public void wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_auf_die_schaltflaeche_string_klicken(String button) {
        button = TestDataHelper.transformTestData(button);
        switch (button) {
        case "Termin buchen":
            COUNTER_PROCESSING_STATION_PAGE.clickOnBookAppointmentButton(true);
            break;
        case "Spontankunden hinzufügen":
            COUNTER_PROCESSING_STATION_PAGE.clickOnAddSpontaneousCustomer();
            break;
        default:
            throw new IllegalArgumentException("For button \"" + button + "\" no action is implemented yet!");
        }
    }

    @Dann("kann die Terminbestätigung gedruckt werden.")
    public void dann_kann_die_terminbestaetigung_gedruckt_werden() {
        WindowControls.switchToOpenedWindow(DriverUtil.getDriver(),
                TestPropertiesHelper.getPropertyAsInteger("defaultExplicitWaitTime", true, DefaultValues.DEFAULT_EXPLICIT_WAIT_TIME),
                WindowType.UNKNOWN, "Vorgangsnummer drucken - Zeitmanagementsystem", false);
        Hook.makeScreenshot(DriverUtil.getDriver(), "Terminbestätigung_" + TestDataHelper.getTestData("new_appointment_number"));
        COUNTER_PROCESSING_STATION_PAGE.checkAppointmentConfirmationPrint();
    }

    @Dann("sollte die aktivierte Öffnungszeit löschbar sein.")
    public void dann_sollte_die_aktivierte_oeffnungszeit_loeschbar_sein() {
        COUNTER_PROCESSING_STATION_PAGE.clickOnDeleteIcon();
    }

    @Dann("sollte die aktivierte Öffnungszeit mit der Anmerkung {string} löschbar sein.")
    public void dann_sollte_die_aktivierte_oeffnungszeit_mit_anmerkung_loeschbar_sein(String anmerkung) {
        String noteKey = TestDataHelper.transformTestData(anmerkung);
        COUNTER_PROCESSING_STATION_PAGE.clickOnDeleteOpeningHoursWithNote(noteKey);
    }

    @Wenn("Der Sachbearbeiter den wartenden Kunden aufruft.")
    public void wenn_der_sachbearbeiter_den_wartenden_kunden_aufruft() {
        PROCESSING_STATION_SECTION.callNextCustomer();
        PROCESSING_STATION_SECTION.confirmCustomerCall();
    }

    @Wenn("Der Sachbearbeiter den Terminkunden mit der Anmerkung {string} aufruft.")
    public void wenn_der_sachbearbeiter_den_termin_kunden_mit_der_anmerkung_aufruft(String anmerkung) {
        PROCESSING_STATION_SECTION.callCustomerWithSpecificNote(anmerkung);
    }

    @Wenn("Der Sachbearbeiter {string} aus der Warteliste aufruft.")
    public void wenn_der_sachbearbeiter_den_kunden_mit_der_nummer_aus_der_warteliste_aufruft(String nummer) {
        PROCESSING_STATION_SECTION.callCustomerFromQueueWithNumber(TestDataHelper.transformTestData(nummer));
    }

    @Wenn("Der Sachbearbeiter den Kunden {string} aus der Warteliste aufruft.")
    public void wenn_der_sachbearbeiter_den_kunden_mit_dem_namen_aus_der_warteliste_aufruft(String name) {
        PROCESSING_STATION_SECTION.callCustomerFromQueueWithName(TestDataHelper.transformTestData(name));
    }

    @Wenn("Der Sachbearbeiter {string} aus den geparkten Terminen aufruft.")
    public void wenn_der_sachbearbeiter_den_kunden_mit_der_nummer_aus_den_geparkten_terminen_aufruft(String nummer) {
        PROCESSING_STATION_SECTION.callCustomerFromParkingTableWithNumber(TestDataHelper.transformTestData(nummer));
    }

    @Dann("werden die eingegebene Arbeitsplatzinformationen im Seitenkopf angezeigt.")
    public void dann_werden_eingegebene_arbeitsplatzinformationen_im_seitenkopf_angezeigt() {
        ADMIN_PAGE.enteredWorkplaceInformationMatchWithPageHeader();
    }

    @Dann("wird der wartende Kunde aufgerufen.")
    public void wird_der_wartende_kunde_aufgerufen() {
        PROCESSING_STATION_SECTION.validateCustomerCall();
    }

    @Dann("wird der wartende Kunde {string} aufgerufen.")
    public void wird_der_wartende_kunde_mit_der_nummer_aufgerufen(String termin) {
        PROCESSING_STATION_SECTION.validateCustomerCallWithNumber(TestDataHelper.transformTestData(termin));
    }

    @Dann("wird die Seite Tresen geöffnet.")
    public void dann_wird_die_seite_tresen_geoeffnet() {
        COUNTER_SECTION.checkInformationVisible();
        //TODO Die Spalten werden nicht mehr angezeigt, erst wenn termin vorhanden sind
        //COUNTER_PROCESSING_STATION_PAGE.checkQueueElementsVisibleWithoutSMS();
    }

    @Dann("öffnet sich die Standort auswählen Seite.")
    public void dann_oeffnet_sich_die_standort_auswaehlen_seite() {
        ADMIN_PAGE.checkForLocationPage();
    }

    @Dann("wird die Seite Sachbearbeiterplatz angezeigt.")
    public void dann_wird_die_seite_sachbearbeiterplatz_geoeffnet() {
        PROCESSING_STATION_SECTION.checkCustomerCallVisible();
        //TODO Die Spalten werden nicht mehr angezeigt, erst wenn termin vorhanden sind
        //COUNTER_PROCESSING_STATION_PAGE.checkQueueElementsVisibleWithoutSMS();
    }

    @Wenn("Sie einen Spontankunden für die Dienstleistung {string} buchen.")
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

    @Wenn("Sie einen Spontankunden für die Dienstleistung buchen:")
    public void wenn_sie_einen_spontankunden_fuer_die_dienstleistung_buchen(DataTable dataTable) {
        List<Map<String, String>> services = dataTable.asMaps(String.class, String.class);

        for (Map<String, String> service : services) {
            String dienstleistung = service.get("Dienstleistung");
            String terminName = service.get("Termin name");
            String kunde = service.get("Kunde");

            // Dienstleistung auswählen
            wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_dienstleistung_string_auswaehlen(dienstleistung);

            // Spontankunden hinzufügen
            RandomNameGenerator randomNameGenerator = new RandomNameGenerator(DriverUtil.getDriver());
            randomNameGenerator.setRandomName();
            TestDataHelper.setTestData(kunde, randomNameGenerator.getName() + " " + randomNameGenerator.getSurname());
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

    @Wenn("Sie einen Terminkunden für die Dienstleistung buchen:")
    public void wenn_sie_einen_terminkunden_fuer_die_dienstleistung_buchen(DataTable dataTable) {
        List<Map<String, String>> services = dataTable.asMaps(String.class, String.class);

        for (Map<String, String> service : services) {
            String dienstleistung = service.get("Dienstleistung");
            String terminName = service.get("Termin name");
            String kunde = service.get("Kunde");

            // Dienstleistung auswählen
            wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_dienstleistung_string_auswaehlen(dienstleistung);

            // Zeitslot
            COUNTER_PROCESSING_STATION_PAGE.selectTimeInNewAppointmentDropDownList("<nächste>");

            // Terminkunden hinzufügen
            RandomNameGenerator randomNameGenerator = new RandomNameGenerator(DriverUtil.getDriver());
            randomNameGenerator.setRandomName();
            TestDataHelper.setTestData(kunde, randomNameGenerator.getName() + " " + randomNameGenerator.getSurname());
            COUNTER_PROCESSING_STATION_PAGE.enterNameInNewAppointmentTextField(TestDataHelper.getTestData(kunde));
            COUNTER_PROCESSING_STATION_PAGE.enterEmailInNewAppointmentTextField(TestDataHelper.getTestData(kunde) + "@mailinator.com");

            COUNTER_PROCESSING_STATION_PAGE.clickOnBookAppointmentButton(false);

            // Terminname und Wartenummer in TestDataHelper speichern
            TestDataHelper.setTestData(terminName, TestDataHelper.getTestData("new_appointment_number"));

            try {
                wenn_sie_im_zeitmanagementsystem_auf_die_schaltflaeche_string_klicken("Ok");
            } catch (Exception e) {
                throw new RuntimeException(e);
            }
            COUNTER_PROCESSING_STATION_PAGE.isCustomerVisibleInQueue(TestDataHelper.getTestData("new_appointment_number"), false);
        }
    }

    @Dann("wird der Spontankunden in der Warteschlange angezeigt.")
    public void wird_der_spontankunde_in_der_warteschlange_angezeigt() {
        COUNTER_PROCESSING_STATION_PAGE.isCustomerVisibleInQueue(TestDataHelper.getTestData("new_waiting_number"), true);
    }

    @Wenn("sie einen Terminkunden mit ausgewählter Dienstleistung, Uhrzeit, name und gültige E-Mail-Adresse buchen.")
    public void wenn_sie_einen_terminkunden_mit_ausgewaehlter_dienstleistung_uhrzeit_name_und_gueltige_email_adresse_buchen() {
        wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_dienstleistung_string_auswaehlen("<beliebig>");
        COUNTER_PROCESSING_STATION_PAGE.selectTimeInNewAppointmentDropDownList("<beliebig>");
        RandomNameGenerator randomNameGenerator = new RandomNameGenerator(DriverUtil.getDriver());
        randomNameGenerator.setRandomName();
        COUNTER_PROCESSING_STATION_PAGE.enterNameInNewAppointmentTextField(randomNameGenerator.getName() + " " + randomNameGenerator.getSurname());
        COUNTER_PROCESSING_STATION_PAGE.enterEmailInNewAppointmentTextField(randomNameGenerator.getEmailConformName() + "@mailinator.com");
        COUNTER_PROCESSING_STATION_PAGE.clickOnBookAppointmentButton(true);
    }

    @Wenn("sie einen Terminkunden mit der Dienstleistung {string}, Uhrzeit, name und gültige E-Mail-Adresse buchen.")
    public void wenn_sie_einen_terminkunden_mit_der_dienstleistung_uhrzeit_name_und_gueltige_email_adresse_buchen(String dienstleistungen) {
        List<String> services = Arrays.asList(dienstleistungen.split(",\\s*"));
        services.forEach(this::wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_dienstleistung_string_auswaehlen);
        COUNTER_PROCESSING_STATION_PAGE.selectTimeInNewAppointmentDropDownList("<beliebig>");
        RandomNameGenerator randomNameGenerator = new RandomNameGenerator(DriverUtil.getDriver());
        randomNameGenerator.setRandomName();
        COUNTER_PROCESSING_STATION_PAGE.enterNameInNewAppointmentTextField(randomNameGenerator.getName() + " " + randomNameGenerator.getSurname());
        COUNTER_PROCESSING_STATION_PAGE.enterEmailInNewAppointmentTextField(randomNameGenerator.getEmailConformName() + "@mailinator.com");
        COUNTER_PROCESSING_STATION_PAGE.clickOnBookAppointmentButton(true);
    }

    @Wenn("sie einen Terminkunden mit der Dienstleistung {string}, Uhrzeit, name, gültige E-Mail-Adresse und die Anmerkung {string} buchen.")
    public void wenn_sie_einen_terminkunden_mit_der_dienstleistung_uhrzeit_name_gueltige_email_adresse_und_die_anmerkung_buchen(String dienstleistungen,
            String anmerkung) {
        List<String> services = Arrays.asList(dienstleistungen.split(",\\s*"));
        services.forEach(this::wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_dienstleistung_string_auswaehlen);
        COUNTER_PROCESSING_STATION_PAGE.selectTimeInNewAppointmentDropDownList("<beliebig>");
        RandomNameGenerator randomNameGenerator = new RandomNameGenerator(DriverUtil.getDriver());
        randomNameGenerator.setRandomName();
        COUNTER_PROCESSING_STATION_PAGE.enterNameInNewAppointmentTextField(randomNameGenerator.getName() + " " + randomNameGenerator.getSurname());
        COUNTER_PROCESSING_STATION_PAGE.enterEmailInNewAppointmentTextField(randomNameGenerator.getEmailConformName() + "@mailinator.com");
        COUNTER_PROCESSING_STATION_PAGE.enterNoteInNewAppointmentTextField(anmerkung);
        COUNTER_PROCESSING_STATION_PAGE.clickOnBookAppointmentButton(true);
    }

    @Dann("Es erscheint ein Pop-Up-Fenster {string} und der Termin ist auch in der Warteschlange sichtbar.")
    public void es_erscheint_ein_popup_fenster_und_der_termin_ist_auch_in_der_warteschlange_sichtbar(String popUpName) {
        Assert.assertTrue(ADMIN_PAGE.isPopUpVisible(popUpName), String.format("Popup '%s' is not visible!", popUpName));
        try {
            wenn_sie_im_zeitmanagementsystem_auf_die_schaltflaeche_string_klicken("Ok");
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
        COUNTER_PROCESSING_STATION_PAGE.isCustomerVisibleInQueue(TestDataHelper.getTestData("new_appointment_number"), false);

    }

    @Wenn("sie einen Terminkunden mit ausgewählter Dienstleistung und Uhrzeit buchen.")
    public void wenn_sie_einen_terminkunden_mit_ausgewaehlter_dienstleistung_und_uhrzeit_buchen_erscheinen_zwei_fehlermeldungen() {
        wenn_sie_im_zeitmanagementsystem_unter_terminvereinbarung_neu_die_dienstleistung_string_auswaehlen("<beliebig>");
        COUNTER_PROCESSING_STATION_PAGE.selectTimeInNewAppointmentDropDownList("<beliebig>");
        COUNTER_PROCESSING_STATION_PAGE.clickOnBookAppointmentButton(false);
    }

    @Dann("erscheinen zwei Fehlermeldungen die bei Name und E-Mail-Adresse rot hinterlegt sind.")
    public void erscheinen_zwei_fehlermeldungen_die_bei_name_und_email_adresse_rot_hinterlegt_sind() {
        Assert.assertNotNull(TestDataHelper.getTestData("Fehler-Name"), "Error message for the name field is not visible!");
        Assert.assertNotNull(TestDataHelper.getTestData("Fehler-Email"), "Error message for the email field is not visible!");
    }

    @Dann("sollte der Kunde erschienen sein und der Termin fertiggestellt.")
    public void sollte_der_kunde_erschienen_sein_und_der_termin_fertig_gestellt() {
        PROCESSING_STATION_SECTION.clickOnYesCustomerAppeared();
        PROCESSING_STATION_SECTION.clickOnFinaliseAppointment();
    }

    @Dann("sollte der Kunde nicht erschienen sein.")
    public void sollteDerKundeNichtErschienenSein() {
        PROCESSING_STATION_SECTION.clickOnNoCustomerDidNotAppear();
    }

    @Dann("ist Für den Standort {string} ist die maximale Anzahl buchbarer Slots pro Termin auf {string} begrenzt.")
    public void fuer_den_standort_ist_die_maximale_anzahl_buchbarer_slots_pro_termin_begrenzt(String standort, String anzahl) {
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationAdminEntry();
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationEntry(standort);
        String found = AUTHORITIES_AND_LOCATIONS_PAGE.getMaxSlotsForLocation(standort);
        Assert.assertEquals(found, anzahl,
                "Expected maximum slots for location '" + standort + "' to be '" + anzahl + "', but found '" + found + "' instead.");
    }

    @Dann("sind Für den Standort {string} Wiederholungsaufrufe auf {string} begrenzt.")
    public void fuer_den_standort_sind_die_wiederholungsaufrufe_begrenzt(String standort, String anzahl) {
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationAdminEntry();
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationEntry(standort);
        String found = AUTHORITIES_AND_LOCATIONS_PAGE.getRepeatCallsForLocation(standort);
        Assert.assertEquals(found, anzahl,
                "Expected 'Wiederholungsaufrufe' for location '" + standort + "' to be '" + anzahl + "', but found '" + found + "' instead.");
    }

    @Wenn("Sie den Termin parken.")
    public void wenn_sie_den_termin_parken() {
        PROCESSING_STATION_SECTION.clickOnParkAppointment();
    }

    @Wenn("Sie den Termin zu {string} mit der Anmerkung {string} weiterleiten.")
    public void wenn_sie_den_termin_weiterleiten(String standort, String anmerkung) {
        PROCESSING_STATION_SECTION.clickOnForwardAppointment();
        PROCESSING_STATION_SECTION.selectLocationForAppointmentForwarding(standort);
        PROCESSING_STATION_SECTION.enterNoteForAppointmentForwarding(anmerkung);
        PROCESSING_STATION_SECTION.submitForwardAppointment();
    }

    @Dann("erscheint der Termin {string} unter geparkte Termine.")
    public void erscheint_der_termin_unter_geparkte_termine(String termin) {
        PROCESSING_STATION_SECTION.isCustomerVisibleInParkingTable(TestDataHelper.transformTestData(termin), true);
    }

    @Angenommen("Die fertige Termintabelle angezeigt.")
    public void sieDieFertigeTermintabelleAnzeigen() {
        COUNTER_PROCESSING_STATION_PAGE.showTheFinishedAppointmentTable();
    }

    // Es geht über den Kundennamen
    @Dann("Sollte der Kunde {string} unter abgeschlossene Termine erscheinen.")
    public void sollte_der_kunde_unter_abgeschlossene_termine_erscheinen(String kunde) {
        COUNTER_PROCESSING_STATION_PAGE.isCustomerVisibleInFinishedTable(TestDataHelper.transformTestData(kunde));
    }

    @Dann("Sollte der Kunde {string} unter verpasste Termine erscheinen.")
    public void sollte_der_kunde_unter_verpasste_termine_erscheinen(String termin) {
        String terminName = TestDataHelper.transformTestData(termin);
        COUNTER_PROCESSING_STATION_PAGE.isCustomerVisibleInMissedTable(terminName, true);
    }

    @Dann("Sollte der Kunde {string} in der Warteliste erscheinen.")
    public void sollte_der_kunde_in_der_warteliste_erscheinen(String kunde) {
        COUNTER_PROCESSING_STATION_PAGE.isCustomerVisibleInQueue(TestDataHelper.transformTestData(kunde), true);
    }

    @Dann("Die Wartezeit-H:mm:ss für {string} sollte ziwschen {string} und {string} liegen.")
    public void die_wartezeit_fuer_den_gegebenen_kunden_sollte_zwischen_zwei_werte_liegen(String kunde, String minimaleWartezeit, String maximaleWartezeit) {
        ScenarioLogManager.getLogger().info("Verifying if waiting time for {} is between {} and {}.", kunde, minimaleWartezeit, maximaleWartezeit);
        Duration minDuration = Duration.ofHours(Long.parseLong(minimaleWartezeit.split(":")[0]))
                .plusMinutes(Long.parseLong(minimaleWartezeit.split(":")[1]))
                .plusSeconds(Long.parseLong(minimaleWartezeit.split(":")[2]));

        Duration maxDuration = Duration.ofHours(Long.parseLong(maximaleWartezeit.split(":")[0]))
                .plusMinutes(Long.parseLong(maximaleWartezeit.split(":")[1]))
                .plusSeconds(Long.parseLong(maximaleWartezeit.split(":")[2]));

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

    @Dann("Die Bearbeitungszeit-H:mm:ss für {string} sollte ziwschen {string} und {string} liegen.")
    public void die_bearbeitungszeit_fuer_den_gegebenen_kunden_sollte_zwischen_zwei_werte_liegen(String kunde, String minimaleBearbeitungszeit,
            String maximaleBearbeitungszeit) {
        ScenarioLogManager.getLogger()
                .info("Verifying if processing time for {} is between {} and {}.", kunde, minimaleBearbeitungszeit, maximaleBearbeitungszeit);
        Duration minDuration = Duration.ofHours(Long.parseLong(minimaleBearbeitungszeit.split(":")[0]))
                .plusMinutes(Long.parseLong(minimaleBearbeitungszeit.split(":")[1]))
                .plusSeconds(Long.parseLong(minimaleBearbeitungszeit.split(":")[2]));

        Duration maxDuration = Duration.ofHours(Long.parseLong(maximaleBearbeitungszeit.split(":")[0]))
                .plusMinutes(Long.parseLong(maximaleBearbeitungszeit.split(":")[1]))
                .plusSeconds(Long.parseLong(maximaleBearbeitungszeit.split(":")[2]));

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

    @Wenn("Sie in der Menüzeile der Standorttabellen {string} im Dropdown Clusterstandort auswählen.")
    public void wenn_sie_in_der_menuezeile_der_standorttabellen_im_dropdown_clusterstandort_auswaehlen(String standort) {
        COUNTER_PROCESSING_STATION_PAGE.SelectClusterLocation(standort);
        COUNTER_PROCESSING_STATION_PAGE.confirmClusterLocationSelection();
    }

    @Dann("wird die Clusteransicht aktiviert.")
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

    @Und("In der Warteschlange sind die Kürzeln für folgende Standorten des Clusters zu sehen:")
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

    @Dann("wird die Clusteransicht deaktiviert und die Ansicht für {string} wird aktiviert.")
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

    @Gegebenseien("Für den Standort sind keine Termine in der Warteschlange vorhanden.")
    public void fuer_den_standort_sind_keine_termine_in_der_warteschlange_vorhanden() {
        COUNTER_PROCESSING_STATION_PAGE.isQueueEmpty();
    }

    @Dann("erscheint die Meldung, dass keine wartenden Kunden vorhanden sind.")
    public void erscheint_die_meldung_dass_keine_wartenden_kunden_vorhanden_sind() {
        PROCESSING_STATION_SECTION.checkForNoWaitingCustomersMessage();
    }

    @Und("Im Namensfeld der Warteschlange vom {string} steht, wie lange es noch dauert, bis der Kunde {string} nochmals aufgerufen werden kann.")
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

    @Dann("wird der Kundennamen {string} unter Kundeninformation angezeigt.")
    public void wird_der_kundennamen_unter_kundeninformation_angezeigt(String name) {
        String kundenName = TestDataHelper.transformTestData(name);
        PROCESSING_STATION_SECTION.checkForCustomerNameUnderCustomerInformation(kundenName);
    }

    @Dann("wird die Wartenummer {string} unter Kundeninformation angezeigt.")
    public void wird_die_wartenummer_unter_kundeninformation_angezeigt(String nummer) {
        String wartenummer = TestDataHelper.transformTestData(nummer);
        PROCESSING_STATION_SECTION.checkForWaitingNumberUnderCustomerInformation(wartenummer);
    }

    @Dann("wird die Dienstleistung {string} unter Kundeninformation angezeigt.")
    public void wird_die_dienstleistung_unter_kundeninformation_angezeigt(String dienstleistung) {
        String service = TestDataHelper.transformTestData(dienstleistung);
        PROCESSING_STATION_SECTION.checkForServiceUnderCustomerInformation(service);
    }

    @Und("wird die Anmerkung {string} unter Kundeninformation angezeigt.")
    public void wird_die_anmerkung_unter_kundeninformation_angezeigt(String anmerkung) {
        String note = TestDataHelper.transformTestData(anmerkung);
        PROCESSING_STATION_SECTION.checkForNoteUnderCustomerInformation(note);
    }

    @Und("wird die Telefinnummer {string} unter Kundeninformation angezeigt.")
    public void wirdDieTelefinnummerUnterKundeninformationAngezeigt(String telefon) {
        String nummer = TestDataHelper.transformTestData(telefon);
        PROCESSING_STATION_SECTION.checkForPhoneNumberUnderCustomerInformation(nummer);
    }

    @Und("wird die E-Mail {string} unter Kundeninformation angezeigt.")
    public void wird_die_email_unter_kundeninformation_angezeigt(String email) {
        String emailAddress = TestDataHelper.transformTestData(email);
        PROCESSING_STATION_SECTION.checkForEmailUnderCustomerInformation(emailAddress);
    }

    @Und("wird die Wartezeit unter Kundeninformation angezeigt.")
    public void wird_die_wartezeit_unter_kundeninformation_angezeigt() {
        PROCESSING_STATION_SECTION.checkForWaitingTimeUnderCustomerInformation();
    }

    @Und("wird die Zeit seit Kundenaufruf unter Kundeninformation angezeigt.")
    public void wird_die_zeit_seit_kundenaufruf_unter_kundeninformation_angezeigt() {
        PROCESSING_STATION_SECTION.checkForTimeSinceCustomerCallUnderCustomerInformation();
    }

    @Wenn("Sie unter der Standortkonfiguration auf die Schaltfläche {string} klicken.")
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

    @Dann("erscheint ein Pop-Up-Fenster {string} um den Standort zu löschen.")
    public void erscheint_ein_popup_fenster_zum_loeschen_vom_standort(String message) {
        String xpath = "//p[contains(text(), '" + message + "')]";
        boolean isMessageVisible = AUTHORITIES_AND_LOCATIONS_PAGE.isWebElementVisible(
                TestPropertiesHelper.getPropertyAsInteger("defaultExplicitWaitTime", true, DefaultValues.DEFAULT_EXPLICIT_WAIT_TIME),
                xpath,
                LocatorType.XPATH, false);
        Assert.assertTrue(isMessageVisible, "Pop-up message is not visible");
    }

    @Wenn("Sie für den Standort den Wert für die E-Mail-Bestätigung auf {word} setzen.")
    public void wenn_sie_fuer_den_standort_den_wert_fuer_die_email_bestaetigung_auf_setzen(String flag) {
        boolean booleanFlag = Boolean.parseBoolean(flag);
        AUTHORITIES_AND_LOCATIONS_PAGE.setValueForEmailConfirmation(booleanFlag);
    }

    @Wenn("Sie für den Standort ins Textfeld Information zu Terminbuchung im Bürgerfrontend {string} eingeben.")
    public void wenn_sie_fuer_den_standort_ins_textfeld_info_zu_terminbuchung_in_buergerfrontend_eingeben(String text) {
        AUTHORITIES_AND_LOCATIONS_PAGE.enterInformationTextForAppointmentBookingInTheCitizenFrontend(text);
    }

    @Wenn("Sie die Änderungen an der Standortkonfiguration speichern.")
    public void wenn_sie_die_aenderungen_an_der_standortkonfiguration_speichern() {
        AUTHORITIES_AND_LOCATIONS_PAGE.saveLocationChanges();
    }

    @Und("Sie {string} minuten bis die Änderungen übernommen werden warten.")
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

    @Dann("Für den Standort {string} ist der Standardwert für die E-Mail-Bestätigung auf {word} gesetzt.")
    public void fuer_den_standort_ist_der_standardwert_fuer_die_email_bestaetigung_auf_gesetzt(String standort, String flag) {
        boolean booleanFlag = Boolean.parseBoolean(flag);
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationAdminEntry();
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationEntry(standort);
        WebElement checkbox = AUTHORITIES_AND_LOCATIONS_PAGE.findElementByLocatorType(
                "input[name='preferences[client][emailConfirmationActivated]'][type='checkbox']", LocatorType.CSSSELECTOR, true);
        boolean isChecked = checkbox.getAttribute("checked") != null;
        Assert.assertEquals(isChecked, booleanFlag, "Für Standort " + standort + " ist die E-Mail-Bestätigung nicht auf " + booleanFlag + " gesetzt.");
    }

    @Dann("ist die Checkbox Mit E-Mail Bestätigung {string}.")
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

    @Dann("ist Für den Standort {string} der Text {string} als Info für Terminbuchung vorhanden.")
    public void ist_fuer_den_standort_der_text_als_info_fuer_terminbuchung_vorhanden(String standort, String expectedText) {
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationAdminEntry();
        AUTHORITIES_AND_LOCATIONS_PAGE.clickOnLocationEntry(standort);
        String text = AUTHORITIES_AND_LOCATIONS_PAGE.getWebElementText(
                TestPropertiesHelper.getPropertyAsInteger("defaultExplicitWaitTime", true, DefaultValues.DEFAULT_EXPLICIT_WAIT_TIME),
                "//textarea[@name='preferences[appointment][infoForAppointment]']", LocatorType.XPATH);
        Assert.assertEquals(text, expectedText,
                "Der Text für die Terminbuchung am Standort " + standort + " stimmt nicht überein. Erwartet: '" + expectedText + "', erhalten: '" + text + "'");
    }
}
