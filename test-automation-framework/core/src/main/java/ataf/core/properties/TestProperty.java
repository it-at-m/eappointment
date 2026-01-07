package ataf.core.properties;

import java.util.Objects;

/**
 * A class representing a test property with a name and its associated value, along with an
 * indicator of whether the property has been overwritten.
 *
 * <p>
 * This class encapsulates the properties loaded in the {@link TestProperties} collection, allowing
 * for type-safe access to property values and their names.
 * Additionally, the {@code overwritten} attribute reflects if a property with the same name has
 * been replaced within the collection.
 * </p>
 *
 * @param <T> the type of the value associated with the property
 */
public class TestProperty<T> {
    private final String name;
    private final T value;
    private final boolean overwritten;

    /**
     * Constructs a new TestProperty with the specified name and value. The property is added to the
     * properties map during construction.
     *
     * @param name the name of the property.
     * @param value the value of the property.
     * @see TestProperties#put(String, TestProperty) for details on property addition.
     */
    public TestProperty(String name, T value) {
        this.name = name;
        this.value = value;
        this.overwritten = TestProperties.put(name, this);
    }

    /**
     * Returns the name of the property.
     *
     * @return the name of the property
     */
    public String name() {
        return name;
    }

    /**
     * Returns the value of the property.
     *
     * @return the value of the property
     */
    public T value() {
        return value;
    }

    /**
     * Indicates whether this property was overwritten in the {@link TestProperties} collection.
     *
     * @return {@code true} if the property was overwritten; {@code false} otherwise
     */
    public boolean overwritten() {
        return overwritten;
    }

    @Override
    public String toString() {
        return "TestProperty{" +
                "name='" + name + '\'' +
                ", value=" + value +
                ", overwritten=" + overwritten +
                '}';
    }

    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (o == null || getClass() != o.getClass()) return false;
        TestProperty<?> that = (TestProperty<?>) o;
        return overwritten == that.overwritten && name.equals(that.name) && value.equals(that.value);
    }

    @Override
    public int hashCode() {
        return Objects.hash(name, value, overwritten);
    }
}
