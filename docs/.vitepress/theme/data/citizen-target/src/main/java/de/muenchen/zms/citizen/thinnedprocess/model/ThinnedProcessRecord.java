package de.muenchen.zms.citizen.thinnedprocess.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import java.time.LocalDate;
import java.time.LocalTime;

/**
 * JPA entity for the process (appointment) row.
 * today: zmsdb\\Query\\Process::TABLE = {@code buerger}
 * future rename: {@code process} per database refactor (citizen table)
 */
@Entity
@Table(name = "buerger")
public class ThinnedProcessRecord {

    @Id
    @Column(name = "BuergerID") // future: citizen_id
    private Long id;

    @Column(name = "StandortID") // future: scope_id
    private Long scopeId;

    @Column(name = "absagecode") // today: auth key
    private String authKey;

    @Column(name = "Name") // future: name (client family name on main row today)
    private String familyName;

    @Column(name = "EMail") // future: email
    private String email;

    @Column(name = "Telefon") // future: phone
    private String telephone;

    @Column(name = "Datum") // appointment date
    private LocalDate appointmentDate;

    @Column(name = "Uhrzeit") // appointment time
    private LocalTime appointmentTime;

    @Column(name = "AnzahlTermine") // future: slot_count
    private Integer slotCount;

    @Column(name = "wartenr") // future: queue_number
    private String queueNumber;

    @Column(name = "wartenrdatum")
    private LocalDate queueNumberDate;

    @Column(name = "status")
    private String status;

    @Column(name = "vorlaeufigeBuchung") // future: provisional_booking
    private Boolean provisionalBooking;

    @Column(name = "bestaetigt") // future: confirmed
    private Boolean confirmed;

    @Column(name = "displayNumber") // future: display_number
    private String displayNumber;

    @Column(name = "external_user_id")
    private String externalUserId;

    @Column(name = "custom_text_field")
    private String customTextfield;

    @Column(name = "custom_text_field2")
    private String customTextfield2;

    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }
    public Long getScopeId() { return scopeId; }
    public void setScopeId(Long scopeId) { this.scopeId = scopeId; }
    public String getAuthKey() { return authKey; }
    public void setAuthKey(String authKey) { this.authKey = authKey; }
    public String getFamilyName() { return familyName; }
    public void setFamilyName(String familyName) { this.familyName = familyName; }
    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }
    public String getTelephone() { return telephone; }
    public void setTelephone(String telephone) { this.telephone = telephone; }
    public LocalDate getAppointmentDate() { return appointmentDate; }
    public void setAppointmentDate(LocalDate appointmentDate) { this.appointmentDate = appointmentDate; }
    public LocalTime getAppointmentTime() { return appointmentTime; }
    public void setAppointmentTime(LocalTime appointmentTime) { this.appointmentTime = appointmentTime; }
    public Integer getSlotCount() { return slotCount; }
    public void setSlotCount(Integer slotCount) { this.slotCount = slotCount; }
    public String getQueueNumber() { return queueNumber; }
    public void setQueueNumber(String queueNumber) { this.queueNumber = queueNumber; }
    public LocalDate getQueueNumberDate() { return queueNumberDate; }
    public void setQueueNumberDate(LocalDate queueNumberDate) { this.queueNumberDate = queueNumberDate; }
    public String getStatus() { return status; }
    public void setStatus(String status) { this.status = status; }
    public Boolean getProvisionalBooking() { return provisionalBooking; }
    public void setProvisionalBooking(Boolean provisionalBooking) { this.provisionalBooking = provisionalBooking; }
    public Boolean getConfirmed() { return confirmed; }
    public void setConfirmed(Boolean confirmed) { this.confirmed = confirmed; }
    public String getDisplayNumber() { return displayNumber; }
    public void setDisplayNumber(String displayNumber) { this.displayNumber = displayNumber; }
    public String getExternalUserId() { return externalUserId; }
    public void setExternalUserId(String externalUserId) { this.externalUserId = externalUserId; }
    public String getCustomTextfield() { return customTextfield; }
    public void setCustomTextfield(String customTextfield) { this.customTextfield = customTextfield; }
    public String getCustomTextfield2() { return customTextfield2; }
    public void setCustomTextfield2(String customTextfield2) { this.customTextfield2 = customTextfield2; }
}
