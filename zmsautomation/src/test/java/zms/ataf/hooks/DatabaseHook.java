package zms.ataf.hooks;

import org.testng.annotations.BeforeSuite;
import org.flywaydb.core.Flyway;

public class DatabaseHook {
    
    @BeforeSuite
    public static void setupDatabase() {
        String dbUrl = String.format("jdbc:mysql://%s:%s/%s",
            System.getenv().getOrDefault("MYSQL_HOST", "db"),
            System.getenv().getOrDefault("MYSQL_PORT", "3306"),
            System.getenv().getOrDefault("MYSQL_DATABASE", "zmsbo"));
        
        Flyway flyway = Flyway.configure()
            .dataSource(dbUrl, 
                System.getenv().getOrDefault("MYSQL_USER", "zmsbo"),
                System.getenv().getOrDefault("MYSQL_PASSWORD", "zmsbo"))
            .locations("classpath:db/migration")
            .cleanDisabled(false)
            .load();
        
        flyway.clean();
        flyway.migrate();
    }
}
