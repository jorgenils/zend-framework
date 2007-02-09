<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to version 1.0 of the Zend Framework
 * license, that is bundled with this package in the file LICENSE, and
 * is available through the world-wide-web at the following URL:
 * http://www.zend.com/license/framework/1_0.txt. If you did not receive
 * a copy of the Zend Framework license and are unable to obtain it
 * through the world-wide-web, please send a note to license@zend.com
 * so we can mail you a copy immediately.
 *
 * @package    Zend_Mail
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */


/**
 * Zend_Mail_Storage_Exception
 */
require_once 'Zend/Mail/Storage/Exception.php';

/**
 * @package    Zend_Mail
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */
class Zend_Mail_Storage_Folder implements RecursiveIterator
{
    /**
     * subfolders of folder array(localName => Zend_Mail_Storage_Folder folder)
     */
    private $_folders;

    /**
     * local name (name of folder in parent folder)
     */
    private $_localName;

    /**
     * global name (absolute name of folder)
     */
    private $_globalName;

    /**
     * folder is selectable if folder is able to hold messages, else it's just a parent folder
     */
    private $_selectable = true;

    /**
     * create a new mail folder instance
     *
     * @param string $localName  name of folder in current subdirectory
     * @param string $globalName absolute name of folder
     * @param bool   $selectable if true folder holds messages, if false it's just a parent for subfolders
     * @param array  $folders    init with given instances of Zend_Mail_Storage_Folder as subfolders
     */
    public function __construct($localName, $globalName = '', $selectable = true, array $folders = array())
    {
        $this->_localName  = $localName;
        $this->_globalName = $globalName ? $globalName : $localName;
        $this->_selectable = $selectable;
        $this->_folders    = $folders;
    }

    /**
     * implements RecursiveIterator::hasChildren()
     *
     * @return bool current element has children
     */
    public function hasChildren()
    {
        $current = $this->current();
        return $current && $current instanceof Zend_Mail_Storage_Folder && !$current->isLeaf();
    }

    /**
     * implements RecursiveIterator::getChildren()
     *
     * @return Zend_Mail_Storage_Folder same as self::current()
     */
    public function getChildren()
    {
        return $this->current();
    }

    /**
     * implements Iterator::valid()
     *
     * @return bool check if there's a current element
     */
    public function valid()
    {
        return key($this->_folders) !== null;
    }

    /**
     * implements Iterator::next()
     */
    public function next()
    {
        next($this->_folders);
    }

    /**
     * implements Iterator::key()
     *
     * @return string key/local name of current element
     */
    public function key()
    {
        return key($this->_folders);
    }

    /**
     * implements Iterator::current()
     *
     * @return Zend_Mail_Storage_Folder current folder
     */
    public function current()
    {
        return current($this->_folders);
    }

    /**
     * implements Iterator::rewind()
     */
    public function rewind()
    {
        reset($this->_folders);
    }

    /**
     * get subfolder named $name
     *
     * @param string $name wanted subfolder
     * @return Zend_Mail_Storage_Folder folder named $folder
     * @throws Zend_Mail_Storage_Exception
     */
    public function __get($name)
    {
        if (!isset($this->_folders[$name])) {
            throw new Zend_Mail_Storage_Exception("no subfolder named $name");
        }

        return $this->_folders[$name];
    }

    /**
     * add or replace subfolder named $name
     *
     * @param string $name local name of subfolder
     * @param Zend_Mail_Storage_Folder instance for new subfolder
     */
    public function __set($name, Zend_Mail_Storage_Folder $folder)
    {
        $this->_folders[$name] = $folder;
    }

    /**
     * magic method for easy output of global name
     *
     * @return string global name of folder
     */
    public function __toString()
    {
        return $this->getGlobalName();
    }

    /**
     * get local name
     *
     * @return string local name
     */
    public function getLocalName()
    {
        return $this->_localName;
    }

    /**
     * get global name
     *
     * @return string global name
     */
    public function getGlobalName()
    {
        return $this->_globalName;
    }

    /**
     * is this folder selectable?
     *
     * @return bool selectable
     */
    public function isSelectable()
    {
        return $this->_selectable;
    }

    /**
     * check if folder has no subfolder
     *
     * @return bool true if no subfolders
     */
    public function isLeaf()
    {
        return empty($this->_folders);
    }
}
