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
 * @version    $Id: SorryController.php 121 2007-04-12 21:48:01Z gavin $
 */

class Forum_SorryController extends ZFDemo_Controller_Action
{
    /**
     * The default action is "indexAction", unless explicitly set to something else.
     * Show all topics, unless a specific topic was requested.
     */
    public function indexAction()
    {
        // STAGE 5: Choose view and submit presentation model to view.
        $params = $this->_request->getParams();
        if (isset($params['error'])) {
            $this->view->error = $params['error'];
        } else {
            $this->view->error = _('Sorry, an unknown error has occurred.');
        }
        if (isset($params['origRequestUri'])) {
            $this->view->orig = $params['origRequestUri'];
        }

        $this->renderToSegment('body'); // initiate STAGE 6 in the view template
    }
}
