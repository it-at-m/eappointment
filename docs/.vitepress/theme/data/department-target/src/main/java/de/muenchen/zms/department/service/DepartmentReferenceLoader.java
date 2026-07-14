package de.muenchen.zms.department.service;

import de.muenchen.zms.department.repository.DepartmentClusterRepository;
import de.muenchen.zms.department.repository.DepartmentDayoffRepository;
import de.muenchen.zms.department.repository.DepartmentLinkRepository;
import de.muenchen.zms.department.repository.DepartmentScopeRepository;
import de.muenchen.zms.department.view.DepartmentView;
import de.muenchen.zms.department.view.ReferenceViews.ClusterReferenceView;
import de.muenchen.zms.department.view.ReferenceViews.DayoffView;
import de.muenchen.zms.department.view.ReferenceViews.LinkView;
import de.muenchen.zms.department.view.ReferenceViews.ScopeReferenceView;
import java.util.List;
import org.springframework.stereotype.Component;

/** today: zmsbackend\\Department\\Service\\Department::readResolvedReferences */
@Component
public class DepartmentReferenceLoader {

    private final DepartmentLinkRepository linkRepository;
    private final DepartmentScopeRepository scopeRepository;
    private final DepartmentClusterRepository clusterRepository;
    private final DepartmentDayoffRepository dayoffRepository;

    DepartmentReferenceLoader(
            DepartmentLinkRepository linkRepository,
            DepartmentScopeRepository scopeRepository,
            DepartmentClusterRepository clusterRepository,
            DepartmentDayoffRepository dayoffRepository) {
        this.linkRepository = linkRepository;
        this.scopeRepository = scopeRepository;
        this.clusterRepository = clusterRepository;
        this.dayoffRepository = dayoffRepository;
    }

    public DepartmentView withReferences(DepartmentView view, int resolveReferences) {
        if (resolveReferences <= 0) {
            return view;
        }
        List<ScopeReferenceView> scopes =
                scopeRepository.findReferencesByDepartmentId(view.id());
        List<ClusterReferenceView> clusters = List.of();
        List<DayoffView> dayoffs = List.of();
        if (resolveReferences > 0) {
            clusters = clusterRepository.findByDepartmentId(view.id());
            dayoffs = dayoffRepository.findByDepartmentId(view.id());
        }
        List<LinkView> links = linkRepository.findByDepartmentId(view.id());
        return view.withReferences(scopes, clusters, links, dayoffs);
    }
}
