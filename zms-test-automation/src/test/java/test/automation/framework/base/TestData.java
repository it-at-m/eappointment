package test.automation.framework.base;

import ataf.core.data.Environment;
import ataf.core.logging.ScenarioLogManager;

/**
 * @author Ludwig Haas (ex.haas02)
 */
public class TestData {

    public static final Environment DEV = new Environment("zms-dev");
    public static final Environment TEST = new Environment("zms-test");
    public static final Environment DEMO = new Environment("zms-demo");
    public static final Environment LOAD = new Environment("zms-load");

    public static void init() {
        ScenarioLogManager.getLogger().info(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>Start of initializing test data>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>");

        ScenarioLogManager.getLogger().info("Adding systems for DEV");
        DEV.addSystem("Admin", "https://zms-dev.muenchen.de/terminvereinbarung/admin/");
        DEV.addSystem("Bürgeransicht", "https://zms-dev.muenchen.de/buergeransicht/#/");
        DEV.addSystem("Statistik", "https://zms-dev.muenchen.de/terminvereinbarung/statistic/");

        ScenarioLogManager.getLogger().info("Adding systems for TEST");
        TEST.addSystem("Admin", "https://zms-test.muenchen.de/terminvereinbarung/admin/");
        TEST.addSystem("Bürgeransicht", "https://zms-test.muenchen.de/buergeransicht/#/");
        TEST.addSystem("Statistik", "https://zms-test.muenchen.de/terminvereinbarung/statistic/");

        ScenarioLogManager.getLogger().info("Adding systems for DEMO");
        DEMO.addSystem("Admin", "https://zms-demo.muenchen.de/terminvereinbarung/admin/");
        DEMO.addSystem("Bürgeransicht", "https://zms-demo.muenchen.de/buergeransicht/#/");
        DEMO.addSystem("Statistik", "https://zms-demo.muenchen.de/terminvereinbarung/statistic/");

        ScenarioLogManager.getLogger().info("Adding systems for LOAD");
        LOAD.addSystem("Admin", "https://zms-load.muenchen.de/terminvereinbarung/admin/");
        LOAD.addSystem("Bürgeransicht", "https://zms-load.muenchen.de/buergeransicht/#/");
        LOAD.addSystem("Statistik", "https://zms-load.muenchen.de/terminvereinbarung/statistic/");

        ScenarioLogManager.getLogger().info("<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<Finished initializing test data<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");
    }
}
