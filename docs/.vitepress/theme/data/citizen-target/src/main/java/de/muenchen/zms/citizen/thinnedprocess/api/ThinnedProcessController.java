package de.muenchen.zms.citizen.thinnedprocess.api;

import de.muenchen.zms.citizen.thinnedprocess.service.ThinnedProcessCancelService;
import de.muenchen.zms.citizen.thinnedprocess.service.ThinnedProcessConfirmService;
import de.muenchen.zms.citizen.thinnedprocess.service.ThinnedProcessFetchService;
import de.muenchen.zms.citizen.thinnedprocess.service.ThinnedProcessPreconfirmService;
import de.muenchen.zms.citizen.thinnedprocess.service.ThinnedProcessReserveService;
import de.muenchen.zms.citizen.thinnedprocess.service.ThinnedProcessUpdateService;
import de.muenchen.zms.citizen.thinnedprocess.view.ThinnedProcessView;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.Authentication;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

/**
 * REST layer for citizen routes (URL paths match today's zmscitizenapi; types are ThinnedProcess).
 * today: AppointmentByIdController, AppointmentReserveController, …
 */
@RestController
public class ThinnedProcessController {

    private final ThinnedProcessFetchService fetchService;
    private final ThinnedProcessReserveService reserveService;
    private final ThinnedProcessUpdateService updateService;
    private final ThinnedProcessConfirmService confirmService;
    private final ThinnedProcessPreconfirmService preconfirmService;
    private final ThinnedProcessCancelService cancelService;

    ThinnedProcessController(
            ThinnedProcessFetchService fetchService,
            ThinnedProcessReserveService reserveService,
            ThinnedProcessUpdateService updateService,
            ThinnedProcessConfirmService confirmService,
            ThinnedProcessPreconfirmService preconfirmService,
            ThinnedProcessCancelService cancelService) {
        this.fetchService = fetchService;
        this.reserveService = reserveService;
        this.updateService = updateService;
        this.confirmService = confirmService;
        this.preconfirmService = preconfirmService;
        this.cancelService = cancelService;
    }

    /** today: GET /appointment/ → AppointmentByIdController */
    @GetMapping("/appointment")
    public ResponseEntity<ThinnedProcessView> getAppointment(
            @RequestParam Long processId,
            @RequestParam(required = false) String authKey,
            Authentication authentication) {
        return ResponseEntity.ok(fetchService.getById(processId, authKey, authentication));
    }

    /** today: POST /reserve-appointment/ → AppointmentReserveController */
    @PostMapping("/reserve-appointment")
    public ResponseEntity<ThinnedProcessView> reserveAppointment(
            @RequestBody ThinnedProcessView body,
            @RequestParam(defaultValue = "false") boolean showUnpublished) {
        return ResponseEntity.ok(reserveService.reserve(body, showUnpublished));
    }

    /** today: POST /update-appointment/ → AppointmentUpdateController */
    @PostMapping("/update-appointment")
    public ResponseEntity<ThinnedProcessView> updateAppointment(
            @RequestBody ThinnedProcessView body, Authentication authentication) {
        return ResponseEntity.ok(updateService.update(body, authentication));
    }

    /** today: POST /confirm-appointment/ → AppointmentConfirmController */
    @PostMapping("/confirm-appointment")
    public ResponseEntity<ThinnedProcessView> confirmAppointment(
            @RequestParam Long processId,
            @RequestParam(required = false) String authKey,
            Authentication authentication) {
        return ResponseEntity.ok(confirmService.confirm(processId, authKey, authentication));
    }

    /** today: POST /preconfirm-appointment/ → AppointmentPreconfirmController */
    @PostMapping("/preconfirm-appointment")
    public ResponseEntity<ThinnedProcessView> preconfirmAppointment(
            @RequestParam Long processId,
            @RequestParam(required = false) String authKey,
            Authentication authentication) {
        return ResponseEntity.ok(preconfirmService.preconfirm(processId, authKey, authentication));
    }

    /** today: POST /cancel-appointment/ → AppointmentCancelController */
    @PostMapping("/cancel-appointment")
    public ResponseEntity<ThinnedProcessView> cancelAppointment(
            @RequestParam Long processId,
            @RequestParam(required = false) String authKey,
            Authentication authentication) {
        return ResponseEntity.ok(cancelService.cancel(processId, authKey, authentication));
    }
}
