package de.muenchen.zms.citizen.thinnedprocess.view;

import com.fasterxml.jackson.annotation.JsonInclude;

/** today: zmscitizenapi\\Models\\ThinnedScope */
@JsonInclude(JsonInclude.Include.NON_NULL)
public record ThinnedScopeView(
        int id,
        ThinnedProviderView provider,
        String shortName,
        String emailFrom,
        Boolean emailRequired,
        Boolean telephoneActivated,
        Boolean telephoneRequired,
        Boolean customTextfieldActivated,
        Boolean customTextfieldRequired,
        String customTextfieldLabel,
        Boolean customTextfield2Activated,
        Boolean customTextfield2Required,
        String customTextfield2Label,
        Boolean captchaActivatedRequired,
        String infoForAppointment,
        String infoForAllAppointments,
        String slotsPerAppointment,
        String appointmentsPerMail,
        String whitelistedMails,
        Integer reservationDuration,
        Integer activationDuration,
        String hint) {}
