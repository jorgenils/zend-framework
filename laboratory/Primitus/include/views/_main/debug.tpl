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
<hr>
<font size="3">Request Debugging Information</font>
<br>
<br>
<b>Primary Controller:</b> {$debug.firstAction->getControllerName()|default:"<font color='red'><b>Primary Controller Not Found</b></font>"}<br>
<b>Primary Action:</b> {$debug.firstAction->getActionName()|default:"<font color='red'><b>Primary Action Not Found</b></font>"}<br>
<br>
<b>Executed Controller Chain:</b>
{foreach item=action from=$debug.executedActions}
->{$action.controller}::{$action.action}
{foreachelse}
<font color="red"><b>Action List not found</b></font>
{/foreach}
