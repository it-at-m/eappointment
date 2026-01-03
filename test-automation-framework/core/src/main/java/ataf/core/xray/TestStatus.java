package ataf.core.xray;

/**
 * Enum representing the status of a test execution. Each status includes an ID, rank, name,
 * description, color code, requirement status name, and a flag
 * indicating whether the status is final.
 *
 * <p>
 * Author: Ludwig Haas (ex.haas02)
 * </p>
 */
public enum TestStatus {
    /**
     * Test has passed
     */
    PASS(0, 0, "PASS", "Der Testlauf ist bestanden", "#95C160", "OK", true),

    /**
     * Test has not started
     */
    TODO(1, 1, "TODO", "Der Testlauf hat noch nicht begonnen", "#A2A6AE", "NOTRUN", false),

    /**
     * Test is currently running
     */
    EXECUTING(2, 2, "EXECUTING", "Der Testlauf wird gerade ausgeführt", "#F1E069", "NOTRUN", false),

    /**
     * Test has failed
     */
    FAIL(3, 3, "FAIL", "Der Testlauf ist fehlgeschlagen", "#D45D52", "NOK", true),

    /**
     * Test has been aborted
     */
    ABORTED(4, 4, "ABORTED", "Der Testlauf wurde abgebrochen", "#111111", "NOTRUN", true),

    /**
     * Test is blocked
     */
    BLOCKIERT(1000, 5, "BLOCKIERT", "Testfall kann nicht ausgeführt werden", "#FFD138", "NOTRUN", false);

    /**
     * Unique identifier for the test status
     */
    public final int ID;

    /**
     * Rank of the status for sorting purposes
     */
    public final int RANK;

    /**
     * Name of the status
     */
    public final String NAME;

    /**
     * Description of the status in German
     */
    public final String DESCRIPTION;

    /**
     * Color code associated with the status
     */
    public final String COLOR;

    /**
     * Requirement status name
     */
    public final String REQUIREMENT_STATUS_NAME;

    /**
     * Indicates if this status is final
     */
    public final boolean FINAL;

    /**
     * Constructs a TestStatus enum with the specified parameters.
     *
     * @param id Unique identifier for the test status.
     * @param rank Rank of the status for sorting.
     * @param name Name of the status.
     * @param description Description of the status.
     * @param color Color code associated with the status.
     * @param requirementStatusName Requirement status name.
     * @param isFinal Indicates if this status is final.
     */
    TestStatus(int id, int rank, String name, String description, String color, String requirementStatusName, boolean isFinal) {
        this.ID = id;
        this.RANK = rank;
        this.NAME = name;
        this.DESCRIPTION = description;
        this.COLOR = color;
        this.REQUIREMENT_STATUS_NAME = requirementStatusName;
        this.FINAL = isFinal;
    }

    /**
     * Retrieves the TestStatus corresponding to the given status name.
     *
     * @param status the name of the status to retrieve.
     * @return the TestStatus associated with the given name, or null if no matching status exists.
     */
    public static TestStatus getStatus(String status) {
        return switch (status) {
            case "PASS" -> PASS;
            case "TODO" -> TODO;
            case "EXECUTING" -> EXECUTING;
            case "FAIL" -> FAIL;
            case "ABORTED" -> ABORTED;
            case "BLOCKIERT" -> BLOCKIERT;
            default -> null; // No matching status found
        };
    }

    /**
     * Returns a string representation of the TestStatus.
     *
     * @return the name of the status.
     */
    @Override
    public String toString() {
        return NAME; // Returns the name of the status
    }
}
