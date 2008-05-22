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
 * @package    Zend_Gdata
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Gdata/Base.php';
require_once 'Zend/Http/Client.php';
require_once 'Zend/Http/Client/Adapter/Test.php';

/**
 * @package Zend_Gdata
 * @subpackage UnitTests
 */
class Zend_Gdata_BaseTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $testAdapter = new Zend_Http_Client_Adapter_Test();
        $client = new Zend_Http_Client(null, array('adapter' => $testAdapter));
        $this->gdata = new Zend_Gdata_Base($client);
    }

    public function testDeveloperKey()
    {
        $key = "foo";
        $this->gdata->setDeveloperKey($key);
        $this->assertEquals($key, $this->gdata->getDeveloperKey());

        $key = "split-header\nattack";
        $this->gdata->setDeveloperKey($key);
        $this->assertEquals("split-header", $this->gdata->getDeveloperKey());

        $key = "foo";
        $this->gdata = new Zend_Gdata_Base($this->gdata->getHttpClient(), $key);
        $this->assertEquals($key, $this->gdata->getDeveloperKey());
    }

    public function testQueryParam()
    {
        $this->gdata->resetParameters();
        $query = 'digital camera';
        $this->gdata->setQuery($query);
        $this->assertTrue(isset($this->gdata->query));
        $this->assertEquals($query, $this->gdata->getQuery());
        unset($this->gdata->query);
        $this->assertFalse(isset($this->gdata->query));
    }

    public function testAltRssParam()
    {
        $this->gdata->resetParameters();
        $query = 'digital camera';
        $alt = 'rss';
        $this->gdata->setQuery($query);
        $this->gdata->setAlt($alt);
        $this->assertTrue(isset($this->gdata->alt));
        $this->assertEquals($alt, $this->gdata->getAlt());
        unset($this->gdata->alt);
        $this->assertFalse(isset($this->gdata->alt));
    }

    public function testCategoryParam()
    {
        $this->gdata->resetParameters();
        $query = 'nikon';
        $category = 'camera';
        $this->gdata->setQuery($query);
        $this->gdata->setCategory($category);
        $this->assertTrue(isset($this->gdata->category));
        $this->assertEquals($category, $this->gdata->getCategory());
        unset($this->gdata->category);
        $this->assertFalse(isset($this->gdata->category));
    }

    public function testMaxResultsParam()
    {
        $this->gdata->resetParameters();
        $query = 'digital camera';
        $max = 3;
        $this->gdata->setQuery($query);
        $this->gdata->setMaxResults($max);
        $this->assertTrue(isset($this->gdata->maxResults));
        $this->assertEquals($max, $this->gdata->getMaxResults());
        unset($this->gdata->maxResults);
        $this->assertFalse(isset($this->gdata->maxResults));
    }

    public function testOrderbyParam()
    {
        $this->gdata->resetParameters();
        $orderby = 'starttime';
        $this->gdata->setOrderby($orderby);
        $this->assertTrue(isset($this->gdata->orderby));
        $this->assertEquals($orderby, $this->gdata->getOrderby());
        unset($this->gdata->orderby);
        $this->assertFalse(isset($this->gdata->orderby));
    }

    public function testStartIndexParam()
    {
        $this->gdata->resetParameters();
        $query = 'digital camera';
        $start = 3;
        $this->gdata->setQuery($query);
        $this->gdata->setStartIndex($start);
        $this->assertTrue(isset($this->gdata->startIndex));
        $this->assertEquals($start, $this->gdata->getStartIndex());
        unset($this->gdata->startIndex);
        $this->assertFalse(isset($this->gdata->startIndex));
    }

    public function testAddAttributeQuery()
    {
        $this->gdata->resetParameters();
        $query = 'digital camera';
        $attrib = 'price';
        $op = '<';
        $attribValue = '50 USD';
        $this->gdata->setQuery('digital camera');
        $this->gdata->addAttributeQuery($attrib, $attribValue, $op);
        $this->gdata->maxResults = 25;
        $this->gdata->unsetAttributeQuery();
    }

    public function testExceptionInvalidAttributeQueryOperator()
    {
        $this->gdata->resetParameters();
        $attrib = 'price';
        $op = '?';
        $attribValue = '50 USD';
        try {
            $this->gdata->addAttributeQuery($attrib, $attribValue, $op);
            $this->fail('Expected to catch Zend_Gdata_InvalidArgumentException');
        } catch (Zend_Gdata_Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('Zend_Gdata_InvalidArgumentException'),
                'Expected Zend_Gdata_InvalidArgumentException, got '.get_class($e));
            $this->assertEquals("Unsupported attribute query comparison operator '?'.", $e->getMessage());
        }
    }

}
