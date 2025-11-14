package base;

import org.junit.jupiter.api.BeforeAll;

import config.TestConfig;

/**
 * Base test class for Citizen API tests.
 * Uses Citizen API base URI instead of the default ZMS API base URI.
 */
public abstract class BaseCitizenApiTest extends BaseApiTest {

    @BeforeAll
    static void setupRestAssured() {
        setupRestAssuredWithBaseUri(TestConfig.getCitizenApiBaseUri());
    }
}
