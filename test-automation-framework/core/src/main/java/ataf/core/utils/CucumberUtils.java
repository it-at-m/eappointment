package ataf.core.utils;

import ataf.core.context.ScenarioContext;
import ataf.core.helpers.TestDataHelper;
import ataf.core.logging.ScenarioLogManager;
import org.apache.logging.log4j.ThreadContext;

import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;

/**
 * Utility class providing methods to attach test-related artifacts to the current Cucumber
 * scenario.
 * <p>
 * This class includes methods to facilitate logging and attaching test data or log files to the
 * test execution context. The utilities streamline the process of
 * enriching test reports with valuable evidence.
 * </p>
 *
 * <p>
 * <b>Usage:</b> Both methods in this class are static and can be invoked directly without creating
 * an instance.
 * </p>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class CucumberUtils {
    /**
     * Attaches the test data to the current scenario if available.
     * <p>
     * This method retrieves test data as a string, converts it to bytes, and attaches it to the current
     * scenario in the report. It also logs the attachment
     * process.
     */
    public static void attachTestData() {
        try {
            String testDataString = TestDataHelper.getTestData();
            if (!testDataString.isEmpty()) {
                final byte[] testData = testDataString.getBytes(StandardCharsets.UTF_8);
                ScenarioContext.get().attach(testData, "text/plain;charset=utf-8", "Test_Daten.txt");
                ScenarioContext.get().log("Saved test data to \"Test_Daten.txt\"");
            }
        } catch (Exception e) {
            ScenarioLogManager.getLogger().error("Attaching of test data has failed,", e);
        }
    }

    /**
     * Attaches a log file as evidence to the current test scenario context.
     * <p>
     * This method retrieves the log file associated with the current test scenario (based on the thread
     * context key), reads its content, and attaches it as a
     * Base64-encoded file to the test execution using the `ScenarioContext`.
     * </p>
     *
     * <p>
     * Additionally, it logs a message indicating successful attachment of the log file. If the log file
     * does not exist or any error occurs during the process,
     * an error message is logged.
     * </p>
     *
     * <p>
     * <b>Note:</b> This method is static, meaning it can be called without an instance of the class.
     * </p>
     *
     * @throws RuntimeException for any issues during file reading or attaching the file to the test
     *             context.
     */
    public static void attachLogFileAsEvidence() {
        String logFileName = ThreadContext.get(ScenarioLogManager.THREAD_CONTEXT_KEY);
        if (logFileName != null && !logFileName.isEmpty()) {
            Path logFilePath = Paths.get("logs/" + logFileName + ".log");
            if (Files.exists(logFilePath)) {
                try {
                    final byte[] testData = Files.readString(logFilePath).getBytes(StandardCharsets.UTF_8);
                    ScenarioContext.get().attach(testData, "text/plain;charset=utf-8", logFileName + ".log");
                    ScenarioContext.get().log("Attached log file to \"" + logFileName + ".log\"");
                } catch (Exception e) {
                    ScenarioLogManager.getLogger().error("Attaching log file has failed!", e);
                }
            }
        }
    }
}
