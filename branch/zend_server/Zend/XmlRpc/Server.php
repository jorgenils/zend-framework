<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to version 1.0 of the Zend Framework
 * license, that is bundled with this package in the file LICENSE, and
 * is available through the world-wide-web at the following URL:
 * http://www.zend.com/license/framework/1_0.txt. If you did not receive
 * a copy of the Zend Framework license and are unable to obtain it
 * through the world-wide-web, please send a note to license@zend.com
 * so we can mail you a copy immediately.
 *
 * @package    Zend_XmlRpc
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */

/**
 * Zend
 */
require_once 'Zend.php';

/**
 * Implement Zend_Server_Interface
 */
require_once 'Zend/Server/Interface.php';

/**
 * Exception this class throws
 */
require_once 'Zend/XmlRpc/Server/Exception.php';

/**
 * XMLRPC Request
 */
require_once 'Zend/XmlRpc/Request.php';

/**
 * XMLRPC Response
 */
require_once 'Zend/XmlRpc/Response.php';

/**
 * XMLRPC HTTP Response
 */
require_once 'Zend/XmlRpc/Response/Http.php';

/**
 * XMLRPC server fault class
 */
require_once 'Zend/XmlRpc/Server/Fault.php';

/**
 * Convert PHP to and from xmlrpc native types
 */
require_once 'Zend/XmlRpc/Value.php';

/**
 * Reflection API for function/method introspection
 */
require_once 'Zend/Server/Reflection.php';

/**
 * Specifically grab the Zend_Server_Reflection_Method for manually setting up 
 * system.* methods and handling callbacks in {@link loadFunctions()}.
 */
require_once 'Zend/Server/Reflection/Method.php';

/**
 * An XML-RPC server implementation
 *
 * Example:
 * <code>
 * require_once 'Zend/XmlRpc/Server.php';
 * require_once 'Zend/XmlRpc/Server/Cache.php';
 * require_once 'Zend/XmlRpc/Server/Fault.php';
 * require_once 'My/Exception.php';
 * require_once 'My/Fault/Observer.php';
 *
 * // Instantiate server
 * $server = new Zend_XmlRpc_Server();
 *
 * // Allow some exceptions to report as fault responses:
 * Zend_XmlRpc_Server_Fault::attachFaultException('My_Exception');
 * Zend_XmlRpc_Server_Fault::attachObserver('My_Fault_Observer');
 *
 * // Get or build dispatch table:
 * if (!Zend_XmlRpc_Server_Cache::get($filename, $server)) {
 *     require_once 'Some/Service/Class.php';
 *     require_once 'Another/Service/Class.php';
 *
 *     // Attach Some_Service_Class in 'some' namespace
 *     $server->setClass('Some_Service_Class', 'some');
 *
 *     // Attach Another_Service_Class in 'another' namespace; use only static 
 *     // methods
 *     $server->setClass('Another_Service_Class', 'another', false);
 *
 *     // Create dispatch table cache file
 *     Zend_XmlRpc_Server_Cache::save($filename, $server);
 * }
 *
 * $response = $server->handle();
 * echo $response;
 * </code>
 *
 * @package    Zend_XmlRpc
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */
class Zend_XmlRpc_Server
{
    /**
     * Array of dispatchable methods
     * @var array
     */
    protected $_methods = array();

    /**
     * Class to use for responses; defaults to {@link Zend_XmlRpc_Response_Http}
     * @var string 
     */
    protected $_responseClass = 'Zend_XmlRpc_Response_Http';

    /**
     * Constructor
     *
     * Sets encoding to UTF-8 and creates system.* methods.
     *
     * @return void
     */
    public function __construct()
    {
        // Set internal encoding to UTF-8
        iconv_set_encoding('internal_encoding', 'UTF-8');

        // Setup system.* methods
        $system = array(
            'listMethods',
            'methodHelp',
            'methodSignature',
            'multicall'
        );
        foreach ($system as $method) {
            $methodName = 'system.' . $method;
            $reflection = new Zend_Server_Reflection_Method(new ReflectionMethod($this, $method), $methodName);
            $reflection->system = true;
            $this->_methods[$methodName] = $reflection;
        }
    }

