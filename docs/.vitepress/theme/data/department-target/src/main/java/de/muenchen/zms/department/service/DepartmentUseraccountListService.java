package de.muenchen.zms.department.service;

import de.muenchen.zms.department.repository.DepartmentUseraccountRepository;
import de.muenchen.zms.department.view.UseraccountView;
import java.util.Arrays;
import java.util.List;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** today: zmsapi\\UseraccountListByDepartments, zmsdb\\Useraccount::readSearchByDepartmentIds */
@Service
public class DepartmentUseraccountListService {

    private final DepartmentUseraccountRepository useraccountRepository;
    private final DepartmentAccessService accessService;

    DepartmentUseraccountListService(
            DepartmentUseraccountRepository useraccountRepository, DepartmentAccessService accessService) {
        this.useraccountRepository = useraccountRepository;
        this.accessService = accessService;
    }

    @Transactional(readOnly = true)
    public List<UseraccountView> listByDepartmentIds(String rawIds, String query) {
        List<Long> requested = parseIds(rawIds);
        List<Long> allowed = accessService.filterAccessibleDepartmentIds(requested);
        return useraccountRepository.searchByDepartmentIds(allowed, query);
    }

    static List<Long> parseIds(String rawIds) {
        return Arrays.stream(rawIds.split(","))
                .map(String::trim)
                .filter(s -> !s.isEmpty())
                .map(Long::valueOf)
                .toList();
    }
}
