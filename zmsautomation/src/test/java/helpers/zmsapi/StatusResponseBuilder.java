package helpers.zmsapi;

import java.time.Instant;
import java.time.ZoneOffset;
import java.time.format.DateTimeFormatter;

import dto.zmsapi.StatusResponse;

/**
 * Builder for StatusResponse test data.
 */
public class StatusResponseBuilder {
    private String generated;
    private String server;
    private StatusResponse.ProcessStats processes;
    private StatusResponse.Version version;
    private StatusResponse.Sources sources;
    private StatusResponse.MailStats mail;
    private StatusResponse.NotificationStats notification;
    private StatusResponse.DatabaseStats database;

    public StatusResponseBuilder() {
        // Set default values
        this.generated = DateTimeFormatter.ISO_OFFSET_DATE_TIME
            .format(Instant.now().atOffset(ZoneOffset.UTC));
        this.server = "test-server";
    }

    public StatusResponseBuilder withGenerated(String generated) {
        this.generated = generated;
        return this;
    }

    public StatusResponseBuilder withServer(String server) {
        this.server = server;
        return this;
    }

    public StatusResponseBuilder withProcesses(StatusResponse.ProcessStats processes) {
        this.processes = processes;
        return this;
    }

    public StatusResponseBuilder withVersion(StatusResponse.Version version) {
        this.version = version;
        return this;
    }

    public StatusResponseBuilder withVersion(String major, String minor, String patch) {
        StatusResponse.Version version = new StatusResponse.Version();
        version.setMajor(major);
        version.setMinor(minor);
        version.setPatch(patch);
        this.version = version;
        return this;
    }

    public StatusResponseBuilder withVersion(int major, int minor, String patch) {
        return withVersion(String.valueOf(major), String.valueOf(minor), patch);
    }

    public StatusResponseBuilder withSources(StatusResponse.Sources sources) {
        this.sources = sources;
        return this;
    }

    public StatusResponseBuilder withMail(StatusResponse.MailStats mail) {
        this.mail = mail;
        return this;
    }

    public StatusResponseBuilder withNotification(StatusResponse.NotificationStats notification) {
        this.notification = notification;
        return this;
    }

    public StatusResponseBuilder withDatabase(StatusResponse.DatabaseStats database) {
        this.database = database;
        return this;
    }

    public StatusResponse build() {
        StatusResponse response = new StatusResponse();
        response.setGenerated(generated);
        response.setServer(server);
        response.setProcesses(processes);
        response.setVersion(version);
        response.setSources(sources);
        response.setMail(mail);
        response.setNotification(notification);
        response.setDatabase(database);
        return response;
    }
}
