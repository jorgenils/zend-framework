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
 * @see Zend_TextParser_Tokenizer_Interface
 */
require_once 'Zend/TextParser/Tokenizer/Interface.php';

/**
 * The MediaWiki tokenizer
 * 
 * @category   Zend
 * @package    Zend_TextParser
 * @subpackage Tokenizer
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_TextParser_Tokenizer_MediaWiki implements Zend_TextParser_Tokenizer_Interface {
    
    /**
     * The tokenized array
     * 
     * @var array
     */
    protected $_tokenized = array();
    
    /**
     * The pointer
     * 
     * @var int
     */
    protected $_pointer;
    
    /**
     * The value to be tokenized
     * 
     * @var string
     */
    protected $_value;
    
    /**
     * The lenght of the {@see $_value}
     * 
     * @var int
     */
    protected $_valueLen;
    
    /**
     * Was the last element a newline?
     * 
     * @var bool
     */
    protected $_newline = false;
    
    /**
     * The temporary buffer
     * 
     * @var string
     */
    protected $_buffer = '';
    
    /**
	 * Get the newline tag
	 * 
	 * @return string 
	 * 
	 * @see Zend_TextParser_Tokenizer_Interface::getNewlineTag()
     */
    public function getNewlineTag()
    {
        return '<newline>';
    }
    
    /**
     * Tokenize a value
     * 
     * @param string $value
     *  
     * @return array 
     * 
     * @see Zend_TextParser_Tokenizer_Interface::tokenize()
     */
    public function tokenize($value)
    {
        $this->_tokenized = array();
        $this->_pointer   = 0;
        $this->_value     = $value;
        $this->_valueLen  = strlen($this->_value);
        $this->_newline   = false;
        $this->_buffer    = '';
        
        while ($char = $this->_nextToken()) {
            switch ($char) {
                case "'":
                    // bold and italic
                    $this->_parseItalicBold();
                    break;
                case '<':
                    // TODO: HTML tags
                    break;
                case '[':
                    // links
                    $this->_parseLinks();
                    break;
                case '=':
                    // headings
                    $this->_parseHeadings();
                    break;
                case '~':
                    // TODO: provide a hook for signatures
                    break;
                case '*':
                case '#':
                case ';':
                case ':':
                    if ($this->_newline) {
                        // TODO: lists and definition lists
                        
                        break;
                    }
                    // break intentionally omitted
                default:
                    // speed hack
                    $len = strcspn($this->_value, "'<[=~", $this->_pointer);
                    
                    $this->_buffer .= substr($this->_value, $this->_pointer - 1, $len + 1);
                    
                    $this->_pointer += $len;
                    
                    break;
            }
        }
        
        if (!empty($this->_buffer)) {
            $this->_tokenized[] = array('tag' => $this->_buffer, 'istag' => false);
        }
        
        return $this->_tokenized;
    }
    
    /**
     * Parse links
     * 
     * @return void
     */
    protected function _parseLinks()
    {
        if ($char = $this->_nextToken()) {
            switch ($char) {
                case '[':
                    // two braces
                    
                    // TODO: provide a hook for a 'title' tag
                    $this->_buffer .= '[[';
                    break;
                default:
                    // just one brace, find the second
                    $len = strpos($this->_value, ']', $this->_pointer);
                    
                    $text = '[' . substr($this->_value, $this->_pointer - 1, $len);
                    
                    if (strpos($text, ' ') !== false) {
                        $url = substr(strtok($text, ' '), 1);
                        
                        $name = substr($text, strlen($url) + 2, -1);
                        
                        $this->_tokenized[] = array(
                            'tag'      => '[' . $url . ' ',
                            'istag'    => true,
                            'stoppers' => array(']'),
                            'name'     => 'url',
                            'attributes'    => array(
                                'url' => $url
                            )
                        );
                        
                        $this->_tokenized[] = array('tag' => $name, 'istag' => false);
                        
                        $this->_tokenized[] = array(
                            'tag'      => ']',
                            'istag'    => true,
                            'stoppers' => false,
                            'name'     => 'url',
                            'attributes'    => array()
                        );
                    } else {
                        $this->_tokenized[] = array(
                            'tag'      => '[',
                            'istag'    => true,
                            'stoppers' => array(']'),
                            'name'     => 'url',
                            'attributes'    => array()
                        );
                        
                        $this->_tokenized[] = array('tag' => substr($text, 1, -1), 'istag' => 'false');
                        
                        $this->_tokenized[] = array(
                            'tag'      => ']',
                            'istag'    => true,
							'stoppers' => false,
                            'name'     => 'url',
                            'attributes'    => array()
                        );
                    }
                    
                    $this->_pointer += $len;
                    break;
            }
        }
    }
    
    /**
     * Parse headings
     * 
     * @return void
     */
    protected function _parseHeadings()
    {
        $num = 1;
        
        while (true) {
            $char = $this->_nextToken();
            
            if ($char == '=') {
                $num++;
            } else {
                switch ($num) {
                    case 1:
                        $this->_buffer .= '=' . $char;
                        
                        return;
                        break;
                    case 2:
                        if (!empty($this->_buffer)) {
                            $this->_tokenized[] = array('tag' => $this->_buffer, 'istag' => false);
                        }
                        
                        $this->_tokenized[] = array(
                            'tag'      => '==',
                            'istag'    => true,
                            'stoppers' => array('=='),
                            'name'     => 'h1',
                            'attributes'    => array()
                        );
                        break;
                    case 3:
                        if (!empty($this->_buffer)) {
                            $this->_tokenized[] = array('tag' => $this->_buffer, 'istag' => false);
                        }
                        
                        $this->_tokenized[] = array(
                            'tag'      => '===',
                            'istag'    => true,
                            'stoppers' => array('==='),
                            'name'     => 'h2',
                            'attributes'    => array()
                        );
                        break;
                    case 4:
                        if (!empty($this->_buffer)) {
                            $this->_tokenized[] = array('tag' => $this->_buffer, 'istag' => false);
                        }
                        
                        $this->_tokenized[] = array(
                            'tag'      => '====',
                            'istag'    => true,
                            'stoppers' => array('===='),
                            'name'     => 'h3',
                            'attributes'    => array()
                        );
                        break;
                    default:
                        if (!empty($this->_buffer)) {
                            $this->_tokenized[] = array('tag' => $this->_buffer, 'istag' => false);
                        }
                        
                        $this->_tokenized[] = array(
                            'tag'      => '====',
                            'istag'    => true,
                            'stoppers' => array('===='),
                            'name'     => 'h3',
                            'attributes'    => array()
                        );
                        
                        $num = $num - 4;
                        
                        for ($i = 0; $i < $num; $i++) {
                            $char = '=' . $char;
                        }
                        break;
                }
                $this->_buffer = $char;
                return;
            }
        }
    }
    
    /**
     * Parse italic and bold tags
     * 
     * @return void
     */
    protected function _parseItalicBold()
    {
        $num = 1;
        
        while (true) {
            $char = $this->_nextToken();
            
            if ($char == "'") {
                $num++;
            } elseif ($num >= 5) {
                if (!empty($this->_buffer)) {
                    $this->_tokenized[] = array('tag' => $this->_buffer, 'istag' => false);
                }
                
                $this->_tokenized[] = array(
                    'tag'      => "'''",
                    'istag'    => true,
                    'stoppers' => array("'''"),
                    'name'     => 'b',
                    'attributes'    => array()
                );
                $this->_tokenized[] = array(
                    'tag'      => "''",
                    'istag'    => true,
                    'stoppers' => array("''"),
                    'name'     => 'i',
                    'attributes'    => array()
                );
                
                if ($num > 5) {
                    $num = $num - 5;
                    
                    for ($i = 0; $i < $num; $i++) {
                        $char = "'" . $char;
                    }
                }
                
                $this->_buffer = $char;
                return;
            } elseif ($num >= 3) {
                if (!empty($this->_buffer)) {
                    $this->_tokenized[] = array('tag' => $this->_buffer, 'istag' => false);
                }
                
                $this->_tokenized[] = array(
                    'tag'      => "'''",
                    'istag'    => true,
                    'stoppers' => array("'''"),
                    'name'     => 'b',
                    'attributes'    => array()
                );
                
                if ($num == 4) {
                    $char = "'" . $char;
                }
                
                $this->_buffer = $char;
                return;
            } elseif ($num == 2) {
                if (!empty($this->_buffer)) {
                    $this->_tokenized[] = array('tag' => $this->_buffer, 'istag' => false);
                }
                
                $this->_tokenized[] = array(
                    'tag'      => "''",
                    'istag'    => true,
                    'stoppers' => array("''"),
                    'name'     => 'i',
                    'attributes'    => array()
                );
                
                $this->_buffer = $char;
                return;
            } else {
                $this->_buffer .= "'" . $char;
                
                return;
            }
        }
    }
    
    /**
     * Get the next token
     * 
     * @return string
     */
    protected function _nextToken()
    {
        return ($this->_valueLen > $this->_pointer) ? $this->_value[$this->_pointer++] : false;
    }
}