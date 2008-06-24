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
 * @package    Zend_TextParser
 * @subpackage Tokenizer
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category   Zend
 * @package    Zend_TextParser
 * @subpackage Tokenizer
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_TextParser_Tokenizer_Interface 
{ 
    
    /**
     * Tokenize a value into an array.
     * 
     * A tokenizer should return an array with this form:
     * <code>
     * // generated from '[code=php file="file.php" attr="attr]"]some text[/code]'
     * array(
     *     0 => array(
     *         'tag'      => '[code=php file="file.php" attr="attr]"]',
     *         'istag'    => true,
     *         'stoppers' => array(
     *             '[/]',
     *             '[/code]'
     *         ),
     *         'name'  => 'code',
     *         'attrs' => array(
     *             'code' => 'php',
     *             'file' => 'file.php',
     *             'attr' => 'attr]'
     *         )
     *     ),
     *     1 => array(
     *         'tag'   => 'some text',
     *         'istag' => false
     *     ),
     *     3 => array(
     *         'tag'      => '[/code]',
     *         'istag'    => true,
     *         'stoppers' => false,
     *         'name'     => 'code',
     *         'attrs'    => array()
     *     )
     * );
     * </code>
     *  
     * @param string $value
     *
     * @return array
     */ 
    public function tokenize($value);
    
    /**
     * Get the newline tag
     * 
     * @return string
     */
    public function getNewlineTag();
}