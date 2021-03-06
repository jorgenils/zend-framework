<?php

/**
 * @category   Zend
 * @package    Zend_Translate
 * @subpackage UnitTests
 */


/**
 * Zend_Translate_Adapter_Tbx
 */
require_once 'Zend/Translate/Adapter/Tbx.php';

/**
 * PHPUnit test case
 */
require_once 'PHPUnit/Framework/TestCase.php';


/**
 * @category   Zend
 * @package    Zend_Translate
 * @subpackage UnitTests
 */
class Zend_Translate_TbxTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $adapter = new Zend_Translate_Adapter_Tbx(dirname(__FILE__) . '/_files/translation_en.tbx');
        $this->assertTrue($adapter instanceof Zend_Translate_Adapter_Tbx);

        try {
            $adapter = new Zend_Translate_Adapter_Tbx(dirname(__FILE__) . '/_files/nofile.tbx', 'en');
            $this->fail("exception expected");
        } catch (Zend_Translate_Exception $e) {
            // success
        }
        try {
            $adapter = new Zend_Translate_Adapter_Tbx(dirname(__FILE__) . '/_files/failed.tbx', 'en');
            $this->fail("exception expected");
        } catch (Zend_Translate_Exception $e) {
            // success
        }
    }

    public function testToString()
    {
        $adapter = new Zend_Translate_Adapter_Tbx(dirname(__FILE__) . '/_files/translation_en.tbx');
        $this->assertEquals('Tbx', $adapter->toString());
    }

    public function testTranslate()
    {
        $adapter = new Zend_Translate_Adapter_Tbx(dirname(__FILE__) . '/_files/translation_en.tbx', 'en');
        $this->assertEquals('Message 1',      $adapter->translate('Message 1'      ));
        $this->assertEquals('Message 1',      $adapter->_('Message 1'              ));
        $this->assertEquals('Message 1 (fr)', $adapter->translate('Message 1', 'fr'));
        $this->assertEquals('Message 5',      $adapter->translate('Message 5'      ));
    }

    public function testIsTranslated()
    {
        $adapter = new Zend_Translate_Adapter_Tbx(dirname(__FILE__) . '/_files/translation_en.tbx', 'en');
        $this->assertTrue( $adapter->isTranslated('Message 1'             ));
        $this->assertFalse($adapter->isTranslated('Message 6'             ));
        $this->assertTrue( $adapter->isTranslated('Message 1', true       ));
        $this->assertTrue( $adapter->isTranslated('Message 1', true,  'en'));
        $this->assertFalse($adapter->isTranslated('Message 1', false, 'es'));
    }

    public function testLoadTranslationData()
    {
        $adapter = new Zend_Translate_Adapter_Tbx(dirname(__FILE__) . '/_files/translation_en.tbx', 'en');

        $this->assertEquals('Message 1', $adapter->translate('Message 1'         ));
        $this->assertEquals('Message 5', $adapter->translate('Message 5'         ));
        $this->assertEquals('Message 2', $adapter->translate('Message 2', 'ru'   ));
        $this->assertEquals('Message 1', $adapter->translate('Message 1', 'xx'   ));
        $this->assertEquals('Message 1', $adapter->translate('Message 1', 'en_US'));

        try {
            $adapter->addTranslation(dirname(__FILE__) . '/_files/translation_en.tbx', 'xx');
            $this->fail("exception expected");
        } catch (Zend_Translate_Exception $e) {
            // success
        }

        $adapter->addTranslation(dirname(__FILE__) . '/_files/translation_en2.tbx', 'de', array('clear' => true));
        $this->assertEquals('Message 1', $adapter->translate('Message 1'));
        $this->assertEquals('Message 8', $adapter->translate('Message 8'));
    }

    public function testOptions()
    {
        $adapter = new Zend_Translate_Adapter_Tbx(dirname(__FILE__) . '/_files/translation_en.tbx', 'en');
        $adapter->setOptions(array('testoption' => 'testkey'));
        $this->assertEquals(array('testoption' => 'testkey', 'clear' => false, 'scan' => null), $adapter->getOptions());
        $this->assertEquals('testkey', $adapter->getOptions('testoption'));
        $this->assertTrue(is_null($adapter->getOptions('nooption')));
    }

    public function testLocale()
    {
        $adapter = new Zend_Translate_Adapter_Tbx(dirname(__FILE__) . '/_files/translation_en.tbx', 'en');
        $this->assertEquals('en', $adapter->getLocale());
        $locale = new Zend_Locale('en');
        $adapter->setLocale($locale);
        $this->assertEquals('en', $adapter->getLocale());

        try {
            $adapter->setLocale('nolocale');
            $this->fail("exception expected");
        } catch (Zend_Translate_Exception $e) {
            // success
        }
        try {
            $adapter->setLocale('ar');
            $this->fail("exception expected");
        } catch (Zend_Translate_Exception $e) {
            // success
        }
    }

    public function testList()
    {
        $adapter = new Zend_Translate_Adapter_Tbx(dirname(__FILE__) . '/_files/translation_en.tbx', 'en');
        $this->assertEquals(array('en' => 'en', 'fr' => 'fr'), $adapter->getList());
        $this->assertTrue($adapter->isAvailable('fr'));
        $locale = new Zend_Locale('en');
        $this->assertTrue( $adapter->isAvailable($locale));
        $this->assertFalse($adapter->isAvailable('sr'   ));
    }
}
