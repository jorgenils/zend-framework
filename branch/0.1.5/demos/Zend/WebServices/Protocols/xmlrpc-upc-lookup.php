<?php
require_once 'Zend/XmlRpc/Client.php';

$server = new Zend_XmlRpc_Client('http://www.upcdatabase.com/rpc');

print_r( $server->lookupUPC('071641301627') );
