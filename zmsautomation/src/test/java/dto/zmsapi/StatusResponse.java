package dto.zmsapi;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

/**
 * Response model for the /status/ endpoint.
 * Based on schema: zmsentities/schema/status.json
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class StatusResponse {

    @JsonProperty("generated")
    private String generated;

    @JsonProperty("server")
    private String server;

    @JsonProperty("processes")
    private ProcessStats processes;

    @JsonProperty("version")
    private Version version;

    @JsonProperty("sources")
    private Sources sources;

    @JsonProperty("mail")
    private MailStats mail;

    @JsonProperty("notification")
    private NotificationStats notification;

    @JsonProperty("database")
    private DatabaseStats database;

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

    public ProcessStats getProcesses() {
        return processes;
    }

    public void setProcesses(ProcessStats processes) {
        this.processes = processes;
    }

    public Version getVersion() {
        return version;
    }

    public void setVersion(Version version) {
        this.version = version;
    }

    public Sources getSources() {
        return sources;
    }

    public void setSources(Sources sources) {
        this.sources = sources;
    }

    public MailStats getMail() {
        return mail;
    }

    public void setMail(MailStats mail) {
        this.mail = mail;
    }

    public NotificationStats getNotification() {
        return notification;
    }

    public void setNotification(NotificationStats notification) {
        this.notification = notification;
    }

    public DatabaseStats getDatabase() {
        return database;
    }

    public void setDatabase(DatabaseStats database) {
        this.database = database;
    }

    /**
     * Process statistics nested object.
     */
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class ProcessStats {
        @JsonProperty("blocked")
        private Integer blocked;

        @JsonProperty("confirmed")
        private Integer confirmed;

        @JsonProperty("deleted")
        private Integer deleted;

        @JsonProperty("missed")
        private Integer missed;

        @JsonProperty("parked")
        private Integer parked;

        @JsonProperty("reserved")
        private Integer reserved;

        @JsonProperty("outdated")
        private Integer outdated;

        @JsonProperty("lastCalculate")
        private String lastCalculate;

        @JsonProperty("lastInsert")
        private Long lastInsert;

        @JsonProperty("outdatedOldest")
        private String outdatedOldest;

        // Getters and setters
        public Integer getBlocked() {
            return blocked;
        }

        public void setBlocked(Integer blocked) {
            this.blocked = blocked;
        }

        public Integer getConfirmed() {
            return confirmed;
        }

        public void setConfirmed(Integer confirmed) {
            this.confirmed = confirmed;
        }

        public Integer getDeleted() {
            return deleted;
        }

        public void setDeleted(Integer deleted) {
            this.deleted = deleted;
        }

        public Integer getMissed() {
            return missed;
        }

        public void setMissed(Integer missed) {
            this.missed = missed;
        }

        public Integer getParked() {
            return parked;
        }

        public void setParked(Integer parked) {
            this.parked = parked;
        }

        public Integer getReserved() {
            return reserved;
        }

        public void setReserved(Integer reserved) {
            this.reserved = reserved;
        }

        public Integer getOutdated() {
            return outdated;
        }

        public void setOutdated(Integer outdated) {
            this.outdated = outdated;
        }

        public String getLastCalculate() {
            return lastCalculate;
        }

        public void setLastCalculate(String lastCalculate) {
            this.lastCalculate = lastCalculate;
        }

        public Long getLastInsert() {
            return lastInsert;
        }

        public void setLastInsert(Long lastInsert) {
            this.lastInsert = lastInsert;
        }

        public String getOutdatedOldest() {
            return outdatedOldest;
        }

        public void setOutdatedOldest(String outdatedOldest) {
            this.outdatedOldest = outdatedOldest;
        }
    }

    /**
     * Version information nested object.
     * Note: patch can be a string (e.g., "00-muc33-patch7-55-g3e25da4c3") or a number.
     */
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class Version {
        @JsonProperty("major")
        private String major;

        @JsonProperty("minor")
        private String minor;

        @JsonProperty("patch")
        private String patch;

        public String getMajor() {
            return major;
        }

        public void setMajor(String major) {
            this.major = major;
        }

        public Integer getMajorAsInt() {
            try {
                return major != null ? Integer.parseInt(major) : null;
            } catch (NumberFormatException e) {
                return null;
            }
        }

        public String getMinor() {
            return minor;
        }

        public void setMinor(String minor) {
            this.minor = minor;
        }

        public Integer getMinorAsInt() {
            try {
                return minor != null ? Integer.parseInt(minor) : null;
            } catch (NumberFormatException e) {
                return null;
            }
        }

        public String getPatch() {
            return patch;
        }

        public void setPatch(String patch) {
            this.patch = patch;
        }
    }

    /**
     * Sources information nested object.
     */
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class Sources {
        @JsonProperty("dldb")
        private DldbSource dldb;

        public DldbSource getDldb() {
            return dldb;
        }

        public void setDldb(DldbSource dldb) {
            this.dldb = dldb;
        }

        @JsonIgnoreProperties(ignoreUnknown = true)
        public static class DldbSource {
            @JsonProperty("last")
            private String last;

            public String getLast() {
                return last;
            }

            public void setLast(String last) {
                this.last = last;
            }
        }
    }

    /**
     * Mail statistics nested object.
     */
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class MailStats {
        @JsonProperty("queueCount")
        private Integer queueCount;

        @JsonProperty("oldestSeconds")
        private Long oldestSeconds;

        @JsonProperty("newestSeconds")
        private Long newestSeconds;

        public Integer getQueueCount() {
            return queueCount;
        }

        public void setQueueCount(Integer queueCount) {
            this.queueCount = queueCount;
        }

        public Long getOldestSeconds() {
            return oldestSeconds;
        }

        public void setOldestSeconds(Long oldestSeconds) {
            this.oldestSeconds = oldestSeconds;
        }

        public Long getNewestSeconds() {
            return newestSeconds;
        }

        public void setNewestSeconds(Long newestSeconds) {
            this.newestSeconds = newestSeconds;
        }
    }

    /**
     * Notification statistics nested object.
     */
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class NotificationStats {
        @JsonProperty("queueCount")
        private Integer queueCount;

        @JsonProperty("oldestSeconds")
        private Long oldestSeconds;

        @JsonProperty("newestSeconds")
        private Long newestSeconds;

        public Integer getQueueCount() {
            return queueCount;
        }

        public void setQueueCount(Integer queueCount) {
            this.queueCount = queueCount;
        }

        public Long getOldestSeconds() {
            return oldestSeconds;
        }

        public void setOldestSeconds(Long oldestSeconds) {
            this.oldestSeconds = oldestSeconds;
        }

        public Long getNewestSeconds() {
            return newestSeconds;
        }

        public void setNewestSeconds(Long newestSeconds) {
            this.newestSeconds = newestSeconds;
        }
    }

    /**
     * Database statistics nested object.
     */
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class DatabaseStats {
        @JsonProperty("clusterStatus")
        private String clusterStatus;

        @JsonProperty("locks")
        private Integer locks;

        @JsonProperty("logbin")
        private String logbin;

        @JsonProperty("nodeConnections")
        private Double nodeConnections; // Percentage (0.0-1.0 or 0-100)

        @JsonProperty("problems")
        private String problems;

        @JsonProperty("threads")
        private Integer threads;

        public String getClusterStatus() {
            return clusterStatus;
        }

        public void setClusterStatus(String clusterStatus) {
            this.clusterStatus = clusterStatus;
        }

        public Integer getLocks() {
            return locks;
        }

        public void setLocks(Integer locks) {
            this.locks = locks;
        }

        public String getLogbin() {
            return logbin;
        }

        public void setLogbin(String logbin) {
            this.logbin = logbin;
        }

        public Double getNodeConnections() {
            return nodeConnections;
        }

        public void setNodeConnections(Double nodeConnections) {
            this.nodeConnections = nodeConnections;
        }

        public String getProblems() {
            return problems;
        }

        public void setProblems(String problems) {
            this.problems = problems;
        }

        public Integer getThreads() {
            return threads;
        }

        public void setThreads(Integer threads) {
            this.threads = threads;
        }
    }
}
