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

require_once 'Zend/Controller/Action.php';
require_once 'Zend/Filter/Input.php';
require_once 'Primitus/DB.php';
require_once 'Primitus/View.php';
require_once 'Primitus/Controller/Exception.php';

/**
 * The Primitus Base Action Controller. This controller implements many of the niceity
 * methods avaialble in the user-defined controllers such as
 * 
 * Filtered input variables: 
 * 
 * GET(), POST(), etc. methods return a Filtered version of their superglobal 
 * counterparts using Zend_Filter_Input
 * 
 * Database access: 
 * DB() returns the instance of Primitus_DB in use
 * 
 * View Access: 
 * view() returns the instance of the Primitus_View for this particular controller
 * 
 * Nice bad action handling: 
 * Throws an exception in the event a bad controller action is called, resulting
 * in a pretty error page
 * 
 * Implementation of indexAction:
 * This is an important aspect, by implementing indexAction here and throwing an
 * exception, you effectively create the ability of Primitus to have "private" controllers
 * by creating user-space controllers which do not implement indexAction. This prevents
 * unauthorized access to a certain measure
 * 
 * Default render implementation:
 * During the render phase, a controller will be asked to render itself at the end of the
 * request by calling the Controller::render($action) method, where $action is the name
 * of the action being rendered. This default implementation automatically looks for the
 * views/<controllername>/<action>.tpl template and if it exists renders the controller
 * using that. 
 * 
 * Of course, this method can be overwritten to implement a different type of rendering
 * i.e. render() could respond with a javascript request, etc.
 * 
 * Required Controller Parameters:
 * _requiredParam() is a niceity function which attempts to fetch a parameter using
 * the standard _getParam() interface. In the event the parameter didn't exist, an
 * exception is automatically thrown.
 * 
 * @category   Primitus
 * @package    Primitus_Controller_Action
 * @subpackage Base
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Primitus_Controller_Action_Base extends Zend_Controller_Action {

	const CONTENT_TYPE = "text/html";
	
    private $_POST;
    private $_GET;
    private $_COOKIE;
    private $_SERVER;
    private $_ENV;
    private $_REQUEST;
   
    protected $_viewMap = array();

    /**
     * Returns the content type this controller should render as in a view
     *
     * @param Zend_Controller_Dispatcher_Token $action The action to be executed
     * @return string the MIME content type
     */
    public function getContentType(Zend_Controller_Dispatcher_Token $action) {
    	return self::CONTENT_TYPE;
    }
      
    /**
     * Returns the instance of the Primitus_DB() object
     *
     * @return Primitus_DB The database instance
     */
    final protected function DB() {
        return Primitus_DB::getInstance();
    }

    /**
     * Returns the instance of the Primitus_View object for this controller
     *
     * @return Primitus_View The instance of the view for this controller
     */
    final protected function view() {
         return Primitus_View::getInstance(get_class($this));
    }

    /**
     * Wrapper for $_REQUEST using Zend Input Filter
     *
     * @return Zend_Filter_Input The filtered $_REQUEST superglobal
     */
    final protected function REQUEST() {
        if(!$this->_REQUEST instanceof Zend_InputFilter) {
            $this->_REQUEST = new Zend_Filter_Input($_REQUEST, false);
        }
        
        return $this->_REQUEST;
    }
    
    /**
     * Wrapper for $_ENV using Zend Input Filter
     *
     * @return Zend_Filter_Input The filtered $_ENV superglobal
     */
    final protected function ENV() {
        if(!$this->_ENV instanceof Zend_InputFilter) {
            $this->_ENV = new Zend_Filter_Input($_ENV, false);
        }
        
        return $this->_ENV;
    }
    
    /**
     * Wrapper for $_COOKIE using Zend Input Filter
     *
     * @return Zend_Filter_Input The filtered $_COOKIE superglobal
     */
    final protected function COOKIE() {
        if(!$this->_COOKIE instanceof Zend_InputFilter) {
            $this->_COOKIE = new Zend_Filter_Input($_COOKIE, false);
        }
        
        return $this->_COOKIE;
    }
    
    /**
     * Wrapper for $_GET using Zend Input Filter
     *
     * @return Zend_Filter_Input The filtered $_GET superglobal
     */
    final protected function GET() {
        if(!$this->_GET instanceof Zend_InputFilter) {
            $this->_GET = new Zend_Filter_Input($_GET, false);
        }
        return $this->_GET;
    }
    
    /**
     * Wrapper for $_POST using Zend Input Filter
     *
     * @return Zend_Filter_Input The filtered $_POST superglobal
     */
    final protected function POST() {
        if(!$this->_POST instanceof Zend_InputFilter) {
            $this->_POST = new Zend_Filter_Input($_POST, false);
        }
        
        return $this->_POST;
    }

    /**
     * Trapper function for controller actions which don't exist
     *
     * @param string $method The method name which didn't exist
     * @param array $params The parameters passed to that method
     */
    public function __call($method, $params) {
    	$class = get_class($this);
		throw new Primitus_Controller_Exception("No Handler for '$class::$method' could be found");
    }  

    /**
     * Default indexAction() implementation, which throws an exception
     */
    public function indexAction() {
         return $this->__call("indexAction", array());
    }

    /**
     * Default render() implementation, which renders the template automatically
     * based on class/action provided
     *
     * @param string $actionName The qualified action name being rendered
     * @return string The template fully rendered
     */
    public function render($actionName) {
    	$view = Primitus_View::getInstance(get_class($this));
    	
    	if(empty($actionName)) {
    		$actionName = "indexAction";
    	}

    	if(array_key_exists($actionName, $this->_viewMap)) {
    		$actionName = $this->_viewMap[$actionName];
    	}

    	return $view->render($actionName);
    }

    /**
     * Uses _getParam() to return a controller parameter, and makes sure the
     * parameter was passed. In the event it wasn't passed, an error is thrown
     *
     * @param string $paramName The name of the parameter to fetch
     * @return mixed The value of the parameter fetched
     */
    public function _requireParam($paramName) {
    	$retval = $this->_getParam($paramName, new Primitus_Controller_Exception("Required parameter '$paramName' not provided"));
    	
    	if($retval instanceof Primitus_Controller_Exception) {
    		throw $retval;
    	}
    	
    	return $retval;
    }

}

?>
