package zms.ataf.ui.pages.admin;

import java.time.Duration;
import java.util.ArrayList;
import java.util.List;

import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.helpers.TestDataHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.web.pages.BasePage;

/**
 * Nutzer*innen. Benutzerverwaltung creates accounts here.
 */
public class UseraccountPage extends BasePage {

    private static final String DEPARTMENT_REQUIRED = "Bitte wählen Sie (mindestens) eine Behörde aus.";

    private static final String INPUT_ERROR =
            "Bei der Eingabe der Daten scheint Ihnen ein Fehler unterlaufen zu sein. Bitte prüfen Sie die Daten.";

    private static final String MISSING_RIGHTS =
            "Um diesen Benutzer zu bearbeiten fehlen Ihnen die notwendigen Rechte.";

    public UseraccountPage(RemoteWebDriver driver) {
        super(driver);
    }

    public void openNewUser() {
        String current = DRIVER.getCurrentUrl();
        Assert.assertTrue(
                current != null && current.contains("/users"),
                "Expected the user administration page, got: " + current);
        String add = current.replaceFirst("(/users)(?:/)?(?:\\?.*)?$", "/users/add/");
        ScenarioLogManager.getLogger().info("Opening the form for a new user at {}", add);
        DRIVER.navigate().to(add);
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector("select[name='departments[][id]']")));
    }

    public void preferLocalLogin() {
        List<WebElement> provider = DRIVER.findElements(By.id("useOidcProvider"));
        if (provider.isEmpty()) {
            return;
        }
        ScenarioLogManager.getLogger().info("Preferring a local login for the new user...");
        new Select(provider.get(0)).selectByVisibleText("Nein, lokale Anmeldung bevorzugen");
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME)).until(driver -> {
            List<WebElement> fields = driver.findElements(By.cssSelector("input[name='changePassword[]']"));
            return fields.size() == 2
                    && fields.stream().allMatch(input -> input.getAttribute("readonly") == null);
        });
    }

    public void enterNewUser(String username, String password) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement name = wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector("input[name='id']")));
        name.clear();
        name.sendKeys(username);
        List<WebElement> passwords = DRIVER.findElements(By.cssSelector("input[name='changePassword[]']"));
        Assert.assertEquals(passwords.size(), 2, "The new-user form does not show password and confirmation.");
        for (WebElement field : passwords) {
            field.clear();
            field.sendKeys(password);
        }
    }

    /** Wall time of the last new-user form submit (initial save or validation retry). */
    private long lastNewUserSubmission;

    public void saveNewUser() {
        ScenarioLogManager.getLogger().info("Saving the new user without a department...");
        waitForAdminLoaderGone();
        // Firefox often reports a successful native click without submitting this form.
        // requestSubmit keeps the save button value; form.submit() is the last resort.
        lastNewUserSubmission = System.currentTimeMillis();
        submitNewUserForm();
        waitForAdminLoaderGone();
    }

    public void assertDepartmentIsRequired() {
        By departmentSelect = By.cssSelector("select[name='departments[][id]']");
        By departmentFieldError = By.cssSelector(".form-group.has-error .message--error");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.ignoring(StaleElementReferenceException.class);
        // Seed from saveNewUser so the first poll cannot immediately double-submit.
        final long[] lastResubmit = { lastNewUserSubmission };
        wait.until(driver -> {
            String url = driver.getCurrentUrl();
            if (url != null && url.contains("useraccount_added")) {
                return true;
            }
            if (isAdminLoaderVisible(driver)) {
                return false;
            }
            String bodyText = "";
            try {
                bodyText = driver.findElement(By.tagName("body")).getText();
            } catch (StaleElementReferenceException ignored) {
                return false;
            }
            if (bodyText.contains(DEPARTMENT_REQUIRED) || bodyText.contains(INPUT_ERROR)) {
                return true;
            }
            if (!driver.findElements(departmentFieldError).isEmpty()) {
                return true;
            }
            List<WebElement> selects = driver.findElements(departmentSelect);
            if (selects.isEmpty()) {
                return false;
            }
            try {
                WebElement group = selects.get(0).findElement(By.xpath("ancestor::*[contains(@class,'form-group')][1]"));
                String groupClass = group.getAttribute("class");
                if (groupClass != null && groupClass.contains("has-error")) {
                    return true;
                }
            } catch (org.openqa.selenium.NoSuchElementException | StaleElementReferenceException ignored) {
                return false;
            }
            // Still on a clean form: Firefox may have dropped the first click; submit once more.
            long now = System.currentTimeMillis();
            if (now - lastResubmit[0] > 5000L) {
                lastResubmit[0] = now;
                lastNewUserSubmission = now;
                ScenarioLogManager.getLogger()
                        .warn("Department validation still missing after save; submitting the new-user form again.");
                submitNewUserForm();
            }
            return false;
        });
        Assert.assertFalse(DRIVER.getCurrentUrl().contains("useraccount_added"),
                "The account was created without a department.");

        String page = DRIVER.findElement(By.tagName("body")).getText();
        Assert.assertTrue(page.contains(INPUT_ERROR), "The standard input error is missing.");
        Assert.assertTrue(page.contains(DEPARTMENT_REQUIRED), "The department error is missing.");
        Assert.assertFalse(page.contains(MISSING_RIGHTS), "The old missing-rights error is shown: " + page);

        WebElement select = DRIVER.findElement(By.cssSelector("select[name='departments[][id]']"));
        String signedInDepartment = TestDataHelper.getTestData("signed_in_department");
        Assert.assertTrue(
                signedInDepartment != null && signedInDepartment.matches("[1-9]\\d*"),
                "The signed-in user admin has no department number: " + signedInDepartment);
        List<String> listed = new ArrayList<>();
        for (WebElement option : select.findElements(By.tagName("option"))) {
            String value = option.getAttribute("value");
            if (value != null && value.matches("[1-9]\\d*")) {
                listed.add(value);
            }
        }
        Assert.assertEquals(listed, List.of(signedInDepartment),
                "Behörde does not list the signed-in user's department.");
        WebElement group = select.findElement(By.xpath("ancestor::*[contains(@class,'form-group')][1]"));
        String groupClass = group.getAttribute("class");
        Assert.assertTrue(groupClass.contains("has-error"), "Behörde is not marked invalid: " + groupClass);
        String fieldError = group.findElement(By.cssSelector(".message--error")).getText().replace('\u00a0', ' ');
        Assert.assertTrue(fieldError.contains("Fehler: " + DEPARTMENT_REQUIRED),
                "The department error is not shown under Behörde: " + fieldError);

        String color = select.getCssValue("border-left-color");
        String width = select.getCssValue("border-left-width");
        Assert.assertTrue(color != null && color.contains("213, 47, 46"),
                "Behörde is not outlined in red: " + color);
        Assert.assertEquals(width, "5px", "Behörde does not have the error border.");
    }

    private void submitNewUserForm() {
        WebElement save = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.presenceOfElementLocated(
                        By.xpath("//button[normalize-space(.)='Nutzer anlegen']")));
        ((JavascriptExecutor) DRIVER).executeScript(
                "var button = arguments[0];"
                        + "button.scrollIntoView({block:'center'});"
                        + "var form = button.closest('form');"
                        + "if (form && typeof form.requestSubmit === 'function') {"
                        + "  form.requestSubmit(button);"
                        + "} else if (form) {"
                        + "  form.submit();"
                        + "} else {"
                        + "  button.click();"
                        + "}",
                save);
    }

    private static boolean isAdminLoaderVisible(org.openqa.selenium.WebDriver driver) {
        for (WebElement loader : driver.findElements(By.cssSelector("div.loader"))) {
            try {
                if (loader.isDisplayed()) {
                    return true;
                }
            } catch (StaleElementReferenceException ignored) {
                // page re-rendered
            }
        }
        return false;
    }

    private void waitForAdminLoaderGone() {
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .ignoring(StaleElementReferenceException.class)
                .until(driver -> !isAdminLoaderVisible(driver));
    }
}
