package ataf.core.data;

/**
 * Represents abstract test data with a specific data type. This class serves as a base class for
 * other classes that need to define a data type.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public abstract class TestData {
    /**
     * The type of data this instance represents.
     */
    protected final String dataType;

    /**
     * Constructs a new TestData instance with the specified data type.
     *
     * @param dataType The type of data this instance represents.
     */
    public TestData(String dataType) {
        this.dataType = dataType;
    }

    /**
     * Returns the data type of this test data.
     *
     * @return The data type as a String.
     */
    public String getDataType() {
        return dataType;
    }

}
