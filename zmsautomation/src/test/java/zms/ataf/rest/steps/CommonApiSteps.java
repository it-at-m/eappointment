package zms.ataf.rest.steps;

import io.cucumber.java.Before;
import io.cucumber.java.en.Then;
import io.restassured.response.Response;

/**
 * Shared step definitions for common API test steps.
 * Uses a shared response context to avoid duplication.
 */
public class CommonApiSteps {
    
    /**
     * Response for this scenario's thread. Other step classes on the same thread
     * publish into it so the status-code step can read the last call.
     */
    private static final ThreadLocal<Response> SHARED_RESPONSE = new ThreadLocal<>();
    
    /**
     * Reset shared state before each scenario to prevent state leakage.
     */
    @Before
    public void resetSharedState() {
        SHARED_RESPONSE.remove();
    }
    
    /**
     * Set the shared response (called by other step classes after making API calls).
     */
    public static void setResponse(Response response) {
        SHARED_RESPONSE.set(response);
    }
    
    /**
     * Get the shared response.
     */
    public static Response getResponse() {
        return SHARED_RESPONSE.get();
    }
    
    /**
     * Common step definition for checking HTTP status codes.
     * This step can be used by both ZMS API and Citizen API tests.
     */
    @Then("the response status code should be {int}")
    public void theResponseStatusCodeShouldBe(int statusCode) {
        Response sharedResponse = getResponse();
        if (sharedResponse == null) {
            throw new IllegalStateException("No response available. Make sure an API call was made before checking status code.");
        }
        sharedResponse.then().statusCode(statusCode);
    }
}
