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
 * @package    UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/TestHelper.php';

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'Zend/Filter/Builder.php';
require_once 'Zend/Filter/Builder/FluentFacade.php';
require_once 'Zend/Filter/StringTrim.php';


/**
 * @category   Zend
 * @package    Zend_Filter_Builder
 * @subpackage UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Builder_IntegrationTest extends PHPUnit_Framework_TestCase
{
    public function testAllClassesGetCalled()
    {
        $testData1 = array(
            'field1' => '  Spaces on either side   ',
            'field2' => 'Needs trimming            ',
        );

        $zfb = new Zend_Filter_Builder;
        $ff  = new Zend_Filter_Builder_FluentFacade($zfb);

        $ff->field1->stringTrim();
        $ff->field2->stringTrim();

        $actual = $ff->filter($testData1);

        $expected = array(
            'field1' => 'Spaces on either side',
            'field2' => 'Needs trimming',
        );

        $this->assertEquals($expected, $actual);
    }
}
