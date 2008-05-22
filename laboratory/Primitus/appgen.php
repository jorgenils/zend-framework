<?php
/**
 * Zend Application
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
 * @package    Application
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

//if(posix_getuid() != 0) {
//	die("You must run this application as root\n");
//}

$_consts['BASEDIR'] = dirname(__FILE__);

$_consts['APP_INC_DIR'] = $_consts['BASEDIR']."/include";
$_consts['APP_TMP_DIR'] = $_consts['BASEDIR']."/webtmp";
$_consts['APP_LIB_DIR'] = $_consts['BASEDIR']."/lib";
$_consts['ZFLIB_INC_DIR'] = $_consts['BASEDIR']."/ZFApp";
$_consts['SMARTY_INC_DIR'] = $_consts['APP_LIB_DIR']."/smarty/libs";
$_consts['ZLIB_INC_DIR'] = $_consts['APP_LIB_DIR']."/Zlib";

$_consts['APP_VIEW_DIR'] = $_consts['APP_INC_DIR']."/views";
$_consts['APP_VIEW_TMP_DIR'] = $_consts['APP_TMP_DIR']."/views_c";
$_consts['APP_PLUGINS_DIR'] = $_consts['APP_INC_DIR']."/plugins";
$_consts['APP_VIEW_PLUGINS_DIR'] = $_consts['APP_PLUGINS_DIR']."/view";
$_consts['APP_CFG_DIR'] = $_consts['APP_INC_DIR']."/config";
$_consts['CONTROLLER_DIR'] = $_consts['APP_INC_DIR']."/controllers";

$_consts['APP_CACHE_TMP_DIR'] = $_consts['APP_TMP_DIR']."/cache";

$_consts['DB_USERNAME'] = "root";
$_consts['DB_PASSWORD'] = "";
$_consts['DB_DSN'] = "mysql:host=localhost;dbname=%s";

ini_set("include_path", "{$_consts['ZLIB_INC_DIR']}:.");

require_once "Zend/Filter.php";

if(empty($_SERVER['argv'][1])) {
    print_usage();    
} else {
    $filter = new Zend_Filter();
    $_consts['APPNAME'] = $filter->getAlpha($_SERVER['argv'][1]);
}

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($_consts['BASEDIR']));

foreach($it as $filename) {
    if(!is_file($filename)) {
        continue;
    }
    
    $file = file_get_contents($filename);
    
    $filename_demangled = $filename;
    
    foreach($_consts as $constant => $value) {
        $file = str_replace("%$constant%", $value, $file);
        $filename_demangled = str_replace("%$constant%", $value, $filename_demangled);
    }

    if(strcmp($filename, $filename_demangled) != 0) {
        @unlink($filename);        
        $filename = $filename_demangled;
    } 
    file_put_contents($filename, $file);
}

function print_usage() {
    die("Usage: {$_SERVER['argv'][0]} <appname>\n\n");
}
?>
