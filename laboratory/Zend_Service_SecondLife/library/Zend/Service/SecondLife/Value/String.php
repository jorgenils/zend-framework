<?php

require_once 'Zend/Service/SecondLife/Value/Scalar.php';

class Zend_Service_SecondLife_Value_String extends Zend_Service_SecondLife_Value_Scalar
{
    protected $_name = self::SECONDLIFE_TYPE_STRING;

    public function _getValue()
    {
        return $this->_xmlDecode($this->_value);
    }

    public function getValue()
    {
        return $this->_xmlDecode($this->_value);
    }

    public function setValue($value)
    {
        parent::setValue($this->_xmlEncode((string)$value));
    }
}
