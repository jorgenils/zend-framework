<?php
/**
 * @package    Zend_Cache
 * @subpackage UnitTests
 */
 
 /**
 * Zend_Cache
 */
require_once 'Zend/Cache.php';
require_once 'Zend/Cache/Frontend/Page.php';
require_once 'Zend/Cache/Backend/Test.php';

/**
 * PHPUnit test case
 */
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * @package    Zend_Cache
 * @subpackage UnitTests
 */
class Zend_Cache_PageFrontendTest extends PHPUnit_Framework_TestCase {
    
    private $_instance;
    
    public function setUp()
    {
        if (!$this->_instance) {
            $this->_instance = new Zend_Cache_Frontend_Page(array());
            $this->_backend = new Zend_Cache_Backend_Test();
            $this->_instance->setBackend($this->_backend);
        }
    }
    
    public function tearDown()
    {
        unset($this->_instance);
    }
    
    public function testConstructorCorrectCall()
    {
        $test = new Zend_Cache_Frontend_Page(array('lifeTime' => 3600, 'caching' => true));      
    }
    
    public function testConstructorUnimplementedOption()
    {
        try {
            $test = new Zend_Cache_Frontend_Page(array('httpConditional' => true));
        } catch (Exception $e) {
            return;
        }
        $this->fail('Zend_Cache_Exception was expected but not thrown');    
    }
    
    public function testConstructorWithBadDefaultOptions()
    {
        try {
            $test = new Zend_Cache_Frontend_Page(array('defaultOptions' => 'foo'));
        } catch (Exception $e) {
            return;
        }
        $this->fail('Zend_Cache_Exception was expected but not thrown');    
    }
    
    public function testConstructorWithBadDefaultOptions2()
    {
        try {
            $test = new Zend_Cache_Frontend_Page(array('defaultOptions' => array('cache' => true, 'foo' => 'bar')));
        } catch (Exception $e) {
            return;
        }
        $this->fail('Zend_Cache_Exception was expected but not thrown');    
    }
    
    public function testConstructorWithBadRegexps()
    {
        try {
            $test = new Zend_Cache_Frontend_Page(array('regexps' => 'foo'));
        } catch (Exception $e) {
            return;
        }
        $this->fail('Zend_Cache_Exception was expected but not thrown');    
    }
    
    public function testConstructorWithBadRegexps2()
    {
        try {
            $test = new Zend_Cache_Frontend_Page(array('regexps' => array('foo', 'bar')));
        } catch (Exception $e) {
            return;
        }
        $this->fail('Zend_Cache_Exception was expected but not thrown');    
    }
    
    public function testConstructorWithBadRegexps3()
    {
        $array = array(
	       '^/$' => array('cache' => true),         
	       '^/index/' => array('cache' => true),    
	       '^/article/' => array('cache' => false), 
	       '^/article/view/' => array(            
	           'foo' => true,                    
	           'cacheWithPostVariables' => true,   
	           'makeIdWithPostVariables' => true,   
	       )
        );
        try {
            $test = new Zend_Cache_Frontend_Page(array('regexps' => $array));
        } catch (Exception $e) {
            return;
        }
        $this->fail('Zend_Cache_Exception was expected but not thrown'); 
    }
    
    public function testConstructorWithGoodRegexps()
    {
        $array = array(
	       '^/$' => array('cache' => true),         
	       '^/index/' => array('cache' => true),    
	       '^/article/' => array('cache' => false), 
	       '^/article/view/' => array(            
	           'cache' => true,                    
	           'cacheWithPostVariables' => true,   
	           'makeIdWithPostVariables' => true,   
	       )
        );
        $test = new Zend_Cache_Frontend_Page(array('regexps' => $array));        
    }
    
    public function testConstructorWithGoodDefaultOptions()
    {
        $test = new Zend_Cache_Frontend_Page(array('defaultOptions' => array('cache' => true)));
    }
    
    public function testStartEndCorrectCall1()
    {
        ob_start();
        ob_implicit_flush(false);
        if (!($this->_instance->start('123', true))) {
            echo('foobar');
            ob_end_flush();
        }
        $data = ob_get_contents();
        ob_end_clean();
        ob_implicit_flush(true);
        $this->assertEquals('foo', $data);
    }
    
    public function testStartEndCorrectCall2()
    {
        ob_start();
        ob_implicit_flush(false);
        if (!($this->_instance->start('false', true))) {
            echo('foobar');
            ob_end_flush();
        }
        $data = ob_get_contents();
        ob_end_clean();
        ob_implicit_flush(true);
        $this->assertEquals('foobar', $data);
    }
    
    public function testStartEndCorrectCallWithDebug()
    {
        $this->_instance->setOption('debugHeader', true);
        ob_start();
        ob_implicit_flush(false);
        if (!($this->_instance->start('123', true))) {
            echo('foobar');
            ob_end_flush();
        }
        $data = ob_get_contents();
        ob_end_clean();
        ob_implicit_flush(true);
        $this->assertEquals('DEBUG HEADER : This is a cached page !foo', $data);
    }   
}
?>
