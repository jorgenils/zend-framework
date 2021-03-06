<?php

class Zend_Reflection_Docblock_Tag implements Reflector
{
    
    protected $_name = null;
    protected $_description = null;
    
    protected static $_tagClasses = array(
        'param'  => 'Zend_Reflection_Docblock_Tag_Param',
        'return' => 'Zend_Reflection_Docblock_Tag_Return',
        );
    
    public static function factory($tagDocblockLine)
    {
        if (preg_match('#^@(\w+)\s#', $tagDocblockLine, $matches)) {
            $tagName = $matches[1];
            if (array_key_exists($tagName, self::$_tagClasses)) {
                $tagClass = self::$_tagClasses[$tagName];
                if (!class_exists($tagClass)) {
                    Zend_Loader::loadClass($tagClass);
                }
                return new $tagClass($tagDocblockLine);
            } else {
                return new self($tagDocblockLine);
            }
        }
        
        throw new Zend_Reflection_Exception('No valid tag name found within provided docblock line.');
    }
    
    public static function export() {}
    public function __toString() {}
    
    public function __construct($tagDocblockLine)
    {
        if (!preg_match('#^@(\w+)\s(.*)?#', $tagDocblockLine, $matches)) {
            throw new Zend_Reflection_Exception('Provided docblock line is does not contain a valid tag');
        }
        
        $this->_name = $matches[1];
        if ($matches[2]) {
            $this->_description = $matches[2];
        }
        
    }
    
    public function getName()
    {
        return $this->_name;
    }
    
    public function getDescription()
    {
        return $this->_description;
    }
    
}
