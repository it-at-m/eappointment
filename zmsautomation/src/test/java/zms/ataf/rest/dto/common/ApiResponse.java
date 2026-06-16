package zms.ataf.rest.dto.common;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

import lombok.Data;

/**
 * Generic wrapper for API responses that contain meta and data fields.
 * Used by both ZMS API and Citizen API.
 *
 * @param <T> The type of data contained in the response
 */
@Data
@JsonIgnoreProperties(ignoreUnknown = true)
public class ApiResponse<T> {

    @JsonProperty("$schema")
    private String schema;

    private MetaResult meta;
    private T data;

    /**
     * Meta result information.
     */
    @Data
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class MetaResult {
        private Boolean error;
        private String generated;
        private String server;
        private Integer length;
        private String message;
        private String status;
        private String route;
        private Boolean reducedData;
        private String exception;
        private String retryAfter;
    }
}
