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
 * Common Abstract tests for backends
 */
require_once '_abstracts/CommonBackendTest.php';


/**
 * PHPUnit test case
 */
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * @package    Zend_Cache
 * @subpackage UnitTests
 */
class Zend_Cache_APCBackendTest extends Zend_Cache_Abstract_CommonBackendTest
{
    
    protected $_instance;
 
    public function __construct()
    {
        parent::__construct('Zend_Cache_Backend_APC');
    }
       
    public function setUp()
    {
        if (!extension_loaded('apc')) {
            $this->markTestSkipped('APC extension is needed to run this test.');
        } else {
            $this->_instance = new Zend_Cache_Backend_APC(array());
            parent::setUp();
        }
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
