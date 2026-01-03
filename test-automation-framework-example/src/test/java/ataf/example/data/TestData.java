package ataf.example.data;

import ataf.core.data.Environment;
import ataf.core.logging.ScenarioLogManager;

/**
 * @author Ludwig Haas (ex.haas02)
 */
public class TestData {

    //Environments
    public static final Environment PRODUCTION = new Environment("PROD");

    public static void init() {
        ScenarioLogManager.getLogger().info(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>Start of initializing test data>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>");

        ScenarioLogManager.getLogger().info("Adding systems for Production");
        PRODUCTION.addSystem("WiLMA", "https://wilma.muenchen.de/");

        ScenarioLogManager.getLogger().info("<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<Finished initialization of test data<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");
    }
}
