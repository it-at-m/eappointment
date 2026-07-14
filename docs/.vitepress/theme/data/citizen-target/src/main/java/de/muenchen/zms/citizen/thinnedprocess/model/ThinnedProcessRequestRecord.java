package de.muenchen.zms.citizen.thinnedprocess.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;

/**
 * Request (service) line on a process.
 * today: zmsbackend\\Request\\Repository\\Request::BATABLE = {@code buergeranliegen}
 * future rename: {@code citizen_requests}
 */
@Entity
@Table(name = "buergeranliegen")
public class ThinnedProcessRequestRecord {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "BuergeranliegenID") // future: citizen_request_id
    private Long id;

    @Column(name = "BuergerID") // future: citizen_id
    private Long processId;

    @Column(name = "Anliegen") // future: request (service id)
    private Integer requestId;

    @Column(name = "source")
    private String source;

    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }
    public Long getProcessId() { return processId; }
    public void setProcessId(Long processId) { this.processId = processId; }
    public Integer getRequestId() { return requestId; }
    public void setRequestId(Integer requestId) { this.requestId = requestId; }
    public String getSource() { return source; }
    public void setSource(String source) { this.source = source; }
}
