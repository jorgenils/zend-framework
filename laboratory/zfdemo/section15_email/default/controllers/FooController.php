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
 * @version    $Id: FooController.php 121 2007-04-12 21:48:01Z gavin $
 *
 * Demonstrate the use of the declareVars() view helper to declare view variables
 * and locate potential bugs caused by typos and misspellings of view variable names.
 * http://www.your server.com/zfdemo/foo/foo
 */

require_once 'Zend/Controller/Action.php';

class FooController extends ZFDemo_Controller_Action
{
    // the default action is "indexAction", unless explicitly set to something else
    public function fooAction()
    {
        // STAGE 3: Choose, create, and optionally update models using business logic.
        // STAGE 4: Apply business logic to create a presentation model for the view.
        // STAGE 5: Choose view and submit presentation model to view.

        $this->renderToSegment('body');
    }
}
