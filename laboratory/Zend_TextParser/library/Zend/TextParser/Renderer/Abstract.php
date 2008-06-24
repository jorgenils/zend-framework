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
 * @see Zend_Filter
 */
require_once 'Zend/Filter.php';
/**
 * @see Zend_Filter_Interface
 */
require_once 'Zend/Filter/Interface.php';


/**
 * @category   Zend
 * @package    Zend_TextParser
 * @subpackage Renderer
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_TextParser_Renderer_Abstract 
{ 
    
    const TOKENIZER = 'tokenizer';
    const FILTER = 'filter';
    const DEFINE_DEFAULT_TAGS = 'defineDefaultTags';
    
    
    /**
     * The tokenizer
     *
     * @var Zend_TextParser_Tokenizer_Interface
     */ 
    protected $_tokenizer; 
    
    /**
     * The context map
     * 
     * @var array
     */
    protected $_contextMap = array();
    
    /**
     * The current context
     * 
     * @var array
     */
    protected $_context = ':ROOT:';
    
    /**
     * The current tag info
     * 
     * @var array
     */
    protected $_currentTag = array();
    
    /**
     * Tags array for available tags
     * 
     * @var array 
     */
    protected $_tags = array();
    
    /**
     * The filters
     * 
     * @var Zend_Filter
     */
    protected $_filter;
        
    /**
     * The function prefix
     * 
     * @var string
     */
    protected $_functionPrefix = '';
    
    /**
     * True if the current tag is empty
     * 
     * @var bool
     */
    protected $_isEmpty = false;
    
    /**
     * The searched stoppers
     * 
     * @var array
     */
    protected $_searchedStoppers = array();
    
    /**
     * The stack
     * 
     * @var array
     */
    protected $_stack;
    
    /**
     * Stop by a certain tag
     * 
     * @var string
     */
    protected $_stopsBy = '';
    
    
    /**
     * Constructor
     * 
     * @param Zend_TextParser_Tokenizer_Interface $tokenizer
     * 
     * @return void 
     */
    public function __construct(array $config = array())
    {
        if (array_key_exists(self::TOKENIZER, $config)) {
            $this->setTokenizer($config[self::TOKENIZER]);
        }
        $this->_filter = new Zend_Filter();
        
        if (array_key_exists(self::FILTER, $config)) {
            if (is_array($config[self::FILTER])) {
                foreach ($config[self::FILTER] as $filter) {
                	$this->addFilter($filter);
                }
            } else {
                $this->addFilter($config[self::FILTER]);
            }
        }
        
        if (!array_key_exists(self::DEFINE_DEFAULT_TAGS, $config) || $config[self::DEFINE_DEFAULT_TAGS]) {
            $this->_defineDefaultTags();
        }
    }
    
    /**
     * Add a filter
     * 
     * @param Zend_Filter_Interface $filter
     * 
     * @return Zend_TextParser_Renderer_Abstract
     */
    public function addFilter(Zend_Filter_Interface $filter)
    {
        $this->_filter->addFilter($filter);
        
        return $this;
    }
    
    /**
     * Set a tokenizer
     *
     * @param Zend_TextParser_Tokenizer_Interface $tokenizer
     *
     * @return Zend_TextParser_Renderer_Abstract
     */ 
    public function setTokenizer(Zend_TextParser_Tokenizer_Interface $tokenizer) 
    { 
        $this->_tokenizer = $tokenizer; 
        
        return $this; 
    } 
    
    /**
     * Get a tokenizer
     *
     * @return Zend_TextParser_Tokenizer_Interface
     */ 
    public function getTokenizer() 
    { 
        return $this->_tokenizer; 
    } 
    
    /**
     * Add a tag
     *
     * @param string $name
     * @param string $type
     * @param array $parameters
     * @param array $allowedIn
     * @param array $allowsInside
     * 
     * @return Zend_TextParser_Renderer_Abstract
     */
    public function addTag($name, $type, array $parameters, array $allowedIn = array(), array $allowsInside = null)
    {
        switch ($type) {
             case Zend_TextParser::CALLBACK_SINGLE:
                if (!array_key_exists('callback', $parameters)) {
                    /**
                     * @see Zend_TextParser_Renderer_Exception
                     */
                    require_once 'Zend/TextParser/Renderer/Exception.php';
                    throw new Zend_TextParser_Renderer_Exception('There is no callback parameter.');
                }
                if (!is_callable($parameters['callback'])) {
                    /**
                     * @see Zend_TextParser_Renderer_Exception
                     */
                    require_once 'Zend/TextParser/Renderer/Exception.php';
                    throw new Zend_TextParser_Renderer_Exception('The callback parameter must be callable.');
                }
                break;
            case Zend_TextParser::REPLACE_SINGLE:
                if (!array_key_exists('replace', $parameters)) {
                    /**
                     * @see Zend_TextParser_Renderer_Exception
                     */
                    require_once 'Zend/TextParser/Renderer/Exception.php';
                    throw new Zend_TextParser_Renderer_Exception('There is no replace parameter.');
                }
                break;
            case Zend_TextParser::CALLBACK:
                if (!array_key_exists('callback', $parameters)) {
                    /**
                     * @see Zend_TextParser_Renderer_Exception
                     */
                    require_once 'Zend/TextParser/Renderer/Exception.php';
                    throw new Zend_TextParser_Renderer_Exception('There is no callback parameter.');
                }
                if (!is_callable($parameters['callback'])) {
                    /**
                     * @see Zend_TextParser_Renderer_Exception
                     */
                    require_once 'Zend/TextParser/Renderer/Exception.php';
                    throw new Zend_TextParser_Renderer_Exception('The callback parameter must be callable.');
                }
                break;
            default:
                $type = Zend_TextParser::REPLACE;
                if (!array_key_exists('start', $parameters) || !array_key_exists('end', $parameters)) {
                    /**
                     * @see Zend_TextParser_Renderer_Exception
                     */
                    require_once 'Zend/TextParser/Renderer/Exception.php';
                    throw new Zend_TextParser_Renderer_Exception('There are no replace parameters found.');
                }
                break;
        }
        if (!is_string($name)) {
            /**
             * @see Zend_TextParser_Renderer_Exception
             */
            require_once 'Zend/TextParser/Renderer/Exception.php';
            throw new Zend_TextParser_Renderer_Exception('The tag\'s name should be a string.');
        }
        
        foreach ($allowedIn as $allow) {
        	if (!is_string($allow) || !isset($this->_contextMap[$allow]) || !is_array($this->_contextMap[$allow])) {
        	    continue;
        	}
        	
        	$this->_contextMap[$allow][] = $name;
        }
        
        if (is_array($allowsInside)) {
            $this->_contextMap[$name] = $allowsInside;
        }
        
        $this->_tags[$name] = array(
            'name'       => $name,
            'type'       => $type,
            'parameters' => $parameters
        );
        
        return $this;
    }
    
    /**
     * Check if a tag is allowed in the current position
     * 
     * @param string $name
     * 
     * @return bool
     */
    protected function _isAllowed($name)
    {
        if (empty($this->_context) || !array_key_exists($this->_context, $this->_contextMap)) {
            return true;
        }
        
        $context = $this->_contextMap[$this->_context];
        
        if (!is_array($context)) {
            return true;
        }
        
        if (in_array($name, $context)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if a piece is a tag
     * 
     * @param string $piece
     * 
     * @return bool|string
     */
    protected function _isTag($piece)
    {
        if (!$piece['istag'] || !$piece['stoppers']) {
            return false;
        }
        
        if (!$this->_isAllowed($piece['name'])) {
            return false;
        }
        
        if (!array_key_exists($piece['name'], $this->_tags)) {
            return false;
        }
        
        return $this->_tags[$piece['name']];
    }
    
    /**
     * Prepare the stoppers
     * 
     * @param string|array $stoppers
     * 
     * @return stoppers
     */
    protected function _prepareStoppers($stoppers = array())
    {
        if (is_string($stoppers)) {
            $stoppers = (array) $stoppers;
        } elseif (!is_array($stoppers)) {
            /**
             * @see Zend_TextParser_Renderer_Exception
             */
            require_once 'Zend/TextParser/Renderer/Exception.php';
            throw new Zend_TextParser_Renderer_Exception('The stoppers should be in an array or string.');
        }
        
        $stoppers = array_flip($stoppers);
        
        // get all the stoppers, and assign them to the searched stoppers
        foreach ($stoppers as $stopper => $num) {
            if (array_key_exists($stopper, $this->_searchedStoppers)) {
                $stoppers[$stopper] = $this->_searchedStoppers[$stopper];
                
                $this->_searchedStoppers[$stopper]++;
            } else {
                $stoppers[$stopper] = 0;
                
                $this->_searchedStoppers[$stopper] = 1;
            }
        }
        
        return $stoppers;
    }
    
    /**
     * Execute a tag
     * 
     * @param array $customInfo
     * @param array $info
     * 
     * @return string
     */
    protected function _execute(array $customInfo, array $info)
    {
        switch ($customInfo['type']) {
            case Zend_TextParser::REPLACE:
                return $customInfo['parameters']['start']
                     . $this->_parse($info['stoppers'])
                     . $customInfo['parameters']['end'];
                break;
            case Zend_TextParser::REPLACE_SINGLE:
                return $customInfo['parameters']['replace'];
                break;
            case Zend_TextParser::CALLBACK:
                return call_user_func_array($customInfo['parameters']['callback'], array(
                    $this->_parse($info['stoppers']),
                    $info
                ));
                break;
            case Zend_TextParser::CALLBACK_SINGLE:
                return call_user_func_array($customInfo['parameters']['callback'], array(
                    $info
                ));
            default:
                /**
                 * @see Zend_TextParser_Renderer_Exception
                 */
                require_once 'Zend/TextParser/Renderer/Exception.php';
                throw new Zend_TextParser_Renderer_Exception('There are no replace parameters found.');
                break;
        }
    }
    
    /**
     * Parse trough the stack
     * 
     * @param array|string $stoppers
     * 
     * @return string
     */
    protected function _parse($stoppers = array())
    {
        $stoppers = $this->_prepareStoppers($stoppers);
        
        $text = '';
        
        while ($piece = array_shift($this->_stack)) {
            if (array_key_exists($piece['tag'], $stoppers)) {
                foreach ($stoppers as $stopper => $num) {
                    $this->_searchedStoppers[$stopper]--;
                }
                
                if (empty($text)) {
                    $this->_isEmpty = true;
                }
                
                return $text;
            } elseif (array_key_exists($piece['tag'], $this->_searchedStoppers)
            && ($this->_searchedStoppers[$piece['tag']] > 0)) {
                // make it all correct
                
                $this->_searchedStoppers[$piece['tag']]--;
                
                // add a 'new' tag to the stack
                array_unshift($this->_stack, $this->_currentTag);
                
                $this->_stopsBy = $piece['name'];
                
                if (empty($text)) {
                    $this->_isEmpty = true;
                }
                
                return $text;
            } elseif ($execute = $this->_isTag($piece)) {
                $oldContext = $this->_context;
                $oldTag     = $this->_currentTag;
                
                $this->_context    = $piece['name'];
                $this->_currentTag = $piece;
                
                if (is_array($execute)) {
                    $text .= $this->_execute($execute, $piece);
                } else {
                    $tmp = $this->$execute($piece);
                    
                    if (!$this->_isEmpty) {
                        $text .= $tmp;
                    }
                    
                    $this->_isEmpty = false;
                }
                
                $this->_currentTag = $oldTag;
                $this->_context    = $oldContext;
            } else {
                $text .= ($this->_isAllowed(':FILTER:')) ? $this->_filter->filter($piece['tag']) : $piece['tag'];
            }
            
            if (!empty($this->_stopsBy) && ($this->_stopsBy != $this->_currentTag['name'])) {
                // add a 'new' tag to the stack
                array_unshift($this->_stack, $this->_currentTag);
                
                return $text;
            }
            
            $this->_stopsBy = '';
            
            // check if a stopper is found
            foreach ($stoppers as $stopper => $num) {
                if ($this->_searchedStoppers[$stopper] <= $num) {
                    if (empty($text)) {
                        $this->_isEmpty = true;
                    }
                    
                    return $text;
                }
            }
        }
        
        if (empty($text)) {
            $this->_isEmpty = true;
        }
        
        return $text;
    }
    
    
    /**
     * Define the default tags
     * 
     * @return void
     */
    abstract protected function _defineDefaultTags();
    
    /**
     * Render a value
     *
     * @param string $value
     *
     * @return string
     */ 
    abstract public function render($value);
}