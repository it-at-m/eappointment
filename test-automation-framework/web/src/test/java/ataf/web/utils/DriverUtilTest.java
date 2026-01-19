package ataf.web.utils;

import org.testng.Assert;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;

/**
 * @author Ludwig Haas (ex.haas02)
 */
public class DriverUtilTest {
    /**
     * Data provider for version comparison test cases.
     */
    @DataProvider(name = "versionComparisonData")
    public Object[][] versionComparisonData() {
        return new Object[][] {
                // Exact match cases
                { "94.0.0", "94.0.0", true },
                { "94", "94.0.0", true },

                // Current version is lower than target
                { "93.5.4", "94", true },
                { "91.213.16", "94", true },
                { "0.0.1", "1", true },

                // Current version is higher than target
                { "95.0.0", "94", false },
                { "94.1.0", "94.0.0", false },
                { "94.5.146", "94.4.0", false },

                // Target version given as major only (should consider it as major.0.0)
                { "94.5.146", "94", true },
                { "94.5.0", "94", true },
                { "95.0.0", "94", false },

                // Target version given as major.minor (should consider it as major.minor.0)
                { "94.5.146", "94.5", true },
                { "94.6.0", "94.5", false },

                // Edge cases
                { "0.0.1", "0", true },
                { "1", "1.0.0", true },

                // Invalid cases (should throw an exception)
                { "abc.2.3", "94", false },
                { "94.3", "xyz", false },
                { "94.5.6", "94.5.a", false }
        };
    }

    /**
     * Unit test for isVersionLessOrEqual method using TestNG.
     *
     * @param currentVersion The version to check.
     * @param targetVersion The reference version.
     * @param expected Expected boolean result.
     */
    @Test(dataProvider = "versionComparisonData")
    public void testIsVersionLessOrEqual(String currentVersion, String targetVersion, boolean expected) {
        try {
            boolean result = DriverUtil.isVersionLessOrEqual(currentVersion, targetVersion);
            Assert.assertEquals(result, expected, "Failed for: " + currentVersion + " <= " + targetVersion);
        } catch (Exception e) {
            Assert.assertEquals(e.getClass(), NumberFormatException.class, "Expected exception for invalid input: " + currentVersion + " vs. " + targetVersion);
        }
    }
}
