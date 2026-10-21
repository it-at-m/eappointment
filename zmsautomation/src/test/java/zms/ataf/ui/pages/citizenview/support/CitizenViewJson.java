package zms.ataf.ui.pages.citizenview.support;

import org.openqa.selenium.JavascriptExecutor;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;

import ataf.web.utils.DriverUtil;
import zms.ataf.ui.pages.citizenview.CitizenViewPageContext;

/**
 * Runs JSON-returning scripts against the citizenview shadow DOM toolkit.
 */
public final class CitizenViewJson {

    private final CitizenViewPageContext context;

    public CitizenViewJson(CitizenViewPageContext context) {
        this.context = context;
    }

    public JsonNode citizenJson(String expression, Object... args) {
        context.set();
        // An IIFE has its own arguments object, so callers read the script arguments from __args.
        String script = CitizenViewScripts.CITIZEN_DOM + "var __args=arguments;return JSON.stringify(" + expression + ");";
        Object raw = ((JavascriptExecutor) DriverUtil.getDriver()).executeScript(script, args);
        try {
            return new ObjectMapper().readTree(raw == null ? "null" : String.valueOf(raw));
        } catch (Exception e) {
            throw new AssertionError("Could not read the citizen view: " + raw, e);
        }
    }

}
