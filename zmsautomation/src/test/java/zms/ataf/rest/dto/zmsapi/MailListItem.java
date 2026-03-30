package zms.ataf.rest.dto.zmsapi;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

/**
 * Minimal mail entity from zmsapi GET /mails/ for finding preconfirmation mail by process.id.
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class MailListItem {

    @JsonProperty("id")
    private Integer id;

    @JsonProperty("process")
    private MailProcessRef process;

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public MailProcessRef getProcess() {
        return process;
    }

    public void setProcess(MailProcessRef process) {
        this.process = process;
    }
}
