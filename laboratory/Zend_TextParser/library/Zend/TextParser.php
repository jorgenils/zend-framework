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
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Loader_PluginLoader
 */
require_once 'Zend/Loader/PluginLoader.php';

/**
 * @category   Zend
 * @package    Zend_TextParser
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_TextParser
{
    
    // constants for adding tags
    const REPLACE = 'replace';
    const REPLACE_SINGLE = 'replace_single';
    const CALLBACK = 'callback';
    const CALLBACK_SINGLE = 'callback_single';
    
    /**
     * The tokenizer loader
     * 
     * @var Zend_Loader_PluginLoader
     */
    protected static $_tokenizerLoader;
    
    /**
     * The renderer loader
     * 
     * @var Zend_Loader_PluginLoader
     */
    protected static $_rendererLoader;
    
    
    /**
     * Get the tokenizer loader
     * 
     * @return Zend_Loader_PluginLoader
     */
    public static function getTokenizerLoader()
    {
        if (!(self::$_tokenizerLoader instanceof Zend_Loader_PluginLoader)) {
            self::$_tokenizerLoader = new Zend_Loader_PluginLoader(array(
                'Zend_TextParser_Tokenizer' => 'Zend/TextParser/Tokenizer/'
            ));
        }
        
        return self::$_tokenizerLoader;
    }
    
    /**
     * Get the renderer loader
     * 
     * @return Zend_Loader_PluginLoader
     */
    public static function getRendererLoader()
    {
        if (!(self::$_rendererLoader instanceof Zend_Loader_PluginLoader)) {
            self::$_rendererLoader = new Zend_Loader_PluginLoader(array(
                'Zend_TextParser_Renderer' => 'Zend/TextParser/Renderer/'
            ));
        }
        
        return self::$_rendererLoader;
    }
    
    /**
     * Add a tokenizer path
     * 
     * @param string $prefix
     * @param string $path
     * 
     * @return Zend_Loader_PluginLoader
     */
    public static function addTokenizerPath($prefix, $path)
    {
        return self::getTokenizerLoader()->addPrefixPath($prefix, $path);
    }
    
    /**
     * Add a renderer path
     * 
     * @param string $prefix
     * @param string $path
     * 
     * @return Zend_Loader_PluginLoader
     */
    public static function addRendererPath($prefix, $path)
    {
        return self::getRendererLoader()->addPrefixPath($prefix, $path);
    }
    
    /**
     * Factory pattern
     * 
     * @return Zend_TextParser_Renderer_Abstract
     */
    public static function factory($tokenizer, $renderer, array $options = array())
    {
        $tokenizerClass = self::getTokenizerLoader()->load($tokenizer); 
        $rendererClass  = self::getRendererLoader()->load($renderer);
        
        $tokenizer = new $tokenizerClass();
        
        $options['tokenizer'] = $tokenizer;
        
        $renderer = new $rendererClass($options);
        
        return $renderer;
    }
}