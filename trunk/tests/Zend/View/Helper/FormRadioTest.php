<?php
// Call Zend_View_Helper_FormRadioTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Zend_View_Helper_FormRadioTest::main");
}

require_once dirname(__FILE__) . '/../../../TestHelper.php';

require_once 'Zend/View/Helper/FormRadio.php';
require_once 'Zend/View.php';

/**
 * Zend_View_Helper_FormRadioTest 
 *
 * Tests formRadio helper
 * 
 * @uses PHPUnit_Framework_TestCase
 */
class Zend_View_Helper_FormRadioTest extends PHPUnit_Framework_TestCase 
{
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("Zend_View_Helper_FormRadioTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    public function setUp()
    {
        $this->view   = new Zend_View();
        $this->helper = new Zend_View_Helper_FormRadio();
        $this->helper->setView($this->view);
    }

    public function testRendersRadioLabelsWhenRenderingMultipleOptions()
    {
        $options = array(
            'foo' => 'Foo',
            'bar' => 'Bar',
            'baz' => 'Baz'
        );
        $html = $this->helper->formRadio(array(
            'name'    => 'foo',
            'value'   => 'bar',
            'options' => $options,
        ));
        foreach ($options as $key => $value) {
            $this->assertRegexp('#<label.*?>.*?' . $value . '.*?</label>#', $html, $html);
            $this->assertRegexp('#<label.*?>.*?<input.*?</label>#', $html, $html);
        }
    }

    public function testCanSpecifyRadioLabelPlacement()
    {
        $options = array(
            'foo' => 'Foo',
            'bar' => 'Bar',
            'baz' => 'Baz'
        );
        $html = $this->helper->formRadio(array(
            'name'    => 'foo',
            'value'   => 'bar',
            'options' => $options,
            'attribs' => array('labelPlacement' => 'append')
        ));
        foreach ($options as $key => $value) {
            $this->assertRegexp('#<label.*?>.*?<input .*?' . $value . '</label>#', $html, $html);
        }

        $html = $this->helper->formRadio(array(
            'name'    => 'foo',
            'value'   => 'bar',
            'options' => $options,
            'attribs' => array('labelPlacement' => 'prepend')
        ));
        foreach ($options as $key => $value) {
            $this->assertRegexp('#<label.*?>' . $value . '<input .*?</label>#', $html, $html);
        }
    }

    public function testCanSpecifyRadioLabelAttribs()
    {
        $options = array(
            'foo' => 'Foo',
            'bar' => 'Bar',
            'baz' => 'Baz'
        );
        $html = $this->helper->formRadio(array(
            'name'    => 'foo',
            'value'   => 'bar',
            'options' => $options,
            'attribs' => array('labelClass' => 'testclass', 'label_id' => 'testid')
        ));

        foreach ($options as $key => $value) {
            $this->assertRegexp('#<label[^>]*?class="testclass"[^>]*>.*?' . $value . '#', $html, $html);
            $this->assertRegexp('#<label[^>]*?id="testid"[^>]*>.*?' . $value . '#', $html, $html);
        }
    }

    public function testCanSpecifyRadioSeparator()
    {
        $options = array(
            'foo' => 'Foo',
            'bar' => 'Bar',
            'baz' => 'Baz'
        );
        $html = $this->helper->formRadio(array(
            'name'    => 'foo',
            'value'   => 'bar',
            'options' => $options,
            'listsep' => '--FunkySep--',
        ));

        $this->assertContains('--FunkySep--', $html);
        $count = substr_count($html, '--FunkySep--');
        $this->assertEquals(2, $count);
    }

    /**
     * ZF-2513
     */
    public function testCanDisableAllRadios()
    {
        $options = array(
            'foo' => 'Foo',
            'bar' => 'Bar',
            'baz' => 'Baz'
        );
        $html = $this->helper->formRadio(array(
            'name'    => 'foo',
            'value'   => 'bar',
            'options' => $options,
            'attribs' => array('disable' => true)
        ));

        $this->assertRegexp('/<input[^>]*?(disabled="disabled")/', $html, $html);
        $count = substr_count($html, 'disabled="disabled"');
        $this->assertEquals(3, $count);
    }

    /**
     * ZF-2513
     */
    public function testCanDisableIndividualRadios()
    {
        $options = array(
            'foo' => 'Foo',
            'bar' => 'Bar',
            'baz' => 'Baz'
        );
        $html = $this->helper->formRadio(array(
            'name'    => 'foo',
            'value'   => 'bar',
            'options' => $options,
            'attribs' => array('disable' => array('bar'))
        ));

        $this->assertRegexp('/<input[^>]*?(value="bar")[^>]*(disabled="disabled")/', $html, $html);
        $count = substr_count($html, 'disabled="disabled"');
        $this->assertEquals(1, $count);
    }

    /**
     * ZF-2513
     */
    public function testCanDisableMultipleRadios()
    {
        $options = array(
            'foo' => 'Foo',
            'bar' => 'Bar',
            'baz' => 'Baz'
        );
        $html = $this->helper->formRadio(array(
            'name'    => 'foo',
            'value'   => 'bar',
            'options' => $options,
            'attribs' => array('disable' => array('foo', 'baz'))
        ));

        foreach (array('foo', 'baz') as $test) {
            $this->assertRegexp('/<input[^>]*?(value="' . $test . '")[^>]*?(disabled="disabled")/', $html, $html);
        }
        $this->assertNotRegexp('/<input[^>]*?(value="bar")[^>]*?(disabled="disabled")/', $html, $html);
        $count = substr_count($html, 'disabled="disabled"');
        $this->assertEquals(2, $count);
    }

    public function testLabelsAreEscapedByDefault()
    {
        $options = array(
            'bar' => '<b>Bar</b>',
        );
        $html = $this->helper->formRadio(array(
            'name'    => 'foo',
            'options' => $options,
        ));

        $this->assertNotContains($options['bar'], $html);
        $this->assertContains('&lt;b&gt;Bar&lt;/b&gt;', $html);
    }

    public function testXhtmlLabelsAreAllowed()
    {
        $options = array(
            'bar' => '<b>Bar</b>',
        );
        $html = $this->helper->formRadio(array(
            'name'    => 'foo',
            'options' => $options,
            'attribs' => array('escape' => false)
        ));

        $this->assertContains($options['bar'], $html);
    }

    /**
     * ZF-1666
     */
    public function testDoesNotRenderHiddenElements()
    {
        $options = array(
            'foo' => 'Foo',
            'bar' => 'Bar',
            'baz' => 'Baz'
        );
        $html = $this->helper->formRadio(array(
            'name'    => 'foo',
            'options' => $options,
        ));

        $this->assertNotRegexp('/<input[^>]*?(type="hidden")/', $html);
    }
}

// Call Zend_View_Helper_FormRadioTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "Zend_View_Helper_FormRadioTest::main") {
    Zend_View_Helper_FormRadioTest::main();
}

