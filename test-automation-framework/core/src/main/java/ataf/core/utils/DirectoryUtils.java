package ataf.core.utils;

import java.io.IOException;
import java.nio.file.FileVisitResult;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.SimpleFileVisitor;
import java.nio.file.attribute.BasicFileAttributes;

/**
 * Utility class providing methods for directory operations.
 *
 * <p>
 * This class contains static methods to perform operations on directories, such as deleting a
 * directory and all its contents recursively.
 * </p>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class DirectoryUtils {

    /**
     * Deletes the specified directory and all of its contents recursively.
     *
     * <p>
     * This method walks through the file tree rooted at the given path, deleting each file and
     * subdirectory encountered during the traversal. The root
     * directory itself is also deleted at the end of the operation.
     * </p>
     *
     * @param path the path to the directory to delete
     * @throws IOException if an I/O error occurs during deletion
     * @throws IllegalArgumentException if the provided path does not exist or is not a directory
     */
    public static void deleteDirectoryContents(Path path) throws IOException {
        if (Files.exists(path) && Files.isDirectory(path)) {
            Files.walkFileTree(path, new SimpleFileVisitor<>() {
                /**
                 * Invoked for a file in a directory.
                 *
                 * @param file the file to visit
                 * @param attrs the file's basic attributes
                 * @return {@code FileVisitResult.CONTINUE} to continue visiting
                 * @throws IOException if an I/O error occurs
                 */
                @Override
                public FileVisitResult visitFile(Path file, BasicFileAttributes attrs) throws IOException {
                    Files.delete(file);
                    return FileVisitResult.CONTINUE;
                }

                /**
                 * Invoked for a directory after entries in the directory, and all of their descendants, have been
                 * visited.
                 *
                 * @param dir the directory about to be visited
                 * @param e {@code null} if the iteration of the directory completes without an error; otherwise the
                 *            I/O exception that caused the iteration of the directory to complete prematurely
                 * @return {@code FileVisitResult.CONTINUE} to continue visiting
                 * @throws IOException if an I/O error occurs
                 */
                @Override
                public FileVisitResult postVisitDirectory(Path dir, IOException e) throws IOException {
                    if (e != null) throw e; // Handle exception if directory deletion fails
                    Files.delete(dir);
                    return FileVisitResult.CONTINUE;
                }
            });
        } else {
            throw new IllegalArgumentException("The provided path is not a directory or does not exist.");
        }
    }
}
