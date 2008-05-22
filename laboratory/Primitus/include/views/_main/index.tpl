{*
/**
 * ZFApp
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 *
 * @category   ZFApp
 * @package    Application
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
*}
<HTML>
<HEAD>
<TITLE>{$pageTitle|default:"ZFApp Application Framework"}</TITLE>
</HEAD>
<BODY>
{render module=$__ZFApp_Primary_Module_Name action=$__ZFApp_Primary_Module_Action}
</BODY>
</HTML>