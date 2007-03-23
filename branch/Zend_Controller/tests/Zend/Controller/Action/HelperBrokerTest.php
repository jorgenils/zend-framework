<?php

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'Zend/Controller/Front.php';
require_once 'Zend/Controller/Request/Http.php';
require_once 'Zend/Controller/Response/Cli.php';

require_once 'Zend/Controller/Action/HelperBroker.php';

class Zend_Controller_Action_HelperBrokerTest extends PHPUnit_Framework_TestCase
{
    
    public function setUp()
    {
        Zend_Controller_Action_HelperBroker::resetHelpers();
    }
    
    public function testLoadingAndReturningHelper()
    {
        $controller = Zend_Controller_Front::getInstance();
        $controller->resetInstance();
        $controller->setControllerDirectory(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files');
        $request = new Zend_Controller_Request_Http('http://framework.zend.com/helper-broker/test-get-redirector/');
        $controller->setResponse(new Zend_Controller_Response_Cli());
        
        $controller->returnResponse(true);
        $response = $controller->dispatch($request);
        $this->assertEquals($response->getBody(), 'Zend_Controller_Action_Helper_Redirector');
    }
    
    public function testReturningHelper()
    {
        $controller = Zend_Controller_Front::getInstance();
        $controller->resetInstance();
        $controller->setControllerDirectory(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files');
        $request = new Zend_Controller_Request_Http('http://framework.zend.com/helper-broker/test-get-redirector/');
        $controller->setResponse(new Zend_Controller_Response_Cli());
        
        $controller->returnResponse(true);
        $response = $controller->dispatch($request);
        $this->assertEquals($response->getBody(), 'Zend_Controller_Action_Helper_Redirector');
    }
    
    public function testNonExistentHelper()
    {
        $controller = Zend_Controller_Front::getInstance();
        $controller->resetInstance();
        $controller->setControllerDirectory(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files');
        $request = new Zend_Controller_Request_Http('http://framework.zend.com/helper-broker/test-bad-helper/');
        $controller->setResponse(new Zend_Controller_Response_Cli());
        
        $controller->returnResponse(true);
        $response = $controller->dispatch($request);
        $this->assertEquals($response->getBody(), 'Action Helper by name NonExistentHelper not found.');
    }
    
    public function testCustomHelperRegistered()
    {
        $controller = Zend_Controller_Front::getInstance();
        $controller->resetInstance();
        $controller->setControllerDirectory(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files');
        $request = new Zend_Controller_Request_Http('http://framework.zend.com/helper-broker/test-custom-helper/');
        $controller->setResponse(new Zend_Controller_Response_Cli());
        
        $controller->returnResponse(true);
        
        require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files/Helpers/TestHelper.php';
        Zend_Controller_Action_HelperBroker::addHelper(new MyApp_TestHelper());
        
        $response = $controller->dispatch($request);
        $this->assertEquals($response->getBody(), 'MyApp_TestHelper');
    }
        
    public function testCustomHelperFromPath()
    {
        $controller = Zend_Controller_Front::getInstance();
        $controller->resetInstance();
        $controller->setControllerDirectory(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files');
        $request = new Zend_Controller_Request_Http('http://framework.zend.com/helper-broker/test-custom-helper/');
        $controller->setResponse(new Zend_Controller_Response_Cli());
        
        $controller->returnResponse(true);
        
        Zend_Controller_Action_HelperBroker::addPath(
            dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'Helpers',
            'MyApp'
            );
        
        $response = $controller->dispatch($request);
        $this->assertEquals($response->getBody(), 'MyApp_TestHelper');
    }
    
    
}