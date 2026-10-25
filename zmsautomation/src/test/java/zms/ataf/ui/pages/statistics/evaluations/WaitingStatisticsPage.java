package zms.ataf.ui.pages.statistics.evaluations;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.time.Duration;
import java.time.format.DateTimeFormatter;
import java.util.Comparator;
import java.util.List;
import java.util.Map;
import java.util.stream.Collectors;
import java.util.stream.Stream;

import org.openqa.selenium.By;
import org.openqa.selenium.HasDownloads;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.utils.DriverUtil;
import zms.ataf.helpers.WaitingTimeArchive.HourAverage;
import zms.ataf.ui.pages.statistics.StatisticsPage;
import zms.ataf.ui.pages.statistics.StatisticsPageContext;

/**
 * Wartestatistik GUI and the three-sheet workbook.
 * Hours outside 07–18 are stored by the calculator but not rendered.
 */
public class WaitingStatisticsPage extends StatisticsPage {

    private static final String WORKBOOK_NAME = "waitingstatistic_.*\\.xlsx";
    private static final DateTimeFormatter HEADER_DAY = DateTimeFormatter.ofPattern("dd.MM.");

    private Map<String, String> workbooksBeforeClick = Map.of();
    private Path downloadedWorkbook;
    private String pendingWorkbookName;
    private long pendingWorkbookSize = -1;

    public WaitingStatisticsPage(RemoteWebDriver driver, StatisticsPageContext statisticsPageContext) {
        super(driver, statisticsPageContext);
    }

