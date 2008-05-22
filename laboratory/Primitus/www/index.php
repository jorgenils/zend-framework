<?php
/**
 * Primitus
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 *
 * @category   Zend
 * @package    Application
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Primitus/Controller/Front.php';
require_once 'Primitus.php';

/**
 * Enabling Primitus_DEBUG will automatically record and display internal Primitus
 * routing and dispatcher data on the bottom of every page
 */
define("PRIMITUS_DEBUG", false);

$frontController = Primitus::initializeRequest();
$frontController->dispatch();

?>
