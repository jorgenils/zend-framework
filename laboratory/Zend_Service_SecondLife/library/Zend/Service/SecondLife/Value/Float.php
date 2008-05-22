<?php
require_once 'Zend/Service/SecondLife/Value/Scalar.php';

class Zend_Service_SecondLife_Value_Float extends Zend_Service_SecondLife_Value_Scalar
{
    protected $_name = self::SECONDLIFE_TYPE_FLOAT;

    public function setValue($value)
    {
        return parent::setValue((float)$value);
    }

    public function getValue()
    {
        return (float)$this->_value;
    }
}
