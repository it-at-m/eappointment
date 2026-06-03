package de.muenchen.zms.department.repository;

import de.muenchen.zms.department.view.ReferenceViews.ScopeReferenceView;
import de.muenchen.zms.department.view.ScopeView;
import java.util.List;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.Repository;
import org.springframework.data.repository.query.Param;

/** today: zmsdb\\Scope::readByDepartmentId, writeEntity */
public interface DepartmentScopeRepository extends Repository<Object, Long> {

    @Query("""
        SELECT new de.muenchen.zms.department.view.ReferenceViews$ScopeReferenceView(
            s.id, s.shortName, CONCAT('/scope/', s.id, '/'))
        FROM Scope s
        WHERE s.departmentId = :departmentId
        ORDER BY s.contactName
        """)
    List<ScopeReferenceView> findReferencesByDepartmentId(@Param("departmentId") Long departmentId);

    @Query("""
        SELECT COUNT(s) FROM Scope s WHERE s.departmentId = :departmentId
        """)
    long countByDepartmentId(@Param("departmentId") Long departmentId);
}
