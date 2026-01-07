package ataf.core.xray;

import ataf.core.assertions.CustomAssertions;
import ataf.core.clients.JiraClient;
import ataf.core.helpers.AuthenticationHelper;
import ataf.core.logging.ScenarioLogManager;
import org.jetbrains.annotations.NotNull;

import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.util.concurrent.atomic.AtomicBoolean;

/**
 * Represents a test case with associated attributes and methods for managing its status and
 * assignee within a Jira system. This class implements Comparable to
 * allow sorting of test cases based on their rank.
 *
 * <p>
 * Author: Ludwig Haas (ex.haas02)
 * </p>
 */
public class Test implements Comparable<Test> {
    /**
     * Unique identifier for the test
     */
    public final int ID;

    /**
     * Jira issue key associated with the test
     */
    public final String ISSUE_KEY;

    /**
     * Rank of the test for sorting purposes
     */
    public final int RANK;

    private TestStatus status; // Current status of the test
    private final AtomicBoolean assignedOnce = new AtomicBoolean(false); // Indicates if the test is assigned

    /**
     * Constructs a Test instance with the specified attributes.
     *
     * @param id Unique identifier for the test.
     * @param issueKey Jira issue key associated with the test.
     * @param rank Rank of the test for sorting purposes.
     * @param status Initial status of the test.
     */
    public Test(int id, String issueKey, int rank, TestStatus status) {
        ID = id;
        ISSUE_KEY = issueKey;
        RANK = rank;
        this.status = status;
    }

    /**
     * Retrieves the current status of the test.
     *
     * @return the current status of the test.
     */
    public TestStatus getStatus() {
        return status;
    }

    /**
     * Updates the status of the test and communicates the change to the Jira system.
     *
     * <p>
     * If the update to the status fails, an error is logged.
     * </p>
     *
     * @param status the new status to be set for the test.
     */
    public void setStatus(TestStatus status) {
        try (JiraClient jiraClient = new JiraClient()) {
            // Execute HTTP request to update test status in Jira
            String response = jiraClient.executeHttpPutRequest(JiraClient.JIRA_XRAY_REST_API_URL + "api/testrun/" + ID + "/status?status=" + status.NAME, "",
                    AuthenticationHelper.getAuthenticationMethod());
            CustomAssertions.assertEquals(jiraClient.getLastRequestStatusCode(), 200, response);
        } catch (Exception e) {
            ScenarioLogManager.getLogger().error("Setting of test status has failed!", e);
        }
        this.status = status; // Update the local status
    }

    /**
     * Assigns the current user as the assignee for the test if it is not already assigned.
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

                // Execute HTTP request to assign the current user to the test
                String response = jiraClient.executeHttpPutRequest(
                        JiraClient.JIRA_XRAY_REST_API_URL + "api/testrun/" + ID + "/assignee?user=" + URLEncoder.encode(jiraName,
                                StandardCharsets.UTF_8),
                        "", AuthenticationHelper.getAuthenticationMethod());

                CustomAssertions.assertEquals(jiraClient.getLastRequestStatusCode(), 200, response);
            } catch (Exception e) {
                ScenarioLogManager.getLogger().error("Setting of test assignee has failed!", e);
            }
        } else {
            ScenarioLogManager.getLogger().warn("Test ({}) has already been assigned!", ISSUE_KEY);
        }
    }

    /**
     * Compares this test to another test based on their rank for sorting.
     *
     * @param test the other test to compare to.
     * @return a negative integer, zero, or a positive integer as this test's rank is less than, equal
     *         to, or greater than the specified test's rank.
     */
    @Override
    public int compareTo(@NotNull Test test) {
        return Integer.compare(RANK, test.RANK);
    }
}
