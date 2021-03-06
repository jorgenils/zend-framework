<?php

class Zend_Tool_Rpc_System_Manifest implements Zend_Tool_Rpc_Manifest_Interface
{
    
    public function getProviders()
    {
        $providers = array(
            new Zend_Tool_Rpc_System_Provider_Version(),
            new Zend_Tool_Rpc_System_Provider_Providers()
            );
            
        return $providers;
    }
    
    public function getActions()
    {
        $actions = array(
            new Zend_Tool_Rpc_System_Action_Create(),
            new Zend_Tool_Rpc_System_Action_Delete()
            );
            
        return $actions;
    }
    
    
}