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
 * @version    $Id: IndexController.php 115 2007-04-10 17:11:36Z gavin $
 */

require_once 'Zend/Controller/Action.php';

class IndexController extends ZFDemo_Controller_Action
{
    // the default action is "indexAction", unless explcitly set to something else
    public function indexAction()
    {
        // STAGE 3: Choose, create, and optionally update models using business logic.
        if (false === strpos($this->view->baseUrl, '/index.php')) {
            $this->view->baseUrl .= '/index.php';
        }
        // STAGE 4: Apply business logic to create a presentation model for the view.
        // STAGE 5: Choose view and submit presentation model to view.

        $this->renderToSegment('body');
    }


    /**
     * reload the DB tables using the DB SQL dump found in config/zfdemo.<type>.sql 
     */
    public function resetAction()
    {
        // STAGE 3: Choose, create, and optionally update models using business logic.
        $registry = Zend_Registry::getInstance();
        $db = $registry['db'];
        // if the DB is not configured to handle "large" queries, then we need to feed it bite-size queries
        $filename = $registry['configDir'] . 'zfdemo.' . $registry['config']->db->type . '.sql';
        $statements = preg_split('/;\n/', file_get_contents($filename, false));
        foreach ($statements as $blocks) {
            $sql = '';
            foreach (explode("\n", $blocks) as $line) {
                if (empty($line) || !strncmp($line, '--', 2)) {
                    continue;
                }
                $sql .= $line . "\n";
            }
            $sql = trim($sql);
            if (!empty($sql)) {
                $db->query($sql);
            }
        }
        // STAGE 4: Apply business logic to create a presentation model for the view.
        $this->view->filename = $filename;
        // STAGE 5: Choose view and submit presentation model to view.
        $this->renderToSegment('body');
    }


    /**
     * show user a link to access resetAction() above and reload/restore their DB
     * from config/zfdemo.<type>.sql
     */
    public function testDbFailedAction()
    {
        $this->renderToSegment('body');
    }


    /**
     * Example of how to send an HTTP 500 response.
     * If mail alerts are enabled in the config.ini, email is sent.
     */
    public function send500Action()
    {
        throw new ZFDemo_Exception_Reroute(_('Example action to test sending a HTTP 500 response'), 500);
    }
}
