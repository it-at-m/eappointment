package ataf.core.reader;

import ataf.core.data.Environment;
import ataf.core.data.TestUser;
import ataf.core.logging.ScenarioLogManager;

import java.nio.file.Path;
import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.Future;

/**
 * Serves as an abstract base class for reading {@link TestUser} data from various file formats or
 * data sources, implementing the {@link TestUserReader}
 * interface. This class defines a general workflow for:
 * <ol>
 * <li>Reading raw data from a provided {@link Path} via the abstract
 * {@link #readRawData(Path)} method, which must be implemented by subclasses.</li>
 * <li>Concurrently processing the raw data into {@code TestUser} objects by splitting
 * it into chunks with {@link #processDataInParallel(List, Environment)}.</li>
 * <li>Handling per-chunk transformations with the abstract
 * {@link #processChunk(List, Environment)} method, which must be
 * implemented by subclasses.</li>
 * </ol>
 * <p>
 * Subclasses can adapt this mechanism to support specific file formats such as CSV, JSON,
 * or XML, by implementing the hook methods.
 * </p>
 *
 * <p>
 * <strong>Usage Example:</strong>
 * </p>
 *
 * <pre>
 * public class CsvTestUserReader extends AbstractTestUserReader {
 *     &#64;Override
 *     protected List&lt;String&gt; readRawData(Path filePath) throws IOException {
 *         // Implementation for reading CSV lines
 *     }
 *
 *     &#64;Override
 *     protected List&lt;TestUser&gt; processChunk(List&lt;String&gt; chunk,
 *             Environment environment) {
 *         // Convert CSV lines in the chunk into TestUser objects
 *     }
 * }
 * </pre>
 *
 * <p>
 * <strong>Thread Safety:</strong>
 * </p>
 * <ul>
 * <li>The parallel processing in {@link #processDataInParallel(List, Environment)}
 * uses a fixed thread pool sized to the number of available processors.</li>
 * <li>Any shared resources accessed by subclasses must be carefully managed
 * to avoid concurrency issues.</li>
 * </ul>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public abstract class AbstractTestUserReader implements TestUserReader {

    /**
     * Reads {@link TestUser} objects from the given file path in the specified {@link Environment},
     * delegating the actual file reading to
     * {@link #readRawData(Path)} and then processing the results in parallel through
     * {@link #processDataInParallel(List, Environment)}.
     *
     * @param filePath the file path from which to read the raw data
     * @param environment the environment associated with the {@link TestUser} objects
     * @return a list of {@code TestUser} objects
     * @throws Exception if an error occurs while reading or parsing the data
     */
    @Override
    public List<TestUser> readTestUsers(Path filePath, Environment environment) throws Exception {
        List<String> rawData = readRawData(filePath); // delegates to format-specific reading
        return processDataInParallel(rawData, environment);
    }

    /**
     * Reads and returns raw data in the form of strings (such as lines or records) from the specified
     * file path. This method is intended to be implemented by
     * subclasses, allowing them to define the format-specific logic (e.g., CSV, JSON, or XML parsing).
     *
     * @param filePath the file path from which to read the raw data
     * @return a list of strings representing the raw data
     * @throws Exception if an error occurs while reading or parsing the file
     */
    protected abstract List<String> readRawData(Path filePath) throws Exception;

    /**
     * Splits the provided raw data into multiple chunks and processes each chunk in parallel to convert
     * lines or records into {@link TestUser} objects.
     *
     * <div>By default, the number of threads corresponds to the number of available processors on the
     * system, as returned by
     * {@code Runtime.getRuntime().availableProcessors()}. The method collects the resulting
     * {@code TestUser} objects from each chunk and aggregates them into a
     * single list.</div>
     *
     * @param rawData a list of strings representing the raw data to be processed
     * @param environment the environment associated with the {@link TestUser} objects
     * @return a list of {@code TestUser} objects created from the raw data
     */
    protected List<TestUser> processDataInParallel(List<String> rawData, Environment environment) {
        List<TestUser> testUsers = new ArrayList<>();
        try {
            // Use a thread pool for parallel processing
            int numThreads = Runtime.getRuntime().availableProcessors();
            ExecutorService executor = Executors.newFixedThreadPool(numThreads);

            // Split lines/records into chunks for each thread
            int chunkSize = (int) Math.ceil((double) rawData.size() / numThreads);
            List<Future<List<TestUser>>> futures = new ArrayList<>();

            for (int i = 0; i < rawData.size(); i += chunkSize) {
                int start = i;
                int end = Math.min(rawData.size(), start + chunkSize);

                // Submit a task to process each chunk
                futures.add(executor.submit(() -> processChunk(rawData.subList(start, end), environment)));
            }

            // Collect results from all threads
            for (Future<List<TestUser>> future : futures) {
                testUsers.addAll(future.get());
            }

            executor.shutdown();
        } catch (Exception e) {
            ScenarioLogManager.getLogger().warn("Exception [{}] caught while reading TestUsers", e.getMessage(), e);
        }
        return testUsers;
    }

    /**
     * Processes a subset of the raw data (a chunk) and converts each record into a {@link TestUser}
     * object. This method is intended to be implemented by
     * subclasses to provide format-specific transformation logic.
     *
     * @param chunk a subset of the raw data to be processed
     * @param environment the environment associated with the {@link TestUser} objects
     * @return a list of {@code TestUser} objects representing the processed data
     */
    protected abstract List<TestUser> processChunk(List<String> chunk, Environment environment);
}
