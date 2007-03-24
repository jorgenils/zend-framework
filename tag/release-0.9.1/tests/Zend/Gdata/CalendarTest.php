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

require_once 'Zend/Gdata/Calendar.php';
require_once 'Zend/Http/Client.php';

/**
 * @package Zend_Gdata
 * @subpackage UnitTests
 */
class Zend_Gdata_CalendarTest extends PHPUnit_Framework_TestCase
{
    const GOOGLE_DEVELOPER_CALENDAR = 'developer-calendar@google.com';
    const ZEND_CONFERENCE_EVENT = 'bn2h4o4mc3a03ci4t48j3m56pg';

    public function setUp()
    {
        $testAdapter = new Zend_Http_Client_Adapter_Test();
        $client = new Zend_Http_Client(null, array('adapter' => $testAdapter));
        $this->gdata = new Zend_Gdata_Calendar($client);
    }

    public function testUpdatedMinMaxParam()
    {
        $updatedMin = '2006-09-20';
        $updatedMax = '2006-11-05';
        $this->gdata->resetParameters();
        $this->gdata->setUser(self::GOOGLE_DEVELOPER_CALENDAR);
        $this->gdata->setUpdatedMin($updatedMin);
        $this->gdata->setUpdatedMax($updatedMax);
        $this->assertTrue(isset($this->gdata->updatedMin));
        $this->assertTrue(isset($this->gdata->updatedMax));
        $this->assertTrue(isset($this->gdata->user));
        $this->assertEquals($this->gdata->formatTimestamp($updatedMin), $this->gdata->getUpdatedMin());
        $this->assertEquals($this->gdata->formatTimestamp($updatedMax), $this->gdata->getUpdatedMax());
        $this->assertEquals(self::GOOGLE_DEVELOPER_CALENDAR, $this->gdata->getUser());

        unset($this->gdata->updatedMin);
        $this->assertFalse(isset($this->gdata->updatedMin));
        unset($this->gdata->updatedMax);
        $this->assertFalse(isset($this->gdata->updatedMax));
        unset($this->gdata->user);
        $this->assertFalse(isset($this->gdata->user));
    }

    public function testStartMinMaxParam()
    {
        $this->gdata->resetParameters();
        $startMin = '2006-10-30';
        $startMax = '2006-11-01';
        $this->gdata->setUser(self::GOOGLE_DEVELOPER_CALENDAR);
        $this->gdata->setStartMin($startMin);
        $this->gdata->setStartMax($startMax);
        $this->assertTrue(isset($this->gdata->startMin));
        $this->assertTrue(isset($this->gdata->startMax));
        $this->assertEquals($this->gdata->formatTimestamp($startMin), $this->gdata->getStartMin());
        $this->assertEquals($this->gdata->formatTimestamp($startMax), $this->gdata->getStartMax());

        unset($this->gdata->startMin);
        $this->assertFalse(isset($this->gdata->startMin));
        unset($this->gdata->startMax);
        $this->assertFalse(isset($this->gdata->startMax));
        unset($this->gdata->user);
        $this->assertFalse(isset($this->gdata->user));
    }

    public function testVisibilityParam()
    {
        $this->gdata->resetParameters();
        $visibility = 'private';
        $this->gdata->setUser(self::GOOGLE_DEVELOPER_CALENDAR);
        $this->gdata->setVisibility($visibility);
        $this->assertTrue(isset($this->gdata->visibility));
        $this->assertEquals($visibility, $this->gdata->getVisibility());
        unset($this->gdata->visibility);
        $this->assertFalse(isset($this->gdata->visibility));
    }

    public function testProjectionParam()
    {
        $this->gdata->resetParameters();
        $projection = 'composite';
        $this->gdata->setUser(self::GOOGLE_DEVELOPER_CALENDAR);
        $this->gdata->setProjection($projection);
        $this->assertTrue(isset($this->gdata->projection));
        $this->assertEquals($projection, $this->gdata->getProjection());
        unset($this->gdata->projection);
        $this->assertFalse(isset($this->gdata->projection));
    }

    public function testOrderbyParam()
    {
        $this->gdata->resetParameters();
        $orderby = 'starttime';
        $this->gdata->setUser(self::GOOGLE_DEVELOPER_CALENDAR);
        $this->gdata->setOrderby($orderby);
        $this->assertTrue(isset($this->gdata->orderby));
        $this->assertEquals($orderby, $this->gdata->getOrderby());
        unset($this->gdata->orderby);
        $this->assertFalse(isset($this->gdata->orderby));
    }

    public function testEventParam()
    {
        $this->gdata->resetParameters();
        $this->gdata->setUser(self::GOOGLE_DEVELOPER_CALENDAR);
        $this->gdata->setEvent(self::ZEND_CONFERENCE_EVENT);
        $this->assertTrue(isset($this->gdata->event));
        $this->assertEquals(self::ZEND_CONFERENCE_EVENT, $this->gdata->getEvent());
        unset($this->gdata->event);
        $this->assertFalse(isset($this->gdata->event));
    }

    public function testCommentsParam()
    {
        $this->gdata->resetParameters();
        $comment = 'we need to reschedule';
        $this->gdata->setComments($comment);
        $this->assertTrue(isset($this->gdata->comments));
        $this->assertEquals($comment, $this->gdata->getComments());
        unset($this->gdata->comments);
        $this->assertFalse(isset($this->gdata->comments));
    }

}
