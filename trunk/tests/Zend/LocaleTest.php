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
 * @package    Zend_Locale
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

error_reporting( E_ALL | E_STRICT ); // now required for each test suite
// define('TESTS_ZEND_LOCALE_BCMATH_ENABLED', false); // uncomment to disable use of bcmath extension by Zend_Date

/**
 * Zend_Locale
 */
require_once 'Zend.php';
require_once 'Zend/Locale.php';

/**
 * PHPUnit test case
 */
require_once 'PHPUnit/Framework/TestCase.php';

// echo "BCMATH is ", Zend_Locale_Math::isBcmathDisabled() ? 'disabled':'not disabled', "\n";

/**
 * @package    Zend_Locale
 * @subpackage UnitTests
 */
class Zend_LocaleTest extends PHPUnit_Framework_TestCase
{


    /**
     * test for object creation
     * expected object instance
     */
    public function testObjectCreation()
    {
        $this->assertTrue(new Zend_Locale() instanceof Zend_Locale,'Zend_Locale Object not returned');
        $this->assertTrue(new Zend_Locale('root') instanceof Zend_Locale,'Zend_Locale Object not returned');
        $this->assertTrue(new Zend_Locale(Zend_Locale::ENVIRONMENT) instanceof Zend_Locale,'Zend_Locale Object not returned');
        $this->assertTrue(new Zend_Locale(Zend_Locale::BROWSER) instanceof Zend_Locale,'Zend_Locale Object not returned');

        $locale = new Zend_Locale('de');
        $this->assertTrue(new Zend_Locale($locale) instanceof Zend_Locale,'Zend_Locale Object not returned');
    }


    /**
     * test for serialization
     * expected string
     */
    public function testSerialize()
    {
        $value = new Zend_Locale('de_DE');
        $serial = $value->serialize();
        $this->assertTrue(!empty($serial),'Zend_Locale not serialized');

        $newvalue = unserialize($serial);
        $this->assertTrue($value->equals($newvalue),'Zend_Locale not unserialized');
    }


    /**
     * test toString
     * expected string
     */
    public function testToString()
    {
        $value = new Zend_Locale('de_DE');
        $this->assertEquals($value->toString(), 'de_DE', 'Locale de_DE expected');
        $this->assertEquals($value->__toString(), 'de_DE', 'Value de_DE expected');
    }


    /**
     * test getDefault
     * expected true
     */
    public function testgetDefault()
    {
        $value = new Zend_Locale();
        $default = $value->getDefault();
        $this->assertTrue(is_array($default), 'No Default Locale found');

        $default = $value->getDefault(Zend_Locale::BROWSER);
        $this->assertTrue(is_array($default), 'No Default Locale found');

        $default = $value->getDefault(Zend_Locale::ENVIRONMENT);
        $this->assertTrue(is_array($default), 'No Default Locale found');

        $default = $value->getDefault(Zend_Locale::FRAMEWORK);
        $this->assertTrue(is_array($default), 'No Default Locale found');
    }


    /**
     * test getEnvironment
     * expected true
     */
    public function testEnvironment()
    {
        $value = new Zend_Locale();
        $default = $value->getEnvironment();
        $this->assertTrue(is_array($default), 'No Environment Locale found');
    }


    /**
     * test getBrowser
     * expected true
     */
    public function testBrowser()
    {
        $value = new Zend_Locale();
        $default = $value->getBrowser();
        $this->assertTrue(is_array($default), 'No Environment Locale found');
    }


    /**
     * test clone
     * expected true
     */
    public function testCloneing()
    {
        $value = new Zend_Locale('de_DE');
        $newvalue = clone $value;
        $this->assertEquals($value->toString(), $newvalue->toString(), 'Cloning Locale failed');
    }


    /**
     * test setLocale
     * expected true
     */
    public function testsetLocale()
    {
        $value = new Zend_Locale('de_DE');
        $value->setLocale('en_US');
        $this->assertEquals($value->toString(), 'en_US', 'Environment Locale not set');

        $value->setLocale('en_AA');
        $this->assertEquals($value->toString(), 'en', 'Environment Locale not set');

        $value->setLocale('xx_AA');
        $this->assertEquals($value->toString(), 'root', 'Environment Locale not set');
    }


    /**
     * test getLanguageTranslationList
     * expected true
     */
    public function testgetLanguageTranslationList()
    {
        $value = new Zend_Locale();
        $list = $value->getLanguageTranslationList();
        $this->assertTrue(is_array($list), 'Language List not returned');
    }


    /**
     * test getLanguageTranslationListLocale
     * expected true
     */
    public function testgetLanguageTranslationListLocale()
    {
        $value = new Zend_Locale();
        $list = $value->getLanguageTranslationList('de');
        $this->assertTrue(is_array($list), 'Language List not returned');
    }


    /**
     * test getLanguageTranslationDisplay
     * expected true
     */
    public function testgetLanguageTranslationDisplay()
    {
        $value = new Zend_Locale('de_AT');
        $list = $value->getLanguageTranslation('de');
        $this->assertEquals($list, 'Deutsch', 'Language Display not returned');

        $list = $value->getLanguageTranslation('de', 'en');
        $this->assertEquals($list, 'German', 'Language Display not returned');

        $list = $value->getLanguageTranslation('xyz');
        $this->assertFalse(is_string($list), 'Language Display should be false');
    }


