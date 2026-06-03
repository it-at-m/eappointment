package de.muenchen.zms.department.view;

import com.fasterxml.jackson.annotation.JsonInclude;

@JsonInclude(JsonInclude.Include.NON_NULL)
public record ScopeView(Long id, String shortName, ContactView contact) {}
