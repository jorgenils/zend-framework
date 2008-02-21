<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Zend_Form_ElementTest::main');
}

require_once dirname(__FILE__) . '/../../TestHelper.php';

// error_reporting(E_ALL);

require_once 'Zend/Form/Element.php';

require_once 'Zend/Config.php';
require_once 'Zend/Controller/Action/HelperBroker.php';
require_once 'Zend/Form.php';
require_once 'Zend/Form/Decorator/Abstract.php';
require_once 'Zend/Loader/PluginLoader.php';
require_once 'Zend/Translate.php';
require_once 'Zend/Validate/NotEmpty.php';
require_once 'Zend/Validate/EmailAddress.php';
require_once 'Zend/View.php';

class Zend_Form_ElementTest extends PHPUnit_Framework_TestCase
{
    public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite('Zend_Form_ElementTest');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    public function setUp()
    {
        Zend_Form::setDefaultTranslator(null);

        if (isset($this->error)) {
            unset($this->error);
        }

        $this->element = new Zend_Form_Element('foo');
        Zend_Controller_Action_HelperBroker::resetHelpers();
    }

    public function tearDown()
    {
    }

    public function getView()
    {
        $view = new Zend_View();
        $libPath = dirname(__FILE__) . '/../../../library';
        $view->addHelperPath($libPath . '/Zend/View/Helper');
        return $view;
    }

    public function testConstructorRequiresMinimallyElementName()
    {
        try {
            $element = new Zend_Form_Element(1);
            $this->fail('Zend_Form_Element constructor should not accept integer argument');
        } catch (Zend_Form_Exception $e) {
        }
        try {
            $element = new Zend_Form_Element(true);
            $this->fail('Zend_Form_Element constructor should not accept boolean argument');
        } catch (Zend_Form_Exception $e) {
        }

        try {
            $element = new Zend_Form_Element('foo');
        } catch (Exception $e) {
            $this->fail('Zend_Form_Element constructor should accept String values');
        }

        $config = array('foo' => 'bar');
        try {
            $element = new Zend_Form_Element($config);
            $this->fail('Zend_Form_Element constructor requires array with name element');
        } catch (Zend_Form_Exception $e) {
        }

        $config = array('name' => 'bar');
        try {
            $element = new Zend_Form_Element($config);
        } catch (Zend_Form_Exception $e) {
            $this->fail('Zend_Form_Element constructor should accept array with name element');
        }

        $config = new Zend_Config(array('foo' => 'bar'));
        try {
            $element = new Zend_Form_Element($config);
            $this->fail('Zend_Form_Element constructor requires Zend_Config object with name element');
        } catch (Zend_Form_Exception $e) {
        }

        $config = new Zend_Config(array('name' => 'bar'));
        try {
            $element = new Zend_Form_Element($config);
        } catch (Zend_Form_Exception $e) {
            $this->fail('Zend_Form_Element constructor should accept Zend_Config with name element');
        }
    }

    public function testNoTranslatorByDefault()
    {
        $this->assertNull($this->element->getTranslator());
    }

    public function testGetTranslatorRetrievesGlobalDefaultWhenAvailable()
    {
        $this->testNoTranslatorByDefault();
        $translator = new Zend_Translate('array', array());
        Zend_Form::setDefaultTranslator($translator);
        $received = $this->element->getTranslator();
        $this->assertSame($translator->getAdapter(), $received);
    }

    public function testTranslatorAccessorsWork()
    {
        $translator = new Zend_Translate('array', array());
        $this->element->setTranslator($translator);
        $received = $this->element->getTranslator($translator);
        $this->assertSame($translator->getAdapter(), $received);
    }

    public function testSetNameNormalizesValueToContainOnlyValidVariableCharacters()
    {
        $this->element->setName('f%\o^&*)o\(%$b#@!.a}{;-,r');
        $this->assertEquals('foobar', $this->element->getName());

        try {
            $this->element->setName('%\^&*)\(%$#@!.}{;-,');
            $this->fail('Empty names should raise exception');
        } catch (Zend_Form_Exception $e) {
            $this->assertContains('Invalid name provided', $e->getMessage());
        }
    }

    public function testZeroIsAllowedAsElementName()
    {
        try {
            $this->element->setName(0);
            $this->assertSame('0', $this->element->getName());
        } catch (Zend_Form_Exception $e) {
            $this->fail('Should allow zero as element name');
        }
    }

    public function testElementValueInitiallyNull()
    {
        $this->assertNull($this->element->getValue());
    }

    public function testValueAccessorsWork()
    {
        $this->element->setValue('bar');
        $this->assertContains('bar', $this->element->getValue());
    }

    public function testGetValueFiltersValue()
    {
        $this->element->setValue('This 0 is 1 a-2-TEST')
                      ->addFilter('alnum')
                      ->addFilter('stringToUpper');
        $test = $this->element->getValue();
        $this->assertEquals('THIS0IS1A2TEST', $test);
    }

    public function checkFilterValues($item, $key)
    {
        $this->assertRegexp('/^[A-Z]+$/', $item);
    }

    public function testRetrievingArrayValueFiltersAllArrayValues()
    {
        $this->element->setValue(array(
                    'foo',
                    array(
                        'bar',
                        'baz'
                    ),
                    'bat'
                ))
             ->addFilter('StringToUpper');
        $test = $this->element->getValue();
        $this->assertTrue(is_array($test));
        array_walk_recursive($test, array($this, 'checkFilterValues'));
    }

    public function testGetUnfilteredValueRetrievesOriginalValue()
    {
        $this->element->setValue('bar');
        $this->assertSame('bar', $this->element->getUnfilteredValue());
    }

    public function testLabelInitiallyNull()
    {
        $this->assertNull($this->element->getLabel());
    }

    public function testLabelAccessorsWork()
    {
        $this->element->setLabel('FooBar');
        $this->assertEquals('FooBar', $this->element->getLabel());
    }

    public function testOrderNullByDefault()
    {
        $this->assertNull($this->element->getOrder());
    }

