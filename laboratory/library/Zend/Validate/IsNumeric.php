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
 * @package    Zend_Validate
 * @subpackage Zend_Validate
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once 'Zend/Validate/Abstract.php';

/**
 * Zend Validate IsNumeric
 *
 * Simple wrapper for is_numeric().
 */
class Zend_Validate_IsNumeric extends Zend_Validate_Abstract
{
    /**
     * Validation failure reason codes
     */
    const NOT_NUMERIC = 'isNumericNot';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_EQUAL => '\'%value%\' is not a numeric value',
    );


    /**
     * Is Valid
     *
     * @see Zend_Validate_Interface::isValid()
     */
    public function isValid($value)
    {
        return is_numeric($value);
    }
}
