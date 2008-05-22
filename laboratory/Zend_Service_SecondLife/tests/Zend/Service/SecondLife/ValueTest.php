<?php

require_once 'Zend/Service/SecondLife/Value.php';
require_once 'Zend/Service/SecondLife/Value/Llsd.php';
require_once 'Zend/Service/SecondLife/Value/Exception.php';

class Zend_Service_SecondLife_ValueTest extends PHPUnit_Framework_TestCase
{

    public function testParseStringXml()
    {
        $node = Zend_Service_SecondLife_Value::parse('string');
        $this->assertEquals("<string>string</string>", $node->toXml());

        $node->setValue('&test');
        $this->assertEquals("<string>&amp;test</string>", $node->toXml());
    }

    public function testParseIntegerXml()
    {
        $node = Zend_Service_SecondLife_Value::parse(1);
        $this->assertEquals("<integer>1</integer>", $node->toXml());

        $node->setValue("bla");
        $this->assertEquals("<integer>0</integer>", $node->toXml());

        $node->setValue(1.2);
        $this->assertEquals("<integer>1</integer>", $node->toXml());
    }

    public function testVariousBooleanRepresentationsWork()
    {
        $node = Zend_Service_SecondLife_Value::parse(false);
        $this->assertEquals("<boolean>false</boolean>", $node->getValue());

        $node = Zend_Service_SecondLife_Value::fromXml('<boolean>false</boolean>');
        $this->assertFalse($node->getValue());

        $n = new Zend_Service_SecondLife_Value_Boolean("true");
        $this->assertTrue($n->getValue());

        $n = new Zend_Service_SecondLife_Value_Boolean("false");
        $this->assertFalse($n->getValue());
    }

    public function testParseArrayXml()
    {
        $node = Zend_Service_SecondLife_Value::parse(array('foo', 'bar'));
        $this->assertEquals("<array><string>foo</string><string>bar</string></array>", $node->toXml());

        $node->setValue(array('bar', 'foo', 'bla'));
        $this->assertEquals("<array><string>bar</string><string>foo</string><string>bla</string></array>", $node->toXml());
    }

    public function testParseMapXml()
    {
        $node = Zend_Service_SecondLife_Value::parse(array('test' => 'bla', 'int' => 1));
        $this->assertEquals("<map><key>test</key><string>bla</string><key>int</key><integer>1</integer></map>", $node->toXml());

        $node->setValue(array('str' => 'string', 'bool' => true, 'int' => 100));
        $this->assertEquals("<map>"
                        ."<key>str</key>"
                        ."<string>string</string>"
                        ."<key>bool</key>"
                        ."<boolean>true</boolean>"
                        ."<key>int</key>"
                        ."<integer>100</integer>"
                        ."</map>", $node->toXml());


        $node->setValue(array('ma' => array('foo' => 'bar')));
        $this->assertEquals("<map><key>ma</key><map><key>foo</key><string>bar</string></map></map>", $node->toXml());

        $node->setValue(array("array" => array("foo", "bar", "bla")));
        $this->assertEquals("<map><key>array</key><array><string>foo</string><string>bar</string><string>bla</string></array></map>", $node->toXml());
        
        
        $node->setValue(array("array" => array("foo", 3, 2)));
        $this->assertEquals("<map><key>array</key><array><string>foo</string><integer>3</integer><integer>2</integer></array></map>", $node->toXml());

        $node->setValue(array('key' => new Zend_Service_SecondLife_Value_String('test')));
        $this->assertEquals('<map><key>key</key><string>test</string></map>', $node->toXml());
    }

    public function testCreateAndParseRootXml()
    {
        $node = new Zend_Service_SecondLife_Value_Llsd(Zend_Service_SecondLife_Value::parse(array("test" => "bla")));
        $this->assertEquals("<llsd><map><key>test</key><string>bla</string></map></llsd>", $node->toXml());

        $node->setValue(array("bla"));
        $this->assertEquals("<llsd><array><string>bla</string></array></llsd>", $node->toXml());
    }

