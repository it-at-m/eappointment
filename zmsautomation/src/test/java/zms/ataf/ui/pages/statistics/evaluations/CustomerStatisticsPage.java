package zms.ataf.ui.pages.statistics.evaluations;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.time.Duration;
import java.util.ArrayList;
import java.util.Comparator;
import java.util.HashSet;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.stream.Collectors;
import java.util.stream.Stream;

import org.openqa.selenium.By;
import org.openqa.selenium.HasDownloads;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.model.LocatorType;
import ataf.web.utils.DriverUtil;
import zms.ataf.ui.pages.statistics.StatisticsPage;
import zms.ataf.ui.pages.statistics.StatisticsPageContext;

public class CustomerStatisticsPage extends StatisticsPage {

    private static final String WORKBOOK_NAME = "clientstatistic_.*\\.xlsx";

    private Map<String, String> workbooksBeforeClick = Map.of();
    private Path downloadedWorkbook;
    private String pendingWorkbookName;
    private long pendingWorkbookSize = -1;

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
        isStatisticDownloaded(WORKBOOK_NAME);
    }

    /** ZMSKVR-1266 / ZMSKVR-1519: each Datum cell appears at most once. */
    public void assertEachCalendarDayAtMostOnce() {
        List<String> dates = uiDayDates();
        ScenarioLogManager.getLogger()
                .info("Checking Kundenstatistik day rows for duplicates ({} days).", dates.size());
        assertUniqueDates(dates, "UI table");
    }

    public void clickCitizenStatisticDownload() {
        downloadedWorkbook = null;
        workbooksBeforeClick = workbookNames();
        pendingWorkbookName = null;
        pendingWorkbookSize = -1;
        super.clickDownloadButton();
    }

    public void assertDownloadedEachCalendarDayAtMostOnce() throws IOException {
        List<String> dates = ClientStatisticWorkbook.dayDates(workbookFromThisDownload());
        ScenarioLogManager.getLogger()
                .info("Checking downloaded Kundenstatistik for duplicate days ({} days).", dates.size());
        assertUniqueDates(dates, "Excel export");
    }

    private List<String> uiDayDates() {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.until(driver -> !driver.findElements(By.cssSelector("td.colDatumTag.statistik")).isEmpty()
                || driver.getPageSource().contains("Keine Daten")
                || driver.getPageSource().contains("im Zeitraum"));
        List<WebElement> cells = DRIVER.findElements(By.cssSelector("td.colDatumTag.statistik"));
        List<String> dates = new ArrayList<>();
        for (WebElement cell : cells) {
            String text = cell.getText().trim();
            if (!text.isEmpty()) {
                dates.add(text);
            }
        }
        return dates;
    }

    private static void assertUniqueDates(List<String> dates, String source) {
        Set<String> unique = new HashSet<>();
        List<String> duplicates = new ArrayList<>();
        for (String date : dates) {
            if (!unique.add(date)) {
                duplicates.add(date);
            }
        }
        Assert.assertTrue(
                duplicates.isEmpty(),
                source + " must not list a calendar day more than once. Duplicates: " + duplicates
                        + ". All days: " + dates);
    }

    private Path workbookFromThisDownload() {
        if (downloadedWorkbook != null) {
            return downloadedWorkbook;
        }
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.withMessage("The citizen statistic workbook from this download did not finish.");
        if (DriverUtil.isLocalExecution()) {
            Path downloads = Paths.get(System.getProperty("user.home"), "Downloads");
            downloadedWorkbook = wait.until(ignored -> stableNewLocalWorkbook(downloads));
            return downloadedWorkbook;
        }
        HasDownloads.DownloadedFile file = wait.until(ignored -> stableNewRemoteWorkbook());
        try {
            Path directory = Files.createTempDirectory("clientstatistic-");
            ((HasDownloads) DRIVER).downloadFile(file.getName(), directory);
            downloadedWorkbook = directory.resolve(file.getName());
            return downloadedWorkbook;
        } catch (IOException exception) {
            throw new IllegalStateException("Could not copy the citizen statistic workbook " + file.getName(), exception);
        }
    }

    private Path stableNewLocalWorkbook(Path downloads) {
        Path newest;
        try (Stream<Path> list = Files.list(downloads)) {
            newest = list.filter(Files::isRegularFile)
                    .filter(path -> isNewWorkbook(path.getFileName().toString(), sizeOf(path), path.toFile().lastModified()))
                    .max(Comparator.comparingLong(path -> path.toFile().lastModified()))
                    .orElse(null);
        } catch (IOException exception) {
            return null;
        }
        if (newest == null) {
            pendingWorkbookName = null;
            pendingWorkbookSize = -1;
            return null;
        }
        long size = sizeOf(newest);
        return rememberStableSize(newest.getFileName().toString(), size) ? newest : null;
    }

    private HasDownloads.DownloadedFile stableNewRemoteWorkbook() {
        HasDownloads.DownloadedFile newest = ((HasDownloads) DRIVER).getDownloadedFiles().stream()
                .filter(candidate -> isNewWorkbook(candidate.getName(), candidate.getSize(), candidate.getLastModifiedTime()))
                .filter(candidate -> candidate.getSize() > 0)
                .max(Comparator.comparingLong(HasDownloads.DownloadedFile::getLastModifiedTime))
                .orElse(null);
        if (newest == null) {
            pendingWorkbookName = null;
            pendingWorkbookSize = -1;
            return null;
        }
        return rememberStableSize(newest.getName(), newest.getSize()) ? newest : null;
    }

    private boolean isNewWorkbook(String name, long size, long modified) {
        if (!name.matches(WORKBOOK_NAME) || size <= 0) {
            return false;
        }
        String previous = workbooksBeforeClick.get(name);
        return previous == null || !previous.equals(size + ":" + modified);
    }

    private static long sizeOf(Path path) {
        try {
            return Files.size(path);
        } catch (IOException exception) {
            return -1;
        }
    }

    private boolean rememberStableSize(String name, long size) {
        boolean stable = name.equals(pendingWorkbookName) && size == pendingWorkbookSize;
        pendingWorkbookName = name;
        pendingWorkbookSize = size;
        return stable;
    }

    private Map<String, String> workbookNames() {
        try {
            if (DriverUtil.isLocalExecution()) {
                Path downloads = Paths.get(System.getProperty("user.home"), "Downloads");
                if (!Files.isDirectory(downloads)) {
                    return Map.of();
                }
                try (Stream<Path> list = Files.list(downloads)) {
                    return list.filter(Files::isRegularFile)
                            .filter(path -> path.getFileName().toString().matches(WORKBOOK_NAME))
                            .collect(Collectors.toMap(
                                    path -> path.getFileName().toString(),
                                    path -> sizeOf(path) + ":" + path.toFile().lastModified(),
                                    (left, right) -> right));
                }
            }
            return ((HasDownloads) DRIVER).getDownloadedFiles().stream()
                    .filter(candidate -> candidate.getName().matches(WORKBOOK_NAME))
                    .collect(Collectors.toMap(
                            HasDownloads.DownloadedFile::getName,
                            candidate -> candidate.getSize() + ":" + candidate.getLastModifiedTime(),
                            (left, right) -> right));
        } catch (IOException exception) {
            return Map.of();
        }
    }
}
