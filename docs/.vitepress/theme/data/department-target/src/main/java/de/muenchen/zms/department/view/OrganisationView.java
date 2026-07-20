package de.muenchen.zms.department.view;

import com.fasterxml.jackson.annotation.JsonInclude;
import java.util.List;

@JsonInclude(JsonInclude.Include.NON_NULL)
public record OrganisationView(Long id, String name, List<DepartmentView> departments) {}
