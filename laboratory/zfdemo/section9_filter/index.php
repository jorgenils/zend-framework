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
 * @version    $Id: index.php 122 2007-04-13 14:27:45Z gavin $
 *
 * http://framework.zend.com/wiki/display/ZFDEV/Tutorial
 *
 * This script performs the minimum tasks needed to load the real ZFDemo bootstrap.
 * Thus, the real bootstrap is not "exposed" in the web server's document root (security).
 */


// CONFIGURE only this line.  All else is in: .../zfdemo/section*/config/*
ZFDemoGrub('section9_filter', 'sandbox'); // load the ZF bootstrap

// absolute real paths are also ok
//ZFDemoGrub('/cygdrive/c/gavin/home/lighttpd/zf/zfdemo/section4_mvc/', 'sandbox');


/**
 * Find and load the real ZFDemo bootstrap.php file
 */
function ZFDemoGrub($installDir, $configEnv)
{
    error_reporting(E_ALL|E_STRICT); // verbose
    $ds = DIRECTORY_SEPARATOR; // too much typing ;)
    
    // where to find this application's configuration (using Conventional Modular Layout)
    if ($installDir[0] === $ds) {
        $tmp = $installDir;
        $appDir = $tmp; // skip costly realpath($tmp)
    } else {
        $tmp = dirname(__FILE__) . $ds . '..' . $ds . '..' . $ds . 'zfdemo' . $ds . $installDir;
        $appDir = realpath($tmp) . $ds;
    }
    
    // show installation help, if loading bootstrap fails
    if ((include $appDir . 'bootstrap.php') === false) {
        $log  = "Looking for application directory in:\n    realpath($tmp" . $ds . ")\n";
        $log .= "    = {$appDir}bootstrap.php\n";
        $file = __FILE__;
        echo <<<EOD
            <html><head><title>ZFDemo - Installation problem</title></head><body>
            <pre>$log</pre>
            <h1>Instructions</h1>
            <p>Check for existence and permissions of the directories above.</p>
            <p>Then edit "\$installDir" in this file ( $file ).</p>
            <p>See also: <a href="http://framework.zend.com/wiki/display/ZFDEV/Tutorial"
            >http://framework.zend.com/wiki/display/ZFDEV/Tutorial</a></p>
            </body></html>
EOD;
        exit;
    }

    if (false === ZFDemo::bootstrap($installDir, $appDir, $configEnv)) { // Run!
        ZFDemo_log::show();
    }
}


/**
 * Hook for SECTION: i18n (translation)
 */
function _($msg)
{
    /////////////////////////////
    // ==> SECTION: mvc <==
    if (func_num_args() === 1) {
        return $msg;
    } else {
        $args = func_get_args();
        array_shift($args);
        return vsprintf($msg, $args);
    }
}
