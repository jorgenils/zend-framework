<?php

require_once 'Zend/Service/SecondLife/Value/Scalar.php';

class Zend_Service_SecondLife_Value_Boolean extends Zend_Service_SecondLife_Value_Scalar
{
    
    protected $_name = self::SECONDLIFE_TYPE_BOOLEAN;

    public function setValue($value)
    {
        $value = (string)$value;
        $value = strtolower($value) === 'false' ? false : true;
        $value = (int)(bool)(string)$value;
        parent::setValue($value);
    }

    public function getValue()
    {
        return (bool)$this->_value;
    }

    protected function _getValue()
    {
        return $this->getValue() ? 'true' : 'false';
    }
}
