package ataf.example.steps;

import ataf.core.clients.HttpClient;
import ataf.core.clients.JiraClient;
import ataf.core.logging.ScenarioLogManager;
import io.cucumber.java.de.Angenommen;
import io.cucumber.java.de.Dann;
import io.cucumber.java.de.Wenn;
import org.testng.Assert;

import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;

/**
 * @author Ludwig Haas (ex.haas02)
 *
 *         Zeigt wie man über die
 *         <a href="https://docs.atlassian.com/software/jira/docs/api/REST/9.9.0/#api/">Jira REST
 *         API</a> JSON Daten auslesen kann.
 */
public class JiraRestSteps {
    private JiraClient.AuthenticationMethod authenticationMethod;

    @Angenommen("^man hat sich erfolgreich mit (Benutzername und Passwort|Access Token) authentifiziert\\.$")
    public void angenommenManHatSichErfolgreichMitBenutzernameUndPasswortAuthentifiziert(String authenticationMethod) {
        switch (authenticationMethod) {
            case "Benutzername und Passwort":
                this.authenticationMethod = HttpClient.AuthenticationMethod.BasicAuth;
                break;
            case "Access Token":
                this.authenticationMethod = HttpClient.AuthenticationMethod.AccessToken;
                break;
            default:
                this.authenticationMethod = JiraClient.AuthenticationMethod.None;
        }
    }

    /**
     * Holt sämtliche Informationen des Vorgangs. Hier kann man zum Beispiel die project id finde.
     */
    @Wenn("man nun über die Jira REST API die Daten zu Vorgang {string} anfragt.")
    public void wennManNunUeberDieJiraRESTAPIDieDatenZuVorgangAusliest(String jiraIssueKey) {
        try (JiraClient jiraClient = new JiraClient()) {
            String jsonResult = jiraClient.executeHttpGetRequest(JiraClient.JIRA_REST_API_URL + "issue/" + jiraIssueKey,
                    authenticationMethod);
            Assert.assertNotNull(jsonResult,
                    "HTTP GET request \"" + JiraClient.JIRA_REST_API_URL + "issue/" + jiraIssueKey + "\" returned an empty result!");
            Files.writeString(Paths.get("./src/test/resources/" + jiraIssueKey + ".json").toAbsolutePath(), jsonResult, StandardCharsets.UTF_8);
            Assert.assertEquals(jiraClient.getLastRequestStatusCode(), 200, jsonResult);
        } catch (Exception e) {
            ScenarioLogManager.getLogger().error(e.getMessage(), e);
        }
    }

    /**
     * Holt Informationen zu den transitions, das sind die Status-Pfade, die ein Vorgang annehmen kann.
     * Hier kann man die ids zu dem Status in progress und done
     * ablesen.
     */
    @Wenn("man nun über die Jira REST API die Status-Übergänge zu Vorgang {string} anfragt.")
    public void wennManNunUeberDieJiraRESTAPIDieStatusUebergaengeZuVorgangAnfragt(String jiraIssueKey) {
        try (JiraClient jiraClient = new JiraClient()) {
            String jsonResult = jiraClient.executeHttpGetRequest(JiraClient.JIRA_REST_API_URL + "issue/" + jiraIssueKey + "/transitions",
                    authenticationMethod);
            Assert.assertNotNull(jsonResult,
                    "HTTP GET request \"" + JiraClient.JIRA_REST_API_URL + "issue/" + jiraIssueKey + "/transitions\" returned an empty result!");
            Files.writeString(Paths.get("./src/test/resources/" + jiraIssueKey + "_transition.json").toAbsolutePath(), jsonResult, StandardCharsets.UTF_8);
            Assert.assertEquals(jiraClient.getLastRequestStatusCode(), 200, jsonResult);
        } catch (Exception e) {
            ScenarioLogManager.getLogger().error(e.getMessage(), e);
        }
    }

    @Dann("sollte man eine Datei mit Namen {string} erhalten haben.")
    public void dannSollteManEineDateiMitNamenErhaltenHaben(String fileName) {
        boolean result = false;
        Path path = Paths.get("./src/test/resources/" + fileName).toAbsolutePath();
        try {
            result = Files.exists(path);
        } catch (Exception e) {
            Assert.fail(e.getMessage(), e);
        }
        Assert.assertTrue(result, "File does not exist in path: " + path);
    }
}
