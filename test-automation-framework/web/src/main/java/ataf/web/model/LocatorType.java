package ataf.web.model;

/**
 * Enum representing different types of locators that can be used to find WebElements.
 * <p>
 * This enum defines various strategies to locate elements within a web page, such as by ID, name,
 * XPath, etc. These locator types are used in conjunction with
 * WebDriver to find elements in the DOM.
 * </p>
 */
public enum LocatorType {
    /**
     * Locates elements by the ID attribute. The ID should be unique within the entire HTML document.
     */
    ID,

    /**
     * Locates elements by the name attribute. This can be used when the name attribute is unique or for
     * form elements.
     */
    NAME,

    /**
     * Locates elements using an XPath expression. XPath allows complex queries to find elements based
     * on their structure in the document.
     */
    XPATH,

    /**
     * Locates a hyperlink element by its visible text. This is used when you know the exact text
     * displayed for a link.
     */
    LINKTEXT,

    /**
     * Locates elements by their class attribute. This can be used when elements share a common class
     * and you want to find them by that class.
     */
    CLASS,

    /**
     * Locates elements using a CSS selector. CSS selectors are patterns used to select elements based
     * on their CSS properties.
     */
    CSSSELECTOR,

    /**
     * Locates elements by their HTML tag name. This is useful when you want to find elements of a
     * specific type, like all {@code <div>} or {@code <input>}
     * tags.
     */
    TAGNAME,

    /**
     * Locates elements by their visible text. Similar to LINKTEXT but may be used with more general
     * text nodes, not just links.
     */
    TEXT
}
