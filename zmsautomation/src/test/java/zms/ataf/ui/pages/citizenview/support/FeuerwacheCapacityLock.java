package zms.ataf.ui.pages.citizenview.support;

import java.util.concurrent.locks.ReentrantLock;

/**
 * Serializes ZMSKVR-151 order-day assertions against ZMSKVR-1235/1244 booked-out
 * scenarios that call {@code reserve every available} on Riem 10575 / Neuperlach 10582.
 *
 * <p>Both share the same ATAF DB in one zmscitizenview JVM. Booked-out empties every
 * seat including Flyway V61 day+7; the order scenario needs that day bookable. A
 * process-wide lock plus order-day reseed keeps both green under parallel Cucumber.
 */
public final class FeuerwacheCapacityLock {

    private static final ReentrantLock LOCK = new ReentrantLock();

    private FeuerwacheCapacityLock() {}

    public static void acquire() {
        LOCK.lock();
    }

    public static void release() {
        if (LOCK.isHeldByCurrentThread()) {
            LOCK.unlock();
        }
    }
}
