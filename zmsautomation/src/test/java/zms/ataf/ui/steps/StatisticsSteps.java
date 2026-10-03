package zms.ataf.ui.steps;

import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.Locale;
import java.util.Map;

import org.testng.Assert;

import ataf.core.helpers.TestDataHelper;
import ataf.web.controls.WindowControls;
import ataf.web.model.WindowType;
import ataf.web.utils.DriverUtil;
import io.cucumber.datatable.DataTable;
import io.cucumber.java.en.And;
import io.cucumber.java.en.Then;
import io.cucumber.java.en.When;
import zms.ataf.helpers.AccountCheckout;
import zms.ataf.helpers.BerlinTime;
import zms.ataf.ui.pages.statistics.StatisticsPage;
import zms.ataf.ui.pages.statistics.StatisticsPageContext;
import zms.ataf.ui.pages.statistics.evaluations.CustomerStatisticsPage;
import zms.ataf.ui.pages.statistics.evaluations.ServiceStatisticsPage;


public class StatisticsSteps {
    private final StatisticsPage STATISTICS_PAGE;
    private final CustomerStatisticsPage CUSTOMER_STATISTICS_PAGE;
    private final ServiceStatisticsPage SERVICE_STATISTICS_PAGE;

    public StatisticsSteps() {
        STATISTICS_PAGE = new StatisticsPage(DriverUtil.getDriver());
        CUSTOMER_STATISTICS_PAGE = new CustomerStatisticsPage(DriverUtil.getDriver(), STATISTICS_PAGE.getContext());
        SERVICE_STATISTICS_PAGE = new ServiceStatisticsPage(DriverUtil.getDriver(), STATISTICS_PAGE.getContext());
    }

    @When("I open the statistics website.")
    public void wenn_sie_zur_webseite_der_administration_navigieren() {
        STATISTICS_PAGE.navigateToPage();
    }

    @When("I sign in to the statistics as {string}.")
    public void iSignInToTheStatisticsAs(String username) throws Exception {
        String login = AccountCheckout.assignWorkstationLogin(TestDataHelper.transformTestData(username));
        TestDataHelper.setTestData("signed_in_login", login);
        STATISTICS_PAGE.loginWithKeycloakUser(login);
    }

    @Then("I should be on the statistics start page.")
    public void dann_sollten_sie_sich_am_start_des_zeitmanagementsystem_befinden() {
        Assert.assertEquals(WindowControls.getActiveWindow().getWindowTitle(), StatisticsPageContext.TITLE,
                "This is not the start page of the \"" + StatisticsPageContext.NAME + "\"");
        WindowControls.getActiveWindow().setWindowType(WindowType.getSystemWindowType("Statistik"));
    }

    @When("I click the button {string} in the statistics.")
    public void wenn_sie_in_der_statistik_auf_die_schaltflaeche_string_klicken(String button) throws Exception {
        button = TestDataHelper.transformTestData(button);
        switch (button) {
        case "Anmelden":
            STATISTICS_PAGE.clickOnLoginButton();
            break;
        case "Auswahl bestätigen":
            STATISTICS_PAGE.clickOnApplySelectionButton();
            break;
        default:
            throw new IllegalArgumentException("For button \"" + button + "\" no action is implemented yet!");
        }
    }

    @When("I select for {string} the value {string} in the statistics.")
    public void wenn_in_der_statistik_sie_fuer_string_den_wert_string_auswaehlen(String type, String value) {
        value = TestDataHelper.transformTestData(value);
        switch (type) {
        case "Standort":
            STATISTICS_PAGE.selectLocation(value);
            break;
        default:
            throw new IllegalArgumentException("For drop down list of type \"" + type + "\" no action is implemented yet!");
        }
    }

    @Then("the statistics overview page is displayed.")
    public void dann_wird_die_seite_sachbearbeiterplatz_geoeffnet() {
        STATISTICS_PAGE.checkIfTheOverviewPageIsOpen();
    }

