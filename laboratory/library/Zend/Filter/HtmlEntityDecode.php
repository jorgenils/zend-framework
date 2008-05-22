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
 * @package    Zend_Filter
 * @subpackage Zend_Filter
 * @copyright  2008 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once 'Zend/Filter/Interface.php';

/**
 * Zend Filter HtmlEntityDecode
 *
 * Simple wrapper for html_entity_decode().
 */
class Zend_Filter_HtmlEntityDecode implements Zend_Filter_Interface
{
    /**
     * Corresponds to html_entity_decode's 2nd argument
     *
     * @var int
     */
    public $quoteStyle;

    /**
     * Corresponds to html_entity_decode's 3rd argument
     *
     * @var string
     */
    public $charset;


    /**
     * Constructor
     *
     * @see html_entity_decode()
     *
     * @param int Quote style
     * @param string Char set
     * @returns void
     * @throws none
     */
    public function __construct($quoteStyle = ENT_COMPAT, $charset = 'ISO-8859-1')
    {
        $this->quoteStyle = $quoteStyle;
        $this->charset    = $charset;
    }

    /**
     * Filter
     *
     * @see Zend_Filter_Interface::filter()
     *
     * @param string Which may contain HTML entities
     * @returns string With all HTML entities replaced by real characters
     * @throws none
     */
    public function filter($value)
    {
        return html_entity_decode($value, $this->quoteStyle, $this->charset);
    }
}
