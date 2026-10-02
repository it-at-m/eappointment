package zms.ataf.ui.pages.admin.search;

import java.time.Duration;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.List;

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
        field.clear();
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

    public void assertCustomerListed(String familyName) {
        CONTEXT.set();
        String rowXpath = "//table[contains(@class,'table--base')]//td[contains(.,'" + familyName + "')]";
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.presenceOfElementLocated(By.xpath(rowXpath)));
    }

    public void assertCustomerNotListed(String familyName) {
        CONTEXT.set();
        String rowXpath = "//table[contains(@class,'table--base')]//td[contains(.,'" + familyName + "')]";
        Assert.assertTrue(
                DRIVER.findElements(By.xpath(rowXpath)).isEmpty(),
                "Kundensuche still lists " + familyName);
    }

    public void assertClerkFilterHidden() {
        CONTEXT.set();
        Assert.assertTrue(
                DRIVER.findElements(By.xpath("//label[normalize-space()='Sachbearbeiter']")).isEmpty(),
                "The Sachbearbeiter filter is visible.");
        Assert.assertTrue(
                DRIVER.findElements(By.id("search-user-yes")).isEmpty(),
                "The Sachbearbeiter filter radios are visible.");
    }

    public void assertListedInOrder(String... familyNames) {
        CONTEXT.set();
        List<WebElement> rows = DRIVER.findElements(By.xpath("//table[contains(@class,'table--base')]/tbody/tr"));
        List<String> found = new ArrayList<>();
        for (WebElement row : rows) {
            String text = row.getText();
            for (String familyName : familyNames) {
                if (text.contains(familyName)) {
                    found.add(familyName);
                }
            }
        }
        Assert.assertEquals(found, List.of(familyNames), "Kundensuche row order. Found: " + found);
    }

    /**
     * Compares the name cell, not the whole row. A shorter name is contained in a longer one,
     * so a row-text contains-check would count both names on the longer row.
     */
    public void assertNamesInOrder(List<String> expected) {
        CONTEXT.set();
        List<WebElement> rows = DRIVER.findElements(By.xpath(
                "//table[@data-processList-count]/tbody/tr[td and not(preceding-sibling::tr[th])]"));
        List<String> found = new ArrayList<>();
        for (WebElement row : rows) {
            String cell = row.findElement(By.xpath("./td[1]")).getText().replace('\u00a0', ' ').trim();
            String name = cell.replaceFirst("\\s*\\(.*$", "").trim();
            if (expected.contains(name)) {
                found.add(name);
            }
        }
        Assert.assertEquals(found, expected, "Kundensuche name order. Found: " + found);
    }

    public void assertStatusWithoutCall(String familyName, String statusLabel, String bookingStamp) {
        assertStatusRow(familyName, statusLabel, bookingStamp, null);
    }

    public void assertStatusWithCall(String familyName, String statusLabel, String bookingStamp, String callStamp) {
        assertStatusRow(familyName, statusLabel, bookingStamp, callStamp);
    }

    public String bookingDateDaysAgo(int daysAgo) {
        return BerlinTime.today().minusDays(daysAgo).format(DateTimeFormatter.ofPattern("dd.MM.yyyy"));
    }

    public String bookingStampDaysAgo(int daysAgo, String time) {
        return bookingDateDaysAgo(daysAgo) + ", " + time;
    }

    private void assertStatusRow(String familyName, String statusLabel, String bookingStamp, String callStamp) {
        CONTEXT.set();
        String rowXpath = "//table[contains(@class,'table--base')]//tr["
                + "td[contains(.,'" + familyName + "')] and "
                + "td[contains(.,'Status: " + statusLabel + "')]]";
        WebElement row = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.presenceOfElementLocated(By.xpath(rowXpath)));
        String text = row.getText().replace('\u00a0', ' ');
        ScenarioLogManager.getLogger().info("Kundensuche row for {}: {}", familyName, text);
        Assert.assertTrue(
                text.contains("Buchung: " + bookingStamp),
                "Booking time missing for " + familyName + ". Expected " + bookingStamp + ". Row: " + text);
        if (callStamp == null) {
            Assert.assertFalse(
                    text.contains("Terminaufruf:"),
                    "Call time should be absent for " + familyName + ". Row: " + text);
        } else {
            Assert.assertTrue(
                    text.contains("Terminaufruf: " + callStamp),
                    "Call time missing for " + familyName + ". Expected " + callStamp + ". Row: " + text);
        }
    }
}
