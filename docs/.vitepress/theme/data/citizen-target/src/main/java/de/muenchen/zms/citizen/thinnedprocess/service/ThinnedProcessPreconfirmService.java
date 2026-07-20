package de.muenchen.zms.citizen.thinnedprocess.service;

import de.muenchen.zms.citizen.thinnedprocess.view.ThinnedProcessView;
import org.springframework.security.core.Authentication;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** today: zmscitizenapi\\Services\\Appointment\\ThinnedProcessPreconfirmService */
@Service
public class ThinnedProcessPreconfirmService {

    private final ThinnedProcessAccessService accessService;
    private final ThinnedProcessWriteSupport writeSupport;
    private final ThinnedProcessNotificationSupport notifications;

    ThinnedProcessPreconfirmService(
            ThinnedProcessAccessService accessService,
            ThinnedProcessWriteSupport writeSupport,
            ThinnedProcessNotificationSupport notifications) {
        this.accessService = accessService;
        this.writeSupport = writeSupport;
        this.notifications = notifications;
    }

    @Transactional
    public ThinnedProcessView preconfirm(Long processId, String authKey, Authentication authentication) {
        ThinnedProcessView current = accessService.requireThinnedProcess(processId, authKey, authentication);
        var process = writeSupport.requireForUpdate(processId);
        writeSupport.markPreconfirmed(process);
        notifications.sendPreconfirmationEmail(current);
        return accessService.requireThinnedProcess(processId, authKey, authentication);
    }
}
