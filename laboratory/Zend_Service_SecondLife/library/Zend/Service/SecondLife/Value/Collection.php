<?php

require_once 'Zend/Service/SecondLife/Value.php';

abstract class Zend_Service_SecondLife_Value_Collection extends Zend_Service_SecondLife_Value
{
    protected $_value = array();

    public function setValue($value)
    {
        if (is_array($value)) {
            $this->_value = array();
            foreach ($value as $key => $el) {
                if (!$el instanceof parent) {
                    $this->_value[$key] = parent::parse($el);
                } else {
                    $this->_value[$key] = $el;
                }
            }
        }
        $this->_asXml = null;
        $this->_asDom = null;
        return $this;
    }
}
