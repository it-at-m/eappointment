package ataf.core.logging;

import ataf.core.context.ScenarioContext;
import ataf.core.context.TestExecutionContext;
import ataf.core.properties.DefaultValues;
import ataf.core.properties.TestProperties;
import org.apache.logging.log4j.Level;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.apache.logging.log4j.ThreadContext;
import org.apache.logging.log4j.core.Appender;
import org.apache.logging.log4j.core.Filter;
import org.apache.logging.log4j.core.LoggerContext;
import org.apache.logging.log4j.core.appender.RollingFileAppender;
import org.apache.logging.log4j.core.appender.rolling.TimeBasedTriggeringPolicy;
import org.apache.logging.log4j.core.config.Configuration;
import org.apache.logging.log4j.core.layout.PatternLayout;
import org.apache.logging.log4j.util.StackLocatorUtil;

import java.nio.charset.StandardCharsets;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;

/**
 * Manages thread-specific loggers for scenarios, ensuring that each thread writes to a unique log
 * file.
 * <p>
 * This class dynamically creates and manages log appenders based on the scenario or thread context.
 * It handles resource cleanup to prevent memory leaks in
 * long-running applications.
 * <p>
 * Thread-safe methods are provided to ensure correct behavior in multi-threaded environments.
 *
 * <p>
 * Usage:
 * <ul>
 * <li>Retrieve a logger using {@link #getLogger()}.</li>
 * <li>Clear thread-specific loggers and resources using {@link #clear()}.</li>
 * </ul>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class ScenarioLogManager {
    private static final Map<Long, Logger> LOGGER_MAP = new ConcurrentHashMap<>();

    /**
     * The key used to store and retrieve the thread-specific context for logging.
     * <p>
     * This key is added to the {@link ThreadContext} to associate a unique identifier (e.g., scenario
     * name, issue key, or thread name) with the current
     * thread's log entries.
     * <p>
     * The value of this key determines the log file name and ensures thread-specific logging behavior.
     *
     * @see ThreadContext#get(String)
     * @see ThreadContext#put(String, String)
     */
    public static final String THREAD_CONTEXT_KEY = "scenario";

    /**
     * Sets the thread context with a scenario-specific or thread-specific name for logging purposes.
     * <p>
     * If a test execution context is available, its issue key is used as part of the log file name. If
     * a scenario context is available, its sanitized name is
     * appended to the log file name. Otherwise, the thread name is used as a fallback.
     */
    private static void setThreadContext() {
        String logFileName = "";

        if (TestExecutionContext.get() != null) {
            logFileName = TestExecutionContext.get().ISSUE_KEY;
        }

        if (ScenarioContext.get() != null) {
            String scenarioName = ScenarioContext.get().getName().replaceAll("[^a-zA-Z0-9_-]", "_");
            if (logFileName.isEmpty()) {
                logFileName = logFileName.concat(scenarioName);
            } else {
                logFileName = logFileName.concat("_").concat(scenarioName);
            }
        }

        if (logFileName.isEmpty()) {
            logFileName = Thread.currentThread().getName();
        }

        if (!logFileName.equals(ThreadContext.get(THREAD_CONTEXT_KEY))) {
            ThreadContext.put(THREAD_CONTEXT_KEY, logFileName);
        }
    }

    /**
     * Dynamically creates or updates a rolling file appender for the current thread.
     * <p>
     * A unique appender is associated with each thread based on the scenario context. If an appender
     * for the current scenario already exists, it will not be
     * recreated.
     *
     * @param threadId the ID of the current thread
     */
    private static void updateAppender(long threadId) {
        LoggerContext context = (LoggerContext) LogManager.getContext(false);
        Configuration configuration = context.getConfiguration();
        String scenario = ThreadContext.get(THREAD_CONTEXT_KEY);

        if (scenario != null && !scenario.equals("main") && !configuration.getAppenders().containsKey(scenario)) {
            Filter filter = new CustomFilter(threadId);

            Appender newAppender = RollingFileAppender.newBuilder()
                    .setName(scenario)
                    .setLayout(PatternLayout.newBuilder()
                            .withPattern("[thread-id %T] %d{yyyy-MM-dd HH:mm:ss} %-5p %c{1}:%L - %m%n")
                            .withCharset(StandardCharsets.UTF_8)
                            .build())
                    .withFileName("logs/" + scenario + ".log")
                    .withFilePattern("logs/" + scenario + "-%d{yyyy-MM-dd}.log.gz")
                    .setConfiguration(configuration)
                    .setFilter(filter)
                    .withPolicy(TimeBasedTriggeringPolicy.newBuilder()
                            .build())
                    .build();

            newAppender.start();
            configuration.addAppender(newAppender);
            configuration.getLoggerConfig(LogManager.ROOT_LOGGER_NAME).addAppender(newAppender,
                    Level.getLevel(TestProperties.getProperty("logLevel", true, DefaultValues.LOG_LEVEL).orElse(DefaultValues.LOG_LEVEL)), filter);
            context.updateLoggers(configuration);
        }
    }

    /**
     * Retrieves the logger for the current thread.
     * <p>
     * If no logger exists for the current thread, a new logger is created and associated with a
     * thread-specific appender. The logger ensures that log messages
     * are written to a unique file for the thread or scenario.
     *
     * @return the thread-specific logger
     */
    public static Logger getLogger() {
        setThreadContext();
        long threadId = Thread.currentThread().getId();
        Class<?> callerClass = StackLocatorUtil.getCallerClass(2);

        return LOGGER_MAP.compute(threadId, (id, existingLogger) -> {
            if (existingLogger == null || !existingLogger.getClass().equals(callerClass)) {
                existingLogger = LogManager.getLogger(callerClass);
            }
            updateAppender(threadId);
            return existingLogger;
        });
    }

    /**
     * Clears the thread-specific logger and removes its associated appender.
     * <p>
     * Stops the appender and removes it from the logger configuration to release resources. This method
     * should be called when a thread no longer requires
     * logging, to prevent resource leaks.
     */
    public static void clear() {
        Logger logger = LOGGER_MAP.remove(Thread.currentThread().getId());
        if (logger != null) {
            LoggerContext context = (LoggerContext) LogManager.getContext(false);
            Configuration configuration = context.getConfiguration();
            Appender appender = configuration.getAppender(ThreadContext.get(THREAD_CONTEXT_KEY));
            if (appender != null) {
                appender.stop();
                configuration.getLoggerConfig(LogManager.ROOT_LOGGER_NAME).removeAppender(appender.getName());
                context.updateLoggers(configuration);
            }
        }
        ThreadContext.clearMap();
    }
}
