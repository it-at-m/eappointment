package zms.ataf.ui.steps;

import ataf.core.helpers.TestDataHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.web.utils.DriverUtil;
import io.cucumber.java.en.Then;
import io.cucumber.java.en.When;
import zms.ataf.ui.pages.calldisplay.CalldisplayPage;

public class CalldisplaySteps {

    private final CalldisplayPage page;

    public CalldisplaySteps() {
        page = new CalldisplayPage(DriverUtil.getDriver());
    }

    @When("I open the call display for locations {string} with template {string}")
    public void iOpenTheCallDisplay(String scopeList, String template) {
        scopeList = TestDataHelper.transformTestData(scopeList);
        template = TestDataHelper.transformTestData(template);
        ScenarioLogManager.getLogger().info("Call display: open locations {} template {}", scopeList, template);
        page.open(scopeList, template);
    }

    @When("I replace location {string} in the call display address with {string}")
    public void iReplaceLocationInTheCallDisplayAddress(String from, String to) {
        from = TestDataHelper.transformTestData(from);
        to = TestDataHelper.transformTestData(to);
        ScenarioLogManager.getLogger().info("Call display: replace location {} with {}", from, to);
        page.replaceLocation(from, to);
    }

    @When("I reload the call display")
    public void iReloadTheCallDisplay() {
        ScenarioLogManager.getLogger().info("Call display: reload");
        page.reload();
    }

    @Then("the call display should be visible")
    public void theCallDisplayShouldBeVisible() {
        ScenarioLogManager.getLogger().info("Call display: assert visible");
        page.assertVisible();
    }

    @Then("the call display should show {string}")
    public void theCallDisplayShouldShow(String text) {
        text = TestDataHelper.transformTestData(text);
        ScenarioLogManager.getLogger().info("Call display: assert text \"{}\"", text);
        page.assertShows(text);
    }

    @Then("the call display should list location {string}")
    public void theCallDisplayShouldListLocation(String scopeId) {
        scopeId = TestDataHelper.transformTestData(scopeId);
        page.assertListsLocation(scopeId);
    }

    @Then("the call display should not list location {string}")
    public void theCallDisplayShouldNotListLocation(String scopeId) {
        scopeId = TestDataHelper.transformTestData(scopeId);
        page.assertDoesNotListLocation(scopeId);
    }
}
