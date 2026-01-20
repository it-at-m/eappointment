package ataf.web.model;

import ataf.core.data.System;
import org.testng.Assert;
import org.testng.annotations.Test;

import java.net.MalformedURLException;
import java.net.URL;

/**
 * @author Ludwig Haas (ex.haas02)
 */
public class WindowTypeTest {
    System system;
    WindowType windowType;

    @Test
    public void getSystemWindowTypeWithSystemNullTest() {
        Assert.assertEquals(WindowType.getSystemWindowType((System) null), WindowType.UNKNOWN);
    }

    @Test
    public void getSystemWindowTypeWithSystemNameNullTest() {
        Assert.assertEquals(WindowType.getSystemWindowType((String) null), WindowType.UNKNOWN);
    }

    @Test
    public void getSystemWindowTypeWithSystemUrlNullTest() {
        Assert.assertEquals(WindowType.getSystemWindowType((URL) null), WindowType.UNKNOWN);
    }

    @Test
    public void createWindowTypeTest() {
        system = new System("my.test", "http://my.test.de");
        windowType = new WindowType("my.test.windowType", system);
    }

    @Test
    public void getSystemWindowTypeWithSystemTest() {
        Assert.assertEquals(WindowType.getSystemWindowType(system), windowType);
    }

    @Test
    public void getSystemWindowTypeWithSystemNameTest() {
        Assert.assertEquals(WindowType.getSystemWindowType(system.NAME), windowType);
    }

    @Test
    public void getSystemWindowTypeWithSystemUrlTest() throws MalformedURLException {
        Assert.assertEquals(WindowType.getSystemWindowType(new URL(system.URL)), windowType);
    }
}
