package zms.ataf.hooks;

import org.flywaydb.core.Flyway;
import org.testng.annotations.BeforeSuite;

public class DatabaseHook {
    
    @BeforeSuite
    public static void setupDatabase() {
        final String dbName = System.getenv().getOrDefault("MYSQL_DATABASE", "zmsbo");
        final boolean allowFlywayClean = Boolean.parseBoolean(System.getenv().getOrDefault("ALLOW_FLYWAY_CLEAN", "false"));
        // Safety: Flyway clean drops all objects. We only allow it by default for the known test DB name.
        final boolean looksLikeExpectedTestDb = "zmsbo".equalsIgnoreCase(dbName);
        final boolean shouldAllowFlywayClean = allowFlywayClean || looksLikeExpectedTestDb;

        String dbUrl = String.format("jdbc:mysql://%s:%s/%s",
            System.getenv().getOrDefault("MYSQL_HOST", "db"),
            System.getenv().getOrDefault("MYSQL_PORT", "3306"),
            dbName);
        
        Flyway flyway = Flyway.configure()
            .dataSource(dbUrl, 
                System.getenv().getOrDefault("MYSQL_USER", "zmsbo"),
                System.getenv().getOrDefault("MYSQL_PASSWORD", "zmsbo"))
            .locations("classpath:db/migration")
            .cleanDisabled(!shouldAllowFlywayClean)
            .load();
        
        if (shouldAllowFlywayClean) {
            flyway.clean();
        } else {
            System.err.println(
                    "[DatabaseHook] Skipping Flyway clean for MYSQL_DATABASE=\"" + dbName
                            + "\". Set ALLOW_FLYWAY_CLEAN=true to override (still running migrate).");
        }
        flyway.migrate();
    }
}
