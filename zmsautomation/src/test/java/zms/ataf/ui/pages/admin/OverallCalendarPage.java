package zms.ataf.ui.pages.admin;

import java.time.Duration;
import java.time.LocalDate;
import java.time.LocalTime;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.Locale;
import java.util.regex.Matcher;
import java.util.regex.Pattern;
import java.util.stream.Collectors;

import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.helpers.TestDataHelper;
import ataf.web.model.LocatorType;
import zms.ataf.helpers.BerlinTime;

/** Gesamtübersicht, opened from the navigation. */
public class OverallCalendarPage extends AdminPage {

    public OverallCalendarPage(RemoteWebDriver driver, AdminPageContext adminPageContext) {
        super(driver, adminPageContext);
    }

    /** Gesamtübersicht cell for the booked number. The title is "HH:mm – HH:mm". */
    public void assertOverallCalendarAppointmentSpansMinutes(int minutes) {
        CONTEXT.set();
        CONTEXT.waitForSpinners();
        String number = TestDataHelper.getTestData("new_appointment_number");
        Assert.assertNotNull(number, "No booked appointment number for the Gesamtübersicht.");
        String iso = TestDataHelper.getTestData("new_appointment_iso_date");
        if (iso == null || iso.isBlank()) {
            String german = TestDataHelper.getTestData("new_appointment_date");
            if (german != null && !german.isBlank()) {
                iso = LocalDate.parse(german, DateTimeFormatter.ofPattern("dd.MM.yyyy", Locale.GERMAN))
                        .format(DateTimeFormatter.ISO_LOCAL_DATE);
            } else {
                iso = BerlinTime.today().format(DateTimeFormatter.ISO_LOCAL_DATE);
            }
        }
        WebElement from = findElementByLocatorType("calendar-date-from", LocatorType.ID, false);
        WebElement until = findElementByLocatorType("calendar-date-until", LocatorType.ID, false);
        ((JavascriptExecutor) DRIVER).executeScript(
                "arguments[0].value=arguments[2]; arguments[1].value=arguments[2];", from, until, iso);
        Select scopes = new Select(findElementByLocatorType("scope-select", LocatorType.ID, false));
        scopes.deselectAll();
        scopes.selectByValue("319");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[normalize-space()='Übernehmen']", LocatorType.XPATH, false, CONTEXT);
        By cellLabel = By.xpath(
                "//span[contains(@class,'overall-calendar-termin-label') and normalize-space(.)='" + number + "']");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement label = wait.until(ExpectedConditions.visibilityOfElementLocated(cellLabel));
        WebElement cell = label.findElement(By.xpath("./parent::*"));
        String title = cell.getAttribute("title");
        Assert.assertNotNull(title, "Gesamtübersicht cell for " + number + " has no time title.");
        Matcher matcher = Pattern.compile("(\\d{2}:\\d{2})\\s*[–-]\\s*(\\d{2}:\\d{2})").matcher(title);
        Assert.assertTrue(matcher.find(), "Could not read the appointment span from \"" + title + "\".");
        int span = (int) java.time.Duration.between(LocalTime.parse(matcher.group(1)), LocalTime.parse(matcher.group(2)))
                .toMinutes();
        Assert.assertEquals(span, minutes, "Gesamtübersicht span for " + number + " from title \"" + title + "\".");
    }

    private String overallViewDay;
    private String overallViewLocation;
    private int overallViewScopeId;

