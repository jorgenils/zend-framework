<?php
/**
 * @package    Zend_Measure
 * @subpackage UnitTests
 */


/**
 * Zend_Measure_Volume
 */
require_once 'Zend/Measure/Volume.php';

/**
 * PHPUnit2 test case
 */
require_once 'PHPUnit2/Framework/TestCase.php';


/**
 * @package    Zend_Measure
 * @subpackage UnitTests
 */
class Zend_Measure_VolumeTest extends PHPUnit2_Framework_TestCase
{

    public function setUp()
    {
    }


    /**
     * test for Volume initialisation
     * expected instance
     */
    public function testVolumeInit()
    {
        $value = new Zend_Measure_Volume('100',Zend_Measure_Volume::STANDARD,'de');
        $this->assertTrue($value instanceof Zend_Measure_Volume,'Zend_Measure_Volume Object not returned');
    }


    /**
     * test for exception unknown type
     * expected exception
     */
    public function testVolumeUnknownType()
    {
        try {
            $value = new Zend_Measure_Volume('100','Volume::UNKNOWN','de');
            $this->assertTrue(false,'Exception expected because of unknown type');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test for exception unknown value
     * expected exception
     */
    public function testVolumeUnknownValue()
    {
        try {
            $value = new Zend_Measure_Volume('novalue',Zend_Measure_Volume::STANDARD,'de');
            $this->assertTrue(false,'Exception expected because of empty value');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test for exception unknown locale
     * expected root value
     */
    public function testVolumeUnknownLocale()
    {
        try {
            $value = new Zend_Measure_Volume('100',Zend_Measure_Volume::STANDARD,'nolocale');
            $this->assertTrue(false,'Exception expected because of unknown locale');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test for positive value
     * expected integer
     */
    public function testVolumeValuePositive()
    {
        $value = new Zend_Measure_Volume('100',Zend_Measure_Volume::STANDARD,'de');
        $this->assertEquals(100, $value->getValue(), 'Zend_Measure_Volume value expected to be a positive integer');
    }


    /**
     * test for negative value
     * expected integer
     */
    public function testVolumeValueNegative()
    {
        $value = new Zend_Measure_Volume('-100',Zend_Measure_Volume::STANDARD,'de');
        $this->assertEquals(-100, $value->getValue(), 'Zend_Measure_Volume value expected to be a negative integer');
    }


    /**
     * test for decimal value
     * expected float
     */
    public function testVolumeValueDecimal()
    {
        $value = new Zend_Measure_Volume('-100,200',Zend_Measure_Volume::STANDARD,'de');
        $this->assertEquals(-100.200, $value->getValue(), 'Zend_Measure_Volume value expected to be a decimal value');
    }


    /**
     * test for decimal seperated value
     * expected float
     */
    public function testVolumeValueDecimalSeperated()
    {
        $value = new Zend_Measure_Volume('-100.100,200',Zend_Measure_Volume::STANDARD,'de');
        $this->assertEquals(-100100.200, $value->getValue(),'Zend_Measure_Volume Object not returned');
    }


    /**
     * test for string with integrated value
     * expected float
     */
    public function testVolumeValueString()
    {
        $value = new Zend_Measure_Volume('string -100.100,200',Zend_Measure_Volume::STANDARD,'de');
        $this->assertEquals(-100100.200, $value->getValue(),'Zend_Measure_Volume Object not returned');
    }


    /**
     * test for equality
     * expected true
     */
    public function testVolumeEquality()
    {
        $value = new Zend_Measure_Volume('string -100.100,200',Zend_Measure_Volume::STANDARD,'de');
        $newvalue = new Zend_Measure_Volume('otherstring -100.100,200',Zend_Measure_Volume::STANDARD,'de');
        $this->assertTrue($value->equals($newvalue),'Zend_Measure_Volume Object should be equal');
    }


    /**
     * test for no equality
     * expected false
     */
    public function testVolumeNoEquality()
    {
        $value = new Zend_Measure_Volume('string -100.100,200',Zend_Measure_Volume::STANDARD,'de');
        $newvalue = new Zend_Measure_Volume('otherstring -100,200',Zend_Measure_Volume::STANDARD,'de');
        $this->assertFalse($value->equals($newvalue),'Zend_Measure_Volume Object should be not equal');
    }


    /**
     * test for serialization
     * expected string
     */
    public function testVolumeSerialize()
    {
        $value = new Zend_Measure_Volume('string -100.100,200',Zend_Measure_Volume::STANDARD,'de');
        $serial = $value->serialize();
        $this->assertTrue(!empty($serial),'Zend_Measure_Volume not serialized');
    }


    /**
     * test for unserialization
     * expected object
     */
    public function testVolumeUnSerialize()
    {
        $value = new Zend_Measure_Volume('string -100.100,200',Zend_Measure_Volume::STANDARD,'de');
        $serial = $value->serialize();
        $newvalue = unserialize($serial);
        $this->assertTrue($value->equals($newvalue),'Zend_Measure_Volume not unserialized');
    }


    /**
     * test for set positive value
     * expected integer
     */
    public function testVolumeSetPositive()
    {
        $value = new Zend_Measure_Volume('100',Zend_Measure_Volume::STANDARD,'de');
        $value->setValue('200',Zend_Measure_Volume::STANDARD,'de');
        $this->assertEquals(200, $value->getValue(), 'Zend_Measure_Volume value expected to be a positive integer');
    }


    /**
     * test for set negative value
     * expected integer
     */
    public function testVolumeSetNegative()
    {
        $value = new Zend_Measure_Volume('-100',Zend_Measure_Volume::STANDARD,'de');
        $value->setValue('-200',Zend_Measure_Volume::STANDARD,'de');
        $this->assertEquals(-200, $value->getValue(), 'Zend_Measure_Volume value expected to be a negative integer');
    }


    /**
     * test for set decimal value
     * expected float
     */
    public function testVolumeSetDecimal()
    {
        $value = new Zend_Measure_Volume('-100,200',Zend_Measure_Volume::STANDARD,'de');
        $value->setValue('-200,200',Zend_Measure_Volume::STANDARD,'de');
        $this->assertEquals(-200.200, $value->getValue(), 'Zend_Measure_Volume value expected to be a decimal value');
    }


    /**
     * test for set decimal seperated value
     * expected float
     */
    public function testVolumeSetDecimalSeperated()
    {
        $value = new Zend_Measure_Volume('-100.100,200',Zend_Measure_Volume::STANDARD,'de');
        $value->setValue('-200.200,200',Zend_Measure_Volume::STANDARD,'de');
        $this->assertEquals(-200200.200, $value->getValue(),'Zend_Measure_Volume Object not returned');
    }


    /**
     * test for set string with integrated value
     * expected float
     */
    public function testVolumeSetString()
    {
        $value = new Zend_Measure_Volume('string -100.100,200',Zend_Measure_Volume::STANDARD,'de');
        $value->setValue('otherstring -200.200,200',Zend_Measure_Volume::STANDARD,'de');
        $this->assertEquals(-200200.200, $value->getValue(),'Zend_Measure_Volume Object not returned');
    }


    /**
     * test for exception unknown type
     * expected exception
     */
    public function testVolumeSetUnknownType()
    {
        try {
            $value = new Zend_Measure_Volume('100',Zend_Measure_Volume::STANDARD,'de');
            $value->setValue('otherstring -200.200,200','Volume::UNKNOWN','de');
            $this->assertTrue(false,'Exception expected because of unknown type');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test for exception unknown value
     * expected exception
     */
    public function testVolumeSetUnknownValue()
    {
        try {
            $value = new Zend_Measure_Volume('100',Zend_Measure_Volume::STANDARD,'de');
            $value->setValue('novalue',Zend_Measure_Volume::STANDARD,'de');
            $this->assertTrue(false,'Exception expected because of empty value');
        } catch (Exception $e) {
            return; // Test OK
        }
    }


    /**
     * test for exception unknown locale
     * expected exception
     */
    public function testVolumeSetUnknownLocale()
    {
        try {
            $value = new Zend_Measure_Volume('100',Zend_Measure_Volume::STANDARD,'de');
            $value->setValue('200',Zend_Measure_Volume::STANDARD,'nolocale');
            $this->assertTrue(false,'Exception expected because of unknown locale');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test setting type
     * expected new type
     */
    public function testVolumeSetType()
    {
        $value = new Zend_Measure_Volume('-100',Zend_Measure_Volume::STANDARD,'de');
        $value->setType(Zend_Measure_Volume::CORD);
        $this->assertEquals($value->getType(), Zend_Measure_Volume::CORD, 'Zend_Measure_Volume type expected');
    }


    /**
     * test setting computed type
     * expected new type
     */
    public function testVolumeSetComputedType1()
    {
        $value = new Zend_Measure_Volume('-100',Zend_Measure_Volume::STANDARD,'de');
        $value->setType(Zend_Measure_Volume::CUBIC_YARD);
        $this->assertEquals($value->getType(), Zend_Measure_Volume::CUBIC_YARD, 'Zend_Measure_Volume type expected');
    }


    /**
     * test setting computed type
     * expected new type
     */
    public function testVolumeSetComputedType2()
    {
        $value = new Zend_Measure_Volume('-100',Zend_Measure_Volume::CUBIC_YARD,'de');
        $value->setType(Zend_Measure_Volume::STANDARD);
        $this->assertEquals($value->getType(), Zend_Measure_Volume::STANDARD, 'Zend_Measure_Volume type expected');
    }


    /**
     * test setting unknown type
     * expected new type
     */
    public function testVolumeSetTypeFailed()
    {
        try {
            $value = new Zend_Measure_Volume('-100',Zend_Measure_Volume::STANDARD,'de');
            $value->setType('Volume::UNKNOWN');
            $this->assertTrue(false,'Exception expected because of unknown type');
        } catch (Exception $e) {
            return true; // OK
        }
    }


    /**
     * test toString
     * expected string
     */
    public function testVolumeToString()
    {
        $value = new Zend_Measure_Volume('-100',Zend_Measure_Volume::STANDARD,'de');
        $this->assertEquals($value->toString(), '-100 m�', 'Value -100 m� expected');
    }


    /**
     * test __toString
     * expected string
     */
    public function testVolume_ToString()
    {
        $value = new Zend_Measure_Volume('-100',Zend_Measure_Volume::STANDARD,'de');
        $this->assertEquals($value->__toString(), '-100 m�', 'Value -100 m� expected');
    }
}
