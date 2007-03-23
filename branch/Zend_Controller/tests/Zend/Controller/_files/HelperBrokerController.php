<?php

require_once 'Zend/Controller/Action.php';

class HelperBrokerController extends Zend_Controller_Action 
{
    
    
    public function testGetRedirectorAction()
    {
        $redirector = $this->_helper->getHelper('Redirector');
        $this->getResponse()->appendBody(get_class($redirector));
    }
    
    public function testBadHelperAction()
    {
        try {
            $this->_helper->getHelper('NonExistentHelper');
        } catch (Exception $e) {
            $this->getResponse()->appendBody($e->getMessage());
        }
        
    }
    
    public function testCustomHelperAction()
    {
        $this->getResponse()->appendBody(get_class($this->_helper->TestHelper));
    }
    
}