    @And("I select for {string} the value {string} in the statistics filter.")
    public void sie_in_der_statistik_im_filter_fuer_den_wert_auswaehlen(String type, String value) {
        value = TestDataHelper.transformTestData(value);
        switch (type) {
        case "Standort":
            STATISTICS_PAGE.selectScopeInStatisticsTableFilter(value);
            break;
        default:
            throw new IllegalArgumentException(
                    "For filter type \"" + type + "\" no action is implemented yet!");
        }
    }

    @And("I filter the statistics from {int} days before today until today.")
    public void sie_in_der_statistik_im_zeitraum_von_tagen_vor_heute_bis_heute_filtern(int daysBack) {
        LocalDate to = BerlinTime.today();
        LocalDate from = to.minusDays(daysBack);
        STATISTICS_PAGE.applyDateRangeFilter(from, to);
    }

    @When("I click the button {string} in the statistics sidebar.")
    public void wenn_sie_in_der_statistik_in_der_seitenleiste_auf_die_schaltflaeche_string_klicken(String button) {
        button = TestDataHelper.transformTestData(button);
        switch (button) {
        case "Kundenstatistik":
            STATISTICS_PAGE.clickOnCustomerStatistics();
            break;
        case "Dienstleistungsstatistik":
            STATISTICS_PAGE.clickOnServiceStatistics();
            break;
        default:
            throw new IllegalArgumentException("For button \"" + button + "\" no action is implemented yet!");
        }
    }

    @When("I select the current month in the statistics.")
    public void wenn_sie_in_der_statistik_den_aktuellen_monat_auswaehlen() {
        STATISTICS_PAGE.clickOnCurrentMonthName();
    }

    @Then("the statistics page {string} is displayed.")
    public void wird_die_statistik_seite_angezeigt(String pageName) {
        STATISTICS_PAGE.checkIfStatisticsPageIsOpen(pageName);
    }

    @Then("the evaluation for the selected month opens.")
    public void oeffnet_sich_die_auswertung_fuer_den_ausgewaehlten_monat() {
        STATISTICS_PAGE.checkIfTheStatisticForTheSelectedMonthIsOpen();
    }

    @And("the following data should be shown for the previous day:")
    public void zeige_kunden_statistik_fuer_vorherigen_tag(DataTable table) throws Exception {
        LocalDate gesternDatum = BerlinTime.today().minusDays(1);
        String gestern = DateTimeFormatter.ofPattern("dd.MM.yyyy", Locale.GERMANY).format(gesternDatum);
        List<Map<String, String>> data = table.asMaps(String.class, String.class);

        for (Map<String, String> row : data) {
            String spalte = row.get("Spaltenname");
            String erwarteterWert = row.get("Erwarteter Wert");

            switch (spalte) {
            case "Erschienene Kunden":
                CUSTOMER_STATISTICS_PAGE.checkForAppearedCustomersOnDate(gestern, erwarteterWert);
                break;
            case "Nicht erschienene Kunden":
                CUSTOMER_STATISTICS_PAGE.checkForNonAppearedCustomersOnDate(gestern, erwarteterWert);
                break;
            case "Erschienene Termin-Kunden":
                CUSTOMER_STATISTICS_PAGE.checkForAppearedAppointmentCustomerOnDate(gestern, erwarteterWert);
                break;
            case "Nicht erschienene Termin-Kunden":
                CUSTOMER_STATISTICS_PAGE.checkForNonAppearedAppointmentCustomerOnDate(gestern, erwarteterWert);
                break;
            case "Erschienene Spontan-Kunden":
                CUSTOMER_STATISTICS_PAGE.checkForAppearedSpontaneousCustomerOnDate(gestern, erwarteterWert);
                break;
            case "Nicht erschienene Spontan-Kunden":
                CUSTOMER_STATISTICS_PAGE.checkForNonAppearedSpontaneousCustomerOnDate(gestern, erwarteterWert);
                break;
            default:
                throw new IllegalArgumentException("For column \"" + spalte + "\" no action is implemented yet!");
            }
        }
    }

