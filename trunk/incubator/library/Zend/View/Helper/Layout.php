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
 * @package    Zend_View
 * @subpackage Helpers
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id$
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * View helper for retrieving layout object
 *
 * @package    Zend_View
 * @subpackage Helpers
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_Layout
{
    /** @var Zend_Layout */
    protected $_layout;

    /** Zend_View_Interface */
    public $view;

    /**
     * Set view
     * 
     * @param  Zend_View_Interface $view 
     * @return void
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    /**
     * Get layout object
     * 
     * @return Zend_Layout
     */
    public function getLayout()
    {
        if (null === $this->_layout) {
            require_once 'Zend/Controller/Front.php';
            $front = Zend_Controller_Front::getInstance();
            if ($front->hasPlugin('Zend_Layout_Controller_Plugin_Layout')) {
                $plugin = $front->getPlugin('Zend_Layout_Controller_Plugin_Layout');
                $this->_layout = $plugin->getLayout();
            } else {
                // Implicitly sets layout object
                require_once 'Zend/Layout.php';
                $layout = new Zend_Layout();
            }
        }

        return $this->_layout;
    }

    /**
     * Set layout object
     * 
     * @param  Zend_Layout $layout 
     * @return Zend_Layout_Controller_Action_Helper_Layout
     */
    public function setLayout(Zend_Layout $layout)
    {
        $this->_layout = $layout;
        return $this;
    }

    /**
     * Return layout object
     *
     * Usage: $this->layout()->setLayout('alternate');
     * 
     * @return Zend_Layout
     */
    public function layout()
    {
        return $this->getLayout();
    }
}
