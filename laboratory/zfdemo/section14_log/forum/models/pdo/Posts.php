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
 * @version    $Id: Posts.php 121 2007-04-12 21:48:01Z gavin $
 */

// STAGE 3: Choose, create, and optionally update models using business logic.
// STAGE 4: Apply business logic to create a presentation model for the view.
//          - might result in updates to model, using API below


class ZFDemoModel_Posts
{
    /**
     * Zend DB Adapter
     * @var Zend_Db_Adapter_Pdo_Mysql
     */
    private static $_db;

    /**
     * Array of topics, where each topic is an array of posts to the topic.
     * @var array
     */
    private static $_posts = array();


    /**
     * Create a cached set of sets of posts, grouped by topic.
     * Only one set of posts are created for one topic ($topicId) per invocation.
     * Cached information lasts only for the duration of this request.
     */
    public static function getPostsByTopicId($topicId)
    {
        static $stmt = null;

        if (!isset(self::$_posts[$topicId])) {
            $db = Zend_Registry::get('db');
            if ($stmt === null) {
                $q = 'SELECT * FROM posts WHERE topic_id = ? ORDER BY creation_time ASC';
                $stmt = $db->prepare($q);
                if ($stmt === false) {
                    throw new ZFDemo_Exception("Preparing query '$q' failed.", 500);
                }
            }
            $stmt->execute(array(intval($topicId)));
            $posts = array();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $row = new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS);
                if ($row->modification_time != $row->creation_time) {
                    $row->modification_time = new Zend_Date($row->modification_time, Zend_Date::ISO_8601);
                } else {
                    $row->modification_time = '';
                }
                $row->country = '';
                $row->creation_time = new Zend_Date($row->creation_time, Zend_Date::ISO_8601);
                $row->user = ZFDemoModel_Users::getById($row->user_id);
                $posts[] = $row;
            }
            $stmt->closeCursor();

            self::$_posts[$topicId] = $posts;
        }
        return self::$_posts[$topicId];
    }

    public static function submit()
    {
        echo __FILE__, ':', __LINE__, ':submit(): TODO - PDO model support for submit<br />
            Post submission is currently only implemented for Zend_Db_Table* (i.e. SECTION 10+)';
        exit;
    }
}
