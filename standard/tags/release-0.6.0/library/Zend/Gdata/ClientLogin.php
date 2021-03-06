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
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License

/**
 * Zend
 */
require_once 'Zend.php';

/**
 * Zend_Http_Client
 */
require_once 'Zend/Http/Client.php';


/**
 * Class to facilitate Google's "Account Authentication
 * for Installed Applications" also known as "ClientLogin".
 * @see http://code.google.com/apis/accounts/AuthForInstalledApps.html
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_ClientLogin
{

    /**
     * The Google client login URI
     *
     */
    const CLIENTLOGIN_URI = 'https://www.google.com/accounts/ClientLogin';

    /**
     * The default 'source' parameter to send to Google
     *
     */
    const DEFAULT_SOURCE = 'Zend-ZendFramework';

    /**
     * Set Google authentication credentials.
     * Must be done before trying to do any Google Data operations that
     * require authentication.
     * For example, viewing private data, or posting or deleting entries.
     *
     * @param string $email
     * @param string $password
     * @param string $service
     * @param Zend_Http_Client $client
     * @param string $source
     * @return Zend_Http_Client
     */
    public function getHttpClient($email, $password, $service = 'xapi',
        $client = null,
        $source = self::DEFAULT_SOURCE)
    {
        if (! ($email && $password)) {
            throw Zend::exception('Zend_Http_Exception', 'Please set your Google credentials before trying to authenticate');
        }

        if ($client == null) {
            $client = new Zend_Http_Client();
        }
        if (!$client instanceof Zend_Http_Client) {
            throw Zend::exception('Zend_Http_Exception', 'Client is not an instance of Zend_Http_Client.');
        }

        // Build the HTTP client for authentication
        $client->setUri(self::CLIENTLOGIN_URI);
        $client->setConfig(array(
                'maxredirects'    => 0,
                'strictredirects' => true
            )
        );
        $client->setParameterPost('accountType', 'HOSTED_OR_GOOGLE');
        $client->setParameterPost('Email', (string) $email);
        $client->setParameterPost('Passwd', (string) $password);
        $client->setParameterPost('service', (string) $service);
        $client->setParameterPost('source', (string) $source);

        // Send the authentication request
        // For some reason Google's server causes an SSL error. We use the
        // output buffer to supress an error from being shown. Ugly - but works!
        ob_start();
        $response = $client->request('POST');
        ob_end_clean();

        // Parse Google's response
        $goog_resp = array();
        foreach (explode("\n", $response->getBody()) as $l) {
            $l = chop($l);
            if ($l) {
                list($key, $val) = explode('=', chop($l), 2);
                $goog_resp[$key] = $val;
            }
        }

        if ($response->getStatus() == 200) {
            $headers['authorization'] = 'GoogleLogin auth=' . $goog_resp['Auth'];
            $client = new Zend_Http_Client();
            $client->setHeaders($headers);
            return $client;

        } elseif ($response->getStatus() == 403) {
            throw Zend::exception('Zend_Http_Exception', 'Authentication with Google failed. Reason: ' .
                (isset($goog_resp['Error']) ? $goog_resp['Error'] : 'Unspecified.'));
        }
    }

}
