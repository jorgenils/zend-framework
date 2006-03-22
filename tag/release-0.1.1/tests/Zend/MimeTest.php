<?php
/**
 * @package 	Zend_Mime
 * @subpackage  UnitTests
 */


/**
 * Zend_Mime
 */
require_once 'Zend/Mime.php';

/**
 * PHPUnit2 test case
 */
require_once 'PHPUnit2/Framework/TestCase.php';


/**
 * @package 	Zend_Mime
 * @subpackage  UnitTests
 */
class Zend_MimeTest extends PHPUnit2_Framework_TestCase
{
    public function testBoundary()
    {
        // check boundary for uniqueness
        $m1 = new Zend_Mime();
        $m2 = new Zend_Mime();
        $this->assertNotEquals($m1->boundary(), $m2->boundary());

        // check instantiating with arbitrary boundary string
        $myBoundary = 'mySpecificBoundary';
        $m3 = new Zend_Mime($myBoundary);
        $this->assertEquals($m3->boundary(), $myBoundary);

    }

    public function testPrintable()
    {
        // check if isPrintable works right.
        $this->assertFalse(Zend_Mime::isPrintable('Test with special chars: �����'));
        $this->assertTrue(Zend_Mime::isPrintable('Test without special chars'));
    }

    public function testQP()
    {
        $text = "This is a cool Test Text with special chars: ����\n"
              . "and with multiple lines���� some of the Lines are long, long"
              . ", long, long, long, long, long, long, long, long, long, long"
              . ", long, long, long, long, long, long, long, long, long, long"
              . ", long, long, long, long, long, long, long, long, long, long"
              . ", long, long, long, long and with ����";

        $qp = Zend_Mime::encodeQuotedPrintable($text);
        $this->assertEquals(quoted_printable_decode($qp), $text);
    }

    public function testBase64()
    {
        $content = str_repeat("\x88\xAA\xAF\xBF\x29\x88\xAA\xAF\xBF\x29\x88\xAA\xAF", 4);
        $encoded = Zend_Mime::encodeBase64($content);
        $this->assertEquals($content, base64_decode($encoded));
    }
}
