package de.muenchen.zms.citizen.thinnedprocess.service;

import de.muenchen.zms.citizen.thinnedprocess.view.ThinnedProcessView;
import org.springframework.security.core.Authentication;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** today: zmscitizenapi\\Services\\Appointment\\ThinnedProcessUpdateService */
@Service
public class ThinnedProcessUpdateService {

    private final ThinnedProcessAccessService accessService;
    private final ThinnedProcessWriteSupport writeSupport;
    private final ThinnedProcessAssembler assembler;

    ThinnedProcessUpdateService(
            ThinnedProcessAccessService accessService,
            ThinnedProcessWriteSupport writeSupport,
            ThinnedProcessAssembler assembler) {
        this.accessService = accessService;
        this.writeSupport = writeSupport;
        this.assembler = assembler;
    }

    @Transactional
    public ThinnedProcessView update(ThinnedProcessView body, Authentication authentication) {
        accessService.requireThinnedProcess(
                body.processId() != null ? body.processId().longValue() : null,
                body.authKey(),
                authentication);
        var process = writeSupport.requireForUpdate(body.processId().longValue());
        if (body.familyName() != null) {
            process.setFamilyName(body.familyName());
        }
        if (body.email() != null) {
            process.setEmail(body.email());
        }
        if (body.telephone() != null) {
            process.setTelephone(body.telephone());
        }
        writeSupport.markReserved(process);
        return assembler.toView(
                new de.muenchen.zms.citizen.thinnedprocess.repository.ThinnedProcessProjection(
                        process.getId(),
                        process.getAuthKey(),
                        process.getFamilyName(),
                        process.getEmail(),
                        process.getTelephone(),
                        process.getAppointmentDate(),
                        process.getAppointmentTime(),
                        process.getSlotCount(),
                        process.getDisplayNumber(),
                        process.getStatus(),
                        process.getScopeId(),
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
                        0));
    }
}
