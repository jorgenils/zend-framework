<?php
/**
 * Primitus
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 *
 * @category   Primitus
 * @package    Application
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


require_once 'Primitus/View/Engine.php';

/**
 * The Primitus_View object implements a multiple-singleton pattern. Each
 * controller is given an instance of this object and uses it to set controller-scoped
 * view variables. Ultimately, it passes this data to the Primitus_View_Engine, which
 * actually processes the templates.
 * 
 * @category   Primitus
 * @package    Primitus
 * @subpackage View
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Primitus_View {

    private static $_instance;
	private static $_global_tpl_vars;
	
	public $template_dir  = "%APP_VIEW_DIR%";
    public $compile_dir   = "%APP_VIEW_TMP_DIR%";
    public $config_dir    = "%APP_CFG_DIR%";

    private $_local_tpl_vars;
	private $_scopeName;
	
    function __construct() {
    	$this->_local_tpl_vars = array();
    	self::$_global_tpl_vars = array();
    }

    /**
     * Returns the current scope name
     *
     * @return string The scope name
     */
    public function getScopeName() {
    	return $this->_scopeName;
    }
    
    /**
     * Sets the scope for this object
     *
     * @param string $scopeName The scope name to set
     */
    public function setScopeName($scopeName) {
    	$this->_scopeName = $scopeName;
    }
    
    /**
     * Renders a particular action for this scope, assigning local
     * view variables and global view variables.
     *
     * @param string $action The unqualified action name (i.e. 'noroute') to render
     * @return string The rendered action from the template processing
     */
    public function render($action) {
    	
		$templateFile = $this->getTemplatePathByAction($action);
			
		if(!$templateFile || !file_exists($templateFile)) {
			throw new Primitus_View_Exception("Could not find expected template '$templateFile");
		}
		
    	$engine = new Primitus_View_Engine();
    	
    	foreach(self::$_global_tpl_vars as $key => $value) {
    		$engine->assign($key, $value);
    	} 
    	
    	foreach($this->_local_tpl_vars as $key => $value) {
    		$engine->assign($key, $value);
    	}
    	
    	return $engine->fetch($templateFile);
    }
    
    /**
     * Assign a local-scope view variable. Overwrites any global-scope variables
     *
     * @param mixed Either an array of variable names or a string for a single variable name
     * @param mixed Either an array of variable values (must be same #) or a signle 
     *              variable value
     */
    public function assign($vars, $values) {
    	
    	if(is_array($vars) && !is_array($values)) {
    		throw new Primitus_View_Exception("Cannot assign an array of vars a single value, two arrays must be passed");
    	} else if(is_array($vars) && is_array($values)) {
    		if(count($vars) != count($values)) {
    			throw new Primitus_View_Exception("Variable and Value count mismatch");
    		}
    		
    		reset($vars);
    		reset($values);
    		foreach($vars as $var_name) {
    			$this->_local_tpl_vars[$var_name] = current($values);
    			next($values);
    		}
    	} else if(is_string($vars)) {
    		$is_valid = (bool)preg_match("/^[a-zA-Z_]+\S*/", $vars);
    		
    		if(!$is_valid) {
    			throw new Primitus_View_Exception("View Variables must start with [a-zA-Z_]");
    		}
    		$this->_local_tpl_vars[$vars] = $values;
    	} else {
    		throw new Primitus_View_Exception("View Variables must start with [a-zA-Z_]");
    	}
    }

    /**
     * Assign a global-scope view variable
     *
     * @param mixed Either an array of variable names or a string for a single variable name
     * @param mixed Either an array of variable values (must be same #) or a signle 
     *              variable value
     */
    public function assignGlobal($vars, $values) {
    	
    	if(is_array($vars) && !is_array($values)) {
    		throw new Primitus_View_Exception("Cannot assign an array of vars a single value, two arrays must be passed");
    	} else if(is_array($vars) && is_array($values)) {
    		if(count($vars) != count($values)) {
    			throw new Primitus_View_Exception("Variable and Value count mismatch");
    		}
    		
    		reset($vars);
    		reset($values);
    		foreach($vars as $var_name) {
    			self::$_global_tpl_vars[$var_name] = current($values);
    			next($values);
    		}
    	} else if(is_string($vars)) {
    		$is_valid = (bool)preg_match("/^[a-zA-Z_]+\S*/", $vars);
    		
    		if(!$is_valid) {
    			throw new Primitus_View_Exception("View Variables must start with [a-zA-Z_]");
    		}
    		self::$_global_tpl_vars[$vars] = $values;
    	} else {
    		throw new Primitus_View_Exception("View Variables must start with [a-zA-Z_]");
    	}
    }

    /**
     * Returns an instance of the view for a particular scope
     *
     * @param string $scopeName The qualified controller name 
     * @return Primitus_View the instance of the view for this particular scope
     */
    final static public function getInstance($scopeName) {
        $scopeName = strtolower($scopeName);
	
    	if(!is_array(self::$_instance)) {
    		self::$_instance = array();
    	}
    	
        if(!isset(self::$_instance[$scopeName]) || 
           !self::$_instance[$scopeName] instanceof self) {
           	
            self::$_instance[$scopeName] = new self();
            self::$_instance[$scopeName]->setScopeName($scopeName);
        }

        return self::$_instance[$scopeName];
    }
    
    /**
     * Returns the location of a template (if it exists) for a given actionName
     * and the current scope
     *
     * @param string $action The unqualified action name
     * @return string The path to the template file for this action using the view's scope
     */
    public function getTemplatePathByAction($action) {
       
        if(!$action) {
        	$action = "index";
        }
        
        $template_dir = $this->template_dir;
        $controller = strtolower($this->getScopeName());
        $action = strtolower($action);
        
        if(substr($controller, strlen($controller)-10) == "controller") {
        	$controller = substr($controller, 0, strlen($controller)-10);
        }
        
        if(substr($action, strlen($action)-6) == "action") {
        	$action = substr($action, 0, strlen($action)-6);
        }
        
        $template_file = "$template_dir/$controller/$action.tpl";

        return $template_file;
    }
}
?>
