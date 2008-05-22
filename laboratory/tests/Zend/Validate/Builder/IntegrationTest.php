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
 * @package    Zend_Validate_Builder
 * @subpackage UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/TestHelper.php';

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'Zend/Validate/Builder.php';
require_once 'Zend/Validate/Builder/FluentFacade.php';
require_once 'Zend/Validate/Builder/ErrorManager.php';
require_once 'Zend/Validate/StringLength.php';


/**
 * @category   Zend
 * @package    Zend_Validate_Builder
 * @subpackage UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Builder_IntegrationTest extends PHPUnit_Framework_TestCase
{
    public function testZvbClassesReportDefaultValidatorMsgs()
    {
        $testData1 = array(
            'field1' => 'This string is 34 characters long.',
            'field2' => 'However, this string contains 44 characters.',
        );

        $zvb = new Zend_Validate_Builder;
        $ff  = new Zend_Validate_Builder_FluentFacade($zvb);

        $ff->field1->stringLength(40);
        $ff->field2->stringLength(30, 40);

        if (!$ff->isValid($testData1)) {

            // This sucks because of the tight coupling to StringLength's 
            // specific message strings, but I can't think of a better way to do 
            // this at the moment...
            $expected = array(
                'field1' => array(
                    'Zend_Validate_StringLength' =>
                        array(
                            'stringLengthTooShort' =>
                            "'This string is 34 characters long.' is less than 40 characters long"
                        ),
                    ),
                'field2' => array(
                    'Zend_Validate_StringLength' =>
                        array(
                            'stringLengthTooLong' =>
                            "'However, this string contains 44 characters.' is greater than 40 characters long"
                        ),
                    ),
                );
            $actual = $ff->getMessages();

            $this->assertEquals($expected, $actual);

        } else {
            $this->fail('Failed to catch invalid fields');
        }
    }

    public function testZvbClassesReportCustomMsgsWithErrorManager()
    {
        $testData1 = array(
            'field1' => 'This string is 34 characters long.',
            'field2' => 'However, this string contains 44 characters.',
        );

        $em  = new Zend_Validate_Builder_ErrorManager;
        $em->setTemplate('field1', 'Zend_Validate_StringLength', 'stringLengthTooShort', '\'%value%\' should be longer than %min% chars');
        $em->setTemplate('field2', 'Zend_Validate_StringLength', 'stringLengthTooLong', '\'%value%\' should not be longer than %max% chars');

        $zvb = new Zend_Validate_Builder;
        $zvb->setErrorManager($em);

        $ff  = new Zend_Validate_Builder_FluentFacade($zvb);

        $ff->field1->stringLength(40);
        $ff->field2->stringLength(30, 40);

        if (!$ff->isValid($testData1)) {

            $expected = array(
                'field1' => array(
                    'Zend_Validate_StringLength' => array(
                        'stringLengthTooShort' => "'This string is 34 characters long.' should be longer than 40 chars"
                        ),
                    ),
                'field2' => array(
                    'Zend_Validate_StringLength' => array(
                        'stringLengthTooLong' => "'However, this string contains 44 characters.' should not be longer than 40 chars"
                        ),
                    ),
                );
            $actual = $ff->getMessages();

            $this->assertEquals($expected, $actual);

        } else {
            $this->fail('Failed to catch invalid fields');
        }
    }
}
