<?php
/**
 * @package    Zend_Feed
 * @subpackage UnitTests
 */


/**
 * Zend_Feed
 */
require_once 'Zend/Feed.php';


/**
 * @package Zend_Feed
 * @subpackage UnitTests
 */
class Zend_Feed_IteratorTest extends PHPUnit2_Framework_TestCase {

    private $feed;
    private $nsfeed;

    public function setUp()
    {
        $this->feed = Zend_Feed::importFile(dirname(__FILE__) . '/_files/TestAtomFeed.xml');
        $this->nsfeed = Zend_Feed::importFile(dirname(__FILE__) . '/_files/TestAtomFeedNamespaced.xml');
    }

    public function testRewind()
    {
        $times = 0;
        foreach ($this->feed as $f) {
            ++$times;
        }

        $times2 = 0;
        foreach ($this->feed as $f) {
            ++$times2;
        }

        $this->assertEquals($times, $times2, 'Feed should have the same number of iterations multiple times through');

        $times = 0;
        foreach ($this->nsfeed as $f) {
            ++$times;
        }

        $times2 = 0;
        foreach ($this->nsfeed as $f) {
            ++$times2;
        }

        $this->assertEquals($times, $times2, 'Feed should have the same number of iterations multiple times through');
    }

    public function testCurrent()
    {
        foreach ($this->feed as $f) {
            $this->assertTrue($f instanceof Zend_Feed_EntryAtom, 'Each feed entry should be an instance of Zend_Feed_EntryAtom');
            break;
        }

        foreach ($this->nsfeed as $f) {
            $this->assertTrue($f instanceof Zend_Feed_EntryAtom, 'Each feed entry should be an instance of Zend_Feed_EntryAtom');
            break;
        }
    }

    public function testKey()
    {
        $keys = array();
        foreach ($this->feed as $k => $f) {
            $keys[] = $k;
        }
        $this->assertEquals($keys, array(0, 1), 'Feed should have keys 0 and 1');

        $keys = array();
        foreach ($this->nsfeed as $k => $f) {
            $keys[] = $k;
        }
        $this->assertEquals($keys, array(0, 1), 'Feed should have keys 0 and 1');
    }

    public function testNext()
    {
        $last = null;
        foreach ($this->feed as $current) {
            $this->assertFalse($last === $current, 'Iteration should produce a new object each entry');
            $last = $current;
        }

        $last = null;
        foreach ($this->nsfeed as $current) {
            $this->assertFalse($last === $current, 'Iteration should produce a new object each entry');
            $last = $current;
        }
    }

}
