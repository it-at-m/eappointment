package ataf.core.helpers;

import org.apache.commons.lang3.RandomStringUtils;
import org.testng.Assert;
import org.testng.annotations.Test;

/**
 * @author Ludwig Haas (ex.haas02)
 */
public class TestPropertiesHelperTest {

    @Test
    public void positiveBooleanTestVariantOne() {
        Assert.assertTrue(TestPropertiesHelper.getPropertyAsBoolean("booleanTest"));
    }

    @Test
    public void positiveBooleanTestVariantTwo() {
        Assert.assertTrue(TestPropertiesHelper.getPropertyAsBoolean("booleanTest", true));
    }

    @Test
    public void positiveBooleanTestVariantThree() {
        Assert.assertTrue(TestPropertiesHelper.getPropertyAsBoolean("booleanTest", true, true));
    }

    @Test
    public void negativeBooleanTestVariantOne() {
        Assert.assertFalse(TestPropertiesHelper.getPropertyAsBoolean(RandomStringUtils.secure().next(12)));
    }

    @Test
    public void negativeBooleanTestVariantTwo() {
        Assert.assertFalse(TestPropertiesHelper.getPropertyAsBoolean(RandomStringUtils.secure().next(12), true));
    }

    @Test
    public void negativeBooleanTestVariantThree() {
        Assert.assertTrue(TestPropertiesHelper.getPropertyAsBoolean(RandomStringUtils.secure().next(12), true, true));
    }

    @Test
    public void positiveByteTestVariantOne() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsByte("byteTest"), (byte) 87);
    }

    @Test
    public void positiveByteTestVariantTwo() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsByte("byteTest", true), (byte) 87);
    }

    @Test
    public void positiveByteTestVariantThree() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsByte("byteTest", true, (byte) 87), (byte) 87);
    }

    @Test
    public void negativeByteTestVariantOne() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsByte(RandomStringUtils.secure().next(12)), (byte) -1);
    }

    @Test
    public void negativeByteTestVariantTwo() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsByte(RandomStringUtils.secure().next(12), true), (byte) -1);
    }

    @Test
    public void negativeByteTestVariantThree() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsByte(RandomStringUtils.secure().next(12), true, (byte) 87), (byte) 87);
    }

    @Test
    public void positiveShortTestVariantOne() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsShort("shortTest"), (short) 25000);
    }

    @Test
    public void positiveShortTestVariantTwo() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsShort("shortTest", true), (short) 25000);
    }

    @Test
    public void positiveShortTestVariantThree() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsShort("shortTest", true, (short) 25000), (short) 25000);
    }

    @Test
    public void negativeShortTestVariantOne() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsShort(RandomStringUtils.secure().next(12)), (short) -1);
    }

    @Test
    public void negativeShortTestVariantTwo() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsShort(RandomStringUtils.secure().next(12), true), (short) -1);
    }

    @Test
    public void negativeShortTestVariantThree() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsShort(RandomStringUtils.secure().next(12), true, (short) 25000), (short) 25000);
    }

    @Test
    public void positiveIntegerTestVariantOne() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsInteger("intTest"), -2000000000);
    }

    @Test
    public void positiveIntegerTestVariantTwo() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsInteger("intTest", true), -2000000000);
    }

    @Test
    public void positiveIntegerTestVariantThree() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsInteger("intTest", true, -2000000000), -2000000000);
    }

    @Test
    public void negativeIntegerTestVariantOne() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsInteger(RandomStringUtils.secure().next(12)), -1);
    }

    @Test
    public void negativeIntegerTestVariantTwo() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsInteger(RandomStringUtils.secure().next(12), true), -1);
    }

    @Test
    public void negativeIntegerTestVariantThree() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsInteger(RandomStringUtils.secure().next(12), true, 2000000000), 2000000000);
    }

    @Test
    public void positiveLongTestVariantOne() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsLong("longTest"), 9000000000000000000L);
    }

    @Test
    public void positiveLongTestVariantTwo() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsLong("longTest", true), 9000000000000000000L);
    }

    @Test
    public void positiveLongTestVariantThree() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsLong("longTest", true, 9000000000000000000L),
                9000000000000000000L);
    }

    @Test
    public void negativeLongTestVariantOne() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsLong(RandomStringUtils.secure().next(12)), -1L);
    }

    @Test
    public void negativeLongTestVariantTwo() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsLong(RandomStringUtils.secure().next(12), true), -1L);
    }

    @Test
    public void negativeLongTestVariantThree() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsLong(RandomStringUtils.secure().next(12), true, 9000000000000000000L),
                9000000000000000000L);
    }

    @Test
    public void positiveFloatTestVariantOne() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsFloat("floatTest"), (float) 4.8);
    }

    @Test
    public void positiveFloatTestVariantTwo() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsFloat("floatTest", true), (float) 4.8);
    }

    @Test
    public void positiveFloatTestVariantThree() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsFloat("floatTest", true, (float) 4.8), (float) 4.8);
    }

    @Test
    public void negativeFloatTestVariantOne() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsFloat(RandomStringUtils.secure().next(12)), (float) -1.0);
    }

    @Test
    public void negativeFloatTestVariantTwo() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsFloat(RandomStringUtils.secure().next(12), true), (float) -1.0);
    }

    @Test
    public void negativeFloatTestVariantThree() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsFloat(RandomStringUtils.secure().next(12), true, (float) 4.8), (float) 4.8);
    }

    @Test
    public void positiveDoubleTestVariantOne() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsDouble("doubleTest"), 24.75);
    }

    @Test
    public void positiveDoubleTestVariantTwo() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsDouble("doubleTest", true), 24.75);
    }

    @Test
    public void positiveDoubleTestVariantThree() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsDouble("doubleTest", true, 24.75), 24.75);
    }

    @Test
    public void negativeDoubleTestVariantOne() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsDouble(RandomStringUtils.secure().next(12)), -1.0);
    }

    @Test
    public void negativeDoubleTestVariantTwo() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsDouble(RandomStringUtils.secure().next(12), true), -1.0);
    }

    @Test
    public void negativeDoubleTestVariantThree() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsDouble(RandomStringUtils.secure().next(12), true, 24.75), 24.75);
    }

    @Test
    public void positiveStringTestVariantOne() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsString("stringTest"), "The Test value of this property!!111");
    }

    @Test
    public void positiveStringTestVariantTwo() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsString("stringTest", true), "The Test value of this property!!111");
    }

    @Test
    public void positiveStringTestVariantThree() {
        Assert.assertEquals(
                TestPropertiesHelper.getPropertyAsString("stringTest", true, "The Test value of this property!!111"),
                "The Test value of this property!!111");
    }

    @Test
    public void negativeStringTestVariantOne() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsString(RandomStringUtils.secure().next(12)), "");
    }

    @Test
    public void negativeStringTestVariantTwo() {
        Assert.assertEquals(TestPropertiesHelper.getPropertyAsString(RandomStringUtils.secure().next(12), true), "");
    }

    @Test
    public void negativeStringTestVariantThree() {
        Assert.assertEquals(
                TestPropertiesHelper.getPropertyAsString(RandomStringUtils.secure().next(12), true, "The Test value of this property!!111"),
                "The Test value of this property!!111");
    }
}
