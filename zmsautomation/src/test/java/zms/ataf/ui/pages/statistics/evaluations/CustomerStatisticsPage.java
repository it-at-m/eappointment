package zms.ataf.ui.pages.statistics.evaluations;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.testng.Assert;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.model.LocatorType;
import zms.ataf.ui.pages.statistics.StatisticsPage;
import zms.ataf.ui.pages.statistics.StatisticsPageContext;

public class CustomerStatisticsPage extends StatisticsPage {

    //aktueller Monat (auf deutsch)
    //private final String currentMonth = LocalDate.now().getMonth().getDisplayName(TextStyle.FULL_STANDALONE, Locale.GERMAN);

    public CustomerStatisticsPage(RemoteWebDriver driver, StatisticsPageContext statisticsPageContext) {
        super(driver, statisticsPageContext);
    }

    public void checkForAppearedCustomersInMonth(String expectedNumber) {
        //td[@class='colKunden report-board--summary']
        //td.colKunden.report-board--summary
        ScenarioLogManager.getLogger()
                .info("Verifying the number of customers who appeared in the month of " + getCurrentMonth() + ". Expected number: " + expectedNumber);
        WebElement customersElement = findElementByLocatorType("//td[@class='colKunden report-board--summary']", LocatorType.XPATH, true);
        scrollToCenterByVisibleElement(customersElement);
        Assert.assertEquals(customersElement.getText(), expectedNumber,
                "Number of customers who appeared in " + getCurrentMonth() + " doesn't match. Expected: " + expectedNumber + ", Actual: " + customersElement.getText());
    }

    public void checkForNonAppearedCustomersInMonth(String expectedNumber) {
        //td[@class='colKundenNoShow report-board--summary']
        //td.colKundenNoShow.report-board--summary
        ScenarioLogManager.getLogger()
                .info("Verifying the number of customers who didn't appear in the month of " + getCurrentMonth() + ". Expected number: " + expectedNumber);
        WebElement customersElement = findElementByLocatorType("//td[@class='colKundenNoShow report-board--summary']", LocatorType.XPATH, true);
        scrollToCenterByVisibleElement(customersElement);
        Assert.assertEquals(customersElement.getText(), expectedNumber,
                "Number of customers who appeared in " + getCurrentMonth() + " doesn't match. Expected: " + expectedNumber + ", Actual: " + customersElement.getText());
    }

    public void checkForAppearedCustomersOnDate(String date, String expectedNumber) {
        //table[@class='table--base']//tr[td[@class='colDatumTag statistik'][text()='02.02.2024']]/td[@class='colKunden statistik']
        ScenarioLogManager.getLogger().info("Verifying the number of customers who appeared on " + date + ". Expected number: " + expectedNumber);
        WebElement customersElement = findElementByLocatorType(
                "//table[@class='table--base']//tr[td[@class='colDatumTag statistik'][text()='" + date + "']]/td[@class='colKunden statistik']",
                LocatorType.XPATH, true);
        scrollToCenterByVisibleElement(customersElement);
        Assert.assertEquals(customersElement.getText(), expectedNumber,
                "Number of customers who appeared on " + date + " doesn't match. Expected: " + expectedNumber + ", Actual: " + customersElement.getText());
    }

    public void checkForNonAppearedCustomersOnDate(String date, String expectedNumber) {
        //table[@class='table--base']//tr[td[@class='colDatumTag statistik'][text()='02.02.2024']]/td[@class='colKundenNoShow statistik']
        ScenarioLogManager.getLogger().info("Verifying the number of customers who didn't appear on " + date + ". Expected number: " + expectedNumber);
        WebElement customersElement = findElementByLocatorType(
                "//table[@class='table--base']//tr[td[@class='colDatumTag statistik'][text()='" + date + "']]/td[@class='colKundenNoShow statistik']",
                LocatorType.XPATH, true);
        scrollToCenterByVisibleElement(customersElement);
        Assert.assertEquals(customersElement.getText(), expectedNumber,
                "Number of customers who appeared on " + date + " doesn't match. Expected: " + expectedNumber + ", Actual: " + customersElement.getText());
    }

    public void checkForAppearedAppointmentCustomerOnDate(String date, String expectedNumber) {
        //table[@class='table--base']//tr[td[@class='colDatumTag statistik'][text()='01.02.2024']]/td[@class='colMitTermin statistik']
        ScenarioLogManager.getLogger().info("Verifying the number of appointment customers who appeard on " + date + ". Expected number: " + expectedNumber);
        WebElement customersElement = findElementByLocatorType(
                "//table[@class='table--base']//tr[td[@class='colDatumTag statistik'][text()='" + date + "']]/td[@class='colMitTermin statistik']",
                LocatorType.XPATH, true);
        scrollToCenterByVisibleElement(customersElement);
        Assert.assertEquals(customersElement.getText(), expectedNumber,
                "Number of customers who appeared on " + date + " doesn't match. Expected: " + expectedNumber + ", Actual: " + customersElement.getText());
    }

