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
 * @version    $Id$
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_TextParser
 */
require_once 'Zend/TextParser.php';
/**
 * @see Zend_TextParser_Renderer_Abstract
 */
require_once 'Zend/TextParser/Renderer/Abstract.php';

/**
 * Helper for easy usage of Zend_TextParser in the view
 *
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_TextParser
{
    
    /**
     * The tokenizer to use
     * 
     * @var string
     */
    protected $_tokenizer = 'bbCode';
    
    /**
     * The renderer to use
     * 
     * @var string
     */
    protected $_renderer = 'html';
    
    /**
     * Zend_TextParser options
     * 
     * @var array
     */
    protected $_options = array();
    
    /**
     * Zend_TextParser instance
     * 
     * @return void
     */
    protected $_textParser;
    
    
    /**
     * View wrapper for Zend_TextParser
     * 
     * @return Zend_View_Helper_TextParser|string
     */
    public function textParser($value = null, $tokenizer = 'bbCode', $renderer = 'html', array $options = array())
    {
        if (is_string($tokenizer)) {
            $this->_tokenizer = $tokenizer;
        }
        if (is_string($renderer)) {
            $this->_renderer = $renderer;
        }
        if (!empty($options)) {
            $this->_options = $options;
        }
        
        if (is_string($value)) {
            return $this->getTextParser()->render($value);
        } else {
            return $this;
        }
    }
    
    /**
     * Set the Zend_TextParser_Renderer_Abstract instance
     * 
     * @param Zend_TextParser_Renderer_Abstract $instance
     * 
     * @return Zend_View_Helper_TextParser
     */
    public function setTextParser(Zend_TextParser_Renderer_Abstract $instance)
    {
        $this->_textParser = $instance;
        
        return $this;
    }
    
    /**
     * Get the Zend_TextParser_Renderer_Abstract instance
     * 
     * @return Zend_TextParser_Renderer_Abstract
     */
    public function getTextParser()
    {
        if (!($this->_textParser instanceof Zend_TextParser_Renderer_Abstract)) {
            $this->_textParser = Zend_TextParser::factory($this->_tokenizer, $this->_renderer, $this->_options);
        }
        
        return $this->_textParser;
    }
}