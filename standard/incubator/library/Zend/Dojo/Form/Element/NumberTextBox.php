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
 * NumberTextBox dijit
 * 
 * @uses       Zend_Dojo_Form_Element_ValidationTextBox
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: $
 */
class Zend_Dojo_Form_Element_NumberTextBox extends Zend_Dojo_Form_Element_ValidationTextBox
{
    /**
     * Use NumberTextBox dijit view helper
     * @var string
     */
    public $helper = 'NumberTextBox';

    /**
     * Allowed numeric type formats
     * @var array
     */
    protected $_allowedTypes = array(
        'decimal',
        'scientific',
        'percent',
        'currency',
    );

    /**
     * Set locale
     *
     * @param  string $locale
     * @return Zend_Dojo_Form_Element_NumberTextBox
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
     * Set numeric format pattern
     *
     * @param  string $pattern
     * @return Zend_Dojo_Form_Element_NumberTextBox
     */
    public function setPattern($pattern)
    {
        $this->setDijitParam('pattern', (string) $pattern);
        return $this;
    }

    /**
     * Retrieve numeric format pattern
     *
     * @return string|null
     */
    public function getPattern()
    {
        return $this->getDijitParam('pattern');
    }

    /**
     * Set numeric format type
     *
     * @see    $_allowedTypes
     * @param  string $type
     * @return Zend_Dojo_Form_Element_NumberTextBox
     */
    public function setType($type)
    {
        $type = strtolower($type);
        if (!in_array($type, $this->_allowedTypes)) {
            require_once 'Zend/Form/Element/Exception.php';
            throw new Zend_Form_Element_Exception(sprintf('Invalid numeric type "%s" specified', $type));
        }

        $this->setDijitParam('type', $type);
        return $this;
    }

    /**
     * Retrieve type
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->getDijitParam('type');
    }

    /**
     * Set decimal places
     *
     * @param  int $places
     * @return Zend_Dojo_Form_Element_NumberTextBox
     */
    public function setPlaces($places)
    {
        $this->setDijitParam('places', (int) $places);
        return $this;
    }

    /**
     * Retrieve decimal places
     *
     * @return int|null
     */
    public function getPlaces()
    {
        return $this->getDijitParam('places');
    }

    /**
     * Set strict flag
     *
     * @param  bool $strict
     * @return Zend_Dojo_Form_Element_NumberTextBox
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
}
