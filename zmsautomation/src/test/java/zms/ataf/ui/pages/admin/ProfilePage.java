package zms.ataf.ui.pages.admin;

import java.time.Duration;
import java.util.ArrayList;
import java.util.HashSet;
import java.util.List;
import java.util.Set;
import java.util.TreeSet;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.pages.BasePage;

/**
 * Mein Profil. The header username opens it. Statistics opens the same page in a new tab.
 */
public class ProfilePage extends BasePage {

    private static final List<String> HEADINGS = List.of("Benutzername", "Rolle", "Berechtigungen");

    private static final List<String> HIDDEN = List.of(
            "E-Mail-Adresse",
            "Anmeldedaten ändern",
            "Zugeordnete Einheiten",
            "Passwort");

    public ProfilePage(RemoteWebDriver driver) {
        super(driver);
    }

    public void openFromHeader() {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement link = wait.until(ExpectedConditions.elementToBeClickable(By.cssSelector(".user-name a")));
        Set<String> before = new HashSet<>(DRIVER.getWindowHandles());
        ScenarioLogManager.getLogger().info("Opening Mein Profil from the header username.");
        link.click();
        wait.until(driver -> driver.getWindowHandles().size() > before.size()
                || driver.getCurrentUrl().contains("/profile/"));
        Set<String> opened = new HashSet<>(DRIVER.getWindowHandles());
        opened.removeAll(before);
        if (!opened.isEmpty()) {
            DRIVER.switchTo().window(opened.iterator().next());
        }
        wait.until(ExpectedConditions.textToBePresentInElementLocated(By.cssSelector("h1"), "Mein Profil"));
    }

    public void assertOnlyLdapRoleAndPermissions(String loginName, String role, Set<String> permissions) {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(".profile-view")));
        WebElement profile = DRIVER.findElement(By.cssSelector(".profile-view"));
        String text = profile.getText();

        List<String> headings = new ArrayList<>();
        for (WebElement heading : profile.findElements(By.cssSelector("h3"))) {
            headings.add(heading.getText().trim());
        }
        Assert.assertEquals(headings, HEADINGS, "Mein Profil should show only Benutzername, Rolle and Berechtigungen.");

        Assert.assertEquals(textAfter(profile, "Benutzername"), loginName.replace("@keycloak", ""),
                "The LDAP name should be the signed-in user without @keycloak.");
        Assert.assertFalse(text.contains("@keycloak"), "Mein Profil should not show @keycloak.");
        Assert.assertEquals(textAfter(profile, "Rolle"), role, "The role should be the German role of this account.");

        Set<String> shown = new TreeSet<>();
        for (WebElement item : profile.findElements(By.cssSelector("ul li"))) {
            String permission = item.getText().trim();
            if (!permission.isEmpty()) {
                shown.add(permission);
            }
        }
        Assert.assertEquals(shown, new TreeSet<>(permissions),
                "The permissions should be the German permissions stored for this role.");

        for (String hidden : HIDDEN) {
            Assert.assertFalse(text.contains(hidden), "Mein Profil should not show \"" + hidden + "\".");
        }
    }

    private static String textAfter(WebElement profile, String heading) {
        WebElement value = profile.findElement(By.xpath(
                ".//h3[normalize-space()='" + heading + "']/following-sibling::p[1]"));
        return value.getText().trim();
    }
}
