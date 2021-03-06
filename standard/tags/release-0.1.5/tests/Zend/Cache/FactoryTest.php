<?php
/**
 * @package    Zend_Cache
 * @subpackage UnitTests
 */

/**
 * Zend_Cache
 */
require_once 'Zend/Cache.php';

/**
 * PHPUnit2 test case
 */
require_once 'PHPUnit2/Framework/TestCase.php';

/**
 * @package    Zend_Cache
 * @subpackage UnitTests
 */
class Zend_Cache_FactoryTest extends PHPUnit2_Framework_TestCase
{

    public function setUp()
    {
    }
    
    public function tearDown()
    {
    }
    
    public function testAvailableFrontends()
    {
        $this->assertType('array', Zend_Cache::$availableFrontends);
    }
    
    public function testAvailableBackends()
    {
        $this->assertType('array', Zend_Cache::$availableBackends);
    }
    
    public function testFactoryCorrectCall()
    {
        $generated_frontend = Zend_Cache::factory('Core', 'File');
        $this->assertEquals('Zend_Cache_Core', get_class($generated_frontend));
    }
    
    public function testBadFrontend()
    {
        try {
            Zend_Cache::factory('badFrontend', 'File');
        } catch (Zend_Cache_Exception $e) {
            return;
        }
        $this->fail('Zend_Cache_Exception was expected but not thrown');
    }
    
    public function testBadBackend()
    {
        try {
            Zend_Cache::factory('Output', 'badBackend');
        } catch (Zend_Cache_Exception $e) {
            return;
        }
        $this->fail('Zend_Cache_Exception was expected but not thrown');    
    }

}
