package de.muenchen.zms.department.repository;

import de.muenchen.zms.department.view.OrganisationView;
import java.util.Optional;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.Repository;
import org.springframework.data.repository.query.Param;

/** today: zmsdb\\Organisation::readByDepartmentId */
public interface DepartmentOrganisationRepository extends Repository<Object, Long> {

    @Query("""
        SELECT new de.muenchen.zms.department.view.OrganisationView(o.id, o.name, NULL)
        FROM Organisation o
        JOIN Department d ON d.organisationId = o.id
        WHERE d.id = :departmentId
        """)
    Optional<OrganisationView> findByDepartmentId(@Param("departmentId") Long departmentId);
}
