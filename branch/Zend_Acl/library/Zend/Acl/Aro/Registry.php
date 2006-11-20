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
 * Zend_Acl_Aro_Interface
 */
require_once 'Zend/Acl/Aro/Interface.php';


/**
 * Zend_Acl_Aro_Registry_Exception
 */
require_once 'Zend/Acl/Aro/Registry/Exception.php';


/**
 * @category   Zend
 * @package    Zend_Acl
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Acl_Aro_Registry
{
    /**
     * Internal ARO registry data storage
     *
     * @var array
     */
    protected $_aros = array();

    /**
     * Public access to registered AROs
     *
     * @param  string $aroId
     * @return Zend_Acl_Aro_Interface
     */
    public function __get($aroId)
    {
        return $this->get($aroId);
    }

    /**
     * Adds an ARO having an identifier unique to the registry
     *
     * The $inherits parameter may be a reference to, or the string identifier for,
     * an ARO existing in the registry, or $inherits may be passed as an array of
     * these - mixing string identifiers and objects is ok - to indicate the AROs
     * from which the newly added ARO will inherit.
     *
     * @param  Zend_Acl_Aro_Interface $aro
     * @param  string|array           $inherits
     * @throws Zend_Acl_Aro_Registry_Exception
     * @return self Provides a fluent interface
     */
    public function add(Zend_Acl_Aro_Interface $aro, $inherits = null)
    {
        $aroId = $aro->getId();

        if ($this->has($aroId)) {
            throw new Zend_Acl_Aro_Registry_Exception("ARO id '$aroId' already exists in the registry");
        }

        $inheritsAros = array();

        if (null !== $inherits) {
            if (!is_array($inherits)) {
                $inherits = array($inherits);
            }
            foreach ($inherits as $inherit) {
                try {
                    if ($inherit instanceof Zend_Acl_Aro_Interface) {
                        $aroInheritId = $inherit->getId();
                        $aroInherit   = $this->get($aroInheritId);
                    } else {
                        $aroInheritId = $inherit;
                        $aroInherit   = $this->get($aroInheritId);
                    }
                } catch (Zend_Acl_Aro_Registry_Exception $e) {
                    throw new Zend_Acl_Aro_Registry_Exception("Inherited ARO id '$aroInheritId' does not "
                                                            . 'exist in the registry');
                }
                $inheritsAros[$aroInheritId] = $aroInherit;
                $this->_aros[$aroInheritId]['inheritedBy'][$aroId] = $aro;
            }
        }

        $this->_aros[$aroId] = array(
            'instance'    => $aro,
            'inherits'    => $inheritsAros,
            'inheritedBy' => array()
            );

        return $this;
    }

    /**
     * Returns the ARO identified by $aroId
     *
     * @param  string $aro
     * @throws Zend_Acl_Aro_Registry_Exception
     * @return Zend_Acl_Aro_Interface
     */
    public function get($aroId)
    {
        if (!$this->has($aroId)) {
            throw new Zend_Acl_Aro_Registry_Exception("ARO '$aroId' not found");
        }

        return $this->_aros[$aroId]['instance'];
    }

    /**
     * Returns true if and only if an ARO identified by $aroId is in the registry
     *
     * @param  string $aroId
     * @return boolean
     */
    public function has($aroId)
    {
        return isset($this->_aros[$aroId]);
    }

    /**
     * Removes the ARO identified by $aroId from the registry
     *
     * @param  string $aroId
     * @throws Zend_Acl_Aro_Registry_Exception
     * @return self Provides a fluent interface
     */
    public function remove($aroId)
    {
        try {
            $aro = $this->get($aroId);
        } catch (Zend_Acl_Aro_Registry_Exception $e) {
            throw $e;
        }

        foreach ($this->_aros[$aroId]['inheritedBy'] as $inheritId => $inherit) {
            unset($this->_aros[$inheritId]['inherits'][$aroId]);
        }

        unset($this->_aros[$aroId]);

        return $this;
    }

    /**
     * Removes all AROs from the registry
     *
     * @return self Provides a fluent interface
     */
    public function removeAll()
    {
        $this->_aros = array();

        return $this;
    }

}
