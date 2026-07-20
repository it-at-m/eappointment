package de.muenchen.zms.department.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.Id;
import jakarta.persistence.Table;

@Entity
@Table(name = "email")
public class DepartmentEmail {

    @Id
    @Column(name = "email_id") // today: emailID
    private Long id;

    @Column(name = "department_id") // today: BehoerdenID
    private Long departmentId;

    @Column(name = "sender_address") // today: absenderadresse
    private String senderAddress;

    @Column(name = "send_reminder") // today: send_reminder
    private Boolean sendReminderEnabled;

    @Column(name = "send_reminder_minutes_before")
    private Integer sendReminderMinutesBefore;

    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }
    public Long getDepartmentId() { return departmentId; }
    public void setDepartmentId(Long departmentId) { this.departmentId = departmentId; }
    public String getSenderAddress() { return senderAddress; }
    public void setSenderAddress(String senderAddress) { this.senderAddress = senderAddress; }
    public Boolean getSendReminderEnabled() { return sendReminderEnabled; }
    public void setSendReminderEnabled(Boolean sendReminderEnabled) { this.sendReminderEnabled = sendReminderEnabled; }
    public Integer getSendReminderMinutesBefore() { return sendReminderMinutesBefore; }
    public void setSendReminderMinutesBefore(Integer minutes) { this.sendReminderMinutesBefore = minutes; }
}
