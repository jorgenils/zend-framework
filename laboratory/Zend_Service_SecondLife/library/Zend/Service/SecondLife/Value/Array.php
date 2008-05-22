<?php

require_once 'Zend/Service/SecondLife/Value/Collection.php';

class Zend_Service_SecondLife_Value_Array extends Zend_Service_SecondLife_Value_Collection
{
    protected $_name = self::SECONDLIFE_TYPE_ARRAY;

    public function toXml()
    {
        if (null === $this->_asXml) {
            $dom     = new DOMDocument('1.0', 'UTF-8');
            $element = $dom->appendChild($dom->createElement($this->_name));
            if (is_array($this->_value)) {
                foreach ($this->_value as $value) {
                    $element->appendChild($dom->importNode($value->getAsDom(), true));
                }
            }
            $this->_asDom = $element;
            $this->_asXml = $this->_clear($dom);
        }
        return $this->_asXml;
    }

    public function getValue()
    {
        $values = array();
        if (is_array($this->_value)) {
            foreach ($this->_value as $value) {
                $values[] = $value->getValue();
            }
        }
        return $values;
    }
}
