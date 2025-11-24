package dto.common;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

/**
 * Generic wrapper for API responses that contain meta and data fields.
 * Used by both ZMS API and Citizen API.
 *
 * @param <T> The type of data contained in the response
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class ApiResponse<T> {

    @JsonProperty("$schema")
    private String schema;

    @JsonProperty("meta")
    private MetaResult meta;

    @JsonProperty("data")
    private T data;

    public String getSchema() {
        return schema;
    }

    public void setSchema(String schema) {
        this.schema = schema;
    }

    public MetaResult getMeta() {
        return meta;
    }

    public void setMeta(MetaResult meta) {
        this.meta = meta;
    }

    public T getData() {
        return data;
    }

    public void setData(T data) {
        this.data = data;
    }

    /**
     * Meta result information.
     */
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class MetaResult {
        @JsonProperty("error")
        private Boolean error;

        @JsonProperty("generated")
        private String generated;

        @JsonProperty("server")
        private String server;

        @JsonProperty("length")
        private Integer length;

        @JsonProperty("message")
        private String message;

        @JsonProperty("status")
        private String status;

        @JsonProperty("route")
        private String route;

        @JsonProperty("reducedData")
        private Boolean reducedData;

        @JsonProperty("exception")
        private String exception;

        @JsonProperty("retryAfter")
        private String retryAfter;

        public Boolean getError() {
            return error;
        }

        public void setError(Boolean error) {
            this.error = error;
        }

        public String getGenerated() {
            return generated;
        }

        public void setGenerated(String generated) {
            this.generated = generated;
        }

        public String getServer() {
            return server;
        }

        public void setServer(String server) {
            this.server = server;
        }

        public Integer getLength() {
            return length;
        }

        public void setLength(Integer length) {
            this.length = length;
        }

        public String getMessage() {
            return message;
        }

        public void setMessage(String message) {
            this.message = message;
        }

        public String getStatus() {
            return status;
        }

        public void setStatus(String status) {
            this.status = status;
        }

        public String getRoute() {
            return route;
        }

        public void setRoute(String route) {
            this.route = route;
        }

        public Boolean getReducedData() {
            return reducedData;
        }

        public void setReducedData(Boolean reducedData) {
            this.reducedData = reducedData;
        }

        public String getException() {
            return exception;
        }

        public void setException(String exception) {
            this.exception = exception;
        }

        public String getRetryAfter() {
            return retryAfter;
        }

        public void setRetryAfter(String retryAfter) {
            this.retryAfter = retryAfter;
        }
    }
}
