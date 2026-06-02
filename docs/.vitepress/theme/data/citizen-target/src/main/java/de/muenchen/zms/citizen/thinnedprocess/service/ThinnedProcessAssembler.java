package de.muenchen.zms.citizen.thinnedprocess.service;

import de.muenchen.zms.citizen.thinnedprocess.repository.ThinnedProcessRequestRepository;
import de.muenchen.zms.citizen.thinnedprocess.repository.ThinnedProcessProjection;
import de.muenchen.zms.citizen.thinnedprocess.view.SubRequestCountView;
import de.muenchen.zms.citizen.thinnedprocess.view.ThinnedProcessView;
import de.muenchen.zms.citizen.thinnedprocess.view.ThinnedProviderView;
import de.muenchen.zms.citizen.thinnedprocess.view.ThinnedScopeView;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.List;
import org.springframework.stereotype.Component;

/**
 * Maps DB projection/records to {@link ThinnedProcessView}.
 * today: zmscitizenapi\\Services\\Core\\MapperService::processToThinnedProcess
 */
@Component
public class ThinnedProcessAssembler {

    private static final DateTimeFormatter TIMESTAMP =
            DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss");

    private final ThinnedProcessRequestRepository requestRepository;

    ThinnedProcessAssembler(ThinnedProcessRequestRepository requestRepository) {
        this.requestRepository = requestRepository;
    }

    public ThinnedProcessView toView(ThinnedProcessProjection row) {
        if (row == null) {
            return null;
        }
        List<SubRequestCountView> subCounts = loadSubRequestCounts(row.processId(), row.mainServiceId());
        String timestamp = formatTimestamp(row);
        ThinnedScopeView scope = buildScope(row);

        return new ThinnedProcessView(
                row.processId() != null ? row.processId().intValue() : null,
                timestamp,
                row.authKey(),
                row.familyName(),
                row.email(),
                row.telephone(),
                row.officeName(),
                row.providerId(),
                scope,
                subCounts,
                row.mainServiceId(),
                row.mainServiceName(),
                row.mainServiceCount() != null ? row.mainServiceCount() : 0,
                row.status(),
                null,
                row.slotCount(),
                row.displayNumber(),
                null);
    }

    private List<SubRequestCountView> loadSubRequestCounts(Long processId, Integer mainServiceId) {
        if (processId == null) {
            return List.of();
        }
        List<SubRequestCountView> result = new ArrayList<>();
        for (ThinnedProcessRequestRepository.RequestCountProjection count :
                requestRepository.countByRequestId(processId)) {
            if (mainServiceId != null && mainServiceId.equals(count.getRequestId())) {
                continue;
            }
            result.add(new SubRequestCountView(count.getRequestId(), null, count.getCnt().intValue()));
        }
        return result;
    }

    private static String formatTimestamp(ThinnedProcessProjection row) {
        if (row.appointmentDate() == null) {
            return null;
        }
        var dateTime = row.appointmentDate().atTime(row.appointmentTime() != null ? row.appointmentTime() : java.time.LocalTime.MIDNIGHT);
        return TIMESTAMP.format(dateTime);
    }

    private static ThinnedScopeView buildScope(ThinnedProcessProjection row) {
        if (row.scopeId() == null) {
            return null;
        }
        ThinnedProviderView provider = null;
        if (row.providerId() != null) {
            provider = new ThinnedProviderView(
                    row.providerId(),
                    row.providerName(),
                    row.providerDisplayName(),
                    row.providerSource(),
                    row.providerLat(),
                    row.providerLon());
        }
        return new ThinnedScopeView(
                row.scopeId().intValue(),
                provider,
                row.scopeShortName(),
                row.scopeEmailFrom(),
                row.scopeEmailRequired(),
                row.scopeTelephoneActivated(),
                row.scopeTelephoneRequired(),
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null);
    }
}
