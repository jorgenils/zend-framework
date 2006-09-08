<?php
/**
 * @package    Zend_Measure
 * @subpackage UnitTests
 */


/**
 * Zend_Measure_Frequency
 */
require_once 'Zend/Measure/Frequency.php';

/**
 * PHPUnit test case
 */
require_once 'PHPUnit/Framework/TestCase.php';


/**
 * @package    Zend_Measure
 * @subpackage UnitTests
 */
class Zend_Measure_FrequencyTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
    }


    /**
     * test for Frequency initialisation
     * expected instance
     */
    public function testFrequencyInit()
    {
        $value = new Zend_Measure_Frequency('100',Zend_Measure_Frequency::STANDARD,'de');
        $this->assertTrue($value instanceof Zend_Measure_Frequency,'Zend_Measure_Frequency Object not returned');
    }


    /**
     * test for exception unknown type
     * expected exception
     */
    public function testFrequencyUnknownType()
    {
        try {
            $value = new Zend_Measure_Frequency('100','Frequency::UNKNOWN','de');
            $this->assertTrue(false,'Exception expected because of unknown type');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test for exception unknown value
     * expected exception
     */
    public function testFrequencyUnknownValue()
    {
        try {
            $value = new Zend_Measure_Frequency('novalue',Zend_Measure_Frequency::STANDARD,'de');
            $this->assertTrue(false,'Exception expected because of empty value');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test for exception unknown locale
     * expected root value
     */
    public function testFrequencyUnknownLocale()
    {
        try {
            $value = new Zend_Measure_Frequency('100',Zend_Measure_Frequency::STANDARD,'nolocale');
            $this->assertTrue(false,'Exception expected because of unknown locale');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test for positive value
     * expected integer
     */
    public function testFrequencyValuePositive()
    {
        $value = new Zend_Measure_Frequency('100',Zend_Measure_Frequency::STANDARD,'de');
        $this->assertEquals(100, $value->getValue(), 'Zend_Measure_Frequency value expected to be a positive integer');
    }


    /**
     * test for negative value
     * expected integer
     */
    public function testFrequencyValueNegative()
    {
        $value = new Zend_Measure_Frequency('-100',Zend_Measure_Frequency::STANDARD,'de');
        $this->assertEquals(-100, $value->getValue(), 'Zend_Measure_Frequency value expected to be a negative integer');
    }


    /**
     * test for decimal value
     * expected float
     */
    public function testFrequencyValueDecimal()
    {
        $value = new Zend_Measure_Frequency('-100,200',Zend_Measure_Frequency::STANDARD,'de');
        $this->assertEquals(-100.200, $value->getValue(), 'Zend_Measure_Frequency value expected to be a decimal value');
    }


    /**
     * test for decimal seperated value
     * expected float
     */
    public function testFrequencyValueDecimalSeperated()
    {
        $value = new Zend_Measure_Frequency('-100.100,200',Zend_Measure_Frequency::STANDARD,'de');
        $this->assertEquals(-100100.200, $value->getValue(),'Zend_Measure_Frequency Object not returned');
    }


    /**
     * test for string with integrated value
     * expected float
     */
    public function testFrequencyValueString()
    {
        $value = new Zend_Measure_Frequency('string -100.100,200',Zend_Measure_Frequency::STANDARD,'de');
        $this->assertEquals(-100100.200, $value->getValue(),'Zend_Measure_Frequency Object not returned');
    }


    /**
     * test for equality
     * expected true
     */
    public function testFrequencyEquality()
    {
        $value = new Zend_Measure_Frequency('string -100.100,200',Zend_Measure_Frequency::STANDARD,'de');
        $newvalue = new Zend_Measure_Frequency('otherstring -100.100,200',Zend_Measure_Frequency::STANDARD,'de');
        $this->assertTrue($value->equals($newvalue),'Zend_Measure_Frequency Object should be equal');
    }


    /**
     * test for no equality
     * expected false
     */
    public function testFrequencyNoEquality()
    {
        $value = new Zend_Measure_Frequency('string -100.100,200',Zend_Measure_Frequency::STANDARD,'de');
        $newvalue = new Zend_Measure_Frequency('otherstring -100,200',Zend_Measure_Frequency::STANDARD,'de');
        $this->assertFalse($value->equals($newvalue),'Zend_Measure_Frequency Object should be not equal');
    }


    /**
     * test for serialization
     * expected string
     */
    public function testFrequencySerialize()
    {
        $value = new Zend_Measure_Frequency('string -100.100,200',Zend_Measure_Frequency::STANDARD,'de');
        $serial = $value->serialize();
        $this->assertTrue(!empty($serial),'Zend_Measure_Frequency not serialized');
    }


    /**
     * test for unserialization
     * expected object
     */
    public function testFrequencyUnSerialize()
    {
        $value = new Zend_Measure_Frequency('string -100.100,200',Zend_Measure_Frequency::STANDARD,'de');
        $serial = $value->serialize();
        $newvalue = unserialize($serial);
        $this->assertTrue($value->equals($newvalue),'Zend_Measure_Frequency not unserialized');
    }


    /**
     * test for set positive value
     * expected integer
     */
    public function testFrequencySetPositive()
    {
        $value = new Zend_Measure_Frequency('100',Zend_Measure_Frequency::STANDARD,'de');
        $value->setValue('200',Zend_Measure_Frequency::STANDARD,'de');
        $this->assertEquals(200, $value->getValue(), 'Zend_Measure_Frequency value expected to be a positive integer');
    }


    /**
     * test for set negative value
     * expected integer
     */
    public function testFrequencySetNegative()
    {
        $value = new Zend_Measure_Frequency('-100',Zend_Measure_Frequency::STANDARD,'de');
        $value->setValue('-200',Zend_Measure_Frequency::STANDARD,'de');
        $this->assertEquals(-200, $value->getValue(), 'Zend_Measure_Frequency value expected to be a negative integer');
    }


    /**
     * test for set decimal value
     * expected float
     */
    public function testFrequencySetDecimal()
    {
        $value = new Zend_Measure_Frequency('-100,200',Zend_Measure_Frequency::STANDARD,'de');
        $value->setValue('-200,200',Zend_Measure_Frequency::STANDARD,'de');
        $this->assertEquals(-200.200, $value->getValue(), 'Zend_Measure_Frequency value expected to be a decimal value');
    }


    /**
     * test for set decimal seperated value
     * expected float
     */
    public function testFrequencySetDecimalSeperated()
    {
        $value = new Zend_Measure_Frequency('-100.100,200',Zend_Measure_Frequency::STANDARD,'de');
        $value->setValue('-200.200,200',Zend_Measure_Frequency::STANDARD,'de');
        $this->assertEquals(-200200.200, $value->getValue(),'Zend_Measure_Frequency Object not returned');
    }


    /**
     * test for set string with integrated value
     * expected float
     */
    public function testFrequencySetString()
    {
        $value = new Zend_Measure_Frequency('string -100.100,200',Zend_Measure_Frequency::STANDARD,'de');
        $value->setValue('otherstring -200.200,200',Zend_Measure_Frequency::STANDARD,'de');
        $this->assertEquals(-200200.200, $value->getValue(),'Zend_Measure_Frequency Object not returned');
    }


    /**
     * test for exception unknown type
     * expected exception
     */
    public function testFrequencySetUnknownType()
    {
        try {
            $value = new Zend_Measure_Frequency('100',Zend_Measure_Frequency::STANDARD,'de');
            $value->setValue('otherstring -200.200,200','Frequency::UNKNOWN','de');
            $this->assertTrue(false,'Exception expected because of unknown type');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test for exception unknown value
     * expected exception
     */
    public function testFrequencySetUnknownValue()
    {
        try {
            $value = new Zend_Measure_Frequency('100',Zend_Measure_Frequency::STANDARD,'de');
            $value->setValue('novalue',Zend_Measure_Frequency::STANDARD,'de');
            $this->assertTrue(false,'Exception expected because of empty value');
        } catch (Exception $e) {
            return; // Test OK
        }
    }


    /**
     * test for exception unknown locale
     * expected exception
     */
    public function testFrequencySetUnknownLocale()
    {
        try {
            $value = new Zend_Measure_Frequency('100',Zend_Measure_Frequency::STANDARD,'de');
            $value->setValue('200',Zend_Measure_Frequency::STANDARD,'nolocale');
            $this->assertTrue(false,'Exception expected because of unknown locale');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test setting type
     * expected new type
     */
    public function testFrequencySetType()
    {
        $value = new Zend_Measure_Frequency('-100',Zend_Measure_Frequency::STANDARD,'de');
        $value->setType(Zend_Measure_Frequency::KILOHERTZ);
        $this->assertEquals($value->getType(), Zend_Measure_Frequency::KILOHERTZ, 'Zend_Measure_Frequency type expected');
    }


    /**
     * test setting computed type
     * expected new type
     */
    public function testFrequencySetComputedType1()
    {
        $value = new Zend_Measure_Frequency('-100',Zend_Measure_Frequency::RADIAN_PER_HOUR,'de');
        $value->setType(Zend_Measure_Frequency::RPM);
        $this->assertEquals($value->getType(), Zend_Measure_Frequency::RPM, 'Zend_Measure_Frequency type expected');
    }


    /**
     * test setting computed type
     * expected new type
     */
    public function testFrequencySetComputedType2()
    {
        $value = new Zend_Measure_Frequency('-100',Zend_Measure_Frequency::RPM,'de');
        $value->setType(Zend_Measure_Frequency::RADIAN_PER_HOUR);
        $this->assertEquals($value->getType(), Zend_Measure_Frequency::RADIAN_PER_HOUR, 'Zend_Measure_Frequency type expected');
    }


    /**
     * test setting unknown type
     * expected new type
     */
    public function testFrequencySetTypeFailed()
    {
        try {
            $value = new Zend_Measure_Frequency('-100',Zend_Measure_Frequency::STANDARD,'de');
            $value->setType('Frequency::UNKNOWN');
            $this->assertTrue(false,'Exception expected because of unknown type');
        } catch (Exception $e) {
            return true; // OK
        }
    }


    /**
     * test toString
     * expected string
     */
    public function testFrequencyToString()
    {
        $value = new Zend_Measure_Frequency('-100',Zend_Measure_Frequency::STANDARD,'de');
        $this->assertEquals($value->toString(), '-100 Hz', 'Value -100 Hz expected');
    }


    /**
     * test __toString
     * expected string
     */
    public function testFrequency_ToString()
    {
        $value = new Zend_Measure_Frequency('-100',Zend_Measure_Frequency::STANDARD,'de');
        $this->assertEquals($value->__toString(), '-100 Hz', 'Value -100 Hz expected');
    }


    /**
     * test getConversionList
     * expected array
     */
    public function testFrequencyConversionList()
    {
        $value = new Zend_Measure_Frequency('-100',Zend_Measure_Frequency::STANDARD,'de');
        $unit  = $value->getConversionList();
        $this->assertTrue(is_array($unit), 'Array expected');
    }
}