    /**
     * Attach a callback as an XMLRPC method
     *
     * Attaches a callback as an XMLRPC method, prefixing the XMLRPC method name 
     * with $namespace, if provided. Reflection is done on the callback's 
     * docblock to create the methodHelp for the XMLRPC method.
     *
     * Additional arguments to pass to the function at dispatch may be passed; 
     * any arguments following the namespace will be aggregated and passed at 
     * dispatch time.
     *
     * @param string|array $function Valid callback
     * @param string $namespace Optional namespace prefix
     * @return void
     * @throws Zend_XmlRpc_Server_Exception
     */
    public function addFunction($function, $namespace = '') 
    {
        if (!is_callable($function)) {
            throw new Zend_XmlRpc_Server_Exception('Unable to attach function or callback; not callable', 611);
        }

        $argv = null;
        if (2 < func_num_args()) {
            $argv = func_get_args();
            $argv = array_slice($argv, 2);
        }

        if (is_string($function)) {
            $this->_methods = array_merge($this->_methods, Zend_Server_Reflection::reflectFunction($function, $argv, $namespace));
        } else {
            $methodName = empty($namespace) ? $function[1] : $namespace . '.' . $function[1];
            $method = new Zend_Server_Reflection_Method(new ReflectionMethod($function[0], $function[1]), $methodName);
            $this->_methods = array_merge($this->_methods, array($method));
        }
    }

    /**
     * Load methods as returned from {@link getFunctions}
     *
     * Typically, you will not use this method; it will be called using the 
     * results pulled from {@link Zend_XmlRpc_Server_Cache::get()}.
     * 
     * @param array $array 
     * @return void
     * @throws Zend_XmlRpc_Server_Exception on invalid input
     */
    public function loadFunctions($array)
    {
        if (!is_array($array)) {
            throw new Zend_XmlRpc_Server_Exception('Unable to load array; not an array', 612);
        }

        foreach ($array as $key => $value) {
            if (!$value instanceof Zend_Server_Reflection_Method) {
                throw new Zend_XmlRpc_Server_Exception('One or more method records are corrupt or otherwise unusable', 613);
            }
        }

        $this->_methods = array_merge($this->_methods, $array);
    }

    /**
     * Do nothing; persistence is handled via {@link Zend_XmlRpc_Server_Cache}
     * 
     * @param mixed $class 
     * @return void
     */
    public function setPersistence($class = null)
    {
    }

    /**
     * Attach class methods as XMLRPC method handlers
     *
     * $class may be either a class name or an object. Reflection is done on the 
     * class or object to determine the available public methods, and each is 
     * attached to the server as an available method; if a $namespace has been 
     * provided, that namespace is used to prefix the XMLRPC method names.
     *
     * Any additional arguments beyond $namespace will be passed to a method at 
     * invocation.
     *
     * @param string|object $class 
     * @param string $namespace Optional
     * @param mixed $argv Optional arguments to pass to methods
     * @return void
     * @throws Zend_XmlRpc_Server_Exception on invalid input
     */
    public function setClass($class, $namespace = '', $argv = null)
    {
        if (is_string($class) && !class_exists($class)) {
            if (!class_exists($class)) {
                throw new Zend_XmlRpc_Server_Exception('Invalid method class', 610);
            }
        }

        $argv = null;
        if (3 < func_num_args()) {
            $argv = func_get_args();
            $argv = array_slice($argv, 3);
        }

        $this->_methods = array_merge($this->_methods, Zend_Server_Reflection::reflectClass($class, $argv, $namespace));
    }

    /**
     * Raise an xmlrpc server fault
     * 
     * @param string|Exception $fault 
     * @param int $code 
     * @return Zend_XmlRpc_Server_Fault
     */
    public function fault($fault, $code = 404)
    {
        if (!$fault instanceof Exception) {
            $fault = (string) $fault;
            $fault = new Zend_XmlRpc_Server_Exception($fault, $code);
        }

        return Zend_XmlRpc_Server_Fault::getInstance($fault);
    }

    /**
     * Handle an xmlrpc call (actual work)
     *
     * @todo use fault() for the fault response...
     * @todo Determine how to get current signature being invoked, and use the 
     * return type from the signature to hint the return value type
     * @param Zend_XmlRpc_Request $request
     * @return Zend_XmlRpc_Response|Zend_XmlRpc_Fault
     */
    protected function _handle(Zend_XmlRpc_Request $request) 
    {
        $method = $request->getMethod();

        // Check for valid method
        if (!isset($this->_methods[$method])) {
            throw new Zend_XmlRpc_Server_Exception('Method does not exist', 620);
        }

        $info     = $this->_methods[$method];
        $params   = $request->getParams();
        $argv     = $info->getInvokeArguments();
        if (0 < count($argv)) {
            $args = array_merge($params, $argv);
        }
        switch ($info->getCallbackType()) {
            case 'function':
                return $info->invokeArgs($args);
            case 'method':
                // System methods
                if ($info->system) {
                    $return = $info->invokeArgs($this, $args);
                    break;
                }

                // Get class
                $class = $info->getDeclaringClass()->getName();

                if ('static' == $info->isStatic()) {
                    // for some reason, invokeArgs() does not work the same as 
                    // invoke(), and expects the first argument to be an object. 
                    // So, using a callback if the method is static.
                    $return = call_user_func_array(array($class, $info->getFunctionName()), $args);
                    break;
                }

                // Object methods
                try {
                    $object = $info->getDeclaringClass()->newInstance();
                } catch (Exception $e) {
                    throw new Zend_XmlRpc_Server_Exception('Error instantiating class ' . $class . ' to invoke method ' . $info->getName(), 621);
                }

                $return = $info->invokeArgs($object, $args);
            default:
                throw new Zend_XmlRpc_Server_Exception('Method missing implementation', 622);
                break;
        }

        $response = new ReflectionClass($this->_responseClass);
        return $response->newInstance($return);
    }