    public void openFromSidebar() {
        ScenarioLogManager.getLogger().info("Opening Wartestatistik from the sidebar.");
        CONTEXT.set();
        By linkLocator = By.xpath("//a[normalize-space()='Wartestatistik']");
        WebElement link = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.elementToBeClickable(linkLocator));
        scrollToCenterByVisibleElement(link);
        ((JavascriptExecutor) DRIVER).executeScript("arguments[0].click();", link);
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.presenceOfElementLocated(
                        By.xpath("//h1[contains(normalize-space(),'Wartestatistik')]")));
    }

    public void assertHourAverages(List<HourAverage> averages) {
        for (HourAverage average : averages) {
            String label = average.hour + " - " + (average.hour + 1) + " Uhr";
            if (!average.shownInReport) {
                Assert.assertTrue(
                        DRIVER.findElements(By.xpath("//th[contains(normalize-space(.),'" + label + "')]")).isEmpty(),
                        "Hour " + label + " is outside the waiting-statistic table.");
                continue;
            }
            String dayHeader = average.day.format(HEADER_DAY);
            assertSection("Gesamt", label, dayHeader, average.totalCount, average.totalWaiting, average.totalWay);
            assertSection("Terminkunden", label, dayHeader, average.appointmentCount,
                    average.appointmentWaiting, average.appointmentWay);
            assertSection("Spontankunden", label, dayHeader, average.spontanCount,
                    average.spontanWaiting, average.spontanWay);
        }
    }

    public void downloadWorkbook() {
        downloadedWorkbook = null;
        pendingWorkbookName = null;
        pendingWorkbookSize = -1;
        workbooksBeforeClick = workbookNames();
        clickDownloadButton();
    }

    public void assertDownloadedHourAverages(List<HourAverage> averages) throws IOException {
        Map<String, Map<String, String>> sheets = WaitingStatisticWorkbook.hourValues(workbookFromThisDownload());
        Assert.assertTrue(sheets.containsKey("Gesamt"), "Workbook sheets: " + sheets.keySet());
        Assert.assertTrue(sheets.containsKey("Terminkunden"), "Workbook sheets: " + sheets.keySet());
        Assert.assertTrue(sheets.containsKey("Spontankunden"), "Workbook sheets: " + sheets.keySet());
        for (HourAverage average : averages) {
            String label = average.hour + "-" + (average.hour + 1) + " Uhr";
            if (!average.shownInReport) {
                Assert.assertFalse(sheets.get("Gesamt").keySet().stream().anyMatch(key -> key.endsWith("|" + label)),
                        "Workbook should not list hour " + label);
                continue;
            }
            assertSheet(sheets, "Gesamt", "Durchschnittliche Wartezeit in Min. (Gesamt)", label, average.totalWaiting);
            assertSheet(sheets, "Gesamt", "Durchschnittliche Wegezeit in Min. (Gesamt)", label, average.totalWay);
            assertSheet(sheets, "Terminkunden", "Durchschnittliche Wartezeit in Min. (Terminkunden)", label,
                    average.appointmentWaiting);
            assertSheet(sheets, "Terminkunden", "Durchschnittliche Wegezeit in Min. (Terminkunden)", label,
                    average.appointmentWay);
            assertSheet(sheets, "Spontankunden", "Durchschnittliche Wartezeit in Min. (Spontankunden)", label,
                    average.spontanWaiting);
            assertSheet(sheets, "Spontankunden", "Durchschnittliche Wegezeit in Min. (Spontankunden)", label,
                    average.spontanWay);
        }
    }

    private void assertSection(
            String heading, String hourLabel, String dayHeader, int count, String waiting, String way) {
        String rowXpath = "//h2[normalize-space()='" + heading + "']/following::table[contains(@class,'table--base')][1]"
                + "//tr[th[contains(normalize-space(.),'" + hourLabel + "')]]";
        WebElement row = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.presenceOfElementLocated(By.xpath(rowXpath)));
        scrollToCenterByVisibleElement(row);
        List<WebElement> headers = row.findElements(By.xpath("./ancestor::table[1]//thead//th"));
        int dateColumn = -1;
        for (int i = 0; i < headers.size(); i++) {
            if (headers.get(i).getText().contains(dayHeader)) {
                dateColumn = i;
                break;
            }
        }
        Assert.assertTrue(dateColumn > 0, heading + " has no column " + dayHeader);
        List<WebElement> cells = row.findElements(By.xpath("./td"));
        Assert.assertTrue(cells.size() >= dateColumn, heading + " " + hourLabel + " has no day cell");
        String text = cells.get(dateColumn - 1).getText().trim();
        String[] lines = text.split("\\R");
        List<String> values = java.util.Arrays.stream(lines)
                .map(String::trim)
                .filter(line -> !line.isEmpty())
                .collect(Collectors.toList());
        ScenarioLogManager.getLogger().info("{} {} cell: {}", heading, hourLabel, values);
        Assert.assertTrue(values.size() >= 3, heading + " " + hourLabel + " cell was " + text);
        Assert.assertEquals(values.get(0), Integer.toString(count), heading + " count");
        Assert.assertEquals(values.get(1), waiting, heading + " waiting time");
        Assert.assertEquals(values.get(2), way, heading + " way time");
    }

    private static void assertSheet(
            Map<String, Map<String, String>> sheets,
            String sheet,
            String section,
            String hourLabel,
            String expected) {
        String actual = sheets.get(sheet).get(section + "|" + hourLabel);
        Assert.assertEquals(actual, expected, sheet + " " + section + " " + hourLabel);
    }

    private Path workbookFromThisDownload() {
        if (downloadedWorkbook != null) {
            return downloadedWorkbook;
        }
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.withMessage("The waiting statistic workbook from this download did not finish.");
        if (DriverUtil.isLocalExecution()) {
            Path downloads = Paths.get(System.getProperty("user.home"), "Downloads");
            downloadedWorkbook = wait.until(ignored -> stableNewLocalWorkbook(downloads));
            return downloadedWorkbook;
        }
        HasDownloads.DownloadedFile file = wait.until(ignored -> stableNewRemoteWorkbook());
        try {
            Path directory = Files.createTempDirectory("waitingstatistic-");
            ((HasDownloads) DRIVER).downloadFile(file.getName(), directory);
            downloadedWorkbook = directory.resolve(file.getName());
            return downloadedWorkbook;
        } catch (IOException exception) {
            throw new IllegalStateException("Could not copy the waiting statistic workbook " + file.getName(), exception);
        }
    }

    private Path stableNewLocalWorkbook(Path downloads) {
        List<Path> newcomers;
        try (Stream<Path> list = Files.list(downloads)) {
            newcomers = list.filter(Files::isRegularFile)
                    .filter(path -> isNewWorkbook(path.getFileName().toString(), sizeOf(path)))
                    .sorted(Comparator.comparingLong(path -> path.toFile().lastModified()))
                    .collect(Collectors.toList());
        } catch (IOException exception) {
            return null;
        }
        if (newcomers.isEmpty()) {
            return null;
        }
        if (pendingWorkbookName == null) {
            pendingWorkbookName = newcomers.get(0).getFileName().toString();
            pendingWorkbookSize = -1;
        }
        Path locked = downloads.resolve(pendingWorkbookName);
        long size = sizeOf(locked);
        return rememberStableSize(size) ? locked : null;
    }

    private HasDownloads.DownloadedFile stableNewRemoteWorkbook() {
        List<HasDownloads.DownloadedFile> newcomers = ((HasDownloads) DRIVER).getDownloadedFiles().stream()
                .filter(candidate -> isNewWorkbook(candidate.getName(), candidate.getSize()))
                .sorted(Comparator.comparingLong(HasDownloads.DownloadedFile::getLastModifiedTime))
                .collect(Collectors.toList());
        if (newcomers.isEmpty()) {
            return null;
        }
        if (pendingWorkbookName == null) {
            pendingWorkbookName = newcomers.get(0).getName();
            pendingWorkbookSize = -1;
        }
        HasDownloads.DownloadedFile locked = ((HasDownloads) DRIVER).getDownloadedFiles().stream()
                .filter(candidate -> pendingWorkbookName.equals(candidate.getName()))
                .filter(candidate -> candidate.getSize() > 0)
                .findFirst()
                .orElse(null);
        if (locked == null) {
            return null;
        }
        return rememberStableSize(locked.getSize()) ? locked : null;
    }

    private boolean isNewWorkbook(String name, long size) {
        return name.matches(WORKBOOK_NAME) && size > 0 && !workbooksBeforeClick.containsKey(name);
    }

    private boolean rememberStableSize(long size) {
        boolean stable = size > 0 && size == pendingWorkbookSize;
        pendingWorkbookSize = size;
        return stable;
    }

    private static long sizeOf(Path path) {
        try {
            return Files.size(path);
        } catch (IOException exception) {
            return -1;
        }
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
                                    path -> Long.toString(sizeOf(path)),
                                    (left, right) -> right));
                }
            }
            return ((HasDownloads) DRIVER).getDownloadedFiles().stream()
                    .filter(file -> file.getName().matches(WORKBOOK_NAME))
                    .collect(Collectors.toMap(
                            HasDownloads.DownloadedFile::getName,
                            file -> Long.toString(file.getSize()),
                            (left, right) -> right));
        } catch (IOException exception) {
            return Map.of();
        }
    }
}
