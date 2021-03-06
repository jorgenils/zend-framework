<?php
// Call Zend_View_Helper_UrlTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Zend_View_Helper_UrlTest::main");
}

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/TestHelper.php';
require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'Zend/View.php';
require_once 'Zend/View/Helper/Url.php';

/* Test dependency on Front Controller because there is no way to get the Controller out of View instance dynamically */
require_once 'Zend/Controller/Front.php';

/**
 * Zend_View_Helper_UrlTest 
 *
 * Tests formText helper, including some common functionality of all form helpers
 * 
 * @uses PHPUnit_Framework_TestCase
 * @version $Id: UrlTest.php 4828 2007-05-16 21:28:54Z matthew $
 */
class Zend_View_Helper_UrlTest extends PHPUnit_Framework_TestCase 
{
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("Zend_View_Helper_UrlTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        $this->front = Zend_Controller_Front::getInstance();
        $this->front->getRouter()->addDefaultRoutes();

        $this->view = new Zend_View();
        $this->helper = new Zend_View_Helper_Url();
        $this->helper->setView($this->view);
    }

    public function testDefaultEmpty()
    {
        $url = $this->helper->url();
        $this->assertEquals('/', $url);
    }

    public function testDefault()
    {
        $url = $this->helper->url(array('controller' => 'ctrl', 'action' => 'act'));
        $this->assertEquals('/ctrl/act', $url);
    }
    
    public function testEncode()
    {
        $url = $this->helper->url(array('controller' => 'My Controller'));
        $this->assertEquals('/My+Controller', $url);

        $url = $this->helper->url(array('controller' => 'My Controller'), null, false, false);
        $this->assertEquals('/My Controller', $url);
    }
    
    public function testEncodeWithSingleParamReset() 
    {    
        $router = $this->front->getRouter();
        $router->removeDefaultRoutes();
        $router->removeRoute('default');
        
        $route = new Zend_Controller_Router_Route(':controller/:action/*', array('controller' => 'index', 'action' => 'index'));
        $router->addRoute('ctrl-act', $route);
        
        $req = new Zend_Controller_Request_Http('http://framework.zend.com/news/view/id/3');
        $router->route($req);
        
        $this->assertEquals(3, count($req->getParams()));
        
        $url = $this->helper->url(array('controller' => null), 'ctrl-act');
        $this->assertSame('/index/view/id/3', $url);
        
        $url = $this->helper->url(array('action' => null), 'ctrl-act');
        $this->assertSame('/news/index/id/3', $url);

        $url = $this->helper->url(array('action' => null, 'id' => null), 'ctrl-act');
        $this->assertSame('/news', $url);
    }    
    
}

// Call Zend_View_Helper_UrlTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "Zend_View_Helper_UrlTest::main") {
    Zend_View_Helper_UrlTest::main();
}
