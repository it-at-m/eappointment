package de.muenchen.zms.citizen.thinnedprocess.service;

import de.muenchen.zms.citizen.thinnedprocess.validation.ThinnedProcessValidationService;
import de.muenchen.zms.citizen.thinnedprocess.view.ThinnedProcessView;
import org.springframework.security.core.Authentication;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** today: zmscitizenapi\\Services\\Appointment\\AppointmentByIdService */
@Service
public class ThinnedProcessFetchService {

    private final ThinnedProcessValidationService validationService;
    private final ThinnedProcessAccessService accessService;
    ThinnedProcessFetchService(ThinnedProcessValidationService validationService, ThinnedProcessAccessService accessService) {
        this.validationService = validationService;
        this.accessService = accessService;
    }

    @Transactional(readOnly = true)
    public ThinnedProcessView getById(Long processId, String authKey, Authentication authentication) {
        boolean authenticated = authentication != null && authentication.isAuthenticated();
        validationService.validateGetById(processId, authKey, authenticated);
        ThinnedProcessView view = accessService.requireThinnedProcess(processId, authKey, authentication);
        // today: zmscitizenapi\\Services\\Captcha\\CaptchaService::generateToken
        return view.withCaptchaToken(java.util.UUID.randomUUID().toString());
    }
}
