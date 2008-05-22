<?php
require_once 'Zend/Service/SecondLife/Value/Scalar.php';

class Zend_Service_SecondLife_Value_Integer extends Zend_Service_SecondLife_Value_Scalar
{
    protected $_name = self::SECONDLIFE_TYPE_INTEGER;

    public function setValue($value)
    {
        return parent::setValue((integer)$value);
    }

    public function getValue()
    {
        return (integer)$this->_value;
    }
}
