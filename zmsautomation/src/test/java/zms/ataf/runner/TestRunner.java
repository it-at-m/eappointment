package zms.ataf.runner;

import ataf.core.runner.BasicTestNGRunner;
import zms.ataf.data.TestData;

public class TestRunner extends BasicTestNGRunner {
    static {
        TestData.init();
    }
}
