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
 * @see Zend_Gdata_Extension_EventStatus
 */
require_once 'Zend/Gdata/Extension/EventStatus.php';

/**
 * @see Zend_Gdata_Extension_ExtendedProperty
 */
require_once 'Zend/Gdata/Extension/ExtendedProperty.php';

/**
 * @see Zend_Gdata_Extension_OriginalEvent
 */
require_once 'Zend/Gdata/Extension/OriginalEvent.php';

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
    protected $_extendedProperty = array();
    protected $_originalEvent = array();

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
        if ($this->_extendedProperty != null) {
            foreach ($this->_extendedProperty as $extProp) {
                $element->appendChild(
                        $extProp->getDOM($element->ownerDocument));
            }
        }
        if ($this->_originalEvent != null) {
            $element->appendChild(
                    $this->_originalEvent->getDOM($element->ownerDocument));
        }
        return $element;
    }
    
    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gd') . ':' . 'extendedProperty'; 
                $extProp = new Zend_Gdata_Extension_ExtendedProperty();
                $extProp->transferFromDOM($child);
                $this->_extendedProperty[] = $extProp;
                break;
            case $this->lookupNamespace('gd') . ':' . 'originalEvent'; 
                $originalEvent = new Zend_Gdata_Extension_OriginalEvent();
                $originalEvent ->transferFromDOM($child);
                $this->_originalEvent = $originalEvent;
                break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function getExtendedProperty() 
    {
        return $this->_extendedProperty;
    }

    public function setExtendedProperty($value) 
    {
        $this->_extendedProperty = $value;
        return $this;
    }

    public function getOriginalEvent() 
    {
        return $this->_originalEvent;
    }

    public function setOriginalEvent($value) 
    {
        $this->_originalEvent = $value;
        return $this;
    }

}
