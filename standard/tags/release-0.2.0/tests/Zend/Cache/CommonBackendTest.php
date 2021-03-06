<?php
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__);

/**
 * @package    Zend_Cache
 * @subpackage UnitTests
 */
 
/**
 * PHPUnit test case
 */
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * @package    Zend_Cache
 * @subpackage UnitTests
 */
class Zend_Cache_CommonBackendTest extends PHPUnit_Framework_TestCase {
    
    protected $_instance;
    protected $_className;
    
    public function __construct($className)
    {
        $this->_className = $className;
    }
    
    public function setUp()
    {
        $this->_instance->setDirectives(array('logging' => true));
        $this->_instance->save('bar : data to cache', 'bar', array('tag3', 'tag4'));
        $this->_instance->save('bar2 : data to cache', 'bar2', array('tag3', 'tag1')); 
        $this->_instance->save('bar3 : data to cache', 'bar3', array('tag2', 'tag3'));   
    }
    
    public function tearDown()
    {
        $this->_instance->clean();
    }
    
    protected function _getTmpDirWindows()
    {
        if (isset($_ENV['TEMP'])) {
            return $_ENV['TEMP'];
        }
        if (isset($_ENV['TMP'])) {
            return $_ENV['TMP'];
        }
        if (isset($_ENV['windir'])) {
            return $_ENV['windir'] . '\\temp';
        }
        if (isset($_ENV['SystemRoot'])) {
            return $_ENV['SystemRoot'] . '\\temp';
        }
        if (isset($_SERVER['TEMP'])) {
            return $_SERVER['TEMP'];
        }
        if (isset($_SERVER['TMP'])) {
            return $_SERVER['TMP'];
        }
        if (isset($_SERVER['windir'])) {
            return $_SERVER['windir'] . '\\temp';
        }
        if (isset($_SERVER['SystemRoot'])) {
            return $_SERVER['SystemRoot'] . '\\temp';
        }
        return '\temp';
    }
    
    protected function _getTmpDirUnix()
    {
        if (isset($_ENV['TMPDIR'])) {
	        return $_ENV['TMPDIR'];
	    }
	    if (isset($_SERVER['TMPDIR'])) {
	        return $_SERVER['TMPDIR'];
	    }
	    return '/tmp';
    }
       
    public function testConstructorCorrectCall()
    {
        $this->fail('PLEASE IMPLEMENT A testConstructorCorrectCall !!!'); 
    } 
    
    public function testConstructorBadArgument()
    {
        try {
            $class = $this->_className;
            $test = new $class('foo');
        } catch (Zend_Cache_Exception $e) {
            return;
        }
        $this->fail('Zend_Cache_Exception was expected but not thrown');    
    }
    
    public function testConstructorBadOption()
    {
        try {
            $class = $this->_className;
            $test = new $class(array('foo' => 'bar'));
        } catch (Zend_Cache_Exception $e) {
            return;
        }
        $this->fail('Zend_Cache_Exception was expected but not thrown'); 
    }
    
    public function testSetDirectivesCorrectCall()
    {
        $this->_instance->setDirectives(array('lifeTime' => 3600, 'logging' => true));
    }
    
    public function testSetDirectivesBadArgument()
    {
        try {
            $this->_instance->setDirectives('foo');
        } catch (Zend_Cache_Exception $e) {
            return;
        }
        $this->fail('Zend_Cache_Exception was expected but not thrown'); 
    }
    
    public function testSetDirectivesBadDirective()
    {
        // A bad directive (not known by a specific backend) is possible
        // => so no exception here
        $this->_instance->setDirectives(array('foo' => true, 'lifeTime' => 3600));
    }
    
    public function testSetDirectivesBadDirective2()
    {
        try {
            $this->_instance->setDirectives(array('foo' => true, 12 => 3600));
        } catch (Zend_Cache_Exception $e) {
            return;
        }
        $this->fail('Zend_Cache_Exception was expected but not thrown'); 
    }
    
    public function testSaveCorrectCall()
    {
        $res = $this->_instance->save('data to cache', 'foo', array('tag1', 'tag2'));
        $this->assertTrue($res);
    }
    
