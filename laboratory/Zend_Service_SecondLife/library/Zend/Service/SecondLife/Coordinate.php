<?php
class Zend_Service_SecondLife_Coordinate
{
    /**
     * X coordinate
     *
     * @var float
     */
    protected $_x = 0.0;

    /**
     * Y coordinate
     *
     * @var float
     */
    protected $_y = 0.0;

    /**
     * Z coordinate
     *
     * @var float
     */
    protected $_z = 0.0;

    /**
     * Minimum value
     *
     * @var float
     */
    protected $_min = 0.0;

    /**
     * Maximum value
     *
     * @var float
     */
    protected $_max = 256.0;

    /**
     * Create new coordinate
     *
     * @param number $x X coordinate
     * @param number $y Y coordinate
     * @param number $z Z coordinate
     */
    public function __construct($x = 0.0, $y = 0.0, $z = 0.0)
    {
        $this->_validate($x);
        $this->_x = (float)$x;

        $this->_validate($y);
        $this->_y = (float)$y;
        
        $this->_validate($z);
        $this->_z = (float)$z;
    }

    /**
     * Get X coordinate
     *
     * @return float
     */
    public function getX()
    {
        return $this->_x;
    }

    /**
     * Get X coordinate
     *
     * @return float
     */
    public function getY()
    {
        return $this->_y;
    }

    /**
     * Get Z coordinate
     *
     * @return float
     */
    public function getZ()
    {
        return $this->_z;
    }

    /**
     * Validate value
     *
     * @param number $value
     */
    protected function _validate($value)
    {
        if (!is_numeric($value)) {
            require_once 'Zend/Service/SecondLife/Coordinate/Exception.php';
            throw new Zend_Service_SecondLife_Coordinate_Exception(
                'Invalid value for coordinate. Numeric value expected'
            );
        }

        if ($value > $this->_max) {
            require_once 'Zend/Service/SecondLife/Coordinate/Exception.php';
            throw new Zend_Service_SecondLife_Coordinate_Exception(
                sprintf('Value must not be greater %s', $this->_max)
            );
        }
        
        if ($value < $this->_min) {
            require_once 'Zend/Service/SecondLife/Coordinate/Exception.php';
            throw new Zend_Service_SecondLife_Coordinate_Exception(
                sprintf('Value must not be less than %s', $this->_min)
            );
        }
    }
}
