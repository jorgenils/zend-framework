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
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Oauth */
require_once 'Zend/Oauth.php';

/** Zend_Uri */
require_once 'Zend/Uri.php';

/** Zend_Oauth_Http_RequestToken */
require_once 'Zend/Oauth/Http/RequestToken.php';

/** Zend_Oauth_Http_UserAuthorisation */
require_once 'Zend/Oauth/Http/UserAuthorisation.php';

/** Zend_Oauth_Http_AccessToken */
require_once 'Zend/Oauth/Http/AccessToken.php';

/** Zend_Oauth_Token_AuthorisedRequest */
require_once 'Zend/Oauth/Token/AuthorisedRequest.php';

/** Zend_Oauth_Config */
require_once 'Zend/Oauth/Config.php';

/**
 * @category   Zend
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Oauth_Consumer extends Zend_Oauth
{

    protected $_requestToken = null;

    protected $_accessToken = null;

    protected $_config = null;

    public function __construct($options = null)
    {
        $this->_config = new Zend_Oauth_Config;
        if (!is_null($options)) {
            if ($options instanceof Zend_Config) {
                $options = $options->toArray();
            }
            $this->_config->setOptions($options);
        }
    }

    public function getRequestToken(array $customServiceParameters = null,
        $httpMethod = null,
        Zend_Oauth_Http_RequestToken $request = null)
    {
        if (is_null($request)) {
            $request = new Zend_Oauth_Http_RequestToken($this, $customServiceParameters);
        } elseif(!is_null($customServiceParameters)) {
            $request->setParameters($customServiceParameters);
        }
        if (!is_null($httpMethod)) {
            $request->setMethod($httpMethod);
        } else {
            $request->setMethod($this->getRequestMethod());
        }
        $this->_requestToken = $request->execute();
        return $this->_requestToken;
    }

    public function getRedirectUrl(array $customServiceParameters = null,
        Zend_Oauth_Token_Request $token = null,
        Zend_Oauth_Http_UserAuthorisation $redirect = null)
    {
        if (is_null($redirect)) {
            $redirect = new Zend_Oauth_Http_UserAuthorisation($this, $customServiceParameters);
        } elseif(!is_null($customServiceParameters)) {
            $redirect->setParameters($customServiceParameters);
        }
        if (!is_null($token)) {
            $this->_requestToken = $token;
        }
        return $redirect->getUrl();
    }

    public function redirect(array $customServiceParameters = null,
        Zend_Oauth_Http_UserAuthorisation $request = null)
    {
        $redirectUrl = $this->getRedirectUrl($customServiceParameters, $request);
        header('Location: ' . $redirectUrl);
    }

    public function getAccessToken($queryData, Zend_Oauth_Token_Request $token,
        $httpMethod = null, Zend_Oauth_Http_AccessToken $request = null)
    {
        $authorisedToken = new Zend_Oauth_Token_AuthorisedRequest($queryData);
        if (!$authorisedToken->isValid()) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                'Response from Service Provider is not a valid authorised request token');
        }
        if (is_null($request)) {
            $request = new Zend_Oauth_Http_AccessToken($this);
        }
        if (!is_null($httpMethod)) {
            $request->setMethod($httpMethod);
        } else {
            $request->setMethod($this->getRequestMethod());
        }
        if (isset($token)) {
            if ($authorisedToken->getToken() !== $token->getToken()) {
                require_once 'Zend/Oauth/Exception.php';
                throw new Zend_Oauth_Exception(
                    'Authorised token from Service Provider does not match
                    supplied Request Token details'
                );
            }
        } else {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception('Request token must be passed to method');
        }
        $this->_requestToken = $token;
        $this->_accessToken = $request->execute();
        return $this->_accessToken;
    }

    public function getLastRequestToken()
    {
        return $this->_requestToken;
    }

    public function getLastAccessToken()
    {
        return $this->_accessToken;
    }

    public function getToken() 
    {
        return $this->_accessToken;
    }

    /**
     * Simple Proxy to the current Zend_Oauth_Config method. It's that instance
     * which holds all configuration methods and values this object also presents
     * as it's API.
     *
     * @param Zend_Http_Client $httpClient
     * @return void
     */
    public function __call($method, array $args) 
    {
        if (method_exists($this->_config, $method)) {
            return call_user_func_array(array($this->_config,$method), $args);
        }
        require_once 'Zend/Oauth/Exception.php';
        throw new Zend_Oauth_Exception('Method does not exist: '.$method);
    }

}