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

class Forum_TopicsController extends ZFDemo_Controller_Action
{
    // topic id requested (if any)
    protected $topicId = false;

    // topics model (list of topics)
    protected $topics;


    public function helloAction()
    {
        // STAGE 3-5 :)
        echo date('Y/m/d H:i:s'), _(' Hello world!');
    }


    /**
     * The default action is "indexAction", unless explicitly set to something else.
     * Show a list of all topics, unless a specific topic was requested.
     */
    public function indexAction()
    {
        $this->displayStage3_4();
        $this->displayStage5();
    }


    /**
     * STAGE 3: Choose, create, and optionally update models using business logic.
     * STAGE 4: Apply business logic to create a presentation model for the view.
     */
    protected function displayStage3_4()
    {
        // ZFDemoModel_Topics provides static methods that auto-instantiate and manage the model
        $this->view->topics = ZFDemoModel_Topics::getPresentationModel();
        $this->view->user = 'anonymous';
        // Controller = 'index', Action = 'index', param name = 'topic'
        $this->view->topicUrl = 'index/index/topic';
        $this->view->hide = false;
        /////////////////////////////
        // ==> SECTION: acl <==
        $role = ZFModule_Forum::getRole();
        // another form of access control (whether or not to show hidden posts)
        $this->view->hide = ($role === 'moderator' || $role === 'admin') ? false : true;
    }


    /**
     * STAGE 5: Choose view and submit presentation model to view.
     */
    protected function displayStage5()
    {
        // Hard way:
        //$this->_response->appendBody($this->view->render('indexTopics.phtml'));
        $this->renderToSegment('body'); // initiate STAGE 6 in the view template
    }


    /**
     * redirect bogus URLs back to the application's "home" page
     */
    public function __call($name, $parameters)
    {
        $this->_redirect('/');
    }
}
