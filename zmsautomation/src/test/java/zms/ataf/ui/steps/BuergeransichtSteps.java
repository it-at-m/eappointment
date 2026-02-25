package zms.ataf.ui.steps;

import java.time.LocalDate;
import java.time.temporal.ChronoUnit;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.function.Predicate;

import org.apache.commons.lang3.RandomStringUtils;
import org.testng.Assert;

import ataf.core.helpers.TestDataHelper;
import ataf.core.utils.DateUtils;
import ataf.web.pages.RandomNameGenerator;
import ataf.web.utils.DriverUtil;
import io.cucumber.java.de.Dann;
import io.cucumber.java.de.Und;
import io.cucumber.java.de.Wenn;
import zms.ataf.helpers.RandomNameHelper;
import zms.ataf.ui.base.services.ServicesUtil;
import zms.ataf.ui.base.services.pojo.Service;
import zms.ataf.ui.pages.buergeransicht.BuergeransichtPage;
import zms.ataf.ui.pages.buergeransicht.BuergeransichtPageContext;


public class BuergeransichtSteps {
    private final BuergeransichtPage BUERGERANSICHT_PAGE;

    public BuergeransichtSteps() {
        BUERGERANSICHT_PAGE = new BuergeransichtPage(DriverUtil.getDriver());
    }

    @Wenn("Sie auf der " + BuergeransichtPageContext.NAME + " ins Textfeld Dienstleistungen {string} eingeben.")
    public void wenn_sie_auf_der_buergeransicht_ins_textfeld_dienstleistungen_string_eingeben(String service) {
        service = TestDataHelper.transformTestData(service);
        TestDataHelper.setTestData("service", service);
        BUERGERANSICHT_PAGE.selectService(service);
    }

    @Wenn("Sie auf der " + BuergeransichtPageContext.NAME + " auf die Schaltfläche {string} klicken.")
    public void wenn_sie_auf_der_buergeransicht_auf_die_schaltflaeche_string_klicken(String button) {
        button = TestDataHelper.transformTestData(button);
        switch (button) {
        case "Weiter zur Terminauswahl":
            BUERGERANSICHT_PAGE.clickOnContinueButton();
            break;
        case "Weiter zum Abschluss der Reservierung":
            BUERGERANSICHT_PAGE.clickOnContinueWithReservationButton();
            break;
        case "Reservierung abschließen":
            BUERGERANSICHT_PAGE.clickOnCompleteReservationButton();
            break;
        case "Termin umbuchen":
            BUERGERANSICHT_PAGE.clickOnChangeAppointmentButton();
            break;
        case "Termin absagen":
            BUERGERANSICHT_PAGE.clickOnCancelAppointmentButton();
            break;
        default:
            throw new IllegalArgumentException("For button \"" + button + "\" no action is implemented yet!");
        }
    }

    @Und("Sie die Terminumbuchung bestätigen.")
    public void wenn_sie_die_terminumbuchung_bestaetigen() {
        BUERGERANSICHT_PAGE.clickOnConfirmChangeAppointmentButton();
    }

    @Und("Sie auf der " + BuergeransichtPageContext.NAME + " den Standort {string} auswählen.")
    public void wenn_sie_auf_der_buergeransicht_den_standort_auswaehlen(String standort) {
        BUERGERANSICHT_PAGE.selectLocation(standort);
    }

    @Dann("ist auf der Bürgeransicht der Standort {string} vorausgewählt.")
    public void ist_auf_der_buergeransicht_der_standort_vorausgewaehlt(String standort) {
        BUERGERANSICHT_PAGE.verifyLocationTabSelected(standort);
    }

    @Wenn("Sie auf der " + BuergeransichtPageContext.NAME + " das {string} auswählen.")
    public void wenn_sie_auf_der_buergeransicht_das_string_auswaehlen(String office) {
        office = TestDataHelper.transformTestData(office);
        if (office.contains("strasse")) {
            TestDataHelper.setTestData("office", office.replace("strasse", "straße"));
        } else {
            TestDataHelper.setTestData("office", office);
        }
        BUERGERANSICHT_PAGE.selectOffice(office);
    }

    @Wenn("Sie auf der " + BuergeransichtPageContext.NAME + " das Jahr {string} auswählen.")
    public void wenn_sie_auf_der_buergeransicht_das_jahr_string_auswaehlen(String year) {
        year = TestDataHelper.transformTestData(year);
        LocalDate currentDate = DateUtils.getDateWithOffset(0, ChronoUnit.YEARS);
        Assert.assertTrue(Integer.parseInt(year) >= currentDate.getYear(), "Given year \"" + year + "\" cannot be in the past!");
        if (year.length() == 2 && !year.startsWith("20")) {
            TestDataHelper.setTestData("year", "20" + year);
        } else {
            TestDataHelper.setTestData("year", year);
        }
        BUERGERANSICHT_PAGE.selectYear(year);
    }

