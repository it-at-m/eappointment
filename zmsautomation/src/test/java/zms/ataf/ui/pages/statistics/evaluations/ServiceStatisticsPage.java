package zms.ataf.ui.pages.statistics.evaluations;

import java.time.Duration;
import java.time.Month;
import java.time.format.TextStyle;
import java.util.Locale;

import org.openqa.selenium.By;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.model.LocatorType;
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
            WebElement serviceRow = wait.until(
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
}
