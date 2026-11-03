package zms.ataf.ui.pages.calldisplay;

import java.time.Duration;
import java.util.Arrays;
import java.util.List;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.openqa.selenium.By;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.pages.BasePage;

public class CalldisplayPage extends BasePage {

    private static final By DISPLAY = By.id("aufrufanzeige");
    private static final Pattern SCOPE_LIST = Pattern.compile("collections\\.scopelist=\"([^\"]*)\"");
    private static final String[] ERROR_PAGE_MARKERS = {
        "Internal Server Error",
        "Internal server Error",
        "Ein Fehler ist aufgetreten",
        "Es ist ein Fehler aufgetreten",
        "Es konnte kein Standort zur aktuellen Auswahl gefunden werden"
    };

    private final CalldisplayPageContext CONTEXT;

    public CalldisplayPage(RemoteWebDriver driver) {
        super(driver);
        CONTEXT = new CalldisplayPageContext(driver);
    }

    public void open(String scopeList, String template) {
        ScenarioLogManager.getLogger().info("Opening call display for locations {} template {}", scopeList, template);
        CONTEXT.open(scopeList, template);
    }

    public void replaceLocation(String from, String to) {
        CONTEXT.replaceLocation(from, to);
    }

    public void reload() {
        CONTEXT.reload();
    }

    public void assertVisible() {
        try {
            new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                    .until(ExpectedConditions.presenceOfElementLocated(DISPLAY));
        } catch (TimeoutException e) {
            Assert.fail("Call display did not open. currentUrl=" + DRIVER.getCurrentUrl(), e);
        }
        String source = DRIVER.getPageSource();
        for (String marker : ERROR_PAGE_MARKERS) {
            Assert.assertFalse(
                    source.contains(marker),
                    "Call display showed an error page containing \"" + marker + "\". currentUrl="
                            + DRIVER.getCurrentUrl());
        }
        Assert.assertTrue(
                source.contains("id=\"customized\"") || source.contains("id='customized'"),
                "Call display page was not rendered. currentUrl=" + DRIVER.getCurrentUrl());
    }

    public void assertShows(String text) {
        assertVisible();
        Assert.assertTrue(
                DRIVER.getPageSource().contains(text),
                "Call display did not show \"" + text + "\". currentUrl=" + DRIVER.getCurrentUrl());
    }

    public void assertListsLocation(String scopeId) {
        Assert.assertTrue(
                listedLocations().contains(scopeId),
                "Call display scopelist " + listedLocations() + " does not include " + scopeId
                        + ". currentUrl=" + DRIVER.getCurrentUrl());
    }

    public void assertDoesNotListLocation(String scopeId) {
        Assert.assertFalse(
                listedLocations().contains(scopeId),
                "Call display scopelist " + listedLocations() + " still includes missing location " + scopeId
                        + ". currentUrl=" + DRIVER.getCurrentUrl());
    }

    /**
     * ZMS-3213 / ZMSALT-3250: newest called waiting number is top-left
     * (first {@code span.terminkundennummer} in document order).
     */
    public void assertWaitingNumbersNewestFirst(String newerNumber, String olderNumber) {
        assertVisible();
        waitForWaitingNumber(newerNumber);
        waitForWaitingNumber(olderNumber);
        List<String> numbers = DRIVER.findElements(By.cssSelector("span.terminkundennummer")).stream()
                .map(el -> {
                    String appointment = el.getAttribute("data-appointment");
                    if (appointment != null && !appointment.isBlank()) {
                        return appointment.trim();
                    }
                    return el.getText() == null ? "" : el.getText().trim();
                })
                .filter(text -> text != null && !text.isBlank())
                .toList();
        int newerIndex = numbers.indexOf(newerNumber);
        int olderIndex = numbers.indexOf(olderNumber);
        Assert.assertTrue(
                newerIndex >= 0,
                "Call display does not show newer number " + newerNumber + " in " + numbers
                        + ". currentUrl=" + DRIVER.getCurrentUrl());
        Assert.assertTrue(
                olderIndex >= 0,
                "Call display does not show older number " + olderNumber + " in " + numbers
                        + ". currentUrl=" + DRIVER.getCurrentUrl());
        Assert.assertTrue(
                newerIndex < olderIndex,
                "Newest call " + newerNumber + " must appear before older call " + olderNumber
                        + " but order was " + numbers + ". currentUrl=" + DRIVER.getCurrentUrl());
    }

    private void waitForWaitingNumber(String number) {
        try {
            new WebDriverWait(DRIVER, Duration.ofSeconds(Math.max(DEFAULT_EXPLICIT_WAIT_TIME, 20)))
                    .until(ExpectedConditions.or(
                            ExpectedConditions.presenceOfElementLocated(
                                    By.cssSelector("span.terminkundennummer[data-appointment='" + number + "']")),
                            ExpectedConditions.presenceOfElementLocated(
                                    By.xpath("//span[contains(@class,'terminkundennummer')]"
                                            + "[normalize-space()='" + number + "']"))));
        } catch (TimeoutException e) {
            Assert.fail(
                    "Call display did not show waiting number " + number
                            + " within timeout. currentUrl=" + DRIVER.getCurrentUrl(),
                    e);
        }
    }

    private List<String> listedLocations() {
        Matcher matcher = SCOPE_LIST.matcher(DRIVER.getPageSource());
        Assert.assertTrue(matcher.find(), "Call display did not publish collections.scopelist.");
        return Arrays.asList(matcher.group(1).split(","));
    }
}
