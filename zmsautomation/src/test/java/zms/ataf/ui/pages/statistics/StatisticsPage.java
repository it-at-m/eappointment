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

    /** Login and SSO flow aligned with AdminPage (same pattern as zmsadmin). */
    public void clickOnLoginButton() throws Exception {
        ScenarioLogManager.getLogger().info("Trying to click on \"Login\" button...");
        clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME, "//button[@type='submit' and @value='keycloak']", LocatorType.XPATH, false);
        ScenarioLogManager.getLogger().info("SSO-Login page detected!");

        final StringBuilder clearUserName = new StringBuilder();
        final StringBuilder clearPassword = new StringBuilder();
        Exception exception = null;
        try {
            AuthenticationHelper.getUserName().access(clearUserName::append);
            AuthenticationHelper.getUserPassword().access(clearPassword::append);
            WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
            wait.until(ExpectedConditions.presenceOfElementLocated(By.id("username")));
            if ("chrome".equals(
                    TestPropertiesHelper.getPropertyAsString("browser", true, DefaultValues.BROWSER))) {
                wait.until(ExpectedConditions.presenceOfElementLocated(By.id("kc-login")));
                DRIVER.navigate().to(DRIVER.getCurrentUrl().replaceFirst("https://",
                        "https://" + URLEncoder.encode(clearUserName.toString(), StandardCharsets.UTF_8) + ":" + URLEncoder.encode(clearPassword.toString(),
                                StandardCharsets.UTF_8) + "@"));
            }

            ScenarioLogManager.getLogger().info("Trying to enter user name...");
            enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, clearUserName.toString(), "username", LocatorType.ID);

            ScenarioLogManager.getLogger().info("Trying to enter password...");
            enterTextInWebElement(DEFAULT_EXPLICIT_WAIT_TIME, clearPassword.toString(), "password", LocatorType.ID);

            ScenarioLogManager.getLogger().info("Trying to click on \"Login\" button (Keycloak)...");
            WebElement kcLogin = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                    .until(ExpectedConditions.elementToBeClickable(By.id("kc-login")));
            scrollToCenterByVisibleElement(kcLogin);
            ((JavascriptExecutor) DRIVER).executeScript("arguments[0].click();", kcLogin);
            new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                    .until(ExpectedConditions.presenceOfElementLocated(By.name("scope")));
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

    // --- First scope selection (post-login, same as zmsadmin: workstation/Standort) ---

    /** First scope selection: select Standort in the initial dropdown (name=scope), same as zmsadmin. Used after login before overview. */
    public void selectLocation(String location) {
        CONTEXT.set();
        ScenarioLogManager.getLogger().info("Trying to select location \"" + location + "\" (first scope, like zmsadmin)");
        selectDropDownListValueByVisibleText(DEFAULT_EXPLICIT_WAIT_TIME, "scope", LocatorType.NAME, location);
        TestDataHelper.setTestData("location", location);
    }

    /** First scope selection: confirm with "Auswahl bestätigen", same as zmsadmin. Navigates to statistics overview. */
    public void clickOnApplySelectionButton() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Auswahl bestätigen\" button...");
        CONTEXT.set();
        // Same locator as AdminPage; JS click avoids navigation timeout and stale element when stats app is slow
        By submitLocator = By.xpath("//button[@type='submit' and @value='weiter']");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement submit = wait.until(ExpectedConditions.elementToBeClickable(submitLocator));
        scrollToCenterByVisibleElement(submit);
        ((JavascriptExecutor) DRIVER).executeScript("arguments[0].click();", submit);
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.presenceOfElementLocated(By.xpath("//h1[contains(normalize-space(),'Übersicht')]")));
    }

    public void checkIfTheOverviewPageIsOpen() {
        ScenarioLogManager.getLogger().info("Checking if the overview page is visible.");
        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//h1[normalize-space()='Übersicht verfügbarer Statistiken']", LocatorType.XPATH, true),
                "'Overview page is not visible!");
        Assert.assertTrue(isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME, "//div[@data-includeurl='/terminvereinbarung/statistic']", LocatorType.XPATH, true),
                "'Overview page is not visible!");
    }

    /** Use JS click and wait for sub-page to avoid navigation timeout when opening Kundenstatistik. */
    public void clickOnCustomerStatistics() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Kundenstatistik\" button in the sidebar...");
        CONTEXT.set();
        By linkLocator = By.xpath("//a[normalize-space()='Kundenstatistik']");
        WebElement link = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.elementToBeClickable(linkLocator));
        scrollToCenterByVisibleElement(link);
        ((JavascriptExecutor) DRIVER).executeScript("arguments[0].click();", link);
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.presenceOfElementLocated(By.xpath("//h1[contains(normalize-space(),'Kundenstatistik')]")));
    }

    public void checkIfStatisticsPageIsOpen(String pageName) {
        ScenarioLogManager.getLogger().info("Checking if the " + pageName + " statistics page is visible.");

        Assert.assertTrue(
                isWebElementVisible(DEFAULT_EXPLICIT_WAIT_TIME,
                        "//h1[contains(normalize-space(.),'" + pageName + "')]",
                        LocatorType.XPATH, true),
                "'Statistics page heading \"" + pageName + "\" is not visible!");
    }

    /** Use JS click and wait for sub-page to avoid navigation timeout when opening Dienstleistungsstatistik. */
    public void clickOnServiceStatistics() {
        ScenarioLogManager.getLogger().info("Trying to click on \"Dienstleistungsstatistik\" button in the sidebar...");
        CONTEXT.set();
        By linkLocator = By.xpath("//a[normalize-space()='Dienstleistungsstatistik']");
        WebElement link = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.elementToBeClickable(linkLocator));
        scrollToCenterByVisibleElement(link);
        ((JavascriptExecutor) DRIVER).executeScript("arguments[0].click();", link);
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.presenceOfElementLocated(By.xpath("//h1[contains(normalize-space(),'Dienstleistungsstatistik')]")));
    }

    // --- Second scope selection (statistics table/report filter; different UI, after opening Kundenstatistik/Dienstleistungsstatistik) ---

    /**
     * Second scope selection: select Standort in the statistics table filter panel (e.g. scope-select).
     * Used on Kundenstatistik/Dienstleistungsstatistik sub-pages only. Not the same as the first post-login scope.
     */
    public void selectScopeInStatisticsTableFilter(String location) {
        ScenarioLogManager.getLogger().info("Selecting scope in statistics table filter (second scope): \"" + location + "\"");
        boolean filterPresent = isWebElementVisible(5,
                "//*[self::button or self::input][normalize-space(text())='Übernehmen' or normalize-space(@value)='Übernehmen']",
                LocatorType.XPATH, false);
        if (!filterPresent) {
            ScenarioLogManager.getLogger().warn("Statistics table filter panel not present.");
            return;
        }

        if (location == null || location.isEmpty()) {
            ScenarioLogManager.getLogger().warn("No location provided for statistics table filter; skipping.");
            return;
        }

        try {
            WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
            WebElement select = wait.until(
                    ExpectedConditions.visibilityOfElementLocated(By.id("scope-select")));

            List<WebElement> options = select.findElements(By.tagName("option"));
            WebElement target = null;
            for (WebElement option : options) {
                String text = option.getText().trim();
                if (text.equals(location) || text.contains(location)) {
                    target = option;
                    break;
                }
            }

            if (target == null) {
                ScenarioLogManager.getLogger().warn(
                        "Could not find option matching location \"" + location + "\" in scope-select.");
                return;
            }

            scrollToCenterByVisibleElement(target);
            target.click();
            ScenarioLogManager.getLogger().info("Scope \"" + target.getText().trim() + "\" selected in statistics table filter.");
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn(
                    "Failed to select scope in statistics table filter, trying fallback. Cause: " + e.getMessage());
            clickOnWebElement(DEFAULT_EXPLICIT_WAIT_TIME,
                    "//select[@id='scope-select']/option[contains(normalize-space(.),'" + location + "')]",
                    LocatorType.XPATH, false);
        }
    }

    /** Second scope context: set date range in the statistics table filter panel (after opening a statistics sub-page). */
    public void applyDateRangeFilter(LocalDate from, LocalDate to) {
        ScenarioLogManager.getLogger().info("Applying date range in statistics table filter...");
        boolean filterPresent = isWebElementVisible(5,
                "//form[contains(@class,'form--base')][contains(@class,'panel--heavy')]",
                LocatorType.XPATH, false);
        if (!filterPresent) {
            ScenarioLogManager.getLogger().warn("Date filter form not present on statistics page.");
            return;
        }

        String fromIso = from.format(DateTimeFormatter.ISO_LOCAL_DATE);
        String toIso = to.format(DateTimeFormatter.ISO_LOCAL_DATE);

        ScenarioLogManager.getLogger().info("Setting statistics date range from " + fromIso + " to " + toIso);
        setDateInputByJs("from", fromIso);
        setDateInputByJs("to", toIso);

        ScenarioLogManager.getLogger().info("Submitting statistics filter with Übernehmen button via JavaScript click...");
        try {
            WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
            WebElement submitButton = wait.until(ExpectedConditions.elementToBeClickable(
                    By.cssSelector("form.form--base.panel--heavy .reportfilter-actions button[type='submit']")));
            scrollToCenterByVisibleElement(submitButton);
            ((JavascriptExecutor) DRIVER).executeScript("arguments[0].click();", submitButton);
        } catch (Exception e) {
            ScenarioLogManager.getLogger().error("Failed to submit statistics filter form", e);
        }
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
                return !((HasDownloads) waitDriver).getDownloadedFiles()
                        .stream()
                        .map(HasDownloads.DownloadedFile::getName)
                        .filter(string -> string.matches(expectedFileNameRegex))
                        .toList()
                        .isEmpty();
            }
        });
    }
}
