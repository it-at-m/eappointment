package zms.ataf.ui.pages.admin;

import java.time.Duration;
import java.util.ArrayList;
import java.util.List;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

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

    /** ZMSKVR-533 / ZMSKVR-661 — matches useraccount.json pattern message (de_DE). */
    static final String USERNAME_CHAR_RESTRICTION =
            "Der Nutzername darf nur Kleinbuchstaben, Zahlen, Punkte, Unterstriche und Bindestriche enthalten";

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
        pendingNewUserPassword = password;
        enterNewUserUsername(username);
        fillChangePasswordFields(password);
    }

    /** ZMSKVR-533 / ZMSKVR-661: with default Keycloak provider, passwords are filled by the form JS. */
    public void enterNewUserUsername(String username) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement name = wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector("input[name='id']")));
        name.clear();
        name.sendKeys(username);
        waitUntilOidcPasswordsFilled();
    }

    public void assertDefaultKeycloakProviderSelected() {
        List<WebElement> provider = DRIVER.findElements(By.id("useOidcProvider"));
        Assert.assertFalse(provider.isEmpty(), "OIDC provider select is missing on the new-user form.");
        String selected = new Select(provider.get(0)).getFirstSelectedOption().getText();
        Assert.assertTrue(
                selected != null && selected.toLowerCase().contains("keycloak"),
                "Expected default Keycloak provider, was: " + selected);
        waitUntilOidcPasswordsFilled();
    }

    public void selectSignedInDepartment() {
        String departmentId = TestDataHelper.getTestData("signed_in_department");
        Assert.assertTrue(
                departmentId != null && departmentId.matches("[1-9]\\d*"),
                "The signed-in user admin has no department number: " + departmentId);
        WebElement select = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(
                        By.cssSelector("select[name='departments[][id]']")));
        new Select(select).selectByValue(departmentId);
        ScenarioLogManager.getLogger().info("Selected Behörde id={}", departmentId);
    }

    public void assertUsernameCharacterRestriction() {
        By usernameInput = By.cssSelector("input[name='id']");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.ignoring(StaleElementReferenceException.class);
        final long[] lastResubmit = { lastNewUserSubmission };
        wait.until(driver -> {
            String url = driver.getCurrentUrl();
            if (url != null && url.contains("useraccount_added")) {
                return true;
            }
            if (isAdminLoaderVisible(driver)) {
                return false;
            }
            if (usernameFieldShowsRestriction(driver)) {
                return true;
            }
            long now = System.currentTimeMillis();
            if (now - lastResubmit[0] > 5000L) {
                lastResubmit[0] = now;
                lastNewUserSubmission = now;
                ScenarioLogManager.getLogger()
                        .warn("Username restriction still missing after save; submitting the new-user form again.");
                submitNewUserForm();
            }
            return false;
        });
        Assert.assertFalse(DRIVER.getCurrentUrl().contains("useraccount_added"),
                "The account was created with an invalid username.");
        WebElement group = DRIVER.findElement(usernameInput)
                .findElement(By.xpath("ancestor::*[contains(@class,'form-group')][1]"));
        String groupClass = group.getAttribute("class");
        Assert.assertTrue(groupClass != null && groupClass.contains("has-error"),
                "Nutzername is not marked invalid: " + groupClass);
        String fieldError = group.findElement(By.cssSelector(".message--error")).getText().replace('\u00a0', ' ');
        Assert.assertTrue(
                fieldError.contains(USERNAME_CHAR_RESTRICTION),
                "Username character restriction is not shown under Nutzername: " + fieldError);
    }

    private void waitUntilOidcPasswordsFilled() {
        List<WebElement> provider = DRIVER.findElements(By.id("useOidcProvider"));
        if (provider.isEmpty()) {
            return;
        }
        String value = provider.get(0).getAttribute("value");
        if (value == null || value.isBlank()) {
            return;
        }
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.ignoring(StaleElementReferenceException.class);
        final long[] lastNudge = {0L};
        wait.until(driver -> {
            List<WebElement> fields = driver.findElements(By.cssSelector("input[name='changePassword[]']"));
            if (fields.size() == 2
                    && fields.stream().allMatch(input -> passwordFieldValue(input).length() >= 6)) {
                return true;
            }
            // The add form fills these on jQuery ready. Under parallel load that handler can
            // miss the first paint; a change event runs the same fill again.
            long now = System.currentTimeMillis();
            if (now - lastNudge[0] > 2000L) {
                lastNudge[0] = now;
                ScenarioLogManager.getLogger()
                        .warn("Keycloak password fields are still empty; re-firing the provider change.");
                ((JavascriptExecutor) driver).executeScript(
                        "var sel=document.getElementById('useOidcProvider');"
                                + "if(sel){sel.dispatchEvent(new Event('change',{bubbles:true}));}");
            }
            return false;
        });
    }

    private static boolean usernameFieldShowsRestriction(org.openqa.selenium.WebDriver driver) {
        List<WebElement> inputs = driver.findElements(By.cssSelector("input[name='id']"));
        if (inputs.isEmpty()) {
            return false;
        }
        try {
            WebElement group = inputs.get(0).findElement(By.xpath("ancestor::*[contains(@class,'form-group')][1]"));
            String groupClass = group.getAttribute("class");
            if (groupClass == null || !groupClass.contains("has-error")) {
                return false;
            }
            String fieldError = group.findElement(By.cssSelector(".message--error")).getText();
            return fieldError != null && fieldError.contains(USERNAME_CHAR_RESTRICTION);
        } catch (org.openqa.selenium.NoSuchElementException | StaleElementReferenceException ignored) {
            return false;
        }
    }

    /** Wall time of the last new-user form submit (initial save or validation retry). */
    private long lastNewUserSubmission;

    /** Alternates requestSubmit (Chromium) with forced form.submit (Firefox) on validation retries. */
    private int newUserSubmitAttempt;

    /** Last password typed into changePassword[] — re-applied before each submit (Firefox drops sendKeys). */
    private String pendingNewUserPassword;

    public void saveNewUser() {
        ScenarioLogManager.getLogger().info("Saving the new user without a department...");
        waitForAdminLoaderGone();
        ensureNewUserPasswordsFilled();
        ensureNoDepartmentSelected();
        // Firefox often reports a successful native click without submitting this form.
        // requestSubmit keeps the save button value; form.submit() is the last resort.
        // Start with forced POST so Gecko does not sit on a no-op requestSubmit for 2.5s.
        lastNewUserSubmission = System.currentTimeMillis();
        newUserSubmitAttempt = 1;
        submitNewUserForm();
        waitForAdminLoaderGone();
    }

    /** Multi-select Behörde must stay empty for the missing-department scenario. */
    private void ensureNoDepartmentSelected() {
        List<WebElement> selects = DRIVER.findElements(By.cssSelector("select[name='departments[][id]']"));
        if (selects.isEmpty()) {
            return;
        }
        try {
            Select select = new Select(selects.get(0));
            if (select.isMultiple()) {
                select.deselectAll();
            } else {
                select.selectByValue("");
            }
        } catch (Exception e) {
            ScenarioLogManager.getLogger()
                    .warn("Could not clear Behörde multi-select before save: {}", e.toString());
        }
    }

    /**
     * Gecko often leaves changePassword[] empty after clear/sendKeys (or after a validation
     * re-render). Empty passwords yield "Passwort … mindestens 6 Zeichen" instead of the
     * department error this scenario asserts.
     */
    private void ensureNewUserPasswordsFilled() {
        if (pendingNewUserPassword == null || pendingNewUserPassword.isBlank()) {
            return;
        }
        List<WebElement> passwords = DRIVER.findElements(By.cssSelector("input[name='changePassword[]']"));
        if (passwords.size() < 2) {
            return;
        }
        boolean needsFill = passwords.stream().anyMatch(field -> passwordFieldValue(field).length() < 6);
        if (!needsFill) {
            return;
        }
        ScenarioLogManager.getLogger()
                .warn("New-user password fields empty or short; re-applying password before submit.");
        fillChangePasswordFields(pendingNewUserPassword);
    }

    private void fillChangePasswordFields(String password) {
        List<WebElement> passwords = DRIVER.findElements(By.cssSelector("input[name='changePassword[]']"));
        Assert.assertEquals(passwords.size(), 2, "The new-user form does not show password and confirmation.");
        JavascriptExecutor js = (JavascriptExecutor) DRIVER;
        for (WebElement field : passwords) {
            // Prefer real typing: Firefox password managers / readonly toggles can ignore .value=.
            try {
                field.click();
                field.clear();
                field.sendKeys(password);
            } catch (Exception typed) {
                ScenarioLogManager.getLogger()
                        .warn("sendKeys on changePassword[] failed ({}); using JS value assign.", typed.toString());
            }
            if (passwordFieldValue(field).equals(password)) {
                continue;
            }
            js.executeScript(
                    "var input = arguments[0]; var value = arguments[1];"
                            + "input.removeAttribute('readonly');"
                            + "input.readOnly = false;"
                            + "input.focus();"
                            + "input.value = value;"
                            + "input.dispatchEvent(new Event('input', {bubbles:true}));"
                            + "input.dispatchEvent(new Event('change', {bubbles:true}));",
                    field,
                    password);
        }
        for (WebElement field : passwords) {
            // Gecko getAttribute('value') stays at the HTML default (""); read the live property.
            String value = passwordFieldValue(field);
            Assert.assertTrue(
                    value.equals(password),
                    "Password field did not keep the typed value (got length " + value.length() + ").");
        }
    }

    /** Live input.value — Firefox getAttribute("value") on type=password stays empty. */
    private String passwordFieldValue(WebElement field) {
        try {
            Object live = ((JavascriptExecutor) DRIVER).executeScript("return arguments[0].value || '';", field);
            return live == null ? "" : String.valueOf(live);
        } catch (org.openqa.selenium.JavascriptException ignored) {
            String attr = field.getAttribute("value");
            return attr == null ? "" : attr;
        }
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
            // Require the department message itself — INPUT_ERROR alone can appear a tick earlier
            // on Firefox and made the wait succeed before Behörde text was painted.
            if (bodyText.contains(DEPARTMENT_REQUIRED)) {
                return true;
            }
            boolean departmentMarkedInvalid = false;
            if (!driver.findElements(departmentFieldError).isEmpty()) {
                departmentMarkedInvalid = true;
            }
            List<WebElement> selects = driver.findElements(departmentSelect);
            if (selects.isEmpty()) {
                return false;
            }
            try {
                WebElement group = selects.get(0).findElement(By.xpath("ancestor::*[contains(@class,'form-group')][1]"));
                String groupClass = group.getAttribute("class");
                if (groupClass != null && groupClass.contains("has-error")) {
                    departmentMarkedInvalid = true;
                }
            } catch (org.openqa.selenium.NoSuchElementException | StaleElementReferenceException ignored) {
                return false;
            }
            if (departmentMarkedInvalid && bodyText.contains(DEPARTMENT_REQUIRED)) {
                return true;
            }
            // Password-only validation means Firefox dropped changePassword[]; refill and POST again.
            long now = System.currentTimeMillis();
            if (now - lastResubmit[0] > 2500L) {
                lastResubmit[0] = now;
                lastNewUserSubmission = now;
                ScenarioLogManager.getLogger()
                        .warn("Department validation still missing after save; submitting the new-user form again.");
                ensureNewUserPasswordsFilled();
                ensureNoDepartmentSelected();
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

        // Chrome reports rgb(213, 47, 46); Firefox often rgb(210, 51, 51). Gecko can briefly
        // still report the default select border (rgb(143, 143, 157)) before the has-error
        // left accent paints — wait, and prefer getComputedStyle.
        WebDriverWait borderWait = new WebDriverWait(DRIVER, Duration.ofSeconds(8));
        borderWait.ignoring(StaleElementReferenceException.class);
        String[] painted = new String[2];
        try {
            borderWait.until(driver -> {
                WebElement field = driver.findElement(By.cssSelector("select[name='departments[][id]']"));
                painted[0] = borderLeftColor(field);
                painted[1] = field.getCssValue("border-left-width");
                return isErrorRedBorder(painted[0]) && "5px".equals(painted[1]);
            });
        } catch (org.openqa.selenium.TimeoutException timedOut) {
            WebElement field = DRIVER.findElement(By.cssSelector("select[name='departments[][id]']"));
            painted[0] = borderLeftColor(field);
            painted[1] = field.getCssValue("border-left-width");
            ScenarioLogManager.getLogger().warn(
                    "Behörde red border not stable after wait (color={}, width={}); has-error + message already asserted.",
                    painted[0],
                    painted[1]);
        }
        Assert.assertTrue(
                isErrorRedBorder(painted[0]) && "5px".equals(painted[1]),
                "Behörde is not outlined in red: color=" + painted[0] + " width=" + painted[1]);
    }

    private String borderLeftColor(WebElement field) {
        try {
            Object computed = ((JavascriptExecutor) DRIVER).executeScript(
                    "var s=window.getComputedStyle(arguments[0]);"
                            + "return s.borderLeftColor||s.borderColor||'';",
                    field);
            if (computed != null && !String.valueOf(computed).isBlank()) {
                return String.valueOf(computed);
            }
        } catch (org.openqa.selenium.JavascriptException ignored) {
            // fall through
        }
        return field.getCssValue("border-left-color");
    }

    private static final Pattern CSS_RGB = Pattern.compile("rgba?\\(\\s*(\\d+)\\s*,\\s*(\\d+)\\s*,\\s*(\\d+)");

    /** True for the admin has-error left border across Chromium and Gecko computed styles. */
    static boolean isErrorRedBorder(String color) {
        if (color == null || color.isBlank()) {
            return false;
        }
        Matcher match = CSS_RGB.matcher(color);
        if (!match.find()) {
            return false;
        }
        int red = Integer.parseInt(match.group(1));
        int green = Integer.parseInt(match.group(2));
        int blue = Integer.parseInt(match.group(3));
        return red >= 180 && green <= 90 && blue <= 90 && red > green + 80 && red > blue + 80;
    }

    private void submitNewUserForm() {
        By saveButton = By.xpath("//button[normalize-space(.)='Nutzer anlegen']");
        WebElement save = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.presenceOfElementLocated(saveButton));
        // Do not click then requestSubmit on the same element — a successful post re-renders
        // the form and the second call hits StaleElementReference (broke chrome/edge).
        // Chromium: requestSubmit(button). Firefox often no-ops click/requestSubmit; force a
        // real navigation POST with a hidden save field (includes name/value for PHP).
        boolean forceNavigationPost = (newUserSubmitAttempt++ % 2) == 1;
        try {
            if (forceNavigationPost) {
                ScenarioLogManager.getLogger().info("zmsadmin: Nutzer anlegen via forced form.submit POST");
                ((JavascriptExecutor) DRIVER).executeScript(
                        "var button = arguments[0];"
                                + "var form = button.form || button.closest('form');"
                                + "if (!form) { button.click(); return; }"
                                + "var input = document.createElement('input');"
                                + "input.type = 'hidden';"
                                + "input.name = button.getAttribute('name') || 'save';"
                                + "input.value = button.getAttribute('value') || 'save';"
                                + "form.appendChild(input);"
                                + "form.submit();",
                        save);
                return;
            }
            ((JavascriptExecutor) DRIVER).executeScript(
                    "var button = arguments[0];"
                            + "button.scrollIntoView({block:'center'});"
                            + "var form = button.form || button.closest('form');"
                            + "if (form && typeof form.requestSubmit === 'function') {"
                            + "  form.requestSubmit(button);"
                            + "} else if (form) {"
                            + "  form.submit();"
                            + "} else {"
                            + "  button.click();"
                            + "}",
                    save);
            ScenarioLogManager.getLogger().info("zmsadmin: Nutzer anlegen via requestSubmit");
        } catch (StaleElementReferenceException stale) {
            // Submit already navigated / re-rendered — that is success for this attempt.
            ScenarioLogManager.getLogger()
                    .info("zmsadmin: Nutzer anlegen stale after submit (page already moved)");
        }
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
