package de.muenchen.zms.department.repository;

import de.muenchen.zms.department.model.Department;
import de.muenchen.zms.department.view.DepartmentView;
import java.util.List;
import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

/** today: zmsbackend\\Department\\Service\\Department + zmsbackend\\Department\\Repository\\Department */
public interface DepartmentRepository extends JpaRepository<Department, Long> {

    @Query("""
        SELECT new de.muenchen.zms.department.view.DepartmentView(
            d.id, d.name, d.address, d.contactName,
            e.senderAddress, e.sendReminderEnabled, e.sendReminderMinutesBefore)
        FROM Department d
        LEFT JOIN DepartmentEmail e ON e.departmentId = d.id
        WHERE d.id = :id
        """)
    Optional<DepartmentView> findViewById(@Param("id") Long id);

    @Query("""
        SELECT new de.muenchen.zms.department.view.DepartmentView(
            d.id, d.name, d.address, d.contactName,
            e.senderAddress, e.sendReminderEnabled, e.sendReminderMinutesBefore)
        FROM Department d
        LEFT JOIN DepartmentEmail e ON e.departmentId = d.id
        ORDER BY d.name
        """)
    List<DepartmentView> findAllViews();

    @Query("""
        SELECT new de.muenchen.zms.department.view.DepartmentView(
            d.id, d.name, d.address, d.contactName,
            e.senderAddress, e.sendReminderEnabled, e.sendReminderMinutesBefore)
        FROM Department d
        LEFT JOIN DepartmentEmail e ON e.departmentId = d.id
        JOIN Scope s ON s.departmentId = d.id
        WHERE s.id = :scopeId
        """)
    Optional<DepartmentView> findViewByScopeId(@Param("scopeId") Long scopeId);

    List<Department> findByOrganisationId(Long organisationId);
}
