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
 * @package    Zend_Filter
 * @subpackage Zend_Filter_Builder
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Zend Filter Builder Filter Factory Interface
 *
 * All this does is just ensure there's a create() method. If there were already 
 * a generic factory interface in the Zend Framework (or PHP) this wouldn't 
 * really be necessary.
 */
interface Zend_Filter_Builder_FilterFactory_Interface
{
    /**
     * Create
     *
     * Factory method that produces an instance of Zend_Filter_Interface. It 
     * takes the base name of the filter class, and the list of namespaces in 
     * the options and returns an instance of the first matching class found.
     *
     * @param string Filter class base name
     * @param array Optional constructor arguments for the filter
     * @returns Zend_Filter_Interface
     * @throws none
     */
    public function create($name, $args);
}
