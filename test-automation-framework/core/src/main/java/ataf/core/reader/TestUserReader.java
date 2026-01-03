package ataf.core.reader;

import ataf.core.data.Environment;
import ataf.core.data.TestUser;

import java.nio.file.Path;
import java.util.List;

/**
 * Defines a reader interface for parsing test users from a specified data source, typically
 * represented by a file path. Implementations are expected to read
 * and construct {@link TestUser} objects based on the provided {@link Environment}.
 *
 * <p>
 * This allows for externalized user data, enabling the creation of test users through different
 * file formats or storage mechanisms without tightly coupling the
 * code to a specific data retrieval method.
 * </p>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public interface TestUserReader {

    /**
     * Reads a list of {@link TestUser} objects from the specified file path within the given
     * {@link Environment}.
     *
     * @param filePath the path to the file containing user data
     * @param environment the environment that these test users belong to
     * @return a list of {@code TestUser} instances
     * @throws Exception if there is an issue reading or parsing the user data
     */
    List<TestUser> readTestUsers(Path filePath, Environment environment) throws Exception;
}
