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
<html>
<head>
<title>An error has occurred</title>
</head>
<body>
<table width="100%">
<tr>
<td><img src="/images/zfapp_logo.png" alt="ZFApp"></td>
<td align="right"><i>ZFApp Application Framework built on <a href="http://framework.zend.com/">Zend Framework</a></i></td>
</tr>
</table>
<h2>An Error has Occurred:</h2>
<p>{$error->getMessage()}</p>
<p><b>File:</b> {$error->getFile()}:{$error->getLine()}</b></p>
<p><b>Backtrace:</b><br/>
<code>
<pre>
{$error->getTraceAsString()}
</pre>
</code>
</p>
</body>
</html>