package zms.ataf.ui.pages.admin;

import java.time.Duration;
import java.util.List;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

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
        ScenarioLogManager.getLogger().info("Opening the form for a new user...");
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement link = wait.until(ExpectedConditions.elementToBeClickable(
                By.xpath("//a[contains(., 'neue*r Nutzer*in')]")));
        link.click();
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector("select[name='departments[][id]']")));
    }

    public void preferLocalLogin() {
        List<WebElement> provider = DRIVER.findElements(By.id("useOidcProvider"));
        if (provider.isEmpty()) {
            return;
        }
        ScenarioLogManager.getLogger().info("Preferring a local login for the new user...");
        new Select(provider.get(0)).selectByVisibleText("Nein, lokale Anmeldung bevorzugen");
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME)).until(driver -> driver
                .findElements(By.cssSelector("input[type='password']"))
                .stream()
                .allMatch(input -> input.getAttribute("readonly") == null));
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

    public void saveNewUser() {
        ScenarioLogManager.getLogger().info("Saving the new user without a department...");
        WebElement save = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.elementToBeClickable(By.xpath("//button[normalize-space(.)='Nutzer anlegen']")));
        save.click();
    }

    public void assertDepartmentIsRequired() {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.until(ExpectedConditions.visibilityOfElementLocated(
                By.xpath("//*[contains(., '" + DEPARTMENT_REQUIRED + "')]")));
        String page = DRIVER.findElement(By.tagName("body")).getText();
        Assert.assertTrue(page.contains(INPUT_ERROR), "The standard input error is missing.");
        Assert.assertTrue(page.contains(DEPARTMENT_REQUIRED), "The department error is missing.");
        Assert.assertFalse(page.contains(MISSING_RIGHTS), "The old missing-rights error is shown: " + page);
        Assert.assertFalse(DRIVER.getCurrentUrl().contains("useraccount_added"),
                "The account was created without a department.");

        WebElement select = DRIVER.findElement(By.cssSelector("select[name='departments[][id]']"));
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
}
