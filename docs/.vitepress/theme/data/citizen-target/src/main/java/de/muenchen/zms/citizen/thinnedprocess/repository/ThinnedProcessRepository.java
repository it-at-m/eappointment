package de.muenchen.zms.citizen.thinnedprocess.repository;

import de.muenchen.zms.citizen.thinnedprocess.model.ThinnedProcessRecord;
import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;

/** today: zmsbackend\\Process\\Service\\Process + zmsbackend\\Process\\Repository\\Process */
public interface ThinnedProcessRepository extends JpaRepository<ThinnedProcessRecord, Long> {

    Optional<ThinnedProcessRecord> findByIdAndAuthKey(Long id, String authKey);

    Optional<ThinnedProcessRecord> findByIdAndExternalUserId(Long id, String externalUserId);
}
