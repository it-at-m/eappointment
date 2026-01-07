package ataf.core.helpers;

import ataf.core.logging.ScenarioLogManager;

/**
 * This class handles exception processing and logging.
 *
 * @author Philipp Lehmann (ex.lehmann08)
 */
public class ExceptionManager {
    /**
     * Default constructor.
     */
    public ExceptionManager() {
        // Implementation will follow later
    }

    /**
     * Processes the given exception and logs the error message.
     *
     * @param e The exception to be processed
     */
    public static void process(Exception e) {
        ScenarioLogManager.getLogger().error("Error message: {}", e.getMessage());
    }
}
