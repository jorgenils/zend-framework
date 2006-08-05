<?php
/**
 * @package    Zend_Measure
 * @subpackage UnitTests
 */


/**
 * Zend_Measure_Capacity
 */
require_once 'Zend/Measure/Capacity.php';

/**
 * PHPUnit2 test case
 */
require_once 'PHPUnit2/Framework/TestCase.php';


/**
 * @package    Zend_Measure
 * @subpackage UnitTests
 */
class Zend_Measure_CapacityTest extends PHPUnit2_Framework_TestCase
{

    public function setUp()
    {
    }
    
    
    /**
     * test for capacity initialisation
     * expected instance
     */
    public function testCapacityInit()
    {
        $value = new Zend_Measure_Capacity('100',Zend_Measure_Capacity::STANDARD,'de');
        $this->assertTrue($value instanceof Zend_Measure_Capacity,'Zend_Measure_Capacity Object not returned');
    }


    /**
     * test for exception unknown type
     * expected exception
     */
    public function testCapacityUnknownType()
    {
        try {
            $value = new Zend_Measure_Capacity('100','Capacity::UNKNOWN','de');
            $this->assertTrue(false,'Exception expected because of unknown type');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test for exception unknown value
     * expected exception
     */
    public function testCapacityUnknownValue()
    {
        try {
            $value = new Zend_Measure_Capacity('novalue',Zend_Measure_Capacity::STANDARD,'de');
            $this->assertTrue(false,'Exception expected because of empty value');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test for exception unknown locale
     * expected root value
     */
    public function testCapacityUnknownLocale()
    {
        try {
            $value = new Zend_Measure_Capacity('100',Zend_Measure_Capacity::STANDARD,'nolocale');
            $this->assertTrue(false,'Exception expected because of unknown locale');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test for positive value
     * expected integer
     */
    public function testCapacityValuePositive()
    {
        $value = new Zend_Measure_Capacity('100',Zend_Measure_Capacity::STANDARD,'de');
        $this->assertEquals(100, $value->getValue(), 'Zend_Measure_Capacity value expected to be a positive integer');
    }


    /**
     * test for negative value
     * expected integer
     */
    public function testCapacityValueNegative()
    {
        $value = new Zend_Measure_Capacity('-100',Zend_Measure_Capacity::STANDARD,'de');
        $this->assertEquals(-100, $value->getValue(), 'Zend_Measure_Capacity value expected to be a negative integer');
    }


    /**
     * test for decimal value
     * expected float
     */
    public function testCapacityValueDecimal()
    {
        $value = new Zend_Measure_Capacity('-100,200',Zend_Measure_Capacity::STANDARD,'de');
        $this->assertEquals(-100.200, $value->getValue(), 'Zend_Measure_Capacity value expected to be a decimal value');
    }


    /**
     * test for decimal seperated value
     * expected float
     */
    public function testCapacityValueDecimalSeperated()
    {
        $value = new Zend_Measure_Capacity('-100.100,200',Zend_Measure_Capacity::STANDARD,'de');
        $this->assertEquals(-100100.200, $value->getValue(),'Zend_Measure_Capacity Object not returned');
    }


    /**
     * test for string with integrated value
     * expected float
     */
    public function testCapacityValueString()
    {
        $value = new Zend_Measure_Capacity('string -100.100,200',Zend_Measure_Capacity::STANDARD,'de');
        $this->assertEquals(-100100.200, $value->getValue(),'Zend_Measure_Capacity Object not returned');
    }


    /**
     * test for equality
     * expected true
     */
    public function testCapacityEquality()
    {
        $value = new Zend_Measure_Capacity('string -100.100,200',Zend_Measure_Capacity::STANDARD,'de');
        $newvalue = new Zend_Measure_Capacity('otherstring -100.100,200',Zend_Measure_Capacity::STANDARD,'de');
        $this->assertTrue($value->equals($newvalue),'Zend_Measure_Capacity Object should be equal');
    }


    /**
     * test for no equality
     * expected false
     */
    public function testCapacityNoEquality()
    {
        $value = new Zend_Measure_Capacity('string -100.100,200',Zend_Measure_Capacity::STANDARD,'de');
        $newvalue = new Zend_Measure_Capacity('otherstring -100,200',Zend_Measure_Capacity::STANDARD,'de');
        $this->assertFalse($value->equals($newvalue),'Zend_Measure_Capacity Object should be not equal');
    }


    /**
     * test for serialization
     * expected string
     */
    public function testCapacitySerialize()
    {
        $value = new Zend_Measure_Capacity('string -100.100,200',Zend_Measure_Capacity::STANDARD,'de');
        $serial = $value->serialize();
        $this->assertTrue(!empty($serial),'Zend_Measure_Capacity not serialized');
    }


    /**
     * test for unserialization
     * expected object
     */
    public function testCapacityUnSerialize()
    {
        $value = new Zend_Measure_Capacity('string -100.100,200',Zend_Measure_Capacity::STANDARD,'de');
        $serial = $value->serialize();
        $newvalue = unserialize($serial);
        $this->assertTrue($value->equals($newvalue),'Zend_Measure_Capacity not unserialized');
    }


    /**
     * test for set positive value
     * expected integer
     */
    public function testCapacitySetPositive()
    {
        $value = new Zend_Measure_Capacity('100',Zend_Measure_Capacity::STANDARD,'de');
        $value->setValue('200',Zend_Measure_Capacity::STANDARD,'de');
        $this->assertEquals(200, $value->getValue(), 'Zend_Measure_Capacity value expected to be a positive integer');
    }


    /**
     * test for set negative value
     * expected integer
     */
    public function testCapacitySetNegative()
    {
        $value = new Zend_Measure_Capacity('-100',Zend_Measure_Capacity::STANDARD,'de');
        $value->setValue('-200',Zend_Measure_Capacity::STANDARD,'de');
        $this->assertEquals(-200, $value->getValue(), 'Zend_Measure_Capacity value expected to be a negative integer');
    }


    /**
     * test for set decimal value
     * expected float
     */
    public function testCapacitySetDecimal()
    {
        $value = new Zend_Measure_Capacity('-100,200',Zend_Measure_Capacity::STANDARD,'de');
        $value->setValue('-200,200',Zend_Measure_Capacity::STANDARD,'de');
        $this->assertEquals(-200.200, $value->getValue(), 'Zend_Measure_Capacity value expected to be a decimal value');
    }


    /**
     * test for set decimal seperated value
     * expected float
     */
    public function testCapacitySetDecimalSeperated()
    {
        $value = new Zend_Measure_Capacity('-100.100,200',Zend_Measure_Capacity::STANDARD,'de');
        $value->setValue('-200.200,200',Zend_Measure_Capacity::STANDARD,'de');
        $this->assertEquals(-200200.200, $value->getValue(),'Zend_Measure_Capacity Object not returned');
    }


    /**
     * test for set string with integrated value
     * expected float
     */
    public function testCapacitySetString()
    {
        $value = new Zend_Measure_Capacity('string -100.100,200',Zend_Measure_Capacity::STANDARD,'de');
        $value->setValue('otherstring -200.200,200',Zend_Measure_Capacity::STANDARD,'de');
        $this->assertEquals(-200200.200, $value->getValue(),'Zend_Measure_Capacity Object not returned');
    }


    /**
     * test for exception unknown type
     * expected exception
     */
    public function testCapacitySetUnknownType()
    {
        try {
            $value = new Zend_Measure_Capacity('100',Zend_Measure_Capacity::STANDARD,'de');
            $value->setValue('otherstring -200.200,200','Capacity::UNKNOWN','de');
            $this->assertTrue(false,'Exception expected because of unknown type');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test for exception unknown value
     * expected exception
     */
    public function testCapacitySetUnknownValue()
    {
        try {
            $value = new Zend_Measure_Capacity('100',Zend_Measure_Capacity::STANDARD,'de');
            $value->setValue('novalue',Zend_Measure_Capacity::STANDARD,'de');
            $this->assertTrue(false,'Exception expected because of empty value');
        } catch (Exception $e) {
            return; // Test OK
        }
    }


    /**
     * test for exception unknown locale
     * expected exception
     */
    public function testCapacitySetUnknownLocale()
    {
        try {
            $value = new Zend_Measure_Capacity('100',Zend_Measure_Capacity::STANDARD,'de');
            $value->setValue('200',Zend_Measure_Capacity::STANDARD,'nolocale');
            $this->assertTrue(false,'Exception expected because of unknown locale');
        } catch (Exception $e) {
            return true; // Test OK
        }
    }


    /**
     * test setting type
     * expected new type
     */
    public function testCapacitySetType()
    {
        $value = new Zend_Measure_Capacity('-100',Zend_Measure_Capacity::STANDARD,'de');
        $value->setType(Zend_Measure_Capacity::NANOFARAD);
        $this->assertEquals($value->getType(), Zend_Measure_Capacity::NANOFARAD, 'Zend_Measure_Capacity type expected');
    }


    /**
     * test setting unknown type
     * expected new type
     */
    public function testCapacitySetTypeFailed()
    {
        try {
            $value = new Zend_Measure_Capacity('-100',Zend_Measure_Capacity::STANDARD,'de');
            $value->setType('Capacity::UNKNOWN');
            $this->assertTrue(false,'Exception expected because of unknown type');
        } catch (Exception $e) {
            return true; // OK
        }
    }


    /**
     * test toString
     * expected string
     */
    public function testCapacityToString()
    {
        $value = new Zend_Measure_Capacity('-100',Zend_Measure_Capacity::STANDARD,'de');
        $this->assertEquals($value->toString(), '-100 F', 'Value -100 F expected');
    }


    /**
     * test __toString
     * expected string
     */
    public function testCapacity_ToString()
    {
        $value = new Zend_Measure_Capacity('-100',Zend_Measure_Capacity::STANDARD,'de');
        $this->assertEquals($value->__toString(), '-100 F', 'Value -100 F expected');
    }
}
