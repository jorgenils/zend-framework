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

require_once 'Zend/Db/Table/Abstract.php';

// STAGE 3: Choose, create, and optionally update models using business logic.
// STAGE 4: Apply business logic to create a presentation model for the view.
//          - might result in updates to model, using API below


class ZFDemoModel_Posts extends Zend_Db_Table_Abstract
{
    /**
     * The only instance of this class created by ZFDemo.
     * @var ZFDemoModel_Posts
     */
    private static $_table = null;

    /**
     * Posts Domain Model (cached topics rowset)
     * Array of topics, where each topic is an array of posts to the topic.
     * @var array of Zend_Db_Table_Row
     */
    private static $_domainModel = null;

    /**
     * Posts Presentation Model (cached topics rowset)
     * @var array of arrays ( Zend_Db_Table_Row->toArray() )
     */
    private static $_presentationModel = null;

    /* exact name of users DB table (case-sensitive)
     * @var string
     */
    protected $_name = 'posts';

    /* primary key name
     * @var array
     */
    protected $_primary = array('post_id');

    /* List of dependent table *CLASSES* having foreign key references to $_primary key above.
     * @var array
     */
    protected $_dependentTables = array('ZFDemoModel_Attachments');

    /* Map detailing foreign key relationships between this dependent table and the parent table it depends on.
     * @var array
     */
    protected $_referenceMap = array(
        'User' => array(
            'columns'       => array('user_id'), // foreign key column name(s)
            'refTableClass' => 'ZFDemoModel_Users', // parent table class name
            'refColumns'    => array('user_id') // parent table's primary key(s)
        ),
        'Topic' => array(
            'columns'       => array('topic_id'), // foreign key column name(s)
            'refTableClass' => 'ZFDemoModel_Topics', // parent table class name
            'refColumns'    => array('topic_id'), // parent table's primary key(s)
            'onDelete'      => self::CASCADE // if topic is deleted, delete any posts that belong to the deleted topic
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
            unset($this->_referenceMap['Topic']['onDelete']);
        }
    }


    /**
     * Create the singleton instance.
     */
    public static function getInstance()
    {
        if (self::$_table === null) {
            self::$_table = new self();
        }

        return self::$_table;
    }


    /**
     * Returns the topics table as a domain model (array of topic row set objects).
     * STAGE 3: Choose, create, and optionally update models using business logic.
     * The domain model can be manipulated later by controllers.
     * @return array of Zend_Db_Table_Row (row from 'posts' table) indexed by id, sorted by creation time
     */
    public static function getDomainModel($topicId)
    {
        if (!isset(self::$_domainModel[$topicId])) {
            $table = self::getInstance();
            $where  = $table->getAdapter()->quoteInto('topic_id = ?', $topicId);
            $rowset = $table->fetchAll($where, 'creation_time');
            $posts = array();
            foreach($rowset as $row) {
                $posts[$row->post_id] = $row;
            }
            self::$_domainModel[$topicId] = $posts;
        }

        return self::$_domainModel[$topicId];
    }


    /**
     * Returns the posts of a topic as a presentation model (array of arrays containing
     * information about each post, suitable for use by a view) by mirroring the domain model
     * into a presentation model.  The presentation model can be modified to support
     * the needs of a view, without mangling the raw, real underlying table data.
     * STAGE 4: Apply business logic to create a presentation model for the view.
     * @return array of ArrayObject (post info)
     */
    public static function getPostsByTopicId($topicId)
    {
        if (!isset(self::$_presentationModel[$topicId])) {
            $posts = array();
            foreach (self::getDomainModel($topicId) as $row) {
                $row = new ArrayObject($row->toArray(), ArrayObject::ARRAY_AS_PROPS);
                $row->user = ZFDemoModel_Users::getById($row->user_id);
                $posts[] = $row;

                /////////////////////////////
                // ==> SECTION: l10n <==
                // create a Locale object for the owner of this post (not the user of this request)
                $postLocale = new Zend_Locale($row->user->locale);
                $row->country = ZFModule_Forum::getCountry($postLocale->getRegion());
                $userLocale = ZFModule_Forum::getUserLocale(); // locale of the user of this request
                $offset = ZFModule_Forum::getTimeOffset();
                if ($row->modification_time != $row->creation_time) {
                    $row->modification_time = new Zend_Date($row->modification_time, $userLocale);
                    $row->modification_time->addTimestamp($offset); // express date/time in user's local timezone
                } else {
                    $row->modification_time = '';
                }
                $row->creation_time = new Zend_Date($row->creation_time, $userLocale);
                $row->creation_time->addTimestamp($offset); // express date/time in user's local timezone
            }

            // cache result only for duration of this request
            self::$_presentationModel[$topicId] = $posts;
        }

        return self::$_presentationModel[$topicId];
    }


    /**
     * Submit a new post to a topic.
     */
    public static function submit($userId, $topicId, $subject, $body)
    {
        $table = self::getInstance();
        $db = $table->getAdapter();
        $data = array(
            // Use Zend_Db_Expr to prevent DB adapter-specific quoting of a string containing SQL expressions.
            // Manually quote any data embedded in the expression using $db->quoteInto().
            'creation_time' => new Zend_Db_Expr('NOW()'),
            'user_id' => $userId,
            'topic_id' => $topicId,
            'subject' => $subject,
            'content' => $body
        );
        $result = 0; // status flag indicating if successfully incremented user's post count
        $postId = 0; // id of the post inserted into the 'posts' table
        $registry = Zend_Registry::getInstance();

        if (!$registry['config']->db->transactions) {
            $rowsInserted = $table->insert($data);
            if ($rowsInserted === 1) {
                $postId = $db->lastInsertId();
                $result = ZFDemoModel_Users::incrementPostCount($userId);
            }
        }
        else {
            // table type supports transactions
            $db->beginTransaction();
            try {
                $rowsInserted = $table->insert($data);
                if ($rowsInserted === 1) {
                    $postId = $db->lastInsertId();
                    $result = ZFDemoModel_Users::incrementPostCount($userId);
                }
                if ($result == 1) {
                    $db->commit();
                }
            } catch (Exception $e) {
                $db->rollBack();
                throw $e; // re-throw the exception so that it can be processed normally by the preDispatch() of the plugin in bootstrap.php
            }
        }

        if ($result != 1) {
            throw new ZFDemo_Exception(_('CRITICAL User id "%1$s" post count could not be incremented for post id "%2$s".', $userId, $postId), 500);
        }
    }
}
