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
 * @version    $Id: ErrorController.php 121 2007-04-12 21:48:01Z gavin $
 */

require_once 'Zend/Controller/Action.php';

class ErrorController extends ZFDemo_Controller_Action 
{
    public function init()
    {
        parent::init();
        $this->view->request = Zend_Debug::dump($this->getInvokeArg('origRequest'), null, false);
        $this->view->rerouteToReason = $this->getInvokeArg('rerouteToReason');
    }


    public function error500Action()
    {
        $this->renderToSegment('body', 'error500'); // render view script 'error500' to response segment 'body'
    }


    /**
     * The default action is "indexAction", unless explicitly set to something else.
     * Below, the magic method supports all actions.
     */
    public function __call($name, $parameters)
    {
        $this->renderToSegment('body', 'error404'); // render view script 'error404' to response segment 'body'
    }
}
