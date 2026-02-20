package zms.ataf.data;

import ataf.core.data.Environment;
import ataf.core.logging.ScenarioLogManager;

public class TestData {
    public static final Environment LOCAL = new Environment("Environment", "LOCAL");
    public static final Environment DEV = new Environment("Environment", "DEV");
    
    public static void init() {
        ScenarioLogManager.getLogger().info("Initializing ZMS API test environments");

        // Local development (devcontainer/DDEV)
        String baseUri =
                System.getenv().getOrDefault("BASE_URI", "http://localhost:8080/terminvereinbarung/api/2");
        String citizenUri =
                System.getenv().getOrDefault("CITIZEN_API_BASE_URI", "http://localhost:8080/terminvereinbarung/api/citizen");
        String adminUri =
                System.getenv().getOrDefault("ADMIN_BASE_URI", "https://127.0.0.1:8091");
        String buergerUri =
                System.getenv().getOrDefault("BUERGER_BASE_URI", "http://localhost:8082");
        String statisticUri =
                System.getenv().getOrDefault("STATISTIC_BASE_URI", "https://127.0.0.1:8091");
        String citizenViewUri =
                System.getenv().getOrDefault("CITIZEN_VIEW_BASE_URI", "http://localhost:8082");

        LOCAL.addSystem("ZMS-API", baseUri);
        LOCAL.addSystem("ZMS-Citizen-API", citizenUri);
        LOCAL.addSystem("Admin", adminUri);
        LOCAL.addSystem("Bürgeransicht", buergerUri);
        LOCAL.addSystem("Statistik", statisticUri);
        LOCAL.addSystem("ZMS-Citizen-View", citizenViewUri);

        // City DEV environment (optional)
        DEV.addSystem("ZMS-API", "https://zms-dev.muenchen.de/terminvereinbarung/api/2");
        DEV.addSystem("ZMS-Citizen-API", "https://zms-dev.muenchen.de/terminvereinbarung/api/citizen");
        DEV.addSystem("Admin", "https://zms-dev.muenchen.de/terminvereinbarung/admin/");
        DEV.addSystem("Bürgeransicht", "https://zms-dev.muenchen.de/buergeransicht/#/");
        DEV.addSystem("Statistik", "https://zms-dev.muenchen.de/terminvereinbarung/statistic/");
        DEV.addSystem("ZMS-Citizen-View", "https://zms-dev.muenchen.de/buergeransicht/#/");
    }
}
