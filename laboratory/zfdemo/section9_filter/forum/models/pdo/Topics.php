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
 * @version    $Id: Topics.php 121 2007-04-12 21:48:01Z gavin $
 */

// STAGE 3: Choose, create, and optionally update models using business logic.
// STAGE 4: Apply business logic to create a presentation model for the view.
//          - might result in updates to model, using API below

require_once 'forum/models/Abstract.php';

class ZFDemoModel_Topics extends ZFDemoModel
{
    /**
     * Create a presentation model of the forum's topics list, appropriate for use by a view.
     */
    public static function getPresentationModel()
    {
        $db = Zend_Registry::get('db');
        $q = 'SELECT * FROM topics ORDER BY creation_time ASC';
        $posts = array();
        foreach ($db->query($q) as $row) {
            $row = new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS);
            if ($row->modification_time != $row->creation_time) {
                $row->modification_time = new Zend_Date($row->modification_time, Zend_Date::ISO_8601);
            } else {
                $row->modification_time = '';
            }
            $row->country = '';
            $row->creation_time = new Zend_Date($row->creation_time, Zend_Date::ISO_8601);
            $row->user = ZFDemoModel_Users::getById($row->user_id);
            $topics[] = $row;
        }

        return new ArrayObject($topics, ArrayObject::ARRAY_AS_PROPS);
    }


    /**
     * Utility function to retrieve topics by their id.
     */
    public static function getPresentationModelByTopicId($topicId)
    {
        return parent::_getBy(__CLASS__, 'topic_id', $topicId);
    }
}
