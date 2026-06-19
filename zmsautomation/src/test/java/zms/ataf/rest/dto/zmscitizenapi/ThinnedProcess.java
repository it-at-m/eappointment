package zms.ataf.rest.dto.zmscitizenapi;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import lombok.Data;

/**
 * Response data for reserve, preconfirm, and confirm appointment endpoints.
 * Used to chain processId and authKey and to assert status and officeId (landing office).
 */
@Data
@JsonIgnoreProperties(ignoreUnknown = true)
public class ThinnedProcess {

    private Integer processId;
    private Integer id;
    private String authKey;
    private String status;
    private Integer officeId;
    private Long timestamp;
    private Integer serviceId;
    private String serviceName;
    private Object scope;
    private Integer slotCount;
    private String displayNumber;
    private String captchaToken;
    private String icsContent;
    private Object queue;

    public Integer getProcessId() {
        return processId != null ? processId : id;
    }
}