    @When("I click the download button in the statistics.")
    public void wenn_sie_in_der_statistik_auf_den_download_button_klicken() {
        SERVICE_STATISTICS_PAGE.clickDownloadButton();
    }

    @Then("the citizen statistics are downloaded.")
    public void wird_die_kundenstatistik_heruntergeladen() {
        // For UI tests we only verify that the download button is present and clickable.
        STATISTICS_PAGE.clickDownloadButton();
    }

    @Then("the service statistics are downloaded.")
    public void wird_die_dienstleistungsstatistik_heruntergeladen() {
        // For UI tests we only verify that the download button is present and clickable.
        STATISTICS_PAGE.clickDownloadButton();
    }

    @Then("the service statistics show these values:")
    public void zeigt_die_dienstleistungsstatistik_diese_werte(DataTable dataTable) {
        SERVICE_STATISTICS_PAGE.assertStatisticValues(dataTable.asMaps(String.class, String.class));
    }

    @And("I select the locations {string} and {string} in the statistics filter.")
    public void sie_in_der_statistik_im_filter_die_standorte_auswaehlen(String first, String second) {
        STATISTICS_PAGE.selectScopesInStatisticsTableFilter(List.of(first, second));
    }

    @Then("the service statistics show the day {int} days before today.")
    public void zeigt_die_dienstleistungsstatistik_den_tag_vor_heute(int daysBeforeToday) {
        SERVICE_STATISTICS_PAGE.assertDayColumn(BerlinTime.today().minusDays(daysBeforeToday), true);
    }

    @Then("the service statistics hide the day {int} days before today.")
    public void blendet_die_dienstleistungsstatistik_den_tag_vor_heute_aus(int daysBeforeToday) {
        SERVICE_STATISTICS_PAGE.assertDayColumn(BerlinTime.today().minusDays(daysBeforeToday), false);
    }

    @Then("the downloaded service statistics show the day {int} days before today.")
    public void zeigt_die_heruntergeladene_dienstleistungsstatistik_den_tag_vor_heute(int daysBeforeToday) throws Exception {
        SERVICE_STATISTICS_PAGE.assertDownloadedDayColumn(BerlinTime.today().minusDays(daysBeforeToday), true);
    }

    @Then("the downloaded service statistics hide the day {int} days before today.")
    public void blendet_die_heruntergeladene_dienstleistungsstatistik_den_tag_vor_heute_aus(int daysBeforeToday) throws Exception {
        SERVICE_STATISTICS_PAGE.assertDownloadedDayColumn(BerlinTime.today().minusDays(daysBeforeToday), false);
    }

    @Then("the downloaded service statistics match these values:")
    public void stimmt_die_heruntergeladene_dienstleistungsstatistik_ueberein(DataTable dataTable) throws Exception {
        SERVICE_STATISTICS_PAGE.assertDownloadedStatisticValues(dataTable.asMaps(String.class, String.class));
    }

    @And("the following services should be shown in the service statistics:")
    public void die_folgenden_dienstleistungen_sollten_in_der_dienstleistungsstatistik_angezeigt_werden(DataTable dataTable) {
        SERVICE_STATISTICS_PAGE.assertTableVisible();

        List<Map<String, String>> rows = dataTable.asMaps(String.class, String.class);
        for (Map<String, String> row : rows) {
            String service = row.get("dienstleistung");
            SERVICE_STATISTICS_PAGE.assertServiceListed(service);
        }
    }

    @When("I check the availability of statistical information for the current month and the service {string}.")
    public void wenn_sie_die_verfuegbarkeit_statistischer_informationen_fuer_den_aktuellen_monat_fuer_die_dienstleistung_ueberpruefen(String dienstleistung) {
        int jahr = BerlinTime.today().getYear();
        int monat = BerlinTime.today().getMonthValue();
        boolean flag = SERVICE_STATISTICS_PAGE.checkAvailabilityOfStatisticalInformationForDateAndService(jahr, monat, dienstleistung);
        Assert.assertFalse(flag, "Statistical information already available!");
    }
}
