package de.muenchen.zms.citizen.thinnedprocess.view;

import com.fasterxml.jackson.annotation.JsonInclude;

/** today: subRequestCounts[] entry in {@code citizenapi/thinnedProcess.json} */
@JsonInclude(JsonInclude.Include.NON_NULL)
public record SubRequestCountView(Integer id, String name, Integer count) {}
