package zms.ataf.ui.pages.statistics.evaluations;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.time.Duration;
import java.time.Month;
import java.time.format.TextStyle;
import java.util.Comparator;
import java.util.List;
import java.util.Locale;
import java.util.Map;
import java.util.stream.Stream;

import org.openqa.selenium.By;
import org.openqa.selenium.HasDownloads;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.model.LocatorType;
import ataf.web.utils.DriverUtil;
import zms.ataf.ui.pages.statistics.StatisticsPage;
import zms.ataf.ui.pages.statistics.StatisticsPageContext;


public class ServiceStatisticsPage extends StatisticsPage {

    public ServiceStatisticsPage(RemoteWebDriver driver, StatisticsPageContext statisticsPageContext) {
        super(driver, statisticsPageContext);
    }

    public WebElement getTableCellText(String serviceName, int dayOfMonth) {
        ScenarioLogManager.getLogger().info("Trying to get the value of a cell in the service statistic table...");
        // dayPosition = 0 to get the value of the month
        int dayPosition = dayOfMonth + 2;

        String xpath = String.format(
                "//table[@class='table--base']//tr[th[contains(text(), '%s')]]//td[%d]",
                serviceName, dayPosition
        );
        ScenarioLogManager.getLogger().info("XPATH: " + xpath);
        return findElementByLocatorType(xpath, LocatorType.XPATH, true);
    }

    public boolean checkAvailabilityOfStatisticalInformationForDateAndService(int year, int month, String serviceName) {
        if (!checkAvailabilityOfStatisticalInformationForDate(year, month)) {
            return false;
        }

        String currentMonth = Month.of(month).getDisplayName(TextStyle.FULL_STANDALONE, Locale.GERMAN);
        ScenarioLogManager.getLogger().info("Trying to find service information for " + currentMonth + " " + year + " and service: " + serviceName);

        try {
            WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
            wait.until(
                    ExpectedConditions.visibilityOfElementLocated(By.xpath("//table[@class='table--base']//tr[th[contains(text(), '" + serviceName + "')]]")));
            return true;
        } catch (NoSuchElementException e) {
            ScenarioLogManager.getLogger().warn("Service information for " + serviceName + " not found.");
            return false;
        }
    }

    public void isServiceStatisticDownloaded() {
        isStatisticDownloaded("requeststatistic_\\d{4}-\\d{1,2}(-\\d{2})?\\.xlsx");
    }

    public void assertTableVisible() {
        ScenarioLogManager.getLogger().info("Checking that service statistics table is visible.");
        WebElement table = findElementByLocatorType("//table[@class='table--base']", LocatorType.XPATH, true);
        scrollToCenterByVisibleElement(table);
    }

    public void assertServiceListed(String serviceName) {
        ScenarioLogManager.getLogger().info("Checking that service row is listed: " + serviceName);
        WebElement row = findElementByLocatorType(
                "//table[@class='table--base']//tr[th[contains(normalize-space(.),'" + serviceName + "')]]",
                LocatorType.XPATH, true);
        scrollToCenterByVisibleElement(row);
    }

    public void assertStatisticValues(List<Map<String, String>> rows) {
        for (Map<String, String> row : rows) {
            String label = row.get("Dienstleistung");
            WebElement tableRow = findElementByLocatorType(
                    "//table[contains(@class,'table--base')]//tr[th[contains(normalize-space(.),\"" + label + "\")]]",
                    LocatorType.XPATH, true);
            scrollToCenterByVisibleElement(tableRow);
            String duration = tableRow.findElement(By.xpath("./td[1]")).getText();
            String sum = tableRow.findElement(By.xpath("./td[2]")).getText();
            Assert.assertEquals(normalizeTime(duration), normalizeTime(row.get("Bearbeitungsdauer")),
                    label + " Bearbeitungsdauer");
            Assert.assertEquals(normalizeCount(sum), normalizeCount(row.get("Summe")), label + " Summe");
        }
    }

    public void assertDownloadedStatisticValues(List<Map<String, String>> rows) throws IOException {
        isServiceStatisticDownloaded();
        Map<String, String[]> workbook = RequestStatisticWorkbook.rows(newestRequestStatistic());
        for (Map<String, String> row : rows) {
            String label = row.get("Dienstleistung");
            String[] cells = workbook.get(label);
            Assert.assertNotNull(cells, "XLSX row missing: " + label + ". Rows: " + workbook.keySet());
            Assert.assertEquals(normalizeTime(cells[0]), normalizeTime(row.get("Bearbeitungsdauer")),
                    label + " XLSX Bearbeitungsdauer");
            Assert.assertEquals(normalizeCount(cells[1]), normalizeCount(row.get("Summe")), label + " XLSX Summe");
        }
    }

    private Path newestRequestStatistic() throws IOException {
        String pattern = "requeststatistic_.*\\.xlsx";
        if (DriverUtil.isLocalExecution()) {
            Path downloads = Paths.get(System.getProperty("user.home"), "Downloads");
            try (Stream<Path> walk = Files.walk(downloads)) {
                return walk.filter(Files::isRegularFile)
                        .filter(path -> path.getFileName().toString().matches(pattern))
                        .max(Comparator.comparingLong(path -> path.toFile().lastModified()))
                        .orElseThrow(() -> new IOException("No requeststatistic workbook in " + downloads));
            }
        }
        HasDownloads downloads = (HasDownloads) DRIVER;
        HasDownloads.DownloadedFile file = downloads.getDownloadedFiles().stream()
                .filter(candidate -> candidate.getName().matches(pattern))
                .max(Comparator.comparingLong(HasDownloads.DownloadedFile::getLastModifiedTime))
                .orElseThrow(() -> new IOException("No requeststatistic workbook in the browser downloads"));
        Path target = Files.createTempFile("requeststatistic-", ".xlsx");
        downloads.downloadFile(file.getName(), target);
        return target;
    }

    private static String normalizeTime(String value) {
        String trimmed = value == null ? "" : value.replace('\u00a0', ' ').trim();
        String[] parts = trimmed.split(":");
        if (parts.length != 2) {
            return trimmed;
        }
        return Integer.parseInt(parts[0].trim()) + ":" + String.format("%02d", Integer.parseInt(parts[1].trim()));
    }

    private static String normalizeCount(String value) {
        String trimmed = value == null ? "" : value.replace('\u00a0', ' ').trim();
        if (trimmed.endsWith(".0")) {
            trimmed = trimmed.substring(0, trimmed.length() - 2);
        }
        return trimmed;
    }
}
