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
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Form_Element_Xhtml */
require_once 'Zend/Form/Element/Xhtml.php';

/**
 * Image form element
 * 
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Form_Element_Image extends Zend_Form_Element_Xhtml
{
    /**
     * Use formImage view helper by default
     * @var string
     */
    protected $_defaultHelper = 'formImage';

    /**
     * Load default decorators
     * 
     * @return void
     */
    protected function _loadDefaultDecorators()
    {
        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('Image')
                ->addDecorator('Errors')
                ->addDecorator('HtmlTag', array('tag' => 'dd'))
                ->addDecorator('Label', array('tag' => 'dt'));
        }
    }
}
