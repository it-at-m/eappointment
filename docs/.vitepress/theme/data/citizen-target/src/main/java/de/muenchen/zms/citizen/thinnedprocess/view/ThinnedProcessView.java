package de.muenchen.zms.citizen.thinnedprocess.view;

import com.fasterxml.jackson.annotation.JsonInclude;
import java.util.List;

/**
 * Citizen API response for appointment/process operations.
 * today: zmscitizenapi\\Models\\ThinnedProcess
 */
@JsonInclude(JsonInclude.Include.NON_NULL)
public record ThinnedProcessView(
        Integer processId,
        String timestamp,
        String authKey,
        String familyName,
        String email,
        String telephone,
        String officeName,
        Integer officeId,
        ThinnedScopeView scope,
        List<SubRequestCountView> subRequestCounts,
        Integer serviceId,
        String serviceName,
        Integer serviceCount,
        String status,
        String captchaToken,
        Integer slotCount,
        String displayNumber,
        String icsContent) {

    public ThinnedProcessView withCaptchaToken(String token) {
        return new ThinnedProcessView(
                processId,
                timestamp,
                authKey,
                familyName,
                email,
                telephone,
                officeName,
                officeId,
                scope,
                subRequestCounts,
                serviceId,
                serviceName,
                serviceCount,
                status,
                token,
                slotCount,
                displayNumber,
                icsContent);
    }
}
