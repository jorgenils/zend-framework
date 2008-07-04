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
 * @package    Zend_Dojo
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

// Call Zend_Dojo_View_Helper_DojoTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Zend_Dojo_View_Helper_DojoTest::main");
}

require_once dirname(__FILE__) . '/../../../../TestHelper.php';

/** Zend_Dojo_View_Helper_Dojo */
require_once 'Zend/Dojo/View/Helper/Dojo.php';

/** Zend_Dojo_View_Helper_Dojo_Container */
require_once 'Zend/Dojo/View/Helper/Dojo/Container.php';

/** Zend_View */
require_once 'Zend/View.php';

/**
 * Test class for Zend_Dojo_View_Helper_Dojo.
 *
 * @category   Zend
 * @package    Zend_Dojo
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Dojo_View_Helper_DojoTest extends PHPUnit_Framework_TestCase 
{
    /**
     * Runs the test methods of this class.
     *
     * @return void
     */
    public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite("Zend_Dojo_View_Helper_DojoTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        Zend_Registry::_unsetInstance();
        $this->view   = $this->getView();
        $this->helper = new Zend_Dojo_View_Helper_Dojo_Container();
        $this->helper->setView($this->view);
        Zend_Registry::set('Zend_Dojo_View_Helper_Dojo', $this->helper);
        Zend_Dojo_View_Helper_Dojo::setUseDeclarative();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
    }

    public function getView()
    {
        require_once 'Zend/View.php';
        $view = new Zend_View();
        $view->addHelperPath(dirname(__FILE__) . '/../../../../library/Zend/Dojo/View/Helper/', 'Zend_Dojo_View_Helper');
        return $view;
    }

    public function testViewPropertyShouldBeNullByDefault()
    {
        $helper = new Zend_Dojo_View_Helper_Dojo();
        $this->assertNull($helper->view);
    }

    public function testShouldBeAbleToSetViewProperty()
    {
        $this->assertTrue($this->helper->view instanceof Zend_View_Interface);
    }

    public function testNoModulesShouldBeRegisteredByDefault()
    {
        $modules = $this->helper->getModules();
        $this->assertTrue(empty($modules));
    }

    public function testShouldBeAbleToRequireModules()
    {
        $this->helper->requireModule('foo.bar');
        $modules = $this->helper->getModules();
        $this->assertContains('foo.bar', $modules);
    }

    public function testInvalidModuleNameShouldThrowExceptionDuringRegistration()
    {
        try {
            $this->helper->requireModule('foo#$!bar');
            $this->fail('Invalid module name should throw exception during registration');
        } catch (Zend_Dojo_View_Exception $e) {
            $this->assertContains('invalid character', $e->getMessage());
        }
    }

    public function testShouldNotRegisterDuplicateModules()
    {
        $this->helper->requireModule('foo.bar');
        $this->helper->requireModule('foo.bar');
        $modules = $this->helper->getModules();
        $this->assertContains('foo.bar', $modules);
        $this->assertEquals(1, count($modules));
    }

    public function testModulePathsShouldBeEmptyByDefault()
    {
        $paths = $this->helper->getModulePaths();
        $this->assertTrue(empty($paths));
    }

    public function testShouldBeAbleToRegisterModulePaths()
    {
        $this->helper->registerModulePath('custom', '../custom');
        $paths = $this->helper->getModulePaths();
        $this->assertTrue(array_key_exists('custom', $paths), var_export($paths, 1));
        $this->assertContains('../custom', $paths);
    }

    public function testShouldNotBeAbleToRegisterDuplicateModulePaths()
    {
        $this->helper->registerModulePath('custom', '../custom');
        $this->helper->registerModulePath('custom', '../custom');
        $paths = $this->helper->getModulePaths();
        $this->assertEquals(1, count($paths));
        $this->assertTrue(array_key_exists('custom', $paths));
        $this->assertContains('../custom', $paths);
    }

    public function testShouldBeDisabledByDefault()
    {
        $this->assertFalse($this->helper->isEnabled());
    }

    public function testCallingAUseMethodShouldEnableHelper()
    {
        $this->testShouldBeDisabledByDefault();
        $this->helper->setCdnVersion('1.0');
        $this->assertTrue($this->helper->isEnabled());
        $this->helper->disable();
        $this->assertFalse($this->helper->isEnabled());
        $this->helper->setLocalPath('/js/dojo/dojo.js');
        $this->assertTrue($this->helper->isEnabled());
    }

    public function testShouldUtilizeCdnByDefault()
    {
        $this->helper->enable();
        $this->assertTrue($this->helper->useCdn());
    }

    public function testShouldUseLatestVersionWhenUsingCdnByDefault()
    {
        $this->helper->enable();
        $this->assertEquals('1.1', $this->helper->getCdnVersion());
    }

    public function testShouldAllowSpecifyingDojoVersionWhenUtilizingCdn()
    {
        $this->helper->setCdnVersion('1.0');
        $this->assertEquals('1.0', $this->helper->getCdnVersion());
    }

    public function testShouldAllowSpecifyingLocalDojoInstall()
    {
        $this->helper->setLocalPath('/js/dojo/dojo.js');
        $this->assertTrue($this->helper->useLocalPath());
    }

    public function testShouldAllowSpecifyingDjConfig()
    {
        $this->helper->setDjConfig(array('parseOnLoad' => 'true'));
        $config = $this->helper->getDjConfig();
        $this->assertTrue(is_array($config));
        $this->assertTrue(array_key_exists('parseOnLoad', $config));
        $this->assertEquals('true', $config['parseOnLoad']);
    }

    public function testShouldAllowRetrievingIndividualDjConfigKeys()
    {
        $this->helper->setDjConfigOption('parseOnLoad', 'true');
        $this->assertEquals('true', $this->helper->getDjConfigOption('parseOnLoad'));
    }

    public function testGetDjConfigShouldReturnEmptyArrayByDefault()
    {
        $this->assertSame(array(), $this->helper->getDjConfig());
    }

    public function testGetDjConfigOptionShouldReturnNullWhenKeyDoesNotExist()
    {
        $this->assertNull($this->helper->getDjConfigOption('bogus'));
    }

    public function testGetDjConfigOptionShouldAllowSpecifyingDefaultValue()
    {
        $this->assertEquals('bar', $this->helper->getDjConfigOption('foo', 'bar'));
    }

    public function testDjConfigShouldSerializeToJson()
    {
        $this->helper->setDjConfigOption('parseOnLoad', true)
                     ->enable();
        $html = $this->helper->__toString();
        $this->assertContains('var djConfig = ', $html, var_export($html, 1));
        $this->assertContains('"parseOnLoad":', $html, $html);
    }

    public function testShouldAllowSpecifyingStylesheetByModuleName()
    {
        $this->helper->addStylesheetModule('dijit.themes.tundra');
        $stylesheets = $this->helper->getStylesheetModules();
        $this->assertContains('dijit.themes.tundra', $stylesheets);
    }

    public function testDuplicateStylesheetModulesShouldNotBeAllowed()
    {
        $this->helper->addStylesheetModule('dijit.themes.tundra');
        $stylesheets = $this->helper->getStylesheetModules();
        $this->assertContains('dijit.themes.tundra', $stylesheets);

        $this->helper->addStylesheetModule('dijit.themes.tundra');
        $stylesheets = $this->helper->getStylesheetModules();
        $this->assertEquals(1, count($stylesheets));
        $this->assertContains('dijit.themes.tundra', $stylesheets);
    }

    public function testInvalidStylesheetModuleNameShouldThrowException()
    {
        try {
            $this->helper->addStylesheetModule('foo/bar/baz');
            $this->fail('invalid module designation should throw exception');
        } catch (Zend_Dojo_View_Exception $e) {
            $this->assertContains('Invalid', $e->getMessage());
        }
    }

    public function testRenderingModuleStylesheetShouldProperlyCreatePaths()
    {
        $this->helper->enable()
                     ->addStylesheetModule('dijit.themes.tundra');
        $html = $this->helper->__toString();
        $this->assertContains('dijit/themes/tundra/tundra.css', $html);
    }

    public function testShouldAllowSpecifyingLocalStylesheet()
    {
        $this->helper->addStylesheet('/css/foo.css');
        $css = $this->helper->getStylesheets();
        $this->assertTrue(is_array($css));
        $this->assertContains('/css/foo.css', $css);
    }

    public function testShouldNotAllowSpecifyingDuplicateLocalStylesheets()
    {
        $this->testShouldAllowSpecifyingLocalStylesheet();
        $this->helper->addStylesheet('/css/foo.css');
        $css = $this->helper->getStylesheets();
        $this->assertTrue(is_array($css));
        $this->assertEquals(1, count($css));
        $this->assertContains('/css/foo.css', $css);
    }

    public function testShouldAllowSpecifyingOnLoadFunctionPointer()
    {
        $this->helper->addOnLoad('foo');
        $onLoad = $this->helper->getOnLoadActions();
        $this->assertTrue(is_array($onLoad));
        $this->assertEquals(1, count($onLoad));
        $action = array_shift($onLoad);
        $this->assertTrue(is_string($action));
        $this->assertEquals('foo', $action);
    }

    public function testShouldAllowCapturingOnLoadActions()
    {
        $this->helper->onLoadCaptureStart(); ?>
function() {
    bar();
    baz();
}
<?php   $this->helper->onLoadCaptureStop();
        $onLoad = $this->helper->getOnLoadActions();
        $this->assertTrue(is_array($onLoad));
        $this->assertEquals(1, count($onLoad));
        $action = array_shift($onLoad);
        $this->assertTrue(is_string($action));
        $this->assertContains('function() {', $action);
        $this->assertContains('bar();', $action);
        $this->assertContains('baz();', $action);
    }

    public function testShouldNotAllowSpecifyingDuplicateOnLoadActions()
    {
        $this->helper->addOnLoad('foo');
        $this->helper->addOnLoad('foo');
        $onLoad = $this->helper->getOnLoadActions();
        $this->assertTrue(is_array($onLoad));
        $this->assertEquals(1, count($onLoad));
        $action = array_shift($onLoad);
        $this->assertEquals('foo', $action);
    }

    public function testDojoMethodShouldReturnContainer()
    {
        $helper = new Zend_Dojo_View_Helper_Dojo();
        $this->assertSame($this->helper, $helper->dojo());
    }

    public function testHelperStorageShouldPersistBetweenViewObjects()
    {
        $view1 = $this->getView();
        $dojo1 = $view1->getHelper('dojo');
        $view2 = $this->getView();
        $dojo2 = $view1->getHelper('dojo');
        $this->assertSame($dojo1, $dojo2);
    }

    public function testSerializingToStringShouldReturnEmptyStringByDefault()
    {
        $this->assertEquals('', $this->helper->__toString());
    }

    public function testEnablingHelperShouldCauseStringSerializationToWork()
    {
        $this->setupDojo();
        $html = $this->helper->__toString();
        $doc  = new DOMDocument;
        $doc->loadHTML($html);
        $xPath = new DOMXPath($doc);
        $results = $xPath->query('//script');
        $this->assertEquals(3, $results->length);
        for ($i = 0; $i < 3; ++$i) {
            $script = $doc->saveXML($results->item($i));
            switch ($i) {
                case 0:
                    $this->assertContains('var djConfig = ', $script);
                    $this->assertContains('parseOnLoad', $script);
                    break;
                case 1:
                    $this->assertContains('src="http://o.aolcdn.com/dojo/1.1/dojo/dojo.xd.js"', $script);
                    $this->assertContains('/>', $script);
                    break;
                case 2:
                    $this->assertContains('dojo.registerModulePath("../custom")', $script, $script);
                    $this->assertContains('dojo.require("dijit.layout.ContentPane")', $script, $script);
                    $this->assertContains('dojo.require("custom.foo")', $script, $script);
                    $this->assertContains('dojo.addOnLoad(foo)', $script, $script);
                    break;
            }
        }

        $results = $xPath->query('//style');
        $this->assertEquals(1, $results->length, $html);
        $style = $doc->saveXML($results->item(0));
        $this->assertContains('@import', $style);
        $this->assertEquals(2, substr_count($style, '@import'));
        $this->assertEquals(1, substr_count($style, 'http://o.aolcdn.com/dojo/1.1/'), $style);
        $this->assertContains('css/custom.css', $style);
        $this->assertContains('dijit/themes/tundra/tundra.css', $style);
    }

    public function testStringSerializationShouldBeDoctypeAware()
    {
        $view = $this->getView();
        $view->doctype('HTML4_LOOSE');
        $this->helper->setView($view);
        $this->setupDojo();
        $html = $this->helper->__toString();
        $this->assertRegexp('|<style [^>]*>[\r\n]+\s*<!--|', $html);
        $this->assertRegexp('|<script [^>]*>[\r\n]+\s*//<!--|', $html);

        $this->helper = new Zend_Dojo_View_Helper_Dojo();
        $view->doctype('XHTML1_STRICT');
        $this->helper->setView($view);
        $this->setupDojo();
        $html = $this->helper->__toString();

        /**
         * @todo should stylesheets be escaped as CDATA when isXhtml()?
         */
        $this->assertRegexp('|<style [^>]*>[\r\n]+\s*<!--|', $html);
        $this->assertRegexp('|<script [^>]*>[\r\n]+\s*//<!\[CDATA\[|', $html);
    }

    public function testDojoHelperContainerPersistsBetweenViewObjects()
    {
        $this->setupDojo();

        $view = $this->getView();
        $this->assertNotSame($this->view, $view);
        $helper = $view->dojo();
        $this->assertSame($this->helper, $helper);
    }

    public function testShouldUseDeclarativeDijitCreationByDefault()
    {
        $this->assertTrue(Zend_Dojo_View_Helper_Dojo::useDeclarative());
    }

    public function testShouldAllowSpecifyingProgrammaticDijitCreation()
    {
        $this->testShouldUseDeclarativeDijitCreationByDefault();
        Zend_Dojo_View_Helper_Dojo::setUseProgrammatic();
        $this->assertTrue(Zend_Dojo_View_Helper_Dojo::useProgrammatic());
    }

    public function testShouldAllowSpecifyingProgrammaticDijitCreationWithNoScriptGeneration()
    {
        $this->testShouldUseDeclarativeDijitCreationByDefault();
        Zend_Dojo_View_Helper_Dojo::setUseProgrammatic(-1);
        $this->assertTrue(Zend_Dojo_View_Helper_Dojo::useProgrammatic());
        $this->assertTrue(Zend_Dojo_View_Helper_Dojo::useProgrammaticNoScript());
    }

    public function setupDojo()
    {
        $this->helper->requireModule('dijit.layout.ContentPane')
                     ->registerModulePath('custom', '../custom')
                     ->requireModule('custom.foo')
                     ->setCdnVersion('1.1')
                     ->setDjConfig(array('parseOnLoad' => 'true'))
                     ->addStylesheetModule('dijit.themes.tundra')
                     ->addStylesheet('/css/custom.css')
                     ->addOnLoad('foo');
    }
}

// Call Zend_Dojo_View_Helper_DojoTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "Zend_Dojo_View_Helper_DojoTest::main") {
    Zend_Dojo_View_Helper_DojoTest::main();
}
