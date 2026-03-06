package zms.ataf.ui.pages.statistics;

import java.io.File;
import java.io.IOException;
import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.time.Duration;
import java.time.LocalDate;
import java.time.Month;
import java.time.format.DateTimeFormatter;
import java.time.format.TextStyle;
import java.util.Arrays;
import java.util.List;
import java.util.Locale;
import java.util.stream.Collectors;
import java.util.stream.Stream;

import org.openqa.selenium.By;
import org.openqa.selenium.HasDownloads;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.helpers.AuthenticationHelper;
import ataf.core.helpers.TestDataHelper;
import ataf.core.helpers.TestPropertiesHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.core.properties.DefaultValues;
import ataf.web.model.LocatorType;
import ataf.web.pages.BasePage;
import ataf.web.utils.DriverUtil;
import zms.ataf.data.TestData;


public class StatisticsPage extends BasePage {
    protected final StatisticsPageContext CONTEXT;

    //aktueller Monat (auf deutsch)
    private final String currentMonth = LocalDate.now().getMonth().getDisplayName(TextStyle.FULL_STANDALONE, Locale.GERMAN);

    public StatisticsPage(RemoteWebDriver driver) {
        super(driver);
        CONTEXT = new StatisticsPageContext(driver);
    }

    public StatisticsPage(RemoteWebDriver driver, StatisticsPageContext statisticsPageContext) {
        super(driver);
        CONTEXT = statisticsPageContext;
    }

    public StatisticsPageContext getContext() {
        return CONTEXT;
    }

    public String getCurrentMonth() {
        return currentMonth;
    }

    public void navigateToPage() {
        CONTEXT.navigateToPage();
    }

