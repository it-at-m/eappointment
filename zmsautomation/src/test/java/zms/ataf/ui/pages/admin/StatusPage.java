package zms.ataf.ui.pages.admin;

import java.time.Duration;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import ataf.web.pages.BasePage;

/**
 * Betriebsstatus. The footer link is on every workstation page.
 * The technical administration block is only rendered for a superuser.
 */
public class StatusPage extends BasePage {

    private static final By STATUS_LINK = By.cssSelector("#page-footer a[title='Betriebsstatus des Systems']");

    public StatusPage(RemoteWebDriver driver) {
        super(driver);
    }

    public void assertStatusLinkVisible() {
        WebElement link = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(STATUS_LINK));
        Assert.assertTrue(link.isDisplayed(), "The status link is not visible in the footer.");
    }

    public void assertTechnicalSectionHidden() {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement heading = wait.until(ExpectedConditions.visibilityOfElementLocated(
                By.xpath("//h1[contains(., 'Betriebsstatus des Systems')]")));
        Assert.assertTrue(heading.isDisplayed(), "The system status page is not open.");
        Assert.assertFalse(
                DRIVER.findElement(By.tagName("body")).getText().contains("Nur für technische Administration sichtbar"),
                "The technical administration section is visible.");
    }

    public void openFromFooter() {
        WebElement link = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.elementToBeClickable(STATUS_LINK));
        link.click();
        new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME))
                .until(ExpectedConditions.visibilityOfElementLocated(By.xpath("//h1[contains(., 'Betriebsstatus des Systems')]")));
    }

    public void assertStatusPageOpen() {
        WebDriverWait wait = new WebDriverWait(DRIVER, Duration.ofSeconds(DEFAULT_EXPLICIT_WAIT_TIME));
        WebElement heading = wait.until(ExpectedConditions.visibilityOfElementLocated(
                By.xpath("//h1[contains(., 'Betriebsstatus des Systems')]")));
        Assert.assertTrue(heading.isDisplayed(), "The system status page is not open.");
        Assert.assertTrue(
                DRIVER.findElement(By.tagName("body")).getText().contains("Nur für technische Administration sichtbar"),
                "The technical administration section is missing.");
    }
}
