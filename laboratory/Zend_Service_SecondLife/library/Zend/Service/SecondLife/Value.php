<?php
abstract class Zend_Service_SecondLife_Value
{
    const SECONDLIFE_TYPE_ROOT    = 'llsd';
    const SECONDLIFE_TYPE_STRING  = 'string';
    const SECONDLIFE_TYPE_MAP     = 'map';
    const SECONDLIFE_TYPE_ARRAY   = 'array';
    const SECONDLIFE_TYPE_INTEGER = 'integer';
    const SECONDLIFE_TYPE_FLOAT   = 'float';
    const SECONDLIFE_TYPE_BOOLEAN = 'boolean';
    const SECONDLIFE_TYPE_KEY     = 'key';

    protected $_type;
    protected $_value;
    protected $_name;

    protected $_asDom = null;
    protected $_asXml = null;

    public function __construct($value = null)
    {
        if ($value !== null) {
            $this->setValue($value);
        }
    }

    public static function parse($value)
    {
        switch (gettype($value)) {
            case "string":
            case "NULL":
                require_once 'Zend/Service/SecondLife/Value/String.php';
                return new Zend_Service_SecondLife_Value_String($value);

            case "integer":
                require_once 'Zend/Service/SecondLife/Value/Integer.php';
                return new Zend_Service_SecondLife_Value_Integer($value);

            case 'double':
                require_once 'Zend/Service/SecondLife/Value/Float.php';
                return new Zend_Service_SecondLife_Value_Float($value);

            case "boolean":
                require_once 'Zend/Service/SecondLife/Value/Boolean.php';
                return new Zend_Service_SecondLife_Value_Boolean($value);

            case "array":
                $is_map = false;
                $counter = 0;
                foreach ($value as $key => $row) {
                    if ($key !== $counter) {
                        $is_map = true;
                    }
                    $counter++;
                }
                if ($is_map) {
                    require_once 'Zend/Service/SecondLife/Value/Map.php';
                    return new Zend_Service_SecondLife_Value_Map($value);
                } else {
                    require_once 'Zend/Service/SecondLife/Value/Array.php';
                    return new Zend_Service_SecondLife_Value_Array($value);
                }
    
            default:
                require_once 'Zend/Service/SecondLife/Value/Exception.php';
                throw new Zend_Service_SecondLife_Value_Exception(
                    sprintf(
                        'Type "%s" not handled',
                        is_object($value) ? get_class($value) : gettype($value)
                    )
                );
        }
    }

    public static function fromXml($value)
    {
        if (!$value instanceof SimpleXMLElement) {
            try {
                /** Ugly hack to ignore parse errors */
                $value = @new SimpleXMLElement($value);
            } catch (Exception $e) {
                require_once 'Zend/Service/SecondLife/Value/Exception.php';
                throw new Zend_Service_SecondLife_Value_Exception('Could not parse snippet');
            }
        }

        $type = $value->getName();

        switch ($type) {
            case self::SECONDLIFE_TYPE_INTEGER:
                require_once 'Zend/Service/SecondLife/Value/Integer.php';
                $secondlife_value = new Zend_Service_SecondLife_Value_Integer($value);
                break;

            case self::SECONDLIFE_TYPE_BOOLEAN:
                require_once 'Zend/Service/SecondLife/Value/Boolean.php';
                $secondlife_value = new Zend_Service_SecondLife_Value_Boolean($value);
                break;

            case self::SECONDLIFE_TYPE_STRING:
                require_once 'Zend/Service/SecondLife/Value/String.php';
                $secondlife_value = new Zend_Service_SecondLife_Value_String($value);
                break;

            case self::SECONDLIFE_TYPE_MAP:
                $collection = array();
                $values     = array();
                $keys       = array();
                foreach ($value->children() as $member) {
                    if ($member->getName() == 'key') {
                        $keys[] = (string)$member;
                    } else {
                        $values[] = self::fromXml($member)->getValue();
                    }
                }

                foreach ($keys as $pos => $key) {
                    $collection[$key] = $values[$pos];
                }

                require_once 'Zend/Service/SecondLife/Value/Map.php';
                $secondlife_value = new Zend_Service_SecondLife_Value_Map($collection);
                break;

            case self::SECONDLIFE_TYPE_ARRAY:
                $values = array();
                foreach ($value->children() as $member) {
                    $values[] = self::fromXml($member)->getValue();
                }

                require_once 'Zend/Service/SecondLife/Value/Array.php';
                $secondlife_value = new Zend_Service_SecondLife_Value_Array($values);
                break;

            case self::SECONDLIFE_TYPE_ROOT:
                require_once 'Zend/Service/SecondLife/Value/Llsd.php';
                $secondlife_value = new Zend_Service_SecondLife_Value_Llsd(
                    count($value->children()) > 0
                    ? self::fromXml($value->children())
                    : null
                );
                break;

            case self::SECONDLIFE_TYPE_KEY:
                require_once 'Zend/Service/SecondLife/Value/Exception.php';
                throw new Zend_Service_SecondLife_Value_Exception(
                    'Type "key" cannot be used standalone'
                );
                break;

            default:
                require_once 'Zend/Service/SecondLife/Value/Exception.php';
                throw new Zend_Service_SecondLife_Value_Exception(
                    sprintf(
                        'Type "%s" not handled',
                        is_object($value) ? get_class($value) : gettype($value)
                    )
                );
        }

        $secondlife_value->_setXml($value->asXml());

        return $secondlife_value;
    }

    public function setValue($value)
    {
        $this->_value = $value;
        $this->_asXml = null;
        $this->_asDom = null;
        return $this;
    }

    abstract public function getValue();

    abstract public function toXml();

    protected function _getValue()
    {
        return $this->_value;
    }

    public function getAsDom()
    {
        $this->toXml();
        return $this->_asDom;
    }

    private function _setXml($xml)
    {
        $this->_asXml = $xml;
    }

    protected function _xmlEncode($string)
    {
        return htmlentities($string, ENT_QUOTES, 'UTF-8');
    }

    protected function _xmlDecode($string)
    {
        return html_entity_decode($string, ENT_QUOTES, 'UTF-8');
    }

    protected function _clear(DOMDocument $dom)
    {
        $xml = preg_replace('/<\?xml version="1.0" encoding="[^\"]*"\?>\n/u', '', $dom->saveXML());
        return trim($xml);
    }
}
