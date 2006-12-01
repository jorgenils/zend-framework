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
     * ACL rules; whitelist (deny everything to all) by default
     *
     * @var array
     */
    protected $_rules = array(
        'allAcos' => array(
            'allAros' => array(
                'allPrivileges' => array(
                    'type'   => self::TYPE_DENY,
                    'assert' => null
                    ),
                'byPrivilegeId' => array()
                ),
            'byAroId' => array()
            ),
        'byAcoId' => array()
        );

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
     * @return Zend_Acl Provides a fluent interface
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
     * @return Zend_Acl Provides a fluent interface
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
     * @return Zend_Acl Provides a fluent interface
     */
    public function removeAll()
    {
        $this->_acos = array();

        return $this;
    }

    /**
     * Adds an "allow" rule to the ACL
     *
     * @param  Zend_Acl_Aro_Interface|string|array $aros
     * @param  Zend_Acl_Aco_Interface|string|array $acos
     * @param  string|array                        $privileges
     * @param  Zend_Acl_Assert_Interface           $assert
     * @uses   Zend_Acl::setRule()
     * @return Zend_Acl Provides a fluent interface
     */
    public function allow($aros = null, $acos = null, $privileges = null, Zend_Acl_Assert_Interface $assert = null)
    {
        return $this->setRule(self::OP_ADD, self::TYPE_ALLOW, $aros, $acos, $privileges, $assert);
    }

    /**
     * Adds a "deny" rule to the ACL
     *
     * @param  Zend_Acl_Aro_Interface|string|array $aros
     * @param  Zend_Acl_Aco_Interface|string|array $acos
     * @param  string|array                        $privileges
     * @param  Zend_Acl_Assert_Interface           $assert
     * @uses   Zend_Acl::setRule()
     * @return Zend_Acl Provides a fluent interface
     */
    public function deny($aros = null, $acos = null, $privileges = null, Zend_Acl_Assert_Interface $assert = null)
    {
        return $this->setRule(self::OP_ADD, self::TYPE_DENY, $aros, $acos, $privileges, $assert);
    }

    /**
     * Removes "allow" permissions from the ACL
     *
     * @param  Zend_Acl_Aro_Interface|string|array $aros
     * @param  Zend_Acl_Aco_Interface|string|array $acos
     * @param  string|array                        $privileges
     * @uses   Zend_Acl::setRule()
     * @return Zend_Acl Provides a fluent interface
     */
    public function removeAllow($aros = null, $acos = null, $privileges = null)
    {
        return $this->setRule(self::OP_REMOVE, self::TYPE_ALLOW, $aros, $acos, $privileges);
    }

    /**
     * Removes "deny" restrictions from the ACL
     *
     * @param  Zend_Acl_Aro_Interface|string|array $aros
     * @param  Zend_Acl_Aco_Interface|string|array $acos
     * @param  string|array                        $privileges
     * @uses   Zend_Acl::setRule()
     * @return Zend_Acl Provides a fluent interface
     */
    public function removeDeny($aros = null, $acos = null, $privileges = null)
    {
        return $this->setRule(self::OP_REMOVE, self::TYPE_DENY, $aros, $acos, $privileges);
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
     * OP_REMOVE specifics:
     *
     *      The rule is removed only in the context of the given AROs, ACOs, and privileges.
     *      Existing rules to which the remove operation does not apply would remain in the
     *      ACL.
     *
     * The $type parameter may be either TYPE_ALLOW or TYPE_DENY, depending on whether the
     * rule is intended to allow or deny permission, respectively.
     *
     * The $aros and $acos parameters may be references to, or the string identifiers for,
     * existing ACOs/AROs, or they may be passed as arrays of these - mixing string identifiers
     * and objects is ok - to indicate the ACOs and AROs to which the rule applies. If either
     * $aros or $acos is null, then the rule applies to all AROs or all ACOs, respectively.
     * Both may be null in order to work with the default rule of the ACL.
     *
     * The $privileges parameter may be used to further specify that the rule applies only
     * to certain privileges upon the ACO(s) in question. This may be specified to be a single
     * privilege with a string, and multiple privileges may be specified as an array of strings.
     *
     * If $assert is provided, then its assert() method must return true in order for
     * the rule to apply. If $assert is provided with $aros, $acos, and $privileges all
     * equal to null, then a rule having a type of:
     *
     *      TYPE_ALLOW will imply a type of TYPE_DENY, and
     *
     *      TYPE_DENY will imply a type of TYPE_ALLOW
     *
     * when the rule's assertion fails. This is because the ACL needs to provide expected
     * behavior when an assertion upon the default ACL rule fails.
     *
     * @param  string                              $operation
     * @param  string                              $type
     * @param  Zend_Acl_Aro_Interface|string|array $aros
     * @param  Zend_Acl_Aco_Interface|string|array $acos
     * @param  string|array                        $privileges
     * @param  Zend_Acl_Assert_Interface           $assert
     * @throws Zend_Acl_Exception
     * @uses   Zend_Acl_Aro_Registry::get()
     * @uses   Zend_Acl::get()
     * @return Zend_Acl Provides a fluent interface
     */
    public function setRule($operation, $type, $aros = null, $acos = null, $privileges = null,
                            Zend_Acl_Assert_Interface $assert = null)
    {
        // ensure that the rule type is valid; normalize input to uppercase
        $type = strtoupper($type);
        if (self::TYPE_ALLOW !== $type && self::TYPE_DENY !== $type) {
            throw Zend::exception('Zend_Acl_Exception', "Unsupported rule type; must be either '"
                                . self::TYPE_ALLOW . "' or '" . self::TYPE_DENY . "'");
        }

        // ensure that all specified AROs exist; normalize input to array of ARO objects
        $arosTemp = $aros;
        if (null === $arosTemp) {
            $arosTemp = array();
        } else if (!is_array($arosTemp)) {
            $arosTemp = array($arosTemp);
        }
        $aros = array();
        foreach ($arosTemp as $aro) {
            $aros[] = $this->getAroRegistry()->get($aro);
        }
        unset($arosTemp);

        // ensure that all specified ACOs exist; normalize input to array of ACO objects
        $acosTemp = $acos;
        if (null === $acosTemp) {
            $acosTemp = array();
        } else if (!is_array($acosTemp)) {
            $acosTemp = array($acosTemp);
        }
        $acos = array();
        foreach ($acosTemp as $aco) {
            $acos[] = $this->get($aco);
        }
        unset($acosTemp);

        // normalize privileges to array
        if (null === $privileges) {
            $privileges = array();
        } else if (!is_array($privileges)) {
            $privileges = array($privileges);
        }

        switch ($operation) {

            // add to the rules
            case self::OP_ADD:
                if (0 === count($acos)) {
                    if (0 === count($aros)) {
                        if (0 === count($privileges)) {
                            $this->_rules['allAcos']['allAros']['allPrivileges']['type']   = $type;
                            $this->_rules['allAcos']['allAros']['allPrivileges']['assert'] = $assert;
                        } else {
                            foreach ($privileges as $privilege) {
                                $this->_rules['allAcos']['allAros']['byPrivilegeId'][$privilege]['type']   = $type;
                                $this->_rules['allAcos']['allAros']['byPrivilegeId'][$privilege]['assert'] = $assert;
                            }
                        }
                    } else {
                        /**
                         * @todo loop over AROs
                         */
                    }
                } else {
                    /**
                     * @todo loop over ACOs
                     */
                }
                break;

            // remove from the rules
            case self::OP_REMOVE:
                /**
                 * @todo implementation
                 */
                break;

            default:
                throw Zend::exception('Zend_Acl_Exception', "Unsupported operation; must be either '" . self::OP_ADD
                                    . "' or '" . self::OP_REMOVE . "'");
        }

        return $this;
    }

    /**
     * Returns true if and only if the ARO has access to the ACO
     *
     * The $aro and $aco parameters may be references to, or the string identifiers for,
     * an existing ACO and ARO combination.
     *
     * If either $aros or $acos is null, then the query applies to all AROs or all ACOs,
     * respectively. Both may be null to query whether the ACL has a "blacklist" rule
     * (allow everything to all). By default, Zend_Acl creates a "whitelist" rule (deny
     * everything to all), and this method would return false unless this default has
     * been overridden (i.e., by executing $acl->allow()).
     *
     * If a $privilege is not provided, then this method returns false if and only if the
     * ARO is denied access to at least one privilege upon the ACO. In other words, this
     * method returns true if and only if the ARO is allowed all privileges on the ACO.
     *
     * This method checks ARO inheritance using a depth-first traversal of the ARO registry.
     * The highest priority parent (i.e., the parent most recently added) is checked first,
     * and its respective parents are checked similarly before the lower-priority parents of
     * the ARO are checked.
     *
     * @param  Zend_Acl_Aro_Interface|string $aro
     * @param  Zend_Acl_Aco_Interface|string $aco
     * @param  string                        $privilege
     * @uses   Zend_Acl::get()
     * @uses   Zend_Acl_Aro_Registry::get()
     * @return boolean
     */
    public function isAllowed($aro = null, $aco = null, $privilege = null)
    {
        /**
         * @todo remove below in favor of recursive algorithm
         */
        if (null === $aco && null === $aro) {
            if (null === $privilege) {
                // query against all ACOs, all AROs, and all privileges
                $ruleDefault = $this->_getRule();
                if (self::TYPE_DENY === $ruleDefault['type']) {
                    return false;
                }
                foreach ($this->_rules['allAcos']['allAros']['byPrivilegeId'] as $privilegeId => $rule) {
                    if (self::TYPE_DENY === $rule['type']) {
                        return false;
                    }
                }
                return true;
            } else {
                // query against all ACOs, all AROs, and one privilege
                if (null === ($rule = $this->_getRule(null, null, $privilege)) ||
                    null !== $rule['assert'] && !$rule['assert']->assert($this, null, null, $privilege)) {
                    $ruleDefault = $this->_getRule();
                    return self::TYPE_ALLOW === $ruleDefault['type'];
                } else {
                    return self::TYPE_ALLOW === $rule['type'];
                }
            }
        }

        /**
         * @todo replace above with recursive algorithm below
         */
        if (null !== $aro) {
            $aro = $this->getAroRegistry()->get($aro);
        }

        if (null !== $aco) {
            $aco = $this->get($aco);
        }

        $rule = null;
        do {
            if ($ruleExistsForAcoAndAro) {
                $rule = $foundRuleForAcoAndAro;
            } else {
                // if there are parent AROs, including 'allAros' pseudo-parent
                //      try the next parent ARO (depth-first)
                // else if there are parent ACOs, including 'allAcos' pseudo-parent
                //      try the next parent ACO
                // else
                //      (does not execute, since a rule must exist for 'allAros' and 'allAcos')
            }
        } while (null === $rule);
        // follow the rule, returning the boolean result

    }

    /**
     * Returns the rule associated with the specified ACO, ARO, and privilege
     * combination, or null if no such rule exists. The rule is an array:
     *
     * array(
     *     'type'   => either TYPE_ALLOW or TYPE_DENY
     *     'assert' => either null or an instance of Zend_Acl_Assert_Interface
     *     );
     *
     * If $aco or $aro is null, then this means that the rule must apply to
     * all ACOs or AROs, respectively.
     *
     * If $privilege is null, then the rule must apply to all privileges.
     *
     * If all three parameters are null, then the default ACL rule is returned.
     *
     * @param  Zend_Acl_Aco_Interface $aco
     * @param  Zend_Acl_Aro_Interface $aro
     * @param  string                 $privilege
     * @return array|null
     */
    protected function _getRule($aco = null, $aro = null, $privilege = null)
    {
        // follow $aco
        if (null === $aco) {
            $visitor = $this->_rules['allAcos'];
        } else if (!isset($this->_rules['byAcoId'][$acoId = $aco->getAcoId()])) {
            return null;
        } else {
            $visitor = $this->_rules['byAcoId'][$acoId];
        }

        // follow $aro
        if (null === $aro) {
            $visitor = $visitor['allAros'];
        } else if (!isset($visitor['byAroId'][$aroId = $aro->getAroId()])) {
            return null;
        } else {
            $visitor = $visitor['byAroId'][$aroId];
        }

        // follow $privilege
        if (null === $privilege) {
            $visitor = $visitor['allPrivileges'];
        } else if (!isset($visitor['byPrivilegeId'][$privilege])) {
            return null;
        } else {
            $visitor = $visitor['byPrivilegeId'][$privilege];
        }

        return $visitor;
    }

}
