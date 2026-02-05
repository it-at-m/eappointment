package zms.ataf.data;

import ataf.core.data.Environment;
import ataf.core.logging.ScenarioLogManager;

public class TestData {
    public static final Environment LOCAL = new Environment("Environment", "LOCAL");
    public static final Environment DEV = new Environment("Environment", "DEV");
    
    public static void init() {
        ScenarioLogManager.getLogger().info("Initializing ZMS API test environments");
        
        // Local development (devcontainer/DDEV)
        String baseUri = System.getenv().getOrDefault("BASE_URI", 
            "http://localhost:8080/terminvereinbarung/api/2");
        String citizenUri = System.getenv().getOrDefault("CITIZEN_API_BASE_URI",
            "http://localhost:8080/terminvereinbarung/api/citizen");
        
        LOCAL.addSystem("ZMS-API", baseUri);
        LOCAL.addSystem("ZMS-Citizen-API", citizenUri);
        
        // City DEV environment (optional)
        DEV.addSystem("ZMS-API", "https://zms-dev.muenchen.de/terminvereinbarung/api/2");
        DEV.addSystem("ZMS-Citizen-API", "https://zms-dev.muenchen.de/terminvereinbarung/api/citizen");
    }
}
