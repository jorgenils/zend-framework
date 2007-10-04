<?php
// Call Zend_View_Helper_Placeholder_Container_AbstractTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    $base = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))));
    $paths = array(
        $base . '/incubator/tests',
        $base . '/incubator/library',
        $base . '/library'
    );
    set_include_path(implode(PATH_SEPARATOR, $paths) . PATH_SEPARATOR . get_include_path());
    define("PHPUnit_MAIN_METHOD", "Zend_View_Helper_Placeholder_ContainerTest::main");
}

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

/** Zend_View_Helper_Placeholder_Container */
require_once 'Zend/View/Helper/Placeholder/Container.php';

/**
 * Test class for Zend_View_Helper_Placeholder_Container.
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage UnitTests
 */
class Zend_View_Helper_Placeholder_ContainerTest extends PHPUnit_Framework_TestCase 
{
    /**
     * @var Zend_View_Helper_Placeholder_Container
     */
    public $container;

    /**
     * Runs the test methods of this class.
     *
     * @return void
     */
    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("Zend_View_Helper_Placeholder_ContainerTest");
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
        $this->container = new Zend_View_Helper_Placeholder_Container(array());
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->container);
    }

    /**
     * @return void
     */
    public function testSetSetsASingleValue()
    {
        $this->container['foo'] = 'bar';
        $this->container['bar'] = 'baz';
        $this->assertEquals('bar', $this->container['foo']);
        $this->assertEquals('baz', $this->container['bar']);

        $this->container->set('foo');
        $this->assertEquals(1, count($this->container));
        $this->assertEquals('foo', $this->container[0]);
    }

    /**
     * @return void
     */
    public function testGetValueReturnsScalarWhenOneElementRegistered()
    {
        $this->container->set('foo');
        $this->assertEquals('foo', $this->container->getValue());
    }

    /**
     * @return void
     */
    public function testGetValueReturnsArrayWhenMultipleValuesPresent()
    {
        $this->container['foo'] = 'bar';
        $this->container['bar'] = 'baz';
        $expected = array('foo' => 'bar', 'bar' => 'baz');
        $return   = $this->container->getValue();
        $this->assertEquals($expected, $return);
    }

    /**
     * @return void
     */
    public function testPrefixAccesorsWork()
    {
        $this->assertEquals('', $this->container->getPrefix());
        $this->container->setPrefix('<ul><li>');
        $this->assertEquals('<ul><li>', $this->container->getPrefix());
    }

    /**
     * @return void
     */
    public function testSetPrefixImplementsFluentInterface()
    {
        $result = $this->container->setPrefix('<ul><li>');
        $this->assertSame($this->container, $result);
    }

    /**
     * @return void
     */
    public function testPostfixAccesorsWork()
    {
        $this->assertEquals('', $this->container->getPostfix());
        $this->container->setPostfix('</li></ul>');
        $this->assertEquals('</li></ul>', $this->container->getPostfix());
    }

    /**
     * @return void
     */
    public function testSetPostfixImplementsFluentInterface()
    {
        $result = $this->container->setPostfix('</li></ul>');
        $this->assertSame($this->container, $result);
    }

    /**
     * @return void
     */
    public function testSeparatorAccesorsWork()
    {
        $this->assertEquals('', $this->container->getSeparator());
        $this->container->setSeparator('</li><li>');
        $this->assertEquals('</li><li>', $this->container->getSeparator());
    }

    /**
     * @return void
     */
    public function testSetSeparatorImplementsFluentInterface()
    {
        $result = $this->container->setSeparator('</li><li>');
        $this->assertSame($this->container, $result);
    }

    /**
     * @return void
     */
    public function testCapturingToPlaceholderStoresContent()
    {
        $this->container->captureStart();
        echo 'This is content intended for capture';
        $this->container->captureEnd();

        $value = $this->container->getValue();
        $this->assertContains('This is content intended for capture', $value);
    }

    /**
     * @return void
     */
    public function testCapturingToPlaceholderAppendsContent()
    {
        $this->container[] = 'foo';
        $this->container->captureStart();
        echo 'This is content intended for capture';
        $this->container->captureEnd();

        $this->assertEquals(2, count($this->container));

        $value = $this->container->getValue();
        $this->assertEquals('foo', $value[0]);
        $this->assertContains('This is content intended for capture', $value[1]);
    }

    /**
     * @return void
     */
    public function testCapturingToPlaceholderUsingSetOverwritesContent()
    {
        $this->container[] = 'foo';
        $this->container->captureStart('set');
        echo 'This is content intended for capture';
        $this->container->captureEnd();

        $this->assertEquals(1, count($this->container));

        $value = $this->container->getValue();
        $this->assertContains('This is content intended for capture', $value);
    }

    /**
     * @return void
     */
    public function testNestedCapturesThrowsException()
    {
        $this->container[] = 'foo';
        $caught = false;
        try {
            $this->container->captureStart('set');
                $this->container->captureStart('set');
                $this->container->captureEnd();
            $this->container->captureEnd();
        } catch (Exception $e) {
            $caught = true;
        }

        $this->assertTrue($caught, 'Nested captures should throw exceptions');
    }

    /**
     * @return void
     */
    public function testToStringWithNoModifiersAndSingleValueReturnsValue()
    {
        $this->container->set('foo');
        $value = $this->container->toString();
        $this->assertEquals($this->container->getValue(), $value);
    }

    /**
     * @return void
     */
    public function testToStringWithModifiersAndSingleValueReturnsFormattedValue()
    {
        $this->container->set('foo');
        $this->container->setPrefix('<li>')
                        ->setPostfix('</li>');
        $value = $this->container->toString();
        $this->assertEquals('<li>foo</li>', $value);
    }

    /**
     * @return void
     */
    public function testToStringWithNoModifiersAndCollectionReturnsImplodedString()
    {
        $this->container[] = 'foo';
        $this->container[] = 'bar';
        $this->container[] = 'baz';
        $value = $this->container->toString();
        $this->assertEquals('foobarbaz', $value);
    }

    /**
     * @return void
     */
    public function testToStringWithModifiersAndCollectionReturnsFormattedString()
    {
        $this->container[] = 'foo';
        $this->container[] = 'bar';
        $this->container[] = 'baz';
        $this->container->setPrefix('<ul><li>')
                        ->setSeparator('</li><li>')
                        ->setPostfix('</li></ul>');
        $value = $this->container->toString();
        $this->assertEquals('<ul><li>foo</li><li>bar</li><li>baz</li></ul>', $value);
    }

    /**
     * @return void
     */
    public function test__toStringProxiesToToString()
    {
        $this->container[] = 'foo';
        $this->container[] = 'bar';
        $this->container[] = 'baz';
        $this->container->setPrefix('<ul><li>')
                        ->setSeparator('</li><li>')
                        ->setPostfix('</li></ul>');
        $value = $this->container->__toString();
        $this->assertEquals('<ul><li>foo</li><li>bar</li><li>baz</li></ul>', $value);
    }
}

// Call Zend_View_Helper_Placeholder_ContainerTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "Zend_View_Helper_Placeholder_ContainerTest::main") {
    Zend_View_Helper_Placeholder_ContainerTest::main();
}