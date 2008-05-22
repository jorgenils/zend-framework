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
        /////////////////////////////
        // ==> SECTION: db <==
        // ZFDemoModel_Topics provides static methods that auto-instantiate and manage the model
        // ZFDemoModel_Posts provides static methods that auto-instantiate and manage the model
        $topicId = intval($_POST['topicId']);
        if ($topicId < 1 || $topicId > 99999) {
            $this->_redirect('forum/admin_topics');
        }
        $posts = ZFDemoModel_Posts::getDomainModel($topicId);
        $deletedRows = array(); // list of deleted posts indexed by post id 
        $visible     = array(); // visibility settings indexed by post id
        $changedRows = array(); // list of altered post rows

        ZFDemo_Log::log(_('INFO BEGIN editAction()'));
        try {
            // iterate over the form variable, queing actions for each change requested
            foreach ($_POST as $var => $val) {
                if (!strncmp($var, 'delete', 6)) {
                    $id = intval(substr($var, 6));
                    if (isset($posts[$id])) {
                        $deletedRows[$id] = $posts[$id];
                        ZFDemo_Log::log(_('INFO deleting post id #%1$d', $id));
                    }
                } elseif (!strncmp($var, 'visible', 7)) {
                    $id = intval(substr($var, 7));
                    if (isset($posts[$id])) {
                        $visible[$id] = 1; // ask Zend_Db_Table_Row to delete itself
                    }
                }
            }
    
            // for every post, make sure visibility setting matches the selections submitted via the form
            foreach ($posts as $id => $row) {
                if (!isset($deletedRows[$id])) { // don't update a row that will be deleted
                    if (!isset($visible[$id])) { // the checkbox for visibility of this post was not checked
                        $visible[$id] = 0; // web interface defaults to checking visible posts, so unchecked means not visible
                    }

                    if ($row->is_visible != $visible[$id]) {
                        ZFDemo_Log::log(_('INFO post id %1$d visibility set to %2$d', $id, $visible[$id]));
                        $row->is_visible = intval($visible[$id]);
                        $changedRows[$id] = $row; // add this row to the list of changed rows
                    }
                }
            }
            // now commit changes to DB
            $registry = Zend_Registry::getInstance();
            if (!$registry['config']->db->transactions) {
                // note: query aggregation would help performance (reader excercise)
                foreach ($deletedRows as $row) {
                    $row->delete(); // now do all the deletes at once
                }
                foreach ($changedRows as $row) {
                    $row->save(); // now synchronize all the modified rows with the table
                }
            } else {
                // table type supports transactions
                $db = $registry['db'];
                $db->beginTransaction();
                try {
                    // note: query aggregation would help performance (reader excercise)
                    foreach ($deletedRows as $row) {
                        $row->delete(); // now do all the deletes at once
                    }
                    foreach ($changedRows as $row) {
                        $row->save(); // now synchronize all the modified rows with the table
                    }
                    $db->commit();
                } catch (Exception $exception) {
                    $db->rollBack();
                    // re-throw the exception 
                    throw $exception; // allow normal processing by the preDispatch() of the plugin in bootstrap.php
                }
            }
        } catch (Exception $exception) {
            ZFDemo_Log::log(_('ERROR END editAction() - failed'));
            throw $exception; // resume normal processing of the exception
        }
        ZFDemo_Log::log(_('NOTICE END editAction() - succeeded'));
        // Controller = 'index', Action = 'index'
        $this->setRedirectCode(303); // PRG pattern via http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
        $this->_redirect('forum/admin_posts/index/topic/' . $topicId);
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
