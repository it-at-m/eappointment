package ataf.web.model;

import ataf.core.logging.ScenarioLogManager;
import org.openqa.selenium.remote.RemoteWebDriver;

import java.io.Serializable;

/***
 * Represents a browser window that is opened and registered with Selenium WebDriver.
 *
 * <p>
 * This class provides functionalities to store and retrieve window properties such as
 * window handle, title, and type. It also includes methods to update the window title
 * and compare windows based on their handles.
 * </p>
 *
 * <p>
 * Usage example:
 * </p>
 *
 * <pre>
 * {@code
 * Window currentWindow = new Window(driver, WindowType.MAIN);
 * String title = currentWindow.getWindowTitle();
 * currentWindow.updateWindowTitle(driver);
 * }
 * </pre>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class Window implements Serializable {
    /**
     * Constant of type {@link String} which represents the window handle.
     */
    public final String WINDOW_HANDLE;

    /**
     * The title of the window. Usually can be found in the header HTML code of the webpage.
     */
    private String windowTitle;

    /**
     * The type of window to be assigned.
     */
    private WindowType windowType;

    /***
     * Constructs a basic Window object. This constructor is intended to be used
     * when the current active window's type is unknown.
     *
     * @param driver the RemoteWebDriver instance used to create the Window object.
     */
    public Window(RemoteWebDriver driver) {
        this(driver, WindowType.UNKNOWN);
    }

    /***
     * Constructs a Window object with a specified window type.
     * This constructor should be used whenever possible for better clarity and specificity.
     *
     * @param driver the RemoteWebDriver instance used to create the Window object.
     * @param windowType the type of the window, which specifies its purpose or role.
     */
    public Window(RemoteWebDriver driver, WindowType windowType) {
        this.WINDOW_HANDLE = driver.getWindowHandle();
        this.windowTitle = driver.getTitle();
        this.windowType = windowType;
    }

    /***
     * Returns a string representation of the Window object, including its handle,
     * title, and type. This method overrides the default {@code toString()} method
     * to provide more readable log output.
     *
     * @return a string representation of the Window object.
     */
    @Override
    public String toString() {
        return "Window{" + "windowHandle='" + WINDOW_HANDLE + '\'' + ", windowTitle='" + windowTitle + '\'' + ", windowType=" + windowType + '}';
    }

    /***
     * Compares this Window object with another object for equality.
     * Two windows are considered equal if they have the same window handle.
     *
     * @param o the object to compare with this Window.
     * @return {@code true} if the specified object is a Window with the same handle; {@code false}
     *         otherwise.
     */
    @Override
    public boolean equals(Object o) {
        if (this == o)
            return true;
        if (!(o instanceof Window window))
            return false;
        return WINDOW_HANDLE.equals(window.WINDOW_HANDLE);
    }

    /***
     * Retrieves the title of this window.
     *
     * @return the title of the window.
     */
    public String getWindowTitle() {
        return windowTitle;
    }

    /***
     * Retrieves the type of this window.
     *
     * @return the type of the window.
     */
    public WindowType getWindowType() {
        return windowType;
    }

    /***
     * Updates the title of this window based on the current title
     * of the associated WebDriver instance. The update will only succeed
     * if the window is the currently active window in the WebDriver.
     *
     * @param driver the RemoteWebDriver instance used to update the title.
     */
    public void updateWindowTitle(RemoteWebDriver driver) {
        if (this.WINDOW_HANDLE.equals(driver.getWindowHandle())) {
            this.windowTitle = driver.getTitle();
        } else {
            ScenarioLogManager.getLogger().error("Updating of window title was not possible because the window is not the active window!");
        }
    }

    /***
     * Sets the type of this window.
     *
     * @param windowType the type of the window to set.
     */
    public void setWindowType(WindowType windowType) {
        this.windowType = windowType;
    }
}
