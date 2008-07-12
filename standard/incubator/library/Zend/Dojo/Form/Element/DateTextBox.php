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
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Dojo_Form_Element_ValidationTextBox */
require_once 'Zend/Dojo/Form/Element/ValidationTextBox.php';

/**
 * DateTextBox dijit
 * 
 * @uses       Zend_Dojo_Form_Element_ValidationTextBox
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: $
 */
class Zend_Dojo_Form_Element_DateTextBox extends Zend_Dojo_Form_Element_ValidationTextBox
{
    /**
     * Use DateTextBox dijit view helper
     * @var string
     */
    public $helper = 'DateTextBox';

    /**
     * Allowed formatLength types
     * @var array
     */
    protected $_allowedFormatTypes = array(
        'long',
        'short',
        'medium',
        'full',
    );

    /**
     * Allowed selector types
     * @var array
     */
    protected $_allowedSelectorTypes = array(
        'time',
        'date',
    );

    /**
     * Set am,pm flag
     *
     * @param  bool $am,pm
     * @return Zend_Dojo_Form_Element_DateTextBox
     */
    public function setAmPm($flag)
    {
        $this->setDijitParam('am,pm', (bool) $flag);
        return $this;
    }

    /**
     * Retrieve am,pm flag
     *
     * @return bool
     */
    public function getAmPm()
    {
        if (!$this->hasDijitParam('am,pm')) {
            return false;
        }
        return $this->getDijitParam('am,pm');
    }

    /**
     * Set strict flag
     *
     * @param  bool $strict
     * @return Zend_Dojo_Form_Element_DateTextBox
     */
    public function setStrict($flag)
    {
        $this->setDijitParam('strict', (bool) $flag);
        return $this;
    }

    /**
     * Retrieve strict flag
     *
     * @return bool
     */
    public function getStrict()
    {
        if (!$this->hasDijitParam('strict')) {
            return false;
        }
        return $this->getDijitParam('strict');
    }

    /**
     * Set locale
     *
     * @param  string $locale
     * @return Zend_Dojo_Form_Element_DateTextBox
     */
    public function setLocale($locale)
    {
        $this->setDijitParam('locale', (string) $locale);
        return $this;
    }

    /**
     * Retrieve locale
     *
     * @return string|null
     */
    public function getLocale()
    {
        return $this->getDijitParam('locale');
    }

    /**
     * Set date format pattern
     *
     * @param  string $pattern
     * @return Zend_Dojo_Form_Element_NumberTextBox
     */
    public function setDatePattern($pattern)
    {
        $this->setDijitParam('datePattern', (string) $pattern);
        return $this;
    }

    /**
     * Retrieve date format pattern
     *
     * @return string|null
     */
    public function getDatePattern()
    {
        return $this->getDijitParam('datePattern');
    }

    /**
     * Set numeric format formatLength
     *
     * @see    $_allowedFormatTypes
     * @param  string $formatLength
     * @return Zend_Dojo_Form_Element_NumberTextBox
     */
    public function setFormatLength($formatLength)
    {
        $formatLength = strtolower($formatLength);
        if (!in_array($formatLength, $this->_allowedFormatTypes)) {
            require_once 'Zend/Form/Element/Exception.php';
            throw new Zend_Form_Element_Exception(sprintf('Invalid formatLength "%s" specified', $formatLength));
        }

        $this->setDijitParam('formatLength', $formatLength);
        return $this;
    }

    /**
     * Retrieve formatLength
     *
     * @return string|null
     */
    public function getFormatLength()
    {
        return $this->getDijitParam('formatLength');
    }

    /**
     * Set numeric format Selector
     *
     * @see    $_allowedSelectorTypes
     * @param  string $selector
     * @return Zend_Dojo_Form_Element_NumberTextBox
     */
    public function setSelector($selector)
    {
        $selector = strtolower($selector);
        if (!in_array($selector, $this->_allowedSelectorTypes)) {
            require_once 'Zend/Form/Element/Exception.php';
            throw new Zend_Form_Element_Exception(sprintf('Invalid Selector "%s" specified', $selector));
        }

        $this->setDijitParam('selector', $selector);
        return $this;
    }

    /**
     * Retrieve selector
     *
     * @return string|null
     */
    public function getSelector()
    {
        return $this->getDijitParam('selector');
    }
}
