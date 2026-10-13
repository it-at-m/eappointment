package zms.ataf.hooks;

import ataf.core.logging.ScenarioLogManager;
import io.cucumber.java.After;
import io.cucumber.java.Before;
import zms.ataf.ui.pages.citizenview.support.FeuerwacheCapacityLock;

/**
 * See {@link FeuerwacheCapacityLock}. Tag scenarios with {@code @feuerwache-capacity-lock}.
 */
public class FeuerwacheCapacityLockHook {

    @Before(value = "@feuerwache-capacity-lock", order = 40)
    public void acquireFeuerwacheCapacityLock() {
        ScenarioLogManager.getLogger().info("Acquiring Feuerwache capacity lock");
        FeuerwacheCapacityLock.acquire();
    }

    @After(value = "@feuerwache-capacity-lock", order = 40)
    public void releaseFeuerwacheCapacityLock() {
        FeuerwacheCapacityLock.release();
        ScenarioLogManager.getLogger().info("Released Feuerwache capacity lock");
    }
}
