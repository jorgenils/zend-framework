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
 * @version    $Id: Topics.php 115 2007-04-10 17:11:36Z gavin $
 */

require_once 'Zend/Db/Table/Abstract.php';

// STAGE 3: Choose, create, and optionally update models using business logic.
// STAGE 4: Apply business logic to create a presentation model for the view.
//          - might result in updates to model, using API below


class ZFDemoModel_Topics extends Zend_Db_Table_Abstract
{
    /**
     * Topics Domain Model (cached topics rowset)
     * @var array of Zend_Db_Table_Row
     */
    private static $_domainModel = null;

    /**
     * Topics Presentation Model (cached topics rowset)
     * @var array of arrays ( Zend_Db_Table_Row->toArray() )
     */
    private static $_presentationModel = null;

    /* exact name of users DB table (case-sensitive)
     * @var string
     */
    protected $_name = 'topics';

    /* primary key name
     * @var array
     */
    protected $_primary = array('topic_id');

    /* List of dependent table *CLASSES* having foreign key references to $_primary key above.
     * @var array
     */
    protected $_dependentTables = array('ZFDemoModel_Posts');

    /* Map detailing foreign key relationships between this dependent table and the parent table it depends on.
     * @var array
     */
    protected $_referenceMap = array(
        'User' => array(
            'columns'       => array('user_id'), // foreign key column name(s)
            'refTableClass' => 'ZFDemoModel_Users', // parent table class name
            'refColumns'    => array('user_id') // parent table's primary key(s)
        )
    );


    /**
     * Returns the topics table as a domain model.
     * STAGE 3: Choose, create, and optionally update models using business logic.
     * The domain model can be manipulated later by controllers.
     * @return array of topic row set objects indexed by topic id
     */
    public static function getDomainModel()
    {
        if (self::$_domainModel === null) {
            $topicsTable = new self();
            $rowset = $topicsTable->fetchAll(1, 'modification_time');
            foreach($rowset as $row) {
                self::$_domainModel[$row->topic_id] = $row;
            }
        }

        return self::$_domainModel;
    }


    /**
     * Returns the topics table as a presentation model (array of arrays containing
     * information about each topic, suitable for use by a view) by mirroring
     * the domain model into a presentation model.  The presentation model can be modified
     * to support the needs of a view, without mangling the raw, real underlying table data.
     * STAGE 4: Apply business logic to create a presentation model for the view.
     * @return array of ArrayObjects (containing topic info) indexed by topic id
     */
    public static function getPresentationModel()
    {
        if (self::$_presentationModel === null) {
            foreach(self::getDomainModel() as $row) {
                $row = new ArrayObject($row->toArray(), ArrayObject::ARRAY_AS_PROPS);
                $row->user = ZFDemoModel_Users::getById($row->user_id);
                self::$_presentationModel[$row->topic_id] = $row;

                /////////////////////////////
                // ==> SECTION: l10n <==
                // create a Locale object for the owner of this post (not the user of this request)
                $postLocale = new Zend_Locale($row->user->locale);
                $row->country = ZFModule_Forum::getCountry($postLocale->getRegion());
                $userLocale = ZFModule_Forum::getUserLocale(); // locale of the user of this request
                $userLocale = Zend_Registry::get('userLocale');
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
        }

        return self::$_presentationModel;
    }


    /**
     * Return a topic presentation model for $topicId appropriate for use by a view
     * @return ArrayObject containing information for $topicId
     */
    public static function getPresentationModelByTopicId($topicId)
    {
        $topics = self::getPresentationModel();

        return $topics[$topicId];
    }
}
