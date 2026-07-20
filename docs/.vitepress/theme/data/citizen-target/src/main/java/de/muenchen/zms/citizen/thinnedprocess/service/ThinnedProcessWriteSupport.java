package de.muenchen.zms.citizen.thinnedprocess.service;

import de.muenchen.zms.citizen.thinnedprocess.model.ThinnedProcessRecord;
import de.muenchen.zms.citizen.thinnedprocess.repository.ThinnedProcessRepository;
import org.springframework.stereotype.Component;

/** today: zmsbackend\\Process\\Service\\Process status transitions (reserve, confirm, cancel) */
@Component
public class ThinnedProcessWriteSupport {

    private final ThinnedProcessRepository repository;

    ThinnedProcessWriteSupport(ThinnedProcessRepository repository) {
        this.repository = repository;
    }

    public ThinnedProcessRecord requireForUpdate(Long processId) {
        return repository.findById(processId).orElseThrow();
    }

    public void markReserved(ThinnedProcessRecord process) {
        process.setProvisionalBooking(true);
        process.setConfirmed(false);
        process.setStatus("reserved");
        repository.save(process);
    }

    public void markPreconfirmed(ThinnedProcessRecord process) {
        process.setStatus("preconfirmed");
        repository.save(process);
    }

    public void markConfirmed(ThinnedProcessRecord process) {
        process.setProvisionalBooking(false);
        process.setConfirmed(true);
        process.setStatus("confirmed");
        repository.save(process);
    }

    public void markCancelled(ThinnedProcessRecord process) {
        process.setStatus("deleted");
        repository.save(process);
    }
}
