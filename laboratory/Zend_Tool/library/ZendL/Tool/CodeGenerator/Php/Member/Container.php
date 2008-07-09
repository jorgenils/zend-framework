<?php

class ZendL_Tool_CodeGenerator_Php_Member_Container extends ArrayObject
{
    
    const TYPE_PROPERTY = 'property';
    const TYPE_METHOD   = 'method';
    
    protected $_type = self::TYPE_PROPERTY;
    
    public function __construct($type = self::TYPE_PROPERTY)
    {
        $this->_type = $type;
    }
    
}