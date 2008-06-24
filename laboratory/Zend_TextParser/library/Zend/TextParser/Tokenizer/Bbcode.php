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
 * @category   Zend
 * @package    Zend_TextParser
 * @subpackage Tokenizer
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_TextParser_Tokenizer_Bbcode implements Zend_TextParser_Tokenizer_Interface 
{
    
    /**
     * The tag start
     * 
     * @var string
     */
    protected $_tagStart = '[';
    
    /**
     * The end of a tag
     * 
     * @var string
     */
    protected $_tagEnd = ']';
    
    /**
     * The tokenized value
     * 
     * @var array
     */
    protected $_tokenized;
    
    /**
     * The value to be tokenized
     * 
     * @var string
     */
    protected $_value;
    
    /**
     * The lenght of the value
     * 
     * @var int
     */
    protected $_valueLen;
    
    /**
     * The text pointer
     * 
     * @var int
     */
    protected $_pointer;
    
    
    /**
     * Convert newlines
     * 
     * Intherhited from {@link Zend_TextParser_Tokenizer_Interface::getNewlineTag()}
     * 
     * @see Zend_TextParser_Tokenizer_Interface::getNewlineTag()
     * 
     * @return string
     */
    public function getNewlineTag()
    {
        return '[newline]';
    }
    
    /**
     * Tokenize a value
     * 
     * Intherhited from {@link Zend_TextParser_Tokenizer_Interface::tokenize()}
     *
     * @see Zend_TextParser_Tokenizer_Interface::tokenize()
     * 
     * @param string $value
     *
     * @return array
     */ 
    public function tokenize($value)
    {
        $this->_tokenized = array();
        $this->_pointer   = 0;
        $this->_value     = $value;
        $this->_valueLen  = strlen($this->_value);
        
        while ($this->_pointer < $this->_valueLen) {
            // get the start position
            $start = strpos($this->_value, $this->_tagStart, $this->_pointer);
            
            if ($start === false) {
                $this->_tokenized[] = array('tag' => substr($this->_value, $this->_pointer), 'istag' => false);
                
                return $this->_tokenized;
            }
            
            $info = array(
                'tag'      => $this->_tagStart,
                'istag'    => true,
                'stoppers' => array('[/]'),
                'name'     => '',
                'attributes'    => array()
            );
            
            $pre = substr($this->_value, $this->_pointer, $start - $this->_pointer);
            
            // get the tag name
            
            $len = strcspn($this->_value, ' =' . $this->_tagEnd, $start);
            
            $info['name'] = substr($this->_value, $start + 1, $len - 1);
            
            $info['tag'] .= $info['name'];
            
            if ($info['name'][0] == '/') {
                $info['stoppers'] = false;
                $info['name']     = substr($info['name'], 1);
            }
            
            $this->_pointer = $len + $start;
            
            if (preg_match('#[^a-zA-Z0-9_]#i', $info['name'])) {
                $add = $pre . $info['tag'];
                
                $this->_tokenized[] = array('tag' => $add, 'istag' => false);
                
                continue;
            }
            
            $info['name'] = strtolower($info['name']);
            
            if ($info['stoppers'] === false) {
				$info['tag'] .= ($this->_valueLen > $this->_pointer) ? $this->_value[$this->_pointer] : '';
			    
                if (($this->_valueLen > $this->_pointer) && ($this->_value[$this->_pointer] == $this->_tagEnd)) {
                    if (!empty($pre)) {
                        $this->_tokenized[] = array('tag' => $pre, 'istag' => false);
                    }
                    
                    $this->_pointer++;
                    
                    $this->_tokenized[] = $info;
                } else {
                    $this->_tokenized[] = array('tag' => $pre . $info['tag'], 'istag' => false);
                }
                continue;
            }
            
            $info['stoppers'][] = $this->_tagStart . '/' . $info['name'] . $this->_tagEnd;
            
            // check the next token
            switch ($this->_value[$this->_pointer]) {
                case $this->_tagEnd:
                    // we are on the end of the tag, add it
                    if (!empty($pre)) {
                        $this->_tokenized[] = array('tag' => $pre, 'istag' => false);
                    }
                    $info['tag'] .= $this->_tagEnd;
                    $this->_tokenized[] = $info;
                    
                    $this->_pointer++;
                    continue 2;
                case '=':
                    $this->_pointer++;
                    $value = $this->_getValue();
                    
                    if ($value['value'] !== false) {
                        $info['attributes'][$info['name']] = $value['value'];
                    }
                    
                    $info['tag'] .= '=' . $value['total'];
                    break;
                case ' ':
                    $info['tag'] .= ' ';
                    break;
                default:
                    echo $this->_value[$this->_pointer];
                    exit();
            }
            
            while ($char = $this->_nextToken()) {
                switch ($char) {
                    case $this->_tagEnd:
                        if (!empty($pre)) {
                            $this->_tokenized[] = array('tag' => $pre, 'istag' => false);
                        }
                        
                        $this->_pointer++;
                        
                        $info['tag']       .= $this->_tagEnd;
                        $this->_tokenized[] = $info;
                        break 2;
                    case ' ':
                        $info['tag'] .= ' ';
                        break;
                    default:
                        // go to a '='
                        $len = strcspn($this->_value, '=', $this->_pointer);
                        
                        $name = substr($this->_value, $this->_pointer, $len);
                        
                        $this->_pointer = $this->_pointer + $len + 1;
                        
                        if (!preg_match('#[^a-zA-Z0-9_]#i', $name)) {
                            // go search the value
                            $value = $this->_getValue();
                            
                            if ($value['value'] !== false) {
                                $info['attributes'][$name] = $value['value'];
                            }
                            
                            $info['tag'] .= $name . '=' . $value['total'];
                        }
                        // it isn't a tag
                        break;
                }
            }
        }
        
        return $this->_tokenized;
    }
    
    /**
     * Get the next token
     * 
     * @return string
     */
    protected function _nextToken()
    {
        return ($this->_valueLen > $this->_pointer) ? $this->_value[++$this->_pointer] : false;
    }
    
    /**
     * Try to get a value
     * 
     * @return string
     */
    protected function _getValue()
    {
        $char = $this->_value[$this->_pointer];
        
        switch ($char) {
            case '"':
            case "'":
                $len = strcspn($this->_value, $char, $this->_pointer + 1);
                
                $val = substr($this->_value, $this->_pointer + 1, $len);
                
                $return = array(
                    'total' => $char . $val . $char,
                    'value' => $val
                );
                
                $this->_pointer = $this->_pointer + $len + 1;
                
                return $return;
                break;
            default:
                if ($char == $this->_tagEnd) {
                    $this->_pointer--;
                    return array('total' => '', 'value' => false);
                }
                // go to a space or the end of the tag
                $len = strcspn($this->_value, ' ' . $this->_tagEnd, $this->_pointer + 1);
                
                $val = $char . substr($this->_value, $this->_pointer + 1, $len);
                
                $return = array(
                    'total' => $val,
                    'value' => $val
                );
                
                $this->_pointer = $this->_pointer + $len;
                
                return $return;
                break;
        }
    }
} 