package de.muenchen.zms.citizen.thinnedprocess.service;

import de.muenchen.zms.citizen.thinnedprocess.view.ThinnedProcessView;
import org.springframework.security.core.Authentication;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** today: zmscitizenapi\\Services\\Appointment\\ThinnedProcessCancelService */
@Service
public class ThinnedProcessCancelService {

    private final ThinnedProcessAccessService accessService;
    private final ThinnedProcessWriteSupport writeSupport;

    ThinnedProcessCancelService(ThinnedProcessAccessService accessService, ThinnedProcessWriteSupport writeSupport) {
        this.accessService = accessService;
        this.writeSupport = writeSupport;
    }

    @Transactional
    public ThinnedProcessView cancel(Long processId, String authKey, Authentication authentication) {
        accessService.requireThinnedProcess(processId, authKey, authentication);
        var process = writeSupport.requireForUpdate(processId);
        writeSupport.markCancelled(process);
        return accessService.requireThinnedProcess(processId, authKey, authentication);
    }
}
