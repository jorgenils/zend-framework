<?php

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
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Gdata_EntryAtom
 */
require_once 'Zend/Gdata/Entry.php';

/**
 * @see Zend_Gdata_Kind_EventEntry
 */
require_once 'Zend/Gdata/Kind/EventEntry.php';

/**
 * @see Zend_Gdata_Calendar_Extension_SendEventNotifications
 */
require_once 'Zend/Gdata/Calendar/Extension/SendEventNotifications.php';

/**
 * @see Zend_Gdata_Calendar_Extension_Timezone
 */
require_once 'Zend/Gdata/Calendar/Extension/Timezone.php';


/**
 * Data model class for a Google Calendar Event Entry 
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Calendar_EventEntry extends Zend_Gdata_Kind_EventEntry
{

    protected $_entryClassName = 'Zend_Gdata_Calendar_EventEntry';
    protected $_sendEventNotifications = null;
    protected $_timezone = null;

    public function __construct($element = null)
    {
        foreach (Zend_Gdata_Calendar::$namespaces as $nsPrefix => $nsUri) {
            $this->registerNamespace($nsPrefix, $nsUri);
        }
        parent::__construct($element);
    }

    public function getDOM($doc = null)
    {
        $element = parent::getDOM($doc);
        if ($this->_sendEventNotifications != null) {
            $element->appendChild($this->_sendEventNotifications->getDOM($element->ownerDocument));
        }
        if ($this->_timezone != null) {
            $element->appendChild($this->_timezone->getDOM($element->ownerDocument));
        }        

        return $element;
    }
    
    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        
        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gCal') . ':' . 'sendEventNotifications'; 
                $sendEventNotifications = new Zend_Gdata_Calendar_Extension_SendEventNotifications();
                $sendEventNotifications ->transferFromDOM($child);
                $this->_sendEventNotifications = $sendEventNotifications;
                break;
            case $this->lookupNamespace('gCal') . ':' . 'timezone'; 
                $timezone = new Zend_Gdata_Calendar_Extension_Timezone();
                $timezone ->transferFromDOM($child);
                $this->_timezone = $timezone;
                break;            
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    public function getEventNotifications() 
    {
        return $this->_eventNotifications;
    }

    public function setEventNotifications($value) 
    {
        $this->_eventNotifications = $value;
        return $this;
    }

    public function getTimezone() 
    {
        return $this->_timezone;
    }

    /**
     * @param Zend_Gdata_Calendar_Extension_Timezone $value
     * @return Zend_Gdata_Extension_EventEntry Provides a fluent interface
     */    
    public function setTimezone($value) 
    {
        $this->_timezone = $value;
        return $this;
    }    

}
