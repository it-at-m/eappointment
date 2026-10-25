package zms.ataf.ui.pages.admin;

import java.time.Duration;

import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.web.pages.BasePage;

/**
 * Footer link Änderungshistorie and the configurable release notes address.
 */
public class ChangelogPage extends BasePage {

    public static final String DEFAULT_RELEASE_NOTES = "https://it-at-m.github.io/eappointment/overview/changelog.html";

    private static final String INTRO_PREFIX =
            "Aktuelle Release Notes und eine Beschreibung der letzten Änderungen finden Sie in unserem";

    private static final By HISTORY_LINK = By.cssSelector(
            "#page-footer .page-version a[title='Änderungshistorie anzeigen']");
    private static final By VERSION = By.cssSelector("#page-footer .page-version span");
    private static final By RELEASE_NOTES_VALUE = By.xpath(
            "//h2[normalize-space()='changelog']/following-sibling::ul[1]"
                    + "//li[.//strong[contains(.,'urlReleaseNotes')]]"
                    + "//div[contains(@class,'eight-twelfths')]");
    private static final By RELEASE_NOTES_EDIT = By.cssSelector(
            "a.edit[data-key='changelog'][data-property='urlReleaseNotes']");

    public ChangelogPage(RemoteWebDriver driver) {
        super(driver);
    }

    public void assertVersionIsNotALink() {
        WebElement version = waitForPage().until(ExpectedConditions.visibilityOfElementLocated(VERSION));
        Assert.assertTrue(version.getText().trim().startsWith("Version"),
                "The footer version is missing.");
        boolean versionIsLinked = DRIVER.findElements(By.cssSelector("#page-footer .page-version a")).stream()
                .anyMatch(link -> link.getText().trim().startsWith("Version"));
        Assert.assertFalse(versionIsLinked, "The footer version is still a link.");
    }

    public void assertHistoryLink() {
        WebElement link = waitForPage().until(ExpectedConditions.visibilityOfElementLocated(HISTORY_LINK));
        Assert.assertEquals(link.getText().trim(), "Änderungshistorie");
        String href = link.getAttribute("href");
        Assert.assertTrue(href != null && href.contains("changelog"),
                "Änderungshistorie does not open the changelog.");
    }

    public void openSystemConfiguration() {
        WebElement history = waitForPage().until(ExpectedConditions.presenceOfElementLocated(HISTORY_LINK));
        String href = history.getAttribute("href");
        Assert.assertTrue(href != null && href.contains("changelog"),
                "Could not derive the system configuration address.");
        DRIVER.navigate().to(href.replace("changelog", "config"));
        waitForPage().until(ExpectedConditions.visibilityOfElementLocated(
                By.xpath("//h1[contains(., 'Systemkonfiguration')]")));
    }

    public void assertReleaseNotesLink(String url) {
        WebElement value = waitForPage().until(ExpectedConditions.visibilityOfElementLocated(RELEASE_NOTES_VALUE));
        Assert.assertEquals(value.getText().trim(), url, "The release notes link has the wrong value.");
    }

    public void setReleaseNotesLink(String url) {
        WebElement edit = waitForPage().until(ExpectedConditions.presenceOfElementLocated(RELEASE_NOTES_EDIT));
        ((JavascriptExecutor) DRIVER).executeScript("arguments[0].scrollIntoView({block:'center'});", edit);
        waitForPage().until(ExpectedConditions.elementToBeClickable(edit)).click();
        WebElement textarea = waitForPage().until(ExpectedConditions.visibilityOfElementLocated(
                By.cssSelector("div.lightbox__content textarea[name='value']")));
        textarea.clear();
        textarea.sendKeys(url);
        if (!url.equals(textarea.getAttribute("value"))) {
            ((JavascriptExecutor) DRIVER).executeScript("arguments[0].value = arguments[1];", textarea, url);
        }
        waitForPage().until(ExpectedConditions.elementToBeClickable(
                By.xpath("//div[contains(@class,'lightbox__content')]//button[normalize-space()='Speichern']")))
                .click();
        waitForPage().until(ExpectedConditions.urlContains("success=config_saved"));
        assertReleaseNotesLink(url);
    }

    public void openChangelog() {
        WebElement link = waitForPage().until(ExpectedConditions.elementToBeClickable(HISTORY_LINK));
        link.click();
        waitForPage().until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector("p.changelog-intro")));
    }

    public void assertIntroductionLinksTo(String url) {
        WebElement paragraph = waitForPage().until(ExpectedConditions.visibilityOfElementLocated(
                By.cssSelector("p.changelog-intro")));
        String text = paragraph.getText().replace('\u00a0', ' ').trim();
        Assert.assertTrue(text.startsWith(INTRO_PREFIX), "The changelog introduction does not match.");
        WebElement link = paragraph.findElement(By.tagName("a"));
        Assert.assertEquals(link.getAttribute("href"), url,
                "The changelog introduction points at the wrong address.");
    }

    public void assertReleasesBeforeMarch2023AreGone() {
        WebElement list = waitForPage().until(ExpectedConditions.presenceOfElementLocated(
                By.cssSelector("div.complete-list")));
        String text = list.getText();
        Assert.assertTrue(text.contains("Release Muc"), "The changelog list is missing.");
        Assert.assertFalse(text.contains("INT_MS_"), "A release from before 15 March 2023 is still listed.");
        Assert.assertFalse(text.contains("2.24."), "A release numbered 2.24 is still listed.");
    }

    private WebDriverWait waitForPage() {
        return new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
    }
}
