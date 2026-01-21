package ataf.core.reader;

import ataf.core.data.Environment;
import ataf.core.data.TestUser;
import ataf.core.data.UserType;
import ataf.core.helpers.TestDataHelper;
import ataf.core.logging.ScenarioLogManager;
import ataf.core.reader.resolver.TestUserResolver;

import java.nio.file.Files;
import java.nio.file.Path;
import java.util.ArrayList;
import java.util.List;

/**
 * A variation of {@link AbstractTestUserReader} that reads CSV files where the username, password,
 * and user type can be in distinct columns. This class
 * supports:
 * <ul>
 * <li>Reading a CSV file using a specified column separator (e.g., comma).</li>
 * <li>Mapping username, password, and user type to configurable column indices.</li>
 * <li>Fallback user-type resolution via {@link TestUserResolver} if the CSV's user-type
 * column is empty or requires additional logic.</li>
 * <li>Encrypting passwords before creating each {@link TestUser} instance.</li>
 * </ul>
 *
 * <p>
 * <strong>Example of a three-column CSV structure:</strong>
 * </p>
 *
 * <pre>{@code
 * username,password,userType
 * john.doe,myPassword,ADMIN
 * jane.smith,secret,END_USER
 * }</pre>
 *
 * <p>
 * <strong>Constructor Usage:</strong>
 * </p>
 *
 * <pre>{@code
 * // Using default CSV separator (comma) with known column indices
 * CsvTestUserReader reader = new CsvTestUserReader(
 *         myResolver, ",", 0, 1, 2);
 * }</pre>
 *
 * <p>
 * This class is thread-safe in the sense that reading from a file and processing
 * chunks is handled by methods in {@link AbstractTestUserReader}, which uses concurrency.
 * However, care must be taken if multiple threads create {@code CsvTestUserReader}
 * instances concurrently or modify shared resources.
 * </p>
 *
 * <p>
 * When the CSV line is parsed, columns are validated against the highest index in use.
 * If any required column is missing, the line is skipped. Otherwise, the column values
 * are trimmed and resolved for username, password, and user type. Null values are skipped.
 * </p>
 *
 * <p>
 * <strong>Note:</strong> If {@code userTypeColumnIndex} is <code>-1</code> in the constructor,
 * the CSV does not contain a user-type column; in that case, the {@link TestUserResolver}
 * implementation must fully determine the user type.
 * </p>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class CsvTestUserReader extends AbstractTestUserReader {

    /**
     * The separator used to split columns within a CSV line (e.g., comma).
     */
    private final String columnSeparator;

    /**
     * A resolver for mapping raw CSV values to usernames, passwords, and {@link UserType}s.
     */
    private final TestUserResolver testUserResolver;

    /**
     * The zero-based index of the column holding the username.
     */
    private final int usernameColumnIndex;

    /**
     * The zero-based index of the column holding the password.
     */
    private final int passwordColumnIndex;

    /**
     * The zero-based index of the column holding the user type (if any). If this is set to
     * <code>-1</code>, the user type is determined solely by the
     * resolver.
     */
    private final int userTypeColumnIndex;

    /**
     * Creates a new reader that parses CSV files using the given column indices for username, password,
     * and user type.
     *
     * @param testUserResolver a {@link TestUserResolver} used to resolve or refine user details
     * @param columnSeparator the delimiter used to split columns in the CSV file (e.g., ",")
     * @param usernameColumnIndex the zero-based index of the column holding the username
     * @param passwordColumnIndex the zero-based index of the column holding the password
     * @param userTypeColumnIndex the zero-based index of the column holding the user type, or
     *            <code>-1</code> if the user type should be resolved purely by
     *            {@code testUserResolver}
     */
    public CsvTestUserReader(TestUserResolver testUserResolver, String columnSeparator, int usernameColumnIndex, int passwordColumnIndex,
            int userTypeColumnIndex) {
        this.testUserResolver = testUserResolver;
        this.columnSeparator = columnSeparator;
        this.usernameColumnIndex = usernameColumnIndex;
        this.passwordColumnIndex = passwordColumnIndex;
        this.userTypeColumnIndex = userTypeColumnIndex;
    }

    /**
     * A convenience constructor that defaults to using a comma (<code>","</code>) as the column
     * separator and assumes username, password, and user type are
     * stored in columns
     * <code>0</code>, <code>1</code>, and <code>2</code>, respectively.
     *
     * @param testUserResolver a {@link TestUserResolver} used to resolve or refine user details
     */
    public CsvTestUserReader(
            TestUserResolver testUserResolver) {
        this(testUserResolver, ",", 0, 1, 2);
    }

    /**
     * Reads all lines from the specified CSV file path. This implementation simply delegates to
     * {@link Files#readAllLines(Path)}.
     *
     * @param filePath the path of the CSV file
     * @return a list of strings, each corresponding to one line in the file
     * @throws Exception if an I/O error occurs while reading lines from the file
     */
    @Override
    protected List<String> readRawData(Path filePath) throws Exception {
        return Files.readAllLines(filePath);
    }

    /**
     * Processes a chunk of CSV data, where each line is split into columns using
     * {@link #columnSeparator}. It maps these columns to username, password, and
     * (optionally) user type fields. If the user type column is <code>-1</code> or empty, the type is
     * determined by the {@link TestUserResolver}.
     *
     * @param chunk the list of raw CSV lines to be parsed within this chunk
     * @param environment the {@link Environment} in which these users exist
     * @return a list of {@link TestUser} objects created from the processed lines
     */
    @Override
    protected List<TestUser> processChunk(List<String> chunk, Environment environment) {
        List<TestUser> users = new ArrayList<>();

        for (String line : chunk) {
            // Split the CSV line into columns
            String[] columns = line.split(columnSeparator);

            // Basic validation: we need at least as many columns as the largest index we reference
            int maxRequiredIndex = Math.max(Math.max(usernameColumnIndex, passwordColumnIndex), userTypeColumnIndex);
            if (columns.length <= maxRequiredIndex && userTypeColumnIndex != -1 || columns.length <= Math.max(usernameColumnIndex, passwordColumnIndex)) {
                ScenarioLogManager.getLogger().warn("Skipping line with insufficient columns: {}", line);
                continue;
            }

            String username = testUserResolver.resolveName(columns[usernameColumnIndex].trim());
            if (username == null) {
                ScenarioLogManager.getLogger().warn("Skipping line because username resolved to null: {}", line);
                continue;
            }

            String password = testUserResolver.resolvePassword(columns[passwordColumnIndex].trim());
            if (password == null) {
                ScenarioLogManager.getLogger().warn("Skipping line because password resolved to null: {}", line);
                continue;
            }

            UserType userType = testUserResolver.resolveType(columns[userTypeColumnIndex].trim());
            if (userType == null) {
                ScenarioLogManager.getLogger().warn("Skipping line because user type resolved to null: {}", line);
                continue;
            }

            // Create the TestUser object
            TestUser user = new TestUser(username, TestDataHelper.encryptTestData(password), environment, userType);
            users.add(user);
        }

        return users;
    }
}