    public function testSaveWithNullLifeTime()
    {
        $this->_instance->setDirectives(array('lifeTime' => null));
        $res = $this->_instance->save('data to cache', 'foo', array('tag1', 'tag2'));
        $this->assertTrue($res);
    }
    
    public function testRemoveCorrectCall()
    {   
        $this->assertTrue($this->_instance->remove('bar'));
        $this->assertFalse($this->_instance->test('bar')); 
        $this->assertFalse($this->_instance->remove('barbar'));
        $this->assertFalse($this->_instance->test('barbar'));        
    }
    
    public function testTestWithAnExistingCacheId()
    {
        $res = $this->_instance->test('bar');
        if (!$res) {
            $this->fail('test() return false');
        }
        if (!($res > 999999)) {
            $this->fail('test() return an incorrect integer');
        }
        return;    
    }
    
    public function testTestWithANonExistingCacheId() 
    {    
        $this->assertFalse($this->_instance->test('barbar'));       
    }
         
    public function testTestWithAnExistingCacheIdAndANullLifeTime()
    {
        $this->_instance->setDirectives(array('lifeTime' => null));
        $res = $this->_instance->test('bar');
        if (!$res) {
            $this->fail('test() return false');
        }
        if (!($res > 999999)) {
            $this->fail('test() return an incorrect integer');
        }
        return;
    }
    
    public function testGetWithANonExistingCacheId()
    {
        $this->assertFalse($this->_instance->get('barbar'));        
    }
    
    public function testGetWithAnExistingCacheId()
    {
        $this->assertEquals('bar : data to cache', $this->_instance->get('bar'));
    }
    
    public function testGetWithAnExistingCacheIdAndUTFCharacters()
    {
        $data = '"""""' . "'" . '\n' . 'ééééé';
        $this->_instance->save($data, 'foo');
        $this->assertEquals($data, $this->_instance->get('foo'));
    }
    
    public function testGetWithAnExpiredCacheId()
    {
        $this->_instance->___expire('bar');
        $this->_instance->setDirectives(array('lifeTime' => -1));
        $this->assertFalse($this->_instance->get('bar'));
        $this->assertEquals('bar : data to cache', $this->_instance->get('bar', true));
    }   

    public function testCleanModeAll()
    {
        $this->assertTrue($this->_instance->clean('all'));
        $this->assertFalse($this->_instance->test('bar'));
        $this->assertFalse($this->_instance->test('bar2'));
    }
    
    public function testCleanModeOld()
    {
        $this->_instance->___expire('bar2');
        $this->assertTrue($this->_instance->clean('old'));
        $this->assertTrue($this->_instance->test('bar') > 999999);
        $this->assertFalse($this->_instance->test('bar2'));
    }
    
    public function testCleanModeMatchingTags()
    {
        $this->assertTrue($this->_instance->clean('matchingTag', array('tag3')));
        $this->assertFalse($this->_instance->test('bar'));
        $this->assertFalse($this->_instance->test('bar2'));
    }
    
    public function testCleanModeMatchingTags2()
    {
        $this->assertTrue($this->_instance->clean('matchingTag', array('tag3', 'tag4')));
        $this->assertFalse($this->_instance->test('bar'));
        $this->assertTrue($this->_instance->test('bar2') > 999999);
    }
    
    public function testCleanModeNotMatchingTags()
    {
        $this->assertTrue($this->_instance->clean('notMatchingTag', array('tag3')));
        $this->assertTrue($this->_instance->test('bar') > 999999);
        $this->assertTrue($this->_instance->test('bar2') > 999999);
    }
    
    public function testCleanModeNotMatchingTags2()
    {
        $this->assertTrue($this->_instance->clean('notMatchingTag', array('tag4')));
        $this->assertTrue($this->_instance->test('bar') > 999999);
        $this->assertFalse($this->_instance->test('bar2'));
    }
    
    public function testCleanModeNotMatchingTags3()
    {
        $this->assertTrue($this->_instance->clean('notMatchingTag', array('tag4', 'tag1')));
        $this->assertTrue($this->_instance->test('bar') > 999999);
        $this->assertTrue($this->_instance->test('bar2') > 999999);
        $this->assertFalse($this->_instance->test('bar3'));      
    }
    
}

?>
