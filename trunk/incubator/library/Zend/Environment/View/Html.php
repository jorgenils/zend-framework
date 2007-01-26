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
 * @package    Zend_Environment
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Exception.php 2794 2007-01-16 01:29:51Z bkarwin $
 */


/**
 * Zend_View_Abstract
 */
require_once('Zend/View/Abstract.php');


/**
 * @category   Zend
 * @package    Zend_Environment
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Environment_View_Html extends Zend_View_Abstract
{
    /**
     * Subclass the default Zend_View to provide defaults. Since this class has
     * embedded HTML no script name is needed. To render, simply call the
     * render() method with a null parameter
     *
     * E.g. return $view->render(null);
     *
     * @param  array $config
     * @return void
     */
    public function __construct($config = array())
    {
        if (!isset($config['scriptPath'])) {
            $config['scriptPath'] = dirname(__FILE__);
        }

        parent::__construct($config);
    }

    /**
     * The default HTML template iterates through each section within the
     * environment and displays the default fields in a matrix.
     *
     * $zflogo contains a base64 encoded version of the Zend Framework logo
     * so that when this page is rendered it can display inline on browsers
     * that support inline CSS encoded images. Currently Internet Explorer for
     * PC does not support this and subsequently the CSS degrades gracefully.
     *
     * The page validates to XHTML 1.0 Strict
     *
     * TO DO:-
     * - Javascript show/hide sections? Cookie-based prefs?
     * - Summary table at top of page for errors/warnings?
     * - Smarter handling of table columns per module
     * - Better linking of modules to php.net documentation
     *
     * @return string
     */
    protected function _run()
    {
        function toString($val, $padding = 4)
        {
            if (is_string($val) || is_object($val) || empty($val)) {
                return trim("{$val}");
            }

            $array = array();

            foreach ($val as $key => $row) {
                $array[] = $key . ': ' . toString($row);
            }

            return "\n" . trim(join("\n", $array));
        }

        $zfbg = trim(str_replace("\n", "", '
R0lGODlhAQBiALMAABNrjjGIqhB0mhFzmBJvkxFwlRRoihFylxJtkBNqjBB1m/n8/9ru9QAAAAAA
AAAAACH5BAAAAAAALAAAAAABAGIAAAQYkC1Jp6346sy371SgjGRpnsJwFAQCJEYEADs='));

        $zflogo = trim(str_replace("\n", "", '
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
UlWj+FukBY0AAAAASUVORK5CYII%3D'));

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Zend Framework : Zend_Environment display</title>
	<style type="text/css" media="screen">
    body
    {
        font-family: Lucida, Verdana, arial, sans-serif;
        font-size: 0.7em;
        line-height: 135%;
        color: #222;
        margin: 1px 10%;
        background: url(data:image/png;base64,<?php echo $zfbg ?>) repeat-x;
	    _background: url(http://framework.zend.com/images/header_bg.gif) repeat-x;
    }

    table, form
    {
        font-size: 100%;
        width: 100%;
        margin: 2em 0;
    }

    tr
    {
        vertical-align: top;
    }

    th
    {
        text-align: left;
    }

    thead th
    {
        border: 1px solid #D3E0EB;
        padding: 0.5em 1em;
        background-color:#EDF7FF;
	}

	tbody th, tbody td
	{
        border-left: 1px solid #D3E0EB;
        border-bottom: 1px solid #D3E0EB;
        padding: 0.5em 1em;
	}

	.header th
	{
	    font-size: 160%;
        background-color:#D3E0EB;
        color: black;
        font-weight: normal;
        padding: 0.6em;
	}

	#logo
	{
	    background: url(data:image/png;base64,<?php echo $zflogo ?>) no-repeat;
	    padding-top: 66px;
	    color: white;
	    font-size: 120%;
	}
</style>
</head>
<body>
    <h1 id="logo">Zend Environment Info : <?php echo date('jS F, Y') ?></h1>
    <?php

    foreach ($this->environment as $section) {
    ?>
    <?php if ( strtolower($section->getType()) != 'security') : ?>
    <table width="100%" summary="Section - <?php echo $section->getType() ?>">
        <col width="30%" />
        <col width="10%" />
        <col width="20%" />
        <col />
        <thead>
            <tr class="header">
                <th colspan="4"><?php echo ucwords($section->getType()) ?></th>
            </tr>
            <tr>
                <th>Title</th>
                <th>Version</th>
                <th>Value</th>
                <th>Info</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($section as $info) { ?>    <tr>
                <td><?php echo nl2br($this->escape(toString($info->title))) ?></td>
                <td><?php echo nl2br($this->escape(toString($info->version))) ?></td>
                <td><?php echo nl2br($this->escape(toString(join(PATH_SEPARATOR . "\n", explode(PATH_SEPARATOR, $info->value))))) ?></td>
                <td><?php echo nl2br($this->escape(toString($info->info))) ?></td>
            </tr>
        <?php } ?></tbody>
    </table>

    <?php else : // special layout for security section ?>

    <table width="100%" summary="Section - <?php echo $section->getType() ?>">
        <col width="10%" />
        <col width="10%" />
        <col width="10%" />
        <col width="10%" />
        <col width="10%" />
        <col />
        <col width="10%" />
        <thead>
            <tr class="header">
                <th colspan="7"><?php echo ucwords($section->getType()) ?></th>
            </tr>
            <tr>
                <th>Group</th>
                <th>Name</th>
                <th>Result</th>
                <th>Current Value</th>
                <th>Recommended Value</th>
                <th>Details</th>
                <th>More Info</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($section as $info) { ?>    <tr>
                <td><?php echo nl2br($this->escape(toString($info->group))) ?></td>
                <td><?php echo nl2br($this->escape(toString($info->name))) ?></td>
                <td><?php echo nl2br($this->escape(toString($info->result))) ?></td>
                <td><?php echo nl2br($this->escape(toString($info->current_value))) ?></td>
                <td><?php echo nl2br($this->escape(toString($info->recommended_value))) ?></td>
                <td><?php echo nl2br($this->escape(toString($info->details))) ?></td>
                <td><a href="<?php echo nl2br($this->escape(toString($info->link))) ?>">More Info &raquo;</a></td>
            </tr>
        <?php } ?></tbody>
    </table>

    <?php endif; ?>

    <?php

    }

    ?></body>
</html><?php

    }
}
