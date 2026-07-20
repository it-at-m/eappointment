package de.muenchen.zms.citizen.thinnedprocess.view;

import com.fasterxml.jackson.annotation.JsonInclude;

/** today: zmscitizenapi\\Models\\ThinnedProvider */
@JsonInclude(JsonInclude.Include.NON_NULL)
public record ThinnedProviderView(
        Integer id,
        String name,
        String displayName,
        String source,
        Double lat,
        Double lon) {}