    public function testCanSetOrder()
    {
        $this->testOrderNullByDefault();
        $this->element->setOrder(50);
        $this->assertEquals(50, $this->element->getOrder());
    }

    public function testRequiredFlagFalseByDefault()
    {
        $this->assertFalse($this->element->isRequired());
    }

    public function testRequiredAcccessorsWork()
    {
        $this->assertFalse($this->element->isRequired());
        $this->element->setRequired(true);
        $this->assertTrue($this->element->isRequired());
    }

    public function testDescriptionInitiallyNull()
    {
        $this->assertNull($this->element->getDescription());
    }

    public function testCanSetDescription()
    {
        $this->testDescriptionInitiallyNull();
        $this->element->setDescription('element hint');
        $this->assertEquals('element hint', $this->element->getDescription());
    }

    public function testElementIsNotArrayByDefault()
    {
        $this->assertFalse($this->element->isArray());
    }

    public function testCanSetArrayFlag()
    {
        $this->testElementIsNotArrayByDefault();
        $this->element->setIsArray(true);
        $this->assertTrue($this->element->isArray());
        $this->element->setIsArray(false);
        $this->assertFalse($this->element->isArray());
    }

    public function testElementBelongsToNullByDefault()
    {
        $this->assertNull($this->element->getBelongsTo());
    }

    public function testCanSetArrayElementBelongsTo()
    {
        $this->testElementBelongsToNullByDefault();
        $this->element->setBelongsTo('foo');
        $this->assertEquals('foo', $this->element->getBelongsTo());
    }

    public function testArrayElementBelongsToNormalizedToValidVariableCharactersOnly()
    {
        $this->testElementBelongsToNullByDefault();
        $this->element->setBelongsTo('f%\o^&*)o\(%$b#@!.a}{;-,r');
        $this->assertEquals('foobar', $this->element->getBelongsTo());
    }

    public function testGetTypeReturnsCurrentElementClass()
    {
        $this->assertEquals('Zend_Form_Element', $this->element->getType());
    }

    public function testCanUseAccessorsToSetIndidualAttribs()
    {
        $this->element->setAttrib('foo', 'bar')
                      ->setAttrib('bar', 'baz')
                      ->setAttrib('baz', 'bat');

        $this->assertEquals('bar', $this->element->getAttrib('foo'));
        $this->assertEquals('baz', $this->element->getAttrib('bar'));
        $this->assertEquals('bat', $this->element->getAttrib('baz'));
    }

    public function testGetUndefinedAttribShouldReturnNull()
    {
        $this->assertNull($this->element->getAttrib('bogus'));
    }

    public function testSetAttribThrowsExceptionsForKeysWithLeadingUnderscores()
    {
        try {
            $this->element->setAttrib('_foo', 'bar');
            $this->fail('setAttrib() should throw an exception for invalid keys');
        } catch (Zend_Form_Exception $e) {
            $this->assertContains('Invalid attribute', $e->getMessage());
        }
    }

    public function testPassingNullValueToSetAttribUnsetsAttrib()
    {
        $this->element->setAttrib('foo', 'bar');
        $this->assertEquals('bar', $this->element->getAttrib('foo'));
        $this->element->setAttrib('foo', null);
        $this->assertFalse(isset($this->element->foo));
    }

    public function testSetAttribsSetsMultipleAttribs()
    {
        $this->element->setAttribs(array(
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'bat'
        ));

        $this->assertEquals('bar', $this->element->getAttrib('foo'));
        $this->assertEquals('baz', $this->element->getAttrib('bar'));
        $this->assertEquals('bat', $this->element->getAttrib('baz'));
    }

    public function testGetAttribsRetrievesAllAttributes()
    {
        $attribs = array(
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'bat'
        );
        $this->element->setAttribs($attribs);

        $attribs['helper'] = 'formText';

        $received = $this->element->getAttribs();
        $this->assertEquals($attribs, $received);
    }

    public function testPassingNullValuesToSetAttribsUnsetsAttribs()
    {
        $this->testSetAttribsSetsMultipleAttribs();
        $this->element->setAttribs(array('foo' => null));
        $this->assertNull($this->element->foo);
    }

    public function testRetrievingOverloadedValuesThrowsExceptionWithInvalidKey()
    {
        try {
            $name = $this->element->_name;
            $this->fail('Overloading should not return protected or private members');
        } catch (Zend_Form_Exception $e) {
            $this->assertContains('Cannot retrieve value for protected/private', $e->getMessage());
        }
    }

    public function testCanSetAndRetrieveAttribsViaOverloading()
    {
        $this->element->foo = 'bar';
        $this->assertEquals('bar', $this->element->foo);
    }

    public function testGetPluginLoaderRetrievesDefaultValidatorPluginLoader()
    {
        $loader = $this->element->getPluginLoader('validate');
        $this->assertTrue($loader instanceof Zend_Loader_PluginLoader);
        $paths = $loader->getPaths('Zend_Validate');
        $this->assertTrue(is_array($paths), var_export($loader, 1));
        $this->assertTrue(0 < count($paths));
        $this->assertContains('Validate', $paths[0]);
    }

    public function testGetPluginLoaderRetrievesDefaultFilterPluginLoader()
    {
        $loader = $this->element->getPluginLoader('filter');
        $this->assertTrue($loader instanceof Zend_Loader_PluginLoader);
        $paths = $loader->getPaths('Zend_Filter');
        $this->assertTrue(is_array($paths));
        $this->assertTrue(0 < count($paths));
        $this->assertContains('Filter', $paths[0]);
    }

    public function testGetPluginLoaderRetrievesDefaultDecoratorPluginLoader()
    {
        $loader = $this->element->getPluginLoader('decorator');
        $this->assertTrue($loader instanceof Zend_Loader_PluginLoader);
        $paths = $loader->getPaths('Zend_Form_Decorator');
        $this->assertTrue(is_array($paths));
        $this->assertTrue(0 < count($paths));
        $this->assertContains('Decorator', $paths[0]);
    }

    public function testCanSetCustomValidatorPluginLoader()
    {
        $loader = new Zend_Loader_PluginLoader();
        $this->element->setPluginLoader($loader, 'validate');
        $test = $this->element->getPluginLoader('validate');
        $this->assertSame($loader, $test);
    }

