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
 * @version    $Id: PostsController.php 121 2007-04-12 21:48:01Z gavin $
 */

require_once 'Zend/Controller/Action.php';
require_once 'forum/controllers/IndexController.php';

class Forum_Admin_PostsController extends Forum_IndexController
{
    /**
     * called by the displayAction() controller in Forum_IndexController (../IndexController.php)
     */
    protected function displayStage3_4()
    {
        // STAGE 4.  Apply business logic to create a presentation model for the view.
        parent::displayStage3_4();
        $this->view->admin = true;
    }


    /**
     * externally redirect browser via HTTP redirect to 'forum/admin_topics'
     */
    public function redirectToTopics()
    {
        $this->_redirect('forum/admin_topics');
    }


    /**
     * STAGE 3: Choose, create, and optionally update models using business logic.
     * STAGE 4: Apply business logic to create a presentation model for the view.
     */
    public function editAction()
    {
        $config = Zend_Registry::get('config'); // application-wide configuration ini
        if ($config->db->modelSet === 'pdo') {
            $this->renderToSegment('body', 'addedInSectionDb'); // initiate STAGE 6 in the view template
        }
    }


    /**
     * Just to be different, we show another alternative to handling bogus actions.
     * The bootstrap try..dispatch()..catch() will catch this exception and then show a 404 page.
     */
    public function __call($name, $parameters)
    {
        // generates exception notice for local requests, but identical to second throw below for non-local requests
        throw new ZFDemo_Exception(_('unknown admin action requested: ') . $name, 404);
        //throw new ZFDemo_Exception_Reroute(_('unknown admin action requested: ') . $name, 404);
    }
}
