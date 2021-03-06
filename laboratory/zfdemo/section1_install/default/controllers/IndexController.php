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
 * @version    $Id: IndexController.php 96 2007-03-26 20:28:20Z gavin $
 */

require_once 'Zend/Controller/Action.php';

class IndexController extends Zend_Controller_Action
{
    // the default action is "indexAction", unless explcitly set to something else
    public function indexAction()
    {
        // STAGE 3.  Choose, create, and optionally update models using business logic.

        // maps to arg 'view' from: $frontController->setParam('view', $view);
        $this->view = $this->getInvokeArg('view');

        // STAGE 4.  Apply business logic to create a presentation model for the view.

        // STAGE 5. Choose view and submit presentation model to view.

        $this->_response->appendBody($this->view->render('indexIndex.phtml'));
    }

}
