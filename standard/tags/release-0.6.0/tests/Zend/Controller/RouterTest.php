<?php
require_once 'Zend/Controller/Router.php';
require_once 'PHPUnit/Framework/TestCase.php';

require_once 'Zend/Controller/Request/Http.php';

class Zend_Controller_RouterTest extends PHPUnit_Framework_TestCase 
{
    /**
     * testRoute 
     */
    public function testRoute()
    {
        $request = new Zend_Controller_RouterTest_Request();
        $router = new Zend_Controller_Router();
        $route = $router->route($request);

        $this->assertEquals('foo', $route->getControllerName(), $request->getPathInfo());
        $this->assertEquals('bar', $route->getActionName(), $request->getPathInfo());
        $params = $route->getParams();
        $this->assertTrue(isset($params['baz']), $request->getPathInfo());
        $this->assertEquals(2, $params['baz'], $request->getPathInfo());
    }

    public function testDefaults()
    {
        $request = new Zend_Controller_RouterTest_Request('http://framework.zend.com/');
        $request->setControllerName('bar')
                ->setActionName('baz');

        $router = new Zend_Controller_Router();
        $route = $router->route($request);

        $this->assertEquals('bar', $route->getControllerName(), $request->getPathInfo());
        $this->assertEquals('baz', $route->getActionName(), $request->getPathInfo());
    }

    public function testWithModules()
    {
        $request = new Zend_Controller_RouterTest_Request('http://framework.zend.com/module/controller/action/var/value');
        $router = new Zend_Controller_Router();
        $router->setParam('useModules', true);
        $route = $router->route($request);

        $this->assertEquals('module', $request->getParam('module'));
        $this->assertEquals('controller', $request->getControllerName());
        $this->assertEquals('action', $request->getActionName());
        $this->assertEquals('value', $request->getParam('var'));
    }
}

/**
 * Zend_Controller_RouterTest_Request - request object for router testing
 * 
 * @uses Zend_Controller_Request_Interface
 */
class Zend_Controller_RouterTest_Request extends Zend_Controller_Request_Http
{
    public function __construct($uri = null)
    {
        if (null === $uri) {
            $uri = 'http://framework.zend.com/foo/bar/baz/2';
        }

        parent::__construct($uri);
    }
}


