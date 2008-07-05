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
 * Dojo Form dijit
 * 
 * @uses       Zend_Dojo_View_Helper_Abstract
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
  */
class Zend_Dojo_View_Helper_CheckBox extends Zend_Dojo_View_Helper_Abstract
{
    /**
     * Dijit being used
     * @var string
     */
    protected $_dijit  = 'dijit.form.CheckBox';

    /**
     * Dojo module to use
     * @var string
     */
    protected $_module = 'dijit.form.CheckBox';

    /**
     * dijit.form.CheckBox
     * 
     * @param  int $id 
     * @param  string $content 
     * @param  array $params  Parameters to use for dijit creation
     * @param  array $attribs HTML attributes
     * @return string
     */
    public function checkBox($id, $value = null, array $params = array(), array $attribs = array(), array $checkedOptions = null)
    {
        // Prepare the checkbox options
        require_once 'Zend/View/Helper/FormCheckbox.php';
        $checked = false;
        if (isset($attribs['checked']) && $attribs['checked']) {
            $checked = true;
        } elseif (isset($attribs['checked'])) {
            $checked = false;
        }
        $checkboxInfo = Zend_View_Helper_FormCheckbox::determineCheckboxInfo($value, $checked, $checkedOptions);
        $attribs['checked'] = $checkboxInfo['checked'];

        $attribs = $this->_prepareDijit($attribs, $params, 'element');

        // and now we create it:
        $html = '';
        if (!strstr($id, '[]')) {
            // hidden element for unchecked value
            $html .= '<input name="' . $id . '" type="hidden" value="' . $this->view->escape($checkboxInfo['unCheckedValue']) . '"' . $this->getClosingBracket();
        }

        // and final element
        $html .= $this->_createFormElement($id, $value, $params, $attribs);

        return $html;
    }
}
