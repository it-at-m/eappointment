package zms.ataf.helpers;

import java.util.Random;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import ataf.web.pages.RandomNameGenerator;
import ataf.web.utils.DriverUtil;

/**
 * Utility class for generating random names with fallback mechanism.
 * Provides a robust name generation that attempts to use ATAF's RandomNameGenerator
 * and falls back to local generation if that fails.
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
     * Generates a random full name using ATAF's RandomNameGenerator with fallback.
     * This method is for UI tests that have access to WebDriver.
     *
     * `@return` A random full name (first name + space + last name + optional numeric suffix)
     */
    public static String generateRandomName() {
        try {
            RandomNameGenerator randomNameGenerator = new RandomNameGenerator(DriverUtil.getDriver());
            randomNameGenerator.setRandomName();
            String name = randomNameGenerator.getName() + " " + randomNameGenerator.getSurname();
            logger.info("✓ Random name generated via ATAF: {}", name);
            return name;
        } catch (Exception e) {
            String fallbackName = generateFallbackRandomName();
            logger.warn("⚠ ATAF RandomNameGenerator failed, using fallback: {} (Reason: {})", 
                       fallbackName, e.getMessage());
            return fallbackName;
        }
    }
    
    /**
     * Generates a random full name locally without external dependencies.
     * This method can be used in both UI and API tests.
     *
     * `@return` A random full name with numeric suffix for uniqueness
     */
    public static String generateFallbackRandomName() {
        String firstName = FIRST_NAMES[random.nextInt(FIRST_NAMES.length)];
        String lastName = LAST_NAMES[random.nextInt(LAST_NAMES.length)];
        int suffix = random.nextInt(9999);
        return firstName + " " + lastName + suffix;
    }
    
    /**
     * Generates a simple random name for API tests without WebDriver dependency.
     * Uses timestamp-based approach for guaranteed uniqueness.
     *
     * `@return` A simple unique name
     */
    public static String generateSimpleRandomName() {
        long timestamp = System.currentTimeMillis();
        return "Test User" + timestamp;
    }
}