package de.muenchen.zms.citizen.thinnedprocess.repository;

import java.util.List;
import java.util.Optional;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.Repository;
import org.springframework.data.repository.query.Param;

/**
 * Focused reads joining buerger, standort, provider — no giant Process entity.
 * today: ZmsApiClientService::getProcessById + MapperService::processToThinnedProcess
 */
public interface ThinnedProcessQueryRepository extends Repository<ThinnedProcessProjection, Long> {

    @Query(
            value =
                    """
            SELECT
                p.BuergerID AS processId,
                p.absagecode AS authKey,
                p.Name AS familyName,
                p.EMail AS email,
                p.Telefon AS telephone,
                p.Datum AS appointmentDate,
                p.Uhrzeit AS appointmentTime,
                p.AnzahlTermine AS slotCount,
                p.displayNumber AS displayNumber,
                p.status AS status,
                s.StandortID AS scopeId,
                s.kurzname AS scopeShortName,
                s.email AS scopeEmailFrom,
                s.emailpflicht AS scopeEmailRequired,
                s.telefonaktiviert AS scopeTelephoneActivated,
                s.telefonpflicht AS scopeTelephoneRequired,
                pr.id AS providerId,
                pr.name AS providerName,
                pr.display_name AS providerDisplayName,
                pr.source AS providerSource,
                pr.lat AS providerLat,
                pr.lon AS providerLon,
                pr.name AS officeName,
                MIN(ba.Anliegen) AS mainServiceId,
                NULL AS mainServiceName,
                COUNT(ba.BuergeranliegenID) AS mainServiceCount
            FROM buerger p
            JOIN standort s ON s.StandortID = p.StandortID
            JOIN provider pr ON pr.id = s.provider_id AND pr.source = s.source
            LEFT JOIN buergeranliegen ba ON ba.BuergerID = p.BuergerID
            WHERE p.BuergerID = :processId
              AND (p.absagecode = :authKey OR p.external_user_id = :externalUserId)
            GROUP BY p.BuergerID
            """,
            nativeQuery = true)
    Optional<ThinnedProcessProjection> findThinnedByIdAndAccess(
            @Param("processId") Long processId,
            @Param("authKey") String authKey,
            @Param("externalUserId") String externalUserId);

    @Query(
            value =
                    """
            SELECT p.BuergerID AS processId
            FROM buerger p
            WHERE p.external_user_id = :externalUserId
              AND (:filterId IS NULL OR p.StandortID = :filterId)
            ORDER BY p.Datum DESC, p.Uhrzeit DESC
            """,
            nativeQuery = true)
    List<Long> findProcessIdsForExternalUser(
            @Param("externalUserId") String externalUserId, @Param("filterId") Long filterId);
}
