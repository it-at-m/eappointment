package zms.ataf.rest.dto.zmsapi;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import lombok.Data;

/**
 * Response model for the /status/ endpoint.
 * Based on schema: zmsentities/schema/status.json
 */
@Data
@JsonIgnoreProperties(ignoreUnknown = true)
public class StatusResponse {

    private String generated;
    private String server;
    private ProcessStats processes;
    private Version version;
    private Sources sources;
    private MailStats mail;
    private NotificationStats notification;
    private DatabaseStats database;

    /**
     * Process statistics nested object.
     */
    @Data
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class ProcessStats {
        private Integer blocked;
        private Integer confirmed;
        private Integer deleted;
        private Integer missed;
        private Integer parked;
        private Integer reserved;
        private Integer outdated;
        private String lastCalculate;
        private String lastInsert;
        private String outdatedOldest;
    }

    /**
     * Version information nested object.
     * Note: patch can be a string (e.g., "00-muc33-patch7-55-g3e25da4c3") or a number.
     */
    @Data
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class Version {
        private String major;
        private String minor;
        private String patch;

        public Integer getMajorAsInt() {
            try {
                return major != null ? Integer.parseInt(major) : null;
            } catch (NumberFormatException e) {
                return null;
            }
        }

        public Integer getMinorAsInt() {
            try {
                return minor != null ? Integer.parseInt(minor) : null;
            } catch (NumberFormatException e) {
                return null;
            }
        }
    }

    /**
     * Sources information nested object.
     */
    @Data
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class Sources {
        private DldbSource dldb;

        @Data
        @JsonIgnoreProperties(ignoreUnknown = true)
        public static class DldbSource {
            private String last;
        }
    }

    /**
     * Mail statistics nested object.
     */
    @Data
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class MailStats {
        private Integer queueCount;
        private Long oldestSeconds;
        private Long newestSeconds;
    }

    /**
     * Notification statistics nested object.
     */
    @Data
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class NotificationStats {
        private Integer queueCount;
        private Long oldestSeconds;
        private Long newestSeconds;
    }

    /**
     * Database statistics nested object.
     */
    @Data
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class DatabaseStats {
        private String clusterStatus;
        private Integer locks;
        private String logbin;
        private Double nodeConnections;
        private String problems;
        private Integer threads;
    }
}
