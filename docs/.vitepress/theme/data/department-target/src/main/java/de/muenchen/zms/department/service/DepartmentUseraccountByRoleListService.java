package de.muenchen.zms.department.service;

import de.muenchen.zms.department.repository.DepartmentUseraccountRepository;
import de.muenchen.zms.department.view.UseraccountView;
import java.util.List;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** today: zmsapi\\UseraccountListByRoleAndDepartments */
@Service
public class DepartmentUseraccountByRoleListService {

    private final DepartmentUseraccountRepository useraccountRepository;
    private final DepartmentAccessService accessService;

    DepartmentUseraccountByRoleListService(
            DepartmentUseraccountRepository useraccountRepository, DepartmentAccessService accessService) {
        this.useraccountRepository = useraccountRepository;
        this.accessService = accessService;
    }

    @Transactional(readOnly = true)
    public List<UseraccountView> listByRoleAndDepartmentIds(Long roleLevel, String rawIds) {
        List<Long> requested = DepartmentUseraccountListService.parseIds(rawIds);
        List<Long> allowed = accessService.filterAccessibleDepartmentIds(requested);
        return useraccountRepository.findByRoleAndDepartmentIds(roleLevel, allowed);
    }
}