    /**
     * One location, one day. The day is the date already offered as "Von", so it stays inside the
     * calendar's allowed range.
     */
    public void showLocationInOverallViewForOneDay(String locationName, int scopeId) {
        CONTEXT.set();
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement from = wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("calendar-date-from")));
        String date = from.getAttribute("value");
        if (date == null || date.isBlank()) {
            throw new IllegalStateException("The overall view has no start date.");
        }
        overallViewDay = date;
        overallViewLocation = locationName;
        overallViewScopeId = scopeId;
        WebElement until = DRIVER.findElement(By.id("calendar-date-until"));
        ((JavascriptExecutor) DRIVER).executeScript(
                "arguments[0].value=arguments[2]; arguments[1].value=arguments[2];", from, until, date);
        Select scopes = new Select(wait.until(ExpectedConditions.presenceOfElementLocated(By.id("scope-select"))));
        scopes.deselectAll();
        scopes.selectByValue(Integer.toString(scopeId));
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[normalize-space()='Übernehmen']", LocatorType.XPATH, false, CONTEXT);
    }

    public void assertOverallViewLinksLocationToOpeningHours(String linkLabel) {
        CONTEXT.set();
        if (overallViewDay == null || overallViewLocation == null || overallViewScopeId <= 0) {
            throw new IllegalStateException("The overall view day and location were not chosen.");
        }
        String locationName = overallViewLocation;
        int scopeId = overallViewScopeId;
        String hrefPart = "/scope/" + scopeId + "/availability/day/" + overallViewDay + "/";
        By linkBy = By.xpath(
                "//*[contains(@class,'overall-calendar-scope-header')]"
                        + "[.//*[contains(@class,'overall-calendar-scope-name') and contains(normalize-space(.),\""
                        + locationName + "\")]]"
                        + "//a[@title=\"" + linkLabel + "\"]");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement link = wait.until(ExpectedConditions.presenceOfElementLocated(linkBy));
        String href = link.getAttribute("href");
        Assert.assertNotNull(href, "The opening-hours link has no address.");
        Assert.assertTrue(href.contains(hrefPart),
                "The opening-hours link should point at " + hrefPart + " but was " + href);
        Assert.assertEquals(link.getAttribute("target"), "_blank",
                "The opening hours should open in a new tab.");
        Assert.assertEquals(link.getAttribute("title"), linkLabel,
                "The link label should say where it leads.");
        Assert.assertFalse(link.findElements(By.cssSelector("i.fa-clock")).isEmpty(),
                "The opening-hours link should show the clock icon.");
        String dayPart = LocalDate.parse(overallViewDay).format(DateTimeFormatter.ofPattern("dd.MM."));
        WebElement dayLabel = DRIVER.findElement(By.cssSelector(".overall-calendar-day-label"));
        Assert.assertTrue(dayLabel.getText().contains(dayPart),
                "The day header should show " + dayPart + " but was \"" + dayLabel.getText() + "\".");
    }

    /**
     * A walk-in window that is outside the appointment hours of the same opening.
     * {@code gapEnd} is exclusive. {@code appointmentStart} is a time that must still be shown.
     */
    public static final class WalkInOpening {
        public final int scopeId;
        public final LocalDate day;
        public final LocalTime gapStart;
        public final LocalTime gapEnd;
        public final LocalTime appointmentStart;

        public WalkInOpening(int scopeId, LocalDate day, LocalTime gapStart, LocalTime gapEnd, LocalTime appointmentStart) {
            this.scopeId = scopeId;
            this.day = day;
            this.gapStart = gapStart;
            this.gapEnd = gapEnd;
            this.appointmentStart = appointmentStart;
        }
    }

    private LocalDate overallViewStart;
    private WalkInOpening walkInOpening;

    public LocalDate overallViewStart() {
        if (overallViewStart == null) {
            throw new IllegalStateException("The overall view has no start date.");
        }
        return overallViewStart;
    }

    public List<String> overallViewScopeOptionValues() {
        Select scopes = new Select(DRIVER.findElement(By.id("scope-select")));
        return scopes.getOptions().stream().map(option -> option.getAttribute("value")).collect(Collectors.toList());
    }

    public void showEveryLocationInOverallView(int days) {
        CONTEXT.set();
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(90));
        WebElement toggle = wait.until(ExpectedConditions.elementToBeClickable(By.id("select-all-scopes")));
        toggle.click();
        WebElement from = DRIVER.findElement(By.id("calendar-date-from"));
        String startValue = from.getAttribute("value");
        if (startValue == null || startValue.isBlank()) {
            throw new IllegalStateException("The overall view has no start date.");
        }
        overallViewStart = LocalDate.parse(startValue);
        LocalDate until = overallViewStart.plusDays(days - 1L);
        WebElement untilInput = DRIVER.findElement(By.id("calendar-date-until"));
        ((JavascriptExecutor) DRIVER).executeScript(
                "arguments[0].value=arguments[1]; arguments[2].value=arguments[3];",
                from, startValue, untilInput, until.toString());
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[normalize-space()='Übernehmen']", LocatorType.XPATH, false, CONTEXT);
        wait.until(ExpectedConditions.numberOfElementsToBeMoreThan(By.cssSelector(".overall-calendar-day-label"), 1));
        wait.until(ExpectedConditions.numberOfElementsToBeMoreThan(By.cssSelector(".overall-calendar-scope-header"), 0));
    }

    public void openOverallViewFullScreen() {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement button = wait.until(ExpectedConditions.elementToBeClickable(By.id("calendar-fullscreen")));
        button.click();
        wait.until(ExpectedConditions.attributeContains(
                By.cssSelector(".overall-calendar-wrapper"), "class", "fullscreen"));
    }

    public void scrollOverallViewToTheRight() {
        Object scrolled = ((JavascriptExecutor) DRIVER).executeScript(
                "var wrapper = document.querySelector('.overall-calendar-wrapper');"
                        + "wrapper.scrollLeft = wrapper.scrollWidth;"
                        + "return wrapper.scrollLeft;");
        Assert.assertTrue(scrolled instanceof Number && ((Number) scrolled).intValue() > 0,
                "The overall view did not scroll sideways.");
    }

    public void assertEachVisibleLocationShowsItsDate() {
        Object result = ((JavascriptExecutor) DRIVER).executeScript(
                "var wrapper = document.querySelector('.overall-calendar-wrapper');"
                        + "var view = wrapper.getBoundingClientRect();"
                        + "function span(el) {"
                        + "  var parts = el.style.gridColumn.split('/');"
                        + "  var start = parseInt(parts[0], 10);"
                        + "  var width = parseInt(parts[1].replace('span', ''), 10);"
                        + "  return [start, start + width - 1];"
                        + "}"
                        + "var days = Array.from(document.querySelectorAll('.overall-calendar-day-header'));"
                        + "var scopes = Array.from(document.querySelectorAll('.overall-calendar-scope-header'));"
                        + "if (scopes.length === 0) return 'no locations';"
                        + "var seen = 0;"
                        + "for (var i = 0; i < scopes.length; i++) {"
                        + "  var box = scopes[i].getBoundingClientRect();"
                        + "  if (box.right < view.left || box.left > view.right || box.width === 0) continue;"
                        + "  seen++;"
                        + "  var place = span(scopes[i]);"
                        + "  var day = null;"
                        + "  for (var d = 0; d < days.length; d++) {"
                        + "    var dayPlace = span(days[d]);"
                        + "    if (place[0] >= dayPlace[0] && place[1] <= dayPlace[1]) day = days[d];"
                        + "  }"
                        + "  if (!day) return 'location without a day';"
                        + "  var label = day.querySelector('.overall-calendar-day-label');"
                        + "  if (!label || !/\\d{2}\\.\\d{2}\\./.test(label.textContent)) return 'day has no date';"
                        + "  var labelBox = label.getBoundingClientRect();"
                        + "  if (labelBox.right < view.left || labelBox.left > view.right || labelBox.width < 2) {"
                        + "    return 'date not in view: ' + label.textContent;"
                        + "  }"
                        + "}"
                        + "return seen > 0 ? 'ok' : 'no location in view';");
        Assert.assertEquals(String.valueOf(result), "ok", "A shown location has no readable date.");
    }

    public void assertOverallViewHasNoAxisLabels(String rowLabel, String columnLabel) {
        List<WebElement> corners = DRIVER.findElements(By.cssSelector("#overall-calendar .overall-calendar-empty-header"));
        Assert.assertEquals(corners.size(), 2, "The overall view should have two empty corner cells.");
        for (WebElement corner : corners) {
            String text = corner.getText().trim();
            Assert.assertNotEquals(text, rowLabel, "The row label should be gone.");
            Assert.assertNotEquals(text, columnLabel, "The column label should be gone.");
            Assert.assertTrue(text.isEmpty(), "The corner cell should be empty but was \"" + text + "\".");
        }
    }

    public void assertHourLabelSitsOnTheHourRow() {
        Object result = ((JavascriptExecutor) DRIVER).executeScript(
                "var label = document.querySelector('.overall-calendar-time-hour .overall-calendar-time-label');"
                        + "if (!label || !/^\\d{2}:00$/.test(label.textContent.trim())) return 'missing hour label';"
                        + "var row = label.parentElement.style.gridRow.split('/')[0].trim();"
                        + "var stripes = Array.from(document.querySelectorAll('.overall-calendar-stripe-hour'));"
                        + "var stripe = stripes.find(function (item) {"
                        + "  return item.style.gridRow.split('/')[0].trim() === row;"
                        + "});"
                        + "if (!stripe) return 'missing hour line';"
                        + "var labelBox = label.getBoundingClientRect();"
                        + "var lineBox = stripe.getBoundingClientRect();"
                        + "var labelMiddle = labelBox.top + labelBox.height / 2;"
                        + "var lineMiddle = lineBox.top + lineBox.height / 2;"
                        + "if (labelMiddle <= lineMiddle) return 'the hour label sits on the hour line';"
                        + "return 'ok';");
        Assert.assertEquals(String.valueOf(result), "ok", "The hour label should sit on the first row of the hour.");
    }

    public void assertDayLinesKeepOneWidth() {
        Object result = ((JavascriptExecutor) DRIVER).executeScript(
                "var widths = Array.from(document.querySelectorAll('.overall-calendar-day-separator')).map(function (line) {"
                        + "  return Math.round(line.getBoundingClientRect().width);"
                        + "});"
                        + "if (widths.length < 2) return 'not enough day lines';"
                        + "var same = widths.every(function (width) { return width === widths[0] && width >= 3; });"
                        + "if (!same) return 'day line widths ' + widths.slice(0, 6).join(',');"
                        + "function sideBorder(selector) {"
                        + "  var style = getComputedStyle(document.querySelector(selector));"
                        + "  return style.borderLeftWidth === '0px' && style.borderRightWidth === '0px';"
                        + "}"
                        + "if (!sideBorder('.overall-calendar-scope-header')) return 'location header has a side border';"
                        + "if (!sideBorder('.overall-calendar-day-header')) return 'day header has a side border';"
                        + "return 'ok';");
        Assert.assertEquals(String.valueOf(result), "ok", "The day lines should keep one width.");
    }

    public void showWalkInOpening(WalkInOpening opening) {
        CONTEXT.set();
        walkInOpening = opening;
        WebElement fullscreen = DRIVER.findElement(By.id("calendar-fullscreen"));
        if (DRIVER.findElement(By.cssSelector(".overall-calendar-wrapper")).getAttribute("class").contains("fullscreen")) {
            fullscreen.click();
        }
        WebElement from = DRIVER.findElement(By.id("calendar-date-from"));
        WebElement until = DRIVER.findElement(By.id("calendar-date-until"));
        String day = opening.day.toString();
        ((JavascriptExecutor) DRIVER).executeScript(
                "arguments[0].value=arguments[2]; arguments[1].value=arguments[2];", from, until, day);
        Select scopes = new Select(DRIVER.findElement(By.id("scope-select")));
        scopes.deselectAll();
        scopes.selectByValue(Integer.toString(opening.scopeId));
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[normalize-space()='Übernehmen']", LocatorType.XPATH, false, CONTEXT);
        String dayPart = opening.day.format(DateTimeFormatter.ofPattern("dd.MM."));
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .ignoring(StaleElementReferenceException.class)
                .until(driver -> driver.findElements(By.cssSelector(".overall-calendar-scope-header")).size() == 1
                        && driver.findElements(By.cssSelector(".overall-calendar-day-label")).size() == 1
                        && driver.findElement(By.cssSelector(".overall-calendar-day-label")).getText().contains(dayPart)
                        && !driver.findElements(By.cssSelector("#overall-calendar .overall-calendar-open")).isEmpty());
    }

    public void assertWalkInHoursAreNotWhite() {
        if (walkInOpening == null) {
            throw new IllegalStateException("No walk-in opening was chosen.");
        }
        DateTimeFormatter clock = DateTimeFormatter.ofPattern("HH:mm");
        String appointment = walkInOpening.appointmentStart.format(clock);
        boolean appointmentShown = false;
        Object titles = ((JavascriptExecutor) DRIVER).executeScript(
                "return Array.from(document.querySelectorAll('#overall-calendar .overall-calendar-open')).map(function (cell) {"
                        + "return cell.getAttribute('title') || '';"
                        + "});");
        if (!(titles instanceof List<?>)) {
            throw new IllegalStateException("The overall view returned no opening cells.");
        }
        for (Object titleObject : (List<?>) titles) {
            String title = String.valueOf(titleObject);
            Matcher matcher = Pattern.compile("(\\d{2}:\\d{2})\\s*[–-]\\s*(\\d{2}:\\d{2})").matcher(title);
            if (!matcher.find()) {
                continue;
            }
            LocalTime start = LocalTime.parse(matcher.group(1));
            if (!start.isBefore(walkInOpening.gapStart) && start.isBefore(walkInOpening.gapEnd)) {
                Assert.fail("Walk-in time " + title + " is shown in white.");
            }
            if (matcher.group(1).equals(appointment)) {
                appointmentShown = true;
            }
        }
        Assert.assertTrue(appointmentShown,
                "The appointment hour " + appointment + " should still be shown for scope " + walkInOpening.scopeId + ".");
    }

    public void assertEmptyDaysAreShownByDefault() {
        CONTEXT.set();
        WebElement show = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.presenceOfElementLocated(
                        By.cssSelector("input[name='emptyDaysVisibility'][value='show']")));
        Assert.assertTrue(show.isSelected(),
                "Days without opening hours should be shown until someone chooses to hide them.");
    }

    public void showOverallViewScopes(LocalDate from, LocalDate until, boolean hideDaysWithoutOpeningHours, int... scopeIds) {
        CONTEXT.set();
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement fromInput = wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("calendar-date-from")));
        WebElement untilInput = DRIVER.findElement(By.id("calendar-date-until"));
        ((JavascriptExecutor) DRIVER).executeScript(
                "arguments[0].value=arguments[2]; arguments[1].value=arguments[3];",
                fromInput, untilInput, from.toString(), until.toString());
        chooseEmptyDaysVisibility(hideDaysWithoutOpeningHours);
        Select scopes = new Select(wait.until(ExpectedConditions.presenceOfElementLocated(By.id("scope-select"))));
        scopes.deselectAll();
        for (int scopeId : scopeIds) {
            scopes.selectByValue(Integer.toString(scopeId));
        }
        String generation = "overall-" + System.nanoTime();
        ((JavascriptExecutor) DRIVER).executeScript(
                "var calendar = document.getElementById('overall-calendar');"
                        + "if (!calendar) return;"
                        + "calendar.textContent = '';"
                        + "calendar.setAttribute('data-sample', arguments[0]);",
                generation);
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[normalize-space()='Übernehmen']", LocatorType.XPATH, false, CONTEXT);
        wait.ignoring(StaleElementReferenceException.class).until(driver -> {
            List<WebElement> calendars = driver.findElements(By.id("overall-calendar"));
            if (calendars.isEmpty()) {
                return false;
            }
            WebElement calendar = calendars.get(0);
            String text = calendar.getText();
            if (text != null && text.contains("Keine Daten verfügbar.")) {
                return true;
            }
            return !generation.equals(calendar.getAttribute("data-sample"))
                    && !calendar.findElements(By.cssSelector(".overall-calendar-day-label")).isEmpty();
        });
    }

    public void chooseEmptyDaysVisibility(boolean hideDaysWithoutOpeningHours) {
        String value = hideDaysWithoutOpeningHours ? "hide" : "show";
        Object result = ((JavascriptExecutor) DRIVER).executeScript(
                "var radio = document.querySelector('input[name=\"emptyDaysVisibility\"][value=\"' + arguments[0] + '\"]');"
                        + "if (!radio) return 'missing';"
                        + "radio.checked = true;"
                        + "radio.dispatchEvent(new Event('change', {bubbles: true}));"
                        + "return radio.checked ? 'ok' : 'not checked';",
                value);
        Assert.assertEquals(String.valueOf(result), "ok",
                "The overall view should offer " + (hideDaysWithoutOpeningHours ? "Ausblenden" : "Einblenden") + ".");
    }

    public void assertOverallViewHasNoData() {
        CONTEXT.set();
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.textToBePresentInElementLocated(
                        By.id("overall-calendar"), "Keine Daten verfügbar."));
    }

    public void assertOverallViewDayCount(int days) {
        CONTEXT.set();
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .ignoring(StaleElementReferenceException.class)
                .until(ExpectedConditions.numberOfElementsToBe(By.cssSelector(".overall-calendar-day-label"), days));
    }

    public void assertOverallViewDays(List<LocalDate> shown, List<LocalDate> hidden) {
        CONTEXT.set();
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.ignoring(StaleElementReferenceException.class);
        try {
            wait.until(driver -> overallDayLabelsMatch(shown, hidden));
        } catch (TimeoutException e) {
            Assert.fail("Overall view days did not match. Shown " + shown + ", hidden " + hidden
                    + ", labels " + overallDayLabels() + ".");
        }
    }

    public void assertOverallViewShowsAppointment(int processId) {
        CONTEXT.set();
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .ignoring(StaleElementReferenceException.class)
                .until(ExpectedConditions.visibilityOfElementLocated(By.xpath(
                        "//span[contains(@class,'overall-calendar-termin-label') and normalize-space(.)='"
                                + processId + "']")));
    }

    public void assertOverallViewLocation(String name, boolean shown) {
        CONTEXT.set();
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.ignoring(StaleElementReferenceException.class);
        try {
            wait.until(driver -> overallLocationShown(name) == shown);
        } catch (TimeoutException e) {
            Assert.fail("Location \"" + name + "\" should be " + (shown ? "shown" : "hidden")
                    + ". Headers: " + overallLocationNames() + ".");
        }
    }

    private boolean overallDayLabelsMatch(List<LocalDate> shown, List<LocalDate> hidden) {
        List<String> labels = overallDayLabels();
        if (shown.stream().anyMatch(day -> labels.stream().noneMatch(text -> text.contains(overallDayPart(day))))) {
            return false;
        }
        return hidden.stream().noneMatch(day -> labels.stream().anyMatch(text -> text.contains(overallDayPart(day))));
    }

    private List<String> overallDayLabels() {
        return DRIVER.findElements(By.cssSelector(".overall-calendar-day-label")).stream()
                .map(WebElement::getText)
                .collect(Collectors.toList());
    }

    private boolean overallLocationShown(String name) {
        return overallLocationNames().stream().anyMatch(text -> text.contains(name));
    }

    private List<String> overallLocationNames() {
        return DRIVER.findElements(By.cssSelector(".overall-calendar-scope-name")).stream()
                .map(WebElement::getText)
                .collect(Collectors.toList());
    }

    private static String overallDayPart(LocalDate day) {
        return day.format(DateTimeFormatter.ofPattern("dd.MM."));
    }
}
