package de.muenchen.zms.citizen.thinnedprocess.service;

import de.muenchen.zms.citizen.thinnedprocess.view.ThinnedProcessView;
import org.springframework.stereotype.Component;

/** today: confirmation/preconfirmation mail from Appointment*Service */
@Component
public class ThinnedProcessNotificationSupport {

    public void sendConfirmationEmail(ThinnedProcessView process) {
        // today: ZmsApiClientService / messaging integration
    }

    public void sendPreconfirmationEmail(ThinnedProcessView process) {
        // today: preconfirm mail path in ThinnedProcessPreconfirmService
    }
}
