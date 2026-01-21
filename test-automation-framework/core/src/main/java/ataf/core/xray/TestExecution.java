package ataf.core.xray;

import ataf.core.assertions.CustomAssertions;
import ataf.core.clients.JiraClient;
import ataf.core.helpers.AuthenticationHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.core.properties.DefaultValues;
import ataf.core.properties.TestProperties;
import com.google.gson.Gson;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import org.apache.hc.core5.http.ContentType;

import java.util.Set;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.ConcurrentSkipListSet;
import java.util.concurrent.atomic.AtomicBoolean;

/**
 * Represents the execution of tests associated with a specific Jira issue. This class manages test
 * assignments, labels, and the retrieval of test cases for a
 * given execution environment.
 *
 * <p>
 * Author: Ludwig Haas (ex.haas02)
 * </p>
 */
public class TestExecution {
    /**
     * Jira issue key associated with the test execution
     */
    public final String ISSUE_KEY;

    /**
     * Environment in which the tests are executed
     */
    public final String ENVIRONMENT;

    private final Set<Test> TESTS; // Set of tests associated with the issue
    private final AtomicBoolean assignedOnce = new AtomicBoolean(false); // AtomicBoolean to ensure assign() happens only once per object
    private final AtomicBoolean finishedOnce = new AtomicBoolean(false); // AtomicBoolean to ensure finish() happens only once per object
    private final Set<String> labelOperations = ConcurrentHashMap.newKeySet(); // Keep track of labels that have been successfully added/removed so we don’t repeat

    /**
     * Constructs a TestExecution instance with the specified issue key and environment.
     *
     * @param issueKey Jira issue key associated with the test execution.
     * @param environment Environment in which the tests will be executed.
     */
    public TestExecution(String issueKey, String environment) {
        ISSUE_KEY = issueKey;
        ENVIRONMENT = environment;
        TESTS = getTests(issueKey); // Retrieve associated tests from Jira
    }

    /**
     * Retrieves a set of tests associated with the specified issue key from Jira.
     *
     * @param issueKey the Jira issue key for which to retrieve tests.
     * @return a set of Test objects associated with the issue key.
     */
    private Set<Test> getTests(String issueKey) {
        Set<Test> tests = new ConcurrentSkipListSet<>();
        try (JiraClient jiraClient = new JiraClient()) {
            // Execute HTTP GET request to fetch tests from Jira
            String response = jiraClient.executeHttpGetRequest(JiraClient.JIRA_XRAY_REST_API_URL + "api/testexec/" + issueKey + "/test",
                    JiraClient.AuthenticationMethod.AccessToken);
            CustomAssertions.assertNotNull(response,
                    "HTTP GET request \"" + JiraClient.JIRA_XRAY_REST_API_URL + "api/testexec/" + issueKey + "/test\" returned an empty result!");
            CustomAssertions.assertEquals(jiraClient.getLastRequestStatusCode(), 200, response);
            Gson gson = new Gson();
            for (JsonElement jsonElement : gson.fromJson(response, JsonArray.class).asList()) {
                // Parse the JSON response to create Test objects
                tests.add(new Test(
                        jsonElement.getAsJsonObject().get("id").getAsInt(),
                        jsonElement.getAsJsonObject().get("key").getAsString(),
                        jsonElement.getAsJsonObject().get("rank").getAsInt(),
                        TestStatus.getStatus(jsonElement.getAsJsonObject().get("status").getAsString())));
            }
        } catch (Exception e) {
            ScenarioLogManager.getLogger().error(e.getMessage(), e);
        }
        return tests;
    }

    /**
     * Retrieves a test by its unique ID.
     *
     * @param id the unique ID of the test.
     * @return the Test object if found; otherwise, null.
     */
    public Test getTestById(int id) {
        for (Test test : TESTS) {
            if (test.ID == id) {
                return test;
            }
        }
        return null; // Test not found
    }

    /**
     * Retrieves a test by its Jira issue key.
     *
     * @param issueKey the Jira issue key of the test.
     * @return the Test object if found; otherwise, null.
     */
    public Test getTestByIssueKey(String issueKey) {
        for (Test test : TESTS) {
            if (test.ISSUE_KEY.equals(issueKey)) {
                return test;
            }
        }
        return null; // Test not found
    }

    /**
     * Retrieves a test by its rank.
     *
     * @param rank the rank of the test.
     * @return the Test object if found; otherwise, null.
     */
    public Test getTestByRank(int rank) {
        for (Test test : TESTS) {
            if (test.RANK == rank) {
                return test;
            }
        }
        return null; // Test not found
    }

    /**
     * Gets the number of tests associated with this execution.
     *
     * @return the number of tests.
     */
    public int getNumberOfTests() {
        return TESTS.size();
    }

