package zms.ataf.helpers;

import java.util.Random;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

/**
 * Utility class for generating random names locally.
 * Provides reliable name generation without external dependencies.
 */
public final class RandomNameHelper {
    private static final Logger logger = LoggerFactory.getLogger(RandomNameHelper.class);
    
    private static final String[] FIRST_NAMES = {
        "Max", "Maria", "Paul", "Anna", "Felix", "Laura", "Lukas", "Sophie",
        "Jonas", "Emma", "Leon", "Mia", "Tim", "Hannah", "Finn", "Lena",
        "Tom", "Lisa", "Elias", "Lea", "Noah", "Sarah", "Ben", "Julia"
    };
    
    private static final String[] LAST_NAMES = {
        "Müller", "Schmidt", "Schneider", "Fischer", "Weber", "Meyer",
        "Wagner", "Becker", "Schulz", "Hoffmann", "Koch", "Bauer",
        "Richter", "Klein", "Wolf", "Schröder", "Neumann", "Braun"
    };
    
    private static final Random random = new Random();
    
    private RandomNameHelper() {
        throw new UnsupportedOperationException("Utility class");
    }
    
    /**
     * Generates a random full name using local name pool.
     * 
     * `@return` A random full name (first name + space + last name + numeric suffix)
     */
    public static String generateRandomName() {
        String firstName = FIRST_NAMES[random.nextInt(FIRST_NAMES.length)];
        String lastName = LAST_NAMES[random.nextInt(LAST_NAMES.length)];
        int suffix = random.nextInt(9999);
        String fullName = firstName + " " + lastName + suffix;
        logger.info("✓ Random name generated locally: {}", fullName);
        return fullName;
    }
    
    /**
     * Generates a random full name (alias for generateRandomName for clarity).
     * 
     * `@return` A random full name with numeric suffix for uniqueness
     */
    public static String generateFallbackRandomName() {
        return generateRandomName();
    }
    
    /**
     * Generates a simple random name for API tests.
     * Uses timestamp-based approach for guaranteed uniqueness.
     *
     * `@return` A simple unique name
     */
    public static String generateSimpleRandomName() {
        long timestamp = System.currentTimeMillis();
        return "TestUser" + timestamp;
    }
    
    /**
     * Converts a name to an email-safe format.
     * Removes special characters, umlauts, and spaces.
     *
     * `@param` name The name to convert
     * `@return` Email-safe version of the name (lowercase, alphanumeric + underscore)
     */
    public static String getEmailConformName(String name) {
        if (name == null || name.isEmpty()) {
            return "testuser";
        }
        
        return name.toLowerCase()
                   .replace("ä", "ae")
                   .replace("ö", "oe")
                   .replace("ü", "ue")
                   .replace("ß", "ss")
                   .replaceAll("[^a-z0-9]", "_")
                   .replaceAll("_+", "_")  // Replace multiple underscores with single
                   .replaceAll("^_|_$", "");  // Remove leading/trailing underscores
    }
}