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

    @JsonProperty("timestamp")
    private Long timestamp;

    @JsonProperty("serviceId")
    private Integer serviceId;

    @JsonProperty("serviceName")
    private String serviceName;

    @JsonProperty("scope")
    private Object scope;

    @JsonProperty("slotCount")
    private Integer slotCount;

    @JsonProperty("displayNumber")
    private String displayNumber;

    @JsonProperty("captchaToken")
    private String captchaToken;

    @JsonProperty("icsContent")
    private String icsContent;

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

    public Long getTimestamp() {
        return timestamp;
    }

    public void setTimestamp(Long timestamp) {
        this.timestamp = timestamp;
    }

    public Integer getServiceId() {
        return serviceId;
    }

    public void setServiceId(Integer serviceId) {
        this.serviceId = serviceId;
    }

    public String getServiceName() {
        return serviceName;
    }

    public void setServiceName(String serviceName) {
        this.serviceName = serviceName;
    }

    public Object getScope() {
        return scope;
    }

    public void setScope(Object scope) {
        this.scope = scope;
    }

    public Integer getSlotCount() {
        return slotCount;
    }

    public void setSlotCount(Integer slotCount) {
        this.slotCount = slotCount;
    }

    public String getDisplayNumber() {
        return displayNumber;
    }

    public void setDisplayNumber(String displayNumber) {
        this.displayNumber = displayNumber;
    }

    public String getCaptchaToken() {
        return captchaToken;
    }

    public void setCaptchaToken(String captchaToken) {
        this.captchaToken = captchaToken;
    }

    public String getIcsContent() {
        return icsContent;
    }

    public void setIcsContent(String icsContent) {
        this.icsContent = icsContent;
    }

    public Object getQueue() {
        return queue;
    }

    public void setQueue(Object queue) {
        this.queue = queue;
    }
}
