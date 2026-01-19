package ataf.rest.helper;

import ataf.core.logging.ScenarioLogManager;
import ataf.rest.model.HeaderAuthentication;
import ataf.rest.model.Operation;
import io.restassured.builder.RequestSpecBuilder;
import io.restassured.http.ContentType;
import io.restassured.response.Response;
import io.restassured.specification.RequestSpecification;

import java.util.Objects;

/**
 * This class assists in building and sending HTTP requests, managing headers, authentication, and
 * body content.
 *
 * @author Titus Pelzl (ex.pelzl), Philipp Lehmann (ex.lehmann08)
 */
public class RequestHelper {
    /**
     * Constructor that initializes a new RequestSpecification.
     */
    public RequestHelper() {
        RequestSpecBuilder requestSpecBuilder = new RequestSpecBuilder();
        requestSpecification = requestSpecBuilder.build();
    }

    private static RequestSpecification requestSpecification;
    private static String baseURL;
    private static String endpoint;

    /**
     * Sets the target endpoint for the request.
     *
     * @param targetEndpoint The endpoint to be set
     */
    public static void setEndpoint(String targetEndpoint) {
        endpoint = targetEndpoint;
    }

    /**
     * Sets the base URL for the request.
     *
     * @param baseUrl The base URL to be set
     */
    public static void setBaseURL(String baseUrl) {
        baseURL = baseUrl;
    }

    /**
     * Sets the content type for the request using a string value.
     *
     * @param contentType The content type as a string
     */
    public static void setContentType(String contentType) {
        requestSpecification.contentType(contentType);
    }

    /**
     * Sets the content type for the request using a {@link ContentType} enum.
     *
     * @param contentType The content type as a {@link ContentType}
     */
    public static void setContentType(ContentType contentType) {
        requestSpecification.contentType(contentType);
    }

    /***
     * Formats the request by applying previously set parameters, including headers, multi-part data,
     * and the request body.
     * Ensures all necessary parameters are included before sending the request.
     */
    public static void formatRequest() {
        requestSpecification.headers(HeaderHelper.getHeaderList());
        if (Objects.nonNull(BodyHelper.getMultiPartSpecification())) {
            requestSpecification.multiPart(BodyHelper.getMultiPartSpecification());
        }
        requestSpecification.body(BodyHelper.getBodyString());

        requestSpecification.baseUri(baseURL); // Base URI is set here, order is arbitrary since it doesn't get overwritten
    }

    /***
     * Sends a request with the previously set parameters.
     *
     * @param operation The operation type for the request (GET, POST, PUT, DELETE)
     * @return The response of the request. If the request fails on the server, a response with an
     *         appropriate error code is returned.
     */
    public static Response sendRequest(Operation operation) {
        Response response = null;
        formatRequest();
        Objects.requireNonNull(requestSpecification);

        ScenarioLogManager.getLogger().info("Sending request");
        response = switch (operation) {
            case POST -> requestSpecification.request().when().post(endpoint);
            case PUT -> requestSpecification.request().when().put(endpoint);
            case DELETE -> requestSpecification.request().when().delete(endpoint);
            default -> requestSpecification.request().when().get(endpoint);
        };

        ScenarioLogManager.getLogger().debug("The response is as follows: {}", Objects.requireNonNull(response));
        return response;
    }

    /**
     * Returns the current {@link RequestSpecification}.
     *
     * @return The current request specification
     */
    public static RequestSpecification getRequestSpecification() {
        return requestSpecification;
    }

    /**
     * Sets a custom {@link RequestSpecification} for future requests.
     *
     * @param rs The request specification to set
     */
    public static void setRequestSpecification(RequestSpecification rs) {
        requestSpecification = rs;
    }

    /***
     * Sets authentication headers for the request. Must be called externally from outside the
     * framework.
     *
     * @param authOper The type of authentication: BASIC, NONE, etc.
     * @param user The username for authentication
     * @param password The password for authentication
     */
    public static void setAuth(HeaderAuthentication authOper, String user, String password) {
        AuthenticationHelper.setUsername(user);
        AuthenticationHelper.setPassword(password);
        AuthenticationHelper.setHeaderAuthentication(authOper);
        requestSpecification = AuthenticationHelper.setAuthorization();
    }

    /***
     * Resets the static parameters used for requests. This function must be used if the implementation
     * is not stateless.
     */
    public static void resetParameters() {
        AuthenticationHelper.resetParameters();
        HeaderHelper.resetParameters();
        BodyHelper.resetParameters();
        requestSpecification = null;
        baseURL = null;
        endpoint = null;
    }
}