    /**
     * Assigns the current user as the assignee for the test execution if it is not already assigned.
     *
     * <p>
     * If the assignment fails, an error is logged.
     * </p>
     */
    public void assign() {
        if (assignedOnce.compareAndSet(false, true)) {
            try (JiraClient jiraClient = new JiraClient()) {
                final StringBuilder clearUserName = new StringBuilder();
                AuthenticationHelper.getUserName().access(clearUserName::append);
                String jiraName = clearUserName.toString();
                clearUserName.setLength(0); // Clear the StringBuilder

                String response = jiraClient.executeHttpPutRequest(
                        JiraClient.JIRA_REST_API_URL + "issue/" + ISSUE_KEY + "/assignee",
                        "{\"name\":\"" + jiraName + "\"}",
                        AuthenticationHelper.getAuthenticationMethod());

                CustomAssertions.assertEquals(jiraClient.getLastRequestStatusCode(), 204, response);
            } catch (Exception e) {
                ScenarioLogManager.getLogger().error("Setting of test execution assignee has failed!", e);
            }
            addLabel(TestProperties.getProperty("test.execution.labels.in.progress", true, DefaultValues.TEST_EXECUTION_LABELS_IN_PROGRESS)
                    .orElse(DefaultValues.TEST_EXECUTION_LABELS_IN_PROGRESS));
        } else {
            ScenarioLogManager.getLogger().warn("Test execution ({}) has already been assigned!", ISSUE_KEY);
        }
    }

    /**
     * Completes the test execution by transitioning its status in Jira.
     *
     * <p>
     * If the transition fails, an error is logged.
     * </p>
     */
    public void finish() {
        if (finishedOnce.compareAndSet(false, true)) {
            try (JiraClient jiraClient = new JiraClient()) {
                String response = jiraClient.executeHttpPostRequest(
                        JiraClient.JIRA_REST_API_URL + "issue/" + ISSUE_KEY + "/transitions",
                        "{\"transition\":{\"id\":\"" + TestProperties.getProperty("test.execution.transition.id.done", true)
                                .map(String.class::cast).orElse("") + "\"}}",
                        ContentType.APPLICATION_JSON,
                        AuthenticationHelper.getAuthenticationMethod());
                CustomAssertions.assertEquals(jiraClient.getLastRequestStatusCode(), 204, response);
            } catch (Exception e) {
                ScenarioLogManager.getLogger().error("The transition of test execution [{}] was not successful!", ISSUE_KEY, e);
            }
            removeLabel(TestProperties.getProperty("test.execution.labels.in.progress", true, DefaultValues.TEST_EXECUTION_LABELS_IN_PROGRESS)
                    .orElse(DefaultValues.TEST_EXECUTION_LABELS_IN_PROGRESS));
        } else {
            ScenarioLogManager.getLogger().warn("Test execution ({}) has already been finished!", ISSUE_KEY);
        }
    }

    /**
     * Adds a label to the Jira issue for this test execution.
     *
     * @param label the label to be added.
     */
    private void addLabel(String label) {
        if (labelOperations.add("ADDED_" + label)) {
            try (JiraClient jiraClient = new JiraClient()) {
                String response = jiraClient.executeHttpPutRequest(
                        JiraClient.JIRA_REST_API_URL + "issue/" + ISSUE_KEY,
                        "{\"update\":{\"labels\":[{\"add\":\"" + label + "\"}]}}",
                        AuthenticationHelper.getAuthenticationMethod());
                CustomAssertions.assertEquals(jiraClient.getLastRequestStatusCode(), 204, response);
            } catch (Exception e) {
                ScenarioLogManager.getLogger().error("Adding of label [{}] for test execution [{}] has failed!", label, ISSUE_KEY, e);
            }
        } else {
            ScenarioLogManager.getLogger().warn("Label [{}] has already been added to test execution ({})!", label, ISSUE_KEY);
        }
    }

    /**
     * Removes a label from the Jira issue for this test execution.
     *
     * @param label the label to be removed.
     */
    private void removeLabel(String label) {
        if (labelOperations.add("REMOVED_" + label)) {
            try (JiraClient jiraClient = new JiraClient()) {
                String response = jiraClient.executeHttpPutRequest(
                        JiraClient.JIRA_REST_API_URL + "issue/" + ISSUE_KEY,
                        "{\"update\":{\"labels\":[{\"remove\":\"" + label + "\"}]}}",
                        AuthenticationHelper.getAuthenticationMethod());
                CustomAssertions.assertEquals(jiraClient.getLastRequestStatusCode(), 204, response);
            } catch (Exception e) {
                ScenarioLogManager.getLogger().error("Removing of label [{}] for test execution [{}] has failed!", label, ISSUE_KEY, e);
            }
        } else {
            ScenarioLogManager.getLogger().warn("Label [{}] has already been removed from test execution ({})!", label, ISSUE_KEY);
        }
    }
}
