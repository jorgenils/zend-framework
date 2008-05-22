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
 * @package    Zend_Scheduler
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * File backend for keeping track of remaining tasks.
 *
 * @category   Zend
 * @package    Zend_Scheduler
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Scheduler_Backend_File extends Zend_Scheduler_Backend_Abstract
{
    /** @var string Filename */
    protected $_filename = '';

    public function setFilename($filename)
    {
        touch($filename);

        if (!is_writable($filename)) {
            throw new Zend_Scheduler_Exception("Cannot write to file '{$filename}'");
        }

        $this->_filename = $filename;
    }

    /**
     * Sets the remaining tasks to perform.
     * 
     * @param array $tasks Remaining tasks
     */ 
    public function saveQueue(array $tasks = null)
    {
        if (empty($this->_filename)) {
            throw new Zend_Scheduler_Exception('Filename must be set in backend');
        }

        if (is_array($tasks)) {
            $tasks = serialize($tasks);
        }
        file_put_contents($this->_filename, $tasks);
    }
 
    /**
     * Gets the remaining tasks to perform.
     * 
     * @return array Remaining tasks
     */ 
    public function loadQueue()
    {
        if (!is_readable($this->_filename)) {
            return array();
        }
        $tasks = unserialize(file_get_contents($this->_filename));
        if (!is_array($tasks)) {
            return array();
        }
        return $tasks;
    }

    /**
     * Clears all remaining tasks in the queue.
     */
    public function clearQueue()
    {
        $this->saveQueue();
    }
}
