package zms.ataf.ui.steps;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.SQLException;

import ataf.core.logging.ScenarioLogManager;
import ataf.web.utils.DriverUtil;
import io.cucumber.java.After;
import io.cucumber.java.en.Then;
import io.cucumber.java.en.When;
import zms.ataf.ui.pages.admin.AdminPage;
import zms.ataf.ui.pages.admin.ChangelogPage;

public class ChangelogSteps {

    private static final String RELEASE_NOTES_ROW = "changelog__urlReleaseNotes";

    private final ChangelogPage page;
    private boolean releaseNotesChanged;

    public ChangelogSteps() {
        page = new ChangelogPage(DriverUtil.getDriver());
    }

    @Then("the version in the administration footer is not a link")
    public void theVersionInTheAdministrationFooterIsNotALink() {
        page.assertVersionIsNotALink();
    }

    @Then("the administration footer links to the changelog as {string}")
    public void theAdministrationFooterLinksToTheChangelogAs(String label) {
        if (!"Änderungshistorie".equals(label)) {
            throw new IllegalArgumentException("Unexpected changelog label.");
        }
        page.assertHistoryLink();
    }

    @When("I open the system configuration.")
    public void iOpenTheSystemConfiguration() {
        page.openSystemConfiguration();
    }

    @Then("the release notes link is {string}")
    public void theReleaseNotesLinkIs(String url) {
        page.assertReleaseNotesLink(url);
    }

    @When("I set the release notes link to {string}")
    public void iSetTheReleaseNotesLinkTo(String url) {
        releaseNotesChanged = true;
        page.setReleaseNotesLink(url);
    }

    @When("I open the changelog from the administration footer.")
    public void iOpenTheChangelogFromTheAdministrationFooter() {
        page.openChangelog();
    }

    @Then("the changelog introduction links to {string}")
    public void theChangelogIntroductionLinksTo(String url) {
        page.assertIntroductionLinksTo(url);
    }

    @After(value = "@ZMSKVR-1479", order = 10002)
    public void restoreDefaultReleaseNotesLink() {
        if (!releaseNotesChanged) {
            return;
        }
        try {
            new AdminPage(DriverUtil.getDriver()).navigateToPage();
            page.openSystemConfiguration();
            page.setReleaseNotesLink(ChangelogPage.DEFAULT_RELEASE_NOTES);
        } catch (RuntimeException | AssertionError uiFailure) {
            ScenarioLogManager.getLogger().warn("Restoring the release notes link in the database.", uiFailure);
            restoreReleaseNotesLinkInDatabase();
        }
    }

    private static void restoreReleaseNotesLinkInDatabase() {
        String sql = "UPDATE config SET value = ? WHERE name = ?";
        try (Connection connection = openZmsConnection();
                PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setString(1, ChangelogPage.DEFAULT_RELEASE_NOTES);
            statement.setString(2, RELEASE_NOTES_ROW);
            int updated = statement.executeUpdate();
            if (updated != 1) {
                throw new IllegalStateException("The release notes link was not restored.");
            }
        } catch (SQLException e) {
            throw new IllegalStateException("Could not restore the release notes link.", e);
        }
    }

    private static Connection openZmsConnection() throws SQLException {
        String host = envOrDefault("MYSQL_HOST", "db");
        String port = mysqlPort(envOrDefault("MYSQL_PORT", "3306"));
        String database = envOrDefault("MYSQL_DATABASE", "db");
        String user = envOrDefault("MYSQL_USER", "db");
        return DriverManager.getConnection(
                "jdbc:mysql://" + host + ":" + port + "/" + database,
                user,
                envOrDefault("MYSQL_PASSWORD", "db"));
    }

    private static String mysqlPort(String raw) {
        int colon = raw.lastIndexOf(':');
        if (colon >= 0 && colon < raw.length() - 1) {
            return raw.substring(colon + 1);
        }
        return raw;
    }

    private static String envOrDefault(String name, String fallback) {
        String value = System.getenv(name);
        if (value == null || value.isBlank()) {
            return fallback;
        }
        return value.trim();
    }
}
