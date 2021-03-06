<?php
require_once 'Zend/Controller/Action.php';
require_once 'PHPUnit/Framework/TestCase.php';

require_once 'Zend/Controller/Request/Http.php';
require_once 'Zend/Controller/Response/Cli.php';

class Zend_Controller_ActionTest extends PHPUnit_Framework_TestCase 
{
    public function setUp()
    {
        $this->_controller = new Zend_Controller_ActionTest_TestController(
            new Zend_Controller_Request_Http(),
            new Zend_Controller_Response_Cli(),
            array(
                'foo' => 'bar',
                'bar' => 'baz'
            )
        );
        $this->_controller->setRedirectExit(false);
        Zend_Controller_Front::getInstance()->resetInstance();
    }

    public function tearDown()
    {
        unset($this->_controller);
    }

    public function testInit()
    {
        $this->assertEquals('bar', $this->_controller->initArgs['foo']);
        $this->assertEquals('baz', $this->_controller->initArgs['bar']);
    }

    public function testPreRun()
    {
        $this->_controller->preDispatch();
        $this->assertNotContains('Prerun ran', $this->_controller->getResponse()->getBody());

        $this->_controller->getRequest()->setParam('prerun', true);
        $this->_controller->preDispatch();
        $this->assertContains('Prerun ran', $this->_controller->getResponse()->getBody());
    }

    public function testPostRun()
    {
        $this->_controller->postDispatch();
        $this->assertNotContains('Postrun ran', $this->_controller->getResponse()->getBody());

        $this->_controller->getRequest()->setParam('postrun', true);
        $this->_controller->postDispatch();
        $this->assertContains('Postrun ran', $this->_controller->getResponse()->getBody());
    }

    public function testGetRequest()
    {
        $this->assertTrue($this->_controller->getRequest() instanceof Zend_Controller_Request_Abstract);
    }

    public function testGetResponse()
    {
        $this->assertTrue($this->_controller->getResponse() instanceof Zend_Controller_Response_Abstract);
    }

    public function testGetInvokeArgs()
    {
        $expected = array('foo' => 'bar', 'bar' => 'baz');
        $this->assertSame($expected, $this->_controller->getInvokeArgs());
    }

    public function testGetInvokeArg()
    {
        $this->assertSame('bar', $this->_controller->getInvokeArg('foo'));
        $this->assertSame('baz', $this->_controller->getInvokeArg('bar'));
    }

    public function testForwardActionOnly()
    {
        $this->_controller->forward('forwarded');
        $this->assertEquals('forwarded', $this->_controller->getRequest()->getActionName());
        $this->assertFalse($this->_controller->getRequest()->isDispatched());
    }

    public function testForwardActionKeepsController()
    {
        $request = $this->_controller->getRequest();
        $request->setControllerName('foo')
                ->setActionName('bar');
        $this->_controller->forward('forwarded');
        $this->assertEquals('forwarded', $request->getActionName());
        $this->assertEquals('foo', $request->getControllerName());
        $this->assertFalse($request->isDispatched());
    }

    public function testForwardActionAndController()
    {
        $request = $this->_controller->getRequest();
        $request->setControllerName('foo')
                ->setActionName('bar');
        $this->_controller->forward('forwarded', 'bar');
        $this->assertEquals('forwarded', $request->getActionName());
        $this->assertEquals('bar', $request->getControllerName());
        $this->assertFalse($request->isDispatched());
    }

    public function testForwardActionControllerAndModule()
    {
        $request = $this->_controller->getRequest();
        $request->setControllerName('foo')
                ->setActionName('bar')
                ->setModuleName('admin');
        $this->_controller->forward('forwarded', 'bar');
        $this->assertEquals('forwarded', $request->getActionName());
        $this->assertEquals('bar', $request->getControllerName());
        $this->assertEquals('admin', $request->getModuleName());
        $this->assertFalse($request->isDispatched());
    }

    public function testForwardCanSetParams()
    {
        $request = $this->_controller->getRequest();
        $request->setParams(array('admin' => 'batman'));
        $this->_controller->forward('forwarded', null, null, array('foo' => 'bar'));
        $this->assertEquals('forwarded', $request->getActionName());
        $received = $request->getParams();
        $this->assertTrue(isset($received['foo']));
        $this->assertEquals('bar', $received['foo']);
        $this->assertFalse($request->isDispatched());
    }

    public function testRun()
    {
        $response = $this->_controller->run();
        $body     = $response->getBody();
        $this->assertContains('In the index action', $body, var_export($this->_controller->getRequest(), 1));
        $this->assertNotContains('Prerun ran', $body, $body);
    }

    public function testRun2()
    {
        $this->_controller->getRequest()->setActionName('bar');
        try {
            $response = $this->_controller->run();
            $this->fail('Should not be able to call bar as action');
        } catch (Exception $e) {
            //success!
        } 
    }

    public function testRun3()
    {
        $this->_controller->getRequest()->setActionName('foo');
        $response = $this->_controller->run();
        $this->assertContains('In the foo action', $response->getBody());
        $this->assertNotContains('Prerun ran', $this->_controller->getResponse()->getBody());
    }

