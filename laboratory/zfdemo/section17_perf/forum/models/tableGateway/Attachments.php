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
 * @version    $Id: Attachments.php 115 2007-04-10 17:11:36Z gavin $
 */

// STAGE 3: Choose, create, and optionally update models using business logic.
// STAGE 4: Apply business logic to create a presentation model for the view.
//          - might result in updates to model, using API below


class ZFDemoModel_Attachments extends Zend_Db_Table_Abstract
{
    /* exact name of attachments DB table (case-sensitive)
     * @var string
     */
    protected $_name = 'attachments';

    /* primary key name
     * @var array
     */
    protected $_primary = array('attachment_id');

    /* Map detailing foreign key relationships between this dependent table and the parent table it depends on.
     * @var array
     */
    protected $_referenceMap = array(
        'User' => array(
            'columns'       => array('user_id'), // foreign key column name(s)
            'refTableClass' => 'ZFDemoModel_Users', // parent table class name
            'refColumns'    => array('user_id') // parent table's primary key(s)
        ),
        'Post' => array(
            'columns'       => array('post_id'), // foreign key column name(s)
            'refTableClass' => 'ZFDemoModel_Posts', // parent table class name
            'refColumns'    => array('post_id'), // parent table's primary key(s)
            'onDelete'      => self::CASCADE // if the post is deleted, delete any attachments that reference the post
        )
    );


    /**
     * In addition to regular constructor, remove cascading write operations performed by Zend_Db_Table*,
     * if the DB already supports DRI (see notes for "db.DRI" in config/config.ini).
     */
    public function __construct(array $config = array())
    {
        parent::__construct($config);
        $registry = Zend_Registry::getInstance();
        // if DB supports DRI, disable cascading deletes within Zend_Db_Table*
        if ($registry['config']->db->DRI) {
            unset($this->_referenceMap['Post']['onDelete']);
        }
    }
}
