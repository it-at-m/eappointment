package de.muenchen.zms.department.repository;

import de.muenchen.zms.department.view.WorkstationView;
import java.util.List;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.Repository;
import org.springframework.data.repository.query.Param;

/** today: zmsdb\\Workstation::readCollectionByDepartmentId */
public interface DepartmentWorkstationRepository extends Repository<Object, Long> {

    @Query("""
        SELECT new de.muenchen.zms.department.view.WorkstationView(w.id, w.name)
        FROM Workstation w
        WHERE w.departmentId = :departmentId
        ORDER BY w.name
        """)
    List<WorkstationView> findByDepartmentId(@Param("departmentId") Long departmentId);
}
