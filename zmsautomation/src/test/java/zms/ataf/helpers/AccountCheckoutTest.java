package zms.ataf.helpers;

import java.util.LinkedHashMap;
import java.util.LinkedHashSet;
import java.util.Map;
import java.util.Set;
import java.util.concurrent.CountDownLatch;
import java.util.concurrent.TimeUnit;
import java.util.concurrent.atomic.AtomicBoolean;
import java.util.concurrent.atomic.AtomicInteger;

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
    public void sameThreadSecondAgentQueueLoginReusesHeldAccount() {
        String first = AccountCheckout.assignWorkstationLogin("agent_queue");
        String again = AccountCheckout.assignWorkstationLogin("agent_queue");
        Assert.assertEquals(again, first);
    }

    @Test
    public void sameThreadCanHoldTwoAgentQueueAccountsForTwoClerks() {
        String first = AccountCheckout.assignWorkstationLogin("agent_queue");
        String second = AccountCheckout.assignAnotherWorkstationLogin("agent_queue");
        Assert.assertNotEquals(second, first);
        int secondIndex = Integer.parseInt(second.replace("ataf_agent_queue_", "")) - 1;
        Assert.assertEquals(
            AccountCheckout.workstationCounter("5"),
            Integer.toString(5 + secondIndex * 100));
    }

    @Test
    public void spareSuperuserGetsItsOwnQueueDesk() {
        AccountCheckout.assignWorkstationLogin("ataf");
        AccountCheckout.releaseAll();
        String spare = AccountCheckout.assignWorkstationLogin("ataf_superuser_2");
        Assert.assertEquals(spare, "ataf_superuser_2");
        Assert.assertEquals(AccountCheckout.queueDesk("13"), "113");
    }

    @Test
    public void userAdminPoolWaitsForAnUnlockedAccountThatHasADepartment() throws Exception {
        Map<String, Integer> departments = new LinkedHashMap<>();
        departments.put("ataf_user_admin_1", 40);
        departments.put("ataf_user_admin_2", 41);
        String first = AccountCheckout.assignUserAdminLogin(departments);
        Assert.assertEquals(first, "ataf_user_admin_1");
        Assert.assertEquals(AccountCheckout.checkedOutDepartmentId(), Integer.valueOf(40));
        AtomicBoolean same = new AtomicBoolean(true);
        AtomicInteger secondDepartment = new AtomicInteger();
        Thread other = new Thread(() -> {
            String second = AccountCheckout.assignUserAdminLogin(departments);
            same.set(first.equals(second));
            secondDepartment.set(AccountCheckout.checkedOutDepartmentId());
            AccountCheckout.releaseAll();
        });
        other.start();
        other.join(2000);
        Assert.assertFalse(other.isAlive());
        Assert.assertFalse(same.get());
        Assert.assertEquals(secondDepartment.get(), 41);
    }

    @Test
    public void userAdminPoolPicksARandomAccountAndUsesItsDepartment() {
        Map<String, Integer> departments = new LinkedHashMap<>();
        departments.put("ataf_user_admin_1", 2);
        departments.put("ataf_user_admin_2", 40);
        departments.put("ataf_user_admin_3", 102);
        Set<String> seen = new LinkedHashSet<>();
        for (int i = 0; i < 30; i++) {
            String login = AccountCheckout.assignRandomUserAdminLogin(departments);
            seen.add(login);
            Assert.assertEquals(AccountCheckout.checkedOutDepartmentId(), departments.get(login));
            AccountCheckout.releaseAll();
        }
        Assert.assertTrue(seen.size() > 1, "The pool always returned " + seen);
    }

    @Test(expectedExceptions = IllegalStateException.class)
    public void userAdminPoolRejectsAccountsWithoutADepartment() {
        AccountCheckout.assignUserAdminLogin(Map.of());
    }

    @Test
    public void workstationLoginNameSharesTheOidcAccount() {
        Assert.assertEquals(AccountCheckout.workstationAccountId("ataf"), "ataf@keycloak");
        Assert.assertEquals(AccountCheckout.workstationAccountId("ataf@keycloak"), "ataf@keycloak");
    }
}
