package base;

import org.junit.jupiter.api.BeforeAll;

import config.TestConfig;

/**
 * Base test class for Citizen API tests.
 * Sets up REST-assured with the Citizen API base URI.
 */
public abstract class BaseCitizenApiTest extends BaseTest {

    @BeforeAll
    static void setupRestAssured() {
        setupRestAssuredWithBaseUri(TestConfig.getCitizenApiBaseUri());
    }
}
