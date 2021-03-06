<?php
/**
 * @package    Zend_Measure
 * @subpackage UnitTests
 */


/**
 * Zend_Measure_Illumination
 */
require_once 'Zend/Measure/Illumination.php';

/**
 * PHPUnit test case
 */
require_once 'PHPUnit/Framework/TestCase.php';


/**
 * @package    Zend_Measure
 * @subpackage UnitTests
 */
class Zend_Measure_IlluminationTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
    }


    /**
     * test for Illumination initialisation
     * expected instance
     */
    public function testIlluminationInit()
    {
        $value = new Zend_Measure_Illumination('100',Zend_Measure_Illumination::STANDARD,'de');
        $this->assertTrue($value instanceof Zend_Measure_Illumination,'Zend_Measure_Illumination Object not returned');
    }


    /**
     * test for exception unknown type
     * expected exception
     */
    public function testIlluminationUnknownType()
    {
        try {
            $value = new Zend_Measure_Illumination('100','Illumination::UNKNOWN','de');
            $this->fail('Exception expected because of unknown type');
        } catch (Zend_Measure_Exception $e) {
            // success
        }
    }


    /**
     * test for exception unknown value
     * expected exception
     */
    public function testIlluminationUnknownValue()
    {
        try {
            $value = new Zend_Measure_Illumination('novalue',Zend_Measure_Illumination::STANDARD,'de');
            $this->fail('Exception expected because of empty value');
        } catch (Zend_Measure_Exception $e) {
            // success
        }
    }


    /**
     * test for exception unknown locale
     * expected root value
     */
    public function testIlluminationUnknownLocale()
    {
        try {
            $value = new Zend_Measure_Illumination('100',Zend_Measure_Illumination::STANDARD,'nolocale');
            $this->fail('Exception expected because of unknown locale');
        } catch (Zend_Measure_Exception $e) {
            // success
        }
    }


    /**
     * test for standard locale
     * expected integer
     */
    public function testIlluminationNoLocale()
    {
        $value = new Zend_Measure_Illumination('100',Zend_Measure_Illumination::STANDARD);
        $this->assertEquals(100, $value->getValue(),'Zend_Measure_Illumination value expected');
    }


    /**
     * test for positive value
     * expected integer
     */
    public function testIlluminationValuePositive()
    {
        $value = new Zend_Measure_Illumination('100',Zend_Measure_Illumination::STANDARD,'de');
        $this->assertEquals(100, $value->getValue(), 'Zend_Measure_Illumination value expected to be a positive integer');
    }


    /**
     * test for negative value
     * expected integer
     */
    public function testIlluminationValueNegative()
    {
        $value = new Zend_Measure_Illumination('-100',Zend_Measure_Illumination::STANDARD,'de');
        $this->assertEquals(-100, $value->getValue(), 'Zend_Measure_Illumination value expected to be a negative integer');
    }


    /**
     * test for decimal value
     * expected float
     */
    public function testIlluminationValueDecimal()
    {
        $value = new Zend_Measure_Illumination('-100,200',Zend_Measure_Illumination::STANDARD,'de');
        $this->assertEquals(-100.200, $value->getValue(), 'Zend_Measure_Illumination value expected to be a decimal value');
    }


    /**
     * test for decimal seperated value
     * expected float
     */
    public function testIlluminationValueDecimalSeperated()
    {
        $value = new Zend_Measure_Illumination('-100.100,200',Zend_Measure_Illumination::STANDARD,'de');
        $this->assertEquals(-100100.200, $value->getValue(),'Zend_Measure_Illumination Object not returned');
    }


    /**
     * test for string with integrated value
     * expected float
     */
    public function testIlluminationValueString()
    {
        $value = new Zend_Measure_Illumination('string -100.100,200',Zend_Measure_Illumination::STANDARD,'de');
        $this->assertEquals(-100100.200, $value->getValue(),'Zend_Measure_v Object not returned');
    }


    /**
     * test for equality
     * expected true
     */
    public function testIlluminationEquality()
    {
        $value = new Zend_Measure_Illumination('string -100.100,200',Zend_Measure_Illumination::STANDARD,'de');
        $newvalue = new Zend_Measure_Illumination('otherstring -100.100,200',Zend_Measure_Illumination::STANDARD,'de');
        $this->assertTrue($value->equals($newvalue),'Zend_Measure_Illumination Object should be equal');
    }


    /**
     * test for no equality
     * expected false
     */
    public function testIlluminationNoEquality()
    {
        $value = new Zend_Measure_Illumination('string -100.100,200',Zend_Measure_Illumination::STANDARD,'de');
        $newvalue = new Zend_Measure_Illumination('otherstring -100,200',Zend_Measure_Illumination::STANDARD,'de');
        $this->assertFalse($value->equals($newvalue),'Zend_Measure_Illumination Object should be not equal');
    }


    /**
     * test for serialization
     * expected string
     */
    public function testIlluminationSerialize()
    {
        $value = new Zend_Measure_Illumination('string -100.100,200',Zend_Measure_Illumination::STANDARD,'de');
        $serial = $value->serialize();
        $this->assertTrue(!empty($serial),'Zend_Measure_Illumination not serialized');
    }


    /**
     * test for unserialization
     * expected object
     */
    public function testIlluminationUnSerialize()
    {
        $value = new Zend_Measure_Illumination('string -100.100,200',Zend_Measure_Illumination::STANDARD,'de');
        $serial = $value->serialize();
        $newvalue = unserialize($serial);
        $this->assertTrue($value->equals($newvalue),'Zend_Measure_Illumination not unserialized');
    }


    /**
     * test for set positive value
     * expected integer
     */
    public function testIlluminationSetPositive()
    {
        $value = new Zend_Measure_Illumination('100',Zend_Measure_Illumination::STANDARD,'de');
        $value->setValue('200',Zend_Measure_Illumination::STANDARD,'de');
        $this->assertEquals(200, $value->getValue(), 'Zend_Measure_Illumination value expected to be a positive integer');
    }


    /**
     * test for set negative value
     * expected integer
     */
    public function testIlluminationSetNegative()
    {
        $value = new Zend_Measure_Illumination('-100',Zend_Measure_Illumination::STANDARD,'de');
        $value->setValue('-200',Zend_Measure_Illumination::STANDARD,'de');
        $this->assertEquals(-200, $value->getValue(), 'Zend_Measure_Illumination value expected to be a negative integer');
    }


    /**
     * test for set decimal value
     * expected float
     */
    public function testIlluminationSetDecimal()
    {
        $value = new Zend_Measure_Illumination('-100,200',Zend_Measure_Illumination::STANDARD,'de');
        $value->setValue('-200,200',Zend_Measure_Illumination::STANDARD,'de');
        $this->assertEquals(-200.200, $value->getValue(), 'Zend_Measure_Illumination value expected to be a decimal value');
    }


    /**
     * test for set decimal seperated value
     * expected float
     */
    public function testIlluminationSetDecimalSeperated()
    {
        $value = new Zend_Measure_Illumination('-100.100,200',Zend_Measure_Illumination::STANDARD,'de');
        $value->setValue('-200.200,200',Zend_Measure_Illumination::STANDARD,'de');
        $this->assertEquals(-200200.200, $value->getValue(),'Zend_Measure_Illumination Object not returned');
    }


    /**
     * test for set string with integrated value
     * expected float
     */
    public function testIlluminationSetString()
    {
        $value = new Zend_Measure_Illumination('string -100.100,200',Zend_Measure_Illumination::STANDARD,'de');
        $value->setValue('otherstring -200.200,200',Zend_Measure_Illumination::STANDARD,'de');
        $this->assertEquals(-200200.200, $value->getValue(),'Zend_Measure_Illumination Object not returned');
    }


    /**
     * test for exception unknown type
     * expected exception
     */
    public function testIlluminationSetUnknownType()
    {
        try {
            $value = new Zend_Measure_Illumination('100',Zend_Measure_Illumination::STANDARD,'de');
            $value->setValue('otherstring -200.200,200','Illumination::UNKNOWN','de');
            $this->fail('Exception expected because of unknown type');
        } catch (Zend_Measure_Exception $e) {
            // success
        }
    }


    /**
     * test for exception unknown value
     * expected exception
     */
    public function testIlluminationSetUnknownValue()
    {
        try {
            $value = new Zend_Measure_Illumination('100',Zend_Measure_Illumination::STANDARD,'de');
            $value->setValue('novalue',Zend_Measure_Illumination::STANDARD,'de');
            $this->fail('Exception expected because of empty value');
        } catch (Zend_Measure_Exception $e) {
            // success
        }
    }


    /**
     * test for exception unknown locale
     * expected exception
     */
    public function testIlluminationSetUnknownLocale()
    {
        try {
            $value = new Zend_Measure_Illumination('100',Zend_Measure_Illumination::STANDARD,'de');
            $value->setValue('200',Zend_Measure_Illumination::STANDARD,'nolocale');
            $this->fail('Exception expected because of unknown locale');
        } catch (Zend_Measure_Exception $e) {
            // success
        }
    }


    /**
     * test for exception unknown locale
     * expected exception
     */
    public function testIlluminationSetWithNoLocale()
    {
        $value = new Zend_Measure_Illumination('100', Zend_Measure_Illumination::STANDARD, 'de');
        $value->setValue('200', Zend_Measure_Illumination::STANDARD);
        $this->assertEquals(200, $value->getValue(), 'Zend_Measure_Illumination value expected to be a positive integer');
    }


    /**
     * test setting type
     * expected new type
     */
    public function testIlluminationSetType()
    {
        $value = new Zend_Measure_Illumination('-100',Zend_Measure_Illumination::STANDARD,'de');
        $value->setType(Zend_Measure_Illumination::NOX);
        $this->assertEquals($value->getType(), Zend_Measure_Illumination::NOX, 'Zend_Measure_Illumination type expected');
    }


    /**
     * test setting unknown type
     * expected new type
     */
    public function testIlluminationSetTypeFailed()
    {
        try {
            $value = new Zend_Measure_Illumination('-100',Zend_Measure_Illumination::STANDARD,'de');
            $value->setType('Illumination::UNKNOWN');
            $this->fail('Exception expected because of unknown type');
        } catch (Zend_Measure_Exception $e) {
            // success
        }
    }


    /**
     * test toString
     * expected string
     */
    public function testIlluminationToString()
    {
        $value = new Zend_Measure_Illumination('-100',Zend_Measure_Illumination::STANDARD,'de');
        $this->assertEquals($value->toString(), '-100 lx', 'Value -100 lx expected');
    }


    /**
     * test __toString
     * expected string
     */
    public function testIllumination_ToString()
    {
        $value = new Zend_Measure_Illumination('-100',Zend_Measure_Illumination::STANDARD,'de');
        $this->assertEquals($value->__toString(), '-100 lx', 'Value -100 lx expected');
    }


    /**
     * test getConversionList
     * expected array
     */
    public function testIlluminationConversionList()
    {
        $value = new Zend_Measure_Illumination('-100',Zend_Measure_Illumination::STANDARD,'de');
        $unit  = $value->getConversionList();
        $this->assertTrue(is_array($unit), 'Array expected');
    }
}
