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
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * Zend
 */
require_once 'Zend.php';


/**
 * Zend_Acl_Aco_Interface
 */
require_once 'Zend/Acl/Aco/Interface.php';


/**
 * Zend_Acl_Aro_Registry
 */
require_once 'Zend/Acl/Aro/Registry.php';


/**
 * Zend_Acl_Assert_Interface
 */
require_once 'Zend/Acl/Assert/Interface.php';


/**
 * @category   Zend
 * @package    Zend_Acl
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Acl
{
    /**
     * Rule type: allow
     */
    const TYPE_ALLOW = 'TYPE_ALLOW';

    /**
     * Rule type: deny
     */
    const TYPE_DENY  = 'TYPE_DENY';

    /**
     * Rule operation: add
     */
    const OP_ADD = 'OP_ADD';

    /**
     * Rule operation: remove
     */
    const OP_REMOVE = 'OP_REMOVE';

    /**
     * ARO registry
     *
     * @var Zend_Acl_Aro_Registry
     */
    protected $_aroRegistry = null;

    /**
     * ACO tree
     *
     * @var array
     */
    protected $_acos = array();

    /**
     * Creates a whitelist implementation (deny everything to all) by default
     *
     * @return void
     */
    public function __construct()
    {
        $this->deny();
    }

    /**
     * Returns the ARO registry for this ACL
     *
     * If no ARO registry has been created yet, a new default ARO registry
     * is created and returned.
     *
     * @return Zend_Acl_Aro_Registry
     */
    public function getAroRegistry()
    {
        if (null === $this->_aroRegistry) {
            $this->_aroRegistry = new Zend_Acl_Aro_Registry();
        }
        return $this->_aroRegistry;
    }

    /**
     * Sets the ARO registry to use for this ACL
     *
     * If $aroRegistry is null, then the ARO registry is replaced with a new
     * and empty default ARO registry.
     *
     * @param  Zend_Acl_Aro_Registry $aroRegistry
     * @return Zend_Acl Provides a fluent interface
     */
    public function setAroRegistry(Zend_Acl_Aro_Registry $aroRegistry = null)
    {
        if (null === $aroRegistry) {
            $aroRegistry = new Zend_Acl_Aro_Registry();
        }
        $this->_aroRegistry = $aroRegistry;
        return $this;
    }

    /**
     * Adds an ACO having an identifier unique to the ACL
     *
     * The $parent parameter may be a reference to, or the string identifier for,
     * the existing ACO from which the newly added ACO will inherit.
     *
     * @param  Zend_Acl_Aco_Interface        $aco
     * @param  Zend_Acl_Aco_Interface|string $parent
     * @throws Zend_Acl_Exception
     * @return self Provides a fluent interface
     */
    public function add(Zend_Acl_Aco_Interface $aco, $parent = null)
    {
        $acoId = $aco->getAcoId();

        if ($this->has($acoId)) {
            throw Zend::exception('Zend_Acl_Exception',
                                  "ACO id '$acoId' already exists in the ACL");
        }

        $acoParent = null;

        if (null !== $parent) {
            Zend::loadClass('Zend_Acl_Exception');
            try {
                if ($parent instanceof Zend_Acl_Aco_Interface) {
                    $acoParentId = $parent->getAcoId();
                } else {
                    $acoParentId = $parent;
                }
                $acoParent = $this->get($acoParentId);
            } catch (Zend_Acl_Exception $e) {
                throw new Zend_Acl_Exception("Parent ACO id '$acoParentId' does not exist");
            }
            $this->_acos[$acoParentId]['children'][$acoId] = $aco;
        }

        $this->_acos[$acoId] = array(
            'instance' => $aco,
            'parent'   => $acoParent,
            'children' => array()
            );

        return $this;
    }

    /**
     * Returns the identified ACO
     *
     * The $aco parameter can either be an ACO or an ACO identifier.
     *
     * @param  Zend_Acl_Aco_Interface|string $aco
     * @throws Zend_Acl_Exception
     * @return Zend_Acl_Aco_Interface
     */
    public function get($aco)
    {
        if ($aco instanceof Zend_Acl_Aco_Interface) {
            $acoId = $aco->getAcoId();
        } else {
            $acoId = $aco;
        }

        if (!$this->has($aco)) {
            throw Zend::exception('Zend_Acl_Exception',
                                  "ACO '$acoId' not found");
        }

        return $this->_acos[$acoId]['instance'];
    }

    /**
     * Returns true if and only if the ACO exists in the ACL
     *
     * The $aco parameter can either be an ACO or an ACO identifier.
     *
     * @param  Zend_Acl_Aco_Interface|string $aco
     * @return boolean
     */
    public function has($aco)
    {
        if ($aco instanceof Zend_Acl_Aco_Interface) {
            $acoId = $aco->getAcoId();
        } else {
            $acoId = $aco;
        }

        return isset($this->_acos[$acoId]);
    }

    /**
     * Returns true if and only if $aco inherits from $inherit
     *
     * Both parameters may be either an ACO or an ACO identifier. If
     * $onlyParent is true, then $aco must inherit directly from
     * $inherit in order to return true. By default, this method looks
     * through the entire inheritance tree to determine whether $aco
     * inherits from $inherit through its ancestor ACOs.
     *
     * @param  Zend_Acl_Aco_Interface|string $aco
     * @param  Zend_Acl_Aco_Interface|string $inherit
     * @param  boolean                       $onlyParent
     * @throws Zend_Acl_Aco_Registry_Exception
     * @return boolean
     */
    public function inherits($aco, $inherit, $onlyParent = false)
    {
        Zend::loadClass('Zend_Acl_Exception');

        try {
            $acoId     = $this->get($aco)->getAcoId();
            $inheritId = $this->get($inherit)->getAcoId();
        } catch (Zend_Acl_Exception $e) {
            throw $e;
        }

        $inherits = (null !== $this->_acos[$acoId]['parent']
                     && $inheritId === ($parentId = key($this->_acos[$acoId]['parent'])));

        if ($inherits || $onlyParent) {
            return $inherits;
        }

        while (null !== $this->_acos[$parentId]['parent']) {
            $parentId = key($this->_acos[$parentId]['parent']);
            if ($inheritId === $parentId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Removes an ACO and all of its children
     *
     * The $aco parameter can either be an ACO or an ACO identifier.
     *
     * @param  Zend_Acl_Aco_Interface|string $aco
     * @throws Zend_Acl_Exception
     * @return self Provides a fluent interface
     */
    public function remove($aco)
    {
        Zend::loadClass('Zend_Acl_Exception');

        try {
            $acoId = $this->get($aco)->getAcoId();
        } catch (Zend_Acl_Exception $e) {
            throw $e;
        }

        if (null !== $this->_acos[$acoId]['parent']) {
            unset($this->_acos[$acoId]['parent']['children'][$acoId]);
        }
        foreach ($this->_acos[$acoId]['children'] as $childId => $child) {
            $this->remove($childId);
        }

        unset($this->_acos[$acoId]);

        return $this;
    }

    /**
     * Removes all ACOs
     *
     * @return self Provides a fluent interface
     */
    public function removeAll()
    {
        $this->_acos = array();

        return $this;
    }

    /**
     * Adds an "allow" rule to the ACL
     *
     * @param  Zend_Acl_Aro_Interface|string|array $aro
     * @param  Zend_Acl_Aco_Interface|string|array $aco
     * @param  string|array                        $privileges
     * @param  Zend_Acl_Assert_Interface           $assert
     * @uses   Zend_Acl::setRule()
     * @return self Provides a fluent interface
     */
    public function allow($aro = null, $aco = null, $privileges = null, Zend_Acl_Assert_Interface $assert = null)
    {
        return $this->setRule(self::OP_ADD, self::TYPE_ALLOW, $aro, $aco, $privileges, $assert);
    }

    /**
     * Adds a "deny" rule to the ACL
     *
     * @param  Zend_Acl_Aro_Interface|string|array $aro
     * @param  Zend_Acl_Aco_Interface|string|array $aco
     * @param  string|array                        $privileges
     * @param  Zend_Acl_Assert_Interface           $assert
     * @uses   Zend_Acl::setRule()
     * @return self Provides a fluent interface
     */
    public function deny($aro = null, $aco = null, $privileges = null, Zend_Acl_Assert_Interface $assert = null)
    {
        return $this->setRule(self::OP_ADD, self::TYPE_DENY, $aro, $aco, $privileges, $assert);
    }

    /**
     * Removes "allow" permissions from the ACL
     *
     * @param  Zend_Acl_Aro_Interface|string|array $aro
     * @param  Zend_Acl_Aco_Interface|string|array $aco
     * @param  string|array                        $privileges
     * @uses   Zend_Acl::setRule()
     * @return self Provides a fluent interface
     */
    public function removeAllow($aro = null, $aco = null, $privileges = null)
    {
        return $this->setRule(self::OP_REMOVE, self::TYPE_ALLOW, $aro, $aco, $privileges);
    }

    /**
     * Removes "deny" restrictions from the ACL
     *
     * @param  Zend_Acl_Aro_Interface|string|array $aro
     * @param  Zend_Acl_Aco_Interface|string|array $aco
     * @param  string|array                        $privileges
     * @uses   Zend_Acl::setRule()
     * @return self Provides a fluent interface
     */
    public function removeDeny($aro = null, $aco = null, $privileges = null)
    {
        return $this->setRule(self::OP_REMOVE, self::TYPE_DENY, $aro, $aco, $privileges);
    }

    /**
     * Performs operations on ACL rules
     *
     * The $operation parameter may be either OP_ADD or OP_REMOVE, depending on whether the
     * user wants to add or remove a rule, respectively:
     *
     * OP_ADD specifics:
     *
     *      A rule is added that would allow one or more AROs access to [certain $privileges
     *      upon] the specified ACO(s).
     *
     *      If $assert is provided, then its assert() method must return true in order for
     *      the rule to apply.
     *
     * OP_REMOVE specifics:
     *
     *      The $assert parameter is ignored.
     *
     *      The rule is removed only in the context of the given AROs, ACOs, and privileges.
     *      Existing rules to which the remove operation does not apply would remain in the
     *      ACL.
     *
     * The $type parameter may be either TYPE_ALLOW or TYPE_DENY, depending on whether the
     * rule is intended to allow or deny permission, respectively.
     *
     * The $aro and $aco parameters may be references to, or the string identifiers for,
     * existing ACOs/AROs, or they may be passed as arrays of these - mixing string identifiers
     * and objects is ok - to indicate the ACOs and AROs to which the rule applies.
     *
     * The $privileges parameter may be used to further specify that the rule applies only
     * to certain privileges upon the ACO(s) in question. This may be specified to be a single
     * privilege with a string, and multiple privileges may be specified as an array of strings.
     *
     * @param  string                              $operation
     * @param  string                              $type
     * @param  Zend_Acl_Aro_Interface|string|array $aro
     * @param  Zend_Acl_Aco_Interface|string|array $aco
     * @param  string|array                        $privileges
     * @param  Zend_Acl_Assert_Interface           $assert
     * @throws Zend_Acl_Exception
     * @return self Provides a fluent interface
     */
    public function setRule($operation, $type, $aro = null, $aco = null, $privileges = null,
                            Zend_Acl_Assert_Interface $assert = null)
    {
        /**
         * @todo implementation
         */

        return $this;
    }

    /**
     * Returns true if and only if the ARO has access to the ACO
     *
     * If a $privilege is not provided, then this method returns false if the ARO is
     * denied access to at least one privilege upon the ACO.
     *
     * @param  Zend_Acl_Aro_Interface|string $aro
     * @param  Zend_Acl_Aco_Interface|string $aco
     * @param  string                        $privilege
     * @return boolean
     */
    public function isAllowed($aro, $aco, $privilege = null)
    {
        /**
         * @todo implementation
         */
    }

}
