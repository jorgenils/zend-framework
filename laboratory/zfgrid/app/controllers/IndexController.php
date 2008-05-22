<?php
/* Zend Framework 1.0 - Basic MVC/Database example application */

class IndexController extends Zend_Controller_Action
{
    /**
     * @var Zend_Db_Adapter_Abstract $_db
     */
    protected $_db;

    public function init()
    {
        $this->_db = Zend_Registry::get('defaultDb');
    }

    public function indexAction()
    {
        $this->view->tables = $this->_db->listTables();
    }

}


/**
 * Zend Framework
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
 * @category   Zend
 * @package    Zend_Grid
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: TestHelper.php 4528 2007-04-17 23:10:47Z darby $
 */
