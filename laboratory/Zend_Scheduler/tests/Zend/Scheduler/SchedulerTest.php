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
 * @package    Zend_Scheduler
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

// Set include path
$includePath = 'C:/Zend Framework/laboratory/Zend_Scheduler/library/'
             . PATH_SEPARATOR . 'C:/Zend Framework/incubator/'
             . PATH_SEPARATOR . 'C:/Zend Framework/library/'
             . PATH_SEPARATOR . get_include_path();
set_include_path($includePath);

/** PHPUnit_Framework_TestCase */
require_once 'PHPUnit/Framework/TestCase.php';

/** Zend_Scheduler */
require_once 'Zend/Scheduler.php';

/** Zend_Controller_Front */
require_once 'Zend/Controller/Front.php';

/** Zend_Controller_Response_Http */
require_once 'Zend/Controller/Response/Http.php';

/** Zend_Config_Ini */
require_once 'Zend/Config/Ini.php';

/**
 * Unit testing for Zend_Scheduler.
 *
 * @category   Zend
 * @package    Zend_Scheduler
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Scheduler_SchedulerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Sets up unit tests.
     */
    public function setUp()
    {
        date_default_timezone_set('US/Pacific');
    }

    /**
     * Tests if a task can be added.
     */
    public function testCanAddTask()
    {
        $scheduler = new Zend_Scheduler();

        $task = new Zend_Scheduler_Task();
        $scheduler->addTask('test', $task);

        $this->assertTrue($scheduler->hasTask('test'), 'Task could not be added');
    }

    /**
     * Tests if more than one task can be added.
     */
    public function testCanAddTasks()
    {
        $scheduler = new Zend_Scheduler();

        $tasks = array(
            'test1' => new Zend_Scheduler_Task(),
            'test2' => new Zend_Scheduler_Task()
        );
        $scheduler->addTasks($tasks);

        $this->assertTrue($scheduler->hasTask('test1'), 'Tasks could not be added');
        $this->assertTrue($scheduler->hasTask('test2'), 'Second task could not be added');
    }

    /**
     * Tests if tasks can be added from a Zend_Config file.
     */
    public function testCanAddConfig()
    {
        $scheduler = new Zend_Scheduler();

        $filename = dirname(__FILE__) . '/_files/tasks.ini';
        $scheduler->addConfig(new Zend_Config_Ini($filename, 'tasks'), 'tasks');

        $this->assertTrue($scheduler->hasTask('test1'), 'Tasks could not be added');
        $this->assertTrue($scheduler->hasTask('test2'), 'Second task could not be added');
    }

    /**
     * Tests if a task can be removed.
     */
    public function testCanRemoveTask()
    {
        $scheduler = new Zend_Scheduler();

        $task = new Zend_Scheduler_Task();
        $scheduler->addTask('test', $task);
        $scheduler->removeTask('test');

        $this->assertFalse($scheduler->hasTask('test'), 'Task could not be removed');
    }

    /**
     * Tests if tasks can be serialized, unserialized, and successfully loaded.
     */
    public function testCanSerializeTasks()
    {
        $scheduler = new Zend_Scheduler();

        $task1 = new Zend_Scheduler_Task();
        $task1->setRequest('index');
        $task2 = new Zend_Scheduler_Task();
        $task2->setRequest('index');
        $scheduler->addTask('test1', $task1);
        $scheduler->addTask('test2', $task2);

        try {
            $serialized = $scheduler->serializeTasks();
        } catch (Exception $e) {
            $this->fail('Could not serialize tasks');
        }

        $scheduler = new Zend_Scheduler();
        $scheduler->addTasks(unserialize($serialized));

        $this->assertTrue($scheduler->hasTask('test1'), 'Unserialized tasks could not be added');
        $this->assertTrue($scheduler->hasTask('test2'), 'Second unserialized task could not be added');
    }

    /**
     * Tests to ensure that the scheduler cannot be serialized.
     *
     * This test is performed because serializing the scheduler depends on the 
     * serialization of the controller, which is impractical.
     */
    public function testCannotSerializeScheduler()
    {
        $scheduler = new Zend_Scheduler();

        try {
            serialize($scheduler);
        } catch (Zend_Scheduler_Exception $e) {
            return true;
        }

        $this->fail('Did not prevent serialization of scheduler');
    }

    /**
     * Tests if a task can interpret basic rules.
     */
    public function testCanInterpretBasicRules()
    {
        $task = new Zend_Scheduler_Task();
        $task->setTime(mktime(23, 59, 59, 12, 31, 2006));

        $task->setMonths('December');
        $this->assertTrue($task->isScheduled(), 'Month rule was not interpreted correctly');

        $task->setDays('31');
        $this->assertTrue($task->isScheduled(), 'Day rule was not interpreted correctly');

        $task->setDays('last');
        $this->assertTrue($task->isScheduled(), "Keyword 'last' was not interpreted correctly");

        $task->setWeekdays('Sunday');
        $this->assertTrue($task->isScheduled(), 'Weekday rule was not interpreted correctly');

        $task->setHours('23');
        $this->assertTrue($task->isScheduled(), 'Hour rule was not interpreted correctly');

        $task->setMinutes('59');
        $this->assertTrue($task->isScheduled(), 'Minute rule was not interpreted correctly');
    }

    /**
     * Tests if a task can interpret the 'earliest run' rule.
     */
    public function testCanInterpretEarliestRunRule()
    {
        $task = new Zend_Scheduler_Task();
        $task->setTime(mktime(23, 59, 59, 12, 31, 2006));

        $task->setEarliestRun('2007-01-01T00:00:00');
        $this->assertFalse($task->isScheduled(), 'Earliest run rule was not interpreted correctly');

        $task->setEarliestRun('2006-01-01T00:00:00');
        $this->assertTrue($task->isScheduled(), 'Earliest run rule was not interpreted correctly');
    }

    /**
     * Tests if a task can interpret the 'latest run' rule.
     */
    public function testCanInterpretLatestRunRule()
    {
        $task = new Zend_Scheduler_Task();
        $task->setTime(mktime(23, 59, 59, 12, 31, 2006));

        $task->setLatestRun('2006-01-01T00:00:00');
        $this->assertFalse($task->isScheduled(), 'Latest run rule was not interpreted correctly');

        $task->setLatestRun('2007-01-01T00:00:00');
        $this->assertTrue($task->isScheduled(), 'Latest run rule was not interpreted correctly');
    }

    /**
     * Tests if a task can interpret rules with ranges, incuding those that 
     * wrap from the maximum value to the minimum value.
     */
    public function testCanInterpretRangeRules()
    {
        $task = new Zend_Scheduler_Task();
        $task->setTime(mktime(23, 59, 59, 12, 31, 2006));

        $task->setMonths('October-December');
        $this->assertTrue($task->isScheduled(), 'Standard month range rule was not interpreted correctly');

        $task->setMonths('November-February');
        $this->assertTrue($task->isScheduled(), 'Wrap-around month range rule was not interpreted correctly');

        $task->setDays('25-31');
        $this->assertTrue($task->isScheduled(), 'Day range rule was not interpreted correctly');

        $task->setDays('25-3');
        $this->assertTrue($task->isScheduled(), 'Wrap-around day range rule was not interpreted correctly');

        $task->setDays('25-last');
        $this->assertTrue($task->isScheduled(), "Day range rule using keyword 'last' was not interpreted correctly");

        $task->setDays('last-3');
        $this->assertTrue($task->isScheduled(), "Wrap-around day range rule using keyword 'last' was not interpreted correctly");

        $task->setWeekdays('Sunday-Wednesday');
        $this->assertTrue($task->isScheduled(), 'Weekday range rule was not interpreted correctly');

        $task->setWeekdays('Friday-Wednesday');
        $this->assertTrue($task->isScheduled(), 'Wrap-around weekday range rule was not interpreted correctly');

        $task->setHours('18-23');
        $this->assertTrue($task->isScheduled(), 'Hour range rule was not interpreted correctly');

        $task->setHours('18-5');
        $this->assertTrue($task->isScheduled(), 'Wrap-around hour range rule was not interpreted correctly');

        $task->setMinutes('30-59');
        $this->assertTrue($task->isScheduled(), 'Minute range rule was not interpreted correctly');

        $task->setMinutes('50-10');
        $this->assertTrue($task->isScheduled(), 'Wrap-around minute range rule was not interpreted correctly');
    }

    /**
     * Tests if a task can interpret incremental step rules.
     */
    public function testCanInterpretStepRules()
    {
        $task = new Zend_Scheduler_Task();
        $task->setTime(mktime(23, 59, 59, 12, 31, 2006));

        $task->setDays('3/2');
        $this->assertTrue($task->isScheduled(), 'Day step rule was not interpreted correctly');

        $task->setHours('5/3');
        $this->assertTrue($task->isScheduled(), 'Hour step rule was not interpreted correctly');

        $task->setMinutes('5/9');
        $this->assertTrue($task->isScheduled(), 'Minute step rule was not interpreted correctly');
    }

    /**
     * Tests if a task gives an error when trying to use step rules in ways 
     * which are not permitted.
     *
     * This test applies to months.
     */
    public function testCannotInterpretInvalidStepRules1()
    {
        $task = new Zend_Scheduler_Task();
        $task->setTime(mktime(23, 59, 59, 12, 31, 2006));

        $task->setMonths('January/3');

        try {
            $task->isScheduled();
        } catch (Zend_Scheduler_Exception $e) {
            return true;
        }

        $this->fail('Did not prevent invalid month step rule');
    }

    /**
     * Tests if a task gives an error when trying to use step rules in ways 
     * which are not permitted.
     *
     * This test applies to days of the week ('weekdays').
     */
    public function testCannotInterpretInvalidStepRules2()
    {
        $task = new Zend_Scheduler_Task();
        $task->setTime(mktime(23, 59, 59, 12, 31, 2006));

        $task->setWeekdays('Monday/3');

        try {
            $task->isScheduled();
        } catch (Zend_Scheduler_Exception $e) {
            return true;
        }

        $this->fail('Did not prevent invalid weekday step rule');
    }

    /**
     * Tests if a task can be successfully dispatched.
     *
     * @see Zend_Controller_Front_Mock
     */
    public function testCanDispatchTask()
    {
        $controller = Zend_Controller_Front_Mock::getInstance();
        $controller->returnResponse(true);
        $controller->throwExceptions(true);

        $scheduler = new Zend_Scheduler();
        $scheduler->setController($controller);

        $task = new Zend_Scheduler_Task();
        $task->setRequest('scheduled');
        $scheduler->addTask('test', $task);

        $responses = $scheduler->run();

        $this->assertTrue(isset($responses['test']), 'Received empty response');
        $this->assertEquals('scheduled:index', $responses['test'][0]->getBody(), 'Response did not match expected response');
    }

    /**
     * Tests if a valid (i.e., included) backend can be loaded.
     */
    public function testCanLoadValidBackend()
    {
        $scheduler = new Zend_Scheduler();

        try {
            $scheduler->setBackend('File');
        } catch (Exception $e) {
            $this->fail('Denied the use of a valid backend');
        }
    }

    /**
     * Tests if a valid custom backend can be loaded.
     */
    public function testCanLoadCustomBackend()
    {
        require_once dirname(__FILE__) . '/_files/CustomBackend.php';

        $scheduler = new Zend_Scheduler();

        try {
            $scheduler->setBackend('CustomBackend');
        } catch (Exception $e) {
            $this->fail('Denied the use of a valid custom backend');
        }
    }

    /**
     * Tests if loading of an invalid backend is prevented.
     *
     * This test applies to nonsense values.
     */
    public function testCannotLoadInvalidBackend1()
    {
        require_once dirname(__FILE__) . '/_files/InvalidBackend.php';

        $scheduler = new Zend_Scheduler();

        try {
            $scheduler->setBackend('Xyz');
        } catch (Zend_Scheduler_Exception $e) {
            return true;
        }

        $this->fail('Allowed the use of an invalid backend');
    }

    /**
     * Tests if loading of an invalid backend is prevented.
     *
     * This test applies to valid classes which do not extend 
     * Zend_Scheduler_Backend_Abstract.
     */
    public function testCannotLoadInvalidBackend2()
    {
        require_once dirname(__FILE__) . '/_files/InvalidBackend.php';

        $scheduler = new Zend_Scheduler();

        try {
            $scheduler->setBackend('InvalidBackend');
        } catch (Zend_Scheduler_Exception $e) {
            return true;
        }

        $this->fail('Allowed the use of an invalid custom backend');
    }

    /**
     * Tests if the 'File' backend is functional.
     */
    public function testCanUseFileBackend()
    {
        $queue   = dirname(__FILE__) . '/_files/task.queue';
        $backend = new Zend_Scheduler_Backend_File(array('filename' => $queue));
        $this->_canUseBackend($backend);
        unset($queue);
    }

    /**
     * Tests if the scheduler can limit execution to an arbitrary number.
     */
    public function testCanLimitExecution()
    {
        $controller = Zend_Controller_Front_Mock::getInstance();
        $controller->returnResponse(true);
        $controller->throwExceptions(true);

        $queue   = dirname(__FILE__) . '/_files/task.queue';
        $backend = new Zend_Scheduler_Backend_File(array('filename' => $queue));

        $scheduler = new Zend_Scheduler($backend);
        $scheduler->setController($controller);
        $scheduler->setLimit(1); // Only execute one task

        $task = new Zend_Scheduler_Task();
        $task->addRequest('scheduled', 'task1');
        $task->addRequest('scheduled', 'task2');
        $scheduler->addTask('test1', $task);

        $task = new Zend_Scheduler_Task();
        $task->setRequest('scheduled', 'task3');
        $scheduler->addTask('test2', $task);

        $responses = $scheduler->run();

        unset($queue);

        $this->assertTrue(isset($responses['test1']), 'Received empty response');
        $this->assertFalse(isset($responses['test2']), 'Did not limit execution');

        $responseCount = count($responses['test1']);
        $this->assertEquals(2, $responseCount, "Expected 2 request responses, received {$responseCount}");
    }

    /**
     * Tests any given backend.
     *
     * @param Zend_Scheduler $scheduler
     * @param Zend_Scheduler_Backend_Abstract $backend
     */
    protected function _canUseBackend(Zend_Scheduler_Backend_Abstract $backend)
    {
        $controller = Zend_Controller_Front_Mock::getInstance();
        $controller->returnResponse(true);
        $controller->throwExceptions(true);

        $scheduler = new Zend_Scheduler($backend);
        $scheduler->setController($controller);
        $scheduler->setLimit(1); // Only execute one task

        $task = new Zend_Scheduler_Task();
        $task->addRequest('scheduled', 'task1');
        $task->addRequest('scheduled', 'task2');
        $scheduler->addTask('test1', $task);

        $task = new Zend_Scheduler_Task();
        $task->setRequest('scheduled', 'task3');
        $scheduler->addTask('test2', $task);

        $task = new Zend_Scheduler_Task();
        $task->setRequest('scheduled', 'task4');
        $scheduler->addTask('test3', $task);

        try {
            $scheduler->run();
        } catch (Zend_Scheduler_Exception $e) {
            $this->fail('Could not execute tasks: ' . $e->getMessage());
        }

        try {
            $tasks = $backend->loadQueue();
        } catch (Zend_Scheduler_Exception $e) {
            $this->fail('Could not load task queue');
        }

        $taskCount = count($tasks);
        $this->assertEquals(2, $taskCount, "Expected 2 tasks in queue, received {$taskCount}");

        $tasksFound = isset($tasks['test2']) and isset($tasks['test3']);
        $this->assertTrue($tasksFound, 'Did not find expected tasks in queue');

        $this->assertType('Zend_Scheduler_Task', $tasks['test2'], 'Received task did not match type');
    }
}

/**
 * Mock front controller for use with {@link Zend_Scheduler_SchedulerTest}.
 *
 * @category   Zend
 * @package    Zend_Scheduler
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Front_Mock extends Zend_Controller_Front
{
    /** @var self Singleton instance */
    private static $_instance = null;

    /**
     * Constructor.
     */
    private function __construct()
    {
    }

    /**
     * Singleton instance.
     * 
     * @return Zend_Controller_Front_Mock
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Pretend to dispatch an HTTP request to a controller/action.
     *
     * @param  Zend_Controller_Request_Abstract|null $request
     * @param  Zend_Controller_Response_Abstract|null $response
     * @return Zend_Controller_Response_Abstract|void Response object
     */
    public function dispatch(Zend_Controller_Request_Abstract  $request  = null, 
                             Zend_Controller_Response_Abstract $response = null)
    {
        $response = new Zend_Controller_Response_Http();
        $response->setBody($request->getControllerName() . ':' . $request->getActionName());

        if ($this->returnResponse()) {
            return $response;
        }

        $response->sendHeaders();
        $response->outputBody();
    }
}