    public function testHasParam()
    {
        $request = $this->_controller->getRequest();
        $request->setParam('foo', 'bar');
        $request->setParam('baz', 'bal');

        $this->assertTrue($this->_controller->hasParam('foo'));
        $this->assertTrue($this->_controller->hasParam('baz'));
    }

    public function testSetParam()
    {
        $this->_controller->setParam('foo', 'bar');
        $params = $this->_controller->getParams();
        $this->assertTrue(isset($params['foo']));
        $this->assertEquals('bar', $params['foo']);
    }

    public function testGetParams()
    {
        $this->_controller->setParam('foo', 'bar');
        $this->_controller->setParam('bar', 'baz');
        $this->_controller->setParam('boo', 'bah');

        $params = $this->_controller->getParams();
        $this->assertEquals('bar', $params['foo']);
        $this->assertEquals('baz', $params['bar']);
        $this->assertEquals('bah', $params['boo']);
    }

    public function testRedirect()
    {
        $response = $this->_controller->getResponse();
        $response->headersSentThrowsException = false;
        $this->_controller->redirect('/baz/foo');
        $this->_controller->redirect('/foo/bar');
        $headers = $response->getHeaders();
        $found   = 0;
        $url     = '';
        foreach ($headers as $header) {
            if ('Location' == $header['name']) {
                ++$found;
                $url = $header['value'];
                break;
            }
        }
        $this->assertEquals(1, $found);
        $this->assertContains('/foo/bar', $url);
    }

    public function testInitView()
    {
        Zend_Controller_Front::getInstance()->setControllerDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files');
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'ViewController.php';
        $controller = new ViewController(
            new Zend_Controller_Request_Http(),
            new Zend_Controller_Response_Cli()
        );
        $view = $controller->initView();
        $this->assertTrue($view instanceof Zend_View);
        $scriptPath = $view->getScriptPaths();
        $this->assertTrue(is_array($scriptPath));
        $this->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR, $scriptPath[0]);
    }

    public function testRender()
    {
        $request = new Zend_Controller_Request_Http();
        $request->setControllerName('view')
                ->setActionName('index');
        $response = new Zend_Controller_Response_Cli();
        Zend_Controller_Front::getInstance()->setControllerDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files');
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'ViewController.php';
        $controller = new ViewController($request, $response);

        $controller->indexAction();
        $this->assertContains('In the index action view', $response->getBody());
    }

    public function testRenderByName()
    {
        $request = new Zend_Controller_Request_Http();
        $request->setControllerName('view')
                ->setActionName('test');
        $response = new Zend_Controller_Response_Cli();
        Zend_Controller_Front::getInstance()->setControllerDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files');
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'ViewController.php';
        $controller = new ViewController($request, $response);

        $controller->testAction();
        $this->assertContains('In the index action view', $response->getBody());
    }

    public function testRenderOutsideControllerSubdir()
    {
        $request = new Zend_Controller_Request_Http();
        $request->setControllerName('view')
                ->setActionName('site');
        $response = new Zend_Controller_Response_Cli();
        Zend_Controller_Front::getInstance()->setControllerDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files');
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'ViewController.php';
        $controller = new ViewController($request, $response);

        $controller->siteAction();
        $this->assertContains('In the sitewide view', $response->getBody());
    }

    public function testRenderNamedSegment()
    {
        $request = new Zend_Controller_Request_Http();
        $request->setControllerName('view')
                ->setActionName('name');
        $response = new Zend_Controller_Response_Cli();
        Zend_Controller_Front::getInstance()->setControllerDirectory(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files');
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'ViewController.php';
        $controller = new ViewController($request, $response);

        $controller->nameAction();
        $this->assertContains('In the name view', $response->getBody('name'));
    }
}

class Zend_Controller_ActionTest_TestController extends Zend_Controller_Action
{
    public $initArgs = array();

    public function init()
    {
        $this->initArgs['foo'] = $this->getInvokeArg('foo');
        $this->initArgs['bar'] = $this->getInvokeArg('bar');
    }

    public function preDispatch()
    {
        if (false !== ($param = $this->_getParam('prerun', false))) {
            $this->getResponse()->appendBody("Prerun ran\n");
        }
    }

    public function postDispatch()
    {
        if (false !== ($param = $this->_getParam('postrun', false))) {
            $this->getResponse()->appendBody("Postrun ran\n");
        }
    }

    public function noRouteAction()
    {
        return $this->indexAction();
    }

    public function indexAction()
    {
        $this->getResponse()->appendBody("In the index action\n");
    }

    public function fooAction()
    {
        $this->getResponse()->appendBody("In the foo action\n");
    }

    public function bar()
    {
        $this->getResponse()->setBody("Should never see this\n");
    }

    public function forward($action, $controller = null, $module = null, array $params = null)
    {
        $this->_forward($action, $controller, $module, $params);
    }

    public function hasParam($param)
    {
        return $this->_hasParam($param);
    }

    public function getParams()
    {
        return $this->_getAllParams();
    }

    public function setParam($key, $value)
    {
        $this->_setParam($key, $value);
        return $this;
    }

    public function redirect($url, $code = 302, $prependBase = true)
    {
        $this->_redirect($url, array('code' => $code, 'prependBase' => $prependBase));
    }
}