    public void clickOnLoginButton() throws Exception {
        ScenarioLogManager.getLogger().info("Trying to click on \"Login\" button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[@type='submit' and @value='keycloak']", LocatorType.XPATH, false);
        if (!DriverUtil.isLocalExecution() || TestPropertiesHelper.getPropertyAsBoolean("useIncognitoMode", true, DefaultValues.USE_INCOGNITO_MODE)) {
            ScenarioLogManager.getLogger().info("SSO-Login page detected!");

            final StringBuilder clearUserName = new StringBuilder();
            final StringBuilder clearPassword = new StringBuilder();
            Exception exception = null;
            try {
                AuthenticationHelper.getUserName().access(clearUserName::append);
                AuthenticationHelper.getUserPassword().access(clearPassword::append);
                // Wait for Keycloak login form (local and ssodev both use id="username")
                WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
                wait.until(ExpectedConditions.presenceOfElementLocated(By.id("username")));
                if ("chrome".equals(
                        TestPropertiesHelper.getPropertyAsString("browser", true, DefaultValues.BROWSER))) {
                    String ssoHost = TestData.getSsoHost();
                    wait.until(ExpectedConditions.presenceOfElementLocated(By.id("kc-login")));
                    DRIVER.navigate().to(DRIVER.getCurrentUrl().replaceFirst("https://",
                            "https://" + URLEncoder.encode(clearUserName.toString(), StandardCharsets.UTF_8) + ":" + URLEncoder.encode(clearPassword.toString(),
                                    StandardCharsets.UTF_8) + "@"));
                }

                ScenarioLogManager.getLogger().info("Trying to enter user name...");
                enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, clearUserName.toString(), "username", LocatorType.ID);

                ScenarioLogManager.getLogger().info("Trying to enter password...");
                enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, clearPassword.toString(), "password", LocatorType.ID);

                ScenarioLogManager.getLogger().info("Trying to click on \"Login\" button...");
                clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "kc-login", LocatorType.ID, false);
                ScenarioLogManager.getLogger().info("SSO login submitted successfully.");
            } catch (Exception e) {
                ScenarioLogManager.getLogger().error(e.getMessage(), e);
                exception = e;
            } finally {
                clearUserName.setLength(0);
                clearPassword.setLength(0);
                if (exception != null) {
                    throw exception;
                }
            }
        }
    }

    public void selectLocation(String location) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to select location \"" + location + "\"");
        selectDropDownListValueByVisibleText(DEFAULT_EXPLICIT_WAIT_TIME, "scope", LocatorType.NAME, location);
        TestDataHelper.setTestData("location", location);
    }

    public void clickOnApplySelectionButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Auswahl bestätigen\" button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[@type='submit' and @value='weiter']", LocatorType.XPATH, false, CONTEXT);
    }

    public void checkIfTheOverviewPageIsOpen() {
        ScenarioLogManager.getLogger().info("Checking if the overview page is visible.");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//h1[normalize-space()='Übersicht verfügbarer Statistiken']", LocatorType.XPATH, true),
                "'Overview page is not visible!");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//div[@data-includeurl='/terminvereinbarung/statistic']", LocatorType.XPATH, true),
                "'Overview page is not visible!");
    }

    public void clickOnCustomerStatistics() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Kundenstatistik\" button in the sidebar...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//a[normalize-space()='Kundenstatistik']", LocatorType.XPATH, false, CONTEXT);
    }

    public void checkIfStatisticsPageIsOpen(String pageName) {
        ScenarioLogManager.getLogger().info("Checking if the " + pageName + " statistics page is visible.");

        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME,
                        "//h1[contains(normalize-space(.),'" + pageName + "')]",
                        LocatorType.XPATH, true),
                "'Statistics page heading \"" + pageName + "\" is not visible!");

        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME,
                        "//li[normalize-space()='" + pageName + " Standort']",
                        LocatorType.XPATH, true),
                "'Statistics page \"" + pageName + " Standort\" is not visible after applying filter!");
    }

    public void clickOnServiceStatistics() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Dienstleistungsstatistik\" button in the sidebar...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//a[normalize-space()='Dienstleistungsstatistik']", LocatorType.XPATH, false, CONTEXT);
    }

    public void applyLocationAndDateFilter(String location) {
        boolean filterPresent = isWebElementVisible(5,
                "//*[self::button or self::input][normalize-space(text())='Übernehmen' or normalize-space(@value)='Übernehmen']",
                LocatorType.XPATH, false);
        if (!filterPresent) {
            ScenarioLogManager.getLogger().warn("Location/date filter panel not present on statistics page.");
            return;
        }

        ScenarioLogManager.getLogger().info(
                "Location/date filter panel detected. Applying location for statistics sub-page...");

        if (location == null || location.isEmpty()) {
            ScenarioLogManager.getLogger().warn("No location provided for statistics filter; skipping filter application.");
            return;
        }

        ScenarioLogManager.getLogger().info("Selecting statistics location: " + location);
        try {
            selectDropDownListValueByVisibleText(DEFAULT_EXPLICIT_WAIT_TIME, "scope", LocatorType.NAME, location);
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn(
                    "Failed to select location via drop-down, trying fallback option click. Cause: " + e.getMessage());
            clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME,
                    "//select[@name='scope']/option[normalize-space()='" + location + "']",
                    LocatorType.XPATH, false);
        }
    }

    public void applyDateRangeFilter(LocalDate from, LocalDate to) {
        boolean filterPresent = isWebElementVisible(5,
                "//*[self::button or self::input][normalize-space(text())='Übernehmen' or normalize-space(@value)='Übernehmen']",
                LocatorType.XPATH, false);
        if (!filterPresent) {
            ScenarioLogManager.getLogger().warn("Date filter panel not present on statistics page.");
            return;
        }

        String fromIso = from.format(DateTimeFormatter.ISO_LOCAL_DATE);
        String toIso = to.format(DateTimeFormatter.ISO_LOCAL_DATE);

        ScenarioLogManager.getLogger().info("Setting statistics date range from " + fromIso + " to " + toIso);
        setDateInputByJs("von", fromIso);
        setDateInputByJs("bis", toIso);

        ScenarioLogManager.getLogger().info("Submitting statistics filter with Übernehmen button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME,
                "//*[self::button or self::input][normalize-space(text())='Übernehmen' or normalize-space(@value)='Übernehmen']",
                LocatorType.XPATH, false);
    }

    private void setDateInputByJs(String fieldName, String isoValue) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        By[] candidates = new By[] { By.name(fieldName), By.id(fieldName) };

        for (By by : candidates) {
            try {
                WebElement element = wait.until(ExpectedConditions.elementToBeClickable(by));
                ((JavascriptExecutor) DRIVER).executeScript(
                        "arguments[0].value = arguments[1];"
                                + "arguments[0].dispatchEvent(new Event('change', {bubbles: true}));",
                        element, isoValue);
                ScenarioLogManager.getLogger()
                        .info("Date input '" + fieldName + "' set to " + isoValue);
                return;
            } catch (Exception ignored) {
                // try next candidate
            }
        }

        ScenarioLogManager.getLogger()
                .warn("Could not locate date input '" + fieldName + "' to set value " + isoValue);
    }

    public boolean checkAvailabilityOfStatisticalInformationForDate(int year, int month) {
        String monthStr = Month.of(month).getDisplayName(TextStyle.FULL_STANDALONE, Locale.GERMAN);
        ScenarioLogManager.getLogger().info("Trying to find statistical information for " + monthStr + " " + year);

        try {
            WebElement element = DRIVER.findElement(By.xpath("//tr[.//a[contains(text(), '" + year + "')]]/td/a[contains(text(), '" + monthStr + "')]"));
            element.click();
            return true;
        } catch (NoSuchElementException e) {
            ScenarioLogManager.getLogger().warn("Statistical information for " + monthStr + " " + year + " not found.");
            return false;
        }
    }

    public void clickOnCurrentMonthName() {
        ScenarioLogManager.getLogger().info("Trying to click on current month link...");
        try {
            WebElement row = DRIVER.findElement(By.xpath("//tr[.//a[contains(text(), '" + LocalDate.now().getYear() + "')]]"));
            List<WebElement> cells = row.findElements(By.tagName("td"));

            boolean monthFound = false;
            for (WebElement cell : cells) {
                if (cell.getText().equals(currentMonth) && !cell.findElements(By.tagName("a")).isEmpty()) {
                    WebElement link = cell.findElement(By.tagName("a"));
                    link.click();
                    monthFound = true;
                    break;
                }
            }

            Assert.assertTrue(monthFound, "Could not find a clickable link for the current month (" + currentMonth + ").");

        } catch (NoSuchElementException e) {
            Assert.fail("No info statistics, could not find the row element for the current year (" + LocalDate.now().getYear() + ").");
        }
    }

    public void checkIfTheStatisticForTheSelectedMonthIsOpen() {
        ScenarioLogManager.getLogger().info("Checking if statistic for the selected month is visible.");
        Assert.assertTrue(
                isWebElementInvisible(DEFAULT_EXPLICIT_WAIT_TIME, "//tr[.//td[contains(text(), 'Bitte wählen Sie einen Zeitraum aus.')]]", LocatorType.XPATH),
                "The text 'Bitte wählen Sie einen Zeitraum aus.' is still visible!");

        // Define a list of strings that must be present in the h2 elements.
        List<String> requiredStrings = Arrays.asList(currentMonth, "Auswertung");

        List<WebElement> h2s = findElementsByLocatorType(DEFAULT_EXPLICIT_WAIT_TIME, "h2.board__heading", LocatorType.CSSSELECTOR);

        boolean allStringsPresent = h2s.stream()
                .allMatch(h2 -> requiredStrings.stream().allMatch(str -> h2.getText().contains(str)));

        Assert.assertTrue(allStringsPresent, "statistic for " + currentMonth + "is not visible!");
    }

    public void clickDownloadButton() {
        ScenarioLogManager.getLogger().info("Trying to click the download button in the customer statistics...");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//a[@title='Download']", LocatorType.XPATH, true),
                "Download button is not visible.");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//a[@title='Download']", LocatorType.XPATH, false);
    }

    public void isStatisticDownloaded(String expectedFileNameRegex) {
        ScenarioLogManager.getLogger().info("Verifying if the file was downloaded...");

        String downloadsDirectory = System.getProperty("user.home") + File.separator + "Downloads";
        Path downloadPath = Paths.get(downloadsDirectory);

        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.withMessage("No file has been downloaded!");

        wait.until(waitDriver -> {
            if (DriverUtil.isLocalExecution()) {
                try (Stream<Path> walk = Files.walk(downloadPath)) {
                    List<Path> result = walk
                            .filter(Files::isRegularFile)
                            .filter(path -> path.getFileName().toString().matches(expectedFileNameRegex))
                            .toList();
                    if (!result.isEmpty()) {
                        ScenarioLogManager.getLogger().info("Matched file(s): " + result.stream().map(Path::toString).collect(Collectors.joining(", ")));
                        return true;
                    }
                } catch (IOException e) {
                    ScenarioLogManager.getLogger().error(e);
                }
                return false;
            } else {
                return !((HasDownloads) waitDriver).getDownloadableFiles()
                        .stream()
                        .filter(string -> string.matches(expectedFileNameRegex))
                        .toList()
                        .isEmpty();
            }
        });
    }
}
