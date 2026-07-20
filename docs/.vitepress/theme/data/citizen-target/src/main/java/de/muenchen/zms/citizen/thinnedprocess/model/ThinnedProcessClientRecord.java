package de.muenchen.zms.citizen.thinnedprocess.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.Id;
import jakarta.persistence.Table;

/**
 * Illustrative client row when split from {@link ThinnedProcessRecord}.
 * today: client fields live on {@code buerger} (Name, EMail, Telefon)
 */
@Entity
@Table(name = "buerger")
public class ThinnedProcessClientRecord {

    @Id
    @Column(name = "BuergerID")
    private Long processId;

    @Column(name = "Name")
    private String familyName;

    @Column(name = "EMail")
    private String email;

    @Column(name = "Telefon")
    private String telephone;

    public Long getProcessId() { return processId; }
    public void setProcessId(Long processId) { this.processId = processId; }
    public String getFamilyName() { return familyName; }
    public void setFamilyName(String familyName) { this.familyName = familyName; }
    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }
    public String getTelephone() { return telephone; }
    public void setTelephone(String telephone) { this.telephone = telephone; }
}
