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
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: $
 */

/** Zend_Dojo_View_Helper_Abstract */
require_once 'Zend/Dojo/View/Helper/Abstract.php';

/**
 * Dojo Textarea dijit
 * 
 * @uses       Zend_Dojo_View_Helper_Abstract
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
  */
class Zend_Dojo_View_Helper_Textarea extends Zend_Dojo_View_Helper_Abstract
{
    /**
     * Dijit being used
     * @var string
     */
    protected $_dijit  = 'dijit.form.Textarea';

    /**
     * HTML element type
     * @var string
     */
    protected $_elementType = 'text';

    /**
     * Dojo module to use
     * @var string
     */
    protected $_module = 'dijit.form.Textarea';

    /**
     * dijit.form.Textarea
     * 
     * @param  int $id 
     * @param  mixed $value 
     * @param  array $params  Parameters to use for dijit creation
     * @param  array $attribs HTML attributes
     * @return string
     */
    public function textarea($id, $value = null, array $params = array(), array $attribs = array())
    {
        $this->dojo->requireModule($this->_module);

        $attribs['id']    = $id;
        $attribs['name']  = $id;
        $attribs['type']  = $this->_elementType;

        foreach (array('id', 'name', 'type') as $param) {
            if (array_key_exists($param, $params)) {
                unset($params[$param]);
            }
        }

        if ($this->_useDeclarative()) {
            $attribs = array_merge($attribs, $params);
            $attribs['dojoType'] = $this->_dijit;
        } elseif (!$this->_useProgrammaticNoScript()) {
            $this->_createDijit($this->_dijit, $id, $params);
        }

        $html = '<textarea' . $this->_htmlAttribs($attribs) . '>'
              . $value
              . "</textarea>\n";

        return $html;
    }
}
