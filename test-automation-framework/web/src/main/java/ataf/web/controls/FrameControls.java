package ataf.web.controls;

import ataf.core.assertions.CustomAssertions;
import ataf.core.logging.ScenarioLogManager;
import ataf.web.model.Frame;
import ataf.web.pages.BasePage;
import org.openqa.selenium.ElementNotInteractableException;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.NoSuchFrameException;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.WebDriverException;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedCondition;
import org.openqa.selenium.support.ui.WebDriverWait;

import java.time.Duration;
import java.time.temporal.ChronoUnit;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;

/**
 * Provides controls for managing frame navigation within a web page in the context of Selenium
 * WebDriver. This class maintains a map of the current frame for
 * each thread and offers methods to switch between frames.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class FrameControls {

    /**
     * Logger instance for logging events and errors related to frame controls.
     */
    /**
     * Map that associates the current thread ID with its corresponding frame.
     */
    private static final Map<Long, Frame> CURRENT_FRAME_MAP = new ConcurrentHashMap<>();

    /**
     * Constant representing the default content of the web page (i.e., no frame).
     */
    public static final Frame DEFAULT_CONTENT = new Frame("default");

    /**
     * Returns the current frame associated with the calling thread.
     *
     * @return the current {@link Frame} associated with the current thread, or {@code null} if no frame
     *         is set.
     */
    public static Frame getCurrentFrame() {
        return CURRENT_FRAME_MAP.get(Thread.currentThread().getId());
    }

    /**
     * Sets the current frame for the calling thread.
     *
     * @param currentFrame the {@link Frame} to be set as the current frame.
     */
    public static void setCurrentFrame(Frame currentFrame) {
        CURRENT_FRAME_MAP.put(Thread.currentThread().getId(), currentFrame);
    }

    /**
     * Sets the current frame for the calling thread to an unknown state.
     */
    public static void setCurrentFrameUnknown() {
        CURRENT_FRAME_MAP.put(Thread.currentThread().getId(), new Frame("unknown"));
    }

    /**
     * Switches the WebDriver context to the default content (i.e., out of any frames) if the current
     * frame is not the default content.
     *
     * @param driver the {@link RemoteWebDriver} used to perform the frame switch.
     */
    public static void switchToDefaultContent(RemoteWebDriver driver) {
        if (!getCurrentFrame().equals(DEFAULT_CONTENT)) {
            driver.switchTo().defaultContent();
            setCurrentFrame(DEFAULT_CONTENT);
            try {
                Thread.sleep(500L);
            } catch (InterruptedException e) {
                ScenarioLogManager.getLogger().error("Wait after switch to default content was interrupted!", e);
            }
        }
    }

    /**
     * Switches the WebDriver context to the parent frame of the current frame. If there is no parent
     * frame, the current frame is set to an unknown state.
     *
     * @param driver the {@link RemoteWebDriver} used to perform the frame switch.
     */
    public static void switchToParentFrame(RemoteWebDriver driver) {
        driver.switchTo().parentFrame();
        Frame parentFrame = getCurrentFrame().PARENT_FRAME;
        if (parentFrame != null) {
            setCurrentFrame(parentFrame);
        } else {
            setCurrentFrameUnknown();
        }

        try {
            Thread.sleep(500L);
        } catch (InterruptedException e) {
            ScenarioLogManager.getLogger().error("Wait after switch to parent frame was interrupted!", e);
        }
    }

    /**
     * Switches the WebDriver context to a specific frame, identified by its {@link Frame} object. If
     * the current frame is already the target frame, no action
     * is taken.
     *
     * @param driver the {@link RemoteWebDriver} used to perform the frame switch.
     * @param explicitWaitTimeOut the timeout in seconds to wait for the frame to be available.
     * @param frame the target {@link Frame} to switch to.
     */
    public static void switchToFrame(RemoteWebDriver driver, int explicitWaitTimeOut, Frame frame) {
        if (getCurrentFrame() == null || !getCurrentFrame().equals(frame)) {
            WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(explicitWaitTimeOut));
            wait.withMessage(
                    "Tried to switch to frame \"" + frame.NAME + "\", identified by locator (" + frame.LOCATOR_TYPE + ") \"" + frame.LOCATOR + "\" for "
                            + explicitWaitTimeOut + " seconds!");
            wait.pollingEvery(Duration.of(500L, ChronoUnit.MILLIS));
            wait.until((ExpectedCondition<Boolean>) waitDriver -> {
                CustomAssertions.assertNotNull(waitDriver, "WebDriver object for waits must not be null!");
                try {
                    BasePage basePage = new BasePage((RemoteWebDriver) waitDriver);
                    waitDriver.switchTo().frame(basePage.findElementByLocatorTypeNoWait(frame.LOCATOR, frame.LOCATOR_TYPE));
                    setCurrentFrame(frame);
                    return true;
                } catch (WebDriverException e) {
                    if (e instanceof StaleElementReferenceException || e instanceof ElementNotInteractableException
                            || e instanceof NoSuchFrameException || e instanceof NoSuchElementException
                            || e.getMessage().contains("cannot determine loading status")
                            || e.getMessage().contains("target frame detached")) {
                        return false;
                    } else {
                        ScenarioLogManager.getLogger()
                                .error("Switching to iframe \"{}\" located by {} \"{}\" has failed! Exception thrown: {}", frame.NAME, frame.LOCATOR_TYPE,
                                        frame.LOCATOR, e.getMessage());
                        throw e;
                    }
                }
            });

            try {
                Thread.sleep(500L);
            } catch (InterruptedException e) {
                ScenarioLogManager.getLogger()
                        .error("Wait after switch to iframe \"{}\" located by {} \"{}\" was interrupted!", frame.NAME, frame.LOCATOR_TYPE, frame.LOCATOR, e);
            }
        }
    }

    /**
     * Clears the current frame for the calling thread, removing it from the frame map.
     */
    public static void clearCurrentFrame() {
        CURRENT_FRAME_MAP.remove(Thread.currentThread().getId());
    }
}
