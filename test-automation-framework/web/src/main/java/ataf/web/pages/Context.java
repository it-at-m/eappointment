package ataf.web.pages;

import org.openqa.selenium.remote.RemoteWebDriver;

/**
 * Abstract class representing a context that can be set in a web page. This class extends
 * {@link BasePage} and is designed to be inherited by concrete classes
 * that define specific contexts for web interactions.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public abstract class Context extends BasePage {

    /**
     * Constructor that initializes the context with a {@link RemoteWebDriver}.
     *
     * @param driver the RemoteWebDriver instance to be used for this context
     */
    public Context(RemoteWebDriver driver) {
        super(driver);
    }

    /**
     * Abstract method to set the context. This method should be implemented by subclasses to define how
     * the context is applied within the page.
     */
    public abstract void set();
}
