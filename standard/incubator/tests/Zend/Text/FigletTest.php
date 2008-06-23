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
 * @package    Zend_Text
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: XmlTest.php 9670 2008-06-11 08:51:21Z dasprid $
 */

/**
 * Test helper
 */
require_once dirname(__FILE__) . '/../../TestHelper.php';

/**
 * Zend_Text_Figlet
 */
require_once 'Zend/Text/Figlet.php';

/**
 * @category   Zend
 * @package    Zend_Text
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Text_FigletTest extends PHPUnit_Framework_TestCase
{
    public function testStandardAlignLeft()
    {
        $figlet = new Zend_Text_Figlet();

        $this->_equalAgainstFile($figlet->render('Dummy'), 'StandardAlignLeft.figlet');
    }

    public function testStandardAlignCenter()
    {
        $figlet = new Zend_Text_Figlet(null, array('justification' => Zend_Text_Figlet::JUSTIFICATION_CENTER));

        $this->_equalAgainstFile($figlet->render('Dummy'), 'StandardAlignCenter.figlet');
    }

    public function testStandardAlignRight()
    {
        $figlet = new Zend_Text_Figlet(null, array('justification' => Zend_Text_Figlet::JUSTIFICATION_RIGHT));

        $this->_equalAgainstFile($figlet->render('Dummy'), 'StandardAlignRight.figlet');
    }

    public function testStandardRightToLeftAlignLeft()
    {
        $figlet = new Zend_Text_Figlet(null, array('justification' => Zend_Text_Figlet::JUSTIFICATION_LEFT,
                                                   'rightToLeft'   => Zend_Text_Figlet::DIRECTION_RIGHT_TO_LEFT));

        $this->_equalAgainstFile($figlet->render('Dummy'), 'StandardRightToLeftAlignLeft.figlet');
    }

    public function testStandardRightToLeftAlignCenter()
    {
        $figlet = new Zend_Text_Figlet(null, array('justification' => Zend_Text_Figlet::JUSTIFICATION_CENTER,
                                                   'rightToLeft'   => Zend_Text_Figlet::DIRECTION_RIGHT_TO_LEFT));

        $this->_equalAgainstFile($figlet->render('Dummy'), 'StandardRightToLeftAlignCenter.figlet');
    }

    public function testStandardRightToLeftAlignRight()
    {
        $figlet = new Zend_Text_Figlet(null, array('rightToLeft' => Zend_Text_Figlet::DIRECTION_RIGHT_TO_LEFT));

        $this->_equalAgainstFile($figlet->render('Dummy'), 'StandardRightToLeftAlignRight.figlet');
    }

    protected function _equalAgainstFile($output, $file)
    {
        $compareString = file_get_contents(dirname(__FILE__) . '/Figlet/' . $file);

        $this->assertEquals($compareString, $output);
    }
}
