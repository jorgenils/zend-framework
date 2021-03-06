<?php

/**
 * @category   Zend
 * @package    Zend_Mail
 * @subpackage UnitTests
 */


/**
 * Zend_Mail_Imap
 */
require_once 'Zend/Mail/Imap.php';

/**
 * PHPUnit test case
 */
require_once 'PHPUnit/Framework/TestCase.php';


/**
 * @category   Zend
 * @package    Zend_Mail
 * @subpackage UnitTests
 */
class Zend_Mail_ImapTest extends PHPUnit_Framework_TestCase
{
    protected $_params;
    
    public function setUp()
    {
        $this->_params = array('host'     => TESTS_ZEND_MAIL_IMAP_HOST, 
                               'user'     => TESTS_ZEND_MAIL_IMAP_USER,
                               'password' => TESTS_ZEND_MAIL_IMAP_PASSWORD);
    }

    public function testConnectOk()
    {
        try {
            $mail = new Zend_Mail_Imap($this->_params);
        } catch (Exception $e) {
            $this->fail('exception raised while loading connection to imap server');
        }
    }
    
    public function testConnectFailure()
    {
        $this->_params['host'] = 'example.example';
        try {
            $mail = new Zend_Mail_Imap($this->_params);
        } catch (Exception $e) {
            return; // test ok
        }
        
        // I can only hope noone installs a imap server there
        $this->fail('no exception raised while connecting to example.example');
    }

    public function testConnectSSL()
    {
        if(!TESTS_ZEND_MAIL_IMAP_SSL) {
            return;
        }
    
        $this->_params['ssl'] = 'SSL';      
        try {
            $mail = new Zend_Mail_Imap($this->_params);
        } catch (Exception $e) {
            $this->fail('exception raised while loading connection to imap server with SSL');
        }
    }

    public function testConnectTLS()
    {
        if(!TESTS_ZEND_MAIL_IMAP_TLS) {
            return;
        }
    
        $this->_params['ssl'] = 'TLS';      
        try {
            $mail = new Zend_Mail_Imap($this->_params);
        } catch (Exception $e) {
            $this->fail('exception raised while loading connection to imap server with TLS');
        }
    }

    public function testInvalidService()
    {
        $this->_params['port'] = TESTS_ZEND_MAIL_IMAP_INVALID_PORT;

        try {
            $mail = new Zend_Mail_Imap($this->_params);
        } catch (Exception $e) {
            return; // test ok
        }
        
        $this->fail('no exception while connection to invalid port');
    }

    public function testWrongService()
    {
        $this->_params['port'] = TESTS_ZEND_MAIL_IMAP_WRONG_PORT;

        try {
            $mail = new Zend_Mail_Imap($this->_params);
        } catch (Exception $e) {
            return; // test ok
        }
        
        $this->fail('no exception while connection to wrong port');
    }

    public function testClose()
    {
        $mail = new Zend_Mail_Imap($this->_params);

        try {
            $mail->close();
        } catch (Exception $e) {
            $this->fail('exception raised while closing imap connection');
        }
    }
/*
    currently imap has no top
    
    public function testHasTop()
    {
        $mail = new Zend_Mail_Imap($this->_params);

        $this->assertTrue($mail->hasTop);
    }
*/
    public function testHasCreate()
    {
        $mail = new Zend_Mail_Imap($this->_params);

        $this->assertFalse($mail->hasCreate);
    }

/*
    noop not yet supported

    public function testNoop()
    {
        $mail = new Zend_Mail_Imap($this->_params);

        try {
            $mail->noop();
        } catch (Exception $e) {
            $this->fail('exception raised while doing nothing (noop)');
        }
    }
*/
    public function testCount()
    {
        $mail = new Zend_Mail_Imap($this->_params);

        $count = $mail->countMessages();
        $this->assertEquals(5, $count);
    }
    
    public function testSize()
    {
        $mail = new Zend_Mail_Imap($this->_params);
        $shouldSizes = array(1 => 397, 89, 709, 452, 497);


        $sizes = $mail->getSize();
        $this->assertEquals($shouldSizes, $sizes);
    }
    
    public function testSingleSize()
    {
        $mail = new Zend_Mail_Imap($this->_params);

        $size = $mail->getSize(2);
        $this->assertEquals(89, $size);
    }

    public function testFetchHeader()
    {
        $mail = new Zend_Mail_Imap($this->_params);
        
        $subject = $mail->getHeader(1)->subject;
        $this->assertEquals('Simple Message', $subject);
    }

/*
    currently imap has no top

    public function testFetchTopBody()
    {
        $mail = new Zend_Mail_Imap($this->_params);
        
        $content = $mail->getHeader(3, 1)->getContent();
        $this->assertEquals('Fair river! in thy bright, clear flow', trim($content));
    }
*/
    public function testFetchMessageHeader()
    {
        $mail = new Zend_Mail_Imap($this->_params);
        
        $subject = $mail->getMessage(1)->subject;
        $this->assertEquals('Simple Message', $subject);
    }

    public function testFetchMessageBody()
    {
        $mail = new Zend_Mail_Imap($this->_params);
        
        $content = $mail->getMessage(3)->getContent();
        list($content, ) = explode("\n", $content, 2);
        $this->assertEquals('Fair river! in thy bright, clear flow', trim($content));
    }

/*    
    public function testFailedRemove()
    {
        $mail = new Zend_Mail_Imap($this->_params);

        try {
            $mail->removeMessage(1);
        } catch (Exception $e) {
            return; // test ok
        }
        
        $this->fail('no exception raised while deleting message (mbox is read-only)');
    }
*/
}
