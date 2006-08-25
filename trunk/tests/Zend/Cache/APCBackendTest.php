<?php
/**
 * @package    Zend_Cache
 * @subpackage UnitTests
 */
 
 /**
 * Zend_Cache
 */
require_once 'Zend/Cache.php';
require_once 'Zend/Cache/Backend/APC.php';

/**
 * Common tests for backends
 */
require_once 'CommonBackendTest.php';

/**
 * PHPUnit2 test case
 */
require_once 'PHPUnit2/Framework/TestCase.php';

/**
 * @package    Zend_Cache
 * @subpackage UnitTests
 */
class Zend_Cache_APCBackendTest extends Zend_Cache_CommonBackendTest {
    
    protected $_instance;
 
    public function __construct()
    {
        parent::__construct('Zend_Cache_Backend_APC');
    }
       
    public function setUp()
    {        
        $this->_instance = new Zend_Cache_Backend_APC(array());
        parent::setUp();          
    }
    
    public function tearDown()
    {
        parent::tearDown();
        unset($this->_instance);
    }
    
    public function testConstructorCorrectCall()
    {
        $test = new Zend_Cache_Backend_APC();    
    }
       
    public function testCleanModeOld() {
        $this->_instance->clean('old');
        // do nothing, just to see if an error occured
    }
    
    public function testCleanModeMatchingTags() {
        $this->_instance->clean('matchingTag', array('tag1'));
        // do nothing, just to see if an error occured
    }
    
    public function testCleanModeNotMatchingTags() {
        $this->_instance->clean('notMatchingTag', array('tag1'));
        // do nothing, just to see if an error occured
    }
    
    // Because of limitations of this backend...
    public function testGetWithAnExpiredCacheId() {}
    public function testCleanModeMatchingTags2() {}
    public function testCleanModeNotMatchingTags2() {}
    public function testCleanModeNotMatchingTags3() {}
    
}

?>
