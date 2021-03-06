<?php
// Call Zend_View_Helper_ActionTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Zend_View_Helper_ActionTest::main");
}

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/TestHelper.php';
require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

/** Zend_View_Helper_Action */
require_once 'Zend/View/Helper/Action.php';

/** Zend_Controller_Front */
require_once 'Zend/Controller/Front.php';

/** Zend_Controller_Request_Http */
require_once 'Zend/Controller/Request/Http.php';

/** Zend_Controller_Response_Http */
require_once 'Zend/Controller/Response/Http.php';

/** Zend_View */
require_once 'Zend/View.php';

/**
 * Test class for Zend_View_Helper_Action.
 */
class Zend_View_Helper_ActionTest extends PHPUnit_Framework_TestCase 
{
    /**
     * Runs the test methods of this class.
     *
     * @return void
     */
    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("Zend_View_Helper_ActionTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->resetInstance();

        $this->request  = new Zend_Controller_Request_Http('http://framework.zend.com/foo');
        $this->response = new Zend_Controller_Response_Http();
        $this->response->headersSentThrowsException = false;
        $front->setRequest($this->request)
              ->setResponse($this->response)
              ->addModuleDirectory(dirname(__FILE__) . '/_files/modules');

        $this->view   = new Zend_View();
        $this->helper = new Zend_View_Helper_Action();
        $this->helper->setView($this->view);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->request, $this->response, $this->helper);
    }

    /**
     * @return void
     */
    public function testInitialStateHasClonedObjects()
    {
        $this->assertNotSame($this->request, $this->helper->request);
        $this->assertNotSame($this->response, $this->helper->response);

        $dispatcher = Zend_Controller_Front::getInstance()->getDispatcher();
        $this->assertNotSame($dispatcher, $this->helper->dispatcher);
    }

    /**
     * @return void
     */
    public function testInitialStateHasDefaultModuleName()
    {
        $dispatcher = Zend_Controller_Front::getInstance()->getDispatcher();
        $module     = $dispatcher->getDefaultModule();
        $this->assertEquals($module, $this->helper->defaultModule);

        $dispatcher->setDefaultModule('foo');
        $helper = new Zend_View_Helper_Action();
        $this->assertEquals('foo', $helper->defaultModule);
    }

    /**
     * @return void
     */
    public function testResetObjectsClearsRequestVars()
    {
        $this->helper->request->setParam('foo', 'bar');
        $this->helper->resetObjects();
        $this->assertNull($this->helper->request->getParam('foo'));
    }

    /**
     * @return void
     */
    public function testResetObjectsClearsResponseBody()
    {
        $this->helper->response->setBody('foobarbaz');
        $this->helper->resetObjects();
        $body = $this->helper->response->getBody();
        $this->assertTrue(empty($body));
    }

    /**
     * @return void
     */
    public function testResetObjectsClearsResponseHeaders()
    {
        $this->helper->response->setHeader('X-Foo', 'Bar')
                               ->setRawHeader('HTTP/1.1');
        $this->helper->resetObjects();
        $headers    = $this->helper->response->getHeaders();
        $rawHeaders = $this->helper->response->getRawHeaders();
        $this->assertTrue(empty($headers));
        $this->assertTrue(empty($rawHeaders));
    }

    /**
     * @return void
     */
    public function testActionReturnsContentFromDefaultModule()
    {
        $value = $this->helper->action('bar', 'foo');
        $this->assertContains('In default module, FooController::barAction()', $value);
    }

    /**
     * @return void
     */
    public function testActionReturnsContentFromSpecifiedModule()
    {
        $value = $this->helper->action('bar', 'foo', 'foo');
        $this->assertContains('In foo module, Foo_FooController::barAction()', $value);
    }

    /**
     * @return void
     */
    public function testActionReturnsContentReflectingPassedParams()
    {
        $value = $this->helper->action('baz', 'foo', null, array('bat' => 'This is my message'));
        $this->assertNotContains('BOGUS', $value, var_export($this->helper->request->getUserParams(), 1));
        $this->assertContains('This is my message', $value);
    }

    /**
     * @return void
     */
    public function testActionReturnsEmptyStringWhenForwardDetected()
    {
        $value = $this->helper->action('forward', 'foo');
        $this->assertEquals('', $value);
    }

    /**
     * @return void
     */
    public function testActionReturnsEmptyStringWhenRedirectDetected()
    {
        $value = $this->helper->action('redirect', 'foo');
        $this->assertEquals('', $value);
    }

    /**
     * @return void
     */
    public function testConstructorThrowsExceptionWithNoControllerDirsInFrontController()
    {
        Zend_Controller_Front::getInstance()->resetInstance();
        try {
            $helper = new Zend_View_Helper_Action();
            $this->fail('Empty front controller should cause action helper to throw exception');
        } catch (Exception $e) {
        }
    }

    /**
     * @return void
     */
    public function testConstructorThrowsExceptionWithNoRequestInFrontController()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->resetInstance();

        $response = new Zend_Controller_Response_Http();
        $response->headersSentThrowsException = false;
        $front->setResponse($response)
              ->addModuleDirectory(dirname(__FILE__) . '/_files/modules');
        try {
            $helper = new Zend_View_Helper_Action();
            $this->fail('No request in front controller should cause action helper to throw exception');
        } catch (Exception $e) {
        }
    }

    /**
     * @return void
     */
    public function testConstructorThrowsExceptionWithNoResponseInFrontController()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->resetInstance();

        $request = new Zend_Controller_Request_Http('http://framework.zend.com/foo');
        $front->setRequest($this->request)
              ->addModuleDirectory(dirname(__FILE__) . '/_files/modules');
        try {
            $helper = new Zend_View_Helper_Action();
            $this->fail('No response in front controller should cause action helper to throw exception');
        } catch (Exception $e) {
        }
    }

    public function testViewObjectRemainsUnchangedAfterAction()
    {
        $value = $this->helper->action('bar', 'foo', 'foo');
        $this->assertContains('In foo module, Foo_FooController::barAction()', $value);
        $this->assertNull($this->view->bar);
    }
}

// Call Zend_View_Helper_ActionTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "Zend_View_Helper_ActionTest::main") {
    Zend_View_Helper_ActionTest::main();
}
