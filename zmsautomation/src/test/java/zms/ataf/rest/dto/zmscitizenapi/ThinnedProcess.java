package zms.ataf.rest.dto.zmscitizenapi;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

/**
 * Response data for reserve, preconfirm, and confirm appointment endpoints.
 * Used to chain processId and authKey and to assert status and officeId (landing office).
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class ThinnedProcess {

    @JsonProperty("processId")
    private Integer processId;

    @JsonProperty("id")
    private Integer id;

    @JsonProperty("authKey")
    private String authKey;

    @JsonProperty("status")
    private String status;

    @JsonProperty("officeId")
    private Integer officeId;

    @JsonProperty("scope")
    private Object scope;

    @JsonProperty("queue")
    private Object queue;

    public Integer getProcessId() {
        return processId != null ? processId : id;
    }

    public void setProcessId(Integer processId) {
        this.processId = processId;
    }

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public String getAuthKey() {
        return authKey;
    }

    public void setAuthKey(String authKey) {
        this.authKey = authKey;
    }

    public String getStatus() {
        return status;
    }

    public void setStatus(String status) {
        this.status = status;
    }

    public Integer getOfficeId() {
        return officeId;
    }

    public void setOfficeId(Integer officeId) {
        this.officeId = officeId;
    }

    public Object getScope() {
        return scope;
    }

    public void setScope(Object scope) {
        this.scope = scope;
    }

    public Object getQueue() {
        return queue;
    }

    public void setQueue(Object queue) {
        this.queue = queue;
    }
}
