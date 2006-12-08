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
 * @package    Zend_Acl
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * Zend_Acl
 */
require_once 'Zend/Acl.php';


/**
 * Zend_Acl_Aco
 */
require_once 'Zend/Acl/Aco.php';


/**
 * Zend_Acl_Aro
 */
require_once 'Zend/Acl/Aro.php';


/**
 * PHPUnit_Framework_TestCase
 */
require_once 'PHPUnit/Framework/TestCase.php';


/**
 * @category   Zend
 * @package    Zend_Acl
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_AclTest extends PHPUnit_Framework_TestCase
{
    /**
     * ACL object for each test method
     *
     * @var Zend_Acl
     */
    protected $_acl;

    /**
     * Instantiates a new ACL object and creates internal reference to it for each test method
     *
     * @return void
     */
    public function setUp()
    {
        $this->_acl = new Zend_Acl();
    }

    /**
     * Ensures that the ARO registry is created automatically only once
     *
     * @return void
     */
    public function testGetARORegistryAuto()
    {
        $aroRegistry1 = $this->_acl->getAroRegistry();
        $this->assertTrue($aroRegistry1 instanceof Zend_Acl_Aro_Registry);
        $aroRegistry2 = $this->_acl->getAroRegistry();
        $this->assertTrue($aroRegistry1 === $aroRegistry2);
    }

    /**
     * Tests setting the ARO registry
     *
     * @return void
     */
    public function testSetARORegistry()
    {
        $aroRegistry = new Zend_Acl_Aro_Registry();
        $this->_acl->setAroRegistry($aroRegistry);
        $this->assertTrue($aroRegistry === $this->_acl->getAroRegistry());
    }

    /**
     * Ensures that basic addition and retrieval of a single ARO works
     *
     * @return void
     */
    public function testARORegistryAddAndGetOne()
    {
        $aroGuest = new Zend_Acl_Aro('guest');
        $aroRegistry = $this->_acl->getAroRegistry();
        $aro = $aroRegistry->add($aroGuest)
                           ->get($aroGuest->getAroId());
        $this->assertTrue($aroGuest === $aro);
        $aro = $aroRegistry->get($aroGuest);
        $this->assertTrue($aroGuest === $aro);
    }

    /**
     * Ensures that basic removal of a single ARO works
     *
     * @return void
     */
    public function testARORegistryRemoveOne()
    {
        $aroGuest = new Zend_Acl_Aro('guest');
        $aroRegistry = $this->_acl->getAroRegistry();
        $aroRegistry->add($aroGuest)
                    ->remove($aroGuest);
        $this->assertFalse($aroRegistry->has($aroGuest));
    }

    /**
     * Tests basic ARO inheritance
     *
     * @return void
     */
    public function testARORegistryInherits()
    {
        $aroGuest  = new Zend_Acl_Aro('guest');
        $aroMember = new Zend_Acl_Aro('member');
        $aroEditor = new Zend_Acl_Aro('editor');
        $aroRegistry = $this->_acl->getAroRegistry();
        $aroRegistry->add($aroGuest)
                    ->add($aroMember, $aroGuest->getAroId())
                    ->add($aroEditor, $aroMember);
        $this->assertTrue(0 === count($aroRegistry->getParents($aroGuest)));
        $aroMemberParents = $aroRegistry->getParents($aroMember);
        $this->assertTrue(1 === count($aroMemberParents));
        $this->assertTrue(isset($aroMemberParents['guest']));
        $aroEditorParents = $aroRegistry->getParents($aroEditor);
        $this->assertTrue(1 === count($aroEditorParents));
        $this->assertTrue(isset($aroEditorParents['member']));
        $this->assertTrue($aroRegistry->inherits($aroMember, $aroGuest, true));
        $this->assertTrue($aroRegistry->inherits($aroEditor, $aroMember, true));
        $this->assertTrue($aroRegistry->inherits($aroEditor, $aroGuest));
        $this->assertFalse($aroRegistry->inherits($aroGuest, $aroMember));
        $this->assertFalse($aroRegistry->inherits($aroMember, $aroEditor));
        $this->assertFalse($aroRegistry->inherits($aroGuest, $aroEditor));
        $aroRegistry->remove($aroMember);
        $this->assertTrue(0 === count($aroRegistry->getParents($aroEditor)));
        $this->assertFalse($aroRegistry->inherits($aroEditor, $aroGuest));
    }

    /**
     * Tests basic ARO multiple inheritance
     *
     * @return void
     */
    public function testARORegistryInheritsMultiple()
    {
        $aroParent1 = new Zend_Acl_Aro('parent1');
        $aroParent2 = new Zend_Acl_Aro('parent2');
        $aroChild   = new Zend_Acl_Aro('child');
        $aroRegistry = $this->_acl->getAroRegistry();
        $aroRegistry->add($aroParent1)
                    ->add($aroParent2)
                    ->add($aroChild, array($aroParent1, $aroParent2));
        $aroChildParents = $aroRegistry->getParents($aroChild);
        $this->assertTrue(2 === count($aroChildParents));
        $i = 1;
        foreach ($aroChildParents as $aroParentId => $aroParent) {
            $this->assertTrue("parent$i" === $aroParentId);
            $i++;
        }
        $this->assertTrue($aroRegistry->inherits($aroChild, $aroParent1));
        $this->assertTrue($aroRegistry->inherits($aroChild, $aroParent2));
        $aroRegistry->remove($aroParent1);
        $aroChildParents = $aroRegistry->getParents($aroChild);
        $this->assertTrue(1 === count($aroChildParents));
        $this->assertTrue(isset($aroChildParents['parent2']));
        $this->assertTrue($aroRegistry->inherits($aroChild, $aroParent2));
    }

    /**
     * Ensures that the same ARO cannot be registered more than once to the registry
     *
     * @return void
     */
    public function testARORegistryDuplicate()
    {
        try {
            $aroGuest = new Zend_Acl_Aro('guest');
            $this->_acl->getAroRegistry()->add($aroGuest)
                                         ->add($aroGuest);
            $this->fail('Expected exception not thrown upon adding same ARO twice');
        } catch (Zend_Acl_Aro_Registry_Exception $e) {
            $this->assertContains('already exists', $e->getMessage());
        }
    }

    /**
     * Ensures that two AROs having the same ID cannot be registered
     *
     * @return void
     */
    public function testARORegistryDuplicateId()
    {
        try {
            $aroGuest1 = new Zend_Acl_Aro('guest');
            $aroGuest2 = new Zend_Acl_Aro('guest');
            $this->_acl->getAroRegistry()->add($aroGuest1)
                                         ->add($aroGuest2);
            $this->fail('Expected exception not thrown upon adding two AROs with same ID');
        } catch (Zend_Acl_Aro_Registry_Exception $e) {
            $this->assertContains('already exists', $e->getMessage());
        }
    }

    /**
     * Ensures that by default, Zend_Acl denies access to everything by all
     *
     * @return void
     */
    public function testDefaultDeny()
    {
        $this->assertFalse($this->_acl->isAllowed());
    }

    /**
     * Ensures that ACL-wide rules (all AROs, ACOs, and privileges) work properly
     *
     * @return void
     */
    public function testDefaultRuleSet()
    {
        $this->_acl->allow();
        $this->assertTrue($this->_acl->isAllowed());
        $this->_acl->deny();
        $this->assertFalse($this->_acl->isAllowed());
    }

    /**
     * Ensures that by default, Zend_Acl denies access to a privilege on anything by all
     *
     * @return void
     */
    public function testDefaultPrivilegeDeny()
    {
        $this->assertFalse($this->_acl->isAllowed(null, null, 'somePrivilege'));
    }

    /**
     * Ensures that ACL-wide rules apply to privileges
     *
     * @return void
     */
    public function testDefaultRuleSetPrivilege()
    {
        $this->_acl->allow();
        $this->assertTrue($this->_acl->isAllowed(null, null, 'somePrivilege'));
        $this->_acl->deny();
        $this->assertFalse($this->_acl->isAllowed(null, null, 'somePrivilege'));
    }

    /**
     * Ensures that a privilege allowed for all AROs upon all ACOs works properly
     *
     * @return void
     */
    public function testPrivilegeAllow()
    {
        $this->_acl->allow(null, null, 'somePrivilege');
        $this->assertTrue($this->_acl->isAllowed(null, null, 'somePrivilege'));
    }

    /**
     * Ensures that a privilege denied for all AROs upon all ACOs works properly
     *
     * @return void
     */
    public function testPrivilegeDeny()
    {
        $this->_acl->allow();
        $this->_acl->deny(null, null, 'somePrivilege');
        $this->assertFalse($this->_acl->isAllowed(null, null, 'somePrivilege'));
    }

    /**
     * Ensures that multiple privileges work properly
     *
     * @return void
     */
    public function testPrivileges()
    {
        $this->_acl->allow(null, null, array('p1', 'p2', 'p3'));
        $this->assertTrue($this->_acl->isAllowed(null, null, 'p1'));
        $this->assertTrue($this->_acl->isAllowed(null, null, 'p2'));
        $this->assertTrue($this->_acl->isAllowed(null, null, 'p3'));
        $this->assertFalse($this->_acl->isAllowed(null, null, 'p4'));
        $this->_acl->deny(null, null, 'p1');
        $this->assertFalse($this->_acl->isAllowed(null, null, 'p1'));
        $this->_acl->deny(null, null, array('p2', 'p3'));
        $this->assertFalse($this->_acl->isAllowed(null, null, 'p2'));
        $this->assertFalse($this->_acl->isAllowed(null, null, 'p3'));
    }

    /**
     * Ensures that assertions on privileges work properly
     *
     * @return void
     */
    public function testPrivilegeAssert()
    {
        $this->_acl->allow(null, null, 'somePrivilege', new AssertTrue());
        $this->assertTrue($this->_acl->isAllowed(null, null, 'somePrivilege'));
        $this->_acl->allow(null, null, 'somePrivilege', new AssertFalse());
        $this->assertFalse($this->_acl->isAllowed(null, null, 'somePrivilege'));
    }

    public function testCMSExample()
    {
        $this->markTestSkipped('pending work in progress');

        // Create new Zend_Acl instance
        $acl = new Zend_Acl();

        // Fetch the ARO registry
        $aro = $acl->getAroRegistry();

        // Add some roles to the ARO registry
        $aro->add('guest');
        $aro->add('staff', $aro->guest);  // staff inherits permissions from guest
        $aro->add('editor', $aro->staff); // editor inherits permissions from staff
        $aro->add('administrator');

        // Whitelist implementation; ACL denies access by default
        $acl->deny();

        // Guest may only view content
        $acl->allow($aro->guest, 'view');

        // Staff inherits view privilege from guest, but also needs additional privileges
        $acl->allow($aro->staff, array('edit', 'submit', 'revise'));

        // Editor inherits view, edit, submit, and revise privileges, but also needs additional privileges
        $acl->allow($aro->editor, array('publish', 'archive', 'delete'));

        // Administrator inherits nothing but is allowed all privileges
        $acl->allow($aro->administrator);

        // Access control checks based on above permission sets

        self::assertTrue($acl->valid('guest', 'view'));
        self::assertFalse($acl->valid('guest', 'edit'));
        self::assertFalse($acl->valid('guest', 'submit'));
        self::assertFalse($acl->valid($aro->guest, 'revise'));
        self::assertFalse($acl->valid('guest', 'publish'));
        self::assertFalse($acl->valid('guest', 'archive'));
        self::assertFalse($acl->valid($aro->guest, 'delete'));
        self::assertFalse($acl->valid('guest', 'unknown'));
        self::assertFalse($acl->valid('guest'));

        self::assertTrue($acl->valid('staff', 'view'));
        self::assertTrue($acl->valid('staff', 'edit'));
        self::assertTrue($acl->valid('staff', 'submit'));
        self::assertTrue($acl->valid($aro->staff, 'revise'));
        self::assertFalse($acl->valid('staff', 'publish'));
        self::assertFalse($acl->valid('staff', 'archive'));
        self::assertFalse($acl->valid($aro->staff, 'delete'));
        self::assertFalse($acl->valid('staff', 'unknown'));
        self::assertFalse($acl->valid('staff'));

        self::assertTrue($acl->valid('editor', 'view'));
        self::assertTrue($acl->valid('editor', 'edit'));
        self::assertTrue($acl->valid('editor', 'submit'));
        self::assertTrue($acl->valid($aro->editor, 'revise'));
        self::assertTrue($acl->valid('editor', 'publish'));
        self::assertTrue($acl->valid('editor', 'archive'));
        self::assertTrue($acl->valid($aro->editor, 'delete'));
        self::assertFalse($acl->valid('editor', 'unknown'));
        self::assertFalse($acl->valid('editor'));

        self::assertTrue($acl->valid('administrator', 'view'));
        self::assertTrue($acl->valid('administrator', 'edit'));
        self::assertTrue($acl->valid('administrator', 'submit'));
        self::assertTrue($acl->valid($aro->administrator, 'revise'));
        self::assertTrue($acl->valid('administrator', 'publish'));
        self::assertTrue($acl->valid('administrator', 'archive'));
        self::assertTrue($acl->valid($aro->administrator, 'delete'));
        self::assertTrue($acl->valid('administrator', 'unknown'));
        self::assertTrue($acl->valid('administrator'));

        // Some checks on specific areas, which inherit access controls from the root ACL node
        self::assertTrue($acl->newsletter->pending->valid('guest', 'view'));
        self::assertTrue($acl->gallery->profiles->valid($aro->staff, 'revise'));
        self::assertFalse($acl->config->hosts->valid($aro->editor, 'unknown'));

        // Checking permissions from the perspective of an ARO
        self::assertTrue($aro->staff->canAccess($acl->newsletter->pending, 'view'));
        self::assertTrue($aro->staff->canAccess($acl->newsletter->pending, 'edit'));
        self::assertFalse($aro->staff->canAccess($acl->newsletter->pending, 'publish'));
        self::assertFalse($aro->staff->canAccess($acl->newsletter->pending));
        self::assertTrue($aro->administrator->canAccess($acl->newsletter->pending));

        // Unknown ARO
        self::assertFalse($aro->unknown->canAccess($acl->newsletter->pending));

        // Add a new group, marketing, which bases its permissions on staff
        $aro->add('marketing', 'staff');

        // Refine the privilege sets for more specific needs

        // Allow marketing to publish and archive newsletters
        $acl->newsletter->allow($aro->marketing, array('publish', 'archive'));

        // Allow marketing to publish and archive latest news
        $acl->news->latest->allow($aro->marketing, array('publish', 'archive'));

        // Deny staff (and marketing, by inheritance) rights to revise latest news
        $acl->news->latest->deny($aro->staff, 'revise');

        // Deny everyone access to archive news announcements
        $acl->news->announcement->deny(null, 'archive');

        // Access control checks for the above refined permission sets

        self::assertTrue($acl->valid('marketing', 'view'));
        self::assertTrue($acl->valid('marketing', 'edit'));
        self::assertTrue($acl->valid('marketing', 'submit'));
        self::assertTrue($acl->valid($aro->marketing, 'revise'));
        self::assertFalse($acl->valid('marketing', 'publish'));
        self::assertFalse($acl->valid('marketing', 'archive'));
        self::assertFalse($acl->valid($aro->marketing, 'delete'));
        self::assertFalse($acl->valid('marketing', 'unknown'));
        self::assertFalse($acl->valid('marketing'));

        self::assertTrue($acl->newsletter->valid('marketing', 'publish'));
        self::assertFalse($acl->newsletter->pending->valid('staff', 'publish'));
        self::assertTrue($acl->newsletter->pending->valid('marketing', 'publish'));
        self::assertTrue($acl->newsletter->valid('marketing', 'archive'));
        self::assertFalse($acl->newsletter->valid('marketing', 'delete'));
        self::assertFalse($acl->newsletter->valid('marketing'));

        self::assertTrue($acl->news->latest->valid('marketing', 'publish'));
        self::assertTrue($acl->news->latest->valid('marketing', 'archive'));
        self::assertFalse($acl->news->latest->valid('marketing', 'delete'));
        self::assertFalse($acl->news->latest->valid('marketing', 'revise'));
        self::assertFalse($acl->news->latest->valid('staging', 'revise'));
        self::assertFalse($acl->news->latest->valid('marketing'));

        self::assertFalse($acl->news->announcement->valid('marketing', 'archive'));
        self::assertFalse($acl->news->announcement->valid('staff', 'archive'));
        self::assertFalse($acl->news->announcement->valid('administrator', 'archive'));

        self::assertFalse($aro->staff->canAccess($acl->news->latest, 'publish'));
        self::assertTrue($aro->marketing->canAccess($acl->news->latest, 'publish'));
        self::assertFalse($aro->editor->canAccess($acl->news->announcement, 'archive'));

        // Remove some previous permission specifications

        // Marketing can no longer publish and archive newsletters
        $acl->newsletter->removeAllow('marketing', array('publish', 'archive'));

        // Marketing can no longer archive the latest news
        $acl->news->latest->removeAllow($aro->marketing, 'archive');

        // Now staff (and marketing, by inheritance) may revise latest news
        $acl->news->latest->removeDeny($aro->staff, 'revise');

        // Access control checks for the above refinements

        self::assertFalse($acl->newsletter->valid('marketing', 'publish'));
        self::assertFalse($acl->newsletter->valid('marketing', 'archive'));

        self::assertFalse($acl->news->latest->valid('marketing', 'archive'));

        self::assertTrue($acl->news->latest->valid('staff', 'revise'));
        self::assertTrue($acl->news->latest->valid($aro->marketing, 'revise'));

        // Grant marketing all permissions on the latest news
        $acl->news->latest->allow('marketing');

        // Access control checks for the above refinement
        self::assertTrue($acl->news->latest->valid('marketing', 'archive'));
        self::assertTrue($acl->news->latest->valid('marketing', 'publish'));
        self::assertTrue($acl->news->latest->valid('marketing', 'edit'));
        self::assertTrue($acl->news->latest->valid('marketing'));

    }

    public function testRegression()
    {
        $this->markTestSkipped('pending work in progress');

        $acl = new Zend_Acl();

        // retrieve an instance of the ARO registry
        $aro = $acl->getAroRegistry();
        $aro->add('guest');
        $aro->add('staff', $aro->guest);

        // deny access to all unknown AROs
        $acl->deny();
        $acl->allow('staff');
        $acl->deny('staff', array('task1', 'task2'));

        // Access control checks for the above refinement
        self::assertFalse($acl->valid('staff', 'task1'));
    }

    public function testAclAroManagement()
    {
        $this->markTestSkipped('pending work in progress');

        $acl = new Zend_Acl();

        // retrieve an instance of the ARO registry
        $aro = $acl->getAroRegistry();
        $aro->add('guest');

        // ensure we cannot create duplicates
        try {
            $guest = $aro->add('guest');
            $this->fail('Cannot create duplicate aros');
        } catch (Exception $e) {
            // success
        }

        // ARO returns a default ARO for non-existant member
        self::assertTrue(($aro->nonexistent instanceof Zend_Acl_Aro));
        self::assertTrue(($aro->nonexistent->getId() == '_default'));

        // ARO returns a correct object for existing member
        $guest = $aro->guest;
        self::assertTrue(($guest instanceof Zend_Acl_Aro));

        // Ensure ARO returns a correct reference to parent registry
        self::assertTrue(($guest->getRegistry() === $aro));

        // Add permissions to ACL, remove ARO and ensure permissions are wiped
        $acl->deny($guest);
        $acl->allow($guest, array('task1', 'task2'));
        $acl->testbranch->allow($guest, array('task3'));
        $acl->forbidden->deny($guest);
        $acl->temporary->allow($guest);
        $acl->allow(array('guest', 'nonexistent'), 'task4', '/temporary/folder');
        $acl->deny(array('guest', 'nonexistent'), 'task4', '/temporary/folder');
        $acl->allow(array('guest', 'nonexistent'), 'task5', '/temporary/folder');
        $acl->deny(array('guest', 'nonexistent'), 'task5', '/temporary/folder');

        // ensure we cannot create get permissions for multiple aros
        try {
            $result = $acl->valid(array('guest', 'staff'));
            $this->fail('Cannot request multiple aros');
        } catch (Exception $e) {
            // success
        }

        // Ensure we can query the types of permissions set on an ACO
        $allow = $acl->getAllow();
        self::assertTrue(isset($allow['guest']));
        self::assertTrue(in_array('task1', $allow['guest']));
        self::assertTrue(in_array('task2', $allow['guest']));

        // Reset testbranch node and allow all
        $acl->testbranch->allow($guest);
        $allow = $acl->testbranch->getAllow();
        self::assertFalse(in_array('task3', $allow['guest']));

        $acl->removeAllow($guest, 'task2');
        $allow = $acl->getAllow();
        self::assertFalse(in_array('task2', $allow['guest']));
        self::assertFalse($acl->valid($guest, 'task2'));
        $deny = $acl->getDeny();
        self::assertTrue(isset($deny['guest']));

        // Remove the temporary node
        try {
            $acl->temporary->remove();
        } catch (Exception $e) {
            $this->fail('Cannot remove temporary node: ' . $e->getMessage());
        }

        // Test for non-existent node
        try {
            $path = 'nonexistent';
            $acl->remove($path);
            $this->fail('Expected exception not thrown when removing non-existent node');
        } catch (Zend_Acl_Exception $expected) {
            $this->assertContains($path, $expected->getMessage());
        } catch (Exception $e) {
            $this->fail('Unexpected exception caught: ' . $e->getMessage());
        }

        // Ensure we cannot remove root node
        try {
            $acl->remove();
            $this->fail('Expected exception not thrown when removing root node');
        } catch (Zend_Acl_Exception $expected) {
            $this->assertContains('root node', $expected->getMessage());
        } catch (Exception $e) {
            $this->fail('Unexpected exception caught: ' . $e->getMessage());
        }

        // Get a view of the ACL through an ARO's set of permissions
        $acl2 = $guest->getValidAco($acl, 'task3');
        self::assertTrue($acl2->testbranch->valid($guest, 'task3'));
        self::assertFalse($acl2->temporary->valid($guest, 'task4'));
        self::assertFalse($acl2->valid($guest, 'task3'));
        self::assertFalse($acl2->forbidden->valid($guest));

        // See if we can reverse-lookup the valid ARO for this new acl
        $group = $acl->getValidAro('task1');
        self::assertTrue(isset($group['guest']));

        // Remove guest from registry and test return results
        self::assertTrue($aro->remove($guest, $acl));
        self::assertFalse($aro->remove('nonexistent', $acl));

        // Helps code coverage
        $acl->removeAro('othernonexistent', null, '/nonexistent/path');

        // Ensure reference to guest now returns default ARO
        self::assertTrue($aro->guest->getId() == '_default');

        // Reset all permissions on root node for Guest and check for defaults
        $acl2->removeAllow('guest');
        $acl2->removeDeny('guest');
        self::assertTrue($acl2->testnode->valid($guest) === Zend_Acl::PERM_DEFAULT);

        // Return an array of ARO members from the registry
        $list = $aro->toArray();
        self::assertTrue(is_array($list));
    }

}


class AssertFalse implements Zend_Acl_Assert_Interface
{
    public function assert(Zend_Acl $acl, Zend_Acl_Aro_Interface $aro = null, Zend_Acl_Aco_Interface $aco = null,
                           $privilege = null)
    {
       return false;
    }
}


class AssertTrue implements Zend_Acl_Assert_Interface
{
    public function assert(Zend_Acl $acl, Zend_Acl_Aro_Interface $aro = null, Zend_Acl_Aco_Interface $aco = null,
                           $privilege = null)
    {
       return true;
    }
}