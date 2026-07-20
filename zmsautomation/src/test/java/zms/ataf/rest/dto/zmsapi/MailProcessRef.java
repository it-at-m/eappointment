package zms.ataf.rest.dto.zmsapi;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

/**
 * Minimal process reference in a zmsapi mail entity (process.id, process.authKey).
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public record MailProcessRef(Integer id, String authKey) {}
