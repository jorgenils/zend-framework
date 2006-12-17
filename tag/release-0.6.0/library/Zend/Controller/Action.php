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
 * @package    Zend_Controller
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */ 


/** Zend */
require_once 'Zend.php';

/** Zend_Controller_Request_Abstract */
require_once 'Zend/Controller/Request/Abstract.php';

/** Zend_Controller_Response_Abstract */
require_once 'Zend/Controller/Response/Abstract.php';


/**
 * @category   Zend
 * @package    Zend_Controller
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Controller_Action
{
    /**
     * Zend_Controller_Request_Abstract object wrapping the request environment
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request = null;

    /**
     * Zend_Controller_Response_Abstract object wrapping the response 
     * @var Zend_Controller_Response_Abstract
     */
    protected $_response = null;

    /**
     * Array of arguments provided to the constructor, minus the 
     * {@link $_request Request object}.
     * @var array 
     */
    protected $_invokeArgs = array();

    /**
     * Redirect codes (from HTTP specification)
     * @var array
     */
    protected $_redirectCodes = array(
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        307 => 'Temporary Redirect'
    );

    /**
     * Class constructor
     *
     * The request and response objects should be registered with the 
     * controller, as should be any additional optional arguments; these will be 
     * available via {@link getRequest()}, {@link getResponse()}, and 
     * {@link getInvokeArgs()}, respectively.
     *
     * When overriding the constructor, please consider this usage as a best 
     * practice and ensure that each is registered appropriately.
     *
     * Additionally, {@link init()} is called as the final action of 
     * instantiation, and may be safely overridden to perform initialization 
     * tasks; as a general rule, override {@link init()} instead of the 
     * constructor to customize an action controller's instantiation.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs Any additional invocation arguments
     * @return void
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        $this->setRequest($request)
             ->setResponse($response)
             ->_setInvokeArgs($invokeArgs)
             ->init();
    }

    /**
     * Initialize object
     *
     * Called from {@link __construct()} as final step of object instantiation. 
     * 
     * @return void
     */
    public function init()
    {
    }

    /**
     * Return the Request object
     * 
     * @return Zend_Controller_Request_Abstract
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Set the Request object
     * 
     * @param Zend_Controller_Request_Abstract $request 
     * @return Zend_Controller_Action
     */
    public function setRequest(Zend_Controller_Request_Abstract $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Return the Response object
     * 
     * @return Zend_Controller_Response_Abstract
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Set the Response object
     * 
     * @param Zend_Controller_Response_Abstract $response 
     * @return Zend_Controller_Action
     */
    public function setResponse(Zend_Controller_Response_Abstract $response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * Set invocation arguments
     * 
     * @param array $args 
     * @return Zend_Controller_Action
     */
    protected function _setInvokeArgs(array $args = array())
    {
        $this->_invokeArgs = $args;
        return $this;
    }

    /**
     * Return the array of constructor arguments (minus the Request object)
     * 
     * @return array
     */
    public function getInvokeArgs()
    {
        return $this->_invokeArgs;
    }

    /**
     * Return a single invocation argument
     * 
     * @param string $key 
     * @return mixed
     */
    public function getInvokeArg($key)
    {
        if (isset($this->_invokeArgs[$key])) {
            return $this->_invokeArgs[$key];
        }

        return null;
    }

    /**
     * Pre-dispatch routines
     *
     * Called before action method. If using class with 
     * {@link Zend_Controller_Front}, it may modify the 
     * {@link $_request Request object} and reset its dispatched flag in order 
     * to skip processing the current action.
     * 
     * @return void
     */
    public function preDispatch()
    {
    }

    /**
     * Post-dispatch routines
     *
     * Called after action method execution. If using class with 
     * {@link Zend_Controller_Front}, it may modify the 
     * {@link $_request Request object} and reset its dispatched flag in order 
     * to process an additional action.
     *
     * Common usages for postDispatch() include rendering content in a sitewide 
     * template, link url correction, setting headers, etc.
     * 
     * @return void
     */
    public function postDispatch()
    {
    }

    /**
     * Proxy for undefined methods.  Default behavior is to throw an
     * exception on undefined methods, however this function can be
     * overridden to implement magic (dynamic) actions, or provide run-time 
     * dispatching.
     *
     * @param string $methodName
     * @param array $args
     */
    public function __call($methodName, $args)
    {
        if (empty($methodName)) {
            $msg = 'No action specified and no default action has been defined in __call() for '
                 . get_class($this);
        } else {
            $msg = get_class($this) . '::' . $methodName
                 .'() does not exist and was not trapped in __call()';
        }

        throw Zend::exception('Zend_Controller_Exception', $msg);
    }

    /**
     * Call the action specified in the request object, and return a response
     *
     * Not used in the Action Controller implementation, but left for usage in 
     * Page Controller implementations. Dispatches a method based on the 
     * request.
     *
     * Returns a Zend_Controller_Response_Abstract object, instantiating one 
     * prior to execution if none exists in the controller.
     *
     * {@link preDispatch()} is called prior to the action, 
     * {@link postDispatch()} is called following it.
     *
     * @param null|Zend_Controller_Request_Abstract $request Optional request 
     * object to use
     * @param null|Zend_Controller_Response_Abstract $response Optional response 
     * object to use
     * @return Zend_Controller_Response_Abstract
     */
    public function run(Zend_Controller_Request_Abstract $request = null, Zend_Controller_Response_Abstract $response = null)
    {
        if (null !== $request) {
            $this->setRequest($request);
        }

        if (null !== $response) {
            $this->setResponse($response);
        }

        $this->preDispatch();

        $action = $this->getRequest()->getActionName();
        if (null === $action) {
            $action = 'noRoute';
        }
        $action = $action . 'Action';

        $this->{$action}();

        $this->postDispatch();

        return $this->getResponse();
    }

    /**
     * Gets a parameter from the {@link $_request Request object}.  If the
     * parameter does not exist, NULL will be returned.
     *
     * If the parameter does not exist and $default is set, then
     * $default will be returned instead of NULL.
     *
     * @param string $paramName
     * @param mixed $default
     * @return mixed
     */
    final protected function _getParam($paramName, $default = null)
    {
        $value = $this->getRequest()->getParam($paramName);
        if ((null == $value) && (null !== $default)) {
            $value = $default;
        }

        return $value;
    }

    /**
     * Set a parameter in the {@link $_request Request object}.
     * 
     * @param string $paramName 
     * @param mixed $value 
     * @return Zend_Controller_Action
     */
    final protected function _setParam($paramName, $value)
    {
        $this->getRequest()->setParam($paramName, $value);

        return $this;
    }

    /**
     * Determine whether a given parameter exists in the 
     * {@link $_request Request object}.
     * 
     * @param string $paramName 
     * @return boolean
     */
    final protected function _hasParam($paramName)
    {
        return null !== $this->getRequest()->getParam();
    }

    /**
     * Return all parameters in the {@link $_request Request object}
     * as an associative array.
     *
     * @return array
     */
    final protected function _getAllParams()
    {
        return $this->getRequest()->getParams();
    }


    /**
     * Forward to another controller/action.
     *
     * It is important to supply the unformatted names, i.e. "article"
     * rather than "ArticleController".  The dispatcher will do the
     * appropriate formatting when the request is received.
     *
     * @param string $controllerName
     * @param string $actionName
     * @param array $params
     * @return void
     */
    final protected function _forward($controllerName, $actionName, $params=array())
    {
        $this->getRequest()->setParams($params)
            ->setControllerName($controllerName)
            ->setActionName($actionName)
            ->setDispatched(false);
    }


    /**
     * Redirect to another URL
     *
     * Prepends base URL as defined in request object if url is relative by 
     * default; override this behaviour by setting the third argument false.
     *
     * @param string $url
     * @param int $code HTTP response code to use in redirect; defaults to 302, 
     * Found (same as Location header redirect sent by PHP)
     * @param bool $prependBase
     */
    protected function _redirect($url, $code = 302, $prependBase = true)
    {
        if (headers_sent($file, $line)) {
            throw Zend::exception('Zend_Controller_Exception', 'Cannot redirect because headers were already been sent in file ' . $file . ', line ' . $line);
        }

        // prevent header injections
        $url = str_replace(array("\n", "\r"), '', $url);

        // Close session, if started
        if (isset($_SESSION)) {
            session_write_close();
        }

        // Send HTTP response code
        if (302 != $code && array_key_exists($code, $this->_redirectCodes)) {
            // Valid code. 302s are automatically generated by PHP for Location 
            // headers
            header('HTTP/1.1 ' . $code . ' ' . $this->_redirectCodes[$code]);
        } elseif (302 != $code) {
            throw Zend::exception('Zend_Controller_Exception', 'Invalid response code ("' . $code . '") provided');
        }

        // If relative URL, decide if we should prepend base URL
        if ($prependBase && !preg_match('|^[a-z]+://|', $url)) {
            $request = $this->getRequest();
            if ($request instanceof Zend_Controller_Request_Http) {
                $base = $request->getBaseUrl();
                if (('/' != substr($base, -1)) && ('/' != substr($url, 0, 1))) {
                    $url = $base . '/' . $url;
                } else {
                    $url = $base . $url;
                }
            }
        }

        // redirect
        header("Location: $url");
        exit();
    }
}
