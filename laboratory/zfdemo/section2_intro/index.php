<?php
/**
 * Zend Framework ZFDemo Tutorial
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @copyright  Copyright (c) 2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: index.php 122 2007-04-13 14:27:45Z gavin $
 */

bootstrap(); // i.e. run the main program!

function bootstrap()
{
    require 'Zend/Version.php';
    if (Zend_Version::compareVersion('0.9.0') > 0) {
        echo "Please upgrade to a newer version of ZF for this demo.\n";
    }

    // STAGE 1. Prepare the front (primary) controller.
    require 'Zend/Controller/Front.php';
    $frontController = Zend_Controller_Front::getInstance(); // manages the overall workflow
    $cwd = realpath(dirname(__FILE__));
    $frontController->setControllerDirectory($cwd);
    
    // Initialize views
    require 'Zend/View.php';
    // create a view object to hold our presentation model (i.e. data for template)
    $view = new Zend_View();
    // defaults to same location as controller (bug: default not working)
    $view->setScriptPath($cwd);
    $frontController->setParam('view', $view);

    // STAGE 2. Find the right action and execute it.
    $frontController->returnResponse(true); // return the response (do not echo it)

    // Dispatch calculated actions of the selected controllers
    $response = $frontController->dispatch(); // similar to "running" the configured MVC "program"

    // STAGES 3 to 5 occur in IndexController.php
    //   STAGE 3: Choose, create, and optionally update models using business logic.
    //   STAGE 4: Apply business logic to create a presentation model for the view.
    //   STAGE 5: Choose view template and submit presentation model to view template for rendering.

    // STAGE 6 occurs in indexIndex.phtml
    //   STAGE 6: Render results in response to request.

    // STAGE 7. Render results in response to request.
    $response->renderExceptions(true); // show any excpetions in the visible output (i.e. debug mode)
    $response->sendResponse(); // send final results to browser, including headers
}
