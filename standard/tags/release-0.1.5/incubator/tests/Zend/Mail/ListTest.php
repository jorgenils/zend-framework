<?php

/**
 * @category   Zend
 * @package    Zend_Mail
 * @subpackage UnitTests
 */


/**
 * Zend_Mail_Mbox
 */
require_once 'Zend/Mail/Mbox.php';

/**
 * Zend_Mail_List
 */
require_once 'Zend/Mail/List.php';

/**
 * PHPUnit2 test case
 */
require_once 'PHPUnit2/Framework/TestCase.php';


/**
 * @category   Zend
 * @package    Zend_Mail
 * @subpackage UnitTests
 */
class Zend_Mail_ListTest extends PHPUnit2_Framework_TestCase
{
    protected $_mboxFile;
    
    public function setUp()
    {
        $this->_mboxFile = dirname(__FILE__) . '/_files/test.mbox';
    }

    public function testCount()
    {
        $list = new Zend_Mail_List(new Zend_Mail_Mbox(array('filename' => $this->_mboxFile)));
        
        $count = count($list);
        $this->assertEquals(5, $count);
    }

    public function testIsset()
    {
        $list = new Zend_Mail_List(new Zend_Mail_Mbox(array('filename' => $this->_mboxFile)));
        
        $this->assertTrue(isset($list[1]));
    }

    public function testNotIsset()
    {
        $list = new Zend_Mail_List(new Zend_Mail_Mbox(array('filename' => $this->_mboxFile)));
        
        $this->assertFalse(isset($list[10]));
    }
    
    public function testArrayGet()
    {
        $list = new Zend_Mail_List(new Zend_Mail_Mbox(array('filename' => $this->_mboxFile)));

        $subject = $list[1]->subject;
        $this->assertEquals('Simple Message', $subject);
    }

    public function testArraySetFail()
    {
        $list = new Zend_Mail_List(new Zend_Mail_Mbox(array('filename' => $this->_mboxFile)));

        try {
            $list[1] = 'test';
        } catch (Exception $e) {
            return; // test ok
        }
        
        $this->fail('no exception thrown while writing to array access');
    }
    
    public function testIterationKey()
    {
        $list = new Zend_Mail_List(new Zend_Mail_Mbox(array('filename' => $this->_mboxFile)));

        $pos = 1;
        foreach($list as $key => $message) {
            $this->assertEquals($key, $pos, "wrong key in iteration $pos");
            ++$pos;
        }       
    }
    
    public function testIterationIsMessage()
    {
        $list = new Zend_Mail_List(new Zend_Mail_Mbox(array('filename' => $this->_mboxFile)));

        foreach($list as $key => $message) {
            $this->assertTrue($message instanceof Zend_Mail_Message, 'value in iteration is not a mail message');
        }
    }
    
    public function testIterationRounds()
    {
        $list = new Zend_Mail_List(new Zend_Mail_Mbox(array('filename' => $this->_mboxFile)));
        
        $count = 0;
        foreach($list as $key => $message) {
            ++$count;
        }
        
        $this->assertEquals(5, $count);
    }

    public function testIterationWithSeek()
    {
        $list = new Zend_Mail_List(new Zend_Mail_Mbox(array('filename' => $this->_mboxFile)));
        
        $count = 0;
        foreach(new LimitIterator($list, 1, 3) as $key => $message) {
            ++$count;
        }
        
        $this->assertEquals(3, $count);
    }

    public function testIterationWithSeekCapped()
    {
        $list = new Zend_Mail_List(new Zend_Mail_Mbox(array('filename' => $this->_mboxFile)));
        
        $count = 0;
        foreach(new LimitIterator($list, 3, 5) as $key => $message) {
            ++$count;
        }
        
        $this->assertEquals(3, $count);
    }
    
    public function testFallback()
    {
        $list = new Zend_Mail_List(new Zend_Mail_Mbox(array('filename' => $this->_mboxFile)));

        try {
            $result = $list->noop();
            $this->assertTrue($result);
        } catch (Exception $e) {
            $this->fail('exception raised while calling noop thru fallback');
        }       
    }
}