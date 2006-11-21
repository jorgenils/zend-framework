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
 * @category   Zend
 * @package    Zend_Acl
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Acl
{
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
     * @param  Zend_Acl_Aco_Interface              $aco
     * @param  Zend_Acl_Aco_Interface|string       $parent
     * @throws Zend_Acl_Exception
     * @return self Provides a fluent interface
     */
    public function add(Zend_Acl_Aco_Interface $aco, $parent = null)
    {
        $acoId = $aco->getId();

        if ($this->has($acoId)) {
            Zend::loadClass('Zend_Acl_Exception');
            throw new Zend_Acl_Exception("ACO id '$acoId' already exists in the ACL");
        }

        $acoParent = null;

        if (null !== $parent) {
            Zend::loadClass('Zend_Acl_Exception');
            try {
                if ($parent instanceof Zend_Acl_Aco_Interface) {
                    $acoParentId = $parent->getId();
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
            $acoId = $aco->getId();
        } else {
            $acoId = $aco;
        }

        if (!$this->has($aco)) {
            Zend::loadClass('Zend_Acl_Exception');
            throw new Zend_Acl_Exception("ACO '$acoId' not found");
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
            $acoId = $aco->getId();
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
            $acoId     = $this->get($aco)->getId();
            $inheritId = $this->get($inherit)->getId();
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

}