    /**
     * Handle an xmlrpc call
     *
     * @param Zend_XmlRpc_Request $request Optional
     * @return Zend_XmlRpc_Response|Zend_XmlRpc_Fault
     */
    public function handle(Zend_XmlRpc_Request $request = null) 
    {
        // Get request
        if (null === $request) {
            require_once 'Zend/XmlRpc/Request/Http.php';
            $request = new Zend_XmlRpc_Request_Http();
        }

        if ($request->isFault()) {
            $response = $request->getFault();
        } else {
            try {
                $response = $this->_handle($request);
            } catch (Exception $e) {
                $response = $this->fault($e);
            }
        }

        return $response;
    }

    /**
     * Set the class to use for the response
     * 
     * @param string $class 
     * @return boolean True if class was set, false if not
     */
    public function setResponseClass($class)
    {
        if (class_exists($class)) {
            $reflection = new ReflectionClass($class);
            if ($reflection->isSubclassOf(new ReflectionClass('Zend_XmlRpc_Response'))) {
                $this->_responseClass = $class;
                return true;
            }
        }

        return false;
    }

    /**
     * Returns a list of registered methods
     *
     * Returns an associative array of method name => 
     * Zend_Server_Reflection_Method objects.
     * 
     * @return array
     */
    public function getFunctions()
    {
        return $this->_methods;
    }

    /**
     * List all available XMLRPC methods
     *
     * Returns an array of methods.
     * 
     * @return array
     */
    public function listMethods()
    {
        return array_keys($this->_methods);
    }

    /**
     * Display help message for an XMLRPC method
     * 
     * @param string $method
     * @return string
     */
    public function methodHelp($method)
    {
        if (!isset($this->_methods[$method])) {
            throw new Zend_Server_Exception('Method "' . $method . '"does not exist', 640);
        }

        return $this->_methods[$method]->getDescription();
    }

    /**
     * Return a method signature
     * 
     * @param string $method
     * @return array
     */
    public function methodSignature($method)
    {
        if (!isset($this->_methods[$method])) {
            throw new Zend_Server_Exception('Method "' . $method . '"does not exist', 640);
        }
        $prototypes = $this->_methods[$method]->getPrototypes();

        $signatures = array();
        foreach ($prototypes as $prototype) {
            $signature = array($prototype->getReturnType());
            foreach ($prototype->getParameters() as $parameter) {
                $signature[] = $parameter->getType();
            }
            $signatures[] = $signature;
        }

        return $signatures;
    }

    /**
     * Multicall - boxcar feature of XML-RPC for calling multiple methods
     * in a single request.
     *
     * Expects a an array of structs representing method calls, each element
     * having the keys:
     * - methodName
     * - params
     *
     * Returns an array of responses, one for each method called, with the value
     * returned by the method. If an error occurs for a given method, returns a
     * struct with a fault response.
     *
     * @see http://www.xmlrpc.com/discuss/msgReader$1208
     * @param array
     * @return array
     */
    public function multicall($methods) 
    {
        $responses = array();
        foreach ($methods as $method) {
            $fault = false;
            if (!is_array($method)) {
                $fault = $this->fault('system.multicall expects each method to be a struct', 601);
            } elseif (!isset($method['methodName'])) {
                $fault = $this->fault('Missing methodName', 602);
            } elseif (!isset($method['params'])) {
                $fault = $this->fault('Missing params', 603);
            } elseif (!is_array($method['params'])) {
                $fault = $this->fault('Params must be an array', 604);
            } else {
                if ('system.multicall' == $method['methodName']) {
                    // don't allow recursive calls to multicall
                    $fault = $this->fault('Recursive system.multicall forbidden', 605);
                }
            }

            if (!$fault) {
                try {
                    $request = new Zend_XmlRpc_Request();
                    $request->setMethod = $method['methodName'];
                    $request->setParams = $method['params'];
                    $response = $this->_handle($request);
                    $responses[] = array($response->getReturnValue());
                } catch (Exception $e) {
                    $fault = $this->fault($e);
                }
            }

            if ($fault) {
                $responses[] = array(
                    'faultCode'   => $fault->getCode(),
                    'faultString' => $fault->getMessage()
                );
            }
        }

        return $responses;
    }
}
