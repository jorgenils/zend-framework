<?php
/**
 * Zend_XmlRpc_Response
 */
require_once 'Zend/XmlRpc/Response.php';

/**
 * Client version of Zend_XmlRpc_Response
 *
 * Accepts the XML response in the constructor in order to load the object.
 * 
 * @uses Zend_XmlRpc_Response
 * @package Zend_XmlRpc
 * @version $Id$
 */
class Zend_XmlRpc_Response_Client extends Zend_XmlRpc_Response
{
    public function __construct($response)
    {
        $this->loadXml($response);
    }
}
