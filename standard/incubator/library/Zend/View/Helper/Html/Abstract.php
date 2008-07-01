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
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zym_View_Helper_Html_Abstract
{
    /**
     * Newline
     */
    const NEWLINE = "\n";

    /**
     * The tag closing bracket
     *
     * @var string
     */
    protected $_closingBracket = null;

    /**
     * Get the tag closing bracket
     *
     * @return string
     */
    public function getClosingBracket()
    {
        if (!$this->_closingBracket) {
            if ($this->_isXhtml()) {
                $this->_closeBracket = ' />';
            } else {
                $this->_closeBracket = '>';
            }
        }

        return $this->_closingBracket;
    }

    /**
     * Is doctype XHTML?
     *
     * @return boolean
     */
    protected function _isXhtml()
    {
        $doctype = $this->getView()->doctype();
        return $doctype->isXhtml();
    }

    /**
     * Converts an associative array to a string of tag attributes.
     *
     * @access public
     *
     * @param array $attribs From this array, each key-value pair is
     * converted to an attribute name and value.
     *
     * @return string The XHTML for the attributes.
     */
    protected function _htmlAttribs(array $attribs)
    {
        $view = $this->getView();

        $xhtml = '';
        foreach ($attribs as $key => $val) {
            $key = $view->escape($key);

            if (is_array($val)) {
                $val = implode(' ', $val);
            } else if ($val === null) {
                continue;
            }

            $val = $view->escape($val);

            $xhtml .= ' ' . $key . '="' . $val . '"'';
        }

        return substr($xhtml, 1);
    }
}