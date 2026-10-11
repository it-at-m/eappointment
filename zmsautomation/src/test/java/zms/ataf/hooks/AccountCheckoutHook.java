package zms.ataf.hooks;

import io.cucumber.java.After;
import zms.ataf.helpers.AccountCheckout;

/**
 * Returns every account this scenario checked out. Lowest order so it runs after
 * other {@code @After} hooks that still use the session.
 */
public class AccountCheckoutHook {

    @After(order = Integer.MIN_VALUE)
    public void releaseCheckedOutAccounts() {
        AccountCheckout.releaseAll();
    }
}
