package de.muenchen.zms.department.view;

import com.fasterxml.jackson.annotation.JsonInclude;
import de.muenchen.zms.department.view.ReferenceViews.ScopeReferenceView;
import java.util.List;

@JsonInclude(JsonInclude.Include.NON_NULL)
public record ClusterView(Long id, String name, List<ScopeReferenceView> scopes) {}
