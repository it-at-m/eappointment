package de.muenchen.zms.citizen.thinnedprocess.repository;

import java.time.LocalDate;
import java.time.LocalTime;

/**
 * Flat query projection for thinned process assembly — not a JPA entity.
 * Loaded by {@link ThinnedProcessQueryRepository} instead of a full Process graph.
 */
public record ThinnedProcessProjection(
        Long processId,
        String authKey,
        String familyName,
        String email,
        String telephone,
        LocalDate appointmentDate,
        LocalTime appointmentTime,
        Integer slotCount,
        String displayNumber,
        String status,
        Long scopeId,
        String scopeShortName,
        String scopeEmailFrom,
        Boolean scopeEmailRequired,
        Boolean scopeTelephoneActivated,
        Boolean scopeTelephoneRequired,
        Integer providerId,
        String providerName,
        String providerDisplayName,
        String providerSource,
        Double providerLat,
        Double providerLon,
        String officeName,
        Integer mainServiceId,
        String mainServiceName,
        Integer mainServiceCount) {}
