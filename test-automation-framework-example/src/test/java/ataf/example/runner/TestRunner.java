package ataf.example.runner;

import ataf.core.runner.BasicTestNGRunner;
import ataf.example.data.TestData;

/**
 * @author Ludwig Haas (ex.haas02)
 */
public class TestRunner extends BasicTestNGRunner {
    static {
        TestData.init();
    }
}
