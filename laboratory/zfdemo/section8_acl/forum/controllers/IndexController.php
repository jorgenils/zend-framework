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
 * @version    $Id: IndexController.php 121 2007-04-12 21:48:01Z gavin $
 */

class Forum_IndexController extends ZFDemo_Controller_Action
{
    protected $topicId = false;


    /**
     * Purely for sanity checking:  http://www.your server.com/zfdemo/forum/index/hello
     */
    public function helloAction()
    {
        // STAGE 3-5 :)
        echo date('Y/m/d H:i:s'), _(' Hello world!');
    }


    /**
     * The default action is "indexAction", unless explicitly set to something else.
     * Show all topics, unless a specific topic was requested.
     */
    public function indexAction()
    {
        // STAGE 3:  Choose, create, and optionally update models using business logic.

        // first, figure out which models we need by choosing between
        // showing posts for a topic, or showing a list of all topics
        if ($this->getTopicId())
        {
            // possibly valid topic, so try to show its posts by:
            $this->_forward('display'); // internally forwarding to a different controller
        } else {
            // no specific topic requested, so show a list of forum topics instead
            $this->redirectToTopics(); // externally redirect via HTTP
            return;
        }
    }


    /**
     * STAGE 3-5: Show all posts for the selected topic.
     */
    public function displayAction()
    {
        if (!$this->getTopicId()) // if user did not supply a valid topic id,
        {
            // we don't want search engines thinking the current URL is the topic list, so ..
            $this->redirectToTopics(); // show all topics instead
            return;
        }
        $this->displayStage3_4(); // business logic applied to models and request input data
        $this->displayStage5(); // choose view, submit presentation model to view
    }


    /**
     * STAGE 3:  Choose, create, and optionally update models using business logic.
     * STAGE 4.  Apply business logic to create a presentation model for the view.
     */
    protected function displayStage3_4()
    {
        // the posts and topics models auto-instantiate themselves, as needed (STAGE 3)
        $this->view->posts = ZFDemoModel_Posts::getPostsByTopicId($this->topicId);
        $this->view->topicId = $this->topicId;
        // the posts and topics models auto-instantiate themselves, as needed (STAGE 3)
        $topic = ZFDemoModel_Topics::getPresentationModelByTopicId($this->topicId);
        $this->view->topicName = $topic->topic_name;
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
        // $this->_response->appendBody($this->view->render('indexPosts.phtml', 'body'));
        $this->renderToSegment('body'); // initiate STAGE 6 in the view template
    }


    /**
     * STAGE 3:  Choose, create, and optionally update models using business logic.
     */
    public function getTopicId()
    {
        if (!$this->topicId) {
            $topic = intval($this->_request->getParam('topic'));
            if ($topic > 0 && $topic < 99999999) {
                $this->topicId = $topic;
            }
        }
        return $this->topicId;
    }


    /**
     * STAGE 3-7: Externally redirect user agent to new URL (call does not return)
     */
    public function redirectToTopics()
    {
        $this->setRedirectCode(303); // PRG pattern via http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
        $this->_redirect('forum/topics');
        // same as:
        // $this->getResponse()->setHeader('Location', $this->_request->getBaseUrl() . '/forum/topics');
        // exit;
    }


    /**
     * STAGE 3-7: Redirect bogus URLs back to the application's "home" page, instead of giving 404 error
     */
    public function __call($name, $parameters)
    {
        // This is an example.  In practical situations, we might sometimes prefer 404 errors instead.
        $this->_redirect('/'); // see __call() in PostsController.php for how to issue a 404 instead
    }
}
