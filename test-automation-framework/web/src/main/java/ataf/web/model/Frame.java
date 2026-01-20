package ataf.web.model;

import java.io.Serializable;
import java.util.Objects;

/**
 * Represents a frame on a web page, encapsulating its name, locator, locator type, and optionally
 * its parent frame. This class implements {@link Serializable}
 * to allow instances of it to be serialized.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class Frame implements Serializable {

    /**
     * The name of the frame.
     */
    public final String NAME;

    /**
     * The locator used to identify the frame within the web page.
     */
    public final String LOCATOR;

    /**
     * The type of the locator used to identify the frame (e.g., ID, NAME, XPATH).
     */
    public final LocatorType LOCATOR_TYPE;

    /**
     * The parent frame of this frame, or null if this frame has no parent.
     */
    public final Frame PARENT_FRAME;

    /**
     * Constructs a new {@code Frame} with the specified name, locator, locator type, and parent frame.
     *
     * @param name the name of the frame
     * @param locator the locator used to identify the frame
     * @param locatorType the type of the locator
     * @param parentFrame the parent frame, or null if this frame has no parent
     */
    public Frame(String name, String locator, LocatorType locatorType, Frame parentFrame) {
        this.NAME = name;
        this.LOCATOR = locator;
        this.LOCATOR_TYPE = locatorType;
        this.PARENT_FRAME = parentFrame;
    }

    /**
     * Constructs a new {@code Frame} with the specified name. The locator is set to the same value as
     * the name, the locator type is set to
     * {@link LocatorType#NAME}, and the parent frame is set to null.
     *
     * @param name the name of the frame
     */
    public Frame(String name) {
        this(name, name, LocatorType.NAME, null);
    }

    /**
     * Returns a string representation of the frame, including its name, locator, and locator type.
     *
     * @return a string representation of the frame
     */
    @Override
    public String toString() {
        return "Frame{NAME='" + NAME + '\'' + ", LOCATOR='" + LOCATOR + '\'' + ", LOCATOR_TYPE=" + LOCATOR_TYPE + '}';
    }

    /**
     * Indicates whether some other object is "equal to" this one. Two frames are considered equal if
     * they have the same name.
     *
     * @param o the reference object with which to compare
     * @return {@code true} if this object is the same as the {@code o} argument; {@code false}
     *         otherwise
     */
    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (o == null || getClass() != o.getClass()) return false;
        Frame frame = (Frame) o;
        return Objects.equals(NAME, frame.NAME);
    }
}
