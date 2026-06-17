package zms.ataf.rest.dto.zmscitizenapi;

import java.util.List;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import lombok.Data;

/**
 * Request body for POST /reserve-appointment/.
 * timestamp: Unix seconds; officeId: int; serviceId: array; serviceCount: array (default [1]).
 */
@Data
@JsonIgnoreProperties(ignoreUnknown = true)
public class ReserveAppointmentRequest {

    private Long timestamp;
    private Integer officeId;
    private List<Integer> serviceId;
    private List<Integer> serviceCount;
    private String captchaToken;
}
