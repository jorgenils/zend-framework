<?php
/* Zend Framework 1.0 - Basic MVC/Database example application */

class GridController extends Zend_Controller_Action
{
    public function init()
    {
        $tableName = $this->_getParam('table', false);
        if ($tableName === false) {
            $this->_redirect('/index');
        }
        $this->_model = new TableModel(array('name' => $tableName));
    }

    public function showAction()
    {
        $params = $this->getRequest()->getUserParams();
        $filters = array(
            'wherecolumn' => 'alnum',
            'order'       => 'alnum',
            'count'       => 'digits',
            'offset'      => 'digits'
        );
        $valids = array(
            'wherecolumn' => array('presence' => 'optional'),
            'wherevalue'  => array('presence' => 'optional'),
            'order'       => array('presence' => 'optional'),
            'count'       => array('int', 'default' => 20),
            'offset'      => 'int'
        );
        $input = new Zend_Filter_Input($filters, $valids, $params);
        if (!$input->isValid()) {
            $this->_redirect();
        }
        $whereColumn  = $input->wherecolumn;
        $whereValue   = $input->wherevalue;
        $order        = $input->order;
        $count        = $input->count;
        $offset       = $input->offset;

        $db = $this->_model->getAdapter();
        $tableInfo = $this->_model->info();
        $this->view->tableInfo = $tableInfo;

        $where = null;
        if ($whereColumn) {
            $expr = $db->quoteIdentifier($whereColumn) . ' IN (?)';
            $where = $db->quoteInto($expr, $whereValue);
        }

        $this->view->rowset = $this->_model->fetchAll(
            $where, $order, $count, $offset);

        $select = $db->select()
            ->from($tableInfo['name'], 'COUNT(*)');

        $this->view->rowCount = $db->fetchOne($select);

        $this->view->distinctValues = array();
        foreach ($tableInfo['cols'] as $columnName) {
            $select = $db->select()
                ->from($tableInfo['name'], $columnName)
                ->distinct();
            $this->view->distinctValues[$columnName] = $db->fetchCol($select);
        }
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
