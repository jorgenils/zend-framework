<?php
require_once 'Zend/Service/SecondLife.php';
require_once 'Zend/Service/SecondLife/Exception.php';
require_once 'Zend/Service/SecondLife/Coordinate.php';
require_once 'Zend/Service/SecondLife/Coordinate/Look.php';

class Zend_Service_SecondLifeTest extends PHPUnit_Framework_TestCase
{

    protected static $_lastNames;

    public function setUp()
    {
        if (!TESTS_ZEND_SERVICE_SECONDLIFE_ONLINE_ENABLED) {
            $this->markTestSkipped('Online tests disabled');
        }

        $this->client = new Zend_Service_SecondLife(
            TESTS_ZEND_SERVICE_SECONDLIFE_ONLINE_FIRSTNAME,
            TESTS_ZEND_SERVICE_SECONDLIFE_ONLINE_LASTNAME,
            TESTS_ZEND_SERVICE_SECONDLIFE_ONLINE_PASSWORD
        );
    }
    
    public static function registrationProvider()
    {
        return array(
            array(
                // Email address
                sprintf(
                    '%s%s@%s',
                    TESTS_ZEND_SERVICE_SECONDLIFE_ONLINE_EMAIL_LOCAL,
                    uniqid(),
                    TESTS_ZEND_SERVICE_SECONDLIFE_ONLINE_EMAIL_HOST
                ),
                // First name
                uniqid() . uniqid(),
                // Password
                uniqid()
            )
        );
    }

    public function testGetLastNames()
    {
        $array = $this->client->getLastNames();
        $this->assertTrue(is_array($array));
        $this->assertTrue(count($array) > 20);
    }

    /**
     *
     * @dataProvider registrationProvider
     */
    public function testCheckNameAndRegisterUser($email, $firstName, $password)
    {
        $lastNameId = array_rand($this->client->getLastNames());
        $this->assertTrue($this->client->checkName($firstName, $lastNameId));
        $secondLifeId = $this->client->registerUser(
            $firstName,
            $lastNameId,
            $email,
            $password,
            '1986-04-24',
            null,
            TESTS_ZEND_SERVICE_SECONDLIFE_ONLINE_REGION_NAME,
            new Zend_Service_SecondLife_Coordinate(1, 2, 3),
            new Zend_Service_SecondLife_Coordinate_Look(1, 1, 0)
        );
        $this->assertTrue(is_string($secondLifeId), 'SecondLife ID is a string');
        $this->assertContains('-', $secondLifeId, 'SecondLife ID must be in the form "<str>-<str>-<str>.."');
        $this->assertTrue(strlen($secondLifeId) > 10, 'SecondLife ID is longer 10 characters');
        $this->assertFalse($this->client->checkName($firstName, $lastNameId));
    }

    /**
     * @dataProvider registrationProvider
     */
    public function testRegisterUserWithoutNameId($email, $firstName, $password)
    {
        $names = $this->client->getLastNames();

        $secondLifeId = $this->client->registerUser(
            $firstName,
            $names[array_rand($names)],
            $email,
            $password,
            '1986-04-24'
        );
        
        $this->assertTrue(is_string($secondLifeId), 'SecondLife ID is a string');
        $this->assertContains('-', $secondLifeId, 'SecondLife ID must be in the form "<str>-<str>-<str>.."');
        $this->assertTrue(strlen($secondLifeId) > 10, 'SecondLife ID is longer 10 characters');
    }

    public function testGetRegCap()
    {
        $response = $this->client->getCapabilities();
        $this->assertTrue(array_key_exists('check_name', $response));
        $this->assertTrue(array_key_exists('create_user', $response));
    }

    public function testInvalidPasswordException()
    {
        $client = new Zend_Service_SecondLife(
            TESTS_ZEND_SERVICE_SECONDLIFE_ONLINE_FIRSTNAME,
            TESTS_ZEND_SERVICE_SECONDLIFE_ONLINE_LASTNAME,
            'wrong'
        );
        $this->setExpectedException('Zend_Service_SecondLife_Exception', 'Incorrect Password');
        $client->getCapabilities();
    }
    
    public function testUnknownUserException()
    {
        $client = new Zend_Service_SecondLife(
            'foo',
            TESTS_ZEND_SERVICE_SECONDLIFE_ONLINE_LASTNAME,
            TESTS_ZEND_SERVICE_SECONDLIFE_ONLINE_PASSWORD
        );
        $this->setExpectedException('Zend_Service_SecondLife_Exception', 'User does not exist');
        $client->getCapabilities();
    }

    public function testUnknownLastnameException()
    {
        $client = new Zend_Service_SecondLife(
            TESTS_ZEND_SERVICE_SECONDLIFE_ONLINE_FIRSTNAME,
            'foobar',
            TESTS_ZEND_SERVICE_SECONDLIFE_ONLINE_PASSWORD
        );
        $this->setExpectedException('Zend_Service_SecondLife_Exception', 'Last Name does not exist');
        $client->getCapabilities();
    }

    public function testConstructorThrowsExceptionIfUsernameNullOrEmpty()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Exception');
        new Zend_Service_SecondLife(null, 'bar', 'pw');
    }

    public function testConstructorThrowsExceptionIfLastnameNullOrEmpty()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Exception');
        new Zend_Service_SecondLife('firstname', '', 'pw');
    }

    public function testConstructorThrowsExceptionIfPasswordNullOrEmpty()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Exception');
        new Zend_Service_SecondLife('foo', 'bar', null);
    }

    public function testGetErrorMessageAndDescription()
    {
        $codes = $this->client->getErrorCodes();
        $this->assertTrue(is_array($codes));
        $this->assertTrue(isset($codes[0][0]));
        $this->assertTrue(isset($codes[0][1]));
        $this->assertTrue(isset($codes[0][2]));


        $this->assertFalse($this->client->getErrorMessage(125));
        $this->assertTrue(is_string($this->client->getErrorMessage(120)));

        $this->assertFalse($this->client->getErrorDescription(125));
        $this->assertTrue(is_string($this->client->getErrorDescription(120)));
    }
}
