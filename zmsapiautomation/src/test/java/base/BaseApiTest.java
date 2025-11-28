package base;

import org.junit.jupiter.api.BeforeAll;

import config.TestConfig;

/**
 * Base test class for ZMS API tests.
 * Sets up REST-assured with the ZMS API base URI.
 */
public abstract class BaseApiTest extends BaseTest {

    @BeforeAll
    static void setupRestAssured() {
        setupRestAssuredWithBaseUri(TestConfig.getBaseUri());
    }
}
