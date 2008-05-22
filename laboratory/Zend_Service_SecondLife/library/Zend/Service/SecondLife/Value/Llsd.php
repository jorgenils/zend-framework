<?php
require_once 'Zend/Service/SecondLife/Value.php';

class Zend_Service_SecondLife_Value_Llsd extends Zend_Service_SecondLife_Value
{
    protected $_name = self::SECONDLIFE_TYPE_ROOT;

    public function setValue($value)
    {
        if (null !== $value and !$value instanceof parent) {
            $this->_value = parent::parse($value);
        } else {
            $this->_value = $value;
        }
        $this->_asDom = null;
        $this->_asXml = null;
    }

    public function toXml()
    {
        if (null === $this->_asXml) {
            $dom = new DOMDocument("1.0", "UTF-8");
            $element = $dom->appendChild($dom->createElement($this->_name));
            $element->appendChild($dom->importNode($this->_value->getAsDom(), true));

            $this->_asDom = $element;
            $this->_asXml = $this->_clear($dom);
        }

        return $this->_asXml;
    }

    public function getValue()
    {
        if (null === $this->_value) {
            return null;
        } else {
            return $this->_value->getValue();
        }
    }
}
