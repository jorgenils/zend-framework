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
 * @package    Zend_Db
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Mysql.php 6847 2007-11-18 05:24:21Z peptolab $
 */


/**
 * @see Zend_Db_TestUtil_Mysqli
 */
require_once 'Zend/Db/TestUtil/Mysqli.php';


PHPUnit_Util_Filter::addFileToFilter(__FILE__);


/**
 * @category   Zend
 * @package    Zend_Db
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_TestUtil_Pdo_Mysql extends Zend_Db_TestUtil_Mysqli
{
    public function getParams(array $constants = array())
    {
        $constants = parent::getParams($constants);

        if (!isset($constants['driver_options'])) {
            $constants['driver_options'] = array();
        }

        if (!isset($constants['driver_options'][PDO::MYSQL_ATTR_USE_BUFFERED_QUERY])) {
            $constants['driver_options'][PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;
        }

        return $constants;
    }
}

