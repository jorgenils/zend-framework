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
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id$
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Container for  Dojo View Helper
 *
 * 
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (C) 2008 - Present, Zend Technologies, Inc.
 * @license    New BSD {@link http://framework.zend.com/license/new-bsd}
 */
class Zend_Dojo_View_Helper_Dojo_Container
{ 
    /**
     *  @const string Base path to CDN
     */
    const CDN_BASE = 'http://o.aolcdn.com/dojo/';

    /**
     * @const string Local path to dojo (following versio string)
     */
    const CDN_DOJO_PATH = '/dojo/dojo.xd.js';

    /**
     * @var Zend_View_Interface
     */
    public $view; 

    /**
     * addOnLoad capture lock
     * @var bool
     */
    protected $_captureLock = false;

    /**
     * addOnLoad object on which to apply lambda
     * @var string
     */
    protected $_captureObj;

    /**
     * Dojo configuration
     * @var array
     */
    protected $_djConfig = array();

    /**
     * Whether or not dojo is enabled
     * @var bool
     */
    protected $_enabled = false;

    /**
     * Are we rendering as XHTML?
     * @var bool
     */
    protected $_isXhtml = false;

    /**
     * Relative path to dojo
     * @var string
     */
    protected $_localPath = null;

    /**
     * Root of dojo where all dojo files are installed
     * @var string
     */
    protected $_localRelativePath = null;

    /**
     * Modules to require
     * @var array
     */
    protected $_modules = array();

    /**
     * Registered module paths
     * @var array
     */
    protected $_modulePaths = array();

    /**
     * Actions to perform on window load
     * @var array
     */
    protected $_onLoadActions = array();

    /**
     * Style sheet modules to load
     * @var array
     */
    protected $_stylesheetModules = array();

    /**
     * Local stylesheets
     * @var array
     */
    protected $_stylesheets = array();

    /**
     * Dojo version to use from CDN
     * @var string
     */
    protected $_version = '1.1';


    /**
     * Set view object
     * 
     * @param  Zend_Dojo_View_Interface $view 
     * @return void
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    /**
     * Enable dojo
     * 
     * @return Zend_Dojo_View_Helper_Dojo
     */
    public function enable()
    {
        $this->_enabled = true;
        return $this;
    }

    /**
     * Disable dojo
     * 
     * @return Zend_Dojo_View_Helper_Dojo
     */
    public function disable()
    {
        $this->_enabled = false;
        return $this;
    }

    /**
     * Is dojo enabled?
     * 
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_enabled;
    }
 
    /**
     * Specify a module to require
     * 
     * @param  string $module 
     * @return Zend_Dojo_View_Helper_Dojo
     */
    public function requireModule($module)
    {
        if (!preg_match('/^[a-z][a-z0-9.]+$/i', $module)) {
            require_once 'Zend/Dojo/View/Exception.php';
            throw new Zend_Dojo_View_Exception(sprintf('Module name specified, "%s", contains invalid characters', (string) $module));
        }

        if (!in_array($module, $this->_modules)) {
            $this->_modules[] = $module;
        }

        return $this;
    }

    /**
     * Retrieve list of modules to require
     * 
     * @return array
     */
    public function getModules()
    {
        return $this->_modules;
    }
 
    /**
     * Register a module path
     * 
     * @param  string $path 
     * @return Zend_Dojo_View_Helper_Dojo
     */
    public function registerModulePath($module, $path)
    {
        $path = (string) $path;
        if (!in_array($module, $this->_modulePaths)) {
            $this->_modulePaths[$module] = $path;
        }

        return $this;
    }

    /**
     * List registered module paths
     * 
     * @return array
     */
    public function getModulePaths()
    {
        return $this->_modulePaths;
    }
 
    /**
     * Use CDN, using version specified
     * 
     * @param  string $version 
     * @return Zend_Dojo_View_Helper_Dojo
     */
    public function setCdnVersion($version = null)
    {
        $this->enable();
        if (preg_match('/^[1-9]\.[0-9](\.[0-9])?$/', $version)) {
            $this->_version = $version;
        }
        return $this;
    }
 
    /**
     * Get CDN version
     * 
     * @return string
     */
    public function getCdnVersion()
    {
        return $this->_version;
    }

    /**
     * Are we using the CDN?
     * 
     * @return void
     */
    public function useCdn()
    {
        return !$this->useLocalPath();
    }
 
    /**
     * Set path to local dojo
     * 
     * @param  string $path 
     * @return Zend_Dojo_View_Helper_Dojo
     */
    public function setLocalPath($path)
    {
        $this->enable();
        $this->_localPath = (string) $path;
        return $this;
    }

    /**
     * Get local path to dojo
     * 
     * @return string
     */
    public function getLocalPath()
    {
        return $this->_localPath;
    }

    /**
     * Are we using a local path?
     * 
     * @return bool
     */
    public function useLocalPath()
    {
        return (null === $this->_localPath) ? false : true;
    }
 
    /**
     * Set Dojo configuration
     * 
     * @param  string $option 
     * @param  mixed $value 
     * @return Zend_Dojo_View_Helper_Dojo
     */
    public function setDjConfig(array $config)
    {
        $this->_djConfig = $config;
        return $this;
    }

    /**
     * Set Dojo configuration option
     * 
     * @param  string $option 
     * @param  mixed $value 
     * @return Zend_Dojo_View_Helper_Dojo
     */
    public function setDjConfigOption($option, $value)
    {
        $option = (string) $option;
        $this->_djConfig[$option] = $value;
        return $this;
    }

    /**
     * Retrieve dojo configuration values
     * 
     * @return array
     */
    public function getDjConfig()
    {
        return $this->_djConfig;
    }

    /**
     * Get dojo configuration value
     * 
     * @param  string $option 
     * @param  mixed $default 
     * @return mixed
     */
    public function getDjConfigOption($option, $default = null)
    {
        $option = (string) $option;
        if (array_key_exists($option, $this->_djConfig)) {
            return $this->_djConfig[$option];
        }
        return $default;
    }
 
    /**
     * Add a stylesheet by module name
     * 
     * @param  string $module 
     * @return Zend_Dojo_View_Helper_Dojo
     */
    public function addStylesheetModule($module)
    {
        if (!preg_match('/^[a-z0-9]+\.[a-z0-9]+(\.[a-z0-9]+)*$/', $module)) {
            require_once 'Zend/Dojo/View/Exception.php';
            throw new Zend_Dojo_View_Exception('Invalid stylesheet module specified');
        }
        if (in_array($module, $this->_stylesheetModules)) {
            return $this;
        }
        $this->_stylesheetModules[] = $module;
        return $this;
    }

    /**
     * Get all stylesheet modules currently registered
     * 
     * @return array
     */
    public function getStylesheetModules()
    {
        return $this->_stylesheetModules;
    }
 
    /**
     * Add a stylesheet
     * 
     * @param  string $path 
     * @return Zend_Dojo_View_Helper_Dojo
     */
    public function addStylesheet($path)
    {
        $path = (string) $path;
        if (!in_array($path, $this->_stylesheets)) {
            $this->_stylesheets[] = (string) $path;
        }
        return $this;
    }

    /**
     * Retrieve registered stylesheets
     * 
     * @return array
     */
    public function getStylesheets()
    {
        return $this->_stylesheets;
    }

    /**
     * Add a script to execute onLoad
     *
     * dojo.addOnLoad accepts:
     * - function name
     * - lambda
     * 
     * @param  string $callback Lambda
     * @return Zend_Dojo_View_Helper_Dojo
     */
    public function addOnLoad($callback)
    {
        if (!in_array($callback, $this->_onLoadActions, true)) {
            $this->_onLoadActions[] = $callback;
        }
        return $this;
    }

    /**
     * Retrieve all registered onLoad actions
     * 
     * @return array
     */
    public function getOnLoadActions()
    {
        return $this->_onLoadActions;
    }

    /**
     * Start capturing routines to run onLoad
     * 
     * @return bool
     */
    public function onLoadCaptureStart()
    {
        if ($this->_captureLock) {
            require_once 'Zend/Dojo/View/Exception.php';
            throw new Zend_Dojo_View_Exception('Cannot nest onLoad captures');
        }

        $this->_captureLock = true;
        return ob_start();
    }

    /**
     * Stop capturing routines to run onLoad
     * 
     * @return bool
     */
    public function onLoadCaptureStop()
    {
        $data               = ob_get_clean();
        $this->_captureLock = false;

        $this->addOnLoad($data);
        return true;
    }

    /**
     * String representation of dojo environment
     * 
     * @return string
     */
    public function __toString()
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $this->_isXhtml = $this->view->doctype()->isXhtml();

        if (Zend_Dojo_View_Helper_Dojo::useDeclarative()) {
            $this->setDjConfigOption('parseOnLoad', true);
        }

        $html  = $this->_renderStylesheets() . PHP_EOL
               . $this->_renderDjConfig() . PHP_EOL
               . $this->_renderDojoScriptTag() . PHP_EOL
               . $this->_renderExtras();
        return $html;
    }

    /**
     * Retrieve local path to dojo resources for building relative paths
     * 
     * @return string
     */
    protected function _getLocalRelativePath()
    {
        if (null === $this->_localRelativePath) {
            $localPath = $this->getLocalPath();
            $localPath = preg_replace('|[^/\\\\]dojo[/\\\\]dojo.js[^/\\\\]*$|i', '', $localPath);
            $this->_localRelativePath = $localPath;
        }
        return $this->_localRelativePath;
    }

    /**
     * Render dojo stylesheets
     * 
     * @return string
     */
    protected function _renderStylesheets()
    {
        if ($this->useCdn()) {
            $base = self::CDN_BASE
                  . $this->getCdnVersion();
        } else {
            $base = $this->_getLocalRelativePath();
        }

        $registeredStylesheets = $this->getStylesheetModules();
        foreach ($registeredStylesheets as $stylesheet) {
            $themeName     = substr($stylesheet, strrpos($stylesheet, '.') + 1);
            $stylesheet    = str_replace('.', '/', $stylesheet);
            $stylesheets[] = $base . '/' . $stylesheet . '/' . $themeName . '.css';
        }

        foreach ($this->getStylesheets() as $stylesheet) {
            $stylesheets[] = $stylesheet;
        }

        if (empty($stylesheets)) {
            return '';
        }

        array_reverse($stylesheets);
        $style = '<style type="text/css">' . PHP_EOL
               . (($this->_isXhtml) ? '<!--' : '<!--') . PHP_EOL;
        foreach ($stylesheets as $stylesheet) {
            $style .= '    @import "' . $stylesheet . '";' . PHP_EOL;
        }
        $style .= (($this->_isXhtml) ? '-->' : '-->') . PHP_EOL
                . '</style>';

        return $style;
    }

    /**
     * Render DjConfig values
     * 
     * @return string
     */
    protected function _renderDjConfig()
    {
        $djConfigValues = $this->getDjConfig();
        if (empty($djConfigValues)) {
            return '';
        }

        require_once 'Zend/Json.php';
        $scriptTag = '<script type="text/javascript">' . PHP_EOL
                   . (($this->_isXhtml) ? '//<![CDATA[' : '//<!--') . PHP_EOL
                   . '    var djConfig = ' . Zend_Json::encode($djConfigValues) . ';' . PHP_EOL
                   . (($this->_isXhtml) ? '//]]>' : '//-->') . PHP_EOL
                   . '</script>';

        return $scriptTag;
    }

    /**
     * Render dojo script tag
     *
     * Renders Dojo script tag by utilizing either local path provided or the 
     * CDN. If any djConfig values were set, they will be serialized and passed 
     * with that attribute.
     * 
     * @return string
     */
    protected function _renderDojoScriptTag()
    {
        if ($this->useCdn()) {
            $source = self::CDN_BASE
                    . $this->getCdnVersion()
                    . self::CDN_DOJO_PATH;
        } else {
            $source = $this->getLocalPath();
        }

        $scriptTag = '<script type="text/javascript" src="' . $source . '"></script>';
        return $scriptTag;
    }

    /**
     * Render dojo module paths and requires
     * 
     * @return string
     */
    protected function _renderExtras()
    {
        $js = array();
        $modulePaths = $this->getModulePaths();
        if (!empty($modulePaths)) {
            foreach ($modulePaths as $path) {
                $js[] =  'dojo.registerModulePath("' . $this->view->escape($path) . '");';
            }
        }

        $modules = $this->getModules();
        if (!empty($modules)) {
            foreach ($modules as $module) {
                $js[] = 'dojo.require("' . $this->view->escape($module) . '");';
            }
        }

        $onLoadActions = array();
        foreach ($this->getOnLoadActions() as $callback) {
            $onLoadActions[] = 'dojo.addOnLoad(' . $callback . ');';
        }

        $content = '';
        if (!empty($js)) {
            $content .= implode("\n    ", $js) . "\n";
        }
        if (!empty($onLoadActions)) {
            $content .= implode("\n    ", $onLoadActions) . "\n";
        }

        if (preg_match('/^\s*$/s', $content)) {
            return '';
        }

        $html = '<script type="text/javascript">' . PHP_EOL
              . (($this->_isXhtml) ? '//<![CDATA[' : '//<!--') . PHP_EOL
              . $content
              . (($this->_isXhtml) ? '//]]>' : '//-->') . PHP_EOL
              . PHP_EOL . '</script>';
        return $html;
    }
}