    /**
     * test getScriptTranslationList
     * expected true
     */
    public function testgetScriptTranslationList()
    {
        $value = new Zend_Locale();
        $list = $value->getScriptTranslationList();
        $this->assertTrue(is_array($list), 'Script List not returned');

        $list = $value->getScriptTranslationList('de');
        $this->assertTrue(is_array($list), 'Script List not returned');

        $value->setLocale('de');
        $list = $value->getScriptTranslation('Arab');
        $this->assertEquals($list, 'Arabisch', 'Script Display not returned');

        $list = $value->getScriptTranslation('Arab', 'en');
        $this->assertEquals($list, 'Arabic', 'Script Display not returned');

        $list = $value->getScriptTranslation('xyz');
        $this->assertFalse(is_string($list), 'Script Display should be false');
    }


    /**
     * test getCountryTranslationList
     * expected true
     */
    public function testgetCountryTranslationList()
    {
        $value = new Zend_Locale();
        $list = $value->getCountryTranslationList();
        $this->assertTrue(is_array($list), 'Region List not returned');

        $list = $value->getCountryTranslationList('de');
        $this->assertTrue(is_array($list), 'Region List not returned');
    }


    /**
     * test getRegionTranslation
     * expected true
     */
    public function testgetRegionTranslationDisplay()
    {
        $value = new Zend_Locale('de_AT');
        $list = $value->getTerritoryTranslation('DE');
        $this->assertEquals($list, 'Deutschland', 'Region Display not returned');

        $list = $value->getTerritoryTranslation('DE','en');
        $this->assertEquals($list, 'Germany', 'Region Display not returned');

        $list = $value->getTerritoryTranslation('xyz');
        $this->assertFalse(is_string($list), 'Region Display should be false');
    }


    /**
     * test getLanguageTranslation
     * expected true
     */
    public function testgetLanguageTranslation()
    {
        $value = new Zend_Locale('de_DE');
        $this->assertEquals($value->getLanguageTranslation('de'), 'Deutsch', 'No language found');
    }


    /**
     * test getCountryTranslation
     * expected true
     */
    public function testgetCountryTranslation()
    {
        $value = new Zend_Locale('de_DE');
        $this->assertEquals($value->getCountryTranslation('DE'), 'Deutschland', 'No region found');
    }


    /**
     * test for equality
     * expected string
     */
    public function testEquals()
    {
        $value = new Zend_Locale('de_DE');
        $serial = new Zend_Locale('de_DE');
        $serial2 = new Zend_Locale('de_AT');
        $this->assertTrue($value->equals($serial),'Zend_Locale not equals');
        $this->assertFalse($value->equals($serial2),'Zend_Locale equal ?');
    }


    /**
     * test getQuestion
     * expected true
     */
    public function testgetQuestion()
    {
        $value = new Zend_Locale();
        $list = $value->getQuestion();
        $this->assertTrue(isset($list['yes']), 'Question not returned');

        $list = $value->getQuestion('de');
        $this->assertTrue(isset($list['yes']), 'Question not returned');
    }


    /**
     * test getBrowser
     * expected true
     */
    public function testgetBrowser()
    {
        putenv("HTTP_ACCEPT_LANGUAGE=,de,en-UK-US;q=0.5,fr_FR;q=0.2");
        $value = new Zend_Locale();
        $list = $value->getBrowser();
        $this->assertTrue(isset($list['de']), 'language array not returned');
    }


    /**
     * test getHttpCharset
     * expected true
     */
    public function testgetHttpCharset()
    {
        putenv("HTTP_ACCEPT_CHARSET=");
        $value = new Zend_Locale();
        $list = $value->getHttpCharset();
        $this->assertTrue(empty($list), 'language array must be empty');

        putenv("HTTP_ACCEPT_CHARSET=,iso-8859-1, utf-8, utf-16, *;q=0.1");
        $value = new Zend_Locale();
        $list = $value->getHttpCharset();
        $this->assertTrue(isset($list['utf-8']), 'language array not returned');
    }


    /**
     * test isLocale
     * expected boolean
     */
    public function testIsLocale()
    {
        $locale = new Zend_Locale('ar');
        $this->assertEquals(Zend_Locale::isLocale($locale), 'ar', "ar expected");
        $this->assertEquals(Zend_Locale::isLocale('de'), 'de', "de expected");
        $this->assertEquals(Zend_Locale::isLocale('de_AT'), 'de_AT', "de_AT expected");
        $this->assertEquals(Zend_Locale::isLocale('de_xx'), 'de', "de expected");
        $this->assertFalse(Zend_Locale::isLocale('yy'), "false expected");
        $this->assertFalse(Zend_Locale::isLocale(1234), "false expected");
        $locale = Zend_Locale::isLocale('', true);
        $this->assertTrue(is_string($locale), "true expected");
    }
}