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
 * @category  Zend
 * @package   Zend_File_Transfer
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: $
 */

/**
 * Generic class for file transfers (Downloads and Uploads)
 *
 * @category  Zend
 * @package   Zend_File_Transfer
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_File_Transfer_Adapter
{
    /**
     * List of all validators
     *
     * @var array
     */
    protected $_validator = array();

    /**
     * Sets a validator for the class, erasing all previous set
     *
     * @param string $validator Type of validator to set
     * @param array  $options   Options to set for this validator
     * @return array All set options
     */
    public function setValidator(array $validator)
    {
        $this->_validator = null;
        $this->addValidator($validator);
        return $this;
    }

    /**
     * Returns all set validators with their options
     *
     * @return array List of all set validators
     */
    public function getValidator()
    {
        return $this->_validator;
    }

    /**
     * Adds a new validator for this class, old validators will not be erased
     *
     * @param array $validator Type of validator to add with it's options
     */
    public function addValidator(array $validator)
    {
        foreach ($validator as $class => $options) {
            switch(strtolower($class)) {
                case 'size':
                    $class = 'Zend_Validate_File_Size';
                    break;

                case 'extension':
                    $class = 'Zend_Validate_File_Extension';
                    break;

                default:
                    break;
            }

            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($class);
            $instance = new $class($options);
        }
        // check validator
        // load validator
        // check options
        // save in validatorchain
    }
}
