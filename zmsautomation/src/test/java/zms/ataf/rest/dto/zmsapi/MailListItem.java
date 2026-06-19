package zms.ataf.rest.dto.zmsapi;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

/**
 * Minimal mail entity from zmsapi GET /mails/ for finding preconfirmation mail by process.id.
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public record MailListItem(Integer id, MailProcessRef process) {}
