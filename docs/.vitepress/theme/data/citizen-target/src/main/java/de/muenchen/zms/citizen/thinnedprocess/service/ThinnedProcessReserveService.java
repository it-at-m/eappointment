package de.muenchen.zms.citizen.thinnedprocess.service;

import de.muenchen.zms.citizen.thinnedprocess.validation.ThinnedProcessValidationService;
import de.muenchen.zms.citizen.thinnedprocess.view.ThinnedProcessView;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** today: zmscitizenapi\\Services\\Appointment\\ThinnedProcessReserveService */
@Service
public class ThinnedProcessReserveService {

    private final ThinnedProcessValidationService validationService;

    ThinnedProcessReserveService(ThinnedProcessValidationService validationService) {
        this.validationService = validationService;
    }

    @Transactional
    public ThinnedProcessView reserve(ThinnedProcessView input, boolean showUnpublished) {
        validationService.validateForReserve(input);
        // today: ZmsApiFacadeService::reserveTimeslot → ThinnedProcessWriteSupport + ThinnedProcessAssembler
        return input;
    }
}
