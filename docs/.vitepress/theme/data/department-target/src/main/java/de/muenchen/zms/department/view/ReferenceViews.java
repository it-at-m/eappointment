package de.muenchen.zms.department.view;

public final class ReferenceViews {
    private ReferenceViews() {}
    public record ScopeReferenceView(Long id, String shortName, String ref) {}
    public record ClusterReferenceView(Long id) {}
    public record LinkView(String name, String url, boolean target) {}
    public record DayoffView(long date, String name) {}
}