    public function testPassingInvalidTypeToSetPluginLoaderThrowsException()
    {
        $loader = new Zend_Loader_PluginLoader();
        try {
            $this->element->setPluginLoader($loader, 'foo');
            $this->fail('Invalid loader type should raise exception');
        } catch (Zend_Form_Exception $e) {
            $this->assertContains('Invalid type', $e->getMessage());
        }
    }

    public function testPassingInvalidTypeToGetPluginLoaderThrowsException()
    {
        try {
            $this->element->getPluginLoader('foo');
            $this->fail('Invalid loader type should raise exception');
        } catch (Zend_Form_Exception $e) {
            $this->assertContains('Invalid type', $e->getMessage());
        }
    }

    public function testCanSetCustomFilterPluginLoader()
    {
        $loader = new Zend_Loader_PluginLoader();
        $this->element->setPluginLoader($loader, 'filter');
        $test = $this->element->getPluginLoader('filter');
        $this->assertSame($loader, $test);
    }

    public function testCanSetCustomDecoratorPluginLoader()
    {
        $loader = new Zend_Loader_PluginLoader();
        $this->element->setPluginLoader($loader, 'decorator');
        $test = $this->element->getPluginLoader('decorator');
        $this->assertSame($loader, $test);
    }

    public function testPassingInvalidLoaderTypeToAddPrefixPathThrowsException()
    {
        try {
            $this->element->addPrefixPath('Zend_Foo', 'Zend/Foo/', 'foo');
            $this->fail('Invalid loader type should raise exception');
        } catch (Zend_Form_Exception $e) {
            $this->assertContains('Invalid type', $e->getMessage());
        }
    }

    public function testCanAddValidatorPluginLoaderPrefixPath()
    {
        $loader = $this->element->getPluginLoader('validate');
        $this->element->addPrefixPath('Zend_Form', 'Zend/Form/', 'validate');
        $paths = $loader->getPaths('Zend_Form');
        $this->assertTrue(is_array($paths));
        $this->assertContains('Form', $paths[0]);
    }

    public function testAddingValidatorPluginLoaderPrefixPathDoesNotAffectOtherLoaders()
    {
        $validateLoader  = $this->element->getPluginLoader('validate');
        $filterLoader    = $this->element->getPluginLoader('filter');
        $decoratorLoader = $this->element->getPluginLoader('decorator');
        $this->element->addPrefixPath('Zend_Form', 'Zend/Form/', 'validate');
        $this->assertFalse($filterLoader->getPaths('Zend_Form'));
        $this->assertFalse($decoratorLoader->getPaths('Zend_Form'));
    }

    public function testCanAddFilterPluginLoaderPrefixPath()
    {
        $loader = $this->element->getPluginLoader('validate');
        $this->element->addPrefixPath('Zend_Form', 'Zend/Form/', 'validate');
        $paths = $loader->getPaths('Zend_Form');
        $this->assertTrue(is_array($paths));
        $this->assertContains('Form', $paths[0]);
    }

    public function testAddingFilterPluginLoaderPrefixPathDoesNotAffectOtherLoaders()
    {
        $filterLoader    = $this->element->getPluginLoader('filter');
        $validateLoader  = $this->element->getPluginLoader('validate');
        $decoratorLoader = $this->element->getPluginLoader('decorator');
        $this->element->addPrefixPath('Zend_Form', 'Zend/Form/', 'filter');
        $this->assertFalse($validateLoader->getPaths('Zend_Form'));
        $this->assertFalse($decoratorLoader->getPaths('Zend_Form'));
    }

    public function testCanAddDecoratorPluginLoaderPrefixPath()
    {
        $loader = $this->element->getPluginLoader('decorator');
        $this->element->addPrefixPath('Zend_Foo', 'Zend/Foo/', 'decorator');
        $paths = $loader->getPaths('Zend_Foo');
        $this->assertTrue(is_array($paths));
        $this->assertContains('Foo', $paths[0]);
    }

    public function testAddingDecoratorrPluginLoaderPrefixPathDoesNotAffectOtherLoaders()
    {
        $decoratorLoader = $this->element->getPluginLoader('decorator');
        $filterLoader    = $this->element->getPluginLoader('filter');
        $validateLoader  = $this->element->getPluginLoader('validate');
        $this->element->addPrefixPath('Zend_Foo', 'Zend/Foo/', 'decorator');
        $this->assertFalse($validateLoader->getPaths('Zend_Foo'));
        $this->assertFalse($filterLoader->getPaths('Zend_Foo'));
    }

    public function testCanAddAllPluginLoaderPrefixPathsSimultaneously()
    {
        $validatorLoader = new Zend_Loader_PluginLoader();
        $filterLoader    = new Zend_Loader_PluginLoader();
        $decoratorLoader = new Zend_Loader_PluginLoader();
        $this->element->setPluginLoader($validatorLoader, 'validate')
                      ->setPluginLoader($filterLoader, 'filter')
                      ->setPluginLoader($decoratorLoader, 'decorator')
                      ->addPrefixPath('Zend', 'Zend/');

        $paths = $filterLoader->getPaths('Zend_Filter');
        $this->assertTrue(is_array($paths));
        $this->assertContains('Filter', $paths[0]);

        $paths = $validatorLoader->getPaths('Zend_Validate');
        $this->assertTrue(is_array($paths));
        $this->assertContains('Validate', $paths[0]);

        $paths = $decoratorLoader->getPaths('Zend_Decorator');
        $this->assertTrue(is_array($paths), var_export($paths, 1));
        $this->assertContains('Decorator', $paths[0]);
    }

    public function testPassingInvalidValidatorToAddValidatorThrowsException()
    {
        try {
            $this->element->addValidator(123);
            $this->fail('Invalid validator should raise exception');
        } catch (Zend_Form_Exception $e) {
            $this->assertContains('Invalid validator', $e->getMessage());
        }
    }

