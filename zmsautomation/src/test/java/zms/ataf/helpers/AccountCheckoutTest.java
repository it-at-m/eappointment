package zms.ataf.helpers;

import java.util.concurrent.CountDownLatch;
import java.util.concurrent.TimeUnit;
import java.util.concurrent.atomic.AtomicBoolean;

import org.testng.Assert;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.Test;

public class AccountCheckoutTest {

    @AfterMethod
    public void release() {
        AccountCheckout.releaseAll();
    }

    @Test
    public void secondCheckoutWaitsUntilTheAccountIsReleased() throws Exception {
        AccountCheckout.checkout("ataf@keycloak");
        CountDownLatch started = new CountDownLatch(1);
        AtomicBoolean acquired = new AtomicBoolean(false);
        Thread waiter = new Thread(() -> {
            started.countDown();
            AccountCheckout.checkout("ataf@keycloak");
            acquired.set(true);
            AccountCheckout.releaseAll();
        });
        waiter.start();
        Assert.assertTrue(started.await(2, TimeUnit.SECONDS));
        Thread.sleep(200);
        Assert.assertFalse(acquired.get());
        AccountCheckout.releaseAll();
        waiter.join(2000);
        Assert.assertFalse(waiter.isAlive());
        Assert.assertTrue(acquired.get());
    }

    @Test
    public void differentAccountsDoNotBlockEachOther() throws Exception {
        AccountCheckout.checkout("ataf@keycloak");
        AtomicBoolean acquired = new AtomicBoolean(false);
        Thread other = new Thread(() -> {
            AccountCheckout.checkout("_system_messenger");
            acquired.set(true);
            AccountCheckout.releaseAll();
        });
        other.start();
        other.join(2000);
        Assert.assertFalse(other.isAlive());
        Assert.assertTrue(acquired.get());
    }

    @Test
    public void defaultWorkstationLoginTakesAFreePoolMember() throws Exception {
        String first = AccountCheckout.assignWorkstationLogin("agent_queue");
        AtomicBoolean same = new AtomicBoolean(true);
        Thread other = new Thread(() -> {
            String second = AccountCheckout.assignWorkstationLogin("agent_queue");
            same.set(first.equals(second));
            AccountCheckout.releaseAll();
        });
        other.start();
        other.join(2000);
        Assert.assertFalse(other.isAlive());
        Assert.assertFalse(same.get());
        Assert.assertEquals(AccountCheckout.queueDesk("13"), "13");
    }

    @Test
    public void spareSuperuserGetsItsOwnQueueDesk() {
        AccountCheckout.assignWorkstationLogin("ataf");
        AccountCheckout.releaseAll();
        String spare = AccountCheckout.assignWorkstationLogin("ataf_2");
        Assert.assertEquals(spare, "ataf_2");
        Assert.assertEquals(AccountCheckout.queueDesk("13"), "113");
    }

    @Test
    public void workstationLoginNameSharesTheOidcAccount() {
        Assert.assertEquals(AccountCheckout.workstationAccountId("ataf"), "ataf@keycloak");
        Assert.assertEquals(AccountCheckout.workstationAccountId("ataf@keycloak"), "ataf@keycloak");
    }
}
