package de.muenchen.zms.department.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.Id;
import jakarta.persistence.Table;

@Entity
@Table(name = "department") // today: behoerde
public class Department {

    @Id
    @Column(name = "id") // today: BehoerdenID
    private Long id;

    @Column(name = "name", nullable = false) // today: Name
    private String name;

    @Column(name = "address") // today: Adresse
    private String address;

    @Column(name = "contact_name") // today: Ansprechpartner
    private String contactName;

    @Column(name = "organisation_id") // today: OrganisationsID
    private Long organisationId;

    @Column(name = "owner_id") // today: KundenID
    private Long ownerId;

    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }
    public String getName() { return name; }
    public void setName(String name) { this.name = name; }
    public String getAddress() { return address; }
    public void setAddress(String address) { this.address = address; }
    public String getContactName() { return contactName; }
    public void setContactName(String contactName) { this.contactName = contactName; }
    public Long getOrganisationId() { return organisationId; }
    public void setOrganisationId(Long organisationId) { this.organisationId = organisationId; }
    public Long getOwnerId() { return ownerId; }
    public void setOwnerId(Long ownerId) { this.ownerId = ownerId; }
}
