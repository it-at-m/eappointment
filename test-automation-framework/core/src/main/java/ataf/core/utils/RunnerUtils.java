package ataf.core.utils;

import ataf.core.assertions.CustomAssertions;
import ataf.core.clients.HttpClient;
import ataf.core.clients.JiraClient;
import ataf.core.context.TestExecutionContext;
import ataf.core.data.Environment;
import ataf.core.helpers.AuthenticationHelper;
import ataf.core.helpers.TestDataHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.core.properties.DefaultValues;
import ataf.core.properties.TestProperties;
import ataf.core.xray.Test;
import ataf.core.xray.TestExecution;
import ataf.core.xray.TestStatus;
import com.google.gson.Gson;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import org.apache.hc.core5.http.ContentType;
import org.apache.logging.log4j.Level;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.core.config.Configurator;

import java.io.IOException;
import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.util.Comparator;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;
import java.util.stream.Stream;

/**
 * Utility class for managing the setup and teardown of test suites, particularly in the context of
 * executing tests related to Jira issues. This class
 * facilitates authentication, feature file management, and the integration of test results with
 * Jira.
 *
 * <p>
 * It handles fetching feature files from Jira, importing Cucumber results, and maintaining the
 * state of test executions.
 * </p>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class RunnerUtils {
    private static Path tempFeatureDirectory;
    private static boolean cucumberFeaturesExported = false;

    /**
     * Is a map that holds objects of type {@link TestExecution}, its keys are of type String and should
     * be the associated Jira issue key of the test execution
     * issue.
     */
    public static final Map<String, TestExecution> TEST_EXECUTION_MAP = new ConcurrentHashMap<>();

    /**
     * Checks whether the test execution is based on Jira.
     *
     * @return true if cucumber features have been exported, false otherwise.
     */
    public static boolean isJiraBasedTestExecution() {
        return cucumberFeaturesExported;
    }

    /**
     * Sets up the test suite by configuring authentication, logging, and Jira test execution settings.
     *
     * <p>
     * This method performs the following actions:
     * </p>
     * <ul>
     * <li>Sets user credentials from system properties.</li>
     * <li>Configures logging level.</li>
     * <li>Creates directories for feature files.</li>
     * <li>Interacts with Jira to create test executions and fetch feature files.</li>
     * </ul>
     *
     * <p>
     * It will also handle exceptions and log errors appropriately.
     * </p>
     */
    public static void setupTestSuite() {
        ScenarioLogManager.getLogger().info("Start of Test Suite!");

        // Getting and setting credentials or authorization token
        try {
            AuthenticationHelper.setUserName(System.getProperty("sso.username").toCharArray());
            System.setProperty("sso.username", "");
        } catch (IllegalArgumentException | NullPointerException ignore) {
        }

        try {
            AuthenticationHelper.setUserPassword(System.getProperty("sso.password").toCharArray());
            System.setProperty("sso.password", "");
        } catch (IllegalArgumentException | NullPointerException ignore) {
        }

        try {
            AuthenticationHelper.setAuthorizationToken(System.getProperty("auth_token").toCharArray());
            System.setProperty("auth_token", "");
        } catch (IllegalArgumentException | NullPointerException ignore) {
        }

        // Getting and setting test data encryption password
        CryptoUtils.setSecret(TestProperties.getProperty("testDataEncryptionPassword", true).map(String.class::cast).orElse("").toCharArray());

        // Getting and setting log level
        Level logLevel = Level.getLevel(
                TestProperties.getProperty("logLevel", true, DefaultValues.LOG_LEVEL).orElse(DefaultValues.LOG_LEVEL));
        if (logLevel != null) {
            Configurator.setAllLevels(LogManager.getRootLogger().getName(), logLevel);
            ScenarioLogManager.getLogger().info("Log Level set to \"{}\"", logLevel.name());
        }

        // Trying to download cucumber scenarios from Jira
        Path featuresDirectoryPath = Paths.get(TestProperties.getProperty("features", true).map(String.class::cast).orElse("")).toAbsolutePath().normalize();
        if (Files.notExists(featuresDirectoryPath)) {
            try {
                Files.createDirectories(featuresDirectoryPath);
            } catch (IOException e) {
                ScenarioLogManager.getLogger().error("Could not create features directories!", e);
                throw new RuntimeException(e);
            }
        }

        // Check if credentials or authorization token have been set
        if (AuthenticationHelper.credentialsHaveNotBeenSet() && AuthenticationHelper.authorizationTokenHasNotBeenSet()) {
            ScenarioLogManager.getLogger().warn("No credentials or authorization token has been submitted. Jira related functions will not work!");
        } else {
            boolean createTestExecution = false;
            try {
                createTestExecution = TestProperties.getProperty("createTestExecution", false).map(Boolean.class::cast).orElse(false);
            } catch (IllegalArgumentException | NullPointerException ignore) {
            }

            String jiraKeys = "";
            try {
                jiraKeys = TestProperties.getProperty("issueKeys", false).map(String.class::cast).orElse("");
            } catch (IllegalArgumentException | NullPointerException ignore) {
            }

            String jiraFilter = "";
            try {
                jiraFilter = TestProperties.getProperty("filterId", false).map(String.class::cast).orElse("");
            } catch (IllegalArgumentException | NullPointerException ignore) {
            }

            // Set authentication methods based on credentials
            if (!AuthenticationHelper.credentialsHaveNotBeenSet()) {
                AuthenticationHelper.setAuthenticationMethod(HttpClient.AuthenticationMethod.BasicAuth);
            }
            if (!AuthenticationHelper.authorizationTokenHasNotBeenSet()) {
                AuthenticationHelper.setAuthenticationMethod(HttpClient.AuthenticationMethod.AccessToken);
            }

            // Create test execution if required
            if (createTestExecution) {
                String testPlanKey = "";
                try {
                    testPlanKey = TestProperties.getProperty("testPlanKey", false).map(String.class::cast).orElse("");
                } catch (IllegalArgumentException | NullPointerException ignore) {
                }

                CustomAssertions.assertFalse(jiraKeys.isEmpty(), "Cannot create test execution: No property value for \"issueKeys\" has been provided!");

                try (JiraClient jiraClient = new JiraClient()) {
                    JsonObject root = new JsonObject();
                    JsonObject fields = new JsonObject();
                    JsonObject project = new JsonObject();
                    project.addProperty("id", TestProperties.getProperty("test.execution.project.id", true).map(String.class::cast).orElse(""));
                    fields.add("project", project);
                    fields.addProperty("summary", TestProperties.getProperty("test.execution.summary", true, DefaultValues.TEST_EXECUTION_SUMMARY)
                            .orElse(DefaultValues.TEST_EXECUTION_SUMMARY));
                    JsonObject issueType = new JsonObject();
                    issueType.addProperty("id", TestProperties.getProperty("test.execution.issuetype.id", true, DefaultValues.TEST_EXECUTION_ISSUE_TYPE_ID)
                            .orElse(DefaultValues.TEST_EXECUTION_ISSUE_TYPE_ID));
                    fields.add("issuetype", issueType);
                    JsonArray labels = new JsonArray();
                    labels.add(TestProperties.getProperty("test.execution.labels.automation.label", true, DefaultValues.TEST_EXECUTION_LABELS_AUTOMATION_LABEL)
                            .orElse(DefaultValues.TEST_EXECUTION_LABELS_AUTOMATION_LABEL));
                    fields.add("labels", labels);
                    String testEnvironment = TestProperties.getProperty("test.execution.test.environment", false).map(String.class::cast).orElse("");
                    if (!testEnvironment.isEmpty()) {
                        JsonArray testEnvironments = new JsonArray();
                        testEnvironments.add(testEnvironment);
                        fields.add("customfield_10229", testEnvironments);
                    }
                    if (!testPlanKey.isEmpty()) {
                        JsonArray testPlan = new JsonArray();
                        testPlan.add(testPlanKey);
                        fields.add("customfield_10231", testPlan);
                    }
                    root.add("fields", fields);
                    String jsonResult = jiraClient.executeHttpPostRequest(JiraClient.JIRA_REST_API_URL + "issue", root.toString(), ContentType.APPLICATION_JSON,
                            AuthenticationHelper.getAuthenticationMethod());
                    CustomAssertions.assertNotNull(jsonResult, "HTTP POST request \"" + JiraClient.JIRA_REST_API_URL + "issue\" returned an empty result!");
                    CustomAssertions.assertEquals(jiraClient.getLastRequestStatusCode(), 201, jsonResult);
                    String testExecutionKey = new Gson().fromJson(jsonResult, JsonObject.class).get("key").getAsString();
                    JsonArray testKeys = new JsonArray();
                    for (String jiraKey : jiraKeys.split(",")) {
                        testKeys.add(jiraKey.trim());
                    }
                    jiraKeys = testExecutionKey;
                    root = new JsonObject();
                    root.add("add", testKeys);
                    jsonResult = jiraClient.executeHttpPostRequest(JiraClient.JIRA_XRAY_REST_API_URL + "api/testexec/" + testExecutionKey + "/test",
                            root.toString(), ContentType.APPLICATION_JSON, AuthenticationHelper.getAuthenticationMethod());
                    CustomAssertions.assertNotNull(jsonResult,
                            "HTTP POST request \"" + JiraClient.JIRA_XRAY_REST_API_URL + "api/testexec/" + testExecutionKey
                                    + "/test\" returned an empty result!");
                    CustomAssertions.assertEquals(jiraClient.getLastRequestStatusCode(), 200, jsonResult);
                    jsonResult = jiraClient.executeHttpPostRequest(JiraClient.JIRA_REST_API_URL + "issue/" + testExecutionKey + "/transitions",
                            "{\"transition\":{\"id\":\"" + TestProperties.getProperty("test.execution.transition.id.in.progress", true).orElse("") + "\"}}",
                            ContentType.APPLICATION_JSON, AuthenticationHelper.getAuthenticationMethod());
                    CustomAssertions.assertEquals(jiraClient.getLastRequestStatusCode(), 204, jsonResult);
                    TEST_EXECUTION_MAP.putIfAbsent(testExecutionKey, new TestExecution(testExecutionKey, testEnvironment));
                } catch (Exception e) {
                    CustomAssertions.fail(e.getMessage(), e);
                }
            }

            // Generate JQL for fetching test executions
            if (jiraKeys.isEmpty() && jiraFilter.isEmpty()) {
                ScenarioLogManager.getLogger().warn(
                        "Cannot create JQL: No property value for \"issueKeys\" or \"filterId\" has been provided. Also see: https://docs.getxray.app/display/XRAY/Exporting+Cucumber+Tests+-+REST");
            } else {
                String jqlQuery = "";
                if (!jiraKeys.isEmpty()) {
                    jqlQuery = "key in (" + jiraKeys + ")";
                }
                if (!jiraFilter.isEmpty()) {
                    if (!jqlQuery.isEmpty()) {
                        jqlQuery = jqlQuery.concat(" OR ");
                    }
                    jqlQuery = jqlQuery.concat("filter = " + jiraFilter);
                }
                jqlQuery = URLEncoder.encode(jqlQuery, StandardCharsets.UTF_8);

                try (JiraClient jiraClient = new JiraClient()) {
                    int startAt = 0;
                    int maxResults = 50;
                    int totalResultsRemaining = -1;
                    Gson gson = new Gson();
                    do {
                        String jsonResult = jiraClient.executeHttpGetRequest(
                                JiraClient.JIRA_REST_API_URL + "search?jql=" + jqlQuery + "&startAt=" + startAt + "&maxResults=" + maxResults,
                                AuthenticationHelper.getAuthenticationMethod());
                        CustomAssertions.assertNotNull(jsonResult,
                                "HTTP GET request \"" + JiraClient.JIRA_REST_API_URL + "search?jql=" + jqlQuery + "&startAt=" + startAt + "&maxResults="
                                        + maxResults + "\" returned an empty result!");
                        CustomAssertions.assertEquals(jiraClient.getLastRequestStatusCode(), 200, jsonResult);
                        if (totalResultsRemaining == -1) {
                            totalResultsRemaining = gson.fromJson(jsonResult, JsonObject.class).get("total").getAsInt();
                        }
                        if (totalResultsRemaining / maxResults > 0) {
                            startAt += maxResults;
                            totalResultsRemaining -= maxResults;
                        } else {
                            totalResultsRemaining -= totalResultsRemaining % maxResults;
                        }
                        String testEnvironment = TestProperties.getProperty("test.execution.test.environment", false).map(String.class::cast).orElse("");
                        boolean testEnvironmentOverwritten = TestProperties.isOverwritten("test.execution.test.environment");
                        JsonArray issuesArray = gson.fromJson(jsonResult, JsonObject.class).get("issues").getAsJsonArray();
                        for (JsonElement issue : issuesArray) {
                            String key = issue.getAsJsonObject().get("key").getAsString();
                            String testType = issue.getAsJsonObject().get("fields").getAsJsonObject().get("issuetype").getAsJsonObject().get("name")
                                    .getAsString();
                            if (testType.equals("Testausführung")) {
                                if (!testEnvironmentOverwritten) {
                                    JsonArray testEnvironments = issue.getAsJsonObject().get("fields").getAsJsonObject().get("customfield_10229")
                                            .getAsJsonArray();
                                    if (!testEnvironments.isEmpty()) {
                                        testEnvironment = testEnvironments.get(0).getAsString();
                                    }
                                    if (testEnvironments.size() > 1) {
                                        ScenarioLogManager.getLogger().warn(
                                                "For test execution with key \"{}\" more than one test environment was set. For running containing tests \"{}\" will be used!",
                                                key, testEnvironment);
                                    }
                                }
                                Environment environment = Environment.contains(testEnvironment);
                                if (environment != null) {
                                    TEST_EXECUTION_MAP.putIfAbsent(key, new TestExecution(key, testEnvironment));
                                } else {
                                    ScenarioLogManager.getLogger().warn(
                                            "Test execution with key \"{}\" has no valid test environment. Test execution with environment \"{}\" will be ignored!",
                                            key, testEnvironment);
                                }
                            } else {
                                ScenarioLogManager.getLogger()
                                        .warn("Jira issue with key \"{}\" is not of type test execution. Issues of type \"{}\" will be ignored!", key,
                                                testType);
                            }
                        }
                    } while (totalResultsRemaining > 0);
                    for (String testExecutionIssueKey : TEST_EXECUTION_MAP.keySet()) {
                        Path featuresZipFilePath = Paths.get(
                                TestProperties.getProperty("features", true).orElse("") + "/" + testExecutionIssueKey + "_features.zip").toAbsolutePath()
                                .normalize();
                        if (Files.exists(featuresZipFilePath)) {
                            try {
                                Files.delete(featuresZipFilePath);
                            } catch (IOException e) {
                                ScenarioLogManager.getLogger().error("Could not delete features.zip file!", e);
                                throw new RuntimeException(e);
                            }
                        }
                        jiraClient.executeHttpGetRequestForFile(JiraClient.JIRA_XRAY_REST_API_URL + "export/test?keys=" + testExecutionIssueKey + "&fz=true",
                                featuresZipFilePath, AuthenticationHelper.getAuthenticationMethod());
                        if (jiraClient.getLastRequestStatusCode() != 200) {
                            if (jiraClient.getLastRequestStatusCode() == 400) {
                                ScenarioLogManager.getLogger().warn("No feature files where generated. Please check Jira log.");
                            } else {
                                CustomAssertions.fail(
                                        "Exporting Cucumber tests has failed: " + jiraClient.getLastRequestProtocolInformation() + " "
                                                + jiraClient.getLastRequestStatusCode() + " - " + jiraClient.getLastRequestStatusReasonPhrase());
                            }
                        } else {
                            if (Files.exists(featuresZipFilePath)) {
                                tempFeatureDirectory = featuresZipFilePath.getParent().resolve("./temp/" + testExecutionIssueKey + "/").toAbsolutePath()
                                        .normalize();
                                if (!Files.exists(tempFeatureDirectory)) {
                                    Files.createDirectories(tempFeatureDirectory);
                                }
                                ZipFileUtils.unzipFolder(featuresZipFilePath, tempFeatureDirectory);
                                Files.delete(featuresZipFilePath);
                                cucumberFeaturesExported = true;
                                tempFeatureDirectory = tempFeatureDirectory.getParent();

                                if (AuthenticationHelper.getUserName() == null) {
                                    AuthenticationHelper.setUserName(jiraClient.getCurrentlyLoggedInUserName().toCharArray());
                                }
                            }
                        }
                    }

                    StringBuilder cucumberTags = new StringBuilder();
                    try {
                        cucumberTags.append(System.getProperty("cucumber.filter.tags"));
                    } catch (IllegalArgumentException | NullPointerException ignore) {
                    } finally {
                        if (cucumberTags.toString().equals("null")) {
                            cucumberTags.delete(0, cucumberTags.length() - 1);
                            cucumberTags.setLength(0);
                        }
                    }

                    if (!cucumberTags.isEmpty() && !TEST_EXECUTION_MAP.values().isEmpty()) {
                        cucumberTags.append(" and ");
                    }

                    boolean hasMoreThanOneTestExecution = false;
                    for (TestExecution testExecution : TEST_EXECUTION_MAP.values()) {
                        if (hasMoreThanOneTestExecution) {
                            cucumberTags.append(" or ");
                        }
                        cucumberTags.append("(@")
                                .append(testExecution.ISSUE_KEY);
                        for (int i = 1; i <= testExecution.getNumberOfTests(); i++) {
                            Test test = testExecution.getTestByRank(i);
                            CustomAssertions.assertNotNull(test,
                                    "Test with supposed rank [" + i + "] of test execution [" + testExecution.ISSUE_KEY + "] could not be retrieved!");
                            TestStatus status = test.getStatus();
                            String issueKey = test.ISSUE_KEY;
                            ScenarioLogManager.getLogger().debug("{} : {}", issueKey, status.NAME);
                            if (status.equals(TestStatus.BLOCKIERT)) {
                                ScenarioLogManager.getLogger()
                                        .warn("Test case [{}] in test execution [{}] will be ignored!", issueKey, testExecution.ISSUE_KEY);
                                cucumberTags.append(" and not @")
                                        .append(issueKey);
                            }
                        }
                        cucumberTags.append(')');
                        hasMoreThanOneTestExecution = true;
                    }

                    if (!cucumberTags.isEmpty()) {
                        ScenarioLogManager.getLogger().debug("cucumber.filter.tags={}", cucumberTags);
                        System.setProperty("cucumber.filter.tags", cucumberTags.toString());
                    }
                } catch (Exception e) {
                    CustomAssertions.fail(e.getMessage(), e);
                }
            }
        }
    }

    /**
     * Cleans up the test suite by deleting temporary feature files and importing Cucumber test results
     * into Jira.
     *
     * <p>
     * This method performs the following actions:
     * </p>
     * <ul>
     * <li>Deletes any temporary feature files that were created during the test run.</li>
     * <li>Imports the results of Cucumber tests into the respective Jira test executions if
     * applicable.</li>
     * </ul>
     */
    public static void tearDownTestSuite() {
        // Deleting temporary feature files
        if (tempFeatureDirectory != null && Files.exists(tempFeatureDirectory)) {
            try (Stream<Path> pathStream = Files.walk(tempFeatureDirectory)) {
                pathStream.sorted(Comparator.reverseOrder()).map(Path::toFile)
                        .forEach(file -> CustomAssertions.assertTrue(file.delete(), file.getName() + " could not be deleted!"));
            } catch (IOException e) {
                ScenarioLogManager.getLogger().error(e.getMessage(), e);
            }
        }

        // If any Cucumber tests were run, try to import results to Jira test execution
        Path cucumberJsonFilePath = Paths.get(TestProperties.extractPath(TestProperties.getProperty("plugin", true).map(String.class::cast).orElse(""), "json"))
                .toAbsolutePath();
        if (Files.exists(cucumberJsonFilePath) && cucumberFeaturesExported) {
            if (!(AuthenticationHelper.credentialsHaveNotBeenSet() && AuthenticationHelper.authorizationTokenHasNotBeenSet())) {
                ScenarioLogManager.getLogger().info("Importing Cucumber results to Jira");
                try (JiraClient jiraClient = new JiraClient()) {
                    String cucumberJson = Files.readString(cucumberJsonFilePath);
                    Gson gson = new Gson();
                    for (TestExecution testExecution : TEST_EXECUTION_MAP.values()) {
                        JsonArray cucumberTestsArray = new JsonArray();
                        for (JsonElement jsonElement : gson.fromJson(cucumberJson, JsonArray.class)) {
                            for (JsonElement tag : jsonElement.getAsJsonObject().get("tags").getAsJsonArray()) {
                                if (tag.getAsJsonObject().get("name").getAsString().replace("@", "").equals(testExecution.ISSUE_KEY)) {
                                    cucumberTestsArray.add(jsonElement.getAsJsonObject());
                                    break;
                                }
                            }
                        }
                        if (cucumberTestsArray.isEmpty()) {
                            ScenarioLogManager.getLogger()
                                    .warn("Could not get Cucumber results for test execution \"{}\"! Maybe no tests were executed due to blocked test run status.",
                                            testExecution.ISSUE_KEY);
                        } else {
                            jiraClient.executeHttpPostRequest(JiraClient.JIRA_XRAY_REST_API_URL + "import/execution/cucumber", cucumberTestsArray.toString(),
                                    ContentType.APPLICATION_JSON, AuthenticationHelper.getAuthenticationMethod());
                            CustomAssertions.assertEquals(jiraClient.getLastRequestStatusCode(), 200,
                                    "The results for test execution \"" + testExecution.ISSUE_KEY + "\" could not be imported to Jira!");
                        }
                        testExecution.finish();
                    }
                } catch (IOException e) {
                    ScenarioLogManager.getLogger().error(e.getMessage(), e);
                }
            }
        }

        TestExecutionContext.clear();
        TestDataHelper.flushMapSuiteTestData();
        CryptoUtils.clearSecret();
        AuthenticationHelper.disposeCredentials();
        ScenarioLogManager.clear();
    }
}
