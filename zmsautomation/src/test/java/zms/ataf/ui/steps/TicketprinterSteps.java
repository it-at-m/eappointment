package zms.ataf.ui.steps;

import ataf.core.helpers.TestDataHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.web.utils.DriverUtil;
import io.cucumber.java.de.Dann;
import io.cucumber.java.de.Wenn;
import zms.ataf.ui.pages.ticketprinter.TicketprinterPage;

public class TicketprinterSteps {

    private final TicketprinterPage page;

    public TicketprinterSteps() {
        page = new TicketprinterPage(DriverUtil.getDriver());
    }

    @Wenn("Sie die Ticketausgabe für den Standort {string} öffnen.")
    public void sie_die_ticketausgabe_fuer_den_standort_oeffnen(String scopeId) {
        scopeId = TestDataHelper.transformTestData(scopeId);
        ScenarioLogManager.getLogger().info("Ticketprinter: open scope {}", scopeId);
        page.openScope(scopeId);
    }

    @Wenn("Sie die Ticketausgabe für die Dienstleistung {string} am Standort {string} öffnen.")
    public void sie_die_ticketausgabe_fuer_die_dienstleistung_am_standort_oeffnen(String requestId, String scopeId) {
        requestId = TestDataHelper.transformTestData(requestId);
        scopeId = TestDataHelper.transformTestData(scopeId);
        ScenarioLogManager.getLogger().info("Ticketprinter: open request {} at scope {}", requestId, scopeId);
        page.openRequest(scopeId, requestId);
    }

    @Dann("sollte die Schaltfläche {string} auf der Ticketausgabe sichtbar sein.")
    public void sollte_die_schaltflaeche_auf_der_ticketausgabe_sichtbar_sein(String label) {
        label = TestDataHelper.transformTestData(label);
        ScenarioLogManager.getLogger().info("Ticketprinter: assert button visible \"{}\"", label);
        page.assertWaitingNumberButtonVisible(label);
    }

    @Wenn("Sie auf der Ticketausgabe auf die Schaltfläche {string} klicken.")
    public void sie_auf_der_ticketausgabe_auf_die_schaltflaeche_klicken(String label) {
        label = TestDataHelper.transformTestData(label);
        ScenarioLogManager.getLogger().info("Ticketprinter: click button \"{}\"", label);
        page.clickWaitingNumberButton(label);
    }

    @Dann("sollte Ihnen eine Wartenummer angezeigt werden.")
    public void sollte_ihnen_eine_wartenummer_angezeigt_werden() {
        ScenarioLogManager.getLogger().info("Ticketprinter: assert waiting number shown");
        page.assertWaitingNumberShown();
    }
}
