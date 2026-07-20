package de.muenchen.zms.citizen.thinnedprocess.repository;

import de.muenchen.zms.citizen.thinnedprocess.model.ThinnedProcessRequestRecord;
import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

/** today: zmsbackend\\Request\\Repository\\Request */
public interface ThinnedProcessRequestRepository extends JpaRepository<ThinnedProcessRequestRecord, Long> {

    List<ThinnedProcessRequestRecord> findByProcessIdOrderById(Long processId);

    @Query("""
        SELECT pr.requestId AS requestId, COUNT(pr) AS cnt
        FROM ThinnedProcessRequestRecord pr
        WHERE pr.processId = :processId
        GROUP BY pr.requestId
        """)
    List<RequestCountProjection> countByRequestId(@Param("processId") Long processId);

    interface RequestCountProjection {
        Integer getRequestId();
        Long getCnt();
    }
}
