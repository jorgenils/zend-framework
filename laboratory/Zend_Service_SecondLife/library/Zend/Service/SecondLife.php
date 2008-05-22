<?php
class Zend_Service_SecondLife
{
    protected $_host = "https://cap.secondlife.com";

    protected $_firstName;
    protected $_lastName;

    protected $_password;

    protected $_errorCodes = array();

    protected $_lastNames = array();

    protected $_httpClient;

    protected $_capabilities = false;

    public function __construct($firstName, $lastName, $password)
    {
        if (empty($firstName) or empty($lastName) or empty($password)) {
            require_once 'Zend/Service/SecondLife/Exception.php';
            throw new Zend_Service_SecondLife_Exception(
                'Firstname, lastname and password must be specified'
            );
        }

        $this->_firstName = $firstName;
        $this->_lastName  = $lastName;
        $this->_password  = $password;
    }

    public function getCapability($capability)
    {
        $this->getCapabilities();

        if (!isset($this->_capabilities[$capability])) {

            require_once 'Zend/Service/SecondLife/Exception.php';
            throw new Zend_Service_SecondLife_Exception(
                sprintf('Invalid capability "%s"', $capability)
            );

        }
        return $this->_capabilities[$capability];
    }

    public function getLastNames()
    {
        if (empty($this->_lastNames)) {
            $xml = $this->_httpGet($this->getCapability('get_last_names'));
            $this->_lastNames = $this->_parseResponse($xml);
        }
        return $this->_lastNames;
    }

    public function checkName($username, $lastNameId)
    {
        require_once 'Zend/Service/SecondLife/Value.php';
        $array = Zend_Service_SecondLife_Value::parse(
            array('username' => $username, 'last_name_id' => $lastNameId)
        );

        require_once 'Zend/Service/SecondLife/Value/Llsd.php';
        $request = new Zend_Service_SecondLife_Value_Llsd($array);
        $xml = $this->_httpPost(
            $this->getCapability('check_name'),
            $request->toXml()
        );
        return $this->_parseResponse($xml);
    }

    public function getLastName($lastNameId)
    {
        $names = $this->getLastNames();
        if (!isset($names[$lastNameId])) {
            require_once 'Zend/Service/SecondLife/Exception.php';
            throw new Zend_Service_SecondLife_Exception(
                sprintf('Invalid last name ID: %s', $lastNameId)
            );
        }

        return $names[$lastNameId];
    }

    public function getLastNameId($lastName)
    {
        if (($index = array_search($lastName, $names = $this->getLastNames())) === false) {
            require_once 'Zend/Service/SecondLife/Exception.php';
            throw new Zend_Service_SecondLife_Exception(
                sprintf('Invalid last name: %s', $lastName)
            );
        }

        return $index;
    }

    public function registerUser(
                    $firstName,
                    $lastName,
                    $email,
                    $password,
                    $dateOfBirth,
                    $stateLimitation                                         = null,
                    $startRegion                                             = null,
                    Zend_Service_SecondLife_Coordinate      $startCoordinate = null,
                    Zend_Service_SecondLife_Coordinate_Look $lookCoordinate  = null
    )
    {
        $params = array(
            'username'     => $firstName,
            'last_name_id' => (is_int($lastName) ? $lastName : $this->getLastNameId($lastName)),
            'email'        => $email,
            'password'     => $password,
            'dob'          => $dateOfBirth
        );


        if (null !== $stateLimitation) {
    
            if (!is_integer($stateLimitation)) {
                require_once 'Zend/Service/SecondLife/Exception.php';
                throw new Zend_Service_SecondLife_Exception(
                    sprintf(
                        'Parameter $stateLimitation must be an integer. "%s" given',
                        gettype($stateLimitation)
                    )
                );
            }
            $params['limited_to_estate'] = $stateLimitation;
        }

        if (null !== $startRegion) {
            $params['start_region_name'] = $startRegion;

            if (null !== $startCoordinate) {
                $params['start_local_x'] = $startCoordinate->getX();
                $params['start_local_y'] = $startCoordinate->getY();
                $params['start_local_z'] = $startCoordinate->getZ();
            }

            if (null !== $lookCoordinate) {
                $params['start_look_at_x'] = $lookCoordinate->getX();
                $params['start_look_at_y'] = $lookCoordinate->getY();
                $params['start_look_at_z'] = $lookCoordinate->getZ();
            }
        }

        require_once 'Zend/Service/SecondLife/Value/Llsd.php';
        $request = new Zend_Service_SecondLife_Value_Llsd($params);

        $xml = $this->_httpPost($this->getCapability('create_user'), $request->toXml());
        $result = $this->_parseResponse($xml);

        if (isset($result['agent_id'])) {
            return $result['agent_id'];

        } else {
            $errorCode = array_shift($result);
            require_once 'Zend/Service/SecondLife/Exception.php';
            throw new Zend_Service_SecondLife_Exception(
                $this->getErrorDescription($errorCode), 
                $errorCode
            );
        }
    }

    public function getErrorCodes()
    {
        if (empty($this->_errorCodes)) {
            $xml = $this->_httpGet($this->getCapability('get_error_codes'));
            $this->_errorCodes = $this->_parseResponse($xml);
        }

        return $this->_errorCodes;
    }

    public function getErrorMessage($code)
    {
        return $this->_getError($code, 1);
    }

    public function getErrorDescription($code)
    {
        return $this->_getError($code, 2);
    }

    public function getCapabilities()
    {
        if (empty($this->_capabilities)) {
            $body = $this->getHttpClient()
                ->setUri($this->_host . '/get_reg_capabilities')
                ->setParameterPost(array(
                    'first_name' => $this->_firstName,
                    'last_name'  => $this->_lastName,
                    'password'   => $this->_password
                ))
                ->request(Zend_Http_Client::POST)
                ->getBody();

            try {
                $this->_capabilities = $this->_parseResponse($body);
            } catch (Zend_Service_SecondLife_Exception $exception) {
                throw new Zend_Service_SecondLife_Exception($body);
            }
        }
        return $this->_capabilities;
    }

    public function setHttpClient(Zend_Http_Client $httpClient = null)
    {
        if (null === $httpClient) {
            require_once 'Zend/Http/Client.php';
            $this->_httpClient = new Zend_Http_Client();
        } else {
            $this->_httpClient = $httpClient;
        }

        return $this->_httpClient;
    }

    protected function _getError($code, $index)
    {
        foreach ($this->getErrorCodes() as $error) {
            if ($error[0] == $code) {
                return $error[$index];
            }
        }
        return false;
    }

    public function getHttpClient()
    {
        if (null === $this->_httpClient) {
            $this->setHttpClient();
        }

        return $this->_httpClient;
    }

    protected function _parseResponse($response)
    {
        require_once 'Zend/Service/SecondLife/Value.php';
        $value = Zend_Service_SecondLife_Value::fromXml($response)->getValue();
        return $value;
        
    }

    protected function _httpGet($path)
    {
        return $this->getHttpClient()
            ->setUri($path)
            ->setMethod(Zend_Http_Client::GET)
            ->request()
            ->getBody();
    }

    protected function _httpPost($path, $data, $mimeType = 'application/xml')
    {
        return $this->getHttpClient()
            ->setUri($path)
            ->setHeaders(array('Content-Type: ' . $mimeType))
            ->setRawData($data)
            ->request(Zend_Http_Client::POST)
            ->getBody();
    }
}
