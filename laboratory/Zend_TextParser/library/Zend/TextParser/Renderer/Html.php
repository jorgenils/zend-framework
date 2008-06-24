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
 * @subpackage Renderer
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
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
 * @see Zend_Uri
 */
require_once 'Zend/Uri.php';

/**
 * @category   Zend
 * @package    Zend_TextParser
 * @subpackage Renderer
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_TextParser_Renderer_Html extends Zend_TextParser_Renderer_Abstract
{
    
    /**
     * Render a value
     *
     * @param string $value
     * 
     * @return string
     */
    public function render($value)
    {
        // first convert the newlines
        $value = str_replace(array("\r\n", "\r", "\n"), $this->getTokenizer()->getNewlineTag(), $value);
        
        $this->_stack = $this->getTokenizer()->tokenize($value);
        
        $value = $this->_parse();
        
        return str_replace($this->getTokenizer()->getNewlineTag(), "<br />\n", $value);
    }
    
    /**
     * Define the default tags
     * 
     * @return void
     */
    protected function _defineDefaultTags()
    {
        // add the default tags
        $this->_tags = array_merge(array(
            'b' => array(
                'name'       => 'b',
                'type'       => Zend_TextParser::CALLBACK,
                'parameters' => array('callback' => array($this, 'renderB'))
            ),
            'i' => array(
                'name'       => 'i',
                'type'       => Zend_TextParser::CALLBACK,
                'parameters' => array('callback' => array($this, 'renderI'))
            ),
            'u' => array(
                'name'       => 'u',
                'type'       => Zend_TextParser::CALLBACK,
                'parameters' => array('callback' => array($this, 'renderU'))
            ),
            'code' => array(
                'name'       => 'code',
                'type'       => Zend_TextParser::CALLBACK,
                'parameters' => array('callback' => array($this, 'renderCode'))
            ),
            'url' => array(
                'name'       => 'b',
                'type'       => Zend_TextParser::CALLBACK,
                'parameters' => array('callback' => array($this, 'renderB'))
            )
        ), $this->_tags);
        
        $this->_contextMap = array_merge(array(
            'b'      => array('i', 'u', 's', ':FILTER:'),
            'i'      => array('b', 'u', 's', ':FILTER:'),
            'u'      => array('b', 'i', 's', ':FILTER:'),
            's'      => array('b', 'i', 'u', ':FILTER:'),
            'code'   => array(),
            'quote'  => array('b', 'i', 'u', 's', 'code', ':FILTER:'),
            ':ROOT:' => null
        ), $this->_contextMap);
    }
    
    /**
     * Bold function
     * 
     * @param array $info
     * 
     * @return string
     */
    public function renderB($text, $info)
    {
        return '<strong>' . $text . '</strong>';
    }
    
    /**
     * Italic function
     * 
     * @param array $info
     * 
     * @return string
     */
    public function renderI($text, $info)
    {
        return '<em>' . $text . '</em>';
    }
    
    /**
     * Underline function
     * 
     * @param array $info
     * 
     * @return string
     */
    public function renderU($text, $info)
    {
        return '<span style="text-decoration: underline;">' . $text . '</span>';
    }
    
    /**
     * Code function
     * 
     * @param array $info
     * 
     * @return string
     */
    public function renderCode($text, $info)
    {
        $text = str_replace($this->getTokenizer()->getNewlineTag(), "\n", $text);
        
        $text = highlight_string($text, true);
        
        return $text;
    }
    
    /**
     * Url function
     * 
     * @param array $info
     * 
     * @return string
     */
    public function renderUrl($text, $info)
    {
        // first get the url
        if (array_key_exists('url', $info['attributes'])) {
            $url = $info['attributes']['url'];
        } else {
            $url = $text;
        }
        
        // we only allow valid URL's
        if (!Zend_Uri::check($url)) {
            return $text;
        }
        
        $return = '<a href="' . $url . '"';
        
        if (array_key_exists('title', $info['attributes'])) {
            $return .= ' title="' . $info['attributes']['title'] . '"';
        }
        
        $return .= '>' . $text . '</a>';
        
        return $return;
    }
}