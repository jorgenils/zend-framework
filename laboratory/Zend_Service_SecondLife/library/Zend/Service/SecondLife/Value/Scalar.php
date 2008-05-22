<?php

require_once 'Zend/Service/SecondLife/Value.php';

abstract class Zend_Service_SecondLife_Value_Scalar extends Zend_Service_SecondLife_Value
{
    public function __construct($string)
    {
        $this->setValue($string);
    }

    public function toXml()
    {
        if (null === $this->_asXml) {
            $dom = new DOMDocument("1.0", "UTF-8");
            $element = $dom->appendChild($dom->createElement($this->_name));
            $element->appendChild($dom->createTextNode($this->_getValue()));

            $this->_asDom = $element;
            $this->_asXml = $this->_clear($dom);
        }
        return $this->_asXml;
    }
}
