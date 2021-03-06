<?php
/**
 * @package    Zend_Measure
 * @subpackage UnitTests
 */


/**
 * Zend_Measure_Viscosity_Dynamic
 */
require_once 'Zend/Measure/Viscosity/Dynamic.php';

/**
 * PHPUnit test case
 */
require_once 'PHPUnit/Framework/TestCase.php';


/**
 * @package    Zend_Measure
 * @subpackage UnitTests
 */
class Zend_Measure_Viscosity_DynamicTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
    }


    /**
     * test for Mass initialisation
     * expected instance
     */
    public function testMassInit()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('100',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $this->assertTrue($value instanceof Zend_Measure_Viscosity_Dynamic,'Zend_Measure_Viscosity_Dynamic Object not returned');
    }


    /**
     * test for exception unknown type
     * expected exception
     */
    public function testViscosity_DynamicUnknownType()
    {
        try {
            $value = new Zend_Measure_Viscosity_Dynamic('100','Viscosity_Dynamic::UNKNOWN','de');
            $this->assertTrue(false,'Exception expected because of unknown type');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test for exception unknown value
     * expected exception
     */
    public function testViscosity_DynamicUnknownValue()
    {
        try {
            $value = new Zend_Measure_Viscosity_Dynamic('novalue',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
            $this->assertTrue(false,'Exception expected because of empty value');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test for exception unknown locale
     * expected root value
     */
    public function testViscosity_DynamicUnknownLocale()
    {
        try {
            $value = new Zend_Measure_Viscosity_Dynamic('100',Zend_Measure_Viscosity_Dynamic::STANDARD,'nolocale');
            $this->assertTrue(false,'Exception expected because of unknown locale');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test for standard locale
     * expected integer
     */
    public function testViscosity_DynamicNoLocale()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('100',Zend_Measure_Viscosity_Dynamic::STANDARD);
        $this->assertEquals(100, $value->getValue(),'Zend_Measure_Viscosity_Dynamic value expected');
    }


    /**
     * test for positive value
     * expected integer
     */
    public function testViscosity_DynamicValuePositive()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('100',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $this->assertEquals(100, $value->getValue(), 'Zend_Measure_Viscosity_Dynamic value expected to be a positive integer');
    }


    /**
     * test for negative value
     * expected integer
     */
    public function testViscosity_DynamicValueNegative()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('-100',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $this->assertEquals(-100, $value->getValue(), 'Zend_Measure_Viscosity_Dynamic value expected to be a negative integer');
    }


    /**
     * test for decimal value
     * expected float
     */
    public function testViscosity_DynamicValueDecimal()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('-100,200',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $this->assertEquals(-100.200, $value->getValue(), 'Zend_Measure_Viscosity_Dynamic value expected to be a decimal value');
    }


    /**
     * test for decimal seperated value
     * expected float
     */
    public function testViscosity_DynamicValueDecimalSeperated()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('-100.100,200',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $this->assertEquals(-100100.200, $value->getValue(),'Zend_Measure_Viscosity_Dynamic Object not returned');
    }


    /**
     * test for string with integrated value
     * expected float
     */
    public function testViscosity_DynamicValueString()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('string -100.100,200',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $this->assertEquals(-100100.200, $value->getValue(),'Zend_Measure_Viscosity_Dynamic Object not returned');
    }


    /**
     * test for equality
     * expected true
     */
    public function testViscosity_DynamicEquality()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('string -100.100,200',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $newvalue = new Zend_Measure_Viscosity_Dynamic('otherstring -100.100,200',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $this->assertTrue($value->equals($newvalue),'Zend_Measure_Viscosity_Dynamic Object should be equal');
    }


    /**
     * test for no equality
     * expected false
     */
    public function testViscosity_DynamicNoEquality()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('string -100.100,200',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $newvalue = new Zend_Measure_Viscosity_Dynamic('otherstring -100,200',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $this->assertFalse($value->equals($newvalue),'Zend_Measure_Viscosity_Dynamic Object should be not equal');
    }


    /**
     * test for serialization
     * expected string
     */
    public function testViscosity_DynamicSerialize()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('string -100.100,200',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $serial = $value->serialize();
        $this->assertTrue(!empty($serial),'Zend_Measure_Viscosity_Dynamic not serialized');
    }


    /**
     * test for unserialization
     * expected object
     */
    public function testViscosity_DynamicUnSerialize()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('string -100.100,200',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $serial = $value->serialize();
        $newvalue = unserialize($serial);
        $this->assertTrue($value->equals($newvalue),'Zend_Measure_Viscosity_Dynamic not unserialized');
    }


    /**
     * test for set positive value
     * expected integer
     */
    public function testViscosity_DynamicSetPositive()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('100',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $value->setValue('200',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $this->assertEquals(200, $value->getValue(), 'Zend_Measure_Viscosity_Dynamic value expected to be a positive integer');
    }


    /**
     * test for set negative value
     * expected integer
     */
    public function testViscosity_DynamicSetNegative()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('-100',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $value->setValue('-200',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $this->assertEquals(-200, $value->getValue(), 'Zend_Measure_Viscosity_Dynamic value expected to be a negative integer');
    }


    /**
     * test for set decimal value
     * expected float
     */
    public function testViscosity_DynamicSetDecimal()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('-100,200',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $value->setValue('-200,200',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $this->assertEquals(-200.200, $value->getValue(), 'Zend_Measure_Viscosity_Dynamic value expected to be a decimal value');
    }


    /**
     * test for set decimal seperated value
     * expected float
     */
    public function testViscosity_DynamicSetDecimalSeperated()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('-100.100,200',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $value->setValue('-200.200,200',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $this->assertEquals(-200200.200, $value->getValue(),'Zend_Measure_Viscosity_Dynamic Object not returned');
    }


    /**
     * test for set string with integrated value
     * expected float
     */
    public function testViscosity_DynamicSetString()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('string -100.100,200',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $value->setValue('otherstring -200.200,200',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $this->assertEquals(-200200.200, $value->getValue(),'Zend_Measure_Viscosity_Dynamic Object not returned');
    }


    /**
     * test for exception unknown type
     * expected exception
     */
    public function testViscosity_DynamicSetUnknownType()
    {
        try {
            $value = new Zend_Measure_Viscosity_Dynamic('100',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
            $value->setValue('otherstring -200.200,200','Viscosity_Dynamic::UNKNOWN','de');
            $this->assertTrue(false,'Exception expected because of unknown type');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test for exception unknown value
     * expected exception
     */
    public function testViscosity_DynamicSetUnknownValue()
    {
        try {
            $value = new Zend_Measure_Viscosity_Dynamic('100',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
            $value->setValue('novalue',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
            $this->assertTrue(false,'Exception expected because of empty value');
        } catch (Exception $e) {
            return; // Test OK
        }
    }


    /**
     * test for exception unknown locale
     * expected exception
     */
    public function testViscosity_DynamicSetUnknownLocale()
    {
        try {
            $value = new Zend_Measure_Viscosity_Dynamic('100',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
            $value->setValue('200',Zend_Measure_Viscosity_Dynamic::STANDARD,'nolocale');
            $this->assertTrue(false,'Exception expected because of unknown locale');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test for exception unknown locale
     * expected exception
     */
    public function testViscosity_DynamicSetWithNoLocale()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('100', Zend_Measure_Viscosity_Dynamic::STANDARD, 'de');
        $value->setValue('200', Zend_Measure_Viscosity_Dynamic::STANDARD);
        $this->assertEquals(200, $value->getValue(), 'Zend_Measure_Viscosity_Dynamic value expected to be a positive integer');
    }


    /**
     * test setting type
     * expected new type
     */
    public function testViscosity_DynamicSetType()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('-100',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $value->setType(Zend_Measure_Viscosity_Dynamic::POISE);
        $this->assertEquals($value->getType(), Zend_Measure_Viscosity_Dynamic::POISE, 'Zend_Measure_Viscosity_Dynamic type expected');
    }


    /**
     * test setting computed type
     * expected new type
     */
    public function testViscosity_DynamicSetComputedType1()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('-100',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $value->setType(Zend_Measure_Viscosity_Dynamic::KILOGRAM_PER_METER_HOUR);
        $this->assertEquals($value->getType(), Zend_Measure_Viscosity_Dynamic::KILOGRAM_PER_METER_HOUR, 'Zend_Measure_Viscosity_Dynamic type expected');
    }


    /**
     * test setting computed type
     * expected new type
     */
    public function testViscosity_DynamicSetComputedType2()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('-100',Zend_Measure_Viscosity_Dynamic::KILOGRAM_PER_METER_HOUR,'de');
        $value->setType(Zend_Measure_Viscosity_Dynamic::STANDARD);
        $this->assertEquals($value->getType(), Zend_Measure_Viscosity_Dynamic::STANDARD, 'Zend_Measure_Viscosity_Dynamic type expected');
    }


    /**
     * test setting unknown type
     * expected new type
     */
    public function testViscosity_DynamicSetTypeFailed()
    {
        try {
            $value = new Zend_Measure_Viscosity_Dynamic('-100',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
            $value->setType('Viscosity_Dynamic::UNKNOWN');
            $this->assertTrue(false,'Exception expected because of unknown type');
        } catch (Exception $e) {
            return true; // OK
        }
    }


    /**
     * test toString
     * expected string
     */
    public function testViscosity_DynamicToString()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('-100',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $this->assertEquals($value->toString(), '-100 kg/ms', 'Value -100 kg/ms expected');
    }


    /**
     * test __toString
     * expected string
     */
    public function testViscosity_Dynamic_ToString()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('-100',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $this->assertEquals($value->__toString(), '-100 kg/ms', 'Value -100 kg/ms expected');
    }


    /**
     * test getConversionList
     * expected array
     */
    public function testViscosity_DynamicConversionList()
    {
        $value = new Zend_Measure_Viscosity_Dynamic('-100',Zend_Measure_Viscosity_Dynamic::STANDARD,'de');
        $unit  = $value->getConversionList();
        $this->assertTrue(is_array($unit), 'Array expected');
    }
}
