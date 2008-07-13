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
 * @package    Zend_Validate_File
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: $
 */

/**
 * Test helper
 */
require_once dirname(__FILE__) . '/../../../TestHelper.php';

/**
 * @see Zend_Validate_File_Upload
 */
require_once 'Zend/Validate/File/Upload.php';

/**
 * @category   Zend
 * @package    Zend_Validate_File
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_File_UploadTest extends PHPUnit_Framework_TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $_FILES = array(
            'test'  => array(
                'name'     => 'test1',
                'type'     => 'text',
                'size'     => 200,
                'tmp_name' => 'tmp_test1',
                'error'    => 0),
            'test2' => array(
                'name'     => 'test2',
                'type'     => 'text2',
                'size'     => 202,
                'tmp_name' => 'tmp_test2',
                'error'    => 1));

        $validator = new Zend_Validate_File_Upload();
        $this->assertTrue($validator->isValid('tmp_test1'));
    }

    /**
     * Ensures that getFiles() returns expected value
     *
     * @return void
     */
    public function testGetFiles()
    {
        $validator = new Zend_Validate_File_Upload(1, 100);
        $this->assertEquals('1B', $validator->getFiles());

        try {
            $validator = new Zend_Validate_File_Upload(100, 1);
            $this->fail("Missing exception");
        } catch (Zend_Validate_Exception $e) {
            $this->assertContains("greater than or equal", $e->getMessage());
        }

        $validator = new Zend_Validate_File_Upload(array(1, 100));
        $this->assertEquals('1B', $validator->getFiles());

        try {
            $validator = new Zend_Validate_File_Upload(array(100, 1));
            $this->fail("Missing exception");
        } catch (Zend_Validate_Exception $e) {
            $this->assertContains("greater than or equal", $e->getMessage());
        }
    }

    /**
     * Ensures that setFiles() returns expected value
     *
     * @return void
     */
    public function testSetFiles()
    {
        $validator = new Zend_Validate_File_Upload(1000, 10000);
        $validator->setFiles(100);
        $this->assertEquals('100B', $validator->getFiles());

        try {
            $validator->setFiles(20000);
            $this->fail("Missing exception");
        } catch (Zend_Validate_Exception $e) {
            $this->assertContains("less than or equal", $e->getMessage());
        }
    }
}