    public function testCanAddSingleValidatorAsString()
    {
        $this->assertFalse($this->element->getValidator('digits'));

        $this->element->addValidator('digits');
        $validator = $this->element->getValidator('digits');
        $this->assertTrue($validator instanceof Zend_Validate_Digits);
        $this->assertFalse($validator->zfBreakChainOnFailure);
    }

    public function testCanRetrieveSingleValidatorRegisteredAsStringUsingClassName()
    {
        $this->assertFalse($this->element->getValidator('digits'));

        $this->element->addValidator('digits');
        $validator = $this->element->getValidator('Zend_Validate_Digits');
        $this->assertTrue($validator instanceof Zend_Validate_Digits);
        $this->assertFalse($validator->zfBreakChainOnFailure);
    }

    public function testCanAddSingleValidatorAsValidatorObject()
    {
        $this->assertFalse($this->element->getValidator('Zend_Validate_Digits'));

        require_once 'Zend/Validate/Digits.php';
        $validator = new Zend_Validate_Digits();
        $this->element->addValidator($validator);
        $test = $this->element->getValidator('Zend_Validate_Digits');
        $this->assertSame($validator, $test);
        $this->assertFalse($validator->zfBreakChainOnFailure);
    }

    public function testCanRetrieveSingleValidatorRegisteredAsValidatorObjectUsingShortName()
    {
        $this->assertFalse($this->element->getValidator('digits'));

        require_once 'Zend/Validate/Digits.php';
        $validator = new Zend_Validate_Digits();
        $this->element->addValidator($validator);
        $test = $this->element->getValidator('digits');
        $this->assertSame($validator, $test);
        $this->assertFalse($validator->zfBreakChainOnFailure);
    }

    public function testCanAddMultipleValidators()
    {
        $this->assertFalse($this->element->getValidator('Zend_Validate_Digits'));
        $this->assertFalse($this->element->getValidator('Zend_Validate_Alnum'));
        $this->element->addValidators(array('digits', 'alnum'));
        $digits = $this->element->getValidator('digits');
        $this->assertTrue($digits instanceof Zend_Validate_Digits);
        $alnum  = $this->element->getValidator('alnum');
        $this->assertTrue($alnum instanceof Zend_Validate_Alnum);
    }

    public function testRemovingUnregisteredValidatorReturnsFalse()
    {
        $this->assertFalse($this->element->removeValidator('bogus'));
    }

