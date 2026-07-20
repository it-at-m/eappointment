package de.muenchen.zms.department.service;

import de.muenchen.zms.department.model.Department;
import de.muenchen.zms.department.view.DepartmentView;
import org.springframework.stereotype.Component;

/** today: zmsbackend\\Department\\Service\\Department writeDepartmentLinks/Dayoffs/Mail helpers */
@Component
public class DepartmentWriteSupport {

    public Long resolveOwnerIdForOrganisation(Long organisationId) {
        // today: (new Owner())->readByOrganisationId($parentId)
        return organisationId;
    }

    public void applyView(Department entity, DepartmentView view) {
        entity.setName(view.name());
        if (view.contact() != null) {
            entity.setAddress(view.contact().street());
            entity.setContactName(view.contact().name());
        }
    }

    public void writeNestedEntities(Long departmentId, DepartmentView view) {
        // today: writeDepartmentLinks, writeDepartmentDayoffs, writeDepartmentMail
    }
}
