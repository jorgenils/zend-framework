<?php
/** Zend_Scheduler_Backend_Abstract */
require_once 'Zend/Scheduler/Backend/Abstract.php';

class CustomBackend extends Zend_Scheduler_Backend_Abstract
{
    /**
     * Sets the remaining tasks to perform.
     * 
     * @param array $tasks Remaining tasks
     */ 
    public function saveQueue(array $tasks = null)
    {
    }
 
    /**
     * Gets the remaining tasks to perform.
     */ 
    public function loadQueue()
    {
    }

    /**
     * Clears all remaining tasks in the queue.
     */
    public function clearQueue()
    {
    }
}
