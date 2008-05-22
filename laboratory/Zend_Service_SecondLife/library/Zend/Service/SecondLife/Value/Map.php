<?php

require_once 'Zend/Service/SecondLife/Value/Collection.php';

require_once 'Zend/Service/SecondLife/Value/Key.php';

class Zend_Service_SecondLife_Value_Map extends Zend_Service_SecondLife_Value_Collection
{
    protected $_name = self::SECONDLIFE_TYPE_MAP;

    public function toXml()
    {
        if (null === $this->_asXml) {
            $dom = new DOMDocument("1.0", "UTF-8");
            $element = $dom->appendChild($dom->createElement($this->_name));
            if (is_array($this->_value)) {
                foreach ($this->_value as $key => $value) {
                    $key = new Zend_Service_SecondLife_Value_Key($key);
                    $element->appendChild($dom->importNode($key->getAsDom(), true));
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
            foreach ($this->_value as $key => $value) {
                $values[$key] = $value->getValue();
            }
        }
        return $values;
    }
}
