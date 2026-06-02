package de.muenchen.zms.department.view;

import com.fasterxml.jackson.annotation.JsonInclude;
import de.muenchen.zms.department.view.ReferenceViews.ClusterReferenceView;
import de.muenchen.zms.department.view.ReferenceViews.DayoffView;
import de.muenchen.zms.department.view.ReferenceViews.LinkView;
import de.muenchen.zms.department.view.ReferenceViews.ScopeReferenceView;
import java.util.List;

@JsonInclude(JsonInclude.Include.NON_NULL)
public record DepartmentView(
        Long id,
        String name,
        ContactView contact,
        String email,
        Boolean sendEmailReminderEnabled,
        Integer sendEmailReminderMinutesBefore,
        List<ScopeReferenceView> scopes,
        List<ClusterReferenceView> clusters,
        List<LinkView> links,
        List<DayoffView> dayoff) {

    public DepartmentView(
            Long id,
            String name,
            String address,
            String contactName,
            String email,
            Boolean sendEmailReminderEnabled,
            Integer sendReminderMinutesBefore) {
        this(
                id,
                name,
                new ContactView("Germany", contactName, address, extractCity(address)),
                email,
                sendEmailReminderEnabled,
                sendReminderMinutesBefore,
                List.of(),
                List.of(),
                List.of(),
                List.of());
    }

    public DepartmentView withReferences(
            List<ScopeReferenceView> scopes,
            List<ClusterReferenceView> clusters,
            List<LinkView> links,
            List<DayoffView> dayoff) {
        return new DepartmentView(
                id, name, contact, email, sendEmailReminderEnabled, sendEmailReminderMinutesBefore,
                scopes, clusters, links, dayoff);
    }

    public DepartmentView withLessData() {
        return new DepartmentView(id, name, null, null, null, null, null, null, null, null);
    }

    private static String extractCity(String address) {
        if (address == null || address.isBlank()) return null;
        String[] parts = address.trim().split("\s+");
        return parts.length > 0 ? parts[parts.length - 1] : null;
    }
}
