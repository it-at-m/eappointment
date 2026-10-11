package zms.ataf.ui.pages.admin.search;

import java.time.Duration;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.LinkedHashSet;
import java.util.List;
import java.util.Set;

import org.openqa.selenium.By;
import org.openqa.selenium.StaleElementReferenceException;
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
     * Walk-in ids come from a random free slot, so same-day results have no stable order.
     */
    public void assertNames(List<String> expected) {
        CONTEXT.set();
        List<WebElement> rows = DRIVER.findElements(By.xpath(
                "//div[contains(@class,'searchresults')]//table[@data-processList-count]"
                        + "/tbody/tr[td[1]//a and not(preceding-sibling::tr[th])]"));
        Set<String> found = new LinkedHashSet<>();
        for (WebElement row : rows) {
            String cell = row.findElement(By.xpath("./td[1]")).getText().replace('\u00a0', ' ').trim();
            String name = cell.replaceFirst("\\s*\\(.*$", "").trim();
            if (expected.contains(name)) {
                found.add(name);
            }
        }
        Assert.assertEquals(found, new LinkedHashSet<>(expected), "Kundensuche names. Found: " + found);
    }

    public void assertStatusWithoutCall(String familyName, String statusLabel, String bookingStamp) {
        assertStatusRow(familyName, statusLabel, bookingStamp, null);
    }

    public void assertStatusWithCall(String familyName, String statusLabel, String bookingStamp, String callStamp) {
        assertStatusRow(familyName, statusLabel, bookingStamp, callStamp);
    }

    public void assertOneAppointmentWithoutFollowUpRows(String familyName, String numberWithoutLetters) {
        CONTEXT.set();
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.presenceOfElementLocated(
                        By.xpath("//table[contains(@class,'table--base')]//td[contains(.,'" + familyName + "')]")));
        List<WebElement> rows = DRIVER.findElements(By.cssSelector("table.table--base tbody tr"));
        List<String> texts = new ArrayList<>();
        for (WebElement row : rows) {
            texts.add(row.getText().replace('\u00a0', ' '));
        }
        long followUps = texts.stream().filter(text -> text.contains("(Folgetermin)")).count();
        long named = texts.stream().filter(text -> text.contains(familyName) && text.contains(numberWithoutLetters)).count();
        Assert.assertEquals(followUps, 0L, "Follow-up slot rows are listed. Rows: " + texts);
        Assert.assertEquals(named, 1L,
                "The appointment should appear once. Other appointments can contain the same digits. Rows: " + texts);
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

    public void openFoundAppointment(String familyName) {
        CONTEXT.set();
        WebElement link = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.elementToBeClickable(By.xpath(
                        "//table[contains(@class,'table--base')]//a[contains(.,'" + familyName + "')]")));
        link.click();
    }

    public void assertEditFormOpen(String familyName) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.until(ExpectedConditions.urlContains("selectedprocess="));
        String url = DRIVER.getCurrentUrl();
        Assert.assertTrue(url.contains("/workstation"), "The result link did not open the workstation: " + url);
        Assert.assertFalse(url.contains("/counter"), "The result link opened the counter: " + url);
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector("form[data-saved-process]")));
        WebElement name = wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector("input[name='familyName']")));
        Assert.assertEquals(name.getAttribute("value"), familyName,
                "The edit form does not show " + familyName + ".");
        Assert.assertTrue(
                wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector("button.process-save"))).isDisplayed(),
                "The edit form has no save button.");
        Assert.assertTrue(DRIVER.findElements(By.cssSelector(".message--error")).isEmpty(),
                "Opening the appointment shows an error.");
    }

    public void deleteOpenAppointment() {
        CONTEXT.set();
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "button.process-delete", LocatorType.CSSSELECTOR, false, CONTEXT);
        WebElement messageTitleElement = findElementByLocatorType("section.board.dialog h2.board__heading", LocatorType.CSSSELECTOR, false);
        Assert.assertTrue(messageTitleElement.getText().contains("Eintrag löschen"), "Delete confirmation did not open.");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "a.button.button--destructive.button-ok", LocatorType.CSSSELECTOR, false, CONTEXT);
        messageTitleElement = findElementByLocatorType("h2.message__heading.title", LocatorType.CSSSELECTOR, false);
        Assert.assertEquals(messageTitleElement.getText(), "Vorgang gelöscht", "Deleting the open appointment did not succeed.");
        // The dialog covers a spinner that stays on the form, so do not wait for that spinner first.
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "button.button-ok", LocatorType.CSSSELECTOR, false);
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.invisibilityOfElementLocated(By.cssSelector("h2.message__heading.title")));
    }

    /**
     * Innenrevision lands on Suche. The left navigation, and the Kundensuche field inside it, stay hidden.
     * The login does not stop on the location page.
     */
    public void assertCustomerSearchIsTheStartPage() {
        CONTEXT.set();
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.urlContains("/search/"));
        String url = DRIVER.getCurrentUrl();
        Assert.assertFalse(url.contains("/workstation/select/"),
                "The login stopped on the location page: " + url);
        WebElement title = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector("h1.main-title")));
        Assert.assertEquals(title.getText().trim(), "Suche", "The start page is not the customer search.");
        Assert.assertTrue(DRIVER.findElements(By.cssSelector("nav.navigation-primary")).isEmpty(),
                "The left menu is visible.");
        Assert.assertTrue(DRIVER.findElements(By.xpath("//label[normalize-space()='Kundensuche']")).isEmpty(),
                "The Kundensuche field is visible.");
        Assert.assertTrue(DRIVER.findElements(By.cssSelector("select[name='scope']")).isEmpty(),
                "The location selection is shown.");
        Assert.assertTrue(DRIVER.findElements(By.cssSelector(".message--error")).isEmpty(),
                "The customer search shows an error.");
    }

    /** The search on the Suche page. The sidebar Suche button is not on this page. */
    public void submitOverallSearch(String query) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Suche: query {}", query);
        WebElement field = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(By.id("search-query")));
        field.clear();
        enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, query, field);
        clickOnWebElement(
                DEFAULT_EXPLICIT_WAIT_TIME,
                "//form[.//input[@id='search-query']]//button[normalize-space()='Übernehmen']",
                LocatorType.XPATH,
                false,
                CONTEXT);
        // Übernehmen re-renders the Suche page. Edge can replace #search-query between
        // findElements and getAttribute, so a stale node has to be polled again.
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .ignoring(StaleElementReferenceException.class)
                .until(driver -> {
                    List<WebElement> fields = driver.findElements(By.id("search-query"));
                    if (fields.isEmpty()) {
                        return false;
                    }
                    return query.equals(fields.get(0).getAttribute("value"));
                });
    }
}
