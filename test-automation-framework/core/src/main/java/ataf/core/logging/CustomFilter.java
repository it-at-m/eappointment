package ataf.core.logging;

import org.apache.logging.log4j.core.LogEvent;
import org.apache.logging.log4j.core.filter.AbstractFilter;

/**
 * A custom Log4j 2 filter that filters log events based on the thread ID.
 * <p>
 * This filter ensures that only log events originating from a specific thread (identified by its
 * thread ID) are accepted. All other log events are denied.
 * <p>
 * It is primarily used to implement thread-specific logging by attaching it to appenders that
 * handle logs for a specific thread.
 * <p>
 * Typical usage involves dynamically creating this filter for each thread and associating it with a
 * thread-specific appender.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class CustomFilter extends AbstractFilter {
    private final long THREAD_ID;

    /**
     * Constructs a new {@link CustomFilter} for the specified thread ID.
     *
     * @param threadId the ID of the thread whose log events should be accepted
     */
    public CustomFilter(long threadId) {
        this.THREAD_ID = threadId;
    }

    /**
     * Filters log events based on the thread ID.
     * <p>
     * This method checks if the thread ID of the incoming log event matches the thread ID specified
     * during the filter's construction. If it matches, the event
     * is accepted; otherwise, it is denied.
     *
     * @param event the log event to filter
     * @return {@link Result#ACCEPT} if the event's thread ID matches; {@link Result#DENY} otherwise
     */
    @Override
    public Result filter(LogEvent event) {
        if (event.getThreadId() == THREAD_ID) {
            return Result.ACCEPT;
        }
        return Result.DENY;
    }
}
