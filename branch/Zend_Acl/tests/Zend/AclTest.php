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
     * Ensures that an exception is thrown when a non-existent ARO is specified for removal
     *
     * @return void
     */
    public function testARORegistryRemoveOneNonExistent()
    {
        try {
            $this->_acl->getAroRegistry()->remove('nonexistent');
            $this->fail('Expected Zend_Acl_Aro_Registry_Exception not thrown upon removing a non-existent ARO');
        } catch (Zend_Acl_Aro_Registry_Exception $e) {
            $this->assertContains('not found', $e->getMessage());
        }
    }

    /**
     * Ensures that removal of all AROs works
     *
     * @return void
     */
    public function testARORegistryRemoveAll()
    {
        $aroGuest = new Zend_Acl_Aro('guest');
        $this->_acl->getAroRegistry()->add($aroGuest)
                                     ->removeAll();
        $this->assertFalse($this->_acl->getAroRegistry()->has($aroGuest));
    }

    /**
     * Ensures that an exception is thrown when a non-existent ARO is specified as a parent upon ARO addition
     *
     * @return void
     */
    public function testARORegistryAddInheritsNonExistent()
    {
        try {
            $this->_acl->getAroRegistry()->add(new Zend_Acl_Aro('guest'), 'nonexistent');
            $this->fail('Expected Zend_Acl_Aro_Registry_Exception not thrown upon specifying a non-existent parent');
        } catch (Zend_Acl_Aro_Registry_Exception $e) {
            $this->assertContains('does not exist', $e->getMessage());
        }
    }

    /**
     * Ensures that an exception is thrown when a non-existent ARO is specified to each parameter of inherits()
     *
     * @return void
     */
    public function testARORegistryInheritsNonExistent()
    {
        $aroGuest = new Zend_Acl_Aro('guest');
        $this->_acl->getAroRegistry()->add($aroGuest);
        try {
            $this->_acl->getAroRegistry()->inherits('nonexistent', $aroGuest);
            $this->fail('Expected Zend_Acl_Aro_Registry_Exception not thrown upon specifying a non-existent child ARO');
        } catch (Zend_Acl_Aro_Registry_Exception $e) {
            $this->assertContains('not found', $e->getMessage());
        }
        try {
            $this->_acl->getAroRegistry()->inherits($aroGuest, 'nonexistent');
            $this->fail('Expected Zend_Acl_Aro_Registry_Exception not thrown upon specifying a non-existent parent ARO');
        } catch (Zend_Acl_Aro_Registry_Exception $e) {
            $this->assertContains('not found', $e->getMessage());
        }
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
        $aroGuest = new Zend_Acl_Aro('guest');
        try {
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
        $aroGuest1 = new Zend_Acl_Aro('guest');
        $aroGuest2 = new Zend_Acl_Aro('guest');
        try {
            $this->_acl->getAroRegistry()->add($aroGuest1)
                                         ->add($aroGuest2);
            $this->fail('Expected exception not thrown upon adding two AROs with same ID');
        } catch (Zend_Acl_Aro_Registry_Exception $e) {
            $this->assertContains('already exists', $e->getMessage());
        }
    }

    /**
     * Ensures that basic addition and retrieval of a single ACO works
     *
     * @return void
     */
    public function testACOAddAndGetOne()
    {
        $acoArea = new Zend_Acl_Aco('area');
        $aco = $this->_acl->add($acoArea)
                          ->get($acoArea->getAcoId());
        $this->assertTrue($acoArea === $aco);
        $aco = $this->_acl->get($acoArea);
        $this->assertTrue($acoArea === $aco);
    }

    /**
     * Ensures that basic removal of a single ACO works
     *
     * @return void
     */
    public function testACORemoveOne()
    {
        $acoArea = new Zend_Acl_Aco('area');
        $aroRegistry = $this->_acl->getAroRegistry();
        $this->_acl->add($acoArea)
                   ->remove($acoArea);
        $this->assertFalse($this->_acl->has($acoArea));
    }

    /**
     * Ensures that an exception is thrown when a non-existent ACO is specified for removal
     *
     * @return void
     */
    public function testACORemoveOneNonExistent()
    {
        try {
            $this->_acl->remove('nonexistent');
            $this->fail('Expected Zend_Acl_Exception not thrown upon removing a non-existent ACO');
        } catch (Zend_Acl_Exception $e) {
            $this->assertContains('not found', $e->getMessage());
        }
    }

    /**
     * Ensures that removal of all ACOs works
     *
     * @return void
     */
    public function testACORemoveAll()
    {
        $acoArea = new Zend_Acl_Aco('area');
        $this->_acl->add($acoArea)
                   ->removeAll();
        $this->assertFalse($this->_acl->has($acoArea));
    }

    /**
     * Ensures that an exception is thrown when a non-existent ACO is specified as a parent upon ACO addition
     *
     * @return void
     */
    public function testACOAddInheritsNonExistent()
    {
        try {
            $this->_acl->add(new Zend_Acl_Aco('area'), 'nonexistent');
            $this->fail('Expected Zend_Acl_Exception not thrown upon specifying a non-existent parent');
        } catch (Zend_Acl_Exception $e) {
            $this->assertContains('does not exist', $e->getMessage());
        }
    }

    /**
     * Ensures that an exception is thrown when a non-existent ACO is specified to each parameter of inherits()
     *
     * @return void
     */
    public function testACOInheritsNonExistent()
    {
        $acoArea = new Zend_Acl_Aco('area');
        $this->_acl->add($acoArea);
        try {
            $this->_acl->inherits('nonexistent', $acoArea);
            $this->fail('Expected Zend_Acl_Exception not thrown upon specifying a non-existent child ACO');
        } catch (Zend_Acl_Exception $e) {
            $this->assertContains('not found', $e->getMessage());
        }
        try {
            $this->_acl->inherits($acoArea, 'nonexistent');
            $this->fail('Expected Zend_Acl_Exception not thrown upon specifying a non-existent parent ACO');
        } catch (Zend_Acl_Exception $e) {
            $this->assertContains('not found', $e->getMessage());
        }
    }

    /**
     * Tests basic ACO inheritance
     *
     * @return void
     */
    public function testACOInherits()
    {
        $acoCity     = new Zend_Acl_Aco('city');
        $acoBuilding = new Zend_Acl_Aco('building');
        $acoRoom     = new Zend_Acl_Aco('room');
        $this->_acl->add($acoCity)
                   ->add($acoBuilding, $acoCity->getAcoId())
                   ->add($acoRoom, $acoBuilding);
        $this->assertTrue($this->_acl->inherits($acoBuilding, $acoCity, true));
        $this->assertTrue($this->_acl->inherits($acoRoom, $acoBuilding, true));
        $this->assertTrue($this->_acl->inherits($acoRoom, $acoCity));
        $this->assertFalse($this->_acl->inherits($acoCity, $acoBuilding));
        $this->assertFalse($this->_acl->inherits($acoBuilding, $acoRoom));
        $this->assertFalse($this->_acl->inherits($acoCity, $acoRoom));
        $this->_acl->remove($acoBuilding);
        $this->assertFalse($this->_acl->has($acoRoom));
    }

    /**
     * Ensures that the same ACO cannot be added more than once
     *
     * @return void
     */
    public function testACODuplicate()
    {
        try {
            $acoArea = new Zend_Acl_Aco('area');
            $this->_acl->add($acoArea)
                       ->add($acoArea);
            $this->fail('Expected exception not thrown upon adding same ACO twice');
        } catch (Zend_Acl_Exception $e) {
            $this->assertContains('already exists', $e->getMessage());
        }
    }

    /**
     * Ensures that two ACOs having the same ID cannot be added
     *
     * @return void
     */
    public function testACODuplicateId()
    {
        try {
            $acoArea1 = new Zend_Acl_Aco('area');
            $acoArea2 = new Zend_Acl_Aco('area');
            $this->_acl->add($acoArea1)
                       ->add($acoArea2);
            $this->fail('Expected exception not thrown upon adding two ACOs with same ID');
        } catch (Zend_Acl_Exception $e) {
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
     * Ensures that the default rule obeys its assertion
     *
     * @return void
     */
    public function testDefaultAssert()
    {
        $this->_acl->deny(null, null, null, new Zend_AclTest_AssertFalse());
        $this->assertTrue($this->_acl->isAllowed());
        $this->assertTrue($this->_acl->isAllowed(null, null, 'somePrivilege'));
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
        $this->_acl->allow(null, null, 'somePrivilege', new Zend_AclTest_AssertTrue());
        $this->assertTrue($this->_acl->isAllowed(null, null, 'somePrivilege'));
        $this->_acl->allow(null, null, 'somePrivilege', new Zend_AclTest_AssertFalse());
        $this->assertFalse($this->_acl->isAllowed(null, null, 'somePrivilege'));
    }

    /**
     * Ensures that by default, Zend_Acl denies access to everything for a particular ARO
     *
     * @return void
     */
    public function testARODefaultDeny()
    {
        $aroGuest = new Zend_Acl_Aro('guest');
        $this->_acl->getAroRegistry()->add($aroGuest);
        $this->assertFalse($this->_acl->isAllowed($aroGuest));
    }

    /**
     * Ensures that ACL-wide rules (all ACOs and privileges) work properly for a particular ARO
     *
     * @return void
     */
    public function testARODefaultRuleSet()
    {
        $aroGuest = new Zend_Acl_Aro('guest');
        $this->_acl->getAroRegistry()->add($aroGuest);
        $this->_acl->allow($aroGuest);
        $this->assertTrue($this->_acl->isAllowed($aroGuest));
        $this->_acl->deny($aroGuest);
        $this->assertFalse($this->_acl->isAllowed($aroGuest));
    }

    /**
     * Ensures that by default, Zend_Acl denies access to a privilege on anything for a particular ARO
     *
     * @return void
     */
    public function testARODefaultPrivilegeDeny()
    {
        $aroGuest = new Zend_Acl_Aro('guest');
        $this->_acl->getAroRegistry()->add($aroGuest);
        $this->assertFalse($this->_acl->isAllowed($aroGuest, null, 'somePrivilege'));
    }

    /**
     * Ensures that ACL-wide rules apply to privileges for a particular ARO
     *
     * @return void
     */
    public function testARODefaultRuleSetPrivilege()
    {
        $aroGuest = new Zend_Acl_Aro('guest');
        $this->_acl->getAroRegistry()->add($aroGuest);
        $this->_acl->allow($aroGuest);
        $this->assertTrue($this->_acl->isAllowed($aroGuest, null, 'somePrivilege'));
        $this->_acl->deny($aroGuest);
        $this->assertFalse($this->_acl->isAllowed($aroGuest, null, 'somePrivilege'));
    }

    /**
     * Ensures that a privilege allowed for a particular ARO upon all ACOs works properly
     *
     * @return void
     */
    public function testAROPrivilegeAllow()
    {
        $aroGuest = new Zend_Acl_Aro('guest');
        $this->_acl->getAroRegistry()->add($aroGuest);
        $this->_acl->allow($aroGuest, null, 'somePrivilege');
        $this->assertTrue($this->_acl->isAllowed($aroGuest, null, 'somePrivilege'));
    }

    /**
     * Ensures that a privilege denied for a particular ARO upon all ACOs works properly
     *
     * @return void
     */
    public function testAROPrivilegeDeny()
    {
        $aroGuest = new Zend_Acl_Aro('guest');
        $this->_acl->getAroRegistry()->add($aroGuest);
        $this->_acl->allow($aroGuest);
        $this->_acl->deny($aroGuest, null, 'somePrivilege');
        $this->assertFalse($this->_acl->isAllowed($aroGuest, null, 'somePrivilege'));
    }

    /**
     * Ensures that multiple privileges work properly for a particular ARO
     *
     * @return void
     */
    public function testAROPrivileges()
    {
        $aroGuest = new Zend_Acl_Aro('guest');
        $this->_acl->getAroRegistry()->add($aroGuest);
        $this->_acl->allow($aroGuest, null, array('p1', 'p2', 'p3'));
        $this->assertTrue($this->_acl->isAllowed($aroGuest, null, 'p1'));
        $this->assertTrue($this->_acl->isAllowed($aroGuest, null, 'p2'));
        $this->assertTrue($this->_acl->isAllowed($aroGuest, null, 'p3'));
        $this->assertFalse($this->_acl->isAllowed($aroGuest, null, 'p4'));
        $this->_acl->deny($aroGuest, null, 'p1');
        $this->assertFalse($this->_acl->isAllowed($aroGuest, null, 'p1'));
        $this->_acl->deny($aroGuest, null, array('p2', 'p3'));
        $this->assertFalse($this->_acl->isAllowed($aroGuest, null, 'p2'));
        $this->assertFalse($this->_acl->isAllowed($aroGuest, null, 'p3'));
    }

    /**
     * Ensures that assertions on privileges work properly for a particular ARO
     *
     * @return void
     */
    public function testAROPrivilegeAssert()
    {
        $aroGuest = new Zend_Acl_Aro('guest');
        $this->_acl->getAroRegistry()->add($aroGuest);
        $this->_acl->allow($aroGuest, null, 'somePrivilege', new Zend_AclTest_AssertTrue());
        $this->assertTrue($this->_acl->isAllowed($aroGuest, null, 'somePrivilege'));
        $this->_acl->allow($aroGuest, null, 'somePrivilege', new Zend_AclTest_AssertFalse());
        $this->assertFalse($this->_acl->isAllowed($aroGuest, null, 'somePrivilege'));
    }

    /**
     * Ensures that removing the default deny rule results in default deny rule
     *
     * @return void
     */
    public function testRemoveDefaultDeny()
    {
        $this->assertFalse($this->_acl->isAllowed());
        $this->_acl->removeDeny();
        $this->assertFalse($this->_acl->isAllowed());
    }

    /**
     * Ensures that removing the default deny rule results in assertion method being removed
     *
     * @return void
     */
    public function testRemoveDefaultDenyAssert()
    {
        $this->_acl->deny(null, null, null, new Zend_AclTest_AssertFalse());
        $this->assertTrue($this->_acl->isAllowed());
        $this->_acl->removeDeny();
        $this->assertFalse($this->_acl->isAllowed());
    }

    /**
     * Ensures that removing the default allow rule results in default deny rule being assigned
     *
     * @return void
     */
    public function testRemoveDefaultAllow()
    {
        $this->_acl->allow();
        $this->assertTrue($this->_acl->isAllowed());
        $this->_acl->removeAllow();
        $this->assertFalse($this->_acl->isAllowed());
    }

    /**
     * Ensures that removing non-existent default allow rule does nothing
     *
     * @return void
     */
    public function testRemoveDefaultAllowNonExistent()
    {
        $this->_acl->removeAllow();
        $this->assertFalse($this->_acl->isAllowed());
    }

    /**
     * Ensures that removing non-existent default deny rule does nothing
     *
     * @return void
     */
    public function testRemoveDefaultDenyNonExistent()
    {
        $this->_acl->allow();
        $this->_acl->removeDeny();
        $this->assertTrue($this->_acl->isAllowed());
    }

    /**
     * Ensures that for a particular ARO, a deny rule on a specific ACO is honored before an allow rule
     * on the entire ACL
     *
     * @return void
     */
    public function testARODefaultAllowRuleWithACODenyRule()
    {
        $this->_acl->getAroRegistry()->add(new Zend_Acl_Aro('guest'))
                                     ->add(new Zend_Acl_Aro('staff'), 'guest');

        $this->_acl->add(new Zend_Acl_Aco('area1'));
        $this->_acl->add(new Zend_Acl_Aco('area2'));

        $this->_acl->deny();
        $this->_acl->allow('staff');
        $this->_acl->deny('staff', array('area1', 'area2'));

        $this->assertFalse($this->_acl->isAllowed('staff', 'area1'));
    }

    /**
     * Ensures that for a particular ARO, a deny rule on a specific privilege is honored before an allow
     * rule on the entire ACL
     *
     * @return void
     */
    public function testARODefaultAllowRuleWithPrivilegeDenyRule()
    {
        $this->_acl->getAroRegistry()->add(new Zend_Acl_Aro('guest'))
                                     ->add(new Zend_Acl_Aro('staff'), 'guest');

        $this->_acl->deny();
        $this->_acl->allow('staff');
        $this->_acl->deny('staff', null, array('privilege1', 'privilege2'));

        $this->assertFalse($this->_acl->isAllowed('staff', null, 'privilege1'));
    }

    public function testCMSExample()
    {
        // Add some roles to the ARO registry
        $this->_acl->getAroRegistry()->add(new Zend_Acl_Aro('guest'))
                                     ->add(new Zend_Acl_Aro('staff'), 'guest')  // staff inherits permissions from guest
                                     ->add(new Zend_Acl_Aro('editor'), 'staff') // editor inherits permissions from staff
                                     ->add(new Zend_Acl_Aro('administrator'));

        // Guest may only view content
        $this->_acl->allow('guest', null, 'view');

        // Staff inherits view privilege from guest, but also needs additional privileges
        $this->_acl->allow('staff', null, array('edit', 'submit', 'revise'));

        // Editor inherits view, edit, submit, and revise privileges, but also needs additional privileges
        $this->_acl->allow('editor', null, array('publish', 'archive', 'delete'));

        // Administrator inherits nothing but is allowed all privileges
        $this->_acl->allow('administrator');

        // Access control checks based on above permission sets

        $this->assertTrue($this->_acl->isAllowed('guest', null, 'view'));
        $this->assertFalse($this->_acl->isAllowed('guest', null, 'edit'));
        $this->assertFalse($this->_acl->isAllowed('guest', null, 'submit'));
        $this->assertFalse($this->_acl->isAllowed('guest', null, 'revise'));
        $this->assertFalse($this->_acl->isAllowed('guest', null, 'publish'));
        $this->assertFalse($this->_acl->isAllowed('guest', null, 'archive'));
        $this->assertFalse($this->_acl->isAllowed('guest', null, 'delete'));
        $this->assertFalse($this->_acl->isAllowed('guest', null, 'unknown'));
        $this->assertFalse($this->_acl->isAllowed('guest'));

        $this->assertTrue($this->_acl->isAllowed('staff', null, 'view'));
        $this->assertTrue($this->_acl->isAllowed('staff', null, 'edit'));
        $this->assertTrue($this->_acl->isAllowed('staff', null, 'submit'));
        $this->assertTrue($this->_acl->isAllowed('staff', null, 'revise'));
        $this->assertFalse($this->_acl->isAllowed('staff', null, 'publish'));
        $this->assertFalse($this->_acl->isAllowed('staff', null, 'archive'));
        $this->assertFalse($this->_acl->isAllowed('staff', null, 'delete'));
        $this->assertFalse($this->_acl->isAllowed('staff', null, 'unknown'));
        $this->assertFalse($this->_acl->isAllowed('staff'));

        $this->assertTrue($this->_acl->isAllowed('editor', null, 'view'));
        $this->assertTrue($this->_acl->isAllowed('editor', null, 'edit'));
        $this->assertTrue($this->_acl->isAllowed('editor', null, 'submit'));
        $this->assertTrue($this->_acl->isAllowed('editor', null, 'revise'));
        $this->assertTrue($this->_acl->isAllowed('editor', null, 'publish'));
        $this->assertTrue($this->_acl->isAllowed('editor', null, 'archive'));
        $this->assertTrue($this->_acl->isAllowed('editor', null, 'delete'));
        $this->assertFalse($this->_acl->isAllowed('editor', null, 'unknown'));
        $this->assertFalse($this->_acl->isAllowed('editor'));

        $this->assertTrue($this->_acl->isAllowed('administrator', null, 'view'));
        $this->assertTrue($this->_acl->isAllowed('administrator', null, 'edit'));
        $this->assertTrue($this->_acl->isAllowed('administrator', null, 'submit'));
        $this->assertTrue($this->_acl->isAllowed('administrator', null, 'revise'));
        $this->assertTrue($this->_acl->isAllowed('administrator', null, 'publish'));
        $this->assertTrue($this->_acl->isAllowed('administrator', null, 'archive'));
        $this->assertTrue($this->_acl->isAllowed('administrator', null, 'delete'));
        $this->assertTrue($this->_acl->isAllowed('administrator', null, 'unknown'));
        $this->assertTrue($this->_acl->isAllowed('administrator'));

        // Some checks on specific areas, which inherit access controls from the root ACL node
        $this->_acl->add(new Zend_Acl_Aco('newsletter'))
                   ->add(new Zend_Acl_Aco('pending'), 'newsletter')
                   ->add(new Zend_Acl_Aco('gallery'))
                   ->add(new Zend_Acl_Aco('profiles', 'gallery'))
                   ->add(new Zend_Acl_Aco('config'))
                   ->add(new Zend_Acl_Aco('hosts'), 'config');
        $this->assertTrue($this->_acl->isAllowed('guest', 'pending', 'view'));
        $this->assertTrue($this->_acl->isAllowed('staff', 'profiles', 'revise'));
        $this->assertFalse($this->_acl->isAllowed('editor', 'hosts', 'unknown'));

        return;

        // Checking permissions from the perspective of an ARO
        $this->assertTrue($aro->staff->canAccess($this->_acl->newsletter->pending, 'view'));
        $this->assertTrue($aro->staff->canAccess($this->_acl->newsletter->pending, 'edit'));
        $this->assertFalse($aro->staff->canAccess($this->_acl->newsletter->pending, 'publish'));
        $this->assertFalse($aro->staff->canAccess($this->_acl->newsletter->pending));
        $this->assertTrue($aro->administrator->canAccess($this->_acl->newsletter->pending));

        // Unknown ARO
        $this->assertFalse($aro->unknown->canAccess($this->_acl->newsletter->pending));

        // Add a new group, marketing, which bases its permissions on staff
        $aro->add('marketing', 'staff');

        // Refine the privilege sets for more specific needs

        // Allow marketing to publish and archive newsletters
        $this->_acl->newsletter->allow($aro->marketing, array('publish', 'archive'));

        // Allow marketing to publish and archive latest news
        $this->_acl->news->latest->allow($aro->marketing, array('publish', 'archive'));

        // Deny staff (and marketing, by inheritance) rights to revise latest news
        $this->_acl->news->latest->deny($aro->staff, 'revise');

        // Deny everyone access to archive news announcements
        $this->_acl->news->announcement->deny(null, 'archive');

        // Access control checks for the above refined permission sets

        $this->assertTrue($this->_acl->isAllowed('marketing', 'view'));
        $this->assertTrue($this->_acl->isAllowed('marketing', 'edit'));
        $this->assertTrue($this->_acl->isAllowed('marketing', 'submit'));
        $this->assertTrue($this->_acl->isAllowed($aro->marketing, 'revise'));
        $this->assertFalse($this->_acl->isAllowed('marketing', 'publish'));
        $this->assertFalse($this->_acl->isAllowed('marketing', 'archive'));
        $this->assertFalse($this->_acl->isAllowed($aro->marketing, 'delete'));
        $this->assertFalse($this->_acl->isAllowed('marketing', 'unknown'));
        $this->assertFalse($this->_acl->isAllowed('marketing'));

        $this->assertTrue($this->_acl->newsletter->isAllowed('marketing', 'publish'));
        $this->assertFalse($this->_acl->newsletter->pending->isAllowed('staff', 'publish'));
        $this->assertTrue($this->_acl->newsletter->pending->isAllowed('marketing', 'publish'));
        $this->assertTrue($this->_acl->newsletter->isAllowed('marketing', 'archive'));
        $this->assertFalse($this->_acl->newsletter->isAllowed('marketing', 'delete'));
        $this->assertFalse($this->_acl->newsletter->isAllowed('marketing'));

        $this->assertTrue($this->_acl->news->latest->isAllowed('marketing', 'publish'));
        $this->assertTrue($this->_acl->news->latest->isAllowed('marketing', 'archive'));
        $this->assertFalse($this->_acl->news->latest->isAllowed('marketing', 'delete'));
        $this->assertFalse($this->_acl->news->latest->isAllowed('marketing', 'revise'));
        $this->assertFalse($this->_acl->news->latest->isAllowed('staging', 'revise'));
        $this->assertFalse($this->_acl->news->latest->isAllowed('marketing'));

        $this->assertFalse($this->_acl->news->announcement->isAllowed('marketing', 'archive'));
        $this->assertFalse($this->_acl->news->announcement->isAllowed('staff', 'archive'));
        $this->assertFalse($this->_acl->news->announcement->isAllowed('administrator', 'archive'));

        $this->assertFalse($aro->staff->canAccess($this->_acl->news->latest, 'publish'));
        $this->assertTrue($aro->marketing->canAccess($this->_acl->news->latest, 'publish'));
        $this->assertFalse($aro->editor->canAccess($this->_acl->news->announcement, 'archive'));

        // Remove some previous permission specifications

        // Marketing can no longer publish and archive newsletters
        $this->_acl->newsletter->removeAllow('marketing', array('publish', 'archive'));

        // Marketing can no longer archive the latest news
        $this->_acl->news->latest->removeAllow($aro->marketing, 'archive');

        // Now staff (and marketing, by inheritance) may revise latest news
        $this->_acl->news->latest->removeDeny($aro->staff, 'revise');

        // Access control checks for the above refinements

        $this->assertFalse($this->_acl->newsletter->isAllowed('marketing', 'publish'));
        $this->assertFalse($this->_acl->newsletter->isAllowed('marketing', 'archive'));

        $this->assertFalse($this->_acl->news->latest->isAllowed('marketing', 'archive'));

        $this->assertTrue($this->_acl->news->latest->isAllowed('staff', 'revise'));
        $this->assertTrue($this->_acl->news->latest->isAllowed($aro->marketing, 'revise'));

        // Grant marketing all permissions on the latest news
        $this->_acl->news->latest->allow('marketing');

        // Access control checks for the above refinement
        $this->assertTrue($this->_acl->news->latest->isAllowed('marketing', 'archive'));
        $this->assertTrue($this->_acl->news->latest->isAllowed('marketing', 'publish'));
        $this->assertTrue($this->_acl->news->latest->isAllowed('marketing', 'edit'));
        $this->assertTrue($this->_acl->news->latest->isAllowed('marketing'));

    }

}


class Zend_AclTest_AssertFalse implements Zend_Acl_Assert_Interface
{
    public function assert(Zend_Acl $acl, Zend_Acl_Aro_Interface $aro = null, Zend_Acl_Aco_Interface $aco = null,
                           $privilege = null)
    {
       return false;
    }
}


class Zend_AclTest_AssertTrue implements Zend_Acl_Assert_Interface
{
    public function assert(Zend_Acl $acl, Zend_Acl_Aro_Interface $aro = null, Zend_Acl_Aco_Interface $aco = null,
                           $privilege = null)
    {
       return true;
    }
}