    public void checkForNonAppearedAppointmentCustomerOnDate(String date, String expectedNumber) {
        //table[@class='table--base']//tr[td[@class='colDatumTag statistik'][text()='01.02.2024']]/td[@class='colMitTerminNoShow statistik']
        ScenarioLogManager.getLogger()
                .info("Verifying the number of appointment customers who didn't appear on " + date + ". Expected number: " + expectedNumber);
        WebElement customersElement = findElementByLocatorType(
                "//table[@class='table--base']//tr[td[@class='colDatumTag statistik'][text()='" + date + "']]/td[@class='colMitTerminNoShow statistik']",
                LocatorType.XPATH, true);
        scrollToCenterByVisibleElement(customersElement);
        Assert.assertEquals(customersElement.getText(), expectedNumber,
                "Number of customers who appeared on " + date + " doesn't match. Expected: " + expectedNumber + ", Actual: " + customersElement.getText());
    }

    public void checkForAppearedSpontaneousCustomerOnDate(String date, String expectedNumber) {
        //table[@class='table--base']//tr[td[@class='colDatumTag statistik'][text()='01.02.2024']]/td[@class='colMitKeinTTermin statistik']
        ScenarioLogManager.getLogger().info("Verifying the number of spontaneous customers who appeard on " + date + ". Expected number: " + expectedNumber);
        WebElement customersElement = findElementByLocatorType(
                "//table[@class='table--base']//tr[td[@class='colDatumTag statistik'][text()='" + date + "']]/td[@class='colMitKeinTTermin statistik']",
                LocatorType.XPATH, true);
        scrollToCenterByVisibleElement(customersElement);
        Assert.assertEquals(customersElement.getText(), expectedNumber,
                "Number of customers who appeared on " + date + " doesn't match. Expected: " + expectedNumber + ", Actual: " + customersElement.getText());
    }

    public void checkForNonAppearedSpontaneousCustomerOnDate(String date, String expectedNumber) {
        //table[@class='table--base']//tr[td[@class='colDatumTag statistik'][text()='01.02.2024']]/td[@class='colMitKeinTTerminNoShow statistik']
        ScenarioLogManager.getLogger()
                .info("Verifying the number of spontaneous customers who didn't appear on " + date + ". Expected number: " + expectedNumber);
        WebElement customersElement = findElementByLocatorType(
                "//table[@class='table--base']//tr[td[@class='colDatumTag statistik'][text()='" + date + "']]/td[@class='colMitKeinTTerminNoShow statistik']",
                LocatorType.XPATH, true);
        scrollToCenterByVisibleElement(customersElement);
        Assert.assertEquals(customersElement.getText(), expectedNumber,
                "Number of customers who appeared on " + date + " doesn't match. Expected: " + expectedNumber + ", Actual: " + customersElement.getText());
    }

    public void checkForServicesInMonth(String expectedNumber) {
        //td[@class='colDienstleistungen report-board--summary']
        ScenarioLogManager.getLogger().info("Verifying the number of services in " + getCurrentMonth() + ". Expected number: " + expectedNumber);
        WebElement services = findElementByLocatorType("//td[@class='colDienstleistungen report-board--summary']", LocatorType.XPATH, true);
        scrollToCenterByVisibleElement(services);
        Assert.assertEquals(services.getText(), expectedNumber,
                "Number of services in " + getCurrentMonth() + " doesn't match. Expected: " + expectedNumber + ", Actual: " + services.getText());
    }

    public void checkForServicesOnDate(String date, String expectedNumber) {
        //table[@class='table--base']//tr[td[@class='colDatumTag statistik'][text()='01.02.2024']]/td[@class='colDienstleistungen statistik']
        ScenarioLogManager.getLogger().info("Verifying the number of services in " + getCurrentMonth() + ". Expected number: " + expectedNumber);
        WebElement services = findElementByLocatorType(
                "//table[@class='table--base']//tr[td[@class='colDatumTag statistik'][text()='" + date + "']]/td[@class='colDienstleistungen statistik']",
                LocatorType.XPATH, true);
        scrollToCenterByVisibleElement(services);
        Assert.assertEquals(services.getText(), expectedNumber,
                "Number of services on " + date + " doesn't match. Expected: " + expectedNumber + ", Actual: " + services.getText());
    }

    public void isClientStatisticDownloaded() {
        isStatisticDownloaded("clientstatistic_\\d{4}-\\d{1,2}(-\\d{2})?\\.xlsx");
    }
}
