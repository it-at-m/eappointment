package de.muenchen.zms.department.repository;

import de.muenchen.zms.department.view.ReferenceViews.LinkView;
import java.util.List;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.Repository;
import org.springframework.data.repository.query.Param;

/** today: zmsbackend\\Link\\Service\\Link::readByDepartmentId */
public interface DepartmentLinkRepository extends Repository<Object, Long> {

    @Query("""
        SELECT new de.muenchen.zms.department.view.ReferenceViews$LinkView(
            l.name, l.url, l.target)
        FROM DepartmentLink l
        WHERE l.departmentId = :departmentId
        """)
    List<LinkView> findByDepartmentId(@Param("departmentId") Long departmentId);
}
