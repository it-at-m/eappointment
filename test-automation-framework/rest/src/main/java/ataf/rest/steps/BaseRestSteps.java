package ataf.rest.steps;

import ataf.core.logging.ScenarioLogManager;
import ataf.rest.frameworkapi.BaseRequest;

/**
 * This class provides a step definition for REST API interactions, using {@link BaseRequest} for
 * making requests.
 *
 * <p>
 * This class is typically used in the context of test steps where interactions with a REST API are
 * needed.
 * </p>
 *
 * @author Titus Pelzl (ex.pelzl), Philipp Lehmann (ex.lehmann08)
 */
public class BaseRestSteps {

    private BaseRequest baseRequest;

    /**
     * Constructor that initializes a new {@link BaseRequest} instance.
     */
    public BaseRestSteps() {
        baseRequest = new BaseRequest();
    }

    /**
     * Returns the current {@link BaseRequest} instance used for making API requests.
     *
     * @return The current BaseRequest instance
     */
    public BaseRequest getBaseRequest() {
        return baseRequest;
    }

    /**
     * Sets a new {@link BaseRequest} instance for making API requests and logs this action.
     *
     * @param baseRequest The new BaseRequest instance to set
     */
    public void setBaseRequest(BaseRequest baseRequest) {
        this.baseRequest = baseRequest;
        ScenarioLogManager.getLogger().info("BaseRequest set");
    }
}
