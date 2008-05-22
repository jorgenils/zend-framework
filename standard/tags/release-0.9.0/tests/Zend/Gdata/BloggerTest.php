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

require_once 'Zend/Gdata/Blogger.php';
require_once 'Zend/Http/Client.php';
require_once 'Zend/Http/Client/Adapter/Test.php';

/**
 * @package Zend_Gdata
 * @subpackage UnitTests
 */
class Zend_Gdata_BloggerTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $testAdapter = new Zend_Http_Client_Adapter_Test();
        $client = new Zend_Http_Client(null, array('adapter' => $testAdapter));
        $this->gdata = new Zend_Gdata_Blogger($client);
    }

    public function testBlogFeed()
    {
        $this->gdata->resetParameters();
        $blog = 'karwin';
        $this->gdata->setBlogName($blog);
        $this->assertTrue(isset($this->gdata->blogName));
        $this->assertEquals($blog, $this->gdata->getBlogName());
        unset($this->gdata->blogName);
        $this->assertFalse(isset($this->gdata->blogName));
    }

    public function testMaxResultsParam()
    {
        $this->gdata->resetParameters();
        $blog = 'karwin';
        $this->gdata->setBlogName($blog);
        $max = 3;
        $this->gdata->setMaxResults($max);
        $this->assertTrue(isset($this->gdata->maxResults));
        $this->assertEquals($max, $this->gdata->getMaxResults());
        unset($this->gdata->maxResults);
        $this->assertFalse(isset($this->gdata->maxResults));
    }

    public function testStartIndexParam()
    {
        $this->gdata->resetParameters();
        $blog = 'karwin';
        $this->gdata->setBlogName($blog);
        $start = 3;
        $this->gdata->setStartIndex($start);
        $this->assertTrue(isset($this->gdata->startIndex));
        $this->assertEquals($start, $this->gdata->getStartIndex());
        unset($this->gdata->startIndex);
        $this->assertFalse(isset($this->gdata->startIndex));
    }

    public function testPublishedMinMaxParam()
    {
        $this->gdata->resetParameters();
        $blog = 'karwin';
        $this->gdata->setBlogName($blog);
        $min = '2006-10-01';
        $this->gdata->setPublishedMin($min);
        $this->assertTrue(isset($this->gdata->publishedMin));
        $this->assertEquals($this->gdata->formatTimestamp($min), $this->gdata->getPublishedMin());
        $max = '2006-10-15';
        $this->gdata->setPublishedMax($max);
        $this->assertTrue(isset($this->gdata->publishedMax));
        $this->assertEquals($this->gdata->formatTimestamp($max), $this->gdata->getPublishedMax());
        unset($this->gdata->publishedMin);
        $this->assertFalse(isset($this->gdata->publishedMin));
        unset($this->gdata->publishedMax);
        $this->assertFalse(isset($this->gdata->publishedMax));
    }

    public function testExceptionNoBlogName()
    {
        $this->gdata->resetParameters();
        try {
            $feed = $this->gdata->getBloggerFeed();
            $this->fail('Expected to catch Zend_Gdata_InvalidArgumentException');
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('Zend_Gdata_InvalidArgumentException'),
                'Expected Zend_Gdata_InvalidArgumentException, got '.get_class($e));
            $this->assertEquals('You must specify a blog name.', $e->getMessage());
        }
    }

    public function testExceptionQueryParam()
    {
        $this->gdata->resetParameters();
        try {
            $feed = $this->gdata->setQuery('string');
            $this->fail('Expected to catch Zend_Gdata_InvalidArgumentException');
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('Zend_Gdata_InvalidArgumentException'),
                'Expected Zend_Gdata_InvalidArgumentException, got '.get_class($e));
            $this->assertEquals('Text queries are not currently supported in Blogger.', $e->getMessage());
        }
    }

    public function testExceptionCategoryParam()
    {
        $this->gdata->resetParameters();
        try {
            $feed = $this->gdata->category = 'string';
            $this->fail('Expected to catch Zend_Gdata_InvalidArgumentException');
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('Zend_Gdata_InvalidArgumentException'),
                'Expected Zend_Gdata_InvalidArgumentException, got '.get_class($e));
            $this->assertEquals('Category queries are not currently supported in Blogger.', $e->getMessage());
        }
    }

    public function testExceptionEntryParam()
    {
        $this->gdata->resetParameters();
        try {
            $feed = $this->gdata->entry = 'string';
            $this->fail('Expected to catch Zend_Gdata_InvalidArgumentException');
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('Zend_Gdata_InvalidArgumentException'),
                'Expected Zend_Gdata_InvalidArgumentException, got '.get_class($e));
            $this->assertEquals('Entry queries are not currently supported in Blogger.', $e->getMessage());
        }
    }

}
