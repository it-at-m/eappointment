package ataf.core.utils;

import java.io.FileInputStream;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.StandardCopyOption;
import java.util.zip.ZipEntry;
import java.util.zip.ZipInputStream;

/**
 * Utility class for handling zip file operations, including extracting the contents of zip files to
 * a specified target directory. This class also includes
 * functionality to protect against zip slip vulnerabilities.
 *
 * <p>
 * This class provides methods to unzip files and to validate zip entry paths.
 * </p>
 *
 * <p>
 * Author: Ludwig Haas (ex.haas02)
 * </p>
 */
public class ZipFileUtils {

    /**
     * Extracts the contents of a zip file to a specified target directory.
     *
     * <p>
     * This method reads a zip file from the specified source path and extracts its contents, creating
     * necessary directories in the target location. It checks
     * for the zip slip vulnerability to prevent unintended file access outside the target directory.
     * </p>
     *
     * @param source the full file path of the source zip file.
     * @param target the target path of the directory to which the zip contents should be extracted.
     * @throws IOException if an I/O error occurs during extraction, including issues with file paths or
     *             zip structure.
     */
    public static void unzipFolder(Path source, Path target) throws IOException {
        try (ZipInputStream zis = new ZipInputStream(new FileInputStream(source.toFile()))) {
            ZipEntry zipEntry = zis.getNextEntry();
            while (zipEntry != null) {
                Path newPath = zipSlipProtect(zipEntry, target);
                if (zipEntry.isDirectory() || zipEntry.getName().endsWith("/")) {
                    // Create directories for the entry
                    Files.createDirectories(newPath);
                } else {
                    // Ensure parent directories exist
                    if (newPath.getParent() != null && Files.notExists(newPath.getParent())) {
                        Files.createDirectories(newPath.getParent());
                    }
                    // Copy the file entry to the target location
                    Files.copy(zis, newPath, StandardCopyOption.REPLACE_EXISTING);
                }
                zipEntry = zis.getNextEntry();
            }
            zis.closeEntry();
        }
    }

    /**
     * Validates a zip entry to protect against zip slip vulnerabilities.
     *
     * <p>
     * This method checks if the normalized path of the zip entry remains within the target directory.
     * If the path is outside the target directory, an
     * IOException is thrown.
     * </p>
     *
     * @param zipEntry the zip entry representing a file or directory within the zip archive.
     * @param targetDir the target path of the directory to which the zip contents should be extracted.
     * @return the normalized path of the zip entry within the target directory.
     * @throws IOException if the zip entry's path is invalid and potentially leads to a zip slip
     *             attack.
     */
    public static Path zipSlipProtect(ZipEntry zipEntry, Path targetDir) throws IOException {
        // Remove leading slashes to prevent absolute paths
        String entryName = zipEntry.getName().replaceFirst("^/+", "");
        // Resolve the entry path
        Path resolvedPath = targetDir.resolve(entryName).normalize();
        // Ensure the normalized path is within the target directory
        if (!resolvedPath.startsWith(targetDir)) {
            throw new IOException("Bad zip entry: " + zipEntry.getName());
        }
        return resolvedPath;
    }
}
