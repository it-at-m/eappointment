package ataf.web.controls;

import ataf.core.assertions.CustomAssertions;
import ataf.core.logging.ScenarioLogManager;
import ataf.web.model.Window;
import ataf.web.model.WindowType;
import org.openqa.selenium.NoSuchWindowException;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.ExpectedCondition;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

import java.time.Duration;
import java.util.ArrayList;
import java.util.LinkedList;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.concurrent.ConcurrentHashMap;

/***
 * This class contains controls for managing windows in a Selenium WebDriver session.
 * It provides functionalities to switch between windows, close windows, and maintain a list of open
 * windows.
 * <p>
 * This class is thread-safe and maintains separate states for each thread.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class WindowControls {
    private static final Map<Long, List<Window>> WINDOW_LIST_MAP = new ConcurrentHashMap<>();
    private static final Map<Long, Integer> LAST_ACTIVE_WINDOW_LIST_INDEX_MAP = new ConcurrentHashMap<>();
    private static final Map<Long, Integer> ACTIVE_WINDOW_LIST_INDEX_MAP = new ConcurrentHashMap<>();

    /***
     * Initializes the window list map for the current thread.
     * This should be called at the beginning of a new WebDriver session.
     */
    public static void initializeWindowMap() {
        WINDOW_LIST_MAP.put(Thread.currentThread().getId(), new LinkedList<>());
    }

    /***
     * Retrieves the list of windows for the current thread.
     *
     * @return The list of windows associated with the current thread.
     */
    private static List<Window> getWindowList() {
        return WINDOW_LIST_MAP.get(Thread.currentThread().getId());
    }

    /***
     * Initializes the last active window index map for the current thread.
     * The initial value is set to -1, indicating no last active window.
     */
    public static void initializeLastActiveWindowListIndexMap() {
        LAST_ACTIVE_WINDOW_LIST_INDEX_MAP.put(Thread.currentThread().getId(), -1);
    }

    /***
     * Initializes the active window index map for the current thread.
     * The initial value is set to 0, indicating the first window as active.
     */
    public static void initializeActiveWindowListIndexMap() {
        ACTIVE_WINDOW_LIST_INDEX_MAP.put(Thread.currentThread().getId(), 0);
    }

    /***
     * Internal controller method to add windows to the list.
     *
     * @param window The window to be added to the controller list.
     */
    private static void addWindow(Window window) {
        if (getWindowList().contains(window)) {
            ScenarioLogManager.getLogger().error("Window \"{}\" has already been added to the list. Will not add!", window.toString());
        } else {
            getWindowList().add(window);
            ScenarioLogManager.getLogger().info("Window added to the list: [{}] {}", getWindowList().indexOf(window), window.toString());
        }
    }

    /***
     * Internal controller method to remove windows from the list.
     *
     * @param window The window to be removed from the controller list.
     */
    private static void removeWindow(Window window) {
        int listIndex = getWindowList().indexOf(window);
        if (listIndex != getIndexOfActiveWindow()) {
            if (getWindowList().contains(window)) {
                getWindowList().remove(window);
                ScenarioLogManager.getLogger().info("Window removed from the list: [{}] {}", listIndex, window.toString());
            } else {
                ScenarioLogManager.getLogger()
                        .error("Window \"{}\" has already been removed from the list or has never been added in the first place. Cannot remove!",
                                window.toString());
            }
        } else {
            ScenarioLogManager.getLogger().error("Cannot remove active window from the list!");
        }
    }

    /***
     * Checks if a window with the specified handle is known to the controller.
     *
     * @param windowHandle The handle of the window to check.
     * @return true if the window is known to the controller, false otherwise.
     */
    public static boolean isWindowWithHandleInList(String windowHandle) {
        for (Window window : getWindowList()) {
            if (window.WINDOW_HANDLE.equals(windowHandle)) {
                return true;
            }
        }
        return false;
    }

    /***
     * Checks if a window with the specified title is known to the controller.
     *
     * @param windowTitle The title of the window to check.
     * @return true if the window is known to the controller, false otherwise.
     */
    public static boolean isWindowWithTitleInList(String windowTitle) {
        for (Window window : getWindowList()) {
            if (window.getWindowTitle().equals(windowTitle)) {
                return true;
            }
        }
        return false;
    }

    /***
     * Switches the WebDriver to a window with the specified title.
     * If there are multiple windows with the same title, it is recommended to use window objects
     * instead.
     *
     * @param driver The RemoteWebDriver instance to switch the window for.
     * @param windowTitle The title of the window to switch to.
     * @throws WindowNotFoundException If no window with the specified title is found.
     */
    public static void switchToWindow(RemoteWebDriver driver, String windowTitle) throws WindowNotFoundException {
        ScenarioLogManager.getLogger().info("Trying to switch to window: {}", windowTitle);
        if (isWindowWithTitleInList(windowTitle)) {
            String currentWindowHandle;
            try {
                currentWindowHandle = driver.getWindowHandle();
            } catch (NoSuchWindowException e) {
                currentWindowHandle = getActiveWindow().WINDOW_HANDLE;
            }
            Window window = getWindowByTitle(windowTitle);
            if (window.WINDOW_HANDLE.equals(currentWindowHandle)) {
                ScenarioLogManager.getLogger().warn("Window with title \"{}\" is already the active window. Cannot switch!", windowTitle);
            } else {
                driver.switchTo().window(window.WINDOW_HANDLE);
                ScenarioLogManager.getLogger().info("Switched to window: {}", windowTitle);
                updateWindowList(driver, window.getWindowType());
                FrameControls.setCurrentFrameUnknown();
            }
        } else {
            throw new WindowNotFoundException("No window with title \"" + windowTitle + "\" could be found!");
        }
    }

    /***
     * Switches the WebDriver to a specified window object.
     *
     * @param driver The RemoteWebDriver instance to switch the window for.
     * @param window The window object to switch to.
     * @throws WindowNotFoundException If the specified window is not in the known window list.
     */
    public static void switchToWindow(RemoteWebDriver driver, Window window) throws WindowNotFoundException {
        String windowTitle = window.getWindowTitle();
        ScenarioLogManager.getLogger().info("Trying to switch to window: {}", windowTitle);
        if (getWindowList().contains(window)) {
            String currentWindowHandle;
            try {
                currentWindowHandle = driver.getWindowHandle();
            } catch (NoSuchWindowException e) {
                currentWindowHandle = getActiveWindow().WINDOW_HANDLE;
            }
            if (window.WINDOW_HANDLE.equals(currentWindowHandle)) {
                ScenarioLogManager.getLogger().warn("Window with title \"{}\" is already the active window. Cannot switch!", windowTitle);
            } else {
                driver.switchTo().window(window.WINDOW_HANDLE);
                ScenarioLogManager.getLogger().info("Switched to window: {}", windowTitle);
                updateWindowList(driver, window.getWindowType());
                FrameControls.setCurrentFrameUnknown();
            }
        } else {
            throw new WindowNotFoundException("No window with title \"" + windowTitle + "\" could be found!");
        }
    }

    /***
     * Switches to an opened window by title. The window must not be known to the controller.
     * Waits for the specified window to appear before switching.
     *
     * @param driver The RemoteWebDriver instance to switch the window for.
     * @param explicitWaitTimeOut The time in seconds to wait for the operation to complete.
     * @param expectedWindowType The expected type of the window to switch to.
     * @param windowTitleToWaitFor The title of the browser window to wait for. Mandatory.
     * @param useContains If true, waits for the window title to contain the specified title instead of
     *            matching exactly.
     */
    public static void switchToOpenedWindow(RemoteWebDriver driver, int explicitWaitTimeOut, WindowType expectedWindowType, String windowTitleToWaitFor,
            boolean useContains) {
        ScenarioLogManager.getLogger().info("Trying to switch to window: {}", windowTitleToWaitFor);
        WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(3L * explicitWaitTimeOut));
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            CustomAssertions.assertNotNull(waitDriver, "WebDriver object for waits must not be null!");
            for (String windowHandle : waitDriver.getWindowHandles()) {
                if (!isWindowWithHandleInList(windowHandle)) {
                    waitDriver.switchTo().window(windowHandle);
                    WebDriverWait titleWait = new WebDriverWait(waitDriver, Duration.ofSeconds(explicitWaitTimeOut));
                    if (useContains) {
                        titleWait.until(ExpectedConditions.titleContains(windowTitleToWaitFor));
                    } else {
                        titleWait.until(ExpectedConditions.titleIs(windowTitleToWaitFor));
                    }
                    ScenarioLogManager.getLogger().info("Switched to window: {}", waitDriver.getTitle());
                    updateWindowList((RemoteWebDriver) waitDriver, expectedWindowType);
                    FrameControls.setCurrentFrameUnknown();
                    if (useContains && getActiveWindow().getWindowTitle().contains(windowTitleToWaitFor) || getActiveWindow().getWindowTitle()
                            .equals(windowTitleToWaitFor)) {
                        return true;
                    }
                }
            }
            return false;
        });
    }

    /***
     * Switches to an opened window by title. The window must not be known to the controller.
     * Waits for the specified window to appear before switching.
     *
     * @param driver The RemoteWebDriver instance to switch the window for.
     * @param explicitWaitTimeOut The time in seconds to wait for the operation to complete.
     * @param expectedWindowType The expected type of the window to switch to.
     * @param windowTitleToWaitFor The title of the browser window to wait for. Mandatory.
     */
    public static void switchToOpenedWindow(RemoteWebDriver driver, int explicitWaitTimeOut, WindowType expectedWindowType, String windowTitleToWaitFor) {
        switchToOpenedWindow(driver, explicitWaitTimeOut, expectedWindowType, windowTitleToWaitFor, false);
    }

    /***
     * Switches to an opened window that is not known to the controller.
     * Particularly useful for handling popups that may close automatically after interactions.
     *
     * @param driver The RemoteWebDriver instance to perform the switch on.
     * @param explicitWaitTimeOut The time in seconds to wait for the operation to complete.
     *            An exception will be thrown if the timeout is exceeded.
     * @param expectedWindowType The type of window expected after the switch.
     *            Use 'unknown' if unsure.
     */
    public static void switchToTemporaryWindow(RemoteWebDriver driver, int explicitWaitTimeOut, WindowType expectedWindowType) {
        ScenarioLogManager.getLogger().info("Trying to switch to temporary window");
        WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(explicitWaitTimeOut));
        wait.until((ExpectedCondition<Boolean>) waitDriver -> {
            CustomAssertions.assertNotNull(waitDriver, "WebDriver object for waits must not be null!");
            for (String windowHandle : waitDriver.getWindowHandles()) {
                if (!isWindowWithHandleInList(windowHandle)) {
                    waitDriver.switchTo().window(windowHandle);
                    ScenarioLogManager.getLogger().info("Switched to window: {}", waitDriver.getTitle());
                    updateWindowList((RemoteWebDriver) waitDriver, expectedWindowType);
                    FrameControls.setCurrentFrameUnknown();
                    return true;
                }
            }
            return false;
        });
    }

    /***
     * Closes an open window identified by its title.
     * Note that multiple windows with the same title can exist; using a Window object is safer.
     *
     * @param driver The RemoteWebDriver instance used to close the window.
     * @param windowTitle The title of the window to be closed.
     * @throws WindowNotFoundException If no window with the specified title is registered,
     *             or if the current window is unknown to the controller,
     *             or if there is no last active window.
     * @throws InvalidWindowOperationException If attempting to close the only open window
     *             or if no last active window was specified.
     */
    public static void closeWindow(RemoteWebDriver driver, String windowTitle) throws WindowNotFoundException, InvalidWindowOperationException {
        ScenarioLogManager.getLogger().info("Trying to close window \"{}\"", windowTitle);
        if (isWindowWithTitleInList(windowTitle)) {
            Window windowToClose = getWindowByTitle(windowTitle);
            Window currentWindow = new Window(driver);

            // Check if current window is known
            if (!currentWindow.equals(getActiveWindow())) {
                throw new WindowNotFoundException(
                        "Current window with title \"" + currentWindow.getWindowTitle()
                                + "\" is not known by controller! Use only controller functions when handling windows!");
            }

            if (currentWindow.equals(windowToClose)) {
                // Check if last active window exists
                if (getIndexOfLastActiveWindow() > -1) {
                    driver.close();
                    ScenarioLogManager.getLogger().info("Window \"{}\" closed!", windowTitle);
                    Window lastActiveWindow = getLastActiveWindow();
                    switchToWindow(driver, lastActiveWindow);
                    updateWindowList(driver, lastActiveWindow.getWindowType());
                } else {
                    if (getWindowList().size() == 1) {
                        throw new InvalidWindowOperationException("Window \"" + windowTitle + "\" cannot be closed, because it is the only open window!");
                    } else {
                        throw new InvalidWindowOperationException(
                                "Window \"" + windowTitle + "\" cannot be closed, because last active window was not specified!");
                    }
                }
            } else {
                switchToWindow(driver, windowTitle);
                driver.close();
                ScenarioLogManager.getLogger().info("Window \"{}\" closed!", windowTitle);
                switchToWindow(driver, currentWindow);
                updateWindowList(driver, currentWindow.getWindowType());
            }
        } else {
            throw new WindowNotFoundException("No window with title \"" + windowTitle + "\" could be found!");
        }
    }

    /***
     * Closes an open window identified by a Window object.
     * Note that multiple windows with the same title can exist; using this method is safer.
     *
     * @param driver The RemoteWebDriver instance used to close the window.
     * @param window The Window object representing the window to be closed.
     * @throws WindowNotFoundException If no window with the specified title is registered,
     *             or if the current window is unknown to the controller,
     *             or if there is no last active window.
     * @throws InvalidWindowOperationException If attempting to close the only open window
     *             or if no last active window was specified.
     */
    public static void closeWindow(RemoteWebDriver driver, Window window) throws WindowNotFoundException, InvalidWindowOperationException {
        String windowTitle = window.getWindowTitle();
        ScenarioLogManager.getLogger().info("Trying to close window \"{}\"", windowTitle);

        if (getWindowList().contains(window)) {
            Window currentWindow = new Window(driver);

            // Check if current window is known
            if (!currentWindow.equals(getActiveWindow())) {
                throw new WindowNotFoundException(
                        "Current window with title \"" + currentWindow.getWindowTitle()
                                + "\" is not known by controller! Use only controller functions when handling windows!");
            }

            if (currentWindow.equals(window)) {
                // Check if last active window exists
                if (getIndexOfLastActiveWindow() > -1) {
                    driver.close();
                    ScenarioLogManager.getLogger().info("Window \"{}\" closed!", windowTitle);
                    Window lastActiveWindow = getLastActiveWindow();
                    switchToWindow(driver, lastActiveWindow);
                    updateWindowList(driver, lastActiveWindow.getWindowType());
                } else {
                    if (getWindowList().size() == 1) {
                        throw new InvalidWindowOperationException("Window \"" + windowTitle + "\" cannot be closed, because it is the only open window!");
                    } else {
                        throw new InvalidWindowOperationException(
                                "Window \"" + windowTitle + "\" cannot be closed, because last active window was not specified!");
                    }
                }
            } else {
                switchToWindow(driver, window);
                driver.close();
                ScenarioLogManager.getLogger().info("Window \"{}\" closed!", windowTitle);
                switchToWindow(driver, currentWindow);
                updateWindowList(driver, currentWindow.getWindowType());
            }
        } else {
            throw new WindowNotFoundException("No window with title \"" + windowTitle + "\" could be found!");
        }
    }

    /***
     * Closes all open windows except for one specified window.
     *
     * @param driver The RemoteWebDriver instance used to perform the close operations.
     * @param windowToLeaveOpen The Window object that should be left open after the operation.
     */
    public static void closeAllWindowsButOne(RemoteWebDriver driver, Window windowToLeaveOpen) {
        List<Window> windowListTemp = new LinkedList<>(getWindowList());
        windowListTemp.forEach(window -> {
            if (!window.equals(windowToLeaveOpen)) {
                closeWindow(driver, window);
            }
        });
        windowListTemp.clear();
    }

    /***
     * Updates the window controller to synchronize the current state with the WebDriver instance.
     * This should be called after navigation and refresh operations.
     *
     * @param driver The RemoteWebDriver instance used to update the window list.
     * @param expectedWindowType The expected type of the currently active window.
     *            Use 'unknown' if unsure.
     */
    public static void updateWindowList(RemoteWebDriver driver, WindowType expectedWindowType) {
        Window activeWindow;
        try {
            // Update the active window
            activeWindow = new Window(driver, expectedWindowType);
            activeWindow.updateWindowTitle(driver);
            if (getWindowList().contains(activeWindow)) {
                int indexOfActiveWindow = getWindowList().indexOf(activeWindow);
                getWindowList().remove(indexOfActiveWindow);
                getWindowList().add(indexOfActiveWindow, activeWindow);
            } else {
                addWindow(activeWindow);
            }
        } catch (NoSuchWindowException e) {
            ScenarioLogManager.getLogger().warn(e.getMessage(), e);
            ScenarioLogManager.getLogger()
                    .warn("Current WebDriver window was already closed! Trying to switch to last active window registered to controller...");
            activeWindow = getLastActiveWindow();
            switchToWindow(driver, activeWindow);
        }

        // Remove closed windows from the list
        List<Window> windowsToRemove = new ArrayList<>();
        Set<String> windowHandles = driver.getWindowHandles();
        if (!getWindowList().isEmpty()) {
            for (Window window : getWindowList()) {
                if (!windowHandles.contains(window.WINDOW_HANDLE)) {
                    windowsToRemove.add(window);
                }
            }
            windowsToRemove.forEach(WindowControls::removeWindow);
        }

        // Update indices for active windows
        int indexOfActiveWindow = getWindowList().indexOf(activeWindow);
        if (getIndexOfActiveWindow() != indexOfActiveWindow) {
            if (getWindowList().size() < 2) {
                LAST_ACTIVE_WINDOW_LIST_INDEX_MAP.replace(Thread.currentThread().getId(), -1);
            } else {
                LAST_ACTIVE_WINDOW_LIST_INDEX_MAP.replace(Thread.currentThread().getId(), getIndexOfActiveWindow());
            }
            ACTIVE_WINDOW_LIST_INDEX_MAP.replace(Thread.currentThread().getId(), indexOfActiveWindow);
        }
    }

    /***
     * Retrieves a Window object known by the controller using its window handle.
     *
     * @param windowHandle The Selenium WebDriver style window handle.
     * @return The Window object associated with the specified handle.
     * @throws WindowNotFoundException If no window with the specified handle is found in the
     *             controller.
     */
    public static Window getWindowByHandle(String windowHandle) throws WindowNotFoundException {
        for (Window window : getWindowList()) {
            if (window.WINDOW_HANDLE.equals(windowHandle)) {
                return window;
            }
        }
        throw new WindowNotFoundException("No window with handle \"" + windowHandle + "\" could be found!");
    }

    /***
     * Retrieves a Window object known by the controller using its title.
     *
     * @param windowTitle The title of the window to retrieve.
     * @return The Window object associated with the specified title.
     * @throws WindowNotFoundException If no window with the specified title is found in the controller.
     */
    public static Window getWindowByTitle(String windowTitle) throws WindowNotFoundException {
        for (Window window : getWindowList()) {
            if (window.getWindowTitle().equals(windowTitle)) {
                return window;
            }
        }
        throw new WindowNotFoundException("No window with title \"" + windowTitle + "\" could be found!");
    }

    /***
     * Retrieves the currently active Window object registered by the controller.
     *
     * @return The Window object of the active window.
     * @throws WindowNotFoundException If no active window is currently registered.
     */
    public static Window getActiveWindow() throws WindowNotFoundException {
        if (!getWindowList().isEmpty()) {
            return getWindowList().get(getIndexOfActiveWindow());
        } else {
            throw new WindowNotFoundException("No active window has been registered!");
        }
    }

    /***
     * Retrieves the index of the currently active window registered by the controller.
     *
     * @return The index of the active window.
     */
    public static int getIndexOfActiveWindow() {
        return ACTIVE_WINDOW_LIST_INDEX_MAP.get(Thread.currentThread().getId());
    }

    /***
     * Retrieves the last active Window object registered by the controller.
     *
     * @return The Window object of the last active window.
     * @throws WindowNotFoundException If no last active window is currently registered.
     */
    public static Window getLastActiveWindow() throws WindowNotFoundException {
        if (!getWindowList().isEmpty() && getIndexOfLastActiveWindow() > -1) {
            return getWindowList().get(getIndexOfLastActiveWindow());
        } else {
            throw new WindowNotFoundException("No last active window has been registered!");
        }
    }

    /***
     * Retrieves the index of the last active window registered by the controller.
     *
     * @return The index of the last active window.
     */
    public static int getIndexOfLastActiveWindow() {
        return LAST_ACTIVE_WINDOW_LIST_INDEX_MAP.get(Thread.currentThread().getId());
    }

    /***
     * Opens a new tab or window and switches to it.
     *
     * @param driver The RemoteWebDriver instance used to open the new window.
     * @param expectedWindowType The expected type of the newly opened window.
     *            Use 'unknown' if unsure.
     * @param windowType Specifies if a new tab or a new window is opened.
     *            Valid values are "WindowType.TAB" and "WindowType.WINDOW".
     * @return The newly opened Window object.
     */
    public static Window newWindow(RemoteWebDriver driver, WindowType expectedWindowType, org.openqa.selenium.WindowType windowType) {
        driver.switchTo().newWindow(windowType);
        updateWindowList(driver, expectedWindowType);
        return getActiveWindow();
    }

    /***
     * Clears the controller of all window data. This should be called when the WebDriver session is
     * closed.
     */
    public static void clearWindowList() {
        if (!getWindowList().isEmpty()) {
            try {
                getWindowList().clear();
                ScenarioLogManager.getLogger().info("Window list cleared!");
            } catch (UnsupportedOperationException e) {
                ScenarioLogManager.getLogger().error(e.getMessage(), e);
            }
        }
        WINDOW_LIST_MAP.remove(Thread.currentThread().getId());
        LAST_ACTIVE_WINDOW_LIST_INDEX_MAP.remove(Thread.currentThread().getId());
        ACTIVE_WINDOW_LIST_INDEX_MAP.remove(Thread.currentThread().getId());
    }

    /***
     * Exception thrown when a requested window is not found in the controller.
     */
    public static class WindowNotFoundException extends RuntimeException {
        /**
         * @param message the exception message.
         */
        public WindowNotFoundException(String message) {
            super(message);
        }

        /**
         * @param message the exception message.
         * @param cause the cause of the exception.
         */
        public WindowNotFoundException(String message, Throwable cause) {
            super(message, cause);
        }

        /**
         * @param cause the cause of the exception.
         */
        public WindowNotFoundException(Throwable cause) {
            super(cause);
        }
    }

    /***
     * Exception thrown for invalid operations on windows, such as trying to close the only open window.
     */
    public static class InvalidWindowOperationException extends RuntimeException {
        /**
         * @param message the exception message.
         */
        public InvalidWindowOperationException(String message) {
            super(message);
        }

        /**
         * @param message the exception message.
         * @param cause the cause of the exception.
         */
        public InvalidWindowOperationException(String message, Throwable cause) {
            super(message, cause);
        }

        /**
         * @param cause the cause of the exception.
         */
        public InvalidWindowOperationException(Throwable cause) {
            super(cause);
        }
    }
}
