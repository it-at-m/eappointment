package zms.ataf.rest.dto.zmsapi;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

/**
 * Minimal process reference in a zmsapi mail entity (process.id, process.authKey).
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class MailProcessRef {

    @JsonProperty("id")
    private Integer id;

    @JsonProperty("authKey")
    private String authKey;

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
}
