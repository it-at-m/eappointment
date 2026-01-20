package ataf.web.model;

import ataf.core.data.System;

import java.net.URL;
import java.util.HashMap;
import java.util.Map;

/**
 * Special type for enabling better comparison between windows.
 * <p>
 * This class manages different types of windows associated with specific systems. It allows
 * retrieval of window types based on system identifiers.
 * </p>
 *
 * <p>
 * Author: Ludwig Haas (ex.haas02)
 * </p>
 */
public class WindowType {
    private static final Map<System, WindowType> SYSTEM_WINDOW_TYPE_MAP = new HashMap<>();

    /**
     * Constant for unknown or generic types of windows.
     */
    public static final WindowType UNKNOWN = new WindowType("UNKNOWN", null);

    /**
     * Name of the window type
     */
    public final String NAME;

    /**
     * Constructs a WindowType with the specified name and associates it with a system.
     *
     * @param name the name of the window type.
     * @param system the system this window type is associated with (can be null).
     */
    public WindowType(String name, System system) {
        this.NAME = name;
        SYSTEM_WINDOW_TYPE_MAP.put(system, this); // Associates the window type with the system
    }

    /**
     * Retrieves the WindowType associated with the specified system.
     *
     * @param system the system to get the window type for.
     * @return the corresponding WindowType, or UNKNOWN if none is found.
     */
    public static WindowType getSystemWindowType(System system) {
        WindowType windowType = SYSTEM_WINDOW_TYPE_MAP.get(system);
        return windowType != null ? windowType : UNKNOWN; // Returns UNKNOWN if no match is found
    }

    /**
     * Retrieves the WindowType associated with the specified system name.
     *
     * @param systemName the name of the system to get the window type for.
     * @return the corresponding WindowType, or UNKNOWN if none is found.
     */
    public static WindowType getSystemWindowType(String systemName) {
        for (Map.Entry<System, WindowType> entry : SYSTEM_WINDOW_TYPE_MAP.entrySet()) {
            if (entry.getKey() != null && entry.getKey().NAME.equals(systemName)) {
                return entry.getValue(); // Returns the matching WindowType
            }
        }
        return UNKNOWN; // Returns UNKNOWN if no match is found
    }

    /**
     * Retrieves the WindowType associated with the specified system URL.
     *
     * @param systemUrl the URL of the system to get the window type for.
     * @return the corresponding WindowType, or UNKNOWN if none is found.
     */
    public static WindowType getSystemWindowType(URL systemUrl) {
        for (Map.Entry<System, WindowType> entry : SYSTEM_WINDOW_TYPE_MAP.entrySet()) {
            if (entry.getKey() != null && systemUrl != null && entry.getKey().URL.equals(systemUrl.toString())) {
                return entry.getValue(); // Returns the matching WindowType
            }
        }
        return UNKNOWN; // Returns UNKNOWN if no match is found
    }
}
