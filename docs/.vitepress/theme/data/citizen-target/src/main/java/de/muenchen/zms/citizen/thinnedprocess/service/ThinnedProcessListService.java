package de.muenchen.zms.citizen.thinnedprocess.service;

import de.muenchen.zms.citizen.thinnedprocess.exception.ThinnedProcessAccessDeniedException;
import de.muenchen.zms.citizen.thinnedprocess.repository.ThinnedProcessQueryRepository;
import de.muenchen.zms.citizen.thinnedprocess.view.ThinnedProcessView;
import java.util.ArrayList;
import java.util.List;
import org.springframework.security.core.Authentication;
import org.springframework.security.oauth2.jwt.Jwt;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** today: zmscitizenapi\\Services\\Appointment\\MyAppointmentsService */
@Service
public class ThinnedProcessListService {

    private final ThinnedProcessQueryRepository queryRepository;
    private final ThinnedProcessAccessService accessService;

    ThinnedProcessListService(ThinnedProcessQueryRepository queryRepository, ThinnedProcessAccessService accessService) {
        this.queryRepository = queryRepository;
        this.accessService = accessService;
    }

    @Transactional(readOnly = true)
    public List<ThinnedProcessView> listForUser(Authentication authentication, Long filterId) {
        String externalUserId = requireExternalUserId(authentication);
        List<ThinnedProcessView> result = new ArrayList<>();
        for (Long processId : queryRepository.findProcessIdsForExternalUser(externalUserId, filterId)) {
            result.add(accessService.requireThinnedProcess(processId, null, authentication));
        }
        return result;
    }

    private static String requireExternalUserId(Authentication authentication) {
        if (authentication == null || !authentication.isAuthenticated()) {
            throw new ThinnedProcessAccessDeniedException("Authentication required for my-appointments.");
        }
        if (authentication.getPrincipal() instanceof Jwt jwt) {
            return jwt.getClaimAsString("sub");
        }
        return authentication.getName();
    }
}