    @Wenn("Sie auf der " + BuergeransichtPageContext.NAME + " den Monat {string} auswählen.")
    public void wenn_sie_auf_der_buergeransicht_den_monat_string_auswaehlen(String month) {
        month = TestDataHelper.transformTestData(month);
        if (month.length() == 1 && month.charAt(0) != '0') {
            TestDataHelper.setTestData("month", "0" + month);
        } else {
            TestDataHelper.setTestData("month", month);
        }
        BUERGERANSICHT_PAGE.selectMonth(month);
    }

    @Wenn("Sie auf der " + BuergeransichtPageContext.NAME + " den Tag {string} auswählen.")
    public void wenn_sie_auf_der_buergeransicht_den_tag_string_auswaehlen(String day) {
        day = TestDataHelper.transformTestData(day);
        if (day.length() == 1) {
            TestDataHelper.setTestData("day", "0" + day);
        } else {
            TestDataHelper.setTestData("day", day);
            if (day.charAt(0) == '0') {
                day = day.substring(1);
            }
        }
        BUERGERANSICHT_PAGE.selectDay(day);
    }

    @Wenn("Sie auf der " + BuergeransichtPageContext.NAME + " die verfügbare Uhrzeit {string} auswählen.")
    public void wenn_sie_auf_der_buergeransicht_die_verfuegbare_uhrzeit_string_auswaehlen(String time) {
        time = TestDataHelper.transformTestData(time);
        Assert.assertTrue(time.matches("^([0-9]+:[0-9]+|<beliebig>|<nächste>)$"),
                "Given time \"" + time + "\" is in wrong format! Use e.g. 8:45, for random values enter <beliebig>");
        BUERGERANSICHT_PAGE.selectTime(time);
    }

    @Wenn("Sie auf der " + BuergeransichtPageContext.NAME + " ins Textfeld Name {string} eingeben.")
    public void wenn_sie_auf_der_buergeransicht_ins_textfeld_name_string_eingeben(String name) {
        name = TestDataHelper.transformTestData(name);
        if (name.equals("<zufällig>")) {
            name = RandomNameHelper.generateRandomName();
        }
        TestDataHelper.setTestData("customer_name", name);
        BUERGERANSICHT_PAGE.enterCustomerName(name);
    }

    @Wenn("Sie auf der " + BuergeransichtPageContext.NAME + " ins Textfeld E-Mail-Adresse {string} eingeben.")
    public void wenn_sie_auf_der_buergeransicht_ins_textfeld_email_string_eingeben(String email) {
        email = TestDataHelper.transformTestData(email);
        if (email.equals("<mailinator>")) {
            if (TestDataHelper.getTestData("customer_name") != null) {
                email = RandomNameGenerator.getEmailConformName(TestDataHelper.getTestData("customer_name")) + "@mailinator.com";
            } else {
                email = RandomStringUtils.randomAlphanumeric(8).toLowerCase() + "@mailinator.com";
            }
        }
        TestDataHelper.setTestData("customer_email", email);
        Assert.assertTrue(email.matches("^[\\w-.]+@([\\w-]+\\.)+[\\w-]{2,4}$"));
        BUERGERANSICHT_PAGE.enterCustomerEmail(email);
    }

    @Wenn("Sie auf der " + BuergeransichtPageContext.NAME + " das Kontrollkästchen für den Datenschutz auswählen.")
    public void wenn_sie_auf_der_buergeransicht_das_kontrollkaestchen_fuer_den_datenschutz_auswaehlen() {
        BUERGERANSICHT_PAGE.selectDataPrivacyAgreementCheckBox();
    }

    @Dann("sollten Sie auf der " + BuergeransichtPageContext.NAME + " die Terminbestätigung angezeigt bekommen.")
    public void dann_sollten_sie_auf_der_buergeransicht_die_terminbestaetigung_angezeigt_bekommen() {
        BUERGERANSICHT_PAGE.checkIfReservationApprovalIsDisplayed();
    }

    @Dann("sollten Sie auf der " + BuergeransichtPageContext.NAME + " die Terminabsage angezeigt bekommen.")
    public void dann_sollten_sie_auf_der_buergeransicht_die_terminabsage_angezeigt_bekommen() {
        BUERGERANSICHT_PAGE.checkIfReservationCancellationIsDisplayed();
    }

    @Wenn("Sie auf der " + BuergeransichtPageContext.NAME + " ins Textfeld Telefon {string} eingeben.")
    public void wenn_sie_auf_der_buergeransicht_ins_textfeld_telefon_string_eingeben(String phoneNumber) {
        phoneNumber = TestDataHelper.transformTestData(phoneNumber);
        TestDataHelper.setTestData("customer_phone_number", phoneNumber);
        BUERGERANSICHT_PAGE.enterCustomerTelephoneNumber(phoneNumber);
    }

