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
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action_Helper
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Controller_Action_Helper_Abstract */
require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * Helper for interacting with Zend_Controller_Plugin_Acl
 *
 * @uses       Zend_Controller_Action_Helper_Abstract
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action_Helper
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Action_Helper_Acl extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var Zend_Controller_Plugin_Acl
     **/
    protected $_aclPlugin;

    /**
     * Constructor
     *
     * @return void
     **/
    function __construct()
    {
        $this->_aclPlugin = $this->getAclPlugin();
    }

    /**
     * Returns the Acl Plugin object
     *
     * @return Zend_Controller_Plugin_Acl
     **/
    public function getAclPlugin()
    {
        if (null === $this->_aclPlugin) {
            require_once 'Zend/Controller/Front.php';
            $front = Zend_Controller_Front::getInstance();
            if ($front->hasPlugin('Zend_Controller_Plugin_Acl')) {
                $this->_aclPlugin = $front->getPlugin('Zend_Controller_Plugin_Acl');
            } else {
                require_once 'Zend/Controller/Plugin/Acl.php';
                $front->registerPlugin(new Zend_Controller_Plugin_Acl());
                $this->_aclPlugin = $this->getAclPlugin();
            }
        }

        return $this->_aclPlugin;
    }

    /**
     * Call the denyAccess function of the Acl Plugin object
     *
     * @return void
     **/
    public function denyAccess()
    {
        $this->_aclPlugin->denyAccess();
    }
}
