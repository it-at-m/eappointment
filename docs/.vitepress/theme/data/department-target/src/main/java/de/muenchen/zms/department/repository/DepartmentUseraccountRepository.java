package de.muenchen.zms.department.repository;

import de.muenchen.zms.department.view.UseraccountView;
import java.util.List;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.Repository;
import org.springframework.data.repository.query.Param;

/** today: zmsbackend\\Useraccount\\Service\\Useraccount::readSearchByDepartmentIds, readListByRoleAndDepartmentIds */
public interface DepartmentUseraccountRepository extends Repository<Object, Long> {

    @Query("""
        SELECT DISTINCT new de.muenchen.zms.department.view.UseraccountView(u.id, u.name, NULL)
        FROM Useraccount u
        JOIN u.departmentAssignments da
        WHERE da.departmentId IN :departmentIds
          AND (:query IS NULL OR u.name LIKE CONCAT('%', :query, '%') OR CAST(u.id AS string) = :query)
        ORDER BY u.name
        """)
    List<UseraccountView> searchByDepartmentIds(
            @Param("departmentIds") List<Long> departmentIds, @Param("query") String query);

    @Query("""
        SELECT DISTINCT new de.muenchen.zms.department.view.UseraccountView(u.id, u.name, NULL)
        FROM Useraccount u
        JOIN u.departmentAssignments da
        JOIN u.roles r
        WHERE da.departmentId IN :departmentIds
          AND r.level = :roleLevel
        ORDER BY u.name
        """)
    List<UseraccountView> findByRoleAndDepartmentIds(
            @Param("roleLevel") Long roleLevel, @Param("departmentIds") List<Long> departmentIds);
}
