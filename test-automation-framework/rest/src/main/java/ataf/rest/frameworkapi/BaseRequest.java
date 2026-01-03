package ataf.rest.frameworkapi;

import ataf.core.assertions.CustomAssertions;
import ataf.core.logging.ScenarioLogManager;
import ataf.rest.helper.RequestHelper;
import ataf.rest.model.Operation;
import io.restassured.response.Response;

/**
 * This class represents the base request functionality for sending HTTP requests and managing
 * responses within the framework.
 *
 * @author Titus Pelzl (ex.pelzl), Philipp Lehmann (ex.lehmann08)
 */
public class BaseRequest {

    /**
     * Default constructor.
     */
    public BaseRequest() {
        // this line intentionally left blank
    }

    private static Response response;

    /***
     * Sends a request where the inner parameters have been prepared in advance.
     *
     * @param endpoint The endpoint to which the request should be sent
     * @param operation The HTTP method to be used for the request (GET, POST, PUT, DELETE)
     */
    public void sendRequest(String endpoint, Operation operation) {
        RequestHelper.setEndpoint(endpoint);
        response = RequestHelper.sendRequest(operation);
        ScenarioLogManager.getLogger().info("Response time of the request: {}ms", response.getTime());
    }

    /***
     * Returns the response of a previously sent request. If no request has been sent,
     * a "NotNull" assertion will fail.
     *
     * @return The response of the previous request
     */
    public Response getResponse() {
        CustomAssertions.assertNotNull(response, "Empty response retrieved! It seems not request has been sent.");
        return response;
    }

    /***
     * Manually sets a response where it is expected by other parts of the framework.
     *
     * @param _response The response to be manually set
     */
    public void setResponse(Response _response) {
        response = _response;
    }
}