    public function testPassingMessagesOptionToAddValidatorSetsValidatorMessages()
    {
        $messageTemplates = array(
            Zend_Validate_Digits::NOT_DIGITS   => 'Value should only contain digits',
            Zend_Validate_Digits::STRING_EMPTY => 'Value needs some digits',
        );
        $this->element->setAllowEmpty(false)
                      ->addValidator('digits', false, array('messages' => $messageTemplates));

        $this->element->isValid('');
        $messages = $this->element->getMessages();
        $found    = false;
        foreach ($messages as $key => $message) {
            if ($key == Zend_Validate_Digits::STRING_EMPTY) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Empty string message not found: ' . var_export($messages, 1));
        $this->assertEquals($messageTemplates[Zend_Validate_Digits::STRING_EMPTY], $message);

        $this->element->isValid('abc');
        $messages = $this->element->getMessages();
        $found    = false;
        foreach ($messages as $key => $message) {
            if ($key == Zend_Validate_Digits::NOT_DIGITS) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Not digits message not found');
        $this->assertEquals($messageTemplates[Zend_Validate_Digits::NOT_DIGITS], $message);
    }

    public function testMessagesAreTranslatedForCurrentLocale()
    {
        $localeFile   = dirname(__FILE__) . '/_files/locale/array.php';
        $translations = include($localeFile);
        $translator   = new Zend_Translate('array', $translations, 'en');
        $translator->setLocale('en');

        $this->element->setAllowEmpty(false)
                      ->setTranslator($translator)
                      ->addValidator('digits');

        $this->element->isValid('');
        $messages = $this->element->getMessages();
        $found    = false;
        foreach ($messages as $key => $message) {
            if ($key == 'stringEmpty') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'String Empty message not found: ' . var_export($messages, 1));
        $this->assertEquals($translations['stringEmpty'], $message);

        $this->element->isValid('abc');
        $messages = $this->element->getMessages();
        $found    = false;
        foreach ($messages as $key => $message) {
            if ($key == 'notDigits') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Not Digits message not found');
        $this->assertEquals($translations['notDigits'], $message);
    }

    /** ZF-2568 */
    public function testTranslatedMessagesCanContainVariableSubstitution()
    {
        $localeFile   = dirname(__FILE__) . '/_files/locale/array.php';
        $translations = include($localeFile);
        $translations['notDigits'] .= ' "%value%"';
        $translator   = new Zend_Translate('array', $translations, 'en');
        $translator->setLocale('en');

        $this->element->setAllowEmpty(false)
                      ->setTranslator($translator)
                      ->addValidator('digits');

        $this->element->isValid('abc');
        $messages = $this->element->getMessages();
        $found    = false;
        foreach ($messages as $key => $message) {
            if ($key == 'notDigits') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'String Empty message not found: ' . var_export($messages, 1));
        $this->assertContains(' "abc"', $message);
        $this->assertContains('Translating the notDigits string', $message);
    }

    public function testCanRemoveValidator()
    {
        $this->assertFalse($this->element->getValidator('Zend_Validate_Digits'));
        $this->element->addValidator('digits');
        $digits = $this->element->getValidator('digits');
        $this->assertTrue($digits instanceof Zend_Validate_Digits);
        $this->element->removeValidator('digits');
        $this->assertFalse($this->element->getValidator('digits'));
    }

    public function testCanClearAllValidators()
    {
        $this->testCanAddMultipleValidators();
        $validators = $this->element->getValidators();
        $this->element->clearValidators();
        $test = $this->element->getValidators();
        $this->assertNotEquals($validators, $test);
        $this->assertTrue(empty($test));
        foreach (array_keys($validators) as $validator) {
            $this->assertFalse($this->element->getValidator($validator));
        }
    }

    public function testCanValidateElement()
    {
        $this->element->addValidator(new Zend_Validate_NotEmpty())
                      ->addValidator(new Zend_Validate_EmailAddress());
        try {
            $result = $this->element->isValid('matthew@zend.com');
        } catch (Exception $e) {
            $this->fail('Validating an element should work');
        }
    }

    public function testCanValidateArrayValue()
    {
        $this->element->setIsArray(true)
             ->addValidator('InArray', false, array(array('foo', 'bar', 'baz', 'bat')));
        $this->assertTrue($this->element->isValid(array('foo', 'bat')));
    }

    public function testIsValidPopulatesElementValue()
    {
        $this->testCanValidateElement();
        $this->assertEquals('matthew@zend.com', $this->element->getValue());
    }

    public function testErrorsPopulatedFollowingFailedIsValidCheck()
    {
        $this->element->addValidator(new Zend_Validate_NotEmpty())
                      ->addValidator(new Zend_Validate_EmailAddress());

        $result = $this->element->isValid('matthew');
        if ($result) {
            $this->fail('Invalid data should fail validations');
        }
        $errors = $this->element->getErrors();
        $this->assertTrue(is_array($errors));
        $this->assertTrue(0 < count($errors));
    }

    public function testMessagesPopulatedFollowingFailedIsValidCheck()
    {
        require_once 'Zend/Validate/NotEmpty.php';
        require_once 'Zend/Validate/EmailAddress.php';
        $this->element->addValidator(new Zend_Validate_NotEmpty())
                      ->addValidator(new Zend_Validate_EmailAddress());

        $result = $this->element->isValid('matthew');
        if ($result) {
            $this->fail('Invalid data should fail validations');
        }
        $messages = $this->element->getMessages();
        $this->assertTrue(is_array($messages));
        $this->assertTrue(0 < count($messages));
    }

    public function testOptionalElementDoesNotPerformValidationsOnEmptyValuesByDefault()
    {
        $this->element->addValidator(new Zend_Validate_EmailAddress());

        $result = $this->element->isValid('');
        if (!$result) {
            $this->fail('Empty data should not fail validations');
        }
        $errors = $this->element->getErrors();
        $this->assertTrue(is_array($errors));
        $this->assertTrue(empty($errors));
    }

    public function testOptionalElementDoesPerformValidationsWhenAllowEmptyIsFalse()
    {
        $this->element->setAllowEmpty(false)
                      ->addValidator(new Zend_Validate_EmailAddress());

        $result = $this->element->isValid('');
        if ($result) {
            $this->fail('Empty data should fail validations when AllowEmpty is false');
        }
        $errors = $this->element->getErrors();
        $this->assertTrue(is_array($errors));
        $this->assertTrue(0 < count($errors));
    }

    public function testAddingInvalidFilterTypeThrowsException()
    {
        try {
            $this->element->addFilter(123);
            $this->fail('Invalid filter type should raise exception');
        } catch (Zend_Form_Exception $e) {
            $this->assertContains('Invalid filter', $e->getMessage());
        }
    }

    public function testCanAddSingleFilterAsString()
    {
        $this->assertFalse($this->element->getFilter('digits'));

        $this->element->addFilter('digits');
        $filter = $this->element->getFilter('digits');
        $this->assertTrue($filter instanceof Zend_Filter_Digits);
    }

    public function testCanRetrieveSingleFilterRegisteredAsStringUsingClassName()
    {
        $this->assertFalse($this->element->getFilter('digits'));

        $this->element->addFilter('digits');
        $filter = $this->element->getFilter('Zend_Filter_Digits');
        $this->assertTrue($filter instanceof Zend_Filter_Digits);
    }

    public function testCanAddSingleFilterAsFilterObject()
    {
        $this->assertFalse($this->element->getFilter('Zend_Filter_Digits'));

        require_once 'Zend/Filter/Digits.php';
        $filter = new Zend_Filter_Digits();
        $this->element->addFilter($filter);
        $test = $this->element->getFilter('Zend_Filter_Digits');
        $this->assertSame($filter, $test);
    }

    public function testCanRetrieveSingleFilterRegisteredAsFilterObjectUsingShortName()
    {
        $this->assertFalse($this->element->getFilter('digits'));

        require_once 'Zend/Filter/Digits.php';
        $filter = new Zend_Filter_Digits();
        $this->element->addFilter($filter);
        $test = $this->element->getFilter('digits');
    }

    public function testCanAddMultipleFilters()
    {
        $this->assertFalse($this->element->getFilter('Zend_Filter_Digits'));
        $this->assertFalse($this->element->getFilter('Zend_Filter_Alnum'));
        $this->element->addFilters(array('digits', 'alnum'));
        $digits = $this->element->getFilter('digits');
        $this->assertTrue($digits instanceof Zend_Filter_Digits);
        $alnum  = $this->element->getFilter('alnum');
        $this->assertTrue($alnum instanceof Zend_Filter_Alnum);
    }

    public function testRemovingUnregisteredFilterReturnsFalse()
    {
        $this->assertFalse($this->element->removeFilter('bogus'));
    }

    public function testCanRemoveFilter()
    {
        $this->assertFalse($this->element->getFilter('Zend_Filter_Digits'));
        $this->element->addFilter('digits');
        $digits = $this->element->getFilter('digits');
        $this->assertTrue($digits instanceof Zend_Filter_Digits);
        $this->element->removeFilter('digits');
        $this->assertFalse($this->element->getFilter('digits'));
    }

    public function testCanClearAllFilters()
    {
        $this->testCanAddMultipleFilters();
        $filters = $this->element->getFilters();
        $this->element->clearFilters();
        $test = $this->element->getFilters();
        $this->assertNotEquals($filters, $test);
        $this->assertTrue(empty($test));
        foreach (array_keys($filters) as $filter) {
            $this->assertFalse($this->element->getFilter($filter));
        }
    }

    public function testGetViewReturnsNullWithNoViewRenderer()
    {
        $this->assertNull($this->element->getView());
    }

    public function testGetViewReturnsViewRendererViewInstanceIfViewRendererActive()
    {
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->initView();
        $view = $viewRenderer->view;
        $test = $this->element->getView();
        $this->assertSame($view, $test);
    }

    public function testCanSetView()
    {
        $view = new Zend_View();
        $this->assertNull($this->element->getView());
        $this->element->setView($view);
        $received = $this->element->getView();
        $this->assertSame($view, $received);
    }

    public function testViewHelperDecoratorRegisteredByDefault()
    {
        $decorator = $this->element->getDecorator('viewHelper');
        $this->assertTrue($decorator instanceof Zend_Form_Decorator_ViewHelper);
    }

    public function testAddingInvalidDecoratorThrowsException()
    {
        try {
            $this->element->addDecorator(123);
            $this->fail('Invalid decorator type should raise exception');
        } catch (Zend_Form_Exception $e) {
            $this->assertContains('Invalid decorator', $e->getMessage());
        }
    }

    public function testCanAddSingleDecoratorAsString()
    {
        $this->element->clearDecorators();
        $this->assertFalse($this->element->getDecorator('viewHelper'));

        $this->element->addDecorator('viewHelper');
        $decorator = $this->element->getDecorator('viewHelper');
        $this->assertTrue($decorator instanceof Zend_Form_Decorator_ViewHelper);
    }

    public function testCanRetrieveSingleDecoratorRegisteredAsStringUsingClassName()
    {
        $decorator = $this->element->getDecorator('Zend_Form_Decorator_ViewHelper');
        $this->assertTrue($decorator instanceof Zend_Form_Decorator_ViewHelper);
    }

    public function testCanAddSingleDecoratorAsDecoratorObject()
    {
        $this->element->clearDecorators();
        $this->assertFalse($this->element->getDecorator('viewHelper'));

        $decorator = new Zend_Form_Decorator_ViewHelper;
        $this->element->addDecorator($decorator);
        $test = $this->element->getDecorator('Zend_Form_Decorator_ViewHelper');
        $this->assertSame($decorator, $test);
    }

    public function testCanRetrieveSingleDecoratorRegisteredAsDecoratorObjectUsingShortName()
    {
        $this->element->clearDecorators();
        $this->assertFalse($this->element->getDecorator('viewHelper'));

        $decorator = new Zend_Form_Decorator_ViewHelper;
        $this->element->addDecorator($decorator);
        $test = $this->element->getDecorator('viewHelper');
        $this->assertSame($decorator, $test);
    }

    public function testCanAddMultipleDecorators()
    {
        $this->element->clearDecorators();
        $this->assertFalse($this->element->getDecorator('viewHelper'));

        $testDecorator = new Zend_Form_ElementTest_Decorator;
        $this->element->addDecorators(array(
            'ViewHelper',
            $testDecorator
        ));

        $viewHelper = $this->element->getDecorator('viewHelper');
        $this->assertTrue($viewHelper instanceof Zend_Form_Decorator_ViewHelper);
        $decorator = $this->element->getDecorator('decorator');
        $this->assertSame($testDecorator, $decorator);
    }

    public function testRemovingUnregisteredDecoratorReturnsFalse()
    {
        $this->assertFalse($this->element->removeDecorator('bogus'));
    }

    public function testCanRemoveDecorator()
    {
        $this->testViewHelperDecoratorRegisteredByDefault();
        $this->element->removeDecorator('viewHelper');
        $this->assertFalse($this->element->getDecorator('viewHelper'));
    }

    public function testCanClearAllDecorators()
    {
        $this->testCanAddMultipleDecorators();
        $this->element->clearDecorators();
        $this->assertFalse($this->element->getDecorator('viewHelper'));
        $this->assertFalse($this->element->getDecorator('decorator'));
    }

    public function testCanAddDecoratorAliasesToAllowMultipleDecoratorsOfSameType()
    {
        $this->element->setDecorators(array(
            array('HtmlTag', array('tag' => 'span')),
            array('decorator' => array('FooBar' => 'HtmlTag'), 'options' => array('tag' => 'div')),
        ));
        $decorator = $this->element->getDecorator('FooBar');
        $this->assertTrue($decorator instanceof Zend_Form_Decorator_HtmlTag);
        $this->assertEquals('div', $decorator->getOption('tag'));

        $decorator = $this->element->getDecorator('HtmlTag');
        $this->assertTrue($decorator instanceof Zend_Form_Decorator_HtmlTag);
        $this->assertEquals('span', $decorator->getOption('tag'));
    }

    public function testRenderElementReturnsMarkup()
    {
        $this->element->setName('foo');
        $html = $this->element->render($this->getView());
        $this->assertTrue(is_string($html));
        $this->assertFalse(empty($html));
        $this->assertContains('<input', $html);
        $this->assertContains('"foo"', $html);
    }

    public function testRenderElementRendersLabelWhenProvided()
    {
        $this->element->setView($this->getView());
        $this->element->setName('foo')
                      ->setLabel('Foo');
        $html = $this->element->render();
        $this->assertTrue(is_string($html));
        $this->assertFalse(empty($html));
        $this->assertContains('<label', $html);
        $this->assertContains('Foo', $html);
        $this->assertContains('</label>', $html);
    }

    public function testRenderElementRendersValueWhenProvided()
    {
        $this->element->setView($this->getView());
        $this->element->setName('foo')
                      ->setValue('bar');
        $html = $this->element->render();
        $this->assertTrue(is_string($html));
        $this->assertFalse(empty($html));
        $this->assertContains('<input', $html);
        $this->assertContains('"foo"', $html);
        $this->assertContains('"bar"', $html);
    }

    public function testRenderElementRendersErrorsWhenProvided()
    {
        $this->element->setView($this->getView())
                      ->setRequired(true)
                      ->setName('foo')
                      ->addValidator('NotEmpty');
        $this->element->isValid('');

        $html = $this->element->render();
        $this->assertTrue(is_string($html));
        $this->assertFalse(empty($html));
        $this->assertContains('error', $html);
        $this->assertRegexp('/empty/i', $html);
    }

    public function testToStringProxiesToRender()
    {
        $this->element->setView($this->getView());
        $this->element->setName('foo');
        $html = $this->element->__toString();
        $this->assertTrue(is_string($html));
        $this->assertFalse(empty($html));
        $this->assertContains('<input', $html);
        $this->assertContains('"foo"', $html);
    }

    public function raiseDecoratorException($content, $element, $options)
    {
        throw new Exception('Raising exception in decorator callback');
    }

    public function handleDecoratorErrors($errno, $errstr, $errfile = '', $errline = 0, array $errcontext = array())
    {
        $this->error = $errstr;
    }

    public function testToStringRaisesErrorWhenExceptionCaught()
    {
        $this->element->setDecorators(array(
            array(
                'decorator' => 'Callback', 
                'options'   => array('callback' => array($this, 'raiseDecoratorException'))
            ),
        ));
        $origErrorHandler = set_error_handler(array($this, 'handleDecoratorErrors'), E_USER_WARNING);

        $text = $this->element->__toString();

        restore_error_handler();

        $this->assertTrue(empty($text));
        $this->assertTrue(isset($this->error));
        $this->assertEquals('Raising exception in decorator callback', $this->error);
    }

    public function getOptions()
    {
        $options = array(
            'name'     => 'changed',
            'value'    => 'foo',
            'label'    => 'bar',
            'order'    => 50,
            'required' => false,
            'foo'      => 'bar',
            'baz'      => 'bat'
        );
        return $options;
    }

    public function testCanSetObjectStateViaSetOptions()
    {
        $options = $this->getOptions();
        $this->element->setOptions($options);
        $this->assertEquals('changed', $this->element->getName());
        $this->assertEquals('foo', $this->element->getValue());
        $this->assertEquals('bar', $this->element->getLabel());
        $this->assertEquals(50, $this->element->getOrder());
        $this->assertFalse($this->element->isRequired());
        $this->assertEquals('bar', $this->element->foo);
        $this->assertEquals('bat', $this->element->baz);
    }

    public function testSetOptionsSkipsCallsToSetOptionsAndSetConfig()
    {
        $options = $this->getOptions();
        $options['config']  = new Zend_Config($options);
        $options['options'] = $options;
        $this->element->setOptions($options);
    }

    public function testSetOptionsSkipsSettingAccessorsRequiringObjectsWhenNoObjectPresent()
    {
        $options = $this->getOptions();
        $options['translator'] = true;
        $options['pluginLoader'] = true;
        $options['view'] = true;
        $this->element->setOptions($options);
    }

    public function testSetOptionsSetsArrayOfStringValidators()
    {
        $options = $this->getOptions();
        $options['validators'] = array(
            'notEmpty',
            'digits'
        );
        $this->element->setOptions($options);
        $validator = $this->element->getValidator('notEmpty');
        $this->assertTrue($validator instanceof Zend_Validate_NotEmpty);
        $validator = $this->element->getValidator('digits');
        $this->assertTrue($validator instanceof Zend_Validate_Digits);
    }

    public function testSetOptionsSetsArrayOfArrayValidators()
    {
        $options = $this->getOptions();
        $options['validators'] = array(
            array('notEmpty', true, array('bar')),
            array('digits', true, array('bar')),
        );
        $this->element->setOptions($options);
        $validator = $this->element->getValidator('notEmpty');
        $this->assertTrue($validator instanceof Zend_Validate_NotEmpty);
        $this->assertTrue($validator->zfBreakChainOnFailure);
        $validator = $this->element->getValidator('digits');
        $this->assertTrue($validator instanceof Zend_Validate_Digits);
        $this->assertTrue($validator->zfBreakChainOnFailure);
    }

    public function testSetOptionsSetsArrayOfAssociativeArrayValidators()
    {
        $options = $this->getOptions();
        $options['validators'] = array(
            array(
                'options'             => array('bar'),
                'breakChainOnFailure' => true, 
                'validator'           => 'notEmpty', 
            ),
            array(
                'options'             => array('bar'),
                'validator'           => 'digits', 
                'breakChainOnFailure' => true, 
            ),
        );
        $this->element->setOptions($options);
        $validator = $this->element->getValidator('notEmpty');
        $this->assertTrue($validator instanceof Zend_Validate_NotEmpty);
        $this->assertTrue($validator->zfBreakChainOnFailure);
        $validator = $this->element->getValidator('digits');
        $this->assertTrue($validator instanceof Zend_Validate_Digits);
        $this->assertTrue($validator->zfBreakChainOnFailure);
    }

    public function testSetOptionsSetsArrayOfStringFilters()
    {
        $options = $this->getOptions();
        $options['filters'] = array('StringToUpper', 'Alpha');
        $this->element->setOptions($options);
        $filter = $this->element->getFilter('StringToUpper');
        $this->assertTrue($filter instanceof Zend_Filter_StringToUpper);
        $filter = $this->element->getFilter('Alpha');
        $this->assertTrue($filter instanceof Zend_Filter_Alpha);
    }

    public function testSetOptionsSetsArrayOfArrayFilters()
    {
        $options = $this->getOptions();
        $options['filters'] = array(
            array('StringToUpper', array('bar' => 'baz')),
            array('Alpha', array('foo')),
        );
        $this->element->setOptions($options);
        $filter = $this->element->getFilter('StringToUpper');
        $this->assertTrue($filter instanceof Zend_Filter_StringToUpper);
        $filter = $this->element->getFilter('Alpha');
        $this->assertTrue($filter instanceof Zend_Filter_Alpha);
    }

    public function testSetOptionsSetsArrayOfAssociativeArrayFilters()
    {
        $options = $this->getOptions();
        $options['filters'] = array(
            array(
                'options' => array('baz'),
                'filter'  => 'StringToUpper'
            ),
            array(
                'options' => array('foo'),
                'filter'  => 'Alpha', 
            ),
        );
        $this->element->setOptions($options);
        $filter = $this->element->getFilter('StringToUpper');
        $this->assertTrue($filter instanceof Zend_Filter_StringToUpper);
        $filter = $this->element->getFilter('Alpha');
        $this->assertTrue($filter instanceof Zend_Filter_Alpha);
    }

    public function testSetOptionsSetsArrayOfStringDecorators()
    {
        $options = $this->getOptions();
        $options['decorators'] = array('label', 'form');
        $this->element->setOptions($options);
        $this->assertFalse($this->element->getDecorator('viewHelper'));
        $this->assertFalse($this->element->getDecorator('errors'));
        $decorator = $this->element->getDecorator('label');
        $this->assertTrue($decorator instanceof Zend_Form_Decorator_Label);
        $decorator = $this->element->getDecorator('form');
        $this->assertTrue($decorator instanceof Zend_Form_Decorator_Form);
    }

    public function testSetOptionsSetsArrayOfArrayDecorators()
    {
        $options = $this->getOptions();
        $options['decorators'] = array(
            array('label', array('id' => 'mylabel')),
            array('form', array('id' => 'form')),
        );
        $this->element->setOptions($options);
        $this->assertFalse($this->element->getDecorator('viewHelper'));
        $this->assertFalse($this->element->getDecorator('errors'));

        $decorator = $this->element->getDecorator('label');
        $this->assertTrue($decorator instanceof Zend_Form_Decorator_Label);
        $options = $decorator->getOptions();
        $this->assertEquals('mylabel', $options['id']);

        $decorator = $this->element->getDecorator('form');
        $this->assertTrue($decorator instanceof Zend_Form_Decorator_Form);
        $options = $decorator->getOptions();
        $this->assertEquals('form', $options['id']);
    }

    public function testSetOptionsSetsArrayOfAssocArrayDecorators()
    {
        $options = $this->getOptions();
        $options['decorators'] = array(
            array(
                'options'   => array('id' => 'mylabel'),
                'decorator' => 'label', 
            ),
            array(
                'options'   => array('id' => 'form'),
                'decorator' => 'form', 
            ),
        );
        $this->element->setOptions($options);
        $this->assertFalse($this->element->getDecorator('viewHelper'));
        $this->assertFalse($this->element->getDecorator('errors'));

        $decorator = $this->element->getDecorator('label');
        $this->assertTrue($decorator instanceof Zend_Form_Decorator_Label);
        $options = $decorator->getOptions();
        $this->assertEquals('mylabel', $options['id']);

        $decorator = $this->element->getDecorator('form');
        $this->assertTrue($decorator instanceof Zend_Form_Decorator_Form);
        $options = $decorator->getOptions();
        $this->assertEquals('form', $options['id']);
    }

    public function testSetOptionsSetsGlobalPrefixPaths()
    {
        $options = $this->getOptions();
        $options['prefixPath'] = array(
            'prefix' => 'Zend_Foo',
            'path'   => 'Zend/Foo/'
        );
        $this->element->setOptions($options);

        foreach (array('validate', 'filter', 'decorator') as $type) {
            $loader = $this->element->getPluginLoader($type);
            $paths = $loader->getPaths('Zend_Foo_' . ucfirst($type));
            $this->assertTrue(is_array($paths), "Failed for type $type: " . var_export($paths, 1));
            $this->assertFalse(empty($paths));
            $this->assertContains('Foo', $paths[0]);
        }
    }

    public function testSetOptionsSetsIndividualPrefixPathsFromKeyedArrays()
    {
        $options = $this->getOptions();
        $options['prefixPath'] = array(
            'filter' => array('prefix' => 'Zend_Foo', 'path' => 'Zend/Foo/')
        );
        $this->element->setOptions($options);

        $loader = $this->element->getPluginLoader('filter');
        $paths = $loader->getPaths('Zend_Foo');
        $this->assertTrue(is_array($paths));
        $this->assertFalse(empty($paths));
        $this->assertContains('Foo', $paths[0]);
    }

    public function testSetOptionsSetsIndividualPrefixPathsFromUnKeyedArrays()
    {
        $options = $this->getOptions();
        $options['prefixPath'] = array(
            array('type' => 'decorator', 'prefix' => 'Zend_Foo', 'path' => 'Zend/Foo/')
        );
        $this->element->setOptions($options);

        $loader = $this->element->getPluginLoader('decorator');
        $paths = $loader->getPaths('Zend_Foo');
        $this->assertTrue(is_array($paths));
        $this->assertFalse(empty($paths));
        $this->assertContains('Foo', $paths[0]);
    }

    public function testCanSetObjectStateViaSetConfig()
    {
        $config = new Zend_Config($this->getOptions());
        $this->element->setConfig($config);
        $this->assertEquals('changed', $this->element->getName());
        $this->assertEquals('foo', $this->element->getValue());
        $this->assertEquals('bar', $this->element->getLabel());
        $this->assertEquals(50, $this->element->getOrder());
        $this->assertFalse($this->element->isRequired());
        $this->assertEquals('bar', $this->element->foo);
        $this->assertEquals('bat', $this->element->baz);
    }

    public function testPassingConfigObjectToConstructorSetsObjectState()
    {
        $config = new Zend_Config($this->getOptions());
        $element = new Zend_Form_Element($config);
        $this->assertEquals('changed', $element->getName());
        $this->assertEquals('foo', $element->getValue());
        $this->assertEquals('bar', $element->getLabel());
        $this->assertEquals(50, $element->getOrder());
        $this->assertFalse($element->isRequired());
        $this->assertEquals('bar', $element->foo);
        $this->assertEquals('bat', $element->baz);
    }

    public function testValueIsFilteredPriorToValidation()
    {
        $this->element->addFilter('StringTrim')
                      ->addValidator('StringLength', false, array(3, 8));
        $this->assertTrue($this->element->isValid('  foobar  '));
        $this->assertEquals('foobar', $this->element->getValue());

        $this->element->setFilters(array('StringTrim'))
                      ->setRequired(true)
                      ->setValidators(array('NotEmpty'));
        $this->assertFalse($this->element->isValid('    '));
    }
}

class Zend_Form_ElementTest_Decorator extends Zend_Form_Decorator_Abstract
{
}

if (PHPUnit_MAIN_METHOD == 'Zend_Form_ElementTest::main') {
    Zend_Form_ElementTest::main();
}
