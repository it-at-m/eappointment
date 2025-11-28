package helpers;

import helpers.zmsapi.ProcessStatusBuilder;
import helpers.zmsapi.StatusResponseBuilder;
import helpers.zmscitizenapi.OfficesAndServicesResponseBuilder;

/**
 * Factory class for creating test data builders.
 * Provides convenient static factory methods to access builders organized by API domain.
 * 
 * <p>Builders are organized in sub-packages:
 * <ul>
 *   <li>{@code helpers.zmsapi} - Builders for ZMS API DTOs</li>
 *   <li>{@code helpers.zmscitizenapi} - Builders for Citizen API DTOs</li>
 * </ul>
 * 
 * <p>This class serves as a central entry point for test data creation,
 * but individual builders can also be instantiated directly if preferred.
 */
public final class TestDataBuilder {

    private TestDataBuilder() {
        // Utility class - prevent instantiation
    }

    /**
     * Create a new StatusResponseBuilder.
     *
     * @return StatusResponseBuilder instance
     */
    public static StatusResponseBuilder statusResponse() {
        return new StatusResponseBuilder();
    }

    /**
     * Create a new ProcessStatusBuilder.
     *
     * @return ProcessStatusBuilder instance
     */
    public static ProcessStatusBuilder processStatus() {
        return new ProcessStatusBuilder();
    }

    /**
     * Create a new OfficesAndServicesResponseBuilder.
     *
     * @return OfficesAndServicesResponseBuilder instance
     */
    public static OfficesAndServicesResponseBuilder officesAndServicesResponse() {
        return new OfficesAndServicesResponseBuilder();
    }
}
