package zms.ataf.steps;

import io.cucumber.java.en.Then;
import io.restassured.response.Response;

/**
 * Shared step definitions for common API test steps.
 * Uses a shared response context to avoid duplication.
 */
public class CommonApiSteps {
    
    /**
     * Shared response holder - populated by step classes that make API calls.
     * This allows common steps to work across different API step classes.
     */
    private static Response sharedResponse;
    
    /**
     * Set the shared response (called by other step classes after making API calls).
     */
    public static void setResponse(Response response) {
        sharedResponse = response;
    }
    
    /**
     * Get the shared response.
     */
    public static Response getResponse() {
        return sharedResponse;
    }
    
    /**
     * Common step definition for checking HTTP status codes.
     * This step can be used by both ZMS API and Citizen API tests.
     */
    @Then("the response status code should be {int}")
    public void theResponseStatusCodeShouldBe(int statusCode) {
        if (sharedResponse == null) {
            throw new IllegalStateException("No response available. Make sure an API call was made before checking status code.");
        }
        sharedResponse.then().statusCode(statusCode);
    }
}