    public function testFromXml()
    {
        $node = Zend_Service_SecondLife_Value::fromXml("<string>bla</string>");
        $this->assertEquals('bla', $node->getValue());

        $node = Zend_Service_SecondLife_Value::fromXml("<integer>1</integer>");
        $this->assertEquals(1, $node->getValue());

        $node = Zend_Service_SecondLife_Value::fromXml("<boolean>true</boolean>");
        $this->assertEquals(true, $node->getValue());

        $node = Zend_Service_SecondLife_Value::fromXml("<boolean>false</boolean>");
        $this->assertEquals(false, $node->getValue());

        $node = Zend_Service_SecondLife_Value::fromXml("<integer>0</integer>");
        $this->assertEquals(0, $node->getValue());

        $node = Zend_Service_SecondLife_Value::fromXml("<integer>1.2</integer>");
        $this->assertEquals(1, $node->getValue());

        $node = Zend_Service_SecondLife_Value::fromXml("<array><string>foo</string></array>");
        $this->assertEquals(array('foo'), $node->getValue());

        $node = Zend_Service_SecondLife_Value::fromXml("<array><array><integer>1</integer></array></array>");
        $this->assertEquals(array(array(1)), $node->getValue());


        $node = Zend_Service_SecondLife_Value::fromXml("<array><array><integer>1</integer></array><string>str</string></array>");
        $this->assertEquals(array(array(1), "str"), $node->getValue());

        $node = Zend_Service_SecondLife_Value::fromXml("<map><key>foo</key><string>bla</string></map>");
        $this->assertEquals(array('foo' => 'bla'), $node->getValue());


        $node = Zend_Service_SecondLife_Value::fromXml("<map><key>foo</key><string>bla</string><key>bar</key><integer>2</integer></map>");
        $this->assertEquals(array('foo' => 'bla', 'bar' => 2), $node->getValue());

        $node = Zend_Service_SecondLife_Value::fromXml("<llsd><boolean>true</boolean></llsd>");
        $this->assertEquals(true, $node->getValue());

        $node = Zend_Service_SecondLife_Value::fromXml("<map><key>5008</key><string>Writer</string><key>5035</key><string>Petrov</string><key>5061</key><string>Igaly</string><key>4899</key><string>Ireton</string></map>");
        $this->assertEquals(array("5008" => 'Writer', "5035" => 'Petrov', '5061' => 'Igaly', '4899' => 'Ireton'), $node->getValue());

        $node = Zend_Service_SecondLife_Value::fromXml('<llsd/>');
        $this->assertNull($node->getValue(), 'When llsd has no inner value null is returned');
    }

    public function testEntitiesAndUmlautsAreCorrectlyHandledInStrings()
    {
        $node = Zend_Service_SecondLife_Value::parse("äöü");
        $this->assertEquals("<string>äöü</string>", $node->toXml());

        $node = Zend_Service_SecondLife_Value::parse("&");
        $this->assertEquals("<string>&amp;</string>", $node->toXml());

        $node = Zend_Service_SecondLife_Value::parse('"');
        $this->assertEquals("<string>\"</string>", $node->toXml());

        $node = Zend_Service_SecondLife_Value::parse("<>");
        $this->assertEquals("<string>&lt;&gt;</string>", $node->toXml());
    }

    public function testEntitiesAndUmlautsAreCorrectlyHandledInXml()
    {
        $node = Zend_Service_SecondLife_Value::fromXml("<string>&amp;</string>");
        $this->assertEquals("&", $node->getValue());

        $node = Zend_Service_SecondLife_Value::fromXml('<string>äüö</string>');
        $this->assertEquals('äüö', $node->getValue());
        
        $node = Zend_Service_SecondLife_Value::fromXml('<string>&lt;&gt;</string>');
        $this->assertEquals('<>', $node->getValue());
    }


    public function testParsingXmlException()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Value_Exception');
        Zend_Service_SecondLife_Value::fromXml('<unknown>test</unknown>');
    }
    
    public function testParsingNativeTypes()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Value_Exception');
        Zend_Service_SecondLife_Value::parse(new stdClass());
    }

    public function testPassingNonXmlCausesException()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Value_Exception');
        Zend_Service_SecondLife_Value::fromXml('string');
    }

    public function testXmlElementKeyIsInternallyRestricted()
    {
        $this->setExpectedException('Zend_Service_SecondLife_Value_Exception');
        Zend_Service_SecondLife_Value::fromXml('<key>foo</key>');
    }
}
