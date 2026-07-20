package de.muenchen.zms.citizen.thinnedprocess.service;

import de.muenchen.zms.citizen.thinnedprocess.exception.ThinnedProcessAccessDeniedException;
import de.muenchen.zms.citizen.thinnedprocess.exception.ThinnedProcessNotFoundException;
import de.muenchen.zms.citizen.thinnedprocess.repository.ThinnedProcessQueryRepository;
import de.muenchen.zms.citizen.thinnedprocess.repository.ThinnedProcessProjection;
import de.muenchen.zms.citizen.thinnedprocess.view.ThinnedProcessView;
import org.springframework.security.core.Authentication;
import org.springframework.security.oauth2.jwt.Jwt;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/**
 * Resolves process by authKey or authenticated external user id.
 * today: zmscitizenapi\\Services\\Core\\ZmsApiFacadeService::getProcessById
 */
@Service
public class ThinnedProcessAccessService {

    private final ThinnedProcessQueryRepository queryRepository;
    private final ThinnedProcessAssembler assembler;

    ThinnedProcessAccessService(ThinnedProcessQueryRepository queryRepository, ThinnedProcessAssembler assembler) {
        this.queryRepository = queryRepository;
        this.assembler = assembler;
    }

    @Transactional(readOnly = true)
    public ThinnedProcessView requireThinnedProcess(
            Long processId, String authKey, Authentication authentication) {
        String externalUserId = resolveExternalUserId(authKey, authentication);
        ThinnedProcessProjection row =
                queryRepository
                        .findThinnedByIdAndAccess(processId, authKey, externalUserId)
                        .orElseThrow(() -> new ThinnedProcessNotFoundException(processId));
        return assembler.toView(row);
    }

    private static String resolveExternalUserId(String authKey, Authentication authentication) {
        if (authKey != null && !authKey.isBlank()) {
            return null;
        }
        if (authentication == null || !authentication.isAuthenticated()) {
            throw new ThinnedProcessAccessDeniedException("authKey or authenticated session required.");
        }
        if (authentication.getPrincipal() instanceof Jwt jwt) {
            return jwt.getClaimAsString("sub");
        }
        return authentication.getName();
    }
}
