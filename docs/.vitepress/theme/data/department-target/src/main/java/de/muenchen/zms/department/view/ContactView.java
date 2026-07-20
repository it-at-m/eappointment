package de.muenchen.zms.department.view;

import com.fasterxml.jackson.annotation.JsonInclude;

@JsonInclude(JsonInclude.Include.NON_NULL)
public record ContactView(String country, String name, String street, String city) {}
