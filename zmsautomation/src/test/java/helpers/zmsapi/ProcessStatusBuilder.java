package helpers.zmsapi;

import dto.zmsapi.StatusResponse;

/**
 * Builder for ProcessStats test data.
 */
public class ProcessStatusBuilder {
    private Integer blocked = 0;
    private Integer confirmed = 0;
    private Integer deleted = 0;
    private Integer missed = 0;
    private Integer parked = 0;
    private Integer reserved = 0;
    private Integer outdated = 0;
    private String lastCalculate;
    private String lastInsert;
    private String outdatedOldest;

    public ProcessStatusBuilder withBlocked(Integer blocked) {
        this.blocked = blocked;
        return this;
    }

    public ProcessStatusBuilder withConfirmed(Integer confirmed) {
        this.confirmed = confirmed;
        return this;
    }

    public ProcessStatusBuilder withDeleted(Integer deleted) {
        this.deleted = deleted;
        return this;
    }

    public ProcessStatusBuilder withMissed(Integer missed) {
        this.missed = missed;
        return this;
    }

    public ProcessStatusBuilder withParked(Integer parked) {
        this.parked = parked;
        return this;
    }

    public ProcessStatusBuilder withReserved(Integer reserved) {
        this.reserved = reserved;
        return this;
    }

    public ProcessStatusBuilder withOutdated(Integer outdated) {
        this.outdated = outdated;
        return this;
    }

    public ProcessStatusBuilder withLastCalculate(String lastCalculate) {
        this.lastCalculate = lastCalculate;
        return this;
    }

    public ProcessStatusBuilder withLastInsert(String lastInsert) {
        this.lastInsert = lastInsert;
        return this;
    }

    public ProcessStatusBuilder withOutdatedOldest(String outdatedOldest) {
        this.outdatedOldest = outdatedOldest;
        return this;
    }

    public StatusResponse.ProcessStats build() {
        StatusResponse.ProcessStats stats = new StatusResponse.ProcessStats();
        stats.setBlocked(blocked);
        stats.setConfirmed(confirmed);
        stats.setDeleted(deleted);
        stats.setMissed(missed);
        stats.setParked(parked);
        stats.setReserved(reserved);
        stats.setOutdated(outdated);
        stats.setLastCalculate(lastCalculate);
        stats.setLastInsert(lastInsert);
        stats.setOutdatedOldest(outdatedOldest);
        return stats;
    }
}
