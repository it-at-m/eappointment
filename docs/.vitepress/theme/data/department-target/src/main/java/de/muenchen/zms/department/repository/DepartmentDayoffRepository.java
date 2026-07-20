package de.muenchen.zms.department.repository;

import de.muenchen.zms.department.view.ReferenceViews.DayoffView;
import java.util.List;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.Repository;
import org.springframework.data.repository.query.Param;

/** today: zmsbackend\\Dayoff\\Service\\DayOff::readOnlyByDepartmentId */
public interface DepartmentDayoffRepository extends Repository<Object, Long> {

    @Query("""
        SELECT new de.muenchen.zms.department.view.ReferenceViews$DayoffView(
            d.dateEpoch, d.name)
        FROM DepartmentDayoff d
        WHERE d.departmentId = :departmentId
        """)
    List<DayoffView> findByDepartmentId(@Param("departmentId") Long departmentId);
}
