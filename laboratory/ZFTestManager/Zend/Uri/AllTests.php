<?php

/**
 * @category   Zend
 * @package    Zend_Uri
 * @subpackage UnitTests
 */

class Zend_Uri_AllTests extends ZFTestManager_AllTests_Abstract
{
    public static $disabled = array();
    public static $requires_config = array();
        
    /**
     * Suite() - this is the stock AllTests::suite() method.  This method
     * will return a TestSuite of the tests from the same directory.  The
     * unit testing conventions used in creating this test suite are 
     * documented in the Zend Framework wiki.  
     * 
     * Quick Start Notes:
     *   - Tests will be autodiscovered through the ____Test.php 
     *     naming convention.
     *   - To disable a test by default, add the full test name to the 
     *     $disabled array above.  Eg: array('Zend_Config_IniTest');
     *   - To require a config to be set for a given test, adds full
     *     test name to the $requires_config array.
     * 
     * @see http://framework.zend.com/wiki/display/ZFDEV/Zend+Framework+Testing+Standards
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite($sub_suite = null)
    {
        return parent::_suite(__CLASS__, __FILE__, $sub_suite);
    }
}
