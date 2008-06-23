<?php
// Call Zend_Controller_Action_Helper_RedirectorTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Zend_Controller_Action_Helper_MultiPageFormTest::main");
}

require_once dirname(__FILE__) . '/../../../../TestHelper.php';

/**
 * @see Zend_Controller_Action_Helper_MultiPageForm
 */
require_once 'Zend/Controller/Action/Helper/MultiPageForm.php';

class Zend_Controller_Action_Helper_MultiPageFormTest extends PHPUnit_Framework_TestCase
{
    /**
     * The helper
     *
     * @var Zend_Controller_Action_Helper_MultiPageForm
     */
    private $_multiPageFormHelper;
    
    /**
     * @var Zend_Controller_Request_Http
     */
    private $_request;

    /**
     * @var Zend_Controller_Response_Http
     */
    private $_response;

    /**
     * @var Zend_Controller_Action
     */
    private $_controller;
    
    /**
     * @var array
     */
    private $_server;
    
    public static function main()
    {
        require_once 'PHPUnit/TextUI/TestRunner.php';

        $suite  = new PHPUnit_Framework_TestSuite('Zend_Controller_Action_Helper_MultiPageFormTest');
        PHPUnit_TextUI_TestRunner::run($suite);
    }
    
    public function setUp()
    {
        $this->_multiPageFormHelper = new Zend_Controller_Action_Helper_MultiPageForm();
    }
    
    public function testGetSetProcessAction()
    {
        $this->assertEquals('process', $this->_multiPageFormHelper->getProcessAction());
        $this->_multiPageFormHelper->setProcessAction('finish');
        $this->assertEquals('finish', $this->_multiPageFormHelper->getProcessAction());
        $this->_multiPageFormHelper->setProcessAction('process');
    }
    
    public function testGetNavigationAction()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }
    
    public function testSetNavigationAction()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }
    
    public function testIsValidAction()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }
    
    public function testIsCompleteForm()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }
    
    public function testGetSetCancelAction()
    {
        $this->assertNull($this->_multiPageFormHelper->getCancelAction());
        $this->_multiPageFormHelper->setCancelAction('cancel');
        $this->assertEquals('cancel', $this->_multiPageFormHelper->getCancelAction());
        $this->_multiPageFormHelper->setCancelAction(null);
        $this->assertNull($this->_multiPageFormHelper->getCancelAction());
    }
    
    public function testSetFormActionMapping()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }
    
    public function testSetValues()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }
    
    public function testGetSessionValues()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }
    
    public function testGetLastValidAction()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }
    
    public function testGetSetForm()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }
    
    public function testGetCurrentSubForm()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }
    
    public function testGetSubForm()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }
    
    public function testIsValid()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }
    
    public function testGetFormSessionData()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }
    
    public function testGetPostData()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }
    
    public function testRedirect()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }
    
    public function testGetSubmitAction()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }
    
    public function testHandle()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }

    public function testIsSubFormAction()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }
    
    public function testIsSubForm()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }

    public function testClear()
    {
        $this->markTestIncomplete('Test method "' . __METHOD__ . '" is not yet implemented.');
    }
}

// Call Zend_Controller_Action_Helper_FlashMessengerTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "Zend_Controller_Action_Helper_MultiPageFormTest::main") {
    Zend_Controller_Action_Helper_MultiPageFormTest::main();
}