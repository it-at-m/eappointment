package zms.ataf.ui.steps;

import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.Locale;
import java.util.Map;

import org.openqa.selenium.WebElement;
import org.testng.Assert;

import ataf.core.helpers.TestDataHelper;
import ataf.core.utils.HolidayUtil;
import ataf.web.controls.WindowControls;
import ataf.web.model.WindowType;
import ataf.web.utils.DriverUtil;
import io.cucumber.datatable.DataTable;
import io.cucumber.java.de.Dann;
import io.cucumber.java.de.Und;
import io.cucumber.java.de.Wenn;
import zms.ataf.ui.pages.statistics.StatisticsPage;
import zms.ataf.ui.pages.statistics.StatisticsPageContext;
import zms.ataf.ui.pages.statistics.evaluations.CustomerStatisticsPage;
import zms.ataf.ui.pages.statistics.evaluations.ServiceStatisticsPage;
import zms.ataf.ui.pages.statistics.evaluations.WaitStatisticsPage;
import zms.ataf.ui.pages.statistics.rawdata.CategoriesPage;


public class StatisticsSteps {
    private final StatisticsPage STATISTICS_PAGE;
    private final CustomerStatisticsPage CUSTOMER_STATISTICS_PAGE;
    private final ServiceStatisticsPage SERVICE_STATISTICS_PAGE;
    private final WaitStatisticsPage WAIT_STATISTICS_PAGE;
    private final CategoriesPage CATEGORIES_PAGE;

    public StatisticsSteps() {
        STATISTICS_PAGE = new StatisticsPage(DriverUtil.getDriver());
        CUSTOMER_STATISTICS_PAGE = new CustomerStatisticsPage(DriverUtil.getDriver(), STATISTICS_PAGE.getContext());
        WAIT_STATISTICS_PAGE = new WaitStatisticsPage(DriverUtil.getDriver(), STATISTICS_PAGE.getContext());
        SERVICE_STATISTICS_PAGE = new ServiceStatisticsPage(DriverUtil.getDriver(), STATISTICS_PAGE.getContext());
        CATEGORIES_PAGE = new CategoriesPage(DriverUtil.getDriver(), STATISTICS_PAGE.getContext());
    }

    @Wenn("Sie zur Webseite der Statistik navigieren.")
    public void wenn_sie_zur_webseite_der_administration_navigieren() {
        STATISTICS_PAGE.navigateToPage();
    }

    @Dann("sollten Sie sich am Start der " + StatisticsPageContext.NAME + " befinden.")
    public void dann_sollten_sie_sich_am_start_des_zeitmanagementsystem_befinden() {
        Assert.assertEquals(WindowControls.getActiveWindow().getWindowTitle(), StatisticsPageContext.TITLE,
                "This is not the start page of the \"" + StatisticsPageContext.NAME + "\"");
        WindowControls.getActiveWindow().setWindowType(WindowType.getSystemWindowType("Statistik"));
    }

    @Wenn("Sie in der " + StatisticsPageContext.NAME + " auf die Schaltfläche {string} klicken.")
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

    @Wenn("Sie in der Statistik für {string} den Wert {string} auswählen.")
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

    @Dann("wird die Übersichtsseite der Statistik angezeigt.")
    public void dann_wird_die_seite_sachbearbeiterplatz_geoeffnet() {
        STATISTICS_PAGE.checkIfTheOverviewPageIsOpen();
    }

    @Wenn("Sie in der Statistik in der Seitenleiste auf die Schaltfläche {string} klicken.")
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

    @Wenn("Sie in der Statistik den aktuellen Monat auswählen.")
    public void wenn_sie_in_der_statistik_den_aktuellen_monat_auswaehlen() {
        STATISTICS_PAGE.clickOnCurrentMonthName();
    }

    @Dann("wird die Statistik-Seite {string} angezeigt.")
    public void wird_die_statistik_seite_angezeigt(String pageName) {
        STATISTICS_PAGE.checkIfStatisticsPageIsOpen(pageName);
    }

    @Dann("öffnet sich die Auswertung für den ausgewählten Monat.")
    public void oeffnet_sich_die_auswertung_fuer_den_ausgewaehlten_monat() {
        STATISTICS_PAGE.checkIfTheStatisticForTheSelectedMonthIsOpen();
    }

    @Und("die folgenden Daten sollten für den vorherigen Tag angezeigt werden:")
    public void zeige_kunden_statistik_fuer_vorherigen_tag(DataTable table) throws Exception {
        String gestern = DateTimeFormatter.ofPattern("dd.MM.yyyy", Locale.GERMANY).format(HolidayUtil.getPreviousBusinessDay());
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
            case "Dienstleistungen (Monat)":
                CUSTOMER_STATISTICS_PAGE.checkForServicesInMonth(erwarteterWert);
                break;
            case "Dienstleistungen (Tag)":
                CUSTOMER_STATISTICS_PAGE.checkForServicesOnDate(gestern, erwarteterWert);
                break;
            default:
                throw new IllegalArgumentException("For column \"" + spalte + "\" no action is implemented yet!");
            }
        }
    }

    @Wenn("Sie In der Statistik auf den Download-Button klicken.")
    public void wenn_sie_in_der_statistik_auf_den_download_button_klicken() {
        STATISTICS_PAGE.clickDownloadButton();
    }

    @Dann("wird die Kundenstatistik heruntergeladen.")
    public void wird_die_kundenstatistik_heruntergeladen() {
        CUSTOMER_STATISTICS_PAGE.isClientStatisticDownloaded();
    }

    @Dann("wird die Dienstleistungsstatistik heruntergeladen.")
    public void wird_die_dienstleistungsstatistik_heruntergeladen() {
        SERVICE_STATISTICS_PAGE.isServiceStatisticDownloaded();
    }

    @Und("die folgenden Dienstleistungen sollten für den vorherigen Tag angezeigt werden:")
    public void die_folgenden_dienstleistungen_sollten_fuer_den_vorherigen_tag_angezeigt_werden(DataTable dataTable) {
        int dayOfMonth = HolidayUtil.getPreviousBusinessDay().getDayOfMonth();

        List<Map<String, String>> rows = dataTable.asMaps(String.class, String.class);
        for (Map<String, String> row : rows) {
            String service = row.get("dienstleistung");
            String erwarteterWert = row.get("Erwarteter Wert");
            WebElement cell = SERVICE_STATISTICS_PAGE.getTableCellText(service, dayOfMonth);
            Assert.assertNotNull(cell, "Cell not found for row: " + service + " and column: " + dayOfMonth);
            Assert.assertEquals(cell.getText().trim(), erwarteterWert, "Mismatch for row: " + service + " and column: " + dayOfMonth);
        }
    }

    @Wenn("Sie die Verfügbarkeit statistischer Informationen für den aktuellen Monat und die Dienstleistung {string} überprüfen.")
    public void wenn_sie_die_verfuegbarkeit_statistischer_informationen_fuer_den_aktuellen_monat_fuer_die_dienstleistung_ueberpruefen(String dienstleistung) {
        int jahr = LocalDate.now().getYear();
        int monat = LocalDate.now().getMonthValue();
        boolean flag = SERVICE_STATISTICS_PAGE.checkAvailabilityOfStatisticalInformationForDateAndService(jahr, monat, dienstleistung);
        Assert.assertFalse(flag, "Statistical information already available!");
    }
}
