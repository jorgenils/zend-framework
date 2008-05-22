<?php
/*************************************************************
 *
 * Zend Framework 1.0
 * Basic MVC/Database example application
 *
 * Copyright (c) 2005-2007 Zend Technologies USA Inc.
 * http://www.zend.com
 *************************************************************/

error_reporting( E_ALL | E_STRICT );

/*
 * Register an autoload() callback.  This is optional but very handy.
 */
require_once 'Zend/Loader.php';
Zend_Loader::registerAutoload();

/*
 * Make a local variable to the path of the rest of the application.
 */
$appDir = dirname(dirname(__FILE__)) . '/app';

/*
 * It's helpful to add your models directory to the include_path.
 * Also, we assume that you already have the Zend Framework library
 * directory in your include_path.
 */
set_include_path(
    $appDir . '/models'
    . PATH_SEPARATOR
    . get_include_path()
);

/*
 * Most applications have some config data.  Load an "ini"-format configuration 
 * file and save the data in the Registry for later use.  Config files are also 
 * optional; Zend Framework requires no config files.
 */
$config = new Zend_Config_Ini("$appDir/etc/config.ini", 'main');
Zend_Registry::set('config', $config);

/*
 * This application uses a database, and our config file contains some 
 * parameters to connect to the database.  Once we create this database 
 * adapter, save it in the registry and also tell the table class where 
 * to find it.
 */
$db = Zend_Db::factory(
    $config->database->adapter,
    $config->database->params->toArray()
);
/*
 * The database we're using has utf-8 characters, so encode them properly
 */
$db->query('SET NAMES utf8');

/*
 * Save this database connection in a place where other classes can find it.
 */
Zend_Registry::set('defaultDb', $db);
Zend_Db_Table::setDefaultAdapter('defaultDb');

/*
 * Most applications have a single controller directory
 * and require no other configuration of the Front Controller.
 * Use this static run() convenience method.
 * It is considered good practice to place application files
 * outside the Apache document root, but at least this index.php
 * file needs to be here.
 */
Zend_Controller_Front::getInstance()->setBaseUrl('/zfgrid');
Zend_Controller_Front::getInstance()->throwExceptions(true);
Zend_Controller_Front::run("$appDir/controllers");

/*
 * That's all!  The rest is license and copyright stuff.
 * Be sure to look at .htaccess in this directory,
 * and then go look at app/controllers/IndexController.php.
 */


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
 * @package    UnitTests
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: TestHelper.php 4528 2007-04-17 23:10:47Z darby $
 */
