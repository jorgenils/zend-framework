<?php
require_once 'Zend/Service/SecondLife/Coordinate.php';
require_once 'Zend/Service/SecondLife/Coordinate/Look.php';
require_once 'Zend/Service/SecondLife/Coordinate/Exception.php';

class Zend_Service_SecondLife_CoordinateTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->x = rand(0, 256);
        $this->y = rand(0, 256);
        $this->z = rand(0, 256);

        $this->xl = rand(0, 1);
        $this->yl = rand(0, 1);
        $this->zl = rand(0, 1);
    }

    public function testDefaultIsZero()
    {
        $coord = new Zend_Service_SecondLife_Coordinate();
        $this->assertEquals(0.0, $coord->getX());
        $this->assertEquals(0.0, $coord->getY());
        $this->assertEquals(0.0, $coord->getZ());
    }

    public function testSettingCoordinate()
    {
        $coord = new Zend_Service_SecondLife_Coordinate($this->x, $this->y, $this->z);
        $this->assertEquals($this->x, $coord->getX());
        $this->assertEquals($this->y, $coord->getY());
        $this->assertEquals($this->z, $coord->getZ());
    }

    public function testExceptionWhenNotPassingANumberAsX()
    {
        require_once 'Zend/Service/SecondLife/Coordinate/Exception.php';
        $this->setExpectedException('Zend_Service_SecondLife_Coordinate_Exception');
        new Zend_Service_SecondLife_Coordinate('str', $this->y, $this->z);
    }

    public function testExceptionWhenNotPassingANumberAsY()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Coordinate_Exception');
        new Zend_Service_SecondLife_Coordinate($this->x, 'str', $this->z);
    }

    public function testExceptionWhenNotPassingANumberAsZ()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Coordinate_Exception');
        new Zend_Service_SecondLife_Coordinate($this->x, $this->y, 'str');
    }

    public function testExceptionWhenPassingATooLargeNumberAsX()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Coordinate_Exception');
        new Zend_Service_SecondLife_Coordinate(266, $this->y, $this->z);
    }

    public function testExceptionWhenPassingATooLargeNumberAsY()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Coordinate_Exception');
        new Zend_Service_SecondLife_Coordinate($this->x, 266, $this->z);
    }

    public function testExceptionWhenPassingATooLargeNumberAsZ()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Coordinate_Exception');
        new Zend_Service_SecondLife_Coordinate($this->x, $this->y, 266);
    }

    public function testExceptionWhenPassingTooSmallNumberAsX()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Coordinate_Exception');
        new Zend_Service_SecondLife_Coordinate(-1, $this->y, $this->z);
    }

    public function testExceptionWhenPassingTooSmallNumberAsY()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Coordinate_Exception');
        new Zend_Service_SecondLife_Coordinate($this->x, -1, $this->z);
    }

    public function testExceptionWhenPassingTooSmallNumberAsZ()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Coordinate_Exception');
        new Zend_Service_SecondLife_Coordinate($this->x, $this->y, -1);
    }

    public function testExceptionWhenPassingTooSmallNumberAsXForLookCoord()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Coordinate_Exception');
        new Zend_Service_SecondLife_Coordinate_Look(-1, $this->yl, $this->zl);
    }

    public function testExceptionWhenPassingTooSmallNumberAsYForLookCoord()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Coordinate_Exception');
        new Zend_Service_SecondLife_Coordinate_Look($this->xl, -1, $this->zl);
    }

    public function testExceptionWhenPassingTooSmallNumberAsZForLookCoord()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Coordinate_Exception');
        new Zend_Service_SecondLife_Coordinate_Look($this->xl, $this->yl, -1);
    }


    public function testExceptionWhenPassingATooLargeNumberAsXForLookCoord()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Coordinate_Exception');
        new Zend_Service_SecondLife_Coordinate_Look(2, $this->yl, $this->zl);
    }

    public function testExceptionWhenPassingATooLargeNumberAsYForLookCoord()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Coordinate_Exception');
        new Zend_Service_SecondLife_Coordinate_Look($this->xl, 2, $this->zl);
    }

    public function testExceptionWhenPassingATooLargeNumberAsZForLookCoord()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Coordinate_Exception');
        new Zend_Service_SecondLife_Coordinate_Look($this->xl, $this->yl, 2);
    }
}
