package de.muenchen.zms.department.repository;

import de.muenchen.zms.department.view.ReferenceViews.ClusterReferenceView;
import java.util.List;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.Repository;
import org.springframework.data.repository.query.Param;

/** today: zmsdb\\Cluster::readByDepartmentId, writeEntity */
public interface DepartmentClusterRepository extends Repository<Object, Long> {

    @Query("""
        SELECT new de.muenchen.zms.department.view.ReferenceViews$ClusterReferenceView(c.id)
        FROM Cluster c
        WHERE c.departmentId = :departmentId
        """)
    List<ClusterReferenceView> findByDepartmentId(@Param("departmentId") Long departmentId);

    @Query("""
        SELECT COUNT(c) FROM Cluster c WHERE c.departmentId = :departmentId
        """)
    long countByDepartmentId(@Param("departmentId") Long departmentId);
}
