package de.muenchen.zms.department.service;

import de.muenchen.zms.department.repository.DepartmentWorkstationRepository;
import de.muenchen.zms.department.view.WorkstationView;
import java.util.List;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** today: zmsapi\\DepartmentWorkstationList, zmsdb\\Workstation::readCollectionByDepartmentId */
@Service
public class DepartmentWorkstationListService {

    private final DepartmentFetchService fetchService;
    private final DepartmentWorkstationRepository workstationRepository;

    DepartmentWorkstationListService(
            DepartmentFetchService fetchService, DepartmentWorkstationRepository workstationRepository) {
        this.fetchService = fetchService;
        this.workstationRepository = workstationRepository;
    }

    @Transactional(readOnly = true)
    public List<WorkstationView> listWorkstations(Long departmentId, int resolveReferences) {
        fetchService.getById(departmentId, 0);
        return workstationRepository.findByDepartmentId(departmentId);
    }
}
