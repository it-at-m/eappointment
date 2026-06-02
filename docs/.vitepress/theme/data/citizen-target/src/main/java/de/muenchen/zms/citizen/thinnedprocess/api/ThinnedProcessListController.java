package de.muenchen.zms.citizen.thinnedprocess.api;

import de.muenchen.zms.citizen.thinnedprocess.service.ThinnedProcessListService;
import de.muenchen.zms.citizen.thinnedprocess.view.ThinnedProcessView;
import java.util.List;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.Authentication;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

/** today: GET /my-appointments/ → ThinnedProcessListController */
@RestController
public class ThinnedProcessListController {

    private final ThinnedProcessListService listService;

    ThinnedProcessListController(ThinnedProcessListService listService) {
        this.listService = listService;
    }

    @GetMapping("/my-appointments")
    public ResponseEntity<List<ThinnedProcessView>> listMyAppointments(
            @RequestParam(required = false) Long filterId, Authentication authentication) {
        return ResponseEntity.ok(listService.listForUser(authentication, filterId));
    }
}
