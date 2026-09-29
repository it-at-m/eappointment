package zms.ataf.ui.pages.admin.search;

import java.time.Duration;
import java.time.format.DateTimeFormatter;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.model.LocatorType;
import zms.ataf.helpers.BerlinTime;
import zms.ataf.ui.pages.admin.AdminPage;
import zms.ataf.ui.pages.admin.AdminPageContext;

/**
 * Kundensuche on the Sachbearbeiterplatz. The same page can also list log rows when the
 * logged-in user has the logs permission. These checks stay on the process result row.
 */
public class CustomerSearchPage extends AdminPage {

    public CustomerSearchPage(RemoteWebDriver driver, AdminPageContext context) {
        super(driver, context);
    }

    public void search(String query) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Kundensuche: query {}", query);
        String input = "//form[contains(@action,'/search/')]//input[@name='query']";
        WebElement field = findElementByLocatorType(input, LocatorType.XPATH, true);
        enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, query, field);
        clickOnWebElement(
                DEFAULT_EXPLICIT_WAIT_TIME,
                "//form[contains(@action,'/search/')]//button[normalize-space()='Suche']",
                LocatorType.XPATH,
                false,
                CONTEXT);
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.presenceOfElementLocated(By.xpath("//th[normalize-space()='Terminstatus']")));
    }

    public void assertCancelledStatus(String familyName, String statusLabel) {
        CONTEXT.set();
        String today = BerlinTime.today().format(DateTimeFormatter.ofPattern("dd.MM.yyyy"));
        String rowXpath = "//table[contains(@class,'table--base')]//tr["
                + "td[contains(.,'" + familyName + "')] and "
                + "td[contains(.,'Status: " + statusLabel + "')]]";
        WebElement row = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.presenceOfElementLocated(By.xpath(rowXpath)));
        String text = row.getText().replace('\u00a0', ' ');
        ScenarioLogManager.getLogger().info("Kundensuche row for {}: {}", familyName, text);
        Assert.assertTrue(
                text.contains("Buchung: " + today),
                "Booking time missing for " + familyName + " on " + today + ". Row: " + text);
        Assert.assertTrue(
                text.contains("Stornierung: " + today),
                "Cancellation time missing for " + familyName + " on " + today + ". Row: " + text);
        String otherStatus = "Abgesagt durch Kunden".equals(statusLabel)
                ? "Abgesagt durch Sachbearbeitung"
                : "Abgesagt durch Kunden";
        Assert.assertFalse(
                text.contains("Status: " + otherStatus),
                "Row for " + familyName + " shows the other cancellation status. Row: " + text);
    }
}
