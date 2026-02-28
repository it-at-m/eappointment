package zms.ataf.ui.pages.mailinator;

import org.openqa.selenium.remote.RemoteWebDriver;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.controls.WindowControls;
import ataf.web.model.Window;
import ataf.web.pages.Context;


public class MailinatorPageContext extends Context {
    public static final String NAME = "Mailinator.com";
    public static final String TITLE = "Home - Mailinator";
    public static final String URL = "https://www.mailinator.com/";

    private Window window;
    private boolean messageOpen;

    MailinatorPageContext(RemoteWebDriver driver) {
        super(driver);
        messageOpen = false;
    }

    void setWindow(Window window) {
        this.window = window;
    }

    boolean isMessageOpen() {
        return messageOpen;
    }

    void setMessageOpen(boolean messageOpen) {
        this.messageOpen = messageOpen;
    }

    @Override
    public void set() {
        if (window == null) {
            ScenarioLogManager.getLogger().info("Window hasn't been set! Trying to get it from controller...");
            window = WindowControls.getWindowByTitle(TITLE);
        }
        if (!WindowControls.getActiveWindow().equals(window)) {
            ScenarioLogManager.getLogger().info("Switching to Mailinator page!");
            WindowControls.switchToWindow(DRIVER, window);
        }
    }
}
