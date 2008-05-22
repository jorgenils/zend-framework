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
 * @version    $Id: SubmissionsController.php 121 2007-04-12 21:48:01Z gavin $
 *
 * /////////////////////////////
 * // ==> SECTION: filter <==
 */

class Forum_SubmissionsController extends ZFDemo_Controller_Action
{
    protected $topicId = false;


    /**
     * The default action is "indexAction", unless explicitly set to something else.
     * Show all topics, unless a specific topic was requested.
     * STAGE 3-5: Show a post submission form for the selected topic.
     */
    public function postFormAction()
    {
        // STAGE 3:  Choose, create, and optionally update models using business logic.

        // first, figure out which models we need by choosing between
        // showing posts for a topic, or showing a list of all topics
        if (!$this->getTopicId()) {
            throw new ZFDemo_Exception(_('ERROR: invalid topic id'), 404);
        } else {
            // STAGE 4: Apply business logic to create a presentation model for the view.
            // Reader excercise: many pieces of information could be added,
            // but this demo includes only the minimum needed.
            $this->view->topicId = $this->topicId;
        }
        // STAGE 5: Choose view and submit presentation model to view.
        $this->renderToSegment('body'); // initiate STAGE 6 in the view template
    }


    /**
     * STAGE 3:  Choose, create, and optionally update models using business logic.
     * Input data (e.g. via parameters in the URL) can be used to created domain models.
     */
    public function getTopicId()
    {
        if (!$this->topicId) {
            $topic = $this->_request->getParam('topic');
            require_once 'Zend/Validate/Digits.php';
            $validator = new Zend_Validate_Digits(); // only permit digits using ctype_digit()
            if ($validator->isValid($topic)) {
                if ($topic > 0 && $topic < 99999999) {
                    $this->topicId = $topic;
                }
            }
        }
        return $this->topicId;
    }


    /**
     * Process a submitted post.
     */
    public function submitFormAction()
    {
        // STAGE 3:  Choose, create, and optionally update models using business logic.
        $topicId = $this->getTopicId();
        require_once 'Zend/Filter/StripTags.php';
        $filter  = new Zend_Filter_StripTags(); // only permit digits using ctype_digit()
        $subject = $filter->filter($_POST['subject']);
        $body    = $filter->filter($_POST['body']);
        ZFDemoModel_Posts::submit(ZFModule_Forum::getAuthorizationId(), $topicId, $subject, $body);
        
        // STAGE 4: Apply business logic to create a presentation model for the view.
        // STAGE 5: Choose view and submit presentation model to view.
        $this->setRedirectCode(303); // PRG pattern via http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
        $this->_redirect("/forum/index/index/topic/$topicId");
    }
}
