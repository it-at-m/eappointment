package de.muenchen.zms.department.service;

import de.muenchen.zms.department.view.ClusterView;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** today: zmsbackend\\Department\\Api\\DepartmentAddCluster, zmsbackend\\Cluster\\Service\\Cluster::writeEntity */
@Service
public class DepartmentClusterCreateService {

    private final DepartmentFetchService fetchService;

    DepartmentClusterCreateService(DepartmentFetchService fetchService) {
        this.fetchService = fetchService;
    }

    @Transactional
    public ClusterView addCluster(Long departmentId, ClusterView input) {
        fetchService.getById(departmentId, 1);
        // today: validate cluster JSON against zmsentities/schema/cluster.json, then persist
        return input;
    }
}