    @Wenn("Sie auf der " + BuergeransichtPageContext.NAME + " ins Textfeld {string} {string} eingeben.")
    public void wenn_sie_auf_der_buergeransicht_ins_textfeld_string_string_eingeben(String textfield, String textToBeEntered) {
        textfield = TestDataHelper.transformTestData(textfield);
        switch (textfield) {
        case "Name":
            wenn_sie_auf_der_buergeransicht_ins_textfeld_name_string_eingeben(textToBeEntered);
            break;
        case "E-Mail-Adresse":
            wenn_sie_auf_der_buergeransicht_ins_textfeld_email_string_eingeben(textToBeEntered);
            break;
        case "Telefon":
            wenn_sie_auf_der_buergeransicht_ins_textfeld_telefon_string_eingeben(textToBeEntered);
            break;
        default:
            textToBeEntered = TestDataHelper.transformTestData(textToBeEntered);
            TestDataHelper.setTestData("custom_field_name", textfield);
            TestDataHelper.setTestData("custom_field_text", textToBeEntered);
            BUERGERANSICHT_PAGE.enterTextInCustomField(textToBeEntered);
        }
    }

    @Wenn("Sie dann auf dem erscheinenden Fenster die Schaltfläche {string} klicken.")
    public void wenn_sie_auf_dem_erscheinenden_fenster_die_schaltflaeche_klicken(String button) {
        button = TestDataHelper.transformTestData(button);
        switch (button) {
        case "Ja":
            BUERGERANSICHT_PAGE.clickOnYesButton();
            break;
        case "Nein":
            BUERGERANSICHT_PAGE.clickOnNoButton();
            break;
        default:
            throw new IllegalArgumentException("For button \"" + button + "\" no action is implemented yet!");
        }
    }

    @Wenn("Sie zur Webseite der " + BuergeransichtPageContext.NAME + " navigieren.")
    public void wenn_sie_zur_webseite_der_buergeransicht_navigieren() {
        BUERGERANSICHT_PAGE.navigateToPage();
    }

    @Und("Sie die Bürgeransicht schließen.")
    public void und_sie_die_buergeransicht_schliessen() {
        BUERGERANSICHT_PAGE.close();
    }

    @Dann("erscheinen die kombinierbaren Dienstleistungen für die Dienstleistung {string}.")
    public void erscheinen_die_kombinierbaren_dienstleistungen_fuer_die_dienstleistung(String serviceName) {
        ServicesUtil servicesUtil = new ServicesUtil();
        String id = servicesUtil.getServiceByName(serviceName).getId();
        Set<Service> combinableServices = servicesUtil.getCombinableServices(id).keySet();
        List<String> serviceNames = new ArrayList<>();

        for (Service service : combinableServices) {
            if (service != null) {
                serviceNames.add(service.getName());
            }
        }
        BUERGERANSICHT_PAGE.checkForCombinableServices(serviceNames);
    }

    @Wenn("für den Service {string} und den Standort-ID {string} ein zufälliger kombinierbarer Service basierend auf der Anzahl der Slots {string} ausgewählt wird.")
    public void wenn_fuer_den_service_und_den_standort_ein_zufaelliger_kombinierbarer_service_basierend_auf_der_anzahl_der_slots_ausgewaehlt_wird(
            String service, String standortId, String slots) {
        ServicesUtil servicesUtil = new ServicesUtil();
        String serviceId = servicesUtil.getServiceByName(service).getId();
        Map<Service, Integer> servicesWithSlots = servicesUtil.getSlotsForServiceAndCombinable(serviceId, standortId);

        // get only services with a specific number of slots
        Predicate<Map.Entry<Service, Integer>> filterByValue = entry -> entry.getValue() == Integer.parseInt(slots);
        List<String> filteredNames = servicesWithSlots.entrySet().stream()
                .filter(filterByValue)
                .map(entry -> entry.getKey().getName())
                .toList();

        BUERGERANSICHT_PAGE.clickFirstEnabledIncreaseButtonByServiceNames(filteredNames);
    }

    @Dann("sollte die Warnung {string} erscheinen.")
    public void sollte_die_warnung_erscheinen(String warnung) {
        BUERGERANSICHT_PAGE.checkForWarningMessage(warnung);
    }

    @Dann("sollte keine Warnung erscheinen.")
    public void sollte_keine_warnung_erscheinen() {
        Assert.assertTrue(BUERGERANSICHT_PAGE.IsWarningInVisible(), "Warning message element is visible!");
    }

    @Dann("erscheinen der Kalender und die Slots für die Terminauswahl.")
    public void erscheinen_der_kalender_und_die_slots_fuer_die_terminauswahl() {
        BUERGERANSICHT_PAGE.calendarIsVisibleForAppointmentSelection();
        BUERGERANSICHT_PAGE.slotsAreVisibleForAppointmentSelection();
    }

    @Und("Informationen zur Terminbuchung sind für den Kunden sichtbar.")
    public void informationen_zur_terminbuchung_sind_fuer_den_kunden_sichtbar() {
        BUERGERANSICHT_PAGE.informationAboutTheAppointmentBookingIsVisibleToTheCustomer();
    }
}
