package zms.ataf.ui.pages.statistics.evaluations;

import java.time.Duration;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.Locale;

import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.logging.ScenarioLogManager;
import zms.ataf.ui.pages.statistics.StatisticsPage;
import zms.ataf.ui.pages.statistics.StatisticsPageContext;

/**
 * Terminkapazität. The daily total is the default view. Channel, unit, and view
 * are kept in the browser and restored after a new date range or location.
 */
public class CapacityStatisticsPage extends StatisticsPage {

    private static final DateTimeFormatter DAY = DateTimeFormatter.ofPattern("dd.MM.yyyy", Locale.GERMANY);

    public CapacityStatisticsPage(RemoteWebDriver driver, StatisticsPageContext statisticsPageContext) {
        super(driver, statisticsPageContext);
    }

    public void assertOneDayDailyTotal() {
        ScenarioLogManager.getLogger().info("Checking the one-day Tagessumme in the capacity statistics.");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement granularity = wait.until(ExpectedConditions.visibilityOfElementLocated(
                By.cssSelector(".report-board--capacity-granularity-select")));
        Assert.assertEquals(granularity.getAttribute("value"), "day", "The capacity view is not Tagessumme.");
        Assert.assertEquals(
                granularity.findElements(By.cssSelector("option[value='hour']")).size(),
                1,
                "Stundenansicht is not available for this one-day range.");

        Assert.assertEquals(
                DRIVER.findElement(By.cssSelector("th.report-board--capacity-date")).getText().trim(),
                "Datum");

        List<WebElement> days = DRIVER.findElements(
                By.cssSelector("table.report-board--capacity-table td.colDatumTag"));
        Assert.assertEquals(days.size(), 1, "The table does not show exactly one day.");

        String from = DRIVER.findElement(By.id("calendar-date-from")).getAttribute("value");
        String until = DRIVER.findElement(By.id("calendar-date-until")).getAttribute("value");
        Assert.assertEquals(from, until, "The selected range is not a single day.");
        Assert.assertEquals(days.get(0).getText().trim(), LocalDate.parse(from).format(DAY));

        String planned = days.get(0).findElement(By.xpath("following-sibling::td[1]")).getText().trim();
        int plannedSlots = Integer.parseInt(planned.replace(".", ""));
        Assert.assertTrue(plannedSlots > 0, "The day has no planned slots: " + planned);

        WebElement canvas = wait.until(ExpectedConditions.presenceOfElementLocated(By.cssSelector(".chartist canvas")));
        WebElement chart = canvas.findElement(By.xpath(".."));
        Assert.assertFalse(chart.getText().contains("Diagrammdaten konnten nicht geladen werden"),
                "The capacity chart did not render.");
        Assert.assertEquals(chart.getAttribute("data-chart-date-from"), from, "The chart does not show that one day.");
        Assert.assertEquals(chart.getAttribute("data-chart-date-to"), until, "The chart does not show that one day.");
    }

    public void selectCapacityFilter(String selectCss, String value) {
        ScenarioLogManager.getLogger().info("Selecting capacity filter " + selectCss + " = " + value);
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement select = wait.until(ExpectedConditions.presenceOfElementLocated(By.cssSelector(selectCss)));
        ((JavascriptExecutor) DRIVER).executeScript(
                "arguments[0].value = arguments[1];"
                        + "arguments[0].dispatchEvent(new Event('change', {bubbles: true}));",
                select, value);
        Assert.assertEquals(select.getAttribute("value"), value);
    }

    public void assertCapacityFilter(String selectCss, String value) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.withMessage("Capacity filter " + selectCss + " did not stay " + value);
        wait.until(driver -> {
            List<WebElement> selects = driver.findElements(By.cssSelector(selectCss));
            return !selects.isEmpty() && value.equals(selects.get(0).getAttribute("value"));
        });
    }

    public void assertDateFilter(LocalDate from, LocalDate until) {
        Assert.assertEquals(
                DRIVER.findElement(By.id("calendar-date-from")).getAttribute("value"),
                from.format(DateTimeFormatter.ISO_LOCAL_DATE),
                "The start date changed.");
        Assert.assertEquals(
                DRIVER.findElement(By.id("calendar-date-until")).getAttribute("value"),
                until.format(DateTimeFormatter.ISO_LOCAL_DATE),
                "The end date changed.");
    }
}
