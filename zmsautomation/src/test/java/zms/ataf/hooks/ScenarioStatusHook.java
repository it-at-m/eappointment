package zms.ataf.hooks;

import ataf.core.logging.ScenarioLogManager;
import io.cucumber.java.After;
import io.cucumber.java.Before;
import io.cucumber.java.Scenario;

/**
 * Lightweight logging for REST and UI scenarios so we can see
 * pass/fail information while tests are running (similar to UI tests).
 */
public class ScenarioStatusHook {

    @Before
    public void logScenarioStart(Scenario scenario) {
        ScenarioLogManager.getLogger().info(
            String.format("Starting scenario: %s %s", scenario.getName(), scenario.getSourceTagNames())
        );
    }

    @After
    public void logScenarioEnd(Scenario scenario) {
        ScenarioLogManager.getLogger().info(
            String.format("Finished scenario: %s -> %s", scenario.getName(), scenario.getStatus())
        );
    }
}
