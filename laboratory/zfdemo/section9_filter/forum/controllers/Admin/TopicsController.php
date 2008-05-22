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
 * @version    $Id: TopicsController.php 121 2007-04-12 21:48:01Z gavin $
 */

require_once 'Zend/Controller/Action.php';
require_once 'forum/controllers/TopicsController.php';

class Forum_Admin_TopicsController extends Forum_TopicsController
{
    /**
     * called by the displayAction() controller in Forum_TopicController (../TopicController.php)
     */
    protected function displayStage3_4()
    {
        // STAGE 4.  Apply business logic to create a presentation model for the view.
        parent::displayStage3_4(); // also model classes auto-instantiate the model (STAGE 3)
        $this->view->admin = true;
        // When showing topics, make topic links point to "admin posts" of that topic
        $this->view->topicUrl = 'admin_posts/index/topic';
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
        // reroute straight to application's 404 controller
        throw new ZFDemo_Exception_Reroute(_('unknown admin action requested: ') . $name, 404);

        // Alternative:
        // generates exception notice for local requests, but identical to above for non-local requests
        // throw new ZFDemo_Exception(_('unknown admin action requested: ') . $name, 404);
    }
}
