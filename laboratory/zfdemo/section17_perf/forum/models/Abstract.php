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
 * @version    $Id: Abstract.php 115 2007-04-10 17:11:36Z gavin $
 *
 *
 * Example of common generic "fetcher" helper (used only by the PDO models in "/pdo")
 * Compare to ZF's support for table relationships in "/tableGateway", as documented here:
 *    http://framework.zend.com/wiki/x/K1c
 */
abstract class ZFDemoModel
{
    protected static function _getBy($classname, $col, $val)
    {
        list($junk, $table) = explode('_', $classname);
        if (is_numeric($val)) {
            $q = "SELECT * FROM $table WHERE $col=" . $val;
        } else {
            $q = "SELECT * FROM $table WHERE $col='" . mysql_escape_string($val) . "'";
        }

        $db = Zend_Registry::get('db');
        $stmt = $db->query($q);
        // http://www.php.net/manual/en/function.PDOStatement-fetchObject.php
        while ($row = $stmt->fetchObject()) {
            $rows[] = $row;
        }
        $stmt->closeCursor();
        if (count($rows) > 1) {
            return $rows;
        } else {
            return $rows[0];
        }
    }
}
