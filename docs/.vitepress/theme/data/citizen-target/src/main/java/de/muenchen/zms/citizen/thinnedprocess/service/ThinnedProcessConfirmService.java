package de.muenchen.zms.citizen.thinnedprocess.service;

import de.muenchen.zms.citizen.thinnedprocess.view.ThinnedProcessView;
import org.springframework.security.core.Authentication;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** today: zmscitizenapi\\Services\\Appointment\\ThinnedProcessConfirmService */
@Service
public class ThinnedProcessConfirmService {

    private final ThinnedProcessAccessService accessService;
    private final ThinnedProcessWriteSupport writeSupport;
    private final ThinnedProcessNotificationSupport notifications;

    ThinnedProcessConfirmService(
            ThinnedProcessAccessService accessService,
            ThinnedProcessWriteSupport writeSupport,
            ThinnedProcessNotificationSupport notifications) {
        this.accessService = accessService;
        this.writeSupport = writeSupport;
        this.notifications = notifications;
    }

    @Transactional
    public ThinnedProcessView confirm(Long processId, String authKey, Authentication authentication) {
        ThinnedProcessView current = accessService.requireThinnedProcess(processId, authKey, authentication);
        var process = writeSupport.requireForUpdate(processId);
        writeSupport.markConfirmed(process);
        notifications.sendConfirmationEmail(current);
        return accessService.requireThinnedProcess(processId, authKey, authentication);
    }
}
